<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staffpermission_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_permission_count($staff_id, $date)
    {
        $this->db->select('count(*) as count');
        $this->db->from('staff_permissions');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('MONTH(date)', date('m', strtotime($date)));
        $this->db->where('YEAR(date)', date('Y', strtotime($date)));
        $query = $this->db->get();
        return $query->row_array();
    }

    public function add_permission($data)
    {
        $this->db->insert('staff_permissions', $data);
    }
}
