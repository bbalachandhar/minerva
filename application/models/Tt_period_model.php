<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_period_model extends MY_Model
{
    public function getAll($session_id)
    {
        return $this->db->where('session_id', $session_id)
                        ->order_by('sort_order', 'ASC')
                        ->get('tt_periods')->result();
    }

    public function getAllNonBreak($session_id)
    {
        return $this->db->where('session_id', $session_id)
                        ->where('is_break', 0)
                        ->order_by('sort_order', 'ASC')
                        ->get('tt_periods')->result();
    }

    public function getById($id)
    {
        return $this->db->where('id', $id)->get('tt_periods')->row();
    }

    public function save($data)
    {
        $this->db->trans_start();
        if (!empty($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id)->update('tt_periods', $data);
        } else {
            $this->db->insert('tt_periods', $data);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->delete('tt_periods');
    }

    public function updateOrder($id, $sort_order)
    {
        $this->db->where('id', $id)->update('tt_periods', ['sort_order' => $sort_order]);
    }

    public function getCount($session_id)
    {
        return $this->db->where('session_id', $session_id)->where('is_break', 0)->count_all_results('tt_periods');
    }
}
