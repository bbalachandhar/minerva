<?php

class Leaverequest extends Admin_Controller
{
    private $leaveTypeBalanceFlagColumnExists = null;

    public function __construct()
    {
        parent::__construct();

        $this->config->load("payroll");
        $this->load->library('media_storage');

        $this->load->model("staff_model");
        $this->load->model("leaverequest_model");
        $this->load->model("timetable_model"); // Load the timetable model
        $this->load->model("day_status_model"); // Day-lock for OD/CPL payroll override
        $this->contract_type    = $this->config->item('contracttype');
        $this->marital_status   = $this->config->item('marital_status');
        $this->staff_attendance = $this->config->item('staffattendance');
        $this->payroll_status   = $this->config->item('payroll_status');
        $this->payment_mode     = $this->config->item('payment_mode');
        $this->status           = $this->config->item('status');
        $this->load->library('mailsmsconf');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    private function csvToIntArray($csv)
    {
        $parts = array_filter(array_map('trim', explode(',', (string) $csv)));
        $result = [];
        foreach ($parts as $part) {
            $value = (int) $part;
            if ($value > 0 && !in_array($value, $result, true)) {
                $result[] = $value;
            }
        }
        return $result;
    }

    private function roleIdsByNames($names)
    {
        $ids = [];
        foreach ($names as $name) {
            $row = $this->db->select('id')->where('LOWER(name)', strtolower($name))->limit(1)->get('roles')->row_array();
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private function leaveTypeIdsByNames($names)
    {
        $ids = [];
        foreach ($names as $name) {
            $row = $this->db->select('id')->where('LOWER(type)', strtolower($name))->limit(1)->get('leave_types')->row_array();
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private function getStaffDepartmentId($staff_id, $staff_details = null)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0) {
            return 0;
        }

        if (is_array($staff_details) && isset($staff_details['department']) && is_numeric($staff_details['department'])) {
            $department_id = (int) $staff_details['department'];
            if ($department_id > 0) {
                return $department_id;
            }
        }

        $row = $this->db->select('department')->where('id', $staff_id)->limit(1)->get('staff')->row_array();
        return isset($row['department']) ? (int) $row['department'] : 0;
    }

    private function resolveRecommenderApproverIds($staff_id, $selected_role_id = 0)
    {
        $staff_id = (int) $staff_id;
        $selected_role_id = (int) $selected_role_id;

        $recommender_id = 0;
        $approver_id = 0;
        $staff_details = $this->staff_model->get($staff_id);

        $setting = $this->setting_model->getSetting();
        if ($setting && !empty($setting->leave_approver_id)) {
            $approver_id = (int) $setting->leave_approver_id;
        }

        $policy = $this->getLeaveManagementPolicy();
        if ($staff_id > 0 && in_array($selected_role_id, $policy['self_approve_roles'], true)) {
            return [
                'recommender_id' => $staff_id,
                'approver_id' => $staff_id,
            ];
        }

        // If selected employee is the configured approver, keep both stages as self.
        if ($staff_id > 0 && $approver_id > 0 && $staff_id === $approver_id) {
            return [
                'recommender_id' => $approver_id,
                'approver_id' => $approver_id,
            ];
        }

        $department_id = $this->getStaffDepartmentId($staff_id, $staff_details);
        if ($department_id > 0) {
            $this->load->model('department_model');
            $department = $this->department_model->getDepartmentType($department_id);
            if ($department && !empty($department['dept_head_id'])) {
                $recommender_id = (int) $department['dept_head_id'];
            }
        }

        if ($recommender_id <= 0 && $approver_id > 0) {
            $recommender_id = $approver_id;
        }

        return [
            'recommender_id' => $recommender_id,
            'approver_id' => $approver_id,
        ];
    }

    private function getLeaveManagementPolicy()
    {
        $setting = $this->setting_model->getSetting();

        $substitution_roles = $this->csvToIntArray((string) ($setting->leave_substitution_required_roles ?? ''));
        $self_approve_roles = $this->csvToIntArray((string) ($setting->leave_self_approve_roles ?? ''));
        $past_date_allowed_roles = $this->csvToIntArray((string) ($setting->leave_past_date_allowed_roles ?? ''));
        $half_day_enabled = isset($setting->leave_enable_half_day) ? (int) $setting->leave_enable_half_day : 1;
        $half_day_allowed_roles = $this->csvToIntArray((string) ($setting->leave_half_day_allowed_roles ?? ''));
        $half_day_allowed_types = $this->csvToIntArray((string) ($setting->leave_half_day_allowed_types ?? ''));

        if (empty($substitution_roles)) {
            $substitution_roles = $this->roleIdsByNames(['teacher']);
        }
        if (empty($self_approve_roles)) {
            $self_approve_roles = $this->roleIdsByNames(['principal']);
        }

        return [
            'substitution_required_roles' => $substitution_roles,
            'self_approve_roles' => $self_approve_roles,
            'past_date_allowed_roles' => $past_date_allowed_roles,
            'half_day_enabled' => $half_day_enabled === 1,
            'half_day_allowed_roles' => $half_day_allowed_roles,
            'half_day_allowed_types' => $half_day_allowed_types,
        ];
    }

    private function isOnDutyLeaveType($leave_type_id)
    {
        $leave_type_id = (int) $leave_type_id;
        if ($leave_type_id <= 0) {
            return false;
        }

        $row = $this->db->select('type')->where('id', $leave_type_id)->limit(1)->get('leave_types')->row_array();
        $name = strtolower(trim((string) ($row['type'] ?? '')));
        return in_array($name, ['on duty', 'od'], true); 
    }

    private function hasLeaveTypeBalanceFlagColumn()
    {
        if ($this->leaveTypeBalanceFlagColumnExists !== null) {
            return $this->leaveTypeBalanceFlagColumnExists;
        }

        $row = $this->db->query("SHOW COLUMNS FROM leave_types LIKE 'requires_balance_check'")->row_array();
        $this->leaveTypeBalanceFlagColumnExists = !empty($row);
        return $this->leaveTypeBalanceFlagColumnExists;
    }

    private function leaveTypeRequiresBalanceCheck($leave_type_details)
    {
        if (!is_array($leave_type_details)) {
            return true;
        }

        if ($this->hasLeaveTypeBalanceFlagColumn() && array_key_exists('requires_balance_check', $leave_type_details)) {
            return ((int) $leave_type_details['requires_balance_check']) === 1;
        }

        $type_name = strtolower(trim((string) ($leave_type_details['type'] ?? '')));
        if (in_array($type_name, ['on duty', 'od', 'hod', 'holiday on duty', 'holiday od'], true)) {
            return false;
        }

        return true;
    }

    private function isMovementCreditType($leave_type_details)
    {
        if (!is_array($leave_type_details)) {
            return false;
        }

        $type_name = strtolower(trim((string) ($leave_type_details['type'] ?? '')));
        return in_array($type_name, ['on duty', 'od', 'hod', 'holiday on duty', 'holiday od'], true);
    }

    /**
     * Returns the credit_source_type_id for a leave type definition array,
     * or null if the type is not a credit-consumer.
        * CPL / comp-off types are treated as standalone claim-based types here.
     */
    private function leaveTypeCreditSourceId($leave_type_details)
    {
        if (!is_array($leave_type_details)) {
            return null;
        }

        // Comp-off style leave types such as CPL are standalone claim-based leave
        // in this setup and must not be tied to OD source-credit consumption.
        $type_name = strtolower(trim((string) ($leave_type_details['type'] ?? '')));
        if (in_array($type_name, ['cpl', 'comp off', 'compensatory off', 'compensatory leave'], true)) {
            return null;
        }

        if (!$this->db->field_exists('credit_source_type_id', 'leave_types')) {
            return null;
        }
        $val = $leave_type_details['credit_source_type_id'] ?? null;
        return empty($val) ? null : (int) $val;
    }

    /**
     * Available credit in the pool for a credit_source type (e.g. OD).
     * = total approved source-type days - all non-disapproved consumer-type days.
     */
    private function getAvailableCreditPoolBalance($staff_id, $source_type_id)
    {
        $earned = (float) ($this->db
            ->select('SUM(leave_days) as total', false)
            ->where('staff_id', $staff_id)
            ->where('leave_type_id', $source_type_id)
            ->where_in('status', ['approve', 'approved'])
            ->get('staff_leave_request')
            ->row_array()['total'] ?? 0);

        $consumer_rows = $this->db
            ->select('id')
            ->where('credit_source_type_id', $source_type_id)
            ->get('leave_types')
            ->result_array();
        $consumer_ids = array_column($consumer_rows, 'id');

        if (empty($consumer_ids)) {
            return $earned;
        }

        $consumed = (float) ($this->db
            ->select('SUM(leave_days) as total', false)
            ->where('staff_id', $staff_id)
            ->where_in('leave_type_id', $consumer_ids)
            ->where('status !=', 'disapprove')
            ->get('staff_leave_request')
            ->row_array()['total'] ?? 0);

        return max(0.0, $earned - $consumed);
    }

    private function parseSchoolDateStrictToYmd($input_date)
    {
        $input_date = trim((string) $input_date);
        if ($input_date === '') {
            return null;
        }

        $format = (string) $this->customlib->getSchoolDateFormat();
        $date_obj = DateTime::createFromFormat('!' . $format, $input_date);
        $errors = DateTime::getLastErrors();

        if (!$date_obj || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
            return null;
        }

        if ($date_obj->format($format) !== $input_date) {
            return null;
        }

        return $date_obj->format('Y-m-d');
    }

    private function parseYmdStrict($input_date)
    {
        $input_date = trim((string) $input_date);
        if ($input_date === '') {
            return null;
        }

        $date_obj = DateTime::createFromFormat('!Y-m-d', $input_date);
        $errors = DateTime::getLastErrors();

        if (!$date_obj || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
            return null;
        }

        if ($date_obj->format('Y-m-d') !== $input_date) {
            return null;
        }

        return $date_obj->format('Y-m-d');
    }

    private function resolvePostedLeaveDateToYmd($display_field, $iso_field)
    {
        $iso_value = $this->input->post($iso_field);
        $parsed_iso = $this->parseYmdStrict($iso_value);
        if (!empty($parsed_iso)) {
            return $parsed_iso;
        }

        return $this->parseSchoolDateStrictToYmd($this->input->post($display_field));
    }

    private function getPresentAttendanceConflictDate($staff_id, $from_date, $to_date)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0 || empty($from_date) || empty($to_date)) {
            return null;
        }

        $row = $this->db->select('sa.date, sat.type, sat.key_value')
            ->from('staff_attendance sa')
            ->join('staff_attendance_type sat', 'sat.id = sa.staff_attendance_type_id', 'left')
            ->where('sa.staff_id', $staff_id)
            ->where('sa.date >=', $from_date)
            ->where('sa.date <=', $to_date)
            ->group_start()
            ->where('LOWER(TRIM(sat.type))', 'present')
            ->or_where('UPPER(TRIM(sat.key_value))', 'P')
            ->group_end()
            ->order_by('sa.date', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) ? $row : null;
    }

    private function getHalfDayAttendanceConflictDate($staff_id, $from_date, $to_date)
    {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0 || empty($from_date) || empty($to_date)) {
            return null;
        }

        $row = $this->db->select('sa.date, sat.type, sat.key_value')
            ->from('staff_attendance sa')
            ->join('staff_attendance_type sat', 'sat.id = sa.staff_attendance_type_id', 'left')
            ->where('sa.staff_id', $staff_id)
            ->where('sa.date >=', $from_date)
            ->where('sa.date <=', $to_date)
            ->group_start()
            ->where('UPPER(TRIM(sat.key_value))', 'HD')
            ->or_where('LOWER(TRIM(sat.type))', 'half day')
            ->group_end()
            ->order_by('sa.date', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) ? $row : null;
    }

    private function canApplyPastDateByPolicy($role_id, $policy = null)
    {
        if ($policy === null) {
            $policy = $this->getLeaveManagementPolicy();
        }
        if (empty($policy['past_date_allowed_roles'])) {
            return true;
        }
        return in_array((int) $role_id, $policy['past_date_allowed_roles'], true);
    }

    private function canApplyHalfDayByPolicy($role_id, $leave_type_id, $policy = null)
    {
        if ($policy === null) {
            $policy = $this->getLeaveManagementPolicy();
        }

        if (empty($policy['half_day_enabled'])) {
            return false;
        }

        $role_id = (int) $role_id;
        $leave_type_id = (int) $leave_type_id;

        $allowed_roles = $policy['half_day_allowed_roles'] ?? [];
        $allowed_types = $policy['half_day_allowed_types'] ?? [];

        if (!empty($allowed_roles) && !in_array($role_id, $allowed_roles, true)) {
            return false;
        }
        if (!empty($allowed_types) && !in_array($leave_type_id, $allowed_types, true)) {
            return false;
        }

        return true;
    }
    private function currentUserIsAdminOrSuperAdmin()
    {
        $role_raw = $this->customlib->getStaffRole();
        $role = json_decode($role_raw);

        $role_id = isset($role->id) ? (int) $role->id : 0;
        $role_name = strtolower(trim((string) ($role->name ?? '')));

        if (in_array($role_name, ['admin', 'super admin'], true)) {
            return true;
        }

        $privileged_ids = $this->roleIdsByNames(['admin', 'super admin']);
        return $role_id > 0 && in_array($role_id, $privileged_ids, true);
    }

    private function isSubstitutionRequiredByPolicy($role_id, $leave_type_id, $policy = null)
    {
        if ($policy === null) {
            $policy = $this->getLeaveManagementPolicy();
        }

        $role_id = (int) $role_id;
        $leave_type_id = (int) $leave_type_id;

        if ($role_id <= 0 || $leave_type_id <= 0) {
            return false;
        }

        if (!in_array($role_id, $policy['substitution_required_roles'], true)) {
            return false;
        }

        if ($this->isOnDutyLeaveType($leave_type_id)) {
            return false;
        }

        return true;
    }

    public function leaverequest()
    {
        if ((int) $this->customlib->getStaffID() <= 0) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leaverequest/leaverequest');
        $all_leave_request = $this->leaverequest_model->staff_leave_request();
        $filtered_leave_request = [];
        $current_user_id = $this->customlib->getStaffID();
        $is_admin_or_super_admin = $this->currentUserIsAdminOrSuperAdmin();

        // Admin / Super Admin: in approve view, show only approver-stage records (plus finalized history)
        if ($is_admin_or_super_admin) {
            foreach ($all_leave_request as $request) {
                $recommender_done = in_array((string) ($request['recommender_status'] ?? ''), ['recommended', 'approved'], true);
                $is_finalized = in_array((string) ($request['status'] ?? ''), ['approved', 'disapproved'], true);

                if ($recommender_done || $is_finalized) {
                    $filtered_leave_request[] = $request;
                }
            }
        } else {
            foreach ($all_leave_request as $request) {
                $show = false;

                // Approver sees only requests assigned to them and only after recommendation
                if ($request['approver_id'] == $current_user_id) {
                    if ($request['recommender_status'] == 'recommended' || $request['recommender_status'] == 'approved') {
                        $show = true;
                    }
                }
                
                if ($show) {
                    $filtered_leave_request[] = $request;
                }
            }
        }
        $data["leave_request"] = $filtered_leave_request;
        $LeaveTypes            = $this->staff_model->getLeaveType();
        $userdata              = $this->customlib->getUserData();
        $current_staff_id = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;
        $data['staff_id'] = $current_staff_id;
        $data["leavetype"]     = $LeaveTypes;
        $staffRole             = $this->staff_model->getStaffRole();
        $data["staffrole"]     = $staffRole;
        $data["status"]        = $this->status;
        $data['leave_management_policy'] = $this->getLeaveManagementPolicy();
        $data['is_admin_or_super_admin'] = $is_admin_or_super_admin;
        $data['sch_setting_detail'] = $this->sch_setting_detail;

        // Fetch staff details, timetable, and potential substitutes
        if ($current_staff_id === 0) {
            $current_staff_id = (int) $this->customlib->getStaffID();
        }
        $staff_details = $this->staff_model->get($current_staff_id);
        $data['current_staff_details'] = $staff_details;

        // Load Subjecttimetable_model to get staff timetable
        $this->load->model('subjecttimetable_model');

        // Fetch timetable for the current staff (for a default range, e.g., current month)
        // This is a placeholder; actual leave application might use selected leave dates
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $data['staff_timetable'] = $this->subjecttimetable_model->getStaffTimetable($current_staff_id, $start_date, $end_date);

        $potential_substitutes = [];
        $recommender_staff = null;
        $approver_staff = null;
        $department_id = $this->getStaffDepartmentId($current_staff_id, $staff_details);
        if ($department_id > 0) {
            $potential_substitutes = $this->staff_model->getEmployeeByDepartment($department_id, $current_staff_id);
        }
        $data['potential_substitutes'] = $potential_substitutes;

        $routing = $this->resolveRecommenderApproverIds($current_staff_id, 0);
        if (!empty($routing['recommender_id'])) {
            $recommender_details = $this->staff_model->get((int) $routing['recommender_id']);
            if (!empty($recommender_details) && is_array($recommender_details)) {
                $recommender_staff = $recommender_details;
            }
        }

        if (!empty($routing['approver_id'])) {
            $approver_details = $this->staff_model->get((int) $routing['approver_id']);
            if (!empty($approver_details) && is_array($approver_details)) {
                $approver_staff = $approver_details;
            }
        }

        if (!empty($recommender_staff)) {
            $data['recommender_info'] = $recommender_staff['name'] . ' ' . $recommender_staff['surname'] . ' (' . $recommender_staff['designation'] . ')';
        } else {
            $data['recommender_info'] = $this->lang->line('not_assigned');
        }

        if (!empty($approver_staff)) {
            $data['approver_info'] = $approver_staff['name'] . ' ' . $approver_staff['surname'] . ' (' . $approver_staff['designation'] . ')';
        } else {
            $data['approver_info'] = $this->lang->line('not_assigned');
        }
        $data['leave_approver_configured'] = !empty($approver_staff);
        $data['leave_screen_mode'] = null; // Approve leave admin view — no restriction

        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/staffleaverequest", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * Apply Leave — accessible to all logged-in staff.
     * Always scoped to the current staff's own leave records.
     */
    public function applyleave()
    {
        if ((int) $this->customlib->getStaffID() <= 0) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leaverequest/applyleave');

        $current_staff_id = (int) $this->customlib->getStaffID();
        $data['staff_id']              = $current_staff_id;
        $data['leave_request']         = $this->leaverequest_model->staff_leave_request($current_staff_id);
        $data['is_admin_or_super_admin'] = false;  // always staff view
        $data['leavetype']             = $this->staff_model->getLeaveType();
        $data['staffrole']             = $this->staff_model->getStaffRole();
        $data['status']                = $this->status;
        $data['leave_management_policy'] = $this->getLeaveManagementPolicy();
        $data['sch_setting_detail']    = $this->sch_setting_detail;

        $staff_details = $this->staff_model->get($current_staff_id);
        $data['current_staff_details'] = $staff_details;

        $this->load->model('subjecttimetable_model');
        $data['staff_timetable'] = $this->subjecttimetable_model->getStaffTimetable(
            $current_staff_id, date('Y-m-01'), date('Y-m-t')
        );

        $department_id = $this->getStaffDepartmentId($current_staff_id, $staff_details);
        $data['potential_substitutes'] = $department_id > 0
            ? $this->staff_model->getEmployeeByDepartment($department_id, $current_staff_id)
            : [];

        $routing = $this->resolveRecommenderApproverIds($current_staff_id, 0);
        $recommender_staff = null;
        $approver_staff    = null;
        if (!empty($routing['recommender_id'])) {
            $rec = $this->staff_model->get((int) $routing['recommender_id']);
            if (!empty($rec) && is_array($rec)) { $recommender_staff = $rec; }
        }
        if (!empty($routing['approver_id'])) {
            $apr = $this->staff_model->get((int) $routing['approver_id']);
            if (!empty($apr) && is_array($apr)) { $approver_staff = $apr; }
        }

        $data['recommender_info'] = $recommender_staff
            ? $recommender_staff['name'] . ' ' . $recommender_staff['surname'] . ' (' . $recommender_staff['designation'] . ')'
            : $this->lang->line('not_assigned');
        $data['approver_info'] = $approver_staff
            ? $approver_staff['name'] . ' ' . $approver_staff['surname'] . ' (' . $approver_staff['designation'] . ')'
            : $this->lang->line('not_assigned');
        $data['leave_approver_configured'] = !empty($approver_staff);
        $data['leave_screen_mode'] = 'apply_leave'; // Apply Leave: regular CL/ML only

        // --- Leave balance summary for the Apply Leave screen ---
        $alloted_leavetype = $this->leaverequest_model->allotedLeaveType($current_staff_id);
        $all_leavetypes    = $this->staff_model->getLeaveType();
        $allotted_map = [];
        foreach ($alloted_leavetype as $lv) {
            $allotted_map[$lv['leave_type_id']] = $lv;
        }
        $balance_summary  = [];
        $has_any_balance  = false;
        foreach ($all_leavetypes as $lv) {
            $is_lop             = isset($lv['is_lop']) && $lv['is_lop'] == 1;
            $credit_src         = $this->leaveTypeCreditSourceId($lv);
            $is_credit_consumer = !$is_lop && $credit_src !== null;
            $is_claim_based     = !$is_lop && !$is_credit_consumer && $this->leaveTypeRequiresBalanceCheck($lv) === false;
            $is_movement_credit = $is_claim_based && $this->isMovementCreditType($lv);

            // Apply Leave balance panel: LOP is always available — show without balance check.
            if ($is_lop) {
                $balance_summary[] = [
                    'type'      => $lv['type'],
                    'allotted'  => null,
                    'used'      => null,
                    'available' => null, // no cap
                    'kind'      => 'lop',
                ];
                $has_any_balance = true;
                continue;
            }

            if ($is_credit_consumer) {
                // CPL: balance = OD credit pool earned minus already-consumed CPL.
                // Always include in panel — even if 0, so staff sees full entitlement picture.
                $pool_balance = $this->getAvailableCreditPoolBalance($current_staff_id, $credit_src);
                $balance_summary[] = [
                    'type'      => $lv['type'],
                    'allotted'  => null,   // credit-based, no fixed allotment
                    'used'      => null,
                    'available' => $pool_balance,
                    'kind'      => 'credit_consumer',
                ];
                if ($pool_balance > 0) {
                    $has_any_balance = true;
                }
            } elseif ($is_claim_based) {
                // Claim-based types (OD, CPL): primary source is staff_monthly_leave_balance
                // (most-recent row), matching exactly what the staff profile Leaves tab shows.
                // These types accumulate balance via attendance approval credits, so
                // closing_balance is the earned pool; (used_for_lop_adjustment + used_for_leave_application)
                // is what's been consumed.  Fall back to staff_leave_details.alloted_leave only when
                // no monthly balance row exists yet.
                $mlb = $this->db
                    ->where('staff_id', $current_staff_id)
                    ->where('leave_type_id', $lv['id'])
                    ->order_by('year', 'DESC')
                    ->order_by('month', 'DESC')
                    ->limit(1)
                    ->get('staff_monthly_leave_balance')
                    ->row_array();

                if (!empty($mlb)) {
                    $closing    = (float)($mlb['closing_balance'] ?? 0);
                    $used_days  = (float)($mlb['used_for_lop_adjustment'] ?? 0)
                                + (float)($mlb['used_for_leave_application'] ?? 0);
                    $available  = max(0.0, $closing - $used_days);
                    $balance_summary[] = [
                        'type'      => $lv['type'],
                        'allotted'  => $closing,
                        'used'      => $used_days,
                        'available' => $available,
                        'kind'      => 'regular',
                    ];
                    if ($available > 0) {
                        $has_any_balance = true;
                    }
                } else {
                    // No monthly balance yet — fall back to static HR allotment.
                    $allotted_days = isset($allotted_map[$lv['id']])
                        ? (float)(($allotted_map[$lv['id']]['alloted_leave'] !== '') ? $allotted_map[$lv['id']]['alloted_leave'] : 0)
                        : 0.0;
                    $count_data = $this->leaverequest_model->countLeavesData($current_staff_id, $lv['id']);
                    $used_days  = !empty($count_data['approve_leave']) ? (float)$count_data['approve_leave'] : 0.0;
                    $available  = max(0.0, $allotted_days - $used_days);
                    $balance_summary[] = [
                        'type'      => $lv['type'],
                        'allotted'  => $allotted_days,
                        'used'      => $used_days,
                        'available' => $available,
                        'kind'      => 'regular',
                    ];
                    if ($available > 0) {
                        $has_any_balance = true;
                    }
                }
            } else {
                // Regular type (CL / ML etc.): show all active types.
                // If not allotted by HR, shown as 'unallotted' so staff sees the full system picture.
                if (!isset($allotted_map[$lv['id']])) {
                    $balance_summary[] = [
                        'type'      => $lv['type'],
                        'allotted'  => 0,
                        'used'      => 0,
                        'available' => 0,
                        'kind'      => 'unallotted',
                    ];
                    continue;
                }
                $allotted_days = (float) $allotted_map[$lv['id']]['alloted_leave'];
                $count_data    = $this->leaverequest_model->countLeavesData($current_staff_id, $lv['id']);
                $used_days     = !empty($count_data['approve_leave']) ? (float) $count_data['approve_leave'] : 0.0;
                $available     = $allotted_days - $used_days;
                // Include allotted types — even exhausted ones — so staff sees the full picture.
                $balance_summary[] = [
                    'type'      => $lv['type'],
                    'allotted'  => $allotted_days,
                    'used'      => $used_days,
                    'available' => max(0, $available),
                    'kind'      => 'regular',
                ];
                if ($available > 0) {
                    $has_any_balance = true;
                }
            }
        }
        $data['leave_balance_summary'] = $balance_summary;
        $data['has_any_leave_balance']  = $has_any_balance;

        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/staffleaverequest", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * Apply Leave Claim — for claim-based leave types (OD, CPL, etc.).
     * Always scoped to the current staff's own leave records.
     */
    public function claimleave()
    {
        if ((int) $this->customlib->getStaffID() <= 0) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leaverequest/claimleave');

        $current_staff_id = (int) $this->customlib->getStaffID();
        $is_admin_or_super_admin = $this->currentUserIsAdminOrSuperAdmin();
        $data['staff_id']              = $current_staff_id;
        // Admin/Super Admin see all records; regular staff see only their own.
        $data['leave_request']         = $is_admin_or_super_admin
            ? $this->leaverequest_model->staff_leave_request()
            : $this->leaverequest_model->staff_leave_request($current_staff_id);
        $data['is_admin_or_super_admin'] = $is_admin_or_super_admin;
        $data['leavetype']             = $this->staff_model->getLeaveType();
        $data['staffrole']             = $this->staff_model->getStaffRole();
        $data['status']                = $this->status;
        $data['leave_management_policy'] = $this->getLeaveManagementPolicy();
        $data['sch_setting_detail']    = $this->sch_setting_detail;

        $staff_details = $this->staff_model->get($current_staff_id);
        $data['current_staff_details'] = $staff_details;

        $this->load->model('subjecttimetable_model');
        $data['staff_timetable'] = $this->subjecttimetable_model->getStaffTimetable(
            $current_staff_id, date('Y-m-01'), date('Y-m-t')
        );

        $department_id = $this->getStaffDepartmentId($current_staff_id, $staff_details);
        $data['potential_substitutes'] = $department_id > 0
            ? $this->staff_model->getEmployeeByDepartment($department_id, $current_staff_id)
            : [];

        $routing = $this->resolveRecommenderApproverIds($current_staff_id, 0);
        $recommender_staff = null;
        $approver_staff    = null;
        if (!empty($routing['recommender_id'])) {
            $rec = $this->staff_model->get((int) $routing['recommender_id']);
            if (!empty($rec) && is_array($rec)) { $recommender_staff = $rec; }
        }
        if (!empty($routing['approver_id'])) {
            $apr = $this->staff_model->get((int) $routing['approver_id']);
            if (!empty($apr) && is_array($apr)) { $approver_staff = $apr; }
        }

        $data['recommender_info'] = $recommender_staff
            ? $recommender_staff['name'] . ' ' . $recommender_staff['surname'] . ' (' . $recommender_staff['designation'] . ')'
            : $this->lang->line('not_assigned');
        $data['approver_info'] = $approver_staff
            ? $approver_staff['name'] . ' ' . $approver_staff['surname'] . ' (' . $approver_staff['designation'] . ')'
            : $this->lang->line('not_assigned');
        $data['leave_approver_configured'] = !empty($approver_staff);
        $data['leave_screen_mode'] = 'claim_leave'; // Apply Leave Claim: OD/CPL claim-based types only

        // --- Leave balance summary for the Apply Leave Claim screen ---
        $alloted_leavetype = $this->leaverequest_model->allotedLeaveType($current_staff_id);
        $all_leavetypes    = $this->staff_model->getLeaveType();
        $allotted_map = [];
        foreach ($alloted_leavetype as $lv) {
            $allotted_map[$lv['leave_type_id']] = $lv;
        }
        $balance_summary  = [];
        $has_any_balance  = false;
        foreach ($all_leavetypes as $lv) {
            $is_lop             = isset($lv['is_lop']) && $lv['is_lop'] == 1;
            $credit_src         = $this->leaveTypeCreditSourceId($lv);
            $is_credit_consumer = !$is_lop && $credit_src !== null;
            $is_claim_based     = !$is_lop && !$is_credit_consumer && $this->leaveTypeRequiresBalanceCheck($lv) === false;
            $is_movement_credit = $is_claim_based && $this->isMovementCreditType($lv);

            if ($is_lop) {
                continue; // LOP not shown on claim leave screen
            }

            if ($is_credit_consumer) {
                $pool_balance = $this->getAvailableCreditPoolBalance($current_staff_id, $credit_src);
                $balance_summary[] = [
                    'type'      => $lv['type'],
                    'allotted'  => null,
                    'used'      => null,
                    'available' => $pool_balance,
                    'kind'      => 'credit_consumer',
                ];
                if ($pool_balance > 0) {
                    $has_any_balance = true;
                }
            } elseif ($is_movement_credit) {
                $balance_summary[] = [
                    'type'      => $lv['type'],
                    'allotted'  => null,
                    'used'      => null,
                    'available' => null,
                    'kind'      => 'claim_based',
                ];
                $has_any_balance = true;
            }
            // Regular CL/ML types not shown on claim screen
        }
        $data['leave_balance_summary'] = $balance_summary;
        $data['has_any_leave_balance']  = $has_any_balance;

        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/staffleaverequest", $data);
        $this->load->view("layout/footer", $data);
    }

    public function countLeave($id)
    {
        $lid               = $this->input->post("lid");
        // 'claim_leave' = no-balance claim-based types such as OD/CPL.
        // 'adjust_lop'  = show all leave types; paid/non-paid behavior is handled by type rules at submit
        $mode              = $this->input->post("mode") ?: 'claim_leave';
        $alloted_leavetype = $this->leaverequest_model->allotedLeaveType($id);
        $all_leavetypes    = $this->staff_model->getLeaveType();

        $html = "<select  name='leave_type' id='leave_type' class='form-control'><option value=''>" . $this->lang->line('select') . "</option>";
        
        $leave_types_to_display = array();

        // Create a map of allotted leaves for easier lookup
        $allotted_map = array();
        foreach($alloted_leavetype as $leave){
            $allotted_map[$leave['leave_type_id']] = $leave;
        }

        if (!empty($all_leavetypes)) {
            foreach ($all_leavetypes as $key => $value) {
                
                $is_allotted = isset($allotted_map[$value['id']]);
                $is_lop = isset($value['is_lop']) && $value['is_lop'] == 1;
                // Source-credit consumer types: consume from another leave type's credit pool.
                $credit_src = $this->leaveTypeCreditSourceId($value);
                $is_credit_consumer = !$is_lop && $credit_src !== null;
                $is_claim_based = !$is_lop && !$is_credit_consumer && $this->leaveTypeRequiresBalanceCheck($value) === false;
                $is_movement_credit = $is_claim_based && $this->isMovementCreditType($value);

                // --- Mode-based filtering ---
                if ($mode === 'claim_leave') {
                    // Claim Leave screen: only claim-based types (OD/CPL — no balance check required).
                    if (!$is_claim_based && !$is_credit_consumer) {
                        continue;
                    }
                } elseif ($mode === 'apply_leave') {
                    // Apply Leave screen: is_lop=1 always allowed; all is_lop=0 types require balance > 0.
                    if (!$is_lop) {
                        if ($is_credit_consumer) {
                            // Credit-consumer (CPL consuming OD pool): check OD pool balance.
                            $pool_bal = $this->getAvailableCreditPoolBalance($id, $credit_src);
                            if ($pool_bal <= 0) {
                                continue;
                            }
                        } elseif ($is_claim_based) {
                            // Claim-based earner types (OD, CPL-earner): balance in staff_monthly_leave_balance.
                            $bal_month = (int) date('m');
                            $bal_year  = (int) date('Y');
                            $mlb = $this->db
                                ->where('staff_id', $id)
                                ->where('leave_type_id', $value['id'])
                                ->where('month', $bal_month)
                                ->where('year', $bal_year)
                                ->limit(1)
                                ->get('staff_monthly_leave_balance')
                                ->row_array();
                            if (empty($mlb) || max(0.0, (float)($mlb['closing_balance'] ?? 0)) <= 0) {
                                continue;
                            }
                        } else {
                            // Regular allotted types (CL, ML): check HR allotment balance.
                            if (!$is_allotted) {
                                continue; // Un-allotted regular types: nothing to apply.
                            }
                            $allotted_leave_days = (float) $allotted_map[$value['id']]['alloted_leave'];
                            $count_leaves  = $this->leaverequest_model->countLeavesData($id, $value['id']);
                            $approve_leave = !empty($count_leaves['approve_leave']) ? (float) $count_leaves['approve_leave'] : 0;
                            if (($allotted_leave_days - $approve_leave) <= 0) {
                                continue;
                            }
                        }
                    }
                }
                // 'adjust_lop' mode intentionally shows all leave types; payroll impact is decided by leave type rules.

                if ($is_lop) {
                    if ($is_allotted) {
                        // It's LOP, but has an allotted amount for tracking. Show it, with count (0 or negative is fine).
                        $allotted_leave_days = (float) ($allotted_map[$value['id']]['alloted_leave'] !== '' ? $allotted_map[$value['id']]['alloted_leave'] : 0);
                        $count_leaves = $this->leaverequest_model->countLeavesData($id, $value["id"]);
                        $approve_leave = !empty($count_leaves['approve_leave']) ? (float) $count_leaves['approve_leave'] : 0.0;
                        $available = $allotted_leave_days - $approve_leave;

                        $leave_types_to_display[$value['id']] = array(
                            'id' => $value['id'],
                            'type' => $value['type'],
                            'display' => $value['type'] . " (" . $available . ")"
                        );
                    } else {
                        // It's a LOP leave not specifically allotted, so it's unlimited
                         $leave_types_to_display[$value['id']] = array(
                            'id' => $value['id'],
                            'type' => $value['type'],
                            'display' => $value['type']
                        );
                    }
                } elseif ($is_credit_consumer) {
                    // Source-credit consumer type — show available source pool balance
                    $pool_balance = $this->getAvailableCreditPoolBalance($id, $credit_src);
                    $src_type = $this->staff_model->getLeaveType($credit_src);
                    $src_name = $src_type['type'] ?? 'OD';
                    $leave_types_to_display[$value['id']] = array(
                        'id'      => $value['id'],
                        'type'    => $value['type'],
                        'display' => $value['type'] . ' (' . $pool_balance . ' ' . $src_name . ' credit available)',
                    );
                } elseif ($is_claim_based) {
                    // Claim-based types (OD, CPL): balance lives in staff_monthly_leave_balance.
                    // Use the most-recent closing_balance as the available count — same source as
                    // the balance panel and staff profile, so the number is consistent everywhere.
                    if ($mode === 'claim_leave') {
                        // On the Claim Leave screen show just the type name (no count suffix).
                        $leave_types_to_display[$value['id']] = array(
                            'id'      => $value['id'],
                            'type'    => $value['type'],
                            'display' => $value['type'],
                        );
                    } else {
                        // apply_leave / adjust_lop: show the actual earned balance.
                        $mlb_display = $this->db
                            ->where('staff_id', $id)
                            ->where('leave_type_id', $value['id'])
                            ->order_by('year', 'DESC')
                            ->order_by('month', 'DESC')
                            ->limit(1)
                            ->get('staff_monthly_leave_balance')
                            ->row_array();
                        if (!empty($mlb_display)) {
                            $closing   = (float)($mlb_display['closing_balance'] ?? 0);
                            $used_disp = (float)($mlb_display['used_for_lop_adjustment'] ?? 0)
                                       + (float)($mlb_display['used_for_leave_application'] ?? 0);
                            $available = max(0.0, $closing - $used_disp);
                        } else {
                            // No monthly balance yet — fall back to static HR allotment.
                            $allotted_leave_days = $is_allotted
                                ? (float)(($allotted_map[$value['id']]['alloted_leave'] !== '') ? $allotted_map[$value['id']]['alloted_leave'] : 0)
                                : 0.0;
                            $count_leaves  = $this->leaverequest_model->countLeavesData($id, $value['id']);
                            $approve_leave = !empty($count_leaves['approve_leave']) ? (float)$count_leaves['approve_leave'] : 0.0;
                            $available     = max(0.0, $allotted_leave_days - $approve_leave);
                        }
                        $leave_types_to_display[$value['id']] = array(
                            'id'      => $value['id'],
                            'type'    => $value['type'],
                            'display' => $value['type'] . ' (' . $available . ')',
                        );
                    }
                } else {
                    // Regular allotted leave (CL / ML etc.)
                    if ($is_allotted) {
                        $allotted_leave_days = (float) ($allotted_map[$value['id']]['alloted_leave'] !== '' ? $allotted_map[$value['id']]['alloted_leave'] : 0);
                        $count_leaves = $this->leaverequest_model->countLeavesData($id, $value["id"]);
                        $approve_leave = !empty($count_leaves['approve_leave']) ? (float) $count_leaves['approve_leave'] : 0.0;
                        $available = $allotted_leave_days - $approve_leave;

                        if ($available >= 0) {
                            $leave_types_to_display[$value['id']] = array(
                                'id' => $value['id'],
                                'type' => $value['type'],
                               'display' => $value['type'] . " (" . $available . ")"
                            );
                        }
                    } else { // Not LOP and not allotted
                        $leave_types_to_display[$value['id']] = array(
                            'id' => $value['id'],
                            'type' => $value['type'],
                           'display' => $value['type'] . " (0)"
                        );
                    }
                }
            }
        }

        // Generate HTML
        if (!empty($leave_types_to_display)) {
            foreach ($leave_types_to_display as $leave) {
                $selected = ($lid == $leave["id"]) ? "selected" : "";
                $html .= "<option value='" . $leave["id"] . "' " . $selected . ">" . $leave["display"] . "</option>";
            }
        }

        $html .= "</select>";
        echo $html;
    }

    /**
     * AJAX: Checks whether a date range violates the leave type's day_type_restriction.
     * POST: leave_type_id, leave_from_date (Y-m-d), leave_to_date (Y-m-d)
     * Returns: { status, is_restricted, restriction_type, warning, violating_dates }
     */
    public function checkDayType()
    {
        $leave_type_id  = (int) $this->input->post('leave_type_id');
        $leave_from_str = $this->input->post('leave_from_date');
        $leave_to_str   = $this->input->post('leave_to_date');

        if ($leave_type_id <= 0 || empty($leave_from_str) || empty($leave_to_str)) {
            echo json_encode(['status' => 'ok', 'is_restricted' => false]);
            return;
        }

        // Check if the column exists
        if (!$this->db->field_exists('day_type_restriction', 'leave_types')) {
            echo json_encode(['status' => 'ok', 'is_restricted' => false]);
            return;
        }

        $leave_type = $this->staff_model->getLeaveType($leave_type_id);
        $restriction = isset($leave_type['day_type_restriction']) ? $leave_type['day_type_restriction'] : null;

        if (empty($restriction)) {
            echo json_encode(['status' => 'ok', 'is_restricted' => false, 'restriction_type' => null]);
            return;
        }

        $violation = $this->checkDayTypeViolation($leave_from_str, $leave_to_str, $restriction);

        if ($violation !== null) {
            echo json_encode([
                'status'           => 'warning',
                'is_restricted'    => true,
                'restriction_type' => $restriction,
                'warning'          => $violation,
            ]);
        } else {
            echo json_encode([
                'status'           => 'ok',
                'is_restricted'    => true,
                'restriction_type' => $restriction,
            ]);
        }
    }

    /**
     * Returns a violation message if the date range conflicts with the given day_type_restriction,
     * or NULL if there is no violation.
     *
     * @param string $leavefrom Y-m-d
     * @param string $leaveto   Y-m-d
     * @param string $restriction 'working_day' | 'holiday'
     * @return string|null
     */
    private function checkDayTypeViolation($leavefrom, $leaveto, $restriction)
    {
        if (empty($restriction) || empty($leavefrom) || empty($leaveto)) {
            return null;
        }

        $start = new DateTime($leavefrom);
        $end   = new DateTime($leaveto);

        // --- Weekend days from settings (0=Sun … 6=Sat) ---
        $settings       = $this->setting_model->getSetting();
        $weekendDaysStr = isset($settings->weekend_days) && $settings->weekend_days !== ''
            ? $settings->weekend_days : '0'; // default: Sunday only
        $weekendDays    = array_map('intval', explode(',', $weekendDaysStr));

        // Second-Saturday rule
        $isSecondSatHoliday = isset($settings->isSecondSaturdayHoliday)
            ? (int) $settings->isSecondSaturdayHoliday : 0;

        $isWeekend = function (DateTime $dt) use ($weekendDays, $isSecondSatHoliday) {
            $dow = (int) $dt->format('w'); // 0=Sun, 6=Sat
            if (in_array($dow, $weekendDays, true)) {
                return true;
            }
            // 2nd Saturday rule
            if ($isSecondSatHoliday === 1 && $dow === 6) {
                $day = (int) $dt->format('j');
                if ($day >= 8 && $day <= 14) {
                    return true;
                }
            }
            return false;
        };

        // --- Official holidays from annual_calendar ---
        $holidays_raw = $this->db
            ->select('from_date, to_date')
            ->from('annual_calendar')
            ->where('is_active', 1)
            ->where('from_date <=', $leaveto)
            ->where('to_date >=', $leavefrom)
            ->get()
            ->result_array();

        $holiday_dates = [];
        foreach ($holidays_raw as $h) {
            $h_start = new DateTime($h['from_date']);
            $h_end   = new DateTime($h['to_date']);
            $cur  = clone (max($start, $h_start) === $start ? $start : $h_start);
            $stop = min($end, $h_end);
            // Clamp to our range
            if (new DateTime($h['from_date']) < $start) { $cur = clone $start; } else { $cur = clone $h_start; }
            if (new DateTime($h['to_date'])   > $end)   { $stop = clone $end; }  else { $stop = clone $h_end; }
            while ($cur <= $stop) {
                $holiday_dates[$cur->format('Y-m-d')] = true;
                $cur->modify('+1 day');
            }
        }

        // Helper: is a date a non-working day (weekend OR official holiday)?
        $isNonWorking = function (DateTime $dt) use ($holiday_dates, $isWeekend) {
            return isset($holiday_dates[$dt->format('Y-m-d')]) || $isWeekend($dt);
        };

        if ($restriction === 'working_day') {
            // OD: must be applied on actual working days (not weekends, not holidays)
            $violations = [];
            $cur = clone $start;
            while ($cur <= $end) {
                if ($isNonWorking($cur)) {
                    $label = $isWeekend($cur) ? 'weekend' : 'holiday';
                    $violations[] = date($this->customlib->getSchoolDateFormat(), $cur->getTimestamp()) . ' (' . $label . ')';
                }
                $cur->modify('+1 day');
            }
            if (!empty($violations)) {
                return 'On Duty can only be applied on working days. Non-working dates in range: ' . implode(', ', $violations) . '.';
            }
        } elseif ($restriction === 'holiday') {
            // CPL: at least one day must be a non-working day (official holiday OR weekend)
            $has_non_working = false;
            $cur = clone $start;
            while ($cur <= $end) {
                if ($isNonWorking($cur)) {
                    $has_non_working = true;
                    break;
                }
                $cur->modify('+1 day');
            }
            if (!$has_non_working) {
                return 'Compensatory Planned Leave (CPL) can only be applied for non-working days (holidays or weekends). No such days found in the selected date range.';
            }
        }

        return null;
    }

    /**
     * AJAX: Returns leave balance / credit info for a given staff + leave type.
     * Used by the leave application form to show the balance panel.
     */
    public function leaveTypeBalanceInfo()
    {
        $staff_id      = (int) $this->input->post('staff_id');
        $leave_type_id = (int) $this->input->post('leave_type_id');

        if ($staff_id <= 0 || $leave_type_id <= 0) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
            return;
        }

        $leave_type = $this->staff_model->getLeaveType($leave_type_id);
        if (empty($leave_type)) {
            echo json_encode(['status' => 'fail', 'message' => 'Leave type not found']);
            return;
        }

        $is_lop                 = (int)($leave_type['is_lop'] ?? 0) === 1;
        $requires_balance_check = $this->leaveTypeRequiresBalanceCheck($leave_type);
        $credit_source_type_id  = $this->leaveTypeCreditSourceId($leave_type);
        $is_credit_consumer     = !$is_lop && $credit_source_type_id !== null;
        $is_claim_based         = !$is_lop && !$is_credit_consumer && !$requires_balance_check;
        $is_movement_credit     = $is_claim_based && $this->isMovementCreditType($leave_type);

        if ($is_credit_consumer) {
            // --- Source-credit consumer ---
            $source_type   = $this->staff_model->getLeaveType($credit_source_type_id);
            $source_name   = $source_type['type'] ?? 'OD';

            // Total approved source (OD) days belong to the staff
            $earned_total = (float) ($this->db
                ->select('SUM(leave_days) as total', false)
                ->where('staff_id', $staff_id)
                ->where('leave_type_id', $credit_source_type_id)
                ->where_in('status', ['approve', 'approved'])
                ->get('staff_leave_request')
                ->row_array()['total'] ?? 0);

            // Total consumed by all consumer-type requests (non-disapproved)
            $consumer_ids = array_column(
                $this->db->select('id')->where('credit_source_type_id', $credit_source_type_id)
                    ->get('leave_types')->result_array(),
                'id'
            );
            $consumed_total = empty($consumer_ids) ? 0.0 : (float) ($this->db
                ->select('SUM(leave_days) as total', false)
                ->where('staff_id', $staff_id)
                ->where_in('leave_type_id', $consumer_ids)
                ->where('status !=', 'disapprove')
                ->get('staff_leave_request')
                ->row_array()['total'] ?? 0);

            // Further split: approved vs pending
            $consumed_approved = empty($consumer_ids) ? 0.0 : (float) ($this->db
                ->select('SUM(leave_days) as total', false)
                ->where('staff_id', $staff_id)
                ->where_in('leave_type_id', $consumer_ids)
                ->where_in('status', ['approve', 'approved'])
                ->get('staff_leave_request')
                ->row_array()['total'] ?? 0);
            $consumed_pending   = max(0, $consumed_total - $consumed_approved);
            $available_credit   = max(0, $earned_total - $consumed_total);

            $note = '<strong>Credit-consuming leave</strong> — uses earned ' . htmlspecialchars($source_name, ENT_QUOTES) . ' credit. '
                . 'Balance deducted from your ' . htmlspecialchars($source_name, ENT_QUOTES) . ' credit pool immediately on approval.';

            echo json_encode([
                'status'             => 'success',
                'type_label'         => htmlspecialchars((string)($leave_type['type'] ?? ''), ENT_QUOTES),
                'is_lop'             => false,
                'is_credit_earn'     => false,
                'is_credit_consumer' => true,
                'is_balance_consume' => true,
                'application_driven' => true,
                'source_type_name'   => htmlspecialchars($source_name, ENT_QUOTES),
                'allotted'           => $earned_total,        // total earned (OD pool)
                'used_approved'      => $consumed_approved,
                'used_pending'       => $consumed_pending,
                'available'          => $available_credit,
                'note'               => $note,
            ]);
            return;
        }

        // --- Allotment-based or pure credit-earner (OD, CL, ML) ---
        $allot_row = $this->db
            ->where('staff_id', $staff_id)
            ->where('leave_type_id', $leave_type_id)
            ->limit(1)
            ->get('staff_leave_details')
            ->row_array();
        $allotted = $allot_row ? (float)($allot_row['alloted_leave'] ?? 0) : 0.0;

        $used_approved = (float)($this->db
            ->select('SUM(leave_days) as total', false)
            ->where('staff_id', $staff_id)
            ->where('leave_type_id', $leave_type_id)
            ->where_in('status', ['approve', 'approved'])
            ->get('staff_leave_request')
            ->row_array()['total'] ?? 0);

        $used_pending = (float)($this->db
            ->select('SUM(leave_days) as total', false)
            ->where('staff_id', $staff_id)
            ->where('leave_type_id', $leave_type_id)
            ->where_in('status', ['pending', 'recommended'])
            ->get('staff_leave_request')
            ->row_array()['total'] ?? 0);

        $available = max(0, $allotted - $used_approved - $used_pending);

        $application_driven = !$is_lop && $requires_balance_check
            && (int)($this->sch_setting_detail->auto_adjust_lop_with_preallotted_leaves ?? 0) === 0;

        if ($is_lop) {
            $note = 'This is a Loss of Pay leave type. Apply only when unable to use paid leave.';
        } elseif ($is_movement_credit) {
            $note = 'This is an <strong>On Duty / movement</strong> type. '
                . 'Approved requests on non-present days may be counted for payroll LOP adjustment. '
                . 'OD approved on a normal Present working day is kept only for audit and is not used for LOP adjustment.';
        } elseif ($is_claim_based) {
            $note = 'This is a <strong>claim-based leave</strong> type. '
                . 'It is applied and tracked as its own leave bucket and is not shown as On Duty credit.';
        } elseif ($application_driven) {
            $note = 'Balance will be <strong>deducted immediately upon approval</strong>. '
                . 'Pending requests are reserved (not yet deducted).';
        } else {
            $note = 'LOP days are auto-adjusted against available balance at payroll time.';
        }

        echo json_encode([
            'status'             => 'success',
            'type_label'         => htmlspecialchars((string)($leave_type['type'] ?? ''), ENT_QUOTES),
            'is_lop'             => $is_lop,
            'is_credit_earn'     => $is_movement_credit,
            'is_claim_based'     => $is_claim_based && !$is_movement_credit,
            'is_credit_consumer' => false,
            'is_balance_consume' => !$is_lop && $requires_balance_check,
            'application_driven' => $application_driven,
            'allotted'           => $allotted,
            'used_approved'      => $used_approved,
            'used_pending'       => $used_pending,
            'available'          => $available,
            'note'               => $note,
        ]);
    }

    public function permissionQuota()
    {
        $staff_id = (int) $this->input->post('staff_id');
        $leave_type_id = (int) $this->input->post('leave_type_id');
        $leave_from_date = $this->input->post('leave_from_date');

        if ($staff_id <= 0 || $leave_type_id <= 0) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
            return;
        }

        $leave_type = $this->staff_model->getLeaveType($leave_type_id);
        $is_permission = !empty($leave_type) && isset($leave_type['type']) && strtolower(trim($leave_type['type'])) === 'permission';

        if (!$is_permission) {
            echo json_encode([
                'status' => 'success',
                'is_permission' => false,
                'quota' => 0,
                'used' => 0,
                'remaining' => 0,
            ]);
            return;
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        if (!empty($leave_from_date)) {
            $parsed = $this->customlib->dateFormatToYYYYMMDD($leave_from_date);
            if (!empty($parsed)) {
                $start_date = date('Y-m-01', strtotime($parsed));
                $end_date = date('Y-m-t', strtotime($parsed));
            }
        }

        $quota = isset($this->sch_setting_detail->max_permission_allowed) && (int) $this->sch_setting_detail->max_permission_allowed > 0
            ? (int) $this->sch_setting_detail->max_permission_allowed
            : 2;

        $used = (float) $this->leaverequest_model->countLeaveDaysInRange($staff_id, $leave_type_id, $start_date, $end_date);
        $remaining = max(0, $quota - $used);

        echo json_encode([
            'status' => 'success',
            'is_permission' => true,
            'quota' => $quota,
            'used' => $used,
            'remaining' => $remaining,
        ]);
    }

    public function leaveStatus()
    {
        $leave_request_id = $this->input->post("leave_request_id");
        $status           = $this->input->post("status");
        $remark           = $this->input->post("detailremark");
        $success_message  = 'Leave request status updated successfully.';
        
        $current_user_id = $this->customlib->getStaffID();
        $leave_request = $this->leaverequest_model->get_staff_leave($leave_request_id);

        $data = [];
        $is_recommender = ($leave_request['recommender_id'] == $current_user_id);
        $is_approver = ($leave_request['approver_id'] == $current_user_id);
        $is_admin_override = $this->currentUserIsAdminOrSuperAdmin() || $this->rbac->hasPrivilege('approve_leave_request', 'can_edit');

        if (!$is_recommender && !$is_approver && !$is_admin_override) {
            $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('unauthorized_action'));
            echo json_encode($array);
            return;
        }

        // For admin/super-admin override, be stage-aware instead of always bypassing both stages.
        // If the request is still in pre-recommender stage (recommender_status = 'pending'),
        // treat the admin as the recommender so the two-stage workflow is preserved.
        // Only bypass to a direct finalization when the recommender stage is already done.
        if ($is_admin_override) {
            $recommender_done = in_array((string) ($leave_request['recommender_status'] ?? ''), ['recommended', 'approved', 'rejected'], true);
            if (!$recommender_done) {
                // Admin acts as recommender for this pre-recommender-stage request.
                $is_recommender = true;
                $is_approver    = false;
                $is_admin_override = false;
            } else {
                // Recommender stage already done; admin can finalize directly.
                $is_recommender = false;
                $is_approver    = false;
            }
        }

        if ($is_recommender) {
            // Map form status to DB ENUM for recommender
            if ($status == 'approved') {
                $data['recommender_status'] = 'recommended';
                $success_message = 'Leave successfully recommended for approval.';
            } elseif ($status == 'disapproved') {
                $data['recommender_status'] = 'rejected';
                $success_message = 'Leave request rejected at recommender level.';
            } else {
                $data['recommender_status'] = $status;
                $success_message = 'Leave recommendation updated successfully.';
            }
            
            $data['recommender_remark'] = $remark;
            $data['recommender_action_date'] = date('Y-m-d');
            
            if ($status == 'disapproved') {
                $data['status'] = 'disapproved';
            } elseif ($status == 'approved' && $leave_request['status'] == 'pending') {
                // If recommender approves, and overall status is still pending, mark it as recommended
                $data['status'] = 'recommended';
                
                // Send notification to approver
                $approver_id = $leave_request['approver_id'];
                if ($approver_id) {
                    $approver_details = $this->staff_model->get($approver_id);
                    $applicant_details = $this->staff_model->get($leave_request['staff_id']);
                    if ($approver_details && isset($approver_details['email'])) {
                        $message_to_approver = "Dear " . $approver_details['name'] . ",<br><br>A leave request from " . $applicant_details['name'] . " " . $applicant_details['surname'] . " has been recommended and is awaiting your final approval.<br><br>Thank you.";
                        $this->mailer->send_mail($approver_details['email'], 'Leave Request Recommended for Approval', $message_to_approver);
                    }
                }
            }
        } elseif ($is_approver) {
            if ($leave_request['recommender_status'] == 'approved' || $leave_request['recommender_status'] == 'recommended') {
                // Map form status to DB ENUM for approver
                if ($status == 'disapproved') {
                    $data['approver_status'] = 'rejected';
                    $success_message = 'Leave request disapproved successfully.';
                } else {
                    $data['approver_status'] = $status;
                    if ($status == 'approved') {
                        $success_message = 'Leave request approved successfully.';
                    } else {
                        $success_message = 'Leave approval status updated successfully.';
                    }
                }
                
                $data['approver_remark'] = $remark;
                $data['approver_action_date'] = date('Y-m-d');
                $data['status'] = $status; // Final status
                if ($status == 'approved') {
                    $data['approve_date'] = date('Y-m-d');
                } else {
                    $data['approve_date'] = null;
                }
            } else {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('recommender_approval_pending'));
                echo json_encode($array);
                return;
            }
        } else {
            // Privileged managers can finalize the request directly.
            if ($status == 'approved' || $status == 'disapproved') {
                if (!in_array((string) $leave_request['recommender_status'], ['approved', 'recommended', 'rejected'], true)) {
                    $data['recommender_status'] = ($status === 'approved') ? 'recommended' : 'rejected';
                    $data['recommender_remark'] = $remark;
                    $data['recommender_action_date'] = date('Y-m-d');
                }

                $data['approver_status'] = ($status == 'disapproved') ? 'rejected' : 'approved';
                $data['approver_remark'] = $remark;
                $data['approver_action_date'] = date('Y-m-d');
                $data['status'] = $status;
                $data['approve_date'] = ($status == 'approved') ? date('Y-m-d') : null;
                $data['admin_remark'] = $remark;
                $success_message = ($status == 'approved')
                    ? 'Leave request approved successfully.'
                    : 'Leave request disapproved successfully.';
            } else {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('invalid_status'));
                echo json_encode($array);
                return;
            }
        }

        if (!empty($data)) {
            if ($leave_request['status'] == 'approved' || $leave_request['status'] == 'disapproved') {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('finalized_record_cannot_be_modified'));
                echo json_encode($array);
                return;
            }
            $update_result = $this->leaverequest_model->changeLeaveStatus($data, $leave_request_id);

            // Day-lock: always clear any existing lock for this request first (handles
            // edit/disapprove/re-approve cycles safely), then re-lock if newly approved.
            $this->day_status_model->deleteDayLock($leave_request_id);

            // Log audit credit for OD/CPL-type leaves at the moment of final approval
            if (isset($data['status']) && $data['status'] === 'approved') {
                $this->leaverequest_model->logLeaveApprovalCredit($leave_request_id, $current_user_id);
                $this->day_status_model->writeDayLock($leave_request_id);
            }

            // Send notification to applicant on final decision
            if (($is_approver || $is_admin_override) && ($status == 'approved' || $status == 'disapproved')) {
                $applicant_details = $this->staff_model->get($leave_request['staff_id']);
                $message_to_applicant = "Dear " . $applicant_details['name'] . ",<br><br>Your leave request has been " . $status . ".<br><br>Thank you.";
                $this->mailer->send_mail($applicant_details['email'], 'Leave Request Status Updated', $message_to_applicant);
            }

            $array = array('status' => 'success', 'error' => '', 'message' => $success_message);
            echo json_encode($array);
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('unauthorized_action'));
            echo json_encode($array);
        }
    }

    /**
     * AJAX: Revert an approved leave request back to pending and restore the balance.
     * Accessible by: admin, super admin, or the configured approver for this request.
     */
    public function revertLeave()
    {
        $leave_request_id = (int) $this->input->post('leave_request_id');
        if ($leave_request_id <= 0) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid request.']);
            return;
        }

        $current_user_id   = (int) $this->customlib->getStaffID();
        $leave_request     = $this->leaverequest_model->get_staff_leave($leave_request_id);

        if (empty($leave_request)) {
            echo json_encode(['status' => 'fail', 'message' => 'Leave request not found.']);
            return;
        }

        if ((string)($leave_request['status'] ?? '') !== 'approved') {
            echo json_encode(['status' => 'fail', 'message' => 'Only approved leave requests can be reverted.']);
            return;
        }

        $is_admin         = $this->currentUserIsAdminOrSuperAdmin();
        $is_approver      = ((int)($leave_request['approver_id'] ?? 0) === $current_user_id);
        $has_edit_priv    = $this->rbac->hasPrivilege('approve_leave_request', 'can_edit');

        if (!$is_admin && !$is_approver && !$has_edit_priv) {
            echo json_encode(['status' => 'fail', 'message' => $this->lang->line('unauthorized_action')]);
            return;
        }

        // HOD: block revert if the CPL credit it generated has already been consumed
        $lr_type_row  = $this->db->select('type')->where('id', (int)($leave_request['leave_type_id'] ?? 0))->limit(1)->get('leave_types')->row_array();
        $lr_type_name = strtolower(trim((string)($lr_type_row['type'] ?? '')));
        if (in_array($lr_type_name, ['hod', 'holiday on duty', 'holiday od'], true)) {
            $cpl_type_row = $this->db->query("SELECT id FROM leave_types WHERE LOWER(type) = 'cpl' LIMIT 1")->row_array();
            if (!empty($cpl_type_row)) {
                $cpl_approved_count = (int) $this->db
                    ->where('staff_id', (int)$leave_request['staff_id'])
                    ->where('leave_type_id', (int)$cpl_type_row['id'])
                    ->where_in('status', ['approved', 'approve'])
                    ->count_all_results('staff_leave_request');
                if ($cpl_approved_count > 0) {
                    echo json_encode(['status' => 'fail', 'message' => 'Cannot revert this HOD leave — the CPL credit it generated has already been used in an approved CPL request. Please disapprove those CPL leaves first.']);
                    return;
                }
            }
        }

        // Revert balance
        $this->leaverequest_model->revertLeaveApproval($leave_request_id, $current_user_id);

        // Remove day-lock records so biometric attendance is used again for payroll
        $this->day_status_model->deleteDayLock($leave_request_id);

        // Reset the leave request to pending
        $reset_data = [
            'status'               => 'pending',
            'approve_date'         => null,
            'approver_status'      => 'pending',
            'approver_remark'      => null,
            'approver_action_date' => null,
            'recommender_status'   => 'pending',
            'recommender_remark'   => null,
            'recommender_action_date' => null,
            'admin_remark'         => null,
        ];
        $this->leaverequest_model->changeLeaveStatus($reset_data, $leave_request_id);

        echo json_encode(['status' => 'success', 'message' => 'Leave approval has been reverted. The request is now pending again and the leave balance has been restored.']);
    }

    public function remove($id, $staff_id)
    {
        $current_user_id = (int) $this->customlib->getStaffID();
        $is_admin_override = $this->currentUserIsAdminOrSuperAdmin() || $this->rbac->hasPrivilege('approve_leave_request', 'can_delete');
        $row = $this->leaverequest_model->get_staff_leave($id);

        if (empty($row)) {
            return;
        }

        $is_owner = ((int) ($row['staff_id'] ?? 0) === $current_user_id) || ((int) ($row['applied_by'] ?? 0) === $current_user_id);
        $is_pre_recommender_stage = ((string) ($row['status'] ?? '') === 'pending')
            && in_array((string) ($row['recommender_status'] ?? ''), ['', 'pending'], true)
            && in_array((string) ($row['approver_status'] ?? ''), ['', 'pending'], true);
        $is_recommended_stage = (in_array((string) ($row['status'] ?? ''), ['pending', 'recommended'], true)
            || (string) ($row['recommender_status'] ?? '') === 'recommended')
            && in_array((string) ($row['approver_status'] ?? ''), ['', 'pending'], true);
        $can_delete_stage = $is_pre_recommender_stage || $is_recommended_stage;

        if (($is_owner || $is_admin_override) && $can_delete_stage) {
            $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
            if ($row['document_file'] != '') {
                $this->media_storage->filedelete($row['document_file'], $uploaddir);
            }
            $this->leaverequest_model->leave_remove($id);
        }
    }

    public function leaveRecord()
    {
        $id                   = $this->input->post("id");
        $result               = $this->staff_model->getLeaveRecord($id);
        if (!isset($result->leave_duration_type) || empty($result->leave_duration_type)) {
            $result->leave_duration_type = 'full_day';
        }

        $is_pre_recommender_stage = ((string) ($result->status ?? '') === 'pending')
            && in_array((string) ($result->recommender_status ?? ''), ['', 'pending'], true)
            && in_array((string) ($result->approver_status ?? ''), ['', 'pending'], true);

        // Self-Healing: Resolve recommender routing for pending requests when missing or stale.
        if ($is_pre_recommender_stage) {
            $routing = $this->resolveRecommenderApproverIds((int) $result->staff_id, 0);
            $new_recommender_id = (int) ($routing['recommender_id'] ?? 0);
            $current_recommender_id = (int) ($result->recommender_id ?? 0);

            if (!empty($new_recommender_id) && $new_recommender_id !== $current_recommender_id) {
                // Update the database
                $this->db->where('id', $id)->update('staff_leave_request', ['recommender_id' => $new_recommender_id]);
                // Update the result object so the UI works immediately
                $result->recommender_id = $new_recommender_id;
                $recommender = $this->staff_model->get($new_recommender_id); // Fetch new details for UI
                if ($recommender) {
                    $result->recommender_name = $recommender['name'];
                    $result->recommender_surname = $recommender['surname'];
                }
            }
        }

        $leave_from           = date("m/d/Y", strtotime($result->leave_from));
        $result->leavefrom    = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result->leave_from));
        $result->date         = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result->date));
        $leave_to             = date("m/d/Y", strtotime($result->leave_to));
        $result->leaveto      = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result->leave_to));
        $result->days         = $this->dateDifference($leave_from, $leave_to);
        $result->leave_status = $this->lang->line($result->status);
        
        // Get recommender and approver names
        if ($result->recommender_id) {
            $recommender = $this->staff_model->get($result->recommender_id);
            $result->recommender_name = $recommender['name'];
            $result->recommender_surname = $recommender['surname'];
        }
        if ($result->approver_id) {
            $approver = $this->staff_model->get($result->approver_id);
            $result->approver_name = $approver['name'];
            $result->approver_surname = $approver['surname'];
        }

        if ($result->recommender_status) {
            $result->recommender_status_text = $this->lang->line(strtolower($result->recommender_status));
        }
        if ($result->approver_status) {
            $result->approver_status_text = $this->lang->line(strtolower($result->approver_status));
        }

        if ($result->alternative_teacher_id) {
            $alt_teacher = $this->staff_model->get($result->alternative_teacher_id);
            if ($alt_teacher) {
                $result->alternative_teacher_name = $alt_teacher['name'];
                $result->alternative_teacher_surname = $alt_teacher['surname'];
                $result->alternative_teacher_employee_id = $alt_teacher['employee_id'];
            }
        }

        $result->substitutions = $this->leaverequest_model->getLeaveSubstitutions($id);

        echo json_encode($result);
    }

    public function dateDifference($date_1, $date_2, $differenceFormat = '%a')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval  = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat) + 1;
    }

    private function calculateLeaveDays($leavefrom, $leaveto, $leave_duration_type)
    {
        $leave_duration_type = strtolower(trim((string) $leave_duration_type));
        if (in_array($leave_duration_type, ['first_half', 'second_half'], true)) {
            return 0.5;
        }

        return (float) $this->dateDifference($leavefrom, $leaveto);
    }

        public function addLeave()
        {
            $request_type = $this->input->post('request_type') ?: 'claim_leave';
            $role         = $this->input->post("role");
            $empid        = $this->input->post("empname");
            $leavetype    = $this->input->post("leave_type");
            $reason       = $this->input->post("reason");
            $remark       = $this->input->post("remark");
            $status       = $this->input->post("addstatus");
            $request_id   = $this->input->post("leaverequestid");
            $applied_date = $this->input->post("applieddate");
            $server_apply_date = date('Y-m-d');
            $leave_duration_type = strtolower(trim((string) $this->input->post('leave_duration_type')));
            if (!in_array($leave_duration_type, ['full_day', 'first_half', 'second_half'], true)) {
                $leave_duration_type = 'full_day';
            }
            $has_duration_column  = $this->db->field_exists('leave_duration_type', 'staff_leave_request');
            $has_direction_column = $this->db->field_exists('leave_direction', 'staff_leave_request');
            
            $leavefrom = "";
            $leaveto = "";
            if ($this->input->post('leave_from_date') && $this->input->post('leave_to_date')) {
                $leavefrom = (string) $this->resolvePostedLeaveDateToYmd('leave_from_date', 'leave_from_date_iso');
                $leaveto = (string) $this->resolvePostedLeaveDateToYmd('leave_to_date', 'leave_to_date_iso');
            }

            if (($this->input->post('leave_from_date') && empty($leavefrom)) || ($this->input->post('leave_to_date') && empty($leaveto))) {
                $msg = array(
                    'leave_from_date' => 'Invalid date format. Please use ' . $this->customlib->getSchoolDateFormat() . ' (example: 04/02/2026 for 4-Feb-2026).',
                );
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                echo json_encode($array);
                return;
            }
            
            $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('empname', $this->lang->line('name'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('applieddate', $this->lang->line('applied_date'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('leave_from_date', $this->lang->line('leave_from_date'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('leave_to_date', $this->lang->line('leave_to_date'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('leave_type', $this->lang->line('available_leave'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('leave_type', $this->lang->line('leave_type'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('leave_duration_type', 'Leave Duration', 'trim|xss_clean');
            $this->form_validation->set_rules('userfile', $this->lang->line('file'), 'callback_handle_upload[userfile]');
            $this->form_validation->set_rules('alternative_teacher_id', $this->lang->line('alternative_teacher'), 'trim|xss_clean');

            $policy = $this->getLeaveManagementPolicy();
            $requires_substitution = $this->isSubstitutionRequiredByPolicy((int) $role, (int) $leavetype, $policy);
            if ($leavefrom != "" && $leaveto != "" && $requires_substitution) {
                $this->form_validation->set_rules('validate_substitutes_flag', 'Substitute Teachers', 'callback_validate_substitutes[' . $leavefrom . ',' . $leaveto . ']');
            }
            $this->form_validation->set_rules('reason', $this->lang->line('reason'), 'trim|xss_clean'); // Added rule for cleaning, but not required
    
            if ($this->form_validation->run() == false) {
    
                $msg = array(
                    'role'            => form_error('role'),
                    'empname'         => form_error('empname'),
                    'applieddate'     => form_error('applieddate'),
                    'leavedates'      => form_error('leavedates'),
                    'leave_type'      => form_error('leave_type'),
                    'leave_from_date' => form_error('leave_from_date'),
                    'leave_to_date'   => form_error('leave_to_date'),
                    'leave_duration_type' => form_error('leave_duration_type'),
                    'userfile'        => form_error('userfile'),
                    'alternative_teacher_id' => form_error('alternative_teacher_id'),
                    'validate_substitutes_flag' => form_error('validate_substitutes_flag'),
                );
    
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            } else {

                if (!empty($leavefrom) && !empty($leaveto) && $leavefrom > $leaveto) {
                    $msg = array(
                        'leave_to_date' => 'Leave To Date must be same as or later than Leave From Date.',
                    );
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                    echo json_encode($array);
                    return;
                }

                if (in_array($leave_duration_type, ['first_half', 'second_half'], true)) {
                    if (!$has_duration_column) {
                        $msg = array(
                            'leave_duration_type' => 'Half-day leave requires latest database update. Please run DB update script first.',
                        );
                        $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                        echo json_encode($array);
                        return;
                    }

                    if (!$this->canApplyHalfDayByPolicy((int) $role, (int) $leavetype, $policy)) {
                        $msg = array(
                            'leave_duration_type' => 'Half-day leave is not enabled for the selected role/leave type.',
                        );
                        $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                        echo json_encode($array);
                        return;
                    }

                    if ($leavefrom !== $leaveto) {
                        $msg = array(
                            'leave_to_date' => 'Half-day leave can only be applied for a single date.',
                        );
                        $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                        echo json_encode($array);
                        return;
                    }
                }
    
                $alternative_teacher_id = $this->input->post('alternative_teacher_id');
                $applied_by   = $this->customlib->getStaffID();
                $leave_days   = $this->calculateLeaveDays($leavefrom, $leaveto, $leave_duration_type);
                $staff_id     = $empid;

                                if ($leave_duration_type === 'full_day') {
                                    $half_day_conflict = $this->getHalfDayAttendanceConflictDate($staff_id, $leavefrom, $leaveto);
                                    if (!empty($half_day_conflict)) {
                                        $conflict_date = date($this->customlib->getSchoolDateFormat(), strtotime($half_day_conflict['date']));
                                        $msg = array(
                                            'leave_duration_type' => 'Full-day leave is not allowed on a half-day attendance date. Please apply half-day leave for ' . $conflict_date . '.',
                                        );
                                        $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                                        echo json_encode($array);
                                        return;
                                    }
                                }

                                // Day-type restriction guard (OD on holidays / CPL on working days)
                                if ($this->db->field_exists('day_type_restriction', 'leave_types')) {
                                    $leave_type_for_restriction = $this->staff_model->getLeaveType($leavetype);
                                    $day_restriction = isset($leave_type_for_restriction['day_type_restriction'])
                                        ? $leave_type_for_restriction['day_type_restriction'] : null;
                                    if (!empty($day_restriction)) {
                                        $day_violation = $this->checkDayTypeViolation($leavefrom, $leaveto, $day_restriction);
                                        if ($day_violation !== null) {
                                            $array = ['status' => 'fail', 'error' => ['leave_from_date' => $day_violation], 'message' => ''];
                                            echo json_encode($array);
                                            return;
                                        }
                                    }
                                }

                                $leave_type_details = $this->staff_model->getLeaveType($leavetype);
                                $is_lop_leave = (isset($leave_type_details['is_lop']) && $leave_type_details['is_lop'] == 1);
                                $requires_balance_check = $this->leaveTypeRequiresBalanceCheck($leave_type_details);
                                $credit_source_type_id_apply = $this->leaveTypeCreditSourceId($leave_type_details);
                                $is_credit_consumer_apply = $credit_source_type_id_apply !== null;
                                // LOP types (is_lop=1) are always allowed — admin/staff may apply retroactively
                                // even when attendance is already marked Present (e.g. ML after auto-present).
                                // OD and CPL-earner types (requires_balance_check=0, not credit-consumer) are also
                                // exempt because OD is explicitly claimable on Present days.
                                $allow_present_day_application = $is_lop_leave || (!$requires_balance_check && !$is_credit_consumer_apply);
                                $present_conflict = $this->getPresentAttendanceConflictDate($staff_id, $leavefrom, $leaveto);
                                if (!empty($present_conflict) && !$allow_present_day_application) {
                                    $conflict_date = date($this->customlib->getSchoolDateFormat(), strtotime($present_conflict['date']));
                                    $msg = array(
                                        'leave_from_date' => 'Leave cannot be applied on a day marked Present. Attendance is already marked Present on ' . $conflict_date . '.',
                                    );
                                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                                    echo json_encode($array);
                                    return;
                                }

                                // Credit-pool balance check for CPL / credit_consumer types
                                if ($is_credit_consumer_apply) {
                                    $pool_available = $this->getAvailableCreditPoolBalance($staff_id, $credit_source_type_id_apply);
                                    if ($pool_available < $leave_days) {
                                        $src = $this->staff_model->getLeaveType($credit_source_type_id_apply);
                                        $src_name = $src['type'] ?? 'OD';
                                        $msg = array(
                                            'leave_type' => 'Insufficient ' . $src_name . ' credit. Available: ' . $pool_available . ' day(s), Requested: ' . $leave_days . ' day(s). Apply for ' . $src_name . ' first to earn credit.',
                                        );
                                        $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                                        echo json_encode($array);
                                        return;
                                    }
                                }

                                // Apply Leave (debit): credit-earner types (OD, CPL-earner) have no HR allotment
                                // row — their available balance lives in staff_monthly_leave_balance.closing_balance.
                                // If is_lop=0 and balance is insufficient, block submission outright.
                                if ($request_type === 'apply_leave' && !$is_lop_leave && !$is_credit_consumer_apply && !$requires_balance_check) {
                                    $bal_month = (int) date('m', strtotime($leavefrom));
                                    $bal_year  = (int) date('Y', strtotime($leavefrom));
                                    $mlb_row = $this->db
                                        ->where('staff_id', $staff_id)
                                        ->where('leave_type_id', $leavetype)
                                        ->where('month', $bal_month)
                                        ->where('year', $bal_year)
                                        ->limit(1)
                                        ->get('staff_monthly_leave_balance')
                                        ->row_array();
                                    $available_balance = !empty($mlb_row) ? max(0.0, (float)($mlb_row['closing_balance'] ?? 0)) : 0.0;
                                    if ($available_balance < $leave_days) {
                                        $type_name = $leave_type_details['type'] ?? 'leave';
                                        $msg = array(
                                            'leave_type' => 'Insufficient ' . $type_name . ' balance. Available: ' . $available_balance . ' day(s), Requested: ' . $leave_days . ' day(s). Please claim ' . $type_name . ' days first.',
                                        );
                                        $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                                        echo json_encode($array);
                                        return;
                                    }
                                }

                
                            $my_laeve     = $this->leaverequest_model->myallotedLeaveType($staff_id, $leavetype);
                            $alloted_leave = (float) (isset($my_laeve['alloted_leave']) && $my_laeve['alloted_leave'] !== '' ? $my_laeve['alloted_leave'] : 0);
                            $total_applied = (float) (isset($my_laeve['total_applied']) && $my_laeve['total_applied'] !== null ? $my_laeve['total_applied'] : 0);
                            $total_remain = $alloted_leave - $total_applied;
                
                                if (!$requires_balance_check || $is_lop_leave || $is_credit_consumer_apply || $total_remain >= $leave_days) {
                            if (isset($_FILES["userfile"]) && !empty($_FILES['userfile']['name'])) {
                        $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
                        $this->customlib->ensureDirectoryExists($uploaddir);
                        $document = $this->media_storage->fileupload("userfile", $uploaddir);
                    } else {
                        $document = '';
                    }
    				
    					if($status == 'approved'){
    						$approve_date = date('Y-m-d');
    					}else{
    						$approve_date = null;
    					}	
    					
                        // Determine Recommender and Approver
                        $routing = $this->resolveRecommenderApproverIds($staff_id, (int) $role);
                        $recommender_id = (int) ($routing['recommender_id'] ?? 0);
                        $approver_id = (int) ($routing['approver_id'] ?? 0);
    
					if (!empty($request_id)) {				 

                        $existing_leave = $this->leaverequest_model->get_staff_leave($request_id);
                        if (empty($existing_leave)) {
                            $array = array('status' => 'fail', 'error' => array('message' => 'Leave request not found.'), 'message' => '');
                            echo json_encode($array);
                            return;
                        }

                        $current_user_id = (int) $this->customlib->getStaffID();
                        $is_admin_override = $this->currentUserIsAdminOrSuperAdmin() || $this->rbac->hasPrivilege('approve_leave_request', 'can_edit');
                        $is_owner = ((int) ($existing_leave['staff_id'] ?? 0) === $current_user_id)
                            || ((int) ($existing_leave['applied_by'] ?? 0) === $current_user_id);
                        $is_pre_recommender_stage = ((string) ($existing_leave['status'] ?? '') === 'pending')
                            && in_array((string) ($existing_leave['recommender_status'] ?? ''), ['', 'pending'], true)
                            && in_array((string) ($existing_leave['approver_status'] ?? ''), ['', 'pending'], true);

                        if (!(($is_owner || $is_admin_override) && $is_pre_recommender_stage)) {
                            $array = array('status' => 'fail', 'error' => array('message' => 'This leave request can only be edited before recommender action.'), 'message' => '');
                            echo json_encode($array);
                            return;
                        }

                        $persisted_apply_date = !empty($existing_leave['date']) ? $existing_leave['date'] : $server_apply_date;
    					 
                        $data = array(
                            'id'              => $request_id,
                            'staff_id'        => $staff_id,
                            'date'            => $persisted_apply_date,
                            'leave_type_id'   => $leavetype,
                            'leave_days'      => $leave_days,
                            'leave_duration_type' => $leave_duration_type,
                            'leave_direction' => ($request_type === 'apply_leave') ? 'debit' : 'credit',
                            'leave_from'      => $leavefrom,
                            'leave_to'        => $leaveto,
                            'employee_remark' => $reason,
                            'status'          => $status,
                            'admin_remark'    => $remark,
                            'applied_by'      => $applied_by,
                            'document_file'   => $document,
                            'approve_date'   => $approve_date,
                            'recommender_id' => $recommender_id,
                            'approver_id' => $approver_id,
                            'recommender_status' => 'pending', // Initial status
                            'alternative_teacher_id' => $alternative_teacher_id,
                        );
    					
                    } else {
    					 
                            $data = array('staff_id' => $staff_id, 'date' => $server_apply_date, 'leave_days' => $leave_days, 'leave_duration_type' => $leave_duration_type, 'leave_direction' => ($request_type === 'apply_leave') ? 'debit' : 'credit', 'leave_type_id' => $leavetype, 'leave_from' => $leavefrom, 'leave_to' => $leaveto, 'employee_remark' => $reason, 'status' => $status, 'admin_remark' => $remark, 'applied_by' => $applied_by, 'document_file' => $document, 'approve_date' => $approve_date,
                            'recommender_id' => $recommender_id,
                            'approver_id' => $approver_id,
                            'recommender_status' => 'pending', // Initial status
                            'alternative_teacher_id' => $alternative_teacher_id,
                        );
                    }

                    if (!$has_duration_column && isset($data['leave_duration_type'])) {
                        unset($data['leave_duration_type']);
                    }
                    if (!$has_direction_column && isset($data['leave_direction'])) {
                        unset($data['leave_direction']);
                    }
    
                    $result = $this->leaverequest_model->addLeaveRequest($data);
    
                    if ($result === false) {

                        $entered_from = (string) $this->input->post('leave_from_date');
                        $entered_to = (string) $this->input->post('leave_to_date');
                        $parsed_from = !empty($leavefrom) ? date($this->customlib->getSchoolDateFormat(), strtotime($leavefrom)) : '';
                        $parsed_to = !empty($leaveto) ? date($this->customlib->getSchoolDateFormat(), strtotime($leaveto)) : '';

                        $duplicate_message = 'Leave already applied for this date/date range.';
                        if ($entered_from !== '' || $entered_to !== '') {
                            $duplicate_message .= ' Entered: ' . $entered_from;
                            if ($entered_to !== '' && $entered_to !== $entered_from) {
                                $duplicate_message .= ' to ' . $entered_to;
                            }
                            $duplicate_message .= '.';
                        }
                        if ($parsed_from !== '' || $parsed_to !== '') {
                            $duplicate_message .= ' Parsed as: ' . $parsed_from;
                            if ($parsed_to !== '' && $parsed_to !== $parsed_from) {
                                $duplicate_message .= ' to ' . $parsed_to;
                            }
                            $duplicate_message .= '.';
                        }
                        $duplicate_message .= ' Please use date format ' . $this->customlib->getSchoolDateFormat() . ' (for example: 04/02/2026 for 4-Feb-2026).';

                        $array = array('status' => 'fail', 'error' => array('message' => $duplicate_message), 'message' => '');
    
                        echo json_encode($array);
    
                        return;
    
                    }
    
                    $leave_request_id = !empty($request_id) ? $request_id : $result;                    // Process and save substitution data
                    $substitutions_data = [];
                    foreach ($this->input->post() as $key => $value) {
                        if (strpos($key, 'substitute_') === 0 && !empty($value)) {
                            $parts = explode('_', $key);
                            $date_part = $parts[1];
                            $time_from_part = $parts[2];
                            $time_to_part = $parts[3];
    
                            $substitutions_data[] = [
                                'substitute_staff_id' => $value,
                                'date' => $date_part,
                                'period' => $time_from_part . '-' . $time_to_part
                            ];
                        }
                    }
                    if (!empty($substitutions_data)) {
                        $this->leaverequest_model->addLeaveSubstitutions($leave_request_id, $substitutions_data);
                    }
    
                                    $display_from = !empty($leavefrom) ? date($this->customlib->getSchoolDateFormat(), strtotime($leavefrom)) : '';
                                    $display_to = !empty($leaveto) ? date($this->customlib->getSchoolDateFormat(), strtotime($leaveto)) : '';
                                    $display_range = $display_from;
                                    if (!empty($display_to) && $display_to !== $display_from) {
                                        $display_range .= ' to ' . $display_to;
                                    }

                                    $apply_success_message = !empty($request_id)
                                        ? 'Leave request updated and submitted for review successfully.'
                                        : 'Leave request submitted for review successfully.';
                                    if (!empty($display_range)) {
                                        $apply_success_message .= ' Applied dates: ' . $display_range . '.';
                                    }
                                    $array = array('status' => 'success', 'error' => '', 'message' => $apply_success_message);
                                } else {
                                    $msg = array(
                                        'applieddate' => "Application Failed: You do not have enough leave balance for this request. Please check your available leaves or contact HR.",
                                    );
                                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                                }    
            }
            echo json_encode($array);
        }        
            public function handle_upload($str, $var)
            {
        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {
            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES[$var]['tmp_name'])) {
                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_extension_error_uploading_image'));
                return false;
            }
            return true;
        }
        return true;
    }
            public function getTimetableAndSubstitutes()    
            {
                $staff_id        = $this->input->post('staff_id');
                $leave_from_date = $this->input->post('leave_from_date');
                $leave_to_date   = $this->input->post('leave_to_date');
                $role_id         = (int) $this->input->post('role_id');
                $leave_type_id   = (int) $this->input->post('leave_type_id');
                
                // Convert dates to Y-m-d for database model compatibility
                $leave_from_db = date("Y-m-d", $this->customlib->datetostrtotime($leave_from_date));
                $leave_to_db   = date("Y-m-d", $this->customlib->datetostrtotime($leave_to_date));

                $staff_details = $this->staff_model->get($staff_id);
                $timetable_html = '';
                $substitution_html = '';
                $status = 'fail';
                $message = $this->lang->line('no_timetable_found_for_this_period');

                $policy = $this->getLeaveManagementPolicy();
                $requires_substitution = $this->isSubstitutionRequiredByPolicy($role_id, $leave_type_id, $policy);
                if (!$requires_substitution) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => $this->lang->line('success_message'),
                        'timetable_html' => '',
                        'substitution_html' => '',
                    ]);
                    return;
                }
   
                if ($staff_details) {
                    $this->load->model('subjecttimetable_model');
                    $staff_timetable = $this->subjecttimetable_model->getStaffTimetable($staff_id, $leave_from_db, $leave_to_db);
                    $department_id = $this->getStaffDepartmentId($staff_id, $staff_details);
                    $potential_substitutes = $department_id > 0
                        ? $this->staff_model->getEmployeeByDepartment($department_id, $staff_id)
                        : [];

    
                    if (!empty($staff_timetable)) {
 
                        $timetable_html .= '<table class="table table-bordered table-striped">';
            $timetable_html .= '<thead><tr><th>' . $this->lang->line('date') . '</th><th>' . $this->lang->line('day') . '</th><th>' . $this->lang->line('class') . '</th><th>' . $this->lang->line('section') . '</th><th>' . $this->lang->line('subject') . '</th><th>' . $this->lang->line('time') . '</th><th>' . $this->lang->line('room_no') . '</th></tr></thead>';
               $timetable_html .= '<tbody>';
    
      
                        $substitution_html .= '<table class="table table-bordered table-striped">';
      
                        $substitution_html .= '<thead><tr><th>' . $this->lang->line('date') . '</th><th>' . $this->lang->line('class') . ' - ' . $this->lang->line('subject') . '</th><th>' . $this->lang->line('select_substitute') . '</th></tr></thead>';
   
                        $substitution_html .= '<tbody>';
 
                        foreach ($staff_timetable as $date => $daily_schedule) {
 
                            $day_name = date('l', strtotime($date));
 
                            if (!empty($daily_schedule)) {

                                foreach ($daily_schedule as $period) {
    
                                     $timetable_html .= '<tr>';
    

                                    $timetable_html .= '<td>' . $this->customlib->dateformat($date) . '</td>';
 
                                    $timetable_html .= '<td>' . $this->lang->line(strtolower($day_name)) . '</td>';
 
                                    $timetable_html .= '<td>' . $period->class . '</td>';
 
                                    $timetable_html .= '<td>' . $period->section . '</td>';
 
                                    $timetable_html .= '<td>' . $period->subject_name . ' (' . $period->subject_code . ')</td>';
 
                                    $timetable_html .= '<td>' . $period->time_from . ' - ' . $period->time_to . '</td>';
 
                                    $timetable_html .= '<td>' . $period->room_no . '</td>';

                                    $timetable_html .= '</tr>';
     
    
                                    // Substitution field generation
 
                                    $substitution_html .= '<tr>';
  
                                    $substitution_html .= '<td>' . $this->customlib->dateformat($date) . '</td>';
 
                                    $substitution_html .= '<td>' . $period->class . ' - ' . $period->subject_name . ' (' . $period->time_from . ' - ' . $period->time_to . ')</td>';
   
                                    $substitution_html .= '<td>';
                                    
                                    $substitution_html .= '<div class="form-group">';
                                    $substitution_html .= '<select name="substitute_' . $date . '_' . str_replace([' ', ':'], '_', $period->time_from) . '_' . str_replace([' ', ':'], '_', $period->time_to) . '" class="form-control" aria-label="Select substitute for ' . $period->class . ' - ' . $period->subject_name . ' from ' . $period->time_from . ' to ' . $period->time_to . '">';
    
 
                                    $substitution_html .= '<option value="">' . $this->lang->line('select_substitute') . '</option>';
    
    
    
                                    foreach ($potential_substitutes as $substitute) {
    
    
    
                                        $substitution_html .= '<option value="' . $substitute['id'] . '">' . $substitute['name'] . ' ' . $substitute['surname'] . ' (' . $substitute['employee_id'] . ')</option>';
 
                                    }
 
                                    $substitution_html .= '</select></div>';

                                    $substitution_html .= '</td></tr>';

                                }

                            } else {

                                $timetable_html .= '<tr><td colspan="7">' . $this->lang->line('no_classes_scheduled_for_this_day') . '</td></tr>';

                                $substitution_html .= '<tr><td colspan="3">' . $this->lang->line('no_substitutions_needed_for_this_day') . '</td></tr>';
 
                            }
     
                        }
 
                        $timetable_html .= '</tbody></table>';

                        $substitution_html .= '</tbody></table>';
 
                        $status = 'success';
 
                        $message = $this->lang->line('timetable_fetched_successfully');
 
                    } else {

                        $timetable_html = '<div class="alert alert-info">' . $this->lang->line('no_timetable_found_for_this_period') . '</div>';
 
                        $substitution_html = '<div class="alert alert-info">' . $this->lang->line('no_substitutions_needed_for_this_period') . '</div>';
 
                    }
    
 
                } else {
    
 
                    $message = $this->lang->line('staff_details_not_found');

                }
    
     
                echo json_encode(['status' => $status, 'message' => $message, 'timetable_html' => $timetable_html, 'substitution_html' => $substitution_html]);
 
            }

    
            public function getRecommenderApproverInfo()
 
            {

                $staff_id = (int) $this->input->post('staff_id');
                $selected_role_id = (int) $this->input->post('role_id');
 
                $recommender_info = $this->lang->line('not_assigned');

                $approver_info = $this->lang->line('not_assigned');
   
    
                $staff_details = $this->staff_model->get($staff_id);

                if ($staff_details) {
                    $routing = $this->resolveRecommenderApproverIds($staff_id, $selected_role_id);

                    if (!empty($routing['recommender_id'])) {
                        $recommender_details = $this->staff_model->get((int) $routing['recommender_id']);
                        if (!empty($recommender_details) && is_array($recommender_details)) {
                            $recommender_info = $recommender_details['name'] . ' ' . $recommender_details['surname'] . ' (' . $recommender_details['designation'] . ')';
                        }
                    }

                    if (!empty($routing['approver_id'])) {
                        $approver_details = $this->staff_model->get((int) $routing['approver_id']);
                        if (!empty($approver_details) && is_array($approver_details)) {
                            $approver_info = $approver_details['name'] . ' ' . $approver_details['surname'] . ' (' . $approver_details['designation'] . ')';
                        }
                    }
 
                }
 
                echo json_encode([
    
      
                    'status' => 'success',
     
    
                    'recommender_info' => $recommender_info,
       
    
                    'approver_info' => $approver_info,
                    'approver_configured' => ($approver_info !== $this->lang->line('not_assigned'))
    
    
    
                ]);
    
    
    
            }

    public function validate_substitutes($str, $params)
    {
        list($leave_from, $leave_to) = explode(',', $params);

        $staff_id = $this->input->post('empname'); // Assuming empname is the staff_id
        $role_id = (int) $this->input->post('role');
        $leave_type_id = (int) $this->input->post('leave_type');

        $policy = $this->getLeaveManagementPolicy();
        if (!$this->isSubstitutionRequiredByPolicy($role_id, $leave_type_id, $policy)) {
            return true;
        }

        $this->load->model('subjecttimetable_model');
        $staff_timetable = $this->subjecttimetable_model->getStaffTimetable($staff_id, $leave_from, $leave_to);

        if (!empty($staff_timetable)) {
            // There are scheduled classes, so substitutes are mandatory
            foreach ($staff_timetable as $date => $daily_schedule) {
                foreach ($daily_schedule as $period) {
                    $field_name = 'substitute_' . $date . '_' . str_replace([' ', ':'], '_', $period->time_from) . '_' . str_replace([' ', ':'], '_', $period->time_to);
                    $substitute_value = $this->input->post($field_name);

                    if (empty($substitute_value)) {
                        $this->form_validation->set_message('validate_substitutes', 'Substitute teacher is mandatory for all scheduled periods during the leave.');
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function recommender_leave_requests()
    {
        if ((int) $this->customlib->getStaffID() <= 0 || $this->sch_setting_detail->institution_type != 'college') {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff/leaverequest');

        $current_user_id = $this->customlib->getStaffID();
        if ($this->currentUserIsAdminOrSuperAdmin()) {
            $leave_requests_for_recommender = $this->leaverequest_model->get_all_recommender_pending_leave_requests();
        } else {
            $leave_requests_for_recommender = $this->leaverequest_model->get_recommender_pending_leave_requests($current_user_id);
        }
        
        $data["leave_request"] = $leave_requests_for_recommender;
        $data["status"] = $this->status; // Status array from payroll config
        $data['sch_setting_detail'] = $this->sch_setting_detail; // Pass sch_setting_detail to the view
        $data['is_admin_or_super_admin'] = $this->currentUserIsAdminOrSuperAdmin();

        $LeaveTypes            = $this->staff_model->getLeaveType();
        $data["leavetype"]     = $LeaveTypes;
        $staffRole             = $this->staff_model->getStaffRole();
        $data["staffrole"]     = $staffRole;

        $userdata              = $this->customlib->getUserData();
        $data['staff_id'] = $userdata['id'];
        $staff_details = $this->staff_model->get($userdata['id']);
        $data['current_staff_details'] = $staff_details;

        $potential_substitutes = [];
        $department_id = $this->getStaffDepartmentId((int) $userdata['id'], $staff_details);
        if ($department_id > 0) {
            $potential_substitutes = $this->staff_model->getEmployeeByDepartment($department_id, $current_user_id);
        }
        $data['potential_substitutes'] = $potential_substitutes;

        if ($department_id > 0) {
            $this->load->model('department_model');
            $department = $this->department_model->getDepartmentType($department_id);
            if ($department && $department['dept_head_id']) {
                $recommender_details = $this->staff_model->get($department['dept_head_id']);
                if ($recommender_details && is_array($recommender_details)) {
                    $data['recommender_info'] = $recommender_details['name'] . ' ' . $recommender_details['surname'] . ' (' . $recommender_details['designation'] . ')';
                } else {
                    $data['recommender_info'] = $this->lang->line('not_assigned');
                }
            } else {
                $data['recommender_info'] = $this->lang->line('not_assigned');
            }
        } else {
            $data['recommender_info'] = $this->lang->line('not_assigned');
        }

        $setting = $this->setting_model->getSetting();
        if ($setting && isset($setting->leave_approver_id)) {
            $approver_details = $this->staff_model->get($setting->leave_approver_id);
            if ($approver_details && is_array($approver_details)) {
                $data['approver_info'] = $approver_details['name'] . ' ' . $approver_details['surname'] . ' (' . $approver_details['designation'] . ')';
            } else {
                $data['approver_info'] = $this->lang->line('not_assigned');
            }
        } else {
            $data['approver_info'] = $this->lang->line('not_assigned');
        }

        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/staffleaverequest", $data);
        $this->load->view("layout/footer", $data);
    }
}