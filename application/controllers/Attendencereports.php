<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Attendencereports extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('file');
        $this->config->load("mailsms");
        $this->config->load("payroll");
        $this->load->library('mailsmsconf');
        $this->config_attendance = $this->config->item('attendence');
        $this->staff_attendance  = $this->config->item('staffattendance');
        $this->load->model("staffattendancemodel");
        $this->load->model("staff_model");
        $this->load->model("payroll_model");
        $this->load->model("Department_model"); // Added to fix the error
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->search_type        = $this->customlib->get_searchtype();
    }

    public function attendance()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('attendencereports/attendance');
        $this->load->view('layout/footer');
    }

    public function staffdaywiseattendancereport()
    {
        if (!$this->rbac->hasPrivilege('attendance_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/staffdaywiseattendancereport');
        $data['sch_setting'] = $this->sch_setting_detail;

        
        $staffRole                   = $this->staff_model->getStaffRole();
        $data["role"]                = $staffRole;
        $data["role_selected"]       = "";
        $attendencetypes             = $this->attendencetype_model->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;      
        $data['date']           = "";
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {

            $resultlist             = array();
            $role                  = $this->input->post('role');
            $date                  = $this->input->post('date');
            $attendance_mode                  = $this->input->post('attendance_mode');
            $data['role_selected']       = $role;
            $data['date_selected'] = $date;
            $resultlist                  = $this->staffattendancemodel->searchAttendenceUserTypeWithMode($role, date('Y-m-d', $this->customlib->datetostrtotime($date)),$attendance_mode);
            $data['resultlist']          = $resultlist;
        }
        $this->load->view('layout/header', $data);
        $this->load->view('attendencereports/staffdaywiseattendancereport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function daywiseattendancereport()
    {
        if (!$this->rbac->hasPrivilege('attendance_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/daywiseattendancereport');
        $data['sch_setting'] = $this->sch_setting_detail;
        $attendencetypes             = $this->attendencetype_model->getAttType();
        $data['attendencetypeslist'] = $attendencetypes;
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['department_list'] = $this->Department_model->getDepartmentType();
        $data['class_id']       = "";
        $data['section_id']     = "";
        $data['date']           = "";
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {

            $resultlist             = array();
            $class                  = $this->input->post('class_id');
            $section                = $this->input->post('section_id');
            $date                  = $this->input->post('date');
            $attendance_mode                  = $this->input->post('attendance_mode');
            $department_id = $this->input->post('department_id');
            $data['class_id']       = $class;
            $data['section_id']     = $section;
            $data['date_selected'] = $date;
            $attendencetypes             = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;
            $resultlist                  = $this->stuattendence_model->searchAttendenceClassSectionWithMode($class, $section, date('Y-m-d', $this->customlib->datetostrtotime($date)),$attendance_mode, $department_id);
            $data['resultlist']          = $resultlist;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('attendencereports/daywiseattendancereport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function classattendencereport()
    {
        if (!$this->rbac->hasPrivilege('attendance_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/attendance_report');
        $attendencetypes             = $this->attendencetype_model->getAttType();
        $data['attendencetypeslist'] = $attendencetypes;

        $setting_data                 = $this->setting_model->get();
        $data['low_attendance_limit']     = $setting_data[0]['low_attendance_limit'];

        $data['title']               = 'Add Fees Type';
        $data['title_list']          = 'Fees Type List';
        $class                       = $this->class_model->get();
        $userdata                    = $this->customlib->getUserData();

        $role_id = $userdata["role_id"];

        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if ($userdata["class_teacher"] == 'yes') {
                $carray = array();
                $class  = array();
                $class  = $this->teacher_model->get_daywiseattendanceclass($userdata["id"]);
            }
        }
        $data['classlist'] = $class;
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
        $userdata          = $this->customlib->getUserData();

        $data['monthlist']      = $this->customlib->getMonthDropdown();
        $data['yearlist']       = $this->stuattendence_model->attendanceYearCount();
        $data['class_id']       = "";
        $data['section_id']     = "";
        $data['date']           = "";
        $data['month_selected'] = "";
        $data['year_selected']  = "";
        $data['sch_setting']    = $this->sch_setting_detail;
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('attendencereports/classattendencereport', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $resultlist             = array();
            $class                  = $this->input->post('class_id');
            $section                = $this->input->post('section_id');
            $month                  = $this->input->post('month');
            $department_id = $this->input->post('department_id'); // Retrieve department_id
            $data['class_id']       = $class;
            $data['section_id']     = $section;
            $data['month_selected'] = $month;
            $studentlist            = $this->student_model->searchByClassSection($class, $section, $department_id); // Pass department_id
            $session_current        = $this->setting_model->getCurrentSessionName();
            $startMonth             = $this->setting_model->getStartMonth();
            $centenary              = substr($session_current, 0, 2); //2017-18 to 2017
            $year_first_substring   = substr($session_current, 2, 2); //2017-18 to 2017
            $year_second_substring  = substr($session_current, 5, 2); //2017-18 to 18
            $month_number           = date("m", strtotime($month));
            $year                   = $this->input->post('year');
            $data['year_selected']  = $year;
            if (!empty($year)) {

                $year = $this->input->post("year");
            } else {

                if ($month_number >= $startMonth && $month_number <= 12) {
                    $year = $centenary . $year_first_substring;
                } else {
                    $year = $centenary . $year_second_substring;
                }
            }

            $num_of_days        = cal_days_in_month(CAL_GREGORIAN, $month_number, $year);
            $attr_result        = array();
            $attendence_array   = array();
            $student_result     = array();
            $data['no_of_days'] = $num_of_days;
            $date_result        = array();
            for ($i = 1; $i <= $num_of_days; $i++) {
                $att_date           = $year . "-" . $month_number . "-" . sprintf("%02d", $i);
                $attendence_array[] = $att_date;

                $res            = $this->stuattendence_model->searchAttendenceReport($class, $section, $att_date);
                $student_result = $res;
                $s              = array();
                foreach ($res as $result_k => $result_v) {
                    $s[$result_v['student_session_id']] = $result_v;
                }
                $date_result[$att_date] = $s;
            }

            $monthAttendance = array();
            foreach ($res as $result_k => $result_v) {

                $date              = $year . "-" . $month;
                $newdate           = date('Y-m-d', strtotime($date));
                $monthAttendance[] = $this->stuMonthAttendance($newdate, 1, $result_v['student_session_id']);
            }

            $data['monthAttendance'] = $monthAttendance;
            $data['resultlist']       = $date_result;
            $data['attendence_array'] = $attendence_array;
            $data['student_array']    = $student_result;

            $this->load->view('layout/header', $data);
            $this->load->view('attendencereports/classattendencereport', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function stuMonthAttendance($st_month, $no_of_months, $student_id)
    {
        $record = array();
        $r     = array();
        $month = date('m', strtotime($st_month));
        $year  = date('Y', strtotime($st_month));
        foreach ($this->config_attendance as $att_key => $att_value) {
            $s = $this->stuattendence_model->count_attendance_obj($month, $year, $student_id, $att_value);

            $attendance_key = $att_key;
            $r[$attendance_key] = $s;
        }

        $record[$student_id] = $r;
        return $record;
    }

    public function attendancereport()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendence/attendancereport');
        $data['searchlist']      = $this->search_type;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $class                   = $this->input->post('class_id');
        $section                 = $this->input->post('section_id');
        $data['class_id']        = $class;
        $data['section_id']      = $section;
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
        $searchterm              = '';
        $condition               = "";
        $date_condition          = "";
        $department_id = $this->input->post('department_id'); // Retrieve department_id

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $between_date        = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $search_type = $_POST['search_type'];
        } else {
            $between_date        = $this->customlib->get_betweendate('this_week');
            $data['search_type'] = '';
        }

        $from_date = date('Y-m-d', strtotime($between_date['from_date']));
        $to_date   = date('Y-m-d', strtotime($between_date['to_date']));
        $dates     = array();
        $off_date  = array();
        $current   = strtotime($from_date);
        $last      = strtotime($to_date);

        while ($current <= $last) {

            $date    = date('Y-m-d', $current);
            $day     = date("D", strtotime($date));
            $holiday = $this->stuattendence_model->checkholidatbydate($date);

            if ($day == 'Sun' || $holiday > 0) {
                $off_date[] = $date;
            } else {
                $dates[] = $date;
            }

            $current = strtotime('+1 day', $current);
        }

        $data['filter']          = date($this->customlib->getSchoolDateFormat(), strtotime($from_date)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($to_date));
        $data['attendance_type'] = $this->attendencetype_model->getstdAttType('2');
        $this->form_validation->set_rules('attendance_type', $this->lang->line('attendance_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

            $this->load->view('layout/header', $data);
            $this->load->view('attendencereports/stuattendance', $data);
            $this->load->view('layout/footer', $data);
        } else {

            $data['attendance_type_id'] = $attendance_type_id = $this->input->post('attendance_type');
            $condition .= " and `student_attendences`.`attendence_type_id`=" . $this->input->post('attendance_type');
            foreach ($dates as $key => $value) {
            }

            if ($data['class_id'] != '') {
                $condition .= ' and class_id=' . $data['class_id'];
            }
            $condition .= " and date_format(student_attendences.date,'%Y-%m-%d') between '" . $from_date . "' and '" . $to_date . "'";
            if ($data['section_id'] != '') {
                $condition .= ' and section_id=' . $data['section_id'];
            }
            if ($department_id != null) {
                $condition .= ' and classes.department_id=' . $department_id; // Add department filter
            }

            $data['student_attendences'] = $this->stuattendence_model->student_attendences($condition, $date_condition, $department_id); // Pass department_id

            $attd = array();

            foreach ($data['student_attendences'] as $value) {
                $std_id          = $value['id'];
                $attd[$std_id][] = $value;
            }

            foreach ($attd as $key => $att_value) {
                $all_week = 1;
                foreach ($att_value as $value) {

                    if (in_array($value['date'], $off_date)) {
                    } else {
                        if (in_array($value['date'], $dates)) {
                            //echo "Match found";
                        } else {
                            $all_week = 0;
                        }
                    }
                }
                if ($all_week == 1) {
                    $fdata[] = $att_value[0];
                }
            }

            $dates = " '" . $from_date . "' and '" . $to_date . "'";

            $this->load->view('layout/header', $data);
            $this->load->view('attendencereports/stuattendance', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function daily_attendance_report()
    {
        $data = array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/daily_attendance_report');
        $date         = "";
        $data['date'] = "";
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $date         = " and student_attendences.date='" . date('Y-m-d') . "'";
            $data['date'] = date($this->customlib->getSchoolDateFormat());
        } else {
            $date         = " and student_attendences.date='" . date('Y-m-d', $this->customlib->datetostrtotime($_POST['date'])) . "'";
            $data['date'] = date($this->customlib->getSchoolDateFormat(), $this->customlib->datetostrtotime($_POST['date']));
        }

        $resultlist     = array();
        $data['result'] = $this->stuattendence_model->get_attendancebydate($date);
		 
        if (!empty($data['result'])) {
            $all_student = $all_present = $all_absent = 0;
            foreach ($data['result'] as $key => $value) {
                $total_present = $value->present + $value->excuse + $value->late + $value->half_day;
                $total_student = $total_present + $value->absent;
				
                if ($total_present > 0) {
                    $presnt_percent = round(($total_present / $total_student) * 100);
                } else {
                    $presnt_percent = 0; 
                }
				
                if ($value->absent > 0) {
                    $presnt_absent = round(($value->absent / $total_student) * 100);
                } else {
                    $presnt_absent = 0;
                }
				
                $all_student += $total_student;
                $all_present += $total_present;
                $all_absent += $value->absent;

                $data['resultlist'][] = array('class_section' => $value->class_name . " (" . $value->section_name . ")", 'total_present' => $total_present, 'total_absent' => $value->absent, 'present_percent' => $presnt_percent . "%", 'absent_persent' => $presnt_absent . "%", 'total_male_present' => $value->male_present, 'total_female_present' => $value->female_present, 'total_male_absent' => $value->male_absent, 'total_female_absent' => $value->female_absent);
                # code...
            }
            $data['all_student'] = $all_student;
            $data['all_present'] = $all_present;
            $data['all_absent']  = $all_absent;
            if ($all_student > 0) {
                $data['all_present_percent'] = round(($data['all_present'] / $data['all_student']) * 100) . "%";
                $data['all_absent_percent']  = round(($data['all_absent'] / $data['all_student']) * 100) . "%";
            } else {
                $data['all_present_percent'] = "0%";
                $data['all_absent_percent']  = "0%";
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('attendencereports/daily_attendance_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function staffattendancereport()
    {
        if (!$this->rbac->hasPrivilege('staff_attendance_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/staff_attendance_report');
        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;
        $staffRole                   = $this->staff_model->getStaffRole();
        $data["role"]                = $staffRole;
        $data['title']               = 'Attendance Report';
        $data['title_list']          = 'Attendance';
        $data['monthlist']           = $this->customlib->getMonthDropdown();
        $data['yearlist']            = $this->staffattendancemodel->attendanceYearCount();
        $data['date']                = "";
        $data["role_selected"]       = "";
        $role                        = $this->input->post("role");
        $month                       = $this->input->post('month');
        $searchyear                  = $this->input->post('year');
        $data['month_selected']      = $month;
        $data['year_selected']       = $searchyear;

        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('year', $this->lang->line('year'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('attendencereports/staffattendancereport', $data);
            $this->load->view('layout/footer', $data);
        } else {

            $type_map = [];
            foreach ($attendencetypes as $type_row) {
                $key_value = strtoupper(trim($type_row['key_value'] ?? ''));
                if (!empty($key_value)) {
                    $type_map[$key_value] = (int) $type_row['id'];
                }
            }

            $month_number           = date("m", strtotime($month));
            $num_of_days            = cal_days_in_month(CAL_GREGORIAN, $month_number, $searchyear);
            
            $this->load->model("holiday_model");
            $this->load->model("setting_model");
            $holidays = $this->holiday_model->get();
            $official_holiday_dates = [];
            foreach ($holidays as $holiday_key => $holiday_value) {
                $from_date = new DateTime($holiday_value['from_date']);
                $to_date = new DateTime($holiday_value['to_date']);
                
                $current = clone $from_date;
                while ($current <= $to_date) {
                    if ($current->format('m') == $month_number && $current->format('Y') == $searchyear) {
                        $official_holiday_dates[] = $current->format('Y-m-d');
                    }
                    $current->modify('+1 day');
                }
            }
            $data['holiday_dates'] = array_unique($official_holiday_dates);

            $settings = $this->setting_model->getSetting();
            $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
            $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
            $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;

            $second_saturday_date = null;
            if ($isSecondSaturdayWeekend) {
                $saturdayCount = 0;
                for ($i = 1; $i <= $num_of_days; $i++) {
                    $date = new DateTime($searchyear . "-" . $month_number . "-" . sprintf("%02d", $i));
                    if ((int)$date->format('w') == 6) {
                        $saturdayCount++;
                        if ($saturdayCount == 2) {
                            $second_saturday_date = $date->format('Y-m-d');
                            break;
                        }
                    }
                }
            }

            $weekend_day_dates = [];
            for ($i = 1; $i <= $num_of_days; $i++) {
                $dateStr = $searchyear . "-" . $month_number . "-" . sprintf("%02d", $i);
                $dayOfWeek = (int)date('w', strtotime($dateStr));
                if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date && $dateStr === $second_saturday_date)) {
                    $weekend_day_dates[] = $dateStr;
                }
            }
            $weekend_day_dates = array_values(array_unique($weekend_day_dates));
            $data['weekend_day_dates'] = $weekend_day_dates;
            $data['weekend_count'] = count($weekend_day_dates);

            $working_day_dates = [];
            for ($i = 1; $i <= $num_of_days; $i++) {
                $dateStr = $searchyear . "-" . $month_number . "-" . sprintf("%02d", $i);
                if (!in_array($dateStr, $weekend_day_dates, true) && !in_array($dateStr, $data['holiday_dates'], true)) {
                    $working_day_dates[] = $dateStr;
                }
            }
            $data['working_day_dates'] = $working_day_dates;
            $data['working_days_count'] = count($working_day_dates);

            $holiday_dates_for_H = array_values(array_unique(array_filter($data['holiday_dates'], function ($dateStr) use ($weekend_day_dates) {
                return !in_array($dateStr, $weekend_day_dates, true);
            })));
            $data['holiday_count'] = count($holiday_dates_for_H);

            $data['month_selected'] = $month;
            $data['year_selected']  = $searchyear;
            $data["role_selected"]  = $role;
            
            $last_day_of_month = $searchyear . "-" . $month_number . "-" . $num_of_days;
            $stafflist = $this->staffattendancemodel->searchAttendanceReport($role, $last_day_of_month);

            $attendence_array   = array();
            $date_result        = array();
            for ($i = 1; $i <= $num_of_days; $i++) {
                $att_date           = $searchyear . "-" . $month_number . "-" . sprintf("%02d", $i);
                $attendence_array[] = $att_date;

                $res = $this->staffattendancemodel->searchAttendanceReport($role, $att_date);
                
                $s = array();
                foreach ($res as $result_k => $result_v) {
                    $s[$result_v['id']] = $result_v;
                }
                $date_result[$att_date] = $s;
            }

            $monthAttendance = array();
            if(!empty($stafflist)){
                foreach ($stafflist as $result_k => $result_v) {
                    $date              = $searchyear . "-" . $month;
                    $newdate           = date('Y-m-d', strtotime($date));
                    $monthAttendance[] = $this->monthAttendance($newdate, 1, $result_v['id']);
                }
            }

            $absent_working_day_counts = [];
            if (!empty($stafflist)) {
                foreach ($stafflist as $staff_row) {
                    $staff_id = $staff_row['id'];
                    $absent_count = 0;
                    foreach ($working_day_dates as $work_date) {
                        $att_key = $date_result[$work_date][$staff_id]['key'] ?? null;
                        if ($att_key === 'A') {
                            $absent_count++;
                        }
                    }
                    $absent_working_day_counts[$staff_id] = $absent_count;
                }
            }
            $data['absent_working_day_counts'] = $absent_working_day_counts;

            $total_late_counts = [];
            $total_permission_counts = [];
            if (!empty($stafflist)) {
                $this->load->model('staffAttendaceSetting_model');
                $start_date = $searchyear . '-' . $month_number . '-01';
                $end_date = date('Y-m-t', strtotime($start_date));

                $role_settings_map = [];
                foreach ($stafflist as $staff_row) {
                    $role_id = (int) ($staff_row['role_id'] ?? 0);
                    if (!$role_id || isset($role_settings_map[$role_id])) {
                        continue;
                    }
                    $role_settings_map[$role_id] = [
                        'FHL' => !empty($type_map['FHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['FHL']) : false,
                        'FHP' => !empty($type_map['FHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['FHP']) : false,
                        'SHL' => !empty($type_map['SHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['SHL']) : false,
                        'SHP' => !empty($type_map['SHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['SHP']) : false,
                    ];
                }

                foreach ($stafflist as $staff_row) {
                    $staff_id = $staff_row['id'];
                    $role_id = (int) ($staff_row['role_id'] ?? 0);
                    $settings = $role_settings_map[$role_id] ?? [];
                    $rows = $this->staffattendancemodel->getAttendanceRowsInRange($staff_id, $start_date, $end_date);

                    $late_total = 0;
                    $permission_total = 0;

                    foreach ($rows as $row) {
                        $in_time = $row['in_time'] ?? '';
                        $out_time = $row['out_time'] ?? '';

                        $late_for_day = 0;
                        if (!empty($in_time)) {
                            if (!empty($settings['SHL']) && $this->timeInRange($in_time, $settings['SHL']->entry_time_from, $settings['SHL']->entry_time_to)) {
                                $late_for_day = 1;
                            } elseif (!empty($settings['FHL']) && $this->timeInRange($in_time, $settings['FHL']->entry_time_from, $settings['FHL']->entry_time_to)) {
                                $late_for_day = 1;
                            }
                        }
                        $late_total += $late_for_day;

                        if (!empty($in_time) && !empty($settings['FHP']) && $this->timeInRange($in_time, $settings['FHP']->entry_time_from, $settings['FHP']->entry_time_to)) {
                            $permission_total += 1;
                        }
                        if (!empty($out_time) && !empty($settings['SHP']) && $this->timeInRange($out_time, $settings['SHP']->entry_time_from, $settings['SHP']->entry_time_to)) {
                            $permission_total += 1;
                        }
                    }

                    $total_late_counts[$staff_id] = $late_total;
                    $total_permission_counts[$staff_id] = $permission_total;
                }
            }
            $data['total_late_counts'] = $total_late_counts;
            $data['total_permission_counts'] = $total_permission_counts;


            $data['monthAttendance'] = $monthAttendance;
            $data['resultlist']      = $date_result;
            $data['no_of_days']      = $num_of_days;

            if (!empty($searchyear)) {
                $data['attendence_array'] = $attendence_array;
                $data['student_array']    = $stafflist;
            } else {
                $data['attendence_array'] = array();
                $data['student_array']    = array();
            }

            $this->load->view('layout/header', $data);
            $this->load->view('attendencereports/staffattendancereport', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function staffattendancereport_export_excel()
    {
        if (!$this->rbac->hasPrivilege('staff_attendance_report', 'can_view')) {
            access_denied();
        }

        require_once APPPATH . 'third_party/vendor/autoload.php';

        $role = $this->input->get('role');
        $month = $this->input->get('month');
        $searchyear = $this->input->get('year');

        if (empty($month) || empty($searchyear)) {
            show_error('Month and year are required', 400);
        }

        $month_number = is_numeric($month) ? sprintf('%02d', (int)$month) : date("m", strtotime($month));
        $num_of_days = cal_days_in_month(CAL_GREGORIAN, (int)$month_number, (int)$searchyear);

        $this->load->model("holiday_model");
        $this->load->model("setting_model");
        $holidays = $this->holiday_model->get();

        $official_holiday_dates = [];
        foreach ($holidays as $holiday_value) {
            $from_date = new DateTime($holiday_value['from_date']);
            $to_date = new DateTime($holiday_value['to_date']);
            $current = clone $from_date;
            while ($current <= $to_date) {
                if ($current->format('m') == $month_number && $current->format('Y') == $searchyear) {
                    $official_holiday_dates[] = $current->format('Y-m-d');
                }
                $current->modify('+1 day');
            }
        }
        $holiday_dates = array_values(array_unique($official_holiday_dates));

        $settings = $this->setting_model->getSetting();
        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;

        $second_saturday_date = null;
        if ($isSecondSaturdayWeekend) {
            $saturdayCount = 0;
            for ($i = 1; $i <= $num_of_days; $i++) {
                $date = new DateTime($searchyear . "-" . $month_number . "-" . sprintf("%02d", $i));
                if ((int)$date->format('w') == 6) {
                    $saturdayCount++;
                    if ($saturdayCount == 2) {
                        $second_saturday_date = $date->format('Y-m-d');
                        break;
                    }
                }
            }
        }

        $weekend_day_dates = [];
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dateStr = $searchyear . "-" . $month_number . "-" . sprintf("%02d", $i);
            $dayOfWeek = (int)date('w', strtotime($dateStr));
            if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date && $dateStr === $second_saturday_date)) {
                $weekend_day_dates[] = $dateStr;
            }
        }
        $weekend_day_dates = array_values(array_unique($weekend_day_dates));

        $working_day_dates = [];
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dateStr = $searchyear . "-" . $month_number . "-" . sprintf("%02d", $i);
            if (!in_array($dateStr, $weekend_day_dates, true) && !in_array($dateStr, $holiday_dates, true)) {
                $working_day_dates[] = $dateStr;
            }
        }

        $holiday_dates_for_H = array_values(array_unique(array_filter($holiday_dates, function ($dateStr) use ($weekend_day_dates) {
            return !in_array($dateStr, $weekend_day_dates, true);
        })));
        $holiday_count = count($holiday_dates_for_H);

        $last_day_of_month = $searchyear . "-" . $month_number . "-" . $num_of_days;
        $stafflist = $this->staffattendancemodel->searchAttendanceReport($role, $last_day_of_month);

        $attendence_array = [];
        $date_result = [];
        for ($i = 1; $i <= $num_of_days; $i++) {
            $att_date = $searchyear . "-" . $month_number . "-" . sprintf("%02d", $i);
            $attendence_array[] = $att_date;

            $res = $this->staffattendancemodel->searchAttendanceReport($role, $att_date);
            $s = [];
            foreach ($res as $result_v) {
                $s[$result_v['id']] = $result_v;
            }
            $date_result[$att_date] = $s;
        }

        $monthAttendance = [];
        if (!empty($stafflist)) {
            foreach ($stafflist as $result_v) {
                $date = $searchyear . "-" . (is_numeric($month) ? sprintf('%02d', (int)$month) : $month);
                $newdate = date('Y-m-d', strtotime($date));
                $monthAttendance[] = $this->monthAttendance($newdate, 1, $result_v['id']);
            }
        }

        $absent_working_day_counts = [];
        if (!empty($stafflist)) {
            foreach ($stafflist as $staff_row) {
                $staff_id = $staff_row['id'];
                $absent_count = 0;
                foreach ($working_day_dates as $work_date) {
                    $att_key = $date_result[$work_date][$staff_id]['key'] ?? null;
                    if ($att_key === 'A') {
                        $absent_count++;
                    }
                }
                $absent_working_day_counts[$staff_id] = $absent_count;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Staff Attendance');

        $headers = ['Staff / Date', '(%)', 'WD', 'A*', 'P*', 'H', 'HD', 'WE'];
        foreach ($attendence_array as $att_date) {
            $headers[] = date('d', strtotime($att_date)) . "\n" . date('D', strtotime($att_date));
        }

        $colIndex = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex, 1, $header);
            $colIndex++;
        }

        $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        $presentFill = ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFD4EDDA']];
        $halfDayFill = ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFF3CD']];
        $absentFill = ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF8D7DA']];
        $weekendFill = ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFF8D7DA']];
        $holidayFill = ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFFFF3CD']];

        $rowIndex = 2;
        $i = 0;
        foreach ($stafflist as $staff_row) {
            $staff_id = $staff_row['id'];
            $present_count = $monthAttendance[$i][$staff_id]['present'] ?? 0;
            $half_day_count = $monthAttendance[$i][$staff_id]['half_day'] ?? 0;
            $absent_count = $absent_working_day_counts[$staff_id] ?? 0;
            $working_days = count($working_day_dates);
            $total_present = $present_count + ($half_day_count * 0.5);
            $total_absent = $absent_count + ($half_day_count * 0.5);

            if ($working_days == 0) {
                $print_percentage = '-';
            } else {
                $percentage = ($total_present / $working_days) * 100;
                $print_percentage = round($percentage, 0);
            }

            $row = [
                $staff_row['name'] . ' ' . $staff_row['surname'],
                $print_percentage,
                $working_days,
                rtrim(rtrim(number_format((float)$total_absent, 1, '.', ''), '0'), '.'),
                rtrim(rtrim(number_format((float)$total_present, 1, '.', ''), '0'), '.'),
                $holiday_count,
                $half_day_count,
                count($weekend_day_dates),
            ];

            $colIndex = 1;
            foreach ($row as $value) {
                $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $value);
                $colIndex++;
            }

            foreach ($attendence_array as $att_date) {
                $attendance_row = $date_result[$att_date][$staff_id] ?? [];
                $attendance_key = $attendance_row['key'] ?? null;
                $display_key = $attendance_key ?? '';

                $present_keys = ['P', 'FHL', 'SHL', 'FHP', 'SHP'];
                $absent_keys = ['A', 'FHA', 'SHA'];
                $normalized_key = $attendance_key;
                if (in_array($attendance_key, $present_keys, true)) {
                    $normalized_key = 'P';
                } elseif (in_array($attendance_key, $absent_keys, true)) {
                    $normalized_key = 'A';
                }

                if (!empty($weekend_day_dates) && in_array($att_date, $weekend_day_dates, true)) {
                    $display_key = 'W';
                } elseif (in_array($att_date, $holiday_dates, true) || $attendance_key == 'HO') {
                    $display_key = 'H';
                } elseif ($attendance_key === 'HD') {
                    $display_key = 'HD';
                } elseif ($normalized_key === 'P') {
                    $display_key = 'P';
                } elseif ($normalized_key === 'A') {
                    $display_key = 'A';
                }

                $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $display_key);

                $cell = $sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getCoordinate();
                if ($display_key === 'P') {
                    $sheet->getStyle($cell)->getFill()->applyFromArray($presentFill);
                } elseif ($display_key === 'HD') {
                    $sheet->getStyle($cell)->getFill()->applyFromArray($halfDayFill);
                } elseif ($display_key === 'A') {
                    $sheet->getStyle($cell)->getFill()->applyFromArray($absentFill);
                } elseif ($display_key === 'W') {
                    $sheet->getStyle($cell)->getFill()->applyFromArray($weekendFill);
                } elseif ($display_key === 'H') {
                    $sheet->getStyle($cell)->getFill()->applyFromArray($holidayFill);
                }

                $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }

            $rowIndex++;
            $i++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Staff_Attendance_Report_' . $month_number . '_' . $searchyear . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function staffAttendanceDetail()
    {
        if (!$this->rbac->hasPrivilege('staff_attendance_report', 'can_view')) {
            access_denied();
        }

        $staff_id = (int) $this->input->post('staff_id');
        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $type = strtoupper(trim($this->input->post('type')));

        if (empty($staff_id) || empty($month) || empty($year) || empty($type)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid request.']);
            return;
        }

        $month_number = is_numeric($month) ? sprintf('%02d', (int) $month) : date("m", strtotime($month));
        $start_date = $year . '-' . $month_number . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        $attendencetypes = $this->staffattendancemodel->getStaffAttendanceType();
        $type_map = [];
        foreach ($attendencetypes as $type_row) {
            $key_value = strtoupper(trim($type_row['key_value'] ?? ''));
            if (!empty($key_value)) {
                $type_map[$key_value] = (int) $type_row['id'];
            }
        }

        $rows = $this->staffattendancemodel->getAttendanceRowsInRange($staff_id, $start_date, $end_date);
        $details = [];

        if ($type === 'HD') {
            $hd_id = $type_map['HD'] ?? null;
            foreach ($rows as $row) {
                if (!empty($hd_id) && (int) $row['staff_attendance_type_id'] === (int) $hd_id) {
                    $details[] = [
                        'date' => $this->customlib->dateformat($row['date']),
                        'in_time' => !empty($row['in_time']) ? $row['in_time'] : '-',
                        'out_time' => !empty($row['out_time']) ? $row['out_time'] : '-',
                        'session' => 'Full Day',
                    ];
                }
            }
        } else {
            $this->load->model('staffAttendaceSetting_model');
            $staff = $this->staff_model->get($staff_id);
            $role_id = (int) ($staff['role_id'] ?? 0);

            $settings = [
                'FHL' => !empty($type_map['FHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['FHL']) : false,
                'FHP' => !empty($type_map['FHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['FHP']) : false,
                'SHL' => !empty($type_map['SHL']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['SHL']) : false,
                'SHP' => !empty($type_map['SHP']) ? $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $type_map['SHP']) : false,
            ];

            foreach ($rows as $row) {
                $in_time = $row['in_time'] ?? '';
                $out_time = $row['out_time'] ?? '';
                $matches = [];

                if ($type === 'TL') {
                    if (!empty($in_time)) {
                        if (!empty($settings['SHL']) && $this->timeInRange($in_time, $settings['SHL']->entry_time_from, $settings['SHL']->entry_time_to)) {
                            $matches[] = 'Afternoon';
                        } elseif (!empty($settings['FHL']) && $this->timeInRange($in_time, $settings['FHL']->entry_time_from, $settings['FHL']->entry_time_to)) {
                            $matches[] = 'Morning';
                        }
                    }
                } elseif ($type === 'TP') {
                    if (!empty($in_time) && !empty($settings['FHP']) && $this->timeInRange($in_time, $settings['FHP']->entry_time_from, $settings['FHP']->entry_time_to)) {
                        $matches[] = 'Morning';
                    }
                    if (!empty($out_time) && !empty($settings['SHP']) && $this->timeInRange($out_time, $settings['SHP']->entry_time_from, $settings['SHP']->entry_time_to)) {
                        $matches[] = 'Afternoon';
                    }
                }

                foreach ($matches as $session_label) {
                    $details[] = [
                        'date' => $this->customlib->dateformat($row['date']),
                        'in_time' => !empty($in_time) ? $in_time : '-',
                        'out_time' => !empty($out_time) ? $out_time : '-',
                        'session' => $session_label,
                    ];
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'type' => $type,
            'rows' => $details,
        ]);
    }

    public function monthAttendance($st_month, $no_of_months, $emp)
    {
        $this->load->model("holiday_model");
        $this->load->model("setting_model");
        $holidays = $this->holiday_model->get();
        $this->load->model("staffattendancemodel");
        $this->staff_attendance  = $this->config->item('staffattendance');

        $record = array();
        for ($i = 0; $i < $no_of_months; $i++) {

            $r     = array();
            $month = date('m', strtotime($st_month . " -$i month"));
            $year  = date('Y', strtotime($st_month . " -$i month"));
            
            $weekend_days_in_month = [];
            $official_holiday_dates = [];
            $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $settings = $this->setting_model->getSetting();
            $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
            $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
            $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;

            $second_saturday_date = null;
            if ($isSecondSaturdayWeekend) {
                $saturdayCount = 0;
                for ($day = 1; $day <= $num_of_days; $day++) {
                    $date = new DateTime($year . "-" . $month . "-" . sprintf("%02d", $day));
                    if ((int)$date->format('w') == 6) {
                        $saturdayCount++;
                        if ($saturdayCount == 2) {
                            $second_saturday_date = $date->format('Y-m-d');
                            break;
                        }
                    }
                }
            }

            // Calculate all configured weekend days in the month
            for ($day = 1; $day <= $num_of_days; $day++) {
                $att_date = $year . "-" . $month . "-" . sprintf("%02d", $day);
                $dayOfWeek = (int)date('w', strtotime($att_date));
                if (in_array($dayOfWeek, $weekendDays, true) || ($second_saturday_date && $att_date === $second_saturday_date)) {
                    $weekend_days_in_month[] = $att_date;
                }
            }
            $weekend_days_in_month = array_values(array_unique($weekend_days_in_month));

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

            $holidays_for_H_column = array_diff($official_holiday_dates, $weekend_days_in_month);

            $attendance_types_from_db = $this->staffattendancemodel->getStaffAttendanceType();
            $att_key_to_id_map = [];
            foreach ($attendance_types_from_db as $type_row) {
                $config_key = str_replace(" ", "_", strtolower($type_row['type']));
                $att_key_to_id_map[$config_key] = $type_row['id'];
            }

            foreach ($this->staff_attendance as $att_key => $att_value_from_config) {
                $attendance_type_id_for_query = $att_key_to_id_map[$att_key] ?? null;

                if ($att_key == 'holiday') {
                    $r[$att_key] = count($holidays_for_H_column);
                    $r['sunday'] = count($weekend_days_in_month);
                    continue;
                }

                if ($attendance_type_id_for_query !== null) {
                    $s = $this->payroll_model->count_attendance_obj($month, $year, $emp, $attendance_type_id_for_query);
                    $r[$att_key] = $s;
                } else {
                    $r[$att_key] = 0;
                }
            }


            $record[$emp] = $r;
        }
        return $record;
    }

    public function biometric_attlog($offset = 0)
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendence/biometric_attlog');
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        $config['total_rows'] = $this->stuattendence_model->biometric_attlogcount();

        $config['base_url']    = base_url() . "report/biometric_attlog";
        $config['per_page']    = 100;
        $config['uri_segment'] = '3';

        $config['full_tag_open']  = '<div class="pagination"><ul>';
        $config['full_tag_close'] = '</ul></div>';

        $config['first_link']      = '« First';
        $config['first_tag_open']  = '<li class="prev page">';
        $config['first_tag_close'] = '</li>';

        $config['last_link']      = 'Last »';
        $config['last_tag_open']  = '<li class="next page">';
        $config['last_tag_close'] = '</li>';

        $config['next_link']      = 'Next →';
        $config['next_tag_open']  = '<li class="next page">';
        $config['next_tag_close'] = '</li>';

        $config['prev_link']      = '← Previous';
        $config['prev_tag_open']  = '<li class="prev page">';
        $config['prev_tag_close'] = '</li>';

        $config['cur_tag_open']  = '<li ><a href="" class="active">';
        $config['cur_tag_close'] = '</a></li>';

        $config['num_tag_open']  = '<li class="page">';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);
        $query = $this->stuattendence_model->biometric_attlog(100, $this->uri->segment(3));

        $data['resultlist'] = $query;
        $this->load->view('layout/header', $data);
        $this->load->view('attendencereports/biometric_attlog', $data);
        $this->load->view('layout/footer', $data);
    }

    public function reportbymonthstudent()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendence/reportbymonthstudent');

        $data                = array();
        $class               = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist']   = $class;
        $sch_setting         = $this->setting_model->getSetting();
        $data['sch_setting'] = $sch_setting;
        $data['monthlist']   = $this->customlib->getMonthNoDropdown($sch_setting->start_month);
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list

        $data['student_id'] = "";
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('student_id', $this->lang->line('student'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {
            $attendencetypes             = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;
            $student_id                  = $data['student_id']                  = $this->input->post('student_id');
            $class_id                    = $this->input->post('class_id');
            $section_id                  = $this->input->post('section_id');
            $month                       = $this->input->post('month');
            $subject_id                  = $this->input->post('subject_id');
            $department_id = $this->input->post('department_id'); // Retrieve department_id
            $month_data                  = sessionMonthDetails($sch_setting->session, $sch_setting->start_month, $month);

            $attr_result        = array();
            $attendence_array   = array();
            $student_result     = array();
            $data['no_of_days'] = $month_data['total_days'];
            $date_result        = array();
            $from_date          = 1;

            $resultlist = $this->studentsubjectattendence_model->getStudentMontlyAttendence($class_id, $section_id, $month_data['month_start'], $month_data['month_end'], $student_id, $subject_id, $department_id); // Pass department_id

            $data['resultlist'] = $resultlist;
        }
        $this->load->view('layout/header', $data);
        $this->load->view('attendencereports/reportbymonthstudent', $data);
        $this->load->view('layout/footer', $data);
    }

    public function reportbymonth()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendence');
        $this->session->set_userdata('subsub_menu', 'Reports/attendence/reportbymonth');

        $data              = array();
        $class             = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist'] = $class;

        $sch_setting         = $this->setting_model->getSetting();
        $data['sch_setting'] = $sch_setting;
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list

        $data['monthlist'] = $this->customlib->getMonthNoDropdown($sch_setting->start_month);

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {
            $attendencetypes             = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;
            $subject_id                  = $this->input->post('subject_id');
            $class_id                    = $this->input->post('class_id');
            $section_id                  = $this->input->post('section_id');
            $month                       = $this->input->post('month');
            $year                        = $this->input->post('year');
            $department_id = $this->input->post('department_id'); // Retrieve department_id
            $month_data                  = sessionMonthDetails($sch_setting->session, $sch_setting->start_month, $month);

            $attr_result        = array();
            $attendence_array   = array();
            $student_result     = array();
            $data['no_of_days'] = $month_data['total_days'];
            $date_result        = array();

            $resultlist = $this->studentsubjectattendence_model->getStudentsMontlyAttendence($class_id, $section_id, $month_data['month_start'], $month_data['month_end'], $subject_id, $department_id); // Pass department_id

            $data['resultlist'] = $resultlist;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('attendencereports/reportbymonth', $data);
        $this->load->view('layout/footer', $data);
    }

    private function timeInRange($time, $from, $to)
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
}
