<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Naac_reports extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('naac_model');
        $this->load->library('rbac');

        // Check if user is logged in, if not, redirect to login page
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login'); // Assuming 'auth/login' is your login route
        }
    }

    public function index() {
        $data['page_title'] = 'NAAC Reports';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/index');
        $this->load->view('layout/footer');
    }

    public function criterion1_report() {
        $data['page_title'] = 'NAAC Criterion 1 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c1_1_data'] = $this->naac_model->get_c1_1_report_data($academic_year);
        $data['c1_2_data'] = $this->naac_model->get_c1_2_report_data($academic_year);
        $data['c1_3_data'] = $this->naac_model->get_c1_3_report_data($academic_year);
        $data['c1_4_data'] = $this->naac_model->get_c1_4_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion1_report', $data);
        $this->load->view('layout/footer');
    }

    public function criterion2_report() {
        $data['page_title'] = 'NAAC Criterion 2 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c2_1_data'] = $this->naac_model->get_c2_1_report_data($academic_year);
        $data['c2_2_data'] = $this->naac_model->get_c2_2_report_data($academic_year);
        $data['c2_3_data'] = $this->naac_model->get_c2_3_report_data($academic_year);
        $data['c2_4_data'] = $this->naac_model->get_c2_4_report_data($academic_year);
        $data['c2_5_data'] = $this->naac_model->get_c2_5_report_data($academic_year);
        $data['c2_6_data'] = $this->naac_model->get_c2_6_report_data($academic_year);
        $data['c2_7_data'] = $this->naac_model->get_c2_7_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion2_report', $data);
        $this->load->view('layout/footer');
    }

    public function criterion3_report() {
        $data['page_title'] = 'NAAC Criterion 3 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c3_1_data'] = $this->naac_model->get_c3_1_report_data($academic_year);
        $data['c3_2_data'] = $this->naac_model->get_c3_2_report_data($academic_year);
        $data['c3_3_data'] = $this->naac_model->get_c3_3_report_data($academic_year);
        $data['c3_4_data'] = $this->naac_model->get_c3_4_report_data($academic_year);
        $data['c3_5_data'] = $this->naac_model->get_c3_5_report_data($academic_year);
        $data['c3_6_data'] = $this->naac_model->get_c3_6_report_data($academic_year);
        $data['c3_7_data'] = $this->naac_model->get_c3_7_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion3_report', $data);
        $this->load->view('layout/footer');
    }

    public function criterion4_report() {
        $data['page_title'] = 'NAAC Criterion 4 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c4_1_data'] = $this->naac_model->get_c4_1_report_data($academic_year);
        $data['c4_2_data'] = $this->naac_model->get_c4_2_report_data($academic_year);
        $data['c4_3_data'] = $this->naac_model->get_c4_3_report_data($academic_year);
        $data['c4_4_data'] = $this->naac_model->get_c4_4_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion4_report', $data);
        $this->load->view('layout/footer');
    }

    public function criterion5_report() {
        $data['page_title'] = 'NAAC Criterion 5 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c5_1_data'] = $this->naac_model->get_c5_1_report_data($academic_year);
        $data['c5_2_data'] = $this->naac_model->get_c5_2_report_data($academic_year);
        $data['c5_3_data'] = $this->naac_model->get_c5_3_report_data($academic_year);
        $data['c5_4_data'] = $this->naac_model->get_c5_4_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion5_report', $data);
        $this->load->view('layout/footer');
    }

    public function criterion6_report() {
        $data['page_title'] = 'NAAC Criterion 6 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c6_1_data'] = $this->naac_model->get_c6_1_report_data($academic_year);
        $data['c6_2_data'] = $this->naac_model->get_c6_2_report_data($academic_year);
        $data['c6_3_data'] = $this->naac_model->get_c6_3_report_data($academic_year);
        $data['c6_4_data'] = $this->naac_model->get_c6_4_report_data($academic_year);
        $data['c6_5_data'] = $this->naac_model->get_c6_5_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion6_report', $data);
        $this->load->view('layout/footer');
    }

    public function criterion7_report() {
        $data['page_title'] = 'NAAC Criterion 7 Report';
        $academic_year = $this->input->get('academic_year'); // Get academic year from filter

        $data['c7_1_data'] = $this->naac_model->get_c7_1_report_data($academic_year);
        $data['c7_2_data'] = $this->naac_model->get_c7_2_report_data($academic_year);
        $data['c7_3_data'] = $this->naac_model->get_c7_3_report_data($academic_year);

        $this->load->view('layout/header', $data);
        $this->load->view('naac/reports/criterion7_report', $data);
        $this->load->view('layout/footer');
    }
}
