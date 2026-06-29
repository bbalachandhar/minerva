<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Billdesk extends Student_Controller
{

    public $api_config;

    public function __construct()
    {
        $this->auth_bypass_methods[] = 'callback'; // Bypass authentication for the callback method
        $this->auth_bypass_methods[] = 'index';    // Bypass authentication for the index method
        $this->auth_bypass_methods[] = 'pay';      // Bypass authentication for the pay method as well
        parent::__construct();
        $this->api_config = $this->paymentsetting_model->getActiveMethod();
        $this->setting = $this->setting_model->get(); // This returns an array of associative arrays
        $this->load->library('gateway_ins/billdesk_lib');
        $this->load->library('mailsmsconf');
        $this->load->model('studentfeemaster_model');
        $this->load->model('studenttransportfee_model');
        $this->load->model('feegrouptype_model');
        $this->load->model('gateway_ins_model');
        $this->load->model('onlinestudent_model'); // Load onlinestudent_model for online admission details
        // Note: student_model might be needed for callback, ensure it's loaded if not already.
        $this->load->model('student_model');
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
        // Menu settings should be conditional or removed for online_admission
        // For now, these are default for 'fees' module and unset for 'online_admission_fee'
        $data['params'] = $this->session->userdata('params');
        $data['setting'] = $this->setting;
        $data['api_error'] = '';

        if (empty($data['params'])) {
            $this->session->set_flashdata('error', 'Payment parameters not found in session.');
            redirect($_SERVER['HTTP_REFERER'] ?? base_url());
        }

        // Server-side fee re-computation — only when school is collecting the charge (mode = 'school').
        // When mode = 'gateway', BillDesk adds its own charge on their payment page, so we send
        // the base amount only.  Sending a charge from our end in gateway mode causes double-charging.
        $charge_mode = $data['params']['billdesk_charge_mode'] ?? 'gateway';
        $posted_payment_method = $this->input->post('billdesk_payment_method');
        if ($charge_mode === 'school' && !empty($posted_payment_method)) {
            $slabs      = $this->paymentsetting_model->getBilldeskSlabs();
            $base_amt   = (float)($data['params']['total'] ?? 0)
                        + (float)($data['params']['fine_amount_balance'] ?? 0)
                        - (float)($data['params']['applied_fee_discount'] ?? 0);
            $computed_charge = 0.00;
            foreach ($slabs as $slab) {
                if ($slab->payment_method === $posted_payment_method && $slab->is_active) {
                    if ($slab->charge_type === 'flat') {
                        $computed_charge = (float)$slab->charge_value;
                    } else {
                        if ((float)$slab->amount_threshold > 0 && $base_amt > (float)$slab->amount_threshold) {
                            $computed_charge = ($base_amt * (float)$slab->charge_value_above) / 100;
                        } else {
                            $computed_charge = ($base_amt * (float)$slab->charge_value) / 100;
                        }
                    }
                    break;
                }
            }
            $data['params']['gateway_processing_charge'] = round($computed_charge, 2);
            $data['params']['billdesk_payment_method']   = $posted_payment_method;
            $this->session->set_userdata('params', $data['params']);
            log_message('error', 'BILLDESK_SLAB: method=' . $posted_payment_method . ' base=' . $base_amt . ' fee=' . $computed_charge);
        } else {
            // Gateway mode — zero out any charge so it is never added to the order amount
            $data['params']['gateway_processing_charge'] = 0.00;
            if (!empty($posted_payment_method)) {
                $data['params']['billdesk_payment_method'] = $posted_payment_method;
            }
            $this->session->set_userdata('params', $data['params']);
        }

        $module = $data['params']['item_type'] ?? 'fees'; // Determine module early
        log_message('error', 'BILLDESK_DEBUG_PAY: Initiating payment for module: ' . $module);
        log_message('error', 'BILLDESK_DEBUG_PAY: Session params: ' . json_encode($data['params']));
        log_message('error', 'BILLDESK_DEBUG_PAY: Settings (first element): ' . json_encode($this->setting[0])); // Log first element as it's an array

        try {
            $total_amount = 0;
            $formatted_amount = '';
            // Corrected access for school_code using $this->setting[0]['dise_code']
            $school_code = (!empty($this->setting[0]['dise_code'])) ? $this->setting[0]['dise_code'] : "MINERVA";
            $ecom_order_ref_no = '';
            $ecom_additional_info = [];
            $split_payment_payload = [];
            $gateway_module = $module; // This will be used in gateway_ins insert

            if ($module == 'fees') {
                $total_amount = ($data['params']['fine_amount_balance'] + $data['params']['total']) - $data['params']['applied_fee_discount'] + $data['params']['gateway_processing_charge'];
                $formatted_amount = number_format($total_amount, 2, '.', '');
                
                $fee_group_names = [];
                $fee_categories = [];
                $unmapped_amount = 0;

                if (isset($data['params']['gateway_processing_charge']) && $data['params']['gateway_processing_charge'] > 0) {
                    $unmapped_amount += $data['params']['gateway_processing_charge'];
                }

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
                            $split_payment_payload[] = [
                                'mercid' => $sub_mid,
                                'amount' => number_format($item_amount, 2, '.', ''),
                                'customer_refid' => $school_code . 'ORN' . uniqid(),
                                'additional_info1' => $data['params']['name'],
                                'additional_info2' => $data['params']['admission_no'] ?? 'NA',
                                'additional_info3' => $data['params']['guardian_phone'],
                                'additional_info4' => $data['params']['email'],
                                'additional_info5' => ($data['params']['class'] ?? '') . ' ' . ($data['params']['section'] ?? ''),
                                'additional_info6' => $data['params']['father_name'] ?? 'NA',
                                'additional_info7' => $fee['fee_group_name'] ?? 'NA',
                            ];
                        } else {
                            $unmapped_amount += $item_amount;
                        }
                    }
                }
                if ($unmapped_amount > 0) {
                    if (!empty($split_payment_payload)) {
                        $split_payment_payload[0]['amount'] = number_format(floatval($split_payment_payload[0]['amount']) + $unmapped_amount, 2, '.', '');
                    } else {
                        $fallback_child_id = $this->_get_fallback_sub_merchant_id();
                        if (!$fallback_child_id) {
                            throw new Exception("Billdesk payment processing failed: No sub-merchant IDs are configured in the feetype table.");
                        }
                        $split_payment_payload[] = [
                            'mercid' => $fallback_child_id,
                            'amount' => number_format($unmapped_amount, 2, '.', ''),
                            'customer_refid' => $school_code . 'ORN' . uniqid(),
                            'additional_info1' => $data['params']['name'],
                            'additional_info2' => $data['params']['admission_no'] ?? 'NA',
                            'additional_info3' => $data['params']['guardian_phone'],
                            'additional_info4' => $data['params']['email'],
                            'additional_info5' => ($data['params']['class'] ?? '') . ' ' . ($data['params']['section'] ?? ''),
                            'additional_info6' => $data['params']['father_name'] ?? 'NA',
                            'additional_info7' => implode(',', array_unique($fee_group_names)),
                        ];
                    }
                }
                $ecom_order_ref_no = time() . rand(1111, 9999);
                $ecom_additional_info = [
                    'additional_info1' => $data['params']['name'],
                    'additional_info2' => $data['params']['admission_no'] ?? 'NA',
                    'additional_info3' => $data['params']['guardian_phone'],
                    'additional_info4' => $data['params']['email'],
                    'additional_info5' => ($data['params']['class'] ?? '') . ' ' . ($data['params']['section'] ?? ''),
                    'additional_info6' => $data['params']['father_name'] ?? 'NA',
                    'additional_info7' => implode(',', array_unique($fee_group_names)),
                ];
                
                $this->session->set_userdata('top_menu', 'Library'); // Original menu settings for student flow
                $this->session->set_userdata('sub_menu', 'book/index'); // Original menu settings for student flow

            } elseif ($module == 'online_admission_fee') {
                $online_admission_id = $data['params']['online_admission_id'];
                $online_student_details = $this->onlinestudent_model->get($online_admission_id);

                if (empty($online_student_details)) {
                    throw new Exception("Online admission student details not found for ID: " . $online_admission_id);
                }
                log_message('error', 'BILLDESK_DEBUG_PAY: Online Student Details: ' . json_encode($online_student_details));

                $total_admission_amount = $data['params']['admission_amount'];
                $processing_charge = $data['params']['processing_charge'];
                $total_amount = $data['params']['total']; // Final amount including processing fees

                $formatted_amount = number_format($total_amount, 2, '.', '');
                
                $onlineform_sub_merchant_id = $data['params']['sch_setting_detail']->onlineform_sub_merchant_id;
                log_message('error', 'BILLDESK_DEBUG_PAY: onlineform_sub_merchant_id: ' . $onlineform_sub_merchant_id);

                // Only add split_payment if a child sub-merchant ID is configured; otherwise
                // the parent mercid handles the full amount (no split payment needed).
                if (!empty($onlineform_sub_merchant_id)) {
                    $split_payment_payload[] = [
                        'mercid' => $onlineform_sub_merchant_id,
                        'amount' => $formatted_amount,
                        'customer_refid' => $data['params']['reference_no'] . time(),
                        'additional_info1' => 'NA', 'additional_info2' => 'NA', 'additional_info3' => 'NA',
                        'additional_info4' => 'NA', 'additional_info5' => 'NA', 'additional_info6' => 'NA',
                        'additional_info7' => 'NA',
                    ];
                }

                $ecom_order_ref_no = $data['params']['reference_no'] . time() . rand(11, 99);

                // Determine course name if possible, otherwise use ID
                $course_applied = 'N/A';
                if (!empty($online_student_details['admission_course_id'])) {
                    $course_row = $this->db->select('course_name')->get_where('online_admission_courses', ['id' => $online_student_details['admission_course_id']])->row_array();
                    if (!empty($course_row['course_name'])) {
                        $course_applied = $course_row['course_name'];
                    }
                }

                // Additional info shared across the main order AND each split_payment child.
                // Billdesk reconciliation reports surface these fields, so keep them consistent.
                $shared_additional_info = [
                    'additional_info1' => $online_student_details['firstname'] . ' ' . $online_student_details['lastname'], // Student Name
                    'additional_info2' => (string) ($data['params']['reference_no'] ?? ''),                                 // Application Ref No
                    'additional_info3' => $online_student_details['mobileno'],                                              // Mobile
                    'additional_info4' => $online_student_details['email'],                                                 // Email
                    'additional_info5' => $online_student_details['father_name'],                                           // Father Name
                    'additional_info6' => $online_student_details['quota_type'] ?? 'NA',                                   // Quota Type
                    'additional_info7' => $course_applied,                                                                  // Course Applied
                ];

                $ecom_additional_info = $shared_additional_info;

                // Apply the same additional_info to the split_payment child if one was added above
                if (!empty($split_payment_payload)) {
                    $split_payment_payload[0] = array_merge(
                        $split_payment_payload[0],
                        $shared_additional_info
                    );
                }

                $gateway_module = 'online_admission'; // Set module for gateway_ins

                // For online admission, menu settings are not relevant, can unset or let it be
                $this->session->unset_userdata('top_menu');
                $this->session->unset_userdata('sub_menu');

            } elseif ($module == 'online_course_fee') {
                // Course fee (remaining balance) payment from applicant dashboard
                $online_admission_id = $data['params']['online_admission_id'];
                $online_student_details = $this->onlinestudent_model->get($online_admission_id);

                if (empty($online_student_details)) {
                    throw new Exception("Online admission student details not found for ID: " . $online_admission_id);
                }

                $total_amount       = $data['params']['total'];     // includes processing charge
                $course_fee_amount  = $data['params']['course_fee_amount']; // actual balance
                $processing_charge  = $data['params']['processing_charge'];
                $formatted_amount   = number_format($total_amount, 2, '.', '');

                $onlineform_sub_merchant_id = $data['params']['sch_setting_detail']->onlineform_sub_merchant_id ?? '';

                if (!empty($onlineform_sub_merchant_id)) {
                    $split_payment_payload[] = [
                        'mercid' => $onlineform_sub_merchant_id,
                        'amount' => $formatted_amount,
                        'customer_refid' => $data['params']['reference_no'] . 'CF' . time(),
                        'additional_info1' => 'NA', 'additional_info2' => 'NA', 'additional_info3' => 'NA',
                        'additional_info4' => 'NA', 'additional_info5' => 'NA', 'additional_info6' => 'NA',
                        'additional_info7' => 'NA',
                    ];
                }

                $ecom_order_ref_no = $data['params']['reference_no'] . time() . rand(11, 99);

                $shared_additional_info = [
                    'additional_info1' => $online_student_details['firstname'] . ' ' . $online_student_details['lastname'],
                    'additional_info2' => (string) ($data['params']['reference_no'] ?? ''),
                    'additional_info3' => $online_student_details['mobileno'],
                    'additional_info4' => $online_student_details['email'],
                    'additional_info5' => $online_student_details['father_name'],
                    'additional_info6' => $online_student_details['quota_type'] ?? 'NA',
                    'additional_info7' => 'Course Fee Payment',
                ];
                $ecom_additional_info = $shared_additional_info;

                if (!empty($split_payment_payload)) {
                    $split_payment_payload[0] = array_merge($split_payment_payload[0], $shared_additional_info);
                }

                $gateway_module = 'online_admission';
                $this->session->unset_userdata('top_menu');
                $this->session->unset_userdata('sub_menu');

            } else {
                throw new Exception("Unknown payment module: " . $module);
            }

            // Determine API endpoints based on gateway_mode (0 = UAT, 1 = Production)
            $is_production = (!empty($this->api_config->gateway_mode) && $this->api_config->gateway_mode == 1);
            $bd_api_base   = $is_production ? 'https://api.billdesk.com/payments/ve1_2/'           : 'https://uat1.billdesk.com/u2/payments/ve1_2/';
            $bd_sdk_base   = $is_production ? 'https://pay.billdesk.com/v1_2/embeddedsdk'          : 'https://uat1.billdesk.com/u2/web/v1_2/embeddedsdk';
            log_message('error', 'BILLDESK_DEBUG_PAY: gateway_mode=' . $this->api_config->gateway_mode . ' is_production=' . ($is_production ? 'true' : 'false') . ' api_base=' . $bd_api_base);

            // Step 2: Ecom Order
            $ecom_payload = [
                'mercid' => $this->api_config->api_secret_key,
                'amount' => $formatted_amount,
                'order_ref_no' => $ecom_order_ref_no,
                'ecom_order_date' => date('Y-m-d\TH:i:sP'),
                'ru' => base_url('user/gateway/billdesk/callback'),
                'currency' => '356',
                'itemcode' => 'DIRECT',
                'additional_info' => $ecom_additional_info,
                'split_payment' => !empty($split_payment_payload) ? $split_payment_payload : null 
            ];
            
            if (empty($ecom_payload['split_payment'])) {
                unset($ecom_payload['split_payment']);
            }

            log_message('error', 'BILLDESK_UAT_DATA: Pay - Ecom Payload (' . $module . '): ' . json_encode($ecom_payload, JSON_PRETTY_PRINT));
            
            $ecom_jwe_token = $this->billdesk_lib->create_jwe($ecom_payload);
            $ecom_jws_token = $this->billdesk_lib->create_jws($ecom_jwe_token);

            $ecom_headers = [
                'Content-Type: application/jose',
                'Accept: application/jose',
                'BD-Traceid: ' . uniqid(),
                'BD-Timestamp: ' . date('YmdHis'),
            ];

            log_message('error', 'BILLDESK_UAT_DATA: 2. Original encrypted & signed ecom Order API request strings, BD-TraceID & BD-Timestamp: Request String=' . $ecom_jws_token);
            log_message('error', 'BILLDESK_UAT_DATA: 2. Ecom Order Request Headers: ' . json_encode($ecom_headers));

            $ch_ecom = curl_init();
            curl_setopt($ch_ecom, CURLOPT_URL, $bd_api_base . 'ecomorders/create');
            curl_setopt($ch_ecom, CURLOPT_POST, 1);
            curl_setopt($ch_ecom, CURLOPT_POSTFIELDS, $ecom_jws_token);
            curl_setopt($ch_ecom, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_ecom, CURLOPT_HTTPHEADER, $ecom_headers);

            log_message('error', 'Billdesk Ecom Order Request URL: ' . $bd_api_base . 'ecomorders/create');
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
                curl_setopt($ch_trans, CURLOPT_URL, $bd_api_base . 'orders/create');
                curl_setopt($ch_trans, CURLOPT_POST, 1);
                curl_setopt($ch_trans, CURLOPT_POSTFIELDS, $trans_jws_token);
                curl_setopt($ch_trans, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_trans, CURLOPT_HTTPHEADER, $trans_headers);

                log_message('error', 'Billdesk Transaction Request URL: ' . $bd_api_base . 'orders/create');
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
                log_message('error', 'DECODE THIS STRING TO SEE THE FULL TRANSACTION PAYLOAD: ' . base64_encode(json_encode($decrypted_trans_response)));
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
                $redirect_url = $bd_sdk_base; // Default URL; may be overridden by response links

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
                    'module' => $gateway_module, // Use the determined gateway_module
                    'payment_status' => 'processing',
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $gateway_ins_id = $this->gateway_ins_model->add_gateway_ins($ins_data);

                // Store gateway_ins_id in session for callback
                $current_params = $this->session->userdata('params');
                $current_params['gateway_ins_id'] = $gateway_ins_id;
                $this->session->set_userdata('params', $current_params);

                $this->load->view('user/gateway/billdesk/redirect', $data);
            }

        } catch (Exception $e) {
            log_message('error', 'BILLDESK_PAY_EXCEPTION: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Payment initiation failed: ' . $e->getMessage());
            // Redirect to a generic error page, or based on module type
            if ($module == 'online_admission_fee') {
                redirect(base_url("publicadmissionform/confirm_payment")); // Go back to confirmation page
            } elseif ($module == 'online_course_fee') {
                redirect(base_url("public_admission/applicant_dashboard"));
            } else {
                redirect($_SERVER['HTTP_REFERER'] ?? base_url());
            }
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

                // Retrieve the gateway_ins record using unique_id and gateway_name
                // This is crucial to get the 'id' of the gateway_ins record for gateway_ins_response linkage
                $gateway_ins_record = $this->gateway_ins_model->get_gateway_ins($response['orderid'], 'billdesk');
                $gateway_ins_id = null;
                if (!empty($gateway_ins_record)) {
                    $gateway_ins_id = $gateway_ins_record['id'];
                }

                                                // Log raw gateway response to gateway_ins_response table
                                                $gateway_ins_response_data = [
                                                    'gateway_ins_id' => $gateway_ins_id,
                                                    'posted_data' => json_encode($_POST), // Assuming $_POST contains the raw data from gateway
                                                    'response' => json_encode($response), // Use 'response' column for the decrypted response
                                                    'created_at' => date('Y-m-d H:i:s')
                                                ];                                log_message('error', 'BILLDESK_DEBUG: Data for gateway_ins_response_data: ' . json_encode($gateway_ins_response_data));
                                $add_response_result = $this->gateway_ins_model->add_gateway_ins_response($gateway_ins_response_data);
                                log_message('error', 'BILLDESK_DEBUG: Result of add_gateway_ins_response (insert_id): ' . ($add_response_result ? $add_response_result : 'FALSE/0'));
                // Now update the gateway_ins status with the determined status
                // Using the unique_id and gateway_name, which update_gateway_ins now supports
                $this->gateway_ins_model->update_gateway_ins(array(
                    'unique_id' => $response['orderid'], 
                    'gateway_name' => 'billdesk', 
                    'payment_status' => $gateway_ins_status
                ));

                // Determine module from the gateway_ins_record's parameter_details
                $params_from_gateway_ins = json_decode($gateway_ins_record['parameter_details'], true);
                $module = $params_from_gateway_ins['item_type'] ?? 'fees';

                if (isset($response['auth_status']) && $response['auth_status'] == '0300') {
                    // Payment successful
                    
                    // Step 8: Verify Transaction via API
                    $verification_response = $this->billdesk_lib->verify_transaction($response['orderid']);
                    
                    if (isset($verification_response['auth_status']) && $verification_response['auth_status'] == '0300') {
                        $response = $verification_response; // Use the verified response
                    } else {
                        log_message('error', "Billdesk Transaction Verification Failed: " . (isset($verification_response['message']) ? $verification_response['message'] : 'Unknown Error'));
                    }

                    if ($module == 'fees') {
                        $transaction_id = $response['transactionid'];
                        $bulk_fees = array();

                        if (!empty($params_from_gateway_ins['student_fees_master_array'])) {
                            foreach ($params_from_gateway_ins['student_fees_master_array'] as $fee_key => $fee_value) {
                            
                                $json_array = array(
                                    'amount'          => $fee_value['amount_balance'],
                                    'date'            => date('Y-m-d'),
                                    'amount_discount' => $fee_value['applied_fee_discount'] ?? null,
                                    'processing_charge_type' => $params_from_gateway_ins['processing_charge_type'],
                                    'gateway_processing_charge' => $params_from_gateway_ins['gateway_processing_charge'],
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

                        log_message('error', 'Billdesk Callback - Bulk Fees Payload: ' . json_encode($bulk_fees));
                        $send_to = $params_from_gateway_ins['guardian_phone'];
                        $response_bulk_deposit = $this->studentfeemaster_model->add_bulk_fee_deposit($bulk_fees, $params_from_gateway_ins['fee_discount_group'] ?? null);

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
                    } elseif ($module == 'online_admission_fee') {
                        $online_admission_id = $params_from_gateway_ins['online_admission_id'];
                        $transaction_id = $response['transactionid'];

                        // Use admission_amount (excluding processing charge) for the receipt,
                        // so incidental_fee_collections shows Rs.1000 not Rs.1020.
                        $admission_fee_amount = (float) ($params_from_gateway_ins['admission_amount'] ?? $response['amount']);

                        $payment_data = [
                            'online_admission_id' => $online_admission_id,
                            'transaction_id'      => $transaction_id,
                            'paid_amount'         => $admission_fee_amount,
                            'payment_mode'        => 'Billdesk',
                            'payment_type'        => 'online_admission',
                            'note'                => 'Online payment via BillDesk. TXN: ' . $transaction_id,
                            'date'                => date('Y-m-d H:i:s'),
                            'paid_status'         => 1,
                        ];

                        $this->onlinestudent_model->paymentSuccess($payment_data);

                        // Send Email/SMS/WhatsApp notification for application fee payment
                        $online_data = $this->onlinestudent_model->get($online_admission_id);
                        if (!empty($online_data)) {
                            $fees_sender_details = array(
                                'firstname'      => $online_data['firstname'],
                                'lastname'       => $online_data['lastname'],
                                'email'          => $online_data['email'],
                                'mobileno'       => $online_data['mobileno'],
                                'guardian_email' => $online_data['guardian_email'],
                                'guardian_phone' => $online_data['guardian_phone'],
                                'reference_no'   => $online_data['reference_no'],
                                'amount'         => number_format($admission_fee_amount, 2),
                                'date'           => date('d-m-Y'),
                                'transaction_id' => $transaction_id,
                                'payment_mode'   => 'Billdesk',
                            );
                            $this->mailsmsconf->mailsms('online_admission_fees_submission', $fees_sender_details);
                        }

                        $this->session->set_flashdata('msg', '<div class="alert alert-success">Payment successful! Your application fee has been received.</div>');
                        $this->session->set_flashdata('show_app_fee_success', 1);
                        redirect(base_url("public_admission/applicant_dashboard"));
                    } elseif ($module == 'online_course_fee') {
                        $online_admission_id = $params_from_gateway_ins['online_admission_id'];
                        $transaction_id = $response['transactionid'];

                        // Use course_fee_amount (excluding processing charge) for the receipt
                        $course_fee_amount = (float) ($params_from_gateway_ins['course_fee_amount'] ?? $response['amount']);

                        $payment_data = [
                            'online_admission_id' => $online_admission_id,
                            'transaction_id'      => $transaction_id,
                            'paid_amount'         => $course_fee_amount,
                            'payment_mode'        => 'Billdesk',
                            'payment_type'        => 'course_fee',
                            'note'                => 'Course fee online payment via BillDesk. TXN: ' . $transaction_id,
                            'date'                => date('Y-m-d H:i:s'),
                            'paid_status'         => 1,
                        ];

                        $this->onlinestudent_model->courseFeePaidSuccess($payment_data);

                        $this->session->set_flashdata('msg', '<div class="alert alert-success">Course fee payment successful!</div>');
                        redirect(base_url("public_admission/applicant_dashboard"));
                    }

                } elseif (isset($response['auth_status']) && $response['auth_status'] == '0002') {
                    // Payment Pending
                    // Determine module based on params for redirection
                    if ($module == 'online_admission_fee') {
                        $this->session->set_flashdata('msg', '<div class="alert alert-warning">Payment is pending. Please check with your bank.</div>');
                        redirect(base_url("public_admission/applicant_dashboard"));
                    } elseif ($module == 'online_course_fee') {
                        $this->session->set_flashdata('msg', '<div class="alert alert-warning">Course fee payment is pending. Please check with your bank.</div>');
                        redirect(base_url("public_admission/applicant_dashboard"));
                    } else {
                        $this->pending($response);
                    }

                } else {
                    // Payment failed
                    // Determine module based on params for redirection
                    if ($module == 'online_admission_fee') {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger">Payment failed. Please try again.</div>');
                        redirect(base_url("public_admission/applicant_dashboard"));
                    } elseif ($module == 'online_course_fee') {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger">Course fee payment failed. Please try again.</div>');
                        redirect(base_url("public_admission/applicant_dashboard"));
                    } else {
                        $this->fail($response);
                    }
                }
            } catch (Exception $e) {
                // Invalid response
                log_message('error', 'Billdesk Callback Exception: ' . $e->getMessage());
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Payment processing error. Please try again.</div>');
                // Use gateway_ins_record if available, otherwise fall back to session params
                if (!empty($gateway_ins_record['parameter_details'])) {
                    $params_from_gateway_ins = json_decode($gateway_ins_record['parameter_details'], true);
                } else {
                    $params_from_gateway_ins = $this->session->userdata('params') ?? [];
                }
                $module = $params_from_gateway_ins['item_type'] ?? 'fees';
                if ($module == 'online_admission_fee') {
                    redirect(base_url("public_admission/applicant_dashboard"));
                } elseif ($module == 'online_course_fee') {
                    redirect(base_url("public_admission/applicant_dashboard"));
                } else {
                    $this->fail(['transaction_error_desc' => $e->getMessage()]);
                }
            }
        } else {
            // No POST data — user cancelled from the BillDesk window
            log_message('error', 'Billdesk Callback: Empty transaction_response POST data (possible user cancellation).');
            $this->session->set_flashdata('error', 'Payment was cancelled. You have not been charged.');
            $session_params = $this->session->userdata('params');
            $module = $session_params['item_type'] ?? 'fees';
            if ($module == 'online_admission_fee') {
                redirect(base_url("public_admission/applicant_dashboard"));
            } elseif ($module == 'online_course_fee') {
                redirect(base_url("public_admission/applicant_dashboard"));
            } else {
                // Return student to their fees page
                redirect(base_url('user/user/getfees'));
            }
        }
    }

    private function success($response)
    {
        $this->session->set_userdata('top_menu', 'fees');
        $this->session->set_userdata('sub_menu', 'student/getFees');
        redirect(base_url('user/gateway/payment/successinvoice'));
    }

    private function fail($response)
    {
        $this->session->set_flashdata('error', 'Payment failed. Please try again or contact support.');
        redirect(base_url('user/gateway/payment/paymentfailed'));
    }

    private function pending($response)
    {
        $this->session->set_flashdata('msg', '<div class="alert alert-warning">Payment is being processed. You will be notified once confirmed.</div>');
        redirect(base_url('user/gateway/payment/paymentprocessing'));
    }
}