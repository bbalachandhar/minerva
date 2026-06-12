<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_subjectload_model extends MY_Model
{
    public function getForClassSection($session_id, $class_id, $section_id)
    {
        return $this->db->select('tt_subject_load.*, subject_group_subjects.subject_id, staff.name as staff_name, staff.surname as staff_surname, subjects.name as subject_name, subjects.code as subject_code, subjects.type as subject_type')
            ->from('tt_subject_load')
            ->join('staff', 'staff.id = tt_subject_load.staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_subject_load.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->where('tt_subject_load.session_id', $session_id)
            ->where('tt_subject_load.class_id', $class_id)
            ->where('tt_subject_load.section_id', $section_id)
            ->order_by('subjects.name', 'ASC')
            ->get()->result();
    }

    public function getSubjectsForClass($session_id, $class_id)
    {
        $sql = "SELECT sgs.id as subject_group_subject_id, sgs.subject_group_id, sgs.subject_id,
                       sub.name as subject_name, sub.code as subject_code, sub.type as subject_type,
                       sg.name as group_name
                FROM subject_group_subjects sgs
                INNER JOIN subjects sub ON sub.id = sgs.subject_id
                INNER JOIN subject_groups sg ON sg.id = sgs.subject_group_id
                WHERE sg.class_id = " . (int)$class_id . "
                  AND sgs.session_id = " . (int)$session_id . "
                ORDER BY sub.name ASC";
        return $this->db->query($sql)->result();
    }

    public function getAllForSession($session_id)
    {
        return $this->db->select('tt_subject_load.*, classes.class, sections.section, subjects.name as subject_name, subjects.code as subject_code, staff.name as staff_name, staff.surname as staff_surname, tt_batches.batch_name')
            ->from('tt_subject_load')
            ->join('classes', 'classes.id = tt_subject_load.class_id')
            ->join('sections', 'sections.id = tt_subject_load.section_id')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_subject_load.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_subject_load.staff_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_subject_load.batch_id', 'left')
            ->where('tt_subject_load.session_id', $session_id)
            ->order_by('tt_subject_load.priority','DESC')
            ->get()->result();
    }

    public function getAllForClassScope($session_id, $class_scope)
    {
        $this->db->select('tt_subject_load.*, subjects.name as subject_name, staff.name as staff_name, staff.surname as staff_surname, tt_batches.batch_name')
            ->from('tt_subject_load')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_subject_load.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('staff', 'staff.id = tt_subject_load.staff_id', 'left')
            ->join('tt_batches', 'tt_batches.id = tt_subject_load.batch_id', 'left')
            ->where('tt_subject_load.session_id', $session_id)
            ->order_by('tt_subject_load.priority', 'DESC');

        if (!empty($class_scope)) {
            $this->db->group_start();
            foreach ($class_scope as $cs) {
                $this->db->or_group_start()
                    ->where('tt_subject_load.class_id', (int)$cs['class_id'])
                    ->where('tt_subject_load.section_id', (int)$cs['section_id'])
                    ->group_end();
            }
            $this->db->group_end();
        }

        return $this->db->get()->result();
    }

    public function saveRows($session_id, $class_id, $section_id, $rows)
    {
        $this->db->trans_start();
        foreach ($rows as $row) {
            $sgs_id  = (int) $row['subject_group_subject_id'];
            $batch_id = !empty($row['batch_id']) ? (int)$row['batch_id'] : null;

            $existing = $this->db->where('session_id', $session_id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('subject_group_subject_id', $sgs_id)
                ->where('batch_id', $batch_id)
                ->get('tt_subject_load')->row();

            $data = [
                'session_id'               => $session_id,
                'class_id'                 => $class_id,
                'section_id'               => $section_id,
                'subject_group_id'         => (int) $row['subject_group_id'],
                'subject_group_subject_id' => $sgs_id,
                'staff_id'                 => (int) $row['staff_id'],
                'alt_staff_id'             => !empty($row['alt_staff_id']) ? (int)$row['alt_staff_id'] : null,
                'periods_per_week'         => (int) $row['periods_per_week'],
                'consecutive_periods'      => (int) ($row['consecutive_periods'] ?? 1),
                'preferred_room_type'      => $row['preferred_room_type'] ?? 'any',
                'preferred_room_id'        => !empty($row['preferred_room_id']) ? (int)$row['preferred_room_id'] : null,
                'batch_id'                 => $batch_id,
                'priority'                 => (int) ($row['priority'] ?? 5),
                'max_per_day'              => (int) ($row['max_per_day'] ?? 2),
                'min_per_day'              => !empty($row['min_per_day']) ? 1 : 0,
                'distribute_evenly'        => !empty($row['distribute_evenly']) ? 1 : 0,
            ];

            if ($existing) {
                $this->db->where('id', $existing->id)->update('tt_subject_load', $data);
            } else {
                $this->db->insert('tt_subject_load', $data);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->delete('tt_subject_load');
    }
}
