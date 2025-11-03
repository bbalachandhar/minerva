<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Incidental_fee_type extends Admin_Controller { // Assuming Admin_Controller exists for authentication/layout

    public function __construct() {
        parent::__construct();
        $this->load->model('incidental_fee_type_model');
        $this->load->library('form_validation');
        // Assuming you have a language file for incidental fees, or using a generic one
        $this->lang->load('message', 'english');
    }

    public function index() {
        if (!$this->rbac->hasPrivilege('incidental_fee_type', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
$this->session->set_userdata('sub_menu', 'admin/incidental_fee_type');
        $data['incidental_fee_type_list'] = $this->incidental_fee_type_model->get();

        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('default_amount', $this->lang->line('default_amount'), 'numeric|trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/incidental_fee_type/incidental_fee_type_list', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'default_amount' => $this->input->post('default_amount') ? $this->input->post('default_amount') : NULL,
                'is_assignable' => $this->input->post('is_assignable') ? 1 : 0,
                'created_by' => $this->customlib->getStaffID(), // Assuming customlib and getStaffID() exist
            );

            $this->incidental_fee_type_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/incidental_fee_type');
        }
    }

    public function edit($id) {
        if (!$this->rbac->hasPrivilege('incidental_fee_type', 'can_edit')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/incidental_fee_type');

        $data['title'] = 'Edit Incidental Fee Type';
        $data['incidental_fee_type_list'] = $this->incidental_fee_type_model->get();
        $data['incidental_fee_type'] = $this->incidental_fee_type_model->get($id);

        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('default_amount', $this->lang->line('default_amount'), 'numeric|trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/incidental_fee_type/incidental_fee_type_edit', $data); // Assuming a separate edit view
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'default_amount' => $this->input->post('default_amount') ? $this->input->post('default_amount') : NULL,
                'is_assignable' => $this->input->post('is_assignable') ? 1 : 0,
            );

            $this->incidental_fee_type_model->update($id, $data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/incidental_fee_type');
        }
    }

    public function delete($id) {
        if (!$this->rbac->hasPrivilege('incidental_fee_type', 'can_delete')) {
            access_denied();
        }
        $this->incidental_fee_type_model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/incidental_fee_type');
    }
}
