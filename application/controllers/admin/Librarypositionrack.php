<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarypositionrack extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('librarypositionrack_model');
    }

    public function index($id = null)
    {
        if (!$this->rbac->hasPrivilege('library_position_rack', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'librarypositionrack/index');

        $data['title']      = 'Add Position Rack';
        $data['title_list'] = 'Position Rack Details';

        if ($id) {
            $data['edit_positionrack'] = $this->librarypositionrack_model->get($id);
        } else {
            $data['edit_positionrack'] = null;
        }
        
        $this->form_validation->set_rules('rack_name', 'Position Rack Name', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $listpositionrack         = $this->librarypositionrack_model->get();
            $data['listpositionrack'] = $listpositionrack;
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionrack/index', $data);
            $this->load->view('layout/footer');
        } else {
            $rack_name = $this->input->post('rack_name');
            $description    = $this->input->post('description');
            $positionrack_id   = $this->input->post('id'); // Get ID from hidden field if editing

            $data = array(
                'rack_name' => $rack_name,
                'description'      => $description,
            );

            if ($positionrack_id) {
                $data['id'] = $positionrack_id;
                $this->librarypositionrack_model->add($data); // add() handles both insert and update
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            } else {
                $this->librarypositionrack_model->add($data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            }
            redirect('admin/librarypositionrack/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('library_position_rack', 'can_delete')) {
            access_denied();
        }
        $this->librarypositionrack_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Rack deleted successfully</div>');
        redirect('admin/librarypositionrack/index');
    }

    public function import()
    {
        if (!$this->rbac->hasPrivilege('library_position_rack', 'can_add')) {
            access_denied();
        }
        $this->load->library('CSVReader');
        $this->form_validation->set_rules('file', 'File', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/librarypositionrack/import');
            $this->load->view('layout/footer');
        } else {
            $file = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file);
            if (!empty($result)) {
                foreach ($result as $row) {
                    $data = array(
                        'rack_name' => $row['rack_name'],
                        'description' => $row['description'],
                    );
                    $this->librarypositionrack_model->add($data);
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Position Racks imported successfully</div>');
                redirect('admin/librarypositionrack/index');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data found in the file.</div>');
                redirect('admin/librarypositionrack/import');
            }
        }
    }

    public function import_sample()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_positionrack_sample_file.xls";
        $data = file_get_contents($filepath);
        $name = 'import_positionrack_sample_file.xls';
        force_download($name, $data);
    }
}
