<?php
// Version: 2026-02-18-FINAL - All code changes: TEMP increment fix, ESI calculation, Excel footer

class Payroll extends Admin_Controller
{
    private $payrollFyStartMonth = null;

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
        $this->load->model("payroll_onetime_deduction_model");
        $this->load->model("staff_model");
        $this->load->model('staffattendancemodel');
        $this->load->model('day_status_model'); // Day-lock for OD/CPL payroll override
        $this->payroll_status     = $this->config->item('payroll_status');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /**
     * Return FY start year for a given calendar month/year.
     */
    private function getFyStartYear($year, $month_num, $fy_start_month = 4)
    {
        $year = (int) $year;
        $month_num = (int) $month_num;
        $fy_start_month = (int) $fy_start_month;

        if ($year <= 0) {
            $year = (int) date('Y');
        }
        if ($month_num < 1 || $month_num > 12) {
            $month_num = (int) date('n');
        }
        if ($fy_start_month < 1 || $fy_start_month > 12) {
            $fy_start_month = 4;
        }

        return ($month_num >= $fy_start_month) ? $year : ($year - 1);
    }

    private function getConfiguredPayrollFyStartMonth()
    {
        if ($this->payrollFyStartMonth !== null) {
            return (int) $this->payrollFyStartMonth;
        }

        $fy_start_month = 4;
        if ($this->db->field_exists('payroll_fy_start_month', 'sch_settings')) {
            $row = $this->db->select('payroll_fy_start_month')->from('sch_settings')->limit(1)->get()->row();
            if ($row && isset($row->payroll_fy_start_month)) {
                $candidate = (int) $row->payroll_fy_start_month;
                if ($candidate >= 1 && $candidate <= 12) {
                    $fy_start_month = $candidate;
                }
            }
        }

        $this->payrollFyStartMonth = $fy_start_month;
        return $fy_start_month;
    }

    /**
     * Opening YTD values should apply only for the tagged FY cycle.
     */
    private function getApplicableOpeningYtd($staff_row, $year, $month_num, $fy_start_month = 4)
    {
        $opening_income = isset($staff_row['opening_ytd_income']) ? max(0, (float) $staff_row['opening_ytd_income']) : 0;
        $opening_tax = isset($staff_row['opening_ytd_tax_deducted']) ? max(0, (float) $staff_row['opening_ytd_tax_deducted']) : 0;
        $opening_fy_start_year = isset($staff_row['opening_ytd_fy_start_year']) ? (int) $staff_row['opening_ytd_fy_start_year'] : 0;
        $current_fy_start_year = $this->getFyStartYear($year, $month_num, $fy_start_month);

        if ($opening_fy_start_year > 0 && $opening_fy_start_year === $current_fy_start_year) {
            return array(
                'income' => $opening_income,
                'tax' => $opening_tax,
            );
        }

        return array(
            'income' => 0,
            'tax' => 0,
        );
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
        $data["is_calculated"] = false; // Keep create form editable for selected month
        $data["existing_payslip_id"] = 0;
        $selected_month_payslip = $this->payroll_model->getPayslipByStaffMonthYear($id, $month, $year);

        if (!empty($selected_month_payslip) && !empty($selected_month_payslip->id)) {
            $data["existing_payslip_id"] = (int) $selected_month_payslip->id;
            if ((float) $selected_month_payslip->basic > 0) {
                $data['result']['basic_salary'] = (float) $selected_month_payslip->basic;
            }
            $data["earnings"]   = $this->payroll_model->getAllowance((int) $selected_month_payslip->id, 'positive');
            $data["deductions"] = $this->payroll_model->getAllowance((int) $selected_month_payslip->id, 'negative');
        } else {
            $last_payslip = $this->payroll_model->getLastPayslip($id);
            if (!empty($last_payslip)) {
                if ((float) $last_payslip['basic'] > 0) {
                    $data['result']['basic_salary'] = (float) $last_payslip['basic'];
                }
                $data["earnings"] = $this->payroll_model->getAllowance((int) $last_payslip['id'], 'positive');
            }
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
        $data['deduction_types'] = $this->payroll_model->getAllowanceTypes('deduction', false);

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
        $employee_payroll['basic'] = $this->resolvePayrollBasicAmount(
            $employee_payroll['basic'] ?? 0,
            $searchEmployee,
            (int) $employee_payroll['staff_id']
        );

        $this->ensureBasicAllowanceExists((int) $id, (int) $employee_payroll['staff_id'], (float) $employee_payroll['basic']);
        
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
        $data["is_calculated"]    = false; // Allow editable individual payroll recalculation

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

        $data['payroll_lop_display'] = $this->resolvePayrollLopValues(
            (int) $employee_payroll['staff_id'],
            $employee_payroll['month'],
            $employee_payroll['year'],
            (array) $data['payroll_lop_summary'],
            (array) $employee_payroll,
            false
        );
        
        // Load standardized allowance types for dropdowns
        $data['earning_types'] = $this->payroll_model->getAllowanceTypes('earning', true);
        $data['deduction_types'] = $this->payroll_model->getAllowanceTypes('deduction', false);
        
        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/edit", $data);
        $this->load->view("layout/footer", $data);
    }

    private function ensureBasicAllowanceExists($payslip_id, $staff_id, $basic_amount)
    {
        if ($payslip_id <= 0 || $staff_id <= 0) {
            return;
        }

        $basic_amount = (float) $basic_amount;

        $existing = $this->db->select('id')
            ->from('payslip_allowance')
            ->where('payslip_id', $payslip_id)
            ->where('allowance_type', 'BASIC')
            ->where('cal_type', 'positive')
            ->limit(1)
            ->get()
            ->row_array();

        if (!empty($existing)) {
            $this->db->where('payslip_id', $payslip_id)
                ->where('allowance_type', 'BASIC')
                ->where('cal_type', 'positive')
                ->update('payslip_allowance', ['amount' => $basic_amount]);
            return;
        }

        $this->payroll_model->add_allowance([
            'payslip_id' => $payslip_id,
            'allowance_type' => 'BASIC',
            'amount' => $basic_amount,
            'staff_id' => $staff_id,
            'cal_type' => 'positive',
        ]);
    }

    private function getExistingProfessionalTaxAmount($payslip_id)
    {
        $payslip_id = (int) $payslip_id;
        if ($payslip_id <= 0) {
            return 0;
        }

        $row = $this->db->select('SUM(amount) AS total_pt')
            ->from('payslip_allowance')
            ->where('payslip_id', $payslip_id)
            ->where('cal_type', 'negative')
            ->where('allowance_type', 'PT')
            ->get()
            ->row_array();

        return isset($row['total_pt']) ? (float) $row['total_pt'] : 0;
    }

    private function getCommittedPayrollStatuses()
    {
        return ['paid', 'generated', 'no_attendance'];
    }

    private function resolvePayrollLopValues($staff_id, $month, $year, array $lop_summary = [], array $existing_payslip = [], $persist_adjustment = false)
    {
        $actual_lop_days = !empty($lop_summary['lop_days']) ? (float) $lop_summary['lop_days'] : 0.0;
        $month_num = (int) date('n', strtotime($year . '-' . $month . '-01'));
        $total_days_of_month = cal_days_in_month(CAL_GREGORIAN, max(1, $month_num), (int) $year);
        $existing_status = strtolower(trim((string) ($existing_payslip['status'] ?? '')));
        $has_committed_payslip = !empty($existing_payslip)
            && in_array($existing_status, $this->getCommittedPayrollStatuses(), true);

        if ($has_committed_payslip) {
            $actual_lop_days = (float) ($existing_payslip['actual_lop_days'] ?? $actual_lop_days);
            $adjusted_lop_days = (float) ($existing_payslip['adjusted_lop_days'] ?? 0);
            if ($adjusted_lop_days < 0) {
                $adjusted_lop_days = 0.0;
            }
            if ($adjusted_lop_days > $actual_lop_days) {
                $adjusted_lop_days = $actual_lop_days;
            }

            $net_lop_days = (float) ($existing_payslip['net_lop_days'] ?? max(0, round($actual_lop_days - $adjusted_lop_days, 2)));
            $net_lop_days = max(0, min($actual_lop_days, $net_lop_days));

            return [
                'actual_lop_days' => round($actual_lop_days, 2),
                'adjusted_lop_days' => round($adjusted_lop_days, 2),
                'net_lop_days' => round($net_lop_days, 2),
                'month_num' => $month_num,
                'total_days_of_month' => $total_days_of_month,
                'source' => 'stored',
            ];
        }

        $adjusted_lop_days = 0.0;
        $net_lop_days = $actual_lop_days;

        $is_full_month_lop = ($total_days_of_month > 0 && $actual_lop_days >= (float) $total_days_of_month);

        if (!$is_full_month_lop && ($persist_adjustment || $actual_lop_days > 0)) {
            $adjusted_result = $persist_adjustment
                ? $this->payroll_model->processLOPWithMonthlyBalance((int) $staff_id, $actual_lop_days, $month_num, (int) $year)
                : $this->payroll_model->previewLOPWithMonthlyBalance((int) $staff_id, $actual_lop_days, $month_num, (int) $year);

            if (!empty($adjusted_result['success'])) {
                $adjusted_lop_days = (float) ($adjusted_result['adjusted_lop_days'] ?? 0);
                if ($adjusted_lop_days < 0) {
                    $adjusted_lop_days = 0.0;
                }
                if ($adjusted_lop_days > $actual_lop_days) {
                    $adjusted_lop_days = $actual_lop_days;
                }
                $net_lop_days = max(0, round($actual_lop_days - $adjusted_lop_days, 2));
            }
        }

        return [
            'actual_lop_days' => round($actual_lop_days, 2),
            'adjusted_lop_days' => round($adjusted_lop_days, 2),
            'net_lop_days' => round($net_lop_days, 2),
            'month_num' => $month_num,
            'total_days_of_month' => $total_days_of_month,
            'source' => 'calculated',
        ];
    }

    private function mergeAmountIntoAllowanceCode(array &$allowances, $allowance_code, $amount, $staff_id)
    {
        $allowance_code = strtoupper(trim((string) $allowance_code));
        $amount = (float) $amount;
        if ($allowance_code === '' || $amount <= 0) {
            return;
        }

        foreach ($allowances as &$allowance_row) {
            $row_code = strtoupper(trim((string) ($allowance_row['allowance_type'] ?? '')));
            $row_type = strtolower(trim((string) ($allowance_row['cal_type'] ?? '')));
            if ($row_type === 'positive' && $row_code === $allowance_code) {
                $allowance_row['amount'] = (float) ($allowance_row['amount'] ?? 0) + $amount;
                return;
            }
        }
        unset($allowance_row);

        $allowances[] = [
            'allowance_type' => $allowance_code,
            'amount' => $amount,
            'staff_id' => $staff_id,
            'cal_type' => 'positive',
        ];
    }

    private function getManualDeductionCodeLookup()
    {
        static $manual_deduction_lookup = null;

        if ($manual_deduction_lookup !== null) {
            return $manual_deduction_lookup;
        }

        $manual_deduction_lookup = [];
        $types = $this->payroll_model->getAllowanceTypes(null, false);
        foreach ((array) $types as $type) {
            $code = strtoupper(trim((string) ($type['allowance_code'] ?? '')));
            $name = strtoupper(trim((string) ($type['allowance_name'] ?? '')));
            if ($code === '') {
                continue;
            }

            $is_statutory = !empty($type['is_statutory']);
            if (in_array($code, ['EPF', 'ESI', 'TDS'], true)) {
                continue;
            }

            if ($is_statutory && $code !== 'PT') {
                continue;
            }

            $manual_deduction_lookup[$code] = $code;
            $manual_deduction_lookup[$this->normalizeDeductionTypeKey($code)] = $code;
            if ($name !== '') {
                $manual_deduction_lookup[$name] = $code;
                $manual_deduction_lookup[$this->normalizeDeductionTypeKey($name)] = $code;
            }
        }

        // Always allow Professional Tax aliases for one-time uploads
        $manual_deduction_lookup['PT'] = 'PT';
        $manual_deduction_lookup['PROFESSIONAL TAX'] = 'PT';
        $manual_deduction_lookup[$this->normalizeDeductionTypeKey('PROFESSIONAL TAX')] = 'PT';
        $manual_deduction_lookup['P TAX'] = 'PT';
        $manual_deduction_lookup[$this->normalizeDeductionTypeKey('P TAX')] = 'PT';

        return $manual_deduction_lookup;
    }

    private function normalizeDeductionTypeKey($value)
    {
        $normalized = strtoupper(trim((string) $value));
        $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $normalized);
        $normalized = str_replace(['_', '-', '.'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }

    private function getOneTimeDeductionPayload($staff_id, $month, $year)
    {
        $month_num = (int) $month;
        if ($month_num < 1 || $month_num > 12) {
            $month_num = (int) date('n', strtotime($year . '-' . $month . '-01'));
        }

        $rows = $this->payroll_onetime_deduction_model->getByStaffMonth((int) $staff_id, $month_num, (int) $year);
        if (empty($rows)) {
            return ['rows' => [], 'total' => 0.0];
        }

        $allowed_codes = $this->getManualDeductionCodeLookup();
        $normalized_rows = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['deduction_type'] ?? '')));
            $amount = (float) ($row['amount'] ?? 0);
            if ($code === '' || $amount <= 0) {
                continue;
            }
            if (!isset($allowed_codes[$code])) {
                continue;
            }

            $normalized_rows[] = [
                'deduction_type' => $code,
                'amount' => $amount,
                'remarks' => isset($row['remarks']) ? (string) $row['remarks'] : null,
            ];
            $total += $amount;
        }

        return [
            'rows' => $normalized_rows,
            'total' => (float) $total,
        ];
    }

    private function addOneTimeDeductionsToPayslip($payslip_id, $staff_id, array $deduction_rows)
    {
        if ((int) $payslip_id <= 0 || (int) $staff_id <= 0 || empty($deduction_rows)) {
            return;
        }

        foreach ($deduction_rows as $row) {
            $code = strtoupper(trim((string) ($row['deduction_type'] ?? '')));
            $amount = (float) ($row['amount'] ?? 0);
            if ($code === '' || $amount <= 0) {
                continue;
            }

            $this->payroll_model->add_allowance([
                'payslip_id' => (int) $payslip_id,
                'allowance_type' => $code,
                'amount' => $amount,
                'staff_id' => (int) $staff_id,
                'cal_type' => 'negative',
            ]);
        }
    }

    private function getPayrollLopSummary($monthAttendance, $monthLeaves, $month, $year, $staff_id)
    {
        $month_num = date('m', strtotime($year . '-' . $month . '-01'));
        $month_key = '01-' . $month_num . '-' . $year;
        $attendance = $monthAttendance[$month_key] ?? reset($monthAttendance) ?? [];

        $period = $this->getPayrollPeriodRange($month, $year);
        $days_in_period = (int) ($attendance['days_in_period'] ?? $this->getDaysInRange($period['start_date'], $period['end_date']));
        $working_days = (int) ($attendance['working_days'] ?? 0);
        $context = null;
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

        $late_permission_counts = $this->getLateAndPermissionCountsRange($staff_id, $period['start_date'], $period['end_date']);
        if (!empty($late_permission_counts['late_computed'])) {
            $late = (int) $late_permission_counts['late'];
        }
        if (!empty($late_permission_counts['permission_computed'])) {
            $permission_count = (int) $late_permission_counts['permission_total'];
            $first_half_permission = (int) $late_permission_counts['first_half_permission'];
            $second_half_permission = (int) $late_permission_counts['second_half_permission'];
        } else {
            $permission_count = $first_half_permission + $second_half_permission;
        }

        $approved_leave = (float) ($monthLeaves[$month_num] ?? 0);

        $lop_rules = $this->config->item('lop_rules');
        $half_day_weight = isset($lop_rules['half_day_weight']) ? (float) $lop_rules['half_day_weight'] : 0.5;

        $max_late_allowed = max(0, isset($this->sch_setting_detail->max_late_allowed) ? (int) $this->sch_setting_detail->max_late_allowed : 0);
        $max_permission_allowed = max(0, isset($this->sch_setting_detail->max_permission_allowed) ? (int) $this->sch_setting_detail->max_permission_allowed : 0);

        $unused_late_quota = max(0, $max_late_allowed - $late);
        $unused_permission_quota = max(0, $max_permission_allowed - $permission_count);

        $effective_late_quota = $max_late_allowed + $unused_permission_quota;
        $effective_permission_quota = $max_permission_allowed + $unused_late_quota;

        $late_half_days = max(0, $late - $effective_late_quota);
        $permission_half_days = max(0, $permission_count - $effective_permission_quota);

        $late_permission_penalty = ($late_half_days + $permission_half_days) * $half_day_weight;

        $paid_leave_absent = $this->getPaidLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id);
        $weekend_lop_days = $this->getNonPayableWeekendCountRange($staff_id, $period['start_date'], $period['end_date'], $context);

        // Debit-direction (applyleave) absent days ALWAYS subtract from LOP in BOTH modes.
        // Staff explicitly consumed their balance for these days — they must never become LOP,
        // regardless of whether auto-adjust is on or off.
        $debit_leave_absent = $this->getDebitLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id, $context);

        // Pre-allotted (CL/ML: rcb=1) absent days only subtract in manual mode (setting=0).
        // In auto-adjust mode (setting=1), buildLopAdjustmentLeavePool handles CL/ML at payroll.
        $auto_adjust_preallotted = (int)($this->sch_setting_detail->auto_adjust_lop_with_preallotted_leaves ?? 0);
        $preallotted_leave_absent = ($auto_adjust_preallotted === 0)
            ? $this->getPreallottedLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id, $context)
            : 0.0;

        $total_present = max(0, $present + ($half_day * $half_day_weight) - $late_permission_penalty);
        $total_absent = $absent_working + ($half_day * $half_day_weight) + $late_permission_penalty;

        $lop_days = max(0, $total_absent - $debit_leave_absent - $preallotted_leave_absent)
            + (($first_half_absent + $second_half_absent) * $half_day_weight)
            + $weekend_lop_days;

        $paid_days = $total_present;
        $od_adjusted_days = 0.0;
        $od_carry_forward_days = 0.0;
        if ($lop_days >= 0) {
            $od_preview = $this->payroll_model->previewLOPWithMonthlyBalance($staff_id, (float) $lop_days, $month_num, $year);
            if (!empty($od_preview['success'])) {
                $od_adjusted_days = (float) ($od_preview['od_adjusted_days'] ?? 0);
                $od_carry_forward_days = (float) ($od_preview['od_carry_forward_days'] ?? 0);
            }
        }

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
            'debit_leave_absent' => $debit_leave_absent,
            'preallotted_leave_absent' => $preallotted_leave_absent,
            'holidays' => $holidays,
            'sundays' => $sundays,
            'late_half_days' => $late_half_days,
            'permission_half_days' => $permission_half_days,
            'weekend_lop_days' => $weekend_lop_days,
            'lop_days' => $lop_days,
            'paid_days' => $paid_days,
            'od_adjusted_days' => $od_adjusted_days,
            'od_carry_forward_days' => $od_carry_forward_days,
        ];
    }

    /**
     * Build attendance counts using the same punch-derived interpretation as report UI.
     */
    private function getDerivedAttendanceSummaryForPeriod($staff_id, $start_date, $end_date, $working_day_dates = null, $context = null)
    {
        if ($context === null) {
            $context = $this->getWorkingDayContextRange($start_date, $end_date);
        }

        if ($working_day_dates === null) {
            $working_day_dates = $context['working_day_dates'] ?? [];
        }

        $type_maps = $this->getPayrollAttendanceTypeMaps();
        $by_config_key = [];
        foreach ($this->staff_attendance as $att_key => $unused) {
            if ($att_key !== 'holiday') {
                $by_config_key[$att_key] = 0;
            }
        }

        $rows = $this->staffattendancemodel->getAttendanceRowsInRange($staff_id, $start_date, $end_date);
        $key_by_date = [];
        foreach ((array) $rows as $row) {
            $row_date = (string) ($row['date'] ?? '');
            if ($row_date === '') {
                continue;
            }

            $key_by_date[$row_date] = $this->getNormalizedAttendanceKeyForPayrollRow(
                (array) $row,
                (int) $staff_id,
                null,
                $type_maps
            );
        }

        $by_display_key = [];
        foreach ((array) $working_day_dates as $work_date) {
            $att_key = strtoupper(trim((string) ($key_by_date[$work_date] ?? 'A')));
            if ($att_key === '') {
                $att_key = 'A';
            }

            $by_display_key[$att_key] = (int) ($by_display_key[$att_key] ?? 0) + 1;

            $config_key = $type_maps['key_value_to_config_key'][$att_key] ?? null;
            if ($config_key !== null && array_key_exists($config_key, $by_config_key)) {
                $by_config_key[$config_key]++;
            }
        }

        return [
            'by_config_key' => $by_config_key,
            'by_display_key' => $by_display_key,
            'key_by_date' => $key_by_date,
        ];
    }

    private function getPayrollAttendanceTypeMaps()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $id_to_key_value = [];
        $key_value_to_config_key = [];
        $types = $this->staffattendancemodel->getStaffAttendanceType();
        foreach ((array) $types as $type_row) {
            $id = (int) ($type_row['id'] ?? 0);
            $key_value = strtoupper(trim((string) ($type_row['key_value'] ?? '')));
            $config_key = str_replace(' ', '_', strtolower((string) ($type_row['type'] ?? '')));

            if ($id > 0 && $key_value !== '') {
                $id_to_key_value[$id] = $key_value;
            }
            if ($key_value !== '') {
                $key_value_to_config_key[$key_value] = $config_key;
            }
        }

        $cache = [
            'id_to_key_value' => $id_to_key_value,
            'key_value_to_config_key' => $key_value_to_config_key,
        ];

        return $cache;
    }

    private function getAdminRoleIdForPayroll()
    {
        static $admin_role_id = null;
        if ($admin_role_id !== null) {
            return (int) $admin_role_id;
        }

        $admin_role_row = $this->db->query("SELECT id FROM roles WHERE LOWER(name)='admin' ORDER BY id ASC LIMIT 1")->row_array();
        $admin_role_id = isset($admin_role_row['id']) ? (int) $admin_role_row['id'] : 1;
        if ($admin_role_id <= 0) {
            $admin_role_id = 1;
        }

        return (int) $admin_role_id;
    }

    private function getPrimaryRoleIdForStaffPayroll($staff_id)
    {
        $role_row = $this->db->select('role_id')
            ->from('staff_roles')
            ->where('staff_id', (int) $staff_id)
            ->order_by('id', 'asc')
            ->limit(1)
            ->get()
            ->row_array();

        $role_id = (int) ($role_row['role_id'] ?? 0);
        if ($role_id <= 0) {
            $role_id = $this->getAdminRoleIdForPayroll();
        }

        return $role_id;
    }

    private function getRoleAttendanceSettingsForPayroll()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $this->load->model('staffAttendaceSetting_model');
        $raw = $this->staffAttendaceSetting_model->getRoleAttendanceSetting();
        $map = [];
        foreach ((array) $raw as $row) {
            $role_id = (int) ($row->role_id ?? 0);
            $type_id = (int) ($row->staff_attendence_type_id ?? 0);
            if ($role_id <= 0 || $type_id <= 0) {
                continue;
            }

            $map[$role_id][$type_id] = [
                'from' => $row->entry_time_from ?? null,
                'to' => $row->entry_time_to ?? null,
            ];
        }

        $cache = $map;
        return $cache;
    }

    private function getPayrollSettingsForAttendanceDerivation()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = $this->setting_model->getSetting();
        return $cache;
    }

    private function hasValidPunchForPayroll(array $attendance_row)
    {
        $in_time = trim((string) ($attendance_row['in_time'] ?? ''));
        $out_time = trim((string) ($attendance_row['out_time'] ?? ''));

        if ($in_time !== '' && $in_time !== '00:00:00') {
            return true;
        }
        if ($out_time !== '' && $out_time !== '00:00:00') {
            return true;
        }

        return false;
    }

    private function getNormalizedAttendanceKeyForPayrollRow(array $attendance_row, $staff_id, $role_id = null, $type_maps = null)
    {
        if ($type_maps === null) {
            $type_maps = $this->getPayrollAttendanceTypeMaps();
        }

        $attendance_key = strtoupper(trim((string) ($attendance_row['key'] ?? '')));
        if ($attendance_key === '') {
            $attendance_type_id = (int) ($attendance_row['staff_attendance_type_id'] ?? 0);
            if ($attendance_type_id > 0) {
                $attendance_key = strtoupper(trim((string) ($type_maps['id_to_key_value'][$attendance_type_id] ?? '')));
            }
        }

        $effective_role_id = (int) $role_id;
        if ($effective_role_id <= 0) {
            $effective_role_id = (int) ($attendance_row['role_id'] ?? 0);
        }
        if ($effective_role_id <= 0) {
            $effective_role_id = $this->getPrimaryRoleIdForStaffPayroll((int) $staff_id);
        }

        $role_settings = $this->getRoleAttendanceSettingsForPayroll();
        if (empty($role_settings[$effective_role_id])) {
            $effective_role_id = $this->getAdminRoleIdForPayroll();
        }

        // Keep payroll aligned with report UI: derive keys from biometric punch windows.
        $is_biometric = !empty($attendance_row['biometric_attendence']);
        $in_time = $attendance_row['in_time'] ?? '';
        $out_time = $attendance_row['out_time'] ?? '';
        if ($is_biometric && !empty($in_time)) {
            $derived = $this->deriveAttendanceKeyFromPunchesForPayroll(
                $in_time,
                $out_time,
                $effective_role_id,
                $role_settings,
                $this->getPayrollSettingsForAttendanceDerivation()
            );
            if ($derived !== null) {
                $attendance_key = $derived;
            }
        }

        if ($attendance_key === '') {
            $attendance_key = 'A';
        }

        if (in_array($attendance_key, ['P', 'FHL', 'SHL', 'FHP', 'SHP', 'HD'], true)
            && !$this->hasValidPunchForPayroll($attendance_row)) {
            $attendance_key = 'A';
        }

        return $attendance_key;
    }

    // Mirrors Attendencereports::_derive_att_key_from_punches.
    private function deriveAttendanceKeyFromPunchesForPayroll($in_time, $out_time, $role_id, $role_settings, $settings)
    {
        if (empty($in_time)) {
            return null;
        }

        if (empty($role_id)) {
            $role_id = 1;
        }

        $morning_session_status = 8;
        $afternoon_session_status = 9;
        $second_half_start = false;

        $morning_type_id = null;
        if (!empty($role_settings[$role_id])) {
            foreach ($role_settings[$role_id] as $type_id => $window) {
                if (!empty($window['from']) && !empty($window['to'])
                    && strtotime($in_time) >= strtotime($window['from'])
                    && strtotime($in_time) <= strtotime($window['to'])) {
                    $morning_type_id = (int) $type_id;
                    break;
                }
            }
        }

        if ($morning_type_id !== null) {
            if ($morning_type_id === 4) {
                $morning_session_status = 8;
                $afternoon_session_status = 1;
                $second_half_start = true;
            } elseif ($morning_type_id === 6) {
                $morning_session_status = 8;
                $afternoon_session_status = 6;
                $second_half_start = true;
            } else {
                $morning_session_status = $morning_type_id;
            }
        } else {
            $present_window = isset($role_settings[$role_id][1]) ? $role_settings[$role_id][1] : null;
            if ($present_window && !empty($present_window['from'])
                && strtotime($in_time) < strtotime($present_window['from'])) {
                $morning_session_status = 1;
            } else {
                $shl_window = isset($role_settings[$role_id][6]) ? $role_settings[$role_id][6] : null;
                if ($shl_window && !empty($shl_window['to'])
                    && strtotime($in_time) > strtotime($shl_window['to'])) {
                    $morning_session_status = 8;
                    $afternoon_session_status = 9;
                    $second_half_start = true;
                } else {
                    $morning_session_status = 8;
                }
            }
        }

        if (!empty($out_time) && !$second_half_start) {
            $shp_window = isset($role_settings[$role_id][7]) ? $role_settings[$role_id][7] : null;

            $in_shp = $shp_window && !empty($shp_window['from']) && !empty($shp_window['to'])
                && strtotime($out_time) >= strtotime($shp_window['from'])
                && strtotime($out_time) <= strtotime($shp_window['to']);

            if ($in_shp) {
                $afternoon_session_status = 7;
            } else {
                $second_half_floor_ts = null;
                foreach ([4, 6] as $second_half_type) {
                    if (!empty($role_settings[$role_id][$second_half_type]['from'])) {
                        $ts = strtotime($role_settings[$role_id][$second_half_type]['from']);
                        if ($ts !== false && ($second_half_floor_ts === null || $ts < $second_half_floor_ts)) {
                            $second_half_floor_ts = $ts;
                        }
                    }
                }

                if ($second_half_floor_ts === null && !empty($settings->morning_session_end_time)) {
                    $second_half_floor_ts = strtotime($settings->morning_session_end_time);
                }

                if ($second_half_floor_ts !== null && strtotime($out_time) < $second_half_floor_ts) {
                    $afternoon_session_status = 9;
                } else {
                    $present_cutoff = null;
                    if ($shp_window && !empty($shp_window['to'])) {
                        $present_cutoff = $shp_window['to'];
                    } elseif (!empty($settings->evening_session_end_time)) {
                        $present_cutoff = $settings->evening_session_end_time;
                    }

                    if ($present_cutoff && strtotime($out_time) >= strtotime($present_cutoff)) {
                        $afternoon_session_status = 1;
                    } else {
                        $afternoon_session_status = 9;
                    }
                }
            }
        }

        $morning_session_status = (int) $morning_session_status;
        $afternoon_session_status = (int) $afternoon_session_status;
        $first_half_present = in_array($morning_session_status, [1, 2, 5], true);
        $second_half_present = in_array($afternoon_session_status, [1, 6, 7], true);

        if ($first_half_present && $second_half_present) {
            if ($morning_session_status === 2) {
                return 'FHL';
            }
            if ($morning_session_status === 5) {
                return 'FHP';
            }
            if ($afternoon_session_status === 6) {
                return 'SHL';
            }
            if ($afternoon_session_status === 7) {
                return 'SHP';
            }
            return 'P';
        }

        if ($first_half_present || $second_half_present) {
            return 'HD';
        }

        return 'A';
    }

    private function getLateAndPermissionCountsRange($staff_id, $start_date, $end_date)
    {
        $summary = $this->getDerivedAttendanceSummaryForPeriod((int) $staff_id, $start_date, $end_date);
        $by_key = $summary['by_display_key'] ?? [];

        $late_count = (int) (($by_key['FHL'] ?? 0) + ($by_key['SHL'] ?? 0));
        $first_half_permission = (int) ($by_key['FHP'] ?? 0);
        $second_half_permission = (int) ($by_key['SHP'] ?? 0);
        $permission_total = $first_half_permission + $second_half_permission;

        // Payroll rule: second-half late punch-ins should consume late quota
        // even if the day is finally treated as half-day (HD) for attendance.
        $rows = $this->staffattendancemodel->getAttendanceRowsInRange((int) $staff_id, $start_date, $end_date);
        if (!empty($rows)) {
            $context = $this->getWorkingDayContextRange($start_date, $end_date);
            $working_lookup = array_fill_keys($context['working_day_dates'] ?? [], true);
            $type_maps = $this->getPayrollAttendanceTypeMaps();
            $role_id = $this->getPrimaryRoleIdForStaffPayroll((int) $staff_id);
            $role_settings = $this->getRoleAttendanceSettingsForPayroll();
            $shl_window = $role_settings[$role_id][6] ?? null;

            if (!empty($shl_window['from']) && !empty($shl_window['to'])) {
                $extra_second_half_late = 0;
                foreach ((array) $rows as $row) {
                    $row_date = (string) ($row['date'] ?? '');
                    if ($row_date === '' || !isset($working_lookup[$row_date])) {
                        continue;
                    }

                    if (empty($row['biometric_attendence'])) {
                        continue;
                    }

                    $normalized_key = $this->getNormalizedAttendanceKeyForPayrollRow((array) $row, (int) $staff_id, $role_id, $type_maps);
                    if ($normalized_key !== 'HD') {
                        continue;
                    }

                    $in_time = trim((string) ($row['in_time'] ?? ''));
                    if ($this->timeInRangeForPayroll($in_time, $shl_window['from'], $shl_window['to'])) {
                        $extra_second_half_late++;
                    }
                }

                $late_count += (int) $extra_second_half_late;
            }
        }

        return [
            'is_computed' => true,
            'late_computed' => true,
            'permission_computed' => true,
            'late' => $late_count,
            'permission_total' => $permission_total,
            'first_half_permission' => $first_half_permission,
            'second_half_permission' => $second_half_permission,
        ];
    }

    private function timeInRangeForPayroll($time, $from, $to)
    {
        if (empty($time) || empty($from) || empty($to)) {
            return false;
        }

        $base_date = date('Y-m-d');
        $time_ts = strtotime($base_date . ' ' . $time);
        $from_ts = strtotime($base_date . ' ' . $from);
        $to_ts = strtotime($base_date . ' ' . $to);

        if ($time_ts === false || $from_ts === false || $to_ts === false) {
            return false;
        }

        return ($time_ts >= $from_ts && $time_ts <= $to_ts);
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
        $summary = $this->getDerivedAttendanceSummaryForPeriod((int) $staff_id, $period['start_date'], $period['end_date'], $context['working_day_dates'], $context);
        $absent_count = (int) (($summary['by_display_key']['A'] ?? 0));

        // Day-lock adjustment: dates with PAID_PRESENT (e.g. OD approved) were biometrically
        // absent but must NOT be counted as absent for payroll purposes.
        $day_locks = $this->day_status_model->getDayStatusRange(
            (int) $staff_id, $period['start_date'], $period['end_date']
        );
        foreach ($day_locks as $locked_date => $lock_row) {
            if ($lock_row['payroll_impact'] === 'PAID_PRESENT') {
                // Only subtract if the biometric actually shows absent for that working day
                if (isset($context['working_day_dates']) && in_array($locked_date, $context['working_day_dates'], true)) {
                    $att_row = $this->staffattendancemodel->searchStaffattendance($locked_date, $staff_id, false);
                    $att_key = $this->getNormalizedAttendanceKeyForPayrollRow((array) $att_row, (int) $staff_id);
                    if ($att_key === 'A') {
                        $absent_count = max(0, $absent_count - 1);
                    }
                }
            }
        }

        return max(0, $absent_count);
    }

    private function isAbsentForWeekendBridge(array $attendance_row, $staff_id)
    {
        $attendance_key = $this->getNormalizedAttendanceKeyForPayrollRow((array) $attendance_row, (int) $staff_id);
        return !in_array($attendance_key, ['P', 'FHL', 'SHL', 'FHP', 'SHP', 'HD'], true);
    }

    private function getNonPayableWeekendCountRange($staff_id, $start_date, $end_date, $context = null)
    {
        if ($context === null) {
            $context = $this->getWorkingDayContextRange($start_date, $end_date);
        }

        $weekend_dates = $context['weekend_day_dates'] ?? [];
        $working_dates = $context['working_day_dates'] ?? [];

        if (empty($weekend_dates) || empty($working_dates)) {
            return 0;
        }

        sort($weekend_dates);

        $extended_start = date('Y-m-d', strtotime($start_date . ' -10 day'));
        $extended_end = date('Y-m-d', strtotime($end_date . ' +10 day'));
        $extended_context = $this->getWorkingDayContextRange($extended_start, $extended_end);
        $working_dates_extended = $extended_context['working_day_dates'] ?? $working_dates;
        sort($working_dates_extended);

        $attendance_cache = [];
        $non_payable_weekends = 0;

        foreach ($weekend_dates as $weekend_date) {
            $prev_working_date = null;
            $next_working_date = null;

            foreach ($working_dates_extended as $working_date) {
                if ($working_date < $weekend_date) {
                    $prev_working_date = $working_date;
                    continue;
                }

                if ($working_date > $weekend_date) {
                    $next_working_date = $working_date;
                    break;
                }
            }

            if (empty($prev_working_date) || empty($next_working_date)) {
                continue;
            }

            if (!isset($attendance_cache[$prev_working_date])) {
                $attendance_cache[$prev_working_date] = $this->staffattendancemodel->searchStaffattendance($prev_working_date, $staff_id, false);
            }
            if (!isset($attendance_cache[$next_working_date])) {
                $attendance_cache[$next_working_date] = $this->staffattendancemodel->searchStaffattendance($next_working_date, $staff_id, false);
            }

            $prev_absent = $this->isAbsentForWeekendBridge((array) ($attendance_cache[$prev_working_date] ?? []), (int) $staff_id);
            $next_absent = $this->isAbsentForWeekendBridge((array) ($attendance_cache[$next_working_date] ?? []), (int) $staff_id);

            if ($prev_absent && $next_absent) {
                $non_payable_weekends++;
            }
        }

        return (float) max(0, $non_payable_weekends);
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
        $approved_paid_leave_credits = $this->getApprovedPaidLeaveCreditsByRange($start_date, $end_date, $staff_id);
        $working_day_lookup = array_fill_keys($working_day_dates, true);

        // Day-lock lookup: dates with PAID_ABSENT (CPL-style) are absent-by-permission,
        // so they absorb LOP credit without requiring a biometric 'A' check.
        $day_locks = $this->day_status_model->getDayStatusRange((int) $staff_id, $start_date, $end_date);

        $paid_leave_absent = 0.0;
        foreach ($approved_paid_leave_credits as $leave_date => $credit) {
            if (!isset($working_day_lookup[$leave_date])) {
                continue;
            }
            // If a PAID_ABSENT day-lock exists for this date, count it directly
            // without checking biometric (override wins — absence is by approved leave).
            if (isset($day_locks[$leave_date]) && $day_locks[$leave_date]['payroll_impact'] === 'PAID_ABSENT') {
                $paid_leave_absent += max(0, min(1, (float) $credit));
                continue;
            }
            $attendance_row = $this->staffattendancemodel->searchStaffattendance($leave_date, $staff_id, false);
            $attendance_key = $this->getNormalizedAttendanceKeyForPayrollRow((array) $attendance_row, (int) $staff_id);
            if ($attendance_key === 'A') {
                $paid_leave_absent += max(0, min(1, (float) $credit));
            }
        }

        return $paid_leave_absent;
    }

    /**
     * Count absent working days that are covered by an approved pre-allotted
     * leave (requires_balance_check=1) such as CL or ML.
     * These days must be excluded from LOP because the balance is already
     * deducted at approval time via used_for_leave_application.
     */
    private function getPreallottedLeaveAbsentCountRange($start_date, $end_date, $staff_id, $context = null)
    {
        if ($context === null) {
            $context = $this->getWorkingDayContextRange($start_date, $end_date);
        }

        $working_day_dates = $context['working_day_dates'];
        $credits = $this->getApprovedPaidLeaveCreditsByRange($start_date, $end_date, $staff_id, true);
        if (empty($credits)) {
            return 0.0;
        }

        $working_day_lookup = array_fill_keys($working_day_dates, true);
        $absent_covered = 0.0;
        foreach ($credits as $leave_date => $credit) {
            if (!isset($working_day_lookup[$leave_date])) {
                continue;
            }
            $attendance_row = $this->staffattendancemodel->searchStaffattendance($leave_date, $staff_id, false);
            $attendance_key = $this->getNormalizedAttendanceKeyForPayrollRow((array) $attendance_row, (int) $staff_id);
            if ($attendance_key === 'A') {
                $absent_covered += max(0, min(1, (float) $credit));
            }
        }

        return $absent_covered;
    }

    /**
     * Count absent working days explicitly covered by an approved applyleave (debit-direction) request.
     * These days must ALWAYS be subtracted from raw LOP in both auto and manual modes,
     * because the staff already consumed their balance for those specific days.
     */
    private function getDebitLeaveAbsentCountRange($start_date, $end_date, $staff_id, $context = null)
    {
        if (!$this->db->field_exists('leave_direction', 'staff_leave_request')) {
            return 0.0; // Column doesn't exist on this instance — no debit-direction leaves possible.
        }

        if ($context === null) {
            $context = $this->getWorkingDayContextRange($start_date, $end_date);
        }

        $credits = $this->getApprovedPaidLeaveCreditsByRange($start_date, $end_date, $staff_id, false, true);
        if (empty($credits)) {
            return 0.0;
        }

        $working_day_lookup = array_fill_keys($context['working_day_dates'], true);

        // Day-lock lookup for PAID_ABSENT override (same as getPaidLeaveAbsentCountRange)
        $day_locks = $this->day_status_model->getDayStatusRange((int) $staff_id, $start_date, $end_date);

        $absent_covered = 0.0;
        foreach ($credits as $leave_date => $credit) {
            if (!isset($working_day_lookup[$leave_date])) {
                continue;
            }
            if (isset($day_locks[$leave_date]) && $day_locks[$leave_date]['payroll_impact'] === 'PAID_ABSENT') {
                $absent_covered += max(0, min(1, (float) $credit));
                continue;
            }
            $attendance_row = $this->staffattendancemodel->searchStaffattendance($leave_date, $staff_id, false);
            $attendance_key = $this->getNormalizedAttendanceKeyForPayrollRow((array) $attendance_row, (int) $staff_id);
            if ($attendance_key === 'A') {
                $absent_covered += max(0, min(1, (float) $credit));
            }
        }

        return $absent_covered;
    }

    private function getApprovedPaidLeaveDates($month_num, $year, $staff_id)
    {
        $start_date = $year . '-' . sprintf('%02d', $month_num) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        return $this->getApprovedPaidLeaveDatesByRange($start_date, $end_date, $staff_id);
    }

    private function getApprovedPaidLeaveDatesByRange($start_date, $end_date, $staff_id)
    {
        $credits = $this->getApprovedPaidLeaveCreditsByRange($start_date, $end_date, $staff_id);
        return array_keys(array_filter($credits, function ($value) {
            return (float) $value > 0;
        }));
    }

    /**
     * Returns approved paid-leave credits keyed by date.
     *
     * $debit_only = true  → only applyleave (leave_direction='debit') requests.
     * $balance_check_only = true → pre-allotted types (rcb=1) and credit-consumers only.
     * Both false → all non-LOP approved leaves.
     *
     * NOTE: debit-direction leaves are intentionally excluded from $balance_check_only
     * because they are handled separately via getDebitLeaveAbsentCountRange and must
     * always be subtracted from LOP regardless of auto-adjust mode.
     */
    private function getApprovedPaidLeaveCreditsByRange($start_date, $end_date, $staff_id, $balance_check_only = false, $debit_only = false)
    {
        $has_duration_column = $this->db->field_exists('leave_duration_type', 'staff_leave_request');
        if ($has_duration_column) {
            $this->db->select('staff_leave_request.leave_from, staff_leave_request.leave_to, staff_leave_request.leave_days, COALESCE(staff_leave_request.leave_duration_type, "full_day") as leave_duration_type', false);
        } else {
            $this->db->select('staff_leave_request.leave_from, staff_leave_request.leave_to, staff_leave_request.leave_days');
        }
        $this->db->from('staff_leave_request');
        $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id');
        $this->db->where('staff_leave_request.staff_id', $staff_id);
        $this->db->where_in('staff_leave_request.status', ['approve', 'approved']);
        $this->db->where('leave_types.is_lop', 0);
        if ($debit_only) {
            // Applyleave (debit-direction) requests only.
            $this->db->where('staff_leave_request.leave_direction', 'debit');
        } elseif ($balance_check_only) {
            // Pre-allotted types (CL/ML: rcb=1) and credit-consumer types (CPL).
            // Debit-direction leaves are handled by getDebitLeaveAbsentCountRange — excluded here.
            $has_rc = $this->db->field_exists('requires_balance_check', 'leave_types');
            $has_cs = $this->db->field_exists('credit_source_type_id', 'leave_types');
            $conditions = [];
            if ($has_rc) { $conditions[] = 'leave_types.requires_balance_check = 1'; }
            if ($has_cs) { $conditions[] = 'leave_types.credit_source_type_id IS NOT NULL'; }
            if (!empty($conditions)) {
                $this->db->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }
        $this->db->where('staff_leave_request.leave_from <=', $end_date);
        $this->db->where('staff_leave_request.leave_to >=', $start_date);
        $rows = $this->db->get()->result_array();

        $leave_credits = [];
        foreach ($rows as $row) {
            $duration_type = strtolower(trim((string) ($row['leave_duration_type'] ?? 'full_day')));
            if (in_array($duration_type, ['first_half', 'second_half'], true)) {
                $date_key = $row['leave_from'];
                if ($date_key >= $start_date && $date_key <= $end_date) {
                    $existing = (float) ($leave_credits[$date_key] ?? 0);
                    $leave_credits[$date_key] = min(1.0, $existing + 0.5);
                }
                continue;
            }

            $from = new DateTime(max($row['leave_from'], $start_date));
            $to = new DateTime(min($row['leave_to'], $end_date));
            while ($from <= $to) {
                $date_key = $from->format('Y-m-d');
                $existing = (float) ($leave_credits[$date_key] ?? 0);
                $leave_credits[$date_key] = min(1.0, $existing + 1.0);
                $from->modify('+1 day');
            }
        }

        ksort($leave_credits);
        return $leave_credits;
    }

    private function getWorkingDayContext($month_num, $year)
    {
        $start_date = $year . '-' . sprintf('%02d', $month_num) . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        return $this->getWorkingDayContextRange($start_date, $end_date);
    }

    private function isCompOffHolidayType($type_label)
    {
        static $override_types = null;
        if ($override_types === null) {
            $override_types = ['compensation', 'comp off', 'compoff', 'compensatory off'];
            $setting = $this->setting_model->getSetting();
            $configured = trim((string) ($setting->leave_workday_override_types ?? ''));
            if ($configured !== '') {
                $parsed = array_filter(array_map('trim', explode(',', strtolower($configured))));
                if (!empty($parsed)) {
                    $override_types = [];
                    foreach ($parsed as $item) {
                        $normalized_item = str_replace(['_', '-'], ' ', $item);
                        $normalized_item = preg_replace('/\s+/', ' ', $normalized_item);
                        if (!in_array($normalized_item, $override_types, true)) {
                            $override_types[] = $normalized_item;
                        }
                    }
                }
            }
        }

        $normalized = strtolower(trim((string) $type_label));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return in_array($normalized, $override_types, true);
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
                    if ($this->isCompOffHolidayType($type_label)) {
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

    private function roundPayrollAmount($amount)
    {
        return (float) round((float) $amount, 0, PHP_ROUND_HALF_UP);
    }

    private function resolvePayrollBasicAmount($basic_amount, $staff = null, $staff_id = 0)
    {
        $basic_amount = (float) $basic_amount;
        if ($basic_amount > 0) {
            return $basic_amount;
        }

        if (is_array($staff) && !empty($staff['basic_salary'])) {
            $contract_basic = (float) $staff['basic_salary'];
            if ($contract_basic > 0) {
                return $contract_basic;
            }
        }

        $staff_id = (int) $staff_id;
        if ($staff_id > 0) {
            $staff_row = $this->payroll_model->searchEmployeeById($staff_id);
            if (is_array($staff_row) && !empty($staff_row['basic_salary'])) {
                $contract_basic = (float) $staff_row['basic_salary'];
                if ($contract_basic > 0) {
                    return $contract_basic;
                }
            }
        }

        return 0;
    }

    private function getMonthDateRange($month, $year)
    {
        $month_num = (int) $month;
        if ($month_num < 1 || $month_num > 12) {
            $month_num = (int) date('m', strtotime($year . '-' . $month . '-01'));
        }
        $start_date = sprintf('%04d-%02d-01', (int) $year, $month_num);
        $end_date = date('Y-m-t', strtotime($start_date));

        return [
            'month_num' => $month_num,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_days' => cal_days_in_month(CAL_GREGORIAN, $month_num, (int) $year),
        ];
    }

    private function applyDojProrataToGross($full_gross_salary, $month, $year, $date_of_joining)
    {
        $full_gross_salary = (float) $full_gross_salary;
        $range = $this->getMonthDateRange($month, $year);
        $total_days = (int) ($range['total_days'] ?? 0);

        $result = [
            'is_prorata_applied' => false,
            'payable_days' => $total_days,
            'total_days' => $total_days,
            'prorated_gross_salary' => $full_gross_salary,
            'prorata_deduction' => 0,
        ];

        if (empty($date_of_joining)) {
            return $result;
        }

        $joining_ts = strtotime($date_of_joining);
        if ($joining_ts === false) {
            return $result;
        }

        $joining_date = date('Y-m-d', $joining_ts);
        $month_start = $range['start_date'];
        $month_end = $range['end_date'];

        if ($joining_date <= $month_start) {
            return $result;
        }

        $result['is_prorata_applied'] = true;

        if ($joining_date > $month_end) {
            $result['payable_days'] = 0;
            $result['prorated_gross_salary'] = 0;
            $result['prorata_deduction'] = $full_gross_salary;
            return $result;
        }

        $start_dt = new DateTime($joining_date);
        $end_dt = new DateTime($month_end);
        $payable_days = (int) $start_dt->diff($end_dt)->days + 1;
        $payable_days = max(0, min($payable_days, $total_days));

        $prorated_gross_salary = $total_days > 0 ? (($full_gross_salary / $total_days) * $payable_days) : 0;

        $result['payable_days'] = $payable_days;
        $result['prorated_gross_salary'] = $prorated_gross_salary;
        $result['prorata_deduction'] = max(0, $full_gross_salary - $prorated_gross_salary);

        return $result;
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
        
        // Get allowance arrays to recalculate total_allowance from individual amounts
        $allowance_type_id = $this->input->post("allowance_type_id");
        $allowance_amount = $this->input->post("allowance_amount");
        
        $this->form_validation->set_rules('net_salary', $this->lang->line('net_salary'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->create($month, $year, $staff_id);
        } else {
        
        // Recalculate total_allowance from individual earnings (skip BASIC, include all others)
        if (!empty($allowance_type_id) && !empty($allowance_amount)) {
            $allowance_types = $this->payroll_model->getAllowanceTypes(null, false);
            $type_map = [];
            foreach ($allowance_types as $type) {
                $type_map[(int)$type['id']] = strtoupper(trim($type['allowance_code'] ?? ''));
            }
            
            $recalc_allowance = 0;
            $basic_from_earning = null;
            foreach ($allowance_type_id as $idx => $type_id) {
                $type_id = (int)$type_id;
                if ($type_id <= 0 || !isset($allowance_amount[$idx])) {
                    continue;
                }
                
                $code = $type_map[$type_id] ?? '';
                $amount = convertCurrencyFormatToBaseAmount($allowance_amount[$idx]);
                if ($code === 'BASIC') {
                    $basic_from_earning = (float)$amount;
                    continue; // BASIC updates base pay, not allowance total
                }
                
                $recalc_allowance += (float)$amount;
            }
            if ($basic_from_earning !== null) {
                $basic = $basic_from_earning;
            }
            $total_allowance = (float)$recalc_allowance;
        } else {
            if($total_allowance){
                $total_allowance = convertCurrencyFormatToBaseAmount($total_allowance);
            }else{
                $total_allowance = 0;  
            }
        }
        
        if($total_deduction){
                $total_deduction = convertCurrencyFormatToBaseAmount($total_deduction);

        }else{
                $total_deduction = 0;  
        }
        
        // Recalculate total_deduction from individual deductions (manual deductions only, not EPF/ESI/TDS)
        $deduction_type_id = $this->input->post("deduction_type_id");
        $deduction_amount = $this->input->post("deduction_amount");
        $existing_pt_amount = $this->getExistingProfessionalTaxAmount((int) $id);
        $pt_in_form = false;
        $month_num_for_pt = date('n', strtotime($year . '-' . $month . '-01'));
        $onetime_payload_for_pt = $this->getOneTimeDeductionPayload((int) $staff_id, (int) $month_num_for_pt, (int) $year);
        $onetime_deduction_rows = (array) ($onetime_payload_for_pt['rows'] ?? []);
        $onetime_deduction_total = (float) ($onetime_payload_for_pt['total'] ?? 0);
        $has_onetime_pt = false;
        foreach ((array) ($onetime_payload_for_pt['rows'] ?? []) as $pt_row) {
            if (strtoupper(trim((string) ($pt_row['deduction_type'] ?? ''))) === 'PT') {
                $has_onetime_pt = true;
                break;
            }
        }
        if (!empty($deduction_type_id) && !empty($deduction_amount)) {
            $allowance_types = $this->payroll_model->getAllowanceTypes(null, false);
            $type_map = [];
            foreach ($allowance_types as $type) {
                $type_map[(int)$type['id']] = strtoupper(trim($type['allowance_code'] ?? ''));
            }
            
            $recalc_deduction = 0;
            foreach ($deduction_type_id as $idx => $type_id) {
                $type_id = (int)$type_id;
                if ($type_id <= 0 || !isset($deduction_amount[$idx])) {
                    continue;
                }
                
                $code = $type_map[$type_id] ?? '';
                // Keep PT as manual deduction; skip only auto-calculated statutory deductions.
                if (in_array($code, ['EPF', 'ESI', 'TDS'], true)) {
                    continue;
                }

                if ($code === 'PT') {
                    if ($has_onetime_pt) {
                        continue;
                    }
                    $pt_in_form = true;
                }

                $amount = convertCurrencyFormatToBaseAmount($deduction_amount[$idx]);
                $recalc_deduction += (float)$amount;
            }
            $total_deduction = (float)$recalc_deduction;
        }

        if (!$pt_in_form && !$has_onetime_pt && $existing_pt_amount > 0) {
            $total_deduction += (float) $existing_pt_amount;
        }

        $total_deduction += $onetime_deduction_total;
        
        if($basic){
                $basic = convertCurrencyFormatToBaseAmount($basic);
        }else{
                $basic = 0;  
        }
        
        if($net_salary){
            $net_salary = $this->roundPayrollAmount(convertCurrencyFormatToBaseAmount($net_salary));
        }else{
                $net_salary = 0;  
        }
        
        if($tax){
            $tax = $this->roundPayrollAmount(convertCurrencyFormatToBaseAmount($tax));
        }else{
            $tax = 0;
        }

        if($leave_deduction){
            $leave_deduction = convertCurrencyFormatToBaseAmount($leave_deduction);
        }else{
            $leave_deduction = 0;
        }
        
        // recalc EPF/ESI values before saving (ensures calculate button updates DB)
        $this->load->library('tax_epf_calculator');
        $this->load->model('payroll_increment_model');
        
        // Get DA from existing earnings (if payslip exists), or from form
        $da = 0;
        $month_numeric = date('n', strtotime($year . '-' . $month . '-01'));
        if ($id > 0) {
            $existing_earnings = $this->payroll_model->getAllowance($id, 'positive');
            if (!empty($existing_earnings)) {
                foreach ($existing_earnings as $earning) {
                    if (strtoupper(trim($earning['allowance_type'])) === 'DA') {
                        $da = (float) $earning['amount'];
                        break;
                    }
                }
            }
        }
        
        // Calculate EPF and ESI contributions
        $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
        if (!is_array($staff_data)) {
            $staff_data = [];
        }
        $basic = $this->resolvePayrollBasicAmount($basic, $staff_data, (int) $staff_id);
        $has_uan = isset($staff_data['uan_no']) && trim((string) $staff_data['uan_no']) !== '';
        $has_esi_no = isset($staff_data['esi_no']) && trim((string) $staff_data['esi_no']) !== '';

        $gross_salary = (float) $basic + (float) $total_allowance;
        $lop_deduction = isset($leave_deduction) ? (float) $leave_deduction : 0;
        $epf_wage = 0;
        $employee_epf = 0;
        $employer_pf = 0;
        $employer_eps = 0;
        $employer_edli = 0;
        $employer_admin = 0;
        if ($has_uan) {
            $epf_wage = $this->tax_epf_calculator->calculate_epf_wage($gross_salary, $lop_deduction);
            $employee_epf = $this->tax_epf_calculator->calculate_employee_epf($epf_wage);
            $employer_pf = $this->tax_epf_calculator->calculate_employer_pf($epf_wage);
            $employer_eps = $this->tax_epf_calculator->calculate_employer_eps($epf_wage);
            $employer_edli = $this->tax_epf_calculator->calculate_employer_edli($epf_wage);
            $employer_admin = $this->tax_epf_calculator->calculate_employer_admin_charges($epf_wage);
        }
        
        // ESI calculation based on gross and LOP
        $esi_wage = 0;
        $employee_esi = 0;
        if ($has_esi_no) {
            $esi_wage = $this->tax_epf_calculator->calculate_esi_wage($gross_salary, $lop_deduction);
            $employee_esi = $this->tax_epf_calculator->calculate_employee_esi($esi_wage);
        }
        $employer_esi = 0;
        if ($esi_wage > 0) {
            $employer_esi = round($esi_wage * 0.0325, 0);
        }

        // Recompute TDS server-side using FY-aware YTD logic so edit flow is consistent with bulk flow.
        $flat_tds_pct = isset($staff_data['tds_percentage']) && $staff_data['tds_percentage'] !== null ? (float) $staff_data['tds_percentage'] : 0;
        if ($flat_tds_pct > 0) {
            $tax = $this->roundPayrollAmount($gross_salary * ($flat_tds_pct / 100));
        } else {
            $fy_start_month = $this->getConfiguredPayrollFyStartMonth();
            $fy_month_index = $this->tax_epf_calculator->get_fy_month_index($month_numeric, $fy_start_month);
            $ytd_data = $this->payroll_model->getYTDIncome($staff_id, $year, $month_numeric, $fy_start_month, false, true);

            $opening_ytd = $this->getApplicableOpeningYtd($staff_data, (int) $year, (int) $month_numeric, (int) $fy_start_month);
            $opening_ytd_income = (float) $opening_ytd['income'];
            $opening_ytd_tax = (float) $opening_ytd['tax'];

            $effective_ytd_income = (float) ($ytd_data['gross'] ?? 0) + max(0, $opening_ytd_income);
            $effective_tax_paid = (float) ($ytd_data['tax_deducted'] ?? 0) + max(0, $opening_ytd_tax);

            if ($effective_ytd_income > 0 && $fy_month_index > 1) {
                $tds_result = $this->tax_epf_calculator->calculate_tds_ytd(
                    $ytd_income = $effective_ytd_income,
                    $current_month_gross = $gross_salary,
                    $current_month = $fy_month_index,
                    $total_months = 12,
                    $tax_already_deducted = $effective_tax_paid
                );
                $tax = $tds_result['monthly_tds'];
            } else {
                $tax = $this->tax_epf_calculator->calculate_monthly_tds($gross_salary);
            }
            $tax = $this->roundPayrollAmount($tax);
        }

        // Keep net salary aligned with current calculated statutory values.
        $net_salary = $this->roundPayrollAmount(
            (float) $gross_salary - ((float) $total_deduction + (float) $employee_epf + (float) $employee_esi + (float) $tax)
        );

            $data = array(
                'id'              => $id,
                'staff_id'        => $staff_id,
                'basic'           => $basic,
                'total_allowance' => $total_allowance,
                'total_deduction' => $total_deduction,
                'net_salary'      => $this->roundPayrollAmount($net_salary),
                'payment_date'    => date("Y-m-d"),
                'status'          => $status,
                'month'           => $month,
                'year'            => $year,
                'tax'             => $tax,
                'leave_deduction' => $leave_deduction,
                'epf_wage'        => round($epf_wage, 2),
                'employee_epf'    => round($employee_epf, 2),
                'employer_pf'     => round($employer_pf, 2),
                'employer_eps'    => round($employer_eps, 2),
                'employer_edli'   => round($employer_edli, 2),
                'employer_admin'  => round($employer_admin, 2),
                'esi_wage'        => round($esi_wage, 2),
                'employee_esi'    => round($employee_esi, 2),
                'employer_esi'    => $employer_esi,
                'generated_by'    => $this->customlib->getStaffID(),
            );

            $checkForUpdate = $this->payroll_model->checkPayslip($month, $year, $staff_id);
            if (!$checkForUpdate) {
                $insert_id         = $this->payroll_model->createPayslip($data);
                $payslipid         = $insert_id;
                
                // Get staff data to calculate EPF/ESI based on dual checkpoints
                $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
                // include allowances in EPF base
                $statutory_deductions = $this->payroll_model->calculateStatutoryDeductions($staff_data, 0.13, 0.0075, $total_allowance);
                
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
                $saved_earnings_count = 0;
                $saved_deductions_count = 0;

        // ========================================
        // SAVE EARNINGS TO DATABASE
        // ========================================
        // Replace all existing earnings with current form values
        $this->db->where('payslip_id', $payslipid)
            ->where('cal_type', 'positive')
            ->delete('payslip_allowance');

        $allowance_amount = $this->input->post("allowance_amount");
        if (!empty($allowance_type_id) && !empty($allowance_amount)) {
            foreach ($allowance_type_id as $idx => $type_id) {
                $type_id = (int) $type_id;
                if ($type_id <= 0 || !isset($allowance_amount[$idx])) {
                    continue;
                }

                $type_code = $allowance_type_map[$type_id] ?? '';
                if ($type_code === '') {
                    continue;
                }

                $amount = convertCurrencyFormatToBaseAmount($allowance_amount[$idx]);
                if ($amount < 0) {
                    continue;
                }

                $earning_data = array(
                    'payslip_id'     => $payslipid,
                    'allowance_type' => $type_code,
                    'amount'         => (float) $amount,
                    'staff_id'       => $staff_id,
                    'cal_type'       => 'positive',
                );
                $this->payroll_model->add_allowance($earning_data);
                $saved_earnings_count++;
            }
        }

        // ========================================
        // SAVE DEDUCTIONS TO DATABASE
        // ========================================
        
        // DELETE ALL DEDUCTIONS FIRST (clean slate - both manual and statutory)
        $this->db->where('payslip_id', $payslipid)
            ->where('cal_type', 'negative')
            ->delete('payslip_allowance');

        // Re-insert manual deductions from form (non-statutory only)
        $deduction_type_id = $this->input->post("deduction_type_id");
        $deduction_amount = $this->input->post("deduction_amount");
        
        $pt_saved_from_form = false;
        if (!empty($deduction_type_id) && !empty($deduction_amount)) {
            $deduction_type_map = [];
            foreach ($allowance_types as $type) {
                $deduction_type_map[(int)$type['id']] = strtoupper(trim($type['allowance_code'] ?? ''));
            }
            
            foreach ($deduction_type_id as $idx => $deduct_amount) {
                if (!isset($deduction_type_id[$idx]) || empty($deduction_type_id[$idx])) {
                    continue;
                }
                
                $type_id = (int) $deduction_type_id[$idx];
                $type_code = $deduction_type_map[$type_id] ?? '';
                
                // Keep PT as manual deduction; skip only auto-calculated statutory deductions.
                if (in_array($type_code, ['EPF', 'ESI', 'TDS'], true)) {
                    continue;
                }

                if ($type_code === 'PT') {
                    if ($has_onetime_pt) {
                        continue;
                    }
                    $pt_saved_from_form = true;
                }

                if (!isset($deduction_amount[$idx])) {
                    continue;
                }
                
                $amount = convertCurrencyFormatToBaseAmount($deduction_amount[$idx]);
                if ($amount > 0) {
                    $deduction_data = array(
                        'payslip_id'     => $payslipid,
                        'allowance_type' => $type_code,
                        'amount'         => (float) $amount,
                        'staff_id'       => $staff_id,
                        'cal_type'       => 'negative',
                    );
                    $this->payroll_model->add_allowance($deduction_data);
                    $saved_deductions_count++;
                }
            }
        }

        // Re-apply approved one-time deductions every calculate/save cycle.
        $this->addOneTimeDeductionsToPayslip((int) $payslipid, (int) $staff_id, $onetime_deduction_rows);
        $saved_deductions_count += count($onetime_deduction_rows);

        if (!$pt_saved_from_form && !$has_onetime_pt && $existing_pt_amount > 0) {
            $pt_data = array(
                'payslip_id'     => $payslipid,
                'allowance_type' => 'PT',
                'amount'         => round($existing_pt_amount, 2),
                'staff_id'       => $staff_id,
                'cal_type'       => 'negative',
            );
            $this->payroll_model->add_allowance($pt_data);
            $saved_deductions_count++;
        }

        // ======== ADD ONLY APPLICABLE STATUTORY DEDUCTIONS ========
        // Get statutory allowance type codes from database
        $epf_code = $this->payroll_model->getStatutoryAllowanceCode('EPF');
        $esi_code = $this->payroll_model->getStatutoryAllowanceCode('ESI');
        $tds_code = $this->payroll_model->getStatutoryAllowanceCode('TDS');
        
        // Add EPF deduction ONLY if employee_epf > 0
        if (!empty($employee_epf) && $employee_epf > 0 && $epf_code) {
            $epf_data = array(
                'payslip_id'     => $payslipid,
                'allowance_type' => $epf_code,
                'amount'         => round($employee_epf, 2),
                'staff_id'       => $staff_id,
                'cal_type'       => 'negative',
            );
            $this->payroll_model->add_allowance($epf_data);
            $saved_deductions_count++;
        }
        
        // Add ESI deduction ONLY if esi_wage > 0 AND employee_esi > 0
        if ($esi_wage > 0 && !empty($employee_esi) && $employee_esi > 0 && $esi_code) {
            $esi_data = array(
                'payslip_id'     => $payslipid,
                'allowance_type' => $esi_code,
                'amount'         => round($employee_esi, 2),
                'staff_id'       => $staff_id,
                'cal_type'       => 'negative',
            );
            $this->payroll_model->add_allowance($esi_data);
            $saved_deductions_count++;
        }
        
        // Add TDS deduction ONLY if tax > 0
        if (!empty($tax) && $tax > 0 && $tds_code) {
            $tds_data = array(
                'payslip_id'     => $payslipid,
                'allowance_type' => $tds_code,
                'amount'         => (float) $tax,
                'staff_id'       => $staff_id,
                'cal_type'       => 'negative',
            );
            $this->payroll_model->add_allowance($tds_data);
            $saved_deductions_count++;
        }

            $this->ensureBasicAllowanceExists((int) $payslipid, (int) $staff_id, (float) $basic);

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Payslip updated successfully. (Earnings saved: ' . (int) $saved_earnings_count . ', Deductions saved: ' . (int) $saved_deductions_count . ')</div>');
            $redirect_id = $id;
            if (empty($redirect_id)) {
                $existing_payslip = $this->payroll_model->getPayslipByStaffMonthYear($staff_id, $month, $year);
                if (!empty($existing_payslip) && !empty($existing_payslip->id)) {
                    $redirect_id = $existing_payslip->id;
                }
            }
            if (!empty($redirect_id)) {
                redirect('admin/payroll/edit/' . $redirect_id);
            }
            redirect('admin/payroll');
            } else {

                $this->session->set_flashdata("msg", '<div class="alert alert-warning text-center">' . $this->lang->line('payslip_not_generated') . '</div>');
                $redirect_id = $id;
                if (empty($redirect_id)) {
                    $existing_payslip = $this->payroll_model->getPayslipByStaffMonthYear($staff_id, $month, $year);
                    if (!empty($existing_payslip) && !empty($existing_payslip->id)) {
                        $redirect_id = $existing_payslip->id;
                    }
                }
                if (!empty($redirect_id)) {
                    redirect('admin/payroll/edit/' . $redirect_id);
                }
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


            $derived_summary = $this->getDerivedAttendanceSummaryForPeriod(
                (int) $emp,
                $period['start_date'],
                $period['end_date'],
                $working_day_dates,
                $context
            );

            foreach ($this->staff_attendance as $att_key => $att_value_from_config) {
                if ($att_key == 'holiday') {
                    $r[$att_key] = count($holidays_for_H_column); // Now this only counts "other leaves"
                    $r['sunday'] = count($weekend_day_dates); // Weekend days count
                    continue;
                }

                $r[$att_key] = (int) ($derived_summary['by_config_key'][$att_key] ?? 0);
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
            $approved_paid_leave_credits = $this->getApprovedPaidLeaveCreditsByRange($period['start_date'], $period['end_date'], $emp);
            $working_day_lookup = array_fill_keys($working_day_dates, true);
            $approved_leave_total = 0.0;
            foreach ($approved_paid_leave_credits as $leave_date => $credit) {
                if (isset($working_day_lookup[$leave_date])) {
                    $approved_leave_total += (float) $credit;
                }
            }

            $record[$month] = round($approved_leave_total, 2);
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
            $net_salary = $this->roundPayrollAmount(convertCurrencyFormatToBaseAmount($this->input->post("net_salary")));
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
        $payslip_id      = (int) $this->input->post("payslip_id");
        $basic = $this->resolvePayrollBasicAmount($basic, null, (int) $staff_id);
        
        $leave_deduction = $this->input->post("leave_deduction");
        
        $this->form_validation->set_rules('net_salary', $this->lang->line('net_salary'), 'trim|required|xss_clean');       
        
        if ($this->form_validation->run() == false) {
            $this->create($month, $year, $staff_id);
        } else {

            $data = array('staff_id' => $staff_id,
                'basic'                  => $basic,
                'total_allowance'        => $total_allowance,
                'total_deduction'        => $total_deduction,
                'net_salary'             => $this->roundPayrollAmount($net_salary),
                'payment_date'           => date("Y-m-d"),
                'status'                 => $status,
                'month'                  => $month,
                'year'                   => $year,
                'tax'                    => $tax,
                'leave_deduction'        => $leave_deduction,
            );

            $existing_month_payslip = $this->payroll_model->getPayslipByStaffMonthYear($staff_id, $month, $year);
            if ($payslip_id <= 0 && !empty($existing_month_payslip) && !empty($existing_month_payslip->id)) {
                $payslip_id = (int) $existing_month_payslip->id;
            }
            if ($payslip_id > 0) {
                $data['id'] = $payslip_id;
            }

            $insert_id        = $this->payroll_model->createPayslip($data);
            $payslipid        = $insert_id;
                
                // Get staff data to calculate EPF/ESI based on dual checkpoints
                $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
                // include allowances in EPF/ESI rules
                $statutory_deductions = $this->payroll_model->calculateStatutoryDeductions($staff_data, 0.13, 0.0075, $total_allowance);
                
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
                
                // Delete existing ALL deductions before adding new ones to prevent duplicates
                // This ensures no orphaned rows with empty allowance_type values remain
                $this->db->where('payslip_id', $payslipid)
                    ->where('cal_type', 'negative')
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

                $this->ensureBasicAllowanceExists((int) $payslipid, (int) $staff_id, (float) $basic);

                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Payslip saved successfully.</div>');
                $redirect_id = $payslipid;
                if (empty($redirect_id)) {
                    $existing_payslip = $this->payroll_model->getPayslipByStaffMonthYear($staff_id, $month, $year);
                    if (!empty($existing_payslip) && !empty($existing_payslip->id)) {
                        $redirect_id = $existing_payslip->id;
                    }
                }
                if (!empty($redirect_id)) {
                    redirect('admin/payroll/edit/' . $redirect_id);
                }
                redirect('admin/payroll');
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

        $selected_date = DateTime::createFromFormat('Y-F-j', $year . '-' . $month . '-1');
        if (!$selected_date) {
            $selected_date = DateTime::createFromFormat('Y-m-j', $year . '-' . date('n', strtotime($month)) . '-1');
        }

        if ($selected_date) {
            $prev_date = clone $selected_date;
            $prev_date->modify('-1 month');
            $prev_month = $prev_date->format('F');
            $prev_year = $prev_date->format('Y');

            $prev_staff_list = $this->payroll_model->searchEmployee($prev_month, $prev_year, '', $role);
            $pending_prev_count = 0;
            foreach ((array) $prev_staff_list as $prev_staff) {
                $prev_payslip_id = isset($prev_staff['payslip_id']) ? (int) $prev_staff['payslip_id'] : 0;
                $prev_status = strtolower(trim((string) ($prev_staff['status'] ?? '')));
                if ($prev_payslip_id > 0 && $prev_status !== 'paid') {
                    $pending_prev_count++;
                }
            }

            if ($pending_prev_count > 0) {
                $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Bulk calculation stopped: Previous month (' . $prev_month . ' ' . $prev_year . ') still has ' . $pending_prev_count . ' generated/unpaid payslip(s). Until those payslips are marked as Paid, their net salary and TDS are skipped from YTD, which can lead to incorrect TDS calculation for the selected month. Please complete payment posting first using Bulk Mark as Paid, then run Bulk Calculate again.</div>');
                redirect('admin/payroll/search/' . $month . '/' . $year . '/' . $role);
            }
        }
        
        // Convert month name to numeric for monthly balance tracking
        $month_numeric = date('n', strtotime($year . '-' . $month . '-01'));

        $staff_list = $this->payroll_model->searchEmployee($month, $year, '', $role);
        $generated = 0;
        $updated_existing = 0;
        $skipped_existing = 0;
        $zero_salary_generated = 0;

        $special_allowance_code = 'SA';
        $earning_types = $this->payroll_model->getAllowanceTypes('earning', true);
        if (!empty($earning_types)) {
            foreach ($earning_types as $earning_type) {
                $type_code = strtoupper(trim((string) ($earning_type['allowance_code'] ?? '')));
                $type_name = strtolower(trim((string) ($earning_type['allowance_name'] ?? '')));
                if ($type_code === 'SA' || $type_name === 'special allowance') {
                    $special_allowance_code = !empty($type_code) ? $type_code : $special_allowance_code;
                    break;
                }
            }
        }

        foreach ($staff_list as $staff) {
            if (!empty($staff['payslip_id']) && !$overwrite) {
                $this->payroll_model->syncOnDutyCreditsForMonth((int) $staff['id'], (int) $month_numeric, (int) $year);
                $skipped_existing++;
                continue;
            }

            $date = $year . '-' . $month;
            $newdate = date('Y-m-d', strtotime($date . ' +1 month'));
            $monthAttendanceData = $this->monthAttendance($newdate, 3, $staff['id']);

            $month_num = date('m', strtotime($year . '-' . $month . '-01'));
            $month_key = '01-' . $month_num . '-' . $year;
            $attendance = $monthAttendanceData[$month_key] ?? reset($monthAttendanceData) ?? [];

            $basic = $this->resolvePayrollBasicAmount($staff['basic_salary'] ?? 0, $staff, (int) $staff['id']);

            $source_payslip = false;
            $selected_month_date = DateTime::createFromFormat('Y-F-d', $year . '-' . $month . '-01');
            if (!$selected_month_date) {
                $selected_month_date = new DateTime($year . '-' . $month . '-01');
            }
            $previous_month_date = clone $selected_month_date;
            $previous_month_date->modify('-1 month');
            $previous_month_name = $previous_month_date->format('F');
            $previous_month_year = $previous_month_date->format('Y');

            $previous_month_payslip = $this->payroll_model->getPayslipByStaffMonthYear($staff['id'], $previous_month_name, $previous_month_year);
            if (!empty($previous_month_payslip) && !empty($previous_month_payslip->id)) {
                $source_payslip = $this->payroll_model->getPayslip($previous_month_payslip->id);
            }

            if (empty($source_payslip)) {
                $source_payslip = $this->payroll_model->getLastPayslip($staff['id']);
            }
            $allowances = [];
            $total_allowance = 0;
            $total_deduction = 0;
            $onetime_deduction_rows = [];
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

            if (!empty($source_payslip)) {
                if (!empty($source_payslip['basic'])) {
                    $basic = $source_payslip['basic'];
                }
                $basic = $this->resolvePayrollBasicAmount($basic, $staff, (int) $staff['id']);
                $tax = !empty($source_payslip['tax']) ? $source_payslip['tax'] : 0;
                // Only get POSITIVE allowances (earnings) from last payslip
                // Deductions are NOT copied - they are calculated fresh each month based on EPF/ESI/TDS rules
                $allowances = $this->payroll_model->getAllowance($source_payslip['id'], 'positive');
                $temp_amount_from_last_month = 0;  // Track TEMP for potential merging
                
                foreach ($allowances as $allowance) {
                    // Skip "BASIC" allowance code to avoid double counting with $basic field
                    $allowance_code = strtoupper(trim($allowance['allowance_type']));
                    if ($allowance_code === 'BASIC') {
                        continue;
                    }
                    
                    // Handle temporary increment from previous month
                    if ($allowance_code === 'TEMP') {
                        $temp_amount_from_last_month = (float) $allowance['amount'];
                        // Don't add TEMP to total_allowance yet - check if it needs to be merged
                        continue;
                    }
                    
                    // Extract DA from allowances if applicable
                    if ($allowance_code === 'DA') {
                        $da = (float) $allowance['amount'];
                    }
                    
                    // Only process positive allowances (earnings)
                    if ($allowance['cal_type'] === 'positive') {
                        $total_allowance += (float) $allowance['amount'];
                    }
                    // Note: Deductions are NOT copied from previous month
                }
                
                // MERGE LOGIC: If TEMP exists from last month, check if it should be merged into SA/BASIC
                // BUT: If we're overwriting and this IS the increment month, skip this - the TEMP is from THIS month's increment
                if ($temp_amount_from_last_month > 0 && !$is_increment_month) {
                    // Get the approved increment for this staff (from ANY month, we need the latest)
                    $approved_increment = $this->payroll_increment_model->getLatestApprovedIncrement($staff['id']);
                    
                    if ($approved_increment && $approved_increment['is_recurring'] == 1) {
                        // This is a recurring increment - merge based on merge_with setting
                        if ($approved_increment['merge_with'] === 'special_allowance') {
                            // Merge into SA allowance line item and totals
                            $this->mergeAmountIntoAllowanceCode($allowances, $special_allowance_code, $temp_amount_from_last_month, (int) $staff['id']);
                            $total_allowance += $temp_amount_from_last_month;
                        } elseif ($approved_increment['merge_with'] === 'basic') {
                            // Merge into basic salary
                            $basic = (float) $basic + $temp_amount_from_last_month;
                        } else {
                            // Default to special_allowance if merge_with not specified
                            $this->mergeAmountIntoAllowanceCode($allowances, $special_allowance_code, $temp_amount_from_last_month, (int) $staff['id']);
                            $total_allowance += $temp_amount_from_last_month;
                        }
                        // Don't add TEMP as separate line item in payslip_allowance (it's merged)
                    } else {
                        // Not a recurring increment (one-time bonus) - just add it as-is
                        $total_allowance += $temp_amount_from_last_month;
                    }
                }
            }

            // Add increment to total_allowance if it's increment month
            if ($is_increment_month && $increment_amount > 0) {
                $total_allowance += $increment_amount;
            }

            $onetime_payload = $this->getOneTimeDeductionPayload((int) $staff['id'], (int) $month_numeric, (int) $year);
            $onetime_deduction_rows = $onetime_payload['rows'];
            $total_deduction += (float) ($onetime_payload['total'] ?? 0);

            $monthLeaves = $this->monthLeaves($newdate, 3, $staff['id']);
            $lop_summary = $this->getPayrollLopSummary($monthAttendanceData, $monthLeaves, $month, $year, $staff['id']);

            $existing_payslip = [];
            if (!empty($staff['payslip_id'])) {
                $existing_payslip = (array) $this->payroll_model->getPayslip((int) $staff['payslip_id']);
            }

            $existing_status = strtolower(trim((string) ($existing_payslip['status'] ?? '')));
            $use_committed_lop = !empty($existing_payslip)
                && in_array($existing_status, $this->getCommittedPayrollStatuses(), true);

            // If overwriting and recomputing LOP, reset the monthly balance adjustments first.
            if (!empty($staff['payslip_id']) && $overwrite && !$use_committed_lop) {
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

            $lop_values = $this->resolvePayrollLopValues(
                (int) $staff['id'],
                $month,
                $year,
                (array) $lop_summary,
                $existing_payslip,
                true
            );
            $actual_lop_days = (float) ($lop_values['actual_lop_days'] ?? 0);
            $adjusted_lop_days = (float) ($lop_values['adjusted_lop_days'] ?? 0);
            $net_lop_days = (float) ($lop_values['net_lop_days'] ?? $actual_lop_days);

            $month_num = date('n', strtotime($year . '-' . $month . '-01'));
            $total_days_of_month = cal_days_in_month(CAL_GREGORIAN, $month_num, (int)$year);
            if (($lop_values['source'] ?? '') === 'stored') {
                log_message('debug', 'Staff ' . $staff['id'] . ' uses committed payslip LOP values during bulk calculation.');
            } elseif ($total_days_of_month > 0 && $actual_lop_days >= (float) $total_days_of_month) {
                log_message('debug', 'Staff ' . $staff['id'] . ' full-month LOP detected; forcing zero adjustment.');
            } else {
                log_message('debug', 'Staff ' . $staff['id'] . ' - Adjusted: ' . $adjusted_lop_days . ', Net: ' . $net_lop_days);
            }

            $full_gross_salary = (float) $basic + (float) $total_allowance;
            $prorata = $this->applyDojProrataToGross($full_gross_salary, $month_numeric, $year, $staff['date_of_joining'] ?? null);

            $doj_prorated_gross = isset($prorata['prorated_gross_salary']) ? (float) $prorata['prorated_gross_salary'] : (float) $full_gross_salary;
            $doj_prorated_gross = max(0, min((float) $full_gross_salary, $doj_prorated_gross));

            $lop_deduction = 0;
            if ($total_days_of_month > 0 && (float) $net_lop_days > 0) {
                $lop_deduction = (($full_gross_salary / $total_days_of_month) * (float) $net_lop_days);
            }
            $lop_deduction = max(0, min($lop_deduction, $doj_prorated_gross));

            $gross_salary = max(0, $doj_prorated_gross - $lop_deduction);

            // Calculate EPF and TDS using the new library
            // EPF CALCULATION: Only if UAN is available for the staff
            $epf_wage = 0;
            $employee_epf = 0;
            $employer_pf = 0;
            $employer_eps = 0;
            $employer_edli = 0;
            $employer_admin = 0;
            
            $has_uan = isset($staff['uan_no']) && trim((string) $staff['uan_no']) !== '';
            if ($has_uan) {
                // Staff has UAN and EPF is enabled - calculate EPF wage
                // total_allowance already includes the increment amount for the month
                // (we added it above when building the payslip), so pass it directly.
                $epf_wage = $this->tax_epf_calculator->calculate_epf_wage($gross_salary, $lop_deduction);
                $employee_epf = $this->tax_epf_calculator->calculate_employee_epf($epf_wage);
                $employer_pf = $this->tax_epf_calculator->calculate_employer_pf($epf_wage);
                $employer_eps = $this->tax_epf_calculator->calculate_employer_eps($epf_wage);
                $employer_edli = $this->tax_epf_calculator->calculate_employer_edli($epf_wage);
                $employer_admin = $this->tax_epf_calculator->calculate_employer_admin_charges($epf_wage);
            }
            // If UAN is not available, EPF is 0 (skip this staff for EPF)
            
            // ESI calculation based on gross salary and LOP for this month
            // Only if ESI number is available and ESI is enabled
            $has_esi_no = isset($staff['esi_no']) && trim((string) $staff['esi_no']) !== '';
            $esi_wage = 0;
            $esi_deduction = 0;
            if ($has_esi_no) {
                $esi_wage = $this->tax_epf_calculator->calculate_esi_wage($gross_salary, $lop_deduction);
                $esi_deduction = $this->tax_epf_calculator->calculate_employee_esi($esi_wage);
            }
            
            // Calculate TDS using India FY (April-March) YTD approach.
            $month_num = date('n', strtotime($year . '-' . $month . '-01'));
            // Check for flat TDS percentage override set on the staff profile
            $flat_tds_pct = isset($staff['tds_percentage']) && $staff['tds_percentage'] !== null ? (float)$staff['tds_percentage'] : 0;
            if ($flat_tds_pct > 0) {
                // Flat % on gross salary — skip new-regime slab calculation entirely
                $monthly_tds = $this->roundPayrollAmount($gross_salary * ($flat_tds_pct / 100));
            } else {
                $fy_start_month = $this->getConfiguredPayrollFyStartMonth();
                $fy_month_index = $this->tax_epf_calculator->get_fy_month_index($month_num, $fy_start_month);

                // Get prior months from the same FY (excluding current payroll month).
                $ytd_data = $this->payroll_model->getYTDIncome($staff['id'], $year, $month_num, $fy_start_month, false, true);

                // Opening balances allow onboarding clients to carry already-paid income and tax.
                $opening_ytd = $this->getApplicableOpeningYtd($staff, (int) $year, (int) $month_num, (int) $fy_start_month);
                $opening_ytd_income = (float) $opening_ytd['income'];
                $opening_ytd_tax = (float) $opening_ytd['tax'];

                $effective_ytd_income = (float) ($ytd_data['gross'] ?? 0) + max(0, $opening_ytd_income);
                $effective_tax_paid = (float) ($ytd_data['tax_deducted'] ?? 0) + max(0, $opening_ytd_tax);

                if ($effective_ytd_income > 0 && $fy_month_index > 1) {
                    $tds_result = $this->tax_epf_calculator->calculate_tds_ytd(
                        $ytd_income = $effective_ytd_income,
                        $current_month_gross = $gross_salary,
                        $current_month = $fy_month_index,
                        $total_months = 12,
                        $tax_already_deducted = $effective_tax_paid
                    );
                    $monthly_tds = $tds_result['monthly_tds'];
                } else {
                    // FY first month without prior data - use simple annualized approach.
                    $monthly_tds = $this->tax_epf_calculator->calculate_monthly_tds($gross_salary);
                }
                $monthly_tds = $this->roundPayrollAmount($monthly_tds);
            }
            
            // Gross salary is already prorated by payable present days, so don't deduct LOP again here
            $total_with_epf_tds_esi = (float) $employee_epf + (float) $esi_deduction + (float) $monthly_tds + (float) $total_deduction;
            
            $net_salary = $this->roundPayrollAmount($gross_salary - $total_with_epf_tds_esi);

            // Calculate ESI employer contribution (3.25% of ESI wage)
            $employer_esi = 0;
            if ($esi_wage > 0) {
                $employer_esi = round($esi_wage * 0.0325, 0);
            }

            $data = array(
                'staff_id' => $staff['id'],
                'basic' => $basic,
                'da' => $da,
                'total_allowance' => $total_allowance,
                'total_deduction' => $total_deduction,
                'net_salary' => $this->roundPayrollAmount($net_salary),
                'payment_date' => date('Y-m-d'),
                'status' => 'generated',
                'month' => $month,
                'year' => $year,
                'tax' => $monthly_tds,  // Store rounded TDS
                'leave_deduction' => $lop_deduction,
                'actual_lop_days' => $actual_lop_days,
                'adjusted_lop_days' => $adjusted_lop_days,
                'net_lop_days' => $net_lop_days,
                // EPF fields
                'epf_wage' => $epf_wage,
                'employee_epf' => round($employee_epf, 2),
                'employer_pf' => round($employer_pf, 2),
                'employer_eps' => round($employer_eps, 2),
                'employer_edli' => round($employer_edli, 2),
                'employer_admin' => round($employer_admin, 2),
                // ESI fields
                'esi_wage' => $esi_wage,
                'employee_esi' => round($esi_deduction, 2),
                'employer_esi' => $employer_esi,
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
                    // Skip TEMP allowances - they are handled separately by increment/merge logic
                    $allowance_code = strtoupper(trim($allowance['allowance_type']));
                    if ($allowance_code === 'TEMP') {
                        continue;
                    }
                    
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
                    'amount'            => $monthly_tds,
                    'staff_id'          => $staff['id'],
                    'cal_type'          => 'negative',
                );
                $this->payroll_model->add_allowance($tds_data);
            }

            // Add approved one-time deductions after statutory cleanup/addition
            $this->addOneTimeDeductionsToPayslip((int) $payslipid, (int) $staff['id'], $onetime_deduction_rows);

            // Ensure BASIC earning row exists in payslip_allowance for consistent earnings rendering
            $this->ensureBasicAllowanceExists((int) $payslipid, (int) $staff['id'], (float) $basic);

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
        if ($zero_salary_generated > 0) {
            $message .= '<div class="alert alert-info text-center">Zero-salary payslips generated (no attendance): ' . $zero_salary_generated . '.</div>';
        }
        $this->session->set_flashdata('msg', $message);

        redirect('admin/payroll/search/' . $month . '/' . $year . '/' . $role);
    }

    public function bulkmarkpaid()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_add')) {
            access_denied();
        }

        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $role = $this->input->post('role');
        $payment_mode = $this->input->post('bulk_payment_mode');
        $payment_date_raw = $this->input->post('bulk_payment_date');
        $payment_note = $this->input->post('bulk_payment_note');

        if (empty($month) || empty($year) || $month === 'select' || $year === 'select') {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Please select month and year before bulk mark as paid.</div>');
            redirect('admin/payroll');
        }

        $valid_months = array_keys((array) $this->customlib->getMonthDropdown());
        if (!in_array($month, $valid_months, true) || !preg_match('/^\d{4}$/', (string) $year)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Invalid month/year provided for bulk mark as paid.</div>');
            redirect('admin/payroll');
        }

        if (empty($payment_mode) || empty($payment_date_raw)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Payment mode and payment date are required for bulk mark as paid.</div>');
            redirect('admin/payroll/search/' . $month . '/' . $year . '/' . $role);
        }

        $payment_date = $this->customlib->dateFormatToYYYYMMDD($payment_date_raw);
        if (empty($payment_date)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">Invalid payment date provided.</div>');
            redirect('admin/payroll/search/' . $month . '/' . $year . '/' . $role);
        }

        $staff_list = $this->payroll_model->searchEmployee($month, $year, '', $role);
        $updated = 0;
        $skipped = 0;

        foreach ($staff_list as $staff) {
            $payslip_id = isset($staff['payslip_id']) ? (int) $staff['payslip_id'] : 0;
            $status = strtolower(trim((string) ($staff['status'] ?? '')));

            if ($payslip_id <= 0 || $status === '' || $status === 'paid') {
                $skipped++;
                continue;
            }

            $data = array(
                'payment_mode' => $payment_mode,
                'payment_date' => $payment_date,
                'remark' => $payment_note,
                'status' => 'paid',
            );
            $this->payroll_model->paymentSuccess($data, $payslip_id);
            $updated++;
        }

        $message = '<div class="alert alert-success text-center">Bulk mark as paid completed. Updated: ' . $updated . '.</div>';
        if ($skipped > 0) {
            $message .= '<div class="alert alert-info text-center">Skipped: ' . $skipped . ' (already paid or no payslip).</div>';
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

        $payslip_id = (int) $this->input->post('payslip_id');  // Get payslip ID if available
        $staff_id = (int) $this->input->post('staff_id');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $month_numeric = date('n', strtotime($year . '-' . $month . '-01'));
        $basic = $this->input->post('basic');
        $basic = $basic ? convertCurrencyFormatToBaseAmount($basic) : 0;
        $staff_data = $this->payroll_model->searchEmployeeById($staff_id);
        if (!is_array($staff_data)) {
            $staff_data = [];
        }
        $basic = $this->resolvePayrollBasicAmount($basic, $staff_data, $staff_id);

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

        // If payslip_id is provided, read existing earnings from database for accurate calculation
        // This ensures EPF/ESI are calculated correctly even when form data is incomplete
        if ($payslip_id > 0) {
            $existing_allowances = $this->payroll_model->getAllowance($payslip_id, 'positive');
            if (!empty($existing_allowances)) {
                foreach ($existing_allowances as $allowance) {
                    $amount = (float) $allowance['amount'];
                    $type_code = strtoupper(trim($allowance['allowance_type'] ?? ''));
                    
                    // Skip BASIC and TEMP - TEMP will be recalculated fresh if needed for the current month
                    // This prevents double-counting the increment on recalculation
                    if ($type_code === 'BASIC' || $type_code === 'TEMP') {
                        continue;
                    }
                    
                    // Extract DA for EPF calculations
                    if ($type_code === 'DA') {
                        $da = (float) $amount;
                    }
                    
                    $total_allowance += $amount;
                }
            }
            
            // Add increment if this is the increment month (fresh calculation, no double-counting)
            if ($is_increment_month && $increment_amount > 0) {
                $total_allowance += $increment_amount;
            }
        } else {
            // For new payslips without payslip_id, calculate from form data
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

                    // Skip basic salary and TEMP; TEMP increment is calculated separately from increment record
                    // This prevents double-counting the increment on recalculation
                    if ($code === 'BASIC' || $code === 'TEMP') {
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

            // Add increment if this is the increment month
            if ($is_increment_month && $increment_amount > 0) {
                $total_allowance += $increment_amount;
            }
        }

        $existing_pt_amount = $this->getExistingProfessionalTaxAmount((int) $payslip_id);
        $pt_in_payload = false;
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

                $code = strtoupper(trim($type['allowance_code'] ?? ''));
                if (in_array($code, ['EPF', 'ESI', 'TDS'], true)) {
                    continue;
                }

                if ($code === 'PT') {
                    $pt_in_payload = true;
                }

                $total_deduction += (float) $amount;
            }
        }

        $onetime_payload = $this->getOneTimeDeductionPayload($staff_id, $month_numeric, $year);
        $onetime_deduction_rows = $onetime_payload['rows'];
        $total_deduction += (float) ($onetime_payload['total'] ?? 0);
        $has_onetime_pt = false;
        foreach ((array) $onetime_deduction_rows as $pt_row) {
            if (strtoupper(trim((string) ($pt_row['deduction_type'] ?? ''))) === 'PT') {
                $has_onetime_pt = true;
                break;
            }
        }

        if (!$pt_in_payload && !$has_onetime_pt && $existing_pt_amount > 0) {
            $total_deduction += (float) $existing_pt_amount;
        }

        $month_num = $month_numeric;
        $newdate = date('Y-m-d', strtotime($year . '-' . $month . ' +1 month'));
        $monthAttendanceData = $this->monthAttendance($newdate, 3, $staff_id);
        $monthLeaves = $this->monthLeaves($newdate, 3, $staff_id);
        $lop_summary = $this->getPayrollLopSummary($monthAttendanceData, $monthLeaves, $month, $year, $staff_id);
        $existing_payslip = [];
        if ($payslip_id > 0) {
            $existing_payslip = (array) $this->payroll_model->getPayslip($payslip_id);
        }

        $lop_values = $this->resolvePayrollLopValues(
            $staff_id,
            $month,
            $year,
            (array) $lop_summary,
            $existing_payslip,
            false
        );
        $actual_lop_days = (float) ($lop_values['actual_lop_days'] ?? 0);
        $adjusted_lop_days = (float) ($lop_values['adjusted_lop_days'] ?? 0);
        $net_lop_days = (float) ($lop_values['net_lop_days'] ?? $actual_lop_days);
        $total_days_of_month = (int) ($lop_values['total_days_of_month'] ?? cal_days_in_month(CAL_GREGORIAN, (int) $month_num, (int) $year));

        $epf_wage = 0;
        $employee_epf = 0;
        $employer_pf = 0;
        $employer_eps = 0;
        $employer_edli = 0;
        $employer_admin = 0;
        $esi_wage = 0;
        $esi_deduction = 0;

        $full_gross_salary = (float) $basic + (float) $total_allowance;
        $prorata = $this->applyDojProrataToGross($full_gross_salary, $month_num, $year, $staff_data['date_of_joining'] ?? null);

        $doj_prorated_gross = isset($prorata['prorated_gross_salary']) ? (float) $prorata['prorated_gross_salary'] : (float) $full_gross_salary;
        $doj_prorated_gross = max(0, min((float) $full_gross_salary, $doj_prorated_gross));

        $lop_deduction = 0;
        if ($total_days_of_month > 0 && (float) $net_lop_days > 0) {
            $lop_deduction = (($full_gross_salary / $total_days_of_month) * (float) $net_lop_days);
        }
        $lop_deduction = max(0, min($lop_deduction, $doj_prorated_gross));

        $gross_salary = max(0, $doj_prorated_gross - $lop_deduction);

        $has_uan = isset($staff_data['uan_no']) && trim((string) $staff_data['uan_no']) !== '';
        $has_esi_no = isset($staff_data['esi_no']) && trim((string) $staff_data['esi_no']) !== '';
        if ($has_uan) {
            // preview should include all current earnings (total_allowance already has increment if applicable)
            $epf_wage = $this->tax_epf_calculator->calculate_epf_wage($gross_salary, $lop_deduction);
            $employee_epf = $this->tax_epf_calculator->calculate_employee_epf($epf_wage);
            $employer_pf = $this->tax_epf_calculator->calculate_employer_pf($epf_wage);
            $employer_eps = $this->tax_epf_calculator->calculate_employer_eps($epf_wage);
            $employer_edli = $this->tax_epf_calculator->calculate_employer_edli($epf_wage);
            $employer_admin = $this->tax_epf_calculator->calculate_employer_admin_charges($epf_wage);
        }

        // ESI calculation based on gross salary and LOP for preview
        if ($has_esi_no) {
            $esi_wage = $this->tax_epf_calculator->calculate_esi_wage($gross_salary, $lop_deduction);
            $esi_deduction = $this->tax_epf_calculator->calculate_employee_esi($esi_wage);
        }

        // Check for flat TDS percentage override set on the staff profile
        $flat_tds_pct = isset($staff_data['tds_percentage']) && $staff_data['tds_percentage'] !== null ? (float)$staff_data['tds_percentage'] : 0;
        if ($flat_tds_pct > 0) {
            // Flat % on gross salary — skip new-regime slab calculation entirely
            $monthly_tds = $this->roundPayrollAmount($gross_salary * ($flat_tds_pct / 100));
        } else {
            $fy_start_month = $this->getConfiguredPayrollFyStartMonth();
            $fy_month_index = $this->tax_epf_calculator->get_fy_month_index($month_num, $fy_start_month);
            $ytd_data = $this->payroll_model->getYTDIncome($staff_id, $year, $month_num, $fy_start_month, false, true);

            $opening_ytd = $this->getApplicableOpeningYtd($staff_data, (int) $year, (int) $month_num, (int) $fy_start_month);
            $opening_ytd_income = (float) $opening_ytd['income'];
            $opening_ytd_tax = (float) $opening_ytd['tax'];

            $effective_ytd_income = (float) ($ytd_data['gross'] ?? 0) + max(0, $opening_ytd_income);
            $effective_tax_paid = (float) ($ytd_data['tax_deducted'] ?? 0) + max(0, $opening_ytd_tax);

            if ($effective_ytd_income > 0 && $fy_month_index > 1) {
                $tds_result = $this->tax_epf_calculator->calculate_tds_ytd(
                    $ytd_income = $effective_ytd_income,
                    $current_month_gross = $gross_salary,
                    $current_month = $fy_month_index,
                    $total_months = 12,
                    $tax_already_deducted = $effective_tax_paid
                );
                $monthly_tds = $tds_result['monthly_tds'];
            } else {
                $monthly_tds = $this->tax_epf_calculator->calculate_monthly_tds($gross_salary);
            }
            $monthly_tds = $this->roundPayrollAmount($monthly_tds);
        }

        // Gross salary is already prorated by payable present days, so don't deduct LOP again here
        $total_with_epf_tds_esi = (float) $employee_epf + (float) $esi_deduction + (float) $monthly_tds + (float) $total_deduction;
        $net_salary = $this->roundPayrollAmount($gross_salary - $total_with_epf_tds_esi);
        
        // Update total_deduction to include statutory deductions for response and form display
        // This ensures the summary card calculations are correct
        $total_deduction_with_statutory = $total_deduction + $employee_epf + $esi_deduction + $monthly_tds;

        // Calculate ESI employer contribution (3.25% of ESI wage)
        $employer_esi = 0;
        if ($esi_wage > 0) {
            $employer_esi = round($esi_wage * 0.0325, 0);
        }

        // ========================================
        // SAVE CALCULATED VALUES TO DATABASE
        // ========================================
        if ($payslip_id > 0) {
            // DELETE ALL DEDUCTIONS FIRST (both manual and statutory)
            // This ensures clean slate for this month's calculations
            $this->db->where('payslip_id', $payslip_id)
                     ->where('cal_type', 'negative')
                     ->delete('payslip_allowance');

            // Re-insert manual deductions from form (non-statutory only)
            $deduction_type_id = $this->input->post('deduction_type_id');
            $deduction_amount = $this->input->post('deduction_amount');
            
            $pt_saved_from_form = false;
            if (!empty($deduction_type_id) && !empty($deduction_amount)) {
                // Build allowance type map for code lookup
                $type_code_map = [];
                foreach ($allowance_types as $type) {
                    $type_code_map[(int)$type['id']] = strtoupper(trim($type['allowance_code'] ?? ''));
                }
                
                foreach ($deduction_type_id as $idx => $type_id) {
                    $type_id = (int)$type_id;
                    if ($type_id <= 0 || !isset($deduction_amount[$idx])) {
                        continue;
                    }
                    
                    $type_code = $type_code_map[$type_id] ?? '';
                    // Keep PT as manual deduction; skip only auto-calculated statutory deductions.
                    if (in_array($type_code, ['EPF', 'ESI', 'TDS'], true)) {
                        continue;
                    }

                    if ($type_code === 'PT') {
                        $pt_saved_from_form = true;
                    }

                    $amount = convertCurrencyFormatToBaseAmount($deduction_amount[$idx]);
                    $deduction_data = array(
                        'payslip_id'     => $payslip_id,
                        'allowance_type' => $type_code,
                        'amount'         => (float) $amount,
                        'staff_id'       => $staff_id,
                        'cal_type'       => 'negative',
                    );
                    $this->payroll_model->add_allowance($deduction_data);
                }
            }

            if (!$pt_saved_from_form && !$has_onetime_pt && $existing_pt_amount > 0) {
                $pt_data = array(
                    'payslip_id'     => $payslip_id,
                    'allowance_type' => 'PT',
                    'amount'         => round($existing_pt_amount, 2),
                    'staff_id'       => $staff_id,
                    'cal_type'       => 'negative',
                );
                $this->payroll_model->add_allowance($pt_data);
            }

            $this->addOneTimeDeductionsToPayslip((int) $payslip_id, (int) $staff_id, $onetime_deduction_rows);

            // ======== ADD ONLY APPLICABLE STATUTORY DEDUCTIONS ========
            // Get statutory allowance type codes from database
            $epf_code = $this->payroll_model->getStatutoryAllowanceCode('EPF');
            $esi_code = $this->payroll_model->getStatutoryAllowanceCode('ESI');
            $tds_code = $this->payroll_model->getStatutoryAllowanceCode('TDS');
            
            // Add EPF deduction ONLY if employee_epf > 0
            if (!empty($employee_epf) && $employee_epf > 0 && $epf_code) {
                $epf_data = array(
                    'payslip_id'     => $payslip_id,
                    'allowance_type' => $epf_code,
                    'amount'         => round($employee_epf, 2),
                    'staff_id'       => $staff_id,
                    'cal_type'       => 'negative',
                );
                $this->payroll_model->add_allowance($epf_data);
            }
            
            // Add ESI deduction ONLY if employee_esi > 0 AND esi_wage > 0
            // ESI eligibility is determined by calculate_esi_wage() based on gross and LOP rules
            if ($esi_wage > 0 && !empty($esi_deduction) && $esi_deduction > 0 && $esi_code) {
                $esi_data = array(
                    'payslip_id'     => $payslip_id,
                    'allowance_type' => $esi_code,
                    'amount'         => round($esi_deduction, 2),
                    'staff_id'       => $staff_id,
                    'cal_type'       => 'negative',
                );
                $this->payroll_model->add_allowance($esi_data);
            }
            
            // Add TDS deduction ONLY if monthly_tds > 0
            if (!empty($monthly_tds) && $monthly_tds > 0 && $tds_code) {
                $tds_data = array(
                    'payslip_id'     => $payslip_id,
                    'allowance_type' => $tds_code,
                    'amount'         => $monthly_tds,
                    'staff_id'       => $staff_id,
                    'cal_type'       => 'negative',
                );
                $this->payroll_model->add_allowance($tds_data);
            }

            // Update payslip totals in staff_payslip table
            $payslip_data = array(
                'basic'           => round($basic, 2),
                'total_allowance' => round($total_allowance, 2),
                'total_deduction' => round($total_deduction, 2),
                'leave_deduction' => round($lop_deduction, 2),
                'actual_lop_days' => round($actual_lop_days, 2),
                'adjusted_lop_days' => round($adjusted_lop_days, 2),
                'net_lop_days' => round($net_lop_days, 2),
                'net_salary'      => $this->roundPayrollAmount($net_salary),
                'epf_wage'        => round($epf_wage, 2),
                'employee_epf'    => round($employee_epf, 2),
                'employer_pf'     => round($employer_pf, 2),
                'employer_eps'    => round($employer_eps, 2),
                'esi_wage'        => round($esi_wage, 2),
                'employee_esi'    => round($esi_deduction, 2),
                'employer_esi'    => $employer_esi,
                'tax'             => $monthly_tds,
            );
            $this->db->where('id', $payslip_id)->update('staff_payslip', $payslip_data);
        }

        $response = [
            'success' => true,
            'total_allowance' => round($total_allowance, 2),
            'total_deduction' => round($total_deduction_with_statutory, 2),  // Include all statutory deductions
            'gross_salary' => round($gross_salary, 2),
            'leave_deduction' => round($lop_deduction, 2),
            'net_salary' => $this->roundPayrollAmount($net_salary),
            'epf_wage' => round($epf_wage, 2),
            'employee_epf' => round($employee_epf, 2),
            'employer_pf' => round($employer_pf, 2),
            'employer_eps' => round($employer_eps, 2),
            'esi_wage' => round($esi_wage, 2),
            'employee_esi' => round($esi_deduction, 2),
            'employer_esi' => $employer_esi,
            'tds' => $monthly_tds,
            'actual_lop_days' => round($actual_lop_days, 2),
            'adjusted_lop_days' => round($adjusted_lop_days, 2),
            'net_lop_days' => round($net_lop_days, 2),
            'is_prorata_applied' => !empty($prorata['is_prorata_applied']),
            'payable_days' => (int) ($prorata['payable_days'] ?? 0),
            'total_days' => (int) ($prorata['total_days'] ?? 0),
            'prorata_deduction' => round((float) ($prorata['prorata_deduction'] ?? 0), 2),
            'is_increment_month' => $is_increment_month,
            'increment_amount' => round($increment_amount, 2)
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    // close calculatepreview
    
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
        $this->load->library('form_validation');
        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');
        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|callback_valid_payroll_month');
        $this->form_validation->set_rules('year', $this->lang->line('year'), 'trim|required|integer|exact_length[4]');

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
                            'net_salary' => $this->roundPayrollAmount($basic_salary + $total_allowance - $total_deduction),
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

    public function valid_payroll_month($month)
    {
        $valid_months = array(
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        );

        if (in_array(trim($month), $valid_months, true)) {
            return true;
        }

        $this->form_validation->set_message('valid_payroll_month', 'The {field} field is invalid.');
        return false;
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
                    // Query Special Allowance directly from payslip_allowance table
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

    public function bulk_onetime_deduction()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');

        $data['page_title'] = 'Bulk Upload One-Time Deductions';
        $data['monthlist'] = $this->customlib->getMonthDropdown();
        $data['year'] = date('Y');

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/bulk_onetime_deduction", $data);
        $this->load->view("layout/footer", $data);
    }

    public function download_onetime_deduction_template()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $filename = 'onetime_deduction_sample_template.csv';
        $csv_rows = [
            ['employee_id', 'amount', 'deduction_type', 'remarks'],
            ['EMP001', '1500', 'ADVANCE', 'March one-time advance recovery'],
            ['EMP002', '750', 'LOAN', 'One-time loan deduction'],
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        foreach ($csv_rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function save_bulk_onetime_deduction()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_create')) {
            access_denied();
        }

        $month = trim((string) $this->input->post('month'));
        $year = (int) $this->input->post('year');
        $default_deduction_type = strtoupper(trim((string) $this->input->post('deduction_type')));
        $remarks = trim((string) $this->input->post('remarks'));

        if ($month === '' || $year <= 0) {
            $this->session->set_flashdata('error', 'Month and year are required.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $month_numeric = (int) date('n', strtotime($year . '-' . $month . '-01'));
        if ($month_numeric < 1 || $month_numeric > 12) {
            $this->session->set_flashdata('error', 'Invalid month selected.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
            $this->session->set_flashdata('error', 'Please upload a CSV file.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->session->set_flashdata('error', 'Only CSV files are allowed.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $upload_dir = 'uploads/payroll_import/';
        $this->customlib->ensureDirectoryExists($upload_dir);
        $temp_name = 'onetime_deduction_' . time() . '_' . mt_rand(1000, 9999) . '.csv';
        $file_path = $upload_dir . $temp_name;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $this->session->set_flashdata('error', 'Unable to upload the file.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $this->load->library('CSVReader');
        $rows = $this->csvreader->parse_file($file_path, true);
        if (empty($rows)) {
            @unlink($file_path);
            $this->session->set_flashdata('error', 'No records found in uploaded CSV.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $valid_codes = $this->getManualDeductionCodeLookup();
        if (empty($valid_codes)) {
            @unlink($file_path);
            $this->session->set_flashdata('error', 'No manual deduction types available. Please configure deduction allowance types first.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $default_code = $default_deduction_type;
        if ($default_code !== '') {
            $default_lookup_key = $this->normalizeDeductionTypeKey($default_code);
            $default_code = $valid_codes[$default_code] ?? ($valid_codes[$default_lookup_key] ?? '');
        }
        if ($default_deduction_type !== '' && $default_code === '') {
            @unlink($file_path);
            $this->session->set_flashdata('error', 'Default deduction type is invalid or statutory.');
            redirect('admin/payroll/bulk_onetime_deduction');
        }

        $user_data = $this->customlib->getUserData();
        $admin_id = isset($user_data['id']) ? (int) $user_data['id'] : 0;

        $processed = 0;
        $skipped = 0;
        $errors = [];

        $this->db->trans_start();
        foreach ($rows as $index => $row) {
            $employee_id = trim((string) ($row['employee_id'] ?? $row['staff_id'] ?? ''));
            $row_code_raw = strtoupper(trim((string) ($row['deduction_type'] ?? $row['type'] ?? $default_code)));
            $row_amount_raw = $row['amount'] ?? $row['deduction_amount'] ?? null;
            $row_remarks = trim((string) ($row['remarks'] ?? $remarks));

            if ($employee_id === '' || $row_code_raw === '' || $row_amount_raw === null || $row_amount_raw === '') {
                $skipped++;
                $errors[] = 'Row ' . ($index + 2) . ': missing employee_id/deduction_type/amount';
                continue;
            }

            $row_code = $valid_codes[$row_code_raw] ?? ($valid_codes[$this->normalizeDeductionTypeKey($row_code_raw)] ?? '');
            if ($row_code === '') {
                $skipped++;
                $errors[] = 'Row ' . ($index + 2) . ': invalid or statutory deduction type ' . $row_code_raw;
                continue;
            }

            $amount = (float) convertCurrencyFormatToBaseAmount($row_amount_raw);
            if ($amount <= 0) {
                $skipped++;
                $errors[] = 'Row ' . ($index + 2) . ': amount must be greater than 0';
                continue;
            }

            $staff = $this->staff_model->get_by_employee_id($employee_id);
            if (empty($staff)) {
                $staff = $this->staff_model->get_by_biometric_id($employee_id);
                if (is_object($staff)) {
                    $staff = (array) $staff;
                }
            }

            if (empty($staff) || empty($staff['id'])) {
                $skipped++;
                $errors[] = 'Row ' . ($index + 2) . ': employee not found (' . $employee_id . ')';
                continue;
            }

            $saved = $this->payroll_onetime_deduction_model->upsertDeduction([
                'staff_id' => (int) $staff['id'],
                'month' => $month_numeric,
                'year' => $year,
                'deduction_type' => $row_code,
                'amount' => $amount,
                'remarks' => $row_remarks,
                'admin_user_id' => $admin_id,
            ]);

            if ($saved) {
                $processed++;
            } else {
                $skipped++;
                $errors[] = 'Row ' . ($index + 2) . ': failed to save';
            }
        }
        $this->db->trans_complete();
        @unlink($file_path);

        if ($processed > 0) {
            $this->session->set_flashdata('success', 'One-time deductions uploaded successfully. Saved: ' . $processed . '.');
        }

        if ($skipped > 0) {
            $error_preview = implode(' | ', array_slice($errors, 0, 8));
            if (count($errors) > 8) {
                $error_preview .= ' | ...';
            }
            $this->session->set_flashdata('error', 'Skipped: ' . $skipped . '. ' . $error_preview);
        }

        if ($processed === 0 && $skipped === 0) {
            $this->session->set_flashdata('error', 'No valid rows found in file.');
        }

        redirect('admin/payroll/bulk_onetime_deduction');
    }

    public function pending_onetime_deductions()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/payroll');

        $data['page_title'] = 'Pending One-Time Deductions';
        $data['deductions'] = $this->payroll_onetime_deduction_model->getPendingDeductions();

        $this->load->view("layout/header", $data);
        $this->load->view("admin/payroll/pending_onetime_deductions", $data);
        $this->load->view("layout/footer", $data);
    }

    public function approve_onetime_deduction($id)
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        $userdata = $this->customlib->getUserData();
        $admin_id = (isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $result = $this->payroll_onetime_deduction_model->approveDeduction((int) $id, $admin_id);
        if ($result) {
            $this->session->set_flashdata('success', 'One-time deduction approved successfully.');
        } else {
            $this->session->set_flashdata('error', 'Unable to approve deduction.');
        }

        redirect('admin/payroll/pending_onetime_deductions');
    }

    public function reject_onetime_deduction($id)
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        $userdata = $this->customlib->getUserData();
        $admin_id = (isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $result = $this->payroll_onetime_deduction_model->rejectDeduction((int) $id, $admin_id);
        if ($result) {
            $this->session->set_flashdata('success', 'One-time deduction rejected.');
        } else {
            $this->session->set_flashdata('error', 'Unable to reject deduction.');
        }

        redirect('admin/payroll/pending_onetime_deductions');
    }

    public function approve_all_onetime_deductions()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        $pending = $this->payroll_onetime_deduction_model->getPendingDeductions();
        if (empty($pending)) {
            $this->session->set_flashdata('error', 'No pending one-time deductions found.');
            redirect('admin/payroll/pending_onetime_deductions');
        }

        $userdata = $this->customlib->getUserData();
        $admin_id = (isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $approved_count = 0;
        foreach ($pending as $row) {
            if ($this->payroll_onetime_deduction_model->approveDeduction((int) $row['id'], $admin_id)) {
                $approved_count++;
            }
        }

        if ($approved_count > 0) {
            $this->session->set_flashdata('success', 'Approved ' . $approved_count . ' one-time deduction(s).');
        } else {
            $this->session->set_flashdata('error', 'Unable to approve pending one-time deductions.');
        }

        redirect('admin/payroll/pending_onetime_deductions');
    }

    public function reject_all_onetime_deductions()
    {
        if (!$this->rbac->hasPrivilege('staff_payroll', 'can_edit')) {
            access_denied();
        }

        $pending = $this->payroll_onetime_deduction_model->getPendingDeductions();
        if (empty($pending)) {
            $this->session->set_flashdata('error', 'No pending one-time deductions found.');
            redirect('admin/payroll/pending_onetime_deductions');
        }

        $userdata = $this->customlib->getUserData();
        $admin_id = (isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $rejected_count = 0;
        foreach ($pending as $row) {
            if ($this->payroll_onetime_deduction_model->rejectDeduction((int) $row['id'], $admin_id)) {
                $rejected_count++;
            }
        }

        if ($rejected_count > 0) {
            $this->session->set_flashdata('success', 'Rejected ' . $rejected_count . ' one-time deduction(s).');
        } else {
            $this->session->set_flashdata('error', 'Unable to reject pending one-time deductions.');
        }

        redirect('admin/payroll/pending_onetime_deductions');
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

