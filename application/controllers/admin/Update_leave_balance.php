<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Update_leave_balance extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('staff_model');
        $this->load->model('leavetypes_model');
    }

    public function index() {
        if (!$this->rbac->hasPrivilege('update_leave_balance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/update_leave_balance/index');

        // All active leave types
        $data['leave_types'] = $this->db
            ->select('id, type')
            ->from('leave_types')
            ->where('is_active', 'yes')
            ->order_by('type', 'asc')
            ->get()
            ->result_array();

        // All active staff with designation
        $data['staff_list'] = $this->db
            ->select('staff.id, staff.name, staff.surname, staff.employee_id, staff_designation.designation')
            ->from('staff')
            ->join('staff_designation', 'staff.designation = staff_designation.id', 'left')
            ->where('staff.is_active', 1)
            ->order_by('staff.name', 'asc')
            ->get()
            ->result_array();

        // Load existing balances indexed by [staff_id][leave_type_id]
        $rows = $this->db
            ->select('staff_id, leave_type_id, alloted_leave')
            ->from('staff_leave_details')
            ->get()
            ->result_array();

        $data['balances'] = [];
        foreach ($rows as $row) {
            $data['balances'][$row['staff_id']][$row['leave_type_id']] = $row['alloted_leave'];
        }

        $data['settings'] = $this->setting_model->getSetting();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/update_leave_balance', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX: Save all staff balances at once.
     * POST body: balances[staff_id][leave_type_id] = value
     */
    public function ajax_save_all() {
        if (!$this->rbac->hasPrivilege('update_leave_balance', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }

        $balances = $this->input->post('balances');
        if (empty($balances) || !is_array($balances)) {
            echo json_encode(['status' => 'fail', 'message' => 'No data received']);
            return;
        }

        $result = $this->_save_balances($balances);
        echo json_encode($result);
    }

    /**
     * AJAX: Save a single staff member's balances.
     * POST body: staff_id, balances[leave_type_id] = value
     */
    public function ajax_save_one() {
        if (!$this->rbac->hasPrivilege('update_leave_balance', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }

        $staff_id = (int) $this->input->post('staff_id');
        $leave_balances = $this->input->post('balances');

        if (!$staff_id || empty($leave_balances) || !is_array($leave_balances)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid data']);
            return;
        }

        $result = $this->_save_balances([$staff_id => $leave_balances]);
        echo json_encode($result);
    }

    private function _save_balances(array $balances) {
        $updated = 0;
        $inserted = 0;

        $this->db->trans_start();

        foreach ($balances as $staff_id => $leave_types) {
            $staff_id = (int) $staff_id;
            if (!$staff_id) continue;

            foreach ($leave_types as $leave_type_id => $balance) {
                $leave_type_id = (int) $leave_type_id;
                if (!$leave_type_id) continue;

                // Allow empty/zero to clear balance; skip only truly null submissions
                if ($balance === null) continue;
                $balance_value = ($balance === '') ? '' : (string) floatval($balance);

                $existing = $this->db
                    ->where('staff_id', $staff_id)
                    ->where('leave_type_id', $leave_type_id)
                    ->get('staff_leave_details')
                    ->row();

                if ($existing) {
                    $this->db->where('id', $existing->id);
                    $this->db->update('staff_leave_details', [
                        'alloted_leave' => $balance_value,
                        'updated_at'    => date('Y-m-d H:i:s'),
                    ]);
                    $updated++;
                } else {
                    $this->db->insert('staff_leave_details', [
                        'staff_id'      => $staff_id,
                        'leave_type_id' => $leave_type_id,
                        'alloted_leave' => $balance_value,
                    ]);
                    $inserted++;
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['status' => 'fail', 'message' => 'Database error while saving balances.'];
        }

        return [
            'status'   => 'success',
            'message'  => 'Leave balances saved successfully. Updated: ' . $updated . ', New: ' . $inserted . '.',
            'updated'  => $updated,
            'inserted' => $inserted,
        ];
    }
}
