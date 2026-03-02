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

}