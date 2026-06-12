<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_subjectload_model extends MY_Model
{
    public function getForClassSection($session_id, $class_id, $section_id)
    {
        $rows = $this->db->select('tt_subject_load.*, subject_group_subjects.subject_id, staff.name as staff_name, staff.surname as staff_surname, subjects.name as subject_name, subjects.code as subject_code, subjects.type as subject_type, tt_joint_lessons.name as joint_lesson_name')
            ->from('tt_subject_load')
            ->join('staff', 'staff.id = tt_subject_load.staff_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = tt_subject_load.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->join('tt_joint_lessons', 'tt_joint_lessons.id = tt_subject_load.joint_lesson_id', 'left')
            ->where('tt_subject_load.session_id', $session_id)
            ->where('tt_subject_load.class_id', $class_id)
            ->where('tt_subject_load.section_id', $section_id)
            ->order_by('subjects.name', 'ASC')
            ->get()->result();
        $this->_enrichWithTeachers($rows);
        return $rows;
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

        // Exclude joint-linked rows — generator handles them in the joint pre-pass
        $this->db->where('tt_subject_load.joint_lesson_id IS NULL', null, false);

        $rows = $this->db->get()->result();
        $this->_enrichWithTeachers($rows);
        return $rows;
    }

    public function saveRows($session_id, $class_id, $section_id, $rows)
    {
        $this->db->trans_start();
        foreach ($rows as $row) {
            $sgs_id   = (int) $row['subject_group_subject_id'];
            $batch_id = !empty($row['batch_id']) ? (int)$row['batch_id'] : null;

            // Build teacher pool from multi-select array; fall back to legacy single fields
            $teacher_ids_raw = isset($row['teacher_ids']) && is_array($row['teacher_ids'])
                ? $row['teacher_ids']
                : [];
            if (empty($teacher_ids_raw) && !empty($row['staff_id'])) {
                $teacher_ids_raw = [$row['staff_id']];
                if (!empty($row['alt_staff_id'])) $teacher_ids_raw[] = $row['alt_staff_id'];
            }
            $teacher_ids = array_values(array_filter(array_map('intval', $teacher_ids_raw)));

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
                'staff_id'                 => $teacher_ids[0] ?? null,
                'alt_staff_id'             => $teacher_ids[1] ?? null,
                'all_teachers_required'    => !empty($row['all_teachers_required']) ? 1 : 0,
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
                $load_id = $existing->id;
            } else {
                $this->db->insert('tt_subject_load', $data);
                $load_id = $this->db->insert_id();
            }

            // Replace teacher pool
            $this->db->where('subject_load_id', $load_id)->delete('tt_subject_load_teachers');
            foreach ($teacher_ids as $order => $t_id) {
                $this->db->insert('tt_subject_load_teachers', [
                    'subject_load_id' => $load_id,
                    'staff_id'        => $t_id,
                    'sort_order'      => $order,
                ]);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id)
    {
        $this->db->where('subject_load_id', $id)->delete('tt_subject_load_teachers');
        $this->db->where('id', $id)->delete('tt_subject_load');
    }

    private function _enrichWithTeachers(&$rows)
    {
        if (empty($rows)) return;
        $ids = array_map(fn($r) => (int)$r->id, $rows);

        $t_rows = $this->db->select('subject_load_id, staff_id')
            ->where_in('subject_load_id', $ids)
            ->order_by('sort_order', 'ASC')
            ->get('tt_subject_load_teachers')->result();

        $t_map = [];
        foreach ($t_rows as $tr) {
            $t_map[(int)$tr->subject_load_id][] = (int) $tr->staff_id;
        }

        foreach ($rows as $row) {
            $tids = $t_map[$row->id] ?? [];
            if (empty($tids)) {
                if (!empty($row->staff_id))     $tids[] = (int) $row->staff_id;
                if (!empty($row->alt_staff_id)) $tids[] = (int) $row->alt_staff_id;
            }
            $row->teacher_ids = $tids;
        }
    }
}
