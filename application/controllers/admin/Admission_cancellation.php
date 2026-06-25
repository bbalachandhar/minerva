<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admission_cancellation controller
 *
 * Provides:
 *   GET  admin/admission_cancellation              → revoked admissions list
 *   POST admin/admission_cancellation/cancel/{id}  → cancel an admission + create refund
 *   POST admin/admission_cancellation/update_refund/{id} → update refund status
 *   GET  admin/admission_cancellation/get_payment_summary/{id} → AJAX: total paid for modal
 */
class Admission_cancellation extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Admission_cancellation_model');
        $this->load->model('onlinestudent_model');
        $this->load->library('form_validation');
    }

    // ------------------------------------------------------------------
    // LIST — revoked admissions
    // ------------------------------------------------------------------

    public function index()
    {
        if (!$this->rbac->hasPrivilege('admission_cancellation', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'admin/admission_cancellation');

        $data['title']               = $this->lang->line('revoked_admissions');
        $data['cancelled_admissions'] = $this->Admission_cancellation_model->get_cancelled_admissions();
        $data['refund_counts']        = $this->Admission_cancellation_model->count_refunds_by_status();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/admission_cancellation/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // CANCEL — POST from modal on online admission list
    // ------------------------------------------------------------------

    /**
     * Accepts JSON POST body or form-encoded POST.
     * Returns JSON response.
     */
    public function cancel($admission_id = null)
    {
        if (!$this->rbac->hasPrivilege('admission_cancellation', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $admission_id = (int) ($admission_id ?: $this->input->post('admission_id'));

        if (!$admission_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid admission ID.']);
            return;
        }

        $cancellation_reason = trim((string) $this->input->post('cancellation_reason'));
        if ($cancellation_reason === '') {
            echo json_encode(['status' => 'error', 'message' => 'Cancellation reason is required.']);
            return;
        }

        $refund_amount_raw = $this->input->post('refund_amount');
        $refund_amount     = ($refund_amount_raw !== null && $refund_amount_raw !== '')
            ? (float) $refund_amount_raw
            : null;  // null → model defaults to total_paid

        $refund_mode = $this->input->post('refund_mode') ?: null;
        $remarks     = trim((string) $this->input->post('remarks'));

        $result = $this->Admission_cancellation_model->cancel_admission($admission_id, [
            'cancellation_reason' => $cancellation_reason,
            'refund_amount'       => $refund_amount,
            'refund_mode'         => $refund_mode,
            'remarks'             => $remarks !== '' ? $remarks : null,
            'cancelled_by'        => $this->customlib->getStaffID() ?: null,
        ]);

        echo json_encode([
            'status'  => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    // ------------------------------------------------------------------
    // UPDATE REFUND STATUS
    // ------------------------------------------------------------------

    /**
     * Staff processes or rejects a pending refund.
     * POST admin/admission_cancellation/update_refund/{refund_id}
     */
    public function update_refund($refund_id = null)
    {
        if (!$this->rbac->hasPrivilege('admission_cancellation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $refund_id = (int) ($refund_id ?: $this->input->post('refund_id'));

        if (!$refund_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid refund ID.']);
            return;
        }

        $status      = $this->input->post('refund_status');
        $refund_ref  = trim((string) $this->input->post('refund_reference_no'));
        $remarks     = trim((string) $this->input->post('remarks'));
        $staff_id    = $this->customlib->getStaffID() ?: null;

        if (!in_array($status, ['processed', 'rejected'], true)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid refund status.']);
            return;
        }

        if ($status === 'processed' && $refund_ref === '') {
            echo json_encode(['status' => 'error', 'message' => 'Refund reference number is required when marking as Processed.']);
            return;
        }

        $result = $this->Admission_cancellation_model->update_refund_status(
            $refund_id, $status, $refund_ref, $staff_id, $remarks
        );

        echo json_encode([
            'status'  => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    // ------------------------------------------------------------------
    // AJAX: payment summary for cancel modal
    // ------------------------------------------------------------------

    /**
     * Returns total paid amount for a given admission_id so the modal
     * can pre-fill the refund amount field.
     */
    public function get_payment_summary($admission_id = null)
    {
        if (!$this->rbac->hasPrivilege('admission_cancellation', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            return;
        }

        $admission_id = (int) ($admission_id ?: $this->input->post('admission_id'));

        if (!$admission_id) {
            echo json_encode(['status' => 'error', 'total_paid' => 0]);
            return;
        }

        $admission = $this->db
            ->select('id, reference_no, admission_status, firstname, middlename, lastname')
            ->from('online_admissions')
            ->where('id', $admission_id)
            ->get()
            ->row_array();

        if (empty($admission)) {
            echo json_encode(['status' => 'error', 'message' => 'Admission not found.', 'total_paid' => 0]);
            return;
        }

        if ($admission['admission_status'] === 'cancelled') {
            echo json_encode(['status' => 'error', 'message' => 'Admission already cancelled.', 'total_paid' => 0]);
            return;
        }

        $ref_clean   = preg_replace('/\s+/', '', (string) $admission['reference_no']);

        // Course fees only (incidental, non-application)
        $row_inc = $this->db
            ->select('COALESCE(SUM(ifc.amount_collected), 0) as total', false)
            ->from('incidental_fee_collections ifc')
            ->join('incidental_fee_types ift', 'ift.id = ifc.incidental_fee_type_id', 'left')
            ->where("REPLACE(ifc.application_ref_no, ' ', '') = " . $this->db->escape($ref_clean), null, false)
            ->where('ifc.application_ref_no IS NOT NULL', null, false)
            ->where('ifc.application_ref_no !=', '')
            ->where("LOWER(COALESCE(ift.title,'')) NOT LIKE '%application%'", null, false)
            ->get()->row_array();

        // Gateway payments — exclude application fee payment_type
        $row_gw = $this->db
            ->select('COALESCE(SUM(oap.paid_amount), 0) as total', false)
            ->from('online_admission_payment oap')
            ->join('online_admissions oa', 'oa.id = oap.online_admission_id', 'inner')
            ->where("REPLACE(oa.reference_no, ' ', '') = " . $this->db->escape($ref_clean), null, false)
            ->where("LOWER(COALESCE(oap.payment_type,'')) NOT LIKE '%online_admission%'", null, false)
            ->get()->row_array();

        $refundable_amount = round(
            (float) ($row_inc['total'] ?? 0) + (float) ($row_gw['total'] ?? 0),
            2
        );

        $name = trim(
            $admission['firstname'] . ' ' .
            ($admission['middlename'] ?? '') . ' ' .
            ($admission['lastname'] ?? '')
        );

        echo json_encode([
            'status'            => 'success',
            'refundable_amount' => $refundable_amount,
            'name'              => $name,
            'ref_no'            => $admission['reference_no'],
        ]);
    }

    // ------------------------------------------------------------------
    // READMIT — reverse a cancellation
    // ------------------------------------------------------------------

    /**
     * POST admin/admission_cancellation/readmit/{admission_id}
     * Restores a cancelled admission to active and voids the pending refund.
     */
    public function readmit($admission_id = null)
    {
        if (!$this->rbac->hasPrivilege('admission_cancellation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $admission_id = (int) ($admission_id ?: $this->input->post('admission_id'));

        if (!$admission_id) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid admission ID.']);
            return;
        }

        $readmit_reason = trim((string) $this->input->post('readmit_reason'));
        if ($readmit_reason === '') {
            echo json_encode(['status' => 'error', 'message' => 'Readmit reason is required.']);
            return;
        }

        $result = $this->Admission_cancellation_model->readmit_admission($admission_id, [
            'readmit_reason' => $readmit_reason,
            'readmitted_by'  => $this->customlib->getStaffID() ?: null,
        ]);

        echo json_encode([
            'status'  => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    public function delete()
    {
        if (!$this->rbac->hasPrivilege('admission_cancellation', 'can_delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $id = (int) $this->input->post('id');
        $admission = $this->db->where('id', $id)->get('online_admissions')->row();

        if (!$admission) {
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
            return;
        }

        $this->db->where('online_admission_id', $id)->delete('admission_cancellation_refunds');
        $this->db->where('online_admission_id', $id)->where('candidate_type', 'applicant')->delete('onlineexam_students');
        $this->db->where('id', $id)->delete('online_admissions');

        echo json_encode(['status' => 'success', 'message' => 'Application #' . $admission->reference_no . ' deleted permanently.']);
    }
}
