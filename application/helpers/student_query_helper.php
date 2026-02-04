<?php
/**
 * Student Query Helper
 *
 * Provides a single source of truth for student queries matching the search page logic.
 * Usage: $this->load->helper('student_query_helper');
 *        $students = get_active_students($CI); // returns array
 *        $count = get_active_students_count($CI); // returns int
 *        $gender_stats = get_active_students_gender_stats($CI); // returns array('male'=>N, 'female'=>N, 'other'=>N)
 */
if (!function_exists('get_active_students')) {
    function get_active_students($CI = null) {
        if ($CI === null) $CI = &get_instance();
        $current_session = $CI->setting_model->getCurrentSession();
        $CI->db->select('students.*, users.role as user_role')
            ->from('students')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('users', 'users.user_id = students.id', 'left')
            ->where('student_session.session_id', $current_session)
            ->where('students.is_active', 'yes')
            ->where('users.role', 'student');
        $query = $CI->db->get();
        return $query->result_array();
    }
}

if (!function_exists('get_active_students_count')) {
    function get_active_students_count($CI = null) {
        if ($CI === null) $CI = &get_instance();
        $current_session = $CI->setting_model->getCurrentSession();
        $CI->db->from('students')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('users', 'users.user_id = students.id', 'left')
            ->where('student_session.session_id', $current_session)
            ->where('students.is_active', 'yes')
            ->where('users.role', 'student');
        return $CI->db->count_all_results();
    }
}

if (!function_exists('get_active_students_gender_stats')) {
    function get_active_students_gender_stats($CI = null) {
        if ($CI === null) $CI = &get_instance();
        $current_session = $CI->setting_model->getCurrentSession();
        $CI->db->select('students.gender, COUNT(*) as total')
            ->from('students')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('users', 'users.user_id = students.id', 'left')
            ->where('student_session.session_id', $current_session)
            ->where('students.is_active', 'yes')
            ->where('users.role', 'student')
            ->group_by('students.gender');
        $query = $CI->db->get();
        $result = array('male'=>0, 'female'=>0, 'other'=>0);
        foreach ($query->result() as $row) {
            $gender = strtolower(trim($row->gender));
            if ($gender === 'male') $result['male'] += $row->total;
            else if ($gender === 'female') $result['female'] += $row->total;
            else $result['other'] += $row->total;
        }
        return $result;
    }
}
