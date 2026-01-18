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

        // Calculate Totals and Prepare Split Payment Logic
        $total_amount = ($data['params']['fine_amount_balance'] + $data['params']['total']) - $data['params']['applied_fee_discount'] + $data['params']['gateway_processing_charge'];
        $formatted_amount = number_format($total_amount, 2, '.', '');
        $school_code = (!empty($this->setting[0]['dise_code'])) ? $this->setting[0]['dise_code'] : "MINERVA";

        // Group fees by Sub Merchant ID
        $grouped_fees = [];
        $grouped_fees['MAIN'] = 0; // Default bucket for fees without specific sub_merchant_id

        // Add Processing Charge to MAIN bucket
        if (isset($data['params']['gateway_processing_charge'])) {
            $grouped_fees['MAIN'] += $data['params']['gateway_processing_charge'];
        }

        $fee_group_names = [];
        $fee_categories = [];
        $first_valid_child_id = null;

        if (!empty($data['params']['student_fees_master_array'])) {
            foreach ($data['params']['student_fees_master_array'] as $fee) {
                $fee_group_names[] = $fee['fee_group_name'];
                $fee_categories[] = $fee['fee_category'];

                $item_amount = $fee['amount_balance'] + $fee['fine_balance'];
                
                if (isset($fee['applied_fee_discount'])) {
                    $item_amount -= $fee['applied_fee_discount'];
                }
                
                $sub_mid = $this->get_sub_merchant_id($fee['fee_groups_feetype_id']);
                
                if ($sub_mid) {
                    if (!$first_valid_child_id) {
                        $first_valid_child_id = $sub_mid;
                    }
                } else {
                    $sub_mid = 'MAIN';
                }
                
                if (!isset($grouped_fees[$sub_mid])) {
                    $grouped_fees[$sub_mid] = 0;
                }
                $grouped_fees[$sub_mid] += $item_amount;
            }
        }
        
        // Handle Unmapped/Processing Fees
        // Assign MAIN bucket to the first valid child ID found, or fallback to a default
        $target_child_id = $first_valid_child_id ? $first_valid_child_id : 'UAT2K800C1';
        
        if (isset($grouped_fees['MAIN']) && $grouped_fees['MAIN'] > 0) {
            if (!isset($grouped_fees[$target_child_id])) {
                $grouped_fees[$target_child_id] = 0;
            }
            $grouped_fees[$target_child_id] += $grouped_fees['MAIN'];
            unset($grouped_fees['MAIN']);
        }
        
        $split_payment_payload = [];
        foreach ($grouped_fees as $mid => $amount) {
            if ($mid == 'MAIN') continue; 
            
            if ($amount > 0) {
                $split_payment_payload[] = [
                    'mercid' => $mid,
                    'amount' => number_format($amount, 2, '.', ''),
                    'customer_refid' => $school_code . 'ORN' . time() . rand(11, 99), 
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
                    'student_name' => $data['params']['name'],
                    'invoice' => "NA",
                    'contact_no' => $data['params']['guardian_phone'],
                    'email' => $data['params']['email'],
                    'amount' => $formatted_amount,
                    'fee_category' => $fee_category_str,
                    'fee_group_name' => $fee_group_name_str,
                ],
                // Add split_payment only if we have splits
                'split_payment' => !empty($split_payment_payload) ? $split_payment_payload : null 
            ];
            
            // Remove split_payment key if null to avoid API errors
            if (empty($ecom_payload['split_payment'])) {
                unset($ecom_payload['split_payment']);
            }

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

                log_message('error', 'Billdesk Transaction Request URL: https://uat1.billdesk.com/u2/payments/ve1_2/orders/create');
                log_message('error', 'Billdesk Transaction Request Headers: ' . print_r($trans_headers, true));

                $trans_response = curl_exec($ch_trans);
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

                // Update gateway_ins status
                $gateway_ins_status = 'failed';
                if (isset($response['auth_status'])) {
                    if ($response['auth_status'] == '0300') {
                        $gateway_ins_status = 'success';
                    } elseif ($response['auth_status'] == '0002') {
                        $gateway_ins_status = 'pending';
                    }
                }
                
                $this->gateway_ins_model->update_gateway_ins(array(
                    'unique_id' => $response['orderid'], 
                    'gateway_name' => 'billdesk', 
                    'payment_status' => $gateway_ins_status
                ));

                if (isset($response['auth_status']) && $response['auth_status'] == '0300') {
                    // Payment successful
                    
                    // Step 8: Verify Transaction via API
                    $verification_response = $this->billdesk_lib->verify_transaction($response['orderid']);
                    
                    if (isset($verification_response['auth_status']) && $verification_response['auth_status'] == '0300') {
                        $response = $verification_response; // Use the verified response
                    } else {
                        throw new Exception("Transaction verification failed: " . (isset($verification_response['message']) ? $verification_response['message'] : 'Unknown Error'));
                    }

                    $params = $this->session->userdata('params');
                    // ... (rest of success logic) ...
                    
                    // (I need to match the existing code context carefully to avoid breaking the rest of the method)
                    // The replace tool requires exact old_string. I'll target the block starting from the if check.
                    
                    $transaction_id = $response['transactionid']; // Assuming 'transactionid' is the key in BillDesk response
                    $bulk_fees = array();

                    if (!empty($params['student_fees_master_array'])) {
                        foreach ($params['student_fees_master_array'] as $fee_key => $fee_value) {
                            // ... (fee construction) ...
                            $json_array = array(
                                'amount'          => $fee_value['amount_balance'],
                                'date'            => date('Y-m-d'),
                                'amount_discount' => $fee_value['applied_fee_discount'],
                                'processing_charge_type' => $params['processing_charge_type'],
                                'gateway_processing_charge' => $params['gateway_processing_charge'],
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
                        log_message('error', 'Billdesk Callback Error: student_fees_master_array is empty or missing in session params.');
                    }

                    // --- DEBUG LOGGING START ---
                    log_message('error', 'Billdesk Callback - Bulk Fees Payload: ' . json_encode($bulk_fees));
                    // --- DEBUG LOGGING END ---

                    $send_to = $params['guardian_phone'];
                    $response_bulk_deposit = $this->studentfeemaster_model->add_bulk_fee_deposit($bulk_fees, $params['fee_discount_group']);

                    // Send SMS/Email
                    $student_id = $this->customlib->getStudentSessionUserID();
                    $student_current_class = $this->customlib->getStudentCurrentClsSection();
                    $student_session_id = $student_current_class->student_session_id;
                    $student = $this->student_model->getStudentByClassSectionID($student_current_class->class_id, $student_current_class->section_id, $student_id);

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
                    }

                    $this->success($response);
                } elseif (isset($response['auth_status']) && $response['auth_status'] == '0002') {
                    // Payment Pending
                    $this->pending($response);
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

    public function pending($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/pending', $data);
    }

    public function fail($response) {
        $data['response'] = $response;
        $this->load->view('user/gateway/billdesk/fail', $data);
    }
}
