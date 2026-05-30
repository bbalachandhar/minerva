<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Admin: view, verify, approve/reject scholarship applications + manage settings.
 */
class Scholarshipapplication extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Scholarship_application_model');
        $this->load->model('Scholarship_type_model');
        $this->load->model('Staff_model');
    }

    // ── List all applications ─────────────────────────────────────────────────

    public function index()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Admissions');
        $this->session->set_userdata('sub_menu', 'admin/scholarshipapplication');

        $status  = $this->input->get('status');
        $type_id = $this->input->get('type_id') ? (int) $this->input->get('type_id') : null;
        $data['applications']     = $this->Scholarship_application_model->getAll($status ?: null, $type_id);
        $data['filter_status']    = $status;
        $data['filter_type_id']   = $type_id;
        $data['scholarship_types'] = $this->Scholarship_type_model->getAll();
        $data['settings']         = $this->Scholarship_application_model->getSettings();
        $data['staff_list']       = $this->Staff_model->getAll(null, 1);

        // Status counts (unfiltered — used for dashboard widgets)
        $all_for_counts = $this->Scholarship_application_model->getAll(null, null);
        $data['status_counts'] = [
            'all'      => count($all_for_counts),
            'pending'  => count(array_filter($all_for_counts, fn($a) => $a['status'] === 'pending')),
            'verified' => count(array_filter($all_for_counts, fn($a) => $a['status'] === 'verified')),
            'approved' => count(array_filter($all_for_counts, fn($a) => $a['status'] === 'approved')),
            'rejected' => count(array_filter($all_for_counts, fn($a) => $a['status'] === 'rejected')),
        ];

        $this->load->view('layout/header');
        $this->load->view('admin/scholarship/scholarshipapplication_list', $data);
        $this->load->view('layout/footer');
    }

    // ── View / verify / approve a single application ─────────────────────────

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_view')) {
            access_denied();
        }

        $application = $this->Scholarship_application_model->get($id);
        if (!$application) {
            show_404();
        }

        $settings         = $this->Scholarship_application_model->getSettings();
        $userdata         = $this->customlib->getUserData();
        $current_staff_id = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $data['application']        = $application;
        $data['settings']           = $settings;
        $data['current_staff_id']   = $current_staff_id;
        $data['scholarship_types']  = $this->Scholarship_type_model->getAll(true); // active only
        // Verifier is per scholarship type; approver is global
        $data['can_verify']  = (!empty($application['type_verifier_id']) && (int)$application['type_verifier_id'] === $current_staff_id);
        $data['can_approve'] = ($settings && !empty($settings['approver_id']) && (int)$settings['approver_id'] === $current_staff_id);

        $this->session->set_userdata('top_menu', 'Admissions');
        $this->session->set_userdata('sub_menu', 'admin/scholarshipapplication');

        // AJAX request: return only the inner content (no header/footer)
        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/scholarship/scholarshipapplication_view', $data);
            return;
        }

        $this->load->view('layout/header');
        $this->load->view('admin/scholarship/scholarshipapplication_view', $data);
        $this->load->view('layout/footer');
    }

    // ── Verify (first-level) ─────────────────────────────────────────────────

    public function verify($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            access_denied();
        }

        $application      = $this->Scholarship_application_model->get($id);
        if (!$application) { show_404(); }
        $userdata         = $this->customlib->getUserData();
        $current_staff_id = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        if (empty($application['type_verifier_id']) || (int)$application['type_verifier_id'] !== $current_staff_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">You are not authorised to verify this scholarship application.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $action  = $this->input->post('action');   // 'verified' or 'rejected'
        $remarks = $this->input->post('verifier_remarks');

        if (!in_array($action, ['verified', 'rejected'])) {
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $update = array(
            'status'           => $action,
            'verifier_id'      => $current_staff_id,
            'verifier_remarks' => $this->security->xss_clean($remarks),
            'verified_at'      => date('Y-m-d H:i:s'),
        );
        // If rejecting, clear any prior approval fields
        if ($action === 'rejected') {
            $update['approver_id']      = null;
            $update['approver_remarks'] = null;
            $update['approved_at']      = null;
        }
        $this->Scholarship_application_model->update($id, $update);

        $msg = ($action === 'verified') ? 'Application marked as Verified.' : 'Application rejected at verification stage.';
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $msg . '</div>');
        redirect('admin/scholarshipapplication/view/' . $id);
    }

    // ── Approve / reject (second-level) ──────────────────────────────────────

    public function approve($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            access_denied();
        }

        $settings         = $this->Scholarship_application_model->getSettings();
        $userdata         = $this->customlib->getUserData();
        $current_staff_id = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        if (!$settings || (int)$settings['approver_id'] !== $current_staff_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">You are not authorised to approve scholarship applications.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $application = $this->Scholarship_application_model->get($id);
        if ($application['status'] !== 'verified') {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">Application must be verified before it can be approved.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $action  = $this->input->post('action');   // 'approved' or 'rejected'
        $remarks = $this->input->post('approver_remarks');

        if (!in_array($action, ['approved', 'rejected'])) {
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $update = array(
            'status'           => $action,
            'approver_id'      => $current_staff_id,
            'approver_remarks' => $this->security->xss_clean($remarks),
            'approved_at'      => date('Y-m-d H:i:s'),
        );
        $this->Scholarship_application_model->update($id, $update);

        $msg = ($action === 'approved') ? 'Scholarship Approved.' : 'Application rejected at approval stage.';
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $msg . '</div>');
        redirect('admin/scholarshipapplication/view/' . $id);
    }

    // ── Override amount ───────────────────────────────────────────────────────

    public function override_amount($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            access_denied();
        }

        $application = $this->Scholarship_application_model->get($id);
        if (!$application) { show_404(); }

        $amount  = $this->input->post('override_amount');
        $comment = trim($this->input->post('override_comment') ?? '');

        if ($comment === '') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Override comment is mandatory. Please explain why the amount is being changed.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        if (!is_numeric($amount) || (float) $amount < 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid override amount. Enter a valid positive number.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $userdata         = $this->customlib->getUserData();
        $current_staff_id = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $this->Scholarship_application_model->update($id, [
            'override_amount'           => (float) $amount,
            'override_comment'          => $this->security->xss_clean($comment),
            'override_by'               => $current_staff_id,
            'override_at'               => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Scholarship amount overridden successfully.</div>');
        redirect('admin/scholarshipapplication/view/' . $id);
    }

    // ── Change scholarship type ───────────────────────────────────────────────

    public function change_type($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            access_denied();
        }

        $application = $this->Scholarship_application_model->get($id);
        if (!$application) { show_404(); }

        $new_type_id = (int) $this->input->post('scholarship_type_id');
        $comment     = trim($this->input->post('type_change_comment') ?? '');

        if ($new_type_id === (int) $application['scholarship_type_id']) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">The selected type is already the current scholarship type. No change made.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        if ($comment === '') {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">A reason/comment is mandatory when changing the scholarship type.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $new_type = $this->Scholarship_type_model->get($new_type_id);
        if (!$new_type || !$new_type['is_active']) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid or inactive scholarship type selected.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        // Check if this applicant already has a separate application for the new type
        if ($this->Scholarship_application_model->alreadyApplied($application['online_admission_id'], $new_type_id)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">This applicant already has an application for &ldquo;' . htmlspecialchars($new_type['name']) . '&rdquo;. Cannot reassign to a duplicate type.</div>');
            redirect('admin/scholarshipapplication/view/' . $id);
            return;
        }

        $userdata         = $this->customlib->getUserData();
        $current_staff_id = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;

        $this->Scholarship_application_model->update($id, [
            'scholarship_type_id'  => $new_type_id,
            // Reset workflow — new type may have a different verifier
            'status'               => 'pending',
            'verifier_id'          => null,
            'verifier_remarks'     => null,
            'verified_at'          => null,
            'approver_id'          => null,
            'approver_remarks'     => null,
            'approved_at'          => null,
            // Clear amount override — new type has its own default
            'override_amount'      => null,
            'override_comment'     => null,
            'override_by'          => null,
            'override_at'          => null,
            // Log the change
            'type_change_comment'  => $this->security->xss_clean($comment),
            'type_changed_by'      => $current_staff_id,
            'type_changed_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Scholarship type changed to &ldquo;' . htmlspecialchars($new_type['name']) . '&rdquo;. Application reset to Pending.</div>');
        redirect('admin/scholarshipapplication/view/' . $id);
    }

    // ── View document inline (browser display) ───────────────────────────────

    public function view_doc($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_view')) {
            show_error('Access denied', 403);
        }
        $application = $this->Scholarship_application_model->get($id);
        if (!$application || empty($application['document'])) {
            show_404();
        }
        $path = FCPATH . 'uploads/scholarship_docs/' . $application['document'];
        if (!file_exists($path)) {
            show_404();
        }
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $mime      = finfo_file($finfo, $path);
        finfo_close($finfo);

        $allowed_inline = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($mime, $allowed_inline)) {
            show_error('File type not supported for inline view.', 415);
        }

        $safe_name = basename($application['document']);
        // Strip the upload-prefix (everything up to and including the first '!')
        if (strpos($safe_name, '!') !== false) {
            $safe_name = substr($safe_name, strpos($safe_name, '!') + 1);
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $safe_name . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }

    // ── Quick reject from list (any admin with can_edit) ──────────────────────

    public function reject_ajax($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            echo json_encode(['success' => false, 'msg' => 'Access denied.']);
            return;
        }
        $id          = (int) $id;
        $application = $this->Scholarship_application_model->get($id);
        if (!$application) {
            echo json_encode(['success' => false, 'msg' => 'Application not found.']);
            return;
        }
        if ($application['status'] === 'rejected') {
            echo json_encode(['success' => false, 'msg' => 'Application is already rejected.']);
            return;
        }
        $remarks = trim($this->input->post('remarks') ?? '');
        if (empty($remarks)) {
            echo json_encode(['success' => false, 'msg' => 'Rejection reason is required.']);
            return;
        }
        $userdata  = $this->customlib->getUserData();
        $staff_id  = (is_array($userdata) && isset($userdata['id'])) ? (int) $userdata['id'] : 0;
        $this->Scholarship_application_model->update($id, [
            'status'           => 'rejected',
            'approver_id'      => $staff_id,
            'approver_remarks' => $this->security->xss_clean($remarks),
            'approved_at'      => date('Y-m-d H:i:s'),
        ]);
        echo json_encode(['success' => true, 'msg' => 'Application rejected.']);
    }

    // ── Remove document ───────────────────────────────────────────────────────

    public function remove_doc($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            echo json_encode(['success' => false, 'msg' => 'Access denied.']);
            return;
        }
        $id          = (int) $id;
        $application = $this->Scholarship_application_model->get($id);
        if (!$application) {
            echo json_encode(['success' => false, 'msg' => 'Application not found.']);
            return;
        }
        if (!empty($application['document'])) {
            $path = FCPATH . 'uploads/scholarship_docs/' . $application['document'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        $this->Scholarship_application_model->update($id, ['document' => null]);
        echo json_encode(['success' => true, 'msg' => 'Document removed.']);
    }

    // ── Download document ─────────────────────────────────────────────────────

    public function download($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_view')) {
            access_denied();
        }
        $application = $this->Scholarship_application_model->get($id);
        if (!$application || empty($application['document'])) {
            show_404();
        }
        $path = './uploads/scholarship_docs/' . $application['document'];
        if (!file_exists($path)) {
            show_404();
        }
        $this->load->library('media_storage');
        $this->media_storage->filedownload($application['document'], 'uploads/scholarship_docs');
    }

    // ── Admin upload document for an application (AJAX) ──────────────────────

    public function upload_doc($id)
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            echo json_encode(['success' => false, 'msg' => 'Access denied.']);
            return;
        }

        $id          = (int) $id;
        $application = $this->Scholarship_application_model->get($id);
        if (!$application) {
            echo json_encode(['success' => false, 'msg' => 'Application not found.']);
            return;
        }

        if (empty($_FILES['doc_file']['name'])) {
            echo json_encode(['success' => false, 'msg' => 'No file selected.']);
            return;
        }

        // Server-side MIME detection (cannot trust $_FILES['type'] alone)
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime = finfo_file($finfo, $_FILES['doc_file']['tmp_name']);
        finfo_close($finfo);

        $ext          = strtolower(pathinfo($_FILES['doc_file']['name'], PATHINFO_EXTENSION));
        $allowed_mime = ['image/jpeg', 'image/png', 'application/pdf'];
        $allowed_ext  = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size     = 614400; // 600 KB

        if (!in_array($file_mime, $allowed_mime) || !in_array($ext, $allowed_ext)) {
            echo json_encode(['success' => false, 'msg' => 'Only JPG, PNG, or PDF files are allowed.']);
            return;
        }

        if ($_FILES['doc_file']['size'] > $max_size) {
            $kb = round($_FILES['doc_file']['size'] / 1024, 1);
            echo json_encode(['success' => false, 'msg' => 'File must be 600 KB or smaller. Selected: ' . $kb . ' KB.']);
            return;
        }

        // Delete previous file if one exists
        if (!empty($application['document'])) {
            $old_path = './uploads/scholarship_docs/' . $application['document'];
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $this->load->library('media_storage');
        $upload = $this->media_storage->fileupload('doc_file', './uploads/scholarship_docs/');
        if (!$upload['status']) {
            echo json_encode(['success' => false, 'msg' => 'Upload failed: ' . $upload['message']]);
            return;
        }

        $filename = $upload['message'];
        $this->Scholarship_application_model->update($id, ['document' => $filename]);

        $new_ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $is_image = in_array($new_ext, ['jpg', 'jpeg', 'png']);

        echo json_encode([
            'success'  => true,
            'msg'      => 'Document uploaded successfully.',
            'filename' => $filename,
            'is_image' => $is_image,
            'view_url' => site_url('admin/scholarshipapplication/view_doc/' . $id),
        ]);
    }

    // ── Settings AJAX (modal submit) ──────────────────────────────────────────

    public function settings_ajax()
    {
        if (!$this->rbac->hasPrivilege('scholarship_application', 'can_edit')) {
            echo json_encode(['success' => false, 'msg' => 'Access denied.']);
            return;
        }

        $this->form_validation->set_rules('approver_id', 'Approver', 'trim|required|integer');

        if ($this->form_validation->run() === false) {
            echo json_encode(['success' => false, 'msg' => strip_tags(validation_errors())]);
            return;
        }

        $approver_id = (int) $this->input->post('approver_id');

        $this->Scholarship_application_model->saveSettings(['approver_id' => $approver_id]);
        echo json_encode(['success' => true, 'msg' => 'Scholarship workflow settings saved.']);
    }
}