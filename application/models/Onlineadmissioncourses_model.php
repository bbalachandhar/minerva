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
}
