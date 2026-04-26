<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hostel_fee_override extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hostel_fee_override_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('hostel_fee_override', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/hostel_fee_override');

        $hostel_fee_groups = $this->hostel_fee_override_model->getHostelFeetypeIds();
        $data['hostel_fee_groups']  = $hostel_fee_groups;
        $data['title']              = 'Hostel Fee Override';
        $data['student_list']       = null;
        $data['selected_fsg_id']    = null;

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $fsg_id = (int)$this->input->post('fee_session_group_id');
            if ($fsg_id) {
                $data['selected_fsg_id'] = $fsg_id;
                $data['student_list']    = $this->hostel_fee_override_model->getStudentsWithHostelFee($fsg_id);
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/hostel_fee_override/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save()
    {
        if (!$this->rbac->hasPrivilege('hostel_fee_override', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            return;
        }

        $student_session_id    = (int)$this->input->post('student_session_id');
        $fee_groups_feetype_id = (int)$this->input->post('fee_groups_feetype_id');
        $override_amount       = (float)$this->input->post('override_amount');
        $note                  = $this->input->post('note');

        if (!$student_session_id || !$fee_groups_feetype_id || $override_amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
            return;
        }

        // Check paid amount — override must be >= already paid
        $paid = $this->hostel_fee_override_model->getPaidAmount($student_session_id, $fee_groups_feetype_id);
        if ($override_amount < $paid) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'User already paid more than what you are trying to reduce from the actual hostel fee, so our system won\'t allow it.',
            ]);
            return;
        }

        $user_id = $this->customlib->getStaffID();
        $result  = $this->hostel_fee_override_model->saveOverride(
            $student_session_id,
            $fee_groups_feetype_id,
            $override_amount,
            $note,
            $user_id
        );

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Override saved successfully.', 'paid' => $paid]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save override.']);
        }
    }

    public function delete()
    {
        if (!$this->rbac->hasPrivilege('hostel_fee_override', 'can_delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            return;
        }

        $student_session_id    = (int)$this->input->post('student_session_id');
        $fee_groups_feetype_id = (int)$this->input->post('fee_groups_feetype_id');

        // Only allow delete if nothing has been paid yet
        $paid = $this->hostel_fee_override_model->getPaidAmount($student_session_id, $fee_groups_feetype_id);
        if ($paid > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot remove override after payment has been made.']);
            return;
        }

        $result = $this->hostel_fee_override_model->deleteOverride($student_session_id, $fee_groups_feetype_id);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Override removed.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No override found to remove.']);
        }
    }
}
