<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_nominalroll_model
 * Handles nominal roll generation and management.
 * Each roll covers one subject within a batch exam.
 */
class Coe_nominalroll_model extends CI_Model
{
    // -------------------------------------------------------------------------
    // Generate nominal rolls for all subjects in a batch exam
    // Idempotent: updates existing rolls, creates new ones
    // Returns ['created' => n, 'updated' => n]
    // -------------------------------------------------------------------------
    public function generate($batch_exam_id, $staff_id = 0)
    {
        $batch_exam_id = (int)$batch_exam_id;

        $batch_exam = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            return ['created' => 0, 'updated' => 0, 'error' => 'Exam event not found'];
        }

        // Get distinct subjects with eligible students
        $subjects = $this->db->query(
            "SELECT DISTINCT subject_id FROM coe_exam_applications
             WHERE exam_group_class_batch_exam_id = ?
               AND application_status IN ('eligible','override_eligible')
             ORDER BY subject_id",
            [$batch_exam_id]
        )->result_array();

        $created = 0;
        $updated = 0;
        $exam_date = $batch_exam->date_from ?: date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        foreach ($subjects as $subj) {
            $subject_id = (int)$subj['subject_id'];

            // Get all eligible students for this subject
            $students = $this->db->query(
                "SELECT app.student_id, app.application_status, app.is_arrear, app.cbcs_category,
                        s.firstname, s.lastname, s.register_no, s.gender, s.dob,
                        ss.id AS student_session_id,
                        c.class AS class_name, d.department_name, sec.section AS section_name
                 FROM coe_exam_applications app
                 LEFT JOIN students s        ON s.id = app.student_id
                 LEFT JOIN student_session ss ON ss.student_id = app.student_id
                                             AND ss.session_id = ?
                 LEFT JOIN classes c         ON c.id = ss.class_id
                 LEFT JOIN department d      ON d.id = ss.department_id
                 LEFT JOIN sections sec      ON sec.id = ss.section_id
                 WHERE app.exam_group_class_batch_exam_id = ?
                   AND app.subject_id = ?
                   AND app.application_status IN ('eligible','override_eligible')
                 ORDER BY s.register_no, s.firstname",
                [$batch_exam->session_id, $batch_exam_id, $subject_id]
            )->result_array();

            $total     = count($students);
            $snapshot  = json_encode($students, JSON_UNESCAPED_UNICODE);

            // Check if roll already exists
            $existing = $this->db->get_where('coe_nominal_rolls', [
                'exam_group_class_batch_exam_id' => $batch_exam_id,
                'subject_id'                     => $subject_id,
            ])->row();

            if ($existing) {
                if ($existing->is_final) {
                    // Skip finalized rolls
                    continue;
                }
                $this->db->where('id', $existing->id);
                $this->db->update('coe_nominal_rolls', [
                    'total_students' => $total,
                    'roll_snapshot'  => $snapshot,
                    'generated_by'   => (int)$staff_id,
                    'generated_at'   => $now,
                ]);
                $updated++;
            } else {
                $this->db->insert('coe_nominal_rolls', [
                    'exam_group_id'                  => $batch_exam->exam_group_id,
                    'exam_group_class_batch_exam_id' => $batch_exam_id,
                    'subject_id'                     => $subject_id,
                    'exam_date'                      => $exam_date,
                    'total_students'                 => $total,
                    'generated_by'                   => (int)$staff_id,
                    'generated_at'                   => $now,
                    'is_final'                       => 0,
                    'roll_snapshot'                  => $snapshot,
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    // -------------------------------------------------------------------------
    // Get all nominal rolls for a batch exam (with subject name)
    // -------------------------------------------------------------------------
    public function getByBatchExam($batch_exam_id)
    {
        return $this->db->query(
            "SELECT nr.*, sub.name AS subject_name, sub.code AS subject_code, sub.type AS subject_type
             FROM coe_nominal_rolls nr
             LEFT JOIN subjects sub ON sub.id = nr.subject_id
             WHERE nr.exam_group_class_batch_exam_id = ?
             ORDER BY sub.name",
            [(int)$batch_exam_id]
        )->result();
    }

    // -------------------------------------------------------------------------
    // Get a single nominal roll by ID
    // -------------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db->query(
            "SELECT nr.*, sub.name AS subject_name, sub.code AS subject_code, sub.type AS subject_type,
                    ebe.exam AS exam_name, ebe.date_from, ebe.date_to, ebe.session_id,
                    ses.session AS session_name,
                    eg.name AS exam_group_name
             FROM coe_nominal_rolls nr
             LEFT JOIN subjects sub ON sub.id = nr.subject_id
             LEFT JOIN exam_group_class_batch_exams ebe ON ebe.id = nr.exam_group_class_batch_exam_id
             LEFT JOIN sessions ses ON ses.id = ebe.session_id
             LEFT JOIN exam_groups eg ON eg.id = nr.exam_group_id
             WHERE nr.id = ?
             LIMIT 1",
            [(int)$id]
        )->row();
    }

    // -------------------------------------------------------------------------
    // Finalize a roll (lock it)
    // -------------------------------------------------------------------------
    public function finalize($id)
    {
        $this->db->where('id', (int)$id);
        $this->db->update('coe_nominal_rolls', ['is_final' => 1]);
        return $this->db->affected_rows() > 0;
    }

    // -------------------------------------------------------------------------
    // Summary stats per batch exam
    // -------------------------------------------------------------------------
    public function getSummary($batch_exam_id)
    {
        $batch_exam_id = (int)$batch_exam_id;

        $row = $this->db->query(
            "SELECT COUNT(*) AS total_rolls,
                    SUM(is_final) AS finalized,
                    SUM(total_students) AS total_students
             FROM coe_nominal_rolls
             WHERE exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row();

        $subjects = (int)$this->db->query(
            "SELECT COUNT(DISTINCT subject_id) AS cnt FROM coe_exam_applications
             WHERE exam_group_class_batch_exam_id = ?
               AND application_status IN ('eligible','override_eligible')",
            [$batch_exam_id]
        )->row()->cnt;

        return [
            'subjects'     => $subjects,
            'total_rolls'  => (int)($row->total_rolls ?? 0),
            'finalized'    => (int)($row->finalized ?? 0),
            'pending'      => $subjects - (int)($row->total_rolls ?? 0),
            'total_students' => (int)($row->total_students ?? 0),
        ];
    }

    // -------------------------------------------------------------------------
    // Get CoE events (same as hallticket model — for index page)
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
        $this->db->where('ebe.is_end_semester', 1);
        $this->db->where('ebe.is_active', 1);
        if ($session_id) {
            $this->db->where('ebe.session_id', (int)$session_id);
        }
        $this->db->order_by('ebe.date_from', 'DESC');
        return $this->db->get()->result();
    }
}
