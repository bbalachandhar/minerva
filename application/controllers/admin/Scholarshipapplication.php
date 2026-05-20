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

        $status = $this->input->get('status');
        $data['applications']     = $this->Scholarship_application_model->getAll($status ?: null);
        $data['filter_status']    = $status;
        $data['settings']         = $this->Scholarship_application_model->getSettings();
        $data['staff_list']       = $this->Staff_model->getAll(null, 1);

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

        $data['application']      = $application;
        $data['settings']         = $settings;
        $data['current_staff_id'] = $current_staff_id;
        // Verifier is per scholarship type; approver is global
        $data['can_verify']  = (!empty($application['type_verifier_id']) && (int)$application['type_verifier_id'] === $current_staff_id);
        $data['can_approve'] = ($settings && !empty($settings['approver_id']) && (int)$settings['approver_id'] === $current_staff_id);

        $this->session->set_userdata('top_menu', 'Admissions');
        $this->session->set_userdata('sub_menu', 'admin/scholarshipapplication');

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