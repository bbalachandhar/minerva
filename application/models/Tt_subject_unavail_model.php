<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_subject_unavail_model extends MY_Model
{
    public function getForSubject($session_id, $subject_id)
    {
        return $this->db->where('session_id', $session_id)
            ->where('subject_id', $subject_id)
            ->get('tt_subject_unavail')->result();
    }

    public function getMapForSession($session_id)
    {
        $rows = $this->db->where('session_id', $session_id)->get('tt_subject_unavail')->result();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->subject_id][$r->day][$r->period_id] = true;
        }
        return $map;
    }

    public function saveUnavailability($session_id, $subject_id, $slots)
    {
        $this->db->trans_start();
        $this->db->where('session_id', $session_id)->where('subject_id', $subject_id)->delete('tt_subject_unavail');
        if (!empty($slots)) {
            $insert = [];
            foreach ($slots as $slot) {
                if (empty($slot['day']) || empty($slot['period_id'])) continue;
                $insert[] = [
                    'session_id' => $session_id,
                    'subject_id' => $subject_id,
                    'day'        => $slot['day'],
                    'period_id'  => (int) $slot['period_id'],
                    'reason'     => $slot['reason'] ?? null,
                ];
            }
            if (!empty($insert)) {
                $this->db->insert_batch('tt_subject_unavail', $insert);
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
