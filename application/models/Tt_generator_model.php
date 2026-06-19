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
        return $this->_generateViaMicroservice($session_id, $staff_id, $class_scope, $settings, false);
    }

    public function testGenerate($session_id, $class_scope, $settings)
    {
        return $this->_generateViaMicroservice($session_id, 0, $class_scope, $settings, true);
    }

    /**
     * Generate timetable via the Python OR-Tools CP-SAT microservice.
     * This is the ONLY generation engine — no PHP fallback.
     */
    private function _generateViaMicroservice($session_id, $staff_id, $class_scope, $settings, $dry_run = false)
    {
        $url = 'http://127.0.0.1:5050/generate';

        // Health check — return clear error if service is down
        $hc = @file_get_contents('http://127.0.0.1:5050/health', false,
            stream_context_create(['http' => ['timeout' => 2]]));
        if ($hc === false) {
            log_message('error', '[TT-Microservice] Service unreachable at localhost:5050');
            return [
                'status'  => '0',
                'message' => 'Timetable solver service is not running. Please contact the administrator.',
            ];
        }

        $this->CI->load->model('Tt_period_model');
        $this->CI->load->model('Tt_subjectload_model');
        $this->CI->load->model('Tt_joint_model');
        $this->CI->load->model('Tt_teacher_model');

        // Working days
        $this->CI->load->library('Customlib');
        $days_map = $this->CI->customlib->getDaysnameWithoutLang();
        $working_days = [];
        foreach (array_keys($days_map) as $d) {
            if ($d === 'Sunday') continue;
            if ($d === 'Saturday' && empty($settings['allow_saturday'])) continue;
            $working_days[] = $d;
        }

        // Periods (non-break only, sorted)
        $period_rows = $this->db->where('session_id', $session_id)
            ->where('is_break', 0)
            ->order_by('sort_order', 'ASC')
            ->get('tt_periods')->result();
        $periods = [];
        foreach ($period_rows as $p) {
            $periods[] = [
                'id' => (int)$p->id,
                'name' => $p->name,
                'start' => $p->start_time ?? '',
                'end' => $p->end_time ?? '',
                'sort_order' => (int)$p->sort_order,
            ];
        }
        if (empty($periods)) return ['status' => '0', 'message' => 'No period slots configured.'];

        // Class labels for human-readable solver diagnostics
        $class_labels = [];
        foreach ($class_scope as $cs) {
            $r = $this->db->select('classes.class, sections.section')
                ->from('classes')
                ->join('sections', 'sections.id = ' . (int)$cs['section_id'])
                ->where('classes.id', (int)$cs['class_id'])
                ->get()->row();
            if ($r) {
                $class_labels[$cs['class_id'] . '_' . $cs['section_id']] = trim($r->class . ' ' . $r->section);
            }
        }

        // Subject loads
        $raw_loads = $this->CI->Tt_subjectload_model->getAllForClassScope($session_id, $class_scope);
        $subject_loads = [];
        foreach ($raw_loads as $l) {
            $teacher_ids = $l->teacher_ids ?? [];
            if (empty($teacher_ids)) {
                if (!empty($l->staff_id))     $teacher_ids[] = (int)$l->staff_id;
                if (!empty($l->alt_staff_id)) $teacher_ids[] = (int)$l->alt_staff_id;
            }
            $subject_loads[] = [
                'class_id'           => (int)$l->class_id,
                'section_id'         => (int)$l->section_id,
                'sgs_id'             => (int)$l->subject_group_subject_id,
                'sg_id'              => (int)$l->subject_group_id,
                'subject_name'       => $l->subject_name ?? '',
                'teacher_name'       => trim(($l->staff_name ?? '') . ' ' . ($l->staff_surname ?? '')),
                'periods_per_week'   => (int)$l->periods_per_week,
                'consecutive'        => max(1, (int)($l->consecutive_periods ?? 1)),
                'teacher_ids'        => array_values(array_map('intval', $teacher_ids)),
                'all_teachers_required' => !empty($l->all_teachers_required),
                'max_per_day'        => max(1, (int)($l->max_per_day ?? 2)),
                'distribute_evenly'  => !empty($l->distribute_evenly),
                'priority'           => (int)($l->priority ?? 5),
                'batch_id'           => $l->batch_id ? (int)$l->batch_id : null,
            ];
        }

        // Staff name cache for joint lesson diagnostics
        $all_staff_ids = [];
        foreach ($raw_loads as $l) {
            if (!empty($l->staff_id)) $all_staff_ids[] = (int)$l->staff_id;
        }

        // Joint lessons (filtered by class_scope)
        $raw_joints = $this->CI->Tt_joint_model->getAllForGeneration($session_id);
        if (!empty($class_scope) && !empty($raw_joints)) {
            $scope_set = [];
            foreach ($class_scope as $cs) {
                $scope_set[$cs['class_id'].'_'.$cs['section_id']] = true;
            }
            foreach ($raw_joints as $jl) {
                $jl->classes = array_values(array_filter($jl->classes, function($cs) use ($scope_set) {
                    return isset($scope_set[$cs->class_id.'_'.$cs->section_id]);
                }));
            }
            $raw_joints = array_values(array_filter($raw_joints, fn($jl) => count($jl->classes) >= 1));
        }
        // Collect all joint teacher IDs and fetch names
        foreach ($raw_joints as $jl) {
            foreach ($jl->teacher_ids ?? [] as $tid) {
                $all_staff_ids[] = (int)$tid;
            }
        }
        $staff_name_map = [];
        if (!empty($all_staff_ids)) {
            $staff_rows = $this->db->select('id, name, surname')
                ->where_in('id', array_unique($all_staff_ids))
                ->get('staff')->result();
            foreach ($staff_rows as $sr) {
                $staff_name_map[(int)$sr->id] = trim($sr->name . ' ' . $sr->surname);
            }
        }

        $joint_lessons = [];
        foreach ($raw_joints as $jl) {
            $classes = [];
            foreach ($jl->classes as $cs) {
                $classes[] = [
                    'class_id'   => (int)$cs->class_id,
                    'section_id' => (int)$cs->section_id,
                    'sgs_id'     => (int)($cs->sgs_id ?? 0),
                    'sg_id'      => (int)($cs->sg_id ?? 0),
                ];
            }
            $t_ids = array_values(array_map('intval', $jl->teacher_ids ?? []));
            $t_names = [];
            foreach ($t_ids as $tid) {
                $t_names[] = $staff_name_map[$tid] ?? "Staff #{$tid}";
            }
            $joint_lessons[] = [
                'id'                    => (int)$jl->id,
                'name'                  => $jl->name ?? '',
                'subject_id'            => (int)($jl->subject_id ?? 0),
                'periods_per_week'      => (int)$jl->periods_per_week,
                'consecutive'           => max(1, (int)($jl->consecutive_periods ?? 1)),
                'teacher_ids'           => $t_ids,
                'teacher_names'         => $t_names,
                'all_teachers_required' => !empty($jl->all_teachers_required),
                'max_per_day'           => max(1, (int)($jl->max_per_day ?? 2)),
                'classes'               => $classes,
                'fixed_slots'           => $jl->fixed_slots ? json_decode($jl->fixed_slots, true) : null,
            ];
        }

        // Teacher constraints
        $constraints_raw = $this->CI->Tt_teacher_model->getAllConstraintsMap($session_id);
        $teacher_constraints = [];
        foreach ($constraints_raw as $tid => $c) {
            $teacher_constraints[(string)$tid] = [
                'max_per_day'        => (int)($c->max_periods_per_day ?? 6),
                'max_per_week'       => (int)($c->max_periods_per_week ?? 36),
                'avoid_first_period' => !empty($c->avoid_first_period),
                'avoid_last_period'  => !empty($c->avoid_last_period),
                'max_consecutive'    => (int)($c->max_consecutive_periods ?? 0),
            ];
        }

        // Teacher unavailability
        $unavail_raw = $this->CI->Tt_teacher_model->getUnavailabilityMap($session_id);
        $teacher_unavailability = [];
        foreach ($unavail_raw as $tid => $day_map) {
            $t_key = (string)$tid;
            $teacher_unavailability[$t_key] = [];
            foreach ($day_map as $day_name => $pid_map) {
                $teacher_unavailability[$t_key][$day_name] = array_values(array_map('intval', array_keys($pid_map)));
            }
        }

        // Class unavailability
        $class_unavailability = [];
        $cu_rows = $this->db->where('session_id', $session_id)->get('tt_class_unavail')->result();
        foreach ($cu_rows as $r) {
            $ck = $r->class_id . '_' . $r->section_id;
            $class_unavailability[$ck][$r->day][] = (int)$r->period_id;
        }

        // Subject unavailability
        $subject_unavailability = [];
        $su_rows = $this->db->select('tt_subject_unavail.*, subject_group_subjects.id as sgs_id')
            ->from('tt_subject_unavail')
            ->join('subject_group_subjects', 'subject_group_subjects.subject_id = tt_subject_unavail.subject_id', 'left')
            ->where('tt_subject_unavail.session_id', $session_id)
            ->get()->result();
        foreach ($su_rows as $r) {
            if ($r->sgs_id) {
                $subject_unavailability[(string)$r->sgs_id][$r->day][] = (int)$r->period_id;
            }
        }

        // Locked entries
        $locked_entries = [];
        $le_q = $this->db->select('*')->from('tt_entries')
            ->where('session_id', $session_id)
            ->where('is_locked', 1);
        if (!empty($class_scope)) {
            $le_q->group_start();
            foreach ($class_scope as $cs) {
                $le_q->or_group_start()
                    ->where('class_id', (int)$cs['class_id'])
                    ->where('section_id', (int)$cs['section_id'])
                    ->group_end();
            }
            $le_q->group_end();
        }
        foreach ($le_q->get()->result() as $e) {
            $locked_entries[] = [
                'class_id'                 => (int)$e->class_id,
                'section_id'               => (int)$e->section_id,
                'sgs_id'                   => (int)$e->subject_group_subject_id,
                'subject_group_subject_id' => (int)$e->subject_group_subject_id,
                'staff_id'                 => $e->staff_id ? (int)$e->staff_id : null,
                'period_id'                => (int)$e->period_id,
                'day'                      => $e->day,
            ];
        }

        $payload = json_encode([
            'working_days'            => $working_days,
            'periods'                 => $periods,
            'subject_loads'           => $subject_loads,
            'joint_lessons'           => $joint_lessons,
            'teacher_constraints'     => (object) $teacher_constraints,
            'teacher_unavailability'  => (object) $teacher_unavailability,
            'class_unavailability'    => (object) $class_unavailability,
            'subject_unavailability'  => (object) $subject_unavailability,
            'locked_entries'          => $locked_entries,
            'class_labels'            => (object) $class_labels,
            'settings' => [
                'max_same_subject_day' => (int)($settings['max_same_subject_day'] ?? 1),
                'fill_free_periods'    => !empty($settings['fill_free_periods']),
            ],
            'time_limit' => 180,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 240,
        ]);
        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        if ($response === false || $http_code !== 200) {
            log_message('error', '[TT-Microservice] HTTP error: ' . ($curl_err ?: "code $http_code"));
            return [
                'status'  => '0',
                'message' => 'Timetable solver returned an error. ' . ($curl_err ?: "HTTP $http_code"),
            ];
        }

        $result = json_decode($response, true);
        if (!$result) {
            return ['status' => '0', 'message' => 'Invalid response from timetable solver.'];
        }

        if (($result['status'] ?? '') === 'infeasible') {
            log_message('info', '[TT-Microservice] Infeasible: ' . ($result['message'] ?? ''));
            return [
                'status'  => '0',
                'message' => 'No valid timetable is possible with the current constraints. ' . ($result['message'] ?? ''),
            ];
        }

        if (($result['status'] ?? '') !== 'success') {
            return ['status' => '0', 'message' => $result['message'] ?? 'Solver error.'];
        }

        $total_required = (int)$result['total_required'];
        $total_placed   = (int)$result['total_placed'];
        $quality = ($total_required > 0)
            ? round(($total_placed / $total_required) * 100, 2)
            : 100.00;

        $log_id = 0;
        if (!$dry_run) {
            $log_id = $this->_createLog($session_id, $staff_id, $class_scope, $settings);

            $draft_entries = [];
            foreach ($result['entries'] as $e) {
                $draft_entries[] = [
                    'gen_log_id'               => $log_id,
                    'session_id'               => $session_id,
                    'class_id'                 => (int)$e['class_id'],
                    'section_id'               => (int)$e['section_id'],
                    'subject_group_id'         => (int)$e['subject_group_id'],
                    'subject_group_subject_id' => (int)$e['subject_group_subject_id'],
                    'staff_id'                 => $e['staff_id'] ? (int)$e['staff_id'] : null,
                    'period_id'                => (int)$e['period_id'],
                    'day'                      => $e['day'],
                    'room_id'                  => !empty($e['room_id']) ? (int)$e['room_id'] : null,
                    'batch_id'                 => !empty($e['batch_id']) ? (int)$e['batch_id'] : null,
                    'is_free_period'           => (int)($e['is_free_period'] ?? 0),
                    'free_period_label'        => $e['free_period_label'] ?? null,
                ];
            }

            if (!empty($draft_entries)) {
                foreach (array_chunk($draft_entries, 500) as $chunk) {
                    $this->db->insert_batch('tt_draft_entries', $chunk);
                }
            }

            $this->db->where('id', $log_id)->update('tt_gen_log', [
                'status'           => 'completed',
                'total_required'   => $total_required,
                'total_placed'     => $total_placed,
                'total_conflicts'  => count($result['unplaced'] ?? []),
                'quality_score'    => $quality,
                'conflict_details' => json_encode($result['unplaced'] ?? []),
            ]);
        }

        log_message('info', "[TT-Microservice] " . ($dry_run ? 'Dry run' : 'Success')
            . ": {$total_placed}/{$total_required} ({$quality}%) in {$result['solve_time_seconds']}s");

        return [
            'status'             => '1',
            'log_id'             => $log_id,
            'total_required'     => $total_required,
            'total_placed'       => $total_placed,
            'cards_placed'       => $total_placed,
            'cards_left'         => $total_required - $total_placed,
            'total_conflicts'    => 0,
            'quality_score'      => $quality,
            'conflicts'          => [],
            'unplaced'           => $result['unplaced'] ?? [],
            'issues'             => $result['issues'] ?? [],
            'class_stats'        => $result['class_stats'] ?? [],
            'dry_run'            => $dry_run,
            'gap_filled_subject' => 0,
            'gap_filled_free'    => 0,
            'engine'             => 'ortools',
            'solve_time_seconds' => $result['solve_time_seconds'] ?? 0,
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

    private function _loadClassUnavail($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_class_unavail')->result();
        foreach ($rows as $r) {
            $this->class_unavail[$r->class_id][$r->section_id][$r->day][$r->period_id] = true;
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
