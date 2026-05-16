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
            'active_only'    => (bool) $this->input->get('active_only'),
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

    // ------------------------------------------------------------------
    // review_applications() — list student-submitted arrear applications
    // ------------------------------------------------------------------
    public function review_applications()
    {
        if (!$this->rbac->hasPrivilege('coe_arrear', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $this->input->get('batch_exam_id');
        $status        = $this->input->get('status') ?: 'pending';

        $data['applications']  = $this->Coe_arrear_model->getArrearApplications($batch_exam_id, $status);
        $data['batch_exam_id'] = $batch_exam_id;
        $data['status']        = $status;
        $data['events']        = $this->Coe_application_model->getExamEventsBySession($this->current_session);
        $data['title']         = 'Arrear / Supplementary Applications';

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_arrear/applications', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // review($id) — approve or reject a single application (AJAX POST)
    // ------------------------------------------------------------------
    public function review($id)
    {
        if (!$this->rbac->hasPrivilege('coe_arrear', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'POST required']);
            return;
        }

        $action = $this->input->post('action');
        if (!in_array($action, ['approved', 'rejected'])) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
            return;
        }

        $remarks = trim($this->input->post('remarks'));

        $app = $this->db->where('id', (int) $id)->get('coe_arrear_applications')->row();
        if (!$app || $app->status !== 'pending') {
            echo json_encode(['status' => 'error', 'msg' => 'Application not found or already reviewed.']);
            return;
        }

        $this->db->where('id', (int) $id)->update('coe_arrear_applications', [
            'status'           => $action,
            'reviewed_by'      => (int) $this->customlib->getStaffID(),
            'reviewed_at'      => date('Y-m-d H:i:s'),
            'reviewer_remarks' => $remarks,
        ]);

        $this->Coe_audit_model->log('arrear_application_' . $action, 'coe_arrear_applications', $id,
            ['status' => 'pending'], ['status' => $action, 'remarks' => $remarks]);

        echo json_encode(['status' => 'success', 'msg' => 'Application ' . $action . '.']);
    }
}
