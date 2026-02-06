<?php
class StaffAttendanceSchedule_model extends CI_Model {
    public function getByStaffId($staff_id) {
        // Fetch schedule based on staff role
        $this->db->select('staff_attendence_schedules.*, staff_roles.role_id');
        $this->db->from('staff');
        $this->db->join('staff_roles', 'staff_roles.staff_id = staff.id', 'left');
        $this->db->join('staff_attendence_schedules', 'staff_attendence_schedules.role_id = staff_roles.role_id');
        $this->db->where('staff.id', $staff_id);
        $this->db->where('staff_attendence_schedules.is_active', 1);
        $this->db->order_by('staff_attendence_schedules.staff_attendence_type_id', 'ASC');
        $query = $this->db->get();
        
        $schedules = $query->result_array();
        if (empty($schedules)) {
            $fallback = $this->db->select('staff_attendence_schedules.*')
                ->from('staff')
                ->join('staff_roles', 'staff_roles.staff_id = staff.id', 'left')
                ->join('staff_attendence_schedules', 'staff_attendence_schedules.role_id = staff_roles.role_id')
                ->where('staff.id', $staff_id)
                ->order_by('staff_attendence_schedules.entry_time_from', 'ASC')
                ->limit(1)
                ->get()
                ->row_array();

            if (!empty($fallback)) {
                return $fallback;
            }

            $morningSchedule = $this->db->select('staff_attendence_schedules.*')
                ->from('staff_attendence_schedules')
                ->where('staff_attendence_type_id', 1)
                ->order_by('entry_time_from', 'ASC')
                ->limit(1)
                ->get()
                ->row_array();

            if (!empty($morningSchedule)) {
                return $morningSchedule;
            }

            // Return default schedule if none found
            return [
                'entry_time_from' => '09:00:00',
                'entry_time_to' => '09:30:00',
                'total_institute_hour' => '08:00:00'
            ];
        }
        
        // Return first schedule (entry schedule)
        return $schedules[0];
    }
}
