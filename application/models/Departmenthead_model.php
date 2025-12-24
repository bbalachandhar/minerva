<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Departmenthead_model extends MY_model
{

    public function add($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        // Check if a record for the given department_id already exists
        $this->db->where('department_id', $data['department_id']);
        $q = $this->db->get('department_heads');

        if ($q->num_rows() > 0) {
            // Update the existing record
            $this->db->where('department_id', $data['department_id']);
            $this->db->update('department_heads', $data);
            $message = UPDATE_RECORD_CONSTANT . " On Department Heads id " . $data['department_id'];
            $action = "Update";
            $record_id = $data['department_id'];
            $this->log($message, $record_id, $action);
        } else {
            // Insert a new record
            $this->db->insert('department_heads', $data);
            $id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On Department Heads id " . $id;
            $action = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function get($id = null)
    {
        $this->db->select('department_heads.*, departments.department_name, staff.name as staff_name, staff.surname as staff_surname, staff.employee_id');
        $this->db->from('department_heads');
        $this->db->join('departments', 'departments.id = department_heads.department_id');
        $this->db->join('staff', 'staff.id = department_heads.staff_id');
        if ($id != null) {
            $this->db->where('department_heads.id', $id);
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function get_department_head_by_department_id($department_id)
    {
        $this->db->select('staff.*, departments.department_name');
        $this->db->from('department_heads');
        $this->db->join('departments', 'departments.id = department_heads.department_id');
        $this->db->join('staff', 'staff.id = department_heads.staff_id');
        $this->db->where('department_heads.department_id', $department_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $this->db->delete('department_heads');
        $message = DELETE_RECORD_CONSTANT . " On Department Heads id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }
}
