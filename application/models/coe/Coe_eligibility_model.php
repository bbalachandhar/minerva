<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_eligibility_model
 *
 * Calculates attendance % per student per subject via
 * student_subject_attendances → subject_timetable → subject_group_subjects,
 * checks fee dues via student_fees_master, and updates coe_exam_applications.
 */
class Coe_eligibility_model extends CI_Model
{
    // attendence_type IDs that count as "present" (for_qr_attendance = 1)
    // ids: 1=Present, 3=Late, 6=Half Day
    private $present_type_ids = [1, 3, 6];

    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // RUN ELIGIBILITY for an entire batch exam
    // Returns ['processed' => n, 'eligible' => n, 'ineligible' => n]
    // ------------------------------------------------------------------

    public function runEligibility($batch_exam_id, $regulation)
    {
        // Get all pending applications for this batch exam
        $applications = $this->db
            ->where('exam_group_class_batch_exam_id', $batch_exam_id)
            ->get('coe_exam_applications')->result();

        if (empty($applications)) {
            return ['processed' => 0, 'eligible' => 0, 'ineligible' => 0, 'error' => 'no_applications'];
        }

        $processed   = 0;
        $eligible    = 0;
        $ineligible  = 0;
        $min_pct     = (float) $regulation->min_attendance_pct;
        $check_fees  = (bool)  $regulation->check_fee_dues;

        foreach ($applications as $app) {
            // Skip overrides — don't reprocess manually-overridden students
            if ($app->application_status === 'override_eligible') {
                continue;
            }

            $att_fail  = false;
            $fee_fail  = false;
            $att_pct   = null;

            // --- Attendance check ---
            $att_pct = $this->_calculateAttendancePct(
                $app->student_session_id,
                $app->subject_id,
                $regulation->session_id
            );
            if ($att_pct !== null && $att_pct < $min_pct) {
                $att_fail = true;
            }

            // --- Fee dues check ---
            if ($check_fees) {
                $has_dues = $this->_hasFeesDue($app->student_session_id);
                if ($has_dues) {
                    $fee_fail = true;
                }
            }

            // Determine result
            if ($att_fail && $fee_fail) {
                $status = 'ineligible';
                $reason = 'both';
            } elseif ($att_fail) {
                $status = 'ineligible';
                $reason = 'attendance';
            } elseif ($fee_fail) {
                $status = 'ineligible';
                $reason = 'fee_dues';
            } else {
                $status = 'eligible';
                $reason = null;
            }

            $this->db->where('id', $app->id)->update('coe_exam_applications', [
                'application_status' => $status,
                'ineligible_reason'  => $reason,
                'attendance_pct'     => $att_pct,
                'processed_at'       => date('Y-m-d H:i:s'),
                'processed_by'       => $this->customlib->getStaffID(),
            ]);

            $processed++;
            if ($status === 'eligible') {
                $eligible++;
            } else {
                $ineligible++;
            }
        }

        return [
            'processed'  => $processed,
            'eligible'   => $eligible,
            'ineligible' => $ineligible,
        ];
    }

    // ------------------------------------------------------------------
    // OVERRIDE eligibility for a single application
    // ------------------------------------------------------------------

    public function overrideEligibility($application_id, $reason, $processed_by)
    {
        $this->db->where('id', $application_id)->update('coe_exam_applications', [
            'application_status' => 'override_eligible',
            'ineligible_reason'  => null,
            'processed_at'       => date('Y-m-d H:i:s'),
            'processed_by'       => $processed_by,
        ]);

        $this->db->insert('coe_eligibility_overrides', [
            'application_id'  => $application_id,
            'override_reason' => $reason,
            'overridden_by'   => $processed_by,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    // ------------------------------------------------------------------
    // ELIGIBILITY SUMMARY per batch exam (for dashboard)
    // ------------------------------------------------------------------

    public function getSummary($batch_exam_id)
    {
        return $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(application_status = 'eligible') AS eligible_count,
                SUM(application_status = 'ineligible') AS ineligible_count,
                SUM(application_status = 'override_eligible') AS override_count,
                SUM(application_status = 'pending') AS pending_count,
                SUM(ineligible_reason = 'attendance') AS att_fail_count,
                SUM(ineligible_reason = 'fee_dues') AS fee_fail_count,
                SUM(ineligible_reason = 'both') AS both_fail_count
             FROM coe_exam_applications
             WHERE exam_group_class_batch_exam_id = ?",
            [$batch_exam_id]
        )->row();
    }

    public function getIneligibleStudents($batch_exam_id)
    {
        return $this->db
            ->select('ca.*, st.firstname, st.lastname, st.register_no, sub.name AS subject_name')
            ->from('coe_exam_applications ca')
            ->join('students st', 'st.id = ca.student_id', 'left')
            ->join('subjects sub', 'sub.id = ca.subject_id', 'left')
            ->where('ca.exam_group_class_batch_exam_id', $batch_exam_id)
            ->where('ca.application_status', 'ineligible')
            ->order_by('st.firstname ASC, sub.name ASC')
            ->get()->result();
    }

    // ------------------------------------------------------------------
    // INTERNAL: calculate attendance % for a student/subject/session
    // ------------------------------------------------------------------

    private function _calculateAttendancePct($student_session_id, $subject_id, $session_id)
    {
        // Total timetable slots for this subject in the session
        // (via subject_timetable → subject_group_subjects → subjects)
        $total_slots = $this->db->query(
            "SELECT COUNT(DISTINCT st.id) AS total
             FROM subject_timetable st
             JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
             WHERE sgs.subject_id = ? AND st.session_id = ?",
            [$subject_id, $session_id]
        )->row();

        if (empty($total_slots) || $total_slots->total == 0) {
            // No timetable data — cannot calculate; return null (won't fail)
            return null;
        }

        $timetable_ids = $this->db->query(
            "SELECT st.id
             FROM subject_timetable st
             JOIN subject_group_subjects sgs ON sgs.id = st.subject_group_subject_id
             WHERE sgs.subject_id = ? AND st.session_id = ?",
            [$subject_id, $session_id]
        )->result_array();

        $tt_ids = array_column($timetable_ids, 'id');
        if (empty($tt_ids)) {
            return null;
        }

        // Attended slots (present type IDs)
        $present_ids_str = implode(',', array_map('intval', $this->present_type_ids));
        $attended = $this->db->query(
            "SELECT COUNT(*) AS attended
             FROM student_subject_attendances
             WHERE student_session_id = ?
               AND subject_timetable_id IN (" . implode(',', array_fill(0, count($tt_ids), '?')) . ")
               AND attendence_type_id IN (" . $present_ids_str . ")",
            array_merge([$student_session_id], $tt_ids)
        )->row();

        $total   = (int) $total_slots->total;
        $present = (int) ($attended->attended ?? 0);

        return round(($present / $total) * 100, 2);
    }

    // ------------------------------------------------------------------
    // INTERNAL: check if a student session has any unpaid fee dues
    // Uses student_fees_master — any record without a corresponding
    // student_fees_deposite row (LEFT JOIN NULL) indicates dues.
    // ------------------------------------------------------------------

    private function _hasFeesDue($student_session_id)
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS due_count
             FROM student_fees_master sfm
             LEFT JOIN student_fees_deposite sfd ON sfd.student_fees_master_id = sfm.id
               AND sfd.is_active = 'yes'
             WHERE sfm.student_session_id = ?
               AND sfm.is_active = 'yes'
               AND sfd.id IS NULL",
            [$student_session_id]
        )->row();

        return ($row && $row->due_count > 0);
    }

    // -----------------------------------------------------------------------
    // 2-step override approval
    // -----------------------------------------------------------------------

    /**
     * Submit an override approval request (staff / teacher).
     */
    public function requestOverride($application_id, $batch_exam_id, $student_id, $requested_by, $reason)
    {
        // Prevent duplicate pending requests
        $existing = $this->db
            ->where('application_id', (int) $application_id)
            ->where('status', 'pending')
            ->get('coe_override_approval_requests')->row();

        if ($existing) {
            return $existing->id;
        }

        $this->db->insert('coe_override_approval_requests', [
            'application_id' => (int) $application_id,
            'batch_exam_id'  => (int) $batch_exam_id,
            'student_id'     => (int) $student_id,
            'requested_by'   => (int) $requested_by,
            'requested_at'   => date('Y-m-d H:i:s'),
            'reason'         => $reason,
            'status'         => 'pending',
        ]);
        return $this->db->insert_id();
    }

    /**
     * Fetch pending requests for a batch exam.
     */
    public function getOverrideRequests($batch_exam_id, $status = null)
    {
        $this->db
            ->select([
                'oar.*',
                "CONCAT(st.firstname,' ',st.lastname) AS student_name",
                'st.admission_no',
                "CONCAT(sf.name) AS requested_by_name",
            ])
            ->from('coe_override_approval_requests oar')
            ->join('students st', 'st.id = oar.student_id', 'left')
            ->join('staff sf', 'sf.id = oar.requested_by', 'left')
            ->where('oar.batch_exam_id', (int) $batch_exam_id);

        if ($status) {
            $this->db->where('oar.status', $status);
        }

        return $this->db->order_by('oar.requested_at', 'DESC')->get()->result();
    }

    /**
     * Fetch a single request by id.
     */
    public function getOverrideRequestById($id)
    {
        return $this->db->where('id', (int) $id)->get('coe_override_approval_requests')->row();
    }

    /**
     * Approve a request — marks approved and calls overrideEligibility().
     */
    public function approveOverrideRequest($request_id, $approved_by, $remarks = '')
    {
        $req = $this->getOverrideRequestById($request_id);
        if (!$req || $req->status !== 'pending') {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->where('id', (int) $request_id)->update('coe_override_approval_requests', [
            'status'           => 'approved',
            'approved_by'      => (int) $approved_by,
            'approved_at'      => $now,
            'approver_remarks' => $remarks,
        ]);

        // Apply the actual override
        $this->overrideEligibility($req->application_id, $req->reason, $approved_by);
        return true;
    }

    /**
     * Reject a request.
     */
    public function rejectOverrideRequest($request_id, $approved_by, $remarks = '')
    {
        $req = $this->getOverrideRequestById($request_id);
        if (!$req || $req->status !== 'pending') {
            return false;
        }

        $this->db->where('id', (int) $request_id)->update('coe_override_approval_requests', [
            'status'           => 'rejected',
            'approved_by'      => (int) $approved_by,
            'approved_at'      => date('Y-m-d H:i:s'),
            'approver_remarks' => $remarks,
        ]);

        return true;
    }
}
