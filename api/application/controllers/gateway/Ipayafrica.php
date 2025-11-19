<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ipayafrica extends Admin_Controller {

    var $setting;
    var $payment_method;

    public function __construct() {
        parent::__construct();

        $this->setting = $this->setting_model->get();
        $this->payment_method = $this->paymentsetting_model->get();
    }

    public function index() {
        $data = array();
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = array();
        $data['student_data'] = $this->student_model->get($data['params']['student_id']);
        $this->load->view('payment/ipayafrica/index', $data);
    }

    public function pay() {

        $this->form_validation->set_rules('phone', ('Phone'), 'trim|required');
        $this->form_validation->set_rules('email', ('Email'), 'trim|required');

        if ($this->form_validation->run() == false) {
            $data = array();
            $data['params'] = $this->session->userdata('params');
            $data['setting'] = $this->setting;
            $data['api_error'] = array();
            $data['student_data'] = $this->student_model->get($data['params']['student_id']);
            $this->load->view('payment/ipayafrica/index', $data);
        } else {

           

            $pay_method = $this->paymentsetting_model->getActiveMethod();
            if ($this->session->has_userdata('params')) {
                $session_params = $this->session->userdata('params');
                $data['session_params'] = $session_params;
            }

            $amount = $data['session_params']['total'];
      
              $fields = array("live"=> "1",
                    "oid"=> uniqid(),
                    "inv"=> time(),
                    "ttl"=> number_format((float)(convertBaseAmountCurrencyFormat($session_params['payment_detail']->fine_amount+$session_params['total'] - $session_params['applied_fee_discount']+ $session_params['gateway_processing_charge'])), 2, '.', ''),
                    "tel"=> $_POST['phone'],
                    "eml"=> $_POST['email'],
                    "vid"=> ($pay_method->api_publishable_key),
                    "curr"=> $session_params['invoice']->currency_name,
                    "p1"=> "airtel",
                    "p2"=> "",
                    "p3"=> "",
                    "p4"=> $amount,
                    "cbk"=> base_url().'gateway/ipayafrica/success',
                    "cst"=> "1",
                    "crl"=> "2"
                    );
             
            $datastring =  $fields['live'].$fields['oid'].$fields['inv'].$fields['ttl'].$fields['tel'].$fields['eml'].$fields['vid'].$fields['curr'].$fields['p1'].$fields['p2'].$fields['p3'].$fields['p4'].$fields['cbk'].$fields['cst'].$fields['crl'];

            $hashkey =($pay_method->api_secret_key);
            $generated_hash = hash_hmac('sha1',$datastring , $hashkey);
            $data['fields']=$fields;
            $data['generated_hash']=$generated_hash;
            $this->load->view('payment/ipayafrica/pay', $data);

        
        }
    }

    public function success() {
        if (!empty($_GET['status'])) {
            $purchaseId = $_GET['txncd'];;
            if ($purchaseId) {
                $params = $this->session->userdata('params');
                $ref_id = $purchaseId;
                $json_array = array(
                    'amount' => $params['total']-$params['applied_fee_discount'],
                            'date' => date('Y-m-d'),
                            'amount_discount' => $params['applied_fee_discount'],
							'processing_charge_type'=>$params['processing_charge_type'],
                            'gateway_processing_charge'=>$params['gateway_processing_charge'],
                    'amount_fine' => $params['payment_detail']->fine_amount,
                    'received_by' => '',
                    'description' => "Online fees deposit through iPayafrica TXN ID: " . $ref_id,
                    'payment_mode' => 'Ipayafrica',
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
            }
        } else {
            redirect(base_url("payment/paymentfailed"));
        }

        
    }

}
