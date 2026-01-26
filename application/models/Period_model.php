<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Period_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $this->db->select()->from('timetable_periods');
        if ($id != null) {
            $this->db->where('id', $id);
        }
        $this->db->order_by('time_from', 'asc');
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

    public function add($data)
    {
        $this->db->insert('timetable_periods', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('timetable_periods', $data);
    }

    public function remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('timetable_periods');
    }

    public function get_by_name($name)
    {
        $this->db->select()->from('timetable_periods');
        $this->db->where('name', $name);
        $query = $this->db->get();
        return $query->row();
    }
}
