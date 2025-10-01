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

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_vendor', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/libraryvendor/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $data = array(
                        'vendor_name' => $row['vendor_name'],
                        'description' => $row['description'],
                    );
                    $this->libraryvendor_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Vendors imported successfully</div>');
                redirect('admin/libraryvendor/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/libraryvendor/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_vendor_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_vendor_sample_file.xls';
        force_download($name, $data);
    }
}
