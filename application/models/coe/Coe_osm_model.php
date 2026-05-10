<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_osm_model
 * On-Screen Marking — script assignment and question-wise marks.
 */
class Coe_osm_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // List OSM scripts (with filters)
    // ------------------------------------------------------------------
    public function getAll($filters = [])
    {
        $this->db
            ->select('osm.*, ans.barcode_token, ans.scanned_filename, ans.exam_date, ans.session_slot,
                      ans.exam_group_class_batch_exam_id,
                      ht.hall_ticket_no, ht.student_id,
                      sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(ev.name, " ", ev.surname) AS evaluator_name')
            ->from('coe_osm_scripts osm')
            ->join('coe_answer_scripts ans', 'ans.id = osm.answer_script_id', 'left')
            ->join('coe_hall_tickets ht',    'ht.id = ans.coe_hall_ticket_id', 'left')
            ->join('subjects sub',           'sub.id = ans.subject_id',        'left')
            ->join('staff ev',               'ev.id = osm.assigned_evaluator', 'left');

        if (!empty($filters['batch_exam_id'])) {
            $this->db->where('ans.exam_group_class_batch_exam_id', (int) $filters['batch_exam_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('osm.status', $filters['status']);
        }
        if (!empty($filters['evaluator_id'])) {
            $this->db->where('osm.assigned_evaluator', (int) $filters['evaluator_id']);
        }
        if (!empty($filters['subject_id'])) {
            $this->db->where('ans.subject_id', (int) $filters['subject_id']);
        }

        return $this->db->order_by('osm.created_at DESC')->get()->result();
    }

    // ------------------------------------------------------------------
    // Get single OSM script with marks
    // ------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db
            ->select('osm.*, ans.barcode_token, ans.scanned_filename, ans.exam_date, ans.session_slot,
                      ans.exam_group_class_batch_exam_id,
                      ht.hall_ticket_no, ht.student_id,
                      sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(ev.name, " ", ev.surname) AS evaluator_name')
            ->from('coe_osm_scripts osm')
            ->join('coe_answer_scripts ans', 'ans.id = osm.answer_script_id', 'left')
            ->join('coe_hall_tickets ht',    'ht.id = ans.coe_hall_ticket_id', 'left')
            ->join('subjects sub',           'sub.id = ans.subject_id',        'left')
            ->join('staff ev',               'ev.id = osm.assigned_evaluator', 'left')
            ->where('osm.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Get marks for an OSM script
    // ------------------------------------------------------------------
    public function getMarks($osm_script_id)
    {
        return $this->db
            ->where('osm_script_id', (int) $osm_script_id)
            ->order_by('question_no ASC, sub_question ASC')
            ->get('coe_osm_marks')->result();
    }

    // ------------------------------------------------------------------
    // Insert OSM script record
    // ------------------------------------------------------------------
    public function insertScript($data)
    {
        $this->db->insert('coe_osm_scripts', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Update OSM script (status, total_marks, etc.)
    // ------------------------------------------------------------------
    public function updateScript($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_osm_scripts', $data);
    }

    // ------------------------------------------------------------------
    // Upsert a question-wise mark entry
    // ------------------------------------------------------------------
    public function saveMark($osm_script_id, $question_no, $sub_question, $marks_awarded, $max_marks, $staff_id)
    {
        $existing = $this->db
            ->where('osm_script_id', (int) $osm_script_id)
            ->where('question_no',   (int) $question_no)
            ->where('sub_question',  $sub_question ?: null)
            ->get('coe_osm_marks')->row();

        $now = date('Y-m-d H:i:s');
        if ($existing) {
            $this->db->where('id', $existing->id)->update('coe_osm_marks', [
                'marks_awarded' => (float) $marks_awarded,
                'max_marks'     => (float) $max_marks,
                'awarded_by'    => $staff_id,
                'awarded_at'    => $now,
            ]);
        } else {
            $this->db->insert('coe_osm_marks', [
                'osm_script_id' => (int) $osm_script_id,
                'question_no'   => (int) $question_no,
                'sub_question'  => $sub_question ?: null,
                'max_marks'     => (float) $max_marks,
                'marks_awarded' => (float) $marks_awarded,
                'awarded_by'    => $staff_id,
                'awarded_at'    => $now,
            ]);
        }
    }

    // ------------------------------------------------------------------
    // Compute and store total marks for a script
    // ------------------------------------------------------------------
    public function computeTotal($osm_script_id)
    {
        $total = $this->db
            ->select_sum('marks_awarded', 'total')
            ->where('osm_script_id', (int) $osm_script_id)
            ->get('coe_osm_marks')->row()->total ?? 0;

        $this->db->where('id', (int) $osm_script_id)->update('coe_osm_scripts', [
            'total_marks' => (float) $total,
        ]);
        return (float) $total;
    }

    // ------------------------------------------------------------------
    // Get all staff (evaluators for assignment dropdown)
    // ------------------------------------------------------------------
    public function getStaff()
    {
        return $this->db
            ->select('id, CONCAT(name, " ", surname) AS full_name, designation')
            ->order_by('name ASC')
            ->get('staff')->result();
    }

    // ------------------------------------------------------------------
    // Count scripts per status for a batch exam
    // ------------------------------------------------------------------
    public function countByStatus($batch_exam_id)
    {
        $rows = $this->db
            ->select('osm.status, COUNT(*) AS cnt')
            ->from('coe_osm_scripts osm')
            ->join('coe_answer_scripts ans', 'ans.id = osm.answer_script_id', 'left')
            ->where('ans.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->group_by('osm.status')
            ->get()->result();

        $out = ['pending' => 0, 'assigned' => 0, 'marking' => 0, 'done' => 0, 'locked' => 0];
        foreach ($rows as $r) {
            $out[$r->status] = (int) $r->cnt;
        }
        return $out;
    }
}
