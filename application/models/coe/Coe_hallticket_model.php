<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_hallticket_model
 * Handles hall ticket generation and retrieval for CoE module.
 */
class Coe_hallticket_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('coe_config', true);
    }

    // -------------------------------------------------------------------------
    // Config helpers
    // -------------------------------------------------------------------------
    private function _prefix()  { return $this->config->item('coe_ht_prefix',  'coe_config'); }
    private function _padding() { return (int)$this->config->item('coe_ht_padding', 'coe_config'); }
    private function _secret()  { return $this->config->item('coe_qr_secret_key', 'coe_config'); }

    // -------------------------------------------------------------------------
    // Generate hall tickets for all eligible students in a batch-exam
    // Returns ['generated' => n, 'skipped' => n]
    // -------------------------------------------------------------------------
    public function generate($batch_exam_id, $staff_id = 0)
    {
        $batch_exam_id = (int)$batch_exam_id;

        // Get batch exam to know session_id
        $batch_exam = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            return ['generated' => 0, 'skipped' => 0, 'error' => 'Exam event not found'];
        }

        // Get all distinct eligible students for this batch exam
        $eligible = $this->db->query(
            "SELECT DISTINCT student_id FROM coe_exam_applications
             WHERE exam_group_class_batch_exam_id = ?
               AND application_status IN ('eligible', 'override_eligible')",
            [$batch_exam_id]
        )->result_array();

        if (empty($eligible)) {
            return ['generated' => 0, 'skipped' => 0];
        }

        // Get students who already have a hall ticket for this batch exam
        $existing_raw = $this->db->query(
            "SELECT student_id FROM coe_hall_tickets WHERE exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->result_array();
        $existing = array_column($existing_raw, 'student_id');

        // Determine next sequence number (global across all hall tickets)
        $prefix     = $this->_prefix();
        $padding    = $this->_padding();
        $prefix_len = strlen($prefix);

        $seq_row = $this->db->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(hall_ticket_no, ?) AS UNSIGNED)), 0) AS max_seq
             FROM coe_hall_tickets",
            [$prefix_len + 1]
        )->row();
        $seq = (int)($seq_row->max_seq ?? 0) + 1;

        $secret    = $this->_secret();
        $generated = 0;
        $skipped   = 0;
        $now       = date('Y-m-d H:i:s');

        foreach ($eligible as $row) {
            $sid = (int)$row['student_id'];

            if (in_array($sid, $existing)) {
                $skipped++;
                continue;
            }

            $ht_no   = $prefix . str_pad($seq, $padding, '0', STR_PAD_LEFT);
            $qr_hash = hash_hmac('sha256', $sid . '|' . $ht_no . '|' . $batch_exam_id, $secret);

            $this->db->insert('coe_hall_tickets', [
                'exam_group_id'                   => $batch_exam->exam_group_id,
                'exam_group_class_batch_exam_id'  => $batch_exam_id,
                'student_id'                      => $sid,
                'hall_ticket_no'                  => $ht_no,
                'qr_hash'                         => $qr_hash,
                'is_valid'                        => 1,
                'generated_by'                    => (int)$staff_id,
                'generated_at'                    => $now,
            ]);

            $seq++;
            $generated++;
        }

        return ['generated' => $generated, 'skipped' => $skipped];
    }

    // -------------------------------------------------------------------------
    // Get all hall tickets for a batch exam (with student info)
    // -------------------------------------------------------------------------
    public function getByBatchExam($batch_exam_id)
    {
        return $this->db->query(
            "SELECT ht.*,
                    s.firstname, s.lastname, s.register_no, s.image AS student_image, s.dob, s.gender,
                    ss.id AS student_session_id,
                    c.class AS class_name,
                    d.department_name,
                    sec.section AS section_name
             FROM coe_hall_tickets ht
             LEFT JOIN students s        ON s.id           = ht.student_id
             LEFT JOIN student_session ss ON ss.student_id = ht.student_id
                                        AND ss.session_id = (SELECT session_id FROM exam_group_class_batch_exams WHERE id = ht.exam_group_class_batch_exam_id LIMIT 1)
             LEFT JOIN classes c         ON c.id           = ss.class_id
             LEFT JOIN department d      ON d.id           = ss.department_id
             LEFT JOIN sections sec      ON sec.id         = ss.section_id
             WHERE ht.exam_group_class_batch_exam_id = ?
             ORDER BY ht.hall_ticket_no ASC",
            [$batch_exam_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Get single hall ticket with full info (for PDF)
    // -------------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db->query(
            "SELECT ht.*,
                    s.firstname, s.lastname, s.register_no, s.image AS student_image, s.dob, s.gender, s.mobileno,
                    c.class AS class_name,
                    d.department_name,
                    sec.section AS section_name,
                    ebe.exam AS exam_name, ebe.date_from, ebe.date_to, ebe.session_id,
                    ses.session AS session_name,
                    eg.name AS exam_group_name
             FROM coe_hall_tickets ht
             LEFT JOIN students s              ON s.id  = ht.student_id
             LEFT JOIN exam_group_class_batch_exams ebe ON ebe.id = ht.exam_group_class_batch_exam_id
             LEFT JOIN sessions ses            ON ses.id = ebe.session_id
             LEFT JOIN exam_groups eg          ON eg.id  = ebe.exam_group_id
             LEFT JOIN student_session ss      ON ss.student_id = ht.student_id AND ss.session_id = ebe.session_id
             LEFT JOIN classes c               ON c.id   = ss.class_id
             LEFT JOIN department d            ON d.id   = ss.department_id
             LEFT JOIN sections sec            ON sec.id = ss.section_id
             WHERE ht.id = ?
             LIMIT 1",
            [(int)$id]
        )->row();
    }

    // -------------------------------------------------------------------------
    // Get subjects for a student in a batch exam (eligible ones only)
    // -------------------------------------------------------------------------
    public function getSubjectsForStudent($student_id, $batch_exam_id)
    {
        return $this->db->query(
            "SELECT app.id AS app_id, app.application_status, app.is_arrear,
                    app.cbcs_category,
                    sub.name AS subject_name, sub.code AS subject_code,
                    sub.type AS subject_type
             FROM coe_exam_applications app
             LEFT JOIN subjects sub ON sub.id = app.subject_id
             WHERE app.student_id = ?
               AND app.exam_group_class_batch_exam_id = ?
               AND app.application_status IN ('eligible', 'override_eligible')
             ORDER BY sub.name",
            [(int)$student_id, (int)$batch_exam_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Summary stats for a batch exam (for index page)
    // -------------------------------------------------------------------------
    public function getSummaryByBatchExam($batch_exam_id)
    {
        $batch_exam_id = (int)$batch_exam_id;

        $eligible_count = (int)$this->db->query(
            "SELECT COUNT(DISTINCT student_id) AS cnt FROM coe_exam_applications
             WHERE exam_group_class_batch_exam_id = ?
               AND application_status IN ('eligible', 'override_eligible')",
            [$batch_exam_id]
        )->row()->cnt;

        $generated_count = (int)$this->db->query(
            "SELECT COUNT(*) AS cnt FROM coe_hall_tickets WHERE exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row()->cnt;

        return [
            'eligible'  => $eligible_count,
            'generated' => $generated_count,
            'pending'   => max(0, $eligible_count - $generated_count),
            'complete'  => ($eligible_count > 0 && $generated_count >= $eligible_count),
        ];
    }

    // -------------------------------------------------------------------------
    // Invalidate a hall ticket
    // -------------------------------------------------------------------------
    public function invalidate($id)
    {
        $this->db->where('id', (int)$id);
        $this->db->update('coe_hall_tickets', ['is_valid' => 0]);
        return $this->db->affected_rows() > 0;
    }

    // -------------------------------------------------------------------------
    // Increment download counter
    // -------------------------------------------------------------------------
    public function incrementDownload($id)
    {
        $this->db->query(
            "UPDATE coe_hall_tickets SET downloaded_count = downloaded_count + 1, printed_at = NOW() WHERE id = ?",
            [(int)$id]
        );
    }

    // -------------------------------------------------------------------------
    // Get all events (batch_exams) that have been marked as COE
    // Used for the index page
    // -------------------------------------------------------------------------
    public function getCoeEvents($session_id = null)
    {
        $this->db->select(
            'ebe.id, ebe.exam AS exam_name, ebe.date_from, ebe.date_to, ebe.session_id,
             ebe.exam_group_id, eg.name AS exam_group_name,
             ses.session AS session_name'
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
    // Verify a hall ticket by qr_hash alone (public, no auth)
    // Returns full row with student + exam info, or false if not found
    // -------------------------------------------------------------------------
    public function verifyByHash($qr_hash)
    {
        return $this->db->query(
            "SELECT ht.*,
                    s.firstname, s.lastname, s.register_no, s.image AS student_image, s.gender, s.dob,
                    c.class AS class_name,
                    d.department_name,
                    sec.section AS section_name,
                    ebe.exam AS exam_name, ebe.date_from, ebe.date_to,
                    ses.session AS session_name,
                    eg.name AS exam_group_name,
                    sch.name AS school_name, sch.address AS school_address, sch.admin_logo
             FROM coe_hall_tickets ht
             LEFT JOIN students s                        ON s.id   = ht.student_id
             LEFT JOIN exam_group_class_batch_exams ebe  ON ebe.id = ht.exam_group_class_batch_exam_id
             LEFT JOIN sessions ses                      ON ses.id = ebe.session_id
             LEFT JOIN exam_groups eg                    ON eg.id  = ebe.exam_group_id
             LEFT JOIN student_session ss                ON ss.student_id = ht.student_id AND ss.session_id = ebe.session_id
             LEFT JOIN classes c                         ON c.id   = ss.class_id
             LEFT JOIN department d                      ON d.id   = ss.department_id
             LEFT JOIN sections sec                      ON sec.id = ss.section_id
             LEFT JOIN sch_settings sch                  ON sch.id = 1
             WHERE ht.qr_hash = ?
             LIMIT 1",
            [$qr_hash]
        )->row();
    }

    // -------------------------------------------------------------------------
    // Verify a hall ticket QR hash (legacy, kept for compatibility)
    // -------------------------------------------------------------------------
    public function verifyQR($hall_ticket_no, $qr_hash)
    {
        $ht = $this->db->get_where('coe_hall_tickets', [
            'hall_ticket_no' => $hall_ticket_no,
            'qr_hash'        => $qr_hash,
            'is_valid'       => 1,
        ])->row();
        return $ht ?: false;
    }
}
