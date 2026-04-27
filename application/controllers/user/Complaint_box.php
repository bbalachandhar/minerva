<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Complaint_box extends Student_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->model('complaint_Model');
    }

    public function index()
    {
        if (!$this->studentmodule_lib->hasActive('complaint_box')) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Access Denied.</div>');
            redirect('user/home');
        }
        $this->session->set_userdata('top_menu', 'complaint_box');

        $student_session_id      = $this->session->userdata['current_class']['student_session_id'];
        $data['complaints']      = $this->complaint_Model->getByStudentSession($student_session_id);
        $data['complaint_types'] = $this->complaint_Model->getComplaintType();

        // Compute status counts from already-fetched complaints (no extra DB query)
        $counts = ['open_count' => 0, 'in_progress_count' => 0, 'resolved_count' => 0, 'total_count' => count($data['complaints'])];
        foreach ($data['complaints'] as $c) {
            if     ($c['status'] === 'open')        $counts['open_count']++;
            elseif ($c['status'] === 'in_progress') $counts['in_progress_count']++;
            elseif ($c['status'] === 'resolved')    $counts['resolved_count']++;
        }
        $data['status_counts'] = $counts;

        // Submitter info for the form header
        $role       = $this->customlib->getUserRole(); // 'student' or 'parent'
        $student_id = $this->customlib->getStudentSessionUserID();
        $student    = $this->student_model->get($student_id);
        if ($role === 'parent') {
            $data['submitter_name']     = $student['guardian_name'] ?? '';
            $data['submitter_role']     = 'Parent';
            $data['submitter_phone']    = $student['guardian_phone'] ?? '';
        } else {
            $data['submitter_name']     = trim(($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? ''));
            $data['submitter_role']     = 'Student';
            $data['submitter_phone']    = $student['mobileno'] ?? '';
        }
        $data['submitter_id_label'] = $student['admission_no'] ?? '';

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/complaint_box/index', $data);
        $this->load->view('layout/student/footer', $data);
    }

    public function add()
    {
        if (!$this->studentmodule_lib->hasActive('complaint_box')) {
            echo json_encode(['status' => 'fail', 'error' => 'Access denied', 'message' => '']);
            return;
        }

        $this->form_validation->set_rules('complaint_type', 'Complaint Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('description',    'Description',    'trim|required|xss_clean');
        $this->form_validation->set_rules('priority',       'Priority',       'trim|required|in_list[low,medium,high,critical]');

        if ($this->form_validation->run() == false) {
            echo json_encode([
                'status'  => 'fail',
                'error'   => [
                    'complaint_type' => form_error('complaint_type'),
                    'description'    => form_error('description'),
                    'priority'       => form_error('priority'),
                ],
                'message' => '',
            ]);
            return;
        }

        $student_session_id = $this->session->userdata['current_class']['student_session_id'];
        $student_id         = $this->customlib->getStudentSessionUserID();
        $student            = $this->student_model->get($student_id);
        $role               = $this->customlib->getUserRole();

        $full_name = trim(($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? ''));
        $mobile    = trim($this->input->post('contact', true)) ?: ($student['mobileno'] ?? '');
        $email     = $student['email'] ?? '';
        $source    = ($role === 'parent') ? 'Parent Portal' : 'Student Portal';
        $submitted = ($role === 'parent') ? 'parent' : 'student';
        $parent_name = ($role === 'parent') ? ($student['guardian_name'] ?? '') : '';

        $image = '';
        if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != '' && $_FILES['attachment']['error'] == 0) {
            $this->customlib->ensureDirectoryExists('./uploads/front_office/complaints/');
            $upload_result = $this->media_storage->fileupload('attachment', './uploads/front_office/complaints/');
            if ($upload_result['status'] === false) {
                echo json_encode(['status' => 'fail', 'error' => ['attachment' => $upload_result['message']], 'message' => '']);
                return;
            }
            $image = $upload_result['message'];
        }

        $insert = [
            'student_session_id' => $student_session_id,
            'submitted_by'       => $submitted,
            'complaint_type'     => $this->input->post('complaint_type', true),
            'source'             => $source,
            'name'               => $full_name,
            'admission_no'       => $student['admission_no'] ?? '',
            'class_name'         => $student['class'] ?? '',
            'section_name'       => $student['section'] ?? '',
            'parent_name'        => $parent_name,
            'contact'            => $mobile,
            'email'              => $email,
            'date'               => date('Y-m-d'),
            'description'        => $this->input->post('description', true),
            'priority'           => $this->input->post('priority'),
            'status'             => 'open',
            'image'              => $image,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        $id = $this->complaint_Model->add($insert);

        if ($id) {
            echo json_encode(['status' => 'success', 'error' => '', 'message' => $this->lang->line('complaint_submitted_success')]);
        } else {
            echo json_encode(['status' => 'fail', 'error' => 'Database error', 'message' => '']);
        }
    }

    public function get_detail($id)
    {
        $id                 = (int)$id;
        $student_session_id = $this->session->userdata['current_class']['student_session_id'];
        $row                = $this->complaint_Model->complaint_list($id);

        if (!$row || (int)$row['student_session_id'] !== $student_session_id) {
            echo json_encode(['status' => 'fail', 'message' => 'Not found']);
            return;
        }

        echo json_encode($row);
    }

    public function delete_complaint($id)
    {
        $id                 = (int)$id;
        $student_session_id = $this->session->userdata['current_class']['student_session_id'];
        $row                = $this->complaint_Model->complaint_list($id);

        if (!$row || (int)$row['student_session_id'] !== $student_session_id) {
            echo json_encode(['status' => 'fail', 'message' => 'Not found']);
            return;
        }
        if ($row['status'] !== 'open' || !empty($row['action_taken'])) {
            echo json_encode(['status' => 'fail', 'message' => 'Cannot delete after action has been taken.']);
            return;
        }
        $this->complaint_Model->delete($id);
        echo json_encode(['status' => 'success', 'message' => 'Complaint deleted successfully.']);
    }

    public function update($id)
    {
        $id                 = (int)$id;
        $student_session_id = $this->session->userdata['current_class']['student_session_id'];
        $row                = $this->complaint_Model->complaint_list($id);

        if (!$row || (int)$row['student_session_id'] !== $student_session_id) {
            echo json_encode(['status' => 'fail', 'message' => 'Not found']);
            return;
        }
        if ($row['status'] !== 'open' || !empty($row['action_taken'])) {
            echo json_encode(['status' => 'fail', 'message' => 'Cannot edit after action has been taken.']);
            return;
        }

        $this->form_validation->set_rules('complaint_type', 'Complaint Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('description',    'Description',    'trim|required|xss_clean');
        $this->form_validation->set_rules('priority',       'Priority',       'trim|required|in_list[low,medium,high,critical]');

        if ($this->form_validation->run() == false) {
            echo json_encode(['status' => 'fail', 'message' => strip_tags(validation_errors())]);
            return;
        }

        $update = [
            'complaint_type' => $this->input->post('complaint_type', true),
            'priority'       => $this->input->post('priority', true),
            'description'    => $this->input->post('description', true),
            'contact'        => $this->input->post('contact', true) ?: $row['contact'],
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if (isset($_FILES['attachment']) && $_FILES['attachment']['name'] != '' && $_FILES['attachment']['error'] == 0) {
            $this->customlib->ensureDirectoryExists('./uploads/front_office/complaints/');
            $upload_result = $this->media_storage->fileupload('attachment', './uploads/front_office/complaints/');
            if ($upload_result['status'] === false) {
                echo json_encode(['status' => 'fail', 'message' => $upload_result['message']]);
                return;
            }
            $update['image'] = $upload_result['message'];
        }

        $this->complaint_Model->compalaint_update($id, $update);
        echo json_encode(['status' => 'success', 'message' => $this->lang->line('record_updated_successfully')]);
    }
}
