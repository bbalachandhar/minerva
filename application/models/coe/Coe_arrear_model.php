<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Coe_arrear_model
 * Arrear register — students with failed subjects across exam events.
 */
class Coe_arrear_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * List all students with at least one arrear for a given session.
     * Returns one row per student with aggregate counts.
     */
    public function getArrearList($session_id, $filters = [])
    {
        $sid = (int) $session_id;

        $this->db
            ->select('
                s.id AS student_id,
                CONCAT(s.firstname," ",s.lastname) AS student_name,
                s.register_no, s.admission_no,
                c.class AS class_name,
                d.department_name,
                COUNT(sr.id) AS arrear_count,
                GROUP_CONCAT(DISTINCT sub.code ORDER BY sub.code SEPARATOR ", ") AS arrear_subjects
            ')
            ->from('coe_student_results sr')
            ->join('students s', 's.id = sr.student_id', 'left')
            ->join('subjects sub', 'sub.id = sr.subject_id', 'left')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sr.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->join('student_session ss', 'ss.student_id = s.id AND ss.session_id = egcbe.session_id', 'left')
            ->join('classes c', 'c.id = ss.class_id', 'left')
            ->join('department d', 'd.id = c.department_id', 'left')
            ->where('egcbe.session_id', $sid)
            ->where('eg.is_end_semester', 1)
            ->where('sr.result_status', 'fail');

        if (!empty($filters['batch_exam_id'])) {
            $this->db->where('sr.exam_group_class_batch_exam_id', (int) $filters['batch_exam_id']);
        }
        if (!empty($filters['department_id'])) {
            $this->db->where('d.id', (int) $filters['department_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('ss.class_id', (int) $filters['class_id']);
        }
        if (!empty($filters['search'])) {
            $q = $this->db->escape_like_str($filters['search']);
            $this->db->group_start()
                ->like('s.firstname', $q)
                ->or_like('s.lastname', $q)
                ->or_like('s.register_no', $q)
                ->or_like('s.admission_no', $q)
                ->group_end();
        }

        return $this->db
            ->group_by('sr.student_id')
            ->order_by('arrear_count', 'DESC')
            ->order_by('student_name', 'ASC')
            ->get()->result();
    }

    /**
     * Arrear history for one student — all (or filtered) failed subjects.
     *
     * @param int   $student_id
     * @param array $filters  Keys: session_id, batch_exam_id, active_only (bool)
     */
    public function getStudentArrears($student_id, $filters = [])
    {
        $student_id = (int) $student_id;

        $where_parts = [
            "sr.student_id   = $student_id",
            "sr.result_status = 'fail'",
            "eg.is_end_semester = 1",
        ];

        if (!empty($filters['session_id'])) {
            $where_parts[] = 'egcbe.session_id = ' . (int) $filters['session_id'];
        }
        if (!empty($filters['batch_exam_id'])) {
            $where_parts[] = 'sr.exam_group_class_batch_exam_id = ' . (int) $filters['batch_exam_id'];
        }

        // "Active only" — exclude subjects where a subsequent pass exists
        $active_clause = '';
        if (!empty($filters['active_only'])) {
            $active_clause = "AND NOT EXISTS (
                SELECT 1
                FROM coe_student_results sr2
                JOIN exam_group_class_batch_exams e2 ON e2.id = sr2.exam_group_class_batch_exam_id
                WHERE sr2.student_id    = sr.student_id
                  AND sr2.subject_id    = sr.subject_id
                  AND sr2.result_status = 'pass'
                  AND e2.date_from      > egcbe.date_from
            )";
        }

        $where_sql = implode(' AND ', $where_parts);

        $sql = "
            SELECT sr.id, sr.subject_id, sr.internal_marks, sr.external_marks,
                   sr.total_marks, sr.grade, sr.result_status, sr.moderation_applied,
                   sub.code AS subject_code, sub.name AS subject_name,
                   egcbe.id AS batch_exam_id, egcbe.exam AS batch_exam_name,
                   egcbe.date_from, egcbe.date_to, egcbe.session_id,
                   s.session, eg.name AS event_name
            FROM coe_student_results sr
            JOIN subjects sub                        ON sub.id  = sr.subject_id
            JOIN exam_group_class_batch_exams egcbe  ON egcbe.id = sr.exam_group_class_batch_exam_id
            JOIN exam_groups eg                       ON eg.id   = egcbe.exam_group_id
            JOIN sessions s                           ON s.id    = egcbe.session_id
            WHERE $where_sql
            $active_clause
            ORDER BY egcbe.date_from ASC, sub.code ASC
        ";

        return $this->db->query($sql)->result();
    }

    /**
     * All academic sessions in which this student has any end-semester results.
     */
    public function getStudentResultSessions($student_id)
    {
        $sql = "
            SELECT DISTINCT s.id, s.session
            FROM coe_student_results sr
            JOIN exam_group_class_batch_exams egcbe ON egcbe.id = sr.exam_group_class_batch_exam_id
            JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
            JOIN sessions s    ON s.id  = egcbe.session_id
            WHERE sr.student_id     = ?
              AND eg.is_end_semester = 1
            ORDER BY s.id DESC
        ";
        return $this->db->query($sql, [(int) $student_id])->result();
    }

    /**
     * Exam events (batch exams) for a student in a given session,
     * where they have end-semester results.
     */
    public function getStudentResultEvents($student_id, $session_id = null)
    {
        $sid = (int) $student_id;
        $cond = $session_id ? 'AND egcbe.session_id = ' . (int)$session_id : '';
        $sql = "
            SELECT DISTINCT egcbe.id AS batch_exam_id,
                            CONCAT(eg.name, ' — ', egcbe.exam) AS label
            FROM coe_student_results sr
            JOIN exam_group_class_batch_exams egcbe ON egcbe.id = sr.exam_group_class_batch_exam_id
            JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
            WHERE sr.student_id     = $sid
              AND eg.is_end_semester = 1
              $cond
            ORDER BY egcbe.date_from DESC
        ";
        return $this->db->query($sql)->result();
    }

    /**
     * Get student info
     */
    public function getStudentInfo($student_id)
    {
        $sid = (int) $student_id;
        $sql = "SELECT s.*,
                       CONCAT(s.firstname,' ',s.lastname) AS full_name,
                       c.class AS class_name,
                       d.department_name
                FROM students s
                LEFT JOIN student_session ss
                    ON ss.student_id = s.id
                    AND ss.id = (SELECT id FROM student_session
                                 WHERE student_id = s.id
                                 ORDER BY session_id DESC LIMIT 1)
                LEFT JOIN classes c ON c.id = ss.class_id
                LEFT JOIN department d ON d.id = c.department_id
                WHERE s.id = ?";
        return $this->db->query($sql, [$sid])->row();
    }

    /**
     * Full SGPA history for one student (all semesters, optional session filter)
     */
    public function getStudentSGPAHistory($student_id, $session_id = null)
    {
        $q = $this->db
            ->select('sg.sgpa, sg.cgpa, sg.arrear_count, sg.result_status,
                      sg.total_credits_earned, sg.total_credits_registered,
                      egcbe.exam AS batch_exam_name, s.session')
            ->from('coe_sgpa_summary sg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sg.exam_group_class_batch_exam_id')
            ->join('sessions s', 's.id = egcbe.session_id', 'left')
            ->where('sg.student_id', (int) $student_id);
        if ($session_id) {
            $q->where('egcbe.session_id', (int) $session_id);
        }
        return $q->order_by('egcbe.date_from', 'ASC')->get()->result();
    }

    /**
     * Departments for filter dropdown
     */
    public function getDepartments()
    {
        return $this->db->select('id, department_name')->from('department')
            ->where('is_active', 1)->order_by('department_name')->get()->result();
    }
}
