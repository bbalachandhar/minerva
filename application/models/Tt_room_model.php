<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_room_model extends MY_Model
{
    public function getAll()
    {
        return $this->db->order_by('room_type', 'ASC')->order_by('name', 'ASC')->get('tt_rooms')->result();
    }

    public function getActive()
    {
        return $this->db->where('is_active', 1)->order_by('room_type','ASC')->order_by('name','ASC')->get('tt_rooms')->result();
    }

    public function getByType($type)
    {
        return $this->db->where('room_type', $type)->where('is_active', 1)->get('tt_rooms')->result();
    }

    public function getById($id)
    {
        return $this->db->where('id', $id)->get('tt_rooms')->row();
    }

    public function save($data)
    {
        $this->db->trans_start();
        if (!empty($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id)->update('tt_rooms', $data);
        } else {
            $this->db->insert('tt_rooms', $data);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->update('tt_rooms', ['is_active' => 0]);
    }

    public function getAvailableRooms($session_id, $day, $period_id, $room_type = null)
    {
        $booked_rooms = $this->db->select('room_id')
            ->where('session_id', $session_id)
            ->where('day', $day)
            ->where('period_id', $period_id)
            ->where('room_id IS NOT NULL', null, false)
            ->get('tt_entries')->result_array();

        $booked_ids = array_column($booked_rooms, 'room_id');

        $this->db->where('is_active', 1);
        if (!empty($booked_ids)) {
            $this->db->where_not_in('id', $booked_ids);
        }
        if ($room_type && $room_type !== 'any') {
            $this->db->where('room_type', $room_type);
        }
        return $this->db->get('tt_rooms')->result();
    }
}
