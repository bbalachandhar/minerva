<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Staff_biometric_punches_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add($data) {
        $this->db->insert('staff_biometric_punches', $data);
        return $this->db->insert_id();
    }

    public function get_punches_by_staff_and_date($staff_id, $date) {
        $this->db->select('punch_time');
        $this->db->from('staff_biometric_punches');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('DATE(punch_time)', $date);
        $this->db->order_by('punch_time', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function delete_punches_by_staff_and_date($staff_id, $date) {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('DATE(punch_time)', $date);
        $this->db->delete('staff_biometric_punches');
    }

    /**
     * Delete all punches between two dates (inclusive).
     * Used for "reset and fetch between dates" functionality.
     */
    public function delete_punches_between_dates($from_date, $to_date) {
        $this->db->where('DATE(punch_time) >=', $from_date);
        $this->db->where('DATE(punch_time) <=', $to_date);
        $this->db->delete('staff_biometric_punches');
    }

    public function get_punches_by_date($date) {
        $this->db->select('staff_id, punch_time');
        $this->db->from('staff_biometric_punches');
        $this->db->where('DATE(punch_time)', $date);
        // Exclude unresolved exceptions from attendance processing
        $this->db->where('(is_exception = 0 OR (is_exception = 1 AND exception_resolved = 1))');
        $this->db->order_by('staff_id, punch_time', 'ASC');
        $query = $this->db->get();
        $result = $query->result_array();

        $staff_punches_by_day = [];
        foreach ($result as $punch) {
            $staff_id = $punch['staff_id'];
            $punch_time = $punch['punch_time'];
            $punch_date = date('Y-m-d', strtotime($punch_time));

            if (!isset($staff_punches_by_day[$staff_id])) {
                $staff_punches_by_day[$staff_id] = [];
            }
            if (!isset($staff_punches_by_day[$staff_id][$punch_date])) {
                $staff_punches_by_day[$staff_id][$punch_date] = [];
            }
            $staff_punches_by_day[$staff_id][$punch_date][] = strtotime($punch_time);
        }
        return $staff_punches_by_day;
    }

    /**
     * Get all unresolved exceptions
     */
    public function get_unresolved_exceptions($limit = null, $offset = 0) {
        $this->db->select('sbp.*, s.name as staff_name, s.employee_id, s.surname, sd.designation');
        $this->db->from('staff_biometric_punches sbp');
        $this->db->join('staff s', 'sbp.staff_id = s.id', 'left');
        $this->db->join('staff_designation sd', 's.designation = sd.id', 'left');
        $this->db->where('sbp.is_exception', 1);
        $this->db->where('sbp.exception_resolved', 0);
        $this->db->order_by('sbp.punch_time', 'DESC');
        
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Count unresolved exceptions
     */
    public function count_unresolved_exceptions() {
        $this->db->where('is_exception', 1);
        $this->db->where('exception_resolved', 0);
        return $this->db->count_all_results('staff_biometric_punches');
    }

    /**
     * Resolve an exception
     */
    public function resolve_exception($punch_id, $resolution_action, $resolved_by) {
        $data = [
            'exception_resolved' => 1,
            'resolved_by' => $resolved_by,
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_action' => $resolution_action
        ];
        
        $this->db->where('id', $punch_id);
        $this->db->update('staff_biometric_punches', $data);
        
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get a single punch by ID
     */
    public function get_by_id($punch_id) {
        $this->db->where('id', $punch_id);
        $query = $this->db->get('staff_biometric_punches');
        return $query->row_array();
    }

}
