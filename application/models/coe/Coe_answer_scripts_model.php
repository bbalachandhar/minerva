<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_answer_scripts_model
 * Manages scanned answer script records linked to hall tickets.
 */
class Coe_answer_scripts_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // List all scripts for a batch exam
    // ------------------------------------------------------------------
    public function getAll($filters = [])
    {
        $this->db
            ->select('ans.*, ht.hall_ticket_no, ht.student_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(up.name, " ", COALESCE(up.surname,"")) AS uploaded_by_name')
            ->from('coe_answer_scripts ans')
            ->join('coe_hall_tickets ht',     'ht.id = ans.coe_hall_ticket_id', 'left')
            ->join('students st',              'st.id = ht.student_id',         'left')
            ->join('subjects sub',             'sub.id = ans.subject_id',       'left')
            ->join('staff up',                 'up.id = ans.uploaded_by',       'left');

        if (!empty($filters['batch_exam_id'])) {
            $this->db->where('ans.exam_group_class_batch_exam_id', (int) $filters['batch_exam_id']);
        }
        if (!empty($filters['subject_id'])) {
            $this->db->where('ans.subject_id', (int) $filters['subject_id']);
        }
        if (!empty($filters['scan_status'])) {
            $this->db->where('ans.scan_status', $filters['scan_status']);
        }
        if (!empty($filters['exam_date'])) {
            $this->db->where('ans.exam_date', $filters['exam_date']);
        }

        return $this->db->order_by('ht.hall_ticket_no ASC')->get()->result();
    }

    // ------------------------------------------------------------------
    // Get single script
    // ------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db
            ->select('ans.*, ht.hall_ticket_no, ht.student_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(up.name, " ", COALESCE(up.surname,"")) AS uploaded_by_name')
            ->from('coe_answer_scripts ans')
            ->join('coe_hall_tickets ht',     'ht.id = ans.coe_hall_ticket_id', 'left')
            ->join('students st',              'st.id = ht.student_id',         'left')
            ->join('subjects sub',             'sub.id = ans.subject_id',       'left')
            ->join('staff up',                 'up.id = ans.uploaded_by',       'left')
            ->where('ans.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Insert
    // ------------------------------------------------------------------
    public function insert($data)
    {
        $this->db->insert('coe_answer_scripts', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Update
    // ------------------------------------------------------------------
    public function update($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_answer_scripts', $data);
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('coe_answer_scripts');
    }

    // ------------------------------------------------------------------
    // Get subjects for a batch exam (for filter dropdowns)
    // ------------------------------------------------------------------
    public function getSubjectsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select("sub.id, sub.name AS subject_name, sub.code AS subject_code,
                      egcbes.date_from AS exam_date, egcbes.time_from,
                      CASE WHEN egcbes.time_from < '12:00:00' THEN 'FN' ELSE 'AN' END AS session_slot")
            ->from('exam_group_class_batch_exam_subjects egcbes')
            ->join('subjects sub', 'sub.id = egcbes.subject_id', 'left')
            ->where('egcbes.exam_group_class_batch_exams_id', (int) $batch_exam_id)
            ->where('egcbes.is_active', 1)
            ->order_by('egcbes.date_from ASC, sub.name ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Get hall tickets not yet registered for a subject (for bulk register)
    // ------------------------------------------------------------------
    public function getUnregisteredHallTickets($batch_exam_id, $subject_id)
    {
        $batch_exam_id = (int) $batch_exam_id;
        $subject_id    = (int) $subject_id;
        $sql = "SELECT ht.id, ht.hall_ticket_no,
                       CONCAT(st.firstname, ' ', st.lastname) AS student_name
                FROM coe_hall_tickets ht
                LEFT JOIN students st ON st.id = ht.student_id
                WHERE ht.exam_group_class_batch_exam_id = ?
                  AND ht.id NOT IN (
                      SELECT coe_hall_ticket_id FROM coe_answer_scripts
                      WHERE subject_id = ? AND exam_group_class_batch_exam_id = ?
                  )
                ORDER BY ht.hall_ticket_no ASC";
        return $this->db->query($sql, [$batch_exam_id, $subject_id, $batch_exam_id])->result();
    }

    // ------------------------------------------------------------------
    // Bulk insert multiple script records
    // ------------------------------------------------------------------
    public function bulkInsert($rows)
    {
        if (empty($rows)) return 0;
        $this->db->insert_batch('coe_answer_scripts', $rows);
        return $this->db->affected_rows();
    }

    // ------------------------------------------------------------------
    // Get hall tickets for a batch exam (for upload form lookup)
    // ------------------------------------------------------------------
    public function getHallTicketsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('ht.id, ht.hall_ticket_no, ht.student_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name')
            ->from('coe_hall_tickets ht')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->where('ht.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('ht.hall_ticket_no ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Check if script already exists for a hall ticket + subject
    // ------------------------------------------------------------------
    public function existsForHallTicketSubject($hall_ticket_id, $subject_id)
    {
        return $this->db
            ->where('coe_hall_ticket_id', (int) $hall_ticket_id)
            ->where('subject_id', (int) $subject_id)
            ->count_all_results('coe_answer_scripts') > 0;
    }

    // ------------------------------------------------------------------
    // Count by status for a batch exam
    // ------------------------------------------------------------------
    public function countByStatus($batch_exam_id)
    {
        $rows = $this->db
            ->select('scan_status, COUNT(*) AS cnt')
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->group_by('scan_status')
            ->get('coe_answer_scripts')->result();

        $out = ['pending' => 0, 'scanned' => 0, 'uploaded' => 0];
        foreach ($rows as $r) {
            $out[$r->scan_status] = (int) $r->cnt;
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Generate a unique barcode token
    // ------------------------------------------------------------------
    public function generateBarcodeToken()
    {
        do {
            $token = bin2hex(random_bytes(8));   // 16 hex chars
            $exists = $this->db->where('barcode_token', $token)->count_all_results('coe_answer_scripts');
        } while ($exists);
        return $token;
    }
}
