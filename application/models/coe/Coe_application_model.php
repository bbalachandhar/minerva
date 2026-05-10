<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_application_model
 *
 * Handles exam events (exam_group + exam_group_class_batch_exams combinations
 * flagged as CoE end-semester) and bulk generation of student application rows
 * in coe_exam_applications.
 */
class Coe_application_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // EXAM EVENTS — list exam_groups marked is_end_semester=1
    // ------------------------------------------------------------------

    /**
     * Return all end-semester exam groups for a session.
     */
    public function getExamEventsBySession($session_id)
    {
        return $this->db
            ->select('eg.id, eg.name, eg.exam_category, eg.exam_type, eg.is_active, egcbe.id AS batch_exam_id, egcbe.exam, egcbe.date_from, egcbe.date_to, egcbe.session_id, egcbe.is_publish, egcbe.coe_locked, s.session, (SELECT COUNT(*) FROM coe_exam_applications capp WHERE capp.exam_group_class_batch_exam_id = egcbe.id) AS application_count')
            ->from('exam_groups eg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.exam_group_id = eg.id')
            ->join('sessions s', 's.id = egcbe.session_id', 'left')
            ->where('egcbe.session_id', $session_id)
            ->where('eg.is_end_semester', 1)
            ->where('eg.is_active', 1)
            ->order_by('egcbe.date_from DESC')
            ->get()->result();
    }

    /**
     * Get a single batch_exam with its exam_group detail.
     */
    public function getExamEventById($batch_exam_id)
    {
        return $this->db
            ->select('eg.id AS exam_group_id, eg.name AS exam_group_name, eg.exam_category, eg.is_end_semester, egcbe.*, s.session')
            ->from('exam_group_class_batch_exams egcbe')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->join('sessions s', 's.id = egcbe.session_id', 'left')
            ->where('egcbe.id', $batch_exam_id)
            ->get()->result_array();
        // returns array; use [0] — or row() version below
    }

    public function getExamEventByIdRow($batch_exam_id)
    {
        return $this->db
            ->select('eg.id AS exam_group_id, eg.name AS exam_group_name, eg.exam_category, egcbe.*, s.session')
            ->from('exam_group_class_batch_exams egcbe')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->join('sessions s', 's.id = egcbe.session_id', 'left')
            ->where('egcbe.id', $batch_exam_id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // APPLICATION GENERATION
    // ------------------------------------------------------------------

    /**
     * Bulk-generate coe_exam_applications for all active students
     * in the exam batch, one row per (student, subject).
     *
     * If a row already exists (UNIQUE key: batch_exam_id, student_id, subject_id)
     * we skip it (INSERT IGNORE).
     *
     * @param int $batch_exam_id  exam_group_class_batch_exams.id
     * @return array ['inserted' => int, 'skipped' => int]
     */
    public function generateApplications($batch_exam_id, $exam_group_id)
    {
        // Get all subjects for this batch exam
        $subjects = $this->db
            ->where('exam_group_class_batch_exams_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_subjects')->result();

        if (empty($subjects)) {
            return ['inserted' => 0, 'skipped' => 0, 'error' => 'no_subjects'];
        }

        // Get all active students enrolled in this batch exam
        $students = $this->db
            ->where('exam_group_class_batch_exam_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_students')->result();

        if (empty($students)) {
            return ['inserted' => 0, 'skipped' => 0, 'error' => 'no_students'];
        }

        $inserted = 0;
        $skipped  = 0;
        $now      = date('Y-m-d H:i:s');

        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                // Check if already exists
                $exists = $this->db
                    ->where('exam_group_class_batch_exam_id', $batch_exam_id)
                    ->where('student_id', $student->student_id)
                    ->where('subject_id', $subject->subject_id)
                    ->count_all_results('coe_exam_applications');

                if ($exists > 0) {
                    $skipped++;
                    continue;
                }

                $this->db->insert('coe_exam_applications', [
                    'exam_group_id'                  => $exam_group_id,
                    'exam_group_class_batch_exam_id' => $batch_exam_id,
                    'student_id'                     => $student->student_id,
                    'student_session_id'             => $student->student_session_id,
                    'subject_id'                     => $subject->subject_id,
                    'is_arrear'                      => 0,
                    'cbcs_category'                  => 'core',
                    'application_status'             => 'pending',
                    'applied_at'                     => $now,
                ]);
                $inserted++;
            }
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    // ------------------------------------------------------------------
    // QUERIES FOR APPLICATION LISTING
    // ------------------------------------------------------------------

    public function getApplicationsByBatchExam($batch_exam_id, $filters = [])
    {
        $this->db
            ->select('ca.*, st.firstname, st.lastname, st.register_no, sub.name AS subject_name, sub.code AS subject_code')
            ->from('coe_exam_applications ca')
            ->join('students st', 'st.id = ca.student_id', 'left')
            ->join('subjects sub', 'sub.id = ca.subject_id', 'left')
            ->where('ca.exam_group_class_batch_exam_id', $batch_exam_id);

        if (!empty($filters['application_status'])) {
            $this->db->where('ca.application_status', $filters['application_status']);
        }
        if (!empty($filters['cbcs_category'])) {
            $this->db->where('ca.cbcs_category', $filters['cbcs_category']);
        }

        return $this->db->order_by('st.firstname ASC, sub.name ASC')->get()->result();
    }

    public function getApplicationStats($batch_exam_id)
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(application_status='eligible') AS eligible_count,
                SUM(application_status='ineligible') AS ineligible_count,
                SUM(application_status='override_eligible') AS override_count,
                SUM(application_status='pending') AS pending_count
             FROM coe_exam_applications
             WHERE exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row();
        return $row;
    }

    // ------------------------------------------------------------------
    // MARK exam_group as end-semester
    // ------------------------------------------------------------------

    public function markEndSemester($exam_group_id, $exam_category = 'main', $exam_type = 'theory')
    {
        $this->db->where('id', $exam_group_id)
            ->update('exam_groups', [
                'is_end_semester' => 1,
                'exam_category'   => $exam_category,
                'exam_type'       => $exam_type,
            ]);
    }
}
