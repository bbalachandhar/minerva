<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subjecttimetable_model extends CI_Model
{
    public $current_session;
    private $_use_tt_entries = null;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function hasTtEntries()
    {
        if ($this->_use_tt_entries === null) {
            $count = $this->db->where('session_id', $this->current_session)
                ->from('tt_entries')
                ->count_all_results();
            $this->_use_tt_entries = ($count > 0);
        }
        return $this->_use_tt_entries;
    }

    public function getSubjectByClassandSectionDay($class_id, $section_id, $day)
    {
        if ($this->hasTtEntries()) {
            return $this->_getTtSubjectByClassandSectionDay($class_id, $section_id, $day);
        }
        $sql = "SELECT `subject_group_subjects`.`subject_id`,subjects.name as `subject_name`,subjects.code,subjects.type,staff.name,staff.surname,staff.employee_id,`subject_timetable`.*,subject_group_class_sections.id as `subject_group_class_sections_id` FROM `subject_timetable` JOIN `subject_group_subjects` ON `subject_timetable`.`subject_group_subject_id` = `subject_group_subjects`.`id`inner JOIN subjects on subject_group_subjects.subject_id = subjects.id INNER JOIN staff on staff.id=subject_timetable.staff_id inner JOIN class_sections on class_sections.class_id=subject_timetable.class_id and class_sections.section_id=subject_timetable.section_id INNER JOIN subject_group_class_sections on subject_group_class_sections.class_section_id=class_sections.id and subject_group_class_sections.session_id=subject_timetable.session_id WHERE `subject_timetable`.`class_id` = " . $class_id . " AND `subject_timetable`.`section_id` = " . $section_id . " AND `subject_timetable`.`day` = " . $this->db->escape($day) . " AND `subject_timetable`.`session_id` = " . $this->current_session . " AND `staff`.`is_active`=1 ORDER by subject_timetable.start_time asc";

        $query = $this->db->query($sql);
        return $query->result();
    }

    private function _getTtSubjectByClassandSectionDay($class_id, $section_id, $day)
    {
        $sql = "SELECT subject_group_subjects.subject_id, subjects.name AS subject_name, subjects.code, subjects.type,
                staff.name, staff.surname, staff.employee_id,
                tt_entries.id, tt_entries.class_id, tt_entries.section_id, tt_entries.staff_id,
                tt_entries.subject_group_subject_id, tt_entries.day, tt_entries.session_id,
                tt_periods.start_time AS time_from, tt_periods.end_time AS time_to,
                tt_rooms.room_number AS room_no,
                'tt_entries' AS timetable_source
            FROM tt_entries
            INNER JOIN subject_group_subjects ON subject_group_subjects.id = tt_entries.subject_group_subject_id
            INNER JOIN subjects ON subjects.id = subject_group_subjects.subject_id
            LEFT JOIN staff ON staff.id = tt_entries.staff_id
            LEFT JOIN tt_periods ON tt_periods.id = tt_entries.period_id
            LEFT JOIN tt_rooms ON tt_rooms.id = tt_entries.room_id
            WHERE tt_entries.class_id = " . $this->db->escape($class_id) . "
            AND tt_entries.section_id = " . $this->db->escape($section_id) . "
            AND tt_entries.day = " . $this->db->escape($day) . "
            AND tt_entries.session_id = " . $this->db->escape($this->current_session) . "
            AND tt_entries.is_free_period = 0
            AND (staff.is_active = 1 OR tt_entries.staff_id IS NULL)
            ORDER BY tt_periods.sort_order ASC";
        return $this->db->query($sql)->result();
    }

    public function getSubjects($class_id, $section_id)
    {
        if ($this->hasTtEntries()) {
            return $this->_getTtSubjects($class_id, $section_id);
        }
        $sql = "SELECT subject_timetable.*,staff.name as `staff_name`,staff.surname as `staff_surname`,staff.contact_no,staff.email,subject_group_subjects.subject_id,subjects.name as `subject_name`,subjects.code,subjects.type FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id=subject_timetable.subject_group_subject_id INNER join staff on  subject_timetable.staff_id=staff.id inner join subjects on subjects.id=subject_group_subjects.subject_id WHERE class_id=" . $this->db->escape($class_id) . " and section_id=" . $this->db->escape($section_id) . " and subject_timetable.session_id=" . $this->current_session . " GROUP by subjects.id DESC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    private function _getTtSubjects($class_id, $section_id)
    {
        $sql = "SELECT tt_entries.id, tt_entries.class_id, tt_entries.section_id, tt_entries.staff_id,
                tt_entries.subject_group_subject_id, tt_entries.day, tt_entries.session_id,
                tt_periods.start_time AS time_from, tt_periods.end_time AS time_to,
                tt_rooms.room_number AS room_no,
                staff.name AS staff_name, staff.surname AS staff_surname, staff.contact_no, staff.email,
                subject_group_subjects.subject_id, subjects.name AS subject_name, subjects.code, subjects.type
            FROM tt_entries
            INNER JOIN subject_group_subjects ON subject_group_subjects.id = tt_entries.subject_group_subject_id
            INNER JOIN subjects ON subjects.id = subject_group_subjects.subject_id
            LEFT JOIN staff ON staff.id = tt_entries.staff_id
            LEFT JOIN tt_periods ON tt_periods.id = tt_entries.period_id
            LEFT JOIN tt_rooms ON tt_rooms.id = tt_entries.room_id
            WHERE tt_entries.class_id = " . $this->db->escape($class_id) . "
            AND tt_entries.section_id = " . $this->db->escape($section_id) . "
            AND tt_entries.session_id = " . $this->db->escape($this->current_session) . "
            AND tt_entries.is_free_period = 0
            GROUP BY subjects.id DESC";
        return $this->db->query($sql)->result();
    }

    public function getSubjectTimetable($class_id, $section_id, $subject_id)
    {
        if ($this->hasTtEntries()) {
            return $this->_getTtSubjectTimetable($class_id, $section_id, $subject_id);
        }
        $sql = "SELECT subject_timetable.*,staff.name as `staff_name`,staff.surname as `staff_surname`,staff.contact_no,staff.email,subject_group_subjects.subject_id,subjects.name as `subject_name`,subjects.code,subjects.type FROM `subject_timetable` INNER JOIN subject_group_subjects on subject_group_subjects.id=subject_timetable.subject_group_subject_id INNER join staff on  subject_timetable.staff_id=staff.id inner join subjects on subjects.id=subject_group_subjects.subject_id WHERE class_id=" . $this->db->escape($class_id) . " and section_id=" . $this->db->escape($section_id) . " and subject_timetable.session_id=" . $this->current_session . " and subjects.id=" . $subject_id;
        $query = $this->db->query($sql);
        return $query->result();
    }

    private function _getTtSubjectTimetable($class_id, $section_id, $subject_id)
    {
        $sql = "SELECT tt_entries.id, tt_entries.class_id, tt_entries.section_id, tt_entries.staff_id,
                tt_entries.subject_group_subject_id, tt_entries.day, tt_entries.session_id,
                tt_periods.start_time AS time_from, tt_periods.end_time AS time_to,
                tt_rooms.room_number AS room_no,
                staff.name AS staff_name, staff.surname AS staff_surname, staff.contact_no, staff.email,
                subject_group_subjects.subject_id, subjects.name AS subject_name, subjects.code, subjects.type
            FROM tt_entries
            INNER JOIN subject_group_subjects ON subject_group_subjects.id = tt_entries.subject_group_subject_id
            INNER JOIN subjects ON subjects.id = subject_group_subjects.subject_id
            LEFT JOIN staff ON staff.id = tt_entries.staff_id
            LEFT JOIN tt_periods ON tt_periods.id = tt_entries.period_id
            LEFT JOIN tt_rooms ON tt_rooms.id = tt_entries.room_id
            WHERE tt_entries.class_id = " . $this->db->escape($class_id) . "
            AND tt_entries.section_id = " . $this->db->escape($section_id) . "
            AND tt_entries.session_id = " . $this->db->escape($this->current_session) . "
            AND subjects.id = " . $this->db->escape($subject_id) . "
            AND tt_entries.is_free_period = 0";
        return $this->db->query($sql)->result();
    }

    public function getTeachers($class_id, $section_id)
    {
        if ($this->hasTtEntries()) {
            return $this->_getTtTeachers($class_id, $section_id);
        }

		$sql = "SELECT DISTINCT time.*, staff.name as staff_name, staff.surname as staff_surname,staff.contact_no, staff.email, staff.employee_id,subject_group_subjects.subject_id,subjects.name as subject_name, subjects.code, subjects.type FROM ( SELECT subject_timetable.id, subject_timetable.staff_id, subject_timetable.day, subject_timetable.subject_group_id,  subject_timetable.subject_group_subject_id, subject_timetable.time_from, subject_timetable.time_to, subject_timetable.room_no,  IFNULL(class_teacher.id,'0') as class_teacher_id  FROM subject_timetable LEFT JOIN class_teacher  ON class_teacher.staff_id = subject_timetable.staff_id  AND class_teacher.class_id = subject_timetable.class_id AND class_teacher.section_id = subject_timetable.section_id  AND class_teacher.session_id = ".$this->current_session." WHERE subject_timetable.class_id = ".$this->db->escape($class_id)."  AND subject_timetable.section_id = ".$this->db->escape($section_id)." AND subject_timetable.session_id = ".$this->current_session."  UNION SELECT class_teacher.id, class_teacher.staff_id, NULL, NULL, NULL, NULL, NULL, NULL, class_teacher.id  FROM class_teacher  WHERE class_teacher.class_id = ".$this->db->escape($class_id)."  AND class_teacher.section_id = ".$this->db->escape($section_id)."  AND class_teacher.session_id = ".$this->current_session.") as time INNER JOIN staff ON staff.id = time.staff_id LEFT JOIN subject_group_subjects ON subject_group_subjects.id = time.subject_group_subject_id LEFT JOIN subjects ON subjects.id = subject_group_subjects.subject_id WHERE staff.is_active = 1 ";
        $query = $this->db->query($sql);
        return $query->result();
    }

    private function _getTtTeachers($class_id, $section_id)
    {
        $sql = "SELECT DISTINCT time.*, staff.name AS staff_name, staff.surname AS staff_surname,
                staff.contact_no, staff.email, staff.employee_id,
                subject_group_subjects.subject_id, subjects.name AS subject_name, subjects.code, subjects.type
            FROM (
                SELECT tt_entries.id, tt_entries.staff_id, tt_entries.day, NULL AS subject_group_id,
                    tt_entries.subject_group_subject_id,
                    tt_periods.start_time AS time_from, tt_periods.end_time AS time_to,
                    tt_rooms.room_number AS room_no,
                    IFNULL(class_teacher.id, '0') AS class_teacher_id
                FROM tt_entries
                LEFT JOIN tt_periods ON tt_periods.id = tt_entries.period_id
                LEFT JOIN tt_rooms ON tt_rooms.id = tt_entries.room_id
                LEFT JOIN class_teacher
                    ON class_teacher.staff_id = tt_entries.staff_id
                    AND class_teacher.class_id = tt_entries.class_id
                    AND class_teacher.section_id = tt_entries.section_id
                    AND class_teacher.session_id = " . $this->db->escape($this->current_session) . "
                WHERE tt_entries.class_id = " . $this->db->escape($class_id) . "
                AND tt_entries.section_id = " . $this->db->escape($section_id) . "
                AND tt_entries.session_id = " . $this->db->escape($this->current_session) . "
                AND tt_entries.is_free_period = 0
                UNION
                SELECT class_teacher.id, class_teacher.staff_id, NULL, NULL, NULL, NULL, NULL, NULL, class_teacher.id
                FROM class_teacher
                WHERE class_teacher.class_id = " . $this->db->escape($class_id) . "
                AND class_teacher.section_id = " . $this->db->escape($section_id) . "
                AND class_teacher.session_id = " . $this->db->escape($this->current_session) . "
            ) AS time
            INNER JOIN staff ON staff.id = time.staff_id
            LEFT JOIN subject_group_subjects ON subject_group_subjects.id = time.subject_group_subject_id
            LEFT JOIN subjects ON subjects.id = subject_group_subjects.subject_id
            WHERE staff.is_active = 1";
        return $this->db->query($sql)->result();
    }

    public function getTeacherSubject($class_id, $section_id, $staff_id)
    {
        if ($this->hasTtEntries()) {
            return $this->_getTtTeacherSubject($class_id, $section_id, $staff_id);
        }
        $sql = "SELECT subject_timetable.*,staff.name as `staff_name`,staff.surname as `staff_surname`,staff.contact_no,staff.email,subject_group_subjects.subject_id,subjects.name as `subject_name`,subjects.code,subjects.type,IFNULL(class_teacher.id,'0') as `class_teacher_id` FROM `subject_timetable`  INNER JOIN subject_group_subjects on subject_group_subjects.id=subject_timetable.subject_group_subject_id INNER join staff on  subject_timetable.staff_id=staff.id  and staff.id=" . $this->db->escape($staff_id) . " inner join subjects on subjects.id=subject_group_subjects.subject_id LEFT JOIN class_teacher on class_teacher.class_id=" . $this->db->escape($class_id) . " and class_teacher.staff_id=staff.id and class_teacher.section_id= " . $this->db->escape($section_id) . " WHERE subject_timetable.class_id=" . $this->db->escape($class_id) . " and subject_timetable.section_id=" . $this->db->escape($section_id) . " and subject_timetable.session_id=" . $this->current_session . " ORDER BY class_teacher.id desc";

        $query = $this->db->query($sql);
        return $query->result();
    }

    private function _getTtTeacherSubject($class_id, $section_id, $staff_id)
    {
        $sql = "SELECT tt_entries.id, tt_entries.class_id, tt_entries.section_id, tt_entries.staff_id,
                tt_entries.subject_group_subject_id, tt_entries.day, tt_entries.session_id,
                tt_periods.start_time AS time_from, tt_periods.end_time AS time_to,
                tt_rooms.room_number AS room_no,
                staff.name AS staff_name, staff.surname AS staff_surname, staff.contact_no, staff.email,
                subject_group_subjects.subject_id, subjects.name AS subject_name, subjects.code, subjects.type,
                IFNULL(class_teacher.id, '0') AS class_teacher_id
            FROM tt_entries
            INNER JOIN subject_group_subjects ON subject_group_subjects.id = tt_entries.subject_group_subject_id
            INNER JOIN subjects ON subjects.id = subject_group_subjects.subject_id
            INNER JOIN staff ON staff.id = tt_entries.staff_id AND staff.id = " . $this->db->escape($staff_id) . "
            LEFT JOIN tt_periods ON tt_periods.id = tt_entries.period_id
            LEFT JOIN tt_rooms ON tt_rooms.id = tt_entries.room_id
            LEFT JOIN class_teacher ON class_teacher.class_id = " . $this->db->escape($class_id) . "
                AND class_teacher.staff_id = staff.id
                AND class_teacher.section_id = " . $this->db->escape($section_id) . "
            WHERE tt_entries.class_id = " . $this->db->escape($class_id) . "
            AND tt_entries.section_id = " . $this->db->escape($section_id) . "
            AND tt_entries.session_id = " . $this->db->escape($this->current_session) . "
            AND tt_entries.is_free_period = 0
            ORDER BY class_teacher.id DESC";
        return $this->db->query($sql)->result();
    }

    public function getByStaffandDay($staff_id, $day_value)
    {
        if ($this->hasTtEntries()) {
            return $this->_getTtByStaffandDay($staff_id, $day_value);
        }
        $sql   = "SELECT `classes`.`class`,`sections`.`section`,`subject_group_subjects`.`subject_id`,`sub`.`name` as `subject_name`,`sub`.`code` as `subject_code`,`subject_timetable`.* FROM `subject_timetable` INNER JOIN `classes` on classes.id = `subject_timetable`.`class_id` INNER JOIN sections on `sections`.`id`=`subject_timetable`.`section_id` INNER JOIN `subject_group_subjects` on `subject_group_subjects`.`id`=`subject_timetable`.`subject_group_subject_id` INNER JOIN `subjects` as `sub` on `sub`.`id`=`subject_group_subjects`.`subject_id`  WHERE subject_timetable.staff_id=" . $this->db->escape($staff_id) . " and subject_timetable.session_id =" . $this->current_session . " and subject_timetable.day=" . $this->db->escape($day_value) . "order by subject_timetable.start_time";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }

    private function _getTtByStaffandDay($staff_id, $day_value)
    {
        $sql = "SELECT classes.class, sections.section,
                subject_group_subjects.subject_id, sub.name AS subject_name, sub.code AS subject_code,
                tt_entries.id, tt_entries.class_id, tt_entries.section_id, tt_entries.staff_id,
                tt_entries.subject_group_subject_id, tt_entries.day, tt_entries.session_id, tt_entries.period_id,
                tt_periods.start_time AS time_from, tt_periods.end_time AS time_to,
                tt_rooms.room_number AS room_no,
                'tt_entries' AS timetable_source
            FROM tt_entries
            INNER JOIN classes ON classes.id = tt_entries.class_id
            INNER JOIN sections ON sections.id = tt_entries.section_id
            INNER JOIN subject_group_subjects ON subject_group_subjects.id = tt_entries.subject_group_subject_id
            INNER JOIN subjects AS sub ON sub.id = subject_group_subjects.subject_id
            LEFT JOIN tt_periods ON tt_periods.id = tt_entries.period_id
            LEFT JOIN tt_rooms ON tt_rooms.id = tt_entries.room_id
            WHERE tt_entries.staff_id = " . $this->db->escape($staff_id) . "
            AND tt_entries.session_id = " . $this->db->escape($this->current_session) . "
            AND tt_entries.day = " . $this->db->escape($day_value) . "
            AND tt_entries.is_free_period = 0
            ORDER BY tt_periods.sort_order ASC";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }

    public function getStaffTimetable($staff_id, $start_date, $end_date)
    {
        $full_timetable = array();
        $current_date   = strtotime($start_date);
        $end_date_ts    = strtotime($end_date);

        while ($current_date <= $end_date_ts) {
            $day_name       = date('l', $current_date);
            $formatted_date = date('Y-m-d', $current_date);
            $daily_timetable = $this->getByStaffandDay($staff_id, $day_name);

            if ($daily_timetable) {
                $full_timetable[$formatted_date] = $daily_timetable;
            } else {
                $full_timetable[$formatted_date] = array();
            }

            $current_date = strtotime('+1 day', $current_date);
        }

        return $full_timetable;
    }

    public function staffHasPeriodOnDay($staff_id, $class_id, $section_id, $day_name)
    {
        if ($this->hasTtEntries()) {
            return $this->db->select('id')->from('tt_entries')
                ->where('staff_id', (int) $staff_id)
                ->where('class_id', (int) $class_id)
                ->where('section_id', (int) $section_id)
                ->where('day', $day_name)
                ->where('session_id', $this->current_session)
                ->where('is_free_period', 0)
                ->limit(1)->get()->row();
        }
        return $this->db->select('id')->from('subject_timetable')
            ->where('staff_id', (int) $staff_id)
            ->where('class_id', (int) $class_id)
            ->where('section_id', (int) $section_id)
            ->where('day', $day_name)
            ->where('session_id', $this->current_session)
            ->limit(1)->get()->row();
    }

    public function getTimetableEntryById($id)
    {
        if ($this->hasTtEntries()) {
            $row = $this->db->select('tt_entries.id, tt_entries.class_id, tt_entries.section_id, tt_entries.session_id, tt_entries.staff_id, tt_entries.subject_group_subject_id, tt_periods.start_time AS time_from, tt_periods.end_time AS time_to, tt_rooms.room_number AS room_no, tt_entries.day')
                ->from('tt_entries')
                ->join('tt_periods', 'tt_periods.id = tt_entries.period_id', 'left')
                ->join('tt_rooms', 'tt_rooms.id = tt_entries.room_id', 'left')
                ->where('tt_entries.id', $id)
                ->get()->row_array();
            if (!empty($row)) {
                $row['timetable_source'] = 'tt_entries';
                return $row;
            }
        }
        $row = $this->db->select('id, class_id, section_id, session_id, staff_id, subject_group_subject_id, time_from, time_to, room_no, day')
            ->from('subject_timetable')
            ->where('id', $id)
            ->get()->row_array();
        if (!empty($row)) {
            $row['timetable_source'] = 'subject_timetable';
        }
        return $row;
    }

    public function user_rating($student_id, $staff_id)
    {
        $this->db->select('staff_rating.rate,staff_rating.comment')->from('staff_rating')->join("users", "users.id = staff_rating.user_id", "inner")->join("staff", "staff_rating.staff_id = staff.id", "inner");
        $this->db->where('staff.is_active', 1);
        $this->db->where('staff_rating.staff_id', $staff_id);
        $this->db->where('staff_rating.user_id', $student_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function add_rating($data)
    {
        $this->db->where('user_id', $data['user_id']);
        $this->db->where('staff_id', $data['staff_id']);
        $q = $this->db->get('staff_rating');

        if ($q->num_rows() > 0) {
            return false;
        } else {
            $this->db->insert("staff_rating", $data);
            return true;
        }
    }

}
