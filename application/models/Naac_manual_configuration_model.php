<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Naac_manual_configuration_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function add($data) {
        $this->db->insert('naac_manual_configuration', $data);
        return $this->db->insert_id();
    }

    public function get($id = null) {
        $this->db->select()->from('naac_manual_configuration');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function remove($id) {
        $this->db->where('id', $id);
        $this->db->delete('naac_manual_configuration');
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('naac_manual_configuration', $data);
    }

}
