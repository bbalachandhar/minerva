<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_seating_model
 * Manages exam hall rooms and student seating assignments.
 */
class Coe_seating_model extends CI_Model
{
    // -------------------------------------------------------------------------
    // Get CoE-locked events (for index)
    // -------------------------------------------------------------------------
    public function getCoeEvents($session_id = null)
    {
        $this->db->select(
            'ebe.id, ebe.exam AS exam_name, ebe.date_from, ebe.date_to, ebe.session_id,
             ebe.exam_group_id, eg.name AS exam_group_name, ses.session AS session_name'
        );
        $this->db->from('exam_group_class_batch_exams ebe');
        $this->db->join('exam_groups eg', 'eg.id = ebe.exam_group_id', 'left');
        $this->db->join('sessions ses', 'ses.id = ebe.session_id', 'left');
        $this->db->where('ebe.coe_locked', 1);
        if ($session_id) {
            $this->db->where('ebe.session_id', (int)$session_id);
        }
        $this->db->order_by('ebe.date_from', 'DESC');
        return $this->db->get()->result();
    }

    // -------------------------------------------------------------------------
    // Get seating rooms for a batch exam
    // -------------------------------------------------------------------------
    public function getRooms($batch_exam_id)
    {
        return $this->db->query(
            "SELECT sr.*, h.name AS hall_name, h.capacity AS hall_capacity, h.location,
                    sub.name AS subject_name, sub.code AS subject_code,
                    COALESCE(sr.capacity_override, h.capacity) AS effective_capacity,
                    (SELECT COUNT(*) FROM coe_seating_assignments sa WHERE sa.seating_room_id = sr.id) AS assigned_count
             FROM coe_seating_rooms sr
             LEFT JOIN halls h   ON h.id  = sr.hall_id
             LEFT JOIN subjects sub ON sub.id = sr.subject_id
             WHERE sr.exam_group_class_batch_exam_id = ?
             ORDER BY sr.exam_date, sr.session_slot, h.name",
            [(int)$batch_exam_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Get a single room with details
    // -------------------------------------------------------------------------
    public function getRoomById($room_id)
    {
        return $this->db->query(
            "SELECT sr.*, h.name AS hall_name, h.capacity AS hall_capacity,
                    COALESCE(sr.capacity_override, h.capacity) AS effective_capacity,
                    sub.name AS subject_name, sub.code AS subject_code,
                    ebe.exam AS exam_name, ebe.session_id,
                    eg.name AS exam_group_name, ses.session AS session_name
             FROM coe_seating_rooms sr
             LEFT JOIN halls h ON h.id = sr.hall_id
             LEFT JOIN subjects sub ON sub.id = sr.subject_id
             LEFT JOIN exam_group_class_batch_exams ebe ON ebe.id = sr.exam_group_class_batch_exam_id
             LEFT JOIN exam_groups eg ON eg.id = sr.exam_group_id
             LEFT JOIN sessions ses ON ses.id = ebe.session_id
             WHERE sr.id = ?
             LIMIT 1",
            [(int)$room_id]
        )->row();
    }

    // -------------------------------------------------------------------------
    // Create a seating room
    // -------------------------------------------------------------------------
    public function createRoom($data)
    {
        $batch_exam = $this->db->get_where('exam_group_class_batch_exams', ['id' => $data['batch_exam_id']])->row();
        if (!$batch_exam) {
            return false;
        }

        $this->db->insert('coe_seating_rooms', [
            'exam_group_id'                  => $batch_exam->exam_group_id,
            'exam_group_class_batch_exam_id' => (int)$data['batch_exam_id'],
            'hall_id'                        => (int)$data['hall_id'],
            'subject_id'                     => !empty($data['subject_id']) ? (int)$data['subject_id'] : null,
            'exam_date'                      => $data['exam_date'],
            'session_slot'                   => in_array($data['session_slot'] ?? 'FN', ['FN','AN']) ? $data['session_slot'] : 'FN',
            'capacity_override'              => !empty($data['capacity_override']) ? (int)$data['capacity_override'] : null,
            'is_active'                      => 1,
        ]);

        return $this->db->insert_id();
    }

    // -------------------------------------------------------------------------
    // Auto-assign students to a room (fills sequentially by register_no)
    // -------------------------------------------------------------------------
    public function autoAssign($room_id)
    {
        $room = $this->getRoomById($room_id);
        if (!$room) {
            return ['assigned' => 0, 'error' => 'Room not found'];
        }

        $capacity   = (int)$room->effective_capacity;
        $batch_id   = (int)$room->exam_group_class_batch_exam_id;

        // Already assigned students in this room
        $existing = $this->db->query(
            "SELECT student_id FROM coe_seating_assignments WHERE seating_room_id = ?",
            [$room_id]
        )->result_array();
        $existing_ids = array_column($existing, 'student_id');

        // All assigned students in this batch (to avoid double-assigning)
        $all_assigned = $this->db->query(
            "SELECT sa.student_id FROM coe_seating_assignments sa
             LEFT JOIN coe_seating_rooms sr ON sr.id = sa.seating_room_id
             WHERE sr.exam_group_class_batch_exam_id = ?",
            [$batch_id]
        )->result_array();
        $all_assigned_ids = array_column($all_assigned, 'student_id');

        // Eligible students with hall tickets, not yet assigned, ordered by register_no
        $students = $this->db->query(
            "SELECT ht.id AS ht_id, ht.student_id, ht.hall_ticket_no,
                    ss.id AS student_session_id, s.register_no
             FROM coe_hall_tickets ht
             LEFT JOIN students s ON s.id = ht.student_id
             LEFT JOIN student_session ss ON ss.student_id = ht.student_id
                                        AND ss.session_id = (SELECT session_id FROM exam_group_class_batch_exams WHERE id = ? LIMIT 1)
             WHERE ht.exam_group_class_batch_exam_id = ?
               AND ht.is_valid = 1
             ORDER BY s.register_no",
            [$batch_id, $batch_id]
        )->result_array();

        $seats_available = $capacity - count($existing_ids);
        if ($seats_available <= 0) {
            return ['assigned' => 0, 'error' => 'Room is full'];
        }

        $assigned = 0;
        $seat_num = count($existing_ids) + 1;

        foreach ($students as $st) {
            if ($seats_available <= 0) break;
            if (in_array($st['student_id'], $all_assigned_ids)) continue;

            $this->db->insert('coe_seating_assignments', [
                'seating_room_id'    => $room_id,
                'student_id'         => (int)$st['student_id'],
                'student_session_id' => (int)($st['student_session_id'] ?? 0),
                'hall_ticket_id'     => (int)$st['ht_id'],
                'seat_number'        => 'S' . str_pad($seat_num, 3, '0', STR_PAD_LEFT),
            ]);

            $assigned++;
            $seat_num++;
            $seats_available--;
            $all_assigned_ids[] = $st['student_id'];
        }

        return ['assigned' => $assigned];
    }

    // -------------------------------------------------------------------------
    // Get students assigned to a room
    // -------------------------------------------------------------------------
    public function getAssignments($room_id)
    {
        return $this->db->query(
            "SELECT sa.*, s.firstname, s.lastname, s.register_no, s.gender,
                    ht.hall_ticket_no, c.class AS class_name, d.department_name
             FROM coe_seating_assignments sa
             LEFT JOIN students s ON s.id = sa.student_id
             LEFT JOIN coe_hall_tickets ht ON ht.id = sa.hall_ticket_id
             LEFT JOIN student_session ss ON ss.id = sa.student_session_id
             LEFT JOIN classes c ON c.id = ss.class_id
             LEFT JOIN department d ON d.id = ss.department_id
             WHERE sa.seating_room_id = ?
             ORDER BY sa.seat_number",
            [(int)$room_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Clear all assignments for a room
    // -------------------------------------------------------------------------
    public function clearAssignments($room_id)
    {
        $this->db->where('seating_room_id', (int)$room_id);
        $this->db->delete('coe_seating_assignments');
        return $this->db->affected_rows();
    }

    // -------------------------------------------------------------------------
    // Delete a room (and its assignments)
    // -------------------------------------------------------------------------
    public function deleteRoom($room_id)
    {
        $this->clearAssignments($room_id);
        $this->db->where('id', (int)$room_id);
        $this->db->delete('coe_seating_rooms');
    }

    // -------------------------------------------------------------------------
    // Stats per batch exam
    // -------------------------------------------------------------------------
    public function getSummary($batch_exam_id)
    {
        $batch_exam_id = (int)$batch_exam_id;
        $rooms = (int)$this->db->where('exam_group_class_batch_exam_id', $batch_exam_id)->count_all_results('coe_seating_rooms');
        $row   = $this->db->query(
            "SELECT COUNT(sa.id) AS total_assigned
             FROM coe_seating_assignments sa
             LEFT JOIN coe_seating_rooms sr ON sr.id = sa.seating_room_id
             WHERE sr.exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row();

        $total_ht = (int)$this->db->where('exam_group_class_batch_exam_id', $batch_exam_id)
            ->where('is_valid', 1)->count_all_results('coe_hall_tickets');

        return [
            'rooms'    => $rooms,
            'assigned' => (int)($row->total_assigned ?? 0),
            'total_ht' => $total_ht,
            'unassigned' => max(0, $total_ht - (int)($row->total_assigned ?? 0)),
        ];
    }

    // -------------------------------------------------------------------------
    // Get halls list for form dropdown
    // -------------------------------------------------------------------------
    public function getHalls()
    {
        return $this->db->where('is_active', 1)->get('halls')->result();
    }

    // -------------------------------------------------------------------------
    // Get subjects list for a batch exam (for room form)
    // -------------------------------------------------------------------------
    public function getSubjects($batch_exam_id)
    {
        return $this->db->query(
            "SELECT DISTINCT sub.id, sub.name, sub.code FROM coe_exam_applications app
             LEFT JOIN subjects sub ON sub.id = app.subject_id
             WHERE app.exam_group_class_batch_exam_id = ?
               AND app.application_status IN ('eligible','override_eligible')
             ORDER BY sub.name",
            [(int)$batch_exam_id]
        )->result();
    }
}
