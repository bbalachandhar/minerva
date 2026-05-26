<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Coe_results_model extends CI_Model {

    public function getAll($batch_exam_id, $filters = [])
    {
        $bid = (int) $batch_exam_id;
        $this->db->select('csr.id, csr.student_id, csr.subject_id, csr.internal_marks,
            csr.external_marks, csr.total_marks, csr.grade, csr.grade_points,
            csr.result_status, csr.is_arrear, csr.moderation_applied, csr.is_published,
            CONCAT(s.firstname," ",s.lastname) AS student_name,
            s.admission_no,
            sub.name AS subject_name, sub.code AS subject_code,
            sg.sgpa, sg.cgpa, sg.arrear_count')
            ->from('coe_student_results csr')
            ->join('students s', 's.id = csr.student_id', 'left')
            ->join('subjects sub', 'sub.id = csr.subject_id', 'left')
            ->join('coe_sgpa_summary sg',
                'sg.student_id = csr.student_id AND sg.exam_group_class_batch_exam_id = csr.exam_group_class_batch_exam_id',
                'left')
            ->where('csr.exam_group_class_batch_exam_id', $bid);

        if (!empty($filters['subject_id'])) {
            $this->db->where('csr.subject_id', (int) $filters['subject_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('csr.result_status', $filters['status']);
        }
        if (isset($filters['has_arrear']) && $filters['has_arrear'] !== '') {
            if ((int) $filters['has_arrear'] === 1) {
                $this->db->where('sg.arrear_count >', 0);
            } else {
                $this->db->where('sg.arrear_count', 0);
            }
        }

        return $this->db->order_by('student_name', 'ASC')->order_by('sub.code', 'ASC')->get()->result();
    }

    public function getSGPASummary($batch_exam_id, $filters = [])
    {
        $bid = (int) $batch_exam_id;
        $this->db->select('sg.*, CONCAT(s.firstname," ",s.lastname) AS student_name, s.admission_no')
            ->from('coe_sgpa_summary sg')
            ->join('students s', 's.id = sg.student_id', 'left')
            ->where('sg.exam_group_class_batch_exam_id', $bid);

        if (isset($filters['has_arrear']) && $filters['has_arrear'] !== '') {
            if ((int) $filters['has_arrear'] === 1) {
                $this->db->where('sg.arrear_count >', 0);
            } else {
                $this->db->where('sg.arrear_count', 0);
            }
        }

        return $this->db->order_by('student_name', 'ASC')->get()->result();
    }

    public function getPublicationStatus($batch_exam_id)
    {
        return $this->db->select('is_published, published_at, published_by')
            ->from('coe_sgpa_summary')
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->limit(1)
            ->get()->row();
    }

    public function publish($batch_exam_id, $staff_id)
    {
        $bid = (int) $batch_exam_id;
        $this->db->where('exam_group_class_batch_exam_id', $bid)
            ->update('coe_student_results', [
                'is_published' => 1,
                'published_at' => date('Y-m-d H:i:s'),
            ]);

        $this->db->where('exam_group_class_batch_exam_id', $bid)
            ->update('coe_sgpa_summary', [
                'is_published' => 1,
                'published_at' => date('Y-m-d H:i:s'),
                'published_by' => (int) $staff_id,
            ]);
        return TRUE;
    }

    public function unpublish($batch_exam_id)
    {
        $bid = (int) $batch_exam_id;
        $this->db->where('exam_group_class_batch_exam_id', $bid)
            ->update('coe_student_results', ['is_published' => 0, 'published_at' => NULL]);

        $this->db->where('exam_group_class_batch_exam_id', $bid)
            ->update('coe_sgpa_summary', ['is_published' => 0, 'published_at' => NULL, 'published_by' => NULL]);
        return TRUE;
    }

    public function getStudentCard($student_id, $batch_exam_id)
    {
        $bid = (int) $batch_exam_id;
        $results = $this->db->select('csr.*, sub.name AS subject_name, sub.code AS subject_code, sc.credits')
            ->from('coe_student_results csr')
            ->join('subjects sub', 'sub.id = csr.subject_id', 'left')
            ->join('coe_subject_config sc',
                'sc.subject_id = csr.subject_id AND sc.exam_group_class_batch_exam_id = csr.exam_group_class_batch_exam_id',
                'left')
            ->where('csr.student_id', (int) $student_id)
            ->where('csr.exam_group_class_batch_exam_id', $bid)
            ->order_by('sub.code', 'ASC')
            ->get()->result();

        $sgpa = $this->db->select('sg.*, CONCAT(s.firstname," ",s.lastname) AS student_name, s.admission_no')
            ->from('coe_sgpa_summary sg')
            ->join('students s', 's.id = sg.student_id', 'left')
            ->where('sg.student_id', (int) $student_id)
            ->where('sg.exam_group_class_batch_exam_id', $bid)
            ->get()->row();

        return ['results' => $results, 'sgpa' => $sgpa];
    }

    public function getSubjectsByBatchExam($batch_exam_id)
    {
        return $this->db->select('sub.id, sub.name AS subject_name, sub.code AS subject_code')
            ->from('subjects sub')
            ->join('exam_group_class_batch_exam_subjects egcbes', 'egcbes.subject_id = sub.id', 'inner')
            ->where('egcbes.exam_group_class_batch_exams_id', (int) $batch_exam_id)
            ->order_by('sub.name', 'ASC')
            ->get()->result();
    }

    public function exportResultsData($batch_exam_id)
    {
        return $this->getAll($batch_exam_id);
    }

    // ---------------------------------------------------------------
    // Tabulation Sheet — flat rows pivoted in controller/view
    // Returns: all results with student info + subject info, ordered
    // by student name then subject code.
    // ---------------------------------------------------------------
    public function getTabulationData($batch_exam_id)
    {
        return $this->db
            ->select('
                sr.student_id,
                CONCAT(s.firstname," ",s.lastname) AS student_name,
                s.register_no, s.admission_no,
                sr.subject_id,
                sub.code AS subject_code, sub.name AS subject_name,
                sr.internal_marks, sr.external_marks, sr.total_marks,
                sr.grade, sr.grade_points, sr.result_status, sr.is_arrear,
                sr.moderation_applied, sr.credits,
                sg.sgpa, sg.cgpa, sg.total_credits_earned,
                sg.total_credits_registered, sg.arrear_count,
                sg.result_status AS overall_status
            ')
            ->from('coe_student_results sr')
            ->join('students s', 's.id = sr.student_id', 'left')
            ->join('subjects sub', 'sub.id = sr.subject_id', 'left')
            ->join('coe_sgpa_summary sg',
                'sg.student_id = sr.student_id
                 AND sg.exam_group_class_batch_exam_id = sr.exam_group_class_batch_exam_id',
                'left')
            ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('student_name', 'ASC')
            ->order_by('sub.code', 'ASC')
            ->get()->result();
    }

    // ---------------------------------------------------------------
    // Merit / Rank List — ordered by SGPA DESC
    // ---------------------------------------------------------------
    public function getMeritList($batch_exam_id)
    {
        return $this->db
            ->select('
                sg.student_id,
                CONCAT(s.firstname," ",s.lastname) AS student_name,
                s.register_no, s.admission_no,
                sg.sgpa, sg.cgpa, sg.arrear_count,
                sg.total_credits_earned, sg.total_credits_registered,
                sg.result_status,
                c.class AS class_name
            ')
            ->from('coe_sgpa_summary sg')
            ->join('students s', 's.id = sg.student_id', 'left')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sg.exam_group_class_batch_exam_id')
            ->join('student_session ss', 'ss.student_id = sg.student_id AND ss.session_id = egcbe.session_id', 'left')
            ->join('classes c', 'c.id = ss.class_id', 'left')
            ->where('sg.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('sg.sgpa', 'DESC')
            ->order_by('sg.arrear_count', 'ASC')
            ->order_by('student_name', 'ASC')
            ->get()->result();
    }

    /**
     * Fetch all semester results for a student across all published events.
     * Returns: array of semester objects with subject-level results + SGPA row.
     * Also computes cumulative CGPA weighted by total credits.
     */
    public function getTranscript($student_id)
    {
        $student_id = (int) $student_id;

        // Get student info
        $student = $this->db
            ->select("id, admission_no, CONCAT(firstname,' ',lastname) AS full_name, email, dob, gender, class_id")
            ->where('id', $student_id)
            ->get('students')->row();

        if (!$student) {
            return null;
        }

        // All published SGPA summary rows for this student
        $semesters = $this->db
            ->select([
                'sg.*',
                'egcbe.exam_group_id',
                'eg.exam_group_name AS semester_name',
                'eg.exam_group_short AS semester_short',
                'egcbe.date_from',
                'egcbe.date_to',
                'sess.session AS academic_year',
            ])
            ->from('coe_sgpa_summary sg')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = sg.exam_group_class_batch_exam_id')
            ->join('exam_groups eg', 'eg.id = egcbe.exam_group_id')
            ->join('sessions sess', 'sess.id = egcbe.session_id', 'left')
            ->where('sg.student_id', $student_id)
            ->where('sg.is_published', 1)
            ->order_by('egcbe.date_from ASC')
            ->get()->result();

        $total_weighted_sgpa = 0;
        $total_credits       = 0;

        foreach ($semesters as &$sem) {
            // Subject results for this semester
            $sem->subjects = $this->db
                ->select([
                    'sr.*',
                    'subj.code AS subject_code',
                    'subj.name AS subject_name',
                    'cfg.credits',
                ])
                ->from('coe_student_results sr')
                ->join('subjects subj', 'subj.id = sr.subject_id', 'left')
                ->join('coe_subject_config cfg',
                    'cfg.subject_id = sr.subject_id AND cfg.exam_group_class_batch_exam_id = sr.exam_group_class_batch_exam_id',
                    'left')
                ->where('sr.exam_group_class_batch_exam_id', (int) $sem->exam_group_class_batch_exam_id)
                ->where('sr.student_id', $student_id)
                ->order_by('subj.code ASC')
                ->get()->result();

            $credits = (float) ($sem->total_credits ?: 0);
            if ($credits > 0 && $sem->sgpa !== null) {
                $total_weighted_sgpa += (float) $sem->sgpa * $credits;
                $total_credits       += $credits;
            }
        }
        unset($sem);

        $cgpa = $total_credits > 0 ? round($total_weighted_sgpa / $total_credits, 2) : null;

        return [
            'student'       => $student,
            'semesters'     => $semesters,
            'cgpa'          => $cgpa,
            'total_credits' => $total_credits,
        ];
    }
}
