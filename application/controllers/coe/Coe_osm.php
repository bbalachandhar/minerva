<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_osm
 * On-Screen Marking controller — assign evaluators, enter marks, lock.
 */
class Coe_osm extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_osm_model');
        $this->load->model('coe/Coe_answer_scripts_model');
        $this->load->model('coe/Coe_application_model');
    }

    // ------------------------------------------------------------------
    // index() — Pick exam event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_view')) {
            access_denied();
        }

        $session_id         = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']     = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_osm/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // dashboard($batch_exam_id) — List all OSM scripts for an event
    // ------------------------------------------------------------------
    public function dashboard($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = [
            'batch_exam_id' => $batch_exam_id,
            'status'        => $this->input->get('status'),
            'subject_id'    => $this->input->get('subject_id'),
        ];

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['osm_scripts']   = $this->Coe_osm_model->getAll($filters);
        $data['counts']        = $this->Coe_osm_model->countByStatus($batch_exam_id);
        $data['subjects']      = $this->Coe_answer_scripts_model->getSubjectsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_osm/dashboard', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // create_from_scripts($batch_exam_id) — Bulk-create OSM records from uploaded scripts
    // ------------------------------------------------------------------
    public function create_from_scripts($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        // Find all uploaded answer scripts for this batch exam that don't have OSM records yet
        $this->db
            ->select('ans.id')
            ->from('coe_answer_scripts ans')
            ->join('coe_osm_scripts osm', 'osm.answer_script_id = ans.id', 'left')
            ->where('ans.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('ans.scan_status', 'uploaded')
            ->where('osm.id IS NULL', null, false);

        $unprocessed = $this->db->get()->result();
        $created = 0;

        foreach ($unprocessed as $ans) {
            $this->Coe_osm_model->insertScript([
                'answer_script_id' => $ans->id,
                'stage'            => 1,
                'status'           => 'pending',
            ]);
            $created++;
        }

        $this->Coe_audit_model->log('bulk_create_osm', 'coe_osm_scripts', null, null, ['batch_exam_id' => $batch_exam_id, 'created' => $created]);
        echo json_encode(['status' => 'success', 'msg' => "$created OSM script(s) created."]);
    }

    // ------------------------------------------------------------------
    // assign($id) — Assign evaluator (AJAX POST)
    // ------------------------------------------------------------------
    public function assign($id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $script = $this->Coe_osm_model->getById($id);
        if (empty($script)) {
            echo json_encode(['status' => 'error', 'msg' => 'Script not found']);
            return;
        }

        $this->form_validation->set_rules('evaluator_id', 'Evaluator', 'required|integer');
        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $upd = [
            'assigned_evaluator' => (int) $this->input->post('evaluator_id'),
            'status'             => 'assigned',
            'assigned_at'        => date('Y-m-d H:i:s'),
        ];

        $this->Coe_osm_model->updateScript($id, $upd);
        $this->Coe_audit_model->log('assign_evaluator', 'coe_osm_scripts', $id, null, $upd);

        echo json_encode(['status' => 'success', 'msg' => 'Evaluator assigned.']);
    }

    // ------------------------------------------------------------------
    // mark($id) — Marking interface
    // ------------------------------------------------------------------
    public function mark($id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_edit')) {
            access_denied();
        }

        $script = $this->Coe_osm_model->getById($id);
        if (empty($script) || in_array($script->status, ['locked'])) {
            show_404();
        }

        $data['script']       = $script;
        $data['existing_marks'] = $this->Coe_osm_model->getMarks($id);

        // Mark status as 'marking' if it's assigned
        if ($script->status === 'assigned') {
            $this->Coe_osm_model->updateScript($id, ['status' => 'marking']);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_osm/mark', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_marks($id) — Save question-wise marks (AJAX POST)
    // ------------------------------------------------------------------
    public function save_marks($id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $script = $this->Coe_osm_model->getById($id);
        if (empty($script) || $script->status === 'locked') {
            echo json_encode(['status' => 'error', 'msg' => 'Script not found or locked']);
            return;
        }

        // Expect: marks[question_no][sub_question] = value, max_marks[...] = value
        $marks_post    = $this->input->post('marks')     ?: [];
        $max_post      = $this->input->post('max_marks') ?: [];
        $staff_id      = (int) $this->session->userdata('staff_id');

        if (empty($marks_post)) {
            echo json_encode(['status' => 'error', 'msg' => 'No marks submitted']);
            return;
        }

        foreach ($marks_post as $qno => $sub_arr) {
            foreach ($sub_arr as $sub => $awarded) {
                $max = isset($max_post[$qno][$sub]) ? (float) $max_post[$qno][$sub] : 0;
                $awarded = max(0, min((float) $awarded, $max));  // clamp to [0, max]
                $this->Coe_osm_model->saveMark($id, (int) $qno, $sub === '_' ? null : $sub, $awarded, $max, $staff_id);
            }
        }

        $total = $this->Coe_osm_model->computeTotal($id);
        $this->Coe_audit_model->log('save_marks', 'coe_osm_scripts', $id, null, ['total' => $total]);

        echo json_encode(['status' => 'success', 'msg' => 'Marks saved. Total: ' . $total]);
    }

    // ------------------------------------------------------------------
    // submit($id) — Mark as done (AJAX POST)
    // ------------------------------------------------------------------
    public function submit($id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $script = $this->Coe_osm_model->getById($id);
        if (empty($script)) {
            echo json_encode(['status' => 'error', 'msg' => 'Script not found']);
            return;
        }

        // Recompute total before locking
        $total = $this->Coe_osm_model->computeTotal($id);

        $upd = [
            'status'       => 'done',
            'submitted_at' => date('Y-m-d H:i:s'),
            'total_marks'  => $total,
        ];
        $this->Coe_osm_model->updateScript($id, $upd);
        $this->Coe_audit_model->log('submit_marking', 'coe_osm_scripts', $id, null, $upd);

        echo json_encode(['status' => 'success', 'msg' => 'Marking submitted. Total: ' . $total]);
    }

    // ------------------------------------------------------------------
    // lock($id) — Lock script (CoE admin action, AJAX POST)
    // ------------------------------------------------------------------
    public function lock($id)
    {
        if (!$this->rbac->hasPrivilege('coe_osm', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $script = $this->Coe_osm_model->getById($id);
        if (empty($script) || $script->status !== 'done') {
            echo json_encode(['status' => 'error', 'msg' => 'Script must be "done" before locking']);
            return;
        }

        $upd = [
            'status'    => 'locked',
            'locked_by' => (int) $this->session->userdata('staff_id'),
            'locked_at' => date('Y-m-d H:i:s'),
        ];
        $this->Coe_osm_model->updateScript($id, $upd);
        $this->Coe_audit_model->log('lock_script', 'coe_osm_scripts', $id, null, $upd);

        echo json_encode(['status' => 'success', 'msg' => 'Script locked.']);
    }
}
