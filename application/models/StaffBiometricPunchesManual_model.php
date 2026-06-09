<?php
class StaffBiometricPunchesManual_model extends CI_Model {
    public function getSpecialAttendanceCounts($staffIds, $month, $year) {
        if (empty($staffIds)) {
            return [];
        }

        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return [];
        }

        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $monthNum);
        $endDate = sprintf('%04d-%02d-%02d 23:59:59', $year, $monthNum, cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year));

        $this->db->select('staff_id, COUNT(DISTINCT DATE(punch_time)) AS present_days');
        $this->db->from('staff_biometric_punches_manual');
        $this->db->where_in('staff_id', $staffIds);
        $this->db->where('source', 'special_attendance');
        $this->db->where('punch_type', 'in');
        $this->db->where('punch_time >=', $startDate);
        $this->db->where('punch_time <=', $endDate);
        $this->db->group_by('staff_id');
        $rows = $this->db->get()->result_array();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['staff_id']] = (int)$row['present_days'];
        }

        return $counts;
    }

    public function replacePunches($staff_id, $month, $year, $punches, $admin_user_id, $reason) {
        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return;
        }

        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $monthNum);
        $endDate = sprintf('%04d-%02d-%02d 23:59:59', $year, $monthNum, cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year));

        $this->db->where('staff_id', $staff_id);
        $this->db->where('punch_time >=', $startDate);
        $this->db->where('punch_time <=', $endDate);
        $this->db->where('source', 'special_attendance');
        $this->db->delete('staff_biometric_punches_manual');

        $this->insertPunches($staff_id, $punches, $admin_user_id, $reason);
    }

    public function getSpecialAttendancePresentEquivalent($staffIds, $month, $year) {
        if (empty($staffIds)) {
            return [];
        }

        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return [];
        }

        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $monthNum);
        $endDate = sprintf('%04d-%02d-%02d 23:59:59', $year, $monthNum, cal_days_in_month(CAL_GREGORIAN, $monthNum, (int)$year));

        $rows = $this->db
            ->select('staff_id, DATE(punch_time) as punch_date, punch_type, TIME(punch_time) as punch_only_time')
            ->from('staff_biometric_punches_manual')
            ->where_in('staff_id', $staffIds)
            ->where('source', 'special_attendance')
            ->where('punch_time >=', $startDate)
            ->where('punch_time <=', $endDate)
            ->order_by('staff_id', 'ASC')
            ->order_by('punch_date', 'ASC')
            ->order_by('punch_time', 'ASC')
            ->get()
            ->result_array();

        $daywise = [];
        foreach ($rows as $row) {
            $staffId = (int)$row['staff_id'];
            $date = $row['punch_date'];
            if (!isset($daywise[$staffId])) {
                $daywise[$staffId] = [];
            }
            if (!isset($daywise[$staffId][$date])) {
                $daywise[$staffId][$date] = ['in' => null, 'out' => null];
            }

            if ($row['punch_type'] === 'in') {
                if ($daywise[$staffId][$date]['in'] === null || $row['punch_only_time'] < $daywise[$staffId][$date]['in']) {
                    $daywise[$staffId][$date]['in'] = $row['punch_only_time'];
                }
            } elseif ($row['punch_type'] === 'out') {
                if ($daywise[$staffId][$date]['out'] === null || $row['punch_only_time'] > $daywise[$staffId][$date]['out']) {
                    $daywise[$staffId][$date]['out'] = $row['punch_only_time'];
                }
            }
        }

        $presentEquivalent = [];
        foreach ($daywise as $staffId => $dates) {
            $sum = 0.0;
            foreach ($dates as $date => $times) {
                if (empty($times['in']) || empty($times['out'])) {
                    continue;
                }
                $inTs = strtotime($date . ' ' . $times['in']);
                $outTs = strtotime($date . ' ' . $times['out']);
                if ($inTs === false || $outTs === false || $outTs <= $inTs) {
                    continue;
                }
                $hours = ($outTs - $inTs) / 3600;
                $sum += ($hours < 6.0) ? 0.5 : 1.0;
            }
            $presentEquivalent[$staffId] = $sum;
        }

        return $presentEquivalent;
    }

    public function insertPunches($staff_id, $punches, $admin_user_id, $reason) {
        foreach ($punches as $punch) {
            // Insert in-punch
            $this->db->insert('staff_biometric_punches_manual', [
                'staff_id' => $staff_id,
                'punch_time' => $punch['date'] . ' ' . $punch['in'],
                'punch_type' => 'in',
                'source' => 'special_attendance',
                'admin_user_id' => $admin_user_id,
                'reason' => $reason
            ]);
            // Insert out-punch
            $this->db->insert('staff_biometric_punches_manual', [
                'staff_id' => $staff_id,
                'punch_time' => $punch['date'] . ' ' . $punch['out'],
                'punch_type' => 'out',
                'source' => 'special_attendance',
                'admin_user_id' => $admin_user_id,
                'reason' => $reason
            ]);
        }
    }

    public function saveSpecialAttendanceInput($staff_id, $month, $year, $lop_days, $reason = null, $admin_user_id = null) {
        if (!$this->db->table_exists('special_attendance_inputs')) {
            return false;
        }

        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return false;
        }

        $lop = is_numeric($lop_days) ? (float)$lop_days : 0;
        if ($lop < 0) {
            $lop = 0;
        }

        $this->db->where('staff_id', (int)$staff_id);
        $this->db->where('month', (int)$monthNum);
        $this->db->where('year', (int)$year);
        $existing = $this->db->get('special_attendance_inputs')->row_array();

        $payload = [
            'staff_id' => (int)$staff_id,
            'month' => (int)$monthNum,
            'year' => (int)$year,
            'lop_days' => $lop,
            'reason' => (string)$reason,
            'admin_user_id' => !empty($admin_user_id) ? (int)$admin_user_id : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($existing['id'])) {
            $this->db->where('id', (int)$existing['id']);
            return (bool)$this->db->update('special_attendance_inputs', $payload);
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        return (bool)$this->db->insert('special_attendance_inputs', $payload);
    }

    public function getSpecialAttendanceInputMap($staffIds, $month, $year) {
        if (empty($staffIds)) {
            return [];
        }

        if (!$this->db->table_exists('special_attendance_inputs')) {
            return [];
        }

        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return [];
        }

        $rows = $this->db
            ->select('staff_id, lop_days')
            ->from('special_attendance_inputs')
            ->where_in('staff_id', $staffIds)
            ->where('month', (int)$monthNum)
            ->where('year', (int)$year)
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['staff_id']] = isset($row['lop_days']) ? (float)$row['lop_days'] : 0.0;
        }

        return $result;
    }

    public function getSpecialAttendanceInputDetailsMap($staffIds, $month, $year) {
        if (empty($staffIds)) {
            return [];
        }

        if (!$this->db->table_exists('special_attendance_inputs')) {
            return [];
        }

        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return [];
        }

        $rows = $this->db
            ->select('staff_id, lop_days, reason, admin_user_id, created_at, updated_at')
            ->from('special_attendance_inputs')
            ->where_in('staff_id', $staffIds)
            ->where('month', (int)$monthNum)
            ->where('year', (int)$year)
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            $staffId = (int)($row['staff_id'] ?? 0);
            if ($staffId <= 0) {
                continue;
            }
            $result[$staffId] = [
                'lop_days' => isset($row['lop_days']) ? (float)$row['lop_days'] : 0.0,
                'reason' => (string)($row['reason'] ?? ''),
                'admin_user_id' => !empty($row['admin_user_id']) ? (int)$row['admin_user_id'] : null,
                'created_at' => (string)($row['created_at'] ?? ''),
                'updated_at' => (string)($row['updated_at'] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * Returns the latest punch day-of-month per staff for special_attendance in a given month.
     * Used to restore the "Till Day" column in the UI without storing it in the DB.
     */
    public function getSpecialAttendanceTillDayMap($staffIds, $month, $year) {
        if (empty($staffIds) || !$this->db->table_exists('staff_biometric_punches_manual')) {
            return [];
        }
        $monthNum = $this->getMonthNumber($month, $year);
        if ($monthNum === null) {
            return [];
        }
        $rows = $this->db
            ->select('staff_id, MAX(DATE(punch_time)) AS max_date')
            ->from('staff_biometric_punches_manual')
            ->where_in('staff_id', $staffIds)
            ->where('source', 'special_attendance')
            ->where('MONTH(punch_time)', (int)$monthNum)
            ->where('YEAR(punch_time)', (int)$year)
            ->group_by('staff_id')
            ->get()
            ->result_array();

        $result = [];
        foreach ($rows as $row) {
            if (!empty($row['max_date'])) {
                $result[(int)$row['staff_id']] = (int)date('j', strtotime($row['max_date']));
            }
        }
        return $result;
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
}
