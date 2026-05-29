<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_qpd
 *
 * Question Paper Distribution controller.
 * Handles AES-256-CBC encrypted upload, time-lock, and controlled distribution.
 *
 * Routes:
 *   GET  coe/coe_qpd                      → index (pick batch exam)
 *   GET  coe/coe_qpd/manage/:id           → manage papers for a batch exam
 *   POST coe/coe_qpd/upload/:id           → upload + encrypt a paper
 *   GET  coe/coe_qpd/download/:paper_id   → decrypt + stream if unlocked
 *   POST coe/coe_qpd/delete/:paper_id     → delete (pre-unlock only)
 */
class Coe_qpd extends MY_Addon_CoeController
{
    // Directory (absolute) where encrypted papers are stored
    private $upload_path;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_qpd_model');
        $this->upload_path = FCPATH . 'uploads/coe_qpd/';
    }

    // ------------------------------------------------------------------
    // INDEX — select batch exam
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_view')) {
            access_denied();
        }

        $session_id = $this->input->get('session_id') ?: $this->current_session;

        $data['title']            = 'Question Paper Distribution';
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = $session_id;
        $data['events']           = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_qpd/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // MANAGE — list papers for a batch exam
    // ------------------------------------------------------------------
    public function manage($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['title']         = 'QPD: ' . $event->exam_group_name;
        $data['event']         = $event;
        $data['batch_exam_id'] = (int) $batch_exam_id;
        $data['papers']        = $this->Coe_qpd_model->getPapersByBatchExam($batch_exam_id);
        $data['subjects']      = $this->Coe_qpd_model->getSubjectsByBatchExam($batch_exam_id);
        $data['now']           = date('Y-m-d H:i:s');

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_qpd/manage', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // UPLOAD — encrypt and store a paper
    // ------------------------------------------------------------------
    public function upload($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $subject_id = (int) $this->input->post('subject_id');
        $unlock_at  = $this->input->post('unlock_at'); // YYYY-MM-DD HH:MM

        if (!$subject_id || empty($unlock_at)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Subject and unlock time are required.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        // Validate unlock_at is a future datetime
        if (strtotime($unlock_at) <= time()) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Unlock time must be in the future.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        // Validate uploaded file
        if (empty($_FILES['paper_file']['name'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">No file uploaded.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        $allowed_ext  = ['pdf', 'doc', 'docx'];
        $original_name = basename($_FILES['paper_file']['name']);
        $ext           = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext, true)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Only PDF, DOC, DOCX allowed.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        if ($_FILES['paper_file']['size'] > 20 * 1024 * 1024) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">File too large (max 20 MB).</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        $file_contents = file_get_contents($_FILES['paper_file']['tmp_name']);
        if ($file_contents === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Failed to read uploaded file.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        // AES-256-CBC encryption
        $aes_key       = random_bytes(32);          // 256-bit key
        $iv            = random_bytes(16);           // 128-bit IV
        $encrypted     = openssl_encrypt($file_contents, 'aes-256-cbc', $aes_key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Encryption failed.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        // Store: IV (16B) + encrypted_key (32B) + ciphertext (variable)
        // We derive a key-encryption-key from a server secret to wrap the file key
        $server_secret = $this->config->item('encryption_key');
        $kek           = hash('sha256', $server_secret . 'qpd_kek', true); // 32-byte KEK
        $encrypted_aes_key = openssl_encrypt($aes_key, 'aes-256-cbc', $kek, OPENSSL_RAW_DATA, $iv);

        // Blob: [IV:16][ENC_KEY:48][CIPHERTEXT:...]
        $blob          = $iv . $encrypted_aes_key . $encrypted;

        // Ensure upload directory exists
        if (!is_dir($this->upload_path)) {
            mkdir($this->upload_path, 0775, true);
        }

        $stored_name   = 'qpd_' . bin2hex(random_bytes(16)) . '.enc';
        $stored_path   = $this->upload_path . $stored_name;

        if (file_put_contents($stored_path, $blob) === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Failed to write file to disk.</div>');
            redirect('coe/coe_qpd/manage/' . $batch_exam_id);
        }

        $id = $this->Coe_qpd_model->insert([
            'exam_group_class_batch_exam_id' => (int) $batch_exam_id,
            'subject_id'      => $subject_id,
            'original_filename' => $original_name,
            'stored_filename'  => $stored_name,
            'encryption_key_iv' => bin2hex($iv),
            'unlock_at'        => date('Y-m-d H:i:s', strtotime($unlock_at)),
            'created_by'       => $this->customlib->getStaffID(),
        ]);

        $this->Coe_audit_model->log('qpd_uploaded', 'coe_qpd_papers', $id, null, [
            'batch_exam_id' => $batch_exam_id,
            'subject_id'    => $subject_id,
            'unlock_at'     => $unlock_at,
        ]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Question paper uploaded and encrypted. It will be available for download after ' . date('d M Y h:i A', strtotime($unlock_at)) . '.</div>');
        redirect('coe/coe_qpd/manage/' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // EDIT_UNLOCK — change the unlock_at time (admin override / testing)
    // ------------------------------------------------------------------
    public function edit_unlock($paper_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_add')) {
            access_denied();
        }

        $paper = $this->Coe_qpd_model->getPaperById($paper_id);
        if (empty($paper)) {
            show_404();
        }

        if ($paper->is_distributed) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Cannot change unlock time after paper has been distributed.</div>');
            redirect('coe/coe_qpd/manage/' . $paper->exam_group_class_batch_exam_id);
        }

        $unlock_at = trim($this->input->post('unlock_at'));
        if (!$unlock_at || !strtotime($unlock_at)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid date/time provided.</div>');
            redirect('coe/coe_qpd/manage/' . $paper->exam_group_class_batch_exam_id);
        }

        $this->Coe_qpd_model->updateUnlockAt($paper_id, date('Y-m-d H:i:s', strtotime($unlock_at)));

        $this->Coe_audit_model->log('qpd_unlock_edited', 'coe_qpd_papers', $paper_id, null, [
            'old_unlock_at' => $paper->unlock_at,
            'new_unlock_at' => $unlock_at,
            'changed_by'    => $this->customlib->getStaffID(),
        ]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Unlock time updated to ' . date('d M Y h:i A', strtotime($unlock_at)) . '.</div>');
        redirect('coe/coe_qpd/manage/' . $paper->exam_group_class_batch_exam_id);
    }

    // ------------------------------------------------------------------
    // DOWNLOAD — decrypt and stream (only if unlock_at passed)
    // ------------------------------------------------------------------
    public function download($paper_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_view')) {
            access_denied();
        }

        $paper = $this->Coe_qpd_model->getPaperById($paper_id);
        if (empty($paper)) {
            show_404();
        }

        // Time-lock check
        if (strtotime($paper->unlock_at) > time()) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">This paper is time-locked. Available after ' . date('d M Y h:i A', strtotime($paper->unlock_at)) . '.</div>');
            redirect('coe/coe_qpd/manage/' . $paper->exam_group_class_batch_exam_id);
        }

        $stored_path = $this->upload_path . $paper->stored_filename;
        if (!file_exists($stored_path)) {
            show_error('Encrypted paper file not found on disk.');
        }

        $blob = file_get_contents($stored_path);
        $iv              = substr($blob, 0, 16);
        $encrypted_key   = substr($blob, 16, 48);
        $ciphertext      = substr($blob, 64);

        $server_secret   = $this->config->item('encryption_key');
        $kek             = hash('sha256', $server_secret . 'qpd_kek', true);
        $aes_key         = openssl_decrypt($encrypted_key, 'aes-256-cbc', $kek, OPENSSL_RAW_DATA, $iv);

        if ($aes_key === false) {
            show_error('Failed to unwrap encryption key.');
        }

        $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $aes_key, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            show_error('Decryption failed.');
        }

        $this->Coe_qpd_model->incrementDownloadCount($paper_id);

        // Per-download audit log with IP address
        $this->Coe_qpd_model->logDownload(
            $paper_id,
            (int) $this->customlib->getStaffID(),
            $this->input->ip_address(),
            $this->input->user_agent()
        );
        $this->Coe_audit_model->log('qpd_downloaded', 'coe_qpd_papers', $paper_id, null, [
            'downloaded_by' => $this->customlib->getStaffID(),
            'ip'            => $this->input->ip_address(),
        ]);

        $ext = strtolower(pathinfo($paper->original_filename, PATHINFO_EXTENSION));
        $mime_map = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $mime = $mime_map[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($paper->original_filename) . '"');
        header('Content-Length: ' . strlen($plaintext));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $plaintext;
        exit;
    }

    // ------------------------------------------------------------------
    // PREVIEW — decrypt and stream inline (PDF only, only if unlocked)
    // ------------------------------------------------------------------
    public function preview($paper_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_view')) {
            access_denied();
        }

        $paper = $this->Coe_qpd_model->getPaperById($paper_id);
        if (empty($paper)) {
            show_404();
        }

        // Time-lock check
        if (strtotime($paper->unlock_at) > time()) {
            show_error('This paper is still time-locked.');
        }

        // Only PDFs can be previewed inline
        $ext = strtolower(pathinfo($paper->original_filename, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            show_error('Only PDF files can be previewed.');
        }

        $stored_path = $this->upload_path . $paper->stored_filename;
        if (!file_exists($stored_path)) {
            show_error('Encrypted paper file not found on disk.');
        }

        $blob          = file_get_contents($stored_path);
        $iv            = substr($blob, 0, 16);
        $encrypted_key = substr($blob, 16, 48);
        $ciphertext    = substr($blob, 64);

        $server_secret = $this->config->item('encryption_key');
        $kek           = hash('sha256', $server_secret . 'qpd_kek', true);
        $aes_key       = openssl_decrypt($encrypted_key, 'aes-256-cbc', $kek, OPENSSL_RAW_DATA, $iv);

        if ($aes_key === false) {
            show_error('Failed to unwrap encryption key.');
        }

        $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $aes_key, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            show_error('Decryption failed.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . addslashes($paper->original_filename) . '"');
        header('Content-Length: ' . strlen($plaintext));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $plaintext;
        exit;
    }

    // ------------------------------------------------------------------
    // DELETE — remove paper (only before distribution)
    // ------------------------------------------------------------------
    public function delete($paper_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_delete')) {
            access_denied();
        }

        $paper = $this->Coe_qpd_model->getPaperById($paper_id);
        if (empty($paper)) {
            show_404();
        }

        if ($paper->is_distributed) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Cannot delete a paper that has already been distributed.</div>');
            redirect('coe/coe_qpd/manage/' . $paper->exam_group_class_batch_exam_id);
        }

        $rows = $this->Coe_qpd_model->delete($paper_id);
        if ($rows > 0) {
            // Remove encrypted file from disk
            $path = $this->upload_path . $paper->stored_filename;
            if (file_exists($path)) {
                @unlink($path);
            }
            $this->Coe_audit_model->log('qpd_deleted', 'coe_qpd_papers', $paper_id, null, null);
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Paper deleted.</div>');
        redirect('coe/coe_qpd/manage/' . $paper->exam_group_class_batch_exam_id);
    }

    // ------------------------------------------------------------------
    // DOWNLOAD_LOG — show per-download audit log for a batch exam
    // ------------------------------------------------------------------
    public function download_log($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_qpd', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['title']         = 'QPD Download Log';
        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['log']           = $this->Coe_qpd_model->getDownloadLogByBatchExam($batch_exam_id);
        $data['papers']        = $this->Coe_qpd_model->getPapersByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_qpd/download_log', $data);
        $this->load->view('layout/footer', $data);
    }
}
