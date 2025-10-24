<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('setting_model'); // Load the setting model
    }

    /**
     * Saves raw biometric punch data into the staff_biometric_punches table.
     * Checks for duplicates before inserting.
     *
     * @param array $punches An array of punch data, each with 'staff_id' and 'punch_time'.
     * @return int The number of new punches successfully inserted.
     */
    public function save_raw_biometric_punches($punches) {
        $inserted_count = 0;
        if (empty($punches)) {
            return $inserted_count;
        }

        foreach ($punches as $punch) {
            // Check for duplicate before inserting
            $this->db->where('staff_id', $punch['staff_id']);
            $this->db->where('punch_time', $punch['punch_time']);
            $query = $this->db->get('staff_biometric_punches');

            if ($query->num_rows() == 0) {
                // No duplicate found, insert the punch
                $this->db->insert('staff_biometric_punches', [
                    'staff_id'   => $punch['staff_id'],
                    'punch_time' => $punch['punch_time'],
                ]);
                if ($this->db->affected_rows() > 0) {
                    $inserted_count++;
                }
            }
        }
        return $inserted_count;
    }

    /**
     * Retrieves the last biometric sync datetime from the sch_settings table.
     *
     * @return string|null The datetime string or null if not set.
     */
    public function get_last_biometric_sync_datetime() {
        $setting = $this->setting_model->getSetting();
        if (isset($setting->last_biometric_sync_datetime)) {
            return $setting->last_biometric_sync_datetime;
        }
        return null;
    }

    /**
     * Updates the last biometric sync datetime in the sch_settings table.
     *
     * @param string $datetime The datetime string to set.
     * @return bool True on success, false on failure.
     */
    public function update_last_biometric_sync_datetime($datetime) {
        $current_settings = $this->setting_model->getSetting();
        $setting_id = $current_settings->id; // Assuming 'id' is always present for the main settings record

        $data_to_update = [
            'id' => $setting_id,
            'last_biometric_sync_datetime' => $datetime
        ];
        return $this->setting_model->add($data_to_update);
    }

    /**
     * Retrieves the active biometric device details.
     *
     * @return array|null An array of device details or null if no active device is found.
     */
    public function get_active_biometric_device() {
        $query = $this->db->get_where('biometric_devices', ['is_active' => 1]);
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return null;
    }


    /**
     * Processes raw biometric punches for a given date, calculating daily attendance.
     *
     * @param string $date The date to process (YYYY-MM-DD).
     * @return bool True if processed successfully, false otherwise.
     */
    public function process_daily_biometric_attendance($date) {
        $this->load->model('staff_model');
        $this->load->model('staffattendancemodel');
        $this->load->model('staffAttendaceSetting_model');
        $this->config->load('attendence');

        $attendence_config = $this->config->item('attendence');
        $present_id = $attendence_config['present'];
        $late_id = $attendence_config['late'];
        $half_day_id = $attendence_config['half_day']; // Assuming half_day config exists
        $absent_id = $attendence_config['absent'];

        $raw_punches = $this->db->select('staff_id, punch_time')
                                ->where('DATE(punch_time)', $date)
                                ->order_by('staff_id, punch_time', 'ASC')
                                ->get('staff_biometric_punches')
                                ->result_array();

        if (empty($raw_punches)) {
            log_message('info', 'No raw biometric punches found for ' . $date);
            return true; // Nothing to process
        }

        $staff_punches_by_day = [];
        foreach ($raw_punches as $punch) {
            $staff_punches_by_day[$punch['staff_id']][] = strtotime($punch['punch_time']);
        }

        foreach ($staff_punches_by_day as $staff_id => $timestamps) {
            sort($timestamps); // Ensure punches are in chronological order

            $total_seconds_worked = 0;
            $in_time_final = '';
            $out_time_final = '';

            // Calculate total time worked based on in/out pairs
            for ($i = 0; $i < count($timestamps); $i++) {
                if ($i % 2 == 0) { // This is an 'in' punch
                    if (empty($in_time_final)) {
                        $in_time_final = date('H:i:s', $timestamps[$i]);
                    }
                    
                    // If there's a corresponding 'out' punch
                    if (isset($timestamps[$i+1])) {
                        $total_seconds_worked += ($timestamps[$i+1] - $timestamps[$i]);
                        $out_time_final = date('H:i:s', $timestamps[$i+1]); // Update last out time
                    } else { // Unmatched 'in' punch at the end of day
                        $out_time_final = null; // No final out for summary calculation if last is unmatched
                    }
                }
            }
            
            $total_hours_worked = round($total_seconds_worked / 3600, 2);

            $attendance_type_id = $present_id; // Default to present
            $remark = '';

            $staff_detail = $this->staff_model->get($staff_id);
            $role_id = isset($staff_detail['role_id']) ? $staff_detail['role_id'] : null;

            $full_day_present_threshold_hours = 8.0; // Default value
            if ($role_id) {
                $role_attendance_setting = $this->staffAttendaceSetting_model->getRoleWiseAttendanceSetting($role_id);
                if ($role_attendance_setting && isset($role_attendance_setting->full_day_present_threshold)) {
                    $full_day_present_threshold_hours = (float)$role_attendance_setting->full_day_present_threshold;
                }
            }
            if ($full_day_present_threshold_hours <= 0) {
                $full_day_present_threshold_hours = 8.0; // Ensure it's a valid positive number
            }

            // Get attendance setting for the staff's role if available
            $staff_attendance_setting = null;
            if ($role_id) {
                $staff_attendance_setting = $this->staffAttendaceSetting_model->getAttendanceTypeByRole($role_id, $in_time_final); 
            }

            // Logic for Late/Half Day/Absent
            if ($staff_attendance_setting && $in_time_final && strtotime($in_time_final) > strtotime($staff_attendance_setting->entry_time_to)) {
                $attendance_type_id = $late_id;
                $remark = 'Late arrival';
            }

            if ($total_hours_worked < $full_day_present_threshold_hours && $total_hours_worked > 0) {
                if ($attendance_type_id == $present_id || $attendance_type_id == $late_id) {
                    $attendance_type_id = $half_day_id; // Assume half_day for less than threshold hours, if not already late/absent
                    $remark = 'Half Day (less than ' . $full_day_present_threshold_hours . ' hours)';
                }
            } else if ($total_hours_worked == 0 && count($timestamps) > 0 && ($attendance_type_id != $late_id && $attendance_type_id != $half_day_id)) {
                 $attendance_type_id = $absent_id;
                 $remark = 'No matched in/out punches or incomplete record.';
            } else if ($total_hours_worked == 0 && count($timestamps) == 0) {
                $attendance_type_id = $absent_id;
                $remark = 'No biometric punches recorded.';
            }


            // Prepare data for staff_attendance
            $attendance_record_data = [
                'staff_id'                 => $staff_id,
                'date'                     => $date,
                'staff_attendance_type_id' => $attendance_type_id,
                'remark'                   => $remark,
                'in_time'                  => $in_time_final, // First in-time for the day
                'out_time'                 => $out_time_final, // Last out-time from a matched pair
                'total_hours_worked'       => $total_hours_worked,
                'updated_at'               => date('Y-m-d H:i:s'),
            ];

            // Check if attendance already exists for this staff and date, then update or insert
            $existing_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $date);

            if ($existing_attendance) {
                $this->staffattendancemodel->update($existing_attendance['id'], $attendance_record_data);
            } else {
                $this->staffattendancemodel->add($attendance_record_data);
            }
        }

        return true;
    }

    /**
     * Retrieves raw biometric punches for a specific staff ID and date.
     *
     * @param int $staff_id The ID of the staff member.
     * @param string $date The date (YYYY-MM-DD).
     * @return array An array of raw punch data (punch_time) sorted chronologically.
     */
    public function get_raw_biometric_punches_by_staff_id_and_date($staff_id, $date) {
        $query = $this->db->select('punch_time')
                          ->where('staff_id', $staff_id)
                          ->where('DATE(punch_time)', $date)
                          ->order_by('punch_time', 'ASC')
                          ->get('staff_biometric_punches');
        return $query->result_array();
    }

}
