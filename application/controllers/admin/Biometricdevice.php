<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Biometricdevice extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('biometric_device_model');
    }

    public function index() {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/biometricdevice');
        $data['title'] = 'Biometric Devices';
        $data['device_list'] = $this->biometric_device_model->get();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/biometricdevice/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add() {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_add'))) {
            access_denied();
        }

        $this->form_validation->set_rules('device_name', 'Device Name', 'required');
        $this->form_validation->set_rules('serial_number', 'Serial Number', 'required');
        $this->form_validation->set_rules('api_endpoint', 'API Endpoint', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->index();
        } else {
            $data = array(
                'device_name' => $this->input->post('device_name'),
                'serial_number' => $this->input->post('serial_number'),
                'api_endpoint' => $this->input->post('api_endpoint'),
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            );

            if ($data['is_active']) {
                $this->biometric_device_model->deactivateAllDevices();
            }

            $this->biometric_device_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Device added successfully</div>');
            redirect('admin/biometricdevice/index');
        }
    }

    public function edit($id) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_edit'))) {
            access_denied();
        }

        $this->form_validation->set_rules('device_name', 'Device Name', 'required');
        $this->form_validation->set_rules('serial_number', 'Serial Number', 'required');
        $this->form_validation->set_rules('api_endpoint', 'API Endpoint', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $data['device'] = $this->biometric_device_model->get($id);
            $data['device_list'] = $this->biometric_device_model->get();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/biometricdevice/index', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'device_name' => $this->input->post('device_name'),
                'serial_number' => $this->input->post('serial_number'),
                'api_endpoint' => $this->input->post('api_endpoint'),
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
            );

            if ($data['is_active']) {
                $this->biometric_device_model->deactivateAllDevices();
            }

            $this->biometric_device_model->update($id, $data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Device updated successfully</div>');
            redirect('admin/biometricdevice/index');
        }
    }

    public function delete($id) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_delete'))) {
            access_denied();
        }
        $this->biometric_device_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Device deleted successfully</div>');
        redirect('admin/biometricdevice/index');
    }

    public function activate($id) {
        if (!($this->rbac->hasPrivilege('biometric_device', 'can_edit'))) {
            access_denied();
        }
        $this->biometric_device_model->deactivateAllDevices();
        $this->biometric_device_model->update($id, ['is_active' => 1]);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Device activated successfully</div>');
        redirect('admin/biometricdevice/index');
    }

}
