<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Leave Balance Setup Controller
 * Purpose: One-time setup page to set initial leave balances for all staff
 */
class Leave_balance_setup extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        
        $this->load->model('staff_model');
        $this->load->model('leavetypes_model');
        $this->load->model('setting_model');
    }

    /**
     * Display the initial leave balance setup page
     */
    public function index() {
        if (!$this->rbac->hasPrivilege('initial_leave_balance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leave_balance_setup/index');

        // Get all active staff with designation names
        $data['staff_list'] = $this->db
            ->select('staff.*, staff_designation.designation')
            ->from('staff')
            ->join('staff_designation', 'staff.designation = staff_designation.id', 'left')
            ->where('staff.is_active', 1)
            ->order_by('staff.name', 'asc')
            ->get()
            ->result_array();

        // Get configured leave increment rules
        $data['leave_rules'] = $this->db
            ->select('mlr.*, lt.type as leave_type_name')
            ->from('monthly_leave_increment_rules mlr')
            ->join('leave_types lt', 'mlr.leave_type_id = lt.id', 'left')
            ->where('mlr.enabled', 1)
            ->order_by('lt.type', 'asc')
            ->get()
            ->result_array();

        // Get existing leave balances
        $existing_balances = $this->db
            ->select('sld.staff_id, sld.leave_type_id, sld.alloted_leave')
            ->from('staff_leave_details sld')
            ->join('staff s', 's.id = sld.staff_id', 'inner')
            ->where('s.is_active', 1)
            ->get()
            ->result_array();

        // Organize balances by staff_id and leave_type_id
        $data['balances'] = [];
        foreach ($existing_balances as $balance) {
            $data['balances'][$balance['staff_id']][$balance['leave_type_id']] = $balance;
        }

        // Get settings
        $data['settings'] = $this->setting_model->getSetting();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/leave_balance_setup', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX: Save initial leave balances for all staff
     */
    public function ajax_save_balances() {
        if (!$this->rbac->hasPrivilege('initial_leave_balance', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }

        $balances = $this->input->post('balances'); // Array of staff_id => leave_type_id => balance
        
        if (empty($balances)) {
            echo json_encode(['status' => 'fail', 'message' => 'No balance data received']);
            return;
        }

        // Get current session
        $current_session = $this->db->where('is_active', 'yes')->get('sessions')->row();
        
        if (!$current_session) {
            echo json_encode(['status' => 'fail', 'message' => 'No active session found']);
            return;
        }

        $current_date = date('Y-m-d');
        $updated_count = 0;
        $inserted_count = 0;
        $errors = [];

        $manual_allowed_leave_type_ids = [];
        $has_balance_flag = $this->db->field_exists('requires_balance_check', 'leave_types');
        $this->db->select('id');
        $this->db->from('leave_types');
        if ($has_balance_flag) {
            $this->db->where('requires_balance_check', 1);
        }
        $leave_type_rows = $this->db->get()->result_array();
        foreach ($leave_type_rows as $leave_type_row) {
            $manual_allowed_leave_type_ids[] = (int) ($leave_type_row['id'] ?? 0);
        }
        $manual_allowed_lookup = array_fill_keys($manual_allowed_leave_type_ids, true);

        $this->db->trans_start();

        foreach ($balances as $staff_id => $leave_types) {
            foreach ($leave_types as $leave_type_id => $balance) {
                $leave_type_id = (int) $leave_type_id;

                if (!isset($manual_allowed_lookup[$leave_type_id])) {
                    continue;
                }

                // Skip if balance is empty or 0
                if ($balance === '' || $balance === null) {
                    continue;
                }

                $balance = floatval($balance);

                // Check if record exists
                $existing = $this->db
                    ->where('staff_id', $staff_id)
                    ->where('leave_type_id', $leave_type_id)
                    ->where('session_id', $current_session->id)
                    ->get('staff_leave_details')
                    ->row();

                if ($existing) {
                    // Update existing record
                    $this->db->where('id', $existing->id);
                    $result = $this->db->update('staff_leave_details', [
                        'alloted_leave' => $balance,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    if ($result) {
                        $updated_count++;
                    }
                } else {
                    // Insert new record
                    $result = $this->db->insert('staff_leave_details', [
                        'staff_id' => $staff_id,
                        'leave_type_id' => $leave_type_id,
                        'session_id' => $current_session->id,
                        'alloted_leave' => $balance,
                        'used_leave' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    if ($result) {
                        $inserted_count++;
                    }
                }
            }
        }

        // Update last_leave_increment_processed to current date
        // This ensures next increment happens from next month onwards
        $this->db->where('id', 1);
        $this->db->update('sch_settings', [
            'last_leave_increment_processed' => $current_date
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode([
                'status' => 'fail',
                'message' => 'Database error occurred while saving balances'
            ]);
            return;
        }

        $message = "Successfully saved initial leave balances! ";
        $message .= "Updated: {$updated_count}, Created: {$inserted_count}. ";
        $message .= "Next increment will occur from next month onwards.";

        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'updated' => $updated_count,
            'inserted' => $inserted_count,
            'last_processed' => $current_date
        ]);
    }

    /**
     * AJAX: Get current balances for a staff member
     */
    public function ajax_get_staff_balances() {
        $staff_id = $this->input->post('staff_id');
        
        if (!$staff_id) {
            echo json_encode(['status' => 'fail', 'message' => 'Staff ID required']);
            return;
        }

        $current_session = $this->db->where('is_active', 'yes')->get('sessions')->row();

        $balances = $this->db
            ->select('sld.leave_type_id, sld.alloted_leave, sld.used_leave, lt.type as leave_type_name')
            ->from('staff_leave_details sld')
            ->join('leave_types lt', 'lt.id = sld.leave_type_id', 'left')
            ->where('sld.staff_id', $staff_id)
            ->where('sld.session_id', $current_session->id)
            ->get()
            ->result_array();

        echo json_encode([
            'status' => 'success',
            'balances' => $balances
        ]);
    }
}
