<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarysubject extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarysubject_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_subject', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarysubject/index');

        $data['title']      = 'Add Subject';
        $data['title_list'] = 'Subject Details';
        
        $this->form_validation->set_rules('subject_name', 'Subject Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listsubject         = $this->librarysubject_model->get();
            $data['listsubject'] = $listsubject;
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubject/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'subject_name' => $this->input->post('subject_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarysubject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Subject added successfully</div>');
            redirect('admin/librarysubject/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_subject', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Subject';
        $data['id']         = $id;
        $editsubject           = $this->librarysubject_model->get($id);
        $data['editsubject']   = $editsubject;
        
        $this->form_validation->set_rules('subject_name', 'Subject Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listsubject         = $this->librarysubject_model->get();
            $data['listsubject'] = $listsubject;
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubject/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'subject_name' => $this->input->post('subject_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarysubject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Subject updated successfully</div>');
            redirect('admin/librarysubject/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_subject', 'can_delete')) {
            access_denied();
        }
        $this->librarysubject_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Subject deleted successfully</div>');
        redirect('admin/librarysubject/index');
    }
}
