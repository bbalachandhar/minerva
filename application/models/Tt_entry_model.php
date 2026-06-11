<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_entry_model extends MY_Model
{
    private function _select()
    {
        return 'tt_entries.*, subjects.name as subject_name, subjects.code as subject_code, subjects.type as subject_type, subjects.tt_color, subjects.tt_abbr,
                staff.name as staff_name, staff.surname as staff_surname,
                tt_rooms.name as room_name, tt_rooms.room_number,
                tt_batches.batch_name,
                tt_periods.name as period_name, tt_periods.start_time, tt_periods.end_time';
    }

    public function getGridEntries($session_id, $class_id, $section_id)
    {
        return $this->db->select($this->_select())
            ->from('tt_entries')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_entries.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_entries.staff_id', 'left')
            ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_entries.batch_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
            ->where('tt_entries.session_id', $session_id)
            ->where('tt_entries.class_id', $class_id)
            ->where('tt_entries.section_id', $section_id)
            ->get()->result();
    }

    public function getTeacherEntries($session_id, $staff_id)
    {
        return $this->db->select($this->_select() . ', classes.class as class_name, sections.section as section_name')
            ->from('tt_entries')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_entries.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_entries.staff_id', 'left')
            ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_entries.batch_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
            ->join('classes', 'classes.id = tt_entries.class_id', 'left')
            ->join('sections', 'sections.id = tt_entries.section_id', 'left')
            ->where('tt_entries.session_id', $session_id)
            ->where('tt_entries.staff_id', $staff_id)
            ->get()->result();
    }

    public function getById($id)
    {
        return $this->db->select($this->_select())
            ->from('tt_entries')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_entries.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_entries.staff_id', 'left')
            ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_entries.batch_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
            ->where('tt_entries.id', $id)
            ->get()->row();
    }

    public function getStaffSlotsForDay($session_id, $staff_id, $day)
    {
        return $this->db->select($this->_select() . ', classes.class as class_name, sections.section as section_name')
            ->from('tt_entries')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_entries.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_entries.staff_id', 'left')
            ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_entries.batch_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
            ->join('classes', 'classes.id = tt_entries.class_id', 'left')
            ->join('sections', 'sections.id = tt_entries.section_id', 'left')
            ->where('tt_entries.session_id', $session_id)
            ->where('tt_entries.staff_id', $staff_id)
            ->where('tt_entries.day', $day)
            ->order_by('tt_periods.sort_order','ASC')
            ->get()->result();
    }

    public function getAvailableTeachers($session_id, $day, $period_id, $exclude_staff_id)
    {
        // Teachers who are NOT already scheduled in this day+period
        $busy = $this->db->select('staff_id')
            ->where('session_id', $session_id)
            ->where('day', $day)
            ->where('period_id', $period_id)
            ->where('staff_id IS NOT NULL', null, false)
            ->get('tt_entries')->result_array();
        $busy_ids = array_column($busy, 'staff_id');
        $busy_ids[] = $exclude_staff_id;

        // Also exclude unavailable teachers
        $unavail = $this->db->select('staff_id')
            ->where('session_id', $session_id)
            ->where('day', $day)
            ->where('period_id', $period_id)
            ->get('tt_teacher_unavail')->result_array();
        $unavail_ids = array_column($unavail, 'staff_id');

        $exclude_ids = array_unique(array_merge($busy_ids, $unavail_ids));

        $this->db->select('staff.id, staff.name, staff.surname, staff.employee_id')
            ->from('staff')
            ->where('staff.role_id', 2)
            ->where('staff.is_active', 1);
        if (!empty($exclude_ids)) {
            $this->db->where_not_in('staff.id', $exclude_ids);
        }
        return $this->db->order_by('staff.name','ASC')->get()->result();
    }

    public function checkConflict($data, $existing_cell_id = 0)
    {
        // Teacher conflict: same teacher, same day, same period, different class
        if (!empty($data['staff_id'])) {
            $q = $this->db->select('tt_entries.id, classes.class, sections.section')
                ->from('tt_entries')
                ->join('classes', 'classes.id = tt_entries.class_id', 'left')
                ->join('sections', 'sections.id = tt_entries.section_id', 'left')
                ->where('tt_entries.session_id', $data['session_id'])
                ->where('tt_entries.staff_id', $data['staff_id'])
                ->where('tt_entries.day', $data['day'])
                ->where('tt_entries.period_id', $data['period_id'])
                ->where('tt_entries.is_free_period', 0);
            if ($existing_cell_id > 0) {
                $q->where('tt_entries.id !=', $existing_cell_id);
            }
            $conflict = $q->get()->row();
            if ($conflict) {
                return "Teacher conflict: already assigned to {$conflict->class} {$conflict->section} on this slot.";
            }
        }

        // Room conflict: same room, same day, same period
        if (!empty($data['room_id'])) {
            $q = $this->db->select('tt_entries.id, classes.class, sections.section')
                ->from('tt_entries')
                ->join('classes', 'classes.id = tt_entries.class_id', 'left')
                ->join('sections', 'sections.id = tt_entries.section_id', 'left')
                ->where('tt_entries.session_id', $data['session_id'])
                ->where('tt_entries.room_id', $data['room_id'])
                ->where('tt_entries.day', $data['day'])
                ->where('tt_entries.period_id', $data['period_id']);
            if ($existing_cell_id > 0) {
                $q->where('tt_entries.id !=', $existing_cell_id);
            }
            $conflict = $q->get()->row();
            if ($conflict) {
                return "Room conflict: room already booked for {$conflict->class} {$conflict->section} on this slot.";
            }
        }

        return false;
    }

    public function saveCell($data, $existing_id = 0)
    {
        $this->db->trans_start();
        if ($existing_id > 0) {
            $this->db->where('id', $existing_id)->update('tt_entries', $data);
        } else {
            $this->db->insert('tt_entries', $data);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function deleteCell($id)
    {
        $this->db->where('id', $id)->where('is_locked', 0)->delete('tt_entries');
    }

    public function setLock($id, $locked)
    {
        $this->db->where('id', $id)->update('tt_entries', ['is_locked' => (int)$locked]);
    }

    public function deleteByScopeExceptLocked($session_id, $class_scope)
    {
        $this->db->where('session_id', $session_id)->where('is_locked', 0);
        if (!empty($class_scope)) {
            $this->db->group_start();
            foreach ($class_scope as $cs) {
                $this->db->or_group_start()
                    ->where('class_id', (int)$cs['class_id'])
                    ->where('section_id', (int)$cs['section_id'])
                    ->group_end();
            }
            $this->db->group_end();
        }
        $this->db->delete('tt_entries');
    }

    public function insertBatch($entries)
    {
        if (empty($entries)) return true;
        $this->db->trans_start();
        $this->db->insert_batch('tt_entries', $entries);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function getMasterReport($session_id, $class_ids = null)
    {
        $this->db->select($this->_select() . ', classes.class as class_name, sections.section as section_name')
            ->from('tt_entries')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_entries.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_entries.staff_id', 'left')
            ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_entries.batch_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
            ->join('classes', 'classes.id = tt_entries.class_id', 'left')
            ->join('sections', 'sections.id = tt_entries.section_id', 'left')
            ->where('tt_entries.session_id', $session_id);
        if (!empty($class_ids)) {
            $this->db->where_in('tt_entries.class_id', $class_ids);
        }
        $this->db->order_by('classes.class','ASC')->order_by('sections.section','ASC')
            ->order_by('tt_periods.sort_order','ASC');
        return $this->db->get()->result();
    }

    public function getRoomUtilization($session_id)
    {
        return $this->db->select('tt_rooms.name as room_name, tt_rooms.room_number, tt_rooms.capacity, tt_rooms.room_type, tt_entries.day, tt_entries.period_id, tt_periods.start_time, tt_periods.end_time, classes.class, sections.section, subjects.name as subject_name')
            ->from('tt_entries')
            ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id')
            ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_entries.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('classes', 'classes.id = tt_entries.class_id', 'left')
            ->join('sections', 'sections.id = tt_entries.section_id', 'left')
            ->where('tt_entries.session_id', $session_id)
            ->where('tt_entries.room_id IS NOT NULL', null, false)
            ->order_by('tt_rooms.name','ASC')
            ->order_by('tt_entries.day','ASC')
            ->order_by('tt_periods.sort_order','ASC')
            ->get()->result();
    }

    public function getTeacherWorkload($session_id)
    {
        return $this->db->select('staff.id as staff_id, staff.name, staff.surname, staff.employee_id, COUNT(tt_entries.id) as total_periods, SUM(CASE WHEN tt_entries.is_free_period=1 THEN 0 ELSE 1 END) as teaching_periods')
            ->from('tt_entries')
            ->join('staff', 'staff.id = tt_entries.staff_id')
            ->where('tt_entries.session_id', $session_id)
            ->where('tt_entries.staff_id IS NOT NULL', null, false)
            ->group_by('staff.id')
            ->order_by('staff.name','ASC')
            ->get()->result();
    }
}
