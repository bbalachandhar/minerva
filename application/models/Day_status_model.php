<?php

/**
 * Day_status_model
 *
 * Manages the staff_day_status table, which "locks" specific calendar dates for a
 * staff member to a particular payroll impact based on an approved leave request.
 *
 * When a leave type has strict_day_lock = 1, every approved leave of that type will
 * write a row to staff_day_status for each date in leave_from..leave_to. The payroll
 * LOP calculation checks this table FIRST; if a day-lock record exists, biometric
 * attendance is ignored for that date, and the payroll_impact column is used instead.
 *
 * Payroll impact semantics:
 *   PAID_PRESENT  => Counted as present; no LOP, no credit consumed (e.g. OD)
 *   PAID_ABSENT   => Counted as absent; leave credit absorbs LOP, zero net deduction (e.g. CPL)
 *   LOP           => Normal loss-of-pay (for completeness; LOP leaves rarely need day-lock)
 *   HOLIDAY       => Written by the holiday module (not via this model)
 *
 * Status label convention (fits VARCHAR(10)):
 *   OD, CPL, FH-OD, SH-OD, FH-CPL, SH-CPL  — prefix FH = first_half, SH = second_half
 *
 * IMPORTANT: Only ONE day-lock record per staff_id + date exists at a time (UNIQUE KEY).
 * If a second approved leave replaces the lock for the same date, deleteDayLock() on the
 * original leave will be a no-op for those overwritten rows. This is an acceptable
 * trade-off for the rare case of overlapping approved leaves.
 */
class Day_status_model extends CI_Model
{
    // ---------------------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------------------

    /**
     * Write day-lock records for an approved leave request.
     * Skips silently if the leave type does not have strict_day_lock = 1.
     * Uses INSERT … ON DUPLICATE KEY UPDATE so re-approvals after an edit are safe.
     *
     * @param int $leave_request_id  The ID from staff_leave_request
     */
    public function writeDayLock($leave_request_id)
    {
        $lr = $this->_loadLeaveRequestWithType($leave_request_id);

        if (empty($lr) || empty($lr['strict_day_lock'])) {
            return; // Leave type does not participate in day-lock
        }

        $payroll_impact = $this->_resolvePayrollImpact($lr);
        $status_label   = $this->_resolveStatusLabel($lr);
        $staff_id       = (int) $lr['staff_id'];
        $leave_id       = (int) $leave_request_id;

        try {
            $from    = new DateTime($lr['leave_from']);
            $to      = new DateTime($lr['leave_to']);
        } catch (Exception $e) {
            log_message('error', "Day_status_model::writeDayLock — invalid date range for leave #{$leave_request_id}");
            return;
        }

        $current = clone $from;
        while ($current <= $to) {
            $date_str = $current->format('Y-m-d');
            $this->db->query(
                "INSERT INTO staff_day_status (staff_id, `date`, status, source, leave_id, payroll_impact)
                 VALUES (?, ?, ?, 'LEAVE', ?, ?)
                 ON DUPLICATE KEY UPDATE
                   status         = VALUES(status),
                   source         = VALUES(source),
                   leave_id       = VALUES(leave_id),
                   payroll_impact = VALUES(payroll_impact)",
                [$staff_id, $date_str, $status_label, $leave_id, $payroll_impact]
            );
            $current->modify('+1 day');
        }
    }

    /**
     * Delete all day-lock records created by a specific leave request.
     * Should be called on ANY status change away from 'approved'
     * (revert to pending, disapprove, admin edit, etc.).
     *
     * @param int $leave_request_id
     */
    public function deleteDayLock($leave_request_id)
    {
        $this->db->where('leave_id', (int) $leave_request_id)
                 ->delete('staff_day_status');
    }

    /**
     * Get the day-status record for a single staff member on a single date.
     *
     * @param  int    $staff_id
     * @param  string $date  Y-m-d format
     * @return array|null  Row from staff_day_status, or null if no lock exists
     */
    public function getDayStatus($staff_id, $date)
    {
        $row = $this->db->get_where('staff_day_status', [
            'staff_id' => (int) $staff_id,
            'date'     => $date,
        ])->row_array();
        return $row ?: null;
    }

    /**
     * Get all day-status records for a staff member within a date range (inclusive).
     * Returns an array keyed by date string (Y-m-d) for O(1) lookups.
     *
     * @param  int    $staff_id
     * @param  string $from  Y-m-d
     * @param  string $to    Y-m-d
     * @return array  ['2024-06-01' => ['status'=>'OD', 'payroll_impact'=>'PAID_PRESENT', ...], ...]
     */
    public function getDayStatusRange($staff_id, $from, $to)
    {
        $rows = $this->db
            ->select('date, status, source, leave_id, payroll_impact')
            ->from('staff_day_status')
            ->where('staff_id', (int) $staff_id)
            ->where('date >=', $from)
            ->where('date <=', $to)
            ->get()->result_array();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['date']] = $row;
        }
        return $indexed;
    }

    /**
     * Get day-status records for multiple staff members within a date range.
     * Returns a nested array: [staff_id => [date => row]].
     * Used by payroll batch processing.
     *
     * @param  int[]  $staff_ids
     * @param  string $from  Y-m-d
     * @param  string $to    Y-m-d
     * @return array
     */
    public function getDayStatusRangeMultiStaff(array $staff_ids, $from, $to)
    {
        if (empty($staff_ids)) {
            return [];
        }
        $rows = $this->db
            ->select('staff_id, date, status, source, leave_id, payroll_impact')
            ->from('staff_day_status')
            ->where_in('staff_id', $staff_ids)
            ->where('date >=', $from)
            ->where('date <=', $to)
            ->get()->result_array();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['staff_id']][$row['date']] = $row;
        }
        return $indexed;
    }

    // ---------------------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------------------

    private function _loadLeaveRequestWithType($leave_request_id)
    {
        return $this->db
            ->select('slr.id, slr.staff_id, slr.leave_from, slr.leave_to, slr.leave_duration_type, slr.leave_days, lt.strict_day_lock, lt.is_lop, lt.credit_source_type_id, lt.type AS leave_type_name')
            ->from('staff_leave_request slr')
            ->join('leave_types lt', 'lt.id = slr.leave_type_id', 'left')
            ->where('slr.id', (int) $leave_request_id)
            ->get()->row_array() ?: [];
    }

    /**
     * Determine payroll_impact from leave type properties.
     *
     * OD-style (no credit consumed, no LOP)    → PAID_PRESENT
     * CPL-style (credit absorbs LOP)           → PAID_ABSENT
     * LOP-style (deduction)                    → LOP
     */
    private function _resolvePayrollImpact(array $lr)
    {
        if (!empty($lr['is_lop'])) {
            return 'LOP';
        }
        if (!empty($lr['credit_source_type_id'])) {
            // CPL-type: staff was absent but credit from OD absorbs the LOP
            return 'PAID_ABSENT';
        }
        // OD-type: the day is treated as if the staff was present on campus
        return 'PAID_PRESENT';
    }

    /**
     * Build a short status label (≤10 chars) from leave type name + duration type.
     * Examples: OD, CPL, FH-OD, SH-CPL
     */
    private function _resolveStatusLabel(array $lr)
    {
        $name = strtoupper(trim($lr['leave_type_name'] ?? 'LEAVE'));
        // Strip everything except uppercase letters, digits, hyphens; cap at 6 chars
        $base = preg_replace('/[^A-Z0-9\-]/', '', $name);
        $base = substr($base, 0, 6);

        $duration = $lr['leave_duration_type'] ?? 'full_day';
        if ($duration === 'first_half') {
            return substr('FH-' . $base, 0, 10);
        }
        if ($duration === 'second_half') {
            return substr('SH-' . $base, 0, 10);
        }
        return substr($base, 0, 10);
    }
}
