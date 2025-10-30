<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentfee extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('smsgateway');
        $this->load->library('mailsmsconf');
        $this->load->library('customlib');
        $this->load->library('media_storage');
        $this->load->model("module_model");
                $this->load->model("studentAppliedDiscount_model");
                $this->load->model("transportfee_model");
                $this->load->model("feetype_model");

                $this->search_type        = $this->config->item('search_type');
                $this->sch_setting_detail = $this->setting_model->getSetting();
                $this->current_session = $this->setting_model->getCurrentSession();
        		
        		$this->thermal_print_module 	= 0;
        		$this->thermal_print_enable  	= 0;
        			
        		if ($this->module_lib->hasModule('thermal_print') && $this->module_lib->hasActive('thermal_print')) {
        			$this->load->model("thermal_print_model");
        			$this->thermal_print_result 		= $this->thermal_print_model->get();			
        			$this->thermal_print_module		= 1;			
        			$this->thermal_print_enable  	= $this->thermal_print_result['is_print'];				 
        		}		
            }
        
        
            public function bulk_upload_fees()
            {
                if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
                    access_denied();
                }
        
                $this->session->set_userdata('top_menu', $this->lang->line('fees_collection'));
                $this->session->set_userdata('sub_menu', 'studentfee/bulk_upload_fees');
                $data['title'] = $this->lang->line('bulk_upload_fees');
                $data['feetype_list'] = $this->feetype_model->get();
        
                // Simply load the view, POST handling is moved to another method
                $this->load->view('layout/header', $data);
                $this->load->view('studentfee/bulk_upload_fees', $data);
                $this->load->view('layout/footer', $data);
            }
        
            public function do_bulk_upload_by_feetype()
            {
                $this->output->set_content_type('application/json');

                if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
                    echo json_encode(['status' => 'fail', 'error' => ['message' => 'Access Denied']]);
                    return;
                }
        
                $this->form_validation->set_rules('feetype_id', $this->lang->line('fee_type'), 'required');
                $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');
        
                if ($this->form_validation->run() == false) {
                    $errors = $this->form_validation->error_array();
                    echo json_encode(['status' => 'fail', 'error' => $errors]);
                    return;
                } else {
                    $feetype_id = $this->input->post('feetype_id');
                    $file_path = $_FILES['file']['tmp_name'];
                    $this->load->library('csvreader');
                    $result = $this->csvreader->parse_file($file_path);
        
                    if (!empty($result)) {
                        $error_messages = [];
                        $total_records = 0;
                        $successful_records = 0;
                        $failed_records = 0;

                        foreach ($result as $row_num => $row) {
                            $total_records++;
                            $admission_no = $row['admission_no'] ?? '';
                            $total_amount_paid = $row['total_amount_paid'] ?? '';
                            $old_bill_number = trim($row['old_bill_number'] ?? '');
                            $old_bill_date = $row['old_bill_date'] ?? '';
                            $payment_mode = $row['payment_mode'] ?? '';
                            $description = $row['description'] ?? '';

                            if (empty($admission_no) || empty($total_amount_paid) || empty($old_bill_number) || empty($old_bill_date) || empty($payment_mode)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Missing required fields.";
                                $failed_records++;
                                continue;
                            }

                            $student = $this->student_model->findByAdmission($admission_no);
                            if (!$student) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Student not found.";
                                $failed_records++;
                                continue;
                            }

                            $student_session_id = $student->student_session_id;

                            $fee_master = $this->studentfeemaster_model->getFeeByFeeType($student_session_id, $feetype_id);
                            if (!$fee_master) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Fee master not found for the given fee type.";
                                $failed_records++;
                                continue;
                            }

                            $fee_balance_obj = json_decode($this->getStuFeetypeBalance($fee_master->fee_groups_feetype_id, $fee_master->id));
                            $fee_balance = $fee_balance_obj->balance;

                            $amount_to_pay = $total_amount_paid;
                            $advance_amount = 0;

                            if ($total_amount_paid > $fee_balance) {
                                $amount_to_pay = $fee_balance;
                                $advance_amount = $total_amount_paid - $fee_balance;
                            }

                            if ($amount_to_pay > 0) {
                                $json_array = [
                                    'amount'          => $amount_to_pay,
                                    'amount_discount' => 0,
                                    'amount_fine'     => 0,
                                    'date'            => date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)),
                                    'description'     => $description,
                                    'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                    'payment_mode'    => $payment_mode,
                                    'received_by'     => $this->customlib->getStaffID(),
                                ];

                                $data_to_insert = [
                                    'fee_category'           => 'fees',
                                    'student_fees_master_id' => $fee_master->id,
                                    'fee_groups_feetype_id'  => $fee_master->fee_groups_feetype_id,
                                    'amount_detail'          => $json_array,
                                ];

                                $inserted_id = $this->studentfeemaster_model->fee_deposit($data_to_insert, null, [], date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)));
                            } else {
                                $inserted_id = true; // Nothing to pay, so consider it successful
                            }

                            if ($inserted_id) {
                                if ($advance_amount > 0) {
                                    $this->student_model->add_advance_payment($student->id, $advance_amount);
                                }
                                $successful_records++;
                            } else {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Failed to deposit fee.";
                                $failed_records++;
                            }
                        }

                        $summary = [
                            'total_records' => $total_records,
                            'successful_records' => $successful_records,
                            'failed_records' => $failed_records
                        ];

                        echo json_encode(['status' => 'success', 'message' => 'File processed.', 'summary' => $summary, 'error_messages' => $error_messages]);

                    } else {
                        echo json_encode(['status' => 'fail', 'error' => ['message' => $this->lang->line('error_processing_file')]]);
                    }
                }
            }

        
            public function exportfeesformat()
            {
                $this->load->helper('download');
                $filepath = "./backend/import/sample_fees_bulk_upload.csv";
                $data     = file_get_contents($filepath);
                $name     = 'sample_fees_bulk_upload.csv';

                force_download($name, $data);
            }

            public function handle_csv_upload()
            {
                log_message('error', 'handle_csv_upload called.');
                log_message('error', 'FILES array: ' . print_r($_FILES, true));

                if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                    $allowedMimeTypes = array(
                        'text/csv',
                        'application/vnd.ms-excel',
                        'application/csv',
                        'application/x-csv',
                        'text/x-csv',
                        'text/plain',
                        'text/x-comma-separated-values',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // For .xlsx
                    );
                    $mime = get_mime_by_extension($_FILES['file']['name']);
                    log_message('error', 'Detected MIME type: ' . $mime);

                    if (!in_array($mime, $allowedMimeTypes)) {
                        $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                        log_message('error', 'File type not allowed.');
                        return false;
                    }
                    return true;
                } else {
                    $this->form_validation->set_message('handle_csv_upload', $this->lang->line('the_file_field_is_required'));
                    log_message('error', 'File field is required.');
                    return false;
                }
            }
        
            public function index()
            {
                if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
                    access_denied();
                }
        
                $this->session->set_userdata('top_menu', $this->lang->line('fees_collection'));
                $this->session->set_userdata('sub_menu', 'studentfee/index');
                $data['sch_setting'] = $this->sch_setting_detail;
                $data['title']       = 'student fees';
                $class               = $this->class_model->get();
                $data['classlist']   = $class;
                $this->load->view('layout/header', $data);
                $this->load->view('studentfee/studentfeeSearch', $data);
                $this->load->view('layout/footer', $data);
            }

    public function pdf()
    {
        $this->load->helper('pdf_helper');
    }

    public function search()
    {
        $search_type = $this->input->post('search_type');
        if ($search_type == "class_search") {
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'required|trim|xss_clean');
        } elseif ($search_type == "keyword_search") {
            $this->form_validation->set_rules('search_text', $this->lang->line('keyword'), 'required|trim|xss_clean');
            $data = array('search_text' => 'dummy');
            $this->form_validation->set_data($data);
        }
        if ($this->form_validation->run() == false) {
            $error = array();
            if ($search_type == "class_search") {
                $error['class_id'] = form_error('class_id');
            } elseif ($search_type == "keyword_search") {
                $error['search_text'] = form_error('search_text');
            }

            $array = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        }
    }

    public function ajaxSearch()
    {
        log_message('debug', 'ajaxSearch called.');
        $class       = $this->input->post('class_id');
        $section     = $this->input->post('section_id');
        $search_text = $this->input->post('search_text');
        $search_type = $this->input->post('search_type');
        log_message('debug', 'ajaxSearch - class_id: ' . print_r($class, true) . ', section_id: ' . $section . ', search_type: ' . $search_type);
        if ($search_type == "class_search") {
            log_message('debug', 'ajaxSearch - Calling getDatatableByClassSection with class: ' . print_r($class, true) . ', section: ' . $section);
            $students = $this->student_model->getDatatableByClassSection($class, $section);
            log_message('debug', 'ajaxSearch - getDatatableByClassSection returned: ' . print_r($students, true));
        } elseif ($search_type == "keyword_search") {
            log_message('debug', 'ajaxSearch - Calling getDatatableByFullTextSearch with search_text: ' . $search_text);
            $students = $this->student_model->getDatatableByFullTextSearch($search_text);
            log_message('debug', 'ajaxSearch - getDatatableByFullTextSearch returned: ' . print_r($students, true));
        }
        $sch_setting = $this->sch_setting_detail;
        $students    = json_decode($students);
        $dt_data     = array();
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {
                $row         = array();
                $row[]       = $student->class;
                $row[]       = $student->section;
                $row[]       = $student->admission_no;
                $row[]       = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";
                $sch_setting = $this->sch_setting_detail;
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }
                $row[] = $this->customlib->dateformat($student->dob);
                $row[] = $student->mobileno;
                $row[] = "<a href=" . site_url('studentfee/addfee/' . $student->student_session_id) . "  class='btn btn-info btn-xs'>" . $this->lang->line('collect_fees') . "</a>";

                $dt_data[] = $row;
            }
        }
        $json_data = array(
            "draw"            => intval($students->draw),
            "recordsTotal"    => intval($students->recordsTotal),
            "recordsFiltered" => intval($students->recordsFiltered),
            "data"            => $dt_data,
        );
        log_message('debug', 'ajaxSearch - Final JSON data: ' . print_r($json_data, true));
        echo json_encode($json_data);
    }

    public function feesearch()
    {
        if (!$this->rbac->hasPrivilege('search_due_fees', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'studentfee/feesearch');
        $data['title']       = $this->lang->line('student_fees');
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['sch_setting'] = $this->sch_setting_detail;
        $feesessiongroup     = $this->feesessiongroup_model->getFeesByGroup();
        $module = $this->module_model->getPermissionByModulename('transport');

        $currentsessiontransportfee = $this->transportfee_model->getSessionFees($this->current_session);
        if (!empty($currentsessiontransportfee)) {
            $transportfesstype = [];
            if ($module['is_active']) {
                $month_list = $this->customlib->getMonthDropdown($this->sch_setting_detail->start_month);
                foreach ($month_list as $key => $value) {

                    $transportfesstype[] = $this->transportfee_model->transportfesstype($this->current_session, $key);
                }

                if (!empty($transportfesstype)) {

                    foreach ($transportfesstype as $trs_key => $trs_value) {
                        $transportfesstype[$trs_key]->type = $this->lang->line(strtolower($trs_value->type));
                        $transportfesstype[$trs_key]->code = $this->lang->line(strtolower($trs_value->code));
                    }
                }

                $feesessiongroup[count($feesessiongroup)] = (object)array('id' => 'Transport', 'group_name' => 'Transport Fees', 'is_system' => 0, 'feetypes' => $transportfesstype);
            }
        }

        $data['feesessiongrouplist'] = $feesessiongroup;
        $data['fees_group']          = "";
        if (isset($_POST['feegroup_id']) && $_POST['feegroup_id'] != '') {
            $data['fees_group'] = $_POST['feegroup_id'];
        }

        if (isset($_POST['select_all']) && $_POST['select_all'] != '') {
            $data['select_all'] = $_POST['select_all'];
        }

        if ($this->input->post('no_fees_assigned') == 1) {
            $data['student_list'] = $this->studentfee_model->getStudentsWithoutFees();
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentSearchFee', $data);
            $this->load->view('layout/footer', $data);
            return;
        }
        $this->form_validation->set_rules('feegroup[]', $this->lang->line('fee_group'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentSearchFee', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function reportbyclass()
    {
        $data['title']     = 'student fees';
        $data['title']     = 'student fees';
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/reportByClass', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $student_fees_array      = array();
            $class_id                = $this->input->post('class_id');
            $section_id              = $this->input->post('section_id');
            $student_result          = $this->student_model->searchByClassSection($class_id, $section_id);
            $data['student_due_fee'] = array();
            if (!empty($student_result)) {
                foreach ($student_result as $key => $student) {
                    $student_array                      = array();
                    $student_array['student_detail']    = $student;
                    $student_session_id                 = $student['student_session_id'];
                    $student_id                         = $student['id'];
                    $student_due_fee                    = $this->studentfee_model->getDueFeeBystudentSection($class_id, $section_id, $student_session_id);
                    $student_array['fee_detail']        = $student_due_fee;
                    $student_fees_array[$student['id']] = $student_array;
                }
            }
            $data['class_id']           = $class_id;
            $data['section_id']         = $section_id;
            $data['student_fees_array'] = $student_fees_array;
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/reportByClass', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }
        $data['title']      = 'studentfee List';
        $studentfee         = $this->studentfee_model->get($id);
        $data['studentfee'] = $studentfee;
        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/studentfeeShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function deleteFee()
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_delete')) {
            access_denied();
        }
        $invoice_id  = $this->input->post('main_invoice');
        $sub_invoice = $this->input->post('sub_invoice');
        if (!empty($invoice_id)) {
            $this->studentfee_model->remove($invoice_id, $sub_invoice);
        }
        $array = array('status' => 'success', 'result' => 'success');
        echo json_encode($array);
    }

    public function deleteStudentDiscount()
    {
        $discount_id = $this->input->post('discount_id');
        if (!empty($discount_id)) {
            $data = array('id' => $discount_id, 'status' => 'assigned', 'payment_id' => "");
            $this->feediscount_model->updateStudentDiscount($data);
        }
        $array = array('status' => 'success', 'result' => 'success');
        echo json_encode($array);
    }

    public function getcollectfee()
    {
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $record              = $this->input->post('data');
        $record_array        = json_decode($record);

        $fees_array = array();
        foreach ($record_array as $key => $value) {
            $fee_groups_feetype_id = $value->fee_groups_feetype_id;
            $fee_master_id         = $value->fee_master_id;
            $fee_session_group_id  = $value->fee_session_group_id;
            $fee_category          = $value->fee_category;
            $trans_fee_id          = $value->trans_fee_id;

            if ($fee_category == "transport") {
                $feeList               = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);
                $feeList->fee_category = $fee_category;
            } else {
                $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
                $feeList->fee_category = $fee_category;
            }

            $fees_array[] = $feeList;
        }

        $data['feearray'] = $fees_array;
        $result           = array(
            'view' => $this->load->view('studentfee/getcollectfee', $data, true),
        );

        $this->output->set_output(json_encode($result));
    }

    public function addfee($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }

        $data['sch_setting']   = $this->sch_setting_detail;
        $data['title']         = 'Student Detail';
        $student               = $this->student_model->getByStudentSession($id);
        $route_pickup_point_id = $student['route_pickup_point_id'];
        $student_session_id    = $student['student_session_id'];
        $transport_fees = [];

        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $transport_fees        = $this->studentfeemaster_model->getStudentTransportFeesByStudentSessionId($student_session_id, $route_pickup_point_id);
        }
       
        $data['student']       = $student;
        $student_due_fee       = $this->studentfeemaster_model->getStudentFees($id);
        $student_discount_fee  = $this->feediscount_model->getStudentFeesDiscount($id);

        $data['transport_fees']         = $transport_fees;
        $data['student_discount_fee']   = $student_discount_fee;
        $data['student_due_fee']        = $student_due_fee;
        $category                       = $this->category_model->get();
        $data['categorylist']           = $category;
        $class_section                  = $this->student_model->getClassSection($student["class_id"]);
        $data["class_section"]          = $class_section;
        $session                        = $this->setting_model->getCurrentSession();
        $studentlistbysection           = $this->student_model->getStudentClassSection($student["class_id"], $session);
        $data["studentlistbysection"]   = $studentlistbysection;
        $student_processing_fee         = $this->studentfeemaster_model->getStudentProcessingFees($id);
        $data['student_processing_fee'] = false;

        foreach ($student_processing_fee as $key => $processing_value) {
            if (!empty($processing_value->fees)) {
                $data['student_processing_fee'] = true;
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/studentAddfee', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getProcessingfees($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
            access_denied();
        }

        $student               = $this->student_model->getByStudentSession($id);
        $route_pickup_point_id = $student['route_pickup_point_id'];
        $student_session_id    = $student['student_session_id'];
	
		if($route_pickup_point_id){
			$transport_fees       = $this->studentfeemaster_model->getProcessingTransportFees($student_session_id, $route_pickup_point_id);
		}else{
			$transport_fees        = '';
		}
        $data['student']       = $student;
        $student_due_fee       = $this->studentfeemaster_model->getStudentProcessingFees($id);
        $data['transport_fees']  = $transport_fees;
        $data['student_due_fee'] = $student_due_fee;
     
        $result = array(
            'view' => $this->load->view('user/student/getProcessingfees', $data, true),
        );
        $this->output->set_output(json_encode($result));
    }

    public function deleteTransportFee()
    {
        $id = $this->input->post('feeid');
        $this->studenttransportfee_model->remove($id);
        $array = array('status' => 'success', 'result' => 'success');
        echo json_encode($array);
    }
    public function delete($id)
    {
        $data['title'] = 'studentfee List';
        $this->studentfee_model->remove($id);
        redirect('studentfee/index');
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }
        $data['title'] = 'Add studentfee';
        $this->form_validation->set_rules('category', $this->lang->line('category'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentfeeCreate', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'category' => $this->input->post('category'),
            );
            $this->studentfee_model->add($data);
            $this->session->set_flashdata('msg', '<div studentfee="alert alert-success text-center">' . $this->lang->line('success_message') . '</div>');
            redirect('studentfee/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_edit')) {
            access_denied();
        }
        $data['title']      = 'Edit studentfees';
        $data['id']         = $id;
        $studentfee         = $this->studentfee_model->get($id);
        $data['studentfee'] = $studentfee;
        $this->form_validation->set_rules('category', $this->lang->line('category'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/studentfeeEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'id'       => $id,
                'category' => $this->input->post('category'),
            );
            $this->studentfee_model->add($data);
            $this->session->set_flashdata('msg', '<div studentfee="alert alert-success text-center">' . $this->lang->line('update_message') . '</div>');
            redirect('studentfee/index');
        }
    }

    public function getAppliedDiscounts()
    {
        $this->form_validation->set_rules('student_fees_deposite', $this->lang->line('student_fees_deposite'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $error = array(
                'student_fees_deposite'  => form_error('student_fees_deposite')
            );
            $array = array('status' => 'fail', 'error' => $error);
            echo json_encode($array);
        } else {

            $data                 = array();
            $student_fees_deposite  = $this->input->post('student_fees_deposite');
            $data['fees_discount']=$this->studentAppliedDiscount_model->get($student_fees_deposite);
            $page=$this->load->view('studentfee/_getAppliedDiscounts',$data,true);
            $return_array=['status'=>1,'page'=>$page];
            echo json_encode($return_array);
        }
    }

    public function addstudentfee()
    {
        $this->form_validation->set_rules('student_fees_master_id', $this->lang->line('fee_master'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('student'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|trim|xss_clean|numeric|callback_check_deposit');
        $this->form_validation->set_rules('amount_discount', $this->lang->line('discount'), 'required|trim|numeric|xss_clean');
        $this->form_validation->set_rules('amount_fine', $this->lang->line('fine'), 'required|trim|numeric|xss_clean');
        $this->form_validation->set_rules('payment_mode', $this->lang->line('payment_mode'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'amount'                 => form_error('amount'),
                'student_fees_master_id' => form_error('student_fees_master_id'),
                'fee_groups_feetype_id'  => form_error('fee_groups_feetype_id'),
                'amount_discount'        => form_error('amount_discount'),
                'amount_fine'            => form_error('amount_fine'),
                'payment_mode'           => form_error('payment_mode'),
                'date'           => form_error('date'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {

            $staff_record = $this->staff_model->get($this->customlib->getStaffID());
            $collected_by             = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
            $discounts = $this->input->post('discounts');

            if(!isset($discounts)){
                $discounts=[];
            }
            $json_array               = array(
                'amount'          => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
                'amount_discount' => convertCurrencyFormatToBaseAmount($this->input->post('amount_discount')),
                'amount_fine'     => convertCurrencyFormatToBaseAmount($this->input->post('amount_fine')),
                'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'     => $this->input->post('description'),
                'collected_by'    => $collected_by,
                'payment_mode'    => $this->input->post('payment_mode'),
                'received_by'     => $staff_record['id'],
            );

            $student_fees_master_id = $this->input->post('student_fees_master_id');
            $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');
            $transport_fees_id      = $this->input->post('transport_fees_id');
            $fee_category           = $this->input->post('fee_category');

            $data = array(
                'fee_category'           => $fee_category,
                'student_fees_master_id' => $this->input->post('student_fees_master_id'),
                'fee_groups_feetype_id'  => $this->input->post('fee_groups_feetype_id'),
                'amount_detail'          => $json_array,
            );

            if ($transport_fees_id != 0 && $fee_category == "transport") {
                $mailsms_array                    = new stdClass();
                $data['student_fees_master_id']   = null;
                $data['fee_groups_feetype_id']    = null;
                $data['student_transport_fee_id'] = $transport_fees_id;

                $mailsms_array                 = $this->studenttransportfee_model->getTransportFeeMasterByStudentTransportID($transport_fees_id);
                $mailsms_array->fee_group_name = $this->lang->line("transport_fees");
                $mailsms_array->type           = $mailsms_array->month;
                $mailsms_array->code           = "";
            } else {

                $mailsms_array = $this->feegrouptype_model->getFeeGroupByIDAndStudentSessionID($this->input->post('fee_groups_feetype_id'), $this->input->post('student_session_id'));

                if ($mailsms_array->is_system) {
                    $mailsms_array->amount = $mailsms_array->balance_fee_master_amount;
                }
            }

            $action             = $this->input->post('action');
            $send_to            = $this->input->post('guardian_phone');
            $email              = $this->input->post('guardian_email');
            $parent_app_key     = $this->input->post('parent_app_key');
            $student_session_id = $this->input->post('student_session_id');
            $inserted_id        = $this->studentfeemaster_model->fee_deposit($data, $send_to, $discounts,date('Y-m-d', $this->input->post('date')));

            $print_record = array();
            if ($action == "print") {
                $receipt_data           = json_decode($inserted_id);
                $data['sch_setting']    = $this->sch_setting_detail;

                $student                = $this->studentsession_model->searchStudentsBySession($student_session_id);
                $data['student']        = $student;
                $data['sub_invoice_id'] = $receipt_data->sub_invoice_id;

                $setting_result         = $this->setting_model->get();
                $data['settinglist']    = $setting_result;

                if ($transport_fees_id != 0 && $fee_category == "transport") {

                    $fee_record = $this->studentfeemaster_model->getTransportFeeByInvoice($receipt_data->invoice_id, $receipt_data->sub_invoice_id);
                    $data['feeList']        = $fee_record;
                    $print_record = $this->load->view('print/printTransportFeesByName', $data, true);
                } else {

                    $fee_record             = $this->studentfeemaster_model->getFeeByInvoice($receipt_data->invoice_id, $receipt_data->sub_invoice_id);
                    $data['feeList']        = $fee_record;                    
                    
                    if($this->thermal_print_module == 1 && $this->thermal_print_enable == 1){						
						$data['thermal_print'] = $this->thermal_print_result;						
						$print_record = $this->load->view('print/thermalPrintFeesByName', $data, true);                        
                    }else{
                        $print_record = $this->load->view('print/printFeesByName', $data, true);
                    }
                    
                }
            }

            $mailsms_array->invoice            = $inserted_id;
            $mailsms_array->student_session_id = $student_session_id;
            $mailsms_array->contact_no         = $send_to;
            $mailsms_array->email              = $email;
            $mailsms_array->parent_app_key     = $parent_app_key;
            $mailsms_array->fee_category       = $fee_category;

            $this->mailsmsconf->mailsms('fee_submission', $mailsms_array);

            $array = array('status' => 'success', 'error' => '', 'print' => $print_record);
            echo json_encode($array);
        }
    }

    public function printFeesByName()
    {
        $data                    = array('payment' => "0");
        $record                  = $this->input->post('data');
        $fee_category            = $this->input->post('fee_category');
        $invoice_id              = $this->input->post('main_invoice');
        $sub_invoice_id          = $this->input->post('sub_invoice');
        $student_session_id      = $this->input->post('student_session_id');
        $setting_result          = $this->setting_model->get();
        $data['settinglist']     = $setting_result;
        $student                 = $this->studentsession_model->searchStudentsBySession($student_session_id);
        $data['student']         = $student;
        $data['sub_invoice_id']  = $sub_invoice_id;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['superadmin_rest'] = $this->customlib->superadmin_visible();         

        if ($fee_category == "transport") {
            $fee_record      = $this->studentfeemaster_model->getTransportFeeByInvoice($invoice_id, $sub_invoice_id);
            $data['feeList'] = $fee_record;
            
            if($this->thermal_print_module == 1 && $this->thermal_print_enable == 1){				
				$data['thermal_print'] = $this->thermal_print_result;				
				$page  = $this->load->view('print/thermalPrintTransportFeesByName', $data, true);
            }else{
                $page  = $this->load->view('print/printTransportFeesByName', $data, true);
            }
        } else {
            $fee_record      = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);
            $data['feeList'] = $fee_record;

            if($this->thermal_print_module == 1 && $this->thermal_print_enable == 1){				
				$data['thermal_print'] = $this->thermal_print_result;				
				$page = $this->load->view('print/thermalPrintFeesByName', $data, true);                
            }else{
                $page = $this->load->view('print/printFeesByName', $data, true);
            }
        }

        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function printFeesByGroup()
    {
        $fee_category           = $this->input->post('fee_category');
        $trans_fee_id           = $this->input->post('trans_fee_id');
        $setting_result         = $this->setting_model->get();
        $data['settinglist']    = $setting_result;
        $data['sch_setting']    = $this->sch_setting_detail;        

        if ($fee_category == "transport") {
            $data['feeList'] = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);

            if($this->thermal_print_module == 1 && $this->thermal_print_enable == 1){			
				$data['thermal_print'] = $this->thermal_print_result;				
				$page = $this->load->view('print/thermalPrintTransportFeesByGroup', $data, true); 				
            }else{
                $page = $this->load->view('print/printTransportFeesByGroup', $data, true); 
            }

        } else {
            $fee_groups_feetype_id = $this->input->post('fee_groups_feetype_id');
            $fee_master_id         = $this->input->post('fee_master_id');
            $fee_session_group_id  = $this->input->post('fee_session_group_id');
            $data['feeList']       = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
            if($this->thermal_print_module == 1 && $this->thermal_print_enable == 1){				
				$data['thermal_print'] = $this->thermal_print_result;
				$page  = $this->load->view('print/thermalPrintFeesByGroup', $data, true);
            }else{
               $page  = $this->load->view('print/printFeesByGroup', $data, true);
            }
        }
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function printFeesByGroupArray()
    {
        $data['sch_setting'] = $this->sch_setting_detail;
        $record              = $this->input->post('data');
        $record_array        = json_decode($record);
        $fees_array          = array();
        foreach ($record_array as $key => $value) {
            $fee_groups_feetype_id = $value->fee_groups_feetype_id;
            $fee_master_id         = $value->fee_master_id;
            $fee_session_group_id  = $value->fee_session_group_id;
            $fee_category          = $value->fee_category;
            $trans_fee_id          = $value->trans_fee_id;

            if ($fee_category == "transport") {
                $feeList               = $this->studentfeemaster_model->getTransportFeeByID($trans_fee_id);
                $feeList->fee_category = $fee_category;
            } else {
                $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
                $feeList->fee_category = $fee_category;
            }
            $fees_array[] = $feeList;
        }
        $data['feearray'] = $fees_array;       
		
        if($this->thermal_print_module == 1 && $this->thermal_print_enable == 1){   
			$data['thermal_print'] = $this->thermal_print_result;			
			$this->load->view('print/thermalPrintFeesByGroupArray', $data);             
        }else{
			$this->load->view('print/printFeesByGroupArray', $data); 
        }        
    }

    public function searchpayment()
    {
        if (!$this->rbac->hasPrivilege('search_fees_payment', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'studentfee/searchpayment');
        $data['title'] = $this->lang->line('fees_collection');

        $this->form_validation->set_rules('paymentid', $this->lang->line('payment_id'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
        } else {
            $paymentid = $this->input->post('paymentid');
            $invoice   = explode("/", $paymentid);

            if (array_key_exists(0, $invoice) && array_key_exists(1, $invoice)) {
                $invoice_id             = $invoice[0];
                $sub_invoice_id         = $invoice[1];
                $feeList                = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);
               $current_session= $this->customlib->getCurrentSession();
                $data['current_session']        = $current_session;
                $data['feeList']        = $feeList;
                $data['sub_invoice_id'] = $sub_invoice_id;
            } else {
                $data['feeList'] = array();
            }
        }
        $data['sch_setting'] = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/searchpayment', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addfeegroup()
    {
        $this->form_validation->set_rules('fee_session_groups', $this->lang->line('fee_group'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'fee_session_groups' => form_error('fee_session_groups'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $student_session_id     = $this->input->post('student_session_id');
            $fee_session_groups     = $this->input->post('fee_session_groups');
            $student_sesssion_array = isset($student_session_id) ? $student_session_id : array();
            $student_ids            = $this->input->post('student_ids');
            $delete_student         = array_diff($student_ids, $student_sesssion_array);

            $preserve_record = array();
            if (!empty($student_sesssion_array)) {
                foreach ($student_sesssion_array as $key => $value) {
                    $insert_array = array(
                        'student_session_id'   => $value,
                        'fee_session_group_id' => $fee_session_groups,
                    );
                    $inserted_id = $this->studentfeemaster_model->add($insert_array);

                    $preserve_record[] = $inserted_id;
                }
            }
            if (!empty($delete_student)) {
                $this->studentfeemaster_model->delete($fee_session_groups, $delete_student);
            }

            $array = array('status' => 1, 'error' => '');
            echo json_encode($array);
        }
    }

    public function getBalanceFee()
    {
        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('fee_groups_feetype_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_fees_master_id', $this->lang->line('student_fees_master_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('student_session_id', $this->lang->line('student_session_id'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'fee_groups_feetype_id'  => form_error('fee_groups_feetype_id'),
                'student_fees_master_id' => form_error('student_fees_master_id'),
                'student_session_id'     => form_error('student_session_id'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $data                 = array();
            $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');
            $student_fees_master_id = $this->input->post('student_fees_master_id');
            $student_session_id   = $this->input->post('student_session_id');
            $student=$this->student_model->getByStudentSession($student_session_id);
            $discount_not_applied = $this->getNotAppliedDiscount($student_session_id);
            $fee_category = $this->input->post('fee_category');
            $trans_fee_id         = $this->input->post('trans_fee_id');

            if ($fee_category == "transport") {
                $remain_amount_object = $this->getStudentTransportFeetypeBalance($trans_fee_id);
                $remain_amount        = (float) json_decode($remain_amount_object)->balance;
                $remain_amount_fine   = json_decode($remain_amount_object)->fine_amount;
            } else {
                $remain_amount_object = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);
                $remain_amount        = (float) json_decode($remain_amount_object)->balance;
                $remain_amount_fine   = json_decode($remain_amount_object)->fine_amount;
            }

            $remain_amount = number_format($remain_amount, 2, ".", "");

            $array = array(
                  'balance' => convertBaseAmountCurrencyFormat($remain_amount), 
                  'discount_not_applied' => $discount_not_applied,
                  'remain_amount_fine' => convertBaseAmountCurrencyFormat($remain_amount_fine),
                  'student_fees' => convertBaseAmountCurrencyFormat(json_decode($remain_amount_object)->student_fees),
                  'student'=>$student,
                  'fee_groups_feetype_id'=>$fee_groups_feetype_id,
                  'student_fees_master_id'=>$student_fees_master_id,
                  'student_session_id'=>$student_session_id,
                  'fee_category'=>$fee_category,
                  'transport_fees_id'=>$trans_fee_id                
                );

            $page=$this->load->view('studentfee/_getBalanceFee',$array,true);

            $return_array=['status'=>1,'page'=>$page,'balance'=>convertBaseAmountCurrencyFormat($remain_amount)];
            echo json_encode($return_array);
        }
    }

    public function getStudentTransportFeetypeBalance($trans_fee_id)
    {
        $data = array();

        $result          = $this->studentfeemaster_model->studentTransportDeposit($trans_fee_id);
        $amount_balance  = 0;
        $amount          = 0;
        $amount_fine     = 0;
        $amount_discount = 0;
        $fine_amount     = 0;
        $fee_fine_amount = 0;

        $due_amt = $result->fees;
        if (strtotime($result->due_date) < strtotime(date('Y-m-d'))) {
            $fee_fine_amount = is_null($result->fine_percentage) ? $result->fine_amount : percentageAmount($result->fees, $result->fine_percentage);
        }

        $amount_detail = json_decode($result->amount_detail);
        if (is_object($amount_detail)) {

            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                $amount          = $amount + $amount_detail_value->amount;
                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
            }
        }

        $amount_balance = $due_amt - ($amount + $amount_discount);
        $fine_amount    = abs($amount_fine - $fee_fine_amount);
        $array          = array('status' => 'success', 'error' => '', 'student_fees' => $due_amt, 'balance' => $amount_balance, 'fine_amount' => $fine_amount);
        return json_encode($array);
    }
	
	public function getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id)
    {
        $data                           = array();
        $data['fee_groups_feetype_id']  = $fee_groups_feetype_id;
        $data['student_fees_master_id'] = $student_fees_master_id;
        $result                         = $this->studentfeemaster_model->studentDeposit($data);

        $amount_balance  = 0;
        $amount          = 0;
        $amount_fine     = 0;
        $amount_discount = 0;
        $fine_amount     = 0;
        $fee_fine_amount = 0;
        $due_fine_amount = 0;
        $due_amt         = $result->amount;
        if ((!empty($result->due_date)) && strtotime($result->due_date) < strtotime(date('Y-m-d'))) {

        // get cumulative fine amount as delay days 
            if($result->fine_type=='cumulative'){
                $date1=date_create("$result->due_date");
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $due_days= $diff->format("%a");;
                
                if($this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id,$due_days)){
                    $due_fine_amount=$this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id,$due_days);
                }else{
                    $due_fine_amount=0;
                }
                $fee_fine_amount       = $due_fine_amount;

            }else if($result->fine_type=='fix' || $result->fine_type=='percentage'){
                $fee_fine_amount       = $result->fine_amount;
            }
        // get cumulative fine amount as delay days
        }

        if ($result->is_system) {
            $due_amt = $result->student_fees_master_amount;
        }

        $amount_detail = json_decode($result->amount_detail);
        if (is_object($amount_detail)) {

            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                $amount          = $amount + $amount_detail_value->amount;
                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
            }
        }

        $amount_balance = $due_amt - ($amount + $amount_discount);
        $fine_amount    = ($fee_fine_amount > 0) ? ($fee_fine_amount - $amount_fine) : 0;
        $array          = array('status' => 'success', 'error' => '', 'student_fees' => $due_amt, 'balance' => $amount_balance, 'fine_amount' => $fine_amount);
        return json_encode($array);
    }

    public function check_deposit($amount)
    {
        if (is_numeric($this->input->post('amount')) && is_numeric($this->input->post('amount_discount'))) {
            if ($this->input->post('amount') != "" && $this->input->post('amount_discount') != "") {
                if ($this->input->post('amount') < 0) {
                    $this->form_validation->set_message('check_deposit', $this->lang->line('deposit_amount_can_not_be_less_than_zero'));
                    return false;
                } else {
                    $transport_fees_id      = $this->input->post('transport_fees_id');
                    $student_fees_master_id = $this->input->post('student_fees_master_id');
                    $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');
                    $deposit_amount         = $this->input->post('amount') - $this->input->post('amount_discount');
                    if ($transport_fees_id != 0) {
                        $remain_amount = $this->getStudentTransportFeetypeBalance($transport_fees_id);
                    } else {
                        $remain_amount = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);
                    }
                    $remain_amount = json_decode($remain_amount)->balance;
                    if (convertBaseAmountCurrencyFormat($remain_amount) < $deposit_amount) {
                        $this->form_validation->set_message('check_deposit', $this->lang->line('deposit_amount_can_not_be_greater_than_remaining'));
                        return false;
                    }
                }
                return true;
            }
        } elseif (!is_numeric($this->input->post('amount'))) {
            $this->form_validation->set_message('check_deposit', $this->lang->line('amount_field_must_contain_only_numbers'));
            return false;
        } elseif (!is_numeric($this->input->post('amount_discount'))) {
            return true;
        }

        return true;
    }

    public function getNotAppliedDiscount($student_session_id)
    {
        $discounts_array = $this->feediscount_model->getDiscountNotApplied($student_session_id);
        foreach ($discounts_array as $discount_key => $discount_value) {
            $discounts_array[$discount_key]->{"amount"} = convertBaseAmountCurrencyFormat($discount_value->amount);
        }
        return $discounts_array;
    }

    public function addfeegrp()
    {
        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('row_counter[]', $this->lang->line('fees_list'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('collected_date', $this->lang->line('date'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'row_counter'    => form_error('row_counter'),
                'collected_date' => form_error('collected_date'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        }
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
}