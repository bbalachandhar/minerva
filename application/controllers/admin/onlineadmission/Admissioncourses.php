<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admissioncourses extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Onlineadmissioncourses_model');
        $this->load->library('form_validation');
        $this->load->library('media_storage'); // Assuming this is needed for file uploads if courses had images
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/onlineadmissionsetting');
        $this->session->set_userdata('subsub_menu', 'onlineadmission/admissioncourses');

        $data['title'] = 'Admission Courses';
        $data['course_list'] = $this->Onlineadmissioncourses_model->get();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/onlineadmission/admissioncourses/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('course_name', $this->lang->line('course_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('course_code', $this->lang->line('course_code'), 'trim|xss_clean');
        $this->form_validation->set_rules('description', $this->lang->line('description'), 'trim|xss_clean');
        $this->form_validation->set_rules('is_active', $this->lang->line('status'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $course_list = $this->Onlineadmissioncourses_model->get();
            $data['course_list'] = $course_list;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/onlineadmission/admissioncourses/index', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'course_name' => $this->input->post('course_name'),
                'course_code' => $this->input->post('course_code'),
                'description' => $this->input->post('description'),
                'is_active'   => $this->input->post('is_active'),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            );
            $this->Onlineadmissioncourses_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/onlineadmission/admissioncourses');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('course_name', $this->lang->line('course_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('course_code', $this->lang->line('course_code'), 'trim|xss_clean');
        $this->form_validation->set_rules('description', $this->lang->line('description'), 'trim|xss_clean');
        $this->form_validation->set_rules('is_active', $this->lang->line('status'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Edit Admission Course';
            $data['course_list'] = $this->Onlineadmissioncourses_model->get();
            $data['course_data'] = $this->Onlineadmissioncourses_model->get($id);

            $this->load->view('layout/header', $data);
            $this->load->view('admin/onlineadmission/admissioncourses/edit', $data); // Using a separate edit view
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'id'          => $id,
                'course_name' => $this->input->post('course_name'),
                'course_code' => $this->input->post('course_code'),
                'description' => $this->input->post('description'),
                'is_active'   => $this->input->post('is_active'),
                'updated_at'  => date('Y-m-d H:i:s'),
            );
            $this->Onlineadmissioncourses_model->add($data); // Reusing add for update
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/onlineadmission/admissioncourses');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_delete')) {
            access_denied();
        }
        $this->Onlineadmissioncourses_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/onlineadmission/admissioncourses');
    }
}
