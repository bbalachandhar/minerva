<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staff extends Admin_Controller
{

    public $sch_setting_detail = array();

    public function __construct()
    {
        parent::__construct();
		$this->load->library('SaasValidation');
        $this->load->library('media_storage');
        $this->load->library('media_storage');
        $this->config->load("payroll");
        $this->config->load("app-config");
        $this->load->library('Enc_lib');
        $this->load->library('mailsmsconf');
        $this->load->model("staff_model");
        $this->load->library('encoding_lib');
        $this->load->model("leaverequest_model");
        $this->load->model("biometric_device_model"); // Load the new model
        $this->load->model("day_status_model"); // Day-lock overlay for attendance report
        $this->contract_type      = $this->config->item('contracttype');
        $this->marital_status     = $this->config->item('marital_status');
        $this->staff_attendance   = $this->config->item('staffattendance');
        $this->payroll_status     = $this->config->item('payroll_status');
        $this->payment_mode       = $this->config->item('payment_mode');
        $this->status             = $this->config->item('status');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    private function canEditLeavesSection()
    {
        $admin = $this->session->userdata('admin');
        $roles = isset($admin['roles']) && is_array($admin['roles']) ? $admin['roles'] : [];
        foreach ($roles as $role_name => $role_id) {
            $normalized = strtolower(trim((string) $role_name));
            if ($normalized === 'super admin' || $normalized === 'admin') {
                return true;
            }
        }
        return false;
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_view')) {
            access_denied();
        }

        $data['title']  = $this->lang->line('staff_list');
        $data['fields'] = $this->customfield_model->get_custom_fields('staff', 1);
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff');
        $search             = $this->input->post("search");
        $resultlist         = $this->staff_model->searchFullText("", 1);
        $data['resultlist'] = $resultlist;
        $staffRole          = $this->staff_model->getStaffRole();
        $data["role"]       = $staffRole;
        $data["role_id"]    = "";
        
        // Get staff categories for filter
        $this->db->select('id, name, color, icon');
        $categories = $this->db->get('staff_designation_category')->result_array();
        $data['categories'] = $categories;

        // Get departments for filter
        $data['departments'] = $this->db->select('id, department_name')->order_by('department_name')->get('department')->result_array();
        $data['department_selected'] = '';
        
        $search_text        = $this->input->post('search_text');
        if (isset($search)) {
            if ($search == 'search_filter') {
                $data['searchby']    = "filter";
                $role                = $this->input->post('role');
                $category            = $this->input->post('category');
                $department          = $this->input->post('department');
                $data['employee_id'] = $this->input->post('empid');
                $data["role_id"]     = $role;
                $data['department_selected'] = !empty($department) ? $department : '';
                $data['search_text'] = $this->input->post('search_text');
                $resultlist          = $this->staff_model->getEmployee($role, 1, null, $department);
                
                // Filter by category if provided
                if (!empty($category)) {
                    $resultlist = array_filter($resultlist, function($staff) use ($category) {
                        return isset($staff['category_id']) && $staff['category_id'] == $category;
                    });
                }
                
                $data['resultlist']  = $resultlist;
            } else if ($search == 'search_full') {
                $data['searchby']    = "text";
                $data['search_text'] = trim($this->input->post('search_text'));
                $resultlist          = $this->staff_model->searchFullText($search_text, 1);
                $data['resultlist']  = $resultlist;
                $data['title']       = $this->lang->line('search_details') . ': ' . $data['search_text'];
            }
        }

        $this->load->view('layout/header');
        $this->load->view('admin/staff/staffsearch', $data);
        $this->load->view('layout/footer');
    }

    public function disablestafflist()
    {
        if (!$this->rbac->hasPrivilege('disable_staff', 'can_view')) {
            access_denied();
        }

        if (isset($_POST['role']) && $_POST['role'] != '') {
            $data['search_role'] = $_POST['role'];
        } else {
            $data['search_role'] = "";
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff/disablestafflist');
        $data['title'] = 'Staff Search';
        $staffRole     = $this->staff_model->getStaffRole();

        $data["role"]       = $staffRole;
        $search             = $this->input->post("search");
        $search_text        = $this->input->post('search_text');
        $resultlist         = $this->staff_model->searchFullText('', 0);
        $data['resultlist'] = $resultlist;

        if (isset($search)) {
            if ($search == 'search_filter') {
                $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
                if ($this->form_validation->run() == false) {
                    $resultlist         = array();
                    $data['resultlist'] = $resultlist;
                } else {
                    $data['searchby']    = "filter";
                    $role                = $this->input->post('role');
                    $data['employee_id'] = $this->input->post('empid');
                    $data['search_text'] = $this->input->post('search_text');
                    $resultlist          = $this->staff_model->getEmployee($role, 0);
                    $data['resultlist']  = $resultlist;
                }
            } else if ($search == 'search_full') {
                $data['searchby']    = "text";
                $data['search_text'] = trim($this->input->post('search_text'));
                $resultlist          = $this->staff_model->searchFullText($search_text, 0);
                $data['resultlist']  = $resultlist;
                $data['title']       = 'Search Details: ' . $data['search_text'];
            }
        }
        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/disablestaff', $data);
        $this->load->view('layout/footer', $data);
    }

    public function profile($id)
    {
        $data['enable_disable'] = 1;
        if ($this->customlib->getStaffID() == $id) {
            $data['enable_disable'] = 0;
        }
        else if (!$this->rbac->hasPrivilege('staff', 'can_view')) {
            access_denied();
        }

        $this->load->model("staffattendancemodel");
        $this->load->model("setting_model");
        $data["id"]      = $id;
        $data['title']   = 'Staff Details';
        $staff_info      = $this->staff_model->getProfile($id);

        // Generate barcode/QR on-demand if the files were never created (e.g. imported via SQL)
        if (!empty($staff_info) && !empty($this->sch_setting_detail->staff_barcode)) {
            $barcode_file = FCPATH . 'uploads/staff_id_card/barcodes/' . $staff_info['id'] . '.png';
            $qrcode_file  = FCPATH . 'uploads/staff_id_card/qrcode/'   . $staff_info['id'] . '.png';
            if (!file_exists($barcode_file) || !file_exists($qrcode_file)) {
                $scan_type = $this->sch_setting_detail->scan_code_type;
                $this->customlib->generatestaffbarcode($staff_info['employee_id'], $staff_info['id'], $scan_type);
            }
        }

        $userdata        = $this->customlib->getUserData();
        $userid          = $userdata['id'];
        $timeline_status = '';
        if ($userid == $id) {
            $timeline_status = 'yes';
        }
        $timeline_list         = $this->timeline_model->getStaffTimeline($id, $timeline_status);
        $data["timeline_list"] = $timeline_list;
        $staff_payroll         = $this->staff_model->getStaffPayroll($id);
        $staff_leaves          = $this->leaverequest_model->staff_leave_request($id);

        $alloted_leavetype           = $this->staff_model->allotedLeaveType($id);
        $data['sch_setting']         = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $this->load->model("payroll_model");
        $salary = $this->payroll_model->getSalaryDetails($id);

        // Ensure $salary is always an array with numeric defaults to avoid "Trying to access array offset on value of type null" in views
        if (empty($salary)) {
            $salary = [
                'net_salary'     => 0,
                'earnings'       => 0,
                'deduction'      => 0,
                'basic_salary'   => 0,
                'tax'            => 0,
                'leave_deduction'=> 0,
                'employee_epf'   => 0,
                'employee_esi'   => 0,
            ];
        } elseif (is_object($salary)) {
            $salary = (array) $salary;
        }
        // Normalize missing keys
        $salary['net_salary']      = isset($salary['net_salary']) ? $salary['net_salary'] : 0;
        $salary['earnings']        = isset($salary['earnings']) ? $salary['earnings'] : 0;
        $salary['deduction']       = isset($salary['deduction']) ? $salary['deduction'] : 0;
        $salary['basic_salary']    = isset($salary['basic_salary']) ? $salary['basic_salary'] : 0;
        $salary['tax']             = isset($salary['tax']) ? $salary['tax'] : 0;
        $salary['leave_deduction'] = isset($salary['leave_deduction']) ? $salary['leave_deduction'] : 0;
        $salary['employee_epf']    = isset($salary['employee_epf']) ? $salary['employee_epf'] : 0;
        $salary['employee_esi']    = isset($salary['employee_esi']) ? $salary['employee_esi'] : 0;

        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;
        $leaveDetail                 = array();

        $leave_rows = $this->db->query(" 
            SELECT
                lt.id AS leave_type_id,
                lt.type,
                sld.id AS staff_leave_detail_id,
                COALESCE(smlb.closing_balance, sld.alloted_leave, 0) AS base_balance,
                COALESCE(smlb.used_for_lop_adjustment, 0) AS used_for_lop_adjustment,
                COALESCE(smlb.used_for_leave_application, 0) AS used_for_leave_application,
                -- Total balance available at the start of the selected payroll period
                COALESCE(smlb.opening_balance, 0) + COALESCE(smlb.earned_in_month, 0) + COALESCE(smlb.admin_adjustment, 0) AS period_balance,
                -- Closing balance of the most recent month after the selected
                -- (consumed) row. Months carry forward so the latest closing IS
                -- the current available balance — do NOT sum consecutive months.
                COALESCE((
                    SELECT b2.closing_balance
                    FROM staff_monthly_leave_balance b2
                    WHERE b2.staff_id = ?
                      AND b2.leave_type_id = lt.id
                      AND b2.used_for_lop_adjustment   = 0
                      AND b2.used_for_leave_application = 0
                      AND (
                            b2.year  > COALESCE(smlb.year, 0)
                         OR (b2.year = COALESCE(smlb.year, 0) AND b2.month > COALESCE(smlb.month, 0))
                          )
                    ORDER BY b2.year DESC, b2.month DESC
                    LIMIT 1
                ), 0) AS unprocessed_balance,
                COALESCE((
                    SELECT b.used_for_lop_adjustment + b.used_for_leave_application
                    FROM staff_monthly_leave_balance b
                    WHERE b.staff_id = ?
                      AND b.leave_type_id = lt.id
                      AND (b.used_for_lop_adjustment + b.used_for_leave_application) > 0
                    ORDER BY b.year DESC, b.month DESC, b.id DESC
                    LIMIT 1
                ), 0) AS last_consumed,
                smlb.year,
                smlb.month,
                lt.credit_source_type_id,
                lt.requires_balance_check,
                CASE
                    WHEN COALESCE(lt.credit_source_type_id, 0) > 0 THEN COALESCE((
                        SELECT SUM(r.leave_days)
                        FROM staff_leave_request r
                        WHERE r.staff_id = ?
                          AND r.leave_type_id = lt.id
                          AND r.leave_direction = 'credit'
                          AND r.status IN ('approve', 'approved')
                          AND (
                                smlb.id IS NULL
                                OR r.leave_from > LAST_DAY(STR_TO_DATE(CONCAT(smlb.year, '-', LPAD(smlb.month, 2, '0'), '-01'), '%Y-%m-%d'))
                              )
                    ), 0)
                    ELSE 0
                END AS extra_credit,
                CASE
                    WHEN COALESCE(lt.credit_source_type_id, 0) > 0 THEN COALESCE((
                        SELECT SUM(r.leave_days)
                        FROM staff_leave_request r
                        WHERE r.staff_id = ?
                          AND r.leave_type_id = lt.id
                          AND r.leave_direction = 'debit'
                          AND r.status IN ('approve', 'approved')
                          AND (
                                smlb.id IS NULL
                                OR r.leave_from > LAST_DAY(STR_TO_DATE(CONCAT(smlb.year, '-', LPAD(smlb.month, 2, '0'), '-01'), '%Y-%m-%d'))
                              )
                    ), 0)
                    ELSE 0
                END AS extra_debit
            FROM leave_types lt
            LEFT JOIN staff_leave_details sld
                ON sld.staff_id = ?
               AND sld.leave_type_id = lt.id
            LEFT JOIN staff_monthly_leave_balance smlb
                ON smlb.id = (
                    SELECT b.id
                    FROM staff_monthly_leave_balance b
                    WHERE b.staff_id = ?
                      AND b.leave_type_id = lt.id
                      AND (
                            b.closing_balance  > 0
                         OR b.opening_balance  > 0
                         OR b.admin_adjustment != 0
                         OR b.used_for_lop_adjustment   > 0
                         OR b.used_for_leave_application > 0
                      )
                    ORDER BY
                        -- Prefer rows where something was actually consumed so that
                        -- cron-only rows (earned only, no LOP/leave) do not hide the
                        -- most recent payroll-processed month.
                        (CASE WHEN b.used_for_lop_adjustment   > 0
                                OR b.used_for_leave_application > 0
                              THEN 0 ELSE 1 END) ASC,
                        b.year DESC, b.month DESC, b.id DESC
                    LIMIT 1
                )
            WHERE lt.is_active = 'yes'
            ORDER BY lt.type ASC
        ", array($id, $id, $id, $id, $id, $id))->result_array();

        foreach ($leave_rows as $row) {
            $leave_type_id    = (int) ($row['leave_type_id'] ?? 0);
            $display_balance  = (float) ($row['base_balance'] ?? 0);
            $approve_leave    = 0.0;

            if ((int) ($row['credit_source_type_id'] ?? 0) > 0) {
                $extra_credit    = (float) ($row['extra_credit'] ?? 0);
                $extra_debit     = (float) ($row['extra_debit'] ?? 0);
                $unprocessed     = (float) ($row['unprocessed_balance'] ?? 0);
                $display_balance += $extra_credit - $extra_debit;
                $approve_leave = (float) ($row['used_for_lop_adjustment'] ?? 0)
                    + (float) ($row['used_for_leave_application'] ?? 0)
                    + $extra_debit;
                // When an admin_adjustment in a later month credits CPL outside of leave
                // requests, unprocessed_balance holds the authoritative closing. Use the
                // max so neither the request-based estimate nor the monthly balance under-counts.
                $effective_balance = max((float) $display_balance, $unprocessed);
                $available_balance = max(0.0, $effective_balance);
                $consumed_balance  = max(0.0, (float) $approve_leave);
                $opening_balance   = $unprocessed > 0
                    ? max(0.0, (float) ($row['period_balance'] ?? 0))
                    : max(0.0, $available_balance + $consumed_balance);
            } elseif (!empty($row['year']) || !empty($row['month'])) {
                // Row is the last payroll-processed month (or most recent non-zero if none).
                // Consumed  = LOP + leave used in that payroll period.
                // Opening   = opening + earned + admin of that row (balance at period start).
                // Available = closing of the MOST RECENT month (unprocessed_balance holds
                //             the latest row's closing after the consumed row; months carry
                //             forward so only the final closing is the true current balance —
                //             summing consecutive months would double-count the carry).
                //             Falls back to the consumed row's own closing if no later row.
                $approve_leave     = (float) ($row['used_for_lop_adjustment'] ?? 0)
                                   + (float) ($row['used_for_leave_application'] ?? 0);
                $unprocessed       = (float) ($row['unprocessed_balance'] ?? 0);
                $base              = (float) $display_balance;
                $available_balance = max(0.0, $unprocessed > 0 ? $unprocessed : $base);
                $consumed_balance  = max(0.0, $approve_leave);
                $opening_balance   = max(0.0, (float) ($row['period_balance'] ?? 0));
            } else {
                $count_leave_row = $this->leaverequest_model->countLeavesData($id, $leave_type_id);
                $approve_leave = isset($count_leave_row['approve_leave']) ? (float) $count_leave_row['approve_leave'] : 0.0;
                $available_balance = max(0.0, (float) $display_balance);
                $consumed_balance  = max(0.0, (float) $approve_leave);
                $opening_balance   = max(0.0, $available_balance + $consumed_balance);
            }

            $leaveDetail[] = array(
                'id'            => $leave_type_id,
                'altid'         => isset($row['staff_leave_detail_id']) ? $row['staff_leave_detail_id'] : null,
                'type'          => $row['type'],
                'alloted_leave' => $display_balance,
                'approve_leave' => $approve_leave,
                'available'     => $available_balance,
                'consumed'      => $consumed_balance,
                'opening'       => $opening_balance,
                'year'          => $row['year'],
                'month'         => $row['month'],
            );
        }

        // Monthly breakdown per leave type — used by the simplified Leaves tab history table.
        // One row per month showing opening, earned, LOP used, leave used, and closing balance.
        $leave_monthly_breakdown = [];
        if ($this->db->table_exists('staff_monthly_leave_balance')) {
            $breakdown_rows = $this->db
                ->select('smlb.leave_type_id, lt.type as leave_type_name,
                          smlb.month, smlb.year,
                          smlb.opening_balance, smlb.admin_adjustment, smlb.earned_in_month,
                          smlb.used_for_lop_adjustment, smlb.used_for_leave_application,
                          smlb.closing_balance')
                ->from('staff_monthly_leave_balance smlb')
                ->join('leave_types lt', 'lt.id = smlb.leave_type_id', 'left')
                ->where('smlb.staff_id', (int) $id)
                ->where('(smlb.opening_balance > 0 OR smlb.earned_in_month > 0 OR smlb.admin_adjustment != 0
                          OR smlb.used_for_lop_adjustment > 0 OR smlb.used_for_leave_application > 0
                          OR smlb.closing_balance > 0)', null, false)
                ->order_by('smlb.leave_type_id', 'ASC')
                ->order_by('smlb.year', 'DESC')
                ->order_by('smlb.month', 'DESC')
                ->get()->result_array();

            foreach ($breakdown_rows as $br) {
                $lt_id = (int) ($br['leave_type_id'] ?? 0);
                if (!isset($leave_monthly_breakdown[$lt_id])) {
                    $leave_monthly_breakdown[$lt_id] = [];
                }
                $leave_monthly_breakdown[$lt_id][] = $br;
            }
        }

        $leave_transactions = [];
        $date_based_leave_type_ids = [];
        if ($this->db->table_exists('staff_leave_balance_audit')) {
            $leave_transactions = $this->db
                ->select('a.id, a.leave_type_id, a.action_type, a.amount, a.balance_before, a.balance_after, a.reference_id, a.reference_type, a.reason, a.created_at, lt.type as leave_type_name')
                ->from('staff_leave_balance_audit a')
                ->join('leave_types lt', 'lt.id = a.leave_type_id', 'left')
                ->where('a.staff_id', (int) $id)
                ->where_not_in('a.action_type', ['LOP_ADJUSTMENT', 'PAYROLL_OD_SYNC'])
                ->order_by('a.created_at', 'DESC')
                ->order_by('a.id', 'DESC')
                ->limit(300)
                ->get()
                ->result_array();

            foreach ($leave_transactions as $audit_txn) {
                $audit_action = strtoupper(trim((string) ($audit_txn['action_type'] ?? '')));
                if (strpos($audit_action, 'CREDIT') !== false) {
                    $date_based_leave_type_ids[(int) ($audit_txn['leave_type_id'] ?? 0)] = true;
                }
            }
        }

        $monthly_credit_rows = [];

        // Monthly earned/admin credits are stored in monthly balance rows (not always in audit).
        // Append them so leave cards show credit entries with dates.
        if ($this->db->table_exists('staff_monthly_leave_balance')) {
            $monthly_credit_rows = $this->db
                ->select('smlb.id, smlb.leave_type_id, lt.type as leave_type_name, smlb.year, smlb.month, smlb.opening_balance, smlb.earned_in_month, smlb.admin_adjustment, smlb.used_for_lop_adjustment, smlb.used_for_leave_application, smlb.closing_balance, smlb.last_processed_date, smlb.created_at, smlb.updated_at')
                ->from('staff_monthly_leave_balance smlb')
                ->join('leave_types lt', 'lt.id = smlb.leave_type_id', 'left')
                ->where('smlb.staff_id', (int) $id)
                ->order_by('smlb.year', 'DESC')
                ->order_by('smlb.month', 'DESC')
                ->order_by('smlb.id', 'DESC')
                ->get()
                ->result_array();

            foreach ($monthly_credit_rows as $credit_row) {
                $earned_credit = (float) ($credit_row['earned_in_month'] ?? 0);
                $admin_credit = (float) ($credit_row['admin_adjustment'] ?? 0);
                $credit_amount = $earned_credit + max(0.0, $admin_credit);

                $event_time = !empty($credit_row['created_at'])
                    ? $credit_row['created_at']
                    : $credit_row['last_processed_date'];

                $ym_label = sprintf('%04d-%02d', (int) ($credit_row['year'] ?? 0), (int) ($credit_row['month'] ?? 0));
                if ($credit_amount > 0) {
                    $opening_balance = (float) ($credit_row['opening_balance'] ?? 0);
                    $leave_transactions[] = [
                        'id' => 0,
                        'leave_type_id' => (int) ($credit_row['leave_type_id'] ?? 0),
                        'action_type' => 'MONTHLY_CREDIT',
                        'amount' => $credit_amount,
                        'balance_before' => $opening_balance,
                        'balance_after' => $opening_balance + $credit_amount,
                        'reference_id' => (int) ($credit_row['id'] ?? 0),
                        'reference_type' => 'monthly_balance',
                        'reason' => 'Monthly leave credit for ' . $ym_label,
                        'created_at' => $event_time,
                        'leave_type_name' => $credit_row['leave_type_name'] ?? '',
                    ];
                }

                $lop_adjusted = (float) ($credit_row['used_for_lop_adjustment'] ?? 0);
                if ($lop_adjusted > 0 && empty($date_based_leave_type_ids[(int) ($credit_row['leave_type_id'] ?? 0)])) {
                    $lop_event_time = !empty($credit_row['last_processed_date'])
                        ? $credit_row['last_processed_date']
                        : (!empty($credit_row['updated_at']) ? $credit_row['updated_at'] : $event_time);

                    $balance_after = (float) ($credit_row['closing_balance'] ?? 0);
                    $balance_before = $balance_after + $lop_adjusted;

                    $leave_transactions[] = [
                        'id' => 0,
                        'leave_type_id' => (int) ($credit_row['leave_type_id'] ?? 0),
                        'action_type' => 'LOP_ADJUSTMENT',
                        'amount' => $lop_adjusted,
                        'balance_before' => $balance_before,
                        'balance_after' => $balance_after,
                        'reference_id' => (int) ($credit_row['id'] ?? 0),
                        'reference_type' => 'monthly_balance',
                        'reason' => 'Payroll LOP adjustment for ' . $ym_label,
                        'created_at' => $lop_event_time,
                        'leave_type_name' => $credit_row['leave_type_name'] ?? '',
                    ];
                }
            }
        }

        if ($this->db->table_exists('staff_leave_request')) {
            $credit_requests = $this->db
                ->select('slr.id, slr.leave_type_id, slr.leave_from, slr.leave_to, slr.leave_days, slr.created_at, slr.updated_at, lt.type as leave_type_name')
                ->from('staff_leave_request slr')
                ->join('leave_types lt', 'lt.id = slr.leave_type_id', 'left')
                ->where('slr.staff_id', (int) $id)
                ->where('slr.leave_direction', 'credit')
                ->where_in('slr.status', ['approve', 'approved'])
                ->order_by('slr.leave_from', 'DESC')
                ->order_by('slr.id', 'DESC')
                ->get()
                ->result_array();

            foreach ($credit_requests as $request_row) {
                $date_based_leave_type_ids[(int) ($request_row['leave_type_id'] ?? 0)] = true;
                $applied_for_date = !empty($request_row['leave_from']) ? $request_row['leave_from'] : null;
                $approved_on_date = !empty($request_row['updated_at']) ? $request_row['updated_at'] : ($request_row['created_at'] ?? null);
                $leave_transactions[] = [
                    'id' => 0,
                    'leave_type_id' => (int) ($request_row['leave_type_id'] ?? 0),
                    'action_type' => 'CREDIT_APPLIED',
                    'amount' => (float) ($request_row['leave_days'] ?? 0),
                    'balance_before' => 0,
                    'balance_after' => 0,
                    'reference_id' => (int) ($request_row['id'] ?? 0),
                    'reference_type' => 'leave_request',
                    'reason' => 'Approved credit request',
                    'created_at' => $approved_on_date,
                    'applied_for_date' => $applied_for_date,
                    'approved_on_date' => $approved_on_date,
                    'leave_type_name' => $request_row['leave_type_name'] ?? '',
                ];
            }
        }

        if (!empty($monthly_credit_rows) && !empty($date_based_leave_type_ids)) {
            foreach ($monthly_credit_rows as $credit_row) {
                $leave_type_id = (int) ($credit_row['leave_type_id'] ?? 0);
                $lop_adjusted = (float) ($credit_row['used_for_lop_adjustment'] ?? 0);
                if (empty($date_based_leave_type_ids[$leave_type_id]) || $lop_adjusted <= 0) {
                    continue;
                }

                $processed_on = !empty($credit_row['last_processed_date'])
                    ? $credit_row['last_processed_date']
                    : (!empty($credit_row['updated_at']) ? $credit_row['updated_at'] : $credit_row['created_at']);
                $payroll_month_label = date('F Y', strtotime(sprintf('%04d-%02d-01', (int) ($credit_row['year'] ?? 0), (int) ($credit_row['month'] ?? 0))));
                $balance_after = (float) ($credit_row['closing_balance'] ?? 0);
                $balance_before = $balance_after + $lop_adjusted;

                $leave_transactions[] = [
                    'id' => 0,
                    'leave_type_id' => $leave_type_id,
                    'action_type' => 'PAYROLL_DEBIT',
                    'amount' => $lop_adjusted,
                    'balance_before' => $balance_before,
                    'balance_after' => $balance_after,
                    'reference_id' => (int) ($credit_row['id'] ?? 0),
                    'reference_type' => 'monthly_balance',
                    'reason' => 'Used in payroll for ' . $payroll_month_label,
                    'created_at' => $processed_on,
                    'applied_for_label' => $payroll_month_label,
                    'approved_on_date' => $processed_on,
                    'leave_type_name' => $credit_row['leave_type_name'] ?? '',
                ];
            }
        }

        if (!empty($leave_transactions) && !empty($date_based_leave_type_ids)) {
            $leave_transactions = array_values(array_filter($leave_transactions, function ($txn) use ($date_based_leave_type_ids) {
                $leave_type_id = (int) ($txn['leave_type_id'] ?? 0);
                $action_type = strtoupper(trim((string) ($txn['action_type'] ?? '')));
                if (in_array($action_type, ['LOP_ADJUSTMENT', 'MONTHLY_CREDIT'], true) && !empty($date_based_leave_type_ids[$leave_type_id])) {
                    return false;
                }
                return true;
            }));

            $latest_payroll_debit_by_period = [];
            foreach ($leave_transactions as $txn) {
                $leave_type_id = (int) ($txn['leave_type_id'] ?? 0);
                $action_type = strtoupper(trim((string) ($txn['action_type'] ?? '')));
                if ($action_type !== 'PAYROLL_DEBIT' || empty($date_based_leave_type_ids[$leave_type_id])) {
                    continue;
                }

                $period_label = strtolower(trim((string) ($txn['applied_for_label'] ?? '')));
                if ($period_label === '') {
                    $period_label = strtolower(trim((string) ($txn['reason'] ?? '')));
                }
                $period_key = $leave_type_id . '|' . $period_label;
                $txn_time = isset($txn['created_at']) ? strtotime((string) $txn['created_at']) : 0;
                if (!isset($latest_payroll_debit_by_period[$period_key]) || $txn_time > $latest_payroll_debit_by_period[$period_key]['time']) {
                    $latest_payroll_debit_by_period[$period_key] = [
                        'time' => $txn_time,
                        'signature' => md5(json_encode([
                            $txn['leave_type_id'] ?? null,
                            $period_label,
                            $txn['action_type'] ?? null,
                            $txn['amount'] ?? null,
                            $txn['created_at'] ?? null,
                            $txn['reason'] ?? null,
                        ])),
                    ];
                }
            }

            if (!empty($latest_payroll_debit_by_period)) {
                $leave_transactions = array_values(array_filter($leave_transactions, function ($txn) use ($date_based_leave_type_ids, $latest_payroll_debit_by_period) {
                    $leave_type_id = (int) ($txn['leave_type_id'] ?? 0);
                    $action_type = strtoupper(trim((string) ($txn['action_type'] ?? '')));
                    if ($action_type !== 'PAYROLL_DEBIT' || empty($date_based_leave_type_ids[$leave_type_id])) {
                        return true;
                    }

                    $period_label = strtolower(trim((string) ($txn['applied_for_label'] ?? '')));
                    if ($period_label === '') {
                        $period_label = strtolower(trim((string) ($txn['reason'] ?? '')));
                    }
                    $period_key = $leave_type_id . '|' . $period_label;

                    $signature = md5(json_encode([
                        $txn['leave_type_id'] ?? null,
                        $period_label,
                        $txn['action_type'] ?? null,
                        $txn['amount'] ?? null,
                        $txn['created_at'] ?? null,
                        $txn['reason'] ?? null,
                    ]));

                    return isset($latest_payroll_debit_by_period[$period_key])
                        && $latest_payroll_debit_by_period[$period_key]['signature'] === $signature;
                }));
            }
        }

        if (!empty($leave_transactions)) {
            usort($leave_transactions, function ($a, $b) {
                $a_time = isset($a['created_at']) ? strtotime((string) $a['created_at']) : 0;
                $b_time = isset($b['created_at']) ? strtotime((string) $b['created_at']) : 0;
                if ($a_time === $b_time) {
                    return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
                }
                return $b_time <=> $a_time;
            });

            // Keep payload bounded for profile response.
            if (count($leave_transactions) > 400) {
                $leave_transactions = array_slice($leave_transactions, 0, 400);
            }
        }

        $data["leavedetails"]  = $leaveDetail;
        $data["leave_transactions"] = $leave_transactions;
        $data["leave_monthly_breakdown"] = $leave_monthly_breakdown;
        $data["staff_leaves"]  = $staff_leaves;
        $data['staffLeaveDetails'] = (array) $leaveDetail; // Ensure it's an array
        $data['staff_doc_id']  = $id;
        $data['staff']         = $staff_info;
        $data['staff_payroll'] = $staff_payroll;
        $data['salary']        = $salary;
        $monthlist             = $this->customlib->getMonthDropdown();
        $startMonth            = $this->setting_model->getStartMonth();
        $data["monthlist"]     = $monthlist;
        $data['yearlist']      = $this->staffattendancemodel->attendanceYearCount();
        $session_current       = $this->setting_model->getCurrentSessionName();
        $startMonth            = $this->setting_model->getStartMonth();
        $centenary             = substr($session_current, 0, 2); //2017-18 to 2017
        $year_first_substring  = substr($session_current, 2, 2); //2017-18 to 2017
        $year_second_substring = substr($session_current, 5, 2); //2017-18 to 18
        $month_number          = date("m", strtotime($startMonth));
        $data['rate_canview']  = 0;

        if ($id != '1') {
            $staff_rating = $this->staff_model->staff_ratingById($id);

            if ($staff_rating['total'] >= 3) {
                $data['rate'] = ($staff_rating['rate'] / $staff_rating['total']);
                $data['rate_canview'] = 1;
            }
            $data['reviews'] = $staff_rating['total'];
        }

        $data['reviews_comment'] = $this->staff_model->staff_ratingById($id);
        $year = date("Y");

        $staff_list              = $this->staff_model->user_reviewlist($id);
        $data['user_reviewlist'] = $staff_list;

        $attendence_count = array();
        $attendencetypes  = $this->attendencetype_model->getStaffAttendanceType();
        foreach ($attendencetypes as $att_key => $att_value) {
            $attendence_count[$att_value['type']] = array();
        }

        foreach ($monthlist as $key => $value) {
            $datemonth       = date("m", strtotime($key));
            $date_each_month = date('Y-' . $datemonth . '-01');

            $date_start = date('01', strtotime($date_each_month));
            $date_end   = date('t', strtotime($date_each_month));
            for ($n = $date_start; $n <= $date_end; $n++) {
                $att_dates        = $year . "-" . $datemonth . "-" . sprintf("%02d", $n);
                $date_array[]     = $att_dates;
                $staff_attendence = $this->staffattendancemodel->searchStaffattendance($att_dates, $id, false);

                if (!empty($staff_attendence)) {
                    if ($staff_attendence['att_type'] != "") {
                        $attendence_count[$staff_attendence['att_type']][] = 1;
                    }
                } else {

                }
                $res[$att_dates] = $staff_attendence;
            }
        }

        $session       = $this->setting_model->getCurrentSessionName();
        $session_start = explode("-", $session);
        $start_year    = $session_start[0];
        $date          = $start_year . "-" . $startMonth;
        $newdate       = date("Y-m-d", strtotime($date . "+1 month"));

        $data["countAttendance"]  = $attendence_count;
        // Derive accurate attendance keys from punch times (mirrors biometric processing logic).
        // Only applies to biometric records with valid in_time. Admin role (ID 1) used when no role mapped.
        $this->load->model('staffAttendaceSetting_model');
        $session_rows_profile = $this->staffattendancemodel->getAttendanceRowsInRange($id, $year.'-01-01', $year.'-12-31');
        $punch_map_profile = [];
        foreach ($session_rows_profile as $sr) {
            $punch_map_profile[$sr['date']] = [
                'in_time'              => $sr['in_time'],
                'out_time'             => $sr['out_time'],
                'biometric_attendence' => $sr['biometric_attendence'],
            ];
        }
        $att_role_settings_raw = $this->staffAttendaceSetting_model->getRoleAttendanceSetting();
        $att_role_settings_map = [];
        foreach ($att_role_settings_raw as $rs) {
            $att_role_settings_map[$rs->role_id][$rs->staff_attendence_type_id] = ['from' => $rs->entry_time_from, 'to' => $rs->entry_time_to];
        }
        $staff_role_id_profile = !empty($staff_info['role_id']) ? (int)$staff_info['role_id'] : 1;
        foreach ($res as $d => &$r) {
            if (empty($r['key'])) continue;
            $punch = isset($punch_map_profile[$d]) ? $punch_map_profile[$d] : null;
            if (!$punch || empty($punch['in_time']) || empty($punch['biometric_attendence'])) continue;
            $derived = $this->_derive_att_key_from_punches(
                $punch['in_time'], $punch['out_time'],
                $staff_role_id_profile, $att_role_settings_map, $this->sch_setting_detail
            );
            if ($derived !== null) { $r['key'] = $derived; }
        }
        unset($r);
        $data["resultlist"]       = $res;
        $data["attendence_array"] = range(01, 31);
        $data["date_array"]       = $date_array;
        $data["payroll_status"]   = $this->payroll_status;
        $data["payment_mode"]     = $this->payment_mode;
        $data["contract_type"]    = $this->contract_type;
        $data["status"]           = $this->status;
        $roles                    = $this->role_model->get();
        $data["roles"]            = $roles;
        $stafflist                = $this->staff_model->get();
        $data['stafflist']        = $stafflist;

        // Current month summary for attendance tab
        $this->load->model("holiday_model");
        $this->load->model("setting_model");
        $this->load->model("payroll_model");

        $current_year = date('Y');
        $current_month_number = date('m');
        $current_month_label = date('F Y');
        $today_date = date('Y-m-d');

        $holidays = $this->holiday_model->get();
        $official_holiday_dates = [];
        $compensation_dates = [];
        // separate normal holidays from compensation (working) days
        foreach ($holidays as $holiday_value) {
            $type_label = strtolower(trim($holiday_value['type'] ?? ''));
            $from_date  = new DateTime($holiday_value['from_date']);
            $to_date    = new DateTime($holiday_value['to_date']);
            $current    = clone $from_date;
            while ($current <= $to_date) {
                if ($current->format('m') == $current_month_number && $current->format('Y') == $current_year) {
                    $d = $current->format('Y-m-d');
                    if ($type_label === 'compensation') {
                        $compensation_dates[] = $d;
                    } else {
                        $official_holiday_dates[] = $d;
                    }
                }
                $current->modify('+1 day');
            }
        }
        $official_holiday_dates = array_values(array_unique($official_holiday_dates));
        $compensation_dates = array_values(array_unique($compensation_dates));

        $settings = $this->setting_model->getSetting();
        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;
        $isFourthSaturdayWeekend = isset($settings->isFourthSaturdayHoliday) ? (int)$settings->isFourthSaturdayHoliday : 0;

        $num_of_days = cal_days_in_month(CAL_GREGORIAN, (int)$current_month_number, (int)$current_year);
        $cutoff_day = (int)date('d');
        $second_saturday_date = null;
        $fourth_saturday_date = null;
        if ($isSecondSaturdayWeekend || $isFourthSaturdayWeekend) {
            $saturdayCount = 0;
            for ($day = 1; $day <= $num_of_days; $day++) {
                $date = new DateTime($current_year . "-" . $current_month_number . "-" . sprintf("%02d", $day));
                if ((int)$date->format('w') == 6) {
                    $saturdayCount++;
                    if ($saturdayCount == 2 && $isSecondSaturdayWeekend) {
                        $second_saturday_date = $date->format('Y-m-d');
                    }
                    if ($saturdayCount == 4 && $isFourthSaturdayWeekend) {
                        $fourth_saturday_date = $date->format('Y-m-d');
                        break;
                    }
                }
            }
        }

        $weekend_day_dates = [];
        for ($day = 1; $day <= $num_of_days; $day++) {
            $dateStr = $current_year . "-" . $current_month_number . "-" . sprintf("%02d", $day);
            $dayOfWeek = (int)date('w', strtotime($dateStr));
            if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date && $dateStr === $second_saturday_date) || ($fourth_saturday_date && $dateStr === $fourth_saturday_date)) {
                $weekend_day_dates[] = $dateStr;
            }
        }
        $weekend_day_dates = array_values(array_unique($weekend_day_dates));
        // remove any compensation days (they are working days)
        if (!empty($compensation_dates)) {
            $weekend_day_dates = array_values(array_diff($weekend_day_dates, $compensation_dates));
        }

        $holiday_dates_for_H = array_values(array_unique(array_filter($official_holiday_dates, function ($dateStr) use ($weekend_day_dates) {
            return !in_array($dateStr, $weekend_day_dates, true);
        })));

        $weekend_day_dates_mtd = array_values(array_filter($weekend_day_dates, function ($dateStr) use ($today_date) {
            return $dateStr <= $today_date;
        }));
        $holiday_dates_for_H_mtd = array_values(array_filter($holiday_dates_for_H, function ($dateStr) use ($today_date) {
            return $dateStr <= $today_date;
        }));

        $working_day_dates = [];
        for ($day = 1; $day <= $num_of_days; $day++) {
            $dateStr = $current_year . "-" . $current_month_number . "-" . sprintf("%02d", $day);
            if (!in_array($dateStr, $weekend_day_dates, true) && !in_array($dateStr, $holiday_dates_for_H, true)) {
                $working_day_dates[] = $dateStr;
            }
        }

        $working_day_dates = array_values(array_filter($working_day_dates, function ($dateStr) use ($today_date) {
            return $dateStr <= $today_date;
        }));

        // Get all raw attendance rows for the current month (for time-range checks and hasValidPunch)
        $month_start_str = $current_year . '-' . sprintf('%02d', (int)$current_month_number) . '-01';
        $month_end_str   = date('Y-m-t', strtotime($month_start_str));
        $month_raw_rows  = $this->staffattendancemodel->getAttendanceRowsInRange($id, $month_start_str, $month_end_str);
        $month_raw_map   = [];
        foreach ($month_raw_rows as $mr) {
            $month_raw_map[$mr['date']] = $mr;
        }

        // Build type_map (key_value → type id) for FHL, SHL, FHP, SHP
        $all_att_types_p = $this->staffattendancemodel->getStaffAttendanceType();
        $type_map_p = [];
        foreach ($all_att_types_p as $type_row_p) {
            $kv_p = strtoupper(trim($type_row_p['key_value'] ?? ''));
            if (!empty($kv_p)) $type_map_p[$kv_p] = (int)$type_row_p['id'];
        }

        // Load role attendance settings (FHL/SHL/FHP/SHP time windows) – same logic as staffattendancereport
        $admin_role_row_p  = $this->db->query("SELECT id FROM roles WHERE LOWER(name)='admin' ORDER BY id ASC LIMIT 1")->row_array();
        $admin_role_id_p   = !empty($admin_role_row_p['id']) ? (int)$admin_role_row_p['id'] : 1;
        $staff_role_id_p   = !empty($staff_info['role_id']) ? (int)$staff_info['role_id'] : $admin_role_id_p;
        $att_settings_p    = [
            'FHL' => !empty($type_map_p['FHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($staff_role_id_p, $type_map_p['FHL']) : false,
            'SHL' => !empty($type_map_p['SHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($staff_role_id_p, $type_map_p['SHL']) : false,
            'FHP' => !empty($type_map_p['FHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($staff_role_id_p, $type_map_p['FHP']) : false,
            'SHP' => !empty($type_map_p['SHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($staff_role_id_p, $type_map_p['SHP']) : false,
        ];
        $has_any_setting_p = !empty($att_settings_p['FHL']) || !empty($att_settings_p['SHL']) || !empty($att_settings_p['FHP']) || !empty($att_settings_p['SHP']);
        if (!$has_any_setting_p && $admin_role_id_p > 0 && $admin_role_id_p !== $staff_role_id_p) {
            $att_settings_p = [
                'FHL' => !empty($type_map_p['FHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id_p, $type_map_p['FHL']) : false,
                'SHL' => !empty($type_map_p['SHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id_p, $type_map_p['SHL']) : false,
                'FHP' => !empty($type_map_p['FHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id_p, $type_map_p['FHP']) : false,
                'SHP' => !empty($type_map_p['SHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id_p, $type_map_p['SHP']) : false,
            ];
        }

        // Count present / absent exactly as staffattendancereport does
        $present_like_keys_p = ['P', 'FHL', 'SHL', 'FHP', 'SHP', 'HD'];
        $present_count  = 0;
        $half_day_count = 0;
        $absent_count   = 0;
        foreach ($working_day_dates as $work_date) {
            $staff_attendance = $this->staffattendancemodel->searchStaffattendance($work_date, $id, false);
            $key     = strtoupper(trim($staff_attendance['key'] ?? ''));
            $raw_row = $month_raw_map[$work_date] ?? [];
            $raw_in  = trim($raw_row['in_time']  ?? '');
            $raw_out = trim($raw_row['out_time'] ?? '');
            $has_punch = ($raw_in !== '' && $raw_in !== '00:00:00') || ($raw_out !== '' && $raw_out !== '00:00:00');
            // hasValidPunch: present-like but no punch → treat as absent
            if (in_array($key, $present_like_keys_p, true) && !$has_punch) {
                $key = 'A';
            }
            if ($key === 'HD') {
                $half_day_count++;
            } elseif (in_array($key, ['P', 'FHL', 'SHL', 'FHP', 'SHP'], true)) {
                $present_count++;
            } else {
                $absent_count++;
            }
        }

        // Late and permission: iterate all raw month rows by in_time/out_time (matches report)
        $late_count       = 0;
        $permission_count = 0;
        foreach ($month_raw_rows as $raw_row_lp) {
            $raw_in_lp  = trim($raw_row_lp['in_time']  ?? '');
            $raw_out_lp = trim($raw_row_lp['out_time'] ?? '');
            $late_for_day = 0;
            if (!empty($raw_in_lp)) {
                if (!empty($att_settings_p['SHL']) && $this->_timeInRangeProfile($raw_in_lp, $att_settings_p['SHL']->entry_time_from, $att_settings_p['SHL']->entry_time_to)) {
                    $late_for_day = 1;
                } elseif (!empty($att_settings_p['FHL']) && $this->_timeInRangeProfile($raw_in_lp, $att_settings_p['FHL']->entry_time_from, $att_settings_p['FHL']->entry_time_to)) {
                    $late_for_day = 1;
                }
            }
            $late_count += $late_for_day;
            if (!empty($raw_in_lp) && !empty($att_settings_p['FHP']) && $this->_timeInRangeProfile($raw_in_lp, $att_settings_p['FHP']->entry_time_from, $att_settings_p['FHP']->entry_time_to)) {
                $permission_count++;
            }
            if (!empty($raw_out_lp) && !empty($att_settings_p['SHP']) && $this->_timeInRangeProfile($raw_out_lp, $att_settings_p['SHP']->entry_time_from, $att_settings_p['SHP']->entry_time_to)) {
                $permission_count++;
            }
        }

        $data['month_summary'] = [
            'label'        => $current_month_label,
            'working_days' => count($working_day_dates),
            'weekends'     => count($weekend_day_dates_mtd),
            'holidays'     => count($holiday_dates_for_H_mtd),
            'present'      => $present_count,
            'half_day'     => $half_day_count,
            'absent'       => $absent_count,
            'late'         => $late_count,
            'permission'   => $permission_count,
        ];

        // Year-wide weekend/holiday dates for attendance grid
        // Using same logic as staffattendancereport controller (with compensation handling)
        $all_holidays_year = $this->holiday_model->get();
        $official_holiday_dates_year = [];
        $compensation_dates_year = [];
        foreach ($all_holidays_year as $holiday_value) {
            $type_label = strtolower(trim($holiday_value['type'] ?? ''));
            $from_date = new DateTime($holiday_value['from_date']);
            $to_date = new DateTime($holiday_value['to_date']);
            $current = clone $from_date;
            while ($current <= $to_date) {
                if ($current->format('Y') == $current_year) {
                    $d = $current->format('Y-m-d');
                    if ($type_label === 'compensation') {
                        $compensation_dates_year[] = $d;
                    } else {
                        $official_holiday_dates_year[] = $d;
                    }
                }
                $current->modify('+1 day');
            }
        }
        // Include only real holidays
        $data['holiday_dates_year'] = array_values(array_unique($official_holiday_dates_year));
        // provide compensation dates for view
        $data['compensation_dates_year'] = array_values(array_unique($compensation_dates_year));
        $holiday_dates_year = $data['holiday_dates_year'];
        $weekend_day_dates_year = [];
        for ($m = 1; $m <= 12; $m++) {
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $m, (int)$current_year);

            $second_saturday_date_year = null;
            $fourth_saturday_date_year = null;
            if ($isSecondSaturdayWeekend || $isFourthSaturdayWeekend) {
                $saturdayCount = 0;
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date = new DateTime($current_year . "-" . sprintf("%02d", $m) . "-" . sprintf("%02d", $day));
                    if ((int)$date->format('w') == 6) {
                        $saturdayCount++;
                        if ($saturdayCount == 2 && $isSecondSaturdayWeekend) {
                            $second_saturday_date_year = $date->format('Y-m-d');
                        }
                        if ($saturdayCount == 4 && $isFourthSaturdayWeekend) {
                            $fourth_saturday_date_year = $date->format('Y-m-d');
                            break;
                        }
                    }
                }
            }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $dateStr = $current_year . "-" . sprintf("%02d", $m) . "-" . sprintf("%02d", $day);
                $dayOfWeek = (int)date('w', strtotime($dateStr));
                if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date_year && $dateStr === $second_saturday_date_year) || ($fourth_saturday_date_year && $dateStr === $fourth_saturday_date_year)) {
                    $weekend_day_dates_year[] = $dateStr;
                }
            }
        }

        // remove weekends that have been converted to working days via compensation
        if (!empty($compensation_dates_year)) {
            $weekend_day_dates_year = array_values(array_diff($weekend_day_dates_year, $compensation_dates_year));
        }
        $data['weekend_day_dates_year'] = array_values(array_unique($weekend_day_dates_year));

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffprofile', $data);
        $this->load->view('layout/footer', $data);
    }

    public function leaverequest()
    {
        if ((int) $this->customlib->getStaffID() <= 0) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff/leaverequest');
        $data['title'] = 'Leave Request';

        // Load leave requests from the model
        $staff_id = $this->customlib->getStaffID();
        log_message('debug', 'leaverequest: current_staff_id from customlib: ' . $staff_id);

        if (empty($staff_id)) {
            $staff_id = 0; // Default or handle as appropriate
            log_message('debug', 'leaverequest: staff_id is empty, set to: ' . $staff_id);
        }
        $data['staff_id'] = $staff_id; // Explicitly add staff_id to data array
        $is_admin_or_super_admin = $this->canEditLeavesSection();
        if ($is_admin_or_super_admin) {
            $data['leave_request'] = $this->leaverequest_model->staff_leave_request();
        } else {
            $data['leave_request'] = $this->leaverequest_model->staff_leave_request($staff_id);
        }
        
        // Fetch staff details, timetable, and potential substitutes
        $current_staff_id = $this->customlib->getStaffID(); // Use current logged-in staff ID
        log_message('debug', 'leaverequest: current_staff_id (again) : ' . $current_staff_id);

        $staff_details = $this->staff_model->get($current_staff_id);
        log_message('debug', 'leaverequest: staff_details for current_staff_id ' . $current_staff_id . ': ' . print_r($staff_details, true));

        $data['current_staff_details'] = $staff_details;

        $potential_substitutes = [];
        $recommender_staff = null;
        $approver_staff = null;
        if ($staff_details && isset($staff_details['department']) && !empty($staff_details['department'])) {
            log_message('debug', 'leaverequest: Staff department ID: ' . $staff_details['department']);
            $potential_substitutes = $this->staff_model->getEmployeeByDepartment($staff_details['department'], $current_staff_id);
            log_message('debug', 'leaverequest: Potential substitutes count: ' . count($potential_substitutes));
            log_message('debug', 'leaverequest: Potential substitutes: ' . print_r($potential_substitutes, true));

            // Fetch Recommender (HOD) details
            $this->load->model('department_model');
            $department = $this->department_model->getDepartmentType($staff_details['department']);
            if ($department && isset($department['dept_head_id']) && !empty($department['dept_head_id'])) {
                $recommender_details = $this->staff_model->get($department['dept_head_id']);
                if (!empty($recommender_details) && is_array($recommender_details)) {
                    $recommender_staff = $recommender_details;
                }
            }
        }
        $data['potential_substitutes'] = $potential_substitutes;

        // Fetch Approver details (from school settings)
        $this->load->model('setting_model'); // Ensure setting_model is loaded
        $setting = $this->setting_model->getSetting();
        if ($setting && !empty($setting->leave_approver_id)) {
            $approver_details = $this->staff_model->get($setting->leave_approver_id);
            if (!empty($approver_details) && is_array($approver_details)) {
                $approver_staff = $approver_details;
            }
        }

        if (empty($recommender_staff) && !empty($approver_staff)) {
            $recommender_staff = $approver_staff;
        }

        if (!empty($recommender_staff)) {
            $recommender_surname = !empty($recommender_staff['surname']) ? ' ' . $recommender_staff['surname'] : '';
            $data['recommender_info'] = $recommender_staff['name'] . $recommender_surname . ' (' . $recommender_staff['designation'] . ')';
            log_message('debug', 'leaverequest: Recommender Info: ' . $data['recommender_info']);
        } else {
            $data['recommender_info'] = $this->lang->line('not_assigned');
            log_message('debug', 'leaverequest: Recommender Info: Not Assigned');
        }

        if (!empty($approver_staff)) {
            $approver_surname = !empty($approver_staff['surname']) ? ' ' . $approver_staff['surname'] : '';
            $data['approver_info'] = $approver_staff['name'] . $approver_surname . ' (' . $approver_staff['designation'] . ')';
        } else {
            $data['approver_info'] = $this->lang->line('not_assigned');
        }
        $data['leave_approver_configured'] = !empty($approver_staff);
        $data['leave_screen_mode'] = 'claim_leave'; // Apply Leave Claim: OD/CPL only
        $staffRole             = $this->staff_model->getStaffRole();
        $data["staffrole"]     = $staffRole;
        
        $leavetype = $this->staff_model->getLeaveType();
        $data['leavetype'] = $leavetype;
        $data['status'] = $this->status;

        $data['is_admin_or_super_admin'] = $is_admin_or_super_admin;

        $setting_obj = $this->setting_model->getSetting();
        $csvToIntArray = function ($csv) {
            $parts = array_filter(array_map('trim', explode(',', (string) $csv)));
            $result = [];
            foreach ($parts as $part) {
                $value = (int) $part;
                if ($value > 0 && !in_array($value, $result, true)) {
                    $result[] = $value;
                }
            }
            return $result;
        };

        $data['leave_management_policy'] = [
            'substitution_required_roles' => $csvToIntArray((string) ($setting_obj->leave_substitution_required_roles ?? '')),
            'self_approve_roles' => $csvToIntArray((string) ($setting_obj->leave_self_approve_roles ?? '')),
            'past_date_allowed_roles' => $csvToIntArray((string) ($setting_obj->leave_past_date_allowed_roles ?? '')),
            'half_day_enabled' => (isset($setting_obj->leave_enable_half_day) ? (int) $setting_obj->leave_enable_half_day : 1) === 1,
            'half_day_allowed_roles' => $csvToIntArray((string) ($setting_obj->leave_half_day_allowed_roles ?? '')),
            'half_day_allowed_types' => $csvToIntArray((string) ($setting_obj->leave_half_day_allowed_types ?? '')),
        ];
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffleaverequest', $data); // Using the consolidated view
        $this->load->view('layout/footer', $data);
    }

    public function countAttendance($year, $emp)
    {
        $record = array();

        foreach ($this->staff_attendance as $att_key => $att_value) {
            $s           = $this->staff_model->count_attendance($year, $emp, $att_value);
            $r[$att_key] = $s;
        }

        $record[$year] = $r;
        return $record;
    }

    public function getSession()
    {
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

        return $data;
    }

    public function getSessionMonthDropdown()
    {
        $startMonth = $this->setting_model->getStartMonth();
        $array      = array();
        for ($m = $startMonth; $m <= $startMonth + 11; $m++) {
            $month         = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
            $array[$month] = $month;
        }
        return $array;
    }

    public function download($staff_id, $doc)
    {
        $stafflist = $this->staff_model->getProfile($staff_id);
        $this->media_storage->filedownload($stafflist[$doc], "./uploads/staff_documents/$staff_id");
    }

    public function doc_delete($id, $doc)
    {
        $this->staff_model->doc_delete($id, $doc);
        $this->session->set_flashdata('msg', '<i class="fa fa-check-square-o" aria-hidden="true"></i>' . $this->lang->line('delete_message') . '');
        redirect('admin/staff/profile/' . $id);
    }

    public function disablestaff($id)
    {
        if (!$this->rbac->hasPrivilege('disable_staff', 'can_view')) {
            access_denied();
        }

        if ((int)$this->customlib->getStaffID() === (int)$id) {
            $response = array('status' => 'fail', 'error' => array('staff' => 'You cannot disable your own account.'), 'message' => '');
            if ($this->input->is_ajax_request()) {
                echo json_encode($response);
                return;
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">You cannot disable your own account.</div>');
            redirect('admin/staff/profile/' . $id);
        }

        $data = array(
            'id'        => $id,
            'is_active' => 0,
            'disable_at'=> date('Y-m-d H:i:s'),
        );
        $this->staff_model->disablestaff($data);

        if ($this->input->is_ajax_request()) {
            echo json_encode(array('status' => 'success', 'error' => '', 'message' => 'Staff disabled successfully.'));
            return;
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Staff disabled successfully.</div>');
        redirect('admin/staff/profile/' . $id);
    }

    public function enablestaff($id)
    {
        if (!$this->rbac->hasPrivilege('disable_staff', 'can_view')) {
            access_denied();
        }

        $this->staff_model->enablestaff($id);

        if ($this->input->is_ajax_request()) {
            echo json_encode(array('status' => 'success', 'error' => '', 'message' => 'Staff enabled successfully.'));
            return;
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Staff enabled successfully.</div>');
        redirect('admin/staff/profile/' . $id);
    }

    public function change_password($id)
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('new_pass', $this->lang->line('new_password'), 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', $this->lang->line('confirm_password'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                'new_pass' => form_error('new_pass'),
                'confirm_pass' => form_error('confirm_pass'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $data['password'] = $this->enc_lib->passHashEnc($this->input->post('new_pass'));
            $data['id'] = $id;
            $this->staff_model->add($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('password_changed_successfully'));
        }
        echo json_encode($array);
    }

    public function ajax_attendance()
    {
        $this->load->model("staffattendancemodel");
        $this->load->model("holiday_model");
        $this->load->model("setting_model");
        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;

        $id           = $this->input->post("id");
        $year         = $this->input->post("year");
        $data["year"] = $year;
        if (!empty($year)) {

            $monthlist         = $this->customlib->getMonthDropdown();
            $startMonth        = $this->setting_model->getStartMonth();
            $data["monthlist"] = $monthlist;
            $data['yearlist']  = $this->staffattendancemodel->attendanceYearCount();
            $session_current   = $this->setting_model->getCurrentSessionName();
            $startMonth        = $this->setting_model->getStartMonth();

            // Weekend/holiday dates for selected year
            $settings = $this->setting_model->getSetting();
            $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
            $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
            $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;
            $isFourthSaturdayWeekend = isset($settings->isFourthSaturdayHoliday) ? (int)$settings->isFourthSaturdayHoliday : 0;

            $holidays = $this->holiday_model->get();
            $official_holiday_dates = [];
            $compensation_dates_year = [];
            foreach ($holidays as $holiday_value) {
                $type_label = strtolower(trim($holiday_value['type'] ?? ''));
                $from_date = new DateTime($holiday_value['from_date']);
                $to_date = new DateTime($holiday_value['to_date']);
                $current = clone $from_date;
                while ($current <= $to_date) {
                    if ($current->format('Y') == $year) {
                        $d = $current->format('Y-m-d');
                        if ($type_label === 'compensation') {
                            $compensation_dates_year[] = $d;
                        } else {
                            $official_holiday_dates[] = $d;
                        }
                    }
                    $current->modify('+1 day');
                }
            }
            $official_holiday_dates = array_values(array_unique($official_holiday_dates));
            $compensation_dates_year = array_values(array_unique($compensation_dates_year));

            $weekend_day_dates_year = [];
            for ($m = 1; $m <= 12; $m++) {
                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $m, (int)$year);

                $second_saturday_date_year = null;
                $fourth_saturday_date_year = null;
                if ($isSecondSaturdayWeekend || $isFourthSaturdayWeekend) {
                    $saturdayCount = 0;
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date = new DateTime($year . "-" . sprintf("%02d", $m) . "-" . sprintf("%02d", $day));
                        if ((int)$date->format('w') == 6) {
                            $saturdayCount++;
                            if ($saturdayCount == 2 && $isSecondSaturdayWeekend) {
                                $second_saturday_date_year = $date->format('Y-m-d');
                            }
                            if ($saturdayCount == 4 && $isFourthSaturdayWeekend) {
                                $fourth_saturday_date_year = $date->format('Y-m-d');
                                break;
                            }
                        }
                    }
                }

                for ($day = 1; $day <= $days_in_month; $day++) {
                    $dateStr = $year . "-" . sprintf("%02d", $m) . "-" . sprintf("%02d", $day);
                    $dayOfWeek = (int)date('w', strtotime($dateStr));
                    if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date_year && $dateStr === $second_saturday_date_year) || ($fourth_saturday_date_year && $dateStr === $fourth_saturday_date_year)) {
                        $weekend_day_dates_year[] = $dateStr;
                    }
                }
            }
            $weekend_day_dates_year = array_values(array_unique($weekend_day_dates_year));
            // strip compensation dates from weekends
            if (!empty($compensation_dates_year)) {
                $weekend_day_dates_year = array_values(array_diff($weekend_day_dates_year, $compensation_dates_year));
            }

            // Include all official holidays (don't filter out those on weekends)
            $holiday_dates_year = array_values(array_unique($official_holiday_dates));

            $data['weekend_day_dates_year'] = $weekend_day_dates_year;
            $data['holiday_dates_year'] = $holiday_dates_year;
            // expose compensatory working days so view can style them if needed
            $data['compensation_dates_year'] = $compensation_dates_year;

            foreach ($monthlist as $key => $value) {
                $datemonth       = date("m", strtotime($key));
                $date_each_month = date('Y-' . $datemonth . '-01');
                $date_end        = date('t', strtotime($date_each_month));
                for ($n = 1; $n <= $date_end; $n++) {
                    $att_date           = sprintf("%02d", $n);
                    $attendence_array[] = $att_date;
                    $datemonth          = date("m", strtotime($key));
                    $att_dates          = $year . "-" . $datemonth . "-" . sprintf("%02d", $n);

                    $date_array[]    = $att_dates;
                    $res[$att_dates] = $this->staffattendancemodel->searchStaffattendance($att_dates, $id);
                }
            }
            $date    = $year . "-" . $startMonth;
            $newdate = date("Y-m-d", strtotime($date . "+1 month"));
            $countAttendance         = $this->countAttendance($year, $id);
            $data["countAttendance"] = $countAttendance;         
            $data["id"]               = $id;
            // Derive accurate attendance keys from punch times (mirrors biometric processing logic).
            // Only applies to biometric records with valid in_time. Admin role (ID 1) used when no role mapped.
            $session_rows_ajax = $this->staffattendancemodel->getAttendanceRowsInRange($id, $year.'-01-01', $year.'-12-31');
            $punch_map_ajax = [];
            foreach ($session_rows_ajax as $sr) {
                $punch_map_ajax[$sr['date']] = [
                    'in_time'              => $sr['in_time'],
                    'out_time'             => $sr['out_time'],
                    'biometric_attendence' => $sr['biometric_attendence'],
                ];
            }
            $this->load->model('staffAttendaceSetting_model');
            $att_role_settings_raw_ajax = $this->staffAttendaceSetting_model->getRoleAttendanceSetting();
            $att_role_settings_map_ajax = [];
            foreach ($att_role_settings_raw_ajax as $rs) {
                $att_role_settings_map_ajax[$rs->role_id][$rs->staff_attendence_type_id] = ['from' => $rs->entry_time_from, 'to' => $rs->entry_time_to];
            }
            $staff_info_ajax = $this->staff_model->getProfile($id);
            $staff_role_id_ajax = !empty($staff_info_ajax['role_id']) ? (int)$staff_info_ajax['role_id'] : 1;
            foreach ($res as $d => &$r) {
                if (empty($r['key'])) continue;
                $punch = isset($punch_map_ajax[$d]) ? $punch_map_ajax[$d] : null;
                if (!$punch || empty($punch['in_time']) || empty($punch['biometric_attendence'])) continue;
                $derived = $this->_derive_att_key_from_punches(
                    $punch['in_time'], $punch['out_time'],
                    $staff_role_id_ajax, $att_role_settings_map_ajax, $settings
                );
                if ($derived !== null) { $r['key'] = $derived; }
            }
            unset($r);

            // Day-lock overlay: for approved OD/CPL leaves with strict_day_lock=1,
            // replace the biometric attendance key with the day_status label (e.g. OD, CPL, FH-OD).
            $start_of_year = $year . '-01-01';
            $end_of_year   = $year . '-12-31';
            $day_locks = $this->day_status_model->getDayStatusRange((int) $id, $start_of_year, $end_of_year);
            foreach ($day_locks as $locked_date => $lock_row) {
                if (isset($res[$locked_date])) {
                    $res[$locked_date]['key'] = $lock_row['status'];
                    $res[$locked_date]['day_lock'] = true;
                    $res[$locked_date]['payroll_impact'] = $lock_row['payroll_impact'];
                }
            }
            $data["resultlist"]       = $res;
            $data["attendence_array"] = $attendence_array;
            $data["date_array"]       = $date_array;

            $page = $this->load->view("admin/staff/ajaxattendance", $data, true);

            // Build compact att_data for client-side month view
            $att_data_js = [];
            foreach ($res as $d => $r) {
                if (!empty($r['key'])) {
                    $att_data_js[$d] = [
                        'key'      => $r['key'],
                        'in_time'  => isset($r['in_time'])  ? $r['in_time']  : '',
                        'out_time' => isset($r['out_time']) ? $r['out_time'] : '',
                    ];
                }
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(array(
                    'status'              => 1,
                    'countAttendance'     => $countAttendance[$year],
                    'page'                => $page,
                    'compensation_dates'  => $compensation_dates_year,
                    'att_data'            => $att_data_js,
                    'holidays'            => $holiday_dates_year,
                    'weekends'            => $weekend_day_dates_year,
                    'comp_dates'          => $compensation_dates_year,
                )));
        }
    }

    public function create()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staff');
        $data['title'] = 'Add Staff';
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $data['sch_setting'] = $this->sch_setting_detail;

        $data['roles'] = $this->role_model->get();
        $data['designation'] = $this->staff_model->getStaffDesignation();
        $data['department'] = $this->staff_model->getDepartment();
        
        // Get staff categories for dropdown
        $this->db->select('id, name, color, icon');
        $categories = $this->db->get('staff_designation_category')->result_array();
        $data['categories'] = $categories;
        
        $genderList                  = $this->customlib->getGender();
        $data['genderList']          = $genderList;
        $payscaleList                = $this->staff_model->getPayroll();
        $leavetypeList               = $this->staff_model->getLeaveType();
        $data["leavetypeList"]       = $leavetypeList;
        $data["payscaleList"]        = $payscaleList;
        $marital_status              = $this->marital_status;
        $data["marital_status"]      = $marital_status;
        $data["contract_type"]       = $this->contract_type;
        $custom_fields               = $this->customfield_model->getByBelong('staff');
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation']) {
                $custom_fields_id   = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[staff][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        $this->form_validation->set_rules('first_doc', $this->lang->line('image'), 'callback_handle_first_upload');
        $this->form_validation->set_rules('second_doc', $this->lang->line('image'), 'callback_handle_second_upload');
        $this->form_validation->set_rules('third_doc', $this->lang->line('image'), 'callback_handle_third_upload');
        $this->form_validation->set_rules('fourth_doc', $this->lang->line('image'), 'callback_handle_fourth_upload');
        
        $this->form_validation->set_rules(
            'email', $this->lang->line('email'), array('required', 'valid_email',
                array('check_exists', array($this->staff_model, 'valid_email_id')),
            )
        );
        if (!$this->sch_setting_detail->staffid_auto_insert) {
            $this->form_validation->set_rules('employee_id', $this->lang->line('staff_id'), 'callback_username_check');
        }

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');

        if ($this->form_validation->run() == true) {

            $custom_field_post  = $this->input->post("custom_fields[staff]");
            $custom_value_array = array();
            if (!empty($custom_fields)) {
                foreach ($custom_field_post as $key => $value) {
                    $check_field_type = $this->input->post("custom_fields[staff][" . $key . "]");
                    $field_value      = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                    $array_custom     = array(
                        'belong_table_id' => 0,
                        'custom_field_id' => $key,
                        'field_value'     => $field_value,
                    );
                    $custom_value_array[] = $array_custom;
                }
            }

            $employee_id       = $this->input->post("employee_id");
            $biometric_id      = $this->input->post("biometric_id");
            $department        = empty2null($this->input->post("department"));
            $designation       = empty2null($this->input->post("designation"));
            $role              = $this->input->post("role");
            $name              = $this->input->post("name");
            $gender            = $this->input->post("gender");
            $marital_status    = $this->input->post("marital_status");
            $dob               = $this->input->post("dob");
            $contact_no        = $this->input->post("contactno");
            $emergency_no      = $this->input->post("emergency_no");
            $email             = $this->input->post("email");
            $date_of_joining   = $this->input->post("date_of_joining");
            $date_of_leaving   = $this->input->post("date_of_leaving");
            $address           = $this->input->post("address");
            $qualification     = $this->input->post("qualification");
            $work_experience          = $this->input->post("work_experience");
            $basic_salary      = $this->input->post('basic_salary');
            $account_title     = $this->input->post("account_title");
            $bank_account_no   = $this->input->post("bank_account_no");
            $bank_name         = $this->input->post("bank_name");
            $ifsc_code         = $this->input->post("ifsc_code");
            $bank_branch       = $this->input->post("bank_branch");
            $contract_type     = $this->input->post("contract_type");
            $shift             = $this->input->post("shift");
            $location          = $this->input->post("location");
            $leave             = $this->input->post("leave");
            $facebook          = $this->input->post("facebook");
            $twitter           = $this->input->post("twitter");
            $linkedin          = $this->input->post("linkedin");
            $instagram         = $this->input->post("instagram");
            $permanent_address = $this->input->post("permanent_address");
            $father_name       = $this->input->post("father_name");
            $surname           = $this->input->post("surname");
            $mother_name       = $this->input->post("mother_name");
            $note              = $this->input->post("note");
            $esi_no            = $this->input->post("esi_no");
            $is_epf_enabled    = $this->input->post("is_epf_enabled") ? 1 : 0;
            $is_esi_enabled    = $this->input->post("is_esi_enabled") ? 1 : 0;
            $skip_payroll      = $this->input->post("skip_payroll") ? 1 : 0;
            $opening_ytd_income_raw = $this->input->post("opening_ytd_income");
            $opening_ytd_income = ($opening_ytd_income_raw !== '' && $opening_ytd_income_raw !== false && $opening_ytd_income_raw !== null && is_numeric($opening_ytd_income_raw)) ? round((float)$opening_ytd_income_raw, 2) : null;
            $opening_ytd_tax_raw = $this->input->post("opening_ytd_tax_deducted");
            $opening_ytd_tax = ($opening_ytd_tax_raw !== '' && $opening_ytd_tax_raw !== false && $opening_ytd_tax_raw !== null && is_numeric($opening_ytd_tax_raw)) ? round((float)$opening_ytd_tax_raw, 2) : null;
            $opening_ytd_fy_start_year_raw = $this->input->post("opening_ytd_fy_start_year");
            $opening_ytd_fy_start_year = ($opening_ytd_fy_start_year_raw !== '' && $opening_ytd_fy_start_year_raw !== false && $opening_ytd_fy_start_year_raw !== null && is_numeric($opening_ytd_fy_start_year_raw)) ? (int)$opening_ytd_fy_start_year_raw : null;
            $has_opening_ytd = ((float)($opening_ytd_income ?? 0) > 0) || ((float)($opening_ytd_tax ?? 0) > 0);
            if ($has_opening_ytd && empty($opening_ytd_fy_start_year)) {
                $opening_ytd_fy_start_year = ((int)date('n') >= 4) ? (int)date('Y') : ((int)date('Y') - 1);
            }
            if (!$has_opening_ytd) {
                $opening_ytd_fy_start_year = null;
            }

            $aadhaar_no = $this->input->post('aadhaar_no');
            $religion = $this->input->post('religion');
            $caste = $this->input->post('caste');
            $blood_group = $this->input->post('blood_group');
            $country = $this->input->post('country');
            $state = $this->input->post('state');
            $pincode = $this->input->post('pincode');

            $previous_salary = $this->input->post('previous_salary');
            $uan_no = $this->input->post('uan_no');
            $pan_no = $this->input->post('pan_no');
            $category_id = empty2null($this->input->post("category_id"));
            $au_fin_no_create = $this->input->post('au_fin_no');
            $aicte_coa_id_create = $this->input->post('aicte_coa_id');

            $password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);

            $data_insert = array(
                'password'               => $this->enc_lib->passHashEnc($password),
                'employee_id'            => $employee_id,
                'biometric_id'           => ($biometric_id == "") ? NULL : $biometric_id,
                'name'                   => $name,
                'email'                  => $email,
                'dob'                    => date('Y-m-d', $this->customlib->datetostrtotime($dob)),
                'date_of_leaving'        => '',
                'gender'                 => $gender,
                'payscale'               => '',
                'is_active'              => 1,
                'prefix'                 => $this->input->post('prefix'),
                'ug_qualification'       => $this->input->post('ug_qualification'),
                'pg_qualification'       => $this->input->post('pg_qualification'),
                'higher_qualification'   => $this->input->post('higher_qualification'),
                'qualified_exam'         => $this->input->post('qualified_exam'),
                'subject_specialization' => $this->input->post('subject_specialization'),
                'additional_qualification' => $this->input->post('additional_qualification'),
                'aadhaar_no' => $aadhaar_no,
                'religion' => $religion,
                'caste' => $caste,
                'blood_group' => $blood_group,
                'country' => $country,
                'state' => $state,
                'pincode' => $pincode,

                'previous_salary' => $previous_salary,
                'uan_no' => $uan_no,
                'pan_no' => $pan_no,
                'previous_institution' => $this->input->post('previous_institution'),
                'subject_expertise' => $this->input->post('subject_expertise'),
                'category_id' => $category_id,
                'au_fin_no'    => ($au_fin_no_create === '' || $au_fin_no_create === false) ? NULL : $au_fin_no_create,
                'aicte_coa_id' => ($aicte_coa_id_create === '' || $aicte_coa_id_create === false) ? NULL : $aicte_coa_id_create,
            );

            if (isset($surname)) {
                $data_insert['surname'] = $surname;
            }

            if (isset($department)) {
                $data_insert['department'] = $department;
            }

            if (isset($designation)) {
                $data_insert['designation'] = $designation;
            }

            if (isset($mother_name)) {
                $data_insert['mother_name'] = $mother_name;
            }

            if (isset($father_name)) {
                $data_insert['father_name'] = $father_name;
            }

            if (isset($contact_no)) {
                $data_insert['contact_no'] = $contact_no;
            }

            if (isset($emergency_no)) {
                $data_insert['emergency_contact_number'] = $emergency_no;
            }

            if (isset($marital_status)) {
                $data_insert['marital_status'] = $marital_status;
            }

            if (isset($address)) {
                $data_insert['local_address'] = $address;
            }

            if (isset($permanent_address)) {
                $data_insert['permanent_address'] = $permanent_address;
            }

            if (isset($work_experience)) {
                $data_insert['work_experience'] = $work_experience;
            }

            if (isset($note)) {
                $data_insert['note'] = $note;
            }

            if (isset($esi_no)) {
                $data_insert['esi_no'] = $esi_no;
            }

            $data_insert['is_epf_enabled'] = $is_epf_enabled;
            $data_insert['is_esi_enabled'] = $is_esi_enabled;
            $data_insert['skip_payroll'] = $skip_payroll;
            $data_insert['opening_ytd_income'] = $opening_ytd_income;
            $data_insert['opening_ytd_tax_deducted'] = $opening_ytd_tax;
            $data_insert['opening_ytd_fy_start_year'] = $opening_ytd_fy_start_year;

            if (isset($basic_salary)) {
                $data_insert['basic_salary'] = $basic_salary;
            }

            if (isset($contract_type)) {
                $data_insert['contract_type'] = $contract_type;
            }

            if (isset($shift)) {
                $data_insert['shift'] = $shift;
            }

            if (isset($location)) {
                $data_insert['location'] = $location;
            }

            if (isset($bank_account_no)) {
                $data_insert['bank_account_no'] = $bank_account_no;
            }

            if (isset($bank_name)) {
                $data_insert['bank_name'] = $bank_name;
            }

            if (isset($account_title)) {
                $data_insert['account_title'] = $account_title;
            }

            if (isset($ifsc_code)) {
                $data_insert['ifsc_code'] = $ifsc_code;
            }

            if (isset($bank_branch)) {
                $data_insert['bank_branch'] = $bank_branch;
            }

            if (isset($facebook)) {
                $data_insert['facebook'] = $facebook;
            }

            if (isset($twitter)) {
                $data_insert['twitter'] = $twitter;
            }

            if (isset($linkedin)) {
                $data_insert['linkedin'] = $linkedin;
            }

            if (isset($instagram)) {
                $data_insert['instagram'] = $instagram;
            }

            if ($date_of_joining != "") {
                $data_insert['date_of_joining'] = $this->customlib->dateFormatToYYYYMMDD($date_of_joining);
            }

            $data_insert['date_of_leaving'] = null;

            $leave_type  = $this->input->post('leave_type');
            $leave_array = array();
            if (!empty($leave_type)) {
                foreach ($leave_type as $leave_key => $leave_value) {
                    $leave_array[] = array(
                        'staff_id'      => 0,
                        'leave_type_id' => $leave_value,
                        'alloted_leave' => $this->input->post('alloted_leave_' . $leave_value),
                    );
                }
            }
            $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => 0);
//==========================
            $insert                                = true;
            $data_setting                          = array();
            $data_setting['id']                    = $this->sch_setting_detail->id;
            $data_setting['staffid_auto_insert']   = $this->sch_setting_detail->staffid_auto_insert;
            $data_setting['staffid_update_status'] = $this->sch_setting_detail->staffid_update_status;
            $employee_id                           = 0;

            if ($this->sch_setting_detail->staffid_auto_insert) {
                $year_prefix = !empty($this->sch_setting_detail->staffid_include_current_year) ? date('Y') : '';
                $id_prefix = $this->sch_setting_detail->staffid_prefix . $year_prefix;
                $number_digits = (int)$this->sch_setting_detail->staffid_no_digit - strlen($id_prefix);
                if ($number_digits < 1) {
                    $number_digits = 1;
                }

                if ($this->sch_setting_detail->staffid_update_status) {

                    $employee_id = $id_prefix . $this->sch_setting_detail->staffid_start_from;
                    $last_student = $this->staff_model->lastRecord();
                    $last_admission_digit = str_replace($id_prefix, "", $last_student->employee_id);

                    $employee_id                = $id_prefix . sprintf("%0" . $number_digits . "d", $last_admission_digit + 1);
                    $data_insert['employee_id'] = $employee_id;
                } else {
                    $employee_id                = $id_prefix . $this->sch_setting_detail->staffid_start_from;
                    $data_insert['employee_id'] = $employee_id;
                }

                $employee_id_exists = $this->staff_model->check_staffid_exists($employee_id);
                if ($employee_id_exists) {
                    $insert = false;
                }
            } else {

                $data_insert['employee_id'] = $this->input->post('employee_id');
            }
            //==========================
            if ($insert) {

                if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                                    $upload_result = $this->media_storage->fileupload("file", "./uploads/staff_images/");
                                    if ($upload_result['status'] === false) {
                                        $this->session->set_flashdata('error', $upload_result['message']);
                                        redirect('admin/staff/create');
                                    }
                                    $img_name             = $upload_result['message'];
                                    $data_insert['image'] = $img_name;                }

                $insert_id = $this->staff_model->batchInsert($data_insert, $role_array, $leave_array, $data_setting);
                $staff_id  = $insert_id;

                if (!$staff_id) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger">Staff could not be saved. Please check for duplicate /empty Biometric ID or contact the administrator.</div>');
                    redirect('admin/staff/create');
                }

                if (!empty($custom_value_array)) {
                    $this->customfield_model->insertRecord($custom_value_array, $insert_id);
                }

                $upload_dir = './uploads/staff_documents/' . $staff_id . '/';
                $this->customlib->ensureDirectoryExists($upload_dir);
                    
                if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                      $upload_result = $this->media_storage->fileupload("first_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/create');
                }
                $resume = $upload_result['message'];
                } else {
                    $resume = "";
                }

                if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
                     $upload_result = $this->media_storage->fileupload("second_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/create');
                }
                $joining_letter = $upload_result['message'];
                } else {
                    $joining_letter = "";
                }

                if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {
                    $upload_result = $this->media_storage->fileupload("third_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/create');
                }
                $resignation_letter = $upload_result['message'];
                } else {
                    $resignation_letter = "";
                }

                if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {
                   $upload_result = $this->media_storage->fileupload("fourth_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/create');
                }
                $fourth_doc = $upload_result['message'];
                } else {
                    $fourth_title = "";
                    $fourth_doc   = "";
                }

                $data_doc = array('id' => $staff_id, 'resume' => $resume, 'joining_letter' => $joining_letter, 'resignation_letter' => $resignation_letter, 'other_document_name' => $fourth_title, 'other_document_file' => $fourth_doc);
                $this->staff_model->add($data_doc);

                //***** generate barcode and qrcode of staff ******//
                    $scan_type= $this->sch_setting_detail->scan_code_type;
                    $this->customlib->generatestaffbarcode($data_insert['employee_id'],$staff_id,$scan_type);
                //***** generate barcode and qrcode of staff ******//

                //===================
                if ($staff_id) {
                    $teacher_login_detail = array('id' => $staff_id, 'credential_for' => 'staff', 'first_name' => $this->input->post("name"), 'last_name' => $this->input->post("surname"), 'username' => $email, 'password' => $password, 'contact_no' => $contact_no, 'email' => $email, 'employee_id' => $data_insert['employee_id']);
                    $this->mailsmsconf->mailsms('staff_login_credential', $teacher_login_detail);
                }
                //==========================

                $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');

                redirect('admin/staff');
            } else {
                $data['error_message'] = 'Admission No ' . $admission_no . ' already exists';
                $this->load->view('layout/header', $data);
                $this->load->view('admin/staff/staffcreate', $data);
                $this->load->view('layout/footer', $data);
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffcreate', $data);
        $this->load->view('layout/footer', $data);
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staff');
        $data['title'] = 'Edit Staff';
        $data['id'] = $id;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $data['can_edit_leave_section'] = $this->canEditLeavesSection();

        $data['getStaffRole'] = $this->role_model->get();
        $data['designation'] = $this->staff_model->getStaffDesignation();
        $data['department'] = $this->staff_model->getDepartment();
        
        // Get staff categories for dropdown
        $this->db->select('id, name, color, icon');
        $categories = $this->db->get('staff_designation_category')->result_array();
        $data['categories'] = $categories;
        
        $genderList = $this->customlib->getGender();
        $data['genderList'] = $genderList;
        $payscaleList = $this->staff_model->getPayroll();
        $leavetypeList = $this->staff_model->getLeaveType();
        $data["leavetypeList"] = $leavetypeList;
        $data["payscaleList"] = $payscaleList;
        $marital_status = $this->marital_status;
        $data["marital_status"] = $marital_status;
        $data["contract_type"] = $this->contract_type;
        $staff = $this->staff_model->get($id);
        $data['staff'] = $staff;

        $staff_leaves          = $this->leaverequest_model->staff_leave_request($id);
        $alloted_leavetype           = $this->staff_model->allotedLeaveType($id);
        $i                           = 0;
        $leaveDetail                 = array();
        foreach ($alloted_leavetype as $key => $value) {
            $count_leaves[]                   = $this->leaverequest_model->countLeavesData($id, $value["leave_type_id"]);
            $leaveDetail[$i]['type']          = $value["type"];
            $leaveDetail[$i]['id']            = $value["leave_type_id"];
            $leaveDetail[$i]['altid']         = $value["id"];
            $leaveDetail[$i]['alloted_leave'] = $value["alloted_leave"];
            $leaveDetail[$i]['approve_leave'] = $count_leaves[$i]['approve_leave'];
            $i++;
        }
        $data['staffLeaveDetails'] = (array) $leaveDetail;

        $custom_fields = $this->customfield_model->getByBelong('staff');
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation']) {
                $custom_fields_id = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[staff][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('biometric_id', $this->lang->line('biometric_id'), 'trim|xss_clean|callback__check_biometric_id_exists');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        $this->form_validation->set_rules('first_doc', $this->lang->line('image'), 'callback_handle_first_upload');
        $this->form_validation->set_rules('second_doc', $this->lang->line('image'), 'callback_handle_second_upload');
        $this->form_validation->set_rules('third_doc', $this->lang->line('image'), 'callback_handle_third_upload');
        $this->form_validation->set_rules('fourth_doc', $this->lang->line('image'), 'callback_handle_fourth_upload');

        $this->form_validation->set_rules(
            'email', $this->lang->line('email'), array('required', 'valid_email',
                array('check_exists', array($this->staff_model, 'valid_email_id')),
            )
        );
        if (!$this->sch_setting_detail->staffid_auto_insert) {
            $this->form_validation->set_rules('employee_id', $this->lang->line('staff_id'), 'callback_username_check');
        }

        if ($this->form_validation->run() == true) {

            $custom_field_post = $this->input->post("custom_fields[staff]");
            $custom_value_array = array();
            if (!empty($custom_fields)) {
                foreach ($custom_field_post as $key => $value) {
                    $check_field_type = $this->input->post("custom_fields[staff][" . $key . "]");
                    $field_value = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                    $array_custom = array(
                        'belong_table_id' => 0,
                        'custom_field_id' => $key,
                        'field_value' => $field_value,
                    );
                    $custom_value_array[] = $array_custom;
                }
            }

            $employee_id = $this->input->post("employee_id");
            $biometric_id = $this->input->post("biometric_id");
            $department = empty2null($this->input->post("department"));
            $designation = empty2null($this->input->post("designation"));
            $role = $this->input->post("role");
            $name = $this->input->post("name");
            $gender = $this->input->post("gender");
            $marital_status = $this->input->post("marital_status");
            $dob = $this->input->post("dob");
            $contact_no = $this->input->post("contactno");
            $emergency_no = $this->input->post("emergency_no");
            $email = $this->input->post("email");
            $date_of_joining = $this->input->post("date_of_joining");
            $date_of_leaving = $this->input->post("date_of_leaving");
            $address = $this->input->post("address");
            $qualification = $this->input->post("qualification");
            $work_experience = $this->input->post("work_experience");
            $basic_salary = $this->input ->post('basic_salary');
            $account_title = $this->input->post("account_title");
            $bank_account_no = $this->input->post("bank_account_no");
            $bank_name = $this->input->post("bank_name");
            $ifsc_code = $this->input->post("ifsc_code");
            $bank_branch = $this->input->post("bank_branch");
            $contract_type = $this->input->post("contract_type");
            $shift = $this->input->post("shift");
            $location = $this->input->post("location");
            $facebook = $this->input->post("facebook");
            $twitter = $this->input->post("twitter");
            $linkedin = $this->input->post("linkedin");
            $instagram = $this->input->post("instagram");
            $permanent_address = $this->input->post("permanent_address");
            $father_name = $this->input->post("father_name");
            $surname = $this->input->post("surname");
            $mother_name = $this->input->post("mother_name");
            $pan_no = $this->input->post("pan_no");
            $previous_institution = $this->input->post("previous_institution");
            $subject_expertise = $this->input->post("subject_expertise");
            $note = $this->input->post("note");
            $esi_no = $this->input->post("esi_no");
            $is_epf_enabled = $this->input->post("is_epf_enabled") ? 1 : 0;
            $is_esi_enabled = $this->input->post("is_esi_enabled") ? 1 : 0;
            $skip_payroll   = $this->input->post("skip_payroll") ? 1 : 0;
            $tds_pct_raw = $this->input->post("tds_percentage");
            $tds_percentage = ($tds_pct_raw !== '' && $tds_pct_raw !== false && $tds_pct_raw !== null && is_numeric($tds_pct_raw)) ? round((float)$tds_pct_raw, 2) : null;
            $opening_ytd_income_raw = $this->input->post("opening_ytd_income");
            $opening_ytd_income = ($opening_ytd_income_raw !== '' && $opening_ytd_income_raw !== false && $opening_ytd_income_raw !== null && is_numeric($opening_ytd_income_raw)) ? round((float)$opening_ytd_income_raw, 2) : null;
            $opening_ytd_tax_raw = $this->input->post("opening_ytd_tax_deducted");
            $opening_ytd_tax = ($opening_ytd_tax_raw !== '' && $opening_ytd_tax_raw !== false && $opening_ytd_tax_raw !== null && is_numeric($opening_ytd_tax_raw)) ? round((float)$opening_ytd_tax_raw, 2) : null;
            $opening_ytd_fy_start_year_raw = $this->input->post("opening_ytd_fy_start_year");
            $opening_ytd_fy_start_year = ($opening_ytd_fy_start_year_raw !== '' && $opening_ytd_fy_start_year_raw !== false && $opening_ytd_fy_start_year_raw !== null && is_numeric($opening_ytd_fy_start_year_raw)) ? (int)$opening_ytd_fy_start_year_raw : null;
            $has_opening_ytd = ((float)($opening_ytd_income ?? 0) > 0) || ((float)($opening_ytd_tax ?? 0) > 0);
            if ($has_opening_ytd && empty($opening_ytd_fy_start_year)) {
                $opening_ytd_fy_start_year = ((int)date('n') >= 4) ? (int)date('Y') : ((int)date('Y') - 1);
            }
            if (!$has_opening_ytd) {
                $opening_ytd_fy_start_year = null;
            }
            $payscale = $this->input->post("payscale");
            $aadhaar_no = $this->input->post("aadhaar_no");
            $religion = $this->input->post("religion");
            $caste = $this->input->post("caste");
            $blood_group = $this->input->post("blood_group");
            $country = $this->input->post("country");
            $state = $this->input->post("state");
            $pincode = $this->input->post("pincode");
            $previous_salary = $this->input->post("previous_salary");
            $uan_no = $this->input->post("uan_no");
            $pan_no = $this->input->post("pan_no");
            $previous_institution = $this->input->post("previous_institution");
            $subject_expertise = $this->input->post("subject_expertise");
            $category_id = empty2null($this->input->post("category_id"));
            $au_fin_no = $this->input->post("au_fin_no");
            $aicte_coa_id = $this->input->post("aicte_coa_id");
            
            $data_update = array(
                'id' => $id,
                'employee_id' => $employee_id,
                'biometric_id'           => ($biometric_id == "") ? NULL : $biometric_id,
                'au_fin_no'              => ($au_fin_no === '' || $au_fin_no === false) ? NULL : $au_fin_no,
                'aicte_coa_id'           => ($aicte_coa_id === '' || $aicte_coa_id === false) ? NULL : $aicte_coa_id,
                'name' => $name,
                'email' => $email,
                'dob' => date('Y-m-d', $this->customlib->datetostrtotime($dob)),
                'gender' => $gender,
                'is_active' => $this->input->post('is_active'),
                'prefix' => $this->input->post('prefix'),
                'ug_qualification' => $this->input->post('ug_qualification'),
                'pg_qualification' => $this->input->post('pg_qualification'),
                'higher_qualification' => $this->input->post('higher_qualification'),
                'qualified_exam' => $this->input->post('qualified_exam'),
                'subject_specialization' => $this->input->post('subject_specialization'),
                'additional_qualification' => $this->input->post('additional_qualification'),
                'payscale' => $payscale,
                'aadhaar_no' => $aadhaar_no,
                'religion' => $religion,
                'caste' => $caste,
                'blood_group' => $blood_group,
                'country' => $country,
                'state' => $state,
                'pincode' => $pincode,
                'previous_salary' => $previous_salary,
                'uan_no' => $uan_no,
                'pan_no' => $pan_no,
                'previous_institution' => $previous_institution,
                'subject_expertise' => $subject_expertise,
                'is_epf_enabled' => $is_epf_enabled,
                'is_esi_enabled' => $is_esi_enabled,
                'skip_payroll' => $skip_payroll,
                'category_id' => $category_id,
                'tds_percentage' => $tds_percentage,
                'opening_ytd_income' => $opening_ytd_income,
                'opening_ytd_tax_deducted' => $opening_ytd_tax,
                'opening_ytd_fy_start_year' => $opening_ytd_fy_start_year,
            );

            if (isset($surname)) {
                $data_update['surname'] = $surname;
            }

            if (isset($department)) {
                $data_update['department'] = $department;
            }

            if (isset($designation)) {
                $data_update['designation'] = $designation;
            }

            if (isset($mother_name)) {
                $data_update['mother_name'] = $mother_name;
            }

            if (isset($father_name)) {
                $data_update['father_name'] = $father_name;
            }

            if (isset($contact_no)) {
                $data_update['contact_no'] = $contact_no;
            }

            if (isset($emergency_no)) {
                $data_update['emergency_contact_no'] = $emergency_no;
            }

            if (isset($marital_status)) {
                $data_update['marital_status'] = $marital_status;
            }

            if (isset($address)) {
                $data_update['local_address'] = $address;
            }

            if (isset($permanent_address)) {
                $data_update['permanent_address'] = $permanent_address;
            }

            if (isset($qualification)) {
                $data_update['qualification'] = $qualification;
            }

            if (isset($work_experience)) {
                $data_update['work_exp'] = $work_experience;
            }

            if (isset($note)) {
                $data_update['note'] = $note;
            }

            if (isset($esi_no)) {
                $data_update['esi_no'] = $esi_no;
            }

            if (isset($basic_salary)) {
                $data_update['basic_salary'] = $basic_salary;
            }

            if (isset($contract_type)) {
                $data_update['contract_type'] = $contract_type;
            }

            if (isset($shift)) {
                $data_update['shift'] = $shift;
            }

            if (isset($location)) {
                $data_update['location'] = $location;
            }

            if (isset($bank_account_no)) {
                $data_update['bank_account_no'] = $bank_account_no;
            }

            if (isset($bank_name)) {
                $data_update['bank_name'] = $bank_name;
            }

            if (isset($account_title)) {
                $data_update['account_title'] = $account_title;
            }

            if (isset($ifsc_code)) {
                $data_update['ifsc_code'] = $ifsc_code;
            }

            if (isset($bank_branch)) {
                $data_update['bank_branch'] = $bank_branch;
            }

            if (isset($facebook)) {
                $data_update['facebook'] = $facebook;
            }

            if (isset($twitter)) {
                $data_update['twitter'] = $twitter;
            }

            if (isset($linkedin)) {
                $data_update['linkedin'] = $linkedin;
            }

            if (isset($instagram)) {
                $data_update['instagram'] = $instagram;
            }

            if ($date_of_joining != "") {
                $data_update['date_of_joining'] = $this->customlib->dateFormatToYYYYMMDD($date_of_joining);
            }

            if ($date_of_leaving != "") {
                $data_update['date_of_leaving'] = $this->customlib->dateFormatToYYYYMMDD($date_of_leaving);
            } else {
                $data_update['date_of_leaving'] = null;
            }

            // Leave balances must not be edited from staff profile edit screen.
            // Use admin/update_leave_balance for controlled updates.
            $leave_array = array();
            $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => $id);

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $upload_result = $this->media_storage->fileupload("file", "./uploads/staff_images/");
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/edit/' . $id);
                }
                $img_name = $upload_result['message'];
                $data_update['image'] = $img_name;
            }

            $this->staff_model->batchInsert($data_update, $role_array, $leave_array);
            if (!empty($custom_value_array)) {
                $this->customfield_model->updateRecord($custom_value_array, $id);
            }

            $upload_dir = './uploads/staff_documents/' . $id . '/';
            $this->customlib->ensureDirectoryExists($upload_dir);

            if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("first_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/edit/' . $id);
                }
                $resume = $upload_result['message'];
                $data_doc['resume'] = $resume;
            }

            if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("second_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/edit/' . $id);
                }
                $joining_letter = $upload_result['message'];
                $data_doc['joining_letter'] = $joining_letter;
            }

            if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("third_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/edit/' . $id);
                }
                $resignation_letter = $upload_result['message'];
                $data_doc['resignation_letter'] = $resignation_letter;
            }

            if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("fourth_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/edit/' . $id);
                }
                $fourth_doc = $upload_result['message'];
                $data_doc['other_document_file'] = $fourth_doc;
                $data_doc['other_document_name'] = $this->input->post('fourth_title');
            }

            if (isset($data_doc)) {
                $data_doc['id'] = $id;
                $this->staff_model->add($data_doc);
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/staff/profile/' . $id);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffedit', $data);
        $this->load->view('layout/footer', $data);
    }

    public function selfedit()
    {
        $id = $this->customlib->getStaffID(); // Get the ID of the logged-in staff member
        // Removed: if (!$this->rbac->hasPrivilege('staff', 'can_edit')) { access_denied(); }
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staff');
        $data['title'] = $this->lang->line('edit_staff'); // Changed title
        $data['id'] = $id;
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;

        $data['getStaffRole'] = $this->role_model->get();
        $data['designation'] = $this->staff_model->getStaffDesignation();
        $data['department'] = $this->staff_model->getDepartment();
        $genderList = $this->customlib->getGender();
        $data['genderList'] = $genderList;
        $payscaleList = $this->staff_model->getPayroll();
        $leavetypeList = $this->staff_model->getLeaveType();
        $data["leavetypeList"] = $leavetypeList;
        $data["payscaleList"] = $payscaleList;
        $marital_status = $this->marital_status;
        $data["marital_status"] = $marital_status;
            $data["contract_type"] = $this->contract_type;
            $staff = $this->staff_model->get($id);
            $data['staff'] = $staff;
        
            $staff_leaves          = $this->leaverequest_model->staff_leave_request($id);
            $alloted_leavetype           = $this->staff_model->allotedLeaveType($id);
            $i                           = 0;
            $leaveDetail                 = array();
            foreach ($alloted_leavetype as $key => $value) {
                $count_leaves[]                   = $this->leaverequest_model->countLeavesData($id, $value["leave_type_id"]);
                $leaveDetail[$i]['type']          = $value["type"];
                $leaveDetail[$i]['id']            = $value["leave_type_id"];
                $leaveDetail[$i]['altid']         = $value["id"];
                $leaveDetail[$i]['alloted_leave'] = $value["alloted_leave"];
                $leaveDetail[$i]['approve_leave'] = $count_leaves[$i]['approve_leave'];
                $i++;
            }        $data['staffLeaveDetails'] = (array) $leaveDetail;

        $custom_fields = $this->customfield_model->getByBelong('staff');
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation']) {
                $custom_fields_id = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[staff][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean'); // Role should not be editable by self
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        $this->form_validation->set_rules('first_doc', $this->lang->line('image'), 'callback_handle_first_upload');
        $this->form_validation->set_rules('second_doc', $this->lang->line('image'), 'callback_handle_second_upload');
        $this->form_validation->set_rules('third_doc', $this->lang->line('image'), 'callback_handle_third_upload');
        $this->form_validation->set_rules('fourth_doc', $this->lang->line('image'), 'callback_handle_fourth_upload');

        $this->form_validation->set_rules(
            'email', $this->lang->line('email'), array('required', 'valid_email',
                array('check_exists', array($this->staff_model, 'valid_email_id')),
            )
        );
        // Employee ID should not be editable by self
        // if (!$this->sch_setting_detail->staffid_auto_insert) {
        //     $this->form_validation->set_rules('employee_id', $this->lang->line('staff_id'), 'callback_username_check');
        // }

        if ($this->form_validation->run() == true) {

            $custom_field_post = $this->input->post("custom_fields[staff]");
            $custom_value_array = array();
            if (!empty($custom_fields)) {
                foreach ($custom_field_post as $key => $value) {
                    $check_field_type = $this->input->post("custom_fields[staff][" . $key . "]");
                    $field_value = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                    $array_custom = array(
                        'belong_table_id' => 0,
                        'custom_field_id' => $key,
                        'field_value' => $field_value,
                    );
                    $custom_value_array[] = $array_custom;
                }
            }

            $employee_id = $this->input->post("employee_id");
            $biometric_id = $this->input->post("biometric_id");
            $department = empty2null($this->input->post("department"));
            $designation = empty2null($this->input->post("designation"));
            $role = $this->input->post("role");
            $name = $this->input->post("name");
            $gender = $this->input->post("gender");
            $marital_status = $this->input->post("marital_status");
            $dob = $this->input->post("dob");
            $contact_no = $this->input->post("contactno");
            $emergency_no = $this->input->post("emergency_no");
            $email = $this->input->post("email");
            $date_of_joining = $this->input->post("date_of_joining");
            $date_of_leaving = $this->input->post("date_of_leaving");
            $address = $this->input->post("address");
            $qualification = $this->input->post("qualification");
            $work_experience = $this->input->post("work_experience");
            $basic_salary = $this->input ->post('basic_salary');
            $account_title = $this->input->post("account_title");
            $bank_account_no = $this->input->post("bank_account_no");
            $bank_name = $this->input->post("bank_name");
            $ifsc_code = $this->input->post("ifsc_code");
            $bank_branch = $this->input->post("bank_branch");
            $contract_type = $this->input->post("contract_type");
            $shift = $this->input->post("shift");
            $location = $this->input->post("location");
            $facebook = $this->input->post("facebook");
            $twitter = $this->input->post("twitter");
            $linkedin = $this->input->post("linkedin");
            $instagram = $this->input->post("instagram");
            $permanent_address = $this->input->post("permanent_address");
            $father_name = $this->input->post("father_name");
            $surname = $this->input->post("surname");
            $mother_name = $this->input->post("mother_name");
            $note = $this->input->post("note");
            $esi_no = $this->input->post("esi_no");
            $is_epf_enabled = $this->input->post("is_epf_enabled") ? 1 : 0;
            $is_esi_enabled = $this->input->post("is_esi_enabled") ? 1 : 0;

            $data_update = array(
                'id' => $id,
                // 'employee_id' => $employee_id, // Self-edit should not change employee ID
                'biometric_id'           => $biometric_id, // If staff can edit biometric ID
                'name' => $name,
                'email' => $email,
                'dob' => date('Y-m-d', $this->customlib->datetostrtotime($dob)),
                'gender' => $gender,
                // 'is_active' => $this->input->post('is_active'), // Self-edit should not change active status
                'prefix' => $this->input->post('prefix'),
                'ug_qualification' => $this->input->post('ug_qualification'),
                'pg_qualification' => $this->input->post('pg_qualification'),
                'higher_qualification' => $this->input->post('higher_qualification'),
                'qualified_exam' => $this->input->post('qualified_exam'),
                'subject_specialization' => $this->input->post('subject_specialization'),
                'additional_qualification' => $this->input->post('additional_qualification'),
            );

            if (isset($surname)) {
                $data_update['surname'] = $surname;
            }

            if (isset($department)) {
                $data_update['department'] = $department;
            }

            if (isset($designation)) {
                $data_update['designation'] = $designation;
            }

            if (isset($mother_name)) {
                $data_update['mother_name'] = $mother_name;
            }

            if (isset($father_name)) {
                $data_update['father_name'] = $father_name;
            }

            if (isset($contact_no)) {
                $data_update['contact_no'] = $contact_no;
            }

            if (isset($emergency_no)) {
                $data_update['emergency_contact_no'] = $emergency_no;
            }

            if (isset($marital_status)) {
                $data_update['marital_status'] = $marital_status;
            }

            if (isset($address)) {
                $data_update['local_address'] = $address;
            }

            if (isset($permanent_address)) {
                $data_update['permanent_address'] = $permanent_address;
            }

            if (isset($qualification)) {
                $data_update['qualification'] = $qualification;
            }

            if (isset($work_experience)) {
                $data_update['work_exp'] = $work_experience;
            }

            if (isset($note)) {
                $data_update['note'] = $note;
            }

            if (isset($esi_no)) {
                $data_update['esi_no'] = $esi_no;
            }

            $data_update['is_epf_enabled'] = $is_epf_enabled;
            $data_update['is_esi_enabled'] = $is_esi_enabled;

            if (isset($basic_salary)) {
                $data_update['basic_salary'] = $basic_salary;
            }
            // $leave_type = $this->input->post('leave_type');
            // $leave_array = array();
            // if (!empty($leave_type)) {
            //     foreach ($leave_type as $leave_key => $leave_value) {
            //         $leave_array[] = array(
            //             'staff_id' => $id,
            //             'leave_type_id' => $leave_value,
            //             'alloted_leave' => $this->input->post('alloted_leave_' . $leave_value),
            //         );
            //     }
            // }
            // $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => $id);

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $upload_result = $this->media_storage->fileupload("file", "./uploads/staff_images/");
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/selfedit'); // Redirect to selfedit on error
                }
                $img_name = $upload_result['message'];
                $data_update['image'] = $img_name;
            }

            // Call staff_model->add for update, passing only data_update since role_array and leave_array are not handled in self-edit
            $this->staff_model->add($data_update);
            if (!empty($custom_value_array)) {
                $this->customfield_model->updateRecord($custom_value_array, $id);
            }

            $upload_dir = './uploads/staff_documents/' . $id . '/';
            $this->customlib->ensureDirectoryExists($upload_dir);

            if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("first_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/selfedit'); // Redirect to selfedit on error
                }
                $resume = $upload_result['message'];
                $data_doc['resume'] = $resume;
            }

            if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("second_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/selfedit'); // Redirect to selfedit on error
                }
                $joining_letter = $upload_result['message'];
                $data_doc['joining_letter'] = $joining_letter;
            }

            if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("third_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/selfedit'); // Redirect to selfedit on error
                }
                $resignation_letter = $upload_result['message'];
                $data_doc['resignation_letter'] = $resignation_letter;
            }

            if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {
                $upload_result = $this->media_storage->fileupload("fourth_doc", $upload_dir);
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/staff/selfedit'); // Redirect to selfedit on error
                }
                $fourth_doc = $upload_result['message'];
                $data_doc['other_document_file'] = $fourth_doc;
                $data_doc['other_document_name'] = $this->input->post('fourth_title');
            }

            if (isset($data_doc)) {
                $data_doc['id'] = $id;
                $this->staff_model->add($data_doc);
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/staff/profile/' . $id);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffselfedit', $data); // Load staffselfedit view
        $this->load->view('layout/footer', $data);
    }




    public function _check_biometric_id_exists($biometric_id)
    {
        $id = $this->input->post('editid');
        if ($this->staff_model->check_biometric_id_exists($biometric_id, $id)) {
            $this->form_validation->set_message('_check_biometric_id_exists', 'The Biometric ID is already in use by another staff member.');
            return false;
        }
        return true;
    }

    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png');
            $temp = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function handle_first_upload()
    {
        if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $temp = explode(".", $_FILES["first_doc"]["name"]);
            $extension = end($temp);
            if ($_FILES["first_doc"]["error"] > 0) {
                $this->form_validation->set_message('handle_first_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_first_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function handle_second_upload()
    {
        if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $temp = explode(".", $_FILES["second_doc"]["name"]);
            $extension = end($temp);
            if ($_FILES["second_doc"]["error"] > 0) {
                $this->form_validation->set_message('handle_second_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_second_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function handle_third_upload()
    {
        if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $temp = explode(".", $_FILES["third_doc"]["name"]);
            $extension = end($temp);
            if ($_FILES["third_doc"]["error"] > 0) {
                $this->form_validation->set_message('handle_third_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_third_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function handle_fourth_upload()
    {
        if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $temp = explode(".", $_FILES["fourth_doc"]["name"]);
            $extension = end($temp);
            if ($_FILES["fourth_doc"]["error"] > 0) {
                $this->form_validation->set_message('handle_fourth_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_fourth_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            return true;
        } else {
            return true;
        }
    }

    public function import()
    {
        $data['field'] = array(
            "biometric_id"             => "biometric_id",
            "employee_id"              => "employee_id",
            "prefix"                   => "prefix",
            "ug_qualification"         => "ug_qualification",
            "pg_qualification"         => "pg_qualification",
            "higher_qualification"     => "higher_qualification",
            "qualified_exam"           => "qualified_exam",
            "subject_specialization"   => "subject_specialization",
            "additional_qualification" => "additional_qualification",
            "qualification"            => "qualification",
            "work_exp"                 => "work_exp",
            "name"                     => "name",
            "surname"                  => "surname",
            "father_name"              => "father_name",
            "mother_name"              => "mother_name",
            "contact_no"               => "contact_no",
            "emergency_contact_no"     => "emergency_contact_no",
            "email"                    => "email",
            "dob"                      => "dob",
            "marital_status"           => "marital_status",
            "date_of_joining"          => "date_of_joining",
            "date_of_leaving"          => "date_of_leaving",
            "local_address"            => "local_address",
            "permanent_address"        => "permanent_address",
            "note"                     => "note",
            "gender"                   => "gender",
            "account_title"            => "account_title",
            "bank_account_no"          => "bank_account_no",
            "bank_name"                => "bank_name",
            "ifsc_code"                => "ifsc_code",
            "bank_branch"              => "bank_branch",
            "payscale"                 => "payscale",
            "basic_salary"             => "basic_salary",
            "esi_no"                   => "esi_no",
            "is_epf_enabled"           => "is_epf_enabled",
            "is_esi_enabled"           => "is_esi_enabled",
            "contract_type"            => "contract_type",
            "shift"                    => "shift",
            "location"                 => "location",
            "facebook"                 => "facebook",
            "twitter"                  => "twitter",
            "linkedin"                 => "linkedin",
            "instagram"                => "instagram",
            "resume"                   => "resume",
            "joining_letter"           => "joining_letter",
            "resignation_letter"       => "resignation_letter",
            "designation"              => "designation",
            "department"               => "department",
            "category_id"              => "category_id",
            "aadhaar_no" => "aadhaar_no",
            "religion" => "religion",
            "caste" => "caste",
            "blood_group" => "blood_group",
            "country" => "country",
            "state" => "state",
            "pincode" => "pincode",
            "previous_salary" => "previous_salary",
            "uan_no" => "uan_no",
            "pan_no" => "pan_no",
            "previous_institution" => "previous_institution",
            "subject_expertise" => "subject_expertise",
        );

        $roles               = $this->role_model->get();
        $data["roles"]       = $roles;
        // Build role name → id map for per-row CSV role resolution
        $role_map = [];
        foreach ($roles as $r_item) {
            $role_map[strtolower(trim($r_item['name']))] = $r_item['id'];
        }
        $all_designations    = $this->staff_model->getStaffDesignation();
        $designation_map     = [];
        foreach ($all_designations as $designation_item) {
            $designation_map[strtolower($designation_item['designation'])] = $designation_item['id'];
        }
        $all_departments     = $this->staff_model->getDepartment();
        $department_map      = [];
        foreach ($all_departments as $department_item) {
            $department_map[strtolower($department_item['department_name'])] = $department_item['id'];
        }
        // Get all staff designation categories
        $all_categories      = $this->db->select('id, name')->from('staff_designation_category')->get()->result_array();
        $category_map        = [];
        foreach ($all_categories as $category_item) {
            $category_map[strtolower($category_item['name'])] = $category_item['id'];
        }
        $data["designation"] = $all_designations;
        $data["department"]  = $all_departments;

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_csv_upload');
        // role is now optional — each CSV row can specify its own role; dropdown is fallback default
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'permit_empty');

        if ($this->form_validation->run() == false) {
            $this->load->view("layout/header", $data);
            $this->load->view("admin/staff/import/import", $data);
            $this->load->view("layout/footer", $data);
        } else {

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {

                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);

                    $rowcount = 0;
                    $inserted_count = 0;
                    $updated_count = 0;
                    $skipped_count = 0;

                    if (!empty($result)) {
                        foreach ($result as $r_key => $r_value) {


                            $staff_data = [];
                            foreach ($data['field'] as $csv_header => $db_field) {
                                $staff_data[$db_field] = isset($r_value[$csv_header]) ? $this->encoding_lib->toUTF8($r_value[$csv_header]) : '';
                            }

                            // Date parsing for dob and date_of_joining
                            if (!empty($staff_data['dob'])) {
                                $parsed_date = strtotime($staff_data['dob']);
                                $staff_data['dob'] = ($parsed_date !== false) ? date('Y-m-d', $parsed_date) : null;
                            } else {
                                $staff_data['dob'] = null;
                            }

                            if (!empty($staff_data['date_of_joining'])) {
                                $parsed_date = strtotime($staff_data['date_of_joining']);
                                $staff_data['date_of_joining'] = ($parsed_date !== false) ? date('Y-m-d', $parsed_date) : null;
                            } else {
                                $staff_data['date_of_joining'] = null;
                            }

                            // Handle designation mapping
                            $csv_designation_name = strtolower(trim($staff_data['designation']));
                            if (isset($designation_map[$csv_designation_name])) {
                                $staff_data['designation'] = $designation_map[$csv_designation_name];
                            } else {
                                if (!empty($csv_designation_name)) {
                                    $designation_id = $this->staff_model->add_designation(array('designation' => $csv_designation_name, 'is_active' => 'yes'));
                                    $staff_data['designation'] = $designation_id;
                                    $designation_map[$csv_designation_name] = $designation_id;
                                } else {
                                    $staff_data['designation'] = null;
                                }
                            }

                            // Handle category mapping
                            if (!empty($staff_data['category_id'])) {
                                $csv_category_input = trim($staff_data['category_id']);
                                $category_id = null;
                                
                                // Check if input is numeric (direct category_id)
                                if (is_numeric($csv_category_input)) {
                                    // Verify category exists
                                    $category_exists = $this->db->select('id')->from('staff_designation_category')
                                        ->where('id', (int)$csv_category_input)->where('is_active', 'yes')->get()->row();
                                    if ($category_exists) {
                                        $category_id = (int)$csv_category_input;
                                    }
                                } else {
                                    // Try to match by category name
                                    $csv_category_name = strtolower($csv_category_input);
                                    if (isset($category_map[$csv_category_name])) {
                                        $category_id = $category_map[$csv_category_name];
                                    }
                                }
                                
                                $staff_data['category_id'] = $category_id;
                            } else {
                                $staff_data['category_id'] = null;
                            }

                            // Handle department mapping
                            $csv_department_name = strtolower(trim($staff_data['department']));
                            if (isset($department_map[$csv_department_name])) {
                                $staff_data['department'] = $department_map[$csv_department_name];
                            } else {
                                $staff_data['department'] = null;
                            }

                            // Handle gender and marital status mapping
                            $staff_data['gender'] = ucfirst(strtolower($staff_data['gender']));
                            $staff_data['marital_status'] = ucfirst(strtolower($staff_data['marital_status']));
                            $staff_data['contract_type'] = ucfirst(strtolower($staff_data['contract_type']));

                            $staff_data['is_active'] = 1;

                            $existing_staff = $this->staff_model->getStaffIdByEmployeeIdOrEmail($staff_data['employee_id'], $staff_data['email']);

                            if ($existing_staff) {
                                log_message('debug', 'Skipping existing record for Employee ID: ' . $staff_data['employee_id'] . ', Email: ' . $staff_data['email']);
                                $skipped_count++;
                                continue;
                            } else {
                                $password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
                                $staff_data['password'] = $this->enc_lib->passHashEnc($password);
                                // Resolve role: prefer CSV column 'role', fall back to UI dropdown
                                $csv_role_raw   = strtolower(trim(isset($r_value['role']) ? $r_value['role'] : ''));
                                $resolved_role  = !empty($csv_role_raw) && isset($role_map[$csv_role_raw])
                                    ? $role_map[$csv_role_raw]
                                    : ($this->input->post('role') ?: 2); // 2 = Teacher as last-resort default
                                $role_array = array('role_id' => $resolved_role, 'staff_id' => 0);
                                $insert_id = $this->staff_model->batchInsert($staff_data, $role_array);
                                $staff_id  = $insert_id;
                                $inserted_count++; // Keep track of inserted records
                                log_message('debug', 'Inserting new record for Employee ID: ' . $staff_data['employee_id'] . ', Email: ' . $staff_data['email']);

                                if ($staff_id) {
                                    //***** generate barcode and qrcode of staff ******//
                                    $scan_type= $this->sch_setting_detail->scan_code_type;
                                    $this->customlib->generatestaffbarcode($staff_data['employee_id'],$staff_id,$scan_type);
                                    //***** generate barcode and qrcode of staff ******//
                                }

                                if ($staff_id) { // Only send login credential for new inserts
                                    $teacher_login_detail = array('id' => $staff_id, 'credential_for' => 'staff', 'first_name' => $this->input->post("name"), 'last_name' => $this->input->post("surname"), 'username' => $staff_data['email'], 'password' => $password, 'contact_no' => $staff_data['contact_no'], 'email' => $staff_data['email']);
                                    $this->mailsmsconf->mailsms('login_credential', $teacher_login_detail);
                                }
                            }
                        } ///Result loop
                    } //Not emprty l

                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('records_found_in_CSV_file_total') . count($result) . $this->lang->line('records_imported_successfully')); // CSVReader already excludes header
                }
            } else {
                $msg = array(
                    'e' => $this->lang->line('the_file_field_is_required'),
                );
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }

            $total_in_csv = count($result); // CSVReader already excludes header row
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Records Found In CSV File Total: ' . $total_in_csv . ' | Records Inserted Successfully: ' . $inserted_count . ' | Skipped: ' . $skipped_count . '</div>');
            redirect('admin/staff/import');
        }
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt');
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/staff_csvfile.csv";
        $data     = file_get_contents($filepath);
        $name     = 'staff_csvfile.csv';
        force_download($name, $data);
    }

    public function rating()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/rating');
        $this->load->view('layout/header');
        $staff_list         = $this->staff_model->getrat();
        $data['resultlist'] = $staff_list;
        $this->load->view('admin/staff/rating', $data);
        $this->load->view('layout/footer');
    }

    public function ratingapr($id)
    {
        $approve['status'] = '1';
        $this->staff_model->ratingapr($id, $approve);
        redirect('admin/staff/rating');
    }

    public function delete_rateing($id)
    {
        $this->staff_model->rating_remove($id);
        redirect('admin/staff/rating');
    }

    public function managebiometricdevice($id = null) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/staff/managebiometricdevice');
        $data['title'] = $this->lang->line('biometric_device_management');
        $data['device_list'] = $this->biometric_device_model->get();
        $data['device_brands'] = [
            'eSSL',
            'ZKTeco',
            'Matrix',
            'Realtime',
            'CP Plus',
            'Hikvision',
            'BioEnable',
            'Suprema',
            'Anviz',
        ];

        if ($id) {
            $data['device_record'] = $this->biometric_device_model->get($id);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/managebiometricdevice', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add_biometric_device() {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_add'))) {
            access_denied();
        }

        $this->form_validation->set_rules('device_name', $this->lang->line('device_name'), 'required');
        $this->form_validation->set_rules('brand', $this->lang->line('brand'), 'required');
        $this->form_validation->set_rules('serial_number', $this->lang->line('serial_number'), 'required');
        $this->form_validation->set_rules('api_endpoint', $this->lang->line('api_endpoint'), 'required');
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->managebiometricdevice();
        } else {
            $data = array(
                'device_name' => $this->input->post('device_name'),
                'brand' => $this->input->post('brand'),
                'serial_number' => $this->input->post('serial_number'),
                'api_endpoint' => $this->input->post('api_endpoint'),
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            );

            if ($data['is_active']) {
                $this->biometric_device_model->deactivateAllDevices();
            }

            $this->biometric_device_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('device_added_successfully') . '</div>');
            redirect('admin/staff/managebiometricdevice');
        }
    }

    public function edit_biometric_device($id) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_edit'))) {
            access_denied();
        }

        $this->form_validation->set_rules('device_name', $this->lang->line('device_name'), 'required');
        $this->form_validation->set_rules('brand', $this->lang->line('brand'), 'required');
        $this->form_validation->set_rules('serial_number', $this->lang->line('serial_number'), 'required');
        $this->form_validation->set_rules('api_endpoint', $this->lang->line('api_endpoint'), 'required');
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->managebiometricdevice($id);
        } else {
            $data = array(
                'device_name' => $this->input->post('device_name'),
                'brand' => $this->input->post('brand'),
                'serial_number' => $this->input->post('serial_number'),
                'api_endpoint' => $this->input->post('api_endpoint'),
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            );

            if ($data['is_active']) {
                $this->biometric_device_model->deactivateAllDevices();
            }

            $this->biometric_device_model->update($id, $data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('device_updated_successfully') . '</div>');
            redirect('admin/staff/managebiometricdevice');
        }
    }

    public function delete_biometric_device($id) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_delete'))) {
            access_denied();
        }
        $this->biometric_device_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('device_deleted_successfully') . '</div>');
        redirect('admin/staff/managebiometricdevice');
    }

    public function activate_biometric_device($id) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_edit'))) {
            access_denied();
        }
        $this->biometric_device_model->deactivateAllDevices();
        $this->biometric_device_model->update($id, ['is_active' => 1]);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('device_activated_successfully') . '</div>');
        redirect('admin/staff/managebiometricdevice');
    }

        public function sync_biometric_attendance() {

            $is_cli = $this->input->is_cli_request();
            if (!$is_cli && !($this->rbac->hasPrivilege('biometric_device', 'can_view'))) {

                access_denied();

            }

    

            $this->load->model('biometric_api_model');

            $this->load->model('attendance_model');

    

            $active_device = $this->attendance_model->get_active_biometric_device();

    

            if (!$active_device) {

                $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line('no_active_biometric_device') . '</div>');

                log_message('error', 'Staff::sync_biometric_attendance - No active biometric device. Redirecting to staffattendance/index.');

                redirect('admin/staffattendance/index');

            }

    

            $last_sync_datetime = $this->attendance_model->get_last_biometric_sync_datetime();

            log_message('debug', 'Staff::sync_biometric_attendance - Raw $last_sync_datetime from model: ' . var_export($last_sync_datetime, true));
            
            $from_datetime = $last_sync_datetime ? $last_sync_datetime : date('Y-m-d H:i:s', strtotime('-30 days')); // Default to 30 days ago if no previous sync
            
            log_message('debug', 'Staff::sync_biometric_attendance - Determined $from_datetime: ' . $from_datetime);

            $to_datetime = date('Y-m-d H:i:s');

    

            $punches = $this->biometric_api_model->get_punches_from_api($active_device, $from_datetime, $to_datetime);

            log_message('debug', 'Punches received from API: ' . (is_array($punches) ? count($punches) : 'No punches received or API error.'));

    

            if ($punches !== false) { // Check for false to indicate API error

                $inserted_count = $this->attendance_model->save_raw_biometric_punches($punches);

                log_message('debug', 'Inserted count from model: ' . $inserted_count);

                $this->attendance_model->update_last_biometric_sync_datetime($to_datetime);

                $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('biometric_sync_success') . ' ' . $inserted_count . ' ' . $this->lang->line('new_punches_recorded') . '</div>');

                log_message('info', 'Staff::sync_biometric_attendance - Sync successful. Redirecting to staffattendance/index.');

                redirect('admin/staffattendance/index');

            } else {

                $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line('biometric_sync_failed') . '</div>');

                redirect('admin/staffattendance/index');

            }

        }

    

        public function profile_completion_report()

        {

            if (!$this->rbac->hasPrivilege('profile_completion_report', 'can_view')) {

                access_denied();

            }

    

            $this->session->set_userdata('top_menu', 'Reports');

            $this->session->set_userdata('sub_menu', 'Reports/staff_profile_completion');

            $data['title'] = $this->lang->line('staff_profile_completion_report');

    

            $staff_list = $this->staff_model->getAll(null, 1); // Get all active staff

            $report_data = [];

    

            foreach ($staff_list as $staff) {

                $staff_profile_completion = $this->staff_model->calculateProfileCompletion($staff);

                $report_data[] = [

                    'id' => $staff['id'],

                    'employee_id' => $staff['employee_id'],

                    'name' => $staff['name'],

                    'surname' => $staff['surname'],

                    'email' => $staff['email'],

                    'completion_percentage' => $staff_profile_completion,

                ];

            }

    

            $data['report_data'] = $report_data;

    

            $this->load->view('layout/header', $data);

            $this->load->view('admin/staff/profile_completion_report', $data); // We will create this view next

            $this->load->view('layout/footer', $data);

        }

    
    public function getEmployeeByRole()
    {
        $role = $this->input->post('role');
        $data = $this->staff_model->getEmployeeByRoleID($role);
        echo json_encode($data);
    }

    public function username_check($employee_id)
    {
        $id = $this->input->post('editid'); // Correctly get ID from 'editid' hidden field

        // If in edit mode, exclude current staff member from uniqueness check
        if ($id) {
            $result = $this->staff_model->check_staffid_exists($employee_id, $id);
        } else {
            $result = $this->staff_model->check_staffid_exists($employee_id);
        }

        if ($result) {
            $this->form_validation->set_message('username_check', $this->lang->line('staff_id_already_exists'));
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * Derive the display attendance key from raw punch times.
     * Mirrors _process_staff_attendance_from_punches logic — read-only, never writes to DB.
     * Falls back to Admin role (ID 1) when $role_id is empty/null.
     *
     * Returns: 'P', 'FHL', 'FHP', 'SHL', 'SHP', 'HD', 'A', or null (no punch data).
     */
    private function _timeInRangeProfile($time, $from, $to)
    {
        if (empty($time) || empty($from) || empty($to)) return false;
        $base = date('Y-m-d');
        $t = strtotime($base . ' ' . $time);
        $f = strtotime($base . ' ' . $from);
        $e = strtotime($base . ' ' . $to);
        if ($t === false || $f === false || $e === false) return false;
        return ($t >= $f && $t <= $e);
    }

    private function _derive_att_key_from_punches($in_time, $out_time, $role_id, $role_settings, $settings)
    {
        if (empty($in_time)) return null;
        if (empty($role_id)) $role_id = 1; // Default to Admin role

        $morning_session_status  = 8; // FHA default
        $afternoon_session_status = 9; // SHA default
        $second_half_start = false;

        // --- Morning: find which schedule window in_time falls into ---
        $morning_type_id = null;
        if (!empty($role_settings[$role_id])) {
            foreach ($role_settings[$role_id] as $type_id => $window) {
                // Skip unconfigured 00:00:00–00:00:00 placeholder windows; !empty('00:00:00') is
                // TRUE in PHP so we must guard explicitly to avoid false BETWEEN matches.
                if ($window['from'] === '00:00:00' && $window['to'] === '00:00:00') continue;
                if (!empty($window['from']) && !empty($window['to'])
                    && strtotime($in_time) >= strtotime($window['from'])
                    && strtotime($in_time) <= strtotime($window['to'])) {
                    $morning_type_id = (int)$type_id;
                    break;
                }
            }
        }
        if ($morning_type_id !== null) {
            if ($morning_type_id === 4) {        // HD window = direct second-half arrival
                $morning_session_status  = 8;    // FHA
                $afternoon_session_status = 1;   // P
                $second_half_start = true;
            } elseif ($morning_type_id === 6) {  // SHL window = second-half late arrival
                $morning_session_status  = 8;    // FHA
                $afternoon_session_status = 6;   // SHL
                $second_half_start = true;
            } else {
                $morning_session_status = $morning_type_id;
            }
        } else {
            // Outside all windows: check early-arrival (before present window start)
            $present_window = isset($role_settings[$role_id][1]) ? $role_settings[$role_id][1] : null;
            if ($present_window && !empty($present_window['from'])
                && strtotime($in_time) < strtotime($present_window['from'])) {
                $morning_session_status = 1; // Early → Present
            } else {
                // Past SHL window end → both halves absent.
                // Guard against unconfigured 00:00:00–00:00:00 placeholder:
                // !empty('00:00:00') is TRUE in PHP, so explicitly skip it.
                $shl_window = isset($role_settings[$role_id][6]) ? $role_settings[$role_id][6] : null;
                $shl_configured = $shl_window
                    && !($shl_window['from'] === '00:00:00' && $shl_window['to'] === '00:00:00');
                if ($shl_configured && strtotime($in_time) > strtotime($shl_window['to'])) {
                    $morning_session_status  = 8;
                    $afternoon_session_status = 9;
                    $second_half_start = true;
                } else {
                    $morning_session_status = 8;
                }
            }
        }

        // --- Afternoon: classify departure time using cutoff boundaries only.
        // Schedule windows (P, FHL, FHP, HD, SHL) are ARRIVAL windows — never use them
        // to classify out_time, otherwise e.g. a 13:22 departure wrongly matches the
        // SHL arrival window (13:21-13:30) and produces an incorrect SHL result.
        if (!empty($out_time) && !$second_half_start) {
            $shp_window = isset($role_settings[$role_id][7]) ? $role_settings[$role_id][7] : null;

            // Step 1: SHP departure window (e.g. 15:15-16:15)
            $in_shp = $shp_window && !empty($shp_window['from']) && !empty($shp_window['to'])
                && strtotime($out_time) >= strtotime($shp_window['from'])
                && strtotime($out_time) <= strtotime($shp_window['to']);
            if ($in_shp) {
                $afternoon_session_status = 7; // SHP
            } else {
                // Step 2: second-half floor = earliest start of HD(4) / SHL(6) arrival windows.
                // Departing before this means the person never worked the afternoon.
                $second_half_floor_ts = null;
                foreach ([4, 6] as $_sh_type) {
                    if (!empty($role_settings[$role_id][$_sh_type]['from'])) {
                        $_ts = strtotime($role_settings[$role_id][$_sh_type]['from']);
                        if ($_ts !== false && ($second_half_floor_ts === null || $_ts < $second_half_floor_ts)) {
                            $second_half_floor_ts = $_ts;
                        }
                    }
                }
                // Fallback to settings field only if no HD/SHL windows exist
                if ($second_half_floor_ts === null && !empty($settings->morning_session_end_time)) {
                    $second_half_floor_ts = strtotime($settings->morning_session_end_time);
                }

                if ($second_half_floor_ts !== null && strtotime($out_time) < $second_half_floor_ts) {
                    $afternoon_session_status = 9; // SHA – departed before second half began
                } else {
                    // Step 3: full-day boundary = SHP window end, then evening_session_end_time.
                    // Departing at or after this = full afternoon present.
                    // Guard against unconfigured 00:00:00 placeholder (same PHP !empty() trap).
                    $present_cutoff = null;
                    $shp_configured = $shp_window
                        && !($shp_window['from'] === '00:00:00' && $shp_window['to'] === '00:00:00');
                    if ($shp_configured && !empty($shp_window['to'])) {
                        $present_cutoff = $shp_window['to'];
                    } elseif (!empty($settings->evening_session_end_time)) {
                        $present_cutoff = $settings->evening_session_end_time;
                    }
                    if ($present_cutoff && strtotime($out_time) >= strtotime($present_cutoff)) {
                        // Guard: if in_time is also past the cutoff, both punches are after-hours.
                        // Do not credit second-half presence for after-hours activity.
                        if (strtotime($in_time) > strtotime($present_cutoff)) {
                            $afternoon_session_status = 9; // after-hours, not second half present
                        } else {
                            $afternoon_session_status = 1; // P – full day
                        }
                    } else {
                        $afternoon_session_status = 9; // SHA – left early in afternoon
                    }
                }
            }
        }
        // No out_time → afternoon_session_status stays SHA (9)

        // --- Determine display key ---
        $morning_session_status  = (int)$morning_session_status;
        $afternoon_session_status = (int)$afternoon_session_status;
        $first_half_present  = in_array($morning_session_status,  [1, 2, 5], true); // P, FHL, FHP
        $second_half_present = in_array($afternoon_session_status, [1, 6, 7], true); // P, SHL, SHP

        if ($first_half_present && $second_half_present) {
            if ($morning_session_status === 2) return 'FHL';
            if ($morning_session_status === 5) return 'FHP';
            if ($afternoon_session_status === 6) return 'SHL';
            if ($afternoon_session_status === 7) return 'SHP';
            return 'P';
        } elseif ($first_half_present || $second_half_present) {
            return 'HD';
        } else {
            return 'A';
        }
    }

    /**
     * AJAX: Recascade all CPL/leave monthly balances for a staff member from a
     * given month forward.  Fixes stale opening_balance values caused by
     * out-of-order HOD credit approvals or missing intermediate month rows.
     *
     * POST: staff_id, from_year, from_month
     */
    public function ajax_recascade_leave_balances()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }

        $staff_id   = (int) $this->input->post('staff_id');
        $from_year  = (int) $this->input->post('from_year');
        $from_month = (int) $this->input->post('from_month');

        if (!$staff_id || !$from_year || !$from_month) {
            echo json_encode(['status' => 'fail', 'message' => 'Missing parameters']);
            return;
        }

        $this->load->model('Payroll_model');

        // Get all leave type rows for this staff in the given month.
        $rows = $this->db
            ->select('leave_type_id, closing_balance')
            ->where('staff_id', $staff_id)
            ->where('year', $from_year)
            ->where('month', $from_month)
            ->get('staff_monthly_leave_balance')
            ->result_array();

        if (empty($rows)) {
            echo json_encode(['status' => 'fail', 'message' => 'No balance rows found for the specified month']);
            return;
        }

        foreach ($rows as $row) {
            $this->payroll_model->cascadeClosingToNextMonth(
                $staff_id,
                (int) $row['leave_type_id'],
                $from_year,
                $from_month,
                (float) $row['closing_balance']
            );
        }

        echo json_encode(['status' => 'success', 'message' => 'Balances recascaded from ' . $from_year . '-' . str_pad($from_month, 2, '0', STR_PAD_LEFT)]);
    }
}
