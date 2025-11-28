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
                        $this->load->model("feegrouptype_model");
                        $this->load->model("studenttransportfee_model");
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
                $data['discount_list'] = $this->feediscount_model->get(); // Fetch discount types
                $data['classlist'] = $this->class_model->get(); // Fetch class list
        
                // Simply load the view, POST handling is moved to another method
                $this->load->view('layout/header', $data);
                $this->load->view('studentfee/bulk_upload_fees', $data);
                $this->load->view('layout/footer', $data);
            }

            public function bulk_adjustment_upload()
            {
                if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
                    access_denied();
                }

                $this->form_validation->set_rules('adjustment_file', 'CSV file', 'callback_handle_adjustment_csv_upload');

                if ($this->form_validation->run() == FALSE) {
                    $this->session->set_flashdata('error_msg', validation_errors());
                    redirect('studentfee/bulk_upload_fees');
                } else {
                    $file_path = $_FILES['adjustment_file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file_path);

                    if (!empty($result)) {
                        $success_count = 0;
                        $error_messages = [];

                        foreach ($result as $row) {
                            $admission_no = $row['admission_no'];
                            $amount = $row['amount'];
                            $date = $row['date'];
                            $payment_mode = $row['payment_mode'];
                            $description = $row['description'];

                            if (empty($admission_no) || !is_numeric($amount) || empty($date) || empty($payment_mode)) {
                                $error_messages[] = "Invalid data for admission number: {$admission_no}";
                                continue;
                            }

                            $student = $this->student_model->findByAdmission($admission_no);

                            if ($student) {
                                $student_session_id = $student->student_session_id;

                                // Find the feetype_id for 'Previous Session Balance'
                                $fee_type = $this->feetype_model->checkFeetypeByName('Previous Session Balance');

                                if ($fee_type) {
                                    $fee_master_details = $this->studentfeemaster_model->getFeeByFeeType($student_session_id, $fee_type->id);

                                    if ($fee_master_details) {
                                        $fee_balance_obj = json_decode($this->getStuFeetypeBalance($fee_master_details->fee_groups_feetype_id, $fee_master_details->id));
                                        $fee_balance = $fee_balance_obj->balance;

                                        $amount_to_pay = $amount;
                                        $advance_amount = 0;

                                        if ($amount > $fee_balance) {
                                            $amount_to_pay = $fee_balance;
                                            $advance_amount = $amount - $fee_balance;
                                        }

                                        if ($amount_to_pay > 0) {
                                            $json_array = [
                                                'amount'          => $amount_to_pay,
                                                'amount_discount' => 0,
                                                'amount_fine'     => 0,
                                                'date'            => date('Y-m-d', strtotime($date)),
                                                'description'     => $description,
                                                'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                                'payment_mode'    => $payment_mode,
                                                'received_by'     => $this->customlib->getStaffID(),
                                            ];

                                            $data_to_insert = [
                                                'fee_category'           => 'fees',
                                                'student_fees_master_id' => $fee_master_details->id,
                                                'fee_groups_feetype_id'  => $fee_master_details->fee_groups_feetype_id,
                                                'amount_detail'          => $json_array,
                                            ];

                                            $this->studentfeemaster_model->fee_deposit($data_to_insert, null, [], date('Y-m-d', strtotime($date)));
                                        }

                                        if ($advance_amount > 0) {
                                            $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                                            $json_array_advance = [
                                                'amount'          => $advance_amount,
                                                'amount_discount' => 0,
                                                'amount_fine'     => 0,
                                                'date'            => date('Y-m-d', strtotime($date)),
                                                'description'     => 'Advance from bulk adjustment',
                                                'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                                'payment_mode'    => $payment_mode,
                                                'received_by'     => $this->customlib->getStaffID(),
                                            ];

                                            $data_to_insert_advance = [
                                                'fee_category'           => 'fees',
                                                'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                                                'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                                                'amount_detail'          => $json_array_advance,
                                            ];

                                            $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], date('Y-m-d', strtotime($date)));
                                        }

                                        $success_count++;
                                    } else {
                                        $error_messages[] = "Balance Master fee not found for admission number: {$admission_no}";
                                    }
                                } else {
                                    $error_messages[] = "'Previous Session Balance' fee type not found.";
                                }
                            } else {
                                $error_messages[] = "Student not found for admission number: {$admission_no}";
                            }
                        }

                        $this->session->set_flashdata('msg', "<div class='alert alert-success'>Successfully added {$success_count} adjustment records.</div>");
                        if (!empty($error_messages)) {
                            $this->session->set_flashdata('error_msg', "<div class='alert alert-danger'>" . implode('<br>', $error_messages) . "</div>");
                        }
                    } else {
                        $this->session->set_flashdata('error_msg', "<div class='alert alert-danger'>Error reading CSV file.</div>");
                    }

                    redirect('studentfee/bulk_upload_fees');
                }
            }

            public function handle_adjustment_csv_upload()
            {
                if (isset($_FILES["adjustment_file"]) && !empty($_FILES['adjustment_file']['name'])) {
                    $allowed_mime_type_arr = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
                    $mime = get_mime_by_extension($_FILES['adjustment_file']['name']);
                    if (in_array($mime, $allowed_mime_type_arr)) {
                        return true;
                    } else {
                        $this->form_validation->set_message('handle_adjustment_csv_upload', 'Please select only CSV file.');
                        return false;
                    }
                } else {
                    $this->form_validation->set_message('handle_adjustment_csv_upload', 'Please select a CSV file.');
                    return false;
                }
            }

            public function do_bulk_upload_transport_fees()
            {
                $this->output->set_content_type('application/json');

                if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
                    echo json_encode(['status' => 'fail', 'error' => ['message' => 'Access Denied']]);
                    return;
                }

                $this->form_validation->set_rules('transport_file', $this->lang->line('file'), 'callback_handle_transport_csv_upload');

                if ($this->form_validation->run() == false) {
                    $errors = $this->form_validation->error_array();
                    echo json_encode(['status' => 'fail', 'error' => $errors]);
                    return;
                } else {
                    $file_path = $_FILES['transport_file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file_path);

                    if (!empty($result)) {
                        $error_messages = [];
                        $total_records = 0;
                        $successful_records = 0;
                        $failed_records = 0;

                        foreach ($result as $row_num => $row) {
                            $total_records++;
                            $admission_no = $row['admission_no'] ?? '';
                            $amount = $row['amount'] ?? '';
                            $date = $row['date'] ?? '';
                            $payment_mode = $row['payment_mode'] ?? '';
                            $description = $row['description'] ?? '';

                            if (empty($admission_no) || !is_numeric($amount) || empty($date) || empty($payment_mode)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Missing or invalid required fields.";
                                $failed_records++;
                                continue;
                            }

                            $valid_payment_modes = ['cash', 'cheque', 'dd', 'bank_transfer', 'upi', 'card', 'govt_7_5_payment', 'govt_fg_payment'];
                            if (!in_array(strtolower($payment_mode), $valid_payment_modes)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Invalid payment mode. Please use one of the following: " . implode(', ', $valid_payment_modes);
                                $failed_records++;
                                continue;
                            }

                            if (DateTime::createFromFormat('Y-m-d', $date) === false) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Invalid date format for date. Please use YYYY-MM-DD format.";
                                $failed_records++;
                                continue;
                            }

                            $student = $this->student_model->findByAdmission($admission_no);
                            if (!$student) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Student not found for admission number: {$admission_no}.";
                                $failed_records++;
                                continue;
                            }

                            $student_session_id = $student->student_session_id;

                            // Get the school setting to determine transport fee type
                            $sch_setting = $this->setting_model->getSetting();
                            $transport_fee_type_setting = $sch_setting->transport_fee_type; // 'yearly' or 'monthly' etc.

                            $transport_fee_details = $this->studenttransportfee_model->getTransportFeeByStudentSession($student_session_id, $student->route_pickup_point_id);
                            
                            if (empty($transport_fee_details)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": No transport fee assigned to student {$admission_no}.";
                                $failed_records++;
                                continue;
                            }

                            $target_transport_fee = null;
                            foreach ($transport_fee_details as $tf_detail) {
                                // Dynamically check against the configured transport_fee_type
                                if (isset($tf_detail['month']) && $tf_detail['month'] == $transport_fee_type_setting) {
                                    $target_transport_fee = $tf_detail;
                                    break;
                                }
                            }

                            if (is_null($target_transport_fee)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": No '{$transport_fee_type_setting}' transport fee found for student {$admission_no}.";
                                $failed_records++;
                                continue;
                            }

                            $student_transport_fee_id = $target_transport_fee['student_transport_fee_id'];
                            $transport_feemaster_id = $target_transport_fee['id']; // This is the ID from transport_feemaster

                            // Get the balance for this specific transport fee
                            $remain_amount_object = json_decode($this->getStudentTransportFeetypeBalance($student_transport_fee_id));
                            $fee_balance = (float) $remain_amount_object->balance;

                            $amount_to_pay = $amount;
                            $advance_amount = 0;

                            if ($amount > $fee_balance) {
                                $amount_to_pay = $fee_balance;
                                $advance_amount = $amount - $fee_balance;
                            }

                            if ($amount_to_pay > 0) {
                                $json_array = [
                                    'amount'          => $amount_to_pay,
                                    'amount_discount' => 0,
                                    'amount_fine'     => 0,
                                    'date'            => date('Y-m-d', strtotime($date)),
                                    'description'     => $description,
                                    'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                    'payment_mode'    => $payment_mode,
                                    'received_by'     => $this->customlib->getStaffID(),
                                ];

                                $data_to_insert = [
                                    'fee_category'           => 'transport', // Important: set fee_category to 'transport'
                                    'student_fees_master_id' => null, // Not applicable for transport fees
                                    'fee_groups_feetype_id'  => null, // Not applicable for transport fees
                                    'student_transport_fee_id' => $student_transport_fee_id, // Link to student_transport_fees
                                    'amount_detail'          => $json_array,
                                ];

                                $inserted_id = $this->studentfeemaster_model->fee_deposit($data_to_insert, null, [], date('Y-m-d', strtotime($date)));
                            } else {
                                $inserted_id = true; // Nothing to pay, so consider it successful
                            }

                            if ($inserted_id) {
                                if ($advance_amount > 0) {
                                    $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                                    $json_array_advance = [
                                        'amount'          => $advance_amount,
                                        'amount_discount' => 0,
                                        'amount_fine'     => 0,
                                        'date'            => date('Y-m-d', strtotime($date)),
                                        'description'     => 'Advance from Bulk Transport Fee Upload',
                                        'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                        'payment_mode'    => $payment_mode,
                                        'received_by'     => $this->customlib->getStaffID(),
                                    ];

                                    $data_to_insert_advance = [
                                        'fee_category'           => 'fees',
                                        'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                                        'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                                        'amount_detail'          => $json_array_advance,
                                    ];

                                    $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], date('Y-m-d', strtotime($date)));
                                }
                                $successful_records++;
                            } else {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Failed to deposit transport fee for student {$admission_no}.";
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

            public function handle_transport_csv_upload()
            {
                if (isset($_FILES["transport_file"]) && !empty($_FILES['transport_file']['name'])) {
                    $allowed_mime_type_arr = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
                    $mime = get_mime_by_extension($_FILES['transport_file']['name']);
                    if (in_array($mime, $allowed_mime_type_arr)) {
                        return true;
                    } else {
                        $this->form_validation->set_message('handle_transport_csv_upload', 'Please select only CSV file.');
                        return false;
                    }
                } else {
                    $this->form_validation->set_message('handle_transport_csv_upload', 'Please select a CSV file.');
                    return false;
                }
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
                    $this->load->library('CSVReader');
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

                            if (!is_numeric($total_amount_paid)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Invalid total amount paid. Please provide a numeric value.";
                                $failed_records++;
                                continue;
                            }

                            $valid_payment_modes = ['cash', 'cheque', 'dd', 'bank_transfer', 'upi', 'card', 'govt_7_5_payment', 'govt_fg_payment'];
                            if (!in_array(strtolower($payment_mode), $valid_payment_modes)) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Invalid payment mode. Please use one of the following: " . implode(', ', $valid_payment_modes);
                                $failed_records++;
                                continue;
                            }

                            if (DateTime::createFromFormat('Y-m-d', $old_bill_date) === false) {
                                $error_messages[] = "Row " . ($row_num + 2) . ": Invalid date format for old_bill_date. Please use YYYY-MM-DD format.";
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
                                    $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                                    $json_array_advance = [
                                        'amount'          => $advance_amount,
                                        'amount_discount' => 0,
                                        'amount_fine'     => 0,
                                        'date'            => date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)),
                                        'description'     => 'Advance Payment',
                                        'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                        'payment_mode'    => $payment_mode,
                                        'received_by'     => $this->customlib->getStaffID(),
                                    ];

                                    $data_to_insert_advance = [
                                        'fee_category'           => 'fees',
                                        'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                                        'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                                        'amount_detail'          => $json_array_advance,
                                    ];

                                    $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)));
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

            public function exportadjustmentformat()
            {
                $this->load->helper('download');
                $filepath = "./backend/import/sample_carry_forward_adjustment.csv";
                $data     = file_get_contents($filepath);
                $name     = 'sample_carry_forward_adjustment.csv';

                force_download($name, $data);
            }

            public function exporttransportfeesformat()
            {
                $this->load->helper('download');
                $filepath = "./backend/import/sample_transport_fees_bulk_upload.csv";
                $data     = file_get_contents($filepath);
                $name     = 'sample_transport_fees_bulk_upload.csv';

                force_download($name, $data);
            }

            public function handle_csv_upload()
            {
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

                    if (!in_array($mime, $allowedMimeTypes)) {
                        $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                        return false;
                    }
                    return true;
                } else {
                    $this->form_validation->set_message('handle_csv_upload', $this->lang->line('the_file_field_is_required'));
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
        } else {
            $array = array('status' => 1, 'error' => '', 'params' => array(
                'class_id'    => $this->input->post('class_id'),
                'section_id'  => $this->input->post('section_id'),
                'search_text' => $this->input->post('search_text'),
                'search_type'   => $this->input->post('search_type'),
            ));
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
        $transportfessthe_array = [];

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
                        $transportfessthe_array[] = $trs_value->id;
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
        } else {
            $data['student_remain_fees'] = array();
            $feegroup                    = $this->input->post('feegroup');
            $class_id                    = $this->input->post('class_id');
            $section_id                  = $this->input->post('section_id');
            $student_remain_fees         = $this->studentfeemaster_model->searchStudentsByFeeGroups($feegroup, $class_id, $section_id);
            $data['student_remain_fees'] = $student_remain_fees;
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

        $this->form_validation->set_rules('deletion_reason', 'Deletion Reason', 'trim|required|min_length[80]');

        if ($this->form_validation->run() == FALSE) {
            $msg = array(
                'deletion_reason' => form_error('deletion_reason'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $invoice_id  = $this->input->post('main_invoice');
            $sub_invoice = $this->input->post('sub_invoice');
            $reason      = $this->input->post('deletion_reason');

            if (!empty($invoice_id)) {
                $this->studentfee_model->remove($invoice_id, $sub_invoice, $reason);
            }
            $array = array('status' => 'success', 'result' => 'success');
        }
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
        $student_session_id  = 0; 

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
                 if($student_session_id == 0){
                    $student_session_id = $feeList->student_session_id;
                }
            } else {
                $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);
                $feeList->fee_category = $fee_category;
                 if($student_session_id == 0){
                    $student_session_id = $feeList->student_session_id;
                }
            }

            $fees_array[] = $feeList;
        }

        $data['feearray'] = $fees_array;
        $data['discount_not_applied'] = $this->getNotAppliedDiscount($student_session_id);
        $advance_balances = $this->studentfeemaster_model->get_advance_balance($student_session_id);
        $data['paid_advance_balance'] = $advance_balances['paid_advance_balance'];
        $data['discount_advance_balance'] = $advance_balances['discount_advance_balance'];

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
        log_message('error', 'addstudentfee method called.');

        $this->form_validation->set_rules('student_fees_master_id', $this->lang->line('fee_master'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('student'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|trim|xss_clean|numeric');
        $this->form_validation->set_rules('amount_discount', $this->lang->line('discount'), 'required|trim|numeric|xss_clean');
        $this->form_validation->set_rules('amount_fine', $this->lang->line('fine'), 'required|trim|numeric|xss_clean');

        if ($this->input->post('use_advance') != 'yes') {
            $this->form_validation->set_rules('payment_mode', $this->lang->line('payment_mode'), 'required|trim|xss_clean');
        }

        if ($this->form_validation->run() == false) {
            log_message('error', 'addstudentfee form validation failed: ' . validation_errors());
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
            log_message('error', 'addstudentfee form validation successful.');
            log_message('error', 'Date from form: ' . $this->input->post('date'));

            $staff_record = $this->staff_model->get($this->customlib->getStaffID());
            $student_session_id = $this->input->post('student_session_id');
            $transport_fees_id = $this->input->post('transport_fees_id');
            $student_fees_master_id = $this->input->post('student_fees_master_id');
            $fee_groups_feetype_id = $this->input->post('fee_groups_feetype_id');

            // Get the fee balance
            if ($transport_fees_id != 0) {
                $remain_amount_obj = $this->getStudentTransportFeetypeBalance($transport_fees_id);
            } else {
                $remain_amount_obj = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);
            }
            $fee_balance = json_decode($remain_amount_obj)->balance;

            $paying_amount = $this->input->post('amount');
            $amount_discount = $this->input->post('amount_discount');
            $net_payment = $paying_amount + $amount_discount;

            $overpayment_amount = 0;
            $amount_to_apply = $paying_amount;
            $discount_to_record_for_current_fee = $amount_discount; // Initialize with full input discount

            $cash_part_of_overpayment = 0;
            $discount_part_of_overpayment = 0;

            if ($net_payment > $fee_balance) {
                $overpayment_amount = $net_payment - $fee_balance;
                // Adjust amount to apply, ensuring it doesn't go negative
                $amount_to_apply = max(0, $paying_amount - $overpayment_amount);

                // Calculate how much of the discount contributes to the overpayment
                $cash_paid_for_fee = $paying_amount;
                $discount_applied_for_fee = $amount_discount;
                $actual_fee_balance = $fee_balance;

                if ($cash_paid_for_fee >= $actual_fee_balance) {
                    $cash_part_of_overpayment = $cash_paid_for_fee - $actual_fee_balance;
                    $discount_part_of_overpayment = $discount_applied_for_fee;
                } else {
                    $remaining_fee_to_cover = $actual_fee_balance - $cash_paid_for_fee;
                    if ($discount_applied_for_fee > $remaining_fee_to_cover) {
                        $discount_part_of_overpayment = $discount_applied_for_fee - $remaining_fee_to_cover;
                    }
                    $cash_part_of_overpayment = 0;
                }
                
                // Adjust the discount to record for the current fee
                $discount_to_record_for_current_fee = $amount_discount - $discount_part_of_overpayment;
            }

            $staff_record = $this->staff_model->get($this->customlib->getStaffID());
            $collected_by             = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
            $discounts = $this->input->post('discounts');
            $use_advance = $this->input->post('use_advance');

            if(!isset($discounts)){
                $discounts=[];
            }

            $selected_payment_mode = $this->input->post('payment_mode');
            $final_payment_mode_for_main_fee = $selected_payment_mode;

            if ($use_advance == 'yes') {
                $final_payment_mode_for_main_fee = 'Advance';
            } elseif (convertCurrencyFormatToBaseAmount($amount_to_apply) == 0 && convertCurrencyFormatToBaseAmount($discount_to_record_for_current_fee) > 0) {
                $final_payment_mode_for_main_fee = 'Discount';
            }

            $json_array = array(
                'amount'          => convertCurrencyFormatToBaseAmount($amount_to_apply),
                'amount_discount' => convertCurrencyFormatToBaseAmount($discount_to_record_for_current_fee),
                'amount_fine'     => convertCurrencyFormatToBaseAmount($this->input->post('amount_fine')),
                'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'     => $this->input->post('description'),
                'collected_by'    => $collected_by,
                'payment_mode'    => $final_payment_mode_for_main_fee,
                'received_by'     => $staff_record['id'],
            );

            $fee_category           = $this->input->post('fee_category');

            $data = array(
                'fee_category'           => $fee_category,
                'student_fees_master_id' => $student_fees_master_id,
                'fee_groups_feetype_id'  => $fee_groups_feetype_id,
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

            log_message('error', 'Calling fee_deposit with data: ' . print_r($data, true));
            $inserted_id        = $this->studentfeemaster_model->fee_deposit($data, $send_to, $discounts,date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))));
            log_message('error', 'fee_deposit returned: ' . $inserted_id);

            if ($overpayment_amount > 0) {
                // Calculate how much of the discount contributes to the overpayment
                $cash_paid_for_fee = $paying_amount;
                $discount_applied_for_fee = $amount_discount;
                $actual_fee_balance = $fee_balance;

                $cash_part_of_overpayment = 0;
                $discount_part_of_overpayment = 0;

                if ($cash_paid_for_fee >= $actual_fee_balance) {
                    // All fee balance is covered by cash, so all discount (if any) is overpaid discount
                    // And remaining cash is cash overpayment
                    $cash_part_of_overpayment = $cash_paid_for_fee - $actual_fee_balance;
                    $discount_part_of_overpayment = $discount_applied_for_fee;
                } else {
                    // Cash partially covers the fee
                    $remaining_fee_to_cover = $actual_fee_balance - $cash_paid_for_fee;
                    // Any discount beyond the remaining fee becomes discount overpayment
                    if ($discount_applied_for_fee > $remaining_fee_to_cover) {
                        $discount_part_of_overpayment = $discount_applied_for_fee - $remaining_fee_to_cover;
                    }
                    // No cash overpayment in this scenario, as cash was less than fee
                    $cash_part_of_overpayment = 0;
                }

                $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                $json_array_advance = [
                    'amount'          => convertCurrencyFormatToBaseAmount($cash_part_of_overpayment),
                    'amount_discount' => convertCurrencyFormatToBaseAmount($discount_part_of_overpayment),
                    'amount_fine'     => 0,
                    'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                    'description'     => 'Overpayment from single fee collection',
                    'collected_by'    => $collected_by,
                    'payment_mode'    => 'Advance', // Hardcoded to Advance
                    'received_by'     => $staff_record['id'],
                ];

                $data_to_insert_advance = [
                    'fee_category'           => 'fees',
                    'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                    'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                    'amount_detail'          => $json_array_advance,
                ];
                $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))));
            }

            if ($use_advance == 'yes') {
                log_message('error', 'use_advance is yes, deducting from advance.');
                $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                $json_array_advance = [
                    'amount'          => -($this->input->post('amount')),
                    'amount_discount' => 0,
                    'amount_fine'     => 0,
                    'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                    'description'     => 'Advance amount used for fee payment',
                    'collected_by'    => $collected_by,
                    'payment_mode'    => 'Advance Adjustment',
                    'received_by'     => $staff_record['id'],
                ];

                $data_to_insert_advance = [
                    'fee_category'           => 'fees',
                    'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                    'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                    'amount_detail'          => $json_array_advance,
                ];

                log_message('error', 'Calling fee_deposit for advance deduction with data: ' . print_r($data_to_insert_advance, true));
                $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))));
                log_message('error', 'Advance deduction successful.');
            }

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

            log_message('error', 'addstudentfee successful, sending response.');
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

            if (!empty($delete_student)) {
                foreach ($delete_student as $student_to_delete_session_id) {
                    $this->studentfeemaster_model->reallocate_payments($student_to_delete_session_id, $fee_session_groups, null);
                }
            }

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

            $array = array('status' => 'success', 'message' => $this->lang->line('fees_group_assign_successfully'));
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
            $advance_balances = $this->studentfeemaster_model->get_advance_balance($student_session_id);
            $paid_advance_balance = $advance_balances['paid_advance_balance'];
            $discount_advance_balance = $advance_balances['discount_advance_balance'];
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
                  'paid_advance_balance' => $paid_advance_balance, 
                  'discount_advance_balance' => $discount_advance_balance,
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
	        log_message('debug', 'getStuFeetypeBalance: Calling studentDeposit with data: ' . json_encode($data));
	        $result                         = $this->studentfeemaster_model->studentDeposit($data);
	        log_message('debug', 'getStuFeetypeBalance: studentDeposit returned result: ' . json_encode($result));
	
	        if (empty($result)) {
	            log_message('error', 'getStuFeetypeBalance: studentDeposit returned empty result for fee_groups_feetype_id ' . $fee_groups_feetype_id . ' and student_fees_master_id ' . $student_fees_master_id);
	            return json_encode(array('status' => 'fail', 'error' => 'Fee details not found for provided IDs.'));
	        }
	        
	        $amount_balance  = 0;
	        $amount          = 0;
	        $amount_fine     = 0;
	        $amount_discount = 0;
	        $fine_amount     = 0;
	        $fee_fine_amount = 0;
	        $due_fine_amount = 0;
	        
	        // Ensure properties exist before accessing them to prevent Undefined property errors
	        $due_amt = isset($result->amount) ? $result->amount : 0;
	
	        if ((!empty($result->due_date)) && strtotime($result->due_date) < strtotime(date('Y-m-d'))) {
			  // get cumulative fine amount as delay days 
	            if(isset($result->fine_type) && $result->fine_type=='cumulative'){
	                $date1=date_create(isset($result->due_date) ? "$result->due_date" : date('Y-m-d'));
	                $date2=date_create(date('Y-m-d'));
	                $diff=date_diff($date1,$date2);
	                $due_days= $diff->format("%a");;
	                
	                if($this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id,$due_days)){
	                    $due_fine_amount=$this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id,$due_days);
	                }else{
	                    $due_fine_amount=0;
	                }
	                $fee_fine_amount       = $due_fine_amount;
	
	            }else if(isset($result->fine_type) && ($result->fine_type=='fix' || $result->fine_type=='percentage')){
	                $fee_fine_amount       = isset($result->fine_amount) ? $result->fine_amount : 0;
	            }
	        // get cumulative fine amount as delay days
	        }
	
	        if (isset($result->is_system) && $result->is_system) {
	            $due_amt = isset($result->student_fees_master_amount) ? $result->student_fees_master_amount : 0;
	        }
	
	        $amount_detail = json_decode(isset($result->amount_detail) ? $result->amount_detail : '[]');
	        if (is_object($amount_detail) || is_array($amount_detail)) {
	
	            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
	                $amount          = $amount + (isset($amount_detail_value->amount) ? $amount_detail_value->amount : 0);
	                $amount_discount = $amount_discount + (isset($amount_detail_value->amount_discount) ? $amount_detail_value->amount_discount : 0);
	                $amount_fine     = $amount_fine + (isset($amount_detail_value->amount_fine) ? $amount_detail_value->amount_fine : 0);
	            }
	        }
	
	        // Check for $result->type before using it
	        if (isset($result->type) && $result->type === "Advance Payments") {
	            if (!isset($result->student_session_id)) {
	                log_message('error', 'getStuFeetypeBalance: student_session_id not found in result for Advance Payments type. Result: ' . json_encode($result));
	                return json_encode(array('status' => 'fail', 'error' => 'Student session ID missing for Advance Payments.'));
	            }
	            log_message('debug', 'getStuFeetypeBalance: Calling get_advance_balance for student_session_id: ' . $result->student_session_id);
	            $advance_balances = $this->studentfeemaster_model->get_advance_balance($result->student_session_id);
	            log_message('debug', 'getStuFeetypeBalance: get_advance_balance returned: ' . json_encode($advance_balances));
	            $amount_balance = $advance_balances['paid_advance_balance'] + $advance_balances['discount_advance_balance'];
	        } else {
	            $amount_balance = $due_amt - ($amount + $amount_discount);
	        }        $fine_amount    = ($fee_fine_amount > 0) ? ($fee_fine_amount - $amount_fine) : 0;
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

    public function addfeegroupbulk()
    {
        $this->load->helper('custom');
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
        } else {
            $row_counters = $this->input->post('row_counter');
            $bulk_data = array();
            $collected_by = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
            
            $use_advance = $this->input->post('use_advance');
            if ($use_advance == 'yes') {
                $payment_mode = 'Advance';
            } else {
                $payment_mode = $this->input->post('payment_mode_fee');
            }

            $description = $this->input->post('fee_gupcollected_note');
            $collected_date = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('collected_date')));
            
            // Get the total amounts paid by the user
            $total_amount_paid = $this->input->post('amount') ? convertCurrencyFormatToBaseAmount($this->input->post('amount')) : 0;
            $total_discount_paid = $this->input->post('amount_discount') ? convertCurrencyFormatToBaseAmount($this->input->post('amount_discount')) : 0;
            $total_fine_paid = $this->input->post('amount_fine') ? convertCurrencyFormatToBaseAmount($this->input->post('amount_fine')) : 0;

            $discounts = $this->input->post('fee_discount_group');
            if(!isset($discounts)){
                $discounts=[];
            }

            // Initialize distributable amounts
            $distributable_payment = $total_amount_paid;
            $distributable_discount = $total_discount_paid;
            $distributable_fine = $total_fine_paid;

            foreach ($row_counters as $row_count) {
                // Get the balance and fine due for this specific fee item
                $balance_due = convertCurrencyFormatToBaseAmount($this->input->post('fee_amount_' . $row_count));
                $fine_due = convertCurrencyFormatToBaseAmount($this->input->post('fee_groups_feetype_fine_amount_' . $row_count));

                // Allocate fine first
                $fine_to_pay_for_this_item = min($fine_due, $distributable_fine);
                $distributable_fine -= $fine_to_pay_for_this_item;

                // Allocate discount
                $discount_to_apply_to_this_item = min($balance_due, $distributable_discount);
                $distributable_discount -= $discount_to_apply_to_this_item;
                
                // Calculate remaining balance for this fee after discount
                $balance_after_discount = $balance_due - $discount_to_apply_to_this_item;

                // Allocate payment
                $payment_to_apply_to_this_item = 0;
                if ($distributable_payment > 0) {
                    $payment_to_apply_to_this_item = min($balance_after_discount, $distributable_payment);
                    $distributable_payment -= $payment_to_apply_to_this_item;
                }

                // Only create a record if some payment, discount, or fine was applied
                if ($payment_to_apply_to_this_item > 0 || $discount_to_apply_to_this_item > 0 || $fine_to_pay_for_this_item > 0) {
                    $json_array = array(
                        'amount'          => $payment_to_apply_to_this_item,
                        'amount_discount' => $discount_to_apply_to_this_item,
                        'amount_fine'     => $fine_to_pay_for_this_item,
                        'date'            => $collected_date,
                        'description'     => $description,
                        'collected_by'    => $collected_by,
                        'payment_mode'    => $payment_mode,
                        'received_by'     => $staff_record['id'],
                    );

                    $data = array(
                        'fee_category'           => $this->input->post('fee_category_' . $row_count),
                        'student_fees_master_id' => $this->input->post('student_fees_master_id_' . $row_count),
                        'fee_groups_feetype_id'  => $this->input->post('fee_groups_feetype_id_' . $row_count),
                        'amount_detail'          => $json_array,
                        'student_transport_fee_id' => $this->input->post('trans_fee_id_' . $row_count)
                    );
                    $bulk_data[] = $data;
                }
            }

            $inserted_ids = $this->studentfeemaster_model->add_bulk_fee_deposit($bulk_data, $discounts);
            $student_session_id = $this->input->post('student_session_id');

            // Handle overpayment by adding to advance
            if ($distributable_payment > 0 || $distributable_discount > 0) { 
                $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                $json_array_advance = [
                    'amount'          => $distributable_payment,
                    'amount_discount' => $distributable_discount,
                    'amount_fine'     => 0,
                    'date'            => $collected_date,
                    'description'     => 'Overpayment from bulk fee collection',
                    'collected_by'    => $collected_by,
                    'payment_mode'    => $payment_mode,
                    'received_by'     => $staff_record['id'],
                ];

                $data_to_insert_advance = [
                    'fee_category'           => 'fees',
                    'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                    'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                    'amount_detail'          => $json_array_advance,
                ];
                $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], $collected_date);
            }

            if ($inserted_ids) {
                if ($use_advance == 'yes') {
                    $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                    $json_array_advance = [
                        'amount'          => -$total_amount_paid,
                        'amount_discount' => -$total_discount_paid,
                        'amount_fine'     => 0,
                        'date'            => $collected_date,
                        'description'     => 'Advance amount used for bulk fee payment',
                        'collected_by'    => $collected_by,
                        'payment_mode'    => 'Advance Adjustment',
                        'received_by'     => $staff_record['id'],
                    ];

                    $data_to_insert_advance = [
                        'fee_category'           => 'fees',
                        'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                        'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                        'amount_detail'          => $json_array_advance,
                    ];
                    $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [], $collected_date);
                }

                // send mail
                $send_to            = $this->input->post('guardian_phone');
                $email              = $this->input->post('guardian_email');
                $parent_app_key     = $this->input->post('parent_app_key');

                foreach($inserted_ids as $invoice_detail){
                    if($invoice_detail['fee_category'] == 'transport'){
                         $mailsms_array                 = $this->studenttransportfee_model->getTransportFeeMasterByStudentTransportID($invoice_detail['student_transport_fee_id']);
                        $mailsms_array->fee_group_name = $this->lang->line("transport_fees");
                        $mailsms_array->type           = $mailsms_array->month;
                        $mailsms_array->code           = "";
                    }else{
                        $mailsms_array = $this->feegrouptype_model->getFeeGroupByIDAndStudentSessionID($invoice_detail['fee_groups_feetype_id'], $student_session_id);
                    }

                    $mailsms_array->invoice            = json_encode($invoice_detail);
                    $mailsms_array->student_session_id = $student_session_id;
                    $mailsms_array->contact_no         = $send_to;
                    $mailsms_array->email              = $email;
                    $mailsms_array->parent_app_key     = $parent_app_key;
                    $mailsms_array->fee_category       = $invoice_detail['fee_category'];
                    $this->mailsmsconf->mailsms('fee_submission', $mailsms_array);
                }

                $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('success_message'));
                echo json_encode($array);
            } else {
                $array = array('status' => 0, 'error' => array('message' => $this->lang->line('error_processing_request')));
                echo json_encode($array);
            }
        }
    }

        public function apply_discount()
        {
            $this->output->set_content_type('application/json');
            if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
                echo json_encode(['status' => 'fail', 'message' => $this->lang->line('access_denied')]);
                return;
            }
    
            $this->form_validation->set_rules('discount_id', $this->lang->line('discount_type'), 'required|trim|xss_clean');
            $this->form_validation->set_rules('fee_type_to_adjust_id', $this->lang->line('fee_type'), 'required|trim|xss_clean');
    
            if ($this->form_validation->run() == false) {
                $errors = $this->form_validation->error_array();
                echo json_encode(['status' => 'fail', 'message' => implode(', ', $errors)]);
                return;
            } else {
                $discount_id = $this->input->post('discount_id');
                $fee_type_to_adjust_id = $this->input->post('fee_type_to_adjust_id');
    
                $discount = $this->feediscount_model->get($discount_id);
                if (!$discount) {
                    echo json_encode(['status' => 'fail', 'message' => $this->lang->line('invalid_discount_type')]);
                    return;
                }
                
                $fee_type_to_adjust = $this->feetype_model->get($fee_type_to_adjust_id);
                if (!$fee_type_to_adjust) {
                    echo json_encode(['status' => 'fail', 'message' => 'Invalid Fee Type to Adjust selected.']);
                    return;
                }
    
                $students_with_discount = $this->feediscount_model->getStudentsByDiscountId($discount_id);
    
                $students_affected_count = 0;
                $total_discount_applied = 0;
    
                foreach ($students_with_discount as $student_data) {
                    $student_session_id = $student_data['student_session_id'];
    
                    $available_discounts = $this->feediscount_model->getDiscountNotApplied($student_session_id);
        
                    $can_apply = false;
                    $student_fees_discount_id = null;
                    $custom_amount = null;
                    foreach($available_discounts as $ad) {
                        if ($ad->fees_discount_id == $discount_id) {
                            $can_apply = true;
                            $student_fees_discount_id = $ad->id;
                            $custom_amount = $ad->custom_amount;
                            break;
                        }
                    }
    
                    if (!$can_apply) {
                        continue;
                    }
    
                    $total_discount_amount = ($discount['amount'] == '0.00' && $custom_amount != null) ? $custom_amount : $discount['amount'];
                    $remaining_discount = $total_discount_amount;
                    $fee_found_and_adjusted = false;
    
                    if (stripos($fee_type_to_adjust['type'], 'transport') !== false) {
                        $student = $this->student_model->getByStudentSession($student_session_id);
                        $transport_fees = $this->studentfeemaster_model->getStudentTransportFeesByStudentSessionId($student_session_id, $student['route_pickup_point_id']);
                        
                        foreach ($transport_fees as $tr_fee) {
                            $fee_balance_obj = json_decode($this->getStudentTransportFeetypeBalance($tr_fee->id));
                            $fee_balance = (float) $fee_balance_obj->balance;
    
                            if ($fee_balance > 0 && $remaining_discount > 0) {
                                $amount_to_discount = min($fee_balance, $remaining_discount);
    
                                $json_array = [
                                    'amount'          => 0,
                                    'amount_discount' => $amount_to_discount,
                                    'amount_fine'     => 0,
                                    'date'            => date('Y-m-d'),
                                    'description'     => $discount['name'] . ' Discount Applied to Transport Fee',
                                    'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                    'payment_mode'    => 'Discount',
                                    'received_by'     => $this->customlib->getStaffID(),
                                ];
    
                                $fee_data = [
                                    'fee_category'           => 'transport',
                                    'student_transport_fee_id' => $tr_fee->id,
                                    'amount_detail'          => $json_array,
                                ];
                                
                                $this->studentfeemaster_model->fee_deposit($fee_data, null, [$student_fees_discount_id], date('Y-m-d'));
                                $remaining_discount -= $amount_to_discount;
                                $fee_found_and_adjusted = true;
                            }
                            if ($remaining_discount <= 0) break;
                        }
    
                    } else {
                        $student_fees = $this->studentfeemaster_model->getStudentFees($student_session_id);
                        foreach ($student_fees as $fee_group) {
                            foreach ($fee_group->fees as $fee) {
                                if ($fee->feetype_id == $fee_type_to_adjust_id) {
                                    $fee_balance_obj = json_decode($this->getStuFeetypeBalance($fee->fee_groups_feetype_id, $fee->id));
                                    $fee_balance = (float) $fee_balance_obj->balance;
    
                                    if ($fee_balance > 0 && $remaining_discount > 0) {
                                        $amount_to_discount = min($fee_balance, $remaining_discount);
    
                                        $json_array = [
                                            'amount'          => 0, 
                                            'amount_discount' => $amount_to_discount,
                                            'amount_fine'     => 0,
                                            'date'            => date('Y-m-d'),
                                            'description'     => $discount['name'] . ' Discount Applied to ' . $fee->type,
                                            'collected_by'    => $this->customlib->getAdminSessionUserName(),
                                            'payment_mode'    => 'Discount',
                                        'received_by'     => $this->customlib->getStaffID(),
                                        ];
    
                                        $fee_data = [
                                            'fee_category'           => 'fees',
                                            'student_fees_master_id' => $fee->id,
                                            'fee_groups_feetype_id'  => $fee->fee_groups_feetype_id,
                                            'amount_detail'          => $json_array,
                                        ];
                                        $this->studentfeemaster_model->fee_deposit($fee_data, null, [$student_fees_discount_id], date('Y-m-d'));
                                        $remaining_discount -= $amount_to_discount;
                                        $fee_found_and_adjusted = true;
                                    }
                                    break; 
                                }
                            }
                            if ($fee_found_and_adjusted) {
                                break;
                            }
                        }
                    }
    
                    if ($fee_found_and_adjusted) {
                        $students_affected_count++;
                        $total_discount_applied += ($total_discount_amount - $remaining_discount);
                    }
    
                    if ($remaining_discount > 0) {
                        $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                        $json_array_advance = [
                            'amount'          => 0,
                            'amount_discount' => $remaining_discount,
                            'amount_fine'     => 0,
                            'date'            => date('Y-m-d'),
                            'description'     => 'Advance from ' . $discount['name'] . ' Discount',
                            'collected_by'    => $this->customlib->getAdminSessionUserName(),
                            'payment_mode'    => 'Discount',
                            'received_by'     => $this->customlib->getStaffID(),
                        ];
    
                        $data_to_insert_advance = [
                            'fee_category'           => 'fees',
                            'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                            'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                            'amount_detail'          => $json_array_advance,
                        ];
                        $this->studentfeemaster_model->fee_deposit($data_to_insert_advance, null, [$student_fees_discount_id], date('Y-m-d'));
    
                        if (!$fee_found_and_adjusted) { 
                            $students_affected_count++; 
                        }
                        $total_discount_applied += $remaining_discount;
                    }
                }
    
                echo json_encode([
                    'status' => 'success',
                    'message' => $this->lang->line('discount_applied_successfully') . ". " . $students_affected_count . " students affected. Total discount applied: " . number_format($total_discount_applied, 2),
                ]);
            }
        }

    public function search_students_for_advance()
    {
        $this->output->set_content_type('application/json');

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            echo json_encode(['status' => 'fail', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $search_text = $this->input->post('search_text');

        $students = $this->student_model->get_students_for_advance_apply($class_id, $section_id, $search_text);

        $eligible_students = [];
        foreach ($students as $student) {
            $advance_balances = $this->studentfeemaster_model->get_advance_balance($student->student_session_id);
            $advance_balance = $advance_balances['paid_advance_balance'] + $advance_balances['discount_advance_balance'];
            if ($advance_balance > 0) {
                $student_details = $this->student_model->getByStudentSession($student->student_session_id);
                $student_details['advance'] = $advance_balance;
                $eligible_students[] = $student_details;
            }
        }

        if (empty($eligible_students)) {
            echo json_encode(['status' => 'fail', 'message' => 'No students found with an advance balance matching the criteria.']);
            return;
        }

        $data['students'] = $eligible_students;
        $data['sch_setting'] = $this->sch_setting_detail;
        $html = $this->load->view('studentfee/_advance_student_list', $data, true);

        echo json_encode(['status' => 'success', 'html' => $html]);
    }

    public function apply_bulk_advance()
    {
        $this->output->set_content_type('application/json');

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {
            echo json_encode(['status' => 'fail', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $student_session_ids = $this->input->post('student_session_ids');

        if (empty($student_session_ids) || !is_array($student_session_ids)) {
            echo json_encode(['status' => 'fail', 'message' => 'No students selected.']);
            return;
        }

        $students_processed = 0;
        $total_applied = 0;
        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $collected_by = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";
        $today = date('Y-m-d');

        foreach ($student_session_ids as $student_session_id) {
            $advance_balance = $this->studentfeemaster_model->get_advance_balance($student_session_id);

            if ($advance_balance <= 0) {
                continue;
            }

            $student_was_processed = false;
            $outstanding_fees_groups = $this->studentfeemaster_model->getStudentFees($student_session_id);

            // Prioritize fees
            $tution_fees = [];
            $other_fees = [];
            $remaining_fees = [];

            foreach ($outstanding_fees_groups as $fee_group) {
                if (!isset($fee_group->fees)) continue;
                foreach ($fee_group->fees as $fee) {
                    // Calculate balance for each fee
                    $balance_obj = json_decode($this->getStuFeetypeBalance($fee->fee_groups_feetype_id, $fee->id));
                    $fee->balance = $balance_obj->balance;

                    if ($fee->balance > 0) {
                        if (stripos($fee->type, 'Tution') !== false || stripos($fee->name, 'Tution') !== false) {
                            $tution_fees[] = $fee;
                        } elseif (stripos($fee->type, 'Other') !== false || stripos($fee->name, 'Other') !== false) {
                            $other_fees[] = $fee;
                        } else {
                            $remaining_fees[] = $fee;
                        }
                    }
                }
            }
            
            $fees_to_pay = array_merge($tution_fees, $other_fees, $remaining_fees);

            foreach ($fees_to_pay as $fee) {
                if ($advance_balance <= 0) {
                    break; // Stop if advance is depleted
                }

                $amount_to_pay = min($fee->balance, $advance_balance);

                // 1. Credit the target fee (positive deposit)
                $json_array_credit = [
                    'amount'          => $amount_to_pay,
                    'amount_discount' => 0,
                    'amount_fine'     => 0,
                    'date'            => $today,
                    'description'     => 'Advance amount adjusted',
                    'collected_by'    => $collected_by,
                    'payment_mode'    => 'Advance',
                    'received_by'     => $staff_record['id'],
                ];

                $credit_data = [
                    'fee_category'           => 'fees',
                    'student_fees_master_id' => $fee->id,
                    'fee_groups_feetype_id'  => $fee->fee_groups_feetype_id,
                    'amount_detail'          => $json_array_credit,
                ];

                $this->studentfeemaster_model->fee_deposit($credit_data, null, [], $today);

                // 2. Debit the advance fee (negative deposit)
                $advance_fee_ids = $this->studentfeemaster_model->get_or_create_advance_fee_ids($student_session_id);
                $json_array_debit = [
                    'amount'          => -$amount_to_pay,
                    'amount_discount' => 0,
                    'amount_fine'     => 0,
                    'date'            => $today,
                    'description'     => 'Used for fee: ' . $fee->type,
                    'collected_by'    => $collected_by,
                    'payment_mode'    => 'Advance Adjustment',
                    'received_by'     => $staff_record['id'],
                ];

                $debit_data = [
                    'fee_category'           => 'fees',
                    'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                    'fee_groups_feetype_id'  => $advance_fee_ids->fee_groups_feetype_id,
                    'amount_detail'          => $json_array_debit,
                ];

                $this->studentfeemaster_model->fee_deposit($debit_data, null, [], $today);

                // 3. Update balances
                $advance_balance -= $amount_to_pay;
                $total_applied += $amount_to_pay;
                $student_was_processed = true;
            }

            if ($student_was_processed) {
                $students_processed++;
            }
        }

        $message = "Process complete. " . $students_processed . " student(s) processed. A total of " . amountFormat($total_applied) . " was applied from advance payments.";
        echo json_encode(['status' => 'success', 'message' => $message]);
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