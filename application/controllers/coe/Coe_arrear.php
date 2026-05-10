<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Coe_arrear
 * Arrear register — track students with failed subjects.
 */
class Coe_arrear extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_arrear_model');
        $this->load->model('coe/Coe_application_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_arrear', 'can_view')) {
            access_denied();
        }

        $session_id = (int) ($this->input->get('session_id') ?: $this->current_session);

        $filters = [
            'batch_exam_id'  => $this->input->get('batch_exam_id'),
            'department_id'  => $this->input->get('department_id'),
            'class_id'       => $this->input->get('class_id'),
            'search'         => $this->input->get('search'),
        ];

        $data['session_id']  = $session_id;
        $data['filters']     = $filters;
        $data['arrears']     = $this->Coe_arrear_model->getArrearList($session_id, $filters);
        $data['events']      = $this->Coe_application_model->getExamEventsBySession($session_id);
        $data['departments'] = $this->Coe_arrear_model->getDepartments();

        $data['sessions'] = $this->db
            ->select('id, session')->from('sessions')
            ->order_by('id', 'DESC')->limit(10)->get()->result();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_arrear/index', $data);
        $this->load->view('layout/footer');
    }

    public function student($student_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_arrear', 'can_view')) {
            access_denied();
        }

        $student_id = (int) $student_id;
        $data['student'] = $this->Coe_arrear_model->getStudentInfo($student_id);
        if (empty($data['student'])) {
            show_404();
        }

        // Filter params
        $filters = [
            'session_id'    => (int) $this->input->get('session_id')    ?: null,
            'batch_exam_id' => (int) $this->input->get('batch_exam_id') ?: null,
            'active_only'   => (bool) $this->input->get('active_only'),
        ];
        $data['filters'] = $filters;

        // Sessions and events for filter dropdowns (only sessions where student has results)
        $data['student_sessions'] = $this->Coe_arrear_model->getStudentResultSessions($student_id);
        $data['student_events']   = $this->Coe_arrear_model->getStudentResultEvents($student_id, $filters['session_id']);

        $data['arrears']      = $this->Coe_arrear_model->getStudentArrears($student_id, $filters);
        $data['sgpa_history'] = $this->Coe_arrear_model->getStudentSGPAHistory($student_id, $filters['session_id']);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_arrear/student', $data);
        $this->load->view('layout/footer');
    }
}
