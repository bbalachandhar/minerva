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
        // Redirect standalone page access to settings tab
        redirect('admin/onlineadmission/admissionsetting');
    }

    public function add()
    {
        // Simple test - write to a file to confirm method is being called
        file_put_contents('/tmp/admissioncourses_add_called.txt', 'Method called at ' . date('Y-m-d H:i:s'));
        
        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add')) {
            access_denied();
        }
        
        // CRITICAL: Clear any stale flashdata IMMEDIATELY to prevent old errors from showing
        $this->session->unset_userdata(array('msg', 'error', 'debug'));
        
        // Get POST data
        $course_name = $this->input->post('course_name');
        $course_code = $this->input->post('course_code');
        $description = $this->input->post('description');
        $is_active = $this->input->post('is_active');
        
        // Log for debugging
        error_log('=== Admissioncourses->add() called ===');
        error_log('POST data: ' . json_encode($_POST));
        error_log('course_name: ' . $course_name);
        error_log('is_active: ' . $is_active);
        
        // Check if course_name is empty
        if (empty($course_name)) {
            error_log('course_name is empty');
            $this->session->set_flashdata('error', '<div class="alert alert-danger">Course Name is required!</div>');
            redirect('admin/onlineadmission/admissionsetting');
            return;
        }
        
        // Prepare data for insert
        $data = array(
            'course_name' => $course_name,
            'course_code' => $course_code,
            'description' => $description,
            'is_active'   => $is_active,
            'is_restricted' => (int)(bool)$this->input->post('is_restricted'),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        );
        
        error_log('Data to insert: ' . json_encode($data));
        
        // Insert directly without validation
        $insert_id = $this->Onlineadmissioncourses_model->add($data);
        
        error_log('Insert result ID: ' . $insert_id);
        
        if ($insert_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-success">✓ Course added successfully! ID: ' . $insert_id . '</div>');
        } else {
            $this->session->set_flashdata('error', '<div class="alert alert-danger">✗ Error adding course</div>');
        }
        
        redirect('admin/onlineadmission/admissionsetting#tab_3');
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
            $this->load->view('admin/onlineadmission/admissioncourses/edit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $course_name = $this->input->post('course_name');
            $course_code = $this->input->post('course_code');
            
            // Manual duplicate check (exclude current record)
            $this->db->where('course_name', $course_name);
            $this->db->where('id !=', $id);
            if ($this->db->get('online_admission_courses')->num_rows() > 0) {
                $this->session->set_flashdata('error', '<div class="alert alert-danger">The Course Name already exists.</div>');
                redirect('admin/onlineadmission/admissioncourses/edit/' . $id);
            }
            
            if (!empty($course_code)) {
                $this->db->where('course_code', $course_code);
                $this->db->where('id !=', $id);
                if ($this->db->get('online_admission_courses')->num_rows() > 0) {
                    $this->session->set_flashdata('error', '<div class="alert alert-danger">The Course Code already exists.</div>');
                    redirect('admin/onlineadmission/admissioncourses/edit/' . $id);
                }
            }
            
            $data = array(
                'id'            => $id,
                'course_name'   => $course_name,
                'course_code'   => $course_code,
                'description'   => $this->input->post('description'),
                'is_active'     => $this->input->post('is_active'),
                'is_restricted' => (int)(bool)$this->input->post('is_restricted'),
                'updated_at'    => date('Y-m-d H:i:s'),
            );
            $this->Onlineadmissioncourses_model->add($data);
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
