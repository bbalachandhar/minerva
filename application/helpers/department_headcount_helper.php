<?php
/**
 * Department-wise Head Count Helper
 *
 * Returns an array of department_name => head_count for active students.
 * Usage: $this->load->helper('department_headcount_helper');
 *        $dept_counts = get_department_headcount($CI); // returns array
 */
if (!function_exists('get_department_headcount')) {
    function get_department_headcount($CI = null) {
        if ($CI === null) $CI = &get_instance();
        $current_session = $CI->setting_model->getCurrentSession();
        $CI->db->select('department.department_name, COUNT(students.id) as head_count')
            ->from('students')
            ->join('student_session', 'student_session.student_id = students.id')
            ->join('classes', 'student_session.class_id = classes.id')
            ->join('department', 'department.id = classes.department_id', 'left')
            ->where('student_session.session_id', $current_session)
            ->where('students.is_active', 'yes')
            ->group_by('department.department_name');
        $query = $CI->db->get();
        $result = array();
        foreach ($query->result() as $row) {
            $dept = $row->department_name ? $row->department_name : 'Unspecified';
            $result[$dept] = (int)$row->head_count;
        }
        return $result;
    }
}
