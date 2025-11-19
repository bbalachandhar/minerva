<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payu extends Admin_Controller {

    var $setting;
    var $payment_method;

    public function __construct() {
        parent::__construct();

        $this->setting = $this->setting_model->get();
        $this->payment_method = $this->paymentsetting_model->get();
    }

    public function index() {

        $pay_method = $this->paymentsetting_model->getActiveMethod();

        if ($pay_method->payment_type == "payu") {
            $data = array();
            if ($this->session->has_userdata('params')) {
                if ($pay_method->api_secret_key != "" && $pay_method->salt != "") {

                    $data = array();
                    $session_params = $this->session->userdata('params');
                    $total=number_format((float)(convertBaseAmountCurrencyFormat($session_params['payment_detail']->fine_amount+$session_params['total'])), 2, '.', '');
                    $data['product_info'] = $session_params['payment_detail']->fee_group_name . " - " . $session_params['payment_detail']->code;
                    $data['session_params'] = $session_params;
                    // Merchant key here as provided by Payu
                    $data['MERCHANT_KEY'] = $pay_method->api_secret_key;
                    // Merchant Salt as provided by Payu
                    $SALT = $pay_method->salt;
                    // End point - change to https://secure.payu.in for LIVE mode
                    $PAYU_BASE_URL = "https://secure.payu.in";
                    $data['action'] = '';
                    $data['surl'] = site_url('gateway/payu/success');
                    $data['furl'] = site_url('gateway/payu/success');

                    $posted = array();
                    if (!empty($_POST)) {

                        foreach ($_POST as $key => $value) {

                            $posted[$key] = $value;
                        }
                    }

                    $data['posted'] = $posted;
                    $data['formError'] = 0;
                    if (empty($posted['txnid'])) {
                        // Generate random transaction id
                        $data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
                    } else {
                        $data['txnid'] = $posted['txnid'];
                    }
                    $session_params['txn_id'] = $data['txnid'];
                    $this->session->set_userdata("params", $session_params);
                    $data['hash'] = '';
// Hash Sequence
                    $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
                    if (empty($posted['hash']) && sizeof($posted) > 0) {

                        if (
                                empty($posted['key']) || empty($posted['txnid']) || empty($posted['amount']) || empty($posted['firstname']) || empty($posted['email']) || empty($posted['phone']) || empty($posted['productinfo']) || empty($posted['surl']) || empty($posted['furl']) || empty($posted['service_provider'])
                        ) {
                            $formError = 1;
                        } else {

                            $hashVarsSeq = explode('|', $hashSequence);
                            $hash_string = '';
                            foreach ($hashVarsSeq as $hash_var) {
                                $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
                                $hash_string .= '|';
                            }

                            $hash_string .= $SALT;

                            $data['hash'] = strtolower(hash('sha512', $hash_string));
                            $data['action'] = $PAYU_BASE_URL . '/_payment';
                        }

                    } elseif (!empty($posted['hash'])) {
                        $data['hash'] = $posted['hash'];
                        $data['action'] = $PAYU_BASE_URL . '/_payment';
                    }

                    $this->load->view('payment/payu/index', $data);
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Oops! Something went wrong');
            $this->load->view('payment/error');
        }
    }

    public function multipay($session_id){
         $pay_method = $this->paymentsetting_model->getActiveMethod();
$session_params = $this->session->userdata('params');
        if ($pay_method->payment_type == "payu") {
            $data = array();
            if ($this->session->has_userdata('params')) {
                if ($pay_method->api_secret_key != "" && $pay_method->salt != "") {

                    $data = array();
                    
                    $total=number_format((float)(($session_params['total']+$session_params['fine_amount_balance'])), 2, '.', '');
                    $data['product_info'] = 'Student fees';
                    $data['session_params'] = $session_params;
                    $data['params'] = $session_params;
                    // Merchant key here as provided by Payu
                    $data['MERCHANT_KEY'] = $session_params['key'];
                    // Merchant Salt as provided by Payu
                    $SALT = $pay_method->salt;
                    // End point - change to https://secure.payu.in for LIVE mode
                    $PAYU_BASE_URL = "https://secure.payu.in";
                    $data['action'] = '';
                    $data['surl'] = site_url('gateway/payu/multi_success/'.$session_id);
                    $data['furl'] = site_url('gateway/payu/success');

                    $posted = array();
                    if (!empty($_POST)) {

                        foreach ($_POST as $key => $value) {

                            $posted[$key] = $value;
                        }
                    }

                    $data['posted'] = $posted;
                    $data['formError'] = 0;
                    if (empty($posted['txnid'])) {
                        // Generate random transaction id
                        $data['txnid'] = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
                    } else {
                        $data['txnid'] = $posted['txnid'];
                    }
                    $session_params['txn_id'] = $data['txnid'];
                    $this->session->set_userdata("params", $session_params);
                    $data['hash'] = '';
// Hash Sequence
                    $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
                    if (!empty($_POST)) {

 $amount =$posted['amount'];
      $customer_name = $posted['firstname'];;
        $customer_emial = $posted['email'];
        $product_info = 'online course';
        $MERCHANT_KEY = $pay_method->api_secret_key;
        $SALT = $pay_method->salt;

        //optional udf values 
        $udf1 = '';
        $udf2 = '';
        $udf3 = '';
        $udf4 = '';
        $udf5 = '';

        $hashstring = $MERCHANT_KEY . '|' . $data['txnid'] . '|' . $amount . '|' . $product_info . '|' . $customer_name . '|' . $customer_emial . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $SALT;
        
        $hash = strtolower(hash('sha512', $hashstring));
                            $data['action'] = $PAYU_BASE_URL . '/_payment';
                      $data['hash'] = $hash;
                    } elseif (!empty($posted['hash'])) {
                        $data['hash'] = $posted['hash'];
                        $data['action'] = $PAYU_BASE_URL . '/_payment';
                    }
                   
                    $this->load->view('payment/payu/multi_pay', $data);
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Oops! Something went wrong');
            $this->load->view('payment/error');
        }
    }
public function multi_success($session_id) {
     $session_data=$this->gateway_ins_model->get_api_session($session_id);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $session_data = $this->session->userdata('params');

            if ($this->input->post('status') == "success") {
                $mihpayid = $this->input->post('mihpayid');
                $transactionid = $this->input->post('txnid');
                $txn_id = $session_data['txn_id'];

                if ($txn_id == $transactionid) {
                    $params = $session_data;
                   $bulk_fees=array();
                    
                 
                    foreach ($params['student_fees_master_array'] as $fee_key => $fee_value) {
                   
                     $json_array = array(
                        'amount'          =>  $fee_value['amount_balance'],
                        'date'            => date('Y-m-d'),
                        'amount_discount' => $params['applied_fee_discount'],
                        'processing_charge_type'=>$params['processing_charge_type'],
                        'gateway_processing_charge'=>$params['gateway_processing_charge'],
                        'amount_fine'     => $fee_value['fine_balance'],
                        'description'     => $this->lang->line('online_fees_deposit_through_razorpay_txn_id') . $payment_id,
                        'received_by'     => '',
                        'payment_mode'    => 'Razorpay',
                    );

                    $insert_fee_data = array(
                        'fee_category'=>$fee_value['fee_category'],
                        'student_transport_fee_id'=>$fee_value['student_transport_fee_id'],
                        'student_fees_master_id' => $fee_value['student_fees_master_id'],
                        'fee_groups_feetype_id'  => $fee_value['fee_groups_feetype_id'],
                        'amount_detail'          => $json_array,
                    );                 
                   $bulk_fees[]=$insert_fee_data;
                    //========
                    }

                      $response = $this->studentfeemaster_model->fee_deposit_bulk($bulk_fees, NULL);
                      
                      if($response){
                        $this->gateway_ins_model->delete_api_session($session_id);
                        redirect("payment/successinvoice", "refresh");
                    }else{
                       redirect(base_url("payment/paymentfailed")); 
                    }
                } else {
                    redirect('payment/paymentfailed', 'refresh');
                }
            } else {

                redirect('payment/paymentfailed', 'refresh');
            }
        }
    }
    public function success() {
       
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $session_data = $this->session->userdata('params');

            if ($this->input->post('status') == "success") {
                $mihpayid = $this->input->post('mihpayid');
                $transactionid = $this->input->post('txnid');
                $txn_id = $session_data['txn_id'];

                if ($txn_id == $transactionid) {
                    $params = $this->session->userdata('params');
                    $json_array = array(
                        'amount' => $params['total']-$params['applied_fee_discount'],
                        'date' => date('Y-m-d'),
                        'amount_discount' => $params['applied_fee_discount'],
						'processing_charge_type'=>$params['processing_charge_type'],
                        'gateway_processing_charge'=>$params['gateway_processing_charge'],
                        'amount_fine' => $params['payment_detail']->fine_amount,
                        'received_by' => '',
                        'description' => "Online fees deposit through PayU TXN ID: " . $txn_id . " PayU Ref ID: " . $mihpayid,
                        'payment_mode' => 'PayU',
                    );
                   if(($params['fee_category']=='transport') && !empty($params['student_transport_fee_id']) ){
                    $data = array(
                    'student_transport_fee_id' => $params['student_transport_fee_id'],
                    'amount_detail' => $json_array,
                );
                }else{
                    $data = array(
                    'student_fees_master_id' => $params['student_fees_master_id'],
                    'fee_groups_feetype_id' => $params['fee_groups_feetype_id'],
                    'amount_detail' => $json_array,
                );
                }
                    // $send_to = $params['guardian_phone'];
                    // $inserted_id = $this->studentfeemaster_model->fee_deposit($data, $send_to, "");
					$inserted_id = $this->studentfeemaster_model->fee_deposit($data, $params['fee_discount_group']);

                    if ($inserted_id) {
                        $invoice_detail = json_decode($inserted_id);
                        redirect("payment/successinvoice/" . $invoice_detail->invoice_id . "/" . $invoice_detail->sub_invoice_id, "refresh");
                    } else {
                        
                    }
                } else {
                    redirect('payment/paymentfailed', 'refresh');
                }
            } else {

                redirect('payment/paymentfailed', 'refresh');
            }
        }
    }

}
