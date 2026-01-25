<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Billdesk extends OnlineAdmission_Controller
{

    public $api_config;

    public function __construct()
    {
        parent::__construct();
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->getSetting(); // Load all settings including onlineform_sub_merchant_id
        $this->load->library('gateway_ins/billdesk_lib');
        $this->load->library('mailsmsconf');
        $this->load->model('onlinestudent_model'); // Used for online admissions data
        $this->load->model('gateway_ins_model'); // For tracking gateway transactions
    }

    public function index()
    {
        $data = array();
        $online_admission_id = $this->session->userdata('reference'); // Get admission_id from session
        
        if (empty($online_admission_id)) {
            $this->session->set_flashdata('error', 'Online Admission ID not found in session.');
            redirect(base_url('onlineadmission/checkout/paymentfailed'));
        }

        $online_data = $this->onlinestudent_model->get($online_admission_id);

        if (empty($online_data)) {
            $this->session->set_flashdata('error', 'Online Admission data not found.');
            redirect(base_url('onlineadmission/checkout/paymentfailed'));
        }

        // Check if BillDesk settings are available
        if (empty($this->api_config->api_secret_key)) {
            $this->session->set_flashdata('error', 'Billdesk settings not available.');
            redirect(base_url("onlineadmission/checkout/paymentfailed/" . $online_data['reference_no']));
        }

        $data['online_data'] = $online_data;
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        try {
            $total_amount = $this->setting->online_admission_amount; // Amount from sch_settings
            $formatted_amount = number_format($total_amount, 2, '.', '');
            $school_code = (!empty($this->setting->dise_code)) ? $this->setting->dise_code : "MINERVA"; // Use $this->setting->dise_code

            $sub_mid = $this->setting->onlineform_sub_merchant_id; // Our new field
            
            $split_payment_payload = [];

            if (!empty($sub_mid)) {
                $split_payment_payload[] = [
                    'mercid' => $sub_mid,
                    'amount' => $formatted_amount,
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
                 // Fallback if sub_merchant_id is not set for online forms
                 // This should be handled based on BillDesk requirements.
                 // For now, let's throw an error as you explicitly want it configured.
                throw new Exception("Billdesk payment processing failed: Online form sub-merchant ID is not configured.");
            }

            // Step 2: Ecom Order
            $ecom_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'amount' => $formatted_amount,
                'order_ref_no' => time() . rand(1111, 9999), // Unique order reference
                'ecom_order_date' => date('Y-m-d\TH:i:sP'),
                'ru' => base_url('onlineadmission/billdesk/callback'), // Callback URL
                'currency' => '356', // INR Code
                'itemcode' => 'DIRECT',
                'additional_info' => [
                    'additional_info1' => $online_data['firstname'] . ' ' . $online_data['lastname'],
                    'additional_info2' => $online_data['reference_no'], // Online admission reference no
                    'additional_info3' => $online_data['mobileno'],
                    'additional_info4' => $online_data['email'],
                    'additional_info5' => $formatted_amount,
                    'additional_info6' => 'Online Admission Fee', // Fee Category
                    'additional_info7' => $online_data['id'], // Store online admission ID
                ],
                'split_payment' => !empty($split_payment_payload) ? $split_payment_payload : null 
            ];
            
            if (empty($ecom_payload['split_payment'])) {
                unset($ecom_payload['split_payment']);
            }

            // Log for debugging
            log_message('debug', 'Billdesk Online Admission Ecom Payload: ' . json_encode($ecom_payload, JSON_PRETTY_PRINT));

            // Create JWE and JWS tokens (using billdesk_lib)
            $ecom_jwe_token = $this->billdesk_lib->create_jwe($ecom_payload);
            $ecom_jws_token = $this->billdesk_lib->create_jws($ecom_jwe_token);

            $ecom_headers = [
                'Content-Type: application/jose',
                'Accept: application/jose',
                'BD-Traceid: ' . uniqid(),
                'BD-Timestamp: ' . date('YmdHis'),
            ];

            // cURL request to BillDesk Ecom Order API
            $ch_ecom = curl_init();
            curl_setopt($ch_ecom, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/ecomorders/create"); // UAT URL
            curl_setopt($ch_ecom, CURLOPT_POST, 1);
            curl_setopt($ch_ecom, CURLOPT_POSTFIELDS, $ecom_jws_token);
            curl_setopt($ch_ecom, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ecom, CURLOPT_HTTPHEADER, $ecom_headers);
            $ecom_response = curl_exec($ch_ecom);
            $ecom_err = curl_error($ch_ecom);
            curl_close($ch_ecom);

            if ($ecom_err) {
                throw new Exception("cURL Error (Ecom Order): " . $ecom_err);
            }

            $decrypted_ecom_response = $this->billdesk_lib->decrypt_response($this->billdesk_lib->verify_response($ecom_response));

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
                    'ru' => base_url('onlineadmission/billdesk/callback'),
                    'device' => [
                        'init_channel' => 'internet',
                        'ip' => $this->input->ip_address(),
                        'user_agent' => $this->input->user_agent()
                    ]
                ];
                
                $trans_jwe_token = $this->billdesk_lib->create_jwe($trans_payload);
                $trans_jws_token = $this->billdesk_lib->create_jws($trans_jwe_token);

                $trans_headers = [
                    'Content-Type: application/jose',
                    'Accept: application/jose',
                    'BD-Traceid: ' . uniqid(),
                    'BD-Timestamp: ' . date('YmdHis'),
                ];

                // cURL request to BillDesk Transaction API
                $ch_trans = curl_init();
                curl_setopt($ch_trans, CURLOPT_URL, "https://uat1.billdesk.com/u2/payments/ve1_2/orders/create"); // UAT URL
                curl_setopt($ch_trans, CURLOPT_POST, 1);
                curl_setopt($ch_trans, CURLOPT_POSTFIELDS, $trans_jws_token);
                curl_setopt($ch_trans, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_trans, CURLOPT_HTTPHEADER, $trans_headers);
                $trans_response = curl_exec($ch_trans);
                $trans_err = curl_error($ch_trans);
                curl_close($ch_trans);

                if ($trans_err) {
                    throw new Exception("cURL Error (Transaction): " . $trans_err);
                }

                $decrypted_trans_response = $this->billdesk_lib->decrypt_response($this->billdesk_lib->verify_response($trans_response));
                
                if (isset($decrypted_trans_response['status']) && $decrypted_trans_response['status'] != 200 && $decrypted_trans_response['status'] != 'PENDING' && $decrypted_trans_response['status'] != 'ACTIVE') {
                    throw new Exception("Billdesk Transaction API Error: " . (isset($decrypted_trans_response['message']) ? $decrypted_trans_response['message'] : 'Unknown Billdesk Error') . " (Code: " . (isset($decrypted_trans_response['error_code']) ? $decrypted_trans_response['error_code'] : 'N/A') . ") Status: " . $decrypted_trans_response['status']);
                }

                // Step 4: Prepare Redirect
                $bdorderid = isset($decrypted_trans_response['bdorderid']) ? $decrypted_trans_response['bdorderid'] : '';
                $merchantid = $this->api_config->api_secret_key;
                
                $rdata = '';
                $redirect_url = 'https://uat1.billdesk.com/u2/web/v1_2/embeddedsdk'; // Default URL

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
                
                $data['form_action'] = $redirect_url;
                $data['fields'] = [
                    'bdorderid' => $bdorderid,
                    'merchantid' => $merchantid,
                    'rdata' => $rdata
                ];

                // Insert into gateway_ins for tracking
                $ins_data = array(
                    'unique_id' => $ecom_orderid,
                    'parameter_details' => json_encode(['online_admission_id' => $online_admission_id, 'amount' => $total_amount]), // Store relevant details
                    'gateway_name' => 'billdesk',
                    'module' => 'online_admission', // Indicate this is for online admission
                    'payment_status' => 'processing',
                );
                $this->gateway_ins_model->add_gateway_ins($ins_data);

                $this->load->view('onlineadmission/billdesk/redirect', $data);

            }

        } catch (Exception $e) {
            $data['api_error'] = $e->getMessage();
            $this->session->set_flashdata('error', $e->getMessage());
            redirect(base_url("onlineadmission/checkout/paymentfailed/" . $online_data['reference_no']));
        }
    }

    public function callback()
    {
        $online_admission_id = $this->session->userdata('reference');
        if (empty($online_admission_id)) {
            $this->session->set_flashdata('error', 'Online Admission ID not found in session for callback.');
            redirect(base_url('onlineadmission/checkout/paymentfailed'));
        }

        $online_data = $this->onlinestudent_model->get($online_admission_id);

        if (empty($online_data)) {
            $this->session->set_flashdata('error', 'Online Admission data not found for callback.');
            redirect(base_url('onlineadmission/checkout/paymentfailed'));
        }

        try {
            $jws_token = $_POST['transaction_response'];
            $jwe_token = $this->billdesk_lib->verify_response($jws_token);
            $response = $this->billdesk_lib->decrypt_response($jwe_token);

            // Log for debugging
            log_message('debug', 'Billdesk Online Admission Callback Response: ' . json_encode($response, JSON_PRETTY_PRINT));

            // Retrieve gateway_ins_id for linking response
            $gateway_ins_record = $this->gateway_ins_model->get_gateway_ins($response['orderid'], 'billdesk');
            $gateway_ins_id = null;
            if (!empty($gateway_ins_record)) {
                $gateway_ins_id = $gateway_ins_record['id'];
            }

            // Log raw gateway response to gateway_ins_response table
            $gateway_ins_response_data = [
                'gateway_ins_id' => $gateway_ins_id,
                'gateway_name' => 'billdesk',
                'response_data' => json_encode($response), // Store the full decrypted response
                'status_code' => isset($response['auth_status']) ? $response['auth_status'] : null,
                'transaction_id' => isset($response['transactionid']) ? $response['transactionid'] : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->gateway_ins_model->add_gateway_ins_response($gateway_ins_response_data);

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
                'module' => 'online_admission', // Indicate module
                'payment_status' => $gateway_ins_status
            ));

            if (isset($response['auth_status']) && $response['auth_status'] == '0300') {
                // Payment successful
                $verification_response = $this->billdesk_lib->verify_transaction($response['orderid']);
                
                if (isset($verification_response['auth_status']) && $verification_response['auth_status'] == '0300') {
                    $response = $verification_response; // Use the verified response
                } else {
                    throw new Exception("Transaction verification failed: " . (isset($verification_response['message']) ? $verification_response['message'] : 'Unknown Error'));
                }

                $transaction_id = $response['transactionid'];

                $payment_data = [
                    'online_admission_id' => $online_admission_id,
                    'transaction_id' => $transaction_id,
                    'paid_amount' => $response['amount'], // Amount from BillDesk response
                    'payment_mode' => 'Billdesk',
                    'payment_type' => 'online_admission',
                    'date' => date('Y-m-d H:i:s'),
                    'gateway_name' => 'billdesk',
                    'paid_status' => 1 // Successfully paid
                ];

                $this->onlinestudent_model->paymentSuccess($payment_data);

                // Send SMS/Email (optional, if required for online admissions)
                // $this->mailsmsconf->mailsms('online_admission_payment_success', $online_data);

                $this->session->set_flashdata('msg', '<div class="alert alert-success">Payment successful! Your online admission has been processed.</div>');
                redirect(base_url("onlineadmission/checkout/successinvoice/" . $online_data['reference_no']));

            } elseif (isset($response['auth_status']) && $response['auth_status'] == '0002') {
                // Payment Pending
                $this->session->set_flashdata('msg', '<div class="alert alert-warning">Payment is pending. Please check with your bank.</div>');
                redirect(base_url("onlineadmission/checkout/processinginvoice/" . $online_data['reference_no']));
            } else {
                // Payment failed
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Payment failed. Please try again.</div>');
                redirect(base_url("onlineadmission/checkout/paymentfailed/" . $online_data['reference_no']));
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Payment processing error: ' . $e->getMessage());
            redirect(base_url("onlineadmission/checkout/paymentfailed/" . $online_data['reference_no']));
        }
    }

}