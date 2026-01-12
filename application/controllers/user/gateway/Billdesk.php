<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Billdesk extends Student_Controller
{

    public $api_config;

    public function __construct()
    {
        parent::__construct();
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->get();
        $this->load->library('gateway_ins/billdesk_lib');
    }

    public function index()
    {

        $data = array();
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';
        $this->load->view('user/gateway/billdesk/index', $data);
    }

    public function pay()
    {
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/index');
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        $total_amount = ($data['params']['fine_amount_balance'] + $data['params']['total']) - $data['params']['applied_fee_discount'] + $data['params']['gateway_processing_charge'];
        $formatted_amount = number_format($total_amount, 2, '.', '');

        try {
            // Step 2: Ecom Order
            $ecom_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'amount' => $formatted_amount,
                'order_ref_no' => time() . rand(1111, 9999),
                'ecom_order_date' => date('Y-m-d\TH:i:sP'),
                'ru' => base_url('user/gateway/billdesk/callback'),
                'currency' => '356',
                'itemcode' => 'DIRECT',
                'additional_info' => [
                    'additional_info1' => 'NA',
                    'additional_info2' => 'NA',
                    'additional_info3' => 'NA',
                    'additional_info4' => 'NA',
                    'additional_info5' => 'NA',
                    'additional_info6' => 'NA',
                    'additional_info7' => 'NA',
                ],
                'split_payment' => [
                    [
                        'mercid' => 'UAT2K800C1',
                        'amount' => $formatted_amount,
                        'customer_refid' => 'V2EcomTestC1ORN' . time() . rand(11,99),
                        'additional_info1' => 'NA',
                        'additional_info2' => 'NA',
                        'additional_info3' => 'NA',
                        'additional_info4' => 'NA',
                        'additional_info5' => 'NA',
                        'additional_info6' => 'NA',
                        'additional_info7' => 'NA',
                    ]
                ],
            ];

            log_message('error', '--- ECOM PAYLOAD (Base64 Encoded) ---');
            log_message('error', 'DECODE THIS STRING TO SEE THE FULL PAYLOAD: ' . base64_encode(json_encode($ecom_payload)));

            try {
                $ecom_jwe_token = $this->billdesk_lib->create_jwe($ecom_payload);
            } catch (Exception $e) {
                throw new Exception("Error creating JWE for Ecom Order: " . $e->getMessage());
            }

            try {
                $ecom_jws_token = $this->billdesk_lib->create_jws($ecom_jwe_token);
            } catch (Exception $e) {
                throw new Exception("Error creating JWS for Ecom Order: " . $e->getMessage());
            }

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

            log_message('error', 'Billdesk Ecom Order Request URL: https://uat1.billdesk.com/u2/payments/ve1_2/ecomorders/create');
            log_message('error', 'Billdesk Ecom Order Request Headers: ' . print_r($ecom_headers, true));

            $ecom_response = curl_exec($ch_ecom);
            $ecom_err = curl_error($ch_ecom);
            curl_close($ch_ecom);

            if ($ecom_err) {
                throw new Exception("cURL Error (Ecom Order): " . $ecom_err);
            }

            try {
                $ecom_response_jwe = $this->billdesk_lib->verify_response($ecom_response);
            } catch (Exception $e) {
                throw new Exception("Error verifying Ecom Order response: " . $e->getMessage());
            }

            try {
                $decrypted_ecom_response = $this->billdesk_lib->decrypt_response($ecom_response_jwe);
            } catch (Exception $e) {
                throw new Exception("Error decrypting Ecom Order response: " . $e->getMessage());
            }
            log_message('error', '--- ECOM RESPONSE (Base64 Encoded) ---');
            log_message('error', 'DECODE THIS STRING TO SEE THE FULL RESPONSE: ' . base64_encode(json_encode($decrypted_ecom_response)));

            if (isset($decrypted_ecom_response['status']) && $decrypted_ecom_response['status'] != 200) {
                throw new Exception("Billdesk Ecom Order API Error: " . (isset($decrypted_ecom_response['message']) ? $decrypted_ecom_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_ecom_response['error_code']) ? $decrypted_ecom_response['error_code'] : 'N/A') . ")");
            } else {
                $ecom_orderid = $decrypted_ecom_response['orderid'];

                // This part of the code for Step 3 will likely not be reached until the IP whitelisting issue is resolved.
                // The logic below is based on the documentation provided.
                
                $trans_payload = [
                    'mercid' => $this->api_config->api_secret_key,
                    'orderid' => $ecom_orderid,
                    'amount' => $formatted_amount,
                    'currency' => '356',
                    'itemcode' => 'DIRECT',
                    'ru' => base_url('user/gateway/billdesk/callback'),
                    'device' => [
                        'init_channel' => 'internet',
                        'ip' => $this->input->ip_address(),
                        'user_agent' => $this->input->user_agent()
                    ]
                ];
                
                log_message('error', '--- Transaction Creation Step (Not reached due to prior error) ---');
                log_message('error', 'DECODE THIS STRING TO SEE THE FULL TRANSACTION PAYLOAD: ' . base64_encode(json_encode($trans_payload)));

                $this->load->view('user/gateway/billdesk/redirect', $data);
            }

        } catch (Exception $e) {
            $data['api_error'] = $e->getMessage();
            $this->load->view('user/gateway/billdesk/error', $data);
        }
    }

    public function callback()
    {
        if (!empty($_POST['transaction_response'])) {
            try {
                $jws_token = $_POST['transaction_response'];
                $jwe_token = $this->billdesk_lib->verify_response($jws_token);
                $response = $this->billdesk_lib->decrypt_response($jwe_token);

                if (isset($response['auth_status']) && $response['auth_status'] == '0300') {
                    // Payment successful
                    $this->success($response);
                } else {
                    // Payment failed
                    $this->fail($response);
                }
            } catch (Exception $e) {
                // Invalid response
                $this->fail(['transaction_error_desc' => $e->getMessage()]);
            }
        } else {
            // Invalid response
            $this->fail(['transaction_error_desc' => 'Invalid response from payment gateway']);
        }
    }

    public function success($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/success', $data);
    }

    public function fail($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/fail', $data);
    }
}
