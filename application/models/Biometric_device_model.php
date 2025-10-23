<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Biometric_device_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add($data) {
        $this->db->insert('biometric_devices', $data);
        return $this->db->insert_id();
    }

    public function get($id = null) {
        $this->db->select()->from('biometric_devices');
        if ($id != null) {
            $this->db->where('id', $id);
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('biometric_devices', $data);
    }

    public function remove($id) {
        $this->db->where('id', $id);
        $this->db->delete('biometric_devices');
    }

    public function getActiveDevice() {
        $this->db->select()->from('biometric_devices');
        $this->db->where('is_active', 1);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function deactivateAllDevices() {
        $this->db->update('biometric_devices', ['is_active' => 0]);
    }

}
