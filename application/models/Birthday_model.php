<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Birthday_model extends MY_Model
{
    protected $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
    }

    public function searchByBirthdayRangeDT($date_from, $date_to, $start, $length, $search_value, $order_column, $order_dir)
    {
        log_message('debug', 'Birthday_model::searchByBirthdayRangeDT called with date_from: ' . $date_from . ' and date_to: ' . $date_to);
        $date_from_formatted = $this->customlib->dateFormatToYYYYMMDD($date_from);
        $date_to_formatted = $this->customlib->dateFormatToYYYYMMDD($date_to);

        $this->db->select('SQL_CALC_FOUND_ROWS classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no,students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,students.mobileno,students.email ,students.state,students.city,students.pincode,students.religion,students.dob ,students.current_address,students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code,students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender,users.id as `user_tbl_id`,users.username,users.password as `user_tbl_password`,users.is_active as `user_tbl_active`,students.app_key,students.parent_app_key', FALSE);
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('categories', 'students.category_id = categories.id', 'left');
        $this->db->join('users', 'users.user_id = students.id', 'left');
        $this->db->where("DATE_FORMAT(students.dob, '%m-%d') >= DATE_FORMAT('" . $date_from_formatted . "', '%m-%d')");
        $this->db->where("DATE_FORMAT(students.dob, '%m-%d') <= DATE_FORMAT('" . $date_to_formatted . "', '%m-%d')");

        if (!empty($search_value)) {
            $this->db->group_start();
            $this->db->like('students.admission_no', $search_value);
            $this->db->or_like('students.firstname', $search_value);
            $this->db->or_like('students.lastname', $search_value);
            $this->db->or_like('classes.class', $search_value);
            $this->db->or_like('students.father_name', $search_value);
            $this->db->or_like('students.dob', $search_value);
            $this->db->or_like('students.gender', $search_value);
            $this->db->or_like('students.guardian_phone', $search_value);
            $this->db->group_end();
        }

        $this->db->order_by($order_column, $order_dir);
        $this->db->limit($length, $start);

        $query  = $this->db->get();
        $result = $query->result_array();

        $total_rows = $this->db->query('SELECT FOUND_ROWS() AS count')->row()->count;

        $data = [];
        foreach ($result as $row) {
            log_message('debug', 'Raw student data row: ' . json_encode($row));
            $data[] = [
                $row['admission_no'],
                $row['firstname'] . ' ' . $row['lastname'],
                $row['class'],
                $row['father_name'],
                $this->customlib->dateformat($row['dob']),
                $row['gender'],
                $row['mobileno']
            ];
        }

        log_message('debug', 'DataTables Result: ' . json_encode($data));
        return [
            "draw" => (int)$this->input->post('draw'),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $total_rows, // For now, assuming no separate filtering count
            "data" => $data
        ];
    }

}