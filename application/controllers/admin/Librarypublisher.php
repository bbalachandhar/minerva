<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarypublisher extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarypublisher_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_publisher', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarypublisher/index');

        $data['title']      = 'Add Publisher';
        $data['title_list'] = 'Publisher Details';
        
        $this->form_validation->set_rules('publisher_name', 'Publisher Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpublisher         = $this->librarypublisher_model->get();
            $data['listpublisher'] = $listpublisher;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypublisher/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'publisher_name' => $this->input->post('publisher_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarypublisher_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Publisher added successfully</div>');
            redirect('admin/librarypublisher/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_publisher', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Publisher';
        $data['id']         = $id;
        $editpublisher           = $this->librarypublisher_model->get($id);
        $data['editpublisher']   = $editpublisher;
        
        $this->form_validation->set_rules('publisher_name', 'Publisher Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpublisher         = $this->librarypublisher_model->get();
            $data['listpublisher'] = $listpublisher;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypublisher/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'publisher_name' => $this->input->post('publisher_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarypublisher_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Publisher updated successfully</div>');
            redirect('admin/librarypublisher/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_publisher', 'can_delete')) {
            access_denied();
        }
        $this->librarypublisher_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Publisher deleted successfully</div>');
        redirect('admin/librarypublisher/index');
    }
}
