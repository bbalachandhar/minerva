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
        $this->load->library('mailsmsconf');
        $this->load->model('studentfeemaster_model');
        $this->load->model('studenttransportfee_model');
        $this->load->model('feegrouptype_model');
        $this->load->model('gateway_ins_model');
        $this->load->model('student_model');
        $this->load->model('onlinestudent_model'); // NEW: For online admission updates
        $this->school_details = $this->setting_model->getSchoolDetail(); // Initialize school_details
    }

    public function index()
    {

        $data = array();
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';
        $this->load->view('user/gateway/billdesk/index', $data);
    }

    private function get_sub_merchant_id($fee_groups_feetype_id) {
        $sql = "SELECT ft.sub_merchant_id FROM feetype ft JOIN fee_groups_feetype fgf ON fgf.feetype_id = ft.id WHERE fgf.id = ?";
        $query = $this->db->query($sql, array($fee_groups_feetype_id));
        $result = $query->row();
        return ($result && !empty($result->sub_merchant_id)) ? $result->sub_merchant_id : null;
    }

    private function _get_fallback_sub_merchant_id() {
        $query = $this->db->select('sub_merchant_id')
                        ->from('feetype')
                        ->where('sub_merchant_id IS NOT NULL')
                        ->where('sub_merchant_id !=', '')
                        ->limit(1)
                        ->get();
        
        if ($query->num_rows() > 0) {
            return $query->row()->sub_merchant_id;
        }
        
        return null; 
    }

    public function pay()
    {
        if (!empty($this->school_details->timezone)) {
            date_default_timezone_set($this->school_details->timezone);
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/index');
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        try {
            // Calculate Totals and Prepare Split Payment Logic
            $total_amount = ($data['params']['fine_amount_balance'] + $data['params']['total']) - $data['params']['applied_fee_discount'] + $data['params']['gateway_processing_charge'];
            $formatted_amount = number_format($total_amount, 2, '.', '');
            $school_code = (!empty($this->setting[0]['dise_code'])) ? $this->setting[0]['dise_code'] : "MINERVA";

            // New, correct split payment logic
            $split_payment_payload = [];
            $unmapped_amount = 0;

            // Start with gateway processing charge in unmapped
            if (isset($data['params']['gateway_processing_charge']) && $data['params']['gateway_processing_charge'] > 0) {
                $unmapped_amount += $data['params']['gateway_processing_charge'];
            }

            $fee_group_names = [];
            $fee_categories = [];
            
            if (!empty($data['params']['student_fees_master_array'])) {
                foreach ($data['params']['student_fees_master_array'] as $key => $fee) {
                    $fee_group_names[] = $fee['fee_group_name'];
                    $fee_categories[] = $fee['fee_category'];

                    $item_amount = $fee['amount_balance'] + $fee['fine_balance'];
                    
                    if (isset($fee['applied_fee_discount'])) {
                        $item_amount -= $fee['applied_fee_discount'];
                    }
                    
                    $fee_groups_feetype_id = $fee['fee_groups_feetype_id'];
                    $sub_mid = $this->get_sub_merchant_id($fee_groups_feetype_id);

                    if ($sub_mid) {
                        // Each fee type with a sub_merchant_id gets its own split payment entry.
                        $split_payment_payload[] = [
                            'mercid' => $sub_mid,
                            'amount' => number_format($item_amount, 2, '.', ''),
                            'customer_refid' => $school_code . 'ORN' . uniqid(), // Unique ref id
                            'additional_info1' => 'NA',
                            'additional_info2' => 'NA',
                            'additional_info3' => 'NA',
                            'additional_info4' => 'NA',
                            'additional_info5' => 'NA',
                            'additional_info6' => 'NA',
                            'additional_info7' => 'NA',
                        ];
                    } else {
                        // This fee does not have a sub_merchant_id, so add it to the unmapped amount.
                        $unmapped_amount += $item_amount;
                    }
                }
            }
            
            // Now, handle the total unmapped amount.
            if ($unmapped_amount > 0) {
                if (!empty($split_payment_payload)) {
                    // If we have existing splits, add the unmapped amount to the first one.
                    // This is to ensure the total split amount matches the transaction amount.
                    $split_payment_payload[0]['amount'] = number_format(
                        floatval($split_payment_payload[0]['amount']) + $unmapped_amount, 
                        2, 
                        '.', 
                        ''
                    );
                } else {
                    // If there are NO fees with sub_merchant_ids, create a single split
                    // for the entire amount using a fallback child merchant ID.
                    $fallback_child_id = $this->_get_fallback_sub_merchant_id();

                    if (!$fallback_child_id) {
                        // This is a critical configuration error. No sub-merchants are defined at all.
                        throw new Exception("Billdesk payment processing failed: No sub-merchant IDs are configured in the feetype table.");
                    }
                    
                    $split_payment_payload[] = [
                        'mercid' => $fallback_child_id,
                        'amount' => number_format($unmapped_amount, 2, '.', ''),
                        'customer_refid' => $school_code . 'ORN' . uniqid(),
                        'additional_info1' => 'NA',
                        'additional_info2' => 'NA',
                        'additional_info3' => 'NA',
                        'additional_info4' => 'NA',
                        'additional_info5' => 'NA',
                        'additional_info6' => 'NA',
                        'additional_info7' => 'NA',
                    ];
                }
            }

            $fee_group_name_str = implode(',', array_unique($fee_group_names));
            $fee_category_str = implode(',', array_unique($fee_categories));

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
                    'additional_info1' => $data['params']['name'],
                    'additional_info2' => "NA",
                    'additional_info3' => $data['params']['guardian_phone'],
                    'additional_info4' => $data['params']['email'],
                    'additional_info5' => $formatted_amount,
                    'additional_info6' => $fee_category_str,
                    'additional_info7' => $fee_group_name_str,
                ],
                // Add split_payment only if we have splits
                'split_payment' => !empty($split_payment_payload) ? $split_payment_payload : null 
            ];
            
            // Remove split_payment key if null to avoid API errors
            if (empty($ecom_payload['split_payment'])) {
                unset($ecom_payload['split_payment']);
            }

            // Log the entire ECOM PAYLOAD in readable JSON format
            log_message('error', 'BILLDESK_UAT_DATA: 1. JSON Request for ecom order: ' . json_encode($ecom_payload, JSON_PRETTY_PRINT));
            log_message('error', 'Billdesk Payload: Entire ECOM PAYLOAD (Readable): ' . json_encode($ecom_payload, JSON_PRETTY_PRINT));
            
            log_message('error', '--- ECOM PAYLOAD (Base64 Encoded) ---');
            log_message('error', 'DECODE THIS STRING TO SEE THE FULL PAYLOAD: ' . base64_encode(json_encode($ecom_payload)));

            try {
                $ecom_jwe_token = $this->billdesk_lib->create_jwe($ecom_payload);
            } catch (Exception $e) {
                throw new Exception("Error creating JWE for Ecom Order: " . $e->getMessage());
            }
            log_message('error', 'Billdesk Ecom Order Encrypted Request (JWE Token): ' . $ecom_jwe_token);

            try {
                $ecom_jws_token = $this->billdesk_lib->create_jws($ecom_jwe_token);
            } catch (Exception $e) {
                throw new Exception("Error creating JWS for Ecom Order: " . $e->getMessage());
            }

            log_message('error', 'Billdesk Ecom Order Signed Request (JWS Token): ' . $ecom_jws_token);

            $ecom_headers = [
                'Content-Type: application/jose',
                'Accept: application/jose',
                'BD-Traceid: ' . uniqid(),
                'BD-Timestamp: ' . date('YmdHis'),
            ];

            log_message('error', 'BILLDESK_UAT_DATA: 2. Original encrypted & signed ecom Order API request strings, BD-TraceID & BD-Timestamp: Request String=' . $ecom_jws_token);
            log_message('error', 'BILLDESK_UAT_DATA: 2. Ecom Order Request Headers: ' . json_encode($ecom_headers));

            $ch_ecom = curl_init();
            curl_setopt($ch_ecom, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/ecomorders/create");
            curl_setopt($ch_ecom, CURLOPT_POST, 1);
            curl_setopt($ch_ecom, CURLOPT_POSTFIELDS, $ecom_jws_token);
            curl_setopt($ch_ecom, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ecom, CURLOPT_HTTPHEADER, $ecom_headers);

            log_message('error', 'Billdesk Ecom Order Request URL: https://uat1.billdesk.com/u2/payments/ve1_2/ecomorders/create');
            log_message('error', 'Billdesk Ecom Order Request Headers: ' . print_r($ecom_headers, true));

            $ecom_response = curl_exec($ch_ecom);
            $ecom_response_headers_sent = curl_getinfo($ch_ecom, CURLINFO_HEADER_OUT); // Headers sent
            $ecom_response_http_code = curl_getinfo($ch_ecom, CURLINFO_HTTP_CODE); // HTTP status code
            $ecom_response_headers_recv = '';
            if (curl_getinfo($ch_ecom, CURLINFO_HEADER_SIZE) > 0) { // Check if headers were included in response
                $ecom_response_headers_recv = substr($ecom_response, 0, curl_getinfo($ch_ecom, CURLINFO_HEADER_SIZE)); // Received headers
            }
            log_message('error', 'BILLDESK_UAT_DATA: 3. Ecom Order Request Headers Sent via cURL: ' . $ecom_response_headers_sent);
            log_message('error', 'BILLDESK_UAT_DATA: 3. Ecom Order HTTP Response Code: ' . $ecom_response_http_code);
            log_message('error', 'BILLDESK_UAT_DATA: 3. Ecom Order Response Headers Received via cURL: ' . $ecom_response_headers_recv);
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
            log_message('error', 'BILLDESK_UAT_DATA: 3. Original ecom order encoded response string: ' . $ecom_response);
            log_message('error', 'BILLDESK_UAT_DATA: 3. Original ecom order decoded response string: ' . json_encode($decrypted_ecom_response, JSON_PRETTY_PRINT));

            // Check for success (status PENDING or 200, or presence of ecom_orderid)
            if (isset($decrypted_ecom_response['error_type'])) {
                 throw new Exception("Billdesk Ecom Order API Error: " . (isset($decrypted_ecom_response['message']) ? $decrypted_ecom_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_ecom_response['error_code']) ? $decrypted_ecom_response['error_code'] : 'N/A') . ")");
            } elseif (isset($decrypted_ecom_response['status']) && $decrypted_ecom_response['status'] != 200 && $decrypted_ecom_response['status'] != 'PENDING') {
                throw new Exception("Billdesk Ecom Order API Error: Status " . $decrypted_ecom_response['status']);
            } else {
                $ecom_orderid = $decrypted_ecom_response['ecom_orderid'];

                // Step 3: Transaction Creation
                $trans_payload = [
                    'mercid' => $this->api_config->api_secret_key,
                    'orderid' => $ecom_orderid,
                    'amount' => $formatted_amount,
                    'order_date' => date('Y-m-d\TH:i:sP'),
                    'currency' => '356',
                    'itemcode' => 'DIRECT',
                    'ru' => base_url('user/gateway/billdesk/callback'),
                    'device' => [
                        'init_channel' => 'internet',
                        'ip' => $this->input->ip_address(),
                        'user_agent' => $this->input->user_agent()
                    ]
                ];
                
                log_message('error', '--- TRANSACTION PAYLOAD (Base64 Encoded) ---');
                log_message('error', 'DECODE THIS STRING TO SEE THE FULL TRANSACTION PAYLOAD: ' . base64_encode(json_encode($trans_payload)));
                log_message('error', 'BILLDESK_UAT_DATA: 4. JSON Request for create order: ' . json_encode($trans_payload, JSON_PRETTY_PRINT));

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
                log_message('error', 'BILLDESK_UAT_DATA: 5. Original encrypted & signed Create Order API request strings, BD-TraceID & BD-Timestamp: Request String=' . $trans_jws_token);
                log_message('error', 'BILLDESK_UAT_DATA: 5. Create Order Request Headers: ' . json_encode($trans_headers));

                $ch_trans = curl_init();
                curl_setopt($ch_trans, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/orders/create");
                curl_setopt($ch_trans, CURLOPT_POST, 1);
                curl_setopt($ch_trans, CURLOPT_POSTFIELDS, $trans_jws_token);
                curl_setopt($ch_trans, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_trans, CURLOPT_HTTPHEADER, $trans_headers);

                log_message('error', 'Billdesk Transaction Request URL: https://uat1.billdesk.com/u2/payments/ve1_2/orders/create');
                log_message('error', 'Billdesk Transaction Request Headers: ' . print_r($trans_headers, true));

                $trans_response = curl_exec($ch_trans);
                $trans_response_headers_sent = curl_getinfo($ch_trans, CURLINFO_HEADER_OUT); // Headers sent
                $trans_response_http_code = curl_getinfo($ch_trans, CURLINFO_HTTP_CODE); // HTTP status code
                $trans_response_headers_recv = '';
                if (curl_getinfo($ch_trans, CURLINFO_HEADER_SIZE) > 0) { // Check if headers were included in response
                    $trans_response_headers_recv = substr($trans_response, 0, curl_getinfo($ch_trans, CURLINFO_HEADER_SIZE)); // Received headers
                }
                log_message('error', 'BILLDESK_UAT_DATA: 6. Create Order Request Headers Sent via cURL: ' . $trans_response_headers_sent);
                log_message('error', 'BILLDESK_UAT_DATA: 6. Create Order HTTP Response Code: ' . $trans_response_http_code);
                log_message('error', 'BILLDESK_UAT_DATA: 6. Create Order Response Headers Received via cURL: ' . $trans_response_headers_recv);
                $trans_err = curl_error($ch_trans);
                curl_close($ch_trans);

                if ($trans_err) {
                    throw new Exception("cURL Error (Transaction): " . $trans_err);
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

                log_message('error', '--- TRANSACTION RESPONSE (Base64 Encoded) ---');
                log_message('error', 'DECODE THIS STRING TO SEE THE FULL TRANSACTION RESPONSE: ' . base64_encode(json_encode($decrypted_trans_response)));
                log_message('error', 'BILLDESK_UAT_DATA: 6. Original encoded Create Order API response string: ' . $trans_response);
                log_message('error', 'BILLDESK_UAT_DATA: 6. Original decoded Create Order API response string: ' . json_encode($decrypted_trans_response, JSON_PRETTY_PRINT));

                if (isset($decrypted_trans_response['status']) && $decrypted_trans_response['status'] != 200 && $decrypted_trans_response['status'] != 'PENDING' && $decrypted_trans_response['status'] != 'ACTIVE') {
                     // Check if it's just a pending status which might be okay, but usually 'status' in response means HTTP status code equivalent or API status. 
                     // In V1.2, success often has status 200 or similar.
                     // If status is not 200/PENDING/ACTIVE, throw error.
                     throw new Exception("Billdesk Transaction API Error: " . (isset($decrypted_trans_response['message']) ? $decrypted_trans_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_trans_response['error_code']) ? $decrypted_trans_response['error_code'] : 'N/A') . ") Status: " . $decrypted_trans_response['status']);
                }

                // Step 4: Prepare Redirect
                // Assuming response contains 'bdorderid' and 'links' or 'rdata'.
                // If the response follows standard hypermedia format:
                $bdorderid = isset($decrypted_trans_response['bdorderid']) ? $decrypted_trans_response['bdorderid'] : '';
                $merchantid = $this->api_config->api_secret_key;
                
                // Try to find 'rdata' or equivalent.
                // Sometimes it's in 'links' array.
                $rdata = '';
                $redirect_url = 'https://uat1.billdesk.com/u2/web/v1_2/embeddedsdk'; // Default URL from user example

                if (isset($decrypted_trans_response['links'])) {
                    foreach ($decrypted_trans_response['links'] as $link) {
                        if (isset($link['headers']['authorization'])) {
                             // This is often the rdata/token
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
                
                // Fallback: Check if 'rdata' is directly in response
                if (empty($rdata) && isset($decrypted_trans_response['authToken'])) {
                    $rdata = $decrypted_trans_response['authToken'];
                }
                
                 if (empty($rdata)) {
                    // One last check, sometimes the entire response or a part of it is what's needed, but let's log if missing
                    log_message('error', 'Warning: Could not find rdata/authToken in Transaction response.');
                }

                $data['form_action'] = $redirect_url;
                $data['fields'] = [
                    'bdorderid' => $bdorderid,
                    'merchantid' => $merchantid,
                    'rdata' => $rdata
                ];

                // Insert into gateway_ins for tracking
                $ins_data = array(
                    'unique_id' => $ecom_orderid,
                    'parameter_details' => json_encode($data['params']),
                    'gateway_name' => 'billdesk',
                    'module' => 'fees',
                    'payment_status' => 'processing',
                );
                $gateway_ins_id = $this->gateway_ins_model->add_gateway_ins($ins_data);

                // Store gateway_ins_id in session for callback
                $current_params = $this->session->userdata('params');
                $current_params['gateway_ins_id'] = $gateway_ins_id;
                $this->session->set_userdata('params', $current_params);

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

                log_message('error', 'BILLDESK_UAT_DATA: 7. Original encoded payment response string: ' . $_POST['transaction_response']);
                log_message('error', 'BILLDESK_UAT_DATA: 7. Original decoded payment response string: ' . json_encode($response, JSON_PRETTY_PRINT));

                // Determine BillDesk transaction status
                $gateway_ins_status = 'failed';
                if (isset($response['auth_status'])) {
                    if ($response['auth_status'] == '0300') {
                        $gateway_ins_status = 'success';
                    } elseif ($response['auth_status'] == '0002') {
                        $gateway_ins_status = 'pending';
                    }
                }

                // Retrieve the gateway_ins record using unique_id (orderid)
                $gateway_ins_record = $this->gateway_ins_model->get_gateway_ins($response['orderid'], 'billdesk');
                $gateway_ins_id = null;
                $module = 'unknown'; // Default to unknown module

                if (!empty($gateway_ins_record)) {
                    $gateway_ins_id = $gateway_ins_record['id'];
                    $module = $gateway_ins_record['module'];
                    $original_params = json_decode($gateway_ins_record['parameter_details'], true); // Get original params
                } else {
                    throw new Exception("Gateway Ins record not found for Order ID: " . $response['orderid']);
                }

                // Log raw gateway response to gateway_ins_response table
                $gateway_ins_response_data = [
                    'gateway_ins_id' => $gateway_ins_id,
                    'posted_data' => json_encode($_POST),
                    'response' => json_encode($response),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->gateway_ins_model->add_gateway_ins_response($gateway_ins_response_data);
                
                // Now update the gateway_ins status
                $this->gateway_ins_model->update_gateway_ins(array(
                    'unique_id' => $response['orderid'], 
                    'gateway_name' => 'billdesk', 
                    'module' => $module, 
                    'payment_status' => $gateway_ins_status
                ));

                // Verify Transaction via API for success/pending statuses
                if (in_array($gateway_ins_status, ['success', 'pending'])) {
                    $verification_response = $this->billdesk_lib->verify_transaction($response['orderid']);
                    if (isset($verification_response['auth_status']) && $verification_response['auth_status'] == '0300') {
                        $response = $verification_response; // Use the verified response
                    } else {
                        log_message('error', "Billdesk Transaction Verification Failed for Order ID " . $response['orderid'] . ": " . (isset($verification_response['message']) ? $verification_response['message'] : 'Unknown Error'));
                    }
                }

                // Dispatch based on module
                if ($module === 'fees') {
                    if (isset($response['auth_status']) && $response['auth_status'] == '0300') {
                        $this->_processStudentFeeCallback($response, $gateway_ins_id, $original_params);
                    } elseif (isset($response['auth_status']) && $response['auth_status'] == '0002') {
                        $this->pending($response);
                    } else {
                        $this->fail($response);
                    }
                } elseif ($module === 'online_admission') {
                    if (isset($response['auth_status']) && $response['auth_status'] == '0300') {
                        $this->_processOnlineAdmissionCallback($response, $gateway_ins_id, $original_params);
                        // _processOnlineAdmissionCallback already handles redirection to its own success view
                    } elseif (isset($response['auth_status']) && $response['auth_status'] == '0002') {
                         $this->load->helper('url');
                         redirect('onlineadmission/billdesk/pending/' . urlencode(json_encode($response)));
                    } else {
                        $this->load->helper('url');
                        redirect('onlineadmission/billdesk/fail/' . urlencode(json_encode($response)));
                    }
                } else {
                    throw new Exception("Unknown module type found in gateway_ins record for Order ID: " . $response['orderid']);
                }

            } catch (Exception $e) {
                log_message('error', 'Billdesk Consolidated Callback Error: ' . $e->getMessage());
                // Fallback to a generic fail page if module cannot be determined or error occurs early
                $this->fail(['transaction_error_desc' => $e->getMessage()]);
            }
        } else {
            log_message('error', 'Billdesk Consolidated Callback Error: No transaction_response received in POST.');
            $this->fail(['transaction_error_desc' => 'Invalid response from payment gateway']);
        }
    }

    private function _processStudentFeeCallback($response, $gateway_ins_id, $original_params)
    {
        // This method contains the original student fee processing logic from callback()
        $transaction_id = $response['transactionid']; // Assuming 'transactionid' is the key in BillDesk response
        $bulk_fees = array();

        if (!empty($original_params['student_fees_master_array'])) {
            foreach ($original_params['student_fees_master_array'] as $fee_key => $fee_value) {
                $json_array = array(
                    'amount'          => $fee_value['amount_balance'],
                    'date'            => date('Y-m-d'),
                    'amount_discount' => $fee_value['applied_fee_discount'] ?? null,
                    'processing_charge_type' => $original_params['processing_charge_type'],
                    'gateway_processing_charge' => $original_params['gateway_processing_charge'],
                    'amount_fine'     => $fee_value['fine_balance'],
                    'description'     => "Online fees deposit through BillDesk. TXN ID: " . $transaction_id,
                    'received_by'     => '',
                    'payment_mode'    => 'Billdesk',
                );

                $insert_fee_data = array(
                    'fee_category' => $fee_value['fee_category'],
                    'student_transport_fee_id' => $fee_value['student_transport_fee_id'],
                    'student_fees_master_id' => $fee_value['student_fees_master_id'],
                    'fee_groups_feetype_id'  => $fee_value['fee_groups_feetype_id'],
                    'amount_detail'          => $json_array,
                );
                $bulk_fees[] = $insert_fee_data;
            }
        } else {
            log_message('error', 'Billdesk Student Fee Callback Error: student_fees_master_array is empty or missing in session params.');
        }

        $send_to = $original_params['guardian_phone'];
        $response_bulk_deposit = $this->studentfeemaster_model->add_bulk_fee_deposit($bulk_fees, $original_params['fee_discount_group'] ?? null);

        // Send SMS/Email
        $student_id = $original_params['student_id'];
        $student_session_id = $original_params['student_session_id']; // Use original_params for student_session_id
        $student = $this->student_model->getByStudentSession($student_session_id); // Fetch student details using student_session_id

        if ($response_bulk_deposit && is_array($response_bulk_deposit)) {
            $invoice = [];
            $amount = [];
            $fine_type = [];
            $due_date = [];
            $fine_percentage = [];
            $fine_amount = [];
            $fee_group_name = [];
            $type = [];
            $code = [];
            $fee_category = '';

            foreach ($response_bulk_deposit as $response_key => $response_value) {
                $fee_category = $response_value['fee_category'];
                $invoice[] = array(
                    'invoice_id'     => $response_value['invoice_id'],
                    'sub_invoice_id' => $response_value['sub_invoice_id'],
                    'fee_category' => $fee_category,
                );

                if ($response_value['student_transport_fee_id'] != 0 && $response_value['fee_category'] == "transport") {
                    $mailsms_array = $this->studenttransportfee_model->getTransportFeeMasterByStudentTransportID($response_value['student_transport_fee_id']);
                    $fee_group_name[] = $this->lang->line("transport_fees");
                    $type[] = $mailsms_array->month;
                    $code[] = "-";
                    $fine_type[] = $mailsms_array->fine_type;
                    $due_date[] = $mailsms_array->due_date;
                    $fine_percentage[] = $mailsms_array->fine_percentage;
                    $fine_amount[] = $mailsms_array->fine_amount;
                    $amount[] = $mailsms_array->amount;
                } else {
                    $mailsms_array = $this->feegrouptype_model->getFeeGroupByIDAndStudentSessionID($response_value['fee_groups_feetype_id'], $student_session_id);
                    $fee_group_name[] = $mailsms_array->fee_group_name;
                    $type[] = $mailsms_array->type;
                    $code[] = $mailsms_array->code;
                    $fine_type[] = $mailsms_array->fine_type;
                    $due_date[] = $mailsms_array->due_date;
                    $fine_percentage[] = $mailsms_array->fine_percentage;
                    $fine_amount[] = $mailsms_array->fine_amount;
                    if ($mailsms_array->is_system) {
                        $amount[] = $mailsms_array->balance_fee_master_amount;
                    } else {
                        $amount[] = $mailsms_array->amount;
                    }
                }
            }

            $obj_mail = [];
            $obj_mail['student_id'] = $student_id;
            $obj_mail['student_session_id'] = $student_session_id;
            $obj_mail['invoice'] = $invoice;
            $obj_mail['contact_no'] = $student['guardian_phone'];
            $obj_mail['email'] = $student['email'];
            $obj_mail['parent_app_key'] = $student['parent_app_key'];
            $obj_mail['amount'] = "(" . implode(',', $amount) . ")";
            $obj_mail['fine_type'] = "(" . implode(',', $fine_type) . ")";
            $obj_mail['due_date'] = "(" . implode(',', $due_date) . ")";
            $obj_mail['fine_percentage'] = "(" . implode(',', $fine_percentage) . ")";
            $obj_mail['fine_amount'] = "(" . implode(',', $fine_amount) . ")";
            $obj_mail['fee_group_name'] = "(" . implode(',', $fee_group_name) . ")";
            $obj_mail['type'] = "(" . implode(',', $type) . ")";
            $obj_mail['code'] = "(" . implode(',', $code) . ")";
            $obj_mail['fee_category'] = $fee_category;
            $obj_mail['send_type'] = 'group';

                    $this->mailsmsconf->mailsms('fee_submission', $obj_mail);
                } // ADDED THIS CLOSING BRACE
        
                $this->session->set_flashdata('success', $this->lang->line('payment_success_message'));
                $this->load->helper('url');
                redirect('user/user/getfees');
            }
    private function _processOnlineAdmissionCallback($response, $gateway_ins_id, $original_params)
    {
        // This method contains the online admission processing logic
        $transaction_id = $response['transactionid'];

        if (empty($original_params)) {
             throw new Exception("Payment parameters not found for online admission callback.");
        }

        // Update onlinestudent record paid_status
        $update_online_admission_data = array(
            'id' => $original_params['online_admission_id'],
            'paid_status' => 1, // 1 for paid
            'transaction_id' => $transaction_id,
            'paid_amount' => $response['amount'],
            'payment_mode' => 'Billdesk'
        );
        $this->onlinestudent_model->edit($update_online_admission_data);

        // Send SMS/Email (adapted for online admission)
        $online_admission_data = $this->onlinestudent_model->get($original_params['online_admission_id']);

        $sender_details = array(
            'firstname' => $online_admission_data['firstname'],
            'lastname' => $online_admission_data['lastname'],
            'email' => $online_admission_data['email'],
            'date' => date('Y-m-d'),
            'reference_no' => $online_admission_data['reference_no'],
            'mobileno' => $online_admission_data['mobileno'],
            'guardian_email' => $online_admission_data['guardian_email'], // Assuming guardian_email is stored
            'guardian_phone' => $online_admission_data['guardian_phone'], // Assuming guardian_phone is stored
        );
        $this->mailsmsconf->mailsms('online_admission_fee_submission', $sender_details); // Use a dedicated template if available

        // Redirect to the dedicated online admission success view
        // Need to load the PublicAdmissionForm controller to access its success methods, or duplicate them.
        // For strict separation, PublicAdmissionForm's methods (success, pending, fail) are better.
        // Or, we can redefine simple success/pending/fail methods here in user/gateway/Billdesk.php
        // and pass a 'module' param to the view for dynamic links.

        // To maintain the redirect structure of the onlineadmission/Billdesk controller I previously created,
        // we need to instantiate that controller and call its success/pending/fail methods.
        // However, this is generally bad practice in CodeIgniter (avoid direct controller instantiation).
        // A better approach is to have common views or to pass enough data to the current view.
        // For the sake of redirecting to the *correct* set of success/pending/fail pages,
        // I will use a simple redirect to the onlineadmission/billdesk controller's success/pending/fail methods.
        
        $this->load->helper('url'); // Ensure URL helper is loaded for redirect
        redirect('onlineadmission/billdesk/success/' . urlencode(json_encode($response)));
    }

    public function success($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/success', $data);
    }

    public function pending($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/pending', $data);
    }

    public function fail($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/fail', $data);
    }
}
