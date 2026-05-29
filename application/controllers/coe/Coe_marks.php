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

    // ------------------------------------------------------------------
    // student_card_ajax($student_id, $batch_exam_id) — Modal AJAX version
    // ------------------------------------------------------------------
    public function student_card_ajax($student_id, $batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_view')) {
            http_response_code(403);
            echo '<p class="text-danger">Access denied.</p>';
            return;
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

        $this->output->set_content_type('text/html');
        echo $this->load->view('admin/coe/coe_marks/_student_card_partial', $data, TRUE);
    }

    // ------------------------------------------------------------------
    // import($batch_exam_id) — CSV import of marks
    // GET:  show import form + download sample template
    // POST: process uploaded CSV
    // CSV format: admission_no,subject_code,internal_marks,external_marks
    // ------------------------------------------------------------------
    public function import($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // Handle file upload
            $config = [
                'upload_path'   => sys_get_temp_dir(),
                'allowed_types' => 'csv',
                'max_size'      => 2048,
            ];
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('marks_csv')) {
                $data['error'] = $this->upload->display_errors('', '');
            } else {
                $file_path = $this->upload->data('full_path');
                $result    = $this->_processMarksCSV($batch_exam_id, $file_path);
                @unlink($file_path);

                $this->Coe_audit_model->log('import_marks_csv', 'coe_student_results', $batch_exam_id, null,
                    ['imported' => $result['imported'], 'errors' => count($result['errors'])]);

                $data['import_result'] = $result;
            }
        }

        $data['title']         = 'Import Marks from CSV';
        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_marks/import', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // import_template($batch_exam_id) — Download sample CSV for this event
    // ------------------------------------------------------------------
    public function import_template($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_marks', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $subjects = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);
        $students = $this->Coe_marks_model->getStudentsByBatchExam($batch_exam_id);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="marks_import_template_' . $batch_exam_id . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['admission_no', 'subject_code', 'internal_marks', 'external_marks']);

        foreach ($students as $st) {
            foreach ($subjects as $sub) {
                fputcsv($out, [$st->admission_no, $sub->subject_code, '', '']);
            }
        }

        fclose($out);
        exit;
    }

    /**
     * Internal CSV processor — returns ['imported' => N, 'errors' => [...]]
     */
    private function _processMarksCSV($batch_exam_id, $file_path)
    {
        $imported = 0;
        $errors   = [];

        // Build lookup: admission_no → student_id
        $students = $this->Coe_marks_model->getStudentsByBatchExam($batch_exam_id);
        $student_map = [];
        foreach ($students as $st) {
            $student_map[strtolower(trim($st->admission_no))] = $st->id;
        }

        // Build lookup: subject_code → subject_id
        $subjects = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);
        $subject_map = [];
        foreach ($subjects as $sub) {
            $subject_map[strtolower(trim($sub->subject_code))] = $sub->subject_id;
        }

        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return ['imported' => 0, 'errors' => ['Could not read uploaded file.']];
        }

        $row_num = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $row_num++;
            if ($row_num === 1) {
                // Skip header row
                continue;
            }
            if (count($row) < 4) {
                $errors[] = "Row $row_num: insufficient columns (expected 4).";
                continue;
            }

            [$adm_no, $sub_code, $internal_raw, $external_raw] = $row;
            $adm_no   = strtolower(trim($adm_no));
            $sub_code = strtolower(trim($sub_code));

            if (!isset($student_map[$adm_no])) {
                $errors[] = "Row $row_num: Admission No '" . htmlspecialchars($adm_no) . "' not found in this exam event.";
                continue;
            }

            if (!isset($subject_map[$sub_code])) {
                $errors[] = "Row $row_num: Subject code '" . htmlspecialchars($sub_code) . "' not found in this exam event.";
                continue;
            }

            if (!is_numeric($internal_raw) || !is_numeric($external_raw)) {
                $errors[] = "Row $row_num: Marks must be numeric (got '$internal_raw', '$external_raw').";
                continue;
            }

            $internal  = (float) $internal_raw;
            $external  = (float) $external_raw;
            $student_id = $student_map[$adm_no];
            $subject_id = $subject_map[$sub_code];

            $this->Coe_marks_model->saveResult($batch_exam_id, $student_id, $subject_id, $internal, $external);
            $imported++;
        }

        fclose($handle);
        return ['imported' => $imported, 'errors' => $errors];
    }
}
