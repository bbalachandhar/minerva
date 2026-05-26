<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Coe_schedule extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_schedule_model');
        $this->load->model('coe/Coe_application_model');
        $this->load->model('coe/Coe_marks_model');
    }

    // ------------------------------------------------------------------
    // index() — List exam events to pick one
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_schedule', 'can_view')) {
            access_denied();
        }

        $session_id         = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']     = $this->Coe_application_model->getExamEventsBySession($session_id);
        $data['sessions']   = $this->db->order_by('id', 'DESC')->get('sessions')->result_array();
        $data['title']      = 'Exam Subject Schedule';

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_schedule/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // manage($batch_exam_id) — View + edit the schedule for an event
    // ------------------------------------------------------------------
    public function manage($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_schedule', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $schedule = $this->Coe_schedule_model->getSchedule($batch_exam_id);
        // Index by subject_id for quick lookup
        $schedule_idx = [];
        foreach ($schedule as $s) {
            $schedule_idx[$s->subject_id] = $s;
        }

        $data['title']         = 'Manage Exam Schedule';
        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_marks_model->getSubjectsByBatchExam($batch_exam_id);
        $data['schedule_idx']  = $schedule_idx;
        $data['halls']         = $this->Coe_schedule_model->getHalls();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_schedule/manage', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_schedule() — Bulk upsert schedule rows (AJAX POST)
    // ------------------------------------------------------------------
    public function save_schedule()
    {
        if (!$this->rbac->hasPrivilege('coe_schedule', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            echo json_encode(['status' => 'error', 'msg' => 'POST required']);
            return;
        }

        $batch_exam_id = (int) $this->input->post('batch_exam_id');
        $rows          = $this->input->post('schedule'); // schedule[subject_id][...]

        if (empty($batch_exam_id) || empty($rows)) {
            echo json_encode(['status' => 'error', 'msg' => 'No data submitted']);
            return;
        }

        $saved = 0;
        foreach ($rows as $subject_id => $vals) {
            $this->Coe_schedule_model->saveRow($batch_exam_id, $subject_id, $vals);
            $saved++;
        }

        $this->Coe_audit_model->log('save_schedule', 'coe_exam_schedule', $batch_exam_id, null,
            ['batch_exam_id' => $batch_exam_id, 'rows' => $saved]);

        echo json_encode(['status' => 'success', 'msg' => "Schedule saved for $saved subject(s)."]);
    }

    // ------------------------------------------------------------------
    // print_pdf($batch_exam_id) — Generate schedule PDF
    // ------------------------------------------------------------------
    public function print_pdf($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_schedule', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $schedule = $this->Coe_schedule_model->getSchedule($batch_exam_id);

        // Build logo base64
        $sch_setting   = $this->sch_setting_detail;
        $logo_filename = $sch_setting->image ?? '';
        $logo_path     = null;
        if ($logo_filename) {
            $full = FCPATH . 'uploads/school_content/logo/' . $logo_filename;
            if (is_file($full)) {
                $mime = mime_content_type($full) ?: 'image/png';
                if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                    $img      = imagecreatefromwebp($full);
                    ob_start();
                    imagepng($img);
                    $raw      = ob_get_clean();
                    imagedestroy($img);
                    $logo_path = 'data:image/png;base64,' . base64_encode($raw);
                } else {
                    $logo_path = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
                }
            }
        }

        $html = $this->load->view('admin/coe/coe_schedule/print_pdf', [
            'event'        => $event,
            'schedule'     => $schedule,
            'sch_setting'  => $sch_setting,
            'logo_path'    => $logo_path,
        ], true);

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load([
            'format'        => 'A4-L',
            'margin_left'   => 15,
            'margin_right'  => 15,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);
        $mpdf->WriteHTML($html, 0);
        $filename = 'ExamSchedule_' . preg_replace('/[^A-Za-z0-9_]/', '_', $event->exam ?? $batch_exam_id) . '.pdf';
        $mpdf->Output($filename, 'I');
    }

    // ------------------------------------------------------------------
    // delete_row($id) — Delete a schedule entry (POST)
    // ------------------------------------------------------------------
    public function delete_row($id)
    {
        if (!$this->rbac->hasPrivilege('coe_schedule', 'can_delete')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $row = $this->Coe_schedule_model->getById($id);
        if (empty($row)) {
            echo json_encode(['status' => 'error', 'msg' => 'Row not found']);
            return;
        }

        $this->Coe_schedule_model->delete($id);
        $this->Coe_audit_model->log('delete_schedule_row', 'coe_exam_schedule', $id, null, null);
        echo json_encode(['status' => 'success', 'msg' => 'Deleted.']);
    }
}
