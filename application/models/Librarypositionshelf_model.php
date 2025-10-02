<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Librarypositionshelf_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null)
    {
        $this->db->select('library_position_shelves.*, library_position_racks.rack_name')->from('library_position_shelves');
        $this->db->join('library_position_racks', 'library_position_racks.id = library_position_shelves.rack_id');
        if ($id != null) {
            $this->db->where('library_position_shelves.id', $id);
        } else {
            $this->db->order_by('library_position_shelves.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('library_position_shelves');
        $message   = DELETE_RECORD_CONSTANT . " On library position shelves id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('library_position_shelves', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On library position shelves id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        } else {
            $this->db->insert('library_position_shelves', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On library position shelves id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
            return $insert_id;
        }
    }

    public function get_shelf_by_name_and_rack_id_case_insensitive($name, $rack_id)
    {
        $this->db->where('LOWER(shelf_name)', strtolower($name));
        $this->db->where('rack_id', $rack_id);
        $query = $this->db->get('library_position_shelves');
        return $query->row();
    }

    public function get_by_rack_id($rack_id)
    {
        $this->db->select('library_position_shelves.*, library_position_racks.rack_name')->from('library_position_shelves');
        $this->db->join('library_position_racks', 'library_position_racks.id = library_position_shelves.rack_id');
        $this->db->where('library_position_shelves.rack_id', $rack_id);
        $this->db->order_by('library_position_shelves.shelf_name');
        $query = $this->db->get();
        return $query->result_array();
    }
}
