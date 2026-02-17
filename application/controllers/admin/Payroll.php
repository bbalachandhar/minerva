<?php

class Payroll extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        
        // Increase execution time for payroll operations (bulk processing may take longer)
        ini_set('max_execution_time', 600); // 10 minutes
        set_time_limit(600);
        ini_set('memory_limit', '512M');
        ini_set('upload_max_filesize', '50M');
        ini_set('post_max_size', '50M');
        
        $this->load->helper('file');
        $this->config->load("mailsms");
        $this->config->load("payroll");
        $this->load->library('mailsmsconf');
        $this->load->library('media_storage');
        $this->config_attendance = $this->config->item('attendence');
        $this->staff_attendance  = $this->config->item('staffattendance');
        $this->payment_mode      = $this->config->item('payment_mode');
        $this->load->model("payroll_model");
        $this->load->model("payroll_increment_model");
        $this->load->model("staff_model");
        $this->load->model('staffattendancemodel');
        $this->payroll_status     = $this->config->item('payroll_status');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {

        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');
        $data["staff_id"]            = "";
        $data["name"]                = "";
        $data["month"]               = date("F", strtotime("-1 month"));
        $data["year"]                = date("Y");
        $data["present"]             = 0;
        $data["absent"]              = 0;
        $data["late"]                = 0;
        $data["half_day"]            = 0;
        $data["holiday"]             = 0;
        $data["leave_count"]         = 0;
        $data["alloted_leave"]       = 0;
        $data["basic"]               = 0;
        $data["payment_mode"]        = $this->payment_mode;
        $user_type                   = $this->staff_model->getStaffRole();
        $data['classlist']           = $user_type;
        $data['monthlist']           = $this->customlib->getMonthDropdown();
        $data['sch_setting']         = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $submit                      = $this->input->post("search");
        if (isset($submit) && $submit == "search") {

            $month    = $this->input->post("month");
            $year     = $this->input->post("year");
            $emp_name = $this->input->post("name");
            $role     = $this->input->post("role");

            $searchEmployee = $this->payroll_model->searchEmployee($month, $year, $emp_name, $role);

            $data["resultlist"] = $searchEmployee;
            $data["name"]       = $emp_name;
            $data["month"]      = $month;
            $data["year"]       = $year;
        }

        $data["payroll_status"] = $this->payroll_status;
        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/stafflist", $data);
        $this->load->view("layout/footer", $data);
    }

    public function create($month, $year, $id)
    {       
        
        $data["staff_id"]            = "";
        $data["basic"]               = "";
        $data["name"]                = "";
        $data["month"]               = "";
        $data["year"]                = "";
        $data["present"]             = 0;
        $data["absent"]              = 0;
        $data["late"]                = 0;
        $data["half_day"]            = 0;
        $data["holiday"]             = 0; // This will store the "other leaves" count
        $data["sunday_count"]        = 0; // New variable for Sundays
        $data["leave_count"]         = 0;
        $data["alloted_leave"]       = 0;
        $data['sch_setting']         = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $user_type                   = $this->staff_model->getStaffRole();
        $data['classlist']           = $user_type;

        $date = $year . "-" . $month;

        $searchEmployee = $this->payroll_model->searchEmployeeById($id);

        $data['result'] = $searchEmployee;
        $data["month"]  = $month;
        $data["year"]   = $year;
        $data["earnings"]   = array();
        $data["deductions"] = array();
        $data["is_calculated"] = false; // Track if payslip is already calculated
        $last_payslip = $this->payroll_model->getLastPayslip($id);

        if(!empty($last_payslip)){
            // Payslip already exists - it's calculated
            $data["is_calculated"] = true;
            if($last_payslip['basic'] > 0){
                $data['result']['basic_salary'] = $last_payslip['basic'];
            }
            $data["earnings"]   = $this->payroll_model->getAllowance($last_payslip['id'], 'positive');
            $data["deductions"] = $this->payroll_model->getAllowance($last_payslip['id'], 'negative');
        }

        $alloted_leave = $this->staff_model->alloted_leave($id);

        $newdate = date('Y-m-d', strtotime($date . " +1 month"));

        $monthAttendanceData = $this->monthAttendance($newdate, 3, $id);
        $data['monthAttendance'] = $monthAttendanceData; // Assign the full array

        // Extract specific counts from monthAttendanceData
        $currentMonthKey = date('01-m-Y', strtotime($date));
        if (isset($monthAttendanceData[$currentMonthKey])) {
            $data["holiday"] = $monthAttendanceData[$currentMonthKey]['holiday'] ?? 0;
            $data["sunday_count"] = $monthAttendanceData[$currentMonthKey]['sunday'] ?? 0;
        } else {
             // Fallback if current month data isn't directly available (e.g., if $no_of_months logic shifts it)
             // This might need more robust handling if monthAttendance can return data for other months.
             // For now, let's assume the first entry is most relevant if monthAttendance is updated to return only one month.
             $firstMonthData = reset($monthAttendanceData);
             $data["holiday"] = $firstMonthData['holiday'] ?? 0;
             $data["sunday_count"] = $firstMonthData['sunday'] ?? 0;
        }

        $data['monthLeaves']     = $this->monthLeaves($newdate, 3, $id);
        $data["attendanceType"]  = $this->staffattendancemodel->getStaffAttendanceType();
        $data["staff_attendance_keys"] = array_keys($this->staff_attendance);
        $data["alloted_leave"]   = $alloted_leave[0]["alloted_leave"];
        $data['month_absent_working_days'] = $this->getMonthAbsentWorkingDays($monthAttendanceData, $id);
        $data['month_absent_total'] = $this->getMonthAbsentTotals($monthAttendanceData, $data['month_absent_working_days']);
        $data['month_paid_leave_absent'] = $this->getMonthPaidLeaveAbsentCounts($monthAttendanceData, $id);
        $data['payroll_lop_summary'] = $this->getPayrollLopSummary($monthAttendanceData, $data['monthLeaves'], $month, $year, $id);

        // Load standardized allowance types for dropdowns
        $data['earning_types'] = $this->payroll_model->getAllowanceTypes('earning', true);
        $data['deduction_types'] = $this->payroll_model->getAllowanceTypes('deduction', true);

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/create", $data);
        $this->load->view("layout/footer", $data);
    }

    public function edit($id)
    {
        $data["staff_id"]         = "";
        $data["basic"]            = "";
        $data["name"]             = "";
        $data["month"]            = "";
        $data["year"]             = "";
        $data["present"]          = 0;
        $data["absent"]           = 0;
        $data["late"]             = 0;
        $data["half_day"]         = 0;
        $data["holiday"]          = 0;
        $data["leave_count"]      = 0;
        $data["alloted_leave"]    = 0;
        $user_type                = $this->staff_model->getStaffRole();
        $employee_payroll         = $this->payroll_model->getPayslip($id);
        $data['employee_payroll'] = $employee_payroll;
        $data['classlist']        = $user_type;
        $data['sch_setting']      = $this->sch_setting_detail;
        $searchEmployee           = $this->payroll_model->searchEmployeeById($employee_payroll['staff_id']);
        if(empty($employee_payroll['basic']) || $employee_payroll['basic'] == 0){
            $employee_payroll['basic'] = $searchEmployee['basic_salary'];
        }
        
        // ==========================================
        // Check for Salary Increments (NEW)
        // ==========================================
        $month_num = date('n', strtotime($employee_payroll['year'] . '-' . $employee_payroll['month'] . '-01'));
        $increment = $this->payroll_increment_model->getApprovedIncrementForMonth($employee_payroll['staff_id'], $month_num, $employee_payroll['year']);
        
        if ($increment) {
            if ($increment['increment_type'] === 'Fixed') {
                $increment_amount = (float) $increment['increment_amount'];
            } else {
                $increment_amount = round($employee_payroll['basic'] * ($increment['increment_percentage'] / 100), 2);
            }
            $employee_payroll['is_increment_month'] = true;
            $employee_payroll['increment_amount'] = $increment_amount;
            $employee_payroll['merge_with'] = $increment['merge_with'];
            $employee_payroll['increment_type'] = $increment['increment_type'];
            $employee_payroll['increment_effective_date'] = $increment['effective_date'];
            $employee_payroll['is_recurring'] = isset($increment['is_recurring']) ? (int)$increment['is_recurring'] : 1;
        } else {
            $employee_payroll['is_increment_month'] = false;
            $employee_payroll['increment_amount'] = 0;
        }
        
        $data['employee_payroll'] = $employee_payroll;
        $date                     = $employee_payroll['year'] . "-" . $employee_payroll['month'];
        $data['result']           = $searchEmployee;
        $data["month"]            = $employee_payroll['month'];
        $data["year"]             = $employee_payroll['year'];
        $data["is_calculated"]    = true; // Edit view is for existing calculated payslips

        $data["earnings"]   = $this->payroll_model->getAllowance($id, 'positive');
        $data["deductions"] = $this->payroll_model->getAllowance($id, 'negative');

        $alloted_leave           = $this->staff_model->alloted_leave($employee_payroll['staff_id']);
        $newdate                 = date('Y-m-d', strtotime($date . " +1 month"));
        $data['monthAttendance'] = $this->monthAttendance($newdate, 3, $employee_payroll['staff_id']);
        $data['monthLeaves']     = $this->monthLeaves($newdate, 3, $employee_payroll['staff_id']);
        $data["attendanceType"]  = $this->staffattendancemodel->getStaffAttendanceType();
        $data["staff_attendance_keys"] = array_keys($this->staff_attendance);
        $data["alloted_leave"]   = $alloted_leave[0]["alloted_leave"];
        $data['month_absent_working_days'] = $this->getMonthAbsentWorkingDays($data['monthAttendance'], $employee_payroll['staff_id']);
        $data['month_absent_total'] = $this->getMonthAbsentTotals($data['monthAttendance'], $data['month_absent_working_days']);
        $data['month_paid_leave_absent'] = $this->getMonthPaidLeaveAbsentCounts($data['monthAttendance'], $employee_payroll['staff_id']);
        $data['payroll_lop_summary'] = $this->getPayrollLopSummary($data['monthAttendance'], $data['monthLeaves'], $data["month"], $data["year"], $employee_payroll['staff_id']);
        
        // Load standardized allowance types for dropdowns
        $data['earning_types'] = $this->payroll_model->getAllowanceTypes('earning', true);
        $data['deduction_types'] = $this->payroll_model->getAllowanceTypes('deduction', true);
        
        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/edit", $data);
        $this->load->view("layout/footer", $data);
    }

    private function getPayrollLopSummary($monthAttendance, $monthLeaves, $month, $year, $staff_id)
    {
        $month_num = date('m', strtotime($year . '-' . $month . '-01'));
        $month_key = '01-' . $month_num . '-' . $year;
        $attendance = $monthAttendance[$month_key] ?? reset($monthAttendance) ?? [];

        $period = $this->getPayrollPeriodRange($month, $year);
        $days_in_period = (int) ($attendance['days_in_period'] ?? $this->getDaysInRange($period['start_date'], $period['end_date']));
        $working_days = (int) ($attendance['working_days'] ?? 0);
        if ($working_days === 0) {
            $context = $this->getWorkingDayContextRange($period['start_date'], $period['end_date']);
            $working_days = count($context['working_day_dates']);
        }

        $holidays = (int) ($attendance['holiday'] ?? 0);
        $sundays = (int) ($attendance['sunday'] ?? 0);

        $present = (int) ($attendance['present'] ?? 0);
        $late = (int) ($attendance['late'] ?? 0);
        $absent_working = $this->getAbsentWorkingDayCount($month_num, $year, $staff_id);
        $half_day = (int) ($attendance['half_day'] ?? 0);
        $first_half_absent = (int) ($attendance['first_half_absent'] ?? 0);
        $second_half_absent = (int) ($attendance['second_half_absent'] ?? 0);
        $first_half_permission = (int) ($attendance['first_half_permission'] ?? 0);
        $second_half_permission = (int) ($attendance['second_half_permission'] ?? 0);

        $approved_leave = (int) ($monthLeaves[$month_num] ?? 0);

        $lop_rules = $this->config->item('lop_rules');
        $half_day_weight = isset($lop_rules['half_day_weight']) ? (float) $lop_rules['half_day_weight'] : 0.5;

        $permission_count = $first_half_permission + $second_half_permission;
        $max_late_allowed = isset($this->sch_setting_detail->max_late_allowed) ? (int) $this->sch_setting_detail->max_late_allowed : 0;
        $max_permission_allowed = isset($this->sch_setting_detail->max_permission_allowed) ? (int) $this->sch_setting_detail->max_permission_allowed : 0;

        $late_half_days = $late > $max_late_allowed ? ($late - $max_late_allowed) : 0;
        $permission_half_days = $permission_count > $max_permission_allowed ? ($permission_count - $max_permission_allowed) : 0;

        $paid_leave_absent = $this->getPaidLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id);

        $late_permission_penalty = ($late_half_days + $permission_half_days) * $half_day_weight;

        $total_present = max(0, $present + ($half_day * $half_day_weight) - $late_permission_penalty + $paid_leave_absent);
        $total_absent = $absent_working + ($half_day * $half_day_weight) + $late_permission_penalty;

        $lop_days = $total_absent
            + (($first_half_absent + $second_half_absent) * $half_day_weight);

        $paid_days = $total_present;

        return [
            'month_key' => $month_key,
            'days_in_month' => $days_in_period,
            'working_days' => $working_days,
            'present' => $present,
            'absent' => $total_absent,
            'half_day' => $half_day,
            'late' => $late,
            'first_half_absent' => $first_half_absent,
            'second_half_absent' => $second_half_absent,
            'first_half_permission' => $first_half_permission,
            'second_half_permission' => $second_half_permission,
            'approved_leave' => $approved_leave,
            'paid_leave_absent' => $paid_leave_absent,
            'holidays' => $holidays,
            'sundays' => $sundays,
            'late_half_days' => $late_half_days,
            'permission_half_days' => $permission_half_days,
            'lop_days' => $lop_days,
            'paid_days' => $paid_days,
        ];
    }

    private function getMonthAbsentWorkingDays($monthAttendance, $staff_id)
    {
        $absent_by_month = [];
        foreach (array_keys($monthAttendance) as $month_key) {
            $month_num = date('m', strtotime($month_key));
            $year = date('Y', strtotime($month_key));
            $absent_by_month[$month_key] = $this->getAbsentWorkingDayCount($month_num, $year, $staff_id);
        }

        return $absent_by_month;
    }

    private function getMonthPaidLeaveAbsentCounts($monthAttendance, $staff_id)
    {
        $paid_leave_by_month = [];
        foreach (array_keys($monthAttendance) as $month_key) {
            $month_num = date('m', strtotime($month_key));
            $year = date('Y', strtotime($month_key));
            $period = $this->getPayrollPeriodRange($month_num, $year);
            $paid_leave_by_month[$month_key] = $this->getPaidLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id);
        }

        return $paid_leave_by_month;
    }

    private function getMonthAbsentTotals($monthAttendance, $absent_working_days)
    {
        $lop_rules = $this->config->item('lop_rules');
        $half_day_weight = isset($lop_rules['half_day_weight']) ? (float) $lop_rules['half_day_weight'] : 0.5;

        $total_by_month = [];
        foreach ($monthAttendance as $month_key => $attendance_row) {
            $half_day = (int) ($attendance_row['half_day'] ?? 0);
            $absent_working = (int) ($absent_working_days[$month_key] ?? 0);
            $total_by_month[$month_key] = $absent_working + ($half_day * $half_day_weight);
        }

        return $total_by_month;
    }

    private function getAbsentWorkingDayCount($month_num, $year, $staff_id)
    {
        $period = $this->getPayrollPeriodRange($month_num, $year);
        $context = $this->getWorkingDayContextRange($period['start_date'], $period['end_date']);
        $working_day_dates = $context['working_day_dates'];
        $absent_count = 0;

        foreach ($working_day_dates as $work_date) {
            $attendance_row = $this->staffattendancemodel->searchStaffattendance($work_date, $staff_id, false);
            $attendance_key = $attendance_row['key'] ?? null;
            if ($attendance_key === 'A') {
                $absent_count++;
            }
        }

        $paid_leave_absent = $this->getPaidLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id, $context);

        return max(0, $absent_count - $paid_leave_absent);
    }

    private function getPaidLeaveAbsentCount($month_num, $year, $staff_id, $context = null)
    {
        $period = $this->getPayrollPeriodRange($month_num, $year);
        return $this->getPaidLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id, $context);
    }

    private function getPaidLeaveAbsentCountRange($start_date, $end_date, $staff_id, $context = null)
    {
        if ($context === null) {
            $context = $this->getWorkingDayContextRange($start_date, $end_date);
        }

        $working_day_dates = $context['working_day_dates'];
        $approved_paid_leave_dates = $this->getApprovedPaidLeaveDatesByRange($start_date, $end_date, $staff_id);
        $approved_paid_leave_dates = array_values(array_intersect($approved_paid_leave_dates, $working_day_dates));

        $paid_leave_absent = 0;
        foreach ($approved_paid_leave_dates as $leave_date) {
            $attendance_row = $this->staffattendancemodel->searchStaffattendance($leave_date, $staff_id, false);
            $attendance_key = $attendance_row['key'] ?? null;
            if ($attendance_key === 'A') {
                $paid_leave_absent++;
            }
        }

        return $paid_leave_absent;
    }

    private function getApprovedPaidLeaveDates($month_num, $year, $staff_id)
    {
        $start_date = $year . '-' . sprintf('%02d', $month_num) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        return $this->getApprovedPaidLeaveDatesByRange($start_date, $end_date, $staff_id);
    }

    private function getApprovedPaidLeaveDatesByRange($start_date, $end_date, $staff_id)
    {
        $this->db->select('staff_leave_request.leave_from, staff_leave_request.leave_to');
        $this->db->from('staff_leave_request');
        $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id');
        $this->db->where('staff_leave_request.staff_id', $staff_id);
        $this->db->where('staff_leave_request.status', 'approve');
        $this->db->where('leave_types.is_lop', 0);
        $this->db->where('staff_leave_request.leave_from <=', $end_date);
        $this->db->where('staff_leave_request.leave_to >=', $start_date);
        $rows = $this->db->get()->result_array();

        $leave_dates = [];
        foreach ($rows as $row) {
            $from = new DateTime(max($row['leave_from'], $start_date));
            $to = new DateTime(min($row['leave_to'], $end_date));
            while ($from <= $to) {
                $leave_dates[] = $from->format('Y-m-d');
                $from->modify('+1 day');
            }
        }

        return array_values(array_unique($leave_dates));
    }

    private function getWorkingDayContext($month_num, $year)
    {
        $start_date = $year . '-' . sprintf('%02d', $month_num) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        return $this->getWorkingDayContextRange($start_date, $end_date);
    }

    private function getWorkingDayContextRange($start_date, $end_date, $settings = null, $holidays = null)
    {
        $this->load->model("holiday_model");
        $this->load->model("setting_model");

        if ($settings === null) {
            $settings = $this->setting_model->getSetting();
        }
        if ($holidays === null) {
            $holidays = $this->holiday_model->get();
        }

        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int) $settings->isSecondSaturdayHoliday : 0;

        $range_start = new DateTime($start_date);
        $range_end = new DateTime($end_date);

        $official_holiday_dates = [];
        $compensation_dates = [];
        foreach ($holidays as $holiday_value) {
            $type_label = strtolower(trim($holiday_value['type'] ?? ''));
            $from_date = new DateTime($holiday_value['from_date']);
            $to_date = new DateTime($holiday_value['to_date']);
            if ($to_date < $range_start || $from_date > $range_end) {
                continue;
            }
            $current = clone $from_date;
            while ($current <= $to_date) {
                if ($current >= $range_start && $current <= $range_end) {
                    if ($type_label === 'compensation') {
                        $compensation_dates[] = $current->format('Y-m-d');
                    } else {
                        $official_holiday_dates[] = $current->format('Y-m-d');
                    }
                }
                $current->modify('+1 day');
            }
        }
        $official_holiday_dates = array_values(array_unique($official_holiday_dates));
        $compensation_dates = array_values(array_unique($compensation_dates));

        $weekend_day_dates = [];
        $working_day_dates = [];
        $holiday_dates = [];

        $current = new DateTime($start_date);
        while ($current <= $range_end) {
            $dateStr = $current->format('Y-m-d');
            $dayOfWeek = (int) $current->format('w');
            $is_second_saturday = false;
            if ($isSecondSaturdayWeekend && $dayOfWeek === 6) {
                $is_second_saturday = $this->isSecondSaturday($current);
            }

            if (in_array($dateStr, $compensation_dates, true)) {
                $working_day_dates[] = $dateStr;
                $current->modify('+1 day');
                continue;
            }

            $is_weekend = in_array($dayOfWeek, $weekendDays, true) || $is_second_saturday;
            $is_official_holiday = in_array($dateStr, $official_holiday_dates, true);

            if ($is_weekend) {
                $weekend_day_dates[] = $dateStr;
            }
            if ($is_official_holiday && !$is_weekend) {
                $holiday_dates[] = $dateStr;
            }
            if (!$is_weekend && !$is_official_holiday) {
                $working_day_dates[] = $dateStr;
            }

            $current->modify('+1 day');
        }

        return [
            'working_day_dates' => array_values(array_unique($working_day_dates)),
            'weekend_day_dates' => array_values(array_unique($weekend_day_dates)),
            'holiday_dates' => array_values(array_unique($holiday_dates)),
        ];
    }

    private function isSecondSaturday(DateTime $dateObj)
    {
        $month_start = new DateTime($dateObj->format('Y-m-01'));
        $count = 0;
        while ($month_start <= $dateObj) {
            if ((int) $month_start->format('w') === 6) {
                $count++;
            }
            if ($month_start->format('Y-m-d') === $dateObj->format('Y-m-d')) {
                break;
            }
            $month_start->modify('+1 day');
        }
        return $count === 2;
    }

    private function getPayrollPeriodRange($month, $year)
    {
        $offset_days = isset($this->sch_setting_detail->payroll_cutoff_day) ? (int) $this->sch_setting_detail->payroll_cutoff_day : 0;
        $month_num = (int) $month;
        $year_num = (int) $year;
        if ($month_num < 1 || $month_num > 12) {
            $month_num = (int) date('m', strtotime($year . '-' . $month . '-01'));
        }

        if ($offset_days <= 0) {
            $start_date = sprintf('%04d-%02d-01', $year_num, $month_num);
            $end_date = date('Y-m-t', strtotime($start_date));
            return [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'offset_days' => 0,
            ];
        }

        $last_day = cal_days_in_month(CAL_GREGORIAN, $month_num, $year_num);
        $cutoff_day = max(1, $last_day - $offset_days);

        $prev_month = $month_num - 1;
        $prev_year = $year_num;
        if ($prev_month === 0) {
            $prev_month = 12;
            $prev_year--;
        }
        $prev_month_days = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year);
        $start_day = min($prev_month_days, $cutoff_day + 1);

        $start_date = sprintf('%04d-%02d-%02d', $prev_year, $prev_month, $start_day);
        $end_date = sprintf('%04d-%02d-%02d', $year_num, $month_num, $cutoff_day);

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'offset_days' => $offset_days,
        ];
    }

    private function getDaysInRange($start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $diff = $start->diff($end);
        return (int) $diff->days + 1;
    }

    public function editpayroll()
    {
        $id              = $this->input->post("id");
        $basic           = $this->input->post("basic");
        $total_allowance = $this->input->post("total_allowance");
        $total_deduction = $this->input->post("total_deduction");
        $net_salary      = $this->input->post("net_salary");
        $status          = $this->input->post("status");
        $staff_id        = $this->input->post("staff_id");
        $month           = $this->input->post("month");
        $name            = $this->input->post("name");
        $year            = $this->input->post("year");
        $tax             = $this->input->post("tax_percent");
        $leave_deduction = $this->input->post("leave_deduction");
        $this->form_validation->set_rules('net_salary', $this->lang->line('net_salary'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->create($month, $year, $staff_id);
        } else {
                
        if($total_allowance){
                $total_allowance = convertCurrencyFormatToBaseAmount($total_allowance);
        }else{
                $total_allowance = 0;  
        }
        
        if($total_deduction){
                $total_deduction = convertCurrencyFormatToBaseAmount($total_deduction);
        }else{
                $total_deduction = 0;  
        }
        
        if($basic){
                $basic = convertCurrencyFormatToBaseAmount($basic);
        }else{
                $basic = 0;  
        }
        
        if($net_salary){
                $net_salary = convertCurrencyFormatToBaseAmount($net_salary);
        }else{
                $net_salary = 0;  
        }
        
        if($tax){
            $tax = convertCurrencyFormatToBaseAmount($tax);
        }else{
            $tax = 0;  
        }

        if($leave_deduction){
            $leave_deduction = convertCurrencyFormatToBaseAmount($leave_deduction);
        }else{
            $leave_deduction = 0;
        }
        
        
            $data = array(
                'id'              => $id,
                'staff_id'        => $staff_id,
                'basic'           => $basic,
                'total_allowance' => $total_allowance,
                'total_deduction' => $total_deduction,
                'net_salary'      => $net_salary,
                'payment_date'    => date("Y-m-d"),
                'status'          => $status,
                'month'           => $month,
                'year'            => $year,
                'tax'             => $tax,
                'leave_deduction' => $leave_deduction,
                'generated_by'    => $this->customlib->getStaffID(),
            );

            $checkForUpdate = $this->payroll_model->checkPayslip($month, $year, $staff_id);
            if (!$checkForUpdate) {
                $insert_id         = $this->payroll_model->createPayslip($data);
                $payslipid         = $insert_id;
                
                // Get staff data to calculate EPF/ESI based on dual checkpoints
                $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
                $statutory_deductions = $this->payroll_model->calculateStatutoryDeductions($staff_data);
                
                // Load allowance type mapping
                $allowance_types = $this->payroll_model->getAllowanceTypes(null, false);
                $allowance_type_map = [];
                foreach ($allowance_types as $type) {
                    $allowance_type_map[(int) $type['id']] = $type['allowance_code'];
                }
                $deduction_type_map = $allowance_type_map; // Same mapping for deductions
                
                $allowance_type_id = $this->input->post("allowance_type_id");
                $deduction_type_id = $this->input->post("deduction_type_id");
                $allowance_prev_id = $this->input->post("allowance_prev_id");
                $deduction_prev_id = $this->input->post("deduction_prev_id");
                $allowance_amount  = $this->input->post("allowance_amount");
                $deduction_amount  = $this->input->post("deduction_amount");

                if (!empty($allowance_type_id)) {

                    $i                        = 0;
                    $insert_payslip_allowance = array();
                    $update_payslip_allowance = array();
                    foreach ($allowance_type_id as $key => $type_id) {
                        
                        if($allowance_amount[$i]){
                                $allowanceamount = convertCurrencyFormatToBaseAmount($allowance_amount[$i]);
                        }else{
                                $allowanceamount = 0;  
                        }
                                
                        if ($allowance_prev_id[$i] != 0) {
                            $update_payslip_allowance[] = array(
                                'id'                => $allowance_prev_id[$i],
                                'payslip_id'        => $payslipid,
                                'allowance_type'    => $allowance_type_map[$allowance_type_id[$i]] ?? '',
                                'amount'            => $allowanceamount,
                                'staff_id'          => $staff_id,
                                'cal_type'          => "positive",
                            );
                        } else {
                            
                            $insert_payslip_allowance[] = array(
                                'payslip_id'        => $payslipid,
                                'allowance_type'    => $allowance_type_map[$allowance_type_id[$i]] ?? '',
                                'amount'            => $allowanceamount,
                                'staff_id'          => $staff_id,
                                'cal_type'          => "positive",
                            );
                        }

                        $i++;
                    }

                    $insert_payslip_allowance = $this->payroll_model->update_allowance($insert_payslip_allowance, $update_payslip_allowance, $allowance_prev_id, $payslipid, 'positive');
                } else {

                    $insert_payslip_allowance = $this->payroll_model->update_allowance([], [], [0], $payslipid, 'positive');
                }

                if (!empty($deduction_type_id)) {
                    $j                        = 0;
                    $insert_payslip_allowance = array();
                    $update_payslip_allowance = array();

                    foreach ($deduction_type_id as $key => $type_id) {
                        
                                if($deduction_amount[$j]){
                                        $deductionamount = convertCurrencyFormatToBaseAmount($deduction_amount[$j]);
                                }else{
                                        $deductionamount = 0;  
                                }
                                
                        if ($deduction_prev_id[$j] != 0) {
                            

                            
                            $update_payslip_allowance[] = array(
                                'id'                => $deduction_prev_id[$j],
                                'payslip_id'        => $payslipid,
                                'allowance_type'    => $deduction_type_map[$deduction_type_id[$j]] ?? '',
                                'amount'            => $deductionamount,
                                'staff_id'          => $staff_id,
                                'cal_type'          => "negative",
                            );
                        } else {
                            
                            
                            $insert_payslip_allowance[] = array(
                                'payslip_id'        => $payslipid,
                                'allowance_type'    => $deduction_type_map[$deduction_type_id[$j]] ?? '',
                                'amount'            => $deductionamount,
                                'staff_id'          => $staff_id,
                                'cal_type'          => "negative",
                            );
                        }
                        $j++;
                    }

                    $insert_payslip_allowance = $this->payroll_model->update_allowance($insert_payslip_allowance, $update_payslip_allowance, $deduction_prev_id, $payslipid, 'negative');
                } else {
                    $insert_payslip_allowance = $this->payroll_model->update_allowance([], [], [0], $payslipid, 'negative');
                }

                // Add automatic EPF/ESI deductions based on dual checkpoint validation
                $auto_statutory_deductions = [];
                
                if ($statutory_deductions['epf_deduction'] > 0) {
                    $auto_statutory_deductions[] = [
                        'payslip_id'     => $payslipid,
                        'allowance_type' => 'Employee Provident Fund (EPF)',
                        'amount'         => $statutory_deductions['epf_deduction'],
                        'staff_id'       => $staff_id,
                        'cal_type'       => 'negative',
                    ];
                }
                
                if ($statutory_deductions['esi_deduction'] > 0) {
                    $auto_statutory_deductions[] = [
                        'payslip_id'     => $payslipid,
                        'allowance_type' => 'Employee State Insurance (ESI)',
                        'amount'         => $statutory_deductions['esi_deduction'],
                        'staff_id'       => $staff_id,
                        'cal_type'       => 'negative',
                    ];
                }
                
                // Insert automatic statutory deductions if any
                if (!empty($auto_statutory_deductions)) {
                    $this->payroll_model->update_allowance($auto_statutory_deductions, [], [], $payslipid, 'negative');
                }

                redirect('admin/payroll');
            } else {

                $this->session->set_flashdata("msg", "<div class='alert alert-warning'>" . $this->lang->line('payslip_not_generated') . "</div>");

                redirect('admin/payroll');
            }
        }
    }

    public function monthAttendance($st_month, $no_of_months, $emp)
    {
        $this->load->model("holiday_model");
        $holidays = $this->holiday_model->get();
        $this->load->model("setting_model");
        $settings = $this->setting_model->getSetting();
        $this->load->model("staffattendancemodel");
        $this->staff_attendance  = $this->config->item('staffattendance');

        $record = array();
        for ($i = 1; $i <= $no_of_months; $i++) {

            $r     = array();
            $month = date('m', strtotime($st_month . " -$i month"));
            $year  = date('Y', strtotime($st_month . " -$i month"));

            $period = $this->getPayrollPeriodRange($month, $year);
            $context = $this->getWorkingDayContextRange($period['start_date'], $period['end_date'], $settings, $holidays);
            $weekend_day_dates = $context['weekend_day_dates'];
            $holidays_for_H_column = $context['holiday_dates'];
            $working_day_dates = $context['working_day_dates'];


            $attendance_types_from_db = $this->staffattendancemodel->getStaffAttendanceType();
            $att_key_to_id_map = [];
            foreach ($attendance_types_from_db as $type_row) {
                $config_key = str_replace(" ", "_", strtolower($type_row['type']));
                $att_key_to_id_map[$config_key] = $type_row['id'];
            }

            foreach ($this->staff_attendance as $att_key => $att_value_from_config) {
                $attendance_type_id_for_query = $att_key_to_id_map[$att_key] ?? null;

                if ($att_key == 'holiday') {
                    $r[$att_key] = count($holidays_for_H_column); // Now this only counts "other leaves"
                    $r['sunday'] = count($weekend_day_dates); // Weekend days count
                    continue;
                }

                if ($attendance_type_id_for_query !== null) {
                    $s = $this->payroll_model->count_attendance_range($period['start_date'], $period['end_date'], $emp, $attendance_type_id_for_query);
                    $r[$att_key] = $s;
                } else {
                    $r[$att_key] = 0;
                }
            }

            $r['days_in_period'] = $this->getDaysInRange($period['start_date'], $period['end_date']);
            $r['working_days'] = count($working_day_dates);


            $record['01-' . $month . '-' . $year] = $r;
        }
        return $record;
    }

    public function monthLeaves($st_month, $no_of_months, $emp)
    {
        $record = array();
        for ($i = 1; $i <= $no_of_months; $i++) {

            $r           = array();
            $month       = date('m', strtotime($st_month . " -$i month"));
            $year        = date('Y', strtotime($st_month . " -$i month"));
            $period = $this->getPayrollPeriodRange($month, $year);
            $context = $this->getWorkingDayContextRange($period['start_date'], $period['end_date']);
            $working_day_dates = $context['working_day_dates'];
            $approved_paid_leave_dates = $this->getApprovedPaidLeaveDatesByRange($period['start_date'], $period['end_date'], $emp);
            $approved_paid_leave_dates = array_values(array_intersect($approved_paid_leave_dates, $working_day_dates));

            $record[$month] = count($approved_paid_leave_dates);
        }

        return $record;
    }

    public function payslip()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_add')) {
            access_denied();
        }        
        
        if($this->input->post("total_allowance")){
                $total_allowance = convertCurrencyFormatToBaseAmount($this->input->post("total_allowance"));
        }else{
                $total_allowance = 0;  
        }
        
        if($this->input->post("total_deduction")){
                $total_deduction = convertCurrencyFormatToBaseAmount($this->input->post("total_deduction"));
        }else{
                $total_deduction = 0;  
        }
        
        if($this->input->post("basic")){
                $basic = convertCurrencyFormatToBaseAmount($this->input->post("basic"));
        }else{
                $basic = 0;  
        }
        
        if($this->input->post("net_salary")){
                $net_salary = convertCurrencyFormatToBaseAmount($this->input->post("net_salary"));
        }else{
                $net_salary = 0;  
        }
        
        if($this->input->post("tax")){
            $tax = convertCurrencyFormatToBaseAmount($this->input->post("tax"));
        }else{
            $tax = 0;  
        }      

        if($leave_deduction){
            $leave_deduction = convertCurrencyFormatToBaseAmount($leave_deduction);
        }else{
            $leave_deduction = 0;
        }
 
        $status          = $this->input->post("status");
        $staff_id        = $this->input->post("staff_id");
        $month           = $this->input->post("month");
        $name            = $this->input->post("name");
        $year            = $this->input->post("year");      
        
        $leave_deduction = $this->input->post("leave_deduction");
        
        $this->form_validation->set_rules('net_salary', $this->lang->line('net_salary'), 'trim|required|xss_clean');       
        
        if ($this->form_validation->run() == false) {
            $this->create($month, $year, $staff_id);
        } else {

            $data = array('staff_id' => $staff_id,
                'basic'                  => $basic,
                'total_allowance'        => $total_allowance,
                'total_deduction'        => $total_deduction,
                'net_salary'             => $net_salary,
                'payment_date'           => date("Y-m-d"),
                'status'                 => $status,
                'month'                  => $month,
                'year'                   => $year,
                'tax'                    => $tax,
                'leave_deduction'        => $leave_deduction,
            );

            $checkForUpdate = $this->payroll_model->checkPayslip($month, $year, $staff_id);
 
            if ($checkForUpdate == true) {

                $insert_id        = $this->payroll_model->createPayslip($data);
                $payslipid        = $insert_id;
                
                // Get staff data to calculate EPF/ESI based on dual checkpoints
                $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
                $statutory_deductions = $this->payroll_model->calculateStatutoryDeductions($staff_data);
                
                // Load allowance type mapping to convert IDs to codes
                $allowance_types = $this->payroll_model->getAllowanceTypes(null, false);
                $allowance_type_map = [];
                foreach ($allowance_types as $type) {
                    $allowance_type_map[(int) $type['id']] = $type['allowance_code'];
                }
                $deduction_type_map = $allowance_type_map; // Same mapping for deductions
                
                $allowance_type_id = $this->input->post("allowance_type_id");
                $deduction_type_id = $this->input->post("deduction_type_id");
                $allowance_amount = $this->input->post("allowance_amount");
                $deduction_amount = $this->input->post("deduction_amount");
                if (!empty($allowance_type_id)) {

                    $i = 0;
                    foreach ($allowance_type_id as $key => $type_id) {
                        
                        if($allowance_amount[$i]){
                                $allowanceamount = convertCurrencyFormatToBaseAmount($allowance_amount[$i]);
                        }else{
                                $allowanceamount = 0;  
                        } 
                        
                        $all_data = array(
                            'payslip_id'        => $payslipid,
                            'allowance_type'    => $allowance_type_map[$allowance_type_id[$i]] ?? '',
                            'amount'            => $allowanceamount,
                            'staff_id'          => $staff_id,
                            'cal_type'          => "positive",
                        );

                        $insert_payslip_allowance = $this->payroll_model->add_allowance($all_data);

                        $i++;
                    }
                }

                if (!empty($deduction_type_id)) {
                    $j = 0;
                    foreach ($deduction_type_id as $key => $type_id) {
                        
                        if($deduction_amount[$j]){
                                $deductionamount = convertCurrencyFormatToBaseAmount($deduction_amount[$j]);
                        }else{
                                $deductionamount = 0;  
                        }
                        
                        $type_data = array('payslip_id' => $payslipid,
                            'allowance_type'            => $deduction_type_map[$deduction_type_id[$j]] ?? '',
                            'amount'                    => $deductionamount,
                            'staff_id'                  => $staff_id,
                            'cal_type'                  => "negative",
                        );

                        $insert_payslip_allowance = $this->payroll_model->add_allowance($type_data);

                        $j++;
                    }
                }
                
                // Delete existing statutory deductions (EPF/ESI) before adding new ones to prevent duplicates
                $this->db->where('payslip_id', $payslipid)
                    ->where_in('allowance_type', ['EPF', 'ESI', 'Employee Provident Fund (EPF)', 'Employee State Insurance (ESI)'])
                    ->delete('payslip_allowance');
                
                // Add automatic EPF/ESI deductions based on dual checkpoint validation
                if ($statutory_deductions['epf_deduction'] > 0) {
                    $epf_data = array(
                        'payslip_id'     => $payslipid,
                        'allowance_type' => 'EPF',
                        'amount'         => $statutory_deductions['epf_deduction'],
                        'staff_id'       => $staff_id,
                        'cal_type'       => 'negative',
                    );
                    $this->payroll_model->add_allowance($epf_data);
                }
                
                if ($statutory_deductions['esi_deduction'] > 0) {
                    $esi_data = array(
                        'payslip_id'     => $payslipid,
                        'allowance_type' => 'ESI',
                        'amount'         => $statutory_deductions['esi_deduction'],
                        'staff_id'       => $staff_id,
                        'cal_type'       => 'negative',
                    );
                    $this->payroll_model->add_allowance($esi_data);
                }

                redirect('admin/payroll');
            } else {

                $this->session->set_flashdata("msg", $this->lang->line('payslip_already_generated'));
                redirect('admin/payroll');
            }
        }
    }

    public function search($month, $year, $role = '')
    {
        $user_type              = $this->staff_model->getStaffRole();
        $data['classlist']      = $user_type;
        $data['monthlist']      = $this->customlib->getMonthDropdown();
        $searchEmployee         = $this->payroll_model->searchEmployee($month, $year, $emp_name = '', $role);
        $data["resultlist"]     = $searchEmployee;
        $data["name"]           = $emp_name;
        $data["month"]          = $month;
        $data["year"]           = $year;
        $data['sch_setting']    = $this->sch_setting_detail;
        $data["payroll_status"] = $this->payroll_status;
        $data["resultlist"]     = $searchEmployee;
        $data["payment_mode"]   = $this->payment_mode;
        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/stafflist", $data);
        $this->load->view("layout/footer", $data);
    }

    public function bulkcalculate()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_add')) {
            access_denied();
        }

        // Load tax and EPF calculator library
        $this->load->library('tax_epf_calculator');

        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $role = $this->input->post('role');
        $overwrite = $this->input->post('bulk_overwrite') ? true : false;

        if (empty($month) || empty($year) || $month === 'select' || $year === 'select') {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Please select month and year for bulk calculation.</div>');
            redirect('admin/payroll');
        }
        
        // Convert month name to numeric for monthly balance tracking
        $month_numeric = date('n', strtotime($year . '-' . $month . '-01'));

        $staff_list = $this->payroll_model->searchEmployee($month, $year, '', $role);
        $generated = 0;
        $updated_existing = 0;
        $skipped_existing = 0;

        foreach ($staff_list as $staff) {
            if (!empty($staff['payslip_id']) && !$overwrite) {
                $skipped_existing++;
                continue;
            }

            $basic = !empty($staff['basic_salary']) ? $staff['basic_salary'] : 0;
            $last_payslip = $this->payroll_model->getLastPayslip($staff['id']);
            $allowances = [];
            $total_allowance = 0;
            $total_deduction = 0;
            $da = 0;  // Initialize DA variable
            $tax = 0;
            $increment_amount = 0;  // Initialize increment variable
            $is_increment_month = false;

            // Check if this is an increment month
            $increment = $this->payroll_increment_model->getApprovedIncrementForMonth($staff['id'], $month_numeric, $year);
            if ($increment) {
                $is_increment_month = true;
                if ($increment['increment_type'] === 'Fixed') {
                    $increment_amount = (float) $increment['increment_amount'];
                } else {
                    $increment_amount = round($basic * ($increment['increment_percentage'] / 100), 2);
                }
            }

            if (!empty($last_payslip)) {
                if ((float) $basic <= 0 && !empty($last_payslip['basic'])) {
                    $basic = $last_payslip['basic'];
                }
                $tax = !empty($last_payslip['tax']) ? $last_payslip['tax'] : 0;
                $allowances = $this->payroll_model->getAllowance($last_payslip['id']);
                foreach ($allowances as $allowance) {
                    // Skip "BASIC" allowance code to avoid double counting with $basic field
                    $allowance_code = strtoupper(trim($allowance['allowance_type']));
                    if ($allowance_code === 'BASIC') {
                        continue;
                    }
                    
                    // Skip temporary increment from previous month
                    if ($allowance_code === 'TEMP') {
                        continue;
                    }
                    
                    // Extract DA from allowances if applicable
                    if ($allowance_code === 'DA') {
                        $da = (float) $allowance['amount'];
                    }
                    
                    if ($allowance['cal_type'] === 'positive') {
                        $total_allowance += (float) $allowance['amount'];
                    } else {
                        $total_deduction += (float) $allowance['amount'];
                    }
                }
            }

            // Add increment to total_allowance if it's increment month
            if ($is_increment_month && $increment_amount > 0) {
                $total_allowance += $increment_amount;
            }

            $date = $year . '-' . $month;
            $newdate = date('Y-m-d', strtotime($date . ' +1 month'));
            $monthAttendanceData = $this->monthAttendance($newdate, 3, $staff['id']);
            $monthLeaves = $this->monthLeaves($newdate, 3, $staff['id']);
            $lop_summary = $this->getPayrollLopSummary($monthAttendanceData, $monthLeaves, $month, $year, $staff['id']);

            // Track LOP values: Actual, Adjusted, and Net
            $actual_lop_days = !empty($lop_summary['lop_days']) ? (float) $lop_summary['lop_days'] : 0;
            $adjusted_lop_days = 0;
            $net_lop_days = $actual_lop_days;

            // If overwriting, reset the monthly balance LOP adjustments for this staff/month
            if (!empty($staff['payslip_id']) && $overwrite) {
                $this->db->where('staff_id', $staff['id']);
                $this->db->where('year', (int)$year);
                $this->db->where('month', (int)$month_numeric);
                // Reset LOP adjustment and recalculate closing balance
                $this->db->set('used_for_lop_adjustment', 0);
                $this->db->set('closing_balance', 'opening_balance + earned_in_month - used_for_leave_application - other_deductions', FALSE);
                $this->db->set('last_processed_date', NULL);
                $this->db->set('payslip_id', NULL);
                $this->db->update('staff_monthly_leave_balance');
            }

            // Process LOP adjustment with monthly balance tracking
            if ($actual_lop_days > 0) {
                $adjusted_result = $this->payroll_model->processLOPWithMonthlyBalance($staff['id'], $actual_lop_days, $month_numeric, $year);
                log_message('debug', 'LOP Adjustment Result for Staff ' . $staff['id'] . ': ' . json_encode($adjusted_result));
                if ($adjusted_result !== false && is_array($adjusted_result) && $adjusted_result['success']) {
                    $net_lop_days = (float) $adjusted_result['net_lop_days'];
                    $adjusted_lop_days = (float) $adjusted_result['adjusted_lop_days'];
                    log_message('debug', 'Staff ' . $staff['id'] . ' - Adjusted: ' . $adjusted_lop_days . ', Net: ' . $net_lop_days);
                } else {
                    log_message('error', 'LOP Adjustment Failed for Staff ' . $staff['id'] . ': ' . json_encode($adjusted_result));
                }
            }

            $gross_salary = (float) $basic + (float) $total_allowance;
            $lop_deduction = 0;
            if ($net_lop_days > 0) {
                // Calculate total days in the month
                $month_num = date('n', strtotime($year . '-' . $month . '-01'));
                $total_days_of_month = cal_days_in_month(CAL_GREGORIAN, $month_num, (int)$year);
                
                $lop_deduction = ($gross_salary / $total_days_of_month) * $net_lop_days;
            }

            // Calculate EPF and TDS using the new library
            // EPF CALCULATION: Only if UAN is available for the staff
            $epf_wage = 0;
            $employee_epf = 0;
            $employer_pf = 0;
            $employer_eps = 0;
            
            if (!empty($staff['uan_no']) && isset($staff['is_epf_enabled']) && $staff['is_epf_enabled'] == 1) {
                // Staff has UAN and EPF is enabled - calculate EPF wage
                $epf_wage = $this->tax_epf_calculator->calculate_epf_wage($basic, $da);
                $employee_epf = $this->tax_epf_calculator->calculate_employee_epf($epf_wage);
                $employer_pf = $this->tax_epf_calculator->calculate_employer_pf($epf_wage);
                $employer_eps = $this->tax_epf_calculator->calculate_employer_eps($epf_wage);
            }
            // If UAN is not available, EPF is 0 (skip this staff for EPF)
            
            // ESI CALCULATION: Only if ESI_no is available for the staff
            $esi_deduction = 0;
            if (!empty($staff['esi_no']) && isset($staff['is_esi_enabled']) && $staff['is_esi_enabled'] == 1) {
                // Staff has ESI_no and ESI is enabled - calculate ESI
                // ESI is 0.75% of gross salary (basic + DA + other allowances)
                $esi_base = $basic + $da; // ESI is calculated on basic+DA typically, though some consider full gross
                $esi_deduction = round($esi_base * 0.0075, 2);
            }
            // If ESI_no is not available, ESI is 0 (skip this staff for ESI)
            
            // Calculate TDS using YTD (Year-To-Date) approach for accurate mid-year increment handling
            $month_num = date('n', strtotime($year . '-' . $month . '-01'));
            $ytd_data = $this->payroll_model->getYTDIncome($staff['id'], $year, $month_num - 1); // -1 because current month hasn't been paid yet
            
            // Use YTD TDS calculation
            if ($ytd_data['gross'] > 0 && $month_num > 1) {
                // Employee has prior income this year - use YTD projection
                $tds_result = $this->tax_epf_calculator->calculate_tds_ytd(
                    $ytd_income = $ytd_data['gross'],
                    $current_month_gross = $gross_salary,
                    $current_month = $month_num,
                    $total_months = 12
                );
                $monthly_tds = $tds_result['monthly_tds'];
            } else {
                // First month of year or no prior payslips - use simple annualized approach
                $monthly_tds = $this->tax_epf_calculator->calculate_monthly_tds($gross_salary);
            }
            
            // Total deductions now include employee EPF, ESI, and TDS
            $total_with_epf_tds_esi = (float) $employee_epf + (float) $esi_deduction + (float) $monthly_tds + (float) $total_deduction + (float) $lop_deduction;
            
            $net_salary = $gross_salary - $total_with_epf_tds_esi;

            $data = array(
                'staff_id' => $staff['id'],
                'basic' => $basic,
                'da' => $da,
                'total_allowance' => $total_allowance,
                'total_deduction' => $total_deduction,
                'net_salary' => $net_salary,
                'payment_date' => date('Y-m-d'),
                'status' => 'generated',
                'month' => $month,
                'year' => $year,
                'tax' => round($monthly_tds, 2),  // Store TDS instead of hardcoded tax
                'leave_deduction' => $lop_deduction,
                'actual_lop_days' => $actual_lop_days,
                'adjusted_lop_days' => $adjusted_lop_days,
                'net_lop_days' => $net_lop_days,
                // New EPF fields
                'epf_wage' => $epf_wage,
                'employee_epf' => round($employee_epf, 2),
                'employer_pf' => round($employer_pf, 2),
                'employer_eps' => round($employer_eps, 2),
                'tax_regime' => 'new',
            );

            if (!empty($staff['payslip_id']) && $overwrite) {
                $data['id'] = $staff['payslip_id'];
            }

            $payslipid = $this->payroll_model->createPayslip($data);

            // Update monthly balance with payslip reference if LOP was adjusted
            if ($adjusted_lop_days > 0 && $payslipid) {
                $this->db->where('staff_id', $staff['id']);
                $this->db->where('year', (int)$year);
                $this->db->where('month', (int)$month_numeric);
                $this->db->where('used_for_lop_adjustment >', 0);
                $this->db->update('staff_monthly_leave_balance', ['payslip_id' => $payslipid]);
            }

            // Always delete old allowances/deductions when recalculating an existing payslip
            if (!empty($staff['payslip_id']) && $overwrite) {
                $this->db->where('payslip_id', $payslipid)->delete('payslip_allowance');
            } else if (!empty($staff['payslip_id'])) {
                // Even if not overwriting, delete deductions to prevent duplicates during recalculation
                $this->db->where('payslip_id', $payslipid)->where('cal_type', 'negative')->delete('payslip_allowance');
            }

            if (!empty($allowances)) {
                foreach ($allowances as $allowance) {
                    // Allowance data structure
                    $allowance_data = array(
                        'payslip_id'        => $payslipid,
                        'allowance_type'    => $allowance['allowance_type'],
                        'amount'            => $allowance['amount'],
                        'staff_id'          => $staff['id'],
                        'cal_type'          => $allowance['cal_type'],
                    );
                    $this->payroll_model->add_allowance($allowance_data);
                }
            }

            // Add increment as a temporary earning if it's an increment month
            if ($is_increment_month && $increment_amount > 0) {
                $temp_allowance_code = $this->payroll_model->getStatutoryAllowanceCode('TEMP');
                if ($temp_allowance_code) {
                    $increment_allowance = array(
                        'payslip_id'        => $payslipid,
                        'allowance_type'    => $temp_allowance_code,  // TEMP code from database
                        'amount'            => round($increment_amount, 2),
                        'staff_id'          => $staff['id'],
                        'cal_type'          => 'positive',  // Increment is an earning
                    );
                    $this->payroll_model->add_allowance($increment_allowance);
                } else {
                    // Fallback to 'TEMP' if database code not found (shouldn't happen)
                    $increment_allowance = array(
                        'payslip_id'        => $payslipid,
                        'allowance_type'    => 'TEMP',
                        'amount'            => round($increment_amount, 2),
                        'staff_id'          => $staff['id'],
                        'cal_type'          => 'positive',
                    );
                    $this->payroll_model->add_allowance($increment_allowance);
                }
            }

            // Delete existing statutory deductions (EPF/ESI/TDS) before adding new ones to prevent duplicates
            // Get statutory allowance type codes from database
            $statutory_types = $this->payroll_model->getStatutoryAllowanceTypes();
            $statutory_codes = array_keys($statutory_types); // Get all statutory codes: EPF, ESI, TDS, PT, etc.
            
            if (!empty($statutory_codes)) {
                $this->db->where('payslip_id', $payslipid)
                    ->where_in('allowance_type', $statutory_codes)
                    ->delete('payslip_allowance');
            }

            // ======== ADD STATUTORY DEDUCTIONS TO PAYSLIP ALLOWANCE ========
            // All statutory deductions are now calculated above and need to be added to payslip_allowance table
            // Using allowance type codes from payroll_allowance_types table
            // - EPF: Calculated only if UAN is available (done above via library)
            // - ESI: Calculated only if ESI_no is available (done above)
            // - TDS: Income tax calculation (done above via library)
            
            // Get allowance type codes from database for all three statutory deductions
            $epf_code = $this->payroll_model->getStatutoryAllowanceCode('EPF');
            $esi_code = $this->payroll_model->getStatutoryAllowanceCode('ESI');
            $tds_code = $this->payroll_model->getStatutoryAllowanceCode('TDS');
            
            // Add EPF deduction (already calculated above via tax_epf_calculator library)
            if (!empty($employee_epf) && $employee_epf > 0 && $epf_code) {
                $epf_data = array(
                    'payslip_id'        => $payslipid,
                    'allowance_type'    => $epf_code,  // Use code from database
                    'amount'            => round($employee_epf, 2),
                    'staff_id'          => $staff['id'],
                    'cal_type'          => 'negative',
                );
                $this->payroll_model->add_allowance($epf_data);
            }
            
            // Add ESI deduction (already calculated above)
            if (!empty($esi_deduction) && $esi_deduction > 0 && $esi_code) {
                $esi_data = array(
                    'payslip_id'        => $payslipid,
                    'allowance_type'    => $esi_code,  // Use code from database
                    'amount'            => round($esi_deduction, 2),
                    'staff_id'          => $staff['id'],
                    'cal_type'          => 'negative',
                );
                $this->payroll_model->add_allowance($esi_data);
            }

            // Add TDS (Income Tax) deduction (already calculated above via library)
            if (!empty($monthly_tds) && $monthly_tds > 0 && $tds_code) {
                $tds_data = array(
                    'payslip_id'        => $payslipid,
                    'allowance_type'    => $tds_code,  // Use code from database
                    'amount'            => round($monthly_tds, 2),
                    'staff_id'          => $staff['id'],
                    'cal_type'          => 'negative',
                );
                $this->payroll_model->add_allowance($tds_data);
            }

            if (!empty($staff['payslip_id']) && $overwrite) {
                $updated_existing++;
            } else {
                $generated++;
            }
        }

        $message = '<div class="alert alert-success text-center">Bulk payroll calculated. Generated: ' . $generated . '.</div>';
        if ($updated_existing > 0) {
            $message .= '<div class="alert alert-info text-center">Overwritten payslips: ' . $updated_existing . '.</div>';
        }
        if ($skipped_existing > 0) {
            $message .= '<div class="alert alert-warning text-center">Skipped existing payslips: ' . $skipped_existing . '.</div>';
        }
        $this->session->set_flashdata('msg', $message);

        redirect('admin/payroll/search/' . $month . '/' . $year . '/' . $role);
    }

    public function paymentRecord()
    {
        $month              = $this->input->get_post("month");
        $year               = $this->input->get_post("year");
        $id                 = $this->input->get_post("staffid");
        $searchEmployee     = $this->payroll_model->searchPayment($id, $month, $year);
        $data['result']     = $searchEmployee;
        $data['net_salary'] = amountFormat($searchEmployee['net_salary']);
        $data['monthlist']  = $this->customlib->getMonthDropdown();
        $data["month"]      = $data['monthlist'][$month];
        $data["year"]       = $year;
        echo json_encode($data);
    }

    public function calculatepreview()
    {
        $this->load->library('tax_epf_calculator');
        $this->load->model('payroll_increment_model');
        $this->load->model('payroll_model');
        $allowance_types = $this->payroll_model->getAllowanceTypes(null, false);
        $special_allowance_type_id = 0;
        if (!empty($allowance_types)) {
            foreach ($allowance_types as $type) {
                $code = strtoupper(trim($type['allowance_code'] ?? ''));
                $name = strtolower(trim($type['allowance_name'] ?? ''));
                if ($code === 'SA' || $name === 'special allowance') {
                    $special_allowance_type_id = (int) $type['id'];
                    break;
                }
            }
        }

        $staff_id = (int) $this->input->post('staff_id');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $month_numeric = date('n', strtotime($year . '-' . $month . '-01'));
        $basic = $this->input->post('basic');
        $basic = $basic ? convertCurrencyFormatToBaseAmount($basic) : 0;

        $allowance_type_id = $this->input->post('allowance_type_id');
        $allowance_amount = $this->input->post('allowance_amount');
        $deduction_type_id = $this->input->post('deduction_type_id');
        $deduction_amount = $this->input->post('deduction_amount');

        $total_allowance = 0;
        $total_deduction = 0;
        $da = 0;
        $increment_amount = 0;
        $is_increment_month = false;

        // Check if this is an increment month
        $increment = $this->payroll_increment_model->getApprovedIncrementForMonth($staff_id, $month_numeric, $year);
        if ($increment) {
            $is_increment_month = true;
            if ($increment['increment_type'] === 'Fixed') {
                $increment_amount = (float) $increment['increment_amount'];
            } elseif ($increment['increment_type'] === 'Percentage') {
                $increment_amount = round($basic * ($increment['increment_percentage'] / 100), 2);
            }
        }

        $allowance_types = $this->payroll_model->getAllowanceTypes(null, false);
        $allowance_type_map = [];
        foreach ($allowance_types as $type) {
            $allowance_type_map[(int) $type['id']] = $type;
        }

        if (!empty($allowance_type_id)) {
            foreach ($allowance_type_id as $i => $type_id) {
                $type_id = (int) $type_id;
                if ($type_id <= 0) {
                    continue;
                }

                $amount = isset($allowance_amount[$i]) ? convertCurrencyFormatToBaseAmount($allowance_amount[$i]) : 0;
                $type = $allowance_type_map[$type_id] ?? null;
                if (!$type) {
                    continue;
                }

                $code = strtoupper(trim($type['allowance_code'] ?? ''));
                $name = strtolower(trim($type['allowance_name'] ?? ''));

                if ($code === 'BASIC') {
                    continue;
                }

                if ($code === 'DA' || strpos($name, 'dearness') !== false) {
                    $da = (float) $amount;
                }

                if (!empty($type['is_statutory'])) {
                    continue;
                }

                $total_allowance += (float) $amount;
            }
        }

        // Add increment to total_allowance if it's increment month
        if ($is_increment_month && $increment_amount > 0) {
            $total_allowance += $increment_amount;
        }

        if (!empty($deduction_type_id)) {
            foreach ($deduction_type_id as $j => $type_id) {
                $type_id = (int) $type_id;
                if ($type_id <= 0) {
                    continue;
                }

                $amount = isset($deduction_amount[$j]) ? convertCurrencyFormatToBaseAmount($deduction_amount[$j]) : 0;
                $type = $allowance_type_map[$type_id] ?? null;
                if (!$type) {
                    continue;
                }

                if (!empty($type['is_statutory'])) {
                    continue;
                }

                $total_deduction += (float) $amount;
            }
        }

        $month_num = $month_numeric;
        $newdate = date('Y-m-d', strtotime($year . '-' . $month . ' +1 month'));
        $monthAttendanceData = $this->monthAttendance($newdate, 3, $staff_id);
        $monthLeaves = $this->monthLeaves($newdate, 3, $staff_id);
        $lop_summary = $this->getPayrollLopSummary($monthAttendanceData, $monthLeaves, $month, $year, $staff_id);

        $actual_lop_days = !empty($lop_summary['lop_days']) ? (float) $lop_summary['lop_days'] : 0;
        $adjusted_lop_days = 0;
        $net_lop_days = $actual_lop_days;

        if ($actual_lop_days > 0) {
            $adjusted_result = $this->payroll_model->previewLOPWithMonthlyBalance($staff_id, $actual_lop_days, $month_num, $year);
            if (!empty($adjusted_result['success'])) {
                $net_lop_days = (float) $adjusted_result['net_lop_days'];
                $adjusted_lop_days = (float) $adjusted_result['adjusted_lop_days'];
            }
        }

        $gross_salary = (float) $basic + (float) $total_allowance;
        $lop_deduction = 0;
        if ($net_lop_days > 0) {
            $total_days_of_month = cal_days_in_month(CAL_GREGORIAN, (int) $month_num, (int) $year);
            $lop_deduction = ($gross_salary / $total_days_of_month) * $net_lop_days;
        }

        $epf_wage = 0;
        $employee_epf = 0;
        $employer_pf = 0;
        $employer_eps = 0;
        $esi_deduction = 0;

        $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
        if (!is_array($staff_data)) {
            $staff_data = [];
        }

        if (!empty($staff_data['uan_no']) && isset($staff_data['is_epf_enabled']) && (int) $staff_data['is_epf_enabled'] === 1) {
            $epf_wage = $this->tax_epf_calculator->calculate_epf_wage($basic, $da);
            $employee_epf = $this->tax_epf_calculator->calculate_employee_epf($epf_wage);
            $employer_pf = $this->tax_epf_calculator->calculate_employer_pf($epf_wage);
            $employer_eps = $this->tax_epf_calculator->calculate_employer_eps($epf_wage);
        }

        if (!empty($staff_data['esi_no']) && isset($staff_data['is_esi_enabled']) && (int) $staff_data['is_esi_enabled'] === 1) {
            $esi_base = $basic + $da;
            $esi_deduction = round($esi_base * 0.0075, 2);
        }

        $ytd_data = $this->payroll_model->getYTDIncome($staff_id, $year, $month_num - 1);
        if ($ytd_data['gross'] > 0 && $month_num > 1) {
            $tds_result = $this->tax_epf_calculator->calculate_tds_ytd(
                $ytd_income = $ytd_data['gross'],
                $current_month_gross = $gross_salary,
                $current_month = $month_num,
                $total_months = 12
            );
            $monthly_tds = $tds_result['monthly_tds'];
        } else {
            $monthly_tds = $this->tax_epf_calculator->calculate_monthly_tds($gross_salary);
        }

        $total_with_epf_tds_esi = (float) $employee_epf + (float) $esi_deduction + (float) $monthly_tds + (float) $total_deduction + (float) $lop_deduction;
        $net_salary = $gross_salary - $total_with_epf_tds_esi;

        $response = [
            'success' => true,
            'total_allowance' => round($total_allowance, 2),
            'total_deduction' => round($total_deduction, 2),
            'gross_salary' => round($gross_salary, 2),
            'leave_deduction' => round($lop_deduction, 2),
            'net_salary' => round($net_salary, 2),
            'epf_wage' => round($epf_wage, 2),
            'employee_epf' => round($employee_epf, 2),
            'employer_pf' => round($employer_pf, 2),
            'employer_eps' => round($employer_eps, 2),
            'tds' => round($monthly_tds, 2),
            'esi_deduction' => round($esi_deduction, 2),
            'actual_lop_days' => round($actual_lop_days, 2),
            'adjusted_lop_days' => round($adjusted_lop_days, 2),
            'net_lop_days' => round($net_lop_days, 2),
            'is_increment_month' => $is_increment_month,
            'increment_amount' => round($increment_amount, 2),
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function paymentStatus($status)
    {
        $id          = $this->input->get('id');
        $updateStaus = $this->payroll_model->updatePaymentStatus($status, $id);
        redirect("admin/payroll");
    }

    public function paymentSuccess()
    {
        $payment_mode = $this->input->post("payment_mode");
        $date         = $this->input->post("payment_date");
        $payment_date = date('Y-m-d', strtotime($date));
        $remark       = $this->input->post("remarks");
        $status       = 'paid';
        $payslipid    = $this->input->post("paymentid");
        $this->form_validation->set_rules('payment_mode', $this->lang->line('payment_mode'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('payment_date', $this->lang->line('payment_date'), 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'payment_mode' => form_error('payment_mode'),
                'payment_date' => form_error('payment_date'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $data = array('payment_mode' => $payment_mode, 'payment_date' => $this->customlib->dateFormatToYYYYMMDD($date), 'remark' => $remark, 'status' => $status);
            $this->payroll_model->paymentSuccess($data, $payslipid);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function payslipView()
    {
        $data["payment_mode"] = $this->payment_mode;
        $this->load->model("setting_model");
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result[0];
        $id                  = $this->input->post("payslipid");
        $result              = $this->payroll_model->getPayslip($id);
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        if (!empty($result)) {
            $allowance                  = $this->payroll_model->getAllowance($result["id"]);
            $data["allowance"]          = $allowance;
            $positive_allowance         = $this->payroll_model->getAllowance($result["id"], "positive");
            $data["positive_allowance"] = $positive_allowance;
            $negative_allowance         = $this->payroll_model->getAllowance($result["id"], "negative");
            $data["negative_allowance"] = $negative_allowance;
            $data["result"]             = $result;
            $this->load->view("admin/payroll/payslipview", $data);
        } else {
            echo "<div class='alert alert-info'>" . $this->lang->line('no_record_found') . "</div>";
        }
    }

    public function payslippdf()
    {
        $this->load->model("setting_model");
        $setting_result             = $this->setting_model->get();
        $data['settinglist']        = $setting_result[0];
        $id                         = 15;
        $result                     = $this->payroll_model->getPayslip($id);
        $allowance                  = $this->payroll_model->getAllowance($result["id"]);
        $data["allowance"]          = $allowance;
        $positive_allowance         = $this->payroll_model->getAllowance($result["id"], "positive");
        $data["positive_allowance"] = $positive_allowance;
        $negative_allowance         = $this->payroll_model->getAllowance($result["id"], "negative");
        $data["negative_allowance"] = $negative_allowance;
        $data["result"]             = $result;
        $this->load->view("admin/payroll/payslippdf", $data);
    }

    public function payrollreport()
    {
        $this->loadPayrollReport(['paid'], 'Paid Payroll Report', 'admin/payroll/payrollreport');
    }

    public function payrollreport_generated()
    {
        $this->loadPayrollReport(['generated'], 'Generated Payroll Report', 'admin/payroll/payrollreport_generated');
    }

    private function loadPayrollReport($status_filter, $report_title, $report_action)
    {
        if (!$this->rbac->hasPrivilege('payroll_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/human_resource');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/attendance_report');
        $month                = $this->input->post("month");
        $year                 = $this->input->post("year");
        $role                 = $this->input->post("role");
        $data["month"]        = $month;
        $data["year"]         = $year;
        $data["role_select"]  = $role;
        $data['monthlist']    = $this->customlib->getMonthDropdown();
        $data['yearlist']     = $this->payroll_model->payrollYearCount();
        $staffRole            = $this->staff_model->getStaffRole();
        $data["role"]         = $staffRole;
        $data["payment_mode"] = $this->payment_mode;
        $data['report_title'] = $report_title;
        $data['report_action'] = $report_action;

        $this->form_validation->set_rules('year', $this->lang->line('year'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view("layout/header", $data);
            $this->load->view("admin/payroll/payrollreport", $data);
            $this->load->view("layout/footer", $data);
        } else {
            $result = $this->payroll_model->getpayrollReport($month, $year, $role, $status_filter);
            $data["result"] = $result;
            $this->load->view("layout/header", $data);
            $this->load->view("admin/payroll/payrollreport", $data);
            $this->load->view("layout/footer", $data);
        }
    }

    public function deletepayroll($payslipid, $month, $year, $role = '')
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_delete')) {
            access_denied();
        }
        if (!empty($payslipid)) {
            $this->payroll_model->deletePayslip($payslipid);
        }

        redirect('admin/payroll/search/' . $month . "/" . $year . "/" . $role);
    }

    public function revertpayroll($payslipid, $month, $year, $role = '')
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_delete')) {
            access_denied();
        }
        if (!empty($payslipid)) {
            $this->payroll_model->revertPayslipStatus($payslipid);
        }
        redirect('admin/payroll/search/' . $month . "/" . $year . "/" . $role);

    }

    public function bulkupload()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_view')) {
            access_denied();
        }
        $this->load->view("layout/header");
        $this->load->view("admin/payroll/bulkupload");
        $this->load->view("layout/footer");
    }

    public function bulkimport()
    {
        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');
        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('year', $this->lang->line('year'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->bulkupload();
        } else {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $file_path = $this->session->userdata('csv_path');
            $this->load->library('CSVReader');
            $result = $this->csvreader->parse_file($file_path, true);

            if (!empty($result)) {
                $this->db->trans_start();
                $header = array_keys($result[0]);
                $updated_count = 0;
                $inserted_count = 0;
                $skipped_count = 0;
                $skipped_staff = [];
                foreach ($result as $row) {
                    // Try to find staff by employee_id first, then by biometric_id
                    $staff = $this->staff_model->get_by_employee_id(trim($row['staff_id']));
                    if (!$staff) {
                        $staff = $this->staff_model->get_by_biometric_id(trim($row['staff_id']));
                        if ($staff) {
                            $staff = (array) $staff; // Convert object to array for consistency
                        }
                    }
                    
                    if ($staff) {

                        $existing_payslip = $this->payroll_model->getPayslipByStaffMonthYear($staff['id'], $month, $year);

                        $total_allowance = 0;
                        $total_deduction = 0;
                        $allowances = [];
                        foreach ($header as $key) {
                            if ($key != 'staff_id') {
                                $amount = $row[$key];
                                if (is_numeric($amount) && $amount != 0) {
                                    if ($amount > 0) {
                                        $total_allowance += $amount;
                                        $allowances[] = ['type' => $key, 'amount' => $amount, 'cal_type' => 'positive'];
                                    } else {
                                        $total_deduction += abs($amount);
                                        $allowances[] = ['type' => $key, 'amount' => abs($amount), 'cal_type' => 'negative'];
                                    }
                                }
                            }
                        }

                        // Update basic salary if BASIC column is provided
                        $basic_salary = $staff['basic_salary'];
                        if (isset($row['BASIC']) && is_numeric($row['BASIC']) && $row['BASIC'] > 0) {
                            $basic_salary = $row['BASIC'];
                            // Update staff table with new basic salary
                            $this->db->where('id', $staff['id']);
                            $this->db->update('staff', ['basic_salary' => $basic_salary]);
                        }

                        $data = array(
                            'staff_id' => $staff['id'],
                            'basic' => $basic_salary,
                            'total_allowance' => $total_allowance,
                            'total_deduction' => $total_deduction,
                            'net_salary' => $basic_salary + $total_allowance - $total_deduction,
                            'payment_date' => date("Y-m-d"),
                            'status' => 'generated',
                            'month' => $month,
                            'year' => $year,
                            'tax' => 0,
                            'leave_deduction' => '0',
                        );

                        if($existing_payslip){
                            $updated_count++;
                            $data['id'] = $existing_payslip->id;
                            $payslipid = $this->payroll_model->createPayslip($data);
                            $this->payroll_model->deletePayslipAllowances($payslipid);

                        }else{
                            $inserted_count++;
                            $payslipid = $this->payroll_model->createPayslip($data);
                        }


                        foreach ($allowances as $allowance) {
                            $allowance_data = array(
                                'payslip_id' => $payslipid,
                                'allowance_type' => $allowance['type'],
                                'amount' => $allowance['amount'],
                                'staff_id' => $staff['id'],
                                'cal_type' => $allowance['cal_type'],
                            );
                            $this->payroll_model->add_allowance($allowance_data);
                        }
                    } else {
                        $skipped_count++;
                        $skipped_staff[] = $row['staff_id'];
                    }
                }
                $this->db->trans_complete();
                $message = '<div class="alert alert-success text-center">' . $this->lang->line('records_found_in_csv_file_total') . ' ' . count($result) . '. ' . $this->lang->line('records_imported_successfully') . ' (' . $this->lang->line('updated') . ': ' . $updated_count . ', ' . $this->lang->line('inserted') . ': ' . $inserted_count . ')' . '</div>';
                if ($skipped_count > 0) {
                    $message .= '<div class="alert alert-warning text-center">Skipped ' . $skipped_count . ' records for the following staff IDs: ' . implode(', ', $skipped_staff) . '</div>';
                }
                $this->session->set_flashdata('msg', $message);
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('no_record_found') . '</div>');
            }
            redirect('admin/payroll/bulkupload');
        }
    }

    public function handle_csv_upload()
    {
        // Ensure PHP limits are set for large file uploads
        ini_set('upload_max_filesize', '50M');
        ini_set('post_max_size', '50M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '600');
        
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $temp = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            
            // Check for upload errors
            if ($_FILES["file"]["error"] > 0) {
                switch ($_FILES["file"]["error"]) {
                    case 1:
                    case 2:
                        $error .= "The uploaded file exceeds the maximum allowed size (50MB). Please reduce the file size or contact administrator.";
                        break;
                    case 3:
                        $error .= "The uploaded file was only partially uploaded.";
                        break;
                    case 4:
                        $error .= "No file was uploaded.";
                        break;
                    default:
                        $error .= "Error uploading file: " . $_FILES["file"]["error"];
                        break;
                }
            }
            
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error: Please select CSV file only.";
            }
            
            if ($error == "") {
                $file_name = $_FILES["file"]["name"];
                $file_size = $_FILES["file"]["size"];
                $file_tmp = $_FILES["file"]["tmp_name"];
                $file_type = $_FILES["file"]["type"];

                $path = "uploads/payroll_import/";
                $this->customlib->ensureDirectoryExists($path);
                $file_path = $path . $file_name;
                move_uploaded_file($file_tmp, $file_path);
                $this->session->set_userdata('csv_path', $file_path);
                return true;
            } else {
                $this->form_validation->set_message('handle_csv_upload', $error);
                return false;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', "Please select a file.");
            return false;
        }
    }

    /**
     * EPF and TDS Settings page
     */
    public function settings()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_view')) {
            access_denied();
        }

        // Load tax and EPF configuration
        $this->config->load('tax_epf');
        
        $data['page_title'] = 'EPF, ESI & TDS Settings';
        $data['new_tax_regime'] = $this->config->item('new_tax_regime');
        $data['old_tax_regime'] = $this->config->item('old_tax_regime');
        $data['epf'] = $this->config->item('epf');
        $data['esi'] = $this->config->item('esi');
        $data['tax_regime'] = $this->config->item('tax_regime');
        
        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/settings", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * =====================================================================
     * SALARY INCREMENT MANAGEMENT SECTION
     * =====================================================================
     */

    /**
     * List all salary increments (management interface)
     */
    public function increments()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');

        $this->load->model('payroll_increment_model');

        $data['page_title'] = 'Salary Increment Management';
        $data['staff_id'] = $this->input->post('staff_id');
        $data['status_filter'] = $this->input->post('status_filter');

        // Get list of increments based on filters
        if ($data['staff_id']) {
            $data['increments'] = $this->payroll_increment_model->getStaffIncrements($data['staff_id'], $data['status_filter']);
        } elseif ($data['status_filter'] === 'Pending') {
            $data['increments'] = $this->payroll_increment_model->getPendingIncrements();
        } else {
            $data['increments'] = array();
        }

        $user_type = $this->staff_model->getStaffRole();
        $data['classlist'] = $user_type;
        $data['stafflist'] = $this->staff_model->getAll(null, 1);

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/increment_list", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * Show form to add new salary increment
     */
    public function add_increment($staff_id = null)
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');

        $this->load->model('payroll_increment_model');

        $data['page_title'] = 'Add Salary Increment';
        $data['staff_id'] = $staff_id;

        if ($staff_id) {
            $staff = $this->staff_model->get($staff_id);
            $data['staff'] = $staff;
            $data['current_salary'] = isset($staff['basic_salary']) ? $staff['basic_salary'] : 0;
            $data['last_increment'] = $this->payroll_increment_model->getLatestApprovedIncrement($staff_id);
        }

        // Fetch only active staff for increment addition
        $all_staff = $this->staff_model->getAll(null, 1);
        $active_staff = array();
        if (!empty($all_staff)) {
            foreach ($all_staff as $staff) {
                if (isset($staff['is_active']) && $staff['is_active'] == 1) {
                    // Query SA directly by staff_id from payslip_allowance
                    $staff['special_allowance'] = 0;
                    $this->db->select('amount');
                    $this->db->from('payslip_allowance');
                    $this->db->where('staff_id', $staff['id']);
                    $this->db->where('allowance_type', 'SA');
                    $this->db->order_by('id', 'DESC');
                    $this->db->limit(1);
                    $sa_row = $this->db->get()->row_array();
                    if (!empty($sa_row) && isset($sa_row['amount'])) {
                        $staff['special_allowance'] = (float) $sa_row['amount'];
                    }
                    $active_staff[] = $staff;
                }
            }
        }
        $data['stafflist'] = $active_staff;

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/add_increment", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * Save salary increment record
     */
    public function save_increment()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $this->form_validation->set_rules('staff_id', 'Staff Member', 'required|integer');
        $this->form_validation->set_rules('effective_date', 'Effective Date', 'required');
        $this->form_validation->set_rules('increment_type', 'Increment Type', 'required|in_list[Fixed,Percentage]');
        $this->form_validation->set_rules('merge_with', 'Merge With', 'required|in_list[basic,special_allowance]');

        if ($this->input->post('increment_type') === 'Fixed') {
            $this->form_validation->set_rules('increment_amount', 'Increment Amount', 'required|numeric');
        } else {
            $this->form_validation->set_rules('increment_percentage', 'Increment Percentage', 'required|numeric|less_than_equal_to[100]|greater_than[0]');
        }

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/payroll/add_increment/' . $this->input->post('staff_id'));
        } else {
            $this->load->model('payroll_increment_model');

            $staff_id = $this->input->post('staff_id');
            $effective_date = $this->input->post('effective_date');

            // Check if staff already has increment or bonus in same month
            $existing = $this->payroll_increment_model->checkExistingForMonth($staff_id, $effective_date);
            if ($existing) {
                $type_label = $existing['is_recurring'] == 1 ? 'Increment' : 'Bonus';
                $this->session->set_flashdata('error', "Staff already has a {$type_label} in this month. Only one increment or bonus allowed per month.");
                redirect('admin/payroll/add_increment/' . $staff_id);
                return;
            }

            $increment_data = array(
                'staff_id' => $staff_id,
                'effective_date' => $effective_date,
                'increment_type' => $this->input->post('increment_type'),
                'merge_with' => $this->input->post('merge_with'),
                'is_recurring' => $this->input->post('is_recurring') ? intval($this->input->post('is_recurring')) : 1,
                'remarks' => $this->input->post('remarks'),
            );

            if ($this->input->post('increment_type') === 'Fixed') {
                $increment_data['increment_amount'] = convertCurrencyFormatToBaseAmount($this->input->post('increment_amount'));
            } else {
                $increment_data['increment_percentage'] = $this->input->post('increment_percentage');
            }

            $result = $this->payroll_increment_model->addIncrement($increment_data);

            if ($result) {
                $type_label = $increment_data['is_recurring'] == 1 ? 'increment' : 'bonus';
                $this->session->set_flashdata('success', "Salary {$type_label} added successfully. Awaiting HR approval.");
                redirect('admin/payroll/increments?status_filter=Pending');
            } else {
                $this->session->set_flashdata('error', 'Failed to add salary increment.');
                redirect('admin/payroll/add_increment/' . $staff_id);
            }
        }
    }

    /**
     * List pending increments for approval
     */
    public function pending_increments()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');

        $this->load->model('payroll_increment_model');

        $data['page_title'] = 'Pending Salary Increments';
        $data['increments'] = $this->payroll_increment_model->getPendingIncrements();

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/pending_increments", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * Approve salary increment
     */
    public function approve_increment($increment_id)
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        $this->load->model('payroll_increment_model');

        $userdata = $this->customlib->getUserData();
        $admin_id = (isset($userdata['id'])) ? $userdata['id'] : 0;
        $result = $this->payroll_increment_model->approveIncrement($increment_id, $admin_id);

        if ($result) {
            $this->session->set_flashdata('success', 'Salary increment approved successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to approve salary increment.');
        }

        redirect('admin/payroll/pending_increments');
    }

    /**
     * Bulk approve salary increments (AJAX)
     */
    public function bulk_approve_increments()
    {
        try {
            if (!$this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                exit;
            }

            if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            $this->load->model('payroll_increment_model');

            // Get increment_ids - could come as array or via FormData as increment_ids[]
            $increment_ids = $this->input->post('increment_ids');
            
            // Debug logging
            error_log("=== BULK APPROVE DEBUG ===");
            error_log("Post data received: " . print_r($_POST, true));
            error_log("increment_ids from input->post(): " . print_r($increment_ids, true));
            
            if (empty($increment_ids)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No increments selected']);
                exit;
            }

            // Ensure increment_ids is an array
            if (!is_array($increment_ids)) {
                $increment_ids = [$increment_ids];
            }

            error_log("Total increments to process: " . count($increment_ids));
            error_log("Increment IDs: " . implode(', ', $increment_ids));

            $userdata = $this->customlib->getUserData();
            $admin_id = (isset($userdata['id'])) ? $userdata['id'] : 0;
            $approved_count = 0;
            $failed_count = 0;
            $errors = [];

            error_log("Admin ID: " . $admin_id);
            error_log("Starting loop with " . count($increment_ids) . " items");

            foreach ($increment_ids as $key => $increment_id) {
                $increment_id = (int) $increment_id;
                error_log("Loop iteration $key: Processing ID=$increment_id");
                
                if ($increment_id > 0) {
                    // Get increment details first for better error tracking
                    $this->db->select('id, staff_id, approval_status, increment_amount');
                    $this->db->where('id', $increment_id);
                    $increment_details = $this->db->get('staff_increment_history')->row_array();
                    
                    if (!$increment_details) {
                        error_log("  - Increment #$increment_id NOT FOUND");
                        $failed_count++;
                        $errors[] = 'Increment #' . $increment_id . ' not found';
                        continue;
                    }
                    
                    error_log("  - Found increment: Status={$increment_details['approval_status']}, Amount={$increment_details['increment_amount']}");
                    
                    $result = $this->payroll_increment_model->approveIncrement($increment_id, $admin_id);
                    if ($result) {
                        error_log("  - APPROVED ✓");
                        $approved_count++;
                    } else {
                        error_log("  - FAILED");
                        $failed_count++;
                        $errors[] = 'Increment #' . $increment_id . ' (Status: ' . $increment_details['approval_status'] . ')';
                    }
                } else {
                    error_log("  - Invalid ID: $increment_id");
                    $failed_count++;
                }
            }

            error_log("Loop complete - Approved: $approved_count, Failed: $failed_count");

            header('Content-Type: application/json');
            if ($approved_count > 0 || $failed_count > 0) {
                $message = $approved_count . ' increment(s) approved successfully';
                if ($failed_count > 0) {
                    $message .= ' | ' . $failed_count . ' could not be approved';
                }
                $success = ($approved_count > 0);
                
                error_log("Sending response: " . json_encode(['success' => $success, 'message' => $message, 'approved' => $approved_count, 'failed' => $failed_count]));
                
                echo json_encode(['success' => $success, 'message' => $message, 'approved' => $approved_count, 'failed' => $failed_count, 'errors' => $errors]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No increments were processed']);
            }
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            error_log('Bulk approve error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Reject salary increment
     */
    public function reject_increment($increment_id)
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        $this->load->model('payroll_increment_model');

        $result = $this->payroll_increment_model->rejectIncrement($increment_id);

        if ($result) {
            $this->session->set_flashdata('success', 'Salary increment rejected.');
        } else {
            $this->session->set_flashdata('error', 'Failed to reject salary increment.');
        }

        redirect('admin/payroll/pending_increments');
    }

    /**
     * Delete salary increment (only pending records)
     */
    public function delete_increment($increment_id)
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_delete')) {
            access_denied();
        }

        $this->load->model('payroll_increment_model');

        $result = $this->payroll_increment_model->deleteIncrement($increment_id);

        if ($result) {
            $this->session->set_flashdata('success', 'Salary increment deleted successfully.');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete salary increment. (Can only delete pending records)');
        }

        redirect('admin/payroll/increments');
    }

    /**
     * Show form to add bulk salary increments for multiple staff
     */
    public function bulk_add_increment()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');

        $this->load->model('payroll_increment_model');

        $data['page_title'] = 'Bulk Add Salary Increment';
        
        // Get only active staff with basic salary
        $all_staff = $this->staff_model->getAll(null, 1);
        $active_staff = array();
        
        // Explicitly filter for active staff (is_active = 1)
        if (!empty($all_staff)) {
            foreach ($all_staff as $staff) {
                if (isset($staff['is_active']) && $staff['is_active'] == 1) {
                    // Query SA directly by staff_id from payslip_allowance
                    $staff['special_allowance'] = 0;
                    $this->db->select('amount');
                    $this->db->from('payslip_allowance');
                    $this->db->where('staff_id', $staff['id']);
                    $this->db->where('allowance_type', 'SA');
                    $this->db->order_by('id', 'DESC');
                    $this->db->limit(1);
                    $sa_row = $this->db->get()->row_array();
                    if (!empty($sa_row) && isset($sa_row['amount'])) {
                        $staff['special_allowance'] = (float) $sa_row['amount'];
                    }
                    $active_staff[] = $staff;
                }
            }
        }
        
        $data['stafflist'] = $active_staff;
        
        // Get roles for filtering
        $user_type = $this->staff_model->getStaffRole();
        $data['roles'] = $user_type;

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/bulk_add_increment", $data);
        $this->load->view("layout/footer", $data);
    }

    /**
     * Save bulk salary increment records
     */
    public function save_bulk_increment()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $this->form_validation->set_rules('staff_ids[]', 'Staff Members', 'required');
        $this->form_validation->set_rules('effective_date', 'Effective Date', 'required');
        $this->form_validation->set_rules('merge_with', 'Merge With', 'required|in_list[basic,special_allowance]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/payroll/bulk_add_increment');
        } else {
            $this->load->model('payroll_increment_model');

            $staff_ids = $this->input->post('staff_ids');
            $effective_date = $this->input->post('effective_date');
            $merge_with = $this->input->post('merge_with');
            $remarks = $this->input->post('remarks');
            $increment_types = $this->input->post('increment_type') ?? array();
            $increment_amounts = $this->input->post('increment_amount') ?? array();
            $is_recurring_values = $this->input->post('is_recurring') ?? array();
            
            // Check override flag - can come from hidden field OR checkbox
            $override_existing = false;
            $override_field = $this->input->post('override_existing');
            $override_checkbox = $this->input->post('override_checkbox');
            
            // Debug logging
            error_log("=== BULK INCREMENT OVERRIDE DEBUG ===");
            error_log("override_existing field: " . var_export($override_field, true));
            error_log("override_checkbox field: " . var_export($override_checkbox, true));
            error_log("String comparison override_field == '1': " . var_export(($override_field == '1'), true));
            error_log("Int comparison override_field == 1: " . var_export(($override_field == 1), true));
            error_log("Checkbox null check: " . var_export(($override_checkbox !== null), true));
            
            // If override_field is explicitly '1', or checkbox exists in POST, enable override
            if ($override_field === '1' || $override_checkbox === 'on' || $override_checkbox === '1') {
                $override_existing = true;
                error_log("Override enabled - WILL DELETE EXISTING");
            } else {
                error_log("Override disabled - WILL REJECT DUPLICATES");
            }
            error_log("Final override_existing value: " . var_export($override_existing, true));

            $success_count = 0;
            $error_messages = array();

            foreach ($staff_ids as $staff_id) {
                // Check if staff already has increment or bonus in same month
                $existing = $this->payroll_increment_model->checkExistingForMonth($staff_id, $effective_date);
                if ($existing) {
                    // If override flag is set, delete the existing increment
                    if ($override_existing) {
                        $this->payroll_increment_model->deleteIncrement($existing['id']);
                    } else {
                        $type_label = $existing['is_recurring'] == 1 ? 'Increment' : 'Bonus';
                        $error_messages[] = "Staff ID {$staff_id}: Already has {$type_label} in this month";
                        continue;
                    }
                }

                // Get increment type and amount for this staff
                $increment_type = isset($increment_types[$staff_id]) ? $increment_types[$staff_id] : 'Fixed';
                $increment_value = isset($increment_amounts[$staff_id]) ? $increment_amounts[$staff_id] : 0;
                $is_recurring = isset($is_recurring_values[$staff_id]) ? intval($is_recurring_values[$staff_id]) : 1;

                // Validate increment value
                if (empty($increment_value) || floatval($increment_value) <= 0) {
                    $error_messages[] = "Staff ID {$staff_id}: Invalid increment amount";
                    continue;
                }

                $increment_data = array(
                    'staff_id' => $staff_id,
                    'effective_date' => $effective_date,
                    'increment_type' => $increment_type,
                    'merge_with' => $merge_with,
                    'is_recurring' => $is_recurring,
                    'remarks' => $remarks,
                );

                if ($increment_type === 'Fixed') {
                    $increment_data['increment_amount'] = convertCurrencyFormatToBaseAmount($increment_value);
                } else {
                    $increment_data['increment_percentage'] = floatval($increment_value);
                }

                $result = $this->payroll_increment_model->addIncrement($increment_data);

                if ($result) {
                    $success_count++;
                } else {
                    $error_messages[] = "Staff ID {$staff_id}: Failed to add increment";
                }
            }

            if ($success_count > 0) {
                $this->session->set_flashdata('success', "Successfully added {$success_count} increment(s)/bonus(es). Awaiting HR approval.");
            }
            
            if (!empty($error_messages)) {
                $this->session->set_flashdata('error', "Errors: " . implode(" | ", $error_messages));
            }

            redirect('admin/payroll/pending_increments');
        }
    }

    /**
     * Fix duplicate EPF/ESI deductions that may have been created by running bulk calculate multiple times
     */
    public function clean_duplicate_deductions()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        // Find all payslips with duplicate EPF/ESI deductions
        $duplicate_query = "
            SELECT payslip_id, allowance_type, COUNT(*) as cnt, GROUP_CONCAT(id ORDER BY id) as ids
            FROM payslip_allowance 
            WHERE cal_type='negative' AND allowance_type IN ('EPF', 'ESI')
            GROUP BY payslip_id, allowance_type 
            HAVING cnt > 1
        ";

        $result = $this->db->query($duplicate_query);
        $duplicates = $result->result_array();

        $deleted_count = 0;
        $message = '';

        if (!empty($duplicates)) {
            foreach ($duplicates as $dup) {
                // Get the IDs as array
                $ids = array_map('trim', explode(',', $dup['ids']));
                
                // Keep the first one, delete the rest
                $delete_ids = array_slice($ids, 1);
                
                // Delete duplicate records
                $this->db->where_in('id', $delete_ids);
                $this->db->delete('payslip_allowance');
                
                $deleted_count += $this->db->affected_rows();
            }
            
            $message = "Fixed " . count($duplicates) . " payslips with duplicate deductions. Deleted " . $deleted_count . " duplicate record(s).";
        } else {
            $message = "No duplicate deductions found. All payslips are clean.";
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-info text-center">' . $message . '</div>');
        redirect('admin/payroll');
    }
}

