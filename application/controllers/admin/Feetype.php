<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Feetype extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'feetype/index');

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if (!$this->rbac->hasPrivilege('fees_type', 'can_add')) {
                access_denied();
            }
            $this->form_validation->set_rules('name', $this->lang->line('name'), 'required|trim');
            $this->form_validation->set_rules('code', $this->lang->line('fees_code'), 'required|trim');

            if ($this->form_validation->run()) {
                $this->feetype_model->add([
                    'type'            => $this->input->post('name'),
                    'code'            => $this->input->post('code'),
                    'sub_merchant_id' => $this->input->post('sub_merchant_id'),
                    'description'     => $this->input->post('description'),
                    'is_active'       => 'yes',
                ]);
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                redirect('admin/feetype/index');
                return;
            }
            // Validation failed — fall through, reload list (modal re-opens via JS)
        }

        $data['feetypeList'] = $this->feetype_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/feetype/feetypeList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_edit')) {
            access_denied();
        }

        // GET: redirect to list — old standalone edit page has been removed
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('admin/feetype/index');
            return;
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'feetype/index');

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'required|trim');
        $this->form_validation->set_rules('code', $this->lang->line('fees_code'), 'required|trim');

        if ($this->form_validation->run()) {
            $this->feetype_model->add([
                'id'              => $id,
                'type'            => $this->input->post('name'),
                'code'            => $this->input->post('code'),
                'sub_merchant_id' => $this->input->post('sub_merchant_id'),
                'description'     => $this->input->post('description'),
                'is_active'       => $this->input->post('is_active') ?: 'yes',
            ]);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
        }

        redirect('admin/feetype/index');
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_delete')) {
            access_denied();
        }
        $this->feetype_model->remove($id);
        redirect('admin/feetype/index');
    }

    public function toggle_active($id)
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_edit')) {
            access_denied();
        }
        $feetype = $this->feetype_model->get($id);
        if ($feetype) {
            $new_status = ($feetype['is_active'] === 'yes') ? 'no' : 'yes';
            $this->feetype_model->add(['id' => $id, 'is_active' => $new_status]);
        }
        redirect('admin/feetype/index');
    }

}
