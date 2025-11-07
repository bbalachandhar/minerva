<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Collect_incidental_fee extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('incidental_fee_type_model');
        $this->load->model('incidental_fee_assignment_model');
        $this->load->model('incidental_fee_collection_model');
        $this->load->model('session_model');
        $this->load->model('class_model');
        $this->load->model('student_model');
        $this->load->model('setting_model');
        $this->load->library('form_validation');
        $this->load->library('media_storage');

    }

    public function index() {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/collect_incidental_fee');

        $data['title'] = 'Collect Incidental Fee';
        $data['fee_types'] = $this->incidental_fee_type_model->get();
        $data['sessions'] = $this->session_model->get();
                        $data['classes'] = $this->class_model->get();
                        $data['student_detail'] = array();
                        $data['outstanding_assignments'] = array();
                        $data['sections'] = array();        $this->form_validation->set_rules('student_id', $this->lang->line('student'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('fee_type_id', $this->lang->line('fee_type'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount_collected', $this->lang->line('amount_collected'), 'required|numeric|trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/incidental_fee_collection/collect_incidental_fee', $data);
            $this->load->view('layout/footer');
        } else {
            $student_id = $this->input->post('student_id');
            $session_id = $this->input->post('session_id');
            $fee_type_id = $this->input->post('fee_type_id');
            $amount_collected = $this->input->post('amount_collected');
            $incidental_fee_assignment_id = $this->input->post('incidental_fee_assignment_id'); // Can be NULL for ad-hoc
            $collected_by = $this->customlib->getStaffID();
            $receipt_no = $this->incidental_fee_collection_model->get_receipt_no();

            $insert_data = array(
                'incidental_fee_type_id' => $fee_type_id,
                'incidental_fee_assignment_id' => $incidental_fee_assignment_id ? $incidental_fee_assignment_id : NULL,
                'session_id' => $session_id,
                'student_id' => $student_id,
                'amount_collected' => $amount_collected,
                'collected_by' => $collected_by,
                'receipt_no' => $receipt_no,
                'notes' => $this->input->post('notes'),
            );

            $collection_id = $this->incidental_fee_collection_model->add($insert_data);

            if ($collection_id) {
                if ($incidental_fee_assignment_id) {
                    $assignment = $this->incidental_fee_assignment_model->get($incidental_fee_assignment_id);
                    if ($assignment) {
                        if ($amount_collected >= $assignment['amount_due']) {
                            $this->incidental_fee_assignment_model->update($incidental_fee_assignment_id, array('status' => 'paid'));
                        } else {
                            $this->incidental_fee_assignment_model->update($incidental_fee_assignment_id, array('status' => 'partially_paid'));
                        }
                    }
                }
                echo json_encode(array('status' => 'success', 'message' => $this->lang->line('fee_collected_successfully'), 'collection_id' => $collection_id));
            } else {
                echo json_encode(array('status' => 'error', 'message' => $this->lang->line('error_collecting_fee')));
            }
        }
    }

    public function searchStudent() {
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            redirect('admin/collect_incidental_fee');
        }
        else {
            $this->session->set_userdata('top_menu', 'Fees Collection');
            $this->session->set_userdata('sub_menu', 'admin/collect_incidental_fee');

            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $search_text = $this->input->post('search_text');
            $session_id = $this->setting_model->getCurrentSession();

            if (empty($class_id) && empty($section_id) && empty($search_text)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please provide at least one search parameter.</div>');
                redirect('admin/collect_incidental_fee');
            }

            $data['student_list'] = $this->student_model->searchStudentsByClassSectionAndText($class_id, $section_id, $session_id, $search_text);
            $data['fee_types'] = $this->incidental_fee_type_model->get();
            $data['sessions'] = $this->session_model->get();
            $data['classes'] = $this->class_model->get();
            $data['sections'] = $this->class_model->get_section($class_id);

            $data['class_id'] = $class_id;
            $data['section_id'] = $section_id;
            $data['session_id'] = $session_id;
            $data['search_text'] = $search_text;

            $this->load->view('layout/header', $data);
            $this->load->view('admin/incidental_fee_collection/collect_incidental_fee', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function getStudentDetails() {
        $student_id = $this->input->post('student_id');
        $session_id = $this->input->post('session_id');

        $student_detail = $this->student_model->get($student_id);
        $outstanding_assignments = $this->incidental_fee_assignment_model->get_by_student_session($student_id, $session_id);

        echo json_encode(array('student_detail' => $student_detail, 'outstanding_assignments' => $outstanding_assignments));
    }

    public function receipt($collection_id) {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            access_denied();
        }
        $data['collection'] = $this->incidental_fee_collection_model->get_collection_by_id($collection_id);
        $data['sch_setting'] = $this->setting_model->getSetting();
        $data['receipt_header'] = $this->setting_model->get_receiptheader();

        $this->load->view('financereports/incidental_fee_print', $data);
    }

    public function getSectionsByClass() {
        $class_id = $this->input->post('class_id');
        log_message('error', 'getSectionsByClass: Received class_id = ' . $class_id);
        $sections = $this->class_model->get_section($class_id);
        log_message('error', 'getSectionsByClass: Sections returned = ' . json_encode($sections));
        echo json_encode($sections);
    }

    public function revert($collection_id) {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_delete')) {
            access_denied();
        }

        if (!$collection_id) {
            redirect('financereports/incidental_fee_report');
        }

        $success = $this->incidental_fee_collection_model->revert($collection_id);

        if ($success) {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fee collection reverted successfully.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error reverting fee collection.</div>');
        }

        redirect('financereports/incidental_fee_report');
    }

}