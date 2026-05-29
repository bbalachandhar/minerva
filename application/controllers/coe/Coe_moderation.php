<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_moderation
 * Grace / moderation / normalisation — manage rules and apply to student results.
 */
class Coe_moderation extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_moderation_model');
        $this->load->model('coe/Coe_application_model');
    }

    // ------------------------------------------------------------------
    // index() — Pick exam event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_view')) {
            access_denied();
        }

        $session_id         = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']     = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_moderation/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // listing($batch_exam_id)
    // ------------------------------------------------------------------
    public function listing($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['rules']         = $this->Coe_moderation_model->getAll($batch_exam_id);
        $data['subjects']      = $this->Coe_moderation_model->getSubjectsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_moderation/listing', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_rule() — Add new rule (AJAX POST)
    // ------------------------------------------------------------------
    public function save_rule()
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $this->form_validation->set_rules('batch_exam_id', 'Exam Event',  'required|integer');
        $this->form_validation->set_rules('rule_type',     'Rule Type',   'required|in_list[grace,moderation,normalisation,scaling]');
        $this->form_validation->set_rules('value_type',    'Value Type',  'required|in_list[flat,percentage]');
        $this->form_validation->set_rules('applies_to',    'Apply To',    'required|in_list[external,internal,total]');
        $this->form_validation->set_rules('value',         'Value',       'required|numeric');
        $this->form_validation->set_rules('description',   'Description', 'required');

        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $row = [
            'exam_group_class_batch_exam_id' => (int) $this->input->post('batch_exam_id'),
            'subject_id'   => $this->input->post('subject_id') ?: null,
            'rule_type'    => $this->input->post('rule_type'),
            'value_type'   => $this->input->post('value_type'),
            'applies_to'   => $this->input->post('applies_to'),
            'value'        => (float) $this->input->post('value'),
            'reason'       => $this->input->post('description'),
            'is_applied'   => 0,
            'created_by'   => (int) $this->session->userdata('staff_id'),
        ];

        $id = $this->Coe_moderation_model->insert($row);
        $this->Coe_audit_model->log('add_rule', 'coe_moderation_rules', $id, null, $row);

        echo json_encode(['status' => 'success', 'msg' => 'Rule added. ID: ' . $id]);
    }

    // ------------------------------------------------------------------
    // preview($batch_exam_id) — Show before/after table (JSON)
    // ------------------------------------------------------------------
    public function preview($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_view')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $preview = $this->Coe_moderation_model->preview($batch_exam_id);
        echo json_encode(['status' => 'success', 'data' => $preview]);
    }

    // ------------------------------------------------------------------
    // apply_single($id) — Apply one rule by ID (AJAX POST, irreversible)
    // ------------------------------------------------------------------
    public function apply_single($id)
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $rule = $this->Coe_moderation_model->getById((int) $id);
        if (empty($rule)) {
            echo json_encode(['status' => 'error', 'msg' => 'Rule not found']);
            return;
        }
        if ($rule->is_applied) {
            echo json_encode(['status' => 'error', 'msg' => 'Rule already applied']);
            return;
        }

        $affected = $this->Coe_moderation_model->applyRule((int) $id);
        $this->Coe_audit_model->log('apply_single_rule', 'coe_moderation_rules', (int) $id, null,
            ['rule_id' => $id, 'students_affected' => $affected]);

        echo json_encode(['status' => 'success', 'msg' => 'Rule applied. ' . $affected . ' student result(s) updated.']);
    }

    // ------------------------------------------------------------------
    // apply($batch_exam_id) — Apply all unapplied rules (AJAX POST, irreversible)
    // ------------------------------------------------------------------
    public function apply($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $applied = $this->Coe_moderation_model->applyRules($batch_exam_id);
        $this->Coe_audit_model->log('apply_rules', 'coe_moderation_rules', null, null,
            ['batch_exam_id' => $batch_exam_id, 'rules_applied' => $applied]);

        echo json_encode(['status' => 'success', 'msg' => "$applied rule(s) applied to student results."]);
    }

    // ------------------------------------------------------------------
    // delete($id) — Delete unapplied rule (AJAX POST)
    // ------------------------------------------------------------------
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('coe_moderation', 'can_delete')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $rule = $this->Coe_moderation_model->getById($id);
        if (empty($rule)) {
            echo json_encode(['status' => 'error', 'msg' => 'Rule not found']);
            return;
        }
        if ($rule->is_applied) {
            echo json_encode(['status' => 'error', 'msg' => 'Cannot delete an applied rule']);
            return;
        }

        $ok = $this->Coe_moderation_model->delete($id);
        $this->Coe_audit_model->log('delete_rule', 'coe_moderation_rules', $id, null, null);
        echo json_encode(['status' => $ok ? 'success' : 'error', 'msg' => $ok ? 'Rule deleted.' : 'Delete failed.']);
    }
}
