<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Attendancedashboard extends Admin_Controller
{
    private $is_period_wise;
    private $current_session;
    private $low_att_limit;

    public function __construct()
    {
        parent::__construct();
        // getSchoolDetail() (used by Admin_Controller for sch_setting_detail) does NOT include
        // attendence_type or low_attendance_limit — must call getSetting() for those fields.
        $full_setting          = $this->setting_model->getSetting();
        $this->is_period_wise  = (bool) ($full_setting->attendence_type ?? false);
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->low_att_limit   = (int) ($full_setting->low_attendance_limit ?? 75);
        if ($this->low_att_limit <= 0) {
            $this->low_att_limit = 75;
        }
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Attendance');
        $this->session->set_userdata('sub_menu', 'admin/attendancedashboard/index');
        $this->session->set_userdata('subsub_menu', '');

        $data['sch_setting']    = $this->sch_setting_detail;
        $data['is_period_wise'] = $this->is_period_wise;
        $data['low_att_limit']  = $this->low_att_limit;
        $data['today_label']    = date('l, d M Y');

        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendancedashboard/index', $data);
        $this->load->view('layout/footer');
    }

    // ── AJAX endpoint: today's coverage stats ────────────────────

    public function ajax_today_coverage()
    {
        if (!$this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) {
            $this->_json(['error' => 'forbidden'], 403);
            return;
        }

        if ($this->is_period_wise) {
            $cov   = $this->studentsubjectattendence_model->getDashboardTodayCoverage($this->current_session);
            $pres  = $this->studentsubjectattendence_model->getDashboardTodayPresent($this->current_session);

            $total_periods  = (int) ($cov['total_periods']  ?? 0);
            $marked_periods = (int) ($cov['marked_periods'] ?? 0);
            $total_sec      = (int) ($cov['total_sections']  ?? 0);
            $marked_sec     = (int) ($cov['marked_sections'] ?? 0);
            $pending_sec    = $total_sec - $marked_sec;

            $total_marked  = (int) ($pres['total_marked']  ?? 0);
            $present_count = (int) ($pres['present_count'] ?? 0);
            $pct_present   = $total_marked > 0 ? round($present_count * 100 / $total_marked, 1) : 0;

            $this->_json([
                'mode'           => 'period',
                'total_periods'  => $total_periods,
                'marked_periods' => $marked_periods,
                'pending_periods'=> $total_periods - $marked_periods,
                'total_sections' => $total_sec,
                'marked_sections'=> $marked_sec,
                'pending_sections'=> $pending_sec,
                'total_marked'   => $total_marked,
                'present_count'  => $present_count,
                'pct_present'    => $pct_present,
                'coverage_pct'   => $total_periods > 0 ? round($marked_periods * 100 / $total_periods, 1) : 0,
            ]);
        } else {
            $cov   = $this->stuattendence_model->getDashboardDayTodayCoverage($this->current_session);
            $pres  = $this->stuattendence_model->getDashboardDayTodayPresent($this->current_session);

            $total_sec   = (int) ($cov['total_sections']  ?? 0);
            $marked_sec  = (int) ($cov['marked_sections'] ?? 0);

            $total_marked  = (int) ($pres['total_marked']  ?? 0);
            $present_count = (int) ($pres['present_count'] ?? 0);
            $pct_present   = $total_marked > 0 ? round($present_count * 100 / $total_marked, 1) : 0;

            $this->_json([
                'mode'            => 'day',
                'total_sections'  => $total_sec,
                'marked_sections' => $marked_sec,
                'pending_sections'=> $total_sec - $marked_sec,
                'total_marked'    => $total_marked,
                'present_count'   => $present_count,
                'pct_present'     => $pct_present,
                'coverage_pct'    => $total_sec > 0 ? round($marked_sec * 100 / $total_sec, 1) : 0,
            ]);
        }
    }

    // ── AJAX endpoint: teacher marking status (period-wise only) ─

    public function ajax_teacher_status()
    {
        if (!$this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) {
            $this->_json(['error' => 'forbidden'], 403);
            return;
        }

        if (!$this->is_period_wise) {
            $this->_json(['mode' => 'day', 'rows' => []]);
            return;
        }

        $rows = $this->studentsubjectattendence_model->getDashboardTeacherStatus($this->current_session);
        $this->_json(['mode' => 'period', 'rows' => $rows]);
    }

    // ── AJAX endpoint: class/section heatmap ─────────────────────

    public function ajax_heatmap()
    {
        if (!$this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) {
            $this->_json(['error' => 'forbidden'], 403);
            return;
        }

        if ($this->is_period_wise) {
            $rows = $this->studentsubjectattendence_model->getDashboardHeatmap($this->current_session);
        } else {
            $rows = $this->stuattendence_model->getDashboardDayHeatmap($this->current_session);
        }

        $this->_json(['mode' => $this->is_period_wise ? 'period' : 'day', 'rows' => $rows]);
    }

    // ── AJAX endpoint: 7-day weekly trend ────────────────────────

    public function ajax_weekly_trend()
    {
        if (!$this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) {
            $this->_json(['error' => 'forbidden'], 403);
            return;
        }

        if ($this->is_period_wise) {
            $rows = $this->studentsubjectattendence_model->getDashboardWeeklyTrend($this->current_session);
        } else {
            $rows = $this->stuattendence_model->getDashboardDayWeeklyTrend($this->current_session);
        }

        $this->_json(['rows' => $rows]);
    }

    // ── AJAX endpoint: low attendance students ───────────────────

    public function ajax_low_attendance()
    {
        if (!$this->rbac->hasPrivilege('student_attendance_dashboard', 'can_view')) {
            $this->_json(['error' => 'forbidden'], 403);
            return;
        }

        if ($this->is_period_wise) {
            $rows = $this->studentsubjectattendence_model->getDashboardLowAttendance($this->current_session, $this->low_att_limit);
            // Count how many students have ANY attendance record this month
            $cov_sql = "SELECT COUNT(DISTINCT ssa.student_session_id) AS covered,
                               (SELECT COUNT(ss2.id) FROM student_session ss2
                                WHERE ss2.session_id = " . $this->db->escape($this->current_session) . "
                                  AND (ss2.is_alumni = 0 OR ss2.is_alumni IS NULL)) AS enrolled
                        FROM student_subject_attendances ssa
                        JOIN student_session ss ON ss.id = ssa.student_session_id
                        WHERE ss.session_id = " . $this->db->escape($this->current_session) . "
                          AND MONTH(ssa.date) = MONTH(CURDATE()) AND YEAR(ssa.date) = YEAR(CURDATE())";
        } else {
            $rows = $this->stuattendence_model->getDashboardDayLowAttendance($this->current_session, $this->low_att_limit);
            $cov_sql = "SELECT COUNT(DISTINCT sa.student_session_id) AS covered,
                               (SELECT COUNT(ss2.id) FROM student_session ss2
                                WHERE ss2.session_id = " . $this->db->escape($this->current_session) . "
                                  AND (ss2.is_alumni = 0 OR ss2.is_alumni IS NULL)) AS enrolled
                        FROM student_attendences sa
                        JOIN student_session ss ON ss.id = sa.student_session_id
                        WHERE ss.session_id = " . $this->db->escape($this->current_session) . "
                          AND MONTH(sa.date) = MONTH(CURDATE()) AND YEAR(sa.date) = YEAR(CURDATE())";
        }
        $cov = $this->db->query($cov_sql)->row_array();

        $this->_json([
            'threshold' => $this->low_att_limit,
            'rows'      => $rows,
            'covered'   => (int) ($cov['covered'] ?? 0),
            'enrolled'  => (int) ($cov['enrolled'] ?? 0),
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────

    private function _json($data, $status = 200)
    {
        http_response_code($status);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
