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

    // Occupancy matrices
    private $class_occ      = [];     // [class_id][section_id][day][period_id][batch_key]
    private $teacher_occ    = [];     // [staff_id][day][period_id]
    private $room_occ       = [];     // [room_id][day][period_id]  (shared rooms: count instead of bool)

    private $teacher_periods_day  = [];
    private $teacher_periods_week = [];
    private $subject_day_count    = [];  // [class_id][section_id][sgs_id][day] = count

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

        $this->CI->load->model('Tt_teacher_model');
        $constraints = $this->CI->Tt_teacher_model->getAllConstraintsMap($session_id);
        $unavail_map = $this->CI->Tt_teacher_model->getUnavailabilityMap($session_id);

        $this->CI->load->model('Tt_subjectload_model');
        $loads = $this->CI->Tt_subjectload_model->getAllForClassScope($session_id, $class_scope);
        usort($loads, function($a, $b) {
            $score_a = ($a->consecutive_periods * 10) + $a->periods_per_week + $a->priority;
            $score_b = ($b->consecutive_periods * 10) + $b->periods_per_week + $b->priority;
            return $score_b - $score_a;
        });

        $log_id = $dry_run ? 0 : $this->_createLog($session_id, $staff_id, $class_scope, $settings);

        $draft_entries  = [];
        $conflicts      = [];
        $total_required = 0;
        $total_placed   = 0;

        foreach ($loads as $load) {
            $class_id   = (int) $load->class_id;
            $section_id = (int) $load->section_id;
            $staff_id_t = (int) $load->staff_id;
            $alt_staff  = !empty($load->alt_staff_id) ? (int)$load->alt_staff_id : null;
            $periods_pw = (int) $load->periods_per_week;
            $consec     = (int) $load->consecutive_periods;
            $batch_id   = $load->batch_id ? (int)$load->batch_id : null;
            $batch_key  = $batch_id ?: 0;
            $room_type  = $load->preferred_room_type ?? 'any';
            $pref_room  = !empty($load->preferred_room_id) ? (int)$load->preferred_room_id : null;
            $max_per_day   = (int) ($load->max_per_day ?? 2);
            $min_per_day   = !empty($load->min_per_day) ? 1 : 0;
            $dist_evenly   = !empty($load->distribute_evenly);
            $sgs_id        = (int) $load->subject_group_subject_id;

            // Teacher preferred room fallback
            if (!$pref_room && !empty($constraints[$staff_id_t]->preferred_room_id)) {
                $pref_room = (int) $constraints[$staff_id_t]->preferred_room_id;
            }

            $placements_needed = ($consec > 1)
                ? (int) ceil($periods_pw / $consec)
                : $periods_pw;

            $total_required += $placements_needed;
            $placed_count    = 0;
            $subject_days_used = [];

            for ($p = 0; $p < $placements_needed; $p++) {
                $slot = $this->_findBestSlot(
                    $class_id, $section_id, $batch_key, $sgs_id,
                    $staff_id_t, $alt_staff,
                    $consec, $subject_days_used,
                    $constraints, $unavail_map,
                    $room_type, $pref_room,
                    $max_per_day, $min_per_day, $dist_evenly
                );

                if ($slot === null) {
                    $conflicts[] = [
                        'class_id'   => $class_id,
                        'section_id' => $section_id,
                        'subject'    => $load->subject_name . ' (' . ($load->subject_code ?? '') . ')',
                        'staff'      => $load->staff_name . ' ' . $load->staff_surname,
                        'placement'  => ($p + 1) . ' of ' . $placements_needed,
                        'reason'     => 'No available slot — teacher fully booked, class full, or constraints block all options.',
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

                    if (!$dry_run) {
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
                }

                $subject_days_used[] = $slot['day'];
                $placed_count++;
                $total_placed++;
            }

            // On1 check: warn if subject didn't get placed on every working day
            if ($min_per_day && $placed_count > 0) {
                $days_covered = array_unique($subject_days_used);
                $missing_days = array_diff($this->working_days, $days_covered);
                foreach ($missing_days as $md) {
                    $conflicts[] = [
                        'class_id'   => $class_id,
                        'section_id' => $section_id,
                        'subject'    => $load->subject_name . ' (' . ($load->subject_code ?? '') . ')',
                        'staff'      => $load->staff_name . ' ' . $load->staff_surname,
                        'placement'  => 'On1',
                        'reason'     => "On1 violation: no slot placed on {$md}.",
                    ];
                }
            }
        }

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
            'dry_run'         => $dry_run,
        ];
    }

    private function _findBestSlot($class_id, $section_id, $batch_key, $sgs_id,
                                    $staff_id, $alt_staff,
                                    $consec, $days_used,
                                    $constraints, $unavail_map,
                                    $room_type, $pref_room,
                                    $max_per_day, $min_per_day, $dist_evenly)
    {
        $best       = null;
        $best_score = -999;

        foreach ($this->working_days as $day) {
            // Class unavailability check
            // (checked per-period below, but skip day early if all periods blocked)

            $day_subject_count = $this->subject_day_count[$class_id][$section_id][$sgs_id][$day] ?? 0;
            if ($day_subject_count >= $max_per_day) continue;

            $day_penalty  = ($dist_evenly && in_array($day, $days_used)) ? -10 : 0;
            $day_on1_bonus = ($min_per_day && $day_subject_count === 0 && !in_array($day, $days_used)) ? 8 : 0;

            $candidate_starts = $this->_getConsecutiveStarts($consec);

            foreach ($candidate_starts as $pid_group) {
                // Class free + class unavailability
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

                $teacher_candidates = [$staff_id];
                if ($alt_staff) $teacher_candidates[] = $alt_staff;

                foreach ($teacher_candidates as $t_id) {
                    $constraint = $constraints[$t_id] ?? null;
                    if ($constraint) {
                        $day_count  = $this->teacher_periods_day[$t_id][$day] ?? 0;
                        if ($day_count + $consec > $constraint->max_periods_per_day) continue;
                        $week_count = $this->teacher_periods_week[$t_id] ?? 0;
                        if ($week_count + $consec > $constraint->max_periods_per_week) continue;
                        if ($constraint->avoid_first_period && $pid_group[0] === $this->period_order[0]) continue;
                        if ($constraint->avoid_last_period  && end($pid_group) === end($this->period_order)) continue;
                    }

                    $teacher_free = true;
                    foreach ($pid_group as $pid) {
                        if (!empty($this->teacher_occ[$t_id][$day][$pid])) {
                            $teacher_free = false; break;
                        }
                        if (!empty($unavail_map[$t_id][$day][$pid])) {
                            $teacher_free = false; break;
                        }
                    }
                    if (!$teacher_free) continue;

                    $room_id = $this->_findRoom($day, $pid_group, $room_type, $pref_room, $t_id, $constraint);

                    // Score
                    $score = $day_penalty + $day_on1_bonus;
                    if ($t_id === $staff_id) $score += 5;
                    if ($room_id && $room_id === $pref_room) $score += 3;
                    // Prefer earlier in the week for spreading
                    $score -= array_search($day, $this->working_days) * 0.1;

                    if ($score > $best_score) {
                        $best_score = $score;
                        $best = [
                            'day'        => $day,
                            'period_ids' => $pid_group,
                            'staff_id'   => $t_id,
                            'room_id'    => $room_id,
                        ];
                    }
                }
            }
        }

        return $best;
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
