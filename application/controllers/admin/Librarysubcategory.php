<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarysubcategory extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarysubcategory_model');
        $this->load->model('librarycategory_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_subcategory', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarysubcategory/index');

        $data['title']      = 'Add Sub Category';
        $data['title_list'] = 'Sub Category Details';
        
        $this->form_validation->set_rules('subcategory_name', 'Sub Category Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('category_id', 'Category', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listsubcategory         = $this->librarysubcategory_model->get();
            $data['listsubcategory'] = $listsubcategory;
            $listcategory         = $this->librarycategory_model->get();
            $data['listcategory'] = $listcategory;
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubcategory/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'subcategory_name' => $this->input->post('subcategory_name'),
                'category_id' => $this->input->post('category_id'),
                'description'      => $this->input->post('description'),
            );
            $this->librarysubcategory_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Sub Category added successfully</div>');
            redirect('admin/librarysubcategory/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_subcategory', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Sub Category';
        $data['id']         = $id;
        $editsubcategory           = $this->librarysubcategory_model->get($id);
        $data['editsubcategory']   = $editsubcategory;
        
        $this->form_validation->set_rules('subcategory_name', 'Sub Category Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('category_id', 'Category', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listsubcategory         = $this->librarysubcategory_model->get();
            $data['listsubcategory'] = $listsubcategory;
            $listcategory         = $this->librarycategory_model->get();
            $data['listcategory'] = $listcategory;
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubcategory/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'subcategory_name' => $this->input->post('subcategory_name'),
                'category_id' => $this->input->post('category_id'),
                'description'      => $this->input->post('description'),
            );
            $this->librarysubcategory_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Sub Category updated successfully</div>');
            redirect('admin/librarysubcategory/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_subcategory', 'can_delete')) {
            access_denied();
        }
        $this->librarysubcategory_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Sub Category deleted successfully</div>');
        redirect('admin/librarysubcategory/index');
    }
}
