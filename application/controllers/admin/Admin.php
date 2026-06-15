<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Admin Controller
 *
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Config $config
 * @property CI_Lang $lang
 * @property Setting_model $setting_model
 * @property Auth $auth
 * @property Student_model $Student_model
 * @property Student_model $student_model
 * @property Customstudentfeemaster_model $Customstudentfeemaster_model
 * @property Studentfeemaster_model $Studentfeemaster_model
 * @property Customlib $customlib
 * @property Notification_model $notification_model
 * @property Studentfeemaster_model $studentfeemaster_model
 * @property Studenttransportfee_model $studenttransportfee_model
 * @property Studentsession_model $studentsession_model
 * @property Role_model $role_model
 * @property Expense_model $expense_model
 * @property Studentfee_model $studentfee_model
 * @property Income_model $income_model
 * @property Admin_model $admin_model
 * @property Book_model $book_model
 * @property Bookissue_model $bookissue_model
 * @property Stuattendence_model $stuattendence_model
 * @property Staff_model $Staff_model
 * @property Apply_leave_model $apply_leave_model
 * @property Staff_model $staff_model
 * @property Rbac $rbac
 * @property Session_model $session_model
 * @property Enc_lib $enc_lib
 * @property M_pdf $m_pdf
 * @property Filetype_model $filetype_model
 * @property Class_model $class_model
 * @property Customfield_model $customfield_model
 * @property Module_lib $module_lib
 * @property CI_DB_utility $dbutil
 */
class Admin extends Admin_Controller
{

    public $sch_setting_detail;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("classteacher_model");
        $this->load->model("Staff_model");
        $this->load->model("Student_model");
        $this->load->model("Customstudentfeemaster_model");
        $this->load->model("Studentfeemaster_model");
        $this->load->library('Enc_lib');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /**
     * Bulk-fetch all fee summary data for a session using ~7 queries total
     * instead of the original O(N×M) per-student pattern.
     *
     * Returns array keyed by student_session_id, each element:
     *   tuition_demand, tuition_paid, other_demand, other_paid,
     *   hostel_demand, hostel_paid, transport_demand, transport_paid,
     *   advance_paid, advance_discount, cf_demand, cf_paid
     *
     * @param mixed $current_session    session_id
     * @param bool  $transport_active   whether transport module is active
     * @param int   $advance_feetype_id feetype.id for "Advance Payments" (0 = none)
     * @param int   $cf_feetype_id      feetype.id for "Previous Session Balance" (0 = none)
     * @return array
     */
    private function _bulkFeeSummaryForSession($current_session, $transport_active = null, $advance_feetype_id = null, $cf_feetype_id = null)
    {
        // Resolve module/feetype IDs here if not supplied, keeping callers simple.
        if ($transport_active === null) {
            $tm = $this->Module_model->getPermissionByModulename('transport');
            $transport_active = !empty($tm['is_active']);
        }
        if ($advance_feetype_id === null) {
            $adv = $this->feetype_model->checkFeetypeByName('Advance Payments');
            $advance_feetype_id = $adv ? (int)$adv->id : 0;
        }
        if ($cf_feetype_id === null) {
            $cf = $this->feetype_model->checkFeetypeByName('Previous Session Balance');
            $cf_feetype_id = $cf ? (int)$cf->id : 0;
        }

        // Shared file cache: both widget AJAX calls reuse the same computed summary
        // for the same session, avoiding running all 7 queries twice per page load.
        $this->load->driver('cache', ['adapter' => 'file', 'backup' => 'dummy']);
        $cache_key = 'bulk_fee_summary_' . $current_session . '_' . ($transport_active ? 1 : 0)
                   . '_' . $advance_feetype_id . '_' . $cf_feetype_id;
        $cached = $this->cache->get($cache_key);
        if ($cached !== false && $cached !== null) {
            return $cached;
        }

        $summary = [];

        $zero = [
            'fee_types'        => [],
            'transport_demand' => 0.0, 'transport_paid'   => 0.0,
            'advance_paid'     => 0.0, 'advance_discount' => 0.0,
            'cf_demand'        => 0.0, 'cf_paid'          => 0.0,
        ];

        $ensure = function ($ssid) use (&$summary, $zero) {
            if (!isset($summary[$ssid])) {
                $summary[$ssid] = $zero;
            }
        };

        // Helper: parse amount_detail JSON and return [paid, discount]
        $parseDetail = function ($json) {
            $paid = 0.0;
            $disc = 0.0;
            if (!isJSON($json)) return [$paid, $disc];
            $details = json_decode($json);
            if ($details === null) return [$paid, $disc];
            if (is_object($details)) $details = (array)$details;
            if (!is_array($details)) return [$paid, $disc];
            foreach ($details as $d) {
                $paid += isset($d->amount)          ? (float)$d->amount          : 0.0;
                $disc += isset($d->amount_discount) ? (float)$d->amount_discount : 0.0;
            }
            return [$paid, $disc];
        };

        $sess = $this->db->escape($current_session);

        // Pre-compute active student session IDs — matches getStudentsBySession() filter
        // (students.is_active = 'yes' AND users.role = 'student' AND valid class + section).
        $valid_ss_query = $this->db->query("
            SELECT ss.id
            FROM student_session ss
            INNER JOIN students st  ON st.id      = ss.student_id  AND st.is_active = 'yes'
            INNER JOIN users    u   ON u.user_id   = st.id          AND u.role = 'student'
            INNER JOIN classes  c   ON c.id        = ss.class_id
            INNER JOIN sections sec ON sec.id      = ss.section_id
            WHERE ss.session_id = $sess
        ");
        $valid_ids = array_column($valid_ss_query->result_array(), 'id');
        if (empty($valid_ids)) {
            $this->cache->save($cache_key, [], 1800);
            return [];
        }
        $in_ids = implode(',', array_map('intval', $valid_ids));

        // Q1 — Regular fee DEMAND grouped by student + feetype (excludes Balance Master and Advance Payments).
        // COALESCE(sfo.override_amount, fgf.amount) applies per-student fee overrides stored in student_fee_overrides.
        $q1 = $this->db->query("
            SELECT sfm.student_session_id, ft.id AS feetype_id, ft.type AS fee_type,
                   SUM(COALESCE(sfo.override_amount, fgf.amount)) AS amount
            FROM student_fees_master sfm
            INNER JOIN fee_session_groups fsg ON fsg.id  = sfm.fee_session_group_id
            INNER JOIN fee_groups         fg  ON fg.id   = fsg.fee_groups_id
            INNER JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
            INNER JOIN feetype            ft  ON ft.id   = fgf.feetype_id
            INNER JOIN student_session    ss  ON ss.id   = sfm.student_session_id
            LEFT JOIN  student_fee_overrides sfo ON sfo.student_session_id = sfm.student_session_id
                                                AND sfo.fee_groups_feetype_id = fgf.id
            WHERE ss.session_id = $sess
              AND ss.id IN ($in_ids)
              AND fg.name != 'Balance Master'
              AND LOWER(ft.type) NOT IN ('advance payments', 'previous session balance')
            GROUP BY sfm.student_session_id, ft.id, ft.type
        ");
        foreach ($q1->result() as $row) {
            $ssid = (int)$row->student_session_id;
            $ensure($ssid);
            $tid = (int)$row->feetype_id;
            $amt = (float)$row->amount;
            if (!isset($summary[$ssid]['fee_types'][$tid])) {
                $summary[$ssid]['fee_types'][$tid] = ['id' => $tid, 'name' => (string)$row->fee_type, 'demand' => 0.0, 'paid' => 0.0];
            }
            $summary[$ssid]['fee_types'][$tid]['demand'] += $amt;
        }

        // Q2 — Regular fee PAID from student_fees_deposite (excludes Balance Master and Advance Payments).
        $q2 = $this->db->query("
            SELECT sfm.student_session_id, ft.id AS feetype_id, ft.type AS fee_type, sfd.amount_detail
            FROM student_fees_deposite sfd
            INNER JOIN student_fees_master sfm ON sfm.id  = sfd.student_fees_master_id
            INNER JOIN fee_session_groups  fsg ON fsg.id  = sfm.fee_session_group_id
            INNER JOIN fee_groups          fg  ON fg.id   = fsg.fee_groups_id
            INNER JOIN fee_groups_feetype  fgf ON fgf.id  = sfd.fee_groups_feetype_id
            INNER JOIN feetype             ft  ON ft.id   = fgf.feetype_id
            INNER JOIN student_session     ss  ON ss.id   = sfm.student_session_id
            WHERE ss.session_id = $sess
              AND ss.id IN ($in_ids)
              AND fg.name != 'Balance Master'
              AND LOWER(ft.type) NOT IN ('advance payments', 'previous session balance')
        ");
        foreach ($q2->result() as $row) {
            $ssid = (int)$row->student_session_id;
            $ensure($ssid);
            $tid = (int)$row->feetype_id;
            [$paid, $disc] = $parseDetail($row->amount_detail);
            $total = $paid + $disc;
            if (!isset($summary[$ssid]['fee_types'][$tid])) {
                $summary[$ssid]['fee_types'][$tid] = ['id' => $tid, 'name' => (string)$row->fee_type, 'demand' => 0.0, 'paid' => 0.0];
            }
            $summary[$ssid]['fee_types'][$tid]['paid'] += $total;
        }

        // Q3 & Q4 — Transport demand + paid
        if ($transport_active) {
            $q3 = $this->db->query("
                SELECT stf.student_session_id, SUM(COALESCE(stf.fee_override, rpp.fees)) AS demand
                FROM student_transport_fees stf
                INNER JOIN route_pickup_point rpp ON rpp.id = stf.route_pickup_point_id
                INNER JOIN student_session    ss  ON ss.id  = stf.student_session_id
                WHERE ss.session_id = $sess
                  AND ss.id IN ($in_ids)
                GROUP BY stf.student_session_id
            ");
            foreach ($q3->result() as $row) {
                $ssid = (int)$row->student_session_id;
                $ensure($ssid);
                $summary[$ssid]['transport_demand'] += (float)$row->demand;
            }

            $q4 = $this->db->query("
                SELECT stf.student_session_id, sfd.amount_detail
                FROM student_fees_deposite    sfd
                INNER JOIN student_transport_fees stf ON stf.id = sfd.student_transport_fee_id
                INNER JOIN student_session        ss  ON ss.id  = stf.student_session_id
                WHERE ss.session_id = $sess
                  AND ss.id IN ($in_ids)
            ");
            foreach ($q4->result() as $row) {
                $ssid = (int)$row->student_session_id;
                $ensure($ssid);
                [$paid, $disc] = $parseDetail($row->amount_detail);
                $summary[$ssid]['transport_paid'] += $paid + $disc;
            }
        }

        // Q5 — Advance balance
        if ($advance_feetype_id > 0) {
            $q5 = $this->db->query("
                SELECT sfm.student_session_id, sfd.amount_detail
                FROM student_fees_deposite    sfd
                INNER JOIN student_fees_master sfm ON sfm.id  = sfd.student_fees_master_id
                INNER JOIN fee_groups_feetype  fgf ON fgf.id  = sfd.fee_groups_feetype_id
                INNER JOIN student_session     ss  ON ss.id   = sfm.student_session_id
                WHERE fgf.feetype_id = " . (int)$advance_feetype_id . "
                  AND ss.session_id  = $sess
                  AND ss.id IN ($in_ids)
            ");
            foreach ($q5->result() as $row) {
                $ssid = (int)$row->student_session_id;
                $ensure($ssid);
                [$paid, $disc] = $parseDetail($row->amount_detail);
                $summary[$ssid]['advance_paid']     += $paid;
                $summary[$ssid]['advance_discount'] += $disc;
            }
        }

        // Q6 — CF (Balance Master) DEMAND per student
        $q6 = $this->db->query("
            SELECT sfm.student_session_id, SUM(sfm.amount) AS cf_demand
            FROM student_fees_master  sfm
            INNER JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
            INNER JOIN fee_groups         fg  ON fg.id  = fsg.fee_groups_id
            INNER JOIN student_session    ss  ON ss.id  = sfm.student_session_id
            WHERE ss.session_id = $sess
              AND ss.id IN ($in_ids)
              AND fg.name = 'Balance Master'
            GROUP BY sfm.student_session_id
        ");
        foreach ($q6->result() as $row) {
            $ssid = (int)$row->student_session_id;
            $ensure($ssid);
            $summary[$ssid]['cf_demand'] = (float)$row->cf_demand;
        }

        // Q7 — CF PAID (Previous Session Balance feetype deposits on Balance Master group)
        if ($cf_feetype_id > 0) {
            $q7 = $this->db->query("
                SELECT sfm.student_session_id, sfd.amount_detail
                FROM student_fees_deposite    sfd
                INNER JOIN student_fees_master sfm ON sfm.id  = sfd.student_fees_master_id
                INNER JOIN fee_session_groups  fsg ON fsg.id  = sfm.fee_session_group_id
                INNER JOIN fee_groups          fg  ON fg.id   = fsg.fee_groups_id
                INNER JOIN fee_groups_feetype  fgf ON fgf.id  = sfd.fee_groups_feetype_id
                INNER JOIN student_session     ss  ON ss.id   = sfm.student_session_id
                WHERE ss.session_id  = $sess
                  AND ss.id IN ($in_ids)
                  AND fg.name        = 'Balance Master'
                  AND fgf.feetype_id = " . (int)$cf_feetype_id . "
            ");
            foreach ($q7->result() as $row) {
                $ssid = (int)$row->student_session_id;
                $ensure($ssid);
                [$paid,] = $parseDetail($row->amount_detail);
                $summary[$ssid]['cf_paid'] += $paid;
            }
        }

        $this->cache->save($cache_key, $summary, 1800);
        return $summary;
    }

    private function getDashboardCache($key, $ttl, $builder)
    {
        $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
        $cached = $this->cache->get($key);
        if ($cached !== false && $cached !== null) {
            return $cached;
        }

        $data = call_user_func($builder);
        $this->cache->save($key, $data, $ttl);
        return $data;
    }

    public function unauthorized()
    {
        $data = array();
        $this->load->view('layout/header', $data);
        $this->load->view('unauthorized', $data);
        $this->load->view('layout/footer', $data);
    }

    public function updateAddonVerify()
    {
        $this->form_validation->set_rules('addon', 'Addon', 'required|trim|xss_clean');
        $this->form_validation->set_rules('addon_check_update_envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'addon'                       => form_error('addon'),
                'addon_check_update_envato_market_purchase_code' => form_error('addon_check_update_envato_market_purchase_code'),
            );
            $array = array('status' => '0', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->addon_update_check(); 
        }
    }

    public function dashboard()
    {
        $data['fees_awaiting_total_net_balance'] = 0;
        
        $role            = $this->customlib->getStaffRole();
        $role_id         = json_decode($role)->id;
        $data['role_id'] = $role_id;

        $staffid       = $this->customlib->getStaffID();
        $notifications = $this->notification_model->getUnreadStaffNotification($staffid, $role_id);

        $data['notifications'] = $notifications;
        $input                 = $this->setting_model->getCurrentSessionName();

        list($a, $b)  = explode('-', $input);
        $Current_year = $a;
        if (strlen($b) == 2) {
            $Next_year = substr($a, 0, 2) . $b;
        } else {
            $Next_year = $b;
        }
        // Increase execution time for dashboard data processing
        set_time_limit(300); // 5 minutes for large datasets
        
        $data['mysqlVersion'] = $this->setting_model->getMysqlVersion();
        $data['sqlMode']      = $this->setting_model->getSqlMode();
        //========================== Current Attendence ==========================
        $current_date       = date('Y-m-d');
        $data['title']      = 'Dashboard';
        $Current_start_date = date('01');

        $last_day_this_month        = date($Current_year.'-m-t');  //added
        $total_students_heads       = 0; //added

        $Current_date       = date('d');
        $Current_month      = date('m');
        $month_collection   = 0;
        $month_expense      = 0;
        $total_students     = 0;
        $total_teachers     = 0;
        $ar                 = $this->startmonthandend();
        $year_str_month     = $Current_year . '-' . $ar[0] . '-01';
        $year_end_month     = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));
        $getDepositeAmount  = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
        $student_transport_fee = $this->studenttransportfee_model->getTransportDepositAmountBetweenDate($year_str_month, $year_end_month);
        
        //======================Current Month Collection ==============================
        
        $first_day_this_month     = date('Y-m-01'); //comment
        
        $data['month_collection'] = 0;


        // Use search page logic for student, male, female counts (with session filter)
        $this->load->model('Session_model');
        $sessions = $this->Session_model->get();
        $selected_session_id = $this->input->get_post('dashboard_session_id');
        if (!$selected_session_id) {
            $selected_session_id = $this->setting_model->getCurrentSession();
        }
        $data['dashboard_sessions'] = $sessions;
        $data['dashboard_selected_session_id'] = $selected_session_id;

        // Student head count is loaded async after dashboard render
        $data['male_students'] = 0;
        $data['female_students'] = 0;
        $data['other_students'] = 0;
        $data['unspecified_students'] = 0; // No longer used, but kept for compatibility
        $data['total_students_heads'] = 0;
        $data['total_students'] = 0;
        $data['extra_students'] = array();
        $tot_roles = $this->role_model->get();
        // Single query: count active staff per role (replaces per-role count_roles() loop)
        $role_count_query = $this->db
            ->select('staff_roles.role_id, COUNT(staff_roles.staff_id) AS cnt')
            ->from('staff_roles')
            ->join('staff', 'staff.id = staff_roles.staff_id')
            ->where('staff.is_active', 1)
            ->group_by('staff_roles.role_id')
            ->get();
        $role_count_map = [];
        foreach ($role_count_query->result() as $rc) {
            $role_count_map[(int)$rc->role_id] = (int)$rc->cnt;
        }
        $count_roles = [];
        foreach ($tot_roles as $key => $value) {
            $count_roles[$value["name"]] = $role_count_map[(int)$value["id"]] ?? 0;
        }
        $data["roles"] = $count_roles;

        //======================== get collection by month ==========================
        $start_month = strtotime($year_str_month);
        $start       = strtotime($year_str_month);
        $end         = strtotime($year_end_month);
        $coll_month  = array();
        $s           = array();
        $total_month = array();
        while ($start_month <= $end) {
            $total_month[] = $this->lang->line(strtolower(date('F', $start_month)));
            $month_start   = date('Y-m-d', $start_month);
            $month_end     = date("Y-m-t", $start_month);
            $return        = $this->whatever($getDepositeAmount, $month_start, $month_end);
            $tranport_amt      = $this->whatever($student_transport_fee,  $month_start, $month_end);
            
            if (!IsNullOrEmptyString($return) || !IsNullOrEmptyString($tranport_amt)) {
                $s[] = convertBaseAmountCurrencyFormat($return+$tranport_amt);
            } else {
                $s[] = "0.00";
            }

            $start_month = strtotime("+1 month", $start_month);
        }
        //======================== getexpense by month ==============================
        $ex                  = array();
        $data['yearly_collection'] = array();
        $data['yearly_expense']    = array();
        $data['total_month']       = array();
        $data['current_month_days'] = array();
        $data['days_collection']    = array();
        $data['days_expense']       = array();

        $data['student_fee_history'] = [];

        $event_colors         = array("#03a9f4", "#c53da9", "#757575", "#8e24aa", "#d81b60", "#7cb342", "#fb8c00", "#fb3b3b");
        $data["event_colors"] = $event_colors;
        $userdata             = $this->customlib->getUserData();
        $data["role"]         = $userdata["user_type"];
        $start_date           = date('Y-m-01');
        $end_date             = date('Y-m-t');
        $current_month        = date('F');

        $data['fees_awaiting'] = [];

        $total_fess    = 0;
        $total_paid    = 0;
        $total_unpaid  = 0;
        $total_partial = 0;

        $total_fess    = 0;
        $total_paid    = 0;
        $total_unpaid  = 0;
        $total_partial = 0;

        $data['fees_awaiting_total_amount'] = 0;

        $month_income = 0;
        $incomegraph = $this->income_model->getIncomeHeadsData($start_date, $end_date);
        foreach ($incomegraph as $key => $value) {
            $incomegraph[$key]['total'] = convertBaseAmountCurrencyFormat($value['total']);
            if (!empty($value['total'])) {
                $month_income = $month_income + $value['total'];
            }
        }
        $data['incomegraph'] = $incomegraph;
        $data['month_income'] = $month_income;

        $expensegraph = $this->expense_model->getExpenseHeadData($start_date, $end_date);
        foreach ($expensegraph as $key => $value) {
            $expensegraph[$key]['total'] = convertBaseAmountCurrencyFormat($value['total']);
            if (!empty($value['total'])) {
                $month_expense = $month_expense + $value['total'];
            }
        }
        $data['expensegraph']  = $expensegraph;
        $data['month_expense'] = $month_expense;

        $enquiry       = $this->admin_model->getAllEnquiryCount($start_date, $end_date);
        $total_counter = $total_paid + $total_unpaid + $total_partial;

        $data['fees_overview'] = array(
            'total_unpaid'     => 0,
            'unpaid_sum'       => 0,
            'unpaid_progress'  => 0,
            'total_partial'    => 0,
            'partial_sum'      => 0,
            'partial_progress' => 0,
            'total_paid'       => 0,
            'paid_sum'         => 0,
            'paid_progress'    => 0,
            'total_demand'     => 0,
            'total_collection' => 0,
            'total_awaiting'   => 0,
        );
        $data['fees_awaiting_progress'] = 0;

        $data['fees_overview']['total_demand'] = 0;
        $data['fees_overview']['total_collection'] = 0;
        $data['fees_overview']['total_awaiting'] = 0;
        $data['fees_awaiting_progress'] = 0;

        $total_enquiry = $enquiry['total'];

        $data['enquiry_overview'] = array(
            'won'              => 0,
            'won_progress'     => 0,
            'active'           => 0,
            'active_progress'  => 0,
            'passive'          => 0,
            'passive_progress' => 0,
            'dead'             => 0,
            'dead_progress'    => 0,
            'lost'             => 0,
            'lost_progress'    => 0,
        );

        $data['total_paid'] = $total_paid;
        $data['total_fees'] = $total_fess;
        if ($total_fess > 0) {
            $data['fessprogressbar'] = ($total_paid * 100) / $total_fess;
        } else {
            $data['fessprogressbar'] = 0;
        }

        $data['total_enquiry']  = 0;
        $data['total_complete'] = 0;
        $data['fenquiryprogressbar'] = 0;

        $data['book_overview'] = array(
            'total'             => 0,
            'total_progress'    => 0,
            'availble'          => 0,
            'availble_progress' => 0,
            'total_issued'      => 0,
            'issued_progress'   => 0,
            'dueforreturn'      => 0,
            'forreturn'         => 0,
        );

        $data['attendence_data'] = array(
            'total_present' => 0,
            'present' => '0%',
            'total_late' => 0,
            'late' => '0%',
            'total_absent' => 0,
            'absent' => '0%',
            'total_half_day' => 0,
            'half_day' => '0%'
        );
        $data['staff_attendance_details'] = array(
            'total_present' => 0,
            'present' => 0,
            'total_late' => 0,
            'late' => 0,
            'total_absent' => 0,
            'absent' => 0,
            'total_half_day' => 0,
            'half_day' => 0,
            'total_permission' => 0,
            'permission' => 0,
            'total_staff' => 0,
            'total_attended' => 0,
            'attended_percent' => 0
        );
        $data['percentTotalStaff_data'] = 0;
        $data['sch_setting']            = $this->sch_setting_detail;

        // Birthday widgets are loaded async after dashboard render
        $data['student_birthdays'] = [];
        $data['staff_birthdays'] = [];
		// new features code added
        // $input_session   = $this->setting_model->getCurrentSessionName();
        // list($a, $b)  = explode('-', $input_session);
        // $Current_year = $a;
        // if(date("m")>=1 && date("m")<=4 ){
            // $Current_year = $b;
        // }else{
            // $Current_year = $a;
        // }

        // $first_day_this_month  = date("20$Current_year".'-m-01'); //added
        // $last_day_this_month  = date("20$Current_year".'-m-t');  //added

        $data['getStudentMonthlyLeave'] = 0;
        $data['getStudentApproveMonthlyLeave'] = 0;
        $data['studentapprovemonthlyleave'] = 0;
        $data['getStaffMonthlyLeave'] = 0;
        $data['getStaffApproveMonthlyLeave'] = 0;
        $data['staffapprovemonthlyleave'] = 0;

        $tot_students = $this->studentsession_model->getTotalStudentBySession();
        if (!empty($tot_students)) {
            $total_students = $tot_students->total_student;
        }

        $data['total_students'] = $total_students;
        $data['total_students_heads'] = 0;
        $data['male_students'] = 0;
        $data['female_students'] = 0;
        $data['other_students'] = 0;
        $data['unspecified_students'] = 0; // No longer used, but kept for compatibility

        if ($data['sch_setting']->attendence_type == 0) {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        } else {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    public function fees_overview_widget()
    {
        if (!$this->rbac->hasPrivilege('fees_overview_widegts', 'can_view')) {
            access_denied();
        }
        $current_session = $this->setting_model->getCurrentSession();
        $cache_key = 'dash_fees_overview_' . $current_session;

        $response = $this->getDashboardCache($cache_key, 1800, function () use ($current_session) {
            $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

            // All feetype/module lookups and the 7 bulk queries are handled
            // (and internally cached) by _bulkFeeSummaryForSession().
            $fee_summary = $this->_bulkFeeSummaryForSession($current_session);

            if (empty($fee_summary)) {
                return $this->_buildZeroFeesOverviewResponse($currency_symbol);
            }

            $unpaid_count = 0; $unpaid_sum  = 0.0;
            $partial_count = 0; $partial_sum = 0.0; $partial_collected = 0.0;
            $paid_count   = 0; $paid_sum    = 0.0;
            $total_demand = 0.0; $total_collection = 0.0; $total_awaiting = 0.0;
            $total_last_yr_cf = 0.0; $total_cf_paid = 0.0; $total_cf_balance = 0.0;

            // Iterate directly over fee_summary (no getStudentsBySession() needed).
            foreach ($fee_summary as $ssid => $fs) {

                $ft_demand = 0.0;
                $ft_paid   = 0.0;
                foreach ($fs['fee_types'] as $ft) {
                    $ft_demand += (float)$ft['demand'];
                    $ft_paid   += (float)$ft['paid'];
                }
                $totalfee       = $ft_demand + $fs['transport_demand'];
                $total_paid_sum = $ft_paid   + $fs['transport_paid'];
                $cf_demand  = $fs['cf_demand'];
                $cf_paid    = $fs['cf_paid'];
                $cf_balance = $cf_demand - $cf_paid;

                if ($totalfee == 0 && $cf_demand == 0) {
                    continue;
                }

                $awaiting_value = $totalfee - $total_paid_sum;

                if ($awaiting_value > 0 && $total_paid_sum == 0) {
                    $unpaid_count++;
                    $unpaid_sum += $awaiting_value;
                } elseif ($awaiting_value > 0 && $total_paid_sum > 0) {
                    $partial_count++;
                    $partial_sum += $awaiting_value;
                    $partial_collected += $total_paid_sum;
                } elseif ($awaiting_value <= 0 && $total_paid_sum > 0) {
                    $paid_count++;
                    // Use $totalfee (demand fulfilled), not $total_paid_sum (actual collected),
                    // so that: unpaid_bal + partial_paid + partial_bal + paid_demand = total_demand.
                    // Over-payments / advance amounts are excluded from this equation intentionally.
                    $paid_sum += $totalfee;
                }

                $total_demand     += $totalfee;
                $total_collection += $total_paid_sum;
                $total_awaiting   += $awaiting_value;
                $total_last_yr_cf += $cf_demand;
                $total_cf_paid    += $cf_paid;
                $total_cf_balance += $cf_balance;
            }

            $total_counter        = $unpaid_count + $partial_count + $paid_count;
            $unpaid_progress      = ($total_counter > 0)    ? ($unpaid_count  * 100 / $total_counter) : 0;
            $partial_progress     = ($total_counter > 0)    ? ($partial_count * 100 / $total_counter) : 0;
            $paid_progress        = ($total_counter > 0)    ? ($paid_count    * 100 / $total_counter) : 0;
            $fees_awaiting_progress = ($total_demand > 0)   ? ($total_awaiting    * 100 / $total_demand) : 0;
            $collection_progress  = ($total_demand > 0)     ? ($total_collection  * 100 / $total_demand) : 0;
            $cfcollection_progress = ($total_last_yr_cf > 0) ? ($total_cf_paid    * 100 / $total_last_yr_cf) : 0;
            $cfbalance_progress   = ($total_last_yr_cf > 0)  ? ($total_cf_balance * 100 / $total_last_yr_cf) : 0;

            return array(
                'status' => 'success',
                'data'   => array(
                    'total_unpaid'    => $unpaid_count,
                    'unpaid_progress' => round($unpaid_progress, 2),
                    'unpaid_sum'      => $unpaid_sum,
                    'unpaid_sum_formatted' => $currency_symbol . number_format($unpaid_sum, 2),

                    'total_partial'    => $partial_count,
                    'partial_progress' => round($partial_progress, 2),
                    'partial_sum'      => $partial_sum,
                    'partial_sum_formatted' => $currency_symbol . number_format($partial_sum, 2),
                    'partial_collected_sum'           => $partial_collected,
                    'partial_collected_sum_formatted'  => $currency_symbol . number_format($partial_collected, 2),

                    'total_paid'    => $paid_count,
                    'paid_progress' => round($paid_progress, 2),
                    'paid_sum'      => $paid_sum,
                    'paid_sum_formatted' => $currency_symbol . number_format($paid_sum, 2),
                    'currency_zero' => $currency_symbol . number_format(0, 2),

                    'total_counter'    => $total_counter,
                    'demand_count'     => $total_counter,
                    'demand_progress'  => ($total_counter > 0) ? 100 : 0,
                    'demand_sum'       => $total_demand,
                    'demand_sum_formatted' => $currency_symbol . number_format($total_demand, 2),

                    'collection_count'    => $total_counter,
                    'collection_progress' => round($collection_progress, 2),
                    'collection_sum'      => $total_collection,
                    'collection_sum_formatted' => $currency_symbol . number_format($total_collection, 2),

                    'awaiting_count'    => $total_counter,
                    'awaiting_progress' => round($fees_awaiting_progress, 2),
                    'awaiting_sum'      => $total_awaiting,
                    'awaiting_sum_formatted' => $currency_symbol . number_format($total_awaiting, 2),

                    'cfdemand_count'    => $total_counter,
                    'cfdemand_progress' => ($total_last_yr_cf > 0) ? 100 : 0,
                    'cfdemand_sum'      => $total_last_yr_cf,
                    'cfdemand_sum_formatted' => $currency_symbol . number_format($total_last_yr_cf, 2),

                    'cfcollection_count'    => $total_counter,
                    'cfcollection_progress' => round($cfcollection_progress, 2),
                    'cfcollection_sum'      => $total_cf_paid,
                    'cfcollection_sum_formatted' => $currency_symbol . number_format($total_cf_paid, 2),

                    'cfbalance_count'    => $total_counter,
                    'cfbalance_progress' => round($cfbalance_progress, 2),
                    'cfbalance_sum'      => $total_cf_balance,
                    'cfbalance_sum_formatted' => $currency_symbol . number_format($total_cf_balance, 2),

                    'total_demand'     => $total_demand,
                    'total_demand_formatted' => $currency_symbol . number_format($total_demand, 2),
                    'total_collection' => $total_collection,
                    'total_collection_formatted' => $currency_symbol . number_format($total_collection, 2),
                    'total_awaiting'   => $total_awaiting,
                    'total_awaiting_formatted' => $currency_symbol . number_format($total_awaiting, 2),
                    'fees_awaiting_progress' => round($fees_awaiting_progress, 2),
                    'fees_awaiting_total_net_balance' => $total_awaiting,
                    'fees_awaiting_total_net_balance_formatted' => $currency_symbol . number_format($total_awaiting, 2),
                    'last_yr_pending_demand'     => $total_last_yr_cf,
                    'last_yr_pending_demand_formatted' => $currency_symbol . number_format($total_last_yr_cf, 2),
                    'last_yr_pending_collection' => $total_cf_paid,
                    'last_yr_pending_collection_formatted' => $currency_symbol . number_format($total_cf_paid, 2),
                    'last_yr_pending'  => $total_cf_balance,
                    'last_yr_pending_formatted' => $currency_symbol . number_format($total_cf_balance, 2),
                ),
            );
        });

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /** Returns a zeroed fees overview response (no students or no data). */
    private function _buildZeroFeesOverviewResponse($currency_symbol)
    {
        $fmt = function ($v) use ($currency_symbol) { return $currency_symbol . number_format($v, 2); };
        return array('status' => 'success', 'data' => array(
            'total_unpaid' => 0, 'unpaid_progress' => 0, 'unpaid_sum' => 0, 'unpaid_sum_formatted' => $fmt(0),
            'total_partial' => 0, 'partial_progress' => 0, 'partial_sum' => 0, 'partial_sum_formatted' => $fmt(0), 'partial_collected_sum' => 0, 'partial_collected_sum_formatted' => $fmt(0),
            'currency_zero' => $fmt(0),
            'total_paid' => 0, 'paid_progress' => 0, 'paid_sum' => 0, 'paid_sum_formatted' => $fmt(0),
            'total_counter' => 0, 'demand_count' => 0, 'demand_progress' => 0, 'demand_sum' => 0, 'demand_sum_formatted' => $fmt(0),
            'collection_count' => 0, 'collection_progress' => 0, 'collection_sum' => 0, 'collection_sum_formatted' => $fmt(0),
            'awaiting_count' => 0, 'awaiting_progress' => 0, 'awaiting_sum' => 0, 'awaiting_sum_formatted' => $fmt(0),
            'cfdemand_count' => 0, 'cfdemand_progress' => 0, 'cfdemand_sum' => 0, 'cfdemand_sum_formatted' => $fmt(0),
            'cfcollection_count' => 0, 'cfcollection_progress' => 0, 'cfcollection_sum' => 0, 'cfcollection_sum_formatted' => $fmt(0),
            'cfbalance_count' => 0, 'cfbalance_progress' => 0, 'cfbalance_sum' => 0, 'cfbalance_sum_formatted' => $fmt(0),
            'total_demand' => 0, 'total_demand_formatted' => $fmt(0),
            'total_collection' => 0, 'total_collection_formatted' => $fmt(0),
            'total_awaiting' => 0, 'total_awaiting_formatted' => $fmt(0),
            'fees_awaiting_progress' => 0,
            'fees_awaiting_total_net_balance' => 0, 'fees_awaiting_total_net_balance_formatted' => $fmt(0),
            'last_yr_pending_demand' => 0, 'last_yr_pending_demand_formatted' => $fmt(0),
            'last_yr_pending_collection' => 0, 'last_yr_pending_collection_formatted' => $fmt(0),
            'last_yr_pending' => 0, 'last_yr_pending_formatted' => $fmt(0),
        ));
    }

    public function fees_classwise_summary_widget()
    {
        if (!$this->rbac->hasPrivilege('fees_classwise_summary_widget', 'can_view')) {
            access_denied();
        }

        $current_session = $this->setting_model->getCurrentSession();
        $this->load->model('Finalyearclass_model');
        $final_year_ids = $this->Finalyearclass_model->getClassIds();
        $final_hash = md5(implode(',', $final_year_ids));
        $cache_key = 'dash_fees_classwise_v2_' . $current_session . '_' . $final_hash;

        $response = $this->getDashboardCache($cache_key, 1800, function () use ($current_session, $final_year_ids) {
            // Lightweight query: only student_session_id + class_id (no student personal data).
            $sess_esc = $this->db->escape($current_session);
            $ss_query = $this->db->query("
                SELECT ss.id AS student_session_id, ss.class_id, c.class AS class_name
                FROM student_session ss
                INNER JOIN classes c ON c.id = ss.class_id
                WHERE ss.session_id = $sess_esc
            ");
            $sessions_map = $ss_query->result();

            $class_rows  = array();
            $class_names = array(); // class_id => class_name
            foreach ($sessions_map as $row) {
                $cid = (int)$row->class_id;
                if (!isset($class_names[$cid])) {
                    $class_names[$cid] = $row->class_name;
                }
            }

            // Resolve transport module once so we can include it in the response.
            $tm = $this->Module_model->getPermissionByModulename('transport');
            $transport_active = !empty($tm['is_active']);

            $fee_summary = $this->_bulkFeeSummaryForSession($current_session, $transport_active);

            $fee_type_columns = []; // ft_id => ft_name, ordered by first appearance

            if (!empty($sessions_map) && !empty($fee_summary)) {
                foreach ($sessions_map as $row) {
                    $class_id = (int)$row->class_id;
                    $ssid     = (int)$row->student_session_id;

                    if (!isset($fee_summary[$ssid])) {
                        continue;
                    }
                    $fs = $fee_summary[$ssid];

                    if (!isset($class_rows[$class_id])) {
                        $class_rows[$class_id] = $this->initClasswiseRow($class_id, $class_names[$class_id] ?? '');
                    }

                    foreach ($fs['fee_types'] as $ft_id => $ft) {
                        if (!isset($fee_type_columns[$ft_id])) {
                            $fee_type_columns[$ft_id] = $ft['name'];
                        }
                        if (!isset($class_rows[$class_id]['fee_types'][$ft_id])) {
                            $class_rows[$class_id]['fee_types'][$ft_id] = [
                                'id' => $ft_id, 'name' => $ft['name'],
                                'demand' => 0.0, 'paid' => 0.0, 'pending' => 0.0,
                            ];
                        }
                        $class_rows[$class_id]['fee_types'][$ft_id]['demand'] += $ft['demand'];
                        $class_rows[$class_id]['fee_types'][$ft_id]['paid']   += $ft['paid'];
                    }
                    $class_rows[$class_id]['transport_demand'] += $fs['transport_demand'];
                    $class_rows[$class_id]['transport_paid']   += $fs['transport_paid'];
                }
            } // end if sessions_map && fee_summary

            $rows = array_values($class_rows);
            usort($rows, function ($a, $b) {
                return strcasecmp($a['class_name'], $b['class_name']);
            });

            $all_rows = array();
            foreach ($rows as $row) {
                $this->finalizeClasswiseRow($row);
                if ($this->rowHasAmounts($row)) {
                    $all_rows[] = $row;
                }
            }

            $exclude_rows = array();
            foreach ($all_rows as $row) {
                if (!in_array($row['class_id'], $final_year_ids, true)) {
                    $exclude_rows[] = $row;
                }
            }

            $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
            $all_totals = $this->sumClasswiseRows($all_rows);
            $exclude_totals = $this->sumClasswiseRows($exclude_rows);

            $all_rows = $this->formatClasswiseRows($all_rows, $currency_symbol);
            $exclude_rows = $this->formatClasswiseRows($exclude_rows, $currency_symbol);
            $all_totals = $this->formatClasswiseRow($all_totals, $currency_symbol);
            $exclude_totals = $this->formatClasswiseRow($exclude_totals, $currency_symbol);

            return array(
                'status' => 'success',
                'data' => array(
                    'all' => $all_rows,
                    'exclude_final' => $exclude_rows,
                    'totals' => array(
                        'all' => $all_totals,
                        'exclude_final' => $exclude_totals,
                    ),
                    'fee_type_columns' => $fee_type_columns,
                    'transport_active' => $transport_active,
                ),
            );
        });

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    private function initClasswiseRow($class_id, $class_name)
    {
        return array(
            'class_id'         => $class_id,
            'class_name'       => $class_name,
            'fee_types'        => [],
            'transport_demand' => 0.0,
            'transport_paid'   => 0.0,
            'transport_pending'=> 0.0,
        );
    }

    private function finalizeClasswiseRow(&$row)
    {
        foreach ($row['fee_types'] as $ft_id => &$ft) {
            $ft['pending'] = max(0.0, $ft['demand'] - $ft['paid']);
        }
        unset($ft);
        $row['transport_pending'] = max(0.0, $row['transport_demand'] - $row['transport_paid']);
    }

    private function rowHasAmounts($row)
    {
        $total = $row['transport_demand'] + $row['transport_paid'];
        foreach ($row['fee_types'] as $ft) {
            $total += $ft['demand'] + $ft['paid'];
        }
        return $total > 0;
    }

    private function sumClasswiseRows($rows)
    {
        $total = $this->initClasswiseRow(0, 'Grand Total');
        foreach ($rows as $row) {
            foreach ($row['fee_types'] as $ft_id => $ft) {
                if (!isset($total['fee_types'][$ft_id])) {
                    $total['fee_types'][$ft_id] = [
                        'id' => $ft_id, 'name' => $ft['name'],
                        'demand' => 0.0, 'paid' => 0.0, 'pending' => 0.0,
                    ];
                }
                $total['fee_types'][$ft_id]['demand']  += $ft['demand'];
                $total['fee_types'][$ft_id]['paid']    += $ft['paid'];
                $total['fee_types'][$ft_id]['pending'] += $ft['pending'];
            }
            $total['transport_demand']  += $row['transport_demand'];
            $total['transport_paid']    += $row['transport_paid'];
            $total['transport_pending'] += $row['transport_pending'];
        }

        return $total;
    }

    private function formatClasswiseRows($rows, $currency_symbol)
    {
        $formatted = array();
        foreach ($rows as $row) {
            $formatted[] = $this->formatClasswiseRow($row, $currency_symbol);
        }
        return $formatted;
    }

    private function formatClasswiseRow($row, $currency_symbol)
    {
        foreach ($row['fee_types'] as $ft_id => &$ft) {
            $ft['demand_formatted']  = $currency_symbol . number_format($ft['demand'],  2);
            $ft['paid_formatted']    = $currency_symbol . number_format($ft['paid'],    2);
            $ft['pending_formatted'] = $currency_symbol . number_format($ft['pending'], 2);
        }
        unset($ft);
        $row['transport_demand_formatted']  = $currency_symbol . number_format($row['transport_demand'],  2);
        $row['transport_paid_formatted']    = $currency_symbol . number_format($row['transport_paid'],    2);
        $row['transport_pending_formatted'] = $currency_symbol . number_format($row['transport_pending'], 2);

        return $row;
    }

    public function student_head_count_widget()
    {
        if (!$this->rbac->hasPrivilege('student_head_count_widget', 'can_view')) {
            access_denied();
        }

        $current_session = $this->setting_model->getCurrentSession();
        $gender_counts = $this->Student_model->getStudentCountByGender($current_session);

        $male_students = isset($gender_counts['Male']) ? (int)$gender_counts['Male'] : 0;
        $female_students = isset($gender_counts['Female']) ? (int)$gender_counts['Female'] : 0;
        $other_students = isset($gender_counts['Other/Unspecified']) ? (int)$gender_counts['Other/Unspecified'] : 0;

        $total_students_heads = $male_students + $female_students + $other_students;

        $male_percent = ($total_students_heads > 0) ? round(($male_students * 100) / $total_students_heads, 2) : 0;
        $female_percent = ($total_students_heads > 0) ? round(($female_students * 100) / $total_students_heads, 2) : 0;
        $other_percent = ($total_students_heads > 0) ? round(($other_students * 100) / $total_students_heads, 2) : 0;

        $response = array(
            'status' => 'success',
            'data' => array(
                'total_students_heads' => $total_students_heads,
                'male_students' => $male_students,
                'female_students' => $female_students,
                'other_students' => $other_students,
                'male_percent' => $male_percent,
                'female_percent' => $female_percent,
                'other_percent' => $other_percent,
            ),
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function student_today_attendance_widget()
    {
        if (!$this->rbac->hasPrivilege('today_attendance_widegts', 'can_view')) {
            access_denied();
        }

        // Use same logic as Student Head Count widget for accurate count
        $current_session = $this->setting_model->getCurrentSession();
        $gender_counts = $this->Student_model->getStudentCountByGender($current_session);
        
        $male_students = isset($gender_counts['Male']) ? (int)$gender_counts['Male'] : 0;
        $female_students = isset($gender_counts['Female']) ? (int)$gender_counts['Female'] : 0;
        $other_students = isset($gender_counts['Other/Unspecified']) ? (int)$gender_counts['Other/Unspecified'] : 0;
        
        $total_students = $male_students + $female_students + $other_students;

        if ($total_students <= 0) {
            $attendance = array(
                'total_present' => 0,
                'present' => '0%',
                'total_late' => 0,
                'late' => '0%',
                'total_absent' => 0,
                'absent' => '0%',
                'total_half_day' => 0,
                'half_day' => '0%',
                'total_students' => 0
            );
        } else {
            $attendance = $this->stuattendence_model->getTodayDayAttendance($total_students);
            if (empty($attendance)) {
                $attendance = array(
                    'total_present' => 0,
                    'present' => '0%',
                    'total_late' => 0,
                    'late' => '0%',
                    'total_absent' => 0,
                    'absent' => '0%',
                    'total_half_day' => 0,
                    'half_day' => '0%',
                    'total_students' => $total_students
                );
            } else {
                $attendance['total_students'] = $total_students;
            }
        }

        $response = array(
            'status' => 'success',
            'data' => $attendance,
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function staff_today_attendance_widget()
    {
        // permission name corrected to match category 'Staff Present Today Widegts'
        if (!$this->rbac->hasPrivilege('staff_present_today_widegts', 'can_view')) {
            access_denied();
        }

        $attendance = $this->Staff_model->getTodayStaffAttendanceDetails();
        if (empty($attendance)) {
            $attendance = array(
                'total_present' => 0,
                'present' => 0,
                'total_late' => 0,
                'late' => 0,
                'total_absent' => 0,
                'absent' => 0,
                'total_half_day' => 0,
                'half_day' => 0,
                'total_permission' => 0,
                'permission' => 0,
                'total_staff' => 0,
                'total_attended' => 0,
                'attended_percent' => 0
            );
        }

        $response = array(
            'status' => 'success',
            'data' => $attendance,
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function enquiry_overview_widget()
    {
        if (!$this->rbac->hasPrivilege('enquiry_overview_widegts', 'can_view')) {
            access_denied();
        }

        $enquiry = $this->admin_model->getAllEnquiryCount();
        $total_enquiry = isset($enquiry['total']) ? $enquiry['total'] : 0;

        $paymentOverview = $this->admin_model->getOnlineStudentPaymentOverview();
        $applications_total = isset($paymentOverview['applications_total']) ? (int) $paymentOverview['applications_total'] : 0;
        $fully_paid = isset($paymentOverview['fully_paid']) ? (int) $paymentOverview['fully_paid'] : 0;
        $partially_paid = isset($paymentOverview['partially_paid']) ? (int) $paymentOverview['partially_paid'] : 0;
        $applied = isset($paymentOverview['applied']) ? (int) $paymentOverview['applied'] : 0;
        $not_paid = isset($paymentOverview['not_paid']) ? (int) $paymentOverview['not_paid'] : 0;
        $revoked       = isset($paymentOverview['revoked'])       ? (int) $paymentOverview['revoked']       : 0;
        $waiting_list  = isset($paymentOverview['waiting_list'])  ? (int) $paymentOverview['waiting_list']  : 0;
        $fully_paid_progress = isset($paymentOverview['fully_paid_progress']) ? (float) $paymentOverview['fully_paid_progress'] : 0;
        $partially_paid_progress = isset($paymentOverview['partially_paid_progress']) ? (float) $paymentOverview['partially_paid_progress'] : 0;
        $applied_progress = isset($paymentOverview['applied_progress']) ? (float) $paymentOverview['applied_progress'] : 0;
        $not_paid_progress = isset($paymentOverview['not_paid_progress']) ? (float) $paymentOverview['not_paid_progress'] : 0;

        // APPLICATION RECEIVED = fully paid + partially paid + applied (excludes not_paid)
        $application_received = $fully_paid + $partially_paid + $applied;

        if ($total_enquiry > 0) {
            $overview = array(
                'total'            => $total_enquiry,
                'won'              => $application_received,
                'won_progress'     => $total_enquiry > 0 ? round(($application_received * 100) / $total_enquiry, 2) : 0,
                'active'           => $fully_paid,
                'active_progress'  => $fully_paid_progress,
                'applications_total'            => $partially_paid,
                'applications_total_progress'   => $partially_paid_progress,
                'applied'                       => $applied,
                'applied_progress'              => $applied_progress,
                'applications_partial'          => $not_paid,
                'applications_partial_progress' => $not_paid_progress,
                'revoked'                       => $revoked,
                'waiting_list'                  => $waiting_list,
            );
        } else {
            $overview = array(
                'total'            => 0,
                'won'              => $application_received,
                'won_progress'     => 0,
                'active'           => $fully_paid,
                'active_progress'  => $fully_paid_progress,
                'applications_total'            => $partially_paid,
                'applications_total_progress'   => $partially_paid_progress,
                'applied'                       => $applied,
                'applied_progress'              => $applied_progress,
                'applications_partial'          => $not_paid,
                'applications_partial_progress' => $not_paid_progress,
                'revoked'                       => $revoked,
                'waiting_list'                  => $waiting_list,
            );
        }

        $response = array(
            'status' => 'success',
            'data' => $overview,
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function library_overview_widget()
    {
        if (!$this->rbac->hasPrivilege('book_overview_widegts', 'can_view')) {
            access_denied();
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');

        $bookoverview  = $this->book_model->bookoverview($start_date, $end_date);
        $bookduereport = $this->bookissue_model->dueforreturn($start_date, $end_date);
        $forreturndata = $this->bookissue_model->forreturn($start_date, $end_date);

        $dueforreturn = isset($bookduereport[0]['total']) ? (int)$bookduereport[0]['total'] : 0;
        $forreturn = isset($forreturndata[0]['total']) ? (int)$forreturndata[0]['total'] : 0;
        $total_qty = isset($bookoverview[0]['qty']) ? (int)$bookoverview[0]['qty'] : 0;
        $total_issued = isset($bookoverview[0]['total_issue']) ? (int)$bookoverview[0]['total_issue'] : 0;

        $availble = 0;
        $availble_progress = 0;
        $issued_progress = 0;

        if ($total_qty > 0) {
            $availble = $total_qty - $total_issued;
            $availble_progress = ($availble * 100) / $total_qty;
            $issued_progress = ($total_issued * 100) / $total_qty;
        }

        $response = array(
            'status' => 'success',
            'data' => array(
                'total' => $total_qty,
                'availble' => $availble,
                'availble_progress' => round($availble_progress, 2),
                'total_issued' => $total_issued,
                'issued_progress' => round($issued_progress, 2),
                'dueforreturn' => $dueforreturn,
                'forreturn' => $forreturn,
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function monthly_fees_collection_widget()
    {
        if (!$this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
            access_denied();
        }

        $input = $this->setting_model->getCurrentSessionName();
        list($a, $b) = explode('-', $input);
        $Current_year = $a;
        if (strlen($b) == 2) {
            $Next_year = substr($a, 0, 2) . $b;
        } else {
            $Next_year = $b;
        }

        $ar = $this->startmonthandend();
        $year_str_month = $Current_year . '-' . $ar[0] . '-01';
        $year_end_month = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));

        $getDepositeAmount = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
        $student_transport_fee = $this->studenttransportfee_model->getTransportDepositAmountBetweenDate($year_str_month, $year_end_month);

        $current_date = date('Y-m-d');
        $first_day_this_month = date('Y-m-01');

        $month_collection = $this->whatever($getDepositeAmount, $first_day_this_month, $current_date);
        $month_transport_collection = $this->whatever($student_transport_fee, $first_day_this_month, $current_date);

        $total = $month_collection + $month_transport_collection;
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

        $response = array(
            'status' => 'success',
            'data' => array(
                'amount' => $total,
                'amount_formatted' => $total ? ($currency_symbol . amountFormat($total)) : ''
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function monthly_income_widget()
    {
        if (!$this->rbac->hasPrivilege('monthly_income_widget', 'can_view')) {
            access_denied();
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $month_income = 0;
        $incomegraph = $this->income_model->getIncomeHeadsData($start_date, $end_date);
        foreach ($incomegraph as $value) {
            if (!empty($value['total'])) {
                $month_income += $value['total'];
            }
        }

        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

        $response = array(
            'status' => 'success',
            'data' => array(
                'amount' => $month_income,
                'amount_formatted' => $month_income ? ($currency_symbol . amountFormat($month_income)) : ''
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function monthly_expense_widget()
    {
        if (!$this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) {
            access_denied();
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $month_expense = 0;
        $expensegraph = $this->expense_model->getExpenseHeadData($start_date, $end_date);
        foreach ($expensegraph as $value) {
            if (!empty($value['total'])) {
                $month_expense += $value['total'];
            }
        }

        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

        $response = array(
            'status' => 'success',
            'data' => array(
                'amount' => $month_expense,
                'amount_formatted' => $month_expense ? ($currency_symbol . amountFormat($month_expense)) : ''
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function whatsapp_sent_widget()
    {
        $start = date('Y-m-01 00:00:00');
        $end   = date('Y-m-t 23:59:59');
        $row = $this->db
            ->select_sum('recipient_count')
            ->where('sent_at >=', $start)
            ->where('sent_at <=', $end)
            ->get('whatsapp_message_log')->row();

        $count = $row ? (int) $row->recipient_count : 0;

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data'   => ['count' => $count],
            ]));
    }

    public function income_donut_widget()    {
        if (!$this->rbac->hasPrivilege('income_donut_graph', 'can_view')) {
            access_denied();
        }

        $this->load->helper('custom');
        $this->load->model('income_model');

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $incomegraph = $this->income_model->getIncomeHeadsData($start_date, $end_date);

        $labels = [];
        $data = [];
        $colors = [];
        $i = 1;
        foreach ($incomegraph as $value) {
            $labels[] = $value['income_category'];
            $data[] = (float)$value['total'];
            $colors[] = incomegraphColors($i++);
            if ($i == 8) {
                $i = 1;
            }
        }

        $response = array(
            'status' => 'success',
            'data' => array(
                'labels' => $labels,
                'values' => $data,
                'colors' => $colors,
            ),
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function expense_donut_widget()
    {
        if (!$this->rbac->hasPrivilege('expense_donut_graph', 'can_view')) {
            access_denied();
        }

        $this->load->helper('custom');
        $this->load->model('expense_model');

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $expensegraph = $this->expense_model->getExpenseHeadData($start_date, $end_date);

        $labels = [];
        $data = [];
        $colors = [];
        $i = 1;
        foreach ($expensegraph as $value) {
            $labels[] = $value['exp_category'];
            $data[] = (float)$value['total'];
            $colors[] = expensegraphColors($i++);
            if ($i == 8) {
                $i = 1;
            }
        }

        $response = array(
            'status' => 'success',
            'data' => array(
                'labels' => $labels,
                'values' => $data,
                'colors' => $colors,
            ),
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function staff_approved_leave_widget()
    {
        if (!$this->rbac->hasPrivilege('staff_approved_leave_widegts', 'can_view')) {
            access_denied();
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');

        $total = count($this->apply_leave_model->getStaffMonthlyLeave($start_date, $end_date));
        $approved = count($this->apply_leave_model->getStaffApproveMonthlyLeave($start_date, $end_date));
        $percent = ($total > 0) ? ($approved * 100) / $total : 0;

        $response = array(
            'status' => 'success',
            'data' => array(
                'total' => $total,
                'approved' => $approved,
                'percent' => round($percent, 2)
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function student_approved_leave_widget()
    {
        if (!$this->rbac->hasPrivilege('student_approved_leave_widegts', 'can_view')) {
            access_denied();
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');

        $total = count($this->apply_leave_model->getStudentMonthlyLeave($start_date, $end_date));
        $approved = count($this->apply_leave_model->getStudentApproveMonthlyLeave($start_date, $end_date));
        $percent = ($total > 0) ? ($approved * 100) / $total : 0;

        $response = array(
            'status' => 'success',
            'data' => array(
                'total' => $total,
                'approved' => $approved,
                'percent' => round($percent, 2)
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function converted_leads_widget()
    {
        if (!$this->rbac->hasPrivilege('conveted_leads_widegts', 'can_view')) {
            access_denied();
        }

        // No date filter — show all-time totals
        $enquiry = $this->admin_model->getAllEnquiryCount();
        $total = isset($enquiry['total']) ? (int)$enquiry['total'] : 0;
        $complete = isset($enquiry['complete']) ? (int)$enquiry['complete'] : 0;
        $percent = ($total > 0) ? ($complete * 100) / $total : 0;

        $response = array(
            'status' => 'success',
            'data' => array(
                'total' => $total,
                'complete' => $complete,
                'percent' => round($percent, 2)
            )
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function fees_collection_expenses_monthly_widget()
    {
        if (!$this->rbac->hasPrivilege('fees_collection_and_expense_monthly_chart', 'can_view')) {
            access_denied();
        }

        $cache_key = 'dash_monthly_chart_' . date('Y-m-d');
        $response = $this->getDashboardCache($cache_key, 300, function () {
            $input = $this->setting_model->getCurrentSessionName();
            list($a, $b) = explode('-', $input);
            $Current_year = $a;
            if (strlen($b) == 2) {
                $Next_year = substr($a, 0, 2) . $b;
            } else {
                $Next_year = $b;
            }

            $ar = $this->startmonthandend();
            $year_str_month = $Current_year . '-' . $ar[0] . '-01';
            $year_end_month = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));

            $getDepositeAmount = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
            $student_transport_fee = $this->studenttransportfee_model->getTransportDepositAmountBetweenDate($year_str_month, $year_end_month);

            $startdate = date('m/01/Y');
            $enddate = date('m/t/Y');
            $start = strtotime($startdate);
            $end = strtotime($enddate);
            $currentdate = $start;
            $month_days = array();
            $days_collection = array();
            $days_expense = array();
            $days_incidental = array();

            while ($currentdate <= $end) {
                $cur_date = date('Y-m-d', $currentdate);
                $month_days[] = date('d', $currentdate);
                $coll_amt = $this->whatever($getDepositeAmount, $cur_date, $cur_date);
                $tranport_amt = $this->whatever($student_transport_fee, $cur_date, $cur_date);
                $days_collection[] = convertBaseAmountCurrencyFormat($coll_amt + $tranport_amt);
                
                // Get incidental fee collection for this day
                $this->load->model('Incidental_fee_collection_model');
                $incidental_amt = $this->Incidental_fee_collection_model->getTotalCollectionBetweenDate($cur_date, $cur_date);
                $days_incidental[] = convertBaseAmountCurrencyFormat($incidental_amt ?: 0);
                
                $ct = $this->getExpensebyday($cur_date);
                $days_expense[] = convertBaseAmountCurrencyFormat($ct);
                $currentdate = strtotime('+1 day', $currentdate);
            }

            return array(
                'status' => 'success',
                'data' => array(
                    'labels' => $month_days,
                    'collection' => $days_collection,
                    'incidental' => $days_incidental,
                    'expense' => $days_expense,
                )
            );
        });

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function fees_collection_expenses_session_widget()
    {
        if (!$this->rbac->hasPrivilege('fees_collection_and_expense_yearly_chart', 'can_view')) {
            access_denied();
        }

        $current_session = $this->setting_model->getCurrentSession();
        $cache_key = 'dash_session_chart_' . $current_session;
        $response = $this->getDashboardCache($cache_key, 300, function () {
            $input = $this->setting_model->getCurrentSessionName();
            list($a, $b) = explode('-', $input);
            $Current_year = $a;
            if (strlen($b) == 2) {
                $Next_year = substr($a, 0, 2) . $b;
            } else {
                $Next_year = $b;
            }

            $ar = $this->startmonthandend();
            $year_str_month = $Current_year . '-' . $ar[0] . '-01';
            $year_end_month = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));

            $getDepositeAmount = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
            $student_transport_fee = $this->studenttransportfee_model->getTransportDepositAmountBetweenDate($year_str_month, $year_end_month);

            $start_month = strtotime($year_str_month);
            $end = strtotime($year_end_month);
            $total_month = array();
            $yearly_collection = array();
            $yearly_expense = array();

            while ($start_month <= $end) {
                $total_month[] = $this->lang->line(strtolower(date('F', $start_month)));
                $month_start = date('Y-m-d', $start_month);
                $month_end = date("Y-m-t", $start_month);
                $return = $this->whatever($getDepositeAmount, $month_start, $month_end);
                $tranport_amt = $this->whatever($student_transport_fee, $month_start, $month_end);
                $yearly_collection[] = (!IsNullOrEmptyString($return) || !IsNullOrEmptyString($tranport_amt)) ? convertBaseAmountCurrencyFormat($return + $tranport_amt) : "0.00";

                $expense_monthly = $this->expense_model->getTotalExpenseBwdate($month_start, $month_end);
                if (!empty($expense_monthly)) {
                    $yearly_expense[] = convertBaseAmountCurrencyFormat($expense_monthly->amount);
                } else {
                    $yearly_expense[] = "0.00";
                }

                $start_month = strtotime("+1 month", $start_month);
            }

            return array(
                'status' => 'success',
                'data' => array(
                    'labels' => $total_month,
                    'collection' => $yearly_collection,
                    'expense' => $yearly_expense,
                )
            );
        });

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
	
    public function getUserImage()
    {
        $id     = $this->session->userdata["admin"]["id"];
        $result = $this->staff_model->get($id);
    }

    public function getSession()
    {
        if (!$this->rbac->hasPrivilege('quick_session_change', 'can_view')) {
            access_denied();
        }
        $session             = $this->session_model->getAllSession();
        $data                = array();
        $session_array       = $this->session->has_userdata('session_array');
        $data['sessionData'] = array('session_id' => 0);
        if ($session_array) {
            $data['sessionData'] = $this->session->userdata('session_array');
        } else {
            $setting             = $this->setting_model->get();
            $data['sessionData'] = array('session_id' => $setting[0]['session_id']);
        }
        $data['sessionList'] = $session;
        $this->load->view('admin/partial/_session', $data);
    }

    public function updateSession()
    {
        $session       = $this->input->post('popup_session');
        $session_array = $this->session->has_userdata('session_array');
        if ($session_array) {
            $this->session->unset_userdata('session_array');
        }
        $session       = $this->session_model->get($session);
        $session_array = array('session_id' => $session['id'], 'session' => $session['session']);
        $this->session->set_userdata('session_array', $session_array);
        echo json_encode(array('status' => 1, 'message' => $this->lang->line('session_changed_successfully')));
    }

    public function updatePurchaseCode()
    {
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('envato_market_purchase_code', $this->lang->line('purchase_code'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'email'                       => form_error('email'),
                'envato_market_purchase_code' => form_error('envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->app_update();
        }
    }

    public function backup()
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/backup');
        $this->session->set_userdata('inner_menu', 'admin/backup');
        $data['title'] = $this->lang->line('backup_history');
        if ($this->input->server('REQUEST_METHOD') == "POST") {
            if ($this->input->post('backup') == "upload") {
                $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
                if ($this->form_validation->run() == false) {

                } else {
                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $fileInfo  = pathinfo($_FILES["file"]["name"]);
                        $file_name = "db-" . date("Y-m-d_H-i-s") . ".sql";
                        $upload_dir = "./backup/temp_uploaded/";
                        $this->customlib->ensureDirectoryExists($upload_dir);
                        move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir . $file_name);
                        $folder_name  = 'temp_uploaded';
                        $path         = './backup/';
                        $filePath     = $path . $folder_name . '/' . $file_name;
                        $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                        $db           = (array) get_instance()->db;
                        $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                        $sql   = '';
                        $error = '';

                        if (file_exists($filePath)) {
                            $lines = file($filePath);

                            foreach ($lines as $line) {

                                // Ignoring comments from the SQL script
                                if (substr($line, 0, 2) == '--' || $line == '') {
                                    continue;
                                }

                                $sql .= $line;

                                if (substr(trim($line), -1, 1) == ';') {
                                    $result = mysqli_query($conn, $sql);
                                    if (!$result) {
                                        $error .= mysqli_error($conn) . "\n";
                                    }
                                    $sql = '';
                                }
                            }
                            $msg = $this->lang->line('restored_message');
                        } // end if file exists

                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                        redirect('admin/admin/backup');
                    }
                }
            }
            if ($this->input->post('backup') == "backup") {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                $this->load->helper('download');
                $this->load->dbutil();
                $version  = $this->customlib->getAppVersion();
                $filename = "db_ver_" . $version . '_' . date("Y-m-d_H-i-s") . ".sql";
                $prefs    = array(
                    'ignore'     => array(),
                    'format'     => 'txt',
                    'filename'   => 'mybackup.sql',
                    'add_drop'   => true,
                    'add_insert' => true,
                    'newline'    => "\n",
                );
                $backup = $this->dbutil->backup($prefs);
                $this->load->helper('file');
                write_file('./backup/database_backup/' . $filename, $backup);
                redirect('admin/admin/backup');
                force_download($filename, $backup);
                $this->session->set_flashdata('feedback', $this->lang->line('success_message_for_client_to_see'));
                redirect('admin/admin/backup');
            } else if ($this->input->post('backup') == "restore") {
                $folder_name  = 'database_backup';
                $file_name    = $this->input->post('filename');
                $path         = './backup/';
                $filePath     = $path . $folder_name . '/' . $file_name;
                $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                $db           = (array) get_instance()->db;
                $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                $sql   = '';
                $error = '';

                if (file_exists($filePath)) {
                    $lines = file($filePath);

                    foreach ($lines as $line) {

                        // Ignoring comments from the SQL script
                        if (substr($line, 0, 2) == '--' || $line == '') {
                            continue;
                        }

                        $sql .= $line;

                        if (substr(trim($line), -1, 1) == ';') {
                            $result = mysqli_query($conn, $sql);
                            if (!$result) {
                                $error .= mysqli_error($conn) . "\n";
                            }
                            $sql = '';
                        }
                    }
                    $msg = $this->lang->line('restored_message');
                } // end if file exists
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $msg . '</div>');
                redirect('admin/admin/backup');
            }
        }
        $dir    = "./backup/database_backup/";
        $result = array();
        if (is_dir($dir)) {
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, array(".", ".."))) {
                    $fullPath = $dir . DIRECTORY_SEPARATOR . $value;
                    if (is_dir($fullPath)) {
                        // Recursively collect files under the subdirectory
                        $files = array();
                        try {
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::LEAVES_ONLY
                            );
                            foreach ($iterator as $fileinfo) {
                                // store path relative to the subdirectory
                                $files[] = substr($fileinfo->getPathname(), strlen($fullPath) + 1);
                            }
                        } catch (Exception $e) {
                            // on error, keep files empty for this folder
                            $files = array();
                        }
                        $result[$value] = $files;
                    } else {
                        $result[] = $value;
                    }
                }
            }
        }
        $data['dbfileList']  = $result;
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/backup', $data);
        $this->load->view('layout/footer', $data);
    }

    public function student_birthdays_widget()
    {
        $today_date = date('Y-m-d');
        $data['student_birthdays'] = $this->Student_model->getBirthDayStudents($today_date, false, false);
        $data['sch_setting'] = $this->sch_setting_detail;

        $html = $this->load->view('admin/widgets/student_birthdays_widget', $data, true);
        echo json_encode([
            'status' => 'success',
            'count' => count($data['student_birthdays']),
            'html' => $html,
        ]);
    }

    public function staff_birthdays_widget()
    {
        $staff_birthdays = [];
        $monday = date('Y-m-d', strtotime('last monday'));
        $sunday = date('Y-m-d', strtotime('next sunday'));

        $current_date = $monday;
        while (strtotime($current_date) <= strtotime($sunday)) {
            $daily_birthdays = $this->Staff_model->getBirthDayStaff($current_date, 1, false, false);
            if (!empty($daily_birthdays)) {
                $staff_birthdays = array_merge($staff_birthdays, $daily_birthdays);
            }
            $current_date = date('Y-m-d', strtotime('+1 day', strtotime($current_date)));
        }

        $data['staff_birthdays'] = $staff_birthdays;
        $data['sch_setting'] = $this->sch_setting_detail;

        $html = $this->load->view('admin/widgets/staff_birthdays_widget', $data, true);
        echo json_encode([
            'status' => 'success',
            'count' => count($staff_birthdays),
            'html' => $html,
        ]);
    }

    public function changepass()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'changepass/index');
        $data['title'] = 'Change Password';

        $role = json_decode((string) $this->customlib->getStaffRole());
        $role_id = isset($role->id) ? (int) $role->id : 0;
        $role_name = strtolower(trim((string) ($role->name ?? '')));
        $is_super_admin = ($role_id === 7) || in_array($role_name, array('super admin', 'superadmin'), true);

        if ($is_super_admin) {
            $this->form_validation->set_rules('current_pass', $this->lang->line("current_password"), 'trim|xss_clean');
        } else {
            $this->form_validation->set_rules('current_pass', $this->lang->line("current_password"), 'trim|required|xss_clean');
        }
        $this->form_validation->set_rules('new_pass', $this->lang->line("new_password"), 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', $this->lang->line("confirm_password"), 'trim|required|xss_clean');
        $data['is_super_admin_reset'] = $is_super_admin;
        if ($this->form_validation->run() == false) {
            $sessionData            = $this->session->userdata('admin');
            $this->data['id']       = $sessionData['id'];
            $this->data['username'] = $sessionData['username'];
            $this->load->view('layout/header', $data);
            $this->load->view('admin/change_password', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sessionData = $this->session->userdata('admin');
            $userdata    = $this->customlib->getUserData();
            $data_array  = array(
                'current_pass' => $this->input->post('current_pass'),
                'new_pass'     => md5($this->input->post('new_pass')),
                'user_id'      => $sessionData['id'],
                'user_email'   => $sessionData['email'],
                'user_name'    => $sessionData['username'],
            );
            $newdata = array(
                'id'       => $sessionData['id'],
                'password' => $this->enc_lib->passHashEnc($this->input->post('new_pass')),
            );

            if ($is_super_admin) {
                $query2 = $this->admin_model->saveNewPass($newdata);
                if ($query2) {
                    $data['error_message'] = "<div class='alert alert-success'>" . $this->lang->line("password_changed_successfully") . "</div>";
                } else {
                    $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("something_went_wrong") . "</div>";
                }

                $this->load->view('layout/header', $data);
                $this->load->view('admin/change_password', $data);
                $this->load->view('layout/footer', $data);
                return;
            }

            $check  = $this->enc_lib->passHashDyc($this->input->post('current_pass'), $userdata["password"]);
            $query1 = $this->admin_model->checkOldPass($data_array);

            if ($query1) {

                if ($check) {
                    $query2 = $this->admin_model->saveNewPass($newdata);
                    if ($query2) {
                        $data['error_message'] = "<div class='alert alert-success'>" . $this->lang->line("password_changed_successfully") . "</div>";
                        $this->load->view('layout/header', $data);
                        $this->load->view('admin/change_password', $data);
                        $this->load->view('layout/footer', $data);
                    }
                } else {
                    $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("invalid_current_password") . "</div>";
                    $this->load->view('layout/header', $data);
                    $this->load->view('admin/change_password', $data);
                    $this->load->view('layout/footer', $data);
                }
            } else {

                $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("invalid_current_password") . "</div>";
                $this->load->view('layout/header', $data);
                $this->load->view('admin/change_password', $data);
                $this->load->view('layout/footer', $data);
            }
        }
    }

    public function pdf_report()
    {
        $data        = array();
        $html        = $this->load->view('reports/students_detail', $data, true);
        $pdfFilePath = "output_pdf_name.pdf";
        $this->load->library('m_pdf');
        /** @var stdClass $m_pdf */
        $m_pdf = $this->m_pdf;
        $m_pdf->pdf->WriteHTML($html);
        $m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function downloadbackup($file)
    {
        $this->load->helper('download');
        $filepath = "./backup/database_backup/" . $file;
        if (!file_exists($filepath) || !is_readable($filepath)) {
            $this->session->set_flashdata('error', 'Backup file not found or not readable.');
            redirect('admin/admin/backup');
            return;
        }
        $data     = file_get_contents($filepath);
        $name     = $file;
        force_download($name, $data);
    }

    public function dropbackup($file)
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_delete')) {
            access_denied();
        }
        unlink('./backup/database_backup/' . $file);
        redirect('admin/admin/backup');
    }

    public function search()
    {
        $search_text=$this->input->post('search_text1');
        if(!isset($search_text)){
            $search_text="";
        }

        $data['title']           = 'Search';
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['search_text']     = trim($search_text);
        $userdata                = $this->customlib->getUserData();
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $carray                  = array();
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/search', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getCollectionbymonth()
    {
        $result = $this->admin_model->getMonthlyCollection();
        return $result;
    }

    public function getCollectionbyday($date)
    {
        $result = $this->admin_model->getCollectionbyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebyday($date)
    {
        $result = $this->admin_model->getExpensebyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebymonth()
    {
        $result = $this->admin_model->getMonthlyExpense();
        return $result;
    }

    public function whatever($feecollection_array, $start_month_date, $end_month_date)
    {
        $return_amount = 0;
        $st_date       = strtotime($start_month_date);
        $ed_date       = strtotime($end_month_date);
        if (!empty($feecollection_array)) {
            while ($st_date <= $ed_date) {
                $date = date('Y-m-d', $st_date);
                foreach ($feecollection_array as $key => $value) {

                    if ($value['date'] == $date) {

                        if (is_numeric($value['amount']) && is_numeric($value['amount_fine'])) {
                        $return_amount = $return_amount + $value['amount'] + $value['amount_fine'];
                    }
                    }
                }
                $st_date = $st_date + 86400;
            }
        } else {

        }

        return $return_amount;
    }

    public function startmonthandend()
    {
        $startmonth = $this->setting_model->getStartMonth();
        if ($startmonth == 1) {
            $endmonth = 12;
        } else {
            $endmonth = $startmonth - 1;
        }
        return array($startmonth, $endmonth);
    }

    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('sql');
            $temp        = explode(".", $_FILES["file"]["name"]);
            $extension   = end($temp);
            $error       = '';
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["file"]["type"] != 'application/octet-stream') {
                // @phpstan-ignore-next-line
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_type_not_allowed"));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                // @phpstan-ignore-next-line
                $this->form_validation->set_message('handle_upload', $this->lang->line("extension_not_allowed"));
                return false;
            }
            if ($_FILES["file"]["size"] > 104857600) { // 100MB = 104857600 bytes
                // @phpstan-ignore-next-line
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_size_shoud_be_less_than") . ' 100 MB');
                return false;
            }
            return true;
        } else {
            // @phpstan-ignore-next-line
            $this->form_validation->set_message('handle_upload', $this->lang->line("the_file_field_is_required"));
            return false;
        }
    }

    public function generate_key($length = 12)
    {
        $str        = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max        = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function addCronsecretkey($id)
    {
        $key  = $this->generate_key(25);
        $data = array('cron_secret_key' => $key);
        $this->setting_model->add_cronsecretkey($data, $id);
        redirect('admin/admin/backup');
    }

    public function updateandappCode()
    {
        $this->form_validation->set_rules('app-email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->andapp_update();
        }
    }

    public function filetype()
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_view')) {
            access_denied();
        }
        
        $data          = array();
        $data['title'] = 'File Type List';
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/filetype');
        $data['filetype'] = $this->filetype_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/filetype', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addfiletype()
    {
        $this->form_validation->set_rules('file_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('image_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('file_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('file_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'file_extension'  => form_error('file_extension'),
                'file_mime'       => form_error('file_mime'),
                'image_extension' => form_error('image_extension'),
                'image_mime'      => form_error('image_mime'),
                'image_size'      => form_error('image_size'),
                'file_size'       => form_error('file_size'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $insert_array = array(
                'file_extension'  => $this->input->post('file_extension'),
                'file_mime'       => $this->input->post('file_mime'),
                'image_extension' => $this->input->post('image_extension'),
                'image_mime'      => $this->input->post('image_mime'),
                'file_size'       => $this->input->post('file_size'),
                'image_size'      => $this->input->post('image_size'),
            );

            $this->filetype_model->add($insert_array);

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function validate_extension($extension)
    {
        if (preg_match('/^([A-Za-z0-9]+)(,\s[A-Za-z0-9]+)*$/', $extension)) {
            return true;
        } else {
            // @phpstan-ignore-next-line
            $this->form_validation->set_message('validate_extension', 'The %s field must be like jpg, jpeg');
            return false;
        }
    }

    public function validate_mime($mime)
    {
        if (preg_match('/^([A-Za-z0-9-.+\/]+)(,\s[A-Za-z0-9-.+\/]+)*$/', $mime)) {
            return true;
        } else {
            // @phpstan-ignore-next-line
            $this->form_validation->set_message('validate_mime', 'The %s field must be like audio/mp4, video/mp4');
            return false;
        }
    }

    public function updateaddon()
    {
        $this->form_validation->set_rules('app-email', $this->lang->line('email'), 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', $this->lang->line('purchase_code'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );

            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->addon_update();
        }
    }

    public function searchvalidation()
    {
        $search_text1 = $this->input->post('search_text1');
        $params       = array('search_text1' => $search_text1);
        $array        = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function search_text()
    {
        $search_text1 = $this->input->post('search_text');
        $params       = array('search_text' => $search_text1);
        $array        = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function dtstudentlist()
    {
        $search_text     = $this->input->post('search_text');
        $sch_setting     = $this->sch_setting_detail;
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $classlist       = $this->class_model->get();
        $carray          = array();
        if (!empty($classlist)) {
            foreach ($classlist as $ckey => $cvalue) {
                $carray[] = $cvalue["id"];
            }
        }
        $search=$this->input->post('search');

        $resultlist      = $this->student_model->searchFullText($search_text, $carray);
        $start           = $this->input->post('start');
        $length          = $this->input->post('length');
     
        $resultlist_view = $this->student_model->getSearchFullView($search_text, $start, $length,$search, $carray);

        $data = array(
            'resultlist'      => $resultlist_view,
            'sch_setting'     => $this->sch_setting_detail,
            'adm_auto_insert' => $this->sch_setting_detail->adm_auto_insert,
            'currency_symbol' => $this->customlib->getSchoolCurrencyFormat(),
        );

        $resultlist_view = $this->load->view('admin/resultlist_view', $data, true);

        $fields   = $this->customfield_model->get_custom_fields('students', 1);
        $students = json_decode($resultlist);
        $dt_data  = array();
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {

                $editbtn    = '';
                $deletebtn  = '';
                $viewbtn    = '';
                $collectbtn = "";
                $viewbtn    = "<a href='" . base_url() . "student/view/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('show') . "'><i class='fa fa-reorder'></i></a>";

                if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "student/edit/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) {

                    $collectbtn = "<a href='" . base_url() . "studentfee/addfee/" . $student->student_session_id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('add_fees') . "'><span >" . $currency_symbol . "</a>";
                }

                $row   = array();
                $row[] = $student->admission_no;
                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";
                $row[] = $student->roll_no;
                $row[] = $student->class . "(" . $student->section . ")";
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }

                $row[] = $this->customlib->dateformat($student->dob);

                $row[] = $this->lang->line(strtolower($student->gender));
                if ($sch_setting->category) {
                    $row[] = $student->category;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno;
                }

                foreach ($fields as $fields_key => $fields_value) {

                    $custom_name   = $fields_value->name;
                    $display_field = $student->$custom_name;
                    if ($fields_value->type == "link") {
                        $display_field = "<a href=" . $student->$custom_name . " target='_blank'>" . $student->$custom_name . "</a>";
                    }
                    $row[] = $display_field;
                }
                $row[] = $viewbtn . '' . $editbtn . '' . $collectbtn;
                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($students->draw),
            "recordsTotal"    => intval($students->recordsTotal),
            "recordsFiltered" => intval($students->recordsFiltered),
            "data"            => $dt_data,
            "resultlist_view" => $resultlist_view,
        );
        echo json_encode($json_data);

    }

}
