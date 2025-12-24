<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Leavetypes_model extends MY_model
{

    public function __construct()
    {
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
    }

    public function addLeaveType($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('leave_types', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On leave types id " . $data['id'];
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
            $this->db->insert('leave_types', $data);
            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On leave types id " . $id;
            $action    = "Insert";
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
            return $id;
        }
    }

    public function getLeaveType()
    {
        $query = $this->db->get('leave_types');
        return $query->result_array();
    }

    public function deleteLeaveType($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('leave_types');
        $message   = DELETE_RECORD_CONSTANT . " On subjects id " . $id;
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

    public function valid_leave_type($str)
    {
        $type = $this->input->post('type');
        $id   = $this->input->post('leavetypeid');
        if (!isset($id)) {
            $id = 0;
        }
        if ($this->check_data_exists($type, $id)) {
            $this->form_validation->set_message('check_exists', 'Record already exists');
            return false;
        } else {
            return true;
        }
    }

    public function check_data_exists($name, $id)
    {
        if ($id != 0) {
            $data  = array('id != ' => $id, 'type' => $name);
            $query = $this->db->where($data)->get('leave_types');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        } else {

            $this->db->where('type', $name);
            $query = $this->db->get('leave_types');
            if ($query->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function update_staff_leave_details($staff_id, $leave_type_id, $days, $overwrite = false)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('leave_type_id', $leave_type_id);
        $q = $this->db->get('staff_leave_details');

        if ($q->num_rows() > 0) {
            if ($overwrite || $q->row()->alloted_leave == 0 || $q->row()->alloted_leave == null) {
                $this->db->where('staff_id', $staff_id);
                $this->db->where('leave_type_id', $leave_type_id);
                $this->db->update('staff_leave_details', array('alloted_leave' => $days));
            }
        } else {
            $this->db->insert('staff_leave_details', array('staff_id' => $staff_id, 'leave_type_id' => $leave_type_id, 'alloted_leave' => $days));
        }
    }
}
