<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cbse_category_model extends MY_Model {


    protected $current_session;
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }
    
    public function category_exists($str) {

        $class = $this->security->xss_clean($str);
        $res = $this->check_data_exists($class);

        if ($res) {
            $pre_category_id = $this->input->post('pre_category_id');
            if (isset($pre_category_id)) {
                if ($res->id == $pre_category_id) {
                    return true;
                }
            }
            $this->form_validation->set_message('category_exists', 'Record already exists');
            return false;
        } else {
            return true;
        }
    }

   public function check_data_exists($data) {
        $this->db->where('name', $data);
        $query = $this->db->get('cbse_category');
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }
   
    public function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && !empty($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('cbse_category', $data);
            $message = UPDATE_RECORD_CONSTANT . " On cbse_category id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $insert_id=$data['id'];
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
            $this->db->insert('cbse_category', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On  cbse_category  id " . $insert_id;
            $action = "Insert";
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
           
        }
        return $insert_id;
    }

   
    public function get($id = null) {
        $this->db->select()->from('cbse_category');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

   
    public function remove($id) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('cbse_category');
        $message = DELETE_RECORD_CONSTANT . " On cbse_category id " . $id;
        $action = "Delete";
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
 
   
}