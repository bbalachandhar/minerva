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

        $session_id = $this->input->get('session_id') ?: $this->current_session['id'];

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

        $data['title']        = 'Attendance Sheet';
        $data['room_id']      = $room_id;
        $data['exam_date']    = $exam_date;
        $data['session_slot'] = $session_slot;
        $data['students']     = $this->Coe_attendance_model->getSeatedStudentsForRoom($room_id, $exam_date, $session_slot);
        $data['summary']      = $this->Coe_attendance_model->getSummaryByRoom($room_id, $exam_date, $session_slot);

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

        $all_ids     = array_filter(array_map('intval', explode(',', $all_ids ?? '')));
        $present_ids = array_filter(array_map('intval', (array) $present_ids));

        $count = $this->Coe_attendance_model->bulkMark(
            $room_id, $exam_date, $session_slot,
            $present_ids, $all_ids, $this->customlib->getStaffID()
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
