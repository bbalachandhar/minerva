<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Departmenthead extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('departmenthead_model');
        $this->load->model('department_model'); // Assuming you have a department model
        $this->load->model('staff_model'); // Assuming you have a staff model
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('assign_department_head', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/departmenthead');

        $data['title'] = $this->lang->line('assign_department_head');
        $data['department_list'] = $this->department_model->get(); // Get all departments
        $data['staff_list'] = $this->staff_model->getStaff(null, 1); // Get all active staff

        $data['department_heads'] = $this->departmenthead_model->get(); // Get all assigned department heads

        $this->load->view('layout/header', $data);
        $this->load->view('admin/departmenthead/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function assign()
    {
        if (!$this->rbac->hasPrivilege('assign_department_head', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('department_id', $this->lang->line('department'), 'required');
        $this->form_validation->set_rules('staff_id', $this->lang->line('department_head'), 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->index();
        } else {
            $data = array(
                'department_id' => $this->input->post('department_id'),
                'staff_id' => $this->input->post('staff_id'),
            );
            $this->departmenthead_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/departmenthead/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('assign_department_head', 'can_delete')) {
            access_denied();
        }
        $this->departmenthead_model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/departmenthead/index');
    }
}
