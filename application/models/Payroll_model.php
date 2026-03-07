<?php
// Version: 2026-02-18-FINAL - Logging removed, ESI docs updated

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
            // Logging removed to prevent excessive logs during bulk payroll operations
            // Individual allowance logs are not needed; summary logs are created at controller level
        } else {
            $this->db->insert('payslip_allowance', $data);
            $id = $this->db->insert_id();
            // Logging removed to prevent excessive logs during bulk payroll operations
            // Individual allowance logs are not needed; summary logs are created at controller level
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

    /**
     * Get Year-To-Date (YTD) gross income for a staff member
     * Useful for mid-year increment TDS calculations
     * 
     * @param int $staff_id Staff ID
     * @param int $year Financial year
     * @param int $current_month Current month number (1-12)
     * @return array ['gross' => total_ytd_gross, 'payslips' => count, 'months' => array of month data]
     */
    public function getYTDIncome($staff_id, $year = null, $current_month = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        if ($current_month === null) {
            $current_month = date('n');
        }
        
        $this->db->select('id, basic, total_allowance, total_deduction, month, net_salary, (basic + total_allowance - total_deduction) as gross_salary');
        $this->db->from('staff_payslip');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('year', $year);
        
        // Get month indices for the months up to current month
        $month_names = array('', 'January', 'February', 'March', 'April', 'May', 'June', 
                            'July', 'August', 'September', 'October', 'November', 'December');
        
        // Build WHERE IN clause for months from January to current month
        $months_to_include = array_slice($month_names, 1, $current_month);
        
        if (!empty($months_to_include)) {
            $this->db->where_in('month', $months_to_include);
        }
        
        $this->db->order_by('month', 'ASC');
        $query = $this->db->get();
        
        $ytd_gross = 0;
        $payslip_count = 0;
        $month_data = array();
        
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $payslip) {
                $gross = $payslip->basic + $payslip->total_allowance - $payslip->total_deduction;
                $ytd_gross += $gross;
                $payslip_count++;
                
                $month_data[] = array(
                    'payslip_id' => $payslip->id,
                    'month' => $payslip->month,
                    'basic' => $payslip->basic,
                    'gross_salary' => $gross,
                    'net_salary' => $payslip->net_salary,
                );
            }
        }
        
        return array(
            'gross' => $ytd_gross,
            'payslips' => $payslip_count,
            'months' => $month_data,
            'current_month' => $current_month,
            'year' => $year,
        );
    }

    public function getAllowance($id, $type = null)
    {
        $this->db->select(
            "payslip_allowance.id,
            payslip_allowance.allowance_type,
            payroll_allowance_types.id as allowance_type_id,
            payroll_allowance_types.allowance_name as allowance_type_name,
            payroll_allowance_types.allowance_code as allowance_code,
            payslip_allowance.amount,
            payslip_allowance.cal_type"
        );
        $this->db->from("payslip_allowance");
        $this->db->join(
            "payroll_allowance_types",
            "payroll_allowance_types.allowance_code = payslip_allowance.allowance_type",
            "left"
        );
        $this->db->where("payslip_allowance.payslip_id", $id);

        if (!empty($type)) {
            $this->db->where("payslip_allowance.cal_type", $type);
        }

        $query = $this->db->get();

        return $query->result_array();
    }

    /**
     * Get all active allowance types from master table
     * @param string $category Optional - 'earning' or 'deduction'
     * @param bool $exclude_statutory If true, excludes auto-calculated items (EPF, ESI, TDS)
     * @return array List of allowance types
     */
    public function getAllowanceTypes($category = null, $exclude_statutory = true)
    {
        $this->db->select('id, allowance_code, allowance_name, category, is_taxable, is_statutory, display_order');
        $this->db->where('is_active', 1);
        
        if (!empty($category)) {
            $this->db->where('category', $category);
        }
        
        if ($exclude_statutory) {
            $this->db->where('is_statutory', 0); // Exclude EPF, ESI, TDS (auto-calculated)
        }
        
        $this->db->order_by('display_order', 'ASC');
        $query = $this->db->get('payroll_allowance_types');
        
        return $query->result_array();
    }

    /**
     * Get single allowance type by ID
     * @param int $id Allowance type ID
     * @return array|null Allowance type details
     */
    public function getAllowanceTypeById($id)
    {
        $query = $this->db->where('id', $id)->get('payroll_allowance_types');
        return $query->row_array();
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

    public function getbetweenpayrollReport($start_date, $end_date, $filter_month = null, $filter_year = null, $category_id = null, $bank_names = null)
    {      
        
        $condition = "date_format(staff_payslip.payment_date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";
        
        // Add month filter if provided
        if (!empty($filter_month)) {
            $escaped = $this->db->escape_str($filter_month);
            // build condition allowing both number and month name
            if (ctype_digit($filter_month)) {
                $num = intval($filter_month);
                $monthName = date('F', mktime(0, 0, 0, $num, 1));
                $condition .= " AND (staff_payslip.month = '" . $escaped . "' OR staff_payslip.month = '" . $this->db->escape_str($monthName) . "')";
            } else {
                // filter_month not numeric; may be name
                $condition .= " AND (staff_payslip.month = '" . $escaped . "' OR staff_payslip.month = '" . $this->db->escape_str(date('m', strtotime($filter_month . ' 1'))) . "')";
            }
        }
        
        // Add year filter if provided
        if (!empty($filter_year)) {
            $condition .= " AND staff_payslip.year = '" . $this->db->escape_str($filter_year) . "'";
        }

        // Add category filter if provided (supports single value or array)
        if (!empty($category_id)) {
            $ids = is_array($category_id) ? array_map('intval', $category_id) : [intval($category_id)];
            $ids = array_filter($ids);
            if (!empty($ids)) {
                $condition .= " AND COALESCE(staff.category_id, staff_designation.category_id) IN (" . implode(',', $ids) . ")";
            }
        }

        // Add bank name filter if provided (supports one or many bank names)
        if (!empty($bank_names)) {
            $banks = is_array($bank_names) ? $bank_names : [$bank_names];
            $include_empty_bank = false;
            $banks = array_values(array_filter(array_map(function ($bank) use (&$include_empty_bank) {
                $bank = trim((string) $bank);
                if ($bank === '__empty__') {
                    $include_empty_bank = true;
                    return '';
                }
                return $bank;
            }, $banks), function ($bank) {
                return $bank !== '';
            }));

            if (!empty($banks) || $include_empty_bank) {
                $bank_conditions = [];
                if (!empty($banks)) {
                    $escaped_banks = array_map(function ($bank) {
                        return "'" . $this->db->escape_str($bank) . "'";
                    }, $banks);
                    $bank_conditions[] = "staff.bank_name IN (" . implode(',', $escaped_banks) . ")";
                }
                if ($include_empty_bank) {
                    $bank_conditions[] = "staff.bank_name IS NULL";
                    $bank_conditions[] = "TRIM(COALESCE(staff.bank_name, '')) = ''";
                }
                $condition .= " AND (" . implode(' OR ', $bank_conditions) . ")";
            }
        }
       
        // Get ESI deduction from payslip_allowance (subquery) - for backward compatibility
        // Read ESI values directly from staff_payslip table (employee_esi and employer_esi)
        $this->db->select('staff.id,staff.employee_id,staff.name,staff.surname,staff.uan_no,staff.esi_no,staff.date_of_joining,staff.bank_name,staff.bank_branch,staff.ifsc_code,staff.bank_account_no,staff.is_active as staff_is_active,staff_payslip.*,
            (SELECT pa.amount FROM payslip_allowance pa 
             INNER JOIN payroll_allowance_types pat ON pa.allowance_type = pat.allowance_code 
             WHERE pa.payslip_id = staff_payslip.id 
             AND pat.allowance_code = "ESI" 
             AND pa.cal_type = "negative" 
             LIMIT 1) as esi_deduction,
                COALESCE((SELECT SUM(pa_pt.amount) FROM payslip_allowance pa_pt
                 LEFT JOIN payroll_allowance_types pat_pt ON pa_pt.allowance_type = pat_pt.allowance_code
                 WHERE pa_pt.payslip_id = staff_payslip.id
                 AND pa_pt.cal_type = "negative"
                 AND (
                     UPPER(pa_pt.allowance_type) = "PT"
                     OR UPPER(COALESCE(pat_pt.allowance_name, "")) = "PROFESSIONAL TAX"
                 )), 0) as professional_tax,
            COALESCE(staff.category_id, staff_designation.category_id) as category_id,
            sdc.name as staff_type,
            sdc.color as staff_type_color,
            sdc.icon as staff_type_icon');
        $this->db->join("staff_payslip", "staff_payslip.staff_id = staff.id", "inner");
        $this->db->join("staff_designation", "staff.designation = staff_designation.id", "left");
        $this->db->join("staff_designation_category sdc", "COALESCE(staff.category_id, staff_designation.category_id) = sdc.id", "left");
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
        
        $this->db->order_by("staff.name", "ASC");
        $this->db->order_by("staff.surname", "ASC");
        
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

    private function isOnDutyLeaveType($leave_type_name)
    {
        $normalized = strtolower(trim((string) $leave_type_name));
        return in_array($normalized, ['on duty', 'od'], true);
    }

    private function isManualSeedAllowedForLeaveType($leave_type_id)
    {
        $leave_type_id = (int) $leave_type_id;
        if ($leave_type_id <= 0) {
            return true;
        }

        $has_balance_flag = $this->db->field_exists('requires_balance_check', 'leave_types');
        $this->db->select('type, is_lop');
        if ($has_balance_flag) {
            $this->db->select('requires_balance_check');
        }
        $row = $this->db->from('leave_types')->where('id', $leave_type_id)->limit(1)->get()->row_array();

        if (!is_array($row)) {
            return true;
        }

        if ($has_balance_flag) {
            return (int) ($row['requires_balance_check'] ?? 1) === 1;
        }

        return !$this->isOnDutyLeaveType($row['type'] ?? '');
    }

    private function getOnDutyLeaveTypeIds()
    {
        $has_balance_flag = $this->db->field_exists('requires_balance_check', 'leave_types');

        $this->db->select('id, type, is_lop');
        if ($has_balance_flag) {
            $this->db->select('requires_balance_check');
        }
        $this->db->from('leave_types');
        $this->db->where('is_lop', 0);
        $rows = $this->db->get()->result_array();

        $eligible_ids = [];
        foreach ($rows as $row) {
            if ($has_balance_flag) {
                if ((int) ($row['requires_balance_check'] ?? 1) === 0) {
                    $eligible_ids[] = (int) $row['id'];
                }
                continue;
            }

            // Backward-compatible fallback when balance flag column is unavailable.
            $type_name = isset($row['type']) ? $row['type'] : '';
            if ($this->isOnDutyLeaveType($type_name)) {
                $eligible_ids[] = (int) $row['id'];
            }
        }

        return array_values(array_unique($eligible_ids));
    }

    private function getLeaveTypeNamesByIds($leave_type_ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $leave_type_ids), function ($id) {
            return $id > 0;
        })));

        if (empty($ids)) {
            return [];
        }

        $this->db->select('id, type');
        $this->db->from('leave_types');
        $this->db->where_in('id', $ids);
        $rows = $this->db->get()->result_array();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = (string) ($row['type'] ?? '');
        }

        return $map;
    }

    private function buildLopAdjustmentLeavePool($staff_id, $od_type_ids, $od_balances)
    {
        $pool = [];
        $seen = [];

        $type_name_map = $this->getLeaveTypeNamesByIds(array_merge($od_type_ids, array_keys((array) $od_balances)));

        foreach ((array) $od_type_ids as $od_type_id) {
            $od_type_id = (int) $od_type_id;
            if ($od_type_id <= 0 || isset($seen[$od_type_id])) {
                continue;
            }

            $pool[] = [
                'leave_type_id' => $od_type_id,
                'type' => $type_name_map[$od_type_id] ?? 'OD',
                'staff_id' => (int) $staff_id,
            ];
            $seen[$od_type_id] = true;
        }

        $paid_leaves = $this->getStaffPaidLeaves($staff_id);
        foreach ($paid_leaves as $leave) {
            $leave_type_id = (int) ($leave['leave_type_id'] ?? 0);
            if ($leave_type_id <= 0 || isset($seen[$leave_type_id])) {
                continue;
            }

            $pool[] = $leave;
            $seen[$leave_type_id] = true;
        }

        usort($pool, function ($left, $right) use ($od_type_ids) {
            $left_is_od = in_array((int) ($left['leave_type_id'] ?? 0), $od_type_ids, true) ? 1 : 0;
            $right_is_od = in_array((int) ($right['leave_type_id'] ?? 0), $od_type_ids, true) ? 1 : 0;

            if ($left_is_od !== $right_is_od) {
                return $right_is_od - $left_is_od;
            }

            $left_type = strtolower(trim((string) ($left['type'] ?? '')));
            $right_type = strtolower(trim((string) ($right['type'] ?? '')));
            if ($left_type === $right_type) {
                return 0;
            }

            return ($left_type < $right_type) ? -1 : 1;
        });

        return $pool;
    }

    private function getApprovedLeaveDaysForTypeInRange($staff_id, $leave_type_id, $start_date, $end_date)
    {
        $total_days = 0.0;
        $is_movement_credit_type = false;
        $settings = null;
        $holiday_cache = [];
        $has_balance_flag = $this->db->field_exists('requires_balance_check', 'leave_types');

        $this->db->select('type, is_lop');
        if ($has_balance_flag) {
            $this->db->select('requires_balance_check');
        }
        $type_row = $this->db->where('id', (int) $leave_type_id)->limit(1)->get('leave_types')->row_array();
        $type_name = strtolower(trim((string) ($type_row['type'] ?? '')));
        $is_lop = (int) ($type_row['is_lop'] ?? 0);
        if ($has_balance_flag) {
            $requires_balance_check = (int) ($type_row['requires_balance_check'] ?? 1);
            $is_movement_credit_type = ($is_lop === 0 && $requires_balance_check === 0);
        } else {
            $is_movement_credit_type = in_array($type_name, ['on duty', 'od'], true);
        }

        $present_dates = [];
        if ($is_movement_credit_type) {
            $settings = $this->setting_model->getSetting();
            $present_rows = $this->db->select('sa.date')
                ->from('staff_attendance sa')
                ->join('staff_attendance_type sat', 'sat.id = sa.staff_attendance_type_id', 'left')
                ->where('sa.staff_id', (int) $staff_id)
                ->where('sa.date >=', $start_date)
                ->where('sa.date <=', $end_date)
                ->group_start()
                ->where('LOWER(TRIM(sat.type))', 'present')
                ->or_where('UPPER(TRIM(sat.key_value))', 'P')
                ->group_end()
                ->get()
                ->result_array();

            foreach ($present_rows as $present_row) {
                $present_date = (string) ($present_row['date'] ?? '');
                if ($present_date !== '') {
                    $present_dates[$present_date] = true;
                }
            }
        }

        $this->db->select('leave_from, leave_to, leave_days, leave_duration_type');
        $this->db->from('staff_leave_request');
        $this->db->where('staff_id', (int) $staff_id);
        $this->db->where('leave_type_id', (int) $leave_type_id);
        $this->db->where_in('status', ['approve', 'approved']);
        $this->db->where('leave_from <=', $end_date);
        $this->db->where('leave_to >=', $start_date);
        $query = $this->db->get();
        $rows = $query->result_array();

        foreach ($rows as $row) {
            $leave_from = isset($row['leave_from']) ? (string) $row['leave_from'] : '';
            $leave_to = isset($row['leave_to']) ? (string) $row['leave_to'] : '';
            if ($leave_from === '' || $leave_to === '') {
                continue;
            }

            $effective_from = max($leave_from, $start_date);
            $effective_to = min($leave_to, $end_date);
            if ($effective_from > $effective_to) {
                continue;
            }

            $duration_type = strtolower(trim((string) ($row['leave_duration_type'] ?? 'full_day')));
            if (in_array($duration_type, ['half_day', 'first_half', 'second_half'], true)) {
                if ($is_movement_credit_type && isset($present_dates[$effective_from]) && !$this->isWeekendOrOfficialHolidayForPayroll($effective_from, $settings, $holiday_cache)) {
                    continue;
                }
                $total_days += 0.5;
                continue;
            }

            if ($is_movement_credit_type) {
                $cursor = $effective_from;
                while ($cursor <= $effective_to) {
                    if (!isset($present_dates[$cursor]) || $this->isWeekendOrOfficialHolidayForPayroll($cursor, $settings, $holiday_cache)) {
                        $total_days += 1.0;
                    }
                    $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
                }
                continue;
            }

            $from_ts = strtotime($effective_from);
            $to_ts = strtotime($effective_to);
            if ($from_ts === false || $to_ts === false) {
                continue;
            }

            $covered_days = (($to_ts - $from_ts) / 86400) + 1;
            if ($covered_days > 0) {
                $total_days += (float) $covered_days;
            }
        }

        return round($total_days, 2);
    }

    private function isWeekendOrOfficialHolidayForPayroll($date_ymd, $settings = null, &$holiday_cache = [])
    {
        static $calendar_holidays = null;

        $date_ymd = trim((string) $date_ymd);
        if ($date_ymd === '') {
            return false;
        }

        if (!is_array($holiday_cache)) {
            $holiday_cache = [];
        }

        if ($settings === null) {
            $settings = $this->setting_model->getSetting();
        }

        $weekend_days_str = isset($settings->weekend_days) && $settings->weekend_days !== '' ? (string) $settings->weekend_days : '0';
        $weekend_days = array_map('intval', explode(',', $weekend_days_str));
        $is_second_saturday_holiday = isset($settings->isSecondSaturdayHoliday) ? (int) $settings->isSecondSaturdayHoliday : 0;

        $day_of_week = (int) date('w', strtotime($date_ymd));
        $is_weekend = in_array($day_of_week, $weekend_days, true);
        if (!$is_weekend && $is_second_saturday_holiday === 1 && $day_of_week === 6 && $this->isSecondSaturdayForPayroll($date_ymd)) {
            $is_weekend = true;
        }

        if ($is_weekend) {
            return true;
        }

        if (!array_key_exists($date_ymd, $holiday_cache)) {
            $holiday_cache[$date_ymd] = false;
            if ($calendar_holidays === null) {
                $this->load->model('holiday_model');
                $calendar_holidays = $this->holiday_model->get();
                if (!is_array($calendar_holidays)) {
                    $calendar_holidays = [];
                }
            }

            foreach ($calendar_holidays as $holiday_row) {
                $from_date = (string) ($holiday_row['from_date'] ?? '');
                $to_date = (string) ($holiday_row['to_date'] ?? '');
                if ($from_date === '' || $to_date === '') {
                    continue;
                }
                if ($date_ymd < $from_date || $date_ymd > $to_date) {
                    continue;
                }

                $type_label = strtolower(trim((string) ($holiday_row['type'] ?? '')));
                if (!$this->isCompOffHolidayTypeForPayroll($type_label)) {
                    $holiday_cache[$date_ymd] = true;
                    break;
                }
            }
        }

        return (bool) $holiday_cache[$date_ymd];
    }

    private function isSecondSaturdayForPayroll($date_ymd)
    {
        $date_obj = DateTime::createFromFormat('Y-m-d', $date_ymd);
        if (!$date_obj) {
            return false;
        }

        $month_start = new DateTime($date_obj->format('Y-m-01'));
        $count = 0;
        while ($month_start <= $date_obj) {
            if ((int) $month_start->format('w') === 6) {
                $count++;
            }
            if ($month_start->format('Y-m-d') === $date_obj->format('Y-m-d')) {
                break;
            }
            $month_start->modify('+1 day');
        }

        return $count === 2;
    }

    private function isCompOffHolidayTypeForPayroll($type_label)
    {
        static $override_types = null;
        if ($override_types === null) {
            $override_types = ['compensation', 'comp off', 'compoff', 'compensatory off'];
            $setting = $this->setting_model->getSetting();
            $configured = trim((string) ($setting->leave_workday_override_types ?? ''));
            if ($configured !== '') {
                $parsed = array_filter(array_map('trim', explode(',', strtolower($configured))));
                if (!empty($parsed)) {
                    $override_types = [];
                    foreach ($parsed as $item) {
                        $normalized_item = str_replace(['_', '-'], ' ', $item);
                        $normalized_item = preg_replace('/\s+/', ' ', $normalized_item);
                        if (!in_array($normalized_item, $override_types, true)) {
                            $override_types[] = $normalized_item;
                        }
                    }
                }
            }
        }

        $normalized = strtolower(trim((string) $type_label));
        $normalized = str_replace(['_', '-'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return in_array($normalized, $override_types, true);
    }

    public function syncOnDutyCreditsForMonth($staff_id, $month, $year, $simulate = false)
    {
        $month_int = (int) $month;
        $year_int = (int) $year;
        $start_date = date('Y-m-d', mktime(0, 0, 0, $month_int, 1, $year_int));
        $end_date = date('Y-m-t', strtotime($start_date));

        $od_type_ids = $this->getOnDutyLeaveTypeIds();
        if (empty($od_type_ids)) {
            return [];
        }

        $od_balances = [];
        foreach ($od_type_ids as $leave_type_id) {
            $approved_days = $this->getApprovedLeaveDaysForTypeInRange($staff_id, $leave_type_id, $start_date, $end_date);

            if ($simulate) {
                $balance = $this->getMonthlyBalanceSnapshot($staff_id, $leave_type_id, $year_int, $month_int);
            } else {
                $balance = $this->getOrCreateMonthlyBalance($staff_id, $leave_type_id, $year_int, $month_int);
            }

            if (!is_array($balance)) {
                continue;
            }

            $opening_balance = (float) ($balance['opening_balance'] ?? 0);
            $used_for_lop_adjustment = (float) ($balance['used_for_lop_adjustment'] ?? 0);
            $used_for_leave_application = (float) ($balance['used_for_leave_application'] ?? 0);
            $other_deductions = (float) ($balance['other_deductions'] ?? 0);
            $closing_balance = $opening_balance + $approved_days - $used_for_lop_adjustment - $used_for_leave_application - $other_deductions;

            $balance['earned_in_month'] = $approved_days;
            $balance['closing_balance'] = $closing_balance;

            if (!$simulate && !empty($balance['id'])) {
                $this->db->where('id', $balance['id']);
                $this->db->update('staff_monthly_leave_balance', [
                    'earned_in_month' => $approved_days,
                    'closing_balance' => $closing_balance,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            $od_balances[(int) $leave_type_id] = $balance;
        }

        return $od_balances;
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
            if ($this->isManualSeedAllowedForLeaveType($leave_type_id)) {
                $this->db->where('staff_id', $staff_id);
                $this->db->where('leave_type_id', $leave_type_id);
                $leave_query = $this->db->get('staff_leave_details');

                if ($leave_query->num_rows() > 0) {
                    $leave_data = $leave_query->row_array();
                    $opening_balance = floatval($leave_data['alloted_leave']);
                }
            }
        }
        
        // Create new record
        $data = [
            'staff_id' => $staff_id,
            'leave_type_id' => $leave_type_id,
            'year' => $year,
            'month' => $month,
            'opening_balance' => $opening_balance,
            'earned_in_month' => 0,
            'used_for_lop_adjustment' => 0,
            'used_for_leave_application' => 0,
            'other_deductions' => 0,
            'closing_balance' => $opening_balance,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'notes' => 'Auto-created on ' . date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('staff_monthly_leave_balance', $data);
        $data['id'] = $this->db->insert_id();
        
        return $data;
    }

    /**
     * Get monthly leave balance snapshot without creating records
     *
     * @param int $staff_id Staff ID
     * @param int $leave_type_id Leave type ID
     * @param int $year Year (YYYY)
     * @param int $month Month (1-12)
     * @return array|null Monthly balance snapshot
     */
    public function getMonthlyBalanceSnapshot($staff_id, $leave_type_id, $year, $month)
    {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('leave_type_id', $leave_type_id);
        $this->db->where('year', $year);
        $this->db->where('month', $month);
        $query = $this->db->get('staff_monthly_leave_balance');

        if ($query->num_rows() > 0) {
            return $query->row_array();
        }

        $opening_balance = 0;
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
            if ($this->isManualSeedAllowedForLeaveType($leave_type_id)) {
                $this->db->where('staff_id', $staff_id);
                $this->db->where('leave_type_id', $leave_type_id);
                $leave_query = $this->db->get('staff_leave_details');

                if ($leave_query->num_rows() > 0) {
                    $leave_data = $leave_query->row_array();
                    $opening_balance = floatval($leave_data['alloted_leave']);
                }
            }
        }

        return [
            'id' => null,
            'staff_id' => $staff_id,
            'leave_type_id' => $leave_type_id,
            'year' => $year,
            'month' => $month,
            'opening_balance' => $opening_balance,
            'earned_in_month' => 0,
            'used_for_lop_adjustment' => 0,
            'used_for_leave_application' => 0,
            'other_deductions' => 0,
            'closing_balance' => $opening_balance,
        ];
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
    public function processLOPWithMonthlyBalance($staff_id, $lop_days, $month, $year, $payslip_id = null, $simulate = false)
    {
        $month_int = intval($month);
        $year_int = intval($year);
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, max(1, min(12, $month_int)), max(1970, $year_int));
        
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
        
        $od_type_ids = $this->getOnDutyLeaveTypeIds();
        $od_balances = $this->syncOnDutyCreditsForMonth($staff_id, $month_int, $year_int, $simulate);

        if ($lop_days <= 0) {
            $od_carry_forward_days = 0;
            foreach ($od_balances as $od_balance) {
                $od_carry_forward_days += (float) ($od_balance['closing_balance'] ?? 0);
            }
            return [
                'success' => true,
                'actual_lop_days' => 0,
                'adjusted_lop_days' => 0,
                'net_lop_days' => 0,
                'adjustments' => [],
                'od_adjusted_days' => 0,
                'od_carry_forward_days' => max(0, round($od_carry_forward_days, 2))
            ];
        }

        if ($days_in_month > 0 && (float) $lop_days >= (float) $days_in_month) {
            $od_carry_forward_days = 0;
            foreach ($od_balances as $od_balance) {
                $od_carry_forward_days += (float) ($od_balance['closing_balance'] ?? 0);
            }

            return [
                'success' => true,
                'actual_lop_days' => $lop_days,
                'adjusted_lop_days' => 0,
                'net_lop_days' => $lop_days,
                'adjustments' => [],
                'od_adjusted_days' => 0,
                'od_carry_forward_days' => max(0, round($od_carry_forward_days, 2)),
                'message' => 'Full-month LOP is not eligible for leave adjustment'
            ];
        }
        
        // Get system setting
        $settings = $this->setting_model->getSetting();
        $auto_adjust = isset($settings->auto_adjust_lop_with_leaves) ? $settings->auto_adjust_lop_with_leaves : 0;
        
        // If auto adjust is disabled, return with no adjustments
        if ($auto_adjust != 1) {
            $od_carry_forward_days = 0;
            foreach ($od_balances as $od_balance) {
                $od_carry_forward_days += (float) ($od_balance['closing_balance'] ?? 0);
            }
            return [
                'success' => true,
                'actual_lop_days' => $lop_days,
                'adjusted_lop_days' => 0,
                'net_lop_days' => $lop_days,
                'adjustments' => [],
                'od_adjusted_days' => 0,
                'od_carry_forward_days' => max(0, round($od_carry_forward_days, 2)),
                'message' => 'Auto LOP adjustment is disabled'
            ];
        }
        
        // Build adjustment pool with OD first, then other paid leaves.
        // OD is included even when no staff_leave_details row exists for OD.
        $paid_leaves = $this->buildLopAdjustmentLeavePool($staff_id, $od_type_ids, $od_balances);
        
        $remaining_lop = $lop_days;
        $adjustments = [];
        $od_adjusted_days = 0;
        
        foreach ($paid_leaves as $leave) {
            if ($remaining_lop <= 0) {
                break;
            }

            $leave_type_id = (int) ($leave['leave_type_id'] ?? 0);
            $is_od_leave = in_array($leave_type_id, $od_type_ids, true);
            
            // Get or create monthly balance
            if ($simulate && isset($od_balances[$leave_type_id])) {
                $balance = $od_balances[$leave_type_id];
            } elseif ($simulate) {
                $balance = $this->getMonthlyBalanceSnapshot($staff_id, $leave['leave_type_id'], $year_int, $month_int);
            } else {
                $balance = $this->getOrCreateMonthlyBalance($staff_id, $leave['leave_type_id'], $year_int, $month_int);
            }
            
            // Ensure we have an array and set defaults for missing keys
            if (!is_array($balance)) {
                continue;
            }
            
            $opening_balance = floatval($balance['opening_balance'] ?? 0);
            $earned_in_month = floatval($balance['earned_in_month'] ?? 0);
            $used_for_leave_application = floatval($balance['used_for_leave_application'] ?? 0);
            $other_deductions = floatval($balance['other_deductions'] ?? 0);
            
            // Calculate available balance
            $available = $opening_balance + $earned_in_month - $used_for_leave_application - $other_deductions;
            
            if ($available <= 0) {
                continue;
            }
            
            // Calculate adjustment
            $to_adjust = min($available, $remaining_lop);
            
            // Update monthly balance
            $used_for_lop_adjustment = floatval($balance['used_for_lop_adjustment'] ?? 0);
            $new_used_lop = $used_for_lop_adjustment + $to_adjust;
            $new_closing = $opening_balance + $earned_in_month - $new_used_lop - $used_for_leave_application - $other_deductions;
            
            $update_data = [
                'used_for_lop_adjustment' => $new_used_lop,
                'closing_balance' => $new_closing,
                'last_processed_date' => date('Y-m-d H:i:s')
            ];
            
            if ($payslip_id) {
                $update_data['payslip_id'] = $payslip_id;
            }
            
            if (!$simulate) {
                $this->db->where('id', $balance['id']);
                $this->db->update('staff_monthly_leave_balance', $update_data);

                // Log to audit table
                $this->logBalanceAudit($balance['id'], $staff_id, $leave['leave_type_id'], 'LOP_ADJUSTMENT', 
                                      $to_adjust, $available, $new_closing, $payslip_id, 'payslip', 
                                      'LOP adjustment for payroll');
            }

            if ($is_od_leave) {
                $od_adjusted_days += (float) $to_adjust;
                if (isset($od_balances[$leave_type_id])) {
                    $od_balances[$leave_type_id]['used_for_lop_adjustment'] = $new_used_lop;
                    $od_balances[$leave_type_id]['closing_balance'] = $new_closing;
                }
            }
            
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
        $od_carry_forward_days = 0;

        if ($simulate) {
            foreach ($od_balances as $od_balance) {
                $od_carry_forward_days += (float) ($od_balance['closing_balance'] ?? 0);
            }
        } elseif (!empty($od_type_ids)) {
            $this->db->select_sum('closing_balance', 'od_carry_forward');
            $this->db->from('staff_monthly_leave_balance');
            $this->db->where('staff_id', (int) $staff_id);
            $this->db->where('year', $year_int);
            $this->db->where('month', $month_int);
            $this->db->where_in('leave_type_id', $od_type_ids);
            $od_row = $this->db->get()->row_array();
            $od_carry_forward_days = (float) ($od_row['od_carry_forward'] ?? 0);
        }
        
        return [
            'success' => true,
            'actual_lop_days' => $lop_days,
            'adjusted_lop_days' => $total_adjusted,
            'net_lop_days' => $remaining_lop,
            'adjustments' => $adjustments,
            'od_adjusted_days' => round($od_adjusted_days, 2),
            'od_carry_forward_days' => max(0, round($od_carry_forward_days, 2))
        ];
    }

    /**
     * Preview LOP adjustment without persisting changes
     *
     * @param int $staff_id Staff ID
     * @param float $lop_days LOP days
     * @param string $month Month (01-12)
     * @param string $year Year (YYYY)
     * @return array Adjustment result
     */
    public function previewLOPWithMonthlyBalance($staff_id, $lop_days, $month, $year)
    {
        return $this->processLOPWithMonthlyBalance($staff_id, $lop_days, $month, $year, null, true);
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

    /**
     * ==============================================================
     * SALARY INCREMENT HANDLING METHODS (NEW)
     * ==============================================================
     */

    /**
     * Check if staff has an approved increment effective in this month
     * 
     * @param int $staff_id Staff ID
     * @param int $month Month number (1-12)
     * @param int $year Year
     * @return array|null Increment data or null
     */
    public function getIncrementForMonth($staff_id, $month, $year)
    {
        $this->load->model('payroll_increment_model');
        return $this->payroll_increment_model->getApprovedIncrementForMonth($staff_id, $month, $year);
    }

    /**
     * Check if payslip month is first month of increment
     * (Used to determine if we show "Increment" line item)
     * 
     * @param int $staff_id Staff ID
     * @param int $month Month number (1-12)
     * @param int $year Year
     * @return bool
     */
    public function isIncrementMonth($staff_id, $month, $year)
    {
        $increment = $this->getIncrementForMonth($staff_id, $month, $year);
        return ($increment !== null);
    }

    /**
     * Get list of all staff with approved increments effective this month
     * 
     * @param int $month Month number (1-12)
     * @param int $year Year
     * @return array List of staff with increments
     */
    public function getStaffWithIncrementThisMonth($month, $year)
    {
        $first_day = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $last_day = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year));
        
        $query = $this->db
            ->select('DISTINCT staff_id, increment_amount, increment_percentage, merge_with')
            ->from('staff_increment_history')
            ->where('approval_status', 'Approved')
            ->where('effective_date >=', $first_day)
            ->where('effective_date <=', $last_day)
            ->get();
        
        return $query->result_array();
    }

    /**
     * Mark increment line item as temporary in payslip
     * (This is called when creating payslip for increment month)
     * 
     * @param int $payslip_id Payslip ID
     * @param int $increment_history_id Increment history ID
     * @return bool
     */
    public function markIncrementAsTemporary($payslip_id, $increment_history_id)
    {
        $this->db->where(array(
            'payslip_id' => $payslip_id,
            'allowance_type' => 'Increment'
        ));
        $this->db->update('payslip_allowance', array(
            'is_temporary' => true,
            'increment_history_id' => $increment_history_id
        ));
        
        return ($this->db->affected_rows() > 0);
    }

    /**
     * Mark increment line as merged (after first month)
     * 
     * @param int $payslip_id Payslip ID
     * @param string $merged_into Where increment was merged (basic or special_allowance)
     * @return bool
     */
    public function markIncrementAsMerged($payslip_id, $merged_into = 'basic')
    {
        $this->db->where(array(
            'payslip_id' => $payslip_id,
            'allowance_type' => 'Increment'
        ));
        $this->db->update('payslip_allowance', array(
            'is_temporary' => false,
            'merged_into' => $merged_into
        ));
        
        return ($this->db->affected_rows() > 0);
    }

    /**
     * Get total increment for staff (sum of all approved increments up to date)
     * 
     * @param int $staff_id Staff ID
     * @param string $up_to_date Date in Y-m-d format
     * @return float Total increment amount
     */
    public function getTotalIncrementUpToDate($staff_id, $up_to_date = null)
    {
        if ($up_to_date === null) {
            $up_to_date = date('Y-m-d');
        }
        
        $query = $this->db
            ->select('SUM(COALESCE(increment_amount, 0)) as total_fixed, 
                      GROUP_CONCAT(increment_percentage) as percentages')
            ->from('staff_increment_history')
            ->where('staff_id', $staff_id)
            ->where('approval_status', 'Approved')
            ->where('effective_date <=', $up_to_date)
            ->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return (float) $result['total_fixed'];
        }
        
        return 0;
    }

    /**
     * Calculate EPF and ESI deductions based on dual checkpoint validation
     * EPF: Requires uan_no NOT EMPTY AND is_epf_enabled == 1
     * ESI: Requires esi_no NOT EMPTY AND is_esi_enabled == 1
     * 
     * @param array $staff - Staff record with uan_no, esi_no, is_epf_enabled, is_esi_enabled, basic_salary
     * @param float $epf_rate - EPF deduction rate (default 12% employee contribution)
     * @param float $esi_rate - ESI deduction rate (default 0.75% employee contribution)
     * @return array - Array with 'epf_deduction' and 'esi_deduction' keys
     */
    /**
     * Calculate Statutory Deductions (EPF, ESI, TDS)
     * Uses payroll_allowance_types table for configuration
     * Validates staff eligibility before calculation
     *
     * Rule: EPF only if UAN is available
     * Rule: ESI based on salary threshold only (≤ ₹21,000)
     * Rule: EPF contribution capped at Rs 15,000 base salary
     *
     * @param array $staff Staff record with uan_no, is_epf_enabled
     * @param float $epf_rate EPF rate (default 12%)
     * @param float $esi_rate ESI rate (default 0.75%)
     * @return array Array with epf_deduction, esi_deduction
     */
    public function calculateStatutoryDeductions($staff, $epf_rate = 0.12, $esi_rate = 0.0075, $additional_earnings = 0)
    {
        $deductions = array(
            'epf_deduction' => 0,
            'esi_deduction' => 0,
        );

        // Get base salary from staff record
        $basic_salary = isset($staff['basic_salary']) ? (float) $staff['basic_salary'] : 0;

        if ($basic_salary <= 0) {
            return $deductions; // No deductions if basic salary is zero
        }

        // Get statutory allowance type codes from database
        $statutory_types = $this->getStatutoryAllowanceTypes();

        // ========== EPF DEDUCTION ==========
        // EPF Eligibility Check: 
        // - UAN number must be available (primary check)
        // - EPF enabled flag must be set (1 = Yes)
        // Rule: Without UAN, no EPF calculation, even if flag is enabled
        
        if (!empty($staff['uan_no'])) {
            // UAN is available - check if EPF is enabled
            if (isset($staff['is_epf_enabled']) && $staff['is_epf_enabled'] == 1) {
                // Calculate EPF with wage ceiling of Rs 15,000
                // Indian law: EPF contribution base is capped at Rs 15,000
                // include additional earnings such as allowances or temporary increments
                $epf_base = min($basic_salary + (float)$additional_earnings, 15000);
                $deductions['epf_deduction'] = round($epf_base * $epf_rate, 2);
            }
        }
        // If UAN is not available, EPF deduction remains 0 (no calculation)

        // ========== ESI DEDUCTION ==========
        // ESI Eligibility Check:
        // - Based on salary threshold only (≤ ₹21,000)
        // - No longer requires esi_no to be present
        // Rule: ESI is calculated for all employees with gross wage ≤ ₹21,000
        // Note: This function uses basic_salary as approximation; full calculation in controller
        
        if ($basic_salary <= 21000) {
            // Salary is within ESI threshold - calculate ESI
            // Calculate ESI - note: ESI should be calculated on gross (basic + DA + allowances)
            // For this simplified function, we calculate on basic as approximation
            $deductions['esi_deduction'] = round($basic_salary * $esi_rate, 2);
        }
        // If salary > ₹21,000, ESI deduction remains 0 (not eligible)

        return $deductions;
    }

    /**
     * Get statutory allowance types from the payroll_allowance_types table
     * These are system-calculated deductions: EPF, ESI, TDS, etc.
     * 
     * @return array Array with keys as codes (EPF, ESI, TDS, PT) and values as allowance_code
     */
    public function getStatutoryAllowanceTypes()
    {
        // Query from the payroll_allowance_types table for statutory deductions
        $query = $this->db->select('id, allowance_code, allowance_name, is_statutory')
            ->from('payroll_allowance_types')
            ->where('is_statutory', 1)
            ->where('is_active', 1)
            ->get();
        
        $statutory_types = array();
        foreach ($query->result_array() as $row) {
            // Map by code for easy lookup
            $statutory_types[$row['allowance_code']] = array(
                'id' => $row['id'],
                'code' => $row['allowance_code'],
                'name' => $row['allowance_name']
            );
        }
        
        return $statutory_types;
    }

    /**
     * Get specific statutory allowance type code
     * @param string $code Code like 'EPF', 'ESI', 'TDS', 'PT'
     * @return string|false Returns the allowance code if found, false otherwise
     */
    public function getStatutoryAllowanceCode($code = 'EPF')
    {
        $code = strtoupper(trim($code));
        $query = $this->db->select('allowance_code')
            ->from('payroll_allowance_types')
            ->where('allowance_code', $code)
            ->where('is_statutory', 1)
            ->where('is_active', 1)
            ->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result['allowance_code'];
        }
        return false;
    }

}
