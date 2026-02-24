<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Smsconfig_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($id = null) {
        $this->db->select()->from('sms_config');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result();
        }
    }

    public function changeStatus($type) {
        $data = array('is_active' => 'disabled');
        // When enabling one configuration we only want to disable others in the same group
        // SMS configs and WhatsApp configs are stored in the same table but should not
        // affect each other. We treat any type starting with 'whatsapp' as whatsapp group.
        if (strpos($type, 'whatsapp') === 0) {
            // disable other whatsapp configs only
            $this->db->where('type LIKE', 'whatsapp%');
            $this->db->where('type !=', $type);
        } else {
            // disable everything except whatsapp entries and the current type
            $this->db->where('type NOT LIKE', 'whatsapp%');
            $this->db->where('type !=', $type);
        }
        $this->db->update('sms_config', $data);
    }

    public function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('type', $data['type']);
        $q = $this->db->get('sms_config');

        if ($q->num_rows() > 0) {
            $this->db->where('type', $data['type']);
            $this->db->update('sms_config', $data);
            $message = UPDATE_RECORD_CONSTANT . " On sms config id " . $data['type'];
            $action = "Update";
            $record_id = $data['type'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('sms_config', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On sms config id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
        }
        if ($data['is_active'] == "enabled") {
            $this->changeStatus($data['type']);
        }

        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    public function getActiveSMS() {
        $this->db->select()->from('sms_config');
        $this->db->where('is_active', 'enabled');
        // exclude any whatsapp configuration entries so they are handled separately
        $this->db->where('type NOT LIKE', 'whatsapp%');
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Retrieve the currently enabled whatsapp configuration (first enabled whatsapp entry)
     */
    public function getActiveWhatsapp() {
        $this->db->select()->from('sms_config');
        $this->db->where('is_active', 'enabled');
        $this->db->like('type', 'whatsapp', 'after');
        $query = $this->db->get();
        return $query->row();
    }

}
