<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Periods extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("period_model");
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('manage_periods', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'academics/periods');

        $data['title'] = 'Period List';
        $data['period_list'] = $this->period_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/periods/periodList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('manage_periods', 'can_add')) {
            access_denied();
        }
        $data['title'] = 'Add Period';

        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time_from', 'Time From', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time_to', 'Time To', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['period_list'] = $this->period_model->get();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/periods/periodList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'time_from' => $this->input->post('time_from'),
                'time_to' => $this->input->post('time_to'),
            );
            $this->period_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Period added successfully</div>');
            redirect('admin/periods/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('manage_periods', 'can_edit')) {
            access_denied();
        }
        $data['title'] = 'Edit Period';
        $data['id'] = $id;
        $period = $this->period_model->get($id);
        $data['period'] = $period;

        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time_from', 'Time From', 'trim|required|xss_clean');
        $this->form_validation->set_rules('time_to', 'Time To', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['period_list'] = $this->period_model->get();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/periods/periodEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'time_from' => $this->input->post('time_from'),
                'time_to' => $this->input->post('time_to'),
            );
            $this->period_model->update($id, $data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Period updated successfully</div>');
            redirect('admin/periods/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('manage_periods', 'can_delete')) {
            access_denied();
        }
        $this->period_model->remove($id);
        redirect('admin/periods/index');
    }
}
