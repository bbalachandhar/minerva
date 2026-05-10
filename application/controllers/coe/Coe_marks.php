<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_marks
 * Marks entry, subject config, SGPA/CGPA computation.
 */
class Coe_marks extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_marks_model');
        $this->load->model('coe/Coe_application_model');
    }

    // ------------------------------------------------------------------
    // index() — Pick exam event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_view')) {
            access_denied();
        }

        $session_id         = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']     = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_marks/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // listing($batch_exam_id) — View results summary
    // ------------------------------------------------------------------
    public function listing($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = [
            'subject_id' => $this->input->get('subject_id'),
            'status'     => $this->input->get('status'),
        ];

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['results']       = $this->Coe_marks_model->getResults($batch_exam_id, $filters);
        $data['subjects']      = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);
        $data['sgpa_summary']  = $this->Coe_marks_model->getSGPASummary($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_marks/listing', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // enter($batch_exam_id) — Bulk marks entry form
    // ------------------------------------------------------------------
    public function enter($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);
        $data['students']      = $this->Coe_marks_model->getStudentsByBatchExam($batch_exam_id);
        $data['configs']       = $this->Coe_marks_model->getSubjectConfigs($batch_exam_id);

        // Index existing results by student_id + subject_id for pre-fill
        $raw_results = $this->Coe_marks_model->getResults($batch_exam_id);
        $data['results_idx'] = [];
        foreach ($raw_results as $r) {
            $data['results_idx'][$r->student_id][$r->subject_id] = $r;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_marks/enter', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_marks() — Save marks (AJAX POST, can be called multiple times)
    // ------------------------------------------------------------------
    public function save_marks()
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $batch_exam_id = (int) $this->input->post('batch_exam_id');
        $marks_data    = $this->input->post('marks'); // marks[student_id][subject_id][internal|external]

        if (empty($batch_exam_id) || empty($marks_data)) {
            echo json_encode(['status' => 'error', 'msg' => 'No data submitted']);
            return;
        }

        $saved = 0;
        foreach ($marks_data as $student_id => $subjects) {
            foreach ($subjects as $subject_id => $vals) {
                $internal = isset($vals['internal']) ? (float) $vals['internal'] : 0;
                $external = isset($vals['external']) ? (float) $vals['external'] : 0;
                $this->Coe_marks_model->saveResult($batch_exam_id, $student_id, $subject_id, $internal, $external);
                $saved++;
            }
        }

        $this->Coe_audit_model->log('save_marks', 'coe_student_results', null, null,
            ['batch_exam_id' => $batch_exam_id, 'rows_saved' => $saved]);

        echo json_encode(['status' => 'success', 'msg' => "$saved mark(s) saved."]);
    }

    // ------------------------------------------------------------------
    // configure_subjects($batch_exam_id) — Subject config form
    // ------------------------------------------------------------------
    public function configure_subjects($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_edit')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);
        $data['configs']       = $this->Coe_marks_model->getSubjectConfigs($batch_exam_id);
        // Index configs
        $data['configs_idx'] = [];
        foreach ($data['configs'] as $c) {
            $data['configs_idx'][$c->subject_id] = $c;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_marks/configure', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_config() — Save subject configs (AJAX POST)
    // ------------------------------------------------------------------
    public function save_config()
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $batch_exam_id = (int) $this->input->post('batch_exam_id');
        $config_data   = $this->input->post('config'); // config[subject_id][credits|max_internal|...]

        if (empty($batch_exam_id) || empty($config_data)) {
            echo json_encode(['status' => 'error', 'msg' => 'No data submitted']);
            return;
        }

        $saved = 0;
        foreach ($config_data as $subject_id => $vals) {
            $this->Coe_marks_model->saveSubjectConfig(
                $batch_exam_id, $subject_id,
                $vals['credits']       ?? 4,
                $vals['max_internal']  ?? 30,
                $vals['max_external']  ?? 70,
                $vals['pass_internal'] ?? 12,
                $vals['pass_external'] ?? 28
            );
            $saved++;
        }

        echo json_encode(['status' => 'success', 'msg' => "Config saved for $saved subject(s)."]);
    }

    // ------------------------------------------------------------------
    // compute_sgpa($batch_exam_id) — Bulk SGPA computation (AJAX POST)
    // ------------------------------------------------------------------
    public function compute_sgpa($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $count = $this->Coe_marks_model->bulkComputeSGPA($batch_exam_id);
        $this->Coe_audit_model->log('compute_sgpa', 'coe_sgpa_summary', null, null,
            ['batch_exam_id' => $batch_exam_id, 'students' => $count]);

        echo json_encode(['status' => 'success', 'msg' => "SGPA/CGPA computed for $count student(s)."]);
    }

    // ------------------------------------------------------------------
    // recompute_grades($batch_exam_id) — Recalculate grades (AJAX POST)
    // ------------------------------------------------------------------
    public function recompute_grades($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $count = $this->Coe_marks_model->recomputeGrades($batch_exam_id);
        echo json_encode(['status' => 'success', 'msg' => "Grades recomputed for $count result(s)."]);
    }

    // ------------------------------------------------------------------
    // student_card($student_id, $batch_exam_id)
    // ------------------------------------------------------------------
    public function student_card($student_id, $batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_view')) {
            access_denied();
        }

        $card  = $this->Coe_marks_model->getStudentCard($student_id, $batch_exam_id);
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);

        $student = $this->db
            ->select('id, CONCAT(firstname, " ", lastname) AS full_name, admission_no')
            ->where('id', (int) $student_id)
            ->get('students')->row();

        $data['student']       = $student;
        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['results']       = $card['results'];
        $data['sgpa']          = $card['sgpa'];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_marks/student_card', $data);
        $this->load->view('layout/footer');
    }
}
