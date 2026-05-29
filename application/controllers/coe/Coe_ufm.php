<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_ufm
 *
 * UFM (Unfair Means) / Malpractice incident management.
 *
 * Routes:
 *   GET  coe/coe_ufm                        → index (pick batch exam)
 *   GET  coe/coe_ufm/list/:batch_exam_id    → list incidents
 *   GET  coe/coe_ufm/report/:batch_exam_id  → report form
 *   POST coe/coe_ufm/save/:batch_exam_id    → save new incident
 *   GET  coe/coe_ufm/view/:id              → view single incident
 *   POST coe/coe_ufm/review/:id            → update status/penalty
 *   GET  coe/coe_ufm/delete/:id            → delete
 */
class Coe_ufm extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_ufm_model');
    }

    // ------------------------------------------------------------------
    // INDEX — select batch exam
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_view')) {
            access_denied();
        }

        $session_id = $this->input->get('session_id') ?: $this->current_session;

        $data['title']            = 'UFM / Malpractice';
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = $session_id;
        $data['events']           = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_ufm/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // LIST — all incidents for a batch exam
    // ------------------------------------------------------------------
    public function listing($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = ['batch_exam_id' => $batch_exam_id];
        if ($this->input->get('status')) {
            $filters['status'] = $this->input->get('status');
        }

        $data['title']         = 'UFM Incidents: ' . $event->exam_group_name;
        $data['event']         = $event;
        $data['batch_exam_id'] = (int) $batch_exam_id;
        $data['incidents']     = $this->Coe_ufm_model->getAll($filters);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_ufm/listing', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // REPORT — form to log new incident
    // ------------------------------------------------------------------
    public function report($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['title']         = 'Report UFM Incident';
        $data['event']         = $event;
        $data['batch_exam_id'] = (int) $batch_exam_id;
        $data['rooms']         = $this->Coe_ufm_model->getRoomsByBatchExam($batch_exam_id);
        $data['hall_tickets']  = $this->Coe_ufm_model->getHallTicketsByBatchExam($batch_exam_id);
        $data['staff_list']    = $this->Coe_ufm_model->getStaffList();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_ufm/report', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // SAVE — create new incident
    // ------------------------------------------------------------------
    public function save($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_add')) {
            access_denied();
        }

        $valid_types = ['copying','mobile_phone','impersonation','unfair_material','communication','other'];

        $this->form_validation->set_rules('hall_ticket_no',  'Hall Ticket No',   'trim|required');
        $this->form_validation->set_rules('seating_room_id', 'Room',             'trim|required|integer');
        $this->form_validation->set_rules('exam_date',       'Exam Date',        'trim|required');
        $this->form_validation->set_rules('session_slot',    'Session',          'trim|required|in_list[FN,AN]');
        $this->form_validation->set_rules('incident_type',   'Incident Type',    'trim|required|in_list[' . implode(',', $valid_types) . ']');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('coe/coe_ufm/report/' . $batch_exam_id);
        }

        $hall_ticket_no  = $this->input->post('hall_ticket_no');
        $ht = $this->Coe_ufm_model->getHallTicketByNo($hall_ticket_no, $batch_exam_id);

        if (!$ht) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Hall ticket number not found in this exam batch.</div>');
            redirect('coe/coe_ufm/report/' . $batch_exam_id);
        }

        $witness_id = (int) $this->input->post('witness_staff_id');

        $data = [
            'coe_hall_ticket_id' => $ht->id,
            'seating_room_id'    => (int) $this->input->post('seating_room_id'),
            'exam_date'          => date('Y-m-d', strtotime($this->input->post('exam_date'))),
            'session_slot'       => $this->input->post('session_slot'),
            'incident_type'      => $this->input->post('incident_type'),
            'description'        => $this->input->post('description'),
            'material_seized'    => $this->input->post('material_seized'),
            'reported_by'        => $this->customlib->getStaffID(),
            'witness_staff_id'   => $witness_id ?: null,
            'status'             => 'reported',
        ];

        $id = $this->Coe_ufm_model->insert($data);
        $this->Coe_audit_model->log('ufm_incident_reported', 'coe_ufm_incidents', $id, null, $data);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">UFM incident reported (ID #' . $id . ').</div>');
        redirect('coe/coe_ufm/listing/' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // VIEW — single incident detail
    // ------------------------------------------------------------------
    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_view')) {
            access_denied();
        }

        $incident = $this->Coe_ufm_model->getById($id);
        if (!$incident) {
            show_404();
        }

        $data['title']    = 'UFM Incident #' . $id;
        $data['incident'] = $incident;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_ufm/view', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // MODAL CONTENT — returns partial HTML for AJAX modal (no layout)
    // ------------------------------------------------------------------
    public function modal_content($id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_view')) {
            show_error('Forbidden', 403);
        }

        if (!$this->input->is_ajax_request()) {
            redirect('coe/coe_ufm/view/' . (int) $id);
        }

        $incident = $this->Coe_ufm_model->getById($id);
        if (!$incident) {
            show_error('Incident not found', 404);
        }

        $this->load->view('admin/coe/coe_ufm/_incident_detail', ['incident' => $incident]);
    }

    // ------------------------------------------------------------------
    // REVIEW — update status and penalty
    // ------------------------------------------------------------------
    public function review($id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_edit')) {
            access_denied();
        }

        $incident = $this->Coe_ufm_model->getById($id);
        if (!$incident) {
            show_404();
        }

        $valid_statuses = ['reported','under_review','penalised','dismissed'];
        $status = $this->input->post('status');
        if (!in_array($status, $valid_statuses, true)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid status.</div>');
            redirect('coe/coe_ufm/view/' . $id);
        }

        $this->Coe_ufm_model->update($id, [
            'status'      => $status,
            'penalty'     => $this->input->post('penalty'),
            'reviewed_by' => $this->customlib->getStaffID(),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->Coe_audit_model->log('ufm_incident_reviewed', 'coe_ufm_incidents', $id, null, ['status' => $status]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Incident updated.</div>');
        redirect('coe/coe_ufm/listing/' . $incident->batch_exam_id);
    }

    // ------------------------------------------------------------------
    // DELETE
    // ------------------------------------------------------------------
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('coe_ufm', 'can_delete')) {
            access_denied();
        }

        $incident = $this->Coe_ufm_model->getById($id);
        if (!$incident) {
            show_404();
        }

        $this->Coe_ufm_model->delete($id);
        $this->Coe_audit_model->log('ufm_incident_deleted', 'coe_ufm_incidents', $id, null, null);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Incident deleted.</div>');
        redirect('coe/coe_ufm/listing/' . $incident->batch_exam_id);
    }
}
