<?php
// Version: 2026-02-18-FINAL - No per-record logging in add()
class Staffattendancemodel extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date = $this->setting_model->getDateYmd();
    }
    
    public function addorUpdate($attendances)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
      
        if(!empty($attendances)){
            foreach ($attendances as $attendance_key => $attendance_value) {
                            
                $this->db->where('staff_id',  $attendance_value['staff_id']);
                $this->db->where('date', $attendance_value['date']);
                $query = $this->db->get('staff_attendance');
                
                $timestamp = date('Y-m-d H:i:s');

                if ($query->num_rows() > 0) {
                    // Record exists, update it and refresh updated_at
                    $existing_id = $query->row()->id;
                    $update_data = $attendance_value;
                    $update_data['updated_at'] = $timestamp;
                    unset($update_data['created_at']);

                    $this->db->where('id', $existing_id);
                    $this->db->update('staff_attendance', $update_data);
                } else {
                    // Record does not exist, insert a new one with timestamps
                    $insert_data = $attendance_value;
                    $insert_data['created_at'] = $timestamp;
                    $insert_data['updated_at'] = $timestamp;

                    $this->db->insert('staff_attendance', $insert_data);
                }

                }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }    
    }

    public function get($id = null) {
        $this->db->select()->join("staff", "staff.id = staff_attendance.staff_id")->from('staff_attendance');
        $this->db->where("staff.is_active", 1);
        if ($id != null) {
            $this->db->where('staff_attendance.id', $id);
        } else {
            $this->db->order_by('staff_attendance.id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getUserType() {
        $query = $this->db->query("select distinct user_type from staff where is_active = 1");
        return $query->result_array();
    }

    public function searchAttendenceUserTypeWithMode($user_type, $date,$mode) {
        $condition = '';

        if ($mode == 1) {
            $condition = " and staff_attendance.biometric_attendence= 0 and staff_attendance.qrcode_attendance=0";
        } elseif ($mode == 2) {
            $condition = " and staff_attendance.biometric_attendence= 0 and staff_attendance.qrcode_attendance=1";
        } elseif ($mode == 3) {
            $condition = " and staff_attendance.biometric_attendence= 1 and staff_attendance.qrcode_attendance=0";
        }

        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);       
            $superadmin_visible = $this->customlib->superadmin_visible(); 
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {                 
                $condition = " and roles.id != 7";
            } 
        }
        
        if ($user_type == "select") { 
            $query = $this->db->query("select staff_attendance.in_time,staff_attendance.out_time,staff_attendance.id,staff_attendance.created_at as attendence_dt, staff_attendance.staff_attendance_type_id,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff_attendance.user_agent,staff_attendance.biometric_device_data,staff_attendance.remark,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,IFNULL(staff_attendance.date, 'xxx') as date,staff.id as staff_id, staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance_type.long_lang_name,staff_attendance_type.long_name_style  from staff left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where staff.is_active = 1 $condition order by staff_attendance.created_at asc");
        } else {
            $query = $this->db->query("select staff_attendance.in_time,staff_attendance.out_time,staff_attendance.staff_attendance_type_id,staff_attendance.created_at as attendence_dt,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff_attendance.user_agent,staff_attendance.biometric_device_data,staff_attendance.remark,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,IFNULL(staff_attendance.date, 'xxx') as date, IFNULL(staff_attendance.id, 0) as id, staff.id as staff_id ,staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance_type.long_lang_name,staff_attendance_type.long_name_style from staff left join staff_roles on (staff.id = staff_roles.staff_id) left join roles on (roles.id = staff_roles.role_id) left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where roles.name = " . $this->db->escape($user_type) . " and staff.is_active = 1 $condition order by staff_attendance.created_at asc");
            
        }
        return $query->result_array();
    }

    public function searchAttendenceUserTypeWithModeRange($user_type, $from_date, $to_date, $mode) {
        $condition = '';

        if ($mode == 1) {
            $condition = " and staff_attendance.biometric_attendence= 0 and staff_attendance.qrcode_attendance=0";
        } elseif ($mode == 2) {
            $condition = " and staff_attendance.biometric_attendence= 0 and staff_attendance.qrcode_attendance=1";
        } elseif ($mode == 3) {
            $condition = " and staff_attendance.biometric_attendence= 1 and staff_attendance.qrcode_attendance=0";
        }

        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);       
            $superadmin_visible = $this->customlib->superadmin_visible(); 
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {                 
                $condition .= " and roles.id != 7";
            } 
        }
        
        if ($user_type == "select") { 
            $query = $this->db->query("select staff_attendance.in_time,staff_attendance.out_time,staff_attendance.id,staff_attendance.created_at as attendence_dt, staff_attendance.staff_attendance_type_id,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff_attendance.user_agent,staff_attendance.biometric_device_data,staff_attendance.remark,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,staff_attendance.date as date,staff.id as staff_id, staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance_type.long_lang_name,staff_attendance_type.long_name_style  from staff_attendance inner join staff on staff_attendance.staff_id = staff.id left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where staff_attendance.date >= " . $this->db->escape($from_date) . " and staff_attendance.date <= " . $this->db->escape($to_date) . " and staff.is_active = 1 $condition order by staff_attendance.date asc, staff_attendance.created_at asc");
        } else {
            $query = $this->db->query("select staff_attendance.in_time,staff_attendance.out_time,staff_attendance.staff_attendance_type_id,staff_attendance.created_at as attendence_dt,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff_attendance.user_agent,staff_attendance.biometric_device_data,staff_attendance.remark,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,staff_attendance.date as date, staff_attendance.id as id, staff.id as staff_id ,staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance_type.long_lang_name,staff_attendance_type.long_name_style from staff_attendance inner join staff on staff_attendance.staff_id = staff.id left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where roles.name = " . $this->db->escape($user_type) . " and staff_attendance.date >= " . $this->db->escape($from_date) . " and staff_attendance.date <= " . $this->db->escape($to_date) . " and staff.is_active = 1 $condition order by staff_attendance.date asc, staff_attendance.created_at asc");
            
        }
        return $query->result_array();
    }

    public function searchAttendenceUserType($user_type, $date) {
        $condition = '';
        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);       
            $superadmin_visible = $this->customlib->superadmin_visible(); 
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {                 
                $condition = " and roles.id != 7";
            } 
        }
        
        if ($user_type == "select") { 

            $query = $this->db->query("select staff_attendance.out_time,staff_attendance.in_time,staff_attendance.id,staff_attendance.created_at as attendence_dt, staff_attendance.staff_attendance_type_id,staff_attendance.session_attendance_data,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff_attendance.user_agent,staff_attendance.biometric_device_data,staff_attendance.remark,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,IFNULL(staff_attendance.date, 'xxx') as date,staff.id as staff_id, staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance_type.long_lang_name,staff_attendance_type.long_name_style  from staff left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where staff.is_active = 1 $condition");
        } else {
            $query = $this->db->query("select  staff_attendance.out_time,staff_attendance.in_time,staff_attendance.staff_attendance_type_id,staff_attendance.session_attendance_data,staff_attendance.created_at as attendence_dt,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff_attendance.user_agent,staff_attendance.biometric_device_data,staff_attendance.remark,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,IFNULL(staff_attendance.date, 'xxx') as date, IFNULL(staff_attendance.id, 0) as id, staff.id as staff_id ,staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance_type.long_lang_name,staff_attendance_type.long_name_style from staff left join staff_roles on (staff.id = staff_roles.staff_id) left join roles on (roles.id = staff_roles.role_id) left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where roles.name = " . $this->db->escape($user_type) . " and staff.is_active = 1 $condition");            
        }
        return $query->result_array();
    }

    public function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('staff_attendance', $data);
            // Logging removed to prevent excessive logs during bulk operations
            // Individual attendance logs are not needed; summary logs are created at controller level
        } else {
            $this->db->insert('staff_attendance', $data);
            $id = $this->db->insert_id();
            // Logging removed to prevent excessive logs during bulk operations
            // Individual attendance logs are not needed; summary logs are created at controller level
        }
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

    public function getStaffAttendanceType() {
        $query = $this->db->select('*')->where("is_active", 'yes')->get("staff_attendance_type");
        return $query->result_array();
    }

    public function searchAttendanceReport($user_type, $date, $staff_category = '', $staff_department = '') {

        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);       
             
            $superadmin_visible = $this->customlib->superadmin_visible(); 
            $condition = '';
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $condition = "and staff_roles.role_id != 7";       
            } 
        }

        $category_join   = "LEFT JOIN staff_designation sd ON sd.id = staff.designation LEFT JOIN staff_designation_category sdc ON COALESCE(staff.category_id, sd.category_id) = sdc.id LEFT JOIN department dept ON dept.id = staff.department";
        $category_filter = (!empty($staff_category)) ? " AND sdc.name = " . $this->db->escape($staff_category) : '';
        $dept_filter     = (!empty($staff_department)) ? " AND dept.id = " . $this->db->escape($staff_department) : '';
        
        if ($user_type == "select") {
            $query = $this->db->query("select staff_attendance.staff_attendance_type_id,staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance.remark,staff_attendance.in_time,staff_attendance.out_time,staff_attendance.session_attendance_data,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,IFNULL(staff_attendance.date, 'xxx') as date, IFNULL(staff_attendance.id, 0) as attendence_id, staff.id as id, sd.designation as designation_name, dept.department_name from staff left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id $category_join where staff.is_active = 1 $category_filter $dept_filter $condition");
        } else {
            $query = $this->db->query("select staff_attendance.staff_attendance_type_id,staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance.remark,staff_attendance.in_time,staff_attendance.out_time,staff_attendance.session_attendance_data,staff_attendance.biometric_attendence,staff_attendance.qrcode_attendance,staff.name,staff.surname,staff.employee_id,staff.contact_no,staff.email,roles.name as user_type,roles.id as role_id,IFNULL(staff_attendance.date, 'xxx') as date, IFNULL(staff_attendance.id, 0) as attendence_id, staff.id as id, sd.designation as designation_name, dept.department_name from staff  left join staff_roles on (staff.id = staff_roles.staff_id) left join roles on (roles.id = staff_roles.role_id) left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id $category_join where roles.name = '" . $user_type . "' and staff.is_active = 1 $category_filter $dept_filter $condition");
        }

        return $query->result_array();
    }

    public function attendanceYearCount() {
        $query = $this->db->select("distinct year(date) as year")->get("staff_attendance");
        $years = $query->result_array();

        $year_map = [];
        foreach ($years as $row) {
            if (!empty($row['year'])) {
                $year_map[(int)$row['year']] = true;
            }
        }

        $current_year = (int)date('Y');
        for ($i = 0; $i < 5; $i++) {
            $year_map[$current_year - $i] = true;
        }

        $final_years = array_keys($year_map);
        rsort($final_years);

        return array_map(function ($year) {
            return ['year' => $year];
        }, $final_years);
    }

    public function searchStaffattendance($date, $staff_id, $active_staff = true) {

        $sql = "select staff_attendance.staff_attendance_type_id,staff_attendance_type.type as `att_type`,staff_attendance_type.key_value as `key`,staff_attendance.remark,staff.name,staff.surname,staff.contact_no,staff.email,roles.name as user_type,IFNULL(staff_attendance.date, 'xxx') as date, IFNULL(staff_attendance.id, 0) as attendence_id, staff.id as id, TIME_FORMAT(staff_attendance.in_time, '%h:%i %p') as in_time, TIME_FORMAT(staff_attendance.out_time, '%h:%i %p') as out_time from staff left join staff_attendance on (staff.id = staff_attendance.staff_id) and staff_attendance.date = " . $this->db->escape($date) . " left join staff_roles on staff_roles.staff_id = staff.id left join roles on staff_roles.role_id = roles.id left join staff_attendance_type on staff_attendance_type.id = staff_attendance.staff_attendance_type_id where staff.id = " . $this->db->escape($staff_id);
        if ($active_staff || !isset($active_staff)) {
            $sql .= " and staff.is_active = 1";
        }
        $query = $this->db->query($sql);
        return $query->row_array();
    }

    public function getAttendanceRowsInRange($staff_id, $start_date, $end_date)
    {
        $this->db->select('date,in_time,out_time,staff_attendance_type_id,session_attendance_data,biometric_attendence');
        $this->db->from('staff_attendance');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('date >=', $start_date);
        $this->db->where('date <=', $end_date);
        $this->db->order_by('date', 'asc');
        return $this->db->get()->result_array();
    }

    /**
     * Fetch all attendance rows for a range of dates for ALL (or role-filtered) staff
     * in a single query.  Replaces the day-by-day loop in staffattendancereport().
     *
     * Returns rows indexed by [date][staff_id].
     */
    public function searchAttendanceReportForMonth($user_type, $start_date, $end_date, $staff_category = '', $staff_department = '')
    {
        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole        = json_decode($getStaffRole);
            $superadmin_visible = $this->customlib->superadmin_visible();
            $condition = '';
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $condition = "AND staff_roles.role_id != 7";
            }
        } else {
            $condition = '';
        }

        $start = $this->db->escape($start_date);
        $end   = $this->db->escape($end_date);

        if ($user_type == "select") {
            $role_filter = '';
        } else {
            $role_filter = "AND roles.name = " . $this->db->escape($user_type);
        }

        $category_filter = (!empty($staff_category)) ? "AND sdc.name = " . $this->db->escape($staff_category) : '';
        $dept_filter     = (!empty($staff_department)) ? "AND dept.id = " . $this->db->escape($staff_department) : '';

        $sql = "SELECT
                    sa.staff_attendance_type_id,
                    sat.type            AS att_type,
                    sat.key_value       AS `key`,
                    sa.remark,
                    sa.in_time,
                    sa.out_time,
                    sa.session_attendance_data,
                    sa.biometric_attendence,
                    sa.qrcode_attendance,
                    s.name,
                    s.surname,
                    s.employee_id,
                    s.contact_no,
                    s.email,
                    roles.name          AS user_type,
                    roles.id            AS role_id,
                    sa.date,
                    sa.id               AS attendence_id,
                    s.id                AS id
                FROM staff_attendance sa
                JOIN staff s           ON s.id = sa.staff_id AND s.is_active = 1
                LEFT JOIN staff_attendance_type sat ON sat.id = sa.staff_attendance_type_id
                LEFT JOIN staff_roles  ON staff_roles.staff_id = s.id
                LEFT JOIN roles        ON roles.id = staff_roles.role_id
                LEFT JOIN staff_designation sd  ON sd.id = s.designation
                LEFT JOIN staff_designation_category sdc ON COALESCE(s.category_id, sd.category_id) = sdc.id
                LEFT JOIN department dept ON dept.id = s.department
                WHERE sa.date BETWEEN $start AND $end
                $role_filter
                $category_filter
                $dept_filter
                $condition
                ORDER BY sa.date, s.id";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Fetch attendance rows for multiple staff IDs in a single query.
     * Returns [staff_id => [rows]] map.
     */
    public function getAttendanceRowsInRangeMultiStaff(array $staff_ids, $start_date, $end_date)
    {
        if (empty($staff_ids)) {
            return [];
        }
        $ids_str = implode(',', array_map('intval', $staff_ids));
        $sql = "SELECT staff_id, date, in_time, out_time, staff_attendance_type_id,
                       session_attendance_data, biometric_attendence
                FROM staff_attendance
                WHERE staff_id IN ($ids_str)
                  AND date BETWEEN " . $this->db->escape($start_date) . " AND " . $this->db->escape($end_date) . "
                ORDER BY date ASC";
        $rows = $this->db->query($sql)->result_array();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['staff_id']][] = $row;
        }
        return $map;
    }

    /**
     * Delete processed (biometric) attendance rows between two dates inclusive.
     * Returns number of rows affected.
     */
    public function delete_processed_attendance_between_dates($from_date, $to_date)
    {
        $this->db->where('date >=', $from_date);
        $this->db->where('date <=', $to_date);
        $this->db->where('biometric_attendence', 1);
        $this->db->delete('staff_attendance');
        return $this->db->affected_rows();
    }

    public function onlineattendence($data) {

        $this->db->where('staff_id', $data['staff_id']);
        $this->db->where('date', $data['date']);
        $q = $this->db->get('staff_attendance');

        if ($q->num_rows() == 0) {
            $this->db->insert('staff_attendance', $data);
            return ($this->db->affected_rows() != 1) ? false : true;
        }
        return false;
    }

    public function getAttendanceByStaffIdAndDate($staff_id, $date) {
        $this->db->select('id, staff_id, staff_attendance_type_id, session_attendance_data, remark, in_time, out_time, date');
        $this->db->from('staff_attendance');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('date', $date);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return null;
    }

    public function count_late_in_month($staff_id, $date)
    {
        $this->db->select('count(*) as count');
        $this->db->from('staff_attendance');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('staff_attendance_type_id', 2); // 2 is for Late
        $this->db->where('MONTH(date)', date('m', strtotime($date)));
        $this->db->where('YEAR(date)', date('Y', strtotime($date)));
        $query = $this->db->get();
        return $query->row_array();
    }

    public function count_permission_in_month($staff_id, $date)
    {
        $this->db->select('count(*) as count');
        $this->db->from('staff_attendance');
        $this->db->where('staff_id', $staff_id);
        $this->db->where_in('staff_attendance_type_id', [5, 7]); // 5=FHP, 7=SHP
        $this->db->where('MONTH(date)', date('m', strtotime($date)));
        $this->db->where('YEAR(date)', date('Y', strtotime($date)));
        $query = $this->db->get();
        return $query->row_array();
    }
}
