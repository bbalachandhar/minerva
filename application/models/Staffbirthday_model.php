<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staffbirthday_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function searchByBirthdayRangeDT($date_from, $date_to, $start, $length, $search_value, $order_column, $order_dir)
    {
        $date_from_formatted = $this->customlib->dateFormatToYYYYMMDD($date_from);
        $date_to_formatted = $this->customlib->dateFormatToYYYYMMDD($date_to);

        // Total records query
        $total_rows = $this->db->count_all('staff');

        $this->db->select('staff.*, staff_designation.designation, department.department_name as department_name, roles.name as role');
        $this->db->from('staff');
        $this->db->join('staff_designation', 'staff.designation = staff_designation.id', 'left');
        $this->db->join('department', 'staff.department = department.id', 'left');
        $this->db->join('staff_roles', 'staff.id = staff_roles.staff_id', 'left');
        $this->db->join('roles', 'staff_roles.role_id = roles.id', 'left');
        $this->db->where('staff.is_active', 1);

        $month_day_from = date('m-d', strtotime($date_from_formatted));
        $month_day_to   = date('m-d', strtotime($date_to_formatted));

        if ($month_day_from <= $month_day_to) {
            // Date range within the same calendar year
            $this->db->where("DATE_FORMAT(staff.dob, '%m-%d') >= ", $month_day_from);
            $this->db->where("DATE_FORMAT(staff.dob, '%m-%d') <= ", $month_day_to);
        } else {
            // Date range crosses year boundary (e.g., December to January)
            $this->db->group_start();
            $this->db->where("DATE_FORMAT(staff.dob, '%m-%d') >= ", $month_day_from);
            $this->db->or_where("DATE_FORMAT(staff.dob, '%m-%d') <= ", $month_day_to);
            $this->db->group_end();
        }

        if (!empty($search_value)) {
            $this->db->group_start();
            $this->db->like('staff.employee_id', $search_value);
            $this->db->or_like('staff.name', $search_value);
            $this->db->or_like('staff.email', $search_value);
            $this->db->or_like('staff_designation.designation', $search_value);
            $this->db->or_like('department.department', $search_value);
            $this->db->or_like('roles.name', $search_value);
            $this->db->group_end();
        }

        $this->db->group_by('staff.id');

        // Clone the current query builder state to get the filtered count
        $temp_db = clone $this->db;
        $filtered_rows = $temp_db->get()->num_rows();

        $this->db->order_by($order_column, $order_dir);
        $this->db->limit($length, $start);

        $query  = $this->db->get();
        $result = $query->result_array();

        $data = [];
        foreach ($result as $row) {
            $data[] = [
                $row['employee_id'],
                $row['name'] . ' ' . $row['surname'],
                $row['role'],
                $row['department_name'],
                $this->customlib->dateformat($row['dob']),
                $row['gender'],
                $row['contact_no'],
                $row['email'],
                $row['local_address']
            ];
        }

        return [
            "draw" => (int)$this->input->post('draw'),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        ];
    }
}
