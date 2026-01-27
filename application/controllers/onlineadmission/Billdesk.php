<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Billdesk extends CI_Controller
{

    public $api_config;
    public $sch_setting_detail;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage'); // Required by PublicAdmissionForm
        $this->load->helper('language');
        $this->load->database();
        $this->load->model('language_model');
        $this->load->model('setting_model');
        $this->sch_setting_detail = $this->setting_model->getSetting(); // Load school settings
        $this->load->model(array('frontcms_setting_model', 'complaint_Model', 'Visitors_model', 'onlinestudent_model', 'filetype_model', 'customfield_model', 'examgroupstudent_model', 'examgroup_model', 'grade_model', 'marksdivision_model', 'currency_model', 'section_model','holiday_model', 'class_model', 'category_model', 'student_model', 'Online_admission_ug_details_model', 'Online_admission_pg_details_model', 'Online_admission_lateral_details_model', 'Online_admission_references_model', 'Online_admission_nata_details_model', 'notificationsetting_model'));
        $this->load->model('examstudent_model');
        $this->load->config('form-builder');
        $this->load->config('app-config');
        $this->load->library(array('mailer', 'form_builder', 'mailsmsconf'));
        $this->blood_group = $this->config->item('bloodgroup');
        $this->load->library('Ajax_pagination');
        $this->load->library('module_lib');
        $this->load->library('captchalib');
        $this->load->library('customlib');
        $this->load->helper('customfield');
        $this->load->helper('custom');
        $this->load->library(array('enc_lib', 'cart', 'auth'));
        
        $this->load->library('gateway_ins/billdesk_lib'); // Reuse BillDesk Library
        $this->load->model('gateway_ins_model'); // Reuse Gateway_ins Model
        $this->load->model('paymentsetting_model'); // For getting active payment gateway settings

        $this->api_config = $this->paymentsetting_model->getActiveMethod(); // Get active payment gateway settings
        $this->setting = $this->setting_model->get(); // General settings

        // --- START Language Loading Logic ---
        $this->school_details = $this->setting_model->getSchoolDetail();
        $language = ($this->school_details->language);
        $this->config->set_item('language', $language);
        $this->load->helper(array('directory', 'custom'));
        $lang_array = array('form_validation_lang');
        $map        = directory_map(APPPATH . "./language/" . $language . "/app_files");
        foreach ($map as $lang_key => $lang_value) {
            $lang_array[] = 'app_files/' . str_replace(".php", "", $lang_value);
        }
        $this->load->language($lang_array, $language);
        // --- END Language Loading Logic ---
    }
    
    public function index()
    {
        $data = array();
        $data['params'] = $this->session->userdata('online_admission_payment_params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        if (empty($data['params'])) {
            $this->session->set_flashdata('error', $this->lang->line('payment_details_not_found'));
            redirect('publicadmissionform'); // Redirect back to form or an error page
        }
        
        // Immediately call pay to initiate the transaction
        $this->pay();
    }

    public function pay()
    {
        if (!empty($this->sch_setting_detail->timezone)) {
            date_default_timezone_set($this->sch_setting_detail->timezone);
        }
        
        $data['params'] = $this->session->userdata('online_admission_payment_params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        if (empty($data['params'])) {
            $this->session->set_flashdata('error', $this->lang->line('payment_details_not_found'));
            redirect('publicadmissionform');
        }

        try {
            $total_amount = $data['params']['total'];
            $formatted_amount = number_format($total_amount, 2, '.', '');
            $school_code = (!empty($this->setting[0]['dise_code'])) ? $this->setting[0]['dise_code'] : "MINERVA";

            // Step 2: Ecom Order
            $ecom_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'amount' => $formatted_amount,
                'order_ref_no' => $data['params']['reference_no'] . time() . rand(11, 99), // Using reference_no + timestamp for uniqueness
                'ru' => base_url('user/gateway/billdesk/callback'), // CONSOLIDATED CALLBACK URL
                'currency' => '356', // Assuming INR
                'itemcode' => 'DIRECT',
                'additional_info' => [
                    'additional_info1' => $data['params']['name'],
                    'additional_info2' => "NA",
                    'additional_info3' => $data['params']['guardian_phone'],
                    'additional_info4' => $data['params']['email'],
                    'additional_info5' => $formatted_amount,
                    'additional_info6' => $data['params']['item_type'], // e.g., 'online_admission_fee'
                    'additional_info7' => $data['params']['reference_no'],
                ],
            ];
            
            log_message('error', 'BILLDESK_ONLINE_ADMISSION_UAT_DATA: 1. JSON Request for ecom order: ' . json_encode($ecom_payload, JSON_PRETTY_PRINT));
            
            $ecom_jwe_token = $this->billdesk_lib->create_jwe($ecom_payload);
            $ecom_jws_token = $this->billdesk_lib->create_jws($ecom_jwe_token);

            $ecom_headers = [
                'Content-Type: application/jose',
                'Accept: application/jose',
                'BD-Traceid: ' . uniqid(),
                'BD-Timestamp: ' . date('YmdHis'),
            ];

            $ch_ecom = curl_init();
            curl_setopt($ch_ecom, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/ecomorders/create");
            curl_setopt($ch_ecom, CURLOPT_POST, 1);
            curl_setopt($ch_ecom, CURLOPT_POSTFIELDS, $ecom_jws_token);
            curl_setopt($ch_ecom, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ecom, CURLOPT_HTTPHEADER, $ecom_headers);
            $ecom_response = curl_exec($ch_ecom);
            curl_close($ch_ecom);

            if ($ecom_response === false) {
                throw new Exception("cURL Error (Ecom Order): " . curl_error($ch_ecom));
            }

            $ecom_response_jwe = $this->billdesk_lib->verify_response($ecom_response);
            $decrypted_ecom_response = $this->billdesk_lib->decrypt_response($ecom_response_jwe);

            log_message('error', 'BILLDESK_ONLINE_ADMISSION_UAT_DATA: 3. Original ecom order decoded response string: ' . json_encode($decrypted_ecom_response, JSON_PRETTY_PRINT));

            if (isset($decrypted_ecom_response['error_type']) || (isset($decrypted_ecom_response['status']) && $decrypted_ecom_response['status'] != 200 && $decrypted_ecom_response['status'] != 'PENDING')) {
                 throw new Exception("Billdesk Ecom Order API Error: " . (isset($decrypted_ecom_response['message']) ? $decrypted_ecom_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_ecom_response['error_code']) ? $decrypted_ecom_response['error_code'] : 'N/A') . ")");
            }

            $ecom_orderid = $decrypted_ecom_response['ecom_orderid'];

            // Step 3: Transaction Creation
            $trans_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'orderid' => $ecom_orderid,
                'amount' => $formatted_amount,
                'order_date' => date('Y-m-d\TH:i:sP'),
                'currency' => '356',
                'itemcode' => 'DIRECT',
                'ru' => base_url('user/gateway/billdesk/callback'), // CONSOLIDATED CALLBACK URL
                'device' => [
                    'init_channel' => 'internet',
                    'ip' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent()
                ]
            ];
            
            log_message('error', 'BILLDESK_ONLINE_ADMISSION_UAT_DATA: 4. JSON Request for create order: ' . json_encode($trans_payload, JSON_PRETTY_PRINT));

            $trans_jwe_token = $this->billdesk_lib->create_jwe($trans_payload);
            $trans_jws_token = $this->billdesk_lib->create_jws($trans_jwe_token);

            $trans_headers = [
                'Content-Type: application/jose',
                'Accept: application/jose',
                'BD-Traceid: ' . uniqid(),
                'BD-Timestamp: ' . date('YmdHis'),
            ];

            $ch_trans = curl_init();
            curl_setopt($ch_trans, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/orders/create");
            curl_setopt($ch_trans, CURLOPT_POST, 1);
            curl_setopt($ch_trans, CURLOPT_POSTFIELDS, $trans_jws_token);
            curl_setopt($ch_trans, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_trans, CURLOPT_HTTPHEADER, $trans_headers);
            $trans_response = curl_exec($ch_trans);
            curl_close($ch_trans);

            if ($trans_response === false) {
                throw new Exception("cURL Error (Transaction): " . curl_error($ch_trans));
            }

            $trans_response_jwe = $this->billdesk_lib->verify_response($trans_response);
            $decrypted_trans_response = $this->billdesk_lib->decrypt_response($trans_response_jwe);

            log_message('error', 'BILLDESK_ONLINE_ADMISSION_UAT_DATA: 6. Original decoded Create Order API response string: ' . json_encode($decrypted_trans_response, JSON_PRETTY_PRINT));

            if (isset($decrypted_trans_response['status']) && $decrypted_trans_response['status'] != 200 && $decrypted_trans_response['status'] != 'PENDING' && $decrypted_trans_response['status'] != 'ACTIVE') {
                 throw new Exception("Billdesk Transaction API Error: " . (isset($decrypted_trans_response['message']) ? $decrypted_trans_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_trans_response['error_code']) ? $decrypted_trans_response['error_code'] : 'N/A') . ") Status: " . $decrypted_trans_response['status']);
            }

            // Step 4: Prepare Redirect
            $bdorderid = isset($decrypted_trans_response['bdorderid']) ? $decrypted_trans_response['bdorderid'] : '';
            $merchantid = $this->api_config->api_secret_key;
            
            $rdata = '';
            $redirect_url = 'https://uat1.billdesk.com/u2/web/v1_2/embeddedsdk';

            if (isset($decrypted_trans_response['links'])) {
                foreach ($decrypted_trans_response['links'] as $link) {
                    if (isset($link['headers']['authorization'])) {
                         $rdata = $link['headers']['authorization'];
                    }
                    if (isset($link['parameters']['rdata'])) {
                        $rdata = $link['parameters']['rdata'];
                    }
                    if (isset($link['href'])) {
                        $redirect_url = $link['href'];
                    }
                }
            }
            
            if (empty($rdata) && isset($decrypted_trans_response['authToken'])) {
                $rdata = $decrypted_trans_response['authToken'];
            }
            
            if (empty($rdata)) {
                log_message('error', 'Warning: Could not find rdata/authToken in Transaction response for online admission.');
            }

            $data['form_action'] = $redirect_url;
            $data['fields'] = [
                'bdorderid' => $bdorderid,
                'merchantid' => $merchantid,
                'rdata' => $rdata
            ];

            // Insert into gateway_ins for tracking
            $ins_data = array(
                'unique_id' => $data['params']['online_admission_id'], // Using online_admission_id as unique identifier
                'parameter_details' => json_encode($data['params']),
                'gateway_name' => 'billdesk',
                'module' => 'online_admission', // Dedicated module name
                'payment_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
            );
            $gateway_ins_id = $this->gateway_ins_model->add_gateway_ins($ins_data);

            // Store gateway_ins_id in session for callback
            $current_params = $this->session->userdata('online_admission_payment_params');
            $current_params['gateway_ins_id'] = $gateway_ins_id;
            $this->session->set_userdata('online_admission_payment_params', $current_params);

            $this->load->view('onlineadmission/billdesk/redirect', $data);

        } catch (Exception $e) {
            $data['api_error'] = $e->getMessage();
            log_message('error', 'Billdesk Online Admission Payment Error: ' . $e->getMessage());
            $this->load->view('onlineadmission/billdesk/error', $data); // Dedicated error view
        }
    }



    public function success($response)
    {
        $data['response'] = $response;
        // Optionally retrieve online_admission_id and reference_no from session if needed in view
        $payment_params = $this->session->userdata('online_admission_payment_params');
        $data['online_admission_id'] = isset($payment_params['online_admission_id']) ? $payment_params['online_admission_id'] : '';
        $data['reference_no'] = isset($payment_params['reference_no']) ? $payment_params['reference_no'] : '';

        $this->load->view('onlineadmission/billdesk/success', $data);
    }

    public function pending($response)
    {
        $data['response'] = $response;
        $payment_params = $this->session->userdata('online_admission_payment_params');
        $data['online_admission_id'] = isset($payment_params['online_admission_id']) ? $payment_params['online_admission_id'] : '';
        $data['reference_no'] = isset($payment_params['reference_no']) ? $payment_params['reference_no'] : '';

        $this->load->view('onlineadmission/billdesk/pending', $data);
    }

    public function fail($response)
    {
        $data['response'] = $response;
        $payment_params = $this->session->userdata('online_admission_payment_params');
        $data['online_admission_id'] = isset($payment_params['online_admission_id']) ? $payment_params['online_admission_id'] : '';
        $data['reference_no'] = isset($payment_params['reference_no']) ? $payment_params['reference_no'] : '';
        
        $this->load->view('onlineadmission/billdesk/fail', $data);
    }
}
