<?php

class Payroll extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
        $this->config->load("mailsms");
        $this->config->load("payroll");
        $this->load->library('mailsmsconf');
        $this->load->library('media_storage');
        $this->config_attendance = $this->config->item('attendence');
        $this->staff_attendance  = $this->config->item('staffattendance');
        $this->payment_mode      = $this->config->item('payment_mode');
        $this->load->model("payroll_model");
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
        $last_payslip = $this->payroll_model->getLastPayslip($id);

        if(!empty($last_payslip)){
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
        $data['employee_payroll'] = $employee_payroll;
        $date                     = $employee_payroll['year'] . "-" . $employee_payroll['month'];
        $data['result']           = $searchEmployee;
        $data["month"]            = $employee_payroll['month'];
        $data["year"]             = $employee_payroll['year'];

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
        foreach ($holidays as $holiday_value) {
            $from_date = new DateTime($holiday_value['from_date']);
            $to_date = new DateTime($holiday_value['to_date']);
            if ($to_date < $range_start || $from_date > $range_end) {
                continue;
            }
            $current = clone $from_date;
            while ($current <= $to_date) {
                if ($current >= $range_start && $current <= $range_end) {
                    $official_holiday_dates[] = $current->format('Y-m-d');
                }
                $current->modify('+1 day');
            }
        }
        $official_holiday_dates = array_values(array_unique($official_holiday_dates));

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
                $allowance_type    = $this->input->post("allowance_type");
                $deduction_type    = $this->input->post("deduction_type");
                $allowance_prev_id = $this->input->post("allowance_prev_id");
                $deduction_prev_id = $this->input->post("deduction_prev_id");
                $allowance_amount  = $this->input->post("allowance_amount");
                $deduction_amount  = $this->input->post("deduction_amount");

                if (!empty($allowance_type)) {

                    $i                        = 0;
                    $insert_payslip_allowance = array();
                    $update_payslip_allowance = array();
                    foreach ($allowance_type as $key => $all) {
                        
                        if($allowance_amount[$i]){
                                $allowanceamount = convertCurrencyFormatToBaseAmount($allowance_amount[$i]);
                        }else{
                                $allowanceamount = 0;  
                        }
                                
                        if ($allowance_prev_id[$i] != 0) {
                            $update_payslip_allowance[] = array(
                                'id'             => $allowance_prev_id[$i],
                                'payslip_id'     => $payslipid,
                                'allowance_type' => $allowance_type[$i],
                                'amount'         => $allowanceamount,
                                'staff_id'       => $staff_id,
                                'cal_type'       => "positive",
                            );
                        } else {
                            
                            $insert_payslip_allowance[] = array(
                                'payslip_id'     => $payslipid,
                                'allowance_type' => $allowance_type[$i],
                                'amount'         => $allowanceamount,
                                'staff_id'       => $staff_id,
                                'cal_type'       => "positive",
                            );
                        }

                        $i++;
                    }

                    $insert_payslip_allowance = $this->payroll_model->update_allowance($insert_payslip_allowance, $update_payslip_allowance, $allowance_prev_id, $payslipid, 'positive');
                } else {

                    $insert_payslip_allowance = $this->payroll_model->update_allowance([], [], [0], $payslipid, 'positive');
                }

                if (!empty($deduction_type)) {
                    $j                        = 0;
                    $insert_payslip_allowance = array();
                    $update_payslip_allowance = array();

                    foreach ($deduction_type as $key => $type) {
                        
                                if($deduction_amount[$j]){
                                        $deductionamount = convertCurrencyFormatToBaseAmount($deduction_amount[$j]);
                                }else{
                                        $deductionamount = 0;  
                                }
                                
                        if ($deduction_prev_id[$j] != 0) {
                            

                            
                            $update_payslip_allowance[] = array(
                                'id'             => $deduction_prev_id[$j],
                                'payslip_id'     => $payslipid,
                                'allowance_type' => $deduction_type[$j],
                                'amount'         => $deductionamount,
                                'staff_id'       => $staff_id,
                                'cal_type'       => "negative",
                            );
                        } else {
                            
                            
                            $insert_payslip_allowance[] = array(
                                'payslip_id'     => $payslipid,
                                'allowance_type' => $deduction_type[$j],
                                'amount'         => $deductionamount,
                                'staff_id'       => $staff_id,
                                'cal_type'       => "negative",
                            );
                        }
                        $j++;
                    }

                    $insert_payslip_allowance = $this->payroll_model->update_allowance($insert_payslip_allowance, $update_payslip_allowance, $deduction_prev_id, $payslipid, 'negative');
                } else {
                    $insert_payslip_allowance = $this->payroll_model->update_allowance([], [], [0], $payslipid, 'negative');
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
                $allowance_type   = $this->input->post("allowance_type");
                $deduction_type   = $this->input->post("deduction_type");
                $allowance_amount = $this->input->post("allowance_amount");
                $deduction_amount = $this->input->post("deduction_amount");
                if (!empty($allowance_type)) {

                    $i = 0;
                    foreach ($allowance_type as $key => $all) {
                        
                        if($allowance_amount[$i]){
                                $allowanceamount = convertCurrencyFormatToBaseAmount($allowance_amount[$i]);
                        }else{
                                $allowanceamount = 0;  
                        } 
                        
                        $all_data = array(
                            'payslip_id'     => $payslipid,
                            'allowance_type' => $allowance_type[$i],
                            'amount'         => $allowanceamount,
                            'staff_id'       => $staff_id,
                            'cal_type'       => "positive",
                        );

                        $insert_payslip_allowance = $this->payroll_model->add_allowance($all_data);

                        $i++;
                    }
                }

                if (!empty($deduction_type)) {
                    $j = 0;
                    foreach ($deduction_type as $key => $type) {
                        
                        if($deduction_amount[$j]){
                                $deductionamount = convertCurrencyFormatToBaseAmount($deduction_amount[$j]);
                        }else{
                                $deductionamount = 0;  
                        }
                        
                        $type_data = array('payslip_id' => $payslipid,
                            'allowance_type'                => $deduction_type[$j],
                            'amount'                        => $deductionamount,
                            'staff_id'                      => $staff_id,
                            'cal_type'                      => "negative",
                        );

                        $insert_payslip_allowance = $this->payroll_model->add_allowance($type_data);

                        $j++;
                    }
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

        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $role = $this->input->post('role');
        $overwrite = $this->input->post('bulk_overwrite') ? true : false;

        if (empty($month) || empty($year) || $month === 'select' || $year === 'select') {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Please select month and year for bulk calculation.</div>');
            redirect('admin/payroll');
        }

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
            $tax = 0;

            if (!empty($last_payslip)) {
                if ((float) $basic <= 0 && !empty($last_payslip['basic'])) {
                    $basic = $last_payslip['basic'];
                }
                $tax = !empty($last_payslip['tax']) ? $last_payslip['tax'] : 0;
                $allowances = $this->payroll_model->getAllowance($last_payslip['id']);
                foreach ($allowances as $allowance) {
                    if ($allowance['cal_type'] === 'positive') {
                        $total_allowance += (float) $allowance['amount'];
                    } else {
                        $total_deduction += (float) $allowance['amount'];
                    }
                }
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

            // Process LOP adjustment with monthly balance tracking
            if ($actual_lop_days > 0) {
                $adjusted_result = $this->payroll_model->processLOPWithMonthlyBalance($staff['id'], $actual_lop_days, $month, $year);
                if ($adjusted_result !== false && is_array($adjusted_result) && $adjusted_result['success']) {
                    $net_lop_days = (float) $adjusted_result['net_lop_days'];
                    $adjusted_lop_days = (float) $adjusted_result['adjusted_lop_days'];
                }
            }

            $gross_salary = (float) $basic + (float) $total_allowance;
            $lop_deduction = 0;
            if (!empty($lop_summary['working_days']) && $net_lop_days > 0) {
                $lop_deduction = ($gross_salary / (float) $lop_summary['working_days']) * $net_lop_days;
            }

            $net_salary = $gross_salary - (float) $total_deduction - (float) $lop_deduction - (float) $tax;

            $data = array(
                'staff_id' => $staff['id'],
                'basic' => $basic,
                'total_allowance' => $total_allowance,
                'total_deduction' => $total_deduction,
                'net_salary' => $net_salary,
                'payment_date' => date('Y-m-d'),
                'status' => 'generated',
                'month' => $month,
                'year' => $year,
                'tax' => $tax,
                'leave_deduction' => $lop_deduction,
                'actual_lop_days' => $actual_lop_days,
                'adjusted_lop_days' => $adjusted_lop_days,
                'net_lop_days' => $net_lop_days,
            );

            if (!empty($staff['payslip_id']) && $overwrite) {
                $data['id'] = $staff['payslip_id'];
            }

            $payslipid = $this->payroll_model->createPayslip($data);

            // Update monthly balance with payslip reference if LOP was adjusted
            if ($adjusted_lop_days > 0 && $payslipid) {
                $this->db->where('staff_id', $staff['id']);
                $this->db->where('year', (int)$year);
                $this->db->where('month', (int)$month);
                $this->db->where('used_for_lop_adjustment >', 0);
                $this->db->update('staff_monthly_leave_balance', ['payslip_id' => $payslipid]);
            }

            if (!empty($staff['payslip_id']) && $overwrite) {
                $this->db->where('payslip_id', $payslipid)->delete('payslip_allowance');
            }

            if (!empty($allowances)) {
                foreach ($allowances as $allowance) {
                    $allowance_data = array(
                        'payslip_id' => $payslipid,
                        'allowance_type' => $allowance['allowance_type'],
                        'amount' => $allowance['amount'],
                        'staff_id' => $staff['id'],
                        'cal_type' => $allowance['cal_type'],
                    );
                    $this->payroll_model->add_allowance($allowance_data);
                }
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
                    $staff = $this->staff_model->get_by_employee_id(trim($row['staff_id']));
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

                        $data = array(
                            'staff_id' => $staff['id'],
                            'basic' => $staff['basic_salary'],
                            'total_allowance' => $total_allowance,
                            'total_deduction' => $total_deduction,
                            'net_salary' => $staff['basic_salary'] + $total_allowance - $total_deduction,
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
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $temp = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error: " . $_FILES["file"]["error"] . "<br>";
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
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
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
}
