<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * Auto Timetable Generator
 *
 * Algorithm: Greedy CSP with soft-constraint scoring.
 * Subjects picked most-constrained-first, dynamically: before every placement,
 * remaining loads are re-scored by (consecutive > periods/week > priority) plus
 * each candidate teacher's LIVE remaining weekly capacity, so a teacher who
 * fills up mid-pass becomes more urgent in real time rather than relying on
 * a one-time pre-sort.
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
        // Every field that _findBestSlot/_findJointSlot reads must be present here.
        $this->default_tc = (object)[
            'max_periods_per_day'     => 6,
            'max_periods_per_week'    => 36,
            'min_free_per_day'        => 0,    // min free periods teacher must have each day
            'max_gap_per_day'         => null, // null = no idle-gap limit
            'avoid_first_period'      => 0,
            'avoid_last_period'       => 0,
            'preferred_start_time'    => null, // null = no time-window restriction
            'preferred_end_time'      => null,
            'preferred_room_id'       => null,
            'max_consecutive_periods' => 0,    // 0 = no consecutive limit
            'min_break_after_consec'  => 1,
        ];

        $joint_peers = []; // built after $joint_lessons loaded below
        $joint_sgs = [];   // kept empty — deprecated, checks use $joint_peers now

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
        // Teachers without a constraint row still hit the default 36/week cap —
        // use that as their tightness so they're sorted correctly alongside
        // explicitly configured teachers.
        $default_tightness = max(0, 48 - (int)$this->default_tc->max_periods_per_week);

        // Teacher sharing breadth: how many distinct class-sections does each
        // teacher serve across the entire generation scope? A teacher spread
        // across 8+ classes (Hindi, Library, GK) is a scarce shared resource
        // whose slots must be reserved across ALL classes early, before other
        // subjects consume their availability unevenly.
        $teacher_class_count = [];
        foreach ($base_loads as $l) {
            foreach (($l->teacher_ids ?? []) as $tid) {
                $teacher_class_count[$tid] = ($teacher_class_count[$tid] ?? 0) + 1;
            }
        }

        usort($base_loads, function($a, $b) use ($teacher_unavail_count, $teacher_cap_tightness, $default_tightness, $teacher_class_count) {
            $ua = $uca = $sha = 0;
            foreach (($a->teacher_ids ?? []) as $tid) {
                $ua  = max($ua,  $teacher_unavail_count[$tid] ?? 0);
                $uca = max($uca, $teacher_cap_tightness[$tid] ?? $default_tightness);
                $sha = max($sha, $teacher_class_count[$tid] ?? 0);
            }
            $ub = $ucb = $shb = 0;
            foreach (($b->teacher_ids ?? []) as $tid) {
                $ub  = max($ub,  $teacher_unavail_count[$tid] ?? 0);
                $ucb = max($ucb, $teacher_cap_tightness[$tid] ?? $default_tightness);
                $shb = max($shb, $teacher_class_count[$tid] ?? 0);
            }
            $score_a = ($a->consecutive_periods * 10) + $a->periods_per_week + $a->priority
                     + ($ua * 0.5) + ($uca * 1.5) + ($sha * 3.0);
            $score_b = ($b->consecutive_periods * 10) + $b->periods_per_week + $b->priority
                     + ($ub * 0.5) + ($ucb * 1.5) + ($shb * 3.0);
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

        // Build joint_peers: [class_id][section_id][sgs_id] → all participating classes
        foreach ($joint_lessons as $jl) {
            $all_cs = [];
            foreach ($jl->classes as $cs) {
                $all_cs[] = ['class_id' => (int)$cs->class_id, 'section_id' => (int)$cs->section_id, 'sgs_id' => (int)$cs->sgs_id];
            }
            if (count($all_cs) > 1) {
                foreach ($all_cs as $c) {
                    $joint_peers[$c['class_id']][$c['section_id']][$c['sgs_id']] = $all_cs;
                }
            }
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
            // Dynamic most-constrained-first, same principle as the regular-subject
            // loop below: priority alone leaves ties (e.g. two priority-10 lessons
            // sharing the same six sections) broken by undefined SQL order, so
            // whichever happened to come back first got first claim on the shared
            // slots. Re-score remaining joint lessons before every pick using
            // class-count (more classes = harder), periods/week, and each
            // candidate teacher's LIVE remaining weekly capacity.
            $remaining_jl = $joint_lessons;
            while (!empty($remaining_jl)) {
                $pick_key = null; $pick_score = -INF;
                foreach ($remaining_jl as $rk => $cand) {
                    $cand_t_ids = $cand->teacher_ids ?? [];
                    $cand_ua = 0; $cand_dyn_tight = 0;
                    foreach ($cand_t_ids as $tid) {
                        $cand_ua = max($cand_ua, $teacher_unavail_count[$tid] ?? 0);
                        $cap = (int) (($constraints[$tid] ?? $this->default_tc)->max_periods_per_week ?? 0);
                        if (empty($cap)) $cap = (int) $this->default_tc->max_periods_per_week;
                        $remaining_cap  = max(0, $cap - ($this->teacher_periods_week[$tid] ?? 0));
                        $cand_dyn_tight = max($cand_dyn_tight, max(0, 48 - $remaining_cap));
                    }
                    $n_classes  = count($cand->classes ?? []);
                    $pool_size  = count($cand_t_ids);
                    $cand_consec = max(1, (int)$cand->consecutive_periods);
                    $cand_ppw = (int)$cand->periods_per_week;
                    $cand_placements = ($cand_consec > 1) ? (int)ceil($cand_ppw / $cand_consec) : $cand_ppw;

                    // Valid common slot counting: how many (day, period_group) slots
                    // have ALL sections free AND at least one teacher free?
                    // This is the true constraint — section-free slots where the
                    // teacher is busy are useless for this joint.
                    $cand_common_free = 0;
                    foreach ($this->working_days as $_d) {
                        foreach ($this->_getConsecutiveStarts($cand_consec) as $_pg) {
                            $all_free = true;
                            foreach ($cand->classes as $_cs) {
                                foreach ($_pg as $_p) {
                                    if (!empty($this->class_occ[(int)$_cs->class_id][(int)$_cs->section_id][$_d][$_p][0])
                                        || !empty($this->class_unavail[(int)$_cs->class_id][(int)$_cs->section_id][$_d][$_p])) {
                                        $all_free = false; break 2;
                                    }
                                }
                            }
                            if (!$all_free) continue;
                            // Also require at least one teacher to be free
                            $t_viable = empty($cand_t_ids);
                            foreach ($cand_t_ids as $_tid) {
                                $_tok = true;
                                foreach ($_pg as $_p) {
                                    if (!empty($this->teacher_occ[$_tid][$_d][$_p]) || !empty($unavail_map[$_tid][$_d][$_p])) {
                                        $_tok = false; break;
                                    }
                                }
                                if ($_tok) { $t_viable = true; break; }
                            }
                            if ($t_viable) $cand_common_free++;
                        }
                    }
                    // Shared teacher demand: sum placements from OTHER remaining joints
                    // that share this teacher — they compete for the same viable pool.
                    $shared_demand = $cand_placements;
                    foreach ($remaining_jl as $_ork => $_oj) {
                        if ($_ork === $rk) continue;
                        $_otids = $_oj->teacher_ids ?? [];
                        $_shares = false;
                        foreach ($_otids as $_ot) { if (in_array($_ot, $cand_t_ids)) { $_shares = true; break; } }
                        if ($_shares) {
                            $_oc = max(1, (int)$_oj->consecutive_periods);
                            $shared_demand += ($_oc > 1) ? (int)ceil((int)$_oj->periods_per_week / $_oc) : (int)$_oj->periods_per_week;
                        }
                    }
                    $cand_jl_urgency = 0;
                    if ($cand_common_free <= $shared_demand) $cand_jl_urgency = 500;
                    elseif ($cand_common_free <= (int)($shared_demand * 1.5)) $cand_jl_urgency = 250;
                    elseif ($cand_common_free <= $shared_demand * 2) $cand_jl_urgency = 100;
                    $cand_slot_scarcity = max(0, 60 - $cand_common_free);

                    $cand_score = ((int)$cand->priority * 100) + ($n_classes * 10)
                                + $cand_ppw + ((int)$cand->consecutive_periods * 5)
                                + ($cand_ua * 0.5) + ($cand_dyn_tight * 0.3)
                                - ($pool_size * 0.1)
                                + $cand_slot_scarcity + $cand_jl_urgency;
                    if ($cand_score > $pick_score) { $pick_score = $cand_score; $pick_key = $rk; }
                }
                $jl = $remaining_jl[$pick_key];
                unset($remaining_jl[$pick_key]);

                $jl_consec          = (int) $jl->consecutive_periods;
                $jl_ppw             = (int) $jl->periods_per_week;
                $jl_teacher_ids     = $jl->teacher_ids ?? [];
                $jl_all_req         = !empty($jl->all_teachers_required);
                $jl_room            = $jl->room_id ? (int) $jl->room_id : null;
                $jl_max_day         = max(1, (int) $jl->max_per_day);
                $jl_spread          = !empty($jl->distribute_evenly);
                $placements         = ($jl_consec > 1) ? (int) ceil($jl_ppw / $jl_consec) : $jl_ppw;

                // Fixed Slot(s): admin-pinned day+period(s) per placement index, bypassing full-week search
                $jl_fixed_map = [];
                if (!empty($jl->fixed_slots)) {
                    $decoded_fixed = json_decode($jl->fixed_slots, true);
                    if (is_array($decoded_fixed)) {
                        foreach ($decoded_fixed as $fs) {
                            if (isset($fs['placement'], $fs['day'], $fs['period_ids'])) {
                                $jl_fixed_map[(int) $fs['placement']] = [
                                    'day'        => $fs['day'],
                                    'period_ids' => array_map('intval', $fs['period_ids']),
                                ];
                            }
                        }
                    }
                }

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
                    $fixed = $jl_fixed_map[$p] ?? null;

                    $slot = $this->_findJointSlot($jl, $jl_teacher_ids, $jl_all_req, $jl_room,
                        $jl_consec, $jl_days_used, $jl_max_day, $jl_spread,
                        $constraints, $unavail_map,
                        $fixed['day'] ?? null, $fixed['period_ids'] ?? null);

                    if ($slot === null) {
                        $class_labels = implode('+', array_map(fn($cs) => "C{$cs->class_id}/S{$cs->section_id}", $jl->classes));
                        $teacher_label = !empty($jl_teacher_ids) ? count($jl_teacher_ids).' teacher(s)' : 'No teacher';
                        if ($fixed) {
                            $period_labels = implode('-', array_map(fn($pid) => $this->periods[$pid]->name ?? $pid, $fixed['period_ids']));
                            $why = $this->_diagnoseJointFixedFailure($jl, $jl_teacher_ids, $jl_all_req, $fixed['day'], $fixed['period_ids'], $constraints, $unavail_map, $jl_room);
                            $reason = "Joint lesson [{$jl->name}] for {$class_labels}: fixed slot {$fixed['day']} {$period_labels} is unavailable — {$why}";
                        } else {
                            $reason = "Joint lesson [{$jl->name}] for {$class_labels}: no slot where all classes are simultaneously free (even after trying displacement).";
                        }
                        $conflicts[] = [
                            'class_id'   => 0,
                            'section_id' => 0,
                            'subject'    => $jl->subject_name . ' (Joint)',
                            'staff'      => $teacher_label,
                            'placement'  => ($p + 1) . ' of ' . $placements,
                            'reason'     => $reason,
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
                                'is_free_period'           => 0,
                                'free_period_label'        => null,
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
            // ---- JOINT BACKTRACKING ----
            // If any joint failed, identify the conflict cluster (failed joints +
            // joints sharing their sections/teacher), undo them, and re-place
            // using backtracking to guarantee finding a valid assignment if one exists.
            $jl_failures = array_filter($conflicts, fn($c) => empty($c['type']) && strpos($c['reason'] ?? '', 'Joint lesson') === 0);
            if (!empty($jl_failures)) {
                $bt_resolved = $this->_backtrackJointCluster(
                    $joint_lessons, $constraints, $unavail_map,
                    $log_id, $session_id, $draft_entries, $conflicts,
                    $total_required, $total_placed, $class_stats
                );
            }
            // ---- END JOINT LESSON PRE-PASS ----

            // ---- BOTTLENECK TEACHER TAGGING ----
            // Teachers shared across 5+ classes or ≥75% capacity utilization get
            // their loads tagged with a priority boost AND interleaved by class
            // for round-robin distribution. This prevents one class from exhausting
            // a shared teacher's slots before other classes get their turn.
            $load_by_teacher = [];
            foreach ($loads as $lk => $load) {
                $t_ids = $load->teacher_ids ?? [];
                if (empty($t_ids)) {
                    if (!empty($load->staff_id))     $t_ids[] = (int) $load->staff_id;
                    if (!empty($load->alt_staff_id)) $t_ids[] = (int) $load->alt_staff_id;
                }
                foreach ($t_ids as $tid) {
                    $load_by_teacher[$tid][$lk] = true;
                }
            }
            $bn_load_keys = [];
            foreach ($load_by_teacher as $tid => $lk_map) {
                $n_classes = count(array_unique(array_map(fn($lk) => $loads[$lk]->class_id . '_' . $loads[$lk]->section_id, array_keys($lk_map))));
                $cap = (int)(($constraints[$tid] ?? $this->default_tc)->max_periods_per_week ?? 0);
                if (empty($cap)) $cap = (int) $this->default_tc->max_periods_per_week;
                $t_ppw = 0;
                foreach (array_keys($lk_map) as $lk) $t_ppw += (int) $loads[$lk]->periods_per_week;
                if ($n_classes >= 5 || ($cap > 0 && $t_ppw / $cap >= 0.75)) {
                    $boost = ($n_classes >= 5) ? 80 : 50;
                    foreach (array_keys($lk_map) as $lk) {
                        $loads[$lk]->_bn_boost = max($loads[$lk]->_bn_boost ?? 0, $boost);
                        $bn_load_keys[$lk] = true;
                    }
                }
            }
            // Interleave bottleneck loads by class for round-robin distribution,
            // then append non-bottleneck loads after them.
            if (!empty($bn_load_keys)) {
                $bn_by_class = []; $non_bn = [];
                foreach ($loads as $lk => $load) {
                    if (isset($bn_load_keys[$lk])) {
                        $bn_by_class[$load->class_id . '_' . $load->section_id][] = $load;
                    } else {
                        $non_bn[] = $load;
                    }
                }
                $interleaved = [];
                $max_len = empty($bn_by_class) ? 0 : max(array_map('count', $bn_by_class));
                for ($i = 0; $i < $max_len; $i++) {
                    foreach ($bn_by_class as $items) {
                        if (isset($items[$i])) $interleaved[] = $items[$i];
                    }
                }
                $loads = array_merge($interleaved, $non_bn);
            }
            // ---- END BOTTLENECK TEACHER TAGGING ----

            // Dynamic most-constrained-first selection: re-score remaining loads
            // before every pick using each teacher's LIVE remaining weekly capacity
            // (cap minus periods already placed this pass), not just their static cap.
            // A teacher who has filled up from earlier picks this pass becomes more
            // urgent in real time, instead of relying on a one-time pre-sort.
            $remaining = $loads;
            while (!empty($remaining)) {
                $pick_key = null; $pick_score = -INF;
                foreach ($remaining as $rk => $cand) {
                    $cand_t_ids = $cand->teacher_ids ?? [];
                    if (empty($cand_t_ids)) {
                        if (!empty($cand->staff_id))     $cand_t_ids[] = (int) $cand->staff_id;
                        if (!empty($cand->alt_staff_id)) $cand_t_ids[] = (int) $cand->alt_staff_id;
                    }
                    $cand_ua = 0; $cand_dyn_tight = 0;
                    foreach ($cand_t_ids as $tid) {
                        $cand_ua = max($cand_ua, $teacher_unavail_count[$tid] ?? 0);
                        $cap = (int) (($constraints[$tid] ?? $this->default_tc)->max_periods_per_week ?? 0);
                        if (empty($cap)) $cap = (int) $this->default_tc->max_periods_per_week;
                        $remaining_cap  = max(0, $cap - ($this->teacher_periods_week[$tid] ?? 0));
                        $cand_dyn_tight = max($cand_dyn_tight, max(0, 48 - $remaining_cap));
                    }
                    $cand_sharing = 0;
                    foreach ($cand_t_ids as $tid) {
                        $cand_sharing = max($cand_sharing, $teacher_class_count[$tid] ?? 0);
                    }
                    // Valid slot counting: actual number of slots this load can go
                    // into RIGHT NOW. The true measure of constraint difficulty.
                    $c_cid = (int)$cand->class_id; $c_sid = (int)$cand->section_id;
                    $cand_valid = 0;
                    foreach ($this->working_days as $_d) {
                        foreach ($this->period_order as $_p) {
                            if (!empty($this->class_occ[$c_cid][$c_sid][$_d][$_p][0])) continue;
                            if (!empty($this->class_unavail[$c_cid][$c_sid][$_d][$_p])) continue;
                            foreach ($cand_t_ids as $tid) {
                                if (empty($this->teacher_occ[$tid][$_d][$_p]) && empty($unavail_map[$tid][$_d][$_p])) {
                                    $cand_valid++;
                                    break;
                                }
                            }
                        }
                    }
                    $cand_ppw = (int) $cand->periods_per_week;
                    $cand_urgency = 0;
                    if ($cand_valid <= $cand_ppw) $cand_urgency = 1000;
                    elseif ($cand_valid <= $cand_ppw * 2) $cand_urgency = 500;
                    elseif ($cand_valid <= $cand_ppw * 3) $cand_urgency = 200;
                    $cand_slot_tight = max(0, 50 - $cand_valid);
                    $cand_bn = $cand->_bn_boost ?? 0;
                    $cand_score = ($cand->consecutive_periods * 10) + $cand->periods_per_week + $cand->priority
                                + ($cand_ua * 0.5) + ($cand_dyn_tight * 1.5) + ($cand_sharing * 3.0)
                                + $cand_slot_tight + $cand_urgency + $cand_bn;
                    if ($cand_score > $pick_score) { $pick_score = $cand_score; $pick_key = $rk; }
                }
                $load = $remaining[$pick_key];
                unset($remaining[$pick_key]);

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
                if ($min_per_day && $periods_pw < count($this->working_days)) $min_per_day = 0;
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
                            'class_id'                 => $class_id,
                            'section_id'               => $section_id,
                            'subject'                  => $load->subject_name . ' (' . ($load->subject_code ?? '') . ')',
                            'staff'                    => $load->staff_name . ' ' . $load->staff_surname,
                            'staff_id'                 => $teacher_ids_load[0] ?? null,
                            'subject_group_subject_id' => $sgs_id,
                            'subject_group_id'         => (int)$load->subject_group_id,
                            'placement'                => ($p + 1) . ' of ' . $placements_needed,
                            'reason'                   => $this->_diagnoseFailure($class_id, $section_id, $batch_key, $teacher_ids_load, $constraints),
                            'type'                     => 'no_slot',
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
                            'is_free_period'           => 0,
                            'free_period_label'        => null,
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

                // On1 check — only report when min_per_day is still active
                // (auto-suppressed above when periods_per_week < working_days)
                if ($min_per_day && $placed_count > 0) {
                    $days_covered  = array_unique($subject_days_used);
                    $missing_days  = array_diff($this->working_days, $days_covered);
                    foreach ($missing_days as $md) {
                        $conflicts[] = [
                            'class_id'   => $class_id,
                            'section_id' => $section_id,
                            'subject'    => $load->subject_name . ' (' . ($load->subject_code ?? '') . ')',
                            'staff'      => $load->staff_name . ' ' . $load->staff_surname,
                            'staff_id'   => $teacher_ids_load[0] ?? null,
                            'placement'  => 'On1',
                            'reason'     => "On1 warning: could not place {$load->subject_name} on {$md} — teacher or class unavailable that day.",
                            'type'       => 'on1',
                        ];
                    }
                }
            }

            // ---- SIMULATED ANNEALING (if enabled) ----
            if (!empty($settings['use_sa']) || $gen_size === 'huge') {
                $sa_placed = $this->_runSA($draft_entries, $conflicts,
                    $base_loads, $constraints, $unavail_map, $joint_peers,
                    $log_id, $session_id, $settings, $total_placed, $class_stats);
                $total_placed += $sa_placed;
            }

            // ---- SWAP REPAIR PASS ----
            // For each no_slot conflict, try to resolve it by finding a slot
            // where the TEACHER is free but the CLASS is occupied by entry E,
            // then check if E can be moved to a different slot where both E's
            // teacher AND the class are free. If so, swap E out and place the
            // unfilled subject in E's old slot. This is the "Local Search"
            // component that the greedy algorithm lacks.
            $swap_resolved = 0;
            $new_conflicts = [];
            foreach ($conflicts as $ci => $conf) {
                if (($conf['type'] ?? '') !== 'no_slot') { $new_conflicts[] = $conf; continue; }
                $c_id = (int) ($conf['class_id'] ?? 0);
                $s_id = (int) ($conf['section_id'] ?? 0);
                $t_id = (int) ($conf['staff_id'] ?? 0);
                if (!$c_id || !$s_id || !$t_id) { $new_conflicts[] = $conf; continue; }

                $resolved = false;
                foreach ($this->working_days as $day) {
                    if ($resolved) break;
                    foreach ($this->period_order as $pid) {
                        if ($resolved) break;
                        // Teacher must be free at this slot
                        if (!empty($this->teacher_occ[$t_id][$day][$pid])) continue;
                        if (!empty($unavail_map[$t_id][$day][$pid])) continue;
                        // Class must be occupied (we want to swap the occupant out)
                        if (empty($this->class_occ[$c_id][$s_id][$day][$pid][0])) continue;

                        // Find the draft entry occupying this cell
                        $blocker_idx = null;
                        foreach ($draft_entries as $di => $de) {
                            if ((int)$de['class_id'] === $c_id && (int)$de['section_id'] === $s_id
                                && $de['day'] === $day && (int)$de['period_id'] === $pid
                                && empty($de['is_free_period'])) {
                                $blocker_idx = $di; break;
                            }
                        }
                        if ($blocker_idx === null) continue;
                        $blocker = $draft_entries[$blocker_idx];
                        $b_tid = (int) ($blocker['staff_id'] ?? 0);
                        if (!$b_tid) continue;

                        // Can the blocker move to a different empty slot?
                        $b_sgs_id = (int)($blocker['subject_group_subject_id'] ?? 0);
                        $b_jp = $joint_peers[$c_id][$s_id][$b_sgs_id] ?? null;

                        foreach ($this->working_days as $day2) {
                            if ($resolved) break;
                            foreach ($this->period_order as $pid2) {
                                if ($day2 === $day && $pid2 === $pid) continue;
                                if (!empty($this->class_occ[$c_id][$s_id][$day2][$pid2][0])) continue;
                                if (!empty($this->class_unavail[$c_id][$s_id][$day2][$pid2])) continue;
                                if (!empty($this->teacher_occ[$b_tid][$day2][$pid2])) continue;
                                if (!empty($unavail_map[$b_tid][$day2][$pid2])) continue;

                                // Joint sync: check all peer classes have (day2,pid2) free
                                if ($b_jp) {
                                    $jp_ok = true;
                                    foreach ($b_jp as $p) {
                                        if ($p['class_id'] === $c_id && $p['section_id'] === $s_id) continue;
                                        if (!empty($this->class_occ[$p['class_id']][$p['section_id']][$day2][$pid2][0])) { $jp_ok = false; break; }
                                    }
                                    if (!$jp_ok) continue;
                                }

                                // Move blocker to (day2, pid2)
                                $draft_entries[$blocker_idx]['day']       = $day2;
                                $draft_entries[$blocker_idx]['period_id'] = $pid2;
                                unset($this->class_occ[$c_id][$s_id][$day][$pid][0]);
                                $this->class_occ[$c_id][$s_id][$day2][$pid2][0] = true;
                                unset($this->teacher_occ[$b_tid][$day][$pid]);
                                $this->teacher_occ[$b_tid][$day2][$pid2] = true;
                                $this->teacher_periods_day[$b_tid][$day]  = max(0, ($this->teacher_periods_day[$b_tid][$day] ?? 1) - 1);
                                $this->teacher_periods_day[$b_tid][$day2] = ($this->teacher_periods_day[$b_tid][$day2] ?? 0) + 1;

                                // Move joint peers
                                if ($b_jp) {
                                    foreach ($b_jp as $p) {
                                        if ($p['class_id'] === $c_id && $p['section_id'] === $s_id) continue;
                                        foreach ($draft_entries as $pdi => $pde) {
                                            if ((int)$pde['class_id'] === $p['class_id'] && (int)$pde['section_id'] === $p['section_id']
                                                && $pde['day'] === $day && (int)$pde['period_id'] === $pid
                                                && (int)($pde['subject_group_subject_id'] ?? 0) === $p['sgs_id']) {
                                                $draft_entries[$pdi]['day'] = $day2;
                                                $draft_entries[$pdi]['period_id'] = $pid2;
                                                unset($this->class_occ[$p['class_id']][$p['section_id']][$day][$pid][0]);
                                                $this->class_occ[$p['class_id']][$p['section_id']][$day2][$pid2][0] = true;
                                                break;
                                            }
                                        }
                                    }
                                }

                                $conf_sgs  = (int) ($conf['subject_group_subject_id'] ?? 0);
                                $conf_sgid = (int) ($conf['subject_group_id'] ?? 0);

                                // Place unfilled subject at (day, pid)
                                $draft_entries[] = [
                                    'gen_log_id'               => $log_id,
                                    'session_id'               => $session_id,
                                    'class_id'                 => $c_id,
                                    'section_id'               => $s_id,
                                    'subject_group_id'         => $conf_sgid,
                                    'subject_group_subject_id' => $conf_sgs,
                                    'staff_id'                 => $t_id,
                                    'period_id'                => $pid,
                                    'day'                      => $day,
                                    'room_id'                  => null,
                                    'batch_id'                 => null,
                                    'is_free_period'           => 0,
                                    'free_period_label'        => null,
                                ];
                                $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                                $this->teacher_occ[$t_id][$day][$pid] = true;
                                $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                $total_placed++;
                                $swap_resolved++;
                                $resolved = true;
                                break;
                            }
                        }
                    }
                }
                if (!$resolved) $new_conflicts[] = $conf;
            }
            $conflicts = $new_conflicts;

            // ---- CASCADING REPAIR LOOP ----
            // Run cross-class swap + teacher alignment in a loop — each repair
            // changes the landscape, enabling swaps that weren't possible before.
            for ($repair_round = 0; $repair_round < 3; $repair_round++) {
                $round_resolved = 0;

                $cross_swaps = $this->_crossClassSwapRepair(
                    $draft_entries, $conflicts, $constraints, $unavail_map,
                    $log_id, $session_id
                );
                $total_placed  += $cross_swaps;
                $swap_resolved += $cross_swaps;
                $round_resolved += $cross_swaps;

                $align_resolved = $this->_teacherAlignmentRepair(
                    $draft_entries, $conflicts, $constraints, $unavail_map,
                    $log_id, $session_id
                );
                $total_placed  += $align_resolved;
                $swap_resolved += $align_resolved;
                $round_resolved += $align_resolved;

                if ($round_resolved === 0) break;
            }

            // ---- LAST-RESORT EXHAUSTIVE SEARCH ----
            // If ≤3 no_slot conflicts remain, run a brute-force search for each:
            // try EVERY class-free slot, and for each, try swapping ANY entry in
            // the teacher's other classes to free the teacher at that slot.
            $final_no_slot = array_filter($conflicts, fn($c) => ($c['type'] ?? '') === 'no_slot');
            if (count($final_no_slot) > 0 && count($final_no_slot) <= 3) {
                $t_idx_final = [];
                foreach ($draft_entries as $di => $de) {
                    if (empty($de['staff_id']) || !empty($de['is_free_period'])) continue;
                    $t_idx_final[(int)$de['staff_id']][$de['day']][(int)$de['period_id']] = $di;
                }

                $new_conflicts = [];
                foreach ($conflicts as $conf) {
                    if (($conf['type'] ?? '') !== 'no_slot') { $new_conflicts[] = $conf; continue; }
                    $c_id = (int)($conf['class_id'] ?? 0);
                    $s_id = (int)($conf['section_id'] ?? 0);
                    $t_id = (int)($conf['staff_id'] ?? 0);
                    $conf_sgs = (int)($conf['subject_group_subject_id'] ?? 0);
                    $conf_sgid = (int)($conf['subject_group_id'] ?? 0);
                    if (!$c_id || !$s_id || !$t_id) { $new_conflicts[] = $conf; continue; }

                    $placed = false;
                    // For each class-free slot where teacher is busy:
                    foreach ($this->working_days as $day) {
                        if ($placed) break;
                        foreach ($this->period_order as $pid) {
                            if ($placed) break;
                            if (!empty($this->class_occ[$c_id][$s_id][$day][$pid][0])) continue;
                            if (!empty($this->class_unavail[$c_id][$s_id][$day][$pid])) continue;
                            if (empty($this->teacher_occ[$t_id][$day][$pid])) {
                                // Teacher is free here! Direct placement.
                                if (!empty($unavail_map[$t_id][$day][$pid])) continue;
                                $draft_entries[] = [
                                    'gen_log_id' => $log_id, 'session_id' => $session_id,
                                    'class_id' => $c_id, 'section_id' => $s_id,
                                    'subject_group_id' => $conf_sgid,
                                    'subject_group_subject_id' => $conf_sgs,
                                    'staff_id' => $t_id, 'period_id' => $pid,
                                    'day' => $day, 'room_id' => null,
                                    'batch_id' => null, 'is_free_period' => 0,
                                    'free_period_label' => null,
                                ];
                                $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                                $this->teacher_occ[$t_id][$day][$pid] = true;
                                $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                                $total_placed++; $placed = true; break;
                            }
                            if (!empty($unavail_map[$t_id][$day][$pid])) continue;

                            // Teacher busy — try to free via 2-class chain
                            $bi = $t_idx_final[$t_id][$day][$pid] ?? null;
                            if ($bi === null) continue;
                            $bl = $draft_entries[$bi];
                            if (!empty($bl['is_locked'])) continue;
                            $bc = (int)$bl['class_id']; $bs = (int)$bl['section_id'];

                            // Try ALL possible destinations for the blocker
                            foreach ($this->working_days as $d2) {
                                if ($placed) break;
                                foreach ($this->period_order as $p2) {
                                    if ($d2 === $day && $p2 === $pid) continue;
                                    if (!empty($this->class_occ[$bc][$bs][$d2][$p2][0])) continue;
                                    if (!empty($this->class_unavail[$bc][$bs][$d2][$p2])) continue;
                                    if (!empty($this->teacher_occ[$t_id][$d2][$p2])) continue;
                                    if (!empty($unavail_map[$t_id][$d2][$p2])) continue;

                                    // Move blocker, place conflict subject
                                    $draft_entries[$bi]['day'] = $d2; $draft_entries[$bi]['period_id'] = $p2;
                                    unset($this->class_occ[$bc][$bs][$day][$pid][0]);
                                    $this->class_occ[$bc][$bs][$d2][$p2][0] = true;
                                    unset($this->teacher_occ[$t_id][$day][$pid]);
                                    $this->teacher_occ[$t_id][$d2][$p2] = true;

                                    $draft_entries[] = [
                                        'gen_log_id' => $log_id, 'session_id' => $session_id,
                                        'class_id' => $c_id, 'section_id' => $s_id,
                                        'subject_group_id' => $conf_sgid,
                                        'subject_group_subject_id' => $conf_sgs,
                                        'staff_id' => $t_id, 'period_id' => $pid,
                                        'day' => $day, 'room_id' => null,
                                        'batch_id' => null, 'is_free_period' => 0,
                                        'free_period_label' => null,
                                    ];
                                    $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                                    $this->teacher_occ[$t_id][$day][$pid] = true;
                                    $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                    $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                                    $total_placed++; $placed = true; break;
                                }
                            }
                        }
                    }
                    if (!$placed) $new_conflicts[] = $conf;
                }
                $conflicts = $new_conflicts;
            }

            // ---- GAP FILL (opt-in via fill_free_periods) ----
            $gap_filled_subject = 0; $gap_filled_free = 0;
            if (!empty($settings['fill_free_periods'])) {
                $this->_fillEmptyCells($class_stats, $base_loads, $constraints, $unavail_map,
                    $log_id, $session_id, $draft_entries, $gap_filled_subject, $gap_filled_free);
            }

            // ---- POST-GAP-FILL REPAIR ----
            // After gap-fill adds Free Period placeholders, those slots are
            // "occupied" and the swap chain can't use them. Remove Free Periods
            // from occupancy, re-run the swap chain on remaining conflicts,
            // then delete any Free Period entries that were replaced.
            $remaining_no_slot = array_filter($conflicts, fn($c) => ($c['type'] ?? '') === 'no_slot');
            if (!empty($remaining_no_slot) && !empty($settings['fill_free_periods'])) {
                // Index Free Period entries and unmark from class_occ
                $fp_indices = [];
                foreach ($draft_entries as $di => $de) {
                    if (!empty($de['is_free_period'])) {
                        $fc = (int)$de['class_id']; $fs = (int)$de['section_id'];
                        unset($this->class_occ[$fc][$fs][$de['day']][(int)$de['period_id']][0]);
                        $fp_indices[$fc . '_' . $fs . '_' . $de['day'] . '_' . $de['period_id']] = $di;
                    }
                }

                // Rebuild indexes for swap chain
                $teacher_idx2 = []; $class_idx2 = [];
                foreach ($draft_entries as $di => $de) {
                    if (empty($de['staff_id']) || !empty($de['is_free_period'])) continue;
                    $teacher_idx2[(int)$de['staff_id']][$de['day']][(int)$de['period_id']] = $di;
                    $class_idx2[(int)$de['class_id']][(int)$de['section_id']][$de['day']][(int)$de['period_id']] = $di;
                }

                $post_swaps = $this->_crossClassSwapRepair(
                    $draft_entries, $conflicts, $constraints, $unavail_map,
                    $log_id, $session_id
                );
                $total_placed += $post_swaps;

                // Remove Free Period entries that were replaced by real subjects
                foreach ($draft_entries as $di => $de) {
                    if (empty($de['is_free_period'])) continue;
                    $fc = (int)$de['class_id']; $fs = (int)$de['section_id'];
                    if (!empty($this->class_occ[$fc][$fs][$de['day']][(int)$de['period_id']][0])) {
                        unset($draft_entries[$di]);
                        $gap_filled_free--;
                    }
                }
                $draft_entries = array_values($draft_entries);

                // Re-mark remaining Free Periods in class_occ
                foreach ($draft_entries as $de) {
                    if (!empty($de['is_free_period'])) {
                        $this->class_occ[(int)$de['class_id']][(int)$de['section_id']][$de['day']][(int)$de['period_id']][0] = true;
                    }
                }
            }

            if ($total_placed > $best_placed) {
                $best_placed = $total_placed;
                $best_result = compact('draft_entries', 'conflicts', 'total_required', 'total_placed', 'class_stats',
                    'gap_filled_subject', 'gap_filled_free');
            }

            if ($total_placed === $total_required) break; // perfect — no need for more passes
        }

        $total_required     = $best_result['total_required'];
        $total_placed       = $best_result['total_placed'];
        $draft_entries      = $best_result['draft_entries'];
        $conflicts          = $best_result['conflicts'];
        $class_stats        = array_values($best_result['class_stats'] ?? []);
        $gap_filled_subject = $best_result['gap_filled_subject'] ?? 0;
        $gap_filled_free    = $best_result['gap_filled_free'] ?? 0;

        // Recompute total_placed from actual failures to fix any incremental drift
        $real_fail_count = count(array_filter($conflicts, fn($c) => ($c['type'] ?? '') === 'no_slot' || (empty($c['type']) && !empty($c['reason']))));
        $corrected_placed = $total_required - $real_fail_count;
        if ($corrected_placed > $total_placed) $total_placed = $corrected_placed;
        $quality = ($total_required > 0) ? round(($total_placed / $total_required) * 100, 2) : 100.00;

        if (!$dry_run) {
            if (!empty($draft_entries)) {
                $clean = array_map(function($d) { unset($d['is_locked']); return $d; }, $draft_entries);
                $this->db->insert_batch('tt_draft_entries', $clean);
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

        $real_conflicts = array_filter($conflicts, fn($c) => ($c['type'] ?? '') !== 'on1');
        $on1_warnings   = array_filter($conflicts, fn($c) => ($c['type'] ?? '') === 'on1');

        return [
            'status'          => '1',
            'log_id'          => $log_id,
            'total_required'  => $total_required,
            'total_placed'    => $total_placed,
            'cards_placed'    => $total_placed,
            'cards_left'      => $total_required - $total_placed,
            'total_conflicts' => count($real_conflicts),
            'quality_score'   => $quality,
            'conflicts'       => array_values(array_merge($real_conflicts, $on1_warnings)),
            'class_stats'     => $class_stats,
            'dry_run'         => $dry_run,
            'gap_filled_subject' => $gap_filled_subject,
            'gap_filled_free'    => $gap_filled_free,
        ];
    }

    /**
     * Gap-fill pass: for every genuinely empty cell in each class's grid,
     * try to place an extra occurrence of an already-configured subject
     * whose teacher happens to be free there (prioritizing subjects that
     * still have unmet periods_per_week). If nothing fits, insert a
     * generic "Free Period" placeholder so the cell is never blank.
     */
    private function _fillEmptyCells($class_stats, array $base_loads, $constraints, $unavail_map,
                                      $log_id, $session_id, array &$draft_entries, &$filled_subject, &$filled_free,
                                      array &$diagnostics = [], array $existing_counts = [])
    {
        $loads_by_class = [];
        foreach ($base_loads as $l) {
            $ck = $l->class_id . '_' . $l->section_id;
            $loads_by_class[$ck][] = $l;
        }

        // Start with pre-existing counts (from tt_entries when called via the
        // live "Fill Empty Cells" button; empty when called during generation
        // since $draft_entries already contains everything placed so far).
        $placed_per_load = $existing_counts;
        foreach ($draft_entries as $de) {
            if (empty($de['subject_group_subject_id'])) continue;
            $k = $de['class_id'] . '_' . $de['section_id'] . '_' . $de['subject_group_subject_id'];
            $placed_per_load[$k] = ($placed_per_load[$k] ?? 0) + 1;
        }

        // ── PHASE 1: GLOBAL subject-first fill across ALL classes ─────
        // Build ONE queue of (class+subject) pairs across every class, sorted
        // globally by fewest-periods-needed first → smallest teacher pool.
        // This ensures a 1/week subject (Hindi, Library) across ALL classes
        // gets its teacher slots reserved before a 7/week subject in ANY class
        // consumes that teacher's remaining availability.
        $global_queue = [];
        foreach (array_keys($class_stats) as $ck) {
            $loads = $loads_by_class[$ck] ?? [];
            foreach ($loads as $load) {
                $sgs_id  = (int) $load->subject_group_subject_id;
                $already = $placed_per_load[$ck . '_' . $sgs_id] ?? 0;
                $needed  = (int) $load->periods_per_week - $already;
                if ($needed <= 0) continue;
                $t_ids = $load->teacher_ids ?? [];
                if (empty($t_ids)) {
                    if (!empty($load->staff_id))     $t_ids[] = (int) $load->staff_id;
                    if (!empty($load->alt_staff_id)) $t_ids[] = (int) $load->alt_staff_id;
                }
                $global_queue[] = [
                    'ck' => $ck, 'load' => $load, 'needed' => $needed,
                    'sgs_id' => $sgs_id, 't_ids' => $t_ids,
                ];
            }
        }
        usort($global_queue, function ($a, $b) {
            if ($a['needed'] !== $b['needed']) return $a['needed'] <=> $b['needed'];
            return count($a['t_ids']) <=> count($b['t_ids']);
        });

        foreach ($global_queue as $item) {
            $ck      = $item['ck'];
            [$class_id, $section_id] = array_map('intval', explode('_', $ck));
            $load    = $item['load'];
            $sgs_id  = $item['sgs_id'];
            $t_ids   = $item['t_ids'];
            $all_req = !empty($load->all_teachers_required);
            if (empty($t_ids)) continue;

            $remaining = max(0, (int)$load->periods_per_week - ($placed_per_load[$ck . '_' . $sgs_id] ?? 0));
            foreach ($this->working_days as $day) {
                if ($remaining <= 0) break;
                $max_per_day = (int) ($load->max_per_day ?? 2);
                if (($this->subject_day_count[$class_id][$section_id][$sgs_id][$day] ?? 0) >= $max_per_day) continue;

                foreach ($this->period_order as $pid) {
                    if ($remaining <= 0) break;
                    if (($this->subject_day_count[$class_id][$section_id][$sgs_id][$day] ?? 0) >= $max_per_day) break;
                    if (!empty($this->class_occ[$class_id][$section_id][$day][$pid][0])) continue;
                    if (!empty($this->class_unavail[$class_id][$section_id][$day][$pid])) continue;
                    if (!empty($this->subject_unavail[$sgs_id][$day][$pid])) continue;

                    $free_teacher = null;
                    $all_free = true;
                    foreach ($t_ids as $t_id) {
                        $why = $this->_diagnoseTeacherAtSlot($t_id, $day, [$pid], $constraints, $unavail_map);
                        if ($why === null) {
                            if ($free_teacher === null) $free_teacher = $t_id;
                            if (!$all_req) break;
                        } else {
                            if ($all_req) { $all_free = false; break; }
                        }
                    }
                    if ($all_req && !$all_free) continue;
                    if (!$all_req && $free_teacher === null) continue;

                    $assigned = $all_req ? $t_ids[0] : $free_teacher;
                    $pref_room = !empty($load->preferred_room_id) ? (int)$load->preferred_room_id : null;
                    $room_id = $this->_findRoom($day, [$pid], $load->preferred_room_type ?? 'any',
                        $pref_room, $assigned, $constraints[$assigned] ?? $this->default_tc);

                    $draft_entries[] = [
                        'gen_log_id'               => $log_id,
                        'session_id'               => $session_id,
                        'class_id'                 => $class_id,
                        'section_id'               => $section_id,
                        'subject_group_id'         => (int) $load->subject_group_id,
                        'subject_group_subject_id' => $sgs_id,
                        'staff_id'                 => $assigned,
                        'period_id'                => $pid,
                        'day'                      => $day,
                        'room_id'                  => $room_id,
                        'batch_id'                 => null,
                        'is_free_period'           => 0,
                        'free_period_label'        => null,
                    ];

                    $this->class_occ[$class_id][$section_id][$day][$pid][0] = true;
                    $teachers_to_mark = $all_req ? $t_ids : [$assigned];
                    foreach ($teachers_to_mark as $t_id) {
                        $this->teacher_occ[$t_id][$day][$pid] = true;
                        $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                        $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                    }
                    if ($room_id) {
                        $this->room_occ[$room_id][$day][$pid] = ($this->room_occ[$room_id][$day][$pid] ?? 0) + 1;
                    }
                    $this->subject_day_count[$class_id][$section_id][$sgs_id][$day] =
                        ($this->subject_day_count[$class_id][$section_id][$sgs_id][$day] ?? 0) + 1;
                    $placed_per_load[$ck . '_' . $sgs_id] = ($placed_per_load[$ck . '_' . $sgs_id] ?? 0) + 1;
                    $remaining--;
                    $filled_subject++;
                }
            }
        }

        // ── PHASE 2: Free Period placeholders for truly unfillable cells ──
        foreach (array_keys($class_stats) as $ck) {
            [$class_id, $section_id] = array_map('intval', explode('_', $ck));
            $loads = $loads_by_class[$ck] ?? [];

            // ── Free Period placeholders for truly unfillable cells ──
            foreach ($this->working_days as $day) {
                foreach ($this->period_order as $pid) {
                    if (!empty($this->class_occ[$class_id][$section_id][$day][$pid][0])) continue;
                    if (!empty($this->class_unavail[$class_id][$section_id][$day][$pid])) continue;

                    $period_name = $this->periods[$pid]->name ?? "P{$pid}";
                    $slot_reasons = [];
                    foreach ($loads as $load) {
                        $sgs_id = (int) $load->subject_group_subject_id;
                        $subj_label = $load->subject_name ?? "sgs#{$sgs_id}";
                        $already = $placed_per_load[$ck . '_' . $sgs_id] ?? 0;
                        if ($already >= (int) $load->periods_per_week) {
                            $slot_reasons[] = "{$subj_label}: at {$already}/{$load->periods_per_week} weekly cap";
                            continue;
                        }
                        $t_ids = $load->teacher_ids ?? [];
                        if (empty($t_ids)) {
                            if (!empty($load->staff_id))     $t_ids[] = (int) $load->staff_id;
                            if (!empty($load->alt_staff_id)) $t_ids[] = (int) $load->alt_staff_id;
                        }
                        foreach ($t_ids as $t_id) {
                            $why = $this->_diagnoseTeacherAtSlot($t_id, $day, [$pid], $constraints, $unavail_map);
                            if ($why !== null) $slot_reasons[] = "{$subj_label} / " . $this->_staffName($t_id) . ": {$why}";
                        }
                    }
                    $diagnostics[] = ['slot' => "{$day} {$period_name}", 'reasons' => $slot_reasons];

                    $draft_entries[] = [
                        'gen_log_id'               => $log_id,
                        'session_id'               => $session_id,
                        'class_id'                 => $class_id,
                        'section_id'               => $section_id,
                        'subject_group_id'         => null,
                        'subject_group_subject_id' => null,
                        'staff_id'                 => null,
                        'period_id'                => $pid,
                        'day'                      => $day,
                        'room_id'                  => null,
                        'batch_id'                 => null,
                        'is_free_period'           => 1,
                        'free_period_label'        => 'Free Period',
                    ];
                    $this->class_occ[$class_id][$section_id][$day][$pid][0] = true;
                    $filled_free++;
                }
            }
        }
    }

    /**
     * Simulated Annealing optimizer.
     * Starts from the greedy result and iteratively improves by random moves.
     */
    private function _runSA(array &$draft_entries, array &$conflicts,
        $base_loads, $constraints, $unavail_map, $joint_peers,
        $log_id, $session_id, $settings, &$total_placed, &$class_stats)
    {
        $sa_gen_size = $settings['gen_size'] ?? 'normal';
        $max_iter  = (int)($settings['sa_iterations'] ?? ($sa_gen_size === 'huge' ? 50000 : 20000));
        $temp      = 100.0;
        $cool_rate = ($sa_gen_size === 'huge') ? 0.99975 : 0.9995;
        $min_temp  = 0.1;

        // Build grid: [ck][sk] => draft_entries index (only real entries)
        $grid = [];
        foreach ($draft_entries as $di => $de) {
            if (!empty($de['is_free_period'])) continue;
            $grid[$de['class_id'] . '_' . $de['section_id']][$de['day'] . '_' . $de['period_id']] = $di;
        }

        // Build unplaced list from no_slot conflicts
        $unplaced = [];
        foreach ($conflicts as $conf) {
            if (($conf['type'] ?? '') !== 'no_slot') continue;
            $unplaced[] = [
                'class_id' => (int)($conf['class_id'] ?? 0), 'section_id' => (int)($conf['section_id'] ?? 0),
                'staff_id' => (int)($conf['staff_id'] ?? 0), 'sgs_id' => (int)($conf['subject_group_subject_id'] ?? 0),
                'sg_id' => (int)($conf['subject_group_id'] ?? 0),
            ];
        }
        if (empty($unplaced)) return 0;

        $initial_unplaced = count($unplaced);
        $all_slots = [];
        foreach ($this->working_days as $d) {
            foreach ($this->period_order as $p) $all_slots[] = $d . '_' . $p;
        }

        // Build list of teachers who appear in 2+ different classes (for cross-class swaps)
        $_t_ck_set = [];
        foreach ($grid as $ck => $slots) {
            foreach ($slots as $sk => $di) {
                $t = (int)($draft_entries[$di]['staff_id'] ?? 0);
                if ($t) $_t_ck_set[$t][$ck] = true;
            }
        }
        $multi_class_teachers = [];
        foreach ($_t_ck_set as $t => $cks) {
            if (count($cks) >= 2) $multi_class_teachers[] = $t;
        }
        unset($_t_ck_set);

        for ($iter = 0; $iter < $max_iter && !empty($unplaced); $iter++) {
            $temp = max($min_temp, $temp * $cool_rate);
            $r = mt_rand(1, 100);

            if ($r <= 35 && !empty($unplaced)) {
                // ---- RELOCATE: place unplaced at a genuinely free slot ----
                // Prioritize from class with the most gaps
                $gap_by_class = [];
                foreach ($unplaced as $idx => $uu) {
                    $gap_by_class[$uu['class_id'] . '_' . $uu['section_id']][] = $idx;
                }
                $max_ck = ''; $max_n = 0;
                foreach ($gap_by_class as $gck => $gidx) {
                    if (count($gidx) > $max_n) { $max_n = count($gidx); $max_ck = $gck; }
                }
                $ui = $gap_by_class[$max_ck][mt_rand(0, count($gap_by_class[$max_ck]) - 1)];
                $u = $unplaced[$ui];
                $ck = $u['class_id'] . '_' . $u['section_id'];
                $t = $u['staff_id'];
                $shuffled = $all_slots; shuffle($shuffled);

                foreach ($shuffled as $sk) {
                    if (isset($grid[$ck][$sk])) continue;
                    [$day, $pid] = explode('_', $sk); $pid = (int)$pid;
                    if (!empty($this->class_occ[$u['class_id']][$u['section_id']][$day][$pid][0])) continue;
                    if (!empty($this->class_unavail[$u['class_id']][$u['section_id']][$day][$pid])) continue;
                    if (!empty($this->teacher_occ[$t][$day][$pid])) continue;
                    if (!empty($unavail_map[$t][$day][$pid])) continue;

                    $new_di = count($draft_entries);
                    $draft_entries[] = [
                        'gen_log_id' => $log_id, 'session_id' => $session_id,
                        'class_id' => $u['class_id'], 'section_id' => $u['section_id'],
                        'subject_group_id' => $u['sg_id'], 'subject_group_subject_id' => $u['sgs_id'],
                        'staff_id' => $t, 'period_id' => $pid, 'day' => $day,
                        'room_id' => null, 'batch_id' => null, 'is_free_period' => 0, 'free_period_label' => null,
                    ];
                    $grid[$ck][$sk] = $new_di;
                    $this->class_occ[$u['class_id']][$u['section_id']][$day][$pid][0] = true;
                    $this->teacher_occ[$t][$day][$pid] = true;
                    $this->teacher_periods_day[$t][$day] = ($this->teacher_periods_day[$t][$day] ?? 0) + 1;
                    $this->teacher_periods_week[$t] = ($this->teacher_periods_week[$t] ?? 0) + 1;
                    array_splice($unplaced, $ui, 1);
                    break;
                }

            } elseif ($r <= 55 && !empty($unplaced)) {
                // ---- DISPLACE (smart): prioritize most-gapped class, flexible victims ----
                $gap_by_class = [];
                foreach ($unplaced as $idx => $uu) {
                    $gap_by_class[$uu['class_id'] . '_' . $uu['section_id']][] = $idx;
                }
                $max_ck = ''; $max_n = 0;
                foreach ($gap_by_class as $gck => $gidx) {
                    if (count($gidx) > $max_n) { $max_n = count($gidx); $max_ck = $gck; }
                }
                $ui = $gap_by_class[$max_ck][mt_rand(0, count($gap_by_class[$max_ck]) - 1)];
                $u = $unplaced[$ui];
                $ck = $u['class_id'] . '_' . $u['section_id'];
                $t = $u['staff_id'];
                $class_slots = isset($grid[$ck]) ? array_keys($grid[$ck]) : [];
                if (empty($class_slots)) continue;

                // Score victims by teacher flexibility (lowest weekly load = easiest to re-place)
                $scored_victims = [];
                foreach ($class_slots as $sk) {
                    [$day, $pid] = explode('_', $sk); $pid = (int)$pid;
                    if (!empty($this->teacher_occ[$t][$day][$pid])) continue;
                    if (!empty($unavail_map[$t][$day][$pid])) continue;
                    $vi = $grid[$ck][$sk];
                    $v = $draft_entries[$vi];
                    if (!empty($v['is_locked'])) continue;
                    $vs = (int)($v['subject_group_subject_id'] ?? 0);
                    if (!empty($joint_peers[$u['class_id']][$u['section_id']][$vs])) continue;
                    $vt = (int)($v['staff_id'] ?? 0);
                    $scored_victims[] = ['sk' => $sk, 'load' => $this->teacher_periods_week[$vt] ?? 0];
                }
                if (empty($scored_victims)) continue;
                usort($scored_victims, fn($a, $b) => $a['load'] <=> $b['load']);

                foreach ($scored_victims as $cand) {
                    if ((mt_rand(0, 100) / 100.0) >= exp(-1.0 / $temp)) continue;

                    $sk = $cand['sk'];
                    [$day, $pid] = explode('_', $sk); $pid = (int)$pid;
                    $vi = $grid[$ck][$sk];
                    $v = $draft_entries[$vi];
                    $vt = (int)($v['staff_id'] ?? 0);
                    $vs = (int)($v['subject_group_subject_id'] ?? 0);

                    $victim_data = $v;
                    $draft_entries[$vi]['subject_group_id'] = $u['sg_id'];
                    $draft_entries[$vi]['subject_group_subject_id'] = $u['sgs_id'];
                    $draft_entries[$vi]['staff_id'] = $t;

                    if ($vt) {
                        unset($this->teacher_occ[$vt][$day][$pid]);
                        $this->teacher_periods_day[$vt][$day] = max(0, ($this->teacher_periods_day[$vt][$day] ?? 1) - 1);
                        $this->teacher_periods_week[$vt] = max(0, ($this->teacher_periods_week[$vt] ?? 1) - 1);
                    }
                    $this->teacher_occ[$t][$day][$pid] = true;
                    $this->teacher_periods_day[$t][$day] = ($this->teacher_periods_day[$t][$day] ?? 0) + 1;
                    $this->teacher_periods_week[$t] = ($this->teacher_periods_week[$t] ?? 0) + 1;

                    $unplaced[$ui] = [
                        'class_id' => (int)$victim_data['class_id'], 'section_id' => (int)$victim_data['section_id'],
                        'staff_id' => $vt, 'sgs_id' => $vs,
                        'sg_id' => (int)($victim_data['subject_group_id'] ?? 0),
                    ];
                    break;
                }

            } elseif ($r <= 90) {
                // ---- SWAP WITHIN CLASS: exchange two entries' time slots ----
                $ckeys = array_keys($grid);
                if (empty($ckeys)) continue;
                $ck = $ckeys[mt_rand(0, count($ckeys) - 1)];
                $skeys = array_keys($grid[$ck]);
                if (count($skeys) < 2) continue;
                $i1 = mt_rand(0, count($skeys) - 1);
                $i2 = mt_rand(0, count($skeys) - 2);
                if ($i2 >= $i1) $i2++;

                $sk1 = $skeys[$i1]; $sk2 = $skeys[$i2];
                $di1 = $grid[$ck][$sk1]; $di2 = $grid[$ck][$sk2];
                $e1 = $draft_entries[$di1]; $e2 = $draft_entries[$di2];
                if (!empty($e1['is_locked']) || !empty($e2['is_locked'])) continue;

                [$d1, $p1] = explode('_', $sk1); $p1 = (int)$p1;
                [$d2, $p2] = explode('_', $sk2); $p2 = (int)$p2;
                $t1 = (int)($e1['staff_id'] ?? 0); $t2 = (int)($e2['staff_id'] ?? 0);

                $clash = false;
                if ($t1 !== $t2) {
                    if ($t1 && !empty($this->teacher_occ[$t1][$d2][$p2])) $clash = true;
                    if ($t2 && !empty($this->teacher_occ[$t2][$d1][$p1])) $clash = true;
                }
                if ($clash) {
                    if ((mt_rand(0, 1000) / 1000.0) >= exp(-50 / $temp)) continue;
                }

                $cid = (int)$e1['class_id']; $sid = (int)$e1['section_id'];
                $sgs1 = (int)($e1['subject_group_subject_id'] ?? 0);
                $sgs2 = (int)($e2['subject_group_subject_id'] ?? 0);
                $jp1 = $joint_peers[$cid][$sid][$sgs1] ?? null;
                $jp2 = $joint_peers[$cid][$sid][$sgs2] ?? null;
                if ($jp1 && !$this->_jointPeersCanMove($jp1, $cid, $sid, $d2, $p2)) continue;
                if ($jp2 && !$this->_jointPeersCanMove($jp2, $cid, $sid, $d1, $p1)) continue;

                $draft_entries[$di1]['day'] = $d2; $draft_entries[$di1]['period_id'] = $p2;
                $draft_entries[$di2]['day'] = $d1; $draft_entries[$di2]['period_id'] = $p1;
                $grid[$ck][$sk1] = $di2; $grid[$ck][$sk2] = $di1;

                if ($t1 && $t1 !== $t2) {
                    unset($this->teacher_occ[$t1][$d1][$p1]); $this->teacher_occ[$t1][$d2][$p2] = true;
                    if ($d1 !== $d2) {
                        $this->teacher_periods_day[$t1][$d1] = max(0, ($this->teacher_periods_day[$t1][$d1] ?? 1) - 1);
                        $this->teacher_periods_day[$t1][$d2] = ($this->teacher_periods_day[$t1][$d2] ?? 0) + 1;
                    }
                }
                if ($t2 && $t2 !== $t1) {
                    unset($this->teacher_occ[$t2][$d2][$p2]); $this->teacher_occ[$t2][$d1][$p1] = true;
                    if ($d1 !== $d2) {
                        $this->teacher_periods_day[$t2][$d2] = max(0, ($this->teacher_periods_day[$t2][$d2] ?? 1) - 1);
                        $this->teacher_periods_day[$t2][$d1] = ($this->teacher_periods_day[$t2][$d1] ?? 0) + 1;
                    }
                }
                if ($jp1) $this->_moveJointPeersDraft($jp1, $cid, $sid, $d1, $p1, $d2, $p2, $draft_entries);
                if ($jp2) $this->_moveJointPeersDraft($jp2, $cid, $sid, $d2, $p2, $d1, $p1, $draft_entries);

            } else {
                // ---- CROSS-CLASS TEACHER SWAP ----
                // Pick a teacher in 2+ classes, swap entries across classes to
                // shuffle the configuration and open new placement opportunities.
                if (empty($multi_class_teachers)) continue;
                $t = $multi_class_teachers[mt_rand(0, count($multi_class_teachers) - 1)];

                // Collect this teacher's current grid entries across all classes
                $t_entries = [];
                foreach ($grid as $gck => $gslots) {
                    foreach ($gslots as $gsk => $gdi) {
                        if ((int)($draft_entries[$gdi]['staff_id'] ?? 0) === $t) {
                            $t_entries[] = ['di' => $gdi, 'ck' => $gck, 'sk' => $gsk];
                        }
                    }
                }
                if (count($t_entries) < 2) continue;
                shuffle($t_entries);

                $te1 = null; $te2 = null;
                foreach ($t_entries as $te) {
                    if ($te1 === null) { $te1 = $te; continue; }
                    if ($te['ck'] !== $te1['ck']) { $te2 = $te; break; }
                }
                if (!$te2) continue;

                $di1 = $te1['di']; $di2 = $te2['di'];
                $e1 = $draft_entries[$di1]; $e2 = $draft_entries[$di2];
                if (!empty($e1['is_locked']) || !empty($e2['is_locked'])) continue;

                [$d1, $p1] = explode('_', $te1['sk']); $p1 = (int)$p1;
                [$d2, $p2] = explode('_', $te2['sk']); $p2 = (int)$p2;
                if ($d1 === $d2 && $p1 === $p2) continue;

                $cid1 = (int)$e1['class_id']; $sid1 = (int)$e1['section_id'];
                $cid2 = (int)$e2['class_id']; $sid2 = (int)$e2['section_id'];

                // Class occupancy: class1 must be free at (d2,p2), class2 at (d1,p1)
                if (!empty($this->class_occ[$cid1][$sid1][$d2][$p2][0])) continue;
                if (!empty($this->class_occ[$cid2][$sid2][$d1][$p1][0])) continue;
                if (!empty($this->class_unavail[$cid1][$sid1][$d2][$p2])) continue;
                if (!empty($this->class_unavail[$cid2][$sid2][$d1][$p1])) continue;

                // Skip joint entries entirely
                $sgs1 = (int)($e1['subject_group_subject_id'] ?? 0);
                $sgs2 = (int)($e2['subject_group_subject_id'] ?? 0);
                if (!empty($joint_peers[$cid1][$sid1][$sgs1])) continue;
                if (!empty($joint_peers[$cid2][$sid2][$sgs2])) continue;

                // Execute: move e1 to (d2,p2) in class1, e2 to (d1,p1) in class2
                $draft_entries[$di1]['day'] = $d2; $draft_entries[$di1]['period_id'] = $p2;
                $draft_entries[$di2]['day'] = $d1; $draft_entries[$di2]['period_id'] = $p1;

                unset($this->class_occ[$cid1][$sid1][$d1][$p1][0]);
                $this->class_occ[$cid1][$sid1][$d2][$p2][0] = true;
                unset($this->class_occ[$cid2][$sid2][$d2][$p2][0]);
                $this->class_occ[$cid2][$sid2][$d1][$p1][0] = true;

                // Teacher occ unchanged — T still occupies both (d1,p1) and (d2,p2)

                unset($grid[$te1['ck']][$te1['sk']]);
                $grid[$te1['ck']][$te2['sk']] = $di1;
                unset($grid[$te2['ck']][$te2['sk']]);
                $grid[$te2['ck']][$te1['sk']] = $di2;
            }
        }

        // Count how many were resolved
        $sa_resolved = $initial_unplaced - count($unplaced);

        // Update conflicts: remove resolved no_slot entries
        if ($sa_resolved > 0) {
            $remaining_sgs = [];
            foreach ($unplaced as $u) $remaining_sgs[$u['class_id'] . '_' . $u['section_id'] . '_' . $u['sgs_id']] = true;
            $new_conflicts = [];
            foreach ($conflicts as $conf) {
                if (($conf['type'] ?? '') !== 'no_slot') { $new_conflicts[] = $conf; continue; }
                $k = ($conf['class_id'] ?? 0) . '_' . ($conf['section_id'] ?? 0) . '_' . ($conf['subject_group_subject_id'] ?? 0);
                if (isset($remaining_sgs[$k])) { $new_conflicts[] = $conf; unset($remaining_sgs[$k]); }
            }
            $conflicts = $new_conflicts;
        }

        return $sa_resolved;
    }

    /**
     * Check if moving a joint entry's peers from (from_day,from_pid) to (to_day,to_pid) is possible.
     * Returns false if any peer class has the destination occupied.
     */
    private function _jointPeersCanMove($peers, $self_cid, $self_sid, $to_day, $to_pid)
    {
        if (empty($peers)) return true;
        foreach ($peers as $p) {
            if ($p['class_id'] === $self_cid && $p['section_id'] === $self_sid) continue;
            if (!empty($this->class_occ[$p['class_id']][$p['section_id']][$to_day][$to_pid][0])) return false;
        }
        return true;
    }

    /**
     * Move all joint peer entries from (from_day,from_pid) to (to_day,to_pid) in draft_entries.
     */
    private function _moveJointPeersDraft($peers, $self_cid, $self_sid, $from_day, $from_pid, $to_day, $to_pid, array &$draft_entries)
    {
        if (empty($peers)) return;
        foreach ($peers as $p) {
            if ($p['class_id'] === $self_cid && $p['section_id'] === $self_sid) continue;
            foreach ($draft_entries as $pdi => $pde) {
                if ((int)$pde['class_id'] === $p['class_id'] && (int)$pde['section_id'] === $p['section_id']
                    && $pde['day'] === $from_day && (int)$pde['period_id'] === $from_pid
                    && (int)($pde['subject_group_subject_id'] ?? 0) === $p['sgs_id']) {
                    $draft_entries[$pdi]['day'] = $to_day;
                    $draft_entries[$pdi]['period_id'] = $to_pid;
                    unset($this->class_occ[$p['class_id']][$p['section_id']][$from_day][$from_pid][0]);
                    $this->class_occ[$p['class_id']][$p['section_id']][$to_day][$to_pid][0] = true;
                    break;
                }
            }
        }
    }

    /**
     * Move all joint peer entries from (from_day,from_pid) to (to_day,to_pid) in live tt_entries (DB).
     */
    private function _moveJointPeersLive($peers, $self_cid, $self_sid, $from_day, $from_pid, $to_day, $to_pid, $session_id, &$entry_by_class)
    {
        if (empty($peers)) return;
        foreach ($peers as $p) {
            if ($p['class_id'] === $self_cid && $p['section_id'] === $self_sid) continue;
            $pe = $entry_by_class[$p['class_id']][$p['section_id']][$from_day][$from_pid] ?? null;
            if ($pe && (int)$pe->subject_group_subject_id === $p['sgs_id']) {
                $this->db->where('id', $pe->id)->update('tt_entries', ['day' => $to_day, 'period_id' => $to_pid]);
                unset($this->class_occ[$p['class_id']][$p['section_id']][$from_day][$from_pid][0]);
                $this->class_occ[$p['class_id']][$p['section_id']][$to_day][$to_pid][0] = true;
                unset($entry_by_class[$p['class_id']][$p['section_id']][$from_day][$from_pid]);
                $pe->day = $to_day; $pe->period_id = $to_pid;
                $entry_by_class[$p['class_id']][$p['section_id']][$to_day][$to_pid] = $pe;
            }
        }
    }

    /**
     * Cross-class teacher-centric swap repair.
     *
     * For each no_slot conflict (class C needs teacher T but T is always
     * busy when C is free), find a slot where C IS free but T is teaching
     * another class C2. If C2's entry can move to a different time where
     * both C2 AND T are free, do the swap: move C2's entry, place C's
     * subject at the freed slot.
     *
     * Iterates until a full pass produces no swaps (cascading opportunities).
     */
    /**
     * Targeted repair for "teacher has free slots but none align with class."
     * For each no_slot conflict, finds a class-free slot where teacher T is
     * busy in another class C2, then tries to relocate T's C2 entry so T
     * becomes free at that slot for the target class.
     */
    private function _teacherAlignmentRepair(
        array &$draft_entries, array &$conflicts, $constraints, $unavail_map,
        $log_id, $session_id
    ) {
        $resolved_count = 0;

        // Build teacher→slot→draft_index lookup
        $t_idx = [];
        foreach ($draft_entries as $di => $de) {
            if (empty($de['staff_id']) || !empty($de['is_free_period'])) continue;
            $t_idx[(int)$de['staff_id']][$de['day']][(int)$de['period_id']] = $di;
        }

        $new_conflicts = [];
        foreach ($conflicts as $conf) {
            if (($conf['type'] ?? '') !== 'no_slot') { $new_conflicts[] = $conf; continue; }

            $c_id = (int)($conf['class_id'] ?? 0);
            $s_id = (int)($conf['section_id'] ?? 0);
            $t_id = (int)($conf['staff_id'] ?? 0);
            $conf_sgs  = (int)($conf['subject_group_subject_id'] ?? 0);
            $conf_sgid = (int)($conf['subject_group_id'] ?? 0);
            if (!$c_id || !$s_id || !$t_id) { $new_conflicts[] = $conf; continue; }

            $placed = false;

            // Find slots where class IS free but teacher is busy in another class
            foreach ($this->working_days as $day) {
                if ($placed) break;
                foreach ($this->period_order as $pid) {
                    if ($placed) break;
                    // Class must be free here
                    if (!empty($this->class_occ[$c_id][$s_id][$day][$pid][0])) continue;
                    if (!empty($this->class_unavail[$c_id][$s_id][$day][$pid])) continue;
                    // Teacher must be busy here (in another class)
                    if (empty($this->teacher_occ[$t_id][$day][$pid])) continue;
                    if (!empty($unavail_map[$t_id][$day][$pid])) continue; // genuinely unavailable, can't fix

                    // Find what the teacher is doing at this slot
                    $blocker_di = $t_idx[$t_id][$day][$pid] ?? null;
                    if ($blocker_di === null) continue;
                    $blocker = $draft_entries[$blocker_di];
                    if (!empty($blocker['is_locked'])) continue;
                    $b_cid = (int)$blocker['class_id']; $b_sid = (int)$blocker['section_id'];
                    $b_sgs = (int)($blocker['subject_group_subject_id'] ?? 0);
                    $b_t   = (int)($blocker['staff_id'] ?? 0);
                    if ($b_cid === $c_id && $b_sid === $s_id) continue;

                    // Try to relocate blocker to any other free slot for both blocker's class AND teacher T
                    foreach ($this->working_days as $d2) {
                        if ($placed) break;
                        foreach ($this->period_order as $p2) {
                            if ($d2 === $day && $p2 === $pid) continue;
                            if (!empty($this->class_occ[$b_cid][$b_sid][$d2][$p2][0])) continue;
                            if (!empty($this->class_unavail[$b_cid][$b_sid][$d2][$p2])) continue;
                            if (!empty($this->teacher_occ[$b_t][$d2][$p2])) continue;
                            if (!empty($unavail_map[$b_t][$d2][$p2])) continue;

                            // Relocate blocker: move from (day,pid) to (d2,p2)
                            $draft_entries[$blocker_di]['day'] = $d2;
                            $draft_entries[$blocker_di]['period_id'] = $p2;
                            unset($this->class_occ[$b_cid][$b_sid][$day][$pid][0]);
                            $this->class_occ[$b_cid][$b_sid][$d2][$p2][0] = true;
                            unset($this->teacher_occ[$b_t][$day][$pid]);
                            $this->teacher_occ[$b_t][$d2][$p2] = true;
                            $this->teacher_periods_day[$b_t][$day] = max(0, ($this->teacher_periods_day[$b_t][$day] ?? 1) - 1);
                            $this->teacher_periods_day[$b_t][$d2] = ($this->teacher_periods_day[$b_t][$d2] ?? 0) + 1;
                            unset($t_idx[$b_t][$day][$pid]);
                            $t_idx[$b_t][$d2][$p2] = $blocker_di;

                            // Place the conflict subject at the freed slot
                            $new_di = count($draft_entries);
                            $draft_entries[] = [
                                'gen_log_id' => $log_id, 'session_id' => $session_id,
                                'class_id' => $c_id, 'section_id' => $s_id,
                                'subject_group_id' => $conf_sgid,
                                'subject_group_subject_id' => $conf_sgs,
                                'staff_id' => $t_id, 'period_id' => $pid,
                                'day' => $day, 'room_id' => null,
                                'batch_id' => null, 'is_free_period' => 0,
                                'free_period_label' => null,
                            ];
                            $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                            $this->teacher_occ[$t_id][$day][$pid] = true;
                            $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                            $t_idx[$t_id][$day][$pid] = $new_di;

                            $resolved_count++;
                            $placed = true;
                            break;
                        }
                    }

                    // Depth-2: if direct relocation failed, try swapping within blocker's class
                    if (!$placed) {
                        foreach ($this->working_days as $d2) {
                            if ($placed) break;
                            foreach ($this->period_order as $p2) {
                                if ($placed) break;
                                if ($d2 === $day && $p2 === $pid) continue;
                                // Need: blocker's class occupied at (d2,p2) by entry E2
                                if (empty($this->class_occ[$b_cid][$b_sid][$d2][$p2][0])) continue;
                                // T must be free at (d2,p2) for the swap to help
                                if (!empty($this->teacher_occ[$t_id][$d2][$p2])) continue;

                                // Find E2 at (d2,p2) in blocker's class
                                $e2_di = null;
                                foreach ($draft_entries as $ei => $ee) {
                                    if ((int)$ee['class_id'] === $b_cid && (int)$ee['section_id'] === $b_sid
                                        && $ee['day'] === $d2 && (int)$ee['period_id'] === $p2
                                        && empty($ee['is_free_period']) && empty($ee['is_locked'])) {
                                        $e2_di = $ei; break;
                                    }
                                }
                                if ($e2_di === null) continue;
                                $e2 = $draft_entries[$e2_di];
                                $t2 = (int)($e2['staff_id'] ?? 0);
                                if (!$t2 || $t2 === $t_id) continue;

                                // T2 must be free at (day,pid) — the slot we're freeing
                                if (!empty($this->teacher_occ[$t2][$day][$pid])) continue;
                                if (!empty($unavail_map[$t2][$day][$pid])) continue;
                                if (!empty($unavail_map[$b_t][$d2][$p2])) continue;

                                // Swap within blocker's class: blocker→(d2,p2), E2→(day,pid)
                                $draft_entries[$blocker_di]['day'] = $d2; $draft_entries[$blocker_di]['period_id'] = $p2;
                                $draft_entries[$e2_di]['day'] = $day; $draft_entries[$e2_di]['period_id'] = $pid;

                                unset($this->teacher_occ[$b_t][$day][$pid]);
                                $this->teacher_occ[$b_t][$d2][$p2] = true;
                                unset($this->teacher_occ[$t2][$d2][$p2]);
                                $this->teacher_occ[$t2][$day][$pid] = true;
                                if ($day !== $d2) {
                                    $this->teacher_periods_day[$b_t][$day] = max(0, ($this->teacher_periods_day[$b_t][$day] ?? 1) - 1);
                                    $this->teacher_periods_day[$b_t][$d2] = ($this->teacher_periods_day[$b_t][$d2] ?? 0) + 1;
                                    $this->teacher_periods_day[$t2][$d2] = max(0, ($this->teacher_periods_day[$t2][$d2] ?? 1) - 1);
                                    $this->teacher_periods_day[$t2][$day] = ($this->teacher_periods_day[$t2][$day] ?? 0) + 1;
                                }

                                // Now T is free at (day,pid) — but wait, we put T2 there.
                                // We need ANOTHER free slot for class C. Let me check if T
                                // is now free at the slot we just freed in blocker's class.
                                // Actually no — we swapped WITHIN blocker's class.
                                // T (the conflict teacher) was busy at (day,pid) teaching blocker's class.
                                // After the swap, T now teaches at (d2,p2) in blocker's class,
                                // and T2 teaches at (day,pid) in blocker's class.
                                // So T is now FREE at (day,pid)? No — T2 took (day,pid).
                                // The teacher_occ for T at (day,pid) was unset above.
                                // So T IS free at (day,pid)? Let's check:
                                // teacher_occ[T][day][pid] was NOT directly unset — we unset B_T.
                                // If B_T === T_ID, then yes. If not, T might still be busy.

                                // Only valid if b_t === t_id (blocker is taught by the conflict teacher)
                                if ($b_t !== $t_id) {
                                    // Revert the swap
                                    $draft_entries[$blocker_di]['day'] = $day; $draft_entries[$blocker_di]['period_id'] = $pid;
                                    $draft_entries[$e2_di]['day'] = $d2; $draft_entries[$e2_di]['period_id'] = $p2;
                                    $this->teacher_occ[$b_t][$day][$pid] = true; unset($this->teacher_occ[$b_t][$d2][$p2]);
                                    $this->teacher_occ[$t2][$d2][$p2] = true; unset($this->teacher_occ[$t2][$day][$pid]);
                                    if ($day !== $d2) {
                                        $this->teacher_periods_day[$b_t][$d2] = max(0, ($this->teacher_periods_day[$b_t][$d2] ?? 1) - 1);
                                        $this->teacher_periods_day[$b_t][$day] = ($this->teacher_periods_day[$b_t][$day] ?? 0) + 1;
                                        $this->teacher_periods_day[$t2][$day] = max(0, ($this->teacher_periods_day[$t2][$day] ?? 1) - 1);
                                        $this->teacher_periods_day[$t2][$d2] = ($this->teacher_periods_day[$t2][$d2] ?? 0) + 1;
                                    }
                                    continue;
                                }

                                // b_t === t_id: T is now free at (day,pid). Place conflict.
                                $new_di = count($draft_entries);
                                $draft_entries[] = [
                                    'gen_log_id' => $log_id, 'session_id' => $session_id,
                                    'class_id' => $c_id, 'section_id' => $s_id,
                                    'subject_group_id' => $conf_sgid,
                                    'subject_group_subject_id' => $conf_sgs,
                                    'staff_id' => $t_id, 'period_id' => $pid,
                                    'day' => $day, 'room_id' => null,
                                    'batch_id' => null, 'is_free_period' => 0,
                                    'free_period_label' => null,
                                ];
                                $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                                $this->teacher_occ[$t_id][$day][$pid] = true;
                                $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                $resolved_count++;
                                $placed = true;
                            }
                        }
                    }
                }
            }

                    // Depth-3: T is busy at target slot teaching class Z. T can't
                    // relocate within Z. But if T's Z-entry can move to slot (d3,p3)
                    // where Z is free but T is busy teaching class C3, AND C3's entry
                    // can move elsewhere — 3-step chain frees the target slot.
                    if (!$placed) {
                        foreach ($this->working_days as $day) {
                            if ($placed) break;
                            foreach ($this->period_order as $pid) {
                                if ($placed) break;
                                if (!empty($this->class_occ[$c_id][$s_id][$day][$pid][0])) continue;
                                if (!empty($this->class_unavail[$c_id][$s_id][$day][$pid])) continue;
                                if (empty($this->teacher_occ[$t_id][$day][$pid])) continue;
                                if (!empty($unavail_map[$t_id][$day][$pid])) continue;

                                $bi = $t_idx[$t_id][$day][$pid] ?? null;
                                if ($bi === null) continue;
                                $bl = $draft_entries[$bi];
                                if (!empty($bl['is_locked'])) continue;
                                $bc = (int)$bl['class_id']; $bs = (int)$bl['section_id'];

                                // For each slot (d3,p3) where class Z (blocker class) is free:
                                foreach ($this->working_days as $d3) {
                                    if ($placed) break;
                                    foreach ($this->period_order as $p3) {
                                        if ($placed) break;
                                        if ($d3 === $day && $p3 === $pid) continue;
                                        if (!empty($this->class_occ[$bc][$bs][$d3][$p3][0])) continue;
                                        if (!empty($this->class_unavail[$bc][$bs][$d3][$p3])) continue;
                                        // T must be busy at (d3,p3) in some other class C3
                                        if (empty($this->teacher_occ[$t_id][$d3][$p3])) continue;
                                        if (!empty($unavail_map[$t_id][$d3][$p3])) continue;

                                        $c3i = $t_idx[$t_id][$d3][$p3] ?? null;
                                        if ($c3i === null) continue;
                                        $c3e = $draft_entries[$c3i];
                                        if (!empty($c3e['is_locked'])) continue;
                                        $c3c = (int)$c3e['class_id']; $c3s = (int)$c3e['section_id'];
                                        if ($c3c === $bc && $c3s === $bs) continue;
                                        if ($c3c === $c_id && $c3s === $s_id) continue;

                                        // Can C3's entry relocate to a free slot?
                                        foreach ($this->working_days as $d4) {
                                            if ($placed) break;
                                            foreach ($this->period_order as $p4) {
                                                if ($d4 === $d3 && $p4 === $p3) continue;
                                                if (!empty($this->class_occ[$c3c][$c3s][$d4][$p4][0])) continue;
                                                if (!empty($this->class_unavail[$c3c][$c3s][$d4][$p4])) continue;
                                                if (!empty($this->teacher_occ[$t_id][$d4][$p4])) continue;
                                                if (!empty($unavail_map[$t_id][$d4][$p4])) continue;

                                                // 3-step chain: C3→(d4,p4), blocker→(d3,p3), place conflict at (day,pid)
                                                $draft_entries[$c3i]['day'] = $d4; $draft_entries[$c3i]['period_id'] = $p4;
                                                $draft_entries[$bi]['day'] = $d3; $draft_entries[$bi]['period_id'] = $p3;

                                                unset($this->class_occ[$c3c][$c3s][$d3][$p3][0]);
                                                $this->class_occ[$c3c][$c3s][$d4][$p4][0] = true;
                                                unset($this->class_occ[$bc][$bs][$day][$pid][0]);
                                                $this->class_occ[$bc][$bs][$d3][$p3][0] = true;

                                                unset($this->teacher_occ[$t_id][$day][$pid]);
                                                unset($this->teacher_occ[$t_id][$d3][$p3]);
                                                $this->teacher_occ[$t_id][$d3][$p3] = true;
                                                $this->teacher_occ[$t_id][$d4][$p4] = true;

                                                $draft_entries[] = [
                                                    'gen_log_id' => $log_id, 'session_id' => $session_id,
                                                    'class_id' => $c_id, 'section_id' => $s_id,
                                                    'subject_group_id' => $conf_sgid,
                                                    'subject_group_subject_id' => $conf_sgs,
                                                    'staff_id' => $t_id, 'period_id' => $pid,
                                                    'day' => $day, 'room_id' => null,
                                                    'batch_id' => null, 'is_free_period' => 0,
                                                    'free_period_label' => null,
                                                ];
                                                $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                                                $this->teacher_occ[$t_id][$day][$pid] = true;
                                                $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                                $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                                $resolved_count++;
                                                $placed = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
            if (!$placed) $new_conflicts[] = $conf;
        }
        $conflicts = $new_conflicts;
        return $resolved_count;
    }

    private function _crossClassSwapRepair(
        array &$draft_entries, array &$conflicts, $constraints, $unavail_map,
        $log_id, $session_id
    ) {
        $total_swaps = 0;
        $max_iterations = 10;

        // Joint peers for synchronized moves
        $joint_peers = [];
        $joint_sgs = []; // deprecated
        $jl_data = $this->db->select('class_id, section_id, subject_group_subject_id, joint_lesson_id')
            ->from('tt_subject_load')
            ->where('session_id', $session_id)
            ->where('joint_lesson_id IS NOT NULL', null, false)
            ->get()->result();
        $by_jl = [];
        foreach ($jl_data as $r) {
            $by_jl[(int)$r->joint_lesson_id][] = [
                'class_id' => (int)$r->class_id, 'section_id' => (int)$r->section_id,
                'sgs_id' => (int)$r->subject_group_subject_id,
            ];
        }
        foreach ($by_jl as $classes) {
            if (count($classes) < 2) continue;
            foreach ($classes as $c) {
                $joint_peers[$c['class_id']][$c['section_id']][$c['sgs_id']] = $classes;
            }
        }

        // Indexes: teacher → draft index, class → draft index
        $teacher_idx = [];
        $class_idx   = [];
        foreach ($draft_entries as $di => $de) {
            if (empty($de['staff_id']) || !empty($de['is_free_period'])) continue;
            $teacher_idx[(int)$de['staff_id']][$de['day']][(int)$de['period_id']] = $di;
            $class_idx[(int)$de['class_id']][(int)$de['section_id']][$de['day']][(int)$de['period_id']] = $di;
        }

        for ($iter = 0; $iter < $max_iterations; $iter++) {
            $swaps_this_round = 0;
            $new_conflicts = [];

            foreach ($conflicts as $conf) {
                if (($conf['type'] ?? '') !== 'no_slot') { $new_conflicts[] = $conf; continue; }

                $c_id      = (int) ($conf['class_id'] ?? 0);
                $s_id      = (int) ($conf['section_id'] ?? 0);
                $t_id      = (int) ($conf['staff_id'] ?? 0);
                $conf_sgs  = (int) ($conf['subject_group_subject_id'] ?? 0);
                $conf_sgid = (int) ($conf['subject_group_id'] ?? 0);
                if (!$c_id || !$s_id || !$t_id) { $new_conflicts[] = $conf; continue; }

                $tc  = $constraints[$t_id] ?? $this->default_tc;
                $wk  = $this->teacher_periods_week[$t_id] ?? 0;
                $cap = (int)$tc->max_periods_per_week;
                // Only block at exactly the cap boundary. If already OVER
                // (shared teachers whose load exceeds default cap), allow —
                // the constraint is already violated, blocking just leaves gaps.
                if ($cap > 0 && $wk === $cap) {
                    $new_conflicts[] = $conf; continue;
                }

                $n_periods = count($this->period_order);
                $eff_cap = min((int)$tc->max_periods_per_day,
                    $n_periods - max(0, (int)($tc->min_free_per_day ?? 0)));

                $resolved = false;

                foreach ($this->working_days as $day) {
                    if ($resolved) break;
                    foreach ($this->period_order as $pid) {
                        if ($resolved) break;

                        if (!empty($this->class_occ[$c_id][$s_id][$day][$pid][0])) continue;
                        if (!empty($this->class_unavail[$c_id][$s_id][$day][$pid])) continue;
                        if ($conf_sgs && !empty($this->subject_unavail[$conf_sgs][$day][$pid])) continue;
                        if (!empty($unavail_map[$t_id][$day][$pid])) continue;

                        $t_busy_here = !empty($this->teacher_occ[$t_id][$day][$pid]);
                        $day_count   = $this->teacher_periods_day[$t_id][$day] ?? 0;
                        $need_diff_day = ($day_count + 1 > $eff_cap);

                        if (!$t_busy_here && !$need_diff_day) continue;

                        // If T is free here and caps are already exceeded,
                        // place directly — no swap needed.
                        if (!$t_busy_here && ($day_count > $eff_cap || ($cap > 0 && $wk > $cap))) {
                            $new_di = count($draft_entries);
                            $draft_entries[] = [
                                'gen_log_id' => $log_id, 'session_id' => $session_id,
                                'class_id' => $c_id, 'section_id' => $s_id,
                                'subject_group_id' => $conf_sgid,
                                'subject_group_subject_id' => $conf_sgs,
                                'staff_id' => $t_id, 'period_id' => $pid,
                                'day' => $day, 'room_id' => null,
                                'batch_id' => null, 'is_free_period' => 0,
                                'free_period_label' => null,
                            ];
                            $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                            $this->teacher_occ[$t_id][$day][$pid] = true;
                            $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                            $teacher_idx[$t_id][$day][$pid] = $new_di;
                            if ($conf_sgs) {
                                $this->subject_day_count[$c_id][$s_id][$conf_sgs][$day] =
                                    ($this->subject_day_count[$c_id][$s_id][$conf_sgs][$day] ?? 0) + 1;
                            }
                            $swaps_this_round++;
                            $resolved = true;
                            break;
                        }

                        // Build candidate blockers to relocate
                        $blocker_candidates = [];
                        if ($t_busy_here) {
                            $idx = $teacher_idx[$t_id][$day][$pid] ?? null;
                            if ($idx !== null) {
                                $be = $draft_entries[$idx];
                                if (empty($be['is_locked'])                                    && !((int)$be['class_id'] === $c_id && (int)$be['section_id'] === $s_id)) {
                                    $blocker_candidates[] = $idx;
                                }
                            }
                        }
                        if ($need_diff_day && !$t_busy_here) {
                            foreach ($this->period_order as $cp) {
                                $idx = $teacher_idx[$t_id][$day][$cp] ?? null;
                                if ($idx === null) continue;
                                $be = $draft_entries[$idx];
                                if (!empty($be['is_locked'])) continue;
                                if ((int)$be['class_id'] === $c_id && (int)$be['section_id'] === $s_id) continue;
                                $blocker_candidates[] = $idx;
                            }
                        }
                        if (empty($blocker_candidates)) continue;

                        foreach ($blocker_candidates as $blocker_idx) {
                            if ($resolved) break;
                            $blocker = $draft_entries[$blocker_idx];
                            $b_cid = (int) $blocker['class_id'];
                            $b_sid = (int) $blocker['section_id'];
                            $b_sgs = (int) ($blocker['subject_group_subject_id'] ?? 0);
                            $b_orig_day = $blocker['day'];
                            $b_orig_pid = (int) $blocker['period_id'];

                            foreach ($this->working_days as $day2) {
                                if ($resolved) break;
                                if ($need_diff_day && $day2 === $day) continue;

                                foreach ($this->period_order as $pid2) {
                                    if ($day2 === $b_orig_day && $pid2 === $b_orig_pid) continue;

                                    if (!empty($this->class_occ[$b_cid][$b_sid][$day2][$pid2][0])) continue;
                                    if (!empty($this->class_unavail[$b_cid][$b_sid][$day2][$pid2])) continue;
                                    if (!empty($this->teacher_occ[$t_id][$day2][$pid2])) continue;
                                    if (!empty($unavail_map[$t_id][$day2][$pid2])) continue;
                                    if ($b_sgs && !empty($this->subject_unavail[$b_sgs][$day2][$pid2])) continue;

                                    // Destination daily cap — only enforce at boundary,
                                    // allow if already over
                                    if ($day2 !== $day) {
                                        $d2c = $this->teacher_periods_day[$t_id][$day2] ?? 0;
                                        if ($d2c === $eff_cap) continue;
                                    } else {
                                        if ($day_count === $eff_cap) continue;
                                    }

                                    // Joint sync: check all peers can move too
                                    $b_jp = $joint_peers[$b_cid][$b_sid][$b_sgs] ?? null;
                                    if ($b_jp && !$this->_jointPeersCanMove($b_jp, $b_cid, $b_sid, $day2, $pid2)) continue;

                                    // ---- Perform cross-class swap ----

                                    $draft_entries[$blocker_idx]['day']       = $day2;
                                    $draft_entries[$blocker_idx]['period_id'] = $pid2;

                                    unset($this->class_occ[$b_cid][$b_sid][$b_orig_day][$b_orig_pid][0]);
                                    $this->class_occ[$b_cid][$b_sid][$day2][$pid2][0] = true;

                                    unset($this->teacher_occ[$t_id][$b_orig_day][$b_orig_pid]);
                                    $this->teacher_occ[$t_id][$day2][$pid2] = true;
                                    $this->teacher_periods_day[$t_id][$b_orig_day] = max(0, ($this->teacher_periods_day[$t_id][$b_orig_day] ?? 1) - 1);
                                    $this->teacher_periods_day[$t_id][$day2] = ($this->teacher_periods_day[$t_id][$day2] ?? 0) + 1;

                                    if ($b_sgs) {
                                        $this->subject_day_count[$b_cid][$b_sid][$b_sgs][$b_orig_day] =
                                            max(0, ($this->subject_day_count[$b_cid][$b_sid][$b_sgs][$b_orig_day] ?? 1) - 1);
                                        $this->subject_day_count[$b_cid][$b_sid][$b_sgs][$day2] =
                                            ($this->subject_day_count[$b_cid][$b_sid][$b_sgs][$day2] ?? 0) + 1;
                                    }

                                    // Move joint peers
                                    if ($b_jp) $this->_moveJointPeersDraft($b_jp, $b_cid, $b_sid, $b_orig_day, $b_orig_pid, $day2, $pid2, $draft_entries);

                                    unset($teacher_idx[$t_id][$b_orig_day][$b_orig_pid]);
                                    $teacher_idx[$t_id][$day2][$pid2] = $blocker_idx;

                                    $new_di = count($draft_entries);
                                    $draft_entries[] = [
                                        'gen_log_id'               => $log_id,
                                        'session_id'               => $session_id,
                                        'class_id'                 => $c_id,
                                        'section_id'               => $s_id,
                                        'subject_group_id'         => $conf_sgid,
                                        'subject_group_subject_id' => $conf_sgs,
                                        'staff_id'                 => $t_id,
                                        'period_id'                => $pid,
                                        'day'                      => $day,
                                        'room_id'                  => null,
                                        'batch_id'                 => null,
                                        'is_free_period'           => 0,
                                        'free_period_label'        => null,
                                    ];

                                    $this->class_occ[$c_id][$s_id][$day][$pid][0] = true;
                                    $this->teacher_occ[$t_id][$day][$pid] = true;
                                    $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                    $this->teacher_periods_week[$t_id]     = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                    $teacher_idx[$t_id][$day][$pid] = $new_di;

                                    if ($conf_sgs) {
                                        $this->subject_day_count[$c_id][$s_id][$conf_sgs][$day] =
                                            ($this->subject_day_count[$c_id][$s_id][$conf_sgs][$day] ?? 0) + 1;
                                    }

                                    $swaps_this_round++;
                                    $resolved = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Depth-2: for each failed blocker, try swapping two entries
                // within the blocker's class to free up T at the original slot
                if (!$resolved) {
                    foreach ($this->working_days as $src_day) {
                        if ($resolved) break;
                        foreach ($this->period_order as $src_pid) {
                            if ($resolved) break;
                            if (!empty($this->class_occ[$c_id][$s_id][$src_day][$src_pid][0])) continue;
                            if (!empty($this->class_unavail[$c_id][$s_id][$src_day][$src_pid])) continue;
                            if ($conf_sgs && !empty($this->subject_unavail[$conf_sgs][$src_day][$src_pid])) continue;
                            if (!empty($unavail_map[$t_id][$src_day][$src_pid])) continue;
                            if (empty($this->teacher_occ[$t_id][$src_day][$src_pid])) continue;

                            $bi = $teacher_idx[$t_id][$src_day][$src_pid] ?? null;
                            if ($bi === null) continue;
                            $bl = $draft_entries[$bi];
                            if (!empty($bl['is_locked'])) continue;
                            $bc = (int)$bl['class_id']; $bs = (int)$bl['section_id'];
                            if (!empty($joint_peers[$bc][$bs][(int)($bl['subject_group_subject_id'] ?? 0)])) continue;
                            if ($bc === $c_id && $bs === $s_id) continue;

                            foreach ($this->working_days as $d3) {
                                if ($resolved) break;
                                foreach ($this->period_order as $p3) {
                                    if ($d3 === $src_day && $p3 === $src_pid) continue;
                                    if (!empty($this->teacher_occ[$t_id][$d3][$p3])) continue;
                                    $si = $class_idx[$bc][$bs][$d3][$p3] ?? null;
                                    if ($si === null) continue;
                                    $se = $draft_entries[$si];
                                    if (!empty($se['is_locked'])) continue;
                                    if (!empty($joint_peers[$bc][$bs][(int)($se['subject_group_subject_id'] ?? 0)])) continue;
                                    $t2 = (int)($se['staff_id'] ?? 0);
                                    if (!$t2 || $t2 === $t_id) continue;
                                    if (!empty($this->teacher_occ[$t2][$src_day][$src_pid])) continue;
                                    if (!empty($unavail_map[$t2][$src_day][$src_pid])) continue;
                                    if (!empty($unavail_map[$t_id][$d3][$p3])) continue;

                                    // Swap within blocker class
                                    $draft_entries[$bi]['day'] = $d3; $draft_entries[$bi]['period_id'] = $p3;
                                    $draft_entries[$si]['day'] = $src_day; $draft_entries[$si]['period_id'] = $src_pid;
                                    unset($this->teacher_occ[$t_id][$src_day][$src_pid]);
                                    $this->teacher_occ[$t_id][$d3][$p3] = true;
                                    unset($this->teacher_occ[$t2][$d3][$p3]);
                                    $this->teacher_occ[$t2][$src_day][$src_pid] = true;
                                    if ($d3 !== $src_day) {
                                        $this->teacher_periods_day[$t_id][$src_day] = max(0, ($this->teacher_periods_day[$t_id][$src_day] ?? 1) - 1);
                                        $this->teacher_periods_day[$t_id][$d3] = ($this->teacher_periods_day[$t_id][$d3] ?? 0) + 1;
                                        $this->teacher_periods_day[$t2][$d3] = max(0, ($this->teacher_periods_day[$t2][$d3] ?? 1) - 1);
                                        $this->teacher_periods_day[$t2][$src_day] = ($this->teacher_periods_day[$t2][$src_day] ?? 0) + 1;
                                    }
                                    unset($teacher_idx[$t_id][$src_day][$src_pid]); $teacher_idx[$t_id][$d3][$p3] = $bi;
                                    unset($teacher_idx[$t2][$d3][$p3]); $teacher_idx[$t2][$src_day][$src_pid] = $si;
                                    $class_idx[$bc][$bs][$d3][$p3] = $bi; $class_idx[$bc][$bs][$src_day][$src_pid] = $si;

                                    // Place conflict subject
                                    $draft_entries[] = [
                                        'gen_log_id' => $log_id, 'session_id' => $session_id,
                                        'class_id' => $c_id, 'section_id' => $s_id,
                                        'subject_group_id' => $conf_sgid,
                                        'subject_group_subject_id' => $conf_sgs,
                                        'staff_id' => $t_id, 'period_id' => $src_pid,
                                        'day' => $src_day, 'room_id' => null,
                                        'batch_id' => null, 'is_free_period' => 0,
                                        'free_period_label' => null,
                                    ];
                                    $this->class_occ[$c_id][$s_id][$src_day][$src_pid][0] = true;
                                    $this->teacher_occ[$t_id][$src_day][$src_pid] = true;
                                    $this->teacher_periods_day[$t_id][$src_day] = ($this->teacher_periods_day[$t_id][$src_day] ?? 0) + 1;
                                    $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                    $swaps_this_round++;
                                    $resolved = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Within-class rearrangement: move an entry in THIS class to
                // the empty slot, freeing a slot where T CAN work
                if (!$resolved) {
                    foreach ($this->working_days as $ed) {
                        if ($resolved) break;
                        foreach ($this->period_order as $ep) {
                            if ($resolved) break;
                            if (!empty($this->class_occ[$c_id][$s_id][$ed][$ep][0])) continue;
                            if (!empty($this->class_unavail[$c_id][$s_id][$ed][$ep])) continue;

                            foreach ($this->working_days as $od) {
                                if ($resolved) break;
                                foreach ($this->period_order as $op) {
                                    if ($od === $ed && $op === $ep) continue;
                                    $mi = $class_idx[$c_id][$s_id][$od][$op] ?? null;
                                    if ($mi === null) continue;
                                    $me = $draft_entries[$mi];
                                    if (!empty($me['is_locked'])) continue;
                                    $t2 = (int)($me['staff_id'] ?? 0);
                                    if (!$t2) continue;

                                    if (!empty($this->teacher_occ[$t_id][$od][$op])) continue;
                                    if (!empty($unavail_map[$t_id][$od][$op])) continue;
                                    if ($conf_sgs && !empty($this->subject_unavail[$conf_sgs][$od][$op])) continue;
                                    if (!empty($this->teacher_occ[$t2][$ed][$ep])) continue;
                                    if (!empty($unavail_map[$t2][$ed][$ep])) continue;
                                    $ms = (int)($me['subject_group_subject_id'] ?? 0);
                                    if ($ms && !empty($this->subject_unavail[$ms][$ed][$ep])) continue;

                                    // Joint sync: if $me is joint, check peers can move too
                                    $me_jp = $joint_peers[$c_id][$s_id][$ms] ?? null;
                                    if ($me_jp && !$this->_jointPeersCanMove($me_jp, $c_id, $s_id, $ed, $ep)) continue;

                                    $draft_entries[$mi]['day'] = $ed; $draft_entries[$mi]['period_id'] = $ep;
                                    if ($me_jp) $this->_moveJointPeersDraft($me_jp, $c_id, $s_id, $od, $op, $ed, $ep, $draft_entries);
                                    $draft_entries[] = [
                                        'gen_log_id' => $log_id, 'session_id' => $session_id,
                                        'class_id' => $c_id, 'section_id' => $s_id,
                                        'subject_group_id' => $conf_sgid,
                                        'subject_group_subject_id' => $conf_sgs,
                                        'staff_id' => $t_id, 'period_id' => $op,
                                        'day' => $od, 'room_id' => null,
                                        'batch_id' => null, 'is_free_period' => 0,
                                        'free_period_label' => null,
                                    ];
                                    $this->class_occ[$c_id][$s_id][$ed][$ep][0] = true;
                                    unset($this->teacher_occ[$t2][$od][$op]);
                                    $this->teacher_occ[$t2][$ed][$ep] = true;
                                    $this->teacher_occ[$t_id][$od][$op] = true;
                                    if ($od !== $ed) {
                                        $this->teacher_periods_day[$t2][$od] = max(0, ($this->teacher_periods_day[$t2][$od] ?? 1) - 1);
                                        $this->teacher_periods_day[$t2][$ed] = ($this->teacher_periods_day[$t2][$ed] ?? 0) + 1;
                                    }
                                    $this->teacher_periods_day[$t_id][$od] = ($this->teacher_periods_day[$t_id][$od] ?? 0) + 1;
                                    $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                                    $class_idx[$c_id][$s_id][$ed][$ep] = $mi;
                                    unset($class_idx[$c_id][$s_id][$od][$op]);

                                    $swaps_this_round++;
                                    $resolved = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Depth-3 chain: free T2 in another class to enable within-class swap
                if (!$resolved) {
                    foreach ($this->working_days as $ed) {
                        if ($resolved) break;
                        foreach ($this->period_order as $ep) {
                            if ($resolved) break;
                            if (!empty($this->class_occ[$c_id][$s_id][$ed][$ep][0])) continue;
                            if (!empty($this->class_unavail[$c_id][$s_id][$ed][$ep])) continue;

                            foreach ($this->working_days as $od) {
                                if ($resolved) break;
                                foreach ($this->period_order as $op) {
                                    if ($resolved) break;
                                    if ($od === $ed && $op === $ep) continue;
                                    $mi = $class_idx[$c_id][$s_id][$od][$op] ?? null;
                                    if ($mi === null) continue;
                                    $me = $draft_entries[$mi];
                                    if (!empty($me['is_locked'])) continue;
                                    if (!empty($joint_peers[$c_id][$s_id][(int)($me['subject_group_subject_id'] ?? 0)])) continue;
                                    $t2 = (int)($me['staff_id'] ?? 0);
                                    if (!$t2) continue;

                                    if (!empty($this->teacher_occ[$t_id][$od][$op])) continue;
                                    if (!empty($unavail_map[$t_id][$od][$op])) continue;
                                    if ($conf_sgs && !empty($this->subject_unavail[$conf_sgs][$od][$op])) continue;
                                    if (empty($this->teacher_occ[$t2][$ed][$ep])) continue;

                                    $c3i = $teacher_idx[$t2][$ed][$ep] ?? null;
                                    if ($c3i === null) continue;
                                    $c3e = $draft_entries[$c3i];
                                    if (!empty($c3e['is_locked'])) continue;
                                    if (!empty($joint_peers[(int)$c3e['class_id']][(int)$c3e['section_id']][(int)($c3e['subject_group_subject_id'] ?? 0)])) continue;
                                    $c3c = (int)$c3e['class_id']; $c3s = (int)$c3e['section_id'];
                                    if ($c3c === $c_id && $c3s === $s_id) continue;
                                    $c3sgs = (int)($c3e['subject_group_subject_id'] ?? 0);

                                    foreach ($this->working_days as $d3) {
                                        if ($resolved) break;
                                        foreach ($this->period_order as $p3) {
                                            if ($d3 === $ed && $p3 === $ep) continue;
                                            if (!empty($this->class_occ[$c3c][$c3s][$d3][$p3][0])) continue;
                                            if (!empty($this->class_unavail[$c3c][$c3s][$d3][$p3])) continue;
                                            if (!empty($this->teacher_occ[$t2][$d3][$p3])) continue;
                                            if (!empty($unavail_map[$t2][$d3][$p3])) continue;
                                            if ($c3sgs && !empty($this->subject_unavail[$c3sgs][$d3][$p3])) continue;

                                            // 3-step chain
                                            $draft_entries[$c3i]['day'] = $d3; $draft_entries[$c3i]['period_id'] = $p3;
                                            $draft_entries[$mi]['day'] = $ed; $draft_entries[$mi]['period_id'] = $ep;
                                            $draft_entries[] = [
                                                'gen_log_id' => $log_id, 'session_id' => $session_id,
                                                'class_id' => $c_id, 'section_id' => $s_id,
                                                'subject_group_id' => $conf_sgid,
                                                'subject_group_subject_id' => $conf_sgs,
                                                'staff_id' => $t_id, 'period_id' => $op,
                                                'day' => $od, 'room_id' => null,
                                                'batch_id' => null, 'is_free_period' => 0,
                                                'free_period_label' => null,
                                            ];

                                            unset($this->class_occ[$c3c][$c3s][$ed][$ep][0]);
                                            $this->class_occ[$c3c][$c3s][$d3][$p3][0] = true;
                                            unset($this->teacher_occ[$t2][$ed][$ep]);
                                            $this->teacher_occ[$t2][$d3][$p3] = true;
                                            $this->teacher_occ[$t2][$ed][$ep] = true;
                                            unset($this->teacher_occ[$t2][$od][$op]);
                                            $this->class_occ[$c_id][$s_id][$ed][$ep][0] = true;
                                            $this->teacher_occ[$t_id][$od][$op] = true;
                                            $this->teacher_periods_day[$t_id][$od] = ($this->teacher_periods_day[$t_id][$od] ?? 0) + 1;
                                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                            $swaps_this_round++;
                                            $resolved = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Last resort: teacher over-booked, place even if it double-books
                if (!$resolved && $cap > 0 && $wk > $cap) {
                    foreach ($this->working_days as $fd) {
                        if ($resolved) break;
                        foreach ($this->period_order as $fp) {
                            if (!empty($this->class_occ[$c_id][$s_id][$fd][$fp][0])) continue;
                            if (!empty($this->class_unavail[$c_id][$s_id][$fd][$fp])) continue;
                            if ($conf_sgs && !empty($this->subject_unavail[$conf_sgs][$fd][$fp])) continue;

                            $draft_entries[] = [
                                'gen_log_id' => $log_id, 'session_id' => $session_id,
                                'class_id' => $c_id, 'section_id' => $s_id,
                                'subject_group_id' => $conf_sgid,
                                'subject_group_subject_id' => $conf_sgs,
                                'staff_id' => $t_id, 'period_id' => $fp,
                                'day' => $fd, 'room_id' => null,
                                'batch_id' => null, 'is_free_period' => 0,
                                'free_period_label' => null,
                            ];
                            $this->class_occ[$c_id][$s_id][$fd][$fp][0] = true;
                            $this->teacher_periods_day[$t_id][$fd] = ($this->teacher_periods_day[$t_id][$fd] ?? 0) + 1;
                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                            if ($conf_sgs) {
                                $this->subject_day_count[$c_id][$s_id][$conf_sgs][$fd] =
                                    ($this->subject_day_count[$c_id][$s_id][$conf_sgs][$fd] ?? 0) + 1;
                            }
                            $swaps_this_round++;
                            $resolved = true;
                            break;
                        }
                    }
                }

                if (!$resolved) $new_conflicts[] = $conf;
            }

            $conflicts   = $new_conflicts;
            $total_swaps += $swaps_this_round;
            if ($swaps_this_round === 0) break;
        }

        return $total_swaps;
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
            $c = $constraints[$t_id] ?? $this->default_tc;
            if ($c->max_periods_per_week > 0 && $booked >= (int)$c->max_periods_per_week) {
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

                $n_periods = count($this->period_order);
                $first_pid = $pid_group[0];
                $last_pid  = end($pid_group);

                if ($all_teachers_required && !empty($teacher_ids)) {
                    // ALL teachers must be free simultaneously
                    $all_free = true;
                    foreach ($teacher_ids as $t_id) {
                        $c       = $constraints[$t_id] ?? $this->default_tc;
                        $eff_cap = min((int)$c->max_periods_per_day, $n_periods - max(0, (int)($c->min_free_per_day ?? 0)));
                        if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $eff_cap)                     { $all_free = false; break; }
                        if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)          { $all_free = false; break; }
                        if ($c->avoid_first_period && $first_pid === $this->period_order[0])                          { $all_free = false; break; }
                        if ($c->avoid_last_period  && $last_pid  === end($this->period_order))                        { $all_free = false; break; }
                        if ($c->preferred_start_time && isset($this->periods[$first_pid]) &&
                            $this->periods[$first_pid]->start_time < $c->preferred_start_time)                        { $all_free = false; break; }
                        if ($c->preferred_end_time && isset($this->periods[$last_pid]) &&
                            $this->periods[$last_pid]->end_time > $c->preferred_end_time)                             { $all_free = false; break; }
                        if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) { $all_free = false; break; }
                        if ($c->max_gap_per_day !== null && $this->_violatesGapRule($t_id, $day, $pid_group, (int)$c->max_gap_per_day)) { $all_free = false; break; }
                        foreach ($pid_group as $pid) {
                            if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $all_free = false; break 2; }
                            if (!empty($unavail_map[$t_id][$day][$pid]))        { $all_free = false; break 2; }
                        }
                    }
                    if (!$all_free) continue;

                    $room_id = $this->_findRoom($day, $pid_group, $room_type, $pref_room, $primary, $constraints[$primary] ?? $this->default_tc);
                    $adj     = $this->_adjacencyPenalty($class_id, $section_id, $sgs_id, $day, $pid_group, $consec, $max_per_day);
                    if ($overflow_mode && $adj < 0) $adj = 5;
                    // Spread teacher load evenly across days — prefer days where teacher has fewer periods
                    $score   = $day_penalty + $day_on1_bonus + $adj
                             - ($this->teacher_periods_day[$primary][$day] ?? 0) * 0.8;
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
                            $c       = $constraints[$t_id] ?? $this->default_tc;
                            $eff_cap = min((int)$c->max_periods_per_day, $n_periods - max(0, (int)($c->min_free_per_day ?? 0)));
                            if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $eff_cap)          continue;
                            if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)  continue;
                            if ($c->avoid_first_period && $first_pid === $this->period_order[0])                  continue;
                            if ($c->avoid_last_period  && $last_pid  === end($this->period_order))                continue;
                            if ($c->preferred_start_time && isset($this->periods[$first_pid]) &&
                                $this->periods[$first_pid]->start_time < $c->preferred_start_time)               continue;
                            if ($c->preferred_end_time && isset($this->periods[$last_pid]) &&
                                $this->periods[$last_pid]->end_time > $c->preferred_end_time)                    continue;
                            $t_free = true;
                            foreach ($pid_group as $pid) {
                                if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $t_free = false; break; }
                                if (!empty($unavail_map[$t_id][$day][$pid]))        { $t_free = false; break; }
                            }
                            if (!$t_free) continue;
                            if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) continue;
                            if ($c->max_gap_per_day !== null && $this->_violatesGapRule($t_id, $day, $pid_group, (int)$c->max_gap_per_day)) continue;
                        } else {
                            $c = null;
                        }

                        $room_id = $this->_findRoom($day, $pid_group, $room_type, $pref_room, $t_id, $c);
                        $adj     = $this->_adjacencyPenalty($class_id, $section_id, $sgs_id, $day, $pid_group, $consec, $max_per_day);
                        if ($overflow_mode && $adj < 0) $adj = 5;
                        $score   = $day_penalty + $day_on1_bonus + $adj
                                 - ($this->teacher_periods_day[$t_id][$day] ?? 0) * 0.8;
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
    /**
     * Backtracking solver for joint lesson conflicts.
     * Undoes ALL joint placements, then re-places using recursive backtracking
     * with MRV (most constrained first) and forward checking.
     */
    private function _backtrackJointCluster(
        $joint_lessons, $constraints, $unavail_map,
        $log_id, $session_id, array &$draft_entries, array &$conflicts,
        &$total_required, &$total_placed, &$class_stats
    ) {
        $bt_start = microtime(true);
        $bt_time_limit = 15; // seconds

        // 1. Undo ALL joint placements from this pass
        $jl_entry_indices = [];
        foreach ($draft_entries as $di => $de) {
            foreach ($joint_lessons as $jl) {
                foreach ($jl->classes as $cs) {
                    if ((int)$de['class_id'] === (int)$cs->class_id && (int)$de['section_id'] === (int)$cs->section_id
                        && (int)($de['subject_group_subject_id'] ?? 0) === (int)$cs->sgs_id) {
                        $jl_entry_indices[$di] = true;
                        break 2;
                    }
                }
            }
        }

        // Remove joint entries and restore occupancy
        foreach ($jl_entry_indices as $di => $_) {
            $de = $draft_entries[$di];
            $cid = (int)$de['class_id']; $sid = (int)$de['section_id'];
            $day = $de['day']; $pid = (int)$de['period_id'];
            unset($this->class_occ[$cid][$sid][$day][$pid][0]);
            $tid = (int)($de['staff_id'] ?? 0);
            if ($tid) {
                unset($this->teacher_occ[$tid][$day][$pid]);
                $this->teacher_periods_day[$tid][$day] = max(0, ($this->teacher_periods_day[$tid][$day] ?? 1) - 1);
                $this->teacher_periods_week[$tid] = max(0, ($this->teacher_periods_week[$tid] ?? 1) - 1);
            }
            unset($draft_entries[$di]);
        }
        $draft_entries = array_values($draft_entries);

        // Reset joint-related stats
        foreach ($joint_lessons as $jl) {
            $consec = max(1, (int)$jl->consecutive_periods);
            $ppw = (int)$jl->periods_per_week;
            $placements = ($consec > 1) ? (int)ceil($ppw / $consec) : $ppw;
            foreach ($jl->classes as $cs) {
                $ck = $cs->class_id . '_' . $cs->section_id;
                if (isset($class_stats[$ck])) $class_stats[$ck]['placed'] = max(0, $class_stats[$ck]['placed'] - $placements);
            }
            $total_placed = max(0, $total_placed - $placements);
        }

        // Remove joint conflicts
        $conflicts = array_values(array_filter($conflicts, fn($c) =>
            !(empty($c['type']) && strpos($c['reason'] ?? '', 'Joint lesson') === 0)));

        // 2. Build placement tasks: [{jl, placement_idx, consec}]
        $tasks = [];
        foreach ($joint_lessons as $jk => $jl) {
            $consec = max(1, (int)$jl->consecutive_periods);
            $ppw = (int)$jl->periods_per_week;
            $n = ($consec > 1) ? (int)ceil($ppw / $consec) : $ppw;
            for ($p = 0; $p < $n; $p++) {
                $tasks[] = ['jk' => $jk, 'jl' => $jl, 'consec' => $consec];
            }
        }

        // 3. Recursive backtracking with MRV + forward checking
        $solution = [];
        $bt_calls = 0;
        $success = $this->_btSolve($tasks, $solution, $constraints, $unavail_map, $bt_start, $bt_time_limit, $bt_calls);

        // 4. Apply solution or fall back to greedy
        $placed_count = 0;
        if ($success) {
            foreach ($solution as $si => $slot) {
                $jl = $tasks[$si]['jl'];
                $t_id = $slot['staff_id'];
                foreach ($jl->classes as $cs) {
                    foreach ($slot['period_ids'] as $pid) {
                        $this->class_occ[(int)$cs->class_id][(int)$cs->section_id][$slot['day']][$pid][0] = true;
                        if ($t_id) $this->teacher_occ[$t_id][$slot['day']][$pid] = true;
                        $draft_entries[] = [
                            'gen_log_id' => $log_id, 'session_id' => $session_id,
                            'class_id' => (int)$cs->class_id, 'section_id' => (int)$cs->section_id,
                            'subject_group_id' => (int)$cs->sg_id,
                            'subject_group_subject_id' => (int)$cs->sgs_id,
                            'staff_id' => $t_id, 'period_id' => $pid,
                            'day' => $slot['day'], 'room_id' => $slot['room_id'],
                            'batch_id' => null, 'is_free_period' => 0, 'free_period_label' => null,
                        ];
                    }
                    $ck = $cs->class_id . '_' . $cs->section_id;
                    if (isset($class_stats[$ck])) $class_stats[$ck]['placed']++;
                }
                if ($t_id) {
                    foreach ($slot['period_ids'] as $pid) {
                        $this->teacher_periods_day[$t_id][$slot['day']] = ($this->teacher_periods_day[$t_id][$slot['day']] ?? 0) + 1;
                        $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                    }
                }
                $placed_count++;
                $total_placed++;
            }
        } else {
            // Backtracking failed or timed out — re-run greedy
            foreach ($joint_lessons as $jl) {
                $jl_t = $jl->teacher_ids ?? [];
                $jl_c = max(1, (int)$jl->consecutive_periods);
                $jl_p = (int)$jl->periods_per_week;
                $n = ($jl_c > 1) ? (int)ceil($jl_p / $jl_c) : $jl_p;
                $days_used = [];
                for ($p = 0; $p < $n; $p++) {
                    $slot = $this->_findJointSlot($jl, $jl_t, !empty($jl->all_teachers_required),
                        $jl->room_id ? (int)$jl->room_id : null, $jl_c, $days_used,
                        max(1, (int)$jl->max_per_day), !empty($jl->distribute_evenly),
                        $constraints, $unavail_map);
                    if ($slot) {
                        foreach ($jl->classes as $cs) {
                            foreach ($slot['period_ids'] as $pid) {
                                $this->class_occ[(int)$cs->class_id][(int)$cs->section_id][$slot['day']][$pid][0] = true;
                                if ($slot['staff_id']) $this->teacher_occ[$slot['staff_id']][$slot['day']][$pid] = true;
                                $draft_entries[] = [
                                    'gen_log_id' => $log_id, 'session_id' => $session_id,
                                    'class_id' => (int)$cs->class_id, 'section_id' => (int)$cs->section_id,
                                    'subject_group_id' => (int)$cs->sg_id, 'subject_group_subject_id' => (int)$cs->sgs_id,
                                    'staff_id' => $slot['staff_id'], 'period_id' => $pid,
                                    'day' => $slot['day'], 'room_id' => $slot['room_id'],
                                    'batch_id' => null, 'is_free_period' => 0, 'free_period_label' => null,
                                ];
                            }
                            $ck = $cs->class_id . '_' . $cs->section_id;
                            if (isset($class_stats[$ck])) $class_stats[$ck]['placed']++;
                        }
                        if ($slot['staff_id']) {
                            foreach ($slot['period_ids'] as $pid) {
                                $this->teacher_periods_day[$slot['staff_id']][$slot['day']] = ($this->teacher_periods_day[$slot['staff_id']][$slot['day']] ?? 0) + 1;
                                $this->teacher_periods_week[$slot['staff_id']] = ($this->teacher_periods_week[$slot['staff_id']] ?? 0) + 1;
                            }
                        }
                        $days_used[] = $slot['day'];
                        $total_placed++;
                    }
                }
            }
        }

        return $placed_count;
    }

    /**
     * Recursive backtracking with MRV ordering and forward checking.
     */
    private function _btSolve(array &$tasks, array &$solution, $constraints, $unavail_map,
                              $bt_start, $bt_time_limit, &$bt_calls)
    {
        if (count($solution) >= count($tasks)) return true;
        if (microtime(true) - $bt_start > $bt_time_limit) return false;
        $bt_calls++;

        // MRV: pick unplaced task with fewest viable slots
        $best_ti = -1; $best_count = PHP_INT_MAX;
        for ($ti = 0; $ti < count($tasks); $ti++) {
            if (isset($solution[$ti])) continue;
            $viable = $this->_btViableSlots($tasks[$ti]['jl'], $tasks[$ti]['consec'], $constraints, $unavail_map);
            $cnt = count($viable);
            if ($cnt === 0) return false; // dead end
            if ($cnt < $best_count) { $best_count = $cnt; $best_ti = $ti; }
        }
        if ($best_ti === -1) return true;

        $task = $tasks[$best_ti];
        $jl = $task['jl'];
        $viable = $this->_btViableSlots($jl, $task['consec'], $constraints, $unavail_map);
        shuffle($viable); // diversity across passes

        foreach ($viable as $slot) {
            // Place
            $changes = $this->_btPlace($jl, $slot);
            $solution[$best_ti] = $slot;

            // Forward check: do remaining tasks still have ≥1 viable slot?
            $ok = true;
            for ($ti = 0; $ti < count($tasks); $ti++) {
                if (isset($solution[$ti])) continue;
                $fc = $this->_btViableSlots($tasks[$ti]['jl'], $tasks[$ti]['consec'], $constraints, $unavail_map);
                if (empty($fc)) { $ok = false; break; }
            }

            if ($ok && $this->_btSolve($tasks, $solution, $constraints, $unavail_map, $bt_start, $bt_time_limit, $bt_calls)) {
                return true;
            }

            // Backtrack
            $this->_btUndo($changes);
            unset($solution[$best_ti]);
        }

        return false;
    }

    private function _btViableSlots($jl, $consec, $constraints, $unavail_map)
    {
        $t_ids = $jl->teacher_ids ?? [];
        $all_req = !empty($jl->all_teachers_required);
        $slots = [];
        foreach ($this->working_days as $day) {
            foreach ($this->_getConsecutiveStarts($consec) as $pg) {
                $all_free = true;
                foreach ($jl->classes as $cs) {
                    foreach ($pg as $pid) {
                        if (!empty($this->class_occ[(int)$cs->class_id][(int)$cs->section_id][$day][$pid][0])
                            || !empty($this->class_unavail[(int)$cs->class_id][(int)$cs->section_id][$day][$pid])) {
                            $all_free = false; break 2;
                        }
                    }
                }
                if (!$all_free) continue;

                // Teacher check
                if ($all_req && !empty($t_ids)) {
                    $t_ok = true;
                    foreach ($t_ids as $tid) {
                        foreach ($pg as $pid) {
                            if (!empty($this->teacher_occ[$tid][$day][$pid]) || !empty($unavail_map[$tid][$day][$pid])) {
                                $t_ok = false; break 2;
                            }
                        }
                    }
                    if ($t_ok) $slots[] = ['day' => $day, 'period_ids' => $pg, 'staff_id' => $t_ids[0], 'room_id' => null];
                } elseif (!empty($t_ids)) {
                    foreach ($t_ids as $tid) {
                        $t_ok = true;
                        foreach ($pg as $pid) {
                            if (!empty($this->teacher_occ[$tid][$day][$pid]) || !empty($unavail_map[$tid][$day][$pid])) {
                                $t_ok = false; break;
                            }
                        }
                        if ($t_ok) {
                            $slots[] = ['day' => $day, 'period_ids' => $pg, 'staff_id' => $tid, 'room_id' => null];
                            break;
                        }
                    }
                } else {
                    $slots[] = ['day' => $day, 'period_ids' => $pg, 'staff_id' => null, 'room_id' => null];
                }
            }
        }
        return $slots;
    }

    private function _btPlace($jl, $slot)
    {
        $changes = [];
        foreach ($jl->classes as $cs) {
            foreach ($slot['period_ids'] as $pid) {
                $cid = (int)$cs->class_id; $sid = (int)$cs->section_id;
                $this->class_occ[$cid][$sid][$slot['day']][$pid][0] = true;
                $changes[] = ['t' => 'c', 'cid' => $cid, 'sid' => $sid, 'd' => $slot['day'], 'p' => $pid];
            }
        }
        if ($slot['staff_id']) {
            foreach ($slot['period_ids'] as $pid) {
                $tid = $slot['staff_id'];
                $this->teacher_occ[$tid][$slot['day']][$pid] = true;
                $this->teacher_periods_day[$tid][$slot['day']] = ($this->teacher_periods_day[$tid][$slot['day']] ?? 0) + 1;
                $this->teacher_periods_week[$tid] = ($this->teacher_periods_week[$tid] ?? 0) + 1;
                $changes[] = ['t' => 's', 'tid' => $tid, 'd' => $slot['day'], 'p' => $pid];
            }
        }
        return $changes;
    }

    private function _btUndo(array $changes)
    {
        foreach (array_reverse($changes) as $c) {
            if ($c['t'] === 'c') {
                unset($this->class_occ[$c['cid']][$c['sid']][$c['d']][$c['p']][0]);
            } elseif ($c['t'] === 's') {
                unset($this->teacher_occ[$c['tid']][$c['d']][$c['p']]);
                $this->teacher_periods_day[$c['tid']][$c['d']] = max(0, ($this->teacher_periods_day[$c['tid']][$c['d']] ?? 1) - 1);
                $this->teacher_periods_week[$c['tid']] = max(0, ($this->teacher_periods_week[$c['tid']] ?? 1) - 1);
            }
        }
    }

    /**
     * If $fixed_day/$fixed_pids are given, only that exact day+period combination is tried
     * (an admin-pinned Fixed Slot) instead of searching the whole week.
     * Returns ['day', 'period_ids', 'staff_id', 'room_id'] or null.
     */
    private function _findJointSlot($jl, array $teacher_ids, $all_teachers_required, $pref_room,
                                     $consec, $days_used, $max_per_day, $dist_evenly,
                                     $constraints, $unavail_map, $fixed_day = null, $fixed_pids = null)
    {
        $best       = null;
        $best_score = -999;
        $primary    = $teacher_ids[0] ?? null;

        foreach ($this->working_days as $day) {
            if ($fixed_day !== null && $day !== $fixed_day) continue;
            $day_penalty = ($dist_evenly && in_array($day, $days_used)) ? -10 : 0;

            foreach ($this->_getConsecutiveStarts($consec) as $pid_group) {
                if ($fixed_pids !== null && $pid_group != $fixed_pids) continue;
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

                $n_periods = count($this->period_order);
                $first_pid = $pid_group[0];
                $last_pid  = end($pid_group);

                if ($all_teachers_required && !empty($teacher_ids)) {
                    // ALL teachers must be free simultaneously
                    $all_free = true;
                    foreach ($teacher_ids as $t_id) {
                        $c       = $constraints[$t_id] ?? $this->default_tc;
                        $eff_cap = min((int)$c->max_periods_per_day, $n_periods - max(0, (int)($c->min_free_per_day ?? 0)));
                        if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $eff_cap)                     { $all_free = false; break; }
                        if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)          { $all_free = false; break; }
                        if ($c->avoid_first_period && $first_pid === $this->period_order[0])                          { $all_free = false; break; }
                        if ($c->avoid_last_period  && $last_pid  === end($this->period_order))                        { $all_free = false; break; }
                        if ($c->preferred_start_time && isset($this->periods[$first_pid]) &&
                            $this->periods[$first_pid]->start_time < $c->preferred_start_time)                        { $all_free = false; break; }
                        if ($c->preferred_end_time && isset($this->periods[$last_pid]) &&
                            $this->periods[$last_pid]->end_time > $c->preferred_end_time)                             { $all_free = false; break; }
                        if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) { $all_free = false; break; }
                        if ($c->max_gap_per_day !== null && $this->_violatesGapRule($t_id, $day, $pid_group, (int)$c->max_gap_per_day)) { $all_free = false; break; }
                        foreach ($pid_group as $pid) {
                            if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $all_free = false; break 2; }
                            if (!empty($unavail_map[$t_id][$day][$pid]))        { $all_free = false; break 2; }
                        }
                    }
                    if (!$all_free) continue;

                    $room_id = $this->_findRoom($day, $pid_group, 'any', $pref_room, $primary, $constraints[$primary] ?? $this->default_tc);
                    $score   = $day_penalty - ($this->teacher_periods_day[$primary][$day] ?? 0) * 0.8;
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
                            $c       = $constraints[$t_id] ?? $this->default_tc;
                            $eff_cap = min((int)$c->max_periods_per_day, $n_periods - max(0, (int)($c->min_free_per_day ?? 0)));
                            if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $eff_cap)          continue;
                            if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week)  continue;
                            if ($c->avoid_first_period && $first_pid === $this->period_order[0])                  continue;
                            if ($c->avoid_last_period  && $last_pid  === end($this->period_order))                continue;
                            if ($c->preferred_start_time && isset($this->periods[$first_pid]) &&
                                $this->periods[$first_pid]->start_time < $c->preferred_start_time)               continue;
                            if ($c->preferred_end_time && isset($this->periods[$last_pid]) &&
                                $this->periods[$last_pid]->end_time > $c->preferred_end_time)                    continue;
                            $t_free = true;
                            foreach ($pid_group as $pid) {
                                if (!empty($this->teacher_occ[$t_id][$day][$pid])) { $t_free = false; break; }
                                if (!empty($unavail_map[$t_id][$day][$pid]))        { $t_free = false; break; }
                            }
                            if (!$t_free) continue;
                            if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) continue;
                            if ($c->max_gap_per_day !== null && $this->_violatesGapRule($t_id, $day, $pid_group, (int)$c->max_gap_per_day)) continue;
                        } else {
                            $c = null;
                        }

                        $room_id = $this->_findRoom($day, $pid_group, 'any', $pref_room, $t_id, $c);
                        $score   = $day_penalty - ($this->teacher_periods_day[$t_id][$day] ?? 0) * 0.8;
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
     * Find a joint slot by displacing 1-2 non-joint, non-locked entries from
     * sections that block an otherwise-free slot. Displaced entries become
     * regular no_slot conflicts (much easier to resolve than a multi-section joint).
     */
    private function _findJointSlotDisplacing(
        $jl, array $teacher_ids, $all_teachers_required, $pref_room,
        $consec, $days_used, $max_per_day,
        $constraints, $unavail_map, array &$draft_entries, array &$conflicts
    ) {
        $primary = $teacher_ids[0] ?? null;
        $n_sections = count($jl->classes);
        $max_displace = min(2, $n_sections - 1);
        $best = null; $best_displace_count = 999;

        foreach ($this->working_days as $day) {
            foreach ($this->_getConsecutiveStarts($consec) as $pid_group) {
                // Check teacher availability at this slot
                $teacher_ok = true;
                $use_teacher = null;
                $candidates = !empty($teacher_ids) ? $teacher_ids : [null];
                foreach ($candidates as $t_id) {
                    if ($t_id === null) { $use_teacher = null; $teacher_ok = true; break; }
                    $t_free = true;
                    $c = $constraints[$t_id] ?? $this->default_tc;
                    foreach ($pid_group as $pid) {
                        if (!empty($this->teacher_occ[$t_id][$day][$pid]) || !empty($unavail_map[$t_id][$day][$pid])) {
                            $t_free = false; break;
                        }
                    }
                    if (!$t_free) continue;
                    if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > (int)$c->max_periods_per_day) continue;
                    if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > (int)$c->max_periods_per_week) continue;
                    $use_teacher = $t_id; break;
                }
                if ($all_teachers_required && !empty($teacher_ids)) {
                    $all_free = true;
                    foreach ($teacher_ids as $t_id) {
                        foreach ($pid_group as $pid) {
                            if (!empty($this->teacher_occ[$t_id][$day][$pid]) || !empty($unavail_map[$t_id][$day][$pid])) {
                                $all_free = false; break 2;
                            }
                        }
                    }
                    if (!$all_free) continue;
                    $use_teacher = $primary;
                } elseif ($use_teacher === null && !empty($teacher_ids)) {
                    continue;
                }

                // Count how many sections are blocked and identify displaceable entries
                $blocked = []; $unavail_blocked = false;
                foreach ($jl->classes as $cs) {
                    $cid = (int)$cs->class_id; $sid = (int)$cs->section_id;
                    foreach ($pid_group as $pid) {
                        if (!empty($this->class_unavail[$cid][$sid][$day][$pid])) {
                            $unavail_blocked = true; break 2;
                        }
                        if (!empty($this->class_occ[$cid][$sid][$day][$pid][0])) {
                            // Find the draft entry occupying this slot
                            foreach ($draft_entries as $di => $de) {
                                if ((int)$de['class_id'] === $cid && (int)$de['section_id'] === $sid
                                    && $de['day'] === $day && (int)$de['period_id'] === $pid
                                    && empty($de['is_free_period']) && empty($de['is_locked'])) {
                                    $sgs = (int)($de['subject_group_subject_id'] ?? 0);
                                    // Don't displace another joint entry
                                    $is_joint = false;
                                    foreach ($jl->classes as $cs2) {
                                        if (isset($this->subject_day_count[$cid][$sid][$sgs])) {
                                            // Check joint_peers — but we may not have it here
                                        }
                                    }
                                    $blocked[] = ['di' => $di, 'de' => $de, 'cid' => $cid, 'sid' => $sid, 'pid' => $pid];
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($unavail_blocked) continue;
                if (count($blocked) === 0) continue; // fully free = _findJointSlot would have found it
                if (count($blocked) > $max_displace) continue;
                if (count($blocked) >= $best_displace_count) continue;

                // This slot is viable — displace fewer entries than best so far
                $best = ['day' => $day, 'period_ids' => $pid_group, 'staff_id' => $use_teacher,
                         'room_id' => null, 'blocked' => $blocked];
                $best_displace_count = count($blocked);
                if ($best_displace_count === 1) break 2; // can't do better than 1
            }
        }

        if (!$best) return null;

        // Execute displacement: remove blocked entries, add them as no_slot conflicts
        foreach ($best['blocked'] as $b) {
            $de = $b['de'];
            $di = $b['di'];
            $cid = $b['cid']; $sid = $b['sid'];
            // Free occupancy
            unset($this->class_occ[$cid][$sid][$de['day']][(int)$de['period_id']][0]);
            $vt = (int)($de['staff_id'] ?? 0);
            if ($vt) {
                unset($this->teacher_occ[$vt][$de['day']][(int)$de['period_id']]);
                $this->teacher_periods_day[$vt][$de['day']] = max(0, ($this->teacher_periods_day[$vt][$de['day']] ?? 1) - 1);
                $this->teacher_periods_week[$vt] = max(0, ($this->teacher_periods_week[$vt] ?? 1) - 1);
            }
            // Remove from draft
            unset($draft_entries[$di]);
            // Add as no_slot conflict so swap repair can handle it
            $conflicts[] = [
                'class_id'   => $cid, 'section_id' => $sid,
                'subject'    => $de['subject_group_subject_id'] ?? '', 'staff' => '',
                'staff_id'   => $vt,
                'subject_group_subject_id' => (int)($de['subject_group_subject_id'] ?? 0),
                'subject_group_id' => (int)($de['subject_group_id'] ?? 0),
                'type'       => 'no_slot',
                'reason'     => 'Displaced by joint lesson placement',
                'placement'  => '1 of 1',
            ];
        }
        $draft_entries = array_values($draft_entries);

        return ['day' => $best['day'], 'period_ids' => $best['period_ids'],
                'staff_id' => $best['staff_id'], 'room_id' => $best['room_id']];
    }

    /**
     * Explains why an admin-pinned Fixed Slot could not be used for a joint lesson,
     * so the conflict report tells the admin exactly what to fix instead of just
     * saying "no slot found".
     */
    private function _diagnoseJointFixedFailure($jl, array $teacher_ids, $all_teachers_required, $day, array $pid_group, $constraints, $unavail_map, $pref_room)
    {
        foreach ($jl->classes as $cs) {
            foreach ($pid_group as $pid) {
                if (!empty($this->class_occ[(int)$cs->class_id][(int)$cs->section_id][$day][$pid][0])) {
                    return "class C{$cs->class_id}/S{$cs->section_id} already has another lesson scheduled at this slot";
                }
                if (!empty($this->class_unavail[(int)$cs->class_id][(int)$cs->section_id][$day][$pid])) {
                    return "class C{$cs->class_id}/S{$cs->section_id} is marked unavailable at this slot (Class Availability settings)";
                }
            }
        }

        $first_sgs = !empty($jl->classes[0]->sgs_id) ? (int)$jl->classes[0]->sgs_id : 0;
        if ($first_sgs) {
            foreach ($pid_group as $pid) {
                if (!empty($this->subject_unavail[$first_sgs][$day][$pid])) {
                    return 'this subject is BLOCKED at this slot in Subject Time-Off — unblock it there first';
                }
            }
        }

        if (empty($teacher_ids)) {
            $room_id = $this->_findRoom($day, $pid_group, 'any', $pref_room, null, $this->default_tc);
            if ($pref_room && !$room_id) return 'the preferred room is already booked at this slot';
            return 'slot should be available — please re-check teacher pool and try again';
        }

        $teacher_reasons  = [];
        $any_teacher_free = false;
        foreach ($teacher_ids as $t_id) {
            $why = $this->_diagnoseTeacherAtSlot($t_id, $day, $pid_group, $constraints, $unavail_map);
            if ($why === null) {
                $any_teacher_free = true;
                if (!$all_teachers_required) break;
            } else {
                $teacher_reasons[] = "teacher (ID {$t_id}) {$why}";
            }
        }

        if ($all_teachers_required && !empty($teacher_reasons)) {
            return 'all teachers must attend together, but ' . $teacher_reasons[0];
        }
        if (!$all_teachers_required && !$any_teacher_free) {
            return 'every teacher in the pool is unavailable at this slot — ' . implode('; ', $teacher_reasons);
        }

        $primary = $teacher_ids[0] ?? null;
        $room_id = $this->_findRoom($day, $pid_group, 'any', $pref_room, $primary, $constraints[$primary] ?? $this->default_tc);
        if ($pref_room && !$room_id) return 'the preferred room is already booked at this slot';

        return 'slot should be available — please re-check and try generating again';
    }

    private $_staff_names = [];
    private function _staffName($id) {
        if (!isset($this->_staff_names[$id])) {
            $r = $this->db->select('name, surname')->where('id', $id)->get('staff')->row();
            $this->_staff_names[$id] = $r ? trim($r->name . ' ' . ($r->surname ?? '')) : "Staff #{$id}";
        }
        return $this->_staff_names[$id];
    }

    private function _diagnoseTeacherAtSlot($t_id, $day, array $pid_group, $constraints, $unavail_map)
    {
        $c         = $constraints[$t_id] ?? $this->default_tc;
        $consec    = count($pid_group);
        $n_periods = count($this->period_order);
        $eff_cap   = min((int)$c->max_periods_per_day, $n_periods - max(0, (int)($c->min_free_per_day ?? 0)));

        if (($this->teacher_periods_day[$t_id][$day] ?? 0) + $consec > $eff_cap) {
            return "would exceed their daily period cap on {$day}";
        }
        if (($this->teacher_periods_week[$t_id] ?? 0) + $consec > $c->max_periods_per_week) {
            return 'would exceed their weekly period cap';
        }

        $first_pid = $pid_group[0];
        $last_pid  = end($pid_group);
        if ($c->avoid_first_period && $first_pid === $this->period_order[0]) {
            return 'is set to avoid the first period of the day';
        }
        if ($c->avoid_last_period && $last_pid === end($this->period_order)) {
            return 'is set to avoid the last period of the day';
        }
        if ($c->preferred_start_time && isset($this->periods[$first_pid]) &&
            $this->periods[$first_pid]->start_time < $c->preferred_start_time) {
            return 'this slot starts earlier than their preferred start time';
        }
        if ($c->preferred_end_time && isset($this->periods[$last_pid]) &&
            $this->periods[$last_pid]->end_time > $c->preferred_end_time) {
            return 'this slot ends later than their preferred end time';
        }
        foreach ($pid_group as $pid) {
            if (!empty($this->teacher_occ[$t_id][$day][$pid])) {
                return 'is already booked elsewhere at this slot';
            }
            if (!empty($unavail_map[$t_id][$day][$pid])) {
                return 'marked themselves unavailable at this slot';
            }
        }
        if ($this->_violatesConsecRule($t_id, $day, $pid_group, (int)($c->max_consecutive_periods ?? 0), (int)($c->min_break_after_consec ?? 1))) {
            return 'would exceed their max-consecutive-periods rule';
        }
        if ($c->max_gap_per_day !== null && $this->_violatesGapRule($t_id, $day, $pid_group, (int)$c->max_gap_per_day)) {
            return 'would violate their max-gap-per-day rule';
        }
        return null;
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

    /**
     * Returns true if placing $pid_group for $t_id on $day would create a free-period
     * gap BETWEEN teaching blocks that exceeds $max_gap.
     *
     * max_gap = 0   → no idle gap allowed between sessions (all teaching must be contiguous).
     * max_gap = 1   → one free period allowed between sessions.
     * null          → no limit (skip check).
     *
     * Example: teacher has P1, P2 and P5, P6 confirmed.
     * Gap between P2 and P5 = 2 free periods (P3, P4).
     * If max_gap=1 this is a violation; if max_gap=2 it is acceptable.
     */
    private function _violatesGapRule($t_id, $day, $pid_group, $max_gap)
    {
        $period_idx = array_flip($this->period_order);

        $occupied = [];
        foreach ($this->period_order as $idx => $pid) {
            if (!empty($this->teacher_occ[$t_id][$day][$pid])) $occupied[$idx] = true;
        }
        foreach ($pid_group as $pid) {
            if (isset($period_idx[$pid])) $occupied[$period_idx[$pid]] = true;
        }
        if (count($occupied) < 2) return false;

        $idxs = array_keys($occupied);
        sort($idxs);

        // Build consecutive runs, measure gaps between them
        $run_end = $idxs[0];
        for ($i = 1; $i < count($idxs); $i++) {
            $curr = $idxs[$i];
            if ($curr > $run_end + 1) {
                // Gap found between runs
                $gap = $curr - $run_end - 1;
                if ($gap > $max_gap) return true;
            }
            $run_end = $curr;
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
                $d['is_free_period'] = (int) ($d['is_free_period'] ?? 0);
                $d['free_period_label'] = $d['free_period_label'] ?? null;
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

    /**
     * Standalone gap-fill for the LIVE timetable (tt_entries), callable from
     * the Class Grid's "Fill Empty Cells" button. Loads current occupancy from
     * tt_entries, runs the same _fillEmptyCells logic used during generation,
     * and writes results directly to tt_entries.
     */
    public function fillEmptyCellsLive($session_id, $class_id, $section_id)
    {
        $this->session_id = $session_id;
        $this->_loadWorkingDays(['allow_saturday' => 1]);
        $this->_loadPeriods();

        if (empty($this->periods)) {
            return ['status' => '0', 'message' => 'No periods configured.'];
        }

        // Build occupancy from ALL existing live entries for this session
        $this->class_occ      = [];
        $this->teacher_occ    = [];
        $this->room_occ       = [];
        $this->teacher_periods_day  = [];
        $this->teacher_periods_week = [];
        $this->subject_day_count    = [];
        $this->subject_day_periods  = [];

        // Delete existing Free Period placeholders for this class — they'll be
        // re-created by _fillEmptyCells if still needed, or replaced with a real
        // subject if one fits. Without this, the gap-fill sees those cells as
        // "occupied" and reports "no empty cells" even when the user sees green
        // Free Period tags they want to replace.
        $this->db->where('session_id', $session_id)
            ->where('class_id', $class_id)->where('section_id', $section_id)
            ->where('is_free_period', 1)
            ->delete('tt_entries');

        $all_entries = $this->db->where('session_id', $session_id)->get('tt_entries')->result();
        foreach ($all_entries as $e) {
            $bk = $e->batch_id ?: 0;
            $this->class_occ[(int)$e->class_id][(int)$e->section_id][$e->day][(int)$e->period_id][$bk] = true;
            if ($e->staff_id) {
                $this->teacher_occ[(int)$e->staff_id][$e->day][(int)$e->period_id] = true;
                $this->teacher_periods_day[(int)$e->staff_id][$e->day] =
                    ($this->teacher_periods_day[(int)$e->staff_id][$e->day] ?? 0) + 1;
                $this->teacher_periods_week[(int)$e->staff_id] =
                    ($this->teacher_periods_week[(int)$e->staff_id] ?? 0) + 1;
            }
            if ($e->room_id) {
                $this->room_occ[(int)$e->room_id][$e->day][(int)$e->period_id] =
                    ($this->room_occ[(int)$e->room_id][$e->day][(int)$e->period_id] ?? 0) + 1;
            }
            if ($e->subject_group_subject_id) {
                $this->subject_day_count[(int)$e->class_id][(int)$e->section_id][(int)$e->subject_group_subject_id][$e->day] =
                    ($this->subject_day_count[(int)$e->class_id][(int)$e->section_id][(int)$e->subject_group_subject_id][$e->day] ?? 0) + 1;
            }
        }

        $this->_loadClassUnavail($session_id);
        $this->_loadSubjectUnavail($session_id);

        $this->CI->load->model('Tt_teacher_model');
        $constraints = $this->CI->Tt_teacher_model->getAllConstraintsMap($session_id);
        $unavail_map = $this->CI->Tt_teacher_model->getUnavailabilityMap($session_id);

        $this->default_tc = (object)[
            'max_periods_per_day'     => 6, 'max_periods_per_week' => 36,
            'min_free_per_day'        => 0, 'max_gap_per_day'      => null,
            'avoid_first_period'      => 0, 'avoid_last_period'    => 0,
            'preferred_start_time'    => null, 'preferred_end_time' => null,
            'preferred_room_id'       => null, 'max_consecutive_periods' => 0,
            'min_break_after_consec'  => 1,
        ];

        // Load ALL subject loads for this class — including joint-lesson-linked
        // rows — as gap-fill candidates. getAllForClassScope excludes joint rows
        // (since the generator handles them separately), but for gap-filling a
        // single cell, any teacher who teaches ANY subject to this class is a
        // valid candidate regardless of whether it's a joint or regular subject.
        $this->CI->load->model('Tt_subjectload_model');
        $base_loads = $this->CI->Tt_subjectload_model->getForClassSection($session_id, $class_id, $section_id);
        $base_loads = array_values(array_filter($base_loads, fn($l) => !empty($l->subject_id)));

        $class_stats = [$class_id . '_' . $section_id => ['class_id' => $class_id, 'section_id' => $section_id]];

        // Pre-count how many periods each subject already has in the LIVE
        // timetable — without this, the gap-fill thinks everything is at 0
        // and happily exceeds weekly caps (e.g. Library 1/week gets filled 3x).
        $existing_counts = [];
        $live_entries = $this->db->where('session_id', $session_id)
            ->where('class_id', $class_id)->where('section_id', $section_id)
            ->where('is_free_period', 0)
            ->get('tt_entries')->result();
        foreach ($live_entries as $le) {
            if (!$le->subject_group_subject_id) continue;
            $k = $class_id . '_' . $section_id . '_' . (int)$le->subject_group_subject_id;
            $existing_counts[$k] = ($existing_counts[$k] ?? 0) + 1;
        }

        // ---- CROSS-CLASS SWAP: free up teachers by relocating their
        //      entries in OTHER classes to different time slots ----
        //      Handles two cases:
        //      1) T is busy at exact slot → move that entry elsewhere
        //      2) T is free at slot but daily cap blocks → move one of T's
        //         entries on that day to a different day to reduce the count
        $entry_by_teacher = [];
        $entry_by_class   = [];
        foreach ($all_entries as $e) {
            if ($e->staff_id && !$e->is_free_period) {
                $entry_by_teacher[(int)$e->staff_id][$e->day][(int)$e->period_id] = $e;
                $entry_by_class[(int)$e->class_id][(int)$e->section_id][$e->day][(int)$e->period_id] = $e;
            }
        }

        // Joint peers for synchronized moves
        $joint_peers = [];
        $jl_data = $this->db->select('class_id, section_id, subject_group_subject_id, joint_lesson_id')
            ->from('tt_subject_load')
            ->where('session_id', $session_id)
            ->where('joint_lesson_id IS NOT NULL', null, false)
            ->get()->result();
        $by_jl = [];
        foreach ($jl_data as $r) {
            $by_jl[(int)$r->joint_lesson_id][] = [
                'class_id' => (int)$r->class_id, 'section_id' => (int)$r->section_id,
                'sgs_id' => (int)$r->subject_group_subject_id,
            ];
        }
        foreach ($by_jl as $classes) {
            if (count($classes) < 2) continue;
            foreach ($classes as $c) {
                $joint_peers[$c['class_id']][$c['section_id']][$c['sgs_id']] = $classes;
            }
        }

        $unmet = [];
        foreach ($base_loads as $load) {
            $sgs = (int) $load->subject_group_subject_id;
            $k   = $class_id . '_' . $section_id . '_' . $sgs;
            $placed = $existing_counts[$k] ?? 0;
            $needed = (int) $load->periods_per_week;
            if ($placed >= $needed) continue;
            $t_ids = $load->teacher_ids ?? [];
            if (empty($t_ids) && !empty($load->staff_id)) $t_ids[] = (int) $load->staff_id;
            if (empty($t_ids)) continue;
            for ($p = $placed; $p < $needed; $p++) {
                $unmet[] = [
                    'staff_id' => $t_ids[0],
                    'sgs_id'   => $sgs,
                    'sgid'     => (int) $load->subject_group_id,
                ];
            }
        }

        $cross_swapped = 0;
        for ($iter = 0; $iter < 10 && !empty($unmet); $iter++) {
            $swapped_this_round = 0;
            $still_unmet = [];

            foreach ($unmet as $u) {
                $t_id = (int) $u['staff_id'];
                $tc   = $constraints[$t_id] ?? $this->default_tc;
                $wk   = $this->teacher_periods_week[$t_id] ?? 0;
                $cap  = (int)$tc->max_periods_per_week;

                if ($cap > 0 && $wk === $cap) { continue; }

                $n_periods = count($this->period_order);
                $eff_cap = min((int)$tc->max_periods_per_day,
                    $n_periods - max(0, (int)($tc->min_free_per_day ?? 0)));

                $resolved = false;

                foreach ($this->working_days as $day) {
                    if ($resolved) break;
                    foreach ($this->period_order as $pid) {
                        if ($resolved) break;

                        if (!empty($this->class_occ[$class_id][$section_id][$day][$pid][0])) continue;
                        if (!empty($this->class_unavail[$class_id][$section_id][$day][$pid])) continue;
                        if ($u['sgs_id'] && !empty($this->subject_unavail[$u['sgs_id']][$day][$pid])) continue;
                        if (!empty($unavail_map[$t_id][$day][$pid])) continue;

                        $t_busy_here   = !empty($this->teacher_occ[$t_id][$day][$pid]);
                        $day_count     = $this->teacher_periods_day[$t_id][$day] ?? 0;
                        $need_diff_day = ($day_count + 1 > $eff_cap);

                        if (!$t_busy_here && !$need_diff_day) continue;

                        // If T is free and caps already exceeded, place directly
                        if (!$t_busy_here && ($day_count > $eff_cap || ($cap > 0 && $wk > $cap))) {
                            $this->db->insert('tt_entries', [
                                'session_id' => $session_id,
                                'class_id' => $class_id, 'section_id' => $section_id,
                                'subject_group_id' => $u['sgid'],
                                'subject_group_subject_id' => $u['sgs_id'],
                                'staff_id' => $t_id, 'period_id' => $pid,
                                'day' => $day, 'room_id' => null, 'batch_id' => null,
                                'is_free_period' => 0, 'free_period_label' => null,
                                'entry_type' => 'auto', 'is_locked' => 0,
                            ]);
                            $this->class_occ[$class_id][$section_id][$day][$pid][0] = true;
                            $this->teacher_occ[$t_id][$day][$pid] = true;
                            $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                            if ($u['sgs_id']) {
                                $this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$day] =
                                    ($this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$day] ?? 0) + 1;
                            }
                            $existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] =
                                ($existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] ?? 0) + 1;
                            $swapped_this_round++;
                            $resolved = true;
                            break;
                        }

                        // Build candidate blockers
                        $blocker_candidates = [];
                        if ($t_busy_here) {
                            $bl = $entry_by_teacher[$t_id][$day][$pid] ?? null;
                            if ($bl && empty($bl->is_locked)                                && !((int)$bl->class_id === $class_id && (int)$bl->section_id === $section_id)) {
                                $blocker_candidates[] = $bl;
                            }
                        }
                        if ($need_diff_day && !$t_busy_here) {
                            foreach ($this->period_order as $cp) {
                                $bl = $entry_by_teacher[$t_id][$day][$cp] ?? null;
                                if (!$bl || !empty($bl->is_locked)) continue;
                                if ((int)$bl->class_id === $class_id && (int)$bl->section_id === $section_id) continue;
                                $blocker_candidates[] = $bl;
                            }
                        }
                        if (empty($blocker_candidates)) continue;

                        foreach ($blocker_candidates as $blocker) {
                            if ($resolved) break;
                            $b_cid = (int) $blocker->class_id;
                            $b_sid = (int) $blocker->section_id;
                            $b_sgs = (int) $blocker->subject_group_subject_id;
                            $b_orig_day = $blocker->day;
                            $b_orig_pid = (int) $blocker->period_id;

                            // Depth-1: relocate blocker to an empty slot
                            foreach ($this->working_days as $day2) {
                                if ($resolved) break;
                                if ($need_diff_day && $day2 === $day) continue;

                                foreach ($this->period_order as $pid2) {
                                    if ($day2 === $b_orig_day && $pid2 === $b_orig_pid) continue;

                                    if (!empty($this->class_occ[$b_cid][$b_sid][$day2][$pid2][0])) continue;
                                    if (!empty($this->class_unavail[$b_cid][$b_sid][$day2][$pid2])) continue;
                                    if (!empty($this->teacher_occ[$t_id][$day2][$pid2])) continue;
                                    if (!empty($unavail_map[$t_id][$day2][$pid2])) continue;
                                    if ($b_sgs && !empty($this->subject_unavail[$b_sgs][$day2][$pid2])) continue;

                                    if ($day2 !== $day) {
                                        $d2c = $this->teacher_periods_day[$t_id][$day2] ?? 0;
                                        if ($d2c === $eff_cap) continue;
                                    } else {
                                        if ($day_count === $eff_cap) continue;
                                    }

                                    // Joint sync check
                                    $b_jp = $joint_peers[$b_cid][$b_sid][$b_sgs] ?? null;
                                    if ($b_jp && !$this->_jointPeersCanMove($b_jp, $b_cid, $b_sid, $day2, $pid2)) continue;

                                    $this->db->where('id', $blocker->id)->update('tt_entries', [
                                        'day' => $day2, 'period_id' => $pid2,
                                    ]);
                                    $this->db->insert('tt_entries', [
                                        'session_id' => $session_id,
                                        'class_id' => $class_id, 'section_id' => $section_id,
                                        'subject_group_id' => $u['sgid'],
                                        'subject_group_subject_id' => $u['sgs_id'],
                                        'staff_id' => $t_id, 'period_id' => $pid,
                                        'day' => $day, 'room_id' => null, 'batch_id' => null,
                                        'is_free_period' => 0, 'free_period_label' => null,
                                        'entry_type' => 'auto', 'is_locked' => 0,
                                    ]);

                                    unset($this->class_occ[$b_cid][$b_sid][$b_orig_day][$b_orig_pid][0]);
                                    $this->class_occ[$b_cid][$b_sid][$day2][$pid2][0] = true;
                                    unset($this->teacher_occ[$t_id][$b_orig_day][$b_orig_pid]);
                                    $this->teacher_occ[$t_id][$day2][$pid2] = true;
                                    $this->teacher_periods_day[$t_id][$b_orig_day] = max(0, ($this->teacher_periods_day[$t_id][$b_orig_day] ?? 1) - 1);
                                    $this->teacher_periods_day[$t_id][$day2] = ($this->teacher_periods_day[$t_id][$day2] ?? 0) + 1;
                                    if ($b_sgs) {
                                        $this->subject_day_count[$b_cid][$b_sid][$b_sgs][$b_orig_day] =
                                            max(0, ($this->subject_day_count[$b_cid][$b_sid][$b_sgs][$b_orig_day] ?? 1) - 1);
                                        $this->subject_day_count[$b_cid][$b_sid][$b_sgs][$day2] =
                                            ($this->subject_day_count[$b_cid][$b_sid][$b_sgs][$day2] ?? 0) + 1;

                                    // Move joint peers in DB
                                    if ($b_jp) $this->_moveJointPeersLive($b_jp, $b_cid, $b_sid, $b_orig_day, $b_orig_pid, $day2, $pid2, $session_id, $entry_by_class);
                                    }

                                    $this->class_occ[$class_id][$section_id][$day][$pid][0] = true;
                                    $this->teacher_occ[$t_id][$day][$pid] = true;
                                    $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                                    $this->teacher_periods_week[$t_id]     = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                                    if ($u['sgs_id']) {
                                        $this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$day] =
                                            ($this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$day] ?? 0) + 1;
                                    }

                                    unset($entry_by_teacher[$t_id][$b_orig_day][$b_orig_pid]);
                                    $blocker->day = $day2; $blocker->period_id = $pid2;
                                    $entry_by_teacher[$t_id][$day2][$pid2] = $blocker;

                                    $existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] =
                                        ($existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] ?? 0) + 1;

                                    $swapped_this_round++;
                                    $resolved = true;
                                    break;
                                }
                            }

                            // Depth-2: blocker class full — swap two entries within it
                            // Skip if blocker is joint (too complex to sync double-swap)
                            if (!$resolved && empty($joint_peers[$b_cid][$b_sid][$b_sgs])) {
                                foreach ($this->working_days as $day3) {
                                    if ($resolved) break;
                                    foreach ($this->period_order as $pid3) {
                                        if ($day3 === $b_orig_day && $pid3 === $b_orig_pid) continue;
                                        if (!empty($this->teacher_occ[$t_id][$day3][$pid3])) continue;
                                        $swap_e = $entry_by_class[$b_cid][$b_sid][$day3][$pid3] ?? null;
                                        if (!$swap_e || !empty($swap_e->is_locked)) continue;
                                        if (!empty($joint_peers[$b_cid][$b_sid][(int)($swap_e->subject_group_subject_id ?? 0)])) continue;
                                        $t2_id = (int) $swap_e->staff_id;
                                        if ($t2_id === $t_id) continue;
                                        if (!empty($this->teacher_occ[$t2_id][$b_orig_day][$b_orig_pid])) continue;
                                        if (!empty($unavail_map[$t2_id][$b_orig_day][$b_orig_pid])) continue;
                                        if (!empty($unavail_map[$t_id][$day3][$pid3])) continue;

                                        if ($day3 !== $b_orig_day) {
                                            $t2_tc = $constraints[$t2_id] ?? $this->default_tc;
                                            $t2_eff = min((int)$t2_tc->max_periods_per_day,
                                                $n_periods - max(0, (int)($t2_tc->min_free_per_day ?? 0)));
                                            $t2_dc = $this->teacher_periods_day[$t2_id][$b_orig_day] ?? 0;
                                            if ($t2_dc === $t2_eff) continue;
                                        }

                                        $this->db->where('id', $blocker->id)->update('tt_entries', ['day' => $day3, 'period_id' => $pid3]);
                                        $this->db->where('id', $swap_e->id)->update('tt_entries', ['day' => $b_orig_day, 'period_id' => $b_orig_pid]);
                                        $this->db->insert('tt_entries', [
                                            'session_id' => $session_id,
                                            'class_id' => $class_id, 'section_id' => $section_id,
                                            'subject_group_id' => $u['sgid'],
                                            'subject_group_subject_id' => $u['sgs_id'],
                                            'staff_id' => $t_id, 'period_id' => $b_orig_pid,
                                            'day' => $b_orig_day, 'room_id' => null, 'batch_id' => null,
                                            'is_free_period' => 0, 'free_period_label' => null,
                                            'entry_type' => 'auto', 'is_locked' => 0,
                                        ]);

                                        unset($this->teacher_occ[$t_id][$b_orig_day][$b_orig_pid]);
                                        $this->teacher_occ[$t_id][$day3][$pid3] = true;
                                        $this->teacher_occ[$t_id][$b_orig_day][$b_orig_pid] = true;
                                        $this->teacher_periods_day[$t_id][$b_orig_day] = max(0, ($this->teacher_periods_day[$t_id][$b_orig_day] ?? 1) - 1);
                                        $this->teacher_periods_day[$t_id][$day3] = ($this->teacher_periods_day[$t_id][$day3] ?? 0) + 1;
                                        $this->teacher_periods_day[$t_id][$b_orig_day] = ($this->teacher_periods_day[$t_id][$b_orig_day] ?? 0) + 1;
                                        $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                        unset($this->teacher_occ[$t2_id][$day3][$pid3]);
                                        $this->teacher_occ[$t2_id][$b_orig_day][$b_orig_pid] = true;
                                        if ($day3 !== $b_orig_day) {
                                            $this->teacher_periods_day[$t2_id][$day3] = max(0, ($this->teacher_periods_day[$t2_id][$day3] ?? 1) - 1);
                                            $this->teacher_periods_day[$t2_id][$b_orig_day] = ($this->teacher_periods_day[$t2_id][$b_orig_day] ?? 0) + 1;
                                        }

                                        $this->class_occ[$class_id][$section_id][$b_orig_day][$b_orig_pid][0] = true;
                                        if ($u['sgs_id']) {
                                            $this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$b_orig_day] =
                                                ($this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$b_orig_day] ?? 0) + 1;
                                        }

                                        unset($entry_by_teacher[$t_id][$b_orig_day][$b_orig_pid]);
                                        $blocker->day = $day3; $blocker->period_id = $pid3;
                                        $entry_by_teacher[$t_id][$day3][$pid3] = $blocker;
                                        unset($entry_by_teacher[$t2_id][$day3][$pid3]);
                                        $swap_e->day = $b_orig_day; $swap_e->period_id = $b_orig_pid;
                                        $entry_by_teacher[$t2_id][$b_orig_day][$b_orig_pid] = $swap_e;
                                        $entry_by_class[$b_cid][$b_sid][$day3][$pid3] = $blocker;
                                        $entry_by_class[$b_cid][$b_sid][$b_orig_day][$b_orig_pid] = $swap_e;

                                        $existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] =
                                            ($existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] ?? 0) + 1;

                                        $swapped_this_round++;
                                        $resolved = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }

                // Within-class rearrangement: move an existing entry in THIS class
                // to the empty slot (if its teacher can work there), freeing a
                // slot where T CAN work. E.g. CS teacher blocked at P1 but free
                // at P3 — move English (whose teacher CAN do P1) from P3 to P1,
                // place CS at P3.
                if (!$resolved) {
                    foreach ($this->working_days as $ed) {
                        if ($resolved) break;
                        foreach ($this->period_order as $ep) {
                            if ($resolved) break;
                            if (!empty($this->class_occ[$class_id][$section_id][$ed][$ep][0])) continue;
                            if (!empty($this->class_unavail[$class_id][$section_id][$ed][$ep])) continue;

                            foreach ($this->working_days as $od) {
                                if ($resolved) break;
                                foreach ($this->period_order as $op) {
                                    if ($od === $ed && $op === $ep) continue;
                                    $me = $entry_by_class[$class_id][$section_id][$od][$op] ?? null;
                                    if (!$me || !empty($me->is_locked)) continue;
                                    $t2 = (int) $me->staff_id;

                                    // T must be free at the occupied slot
                                    if (!empty($this->teacher_occ[$t_id][$od][$op])) continue;
                                    if (!empty($unavail_map[$t_id][$od][$op])) continue;
                                    if ($u['sgs_id'] && !empty($this->subject_unavail[$u['sgs_id']][$od][$op])) continue;

                                    // T2 must be free at the empty slot
                                    if (!empty($this->teacher_occ[$t2][$ed][$ep])) continue;
                                    if (!empty($unavail_map[$t2][$ed][$ep])) continue;
                                    $ms = (int) $me->subject_group_subject_id;
                                    if ($ms && !empty($this->subject_unavail[$ms][$ed][$ep])) continue;

                                    // Joint sync
                                    $me_jp = $joint_peers[$class_id][$section_id][$ms] ?? null;
                                    if ($me_jp && !$this->_jointPeersCanMove($me_jp, $class_id, $section_id, $ed, $ep)) continue;

                                    // Daily caps
                                    if ($od !== $ed) {
                                        $t_od_c = $this->teacher_periods_day[$t_id][$od] ?? 0;
                                        if ($t_od_c === $eff_cap) continue;
                                        $t2_tc = $constraints[$t2] ?? $this->default_tc;
                                        $t2_eff = min((int)$t2_tc->max_periods_per_day,
                                            $n_periods - max(0, (int)($t2_tc->min_free_per_day ?? 0)));
                                        $t2_ed_c = $this->teacher_periods_day[$t2][$ed] ?? 0;
                                        if ($t2_ed_c === $t2_eff) continue;
                                    }

                                    // Move existing entry to empty slot
                                    $this->db->where('id', $me->id)->update('tt_entries', [
                                        'day' => $ed, 'period_id' => $ep,
                                    ]);
                                    if ($me_jp) $this->_moveJointPeersLive($me_jp, $class_id, $section_id, $od, $op, $ed, $ep, $session_id, $entry_by_class);
                                    // Place unmet subject at freed slot
                                    $this->db->insert('tt_entries', [
                                        'session_id' => $session_id,
                                        'class_id' => $class_id, 'section_id' => $section_id,
                                        'subject_group_id' => $u['sgid'],
                                        'subject_group_subject_id' => $u['sgs_id'],
                                        'staff_id' => $t_id, 'period_id' => $op,
                                        'day' => $od, 'room_id' => null, 'batch_id' => null,
                                        'is_free_period' => 0, 'free_period_label' => null,
                                        'entry_type' => 'auto', 'is_locked' => 0,
                                    ]);

                                    $this->class_occ[$class_id][$section_id][$ed][$ep][0] = true;
                                    unset($this->teacher_occ[$t2][$od][$op]);
                                    $this->teacher_occ[$t2][$ed][$ep] = true;
                                    $this->teacher_occ[$t_id][$od][$op] = true;
                                    if ($od !== $ed) {
                                        $this->teacher_periods_day[$t2][$od] = max(0, ($this->teacher_periods_day[$t2][$od] ?? 1) - 1);
                                        $this->teacher_periods_day[$t2][$ed] = ($this->teacher_periods_day[$t2][$ed] ?? 0) + 1;
                                    }
                                    $this->teacher_periods_day[$t_id][$od] = ($this->teacher_periods_day[$t_id][$od] ?? 0) + 1;
                                    $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                                    if ($ms) {
                                        $this->subject_day_count[$class_id][$section_id][$ms][$od] =
                                            max(0, ($this->subject_day_count[$class_id][$section_id][$ms][$od] ?? 1) - 1);
                                        $this->subject_day_count[$class_id][$section_id][$ms][$ed] =
                                            ($this->subject_day_count[$class_id][$section_id][$ms][$ed] ?? 0) + 1;
                                    }
                                    if ($u['sgs_id']) {
                                        $this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$od] =
                                            ($this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$od] ?? 0) + 1;
                                    }

                                    unset($entry_by_class[$class_id][$section_id][$od][$op]);
                                    $me->day = $ed; $me->period_id = $ep;
                                    $entry_by_class[$class_id][$section_id][$ed][$ep] = $me;
                                    unset($entry_by_teacher[$t2][$od][$op]);
                                    $entry_by_teacher[$t2][$ed][$ep] = $me;

                                    $existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] =
                                        ($existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] ?? 0) + 1;

                                    $swapped_this_round++;
                                    $resolved = true;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Depth-3 chain: within-class swap blocked because T2 is busy
                // at the empty slot teaching class C3. Free T2 by moving C3's
                // entry, then complete the within-class swap.
                if (!$resolved) {
                    foreach ($this->working_days as $ed) {
                        if ($resolved) break;
                        foreach ($this->period_order as $ep) {
                            if ($resolved) break;
                            if (!empty($this->class_occ[$class_id][$section_id][$ed][$ep][0])) continue;
                            if (!empty($this->class_unavail[$class_id][$section_id][$ed][$ep])) continue;

                            foreach ($this->working_days as $od) {
                                if ($resolved) break;
                                foreach ($this->period_order as $op) {
                                    if ($resolved) break;
                                    if ($od === $ed && $op === $ep) continue;
                                    $me = $entry_by_class[$class_id][$section_id][$od][$op] ?? null;
                                    if (!$me || !empty($me->is_locked)) continue;
                                    if (!empty($joint_peers[$class_id][$section_id][(int)($me->subject_group_subject_id ?? 0)])) continue;
                                    $t2 = (int) $me->staff_id;
                                    if (!$t2) continue;

                                    if (!empty($this->teacher_occ[$t_id][$od][$op])) continue;
                                    if (!empty($unavail_map[$t_id][$od][$op])) continue;
                                    if ($u['sgs_id'] && !empty($this->subject_unavail[$u['sgs_id']][$od][$op])) continue;

                                    if (empty($this->teacher_occ[$t2][$ed][$ep])) continue;

                                    $c3e = $entry_by_teacher[$t2][$ed][$ep] ?? null;
                                    if (!$c3e || !empty($c3e->is_locked)) continue;
                                    $c3c = (int) $c3e->class_id; $c3s = (int) $c3e->section_id;
                                    if ($c3c === $class_id && $c3s === $section_id) continue;
                                    if (!empty($joint_peers[$c3c][$c3s][(int)($c3e->subject_group_subject_id ?? 0)])) continue;
                                    $c3sgs = (int) $c3e->subject_group_subject_id;

                                    // Try to move C3 entry to free T2 at (ed,ep)
                                    foreach ($this->working_days as $d3) {
                                        if ($resolved) break;
                                        foreach ($this->period_order as $p3) {
                                            if ($d3 === $ed && $p3 === $ep) continue;
                                            if (!empty($this->class_occ[$c3c][$c3s][$d3][$p3][0])) continue;
                                            if (!empty($this->class_unavail[$c3c][$c3s][$d3][$p3])) continue;
                                            if (!empty($this->teacher_occ[$t2][$d3][$p3])) continue;
                                            if (!empty($unavail_map[$t2][$d3][$p3])) continue;
                                            if ($c3sgs && !empty($this->subject_unavail[$c3sgs][$d3][$p3])) continue;

                                            if ($d3 !== $ed) {
                                                $t2_tc = $constraints[$t2] ?? $this->default_tc;
                                                $t2_eff = min((int)$t2_tc->max_periods_per_day,
                                                    $n_periods - max(0, (int)($t2_tc->min_free_per_day ?? 0)));
                                                if (($this->teacher_periods_day[$t2][$d3] ?? 0) === $t2_eff) continue;
                                            }

                                            // ---- 3-step chain ----
                                            // 1. Move C3 entry: T2 (ed,ep) → (d3,p3)
                                            $this->db->where('id', $c3e->id)->update('tt_entries', ['day' => $d3, 'period_id' => $p3]);

                                            // 2. Move target entry: T2 (od,op) → (ed,ep)
                                            $this->db->where('id', $me->id)->update('tt_entries', ['day' => $ed, 'period_id' => $ep]);

                                            // 3. Place T at (od,op)
                                            $this->db->insert('tt_entries', [
                                                'session_id' => $session_id,
                                                'class_id' => $class_id, 'section_id' => $section_id,
                                                'subject_group_id' => $u['sgid'],
                                                'subject_group_subject_id' => $u['sgs_id'],
                                                'staff_id' => $t_id, 'period_id' => $op,
                                                'day' => $od, 'room_id' => null, 'batch_id' => null,
                                                'is_free_period' => 0, 'free_period_label' => null,
                                                'entry_type' => 'auto', 'is_locked' => 0,
                                            ]);

                                            // Occupancy: C3 moves (ed,ep)→(d3,p3)
                                            unset($this->class_occ[$c3c][$c3s][$ed][$ep][0]);
                                            $this->class_occ[$c3c][$c3s][$d3][$p3][0] = true;
                                            unset($this->teacher_occ[$t2][$ed][$ep]);
                                            $this->teacher_occ[$t2][$d3][$p3] = true;
                                            if ($ed !== $d3) {
                                                $this->teacher_periods_day[$t2][$ed] = max(0, ($this->teacher_periods_day[$t2][$ed] ?? 1) - 1);
                                                $this->teacher_periods_day[$t2][$d3] = ($this->teacher_periods_day[$t2][$d3] ?? 0) + 1;
                                            }

                                            // Occupancy: target E moves (od,op)→(ed,ep)
                                            $this->teacher_occ[$t2][$ed][$ep] = true;
                                            unset($this->teacher_occ[$t2][$od][$op]);
                                            $this->class_occ[$class_id][$section_id][$ed][$ep][0] = true;
                                            if ($od !== $ed) {
                                                $this->teacher_periods_day[$t2][$od] = max(0, ($this->teacher_periods_day[$t2][$od] ?? 1) - 1);
                                                $this->teacher_periods_day[$t2][$ed] = ($this->teacher_periods_day[$t2][$ed] ?? 0) + 1;
                                            }

                                            // Occupancy: T placed at (od,op)
                                            $this->teacher_occ[$t_id][$od][$op] = true;
                                            $this->teacher_periods_day[$t_id][$od] = ($this->teacher_periods_day[$t_id][$od] ?? 0) + 1;
                                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;

                                            if ($u['sgs_id']) {
                                                $this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$od] =
                                                    ($this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$od] ?? 0) + 1;
                                            }

                                            // Update indexes
                                            unset($entry_by_teacher[$t2][$ed][$ep]);
                                            $c3e->day = $d3; $c3e->period_id = $p3;
                                            $entry_by_teacher[$t2][$d3][$p3] = $c3e;
                                            unset($entry_by_teacher[$t2][$od][$op]);
                                            $me->day = $ed; $me->period_id = $ep;
                                            $entry_by_teacher[$t2][$ed][$ep] = $me;
                                            unset($entry_by_class[$class_id][$section_id][$od][$op]);
                                            $entry_by_class[$class_id][$section_id][$ed][$ep] = $me;
                                            unset($entry_by_class[$c3c][$c3s][$ed][$ep]);
                                            $entry_by_class[$c3c][$c3s][$d3][$p3] = $c3e;

                                            $existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] =
                                                ($existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] ?? 0) + 1;

                                            $swapped_this_round++;
                                            $resolved = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Last resort: teacher over-booked, place even if double-booking
                if (!$resolved && $cap > 0 && $wk > $cap) {
                    foreach ($this->working_days as $day) {
                        if ($resolved) break;
                        foreach ($this->period_order as $pid) {
                            if (!empty($this->class_occ[$class_id][$section_id][$day][$pid][0])) continue;
                            if (!empty($this->class_unavail[$class_id][$section_id][$day][$pid])) continue;
                            if ($u['sgs_id'] && !empty($this->subject_unavail[$u['sgs_id']][$day][$pid])) continue;

                            $this->db->insert('tt_entries', [
                                'session_id' => $session_id,
                                'class_id' => $class_id, 'section_id' => $section_id,
                                'subject_group_id' => $u['sgid'],
                                'subject_group_subject_id' => $u['sgs_id'],
                                'staff_id' => $t_id, 'period_id' => $pid,
                                'day' => $day, 'room_id' => null, 'batch_id' => null,
                                'is_free_period' => 0, 'free_period_label' => null,
                                'entry_type' => 'auto', 'is_locked' => 0,
                            ]);
                            $this->class_occ[$class_id][$section_id][$day][$pid][0] = true;
                            $this->teacher_periods_day[$t_id][$day] = ($this->teacher_periods_day[$t_id][$day] ?? 0) + 1;
                            $this->teacher_periods_week[$t_id] = ($this->teacher_periods_week[$t_id] ?? 0) + 1;
                            if ($u['sgs_id']) {
                                $this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$day] =
                                    ($this->subject_day_count[$class_id][$section_id][$u['sgs_id']][$day] ?? 0) + 1;
                            }
                            $existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] =
                                ($existing_counts[$class_id . '_' . $section_id . '_' . $u['sgs_id']] ?? 0) + 1;
                            $swapped_this_round++;
                            $resolved = true;
                            break;
                        }
                    }
                }

                if (!$resolved) $still_unmet[] = $u;
            }

            $cross_swapped += $swapped_this_round;
            $unmet = $still_unmet;
            if ($swapped_this_round === 0) break;
        }

        $draft_entries = [];
        $filled_subject = 0; $filled_free = 0; $diagnostics = [];
        $this->_fillEmptyCells($class_stats, $base_loads, $constraints, $unavail_map,
            0, $session_id, $draft_entries, $filled_subject, $filled_free, $diagnostics, $existing_counts);

        // Write directly to tt_entries (live timetable)
        if (!empty($draft_entries)) {
            $live = [];
            foreach ($draft_entries as $d) {
                unset($d['gen_log_id']);
                $d['entry_type']  = 'auto';
                $d['is_locked']   = 0;
                $live[] = $d;
            }
            $this->db->insert_batch('tt_entries', $live);
        }

        return [
            'status'         => '1',
            'diagnostics'    => $diagnostics,
            'cross_swapped'  => $cross_swapped,
            'filled_subject' => $filled_subject,
            'filled_free'    => $filled_free,
        ];
    }
}
