<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Admissioncourses extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Onlineadmissioncourses_model");
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('course_name', $this->lang->line('course_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('is_active', $this->lang->line('status'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->session->set_flashdata('error', validation_errors());
        } else {
            $data = array(
                'course_name' => $this->input->post('course_name'),
                'course_code' => $this->input->post('course_code'),
                'description' => $this->input->post('description'),
                'is_active'   => $this->input->post('is_active'),
            );

            $this->Onlineadmissioncourses_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
        }
                        redirect('admin/onlineadmission/admissionsetting#' . $this->input->post('active_tab'));
                    }
                
                    public function edit($id)
                    {
                        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) {
                            access_denied();
                        }
                
                        $this->load->model('Onlineadmissioncourses_model');
                        $data['course']      = $this->Onlineadmissioncourses_model->get($id);
                        $data['course_list'] = $this->Onlineadmissioncourses_model->get();
                
                        $this->form_validation->set_rules('course_name', $this->lang->line('course_name'), 'trim|required|xss_clean');
                        $this->form_validation->set_rules('is_active', $this->lang->line('status'), 'trim|required|xss_clean');
                
                        if ($this->form_validation->run() == false) {
                            $this->load->view('layout/header');
                            $this->load->view('admin/admissioncourses/edit', $data);
                            $this->load->view('layout/footer');
                        } else {
                            $data = array(
                                'id'          => $id,
                                'course_name' => $this->input->post('course_name'),
                                'course_code' => $this->input->post('course_code'),
                                'description' => $this->input->post('description'),
                                'is_active'   => $this->input->post('is_active'),
                            );
                
                            $this->Onlineadmissioncourses_model->add($data);
                            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('update_message') . '</div>');
                            redirect('admin/onlineadmission/admissionsetting#tab_3');
                        }
                    }
                
                    public function delete($id)
                    {
                        if (!$this->rbac->hasPrivilege('online_admission_admission_courses', 'can_delete')) {
                            access_denied();
                        }
                
                        $this->Onlineadmissioncourses_model->remove($id);
                        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('delete_message') . '</div>');
                        redirect('admin/onlineadmission/admissionsetting#tab_3');
                    }        }
        
