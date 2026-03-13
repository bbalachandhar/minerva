<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leadapi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('enquiry_model');
    }

    public function create_enquiry()
    {
        $method = strtoupper((string) $this->input->server('REQUEST_METHOD'));

        if ($method === 'OPTIONS') {
            $this->output->set_status_header(200)->set_output('');
            return;
        }

        if ($method !== 'POST') {
            return $this->respond(405, [
                'status' => 0,
                'message' => 'Method not allowed. Received: ' . $method . '. This endpoint only accepts POST requests.',
            ]);
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            return $this->respond(400, [
                'status' => 0,
                'message' => 'Invalid JSON payload.',
            ]);
        }

        $vendor_code = trim((string) $this->input->get_request_header('X-Vendor-Code', true));
        $api_key = trim((string) $this->input->get_request_header('X-Api-Key', true));

        // Allow body credentials too, so vendor can integrate without header support.
        if ($vendor_code === '' && isset($payload['vendor_code'])) {
            $vendor_code = trim((string) $payload['vendor_code']);
        }
        if ($api_key === '' && isset($payload['api_key'])) {
            $api_key = trim((string) $payload['api_key']);
        }

        $vendor_code = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $vendor_code));

        if ($vendor_code === '' || $api_key === '') {
            return $this->respond(401, [
                'status' => 0,
                'message' => 'Unauthorized. vendor_code and api_key are required.',
            ]);
        }

        if (!$this->db->table_exists('lead_api_vendors')) {
            return $this->respond(500, [
                'status' => 0,
                'message' => 'API vendor table is missing. Please run DB update 2026_03_13_create_lead_api_vendors_table.sql.',
            ]);
        }

        $vendor = $this->db
            ->where('vendor_code', $vendor_code)
            ->where('is_active', 1)
            ->limit(1)
            ->get('lead_api_vendors')
            ->row_array();

        if (empty($vendor)) {
            return $this->respond(401, [
                'status' => 0,
                'message' => 'Unauthorized. Invalid vendor_code.',
            ]);
        }

        $hash = (string) ($vendor['api_key_hash'] ?? '');
        $is_valid_key = $hash !== '' && (password_verify($api_key, $hash) || hash_equals($hash, $api_key));
        if (!$is_valid_key) {
            return $this->respond(401, [
                'status' => 0,
                'message' => 'Unauthorized. Invalid api_key.',
            ]);
        }

        $param = isset($payload['param']) && is_array($payload['param']) ? $payload['param'] : $payload;

        $name = trim((string) ($param['applicantname'] ?? ''));
        $mobile_raw = trim((string) ($param['mobilenumber'] ?? ''));
        $email_raw = strtolower(trim((string) ($param['emailid'] ?? '')));

        if ($name === '' || $mobile_raw === '') {
            return $this->respond(422, [
                'status' => 0,
                'message' => 'Validation failed.',
                'errors' => [
                    'applicantname' => $name === '' ? 'applicantname is required.' : '',
                    'mobilenumber' => $mobile_raw === '' ? 'mobilenumber is required.' : '',
                ],
            ]);
        }

        $mobile_digits = preg_replace('/\D+/', '', $mobile_raw);
        $mobile_variants = array_values(array_filter(array_unique([
            $mobile_raw,
            $mobile_digits,
            ltrim($mobile_digits, '0'),
            strlen($mobile_digits) >= 10 ? substr($mobile_digits, -10) : '',
        ])));

        $this->db->select('e.id, e.name, e.contact, e.email, e.date, e.ref_no, e.lead_vendor_id, v.vendor_name as lead_vendor_name');
        $this->db->from('enquiry e');
        $this->db->join('lead_api_vendors v', 'v.id = e.lead_vendor_id', 'left');
        if (!empty($mobile_variants) && $email_raw !== '') {
            $this->db->group_start()
                ->where_in('contact', $mobile_variants)
                ->or_where('LOWER(email)', $email_raw)
                ->group_end();
        } elseif (!empty($mobile_variants)) {
            $this->db->where_in('contact', $mobile_variants);
        } elseif ($email_raw !== '') {
            $this->db->where('LOWER(email)', $email_raw);
        }
        $duplicate_rows = $this->db->order_by('e.id', 'ASC')->limit(50)->get()->result_array();
        $duplicate_entries = [];
        $duplicate_source_vendor_id = null;
        $duplicate_source_vendor_name = '';
        foreach ($duplicate_rows as $duplicate_row) {
            $matched_by = [];
            if (in_array((string) ($duplicate_row['contact'] ?? ''), $mobile_variants, true)) {
                $matched_by[] = 'mobilenumber';
            }
            if ($email_raw !== '' && strtolower((string) ($duplicate_row['email'] ?? '')) === $email_raw) {
                $matched_by[] = 'emailid';
            }

            $row_vendor_id = (int) ($duplicate_row['lead_vendor_id'] ?? 0);
            $current_vendor_id = (int) ($vendor['id'] ?? 0);
            if ($row_vendor_id > 0) {
                if ($duplicate_source_vendor_id === null) {
                    $duplicate_source_vendor_id = $row_vendor_id;
                    $duplicate_source_vendor_name = (string) ($duplicate_row['lead_vendor_name'] ?? '');
                }
                if (
                    $current_vendor_id > 0 &&
                    $row_vendor_id !== $current_vendor_id &&
                    $duplicate_source_vendor_id === $current_vendor_id
                ) {
                    // Prefer source from another vendor when both self and external matches exist.
                    $duplicate_source_vendor_id = $row_vendor_id;
                    $duplicate_source_vendor_name = (string) ($duplicate_row['lead_vendor_name'] ?? '');
                }
            }

            $duplicate_entries[] = [
                'id' => (int) $duplicate_row['id'],
                'name' => (string) ($duplicate_row['name'] ?? ''),
                'contact' => (string) ($duplicate_row['contact'] ?? ''),
                'email' => (string) ($duplicate_row['email'] ?? ''),
                'date' => (string) ($duplicate_row['date'] ?? ''),
                'ref_no' => (string) ($duplicate_row['ref_no'] ?? ''),
                'lead_vendor_id' => $row_vendor_id > 0 ? $row_vendor_id : null,
                'lead_vendor_name' => (string) ($duplicate_row['lead_vendor_name'] ?? ''),
                'matched_by' => $matched_by,
            ];
        }

        if (count($duplicate_entries) > 10) {
            $duplicate_entries = array_slice($duplicate_entries, 0, 10);
        }

        $program_name = trim((string) ($param['programname'] ?? ''));
        $program_id_raw = trim((string) ($param['programid'] ?? ''));
        $application_category = trim((string) ($param['applicationcategory'] ?? ''));
        $application_category_id = trim((string) ($param['applicationcategoryid'] ?? ''));
        $campus_name = trim((string) ($param['campusname'] ?? ''));
        $campus_id = trim((string) ($param['campusid'] ?? ''));

        $admission_course_id = null;
        $course_level = null;
        $admission_type = null;

        if ($program_id_raw !== '' && ctype_digit($program_id_raw)) {
            $course = $this->db
                ->select('id, course_level, admission_type')
                ->where('id', (int) $program_id_raw)
                ->limit(1)
                ->get('online_admission_courses')
                ->row_array();
            if (!empty($course)) {
                $admission_course_id = (int) $course['id'];
                $course_level = $course['course_level'] ?? null;
                $admission_type = $course['admission_type'] ?? null;
            }
        }

        if ($admission_course_id === null && $program_name !== '') {
            $course = $this->db
                ->select('id, course_level, admission_type')
                ->like('course_name', $program_name)
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get('online_admission_courses')
                ->row_array();
            if (!empty($course)) {
                $admission_course_id = (int) $course['id'];
                $course_level = $course['course_level'] ?? null;
                $admission_type = $course['admission_type'] ?? null;
            }
        }

        $reference_no = 'ENQ-API-' . date('YmdHis') . rand(100, 999);
        $source = substr('API - ' . strtoupper($vendor_code), 0, 50);

        $notes = [
            'vendor=' . $vendor_code,
            'vendor_request_id=' . (isset($payload['id']) ? (string) $payload['id'] : ''),
            'programid=' . $program_id_raw,
            'applicationcategory=' . $application_category,
            'applicationcategoryid=' . $application_category_id,
            'campusname=' . $campus_name,
            'campusid=' . $campus_id,
        ];

        $enquiry_data = [
            'name' => $name,
            'contact' => substr($mobile_raw, 0, 20),
            'email' => $email_raw !== '' ? substr($email_raw, 0, 50) : null,
            'address' => isset($param['address']) ? (string) $param['address'] : '',
            'state' => isset($param['state']) ? (string) $param['state'] : null,
            'city' => isset($param['city']) ? (string) $param['city'] : null,
            'reference' => substr($vendor_code, 0, 20),
            'reference_name' => $vendor['vendor_name'] ?? strtoupper($vendor_code),
            'reference_contact' => isset($param['referencecontact']) ? substr((string) $param['referencecontact'], 0, 255) : null,
            'date' => date('Y-m-d'),
            'follow_up_date' => date('Y-m-d'),
            'description' => $program_name !== '' ? substr('Program: ' . $program_name, 0, 500) : '',
            'note' => implode('; ', array_filter($notes)),
            'source' => $source,
            'lead_vendor_id' => (int) ($vendor['id'] ?? 0) > 0 ? (int) $vendor['id'] : null,
            'assigned' => null,
            'class_id' => null,
            'admission_course_id' => $admission_course_id,
            'course_level' => in_array($course_level, ['ug', 'pg'], true) ? $course_level : null,
            'admission_type' => in_array($admission_type, ['first_year', 'lateral'], true) ? $admission_type : null,
            'status' => 'active',
            'created_by' => !empty($vendor['created_by']) ? (int) $vendor['created_by'] : 1,
            'ref_no' => $reference_no,
        ];

        if ($this->db->field_exists('duplicate_source_vendor_id', 'enquiry')) {
            $enquiry_data['duplicate_source_vendor_id'] = $duplicate_source_vendor_id;
        }

        $this->db->insert('enquiry', $enquiry_data);
        $insert_id = (int) $this->db->insert_id();

        if ($insert_id <= 0) {
            $db_error = $this->db->error();
            log_message('error', 'Lead API enquiry insert failed: ' . json_encode($db_error));
            return $this->respond(500, [
                'status' => 0,
                'message' => 'Failed to create enquiry.',
            ]);
        }

        $this->db->where('id', (int) $vendor['id'])->update('lead_api_vendors', [
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);

        $has_duplicate = !empty($duplicate_entries);

        return $this->respond(201, [
            'status' => 1,
            'message' => $has_duplicate ? 'Enquiry created successfully. Duplicate entries found.' : 'Enquiry created successfully.',
            'duplicate' => $has_duplicate ? 1 : 0,
            'duplicate_count' => count($duplicate_entries),
            'duplicate_source_vendor_id' => $duplicate_source_vendor_id,
            'duplicate_source_vendor_name' => $duplicate_source_vendor_name,
            'existing_duplicates' => $duplicate_entries,
            'data' => [
                'enquiry_id' => $insert_id,
                'ref_no' => $reference_no,
            ],
        ]);
    }

    private function respond($http_code, $data)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($http_code)
            ->set_output(json_encode($data));
    }
}
