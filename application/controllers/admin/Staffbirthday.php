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

        // Initialize $date_from and $date_to from session or default to current date
        $data['date_from'] = $this->session->userdata('staff_birthday_report_date_from');
        $data['date_to']   = $this->session->userdata('staff_birthday_report_date_to');

        if (empty($data['date_from'])) {
            $data['date_from'] = date($this->customlib->getSchoolDateFormat());
        }
        if (empty($data['date_to'])) {
            $data['date_to'] = date($this->customlib->getSchoolDateFormat());
        }


        if ($this->input->server('REQUEST_METHOD') == 'POST' && $this->input->is_ajax_request()) {
            // Temporarily enable full error reporting for debugging AJAX
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            $date_from = $this->input->post('date_from');
            $date_to   = $this->input->post('date_to');

            // Save dates to session for persistence
            $this->session->set_userdata('staff_birthday_report_date_from', $date_from);
            $this->session->set_userdata('staff_birthday_report_date_to', $date_to);

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

            $response = [];
            try {
                $result = $this->Staffbirthday_model->searchByBirthdayRangeDT($date_from, $date_to, $start, $length, $search_value, $order_column_name, $order_dir);
                $response = $result;
            } catch (Exception $e) {
                // Log the exception and return a JSON error response
                log_message('error', 'Staff Birthday Report AJAX Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
                $response = [
                    "draw" => (int)$draw,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "error" => "An error occurred: " . $e->getMessage()
                ];
            }
            echo json_encode($response);
            exit;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staff_birthday_report', $data);
        $this->load->view('layout/footer', $data);
    }
}
