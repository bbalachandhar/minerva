<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('setting_model'); // Load the setting model
    }

    private function time_in_range($time, $from, $to) {
        if (empty($time)) {
            return false;
        }

        $from_clean = is_string($from) ? trim($from) : $from;
        $to_clean = is_string($to) ? trim($to) : $to;
        if ($from_clean === '00:00:00' && $to_clean === '00:00:00') {
            return false;
        }

        $time_ts = strtotime($time);
        $from_ts = !empty($from) ? strtotime($from) : null;
        $to_ts = !empty($to) ? strtotime($to) : null;

        if ($from_ts && $to_ts) {
            return $time_ts >= $from_ts && $time_ts <= $to_ts;
        }

        if ($from_ts && !$to_ts) {
            return $time_ts >= $from_ts;
        }

        if (!$from_ts && $to_ts) {
            return $time_ts <= $to_ts;
        }

        return false;
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
    public function process_daily_biometric_attendance($date, $now_ts = null) {
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

        $present_id = 1;
        $first_half_late_id = 2;
        $absent_id = 3;
        $half_day_id = 4;
        $permission_first_session_id = 5;
        $second_half_late_id = 6;
        $permission_second_session_id = 7;
        $first_half_absent_id = 8;
        $second_half_absent_id = 9;

        $morning_session_end_time = $setting->morning_session_end_time;
        $evening_session_end_time = $setting->evening_session_end_time;
        $now_ts = $now_ts ?: time();
        $final_cutoff_ts = null;
        if (!empty($evening_session_end_time)) {
            $final_cutoff_ts = strtotime($date . ' ' . $evening_session_end_time);
        } elseif (!empty($morning_session_end_time)) {
            $final_cutoff_ts = strtotime($date . ' ' . $morning_session_end_time);
        }
        $is_final = $final_cutoff_ts ? ($now_ts >= $final_cutoff_ts) : true;
        $now_ts = time();
        $final_cutoff_ts = null;
        if (!empty($evening_session_end_time)) {
            $final_cutoff_ts = strtotime($date . ' ' . $evening_session_end_time);
        } elseif (!empty($morning_session_end_time)) {
            $final_cutoff_ts = strtotime($date . ' ' . $morning_session_end_time);
        }
        $is_final = $final_cutoff_ts ? ($now_ts >= $final_cutoff_ts) : true;

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
            $first_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $first_half_late_id);
            $fhp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_first_session_id);
            $half_day_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $half_day_id);
            $second_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $second_half_late_id);
            $shp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_second_session_id);

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

                // Morning Session Logic (Arrival)
                $first_half_status = $first_half_absent_id;
                $second_half_status = $second_half_absent_id;

                $arrival_second_half_status = null;
                $pending_out_punch = false;
                $pending_out_punch = false;

                if ($half_day_settings && $this->time_in_range($in_time_final, $half_day_settings->entry_time_from, $half_day_settings->entry_time_to)) {
                    $first_half_status = $first_half_absent_id;
                    $arrival_second_half_status = $present_id;
                    $remark = 'Second half attendance';
                } elseif ($second_half_late_settings && $this->time_in_range($in_time_final, $second_half_late_settings->entry_time_from, $second_half_late_settings->entry_time_to)) {
                    $first_half_status = $first_half_absent_id;
                    $arrival_second_half_status = $second_half_late_id;
                } else {
                    if ($present_settings && $this->time_in_range($in_time_final, $present_settings->entry_time_from, $present_settings->entry_time_to)) {
                        $first_half_status = $present_id;
                    } elseif ($first_half_late_settings && $this->time_in_range($in_time_final, $first_half_late_settings->entry_time_from, $first_half_late_settings->entry_time_to)) {
                        $first_half_status = $first_half_late_id;
                    } elseif ($fhp_settings && $this->time_in_range($in_time_final, $fhp_settings->entry_time_from, $fhp_settings->entry_time_to)) {
                        $first_half_status = $permission_first_session_id;
                    } else {
                        $first_half_status = $first_half_absent_id;
                    }

                    if ($second_half_late_settings && !empty($second_half_late_settings->entry_time_to)) {
                        if (strtotime($in_time_final) > strtotime($second_half_late_settings->entry_time_to)) {
                            $arrival_second_half_status = $second_half_absent_id;
                        }
                    }
                }

                // Evening Session Logic (Departure)
                if ($arrival_second_half_status !== null) {
                    $second_half_status = $arrival_second_half_status;
                } elseif ($out_time_final) {
                    if ($shp_settings && $this->time_in_range($out_time_final, $shp_settings->entry_time_from, $shp_settings->entry_time_to)) {
                        $second_half_status = $permission_second_session_id;
                    } else {
                        $second_half_cutoff = !empty($morning_session_end_time) ? $morning_session_end_time : null;

                        if ($second_half_cutoff && strtotime($out_time_final) < strtotime($second_half_cutoff)) {
                            $second_half_status = $second_half_absent_id;
                        } else {
                            $present_cutoff = null;
                            if (!empty($evening_session_end_time)) {
                                $present_cutoff = $evening_session_end_time;
                            } elseif ($shp_settings && !empty($shp_settings->entry_time_to)) {
                                $present_cutoff = $shp_settings->entry_time_to;
                            }

                            if ($present_cutoff && strtotime($out_time_final) >= strtotime($present_cutoff)) {
                                $second_half_status = $present_id;
                            } else {
                                $second_half_status = $second_half_absent_id;
                            }
                        }
                    }
                } else {
                    if (!$is_final) {
                        $second_half_status = $present_id;
                        $pending_out_punch = true;
                        $remark = 'Pending out punch';
                    } else {
                        $first_half_status = $first_half_absent_id;
                        $second_half_status = $second_half_absent_id;
                        $remark = 'No closing punch found';
                    }
                }

                $first_half_present = in_array($first_half_status, [$present_id, $first_half_late_id, $permission_first_session_id], true);
                $second_half_present = in_array($second_half_status, [$present_id, $second_half_late_id, $permission_second_session_id], true);

                if ($first_half_present && $second_half_present) {
                    $attendance_type_id = $present_id;
                } elseif ($first_half_present || $second_half_present) {
                    $attendance_type_id = $half_day_id;
                } else {
                    $attendance_type_id = $absent_id;
                }

                $session_attendance_data = json_encode([
                    'morning_session' => $first_half_status,
                    'afternoon_session' => $second_half_status,
                    'pending_out_punch' => (!empty($pending_out_punch) && !$is_final) ? true : false,
                    'is_final' => $is_final
                ]);
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
                'session_attendance_data'  => isset($session_attendance_data) ? $session_attendance_data : null,
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

    public function process_daily_manual_attendance_for_staff($date, $staffIds, $source = 'special_attendance') {
        if (empty($staffIds)) {
            return false;
        }

        $this->load->model('staff_model');
        $this->load->model('staffattendancemodel');
        $this->load->model('staffAttendaceSetting_model');
        $this->load->model('staffattendancemonthlysummary_model');

        $present_id = 1;
        $first_half_late_id = 2;
        $absent_id = 3;
        $half_day_id = 4;
        $permission_first_session_id = 5;
        $second_half_late_id = 6;
        $permission_second_session_id = 7;
        $first_half_absent_id = 8;
        $second_half_absent_id = 9;

        $setting = $this->setting_model->getSetting();
        $morning_session_end_time = $setting->morning_session_end_time;
        $evening_session_end_time = $setting->evening_session_end_time;

        $staffIds = array_values(array_unique(array_map('intval', $staffIds)));
        $staff_punches_by_day = [];
        foreach ($staffIds as $staff_id) {
            $staff_punches_by_day[$staff_id] = [];
        }

        $raw_punches = $this->db->select('staff_id, punch_time')
            ->where_in('staff_id', $staffIds)
            ->where('DATE(punch_time)', $date)
            ->where('source', $source)
            ->order_by('staff_id, punch_time', 'ASC')
            ->get('staff_biometric_punches_manual')
            ->result_array();

        foreach ($raw_punches as $punch) {
            if (isset($staff_punches_by_day[$punch['staff_id']])) {
                $staff_punches_by_day[$punch['staff_id']][] = strtotime($punch['punch_time']);
            }
        }

        foreach ($staff_punches_by_day as $staff_id => $timestamps) {
            $staff_detail = $this->staff_model->get($staff_id);
            $role_id = isset($staff_detail['role_id']) ? $staff_detail['role_id'] : null;

            if (!$role_id) {
                continue;
            }

            $present_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $present_id);
            $first_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $first_half_late_id);
            $fhp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_first_session_id);
            $half_day_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $half_day_id);
            $second_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $second_half_late_id);
            $shp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_second_session_id);

            if (empty($timestamps)) {
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

                $attendance_type_id = $present_id;
                $remark = '';

                $first_half_status = $first_half_absent_id;
                $second_half_status = $second_half_absent_id;

                $arrival_second_half_status = null;

                if ($half_day_settings && $this->time_in_range($in_time_final, $half_day_settings->entry_time_from, $half_day_settings->entry_time_to)) {
                    $first_half_status = $first_half_absent_id;
                    $arrival_second_half_status = $present_id;
                    $remark = 'Second half attendance';
                } elseif ($second_half_late_settings && $this->time_in_range($in_time_final, $second_half_late_settings->entry_time_from, $second_half_late_settings->entry_time_to)) {
                    $first_half_status = $first_half_absent_id;
                    $arrival_second_half_status = $second_half_late_id;
                } else {
                    if ($present_settings && $this->time_in_range($in_time_final, $present_settings->entry_time_from, $present_settings->entry_time_to)) {
                        $first_half_status = $present_id;
                    } elseif ($first_half_late_settings && $this->time_in_range($in_time_final, $first_half_late_settings->entry_time_from, $first_half_late_settings->entry_time_to)) {
                        $first_half_status = $first_half_late_id;
                    } elseif ($fhp_settings && $this->time_in_range($in_time_final, $fhp_settings->entry_time_from, $fhp_settings->entry_time_to)) {
                        $first_half_status = $permission_first_session_id;
                    } else {
                        $first_half_status = $first_half_absent_id;
                    }

                    if ($second_half_late_settings && !empty($second_half_late_settings->entry_time_to)) {
                        if (strtotime($in_time_final) > strtotime($second_half_late_settings->entry_time_to)) {
                            $arrival_second_half_status = $second_half_absent_id;
                        }
                    }
                }

                if ($arrival_second_half_status !== null) {
                    $second_half_status = $arrival_second_half_status;
                } elseif ($out_time_final) {
                    if ($shp_settings && $this->time_in_range($out_time_final, $shp_settings->entry_time_from, $shp_settings->entry_time_to)) {
                        $second_half_status = $permission_second_session_id;
                    } else {
                        $second_half_cutoff = !empty($morning_session_end_time) ? $morning_session_end_time : null;

                        if ($second_half_cutoff && strtotime($out_time_final) < strtotime($second_half_cutoff)) {
                            $second_half_status = $second_half_absent_id;
                        } else {
                            $present_cutoff = null;
                            if (!empty($evening_session_end_time)) {
                                $present_cutoff = $evening_session_end_time;
                            } elseif ($shp_settings && !empty($shp_settings->entry_time_to)) {
                                $present_cutoff = $shp_settings->entry_time_to;
                            }

                            if ($present_cutoff && strtotime($out_time_final) >= strtotime($present_cutoff)) {
                                $second_half_status = $present_id;
                            } else {
                                $second_half_status = $second_half_absent_id;
                            }
                        }
                    }
                } else {
                    if (!$is_final) {
                        $second_half_status = $present_id;
                        $pending_out_punch = true;
                        $remark = 'Pending out punch';
                    } else {
                        $first_half_status = $first_half_absent_id;
                        $second_half_status = $second_half_absent_id;
                        $remark = 'No closing punch found';
                    }
                }

                $first_half_present = in_array($first_half_status, [$present_id, $first_half_late_id, $permission_first_session_id], true);
                $second_half_present = in_array($second_half_status, [$present_id, $second_half_late_id, $permission_second_session_id], true);

                if ($first_half_present && $second_half_present) {
                    $attendance_type_id = $present_id;
                } elseif ($first_half_present || $second_half_present) {
                    $attendance_type_id = $half_day_id;
                } else {
                    $attendance_type_id = $absent_id;
                }

                $session_attendance_data = json_encode([
                    'morning_session' => $first_half_status,
                    'afternoon_session' => $second_half_status,
                    'pending_out_punch' => (!empty($pending_out_punch) && !$is_final) ? true : false,
                    'is_final' => $is_final
                ]);
            }

            $attendance_record_data = [
                'staff_id'                 => $staff_id,
                'date'                     => $date,
                'staff_attendance_type_id' => $attendance_type_id,
                'remark'                   => $remark,
                'in_time'                  => $in_time_final,
                'out_time'                 => $out_time_final,
                'total_hours_worked'       => $total_hours_worked,
                'session_attendance_data'  => isset($session_attendance_data) ? $session_attendance_data : null,
                'biometric_attendence'     => 1,
                'qrcode_attendance'        => 0,
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
     * Evaluate attendance for a single staff based on provided in/out times (non-persistent - used for AJAX evaluation).
     * Returns array with attendance_type_id, in_time, out_time, total_hours_worked, session (morning/afternoon ids), remark, pending_out_punch
     */
    public function evaluate_attendance_for_times($staff_id, $date, $in_time = null, $out_time = null, $is_final = false) {
        $this->load->model('staff_model');
        $this->load->model('staffAttendaceSetting_model');

        $present_id = 1;
        $first_half_late_id = 2;
        $absent_id = 3;
        $half_day_id = 4;
        $permission_first_session_id = 5;
        $second_half_late_id = 6;
        $permission_second_session_id = 7;
        $first_half_absent_id = 8;
        $second_half_absent_id = 9;

        $setting = $this->setting_model->getSetting();
        $morning_session_end_time = $setting->morning_session_end_time;
        $evening_session_end_time = $setting->evening_session_end_time;

        $staff_detail = $this->staff_model->get($staff_id);
        if (!$staff_detail) {
            return null;
        }
        $role_id = isset($staff_detail['role_id']) ? $staff_detail['role_id'] : null;
        if (!$role_id) {
            return null;
        }

        // Normalize times
        $in_time_final = null;
        $out_time_final = null;
        $timestamps = [];
        if (!empty($in_time)) {
            $in_time_final = date('H:i:s', strtotime($in_time));
            $timestamps[] = strtotime($date . ' ' . $in_time_final);
        }
        if (!empty($out_time)) {
            $out_time_final = date('H:i:s', strtotime($out_time));
            $timestamps[] = strtotime($date . ' ' . $out_time_final);
        }

        $total_hours_worked = 0;
        if (empty($timestamps)) {
            $attendance_type_id = $absent_id;
            $remark = 'No punch found';
            $session_data = [
                'morning_session' => $first_half_absent_id,
                'afternoon_session' => $second_half_absent_id,
                'pending_out_punch' => false,
                'is_final' => $is_final,
            ];
        } else {
            sort($timestamps);
            if (empty($in_time_final) && isset($timestamps[0])) {
                $in_time_final = date('H:i:s', $timestamps[0]);
            }
            if (empty($out_time_final) && isset($timestamps[1])) {
                $out_time_final = date('H:i:s', end($timestamps));
            }

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

            $present_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $present_id);
            $first_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $first_half_late_id);
            $fhp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_first_session_id);
            $half_day_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $half_day_id);
            $second_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $second_half_late_id);
            $shp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $permission_second_session_id);

            // Morning session
            $first_half_status = $first_half_absent_id;
            $second_half_status = $second_half_absent_id;
            $arrival_second_half_status = null;
            $pending_out_punch = false;

            if ($half_day_settings && $this->time_in_range($in_time_final, $half_day_settings->entry_time_from, $half_day_settings->entry_time_to)) {
                $first_half_status = $first_half_absent_id;
                $arrival_second_half_status = $present_id;
                $remark = 'Second half attendance';
            } elseif ($second_half_late_settings && $this->time_in_range($in_time_final, $second_half_late_settings->entry_time_from, $second_half_late_settings->entry_time_to)) {
                $first_half_status = $first_half_absent_id;
                $arrival_second_half_status = $second_half_late_id;
            } else {
                if ($present_settings && $this->time_in_range($in_time_final, $present_settings->entry_time_from, $present_settings->entry_time_to)) {
                    $first_half_status = $present_id;
                } elseif ($first_half_late_settings && $this->time_in_range($in_time_final, $first_half_late_settings->entry_time_from, $first_half_late_settings->entry_time_to)) {
                    $first_half_status = $first_half_late_id;
                } elseif ($fhp_settings && $this->time_in_range($in_time_final, $fhp_settings->entry_time_from, $fhp_settings->entry_time_to)) {
                    $first_half_status = $permission_first_session_id;
                } else {
                    $first_half_status = $first_half_absent_id;
                }

                if ($second_half_late_settings && !empty($second_half_late_settings->entry_time_to)) {
                    if (strtotime($in_time_final) > strtotime($second_half_late_settings->entry_time_to)) {
                        $arrival_second_half_status = $second_half_absent_id;
                    }
                }
            }

            // Evening session
            if ($arrival_second_half_status !== null) {
                $second_half_status = $arrival_second_half_status;
            } elseif ($out_time_final) {
                if ($shp_settings && $this->time_in_range($out_time_final, $shp_settings->entry_time_from, $shp_settings->entry_time_to)) {
                    $second_half_status = $permission_second_session_id;
                } else {
                    $second_half_cutoff = !empty($morning_session_end_time) ? $morning_session_end_time : null;

                    if ($second_half_cutoff && strtotime($out_time_final) < strtotime($second_half_cutoff)) {
                        $second_half_status = $second_half_absent_id;
                    } else {
                        $present_cutoff = null;
                        if (!empty($evening_session_end_time)) {
                            $present_cutoff = $evening_session_end_time;
                        } elseif ($shp_settings && !empty($shp_settings->entry_time_to)) {
                            $present_cutoff = $shp_settings->entry_time_to;
                        }

                        if ($present_cutoff && strtotime($out_time_final) >= strtotime($present_cutoff)) {
                            $second_half_status = $present_id;
                        } else {
                            $second_half_status = $second_half_absent_id;
                        }
                    }
                }
            } else {
                if (!$is_final) {
                    $second_half_status = $present_id;
                    $pending_out_punch = true;
                    $remark = 'Pending out punch';
                } else {
                    $first_half_status = $first_half_absent_id;
                    $second_half_status = $second_half_absent_id;
                    $remark = 'No closing punch found';
                }
            }

            $first_half_present = in_array($first_half_status, [$present_id, $first_half_late_id, $permission_first_session_id], true);
            $second_half_present = in_array($second_half_status, [$present_id, $second_half_late_id, $permission_second_session_id], true);

            if ($first_half_present && $second_half_present) {
                $attendance_type_id = $present_id;
            } elseif ($first_half_present || $second_half_present) {
                $attendance_type_id = $half_day_id;
            } else {
                $attendance_type_id = $absent_id;
            }

            $session_data = [
                'morning_session' => $first_half_status,
                'afternoon_session' => $second_half_status,
                'pending_out_punch' => (!empty($pending_out_punch) && !$is_final) ? true : false,
                'is_final' => $is_final,
            ];
        }

        return [
            'attendance_type_id' => $attendance_type_id,
            'in_time' => $in_time_final,
            'out_time' => $out_time_final,
            'total_hours_worked' => $total_hours_worked,
            'session' => $session_data,
            'remark' => isset($remark) ? $remark : '',
            'pending_out_punch' => isset($pending_out_punch) ? (bool)$pending_out_punch : false,
        ];
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
