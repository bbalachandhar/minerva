<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class complaint_Model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session      = $this->setting_model->getCurrentSession();
        $this->current_session_name = $this->setting_model->getCurrentSessionName();
        $this->start_month          = $this->setting_model->getStartMonth();
    }

    /**
     * Generate a sequential ticket number: Comp_XXXX (global auto-increment sequence)
     */
    private function generateTicketNo($id)
    {
        return 'Comp_' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    public function add($data)
    {
        // Always stamp the current academic session
        if (empty($data['session_id'])) {
            $data['session_id'] = $this->current_session;
        }
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->insert('complaint', $data);
        $query     = $this->db->insert_id();
        $message   = INSERT_RECORD_CONSTANT . " On  Complain id " . $query;
        $action    = "Insert";
        $record_id = $query;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        // Assign ticket number after insert
        $ticket_no = $this->generateTicketNo($query);
        $this->db->where('id', $query)->update('complaint', ['ticket_no' => $ticket_no]);

        return $query;
    }

    public function image_add($complaint_id, $image)
    {
        $this->db->set('image', $image);
        $this->db->where('id', $complaint_id);
        $this->db->update('complaint');
    }

    /**
     * List all complaints with optional status/priority/source filter for admin.
     */
    public function complaint_list($id = null, $filters = [])
    {
        $this->db->select('c.*, u.username as responded_by_name, s.session as session_name')
                 ->from('complaint c')
                 ->join('users u', 'u.id = c.responded_by', 'left')
                 ->join('sessions s', 's.id = c.session_id', 'left');
        if ($id != null) {
            $this->db->where('c.id', $id);
        } else {
            // Default to current session unless a specific session filter is set
            $session_id = !empty($filters['session_id']) ? (int)$filters['session_id'] : (int)$this->current_session;
            $this->db->where('c.session_id', $session_id);
            if (!empty($filters['status']))       $this->db->where('c.status', $filters['status']);
            if (!empty($filters['priority']))     $this->db->where('c.priority', $filters['priority']);
            if (!empty($filters['source']))       $this->db->where('c.source', $filters['source']);
            if (!empty($filters['submitted_by'])) $this->db->where('c.submitted_by', $filters['submitted_by']);
            $this->db->order_by('c.id', 'DESC');
        }
        $query = $this->db->get();
        return ($id != null) ? $query->row_array() : $query->result_array();
    }

    /**
     * Get complaints for a specific student_session_id (student/parent portal).
     */
    public function getByStudentSession($student_session_id)
    {
        return $this->db->select('c.*, u.username as responded_by_name')
                        ->from('complaint c')
                        ->join('users u', 'u.id = c.responded_by', 'left')
                        ->where('c.student_session_id', $student_session_id)
                        ->order_by('c.id', 'DESC')
                        ->get()->result_array();
    }

    /**
     * Status counts across all complaints (for dashboard widget).
     */
    public function getStatusCounts($session_id = null)
    {
        $sid = $session_id ?: $this->current_session;
        $sql = "SELECT
                    SUM(CASE WHEN status = 'open'        THEN 1 ELSE 0 END) AS open_count,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
                    SUM(CASE WHEN status = 'resolved'    THEN 1 ELSE 0 END) AS resolved_count,
                    SUM(CASE WHEN status = 'closed'      THEN 1 ELSE 0 END) AS closed_count,
                    COUNT(*) AS total_count
                FROM complaint WHERE session_id = ?";
        return $this->db->query($sql, [(int)$sid])->row_array();
    }

    public function getSessions()
    {
        return $this->db->select('id, session')
                        ->from('sessions')
                        ->order_by('id', 'DESC')
                        ->get()->result_array();
    }

    /**
     * Count open/unresolved complaints for a student_session_id.
     */
    public function getOpenCountByStudentSession($student_session_id)
    {
        return $this->db->where('student_session_id', $student_session_id)
                        ->where_in('status', ['open', 'in_progress'])
                        ->count_all_results('complaint');
    }

    public function image_delete($id, $img_name)
    {
        $file = "./uploads/front_office/complaints/" . $img_name;
        if (file_exists($file)) unlink($file);
        $this->db->where('id', $id)->delete('complaint');
    }

    public function compalaint_update($id, $data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id)->update('complaint', $data);
        $message   = UPDATE_RECORD_CONSTANT . " On Complaint id " . $id;
        $action    = "Update";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id)->delete('complaint');
        $message   = DELETE_RECORD_CONSTANT . " On Complaint id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
    }

    public function getComplaintType()
    {
        return $this->db->select('*')->from('complaint_type')->get()->result_array();
    }

    public function getComplaintSource()
    {
        return $this->db->select('*')->from('source')->get()->result_array();
    }

}
