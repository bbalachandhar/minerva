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

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_publisher', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarypublisher/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $data = array(
                        'publisher_name' => $row['publisher_name'],
                        'description' => $row['description'],
                    );
                    $this->librarypublisher_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Publishers imported successfully</div>');
                redirect('admin/librarypublisher/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarypublisher/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_publisher_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_publisher_sample_file.xls';
        force_download($name, $data);
    }
}
