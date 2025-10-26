<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staffattendancemonthlysummary_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_summary($staff_id, $month, $year)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $query = $this->db->get('staff_attendance_monthly_summary');
        return $query->row();
    }

    public function add_summary($data)
    {
        $this->db->insert('staff_attendance_monthly_summary', $data);
        return $this->db->insert_id();
    }

    public function update_summary($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('staff_attendance_monthly_summary', $data);
    }
}
