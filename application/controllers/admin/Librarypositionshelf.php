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

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_position_shelf', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionshelf/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $rack = $this->librarypositionrack_model->get_rack_by_name($row['rack_name']);
                    if($rack){
                        $rack_id = $rack->id;
                    }else{
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Rack not found for shelf '.$row['shelf_name'].'</div>');
                        redirect('admin/librarypositionshelf/import');
                    }
                    $data = array(
                        'shelf_name' => $row['shelf_name'],
                        'rack_id' => $rack_id,
                        'description' => $row['description'],
                    );
                    $this->librarypositionshelf_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Shelves imported successfully</div>');
                redirect('admin/librarypositionshelf/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarypositionshelf/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_positionshelf_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_positionshelf_sample_file.xls';
        force_download($name, $data);
    }
}
