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
        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['fee_types'] = $this->incidental_fee_type_model->get();
        $data['classes'] = $this->class_model->get();
        $data['current_session_id'] = $this->current_session;

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $search_type = $this->input->post('search_type');
            if ($search_type == 'period') {
                $start_date = $this->input->post('date_from');
                $end_date = $this->input->post('date_to');
            } else {
                $dates = $this->customlib->get_betweendate($search_type);
                $start_date = date('Y-m-d', strtotime($dates['from_date']));
                $end_date = date('Y-m-d', strtotime($dates['to_date']));
            }
            $data['search_type'] = $search_type;
        } else {
            $dates = $this->customlib->get_betweendate('this_year');
            $start_date = date('Y-m-d', strtotime($dates['from_date']));
            $end_date = date('Y-m-d', strtotime($dates['to_date']));
            $data['search_type'] = 'this_year';
        }

        $session_id  = $this->input->post('session_id');
        $fee_type_id = $this->input->post('fee_type_id');
        $class_id    = $this->input->post('class_id');
        $student_id  = $this->input->post('student_id');

        $filters = array(
            'session_id' => $session_id,
            'fee_type_id' => $fee_type_id,
            'class_id' => $class_id,
            'student_id' => $student_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
        );

        $data['collections'] = $this->incidental_fee_collection_model->get_collections_report($filters);

        $total_amount_collected = 0;
        if (!empty($data['collections'])) {
            foreach ($data['collections'] as $collection) {
                $total_amount_collected += $collection['amount_collected'];
            }
        }
        $data['total_amount_collected'] = $total_amount_collected;

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/incidental_fee_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function update_incidental_receipt_no()
    {
        if (!$this->rbac->hasPrivilege('incidental_fee_report', 'can_edit') && !$this->rbac->hasPrivilege('collect_incidental_fee', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $collection_id = (int) $this->input->post('collection_id');
        $application_ref_no = trim((string) $this->input->post('application_ref_no'));

        if ($collection_id <= 0 || $application_ref_no === '') {
            echo json_encode(['status' => 'fail', 'message' => 'Application reference number is required.']);
            return;
        }

        if (strlen($application_ref_no) > 100 || !preg_match('/^[A-Za-z0-9\-\/]+$/', $application_ref_no)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid application reference number format.']);
            return;
        }

        $this->load->model('incidental_fee_collection_model');

        $updated = $this->incidental_fee_collection_model->update_application_ref_no($collection_id, $application_ref_no);

        if ($updated) {
            echo json_encode(['status' => 'success', 'message' => 'Application reference number updated successfully.']);
        } else {
            echo json_encode(['status' => 'fail', 'message' => 'Failed to update application reference number.']);
        }
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

        $data['searchlist'] = $this->customlib->get_searchtype();
        $data['group_by'] = $this->customlib->get_groupby();
        $data['feetypeList'] = $this->feetype_model->get();
        $data['collect_by']  = $this->studentfeemaster_model->get_feesreceived_by();

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $search_type = $this->input->post('search_type');
            if ($search_type == 'period') {
                $start_date = $this->input->post('date_from');
                $end_date = $this->input->post('date_to');
            } else {
                $dates = $this->customlib->get_betweendate($search_type);
                $start_date = date('Y-m-d', strtotime($dates['from_date']));
                $end_date = date('Y-m-d', strtotime($dates['to_date']));
            }
            $data['search_type'] = $search_type;
        } else {
            $dates = $this->customlib->get_betweendate('this_year');
            $start_date = date('Y-m-d', strtotime($dates['from_date']));
            $end_date = date('Y-m-d', strtotime($dates['to_date']));
            $data['search_type'] = 'this_year';
        }

        $this->form_validation->set_rules('search_type', $this->lang->line('search_duration'), 'trim|required|xss_clean');

        $data['classlist']        = $this->class_model->get();
        $data['selected_section'] = '';
        $data['results'] = array();
        $subtotal = 0;

        $feetype_id = $this->input->post('feetype_id');
        $received_by = $this->input->post('collect_by');
        $group = $this->input->post('group');
        $data['received_by'] = $received_by;
        $data['group_byid'] = $group;

        if ($this->form_validation->run() == false) {
            $collection = array();
        } else {

            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');

            $data['selected_section'] = $section_id;

            $data['results'] = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date, $feetype_id, $received_by, $group, $class_id, $section_id);

            if (!empty($data['results'])) {
                foreach ($data['results'] as $row) {
                    $subtotal += ((float) $row['amount'] + (float) $row['amount_fine']);
                }
            }

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
        
        // Default to last month on first visit; POST overrides
        $lm = strtotime('first day of last month');
        $filter_month = $this->input->post('filter_month') ?: date('F', $lm);
        $filter_year  = $this->input->post('filter_year')  ?: (int)date('Y', $lm);
        $data['filter_month'] = $filter_month;
        $data['filter_year']  = $filter_year;

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
            $start_date = date('Y-m-d', strtotime($dates['from_date']));
            $end_date   = date('Y-m-d', strtotime($dates['to_date']));
        } else {
            // Narrow to the selected month only — much faster than full-year scan
            $mn = ctype_digit((string)$filter_month) ? (int)$filter_month : (int)date('n', strtotime($filter_month . ' 1 ' . $filter_year));
            $start_date = $filter_year . '-' . str_pad($mn, 2, '0', STR_PAD_LEFT) . '-01';
            $end_date   = date('Y-m-t', strtotime($start_date));
            $data['search_type'] = '';
        }

        $data['label']        = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $data['payment_mode'] = $this->payment_mode;

        $result = $this->payroll_model->getbetweenpayrollReport($start_date, $end_date, $filter_month, $filter_year);
        // Bulk-fetch all allowances in 2 queries instead of 2 per row (N+1 fix)
        $payslip_ids = !empty($result) ? array_column($result, 'id') : [];
        $bulk_allowances = !empty($payslip_ids) ? $this->payroll_model->getAllowancesBulk($payslip_ids) : [];
        if (!empty($result)) {
            foreach ($result as &$row) {
                $pid = $row['id'];
                $row['earnings_breakdown']  = $bulk_allowances[$pid]['positive'] ?? [];
                $row['deductions_breakdown'] = $bulk_allowances[$pid]['negative'] ?? [];
                // if there is a leave deduction (LOP) add it to the breakdown so it shows under details
                if (!empty($row['leave_deduction']) && $row['leave_deduction'] > 0) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'LOP',
                        'amount' => $row['leave_deduction'],
                        'cal_type' => 'negative'
                    );
                }
                // statutory deductions stored as separate columns; avoid duplicates if allowance already exists
                $existingTypes = array_column($row['deductions_breakdown'], 'allowance_type');
                if (!empty($row['employee_epf']) && $row['employee_epf'] > 0 && !in_array('EPF', $existingTypes)) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'EPF',
                        'amount' => $row['employee_epf'],
                        'cal_type' => 'negative'
                    );
                    $existingTypes[] = 'EPF';
                }
                if (!empty($row['esi_deduction']) && $row['esi_deduction'] > 0 && !in_array('ESI', $existingTypes)) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'ESI',
                        'amount' => $row['esi_deduction'],
                        'cal_type' => 'negative'
                    );
                    $existingTypes[] = 'ESI';
                }
                if (!empty($row['tax']) && $row['tax'] > 0 && !in_array('TDS', $existingTypes)) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'TDS',
                        'amount' => $row['tax'],
                        'cal_type' => 'negative'
                    );
                    $existingTypes[] = 'TDS';
                }
            }
            unset($row);
        }
        $data['payrollList'] = $result;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/payroll', $data);
        $this->load->view('layout/footer', $data);
    }

    public function payrollreportsummary()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/payrollreportsummary');
        $filter_category        = $this->input->post('filter_category') ?: [];
        $filter_category        = is_array($filter_category) ? array_map('intval', array_filter($filter_category)) : [];
        $data['filter_category'] = $filter_category;
        $data['categories']      = $this->db->select('id, name')->order_by('id')->get('staff_designation_category')->result_array();

        // Default to last month on first visit; POST overrides
        $lm = strtotime('first day of last month');
        $filter_month         = $this->input->post('filter_month') ?: date('F', $lm);
        $filter_year          = $this->input->post('filter_year')  ?: (int)date('Y', $lm);
        $data['filter_month'] = $filter_month;
        $data['filter_year']  = $filter_year;

        $mn = ctype_digit((string)$filter_month) ? (int)$filter_month : (int)date('n', strtotime($filter_month . ' 1 ' . $filter_year));
        $start_date = $filter_year . '-' . str_pad($mn, 2, '0', STR_PAD_LEFT) . '-01';
        $end_date   = date('Y-m-t', strtotime($start_date));

        $data['label']        = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
        $data['payment_mode'] = $this->payment_mode;

        $result              = $this->payroll_model->getbetweenpayrollReport($start_date, $end_date, $filter_month, $filter_year, $data['filter_category']);
        if (!empty($result)) {
            $result = array_values(array_filter($result, function ($row) {
                $raw = $row['staff_is_active'] ?? 1;
                if (is_bool($raw)) {
                    return $raw;
                }
                $normalized = strtolower(trim((string) $raw));
                return in_array($normalized, ['1', 'true', 'yes'], true);
            }));
        }
        // Bulk-fetch allowances in 2 queries instead of 2 per row (N+1 fix)
        $payslip_ids_sum = !empty($result) ? array_column($result, 'id') : [];
        $bulk_allowances_sum = !empty($payslip_ids_sum) ? $this->payroll_model->getAllowancesBulk($payslip_ids_sum) : [];
        if (!empty($result)) {
            foreach ($result as &$row) {
                $row['working_days'] = '';
                if (!empty($row['month']) && !empty($row['year'])) {
                    $month_num = date('m', strtotime($row['month'] . ' 1'));
                    if ($month_num >= 1 && $month_num <= 12) {
                        $month_start = $row['year'] . '-' . $month_num . '-01';
                        $month_end = date('Y-m-t', strtotime($month_start));
                        try {
                            $ctx = $this->getWorkingDayContextRange($month_start, $month_end);
                            $row['working_days'] = count($ctx['working_day_dates']);
                        } catch (\Throwable $e) {
                            $row['working_days'] = '';
                        }
                    }
                }

                $pid_s = $row['id'];
                $row['earnings_breakdown']  = $bulk_allowances_sum[$pid_s]['positive'] ?? [];
                $row['deductions_breakdown'] = $bulk_allowances_sum[$pid_s]['negative'] ?? [];
                if (!empty($row['leave_deduction']) && $row['leave_deduction'] > 0) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'LOP',
                        'amount' => $row['leave_deduction'],
                        'cal_type' => 'negative'
                    );
                }
                $existingTypes = array_column($row['deductions_breakdown'], 'allowance_type');
                if (!empty($row['employee_epf']) && $row['employee_epf'] > 0 && !in_array('EPF', $existingTypes)) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'EPF',
                        'amount' => $row['employee_epf'],
                        'cal_type' => 'negative'
                    );
                    $existingTypes[] = 'EPF';
                }
                if (!empty($row['esi_deduction']) && $row['esi_deduction'] > 0 && !in_array('ESI', $existingTypes)) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'ESI',
                        'amount' => $row['esi_deduction'],
                        'cal_type' => 'negative'
                    );
                    $existingTypes[] = 'ESI';
                }
                if (!empty($row['tax']) && $row['tax'] > 0 && !in_array('TDS', $existingTypes)) {
                    $row['deductions_breakdown'][] = array(
                        'allowance_type' => 'TDS',
                        'amount' => $row['tax'],
                        'cal_type' => 'negative'
                    );
                    $existingTypes[] = 'TDS';
                }
            }
            unset($row);
        }
        $data['payrollList'] = $result;
        $this->load->view('layout/header', $data);
        $this->load->view('financereports/payroll_report_summary', $data);
        $this->load->view('layout/footer', $data);
    }

    public function payrollbankcopy()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/payrollbankcopy');

        $filter_category         = $this->input->post('filter_category') ?: [];
        $filter_category         = is_array($filter_category) ? array_map('intval', array_filter($filter_category)) : [];
        $data['filter_category'] = $filter_category;
        $data['categories']      = $this->db->select('id, name')->order_by('id')->get('staff_designation_category')->result_array();

        // Default to last month on first visit; POST overrides
        $lm = strtotime('first day of last month');
        $filter_month         = $this->input->post('filter_month') ?: date('F', $lm);
        $filter_year          = $this->input->post('filter_year')  ?: (int)date('Y', $lm);
        $data['filter_month'] = $filter_month;
        $data['filter_year']  = $filter_year;

        $data['banks'] = $this->db->distinct()
            ->select('bank_name')
            ->from('staff')
            ->order_by('bank_name', 'ASC')
            ->get()
            ->result_array();

        $filter_banks = $this->input->post('filter_banks') ?: [];
        $filter_banks = is_array($filter_banks) ? array_values(array_filter(array_map('trim', $filter_banks))) : [];
        $data['filter_banks'] = $filter_banks;

        $mn_bc = ctype_digit((string)$filter_month) ? (int)$filter_month : (int)date('n', strtotime($filter_month . ' 1 ' . $filter_year));
        $start_date = $filter_year . '-' . str_pad($mn_bc, 2, '0', STR_PAD_LEFT) . '-01';
        $end_date   = date('Y-m-t', strtotime($start_date));

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        $result = $this->payroll_model->getbetweenpayrollReport(
            $start_date,
            $end_date,
            $filter_month,
            $filter_year,
            $data['filter_category'],
            $filter_banks
        );
        if (!empty($result)) {
            $result = array_values(array_filter($result, function ($row) {
                $raw = $row['staff_is_active'] ?? 1;
                if (is_bool($raw)) {
                    return $raw;
                }

                $normalized = strtolower(trim((string) $raw));
                return in_array($normalized, ['1', 'true', 'yes'], true);
            }));
        }

        $data['payrollList'] = $result;

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/payroll_bank_copy', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * EPF report: filter by date range and optional month/year
     * shows basic payroll fields along with EPF contributions and working days.
     */
    public function epfreport()
    {
        try {
            log_message('debug','epfreport invoked');
            $this->session->set_userdata('top_menu', 'Reports');
            $this->session->set_userdata('sub_menu', 'Reports/finance');
            $this->session->set_userdata('subsub_menu', 'Reports/finance/epfreport');
            $data['searchlist']  = $this->customlib->get_searchtype();
            $data['date_type']   = $this->customlib->date_type();
            $data['date_typeid'] = '';

            $filter_category        = $this->input->post('filter_category') ?: [];
            $filter_category        = is_array($filter_category) ? array_map('intval', array_filter($filter_category)) : [];
            $data['filter_category'] = $filter_category;
            $data['categories']      = $this->db->select('id, name')->order_by('id')->get('staff_designation_category')->result_array();

            // Default to last month on first visit; POST overrides
            $lm = strtotime('first day of last month');
            $filter_month = $this->input->post('filter_month') ?: date('F', $lm);
            $filter_year  = $this->input->post('filter_year')  ?: (int)date('Y', $lm);
            $display_month = $filter_month;
            if (!empty($filter_month) && !ctype_digit($filter_month)) {
                $filter_month = date('m', strtotime($filter_month . ' 1'));
            }
            log_message('debug','epfreport filter month='.print_r($filter_month,true).' year='.print_r($filter_year,true));
            $data['filter_month'] = $display_month;
            $data['filter_year']  = $filter_year;

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
            $dates               = $this->customlib->get_betweendate($_POST['search_type']);
            $data['search_type'] = $_POST['search_type'];
            $start_date = date('Y-m-d', strtotime($dates['from_date']));
            $end_date   = date('Y-m-d', strtotime($dates['to_date']));
        } else {
            $start_date = $filter_year . '-' . str_pad((int)$filter_month, 2, '0', STR_PAD_LEFT) . '-01';
            $end_date   = date('Y-m-t', strtotime($start_date));
            $data['search_type'] = '';
        }

        $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

        log_message('debug','epfreport fetch payroll range '.$start_date.' to '.$end_date);
        $result = $this->payroll_model->getbetweenpayrollReport($start_date, $end_date, $filter_month, $filter_year, $filter_category);
        if (!empty($result)) {
            $result = array_values(array_filter($result, function ($row) {
                $raw = $row['staff_is_active'] ?? 1;
                if (is_bool($raw)) {
                    return $raw;
                }
                $normalized = strtolower(trim((string) $raw));
                return in_array($normalized, ['1', 'true', 'yes'], true);
            }));
        }
        log_message('debug','epfreport result count:'.count($result));

        // attach working days and lop information from payslip
        if (!empty($result)) {
            foreach ($result as &$row) {
                // compute working days based on row month/year
                $row['working_days'] = '';
                if (!empty($row['month']) && !empty($row['year'])) {
                    // determine numeric month; ensure valid 1-12
                    $month_num = date('m', strtotime($row['month']));
                    if ($month_num >= 1 && $month_num <= 12) {
                        $start_date = $row['year'] . '-' . $month_num . '-01';
                        $end_date = date('Y-m-t', strtotime($start_date));
                        log_message('debug','epfreport calculating working days for '.$start_date.' to '.$end_date);
                        try {
                            $ctx = $this->getWorkingDayContextRange($start_date, $end_date);
                            $row['working_days'] = count($ctx['working_day_dates']);
                        } catch (\Throwable $e) {
                            log_message('error','failed to calculate WD for '.$start_date.' '.$e->getMessage());
                            $row['working_days'] = '';
                        }
                        log_message('debug','epfreport row '.$row['id'].' month='.$row['month'].' year='.$row['year'].' wd='.$row['working_days']);
                    } else {
                        log_message('error','invalid month parsed for row: '.print_r($row, true));
                        $row['working_days'] = '';
                    }
                }
                // use actual_lop_days column from payslip if exists
                $row['lop_days'] = isset($row['actual_lop_days']) ? $row['actual_lop_days'] : 0;
                $row['lop_amount'] = isset($row['leave_deduction']) ? $row['leave_deduction'] : 0;
                // calculate adjusted LOP based on leave balance (preview - does not persist)
                $row['adjusted_lop_days'] = '';
                $row['net_lop_days'] = '';
                if (is_numeric($row['lop_days']) && $row['lop_days'] > 0 && !empty($row['staff_id'])) {
                    $month_num = date('m', strtotime($row['month']));
                    try {
                        $adj = $this->payroll_model->previewLOPWithMonthlyBalance($row['staff_id'], $row['lop_days'], $month_num, $row['year']);
                        $row['adjusted_lop_days'] = isset($adj['adjusted_lop_days']) ? $adj['adjusted_lop_days'] : 0;
                        $row['net_lop_days'] = isset($adj['net_lop_days']) ? $adj['net_lop_days'] : 0;
                    } catch (\Throwable $e) {
                        log_message('error','epfreport adjusted lop failed for staff '.$row['staff_id'].' '.$e->getMessage());
                        $row['adjusted_lop_days'] = '';
                        $row['net_lop_days'] = '';
                    }
                }
            }
            unset($row);
        }

        $data['epfList']      = $result;
        $data['working_days'] = '';
        if (!empty($filter_month) && !empty($filter_year)) {
            $data['working_days'] = $this->calculateWorkingDays($filter_month, $filter_year);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/epfreport', $data);
        $this->load->view('layout/footer', $data);
        } catch (\Throwable $e) {
            log_message('error','epfreport failed: '.$e->getMessage());
            log_message('error','epfreport trace: '.$e->getTraceAsString());
            // display generic error
            show_error('An error occurred while generating the report.');
        }
    }

    /**
     * ESI report: same filters as EPF but shows ESI contributions instead.
     */
    public function esireport()
    {
        try {
            log_message('debug','esireport invoked');
            $this->session->set_userdata('top_menu', 'Reports');
            $this->session->set_userdata('sub_menu', 'Reports/finance');
            $this->session->set_userdata('subsub_menu', 'Reports/finance/esireport');
            $data['searchlist']  = $this->customlib->get_searchtype();
            $data['date_type']   = $this->customlib->date_type();
            $data['date_typeid'] = '';

            $filter_category        = $this->input->post('filter_category') ?: [];
            $filter_category        = is_array($filter_category) ? array_map('intval', array_filter($filter_category)) : [];
            $data['filter_category'] = $filter_category;
            $data['categories']      = $this->db->select('id, name')->order_by('id')->get('staff_designation_category')->result_array();

            // Default to last month on first visit; POST overrides
            $lm = strtotime('first day of last month');
            $filter_month = $this->input->post('filter_month') ?: date('F', $lm);
            $filter_year  = $this->input->post('filter_year')  ?: (int)date('Y', $lm);
            $display_month = $filter_month;
            if (!empty($filter_month) && !ctype_digit($filter_month)) {
                $filter_month = date('m', strtotime($filter_month . ' 1'));
            }
            log_message('debug','esireport filter month='.print_r($filter_month,true).' year='.print_r($filter_year,true));
            $data['filter_month'] = $display_month;
            $data['filter_year']  = $filter_year;

            if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
                $dates               = $this->customlib->get_betweendate($_POST['search_type']);
                $data['search_type'] = $_POST['search_type'];
                $start_date = date('Y-m-d', strtotime($dates['from_date']));
                $end_date   = date('Y-m-d', strtotime($dates['to_date']));
            } else {
                $start_date = $filter_year . '-' . str_pad((int)$filter_month, 2, '0', STR_PAD_LEFT) . '-01';
                $end_date   = date('Y-m-t', strtotime($start_date));
                $data['search_type'] = '';
            }

            $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));

            log_message('debug','esireport fetch payroll range '.$start_date.' to '.$end_date);
            $result = $this->payroll_model->getbetweenpayrollReport($start_date, $end_date, $filter_month, $filter_year, $filter_category);
            if (!empty($result)) {
                $result = array_values(array_filter($result, function ($row) {
                    $raw = $row['staff_is_active'] ?? 1;
                    if (is_bool($raw)) {
                        return $raw;
                    }
                    $normalized = strtolower(trim((string) $raw));
                    return in_array($normalized, ['1', 'true', 'yes'], true);
                }));
            }
            log_message('debug','esireport result count:'.count($result));

            // reuse working days/LOP logic from EPF
            if (!empty($result)) {
                foreach ($result as &$row) {
                    $row['working_days'] = '';
                    if (!empty($row['month']) && !empty($row['year'])) {
                        $month_num = date('m', strtotime($row['month']));
                        if ($month_num >= 1 && $month_num <= 12) {
                            $start_date = $row['year'] . '-' . $month_num . '-01';
                            $end_date = date('Y-m-t', strtotime($start_date));
                            log_message('debug','esireport calculating working days for '.$start_date.' to '.$end_date);
                            try {
                                $ctx = $this->getWorkingDayContextRange($start_date, $end_date);
                                $row['working_days'] = count($ctx['working_day_dates']);
                            } catch (\Throwable $e) {
                                log_message('error','failed to calculate WD for '.$start_date.' '.$e->getMessage());
                                $row['working_days'] = '';
                            }
                            log_message('debug','esireport row '.$row['id'].' month='.$row['month'].' year='.$row['year'].' wd='.$row['working_days']);
                        } else {
                            log_message('error','invalid month parsed for row: '.print_r($row, true));
                            $row['working_days'] = '';
                        }
                    }
                    $row['lop_days'] = isset($row['actual_lop_days']) ? $row['actual_lop_days'] : 0;
                    $row['lop_amount'] = isset($row['leave_deduction']) ? $row['leave_deduction'] : 0;
                    $row['adjusted_lop_days'] = '';
                    $row['net_lop_days'] = '';
                    if (is_numeric($row['lop_days']) && $row['lop_days'] > 0 && !empty($row['staff_id'])) {
                        $month_num = date('m', strtotime($row['month']));
                        try {
                            $adj = $this->payroll_model->previewLOPWithMonthlyBalance($row['staff_id'], $row['lop_days'], $month_num, $row['year']);
                            $row['adjusted_lop_days'] = isset($adj['adjusted_lop_days']) ? $adj['adjusted_lop_days'] : 0;
                            $row['net_lop_days'] = isset($adj['net_lop_days']) ? $adj['net_lop_days'] : 0;
                        } catch (\Throwable $e) {
                            log_message('error','esireport adjusted lop failed for staff '.$row['staff_id'].' '.$e->getMessage());
                            $row['adjusted_lop_days'] = '';
                            $row['net_lop_days'] = '';
                        }
                    }
                }
                unset($row);
            }

            $data['esilist']      = $result;
            $data['working_days'] = '';
            if (!empty($filter_month) && !empty($filter_year)) {
                $data['working_days'] = $this->calculateWorkingDays($filter_month, $filter_year);
            }

            $this->load->view('layout/header', $data);
            $this->load->view('financereports/esireport', $data);
            $this->load->view('layout/footer', $data);
        } catch (\Throwable $e) {
            log_message('error','esireport failed: '.$e->getMessage());
            log_message('error','esireport trace: '.$e->getTraceAsString());
            show_error('An error occurred while generating the report.');
        }
    }

    /**
     * Salary abstract report
     */
    public function salaryabstract()
    {
        try {
            log_message('debug','salaryabstract invoked');
            $this->session->set_userdata('top_menu', 'Reports');
            $this->session->set_userdata('sub_menu', 'Reports/finance');
            $this->session->set_userdata('subsub_menu', 'Reports/finance/salaryabstract');
            $data['searchlist']  = $this->customlib->get_searchtype();
            $data['date_type']   = $this->customlib->date_type();
            $data['date_typeid'] = '';

            $filter_category        = $this->input->post('filter_category') ?: [];
            $filter_category        = is_array($filter_category) ? array_map('intval', array_filter($filter_category)) : [];
            $data['filter_category'] = $filter_category;
            $data['categories']      = $this->db->select('id, name')->order_by('id')->get('staff_designation_category')->result_array();

            $filter_month = $this->input->post('filter_month');
            $filter_year  = $this->input->post('filter_year');
            // preserve original for label
            $display_month = $filter_month;
            // convert month name to number if necessary
            if (!empty($filter_month) && !ctype_digit($filter_month)) {
                $filter_month = date('m', strtotime($filter_month . ' 1'));
            }
            log_message('debug','salaryabstract filter month='.print_r($filter_month,true).' year='.print_r($filter_year,true));
            $data['filter_month'] = $display_month;
            $data['filter_year']  = $filter_year;

            // determine date range either from search_type or explicit month/year
            if (!empty($filter_month) && !empty($filter_year)) {
                // user wants a specific month; label accordingly
                $data['label'] = "Salary Abstract for the month of $filter_month -$filter_year";
                $data['search_type'] = '';
                // use broad payment_date range so records are picked up even if paid later
                $start_date = '1900-01-01';
                $end_date = '2999-12-31';
            } else {
                if (isset($_POST['search_type']) && $_POST['search_type'] != '') {
                    $dates               = $this->customlib->get_betweendate($_POST['search_type']);
                    $data['search_type'] = $_POST['search_type'];
                } else {
                    $dates               = $this->customlib->get_betweendate('this_year');
                    $data['search_type'] = '';
                }
                $start_date = date('Y-m-d', strtotime($dates['from_date']));
                $end_date   = date('Y-m-d', strtotime($dates['to_date']));

                $data['label'] = date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) . " " . $this->lang->line('to') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($end_date));
            }

            log_message('debug','salaryabstract fetch payroll range '.$start_date.' to '.$end_date);
            // make range available to view for troubleshooting
            $data['start_date'] = $start_date;
            $data['end_date'] = $end_date;
            $result = $this->payroll_model->getbetweenpayrollReport($start_date, $end_date, $filter_month, $filter_year, $filter_category);
            log_message('debug','salaryabstract result count:'.count($result));

            // Filter out inactive staff (same as payroll summary)
            if (!empty($result)) {
                $result = array_values(array_filter($result, function ($row) {
                    $raw = $row['staff_is_active'] ?? 1;
                    if (is_bool($raw)) {
                        return $raw;
                    }
                    $normalized = strtolower(trim((string) $raw));
                    return in_array($normalized, ['1', 'true', 'yes'], true);
                }));
            }

            // add working days for each row (optional)
            if (!empty($result)) {
                foreach ($result as &$row) {
                    $row['working_days'] = '';
                    if (!empty($row['month']) && !empty($row['year'])) {
                        $mnum = date('m', strtotime($row['month']));
                        if ($mnum>=1 && $mnum<=12) {
                            $sd = $row['year'].'-'.sprintf('%02d',$mnum).'-01';
                            $ed = date('Y-m-t',strtotime($sd));
                            try {
                                $ctx = $this->getWorkingDayContextRange($sd,$ed);
                                $row['working_days'] = count($ctx['working_day_dates']);
                            } catch (\Throwable $e) { }
                        }
                    }
                }
                unset($row);
            }

            $data['abstractList'] = $result;
            $data['working_days'] = '';
            if (!empty($filter_month) && !empty($filter_year)) {
                $data['working_days'] = $this->calculateWorkingDays($filter_month, $filter_year);
            }

            $this->load->view('layout/header',$data);
            $this->load->view('financereports/salaryabstract',$data);
            $this->load->view('layout/footer',$data);
        } catch (\Throwable $e) {
            log_message('error','salaryabstract failed: '.$e->getMessage());
            log_message('error','salaryabstract trace: '.$e->getTraceAsString());
            show_error('An error occurred while generating the report.');
        }
    }
    /**
     * computes number of working days in a month accounting for weekends/holidays
     * copied from Specialattendance->get_working_days but returns integer
     */
    private function calculateWorkingDays($month, $year)
    {
        $validMonths = array(
            'January','February','March','April','May','June',
            'July','August','September','October','November','December'
        );
        if (empty($month) || empty($year) || !in_array($month, $validMonths, true) || !ctype_digit((string)$year)) {
            return 0;
        }

        $this->load->model('setting_model');
        $settings = $this->setting_model->getSetting();
        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayHoliday = isset($settings->isSecondSaturdayHoliday) ? (int)$settings->isSecondSaturdayHoliday : 0;

        $this->db->select('annual_calendar.from_date, annual_calendar.to_date, holiday_type.type');
        $this->db->from('annual_calendar');
        $this->db->join('holiday_type', 'holiday_type.id = annual_calendar.holiday_type', 'left');
        $this->db->where('is_active', 1);
        $this->db->where('(MONTH(from_date) = ' . date('n', strtotime("$month 1, $year")) . ' AND YEAR(from_date) = ' . $year . ') 
                         OR (MONTH(to_date) = ' . date('n', strtotime("$month 1, $year")) . ' AND YEAR(to_date) = ' . $year . ')
                         OR (from_date <= "' . $year . '-' . str_pad(date('n', strtotime("$month 1, $year")), 2, '0', STR_PAD_LEFT) . '-' . date('t', strtotime("$month 1, $year")) . '" 
                             AND to_date >= "' . $year . '-' . str_pad(date('n', strtotime("$month 1, $year")), 2, '0', STR_PAD_LEFT) . '-01")');
        $holidays = $this->db->get()->result_array();

        $firstDay = DateTime::createFromFormat('F j, Y', $month . ' 1, ' . $year);
        if ($firstDay === false) {
            return 0;
        }
        $monthIndex = (int)$firstDay->format('n') - 1;
        $daysInMonth = (int)$firstDay->format('t');
        $workingDays = 0;
        $holidayDates = [];
        $compensationDates = [];
        foreach ($holidays as $holiday) {
            $from = new DateTime(date('Y-m-d', strtotime($holiday['from_date'])));
            $to = new DateTime(date('Y-m-d', strtotime($holiday['to_date'])));
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($from, $interval, $to->modify('+1 day'));
            foreach ($period as $date) {
                if ($date->format('n') == ($monthIndex + 1) && $date->format('Y') == $year) {
                    $type_label = strtolower(trim($holiday['type'] ?? ''));
                    if ($type_label === 'compensatory') {
                        $compensationDates[] = $date->format('Y-m-d');
                    } else {
                        $holidayDates[] = $date->format('Y-m-d');
                    }
                }
            }
        }

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = sprintf('%02d', $i);
            $currDate = $year . '-' . str_pad(($monthIndex + 1), 2, '0', STR_PAD_LEFT) . '-' . $day;
            $dt = new DateTime($currDate);
            $weekday = intval($dt->format('w')); // 0 for Sunday
            if (in_array($currDate, $holidayDates, true)) {
                continue;
            }
            if (in_array($weekday, $weekendDays, true)) {
                // optionally second saturday
                if ($isSecondSaturdayHoliday && $weekday === 6) {
                    $weekCount = floor(($i - 1) / 7) + 1;
                    if ($weekCount === 2) {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            $workingDays++;
        }

        return $workingDays;
    }

    // Helper copied from Payroll controller to calculate working/holiday/weekend context over a date range.
    private function getWorkingDayContextRange($start_date, $end_date, $settings = null, $holidays = null)
    {
        $this->load->model("holiday_model");
        $this->load->model("setting_model");

        if ($settings === null) {
            $settings = $this->setting_model->getSetting();
        }
        if ($holidays === null) {
            $holidays = $this->holiday_model->get();
        }

        $weekendDaysStr = isset($settings->weekend_days) && !empty($settings->weekend_days) ? $settings->weekend_days : '0';
        $weekendDays = array_map('intval', explode(',', $weekendDaysStr));
        $isSecondSaturdayWeekend = isset($settings->isSecondSaturdayHoliday) ? (int) $settings->isSecondSaturdayHoliday : 0;

        $range_start = new DateTime($start_date);
        $range_end = new DateTime($end_date);

        $official_holiday_dates = [];
        $compensation_dates = [];
        foreach ($holidays as $holiday_value) {
            $type_label = strtolower(trim($holiday_value['type'] ?? ''));
            $from_date = new DateTime($holiday_value['from_date']);
            $to_date = new DateTime($holiday_value['to_date']);
            if ($to_date < $range_start || $from_date > $range_end) {
                continue;
            }
            $current = clone $from_date;
            while ($current <= $to_date) {
                if ($current >= $range_start && $current <= $range_end) {
                    if ($type_label === 'compensation') {
                        $compensation_dates[] = $current->format('Y-m-d');
                    } else {
                        $official_holiday_dates[] = $current->format('Y-m-d');
                    }
                }
                $current->modify('+1 day');
            }
        }
        $official_holiday_dates = array_values(array_unique($official_holiday_dates));
        $compensation_dates = array_values(array_unique($compensation_dates));

        $weekend_day_dates = [];
        $working_day_dates = [];
        $holiday_dates = [];

        $current = new DateTime($start_date);
        while ($current <= $range_end) {
            $dateStr = $current->format('Y-m-d');
            $dayOfWeek = (int) $current->format('w');
            $is_second_saturday = false;
            if ($isSecondSaturdayWeekend && $dayOfWeek === 6) {
                $is_second_saturday = $this->isSecondSaturday($current);
            }

            if (in_array($dateStr, $compensation_dates, true)) {
                $working_day_dates[] = $dateStr;
                $current->modify('+1 day');
                continue;
            }

            $is_weekend = in_array($dayOfWeek, $weekendDays, true) || $is_second_saturday;
            $is_official_holiday = in_array($dateStr, $official_holiday_dates, true);

            if ($is_weekend) {
                $weekend_day_dates[] = $dateStr;
            }
            if ($is_official_holiday && !$is_weekend) {
                $holiday_dates[] = $dateStr;
            }
            if (!$is_weekend && !$is_official_holiday) {
                $working_day_dates[] = $dateStr;
            }

            $current->modify('+1 day');
        }

        return [
            'working_day_dates' => array_values(array_unique($working_day_dates)),
            'weekend_day_dates' => array_values(array_unique($weekend_day_dates)),
            'holiday_dates' => array_values(array_unique($holiday_dates)),
        ];
    }

    private function isSecondSaturday(DateTime $dateObj)
    {
        $month_start = new DateTime($dateObj->format('Y-m-01'));
        $count = 0;
        while ($month_start <= $dateObj) {
            if ((int) $month_start->format('w') === 6) {
                $count++;
            }
            if ($month_start->format('Y-m-d') === $dateObj->format('Y-m-d')) {
                break;
            }
            $month_start->modify('+1 day');
        }
        return $count === 2;
    }

    /**
     * copy of Payroll->monthAttendance simplified for finance reports
     */
    private function monthAttendance($st_month, $no_of_months, $emp)
    {
        $this->load->model("holiday_model");
        $holidays = $this->holiday_model->get();
        $this->load->model("setting_model");
        $settings = $this->setting_model->getSetting();
        $this->load->model("staffattendancemodel");
        $this->staff_attendance  = $this->config->item('staffattendance');

        $record = array();
        for ($i = 1; $i <= $no_of_months; $i++) {
            $r     = array();
            $month = date('m', strtotime($st_month . " -$i month"));
            $year  = date('Y', strtotime($st_month . " -$i month"));

            $period = $this->getPayrollPeriodRange($month, $year);
            $context = $this->getWorkingDayContextRange($period['start_date'], $period['end_date'], $settings, $holidays);
            $weekend_day_dates = $context['weekend_day_dates'];
            $holidays_for_H_column = $context['holiday_dates'];
            $working_day_dates = $context['working_day_dates'];

            $attendance_types_from_db = $this->staffattendancemodel->getStaffAttendanceType();
            $att_key_to_id_map = [];
            foreach ($attendance_types_from_db as $type_row) {
                $config_key = str_replace(" ", "_", strtolower($type_row['type']));
                $att_key_to_id_map[$config_key] = $type_row['id'];
            }

            foreach ($this->staff_attendance as $att_key => $att_value_from_config) {
                $attendance_type_id_for_query = $att_key_to_id_map[$att_key] ?? null;

                if ($att_key == 'holiday') {
                    $r[$att_key] = count($holidays_for_H_column); // Now this only counts "other leaves"
                    $r['sunday'] = count($weekend_day_dates); // Weekend days count
                    continue;
                }

                if ($attendance_type_id_for_query !== null) {
                    $s = $this->payroll_model->count_attendance_range($period['start_date'], $period['end_date'], $emp, $attendance_type_id_for_query);
                    $r[$att_key] = $s;
                } else {
                    $r[$att_key] = 0;
                }
            }

            $r['working_days']   = count($working_day_dates);
            $record[$month . '-' . $year] = $r;
        }
        return $record;
    }

    /**
     * replicate getPayrollLopSummary logic for working/LOP days
     */
    private function calculateLopSummary($monthAttendance, $monthLeaves, $month, $year, $staff_id)
    {
        $month_num = date('m', strtotime($year . '-' . $month . '-01'));
        $month_key = '01-' . $month_num . '-' . $year;
        $attendance = $monthAttendance[$month_key] ?? reset($monthAttendance) ?? [];

        $period = $this->getPayrollPeriodRange($month, $year);
        $days_in_period = (int) ($attendance['days_in_period'] ?? $this->getDaysInRange($period['start_date'], $period['end_date']));
        $working_days = (int) ($attendance['working_days'] ?? 0);
        if ($working_days === 0) {
            $context = $this->getWorkingDayContextRange($period['start_date'], $period['end_date']);
            $working_days = count($context['working_day_dates']);
        }

        $holidays = (int) ($attendance['holiday'] ?? 0);
        $sundays = (int) ($attendance['sunday'] ?? 0);

        $present = (int) ($attendance['present'] ?? 0);
        $late = (int) ($attendance['late'] ?? 0);
        $absent_working = $this->getAbsentWorkingDayCount($month_num, $year, $staff_id);
        $half_day = (int) ($attendance['half_day'] ?? 0);
        $first_half_absent = (int) ($attendance['first_half_absent'] ?? 0);
        $second_half_absent = (int) ($attendance['second_half_absent'] ?? 0);
        $first_half_permission = (int) ($attendance['first_half_permission'] ?? 0);
        $second_half_permission = (int) ($attendance['second_half_permission'] ?? 0);

        $approved_leave = (int) ($monthLeaves[$month_num] ?? 0);

        $lop_rules = $this->config->item('lop_rules');
        $half_day_weight = isset($lop_rules['half_day_weight']) ? (float) $lop_rules['half_day_weight'] : 0.5;

        $permission_count = $first_half_permission + $second_half_permission;
        $max_late_allowed = isset($this->sch_setting_detail->max_late_allowed) ? (int) $this->sch_setting_detail->max_late_allowed : 0;
        $max_permission_allowed = isset($this->sch_setting_detail->max_permission_allowed) ? (int) $this->sch_setting_detail->max_permission_allowed : 0;

        $late_half_days = $late > $max_late_allowed ? ($late - $max_late_allowed) : 0;
        $permission_half_days = $permission_count > $max_permission_allowed ? ($permission_count - $max_permission_allowed) : 0;

        $paid_leave_absent = $this->getPaidLeaveAbsentCountRange($period['start_date'], $period['end_date'], $staff_id);

        $late_permission_penalty = ($late_half_days + $permission_half_days) * $half_day_weight;

        $total_present = max(0, $present + ($half_day * $half_day_weight) - $late_permission_penalty + $paid_leave_absent);
        $total_absent = $absent_working + ($half_day * $half_day_weight) + $late_permission_penalty;

        $lop_days = $total_absent
            + (($first_half_absent + $second_half_absent) * $half_day_weight);

        return ['working_days'=>$working_days,'lop_days'=>$lop_days];
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