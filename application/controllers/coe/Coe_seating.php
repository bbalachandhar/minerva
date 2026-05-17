<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Coe_seating extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // INDEX — list CoE events with seating summary
    // =========================================================================
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_seating');

        $selected_session = (int)($this->input->get('session_id') ?: $this->current_session);
        $events           = $this->Coe_seating_model->getCoeEvents($selected_session);
        $summaries        = [];
        foreach ($events as $ev) {
            $summaries[$ev->id] = $this->Coe_seating_model->getSummary($ev->id);
        }

        $sessions = $this->db->order_by('id', 'DESC')->get('sessions')->result_array();

        $data = [
            'title'       => lang('coe_seating'),
            'events'           => $events,
            'summaries'        => $summaries,
            'sessions'         => $sessions,
            'selected_session' => $selected_session,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_seating/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // MANAGE — view and manage rooms for a batch exam
    // =========================================================================
    public function manage($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $batch_exam    = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            show_404();
        }

        $rooms    = $this->Coe_seating_model->getRooms($batch_exam_id);
        $summary  = $this->Coe_seating_model->getSummary($batch_exam_id);
        $halls    = $this->Coe_seating_model->getHalls();
        $subjects = $this->Coe_seating_model->getSubjects($batch_exam_id);

        $data = [
            'title'  => lang('coe_seating'),
            'batch_exam'  => $batch_exam,
            'rooms'       => $rooms,
            'summary'     => $summary,
            'halls'       => $halls,
            'subjects'    => $subjects,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_seating/manage', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // CREATE_ROOM — POST handler to add a seating room
    // =========================================================================
    public function create_room()
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int)$this->input->post('batch_exam_id');
        $hall_id       = (int)$this->input->post('hall_id');
        $exam_date     = $this->input->post('exam_date');
        $session_slot  = $this->input->post('session_slot');
        $subject_id    = (int)$this->input->post('subject_id');
        $cap_override  = (int)$this->input->post('capacity_override');
        $staff_id      = (int)$this->session->userdata('staff_id');

        if (!$batch_exam_id || !$hall_id || !$exam_date) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please fill all required fields.</div>');
            redirect('coe/coe_seating/manage/' . $batch_exam_id);
        }

        $room_id = $this->Coe_seating_model->createRoom([
            'batch_exam_id'    => $batch_exam_id,
            'hall_id'          => $hall_id,
            'exam_date'        => $exam_date,
            'session_slot'     => $session_slot,
            'subject_id'       => $subject_id ?: null,
            'capacity_override'=> $cap_override ?: null,
        ]);

        $this->Coe_audit_model->log(
            'seating_room_created',
            'coe_seating_rooms',
            $room_id,
            null,
            ['hall_id' => $hall_id, 'exam_date' => $exam_date, 'session_slot' => $session_slot]
        );

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Seating room created successfully.</div>');
        redirect('coe/coe_seating/manage/' . $batch_exam_id);
    }

    // =========================================================================
    // AUTO_ASSIGN — auto-assign eligible students to a room
    // =========================================================================
    public function auto_assign($room_id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_add')) {
            access_denied();
        }

        $room_id = (int)$room_id;
        $room    = $this->Coe_seating_model->getRoomById($room_id);
        if (!$room) {
            show_404();
        }

        $result   = $this->Coe_seating_model->autoAssign($room_id);
        $staff_id = (int)$this->session->userdata('staff_id');

        $this->Coe_audit_model->log(
            'seating_auto_assigned',
            'coe_seating_rooms',
            $room_id,
            null,
            $result
        );

        if (isset($result['error'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">' . $result['error'] . '</div>');
        } else {
            $this->session->set_flashdata('msg',
                '<div class="alert alert-success"><strong>' . $result['assigned'] . '</strong> students assigned to this room.</div>'
            );
        }
        redirect('coe/coe_seating/view_room/' . $room_id);
    }

    // =========================================================================
    // VIEW_ROOM — view student seating list for one room
    // =========================================================================
    public function view_room($room_id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_view')) {
            access_denied();
        }

        $room_id     = (int)$room_id;
        $room        = $this->Coe_seating_model->getRoomById($room_id);
        if (!$room) {
            show_404();
        }

        $assignments = $this->Coe_seating_model->getAssignments($room_id);

        $data = [
            'title'  => lang('coe_seating'),
            'room'        => $room,
            'assignments' => $assignments,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_seating/view_room', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // PRINT_SEATING — PDF seating plan for a room
    // =========================================================================
    public function print_seating($room_id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_view')) {
            access_denied();
        }

        $room_id     = (int)$room_id;
        $room        = $this->Coe_seating_model->getRoomById($room_id);
        if (!$room) {
            show_404();
        }

        $assignments = $this->Coe_seating_model->getAssignments($room_id);
        $sch_setting     = $this->sch_setting_detail;
        $logo_filename   = $sch_setting->admission_logo_left ?? '';
        $logo_path       = null;
        if ($logo_filename && is_file(FCPATH . 'uploads/logos/' . $logo_filename)) {
            $logo_path = base_url('uploads/logos/' . $logo_filename);
        }

        $html = $this->load->view('admin/coe/coe_seating/print_seating', [
            'room'        => $room,
            'assignments' => $assignments,
            'logo_path'   => $logo_path,
            'sch_setting' => $sch_setting,
        ], true);

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load(['format' => 'A4', 'margin_left' => 15, 'margin_right' => 15, 'margin_top' => 10, 'margin_bottom' => 10]);
        $mpdf->WriteHTML($html, 0);
        $mpdf->Output('SeatingPlan_' . preg_replace('/[^A-Za-z0-9_]/', '_', $room->hall_name) . '.pdf', 'I');
    }

    // =========================================================================
    // CLEAR_ROOM — clear all assignments for a room
    // =========================================================================
    public function clear_room($room_id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_edit')) {
            access_denied();
        }

        $room_id = (int)$room_id;
        $room    = $this->Coe_seating_model->getRoomById($room_id);
        if (!$room) {
            show_404();
        }

        $this->Coe_seating_model->clearAssignments($room_id);
        $this->Coe_audit_model->log('seating_cleared', 'coe_seating_rooms', $room_id, null, null);

        $this->session->set_flashdata('msg', '<div class="alert alert-warning">All assignments cleared for this room.</div>');
        redirect('coe/coe_seating/manage/' . $room->exam_group_class_batch_exam_id);
    }

    // =========================================================================
    // DELETE_ROOM
    // =========================================================================
    public function delete_room($room_id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_delete')) {
            access_denied();
        }

        $room_id = (int)$room_id;
        $room    = $this->Coe_seating_model->getRoomById($room_id);
        if (!$room) {
            show_404();
        }

        $batch_exam_id = $room->exam_group_class_batch_exam_id;
        $this->Coe_seating_model->deleteRoom($room_id);
        $this->Coe_audit_model->log('seating_room_deleted', 'coe_seating_rooms', $room_id, null, null);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Room deleted.</div>');
        redirect('coe/coe_seating/manage/' . $batch_exam_id);
    }

    // =========================================================================
    // HALLS — list all exam halls with CRUD
    // =========================================================================
    public function halls()
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_seating');

        $halls = $this->Coe_seating_model->getAllHalls();

        $data = [
            'title' => lang('coe_seating') . ' — Exam Halls',
            'halls' => $halls,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_seating/halls', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // SAVE_HALL — POST: insert or update an exam hall
    // =========================================================================
    public function save_hall()
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_add')) {
            access_denied();
        }

        $id       = (int)$this->input->post('id');
        $name     = trim($this->input->post('name'));
        $capacity = (int)$this->input->post('capacity');
        $location = trim($this->input->post('location'));
        $block    = trim($this->input->post('block'));
        $floor    = trim($this->input->post('floor'));
        $is_active = (int)(bool)$this->input->post('is_active');

        if (!$name || $capacity < 1) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Hall name and capacity are required.</div>');
            redirect('coe/coe_seating/halls');
        }

        $row = [
            'name'      => $name,
            'capacity'  => $capacity,
            'location'  => $location,
            'description' => $block . ($floor ? ' | Floor: ' . $floor : ''),
            'is_active' => $is_active,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->Coe_seating_model->updateHall($id, $row);
            $this->Coe_audit_model->log('exam_hall_updated', 'halls', $id, null, $row);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Exam hall updated.</div>');
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $new_id = $this->Coe_seating_model->insertHall($row);
            $this->Coe_audit_model->log('exam_hall_created', 'halls', $new_id, null, $row);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Exam hall added.</div>');
        }

        redirect('coe/coe_seating/halls');
    }

    // =========================================================================
    // DELETE_HALL — remove an exam hall (only if no seating rooms reference it)
    // =========================================================================
    public function delete_hall($id)
    {
        if (!$this->rbac->hasPrivilege('coe_seating', 'can_delete')) {
            access_denied();
        }

        $id = (int)$id;
        $in_use = $this->db->where('hall_id', $id)->count_all_results('coe_seating_rooms');
        if ($in_use > 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Cannot delete: this hall is assigned to ' . $in_use . ' seating room(s). Remove those rooms first.</div>');
            redirect('coe/coe_seating/halls');
        }

        $this->Coe_seating_model->deleteHall($id);
        $this->Coe_audit_model->log('exam_hall_deleted', 'halls', $id, null, null);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Exam hall deleted.</div>');
        redirect('coe/coe_seating/halls');
    }
}
