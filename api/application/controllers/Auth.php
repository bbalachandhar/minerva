<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public $authentication_model;
    public $setting_model;
    public
 
$form_validation;
    public $db;
    public $email;
    public $customlib;
    public $user_model;
    public $student_model;
    public $auth_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
    }

    public function login()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $params   = json_decode(file_get_contents('php://input'), true);
                if ($params) {
                    $username = $params['username'];
                    $password = $params['password'];
                    $app_key  = $params['deviceToken'];
                    $response = $this->auth_model->login($username, $password, $app_key);
                    json_output(200, $response);
                } else {
                    json_output(400, array('status' => 400, 'message' => 'Bad request.'));
                }
            }
        }
    }

    public function staff_login()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $params   = json_decode(file_get_contents('php://input'), true);
                if ($params) {
                    $email    = $params['email'];
                    $password = $params['password'];
                    $app_key  = $params['deviceToken'];
                    $response = $this->auth_model->staff_login($email, $password, $app_key);
                    json_output(200, $response);
                } else {
                    json_output(400, array('status' => 400, 'message' => 'Bad request.'));
                }
            }
        }
    }

    public function staff_logout()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), true);
            if ($params && isset($params['deviceToken'])) {
                $response = $this->auth_model->staff_logout($params['deviceToken']);
                json_output(200, $response);
            } else {
                json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            }
        }
    }
}
