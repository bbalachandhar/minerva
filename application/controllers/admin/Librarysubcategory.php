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

    public function index($id = null)
    {
        if (!$this->rbac->hasPrivilege('books', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'library/subcategory');

        $data['title']      = 'Add Library Subcategory';
        $data['title_list'] = 'Library Subcategory List';

        if ($id) {
            $data['edit_subcategory'] = $this->librarysubcategory_model->get($id);
        } else {
            $data['edit_subcategory'] = null;
        }

        $this->form_validation->set_rules('category_id', $this->lang->line('category'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subcategory_name', $this->lang->line('subcategory_name'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $subcategorylist         = $this->librarysubcategory_model->get();
            $data['subcategorylist'] = $subcategorylist;
            $categorylist            = $this->librarycategory_model->get();
            $data['categorylist']    = $categorylist;
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubcategory/index', $data);
            $this->load->view('layout/footer');
        } else {
            $subcategory_name = $this->input->post('subcategory_name');
            $category_id      = $this->input->post('category_id');
            $subcategory_id   = $this->input->post('id'); // Get ID from hidden field if editing

            $data = array(
                'subcategory_name' => $subcategory_name,
                'category_id'      => $category_id,
            );

            if ($subcategory_id) {
                $data['id'] = $subcategory_id;
                $this->librarysubcategory_model->add($data); // add() handles both insert and update
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->librarysubcategory_model->add($data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            }
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

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_subcategory', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubcategory/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $category = $this->librarycategory_model->get_category_by_name($row['category_name']);
                    if($category){
                        $category_id = $category->id;
                    }else{
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Category not found for subcategory '.$row['subcategory_name'].'</div>');
                        redirect('admin/librarysubcategory/import');
                    }
                    $data = array(
                        'subcategory_name' => $row['subcategory_name'],
                        'category_id' => $category_id,
                        'description' => $row['description'],
                    );
                    $this->librarysubcategory_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Sub Categories imported successfully</div>');
                redirect('admin/librarysubcategory/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarysubcategory/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_subcategory_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_subcategory_sample_file.xls';
        force_download($name, $data);
    }
}
