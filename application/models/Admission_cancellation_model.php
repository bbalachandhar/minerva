<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admission_cancellation_model
 *
 * Handles the full lifecycle of admission cancellation and refund tracking.
 * Ensures all DB mutations happen inside transactions for consistency.
 */
class Admission_cancellation_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // CANCEL AN ADMISSION
    // ------------------------------------------------------------------

    /**
     * Cancel an admission and create a pending refund record atomically.
     *
     * @param  int   $admission_id
     * @param  array $data {
     *   cancellation_reason string  (required)
     *   refund_amount       float   (default = total_paid_amount)
     *   refund_mode         string  cash|neft|upi|cheque|dd|online
     *   remarks             string  optional notes
     *   cancelled_by        int     staff ID
     * }
     * @return array ['success' => bool, 'message' => string, 'refund_id' => int|null]
     */
    public function cancel_admission($admission_id, $data)
    {
        $admission_id = (int) $admission_id;

        // Fetch the admission record
        $admission = $this->db
            ->select('id, reference_no, firstname, middlename, lastname, admission_status, course_fee_total, quota_type')
            ->from('online_admissions')
            ->where('id', $admission_id)
            ->get()
            ->row_array();

        if (empty($admission)) {
            return ['success' => false, 'message' => 'Admission record not found.'];
        }

        if ($admission['admission_status'] === 'cancelled') {
            return ['success' => false, 'message' => 'This admission has already been cancelled.'];
        }

        // Sum all payments collected against this reference number
        $ref_clean = preg_replace('/\s+/', '', (string) $admission['reference_no']);
        $total_paid = $this->_get_total_paid($ref_clean);

        $refund_amount = isset($data['refund_amount']) && $data['refund_amount'] !== ''
            ? (float) $data['refund_amount']
            : $total_paid;

        // Clamp refund to total paid
        if ($refund_amount > $total_paid) {
            $refund_amount = $total_paid;
        }

        $applicant_name = trim(
            $admission['firstname'] . ' ' .
            ($admission['middlename'] ?? '') . ' ' .
            ($admission['lastname'] ?? '')
        );

        $cancelled_by = isset($data['cancelled_by']) ? (int) $data['cancelled_by'] : null;
        $cancelled_by = $cancelled_by ?: null;

        $this->db->trans_start();

        // 1. Mark admission as cancelled
        $this->db->where('id', $admission_id);
        $this->db->update('online_admissions', [
            'admission_status'    => 'cancelled',
            'cancelled_at'        => date('Y-m-d H:i:s'),
            'cancelled_by'        => $cancelled_by,
            'cancellation_reason' => isset($data['cancellation_reason']) ? $data['cancellation_reason'] : null,
        ]);

        // 2. Insert refund record
        $refund_insert = [
            'online_admission_id' => $admission_id,
            'reference_no'        => $admission['reference_no'],
            'applicant_name'      => $applicant_name,
            'total_paid_amount'   => $total_paid,
            'refund_amount'       => $refund_amount,
            'refund_mode'         => isset($data['refund_mode']) ? $data['refund_mode'] : null,
            'refund_reference_no' => null, // filled when processed
            'refund_status'       => 'pending',
            'cancellation_reason' => isset($data['cancellation_reason']) ? $data['cancellation_reason'] : null,
            'remarks'             => isset($data['remarks']) ? $data['remarks'] : null,
            'initiated_by'        => $cancelled_by,
            'initiated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('admission_refunds', $refund_insert);
        $refund_id = $this->db->insert_id();

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            log_message('error', 'Admission_cancellation_model::cancel_admission transaction failed for admission_id=' . $admission_id);
            return ['success' => false, 'message' => 'Database error. Please try again.'];
        }

        return ['success' => true, 'message' => 'Admission cancelled and refund initiated.', 'refund_id' => $refund_id];
    }

    // ------------------------------------------------------------------
    // REFUND STATUS UPDATE
    // ------------------------------------------------------------------

    /**
     * Update refund status to processed or rejected.
     *
     * @param  int    $refund_id
     * @param  string $status       'processed' | 'rejected'
     * @param  string $refund_ref   UTR/cheque no. (required when processed, optional for rejected)
     * @param  int    $processed_by staff ID
     * @param  string $remarks
     * @return array  ['success' => bool, 'message' => string]
     */
    public function update_refund_status($refund_id, $status, $refund_ref, $processed_by, $remarks = '')
    {
        $refund_id = (int) $refund_id;
        $allowed   = ['processed', 'rejected'];

        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $refund = $this->db->get_where('admission_refunds', ['id' => $refund_id])->row_array();
        if (empty($refund)) {
            return ['success' => false, 'message' => 'Refund record not found.'];
        }

        if ($refund['refund_status'] !== 'pending') {
            return ['success' => false, 'message' => 'Refund has already been ' . $refund['refund_status'] . '.'];
        }

        $update = [
            'refund_status'       => $status,
            'refund_reference_no' => $refund_ref ?: null,
            'processed_by'        => $processed_by ?: null,
            'processed_at'        => date('Y-m-d H:i:s'),
        ];

        if ($remarks !== '') {
            $update['remarks'] = $remarks;
        }

        $this->db->where('id', $refund_id);
        $this->db->update('admission_refunds', $update);

        if ($this->db->affected_rows() === 0) {
            return ['success' => false, 'message' => 'No changes made.'];
        }

        return ['success' => true, 'message' => 'Refund status updated to ' . $status . '.'];
    }

    // ------------------------------------------------------------------
    // QUERIES
    // ------------------------------------------------------------------

    /**
     * Get all cancelled admissions with their refund records.
     */
    public function get_cancelled_admissions($filters = [])
    {
        $this->db->select(
            'oa.id as admission_id, oa.reference_no, oa.firstname, oa.middlename, oa.lastname,
             oa.mobileno, oa.email, oa.cancelled_at, oa.cancellation_reason,
             CONCAT(cs.name, " ", cs.surname) as cancelled_by_name,
             oac.course_name,
             ar.id as refund_id, ar.total_paid_amount, ar.refund_amount,
             ar.refund_mode, ar.refund_reference_no, ar.refund_status,
             ar.remarks, ar.initiated_at, ar.processed_at,
             CONCAT(ps.name, " ", ps.surname) as processed_by_name',
            false
        );
        $this->db->from('online_admissions oa');
        $this->db->join('admission_refunds ar', 'ar.online_admission_id = oa.id', 'left');
        $this->db->join('online_admission_courses oac',
            'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left');
        $this->db->join('staff cs', 'cs.id = oa.cancelled_by', 'left');
        $this->db->join('staff ps', 'ps.id = ar.processed_by', 'left');
        $this->db->where('oa.admission_status', 'cancelled');

        if (!empty($filters['refund_status'])) {
            $this->db->where('ar.refund_status', $filters['refund_status']);
        }

        $this->db->order_by('oa.cancelled_at', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Get a single cancelled admission with its refund detail.
     */
    public function get_cancelled_admission_by_id($admission_id)
    {
        return $this->db
            ->select(
                'oa.id as admission_id, oa.reference_no, oa.firstname, oa.middlename, oa.lastname,
                 oa.mobileno, oa.email, oa.cancelled_at, oa.cancellation_reason,
                 CONCAT(cs.name, " ", cs.surname) as cancelled_by_name,
                 oac.course_name,
                 ar.id as refund_id, ar.total_paid_amount, ar.refund_amount,
                 ar.refund_mode, ar.refund_reference_no, ar.refund_status,
                 ar.remarks, ar.initiated_at, ar.processed_at,
                 CONCAT(ps.name, " ", ps.surname) as processed_by_name',
                false
            )
            ->from('online_admissions oa')
            ->join('admission_refunds ar', 'ar.online_admission_id = oa.id', 'left')
            ->join('online_admission_courses oac',
                'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left')
            ->join('staff cs', 'cs.id = oa.cancelled_by', 'left')
            ->join('staff ps', 'ps.id = ar.processed_by', 'left')
            ->where('oa.id', (int) $admission_id)
            ->where('oa.admission_status', 'cancelled')
            ->get()
            ->row_array();
    }

    /**
     * Get refund record by its own ID.
     */
    public function get_refund_by_id($refund_id)
    {
        return $this->db
            ->select('ar.*, oa.firstname, oa.middlename, oa.lastname, oa.mobileno, oa.email, oa.reference_no,
                      oac.course_name,
                      CONCAT(is_.name, " ", is_.surname) as initiated_by_name,
                      CONCAT(ps.name, " ", ps.surname)   as processed_by_name',
                false
            )
            ->from('admission_refunds ar')
            ->join('online_admissions oa', 'oa.id = ar.online_admission_id', 'left')
            ->join('online_admission_courses oac',
                'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left')
            ->join('staff is_', 'is_.id = ar.initiated_by', 'left')
            ->join('staff ps', 'ps.id = ar.processed_by', 'left')
            ->where('ar.id', (int) $refund_id)
            ->get()
            ->row_array();
    }

    /**
     * Count cancelled admissions (for widget / stats).
     *
     * @return int
     */
    public function count_cancelled()
    {
        return (int) $this->db
            ->where('admission_status', 'cancelled')
            ->count_all_results('online_admissions');
    }

    /**
     * Count refunds by status.
     */
    public function count_refunds_by_status()
    {
        $rows = $this->db
            ->select('refund_status, COUNT(*) as cnt')
            ->from('admission_refunds')
            ->group_by('refund_status')
            ->get()
            ->result_array();

        $map = ['pending' => 0, 'processed' => 0, 'rejected' => 0];
        foreach ($rows as $r) {
            $map[$r['refund_status']] = (int) $r['cnt'];
        }
        return $map;
    }

    // ------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------

    /**
     * Sum all fee payments recorded for a given application reference no.
     * Includes both incidental_fee_collections and online_admission_payment.
     */
    private function _get_total_paid($ref_clean)
    {
        // 1. Incidental fee collections
        $row1 = $this->db
            ->select('COALESCE(SUM(amount_collected), 0) as total', false)
            ->from('incidental_fee_collections')
            ->where("REPLACE(application_ref_no, ' ', '') = " . $this->db->escape($ref_clean), null, false)
            ->where('application_ref_no IS NOT NULL', null, false)
            ->where('application_ref_no !=', '')
            ->get()
            ->row_array();

        $incidental_total = isset($row1['total']) ? (float) $row1['total'] : 0.0;

        // 2. Online / gateway payments
        $row2 = $this->db
            ->select('COALESCE(SUM(oap.paid_amount), 0) as total', false)
            ->from('online_admission_payment oap')
            ->join('online_admissions oa', 'oa.id = oap.online_admission_id', 'inner')
            ->where("REPLACE(oa.reference_no, ' ', '') = " . $this->db->escape($ref_clean), null, false)
            ->get()
            ->row_array();

        $gateway_total = isset($row2['total']) ? (float) $row2['total'] : 0.0;

        return round($incidental_total + $gateway_total, 2);
    }
}
