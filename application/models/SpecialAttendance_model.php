<?php
class SpecialAttendance_model extends CI_Model {
    public function generatePunches($staff_id, $month, $year, $days, $schedule) {
        $this->load->model('setting_model');
        $settings = $this->setting_model->getSetting();

        $working_days = $this->getWorkingDays($month, $year, $settings);
        
        // Randomly select days from working days (not just first N days)
        shuffle($working_days);
        $selected_days = array_slice($working_days, 0, min($days, count($working_days)));
        sort($selected_days); // Sort to maintain chronological order
        
        $punches = [];
        foreach ($selected_days as $date) {
            // First IN: Randomize within entry time window
            $entryFrom = !empty($schedule['entry_time_from']) ? $schedule['entry_time_from'] : '09:00:00';
            $entryTo = !empty($schedule['entry_time_to']) ? $schedule['entry_time_to'] : '09:30:00';
            $in_time = $this->randomizeTime($entryFrom, $entryTo);
            
            // Last OUT: 8 to 9 hours after IN
            $out_time = $this->calculateOutTime($in_time, 8, 9);
            
            $punches[] = [
                'date' => $date,
                'in' => $in_time,
                'out' => $out_time
            ];
        }
        return $punches;
    }

    public function getWorkingDaysCount($month, $year) {
        $this->load->model('setting_model');
        $settings = $this->setting_model->getSetting();
        $working_days = $this->getWorkingDays($month, $year, $settings);
        return count($working_days);
    }

    public function generatePunchesFromLop($staff_id, $month, $year, $lop_days, $schedule) {
        $this->load->model('setting_model');
        $settings = $this->setting_model->getSetting();
        $attendanceWindows = $this->getStaffAttendanceWindows($staff_id, $schedule, $settings);

        $working_days = $this->getWorkingDays($month, $year, $settings);
        $total_working_days = count($working_days);

        $lop = is_numeric($lop_days) ? (float)$lop_days : 0;
        if ($lop < 0) {
            $lop = 0;
        }
        if ($lop > $total_working_days) {
            $lop = (float)$total_working_days;
        }

        $present_days_target = $total_working_days - $lop;
        if ($present_days_target <= 0) {
            return [];
        }

        $full_days = (int)floor($present_days_target);
        $has_half_day = (($present_days_target - $full_days) >= 0.5);

        shuffle($working_days);
        $required_days = $full_days + ($has_half_day ? 1 : 0);
        $selected_days = array_slice($working_days, 0, min($required_days, count($working_days)));
        sort($selected_days);

        $punches = [];
        foreach ($selected_days as $index => $date) {
            $is_half_day = $has_half_day && ($index === (count($selected_days) - 1));

            $entryFrom = $attendanceWindows['entry_from'];
            $entryTo = $attendanceWindows['entry_to'];
            if ($is_half_day && !empty($attendanceWindows['half_day_entry_from']) && !empty($attendanceWindows['half_day_entry_to'])) {
                $entryFrom = $attendanceWindows['half_day_entry_from'];
                $entryTo = $attendanceWindows['half_day_entry_to'];
            }
            $in_time = $this->randomizeTime($entryFrom, $entryTo);

            if ($is_half_day) {
                $out_time = $this->calculateOutTime($in_time, 4, 5);
            } else {
                $out_time = $this->calculateFullDayOutTime($in_time, $attendanceWindows['full_day_out_cutoff']);
            }

            $punches[] = [
                'date' => $date,
                'in' => $in_time,
                'out' => $out_time
            ];
        }

        return $punches;
    }

    private function getStaffAttendanceWindows($staff_id, $schedule, $settings) {
        $entryFrom = !empty($schedule['entry_time_from']) ? $schedule['entry_time_from'] : '09:00:00';
        $entryTo = !empty($schedule['entry_time_to']) ? $schedule['entry_time_to'] : '09:30:00';
        $halfDayEntryFrom = null;
        $halfDayEntryTo = null;

        $fullDayOutCutoff = !empty($settings->evening_session_end_time) ? $settings->evening_session_end_time : null;

        if (!empty($staff_id)) {
            $this->load->model('staff_model');
            $this->load->model('staffAttendaceSetting_model');

            $staff = $this->staff_model->get((int)$staff_id);
            $role_id = isset($staff['role_id']) ? (int)$staff['role_id'] : 0;

            if ($role_id > 0) {
                $present_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, 1);
                $first_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, 2);
                $fhp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, 5);
                $half_day_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, 4);
                $permission_second_session_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, 7);

                if (!empty($present_settings->entry_time_from) && !empty($present_settings->entry_time_to)) {
                    $entryFrom = $present_settings->entry_time_from;
                    $entryTo = $present_settings->entry_time_to;
                } elseif (!empty($first_half_late_settings->entry_time_from) && !empty($first_half_late_settings->entry_time_to)) {
                    $entryFrom = $first_half_late_settings->entry_time_from;
                    $entryTo = $first_half_late_settings->entry_time_to;
                } elseif (!empty($fhp_settings->entry_time_from) && !empty($fhp_settings->entry_time_to)) {
                    $entryFrom = $fhp_settings->entry_time_from;
                    $entryTo = $fhp_settings->entry_time_to;
                }

                if (empty($fullDayOutCutoff) && !empty($permission_second_session_settings->entry_time_to)) {
                    $fullDayOutCutoff = $permission_second_session_settings->entry_time_to;
                }

                if (!empty($half_day_settings->entry_time_from) && !empty($half_day_settings->entry_time_to)) {
                    $halfDayEntryFrom = $half_day_settings->entry_time_from;
                    $halfDayEntryTo = $half_day_settings->entry_time_to;
                }
            }
        }

        return [
            'entry_from' => $entryFrom,
            'entry_to' => $entryTo,
            'full_day_out_cutoff' => $fullDayOutCutoff,
            'half_day_entry_from' => $halfDayEntryFrom,
            'half_day_entry_to' => $halfDayEntryTo
        ];
    }

    private function calculateFullDayOutTime($in_time, $fullDayOutCutoff = null) {
        $defaultOut = $this->calculateOutTime($in_time, 8, 9);
        if (empty($fullDayOutCutoff)) {
            return $defaultOut;
        }

        $inTs = strtotime($in_time);
        $defaultTs = strtotime($defaultOut);
        $cutoffTs = strtotime($fullDayOutCutoff);

        if ($inTs === false || $defaultTs === false || $cutoffTs === false) {
            return $defaultOut;
        }

        if ($defaultTs >= $cutoffTs) {
            return date('H:i:s', $defaultTs);
        }

        $minTs = max($inTs + (8 * 3600), $cutoffTs);
        $maxTs = max($inTs + (9 * 3600), $minTs);

        if ($maxTs <= $minTs) {
            return date('H:i:s', $minTs);
        }

        return date('H:i:s', rand($minTs, $maxTs));
    }

    private function getWorkingDays($month, $year, $settings) {
        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return [];
        }

        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayHoliday = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;

        $holidayData = $this->getHolidayDates($monthNum, $year);
        $holidayDates = $holidayData['holiday_dates'] ?? [];
        $compensationDates = $holidayData['compensation_dates'] ?? [];

        if ($isSecondSaturdayHoliday) {
            $secondSaturday = $this->getSecondSaturdayDate($monthNum, $year);
            if ($secondSaturday && !in_array($secondSaturday, $compensationDates, true) && !in_array($secondSaturday, $holidayDates, true)) {
                $holidayDates[] = $secondSaturday;
            }
        }

        $days = [];
        $num_days = cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year);
        for ($d = 1; $d <= $num_days; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $d);
            $dow = (int)date('w', strtotime($date));

            if (in_array($date, $compensationDates, true)) {
                $days[] = $date;
                continue;
            }

            if (in_array($dow, $weekendDays, true)) {
                continue;
            }
            if (in_array($date, $holidayDates, true)) {
                continue;
            }
            $days[] = $date;
        }
        return $days;
    }

    private function getMonthNumber($month, $year) {
        if (is_numeric($month)) {
            $monthNum = (int)$month;
            return ($monthNum >= 1 && $monthNum <= 12) ? $monthNum : null;
        }
        $date = DateTime::createFromFormat('F Y', $month . ' ' . $year);
        if ($date === false) {
            return null;
        }
        return (int)$date->format('n');
    }

    private function getHolidayDates($monthNum, $year) {
        $holidayDates = [];
        $compensationDates = [];

        $monthStart = sprintf('%04d-%02d-01', $year, $monthNum);
        $monthEnd = sprintf('%04d-%02d-%02d', $year, $monthNum, cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year));

        $this->db->select('annual_calendar.from_date, annual_calendar.to_date, holiday_type.type');
        $this->db->from('annual_calendar');
        $this->db->join('holiday_type', 'holiday_type.id = annual_calendar.holiday_type', 'left');
        $this->db->where('is_active', 1);
        $this->db->where("(from_date <= '{$monthEnd}' AND to_date >= '{$monthStart}')");
        $holidays = $this->db->get()->result_array();

        foreach ($holidays as $holiday) {
            $typeLabel = strtolower(trim($holiday['type'] ?? ''));
            $from = new DateTime(date('Y-m-d', strtotime($holiday['from_date'])));
            $to = new DateTime(date('Y-m-d', strtotime($holiday['to_date'])));
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($from, $interval, $to->modify('+1 day'));

            foreach ($period as $date) {
                if ((int)$date->format('n') == $monthNum && (int)$date->format('Y') == (int)$year) {
                    if ($typeLabel === 'compensation') {
                        $compensationDates[] = $date->format('Y-m-d');
                    } else {
                        $holidayDates[] = $date->format('Y-m-d');
                    }
                }
            }
        }

        $compensationDates = array_values(array_unique($compensationDates));
        $holidayDates = array_values(array_diff(array_unique($holidayDates), $compensationDates));

        return [
            'holiday_dates' => $holidayDates,
            'compensation_dates' => $compensationDates,
        ];
    }

    private function getSecondSaturdayDate($monthNum, $year) {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year);
        $saturdayCount = 0;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $i);
            if ((int)date('w', strtotime($date)) === 6) {
                $saturdayCount++;
                if ($saturdayCount === 2) {
                    return $date;
                }
            }
        }
        return null;
    }
    private function randomizeTime($start, $end) {
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);
        $rand_ts = rand($start_ts, $end_ts);
        return date('H:i:s', $rand_ts);
    }
    
    private function calculateOutTime($in_time, $minHours, $maxHours) {
        $in_timestamp = strtotime($in_time);
        $min_out_timestamp = $in_timestamp + ((int)$minHours * 3600);
        $max_out_timestamp = $in_timestamp + ((int)$maxHours * 3600);

        $out_timestamp = rand($min_out_timestamp, $max_out_timestamp);

        return date('H:i:s', $out_timestamp);
    }
}
