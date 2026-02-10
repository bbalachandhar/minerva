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
 * - Via cron: */5 * * * * curl http://localhost/minerva/cron_leave_increment/process
 * - Via manual: http://localhost/minerva/cron_leave_increment/process?secret_key=YOUR_KEY
 */
class Cron_leave_increment extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('setting_model');
        $this->load->model('leavetypes_model');
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

        // Check if this is the reset month
        $is_reset_month = ($current_month == $settings->leave_reset_month);

        if ($is_reset_month) {
            $result = $this->reset_leave_counts($settings, $leave_rules);
        } else {
            $result = $this->increment_leave_counts($settings, $leave_rules);
        }

        // Update last processed date
        $this->db->where('id', $settings->id);
        $this->db->update('sch_settings', ['last_leave_increment_processed' => $current_date]);

        log_message('info', 'Leave Increment Cron: Completed - ' . json_encode($result));
        echo json_encode($result);
    }

    /**
     * Increment leave counts for all active staff (supports multiple leave types)
     */
    private function increment_leave_counts($settings, $leave_rules) {
        // Get all active staff
        $this->db->select('id, name, employee_id');
        $this->db->where('is_active', 1);
        $staff_list = $this->db->get('staff')->result_array();

        $total_updated = 0;
        $results_by_type = [];

        // Process each enabled leave type rule
        foreach ($leave_rules as $rule) {
            $leave_type_id = $rule->leave_type_id;
            $increment_days = $rule->increment_days;
            $leave_type_name = $rule->leave_type_name;

            $updated_count = 0;

            foreach ($staff_list as $staff) {
                // Check if staff has this leave type allocated
                $this->db->select('*');
                $this->db->where('staff_id', $staff['id']);
                $this->db->where('leave_type_id', $leave_type_id);
                $leave_detail = $this->db->get('staff_leave_details')->row();

                if ($leave_detail) {
                    // Increment existing allocation
                    $new_balance = $leave_detail->alloted_leave + $increment_days;

                    $this->db->where('staff_id', $staff['id']);
                    $this->db->where('leave_type_id', $leave_type_id);
                    $this->db->update('staff_leave_details', ['alloted_leave' => $new_balance]);

                    $updated_count++;

                    log_message('info', "Leave Increment ({$leave_type_name}): Staff ID {$staff['id']} ({$staff['employee_id']}) - Incremented from {$leave_detail->alloted_leave} to {$new_balance}");
                } else {
                    // Create new allocation with increment days
                    $this->db->insert('staff_leave_details', [
                        'staff_id' => $staff['id'],
                        'leave_type_id' => $leave_type_id,
                        'alloted_leave' => $increment_days
                    ]);

                    $updated_count++;

                    log_message('info', "Leave Increment ({$leave_type_name}): Staff ID {$staff['id']} ({$staff['employee_id']}) - Created new allocation with {$increment_days} days");
                }
            }

            $total_updated += $updated_count;
            $results_by_type[] = [
                'leave_type' => $leave_type_name,
                'increment_days' => $increment_days,
                'staff_updated' => $updated_count
            ];
        }

        return [
            'status' => 'success',
            'action' => 'increment',
            'message' => 'Leave incremented successfully for multiple types',
            'total_staff' => count($staff_list),
            'total_updated' => $total_updated,
            'leave_types_processed' => count($leave_rules),
            'details' => $results_by_type,
            'month' => date('F Y')
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
                $this->db->update('staff_leave_details', ['alloted_leave' => 0, 'used_leave' => 0]);

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
     * Manual trigger method for testing (requires admin login)
     */
    public function manual_process() {
        // Check if user is logged in as admin
        if (!$this->session->userdata('admin')) {
            redirect('site/login');
            return;
        }

        $settings = $this->setting_model->getSetting();
        
        if (empty($settings->monthly_leave_increment_enabled)) {
            echo '<h3>Monthly Leave Increment is Disabled</h3>';
            echo '<p>Please enable it in General Settings first.</p>';
            return;
        }

        echo '<h2>Manual Leave Increment Process</h2>';
        
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
        echo '<li>Reset Month: ' . date('F', mktime(0, 0, 0, $settings->leave_reset_month, 1)) . '</li>';
        echo '<li>Last Processed: ' . ($settings->last_leave_increment_processed ?: 'Never') . '</li>';
        echo '</ul>';

        if (empty($leave_rules)) {
            echo '<p style="color: red;">No enabled leave type rules found. Please configure at least one leave type rule.</p>';
            echo '<p><a href="' . base_url('schsettings') . '">Go to Settings</a></p>';
            return;
        }

        $current_month = (int)date('n');
        $is_reset_month = ($current_month == $settings->leave_reset_month);

        if ($is_reset_month) {
            $result = $this->reset_leave_counts($settings, $leave_rules);
        } else {
            $result = $this->increment_leave_counts($settings, $leave_rules);
        }

        // Update last processed date
        $this->db->where('id', $settings->id);
        $this->db->update('sch_settings', ['last_leave_increment_processed' => date('Y-m-d')]);

        echo '<h3>Result:</h3>';
        echo '<pre>' . print_r($result, true) . '</pre>';
        echo '<p><a href="' . base_url('schsettings') . '">Back to Settings</a></p>';
    }
}
