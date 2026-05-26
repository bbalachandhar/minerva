<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_marks_model
 * Student results, subject config, SGPA/CGPA computation.
 *
 * Anna University NEP 2020 grading:
 *   O  >= 91 → 10 pts
 *   A+ >= 81 → 9  pts
 *   A  >= 71 → 8  pts
 *   B+ >= 61 → 7  pts
 *   B  >= 51 → 6  pts
 *   C  == 50 → 5  pts
 *   U  <  50 → 0  pts (fail)
 *
 * Fail conditions: external < 28 (of 70) OR total < 50
 */
class Coe_marks_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // List student results for a batch exam
    // ------------------------------------------------------------------
    public function getResults($batch_exam_id, $filters = [])
    {
        $this->db
            ->select('sr.*, CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      st.admission_no, sub.name AS subject_name, sub.code AS subject_code')
            ->from('coe_student_results sr')
            ->join('students st',  'st.id = sr.student_id',  'left')
            ->join('subjects sub', 'sub.id = sr.subject_id', 'left')
            ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id);

        if (!empty($filters['subject_id'])) {
            $this->db->where('sr.subject_id', (int) $filters['subject_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('sr.result_status', $filters['status']);
        }
        if (!empty($filters['student_id'])) {
            $this->db->where('sr.student_id', (int) $filters['student_id']);
        }

        return $this->db->order_by('st.firstname, sub.code')->get()->result();
    }

    // ------------------------------------------------------------------
    // Get single result row
    // ------------------------------------------------------------------
    public function getResultById($id)
    {
        return $this->db
            ->select('sr.*, CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      st.admission_no, sub.name AS subject_name, sub.code AS subject_code')
            ->from('coe_student_results sr')
            ->join('students st',  'st.id = sr.student_id',  'left')
            ->join('subjects sub', 'sub.id = sr.subject_id', 'left')
            ->where('sr.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Upsert a student result row (insert or update)
    // ------------------------------------------------------------------
    public function saveResult($batch_exam_id, $student_id, $subject_id, $internal, $external)
    {
        $existing = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('student_id',  (int) $student_id)
            ->where('subject_id',  (int) $subject_id)
            ->get('coe_student_results')->row();

        $total  = (float) $internal + (float) $external;
        $grade  = $this->computeGrade($total, $external);

        $data = [
            'internal_marks' => (float) $internal,
            'external_marks' => (float) $external,
            'total_marks'    => $total,
            'grade'          => $grade['grade'],
            'grade_points'   => $grade['points'],
            'result_status'  => $grade['fail'] ? 'fail' : 'pass',
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('coe_student_results', $data);
            return $existing->id;
        }

        $insert = array_merge($data, [
            'exam_group_class_batch_exam_id' => (int) $batch_exam_id,
            'student_id'                     => (int) $student_id,
            'subject_id'                     => (int) $subject_id,
            'is_published'                   => 0,
        ]);
        $this->db->insert('coe_student_results', $insert);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Compute grade from total (and external, for fail check)
    // Returns ['grade' => 'A+', 'points' => 9, 'fail' => false]
    // ------------------------------------------------------------------
    public function computeGrade($total, $external = null)
    {
        $total    = (float) $total;
        $external = ($external !== null) ? (float) $external : null;

        // Fail conditions
        $fail = ($total < 50) || ($external !== null && $external < 28);

        if ($fail) {
            return ['grade' => 'U', 'points' => 0, 'fail' => true];
        }

        if ($total >= 91) { return ['grade' => 'O',  'points' => 10, 'fail' => false]; }
        if ($total >= 81) { return ['grade' => 'A+', 'points' => 9,  'fail' => false]; }
        if ($total >= 71) { return ['grade' => 'A',  'points' => 8,  'fail' => false]; }
        if ($total >= 61) { return ['grade' => 'B+', 'points' => 7,  'fail' => false]; }
        if ($total >= 51) { return ['grade' => 'B',  'points' => 6,  'fail' => false]; }
        // exactly 50
        return ['grade' => 'C', 'points' => 5, 'fail' => false];
    }

    // ------------------------------------------------------------------
    // Recompute grades for all results in a batch exam
    // ------------------------------------------------------------------
    public function recomputeGrades($batch_exam_id)
    {
        $rows = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->get('coe_student_results')->result();

        $updated = 0;
        foreach ($rows as $row) {
            $grade = $this->computeGrade($row->total_marks, $row->external_marks);
            $this->db->where('id', $row->id)->update('coe_student_results', [
                'grade'        => $grade['grade'],
                'grade_points' => $grade['points'],
                'result_status' => $grade['fail'] ? 'fail' : 'pass',
            ]);
            $updated++;
        }
        return $updated;
    }

    // ------------------------------------------------------------------
    // Subject config — get all for a batch exam
    // ------------------------------------------------------------------
    public function getSubjectConfigs($batch_exam_id)
    {
        return $this->db
            ->select('sc.*, sub.name AS subject_name, sub.code AS subject_code')
            ->from('coe_subject_config sc')
            ->join('subjects sub', 'sub.id = sc.subject_id', 'left')
            ->where('sc.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('sub.code')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Subject config — upsert
    // ------------------------------------------------------------------
    public function saveSubjectConfig($batch_exam_id, $subject_id, $credits, $max_internal, $max_external, $pass_internal, $pass_external)
    {
        $existing = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('subject_id', (int) $subject_id)
            ->get('coe_subject_config')->row();

        $data = [
            'credits'       => (float) $credits,
            'max_internal'  => (float) $max_internal,
            'max_external'  => (float) $max_external,
            'pass_internal' => (float) $pass_internal,
            'pass_external' => (float) $pass_external,
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('coe_subject_config', $data);
        } else {
            $data['exam_group_class_batch_exam_id'] = (int) $batch_exam_id;
            $data['subject_id']                     = (int) $subject_id;
            $this->db->insert('coe_subject_config', $data);
        }
    }

    // ------------------------------------------------------------------
    // SGPA — compute for a student in a batch exam
    // Returns float SGPA value and stores in coe_sgpa_summary
    // ------------------------------------------------------------------
    public function computeSGPA($batch_exam_id, $student_id)
    {
        // Get results with subject credits from coe_subject_config
        $rows = $this->db
            ->select('sr.grade_points, sr.status, sc.credits')
            ->from('coe_student_results sr')
            ->join('coe_subject_config sc',
                   'sc.subject_id = sr.subject_id AND sc.exam_group_class_batch_exam_id = sr.exam_group_class_batch_exam_id',
                   'left')
            ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('sr.student_id', (int) $student_id)
            ->get()->result();

        if (empty($rows)) {
            return 0.0;
        }

        $sum_gp    = 0.0;
        $sum_cr    = 0.0;
        $has_fail  = false;

        foreach ($rows as $row) {
            $credits = (float) ($row->credits ?? 4);
            $sum_cr += $credits;
            $sum_gp += (float) $row->grade_points * $credits;
            if ($row->result_status === 'fail') {
                $has_fail = true;
            }
        }

        $sgpa = ($sum_cr > 0) ? round($sum_gp / $sum_cr, 2) : 0.0;

        // Upsert into coe_sgpa_summary
        $existing = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('student_id', (int) $student_id)
            ->get('coe_sgpa_summary')->row();

        $cgpa = $this->computeCGPA($student_id, $batch_exam_id, $sgpa, (float) ($sum_cr));

        $payload = [
            'sgpa'                       => $sgpa,
            'cgpa'                       => $cgpa,
            'total_credits_registered'   => $sum_cr,
            'total_credits_earned'       => $has_fail ? ($sum_cr - $this->_failedCredits($batch_exam_id, $student_id)) : $sum_cr,
            'arrear_count'               => $has_fail ? $this->_countFailed($batch_exam_id, $student_id) : 0,
            'result_status'              => $has_fail ? 'fail' : 'pass',
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('coe_sgpa_summary', $payload);
        } else {
            $payload['exam_group_class_batch_exam_id'] = (int) $batch_exam_id;
            $payload['student_id']                     = (int) $student_id;
            $this->db->insert('coe_sgpa_summary', $payload);
        }

        return $sgpa;
    }

    // ------------------------------------------------------------------
    // CGPA — across all semesters with published (or current) results
    // ------------------------------------------------------------------
    public function computeCGPA($student_id, $current_batch_exam_id = null, $current_sgpa = null, $current_credits = null)
    {
        // Gather all existing SGPA rows for this student (already computed semesters)
        $summaries = $this->db
            ->select('sgpa, total_credits_registered, exam_group_class_batch_exam_id')
            ->where('student_id', (int) $student_id)
            ->get('coe_sgpa_summary')->result();

        $sum_gp = 0.0;
        $sum_cr = 0.0;
        $seen   = [];

        foreach ($summaries as $s) {
            $seen[] = (int) $s->exam_group_class_batch_exam_id;
            $sum_cr += (float) $s->total_credits_registered;
            $sum_gp += (float) $s->sgpa * (float) $s->total_credits_registered;
        }

        // Include current semester if not yet persisted
        if ($current_batch_exam_id && !in_array((int) $current_batch_exam_id, $seen)
            && $current_sgpa !== null && $current_credits) {
            $sum_cr += $current_credits;
            $sum_gp += $current_sgpa * $current_credits;
        }

        return ($sum_cr > 0) ? round($sum_gp / $sum_cr, 2) : 0.0;
    }

    // ------------------------------------------------------------------
    // Bulk SGPA computation for all students in a batch exam
    // ------------------------------------------------------------------
    public function bulkComputeSGPA($batch_exam_id)
    {
        $students = $this->db
            ->distinct()->select('student_id')
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->get('coe_student_results')->result();

        $count = 0;
        foreach ($students as $s) {
            $this->computeSGPA($batch_exam_id, $s->student_id);
            $count++;
        }
        return $count;
    }

    // ------------------------------------------------------------------
    // Get SGPA summary for a batch exam
    // ------------------------------------------------------------------
    public function getSGPASummary($batch_exam_id, $filters = [])
    {
        $this->db
            ->select('sg.*, CONCAT(st.firstname, " ", st.lastname) AS student_name, st.admission_no')
            ->from('coe_sgpa_summary sg')
            ->join('students st', 'st.id = sg.student_id', 'left')
            ->where('sg.exam_group_class_batch_exam_id', (int) $batch_exam_id);

        if (!empty($filters['has_arrear'])) {
            if ((int) $filters['has_arrear'] === 1) {
                $this->db->where('sg.arrear_count >', 0);
            } else {
                $this->db->where('sg.arrear_count', 0);
            }
        }

        return $this->db->order_by('st.firstname ASC')->get()->result();
    }

    // ------------------------------------------------------------------
    // Get student card: all subjects for a student + SGPA
    // ------------------------------------------------------------------
    public function getStudentCard($student_id, $batch_exam_id)
    {
        $results = $this->db
            ->select('sr.*, sub.name AS subject_name, sub.code AS subject_code, sc.credits, sc.max_internal, sc.max_external')
            ->from('coe_student_results sr')
            ->join('subjects sub',         'sub.id = sr.subject_id',                                                 'left')
            ->join('coe_subject_config sc', 'sc.subject_id = sr.subject_id AND sc.exam_group_class_batch_exam_id = sr.exam_group_class_batch_exam_id', 'left')
            ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('sr.student_id', (int) $student_id)
            ->order_by('sub.code')
            ->get()->result();

        $sgpa = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('student_id', (int) $student_id)
            ->get('coe_sgpa_summary')->row();

        return ['results' => $results, 'sgpa' => $sgpa];
    }

    // ------------------------------------------------------------------
    // Get subjects for a batch exam
    // ------------------------------------------------------------------
    public function getSubjectsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('sub.id, sub.id AS subject_id, sub.name AS subject_name, sub.code AS subject_code')
            ->from('exam_group_class_batch_exam_subjects egcbes')
            ->join('subjects sub', 'sub.id = egcbes.subject_id', 'left')
            ->where('egcbes.exam_group_class_batch_exams_id', (int) $batch_exam_id)
            ->order_by('sub.name ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Get students enrolled in this batch exam
    // ------------------------------------------------------------------
    public function getStudentsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->distinct()
            ->select('st.id, CONCAT(st.firstname, " ", st.lastname) AS full_name, st.admission_no')
            ->from('coe_hall_tickets ht')
            ->join('students st', 'st.id = ht.student_id', 'inner')
            ->where('ht.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('st.firstname ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Internal: sum of credits for failed subjects
    // ------------------------------------------------------------------
    private function _failedCredits($batch_exam_id, $student_id)
    {
        $rows = $this->db
            ->select('sc.credits')
            ->from('coe_student_results sr')
            ->join('coe_subject_config sc',
                   'sc.subject_id = sr.subject_id AND sc.exam_group_class_batch_exam_id = sr.exam_group_class_batch_exam_id',
                   'left')
            ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('sr.student_id', (int) $student_id)
            ->where('sr.result_status', 'fail')
            ->get()->result();

        return array_sum(array_column((array) $rows, 'credits'));
    }

    // ------------------------------------------------------------------
    // Internal: count failed subjects
    // ------------------------------------------------------------------
    private function _countFailed($batch_exam_id, $student_id)
    {
        return (int) $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('student_id', (int) $student_id)
            ->where('result_status', 'fail')
            ->count_all_results('coe_student_results');
    }
}
