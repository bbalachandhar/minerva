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
