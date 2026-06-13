<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_substitution_model extends MY_Model
{
    public function save($data)
    {
        $this->db->trans_start();
        if (!empty($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id)->update('tt_substitutions', $data);
        } else {
            $this->db->insert('tt_substitutions', $data);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function cancel($id)
    {
        $this->db->where('id', $id)->update('tt_substitutions', ['status' => 'cancelled']);
    }

    public function getByDateStaff($session_id, $absent_staff_id, $date)
    {
        return $this->db->where('session_id', $session_id)
            ->where('absent_staff_id', $absent_staff_id)
            ->where('date', $date)
            ->where('status !=', 'cancelled')
            ->get('tt_substitutions')->result();
    }

    public function getRecent($session_id, $days = 30)
    {
        $from = date('Y-m-d', strtotime("-{$days} days"));
        return $this->db->select('tt_substitutions.*, a.name as absent_name, a.surname as absent_surname, s.name as sub_name, s.surname as sub_surname, subjects.name as subject_name, classes.class, sections.section, tt_periods.name as period_name, tt_periods.start_time')
            ->from('tt_substitutions')
            ->join('staff as a', 'a.id = tt_substitutions.absent_staff_id', 'left')
            ->join('staff as s', 's.id = tt_substitutions.substitute_staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_substitutions.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('classes', 'classes.id = tt_substitutions.class_id', 'left')
            ->join('sections', 'sections.id = tt_substitutions.section_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_substitutions.period_id', 'left')
            ->where('tt_substitutions.session_id', $session_id)
            ->where('tt_substitutions.date >=', $from)
            ->order_by('tt_substitutions.date','DESC')
            ->order_by('tt_periods.sort_order','ASC')
            ->get()->result();
    }

    public function getForClassWeek($session_id, $class_id, $section_id, array $dates)
    {
        if (empty($dates)) return [];
        return $this->db->select('tt_substitutions.*, a.name as absent_name, a.surname as absent_surname, s.name as sub_name, s.surname as sub_surname')
            ->from('tt_substitutions')
            ->join('staff as a', 'a.id = tt_substitutions.absent_staff_id', 'left')
            ->join('staff as s', 's.id = tt_substitutions.substitute_staff_id', 'left')
            ->where('tt_substitutions.session_id', $session_id)
            ->where('tt_substitutions.class_id', $class_id)
            ->where('tt_substitutions.section_id', $section_id)
            ->where_in('tt_substitutions.date', $dates)
            ->where('tt_substitutions.status !=', 'cancelled')
            ->get()->result();
    }

    public function getForTeacherWeek($session_id, $staff_id, array $dates)
    {
        if (empty($dates)) return ['absent' => [], 'covering' => []];

        $absent = $this->db->select('tt_substitutions.*, s.name as sub_name, s.surname as sub_surname, subjects.name as subject_name, classes.class as class_name, sections.section as section_name')
            ->from('tt_substitutions')
            ->join('staff as s', 's.id = tt_substitutions.substitute_staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_substitutions.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('classes', 'classes.id = tt_substitutions.class_id', 'left')
            ->join('sections', 'sections.id = tt_substitutions.section_id', 'left')
            ->where('tt_substitutions.session_id', $session_id)
            ->where('tt_substitutions.absent_staff_id', $staff_id)
            ->where_in('tt_substitutions.date', $dates)
            ->where('tt_substitutions.status !=', 'cancelled')
            ->get()->result();

        $covering = $this->db->select('tt_substitutions.*, a.name as absent_name, a.surname as absent_surname, subjects.name as subject_name, classes.class as class_name, sections.section as section_name')
            ->from('tt_substitutions')
            ->join('staff as a', 'a.id = tt_substitutions.absent_staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_substitutions.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('classes', 'classes.id = tt_substitutions.class_id', 'left')
            ->join('sections', 'sections.id = tt_substitutions.section_id', 'left')
            ->where('tt_substitutions.session_id', $session_id)
            ->where('tt_substitutions.substitute_staff_id', $staff_id)
            ->where_in('tt_substitutions.date', $dates)
            ->where('tt_substitutions.status !=', 'cancelled')
            ->get()->result();

        return ['absent' => $absent, 'covering' => $covering];
    }

    public function getByDate($session_id, $date)
    {
        return $this->db->select('tt_substitutions.*, a.name as absent_name, a.surname as absent_surname, s.name as sub_name, s.surname as sub_surname, subjects.name as subject_name, classes.class as class_name, sections.section as section_name, tt_periods.name as period_name, tt_periods.start_time, tt_periods.sort_order as period_sort')
            ->from('tt_substitutions')
            ->join('staff as a', 'a.id = tt_substitutions.absent_staff_id', 'left')
            ->join('staff as s', 's.id = tt_substitutions.substitute_staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_substitutions.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('classes', 'classes.id = tt_substitutions.class_id', 'left')
            ->join('sections', 'sections.id = tt_substitutions.section_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_substitutions.period_id', 'left')
            ->where('tt_substitutions.session_id', $session_id)
            ->where('tt_substitutions.date', $date)
            ->where('tt_substitutions.status !=', 'cancelled')
            ->order_by('tt_periods.sort_order', 'ASC')
            ->order_by('a.name', 'ASC')
            ->get()->result();
    }

    public function getReport($session_id, $from_date, $to_date, $staff_id = null)
    {
        $this->db->select('tt_substitutions.*, a.name as absent_name, a.surname as absent_surname, a.employee_id as absent_employee_id, s.name as sub_name, s.surname as sub_surname, s.employee_id as sub_employee_id, subjects.name as subject_name, classes.class, sections.section, tt_periods.name as period_name, tt_periods.start_time')
            ->from('tt_substitutions')
            ->join('staff as a', 'a.id = tt_substitutions.absent_staff_id', 'left')
            ->join('staff as s', 's.id = tt_substitutions.substitute_staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_substitutions.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('classes', 'classes.id = tt_substitutions.class_id', 'left')
            ->join('sections', 'sections.id = tt_substitutions.section_id', 'left')
            ->join('tt_periods', 'tt_periods.id = tt_substitutions.period_id', 'left')
            ->where('tt_substitutions.session_id', $session_id)
            ->where('tt_substitutions.status', 'confirmed');
        if ($from_date) $this->db->where('tt_substitutions.date >=', $from_date);
        if ($to_date)   $this->db->where('tt_substitutions.date <=', $to_date);
        if ($staff_id)  $this->db->where('tt_substitutions.absent_staff_id', $staff_id);
        return $this->db->order_by('tt_substitutions.date','DESC')->get()->result();
    }
}
