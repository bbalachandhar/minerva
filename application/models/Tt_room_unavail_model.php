<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_room_unavail_model extends MY_Model
{
    public function getForRoom($session_id, $room_id)
    {
        return $this->db->where('session_id', $session_id)
            ->where('room_id', $room_id)
            ->get('tt_room_unavail')->result();
    }

    public function getMapForSession($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_room_unavail')->result();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->room_id][$r->day][$r->period_id] = true;
        }
        return $map;
    }

    public function saveUnavailability($session_id, $room_id, $slots)
    {
        $this->db->trans_start();
        $this->db->where('session_id', $session_id)->where('room_id', $room_id)->delete('tt_room_unavail');
        if (!empty($slots)) {
            $insert = [];
            foreach ($slots as $slot) {
                if (empty($slot['day']) || empty($slot['period_id'])) continue;
                $insert[] = [
                    'session_id' => $session_id,
                    'room_id'    => $room_id,
                    'day'        => $slot['day'],
                    'period_id'  => (int) $slot['period_id'],
                    'reason'     => $slot['reason'] ?? null,
                ];
            }
            if (!empty($insert)) {
                $this->db->insert_batch('tt_room_unavail', $insert);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
