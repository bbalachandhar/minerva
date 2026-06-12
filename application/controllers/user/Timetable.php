<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Timetable extends Student_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Tt_period_model');
        $this->load->model('Tt_entry_model');
        $this->load->library('media_storage');
    }

    public function index() {
        $this->session->set_userdata('top_menu', 'Time_table');
        $cls = $this->customlib->getStudentCurrentClsSection();
        $session_id = $cls->session_id;
        $class_id   = $cls->class_id;
        $section_id = $cls->section_id;

        $periods = $this->Tt_period_model->getAll($session_id);
        $entries = $this->Tt_entry_model->getStudentEntries($session_id, $class_id, $section_id);
        $days    = $this->_getWorkingDays();

        $entry_map = [];
        foreach ($entries as $e) {
            $entry_map[$e->day][$e->period_id] = $e;
        }

        $cls_row = $this->db->select('class')->where('id', $class_id)->get('classes')->row();
        $sec_row = $this->db->select('section')->where('id', $section_id)->get('sections')->row();

        $data['periods']       = $periods;
        $data['entry_map']     = $entry_map;
        $data['days']          = $days;
        $data['class_label']   = $cls_row ? $cls_row->class : '';
        $data['section_label'] = $sec_row ? $sec_row->section : '';
        $data['class_id']      = $class_id;
        $data['section_id']    = $section_id;

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/timetable/timetableList', $data);
        $this->load->view('layout/student/footer', $data);
    }

    public function printclasstimetable() {
        $cls        = $this->customlib->getStudentCurrentClsSection();
        $session_id = $cls->session_id;
        $class_id   = $cls->class_id;
        $section_id = $cls->section_id;

        $periods = $this->Tt_period_model->getAll($session_id);
        $entries = $this->Tt_entry_model->getStudentEntries($session_id, $class_id, $section_id);
        $days    = $this->_getWorkingDays();

        $entry_map = [];
        foreach ($entries as $e) {
            $entry_map[$e->day][$e->period_id] = $e;
        }

        $cls_row = $this->db->select('class')->where('id', $class_id)->get('classes')->row();
        $sec_row = $this->db->select('section')->where('id', $section_id)->get('sections')->row();
        $header_img     = $this->setting_model->get_general_purpose_header();
        $header_img_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;

        $data = [
            'periods'       => $periods,
            'entry_map'     => $entry_map,
            'days'          => $days,
            'class_label'   => $cls_row ? $cls_row->class : '',
            'section_label' => $sec_row ? $sec_row->section : '',
            'header_img_url'=> $header_img_url,
            'for_print'     => true,
        ];

        $page = $this->load->view('admin/tt/print_class_grid', $data, true);
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['status' => '1', 'error' => '', 'page' => $page]));
    }

    private function _getWorkingDays() {
        $days        = $this->customlib->getDaysnameWithoutLang();
        $settings    = $this->setting_model->getSetting();
        $weekend_str = isset($settings->weekend_days) ? (string) $settings->weekend_days : '';
        if ($weekend_str !== '') {
            $dow_map = [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
            foreach (array_map('intval', explode(',', $weekend_str)) as $dow) {
                if (isset($dow_map[$dow])) unset($days[$dow_map[$dow]]);
            }
        }
        return $days;
    }
}
