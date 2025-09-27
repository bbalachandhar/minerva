<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Libraryvendor extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('libraryvendor_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_vendor', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'libraryvendor/index');

        $data['title']      = 'Add Vendor';
        $data['title_list'] = 'Vendor Details';
        
        $this->form_validation->set_rules('vendor_name', 'Vendor Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listvendor         = $this->libraryvendor_model->get();
            $data['listvendor'] = $listvendor;
            $this->load->view('layout/header');
            $this->load->view('admin/libraryvendor/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'vendor_name' => $this->input->post('vendor_name'),
                'description'      => $this->input->post('description'),
            );
            $this->libraryvendor_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Vendor added successfully</div>');
            redirect('admin/libraryvendor/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_vendor', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Vendor';
        $data['id']         = $id;
        $editvendor           = $this->libraryvendor_model->get($id);
        $data['editvendor']   = $editvendor;
        
        $this->form_validation->set_rules('vendor_name', 'Vendor Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listvendor         = $this->libraryvendor_model->get();
            $data['listvendor'] = $listvendor;
            $this->load->view('layout/header');
            $this->load->view('admin/libraryvendor/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'vendor_name' => $this->input->post('vendor_name'),
                'description'      => $this->input->post('description'),
            );
            $this->libraryvendor_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Vendor updated successfully</div>');
            redirect('admin/libraryvendor/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_vendor', 'can_delete')) {
            access_denied();
        }
        $this->libraryvendor_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Vendor deleted successfully</div>');
        redirect('admin/libraryvendor/index');
    }
}
