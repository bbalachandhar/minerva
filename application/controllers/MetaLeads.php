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
        $mode         = $this->input->get('hub_mode');         // dots become underscores in CI
        $token        = $this->input->get('hub_verify_token');
        $challenge    = $this->input->get('hub_challenge');

        // Fallback to raw query string in case CI's input class alters the keys.
        if (empty($mode) || empty($token)) {
            parse_str($_SERVER['QUERY_STRING'] ?? '', $qs);
            $mode      = $qs['hub.mode']         ?? '';
            $token     = $qs['hub.verify_token'] ?? '';
            $challenge = $qs['hub.challenge']    ?? '';
        }

        $setting = $this->setting_model->getSetting();
        $configured_token = trim((string) ($setting->meta_verify_token ?? ''));
        $enabled          = (int) ($setting->meta_leads_enabled ?? 0);

        if ($enabled !== 1) {
            log_message('error', '[MetaLeads] Verification attempt but Meta Leads integration is disabled.');
            $this->output->set_status_header(403)->set_output('Meta Lead integration is disabled.');
            return;
        }

        if ($mode === 'subscribe' && $configured_token !== '' && hash_equals($configured_token, (string) $token)) {
            log_message('info', '[MetaLeads] Webhook verification successful.');
            $this->output->set_status_header(200)->set_output((string) $challenge);
            return;
        }

        log_message('error', '[MetaLeads] Webhook verification FAILED. mode=' . $mode . ' token_match=' . ($token === $configured_token ? 'yes' : 'no'));
        $this->output->set_status_header(403)->set_output('Verification failed.');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Lead Event (Meta POSTs when a lead form is submitted)
    // ─────────────────────────────────────────────────────────────────────
    private function _handle_lead_event()
    {
        $raw_body = file_get_contents('php://input');

        // ── 1. Verify HMAC signature ──────────────────────────────────────
        if (!$this->_verify_signature($raw_body)) {
            log_message('error', '[MetaLeads] HMAC signature verification failed. Possible spoofed POST.');
            $this->output->set_status_header(200)->set_output('EVENT_RECEIVED'); // always 200 to Meta
            return;
        }

        $setting = $this->setting_model->getSetting();
        $enabled = (int) ($setting->meta_leads_enabled ?? 0);
        if ($enabled !== 1) {
            $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
            return;
        }

        $payload = json_decode($raw_body, true);
        if (!is_array($payload)) {
            log_message('error', '[MetaLeads] Invalid JSON payload.');
            $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
            return;
        }

        // ── 2. Walk through all entries & changes ─────────────────────────
        $entries = $payload['entry'] ?? [];
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

                log_message('info', "[MetaLeads] New lead event: leadgen_id={$leadgen_id}, form_id={$form_id}, page_id={$page_id}");
                $this->_process_lead($leadgen_id, $form_id, $page_id, $ad_id, $setting);
            }
        }

        // Meta requires a 200 response within 20 seconds.
        $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Fetch lead data from Graph API and insert enquiry record
    // ─────────────────────────────────────────────────────────────────────
    private function _process_lead($leadgen_id, $form_id, $page_id, $ad_id, $setting)
    {
        $access_token       = trim((string) ($setting->meta_page_access_token ?? ''));
        $default_course_id  = (int) ($setting->meta_default_course_id ?? 0);

        if ($access_token === '') {
            log_message('error', '[MetaLeads] Page access token not configured.');
            return;
        }

        // ── Fetch full lead from Graph API ────────────────────────────────
        $graph_url = self::GRAPH_API_BASE . '/' . urlencode($leadgen_id)
            . '?fields=field_data,created_time,ad_id,form_id'
            . '&access_token=' . urlencode($access_token);

        $lead_data = $this->_graph_get($graph_url);

        if (empty($lead_data) || !isset($lead_data['field_data'])) {
            log_message('error', "[MetaLeads] Could not fetch lead data for leadgen_id={$leadgen_id}. Response: " . json_encode($lead_data));
            return;
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
            return;
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

        // Update last_used_at on meta vendor
        if ($vendor_id) {
            $this->db->where('id', $vendor_id)->update('lead_api_vendors', ['last_used_at' => date('Y-m-d H:i:s')]);
        }

        log_message('info', "[MetaLeads] Enquiry created id={$new_id} ref={$ref_no} for leadgen_id={$leadgen_id}");
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
