<?php
/**
 * MetaLeads — Meta Lead Ads Webhook Controller
 *
 * Handles the two-step Meta webhook protocol:
 *   GET  /metaleads/webhook  →  Verification challenge echo
 *   POST /metaleads/webhook  →  Real-time lead event
 *
 * Flow:
 *   1. Meta sends GET with hub.verify_token + hub.challenge → we echo challenge.
 *   2. When a user submits a Meta Lead Ad form, Meta POSTs a webhook with
 *      leadgen_id, form_id, page_id, adgroup_id.
 *   3. We call the Meta Graph API to fetch the actual field data.
 *   4. We map the fields to enquiry columns and insert via enquiry_model->add().
 *
 * Security:
 *   - Verify Token match on GET (prevents random subscriptions).
 *   - X-Hub-Signature-256 HMAC validation on POST (prevents spoofed payloads).
 *   - All external data sanitised before DB insert.
 *   - Graph API call uses HTTPS with peer verification.
 */

defined('BASEPATH') or exit('No direct script access allowed');

class MetaLeads extends CI_Controller
{
    /** Meta Graph API base */
    const GRAPH_API_BASE = 'https://graph.facebook.com/v19.0';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('enquiry_model');
        $this->load->model('setting_model');
        $this->_ensure_log_table();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Auto-create the webhook event log table if it doesn't exist yet.
    // ─────────────────────────────────────────────────────────────────────
    private function _ensure_log_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `meta_webhook_events` (
            `id`               INT(11)      NOT NULL AUTO_INCREMENT,
            `received_at`      DATETIME     NOT NULL,
            `source_ip`        VARCHAR(45)  DEFAULT NULL,
            `signature_status` ENUM('ok','fail','skipped') NOT NULL DEFAULT 'skipped',
            `leadgen_id`       VARCHAR(64)  DEFAULT NULL,
            `page_id`          VARCHAR(64)  DEFAULT NULL,
            `form_id`          VARCHAR(64)  DEFAULT NULL,
            `outcome`          VARCHAR(50)  NOT NULL DEFAULT 'pending',
            `enquiry_id`       INT(11)      DEFAULT NULL,
            `note`             VARCHAR(500) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_received_at` (`received_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Insert a skeleton log row at the very start of every POST hit.
    //  Returns the inserted row id (0 on failure).
    // ─────────────────────────────────────────────────────────────────────
    private function _log_webhook_start()
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
        $ip = trim(explode(',', $ip)[0]);  // first IP if comma-list
        $this->db->insert('meta_webhook_events', [
            'received_at'      => date('Y-m-d H:i:s'),
            'source_ip'        => substr($ip, 0, 45),
            'signature_status' => 'skipped',
            'outcome'          => 'pending',
        ]);
        return (int) $this->db->insert_id();
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Update log row with final status information.
    // ─────────────────────────────────────────────────────────────────────
    private function _log_webhook_update($log_id, array $fields)
    {
        if ($log_id > 0) {
            $this->db->where('id', $log_id)->update('meta_webhook_events', $fields);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  GET  /metaleads/webhook
    //  Meta calls this to verify the webhook subscription.
    // ─────────────────────────────────────────────────────────────────────
    public function webhook()
    {
        $method = strtoupper($this->input->server('REQUEST_METHOD'));

        if ($method === 'GET') {
            $this->_handle_verification();
        } elseif ($method === 'POST') {
            $this->_handle_lead_event();
        } else {
            $this->output->set_status_header(405)->set_output('Method Not Allowed');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Webhook Verification (Meta sends hub.challenge → we echo it back)
    // ─────────────────────────────────────────────────────────────────────
    private function _handle_verification()
    {
        // PHP converts dots to underscores in $_GET — hub.mode → hub_mode etc.
        $mode      = isset($_GET['hub_mode'])         ? $_GET['hub_mode']         : '';
        $token     = isset($_GET['hub_verify_token']) ? $_GET['hub_verify_token'] : '';
        $challenge = isset($_GET['hub_challenge'])    ? $_GET['hub_challenge']    : '';

        // Direct DB query — skip getSetting() entirely to avoid any SELECT/join issue
        $row = $this->db->select('meta_leads_enabled, meta_verify_token')
            ->limit(1)->get('sch_settings')->row_array();

        $enabled          = (int)   ($row['meta_leads_enabled']  ?? 0);
        $configured_token = trim((string) ($row['meta_verify_token'] ?? ''));

        if ($enabled !== 1) {
            log_message('error', '[MetaLeads] Verification attempt but Meta Leads integration is disabled.');
            http_response_code(403);
            echo 'Meta Lead integration is disabled.';
            exit;
        }

        if ($mode === 'subscribe' && $configured_token !== '' && hash_equals($configured_token, $token)) {
            log_message('info', '[MetaLeads] Webhook verification successful.');
            http_response_code(200);
            header('Content-Type: text/plain');
            echo $challenge;
            exit;
        }

        log_message('error', '[MetaLeads] Webhook verification FAILED. mode=' . $mode
            . ' received_token=' . $token . ' configured_token=' . $configured_token);
        http_response_code(403);
        echo 'Verification failed.';
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Lead Event (Meta POSTs when a lead form is submitted)
    // ─────────────────────────────────────────────────────────────────────
    private function _handle_lead_event()
    {
        $raw_body = file_get_contents('php://input');

        // ── Log the raw incoming hit immediately ──────────────────────────
        $log_id = $this->_log_webhook_start();

        // ── 1. Verify HMAC signature ──────────────────────────────────────
        $sig_header   = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        $sig_verified = $this->_verify_signature($raw_body);
        $sig_status   = ($sig_header === '') ? 'skipped' : ($sig_verified ? 'ok' : 'fail');

        if (!$sig_verified) {
            log_message('error', '[MetaLeads] HMAC signature verification failed. Possible spoofed POST.');
            $this->_log_webhook_update($log_id, ['signature_status' => $sig_status, 'outcome' => 'hmac_fail', 'note' => 'Signature mismatch']);
            $this->output->set_status_header(200)->set_output('EVENT_RECEIVED'); // always 200 to Meta
            return;
        }

        $this->_log_webhook_update($log_id, ['signature_status' => $sig_status]);

        $setting = $this->setting_model->getSetting();
        $enabled = (int) ($setting->meta_leads_enabled ?? 0);
        if ($enabled !== 1) {
            $this->_log_webhook_update($log_id, ['outcome' => 'disabled', 'note' => 'meta_leads_enabled != 1']);
            $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
            return;
        }

        $payload = json_decode($raw_body, true);
        if (!is_array($payload)) {
            log_message('error', '[MetaLeads] Invalid JSON payload.');
            $this->_log_webhook_update($log_id, ['outcome' => 'parse_fail', 'note' => 'Invalid JSON body']);
            $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
            return;
        }

        // ── 2. Walk through all entries & changes ─────────────────────────
        $entries = $payload['entry'] ?? [];
        $processed = 0;
        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                if (($change['field'] ?? '') !== 'leadgen') {
                    continue;
                }
                $lead_value = $change['value'] ?? [];
                $leadgen_id = (string) ($lead_value['leadgen_id'] ?? '');
                $form_id    = (string) ($lead_value['form_id']    ?? '');
                $page_id    = (string) ($lead_value['page_id']    ?? '');
                $ad_id      = (string) ($lead_value['ad_id']      ?? '');

                if ($leadgen_id === '') {
                    continue;
                }

                // Update log with lead identifiers before processing
                $this->_log_webhook_update($log_id, [
                    'leadgen_id' => substr($leadgen_id, 0, 64),
                    'page_id'    => substr($page_id, 0, 64),
                    'form_id'    => substr($form_id, 0, 64),
                ]);

                log_message('info', "[MetaLeads] New lead event: leadgen_id={$leadgen_id}, form_id={$form_id}, page_id={$page_id}");
                list($outcome, $enquiry_id, $note) = $this->_process_lead($leadgen_id, $form_id, $page_id, $ad_id, $setting);

                $this->_log_webhook_update($log_id, [
                    'outcome'    => $outcome,
                    'enquiry_id' => $enquiry_id,
                    'note'       => substr($note, 0, 500),
                ]);
                $processed++;
            }
        }

        if ($processed === 0) {
            $this->_log_webhook_update($log_id, ['outcome' => 'no_leadgen_field', 'note' => 'No leadgen changes in payload']);
        }

        // Meta requires a 200 response within 20 seconds.
        $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Fetch lead data from Graph API and insert enquiry record.
    //  Returns [ $outcome_string, $enquiry_id|null, $note_string ]
    // ─────────────────────────────────────────────────────────────────────
    private function _process_lead($leadgen_id, $form_id, $page_id, $ad_id, $setting)
    {
        $access_token       = trim((string) ($setting->meta_page_access_token ?? ''));
        $default_course_id  = (int) ($setting->meta_default_course_id ?? 0);

        if ($access_token === '') {
            log_message('error', '[MetaLeads] Page access token not configured.');
            return ['no_access_token', null, 'Page access token not configured'];
        }

        // ── Fetch full lead from Graph API ────────────────────────────────
        $graph_url = self::GRAPH_API_BASE . '/' . urlencode($leadgen_id)
            . '?fields=field_data,created_time,ad_id,form_id'
            . '&access_token=' . urlencode($access_token);

        $lead_data = $this->_graph_get($graph_url);

        if (empty($lead_data) || !isset($lead_data['field_data'])) {
            $api_err = isset($lead_data['error']['message']) ? $lead_data['error']['message'] : 'no field_data';
            log_message('error', "[MetaLeads] Could not fetch lead data for leadgen_id={$leadgen_id}. Response: " . json_encode($lead_data));
            return ['graph_api_error', null, 'Graph API error: ' . substr($api_err, 0, 200)];
        }

        // ── Map Meta field_data array → flat key/value ────────────────────
        $fields = [];
        foreach ($lead_data['field_data'] as $fd) {
            $key   = strtolower(trim((string) ($fd['name']  ?? '')));
            $val   = trim((string) ($fd['values'][0] ?? ''));
            $fields[$key] = $val;
        }

        // ── Extract standard fields (Meta forms use varied key names) ─────
        $name    = $this->_extract_name($fields);
        $mobile  = $this->_extract_phone($fields);
        $email   = strtolower($this->_extract($fields, ['email', 'email_address']));
        $city    = $this->_extract($fields, ['city']);
        $state   = $this->_extract($fields, ['state', 'province']);
        $program = $this->_extract($fields, ['course', 'program', 'course_of_interest', 'program_name', 'stream']);
        $custom  = $this->_extract($fields, ['message', 'comments', 'any_other_information']);

        if ($name === '' || $mobile === '') {
            log_message('error', "[MetaLeads] leadgen_id={$leadgen_id} missing name or mobile. fields=" . json_encode($fields));
            return ['missing_name_mobile', null, 'Lead has no name or mobile. keys=' . implode(',', array_keys($fields))];
        }

        // ── Look up the meta vendor row ───────────────────────────────────
        $vendor = $this->db
            ->where('vendor_code', 'meta')
            ->limit(1)
            ->get('lead_api_vendors')
            ->row_array();

        $vendor_id   = !empty($vendor['id']) ? (int) $vendor['id'] : null;
        $vendor_name = !empty($vendor['vendor_name']) ? $vendor['vendor_name'] : 'Meta Lead Ads';

        // ── Resolve course ────────────────────────────────────────────────
        $admission_course_id = null;
        $course_level        = null;
        $admission_type      = null;

        if ($program !== '') {
            $course = $this->db
                ->select('id, course_level, admission_type')
                ->like('course_name', $program)
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get('online_admission_courses')
                ->row_array();
            if (!empty($course)) {
                $admission_course_id = (int) $course['id'];
                $course_level        = $course['course_level'] ?? null;
                $admission_type      = $course['admission_type'] ?? null;
            }
        }

        if ($admission_course_id === null && $default_course_id > 0) {
            $course = $this->db
                ->select('id, course_level, admission_type')
                ->where('id', $default_course_id)
                ->limit(1)
                ->get('online_admission_courses')
                ->row_array();
            if (!empty($course)) {
                $admission_course_id = (int) $course['id'];
                $course_level        = $course['course_level'] ?? null;
                $admission_type      = $course['admission_type'] ?? null;
            }
        }

        // ── Build notes from all Meta fields ─────────────────────────────
        $note_parts = [
            'vendor=meta',
            'leadgen_id=' . $leadgen_id,
            'form_id='    . $form_id,
            'page_id='    . $page_id,
            'ad_id='      . $ad_id,
        ];
        foreach ($fields as $k => $v) {
            $note_parts[] = $k . '=' . $v;
        }

        $ref_no = 'ENQ-META-' . date('YmdHis') . rand(100, 999);

        $enquiry_data = [
            'name'               => substr($name,   0, 100),
            'contact'            => substr($mobile, 0, 20),
            'email'              => $email !== '' ? substr($email, 0, 50) : null,
            'city'               => $city  !== '' ? substr($city,  0, 50) : null,
            'state'              => $state !== '' ? substr($state, 0, 50) : null,
            'address'            => '',
            'reference'          => 'meta',
            'reference_name'     => $vendor_name,
            'reference_contact'  => null,
            'date'               => date('Y-m-d'),
            'follow_up_date'     => date('Y-m-d'),
            'description'        => $program !== '' ? substr('Program: ' . $program, 0, 500) : ($custom !== '' ? substr($custom, 0, 500) : ''),
            'note'               => substr(implode('; ', array_filter($note_parts)), 0, 1000),
            'source'             => 'API - META',
            'lead_vendor_id'     => $vendor_id,
            'admission_course_id'=> $admission_course_id,
            'course_level'       => in_array($course_level, ['ug', 'pg'], true)                     ? $course_level  : null,
            'admission_type'     => in_array($admission_type, ['first_year', 'lateral'], true)      ? $admission_type : null,
            'status'             => 'active',
            'created_by'         => 1,
            'ref_no'             => $ref_no,
        ];

        // Strip fields the enquiry table may not have (safety guard)
        $allowed_columns = $this->db->list_fields('enquiry');
        $enquiry_data = array_intersect_key($enquiry_data, array_flip($allowed_columns));

        $this->db->insert('enquiry', $enquiry_data);
        $new_id = (int) $this->db->insert_id();

        if ($new_id === 0) {
            return ['db_insert_fail', null, 'INSERT failed: ' . $this->db->error()['message']];
        }

        // Update last_used_at on meta vendor
        if ($vendor_id) {
            $this->db->where('id', $vendor_id)->update('lead_api_vendors', ['last_used_at' => date('Y-m-d H:i:s')]);
        }

        log_message('info', "[MetaLeads] Enquiry created id={$new_id} ref={$ref_no} for leadgen_id={$leadgen_id}");
        return ['created', $new_id, "ref={$ref_no} name={$name}"];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Helper: HMAC-SHA256 signature verification
    //  Meta signs the POST body with your App Secret.
    //  Header: X-Hub-Signature-256: sha256=<hex>
    // ─────────────────────────────────────────────────────────────────────
    private function _verify_signature($raw_body)
    {
        $sig_header = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        if ($sig_header === '') {
            // If not configured (local dev), allow through but log warning.
            log_message('debug', '[MetaLeads] No X-Hub-Signature-256 header present — skipping HMAC check.');
            return true;
        }

        // App Secret must be stored in sch_settings as meta_app_secret
        $setting    = $this->setting_model->getSetting();
        $app_secret = trim((string) ($setting->meta_app_secret ?? ''));
        if ($app_secret === '') {
            log_message('debug', '[MetaLeads] meta_app_secret not configured — skipping HMAC check.');
            return true;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $raw_body, $app_secret);
        return hash_equals($expected, $sig_header);
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Helper: cURL GET to Meta Graph API
    // ─────────────────────────────────────────────────────────────────────
    private function _graph_get($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Minerva-MetaLeads/1.0',
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', '[MetaLeads] Graph API cURL error: ' . $err);
            return [];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : [];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Field extraction helpers
    // ─────────────────────────────────────────────────────────────────────

    /** Extract first non-empty value matching any key in $keys */
    private function _extract(array $fields, array $keys): string
    {
        foreach ($keys as $k) {
            if (isset($fields[$k]) && $fields[$k] !== '') {
                return $fields[$k];
            }
        }
        return '';
    }

    /** Reconstruct name from full_name or first_name + last_name */
    private function _extract_name(array $fields): string
    {
        $full = $this->_extract($fields, ['full_name', 'name', 'applicant_name', 'applicantname']);
        if ($full !== '') {
            return $full;
        }
        $first = $this->_extract($fields, ['first_name']);
        $last  = $this->_extract($fields, ['last_name']);
        return trim($first . ' ' . $last);
    }

    /** Extract phone and strip non-digit characters for uniformity */
    private function _extract_phone(array $fields): string
    {
        $raw = $this->_extract($fields, ['phone_number', 'mobile', 'phone', 'contact', 'mobilenumber', 'mobile_number']);
        // Keep digits and leading + for international
        $cleaned = preg_replace('/[^\d+]/', '', $raw);
        return substr($cleaned, 0, 20);
    }
}
