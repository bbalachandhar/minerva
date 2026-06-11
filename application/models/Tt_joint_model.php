<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_joint_model extends MY_Model
{
    public function getAll($session_id)
    {
        $lessons = $this->db->select('
                tj.*,
                subjects.name  as subject_name,
                subjects.code  as subject_code,
                subjects.tt_color,
                subjects.tt_abbr,
                staff.name     as staff_name,
                staff.surname  as staff_surname,
                staff.employee_id,
                tt_rooms.name  as room_name
            ')
            ->from('tt_joint_lessons tj')
            ->join('subjects',  'subjects.id  = tj.subject_id',  'left')
            ->join('staff',     'staff.id     = tj.staff_id',    'left')
            ->join('tt_rooms',  'tt_rooms.id  = tj.room_id',     'left')
            ->where('tj.session_id', $session_id)
            ->order_by('tj.priority', 'DESC')
            ->order_by('tj.name',     'ASC')
            ->get()->result();

        foreach ($lessons as $l) {
            $l->classes = $this->_getClasses($l->id);
        }
        return $lessons;
    }

    public function getById($id)
    {
        $lesson = $this->db->select('tj.*, subjects.name as subject_name, staff.name as staff_name, staff.surname as staff_surname')
            ->from('tt_joint_lessons tj')
            ->join('subjects', 'subjects.id = tj.subject_id', 'left')
            ->join('staff',    'staff.id    = tj.staff_id',   'left')
            ->where('tj.id', $id)
            ->get()->row();
        if ($lesson) {
            $lesson->classes = $this->_getClasses($id);
        }
        return $lesson;
    }

    private function _getClasses($joint_lesson_id)
    {
        return $this->db->select('jlc.*, classes.class as class_name, sections.section as section_name')
            ->from('tt_joint_lesson_classes jlc')
            ->join('classes',  'classes.id  = jlc.class_id',   'left')
            ->join('sections', 'sections.id = jlc.section_id', 'left')
            ->where('jlc.joint_lesson_id', $joint_lesson_id)
            ->order_by('classes.class', 'ASC')
            ->get()->result();
    }

    public function save($session_id, $data, $classes)
    {
        $this->db->trans_start();

        $row = [
            'session_id'          => $session_id,
            'name'                => $data['name'],
            'subject_id'          => (int) $data['subject_id'],
            'staff_id'            => !empty($data['staff_id'])     ? (int) $data['staff_id']     : null,
            'alt_staff_id'        => !empty($data['alt_staff_id']) ? (int) $data['alt_staff_id'] : null,
            'room_id'             => !empty($data['room_id'])      ? (int) $data['room_id']      : null,
            'periods_per_week'    => max(1, (int) ($data['periods_per_week']    ?? 1)),
            'consecutive_periods' => max(1, (int) ($data['consecutive_periods'] ?? 1)),
            'max_per_day'         => max(1, (int) ($data['max_per_day']         ?? 1)),
            'distribute_evenly'   => !empty($data['distribute_evenly']) ? 1 : 0,
            'priority'            => max(1, min(10, (int) ($data['priority'] ?? 5))),
            'notes'               => $data['notes'] ?? null,
        ];

        $id = !empty($data['id']) ? (int) $data['id'] : 0;
        if ($id > 0) {
            $this->db->where('id', $id)->update('tt_joint_lessons', $row);
        } else {
            $this->db->insert('tt_joint_lessons', $row);
            $id = $this->db->insert_id();
        }

        // Replace classes
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_classes');
        foreach ($classes as $cs) {
            $this->db->insert('tt_joint_lesson_classes', [
                'joint_lesson_id' => $id,
                'class_id'        => (int) $cs['class_id'],
                'section_id'      => (int) $cs['section_id'],
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : false;
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_classes');
        $this->db->where('id', $id)->delete('tt_joint_lessons');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Returns all joint lessons enriched for the generator.
     * Each lesson has ->classes (array of {class_id, section_id}) and ->sgs_map
     * which maps class_id → {sgs_id, sg_id} looked up from subject_group_subjects.
     */
    public function getAllForGeneration($session_id)
    {
        $lessons = $this->db->select('tj.*, subjects.tt_color, subjects.tt_abbr, subjects.name as subject_name, subjects.code as subject_code, subjects.type as subject_type')
            ->from('tt_joint_lessons tj')
            ->join('subjects', 'subjects.id = tj.subject_id', 'left')
            ->where('tj.session_id', $session_id)
            ->order_by('tj.priority', 'DESC')
            ->get()->result();

        if (empty($lessons)) return [];

        // Pre-load sgs map for all subject_ids involved in this session
        $subject_ids = array_unique(array_column($lessons, 'subject_id'));
        $sgs_rows = $this->db->query(
            'SELECT sgs.id as sgs_id, sgs.subject_id, sg.id as sg_id, sg.class_id
             FROM subject_group_subjects sgs
             JOIN subject_groups sg ON sg.id = sgs.subject_group_id
             WHERE sg.session_id = ?
               AND sgs.subject_id IN (' . implode(',', array_fill(0, count($subject_ids), '?')) . ')',
            array_merge([$session_id], $subject_ids)
        )->result();

        $sgs_map = []; // [subject_id][class_id] => {sgs_id, sg_id}
        foreach ($sgs_rows as $r) {
            $sgs_map[$r->subject_id][$r->class_id] = ['sgs_id' => (int)$r->sgs_id, 'sg_id' => (int)$r->sg_id];
        }

        foreach ($lessons as $l) {
            $l->classes = $this->db->select('class_id, section_id')
                ->where('joint_lesson_id', $l->id)
                ->get('tt_joint_lesson_classes')->result();
            // Attach sgs_id + sg_id per class
            foreach ($l->classes as $cs) {
                $entry = $sgs_map[$l->subject_id][$cs->class_id] ?? ['sgs_id' => 0, 'sg_id' => 0];
                $cs->sgs_id = $entry['sgs_id'];
                $cs->sg_id  = $entry['sg_id'];
            }
            $l->sgs_map = $sgs_map[$l->subject_id] ?? [];
        }

        return $lessons;
    }
}
