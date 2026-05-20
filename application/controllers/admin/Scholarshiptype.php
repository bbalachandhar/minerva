<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin CRUD for scholarship type master list.
 */
class Scholarshiptype extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Scholarship_type_model');
        $this->load->model('Scholarship_application_model');
        $this->load->model('Staff_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'admin/scholarshiptype');

        $this->form_validation->set_rules('name', 'Scholarship Name', 'trim|required|max_length[300]|xss_clean');

        if ($this->form_validation->run() === false) {
            $data['scholarship_types'] = $this->Scholarship_type_model->getAll();
            $this->load->view('layout/header');
            $this->load->view('admin/scholarship/scholarshiptype', $data);
            $this->load->view('layout/footer');
        } else {
            if (!$this->rbac->hasPrivilege('online_admission', 'can_add')) {
                access_denied();
            }
            $amount = $this->input->post('amount');
            $insert = array(
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'amount'      => ($amount !== '' && $amount !== null) ? (float) $amount : null,
                'sort_order'  => (int) $this->input->post('sort_order'),
                'is_active'   => 1,
            );
            $this->Scholarship_type_model->insert($insert);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Scholarship type added successfully.</div>');
            redirect('admin/scholarshiptype');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('name', 'Scholarship Name', 'trim|required|max_length[300]|xss_clean');

        if ($this->form_validation->run() === false) {
            $data['scholarship_type']  = $this->Scholarship_type_model->get($id);
            $data['scholarship_types'] = $this->Scholarship_type_model->getAll();
            $this->load->view('layout/header');
            $this->load->view('admin/scholarship/scholarshiptypeedit', $data);
            $this->load->view('layout/footer');
        } else {
            $amount = $this->input->post('amount');
            $update = array(
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'amount'      => ($amount !== '' && $amount !== null) ? (float) $amount : null,
                'sort_order'  => (int) $this->input->post('sort_order'),
                'is_active'   => (int) $this->input->post('is_active'),
            );
            $this->Scholarship_type_model->update($id, $update);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Scholarship type updated successfully.</div>');
            redirect('admin/scholarshiptype');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_delete')) {
            access_denied();
        }
        // Block if any applications reference this type
        $count = $this->db->where('scholarship_type_id', $id)->count_all_results('scholarship_applications');
        if ($count > 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Cannot delete: ' . $count . ' application(s) reference this type.</div>');
        } else {
            $this->Scholarship_type_model->delete($id);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Scholarship type deleted.</div>');
        }
        redirect('admin/scholarshiptype');
    }
}
