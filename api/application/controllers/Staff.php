<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Staff extends CI_Controller
{
    public $Auth_model; // To reference the Auth_model correctly
    public $staff_model;
    public $user_model;
    public $setting_model;
    public $student_model;
    public $customlib;
    public $enc_lib;
    public $staffroles_model; // Not directly used in controller, but good practice if it ever gets loaded here.

    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('staff_model');
    }

    public function profile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $auth = $this->auth_model->auth();
        if ($auth['status'] != 200) {
            json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
            return;
        }

        $staff_id = $this->input->get_request_header('User-ID', true);
        $profile_data = $this->staff_model->getProfile($staff_id);

        if ($profile_data) {
            json_output(200, array('status' => 200, 'message' => 'Profile data retrieved successfully', 'data' => $profile_data));
        } else {
            json_output(404, array('status' => 404, 'message' => 'Profile not found'));
        }
    }

    public function edit_profile()
    {
        // Implementation for editing profile will be added here later.
        json_output(501, array('status' => 501, 'message' => 'Not Implemented'));
    }
}
