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

        try {
            // Step 2: Ecom Order
            $ecom_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'amount' => $total_amount, // Amount as a number, verify if BillDesk expects string like "100.00"
                'order_ref_no' => time() . rand(1111, 9999),
                'ecom_order_date' => date('Y-m-d\TH:i:sP'),
                'ru' => base_url('user/gateway/billdesk/callback'),
                'currency' => '356',
                'itemcode' => 'DIRECT',
                'additional_info' => [
                    'additional_info1' => 'NA', 'additional_info2' => 'NA', 'additional_info3' => 'NA',
                    'additional_info4' => 'NA', 'additional_info5' => 'NA', 'additional_info6' => 'NA',
                    'additional_info7' => 'NA',
                ],
                'split_payment' => [
                    [
                        'mercid' => 'UAT2K666C1', // Placeholder, needs actual child merchant ID
                        'amount' => $total_amount, // For simplicity, splitting total amount to first child
                        'customer_refid' => 'V2EcomTestC1ORN' . time() . rand(11,99),
                        'additional_info1' => 'NA', 'additional_info2' => 'NA', 'additional_info3' => 'NA',
                        'additional_info4' => 'NA', 'additional_info5' => 'NA', 'additional_info6' => 'NA',
                        'additional_info7' => 'NA',
                    ],
                ],
            ];

            log_message('error', 'Billdesk Ecom Order Raw Client ID: ' . $this->billdesk_lib->getClientid());
            log_message('error', 'Billdesk Ecom Order Raw JSON Payload (before JWE): ' . json_encode($ecom_payload));

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
            log_message('error', 'Billdesk Ecom Order Request Body (JWS Token): ' . $ecom_jws_token);

            $ecom_response = curl_exec($ch_ecom);
            $ecom_err = curl_error($ch_ecom);
            curl_close($ch_ecom);

            log_message('debug', 'Billdesk Ecom Order cURL Response: ' . $ecom_response);
            log_message('error', 'Billdesk Ecom Order cURL Error: ' . $ecom_err);
            if (!empty($ecom_err)) {
                log_message('error', 'Billdesk Ecom Order cURL Error (even if no exception): ' . $ecom_err);
            }
            log_message('error', 'Billdesk Ecom Order Raw Response (before verify_response): ' . $ecom_response);

            if ($ecom_err) {
                throw new Exception("cURL Error (Ecom Order): " . $ecom_err);
            }

            // Check if the response is a Billdesk API error in JSON format
            $billdesk_error = json_decode($ecom_response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($billdesk_error['status']) && $billdesk_error['status'] == 401 && isset($billdesk_error['message'])) {
                throw new Exception("Billdesk API Error: " . $billdesk_error['message'] . " (Error Code: " . (isset($billdesk_error['error_code']) ? $billdesk_error['error_code'] : 'N/A') . ")");
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
            log_message('error', 'Decrypted Ecom Order Response: ' . json_encode($decrypted_ecom_response));

            // --- Defensive coding for Ecom Order Response ---
            // Assuming 200 is success for this API. Adjust if BillDesk uses another success code or status string.
            if (isset($decrypted_ecom_response['status']) && $decrypted_ecom_response['status'] != 200) { 
                throw new Exception("Billdesk Ecom Order API Error: " . (isset($decrypted_ecom_response['message']) ? $decrypted_ecom_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_ecom_response['error_code']) ? $decrypted_ecom_response['error_code'] : 'N/A') . ")");
            }
            // --- End Defensive coding ---

            $ecom_orderid = $decrypted_ecom_response['orderid']; // This line will only be reached if status is 200

            // Step 3: Transaction Creation
            $trans_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'orderid' => $ecom_orderid,
                'amount' => $total_amount, // Amount as a number, verify if BillDesk expects string like "100.00"
                'currency' => '356',
                'itemcode' => 'DIRECT',
                'ru' => base_url('user/gateway/billdesk/callback'),
                'device' => [
                    'init_channel' => 'internet',
                    'ip' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent()
                ]
            ];
            log_message('error', 'Billdesk Transaction Creation Raw JSON Payload (before JWE): ' . json_encode($trans_payload)); // NEW LOG ADDED
            

            try {
                $trans_jwe_token = $this->billdesk_lib->create_jwe($trans_payload);
            } catch (Exception $e) {
                throw new Exception("Error creating JWE for Transaction: " . $e->getMessage());
            }

            try {
                $trans_jws_token = $this->billdesk_lib->create_jws($trans_jwe_token);
            } catch (Exception $e) {
                throw new Exception("Error creating JWS for Transaction: " . $e->getMessage());
            }
            
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
            
            log_message('error', 'Billdesk Transaction Creation Request URL: https://uat1.billdesk.com/u2/payments/ve1_2/orders/create'); // NEW LOG ADDED
            log_message('error', 'Billdesk Transaction Creation Request Headers: ' . print_r($trans_headers, true)); // NEW LOG ADDED
            log_message('error', 'Billdesk Transaction Creation Request Body (JWS Token): ' . $trans_jws_token); // NEW LOG ADDED
            
            $trans_response = curl_exec($ch_trans);
            $trans_err = curl_error($ch_trans);
            curl_close($ch_trans);

            log_message('debug', 'Billdesk Transaction Creation cURL Response: ' . $trans_response);
            log_message('error', 'Billdesk Transaction Creation cURL Error: ' . $trans_err);

            if ($trans_err) {
                throw new Exception("cURL Error (Transaction Creation): " . $trans_err);
            }
            
            try {
                $trans_response_jwe = $this->billdesk_lib->verify_response($trans_response);
            } catch (Exception $e) {
                throw new Exception("Error verifying Transaction response: " . $e->getMessage());
            }
            
            try {
                $decrypted_trans_response = $this->billdesk_lib->decrypt_response($trans_response_jwe);
            } catch (Exception $e) {
                throw new Exception("Error decrypting Transaction response: " . $e->getMessage());
            }
            log_message('error', 'Decrypted Transaction Response: ' . json_encode($decrypted_trans_response));
            
            // --- Defensive coding for Transaction Creation Response ---
            // Assuming 200 is success for this API. Adjust if BillDesk uses another success code or status string.
            if (isset($decrypted_trans_response['status']) && $decrypted_trans_response['status'] != 200) { 
                throw new Exception("Billdesk Transaction Creation API Error: " . (isset($decrypted_trans_response['message']) ? $decrypted_trans_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_trans_response['error_code']) ? $decrypted_trans_response['error_code'] : 'N/A') . ")");
            }
            // --- End Defensive coding ---

            // Step 4: Redirect to BillDesk
            $data['form_action'] = 'https://uat1.billdesk.com/u2/web/v1_2/embeddedsdk';
            $data['fields'] = [
                'bdorderid' => $decrypted_trans_response['bdorderid'],
                'merchantid' => $this->api_config->api_secret_key,
                'rdata' => $decrypted_trans_response['rdata'],
            ];

            $this->load->view('user/gateway/billdesk/redirect', $data);

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