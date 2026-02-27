<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Specialattendance extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->model('Department_model');
        $this->load->model('Staff_model');
        // Permission check
        if (!$this->rbac->hasPrivilege('Special Attendance', 'can_view')) {
            access_denied();
        }
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/specialattendance/index');
        
        $data['title'] = $this->lang->line('special_attendance');
        $data['departments'] = $this->Department_model->getDepartmentType();
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/specialattendance/index', $data);
        $this->load->view('layout/footer', $data);
    }
    
    public function get_employees_by_department()
    {
        $department_id = $this->input->post('department_id');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $employees = $this->Staff_model->getByDepartment($department_id);

        $presentCounts = [];
        $presentEquivalent = [];
        $workingDays = null;
        if (!empty($month) && !empty($year) && !empty($employees)) {
            $this->load->model('StaffBiometricPunchesManual_model');
            $this->load->model('SpecialAttendance_model');
            $staffIds = array_map(function ($emp) {
                return $emp['id'];
            }, $employees);
            $presentCounts = $this->StaffBiometricPunchesManual_model->getSpecialAttendanceCounts($staffIds, $month, $year);
            $presentEquivalent = $this->StaffBiometricPunchesManual_model->getSpecialAttendancePresentEquivalent($staffIds, $month, $year);
            $workingDays = $this->SpecialAttendance_model->getWorkingDaysCount($month, $year);
        }

        $filteredEmployees = [];
        foreach ($employees as &$emp) {
            $emp['present_days'] = isset($presentCounts[$emp['id']]) ? $presentCounts[$emp['id']] : 0;
            $hasSpecial = isset($presentEquivalent[$emp['id']]);
            $emp['has_special_attendance'] = $hasSpecial ? 1 : 0;

            $presentEquivalentDays = $hasSpecial ? (float)$presentEquivalent[$emp['id']] : 0.0;
            if ($workingDays !== null && (float)$workingDays > 0) {
                $attendancePercent = ($presentEquivalentDays / (float)$workingDays) * 100;
            } else {
                $attendancePercent = 0;
            }
            $emp['attendance_percentage'] = round($attendancePercent, 2);

            if ($hasSpecial && $workingDays !== null) {
                $lopDays = (float)$workingDays - (float)$presentEquivalent[$emp['id']];
                if ($lopDays < 0) {
                    $lopDays = 0;
                }
                $emp['lop_days'] = round($lopDays, 2);
            } else {
                $emp['lop_days'] = null;
            }

            if ($workingDays !== null && (float)$workingDays > 0 && $emp['attendance_percentage'] < 50) {
                $filteredEmployees[] = $emp;
            }
        }
        unset($emp);

        echo json_encode($filteredEmployees);
    }
    
    public function get_working_days()
    {
        $month = $this->input->post('month');
        $year = $this->input->post('year');

        $validMonths = array(
            'January','February','March','April','May','June',
            'July','August','September','October','November','December'
        );

        if (empty($month) || empty($year) || !in_array($month, $validMonths, true) || !ctype_digit((string)$year)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid month or year']))
                ->_display();
            exit;
        }
        
        // Get weekend configuration from settings
        $this->load->model('setting_model');
        $settings = $this->setting_model->getSetting();
        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayHoliday = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;
        
        // Get holidays from annual_calendar (exclude compensation from holiday list)
        $this->db->select('annual_calendar.from_date, annual_calendar.to_date, holiday_type.type');
        $this->db->from('annual_calendar');
        $this->db->join('holiday_type', 'holiday_type.id = annual_calendar.holiday_type', 'left');
        $this->db->where('is_active', 1);
        $this->db->where('(MONTH(from_date) = ' . date('n', strtotime("$month 1, $year")) . ' AND YEAR(from_date) = ' . $year . ') 
                         OR (MONTH(to_date) = ' . date('n', strtotime("$month 1, $year")) . ' AND YEAR(to_date) = ' . $year . ')
                         OR (from_date <= "' . $year . '-' . str_pad(date('n', strtotime("$month 1, $year")), 2, '0', STR_PAD_LEFT) . '-' . date('t', strtotime("$month 1, $year")) . '" 
                             AND to_date >= "' . $year . '-' . str_pad(date('n', strtotime("$month 1, $year")), 2, '0', STR_PAD_LEFT) . '-01")');
        $holidays = $this->db->get()->result_array();
        
        // Calculate working days
        $firstDay = DateTime::createFromFormat('F j, Y', $month . ' 1, ' . $year);
        if ($firstDay === false) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid date']))
                ->_display();
            exit;
        }

        $monthIndex = (int)$firstDay->format('n') - 1;
        $daysInMonth = (int)$firstDay->format('t');
        $workingDays = 0;
        $holidayDates = [];
        $compensationDates = [];
        
        // Collect all holiday dates
        foreach ($holidays as $holiday) {
            $from = new DateTime(date('Y-m-d', strtotime($holiday['from_date'])));
            $to = new DateTime(date('Y-m-d', strtotime($holiday['to_date'])));
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($from, $interval, $to->modify('+1 day'));
            
            foreach ($period as $date) {
                if ($date->format('n') == ($monthIndex + 1) && $date->format('Y') == $year) {
                    $type_label = strtolower(trim($holiday['type'] ?? ''));
                    if ($type_label === 'compensation') {
                        $compensationDates[] = $date->format('Y-m-d');
                    } else {
                        $holidayDates[] = $date->format('Y-m-d');
                    }
                }
            }
        }
        
        // Add second Saturday holidays if enabled
        if ($isSecondSaturdayHoliday) {
            // Find all Saturdays in the month and identify the second one
            $saturdayCount = 0;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = new DateTime("$year-" . str_pad($monthIndex + 1, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT));
                $dayOfWeek = (int)$date->format('w');
                
                if ($dayOfWeek == 6) { // Saturday
                    $saturdayCount++;
                    if ($saturdayCount == 2) { // Second Saturday
                        $dateStr = $date->format('Y-m-d');
                        if (!in_array($dateStr, $holidayDates, true)) {
                            $holidayDates[] = $dateStr;
                        }
                        break;
                    }
                }
            }
        }

        $compensationDates = array_values(array_unique($compensationDates));
        $holidayDates = array_values(array_diff(array_unique($holidayDates), $compensationDates));
        
        // Count working days (exclude configured weekend days and holidays)
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = new DateTime("$year-" . str_pad($monthIndex + 1, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT));
            $dayOfWeek = (int)$date->format('w');
            $dateStr = $date->format('Y-m-d');
            
            // Treat compensation days as working days, even if weekend
            if (in_array($dateStr, $compensationDates, true)) {
                $workingDays++;
                continue;
            }

            // Check if day is a weekend or holiday
            if (!in_array($dayOfWeek, $weekendDays, true) && !in_array($dateStr, $holidayDates, true)) {
                $workingDays++;
            }
        }

        log_message('debug', 'Specialattendance get_working_days month=' . $month . ' year=' . $year .
            ' weekendDays=' . json_encode($weekendDays) .
            ' secondSaturday=' . $isSecondSaturdayHoliday .
            ' holidayDates=' . json_encode($holidayDates) .
            ' daysInMonth=' . $daysInMonth .
            ' workingDays=' . $workingDays);
        
        echo json_encode([
            'working_days' => $workingDays,
            'payable_working_days' => $daysInMonth,
            'holidays' => $holidayDates,
            'weekend_days' => $weekendDays
        ]);
    }
    
    public function generate_attendance()
    {
        // Only admin can run
        if (!$this->rbac->hasPrivilege('Special Attendance', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $employee_ids = $this->input->post('employee_ids');
        $days_absent = $this->input->post('days_absent');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $reason = $this->input->post('reason');
        $admin_user_id = $this->session->userdata('id');

        $valid_entries = 0;
        if (!empty($employee_ids) && !empty($days_absent)) {
            foreach ($employee_ids as $emp_id) {
                $raw_days = isset($days_absent[$emp_id]) ? trim((string)$days_absent[$emp_id]) : '';
                if ($raw_days !== '' && is_numeric($raw_days) && (float)$raw_days >= 0) {
                    $valid_entries++;
                }
            }
        }

        if ($valid_entries === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter LOP Days (0 or more) for at least one staff member.']);
            return;
        }
        
        // Process attendance generation
        $this->load->model('SpecialAttendance_model');
        $this->load->model('StaffAttendanceSchedule_model');
        $this->load->model('StaffBiometricPunchesManual_model');
        
        foreach ($employee_ids as $index => $emp_id) {
            $raw_days = isset($days_absent[$emp_id]) ? trim((string)$days_absent[$emp_id]) : '';
            if ($raw_days !== '' && is_numeric($raw_days) && (float)$raw_days >= 0) {
                $days = (float)$raw_days;
                $schedule = $this->StaffAttendanceSchedule_model->getByStaffId($emp_id);
                $punches = $this->SpecialAttendance_model->generatePunchesFromLop($emp_id, $month, $year, $days, $schedule);
                $this->StaffBiometricPunchesManual_model->replacePunches($emp_id, $month, $year, $punches, $admin_user_id, $reason);
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'Attendance generated successfully']);
    }

    public function process_attendance()
    {
        if (!$this->rbac->hasPrivilege('Special Attendance', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $employee_ids = $this->input->post('employee_ids');
        $days_absent = $this->input->post('days_absent'); // optional array
        $month = $this->input->post('month');
        $year = $this->input->post('year');

        if (empty($employee_ids) || empty($month) || empty($year)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
            return;
        }

        // if days_absent provided, keep only rows with explicit numeric values >= 0
        if (!empty($days_absent) && is_array($days_absent)) {
            $employee_ids = array_filter($employee_ids, function($id) use ($days_absent) {
                if (!isset($days_absent[$id])) {
                    return false;
                }
                $raw_days = trim((string)$days_absent[$id]);
                return ($raw_days !== '' && is_numeric($raw_days) && (float)$raw_days >= 0);
            });
        }

        if (empty($employee_ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No valid staff selected for processing']);
            return;
        }

        $this->load->model('Attendance_model');

        $monthNum = DateTime::createFromFormat('F Y', $month . ' ' . $year);
        if (!$monthNum) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid month/year']);
            return;
        }

        $monthNumber = (int)$monthNum->format('n');

        // Replace mode: remove existing attendance for selected staff and month
        $this->db->where_in('staff_id', $employee_ids);
        $this->db->where('MONTH(date)', $monthNumber);
        $this->db->where('YEAR(date)', (int)$year);
        $this->db->delete('staff_attendance');

        $dates = $this->db->select('DATE(punch_time) AS punch_date')
            ->from('staff_biometric_punches_manual')
            ->where_in('staff_id', $employee_ids)
            ->where('source', 'special_attendance')
            ->where('MONTH(punch_time)', $monthNumber)
            ->where('YEAR(punch_time)', (int)$year)
            ->group_by('DATE(punch_time)')
            ->get()
            ->result_array();

        foreach ($dates as $row) {
            $this->Attendance_model->process_daily_manual_attendance_for_staff($row['punch_date'], $employee_ids, 'special_attendance');
        }

        echo json_encode(['status' => 'success', 'message' => 'Staff attendance processed']);
    }
}
