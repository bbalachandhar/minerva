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

    public function index($id = null)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'library/category');

        $data['title']      = 'Add Library Category';
        $data['title_list'] = 'Library Category List';

        if ($id) {
            $data['edit_category'] = $this->librarycategory_model->get($id);
        } else {
            $data['edit_category'] = null;
        }

        $this->form_validation->set_rules('category_name', $this->lang->line('category_name'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $categorylist         = $this->librarycategory_model->get();
            $data['categorylist'] = $categorylist;
            $this->load->view('layout/header');
            $this->load->view('admin/librarycategory/index', $data);
            $this->load->view('layout/footer');
        } else {
            $category_name = $this->input->post('category_name');
            $category_id   = $this->input->post('id'); // Get ID from hidden field if editing

            $data = array(
                'category_name' => $category_name,
            );

            if ($category_id) {
                $data['id'] = $category_id;
                $this->librarycategory_model->add($data); // add() handles both insert and update
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->librarycategory_model->add($data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            }
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

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_category', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarycategory/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $data = array(
                        'category_name' => $row['category_name'],
                        'description' => $row['description'],
                    );
                    $this->librarycategory_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Categories imported successfully</div>');
                redirect('admin/librarycategory/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarycategory/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_category_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_category_sample_file.xls';
        force_download($name, $data);
    }
}
