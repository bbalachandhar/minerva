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

    public function index($id = null)
    {
        if (!$this->rbac->hasPrivilege('library_subject', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarysubject/index');

        $data['title']      = 'Add Subject';
        $data['title_list'] = 'Subject Details';

        if ($id) {
            $data['edit_subject'] = $this->librarysubject_model->get($id);
        } else {
            $data['edit_subject'] = null;
        }
        
        $this->form_validation->set_rules('subject_name', 'Subject Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listsubject         = $this->librarysubject_model->get();
            $data['listsubject'] = $listsubject;
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubject/index', $data);
            $this->load->view('layout/footer');
        } else {
            $subject_name = $this->input->post('subject_name');
            $description    = $this->input->post('description');
            $subject_id   = $this->input->post('id'); // Get ID from hidden field if editing

            $data = array(
                'subject_name' => $subject_name,
                'description'      => $description,
            );

            if ($subject_id) {
                $data['id'] = $subject_id;
                $this->librarysubject_model->add($data); // add() handles both insert and update
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->librarysubject_model->add($data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            }
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

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_subject', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarysubject/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $data = array(
                        'subject_name' => $row['subject_name'],
                        'description' => $row['description'],
                    );
                    $this->librarysubject_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Subjects imported successfully</div>');
                redirect('admin/librarysubject/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarysubject/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_subject_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_subject_sample_file.xls';
        force_download($name, $data);
    }
} 
