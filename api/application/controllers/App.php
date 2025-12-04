<?php

defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{
    // Declare properties to avoid dynamic property deprecation warnings
    public $email;
    public $setting_model; // Corrected capitalization from Setting_model
    public $customlib;
    public $student_model;
    public $examgroup_model;
    public $event_model;
    public $load; // Declare $this->load property
    public $grade_model; // Also declare grade_model as it's used in getGradeByMarks

    public function __construct()
    {
        parent::__construct();

        $this->load->model('setting_model'); // Add this line
        $this->load->model('student_model');
        $this->load->model('examgroup_model');
        $this->load->model('event_model');
        $this->load->model('grade_model'); // Add this line
    }

    public function index()
    {
        // Fix for ArgumentCountError: getPublicEvents expects 3 arguments
        // Using the first and last day of the current month as default dates
        $current_month_start = date('Y-m-01');
        $current_month_end = date('Y-m-t');
        // 'no' for shd_notification as a default, adjust if a notification is desired
        $resp['public_events'] = $this->event_model->getPublicEvents($current_month_start, $current_month_end, 'no'); 
        $resp['url'] = base_url();
        $resp['site_url'] = base_url();
        // Fetch settings from setting_model
        $app_settings = $this->setting_model->get(); 

        // Populate app_ver and app_logo from app_settings
        $resp['app_ver'] = isset($app_settings[0]->app_ver) ? $app_settings[0]->app_ver : "1.0"; 
        $resp['app_logo'] = isset($app_settings[0]->app_logo) ? base_url('uploads/school_content/admin_logo/' . $app_settings[0]->app_logo) : ""; 
        $resp['app_secondary_color_code'] = isset($app_settings[0]->app_secondary_color_code) ? $app_settings[0]->app_secondary_color_code : "";
        $resp['app_primary_color_code'] = isset($app_settings[0]->app_primary_color_code) ? $app_settings[0]->app_primary_color_code : "";
        $resp['lang_code'] = isset($app_settings[0]->language_code) ? $app_settings[0]->language_code : ""; // ADD THIS LINE

        $date_list             = array();
        foreach ($resp['public_events'] as &$ev_tsk_value) {
            $evt_array = array();
            if ($ev_tsk_value->event_type == "public") {
                $start = strtotime($ev_tsk_value->start_date);
                $end   = strtotime($ev_tsk_value->end_date);

                for ($st = $start; $st <= $end; $st += 86400) {
                    $evt_array[] = date('Y-m-d', $st);
                }
                $date_list[]                = $evt_array;
                $ev_tsk_value->events_lists = implode(",", $evt_array);
            } elseif ($ev_tsk_value->event_type == "task") {

                $evt_array[]                = date('Y-m-d', strtotime($ev_tsk_value->start_date));
                $ev_tsk_value->events_lists = implode(",", $evt_array);
                $date_list[]                = $evt_array;
            }
        }

        echo json_encode($resp);
    }

    public function index1()
    {        
        $student_id = 2;
        $student    = $this->student_model->get($student_id);
        $examList   = $this->examgroup_model->getExamByClassandSection($student['class_id'], $student['section_id']);
        $response   = array();
        if (!empty($examList)) {
            $new_array = array();
            foreach ($examList as $ex_key => $ex_value) {
                $array   = array();
                $x       = array();
                $exam_id = $ex_value['exam_id'];
                $student['id'];
                $exam_subjects = $this->examgroup_model->getresultByStudentandExam($exam_id, $student['id']);
                $total_marks   = 0;
                $get_marks     = 0;
                $result        = "Pass";

                foreach ($exam_subjects as $key => $value) {

                    $total_marks = $total_marks + $value['full_marks'];
                    $get_marks   = $get_marks + $value['get_marks'];

                    if (($value['get_marks'] < $value['passing_marks']) || ($value['attendence'] != 'pre')) {
                        $result = 'Fail';
                    }
                }

                $exam_result              = new stdClass();
                $exam_result->total_marks = $total_marks;
                $exam_result->get_marks   = $get_marks;
                $exam_result->percentage  = number_format((($get_marks * 100) / $total_marks), 2) . '%';
                $exam_result->grade       = $this->getGradeByMarks($get_marks);
                $exam_result->result      = $result;
                $array['exam_name']       = $ex_value['name'];
                $array['exam_result']     = $exam_result;
                $new_array[]              = $array;
            }
            $response = $new_array;
        }
    }

    public function getGradeByMarks($marks = 0)
    {
        $gradeList = $this->grade_model->get();

        if (empty($gradeList)) {
            return "empty list";
        } else {
            foreach ($gradeList as $grade_key => $grade_value) {
                if ($marks >= $grade_value['mark_from'] && $marks <= $grade_value['mark_upto']) {
                    return $grade_value['name'];
                }
            }
            return "no record found";
        }
    }

}
