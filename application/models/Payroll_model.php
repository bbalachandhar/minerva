<?php

class Payroll_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
    }

    public function searchEmployee($month, $year, $emp_name, $role)
    {
        $condition = "";
        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);       
            $superadmin_visible = $this->customlib->superadmin_visible(); 
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {                 
                $condition = " and roles.id != 7";
            } 
        }
        
        $date_month = date("m", strtotime($year));
        if (!empty($role) && !empty($emp_name)) {

            $query = $this->db->query("select staff_payslip.status,
        IFNULL(staff_payslip.id, 0) as payslip_id ,staff.* ,roles.name as user_type ,staff_designation.designation as designation,department.department_name as department from staff left join staff_payslip on staff.id = staff_payslip.staff_id and month = " . $this->db->escape($month) . " and year = " . $this->db->escape($year) . " left join department on department.id = staff.department left join staff_designation on staff_designation.id = staff.designation left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id where roles.name = " . $this->db->escape($role) . " and name = " . $this->db->escape($emp_name) . " and staff.is_active = 1 $condition");
        } else if (!empty($role)) {

            $query = $this->db->query("select staff_payslip.status,
        IFNULL(staff_payslip.id, 0) as payslip_id ,staff.*,staff_designation.designation as designation,department.department_name as department ,roles.name as user_type from staff left join staff_payslip on staff.id = staff_payslip.staff_id and month = " . $this->db->escape($month) . " and year = " . $this->db->escape($year) . " left join department on department.id = staff.department left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_designation on staff_designation.id = staff.designation where roles.name = " . $this->db->escape($role) . " and staff.is_active = 1 $condition");
        } else {

            $query = $this->db->query("select staff_payslip.status,
        IFNULL(staff_payslip.id, 0) as payslip_id ,staff.* ,roles.name as user_type ,staff_designation.designation as designation,department.department_name as department  from staff left join staff_payslip on staff.id = staff_payslip.staff_id and month = " . $this->db->escape($month) . " and year = " . $this->db->escape($year) . " left join department on department.id = staff.department left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_designation on staff_designation.id = staff.designation where staff.is_active = 1 $condition");
        }

        return $query->result_array();
    }

     public function update_allowance($insert_data, $update_data, $delete_data,$payslipid,$type)
    {
        $this->db->trans_begin();
        
        if (!empty($delete_data)) {
            $this->db->where('cal_type', $type);
            $this->db->where('payslip_id', $payslipid);
            $this->db->where_not_in('id', $delete_data);
            $this->db->delete('payslip_allowance');
        }

        if (!empty($insert_data)) {
            $this->db->insert_batch('payslip_allowance', $insert_data);
        }
        if (!empty($update_data)) {
            $this->db->update_batch('payslip_allowance', $update_data, 'id');
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

   public function createPayslip($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id']) && $data['id'] != '') {
            $this->db->where('id', $data['id']);
            $this->db->update('staff_payslip', $data);
            $message = UPDATE_RECORD_CONSTANT . " On Staff Payslip id " . $data['id'];
            $action = "Update";
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
                return $record_id;
            }
        } else {
            $this->db->insert('staff_payslip', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On Staff Payslip id " . $insert_id;
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
            return $insert_id;
        }
    }

    public function checkPayslip($month, $year, $staff_id)
    {

        $query = $this->db->where(array('month' => $month, 'year' => $year, 'staff_id' => $staff_id))->get("staff_payslip");

        if ($query->num_rows() > 0) {
            return false;
        } else {

            return true;
        }
    }

    public function add_allowance($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('payslip_allowance', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On payslip allowance id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('payslip_allowance', $data);
            $id = $this->db->insert_id();

            $message   = INSERT_RECORD_CONSTANT . " On payslip allowance id " . $id;
            $action    = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
        }

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

    public function searchPaylist($name, $month, $year)
    {
        $query = $this->db->select('staff.*,staff_designation.designation as desg,department.department_name as department')->where(array('staff.name' => $name, 'staff_payslip.month' => $month, 'staff_payslip.year' => $year))->join("staff_payslip", "staff.id = staff_payslip.staff_id")->join("staff_designation", "staff.designation = staff_designation.id")->join("department", "staff.department = department.id")->get("staff");

        return $query->result_array();
    }

    public function count_attendance($month, $year, $staff_id, $attendance_type = 1)
    {
        $date_month = date("m", strtotime($month));
        $query      = $this->db->select('count(*) as att')->where(array('staff_id' => $staff_id, 'month(date)' => $month, 'year(date)' => $year, 'staff_attendance_type_id' => $attendance_type))->get("staff_attendance");
        return $query->result_array();
    }

    public function count_attendance_obj($month, $year, $staff_id, $attendance_type = 1)
    {
        $session_level_types = [2, 5, 6, 7]; // FHL, FHP, SHL, SHP

        if (in_array($attendance_type, $session_level_types)) {
            // New logic to count from JSON
            $this->db->select('session_attendance_data');
            $this->db->from('staff_attendance');
            $this->db->where('staff_id', $staff_id);
            $this->db->where('month(date)', $month);
            $this->db->where('year(date)', $year);
            $query = $this->db->get();
            $results = $query->result_array();

            $count = 0;
            if (!empty($results)) {
                foreach ($results as $row) {
                    if (!empty($row['session_attendance_data'])) {
                        $session_data = json_decode($row['session_attendance_data'], true);
                        if ($session_data) {
                            if ((isset($session_data['morning_session']) && $session_data['morning_session'] == $attendance_type)) {
                                $count++;
                            }
                            if ((isset($session_data['afternoon_session']) && $session_data['afternoon_session'] == $attendance_type)) {
                                $count++;
                            }
                        }
                    }
                }
            }
            return $count;
        } else {
            $date_like = $year . '-' . sprintf("%02d", $month) . '-%';
            $this->db->select('count(*) as attendence');
            $this->db->from('staff_attendance');
            $this->db->where('staff_id', $staff_id);
            $this->db->where('date LIKE', $date_like);
            $this->db->where('staff_attendance_type_id', $attendance_type);
            $query = $this->db->get();
            return $query->row()->attendence;
        }
    }

    public function count_attendance_range($start_date, $end_date, $staff_id, $attendance_type = 1)
    {
        $session_level_types = [2, 5, 6, 7]; // FHL, FHP, SHL, SHP

        if (in_array($attendance_type, $session_level_types)) {
            $this->db->select('session_attendance_data');
            $this->db->from('staff_attendance');
            $this->db->where('staff_id', $staff_id);
            $this->db->where('date >=', $start_date);
            $this->db->where('date <=', $end_date);
            $query = $this->db->get();
            $results = $query->result_array();

            $count = 0;
            if (!empty($results)) {
                foreach ($results as $row) {
                    if (!empty($row['session_attendance_data'])) {
                        $session_data = json_decode($row['session_attendance_data'], true);
                        if ($session_data) {
                            if ((isset($session_data['morning_session']) && $session_data['morning_session'] == $attendance_type)) {
                                $count++;
                            }
                            if ((isset($session_data['afternoon_session']) && $session_data['afternoon_session'] == $attendance_type)) {
                                $count++;
                            }
                        }
                    }
                }
            }
            return $count;
        }

        $this->db->select('count(*) as attendence');
        $this->db->from('staff_attendance');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('date >=', $start_date);
        $this->db->where('date <=', $end_date);
        $this->db->where('staff_attendance_type_id', $attendance_type);
        $query = $this->db->get();
        $row = $query->row();
        return $row ? (int) $row->attendence : 0;
    }

    public function updatePaymentStatus($status, $id)
    {
        $data = array('status' => $status);
        $this->db->where("id", $id)->update("staff_payslip", $data);
    }

    public function searchEmployeeById($id)
    {
        $query = $this->db->select('staff.*,roles.name as user_type ,staff_designation.designation,department.department_name as department')->join("staff_designation", "staff_designation.id = staff.designation", "left")->join("department", "department.id = staff.department", "left")->join("staff_roles", "staff_roles.staff_id = staff.id", "left")->join("roles", "staff_roles.role_id = roles.id", "left")->where("staff.id", $id)->get("staff");

        return $query->row_array();
    }

    public function searchPayment($id, $month, $year)
    {
        $query = $this->db->select('staff.name,staff.surname,staff.employee_id,staff.basic_salary,staff_payslip.*')->where(array('staff_payslip.month' => $month, 'staff_payslip.year' => $year, 'staff_payslip.staff_id' => $id))->join("staff_payslip", "staff.id = staff_payslip.staff_id")->get("staff");
        return $query->row_array();
    }

    public function paymentSuccess($data, $payslipid)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where("id", $payslipid)->update("staff_payslip", $data);
        $message   = UPDATE_RECORD_CONSTANT . " On staff payslip id " . $payslipid;
        $action    = "Update";
        $record_id = $payslipid;
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

    public function getPayslip($id)
    {
        $query = $this->db->select("staff.name,staff.surname,department.department_name as department,staff_designation.designation,staff.employee_id,staff_payslip.*")->join("staff", "staff.id = staff_payslip.staff_id")->join("staff_designation", "staff.designation = staff_designation.id", "left")->join("department", "staff.department = department.id", "left")->where("staff_payslip.id", $id)->get("staff_payslip");

        return $query->row_array();
    }

    public function getLastPayslip($staff_id)
    {
        $this->db->select("staff_payslip.*");
        $this->db->from('staff_payslip');
        $this->db->where('staff_id', $staff_id);
        $this->db->order_by('year', 'DESC');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $payslip_id = $query->row()->id;
            return $this->getPayslip($payslip_id);
        }
        return false;
    }

    public function getAllowance($id, $type = null)
    {
        if (!empty($type)) {

            $query = $this->db->select("id,allowance_type,amount,cal_type")->where(array('payslip_id' => $id, 'cal_type' => $type))->get("payslip_allowance");
        } else {

            $query = $this->db->select("id,allowance_type,amount,cal_type")->where("payslip_id", $id)->get("payslip_allowance");
        }

        return $query->result_array();
    }     

    public function getSalaryDetails($id)
    {
        $query = $this->db->select("net_salary, total_allowance as earnings, total_deduction as deduction, basic as basic_salary, tax, leave_deduction")
            ->where('staff_id', $id)
            ->where_in('status', ['paid', 'generated'])
            ->order_by('year', 'DESC')
            ->order_by('FIELD(month, "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December")', 'DESC', false)
            ->limit(1)
            ->get("staff_payslip");
        return $query->row_array();
    }

    public function getpayrollReport($month, $year, $role, $statuses = ['paid'])
    {
        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);  
            $superadmin_visible = $this->customlib->superadmin_visible(); 
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $this->db->where("roles.id !=", 7);                 
            } 
        }
        
        if ($role == "select" && $month != "") {
            $data = array('staff_payslip.month' => $month, 'staff_payslip.year' => $year);
        } else if ($role == "select" && $month == "") {

            $data = array('staff_payslip.year' => $year);
        } else if ($role != "select" && $month == "") {

            $data = array('staff_payslip.year' => $year, 'roles.name' => $role);
        } else {

            $data = array('staff_payslip.month' => $month, 'staff_payslip.year' => $year, 'roles.name' => $role);
        }
        $data['staff.is_active'] = 1;

        if (empty($statuses)) {
            $statuses = ['paid'];
        }

        $query = $this->db->select('staff.id,staff.employee_id,staff.name,roles.name as user_type,staff.surname,staff_designation.designation,department.department_name as department,staff_payslip.*')->join("staff_payslip", "staff_payslip.staff_id = staff.id", "inner")->join("staff_designation", "staff.designation = staff_designation.id", "left")->join("department", "staff.department = department.id", "left")->join("staff_roles", "staff_roles.staff_id = staff.id", "left")->join("roles", "staff_roles.role_id = roles.id", "left")->where($data)->where_in('staff_payslip.status', $statuses)->get("staff");

        return $query->result_array();
    }

    public function deletePayslip($payslipid)
    {
        $this->db->where("id", $payslipid)->delete("staff_payslip");
        $this->db->where("payslip_id", $payslipid)->delete("payslip_allowance");
    }

    public function deletePayslipAllowances($payslipid)
    {
        $this->db->where("payslip_id", $payslipid)->delete("payslip_allowance");
    }

    public function revertPayslipStatus($payslipid)
    {
        $data = array('status' => "generated");
        $this->db->where("id", $payslipid)->update("staff_payslip", $data);
    }

    public function getPayslipByStaffMonthYear($staff_id, $month, $year)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('month', $month);
        $this->db->where('year', $year);
        $this->db->order_by('id', 'DESC'); // Order by ID to get the most recent one consistently
        $query = $this->db->get('staff_payslip');
        if ($query->num_rows() > 0) { // Change condition to "> 0" to handle multiple existing duplicates
            return $query->row(); // Return the first one (most recent due to order_by)
        }
        return false;
    }

    public function payrollYearCount()
    {
        $query = $this->db->select("distinct(year) as year")->get("staff_payslip");
        return $query->result_array();
    }

    public function getbetweenpayrollReport($start_date, $end_date)
    {      
        
        $condition = "date_format(staff_payslip.payment_date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";       
       
        $this->db->select('staff.id,staff.employee_id,staff.name,roles.name as user_type,staff.surname,staff_designation.designation,department.department_name as department,staff_payslip.*');
        $this->db->join("staff_payslip", "staff_payslip.staff_id = staff.id", "inner");
        $this->db->join("staff_designation", "staff.designation = staff_designation.id", "left");
        $this->db->join("department", "staff.department = department.id", "left");
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");        
        $this->db->where($condition); 
        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);       
            
            $superadmin_rest = $this->customlib->superadmin_visible(); 
            if ($superadmin_rest == 'disabled' && $staffrole->id != 7) {
                $this->db->where("roles.id !=", 7)  ;          
            } 
        }
        
        $query = $this->db->get("staff");         
        return $query->result_array(); 
    }

    /**
     * Get staff available paid leaves (where is_lop = 0)
     * 
     * @param int $staff_id Staff ID
     * @return array Array of available paid leaves
     */
    public function getStaffPaidLeaves($staff_id)
    {
        $this->db->select('staff_leave_details.*, leave_types.type, leave_types.is_lop');
        $this->db->from('staff_leave_details');
        $this->db->join('leave_types', 'leave_types.id = staff_leave_details.leave_type_id');
        $this->db->where('staff_leave_details.staff_id', $staff_id);
        $this->db->where('leave_types.is_lop', 0); // Only paid leaves (not LOP type)
        $this->db->order_by('leave_types.type', 'ASC'); // Alphabetical order
        $query = $this->db->get();
        
        return $query->result_array();
    }

    /**
     * Get or create monthly leave balance for a staff member
     * If record doesn't exist, create from previous month's closing balance
     * 
     * @param int $staff_id Staff ID
     * @param int $leave_type_id Leave type ID
     * @param int $year Year (YYYY)
     * @param int $month Month (1-12)
     * @return array|null Monthly balance record
     */
    public function getOrCreateMonthlyBalance($staff_id, $leave_type_id, $year, $month)
    {
        // Check if record exists
        $this->db->where('staff_id', $staff_id);
        $this->db->where('leave_type_id', $leave_type_id);
        $this->db->where('year', $year);
        $this->db->where('month', $month);
        $query = $this->db->get('staff_monthly_leave_balance');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        // Record doesn't exist - create from previous month or staff_leave_details
        $opening_balance = 0;
        
        // Try to get previous month's closing balance
        $prev_month = $month - 1;
        $prev_year = $year;
        
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year = $year - 1;
        }
        
        $this->db->where('staff_id', $staff_id);
        $this->db->where('leave_type_id', $leave_type_id);
        $this->db->where('year', $prev_year);
        $this->db->where('month', $prev_month);
        $prev_query = $this->db->get('staff_monthly_leave_balance');
        
        if ($prev_query->num_rows() > 0) {
            $prev_balance = $prev_query->row_array();
            $opening_balance = $prev_balance['closing_balance'];
        } else {
            // No previous month - get from staff_leave_details
            $this->db->where('staff_id', $staff_id);
            $this->db->where('leave_type_id', $leave_type_id);
            $leave_query = $this->db->get('staff_leave_details');
            
            if ($leave_query->num_rows() > 0) {
                $leave_data = $leave_query->row_array();
                $opening_balance = floatval($leave_data['alloted_leave']);
            }
        }
        
        // Create new record
        $data = [
            'staff_id' => $staff_id,
            'leave_type_id' => $leave_type_id,
            'year' => $year,
            'month' => $month,
            'opening_balance' => $opening_balance,
            'closing_balance' => $opening_balance,
            'notes' => 'Auto-created on ' . date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('staff_monthly_leave_balance', $data);
        $data['id'] = $this->db->insert_id();
        
        return $data;
    }

    /**
     * Validate if month/year can be processed (not future month)
     * 
     * @param int $year Year
     * @param int $month Month
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateProcessingMonth($year, $month)
    {
        $current_year = intval(date('Y'));
        $current_month = intval(date('m'));
        
        if ($year > $current_year || ($year == $current_year && $month > $current_month)) {
            return [
                'valid' => false,
                'message' => 'Cannot process payroll for future months'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Month is valid for processing'
        ];
    }

    /**
     * Process LOP adjustment using monthly balance tracking
     * 
     * @param int $staff_id Staff ID  
     * @param float $lop_days LOP days
     * @param string $month Month (01-12)
     * @param string $year Year (YYYY)
     * @param int|null $payslip_id Payslip ID
     * @return array Adjustment result
     */
    public function processLOPWithMonthlyBalance($staff_id, $lop_days, $month, $year, $payslip_id = null)
    {
        $month_int = intval($month);
        $year_int = intval($year);
        
        // Validate month/year
        $validation = $this->validateProcessingMonth($year_int, $month_int);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['message'],
                'actual_lop_days' => $lop_days,
                'adjusted_lop_days' => 0,
                'net_lop_days' => $lop_days
            ];
        }
        
        if ($lop_days <= 0) {
            return [
                'success' => true,
                'actual_lop_days' => 0,
                'adjusted_lop_days' => 0,
                'net_lop_days' => 0,
                'adjustments' => []
            ];
        }
        
        // Get system setting
        $settings = $this->setting_model->getSetting();
        $auto_adjust = isset($settings->auto_adjust_lop_with_leaves) ? $settings->auto_adjust_lop_with_leaves : 0;
        
        // Get all paid leave types for this staff
        $paid_leaves = $this->getStaffPaidLeaves($staff_id);
        
        $remaining_lop = $lop_days;
        $adjustments = [];
        
        foreach ($paid_leaves as $leave) {
            if ($remaining_lop <= 0) {
                break;
            }
            
            // Get or create monthly balance
            $balance = $this->getOrCreateMonthlyBalance($staff_id, $leave['leave_type_id'], $year_int, $month_int);
            
            // Calculate available balance
            $available = $balance['opening_balance'] + $balance['earned_in_month'] 
                       - $balance['used_for_leave_application'] - $balance['other_deductions'];
            
            if ($available <= 0) {
                continue;
            }
            
            // Calculate adjustment
            $to_adjust = min($available, $remaining_lop);
            
            // Update monthly balance
            $new_used_lop = $balance['used_for_lop_adjustment'] + $to_adjust;
            $new_closing = $balance['opening_balance'] + $balance['earned_in_month'] 
                         - $new_used_lop - $balance['used_for_leave_application'] - $balance['other_deductions'];
            
            $update_data = [
                'used_for_lop_adjustment' => $new_used_lop,
                'closing_balance' => $new_closing,
                'last_processed_date' => date('Y-m-d H:i:s')
            ];
            
            if ($payslip_id) {
                $update_data['payslip_id'] = $payslip_id;
            }
            
            $this->db->where('id', $balance['id']);
            $this->db->update('staff_monthly_leave_balance', $update_data);
            
            // Log to audit table
            $this->logBalanceAudit($balance['id'], $staff_id, $leave['leave_type_id'], 'LOP_ADJUSTMENT', 
                                  $to_adjust, $available, $new_closing, $payslip_id, 'payslip', 
                                  'LOP adjustment for payroll');
            
            // Track adjustment
            $adjustments[] = [
                'leave_type_id' => $leave['leave_type_id'],
                'leave_type' => $leave['type'],
                'balance_before' => $available,
                'days_adjusted' => $to_adjust,
                'balance_after' => $new_closing
            ];
            
            $remaining_lop -= $to_adjust;
        }
        
        $total_adjusted = $lop_days - $remaining_lop;
        
        return [
            'success' => true,
            'actual_lop_days' => $lop_days,
            'adjusted_lop_days' => $total_adjusted,
            'net_lop_days' => $remaining_lop,
            'adjustments' => $adjustments
        ];
    }

    /**
     * Log balance change to audit table
     * 
     * @param int $balance_id Monthly balance ID
     * @param int $staff_id Staff ID
     * @param int $leave_type_id Leave type ID
     * @param string $action_type Action type
     * @param float $amount Amount
     * @param float $balance_before Balance before
     * @param float $balance_after Balance after
     * @param int|null $reference_id Reference ID
     * @param string|null $reference_type Reference type
     * @param string|null $reason Reason
     * @return bool Success
     */
    private function logBalanceAudit($balance_id, $staff_id, $leave_type_id, $action_type, $amount, 
                                    $balance_before, $balance_after, $reference_id = null, 
                                    $reference_type = null, $reason = null)
    {
        $data = [
            'balance_id' => $balance_id,
            'staff_id' => $staff_id,
            'leave_type_id' => $leave_type_id,
            'action_type' => $action_type,
            'amount' => $amount,
            'balance_before' => $balance_before,
            'balance_after' => $balance_after,
            'reference_id' => $reference_id,
            'reference_type' => $reference_type,
            'reason' => $reason,
            'performed_by' => $this->session->userdata('admin')['id'] ?? null
        ];
        
        return $this->db->insert('staff_leave_balance_audit', $data);
    }

    /**
     * Get approved leave days for a staff member in a specific month/year
     * 
     * @param int $staff_id Staff ID
     * @param string $month Month (01-12)
     * @param string $year Year (YYYY)
     * @return array Array of approved leaves by leave type
     */
    public function getApprovedLeavesByMonth($staff_id, $month, $year)
    {
        $month_num = intval($month);
        $year_num = intval($year);
        
        // Get approved leaves for the given month/year
        $this->db->select('staff_leave_request.*, leave_types.type, leave_types.is_lop');
        $this->db->from('staff_leave_request');
        $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id');
        $this->db->where('staff_leave_request.staff_id', $staff_id);
        $this->db->where('staff_leave_request.status', 'approve');
        $this->db->where('MONTH(staff_leave_request.leave_from)', $month_num);
        $this->db->where('YEAR(staff_leave_request.leave_from)', $year_num);
        $query = $this->db->get();
        
        return $query->result_array();
    }

    /**
     * Auto-adjust LOP with available paid leaves (where is_lop = 0)
     * 
     * @param int $staff_id Staff ID
     * @param float $lop_days Total LOP days to be adjusted
     * @param string $month Month (01-12)
     * @param string $year Year (YYYY)
     * @param int|null $payslip_id Optional payslip ID for logging
     * @return array Adjustment details [adjusted_lop, remaining_lop, adjustments]
     */
    public function autoAdjustLOPWithLeaves($staff_id, $lop_days, $month, $year, $payslip_id = null)
    {
        if ($lop_days <= 0) {
            return [
                'adjusted_lop' => 0,
                'remaining_lop' => 0,
                'adjustments' => []
            ];
        }

        // Get available paid leaves (where is_lop = 0)
        $paid_leaves = $this->getStaffPaidLeaves($staff_id);
        
        $remaining_lop = $lop_days;
        $adjustments = [];
        
        // Iterate through all paid leave types
        foreach ($paid_leaves as $leave) {
            if ($remaining_lop <= 0) {
                break; // No more LOP to adjust
            }
            
            $available_leave = floatval($leave['alloted_leave']);
            
            if ($available_leave <= 0) {
                continue; // No leaves available in this category
            }
            
            // Calculate how much to adjust from this leave type
            $to_adjust = min($available_leave, $remaining_lop);
            
            // Update staff_leave_details - deduct the adjusted amount
            $new_balance = $available_leave - $to_adjust;
            $this->db->where('id', $leave['id']);
            $this->db->update('staff_leave_details', ['alloted_leave' => $new_balance]);
            
            // Track adjustment
            $adjustments[] = [
                'leave_type_id' => $leave['leave_type_id'],
                'leave_type' => $leave['type'],
                'available_before' => $available_leave,
                'days_adjusted' => $to_adjust,
                'balance_after' => $new_balance
            ];
            
            $remaining_lop -= $to_adjust;
        }
        
        $total_adjusted = $lop_days - $remaining_lop;
        
        // Log the adjustment
        if ($payslip_id && $total_adjusted > 0) {
            $this->logLOPAdjustment($staff_id, $payslip_id, $month, $year, $lop_days, $remaining_lop, $adjustments);
        }
        
        return [
            'adjusted_lop' => $total_adjusted,
            'remaining_lop' => $remaining_lop,
            'adjustments' => $adjustments
        ];
    }

    /**
     * Adjust LOP based on approved leave applications
     * 
     * @param int $staff_id Staff ID
     * @param float $lop_days Total LOP days to be adjusted
     * @param string $month Month (01-12)
     * @param string $year Year (YYYY)
     * @param int|null $payslip_id Optional payslip ID for logging
     * @return array Adjustment details [adjusted_lop, remaining_lop, adjustments]
     */
    public function adjustLOPWithApprovedLeaves($staff_id, $lop_days, $month, $year, $payslip_id = null)
    {
        if ($lop_days <= 0) {
            return [
                'adjusted_lop' => 0,
                'remaining_lop' => 0,
                'adjustments' => []
            ];
        }

        // Get approved leaves for this month
        $approved_leaves = $this->getApprovedLeavesByMonth($staff_id, $month, $year);
        
        $remaining_lop = $lop_days;
        $adjustments = [];
        
        foreach ($approved_leaves as $leave_request) {
            if ($remaining_lop <= 0) {
                break;
            }
            
            $leave_days = floatval($leave_request['leave_days']);
            
            if ($leave_days <= 0) {
                continue;
            }
            
            // Calculate adjustment
            $to_adjust = min($leave_days, $remaining_lop);
            
            // Track adjustment
            $adjustments[] = [
                'leave_type_id' => $leave_request['leave_type_id'],
                'leave_type' => $leave_request['type'],
                'leave_request_id' => $leave_request['id'],
                'days_adjusted' => $to_adjust,
                'leave_from' => $leave_request['leave_from'],
                'leave_to' => $leave_request['leave_to']
            ];
            
            $remaining_lop -= $to_adjust;
        }
        
        $total_adjusted = $lop_days - $remaining_lop;
        
        // Log the adjustment
        if ($payslip_id && $total_adjusted > 0) {
            $this->logLOPAdjustment($staff_id, $payslip_id, $month, $year, $lop_days, $remaining_lop, $adjustments);
        }
        
        return [
            'adjusted_lop' => $total_adjusted,
            'remaining_lop' => $remaining_lop,
            'adjustments' => $adjustments
        ];
    }

    /**
     * Log LOP adjustment for audit trail
     * 
     * @param int $staff_id Staff ID
     * @param int $payslip_id Payslip ID
     * @param string $month Month
     * @param string $year Year
     * @param float $original_lop Original LOP days
     * @param float $adjusted_lop Final LOP days after adjustment
     * @param array $adjustments Adjustment details
     * @return bool Success
     */
    private function logLOPAdjustment($staff_id, $payslip_id, $month, $year, $original_lop, $adjusted_lop, $adjustments)
    {
        $log_data = [
            'staff_id' => $staff_id,
            'payslip_id' => $payslip_id,
            'month' => $month,
            'year' => $year,
            'original_lop_days' => $original_lop,
            'adjusted_lop_days' => $adjusted_lop,
            'adjustment_details' => json_encode($adjustments),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('staff_lop_adjustment_log', $log_data);
    }

    /**
     * Process LOP adjustment based on system settings
     * 
     * @param int $staff_id Staff ID
     * @param float $lop_days LOP days to adjust
     * @param string $month Month (01-12)
     * @param string $year Year (YYYY)
     * @param int|null $payslip_id Optional payslip ID
     * @return array Adjustment result
     */
    public function processLOPAdjustment($staff_id, $lop_days, $month, $year, $payslip_id = null)
    {
        // Get system setting
        $settings = $this->setting_model->getSetting();
        $auto_adjust = isset($settings->auto_adjust_lop_with_leaves) ? $settings->auto_adjust_lop_with_leaves : 0;
        
        if ($auto_adjust == 1) {
            // Auto-adjust from available paid leaves
            return $this->autoAdjustLOPWithLeaves($staff_id, $lop_days, $month, $year, $payslip_id);
        } else {
            // Adjust based on approved leave applications
            return $this->adjustLOPWithApprovedLeaves($staff_id, $lop_days, $month, $year, $payslip_id);
        }
    }

    /**
     * Get LOP adjustment history for a staff member
     * 
     * @param int $staff_id Staff ID
     * @param string|null $month Optional month filter
     * @param string|null $year Optional year filter
     * @return array Adjustment history
     */
    public function getLOPAdjustmentHistory($staff_id, $month = null, $year = null)
    {
        $this->db->select('staff_lop_adjustment_log.*');
        $this->db->from('staff_lop_adjustment_log');
        $this->db->where('staff_id', $staff_id);
        
        if ($month !== null) {
            $this->db->where('month', $month);
        }
        
        if ($year !== null) {
            $this->db->where('year', $year);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get();
        
        $results = $query->result_array();
        
        // Decode JSON adjustment details
        foreach ($results as &$result) {
            if (isset($result['adjustment_details'])) {
                $result['adjustment_details'] = json_decode($result['adjustment_details'], true);
            }
        }
        
        return $results;
    }

}
