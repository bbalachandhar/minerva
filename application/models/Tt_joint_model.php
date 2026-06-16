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

    /**
     * For class timetable display: returns a map of sgs_id → "Name1, Name2"
     * covering all teachers assigned to any joint lesson that this class-section
     * participates in.  Used so the class grid shows ALL teachers for joint slots
     * (individual teacher timetable still shows only that teacher's own entries).
     */
    public function getTeacherMapForClass($session_id, $class_id, $section_id)
    {
        $rows = $this->db
            ->select('sl.subject_group_subject_id as sgs_id, staff.name, staff.surname, jlt.sort_order')
            ->from('tt_subject_load sl')
            ->join('tt_joint_lesson_teachers jlt', 'jlt.joint_lesson_id = sl.joint_lesson_id')
            ->join('staff', 'staff.id = jlt.staff_id', 'left')
            ->where('sl.session_id', $session_id)
            ->where('sl.class_id', $class_id)
            ->where('sl.section_id', $section_id)
            ->where('sl.joint_lesson_id IS NOT NULL', null, false)
            ->order_by('jlt.sort_order', 'ASC')
            ->get()->result();

        $map = [];
        foreach ($rows as $r) {
            $name = trim($r->name . ' ' . ($r->surname ?? ''));
            if ($name) $map[$r->sgs_id][] = $name;
        }
        foreach ($map as $sgs_id => $names) {
            $map[$sgs_id] = implode(', ', array_unique($names));
        }
        return $map;
    }

    public function save($session_id, $data, $classes, $teacher_ids = [])
    {
        $this->db->trans_start();

        $teacher_ids = array_values(array_unique(array_filter(array_map('intval', (array) $teacher_ids))));

        $row = [
            'session_id'            => $session_id,
            'name'                  => $data['name'],
            'subject_id'            => (int) $data['subject_id'],
            'staff_id'              => isset($teacher_ids[0]) ? $teacher_ids[0] : null,
            'alt_staff_id'          => isset($teacher_ids[1]) ? $teacher_ids[1] : null,
            'room_id'               => !empty($data['room_id']) ? (int) $data['room_id'] : null,
            'periods_per_week'      => max(1, (int) ($data['periods_per_week']    ?? 1)),
            'consecutive_periods'   => max(1, (int) ($data['consecutive_periods'] ?? 1)),
            'max_per_day'           => max(1, (int) ($data['max_per_day']         ?? 1)),
            'distribute_evenly'     => !empty($data['distribute_evenly']) ? 1 : 0,
            'priority'              => max(1, min(10, (int) ($data['priority'] ?? 5))),
            'notes'                 => $data['notes'] ?? null,
            'all_teachers_required' => !empty($data['all_teachers_required']) ? 1 : 0,
            'fixed_slots'           => !empty($data['fixed_slots']) ? $data['fixed_slots'] : null,
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

        if (!$this->db->trans_status()) return false;

        // Sync subject load rows outside the main transaction (best-effort)
        $this->_syncSubjectLoad($session_id, $id, $row, $teacher_ids, $classes);

        return $id;
    }

    public function delete($id)
    {
        // Remove linked subject load teacher pool rows first
        $linked_ids = $this->db->select('id')->where('joint_lesson_id', $id)->get('tt_subject_load')->result();
        foreach ($linked_ids as $sl) {
            $this->db->where('subject_load_id', $sl->id)->delete('tt_subject_load_teachers');
        }
        $this->db->where('joint_lesson_id', $id)->delete('tt_subject_load');

        $this->db->trans_start();
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_classes');
        $this->db->where('joint_lesson_id', $id)->delete('tt_joint_lesson_teachers');
        $this->db->where('id', $id)->delete('tt_joint_lessons');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Auto-upsert one tt_subject_load row per participating class-section.
     * Rows are tagged with joint_lesson_id so the generator skips them in
     * the regular pass and they appear as read-only in the Subject Load screen.
     * Orphaned rows (from classes removed from the lesson) are deleted.
     */
    private function _syncSubjectLoad($session_id, $joint_lesson_id, $lesson_row, $teacher_ids, $classes)
    {
        $subject_id  = (int) $lesson_row['subject_id'];
        $primary_tid = isset($teacher_ids[0]) ? $teacher_ids[0] : null;

        // Build sgs lookup: [class_id] => {sgs_id, sg_id}
        if (empty($classes)) {
            // No classes → delete any orphaned rows
            $this->db->where('joint_lesson_id', $joint_lesson_id)->delete('tt_subject_load');
            return;
        }

        $class_ids = array_values(array_unique(array_map(fn($c) => (int)$c['class_id'], $classes)));
        $sgs_rows  = $this->db->query(
            'SELECT sgs.id as sgs_id, sgs.subject_group_id as sg_id, sg.class_id
             FROM subject_group_subjects sgs
             JOIN subject_groups sg ON sg.id = sgs.subject_group_id
             WHERE sg.session_id = ?
               AND sgs.subject_id = ?
               AND sg.class_id IN (' . implode(',', array_fill(0, count($class_ids), '?')) . ')',
            array_merge([$session_id, $subject_id], $class_ids)
        )->result();

        $sgs_map = []; // [class_id] => {sgs_id, sg_id}
        foreach ($sgs_rows as $r) {
            $sgs_map[(int)$r->class_id] = ['sgs_id' => (int)$r->sgs_id, 'sg_id' => (int)$r->sg_id];
        }

        // Track which (class_id, section_id) we upserted — for orphan cleanup
        $kept_keys = [];

        foreach ($classes as $cs) {
            $class_id   = (int) $cs['class_id'];
            $section_id = (int) $cs['section_id'];

            if (!isset($sgs_map[$class_id])) continue; // subject not in this class's group — skip

            $sgs_id = $sgs_map[$class_id]['sgs_id'];
            $sg_id  = $sgs_map[$class_id]['sg_id'];

            // Keyed by sgs_id too — if this lesson's subject is later changed,
            // the old subject's row must NOT look "kept" just because the same
            // class+section got a fresh row under the new subject.
            $kept_keys[] = $class_id . '_' . $section_id . '_' . $sgs_id;

            $existing = $this->db
                ->where('session_id',               $session_id)
                ->where('class_id',                 $class_id)
                ->where('section_id',               $section_id)
                ->where('subject_group_subject_id', $sgs_id)
                ->where('batch_id IS NULL',         null, false)
                ->get('tt_subject_load')->row();

            $load_row = [
                'session_id'               => $session_id,
                'class_id'                 => $class_id,
                'section_id'               => $section_id,
                'subject_group_id'         => $sg_id,
                'subject_group_subject_id' => $sgs_id,
                'staff_id'                 => $primary_tid,
                'alt_staff_id'             => isset($teacher_ids[1]) ? $teacher_ids[1] : null,
                'all_teachers_required'    => !empty($lesson_row['all_teachers_required']) ? 1 : 0,
                'periods_per_week'         => (int) $lesson_row['periods_per_week'],
                'consecutive_periods'      => (int) $lesson_row['consecutive_periods'],
                'max_per_day'              => (int) $lesson_row['max_per_day'],
                'distribute_evenly'        => (int) $lesson_row['distribute_evenly'],
                'priority'                 => (int) $lesson_row['priority'],
                'preferred_room_type'      => 'any',
                'preferred_room_id'        => !empty($lesson_row['room_id']) ? (int)$lesson_row['room_id'] : null,
                'batch_id'                 => null,
                'joint_lesson_id'          => $joint_lesson_id,
            ];

            if ($existing) {
                if ($existing->joint_lesson_id !== null && (int)$existing->joint_lesson_id !== $joint_lesson_id) {
                    continue; // Owned by a different joint lesson — skip
                }
                // Owned by this joint lesson OR unowned manual row (joint_lesson_id=NULL) — claim/update
                $this->db->where('id', $existing->id)->update('tt_subject_load', $load_row);
                $load_id = $existing->id;
            } else {
                $this->db->insert('tt_subject_load', $load_row);
                $load_id = $this->db->insert_id();
            }

            // Sync teacher pool
            $this->db->where('subject_load_id', $load_id)->delete('tt_subject_load_teachers');
            foreach ($teacher_ids as $order => $t_id) {
                $this->db->insert('tt_subject_load_teachers', [
                    'subject_load_id' => $load_id,
                    'staff_id'        => $t_id,
                    'sort_order'      => $order,
                ]);
            }
        }

        // Delete orphaned linked rows (class-sections no longer in this lesson)
        $orphans = $this->db
            ->where('joint_lesson_id', $joint_lesson_id)
            ->where('session_id', $session_id)
            ->get('tt_subject_load')->result();

        foreach ($orphans as $o) {
            $key = $o->class_id . '_' . $o->section_id . '_' . $o->subject_group_subject_id;
            if (!in_array($key, $kept_keys)) {
                $this->db->where('subject_load_id', $o->id)->delete('tt_subject_load_teachers');
                $this->db->where('id', $o->id)->delete('tt_subject_load');
            }
        }
    }

    /**
     * Returns all joint lessons enriched for the generator.
     * Each lesson has ->classes and ->teacher_ids (ordered array of staff IDs).
     */
    public function getAllForGeneration($session_id)
    {
        $lessons = $this->db->select('tj.*, subjects.tt_color, subjects.tt_abbr, subjects.name as subject_name, subjects.code as subject_code, subjects.type as subject_type, tj.all_teachers_required')
            ->from('tt_joint_lessons tj')
            ->join('subjects', 'subjects.id = tj.subject_id', 'left')
            ->where('tj.session_id', $session_id)
            ->order_by('tj.priority', 'DESC')
            ->get()->result();

        if (empty($lessons)) return [];

        $subject_ids = array_unique(array_column($lessons, 'subject_id'));
        $sgs_rows = $this->db->query(
            'SELECT sgs.id as sgs_id, sgs.subject_id, sg.id as sg_id, sg.class_id
             FROM subject_group_subjects sgs
             JOIN subject_groups sg ON sg.id = sgs.subject_group_id
             WHERE sg.session_id = ?
               AND sgs.subject_id IN (' . implode(',', array_fill(0, count($subject_ids), '?')) . ')',
            array_merge([$session_id], $subject_ids)
        )->result();

        $sgs_map = [];
        foreach ($sgs_rows as $r) {
            $sgs_map[$r->subject_id][$r->class_id] = ['sgs_id' => (int)$r->sgs_id, 'sg_id' => (int)$r->sg_id];
        }

        $lesson_ids = array_column($lessons, 'id');
        $teacher_rows = [];
        if (!empty($lesson_ids)) {
            $teacher_rows = $this->db->select('joint_lesson_id, staff_id')
                ->where_in('joint_lesson_id', $lesson_ids)
                ->order_by('sort_order', 'ASC')
                ->get('tt_joint_lesson_teachers')->result();
        }
        $teacher_map = [];
        foreach ($teacher_rows as $tr) {
            $teacher_map[$tr->joint_lesson_id][] = (int) $tr->staff_id;
        }

        foreach ($lessons as $l) {
            $l->classes = $this->db->select('class_id, section_id')
                ->where('joint_lesson_id', $l->id)
                ->get('tt_joint_lesson_classes')->result();

            $t_ids = $teacher_map[$l->id] ?? [];
            if (empty($t_ids)) {
                if ($l->staff_id)     $t_ids[] = (int) $l->staff_id;
                if ($l->alt_staff_id) $t_ids[] = (int) $l->alt_staff_id;
            }
            $l->teacher_ids = $t_ids;

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
