<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarypositionshelf extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarypositionshelf_model');
        $this->load->model('librarypositionrack_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_position_shelf', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarypositionshelf/index');

        $data['title']      = 'Add Position Shelf';
        $data['title_list'] = 'Position Shelf Details';
        
        $this->form_validation->set_rules('shelf_name', 'Position Shelf Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('rack_id', 'Position Rack', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpositionshelf         = $this->librarypositionshelf_model->get();
            $data['listpositionshelf'] = $listpositionshelf;
            $listpositionrack         = $this->librarypositionrack_model->get();
            $data['listpositionrack'] = $listpositionrack;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionshelf/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'shelf_name' => $this->input->post('shelf_name'),
                'rack_id' => $this->input->post('rack_id'),
                'description'      => $this->input->post('description'),
            );
            $this->librarypositionshelf_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Shelf added successfully</div>');
            redirect('admin/librarypositionshelf/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_position_shelf', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Position Shelf';
        $data['id']         = $id;
        $editpositionshelf           = $this->librarypositionshelf_model->get($id);
        $data['editpositionshelf']   = $editpositionshelf;
        
        $this->form_validation->set_rules('shelf_name', 'Position Shelf Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('rack_id', 'Position Rack', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpositionshelf         = $this->librarypositionshelf_model->get();
            $data['listpositionshelf'] = $listpositionshelf;
            $listpositionrack         = $this->librarypositionrack_model->get();
            $data['listpositionrack'] = $listpositionrack;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionshelf/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'shelf_name' => $this->input->post('shelf_name'),
                'rack_id' => $this->input->post('rack_id'),
                'description'      => $this->input->post('description'),
            );
            $this->librarypositionshelf_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Shelf updated successfully</div>');
            redirect('admin/librarypositionshelf/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_position_shelf', 'can_delete')) {
            access_denied();
        }
        $this->librarypositionshelf_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Shelf deleted successfully</div>');
        redirect('admin/librarypositionshelf/index');
    }
}
