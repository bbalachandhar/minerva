<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Tt_batch_model extends MY_Model
{
    public function getAllWithNames($session_id)
    {
        return $this->db->select('tt_batches.*, classes.class, sections.section')
            ->from('tt_batches')
            ->join('classes', 'classes.id = tt_batches.class_id')
            ->join('sections', 'sections.id = tt_batches.section_id')
            ->where('tt_batches.session_id', $session_id)
            ->order_by('classes.class','ASC')
            ->order_by('sections.section','ASC')
            ->order_by('tt_batches.batch_name','ASC')
            ->get()->result();
    }

    public function getForClassSection($session_id, $class_id, $section_id)
    {
        return $this->db->where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->order_by('batch_name','ASC')
            ->get('tt_batches')->result();
    }

    public function getById($id)
    {
        return $this->db->where('id', $id)->get('tt_batches')->row();
    }

    public function save($data)
    {
        $this->db->trans_start();
        if (!empty($data['id']) && $data['id'] > 0) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id)->update('tt_batches', $data);
        } else {
            $this->db->insert('tt_batches', $data);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function delete($id)
    {
        $this->db->where('id', $id)->delete('tt_batches');
    }
}
