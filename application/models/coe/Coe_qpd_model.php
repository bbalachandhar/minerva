<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_qpd_model
 *
 * Handles Question Paper Distribution (QPD):
 * - AES-256-CBC encrypted upload of question papers
 * - Time-lock: paper is sealed until unlock_at
 * - Download only after unlock_at (decrypts on the fly)
 */
class Coe_qpd_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // Get all papers for a batch exam
    // ------------------------------------------------------------------
    public function getPapersByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('qpd.*, sub.name AS subject_name, sub.code AS subject_code, CONCAT(st.firstname, " ", st.lastname) AS uploaded_by_name')
            ->from('coe_qpd_papers qpd')
            ->join('subjects sub', 'sub.id = qpd.subject_id', 'left')
            ->join('staff st', 'st.id = qpd.created_by', 'left')
            ->where('qpd.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('sub.name ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // Get a single paper by id
    // ------------------------------------------------------------------
    public function getPaperById($id)
    {
        return $this->db
            ->select('qpd.*, sub.name AS subject_name, sub.code AS subject_code, egcbe.exam_group_id')
            ->from('coe_qpd_papers qpd')
            ->join('subjects sub', 'sub.id = qpd.subject_id', 'left')
            ->join('exam_group_class_batch_exams egcbe', 'egcbe.id = qpd.exam_group_class_batch_exam_id', 'left')
            ->where('qpd.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Insert a new paper record
    // ------------------------------------------------------------------
    public function insert($data)
    {
        $this->db->insert('coe_qpd_papers', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Increment download count and set distributed flag
    // ------------------------------------------------------------------
    public function markDistributed($id, $staff_id)
    {
        $this->db->where('id', (int) $id)->update('coe_qpd_papers', [
            'is_distributed'  => 1,
            'distributed_at'  => date('Y-m-d H:i:s'),
            'distributed_by'  => (int) $staff_id,
            'download_count'  => $this->db->query("SELECT download_count+1 FROM coe_qpd_papers WHERE id=?", [(int) $id])->row()->{'download_count+1'} ?? 1,
        ]);
    }

    public function incrementDownloadCount($id)
    {
        $this->db->set('download_count', 'download_count+1', false)
                 ->where('id', (int) $id)
                 ->update('coe_qpd_papers');
    }

    // ------------------------------------------------------------------
    // Delete a paper record (by ID, only if not yet distributed)
    // ------------------------------------------------------------------
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->where('is_distributed', 0)->delete('coe_qpd_papers');
        return $this->db->affected_rows();
    }

    // ------------------------------------------------------------------
    // Helpers: subjects for a batch exam (used in upload form)
    // ------------------------------------------------------------------
    public function getSubjectsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('DISTINCT sub.id, sub.name, sub.code')
            ->from('exam_group_class_batch_exam_subjects egcbs')
            ->join('subjects sub', 'sub.id = egcbs.subject_id')
            ->where('egcbs.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('sub.name ASC')
            ->get()->result();
    }
}
