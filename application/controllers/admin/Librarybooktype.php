<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarybooktype extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarybooktype_model');
    }

    public function index($id = null)
    {
        if (!$this->rbac->hasPrivilege('library_book_type', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarybooktype/index');

        $data['title']      = 'Add Book Type';
        $data['title_list'] = 'Book Type Details';

        if ($id) {
            $data['edit_booktype'] = $this->librarybooktype_model->get($id);
        } else {
            $data['edit_booktype'] = null;
        }
        
        $this->form_validation->set_rules('book_type_name', 'Book Type Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listbooktype         = $this->librarybooktype_model->get();
            $data['listbooktype'] = $listbooktype;
            $this->load->view('layout/header');
            $this->load->view('admin/librarybooktype/index', $data);
            $this->load->view('layout/footer');
        } else {
            $book_type_name = $this->input->post('book_type_name');
            $description    = $this->input->post('description');
            $booktype_id   = $this->input->post('id'); // Get ID from hidden field if editing

            $data = array(
                'book_type_name' => $book_type_name,
                'description'      => $description,
            );

            if ($booktype_id) {
                $data['id'] = $booktype_id;
                $this->librarybooktype_model->add($data); // add() handles both insert and update
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->librarybooktype_model->add($data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            }
            redirect('admin/librarybooktype/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_book_type', 'can_delete')) {
            access_denied();
        }
        $this->librarybooktype_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Book Type deleted successfully</div>');
        redirect('admin/librarybooktype/index');
    }

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_book_type', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarybooktype/import');
            $this->load->view('layout/footer');
        }
        else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $data = array(
                        'book_type_name' => $row['book_type_name'],
                        'description' => $row['description'],
                    );
                    $this->librarybooktype_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Book Types imported successfully</div>');
                redirect('admin/librarybooktype/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarybooktype/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_booktype_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_booktype_sample_file.xls';
        force_download($name, $data);
    }
} 
