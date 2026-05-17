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
            ->select('eg.id AS exam_group_id, eg.name AS exam_group_name, eg.exam_category, eg.exam_type, eg.is_active, egcbe.id AS batch_exam_id, egcbe.exam, egcbe.class_id, egcbe.date_from, egcbe.date_to, egcbe.session_id, egcbe.is_publish, egcbe.coe_locked, s.session, c.class AS class_name, (SELECT COUNT(*) FROM coe_exam_applications capp WHERE capp.exam_group_class_batch_exam_id = egcbe.id) AS application_count')
            ->from('exam_groups eg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.exam_group_id = eg.id')
            ->join('sessions s', 's.id = egcbe.session_id', 'left')
            ->join('classes c', 'c.id = egcbe.class_id', 'left')
            ->where('egcbe.session_id', $session_id)
            ->where('eg.is_end_semester', 1)
            ->where('eg.is_active', 1)
            ->order_by('eg.id DESC, egcbe.date_from DESC')
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
            // For arrear exams we also auto-enroll any student found in results
            // who isn't yet in exam_group_class_batch_exam_students.

            $subject_ids_cfg = array_column((array)$subjects, 'subject_id');
            $sub_list        = implode(',', array_map('intval', $subject_ids_cfg));

            // Get batch class_id for scoping
            $batch_row    = $this->db->where('id', $batch_exam_id)->get('exam_group_class_batch_exams')->row();
            $class_id_flt = !empty($batch_row->class_id) ? (int) $batch_row->class_id : null;
            $class_filter = $class_id_flt ? "AND egcbe.class_id = {$class_id_flt}" : '';

            $sql = "
                SELECT DISTINCT sr.student_id, sr.subject_id
                FROM coe_student_results sr
                JOIN exam_group_class_batch_exams egcbe
                    ON egcbe.id = sr.exam_group_class_batch_exam_id
                JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
                WHERE sr.result_status   = 'fail'
                  AND eg.is_end_semester = 1
                  AND sr.subject_id IN ({$sub_list})
                  {$class_filter}
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

            // Auto-enroll any arrear students not yet in the student pool
            $all_arrear_student_ids = array_unique(array_column((array)$arrear_pairs, 'student_id'));
            foreach ($all_arrear_student_ids as $stu_id) {
                $stu_id = (int) $stu_id;
                if (!isset($student_map[$stu_id])) {
                    // Find their student_session for this batch's session
                    $ss = $this->db
                        ->select('id AS student_session_id, student_id')
                        ->from('student_session')
                        ->where('student_id', $stu_id)
                        ->where('session_id', $batch_row->session_id)
                        ->where('is_active !=', 'no')
                        ->get()->row();

                    // If no session row for this session, use the latest active one
                    if (!$ss) {
                        $ss = $this->db
                            ->select('id AS student_session_id, student_id')
                            ->from('student_session')
                            ->where('student_id', $stu_id)
                            ->where('is_active !=', 'no')
                            ->order_by('id', 'DESC')
                            ->limit(1)
                            ->get()->row();
                    }

                    if ($ss) {
                        $enrolled_chk = $this->db
                            ->where('exam_group_class_batch_exam_id', $batch_exam_id)
                            ->where('student_id', $stu_id)
                            ->count_all_results('exam_group_class_batch_exam_students');
                        if ($enrolled_chk === 0) {
                            $this->db->insert('exam_group_class_batch_exam_students', [
                                'exam_group_class_batch_exam_id' => $batch_exam_id,
                                'student_id'                     => $ss->student_id,
                                'student_session_id'             => $ss->student_session_id,
                                'is_active'                      => 1,
                            ]);
                        }
                        $student_map[$stu_id] = $ss;
                    }
                }
            }

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
        // Restrict to configured CoE subjects when provided
        if (!empty($filters['subject_ids']) && is_array($filters['subject_ids'])) {
            $this->db->where_in('ca.subject_id', array_map('intval', $filters['subject_ids']));
        }

        return $this->db->order_by('st.firstname ASC, sub.name ASC')->get()->result();
    }

    public function getApplicationStats($batch_exam_id, $subject_ids = [])
    {
        $where = 'exam_group_class_batch_exam_id = ' . (int)$batch_exam_id;
        if (!empty($subject_ids)) {
            $ids   = implode(',', array_map('intval', $subject_ids));
            $where .= ' AND subject_id IN (' . $ids . ')';
        }
        return $this->db->query(
            "SELECT
                COUNT(*) AS total,
                COUNT(DISTINCT student_session_id) AS total_students,
                SUM(application_status='eligible') AS eligible_count,
                COUNT(DISTINCT CASE WHEN application_status='eligible' THEN student_session_id END) AS eligible_students,
                SUM(application_status='ineligible') AS ineligible_count,
                COUNT(DISTINCT CASE WHEN application_status='ineligible' THEN student_session_id END) AS ineligible_students,
                SUM(application_status='override_eligible') AS override_count,
                COUNT(DISTINCT CASE WHEN application_status='override_eligible' THEN student_session_id END) AS override_students,
                SUM(application_status='pending') AS pending_count,
                COUNT(DISTINCT CASE WHEN application_status='pending' THEN student_session_id END) AS pending_students,
                COUNT(DISTINCT CASE WHEN ineligible_reason='both' THEN student_session_id END) AS both_fail_students,
                SUM(ineligible_reason='both') AS both_fail_count
             FROM coe_exam_applications
             WHERE {$where}"
        )->row();
    }

    // ------------------------------------------------------------------
    // SUBJECT SETUP — list, save, detect
    // ------------------------------------------------------------------

    /**
     * Return subjects that have active (uncleared) arrears for this batch's class,
     * annotated with arrear_count and whether they are already configured.
     */
    public function getSubjectsWithArrears($batch_exam_id)
    {
        $batch    = $this->db->where('id', $batch_exam_id)->get('exam_group_class_batch_exams')->row();
        if (!$batch) {
            return (object)['subjects' => [], 'configured_ids' => []];
        }

        $class_id     = !empty($batch->class_id) ? (int) $batch->class_id : null;
        $class_filter = $class_id ? "AND egcbe.class_id = {$class_id}" : '';

        // Subjects with at least one net-fail (fail with no subsequent pass)
        $sql = "
            SELECT sub.id, sub.name, sub.code, sub.type,
                   COUNT(DISTINCT sr.student_id) AS arrear_count
            FROM coe_student_results sr
            JOIN exam_group_class_batch_exams egcbe ON egcbe.id = sr.exam_group_class_batch_exam_id
            JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
            JOIN subjects sub ON sub.id = sr.subject_id
            WHERE sr.result_status = 'fail'
              AND eg.is_end_semester = 1
              {$class_filter}
              AND NOT EXISTS (
                  SELECT 1 FROM coe_student_results sr2
                  JOIN exam_group_class_batch_exams e2 ON e2.id = sr2.exam_group_class_batch_exam_id
                  WHERE sr2.student_id = sr.student_id
                    AND sr2.subject_id = sr.subject_id
                    AND sr2.result_status = 'pass'
                    AND e2.date_from > egcbe.date_from
              )
            GROUP BY sub.id
            ORDER BY sub.name
        ";
        $subjects = $this->db->query($sql)->result();

        // Already-configured subject IDs for this batch
        $configured_rows = $this->db
            ->select('subject_id')
            ->where('exam_group_class_batch_exams_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_subjects')
            ->result_array();
        $configured_ids  = array_column($configured_rows, 'subject_id');
        $configured_set  = array_flip($configured_ids);

        // Flag each subject and also surface any manually-added ones not in results
        foreach ($subjects as $s) {
            $s->is_configured = isset($configured_set[$s->id]) ? 1 : 0;
        }
        $found_ids = array_column((array) $subjects, 'id');
        foreach ($configured_ids as $sub_id) {
            if (!in_array($sub_id, $found_ids)) {
                $extra = $this->db->select('id, name, code, type')->where('id', $sub_id)->get('subjects')->row();
                if ($extra) {
                    $extra->arrear_count  = 0;
                    $extra->is_configured = 1;
                    $subjects[]           = $extra;
                }
            }
        }

        return (object)['subjects' => $subjects, 'configured_ids' => $configured_ids];
    }

    /**
     * Save (replace) subject list for a batch exam.
     * Deactivates existing, re-activates or inserts the new selection.
     */
    public function saveBatchSubjects($batch_exam_id, array $subject_ids, $date_from)
    {
        // Deactivate all current
        $this->db->where('exam_group_class_batch_exams_id', $batch_exam_id)
                 ->update('exam_group_class_batch_exam_subjects', ['is_active' => 0]);

        foreach ($subject_ids as $sub_id) {
            $sub_id = (int) $sub_id;
            $exists = $this->db
                ->where('exam_group_class_batch_exams_id', $batch_exam_id)
                ->where('subject_id', $sub_id)
                ->count_all_results('exam_group_class_batch_exam_subjects');

            if ($exists > 0) {
                $this->db->where('exam_group_class_batch_exams_id', $batch_exam_id)
                         ->where('subject_id', $sub_id)
                         ->update('exam_group_class_batch_exam_subjects', ['is_active' => 1]);
            } else {
                $this->db->insert('exam_group_class_batch_exam_subjects', [
                    'exam_group_class_batch_exams_id' => $batch_exam_id,
                    'subject_id'                      => $sub_id,
                    'date_from'                       => $date_from,
                    'time_from'                       => '09:00:00',
                    'duration'                        => '3 Hours',
                    'is_active'                       => 1,
                ]);
            }
        }
    }

    /**
     * Return arrear candidates grouped by student, for the configured subjects
     * of this batch exam. Each student entry lists the subjects they have
     * active (uncleared) arrears in.
     */
    public function getArrearCandidates($batch_exam_id)
    {
        $subject_rows = $this->db
            ->select('subject_id')
            ->where('exam_group_class_batch_exams_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_subjects')
            ->result_array();
        $subject_ids  = array_column($subject_rows, 'subject_id');

        if (empty($subject_ids)) {
            return ['students' => [], 'total_pairs' => 0, 'subject_ids' => []];
        }

        $sub_list = implode(',', array_map('intval', $subject_ids));

        $batch    = $this->db->where('id', $batch_exam_id)->get('exam_group_class_batch_exams')->row();
        $class_id = !empty($batch->class_id) ? (int) $batch->class_id : null;
        $class_filter = $class_id ? "AND egcbe.class_id = {$class_id}" : '';

        $sql = "
            SELECT DISTINCT sr.student_id, sr.subject_id,
                   st.firstname, st.lastname, st.register_no,
                   sub.name AS subject_name, sub.code AS subject_code
            FROM coe_student_results sr
            JOIN exam_group_class_batch_exams egcbe ON egcbe.id = sr.exam_group_class_batch_exam_id
            JOIN exam_groups eg ON eg.id = egcbe.exam_group_id
            JOIN students st ON st.id = sr.student_id
            JOIN subjects sub ON sub.id = sr.subject_id
            WHERE sr.result_status = 'fail'
              AND eg.is_end_semester = 1
              AND sr.subject_id IN ({$sub_list})
              {$class_filter}
              AND NOT EXISTS (
                  SELECT 1 FROM coe_student_results sr2
                  JOIN exam_group_class_batch_exams e2 ON e2.id = sr2.exam_group_class_batch_exam_id
                  WHERE sr2.student_id = sr.student_id
                    AND sr2.subject_id = sr.subject_id
                    AND sr2.result_status = 'pass'
                    AND e2.date_from > egcbe.date_from
              )
            ORDER BY st.firstname, st.lastname, sub.name
        ";

        $rows = $this->db->query($sql)->result();

        $students    = [];
        $total_pairs = count($rows);
        foreach ($rows as $row) {
            $sid = $row->student_id;
            if (!isset($students[$sid])) {
                $students[$sid] = [
                    'student_id'  => $sid,
                    'firstname'   => $row->firstname,
                    'lastname'    => $row->lastname,
                    'register_no' => $row->register_no,
                    'subjects'    => [],
                ];
            }
            $students[$sid]['subjects'][] = [
                'subject_id'   => $row->subject_id,
                'subject_name' => $row->subject_name,
                'subject_code' => $row->subject_code,
            ];
        }

        return [
            'students'    => array_values($students),
            'total_pairs' => $total_pairs,
            'subject_ids' => $subject_ids,
        ];
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
