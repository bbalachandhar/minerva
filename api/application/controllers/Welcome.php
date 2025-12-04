<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{
    public $email;
    public $Setting_model;
    public $customlib;
    public $setting_model;
    public $student_model;
    public $customfield_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('student_model', 'customfield_model', 'setting_model'));
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

}
