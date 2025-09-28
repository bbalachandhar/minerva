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
        // Search in students table
        $this->db->select('id, firstname, lastname');
        $this->db->from('students');
        $this->db->where('id', $id);
        $student_query = $this->db->get();
        if ($student_query->num_rows() > 0) {
            $student = $student_query->row_array();
            return [
                'user_id' => $student['id'],
                'name' => $student['firstname'] . ' ' . $student['lastname'],
                'user_type' => 'student'
            ];
        }

        // Search in staff table
        $this->db->select('id, name, surname');
        $this->db->from('staff');
        $this->db->where('id', $id);
        $staff_query = $this->db->get();
        if ($staff_query->num_rows() > 0) {
            $staff = $staff_query->row_array();
            return [
                'user_id' => $staff['id'],
                'name' => $staff['name'] . ' ' . $staff['surname'],
                'user_type' => 'staff'
            ];
        }

        return false; // User not found
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
        $this->datatables
            ->select('id, user_id, user_type, name, attendance_date, in_time, out_time, duration')
            ->from('library_attendance');

        if ($date) {
            $this->datatables->where('attendance_date', $date);
        }
        
        $this->datatables->order_by('id', 'desc'); // Order by latest entry

        return $this->datatables->generate('json');
    }
}
