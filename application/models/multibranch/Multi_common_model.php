<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Multi_common_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->db_default = $this->load->database('default', true);
    }

    /*
    This function is used to get student
    */
    public function getStudentCount($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school         = [];
        $school['name'] = $current_db->name;
        $this->db_default->join('student_session', 'student_session.student_id = students.id');
        $this->db_default->where('student_session.session_id', $current_db->session_id);
        $this->db_default->where('students.is_active', 'yes');
        $this->db_default->where('student_session.is_alumni', 0);
        $school['total_student'] = $this->db_default->count_all_results('students');
        $school['db_name']       = $default_db;
        $school['session']       = $current_db->session;
        // gender breakdown
        $gq = $this->db_default->query(
            "SELECT students.gender, COUNT(DISTINCT students.id) as cnt
             FROM students
             INNER JOIN student_session ON student_session.student_id = students.id
             WHERE student_session.session_id = ? AND students.is_active = 'yes'
             AND student_session.is_alumni = 0
             GROUP BY students.gender",
            [$current_db->session_id]
        );
        $school['male_students'] = $school['female_students'] = 0;
        foreach ($gq->result() as $gr) {
            if (strtolower($gr->gender) === 'male')   $school['male_students']   = (int)$gr->cnt;
            if (strtolower($gr->gender) === 'female') $school['female_students'] = (int)$gr->cnt;
        }
        //====================

        $results[$default_db] = $school;

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);

                //===================
                $db_dynamic_name = $db_dynamic->database;

                $current_db     = $school_array[$db_dynamic_name];
                $school         = [];
                $school['name'] = $current_db->name;

                $db_dynamic->join('student_session', 'student_session.student_id = students.id');
                $db_dynamic->where('student_session.session_id', $current_db->session_id);
                $db_dynamic->where('students.is_active', 'yes');
                $db_dynamic->where('student_session.is_alumni', 0);
                $school['total_student']   = $db_dynamic->count_all_results('students');
                $school['db_name']         = $db_dynamic_name;
                $school['session']         = $current_db->session;
                // gender breakdown
                $gq = $db_dynamic->query(
                    "SELECT students.gender, COUNT(DISTINCT students.id) as cnt
                     FROM students
                     INNER JOIN student_session ON student_session.student_id = students.id
                     WHERE student_session.session_id = ? AND students.is_active = 'yes'
                     AND student_session.is_alumni = 0
                     GROUP BY students.gender",
                    [$current_db->session_id]
                );
                $school['male_students'] = $school['female_students'] = 0;
                foreach ($gq->result() as $gr) {
                    if (strtolower($gr->gender) === 'male')   $school['male_students']   = (int)$gr->cnt;
                    if (strtolower($gr->gender) === 'female') $school['female_students'] = (int)$gr->cnt;
                }
                $results[$db_dynamic_name] = $school;
                //====================

            }

        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get student fees based on active current session
    */
    public function getCurrentSessionStudentFees($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];

        $school = [];

        $sql = "SELECT table0.*,`$default_db`.fee_session_groups.fee_groups_id,`$default_db`.fee_session_groups.session_id,`$default_db`.fee_groups.name,`$default_db`.fee_groups.is_system,`$default_db`.fee_groups_feetype.amount as `fee_amount`,`$default_db`.fee_groups_feetype.id as fee_groups_feetype_id,`$default_db`.student_fees_deposite.id as `student_fees_deposite_id`,`$default_db`.student_fees_deposite.amount_detail,`$default_db`.students.id as student_id,`$default_db`.classes.class,`$default_db`.sections.section FROM `$default_db`.`student_fees_master` as table0 INNER JOIN `$default_db`.fee_session_groups on `$default_db`.fee_session_groups.id=table0.fee_session_group_id INNER JOIN `$default_db`.student_session on student_session.id=table0.student_session_id INNER JOIN `$default_db`.students on `$default_db`.students.id=student_session.student_id inner join `$default_db`.classes on student_session.class_id=`$default_db`.classes.id INNER JOIN `$default_db`.sections on `$default_db`.sections.id=student_session.section_id inner join `$default_db`.fee_groups on `$default_db`.fee_groups.id=`$default_db`.fee_session_groups.fee_groups_id INNER JOIN `$default_db`.fee_groups_feetype on `$default_db`.fee_session_groups.id=`$default_db`.fee_groups_feetype.fee_session_group_id LEFT JOIN `$default_db`.student_fees_deposite on `$default_db`.student_fees_deposite.student_fees_master_id=table0.id and `$default_db`.student_fees_deposite.fee_groups_feetype_id=`$default_db`.fee_groups_feetype.id WHERE `$default_db`.student_session.session_id='" . $current_db->session_id . "' and  `$default_db`.fee_session_groups.session_id='" . $current_db->session_id . "'";
    
    
        $query  = $this->db->query($sql);
        $result = $query->result();

        //====================

        $results[$default_db] = $result;

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);

                //===================
                $db_dynamic_name = $db_dynamic->database;

                $current_db = $school_array[$db_dynamic_name];
                $school     = [];

                $sql = "SELECT table$branch_value->id.*,`$db_dynamic_name`.fee_session_groups.fee_groups_id,`$db_dynamic_name`.fee_session_groups.session_id,`$db_dynamic_name`.fee_groups.name,`$db_dynamic_name`.fee_groups.is_system,`$db_dynamic_name`.fee_groups_feetype.amount as `fee_amount`,`$db_dynamic_name`.fee_groups_feetype.id as fee_groups_feetype_id,`$db_dynamic_name`.student_fees_deposite.id as `student_fees_deposite_id`,`$db_dynamic_name`.student_fees_deposite.amount_detail,`$db_dynamic_name`.students.id as student_id,`$db_dynamic_name`.classes.class,`$db_dynamic_name`.sections.section FROM `$db_dynamic_name`.`student_fees_master` table$branch_value->id INNER JOIN `$db_dynamic_name`.fee_session_groups on `$db_dynamic_name`.fee_session_groups.id=table$branch_value->id.fee_session_group_id INNER JOIN `$db_dynamic_name`.student_session on student_session.id=table$branch_value->id.student_session_id INNER JOIN `$db_dynamic_name`.students on `$db_dynamic_name`.students.id=student_session.student_id inner join `$db_dynamic_name`.classes on student_session.class_id=`$db_dynamic_name`.classes.id INNER JOIN `$db_dynamic_name`.sections on `$db_dynamic_name`.sections.id=student_session.section_id inner join `$db_dynamic_name`.fee_groups on `$db_dynamic_name`.fee_groups.id=`$db_dynamic_name`.fee_session_groups.fee_groups_id INNER JOIN `$db_dynamic_name`.fee_groups_feetype on `$db_dynamic_name`.fee_session_groups.id=`$db_dynamic_name`.fee_groups_feetype.fee_session_group_id LEFT JOIN `$db_dynamic_name`.student_fees_deposite on `$db_dynamic_name`.student_fees_deposite.student_fees_master_id=table$branch_value->id.id and `$db_dynamic_name`.student_fees_deposite.fee_groups_feetype_id=`$db_dynamic_name`.fee_groups_feetype.id WHERE `$db_dynamic_name`.student_session.session_id='" . $current_db->session_id . "' and  `$db_dynamic_name`.fee_session_groups.session_id='" . $current_db->session_id . "'";

                $query  = $this->db->query($sql);
                $result = $query->result();

                $results[$db_dynamic_name] = $result;
                //====================

            }

        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get staff list
    */
    public function getStaff($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school         = [];
        $school['name'] = $current_db->name;

        $school['total_staff'] = (int)$this->db_default->query("SELECT COUNT(DISTINCT id) as cnt FROM staff WHERE is_active=1")->row()->cnt;
        $school['db_name']     = $default_db;
        // gender breakdown
        $gq = $this->db_default->query("SELECT gender, COUNT(*) as cnt FROM staff WHERE is_active = 1 GROUP BY gender");
        $school['male_staff'] = $school['female_staff'] = 0;
        foreach ($gq->result() as $gr) {
            if (strtolower($gr->gender) === 'male')   $school['male_staff']   = (int)$gr->cnt;
            if (strtolower($gr->gender) === 'female') $school['female_staff'] = (int)$gr->cnt;
        }

        //====================

        $results[$default_db] = $school;

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);

                //===================
                $db_dynamic_name = $db_dynamic->database;

                $current_db     = $school_array[$db_dynamic_name];
                $school         = [];
                $school['name'] = $current_db->name;

                $school['total_staff'] = (int)$db_dynamic->query("SELECT COUNT(DISTINCT id) as cnt FROM staff WHERE is_active=1")->row()->cnt;
                $school['db_name']     = $db_dynamic_name;
                // gender breakdown
                $gq = $db_dynamic->query("SELECT gender, COUNT(*) as cnt FROM staff WHERE is_active = 1 GROUP BY gender");
                $school['male_staff'] = $school['female_staff'] = 0;
                foreach ($gq->result() as $gr) {
                    if (strtolower($gr->gender) === 'male')   $school['male_staff']   = (int)$gr->cnt;
                    if (strtolower($gr->gender) === 'female') $school['female_staff'] = (int)$gr->cnt;
                }

                $results[$db_dynamic_name] = $school;
                //====================
            }

        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get staff attendance based on date
    */
    public function getStaffAttendance($date, $school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school['name'] = $current_db->name;
        $sql            = "select `$default_db`.staff_attendance.staff_attendance_type_id,`$default_db`.staff_attendance_type.type as `att_type`,`$default_db`.staff_attendance_type.key_value as `key`,`$default_db`.staff_attendance.remark,table0.name,table0.surname,table0.employee_id,table0.contact_no,table0.email,`$default_db`.roles.name as user_type,IFNULL(`$default_db`.staff_attendance.date, 'xxx') as date, IFNULL(`$default_db`.staff_attendance.id, 0) as attendence_id, table0.id as id from `$default_db`.`staff` table0  left join `$default_db`.staff_roles on (table0.id = `$default_db`.staff_roles.staff_id) left join `$default_db`.roles on (`$default_db`.roles.id = `$default_db`.staff_roles.role_id) left join `$default_db`.staff_attendance on (table0.id = `$default_db`.staff_attendance.staff_id) and `$default_db`.staff_attendance.date = " . $this->db->escape($date) . " left join `$default_db`.staff_attendance_type on `$default_db`.staff_attendance_type.id = `$default_db`.staff_attendance.staff_attendance_type_id  where table0.is_active = 1 ";

        $query  = $this->db->query($sql);
        $result = $query->result();

        //====================

        $results[$default_db] = $result;

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);

                //===================
                $db_dynamic_name = $db_dynamic->database;

                $current_db = $school_array[$db_dynamic_name];

                $school['name'] = $current_db->name;
                $sql            = "select `$db_dynamic_name`.staff_attendance.staff_attendance_type_id,`$db_dynamic_name`.staff_attendance_type.type as `att_type`,`$db_dynamic_name`.staff_attendance_type.key_value as `key`,`$db_dynamic_name`.staff_attendance.remark,table$branch_value->id.name,table$branch_value->id.surname,table$branch_value->id.employee_id,table$branch_value->id.contact_no,table$branch_value->id.email,`$db_dynamic_name`.roles.name as user_type,IFNULL(`$db_dynamic_name`.staff_attendance.date, 'xxx') as date, IFNULL(`$db_dynamic_name`.staff_attendance.id, 0) as attendence_id, table$branch_value->id.id as id from `$db_dynamic_name`.`staff` table$branch_value->id  left join `$db_dynamic_name`.staff_roles on (table$branch_value->id.id = `$db_dynamic_name`.staff_roles.staff_id) left join `$db_dynamic_name`.roles on (`$db_dynamic_name`.roles.id = `$db_dynamic_name`.staff_roles.role_id) left join `$db_dynamic_name`.staff_attendance on (table$branch_value->id.id = `$db_dynamic_name`.staff_attendance.staff_id) and `$db_dynamic_name`.staff_attendance.date = " . $this->db->escape($date) . " left join `$db_dynamic_name`.staff_attendance_type on `$db_dynamic_name`.staff_attendance_type.id = `$db_dynamic_name`.staff_attendance.staff_attendance_type_id  where table$branch_value->id.is_active = 1 ";

                $query  = $this->db->query($sql);
                $result = $query->result();

                $results[$db_dynamic_name] = $result;
                //====================

            }

        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get offline admitted student list
    */
    public function getOfflineStudentAdmissions($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];

        $school_arr         = sessionYearDetails($current_db->session, $current_db->start_month);
        $school_month_start = $school_arr['month_start'];
        $school_month_end   = $school_arr['month_end'];

        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;
        $this->db_default->join('student_session', 'student_session.student_id = students.id');
        $this->db_default->join('classes', 'student_session.class_id = classes.id');
        $this->db_default->join('sections', 'sections.id = student_session.section_id');
        $this->db_default->join('categories', 'students.category_id = categories.id', 'left');
        $this->db_default->join('users', 'users.user_id = students.id', 'left');
        $this->db_default->where('student_session.session_id', $current_db->session_id);
        $this->db_default->where('admission_date >=', $school_month_start);
        $this->db_default->where('admission_date <=', $school_month_end);
        $this->db_default->where('users.role', 'student');
        $school['offline_admission'] = $this->db_default->count_all_results('students');
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;

        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);

                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];
                $school_arr         = sessionYearDetails($db_dynamic_array->session, $db_dynamic_array->start_month);
                $school_month_start = $school_arr['month_start'];
                $school_month_end   = $school_arr['month_end'];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;
                $db_dynamic->join('student_session', 'student_session.student_id = students.id');
                $db_dynamic->join('classes', 'student_session.class_id = classes.id');
                $db_dynamic->join('sections', 'sections.id = student_session.section_id');
                $db_dynamic->join('categories', 'students.category_id = categories.id', 'left');
                $db_dynamic->join('users', 'users.user_id = students.id', 'left');
                $db_dynamic->where('student_session.session_id', $db_dynamic_array->session_id);
                $db_dynamic->where('admission_date >=', $school_month_start);
                $db_dynamic->where('admission_date <=', $school_month_end);
                $db_dynamic->where('users.role', 'student');
                $school['offline_admission'] = $db_dynamic->count_all_results('students');
                $school['db_name']         = $db_dynamic_name;

                $results[$db_dynamic_name] = $school;

                //====================
            }

        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get online admitted admission student list
    */
    public function getOnlineStudentAdmissions($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school_arr         = sessionYearDetails($current_db->session, $current_db->start_month);
        $school_month_start = $school_arr['month_start'];
        $school_month_end   = $school_arr['month_end'];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;     
        $this->db_default->join('class_sections', 'online_admissions.class_section_id = class_sections.id');
        $this->db_default->where('admission_date >=', $school_month_start);
        $this->db_default->where('admission_date <=', $school_month_end);
        $school['online_admission'] = $this->db_default->count_all_results('online_admissions');
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;

        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);

                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];
                $school_arr         = sessionYearDetails($db_dynamic_array->session, $db_dynamic_array->start_month);
                $school_month_start = $school_arr['month_start'];
                $school_month_end   = $school_arr['month_end'];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;
                $db_dynamic->join('class_sections', 'online_admissions.class_section_id = class_sections.id');                $db_dynamic->where('admission_date >=', $school_month_start);
                $db_dynamic->where('admission_date <=', $school_month_end);
                $school['online_admission'] = $db_dynamic->count_all_results('online_admissions');
                $school['db_name']         = $db_dynamic_name;
                $results[$db_dynamic_name] = $school;
                //====================

            }
        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get book
    */
    public function getBooks($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];

        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;     
        $school['total_books'] = $this->db_default->count_all_results('books');
        $school['db_name']         = $default_db;

        $results[$default_db] = $school;

        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);
  
                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];            

        $school            = [];
        $school['name']    = $db_dynamic_array->name;
        $school['session'] = $db_dynamic_array->session;     
        $school['total_books'] = $db_dynamic->count_all_results('books');
        $school['db_name']     = $db_dynamic_name;
        $results[$db_dynamic_name] = $school;
        //====================

            }
        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get library members
    */
    public function getLibararyMembers($school_array = [])
    {
        $results = [];
        //===================
        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;      
        $school['total_members'] = $this->db_default->count_all_results('libarary_members');
        $school['db_name']         = $default_db;

        $results[$default_db] = $school;
        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);  
                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];       

                $school            = [];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;    
                $school['total_members'] = $db_dynamic->count_all_results('libarary_members');
                $school['db_name']         = $db_dynamic_name;
                $results[$db_dynamic_name] = $school;
                //====================
            }
        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get issue book
    */
    public function getLibararyBookIssued($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;
        $this->db_default->where('is_returned',0);     
        $school['total_book_issued'] = $this->db_default->count_all_results('book_issues');
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;

        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);
  
                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name]; 
                $school            = [];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;
                $db_dynamic->where('is_returned',0);     
                $school['total_book_issued'] = $db_dynamic->count_all_results('book_issues');
                $school['db_name']         = $db_dynamic_name;
                $results[$db_dynamic_name] = $school;
                //====================
            }
        }
        //=========================================
        return $results;
    }

    /*
     * getAcademicsSummary — consolidated replacement for the six separate calls:
     *   getBooks + getLibararyMembers + getLibararyBookIssued +
     *   getOfflineStudentAdmissions + getOnlineStudentAdmissions + getAlumniStudents
     *
     * Opens each branch DB connection ONCE instead of six times, reducing
     * DB connection overhead by ~83% for academics_async.
     */
    public function getAcademicsSummary($school_array = [])
    {
        $results    = [];
        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];

        $sa    = sessionYearDetails($current_db->session, $current_db->start_month);
        $ms    = $sa['month_start'];
        $me    = $sa['month_end'];
        $sid   = (int)$current_db->session_id;

        $results[$default_db] = [
            'name'                 => $current_db->name,
            'session'              => $current_db->session,
            'db_name'              => $default_db,
            'total_books'          => (int)$this->db_default->query("SELECT COUNT(*) AS c FROM books")->row()->c,
            'total_members'        => (int)$this->db_default->query("SELECT COUNT(*) AS c FROM libarary_members")->row()->c,
            'total_book_issued'    => (int)$this->db_default->query("SELECT COUNT(*) AS c FROM book_issues WHERE is_returned=0")->row()->c,
            'total_alumni_student' => (int)$this->db_default->query("SELECT COUNT(*) AS c FROM alumni_students")->row()->c,
            'offline_admission'    => (int)$this->db_default->query(
                "SELECT COUNT(DISTINCT students.id) AS c FROM students
                 INNER JOIN student_session ON student_session.student_id = students.id
                 INNER JOIN users           ON users.user_id = students.id
                 WHERE student_session.session_id = ?
                   AND students.admission_date >= ? AND students.admission_date <= ?
                   AND users.role = 'student'",
                [$sid, $ms, $me])->row()->c,
            'online_admission'     => (int)$this->db_default->query(
                "SELECT COUNT(*) AS c FROM online_admissions
                 INNER JOIN class_sections ON online_admissions.class_section_id = class_sections.id
                 WHERE online_admissions.admission_date >= ? AND online_admissions.admission_date <= ?",
                [$ms, $me])->row()->c,
        ];

        $this->load->model("multibranch_model");
        $branches = $this->multibranch_model->get();
        if (!empty($branches)) {
            foreach ($branches as $branch_value) {
                $db_dynamic      = $this->load->database('branch_' . $branch_value->id, true);
                $db_dynamic_name = $db_dynamic->database;
                $cd              = $school_array[$db_dynamic_name];
                $sa2             = sessionYearDetails($cd->session, $cd->start_month);
                $ms2             = $sa2['month_start'];
                $me2             = $sa2['month_end'];
                $sid2            = (int)$cd->session_id;

                $results[$db_dynamic_name] = [
                    'name'                 => $cd->name,
                    'session'              => $cd->session,
                    'db_name'              => $db_dynamic_name,
                    'total_books'          => (int)$db_dynamic->query("SELECT COUNT(*) AS c FROM books")->row()->c,
                    'total_members'        => (int)$db_dynamic->query("SELECT COUNT(*) AS c FROM libarary_members")->row()->c,
                    'total_book_issued'    => (int)$db_dynamic->query("SELECT COUNT(*) AS c FROM book_issues WHERE is_returned=0")->row()->c,
                    'total_alumni_student' => (int)$db_dynamic->query("SELECT COUNT(*) AS c FROM alumni_students")->row()->c,
                    'offline_admission'    => (int)$db_dynamic->query(
                        "SELECT COUNT(DISTINCT students.id) AS c FROM students
                         INNER JOIN student_session ON student_session.student_id = students.id
                         INNER JOIN users           ON users.user_id = students.id
                         WHERE student_session.session_id = ?
                           AND students.admission_date >= ? AND students.admission_date <= ?
                           AND users.role = 'student'",
                        [$sid2, $ms2, $me2])->row()->c,
                    'online_admission'     => (int)$db_dynamic->query(
                        "SELECT COUNT(*) AS c FROM online_admissions
                         INNER JOIN class_sections ON online_admissions.class_section_id = class_sections.id
                         WHERE online_admissions.admission_date >= ? AND online_admissions.admission_date <= ?",
                        [$ms2, $me2])->row()->c,
                ];
            }
        }
        return $results;
    }

    /*
    This function is used to get alumni student
    */
    public function getAlumniStudents($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;    
        $school['total_alumni_student'] = $this->db_default->count_all_results('alumni_students');
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;
        
        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);
  
                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];
                $school            = [];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;    
                $school['total_alumni_student'] = $db_dynamic->count_all_results('alumni_students');
                $school['db_name']         = $db_dynamic_name;
                $results[$db_dynamic_name] = $school;
                //====================
            }
        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get user log detail
    */
    public function getUserLog($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;    
        $school['total_userlog'] = $this->db_default->count_all_results('userlog');
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;
        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);  
                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];
                $school            = [];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;    
                $school['total_userlog'] = $db_dynamic->count_all_results('userlog');
                $school['db_name']         = $db_dynamic_name;
                $results[$db_dynamic_name] = $school;
                //====================
            }
        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get student transport fees
    */
    public function getStudentTransportFees($school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;
         $this->db_default->select('student_transport_fees.*,route_pickup_point.fees,transport_feemaster.month,transport_feemaster.due_date ,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail`,students.id as `student_id`');
        $this->db_default->from('student_transport_fees');
        $this->db_default->join('transport_feemaster' ,'transport_feemaster.id =student_transport_fees.transport_feemaster_id');   
        $this->db_default->join('student_fees_deposite' ,'student_fees_deposite.student_transport_fee_id=student_transport_fees.id','LEFT');
        $this->db_default->join('student_session' ,'student_session.id= student_transport_fees.student_session_id'); 
        $this->db_default->join('classes' ,'classes.id= student_session.class_id');  
        $this->db_default->join('sections' ,'sections.id= student_session.section_id');  
        $this->db_default->join('students' ,'students.id=student_session.student_id');  
        $this->db_default->join('route_pickup_point' ,'route_pickup_point.id = student_transport_fees.route_pickup_point_id'); 
        $this->db_default->join('categories' ,'students.category_id = categories.id','LEFT');
        $q =$this->db_default->get();
        $total_fees=$q->result();     
        $school['total_fees_record'] = $total_fees;
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;
        
        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
                $is_branch_available = true;
                foreach ($branches as $branch_key => $branch_value) {

                        $school     = [];
                        $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);
  
                        //===================
                        $db_dynamic_name = $db_dynamic->database;

                        $db_dynamic_array = $school_array[$db_dynamic_name];
                        $school            = [];
                        $school['name']    = $db_dynamic_array->name;
                        $school['session'] = $db_dynamic_array->session;

                        $db_dynamic->select('student_transport_fees.*,route_pickup_point.fees,transport_feemaster.month,transport_feemaster.due_date ,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail`,students.id as `student_id`');
                        $db_dynamic->from('student_transport_fees');
                        $db_dynamic->join('transport_feemaster' ,'transport_feemaster.id =student_transport_fees.transport_feemaster_id');   
                        $db_dynamic->join('student_fees_deposite' ,'student_fees_deposite.student_transport_fee_id=student_transport_fees.id','LEFT');
                        $db_dynamic->join('student_session' ,'student_session.id= student_transport_fees.student_session_id'); 
                        $db_dynamic->join('classes' ,'classes.id= student_session.class_id');  
                        $db_dynamic->join('sections' ,'sections.id= student_session.section_id');  
                        $db_dynamic->join('students' ,'students.id=student_session.student_id');  
                        $db_dynamic->join('route_pickup_point' ,'route_pickup_point.id = student_transport_fees.route_pickup_point_id');  
                        $db_dynamic->join('categories' ,'students.category_id = categories.id','LEFT');
                        $q=$db_dynamic->get();
                        $total_fees=$q->result();     
                        $school['total_fees_record'] = $total_fees;
                        $school['db_name']         = $db_dynamic_name;
                        $results[$db_dynamic_name] = $school;
                        //====================
                }
        }
        //=========================================
        return $results;
    }

    /*
    This function is used to get payrol of staff from all branch based of month and year
    */
    public function getStaffPayslipCount($month, $year, $school_array = [])
    {
        $results = [];
        //===================

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];
        $school            = [];
        $school['name']    = $current_db->name;
        $school['session'] = $current_db->session;
        $this->db_default->select('staff_payslip.*,');
        $this->db_default->from('staff_payslip');
        $this->db_default->join('staff' ,'staff.id =staff_payslip.staff_id');   
        $this->db_default->where('staff_payslip.month' ,$month);   
        $this->db_default->where('staff_payslip.year' ,$year);        
        $q =$this->db_default->get();
        $total_fees=$q->result();     
        $school['total_payroll_record'] = $total_fees;
        $school['db_name']         = $default_db;
        $results[$default_db] = $school;
        //====================

        $condition = array();
        $this->load->model("multibranch_model");
        //=============================
        $branches            = $this->multibranch_model->get();
        $is_branch_available = false;
        if (!empty($branches)) {
            $is_branch_available = true;
            foreach ($branches as $branch_key => $branch_value) {

                $school     = [];
                $db_dynamic = $this->load->database('branch_' . $branch_value->id, true);
  
                //===================
                $db_dynamic_name = $db_dynamic->database;
                $db_dynamic_array = $school_array[$db_dynamic_name];
                $school            = [];
                $school['name']    = $db_dynamic_array->name;
                $school['session'] = $db_dynamic_array->session;
                $db_dynamic->select('staff_payslip.*,');
                $db_dynamic->from('staff_payslip');
                $db_dynamic->join('staff' ,'staff.id =staff_payslip.staff_id');   
                $db_dynamic->where('staff_payslip.month' ,$month);  
                $db_dynamic->where('staff_payslip.year' ,$year);  
                $q=$db_dynamic->get();
                $total_fees=$q->result();     
                $school['total_payroll_record'] = $total_fees;
                $school['db_name']         = $db_dynamic_name;
                $results[$db_dynamic_name] = $school;
                //====================
            }
        }
        //=========================================
        return $results;
    }

    /*
    This function returns inventory summary (total value, stock, categories) for all branches
    */
    public function getInventorySummary($school_array = [], $branch_list = null)
    {
        $results = [];

        $default_db = $this->db_default->database;
        $current_db = $school_array[$default_db];

        $sql = "SELECT ic.item_category AS category_name,
                  COUNT(DISTINCT i.id) AS item_types,
                  IFNULL(SUM(ist.quantity),0) AS total_stock,
                  IFNULL(ROUND(SUM(ist.quantity * ist.purchase_price)),0) AS total_value
                FROM `$default_db`.item_category ic
                LEFT JOIN `$default_db`.item i ON i.item_category_id = ic.id
                LEFT JOIN `$default_db`.item_stock ist ON ist.item_id = i.id AND ist.is_active = 'yes'
                WHERE ic.is_active = 'yes'
                GROUP BY ic.id
                ORDER BY total_value DESC";

        $categories  = $this->db->query($sql)->result();
        $total_value = 0;
        $total_stock = 0;
        $total_items = 0;
        foreach ($categories as $cat) {
            $total_value += (float) $cat->total_value;
            $total_stock += (int)   $cat->total_stock;
            $total_items += (int)   $cat->item_types;
        }

        $results[$default_db] = [
            'name'        => $current_db->name,
            'db_name'     => $default_db,
            'total_value' => $total_value,
            'total_stock' => $total_stock,
            'total_items' => $total_items,
            'categories'  => $categories,
        ];

        $this->load->model("multibranch_model");
        // Accept pre-loaded branch list to avoid redundant multi_branch query
        $branches = ($branch_list !== null) ? $branch_list : $this->multibranch_model->get();

        if (!empty($branches)) {
            foreach ($branches as $branch_value) {
                $db_dynamic      = $this->load->database('branch_' . $branch_value->id, true);
                $db_dynamic_name = $db_dynamic->database;
                $current_db      = $school_array[$db_dynamic_name];

                $sql = "SELECT ic.item_category AS category_name,
                          COUNT(DISTINCT i.id) AS item_types,
                          IFNULL(SUM(ist.quantity),0) AS total_stock,
                          IFNULL(ROUND(SUM(ist.quantity * ist.purchase_price)),0) AS total_value
                        FROM `$db_dynamic_name`.item_category ic
                        LEFT JOIN `$db_dynamic_name`.item i ON i.item_category_id = ic.id
                        LEFT JOIN `$db_dynamic_name`.item_stock ist ON ist.item_id = i.id AND ist.is_active = 'yes'
                        WHERE ic.is_active = 'yes'
                        GROUP BY ic.id
                        ORDER BY total_value DESC";

                $categories  = $this->db->query($sql)->result();
                $total_value = 0;
                $total_stock = 0;
                $total_items = 0;
                foreach ($categories as $cat) {
                    $total_value += (float) $cat->total_value;
                    $total_stock += (int)   $cat->total_stock;
                    $total_items += (int)   $cat->item_types;
                }

                $results[$db_dynamic_name] = [
                    'name'        => $current_db->name,
                    'db_name'     => $db_dynamic_name,
                    'total_value' => $total_value,
                    'total_stock' => $total_stock,
                    'total_items' => $total_items,
                    'categories'  => $categories,
                ];
            }
        }

        return $results;
    }

    /**
     * Get daily attendance summary (student + staff) for all branches.
     * Returns an array keyed by db_name with keys:
     *   student_present, student_boys_present, student_girls_present,
     *   student_absent, student_total,
     *   staff_present, staff_absent, staff_total
     */
    public function getAttendanceSummary($date, $school_array = [])
    {
        $results     = [];
        $default_db  = $this->db_default->database;
        $branch_info = $school_array[$default_db];

        // ── Home branch ──────────────────────────────────────────────────────
        $session_id = $branch_info->session_id;
        $results[$default_db] = $this->_attendance_summary_for_db(
            $this->db, $default_db, $date, $session_id
        );

        // ── Branch DBs ───────────────────────────────────────────────────────
        $this->load->model('multibranch_model');
        $branches = $this->multibranch_model->get();
        if (!empty($branches)) {
            foreach ($branches as $branch_value) {
                $db_dyn      = $this->load->database('branch_' . $branch_value->id, true);
                $db_dyn_name = $db_dyn->database;
                if (!isset($school_array[$db_dyn_name])) continue;
                $session_id  = $school_array[$db_dyn_name]->session_id;
                $results[$db_dyn_name] = $this->_attendance_summary_for_db(
                    $db_dyn, $db_dyn_name, $date, $session_id
                );
            }
        }

        return $results;
    }

    /**
     * Run student + staff attendance aggregate queries against one DB connection.
     */
    private function _attendance_summary_for_db($db, $db_name, $date, $session_id)
    {
        // Student attendance (type IDs: 1=Present,2=LateExcuse,3=Late,6=HalfDay → present;  4=Absent)
        $stu = $db->query(
            "SELECT
               SUM(CASE WHEN sa.attendence_type_id IN (1,2,3,6) THEN 1 ELSE 0 END)                                     AS present,
               SUM(CASE WHEN sa.attendence_type_id IN (1,2,3,6) AND s.gender = 'Male'   THEN 1 ELSE 0 END)             AS boys_present,
               SUM(CASE WHEN sa.attendence_type_id IN (1,2,3,6) AND s.gender = 'Female' THEN 1 ELSE 0 END)             AS girls_present,
               SUM(CASE WHEN sa.attendence_type_id = 4 THEN 1 ELSE 0 END)                                              AS absent,
               COUNT(sa.id)                                                                                             AS total_marked
             FROM `$db_name`.student_attendences sa
             JOIN `$db_name`.student_session ss ON ss.id = sa.student_session_id
             JOIN `$db_name`.students s          ON s.id  = ss.student_id AND s.is_active = 'yes'
             WHERE sa.date = ? AND ss.session_id = ?",
            [$date, $session_id]
        )->row();

        // Staff attendance (key_values that count as present vs absent)
        $sta = $db->query(
            "SELECT
               SUM(CASE WHEN sat.key_value IN ('P','FHL','HD','FHP','SHL','SHP') THEN 1 ELSE 0 END) AS present,
               SUM(CASE WHEN sat.key_value IN ('A','FHA','SHA')                  THEN 1 ELSE 0 END) AS absent,
               COUNT(sa.id)                                                                         AS total
             FROM `$db_name`.staff_attendance sa
             JOIN `$db_name`.staff_attendance_type sat ON sat.id = sa.staff_attendance_type_id
             WHERE sa.date = ?",
            [$date]
        )->row();

        return [
            'student_present'      => (int) ($stu->present       ?? 0),
            'student_boys_present' => (int) ($stu->boys_present  ?? 0),
            'student_girls_present'=> (int) ($stu->girls_present ?? 0),
            'student_absent'       => (int) ($stu->absent        ?? 0),
            'student_total'        => (int) ($stu->total_marked  ?? 0),
            'staff_present'        => (int) ($sta->present ?? 0),
            'staff_absent'         => (int) ($sta->absent  ?? 0),
            'staff_total'          => (int) ($sta->total   ?? 0),
        ];
    }
}