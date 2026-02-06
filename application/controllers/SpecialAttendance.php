<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Specialattendance extends Admin_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('SpecialAttendance_model');
        $this->load->model('Department_model');
        $this->load->model('Staff_model');
        $this->load->model('StaffAttendanceSchedule_model');
        $this->load->model('StaffBiometricPunchesManual_model');
        $this->load->library('form_validation');
    }

    public function index() {
        $departments = $this->Department_model->getAll();
        $this->load->view('admin/specialattendance/index', ['departments' => $departments]);
    }

    public function get_employees_by_department() {
        $department_id = $this->input->post('department_id');
        $employees = $this->Staff_model->getByDepartment($department_id);
        echo json_encode($employees);
    }

    public function generate_attendance() {
        // Only admin can run
        if (!$this->auth->is_admin()) {
            show_error('Unauthorized', 403);
        }
        $data = $this->input->post();
        $month = $data['month'];
        $year = $data['year'];
        $reason = $data['reason'];
        $admin_user_id = $this->session->userdata('id');
        foreach ($data['employees'] as $emp) {
            $schedule = $this->StaffAttendanceSchedule_model->getByStaffId($emp['id']);
            $days = $emp['days_present'];
            $punches = $this->SpecialAttendance_model->generatePunches($emp['id'], $month, $year, $days, $schedule);
            $this->StaffBiometricPunchesManual_model->insertPunches($emp['id'], $punches, $admin_user_id, $reason);
        }
        echo json_encode(['status' => 'success']);
    }
}
