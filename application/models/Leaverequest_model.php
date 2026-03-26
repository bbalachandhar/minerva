<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Leaverequest_model extends MY_model
{

    public function staff_leave_request($id = null)
    {
        if ($id != null) {
            $this->db->where("staff_leave_request.staff_id", $id);
        } elseif ($this->session->has_userdata('admin')) {
            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);

            $superadmin_visible = $this->customlib->superadmin_visible();
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $this->db->where("roles.id !=", 7);
            }
        }

        $query = $this->db->select('staff.name,staff.surname,staff.employee_id,staff_leave_request.*,leave_types.type,
            recommender.name as recommender_name, recommender.surname as recommender_surname,
            approver.name as approver_name, approver.surname as approver_surname, department.department_name')
            ->join("staff", "staff.id = staff_leave_request.staff_id")
            ->join("leave_types", "leave_types.id = staff_leave_request.leave_type_id")
            ->join("staff_roles", "staff_roles.staff_id = staff.id")
            ->join("roles", "staff_roles.role_id = roles.id")
            ->join("staff as recommender", "recommender.id = staff_leave_request.recommender_id", "left")
            ->join("staff as approver", "approver.id = staff_leave_request.approver_id", "left")
            ->join("department", "department.id = staff.department", "left")
            ->where("staff.is_active", "1")
            ->order_by("staff_leave_request.id", "desc")
            ->get("staff_leave_request");

        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $applied_by = $this->staff_model->get($value['applied_by']);
            if (!empty($applied_by['employee_id'])) {
                $result[$key]['applied_by'] = $applied_by['name'] . ' ' . $applied_by['surname'] . ' (' . $applied_by['employee_id'] . ')';
            } else {
                $result[$key]['applied_by'] = '';
            }
        }
        return $result;
    }

    public function user_leave_request($id = null)
    {
        $this->db->select('staff.name,staff.surname,staff.employee_id,staff_leave_request.*,leave_types.type,
            recommender.name as recommender_name, recommender.surname as recommender_surname,
            approver.name as approver_name, approver.surname as approver_surname');
        $this->db->join("staff", "staff.id = staff_leave_request.staff_id");
        $this->db->join("staff_roles", "staff_roles.staff_id = staff.id", "left");
        $this->db->join("roles", "staff_roles.role_id = roles.id", "left");
        $this->db->join("leave_types", "leave_types.id = staff_leave_request.leave_type_id");
        $this->db->join("staff as recommender", "recommender.id = staff_leave_request.recommender_id", "left");
        $this->db->join("staff as approver", "approver.id = staff_leave_request.approver_id", "left");
        $this->db->where("staff.is_active", "1");
        $this->db->where("staff.id", $id);

        if ($this->session->has_userdata('admin')) {
            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);
            $superadmin_visible = $this->customlib->superadmin_visible();
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $this->db->where("roles.id !=", 7);
            }
        }

        $this->db->order_by("staff_leave_request.id", "desc");
        $query = $this->db->get("staff_leave_request");

        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $applied_by = $this->staff_model->get($value['applied_by']);
            if (!empty($applied_by['employee_id'])) {
                $result[$key]['applied_by'] = $applied_by['name'] . ' ' . $applied_by['surname'] . ' (' . $applied_by['employee_id'] . ')';
            } else {
                $result[$key]['applied_by'] = '';
            }

        }
        return $result;
    }

    public function allotedLeaveType($id)
    {
        $query = $this->db->select('staff_leave_details.*,leave_types.type,leave_types.id as typeid')->where(array('staff_id' => $id))->join("leave_types", "staff_leave_details.leave_type_id = leave_types.id")->get("staff_leave_details");
        return $query->result_array();
    }

    public function myallotedLeaveType($id, $leave_type_id)
    {
        $query = $this->db->select('staff_leave_details.*,leave_types.type,leave_types.id as typeid , (SELECT sum(leave_days) from staff_leave_request WHERE leave_type_id=' . $leave_type_id . ' and staff_id=' . $id . ' and status !="disapprove") as `total_applied`', null, false)->where(array('staff_id' => $id, 'leave_types.id' => $leave_type_id))->join("leave_types", "staff_leave_details.leave_type_id = leave_types.id")->get("staff_leave_details");
        return $query->row_array();
    }

    public function countLeavesData($staff_id, $leave_type_id)
    {
        $query1 = $this->db->select('sum(leave_days) as approve_leave')->where(array('staff_id' => $staff_id, 'status!=' => 'disapprove', 'leave_type_id' => $leave_type_id))->get("staff_leave_request");
        return $query1->row_array();
    }

    public function countLeaveDaysInRange($staff_id, $leave_type_id, $start_date, $end_date)
    {
        $this->db->select('leave_from, leave_to, leave_days');
        $this->db->from('staff_leave_request');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('leave_type_id', $leave_type_id);
        $this->db->where('status!=', 'disapprove');
        $this->db->where('leave_from <=', $end_date);
        $this->db->where('leave_to >=', $start_date);
        $rows = $this->db->get()->result_array();

        $total = 0;
        foreach ($rows as $row) {
            $from = new DateTime(max($row['leave_from'], $start_date));
            $to = new DateTime(min($row['leave_to'], $end_date));
            $days = (int) $from->diff($to)->days + 1;

            if (!empty($row['leave_days']) && $row['leave_from'] === $row['leave_to']) {
                $total += (float) $row['leave_days'];
            } else {
                $total += $days;
            }
        }

        return $total;
    }

    public function changeLeaveStatus($data, $staff_id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where("id", $staff_id)->update("staff_leave_request", $data);
        $message   = UPDATE_RECORD_CONSTANT . " On staff leave request id " . $staff_id;
        $action    = "Update";
        $record_id = $staff_id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function getLeaveSummary()
    {
        $query = $this->db->select('*')->get("staff");
        return $query->result_array();
    }

    public function leave_remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('staff_leave_request');
        $message   = DELETE_RECORD_CONSTANT . " On staff leave request id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function check_if_leave_exists($staff_id, $leave_from, $leave_to, $exclude_id = null, $leave_duration_type = 'full_day')
    {
        $inactive_statuses = ['disapprove', 'disapproved', 'rejected', 'cancel', 'cancelled', 'canceled'];
        $has_duration_column = $this->db->field_exists('leave_duration_type', 'staff_leave_request');

        if ($has_duration_column) {
            $this->db->select('id, leave_from, leave_to, leave_days, COALESCE(leave_duration_type, "full_day") as leave_duration_type', false);
        } else {
            $this->db->select('id, leave_from, leave_to, leave_days');
        }
        $this->db->from('staff_leave_request');
        $this->db->where('staff_id', (int) $staff_id);
        $this->db->where('leave_from <=', $leave_to);
        $this->db->where('leave_to >=', $leave_from);
        $this->db->where_not_in('LOWER(status)', $inactive_statuses);

        if (!empty($exclude_id)) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return false;
        }

        $new_duration = strtolower(trim((string) $leave_duration_type));
        $is_new_half_day = in_array($new_duration, ['first_half', 'second_half'], true);

        if (!$is_new_half_day) {
            return true;
        }

        if ($leave_from !== $leave_to) {
            return true;
        }

        $existing_half_count = 0;
        foreach ($rows as $row) {
            $existing_duration = strtolower(trim((string) ($row['leave_duration_type'] ?? 'full_day')));
            if (!in_array($existing_duration, ['full_day', 'first_half', 'second_half'], true)) {
                $existing_duration = 'full_day';
            }

            if ($row['leave_from'] !== $row['leave_to']) {
                return true;
            }

            if ($existing_duration === 'full_day') {
                return true;
            }

            if ($existing_duration === $new_duration) {
                return true;
            }

            if (in_array($existing_duration, ['first_half', 'second_half'], true)) {
                $existing_half_count++;
            }
        }

        return $existing_half_count >= 2;
    }

    public function addLeaveRequest($data)
    {
        $has_duration_column = $this->db->field_exists('leave_duration_type', 'staff_leave_request');
        if (!$has_duration_column && isset($data['leave_duration_type'])) {
            unset($data['leave_duration_type']);
        }

        if (isset($data['id'])) {
            $leave_exists = $this->check_if_leave_exists($data['staff_id'], $data['leave_from'], $data['leave_to'], $data['id'], $data['leave_duration_type'] ?? 'full_day');
            if ($leave_exists) {
                return false;
            }
        } else {
            $leave_exists = $this->check_if_leave_exists($data['staff_id'], $data['leave_from'], $data['leave_to'], null, $data['leave_duration_type'] ?? 'full_day');
            if ($leave_exists) {
                return false;
            }
        }

        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {

                    $this->db->where("id", $data["id"]);
                    $this->db->update("staff_leave_request", $data);
                    $message   = UPDATE_RECORD_CONSTANT . " On staff leave request id " . $data['id'];
                    $action    = "Update";
                    $record_id = $data['id'];
                    $this->log($message, $record_id, $action);            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                return true;
            }
        } else {
            // Set initial status for new leave requests
            if (!isset($data['recommender_status'])) {
                $data['recommender_status'] = 'pending';
            }
            if (!isset($data['approver_status'])) {
                $data['approver_status'] = 'pending';
            }
             if (!isset($data['admin_remark'])) {
                $data['admin_remark'] = "";
            }
            
            $this->db->insert("staff_leave_request", $data);
            $id        = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On staff leave request id " . $id;
            $action    = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                return $id;
            }
        }
    }

    public function get_staff_leave($id)
    {
        $this->db->select('staff_leave_request.*');
        $this->db->from('staff_leave_request');
        $this->db->where('staff_leave_request.id', $id);
        $result = $this->db->get();
        return $result->row_array();
    }

    public function addLeaveSubstitutions($leave_request_id, $substitutions_data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);

        // Delete existing substitutions for this leave request (in case of update)
        $this->db->where('leave_request_id', $leave_request_id);
        $this->db->delete('leave_substitutions');

        if (!empty($substitutions_data)) {
            foreach ($substitutions_data as $key => $data) {
                $substitutions_data[$key]['leave_request_id'] = $leave_request_id;
            }
            $this->db->insert_batch('leave_substitutions', $substitutions_data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    public function getLeaveSubstitutions($leave_request_id)
    {
        $this->db->select('leave_substitutions.*, staff.name, staff.surname, staff.employee_id');
        $this->db->from('leave_substitutions');
        $this->db->join('staff', 'staff.id = leave_substitutions.substitute_staff_id', 'left');
        $this->db->where('leave_request_id', $leave_request_id);
        return $this->db->get()->result_array();
    }

    public function get_recommender_pending_leave_requests($recommender_id)
    {
        $query = $this->db->select('staff.name,staff.surname,staff.employee_id,staff_leave_request.*,leave_types.type,
            recommender.name as recommender_name, recommender.surname as recommender_surname,
            approver.name as approver_name, approver.surname as approver_surname, department.department_name')
            ->join("staff", "staff.id = staff_leave_request.staff_id")
            ->join("leave_types", "leave_types.id = staff_leave_request.leave_type_id")
            ->join("staff_roles", "staff_roles.staff_id = staff.id")
            ->join("roles", "staff_roles.role_id = roles.id")
            ->join("staff as recommender", "recommender.id = staff_leave_request.recommender_id", "left")
            ->join("staff as approver", "approver.id = staff_leave_request.approver_id", "left")
            ->join("department", "department.id = staff.department", "left")
            ->where("staff_leave_request.recommender_id", $recommender_id)
            ->where("staff_leave_request.recommender_status", "pending")
            ->where("staff.is_active", "1")
            ->order_by("staff_leave_request.id", "desc")
            ->get("staff_leave_request");

        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $applied_by = $this->staff_model->get($value['applied_by']);
            if (!empty($applied_by['employee_id'])) {
                $result[$key]['applied_by'] = $applied_by['name'] . ' ' . $applied_by['surname'] . ' (' . $applied_by['employee_id'] . ')';
            } else {
                $result[$key]['applied_by'] = '';
            }
        }
        return $result;
    }

    public function count_recommender_pending_leave_requests($recommender_id)
    {
        return (int) $this->db
            ->from('staff_leave_request')
            ->join('staff', 'staff.id = staff_leave_request.staff_id')
            ->where('staff_leave_request.recommender_id', (int) $recommender_id)
            ->where('staff_leave_request.recommender_status', 'pending')
            ->where('staff.is_active', '1')
            ->count_all_results();
    }

    public function count_all_recommender_pending_leave_requests()
    {
        return (int) $this->db
            ->from('staff_leave_request')
            ->join('staff', 'staff.id = staff_leave_request.staff_id')
            ->where('staff_leave_request.recommender_status', 'pending')
            ->where('staff.is_active', '1')
            ->count_all_results();
    }

    public function count_approver_pending_leave_requests($approver_id)
    {
        return (int) $this->db
            ->from('staff_leave_request')
            ->join('staff', 'staff.id = staff_leave_request.staff_id')
            ->where('staff_leave_request.approver_id', (int) $approver_id)
            ->where('staff_leave_request.approver_status', 'pending')
            ->where_in('staff_leave_request.recommender_status', ['approved', 'recommended'])
            ->where('staff.is_active', '1')
            ->count_all_results();
    }

    public function count_all_approver_pending_leave_requests()
    {
        return (int) $this->db
            ->from('staff_leave_request')
            ->join('staff', 'staff.id = staff_leave_request.staff_id')
            ->where('staff_leave_request.approver_status', 'pending')
            ->where_in('staff_leave_request.recommender_status', ['approved', 'recommended'])
            ->where('staff.is_active', '1')
            ->count_all_results();
    }

    public function get_all_recommender_pending_leave_requests()
    {
        $query = $this->db->select('staff.name,staff.surname,staff.employee_id,staff_leave_request.*,leave_types.type,
            recommender.name as recommender_name, recommender.surname as recommender_surname,
            approver.name as approver_name, approver.surname as approver_surname, department.department_name')
            ->join("staff", "staff.id = staff_leave_request.staff_id")
            ->join("leave_types", "leave_types.id = staff_leave_request.leave_type_id")
            ->join("staff_roles", "staff_roles.staff_id = staff.id")
            ->join("roles", "staff_roles.role_id = roles.id")
            ->join("staff as recommender", "recommender.id = staff_leave_request.recommender_id", "left")
            ->join("staff as approver", "approver.id = staff_leave_request.approver_id", "left")
            ->join("department", "department.id = staff.department", "left")
            ->where("staff_leave_request.recommender_status", "pending")
            ->where("staff.is_active", "1")
            ->order_by("staff_leave_request.id", "desc")
            ->get("staff_leave_request");

        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $applied_by = $this->staff_model->get($value['applied_by']);
            if (!empty($applied_by['employee_id'])) {
                $result[$key]['applied_by'] = $applied_by['name'] . ' ' . $applied_by['surname'] . ' (' . $applied_by['employee_id'] . ')';
            } else {
                $result[$key]['applied_by'] = '';
            }
        }
        return $result;
    }

    /**
     * Called after a non-LOP leave is finally approved.
     *
     * For requires_balance_check=0 (OD/CPL):
     *   Logs LEAVE_APPROVED_CREDIT — the earned credit audit trail.
     *   Balance computation remains authoritative at payroll sync time.
     *
     * For requires_balance_check=1 (CL/ML and any pre-allotted paid leave):
     *   Immediately deducts from used_for_leave_application in staff_monthly_leave_balance
     *   so the employee sees their balance reduced right away.
     *   Logs LEAVE_APPLICATION_DEBIT for the audit trail.
     *   Payroll will then exclude approved-leave absent days from LOP count
     *   to prevent double deduction.
     *
     * @param int $leave_request_id
     * @param int $approver_id  The user who triggered the final approval
     * @return bool
     */
    public function logLeaveApprovalCredit($leave_request_id, $approver_id)
    {
        $leave_req = $this->get_staff_leave($leave_request_id);
        if (empty($leave_req)) {
            return false;
        }

        $leave_type_id = (int) ($leave_req['leave_type_id'] ?? 0);
        $staff_id      = (int) ($leave_req['staff_id'] ?? 0);
        $leave_days    = (float) ($leave_req['leave_days'] ?? 0);
        $leave_from    = (string) ($leave_req['leave_from'] ?? '');

        if (!$leave_type_id || !$staff_id || !$leave_days || !$leave_from) {
            return false;
        }

        $has_balance_flag = $this->db->field_exists('requires_balance_check', 'leave_types');
        $has_credit_source_flag = $this->db->field_exists('credit_source_type_id', 'leave_types');
        $this->db->select(
            'type, is_lop'
            . ($has_balance_flag ? ', requires_balance_check' : '')
            . ($has_credit_source_flag ? ', credit_source_type_id' : '')
        );
        $type_row = $this->db->where('id', $leave_type_id)->limit(1)->get('leave_types')->row_array();

        if (empty($type_row)) {
            return false;
        }

        $is_lop = (int) ($type_row['is_lop'] ?? 0);
        if ($is_lop !== 0) {
            return false; // LOP-type leaves never earn or consume balance
        }

        if ($has_balance_flag) {
            $requires_balance_check = (int) ($type_row['requires_balance_check'] ?? 1);
        } else {
            $type_name = strtolower(trim((string) ($type_row['type'] ?? '')));
            $requires_balance_check = in_array($type_name, ['on duty', 'od'], true) ? 0 : 1;
        }

        // Detect credit-consumer type (e.g. CPL consuming OD credit pool)
        $credit_source_type_id = null;
        if ($has_credit_source_flag && !empty($type_row['credit_source_type_id'])) {
            $credit_source_type_id = (int) $type_row['credit_source_type_id'];
        }
        $is_credit_consumer = $credit_source_type_id !== null;

        $month = (int) date('m', strtotime($leave_from));
        $year  = (int) date('Y', strtotime($leave_from));

        // Look up or create the monthly balance row
        $balance_row = $this->db
            ->where('staff_id', $staff_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('month', $month)
            ->where('year', $year)
            ->limit(1)
            ->get('staff_monthly_leave_balance')
            ->row_array();

        if ($requires_balance_check === 0) {
            // OD (pure credit-earner) — audit only; payroll sync is authoritative.
            // Note: CPL (credit-consumer) is handled separately below.
            if (!$is_credit_consumer) {
                $balance_id = !empty($balance_row) ? (int) $balance_row['id'] : null;
                $balance_before = 0.0;
                
                if (!empty($balance_row)) {
                    $balance_before = (float) ($balance_row['closing_balance'] ?? 0);
                } else {
                    // Try to get latest previous balance carryover
                    $prev_balance_row = $this->db->where('staff_id', $staff_id)
                        ->where('leave_type_id', $leave_type_id)
                        ->order_by('year', 'DESC')->order_by('month', 'DESC')->limit(1)
                        ->get('staff_monthly_leave_balance')->row_array();
                    if (!empty($prev_balance_row)) {
                        $balance_before = (float) ($prev_balance_row['closing_balance'] ?? 0);
                    }
                }
                
                $balance_after  = $balance_before + $leave_days;

                // For CPL earning (requires_balance_check = 0 and not a consumer), update the monthly balance
                if ($balance_id) {
                    $this->db->where('id', $balance_id)->update('staff_monthly_leave_balance', [
                        'opening_balance' => (float)$balance_row['opening_balance'] + $leave_days,
                        'earned_in_month' => (float)$balance_row['earned_in_month'] + $leave_days,
                        'closing_balance' => $balance_after,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    $this->db->insert('staff_monthly_leave_balance', [
                        'staff_id' => $staff_id,
                        'leave_type_id' => $leave_type_id,
                        'month' => $month,
                        'year' => $year,
                        'opening_balance' => $balance_before + $leave_days,
                        'earned_in_month' => $leave_days,
                        'used_for_lop_adjustment' => 0,
                        'used_for_leave_application' => 0,
                        'closing_balance' => $balance_after,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $balance_id = $this->db->insert_id();
                }

                $reason = sprintf(
                    'Leave approved: %s to %s (%s days) — %s',
                    $leave_req['leave_from'],
                    $leave_req['leave_to'],
                    $leave_days,
                    $type_row['type']
                );

                return $this->db->insert('staff_leave_balance_audit', [
                    'balance_id'     => $balance_id,
                    'staff_id'       => $staff_id,
                    'leave_type_id'  => $leave_type_id,
                    'action_type'    => 'LEAVE_APPROVED_CREDIT',
                    'amount'         => $leave_days,
                    'balance_before' => $balance_before,
                    'balance_after'  => $balance_after,
                    'reference_id'   => $leave_request_id,
                    'reference_type' => 'leave_request',
                    'performed_by'   => $approver_id,
                    'reason'         => $reason,
                ]);
            }
        }

        // --- Credit-consumer type (e.g. CPL consuming OD earned credit) ---
        if ($is_credit_consumer) {
            // Find the source type (OD) monthly balance row for the same month/year.
            $src_balance_row = $this->db
                ->where('staff_id', $staff_id)
                ->where('leave_type_id', $credit_source_type_id)
                ->where('year', $year)
                ->where('month', $month)
                ->limit(1)
                ->get('staff_monthly_leave_balance')
                ->row_array();

            $src_balance_id = null;
            $src_before     = 0.0;
            $src_after      = 0.0;

            if (!empty($src_balance_row)) {
                $src_balance_id = (int) $src_balance_row['id'];
                $old_used       = (float) ($src_balance_row['used_for_leave_application'] ?? 0);
                $new_used       = $old_used + $leave_days;
                $src_before     = (float) ($src_balance_row['closing_balance'] ?? 0);
                $src_after      = max(0, $src_before - $leave_days);

                $this->db->where('id', $src_balance_id)->update('staff_monthly_leave_balance', [
                    'used_for_leave_application' => $new_used,
                    'closing_balance'            => $src_after,
                    'updated_at'                 => date('Y-m-d H:i:s'),
                ]);
            }

            $src_type_row = $this->db
                ->select('type')
                ->where('id', $credit_source_type_id)
                ->limit(1)
                ->get('leave_types')
                ->row_array();
            $src_type_name = $src_type_row['type'] ?? 'OD';

            return $this->db->insert('staff_leave_balance_audit', [
                'balance_id'     => $src_balance_id,
                'staff_id'       => $staff_id,
                'leave_type_id'  => $credit_source_type_id,
                'action_type'    => 'CREDIT_POOL_DEBIT',
                'amount'         => $leave_days,
                'balance_before' => $src_before,
                'balance_after'  => $src_after,
                'reference_id'   => $leave_request_id,
                'reference_type' => 'leave_request',
                'performed_by'   => $approver_id,
                'reason'         => sprintf(
                    '%s approved: consumed %s %s credit (%s to %s)',
                    $type_row['type'],
                    $leave_days,
                    $src_type_name,
                    $leave_req['leave_from'],
                    $leave_req['leave_to']
                ),
            ]);
        }

        // --- requires_balance_check = 1 (CL, ML, etc.) ---
        // When auto-adjust mode is ON (setting=1), payroll handles deduction via
        // buildLopAdjustmentLeavePool; just write an audit note and return.
        // When application-driven mode is OFF (setting=0, recommended), deduct immediately.
        $CI = &get_instance();
        $settings = $CI->setting_model->getSetting();
        $auto_adjust_preallotted = (int)($settings->auto_adjust_lop_with_preallotted_leaves ?? 0);

        if ($auto_adjust_preallotted === 1) {
            $balance_id     = !empty($balance_row) ? (int) $balance_row['id'] : null;
            $balance_before = !empty($balance_row) ? (float) ($balance_row['closing_balance'] ?? 0) : 0.0;
            $reason = sprintf(
                'Leave approved (auto-deduct mode): %s to %s (%s days) — %s',
                $leave_req['leave_from'],
                $leave_req['leave_to'],
                $leave_days,
                $type_row['type']
            );
            return $this->db->insert('staff_leave_balance_audit', [
                'balance_id'     => $balance_id,
                'staff_id'       => $staff_id,
                'leave_type_id'  => $leave_type_id,
                'action_type'    => 'LEAVE_APPROVED_AUDIT',
                'amount'         => $leave_days,
                'balance_before' => $balance_before,
                'balance_after'  => $balance_before,
                'reference_id'   => $leave_request_id,
                'reference_type' => 'leave_request',
                'performed_by'   => $approver_id,
                'reason'         => $reason,
            ]);
        }

        // Application-driven mode: immediately deduct so the employee sees the
        // reduced balance right away, without waiting for payroll.

        if (empty($balance_row)) {
            // Row doesn't exist yet. Seed opening_balance from staff_leave_details.
            $allotted = 0.0;
            $allot_row = $this->db
                ->where('staff_id', $staff_id)
                ->where('leave_type_id', $leave_type_id)
                ->limit(1)
                ->get('staff_leave_details')
                ->row_array();
            if (!empty($allot_row)) {
                $allotted = (float) ($allot_row['alloted_leave'] ?? 0);
            }

            $new_row = [
                'staff_id'                  => $staff_id,
                'leave_type_id'             => $leave_type_id,
                'year'                      => $year,
                'month'                     => $month,
                'opening_balance'           => $allotted,
                'earned_in_month'           => 0,
                'used_for_lop_adjustment'   => 0,
                'used_for_leave_application'=> $leave_days,
                'other_deductions'          => 0,
                'closing_balance'           => max(0, $allotted - $leave_days),
                'notes'                     => 'Auto-created on leave approval ' . date('Y-m-d H:i:s'),
            ];
            $this->db->insert('staff_monthly_leave_balance', $new_row);
            $balance_id     = $this->db->insert_id();
            $balance_before = $allotted;
            $balance_after  = max(0, $allotted - $leave_days);
        } else {
            $balance_id  = (int) $balance_row['id'];
            $old_used    = (float) ($balance_row['used_for_leave_application'] ?? 0);
            $new_used    = $old_used + $leave_days;
            $balance_before = (float) ($balance_row['closing_balance'] ?? 0);
            $balance_after  = max(0, $balance_before - $leave_days);

            $this->db->where('id', $balance_id)->update('staff_monthly_leave_balance', [
                'used_for_leave_application' => $new_used,
                'closing_balance'            => $balance_after,
                'updated_at'                 => date('Y-m-d H:i:s'),
            ]);
        }

        $reason = sprintf(
            'Leave application approved: %s to %s (%s days) — %s',
            $leave_req['leave_from'],
            $leave_req['leave_to'],
            $leave_days,
            $type_row['type']
        );

        return $this->db->insert('staff_leave_balance_audit', [
            'balance_id'     => $balance_id,
            'staff_id'       => $staff_id,
            'leave_type_id'  => $leave_type_id,
            'action_type'    => 'LEAVE_APPLICATION_DEBIT',
            'amount'         => $leave_days,
            'balance_before' => $balance_before,
            'balance_after'  => $balance_after,
            'reference_id'   => $leave_request_id,
            'reference_type' => 'leave_request',
            'performed_by'   => $approver_id,
            'reason'         => $reason,
        ]);
    }

    /**
     * Reverses the balance effect of logLeaveApprovalCredit() for a given leave request.
     * Should only be called when the request is in 'approved' status.
     *
     * @param int $leave_request_id
     * @param int $reverted_by  Staff ID of the user triggering the revert
     * @return bool
     */
    public function revertLeaveApproval($leave_request_id, $reverted_by)
    {
        $leave_req = $this->get_staff_leave($leave_request_id);
        if (empty($leave_req) || (string)($leave_req['status'] ?? '') !== 'approved') {
            return false;
        }

        $leave_type_id = (int)($leave_req['leave_type_id'] ?? 0);
        $staff_id      = (int)($leave_req['staff_id'] ?? 0);
        $leave_days    = (float)($leave_req['leave_days'] ?? 0);
        $leave_from    = (string)($leave_req['leave_from'] ?? '');

        if (!$leave_type_id || !$staff_id || !$leave_days || !$leave_from) {
            return false;
        }

        $has_balance_flag      = $this->db->field_exists('requires_balance_check', 'leave_types');
        $has_credit_source_flag = $this->db->field_exists('credit_source_type_id', 'leave_types');

        $this->db->select(
            'type, is_lop'
            . ($has_balance_flag ? ', requires_balance_check' : '')
            . ($has_credit_source_flag ? ', credit_source_type_id' : '')
        );
        $type_row = $this->db->where('id', $leave_type_id)->limit(1)->get('leave_types')->row_array();

        if (empty($type_row)) {
            return false;
        }

        $is_lop = (int)($type_row['is_lop'] ?? 0);
        if ($is_lop !== 0) {
            // LOP leaves never touched the balance — nothing to revert; just reset the request
            return true;
        }

        if ($has_balance_flag) {
            $requires_balance_check = (int)($type_row['requires_balance_check'] ?? 1);
        } else {
            $type_name = strtolower(trim((string)($type_row['type'] ?? '')));
            $requires_balance_check = in_array($type_name, ['on duty', 'od'], true) ? 0 : 1;
        }

        $credit_source_type_id = null;
        if ($has_credit_source_flag && !empty($type_row['credit_source_type_id'])) {
            $credit_source_type_id = (int)$type_row['credit_source_type_id'];
        }
        $is_credit_consumer = $credit_source_type_id !== null;

        $month = (int)date('m', strtotime($leave_from));
        $year  = (int)date('Y', strtotime($leave_from));

        $reason_prefix = sprintf(
            'Approval reverted: %s to %s (%s days) — %s',
            $leave_req['leave_from'],
            $leave_req['leave_to'],
            $leave_days,
            $type_row['type']
        );

        if ($requires_balance_check === 0 && !$is_credit_consumer) {
            // OD / CPL pure earner
            $balance_row = $this->db
                ->where('staff_id', $staff_id)
                ->where('leave_type_id', $leave_type_id)
                ->where('month', $month)
                ->where('year', $year)
                ->limit(1)
                ->get('staff_monthly_leave_balance')
                ->row_array();

            $balance_id     = !empty($balance_row) ? (int)$balance_row['id'] : null;
            $balance_before = !empty($balance_row) ? (float)($balance_row['closing_balance'] ?? 0) : 0.0;
            $balance_after  = max(0, $balance_before - $leave_days);

            if ($balance_id) {
                // Remove the previously earned days
                $new_opening = max(0, (float)$balance_row['opening_balance'] - $leave_days);
                $new_earned = max(0, (float)$balance_row['earned_in_month'] - $leave_days);
                
                $this->db->where('id', $balance_id)->update('staff_monthly_leave_balance', [
                    'opening_balance' => $new_opening,
                    'earned_in_month' => $new_earned,
                    'closing_balance' => $balance_after,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            $this->db->insert('staff_leave_balance_audit', [
                'balance_id'     => $balance_id,
                'staff_id'       => $staff_id,
                'leave_type_id'  => $leave_type_id,
                'action_type'    => 'LEAVE_APPROVAL_REVERTED',
                'amount'         => $leave_days,
                'balance_before' => $balance_before,
                'balance_after'  => $balance_after,
                'reference_id'   => $leave_request_id,
                'reference_type' => 'leave_request',
                'performed_by'   => $reverted_by,
                'reason'         => $reason_prefix . ' (Reverted balance credit)',
            ]);
            return true;
        }

        if ($is_credit_consumer) {
            // CPL — restore OD pool that was debited
            $src_balance_row = $this->db
                ->where('staff_id', $staff_id)
                ->where('leave_type_id', $credit_source_type_id)
                ->where('month', $month)
                ->where('year', $year)
                ->limit(1)
                ->get('staff_monthly_leave_balance')
                ->row_array();

            $src_balance_id = null;
            $src_before     = 0.0;
            $src_after      = 0.0;

            if (!empty($src_balance_row)) {
                $src_balance_id = (int)$src_balance_row['id'];
                $old_used       = (float)($src_balance_row['used_for_leave_application'] ?? 0);
                $new_used       = max(0, $old_used - $leave_days);
                $src_before     = (float)($src_balance_row['closing_balance'] ?? 0);
                $src_after      = $src_before + $leave_days;

                $this->db->where('id', $src_balance_id)->update('staff_monthly_leave_balance', [
                    'used_for_leave_application' => $new_used,
                    'closing_balance'            => $src_after,
                    'updated_at'                 => date('Y-m-d H:i:s'),
                ]);
            }

            $src_type_row  = $this->db->select('type')->where('id', $credit_source_type_id)->limit(1)->get('leave_types')->row_array();
            $src_type_name = $src_type_row['type'] ?? 'OD';

            $this->db->insert('staff_leave_balance_audit', [
                'balance_id'     => $src_balance_id,
                'staff_id'       => $staff_id,
                'leave_type_id'  => $credit_source_type_id,
                'action_type'    => 'LEAVE_APPROVAL_REVERTED',
                'amount'         => $leave_days,
                'balance_before' => $src_before,
                'balance_after'  => $src_after,
                'reference_id'   => $leave_request_id,
                'reference_type' => 'leave_request',
                'performed_by'   => $reverted_by,
                'reason'         => $reason_prefix . sprintf(' (CPL reverted — %s credit pool restored)', $src_type_name),
            ]);
            return true;
        }

        // requires_balance_check = 1 (CL, ML etc.)
        $CI = &get_instance();
        $settings = $CI->setting_model->getSetting();
        $auto_adjust_preallotted = (int)($settings->auto_adjust_lop_with_preallotted_leaves ?? 0);

        $balance_row = $this->db
            ->where('staff_id', $staff_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('month', $month)
            ->where('year', $year)
            ->limit(1)
            ->get('staff_monthly_leave_balance')
            ->row_array();

        $balance_id     = !empty($balance_row) ? (int)$balance_row['id'] : null;
        $balance_before = !empty($balance_row) ? (float)($balance_row['closing_balance'] ?? 0) : 0.0;
        $balance_after  = $balance_before;

        if ($auto_adjust_preallotted !== 1 && !empty($balance_row)) {
            // Undo the deduction: restore used_for_leave_application and closing_balance
            $old_used    = (float)($balance_row['used_for_leave_application'] ?? 0);
            $new_used    = max(0, $old_used - $leave_days);
            $balance_after = $balance_before + $leave_days;

            $this->db->where('id', $balance_id)->update('staff_monthly_leave_balance', [
                'used_for_leave_application' => $new_used,
                'closing_balance'            => $balance_after,
                'updated_at'                 => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->insert('staff_leave_balance_audit', [
            'balance_id'     => $balance_id,
            'staff_id'       => $staff_id,
            'leave_type_id'  => $leave_type_id,
            'action_type'    => 'LEAVE_APPROVAL_REVERTED',
            'amount'         => $leave_days,
            'balance_before' => $balance_before,
            'balance_after'  => $balance_after,
            'reference_id'   => $leave_request_id,
            'reference_type' => 'leave_request',
            'performed_by'   => $reverted_by,
            'reason'         => $reason_prefix . ($auto_adjust_preallotted === 1 ? ' (auto-adjust mode — audit only)' : ''),
        ]);

        return true;
    }

}