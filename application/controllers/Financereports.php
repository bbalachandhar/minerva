<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financereports extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->time               = strtotime(date('d-m-Y H:i:s'));
        $this->payment_mode       = $this->customlib->payment_mode();
        $this->search_type        = $this->customlib->get_searchtype();
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->library('media_storage');
        $this->load->model("module_model");
        $this->load->model('Department_model');
        $this->load->model('customstudentfeemaster_model');
        $this->load->model('gateway_ins_model');
        $this->load->model('paymentsetting_model');
        $this->load->library('gateway_ins/billdesk_lib');
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function finance()
    {
        $this->session->set_userdata('top_menu', 'Financereports');
        $this->session->set_userdata('sub_menu', 'Financereports/finance');
        $this->session->set_userdata('subsub_menu', '');
        $this->load->view('layout/header');
        $this->load->view('financereports/finance');
        $this->load->view('layout/footer');
    }

    public function incidental_fee_report()
    {
        if (!$this->rbac->hasPrivilege('incidental_fee_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/incidental_fee_report');

        $this->load->model('incidental_fee_type_model');
        $this->load->model('incidental_fee_assignment_model');
        $this->load->model('incidental_fee_collection_model');
        $this->load->model('class_model');
        $this->load->model('student_model');

        $data['title'] = 'Incidental Fee Report';
        $data['fee_types'] = $this->incidental_fee_type_model->get();
        $data['classes'] = $this->class_model->get();
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['sch_setting'] = $this->sch_setting_detail;

        $current_session_id = $this->setting_model->getCurrentSession();
        $data['current_session_id'] = $current_session_id;

        $this->form_validation->set_rules('search_type', $this->lang->line('search_duration'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $data['collections'] = array();
            $data['assignments'] = array();
        } else {
            $search_type = $this->input->post('search_type');
            $session_id = $current_session_id;
            $fee_type_id = $this->input->post('fee_type_id');
            $class_id = $this->input->post('class_id');
            $student_id = $this->input->post('student_id');

            $dates = $this->customlib->get_betweendate($search_type);
            $start_date = date('Y-m-d', strtotime($dates['from_date']));
            $end_date = date('Y-m-d', strtotime($dates['to_date']));

            $filters = array(
                'session_id' => $session_id,
                'fee_type_id' => $fee_type_id,
                'class_id' => $class_id,
                'student_id' => $student_id,
                'start_date' => $start_date,
                'end_date' => $end_date
            );

            $data['collections'] = $this->incidental_fee_collection_model->get_collections_report($filters);

            $total_amount_collected = 0;
            if (!empty($data['collections'])) {
                foreach ($data['collections'] as $collection) {
                    $total_amount_collected += $collection['amount_collected'];
                }
            }
            $data['total_amount_collected'] = $total_amount_collected;
            // For assignments report, we might need a separate method in the model
            // For now, let's focus on collections report first.
            // $data['assignments'] = $this->incidental_fee_assignment_model->get_assignments_report($filters);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/incidental_fee_report', $data); // New view for incidental reports
        $this->load->view('layout/footer', $data);
    }

    public function reportduefees()
    {
        if (!$this->rbac->hasPrivilege('balance_fees_statement', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/reportduefees');
        $data                = array();
        $data['title']       = 'student fees';
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
        $data['sch_setting'] = $this->sch_setting_detail;
        if ($this->input->server('REQUEST_METHOD') == "POST") {
            $department_id      = $this->input->post('department_id');
            if(!empty($department_id)){
                $data['classlist']   = $this->class_model->getClassesByDepartment($department_id);
            }
            $date               = date('Y-m-d');
            $class_id           = $this->input->post('class_id');
            $section_id         = $this->input->post('section_id');
            $data['class_id']   = $class_id;
            $data['section_id'] = $section_id;
            $fees_dues          = $this->studentfeemaster_model->getStudentDueFeeTypesByDate($date, $class_id, $section_id, $department_id); // Pass department_id
            $students_list      = array();

            if (!empty($fees_dues)) {
                foreach ($fees_dues as $fee_due_key => $fee_due_value) {
                    $amount_paid = 0;

                    if (isJSON($fee_due_value->amount_detail)) {
                        $student_fees_array = json_decode($fee_due_value->amount_detail);
                        foreach ($student_fees_array as $fee_paid_key => $fee_paid_value) {
                            $amount_paid += ($fee_paid_value->amount + $fee_paid_value->amount_discount);
                        }
                    }
                    if ($amount_paid < $fee_due_value->fee_amount || ($amount_paid < $fee_due_value->amount && $fee_due_value->is_system)) {

                        $students_list[$fee_due_value->student_session_id]['admission_no']             = $fee_due_value->admission_no;
                        $students_list[$fee_due_value->student_session_id]['class_id']             = $fee_due_value->class_id;
                        $students_list[$fee_due_value->student_session_id]['section_id']             = $fee_due_value->section_id;
                        $students_list[$fee_due_value->student_session_id]['student_id']             = $fee_due_value->student_id;
                        $students_list[$fee_due_value->student_session_id]['roll_no']                  = $fee_due_value->roll_no;
                        $students_list[$fee_due_value->student_session_id]['admission_date']           = $fee_due_value->admission_date;
                        $students_list[$fee_due_value->student_session_id]['firstname']                = $fee_due_value->firstname;
                        $students_list[$fee_due_value->student_session_id]['middlename']               = $fee_due_value->middlename;
                        $students_list[$fee_due_value->student_session_id]['lastname']                 = $fee_due_value->lastname;
                        $students_list[$fee_due_value->student_session_id]['father_name']              = $fee_due_value->father_name;
                        $students_list[$fee_due_value->student_session_id]['image']                    = $fee_due_value->image;
                        $students_list[$fee_due_value->student_session_id]['mobileno']                 = $fee_due_value->mobileno;
                        $students_list[$fee_due_value->student_session_id]['email']                    = $fee_due_value->email;
                        $students_list[$fee_due_value->student_session_id]['state']                    = $fee_due_value->state;
                        $students_list[$fee_due_value->student_session_id]['city']                     = $fee_due_value->city;
                        $students_list[$fee_due_value->student_session_id]['pincode']                  = $fee_due_value->pincode;
                        $students_list[$fee_due_value->student_session_id]['class']                    = $fee_due_value->class;
                        $students_list[$fee_due_value->student_session_id]['section']                  = $fee_due_value->section;
                        $students_list[$fee_due_value->student_session_id]['fee_groups_feetype_ids'][] = $fee_due_value->fee_groups_feetype_id;
                    }
                }
            }

            if (!empty($students_list)) {
                foreach ($students_list as $student_key => $student_value) {
                    $students_list[$student_key]['fees_list'] = $this->studentfeemaster_model->studentDepositByFeeGroupFeeTypeArray($student_key, $student_value['fee_groups_feetype_ids']);
                    $students_list[$student_key]['transport_fees']       = array();
                    $student               = $this->student_model->get($student_value['student_id']);

                    if(!empty($student)){
                        $route_pickup_point_id = $student['route_pickup_point_id'];                    
                        $student_session_id    = $student['student_session_id'];
                    }else{
                        $route_pickup_point_id = '';                    
                        $student_session_id    = '';   
                    }
                    
                    $transport_fees = [];
                    $module = $this->module_model->getPermissionByModulename('transport');

                    if ($module['is_active']) {
                        $transport_fees        = $this->studentfeemaster_model->getStudentTransportFees($student_session_id, $route_pickup_point_id);
                    }
                    $students_list[$student_key]['transport_fees']       = $transport_fees;
                }
            }

            $data['student_due_fee'] = $students_list;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/reportduefees', $data);
        $this->load->view('layout/footer', $data);
    }

    public function printreportduefees()
    {
        $data                = array();
        $data['title']       = 'student fees';
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['sch_setting'] = $this->sch_setting_detail;
        $date                = date('Y-m-d');
        $class_id            = $this->input->post('class_id');
        $section_id          = $this->input->post('section_id');
        $data['class_id']    = $class_id;
        $data['section_id']  = $section_id;
        $fees_dues           = $this->studentfeemaster_model->getStudentDueFeeTypesByDate($date, $class_id, $section_id);
        $students_list       = array();

        if (!empty($fees_dues)) {
            foreach ($fees_dues as $fee_due_key => $fee_due_value) {
                $amount_paid = 0;

                if (isJSON($fee_due_value->amount_detail)) {
                    $student_fees_array = json_decode($fee_due_value->amount_detail);
                    foreach ($student_fees_array as $fee_paid_key => $fee_paid_value) {
                        $amount_paid += ($fee_paid_value->amount + $fee_paid_value->amount_discount);
                    }
                }
               
                if ($amount_paid < $fee_due_value->fee_amount || ($amount_paid < $fee_due_value->amount && $fee_due_value->is_system)) {
                    $students_list[$fee_due_value->student_session_id]['admission_no']             = $fee_due_value->admission_no;
                    $students_list[$fee_due_value->student_session_id]['class_id']             = $fee_due_value->class_id;
                    $students_list[$fee_due_value->student_session_id]['section_id']             = $fee_due_value->section_id;
                    $students_list[$fee_due_value->student_session_id]['student_id']             = $fee_due_value->student_id;
                    $students_list[$fee_due_value->student_session_id]['roll_no']                  = $fee_due_value->roll_no;
                    $students_list[$fee_due_value->student_session_id]['admission_date']           = $fee_due_value->admission_date;
                    $students_list[$fee_due_value->student_session_id]['firstname']                = $fee_due_value->firstname;
                    $students_list[$fee_due_value->student_session_id]['middlename']               = $fee_due_value->middlename;
                    $students_list[$fee_due_value->student_session_id]['lastname']                 = $fee_due_value->lastname;
                    $students_list[$fee_due_value->student_session_id]['father_name']              = $fee_due_value->father_name;
                    $students_list[$fee_due_value->student_session_id]['image']                    = $fee_due_value->image;
                    $students_list[$fee_due_value->student_session_id]['mobileno']                 = $fee_due_value->mobileno;
                    $students_list[$fee_due_value->student_session_id]['email']                    = $fee_due_value->email;
                    $students_list[$fee_due_value->student_session_id]['state']                    = $fee_due_value->state;
                    $students_list[$fee_due_value->student_session_id]['city']                     = $fee_due_value->city;
                    $students_list[$fee_due_value->student_session_id]['pincode']                  = $fee_due_value->pincode;
                    $students_list[$fee_due_value->student_session_id]['class']                    = $fee_due_value->class;
                    $students_list[$fee_due_value->student_session_id]['section']                  = $fee_due_value->section;
                    $students_list[$fee_due_value->student_session_id]['fee_groups_feetype_ids'][] = $fee_due_value->fee_groups_feetype_id;
                }
            }
        }

        if (!empty($students_list)) {
            foreach ($students_list as $student_key => $student_value) {
                $students_list[$student_key]['fees_list'] = $this->studentfeemaster_model->studentDepositByFeeGroupFeeTypeArray($student_key, $student_value['fee_groups_feetype_ids']);
                $students_list[$student_key]['transport_fees']       = array();
                $student               = $this->student_model->getByStudentSession($student_value['student_id']);

                $route_pickup_point_id = $student['route_pickup_point_id'];
                $student_session_id    = $student['student_session_id'];
                $transport_fees = [];
                $module = $this->module_model->getPermissionByModulename('transport');

                if ($module['is_active']) {

                    $transport_fees        = $this->studentfeemaster_model->getStudentTransportFees($student_session_id, $route_pickup_point_id);
                }
                $students_list[$student_key]['transport_fees']       = $transport_fees;
            }
        }
        $data['student_due_fee'] = $students_list;
        $page                    = $this->load->view('financereports/_printreportduefees', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function reportdailycollection()
    {
        if (!$this->rbac->hasPrivilege('daily_collection_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/reportdailycollection');
        $data          = array();
        $data['title'] = 'Daily Collection Report';
        $this->form_validation->set_rules('date_from', $this->lang->line('date_from'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date_to', $this->lang->line('date_to'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {

            $date_from          = $this->input->post('date_from');
            $date_to            = $this->input->post('date_to');
            $formated_date_from = strtotime($this->customlib->dateFormatToYYYYMMDD($date_from));
            $formated_date_to   = strtotime($this->customlib->dateFormatToYYYYMMDD($date_to));
            $st_fees            = $this->studentfeemaster_model->getCurrentSessionStudentFees();
            $fees_data          = array();

            for ($i = $formated_date_from; $i <= $formated_date_to; $i += 86400) {
                $fees_data[$i]['amt']                       = 0;
                $fees_data[$i]['count']                     = 0;
                $fees_data[$i]['student_fees_deposite_ids'] = array();
            }

            if (!empty($st_fees)) {
                foreach ($st_fees as $fee_key => $fee_value) {
                    if (isJSON($fee_value->amount_detail)) {
                        $fees_details = (json_decode($fee_value->amount_detail));
                        if (!empty($fees_details)) {
                            foreach ($fees_details as $fees_detail_key => $fees_detail_value) {
                                $date = strtotime($fees_detail_value->date);
                                if ($date >= $formated_date_from && $date <= $formated_date_to) {
                                    if (array_key_exists($date, $fees_data)) {
                                        $fees_data[$date]['amt'] += $fees_detail_value->amount + $fees_detail_value->amount_fine;
                                        $fees_data[$date]['count'] += 1;
                                        $fees_data[$date]['student_fees_deposite_ids'][] = $fee_value->student_fees_deposite_id;
                                    } else {
                                        $fees_data[$date]['amt']                         = $fees_detail_value->amount + $fees_detail_value->amount_fine;
                                        $fees_data[$date]['count']                       = 1;
                                        $fees_data[$date]['student_fees_deposite_ids'][] = $fee_value->student_fees_deposite_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $data['fees_data'] = $fees_data;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/reportdailycollection', $data);
        $this->load->view('layout/footer', $data);
    }

    public function feeCollectionStudentDeposit()
    {
        $data                 = array();
        $date                 = $this->input->post('date');
        $fees_id              = $this->input->post('fees_id');
        $fees_id_array        = explode(',', $fees_id);
        $fees_list            = $this->studentfeemaster_model->getFeesDepositeByIdArray($fees_id_array);
        $data['student_list'] = $fees_list;
        $data['date']         = $date;
        $data['sch_setting']  = $this->sch_setting_detail;
        $page                 = $this->load->view('financereports/_feeCollectionStudentDeposit', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function reportbyname()
    {
        if (!$this->rbac->hasPrivilege('fees_statement', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/reportbyname');
        $data['title']       = 'student fees';
        $data['title']       = 'student fees';
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
        $data['sch_setting'] = $this->sch_setting_detail;

        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('financereports/reportByName', $data);
            $this->load->view('layout/footer', $data);
        } else { {
                $data['student_due_fee'] = array();
                $class_id                = $this->input->post('class_id');
                $section_id              = $this->input->post('section_id');
                $student_id              = $this->input->post('student_id');
                $department_id           = $this->input->post('department_id'); // Retrieve department_id
                if(!empty($department_id)){
                    $data['classlist']   = $this->class_model->getClassesByDepartment($department_id);
                }
                $student_due_fee         = $this->studentfeemaster_model->getStudentFeesByClassSectionStudent($class_id, $section_id, $student_id, $department_id); // Pass department_id
                foreach ($student_due_fee as $key => $value) {
                    $transport_fees = array();
                    $student               = $this->student_model->getByStudentSession($value['student_session_id']);
                    
                    if($student){
						$route_pickup_point_id = $student['route_pickup_point_id'];
						$student_session_id    = $student['student_session_id'];
                    }else{
                        $route_pickup_point_id = '';
                        $student_session_id    = '';
                    }
					
                    $transport_fees = [];
                    $module = $this->module_model->getPermissionByModulename('transport');

                    if ($module['is_active']) {

                        $transport_fees        = $this->studentfeemaster_model->getStudentTransportFeesByStudentSessionId($student_session_id, $route_pickup_point_id);
                    }
                    $student_due_fee[$key]['transport_fees']         = $transport_fees;
                }			 
				 
                $data['student_due_fee'] = $student_due_fee;
                $data['class_id']        = $class_id;
                $data['section_id']      = $section_id;
                $data['student_id']      = $student_id;
                $category                = $this->category_model->get();
                $data['categorylist']    = $category;
                $this->load->view('layout/header', $data);
                $this->load->view('financereports/reportByName', $data);
                $this->load->view('layout/footer', $data);
            }
        }
    }

        public function studentacademicreport()

        {

            if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {

                access_denied();

            }

    

            $this->session->set_userdata('top_menu', 'Reports');

            $this->session->set_userdata('sub_menu', 'Reports/finance');

            $this->session->set_userdata('subsub_menu', 'Reports/finance/studentacademicreport');

                        $data['title']           = 'student fee';

                        $data['payment_type']    = $this->customlib->getPaymenttype();

                        

$data['department_id_selected'] = $this->input->post('department_id');
            $data['class_id_selected'] = $this->input->post('class_id');
            $data['section_id_selected'] = $this->input->post('section_id');

            

                        if (!empty($data['department_id_selected'])) {

                            $data['classlist'] = $this->class_model->getClassesByDepartment($data['department_id_selected']);

                        } else {

                            $data['classlist'] = $this->class_model->get(); // All classes if no department selected

                        }

                        $data['department_list'] = $this->Department_model->getDepartmentType();

                        $data['sch_setting']     = $this->sch_setting_detail;

                        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

            $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

    

            if ($this->form_validation->run() == false) {

                $data['student_due_fee'] = array();

                $data['resultarray']     = array();

                $data['feetype']     = "";

                $data['feetype_arr'] = array();

            } else {

                $student_Array = array();

                $search_type   = $this->input->post('search_type');

                $class_id   = $this->input->post('class_id');

                $section_id = $this->input->post('section_id');

                $department_id = $this->input->post('department_id'); // Retrieve department_id

    

                if (isset($class_id)) {

                    $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id, $this->current_session, $department_id); // Pass department_id

                } else {

                    $studentlist = $this->student_model->getStudents($department_id); // Pass department_id

                }

    

                $student_Array = array();

                if (!empty($studentlist)) {

                    foreach ($studentlist as $key => $eachstudent) {

                        $obj                = new stdClass();

                        $obj->name          = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);

                        $obj->class         = $eachstudent['class'];

                        $obj->section       = $eachstudent['section'];

                        $obj->admission_no  = $eachstudent['admission_no'];

                        $obj->roll_no       = $eachstudent['roll_no'];

                        $obj->father_name   = $eachstudent['father_name'];

    					$obj->mobileno   	= $eachstudent['mobileno'];

                        $student_session_id = $eachstudent['student_session_id'];

                        $student_total_fees = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id);

    

                        if (!empty($student_total_fees)) {

                            $totalfee = 0;

                            $deposit  = 0;

                            $discount = 0;

                            $balance  = 0;

                            $fine     = 0;

                            

                            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

    

                                if (!empty($student_total_fees_value->fees)) {

                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                        $totalfee = $totalfee + $each_fee_value->amount;

                                        

                                        if(isJSON($each_fee_value->amount_detail)){                                        

                                            $amount_detail = json_decode($each_fee_value->amount_detail);

        

                                            if (is_object($amount_detail) && !empty($amount_detail)) {

                                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                    $deposit  = $deposit + $amount_detail_value->amount;

                                                    $discount = $discount + (isset($amount_detail_value->amount_discount) ? $amount_detail_value->amount_discount : 0);

                                                    $fine     = $fine + $amount_detail_value->amount_fine;

                                                }

                                            }

                                        }

                                    }

                                }

                            }

    

                            $obj->totalfee     = $totalfee;

                            $obj->payment_mode = "N/A";

                            $obj->deposit      = $deposit;

                            $obj->fine         = $fine;

                            $obj->discount     = $discount;

                            $obj->balance      = $totalfee - $deposit;

                        } else {

    

                            $obj->totalfee     = 0;

                            $obj->payment_mode = 0;

                            $obj->deposit      = 0;

                            $obj->fine         = 0;

                            $obj->balance      = 0;

                            $obj->discount     = 0;

                        }

    

                        if ($search_type == 'all') {

                            $student_Array[] = $obj;

                        } elseif ($search_type == 'balance') {

                            if ($obj->balance > 0) {

                                $student_Array[] = $obj;

                            }

                        } elseif ($search_type == 'paid') {

                            if ($obj->balance <= 0) {

                                $student_Array[] = $obj;

                            }

                        }

                    }

                }

    

                $classlistdata[]         = array('result' => $student_Array);

                $data['student_due_fee'] = $student_Array;

                $data['resultarray']     = $classlistdata;

            }

    

            $this->load->view('layout/header', $data);

            $this->load->view('financereports/studentAcademicReport', $data);

            $this->load->view('layout/footer', $data);

        }

    public function collection_report()
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }

        $data['collect_by']  = $this->studentfeemaster_model->get_feesreceived_by();
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['group_by']    = $this->customlib->get_groupby();
        $feetype             = $this->feetype_model->get();
        $tnumber = count($feetype);
        $feetype[$tnumber] = array('id' => 'transport_fees', 'type' => 'Transport Fees');

        $data['feetypeList'] = $feetype;
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/collection_report');
        $subtotal = false;

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        if (isset($_POST['collect_by']) && $_POST['collect_by'] != '') {
            $data['received_by'] = $received_by = $_POST['collect_by'];
        } else {
            $data['received_by'] = $received_by = '';
        }

        if (isset($_POST['feetype_id']) && $_POST['feetype_id'] != '') {
            $feetype_id = $_POST['feetype_id'];
        } else {
            $feetype_id = "";
        }

        if (isset($_POST['group']) && $_POST['group'] != '') {
            $data['group_byid'] = $group = $_POST['group'];
            $subtotal           = true;
        } else {
            $data['group_byid'] = $group = '';
        }

        $collect_by = array();
        $collection = array();
        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $this->form_validation->set_rules('search_type', $this->lang->line('search_duration'), 'trim|required|xss_clean');

        $data['classlist']        = $this->class_model->get();
        $data['selected_section'] = '';

        if ($this->form_validation->run() == false) {
            $data['results'] = array();
        } else {

            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');

            $data['selected_section'] = $section_id;

            $data['results'] = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date, $feetype_id, $received_by, $group, $class_id, $section_id);

            if ($group != '') {

                if ($group == 'class') {
                    $group_by = 'class_id';
                } elseif ($group == 'collection') {
                    $group_by = 'received_by';
                } elseif ($group == 'mode') {
                    $group_by = 'payment_mode';
                }

                foreach ($data['results'] as $key => $value) {
                    $collection[$value[$group_by]][] = $value;
                }
            } else {

                $s = 0;
                foreach ($data['results'] as $key => $value) {
                    $collection[$s++] = array($value);
                }
            }

            $data['results'] = $collection;
        }
        $data['subtotal']    = $subtotal;

        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/collection_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function onlinefees_report()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/onlinefees_report');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['group_by']   = $this->customlib->get_groupby();

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $collection = array();
        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));
        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['collectlist'] = array();
        } else {
            $data['collectlist'] = $this->studentfeemaster_model->getOnlineFeeCollectionReport($start_date, $end_date);
        }

        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/onlineFeesReport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function online_fee_pending_report()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/online_fee_pending_report');
        $data['title'] = $this->lang->line('online_fee_pending_report');
        
        $data['pending_transactions'] = $this->gateway_ins_model->get_pending_transactions('billdesk');
        $data['sch_setting'] = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/onlineFeePendingReport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function duefeesremark()
    {
        if (!$this->rbac->hasPrivilege('balance_fees_report_with_remark', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/duefeesremark');
        $data                = array();
        $data['title']       = 'student fees';
        $data['department_id_selected'] = $this->input->post('department_id');
        $data['class_id_selected'] = $this->input->post('class_id');
        $data['section_id_selected'] = $this->input->post('section_id');

        if (!empty($data['department_id_selected'])) {
            $data['classlist'] = $this->class_model->getClassesByDepartment($data['department_id_selected']);
        } else {
            $data['classlist'] = $this->class_model->get();
        }
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == true) {
            $department_id      = $this->input->post('department_id'); // Retrieve department_id
            if(!empty($department_id)){
                $data['classlist']   = $this->class_model->getClassesByDepartment($department_id);
            }
            $date               = date('Y-m-d');
            $class_id           = $this->input->post('class_id');
            $section_id         = $this->input->post('section_id');
            $data['class_id']   = $class_id;
            $data['section_id'] = $section_id;
            $date               = date('Y-m-d');
            $student_due_fee    = $this->studentfee_model->getDueStudentFeesByDateClassSection($class_id, $section_id, $date, $department_id);
            $students = array();
            if (!empty($student_due_fee)) {
                foreach ($student_due_fee as $student_due_fee_key => $student_due_fee_value) {

                    $amt_due = ($student_due_fee_value['is_system']) ? $student_due_fee_value['previous_balance_amount'] : $student_due_fee_value['amount'];

                    $a = json_decode($student_due_fee_value['amount_detail']);


                    if (!empty($a)) {
                        $amount          = 0;
                        $amount_discount = 0;
                        $amount_fine     = 0;

                        foreach ($a as $a_key => $a_value) {
                            $amount          = $amount + $a_value->amount;
                            $amount_discount = $amount_discount + $a_value->amount_discount;
                            $amount_fine     = $amount_fine + $a_value->amount_fine;
                        }
                        if ($amt_due <= ($amount + $amount_discount)) {
                            unset($student_due_fee[$student_due_fee_key]);
                        } else {

                            if (!array_key_exists($student_due_fee_value['student_session_id'], $students)) {
                                $students[$student_due_fee_value['student_session_id']] = $this->add_new_student($student_due_fee_value);
                            }

                            $students[$student_due_fee_value['student_session_id']]['fees'][] = array(
                                'is_system' => $student_due_fee_value['is_system'],
                                'amount'          => $amt_due,
                                'amount_deposite' => $amount,
                                'amount_discount' => $amount_discount,
                                'amount_fine'     => $amount_fine,
                                'fee_group'       => $student_due_fee_value['fee_group'],
                                'fee_type'        => $student_due_fee_value['fee_type'],
                                'fee_code'        => $student_due_fee_value['fee_code'],

                            );
                        }
                    } else {
                        $amount          = 0;
                        $amount_discount = 0;

                        if ($amt_due <= ($amount + $amount_discount)) {
                            unset($student_due_fee[$student_due_fee_key]);
                        } else {
                            if (!array_key_exists($student_due_fee_value['student_session_id'], $students)) {

                                $students[$student_due_fee_value['student_session_id']] = $this->add_new_student($student_due_fee_value);
                            }
                            $students[$student_due_fee_value['student_session_id']]['fees'][] = array(
                                'is_system' => $student_due_fee_value['is_system'],
                                'amount'          => $amt_due,
                                'amount_deposite' => 0,
                                'amount_discount' => 0,
                                'amount_fine'     => 0,
                                'fee_group'       => $student_due_fee_value['fee_group'],
                                'fee_type'        => $student_due_fee_value['fee_type'],
                                'fee_code'        => $student_due_fee_value['fee_code'],
                            );
                        }
                    }
                }
            }

            $data['student_remain_fees'] = $students;
        }
        $data['start_month'] = $this->sch_setting_detail->start_month;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/duefeesremark', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add_new_student($student)
    {		 
        $new_student = array(
            'id'                 => $student['id'],
            'student_session_id' => $student['student_session_id'],
            'class'              => $student['class'],
            'section_id'         => $student['section_id'],
            'section'            => $student['section'],
            'admission_no'       => $student['admission_no'],
            'roll_no'            => $student['roll_no'],
            'admission_date'     => $student['admission_date'],
            'firstname'          => $student['firstname'],
            'middlename'         => $student['middlename'],
            'lastname'           => $student['lastname'],
            'image'              => $student['image'],
            'mobileno'           => $student['mobileno'],
            'email'              => $student['email'],
            'state'              => $student['state'],
            'city'               => $student['city'],
            'pincode'            => $student['pincode'],
            'religion'           => $student['religion'],
            'dob'                => $student['dob'],
            'current_address'    => $student['current_address'],
            'permanent_address'  => $student['permanent_address'],
            'category_id'        => $student['category_id'],
            'category'           => $student['category'],
            'adhar_no'           => $student['adhar_no'],
            'samagra_id'         => $student['samagra_id'],
            'bank_account_no'    => $student['bank_account_no'],
            'bank_name'          => $student['bank_name'],
            'ifsc_code'          => $student['ifsc_code'],
            'guardian_name'      => $student['guardian_name'],
            'guardian_relation'  => $student['guardian_relation'],
            'guardian_phone'     => $student['guardian_phone'],
            'guardian_address'   => $student['guardian_address'],
            'is_active'          => $student['is_active'],
            'father_name'        => $student['father_name'],
            'rte'                => $student['rte'],
            'gender'             => $student['gender'],

        );
        return $new_student;
    }

    public function printduefeesremark()
    {
        if (!$this->rbac->hasPrivilege('fees_statement', 'can_view')) {
            access_denied();
        }

        $date                = date('Y-m-d');
        $class_id            = $this->input->post('class_id');
        $section_id          = $this->input->post('section_id');
        $data['class_id']    = $class_id;
        $data['section_id']  = $section_id;
        $data['class']       = $this->class_model->get($class_id);
        $data['section']     = $this->section_model->get($section_id);
        $date                = date('Y-m-d');
        $data['sch_setting'] = $this->sch_setting_detail;
        $student_due_fee     = $this->studentfee_model->getDueStudentFeesByDateClassSection($class_id, $section_id, $date);

        $students = array();

        if (!empty($student_due_fee)) {
            foreach ($student_due_fee as $student_due_fee_key => $student_due_fee_value) {
                
                $amt_due = ($student_due_fee_value['is_system']) ? $student_due_fee_value['previous_balance_amount'] : $student_due_fee_value['amount'];

                $a = json_decode($student_due_fee_value['amount_detail']);
                if (!empty($a)) {
                    $amount          = 0;
                    $amount_discount = 0;
                    $amount_fine     = 0;

                    foreach ($a as $a_key => $a_value) {
                        $amount          = $amount + $a_value->amount;
                        $amount_discount = $amount_discount + $a_value->amount_discount;
                        $amount_fine     = $amount_fine + $a_value->amount_fine;
                    }
                    if ($amt_due <= ($amount + $amount_discount)) {
                        unset($student_due_fee[$student_due_fee_key]);
                    } else {

                        if (!array_key_exists($student_due_fee_value['student_session_id'], $students)) {
                            $students[$student_due_fee_value['student_session_id']] = $this->add_new_student($student_due_fee_value);
                        }

                        $students[$student_due_fee_value['student_session_id']]['fees'][] = array(
                            'is_system' => $student_due_fee_value['is_system'],
                            'amount'          => $amt_due,
                            'amount_deposite' => $amount,
                            'amount_discount' => $amount_discount,
                            'amount_fine'     => $amount_fine,
                            'fee_group'       => $student_due_fee_value['fee_group'],
                            'fee_type'        => $student_due_fee_value['fee_type'],
                            'fee_code'        => $student_due_fee_value['fee_code'],
                        );
                    }
                } else {
                    $amount          = 0;
                    $amount_discount = 0;

                    if ($amt_due <= ($amount + $amount_discount)) {
                        unset($student_due_fee[$student_due_fee_key]);
                    } else {
                        if (!array_key_exists($student_due_fee_value['student_session_id'], $students)) {
                            $students[$student_due_fee_value['student_session_id']] = $this->add_new_student($student_due_fee_value);
                        }
                        $students[$student_due_fee_value['student_session_id']]['fees'][] = array(
                            'is_system' => $student_due_fee_value['is_system'],
                            'amount'          => $amt_due,
                            'amount_deposite' => 0,
                            'amount_discount' => 0,
                            'amount_fine'     => 0,
                            'fee_group'       => $student_due_fee_value['fee_group'],
                            'fee_type'        => $student_due_fee_value['fee_type'],
                            'fee_code'        => $student_due_fee_value['fee_code'],
                        );
                    }
                }
            }
        }

        $data['student_remain_fees'] = $students;
        $page = $this->load->view('financereports/_printduefeesremark', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function income()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/income');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/income', $data);
        $this->load->view('layout/footer', $data);
    }

    public function searchreportvalidation()
    {
        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $error = array();

            $error['search_type'] = form_error('search_type');

            $array = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        } else {
            $search_type = $this->input->post('search_type');
            $date_from   = "";
            $date_to     = "";
            if ($search_type == 'period') {

                $date_from = $this->input->post('date_from');
                $date_to   = $this->input->post('date_to');
            }

            $params = array('search_type' => $search_type, 'date_from' => $date_from, 'date_to' => $date_to);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }
    }

    public function getincomelistbydt()
    {
        $search_type = $this->input->post('search_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');

        if ($search_type == "") {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        } else {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        $incomeList = $this->income_model->search("", $start_date, $end_date);

        $incomeList      = json_decode($incomeList);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        $grand_total     = 0;
        if (!empty($incomeList->data)) {
            foreach ($incomeList->data as $key => $value) {
                $grand_total += $value->amount;

                $row   = array();
                $row[] = $value->name;
                $row[] = $value->invoice_no;
                $row[] = $value->income_category;
                $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->date));
                $row[] = $currency_symbol . amountFormat($value->amount);
                $dt_data[] = $row;
            }
            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('grand_total') . "</b>";
            $footer_row[] = $currency_symbol . amountFormat($grand_total);
            $dt_data[]    = $footer_row;
        }

        $json_data = array(
            "draw"            => intval($incomeList->draw),
            "recordsTotal"    => intval($incomeList->recordsTotal),
            "recordsFiltered" => intval($incomeList->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function expense()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/expense');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';

        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        } else {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/expense', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getexpenselistbydt()
    {
        $search_type = $this->input->post('search_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');

        if ($search_type == "") {
            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        } else {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $expenseList   = $this->expense_model->search('', $start_date, $end_date);

        $m               = json_decode($expenseList);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        $grand_total     = 0;
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $grand_total += $value->amount;

                $row       = array();
                $row[]     = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->date));
                $row[]     = $value->exp_category;
                $row[]     = $value->name;
                $row[]     = $value->invoice_no;
                $row[]     = $currency_symbol . amountFormat($value->amount);
                $dt_data[] = $row;
            }
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('grand_total') . "</b>";
            $footer_row[] = "<b>" . $currency_symbol . amountFormat($grand_total) . "</b>";
            $dt_data[]    = $footer_row;
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function payroll()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/payroll');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label']        = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $data['payment_mode'] = $this->payment_mode;

        $result              = $this->payroll_model->getbetweenpayrollReport($start_date, $end_date);
        $data['payrollList'] = $result;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/payroll', $data);
        $this->load->view('layout/footer', $data);
    }

    public function incomegroup()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/incomegroup');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';
        $data['headlist']    = $this->incomehead_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/incomegroup', $data);
        $this->load->view('layout/footer', $data);
    }

    public function dtincomegroupreport()
    {
        $search_type = $this->input->post('search_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');
        $head        = $this->input->post('head');

        if (isset($search_type) && $search_type != '') {

            $dates               = $this->customlib->get_betweendate($search_type);
            $data['search_type'] = $_POST['search_type'];
        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }
        $data['head_id'] = $head_id = "";
        if (isset($_POST['head']) && $_POST['head'] != '') {
            $data['head_id'] = $head_id = $_POST['head'];
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label']   = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $incomeList      = $this->income_model->searchincomegroup($start_date, $end_date, $head_id);
        $m               = json_decode($incomeList);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        $grand_total     = 0;

        if (!empty($m->data)) {
            $grd_total  = 0;
            $inchead_id = 0;
            $count      = 0;
            foreach ($m->data as $key => $value) {
                $income_head[$value->head_id][] = $value;
            }

            foreach ($m->data as $key => $value) {
                $inc_head_id  = $value->head_id;
                $total_amount = "<b>" . $value->amount . "</b>";
                $grd_total += $value->amount;
                $row = array();
                if ($inchead_id == $inc_head_id) {
                    $row[] = "";
                    $count++;
                } else {
                    $row[] = $value->income_category;
                    $count = 0;
                }
                $row[]      = $value->id;
                $row[]      = $value->name;
                $row[]      = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->date));
                $row[]      = $value->invoice_no;
                $row[]      = amountFormat($value->amount);
                $dt_data[]  = $row;
                $inchead_id = $value->head_id;
                $sub_total  = 0;
                if ($count == (count($income_head[$value->head_id]) - 1)) {
                    foreach ($income_head[$value->head_id] as $inc_headkey => $inc_headvalue) {
                        $sub_total += $inc_headvalue->amount;
                    }
                    $amount_row   = array();
                    $amount_row[] = "";
                    $amount_row[] = "";
                    $amount_row[] = "";
                    $amount_row[] = "";
                    $amount_row[] = "<b>" . $this->lang->line('sub_total') . "</b>";
                    $amount_row[] = "<b>" . $currency_symbol . amountFormat($sub_total) . "</b>";
                    $dt_data[]    = $amount_row;
                }
            }

            $grand_total  = "<b>" . $currency_symbol . amountFormat($grd_total) . "</b>";
            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('total') . "</b>";
            $footer_row[] = $grand_total;
            $dt_data[]    = $footer_row;
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function getgroupreportparam()
    {
        $search_type = $this->input->post('search_type');
        $head        = $this->input->post('head');
        $date_from = "";
        $date_to   = "";
        if ($search_type == 'period') {

            $date_from = $this->input->post('date_from');
            $date_to   = $this->input->post('date_to');
        }

        $params = array('search_type' => $search_type, 'head' => $head, 'date_from' => $date_from, 'date_to' => $date_to);
        $array  = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }

    public function expensegroup()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/expensegroup');
        $data['searchlist']  = $this->customlib->get_searchtype();
        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';
        $data['headlist']    = $this->expensehead_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/expensegroup', $data);
        $this->load->view('layout/footer', $data);
    }

    public function dtexpensegroupreport()
    {
        $search_type = $this->input->post('search_type');
        $date_from   = $this->input->post('date_from');
        $date_to     = $this->input->post('date_to');
        $head        = $this->input->post('head');

        $data['date_type']   = $this->customlib->date_type();
        $data['date_typeid'] = '';

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $data['head_id'] = $head_id = "";
        if (isset($_POST['head']) && $_POST['head'] != '') {
            $data['head_id'] = $head_id = $_POST['head'];
        }

        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $result        = $this->expensehead_model->searchexpensegroup($start_date, $end_date, $head_id);

        $m               = json_decode($result);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        $grand_total     = 0;
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $expense_head[$value->exp_head_id][] = $value;
            }

            $grd_total  = 0;
            $exphead_id = 0;
            $count      = 0;
            foreach ($m->data as $key => $value) {

                $exp_head_id  = $value->exp_head_id;
                $total_amount = "<b>" . $value->total_amount . "</b>";
                $grd_total += $value->total_amount;
                $row = array();

                if ($exphead_id == $exp_head_id) {
                    $row[] = "";
                    $count++;
                } else {
                    $row[] = $value->exp_category;
                    $count = 0;
                }

                $row[]      = $value->id;
                $row[]      = $value->name;
                $row[]      = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->date));
                $row[]      = $value->invoice_no;
                $row[]      = amountFormat($value->amount);
                $dt_data[]  = $row;
                $exphead_id = $value->exp_head_id;
                $sub_total  = 0;
                if ($count == (count($expense_head[$value->exp_head_id]) - 1)) {
                    foreach ($expense_head[$value->exp_head_id] as $exp_headkey => $exp_headvalue) {
                        $sub_total += $exp_headvalue->amount;
                    }
                    $amount_row   = array();
                    $amount_row[] = "";
                    $amount_row[] = "";
                    $amount_row[] = "";
                    $amount_row[] = "";
                    $amount_row[] = "<b>" . $this->lang->line('sub_total') . "</b>";
                    $amount_row[] = "<b>" . $currency_symbol . amountFormat($sub_total) . "</b>";
                    $dt_data[]    = $amount_row;
                }
            }

            $grand_total  = "<b>" . $currency_symbol . amountFormat($grd_total) . "</b>";
            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('total') . "</b>";
            $footer_row[] = $grand_total;
            $dt_data[]    = $footer_row;
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function onlineadmission()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/onlineadmission');
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['group_by']   = $this->customlib->get_groupby();

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
        } else {

            $dates               = $this->customlib->get_betweendate('this_year');
            $data['search_type'] = '';
        }

        $collection = array();
        $start_date = date('Y-m-d', strtotime($dates['from_date']));
        $end_date   = date('Y-m-d', strtotime($dates['to_date']));
        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $data['collectlist'] = array();
        } else {

            $data['collectlist'] = $this->onlinestudent_model->getOnlineAdmissionFeeCollectionReport($start_date, $end_date);
        }
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/onlineadmission', $data);
        $this->load->view('layout/footer', $data);
    }
	
	    public function incomeexpensebalancereport()
	    {	
			$this->session->set_userdata('top_menu', 'Reports');
	        $this->session->set_userdata('sub_menu', 'Reports/finance');
	        $this->session->set_userdata('subsub_menu', 'Reports/finance/incomeexpensebalancereport');
			$data['searchlist']  = $this->customlib->get_searchtype();
			
			if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
	
	            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
	            $data['search_type'] = $_POST['search_type'];
	        } else {
	
	            $dates               = $this->customlib->get_betweendate('this_year');
	            $data['search_type'] = '';
	        }
	
	        $collection = array();
	        $start_date = date('Y-m-d', strtotime($dates['from_date']));
	        $end_date   = date('Y-m-d', strtotime($dates['to_date']));
	        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');
	
	        if ($this->form_validation->run() == false) {
	            $data['incomeexpensebalancereport'] = '';
	        } else {
	            $data['incomeexpensebalancereport'] = $this->income_model->incomeexpensebalancereport($start_date, $end_date);
	        }	
			
	        $this->load->view('layout/header', $data);
	        $this->load->view('financereports/incomeexpensebalancereport', $data);
	        $this->load->view('layout/footer', $data);
	    } 
	
	        public function deleted_payments_report()
	        {
	            $this->session->set_userdata('top_menu', 'Reports');
	            $this->session->set_userdata('sub_menu', 'Reports/finance');
	            $this->session->set_userdata('subsub_menu', 'Reports/finance/deleted_payments_report');
	            $data['searchlist'] = $this->customlib->get_searchtype();
	    
	            if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
	                $dates = $this->customlib->get_betweendate($_POST['search_type']);
	                $data['search_type'] = $_POST['search_type'];
	            } else {
	                $dates = $this->customlib->get_betweendate('this_month');
	                $data['search_type'] = '';
	            }
	    
	            $start_date = date('Y-m-d 00:00:00', strtotime($dates['from_date']));
	                    $end_date   = date('Y-m-d 23:59:59', strtotime($dates['to_date']));
	            
	                    $data['report_data'] = $this->studentfee_model->getDeletedPaymentsReport($start_date, $end_date);
	                    $data['sch_setting'] = $this->sch_setting_detail;
	            
	                    $this->load->view('layout/header', $data);
	                    $this->load->view('financereports/deleted_payments_report', $data);
	                    $this->load->view('layout/footer', $data);
	        }    

        public function print_incidental_receipt($id)

        {

            if (!$this->rbac->hasPrivilege('incidental_fee_report', 'can_view')) {

                access_denied();

            }

    

            $this->load->model('incidental_fee_collection_model');

            $data['collection'] = $this->incidental_fee_collection_model->get_collection_by_id($id);

            $data['sch_setting'] = $this->sch_setting_detail;

            $data['receipt_header'] = $this->setting_model->get_receiptheader();

    

                    $this->load->view('financereports/incidental_fee_print', $data);
            
                }
            
                    public function check_status($unique_id, $gateway_name)
                    {
                        if (!$this->rbac->hasPrivilege('online_fees_report', 'can_view')) {
                            access_denied();
                        }
                
                        try {
                            $verification_response = null;
                            $gateway_ins = $this->gateway_ins_model->get_gateway_ins($unique_id, $gateway_name);
                
                            if ($gateway_ins) {
                                
                                if ($gateway_name == 'billdesk') {
                                    // --- BillDesk Specific Logic ---
                                    $verification_response = $this->billdesk_lib->verify_transaction($unique_id);
                                    
                                    $status = 'failed';
                                    $msg_type = 'error';
                                    $message = "Transaction Verification Failed. Status: " . (isset($verification_response['auth_status']) ? $verification_response['auth_status'] : 'Unknown');
                
                                    if (isset($verification_response['auth_status']) && $verification_response['auth_status'] == '0300') {
                                        $status = 'success';
                                        $msg_type = 'success';
                                        $message = "Transaction Verified Successfully. Fees Updated.";
                
                                        // Process Payment
                                        $params = json_decode($gateway_ins['parameter_details'], true);
                                        $transaction_id = isset($verification_response['transactionid']) ? $verification_response['transactionid'] : $unique_id;
                                        
                                        $bulk_fees = array();
                                        if (!empty($params['student_fees_master_array'])) {
                                            foreach ($params['student_fees_master_array'] as $fee_key => $fee_value) {
                                                $json_array = array(
                                                    'amount'          => $fee_value['amount_balance'],
                                                    'date'            => date('Y-m-d'),
                                                    'amount_discount' => $fee_value['applied_fee_discount'],
                                                    'processing_charge_type' => $params['processing_charge_type'],
                                                    'gateway_processing_charge' => $params['gateway_processing_charge'],
                                                    'amount_fine'     => $fee_value['fine_balance'],
                                                    'description'     => "Online fees deposit through BillDesk (Verified). TXN ID: " . $transaction_id,
                                                    'received_by'     => '',
                                                    'payment_mode'    => 'Billdesk',
                                                );
                
                                                $insert_fee_data = array(
                                                    'fee_category' => $fee_value['fee_category'],
                                                    'student_transport_fee_id' => $fee_value['student_transport_fee_id'],
                                                    'student_fees_master_id' => $fee_value['student_fees_master_id'],
                                                    'fee_groups_feetype_id'  => $fee_value['fee_groups_feetype_id'],
                                                    'amount_detail'          => $json_array,
                                                );
                                                $bulk_fees[] = $insert_fee_data;
                                            }
                                            $this->studentfeemaster_model->add_bulk_fee_deposit($bulk_fees, $params['fee_discount_group']);
                                        }
                                    } elseif (isset($verification_response['auth_status']) && $verification_response['auth_status'] == '0002') {
                                        $status = 'pending';
                                        $msg_type = 'warning';
                                        $message = "Transaction is still Pending at BillDesk.";
                                    }
                
                                    $this->gateway_ins_model->update_gateway_ins(array(
                                        'id' => $gateway_ins['id'],
                                        'payment_status' => $status
                                    ));
                
                                    $this->session->set_flashdata('msg', '<div class="alert alert-'.$msg_type.'">'.$message.'</div>');
                                    // --- End BillDesk Logic ---
                                    
                                } elseif ($gateway_name == 'razorpay') {
                                    // Placeholder for Razorpay logic
                                    $this->session->set_flashdata('msg', '<div class="alert alert-info">Razorpay verification not yet implemented.</div>');
                                } else {
                                    $this->session->set_flashdata('msg', '<div class="alert alert-warning">Unknown gateway: ' . $gateway_name . '</div>');
                                }
                
                            } else {
                                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Transaction not found in local records.</div>');
                            }
                
                        } catch (Exception $e) {
                            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Verification Error: ' . $e->getMessage() . '</div>');
                        }
                
                        redirect('financereports/online_fee_pending_report');
                    }            
                    public function balancesummaryreport()        {
            if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) { // Using existing privilege for now
                access_denied();
            }

            $this->session->set_userdata('top_menu', 'Reports');
            $this->session->set_userdata('sub_menu', 'Reports/finance');
            $this->session->set_userdata('subsub_menu', 'Reports/finance/balancesummaryreport');
            $data['title']           = 'Balance Summary Report'; // Changed title
            $data['payment_type']    = $this->customlib->getPaymenttype();

            $data['department_id_selected'] = $this->input->post('department_id');
            $data['class_id_selected'] = $this->input->post('class_id');

            if (!empty($data['department_id_selected'])) {
                $data['classlist'] = $this->class_model->getClassesByDepartment($data['department_id_selected']);
            } else {
                $data['classlist'] = $this->class_model->get();
            }

            $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
            $data['sch_setting']     = $this->sch_setting_detail;
            $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

            // Get all discount types for table headers
            $this->load->model("feediscount_model"); // Load the discount model for this method
            $data['discount_list'] = $this->feediscount_model->get();
            $this->load->model("customstudentfeemaster_model"); // Load customstudentfeemaster_model for this method
            $this->load->model("student_model"); // Load student_model for this method

            $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean'); // Changed to single select for now

            if ($this->form_validation->run() == FALSE) {
                $data['student_due_fee'] = array();
                $data['resultarray']     = array();
                $data['discount_totals_footer'] = array_fill_keys(array_column($data['discount_list'], 'id'), 0);
            } else {
                $student_Array = array();
                $search_type   = $this->input->post('search_type');
                $class_id   = $this->input->post('class_id');
                $section_id = $this->input->post('section_id'); // Not used in this version according to requirement
                $department_id = $this->input->post('department_id'); // Retrieve department_id

                // Always use current session
                $current_session_id = $this->setting_model->getCurrentSession();

                // Fetch students based on selected class_id or all classes
                if ($class_id == 'all') {
                    // Assuming a method to get all students for the current session
                    $studentlist = $this->student_model->getStudentsBySessionAndDepartment($current_session_id, $department_id);
                } else {
                    // Assuming a method to get students by class_id and session
                    $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, null, $current_session_id, $department_id); // Passing null for section_id
                }

                $class_summary = array();
                
                if (!empty($studentlist)) {
                    foreach ($studentlist as $eachstudent) {
                        $class_name = $eachstudent['class'];
                        $student_session_id = $eachstudent['student_session_id'];
    
                        // Initialize class totals if not already set
                        if (!isset($class_summary[$class_name])) {
                            $class_summary[$class_name] = (object) [
                                'class_name' => $class_name,
                                'totalfee' => 0,
                                'deposit' => 0,
                                'fine' => 0,
                                'discount' => 0,
                                'balance' => 0,
                                'last_yr_cf' => 0,
                                'cf_paid' => 0,
                                'cf_balance' => 0,
                                'tuition_demand' => 0,
                                'tuition_paid' => 0,
                                'tuition_balance' => 0, // Added
                                'other_demand' => 0,
                                'other_paid' => 0,
                                'other_balance' => 0, // Added
                                'hostel_demand' => 0,
                                'hostel_paid' => 0,
                                'hostel_balance' => 0, // Added
                                'transport_demand' => 0,
                                'transport_paid' => 0,
                                'transport_balance' => 0, // Added
                                'advance_paid' => 0,
                                'actual_balance' => 0,   // Added
                            ];
                            // Initialize dynamic discount properties
                            foreach ($data['discount_list'] as $discount) {
                                $prop_name = 'discount_' . $discount['id'];
                                $class_summary[$class_name]->$prop_name = 0;
                            }
                        }
    
                        // Get all fees and discounts for the student
                        $fees_data = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id);
    
                        // Aggregate student's fees into class totals
                        $class_summary[$class_name]->tuition_demand += $fees_data->tuition_demand;
                        $class_summary[$class_name]->tuition_paid += $fees_data->tuition_paid;
                        $class_summary[$class_name]->tuition_balance += ($fees_data->tuition_demand - $fees_data->tuition_paid);
                        $class_summary[$class_name]->other_demand += $fees_data->other_demand;
                        $class_summary[$class_name]->other_paid += $fees_data->other_paid;
                        $class_summary[$class_name]->other_balance += ($fees_data->other_demand - $fees_data->other_paid);
                        $class_summary[$class_name]->hostel_demand += $fees_data->hostel_demand;
                        $class_summary[$class_name]->hostel_paid += $fees_data->hostel_paid;
                        $class_summary[$class_name]->hostel_balance += ($fees_data->hostel_demand - $fees_data->hostel_paid);
                        $class_summary[$class_name]->transport_demand += $fees_data->transport_demand;
                        $class_summary[$class_name]->transport_paid += $fees_data->transport_paid;
                        $class_summary[$class_name]->transport_balance += ($fees_data->transport_demand - $fees_data->transport_paid);
                        $class_summary[$class_name]->advance_paid += $fees_data->advance_paid;
    
                        // Sum from tuition, other, hostel, transport demand and paid
                        $totalfee_student = $fees_data->tuition_demand + $fees_data->other_demand + $fees_data->hostel_demand + $fees_data->transport_demand;
                        $total_paid_sum_student = $fees_data->tuition_paid + $fees_data->other_paid + $fees_data->hostel_paid + $fees_data->transport_paid;
    
                        $total_fine_sum_student = 0;
                        $total_discount_sum_student = 0;
    
                        if (!empty($fees_data->fees)) {
                            foreach ($fees_data->fees as $fee_item) {
                                $total_fine_sum_student += $fee_item->total_fine;
                                $total_discount_sum_student += $fee_item->total_discount;
                            }
                        }
    
                        $class_summary[$class_name]->totalfee += $totalfee_student;
                        $class_summary[$class_name]->deposit += $total_paid_sum_student;
                        $class_summary[$class_name]->fine += $total_fine_sum_student;
                        $class_summary[$class_name]->discount += $total_discount_sum_student;
                        $class_summary[$class_name]->balance += $totalfee_student - $total_paid_sum_student;
    
                        // Get and aggregate previous session balance (CF-Demand)
                        $previous_session_balance_data = $this->customstudentfeemaster_model->getPreviousSessionBalance($student_session_id);
                        $last_yr_cf_student = !empty($previous_session_balance_data) ? $previous_session_balance_data->amount : 0;
                        $class_summary[$class_name]->last_yr_cf += $last_yr_cf_student;
    
                        // Get and aggregate amount paid against previous session balance (CF-Paid)
                        $cf_paid_student = $this->customstudentfeemaster_model->getPreviousSessionPaid($student_session_id);
                        $class_summary[$class_name]->cf_paid += $cf_paid_student;
    
                        // Calculate CF-Balance for the class
                        $class_summary[$class_name]->cf_balance += ($last_yr_cf_student - $cf_paid_student);

                        // Get student's applied discounts to find subsidies and accumulate
                        $applied_discounts = $this->feediscount_model->getStudentFeesDiscount($student_session_id);
                        
                        $total_student_discount = 0;
                        foreach ($applied_discounts as $student_discount) {
                            $prop_name = 'discount_' . $student_discount['fees_discount_id'];
                            $discount_amount = 0;
                            if (isset($student_discount['custom_amount']) && $student_discount['custom_amount'] != null) {
                                $discount_amount = $student_discount['custom_amount'];
                            } else {
                                $discount_amount = $student_discount['amount'];
                            }
                            
                            if (property_exists($class_summary[$class_name], $prop_name)) {
                                $class_summary[$class_name]->$prop_name += $discount_amount;
                            }
                            $total_student_discount += $discount_amount;
                        }
                    }
                }
                
                // After aggregating all students for a class
                foreach ($class_summary as $class_name => $summary_obj) {
                    // Formula: TotalBalance - (Govt 7.5 Subsidy + Govt FG Subsidy + (Advance Payments - CF-Paid))
                    // TotalBalance for the class is $summary_obj->balance
                    // Govt 7.5 Subsidy for the class is $summary_obj->govt_7_5_subsidy
                    // Govt FG Subsidy for the class is $summary_obj->govt_fg_subsidy
                    // Advance Payments for the class is $summary_obj->advance_paid
                    // CF-Paid for the class is $summary_obj->cf_paid

                    $total_balance_class = $summary_obj->balance;
                    
                    $total_dynamic_discounts = 0;
                    foreach ($data['discount_list'] as $discount) {
                        $prop_name = 'discount_' . $discount['id'];
                        if (property_exists($summary_obj, $prop_name)) {
                            $total_dynamic_discounts += $summary_obj->$prop_name;
                        }
                    }

                    $advance_minus_cf_paid_class = $summary_obj->advance_paid - $summary_obj->cf_paid;

                    // $class_summary[$class_name]->actual_balance = $total_balance_class - ($total_dynamic_discounts + $advance_minus_cf_paid_class);

                    // Add CF-Balance to the balance for each class
                    $class_summary[$class_name]->balance += $class_summary[$class_name]->cf_balance;
                }

                // Sum up the total subsidies from all classes for the footer
                $discount_totals_footer = array_fill_keys(array_column($data['discount_list'], 'id'), 0);
                foreach ($class_summary as $summary_obj) {
                    foreach ($data['discount_list'] as $discount) {
                        $prop_name = 'discount_' . $discount['id'];
                        if (property_exists($summary_obj, $prop_name)) {
                            $discount_totals_footer[$discount['id']] += $summary_obj->$prop_name;
                        }
                    }
                }
                $data['discount_totals_footer'] = $discount_totals_footer;
                                                        // Convert class_summary associative array to indexed array for the view
                                                $student_Array = array_values($class_summary);    
                log_message('debug', 'Final student_Array passed to view: ' . json_encode($student_Array));
                $data['student_due_fee'] = $student_Array; // Renaming to be consistent if needed, but current_one uses student_due_fee

            }

            $this->load->view('layout/header', $data);
            $this->load->view('financereports/balance_summary_report', $data); // New view
            $this->load->view('layout/footer', $data);
        }

    public function categorywisebalancefeesreport()
    {
        if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) { // Using existing privilege for now
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/categorywisebalancefeesreport');
        $data['title']           = 'Category Wise Balance Fees Report';

        $data['department_id_selected'] = $this->input->post('department_id');
        $data['class_id_selected'] = $this->input->post('class_id');
        $data['section_id_selected'] = $this->input->post('section_id');

        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        $this->load->model("feediscount_model");
        $this->load->model("customstudentfeemaster_model");
        $this->load->model("student_model");

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');

        if (!empty($data['department_id_selected'])) {
            $data['classlist'] = $this->class_model->getClassesByDepartment($data['department_id_selected']);
        } else {
            $data['classlist'] = $this->class_model->get();
        }
        
        if ($this->form_validation->run() == FALSE) {
            $data['category_summary'] = array();
        } else {
            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $department_id = $this->input->post('department_id');

            $current_session_id = $this->setting_model->getCurrentSession();

            if ($class_id == 'all') {
                $studentlist = $this->student_model->getStudentsBySessionAndDepartment($current_session_id, $department_id);
            } else {
                $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id, $current_session_id, $department_id);
            }

            $category_summary = array();
            
            if (!empty($studentlist)) {
                foreach ($studentlist as $eachstudent) {
                    $category_id = $eachstudent['category_id'];
                    if ($category_id == null) {
                        $category_id = "N/A";
                    }

                    if (!isset($category_summary[$category_id])) {
                        $category_summary[$category_id] = (object) [
                            'category_id' => $category_id,
                            'category_name' => $category_id == "N/A" ? "N/A" : $eachstudent['category'],
                            'number_of_students' => 0,
                            'tuition_fee_demand' => 0,
                            'other_fees_demand' => 0,
                            'govt_fg_discounts' => 0,
                            'govt_7_5_discounts' => 0,
                            'total_management_discounts' => 0,
                            'total_paid' => 0,
                            'pending_fee' => 0,
                            'transport_fee_demand' => 0,
                            'transport_fee_paid' => 0,
                            'transport_fee_balance' => 0,
                            'hostel_fee_demand' => 0,
                            'hostel_fee_paid' => 0,
                            'hostel_fee_balance' => 0,
                            'transport_subsidy_7_5' => 0,
                            'hostel_subsidy_7_5' => 0,
                        ];
                    }

                    $category_summary[$category_id]->number_of_students++;
                    
                    $student_session_id = $eachstudent['student_session_id'];
                    $fees_data = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id, $eachstudent['class_id'], $eachstudent['section_id']);

                    $category_summary[$category_id]->tuition_fee_demand += $fees_data->tuition_demand;
                    $category_summary[$category_id]->other_fees_demand += $fees_data->other_demand;
                    $category_summary[$category_id]->total_paid += $fees_data->tuition_paid + $fees_data->other_paid;
                    $category_summary[$category_id]->transport_fee_demand += $fees_data->transport_demand;
                    $category_summary[$category_id]->transport_fee_paid += $fees_data->transport_paid;
                    $category_summary[$category_id]->hostel_fee_demand += $fees_data->hostel_demand;
                    $category_summary[$category_id]->hostel_fee_paid += $fees_data->hostel_paid;
                    
                    $category_summary[$category_id]->transport_fee_balance += $fees_data->transport_demand - $fees_data->transport_paid;
                    $category_summary[$category_id]->hostel_fee_balance += $fees_data->hostel_demand - $fees_data->hostel_paid;

                    $applied_discounts = $this->feediscount_model->getStudentFeesDiscount($student_session_id);

                    foreach ($applied_discounts as $student_discount) {
                        $discount_name = strtolower($student_discount['name']);
                        $discount_amount = isset($student_discount['custom_amount']) && $student_discount['custom_amount'] != null ? $student_discount['custom_amount'] : $student_discount['amount'];

                        if (strpos($discount_name, 'govt fg') !== false) {
                            $category_summary[$category_id]->govt_fg_discounts += $discount_amount;
                        }
                        if (strpos($discount_name, 'govt 7.5') !== false) {
                            $category_summary[$category_id]->govt_7_5_discounts += $discount_amount;
                        }
                        if (strpos($discount_name, 'mgmt') !== false) {
                            $category_summary[$category_id]->total_management_discounts += $discount_amount;
                        }
                        if (strpos($discount_name, '7.5 transport subsidy') !== false) {
                            $category_summary[$category_id]->transport_subsidy_7_5 += $discount_amount;
                        }
                        if (strpos($discount_name, '7.5 hostel subsidy') !== false) {
                            $category_summary[$category_id]->hostel_subsidy_7_5 += $discount_amount;
                        }
                    }
                }

                foreach ($category_summary as $category_id => $summary) {
                    $total_demand = $summary->tuition_fee_demand + $summary->other_fees_demand;
                    $total_discounts = $summary->govt_fg_discounts + $summary->govt_7_5_discounts + $summary->total_management_discounts;
                    $summary->pending_fee = $total_demand - ($summary->total_paid + $total_discounts);
                }
            }
            
            $data['category_summary'] = $category_summary;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/category_wise_balance_fees_report', $data);
        $this->load->view('layout/footer', $data);
    }
}