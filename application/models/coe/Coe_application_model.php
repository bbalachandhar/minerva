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
            ->select('eg.id, eg.name, eg.exam_category, eg.exam_type, eg.is_active, egcbe.id AS batch_exam_id, egcbe.exam, egcbe.class_id, egcbe.date_from, egcbe.date_to, egcbe.session_id, egcbe.is_publish, egcbe.coe_locked, s.session, (SELECT COUNT(*) FROM coe_exam_applications capp WHERE capp.exam_group_class_batch_exam_id = egcbe.id) AS application_count')
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
        $batch_exam_id  = (int) $batch_exam_id;
        $exam_group_id  = (int) $exam_group_id;

        // Resolve exam_category from the exam_group so we can branch logic
        $eg = $this->db->select('exam_category')->from('exam_groups')
            ->where('id', $exam_group_id)->get()->row();
        $exam_category = $eg ? $eg->exam_category : 'main';

        // Subjects configured for this batch exam
        $subjects = $this->db
            ->where('exam_group_class_batch_exams_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_subjects')->result();

        if (empty($subjects)) {
            return ['inserted' => 0, 'skipped' => 0, 'error' => 'no_subjects'];
        }

        // Students enrolled in this batch exam (admin-controlled pool)
        $students = $this->db
            ->where('exam_group_class_batch_exam_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_students')->result();

        if (empty($students)) {
            return ['inserted' => 0, 'skipped' => 0, 'error' => 'no_students'];
        }

        // Index students by student_id for fast lookup
        $student_map = [];
        foreach ($students as $st) {
            $student_map[$st->student_id] = $st;
        }

        $inserted = 0;
        $skipped  = 0;
        $now      = date('Y-m-d H:i:s');

        // ---------------------------------------------------------------
        // ARREAR / SUPPLEMENTARY — smart per-subject eligibility check
        // ---------------------------------------------------------------
        if (in_array($exam_category, ['arrear', 'supplementary'], true)) {

            // Build one query to get all (student_id, subject_id) pairs that
            // have an active arrear among the enrolled student pool.
            //
            // A pair is an "active arrear" when:
            //   1. There exists a coe_student_results row with result_status='fail'
            //      for that (student, subject) in an end-semester exam.
            //   2. There is NO later coe_student_results row with result_status='pass'
            //      for the same (student, subject) — i.e. not yet cleared.

            $student_ids = array_column($students, 'student_id');
            $subject_ids = array_column($subjects,  'subject_id');

            $sid_list = implode(',', array_map('intval', $student_ids));
            $sub_list = implode(',', array_map('intval', $subject_ids));

            $sql = "
                SELECT DISTINCT sr.student_id, sr.subject_id
                FROM coe_student_results sr
                JOIN exam_group_class_batch_exams egcbe
                    ON egcbe.id = sr.exam_group_class_batch_exam_id
                JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
                WHERE sr.result_status   = 'fail'
                  AND eg.is_end_semester = 1
                  AND sr.student_id IN ({$sid_list})
                  AND sr.subject_id IN ({$sub_list})
                  -- Net arrear: no subsequent pass for same student+subject
                  AND NOT EXISTS (
                      SELECT 1
                      FROM coe_student_results sr2
                      JOIN exam_group_class_batch_exams e2
                          ON e2.id = sr2.exam_group_class_batch_exam_id
                      WHERE sr2.student_id    = sr.student_id
                        AND sr2.subject_id    = sr.subject_id
                        AND sr2.result_status = 'pass'
                        AND e2.date_from      > egcbe.date_from
                  )
            ";

            $arrear_pairs = $this->db->query($sql)->result();

            foreach ($arrear_pairs as $pair) {
                $student = $student_map[$pair->student_id] ?? null;
                if (!$student) {
                    continue; // student no longer in enrolled pool
                }

                $exists = $this->db
                    ->where('exam_group_class_batch_exam_id', $batch_exam_id)
                    ->where('student_id', $pair->student_id)
                    ->where('subject_id', $pair->subject_id)
                    ->count_all_results('coe_exam_applications');

                if ($exists > 0) {
                    $skipped++;
                    continue;
                }

                $this->db->insert('coe_exam_applications', [
                    'exam_group_id'                  => $exam_group_id,
                    'exam_group_class_batch_exam_id' => $batch_exam_id,
                    'student_id'                     => $pair->student_id,
                    'student_session_id'             => $student->student_session_id,
                    'subject_id'                     => $pair->subject_id,
                    'is_arrear'                      => 1,
                    'cbcs_category'                  => 'core',
                    'application_status'             => 'pending',
                    'applied_at'                     => $now,
                ]);
                $inserted++;
            }

            return ['inserted' => $inserted, 'skipped' => $skipped, 'mode' => 'arrear'];
        }

        // ---------------------------------------------------------------
        // REGULAR (main) exam — enroll every student for every subject
        // ---------------------------------------------------------------
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
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

        return ['inserted' => $inserted, 'skipped' => $skipped, 'mode' => 'regular'];
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
