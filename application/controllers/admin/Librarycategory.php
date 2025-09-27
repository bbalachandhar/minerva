<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarycategory extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarycategory_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('library_category', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarycategory/index');

        $data['title']      = 'Add Category';
        $data['title_list'] = 'Category Details';
        
        $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listcategory         = $this->librarycategory_model->get();
            $data['listcategory'] = $listcategory;
            $this->load->view('layout/header');
            $this->load->view('admin/librarycategory/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'category_name' => $this->input->post('category_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarycategory_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Category added successfully</div>');
            redirect('admin/librarycategory/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('library_category', 'can_edit')) {
            access_denied();
        }

        $data['title']      = 'Edit Category';
        $data['id']         = $id;
        $editcategory           = $this->librarycategory_model->get($id);
        $data['editcategory']   = $editcategory;
        
        $this->form_validation->set_rules('category_name', 'Category Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listcategory         = $this->librarycategory_model->get();
            $data['listcategory'] = $listcategory;
            $this->load->view('layout/header');
            $this->load->view('admin/librarycategory/index', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'               => $id,
                'category_name' => $this->input->post('category_name'),
                'description'      => $this->input->post('description'),
            );
            $this->librarycategory_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Category updated successfully</div>');
            redirect('admin/librarycategory/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_category', 'can_delete')) {
            access_denied();
        }
        $this->librarycategory_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Category deleted successfully</div>');
        redirect('admin/librarycategory/index');
    }
}
