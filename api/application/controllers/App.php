<?php

defined('BASEPATH') or exit('No direct script access allowed');

class App extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('setting_model');
    }

    public function index()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method !== 'POST' && $method !== 'GET') {
            json_output(405, array('status' => 0, 'message' => 'Method Not Allowed'));
            return;
        }

        $setting = $this->setting_model->getSetting();

        $configured_mobile_api_url = isset($setting->mobile_api_url) ? trim((string) $setting->mobile_api_url) : '';
        $site_root = rtrim($configured_mobile_api_url, '/');
        // Strip trailing /api if present
        if (substr($site_root, -4) === '/api') {
            $site_root = substr($site_root, 0, -4);
        }
        $site_root = rtrim($site_root, '/');

        // Fallback: derive site root from base_url config
        if (empty($site_root)) {
            $site_root = rtrim(base_url(), '/');
        }

        $app_logo = '';
        if (isset($setting->app_logo) && $setting->app_logo) {
            $app_logo = $site_root . '/uploads/school_content/logo/app_logo/' . $setting->app_logo;
        }

        $currency_symbol = isset($setting->currency_symbol) ? (string) $setting->currency_symbol : '';
        if (empty($currency_symbol) && isset($setting->currency)) {
            $cur = $this->db->where('id', $setting->currency)->get('currencies')->row();
            if ($cur) $currency_symbol = $cur->symbol;
        }

        json_output(200, array(
            'status'                   => 1,
            'url'                      => isset($setting->mobile_api_url) ? (string) $setting->mobile_api_url : '',
            'site_url'                 => $site_root,
            'attendence_type'          => isset($setting->attendence_type) ? (int) $setting->attendence_type : 0,
            'app_logo'                 => $app_logo,
            'app_primary_color_code'   => isset($setting->app_primary_color_code) ? (string) $setting->app_primary_color_code : '',
            'app_secondary_color_code' => isset($setting->app_secondary_color_code) ? (string) $setting->app_secondary_color_code : '',
            'lang_code'                => isset($setting->language_code) ? (string) $setting->language_code : '',
            'school_name'              => isset($setting->name) ? (string) $setting->name : '',
            'school_code'              => isset($setting->dise_code) ? (string) $setting->dise_code : '',
            'currency_symbol'          => $currency_symbol,
            'institution_type'         => isset($setting->institution_type) ? (string) $setting->institution_type : 'school',
            'app_ver'                  => (string) $this->config->item('app_ver'),
            'student_profile_edit'     => isset($setting->student_profile_edit) ? (int) $setting->student_profile_edit : 0,
            'staff_profile_edit'       => isset($setting->staff_profile_edit) ? (int) $setting->staff_profile_edit : 0,
        ));
    }

    public function index1()
    {        
        $student_id = 2;
        $student    = $this->student_model->get($student_id);
        $examList   = $this->examschedule_model->getExamByClassandSection($student['class_id'], $student['section_id']);
        $response   = array();
        if (!empty($examList)) {
            $new_array = array();
            foreach ($examList as $ex_key => $ex_value) {
                $array   = array();
                $x       = array();
                $exam_id = $ex_value['exam_id'];
                $student['id'];
                $exam_subjects = $this->examschedule_model->getresultByStudentandExam($exam_id, $student['id']);
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
