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
        if (!empty($this->school_details->timezone)) {
            date_default_timezone_set($this->school_details->timezone);
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'book/index');
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        $total_amount = ($data['params']['fine_amount_balance'] + $data['params']['total']) - $data['params']['applied_fee_discount'] + $data['params']['gateway_processing_charge'];
        $formatted_amount = number_format($total_amount, 2, '.', '');
        $school_code = (!empty($this->setting[0]['dise_code'])) ? $this->setting[0]['dise_code'] : "MINERVA";

        $fee_group_names = [];
        $fee_categories = [];
        foreach ($data['params']['student_fees_master_array'] as $fee) {
            $fee_group_names[] = $fee['fee_group_name'];
            $fee_categories[] = $fee['fee_category'];
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
                'split_payment' => [
                    [
                        'mercid' => 'UAT2K800C1',
                        'amount' => $formatted_amount,
                        'customer_refid' => $school_code . 'ORN' . time() . rand(11,99),
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

                if (isset($decrypted_trans_response['status']) && $decrypted_trans_response['status'] != 200) {
                     // Check if it's just a pending status which might be okay, but usually 'status' in response means HTTP status code equivalent or API status. 
                     // In V1.2, success often has status 200 or similar.
                     // If status is not 200, throw error.
                     throw new Exception("Billdesk Transaction API Error: " . (isset($decrypted_trans_response['message']) ? $decrypted_trans_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_trans_response['error_code']) ? $decrypted_trans_response['error_code'] : 'N/A') . ")");
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
                    
                    $params = $this->session->userdata('params');
                    $transaction_id = $response['transactionid']; // Assuming 'transactionid' is the key in BillDesk response
                    $bulk_fees = array();

                    foreach ($params['student_fees_master_array'] as $fee_key => $fee_value) {
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

                    $send_to = $params['guardian_phone'];
                    $response_bulk_deposit = $this->studentfeemaster_model->fee_deposit_bulk($bulk_fees, $params['fee_discount_group']);

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
