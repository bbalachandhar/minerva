<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Birthday extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('customlib');
        $this->load->model('Birthday_model'); // Load the new Birthday_model
    }

    public function birthday_list()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'admin/birthday/birthday_list'); // This will need to be updated for menu highlighting

        $data['title'] = 'Birthday Report';

        if ($this->input->server('REQUEST_METHOD') == 'POST' && $this->input->is_ajax_request()) {
            $date_from = $this->input->post('date_from');
            $date_to   = $this->input->post('date_to');

            $draw = $this->input->post('draw');
            $start = $this->input->post('start');
            $length = $this->input->post('length');
            $search_value = $this->input->post('search')['value'];
            $order_column = $this->input->post('order')[0]['column'];
            $order_dir = $this->input->post('order')[0]['dir'];

            $columns = array(
                'admission_no',
                'roll_no',
                'firstname',
                'class',
                'section',
                'dob',
                'gender',
                'mobileno',
                'email',
                'current_address'
            );
            $order_column_name = $columns[$order_column];

            $result = $this->Birthday_model->searchByBirthdayRangeDT($date_from, $date_to, $start, $length, $search_value, $order_column_name, $order_dir);
            echo json_encode($result);
            exit;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('student/birthday_report', $data); // Use the existing birthday_report view
        $this->load->view('layout/footer', $data);
    }
}