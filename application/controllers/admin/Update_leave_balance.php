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

        // Selected month/year — default to current month
        $sel_year  = (int) ($this->input->get('year')  ?: date('Y'));
        $sel_month = (int) ($this->input->get('month') ?: date('n'));
        $sel_year  = max(2020, min((int) date('Y') + 1, $sel_year));
        $sel_month = max(1, min(12, $sel_month));

        $data['sel_year']  = $sel_year;
        $data['sel_month'] = $sel_month;

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

        // Build balance matrix for the selected month.
        // For each staff x leave_type, show the opening_balance of that month's row.
        // If the row doesn't exist yet, fall back to the latest prior row's closing_balance.
        $rows = $this->db->query("
            SELECT
                s.id AS staff_id,
                lt.id AS leave_type_id,
                smlb.id AS row_id,
                smlb.opening_balance,
                smlb.earned_in_month,
                smlb.used_for_lop_adjustment,
                smlb.used_for_leave_application,
                smlb.other_deductions,
                smlb.closing_balance,
                smlb.year,
                smlb.month,
                -- fallback: latest prior month closing when target row missing
                (SELECT b2.closing_balance
                 FROM staff_monthly_leave_balance b2
                 WHERE b2.staff_id = s.id AND b2.leave_type_id = lt.id
                   AND (b2.year < ? OR (b2.year = ? AND b2.month < ?))
                 ORDER BY b2.year DESC, b2.month DESC LIMIT 1
                ) AS prior_closing,
                sld.alloted_leave
            FROM staff s
            JOIN leave_types lt ON lt.is_active = 'yes'
            LEFT JOIN staff_leave_details sld
                ON sld.staff_id = s.id AND sld.leave_type_id = lt.id
            LEFT JOIN staff_monthly_leave_balance smlb
                ON smlb.staff_id = s.id
               AND smlb.leave_type_id = lt.id
               AND smlb.year  = ?
               AND smlb.month = ?
            WHERE s.is_active = 1
        ", [$sel_year, $sel_year, $sel_month, $sel_year, $sel_month])->result_array();

        $data['balances'] = [];
        foreach ($rows as $row) {
            if (!empty($row['row_id'])) {
                // Row exists for this month — show opening_balance (what admin can edit for payroll)
                $display = (float) $row['opening_balance'];
            } elseif ($row['prior_closing'] !== null) {
                $display = (float) $row['prior_closing'];
            } else {
                $display = (float) ($row['alloted_leave'] ?? 0);
            }

            $data['balances'][$row['staff_id']][$row['leave_type_id']] = [
                'opening_balance'         => $display,
                'closing_balance'         => !empty($row['row_id']) ? (float) $row['closing_balance'] : null,
                'used_for_lop_adjustment' => (float) ($row['used_for_lop_adjustment'] ?? 0),
                'row_exists'              => !empty($row['row_id']),
                'year'                    => $row['year'] ?: $sel_year,
                'month'                   => $row['month'] ?: $sel_month,
            ];
        }

        $data['settings'] = $this->setting_model->getSetting();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/update_leave_balance', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX: Save all staff balances at once.
     * POST body: balances[staff_id][leave_type_id] = value, year, month
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

        $year  = (int) $this->input->post('year');
        $month = (int) $this->input->post('month');
        $result = $this->_save_balances($balances, $year, $month);
        echo json_encode($result);
    }

    /**
     * AJAX: Save a single staff member's balances.
     * POST body: staff_id, balances[leave_type_id] = value, year, month
     */
    public function ajax_save_one() {
        if (!$this->rbac->hasPrivilege('update_leave_balance', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }

        $staff_id       = (int) $this->input->post('staff_id');
        $leave_balances = $this->input->post('balances');
        $year           = (int) $this->input->post('year');
        $month          = (int) $this->input->post('month');

        if (!$staff_id || empty($leave_balances) || !is_array($leave_balances)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid data']);
            return;
        }

        $result = $this->_save_balances([$staff_id => $leave_balances], $year, $month);
        echo json_encode($result);
    }

    /**
     * Save admin-set opening balances for a specific month.
     * Writes directly to opening_balance, resets used_for_lop_adjustment=0
     * so payroll overwrite recalculates LOP from scratch.
     */
    private function _save_balances(array $balances, int $year, int $month) {
        $year  = max(2020, min((int) date('Y') + 1, $year ?: (int) date('Y')));
        $month = max(1, min(12, $month ?: (int) date('n')));
        $updated  = 0;
        $inserted = 0;
        $performed_by = $this->customlib->getStaffID();

        $this->db->trans_start();

        foreach ($balances as $staff_id => $leave_types) {
            $staff_id = (int) $staff_id;
            if (!$staff_id) continue;

            foreach ($leave_types as $leave_type_id => $new_opening) {
                $leave_type_id = (int) $leave_type_id;
                if (!$leave_type_id) continue;
                if ($new_opening === null || $new_opening === '') continue;
                $new_opening = max(0, (float) $new_opening);

                // Find the row for this specific month
                $existing = $this->db
                    ->where('staff_id', $staff_id)
                    ->where('leave_type_id', $leave_type_id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->limit(1)
                    ->get('staff_monthly_leave_balance')
                    ->row_array();

                if (!empty($existing)) {
                    $old_opening = (float) $existing['opening_balance'];
                    if (abs($new_opening - $old_opening) < 0.001) continue; // no change

                    // Recalculate closing without LOP (payroll fills that in on overwrite)
                    $new_closing = $new_opening
                        + (float) $existing['earned_in_month']
                        - (float) $existing['used_for_leave_application']
                        - (float) $existing['other_deductions'];
                    $new_closing = max(0, $new_closing);

                    $this->db->where('id', $existing['id']);
                    $this->db->update('staff_monthly_leave_balance', [
                        'opening_balance'         => $new_opening,
                        'used_for_lop_adjustment' => 0,     // reset so overwrite payroll recalculates fresh
                        'closing_balance'         => $new_closing,
                        'admin_adjustment'        => 0,     // clear any old workaround adjustment
                        'payslip_id'              => null,
                        'last_processed_date'     => null,
                        'updated_at'              => date('Y-m-d H:i:s'),
                    ]);

                    $this->db->insert('staff_leave_balance_audit', [
                        'balance_id'     => (int) $existing['id'],
                        'staff_id'       => $staff_id,
                        'leave_type_id'  => $leave_type_id,
                        'action_type'    => 'ADMIN_OVERRIDE',
                        'amount'         => $new_opening - $old_opening,
                        'balance_before' => $old_opening,
                        'balance_after'  => $new_opening,
                        'reference_id'   => null,
                        'reference_type' => 'admin_update_leave_balance',
                        'performed_by'   => $performed_by,
                        'reason'         => 'Admin set opening balance for ' . date('M Y', mktime(0,0,0,$month,1,$year)),
                        'created_at'     => date('Y-m-d H:i:s'),
                    ]);

                    $updated++;
                } else {
                    // No row for this month yet — insert one so payroll picks it up
                    $this->db->insert('staff_monthly_leave_balance', [
                        'staff_id'                   => $staff_id,
                        'leave_type_id'              => $leave_type_id,
                        'year'                       => $year,
                        'month'                      => $month,
                        'opening_balance'            => $new_opening,
                        'earned_in_month'            => 0,
                        'used_for_lop_adjustment'    => 0,
                        'used_for_leave_application' => 0,
                        'other_deductions'           => 0,
                        'closing_balance'            => $new_opening,
                        'admin_adjustment'           => 0,
                        'last_processed_date'        => null,
                        'notes'                      => 'Admin-seeded opening for ' . date('M Y', mktime(0,0,0,$month,1,$year)) . ' on ' . date('Y-m-d H:i:s'),
                        'created_at'                 => date('Y-m-d H:i:s'),
                        'updated_at'                 => date('Y-m-d H:i:s'),
                    ]);
                    $new_id = $this->db->insert_id();

                    if ($new_opening > 0) {
                        $this->db->insert('staff_leave_balance_audit', [
                            'balance_id'     => $new_id,
                            'staff_id'       => $staff_id,
                            'leave_type_id'  => $leave_type_id,
                            'action_type'    => 'ADMIN_OVERRIDE',
                            'amount'         => $new_opening,
                            'balance_before' => 0,
                            'balance_after'  => $new_opening,
                            'reference_id'   => null,
                            'reference_type' => 'admin_update_leave_balance',
                            'performed_by'   => $performed_by,
                            'reason'         => 'Admin-seeded opening balance for ' . date('M Y', mktime(0,0,0,$month,1,$year)),
                            'created_at'     => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $inserted++;
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['status' => 'fail', 'message' => 'Database error while saving balances.'];
        }

        $month_label = date('F Y', mktime(0, 0, 0, $month, 1, $year));
        return [
            'status'   => 'success',
            'message'  => 'Opening balances for ' . $month_label . ' saved. Updated: ' . $updated . ', New: ' . $inserted . '. Regenerate payroll for ' . $month_label . ' with overwrite to apply these balances.',
            'updated'  => $updated,
            'inserted' => $inserted,
        ];
    }
}
