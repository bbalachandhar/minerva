<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Coe_setup_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAll()
    {
        return $this->db
            ->select('cr.*, s.session, c.class AS class_name, d.department_name AS department_name, st.name AS created_by_name')
            ->from('coe_exam_regulations cr')
            ->join('sessions s', 's.id = cr.session_id', 'left')
            ->join('classes c', 'c.id = cr.class_id', 'left')
            ->join('department d', 'd.id = cr.department_id', 'left')
            ->join('staff st', 'st.id = cr.created_by', 'left')
            ->where('cr.is_active', 1)
            ->order_by('s.session DESC, c.class ASC')
            ->get()->result();
    }

    public function getBySession($session_id)
    {
        return $this->db
            ->select('cr.*, s.session, c.class AS class_name, d.department_name AS department_name')
            ->from('coe_exam_regulations cr')
            ->join('sessions s', 's.id = cr.session_id', 'left')
            ->join('classes c', 'c.id = cr.class_id', 'left')
            ->join('department d', 'd.id = cr.department_id', 'left')
            ->where('cr.session_id', $session_id)
            ->where('cr.is_active', 1)
            ->order_by('c.class ASC')
            ->get()->result();
    }

    public function getByClassSession($class_id, $session_id)
    {
        return $this->db
            ->where('class_id', $class_id)
            ->where('session_id', $session_id)
            ->where('is_active', 1)
            ->get('coe_exam_regulations')->row();
    }

    public function getById($id)
    {
        return $this->db
            ->where('id', $id)
            ->where('is_active', 1)
            ->get('coe_exam_regulations')->row();
    }

    public function insert($data)
    {
        $this->db->insert('coe_exam_regulations', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id)->update('coe_exam_regulations', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->update('coe_exam_regulations', ['is_active' => 0]);
    }
}
