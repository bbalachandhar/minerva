<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staff_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('customlib');
        $this->load->library('enc_lib');
    }

    public function getByEmail($email)
    {
        $this->db->select('staff.*,languages.language,languages.id as language_id,languages.is_rtl,IFNULL(currencies.name,0) as currency_name,IFNULL(currencies.symbol,0) as symbol,IFNULL(currencies.base_price,0) as base_price ,IFNULL(currencies.id,0) as `currency`');
        $this->db->from('staff');
        $this->db->join('languages', 'languages.id=staff.lang_id', 'left');
        $this->db->join('currencies', 'currencies.id=staff.currency_id', 'left');
        $this->db->where('email', $email);
        $query = $this->db->get();
        if ($query->num_rows() == 1) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function checkLogin($data)
    {
        $record = $this->getByEmail($data['email']);
        if ($record) {
            $pass_verify = $this->enc_lib->passHashDyc($data['password'], $record->password);
            if ($pass_verify) {
                $CI =& get_instance();
                $CI->load->model('staffroles_model');
                $roles = $CI->staffroles_model->getStaffRoles($record->id);

                if (!empty($roles)) {
                    $record->roles = array($roles[0]->name => $roles[0]->role_id);
                    return $record;
                }
                return $record;
            }
        }
        return false;
    }

    public function getAll($id = null, $is_active = null)
    {
        $this->db->select("staff.*,staff_designation.designation,department.department_name as department, roles.id as role_id, roles.name as role");
        $this->db->from('staff');
        $this->db->join('staff_designation', "staff_designation.id = staff.designation", "left");
        $this->db->join('staff_roles', "staff_roles.staff_id = staff.id", "left");
        $this->db->join('roles', "roles.id = staff_roles.role_id", "left");
        $this->db->join('department', "department.id = staff.department", "left");

        if ($id != null) {
            $this->db->where('staff.id', $id);
        } else {
            if ($is_active != null) {
                $this->db->where('staff.is_active', $is_active);
            }
            $this->db->order_by('staff.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getProfile($id)
    {
        $this->db->select('staff.*'); // Select all columns directly from the staff table
        $this->db->where("staff.id", $id);
        $this->db->from('staff');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function update($data)
    {
        $this->db->where('id', $data['id']);
        $query = $this->db->update('staff', $data);
        if ($query) {
            return true;
        } else {
            return false;
        }
    }

}
