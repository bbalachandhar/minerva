<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Studentprofilecompleteness_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getStudents() {
        $this->db->select('s.id, s.firstname, s.lastname, s.admission_no, c.class, sec.section,
            s.dob, s.gender, s.mobileno, s.email, s.current_address, s.permanent_address,
            s.father_name, s.father_phone, s.father_occupation, s.mother_name, s.mother_phone, s.mother_occupation,
            s.guardian_name, s.guardian_phone, s.guardian_email, s.adhar_no, s.bank_account_no, s.bank_name,
            s.ifsc_code, s.image, s.father_pic, s.mother_pic, s.guardian_pic, s.previous_school,
            s.hsc_reg_no, s.ug_reg_no, s.emis_num, s.migration_cert_num, s.medium, s.religion, s.cast,
            s.blood_group, s.height, s.weight');
        $this->db->from('students s');
        $this->db->join('student_session ss', 'ss.student_id = s.id');
        $this->db->join('classes c', 'c.id = ss.class_id');
        $this->db->join('sections sec', 'sec.id = ss.section_id', 'left');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getStudentsByClassAndSection($class_id, $section_id) {
        $this->db->select('s.id, s.firstname, s.lastname, s.admission_no, c.class, sec.section,
            s.dob, s.gender, s.mobileno, s.email, s.current_address, s.permanent_address,
            s.father_name, s.father_phone, s.father_occupation, s.mother_name, s.mother_phone, s.mother_occupation,
            s.guardian_name, s.guardian_phone, s.guardian_email, s.adhar_no, s.bank_account_no, s.bank_name,
            s.ifsc_code, s.image, s.father_pic, s.mother_pic, s.guardian_pic, s.previous_school,
            s.hsc_reg_no, s.ug_reg_no, s.emis_num, s.migration_cert_num, s.medium, s.religion, s.cast,
            s.blood_group, s.height, s.weight');
        $this->db->from('students s');
        $this->db->join('student_session ss', 'ss.student_id = s.id');
        $this->db->join('classes c', 'c.id = ss.class_id');
        $this->db->join('sections sec', 'sec.id = ss.section_id', 'left');
        $this->db->where('ss.class_id', $class_id);
        if ($section_id != '') {
            $this->db->where('ss.section_id', $section_id);
        }
        $query = $this->db->get();
        return $query->result_array();
    }
}
?>