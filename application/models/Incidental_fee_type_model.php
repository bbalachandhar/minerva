<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Incidental_fee_type_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add($data) {
        $this->db->insert('incidental_fee_types', $data);
        return $this->db->insert_id();
    }

    public function get($id = null) {
        if ($id) {
            $this->db->where('id', $id);
            return $this->db->get('incidental_fee_types')->row_array();
        }
        return $this->db->get('incidental_fee_types')->result_array();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('incidental_fee_types', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('incidental_fee_types');
        return $this->db->affected_rows();
    }
}
