<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cbseexam_admitcard_model extends MY_model
{


    protected $current_session;
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $this->db->select()->from('cbse_template_admitcards');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

    public function getidcardbyid($idcard)
    {
        $this->db->select('*');
        $this->db->from('cbse_template_admitcards');
        $this->db->where('id', $idcard);
        $query = $this->db->get();
        return $query->result();
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('cbse_template_admitcards', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  admit cards id " . $data['id'];
            $action    = "Update";
            $record_id = $id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('cbse_template_admitcards', $data);

            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On admit cards id " . $id;
            $action    = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
        }
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $id;
        }
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('cbse_template_admitcards');
        $message   = DELETE_RECORD_CONSTANT . " On admit cards id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function save_active_status($value){
        $this->db->set('is_active',0); 
        $this->db->update('cbse_template_admitcards');

        $this->db->set('is_active',1); 
        $this->db->where('id',$value);
        $this->db->update('cbse_template_admitcards');
    }

    public function get_active_admitcard()
    {
        $this->db->select('*');
        $this->db->from('cbse_template_admitcards');
        $this->db->where('is_active', 1);
        $query = $this->db->get();
         return $query->row();
    }


     public function getexamlist($id=null)
    {
        $this->db->select()->from('cbse_exams');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }

        $this->db->where('cbse_exams.session_id', $this->current_session);

        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result();
        }
    }


    public function get_cbse_exam_list($class_id,$section_id)
    {
        
      $this->db->select('cbse_exams.*')
        ->from('cbse_exams')
        ->join('cbse_exam_class_sections', 'cbse_exams.id = cbse_exam_class_sections.cbse_exam_id', 'INNER')
        ->join('class_sections', 'class_sections.id = cbse_exam_class_sections.class_section_id', 'INNER')
        ->where('class_sections.class_id', $class_id)
        ->where('class_sections.section_id', $section_id)
		->where('cbse_exams.session_id', $this->current_session);

        $query = $this->db->get();
        return $query->result();

    }
	
	public function get_cbse_exam_students_list($class_id,$section_id,$cbse_exam_id)
    {
        
      $this->db->select('*')
        ->from('cbse_exam_students')
        ->join('student_session', 'student_session.id = cbse_exam_students.student_session_id', 'left')
        ->join('students', 'students.id = student_session.student_id', 'left')
        ->join('categories', 'categories.id = students.category_id', 'left')
        ->where('student_session.class_id', $class_id)
        ->where('student_session.section_id', $section_id)
        ->where('cbse_exam_students.cbse_exam_id', $cbse_exam_id);

        $query = $this->db->get();
        return $query->result();

    }
    // public function get_cbse_exam_students($students_array,$cbse_exam_id)
    // {
    //     $students= implode(",",$students_array);
    //     $this->db->select('*,cbse_exam_students.roll_no as roll_no,students.roll_no as profile_roll_no')
    //     ->from('cbse_exam_students')
    //     ->join('student_session', 'student_session.id = cbse_exam_students.student_session_id', 'left')
    //     ->join('students', 'students.id = student_session.student_id', 'left')
    //     ->join('classes', 'classes.id = student_session.class_id', 'left')
    //     ->join('sections', 'sections.id = student_session.section_id', 'left')
    //     ->where("students.id in  ($students)")
    //     ->where('cbse_exam_students.cbse_exam_id', $cbse_exam_id);
    //     $query = $this->db->get();
    //     return $query->result();
    // }


    public function get_cbse_exam_students($students_array,$cbse_exam_id)
    {
        
        //admission roll no
        $sql     = "SELECT *  FROM `cbse_exam_students` where cbse_exam_id=" . $cbse_exam_id . " and (roll_no IS NULL OR 0)";
        $query   = $this->db->query($sql);
        $results = $query->result();
        if (!empty($results)) {
            $maxid = $this->db->query('SELECT MAX(roll_no) AS `maxid` FROM `cbse_exam_students` where cbse_exam_id='.$cbse_exam_id)->row()->maxid;

            // $student_update = array();
            if ($maxid == 0) {
                $update_roll_no = 100001;
            } else {
                $update_roll_no = $maxid + 1;
            }
            $update_student = array();
            foreach ($results as $res_key => $res_value) {
                $update_student[] = array('id' => $res_value->id, 'roll_no' => $update_roll_no);
                $update_roll_no++;
            }
            $this->db->update_batch('cbse_exam_students', $update_student, 'id');
        }
        //admission roll no


        $students= implode(",",$students_array);
        $this->db->select('*,cbse_exam_students.roll_no as roll_no,students.roll_no as profile_roll_no')
        ->from('cbse_exam_students')
        ->join('student_session', 'student_session.id = cbse_exam_students.student_session_id', 'left')
        ->join('students', 'students.id = student_session.student_id', 'left')
        ->join('classes', 'classes.id = student_session.class_id', 'left')
        ->join('sections', 'sections.id = student_session.section_id', 'left')
        ->where("students.id in  ($students)")
        ->where('cbse_exam_students.cbse_exam_id', $cbse_exam_id);
        $query = $this->db->get();
        return $query->result();
    }

    public function get_cbse_exam_timetable($cbse_exam_id){
        
        // $this->db->select('*')
        // ->from('cbse_exam_timetable')
        // ->join('cbse_exams', 'cbse_exams.id = cbse_exam_timetable.cbse_exam_id', 'left')
        // ->join('subjects', 'cbse_exam_timetable.subject_id = subjects.id', 'left')
        // ->where('cbse_exam_timetable.cbse_exam_id', $cbse_exam_id);
        // $query = $this->db->get();
        // return $query->result();

         $this->db->select('*')
        ->from('cbse_exam_timetable')
        ->join('cbse_exams', 'cbse_exams.id = cbse_exam_timetable.cbse_exam_id', 'left')
        ->join('subjects', 'cbse_exam_timetable.subject_id = subjects.id', 'left')
        ->where('cbse_exam_timetable.cbse_exam_id', $cbse_exam_id)
        ->where('cbse_exams.session_id', $this->current_session);
        $query = $this->db->get();
        return $query->result();
    }








}
