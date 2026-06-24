<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class enquiry_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getclasses($id = null)
    {
        $this->db->select()->from('classes');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function get_enquiry_type()
    {
        $this->db->select('*');
        $this->db->from('enquiry_type');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getComplaintSource()
    {
        $this->db->select('*');
        $this->db->from('source');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getComplaintType()
    {
        $this->db->select('*');
        $this->db->from('complaint_type');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_reference()
    {
        $this->db->select('*');
        $this->db->from('reference');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function add($data)
    {
        $result = $this->db->insert('enquiry', $data);
        if (!$result) {
            $err = $this->db->error();
            log_message('error', '[Enquiry_model::add] INSERT failed - Code: ' . $err['code'] . ' Message: ' . $err['message']);
            log_message('error', '[Enquiry_model::add] Last query: ' . $this->db->last_query());
            return false;
        }
        $id = $this->db->insert_id();
        $message   = INSERT_RECORD_CONSTANT . " On enquiry id " . $id;
        $this->log($message, $id, "Insert");
        return $id;
    }

    public function getenquiry_list($id = null, $status = 'active', $lead_vendor_id = null)

    {

        $this->db->select('enquiry.*,classes.class as classname,staff.id as staff_id,staff.name as staff_name,staff.surname as staff_surname,staff.employee_id,online_admission_courses.course_name as admission_course_name,online_admission_courses.mgt_fee as course_fee,lv.vendor_name as lead_vendor_name,lv.vendor_code as lead_vendor_code,dsv.vendor_name as duplicate_source_vendor_name,dsv.vendor_code as duplicate_source_vendor_code')->

            join("classes", "enquiry.class_id = classes.id", "left")->

            join("staff", "staff.id = enquiry.assigned", "left")->
            
            join("online_admission_courses", "enquiry.admission_course_id = online_admission_courses.id", "left")->

            join("lead_api_vendors lv", "lv.id = enquiry.lead_vendor_id", "left")->

            join("lead_api_vendors dsv", "dsv.id = enquiry.duplicate_source_vendor_id", "left");

            

        if (!empty($id)) {

            $this->db->where("enquiry.id", $id);

        }



                if (is_array($status)) {



                    $this->db->where_in('enquiry.status', $status);



                } else if ($status != 'all') { // Only apply status filter if not 'all'



                    $this->db->where('enquiry.status', $status);



                }

        if (!empty($lead_vendor_id)) {
            $this->db->where('enquiry.lead_vendor_id', (int) $lead_vendor_id);
        }

        

        // primary sort: last inserted first, secondary: newest date first
        $this->db->order_by("enquiry.id", "desc");
        $this->db->order_by("enquiry.date", "desc");

        $query = $this->db->get("enquiry");



        if (!empty($id)) {

            return $query->row_array();

        } else {

            return $query->result_array();

        }

    }

    public function getFollowByEnquiry($id)
    {
        $query = $this->db->select("*")->where("enquiry_id", $id)->order_by("id", "desc")->get("follow_up");
        return $query->row_array();
    }

    public function getLatestFollowUps($enquiry_ids)
    {
        if (empty($enquiry_ids)) {
            return [];
        }

        // Sanitize IDs
        $enquiry_ids = array_map('intval', $enquiry_ids);
        $mapped = [];

        // Chunk IDs to avoid hitting max_allowed_packet limits in MySQL if there are many thousand queries
        $chunks = array_chunk($enquiry_ids, 1000);
        foreach ($chunks as $chunk) {
            $ids_string = implode(',', $chunk);
            $sql = "SELECT f.* FROM follow_up f
                    JOIN (
                        SELECT MAX(id) as max_id FROM follow_up
                        WHERE enquiry_id IN ({$ids_string})
                        GROUP BY enquiry_id
                    ) max_f ON f.id = max_f.max_id";

            $query = $this->db->query($sql);
            $result = $query->result_array();

            foreach ($result as $row) {
                $mapped[$row['enquiry_id']] = $row;
            }
        }

        return $mapped;
    }

    public function getfollow_up_list($enquiry_id, $follow_up = null)
    {
        $this->db->select('follow_up.*, staff.employee_id, staff.name, staff.surname,enquiry.created_by')->from('follow_up');
        $this->db->join('enquiry', 'enquiry.id = follow_up.enquiry_id');
        $this->db->join('staff', 'staff.id = follow_up.followup_by')->join("staff_roles", "staff_roles.staff_id = staff.id", "left");

        if ($this->session->has_userdata('admin')) {
            $getStaffRole       = $this->customlib->getStaffRole();
            $staffrole          = json_decode($getStaffRole);
            $superadmin_visible = $this->customlib->superadmin_visible();
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $this->db->where("staff_roles.role_id !=", 7);
            }
        }

        if ($follow_up != null) {
            $this->db->where('follow_up.id', $follow_up);
            $this->db->where('follow_up.enquiry_id', $enquiry_id);
            $this->db->order_by('follow_up.id desc');
        } else {
            $this->db->where('follow_up.enquiry_id', $enquiry_id);
            $this->db->order_by('follow_up.id desc');
        }
        $query = $this->db->get();
        if ($follow_up != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function add_follow_up($data)
    {
        $this->db->insert('follow_up', $data);
    }

    public function follow_up_update($enquiry_id, $follow_up_id, $data)
    {
        $this->db->where('id', $follow_up_id);
        $this->db->where('enquiry_id', $enquiry_id);
        $this->db->update('follow_up', $data);
        redirect('admin/enquiry/follow_up_edit/' . $enquiry_id . '/' . $follow_up_id . '');
    }

    public function enquiry_update($id, $data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->update('enquiry', $data);
        $message   = UPDATE_RECORD_CONSTANT . " On  enquiry id " . $id;
        $action    = "Update";
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

    public function enquiry_delete($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('enquiry');
        $message   = DELETE_RECORD_CONSTANT . " On  enquiry id " . $id;
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

    public function delete_follow_up($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('follow_up');
    }

    public function next_follow_up_date($enquiry_id)
    {
        $this->db->select('max(`id`) as id');
        $this->db->from('follow_up');
        $this->db->where('enquiry_id', $enquiry_id);
        $query = $this->db->get();
        $data  = $query->row_array();
        $id    = $data['id'];
        $this->db->select('*');
        $this->db->from('follow_up');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function changeStatus($data)
    {
        $this->db->where("id", $data["id"])->update("enquiry", $data);
    }

    public function searchEnquiry($class, $source, $date_from, $date_to, $status = 'active', $department_id = null, $lead_vendor_id = null, $last_follow_up_from = null, $last_follow_up_to = null)
    {
        $this->db->select('enquiry.*,classes.class as classname,online_admission_courses.course_name as admission_course_name,lv.vendor_name as lead_vendor_name,lv.vendor_code as lead_vendor_code,dsv.vendor_name as duplicate_source_vendor_name,dsv.vendor_code as duplicate_source_vendor_code')
            ->join("classes", "classes.id = enquiry.class_id", "left")
            ->join("online_admission_courses", "enquiry.admission_course_id = online_admission_courses.id", "left")
            ->join("lead_api_vendors lv", "lv.id = enquiry.lead_vendor_id", "left")
            ->join("lead_api_vendors dsv", "dsv.id = enquiry.duplicate_source_vendor_id", "left");

        if (!empty($class)) {
            $this->db->where("enquiry.class_id", $class);
        }

        if (!empty($source)) {
            $this->db->where("source", $source);
        }
        
        if (!empty($status)) {
            if ($status != 'all') {
                $this->db->where("enquiry.status", $status);
            }
            // If $status is 'all', no 'where' clause is added, showing all records.
        } else {
            $this->db->where("enquiry.status", 'active');
        }

        if ((!empty($date_from)) && (!empty($date_to))) {
            $this->db->where("date >= ", $date_from);
            $this->db->where("date <= ", $date_to);
        }
        
        if ($department_id != null) {
            $this->db->where("classes.department_id", $department_id);
        }

        if (!empty($lead_vendor_id)) {
            $this->db->where('enquiry.lead_vendor_id', (int) $lead_vendor_id);
        }

        // Filter by Next Follow Up Date (latest follow_up.next_date, fallback to enquiry.follow_up_date)
        if (!empty($last_follow_up_from) && !empty($last_follow_up_to)) {
            $from_esc = $this->db->escape($last_follow_up_from);
            $to_esc   = $this->db->escape($last_follow_up_to);
            $this->db->where(
                "COALESCE((SELECT next_date FROM follow_up WHERE enquiry_id = enquiry.id ORDER BY id DESC LIMIT 1), enquiry.follow_up_date) BETWEEN {$from_esc} AND {$to_esc}",
                null, false
            );
        }

        // last inserted first, secondary: newest date
        $this->db->order_by("enquiry.id", "desc");
        $this->db->order_by("enquiry.date", "desc");

        $query = $this->db->get("enquiry");
        log_message('error', '[searchEnquiry DEBUG] SQL: ' . $this->db->last_query());
        return $query->result_array();
    }

    public function check_number($phone_number)
    {
        $this->db->select('contact,name');
        $this->db->from('enquiry');
        $this->db->where("contact", $phone_number);
        $result = $this->db->get();
        return $result->row_array();
    }

    /**
     * Server-Side Processing query for DataTables.
     * Returns paginated, searched, ordered enquiry rows with follow-up data
     * resolved in a single LEFT JOIN subquery (no N+1).
     */
    public function dtenquirylist_ssp($draw, $start, $length, $search_value, $order_col_idx, $order_dir, $filter)
    {
        $db = $this->db;

        // Map DataTable column index → SQL expression
        $col_map = [
            0 => 'e.name',
            1 => 'e.contact',
            2 => 'oac.course_name',
            3 => 'e.source',
            4 => 'dsv.vendor_name',
            5 => 'e.date',
            6 => 'f.date',
            7 => 'COALESCE(f.next_date, e.follow_up_date)',
            8 => 'e.status',
        ];
        $order_col = isset($col_map[$order_col_idx]) ? $col_map[$order_col_idx] : 'e.id';
        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Build filter WHERE parts (no search)
        $filter_parts = [];
        $status = !empty($filter['status']) ? $filter['status'] : 'active';
        if ($status !== 'all') {
            $filter_parts[] = "e.status = " . $db->escape($status);
        }
        if (!empty($filter['admission_course_id'])) {
            $filter_parts[] = "e.admission_course_id = " . (int) $filter['admission_course_id'];
        }
        if (!empty($filter['source'])) {
            $filter_parts[] = "e.source = " . $db->escape($filter['source']);
        }
        if (!empty($filter['lead_vendor_id'])) {
            $filter_parts[] = "e.lead_vendor_id = " . (int) $filter['lead_vendor_id'];
        }
        if (!empty($filter['is_duplicate'])) {
            if ($filter['is_duplicate'] === 'yes') {
                $filter_parts[] = "e.duplicate_source_vendor_id IS NOT NULL";
            } elseif ($filter['is_duplicate'] === 'no') {
                $filter_parts[] = "e.duplicate_source_vendor_id IS NULL";
            }
        }
        if (!empty($filter['date_from'])) {
            $filter_parts[] = "e.date >= " . $db->escape($filter['date_from']);
        }
        if (!empty($filter['date_to'])) {
            $filter_parts[] = "e.date <= " . $db->escape($filter['date_to']);
        }
        $has_fu_filter = !empty($filter['next_followup_from']) && !empty($filter['next_followup_to']);
        if ($has_fu_filter) {
            $filter_parts[] = "COALESCE(f.next_date, e.follow_up_date) BETWEEN "
                . $db->escape($filter['next_followup_from'])
                . " AND "
                . $db->escape($filter['next_followup_to']);
        }

        $filter_where = !empty($filter_parts) ? 'WHERE ' . implode(' AND ', $filter_parts) : '';

        // Add search condition on top of filter
        $search_where = $filter_where;
        if (!empty($search_value)) {
            $sv = $db->escape('%' . $search_value . '%');
            $sc = [
                "e.name LIKE $sv", "e.contact LIKE $sv", "e.email LIKE $sv",
                "e.source LIKE $sv", "e.ref_no LIKE $sv",
                "lv.vendor_name LIKE $sv", "e.city LIKE $sv",
            ];
            $s_sql = '(' . implode(' OR ', $sc) . ')';
            $search_where = !empty($filter_where) ? "$filter_where AND $s_sql" : "WHERE $s_sql";
        }

        // Base FROM + JOINs  (follow_up resolved once per enquiry via correlated MAX subquery)
        $base_from = "FROM enquiry e
            LEFT JOIN classes c ON c.id = e.class_id
            LEFT JOIN online_admission_courses oac ON oac.id = e.admission_course_id
            LEFT JOIN lead_api_vendors lv ON lv.id = e.lead_vendor_id
            LEFT JOIN lead_api_vendors dsv ON dsv.id = e.duplicate_source_vendor_id
            LEFT JOIN follow_up f ON f.id = (
                SELECT MAX(f2.id) FROM follow_up f2 WHERE f2.enquiry_id = e.id
            )";

        // Count without search (for recordsTotal per filter set)
        $records_total = (int) $db->query("SELECT COUNT(e.id) as cnt $base_from $filter_where")->row_array()['cnt'];

        // Count with search (for recordsFiltered)
        $records_filtered = (int) $db->query("SELECT COUNT(e.id) as cnt $base_from $search_where")->row_array()['cnt'];

        // Paginated data
        $start  = max(0, (int) $start);
        $length = max(1, min(200, (int) $length));

        $rows = $db->query("
            SELECT
                e.id, e.ref_no, e.name, e.contact, e.email, e.state, e.city,
                e.source, e.date, e.follow_up_date, e.status, e.created_by, e.assigned,
                e.lead_vendor_id, e.duplicate_source_vendor_id,
                oac.course_name as admission_course_name,
                lv.vendor_name as lead_vendor_name,
                dsv.vendor_name as duplicate_source_vendor_name,
                f.date as followupdate,
                f.next_date
            $base_from
            $search_where
            ORDER BY $order_col $order_dir
            LIMIT $start, $length
        ")->result_array();

        return [
            'draw'            => (int) $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $rows,
        ];
    }

}
