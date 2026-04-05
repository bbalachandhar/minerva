<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Monthly Leave Increment Automation Controller
 * 
 * This controller handles automatic monthly leave increments and annual resets
 * based on configured settings in sch_settings table.
 * 
 * Features:
 * - Automatically increments specified leave type by configured days each month
 * - Resets leave counts to 0 at configured month (e.g., January for annual reset)
 * - Can be triggered via cron job or manual execution
 * - Logs all operations for audit trail
 * 
 * Usage:
 * - Via cron:   0 2 * * * curl http://localhost/minerva/cron_leave_increment/process?secret_key=KEY
 * - Via manual: http://localhost/minerva/cron_leave_increment/manual_process
 */
class Cron_leave_increment extends MY_Controller {

    private $run_lock_name = null;

    public function __construct() {
        parent::__construct();
        // setting_model and leavetypes_model are already loaded by MY_Controller
    }

    private function acquire_run_lock($proc_year, $proc_month) {
        $this->run_lock_name = 'leave_increment_' . (int)$proc_year . '_' . (int)$proc_month;
        $row = $this->db->query('SELECT GET_LOCK(?, 2) AS lck', [$this->run_lock_name])->row_array();
        return !empty($row) && (int)$row['lck'] === 1;
    }

    private function release_run_lock() {
        if (empty($this->run_lock_name)) {
            return;
        }
        $this->db->query('SELECT RELEASE_LOCK(?)', [$this->run_lock_name]);
        $this->run_lock_name = null;
    }

    /**
     * Main process method - checks if increment is due and executes it
     */
    public function process() {
        // Verify cron secret key for security
        $settings = $this->setting_model->getSetting();
        $provided_key = $this->input->get('secret_key');
        
        if (empty($provided_key) || $provided_key !== $settings->cron_secret_key) {
            log_message('error', 'Leave Increment Cron: Invalid or missing secret key');
            show_error('Unauthorized access', 403);
            return;
        }

        // Check if monthly leave increment is enabled
        if (empty($settings->monthly_leave_increment_enabled)) {
            log_message('info', 'Leave Increment Cron: Feature is disabled in settings');
            echo json_encode(['status' => 'disabled', 'message' => 'Monthly leave increment is disabled']);
            return;
        }

        // Get enabled leave type rules from the new table
        $leave_rules = $this->db->select('mlr.*, lt.type as leave_type_name')
            ->from('monthly_leave_increment_rules mlr')
            ->join('leave_types lt', 'mlr.leave_type_id = lt.id', 'left')
            ->where('mlr.enabled', 1)
            ->where('lt.is_active', 'yes')
            ->get()
            ->result();

        // Validate configuration
        if (empty($leave_rules)) {
            log_message('error', 'Leave Increment Cron: No enabled leave type rules configured');
            echo json_encode(['status' => 'error', 'message' => 'No enabled leave type rules configured']);
            return;
        }

        $current_date = date('Y-m-d');
        $current_month = (int)date('n');
        $current_day = (int)date('j');

        // Check if we've already processed this month
        $last_processed = $settings->last_leave_increment_processed;
        if (!empty($last_processed)) {
            $last_processed_month = (int)date('n', strtotime($last_processed));
            $last_processed_year = (int)date('Y', strtotime($last_processed));
            $current_year = (int)date('Y');

            if ($last_processed_month == $current_month && $last_processed_year == $current_year) {
                log_message('info', 'Leave Increment Cron: Already processed for this month');
                echo json_encode(['status' => 'skipped', 'message' => 'Already processed for current month']);
                return;
            }
        }

        // Process on the 1st or 2nd of each month (in case 1st is missed)
        if ($current_day > 2) {
            log_message('info', 'Leave Increment Cron: Not the right day of month (current: ' . $current_day . ')');
            echo json_encode(['status' => 'skipped', 'message' => 'Will process on 1st or 2nd of month']);
            return;
        }

        // Check if this is the reset month (only when a reset month is configured)
        $configured_reset_month = isset($settings->leave_reset_month) ? (int)$settings->leave_reset_month : 0;
        $is_reset_month = ($configured_reset_month > 0 && $current_month === $configured_reset_month);

        if (!$this->acquire_run_lock((int)date('Y'), $current_month)) {
            echo json_encode(['status' => 'busy', 'message' => 'A leave increment run is already in progress for this month']);
            return;
        }

        try {
            if ($is_reset_month) {
                $result = $this->reset_leave_counts($settings, $leave_rules);
            } else {
                $result = $this->increment_leave_counts($settings, $leave_rules, (int)date('Y'), $current_month);
            }
        } finally {
            $this->release_run_lock();
        }

        // Update last processed date
        $this->db->where('id', $settings->id);
        $this->db->update('sch_settings', ['last_leave_increment_processed' => $current_date]);

        log_message('info', 'Leave Increment Cron: Completed - ' . json_encode($result));
        echo json_encode($result);
    }

    /**
     * Increment leave counts for all active staff (supports multiple leave types).
     *
     * @param object $settings   School settings row
     * @param array  $leave_rules Enabled leave type rules
     * @param int    $proc_year  Year to process (defaults to current year)
     * @param int    $proc_month Month to process 1-12 (defaults to current month)
     */
    private function increment_leave_counts($settings, $leave_rules, $proc_year = null, $proc_month = null) {
        $proc_year  = (int) ($proc_year  ?: date('Y'));
        $proc_month = (int) ($proc_month ?: date('n'));

        $active_staff_count = (int) $this->db->where('is_active', 1)->count_all_results('staff');

        $total_updated = 0;
        $results_by_type = [];
        $overall_success = true;

        // Previous month for opening_balance lookup
        $prev_month = $proc_month - 1;
        $prev_year  = $proc_year;
        if ($prev_month < 1) { $prev_month = 12; $prev_year = $proc_year - 1; }

        // Process each enabled leave type rule
        foreach ($leave_rules as $rule) {
            $leave_type_id   = $rule->leave_type_id;
            $increment_days  = (float) $rule->increment_days;
            $leave_type_name = $rule->leave_type_name;

            $this->db->trans_start();

            // Ensure one staff_leave_details row exists per active staff for this leave type.
            $this->db->query("\n                INSERT INTO staff_leave_details (staff_id, leave_type_id, alloted_leave, created_at, updated_at)\n                SELECT s.id, ?, 0, NOW(), NOW()\n                FROM staff s\n                LEFT JOIN staff_leave_details d\n                    ON d.staff_id = s.id\n                   AND d.leave_type_id = ?\n                WHERE s.is_active = 1\n                  AND d.id IS NULL\n            ", [$leave_type_id, $leave_type_id]);

            // Increment running total only when the monthly row does not already exist (idempotent).
            $this->db->query("\n                UPDATE staff_leave_details d\n                JOIN staff s\n                    ON s.id = d.staff_id\n                   AND s.is_active = 1\n                LEFT JOIN staff_monthly_leave_balance cur\n                    ON cur.staff_id = d.staff_id\n                   AND cur.leave_type_id = d.leave_type_id\n                   AND cur.year = ?\n                   AND cur.month = ?\n                SET d.alloted_leave = (\n                        CASE\n                            WHEN d.alloted_leave REGEXP '^[0-9]+(\\\\.[0-9]+)?$' THEN CAST(d.alloted_leave AS DECIMAL(10,2))\n                            ELSE 0\n                        END\n                    ) + ?,\n                    d.updated_at = NOW()\n                WHERE d.leave_type_id = ?\n                  AND cur.id IS NULL\n            ", [$proc_year, $proc_month, $increment_days, $leave_type_id]);

            // Insert monthly row for staff not yet processed in this month.
            $this->db->query("\n                INSERT INTO staff_monthly_leave_balance (\n                    staff_id, leave_type_id, year, month,\n                    opening_balance, earned_in_month,\n                    used_for_lop_adjustment, used_for_leave_application, other_deductions,\n                    closing_balance, last_processed_date, notes, created_at, updated_at\n                )\n                SELECT\n                    s.id,\n                    ?,\n                    ?,\n                    ?,\n                    COALESCE(prev.closing_balance, 0) AS opening_balance,\n                    ? AS earned_in_month,\n                    0 AS used_for_lop_adjustment,\n                    0 AS used_for_leave_application,\n                    0 AS other_deductions,\n                    COALESCE(prev.closing_balance, 0) + ? AS closing_balance,\n                    NOW(),\n                    'Monthly increment by cron',\n                    NOW(), NOW()\n                FROM staff s\n                LEFT JOIN staff_monthly_leave_balance prev\n                    ON prev.staff_id = s.id\n                   AND prev.leave_type_id = ?\n                   AND prev.year = ?\n                   AND prev.month = ?\n                LEFT JOIN staff_monthly_leave_balance cur\n                    ON cur.staff_id = s.id\n                   AND cur.leave_type_id = ?\n                   AND cur.year = ?\n                   AND cur.month = ?\n                WHERE s.is_active = 1\n                  AND cur.id IS NULL\n            ", [
                $leave_type_id,
                $proc_year,
                $proc_month,
                $increment_days,
                $increment_days,
                $leave_type_id,
                $prev_year,
                $prev_month,
                $leave_type_id,
                $proc_year,
                $proc_month,
            ]);

            // Keep current month rows consistent for re-runs.
            $this->db->query("\n                UPDATE staff_monthly_leave_balance\n                SET earned_in_month = ?,\n                    closing_balance = opening_balance + ? - used_for_lop_adjustment - used_for_leave_application - other_deductions,\n                    updated_at = NOW()\n                WHERE leave_type_id = ?\n                  AND year = ?\n                  AND month = ?\n            ", [$increment_days, $increment_days, $leave_type_id, $proc_year, $proc_month]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                log_message('error', "Leave Increment ({$leave_type_name}): transaction failed for {$proc_month}/{$proc_year}");
                $updated_count = 0;
                $overall_success = false;
            } else {
                $updated_count = $active_staff_count;
            }

            $processed_count = (int)$this->db
                ->where('leave_type_id', $leave_type_id)
                ->where('year', $proc_year)
                ->where('month', $proc_month)
                ->count_all_results('staff_monthly_leave_balance');

            if ($processed_count < $active_staff_count) {
                $overall_success = false;
            }

            $total_updated += $updated_count;
            $results_by_type[] = [
                'leave_type'    => $leave_type_name,
                'increment_days' => $increment_days,
                'staff_updated'  => $updated_count,
                'processed_rows' => $processed_count,
                'expected_rows'  => $active_staff_count,
            ];
        }

        return [
            'status'               => $overall_success ? 'success' : 'partial',
            'action'               => 'increment',
            'message'              => $overall_success
                ? 'Leave incremented successfully for multiple types'
                : 'Leave increment completed with gaps; rerun same month to auto-heal missing rows',
            'total_staff'          => $active_staff_count,
            'total_updated'        => $total_updated,
            'leave_types_processed'=> count($leave_rules),
            'details'              => $results_by_type,
            'month'                => date('F', mktime(0,0,0,$proc_month,1,$proc_year)) . ' ' . $proc_year,
        ];
    }

    /**
     * Reset leave counts to 0 for configured reset month (supports multiple leave types)
     */
    private function reset_leave_counts($settings, $leave_rules) {
        $total_reset = 0;
        $results_by_type = [];

        // Process each enabled leave type rule
        foreach ($leave_rules as $rule) {
            $leave_type_id = $rule->leave_type_id;
            $leave_type_name = $rule->leave_type_name;

            // Get all active staff with this leave type
            $this->db->select('staff_leave_details.*, staff.employee_id, staff.name');
            $this->db->from('staff_leave_details');
            $this->db->join('staff', 'staff.id = staff_leave_details.staff_id');
            $this->db->where('staff.is_active', 1);
            $this->db->where('staff_leave_details.leave_type_id', $leave_type_id);
            $leave_allocations = $this->db->get()->result_array();

            $reset_count = 0;

            foreach ($leave_allocations as $allocation) {
                // Reset to 0
                $this->db->where('staff_id', $allocation['staff_id']);
                $this->db->where('leave_type_id', $leave_type_id);
                $this->db->update('staff_leave_details', ['alloted_leave' => 0]);

                $reset_count++;

                log_message('info', "Leave Reset ({$leave_type_name}): Staff ID {$allocation['staff_id']} ({$allocation['employee_id']}) - Reset from {$allocation['alloted_leave']} to 0");
            }

            $total_reset += $reset_count;
            $results_by_type[] = [
                'leave_type' => $leave_type_name,
                'staff_reset' => $reset_count
            ];
        }

        return [
            'status' => 'success',
            'action' => 'reset',
            'message' => 'Leave counts reset successfully for multiple types',
            'total_reset' => $total_reset,
            'leave_types_processed' => count($leave_rules),
            'details' => $results_by_type,
            'reset_month' => date('F'),
            'month' => date('F Y')
        ];
    }

    /**
     * Manual trigger method for testing/backfill (requires admin login).
     *
     * Accepts optional GET params:
     *   ?year=2026&month=3   → process March 2026 specifically (backfill)
     * Without params, processes the current month.
     */
    public function manual_process() {
        // Check if user is logged in as admin
        if (!$this->session->userdata('admin')) {
            redirect('site/login');
            return;
        }

        // Long-running manual jobs should not stop halfway due to PHP execution limits.
        @set_time_limit(0);
        @ini_set('max_execution_time', 0);

        $settings = $this->setting_model->getSetting();

        if (empty($settings->monthly_leave_increment_enabled)) {
            echo '<h3>Monthly Leave Increment is Disabled</h3>';
            echo '<p>Please enable it in General Settings first.</p>';
            return;
        }

        // Allow year/month override for backdated processing
        $proc_year  = (int) ($this->input->get('year')  ?: date('Y'));
        $proc_month = (int) ($this->input->get('month') ?: date('n'));
        $proc_year  = max(2000, min((int)date('Y') + 1, $proc_year));
        $proc_month = max(1,    min(12, $proc_month));

        if (!$this->acquire_run_lock($proc_year, $proc_month)) {
            echo '<h3>Another leave increment run is already in progress</h3>';
            echo '<p>Please wait and retry in a minute.</p>';
            return;
        }

        echo '<h2>Manual Leave Increment Process</h2>';
        echo '<p><strong>Processing month:</strong> ' . date('F', mktime(0,0,0,$proc_month,1,$proc_year)) . ' ' . $proc_year . '</p>';

        // Get enabled leave type rules
        $leave_rules = $this->db->select('mlr.*, lt.type as leave_type_name')
            ->from('monthly_leave_increment_rules mlr')
            ->join('leave_types lt', 'mlr.leave_type_id = lt.id', 'left')
            ->where('mlr.enabled', 1)
            ->where('lt.is_active', 'yes')
            ->get()
            ->result();

        echo '<p>Current Settings:</p>';
        echo '<ul>';
        echo '<li>Enabled: Yes</li>';
        echo '<li>Configured Leave Types: ' . count($leave_rules) . '</li>';
        if (!empty($leave_rules)) {
            echo '<li>Leave Rules:';
            echo '<ul>';
            foreach ($leave_rules as $rule) {
                echo "<li>{$rule->leave_type_name}: +{$rule->increment_days} days/month</li>";
            }
            echo '</ul></li>';
        }
        $reset_month_val = isset($settings->leave_reset_month) ? (int)$settings->leave_reset_month : 0;
        echo '<li>Reset Month: ' . ($reset_month_val > 0 ? date('F', mktime(0, 0, 0, $reset_month_val, 1)) : '<em>No automatic reset configured</em>') . '</li>';
        echo '<li>Last Processed: ' . ($settings->last_leave_increment_processed ?: 'Never') . '</li>';
        echo '</ul>';

        if (empty($leave_rules)) {
            echo '<p style="color: red;">No enabled leave type rules found. Please configure at least one leave type rule.</p>';
            echo '<p><a href="' . base_url('schsettings') . '">Go to Settings</a></p>';
            return;
        }

        $configured_reset_month_m = isset($settings->leave_reset_month) ? (int)$settings->leave_reset_month : 0;
        $is_reset_month = ($configured_reset_month_m > 0 && $proc_month === $configured_reset_month_m);

        try {
            if ($is_reset_month) {
                $result = $this->reset_leave_counts($settings, $leave_rules);
            } else {
                $result = $this->increment_leave_counts($settings, $leave_rules, $proc_year, $proc_month);
            }
        } finally {
            $this->release_run_lock();
        }

        // Only update last_leave_increment_processed when processing the current month
        if ($proc_year == (int)date('Y') && $proc_month == (int)date('n')) {
            $this->db->where('id', $settings->id);
            $this->db->update('sch_settings', ['last_leave_increment_processed' => date('Y-m-d')]);
        }

        echo '<h3>Result:</h3>';
        echo '<pre>' . print_r($result, true) . '</pre>';

        // Quick links to process adjacent months
        $prev_m = $proc_month - 1; $prev_y = $proc_year;
        if ($prev_m < 1) { $prev_m = 12; $prev_y--; }
        $next_m = $proc_month + 1; $next_y = $proc_year;
        if ($next_m > 12) { $next_m = 1; $next_y++; }
        echo '<p>';
        echo '<a href="' . base_url("cron_leave_increment/manual_process?year={$prev_y}&month={$prev_m}") . '">← Process ' . date('M Y', mktime(0,0,0,$prev_m,1,$prev_y)) . '</a> &nbsp;|&nbsp; ';
        if ($next_y < (int)date('Y') || ($next_y == (int)date('Y') && $next_m <= (int)date('n'))) {
            echo '<a href="' . base_url("cron_leave_increment/manual_process?year={$next_y}&month={$next_m}") . '">Process ' . date('M Y', mktime(0,0,0,$next_m,1,$next_y)) . ' →</a> &nbsp;|&nbsp; ';
        }
        echo '<a href="' . base_url('schsettings') . '">Back to Settings</a>';
        echo '</p>';
    }
}
