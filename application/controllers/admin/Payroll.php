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

        $holidays = (int) ($attendance['holiday'] ?? 0);
        $sundays = (int) ($attendance['sunday'] ?? 0);
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int) $month_num, (int) $year);
        $working_days = max(0, $days_in_month - $holidays - $sundays);

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
        $late_to_half_day = isset($lop_rules['late_to_half_day']) ? (int) $lop_rules['late_to_half_day'] : 0;
        $permission_to_half_day = isset($lop_rules['permission_to_half_day']) ? (int) $lop_rules['permission_to_half_day'] : 0;

        $late_half_days = $late_to_half_day > 0 ? floor($late / $late_to_half_day) : 0;
        $permission_count = $first_half_permission + $second_half_permission;
        $permission_half_days = $permission_to_half_day > 0 ? floor($permission_count / $permission_to_half_day) : 0;

        $lop_days = $absent_working
            + ($half_day * $half_day_weight)
            + (($first_half_absent + $second_half_absent) * 0.5)
            + ($late_half_days * 0.5)
            + ($permission_half_days * 0.5);

        $total_present = $present + ($half_day * $half_day_weight);
        $total_absent = $absent_working + ($half_day * $half_day_weight);
        $paid_days = $total_present;

        return [
            'month_key' => $month_key,
            'days_in_month' => $days_in_month,
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
        $context = $this->getWorkingDayContext($month_num, $year);
        $working_day_dates = $context['working_day_dates'];
        $absent_count = 0;

        foreach ($working_day_dates as $work_date) {
            $attendance_row = $this->staffattendancemodel->searchStaffattendance($work_date, $staff_id, false);
            $attendance_key = $attendance_row['key'] ?? null;
            if ($attendance_key === 'A') {
                $absent_count++;
            }
        }

        return $absent_count;
    }

    private function getWorkingDayContext($month_num, $year)
    {
        $this->load->model("holiday_model");
        $this->load->model("setting_model");

        $holidays = $this->holiday_model->get();
        $settings = $this->setting_model->getSetting();

        $num_of_days = cal_days_in_month(CAL_GREGORIAN, (int) $month_num, (int) $year);

        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int) $settings->isSecondSaturdayHoliday : 0;

        $second_saturday_date = null;
        if ($isSecondSaturdayWeekend) {
            $saturdayCount = 0;
            for ($day = 1; $day <= $num_of_days; $day++) {
                $dateObj = new DateTime($year . "-" . $month_num . "-" . sprintf("%02d", $day));
                if ((int) $dateObj->format('w') == 6) {
                    $saturdayCount++;
                    if ($saturdayCount == 2) {
                        $second_saturday_date = $dateObj->format('Y-m-d');
                        break;
                    }
                }
            }
        }

        $weekend_day_dates = [];
        for ($day = 1; $day <= $num_of_days; $day++) {
            $dateStr = $year . "-" . $month_num . "-" . sprintf("%02d", $day);
            $dayOfWeek = (int) date('w', strtotime($dateStr));
            if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date && $dateStr === $second_saturday_date)) {
                $weekend_day_dates[] = $dateStr;
            }
        }
        $weekend_day_dates = array_values(array_unique($weekend_day_dates));

        $official_holiday_dates = [];
        foreach ($holidays as $holiday_value) {
            $from_date = new DateTime($holiday_value['from_date']);
            $to_date = new DateTime($holiday_value['to_date']);

            $current = clone $from_date;
            while ($current <= $to_date) {
                if ($current->format('m') == $month_num && $current->format('Y') == $year) {
                    $official_holiday_dates[] = $current->format('Y-m-d');
                }
                $current->modify('+1 day');
            }
        }
        $official_holiday_dates = array_values(array_unique($official_holiday_dates));

        $holiday_dates = array_values(array_unique(array_filter($official_holiday_dates, function ($dateStr) use ($weekend_day_dates) {
            return !in_array($dateStr, $weekend_day_dates, true);
        })));

        $working_day_dates = [];
        for ($day = 1; $day <= $num_of_days; $day++) {
            $dateStr = $year . "-" . $month_num . "-" . sprintf("%02d", $day);
            if (!in_array($dateStr, $weekend_day_dates, true) && !in_array($dateStr, $holiday_dates, true)) {
                $working_day_dates[] = $dateStr;
            }
        }

        return [
            'working_day_dates' => $working_day_dates,
            'weekend_day_dates' => $weekend_day_dates,
            'holiday_dates' => $holiday_dates,
        ];
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
                'leave_deduction' => '0',
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


            $official_holiday_dates = [];
            $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
            $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
            $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int) $settings->isSecondSaturdayHoliday : 0;

            $second_saturday_date = null;
            if ($isSecondSaturdayWeekend) {
                $saturdayCount = 0;
                for ($day = 1; $day <= $num_of_days; $day++) {
                    $dateObj = new DateTime($year . "-" . $month . "-" . sprintf("%02d", $day));
                    if ((int) $dateObj->format('w') == 6) {
                        $saturdayCount++;
                        if ($saturdayCount == 2) {
                            $second_saturday_date = $dateObj->format('Y-m-d');
                            break;
                        }
                    }
                }
            }

            $weekend_day_dates = [];
            for ($day = 1; $day <= $num_of_days; $day++) {
                $att_date = $year . "-" . $month . "-" . sprintf("%02d", $day);
                $dayOfWeek = (int) date('w', strtotime($att_date));
                if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date && $att_date === $second_saturday_date)) {
                    $weekend_day_dates[] = $att_date;
                }
            }
            $weekend_day_dates = array_values(array_unique($weekend_day_dates));

            // Collect official holiday dates from annual_calendar
            foreach ($holidays as $holiday_key => $holiday_value) {
                $from_date = new DateTime($holiday_value['from_date']);
                $to_date = new DateTime($holiday_value['to_date']);
                
                $current = clone $from_date;
                while ($current <= $to_date) {
                    if ($current->format('m') == $month && $current->format('Y') == $year) {
                        $official_holiday_dates[] = $current->format('Y-m-d');
                    }
                    $current->modify('+1 day');
                }
            }
            $official_holiday_dates = array_unique($official_holiday_dates);

            // This is for display in the "H" column for "other leaves"
            $holidays_for_H_column = array_diff($official_holiday_dates, $weekend_day_dates);


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
                    $s = $this->payroll_model->count_attendance_obj($month, $year, $emp, $attendance_type_id_for_query);
                    $r[$att_key] = $s;
                } else {
                    $r[$att_key] = 0;
                }
            }


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
            $leave_count = $this->staff_model->count_leave($month, $year, $emp);
            if (!empty($leave_count["tl"])) {
                $l = $leave_count["tl"];
            } else {
                $l = "0";
            }

            $record[$month] = $l;
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
                'leave_deduction'        => '0',
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

        $this->form_validation->set_rules('year', $this->lang->line('year'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view("layout/header", $data);
            $this->load->view("admin/payroll/payrollreport", $data);
            $this->load->view("layout/footer", $data);
        } else {
            $result = $this->payroll_model->getpayrollReport($month, $year, $role);
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
