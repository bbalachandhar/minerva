<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Finalyearclass_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getClassIds()
    {
        $this->db->select('class_id')->from('final_year_classes');
        $this->db->order_by('class_id');
        $query = $this->db->get();
        $rows = $query->result_array();

        return array_map(function ($row) {
            return (int)$row['class_id'];
        }, $rows);
    }

    public function replaceAll($class_ids)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        $this->db->empty_table('final_year_classes');

        if (!empty($class_ids)) {
            $insert_rows = array();
            foreach ($class_ids as $class_id) {
                $insert_rows[] = array(
                    'class_id' => (int)$class_id,
                    'created_at' => date('Y-m-d H:i:s'),
                );
            }
            $this->db->insert_batch('final_year_classes', $insert_rows);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_commit();
        return true;
    }
}
