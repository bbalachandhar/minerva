<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Library_checkout_pending extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->model('libraryattendance_model');
    }

    public function index()
    {
        // Check privilege if needed
        // if (!$this->rbac->hasPrivilege('library_checkout_pending', 'can_view')) {
        //     access_denied();
        // }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'library/checkoutpending'); // New submenu item

        $data['title'] = $this->lang->line('library_checkout_pending'); // Assuming a language key for the title

        $this->load->view('layout/header');
        $this->load->view('admin/library/checkoutpending', $data);
        $this->load->view('layout/footer');
    }

    public function get_pending_dt()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // Check privilege if needed
        // if (!$this->rbac->hasPrivilege('library_checkout_pending', 'can_view')) {
        //     echo json_encode(array("data" => array()));
        //     exit();
        // }

        $data = $this->libraryattendance_model->get_pending_checkout_records_dt();
        echo $data; // Model already returns JSON
    }
}
