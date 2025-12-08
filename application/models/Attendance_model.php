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
            log_message('debug', 'Processing punch for biometric_id: ' . $punch['staff_id']);
            $staff = $this->staff_model->get_by_biometric_id($punch['staff_id']);

            if ($staff) {
                log_message('debug', 'Staff found for biometric_id: ' . $punch['staff_id'] . '. Staff ID: ' . $staff->id);
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
                log_message('error', 'Biometric API: Staff with biometric_id ' . $punch['staff_id'] . ' not found.');
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
        $first_half_absent_id = $attendence_config['first_half_absent'];
        $second_half_absent_id = $attendence_config['second_half_absent'];

        $office_end_time = $setting->office_end_time;
        $morning_session_end_time = $setting->morning_session_end_time;
        $evening_session_end_time = $setting->evening_session_end_time;
        $max_late_allowed = $setting->max_late_allowed;
        $max_permission_allowed = $setting->max_permission_allowed;

        $all_staff = $this->staff_model->get(); 
        $staff_punches_by_day = [];

        // Initialize all active staff to handle absentees
        foreach ($all_staff as $staff_member) {
            $staff_punches_by_day[$staff_member['id']] = [];
        }
        
        $raw_punches = $this->db->select('staff_id, punch_time')
                                ->where('DATE(punch_time)', $date)
                                ->order_by('staff_id, punch_time', 'ASC')
                                ->get('staff_biometric_punches')
                                ->result_array();

        foreach ($raw_punches as $punch) {
            if(isset($staff_punches_by_day[$punch['staff_id']])){
                 $staff_punches_by_day[$punch['staff_id']][] = strtotime($punch['punch_time']);
            }
        }

        foreach ($staff_punches_by_day as $staff_id => $timestamps) {

            $staff_detail = $this->staff_model->get($staff_id);
            $role_id = isset($staff_detail['role_id']) ? $staff_detail['role_id'] : null;

            if (!$role_id) {
                continue; // Skip staff without a role
            }

            // Get settings for all relevant attendance types for this role
            $present_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $present_id);
            $late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $late_id);
            $fhp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_first_session_id);

            if (empty($timestamps)) {
                // No punches for the day, mark as Absent
                $attendance_type_id = $absent_id;
                $in_time_final = null;
                $out_time_final = null;
                $total_hours_worked = 0;
                $remark = 'No punch found';
            } else {
                sort($timestamps);
                $in_time_final = date('H:i:s', $timestamps[0]);
                $out_time_final = (count($timestamps) > 1) ? date('H:i:s', end($timestamps)) : null;

                $total_seconds_worked = 0;
                for ($i = 0; $i < count($timestamps); $i += 2) {
                    if (isset($timestamps[$i + 1])) {
                        $total_seconds_worked += ($timestamps[$i + 1] - $timestamps[$i]);
                    }
                }
                $total_hours_worked = round($total_seconds_worked / 3600, 2);

                // Default to present and then evaluate rules
                $attendance_type_id = $present_id;
                $remark = '';

                $month = date('m', strtotime($date));
                $year = date('Y', strtotime($date));
                $summary = $this->staffattendancemonthlysummary_model->get_summary($staff_id, $month, $year);
                if (!$summary) {
                    $summary_data = ['staff_id' => $staff_id, 'session_id' => $this->current_session, 'month' => $month, 'year' => $year];
                    $this->staffattendancemonthlysummary_model->add_summary($summary_data);
                    $summary = $this->staffattendancemonthlysummary_model->get_summary($staff_id, $month, $year);
                }

                // Morning Session Logic (Arrival)
                if ($present_settings && strtotime($in_time_final) > strtotime($present_settings->entry_time_to)) {
                    // Arrived after the 'Present' window
                    if ($fhp_settings && strtotime($in_time_final) <= strtotime($fhp_settings->entry_time_to)) {
                        // It's within the FHP window
                        if ($summary->permission_count < $max_permission_allowed) {
                            $attendance_type_id = $permission_first_session_id;
                            $remark = 'First Half Permission';
                            $summary->permission_count++;
                        } else {
                            // FHP quota is used, now check for Late
                            if ($late_settings && strtotime($in_time_final) <= strtotime($late_settings->entry_time_to)) {
                                $attendance_type_id = $late_id;
                                $remark = 'Permission quota exceeded, marked as Late';
                            } else {
                                 $attendance_type_id = $first_half_absent_id;
                                 $remark = 'Permission quota exceeded and arrived after Late window';
                            }
                        }
                    } elseif ($late_settings && strtotime($in_time_final) <= strtotime($late_settings->entry_time_to)) {
                         $attendance_type_id = $late_id;
                         $remark = 'Late arrival';
                    } else {
                        // Arrived after all grace periods for the morning
                        $attendance_type_id = $first_half_absent_id;
                        $remark = 'Arrived after morning session cut-off';
                    }
                }
                
                            // Evening Session Logic (Departure)
                            if ($out_time_final) {
                                if ($attendance_type_id != $absent_id && $attendance_type_id != $first_half_absent_id) { // Don't override a morning absence
                                    if (strtotime($out_time_final) < strtotime($morning_session_end_time)) {
                                        $attendance_type_id = $half_day_id;
                                        $remark = 'Left before morning session ended';
                                    } elseif (strtotime($out_time_final) < strtotime($evening_session_end_time)) { // Left before evening session ended
                                        $shp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_second_session_id);
                
                                        if ($shp_settings && strtotime($out_time_final) >= strtotime($shp_settings->entry_time_from) && strtotime($out_time_final) <= strtotime($shp_settings->entry_time_to)) {
                                            // If within SHP window and quota available
                                            if ($summary->permission_count < $max_permission_allowed) {
                                                $attendance_type_id = $permission_second_session_id;
                                                $remark = 'Second Half Permission';
                                                $summary->permission_count++;
                                            } else {
                                                // SHP quota exhausted
                                                $attendance_type_id = $second_half_absent_id;
                                                $remark = 'Second Half Permission quota exceeded';
                                            }
                                        } else {
                                            // Not within SHP window or no SHP settings
                                            $attendance_type_id = $second_half_absent_id;
                                            $remark = 'Left before evening session ended, outside SHP window';
                                        }
                                    }
                                }
                            } else {
                                 // No final out punch
                                 if($attendance_type_id != $absent_id && $attendance_type_id != $first_half_absent_id){
                                    $attendance_type_id = $second_half_absent_id;
                                    $remark = 'No closing punch found';
                                 }
                            }                $this->staffattendancemonthlysummary_model->update_summary($summary->id, (array)$summary);
            }

            // Prepare and save the final record
            $attendance_record_data = [
                'staff_id'                 => $staff_id,
                'date'                     => $date,
                'staff_attendance_type_id' => $attendance_type_id,
                'remark'                   => $remark,
                'in_time'                  => $in_time_final,
                'out_time'                 => $out_time_final,
                'total_hours_worked'       => $total_hours_worked,
                'updated_at'               => date('Y-m-d H:i:s'),
            ];
            
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
