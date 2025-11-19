<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment extends Admin_Controller
{

    public $payment_method;
    public $school_name;
    public $school_setting;
    public $setting;

    public function __construct()
    {
        parent::__construct();
        $this->payment_method = $this->paymentsetting_model->getActiveMethod();
        $this->setting            = $this->setting_model->get();
        $this->std_fine           = 0;
        $this->sch_setting_detail = $this->setting_model->getSetting();

        $this->load->model(array('auth_model','gateway_ins_model'));
        $this->load->library('Customlib');
    }

    public function index($student_fees_master_id, $fee_groups_feetype_id, $student_id, $student_transport_fee_id = null)
    {      
        $this->session->unset_userdata("params");

        if (!empty($this->payment_method)) {

            $data                           = array();

            $student_record = $this->student_model->get($student_id);

            $page                = new stdClass();
            if (!empty($student_record->currency_id)) {
                $page->symbol        = $student_record->symbol;
                $page->currency_name = $student_record->currency_name;
                $student_currency['currency_base_price'] = $student_record->base_price;
                $student_currency['currency_symbol'] = $student_record->symbol;
            } else {
                $page->symbol        = $this->setting[0]['currency_symbol'];
                $page->currency_name = $this->setting[0]['short_name'];
                $student_currency['currency_base_price'] = $this->setting[0]['base_price'];
                $student_currency['currency_symbol'] = $this->setting[0]['currency_symbol'];
            }

            $this->session->set_userdata("student", $student_currency);
            $this->session->set_userdata('student_currency', array('currency_name' => $page->currency_name, 'currency_base_price' => $student_currency['currency_base_price'], 'currency_symbol' => $student_currency['currency_symbol']));
            $pay_method     = $this->paymentsetting_model->getActiveMethod();

            if ($student_transport_fee_id !== null && ($student_fees_master_id == 0) && ($fee_groups_feetype_id == 0)) {
                $payment_details = (object)array();
                $result = $this->studentfeemaster_model->studentTRansportDeposit($student_transport_fee_id);

                $fee_category             = "transport";
                $student_transport_fee_id = $student_transport_fee_id;
                $fee_group_name        = ("Transport Fees");
                $fee_type_code          = $result->month;
                $payment_details->fee_group_name = $fee_group_name;
                $payment_details->code = $result->month;
				$amount_balance = $result->fees;
				
				 
                $payment_details->fine_amount = $result->fine_amount;
				
				
                
            } else {
                $data['fee_groups_feetype_id']  = $fee_groups_feetype_id;
                $data['student_fees_master_id'] = $student_fees_master_id;
                $result                         = $this->studentfeemaster_model->studentDeposit($data);

                $amount_balance                 = 0;
                $amount                         = 0;
                $amount_fine                    = 0;
                $amount_discount                = 0;
                $amount_detail                  = json_decode($result->amount_detail);
                $totalamount_fine               = 0;
                if (strtotime($result->due_date) < strtotime(date('Y-m-d'))) {
                    $totalamount_fine = $result->fine_amount;
                }

                if (is_object($amount_detail)) {
                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                        $amount          = $amount + $amount_detail_value->amount;
                        $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                        $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
                    }
                }

                $amount_balance = $result->amount - ($amount + $amount_discount);
                if ($result->is_system) {

                    $amount_balance = $result->student_fees_master_amount - ($amount + $amount_discount);
                }
                $totalamount_fine = $totalamount_fine - $amount_fine;
                $fee_category             = "fees";
                $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                $payment_details->fine_amount = $totalamount_fine;
            }
                $fee_discount_group=array();
                $final_discount_amount=0;
                if(!empty($fee_discount_group)){ 
                        $get_discount = $this->studentfeemaster_model->get_discount_amount($fee_discount_group);//addedd 
                        foreach($get_discount as $key=>$value){
                            if($value['type'] == "fix") {
                                $final_discount_amount += $value['amount'];
                            }else if($value['type']  == "percentage") {
                                $per_amount=(($amount_balance * $value['percentage'])/100);
                                $final_discount_amount += $per_amount;
                            }
                        }
                    }
                   $gateway_processing_charge=0;
                    if($pay_method->charge_type=='percentage'){
                    $gateway_processing_charge=(($amount_balance * $pay_method->charge_value)/100);
                    }elseif($pay_method->charge_type=='fix'){
                        $gateway_processing_charge=$pay_method->charge_value;
                    }else{
                     $gateway_processing_charge=0;   
                    }  

            $params              = array(
                'key'                    => $pay_method->api_secret_key,
                'api_publishable_key'    => $pay_method->api_publishable_key,
                'invoice'                => $page,
                'total'                  => $amount_balance,
                'student_session_id'     => $student_record->student_session_id,
                'applied_fee_discount'      => ($final_discount_amount),
                'email'                  => $student_record->email,
                'guardian_phone'         => $student_record->guardian_phone,
                'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                'student_transport_fee_id' => $student_transport_fee_id,
                'student_fees_master_id' => $student_fees_master_id,
                'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                'student_id'             => $student_id,
                'payment_detail'         => $payment_details,
                'fee_category' => $fee_category,
                'processing_charge_type'=>$pay_method->charge_type,
                'gateway_processing_charge'=>$gateway_processing_charge
            );

            $this->session->set_userdata("params", $params);
            if ($pay_method->payment_type == "stripe") {
                if ($pay_method->api_secret_key == "" || $pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Stripe settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/stripe", 'refresh');
                }
            } else if ($pay_method->payment_type == "payu") {

                redirect("gateway/payu", 'refresh');
            } else if ($pay_method->payment_type == "paypal") {

                if ($pay_method->api_username == "" || $pay_method->api_password == "" || $pay_method->api_signature == "") {

                    $this->session->set_flashdata('error', 'Paypal settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/paypal", 'refresh');
                }
            } else if ($pay_method->payment_type == "instamojo") {

                if ($pay_method->api_secret_key == "" || $pay_method->salt == "" || $pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Instamojo settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Instamojo", 'refresh');
                }
            } else if ($pay_method->payment_type == "razorpay") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Razorpay settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Razorpay", 'refresh');
                }
            } else if ($pay_method->payment_type == "paystack") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Paystack settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Paystack", 'refresh');
                }
            } else if ($pay_method->payment_type == "paytm") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'paytm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Paytm", 'refresh');
                }
            } else if ($pay_method->payment_type == "midtrans") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'midtrans settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/midtrans", 'refresh');
                }
            } else if ($pay_method->payment_type == "pesapal") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'pesapal settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/pesapal", 'refresh');
                }
            } else if ($pay_method->payment_type == "flutterwave") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Flutterwave settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/flutterwave", 'refresh');
                }
            } else if ($pay_method->payment_type == "ipayafrica") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'iPayAfrica settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/ipayafrica", 'refresh');
                }
            } else if ($pay_method->payment_type == "jazzcash") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Jazzcash settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/jazzcash", 'refresh');
                }
            } else if ($pay_method->payment_type == "billplz") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Billplz settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/billplz", 'refresh');
                }
            } else if ($pay_method->payment_type == "ccavenue") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'CCAvenue settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/ccavenue", 'refresh');
                }
            } else if ($pay_method->payment_type == "sslcommerz") {

                if ($pay_method->api_password == "") {

                    $this->session->set_flashdata('error', 'Sslcommerz settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/sslcommerz", 'refresh');
                }
            } else if ($pay_method->payment_type == "walkingm") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/walkingm", 'refresh');
                }
            } else if ($pay_method->payment_type == "mollie") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Mollie settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/mollie", 'refresh');
                }
            } else if ($pay_method->payment_type == "cashfree") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Cashfree settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/cashfree", 'refresh');
                }
            } else if ($pay_method->payment_type == "payfast") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Payfast settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/payfast", 'refresh');
                }
            } else if ($pay_method->payment_type == "toyyibpay") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Toyyibpay settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/toyyibpay", 'refresh');
                }
            } else if ($pay_method->payment_type == "twocheckout") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Twocheckout settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/twocheckout", 'refresh');
                }
            } else if ($pay_method->payment_type == "skrill") {

                if ($pay_method->api_email == "") {

                    $this->session->set_flashdata('error', 'Skrill settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/skrill", 'refresh');
                }
            } else if ($pay_method->payment_type == "payhere") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Payhere settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/payhere", 'refresh');
                }
            } else if ($pay_method->payment_type == "onepay") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Onepay settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/onepay", 'refresh');
                }
            } else {
                $this->session->set_flashdata('error', 'Oops! An error occurred with this payment, Please contact to administrator');
                $this->load->view('payment/error');
            }
        }
    }

  public function paymentrequest()
    {
        $method = $this->input->server('REQUEST_METHOD');
		$fee_discount_group=array();
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params_request = json_decode(file_get_contents('php://input'), true);
                    if(!empty($params_request['student_fees_master_id'])){
                        $student_fees_master_id=$params_request['student_fees_master_id'];
                    }else{
                        $student_fees_master_id=0;
                    }
                     if(!empty($params_request['fee_groups_feetype_id'])){
                         $fee_groups_feetype_id=$params_request['fee_groups_feetype_id'];
                    }else{
                        $fee_groups_feetype_id=0;
                    }
                     if(!empty($params_request['student_transport_fee_id'])){
                       $student_transport_fee_id=$params_request['student_transport_fee_id']; 
                    }else{
                        $student_transport_fee_id=0;
                    }
                    
                   
                    $student_id=$params_request['student_id'];
                    
                    $fee_discount_group=$params_request['fee_discount_group'];
                     $this->session->unset_userdata("params");

        if (!empty($this->payment_method)) {

            $data                           = array(); 
            $student_record = $this->student_model->get($student_id);

            $page                = new stdClass();
            if (!empty($student_record->currency_id)) {
                $page->symbol        = $student_record->symbol;
                $page->currency_name = $student_record->currency_name;
                $student_currency['currency_base_price'] = $student_record->base_price;
                $student_currency['currency_symbol'] = $student_record->symbol;
            } else {
                $page->symbol        = $this->setting[0]['currency_symbol'];
                $page->currency_name = $this->setting[0]['short_name'];
                $student_currency['currency_base_price'] = $this->setting[0]['base_price'];
                $student_currency['currency_symbol'] = $this->setting[0]['currency_symbol'];
            }

            $this->session->set_userdata("student", $student_currency);
            $this->session->set_userdata('student_currency', array('currency_name' => $page->currency_name, 'currency_base_price' => $student_currency['currency_base_price'], 'currency_symbol' => $student_currency['currency_symbol']));
            $pay_method     = $this->paymentsetting_model->getActiveMethod();

            if ($student_transport_fee_id !== null && ($student_fees_master_id == 0) && ($fee_groups_feetype_id == 0)) {
                $payment_details = (object)array();
                $result = $this->studentfeemaster_model->studentTRansportDeposit($student_transport_fee_id);

                $fee_category             = "transport";
                $student_transport_fee_id = $student_transport_fee_id;
                $fee_group_name        = ("Transport Fees");
                $fee_type_code          = $result->month;
                $payment_details->fee_group_name = $fee_group_name;
                $payment_details->code = $result->month;
                  $payment_details->fine_amount = 0;
                if (($result->due_date != "0000-00-00" && $result->due_date != null) && (strtotime($result->due_date) < strtotime(date('Y-m-d')))) {
              
                 if ($result->fine_type == "percentage") {
                                    $payment_details->fine_amount = ($result->fees * $result->fine_percentage) / 100;
                                } elseif ($result->fine_type == "fix") {
                                     $payment_details->fine_amount = $result->fine_amount;
                                }
            }
                $amount_balance = $result->fees;
            } else {
                $data['fee_groups_feetype_id']  = $fee_groups_feetype_id;
                $data['student_fees_master_id'] = $student_fees_master_id;
                $result                         = $this->studentfeemaster_model->studentDeposit($data);

                $amount_balance                 = 0;
                $amount                         = 0;
                $amount_fine                    = 0;
                $amount_discount                = 0;
                $amount_detail                  = json_decode($result->amount_detail);
                $totalamount_fine               = 0;                

                if (is_object($amount_detail)) {
                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                        $amount          = $amount + $amount_detail_value->amount;
                        $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                        $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
                    }
                }

                $amount_balance = $result->amount - ($amount + $amount_discount);
                if ($result->is_system) {

                    $amount_balance = $result->student_fees_master_amount - ($amount + $amount_discount);
                }
				
						if (($result->due_date != "0000-00-00" && $result->due_date != null) && (strtotime($result->due_date) < strtotime(date('Y-m-d'))) && $amount_balance > 0) {
                           
                            // get cumulative fine amount as delay days 
                            if ($result->fine_type == 'cumulative') {
                                $date1 = date_create("$result->due_date");
                                $date2 = date_create(date('Y-m-d'));
                                $diff = date_diff($date1, $date2);
                                $due_days = $diff->format("%a");                                

                                if ($this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id, $due_days)) {
                                    $due_fine_amount = $this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id, $due_days);
                                } else {
                                    $due_fine_amount = 0;
                                }
                                $totalamount_fine  =  $due_fine_amount;
                            } else if ($result->fine_type == 'fix' || $result->fine_type == 'percentage') {
                                $totalamount_fine   = $result->fine_amount;
                            }
                            // get cumulative fine amount as delay days
                        }
						
                $totalamount_fine = $totalamount_fine - $amount_fine;
                $fee_category             = "fees";
                $payment_details              = $this->feegrouptype_model->getFeeGroupByID($fee_groups_feetype_id);
                $payment_details->fine_amount = $totalamount_fine;
            }
              
                $final_discount_amount=0;
                if(!empty($fee_discount_group)){ 
                   // $fee_discount_group=$fee_discount_group[0];
                        $get_discount = $this->studentfeemaster_model->get_discount_amount($fee_discount_group);//addedd 
                        foreach($get_discount as $key=>$value){
                            if($value['type'] == "fix") {
                                $final_discount_amount += $value['amount'];
                            }else if($value['type']  == "percentage") {
                                $per_amount=(($amount_balance * $value['percentage'])/100);
                                $final_discount_amount += $per_amount;
                            }
                        }
                    }
                   
					$gateway_processing_charge=0;
                    if($pay_method->charge_type=='percentage'){
						$gateway_processing_charge=(($amount_balance * $pay_method->charge_value)/100);
                    }elseif($pay_method->charge_type=='fix'){
                        $gateway_processing_charge=$pay_method->charge_value;
                    }else{
						$gateway_processing_charge=0;   
                    }  

            $params              = array(
                'key'                    => $pay_method->api_secret_key,
                'api_publishable_key'    => $pay_method->api_publishable_key,
                'invoice'                => $page,
                'total'                  => $amount_balance,
                'student_session_id'     => $student_record->student_session_id,
                'applied_fee_discount'      => ($final_discount_amount),
                'email'                  => $student_record->email,
                'guardian_phone'         => $student_record->guardian_phone,
                'name'                   => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                'student_transport_fee_id' => $student_transport_fee_id,
                'student_fees_master_id' => $student_fees_master_id,
                'fee_groups_feetype_id'  => $fee_groups_feetype_id,
                'student_id'             => $student_id,
                'payment_detail'         => $payment_details,
                'fee_discount_group'=>$fee_discount_group,
                'fee_category' => $fee_category,
                'processing_charge_type'=>$pay_method->charge_type,
                'gateway_processing_charge'=>$gateway_processing_charge
            );
        
               $session_data=$this->gateway_ins_model->add_api_session(array('params'=>json_encode($params)));
                 
                $params['redirect_url']=base_url("payment/pay/".$session_data);
                    json_output($response['status'], $params);
            
        }
                }
                }
                }      
       
    }

    public function paymentfailed()
    {

        $data = array();
        $this->load->view('payment/paymentfailed', $data);
    }

    public function getSelectedFeesPay()
    {
        $method = $this->input->server('REQUEST_METHOD');
    
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params_request = json_decode(file_get_contents('php://input'), true);               
         
                    $amount_paid         = 0;
                    $amount_fine_paid    = 0;
                    $amount_discount     = 0;
                    $fee_record          = array();
                    $fees_array = array();
                $total_amount_balance=0;
                $total_fine_amount_balance=0;
                 $student_record = $this->student_model->get($params_request['student_id']);

                    foreach ($params_request['fees_data'] as $value) {
                        $fees_fine_amount   = 0;
                        $fine_amount_paid   = 0; 
                        $fine_amount_balance = 0; 
                        $fee_groups_feetype_id = $value['fee_groups_feetype_id'];
                        $fee_master_id         = $value['fee_master_id'];
                        $fee_session_group_id  = $value['fee_session_group_id'];
                        $fee_category          = $value['fee_category'];
                        $trans_fee_id          = $value['trans_fee_id'];
                         $fee_record          = array();

                    $fee_record['fee_category']             = $value['fee_category'];
                    $fee_record['student_transport_fee_id'] =$value['trans_fee_id'];

                    $fee_record['fee_groups_feetype_id']  = $value['fee_groups_feetype_id'];
                    $fee_record['student_fees_master_id'] = $value['fee_master_id'];
                                         
                        if ($fee_category == "transport") {
                        $result = $this->studentfeemaster_model->studentTRansportDeposit($trans_fee_id);
                       
                        $fee_record['fee_group_name'] = ("Transport Fees");
                        $fee_record['fee_type_code']  = $result->month;
                        $fee_record['fees_type']  =("Transport Fees");
                        $fee_record['is_system']             = 0;
                        //===========================

                        $amount_detail = json_decode($result->amount_detail);

                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_paid      = $amount_paid + $amount_detail_value->amount;
                                $amount_discount  = $amount_discount + $amount_detail_value->amount_discount;
                                $amount_fine_paid = $amount_fine_paid + $amount_detail_value->amount_fine;
                            }
                        }

                        $fees_balance = $result->fees - ($amount_paid + $amount_discount);

                        if (($result->due_date != "0000-00-00" && $result->due_date != null) && (strtotime($result->due_date) < strtotime(date('Y-m-d'))) && $fees_balance > 0) {
                            $fine_amount_balance = is_null($result->fine_percentage) ? $result->fine_amount : percentageAmount($result->fees, $result->fine_percentage);
                        }
                    } elseif ($fee_category == "fees") {

                        $result                       = $this->studentfeemaster_model->studentDeposit($fee_record);

                        $fee_record['fee_group_name'] = $result->fee_group_name;
                        
                        $fee_record['fee_type_code']  = $result->fee_type_code;
                        $fee_record['fees_type']  = $result->fees_type;
                        //===========================
                        $fee_record['is_system']             = $result->is_system;
                        $amount_detail = json_decode($result->amount_detail);

                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_paid      = $amount_paid + $amount_detail_value->amount;
                                $amount_discount  = $amount_discount + $amount_detail_value->amount_discount;
                                $amount_fine_paid = $amount_fine_paid + $amount_detail_value->amount_fine;
                            }
                        }

                        $fees_balance = $result->amount - ($amount_paid + $amount_discount);

                        if ($result->is_system) {
                            $fees_balance = $result->student_fees_master_amount - ($amount_paid + $amount_discount);
                        }

                        if (($result->due_date != "0000-00-00" && $result->due_date != null) && (strtotime($result->due_date) < strtotime(date('Y-m-d'))) && $fees_balance > 0) {
                           
                            // get cumulative fine amount as delay days 
                            if ($result->fine_type == 'cumulative') {
                                $date1 = date_create("$result->due_date");
                                $date2 = date_create(date('Y-m-d'));
                                $diff = date_diff($date1, $date2);
                                $due_days = $diff->format("%a");;

                                $fee_groups_feetype_id =  $fee_groups_feetype_id;

                                if ($this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id, $due_days)) {
                                    $due_fine_amount = $this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id, $due_days);
                                } else {
                                    $due_fine_amount = 0;
                                }
                                $fine_amount_balance  =  $due_fine_amount;
                            } else if ($result->fine_type == 'fix' || $result->fine_type == 'percentage') {
                                $fine_amount_balance   = $result->fine_amount;
                            }
                            // get cumulative fine amount as delay days
                        }
                    }
                    
                    $fee_record['fine_balance']   = ($fine_amount_balance - $amount_fine_paid);
                    $fee_record['amount_balance'] = $fees_balance;                   
                    
                    $fees_master_array[]          = $fee_record;
                    $total_fine_amount_balance += ($fine_amount_balance - $amount_fine_paid);
                    $total_amount_balance += $fees_balance;                      
                        
                    }
                   
                    $page                = new stdClass();
            if (!empty($student_record->currency_id)) {
                $page->symbol        = $student_record->symbol;
                $page->currency_name = $student_record->currency_name;
                $student_currency['currency_base_price'] = $student_record->base_price;
                $student_currency['currency_symbol'] = $student_record->symbol;
            } else {
                $page->symbol        = $this->setting[0]['currency_symbol'];
                $page->currency_name = $this->setting[0]['short_name'];
                $student_currency['currency_base_price'] = $this->setting[0]['base_price'];
                $student_currency['currency_symbol'] = $this->setting[0]['currency_symbol'];
            }
                        $gateway_processing_charge=0;
                    $pay_method= $this->payment_method;                   

                     $params = array( //payment session
                    'key'                       => $pay_method->api_secret_key,
                    'api_publishable_key'       => $pay_method->api_publishable_key,
                    'invoice'                   => $page,
                    'total'                     => ($total_amount_balance),
                    'applied_fee_discount'      => 0,
                    'student_fees_discount_id'      => null,
                    'fine_amount_balance'       => ($total_fine_amount_balance),
                    'student_session_id'        => $student_record->student_session_id,
                    'name'                      => $this->customlib->getFullName($student_record->firstname, $student_record->middlename, $student_record->lastname, $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname),
                    'guardian_phone'            => $student_record->guardian_phone,
                    'mobileno'                  => $student_record->mobileno,
                    'guardian_email'            => $student_record->guardian_email,
                    'address'                   => $student_record->permanent_address,
                    'student_fees_master_array' => $fees_master_array,
                    'student_id'                => $params_request['student_id'],
                    'processing_charge_type'=>$pay_method->charge_type,
                    'gateway_processing_charge'=>$gateway_processing_charge
                );                

                    $session_data=$this->gateway_ins_model->add_api_session(array('params'=>json_encode($params)));
                 
                $params['redirect_url']=base_url("payment/multi_pay/".$session_data);
                    json_output($response['status'], $params);
                }
            }
        }
    }
	
    public function pay($session_id){
		
        $session_data=$this->gateway_ins_model->get_api_session($session_id);          
        $params= json_decode($session_data['params'],true);
        $params['invoice']=(object)$params['invoice'];
        $params['payment_detail']=(object)$params['payment_detail'];
        $student_record = $this->student_model->get($params['student_id']);

            $page                = new stdClass();
            if (!empty($student_record->currency_id)) {
                $page->symbol        = $student_record->symbol;
                $page->currency_name = $student_record->currency_name;
                $student_currency['currency_base_price'] = $student_record->base_price;
                $student_currency['currency_symbol'] = $student_record->symbol;
            } else {
                $page->symbol        = $this->setting[0]['currency_symbol'];
                $page->currency_name = $this->setting[0]['short_name'];
                $student_currency['currency_base_price'] = $this->setting[0]['base_price'];
                $student_currency['currency_symbol'] = $this->setting[0]['currency_symbol'];
            }

            $this->session->set_userdata("student", $student_currency);
            $this->session->set_userdata("params", $params);
            $this->session->set_userdata('student_currency', array('currency_name' => $page->currency_name, 'currency_base_price' => $student_currency['currency_base_price'], 'currency_symbol' => $student_currency['currency_symbol']));
			$pay_method= $this->payment_method;
      
            if ($pay_method->payment_type == "stripe") {
                if ($pay_method->api_secret_key == "" || $pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Stripe settings not available');
                    $this->load->view('payment/error');
                } else {
                    redirect("gateway/stripe", 'refresh');
                }
            } else if ($pay_method->payment_type == "payu") {

                redirect("gateway/payu", 'refresh');
            } else if ($pay_method->payment_type == "paypal") {

                if ($pay_method->api_username == "" || $pay_method->api_password == "" || $pay_method->api_signature == "") {

                    $this->session->set_flashdata('error', 'Paypal settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/paypal", 'refresh');
                }
            } else if ($pay_method->payment_type == "instamojo") {

                if ($pay_method->api_secret_key == "" || $pay_method->salt == "" || $pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Instamojo settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Instamojo", 'refresh');
                }
            } else if ($pay_method->payment_type == "razorpay") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Razorpay settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Razorpay", 'refresh');
                }
            } else if ($pay_method->payment_type == "paystack") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Paystack settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Paystack", 'refresh');
                }
            } else if ($pay_method->payment_type == "paytm") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'paytm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/Paytm", 'refresh');
                }
            } else if ($pay_method->payment_type == "midtrans") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'midtrans settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/midtrans", 'refresh');
                }
            } else if ($pay_method->payment_type == "pesapal") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'pesapal settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/pesapal", 'refresh');
                }
            } else if ($pay_method->payment_type == "flutterwave") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Flutterwave settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/flutterwave", 'refresh');
                }
            } else if ($pay_method->payment_type == "ipayafrica") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'iPayAfrica settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/ipayafrica", 'refresh');
                }
            } else if ($pay_method->payment_type == "jazzcash") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'iPayAfrica settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/jazzcash", 'refresh');
                }
            } else if ($pay_method->payment_type == "billplz") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Billplz settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/billplz", 'refresh');
                }
            } else if ($pay_method->payment_type == "ccavenue") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'CCAvenue settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/ccavenue", 'refresh');
                }
            } else if ($pay_method->payment_type == "sslcommerz") {

                if ($pay_method->api_password == "") {

                    $this->session->set_flashdata('error', 'Sslcommerz settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/sslcommerz", 'refresh');
                }
            } else if ($pay_method->payment_type == "walkingm") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/walkingm", 'refresh');
                }
            } else if ($pay_method->payment_type == "mollie") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/mollie", 'refresh');
                }
            } else if ($pay_method->payment_type == "cashfree") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/cashfree", 'refresh');
                }
            } else if ($pay_method->payment_type == "payfast") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/payfast", 'refresh');
                }
            } else if ($pay_method->payment_type == "toyyibpay") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Toyyibpay settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/toyyibpay", 'refresh');
                }
            } else if ($pay_method->payment_type == "twocheckout") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/twocheckout", 'refresh');
                }
            } else if ($pay_method->payment_type == "skrill") {

                if ($pay_method->api_email == "") {

                    $this->session->set_flashdata('error', 'Walkingm settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/skrill", 'refresh');
                }
            } else if ($pay_method->payment_type == "payhere") {

                if ($pay_method->api_secret_key == "") {

                    $this->session->set_flashdata('error', 'Payhere settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/payhere", 'refresh');
                }
            } else if ($pay_method->payment_type == "onepay") {

                if ($pay_method->api_publishable_key == "") {

                    $this->session->set_flashdata('error', 'Onepay settings not available');
                    $this->load->view('payment/error');
                } else {

                    redirect("gateway/onepay", 'refresh');
                }
            } else {
                $this->session->set_flashdata('error', 'Oops! An error occurred with this payment, Please contact to administrator');
                $this->load->view('payment/error');
            }
    }    
    
    public function multi_pay($session_id){
		
        $session_data=$this->gateway_ins_model->get_api_session($session_id);
             
        $this->session->set_userdata("params", json_decode($session_data['params'],true));  
         
        $pay_method= $this->payment_method;

        if ($pay_method->payment_type == "razorpay") {
                    if ($pay_method->api_secret_key == "" || $pay_method->api_publishable_key == "") {
                        $this->session->set_flashdata('error', '<div class="alert alert-danger">' . $this->lang->line('razorpay_settings_not_available') . '</div>');
                        redirect($_SERVER['HTTP_REFERER']);
                    } else {
                        
                        redirect(base_url("gateway/razorpay/multipay/".$session_id));
                    }
                } else if ($pay_method->payment_type == "payu") {

                redirect("gateway/payu/multipay/".$session_id, 'refresh');
            }  else {
                    $this->session->set_flashdata('error', $this->lang->line('something_went_wrong'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
    }

    public function successinvoice($invoice_id=NULL, $sub_invoice_id=NULL)
    {
        $data                = array();
        // $data['title']       = 'Invoice';
        // $setting_result      = $this->setting_model->get();
        // $data['settinglist'] = $setting_result;
        // $studentfee          = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);

        // $a                         = json_decode($studentfee->amount_detail);
        // $record                    = $a->{$sub_invoice_id};
        // $data['studentfee']        = $studentfee;
        // $data['studentfee_detail'] = $record;

        $this->load->view('payment/invoice', $data);
    }
}
