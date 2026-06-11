<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_class_unavail_model extends MY_Model
{
    public function getForClassSection($session_id, $class_id, $section_id)
    {
        return $this->db->where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get('tt_class_unavail')->result();
    }

    public function getMapForSession($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_class_unavail')->result();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->class_id][$r->section_id][$r->day][$r->period_id] = true;
        }
        return $map;
    }

    public function saveUnavailability($session_id, $class_id, $section_id, $slots)
    {
        $this->db->trans_start();
        $this->db->where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->delete('tt_class_unavail');
        if (!empty($slots)) {
            $insert = [];
            foreach ($slots as $slot) {
                if (empty($slot['day']) || empty($slot['period_id'])) continue;
                $insert[] = [
                    'session_id' => $session_id,
                    'class_id'   => $class_id,
                    'section_id' => $section_id,
                    'day'        => $slot['day'],
                    'period_id'  => (int) $slot['period_id'],
                    'reason'     => $slot['reason'] ?? null,
                ];
            }
            if (!empty($insert)) {
                $this->db->insert_batch('tt_class_unavail', $insert);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
