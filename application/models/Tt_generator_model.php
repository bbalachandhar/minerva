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

        usort($base_loads, function($a, $b) use ($teacher_unavail_count, $teacher_cap_tightness, $default_tightness) {
            // For each load, take the worst-constrained teacher in its pool.
            $ua = $uca = 0;
            foreach (($a->teacher_ids ?? []) as $tid) {
                $ua  = max($ua,  $teacher_unavail_count[$tid] ?? 0);
                $uca = max($uca, $teacher_cap_tightness[$tid] ?? $default_tightness);
            }
            $ub = $ucb = 0;
            foreach (($b->teacher_ids ?? []) as $tid) {
                $ub  = max($ub,  $teacher_unavail_count[$tid] ?? 0);
                $ucb = max($ucb, $teacher_cap_tightness[$tid] ?? $default_tightness);
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
                    // Fewer alternative teachers means fewer chances the search finds
                    // one free at any given slot — more constrained, so it should claim
                    // the shared slot pool before a same-priority lesson with a bigger
                    // pool. Needed as a tiebreak: two lessons can otherwise score
                    // identically (same priority/classes/periods/consecutive) and would
                    // fall back to undefined array order, same problem as before.
                    $cand_score = ((int)$cand->priority * 100) + ($n_classes * 10)
                                + ((int)$cand->periods_per_week) + ((int)$cand->consecutive_periods * 5)
                                + ($cand_ua * 0.5) + ($cand_dyn_tight * 0.3)
                                - ($pool_size * 0.1);
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
                            $reason = "Joint lesson [{$jl->name}] for {$class_labels}: no slot where all classes are simultaneously free.";
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
            // ---- END JOINT LESSON PRE-PASS ----

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
                    $cand_score = ($cand->consecutive_periods * 10) + $cand->periods_per_week + $cand->priority
                                + ($cand_ua * 0.5) + ($cand_dyn_tight * 0.3);
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

            // ---- GAP FILL (opt-in via fill_free_periods) ----
            // Genuine constraint-satisfaction is scored above via total_placed;
            // gap-fill runs after so it can't inflate that quality metric — it
            // only touches cells nothing above could place at all.
            $gap_filled_subject = 0; $gap_filled_free = 0;
            if (!empty($settings['fill_free_periods'])) {
                $this->_fillEmptyCells($class_stats, $base_loads, $constraints, $unavail_map,
                    $log_id, $session_id, $draft_entries, $gap_filled_subject, $gap_filled_free);
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

        foreach (array_keys($class_stats) as $ck) {
            [$class_id, $section_id] = array_map('intval', explode('_', $ck));
            $loads = $loads_by_class[$ck] ?? [];
            if (empty($loads)) continue;

            // ── PHASE 1: Subject-first fill ─────────────────────────────
            // Iterate subjects from fewest-periods-needed to most (most
            // constrained first — a 1/week subject has the least flexibility
            // and must get first pick of available slots, before a 7/week
            // subject's teacher consumes all the free time).
            $fill_queue = [];
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
                $fill_queue[] = ['load' => $load, 'needed' => $needed, 'sgs_id' => $sgs_id, 't_ids' => $t_ids];
            }
            usort($fill_queue, function ($a, $b) {
                if ($a['needed'] !== $b['needed']) return $a['needed'] <=> $b['needed'];
                return count($a['t_ids']) <=> count($b['t_ids']);
            });

            foreach ($fill_queue as $item) {
                $load    = $item['load'];
                $sgs_id  = $item['sgs_id'];
                $t_ids   = $item['t_ids'];
                $all_req = !empty($load->all_teachers_required);
                $subj_label = $load->subject_name ?? "sgs#{$sgs_id}";
                if (empty($t_ids)) continue;

                $remaining = $item['needed'];
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
            'filled_subject' => $filled_subject,
            'filled_free'    => $filled_free,
        ];
    }
}
