<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_attendance_model
 *
 * Handles real-time exam attendance:
 * - Mark present/absent for each hall ticket on a given exam date/session
 * - QR-scan marking (via API endpoint)
 * - Summary stats per room
 */
class Coe_attendance_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // Get the batch_exam_id for a given seating room (for breadcrumb)
    // ------------------------------------------------------------------
    public function getBatchExamIdByRoom($room_id)
    {
        $row = $this->db->select('exam_group_class_batch_exam_id')
            ->where('id', (int) $room_id)
            ->get('coe_seating_rooms')->row();
        return $row ? $row->exam_group_class_batch_exam_id : null;
    }

    // ------------------------------------------------------------------
    // Get attendance records for a seating room + date + slot
    // ------------------------------------------------------------------
    public function getRoomAttendance($seating_room_id, $exam_date, $session_slot)
    {
        return $this->db
            ->select('att.*, ht.hall_ticket_no, ht.student_id, 
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sa.seat_number')
            ->from('coe_exam_attendance att')
            ->join('coe_hall_tickets ht', 'ht.id = att.coe_hall_ticket_id')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->join('coe_seating_assignments sa', 'sa.hall_ticket_id = ht.id AND sa.seating_room_id = att.seating_room_id', 'left')
            ->where('att.seating_room_id', (int) $seating_room_id)
            ->where('att.exam_date', $exam_date)
            ->where('att.session_slot', $session_slot)
            ->order_by('sr.seat_number ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Get all students seated in a room (for initialising attendance sheet)
    // ------------------------------------------------------------------
    public function getSeatedStudentsForRoom($seating_room_id, $exam_date, $session_slot)
    {
        return $this->db
            ->select('ht.id AS hall_ticket_id, ht.hall_ticket_no, ht.student_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sa.seat_number,
                      att.id AS att_id, att.is_present, att.qr_scanned, att.remarks')
            ->from('coe_seating_assignments sa')
            ->join('coe_hall_tickets ht', 'ht.id = sa.hall_ticket_id')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->join('coe_exam_attendance att',
                   'att.coe_hall_ticket_id = ht.id AND att.seating_room_id = sa.seating_room_id AND att.exam_date = "' . $this->db->escape_str($exam_date) . '" AND att.session_slot = "' . $this->db->escape_str($session_slot) . '"',
                   'left')
            ->where('sa.seating_room_id', (int) $seating_room_id)
            ->order_by('sa.seat_number ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Upsert (mark present/absent)
    // ------------------------------------------------------------------
    public function markAttendance($hall_ticket_id, $seating_room_id, $exam_date, $session_slot, $is_present, $marked_by, $remarks = '', $qr_scanned = 0)
    {
        $existing = $this->db->where([
            'coe_hall_ticket_id' => (int) $hall_ticket_id,
            'exam_date'          => $exam_date,
            'session_slot'       => $session_slot,
        ])->get('coe_exam_attendance')->row();

        $payload = [
            'seating_room_id'   => (int) $seating_room_id,
            'is_present'        => $is_present ? 1 : 0,
            'marked_by'         => (int) $marked_by,
            'marked_at'         => date('Y-m-d H:i:s'),
            'qr_scanned'        => $qr_scanned ? 1 : 0,
            'remarks'           => $remarks,
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('coe_exam_attendance', $payload);
            return $existing->id;
        } else {
            $payload['coe_hall_ticket_id'] = (int) $hall_ticket_id;
            $payload['exam_date']          = $exam_date;
            $payload['session_slot']       = $session_slot;
            $this->db->insert('coe_exam_attendance', $payload);
            return $this->db->insert_id();
        }
    }

    // ------------------------------------------------------------------
    // Bulk mark (from form submission — array of hall ticket IDs that are present)
    // ------------------------------------------------------------------
    public function bulkMark($seating_room_id, $exam_date, $session_slot, $present_ids, $all_ids, $marked_by, $remarks_map = [])
    {
        $inserted = 0;
        $present_set = array_flip((array) $present_ids);
        foreach ((array) $all_ids as $htid) {
            $is_present = isset($present_set[$htid]) ? 1 : 0;
            $remark     = $remarks_map[$htid] ?? '';
            $this->markAttendance($htid, $seating_room_id, $exam_date, $session_slot, $is_present, $marked_by, $remark);
            $inserted++;
        }
        return $inserted;
    }

    // ------------------------------------------------------------------
    // Mark by QR hash (API endpoint)
    // ------------------------------------------------------------------
    public function markByQrHash($qr_hash, $seating_room_id, $exam_date, $session_slot, $marked_by)
    {
        $ht = $this->db->where('qr_hash', $this->db->escape_str($qr_hash))
                       ->get('coe_hall_tickets')->row();
        if (!$ht) {
            return ['status' => 'error', 'message' => 'Hall ticket not found'];
        }

        $id = $this->markAttendance($ht->id, $seating_room_id, $exam_date, $session_slot, 1, $marked_by, '', 1);
        return ['status' => 'success', 'hall_ticket_no' => $ht->hall_ticket_no, 'attendance_id' => $id];
    }

    // ------------------------------------------------------------------
    // Summary stats
    // ------------------------------------------------------------------
    public function getSummaryByRoom($seating_room_id, $exam_date, $session_slot)
    {
        $row = $this->db
            ->select('COUNT(*) AS total, SUM(is_present) AS present_count, SUM(1-is_present) AS absent_count')
            ->where('seating_room_id', (int) $seating_room_id)
            ->where('exam_date', $exam_date)
            ->where('session_slot', $session_slot)
            ->get('coe_exam_attendance')->row();
        return $row;
    }

    // ------------------------------------------------------------------
    // Get a single hall ticket by hall_ticket_no (optionally scoped to batch exam)
    // ------------------------------------------------------------------
    public function getHallTicketByNumber($hall_ticket_no, $batch_exam_id = null)
    {
        $this->db->where('hall_ticket_no', $hall_ticket_no);
        if ($batch_exam_id) {
            $this->db->where('exam_group_class_batch_exam_id', (int) $batch_exam_id);
        }
        return $this->db->get('coe_hall_tickets')->row();
    }

    // ------------------------------------------------------------------
    // Get all hall tickets for a batch exam (for sample CSV generation)
    // ------------------------------------------------------------------
    public function getHallTicketsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('ht.hall_ticket_no, CONCAT(st.firstname, " ", st.lastname) AS student_name')
            ->from('coe_hall_tickets ht')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->where('ht.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('ht.is_valid', 1)
            ->order_by('ht.hall_ticket_no ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Get all rooms for a batch exam (for dropdown)
    // ------------------------------------------------------------------
    public function getRoomsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->distinct()
            ->select('sr.id, h.name AS hall_name, sr.exam_date, sr.session_slot, COALESCE(sr.capacity_override, h.capacity) AS seating_capacity')
            ->from('coe_seating_rooms sr')
            ->join('halls h', 'h.id = sr.hall_id', 'left')
            ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('sr.exam_date ASC, sr.session_slot ASC, h.name ASC')
            ->get()->result();
    }

    /**
     * Full room context for the attendance sheet header.
     * Joins hall, exam schedule, subject, exam group, class, session.
     * Uses a raw query to work around collation mismatches between
     * core tables (utf8mb4_general_ci) and COE tables (utf8mb4_unicode_ci).
     */
    public function getRoomInfo($room_id, $exam_date, $session_slot)
    {
        $sql = "
            SELECT
                sr.id                                       AS room_id,
                sr.exam_date,
                sr.session_slot,
                sr.exam_group_class_batch_exam_id,
                COALESCE(sr.capacity_override, h.capacity)  AS room_capacity,
                h.name                                      AS hall_name,
                h.capacity                                  AS hall_capacity,
                es.start_time,
                es.end_time,
                sub.name                                    AS subject_name,
                sub.code                                    AS subject_code,
                eg.name                                     AS exam_group_name,
                eg.exam_category,
                egcbe.exam                                  AS batch_title,
                c.class                                     AS class_name,
                s.session                                   AS session_year
            FROM coe_seating_rooms sr
            LEFT JOIN halls h
                   ON h.id = sr.hall_id
            LEFT JOIN exam_group_class_batch_exams egcbe
                   ON egcbe.id = sr.exam_group_class_batch_exam_id
            LEFT JOIN exam_groups eg
                   ON eg.id = egcbe.exam_group_id
            LEFT JOIN classes c
                   ON c.id = egcbe.class_id
            LEFT JOIN sessions s
                   ON s.id = egcbe.session_id
            LEFT JOIN coe_exam_schedule es
                   ON es.exam_group_class_batch_exam_id = sr.exam_group_class_batch_exam_id
                  AND es.exam_date    = sr.exam_date
                  AND CONVERT(es.session_slot  USING utf8mb4) COLLATE utf8mb4_unicode_ci
                    = CONVERT(sr.session_slot  USING utf8mb4) COLLATE utf8mb4_unicode_ci
                  AND (sr.subject_id IS NULL OR es.subject_id = sr.subject_id)
            LEFT JOIN subjects sub
                   ON sub.id = es.subject_id
            WHERE sr.id = ?
            LIMIT 1
        ";
        $q = $this->db->query($sql, [(int) $room_id]);
        return ($q !== false) ? $q->row() : null;
    }
}
