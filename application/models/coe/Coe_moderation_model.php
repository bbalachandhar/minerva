<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_moderation_model
 * Grace / moderation / normalisation rules and application.
 */
class Coe_moderation_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // List rules for a batch exam
    // ------------------------------------------------------------------
    public function getAll($batch_exam_id)
    {
        return $this->db
            ->select('mr.*, sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(st.name, " ", st.surname) AS applied_by_name')
            ->from('coe_moderation_rules mr')
            ->join('subjects sub', 'sub.id = mr.subject_id',  'left')
            ->join('staff st',     'st.id = mr.applied_by',   'left')
            ->where('mr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('mr.created_at ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Get single rule
    // ------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db
            ->select('mr.*, sub.name AS subject_name, sub.code AS subject_code')
            ->from('coe_moderation_rules mr')
            ->join('subjects sub', 'sub.id = mr.subject_id', 'left')
            ->where('mr.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Insert rule
    // ------------------------------------------------------------------
    public function insert($data)
    {
        $this->db->insert('coe_moderation_rules', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Update rule
    // ------------------------------------------------------------------
    public function update($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_moderation_rules', $data);
    }

    // ------------------------------------------------------------------
    // Delete rule (only if not applied)
    // ------------------------------------------------------------------
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->where('is_applied', 0)->delete('coe_moderation_rules');
        return $this->db->affected_rows() > 0;
    }

    // ------------------------------------------------------------------
    // Preview: compute before/after for students affected by rules
    // ------------------------------------------------------------------
    public function preview($batch_exam_id)
    {
        $rules = $this->getAll($batch_exam_id);
        if (empty($rules)) {
            return [];
        }

        $preview = [];

        foreach ($rules as $rule) {
            $q = $this->db
                ->select('sr.id, sr.student_id, sr.subject_id, sr.external_marks, sr.total_marks,
                          sr.moderation_applied, CONCAT(st.firstname, " ", st.lastname) AS student_name,
                          st.admission_no')
                ->from('coe_student_results sr')
                ->join('students st', 'st.id = sr.student_id', 'left')
                ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
                ->where('sr.is_published', 0);

            if (!empty($rule->subject_id)) {
                $q = $this->db->where('sr.subject_id', (int) $rule->subject_id);
            }

            $rows = $q->get()->result();

            foreach ($rows as $row) {
                $added = $this->_computeAdded($rule, $row);
                $preview[] = [
                    'rule'         => $rule,
                    'student_name' => $row->student_name,
                    'admission_no' => $row->admission_no,
                    'subject_id'   => $row->subject_id,
                    'result_id'    => $row->id,
                    'before_total' => $row->total_marks,
                    'grace_added'  => $added,
                    'after_total'  => min((float) $row->total_marks + $added, 100),
                ];
            }
        }

        return $preview;
    }

    // ------------------------------------------------------------------
    // Apply a single rule by its ID
    // ------------------------------------------------------------------
    public function applyRule($rule_id)
    {
        $rule = $this->db
            ->where('id', (int) $rule_id)
            ->where('is_applied', 0)
            ->get('coe_moderation_rules')->row();

        if (empty($rule)) {
            return false; // already applied or not found
        }

        $q = $this->db
            ->select('sr.id, sr.external_marks, sr.internal_marks, sr.total_marks, sr.moderation_applied')
            ->from('coe_student_results sr')
            ->where('sr.exam_group_class_batch_exam_id', (int) $rule->exam_group_class_batch_exam_id)
            ->where('sr.is_published', 0);

        if (!empty($rule->subject_id)) {
            $this->db->where('sr.subject_id', (int) $rule->subject_id);
        }

        $rows = $q->get()->result();

        foreach ($rows as $row) {
            $added     = $this->_computeAdded($rule, $row);
            $new_total = min((float) $row->total_marks + $added, 100);
            $new_ext   = ($rule->applies_to === 'external')
                ? min((float) $row->external_marks + $added, 70)
                : $row->external_marks;
            $new_int   = ($rule->applies_to === 'internal')
                ? min((float) $row->internal_marks + $added, 30)
                : $row->internal_marks;

            $this->db->where('id', $row->id)->update('coe_student_results', [
                'external_marks'     => $new_ext,
                'internal_marks'     => $new_int,
                'total_marks'        => $new_total,
                'moderation_applied' => (float) $row->moderation_applied + $added,
            ]);
        }

        $this->db->where('id', (int) $rule_id)->update('coe_moderation_rules', [
            'is_applied' => 1,
            'applied_at' => date('Y-m-d H:i:s'),
        ]);

        return count($rows);
    }

    // ------------------------------------------------------------------
    // Apply all unapplied rules for a batch exam
    // ------------------------------------------------------------------
    public function applyRules($batch_exam_id)
    {
        $rules = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('is_applied', 0)
            ->get('coe_moderation_rules')->result();

        $applied = 0;

        foreach ($rules as $rule) {
            $q = $this->db
                ->select('sr.id, sr.external_marks, sr.internal_marks, sr.total_marks, sr.moderation_applied')
                ->from('coe_student_results sr')
                ->where('sr.exam_group_class_batch_exam_id', (int) $batch_exam_id)
                ->where('sr.is_published', 0);

            if (!empty($rule->subject_id)) {
                $this->db->where('sr.subject_id', (int) $rule->subject_id);
            }

            $rows = $q->get()->result();

            foreach ($rows as $row) {
                $added      = $this->_computeAdded($rule, $row);
                $new_total  = min((float) $row->total_marks + $added, 100);
                $new_ext    = ($rule->applies_to === 'external')
                    ? min((float) $row->external_marks + $added, 70)
                    : $row->external_marks;
                $new_int    = ($rule->applies_to === 'internal')
                    ? min((float) $row->internal_marks + $added, 30)
                    : $row->internal_marks;

                $this->db->where('id', $row->id)->update('coe_student_results', [
                    'external_marks'      => $new_ext,
                    'internal_marks'      => $new_int,
                    'total_marks'         => $new_total,
                    'moderation_applied'  => (float) $row->moderation_applied + $added,
                ]);
            }

            $this->db->where('id', $rule->id)->update('coe_moderation_rules', [
                'is_applied'  => 1,
                'applied_at'  => date('Y-m-d H:i:s'),
            ]);
            $applied++;
        }

        return $applied;
    }

    // ------------------------------------------------------------------
    // Internal: compute marks to add for a row given a rule
    // ------------------------------------------------------------------
    private function _computeAdded($rule, $row)
    {
        // value_type determines flat vs percentage; rule_type is grace/moderation/normalisation/scaling
        if ($rule->value_type === 'flat') {
            return (float) $rule->value;
        }

        if ($rule->value_type === 'percentage') {
            $base = ($rule->applies_to === 'external') ? $row->external_marks : $row->total_marks;
            return round($base * (float) $rule->value / 100, 2);
        }

        // normalisation type: target - current (floor at 0)
        if ($rule->rule_type === 'normalisation' || $rule->rule_type === 'scaling') {
            return max(0, (float) $rule->value - (float) $row->total_marks);
        }

        return 0;
    }

    // ------------------------------------------------------------------
    // Get subjects for a batch exam
    // ------------------------------------------------------------------
    public function getSubjectsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('sub.id, sub.name AS subject_name, sub.code AS subject_code')
            ->from('exam_group_class_batch_exam_subjects egcbes')
            ->join('subjects sub', 'sub.id = egcbes.subject_id', 'left')
            ->where('egcbes.exam_group_class_batch_exams_id', (int) $batch_exam_id)
            ->order_by('sub.name ASC')
            ->get()->result();
    }
}
