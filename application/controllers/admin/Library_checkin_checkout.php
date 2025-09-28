<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Library_checkin_checkout extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('libraryattendance_model');
    }

    public function index()
    {
        // Check privilege if needed, similar to OPAQ
        // if (!$this->rbac->hasPrivilege('library_checkin_checkout', 'can_view')) {
        //     access_denied();
        // }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'library/checkincheckout'); // New submenu item

        $data['title'] = $this->lang->line('library_checkin_checkout'); // Assuming a language key for the title

        $this->load->view('layout/header');
        $this->load->view('admin/library/checkincheckout', $data);
        $this->load->view('layout/footer');
    }

    public function process_scan()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $this->form_validation->set_rules('id_number', $this->lang->line('id_number'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $error_array = $this->form_validation->error_array();
            echo json_encode(['status' => 'fail', 'message' => reset($error_array)]);
            exit();
        }

        $id_number = $this->input->post('id_number');
        $current_date = date('Y-m-d');

        $user_details = $this->libraryattendance_model->get_user_details_by_id($id_number);

        if (!$user_details) {
            echo json_encode(['status' => 'fail', 'message' => $this->lang->line('invalid_id_number')]); // Assuming language key
            exit();
        }

        $pending_entry = $this->libraryattendance_model->get_current_day_pending_entry(
            $user_details['user_id'],
            $user_details['user_type'],
            $current_date
        );

        if ($pending_entry) {
            // Found a pending entry, so this is a Check-Out
            $this->libraryattendance_model->record_check_out($pending_entry['id'], $pending_entry['in_time']);
            echo json_encode([
                'status' => 'success',
                'action' => 'checkout',
                'name' => $user_details['name'],
                'message' => $this->lang->line('goodbye') . ' ' . $user_details['name'] . '!' // Assuming language key
            ]);
        } else {
            // No pending entry, so this is a Check-In
            $this->libraryattendance_model->record_check_in(
                $user_details['user_id'],
                $user_details['user_type'],
                $user_details['name']
            );
            echo json_encode([
                'status' => 'success',
                'action' => 'checkin',
                'name' => $user_details['name'],
                'message' => $this->lang->line('welcome') . ' ' . $user_details['name'] . '!' // Assuming language key
            ]);
        }
    }

    public function get_attendance_dt()
    {
        // Check privilege if needed
        // if (!$this->rbac->hasPrivilege('library_checkin_checkout', 'can_view')) {
        //     echo json_encode(array("data" => array()));
        //     exit();
        // }

        $date = $this->input->post('date'); // Optional: filter by date
        $data = $this->libraryattendance_model->get_attendance_records_dt($date);
        echo $data; // Model already returns JSON
    }
}
