<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Filetype_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();

    }

    public function get($id = null)
    {
        $this->db->select()->from('filetypes');
        $query = $this->db->get();
        $result = $query->row();
        
        if (is_null($result)) {
            $default_filetype = new stdClass();
            $default_filetype->image_extension = '';
            $default_filetype->file_extension = '';
            $default_filetype->image_mime = '';
            $default_filetype->file_mime = '';
            $default_filetype->file_size = 0;
            $default_filetype->image_size = 0;
            return $default_filetype;
        }
        return $result;
    }

    public function add($data)
    {
        $q = $this->db->get('filetypes');
        if ($q->num_rows() > 0) {
            $row = $q->row();
            $this->db->where('id', $row->id);
            $this->db->update('filetypes', $data);
        } else {
            $this->db->insert('filetypes', $data);
        }
    }

}
