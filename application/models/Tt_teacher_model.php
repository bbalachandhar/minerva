<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_teacher_model extends MY_Model
{
    // -------------------------------------------------------------------------
    // Constraints
    // -------------------------------------------------------------------------

    public function getAllConstraints($session_id)
    {
        return $this->db->select('tt_teacher_constraints.*, staff.name, staff.surname, staff.employee_id, tt_rooms.name as preferred_room_name')
            ->from('tt_teacher_constraints')
            ->join('staff', 'staff.id = tt_teacher_constraints.staff_id')
            ->join('tt_rooms', 'tt_rooms.id = tt_teacher_constraints.preferred_room_id', 'left')
            ->where('tt_teacher_constraints.session_id', $session_id)
            ->order_by('staff.name','ASC')
            ->get()->result();
    }

    public function getConstraint($session_id, $staff_id)
    {
        return $this->db->where('session_id', $session_id)
            ->where('staff_id', $staff_id)
            ->get('tt_teacher_constraints')->row();
    }

    public function getAllConstraintsMap($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_teacher_constraints')->result();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->staff_id] = $r;
        }
        return $map;
    }

    public function saveConstraint($data)
    {
        $this->db->trans_start();
        if (!empty($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id)->update('tt_teacher_constraints', $data);
        } else {
            // Upsert by staff+session
            $existing = $this->getConstraint($data['session_id'], $data['staff_id']);
            if ($existing) {
                $this->db->where('id', $existing->id)->update('tt_teacher_constraints', $data);
            } else {
                $this->db->insert('tt_teacher_constraints', $data);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function deleteConstraint($id)
    {
        $this->db->where('id', $id)->delete('tt_teacher_constraints');
    }

    // -------------------------------------------------------------------------
    // Unavailability
    // -------------------------------------------------------------------------

    public function getUnavailability($session_id, $staff_id)
    {
        return $this->db->where('session_id', $session_id)
            ->where('staff_id', $staff_id)
            ->get('tt_teacher_unavail')->result();
    }

    public function getUnavailabilityMap($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_teacher_unavail')->result();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->staff_id][$r->day][$r->period_id] = true;
        }
        return $map;
    }

    public function saveUnavailability($session_id, $staff_id, $slots)
    {
        $this->db->trans_start();
        // Delete all existing for this teacher+session then re-insert
        $this->db->where('session_id', $session_id)->where('staff_id', $staff_id)->delete('tt_teacher_unavail');
        if (!empty($slots)) {
            $insert = [];
            foreach ($slots as $slot) {
                $insert[] = [
                    'session_id' => $session_id,
                    'staff_id'   => $staff_id,
                    'day'        => $slot['day'],
                    'period_id'  => (int) $slot['period_id'],
                    'reason'     => $slot['reason'] ?? null,
                ];
            }
            $this->db->insert_batch('tt_teacher_unavail', $insert);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
