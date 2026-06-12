<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vehicle_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $this->db->select()->from('vehicles');
        if ($id != null) {
            $this->db->where('vehicles.id', $id);
        } else {
            $this->db->order_by('vehicles.id','desc');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
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
        $this->db->delete('vehicles');
        
        $this->db->where('vehicle_id', $id);
        $this->db->delete('vehicle_routes');
        
        $message   = DELETE_RECORD_CONSTANT . " On vehicles id " . $id;
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
            $this->db->update('vehicles', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  vehicles id " . $data['id'];
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
            $this->db->insert('vehicles', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On vehicles id " . $insert_id;
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

    public function bulkInsert($rows)
    {
        $inserted = 0; $skipped = [];
        $dateFields = [
            'fc_validity_start', 'fc_validity_end',
            'insurance_start',   'insurance_end',
            'permit_expiry_start','permit_expiry_end',
            'road_tax_start',    'road_tax_end',
            'pollution_cert_start','pollution_cert_end',
            'green_tax_start',   'green_tax_end',
        ];
        foreach ($rows as $row) {
            $vehicle_no = trim($row['vehicle_no'] ?? '');
            if ($vehicle_no === '') { $skipped[] = '(empty vehicle_no)'; continue; }
            $this->db->where('vehicle_no', $vehicle_no);
            if ($this->db->count_all_results('vehicles') > 0) { $skipped[] = $vehicle_no . ' (duplicate)'; continue; }
            $data = [
                'vehicle_no'           => $vehicle_no,
                'vehicle_model'        => trim($row['vehicle_model'] ?? ''),
                'manufacture_year'     => trim($row['manufacture_year'] ?? ''),
                'registration_number'  => trim($row['registration_number'] ?? ''),
                'chasis_number'        => trim($row['chasis_number'] ?? ''),
                'engine_number'        => trim($row['engine_number'] ?? ''),
                'max_seating_capacity' => trim($row['max_seating_capacity'] ?? ''),
                'driver_name'          => trim($row['driver_name'] ?? ''),
                'driver_licence'       => trim($row['driver_licence'] ?? ''),
                'driver_contact'       => trim($row['driver_contact'] ?? ''),
                'note'                 => trim($row['note'] ?? ''),
            ];
            foreach ($dateFields as $f) {
                $raw = trim($row[$f] ?? '');
                $ts  = $raw !== '' ? $this->customlib->datetostrtotime($raw) : null;
                $data[$f] = $ts ? date('Y-m-d', $ts) : null;
            }
            $this->db->insert('vehicles', $data);
            $inserted++;
        }
        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    public function vehicleListByarray($array)
    {
        $this->db->select('*');
        $this->db->from('vehicles');
        $this->db->where_in('vehicles.id', $array);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get vehicles whose any end-date field falls exactly $days_ahead days from today.
     * Returns an array of arrays, each including a 'expiry_label' and the vehicle fields.
     */
    public function getExpiringVehicles($days_ahead)
    {
        $target = date('Y-m-d', strtotime("+{$days_ahead} days"));
        $end_fields = [
            'fc_validity_end'    => 'FC Validity',
            'insurance_end'      => 'Insurance',
            'permit_expiry_end'  => 'Permit Expiry',
            'road_tax_end'       => 'Road Tax',
            'pollution_cert_end' => 'Pollution Certificate',
            'green_tax_end'      => 'Green Tax',
        ];

        $results = [];
        foreach ($end_fields as $field => $label) {
            $this->db->select('*, "' . $this->db->escape_str($label) . '" AS expiry_label, "' . $this->db->escape_str($field) . '" AS expiry_field');
            $this->db->from('vehicles');
            $this->db->where($field, $target);
            $rows = $this->db->get()->result_array();
            $results = array_merge($results, $rows);
        }
        return $results;
    }

    /** Get the 3 configured notification assignees joined with staff details */
    public function getAssignees()
    {
        $this->db->select('vea.slot, vea.staff_id, s.name, s.email, s.contact_no');
        $this->db->from('vehicle_expiry_assignees vea');
        $this->db->join('staff s', 's.id = vea.staff_id', 'inner');
        $this->db->order_by('vea.slot', 'ASC');
        return $this->db->get()->result_array();
    }

    /** Get raw assignee staff_ids keyed by slot (for form pre-population) */
    public function getAssigneesBySlot()
    {
        $rows = $this->db->get('vehicle_expiry_assignees')->result_array();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['slot']] = $row['staff_id'];
        }
        return $map;
    }

    /** Upsert the 3 assignee slots. $data = ['1'=>staff_id, '2'=>staff_id, '3'=>staff_id] */
    public function saveAssignees($data)
    {
        foreach ([1, 2, 3] as $slot) {
            $staff_id = isset($data[$slot]) ? (int)$data[$slot] : null;
            $existing = $this->db->where('slot', $slot)->get('vehicle_expiry_assignees')->row();
            if ($staff_id) {
                if ($existing) {
                    $this->db->where('slot', $slot)->update('vehicle_expiry_assignees', ['staff_id' => $staff_id]);
                } else {
                    $this->db->insert('vehicle_expiry_assignees', ['staff_id' => $staff_id, 'slot' => $slot]);
                }
            } else {
                if ($existing) {
                    $this->db->where('slot', $slot)->delete('vehicle_expiry_assignees');
                }
            }
        }
    }

    /** Get a single value from vehicle_notification_config by key */
    public function getNotificationConfig($key)
    {
        $row = $this->db->get('vehicle_notification_config')->row_array();
        return ($row && array_key_exists($key, $row)) ? $row[$key] : null;
    }

    /** Save a key-value pair into vehicle_notification_config */
    public function saveNotificationConfig($key, $value)
    {
        $exists = $this->db->get('vehicle_notification_config')->num_rows() > 0;
        if ($exists) {
            $this->db->where('id', 1)->update('vehicle_notification_config', [$key => $value]);
        } else {
            $this->db->insert('vehicle_notification_config', ['id' => 1, $key => $value]);
        }
    }

}
