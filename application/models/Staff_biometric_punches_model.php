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

}
