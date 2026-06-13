<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Student_health_model extends CI_Model
{
    public function getStudentByAdmissionNo($admission_no)
    {
        return $this->db
            ->select('s.id, s.admission_no, s.emis_num, s.firstname, s.middlename, s.lastname,
                      s.dob, s.gender, s.blood_group, s.admission_date, s.previous_school,
                      s.father_name, s.father_phone, s.father_occupation,
                      s.mother_name, s.mother_phone, s.mother_occupation,
                      s.current_address, s.mobileno,
                      cl.class, sc.section,
                      ss.session_id')
            ->from('students s')
            ->join('student_session ss', 'ss.student_id = s.id AND ss.is_active = \'yes\'', 'left')
            ->join('classes cl',         'cl.id = ss.class_id',                        'left')
            ->join('sections sc',        'sc.id = ss.section_id',                      'left')
            ->where('s.admission_no', $admission_no)
            ->where('s.is_active', 'yes')
            ->limit(1)
            ->get()->row();
    }

    public function getSiblings($student_id, $father_phone)
    {
        if (!$father_phone) return [];
        return $this->db
            ->select('s.firstname, s.lastname, s.admission_no, cl.class, sc.section')
            ->from('students s')
            ->join('student_session ss', 'ss.student_id = s.id AND ss.is_active = \'yes\'', 'left')
            ->join('classes cl',         'cl.id = ss.class_id',                        'left')
            ->join('sections sc',        'sc.id = ss.section_id',                      'left')
            ->where('s.father_phone', $father_phone)
            ->where('s.id !=', $student_id)
            ->where('s.is_active', 'yes')
            ->get()->result();
    }

    public function getRecord($student_id)
    {
        return $this->db->where('student_id', $student_id)->get('student_health_records')->row();
    }

    public function getRecordByToken($token)
    {
        return $this->db->where('form_token', $token)->get('student_health_records')->row();
    }

    public function saveRecord($student_id, $data)
    {
        $existing = $this->getRecord($student_id);
        $data['student_id'] = $student_id;
        if (!$existing) {
            $data['form_token'] = bin2hex(random_bytes(24));
            $this->db->insert('student_health_records', $data);
            return $data['form_token'];
        } else {
            $this->db->where('student_id', $student_id)->update('student_health_records', $data);
            return $existing->form_token;
        }
    }

    public function getStudentById($student_id)
    {
        return $this->db
            ->select('s.id, s.admission_no, s.emis_num, s.firstname, s.middlename, s.lastname,
                      s.dob, s.gender, s.blood_group, s.admission_date, s.previous_school,
                      s.father_name, s.father_phone, s.father_occupation,
                      s.mother_name, s.mother_phone, s.mother_occupation,
                      s.current_address, s.mobileno,
                      cl.class, sc.section,
                      se.session as session_name')
            ->from('students s')
            ->join('student_session ss', 'ss.student_id = s.id AND ss.is_active = \'yes\'', 'left')
            ->join('classes cl',         'cl.id = ss.class_id',                        'left')
            ->join('sections sc',        'sc.id = ss.section_id',                      'left')
            ->join('sessions se',        'se.id = ss.session_id',                      'left')
            ->where('s.id', $student_id)
            ->limit(1)
            ->get()->row();
    }
}
