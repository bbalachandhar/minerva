<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_revaluation_model
 * Two-stage revaluation requests and assignments.
 */
class Coe_revaluation_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // List all requests (with optional filters)
    // ------------------------------------------------------------------
    public function getAll($filters = [])
    {
        $this->db
            ->select('rv.*, CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      st.admission_no, sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(cr.name, " ", cr.surname) AS created_by_name')
            ->from('coe_revaluation_requests rv')
            ->join('students st',   'st.id = rv.student_id',   'left')
            ->join('subjects sub',  'sub.id = rv.subject_id',  'left')
            ->join('staff cr',      'cr.id = rv.created_by',   'left');

        if (!empty($filters['batch_exam_id'])) {
            $this->db->where('rv.exam_group_class_batch_exam_id', (int) $filters['batch_exam_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('rv.status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $this->db->where('rv.payment_status', $filters['payment_status']);
        }
        if (!empty($filters['subject_id'])) {
            $this->db->where('rv.subject_id', (int) $filters['subject_id']);
        }

        return $this->db->order_by('rv.created_at DESC')->get()->result();
    }

    // ------------------------------------------------------------------
    // Get single request
    // ------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db
            ->select('rv.*, CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      st.admission_no, sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(cr.name, " ", cr.surname) AS created_by_name')
            ->from('coe_revaluation_requests rv')
            ->join('students st',  'st.id = rv.student_id',  'left')
            ->join('subjects sub', 'sub.id = rv.subject_id', 'left')
            ->join('staff cr',     'cr.id = rv.created_by',  'left')
            ->where('rv.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Get assignments for a request
    // ------------------------------------------------------------------
    public function getAssignments($request_id)
    {
        return $this->db
            ->select('ra.*, CONCAT(ev.name, " ", ev.surname) AS evaluator_name,
                      CONCAT(ab.name, " ", ab.surname) AS assigned_by_name')
            ->from('coe_revaluation_assignments ra')
            ->join('staff ev', 'ev.id = ra.assigned_evaluator', 'left')
            ->join('staff ab', 'ab.id = ra.assigned_by',        'left')
            ->where('ra.revaluation_request_id', (int) $request_id)
            ->order_by('ra.created_at ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Insert request
    // ------------------------------------------------------------------
    public function insertRequest($data)
    {
        $this->db->insert('coe_revaluation_requests', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Update request
    // ------------------------------------------------------------------
    public function updateRequest($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_revaluation_requests', $data);
    }

    // ------------------------------------------------------------------
    // Insert assignment
    // ------------------------------------------------------------------
    public function insertAssignment($data)
    {
        $this->db->insert('coe_revaluation_assignments', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Update assignment
    // ------------------------------------------------------------------
    public function updateAssignment($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_revaluation_assignments', $data);
    }

    // ------------------------------------------------------------------
    // Get a single assignment
    // ------------------------------------------------------------------
    public function getAssignmentById($id)
    {
        return $this->db
            ->select('ra.*, rv.student_id, rv.subject_id, rv.original_marks,
                      rv.exam_group_class_batch_exam_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sub.name AS subject_name, sub.code AS subject_code,
                      CONCAT(ev.name, " ", ev.surname) AS evaluator_name')
            ->from('coe_revaluation_assignments ra')
            ->join('coe_revaluation_requests rv', 'rv.id = ra.revaluation_request_id', 'left')
            ->join('students st',                 'st.id = rv.student_id',              'left')
            ->join('subjects sub',                'sub.id = rv.subject_id',             'left')
            ->join('staff ev',                    'ev.id = ra.assigned_evaluator',      'left')
            ->where('ra.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Get all staff (for evaluator dropdown)
    // ------------------------------------------------------------------
    public function getStaff()
    {
        return $this->db
            ->select('id, CONCAT(name, " ", surname) AS full_name, designation')
            ->order_by('name ASC')
            ->get('staff')->result();
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
