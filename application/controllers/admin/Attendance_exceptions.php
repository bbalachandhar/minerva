<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Attendance_exceptions extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("staff_biometric_punches_model");
        $this->load->model("staffattendancemodel");
        $this->load->model("staff_model");
    }

    public function index()
    {
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/attendance_exceptions/index');
        
        $data['title'] = 'Attendance Exceptions';
        $data['title_list'] = 'Biometric Punch Exceptions';
        
        // Get unresolved exceptions
        $data['exceptions'] = $this->staff_biometric_punches_model->get_unresolved_exceptions();
        $data['exception_count'] = $this->staff_biometric_punches_model->count_unresolved_exceptions();
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance_exceptions/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function resolve()
    {
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_edit'))) {
            access_denied();
        }

        $punch_id = $this->input->post('punch_id');
        $action = $this->input->post('action');
        $staff_id = $this->session->userdata('staff_id');

        if (empty($punch_id) || empty($action)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            return;
        }

        // Get the punch details
        $punch = $this->staff_biometric_punches_model->get_by_id($punch_id);
        
        if (!$punch) {
            echo json_encode(['status' => 'error', 'message' => 'Punch not found']);
            return;
        }

        $punch_date = date('Y-m-d', strtotime($punch['punch_time']));
        $punch_staff_id = $punch['staff_id'];

        // Handle different resolution actions
        switch ($action) {
            case 'assign_current_day':
                // Simply mark as resolved - it will be picked up in normal processing
                $result = $this->staff_biometric_punches_model->resolve_exception($punch_id, 'assign_current_day', $staff_id);
                $message = 'Exception resolved. Punch assigned to current day.';
                break;

            case 'assign_previous_day':
                // Get previous working day
                $previous_date = date('Y-m-d', strtotime($punch_date . ' -1 day'));
                
                // Update the punch_time to previous day with same time
                $time_part = date('H:i:s', strtotime($punch['punch_time']));
                $new_punch_time = $previous_date . ' ' . $time_part;
                
                $this->db->where('id', $punch_id);
                $this->db->update('staff_biometric_punches', ['punch_time' => $new_punch_time]);
                
                // Mark as resolved
                $result = $this->staff_biometric_punches_model->resolve_exception($punch_id, 'assign_previous_day', $staff_id);
                $message = 'Exception resolved. Punch assigned to previous day (' . $previous_date . ').';
                break;

            case 'mark_invalid':
                // Just mark as resolved without processing
                $result = $this->staff_biometric_punches_model->resolve_exception($punch_id, 'mark_invalid', $staff_id);
                $message = 'Exception resolved. Punch marked as invalid.';
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
                return;
        }

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => $message]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to resolve exception']);
        }
    }

    public function get_punch_context()
    {
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_view'))) {
            access_denied();
        }

        $punch_id = $this->input->post('punch_id');
        
        if (empty($punch_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            return;
        }

        $punch = $this->staff_biometric_punches_model->get_by_id($punch_id);
        
        if (!$punch) {
            echo json_encode(['status' => 'error', 'message' => 'Punch not found']);
            return;
        }

        $punch_date = date('Y-m-d', strtotime($punch['punch_time']));
        $previous_date = date('Y-m-d', strtotime($punch_date . ' -1 day'));
        $staff_id = $punch['staff_id'];

        // Get all punches for current day
        $current_day_punches = $this->staff_biometric_punches_model->get_punches_by_staff_and_date($staff_id, $punch_date);
        
        // Get all punches for previous day
        $previous_day_punches = $this->staff_biometric_punches_model->get_punches_by_staff_and_date($staff_id, $previous_date);

        // Get attendance record for previous day
        $previous_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $previous_date);

        echo json_encode([
            'status' => 'success',
            'punch' => $punch,
            'current_day_punches' => $current_day_punches,
            'previous_day_punches' => $previous_day_punches,
            'previous_attendance' => $previous_attendance,
            'punch_date' => $punch_date,
            'previous_date' => $previous_date
        ]);
    }
}
