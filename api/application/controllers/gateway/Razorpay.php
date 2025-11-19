<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Razorpay extends Admin_Controller {

    var $setting;
    var $payment_method;

    public function __construct() {
        parent::__construct();

        $this->setting = $this->setting_model->get();
        $this->payment_method = $this->paymentsetting_model->get();
         $this->load->model(array('gateway_ins_model'));
    }

    public function index() {

        $razorpay = $this->paymentsetting_model->getActiveMethod();
        $pay_method = $this->paymentsetting_model->getActiveMethod();
        $data['setting'] = $this->setting;
        $data['api_error'] = array();
        if ($this->session->has_userdata('params')) {
            $session_params = $this->session->userdata('params');
        } 
       
       
		$total =number_format((float)($session_params['payment_detail']->fine_amount+$session_params['total'] - $session_params['applied_fee_discount']+ $session_params['gateway_processing_charge']), 2, '.', '');
		 
		 
        $data['params'] = $session_params;
        $amount = $session_params['total'];
        $data['name'] = $session_params['name'];
        $data['merchant_order_id'] = time() . "01";
        $data['txnid'] = time() . "02";
        $data['title'] = 'Student Fee';
        $data['total'] = $total * 100;
        $data['amount'] = $total;
        $data['key_id'] = $pay_method->api_publishable_key;
        $data['currency_code'] = $session_params['invoice']->currency_name;
        $this->load->view('payment/razorpay/razorpay', $data);
    }

    public function multipay($id) {

        $razorpay = $this->paymentsetting_model->getActiveMethod();
        $pay_method = $this->paymentsetting_model->getActiveMethod();
        $data['setting'] = $this->setting;
        $data['api_error'] = array();
        if ($this->session->has_userdata('params')) {
            $session_params = $this->session->userdata('params');
        } 
      
        $total=number_format((float)($session_params['total']+$session_params['fine_amount_balance']), 2, '.', '');
        $data['params'] = $session_params;
        $amount = $session_params['total'];
        $data['name'] = $session_params['name'];
        $data['merchant_order_id'] = time() . "01";
        $data['txnid'] = time() . "02";
        $data['title'] = 'Student Fee';
        $data['total'] = $total * 100;
        $data['amount'] = $total;
        $data['key_id'] = $pay_method->api_publishable_key;
        $data['currency_code'] = $session_params['invoice']['currency_name'];
        $data['id']=$id;
        $this->load->view('payment/razorpay/razorpay_multi', $data);
    }

    public function callback() {


$params = $this->session->userdata('params');
     
        if (isset($_POST['razorpay_payment_id']) && $_POST['razorpay_payment_id'] != '') {
            
          
            $payment_id = $_POST['razorpay_payment_id'];
            $json_array = array(
                'amount' => $params['total']-$params['applied_fee_discount'],
                'date' => date('Y-m-d'),
                'amount_discount' => $params['applied_fee_discount'],
				'processing_charge_type'=>$params['processing_charge_type'],
                'gateway_processing_charge'=>$params['gateway_processing_charge'],
                'amount_fine' => $params['payment_detail']->fine_amount,
                'description' => "Online fees deposit through Razorpay TXN ID: " . $payment_id,
                'received_by' => '',
                'payment_mode' => 'Razorpay',
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
          
            
			$inserted_id = $this->studentfeemaster_model->fee_deposit($data, $params['fee_discount_group']);
			
            $invoice_detail = json_decode($inserted_id);


            redirect("payment/successinvoice/" . $invoice_detail->invoice_id . "/" . $invoice_detail->sub_invoice_id, "refresh");
        } else {
            redirect(base_url("payment/paymentfailed"));
        }
    }

    public function multi_callback($session_id){
       $session_data=$this->gateway_ins_model->get_api_session($session_id);
    
     $params=json_decode($session_data['params'],true);
     
        if (isset($_POST['razorpay_payment_id']) && $_POST['razorpay_payment_id'] != '') {
            
          
                    $payment_id = $_POST['razorpay_payment_id'];
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
                  }
    }

}
