<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_ufm_model
 *
 * UFM (Unfair Means) / Malpractice incident tracking.
 */
class Coe_ufm_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // List all incidents (with optional filters)
    // ------------------------------------------------------------------
    public function getAll($filters = [])
    {
        $this->db
            ->select('ufm.*, ht.hall_ticket_no, ht.student_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sr.hall_name,
                      CONCAT(rep.firstname, " ", rep.lastname) AS reported_by_name,
                      CONCAT(rev.firstname, " ", rev.lastname) AS reviewed_by_name')
            ->from('coe_ufm_incidents ufm')
            ->join('coe_hall_tickets ht', 'ht.id = ufm.coe_hall_ticket_id', 'left')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->join('coe_seating_rooms sr', 'sr.id = ufm.seating_room_id', 'left')
            ->join('staff rep', 'rep.id = ufm.reported_by', 'left')
            ->join('staff rev', 'rev.id = ufm.reviewed_by', 'left');

        if (!empty($filters['batch_exam_id'])) {
            $this->db->where('sr.batch_exam_id', (int) $filters['batch_exam_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('ufm.status', $filters['status']);
        }
        if (!empty($filters['exam_date'])) {
            $this->db->where('ufm.exam_date', $filters['exam_date']);
        }

        return $this->db->order_by('ufm.created_at DESC')->get()->result();
    }

    // ------------------------------------------------------------------
    // Get single incident
    // ------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db
            ->select('ufm.*, ht.hall_ticket_no, ht.student_id,
                      CONCAT(st.firstname, " ", st.lastname) AS student_name,
                      sr.hall_name, sr.batch_exam_id,
                      CONCAT(rep.firstname, " ", rep.lastname) AS reported_by_name')
            ->from('coe_ufm_incidents ufm')
            ->join('coe_hall_tickets ht', 'ht.id = ufm.coe_hall_ticket_id', 'left')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->join('coe_seating_rooms sr', 'sr.id = ufm.seating_room_id', 'left')
            ->join('staff rep', 'rep.id = ufm.reported_by', 'left')
            ->where('ufm.id', (int) $id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Insert new incident
    // ------------------------------------------------------------------
    public function insert($data)
    {
        $this->db->insert('coe_ufm_incidents', $data);
        return $this->db->insert_id();
    }

    // ------------------------------------------------------------------
    // Update incident (for review/penalty)
    // ------------------------------------------------------------------
    public function update($id, $data)
    {
        $this->db->where('id', (int) $id)->update('coe_ufm_incidents', $data);
    }

    // ------------------------------------------------------------------
    // Delete incident
    // ------------------------------------------------------------------
    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('coe_ufm_incidents');
    }

    // ------------------------------------------------------------------
    // Look up hall ticket by number (for report form)
    // ------------------------------------------------------------------
    public function getHallTicketByNo($hall_ticket_no, $batch_exam_id)
    {
        return $this->db
            ->select('ht.id, ht.hall_ticket_no, ht.student_id, CONCAT(st.firstname, " ", st.lastname) AS student_name')
            ->from('coe_hall_tickets ht')
            ->join('students st', 'st.id = ht.student_id', 'left')
            ->where('ht.hall_ticket_no', $hall_ticket_no)
            ->where('ht.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->get()->row();
    }

    // ------------------------------------------------------------------
    // Get rooms for a batch exam (for report form)
    // ------------------------------------------------------------------
    public function getRoomsByBatchExam($batch_exam_id)
    {
        return $this->db
            ->select('id, hall_name, exam_date, session_slot')
            ->where('batch_exam_id', (int) $batch_exam_id)
            ->order_by('exam_date ASC, session_slot ASC, hall_name ASC')
            ->get('coe_seating_rooms')->result();
    }
}
