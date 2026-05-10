<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_invigilation_model
 * Manages exam duty roster — invigilators assigned to seating rooms.
 */
class Coe_invigilation_model extends CI_Model
{
    private static $VALID_DUTY_TYPES = ['chief_superintendent', 'invigilator', 'deputy', 'flying_squad'];

    // -------------------------------------------------------------------------
    // Get CoE-locked events
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
    // Get rooms for a batch exam (for duty assignment)
    // -------------------------------------------------------------------------
    public function getRooms($batch_exam_id)
    {
        return $this->db->query(
            "SELECT sr.*, h.name AS hall_name, h.location,
                    (SELECT COUNT(*) FROM coe_invigilation_duties d WHERE d.seating_room_id = sr.id) AS duty_count
             FROM coe_seating_rooms sr
             LEFT JOIN halls h ON h.id = sr.hall_id
             WHERE sr.exam_group_class_batch_exam_id = ?
             ORDER BY sr.exam_date, sr.session_slot, h.name",
            [(int)$batch_exam_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Get duties by batch exam (all rooms and their staff)
    // -------------------------------------------------------------------------
    public function getDutiesByBatchExam($batch_exam_id)
    {
        return $this->db->query(
            "SELECT d.*, st.name AS staff_firstname, st.surname AS staff_surname,
                    st.designation, h.name AS hall_name, sr.exam_date, sr.session_slot,
                    sr.exam_group_class_batch_exam_id AS batch_exam_id
             FROM coe_invigilation_duties d
             LEFT JOIN staff st ON st.id = d.staff_id
             LEFT JOIN coe_seating_rooms sr ON sr.id = d.seating_room_id
             LEFT JOIN halls h ON h.id = sr.hall_id
             WHERE sr.exam_group_class_batch_exam_id = ?
             ORDER BY sr.exam_date, sr.session_slot, d.duty_type, h.name",
            [(int)$batch_exam_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Get duties for a specific room
    // -------------------------------------------------------------------------
    public function getDutiesByRoom($room_id)
    {
        return $this->db->query(
            "SELECT d.*, st.name AS staff_firstname, st.surname AS staff_surname, st.designation
             FROM coe_invigilation_duties d
             LEFT JOIN staff st ON st.id = d.staff_id
             WHERE d.seating_room_id = ?
             ORDER BY d.duty_type",
            [(int)$room_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Assign a duty
    // -------------------------------------------------------------------------
    public function assignDuty($data)
    {
        $duty_type = in_array($data['duty_type'] ?? '', self::$VALID_DUTY_TYPES)
            ? $data['duty_type']
            : 'invigilator';

        // Avoid duplicate (same staff, same room, same duty_type)
        $exists = $this->db->where('seating_room_id', (int)$data['seating_room_id'])
            ->where('staff_id', (int)$data['staff_id'])
            ->where('duty_type', $duty_type)
            ->count_all_results('coe_invigilation_duties');
        if ($exists > 0) {
            return false;
        }

        $this->db->insert('coe_invigilation_duties', [
            'seating_room_id' => (int)$data['seating_room_id'],
            'staff_id'        => (int)$data['staff_id'],
            'duty_type'       => $duty_type,
            'remarks'         => $data['remarks'] ?? null,
        ]);
        return $this->db->insert_id();
    }

    // -------------------------------------------------------------------------
    // Remove a duty
    // -------------------------------------------------------------------------
    public function removeDuty($duty_id)
    {
        $this->db->where('id', (int)$duty_id);
        $this->db->delete('coe_invigilation_duties');
    }

    // -------------------------------------------------------------------------
    // Get available staff (all active staff)
    // -------------------------------------------------------------------------
    public function getAvailableStaff()
    {
        return $this->db->select('id, name, surname, designation')
            ->where('is_active', 1)
            ->order_by('name', 'ASC')
            ->get('staff')->result();
    }

    // -------------------------------------------------------------------------
    // Bulk import duties from CSV rows
    // Expected keys: hall_name, exam_date (YYYY-MM-DD), session_slot (FN|AN),
    //                staff_id (int), duty_type, remarks
    // -------------------------------------------------------------------------
    public function bulkImportDuties($batch_exam_id, $rows)
    {
        $batch_exam_id = (int)$batch_exam_id;
        $inserted      = 0;
        $skipped       = 0;
        $errors        = [];

        // Pre-load room lookup: keyed by "hall_name|exam_date|session_slot"
        $rooms_raw = $this->db->query(
            "SELECT sr.id, h.name AS hall_name, sr.exam_date, sr.session_slot
             FROM coe_seating_rooms sr
             LEFT JOIN halls h ON h.id = sr.hall_id
             WHERE sr.exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->result();

        $room_map = [];
        foreach ($rooms_raw as $r) {
            $key = strtolower(trim($r->hall_name)) . '|' . $r->exam_date . '|' . strtoupper($r->session_slot);
            $room_map[$key] = $r->id;
        }

        foreach ($rows as $line_no => $row) {
            $row_num = $line_no + 2; // 1-indexed, +1 for header row

            // Skip comment lines (start with #) — CSVReader may return them
            $first_val = reset($row);
            if (strpos(trim((string)$first_val), '#') === 0) {
                continue;
            }

            // Normalise keys (CSVReader uses first-row header as keys)
            $hall_name    = trim($row['hall_name']    ?? '');
            $exam_date    = trim($row['exam_date']    ?? '');
            $session_slot = strtoupper(trim($row['session_slot'] ?? ''));
            $staff_id     = (int)($row['staff_id']    ?? 0);
            $duty_type    = trim($row['duty_type']    ?? 'invigilator');
            $remarks      = trim($row['remarks']      ?? '');

            // Validate required fields
            if ($hall_name === '' || $exam_date === '' || $session_slot === '' || $staff_id <= 0) {
                $errors[] = "Row {$row_num}: Missing required field (hall_name, exam_date, session_slot, or staff_id).";
                continue;
            }

            // Validate exam_date format
            $parsed_date = date('Y-m-d', strtotime($exam_date));
            if ($parsed_date === '1970-01-01' && $exam_date !== '1970-01-01') {
                $errors[] = "Row {$row_num}: Invalid exam_date '{$exam_date}'. Use YYYY-MM-DD format.";
                continue;
            }

            // Validate session_slot
            if (!in_array($session_slot, ['FN', 'AN'], true)) {
                $errors[] = "Row {$row_num}: session_slot must be FN or AN (got '{$session_slot}').";
                continue;
            }

            // Validate duty_type
            if (!in_array($duty_type, self::$VALID_DUTY_TYPES, true)) {
                $duty_type = 'invigilator';
            }

            // Lookup seating room
            $lookup_key = strtolower($hall_name) . '|' . $parsed_date . '|' . $session_slot;
            if (!isset($room_map[$lookup_key])) {
                $errors[] = "Row {$row_num}: No room found for hall '{$hall_name}' on {$parsed_date} ({$session_slot}).";
                continue;
            }
            $room_id = $room_map[$lookup_key];

            // Check staff exists
            $staff_exists = (int)$this->db->where('id', $staff_id)->count_all_results('staff');
            if (!$staff_exists) {
                $errors[] = "Row {$row_num}: Staff ID {$staff_id} not found.";
                continue;
            }

            // Duplicate check
            $exists = (int)$this->db
                ->where('seating_room_id', $room_id)
                ->where('staff_id', $staff_id)
                ->where('duty_type', $duty_type)
                ->count_all_results('coe_invigilation_duties');
            if ($exists > 0) {
                $skipped++;
                continue;
            }

            $this->db->insert('coe_invigilation_duties', [
                'seating_room_id' => $room_id,
                'staff_id'        => $staff_id,
                'duty_type'       => $duty_type,
                'remarks'         => $remarks ?: null,
            ]);
            $inserted++;
        }

        return ['inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors];
    }

    // -------------------------------------------------------------------------
    // Summary per batch exam
    // -------------------------------------------------------------------------
    public function getSummary($batch_exam_id)
    {
        $batch_exam_id = (int)$batch_exam_id;
        $rooms = (int)$this->db->where('exam_group_class_batch_exam_id', $batch_exam_id)
            ->count_all_results('coe_seating_rooms');
        $duties = (int)$this->db->query(
            "SELECT COUNT(d.id) AS n FROM coe_invigilation_duties d
             LEFT JOIN coe_seating_rooms sr ON sr.id = d.seating_room_id
             WHERE sr.exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row()->n ?? 0;
        $rooms_with_duties = (int)$this->db->query(
            "SELECT COUNT(DISTINCT d.seating_room_id) AS n FROM coe_invigilation_duties d
             LEFT JOIN coe_seating_rooms sr ON sr.id = d.seating_room_id
             WHERE sr.exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row()->n ?? 0;

        return [
            'rooms'             => $rooms,
            'total_duties'      => $duties,
            'rooms_with_duties' => $rooms_with_duties,
            'rooms_unassigned'  => max(0, $rooms - $rooms_with_duties),
        ];
    }
}
