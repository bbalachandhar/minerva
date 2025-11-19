<?php

defined('BASEPATH') or exit('No direct script access allowed');
//require_once APPPATH . 'third_party/stripe/init.php';

class Stripe extends Admin_Controller {

    var $setting;
    var $payment_method;

    public function __construct() {
        parent::__construct();

        $this->setting = $this->setting_model->get();
        $this->payment_method = $this->paymentsetting_model->get();
        $this->load->library('stripe_payment');
    }


    public function create_payment_intent()
    {
        // $params                        = $this->session->userdata('params');
        // $data                = $this->input->post();
        // $data['description'] = $this->lang->line("online_fees_deposit");
        // $data['currency']    = $params['invoice']->currency_name;

        $jsonStr = file_get_contents('php://input');
        $jsonObj = json_decode($jsonStr);
        
        $this->stripe_payment->PaymentIntent($jsonObj );
    }

    public function create_customer()
    {
        $jsonStr = file_get_contents('php://input');
        $jsonObj = json_decode($jsonStr);

        $user_detail = $this->session->userdata('params');
         $jsonObj = new stdClass();
        $jsonObj->fullname = $user_detail['name'];
        $jsonObj->email = $user_detail['email'];
       
        $this->stripe_payment->AddCustomer($jsonObj);
    }

     public function insert_payment()
    {

        $jsonStr = file_get_contents('php://input');
        $jsonObj = json_decode($jsonStr);
        $return_response = $this->stripe_payment->InsertTransaction($jsonObj);
        if ($return_response['status']) {
            $payment = $return_response['payment'];
            // If transaction was successful
            if (!empty($payment) && $payment->status == 'succeeded') {
                $params              = $this->session->userdata('params');
                $data                =[];
                $data['description'] = $this->lang->line("online_fees_deposit");
                $data['currency']    = $params['invoice']->currency_name;
                // Retrieve transaction details
                $transaction_id = $payment->id;

                //=====================================


                $payment_data['transactionid'] = $transaction_id;
                $params = $this->session->userdata('params');
                $ref_id = $transaction_id;
                $json_array = array(
                    'amount' => $params['total']-$params['applied_fee_discount'],
                    'date' => date('Y-m-d'),
                    'amount_discount' => $params['applied_fee_discount'],
					'processing_charge_type'=>$params['processing_charge_type'],
                    'gateway_processing_charge'=>$params['gateway_processing_charge'],
                    'amount_fine' => $params['payment_detail']->fine_amount,
                    'received_by' => '',
                    'description' => "Online fees deposit through Stripe TXN ID: " . $ref_id,
                    'payment_mode' => 'Stripe',
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

                //=============================
          
                    echo json_encode(['status'=>1,'msg' => 'Transaction successful.','return_url'=>base_url("payment/successinvoice")]);

                //=====================================



            } else {
                http_response_code(500);
                echo json_encode(['status'=>0,'msg' => 'Transaction has been failed!','return_url'=>base_url('payment/paymentfailed')]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['status'=>0,'msg' => $return_response['error']]);
        }
    }

    public function index() {
        $error= array();
        $data = array();
         $session_params = $this->session->userdata('params');

        $pay_method = $this->paymentsetting_model->getActiveMethod();
        if ($pay_method->payment_type == "stripe") {
            $data = array();
            if ($this->session->has_userdata('params')) {
                if ($pay_method->api_secret_key != "" && $pay_method->api_publishable_key != "") {
                   
                    $total=number_format((float)(convertBaseAmountCurrencyFormat($session_params['payment_detail']->fine_amount+$session_params['total'])), 2, '.', '');
                    $data['setting'] = $this->setting;

                    $data['session_params'] = $session_params;
                     $data['session_params']['api_publishable_key'] = $pay_method->api_publishable_key;
                   
                    $this->load->view('payment/stripe/pay', $data);
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Oops! Something went wrong');
            $this->load->view('payment/error');
        }
    }

}
