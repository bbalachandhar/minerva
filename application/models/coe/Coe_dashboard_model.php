<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Coe_dashboard_model
 * Aggregated KPIs and pipeline status for the CoE central dashboard.
 */
class Coe_dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ---------------------------------------------------------------
    // System-level KPI cards for current session
    // ---------------------------------------------------------------
    public function getKPIs($session_id)
    {
        $sid = (int) $session_id;

        $total_events = $this->db
            ->from('exam_group_class_batch_exams egcbe')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('eg.is_active', 1)
            ->count_all_results();

        $total_apps = (int) $this->db
            ->select('COUNT(*) AS cnt')
            ->from('coe_exam_applications capp')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = capp.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->get()->row()->cnt;

        $pass_count = (int) $this->db
            ->select('COUNT(*) AS cnt')
            ->from('coe_sgpa_summary sg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sg.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('sg.result_status', 'pass')
            ->get()->row()->cnt;

        $total_computed = (int) $this->db
            ->select('COUNT(*) AS cnt')
            ->from('coe_sgpa_summary sg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sg.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->get()->row()->cnt;

        $published_events = $this->db
            ->from('exam_group_class_batch_exams egcbe')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('egcbe.is_publish', 1)
            ->count_all_results();

        $rev_pending = (int) $this->db
            ->select('COUNT(*) AS cnt')
            ->from('coe_revaluation_requests rr')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = rr.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where_in('rr.status', ['pending', 'assigned'])
            ->get()->row()->cnt;

        $ufm_count = (int) $this->db
            ->select('COUNT(*) AS cnt')
            ->from('coe_ufm_incidents ufm')
            ->join('coe_hall_tickets ht', 'ht.id = ufm.coe_hall_ticket_id')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = ht.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->get()->row()->cnt;

        $arrear_students = (int) $this->db
            ->select('COUNT(DISTINCT sr.student_id) AS cnt')
            ->from('coe_student_results sr')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sr.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('sr.result_status', 'fail')
            ->get()->row()->cnt;

        $pass_pct = ($total_computed > 0) ? round($pass_count / $total_computed * 100, 1) : 0;

        $total_ht = (int) $this->db
            ->select('COUNT(*) AS cnt')
            ->from('coe_hall_tickets ht')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = ht.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->get()->row()->cnt;

        $avg_sgpa_row = $this->db
            ->select('ROUND(AVG(sg.sgpa), 2) AS avg_sgpa')
            ->from('coe_sgpa_summary sg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sg.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->get()->row();
        $avg_sgpa = $avg_sgpa_row ? (float) $avg_sgpa_row->avg_sgpa : 0.0;

        return [
            'total_events'    => $total_events,
            'total_apps'      => $total_apps,
            'pass_pct'        => $pass_pct,
            'published_events'=> $published_events,
            'rev_pending'     => $rev_pending,
            'ufm_count'       => $ufm_count,
            'arrear_students' => $arrear_students,
            'total_computed'  => $total_computed,
            'total_ht'        => $total_ht,
            'avg_sgpa'        => $avg_sgpa,
        ];
    }

    // ---------------------------------------------------------------
    // Per-event pipeline counts
    // ---------------------------------------------------------------
    public function getEventPipeline($session_id)
    {
        $sid = (int) $session_id;

        $events = $this->db
            ->select('egcbe.id AS batch_exam_id, egcbe.exam, egcbe.date_from, egcbe.date_to,
                      egcbe.is_publish, eg.name AS event_name, eg.exam_category, s.session')
            ->from('exam_group_class_batch_exams egcbe')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->join('sessions s', 's.id = egcbe.session_id', 'left')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('eg.is_active', 1)
            ->order_by('egcbe.date_from', 'DESC')
            ->get()->result();

        foreach ($events as &$evt) {
            $bid = (int) $evt->batch_exam_id;

            $evt->app_count    = $this->_cnt('coe_exam_applications',   'exam_group_class_batch_exam_id', $bid);
            $evt->ht_count     = $this->_cnt('coe_hall_tickets',        'exam_group_class_batch_exam_id', $bid);
            $evt->nr_count     = $this->_cnt('coe_nominal_rolls',       'exam_group_class_batch_exam_id', $bid);
            $evt->rooms_count  = $this->_cnt('coe_seating_rooms',       'exam_group_class_batch_exam_id', $bid);
            $evt->qpd_count    = $this->_cnt('coe_qpd_papers',          'exam_group_class_batch_exam_id', $bid);
            $evt->script_count = $this->_cnt('coe_answer_scripts',      'exam_group_class_batch_exam_id', $bid);
            $evt->mod_count    = $this->_cnt('coe_moderation_rules',    'exam_group_class_batch_exam_id', $bid);
            $evt->rev_count    = $this->_cnt('coe_revaluation_requests','exam_group_class_batch_exam_id', $bid);

            $evt->marks_students = (int) $this->db
                ->select('COUNT(DISTINCT student_id) AS cnt')
                ->from('coe_student_results')
                ->where('exam_group_class_batch_exam_id', $bid)
                ->get()->row()->cnt;

            $evt->sgpa_count   = $this->_cnt('coe_sgpa_summary',       'exam_group_class_batch_exam_id', $bid);

            $pass = (int) $this->db
                ->select('COUNT(*) AS cnt')
                ->from('coe_sgpa_summary')
                ->where('exam_group_class_batch_exam_id', $bid)
                ->where('result_status', 'pass')
                ->get()->row()->cnt;

            $fail = (int) $this->db
                ->select('COUNT(*) AS cnt')
                ->from('coe_sgpa_summary')
                ->where('exam_group_class_batch_exam_id', $bid)
                ->where('result_status', 'fail')
                ->get()->row()->cnt;

            $evt->pass_count   = $pass;
            $evt->fail_count   = $fail;
            $evt->pass_pct     = ($pass + $fail) > 0 ? round($pass / ($pass + $fail) * 100, 1) : null;

            $evt->ufm_count    = (int) $this->db
                ->select('COUNT(*) AS cnt')
                ->from('coe_ufm_incidents ufm')
                ->join('coe_hall_tickets ht', 'ht.id = ufm.coe_hall_ticket_id')
                ->where('ht.exam_group_class_batch_exam_id', $bid)
                ->get()->row()->cnt;
        }

        return $events;
    }

    // ---------------------------------------------------------------
    // Recent CoE audit log
    // ---------------------------------------------------------------
    public function getRecentAudit($limit = 15)
    {
        return $this->db
            ->select('cal.id, cal.action,
                      cal.entity AS target_table,
                      cal.entity_id AS target_id,
                      cal.performed_at AS created_at,
                      CONCAT(st.name," ",st.surname) AS staff_name')
            ->from('coe_audit_log cal')
            ->join('staff st', 'st.id = cal.performed_by', 'left')
            ->order_by('cal.performed_at', 'DESC')
            ->limit((int) $limit)
            ->get()->result();
    }

    // ---------------------------------------------------------------
    // Pending task count per stage across all events in session
    // Real date-aware alerts — shows genuine blockers per stage
    // ---------------------------------------------------------------
    public function getPendingTasks($session_id)
    {
        $sid  = (int) $session_id;
        $now  = date('Y-m-d');

        $alerts = [];

        // 1. Eligibility not run AND exam starts within 7 days
        $eligibility_due = (int) $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM exam_group_class_batch_exams egcbe
             JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
             WHERE egcbe.session_id = ?
               AND eg.is_end_semester = 1
               AND eg.is_active = 1
               AND egcbe.eligibility_run_at IS NULL
               AND egcbe.date_from IS NOT NULL
               AND DATEDIFF(egcbe.date_from, CURDATE()) <= 7
               AND DATEDIFF(egcbe.date_from, CURDATE()) >= 0",
            [$sid]
        )->row()->cnt;

        if ($eligibility_due > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'fa-check-square-o',
                'message' => $eligibility_due . ' exam event(s) have not had eligibility checked — exam is within 7 days.',
                'link'    => site_url('coe/coe_eligibility?session_id=' . $sid),
            ];
        }

        // 2. Hall tickets not generated AND exam starts within 5 days
        $ht_due = (int) $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM exam_group_class_batch_exams egcbe
             JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
             WHERE egcbe.session_id = ?
               AND eg.is_end_semester = 1
               AND eg.is_active = 1
               AND egcbe.date_from IS NOT NULL
               AND DATEDIFF(egcbe.date_from, CURDATE()) <= 5
               AND DATEDIFF(egcbe.date_from, CURDATE()) >= 0
               AND NOT EXISTS (
                 SELECT 1 FROM coe_hall_tickets ht
                 WHERE ht.exam_group_class_batch_exam_id = egcbe.id
                 LIMIT 1
               )",
            [$sid]
        )->row()->cnt;

        if ($ht_due > 0) {
            $alerts[] = [
                'type'    => 'danger',
                'icon'    => 'fa-id-card',
                'message' => $ht_due . ' exam event(s) have no hall tickets — exam starts in ≤ 5 days!',
                'link'    => site_url('coe/coe_hallticket?session_id=' . $sid),
            ];
        }

        // 3. Marks not entered AND exam ended > 5 days ago
        $marks_due = (int) $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM exam_group_class_batch_exams egcbe
             JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
             WHERE egcbe.session_id = ?
               AND eg.is_end_semester = 1
               AND eg.is_active = 1
               AND egcbe.date_to IS NOT NULL
               AND DATEDIFF(CURDATE(), egcbe.date_to) > 5
               AND NOT EXISTS (
                 SELECT 1 FROM coe_student_results sr
                 WHERE sr.exam_group_class_batch_exam_id = egcbe.id
                 LIMIT 1
               )",
            [$sid]
        )->row()->cnt;

        if ($marks_due > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'fa-pencil',
                'message' => $marks_due . ' event(s) have no marks entered — exam ended over 5 days ago.',
                'link'    => site_url('coe/coe_marks?session_id=' . $sid),
            ];
        }

        // 4. Results not published AND marks entry complete AND it's been > 3 days
        $unpublished = (int) $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM exam_group_class_batch_exams egcbe
             JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
             WHERE egcbe.session_id = ?
               AND eg.is_end_semester = 1
               AND eg.is_active = 1
               AND egcbe.is_publish = 0
               AND egcbe.date_to IS NOT NULL
               AND DATEDIFF(CURDATE(), egcbe.date_to) > 3
               AND EXISTS (
                 SELECT 1 FROM coe_sgpa_summary sg
                 WHERE sg.exam_group_class_batch_exam_id = egcbe.id
                   AND sg.is_published = 0
                 LIMIT 1
               )",
            [$sid]
        )->row()->cnt;

        if ($unpublished > 0) {
            $alerts[] = [
                'type'    => 'info',
                'icon'    => 'fa-bullhorn',
                'message' => $unpublished . ' event(s) have computed results awaiting publication.',
                'link'    => site_url('coe/coe_results?session_id=' . $sid),
            ];
        }

        // 5. Pending revaluation requests (payment received but evaluator not assigned)
        $rev_unassigned = (int) $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM coe_revaluation_requests rr
             JOIN exam_group_class_batch_exams egcbe ON egcbe.id = rr.exam_group_class_batch_exam_id
             WHERE egcbe.session_id = ?
               AND rr.payment_status = 'paid'
               AND rr.status = 'pending'",
            [$sid]
        )->row()->cnt;

        if ($rev_unassigned > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'fa-refresh',
                'message' => $rev_unassigned . ' revaluation request(s) are paid but evaluator not assigned.',
                'link'    => site_url('coe/coe_revaluation?session_id=' . $sid),
            ];
        }

        // 6. Override approval requests pending
        $override_pending = (int) $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM coe_override_approval_requests oar
             JOIN exam_group_class_batch_exams egcbe ON egcbe.id = oar.batch_exam_id
             WHERE egcbe.session_id = ?
               AND oar.status = 'pending'",
            [$sid]
        )->row()->cnt;

        if ($override_pending > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'fa-user-plus',
                'message' => $override_pending . ' eligibility override request(s) are awaiting HOD/Principal approval.',
                'link'    => site_url('coe/coe_eligibility?session_id=' . $sid),
            ];
        }

        // Legacy counts for backward compat with dashboard widgets
        $no_ht        = 0;
        $no_marks     = 0;
        $no_published = 0;

        $events = $this->db
            ->select('egcbe.id')
            ->from('exam_group_class_batch_exams egcbe')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('eg.is_active', 1)
            ->get()->result();

        foreach ($events as $e) {
            $bid = (int) $e->id;
            if ($this->_cnt('coe_hall_tickets', 'exam_group_class_batch_exam_id', $bid) === 0) {
                $no_ht++;
            }
            if ($this->_cnt('coe_student_results', 'exam_group_class_batch_exam_id', $bid) === 0) {
                $no_marks++;
            }
            if ($this->_cnt('coe_sgpa_summary', 'exam_group_class_batch_exam_id', $bid) === 0) {
                $no_published++;
            }
        }

        return [
            'no_hall_tickets' => $no_ht,
            'no_marks'        => $no_marks,
            'no_results'      => $no_published,
            'alerts'          => $alerts,
            'total_alerts'    => count($alerts),
        ];
    }

    // ---------------------------------------------------------------
    private function _cnt($table, $col, $val)
    {
        return (int) $this->db->where($col, (int) $val)->count_all_results($table);
    }

    // ---------------------------------------------------------------
    // Department-wise pass rates for a session
    // ---------------------------------------------------------------
    public function getDepartmentStats($session_id)
    {
        $sid = (int) $session_id;
        return $this->db->query(
            "SELECT d.department_name,
                    COUNT(DISTINCT sg.student_id) AS total_students,
                    SUM(CASE WHEN sg.result_status = 'pass' THEN 1 ELSE 0 END) AS passed,
                    SUM(CASE WHEN sg.result_status = 'fail' THEN 1 ELSE 0 END) AS failed,
                    ROUND(AVG(sg.sgpa), 2) AS avg_sgpa,
                    ROUND(AVG(sg.cgpa), 2) AS avg_cgpa,
                    SUM(sg.arrear_count) AS total_arrears
             FROM coe_sgpa_summary sg
             JOIN exam_group_class_batch_exams egcbe ON egcbe.id = sg.exam_group_class_batch_exam_id
             JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
             JOIN student_session ss ON ss.student_id = sg.student_id AND ss.session_id = egcbe.session_id
             JOIN classes c ON c.id = ss.class_id
             JOIN department d ON d.id = c.department_id
             WHERE egcbe.session_id = ? AND eg.is_end_semester = 1
             GROUP BY d.id, d.department_name
             ORDER BY avg_sgpa DESC",
            [$sid]
        )->result();
    }

    // ---------------------------------------------------------------
    // Subject-wise performance across all events in a session
    // ---------------------------------------------------------------
    public function getSubjectStats($session_id)
    {
        $sid = (int) $session_id;
        return $this->db->query(
            "SELECT sub.name AS subject_name, sub.code AS subject_code,
                    COUNT(*) AS total,
                    SUM(CASE WHEN sr.result_status = 'pass' THEN 1 ELSE 0 END) AS passed,
                    SUM(CASE WHEN sr.result_status = 'fail' THEN 1 ELSE 0 END) AS failed,
                    ROUND(AVG(sr.total_marks), 2) AS avg_marks,
                    ROUND(MAX(sr.total_marks), 2) AS max_marks,
                    ROUND(MIN(sr.total_marks), 2) AS min_marks
             FROM coe_student_results sr
             JOIN subjects sub ON sub.id = sr.subject_id
             JOIN exam_group_class_batch_exams egcbe ON egcbe.id = sr.exam_group_class_batch_exam_id
             JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
             WHERE egcbe.session_id = ? AND eg.is_end_semester = 1
             GROUP BY sr.subject_id, sub.name, sub.code
             ORDER BY (SUM(CASE WHEN sr.result_status = 'pass' THEN 1 ELSE 0 END)/COUNT(*)) ASC",
            [$sid]
        )->result();
    }
}
