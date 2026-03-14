<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Departmenthead_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($department_id = null) {
        $this->db
            ->select('department.id, department.id as department_id, department.department_name, department.dept_head_id as staff_id, staff.name as staff_name, staff.surname as staff_surname, staff.employee_id')
            ->from('department')
            ->join('staff', 'staff.id = department.dept_head_id', 'left')
            ->where('department.dept_head_id IS NOT NULL', null, false)
            ->order_by('department.department_name', 'ASC');

        if (!empty($department_id)) {
            $this->db->where('department.id', (int) $department_id);
            return $this->db->get()->row_array();
        }

        return $this->db->get()->result_array();
    }

    public function add($data) {
        $department_id = (int) ($data['department_id'] ?? 0);
        $staff_id = (int) ($data['staff_id'] ?? 0);

        if ($department_id <= 0 || $staff_id <= 0) {
            return false;
        }

        return $this->db
            ->where('id', $department_id)
            ->update('department', ['dept_head_id' => $staff_id]);
    }

    public function delete($department_id) {
        $department_id = (int) $department_id;
        if ($department_id <= 0) {
            return false;
        }

        return $this->db
            ->where('id', $department_id)
            ->update('department', ['dept_head_id' => null]);
    }

    public function get_department_head_by_department_id($department_id) {
        $department_id = (int) $department_id;
        if ($department_id <= 0) {
            return null;
        }

        $row = $this->db
            ->select('staff.id, staff.employee_id, staff.name, staff.surname, staff.department, department.department_name')
            ->from('department')
            ->join('staff', 'staff.id = department.dept_head_id', 'inner')
            ->where('department.id', $department_id)
            ->where('staff.is_active', 1)
            ->limit(1)
            ->get()
            ->row_array();

        return !empty($row) ? $row : null;
    }

}
