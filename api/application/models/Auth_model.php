<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{

    public $client_service               = "minervaerp";
    public $auth_key                     = "schoolAdmin@";
    public $security_authentication_flag = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('enc_lib');
        $this->load->model(array('user_model', 'setting_model', 'student_model', 'staff_model'));
    }

    public function check_auth_client()
    {
        $client_service = $this->input->get_request_header('Client-Service', true);
        $auth_key       = $this->input->get_request_header('Auth-Key', true);
        if ($client_service == $this->client_service && $auth_key == $this->auth_key) {
            return true;
        } else {
            return json_output(200, array('status' => 0, 'message' => 'Unauthorized.'));
        }
    }

    public function login($username, $password, $app_key)
    {
        $resultdata    = $this->setting_model->getSetting();
        
        if($resultdata->student_panel_login){
            $q = $this->checkLogin($username, $password);
        }else{
            return array('status' => 0, 'message' => 'Your account is suspended'); 
        }
        
        if (empty($q)) {
            // Diagnose staff credentials that are valid in staff table but not mapped to users table for mobile auth.
            $this->db->select('staff.id as staff_id, staff.password as staff_password, staff.is_active as staff_is_active');
            $this->db->from('staff');
            $this->db->group_start();
            $this->db->where('staff.email', $username);
            $this->db->or_where('LOWER(staff.email)', strtolower($username));
            $this->db->or_where('staff.employee_id', $username);
            $this->db->or_where('staff.contact_no', $username);
            $this->db->group_end();
            $staff_diag_query = $this->db->get();

            if ($staff_diag_query->num_rows() > 0) {
                $staff_rows = $staff_diag_query->result();
                foreach ($staff_rows as $staff_row) {
                    $staff_active_val = strtolower(trim((string) $staff_row->staff_is_active));
                    $staff_active = ($staff_active_val === 'yes' || $staff_active_val === '1' || $staff_row->staff_is_active === 1 || $staff_row->staff_is_active === true);
                    if (!$staff_active) {
                        continue;
                    }

                    $is_match = ((string) $staff_row->staff_password === (string) $password);
                    if (!$is_match) {
                        $is_match = $this->enc_lib->passHashDyc($password, $staff_row->staff_password);
                    }

                    if ($is_match) {
                        $recovered_user = $this->recoverStaffUserLogin($staff_row, $username);
                        if (!empty($recovered_user)) {
                            // Retry full login flow after auto-recovering missing users mapping.
                            return $this->login($username, $password, $app_key);
                        }
                        return array('status' => 0, 'message' => 'Staff account is not mapped for mobile login. Please contact administrator.');
                    }
                }
            }

            return array('status' => 0, 'message' => 'Invalid Username or Password');
        } else {

            $active_value = strtolower(trim((string) $q->is_active));
            $is_active = ($active_value === 'yes' || $active_value === '1' || $q->is_active === 1 || $q->is_active === true);

            if ($is_active) {
                if ($q->role == "student") {

                    $result = $this->user_model->read_user_information($q->id);

                    if ($result != false) {

                        $setting_result = $this->setting_model->get();

                        if ($result->currency_id == 0) {
                            $currency_symbol    = $setting_result[0]['currency_symbol'];
                            $currency           = $setting_result[0]['currency'];
                            $currency_short_name           = $setting_result[0]['short_name'];
                             
                        } else {
                             
                            $currencyarray = $this->user_model->getstudentcurrentcurrency($result->user_id);
                            $currency               = $currencyarray[0]->id;
                            $currency_symbol        = $currencyarray[0]->symbol;
                            $currency_short_name        = $currencyarray[0]->short_name;
                        }
                        
                        if ($result->lang_id == 0) {
                            $lang_id    = $setting_result[0]['lang_id'];
                            $language   = $setting_result[0]['language'];
                            $short_code = $setting_result[0]['short_code'];
                        } else {
                            $lang_id    = $result->lang_id;
                            $curentlang = $this->user_model->getstudentcurrentlanguage($result->user_id);
                            $language   = $curentlang[0]->language;
                            $short_code = $curentlang[0]->short_code;
                        }

                        if ($result->role == "student") {

                            $last_login = date('Y-m-d H:i:s');
                            $token      = $this->getToken();
                            $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));
                            $this->db->trans_start();
                            $this->db->insert('users_authentication', array('users_id' => $q->id, 'token' => $token, 'expired_at' => $expired_at));

                            $updateData = array(
                                'app_key' => $app_key,
                            );

                            $this->db->where('id', $result->user_id);
                            $this->db->update('students', $updateData);
                            $fullname = getFullName($result->firstname, $result->middlename, $result->lastname, $setting_result[0]['middlename'], $setting_result[0]['lastname']);

                            if (empty($fullname)) {$fullname = '';}

                            $session_data = array(
                                'id'              => $result->id,
                                'student_id'      => $result->user_id,
                                'admission_no'    => $result->admission_no,
                                'role'            => $result->role,
                                'mobileno'        => $result->mobileno,
                                'email'           => $result->email,
                                'username'        => $fullname,
                                'class'           => $result->class,
                                'class_id'        => $result->class_id,
                                'section'         => $result->section,
                                'section_id'      => $result->section_id,
                                'date_format'     => $setting_result[0]['date_format'],
                                'currency_symbol' => $currency_symbol,
                                'currency_short_name'      => $currency_short_name,
                                'currency_id'     => $currency,                                
                                'timezone'        => $setting_result[0]['timezone'],
                                'sch_name'        => $setting_result[0]['name'],
                                'language'        => array('lang_id' => $lang_id, 'language' => $language, 'short_code' => $short_code),
                                'is_rtl'          => $setting_result[0]['is_rtl'],
                                'theme'           => $setting_result[0]['theme'],
                                'image'           => $result->image,
                                'student_session_id'           => $result->student_session_id,
                                'start_week'      => $setting_result[0]['start_week'],
                                'superadmin_restriction'      => $setting_result[0]['superadmin_restriction'],
                            );
                            $this->session->set_userdata('student', $session_data);
                            if ($this->db->trans_status() === false) {
                                $this->db->trans_rollback();

                                return array('status' => 0, 'message' => 'Internal server error.');
                            } else {
                                $this->db->trans_commit();
                                return array('status' => 1, 'message' => 'Successfully login.', 'id' => $q->id, 'token' => $token, 'role' => $q->role, 'record' => $session_data);
                            }
                        }
                    } else {
                        return array('status' => 0, 'message' => 'Your account is suspended');
                    }
                } else if ($q->role == "parent") {
                    $login_post = array(
                        'username' => $username,
                        'password' => $password,
                    );                  
                    
                        $resultdata    = $this->setting_model->getSetting();                    
         
                        if ($resultdata->parent_panel_login) {
                            $result = $this->user_model->checkLoginParent($login_post);
                        } else {
                            $result = false;
                        }                   
                    
                    if ($result != false) {
                        
                        
                    $curentlang = $this->user_model->getstudentcurrentlanguage($result->id);
                    $setting_result = $this->setting_model->get();

                    if (empty($curentlang)) {
                        $lang_id    = $setting_result[0]['lang_id'];
                        $language   = $setting_result[0]['language'];
                        $short_code = $setting_result[0]['short_code'];
                    } else {
                        $lang_id    = $curentlang[0]->lang_id;
                        $language   = $curentlang[0]->language;
                        $short_code = $curentlang[0]->short_code;
                    }

                    if ($result->role == "parent") {                        

                        $last_login = date('Y-m-d H:i:s');
                        $token      = $this->getToken();
                        $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));

                        $this->db->insert('users_authentication', array('users_id' => $q->id, 'token' => $token, 'expired_at' => $expired_at));

                        if ($result->guardian_relation == "Father") {
                            $image = $result->father_pic;
                        } else if ($result->guardian_relation == "Mother") {
                            $image = $result->mother_pic;
                        } else {
                            $image = $result->guardian_pic;
                        }

                        $guardian_name = $result->guardian_name;
                        if (empty($guardian_name)) {$guardian_name = '';}

                        $session_data = array(
                            'id'              => $result->id,
                            'role'            => $result->role,
                            'username'        => $guardian_name,
                            'student_session_id'           => $result->student_session_id,
                            'date_format'     => $setting_result[0]['date_format'],
                            'timezone'        => $setting_result[0]['timezone'],
                            'sch_name'        => $setting_result[0]['name'],
                            'currency_symbol' => $setting_result[0]['currency_symbol'],
                            'currency_short_name' => $setting_result[0]['currency_short_name'],                        
                            'language'        => array('lang_id' => $lang_id, 'language' => $language, 'short_code' => $short_code),
                            'is_rtl'          => $setting_result[0]['is_rtl'],
                            'theme'           => $setting_result[0]['theme'],
                            'image'           => $image,
                            'start_week'      => $setting_result[0]['start_week'],
                            'superadmin_restriction'      => $setting_result[0]['superadmin_restriction'],
                        );

                        $user_id        = ($result->id);
                        $students_array = $this->student_model->read_siblings_students($user_id);
                        $child_student  = array();
                        $update_student = array();
                        foreach ($students_array as $std_key => $std_val) {
                            $child = array(
                                'student_id' => $std_val->id,
                                'class'      => $std_val->class,
                                'section'    => $std_val->section,
                                'class_id'   => $std_val->class_id,
                                'section_id' => $std_val->section_id,
                                'name'       => $std_val->firstname . " " . $std_val->lastname,
                                'image'      => $std_val->image,
                                'student_session_id'      => $std_val->student_session_id,
								'admission_no'      => $std_val->admission_no,
                            );
                            $child_student[] = $child;
                            $stds            = array(
                                'id'             => $std_val->id,
                                'parent_app_key' => $app_key,
                            );
                            $update_student[] = $stds;
                        }
                        if (!empty($update_student)) {
                            $this->db->update_batch('students', $update_student, 'id');
                        }

                        $session_data['parent_childs'] = $child_student;
                        $this->session->set_userdata('student', $session_data);

                        return array('status' => 1, 'message' => 'Successfully login.', 'id' => $q->id, 'token' => $token, 'role' => $q->role, 'record' => $session_data);
                        
                    }else{
                        return array('status' => 0, 'message' => 'Invalid Username or Password');
                    }
                    
                    }else{
                        return array('status' => 0, 'message' => 'Your account is suspended');
                    }

                } else {
                    // Staff/teacher and other non-student, non-parent roles.
                    $setting_result = $this->setting_model->get();

                    $this->db->select('users.id as user_login_id, users.user_id as staff_id, users.role, users.lang_id, staff.name, staff.surname, staff.email, staff.contact_no, staff.employee_id, staff.image, staff_designation.designation, department.department_name');
                    $this->db->from('users');
                    $this->db->join('staff', 'staff.id = users.user_id', 'left');
                    $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
                    $this->db->join('department', 'department.id = staff.department', 'left');
                    $this->db->where('users.id', $q->id);
                    $staff_result = $this->db->get()->row();

                    if (empty($staff_result)) {
                        // Fallback for legacy/inconsistent users.user_id mappings.
                        $staff_lookup = $this->getStaffByLoginIdentifier($username);
                        if (empty($staff_lookup) && !empty($q->username) && strcasecmp((string) $q->username, (string) $username) !== 0) {
                            $staff_lookup = $this->getStaffByLoginIdentifier($q->username);
                        }

                        if (!empty($staff_lookup)) {
                            $staff_result = (object) array(
                                'user_login_id'    => $q->id,
                                'staff_id'         => $staff_lookup->id,
                                'role'             => $q->role,
                                'lang_id'          => $q->lang_id,
                                'name'             => $staff_lookup->name,
                                'surname'          => $staff_lookup->surname,
                                'email'            => $staff_lookup->email,
                                'contact_no'       => $staff_lookup->contact_no,
                                'employee_id'      => $staff_lookup->employee_id,
                                'image'            => $staff_lookup->image,
                                'designation'      => $staff_lookup->designation,
                                'department_name'  => $staff_lookup->department_name,
                            );
                        }
                    }

                    if (empty($staff_result)) {
                        return array('status' => 0, 'message' => 'Staff profile not found.');
                    }

                    if ($staff_result->lang_id == 0) {
                        $lang_id = $setting_result[0]['lang_id'];
                        $language = $setting_result[0]['language'];
                        $short_code = $setting_result[0]['short_code'];
                    } else {
                        $lang_id = $staff_result->lang_id;
                        $curentlang = $this->user_model->getstudentcurrentlanguage($staff_result->staff_id);
                        if (!empty($curentlang)) {
                            $language = $curentlang[0]->language;
                            $short_code = $curentlang[0]->short_code;
                        } else {
                            $language = $setting_result[0]['language'];
                            $short_code = $setting_result[0]['short_code'];
                        }
                    }

                    $token = $this->getToken();
                    $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));
                    $this->db->insert('users_authentication', array('users_id' => $q->id, 'token' => $token, 'expired_at' => $expired_at));

                    $full_name = trim(($staff_result->name ? $staff_result->name : '') . ' ' . ($staff_result->surname ? $staff_result->surname : ''));
                    if ($full_name === '') {
                        $full_name = $q->username;
                    }

                    $session_data = array(
                        'id' => $staff_result->user_login_id,
                        'staff_id' => $staff_result->staff_id,
                        'role' => $q->role,
                        'username' => $full_name,
                        'name' => $staff_result->name,
                        'surname' => $staff_result->surname,
                        'email' => $staff_result->email,
                        'mobileno' => $staff_result->contact_no,
                        'employee_id' => $staff_result->employee_id,
                        'designation' => $staff_result->designation,
                        'department' => $staff_result->department_name,
                        'date_format' => $setting_result[0]['date_format'],
                        'timezone' => $setting_result[0]['timezone'],
                        'sch_name' => $setting_result[0]['name'],
                        'language' => array('lang_id' => $lang_id, 'language' => $language, 'short_code' => $short_code),
                        'is_rtl' => $setting_result[0]['is_rtl'],
                        'theme' => $setting_result[0]['theme'],
                        'image' => $staff_result->image,
                        'start_week' => $setting_result[0]['start_week'],
                        'superadmin_restriction' => $setting_result[0]['superadmin_restriction'],
                    );

                    $this->session->set_userdata('student', $session_data);

                    return array('status' => 1, 'message' => 'Successfully login.', 'id' => $q->id, 'token' => $token, 'role' => $q->role, 'record' => $session_data);
                }
            } else {
                return array('status' => '0', 'message' => 'Your account is disabled please contact to administrator');
            }
        }
    }

    public function checkLogin($username, $password)
    {
        $resultdata    = $this->setting_model->get();
        $student_login = json_decode($resultdata[0]['student_login']);
        $parent_login  = json_decode($resultdata[0]['parent_login']);
        
        $this->db->select('users.id as id, username, password,role,users.is_active as is_active,lang_id');
        $this->db->from('users');
        $this->db->join('students', 'students.id = users.user_id');
        $this->db->where('password', $password);
        
        $this->db->group_start();        
        $this->db->where('username', $username); 
        
        if(!empty($student_login)){
            if (in_array("admission_no", $student_login)) {
                $this->db->or_where('students.admission_no', $username);
            }
            if (in_array("mobile_number", $student_login)) {
                $this->db->or_where('students.mobileno', $username);
            }
            if (in_array("email", $student_login)) {
                $this->db->or_where('students.email', $username);
            }
        }
        
        $this->db->group_end();
        
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        } else {

            $this->db->select('users.id as id, username, password,role,users.is_active as is_active,lang_id');
            $this->db->from('users');
            $this->db->join('students', 'students.parent_id = users.id');
            $this->db->where('password', $password);                       
            
            $this->db->group_start();            
            $this->db->where('username', $username); 
            
            if(!empty($parent_login)){
                if (in_array("mobile_number", $parent_login)) {
                    $this->db->or_where('students.guardian_phone', $username);
                }
                if (in_array("email", $parent_login)) {
                    $this->db->or_where('students.guardian_email', $username);
                }
            }
            
            $this->db->group_end();
            
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows() == 1) {
                return $query->row();
            } else {
                // Staff/teacher fallback login.
                // Browser login validates against staff.password. Mirror that behavior here,
                // then map the matched staff user to its corresponding users row.
                $this->db->select('staff.id as staff_id, staff.password as staff_password, staff.email, staff.employee_id, staff.contact_no, staff.is_active as staff_is_active');
                $this->db->from('staff');
                $this->db->group_start();
                $this->db->where('staff.email', $username);
                $this->db->or_where('LOWER(staff.email)', strtolower($username));
                $this->db->or_where('staff.employee_id', $username);
                $this->db->or_where('staff.contact_no', $username);
                $this->db->group_end();
                $staff_query = $this->db->get();

                if ($staff_query->num_rows() > 0) {
                    $staff_rows = $staff_query->result();
                    foreach ($staff_rows as $staff_row) {
                        $staff_active_val = strtolower(trim((string) $staff_row->staff_is_active));
                        $staff_active = ($staff_active_val === 'yes' || $staff_active_val === '1' || $staff_row->staff_is_active === 1 || $staff_row->staff_is_active === true);
                        if (!$staff_active) {
                            continue;
                        }

                        $is_match = ((string) $staff_row->staff_password === (string) $password);
                        if (!$is_match) {
                            $is_match = $this->enc_lib->passHashDyc($password, $staff_row->staff_password);
                        }

                        if (!$is_match) {
                            continue;
                        }

                        $this->db->select('users.id as id, users.username, users.password, users.role, users.is_active as is_active, users.lang_id');
                        $this->db->from('users');
                        $this->db->where('users.user_id', $staff_row->staff_id);
                        $this->db->where_not_in('users.role', array('student', 'parent'));
                        $this->db->limit(1);
                        $user_query = $this->db->get();

                        if ($user_query->num_rows() == 1) {
                            return $user_query->row();
                        }

                        // Fallback: some installations keep staff login in users.username
                        // while users.user_id may be stale or null.
                        $this->db->select('users.id as id, users.username, users.password, users.role, users.is_active as is_active, users.lang_id');
                        $this->db->from('users');
                        $this->db->where_not_in('users.role', array('student', 'parent'));
                        $this->db->group_start();
                        $this->db->where('users.username', $username);
                        if (!empty($staff_row->email)) {
                            $this->db->or_where('users.username', $staff_row->email);
                        }
                        if (!empty($staff_row->employee_id)) {
                            $this->db->or_where('users.username', $staff_row->employee_id);
                        }
                        if (!empty($staff_row->contact_no)) {
                            $this->db->or_where('users.username', $staff_row->contact_no);
                        }
                        $this->db->group_end();
                        $this->db->order_by('users.id', 'DESC');
                        $this->db->limit(1);
                        $user_query = $this->db->get();

                        if ($user_query->num_rows() == 1) {
                            return $user_query->row();
                        }

                        $recovered_user = $this->recoverStaffUserLogin($staff_row, $username);
                        if (!empty($recovered_user)) {
                            return $recovered_user;
                        }
                    }
                }

                return false;
            }
        }
    }

    private function getStaffByLoginIdentifier($identifier)
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        $this->db->select('staff.id, staff.name, staff.surname, staff.email, staff.contact_no, staff.employee_id, staff.image, staff_designation.designation, department.department_name');
        $this->db->from('staff');
        $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
        $this->db->join('department', 'department.id = staff.department', 'left');
        $this->db->group_start();
        $this->db->where('staff.email', $identifier);
        $this->db->or_where('LOWER(staff.email)', strtolower($identifier));
        $this->db->or_where('staff.employee_id', $identifier);
        $this->db->or_where('staff.contact_no', $identifier);
        $this->db->group_end();
        $this->db->order_by('staff.id', 'DESC');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    private function recoverStaffUserLogin($staffRow, $loginIdentifier)
    {
        if (empty($staffRow) || empty($staffRow->staff_id)) {
            return null;
        }

        $staff_id = (int) $staffRow->staff_id;

        // Prefer an existing non-student/parent user tied to this staff.
        $this->db->select('users.id as id, users.username, users.password, users.role, users.is_active as is_active, users.lang_id');
        $this->db->from('users');
        $this->db->where('users.user_id', $staff_id);
        $this->db->where_not_in('users.role', array('student', 'parent'));
        $this->db->order_by('users.id', 'DESC');
        $this->db->limit(1);
        $existing_mapped = $this->db->get();
        if ($existing_mapped->num_rows() == 1) {
            return $existing_mapped->row();
        }

        // Resolve a staff role from staff_roles for created users row.
        $this->db->select('roles.name as role_name');
        $this->db->from('staff_roles');
        $this->db->join('roles', 'roles.id = staff_roles.role_id', 'inner');
        $this->db->where('staff_roles.staff_id', $staff_id);
        $this->db->where_not_in('roles.name', array('student', 'parent'));
        $this->db->order_by('staff_roles.role_id', 'ASC');
        $this->db->limit(1);
        $role_row = $this->db->get()->row();
        $role_name = !empty($role_row->role_name) ? strtolower(trim($role_row->role_name)) : 'teacher';

        $candidates = array();
        if (!empty($loginIdentifier)) {
            $candidates[] = $loginIdentifier;
        }
        if (!empty($staffRow->email)) {
            $candidates[] = $staffRow->email;
        }
        if (!empty($staffRow->employee_id)) {
            $candidates[] = $staffRow->employee_id;
        }
        if (!empty($staffRow->contact_no)) {
            $candidates[] = $staffRow->contact_no;
        }
        $candidates[] = 'staff_' . $staff_id;

        $chosen_username = null;
        foreach (array_unique($candidates) as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }
            $this->db->select('id, role');
            $this->db->from('users');
            $this->db->where('username', $candidate);
            $this->db->limit(1);
            $u = $this->db->get()->row();
            if (empty($u)) {
                $chosen_username = $candidate;
                break;
            }

            // Reuse existing non-student/parent username login if present.
            if (!in_array($u->role, array('student', 'parent'), true)) {
                $this->db->select('users.id as id, users.username, users.password, users.role, users.is_active as is_active, users.lang_id');
                $this->db->from('users');
                $this->db->where('users.id', (int) $u->id);
                $this->db->limit(1);
                $existing = $this->db->get();
                if ($existing->num_rows() == 1) {
                    return $existing->row();
                }
            }
        }

        if ($chosen_username === null) {
            return null;
        }

        $new_user = array(
            'username'  => $chosen_username,
            'password'  => $staffRow->staff_password,
            'user_id'   => $staff_id,
            'role'      => $role_name,
            'is_active' => 1,
            'lang_id'   => 0,
        );

        $this->db->insert('users', $new_user);
        $new_id = (int) $this->db->insert_id();
        if ($new_id <= 0) {
            return null;
        }

        $this->db->select('users.id as id, users.username, users.password, users.role, users.is_active as is_active, users.lang_id');
        $this->db->from('users');
        $this->db->where('users.id', $new_id);
        $this->db->limit(1);
        $created = $this->db->get();

        return ($created->num_rows() == 1) ? $created->row() : null;
    }

    public function getToken($randomIdLength = 10)
    {
        $token = '';
        do {
            $bytes = rand(1, $randomIdLength);
            $token .= str_replace(
                ['.', '/', '='], '', base64_encode($bytes)
            );
        } while (strlen($token) < $randomIdLength);
        return $token;
    }

    public function logout($deviceToken)
    {
        $users_id = $this->input->get_request_header('User-ID', true);
        $token    = $this->input->get_request_header('Authorization', true);
        $this->session->unset_userdata('student');
        $this->session->sess_destroy();
        $this->db->where('app_key', $deviceToken)->update('students', array('app_key' => null));
        $this->db->where('users_id', $users_id)->where('token', $token)->delete('users_authentication');
        return array('status' => 200, 'message' => 'Successfully logout.');
    }

    public function auth()
    {
        if ($this->security_authentication_flag) {
            $users_id = $this->input->get_request_header('User-ID', true);
            $token    = $this->input->get_request_header('Authorization', true);
            $q        = $this->db->select('expired_at')->from('users_authentication')->where('users_id', $users_id)->where('token', $token)->get()->row();
            if ($q == "") {
                return json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
            } else {
                if ($q->expired_at < date('Y-m-d H:i:s')) {
                    return json_output(401, array('status' => 401, 'message' => 'Your session has been expired.'));
                } else {
                    $updated_at = date('Y-m-d H:i:s');
                    $expired_at = date("Y-m-d H:i:s", strtotime('+8760 hours'));
                    $this->db->where('users_id', $users_id)->where('token', $token)->update('users_authentication', array('expired_at' => $expired_at, 'updated_at' => $updated_at));
                    return array('status' => 200, 'message' => 'Authorized.');
                }
            }
        } else {
            return array('status' => 200, 'message' => 'Authorized.');
        }
    }

}
