<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Admin Controller
 *
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Config $config
 * @property CI_Lang $lang
 * @property Setting_model $setting_model
 * @property Auth $auth
 * @property Student_model $Student_model
 * @property Student_model $student_model
 * @property Customstudentfeemaster_model $Customstudentfeemaster_model
 * @property Studentfeemaster_model $Studentfeemaster_model
 * @property Customlib $customlib
 * @property Notification_model $notification_model
 * @property Studentfeemaster_model $studentfeemaster_model
 * @property Studenttransportfee_model $studenttransportfee_model
 * @property Studentsession_model $studentsession_model
 * @property Role_model $role_model
 * @property Expense_model $expense_model
 * @property Studentfee_model $studentfee_model
 * @property Income_model $income_model
 * @property Admin_model $admin_model
 * @property Book_model $book_model
 * @property Bookissue_model $bookissue_model
 * @property Stuattendence_model $stuattendence_model
 * @property Staff_model $Staff_model
 * @property Apply_leave_model $apply_leave_model
 * @property Staff_model $staff_model
 * @property Rbac $rbac
 * @property Session_model $session_model
 * @property Enc_lib $enc_lib
 * @property M_pdf $m_pdf
 * @property Filetype_model $filetype_model
 * @property Class_model $class_model
 * @property Customfield_model $customfield_model
 * @property Module_lib $module_lib
 * @property CI_DB_utility $dbutil
 */
class Admin extends Admin_Controller
{

    public $sch_setting_detail;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("classteacher_model");
        $this->load->model("Staff_model");
        $this->load->model("Student_model");
        $this->load->model("Customstudentfeemaster_model");
        $this->load->model("Studentfeemaster_model");
        $this->load->library('Enc_lib');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function unauthorized()
    {
        $data = array();
        $this->load->view('layout/header', $data);
        $this->load->view('unauthorized', $data);
        $this->load->view('layout/footer', $data);
    }

    public function updateAddonVerify()
    {
        $this->form_validation->set_rules('addon', 'Addon', 'required|trim|xss_clean');
        $this->form_validation->set_rules('addon_check_update_envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'addon'                       => form_error('addon'),
                'addon_check_update_envato_market_purchase_code' => form_error('addon_check_update_envato_market_purchase_code'),
            );
            $array = array('status' => '0', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->addon_update_check(); 
        }
    }

    public function dashboard()
    {
        $data['fees_awaiting_total_net_balance'] = 0;
        
        $role            = $this->customlib->getStaffRole();
        $role_id         = json_decode($role)->id;
        $data['role_id'] = $role_id;

        $staffid       = $this->customlib->getStaffID();
        $notifications = $this->notification_model->getUnreadStaffNotification($staffid, $role_id);

        $data['notifications'] = $notifications;
        $input                 = $this->setting_model->getCurrentSessionName();

        list($a, $b)  = explode('-', $input);
        $Current_year = $a;
        if (strlen($b) == 2) {
            $Next_year = substr($a, 0, 2) . $b;
        } else {
            $Next_year = $b;
        }
        // Increase execution time for dashboard data processing
        set_time_limit(300); // 5 minutes for large datasets
        
        $data['mysqlVersion'] = $this->setting_model->getMysqlVersion();
        $data['sqlMode']      = $this->setting_model->getSqlMode();
        //========================== Current Attendence ==========================
        $current_date       = date('Y-m-d');
        $data['title']      = 'Dashboard';
        $Current_start_date = date('01');

        $last_day_this_month        = date($Current_year.'-m-t');  //added
        $total_students_heads       = 0; //added

        $Current_date       = date('d');
        $Current_month      = date('m');
        $month_collection   = 0;
        $month_expense      = 0;
        $total_students     = 0;
        $total_teachers     = 0;
        $ar                 = $this->startmonthandend();
        $year_str_month     = $Current_year . '-' . $ar[0] . '-01';
        $year_end_month     = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));
        $getDepositeAmount  = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
        $student_transport_fee = $this->studenttransportfee_model->getTransportDepositAmountBetweenDate($year_str_month, $year_end_month);
        
        //======================Current Month Collection ==============================
        
        $first_day_this_month     = date('Y-m-01'); //comment
        
        // $current_month_collection = $this->studentfeemaster_model->getDepositAmountBetweenDate($first_day_this_month, $current_date);
        $month_collection         = $this->whatever($getDepositeAmount, $first_day_this_month, $current_date);
        $month_transport_collection         = $this->whatever($student_transport_fee, $first_day_this_month, $current_date);

        $data['month_collection'] = $month_collection+$month_transport_collection;


        $tot_head_students = $this->studentsession_model->getTotalHeadCountBySession();
        $total_students = count($tot_head_students);
        $data['total_students'] = $total_students;
        $tot_roles              = $this->role_model->get();
        foreach ($tot_roles as $key => $value) {

            $count_roles[$value["name"]] = $this->role_model->count_roles($value["id"]);

        }
        $data["roles"] = $count_roles;

        //======================== get collection by month ==========================
        $start_month = strtotime($year_str_month);
        $start       = strtotime($year_str_month);
        $end         = strtotime($year_end_month);
        $coll_month  = array();
        $s           = array();
        $total_month = array();
        while ($start_month <= $end) {
            $total_month[] = $this->lang->line(strtolower(date('F', $start_month)));
            $month_start   = date('Y-m-d', $start_month);
            $month_end     = date("Y-m-t", $start_month);
            $return        = $this->whatever($getDepositeAmount, $month_start, $month_end);
            $tranport_amt      = $this->whatever($student_transport_fee,  $month_start, $month_end);
            
            if (!IsNullOrEmptyString($return) || !IsNullOrEmptyString($tranport_amt)) {
                $s[] = convertBaseAmountCurrencyFormat($return+$tranport_amt);
            } else {
                $s[] = "0.00";
            }

            $start_month = strtotime("+1 month", $start_month);
        }
        //======================== getexpense by month ==============================
        $ex                  = array();
        $start_session_month = strtotime($year_str_month);
        while ($start_session_month <= $end) {

            $month_start = date('Y-m-d', $start_session_month);
            $month_end   = date("Y-m-t", $start_session_month);

            $expense_monthly = $this->expense_model->getTotalExpenseBwdate($month_start, $month_end);

            if (!empty($expense_monthly)) {
                $ex[] = convertBaseAmountCurrencyFormat($expense_monthly->amount);
            } else {
                $ex[] = "0.00";  // Add 0.00 when no expenses to prevent array gaps
            }

            $start_session_month = strtotime("+1 month", $start_session_month);
        }

        $data['yearly_collection'] = $s;
       
        $data['yearly_expense']    = $ex;
        $data['total_month']       = $total_month;

        //======================= current month collection /expense ===================
     
        // hardcoded '01' for first day
        $startdate       = date('m/01/Y');
        $enddate         = date('m/t/Y');
        $start           = strtotime($startdate);
        $end             = strtotime($enddate);
        $currentdate     = $start;
        $month_days      = array();
        $days_collection = array();
        while ($currentdate <= $end) {
            $cur_date          = date('Y-m-d', $currentdate);
            $month_days[]      = date('d', $currentdate);
            $coll_amt          = $this->whatever($getDepositeAmount, $cur_date, $cur_date);
            $tranport_amt      = $this->whatever($student_transport_fee, $cur_date, $cur_date);
            $days_collection[] = convertBaseAmountCurrencyFormat($coll_amt+$tranport_amt);
            $currentdate       = strtotime('+1 day', $currentdate);
        }
        $data['current_month_days'] = $month_days;
        $data['days_collection']    = $days_collection;


        //======================= current month /expense ==============================
        // hardcoded '01' for first day

        $startdate    = date('m/01/Y');
        $enddate      = date('m/t/Y');
        $start        = strtotime($startdate);
        $end          = strtotime($enddate);
        $currentdate  = $start;
        $days_expense = array();
        while ($currentdate <= $end) {
            $cur_date       = date('Y-m-d', $currentdate);
            $month_days[]   = date('d', $currentdate);
            $currentdate    = strtotime('+1 day', $currentdate);
            $ct             = $this->getExpensebyday($cur_date);
            $days_expense[] = convertBaseAmountCurrencyFormat($ct);
        }

        $data['days_expense']        = $days_expense;

        $student_fee_history         = $this->studentfee_model->getTodayStudentFees();
        $data['student_fee_history'] = $student_fee_history;

        $event_colors         = array("#03a9f4", "#c53da9", "#757575", "#8e24aa", "#d81b60", "#7cb342", "#fb8c00", "#fb3b3b");
        $data["event_colors"] = $event_colors;
        $userdata             = $this->customlib->getUserData();
        $data["role"]         = $userdata["user_type"];
        $start_date           = date('Y-m-01');
        $end_date             = date('Y-m-t');
        $current_month        = date('F');

        $student_due_fee       = $this->studentfeemaster_model->getFeesAwaiting($start_date, $end_date);
        $student_transport_fee = $this->studentfeemaster_model->getTransportFeesByDueDate($start_date, $end_date);
        $data['fees_awaiting'] = $student_due_fee;

        $current_session = $this->setting_model->getCurrentSession();
        $students = $this->Student_model->getStudentsBySession($current_session);

        // Debug output for fee fetching
        error_log('DEBUG: getFeesAwaiting returned: ' . print_r($student_due_fee, true));
        error_log('DEBUG: getTransportFeesByDueDate returned: ' . print_r($student_transport_fee, true));

        $total_fess    = 0;
        $total_paid    = 0;
        $total_unpaid  = 0;
        $total_partial = 0;

        if (!empty($student_transport_fee)) {

            foreach ($student_transport_fee as $transport_fees_key => $transport_fees_value) {

                $amount_to_be_taken = 0;
                if ($transport_fees_value->fees > 0) {
                    $amount_to_be_taken = $transport_fees_value->fees;
                }

                if ($amount_to_be_taken > 0) {
                    $total_fess++;

                    if (is_string($transport_fees_value->amount_detail) && is_array(json_decode($transport_fees_value->amount_detail, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                        $amount_paid_details = (json_decode($transport_fees_value->amount_detail));
                        $amt_                = 0;
                        foreach ($amount_paid_details as $amount_paid_detail_key => $amount_paid_detail_value) {
                            $amt_ = $amt_ + $amount_paid_detail_value->amount;
                        }

                        if (($amt_ + $amount_paid_detail_value->amount_discount) >= $amount_to_be_taken) {
                            $total_paid++;
                        } elseif (($amt_ + $amount_paid_detail_value->amount_discount) < $amount_to_be_taken) {
                            $total_partial++;
                        }
                    } else {
                        $total_unpaid++;
                    }

                }
            }
        }

        if (!empty($data['fees_awaiting'])) {

            $unpaid_total_amount = 0;
            $allowed_fee_codes = array('TUTFEE', 'OTHERFEE', 'HostFee', 'TransFee');
            foreach ($data['fees_awaiting'] as $awaiting_key => $awaiting_value) {
                // Only include specified fee types
                if (!isset($awaiting_value->code) || !in_array($awaiting_value->code, $allowed_fee_codes)) {
                    continue;
                }
                $amount_to_be_taken = 0;
                if (isset($awaiting_value->fee_amount) && $awaiting_value->fee_amount > 0) {
                    $amount_to_be_taken = $awaiting_value->fee_amount;
                } elseif (isset($awaiting_value->amount) && $awaiting_value->amount > 0) {
                    $amount_to_be_taken = $awaiting_value->amount;
                }
                $unpaid_total_amount += $amount_to_be_taken;
            }
            $data['fees_awaiting_total_amount'] = $unpaid_total_amount;
        }

        $month_income = 0;
        $incomegraph = $this->income_model->getIncomeHeadsData($start_date, $end_date);
        foreach ($incomegraph as $key => $value) {
            $incomegraph[$key]['total'] = convertBaseAmountCurrencyFormat($value['total']);
            if (!empty($value['total'])) {
                $month_income = $month_income + $value['total'];  // Add raw numeric value, not formatted string
            }
        }
        $data['incomegraph'] = $incomegraph;
        $data['month_income'] = $month_income;

        $expensegraph = $this->expense_model->getExpenseHeadData($start_date, $end_date);
        foreach ($expensegraph as $key => $value) {
            $expensegraph[$key]['total'] = convertBaseAmountCurrencyFormat($value['total']);
            if (!empty($value['total'])) {
                $month_expense = $month_expense + $value['total'];  // Add raw numeric value, not formatted string
            }
        }
        $data['expensegraph']  = $expensegraph;
        $data['month_expense'] = $month_expense;

        $enquiry       = $this->admin_model->getAllEnquiryCount($start_date, $end_date);
        $total_counter = $total_paid + $total_unpaid + $total_partial;

        $data['fees_overview'] = array(
            'total_unpaid'     => 0,
            'unpaid_sum'       => 0,
            'unpaid_progress'  => 0,
            'total_partial'    => 0,
            'partial_sum'      => 0,
            'partial_progress' => 0,
            'total_paid'       => 0,
            'paid_sum'         => 0,
            'paid_progress'    => 0,
            'total_demand'     => 0,
            'total_collection' => 0,
            'total_awaiting'   => 0,
        );
        $data['fees_awaiting_progress'] = 0;

        // Calculate Total Demand, Collection, Awaiting (match Custom Balance Fees Report)
        $total_demand = 0;
        $total_collection = 0;
        $total_awaiting = 0;
        if (!empty($students)) {
            foreach ($students as $student) {
                $student_session_id = $student['student_session_id'];
                $fees_data = $this->Customstudentfeemaster_model->getTransStudentFees($student_session_id);
                $advance_balances = $this->Studentfeemaster_model->get_advance_balance($student_session_id);
                $advance_paid = isset($advance_balances['paid_advance_balance']) ? $advance_balances['paid_advance_balance'] : 0;
                $advance_discount = isset($advance_balances['discount_advance_balance']) ? $advance_balances['discount_advance_balance'] : 0;

                // Total fees (sum of fee items)
                $totalfee = 0;
                if (!empty($fees_data->fees)) {
                    foreach ($fees_data->fees as $fee_item) {
                        $totalfee += $fee_item->amount;
                    }
                }

                // Total paid (tuition + other + hostel + transport)
                $total_paid_sum = 0;
                if ($fees_data) {
                    $total_paid_sum = $fees_data->tuition_paid + $fees_data->other_paid + $fees_data->hostel_paid + $fees_data->transport_paid;
                }

                // Previous session balance (CF)
                $previous_session_balance_data = $this->Customstudentfeemaster_model->getPreviousSessionBalance($student_session_id);
                $last_yr_cf = !empty($previous_session_balance_data) ? $previous_session_balance_data->amount : 0;
                $cf_paid = $this->Customstudentfeemaster_model->getPreviousSessionPaid($student_session_id);
                $cf_balance = $last_yr_cf - $cf_paid;

                $balance = $totalfee - $total_paid_sum;
                $balance += $cf_balance;
                $net_balance = $balance - ($advance_paid + $advance_discount);

                $student_demand = $totalfee;
                $student_collection = $total_paid_sum;
                $student_awaiting = $net_balance;

                $total_demand += $student_demand;
                $total_collection += $student_collection;
                $total_awaiting += $student_awaiting;
            }
        }
        $data['fees_overview']['total_demand'] = $total_demand;
        $data['fees_overview']['total_collection'] = $total_collection;
        $data['fees_overview']['total_awaiting'] = $total_awaiting;
        $data['fees_awaiting_progress'] = ($total_demand > 0) ? (($total_awaiting * 100) / $total_demand) : 0;

        $total_enquiry = $enquiry['total'];

        if ($total_enquiry > 0) {

            $data['enquiry_overview'] = array(
                'won'              => $enquiry['complete'],
                'won_progress'     => ($enquiry['complete'] * 100) / $total_enquiry,
                'active'           => $enquiry['active'],
                'active_progress'  => ($enquiry['active'] * 100) / $total_enquiry,
                'passive'          => $enquiry['passive'],
                'passive_progress' => ($enquiry['passive'] * 100) / $total_enquiry,
                'dead'             => $enquiry['dead'],
                'dead_progress'    => ($enquiry['dead'] * 100) / $total_enquiry,
                'lost'             => $enquiry['lost'],
                'lost_progress'    => ($enquiry['lost'] * 100) / $total_enquiry,
            );

        } else {

            $data['enquiry_overview'] = array(
                'won'              => 0,
                'won_progress'     => 0,
                'active'           => 0,
                'active_progress'  => 0,
                'passive'          => 0,
                'passive_progress' => 0,
                'dead'             => 0,
                'dead_progress'    => 0,
                'lost'             => 0,
                'lost_progress'    => 0,
            );

        }

        $data['total_paid'] = $total_paid;
        $data['total_fees'] = $total_fess;
        if ($total_fess > 0) {
            $data['fessprogressbar'] = ($total_paid * 100) / $total_fess;
        } else {
            $data['fessprogressbar'] = 0;
        }

        $data['total_enquiry']  = $total_enquiry  = $enquiry['total'];
        $data['total_complete'] = $complete_enquiry = $enquiry['complete'];
        if ($total_enquiry > 0) {
            $data['fenquiryprogressbar'] = ($complete_enquiry * 100) / $total_enquiry;
        } else {
            $data['fenquiryprogressbar'] = 0;
        }

        $bookoverview      = $this->book_model->bookoverview($start_date, $end_date);
        $bookduereport     = $this->bookissue_model->dueforreturn($start_date, $end_date);
        $forreturndata     = $this->bookissue_model->forreturn($start_date, $end_date);
        $dueforreturn      = $bookduereport[0]['total'];
        $forreturn         = $forreturndata[0]['total'];
        $total_qty         = $bookoverview[0]['qty'];
        $total_issued      = $bookoverview[0]['total_issue'];
        $availble          = '0';
        $availble_progress = 0;
        $issued_progress   = 0;

        if ($total_qty > 0) {
            $availble          = $total_qty - $total_issued;
            $availble_progress = ($availble * 100) / $total_qty;
            $issued_progress   = ($total_issued * 100) / $total_qty;
        }

        $data['book_overview'] = array(
            'total'             => $total_qty,
            'total_progress'    => 100,
            'availble'          => $availble,
            'availble_progress' => round($availble_progress, 2),
            'total_issued'      => $total_issued,
            'issued_progress'   => round($issued_progress, 2),
            'dueforreturn'      => $dueforreturn,
            'forreturn'         => $forreturn,
        );

        $Attendence                   = $this->stuattendence_model->getTodayDayAttendance($total_students);
        $data['attendence_data']      = $Attendence;
        $data['staff_attendance_details'] = $this->Staff_model->getTodayStaffAttendanceDetails();
        $staff_attendance_details = $data['staff_attendance_details'];
        if ($staff_attendance_details['total_staff'] > 0) {
            $data['percentTotalStaff_data'] = ($staff_attendance_details['total_present'] * 100) / $staff_attendance_details['total_staff'];
        } else {
            $data['percentTotalStaff_data'] = 0;
        }
        $data['sch_setting']            = $this->sch_setting_detail;

        // Birthday widgets automatically respect branch settings via Db_manager library
        // Db_manager switches database connection based on session['admin']['db_array']['db_group']
        // All model queries (Student_model, Staff_model) use $this->db which points to active branch database
        $today_date = date('Y-m-d');
        $data['student_birthdays'] = $this->Student_model->getBirthDayStudents($today_date, false, false);
        
        $staff_birthdays = array();
        $today_day_no = date('N');
        $monday = date('Y-m-d', strtotime('last monday'));
        $sunday = date('Y-m-d', strtotime('next sunday'));

        $current_date = $monday;
        while (strtotime($current_date) <= strtotime($sunday)) {
            $daily_birthdays = $this->Staff_model->getBirthDayStaff($current_date, 1, false, false);
            if (!empty($daily_birthdays)) {
                $staff_birthdays = array_merge($staff_birthdays, $daily_birthdays);
            }
            $current_date = date('Y-m-d', strtotime('+1 day', strtotime($current_date)));
        }

        $data['staff_birthdays'] = $staff_birthdays;
		// new features code added
        // $input_session   = $this->setting_model->getCurrentSessionName();
        // list($a, $b)  = explode('-', $input_session);
        // $Current_year = $a;
        // if(date("m")>=1 && date("m")<=4 ){
            // $Current_year = $b;
        // }else{
            // $Current_year = $a;
        // }

        // $first_day_this_month  = date("20$Current_year".'-m-01'); //added
        // $last_day_this_month  = date("20$Current_year".'-m-t');  //added

        $data['getStudentMonthlyLeave'] = $getStudentMonthlyLeave = count($this->apply_leave_model->getStudentMonthlyLeave($start_date, $end_date));
        $data['getStudentApproveMonthlyLeave'] = $getStudentApproveMonthlyLeave =   count($this->apply_leave_model->getStudentApproveMonthlyLeave($start_date, $end_date));
 
        if ($getStudentMonthlyLeave > 0) {
            $data['studentapprovemonthlyleave'] = ($getStudentApproveMonthlyLeave * 100) / $getStudentMonthlyLeave;
        } else {
            $data['studentapprovemonthlyleave'] = 0;
        }
        $data['getStaffMonthlyLeave'] = $getStaffMonthlyLeave = count($this->apply_leave_model->getStaffMonthlyLeave($start_date, $end_date));
		
		// echo $this->db->last_query(); die;
		
        $data['getStaffApproveMonthlyLeave'] = $getStaffApproveMonthlyLeave =   count($this->apply_leave_model->getStaffApproveMonthlyLeave($start_date, $end_date));

        if ($getStaffMonthlyLeave > 0) {
            $data['staffapprovemonthlyleave'] = ($getStaffApproveMonthlyLeave * 100) / $getStaffMonthlyLeave;
        } else {
            $data['staffapprovemonthlyleave'] = 0;
        }

        $tot_students = $this->studentsession_model->getTotalStudentBySession();
        if (!empty($tot_students)) {
            $total_students = $tot_students->total_student;
        }

        if (!empty($tot_head_students)) {
            $total_students_heads = count($tot_head_students);
        } 

        $data['total_students'] = $total_students;
        $data['total_students_heads'] = $total_students_heads;
        
        // Get student count by gender
        $gender_counts = $this->Student_model->getStudentCountByGender($current_session);
        $data['male_students'] = $gender_counts['Male'];
        $data['female_students'] = $gender_counts['Female'];
        $data['other_students'] = $gender_counts['Other'];
        $data['unspecified_students'] = $gender_counts['Not Specified'];

        if ($data['sch_setting']->attendence_type == 0) {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        } else {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    public function fees_overview_widget()
    {
        if (!$this->rbac->hasPrivilege('fees_overview_widegts', 'can_view')) {
            access_denied();
        }

        $current_session = $this->setting_model->getCurrentSession();
        $students = $this->Student_model->getStudentsBySession($current_session);

        $unpaid_count = 0;
        $unpaid_sum = 0;
        $partial_count = 0;
        $partial_sum = 0;
        $paid_count = 0;
        $paid_sum = 0;

        $total_demand = 0;
        $total_collection = 0;
        $total_awaiting = 0;

        if (!empty($students)) {
            foreach ($students as $student) {
                $student_session_id = $student['student_session_id'];
                $fees_data = $this->Customstudentfeemaster_model->getTransStudentFees($student_session_id);
                
                // Handle null or empty fees_data
                if (empty($fees_data)) {
                    continue;
                }
                
                $advance_balances = $this->Studentfeemaster_model->get_advance_balance($student_session_id);
                $advance_paid = isset($advance_balances['paid_advance_balance']) ? $advance_balances['paid_advance_balance'] : 0;
                $advance_discount = isset($advance_balances['discount_advance_balance']) ? $advance_balances['discount_advance_balance'] : 0;

                $totalfee = 0;
                if (!empty($fees_data->fees)) {
                    foreach ($fees_data->fees as $fee_item) {
                        $totalfee += $fee_item->amount;
                    }
                }

                $total_paid_sum = 0;
                if ($fees_data) {
                    $total_paid_sum = (isset($fees_data->tuition_paid) ? $fees_data->tuition_paid : 0) + 
                                    (isset($fees_data->other_paid) ? $fees_data->other_paid : 0) + 
                                    (isset($fees_data->hostel_paid) ? $fees_data->hostel_paid : 0) + 
                                    (isset($fees_data->transport_paid) ? $fees_data->transport_paid : 0);
                }

                $previous_session_balance_data = $this->Customstudentfeemaster_model->getPreviousSessionBalance($student_session_id);
                $last_yr_cf = !empty($previous_session_balance_data) ? $previous_session_balance_data->amount : 0;
                $cf_paid = $this->Customstudentfeemaster_model->getPreviousSessionPaid($student_session_id);
                $cf_balance = $last_yr_cf - $cf_paid;

                $balance = $totalfee - $total_paid_sum;
                $balance += $cf_balance;
                $net_balance = $balance - ($advance_paid + $advance_discount);

                if ($totalfee == 0 && $cf_balance == 0) {
                    continue;
                }

                if ($net_balance > 0 && $total_paid_sum == 0) {
                    $unpaid_count++;
                    $unpaid_sum += $net_balance;
                } elseif ($net_balance > 0 && $total_paid_sum > 0) {
                    $partial_count++;
                    $partial_sum += $net_balance;
                } elseif ($net_balance <= 0 && $total_paid_sum > 0) {
                    $paid_count++;
                    $paid_sum += $total_paid_sum;
                }

                $total_demand += $totalfee;
                $total_collection += $total_paid_sum;
                $total_awaiting += $net_balance;
            }
        }

        $total_counter = $unpaid_count + $partial_count + $paid_count;
        $unpaid_progress = ($total_counter > 0) ? (($unpaid_count * 100) / $total_counter) : 0;
        $partial_progress = ($total_counter > 0) ? (($partial_count * 100) / $total_counter) : 0;
        $paid_progress = ($total_counter > 0) ? (($paid_count * 100) / $total_counter) : 0;
        $fees_awaiting_progress = ($total_demand > 0) ? (($total_awaiting * 100) / $total_demand) : 0;

        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

        $response = array(
            'status' => 'success',
            'data' => array(
                'total_unpaid' => $unpaid_count,
                'unpaid_progress' => round($unpaid_progress, 2),
                'unpaid_sum' => $unpaid_sum,
                'unpaid_sum_formatted' => $currency_symbol . number_format($unpaid_sum, 2),
                'total_partial' => $partial_count,
                'partial_progress' => round($partial_progress, 2),
                'partial_sum' => $partial_sum,
                'partial_sum_formatted' => $currency_symbol . number_format($partial_sum, 2),
                'total_paid' => $paid_count,
                'paid_progress' => round($paid_progress, 2),
                'paid_sum' => $paid_sum,
                'paid_sum_formatted' => $currency_symbol . number_format($paid_sum, 2),
                'total_demand' => $total_demand,
                'total_demand_formatted' => $currency_symbol . number_format($total_demand, 2),
                'total_collection' => $total_collection,
                'total_collection_formatted' => $currency_symbol . number_format($total_collection, 2),
                'total_awaiting' => $total_awaiting,
                'total_awaiting_formatted' => $currency_symbol . number_format($total_awaiting, 2),
                'fees_awaiting_progress' => round($fees_awaiting_progress, 2),
                'fees_awaiting_total_net_balance' => $total_awaiting,
                'fees_awaiting_total_net_balance_formatted' => $currency_symbol . number_format($total_awaiting, 2),
            ),
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
	
    public function getUserImage()
    {
        $id     = $this->session->userdata["admin"]["id"];
        $result = $this->staff_model->get($id);
    }

    public function getSession()
    {
        if (!$this->rbac->hasPrivilege('quick_session_change', 'can_view')) {
            access_denied();
        }
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
        $this->load->view('admin/partial/_session', $data);
    }

    public function updateSession()
    {
        $session       = $this->input->post('popup_session');
        $session_array = $this->session->has_userdata('session_array');
        if ($session_array) {
            $this->session->unset_userdata('session_array');
        }
        $session       = $this->session_model->get($session);
        $session_array = array('session_id' => $session['id'], 'session' => $session['session']);
        $this->session->set_userdata('session_array', $session_array);
        echo json_encode(array('status' => 1, 'message' => $this->lang->line('session_changed_successfully')));
    }

    public function updatePurchaseCode()
    {
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('envato_market_purchase_code', $this->lang->line('purchase_code'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'email'                       => form_error('email'),
                'envato_market_purchase_code' => form_error('envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->app_update();
        }
    }

    public function backup()
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/backup');
        $this->session->set_userdata('inner_menu', 'admin/backup');
        $data['title'] = $this->lang->line('backup_history');
        if ($this->input->server('REQUEST_METHOD') == "POST") {
            if ($this->input->post('backup') == "upload") {
                $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
                if ($this->form_validation->run() == false) {

                } else {
                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $fileInfo  = pathinfo($_FILES["file"]["name"]);
                        $file_name = "db-" . date("Y-m-d_H-i-s") . ".sql";
                        move_uploaded_file($_FILES["file"]["tmp_name"], "./backup/temp_uploaded/" . $file_name);
                        $folder_name  = 'temp_uploaded';
                        $path         = './backup/';
                        $filePath     = $path . $folder_name . '/' . $file_name;
                        $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                        $db           = (array) get_instance()->db;
                        $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                        $sql   = '';
                        $error = '';

                        if (file_exists($filePath)) {
                            $lines = file($filePath);

                            foreach ($lines as $line) {

                                // Ignoring comments from the SQL script
                                if (substr($line, 0, 2) == '--' || $line == '') {
                                    continue;
                                }

                                $sql .= $line;

                                if (substr(trim($line), -1, 1) == ';') {
                                    $result = mysqli_query($conn, $sql);
                                    if (!$result) {
                                        $error .= mysqli_error($conn) . "\n";
                                    }
                                    $sql = '';
                                }
                            }
                            $msg = $this->lang->line('restored_message');
                        } // end if file exists

                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                        redirect('admin/admin/backup');
                    }
                }
            }
            if ($this->input->post('backup') == "backup") {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                $this->load->helper('download');
                $this->load->dbutil();
                $version  = $this->customlib->getAppVersion();
                $filename = "db_ver_" . $version . '_' . date("Y-m-d_H-i-s") . ".sql";
                $prefs    = array(
                    'ignore'     => array(),
                    'format'     => 'txt',
                    'filename'   => 'mybackup.sql',
                    'add_drop'   => true,
                    'add_insert' => true,
                    'newline'    => "\n",
                );
                $backup = $this->dbutil->backup($prefs);
                $this->load->helper('file');
                write_file('./backup/database_backup/' . $filename, $backup);
                redirect('admin/admin/backup');
                force_download($filename, $backup);
                $this->session->set_flashdata('feedback', $this->lang->line('success_message_for_client_to_see'));
                redirect('admin/admin/backup');
            } else if ($this->input->post('backup') == "restore") {
                $folder_name  = 'database_backup';
                $file_name    = $this->input->post('filename');
                $path         = './backup/';
                $filePath     = $path . $folder_name . '/' . $file_name;
                $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                $db           = (array) get_instance()->db;
                $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                $sql   = '';
                $error = '';

                if (file_exists($filePath)) {
                    $lines = file($filePath);

                    foreach ($lines as $line) {

                        // Ignoring comments from the SQL script
                        if (substr($line, 0, 2) == '--' || $line == '') {
                            continue;
                        }

                        $sql .= $line;

                        if (substr(trim($line), -1, 1) == ';') {
                            $result = mysqli_query($conn, $sql);
                            if (!$result) {
                                $error .= mysqli_error($conn) . "\n";
                            }
                            $sql = '';
                        }
                    }
                    $msg = $this->lang->line('restored_message');
                } // end if file exists
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $msg . '</div>');
                redirect('admin/admin/backup');
            }
        }
        $dir    = "./backup/database_backup/";
        $result = array();
        if (is_dir($dir)) {
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, array(".", ".."))) {
                    $fullPath = $dir . DIRECTORY_SEPARATOR . $value;
                    if (is_dir($fullPath)) {
                        // Recursively collect files under the subdirectory
                        $files = array();
                        try {
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS),
                                RecursiveIteratorIterator::LEAVES_ONLY
                            );
                            foreach ($iterator as $fileinfo) {
                                // store path relative to the subdirectory
                                $files[] = substr($fileinfo->getPathname(), strlen($fullPath) + 1);
                            }
                        } catch (Exception $e) {
                            // on error, keep files empty for this folder
                            $files = array();
                        }
                        $result[$value] = $files;
                    } else {
                        $result[] = $value;
                    }
                }
            }
        }
        $data['dbfileList']  = $result;
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/backup', $data);
        $this->load->view('layout/footer', $data);
    }

    public function changepass()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'changepass/index');
        $data['title'] = 'Change Password';
        $this->form_validation->set_rules('current_pass', $this->lang->line("current_password"), 'trim|required|xss_clean');
        $this->form_validation->set_rules('new_pass', $this->lang->line("new_password"), 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', $this->lang->line("confirm_password"), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $sessionData            = $this->session->userdata('admin');
            $this->data['id']       = $sessionData['id'];
            $this->data['username'] = $sessionData['username'];
            $this->load->view('layout/header', $data);
            $this->load->view('admin/change_password', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sessionData = $this->session->userdata('admin');
            $userdata    = $this->customlib->getUserData();
            $data_array  = array(
                'current_pass' => $this->input->post('current_pass'),
                'new_pass'     => md5($this->input->post('new_pass')),
                'user_id'      => $sessionData['id'],
                'user_email'   => $sessionData['email'],
                'user_name'    => $sessionData['username'],
            );
            $newdata = array(
                'id'       => $sessionData['id'],
                'password' => $this->enc_lib->passHashEnc($this->input->post('new_pass')),
            );
            $check  = $this->enc_lib->passHashDyc($this->input->post('current_pass'), $userdata["password"]);
            $query1 = $this->admin_model->checkOldPass($data_array);

            if ($query1) {

                if ($check) {
                    $query2 = $this->admin_model->saveNewPass($newdata);
                    if ($query2) {
                        $data['error_message'] = "<div class='alert alert-success'>" . $this->lang->line("password_changed_successfully") . "</div>";
                        $this->load->view('layout/header', $data);
                        $this->load->view('admin/change_password', $data);
                        $this->load->view('layout/footer', $data);
                    }
                } else {
                    $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("invalid_current_password") . "</div>";
                    $this->load->view('layout/header', $data);
                    $this->load->view('admin/change_password', $data);
                    $this->load->view('layout/footer', $data);
                }
            } else {

                $data['error_message'] = "<div class='alert alert-danger'>" . $this->lang->line("invalid_current_password") . "</div>";
                $this->load->view('layout/header', $data);
                $this->load->view('admin/change_password', $data);
                $this->load->view('layout/footer', $data);
            }
        }
    }

    public function pdf_report()
    {
        $data        = array();
        $html        = $this->load->view('reports/students_detail', $data, true);
        $pdfFilePath = "output_pdf_name.pdf";
        $this->load->library('m_pdf');
        /** @var stdClass $m_pdf */
        $m_pdf = $this->m_pdf;
        $m_pdf->pdf->WriteHTML($html);
        $m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function downloadbackup($file)
    {
        $this->load->helper('download');
        $filepath = "./backup/database_backup/" . $file;
        $data     = file_get_contents($filepath);
        $name     = $file;
        force_download($name, $data);
    }

    public function dropbackup($file)
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_delete')) {
            access_denied();
        }
        unlink('./backup/database_backup/' . $file);
        redirect('admin/admin/backup');
    }

    public function search()
    {
        $search_text=$this->input->post('search_text1');
        if(!isset($search_text)){
            $search_text="";
        }

        $data['title']           = 'Search';
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['search_text']     = trim($search_text);
        $userdata                = $this->customlib->getUserData();
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $carray                  = array();
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/search', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getCollectionbymonth()
    {
        $result = $this->admin_model->getMonthlyCollection();
        return $result;
    }

    public function getCollectionbyday($date)
    {
        $result = $this->admin_model->getCollectionbyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebyday($date)
    {
        $result = $this->admin_model->getExpensebyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebymonth()
    {
        $result = $this->admin_model->getMonthlyExpense();
        return $result;
    }

    public function whatever($feecollection_array, $start_month_date, $end_month_date)
    {
        $return_amount = 0;
        $st_date       = strtotime($start_month_date);
        $ed_date       = strtotime($end_month_date);
        if (!empty($feecollection_array)) {
            while ($st_date <= $ed_date) {
                $date = date('Y-m-d', $st_date);
                foreach ($feecollection_array as $key => $value) {

                    if ($value['date'] == $date) {

                        if (is_numeric($value['amount']) && is_numeric($value['amount_fine'])) {
                        $return_amount = $return_amount + $value['amount'] + $value['amount_fine'];
                    }
                    }
                }
                $st_date = $st_date + 86400;
            }
        } else {

        }

        return $return_amount;
    }

    public function startmonthandend()
    {
        $startmonth = $this->setting_model->getStartMonth();
        if ($startmonth == 1) {
            $endmonth = 12;
        } else {
            $endmonth = $startmonth - 1;
        }
        return array($startmonth, $endmonth);
    }

    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('sql');
            $temp        = explode(".", $_FILES["file"]["name"]);
            $extension   = end($temp);
            $error       = '';
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["file"]["type"] != 'application/octet-stream') {
                // @phpstan-ignore-next-line
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_type_not_allowed"));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                // @phpstan-ignore-next-line
                $this->form_validation->set_message('handle_upload', $this->lang->line("extension_not_allowed"));
                return false;
            }
            if ($_FILES["file"]["size"] > 102400000) {
                // @phpstan-ignore-next-line
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_size_shoud_be_less_than") . ' 100 MB');
                return false;
            }
            return true;
        } else {
            // @phpstan-ignore-next-line
            $this->form_validation->set_message('handle_upload', $this->lang->line("the_file_field_is_required"));
            return false;
        }
    }

    public function generate_key($length = 12)
    {
        $str        = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max        = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function addCronsecretkey($id)
    {
        $key  = $this->generate_key(25);
        $data = array('cron_secret_key' => $key);
        $this->setting_model->add_cronsecretkey($data, $id);
        redirect('admin/admin/backup');
    }

    public function updateandappCode()
    {
        $this->form_validation->set_rules('app-email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->andapp_update();
        }
    }

    public function filetype()
    {
        if (!$this->rbac->hasPrivilege('fees_type', 'can_view')) {
            access_denied();
        }
        
        $data          = array();
        $data['title'] = 'File Type List';
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/filetype');
        $data['filetype'] = $this->filetype_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/filetype', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addfiletype()
    {
        $this->form_validation->set_rules('file_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('image_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('file_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('file_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'file_extension'  => form_error('file_extension'),
                'file_mime'       => form_error('file_mime'),
                'image_extension' => form_error('image_extension'),
                'image_mime'      => form_error('image_mime'),
                'image_size'      => form_error('image_size'),
                'file_size'       => form_error('file_size'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $insert_array = array(
                'file_extension'  => $this->input->post('file_extension'),
                'file_mime'       => $this->input->post('file_mime'),
                'image_extension' => $this->input->post('image_extension'),
                'image_mime'      => $this->input->post('image_mime'),
                'file_size'       => $this->input->post('file_size'),
                'image_size'      => $this->input->post('image_size'),
            );

            $this->filetype_model->add($insert_array);

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function validate_extension($extension)
    {
        if (preg_match('/^([A-Za-z0-9]+)(,\s[A-Za-z0-9]+)*$/', $extension)) {
            return true;
        } else {
            // @phpstan-ignore-next-line
            $this->form_validation->set_message('validate_extension', 'The %s field must be like jpg, jpeg');
            return false;
        }
    }

    public function validate_mime($mime)
    {
        if (preg_match('/^([A-Za-z0-9-.+\/]+)(,\s[A-Za-z0-9-.+\/]+)*$/', $mime)) {
            return true;
        } else {
            // @phpstan-ignore-next-line
            $this->form_validation->set_message('validate_mime', 'The %s field must be like audio/mp4, video/mp4');
            return false;
        }
    }

    public function updateaddon()
    {
        $this->form_validation->set_rules('app-email', $this->lang->line('email'), 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', $this->lang->line('purchase_code'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );

            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->addon_update();
        }
    }

    public function searchvalidation()
    {
        $search_text1 = $this->input->post('search_text1');
        $params       = array('search_text1' => $search_text1);
        $array        = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function search_text()
    {
        $search_text1 = $this->input->post('search_text');
        $params       = array('search_text' => $search_text1);
        $array        = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function dtstudentlist()
    {
        $search_text     = $this->input->post('search_text');
        $sch_setting     = $this->sch_setting_detail;
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $classlist       = $this->class_model->get();
        $carray          = array();
        if (!empty($classlist)) {
            foreach ($classlist as $ckey => $cvalue) {
                $carray[] = $cvalue["id"];
            }
        }
        $search=$this->input->post('search');

        $resultlist      = $this->student_model->searchFullText($search_text, $carray);
        $start           = $this->input->post('start');
        $length          = $this->input->post('length');
     
        $resultlist_view = $this->student_model->getSearchFullView($search_text, $start, $length,$search, $carray);

        $data = array(
            'resultlist'      => $resultlist_view,
            'sch_setting'     => $this->sch_setting_detail,
            'adm_auto_insert' => $this->sch_setting_detail->adm_auto_insert,
            'currency_symbol' => $this->customlib->getSchoolCurrencyFormat(),
        );

        $resultlist_view = $this->load->view('admin/resultlist_view', $data, true);

        $fields   = $this->customfield_model->get_custom_fields('students', 1);
        $students = json_decode($resultlist);
        $dt_data  = array();
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {

                $editbtn    = '';
                $deletebtn  = '';
                $viewbtn    = '';
                $collectbtn = "";
                $viewbtn    = "<a href='" . base_url() . "student/view/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('show') . "'><i class='fa fa-reorder'></i></a>";

                if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "student/edit/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) {

                    $collectbtn = "<a href='" . base_url() . "studentfee/addfee/" . $student->student_session_id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' title='" . $this->lang->line('add_fees') . "'><span >" . $currency_symbol . "</a>";
                }

                $row   = array();
                $row[] = $student->admission_no;
                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";
                $row[] = $student->roll_no;
                $row[] = $student->class . "(" . $student->section . ")";
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }

                $row[] = $this->customlib->dateformat($student->dob);

                $row[] = $this->lang->line(strtolower($student->gender));
                if ($sch_setting->category) {
                    $row[] = $student->category;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno;
                }

                foreach ($fields as $fields_key => $fields_value) {

                    $custom_name   = $fields_value->name;
                    $display_field = $student->$custom_name;
                    if ($fields_value->type == "link") {
                        $display_field = "<a href=" . $student->$custom_name . " target='_blank'>" . $student->$custom_name . "</a>";
                    }
                    $row[] = $display_field;
                }
                $row[] = $viewbtn . '' . $editbtn . '' . $collectbtn;
                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($students->draw),
            "recordsTotal"    => intval($students->recordsTotal),
            "recordsFiltered" => intval($students->recordsFiltered),
            "data"            => $dt_data,
            "resultlist_view" => $resultlist_view,
        );
        echo json_encode($json_data);

    }

}
