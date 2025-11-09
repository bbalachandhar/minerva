<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hostel extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('Customlib');
        $this->load->model('hostelroom_model');
        $this->load->model('student_model');
    }

    public function bulk_assign_students()
    {
        $this->load->library('csvimport');
        $hostel_id = $this->input->post('hostel_id');
        $success_list = array();
        $warning_list = array();
        $error_list   = array();

        if (isset($_FILES['student_csv']) && !empty($_FILES['student_csv']['name'])) {
            $file_path = $_FILES['student_csv']['tmp_name'];
            $csv_array = $this->csvimport->get_array($file_path);

            if (!empty($csv_array)) {
                foreach ($csv_array as $row) {
                    $admission_no = $row['admission_no'];
                    $student      = $this->student_model->get_student_by_admission_no($admission_no);

                    if ($student) {
                        $student_id = $student->id;
                        if ($student->hostel_room_id != 0) {
                            $room = $this->hostelroom_model->get($student->hostel_room_id);
                            if ($room['hostel_id'] == $hostel_id) {
                                $warning_list[] = $admission_no . " (Already in Hostel)";
                            } else {
                                $free_room = $this->hostelroom_model->get_free_room($hostel_id);
                                if ($free_room) {
                                    $room_id = $free_room['id'];
                                    $this->student_model->add(array('id' => $student_id, 'hostel_room_id' => $room_id));
                                    $success_list[] = $admission_no;
                                } else {
                                    $error_list[] = $admission_no . " (No Free Room)";
                                }
                            }
                        } else {
                            $free_room = $this->hostelroom_model->get_free_room($hostel_id);

                            if ($free_room) {
                                $room_id = $free_room['id'];
                                $this->student_model->add(array('id' => $student_id, 'hostel_room_id' => $room_id));
                                $success_list[] = $admission_no;
                            } else {
                                $error_list[] = $admission_no . " (No Free Room)";
                            }
                        }
                    } else {
                        $error_list[] = $admission_no . " (Student Not Found)";
                    }
                }

                $message = "";
                if (!empty($success_list)) {
                    $message .= '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '<ul><li>' . implode('</li><li>', $success_list) . '</li></ul></div>';
                }
                if (!empty($warning_list)) {
                    $message .= '<div class="alert alert-warning text-left">Already in Hostel<ul><li>' . implode('</li><li>', $warning_list) . '</li></ul></div>';
                }
                if (!empty($error_list)) {
                    $message .= '<div class="alert alert-danger text-left">Errors<ul><li>' . implode('</li><li>', $error_list) . '</li></ul></div>';
                }

                $this->session->set_flashdata('msg', $message);
            }
        }
        redirect('admin/hostel');
    }

    public function download_template()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/student_bulk_assign_template.csv";
        $data     = file_get_contents($filepath);
        $name     = 'student_bulk_assign_template.csv';
        force_download($name, $data);
    }

    public function index()
    {

        if (!$this->rbac->hasPrivilege('hostel', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hostel');
        $this->session->set_userdata('sub_menu', 'hostel/index');
        $listhostel         = $this->hostel_model->listhostel();
        $data['listhostel'] = $listhostel;
        $ght                = $this->customlib->getHostaltype();
        $data['ght']        = $ght;
        $this->load->view('layout/header');
        $this->load->view('admin/hostel/createhostel', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('hostel', 'can_add')) {
            access_denied();
        }
        $data['title'] = 'Add Library';
        $this->form_validation->set_rules('hostel_name', $this->lang->line('hostel_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $listhostel         = $this->hostel_model->listhostel();
            $data['listhostel'] = $listhostel;
            $ght                = $this->customlib->getHostaltype();
            $data['ght']        = $ght;
            $this->load->view('layout/header');
            $this->load->view('admin/hostel/createhostel', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'hostel_name' => $this->input->post('hostel_name'),
                'type'        => $this->input->post('type'),
                'address'     => $this->input->post('address'),
                'intake'      => $this->input->post('intake'),
                'description' => $this->input->post('description'),
            );
            $this->hostel_model->addhostel($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/hostel/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('hostel', 'can_edit')) {
            access_denied();
        }
        $data['title']      = 'Add Hostel';
        $data['id']         = $id;
        $edithostel         = $this->hostel_model->get($id);
        $data['edithostel'] = $edithostel;
        $ght                = $this->customlib->getHostaltype();
        $data['ght']        = $ght;
        $this->form_validation->set_rules('hostel_name', $this->lang->line('hostel_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $listhostel         = $this->hostel_model->listhostel();
            $data['listhostel'] = $listhostel;
            $this->load->view('layout/header');
            $this->load->view('admin/hostel/edithostel', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'          => $this->input->post('id'),
                'hostel_name' => $this->input->post('hostel_name'),
                'type'        => $this->input->post('type'),
                'address'     => $this->input->post('address'),
                'intake'      => $this->input->post('intake'),
                'description' => $this->input->post('description'),
            );
            $this->hostel_model->addhostel($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/hostel/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('hostel', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->hostel_model->remove($id);
        redirect('admin/hostel/index');
    }

}
