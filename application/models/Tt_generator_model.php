<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * Auto Timetable Generator
 *
 * Algorithm: Greedy CSP with soft-constraint scoring.
 * Subjects sorted hardest-first (consecutive > periods/week > priority).
 * For each subject, find best available slot respecting:
 *   - Teacher hard constraints (max/day, max/week, unavailability, avoid first/last)
 *   - Class time-off (tt_class_unavail)
 *   - Subject max_per_day and distribute_evenly
 *   - Room preferences (preferred_room_id, shared rooms allowed double-booking)
 *   - Teacher preferred_room_id as a scoring bonus
 */
class Tt_generator_model extends MY_Model
{
    private $CI;
    private $session_id;
    private $settings;
    private $working_days   = [];
    private $periods        = [];      // all non-break period objects indexed by id
    private $period_order   = [];      // ordered list of period IDs
    private $default_tc;               // fallback constraint object for unconfigured teachers

    // Occupancy matrices
    private $class_occ      = [];     // [class_id][section_id][day][period_id][batch_key]
    private $teacher_occ    = [];     // [staff_id][day][period_id]
    private $room_occ       = [];     // [room_id][day][period_id]  (shared rooms: count instead of bool)

    private $teacher_periods_day  = [];
    private $teacher_periods_week = [];
    private $subject_day_count    = [];  // [class_id][section_id][sgs_id][day] = count
    private $subject_day_periods  = [];  // [class_id][section_id][sgs_id][day] = [period_id, ...]

    // Class unavailability: [class_id][section_id][day][period_id] = true
    private $class_unavail   = [];
    // Room unavailability: [room_id][day][period_id] = true
    private $room_unavail    = [];
    // Subject time-off: [sgs_id][day][period_id] = true
    private $subject_unavail = [];

    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance();
    }

    public function generate($session_id, $staff_id, $class_scope, $settings)
    {
        return $this->_run($session_id, $staff_id, $class_scope, $settings, false);
    }

    public function testGenerate($session_id, $class_scope, $settings)
    {
        return $this->_run($session_id, 0, $class_scope, $settings, true);
    }

    private function _run($session_id, $staff_id, $class_scope, $settings, $dry_run)
    {
        $this->session_id = $session_id;
        $this->settings   = $settings;

        $this->_loadWorkingDays($settings);
        $this->_loadPeriods();

        if (empty($this->periods)) {
            return ['status' => '0', 'message' => 'No period slots configured. Please set up Period Setup first.'];
        }

        $this->_loadLockedEntries($session_id, $class_scope);
        $this->_loadClassUnavail($session_id);
        $this->_loadRoomUnavail($session_id);
        $this->_loadSubjectUnavail($session_id);

        // When generating a subset of classes, the generator would otherwise ignore
        // confirmed timetable entries for those teachers in OTHER classes, treating
        // shared teachers (e.g. K.MAHESWARI teaching Grade III A and Grade IV A) as
        // completely free. This causes them to be double-booked.
        // Fix: preload other classes' confirmed entries for every teacher who appears
        // in the current scope's subject loads. Done before the snapshot so all passes
        // benefit from accurate occupancy.
        if (!empty($class_scope)) {
            $this->_preloadSharedTeacherOccupancy($session_id, $class_scope);
        }

        // Snapshot occupancy after locked entries so each pass starts clean
        $locked_occ_snapshot = [
            'class_occ'            => $this->class_occ,
            'teacher_occ'          => $this->teacher_occ,
            'room_occ'             => $this->room_occ,
            'teacher_periods_day'  => $this->teacher_periods_day,
            'teacher_periods_week' => $this->teacher_periods_week,
            'subject_day_count'    => $this->subject_day_count,
            'subject_day_periods'  => [],  // always start fresh — locked entries don't need adjacency tracking
        ];

        $this->CI->load->model('Tt_teacher_model');
        $constraints = $this->CI->Tt_teacher_model->getAllConstraintsMap($session_id);
        $unavail_map = $this->CI->Tt_teacher_model->getUnavailabilityMap($session_id);

        // Default constraint applied to any teacher with no configured row.
        // Enforces sensible limits even when admin hasn't explicitly set them.
        $this->default_tc = (object)[
            'max_periods_per_day'     => 6,
            'max_periods_per_week'    => 36,
            'avoid_first_period'      => 0,
            'avoid_last_period'       => 0,
            'preferred_room_id'       => null,
            'max_consecutive_periods' => 0,  // 0 = no limit
            'min_break_after_consec'  => 1,
        ];

        // Pre-compute how many slots each teacher is unavailable for this session.
        // Teachers with more blocked slots are harder to schedule and must go first.
        $teacher_unavail_count = [];
        foreach ($unavail_map as $t_id => $day_map) {
            $cnt = 0;
            foreach ($day_map as $pid_map) { $cnt += count($pid_map); }
            $teacher_unavail_count[$t_id] = $cnt;
        }

        // Pre-compute weekly-cap tightness: a teacher whose cap is close to their
        // total assigned PPW has almost no slack — schedule those subjects first.
        // tightness = 0 when uncapped; rises as (cap - total_ppw) shrinks toward 0.
        $teacher_cap_tightness = [];
        foreach ($constraints as $t_id => $c) {
            if (!empty($c->max_periods_per_week)) {
                // Higher tightness when cap is low relative to a "full" week (48 slots).
                $teacher_cap_tightness[$t_id] = max(0, 48 - (int)$c->max_periods_per_week);
            }
        }

        $this->CI->load->model('Tt_subjectload_model');
        $base_loads = $this->CI->Tt_subjectload_model->getAllForClassScope($session_id, $class_scope);
        usort($base_loads, function($a, $b) use ($teacher_unavail_count, $teacher_cap_tightness) {
            // For each load, take the worst-constrained teacher in its pool.
            $ua = $uca = 0;
            foreach (($a->teacher_ids ?? []) as $tid) {
                $ua  = max($ua,  $teacher_unavail_count[$tid] ?? 0);
                $uca = max($uca, $teacher_cap_tightness[$tid] ?? 0);
            }
            $ub = $ucb = 0;
            foreach (($b->teacher_ids ?? []) as $tid) {
                $ub  = max($ub,  $teacher_unavail_count[$tid] ?? 0);
                $ucb = max($ucb, $teacher_cap_tightness[$tid] ?? 0);
            }
            // 0.5 per blocked slot: a teacher blocked for 12/48 slots (25% of week)
            // gains +6, lifting a PPW=1 subject level with PPW=7 — correct behaviour.
            // 0.3 per cap-tightness unit keeps it secondary to unavailability.
            $score_a = ($a->consecutive_periods * 10) + $a->periods_per_week + $a->priority
                     + ($ua * 0.5) + ($uca * 0.3);
            $score_b = ($b->consecutive_periods * 10) + $b->periods_per_week + $b->priority
                     + ($ub * 0.5) + ($ucb * 0.3);
            return $score_b <=> $score_a;
        });

        // Load joint lessons (placed first in every pass — hardest constraint)
        $this->CI->load->model('Tt_joint_model');
        $joint_lessons = $this->CI->Tt_joint_model->getAllForGeneration($session_id);

        // Filter joint lessons to only those whose class-sections intersect with class_scope
        if (!empty($class_scope) && !empty($joint_lessons)) {
            $scope_set = [];
            foreach ($class_scope as $cs) {
                $scope_set[$cs['class_id'].'_'.$cs['section_id']] = true;
            }
            foreach ($joint_lessons as $jl) {
                $jl->classes = array_values(array_filter($jl->classes, function($cs) use ($scope_set) {
                    return isset($scope_set[$cs->class_id.'_'.$cs->section_id]);
                }));
            }
            $joint_lessons = array_values(array_filter($joint_lessons, fn($jl) => count($jl->classes) >= 1));
        }

        $gen_size       = $settings['gen_size']       ?? 'normal';
        $gen_strictness = $settings['gen_strictness']  ?? 'normal';
        $passes         = ($gen_size === 'huge') ? 10 : (($gen_size === 'large') ? 3 : 1);

        $log_id = $dry_run ? 0 : $this->_createLog($session_id, $staff_id, $class_scope, $settings);

        $best_placed   = -1;
        $best_result   = null;

        for ($pass = 0; $pass < $passes; $pass++) {
            // Restore occupancy snapshot
            $this->class_occ            = $locked_occ_snapshot['class_occ'];
            $this->teacher_occ          = $locked_occ_snapshot['teacher_occ'];
            $this->room_occ             = $locked_occ_snapshot['room_occ'];
            $this->teacher_periods_day  = $locked_occ_snapshot['teacher_periods_day'];
            $this->teacher_periods_week = $locked_occ_snapshot['teacher_periods_week'];
            $this->subject_day_count    = $locked_occ_snapshot['subject_day_count'];
            $this->subject_day_periods  = $locked_occ_snapshot['subject_day_periods'];

            // Shuffle loads slightly on passes > 0 to escape local optima
            $loads = $base_loads;
            if ($pass > 0) {
                $hard_loads = array_filter($loads, fn($l) => $l->consecutive_periods > 1);
                $soft_loads = array_filter($loads, fn($l) => $l->consecutive_periods <= 1);
                shuffle($soft_loads);
                $loads = array_values(array_merge($hard_loads, $soft_loads));
            }

            $draft_entries  = [];
            $conflicts      = [];
            $total_required = 0;
            $total_placed   = 0;
            $class_stats    = [];  // [class_id.'_'.section_id] => {label, required, placed}

            // ---- JOINT LESSON PRE-PASS ----
            foreach ($joint_lessons as $jl) {
                $jl_consec          = (int) $jl->consecutive_periods;
                $jl_ppw             = (int) $jl->periods_per_week;
                $jl_teacher_ids     = $jl->teacher_ids ?? [];
                $jl_all_req         = !empty($jl->all_teachers_required);
                $jl_room            = $jl->room_id ? (int) $jl->room_id : null;
                $jl_max_day         = max(1, (int) $jl->max_per_day);
                $jl_spread          = !empty($jl->distribute_evenly);
                $placements         = ($jl_consec > 1) ? (int) ceil($jl_ppw / $jl_consec) : $jl_ppw;

                // Track per-class stats
                foreach ($jl->classes as $cs) {
                    $ck = $cs->class_id . '_' . $cs->section_id;
                    if (!isset($class_stats[$ck])) {
                        $class_stats[$ck] = ['class_id' => (int)$cs->class_id, 'section_id' => (int)$cs->section_id, 'required' => 0, 'placed' => 0];
                    }
                    $class_stats[$ck]['required'] += $placements;
                }
                $total_required += $placements;

                $jl_days_used = [];

                for ($p = 0; $p < $placements; $p++) {
                    $slot = $this->_findJointSlot($jl, $jl_teacher_ids, $jl_all_req, $jl_room,
                        $jl_consec, $jl_days_used, $jl_max_day, $jl_spread,
                        $constraints, $unavail_map);

                    if ($slot === null) {
                        $class_labels = implode('+', array_map(fn($cs) => "C{$cs->class_id}/S{$cs->section_id}", $jl->classes));
                        $teacher_label = !empty($jl_teacher_ids) ? count($jl_teacher_ids).' teacher(s)' : 'No teacher';
                        $conflicts[] = [
                            'class_id'   => 0,
                            'section_id' => 0,
                            'subject'    => $jl->subject_name . ' (Joint)',
                            'staff'      => $teacher_label,
                            'placement'  => ($p + 1) . ' of ' . $placements,
                            'reason'     => "Joint lesson [{$jl->name}] for {$class_labels}: no slot where all classes are simultaneously free.",
                        ];
                        continue;
                    }

                    $assigned_teacher = $slot['staff_id'];

                    // Place in ALL participating class-sections
                    foreach ($jl->classes as $cs) {
                        foreach ($slot['period_ids'] as $pid) {
                            $this->class_occ[(int)$cs->class_id][(int)$cs->section_id][$slot['day']][$pid][0] = true;
                            if ($assigned_teacher) {
                                $this->teacher_occ[$assigned_teacher][$slot['day']][$pid] = true;
                            }
                            if ($slot['room_id']) {
                                $this->room_occ[$slot['room_id']][$slot['day']][$pid] =
                                    ($this->room_occ[$slot['room_id']][$slot['day']][$pid] ?? 0) + 1;
                            }

                            $draft_entries[] = [
                                'gen_log_id'               => $log_id,
                                'session_id'               => $session_id,
                                'class_id'                 => (int)$cs->class_id,
                                'section_id'               => (int)$cs->section_id,
                                'subject_group_id'         => (int)$cs->sg_id,
                                'subject_group_subject_id' => (int)$cs->sgs_id,
                                'staff_id'                 => $assigned_teacher,
                                'period_id'                => $pid,
                                'day'                      => $slot['day'],
                                'room_id'                  => $slot['room_id'],
                                'batch_id'                 => null,
                            ];
                        }

                        $ck = $cs->class_id . '_' . $cs->section_id;
                        if (isset($class_stats[$ck])) $class_stats[$ck]['placed']++;
                    }

                    if ($assigned_teacher) {
                        foreach ($slot['period_ids'] as $pid) {
                            $this->teacher_periods_day[$assigned_teacher][$slot['day']] =
                                ($this->teacher_periods_day[$assigned_teacher][$slot['day']] ?? 0) + 1;
                            $this->teacher_periods_week[$assigned_teacher] =
                                ($this->teacher_periods_week[$assigned_teacher] ?? 0) + 1;
                        }
                    }

                    // Mark ALL teachers occupied when all_teachers_required is set
                    if ($jl_all_req && count($jl_teacher_ids) > 1) {
                        foreach ($jl_teacher_ids as $t_extra) {
                            if ($t_extra === $assigned_teacher) continue;
                            foreach ($slot['period_ids'] as $pid) {
                                $this->teacher_occ[$t_extra][$slot['day']][$pid] = true;
                                $this->teacher_periods_day[$t_extra][$slot['day']] =
                                    ($this->teacher_periods_day[$t_extra][$slot['day']] ?? 0) + 1;
                                $this->teacher_periods_week[$t_extra] =
                                    ($this->teacher_periods_week[$t_extra] ?? 0) + 1;
                            }
                        }
                    }

                    $jl_days_used[] = $slot['day'];
                    $total_placed++;
                }
            }
            // ---- END JOINT LESSON PRE-PASS ----

            foreach ($loads as $load) {
                $class_id   = (int) $load->class_id;
                $section_id = (int) $load->section_id;
                $teacher_ids_load     = $load->teacher_ids ?? [];
                $all_teachers_req     = !empty($load->all_teachers_required);
                // Fallback for loads without a pool yet
                if (empty($teacher_ids_load)) {
                    if (!empty($load->staff_id))     $teacher_ids_load[] = (int)$load->staff_id;
                    if (!empty($load->alt_staff_id)) $teacher_ids_load[] = (int)$load->alt_staff_id;
                }
                $periods_pw = (int) $load->periods_per_week;
                $consec     = (int) $load->consecutive_periods;
                $batch_id   = $load->batch_id ? (int)$load->batch_id : null;
                $batch_key  = $batch_id ?: 0;
                $room_type  = $load->preferred_room_type ?? 'any';
                $pref_room  = !empty($load->preferred_room_id) ? (int)$load->preferred_room_id : null;
                $sgs_id     = (int) $load->subject_group_subject_id;

                // max_per_day is a hard user constraint — never override it.
                // strict mode may tighten it to 1; relaxed mode must not loosen it.
                $max_per_day = (int) ($load->max_per_day ?? 2);
                if ($gen_strictness === 'strict') $max_per_day = min($max_per_day, 1);

                $min_per_day  = !empty($load->min_per_day) ? 1 : 0;
                $dist_evenly  = !empty($load->distribute_evenly);

                // Teacher preferred room fallback (use primary teacher)
                $primary_t = $teacher_ids_load[0] ?? null;
                if (!$pref_room && $primary_t && !empty($constraints[$primary_t]->preferred_room_id)) {
                    $pref_room = (int) $constraints[$primary_t]->preferred_room_id;
                }

                $placements_needed = ($consec > 1)
                    ? (int) ceil($periods_pw / $consec)
                    : $periods_pw;

                $ck = $class_id . '_' . $section_id;
                if (!isset($class_stats[$ck])) {
                    $class_stats[$ck] = ['class_id' => $class_id, 'section_id' => $section_id, 'required' => 0, 'placed' => 0];
                }
                $class_stats[$ck]['required'] += $placements_needed;

                $total_required += $placements_needed;
                $placed_count    = 0;
                $subject_days_used = [];

                for ($p = 0; $p < $placements_needed; $p++) {
                    $slot = $this->_findBestSlot(
                        $class_id, $section_id, $batch_key, $sgs_id,
                        $teacher_ids_load, $all_teachers_req,
                        $consec, $subject_days_used,
                        $constraints, $unavail_map,
                        $room_type, $pref_room,
                        $max_per_day, $min_per_day, $dist_evenly, $periods_pw
                    );

                    if ($slot === null) {
                        $conflicts[] = [
                            'class_id'   => $class_id,
                            'section_id' => $section_id,
                            'subject'    => $load->subject_name . ' (' . ($load->subject_code ?? '') . ')',
                            'staff'      => $load->staff_name . ' ' . $load->staff_surname,
                            'staff_id'   => $teacher_ids_load[0] ?? null,
                            'placement'  => ($p + 1) . ' of ' . $placements_needed,
                            'reason'     => $this->_diagnoseFailure($class_id, $section_id, $batch_key, $teacher_ids_load, $constraints),
                            'type'       => 'no_slot',
                        ];
                        continue;
                    }

                    $assigned_teacher = $slot['staff_id'];
                    foreach ($slot['period_ids'] as $pid) {
                        $this->class_occ[$class_id][$section_id][$slot['day']][$pid][$batch_key] = true;
                        $this->teacher_occ[$assigned_teacher][$slot['day']][$pid] = true;
                        if ($slot['room_id']) {
                            $this->room_occ[$slot['room_id']][$slot['day']][$pid] =
                                ($this->room_occ[$slot['room_id']][$slot['day']][$pid] ?? 0) + 1;
                        }
                        $this->teacher_periods_day[$assigned_teacher][$slot['day']] =
                            ($this->teacher_periods_day[$assigned_teacher][$slot['day']] ?? 0) + 1;
                        $this->teacher_periods_week[$assigned_teacher] =
                            ($this->teacher_periods_week[$assigned_teacher] ?? 0) + 1;
                        $this->subject_day_count[$class_id][$section_id][$sgs_id][$slot['day']] =
                            ($this->subject_day_count[$class_id][$section_id][$sgs_id][$slot['day']] ?? 0) + 1;
                        $this->subject_day_periods[$class_id][$section_id][$sgs_id][$slot['day']][] = $pid;

                        $draft_entries[] = [
                            'gen_log_id'               => $log_id,
                            'session_id'               => $session_id,
                            'class_id'                 => $class_id,
                            'section_id'               => $section_id,
                            'subject_group_id'         => (int)$load->subject_group_id,
                            'subject_group_subject_id' => $sgs_id,
                            'staff_id'                 => $assigned_teacher,
                            'period_id'                => $pid,
                            'day'                      => $slot['day'],
                            'room_id'                  => $slot['room_id'],
                            'batch_id'                 => $batch_id,
                        ];
                    }

                    // Mark ALL teachers occupied when all_teachers_required is set
                    if ($all_teachers_req && count($teacher_ids_load) > 1) {
                        foreach ($teacher_ids_load as $t_extra) {
                            if ($t_extra === $assigned_teacher) continue;
                            foreach ($slot['period_ids'] as $pid) {
                                $this->teacher_occ[$t_extra][$slot['day']][$pid] = true;
                                $this->teacher_periods_day[$t_extra][$slot['day']] =
                                    ($this->teacher_periods_day[$t_extra][$slot['day']] ?? 0) + 1;
                                $this->teacher_periods_week[$t_extra] =
                                    ($this->teacher_periods_week[$t_extra] ?? 0) + 1;
                            }
                        }
                    }

                    $subject_days_used[] = $slot['day'];
                    $placed_count++;
                    $total_placed++;
                    $class_stats[$ck]['placed']++;
                }

                // On1 check
                if ($min_per_day && $placed_count > 0) {
                    $days_covered  = array_unique($subject_days_used);
                    $missing_days  = array_diff($this->working_days, $days_covered);
                    $wd_count      = count($this->working_days);
                    $impossible    = $periods_pw < $wd_count;
                    foreach ($missing_days as $md) {
                        $reason = $impossible
                            ? "On1 warning: {$md} has no {$load->subject_name} — subject only has {$periods_pw} period(s)/week across {$wd_count} working days, so it cannot appear on every day. Disable the 'Min 1/day' setting for this subject."
                            : "On1 warning: could not place {$load->subject_name} on {$md} — teacher or class unavailable that day.";
                        $conflicts[] = [
                            'class_id'   => $class_id,
                            'section_id' => $section_id,
                            'subject'    => $load->subject_name . ' (' . ($load->subject_code ?? '') . ')',
                            'staff'      => $load->staff_name . ' ' . $load->staff_surname,
                            'staff_id'   => $teacher_ids_load[0] ?? null,
                            'placement'  => 'On1',
                            'reason'     => $reason,
                            'type'       => 'on1',
                        ];
                    }
                }
            }

            if ($total_placed > $best_placed) {
                $best_placed = $total_placed;
                $best_result = compact('draft_entries', 'conflicts', 'total_required', 'total_placed', 'class_stats');
            }

            if ($total_placed === $total_required) break; // perfect — no need for more passes
        }

        $total_required = $best_result['total_required'];
        $total_placed   = $best_result['total_placed'];
        $draft_entries  = $best_result['draft_entries'];
        $conflicts      = $best_result['conflicts'];
        $class_stats    = array_values($best_result['class_stats'] ?? []);
        $quality = ($total_required > 0) ? round(($total_placed / $total_required) * 100, 2) : 100.00;

        if (!$dry_run) {
            if (!empty($draft_entries)) {
                $this->db->insert_batch('tt_draft_entries', $draft_entries);
            }
            $this->db->where('id', $log_id)->update('tt_gen_log', [
                'status'           => 'completed',
                'total_required'   => $total_required,
                'total_placed'     => $total_placed,
                'total_conflicts'  => count($conflicts),
                'quality_score'    => $quality,
                'conflict_details' => json_encode($conflicts),
            ]);
        }

        return [
            'status'          => '1',
            'log_id'          => $log_id,
            'total_required'  => $total_required,
            'total_placed'    => $total_placed,
            'cards_placed'    => $total_placed,
            'cards_left'      => $total_required - $total_placed,
            'total_conflicts' => count($conflicts),
            'quality_score'   => $quality,
            'conflicts'       => $conflicts,
            'class_stats'     => $class_stats,
            'dry_run'         => $dry_run,
        ];
    }

    /**
     * Produces a specific, human-readable reason why placement failed.
     * Checks teacher weekly cap, class grid fullness, and general unavailability.
     */
    private function _diagnoseFailure($class_id, $section_id, $batch_key, array $teacher_ids, $constraints)
    {
        $parts = [];

        foreach ($teacher_ids as $t_id) {
            $booked = $this->teacher_periods_week[$t_id] ?? 0;
            $c = $constraints[$t_id] ?? null;
            if ($c && $c->max_periods_per_week > 0 && $booked >= (int)$c->max_periods_per_week) {
                $parts[] = "Teacher is at weekly maximum ({$booked} of {$c->max_periods_per_week} periods used)";
            } elseif ($booked > 0) {
                $parts[] = "Teacher has {$booked} period(s) booked — no free slot aligns with this class";
            } else {
                $parts[] = "Teacher has no bookings but all slots are blocked by unavailability or class constraints";
            }
        }

        // Check class grid fullness
        $used  = 0;
        $total = count($this->working_days) * count($this->period_order);
        foreach ($this->working_days as $day) {
            foreach ($this->period_order as $pid) {
                if (!empty($this->class_occ[$class_id][$section_id][$day][$pid][$batch_key])) $used++;
            }
        }
        if ($used >= $total) {
            $parts[] = "Class timetable is completely full ({$used}/{$total} slots)";
        }

        return implode('; ', $parts) ?: 'No available slot — teacher or class unavailability blocks all options.';
    }

    private function _findBestSlot($class_id, $section_id, $batch_key, $sgs_id,
                                    array $teacher_ids, $all_teachers_required,
                                    $consec, $days_used,
                                    $constraints, $unavail_map,
                                    $room_type, $pref_room,
                                    $max_per_day, $min_per_day, $dist_evenly, $periods_pw = 0)
    {
        $best       = null;
        $best_score = -999;
        $primary    = $teacher_ids[0] ?? null;

        // Overflow mode: PPW > working days → the extra period must land on a day that
        // already has one, so we flip the adjacency penalty into a small bonus to encourage
        // the double to be consecutive (e.g. 7 PPW / 6 days → 1 day gets back-to-back).
        $wd_count       = count($this->working_days);
        $days_with_subj = count(array_filter(
            $this->subject_day_count[$class_id][$section_id][$sgs_id] ?? [],
            fn($c) => $c > 0
        ));
        $overflow_mode = ($periods_pw > 0) && ($periods_pw > $wd_count) && ($days_with_subj >= $wd_count);

        foreach ($this->working_days as $day) {
            $day_subject_count = $this->subject_day_count[$class_id][$section_id][$sgs_id][$day] ?? 0;
            if ($day_subject_count >= $max_per_day) continue;

            $day_penalty   = ($dist_evenly && in_array($day, $days_used)) ? -10 : 0;
            $day_on1_bonus = ($min_per_day && $day_subject_count === 0 && !in_array($day, $days_used)) ? 8 : 0;

            foreach ($this->_getConsecutiveStarts($consec) as $pid_group) {
                // Class free check
                $class_free = true;
                foreach ($pid_group as $pid) {
                    if (!empty($this->class_occ[$class_id][$section_id][$day][$pid][$batch_key])) {
                        $class_free = false; break;
                    }
                    if (!empty($this->class_unavail[$class_id][$section_id][$day][$pid])) {
                        $class_free = false; break;
                    }
                }
                if (!$class_free) continue;

                // Subject time-off check
                $subj_free = true;
                foreach ($pid_group as $pid) {
                    if (!empty($this->subject_unavail[$sgs_id][$day][$pid])) {
                        $subj_free = false; break;
                    }
                }
                if (!$subj_free) continue;

                if ($all_teachers_required && !empty($teacher_ids)) {
                    // ALL teachers must be free simultaneously
                    $all_free = true;
                    foreach ($teacher_ids as $t_id) {
                        $c = $constraints[$t_id] ?? $this->default_tc;
                        if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $c->max_periods_per_day) { $all_free = false; break; }
                        if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)     { $all_free = false; break; }
                        if ($c->avoid_first_period && $pid_group[0] === $this->period_order[0])                  { $all_free = false; break; }
                        if ($c->avoid_last_period  && end($pid_group) === end($this->period_order))              { $all_free = false; break; }
                        if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) { $all_free = false; break; }
                        foreach ($pid_group as $pid) {
                            if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $all_free = false; break 2; }
                            if (!empty($unavail_map[$t_id][$day][$pid]))        { $all_free = false; break 2; }
                        }
                    }
                    if (!$all_free) continue;

                    $room_id = $this->_findRoom($day, $pid_group, $room_type, $pref_room, $primary, $constraints[$primary] ?? $this->default_tc);
                    $adj     = $this->_adjacencyPenalty($class_id, $section_id, $sgs_id, $day, $pid_group, $consec, $max_per_day);
                    if ($overflow_mode && $adj < 0) $adj = 5;
                    $score   = $day_penalty + $day_on1_bonus + $adj;
                    if ($room_id && $room_id === $pref_room) $score += 3;
                    $score  -= array_search($day, $this->working_days) * 0.1;

                    if ($score > $best_score) {
                        $best_score = $score;
                        $best = ['day' => $day, 'period_ids' => $pid_group, 'staff_id' => $primary, 'room_id' => $room_id];
                    }
                } else {
                    // Pool mode: try each teacher, pick best scoring
                    $candidates = !empty($teacher_ids) ? $teacher_ids : [null];
                    foreach ($candidates as $t_id) {
                        if ($t_id !== null) {
                            $c = $constraints[$t_id] ?? $this->default_tc;
                            if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $c->max_periods_per_day) continue;
                            if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)     continue;
                            if ($c->avoid_first_period && $pid_group[0] === $this->period_order[0])                  continue;
                            if ($c->avoid_last_period  && end($pid_group) === end($this->period_order))              continue;
                            $t_free = true;
                            foreach ($pid_group as $pid) {
                                if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $t_free = false; break; }
                                if (!empty($unavail_map[$t_id][$day][$pid]))        { $t_free = false; break; }
                            }
                            if (!$t_free) continue;
                            if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) continue;
                        } else {
                            $c = null;
                        }

                        $room_id = $this->_findRoom($day, $pid_group, $room_type, $pref_room, $t_id, $c);
                        $adj     = $this->_adjacencyPenalty($class_id, $section_id, $sgs_id, $day, $pid_group, $consec, $max_per_day);
                        if ($overflow_mode && $adj < 0) $adj = 5;
                        $score   = $day_penalty + $day_on1_bonus + $adj;
                        if ($t_id === $primary) $score += 5;
                        if ($room_id && $room_id === $pref_room) $score += 3;
                        $score  -= array_search($day, $this->working_days) * 0.1;

                        if ($score > $best_score) {
                            $best_score = $score;
                            $best = ['day' => $day, 'period_ids' => $pid_group, 'staff_id' => $t_id, 'room_id' => $room_id];
                        }
                    }
                }
            }
        }

        return $best;
    }

    /**
     * Find a slot where ALL participating class-sections + teacher are simultaneously free.
     * Returns ['day', 'period_ids', 'staff_id', 'room_id'] or null.
     */
    private function _findJointSlot($jl, array $teacher_ids, $all_teachers_required, $pref_room,
                                     $consec, $days_used, $max_per_day, $dist_evenly,
                                     $constraints, $unavail_map)
    {
        $best       = null;
        $best_score = -999;
        $primary    = $teacher_ids[0] ?? null;

        foreach ($this->working_days as $day) {
            $day_penalty = ($dist_evenly && in_array($day, $days_used)) ? -10 : 0;

            foreach ($this->_getConsecutiveStarts($consec) as $pid_group) {
                // ALL class-sections must be free in this slot
                $all_cs_free = true;
                foreach ($jl->classes as $cs) {
                    foreach ($pid_group as $pid) {
                        if (!empty($this->class_occ[(int)$cs->class_id][(int)$cs->section_id][$day][$pid][0])) {
                            $all_cs_free = false; break 2;
                        }
                        if (!empty($this->class_unavail[(int)$cs->class_id][(int)$cs->section_id][$day][$pid])) {
                            $all_cs_free = false; break 2;
                        }
                    }
                }
                if (!$all_cs_free) continue;

                // Subject time-off check — joint lessons share the same subject across all classes,
                // so checking the first class's sgs_id is sufficient
                $first_sgs = !empty($jl->classes[0]->sgs_id) ? (int)$jl->classes[0]->sgs_id : 0;
                if ($first_sgs) {
                    $subj_ok = true;
                    foreach ($pid_group as $pid) {
                        if (!empty($this->subject_unavail[$first_sgs][$day][$pid])) {
                            $subj_ok = false; break;
                        }
                    }
                    if (!$subj_ok) continue;
                }

                if ($all_teachers_required && !empty($teacher_ids)) {
                    // ALL teachers must be free simultaneously
                    $all_free = true;
                    foreach ($teacher_ids as $t_id) {
                        $c = $constraints[$t_id] ?? $this->default_tc;
                        if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $c->max_periods_per_day) { $all_free = false; break; }
                        if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)     { $all_free = false; break; }
                        if ($c->avoid_first_period && $pid_group[0] === $this->period_order[0])                  { $all_free = false; break; }
                        if ($c->avoid_last_period  && end($pid_group) === end($this->period_order))              { $all_free = false; break; }
                        if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) { $all_free = false; break; }
                        foreach ($pid_group as $pid) {
                            if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $all_free = false; break 2; }
                            if (!empty($unavail_map[$t_id][$day][$pid]))        { $all_free = false; break 2; }
                        }
                    }
                    if (!$all_free) continue;

                    $room_id = $this->_findRoom($day, $pid_group, 'any', $pref_room, $primary, $constraints[$primary] ?? $this->default_tc);
                    $score   = $day_penalty;
                    if ($room_id && $room_id === $pref_room) $score += 3;
                    $score  -= array_search($day, $this->working_days) * 0.1;

                    if ($score > $best_score) {
                        $best_score = $score;
                        $best = ['day' => $day, 'period_ids' => $pid_group, 'staff_id' => $primary, 'room_id' => $room_id];
                    }
                } else {
                    // Pool mode: try each teacher, pick best scoring
                    $candidates = !empty($teacher_ids) ? $teacher_ids : [null];
                    foreach ($candidates as $t_id) {
                        if ($t_id !== null) {
                            $c = $constraints[$t_id] ?? $this->default_tc;
                            if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $c->max_periods_per_day) continue;
                            if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)     continue;
                            if ($c->avoid_first_period && $pid_group[0] === $this->period_order[0])                  continue;
                            if ($c->avoid_last_period  && end($pid_group) === end($this->period_order))              continue;
                            $t_free = true;
                            foreach ($pid_group as $pid) {
                                if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $t_free = false; break; }
                                if (!empty($unavail_map[$t_id][$day][$pid]))        { $t_free = false; break; }
                            }
                            if (!$t_free) continue;
                            if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) continue;
                        } else {
                            $c = null;
                        }

                        $room_id = $this->_findRoom($day, $pid_group, 'any', $pref_room, $t_id, $c);
                        $score   = $day_penalty;
                        if ($t_id === $primary) $score += 5;
                        if ($room_id && $room_id === $pref_room) $score += 3;
                        $score  -= array_search($day, $this->working_days) * 0.1;

                        if ($score > $best_score) {
                            $best_score = $score;
                            $best = ['day' => $day, 'period_ids' => $pid_group, 'staff_id' => $t_id, 'room_id' => $room_id];
                        }
                    }
                }
            }
        }
        return $best;
    }

    /**
     * Returns a negative score when a single-period subject would land
     * immediately next to one of its already-placed periods on the same day.
     *
     * max_per_day = 1  → strong penalty (-25): back-to-back is always wrong
     * max_per_day > 1  → soft penalty (-8): same day is allowed but consecutive
     *                    slots are still undesirable (e.g. Maths 5×/week, 2/day OK
     *                    but periods 5+6 back-to-back is poor pedagogy — prefer
     *                    spacing them across non-adjacent slots).
     * consec > 1       → no penalty: explicitly configured double/triple block.
     */
    private function _adjacencyPenalty($class_id, $section_id, $sgs_id, $day, $pid_group, $consec, $max_per_day)
    {
        if ($consec > 1) return 0;  // intentional double/triple block
        $placed = $this->subject_day_periods[$class_id][$section_id][$sgs_id][$day] ?? [];
        if (empty($placed)) return 0;

        $period_idx    = array_flip($this->period_order);
        $candidate_idx = $period_idx[$pid_group[0]] ?? -99;
        foreach ($placed as $pp) {
            if (abs($candidate_idx - ($period_idx[$pp] ?? -99)) === 1) {
                return $max_per_day > 1 ? -8 : -25;
            }
        }
        return 0;
    }

    /**
     * Returns true if placing $pid_group for teacher $t_id on $day would
     * exceed their configured max_consecutive_periods or violate the required
     * break gap after hitting that limit.
     *
     * max_consec = 0   → no limit, always returns false.
     * min_break         → consecutive free slots required after a max-length run.
     *                     With min_break=1 this is automatically satisfied by the
     *                     max_consec check alone (adding at position run_end+1 would
     *                     extend the run beyond max_consec). min_break>1 explicitly
     *                     enforces a wider gap between teaching blocks.
     */
    private function _violatesConsecRule($t_id, $day, $pid_group, $max_consec, $min_break)
    {
        if ($max_consec <= 0) return false;

        $period_idx = array_flip($this->period_order);

        // Existing occupied indices for this teacher on this day
        $occupied = [];
        foreach ($this->period_order as $idx => $pid) {
            if (!empty($this->teacher_occ[$t_id][$day][$pid])) $occupied[$idx] = true;
        }
        // Add candidate period(s)
        foreach ($pid_group as $pid) {
            if (isset($period_idx[$pid])) $occupied[$period_idx[$pid]] = true;
        }
        if (empty($occupied)) return false;

        $idxs = array_keys($occupied);
        sort($idxs);

        $run_len = 1;
        $prev    = $idxs[0];
        for ($i = 1; $i < count($idxs); $i++) {
            $curr = $idxs[$i];
            if ($curr === $prev + 1) {
                $run_len++;
                if ($run_len > $max_consec) return true;
            } else {
                // Gap found — if prior run hit the max, check the gap is wide enough
                if ($run_len === $max_consec && $min_break > 1 && ($curr - $prev - 1) < $min_break) {
                    return true;
                }
                $run_len = 1;
            }
            $prev = $curr;
        }
        return false;
    }

    private function _getConsecutiveStarts($consec)
    {
        if ($consec <= 1) {
            return array_map(fn($pid) => [$pid], $this->period_order);
        }
        $groups = [];
        $n = count($this->period_order);
        for ($i = 0; $i <= $n - $consec; $i++) {
            $groups[] = array_slice($this->period_order, $i, $consec);
        }
        return $groups;
    }

    private function _findRoom($day, $pid_group, $room_type, $pref_room, $t_id, $constraint)
    {
        // Try teacher preferred room if set
        $teacher_pref_room = !empty($constraint->preferred_room_id) ? (int)$constraint->preferred_room_id : null;

        $candidates = [];
        if ($pref_room)         $candidates[] = $pref_room;
        if ($teacher_pref_room && $teacher_pref_room !== $pref_room) $candidates[] = $teacher_pref_room;

        foreach ($candidates as $rid) {
            if ($this->_roomFree($rid, $day, $pid_group)) return $rid;
        }

        if ($room_type === 'any' && empty($candidates)) return null;

        $this->CI->load->model('Tt_room_model');
        $rooms = $room_type !== 'any'
            ? $this->CI->Tt_room_model->getByType($room_type)
            : $this->CI->Tt_room_model->getActive();

        foreach ($rooms as $room) {
            if (in_array($room->id, $candidates)) continue;
            if ($room->is_shared) return $room->id;  // shared rooms always available
            if ($this->_roomFree($room->id, $day, $pid_group)) return $room->id;
        }

        return null;
    }

    private function _roomFree($room_id, $day, $pid_group)
    {
        foreach ($pid_group as $pid) {
            if (!empty($this->room_occ[$room_id][$day][$pid])) return false;
            if (!empty($this->room_unavail[$room_id][$day][$pid])) return false;
        }
        return true;
    }

    /**
     * When regenerating a subset of classes, preload confirmed tt_entries from
     * OTHER classes for every teacher assigned to the current scope.
     *
     * Without this, a teacher like K.MAHESWARI who is confirmed in Grade III A
     * would appear fully free when generating Grade III A's timetable separately,
     * causing double-booking and "no available slot" failures for low-PPW subjects
     * processed last in the greedy order.
     *
     * Scope's own entries are skipped — those are about to be replaced.
     */
    private function _preloadSharedTeacherOccupancy($session_id, $class_scope)
    {
        // Collect teacher IDs from all loads for the current scope
        $loads = $this->db->select('tslt.staff_id')
            ->from('tt_subject_load sl')
            ->join('tt_subject_load_teachers tslt', 'tslt.subject_load_id = sl.id')
            ->where('sl.session_id', $session_id)
            ->group_start();
        foreach ($class_scope as $cs) {
            $loads->or_group_start()
                ->where('sl.class_id', (int)$cs['class_id'])
                ->where('sl.section_id', (int)$cs['section_id'])
                ->group_end();
        }
        $loads = $loads->group_end()->get()->result();

        $teacher_ids = array_unique(array_column($loads, 'staff_id'));
        if (empty($teacher_ids)) return;

        // Build set of scope class+section keys to exclude
        $scope_keys = [];
        foreach ($class_scope as $cs) {
            $scope_keys[(int)$cs['class_id'] . '_' . (int)$cs['section_id']] = true;
        }

        // Fetch confirmed entries for these teachers from ALL classes
        $entries = $this->db->select('staff_id, day, period_id, class_id, section_id')
            ->from('tt_entries')
            ->where('session_id', $session_id)
            ->where_in('staff_id', $teacher_ids)
            ->get()->result();

        foreach ($entries as $e) {
            // Skip entries belonging to the classes being regenerated
            if (isset($scope_keys[$e->class_id . '_' . $e->section_id])) continue;

            $tid = (int)$e->staff_id;
            $this->teacher_occ[$tid][$e->day][$e->period_id] = true;
            $this->teacher_periods_day[$tid][$e->day] =
                ($this->teacher_periods_day[$tid][$e->day] ?? 0) + 1;
            $this->teacher_periods_week[$tid] =
                ($this->teacher_periods_week[$tid] ?? 0) + 1;
        }
    }

    private function _loadWorkingDays($settings)
    {
        $this->CI->load->library('Customlib');
        $days_map = $this->CI->customlib->getDaysnameWithoutLang();
        $this->working_days = [];
        foreach (array_keys($days_map) as $d) {
            if ($d === 'Sunday') continue;
            if ($d === 'Saturday' && empty($settings['allow_saturday'])) continue;
            $this->working_days[] = $d;
        }
    }

    private function _loadPeriods()
    {
        $rows = $this->db->where('session_id', $this->session_id)
            ->where('is_break', 0)
            ->order_by('sort_order','ASC')
            ->get('tt_periods')->result();
        $this->periods = [];
        $this->period_order = [];
        foreach ($rows as $r) {
            $this->periods[$r->id] = $r;
            $this->period_order[]  = $r->id;
        }
    }

    private function _loadLockedEntries($session_id, $class_scope)
    {
        $q = $this->db->select('tt_entries.*')
            ->from('tt_entries')
            ->where('tt_entries.session_id', $session_id)
            ->where('tt_entries.is_locked', 1);
        if (!empty($class_scope)) {
            $q->group_start();
            foreach ($class_scope as $cs) {
                $q->or_group_start()
                    ->where('tt_entries.class_id', (int)$cs['class_id'])
                    ->where('tt_entries.section_id', (int)$cs['section_id'])
                    ->group_end();
            }
            $q->group_end();
        }
        foreach ($q->get()->result() as $e) {
            $bk = $e->batch_id ?: 0;
            $this->class_occ[$e->class_id][$e->section_id][$e->day][$e->period_id][$bk] = true;
            if ($e->staff_id) {
                $this->teacher_occ[$e->staff_id][$e->day][$e->period_id] = true;
                $this->teacher_periods_day[$e->staff_id][$e->day] =
                    ($this->teacher_periods_day[$e->staff_id][$e->day] ?? 0) + 1;
                $this->teacher_periods_week[$e->staff_id] =
                    ($this->teacher_periods_week[$e->staff_id] ?? 0) + 1;
            }
            if ($e->room_id) {
                $this->room_occ[$e->room_id][$e->day][$e->period_id] =
                    ($this->room_occ[$e->room_id][$e->day][$e->period_id] ?? 0) + 1;
            }
        }
    }

    private function _loadClassUnavail($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_class_unavail')->result();
        foreach ($rows as $r) {
            $this->class_unavail[$r->class_id][$r->section_id][$r->day][$r->period_id] = true;
        }
    }

    private function _loadRoomUnavail($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_room_unavail')->result();
        foreach ($rows as $r) {
            $this->room_unavail[$r->room_id][$r->day][$r->period_id] = true;
        }
    }

    private function _loadSubjectUnavail($session_id)
    {
        // tt_subject_unavail stores by subject_id; map to sgs_id via subject_group_subjects
        $rows = $this->db->select('tt_subject_unavail.*, subject_group_subjects.id as sgs_id')
            ->from('tt_subject_unavail')
            ->join('subject_group_subjects', 'subject_group_subjects.subject_id = tt_subject_unavail.subject_id', 'left')
            ->where('tt_subject_unavail.session_id', $session_id)
            ->get()->result();
        foreach ($rows as $r) {
            if ($r->sgs_id) {
                $this->subject_unavail[$r->sgs_id][$r->day][$r->period_id] = true;
            }
        }
    }

    private function _createLog($session_id, $staff_id, $class_scope, $settings)
    {
        $this->db->insert('tt_gen_log', [
            'session_id'    => $session_id,
            'generated_by'  => $staff_id,
            'class_scope'   => json_encode($class_scope),
            'status'        => 'running',
            'settings_json' => json_encode($settings),
        ]);
        return $this->db->insert_id();
    }

    public function confirmDraft($gen_log_id, $confirmed_by)
    {
        $row = $this->db->select('session_id, class_scope')->where('id', $gen_log_id)->get('tt_gen_log')->row();
        if (!$row) return false;

        $class_scope = json_decode($row->class_scope, true);
        $sid = $row->session_id;

        $this->db->trans_start();

        $this->CI->load->model('Tt_entry_model');
        $this->CI->Tt_entry_model->deleteByScopeExceptLocked($sid, $class_scope);

        $drafts = $this->db->where('gen_log_id', $gen_log_id)->get('tt_draft_entries')->result_array();
        if (!empty($drafts)) {
            $live = array_map(function($d) {
                unset($d['id'], $d['gen_log_id']);
                $d['entry_type']     = 'auto';
                $d['is_locked']      = 0;
                $d['is_free_period'] = 0;
                return $d;
            }, $drafts);
            $this->db->insert_batch('tt_entries', $live);
        }

        $this->db->where('id', $gen_log_id)->update('tt_gen_log', [
            'confirmed_at' => date('Y-m-d H:i:s'),
            'confirmed_by' => $confirmed_by,
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function discardDraft($gen_log_id)
    {
        $this->db->where('gen_log_id', $gen_log_id)->delete('tt_draft_entries');
        $this->db->where('id', $gen_log_id)->update('tt_gen_log', ['status' => 'failed']);
    }

    public function getLog($gen_log_id)
    {
        return $this->db->where('id', $gen_log_id)->get('tt_gen_log')->row();
    }

    public function getRecentLogs($session_id, $limit = 5)
    {
        return $this->db->select('tt_gen_log.*, staff.name as generated_by_name, staff.surname as generated_by_surname')
            ->from('tt_gen_log')
            ->join('staff','staff.id = tt_gen_log.generated_by','left')
            ->where('tt_gen_log.session_id', $session_id)
            ->order_by('tt_gen_log.generated_at','DESC')
            ->limit($limit)
            ->get()->result();
    }

    public function getDraftGrouped($gen_log_id)
    {
        $rows = $this->db->select('tt_draft_entries.*, subjects.name as subject_name, subjects.type as subject_type, subjects.tt_color, subjects.tt_abbr, staff.name as staff_name, staff.surname as staff_surname, tt_rooms.name as room_name, tt_batches.batch_name, classes.class as class_name, sections.section as section_name, tt_periods.name as period_name, tt_periods.start_time, tt_periods.end_time, tt_periods.sort_order')
            ->from('tt_draft_entries')
            ->join('subject_group_subjects','subject_group_subjects.id = tt_draft_entries.subject_group_subject_id','left')
            ->join('subjects','subjects.id = subject_group_subjects.subject_id','left')
            ->join('staff','staff.id = tt_draft_entries.staff_id','left')
            ->join('tt_rooms','tt_rooms.id = tt_draft_entries.room_id','left')
            ->join('tt_batches','tt_batches.id = tt_draft_entries.batch_id','left')
            ->join('classes','classes.id = tt_draft_entries.class_id','left')
            ->join('sections','sections.id = tt_draft_entries.section_id','left')
            ->join('tt_periods','tt_periods.id = tt_draft_entries.period_id','left')
            ->where('tt_draft_entries.gen_log_id', $gen_log_id)
            ->order_by('classes.class','ASC')->order_by('sections.section','ASC')
            ->order_by('tt_draft_entries.day','ASC')->order_by('tt_periods.sort_order','ASC')
            ->get()->result();

        $grouped = [];
        foreach ($rows as $r) {
            $key = $r->class_id . '_' . $r->section_id;
            if (!isset($grouped[$key])) {
                $grouped[$key] = ['class' => $r->class_name, 'section' => $r->section_name, 'entries' => []];
            }
            $grouped[$key]['entries'][] = $r;
        }
        return $grouped;
    }
}
