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
            ->select('id, type, requires_balance_check, credit_source_type_id')
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

        // Build a deterministic matrix for every active staff x active leave type.
        // Priority: latest monthly closing_balance, fallback: staff_leave_details.alloted_leave.
        $rows = $this->db->query("
            SELECT
                s.id AS staff_id,
                lt.id AS leave_type_id,
                COALESCE(smlb.closing_balance, sld.alloted_leave, 0) AS base_balance,
                smlb.year,
                smlb.month,
                lt.credit_source_type_id,
                lt.requires_balance_check,
                CASE
                    WHEN COALESCE(lt.credit_source_type_id, 0) > 0 THEN COALESCE((
                        SELECT SUM(r.leave_days)
                        FROM staff_leave_request r
                        WHERE r.staff_id = s.id
                          AND r.leave_type_id = lt.id
                          AND r.leave_direction = 'credit'
                          AND r.status IN ('approve', 'approved')
                          AND (
                                smlb.id IS NULL
                                OR r.leave_from > LAST_DAY(STR_TO_DATE(CONCAT(smlb.year, '-', LPAD(smlb.month, 2, '0'), '-01'), '%Y-%m-%d'))
                              )
                    ), 0)
                    ELSE 0
                END AS extra_credit,
                CASE
                    WHEN COALESCE(lt.credit_source_type_id, 0) > 0 THEN COALESCE((
                        SELECT SUM(r.leave_days)
                        FROM staff_leave_request r
                        WHERE r.staff_id = s.id
                          AND r.leave_type_id = lt.id
                          AND r.leave_direction = 'debit'
                          AND r.status IN ('approve', 'approved')
                          AND (
                                smlb.id IS NULL
                                OR r.leave_from > LAST_DAY(STR_TO_DATE(CONCAT(smlb.year, '-', LPAD(smlb.month, 2, '0'), '-01'), '%Y-%m-%d'))
                              )
                    ), 0)
                    ELSE 0
                END AS extra_debit
            FROM staff s
            JOIN leave_types lt ON lt.is_active = 'yes'
            LEFT JOIN staff_leave_details sld
                ON sld.staff_id = s.id
               AND sld.leave_type_id = lt.id
            LEFT JOIN staff_monthly_leave_balance smlb
                ON smlb.id = (
                    SELECT b.id
                    FROM staff_monthly_leave_balance b
                    WHERE b.staff_id = s.id
                      AND b.leave_type_id = lt.id
                    ORDER BY b.year DESC, b.month DESC, b.id DESC
                    LIMIT 1
                )
            WHERE s.is_active = 1
        ")->result_array();

        $data['balances'] = [];
        foreach ($rows as $row) {
            $display_balance = (float) $row['base_balance'];

            if ((int) ($row['credit_source_type_id'] ?? 0) > 0) {
                $display_balance += (float) ($row['extra_credit'] ?? 0) - (float) ($row['extra_debit'] ?? 0);
            }

            $data['balances'][$row['staff_id']][$row['leave_type_id']] = [
                'closing_balance' => $display_balance,
                'year'            => $row['year'],
                'month'           => $row['month'],
            ];
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
        $updated  = 0;
        $inserted = 0;
        $cur_year  = (int) date('Y');
        $cur_month = (int) date('n');

        $this->db->trans_start();

        foreach ($balances as $staff_id => $leave_types) {
            $staff_id = (int) $staff_id;
            if (!$staff_id) continue;

            foreach ($leave_types as $leave_type_id => $new_balance) {
                $leave_type_id = (int) $leave_type_id;
                if (!$leave_type_id) continue;
                if ($new_balance === null) continue;
                $new_balance = ($new_balance === '') ? 0 : max(0, (float) $new_balance);

                // Find the most recent row for this staff+leave_type
                $latest = $this->db
                    ->where('staff_id', $staff_id)
                    ->where('leave_type_id', $leave_type_id)
                    ->order_by('year', 'DESC')
                    ->order_by('month', 'DESC')
                    ->limit(1)
                    ->get('staff_monthly_leave_balance')
                    ->row_array();

                if (!empty($latest)) {
                    // Update closing_balance of the most recent row
                    $this->db->where('id', $latest['id']);
                    $this->db->update('staff_monthly_leave_balance', [
                        'closing_balance' => $new_balance,
                        'updated_at'      => date('Y-m-d H:i:s'),
                    ]);
                    $updated++;
                } else {
                    // No row exists yet — create one for the current month
                    $this->db->insert('staff_monthly_leave_balance', [
                        'staff_id'                  => $staff_id,
                        'leave_type_id'             => $leave_type_id,
                        'year'                      => $cur_year,
                        'month'                     => $cur_month,
                        'opening_balance'           => 0,
                        'earned_in_month'           => 0,
                        'used_for_lop_adjustment'   => 0,
                        'used_for_leave_application'=> 0,
                        'other_deductions'          => 0,
                        'closing_balance'           => $new_balance,
                        'last_processed_date'       => date('Y-m-d'),
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
