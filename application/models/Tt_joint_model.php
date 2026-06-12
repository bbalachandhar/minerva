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
                tt_rooms.name  as room_name
            ')
            ->from('tt_joint_lessons tj')
            ->join('subjects',  'subjects.id  = tj.subject_id',  'left')
            ->join('tt_rooms',  'tt_rooms.id  = tj.room_id',     'left')
            ->where('tj.session_id', $session_id)
            ->order_by('tj.priority', 'DESC')
            ->order_by('tj.name',     'ASC')
            ->get()->result();

        foreach ($lessons as $l) {
            $l->classes  = $this->_getClasses($l->id);
            $l->teachers = $this->_getTeachers($l->id);
        }
        return $lessons;
    }

    public function getById($id)
    {
        $lesson = $this->db->select('tj.*, subjects.name as subject_name')
            ->from('tt_joint_lessons tj')
            ->join('subjects', 'subjects.id = tj.subject_id', 'left')
            ->where('tj.id', $id)
            ->get()->row();
        if ($lesson) {
            $lesson->classes  = $this->_getClasses($id);
            $lesson->teachers = $this->_getTeachers($id);
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

    private function _getTeachers($joint_lesson_id)
    {
        return $this->db->select('jlt.staff_id, jlt.sort_order, staff.name, staff.surname, staff.employee_id')
            ->from('tt_joint_lesson_teachers jlt')
            ->join('staff', 'staff.id = jlt.staff_id', 'left')
            ->where('jlt.joint_lesson_id', $joint_lesson_id)
            ->order_by('jlt.sort_order', 'ASC')
            ->get()->result();
    }

    public function save($session_id, $data, $classes, $teacher_ids = [])
    {
        $this->db->trans_start();

        // Sanitise teacher_ids to a clean integer array
        $teacher_ids = array_values(array_unique(array_filter(array_map('intval', (array) $teacher_ids))));

        $row = [
            'session_id'          => $session_id,
            'name'                => $data['name'],
            'subject_id'          => (int) $data['subject_id'],
            // Keep legacy columns populated for backward compat
            'staff_id'            => isset($teacher_ids[0]) ? $teacher_ids[0] : null,
            'alt_staff_id'        => isset($teacher_ids[1]) ? $teacher_ids[1] : null,
            'room_id'             => !empty($data['room_id']) ? (int) $data['room_id'] : null,
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

        // Replace teachers
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_teachers');
        foreach ($teacher_ids as $order => $staff_id) {
            $this->db->insert('tt_joint_lesson_teachers', [
                'joint_lesson_id' => $id,
                'staff_id'        => $staff_id,
                'sort_order'      => $order,
            ]);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $id : false;
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_classes');
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_teachers');
        $this->db->where('id', $id)->delete('tt_joint_lessons');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Returns all joint lessons enriched for the generator.
     * Each lesson has ->classes and ->teacher_ids (ordered array of staff IDs).
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

        // Pre-load teacher IDs for all lessons
        $lesson_ids = array_column($lessons, 'id');
        $teacher_rows = [];
        if (!empty($lesson_ids)) {
            $teacher_rows = $this->db->select('joint_lesson_id, staff_id')
                ->where_in('joint_lesson_id', $lesson_ids)
                ->order_by('sort_order', 'ASC')
                ->get('tt_joint_lesson_teachers')->result();
        }
        $teacher_map = []; // [joint_lesson_id] => [staff_id, ...]
        foreach ($teacher_rows as $tr) {
            $teacher_map[$tr->joint_lesson_id][] = (int) $tr->staff_id;
        }

        foreach ($lessons as $l) {
            $l->classes = $this->db->select('class_id, section_id')
                ->where('joint_lesson_id', $l->id)
                ->get('tt_joint_lesson_classes')->result();

            // Use junction table; fall back to legacy columns if junction is empty
            $t_ids = $teacher_map[$l->id] ?? [];
            if (empty($t_ids)) {
                if ($l->staff_id)     $t_ids[] = (int) $l->staff_id;
                if ($l->alt_staff_id) $t_ids[] = (int) $l->alt_staff_id;
            }
            $l->teacher_ids = $t_ids;

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
