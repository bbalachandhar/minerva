<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Onlineadmissioncourses_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function add($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('online_admission_courses', $data);
            $message = UPDATE_RECORD_CONSTANT . " On online_admission_courses id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('online_admission_courses', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On online_admission_courses id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return $record_id ?? $insert_id;
        }
    }

    public function get($id = null)
    {
        $this->db->select()->from('online_admission_courses');
        if ($id != null) {
            $this->db->where('id', $id);
        }
        $this->db->order_by('id DESC');
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getActiveCourses($course_level = null, $admission_type = null)
    {
        $this->db->select('*');
        $this->db->from('online_admission_courses');
        $this->db->where('is_active', 1);

        if (!empty($course_level)) {
            $this->db->where('course_level', $course_level);
        }

        if (!empty($admission_type)) {
            $this->db->where('admission_type', $admission_type);
        }

        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'ASC');

        return $this->db->get()->result_array();
    }

    public function getById($id)
    {
        $this->db->select('*');
        $this->db->from('online_admission_courses');
        $this->db->where('id', $id);
        return $this->db->get()->row_array();
    }
    
    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $this->db->delete('online_admission_courses');
        $message = DELETE_RECORD_CONSTANT . " On online_admission_courses id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validation callback for checking if course name exists (excluding current record during edit)
     */
    public function valid_check_exists($str)
    {
        $id = $this->input->post('id');
        if ($id) {
            // During edit, exclude current record
            $this->db->where('id !=', $id);
        }
        $this->db->where('course_name', $str);
        $query = $this->db->get('online_admission_courses');
        
        if ($query->num_rows() > 0) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Validation callback for checking if course code exists (excluding current record during edit)
     */
    public function valid_check_exists_code($str)
    {
        if (empty($str)) {
            // Course code is optional
            return TRUE;
        }
        
        $id = $this->input->post('id');
        if ($id) {
            // During edit, exclude current record
            $this->db->where('id !=', $id);
        }
        $this->db->where('course_code', $str);
        $query = $this->db->get('online_admission_courses');
        
        if ($query->num_rows() > 0) {
            return FALSE;
        }
        return TRUE;
    }
}
