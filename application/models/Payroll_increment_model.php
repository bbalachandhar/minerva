<?php

/**
 * Payroll Increment Model
 * Handles salary increment tracking and operations
 * 
 * @category Model
 * @version 1.0
 */

class Payroll_increment_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add new salary increment record
     * 
     * @param array $data Increment data
     * @return int Insert ID
     */
    public function addIncrement($data)
    {
        $this->db->trans_start();
        
        $insert_data = array(
            'staff_id' => $data['staff_id'],
            'effective_date' => $data['effective_date'],
            'increment_amount' => isset($data['increment_amount']) ? $data['increment_amount'] : NULL,
            'increment_percentage' => isset($data['increment_percentage']) ? $data['increment_percentage'] : NULL,
            'increment_type' => isset($data['increment_type']) ? $data['increment_type'] : 'Fixed',
            'merge_with' => isset($data['merge_with']) ? $data['merge_with'] : 'basic',
            'is_recurring' => isset($data['is_recurring']) ? $data['is_recurring'] : 1,
            'approval_status' => 'Pending',
            'remarks' => isset($data['remarks']) ? $data['remarks'] : NULL,
        );
        
        $this->db->insert('staff_increment_history', $insert_data);
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === false) {
            return false;
        }
        
        return $insert_id;
    }

    /**
     * Update salary increment record
     * 
     * @param int $increment_id Increment ID
     * @param array $data Updated data
     * @return bool
     */
    public function updateIncrement($increment_id, $data)
    {
        $this->db->trans_start();
        
        $update_data = array();
        
        if (isset($data['increment_amount'])) {
            $update_data['increment_amount'] = $data['increment_amount'];
        }
        if (isset($data['increment_percentage'])) {
            $update_data['increment_percentage'] = $data['increment_percentage'];
        }
        if (isset($data['effective_date'])) {
            $update_data['effective_date'] = $data['effective_date'];
        }
        if (isset($data['merge_with'])) {
            $update_data['merge_with'] = $data['merge_with'];
        }
        if (isset($data['remarks'])) {
            $update_data['remarks'] = $data['remarks'];
        }
        
        $this->db->where('id', $increment_id);
        $this->db->update('staff_increment_history', $update_data);
        
        $this->db->trans_complete();
        
        return ($this->db->trans_status() !== false);
    }

    /**
     * Approve salary increment
     * 
     * @param int $increment_id Increment ID
     * @param int $approved_by Staff ID of approver
     * @return bool
     */
    public function approveIncrement($increment_id, $approved_by)
    {
        $update_data = array(
            'approval_status' => 'Approved',
            'approved_by' => $approved_by,
            'approved_date' => date('Y-m-d H:i:s'),
        );
        
        $this->db->where('id', $increment_id);
        $this->db->update('staff_increment_history', $update_data);
        
        return ($this->db->affected_rows() > 0);
    }

    /**
     * Reject salary increment
     * 
     * @param int $increment_id Increment ID
     * @return bool
     */
    public function rejectIncrement($increment_id)
    {
        $update_data = array(
            'approval_status' => 'Rejected',
        );
        
        $this->db->where('id', $increment_id);
        $this->db->update('staff_increment_history', $update_data);
        
        return ($this->db->affected_rows() > 0);
    }

    /**
     * Get increment by ID
     * 
     * @param int $increment_id Increment ID
     * @return array|null
     */
    public function getIncrementById($increment_id)
    {
        $query = $this->db->where('id', $increment_id)->get('staff_increment_history');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Get all increments for a staff member
     * 
     * @param int $staff_id Staff ID
     * @param string $status Filter by status (Pending, Approved, Rejected, or blank for all)
     * @return array
     */
    public function getStaffIncrements($staff_id, $status = '')
    {
        $this->db->select('*')->from('staff_increment_history')->where('staff_id', $staff_id);
        
        if (!empty($status)) {
            $this->db->where('approval_status', $status);
        }
        
        $this->db->order_by('effective_date', 'DESC');
        $query = $this->db->get();
        
        return $query->result_array();
    }

    /**
     * Get approved increment for a specific month/year
     * 
     * @param int $staff_id Staff ID
     * @param int $month Month number (1-12)
     * @param int $year Year
     * @return array|null
     */
    public function getApprovedIncrementForMonth($staff_id, $month, $year)
    {
        // Get the month range
        $first_day = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $last_day = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year));
        
        $query = $this->db
            ->where('staff_id', $staff_id)
            ->where('approval_status', 'Approved')
            ->where('effective_date >=', $first_day)
            ->where('effective_date <=', $last_day)
            ->get('staff_increment_history');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Check if staff has approved increment effective on specific date
     * 
     * @param int $staff_id Staff ID
     * @param string $date Date in Y-m-d format
     * @return array|null
     */
    public function checkIncrementEffectiveDate($staff_id, $date)
    {
        $query = $this->db
            ->where('staff_id', $staff_id)
            ->where('approval_status', 'Approved')
            ->where('effective_date', $date)
            ->get('staff_increment_history');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Get latest approved increment for a staff member
     * 
     * @param int $staff_id Staff ID
     * @return array|null
     */
    public function getLatestApprovedIncrement($staff_id)
    {
        $query = $this->db
            ->where('staff_id', $staff_id)
            ->where('approval_status', 'Approved')
            ->where('effective_date <=', date('Y-m-d'))
            ->order_by('effective_date', 'DESC')
            ->limit(1)
            ->get('staff_increment_history');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Calculate increment amount based on type
     * 
     * @param array $increment Increment record
     * @param float $current_basic Current basic salary
     * @return float Increment amount
     */
    public function calculateIncrementAmount($increment, $current_basic = 0)
    {
        if ($increment['increment_type'] === 'Fixed') {
            return (float) $increment['increment_amount'];
        } elseif ($increment['increment_type'] === 'Percentage') {
            return round($current_basic * ($increment['increment_percentage'] / 100), 2);
        }
        
        return 0;
    }

    /**
     * Delete salary increment record
     * 
     * @param int $increment_id Increment ID
     * @return bool
     */
    public function deleteIncrement($increment_id)
    {
        // Only allow deletion of Pending records
        $increment = $this->getIncrementById($increment_id);
        
        if (!$increment || $increment['approval_status'] !== 'Pending') {
            return false;
        }
        
        $this->db->where('id', $increment_id);
        $this->db->delete('staff_increment_history');
        
        return ($this->db->affected_rows() > 0);
    }

    /**
     * Get pending increments for approval
     * 
     * @return array
     */
    public function getPendingIncrements()
    {
        $query = $this->db
            ->select('shi.*, s.name, s.employee_id')
            ->from('staff_increment_history shi')
            ->join('staff s', 's.id = shi.staff_id', 'inner')
            ->where('shi.approval_status', 'Pending')
            ->order_by('shi.created_at', 'ASC')
            ->get();
        
        return $query->result_array();
    }

    /**
     * Get increment history for a staff member with details
     * 
     * @param int $staff_id Staff ID
     * @return array
     */
    public function getIncrementHistory($staff_id)
    {
        $query = $this->db
            ->select('shi.*, ab.name as approved_by_name, ab.employee_id as approved_by_emp_id')
            ->from('staff_increment_history shi')
            ->join('staff ab', 'ab.id = shi.approved_by', 'left')
            ->where('shi.staff_id', $staff_id)
            ->order_by('shi.effective_date', 'DESC')
            ->get();
        
        return $query->result_array();
    }

    /**
     * Check if staff already has increment or bonus in the same month
     * Constraint: Only one increment OR bonus per staff per month
     * 
     * @param int $staff_id Staff ID
     * @param string $effective_date Date string (YYYY-MM-DD)
     * @return array|false Existing record if found, false if none
     */
    public function checkExistingForMonth($staff_id, $effective_date)
    {
        // Extract month and year from effective_date
        $month = date('m', strtotime($effective_date));
        $year = date('Y', strtotime($effective_date));
        
        $query = $this->db
            ->select('*')
            ->from('staff_increment_history')
            ->where('staff_id', $staff_id)
            ->where('MONTH(effective_date)', $month)
            ->where('YEAR(effective_date)', $year)
            ->where('approval_status !=', 'Rejected')
            ->get();
        
        $result = $query->result_array();
        return !empty($result) ? $result[0] : false;
    }

}

/* End of file Payroll_increment_model.php */
