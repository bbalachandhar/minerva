<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Itemissue_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $availability = $this->getIssuePlaceColumnAvailability();
        $issue_to_display_expr = $this->getIssueToDisplayExpression($availability);

        $sql   = "SELECT item_issue.*,item.name as `item_name`,item.item_category_id,item_category.item_category,staff.employee_id,staff.name as staff_name,staff.surname,roles.name as issue_type_name,
        " . $issue_to_display_expr . " as issue_to_display
        FROM `item_issue`
        INNER JOIN item on item.id=item_issue.item_id
        INNER JOIN item_category on item_category.id=item.item_category_id
        LEFT JOIN staff on staff.id=item_issue.issue_to
        LEFT JOIN roles on roles.id=item_issue.issue_type";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * This function is used to get issue item list
     * @param $id
     */
    public function getitemlist($filters = array())
    {
        $availability = $this->getIssuePlaceColumnAvailability();
        $issue_to_display_expr = $this->getIssueToDisplayExpression($availability);

        $condition = '';

        $issue_target_type = trim((string) ($filters['issue_target_type'] ?? ''));
        if ($availability['issue_target_type'] && $issue_target_type !== '' && in_array($issue_target_type, array('staff', 'place'), true)) {
            $condition .= " AND item_issue.issue_target_type = " . $this->db->escape($issue_target_type);
        }

        $issue_location_type = trim((string) ($filters['issue_location_type'] ?? ''));
        if ($availability['issue_location_type'] && $issue_location_type !== '') {
            $condition .= " AND item_issue.issue_location_type = " . $this->db->escape($issue_location_type);
        }

        $issue_place_name = trim((string) ($filters['issue_place_name'] ?? ''));
        if ($availability['issue_place_name'] && $issue_place_name !== '') {
            $escaped = $this->db->escape_like_str($issue_place_name);
            $condition .= " AND item_issue.issue_place_name LIKE '%" . $escaped . "%'";
        }

        $issue_floor = trim((string) ($filters['issue_floor'] ?? ''));
        if ($availability['issue_floor'] && $issue_floor !== '') {
            $escaped = $this->db->escape_like_str($issue_floor);
            $condition .= " AND item_issue.issue_floor LIKE '%" . $escaped . "%'";
        }

        $issue_room_no = trim((string) ($filters['issue_room_no'] ?? ''));
        if ($availability['issue_room_no'] && $issue_room_no !== '') {
            $escaped = $this->db->escape_like_str($issue_room_no);
            $condition .= " AND item_issue.issue_room_no LIKE '%" . $escaped . "%'";
        }

        $sql = "select item_issue.*,item.name as `item_name`,item.item_category_id,item_category.item_category,staff.employee_id,staff.name as staff_name,staff.surname,issueby.employee_id as issueby_employee_id,issueby.name as issueby_staff_name,issueby.surname as issueby_surname,roles.name as issue_type_name,
        " . $issue_to_display_expr . " as issue_to_display
        from item_issue
         inner join item on item.id=item_issue.item_id
         inner join item_category on item_category.id=item.item_category_id
         left join staff on staff.id=item_issue.issue_to
         inner join staff as issueby on issueby.id=item_issue.issue_by         
         left join roles on roles.id=item_issue.issue_type
         where 1 " . $condition;
        $this->datatables->query($sql)
            ->orderable('item.id,item.name,item_category,issue_date,staff.name,issue_by,quantity,null')
            ->searchable('item.id,item.name,item_category,issue_date,staff.name,issue_by,item_issue.quantity,null')
            ->sort('item_issue.id','desc')
            ->query_where_enable(true);
        return $this->datatables->generate('json');
    }

    private function getIssuePlaceColumnAvailability()
    {
        static $availability = null;
        if ($availability !== null) {
            return $availability;
        }

        $availability = array(
            'issue_target_type' => $this->db->field_exists('issue_target_type', 'item_issue'),
            'issue_location_type' => $this->db->field_exists('issue_location_type', 'item_issue'),
            'issue_place_name' => $this->db->field_exists('issue_place_name', 'item_issue'),
            'issue_floor' => $this->db->field_exists('issue_floor', 'item_issue'),
            'issue_room_no' => $this->db->field_exists('issue_room_no', 'item_issue'),
            'issue_block' => $this->db->field_exists('issue_block', 'item_issue'),
            'issue_building' => $this->db->field_exists('issue_building', 'item_issue'),
        );

        return $availability;
    }

    private function getIssueToDisplayExpression($availability)
    {
        $staff_expr = "CONCAT(IFNULL(staff.name,''),' ',IFNULL(staff.surname,''), IFNULL(CONCAT(' (',staff.employee_id,')'),''))";

        if (!$availability['issue_target_type']) {
            return $staff_expr;
        }

        $location_type_expr = $availability['issue_location_type'] ? "IFNULL(item_issue.issue_location_type,'')" : "''";
        $location_type_separator_expr = $availability['issue_location_type'] ? "IF(item_issue.issue_location_type IS NULL OR item_issue.issue_location_type = '', '', ': ')" : "''";
        $place_name_expr = $availability['issue_place_name'] ? "IFNULL(item_issue.issue_place_name,'')" : "''";
        $floor_expr = $availability['issue_floor'] ? "IF(item_issue.issue_floor IS NULL OR item_issue.issue_floor = '', '', CONCAT(', Floor ', item_issue.issue_floor))" : "''";
        $room_expr = $availability['issue_room_no'] ? "IF(item_issue.issue_room_no IS NULL OR item_issue.issue_room_no = '', '', CONCAT(', Room ', item_issue.issue_room_no))" : "''";
        $block_expr = $availability['issue_block'] ? "IF(item_issue.issue_block IS NULL OR item_issue.issue_block = '', '', CONCAT(', Block ', item_issue.issue_block))" : "''";
        $building_expr = $availability['issue_building'] ? "IF(item_issue.issue_building IS NULL OR item_issue.issue_building = '', '', CONCAT(', ', item_issue.issue_building))" : "''";

        $place_expr = "TRIM(CONCAT(" .
            $location_type_expr . "," .
            $location_type_separator_expr . "," .
            $place_name_expr . "," .
            $floor_expr . "," .
            $room_expr . "," .
            $block_expr . "," .
            $building_expr .
        "))";

        return "CASE WHEN item_issue.issue_target_type = 'place' THEN " . $place_expr . " ELSE " . $staff_expr . " END";
    }

    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('item_issue');
        $message   = DELETE_RECORD_CONSTANT . " On item issue id " . $id;
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

    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */
    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('item_issue', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  item issue id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
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
        } else {
            $this->db->insert('item_issue', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On item issue id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
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
            return $insert_id;
        }
    }

    public function get_IssueInventoryReport($start_date, $end_date)
    {
        $condition = " and date_format(item_issue.issue_date,'%Y-%m-%d') between '" . $start_date . "' and '" . $end_date . "'";

        $sql = "SELECT item_issue.*,item.name as `item_name`,item.item_category_id,item_category.item_category,staff.employee_id,staff.name as staff_name,staff.surname
        ,issued_by.employee_id as issued_by_employee_id,issued_by.name as issued_by_name,issued_by.surname as issued_by_surname,roles.name as issue_type_name
        FROM `item_issue`
        INNER JOIN item on item.id=item_issue.item_id 
        INNER JOIN item_category on item_category.id=item.item_category_id
        LEFT JOIN staff on staff.id=item_issue.issue_to
        INNER JOIN staff as issued_by on issued_by.id=item_issue.issue_by
        LEFT JOIN roles on roles.id=item_issue.issue_type
        where 1 " . $condition;
        $this->datatables->query($sql)
            ->orderable('item.name,item_category,issue_date,staff_name,issue_by,item_issue.quantity')
            ->searchable('item.name,item_category,issue_date,staff.name,issue_by,item_issue.quantity')
            ->query_where_enable(true);
        return $this->datatables->generate('json');
    }

}
