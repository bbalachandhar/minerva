<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_attendance
 *
 * Real-time exam attendance tracking.
 *
 * Routes:
 *   GET  coe/coe_attendance                               → index (pick batch exam)
 *   GET  coe/coe_attendance/room/:room_id/:date/:slot     → sheet for a room
 *   POST coe/coe_attendance/save/:room_id/:date/:slot     → bulk save
 *   POST coe/coe_attendance/qr_scan                       → JSON: mark by QR hash
 */
class Coe_attendance extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_attendance_model');
    }

    // ------------------------------------------------------------------
    // INDEX — select batch exam
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_view')) {
            access_denied();
        }

        $session_id = $this->input->get('session_id') ?: $this->current_session;

        $data['title']            = 'Exam Attendance';
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = $session_id;
        $data['events']           = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_attendance/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // ROOMS — list rooms for a batch exam
    // ------------------------------------------------------------------
    public function rooms($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['title']         = 'Attendance: ' . $event->exam_group_name;
        $data['event']         = $event;
        $data['batch_exam_id'] = (int) $batch_exam_id;
        $data['rooms']         = $this->Coe_attendance_model->getRoomsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_attendance/rooms', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // SHEET — attendance sheet for a room
    // ------------------------------------------------------------------
    public function sheet($room_id, $exam_date, $session_slot)
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_view')) {
            access_denied();
        }

        // Sanitise
        $room_id      = (int) $room_id;
        $exam_date    = date('Y-m-d', strtotime($exam_date));
        $session_slot = in_array($session_slot, ['FN', 'AN']) ? $session_slot : 'FN';

        $data['title']          = 'Attendance Sheet';
        $data['room_id']        = $room_id;
        $data['exam_date']      = $exam_date;
        $data['session_slot']   = $session_slot;
        $data['batch_exam_id']  = $this->Coe_attendance_model->getBatchExamIdByRoom($room_id);
        $data['students']       = $this->Coe_attendance_model->getSeatedStudentsForRoom($room_id, $exam_date, $session_slot);
        $data['summary']        = $this->Coe_attendance_model->getSummaryByRoom($room_id, $exam_date, $session_slot);
        $data['room_info']      = $this->Coe_attendance_model->getRoomInfo($room_id, $exam_date, $session_slot);

        // Staff currently marking attendance (for audit header)
        $staff_id = $this->customlib->getStaffID();
        $sq = $this->db
            ->select("CONCAT(s.name, ' ', s.surname) AS full_name, sd.designation, s.employee_id")
            ->from('staff s')
            ->join('staff_designation sd', 'sd.id = s.designation', 'left')
            ->where('s.id', (int) $staff_id)
            ->get();
        $data['marking_staff'] = ($sq !== false) ? $sq->row() : null;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_attendance/sheet', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // SAVE — bulk attendance from form
    // ------------------------------------------------------------------
    public function save($room_id, $exam_date, $session_slot)
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_add')) {
            access_denied();
        }

        $room_id      = (int) $room_id;
        $exam_date    = date('Y-m-d', strtotime($exam_date));
        $session_slot = in_array($session_slot, ['FN', 'AN']) ? $session_slot : 'FN';

        $all_ids     = $this->input->post('all_ids');     // hidden comma-list
        $present_ids = $this->input->post('present_ids'); // checked checkboxes
        $remarks_raw = $this->input->post('remarks') ?: [];

        $all_ids     = array_filter(array_map('intval', explode(',', $all_ids ?? '')));
        $present_ids = array_filter(array_map('intval', (array) $present_ids));

        // Sanitise remarks: key = hall_ticket_id (int), value = plain text
        $remarks_map = [];
        foreach ((array) $remarks_raw as $htid => $remark) {
            $remarks_map[(int) $htid] = substr(strip_tags(trim($remark)), 0, 255);
        }

        $count = $this->Coe_attendance_model->bulkMark(
            $room_id, $exam_date, $session_slot,
            $present_ids, $all_ids, $this->customlib->getStaffID(), $remarks_map
        );

        $this->Coe_audit_model->log('attendance_saved', 'coe_exam_attendance', $room_id, null, [
            'exam_date'    => $exam_date,
            'session_slot' => $session_slot,
            'count'        => $count,
        ]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Attendance saved for ' . $count . ' students.</div>');
        redirect('coe/coe_attendance/sheet/' . $room_id . '/' . $exam_date . '/' . $session_slot);
    }

    // ------------------------------------------------------------------
    // UPLOAD_CSV — bulk attendance import from CSV file
    // POST  coe/coe_attendance/upload_csv/:room_id/:date/:slot
    // CSV columns: hall_ticket_no, present  (1/0 or P/A/Yes/No)
    // ------------------------------------------------------------------
    public function upload_csv($room_id, $exam_date, $session_slot)
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_add')) {
            access_denied();
        }

        $room_id      = (int) $room_id;
        $exam_date    = date('Y-m-d', strtotime($exam_date));
        $session_slot = in_array($session_slot, ['FN', 'AN']) ? $session_slot : 'FN';
        $back_url     = 'coe/coe_attendance/sheet/' . $room_id . '/' . $exam_date . '/' . $session_slot;

        $batch_exam_id = $this->Coe_attendance_model->getBatchExamIdByRoom($room_id);

        if (empty($_FILES['attendance_csv']['name'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">No file uploaded.</div>');
            redirect($back_url);
        }

        $ext = strtolower(pathinfo($_FILES['attendance_csv']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Only CSV files are accepted.</div>');
            redirect($back_url);
        }

        $handle = fopen($_FILES['attendance_csv']['tmp_name'], 'r');
        if ($handle === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Could not read uploaded file.</div>');
            redirect($back_url);
        }

        // Skip header row
        fgetcsv($handle);

        $marked  = 0;
        $skipped = 0;
        $errors  = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) { $skipped++; continue; }

            $ht_no   = trim($row[0]);
            $present = in_array(strtolower(trim($row[1])), ['1', 'p', 'present', 'yes', 'y']) ? 1 : 0;

            if ($ht_no === '') { $skipped++; continue; }

            $ht = $this->Coe_attendance_model->getHallTicketByNumber($ht_no, $batch_exam_id);
            if (!$ht) {
                $errors[] = htmlspecialchars($ht_no);
                $skipped++;
                continue;
            }

            $this->Coe_attendance_model->markAttendance(
                $ht->id, $room_id, $exam_date, $session_slot, $present,
                $this->customlib->getStaffID()
            );
            $marked++;
        }
        fclose($handle);

        $msg = '<div class="alert alert-success"><strong>' . $marked . '</strong> student(s) marked from CSV.';
        if ($skipped) {
            $msg .= ' <strong>' . $skipped . '</strong> row(s) skipped.';
        }
        if (!empty($errors)) {
            $shown = array_slice($errors, 0, 5);
            $msg  .= '<br><small>Unknown hall tickets: ' . implode(', ', $shown);
            if (count($errors) > 5) { $msg .= ' …and ' . (count($errors) - 5) . ' more'; }
            $msg  .= '</small>';
        }
        $msg .= '</div>';

        $this->session->set_flashdata('msg', $msg);
        redirect($back_url);
    }

    // ------------------------------------------------------------------
    // SAMPLE_CSV — download a template CSV for bulk upload
    // GET  coe/coe_attendance/sample_csv/:batch_exam_id
    // ------------------------------------------------------------------
    public function sample_csv($batch_exam_id = null)
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_view')) {
            access_denied();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="attendance_template.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['hall_ticket_no', 'present']);

        if ($batch_exam_id) {
            $tickets = $this->Coe_attendance_model->getHallTicketsByBatchExam((int) $batch_exam_id);
            foreach ($tickets as $t) {
                fputcsv($out, [$t->hall_ticket_no, '']);
            }
        } else {
            // Generic sample rows when no batch exam provided
            fputcsv($out, ['HT2026001', '1']);
            fputcsv($out, ['HT2026002', '0']);
            fputcsv($out, ['HT2026003', '1']);
        }

        fclose($out);
        exit;
    }

    // ------------------------------------------------------------------
    // QR SCAN — JSON API endpoint (called by invigilator mobile/tablet)
    // POST body: { qr_hash, room_id, exam_date, session_slot }
    // ------------------------------------------------------------------
    public function qr_scan()
    {
        if (!$this->rbac->hasPrivilege('coe_attendance', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $this->output->set_content_type('application/json');

        $raw       = file_get_contents('php://input');
        $payload   = json_decode($raw, true);

        $qr_hash      = isset($payload['qr_hash'])      ? trim($payload['qr_hash'])      : '';
        $room_id      = isset($payload['room_id'])       ? (int) $payload['room_id']       : 0;
        $exam_date    = isset($payload['exam_date'])     ? $payload['exam_date']           : date('Y-m-d');
        $session_slot = isset($payload['session_slot'])  ? $payload['session_slot']        : 'FN';

        // Validate qr_hash: hex string, length 64
        if (!preg_match('/^[0-9a-f]{64}$/', $qr_hash)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid QR hash']);
            return;
        }

        if (!$room_id) {
            echo json_encode(['status' => 'error', 'message' => 'Room ID required']);
            return;
        }

        $result = $this->Coe_attendance_model->markByQrHash(
            $qr_hash, $room_id, $exam_date, $session_slot,
            $this->customlib->getStaffID()
        );

        echo json_encode($result);
    }
}
