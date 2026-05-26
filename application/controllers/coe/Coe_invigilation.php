<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Coe_invigilation extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // INDEX — list events with invigilation summary
    // =========================================================================
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_invigilation');

        $selected_session = (int)($this->input->get('session_id') ?: $this->current_session);
        $events           = $this->Coe_invigilation_model->getCoeEvents($selected_session);
        $summaries        = [];
        foreach ($events as $ev) {
            $summaries[$ev->id] = $this->Coe_invigilation_model->getSummary($ev->id);
        }

        $sessions = $this->db->order_by('id', 'DESC')->get('sessions')->result_array();

        $data = [
            'title'       => lang('coe_invigilation'),
            'events'           => $events,
            'summaries'        => $summaries,
            'sessions'         => $sessions,
            'selected_session' => $selected_session,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_invigilation/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // MANAGE — assign duties for a batch exam (shows all rooms + duty roster)
    // =========================================================================
    public function manage($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $batch_exam    = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            show_404();
        }

        $rooms   = $this->Coe_invigilation_model->getRooms($batch_exam_id);
        $duties  = $this->Coe_invigilation_model->getDutiesByBatchExam($batch_exam_id);
        $summary = $this->Coe_invigilation_model->getSummary($batch_exam_id);
        $staff   = $this->Coe_invigilation_model->getAvailableStaff();

        $data = [
            'title'   => lang('coe_invigilation'),
            'batch_exam'   => $batch_exam,
            'rooms'        => $rooms,
            'duties'       => $duties,
            'summary'      => $summary,
            'staff'        => $staff,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_invigilation/manage', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // ASSIGN_DUTY — POST handler
    // =========================================================================
    public function assign_duty()
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_add')) {
            access_denied();
        }

        $room_id   = (int)$this->input->post('seating_room_id');
        $staff_id  = (int)$this->input->post('staff_id');
        $duty_type = $this->input->post('duty_type');
        $remarks   = $this->input->post('remarks');

        // Get batch_exam_id for redirect
        $room = $this->db->get_where('coe_seating_rooms', ['id' => $room_id])->row();
        $batch_exam_id = $room ? (int)$room->exam_group_class_batch_exam_id : 0;

        if (!$room_id || !$staff_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please select room and staff member.</div>');
            redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
        }

        $duty_id = $this->Coe_invigilation_model->assignDuty([
            'seating_room_id' => $room_id,
            'staff_id'        => $staff_id,
            'duty_type'       => $duty_type,
            'remarks'         => $remarks,
        ]);

        if ($duty_id === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">This staff member already has this duty type assigned for this room.</div>');
        } else {
            $this->Coe_audit_model->log(
                'duty_assigned',
                'coe_invigilation_duties',
                $duty_id,
                null,
                ['room_id' => $room_id, 'staff_id' => $staff_id, 'duty_type' => $duty_type]
            );
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Duty assigned successfully.</div>');
        }

        redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
    }

    // =========================================================================
    // REMOVE_DUTY — remove a duty assignment
    // =========================================================================
    public function remove_duty($duty_id)
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_delete')) {
            access_denied();
        }

        $duty_id = (int)$duty_id;
        $duty    = $this->db->get_where('coe_invigilation_duties', ['id' => $duty_id])->row();
        if (!$duty) {
            show_404();
        }

        $room = $this->db->get_where('coe_seating_rooms', ['id' => $duty->seating_room_id])->row();
        $batch_exam_id = $room ? (int)$room->exam_group_class_batch_exam_id : 0;

        $this->Coe_invigilation_model->removeDuty($duty_id);
        $this->Coe_audit_model->log('duty_removed', 'coe_invigilation_duties', $duty_id, null, null);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Duty assignment removed.</div>');
        redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
    }

    // =========================================================================
    // DOWNLOAD_SAMPLE — return a pre-filled sample CSV for bulk import
    // =========================================================================
    public function download_sample($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $rooms = $this->Coe_invigilation_model->getRooms($batch_exam_id);
        $staff = $this->Coe_invigilation_model->getAvailableStaff();

        $lines   = [];
        $lines[] = 'hall_name,exam_date,session_slot,staff_id,duty_type,remarks';
        $lines[] = '# hall_name: exact hall name | exam_date: YYYY-MM-DD | session_slot: FN or AN';
        $lines[] = '# duty_type: invigilator | chief_superintendent | deputy | flying_squad';
        $lines[] = '# staff_id: numeric ID from the staff list below';
        $lines[] = '';

        // Example rows using real rooms
        foreach ($rooms as $room) {
            $st = $staff ? $staff[0] : null;
            $lines[] = implode(',', [
                $room->hall_name,
                $room->exam_date,
                $room->session_slot,
                $st ? $st->id : '',
                'invigilator',
                '',
            ]);
        }

        $lines[] = '';
        $lines[] = '# Staff reference:';
        foreach ($staff as $st) {
            $lines[] = '# ' . $st->id . ' - ' . $st->name . ' ' . $st->surname
                . ($st->designation ? ' (' . $st->designation . ')' : '');
        }

        $csv = implode("\n", $lines);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="invigilation_import_sample.csv"');
        header('Pragma: no-cache');
        echo $csv;
        exit;
    }

    // =========================================================================
    // BULK_IMPORT — POST handler: process uploaded CSV
    // =========================================================================
    public function bulk_import($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;

        // File upload validation
        $file = $_FILES['import_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> No file uploaded or upload error.</div>');
            redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Only CSV files are accepted.</div>');
            redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
        }

        $this->load->library('CSVReader');
        $rows = $this->csvreader->parse_file($file['tmp_name']);

        if (empty($rows)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> CSV is empty or could not be parsed.</div>');
            redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
        }

        $result = $this->Coe_invigilation_model->bulkImportDuties($batch_exam_id, $rows);

        // Log to audit
        if ($result['inserted'] > 0) {
            $this->Coe_audit_model->log(
                'bulk_import_duties',
                'coe_invigilation_duties',
                $batch_exam_id,
                null,
                ['inserted' => $result['inserted'], 'skipped' => $result['skipped'], 'errors' => count($result['errors'])]
            );
        }

        $msg_parts = [];
        if ($result['inserted'] > 0) {
            $msg_parts[] = '<strong>' . $result['inserted'] . '</strong> duties imported.';
        }
        if ($result['skipped'] > 0) {
            $msg_parts[] = $result['skipped'] . ' duplicates skipped.';
        }
        if (!empty($result['errors'])) {
            $msg_parts[] = '<br><ul class="mb-0"><li>' . implode('</li><li>', array_map('htmlspecialchars', $result['errors'])) . '</li></ul>';
        }

        $alert_class = empty($result['errors']) && $result['inserted'] > 0 ? 'alert-success' : (empty($result['errors']) ? 'alert-warning' : 'alert-danger');
        $icon        = empty($result['errors']) && $result['inserted'] > 0 ? 'fa-check-circle' : 'fa-exclamation-circle';

        $this->session->set_flashdata('msg', '<div class="alert ' . $alert_class . '"><i class="fa ' . $icon . '"></i> ' . implode(' ', $msg_parts) . '</div>');
        redirect('coe/coe_invigilation/manage/' . $batch_exam_id);
    }

    // =========================================================================
    // PRINT_ROSTER — PDF duty roster for a batch exam
    // =========================================================================

    public function print_roster($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_invigilation', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $batch_exam    = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            show_404();
        }

        $duties      = $this->Coe_invigilation_model->getDutiesByBatchExam($batch_exam_id);
        $sch_setting     = $this->sch_setting_detail;
        $logo_filename   = $sch_setting->image ?? '';
        $logo_path       = null;
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

        $html = $this->load->view('admin/coe/coe_invigilation/print_roster', [
            'batch_exam'  => $batch_exam,
            'duties'      => $duties,
            'logo_path'   => $logo_path,
            'sch_setting' => $sch_setting,
        ], true);

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load(['format' => 'A4', 'margin_left' => 15, 'margin_right' => 15, 'margin_top' => 10, 'margin_bottom' => 10]);
        $mpdf->WriteHTML($html, 0);
        $mpdf->Output('DutyRoster_' . date('Ymd') . '.pdf', 'I');
    }
}
