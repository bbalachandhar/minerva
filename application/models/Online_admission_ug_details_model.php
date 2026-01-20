<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Online_admission_ug_details_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function add($data)
    {
        $this->db->insert('online_admission_ug_details', $data);
        return $this->db->insert_id();
    }

    public function get($id = null)
    {
        $this->db->select('*');
        $this->db->from('online_admission_ug_details');
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

    public function get_by_online_admission_id($online_admission_id)
    {
        $this->db->select('*');
        $this->db->from('online_admission_ug_details');
        $this->db->where('online_admission_id', $online_admission_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function update($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('online_admission_ug_details', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('online_admission_ug_details');
    }
}
