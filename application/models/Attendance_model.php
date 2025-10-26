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

        $this->load->model('staff_model');

        foreach ($punches as $punch) {
            log_message('debug', 'Processing punch for employee_id: ' . $punch['staff_id']);
            $staff = $this->staff_model->get_by_employee_id($punch['staff_id']);

            if ($staff) {
                log_message('debug', 'Staff found for employee_id: ' . $punch['staff_id'] . '. Staff ID: ' . $staff->id);
                $staff_id = $staff->id;

                // Check for duplicate before inserting
                $this->db->where('staff_id', $staff_id);
                $this->db->where('punch_time', $punch['punch_time']);
                $query = $this->db->get('staff_biometric_punches');

                if ($query->num_rows() == 0) {
                    log_message('debug', 'No duplicate found. Inserting punch for staff_id: ' . $staff_id);
                    // No duplicate found, insert the punch
                    $this->db->insert('staff_biometric_punches', [
                        'staff_id'   => $staff_id,
                        'punch_time' => $punch['punch_time'],
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $inserted_count++;
                    }
                } else {
                    log_message('debug', 'Duplicate found for staff_id: ' . $staff_id . ' and punch_time: ' . $punch['punch_time']);
                }
            } else {
                log_message('error', 'Biometric API: Staff with employee_id ' . $punch['staff_id'] . ' not found.');
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
        $setting = $this->setting_model->getSetting();
        if (!$setting->staff_biometric) {
            log_message('info', 'Staff biometric attendance is disabled.');
            return false;
        }

        $this->load->model('staff_model');
        $this->load->model('staffattendancemodel');
        $this->load->model('staffAttendaceSetting_model');
        $this->load->model('staffattendancemonthlysummary_model');
        $this->config->load('attendence');

        $attendence_config = $this->config->item('attendence');
        $present_id = $attendence_config['present'];
        $late_id = $attendence_config['late'];
        $absent_id = $attendence_config['absent'];
        $half_day_id = $attendence_config['half_day'];
        $permission_first_session_id = $attendence_config['permission_first_session'];
        $permission_second_session_id = $attendence_config['permission_second_session'];

        $office_end_time = $setting->office_end_time;
        $morning_session_end_time = $setting->morning_session_end_time;
        $evening_session_end_time = $setting->evening_session_end_time;
        $afternoon_permission_start_time = date('H:i:s', strtotime($evening_session_end_time) - 3600);
        $max_late_allowed = $setting->max_late_allowed;
        $max_permission_allowed = $setting->max_permission_allowed;

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

            // Get or create monthly summary record
            $month = date('m', strtotime($date));
            $year = date('Y', strtotime($date));
            $summary = $this->staffattendancemonthlysummary_model->get_summary($staff_id, $month, $year);
            if (!$summary) {
                $summary_data = [
                    'staff_id' => $staff_id,
                    'session_id' => $this->current_session,
                    'month' => $month,
                    'year' => $year,
                ];
                $this->staffattendancemonthlysummary_model->add_summary($summary_data);
                $summary = $this->staffattendancemonthlysummary_model->get_summary($staff_id, $month, $year);
            }

            // Get attendance setting for the staff's role if available
            $staff_attendance_setting = null;
            if ($role_id) {
                $staff_attendance_setting = $this->staffAttendaceSetting_model->getAttendanceTypeByRole($role_id, $in_time_final); 
            }

            // Logic for Late/Permission/Absent
            if ($staff_attendance_setting) {
                if (strtotime($in_time_final) > strtotime($staff_attendance_setting->entry_time_to)) {
                    if ($summary->late_count < $max_late_allowed) {
                        $attendance_type_id = $late_id;
                        $remark = 'Late arrival';
                        $summary->late_count++;
                    } else {
                        $attendance_type_id = $absent_id;
                        $remark = 'Late limit exceeded';
                    }
                } elseif (strtotime($in_time_final) > strtotime($staff_attendance_setting->entry_time_from)) {
                    if ($summary->permission_count < $max_permission_allowed) {
                        $attendance_type_id = $permission_first_session_id;
                        $remark = 'Permission';
                        $summary->permission_count++;
                    } else {
                        $attendance_type_id = $absent_id;
                        $remark = 'Permission limit exceeded';
                    }
                }
            }

            // Logic for Half Day and afternoon permission
            if ($out_time_final) {
                if (strtotime($out_time_final) < strtotime($morning_session_end_time)) {
                    $attendance_type_id = $half_day_id;
                    $remark = 'Half Day';
                } elseif (strtotime($out_time_final) > strtotime($evening_session_end_time)) {
                    // Overtime calculation
                    $overtime_seconds = strtotime($out_time_final) - strtotime($office_end_time);
                    if ($overtime_seconds > 0) {
                        $summary->overtime_hours += round($overtime_seconds / 3600, 2);
                    }
                } elseif (strtotime($out_time_final) < strtotime($evening_session_end_time) && strtotime($out_time_final) > strtotime($afternoon_permission_start_time)) {
                    if ($summary->permission_count < $max_permission_allowed) {
                        $attendance_type_id = $permission_second_session_id;
                        $remark = 'Permission';
                        $summary->permission_count++;
                    } else {
                        $attendance_type_id = $absent_id;
                        $remark = 'Permission limit exceeded';
                    }
                } else {
                    $summary->early_leaving_count++;
                }
            }

            // Update summary table
            $this->staffattendancemonthlysummary_model->update_summary($summary->id, (array)$summary);

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
