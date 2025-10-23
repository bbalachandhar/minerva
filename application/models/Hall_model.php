<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hall_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // --- Hall CRUD Operations ---

    /**
     * Add a new hall
     * @param array $data
     * @return int Insert ID
     */
    public function add_hall($data)
    {
        $this->db->insert('halls', $data);
        return $this->db->insert_id();
    }

    /**
     * Get a hall by ID
     * @param int $id
     * @return object/array Hall details
     */
    public function get_hall($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('halls');
        return $query->row(); // or $query->row_array()
    }

    /**
     * Get all halls
     * @return array List of all halls
     */
    public function get_all_halls()
    {
        $query = $this->db->get('halls');
        return $query->result(); // or $query->result_array()
    }

    /**
     * Update a hall
     * @param int $id
     * @param array $data
     * @return bool True on success, false on failure
     */
    public function update_hall($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('halls', $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete a hall
     * @param int $id
     * @return bool True on success, false on failure
     */
    public function delete_hall($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('halls');
        return $this->db->affected_rows() > 0;
    }

    // --- Hall Booking CRUD Operations ---

    /**
     * Add a new hall booking
     * @param array $data
     * @return int Insert ID
     */
    public function add_booking($data)
    {
        $this->db->insert('hall_bookings', $data);
        return $this->db->insert_id();
    }

    /**
     * Get a booking by ID
     * @param int $id
     * @return object/array Booking details
     */
    public function get_booking($id)
    {
        $this->db->select('hb.*, h.name as hall_name, h.location, s.name as booked_by_staff_name, s.employee_id');
        $this->db->from('hall_bookings hb');
        $this->db->join('halls h', 'hb.hall_id = h.id');
        $this->db->join('staff s', 'hb.booked_by_user_id = s.id');
        $this->db->where('hb.id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get all bookings (with hall and staff details) for admin view
     * @return array List of all bookings
     */
    public function get_all_bookings()
    {
        $this->db->select('hb.*, h.name as hall_name, h.location, s.name as booked_by_staff_name, s.employee_id');
        $this->db->from('hall_bookings hb');
        $this->db->join('halls h', 'hb.hall_id = h.id');
        $this->db->join('staff s', 'hb.booked_by_user_id = s.id');
        $this->db->order_by('hb.start_time', 'desc');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get bookings made by a specific user
     * @param int $user_id
     * @return array List of bookings by the user
     */
    public function get_user_bookings($user_id)
    {
        $this->db->select('hb.*, h.name as hall_name, h.location');
        $this->db->from('hall_bookings hb');
        $this->db->join('halls h', 'hb.hall_id = h.id');
        $this->db->where('hb.booked_by_user_id', $user_id);
        $this->db->order_by('hb.start_time', 'desc');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get bookings for a specific hall
     * @param int $hall_id
     * @return array List of bookings for the hall
     */
    public function get_hall_bookings($hall_id)
    {
        $this->db->select('hb.*, h.name as hall_name, h.location, s.name as booked_by_staff_name, s.employee_id');
        $this->db->from('hall_bookings hb');
        $this->db->join('halls h', 'hb.hall_id = h.id');
        $this->db->join('staff s', 'hb.booked_by_user_id = s.id');
        $this->db->where('hb.hall_id', $hall_id);
        $this->db->order_by('hb.start_time', 'asc');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Update a hall booking
     * @param int $id
     * @param array $data
     * @return bool True on success, false on failure
     */
    public function update_booking($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('hall_bookings', $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete a hall booking
     * @param int $id
     * @return bool True on success, false on failure
     */
    public function delete_booking($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('hall_bookings');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Check hall availability for a given time slot
     * @param int $hall_id
     * @param string $start_time (DATETIME format YYYY-MM-DD HH:MM:SS)
     * @param string $end_time (DATETIME format YYYY-MM-DD HH:MM:SS)
     * @param int $exclude_booking_id (Optional) Exclude a specific booking ID during update checks
     * @return bool True if available, false otherwise
     */
    public function check_availability($hall_id, $start_time, $end_time, $exclude_booking_id = null)
    {
        $this->db->where('hall_id', $hall_id);
        $this->db->where_in('status', ['pending', 'approved']); // Check against pending and approved bookings

        // Check for overlapping time slots
        $this->db->group_start();
        $this->db->where('start_time <', $end_time);
        $this->db->where('end_time >', $start_time);
        $this->db->group_end();

        if ($exclude_booking_id) {
            $this->db->where('id !=', $exclude_booking_id);
        }

        $query = $this->db->get('hall_bookings');
        return $query->num_rows() == 0; // If no rows, it's available
    }

    // --- Hall Approval Config CRUD Operations ---

    /**
     * Add a new hall approval configuration
     * @param array $data
     * @return int Insert ID
     */
    public function add_approval_config($data)
    {
        $this->db->insert('hall_approval_config', $data);
        return $this->db->insert_id();
    }

    /**
     * Get an approval configuration by ID
     * @param int $id
     * @return object/array Approval config details
     */
    public function get_approval_config($id)
    {
        $this->db->select('hac.*, h.name as hall_name, s.name as staff_name, s.employee_id, r.name as role_name');
        $this->db->from('hall_approval_config hac');
        $this->db->join('halls h', 'hac.hall_id = h.id', 'left');
        $this->db->join('staff s', 'hac.staff_id = s.id', 'left');
        $this->db->join('roles r', 'hac.role_id = r.id', 'left');
        $this->db->where('hac.id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    /**
     * Get all approval configurations (with staff/role names)
     * @return array List of all approval configurations
     */
    public function get_all_approval_configs($start = 0, $length = 100, $order = array(), $search_value = '')
    {
        $this->_get_approval_configs_query($search_value);

        if (!empty($order)) {
            foreach ($order as $o) {
                $this->db->order_by($this->db->escape_str($o['column']), $this->db->escape_str($o['dir']));
            }
        }

        $this->db->limit($length, $start);
        $query = $this->db->get();
        return $query->result();
    }

    public function count_all_approval_configs()
    {
        $this->db->from('hall_approval_config hac');
        return $this->db->count_all_results();
    }

    public function count_filtered_approval_configs($search_value)
    {
        $this->_get_approval_configs_query($search_value);
        $query = $this->db->get();
        return $query->num_rows();
    }

    private function _get_approval_configs_query($search_value = '')
    {
        $this->db->select('hac.*, h.name as hall_name, s.name as staff_name, s.employee_id, r.name as role_name');
        $this->db->from('hall_approval_config hac');
        $this->db->join('halls h', 'hac.hall_id = h.id', 'left');
        $this->db->join('staff s', 'hac.staff_id = s.id', 'left');
        $this->db->join('roles r', 'hac.role_id = r.id', 'left');

        if (!empty($search_value)) {
            $this->db->group_start();
            $this->db->like('h.name', $search_value);
            $this->db->or_like('s.name', $search_value);
            $this->db->or_like('s.employee_id', $search_value);
            $this->db->or_like('r.name', $search_value);
            $this->db->group_end();
        }
    }

    /**
     * Update a hall approval configuration
     * @param int $id
     * @param array $data
     * @return bool True on success, false on failure
     */
    public function update_approval_config($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('hall_approval_config', $data);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete a hall approval configuration
     * @param int $id
     * @return bool True on success, false on failure
     */
    public function delete_approval_config($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('hall_approval_config');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get approvers for a specific hall or all halls
     * @param int $hall_id Optional, if null, gets all global approvers
     * @return array List of approvers (staff or roles) with their details
     */
    public function get_approvers($hall_id = null)
    {
        $this->db->select('hac.*, s.name as staff_name, s.employee_id, r.name as role_name');
        $this->db->from('hall_approval_config hac');
        $this->db->join('staff s', 'hac.staff_id = s.id', 'left');
        $this->db->join('roles r', 'hac.role_id = r.id', 'left');
        $this->db->where('hac.can_approve', 1);

        $this->db->group_start();
        $this->db->where('hac.hall_id', $hall_id);
        $this->db->or_where('hac.hall_id IS NULL');
        $this->db->group_end();

        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get all bookings made by a specific user (staff)
     * @param int $booked_by_user_id
     * @return array List of bookings by the user
     */
    public function get_bookings_by_user($booked_by_user_id)
    {
        $this->db->select('hb.*, h.name as hall_name, h.location');
        $this->db->from('hall_bookings hb');
        $this->db->join('halls h', 'hb.hall_id = h.id');
        $this->db->where('hb.booked_by_user_id', $booked_by_user_id);
        $this->db->order_by('hb.start_time', 'desc');
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Get pending bookings for a specific approver (staff or role)
     * @param int $approver_staff_id Optional, staff ID of the approver
     * @param int $approver_role_id Optional, role ID of the approver
     * @return array List of pending bookings that the approver can see
     */
    public function get_pending_bookings_for_approver($approver_staff_id = null, $approver_role_id = null)
    {
        $this->db->select('hb.*, h.name as hall_name, h.location, s.name as booked_by_staff_name, s.employee_id');
        $this->db->from('hall_bookings hb');
        $this->db->join('halls h', 'hb.hall_id = h.id');
        $this->db->join('staff s', 'hb.booked_by_user_id = s.id');
        $this->db->where('hb.status', 'pending');

        $this->db->group_start(); // Start group for hall-specific or global approval

        // Check for hall-specific approvals
        $this->db->where_in('hb.hall_id', 
            '(SELECT hac.hall_id FROM hall_approval_config hac WHERE hac.can_approve = 1 AND (hac.staff_id = ' . $this->db->escape($approver_staff_id) . ' OR hac.role_id = ' . $this->db->escape($approver_role_id) . '))', FALSE
        );

        // Check for global approvals (hall_id IS NULL)
        $this->db->or_where_in('hb.hall_id', 
            '(SELECT h.id FROM halls h JOIN hall_approval_config hac ON hac.hall_id IS NULL WHERE hac.can_approve = 1 AND (hac.staff_id = ' . $this->db->escape($approver_staff_id) . ' OR hac.role_id = ' . $this->db->escape($approver_role_id) . '))', FALSE
        );

        $this->db->group_end(); // End group for hall-specific or global approval

        $this->db->order_by('hb.start_time', 'asc');
        $query = $this->db->get();
        return $query->result();
    }

}