<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Studentprofilecompleteness extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('studentprofilecompleteness_model');
        $this->load->model('class_model');
        $this->load->model('section_model');
    }

    public function index() {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'admin/studentprofilecompleteness');
        $data['title'] = 'Student Profile Completeness Report';

        $data['classlist'] = $this->class_model->get();
        $data['class_id'] = $class_id = $this->input->post('class_id');
        $data['section_id'] = $section_id = $this->input->post('section_id');

        if (isset($class_id) && $class_id != '') {
            $data['students'] = $this->studentprofilecompleteness_model->getStudentsByClassAndSection($class_id, $section_id);
        } else {
            $data['students'] = $this->studentprofilecompleteness_model->getStudents();
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/studentprofilecompleteness/index', $data);
        $this->load->view('layout/footer', $data);
    }
}
?>