<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Onlineadmissioncourse_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    /**
     * This function will add new course record in the database
     * @param array $data
     */
    public function add($data)
    {
        $this->db->trans_start();
        $this->db->insert('online_admission_courses', $data);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();
        return $insert_id;
    }

    /**
     * This function will update course record in the database
     * @param array $data
     */
    public function update($data)
    {
        $this->db->trans_start();
        $this->db->where('id', $data['id']);
        $this->db->update('online_admission_courses', $data);
        $this->db->trans_complete();
    }

    /**
     * This function will get record from the database
     * @param int $id
     * @return array
     */
    public function get($id = null)
    {
        $this->db->select('*');
        $this->db->from('online_admission_courses');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    /**
     * This function will delete record from the database
     * @param int $id
     */
    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete('online_admission_courses');
        $this->db->trans_complete();
    }

    /**
     * This function checks if a course name already exists
     * @param string $course_name
     * @param int $id
     * @return boolean
     */
    public function check_course_name_exists($course_name, $id = null)
    {
        $this->db->where('course_name', $course_name);
        if ($id != null) {
            $this->db->where('id !=', $id);
        }
        $query = $this->db->get('online_admission_courses');
        return $query->num_rows() > 0;
    }

    /**
     * This function checks if a course code already exists
     * @param string $course_code
     * @param int $id
     * @return boolean
     */
    public function check_course_code_exists($course_code, $id = null)
    {
        $this->db->where('course_code', $course_code);
        if ($id != null) {
            $this->db->where('id !=', $id);
        }
        $query = $this->db->get('online_admission_courses');
        return $query->num_rows() > 0;
    }
}
