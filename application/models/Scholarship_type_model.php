<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Scholarship_type_model extends CI_Model
{
    public function getAll($active_only = false)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('sort_order, name');
        return $this->db->get('scholarship_types')->result_array();
    }

    public function get($id)
    {
        return $this->db->where('id', $id)->get('scholarship_types')->row_array();
    }

    public function insert($data)
    {
        $this->db->insert('scholarship_types', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('scholarship_types', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('scholarship_types');
    }
}
