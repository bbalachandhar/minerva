<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staffbirthday extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('customlib');
        $this->load->model('Staffbirthday_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('staff_birthday_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/staff_birthday_report');

        $data['title'] = 'Staff Birthday Report';

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
                'staff_id',
                'name',
                'role',
                'department',
                'dob',
                'gender',
                'mobileno',
                'email',
                'current_address'
            );
            $order_column_name = $columns[$order_column];

            $result = $this->Staffbirthday_model->searchByBirthdayRangeDT($date_from, $date_to, $start, $length, $search_value, $order_column_name, $order_dir);
            echo json_encode($result);
            exit;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staff_birthday_report', $data);
        $this->load->view('layout/footer', $data);
    }
}
