<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Coe_schedule_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get full schedule for a batch exam, with subject details.
     */
    public function getSchedule($batch_exam_id)
    {
        return $this->db
            ->select([
                'cs.*',
                'subj.code AS subject_code',
                'subj.name AS subject_name',
                'h.name AS hall_name',
            ])
            ->from('coe_exam_schedule cs')
            ->join('subjects subj', 'subj.id = cs.subject_id', 'left')
            ->join('halls h', 'h.id = cs.hall_id', 'left')
            ->where('cs.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('cs.exam_date ASC, cs.start_time ASC')
            ->get()->result();
    }

    /**
     * Get single schedule row by id.
     */
    public function getById($id)
    {
        return $this->db->where('id', (int) $id)->get('coe_exam_schedule')->row();
    }

    /**
     * Upsert a schedule row by batch_exam_id + subject_id.
     */
    public function saveRow($batch_exam_id, $subject_id, $data)
    {
        $existing = $this->db
            ->where('exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->where('subject_id', (int) $subject_id)
            ->get('coe_exam_schedule')->row();

        $row = [
            'exam_date'    => $data['exam_date']    ?? null,
            'start_time'   => $data['start_time']   ?? null,
            'end_time'     => $data['end_time']      ?? null,
            'session_slot' => in_array($data['session_slot'] ?? '', ['FN', 'AN']) ? $data['session_slot'] : 'FN',
            'hall_id'      => !empty($data['hall_id']) ? (int) $data['hall_id'] : null,
            'notes'        => $data['notes']         ?? null,
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('coe_exam_schedule', $row);
            return $existing->id;
        } else {
            $row['exam_group_class_batch_exam_id'] = (int) $batch_exam_id;
            $row['subject_id']  = (int) $subject_id;
            $row['created_by']  = (int) (isset($this->session) ? $this->session->userdata('staff_id') : 0);
            $this->db->insert('coe_exam_schedule', $row);
            return $this->db->insert_id();
        }
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete('coe_exam_schedule');
    }

    /**
     * Get halls for dropdown.
     */
    public function getHalls()
    {
        return $this->db
            ->select('id, name AS hall_name, capacity')
            ->where('is_active', 1)
            ->order_by('name ASC')
            ->get('halls')->result();
    }
}
