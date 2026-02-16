<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Finalyearclasses extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Finalyearclass_model');
        $this->load->model('Class_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('final_year_classes', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/finalyearclasses');

        $data['classlist'] = $this->Class_model->get();
        $data['selected_class_ids'] = $this->Finalyearclass_model->getClassIds();

        $this->load->view('layout/header');
        $this->load->view('admin/settings/final_year_classes', $data);
        $this->load->view('layout/footer');
    }

    public function save()
    {
        if (!$this->rbac->hasPrivilege('final_year_classes', 'can_edit')) {
            access_denied();
        }

        $class_ids = $this->input->post('class_ids');
        if (!is_array($class_ids)) {
            $class_ids = array();
        }

        $class_ids = array_values(array_unique(array_filter($class_ids, 'strlen')));
        $this->Finalyearclass_model->replaceAll($class_ids);

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Final year classes updated successfully.</div>');
        redirect('admin/finalyearclasses');
    }
}
