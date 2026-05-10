<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_application
 *
 * Exam Events management:
 * - List all end-semester exam events for a session
 * - Mark an existing exam_group as end-semester (CoE)
 * - Generate student application rows in bulk
 * - View per-event application list
 */
class Coe_application extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // INDEX — list exam events for the active session
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_application');

        $session_id = $this->input->get('session_id') ?: $this->current_session;

        $data['title']            = $this->lang->line('coe_exam_events');
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = (int) $session_id;
        $data['events']           = $this->Coe_application_model->getExamEventsBySession($session_id);

        // All exam_groups available for linking (for the "mark as CoE" dropdown)
        $data['all_exam_groups']  = $this->db->where('is_active', 1)->get('exam_groups')->result();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_application/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // MARK an exam_group as end-semester CoE exam
    // ------------------------------------------------------------------
    public function mark_end_semester()
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('exam_group_id',  'Exam Group',      'trim|required|integer');
        $this->form_validation->set_rules('exam_category',  'Exam Category',   'trim|required|in_list[main,arrear,supplementary]');
        $this->form_validation->set_rules('exam_type',      'Exam Mode',       'trim|required|in_list[theory,practical,project,viva,online]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            redirect('coe/coe_application');
        }

        $exam_group_id = (int) $this->input->post('exam_group_id');
        $exam_category = $this->input->post('exam_category');
        $exam_type     = $this->input->post('exam_type');

        $this->Coe_application_model->markEndSemester($exam_group_id, $exam_category, $exam_type);
        $this->Coe_audit_model->log('exam_group_marked_coe', 'exam_groups', $exam_group_id, null, ['exam_category' => $exam_category, 'exam_type' => $exam_type]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Exam group marked as CoE end-semester exam.</div>');
        redirect('coe/coe_application');
    }

    // ------------------------------------------------------------------
    // GENERATE applications for a batch exam
    // ------------------------------------------------------------------
    public function generate($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        // Check: exam must not be coe_locked
        if ($event->coe_locked) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">Cannot generate applications: exam is locked by CoE.</div>');
            redirect('coe/coe_application');
        }

        $result = $this->Coe_application_model->generateApplications($batch_exam_id, $event->exam_group_id);

        if (isset($result['error'])) {
            $msg_map = [
                'no_subjects' => 'No subjects assigned to this exam batch. Please assign exam subjects first.',
                'no_students' => 'No students found in this exam batch.',
            ];
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">' . ($msg_map[$result['error']] ?? 'Error generating applications.') . '</div>');
        } else {
            $this->Coe_audit_model->log('applications_generated', 'exam_group_class_batch_exams', $batch_exam_id, null, $result);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('coe_applications_generated') . ' Inserted: ' . $result['inserted'] . ', Skipped (duplicate): ' . $result['skipped'] . '</div>');
        }

        redirect('coe/coe_application/view/' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // VIEW — applications for a specific batch exam
    // ------------------------------------------------------------------
    public function view($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = [
            'application_status' => $this->input->get('application_status'),
            'cbcs_category'      => $this->input->get('cbcs_category'),
        ];

        $data['title']          = $this->lang->line('coe_exam_events') . ' — ' . $event->exam;
        $data['event']          = $event;
        $data['applications']   = $this->Coe_application_model->getApplicationsByBatchExam($batch_exam_id, $filters);
        $data['stats']          = $this->Coe_application_model->getApplicationStats($batch_exam_id);
        $data['filters']        = $filters;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_application/view', $data);
        $this->load->view('layout/footer', $data);
    }
}
