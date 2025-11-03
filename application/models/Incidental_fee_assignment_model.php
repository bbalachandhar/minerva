<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Incidental_fee_assignment_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function add($data) {
        $this->db->insert('incidental_fee_assignments', $data);
        return $this->db->insert_id();
    }

    public function get($id = null) {
        $this->db->select('incidental_fee_assignments.*, incidental_fee_types.title as fee_type_title, incidental_fee_types.description as fee_type_description, students.firstname, students.lastname, students.admission_no, classes.class as class_name, sessions.session as session_name');
        $this->db->from('incidental_fee_assignments');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_assignments.incidental_fee_type_id', 'left');
        $this->db->join('students', 'students.id = incidental_fee_assignments.student_id', 'left');
        $this->db->join('classes', 'classes.id = incidental_fee_assignments.class_id', 'left');
        $this->db->join('sessions', 'sessions.id = incidental_fee_assignments.session_id', 'left');

        if ($id) {
            $this->db->where('incidental_fee_assignments.id', $id);
            return $this->db->get()->row_array();
        }
        return $this->db->get()->result_array();
    }

    public function get_by_student_session($student_id, $session_id) {
        $this->db->select('incidental_fee_assignments.*, incidental_fee_types.title as fee_type_title, incidental_fee_types.description as fee_type_description');
        $this->db->from('incidental_fee_assignments');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_assignments.incidental_fee_type_id', 'left');
        $this->db->where('incidental_fee_assignments.student_id', $student_id);
        $this->db->where('incidental_fee_assignments.session_id', $session_id);
        $this->db->where('incidental_fee_assignments.status !=', 'paid'); // Only show outstanding assignments
        return $this->db->get()->result_array();
    }

    public function update($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('incidental_fee_assignments', $data);
        return $this->db->affected_rows();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('incidental_fee_assignments');
        return $this->db->affected_rows();
    }
}
