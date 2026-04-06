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
        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null || empty($working_days)) {
            return [];
        }

        $weekend_days = $this->getWeekendDays($monthNum, (int)$year, $settings);
        $target_lop = is_numeric($lop_days) ? (float)$lop_days : 0.0;
        if ($target_lop < 0) {
            $target_lop = 0.0;
        }

        $status_by_date = $this->planStatusesForTargetLop($working_days, $weekend_days, $target_lop);
        if (empty($status_by_date)) {
            return [];
        }

        $punches = [];
        foreach ($working_days as $date) {
            $status = $status_by_date[$date] ?? 'A';
            if ($status === 'A') {
                continue;
            }

            $is_half_day = ($status === 'H');

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

    private function planStatusesForTargetLop(array $working_days, array $weekend_days, $target_lop)
    {
        $n = count($working_days);
        if ($n === 0) {
            return [];
        }

        $target_units = (int) round(max(0, (float)$target_lop) * 2);
        $working_lookup = array_fill_keys($working_days, true);
        $weekend_lookup = array_fill_keys($weekend_days, true);

        $weekend_gap_units = array_fill(0, max(0, $n - 1), 0);
        for ($i = 1; $i < $n; $i++) {
            $count = $this->countWeekendDaysBetween($working_days[$i - 1], $working_days[$i], $weekend_lookup, $working_lookup);
            $weekend_gap_units[$i - 1] = max(0, (int)$count) * 2;
        }

        $max_units = 2 * $n;
        foreach ($weekend_gap_units as $gap_units) {
            $max_units += (int)$gap_units;
        }
        if ($target_units > $max_units) {
            $target_units = $max_units;
        }

        $status_defs = [
            'P' => ['units' => 0, 'absent' => 0],
            'H' => ['units' => 1, 'absent' => 0],
            'A' => ['units' => 2, 'absent' => 1],
        ];

        $dp = [];
        $prev = [];

        foreach ($status_defs as $status => $def) {
            $u = $def['units'];
            $a = $def['absent'];
            if (!isset($dp[0])) {
                $dp[0] = [];
            }
            if (!isset($dp[0][$u])) {
                $dp[0][$u] = [];
            }
            $dp[0][$u][$a] = true;
            $prev[0][$u][$a] = ['prev_units' => null, 'prev_absent' => null, 'status' => $status];
        }

        for ($i = 1; $i < $n; $i++) {
            foreach ($dp[$i - 1] ?? [] as $used_units => $absent_states) {
                foreach ($absent_states as $prev_absent => $_reachable) {
                    foreach ($status_defs as $status => $def) {
                        $pair_units = ((int)$prev_absent === 1 && (int)$def['absent'] === 1) ? (int)$weekend_gap_units[$i - 1] : 0;
                        $new_units = (int)$used_units + (int)$def['units'] + $pair_units;
                        if ($new_units > $max_units) {
                            continue;
                        }

                        if (!isset($dp[$i])) {
                            $dp[$i] = [];
                        }
                        if (!isset($dp[$i][$new_units])) {
                            $dp[$i][$new_units] = [];
                        }

                        $new_absent = (int)$def['absent'];
                        if (!isset($dp[$i][$new_units][$new_absent])) {
                            $dp[$i][$new_units][$new_absent] = true;
                            $prev[$i][$new_units][$new_absent] = [
                                'prev_units' => (int)$used_units,
                                'prev_absent' => (int)$prev_absent,
                                'status' => $status,
                            ];
                        }
                    }
                }
            }
        }

        $best_units = null;
        $best_absent = null;
        $best_diff = null;

        foreach ($dp[$n - 1] ?? [] as $units => $absent_states) {
            foreach ($absent_states as $absent => $_reachable) {
                $diff = abs((int)$units - $target_units);
                if ($best_diff === null || $diff < $best_diff || ($diff === $best_diff && (int)$units > (int)$best_units)) {
                    $best_diff = $diff;
                    $best_units = (int)$units;
                    $best_absent = (int)$absent;
                }
            }
        }

        if ($best_units === null) {
            return [];
        }

        $statuses = array_fill(0, $n, 'A');
        $cur_units = $best_units;
        $cur_absent = $best_absent;
        for ($i = $n - 1; $i >= 0; $i--) {
            $node = $prev[$i][$cur_units][$cur_absent] ?? null;
            if (!$node) {
                break;
            }
            $statuses[$i] = (string)$node['status'];
            $cur_units = $node['prev_units'];
            $cur_absent = $node['prev_absent'];
            if ($cur_units === null || $cur_absent === null) {
                break;
            }
        }

        $result = [];
        foreach ($working_days as $idx => $date) {
            $result[$date] = $statuses[$idx] ?? 'A';
        }
        return $result;
    }

    private function countWeekendDaysBetween($start_date, $end_date, array $weekend_lookup, array $working_lookup)
    {
        // Count ALL non-working days between two working days (weekends + holidays),
        // not just Sundays, because holidays are also sandwichable.
        $count = 0;
        $cursor = date('Y-m-d', strtotime($start_date . ' +1 day'));
        while ($cursor < $end_date) {
            if (!isset($working_lookup[$cursor])) {
                $count++;
            }
            $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
        }
        return $count;
    }

    private function selectSandwichAwareWorkingDays($working_days, $monthNum, $year, $settings, $required_days)
    {
        if ($required_days <= 0 || empty($working_days)) {
            return [];
        }

        $required_days = min((int)$required_days, count($working_days));
        $weekend_days = $this->getWeekendDays($monthNum, $year, $settings);
        $weekend_lookup = array_fill_keys($weekend_days, true);

        $non_bridge_days = [];
        $bridge_days = [];

        foreach ($working_days as $date) {
            $prev_date = date('Y-m-d', strtotime($date . ' -1 day'));
            $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
            $is_bridge_day = isset($weekend_lookup[$prev_date]) || isset($weekend_lookup[$next_date]);

            if ($is_bridge_day) {
                $bridge_days[] = $date;
            } else {
                $non_bridge_days[] = $date;
            }
        }

        // Keep bridge days present first to avoid accidental sandwich LOP expansion.
        $selected = [];
        foreach ($bridge_days as $date) {
            if (count($selected) >= $required_days) {
                break;
            }
            $selected[] = $date;
        }

        if (count($selected) < $required_days) {
            foreach ($non_bridge_days as $date) {
                if (count($selected) >= $required_days) {
                    break;
                }
                $selected[] = $date;
            }
        }

        return $selected;
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

                $has_any_mapping = !empty($present_settings)
                    || !empty($first_half_late_settings)
                    || !empty($fhp_settings)
                    || !empty($half_day_settings)
                    || !empty($permission_second_session_settings);

                if (!$has_any_mapping) {
                    $admin_role_row = $this->db->query("SELECT id FROM roles WHERE LOWER(name)='admin' ORDER BY id ASC LIMIT 1")->row_array();
                    $admin_role_id = isset($admin_role_row['id']) ? (int)$admin_role_row['id'] : 0;

                    if ($admin_role_id > 0 && $admin_role_id !== (int)$role_id) {
                        $present_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id, 1);
                        $first_half_late_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id, 2);
                        $fhp_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id, 5);
                        $half_day_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id, 4);
                        $permission_second_session_settings = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($admin_role_id, 7);
                    }
                }

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

    private function getWeekendDays($monthNum, $year, $settings)
    {
        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayHoliday = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;

        $holidayData = $this->getHolidayDates($monthNum, $year);
        $compensationDates = $holidayData['compensation_dates'] ?? [];

        $weekend_dates = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $monthNum, $d);
            $dow = (int)date('w', strtotime($date));
            if (in_array($dow, $weekendDays, true)) {
                $weekend_dates[] = $date;
            }
        }

        if ($isSecondSaturdayHoliday) {
            $secondSaturday = $this->getSecondSaturdayDate($monthNum, $year);
            if (!empty($secondSaturday) && !in_array($secondSaturday, $weekend_dates, true)) {
                $weekend_dates[] = $secondSaturday;
            }
        }

        if (!empty($compensationDates)) {
            $weekend_dates = array_values(array_diff($weekend_dates, $compensationDates));
        }

        sort($weekend_dates);
        return array_values(array_unique($weekend_dates));
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
