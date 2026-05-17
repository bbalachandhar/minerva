<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Coe_hallticket extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // INDEX — list all CoE-locked exam events with generation status
    // =========================================================================
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_hallticket', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_hallticket');

        $selected_session = (int)($this->input->get('session_id') ?: $this->current_session);

        $events    = $this->Coe_hallticket_model->getCoeEvents($selected_session);
        $summaries = [];
        foreach ($events as $ev) {
            $summaries[$ev->id] = $this->Coe_hallticket_model->getSummaryByBatchExam($ev->id);
        }

        // All sessions for filter dropdown
        $sessions = $this->db->order_by('id', 'DESC')->get('sessions')->result_array();

        $data = [
            'title'       => lang('coe_hallticket'),
            'events'           => $events,
            'summaries'        => $summaries,
            'sessions'         => $sessions,
            'selected_session' => $selected_session,
            'sch_setting'      => $this->sch_setting_detail,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_hallticket/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // GENERATE — generate hall tickets for all eligible students of a batch exam
    // =========================================================================
    public function generate($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_hallticket', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $staff_id      = (int)$this->session->userdata('staff_id');

        $result = $this->Coe_hallticket_model->generate($batch_exam_id, $staff_id);

        $this->Coe_audit_model->log(
            'halltickets_generated',
            'coe_hall_tickets',
            $batch_exam_id,
            null,
            $result
        );

        if (isset($result['error'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $result['error'] . '</div>');
        } else {
            $msg = 'Generated <strong>' . $result['generated'] . '</strong> hall ticket(s).';
            if ($result['skipped'] > 0) {
                $msg .= ' Skipped <strong>' . $result['skipped'] . '</strong> (already generated).';
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $msg . '</div>');
        }

        redirect('coe/coe_hallticket/view/' . $batch_exam_id);
    }

    // =========================================================================
    // VIEW — list all hall tickets for a batch exam (DataTable)
    // =========================================================================
    public function view($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_hallticket', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;

        $batch_exam = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            show_404();
        }

        $hall_tickets = $this->Coe_hallticket_model->getByBatchExam($batch_exam_id);
        $summary      = $this->Coe_hallticket_model->getSummaryByBatchExam($batch_exam_id);

        $data = [
            'title'   => lang('coe_hallticket'),
            'batch_exam'   => $batch_exam,
            'hall_tickets' => $hall_tickets,
            'summary'      => $summary,
            'sch_setting'  => $this->sch_setting_detail,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_hallticket/view', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // PRINT_PDF — generate a single hall ticket PDF inline in browser
    // =========================================================================
    public function print_pdf($hall_ticket_id)
    {
        if (!$this->rbac->hasPrivilege('coe_hallticket', 'can_view')) {
            access_denied();
        }

        $hall_ticket_id = (int)$hall_ticket_id;
        $ht = $this->Coe_hallticket_model->getById($hall_ticket_id);
        if (!$ht) {
            show_404();
        }

        $subjects = $this->Coe_hallticket_model->getSubjectsForStudent(
            $ht->student_id,
            $ht->exam_group_class_batch_exam_id
        );

        // Generate QR code (data URI)
        require_once APPPATH . 'third_party/vendor/autoload.php';
        $qr_options = new \chillerlan\QRCode\QROptions([
            'outputType'  => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'scale'       => 5,
            'imageBase64' => true,
        ]);
        $qr_data    = base_url('verify/' . $ht->qr_hash);
        $qr_img     = (new \chillerlan\QRCode\QRCode($qr_options))->render($qr_data);

        // Student photo path (absolute for mPDF)
        $student_photo = FCPATH . 'uploads/student_images/' . ($ht->student_image ?: '');
        if (!$ht->student_image || !is_file($student_photo)) {
            $student_photo = FCPATH . 'uploads/no_image/no_image.jpg';
        }

        // School logo path (absolute for mPDF)
        $logo_path = FCPATH . 'uploads/logos/' . ($this->sch_setting_detail->admission_logo_left ?? '');
        if (!($this->sch_setting_detail->admission_logo_left ?? '') || !is_file($logo_path)) {
            $logo_path = null;
        }

        // Increment download counter
        $this->Coe_hallticket_model->incrementDownload($hall_ticket_id);

        // Render HTML
        $view_data = [
            'ht'           => $ht,
            'subjects'     => $subjects,
            'qr_img'       => $qr_img,
            'student_photo'=> $student_photo,
            'logo_path'    => $logo_path,
            'sch_setting'  => $this->sch_setting_detail,
        ];
        $html = $this->load->view('admin/coe/coe_hallticket/print', $view_data, true);

        // mPDF output
        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load([
            'format'        => 'A4',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 8,
            'margin_bottom' => 8,
        ]);
        $mpdf->WriteHTML($html, 0);
        $mpdf->Output('HallTicket_' . $ht->hall_ticket_no . '.pdf', 'I');
    }

    // =========================================================================
    // PRINT_ALL — generate a combined PDF for all hall tickets of a batch exam
    // =========================================================================
    public function print_all($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_hallticket', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $hall_tickets  = $this->Coe_hallticket_model->getByBatchExam($batch_exam_id);
        if (empty($hall_tickets)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">No hall tickets generated yet.</div>');
            redirect('coe/coe_hallticket/view/' . $batch_exam_id);
            return;
        }

        require_once APPPATH . 'third_party/vendor/autoload.php';
        $qr_options = new \chillerlan\QRCode\QROptions([
            'outputType'  => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
            'scale'       => 5,
            'imageBase64' => true,
        ]);
        $qrEngine = new \chillerlan\QRCode\QRCode($qr_options);

        $logo_path = FCPATH . 'uploads/logos/' . ($this->sch_setting_detail->admission_logo_left ?? '');
        if (!($this->sch_setting_detail->admission_logo_left ?? '') || !is_file($logo_path)) {
            $logo_path = null;
        }

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load([
            'format'        => 'A4',
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 8,
            'margin_bottom' => 8,
        ]);
        $page_num = 0;
        foreach ($hall_tickets as $ht) {
            // Get full hall ticket data (with exam/session info)
            $full_ht  = $this->Coe_hallticket_model->getById($ht->id);
            $subjects = $this->Coe_hallticket_model->getSubjectsForStudent(
                $ht->student_id,
                $ht->exam_group_class_batch_exam_id
            );

            $qr_data = base_url('verify/' . $ht->qr_hash);
            $qr_img  = $qrEngine->render($qr_data);

            $student_photo = FCPATH . 'uploads/student_images/' . ($ht->student_image ?: '');
            if (!$ht->student_image || !is_file($student_photo)) {
                $student_photo = FCPATH . 'uploads/no_image/no_image.jpg';
            }

            if ($page_num > 0) {
                $mpdf->AddPage();
            }

            $html = $this->load->view('admin/coe/coe_hallticket/print', [
                'ht'            => $full_ht,
                'subjects'      => $subjects,
                'qr_img'        => $qr_img,
                'student_photo' => $student_photo,
                'logo_path'     => $logo_path,
                'sch_setting'   => $this->sch_setting_detail,
            ], true);

            $mpdf->WriteHTML($html, 0);
            $this->Coe_hallticket_model->incrementDownload($ht->id);
            $page_num++;
        }

        $batch_exam = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        $filename   = 'HallTickets_' . preg_replace('/[^A-Za-z0-9_-]/', '_', ($batch_exam->exam ?? 'batch')) . '.pdf';
        $mpdf->Output($filename, 'I');
    }

    // =========================================================================
    // INVALIDATE — mark a hall ticket as invalid (POST only)
    // =========================================================================
    public function invalidate($hall_ticket_id)
    {
        if (!$this->rbac->hasPrivilege('coe_hallticket', 'can_edit')) {
            access_denied();
        }

        $hall_ticket_id = (int)$hall_ticket_id;
        $ht = $this->Coe_hallticket_model->getById($hall_ticket_id);
        if (!$ht) {
            show_404();
        }

        $this->Coe_hallticket_model->invalidate($hall_ticket_id);
        $this->Coe_audit_model->log(
            'hallticket_invalidated',
            'coe_hall_tickets',
            $hall_ticket_id,
            ['is_valid' => 1],
            ['is_valid' => 0]
        );

        $this->session->set_flashdata('msg', '<div class="alert alert-warning">Hall ticket <strong>' . $ht->hall_ticket_no . '</strong> has been invalidated.</div>');
        redirect('coe/coe_hallticket/view/' . $ht->exam_group_class_batch_exam_id);
    }
}
