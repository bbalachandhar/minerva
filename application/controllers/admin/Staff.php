<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staff extends Admin_Controller
{

    public $sch_setting_detail = array();

    public function __construct()
    {
        parent::__construct();
		$this->load->library('SaasValidation');
        $this->load->library('media_storage');
        $this->load->library('media_storage');
        $this->config->load("payroll");
        $this->config->load("app-config");
        $this->load->library('Enc_lib');
        $this->load->library('mailsmsconf');
        $this->load->model("staff_model");
        $this->load->library('encoding_lib');
        $this->load->model("leaverequest_model");
        $this->contract_type      = $this->config->item('contracttype');
        $this->marital_status     = $this->config->item('marital_status');
        $this->staff_attendance   = $this->config->item('staffattendance');
        $this->payroll_status     = $this->config->item('payroll_status');
        $this->payment_mode       = $this->config->item('payment_mode');
        $this->status             = $this->config->item('status');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('staff', 'can_view')) {
            access_denied();
        }

        $data['title']  = $this->lang->line('staff_list');
        $data['fields'] = $this->customfield_model->get_custom_fields('staff', 1);
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff');
        $search             = $this->input->post("search");
        $resultlist         = $this->staff_model->searchFullText("", 1);
        $data['resultlist'] = $resultlist;
        $staffRole          = $this->staff_model->getStaffRole();
        $data["role"]       = $staffRole;
        $data["role_id"]    = "";
        $search_text        = $this->input->post('search_text');
        if (isset($search)) {
            if ($search == 'search_filter') {
                $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
                if ($this->form_validation->run() == false) {
                    $data["resultlist"] = array();
                } else {
                    $data['searchby']    = "filter";
                    $role                = $this->input->post('role');
                    $data['employee_id'] = $this->input->post('empid');
                    $data["role_id"]     = $role;
                    $data['search_text'] = $this->input->post('search_text');
                    $resultlist          = $this->staff_model->getEmployee($role, 1);
                    $data['resultlist']  = $resultlist;
                }
            } else if ($search == 'search_full') {
                $data['searchby']    = "text";
                $data['search_text'] = trim($this->input->post('search_text'));
                $resultlist          = $this->staff_model->searchFullText($search_text, 1);
                $data['resultlist']  = $resultlist;
                $data['title']       = $this->lang->line('search_details') . ': ' . $data['search_text'];
            }
        }

        $this->load->view('layout/header');
        $this->load->view('admin/staff/staffsearch', $data);
        $this->load->view('layout/footer');
    }

    public function disablestafflist()
    {
        if (!$this->rbac->hasPrivilege('disable_staff', 'can_view')) {
            access_denied();
        }

        if (isset($_POST['role']) && $_POST['role'] != '') {
            $data['search_role'] = $_POST['role'];
        } else {
            $data['search_role'] = "";
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff/disablestafflist');
        $data['title'] = 'Staff Search';
        $staffRole     = $this->staff_model->getStaffRole();

        $data["role"]       = $staffRole;
        $search             = $this->input->post("search");
        $search_text        = $this->input->post('search_text');
        $resultlist         = $this->staff_model->searchFullText('', 0);
        $data['resultlist'] = $resultlist;

        if (isset($search)) {
            if ($search == 'search_filter') {
                $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
                if ($this->form_validation->run() == false) {
                    $resultlist         = array();
                    $data['resultlist'] = $resultlist;
                } else {
                    $data['searchby']    = "filter";
                    $role                = $this->input->post('role');
                    $data['employee_id'] = $this->input->post('empid');
                    $data['search_text'] = $this->input->post('search_text');
                    $resultlist          = $this->staff_model->getEmployee($role, 0);
                    $data['resultlist']  = $resultlist;
                }
            } else if ($search == 'search_full') {
                $data['searchby']    = "text";
                $data['search_text'] = trim($this->input->post('search_text'));
                $resultlist          = $this->staff_model->searchFullText($search_text, 0);
                $data['resultlist']  = $resultlist;
                $data['title']       = 'Search Details: ' . $data['search_text'];
            }
        }
        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/disablestaff', $data);
        $this->load->view('layout/footer', $data);
    }

    public function profile($id)
    {
        $data['enable_disable'] = 1;
        if ($this->customlib->getStaffID() == $id) {
            $data['enable_disable'] = 0;
        } else if (!$this->rbac->hasPrivilege('staff', 'can_view')) {
            access_denied();
        }

        $this->load->model("staffattendancemodel");
        $this->load->model("setting_model");
        $data["id"]      = $id;
        $data['title']   = 'Staff Details';
        $staff_info      = $this->staff_model->getProfile($id);
        $userdata        = $this->customlib->getUserData();
        $userid          = $userdata['id'];
        $timeline_status = '';
        if ($userid == $id) {
            $timeline_status = 'yes';
        }
        $timeline_list         = $this->timeline_model->getStaffTimeline($id, $timeline_status);
        $data["timeline_list"] = $timeline_list;
        $staff_payroll         = $this->staff_model->getStaffPayroll($id);
        $staff_leaves          = $this->leaverequest_model->staff_leave_request($id);

        $alloted_leavetype           = $this->staff_model->allotedLeaveType($id);
        $data['sch_setting']         = $this->sch_setting_detail;
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $this->load->model("payroll_model");
        $salary = $this->payroll_model->getSalaryDetails($id);

        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;
        $i                           = 0;
        $leaveDetail                 = array();
        foreach ($alloted_leavetype as $key => $value) {
            $count_leaves[]                   = $this->leaverequest_model->countLeavesData($id, $value["leave_type_id"]);
            $leaveDetail[$i]['type']          = $value["type"];
            $leaveDetail[$i]['alloted_leave'] = $value["alloted_leave"];
            $leaveDetail[$i]['approve_leave'] = $count_leaves[$i]['approve_leave'];
            $i++;
        }
        $data["leavedetails"]  = $leaveDetail;
        $data["staff_leaves"]  = $staff_leaves;
        $data['staff_doc_id']  = $id;
        $data['staff']         = $staff_info;
        $data['staff_payroll'] = $staff_payroll;
        $data['salary']        = $salary;
        $monthlist             = $this->customlib->getMonthDropdown();
        $startMonth            = $this->setting_model->getStartMonth();
        $data["monthlist"]     = $monthlist;
        $data['yearlist']      = $this->staffattendancemodel->attendanceYearCount();
        $session_current       = $this->setting_model->getCurrentSessionName();
        $startMonth            = $this->setting_model->getStartMonth();
        $centenary             = substr($session_current, 0, 2); //2017-18 to 2017
        $year_first_substring  = substr($session_current, 2, 2); //2017-18 to 2017
        $year_second_substring = substr($session_current, 5, 2); //2017-18 to 18
        $month_number          = date("m", strtotime($startMonth));
        $data['rate_canview']  = 0;

        if ($id != '1') {
            $staff_rating = $this->staff_model->staff_ratingById($id);

            if ($staff_rating['total'] >= 3) {
                $data['rate'] = ($staff_rating['rate'] / $staff_rating['total']);
                $data['rate_canview'] = 1;
            }
            $data['reviews'] = $staff_rating['total'];
        }

        $data['reviews_comment'] = $this->staff_model->staff_ratingById($id);
        $year = date("Y");

        $staff_list              = $this->staff_model->user_reviewlist($id);
        $data['user_reviewlist'] = $staff_list;

        $attendence_count = array();
        $attendencetypes  = $this->attendencetype_model->getStaffAttendanceType();
        foreach ($attendencetypes as $att_key => $att_value) {
            $attendence_count[$att_value['type']] = array();
        }

        foreach ($monthlist as $key => $value) {
            $datemonth       = date("m", strtotime($key));
            $date_each_month = date('Y-' . $datemonth . '-01');

            $date_start = date('01', strtotime($date_each_month));
            $date_end   = date('t', strtotime($date_each_month));
            for ($n = $date_start; $n <= $date_end; $n++) {
                $att_dates        = $year . "-" . $datemonth . "-" . sprintf("%02d", $n);
                $date_array[]     = $att_dates;
                $staff_attendence = $this->staffattendancemodel->searchStaffattendance($att_dates, $id, false);

                if (!empty($staff_attendence)) {
                    if ($staff_attendence['att_type'] != "") {
                        $attendence_count[$staff_attendence['att_type']][] = 1;
                    }
                } else {

                }
                $res[$att_dates] = $staff_attendence;
            }
        }

        $session       = $this->setting_model->getCurrentSessionName();
        $session_start = explode("-", $session);
        $start_year    = $session_start[0];
        $date          = $start_year . "-" . $startMonth;
        $newdate       = date("Y-m-d", strtotime($date . "+1 month"));

        $data["countAttendance"]  = $attendence_count;
        $data["resultlist"]       = $res;
        $data["attendence_array"] = range(01, 31);
        $data["date_array"]       = $date_array;
        $data["payroll_status"]   = $this->payroll_status;
        $data["payment_mode"]     = $this->payment_mode;
        $data["contract_type"]    = $this->contract_type;
        $data["status"]           = $this->status;
        $roles                    = $this->role_model->get();
        $data["roles"]            = $roles;
        $stafflist                = $this->staff_model->get();
        $data['stafflist']        = $stafflist;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffprofile', $data);
        $this->load->view('layout/footer', $data);
    }

    public function countAttendance($year, $emp)
    {
        $record = array();

        foreach ($this->staff_attendance as $att_key => $att_value) {
            $s           = $this->staff_model->count_attendance($year, $emp, $att_value);
            $r[$att_key] = $s;
        }

        $record[$year] = $r;
        return $record;
    }

    public function getSession()
    {
        $session             = $this->session_model->getAllSession();
        $data                = array();
        $session_array       = $this->session->has_userdata('session_array');
        $data['sessionData'] = array('session_id' => 0);
        if ($session_array) {
            $data['sessionData'] = $this->session->userdata('session_array');
        } else {
            $setting             = $this->setting_model->get();
            $data['sessionData'] = array('session_id' => $setting[0]['session_id']);
        }
        $data['sessionList'] = $session;

        return $data;
    }

    public function getSessionMonthDropdown()
    {
        $startMonth = $this->setting_model->getStartMonth();
        $array      = array();
        for ($m = $startMonth; $m <= $startMonth + 11; $m++) {
            $month         = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
            $array[$month] = $month;
        }
        return $array;
    }

    public function download($staff_id, $doc)
    {
        $stafflist = $this->staff_model->getProfile($staff_id);
        $this->media_storage->filedownload($stafflist[$doc], "./uploads/staff_documents/$staff_id");
    }

    public function doc_delete($id, $doc)
    {
        $this->staff_model->doc_delete($id, $doc);
        $this->session->set_flashdata('msg', '<i class="fa fa-check-square-o" aria-hidden="true"></i>' . $this->lang->line('delete_message') . '');
        redirect('admin/staff/profile/' . $id);
    }

    public function ajax_attendance()
    {
        $this->load->model("staffattendancemodel");
        $attendencetypes             = $this->staffattendancemodel->getStaffAttendanceType();
        $data['attendencetypeslist'] = $attendencetypes;

        $id           = $this->input->post("id");
        $year         = $this->input->post("year");
        $data["year"] = $year;
        if (!empty($year)) {

            $monthlist         = $this->customlib->getMonthDropdown();
            $startMonth        = $this->setting_model->getStartMonth();
            $data["monthlist"] = $monthlist;
            $data['yearlist']  = $this->staffattendancemodel->attendanceYearCount();
            $session_current   = $this->setting_model->getCurrentSessionName();
            $startMonth        = $this->setting_model->getStartMonth();

            foreach ($monthlist as $key => $value) {
                $datemonth       = date("m", strtotime($key));
                $date_each_month = date('Y-' . $datemonth . '-01');
                $date_end        = date('t', strtotime($date_each_month));
                for ($n = 1; $n <= $date_end; $n++) {
                    $att_date           = sprintf("%02d", $n);
                    $attendence_array[] = $att_date;
                    $datemonth          = date("m", strtotime($key));
                    $att_dates          = $year . "-" . $datemonth . "-" . sprintf("%02d", $n);

                    $date_array[]    = $att_dates;
                    $res[$att_dates] = $this->staffattendancemodel->searchStaffattendance($att_dates, $id);
                }
            }
            $date    = $year . "-" . $startMonth;
            $newdate = date("Y-m-d", strtotime($date . "+1 month"));
            $countAttendance         = $this->countAttendance($year, $id);
            $data["countAttendance"] = $countAttendance;         
            $data["id"]               = $id;
            $data["resultlist"]       = $res;
            $data["attendence_array"] = $attendence_array;
            $data["date_array"]       = $date_array;

            $page = $this->load->view("admin/staff/ajaxattendance", $data, true);
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode(array(
                    'status'          => 1,
                    'countAttendance' => $countAttendance[$year],
                    'page'            => $page,
                )));
        }
    }

    public function create()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staff');
        $data['title'] = 'Add Staff';
        $data['staffid_auto_insert'] = $this->sch_setting_detail->staffid_auto_insert;
        $data['sch_setting'] = $this->sch_setting_detail;

        $data['roles'] = $this->role_model->get();
        $data['designation'] = $this->staff_model->getStaffDesignation();
        $data['department'] = $this->staff_model->getDepartment();
        $genderList                  = $this->customlib->getGender();
        $data['genderList']          = $genderList;
        $payscaleList                = $this->staff_model->getPayroll();
        $leavetypeList               = $this->staff_model->getLeaveType();
        $data["leavetypeList"]       = $leavetypeList;
        $data["payscaleList"]        = $payscaleList;
        $marital_status              = $this->marital_status;
        $data["marital_status"]      = $marital_status;
        $data["contract_type"]       = $this->contract_type;
        $custom_fields               = $this->customfield_model->getByBelong('staff');
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation']) {
                $custom_fields_id   = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[staff][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }

        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        $this->form_validation->set_rules('first_doc', $this->lang->line('image'), 'callback_handle_first_upload');
        $this->form_validation->set_rules('second_doc', $this->lang->line('image'), 'callback_handle_second_upload');
        $this->form_validation->set_rules('third_doc', $this->lang->line('image'), 'callback_handle_third_upload');
        $this->form_validation->set_rules('fourth_doc', $this->lang->line('image'), 'callback_handle_fourth_upload');
        
        $this->form_validation->set_rules(
            'email', $this->lang->line('email'), array('required', 'valid_email',
                array('check_exists', array($this->staff_model, 'valid_email_id')),
            )
        );
        if (!$this->sch_setting_detail->staffid_auto_insert) {
            $this->form_validation->set_rules('employee_id', $this->lang->line('staff_id'), 'callback_username_check');
        }

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');

        if ($this->form_validation->run() == true) {

            $custom_field_post  = $this->input->post("custom_fields[staff]");
            $custom_value_array = array();
            if (!empty($custom_fields)) {
                foreach ($custom_field_post as $key => $value) {
                    $check_field_type = $this->input->post("custom_fields[staff][" . $key . "]");
                    $field_value      = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                    $array_custom     = array(
                        'belong_table_id' => 0,
                        'custom_field_id' => $key,
                        'field_value'     => $field_value,
                    );
                    $custom_value_array[] = $array_custom;
                }
            }

            $employee_id       = $this->input->post("employee_id");
            $department        = empty2null($this->input->post("department"));
            $designation       = empty2null($this->input->post("designation"));
            $role              = $this->input->post("role");
            $name              = $this->input->post("name");
            $gender            = $this->input->post("gender");
            $marital_status    = $this->input->post("marital_status");
            $dob               = $this->input->post("dob");
            $contact_no        = $this->input->post("contactno");
            $emergency_no      = $this->input->post("emergency_no");
            $email             = $this->input->post("email");
            $date_of_joining   = $this->input->post("date_of_joining");
            $date_of_leaving   = $this->input->post("date_of_leaving");
            $address           = $this->input->post("address");
            $qualification     = $this->input->post("qualification");
            $work_exp          = $this->input->post("work_exp");
            $basic_salary      = $this->input->post('basic_salary');
            $account_title     = $this->input->post("account_title");
            $bank_account_no   = $this->input->post("bank_account_no");
            $bank_name         = $this->input->post("bank_name");
            $ifsc_code         = $this->input->post("ifsc_code");
            $bank_branch       = $this->input->post("bank_branch");
            $contract_type     = $this->input->post("contract_type");
            $shift             = $this->input->post("shift");
            $location          = $this->input->post("location");
            $leave             = $this->input->post("leave");
            $facebook          = $this->input->post("facebook");
            $twitter           = $this->input->post("twitter");
            $linkedin          = $this->input->post("linkedin");
            $instagram         = $this->input->post("instagram");
            $permanent_address = $this->input->post("permanent_address");
            $father_name       = $this->input->post("father_name");
            $surname           = $this->input->post("surname");
            $mother_name       = $this->input->post("mother_name");
            $note              = $this->input->post("note");
            $epf_no            = $this->input->post("epf_no");

            $password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);

            $data_insert = array(
                'password'               => $this->enc_lib->passHashEnc($password),
                'employee_id'            => $employee_id,
                'name'                   => $name,
                'email'                  => $email,
                'dob'                    => date('Y-m-d', $this->customlib->datetostrtotime($dob)),
                'date_of_leaving'        => '',
                'gender'                 => $gender,
                'payscale'               => '',
                'is_active'              => 1,
                'prefix'                 => $this->input->post('prefix'),
                'ug_qualification'       => $this->input->post('ug_qualification'),
                'pg_qualification'       => $this->input->post('pg_qualification'),
                'higher_qualification'   => $this->input->post('higher_qualification'),
                'qualified_exam'         => $this->input->post('qualified_exam'),
                'subject_specialization' => $this->input->post('subject_specialization'),
                'additional_qualification' => $this->input->post('additional_qualification'),
            );

            if (isset($surname)) {
                $data_insert['surname'] = $surname;
            }

            if (isset($department)) {
                $data_insert['department'] = $department;
            }

            if (isset($designation)) {
                $data_insert['designation'] = $designation;
            }

            if (isset($mother_name)) {
                $data_insert['mother_name'] = $mother_name;
            }

            if (isset($father_name)) {
                $data_insert['father_name'] = $father_name;
            }

            if (isset($contact_no)) {
                $data_insert['contact_no'] = $contact_no;
            }

            if (isset($emergency_no)) {
                $data_insert['emergency_contact_no'] = $emergency_no;
            }

            if (isset($marital_status)) {
                $data_insert['marital_status'] = $marital_status;
            }

            if (isset($address)) {
                $data_insert['local_address'] = $address;
            }

            if (isset($permanent_address)) {
                $data_insert['permanent_address'] = $permanent_address;
            }

            if (isset($qualification)) {
                $data_insert['qualification'] = $qualification;
            }

            if (isset($work_exp)) {
                $data_insert['work_exp'] = $work_exp;
            }

            if (isset($note)) {
                $data_insert['note'] = $note;
            }

            if (isset($epf_no)) {
                $data_insert['epf_no'] = $epf_no;
            }

            if (isset($basic_salary)) {
                $data_insert['basic_salary'] = $basic_salary;
            }

            if (isset($contract_type)) {
                $data_insert['contract_type'] = $contract_type;
            }

            if (isset($shift)) {
                $data_insert['shift'] = $shift;
            }

            if (isset($location)) {
                $data_insert['location'] = $location;
            }

            if (isset($bank_account_no)) {
                $data_insert['bank_account_no'] = $bank_account_no;
            }

            if (isset($bank_name)) {
                $data_insert['bank_name'] = $bank_name;
            }

            if (isset($account_title)) {
                $data_insert['account_title'] = $account_title;
            }

            if (isset($ifsc_code)) {
                $data_insert['ifsc_code'] = $ifsc_code;
            }

            if (isset($bank_branch)) {
                $data_insert['bank_branch'] = $bank_branch;
            }

            if (isset($facebook)) {
                $data_insert['facebook'] = $facebook;
            }

            if (isset($twitter)) {
                $data_insert['twitter'] = $twitter;
            }

            if (isset($linkedin)) {
                $data_insert['linkedin'] = $linkedin;
            }

            if (isset($instagram)) {
                $data_insert['instagram'] = $instagram;
            }

            if ($date_of_joining != "") {
                $data_insert['date_of_joining'] = $this->customlib->dateFormatToYYYYMMDD($date_of_joining);
            }

            $data_insert['date_of_leaving'] = null;

            $leave_type  = $this->input->post('leave_type');
            $leave_array = array();
            if (!empty($leave_type)) {
                foreach ($leave_type as $leave_key => $leave_value) {
                    $leave_array[] = array(
                        'staff_id'      => 0,
                        'leave_type_id' => $leave_value,
                        'alloted_leave' => $this->input->post('alloted_leave_' . $leave_value),
                    );
                }
            }
            $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => 0);
//==========================
            $insert                                = true;
            $data_setting                          = array();
            $data_setting['id']                    = $this->sch_setting_detail->id;
            $data_setting['staffid_auto_insert']   = $this->sch_setting_detail->staffid_auto_insert;
            $data_setting['staffid_update_status'] = $this->sch_setting_detail->staffid_update_status;
            $employee_id                           = 0;

            if ($this->sch_setting_detail->staffid_auto_insert) {
                if ($this->sch_setting_detail->staffid_update_status) {

                    $employee_id = $this->sch_setting_detail->staffid_prefix . $this->sch_setting_detail->staffid_start_from;
                    $last_student = $this->staff_model->lastRecord();
                    $last_admission_digit = str_replace($this->sch_setting_detail->staffid_prefix, "", $last_student->employee_id);

                    $employee_id                = $this->sch_setting_detail->staffid_prefix . sprintf("%0" . $this->sch_setting_detail->staffid_no_digit . "d", $last_admission_digit + 1);
                    $data_insert['employee_id'] = $employee_id;
                } else {
                    $employee_id                = $this->sch_setting_detail->staffid_prefix . $this->sch_setting_detail->staffid_start_from;
                    $data_insert['employee_id'] = $employee_id;
                }

                $employee_id_exists = $this->staff_model->check_staffid_exists($employee_id);
                if ($employee_id_exists) {
                    $insert = false;
                }
            } else {

                $data_insert['employee_id'] = $this->input->post('employee_id');
            }
            //==========================
            if ($insert) {

                if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                    $img_name             = $this->media_storage->fileupload("file", "./uploads/staff_images/");
                    $data_insert['image'] = $img_name;
                }

                $insert_id = $this->staff_model->batchInsert($data_insert, $role_array, $leave_array, $data_setting);
                $staff_id  = $insert_id;
                if (!empty($custom_value_array)) {
                    $this->customfield_model->insertRecord($custom_value_array, $insert_id);
                }

                $upload_dir = './uploads/staff_documents/' . $staff_id . '/';
                if (!is_dir($upload_dir) && !mkdir($upload_dir)) {
                    die("Error creating folder $upload_dir");
                }
                    
                if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                      $resume = $this->media_storage->fileupload("first_doc", $upload_dir);
                } else {
                    $resume = "";
                }

                if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
                     $joining_letter = $this->media_storage->fileupload("second_doc", $upload_dir);
                } else {
                    $joining_letter = "";
                }

                if (isset($_FILES["third_doc"]) && !empty($_FILES['third_doc']['name'])) {
                    $resignation_letter = $this->media_storage->fileupload("third_doc", $upload_dir);
                } else {
                    $resignation_letter = "";
                }

                if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {
                   $fourth_doc = $this->media_storage->fileupload("fourth_doc", $upload_dir);
                } else {
                    $fourth_title = "";
                    $fourth_doc   = "";
                }

                $data_doc = array('id' => $staff_id, 'resume' => $resume, 'joining_letter' => $joining_letter, 'resignation_letter' => $resignation_letter, 'other_document_name' => $fourth_title, 'other_document_file' => $fourth_doc);
                $this->staff_model->add($data_doc);

                //***** generate barcode and qrcode of staff ******//
                    $scan_type= $this->sch_setting_detail->scan_code_type;
                    $this->customlib->generatestaffbarcode($data_insert['employee_id'],$staff_id,$scan_type);
                //***** generate barcode and qrcode of staff ******//

                //===================
                if ($staff_id) {
                    $teacher_login_detail = array('id' => $staff_id, 'credential_for' => 'staff', 'first_name' => $this->input->post("name"), 'last_name' => $this->input->post("surname"), 'username' => $email, 'password' => $password, 'contact_no' => $contact_no, 'email' => $email, 'employee_id' => $data_insert['employee_id']);
                    $this->mailsmsconf->mailsms('staff_login_credential', $teacher_login_detail);
                }
                //==========================

                $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');

                redirect('admin/staff');
            } else {
                $data['error_message'] = 'Admission No ' . $admission_no . ' already exists';
                $this->load->view('layout/header', $data);
                $this->load->view('admin/staff/staffcreate', $data);
                $this->load->view('layout/footer', $data);
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/staff/staffcreate', $data);
        $this->load->view('layout/footer', $data);
    }

    public function import()
    {
        $data['field'] = array(
            "employee_id"              => "employee_id",
            "prefix"                   => "prefix",
            "ug_qualification"         => "ug_qualification",
            "pg_qualification"         => "pg_qualification",
            "higher_qualification"     => "higher_qualification",
            "qualified_exam"           => "qualified_exam",
            "subject_specialization"   => "subject_specialization",
            "additional_qualification" => "additional_qualification",
            "qualification"            => "qualification",
            "work_exp"                 => "work_exp",
            "name"                     => "name",
            "surname"                  => "surname",
            "father_name"              => "father_name",
            "mother_name"              => "mother_name",
            "contact_no"               => "contact_no",
            "emergency_contact_no"     => "emergency_contact_no",
            "email"                    => "email",
            "dob"                      => "dob",
            "marital_status"           => "marital_status",
            "date_of_joining"          => "date_of_joining",
            "date_of_leaving"          => "date_of_leaving",
            "local_address"            => "local_address",
            "permanent_address"        => "permanent_address",
            "note"                     => "note",
            "gender"                   => "gender",
            "account_title"            => "account_title",
            "bank_account_no"          => "bank_account_no",
            "bank_name"                => "bank_name",
            "ifsc_code"                => "ifsc_code",
            "bank_branch"              => "bank_branch",
            "payscale"                 => "payscale",
            "basic_salary"             => "basic_salary",
            "epf_no"                   => "epf_no",
            "contract_type"            => "contract_type",
            "shift"                    => "shift",
            "location"                 => "location",
            "facebook"                 => "facebook",
            "twitter"                  => "twitter",
            "linkedin"                 => "linkedin",
            "instagram"                => "instagram",
            "resume"                   => "resume",
            "joining_letter"           => "joining_letter",
            "resignation_letter"       => "resignation_letter",
            "designation"              => "designation",
            "department"               => "department",
        );

        $roles               = $this->role_model->get();
        $data["roles"]       = $roles;
        $all_designations    = $this->staff_model->getStaffDesignation();
        $designation_map     = [];
        foreach ($all_designations as $designation_item) {
            $designation_map[strtolower($designation_item['designation'])] = $designation_item['id'];
        }
        $all_departments     = $this->staff_model->getDepartment();
        $department_map      = [];
        foreach ($all_departments as $department_item) {
            $department_map[strtolower($department_item['department_name'])] = $department_item['id'];
        }
        $data["designation"] = $all_designations;
        $data["department"]  = $all_departments;

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_csv_upload');
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view("layout/header", $data);
            $this->load->view("admin/staff/import/import", $data);
            $this->load->view("layout/footer", $data);
        } else {

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {

                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);

                    $rowcount = 0;
                    $inserted_count = 0;
                    $updated_count = 0;
                    $skipped_count = 0;

                    if (!empty($result)) {
                        foreach ($result as $r_key => $r_value) {


                            $staff_data = [];
                            foreach ($data['field'] as $csv_header => $db_field) {
                                $staff_data[$db_field] = isset($r_value[$csv_header]) ? $this->encoding_lib->toUTF8($r_value[$csv_header]) : '';
                            }

                            // Date parsing for dob and date_of_joining
                            if (!empty($staff_data['dob'])) {
                                $parsed_date = strtotime($staff_data['dob']);
                                $staff_data['dob'] = ($parsed_date !== false) ? date('Y-m-d', $parsed_date) : null;
                            } else {
                                $staff_data['dob'] = null;
                            }

                            if (!empty($staff_data['date_of_joining'])) {
                                $parsed_date = strtotime($staff_data['date_of_joining']);
                                $staff_data['date_of_joining'] = ($parsed_date !== false) ? date('Y-m-d', $parsed_date) : null;
                            } else {
                                $staff_data['date_of_joining'] = null;
                            }

                            // Handle designation mapping
                            $csv_designation_name = strtolower(trim($staff_data['designation']));
                            if (isset($designation_map[$csv_designation_name])) {
                                $staff_data['designation'] = $designation_map[$csv_designation_name];
                            } else {
                                $staff_data['designation'] = null;
                            }

                            // Handle department mapping
                            $csv_department_name = strtolower(trim($staff_data['department']));
                            if (isset($department_map[$csv_department_name])) {
                                $staff_data['department'] = $department_map[$csv_department_name];
                            } else {
                                $staff_data['department'] = null;
                            }

                            $staff_data['is_active'] = 1;

                            $existing_staff_id = $this->staff_model->getStaffIdByEmployeeIdOrEmail($staff_data['employee_id'], $staff_data['email']);

                            if ($existing_staff_id) {
                                log_message('debug', 'Skipping existing record for Employee ID: ' . $staff_data['employee_id'] . ', Email: ' . $staff_data['email']);
                                $skipped_count++;
                                continue;
                            } else {
                                $password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
                                $staff_data['password'] = $this->enc_lib->passHashEnc($password);
                                $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => 0);
                                $insert_id = $this->staff_model->batchInsert($staff_data, $role_array);
                                $staff_id  = $insert_id;
                                $inserted_count++; // Keep track of inserted records
                                log_message('debug', 'Inserting new record for Employee ID: ' . $staff_data['employee_id'] . ', Email: ' . $staff_data['email']);

                                if ($staff_id) {
                                    //***** generate barcode and qrcode of staff ******//
                                    $scan_type= $this->sch_setting_detail->scan_code_type;
                                    $this->customlib->generatestaffbarcode($staff_data['employee_id'],$staff_id,$scan_type);
                                    //***** generate barcode and qrcode of staff ******//
                                }

                                if ($staff_id) { // Only send login credential for new inserts
                                    $teacher_login_detail = array('id' => $staff_id, 'credential_for' => 'staff', 'username' => $staff_data['email'], 'password' => $password, 'contact_no' => $staff_data['contact_no'], 'email' => $staff_data['email']);
                                    $this->mailsmsconf->mailsms('login_credential', $teacher_login_detail);
                                }
                            }
                        } ///Result loop
                    } //Not emprty l

                    $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('records_found_in_CSV_file_total') . (count($result) - 1) . $this->lang->line('records_imported_successfully')); // Adjusted for header row
                }
            } else {
                $msg = array(
                    'e' => $this->lang->line('the_file_field_is_required'),
                );
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $this->lang->line('total') . ' ' . (count($result) - 1) . " " . $this->lang->line('records_found_in_CSV_file_total') . ' ' . $inserted_count . ' ' . $this->lang->line('records_inserted_successfully') . '. ' . $skipped_count . ' records were skipped because they already exist.</div>');
            redirect('admin/staff/import');
        }
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt');
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/staff_csvfile.csv";
        $data     = file_get_contents($filepath);
        $name     = 'staff_csvfile.csv';
        force_download($name, $data);
    }

    public function rating()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/rating');
        $this->load->view('layout/header');
        $staff_list         = $this->staff_model->getrat();
        $data['resultlist'] = $staff_list;
        $this->load->view('admin/staff/rating', $data);
        $this->load->view('layout/footer');
    }

    public function ratingapr($id)
    {
        $approve['status'] = '1';
        $this->staff_model->ratingapr($id, $approve);
        redirect('admin/staff/rating');
    }

    public function delete_rateing($id)
    {
        $this->staff_model->rating_remove($id);
        redirect('admin/staff/rating');
    }

}
