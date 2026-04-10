<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Libraryattendance_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    /**
     * This function searches for a user (student or staff) by their ID.
     * @param int $id The ID to search for.
     * @return array|false Returns user details if found, otherwise false.
     */
    public function get_user_details_by_id($id)
    {
        $id = trim((string) $id);
        if ($id === '') {
            return false;
        }

        // 1) Library card mapping (preferred for scanner flows)
        $this->db->select('member_type, member_id')
            ->from('libarary_members')
            ->where('TRIM(library_card_no)', $id)
            ->limit(1);
        $member_query = $this->db->get();
        if ($member_query->num_rows() > 0) {
            $member = $member_query->row_array();
            if ($member['member_type'] === 'student') {
                $student = $this->get_student_by_pk((int) $member['member_id']);
                if ($student) {
                    return $student;
                }
            }
            if ($member['member_type'] === 'staff') {
                $staff = $this->get_staff_by_pk((int) $member['member_id']);
                if ($staff) {
                    return $staff;
                }
            }
        }

        // 2) Student admission number
        $this->db->select('id, firstname, lastname')
            ->from('students')
            ->where('TRIM(admission_no)', $id)
            ->limit(1);
        $student_adm_query = $this->db->get();
        if ($student_adm_query->num_rows() > 0) {
            $student = $student_adm_query->row_array();
            return [
                'user_id' => $student['id'],
                'name' => trim($student['firstname'] . ' ' . $student['lastname']),
                'user_type' => 'student',
            ];
        }

        // 3) Staff employee/biometric identifiers
        $this->db->select('id, name, surname')
            ->from('staff')
            ->group_start()
            ->where('TRIM(employee_id)', $id)
            ->or_where('TRIM(biometric_id)', $id)
            ->group_end()
            ->limit(1);
        $staff_code_query = $this->db->get();
        if ($staff_code_query->num_rows() > 0) {
            $staff = $staff_code_query->row_array();
            return [
                'user_id' => $staff['id'],
                'name' => trim($staff['name'] . ' ' . $staff['surname']),
                'user_type' => 'staff',
            ];
        }

        // 4) Fallback to numeric primary key lookups
        if (ctype_digit($id)) {
            $student = $this->get_student_by_pk((int) $id);
            if ($student) {
                return $student;
            }

            $staff = $this->get_staff_by_pk((int) $id);
            if ($staff) {
                return $staff;
            }
        }

        return false;
    }

    private function get_student_by_pk($id)
    {
        $this->db->select('id, firstname, lastname')
            ->from('students')
            ->where('id', (int) $id)
            ->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $student = $query->row_array();
            return [
                'user_id' => $student['id'],
                'name' => trim($student['firstname'] . ' ' . $student['lastname']),
                'user_type' => 'student',
            ];
        }
        return false;
    }

    private function get_staff_by_pk($id)
    {
        $this->db->select('id, name, surname')
            ->from('staff')
            ->where('id', (int) $id)
            ->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $staff = $query->row_array();
            return [
                'user_id' => $staff['id'],
                'name' => trim($staff['name'] . ' ' . $staff['surname']),
                'user_type' => 'staff',
            ];
        }
        return false;
    }

    /**
     * This function checks for an existing pending entry (in_time set, out_time NULL) for a user on a given date.
     * @param int $user_id The ID of the user.
     * @param string $user_type The type of user ('student' or 'staff').
     * @param string $date The date in 'YYYY-MM-DD' format.
     * @return array|false Returns the pending entry if found, otherwise false.
     */
    public function get_current_day_pending_entry($user_id, $user_type, $date)
    {
        $this->db->select('*');
        $this->db->from('library_attendance');
        $this->db->where('user_id', $user_id);
        $this->db->where('user_type', $user_type);
        $this->db->where('attendance_date', $date);
        $this->db->where('out_time IS NULL');
        $this->db->order_by('in_time', 'desc'); // Get the latest pending entry
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return false;
    }

    /**
     * This function records a new check-in entry.
     * @param int $user_id The ID of the user.
     * @param string $user_type The type of user ('student' or 'staff').
     * @param string $name The name of the user.
     * @return int The ID of the newly inserted record.
     */
    public function record_check_in($user_id, $user_type, $name)
    {
        $data = [
            'user_id' => $user_id,
            'user_type' => $user_type,
            'name' => $name,
            'attendance_date' => date('Y-m-d'),
            'in_time' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('library_attendance', $data);
        return $this->db->insert_id();
    }

    /**
     * This function records a check-out entry and calculates duration.
     * @param int $entry_id The ID of the attendance record to update.
     * @param string $in_time The in_time of the record in 'YYYY-MM-DD HH:MM:SS' format.
     * @return bool True on success, false on failure.
     */
    public function record_check_out($entry_id, $in_time)
    {
        $out_time = date('Y-m-d H:i:s');

        // Calculate duration
        $in_timestamp = strtotime($in_time);
        $out_timestamp = strtotime($out_time);
        $duration_seconds = $out_timestamp - $in_timestamp;

        // Convert seconds to TIME format (HH:MM:SS)
        $duration_time = gmdate('H:i:s', $duration_seconds);

        $data = [
            'out_time' => $out_time,
            'duration' => $duration_time
        ];
        $this->db->where('id', $entry_id);
        return $this->db->update('library_attendance', $data);
    }

    /**
     * This function fetches attendance records for DataTables display.
     * @param string $date The date to filter records (YYYY-MM-DD).
     * @return string JSON formatted data for DataTables.
     */
    public function get_attendance_records_dt($date = null)
    {
        $this->db->select("library_attendance.id, library_attendance.user_id, library_attendance.user_type, library_attendance.name,
            CASE
                WHEN library_attendance.user_type = 'student' THEN students.admission_no
                WHEN library_attendance.user_type = 'staff' THEN staff.employee_id
                ELSE ''
            END AS library_id,
            CASE
                WHEN library_attendance.user_type = 'student' THEN 'Student'
                WHEN library_attendance.user_type = 'staff' THEN IFNULL(staff_designation_category.name, '')
                ELSE ''
            END AS user_category,
            library_attendance.attendance_date, library_attendance.in_time, library_attendance.out_time, library_attendance.duration", false);
        $this->db->from('library_attendance');
        $this->db->join('students', 'students.id = library_attendance.user_id AND library_attendance.user_type = "student"', 'left');
        $this->db->join('categories', 'categories.id = students.category_id', 'left');
        $this->db->join('staff', 'staff.id = library_attendance.user_id AND library_attendance.user_type = "staff"', 'left');
        $this->db->join('staff_designation_category', 'staff_designation_category.id = staff.category_id', 'left');

        if ($date) {
            $this->db->where('attendance_date', $date);
        }
        
        $this->db->order_by('id', 'desc');
        
        $query = $this->db->get();
        $result = $query->result_array();

        foreach ($result as &$row) {
            $row['library_id']    = isset($row['library_id']) && $row['library_id'] !== null ? $row['library_id'] : '';
            $row['user_category'] = isset($row['user_category']) && $row['user_category'] !== null ? $row['user_category'] : '';
            // Backward-compatible alias for any cached/older DataTables config still expecting admission_no.
            $row['admission_no']  = $row['library_id'];
        }
        unset($row);
        
        $total_records = count($result);

        $json_response = [
            "draw"            => intval($this->input->post('draw')),
            "recordsTotal"    => $total_records,
            "recordsFiltered" => $total_records,
            "data"            => $result
        ];

        return json_encode($json_response);
    }

    /**
     * This function fetches pending checkout records for DataTables display.
     * Records are those with in_time set and out_time is NULL.
     * @return string JSON formatted data for DataTables.
     */
    public function get_pending_checkout_records_dt()
    {
        $this->db->select('id, user_id, user_type, name, attendance_date, in_time, out_time');
        $this->db->from('library_attendance');
        $this->db->where('out_time IS NULL');
        $this->db->order_by('in_time', 'asc');
        $query = $this->db->get();
        $result = $query->result_array();

        $data = [];
        foreach ($result as $row) {
            $in_timestamp = strtotime($row['in_time']);
            $now_timestamp = time();
            $duration_seconds = $now_timestamp - $in_timestamp;
            $duration_time = gmdate('H:i:s', $duration_seconds);
            
            $row['time_spent'] = $duration_time;
            $data[] = $row;
        }

        $total_records = count($data);

        $json_response = [
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => $total_records,
            "recordsFiltered" => $total_records,
            "data" => $data
        ];

        return json_encode($json_response);
    }
}
