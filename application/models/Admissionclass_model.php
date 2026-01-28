<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admissionclass_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function add($data)
    {
        $this->db->insert('admission_classis', $data);
        return $this->db->insert_id();
    }

    public function get($id = null)
    {
        if ($id) {
            $this->db->where('id', $id);
            $query = $this->db->get('admission_classis');
            return $query->row();
        } else {
            $query = $this->db->get('admission_classis');
            return $query->result();
        }
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('admission_classis', $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('admission_classis');
        return $this->db->affected_rows();
    }

    public function changeStatus($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update('admission_classis', ['is_active' => $status]);
        return $this->db->affected_rows();
    }
}
