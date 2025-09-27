<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarypositionrack extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarypositionrack_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_position_rack', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarypositionrack/index');

        $data['title']      = 'Add Position Rack';
        $data['title_list'] = 'Position Rack Details';
        
        $this->form_validation->set_rules('rack_name', 'Position Rack Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpositionrack         = $this->librarypositionrack_model->get();
            $data['listpositionrack'] = $listpositionrack;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionrack/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'rack_name' => $this->input->post('rack_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarypositionrack_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Rack added successfully</div>');
            redirect('admin/librarypositionrack/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_position_rack', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Position Rack';
        $data['id']         = $id;
        $editpositionrack           = $this->librarypositionrack_model->get($id);
        $data['editpositionrack']   = $editpositionrack;
        
        $this->form_validation->set_rules('rack_name', 'Position Rack Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpositionrack         = $this->librarypositionrack_model->get();
            $data['listpositionrack'] = $listpositionrack;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionrack/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'rack_name' => $this->input->post('rack_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarypositionrack_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Rack updated successfully</div>');
            redirect('admin/librarypositionrack/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_position_rack', 'can_delete')) {
            access_denied();
        }
        $this->librarypositionrack_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Rack deleted successfully</div>');
        redirect('admin/librarypositionrack/index');
    }
}
