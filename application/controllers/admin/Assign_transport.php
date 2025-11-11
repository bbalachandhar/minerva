<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Assign_transport extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model(array('class_model', 'section_model', 'student_model', 'route_model', 'Pickuppoint_model', 'vehroute_model', 'studenttransportfee_model', 'transportfee_model'));
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_view')) { // Using existing privilege
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'admin/assign_transport'); // New sub-menu item

        $data['title'] = 'Assign Transport';
        $data['classlist'] = $this->class_model->get();
        $data['route_list'] = $this->route_model->get();
        $data['pickup_point_list'] = $this->Pickuppoint_model->get(); // For initial load of dropdowns

        $data['class_id'] = $this->input->post('class_id');
        $data['section_id'] = $this->input->post('section_id');
        $data['search_text'] = $this->input->post('search_text');

        $students = [];
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $students = $this->student_model->searchByClassSectionAndText($data['class_id'], $data['section_id'], $data['search_text'], null, null);
        }
        $data['studentlist'] = $students;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/assign_transport/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function assign()
    {
        if (!$this->rbac->hasPrivilege('assign_transport_fee', 'can_edit')) { // Using existing privilege
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

            log_message('debug', 'Assign_transport: Attempting to get vehicle route for route_id: ' . $route_id . ' and pickup_point_id: ' . $pickup_point_id);
            $vehroute = $this->vehroute_model->getVehrouteByRouteAndPickupPoint($route_id, $pickup_point_id);
            log_message('debug', 'Assign_transport: Result of getVehrouteByRouteAndPickupPoint: ' . json_encode($vehroute));

            if (empty($vehroute)) {
                log_message('error', 'Assign_transport: No vehicle route found for route_id: ' . $route_id . ' and pickup_point_id: ' . $pickup_point_id);
                $array = array('status' => 'fail', 'error' => array('pickup_point_id' => 'No vehicle route found for the selected route and pickup point.'), 'message' => '');
                echo json_encode($array);
                exit();
            }

            $vehroute_id = $vehroute['id'];

            $current_session_id = $this->setting_model->getCurrentSession();
            $transport_feemasters = $this->transportfee_model->getSessionFees($current_session_id);

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
}
