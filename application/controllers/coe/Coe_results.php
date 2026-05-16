<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coe_results extends MY_Addon_CoeController {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_results_model');
        $this->load->model('coe/Coe_application_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }
        $data['events'] = $this->Coe_application_model->getExamEventsBySession($this->current_session);
        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_results/index', $data);
        $this->load->view('layout/footer');
    }

    public function listing($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }
        $batch_exam_id = (int) $batch_exam_id;
        $data['event']        = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        $data['batch_exam_id'] = $batch_exam_id;

        $filters = [
            'subject_id' => $this->input->get('subject_id'),
            'status'     => $this->input->get('status'),
            'has_arrear' => $this->input->get('has_arrear'),
        ];

        $data['results']      = $this->Coe_results_model->getAll($batch_exam_id, $filters);
        $data['sgpa_summary'] = $this->Coe_results_model->getSGPASummary($batch_exam_id, $filters);
        $data['subjects']     = $this->Coe_results_model->getSubjectsByBatchExam($batch_exam_id);
        $data['pub_status']   = $this->Coe_results_model->getPublicationStatus($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_results/listing', $data);
        $this->load->view('layout/footer');
    }

    public function publish($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied.']);
            return;
        }
        $batch_exam_id = (int) $batch_exam_id;
        $staff_id = $this->session->userdata('staff_id');
        $success = $this->Coe_results_model->publish($batch_exam_id, $staff_id);

        if ($success) {
            $this->Coe_audit_model->log('publish_results', 'coe_student_results', $batch_exam_id, null, null);

            // Send result notification emails asynchronously (ignore failures)
            try {
                $this->_send_results_published_email($batch_exam_id);
            } catch (Exception $e) {
                log_message('error', 'CoE results email failed: ' . $e->getMessage());
            }

            echo json_encode(['status' => 'success', 'msg' => 'Results published successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Failed to publish results.']);
        }
    }

    /**
     * Send result-published notification to each student with their SGPA summary.
     */
    private function _send_results_published_email($batch_exam_id)
    {
        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            return;
        }

        // Load students with email + their SGPA summary
        $students = $this->db
            ->select([
                'st.id AS student_id',
                "CONCAT(st.firstname, ' ', st.lastname) AS full_name",
                'st.admission_no',
                'st.email',
                'sg.sgpa',
                'sg.cgpa',
                'sg.arrear_count',
                'sg.result_status',
            ])
            ->from('students st')
            ->join('coe_sgpa_summary sg', 'sg.student_id = st.id AND sg.exam_group_class_batch_exam_id = ' . $batch_exam_id, 'inner')
            ->where('sg.is_published', 1)
            ->where('st.email IS NOT NULL', null, false)
            ->where("st.email != ''", null, false)
            ->get()->result();

        if (empty($students)) {
            return;
        }

        $this->load->library('email');
        $exam_name = htmlspecialchars($event->exam_group_name ?? $event->exam ?? 'Examination');
        $institution = htmlspecialchars($this->config->item('school_name') ?? 'Institution');
        $noreply = $this->config->item('smtp_user') ?: 'noreply@institution.edu.in';

        foreach ($students as $st) {
            if (!filter_var($st->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $sgpa       = $st->sgpa !== null ? number_format($st->sgpa, 2) : '—';
            $cgpa       = $st->cgpa !== null ? number_format($st->cgpa, 2) : '—';
            $arrears    = (int) $st->arrear_count;
            $status     = strtoupper($st->result_status ?? 'N/A');
            $status_cls = $st->result_status === 'pass' ? 'color:#27ae60' : 'color:#c0392b';

            $body = '
<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;font-size:14px;color:#333">
<p>Dear ' . htmlspecialchars($st->full_name) . ',</p>
<p>Your results for <strong>' . $exam_name . '</strong> have been published.</p>
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse">
 <tr><th style="background:#f0f0f0">Admission No</th><td>' . htmlspecialchars($st->admission_no) . '</td></tr>
 <tr><th style="background:#f0f0f0">Examination</th><td>' . $exam_name . '</td></tr>
 <tr><th style="background:#f0f0f0">SGPA</th><td>' . $sgpa . '</td></tr>
 <tr><th style="background:#f0f0f0">CGPA</th><td>' . $cgpa . '</td></tr>
 <tr><th style="background:#f0f0f0">Arrears</th><td>' . $arrears . '</td></tr>
 <tr><th style="background:#f0f0f0">Result</th><td style="' . $status_cls . '"><strong>' . $status . '</strong></td></tr>
</table>
<p style="margin-top:16px">Please login to the student portal to view detailed subject-wise results and download your grade card.</p>
<p style="color:#888;font-size:12px">This is a system-generated email from ' . $institution . '. Do not reply.</p>
</body>
</html>';

            $this->email->clear();
            $this->email->from($noreply, $institution . ' Examinations');
            $this->email->to($st->email);
            $this->email->subject('[Exam Results] ' . $exam_name . ' — ' . $institution);
            $this->email->message($body);
            $this->email->send();
        }
    }

    public function unpublish($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied.']);
            return;
        }
        $batch_exam_id = (int) $batch_exam_id;
        $this->Coe_results_model->unpublish($batch_exam_id);
        $this->Coe_audit_model->log('unpublish_results', 'coe_student_results', $batch_exam_id, null, null);
        echo json_encode(['status' => 'success', 'msg' => 'Results unpublished.']);
    }

    public function student_result($student_id = 0, $batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }
        $student_id    = (int) $student_id;
        $batch_exam_id = (int) $batch_exam_id;

        $card = $this->Coe_results_model->getStudentCard($student_id, $batch_exam_id);

        $data['results']       = $card['results'];
        $data['sgpa']          = $card['sgpa'];
        $data['batch_exam_id'] = $batch_exam_id;
        $data['event']         = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);

        // Load student details
        $this->db->select("CONCAT(s.firstname,' ',s.lastname) AS full_name, s.admission_no")
            ->from('students s')->where('s.id', $student_id);
        $data['student'] = $this->db->get()->row();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_results/student_result', $data);
        $this->load->view('layout/footer');
    }

    public function export($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }
        $batch_exam_id = (int) $batch_exam_id;
        $rows = $this->Coe_results_model->exportResultsData($batch_exam_id);
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);

        $filename = 'results_' . $batch_exam_id . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Admission No', 'Student', 'Subject Code', 'Subject', 'Internal', 'External',
                       'Moderation', 'Total', 'Grade', 'Grade Points', 'Status', 'SGPA', 'CGPA']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r->admission_no,
                $r->student_name,
                $r->subject_code,
                $r->subject_name,
                $r->internal_marks,
                $r->external_marks,
                $r->moderation_marks,
                $r->total_marks,
                $r->grade,
                $r->grade_points,
                $r->status,
                isset($r->sgpa) ? $r->sgpa : '',
                isset($r->cgpa) ? $r->cgpa : '',
            ]);
        }
        fclose($out);
        exit;
    }

    // ------------------------------------------------------------------
    // tabulation($batch_exam_id) — Anna University style tabulation sheet
    // ------------------------------------------------------------------
    public function tabulation($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $rows = $this->Coe_results_model->getTabulationData($batch_exam_id);

        // Pivot flat rows into students[student_id]['subjects'][subject_id]
        $students = [];
        $subjects  = []; // subject_id => {code, name}

        foreach ($rows as $r) {
            if (!isset($students[$r->student_id])) {
                $students[$r->student_id] = [
                    'student_id'    => $r->student_id,
                    'student_name'  => $r->student_name,
                    'register_no'   => $r->register_no,
                    'admission_no'  => $r->admission_no,
                    'sgpa'          => $r->sgpa,
                    'cgpa'          => $r->cgpa,
                    'arrear_count'  => $r->arrear_count,
                    'overall_status'=> $r->overall_status,
                    'credits_earned'=> $r->total_credits_earned,
                    'credits_reg'   => $r->total_credits_registered,
                    'subjects'      => [],
                ];
            }
            $students[$r->student_id]['subjects'][$r->subject_id] = $r;

            if (!isset($subjects[$r->subject_id])) {
                $subjects[$r->subject_id] = (object)[
                    'id'   => $r->subject_id,
                    'code' => $r->subject_code,
                    'name' => $r->subject_name,
                ];
            }
        }

        // Sort subjects by code
        uasort($subjects, function($a, $b) { return strcmp($a->code, $b->code); });

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['students']      = $students;
        $data['subjects']      = $subjects;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_results/tabulation', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // merit_list($batch_exam_id) — Rank list by SGPA
    // ------------------------------------------------------------------
    public function merit_list($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $students = $this->Coe_results_model->getMeritList($batch_exam_id);

        // Assign ranks (handle ties)
        $rank = 0;
        $prev_sgpa = null;
        $count = 0;
        foreach ($students as &$st) {
            $count++;
            if ($st->sgpa !== $prev_sgpa) {
                $rank = $count;
            }
            $st->rank = $rank;
            $prev_sgpa = $st->sgpa;
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['students']      = $students;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_results/merit_list', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // transcript($student_id) — CGPA cumulative transcript across semesters
    // ------------------------------------------------------------------
    public function transcript($student_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_results', 'can_view')) {
            access_denied();
        }

        $student_id = (int) $student_id;
        $transcript = $this->Coe_results_model->getTranscript($student_id);

        if (empty($transcript)) {
            show_404();
        }

        $data['title']      = 'CGPA Transcript';
        $data['transcript'] = $transcript;
        $data['student']    = $transcript['student'];

        if ($this->input->get('print')) {
            $this->load->library('m_pdf');
            $mpdf = $this->m_pdf->load(['format' => 'A4', 'margin_left' => 15, 'margin_right' => 15]);

            ob_start();
            $this->load->view('admin/coe/coe_results/transcript', $data);
            $html = ob_get_clean();

            $mpdf->WriteHTML($html, 0);
            $mpdf->Output('transcript_' . $transcript['student']->admission_no . '.pdf', 'I');
            exit;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_results/transcript', $data);
        $this->load->view('layout/footer');
    }
}
