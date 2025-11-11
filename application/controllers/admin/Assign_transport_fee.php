<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Assign_transport_fee extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model(array('class_model', 'section_model', 'student_model', 'route_model', 'Pickuppoint_model', 'vehroute_model', 'studenttransportfee_model', 'transportfee_model'));
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'admin/assign_transport_fee');

        $data['title'] = 'Assign Transport Fee';
        $data['classlist'] = $this->class_model->get();
        $data['route_list'] = $this->route_model->get();
        $data['pickup_point_list'] = $this->Pickuppoint_model->get();

        $data['route_filter_id'] = ''; // Initialize for initial load
        $data['pickup_point_filter_id'] = ''; // Initialize for initial load

        $this->load->view('layout/header', $data);
        $this->load->view('admin/assign_transport_fee/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function search()
    {
        if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_view')) {
            access_denied();
        }

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $route_filter_id = $this->input->post('route_filter_id');
        $pickup_point_filter_id = $this->input->post('pickup_point_filter_id');
        $search_text = $this->input->post('search_text');

        $data['class_id'] = $class_id;
        $data['section_id'] = $section_id;
        $data['route_filter_id'] = $route_filter_id;
        $data['pickup_point_filter_id'] = $pickup_point_filter_id;
        $data['search_text'] = $search_text;

        $data['classlist'] = $this->class_model->get();
        $data['route_list'] = $this->route_model->get();
        $data['pickup_point_list'] = $this->Pickuppoint_model->get();

        $students = $this->student_model->searchByClassSectionAndText($class_id, $section_id, $search_text, $route_filter_id, $pickup_point_filter_id);
        $data['studentlist'] = $students;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/assign_transport_fee/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function assign()
    {
        if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('route_id', 'Route', 'trim|required|xss_clean');
        $this->form_validation->set_rules('pickup_point_id', 'Pickup Point', 'trim|required|xss_clean');
        $this->form_validation->set_rules('student_session_id[]', 'Students', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'route_id' => form_error('route_id'),
                'pickup_point_id' => form_error('pickup_point_id'),
                'student_session_id' => form_error('student_session_id[]'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $route_id = $this->input->post('route_id');
            $pickup_point_id = $this->input->post('pickup_point_id');
            $student_session_ids = $this->input->post('student_session_id');

            $vehroute = $this->vehroute_model->getVehrouteByRouteAndPickupPoint($route_id, $pickup_point_id);

            if (empty($vehroute)) {
                $array = array('status' => 'fail', 'error' => array('pickup_point_id' => 'No vehicle route found for the selected route and pickup point.'), 'message' => '');
                echo json_encode($array);
                exit();
            }

            $vehroute_id = $vehroute['id'];

            $transport_feemasters = $this->transportfee_model->getTransportFeeByVehrouteId($vehroute_id);

            if (empty($transport_feemasters)) {
                $array = array('status' => 'fail', 'error' => array('pickup_point_id' => 'No transport fee master found for the selected vehicle route.'), 'message' => '');
                echo json_encode($array);
                exit();
            }

            $this->db->trans_start();

            foreach ($student_session_ids as $student_session_id) {
                // Update student_session table
                $this->student_model->updateStudentSessionTransport($student_session_id, $vehroute_id, $pickup_point_id);

                // Add/Update student_transport_fees for each transport fee master
                $this->studenttransportfee_model->removeStudentTransportFee($student_session_id); // Remove existing first
                foreach ($transport_feemasters as $transport_feemaster) {
                    $this->studenttransportfee_model->addStudentTransportFee($student_session_id, $route_id, $pickup_point_id, $transport_feemaster['id']);
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('something_went_wrong'));
            } else {
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('record_updated_successfully'));
            }
        }
        echo json_encode($array);
    }

        public function unassign()

        {

            if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_edit')) {

                access_denied();

            }

    

            $this->form_validation->set_rules('student_session_id[]', 'Students', 'trim|required|xss_clean');

    

            if ($this->form_validation->run() == false) {

                $msg = array(

                    'student_session_id' => form_error('student_session_id[]'),

                );

                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');

            } else {

                $student_session_ids = $this->input->post('student_session_id');

    

                $this->db->trans_start();

    

                foreach ($student_session_ids as $student_session_id) {

                    // Update student_session table

                    $this->student_model->updateStudentSessionTransport($student_session_id, null, null);

    

                    // Remove from student_transport_fees

                    $this->studenttransportfee_model->removeStudentTransportFee($student_session_id);

                }

    

                $this->db->trans_complete();

    

                if ($this->db->trans_status() === FALSE) {

                    $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('something_went_wrong'));

                } else {

                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('record_updated_successfully'));

                }

            }

            echo json_encode($array);

        }

    

        public function save_assignments()

        {

            if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_edit')) {

                access_denied();

            }

    

            $this->form_validation->set_rules('all_student_session_ids[]', 'All Students', 'trim|required|xss_clean');

            // unassigned_student_session_ids[] is optional, as all students might be checked

    

            if ($this->form_validation->run() == false) {

                $msg = array(

                    'all_student_session_ids' => form_error('all_student_session_ids[]'),

                );

                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');

            } else {

                $all_student_session_ids = $this->input->post('all_student_session_ids');

                $unassigned_student_session_ids = $this->input->post('unassigned_student_session_ids'); // This can be null if all are checked

    

                if (empty($unassigned_student_session_ids)) {

                    $unassigned_student_session_ids = array(); // Ensure it's an array for array_diff

                }

    

                $this->db->trans_start();

    

                // Students to be unassigned are those whose checkboxes were unchecked

                foreach ($unassigned_student_session_ids as $student_session_id) {

                    $this->student_model->updateStudentSessionTransport($student_session_id, null, null);

                    $this->studenttransportfee_model->removeStudentTransportFee($student_session_id);

                }

    

                // Students who were previously unassigned but are now checked (re-assigned)

                // This logic is not fully implemented as the form doesn't provide route/pickup_point for re-assignment.

                // The request was specifically for un-checking to un-assign.

                // If re-assignment is needed, the form would need to provide route_id and pickup_point_id for each student.

    

                $this->db->trans_complete();

    

                if ($this->db->trans_status() === FALSE) {

                    $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('something_went_wrong'));

                } else {

                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('record_updated_successfully'));

                }

            }

            echo json_encode($array);

        }

    }
