<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Coe_flyingsquad extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_flyingsquad_model');
        $this->load->model('coe/Coe_application_model');
    }

    // ------------------------------------------------------------------
    // index() — Choose exam event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_flyingsquad', 'can_view')) {
            access_denied();
        }

        $session_id         = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']     = $this->Coe_application_model->getExamEventsBySession($session_id);
        $data['title']      = 'Flying Squad Visits';

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_flyingsquad/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // manage($batch_exam_id) — List + add visits
    // ------------------------------------------------------------------
    public function manage($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_flyingsquad', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = [
            'visit_date' => $this->input->get('visit_date'),
            'severity'   => $this->input->get('severity'),
        ];

        $data['title']            = 'Flying Squad Visits';
        $data['event']            = $event;
        $data['batch_exam_id']    = $batch_exam_id;
        $data['visits']           = $this->Coe_flyingsquad_model->getVisits($batch_exam_id, $filters);
        $data['staff']            = $this->Coe_flyingsquad_model->getStaff();
        $data['halls']            = $this->Coe_flyingsquad_model->getHalls();
        $data['severity_summary'] = $this->Coe_flyingsquad_model->getSeveritySummary($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_flyingsquad/manage', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // add() — Save new visit (AJAX POST)
    // ------------------------------------------------------------------
    public function add()
    {
        if (!$this->rbac->hasPrivilege('coe_flyingsquad', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'POST required']);
            return;
        }

        $this->form_validation->set_rules('batch_exam_id',     'Exam Event',        'required|integer');
        $this->form_validation->set_rules('observer_staff_id', 'Observer',          'required|integer');
        $this->form_validation->set_rules('visit_date',        'Visit Date',        'required');
        $this->form_validation->set_rules('observations',      'Observations',      'required');

        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $allowed_severity = ['none', 'minor', 'major'];
        $severity = $this->input->post('severity');
        if (!in_array($severity, $allowed_severity)) {
            $severity = 'none';
        }

        $irregularities = (bool) $this->input->post('irregularities_found');

        $data = [
            'exam_group_class_batch_exam_id' => (int) $this->input->post('batch_exam_id'),
            'observer_staff_id'              => (int) $this->input->post('observer_staff_id'),
            'visit_date'                     => $this->input->post('visit_date'),
            'visit_time'                     => $this->input->post('visit_time') ?: date('H:i:s'),
            'hall_id'                        => (int) $this->input->post('hall_id') ?: null,
            'hall_name'                      => $this->input->post('hall_name') ?: null,
            'observations'                   => $this->input->post('observations'),
            'irregularities_found'           => $irregularities ? 1 : 0,
            'irregularity_details'           => $irregularities ? $this->input->post('irregularity_details') : null,
            'action_taken'                   => $this->input->post('action_taken'),
            'severity'                       => $severity,
        ];

        $id = $this->Coe_flyingsquad_model->insert($data);
        $this->Coe_audit_model->log('flying_squad_add', 'coe_flying_squad_visits', $id, null, $data);

        echo json_encode(['status' => 'success', 'msg' => 'Visit recorded. ID: ' . $id, 'id' => $id]);
    }

    // ------------------------------------------------------------------
    // edit($id) — Update visit (AJAX POST)
    // ------------------------------------------------------------------
    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('coe_flyingsquad', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $visit = $this->Coe_flyingsquad_model->getById($id);
        if (empty($visit)) {
            echo json_encode(['status' => 'error', 'msg' => 'Visit not found']);
            return;
        }

        $allowed_severity = ['none', 'minor', 'major'];
        $severity = $this->input->post('severity');
        if (!in_array($severity, $allowed_severity)) {
            $severity = $visit->severity;
        }

        $irregularities = (bool) $this->input->post('irregularities_found');

        $data = [
            'observer_staff_id'    => (int) $this->input->post('observer_staff_id') ?: $visit->observer_staff_id,
            'visit_date'           => $this->input->post('visit_date') ?: $visit->visit_date,
            'visit_time'           => $this->input->post('visit_time') ?: $visit->visit_time,
            'hall_name'            => $this->input->post('hall_name') ?: $visit->hall_name,
            'observations'         => $this->input->post('observations') ?: $visit->observations,
            'irregularities_found' => $irregularities ? 1 : 0,
            'irregularity_details' => $this->input->post('irregularity_details'),
            'action_taken'         => $this->input->post('action_taken'),
            'severity'             => $severity,
        ];

        $this->Coe_flyingsquad_model->update($id, $data);
        $this->Coe_audit_model->log('flying_squad_edit', 'coe_flying_squad_visits', $id, null, $data);

        echo json_encode(['status' => 'success', 'msg' => 'Visit updated.']);
    }

    // ------------------------------------------------------------------
    // delete($id) — Delete visit (POST)
    // ------------------------------------------------------------------
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('coe_flyingsquad', 'can_delete')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $visit = $this->Coe_flyingsquad_model->getById($id);
        if (empty($visit)) {
            echo json_encode(['status' => 'error', 'msg' => 'Visit not found']);
            return;
        }

        $this->Coe_flyingsquad_model->delete($id);
        $this->Coe_audit_model->log('flying_squad_delete', 'coe_flying_squad_visits', $id, null, null);

        echo json_encode(['status' => 'success', 'msg' => 'Visit deleted.']);
    }
}
