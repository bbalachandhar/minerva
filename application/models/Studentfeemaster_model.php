<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Studentfeemaster_model extends MY_Model
{

    protected $balance_group;
    protected $balance_type;

    public function __construct()
    {
        parent::__construct();
        $this->load->config('ci-blog');
        $this->balance_group   = $this->config->item('ci_balance_group');
        $this->balance_type    = $this->config->item('ci_balance_type');
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function searchAssignFeeByClassSection($class_id = null, $section_id = null, $fee_session_group_id = null, $category = null, $gender = null, $rte = null, $hostel_id = null)
    {
        $sql = "SELECT IFNULL(`student_fees_master`.`id`, '0') as `student_fees_master_id`,`classes`.`id` AS `class_id`,"
            . " `student_session`.`id` as `student_session_id`, `students`.`id`, "
            . "`classes`.`class`, `sections`.`id` AS `section_id`, `sections`.`section`, "
            . "`students`.`id`, `students`.`admission_no`, `students`.`roll_no`,"
            . " `students`.`admission_date`, `students`.`firstname`, `students`.`middlename`,`students`.`lastname`,"
            . " `students`.`image`, `students`.`mobileno`, `students`.`email`, `students`.`state`,"
            . " `students`.`city`, `students`.`pincode`, `students`.`religion`, `students`.`dob`, "
            . "`students`.`current_address`, `students`.`permanent_address`,"
            . " IFNULL(students.category_id, 0) as `category_id`,"
            . " IFNULL(categories.category, '') as `category`,"
            . " `students`.`adhar_no`, `students`.`samagra_id`,"
            . " `students`.`bank_account_no`, `students`.`bank_name`, `students`.`ifsc_code`,"
            . " `students`.`guardian_name`, `students`.`guardian_relation`, `students`.`guardian_phone`,"
            . " `students`.`guardian_address`, `students`.`is_active`, `students`.`created_at`,"
            . " `students`.`updated_at`, `students`.`father_name`, `students`.`rte`,"
            . " `students`.`gender` FROM `students` JOIN `student_session` "
            . "ON `student_session`.`student_id` = `students`.`id` JOIN `classes` "
            . "ON `student_session`.`class_id` = `classes`.`id` JOIN `sections` "
            . "ON `sections`.`id` = `student_session`.`section_id` LEFT JOIN `categories` "
            . "ON `students`.`category_id` = `categories`.`id` LEFT JOIN student_fees_master on"
            . " student_fees_master.student_session_id=student_session.id"
            . "  AND student_fees_master.fee_session_group_id=" . $this->db->escape($fee_session_group_id);

        if ($hostel_id != null) {
            $sql .= " JOIN `hostel_rooms` ON `hostel_rooms`.`id` = `students`.`hostel_room_id`";
        }

        $sql .= " WHERE `student_session`.`session_id` =  " . $this->current_session
            . " and `students`.`is_active` =  'yes'";

        if ($class_id != null) {
            $sql .= " AND `student_session`.`class_id` = " . $this->db->escape($class_id);
        }
        if ($section_id != null) {
            $sql .= " AND `student_session`.`section_id` =" . $this->db->escape($section_id);
        }
        if ($category != null) {
            $sql .= " AND `students`.`category_id` =" . $this->db->escape($category);
        }
        if ($gender != null) {
            $sql .= " AND `students`.`gender` =" . $this->db->escape($gender);
        }
        if ($rte != null) {
            $sql .= " AND `students`.`rte` =" . $this->db->escape($rte);
        }
        if ($hostel_id != null) {
            $sql .= " AND `hostel_rooms`.`hostel_id` =" . $this->db->escape($hostel_id);
        }
        $sql .= " ORDER BY `students`.`id`";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function add($data)
    {
        $this->db->where('student_session_id', $data['student_session_id']);
        $this->db->where('fee_session_group_id', $data['fee_session_group_id']);
        $q = $this->db->get('student_fees_master');

        if ($q->num_rows() > 0) {
            return $q->row()->id;
        } else {
            $this->db->insert('student_fees_master', $data);
            $id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On student fees master id " . $id;
            $action    = "Insert";
            $record_id = $id;            
        }
    }

    public function assign_bulk_fees($fee_session_group, $student_session_id, $delete_fee_session_group)
    {
        if (!empty($fee_session_group)) {
            $data_insert = array();
            foreach ($fee_session_group as $fee_session_key => $fee_session_value) {
                $array = array();
                $array['is_system'] = 0;
                $array['student_session_id'] = $student_session_id;
                $array['fee_session_group_id'] = $fee_session_value;
                $data_insert[] = $array;
            }
            $this->db->insert_batch('student_fees_master', $data_insert);
        }

        if (!empty($delete_fee_session_group)) {
            $this->db->where('student_session_id', $student_session_id);
            $this->db->where_in('fee_session_group_id', $delete_fee_session_group);
            $this->db->delete('student_fees_master');
        }
    }

    public function addPreviousBal($student_data, $due_date)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $fee_group_exists = $this->feegroup_model->checkGroupExistsByName($this->balance_group);
        $fee_type_exists  = $this->feetype_model->checkFeetypeByName($this->balance_type);
        $fee_group_id     = 0;
        $fee_type_id      = 0;
        if (!$fee_group_exists) {
            $this->db->insert('fee_groups', array('name' => $this->balance_group, 'is_system' => 1));
            $fee_group_id = $this->db->insert_id();
        } else {
            $fee_group_id = $fee_group_exists->id;
        }

        if (!$fee_type_exists) {
            $this->db->insert('feetype', array('type' => $this->balance_type, 'code' => $this->balance_type, 'is_system' => 1));
            $fee_type_id = $this->db->insert_id();
        } else {
            $fee_type_id = $fee_type_exists->id;
        }
        $to_be_insert = array(
            'session_id'           => $this->current_session,
            'fee_groups_id'        => $fee_group_id,
            'feetype_id'           => $fee_type_id,
            'fee_session_group_id' => 0,
            'due_date'             => $due_date,
        );
        $parentid = $this->feesessiongroup_model->group_exists($to_be_insert['fee_groups_id']);

        $to_be_insert['fee_session_group_id'] = $parentid;

        $session_group_exists = $this->feesessiongroup_model->checkExists($to_be_insert);
        if (!$session_group_exists) {
            $this->db->insert('fee_groups_feetype', $to_be_insert);
        } else {
            $this->db->where('id', $session_group_exists);
            $this->db->update('fee_groups_feetype', $to_be_insert);
        }
        $student_list = array();
        if (isset($student_data) && !empty($student_data)) {

            $total_rec = count($student_data);
            for ($i = 0; $i < $total_rec; $i++) {
                $student_list[]                           = $student_data[$i]['student_session_id'];
                $student_data[$i]['id']                   = 0;
                $student_data[$i]['fee_session_group_id'] = $parentid;
                $student_data[$i]['is_active']            = 'yes';
            }
            $check_insert_feemaster = $this->selectInArray($parentid, $student_list);
            if (!empty($check_insert_feemaster)) {
                $insert_new_student = array();
                foreach ($student_data as $student_key => $student_value) {
                    $student_data[$student_key]['id'] = $this->findValueExists($check_insert_feemaster, $student_value['student_session_id']);
                    if ($student_data[$student_key]['id'] == 0) {
                        $insert_new_student[] = $student_data[$student_key];
                        unset($student_data[$student_key]);
                    }
                }

                if (!empty($insert_new_student)) {
                    $this->db->insert_batch('student_fees_master', $insert_new_student);
                }
                $this->db->update_batch('student_fees_master', $student_data, 'id');
            } else {
                $this->db->insert_batch('student_fees_master', $student_data);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function findValueExists($array, $find)
    {
        $id = 0;
        foreach ($array as $x => $x_value) {
            if ($x_value->student_session_id == $find) {
                return $x_value->id;
            }
        }
        return $id;
    }

    public function selectInArray($fee_session_groups, $student_session_array)
    {
        $this->db->where('fee_session_group_id', $fee_session_groups);
        $this->db->where_in('student_session_id', $student_session_array);
        $q      = $this->db->get('student_fees_master');
        $result = $q->result();
        return $result;
    }

    public function delete($fee_session_groups, $array)
    {
        $this->db->where('fee_session_group_id', $fee_session_groups);
        $this->db->where_in('student_session_id', $array);
        $this->db->delete('student_fees_master');
    }

    public function getBalanceMasterRecord($group_name, $student_session_array)
    {
        $sql = "select * from student_fees_master where student_session_id in $student_session_array and fee_session_group_id=(SELECT id FROM `fee_session_groups` where fee_groups_id=(SELECT id FROM `fee_groups` WHERE name=" . "'" . $group_name . "'" . ") and session_id=$this->current_session)";

        $query  = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    public function getStudentFeesByClassSectionStudent($class_id = NULL, $section_id = NULL, $student_id = NULL, $department_id = NULL)
    {
        $where_condition = array();
        if ($class_id != NULL) {
            if (is_array($class_id)) {
                $where_condition[] = " and student_session.class_id IN (" . implode(',', array_map(array($this->db, 'escape'), $class_id)) . ")";
            } else {
                $where_condition[] = " and student_session.class_id=" . $this->db->escape($class_id);
            }
        }
        if ($section_id != NULL) {
            $where_condition[] = " and student_session.section_id=" . $section_id;
        }
        if ($student_id != NULL) {
            $where_condition[] = " and student_session.student_id=" . $student_id;
        }
        if ($department_id != NULL) {
            $where_condition[] = " and classes.department_id=" . $department_id; // Add department filter
        }

        $where_condition_string = implode(" ", $where_condition);

        $sql = "SELECT student_fees_master.*,student_session.id as `student_session_id`,student_session.route_pickup_point_id,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,students.category_id,students.image,students.id as student_id,students.father_name,students.admission_no,students.mobileno,students.roll_no,students.rte, IFNULL(categories.category, '') as `category` FROM `student_fees_master` INNER JOIN student_session on student_session.id=student_fees_master.student_session_id INNER JOIN students on students.id=student_session.student_id INNER JOIN classes on classes.id =student_session.class_id left join  categories on students.category_id = categories.id INNER join sections on sections.id=student_session.section_id  WHERE student_session.session_id=" . $this->db->escape($this->current_session) . " AND (student_session.is_alumni = 0 OR student_session.is_alumni IS NULL)" . $where_condition_string;

        $query        = $this->db->query($sql);
        $result       = $query->result();
        $student_fees = array();
        if (!empty($result)) {
            $all_master_ids  = [];
            $all_session_ids = [];
            foreach ($result as $rv) {
                $all_master_ids[]  = $rv->id;
                $all_session_ids[] = $rv->student_session_id;
            }
            $batch_fees      = $this->getDueFeesForMultipleMasters(array_unique($all_master_ids));
            $batch_discounts = $this->feediscount_model->getStudentFeesDiscountBatch(array_unique($all_session_ids));

            foreach ($result as $result_key => $result_value) {
                $student_fees_master_id = $result_value->id;
                $result_value->fees     = isset($batch_fees[$student_fees_master_id]) ? $batch_fees[$student_fees_master_id] : [];

                if ($result_value->is_system != 0 && isset($result_value->fees[0])) {
                    $result_value->fees[0]->amount = $result_value->amount;
                }

                if (!array_key_exists($result_value->student_session_id, $student_fees)) {

                    $student_fees[$result_value->student_session_id] = array(
                        'student_session_id'    => $result_value->student_session_id,
                        'route_pickup_point_id' => $result_value->route_pickup_point_id,
                        'firstname'             => $result_value->firstname,
                        'student_id'            => $result_value->student_id,
                        'middlename'            => $result_value->middlename,
                        'lastname'              => $result_value->lastname,
                        'class_id'              => $result_value->class_id,
                        'class'                 => $result_value->class,
                        'section'               => $result_value->section,
                        'father_name'           => $result_value->father_name,
                        'admission_no'          => $result_value->admission_no,
                        'mobileno'              => $result_value->mobileno,
                        'roll_no'               => $result_value->roll_no,
                        'category_id'           => $result_value->category_id,
                        'category'              => $result_value->category,
                        'rte'                   => $result_value->rte,
                        'image'                 => $result_value->image
                    ); //the magic

                    $student_fees[$result_value->student_session_id]['student_discount_fee'] =
                        isset($batch_discounts[$result_value->student_session_id])
                        ? $batch_discounts[$result_value->student_session_id]
                        : [];
                }

                $student_fees[$result_value->student_session_id]['fees'][] = $result_value->fees;
            }
        }

        return $student_fees;
    }

    public function getStudentFees($student_session_id)
    {
        if (empty($student_session_id)) {
            return [];
        }
        $sql    = "SELECT `student_fees_master`.*,fee_groups.name,fee_session_groups.fee_groups_id as `fsg_fee_groups_id` FROM `student_fees_master` INNER JOIN fee_session_groups on student_fees_master.fee_session_group_id=fee_session_groups.id INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  WHERE `student_session_id` = " . (int)$student_session_id . " ORDER BY `student_fees_master`.`id`";
        $query  = $this->db->query($sql);
        if (!$query) {
            return [];
        }
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $result_key => $result_value) {

                $fee_session_group_id   = $result_value->fee_session_group_id;
                $student_fees_master_id = $result_value->id;
                $result_value->fees     = $this->getDueFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id);

                if ($result_value->is_system != 0) {
                    $result_value->fees[0]->amount = $result_value->amount;
                }
            }

            // When a fee group has exactly two installments (e.g. Odd/Even semester),
            // label them so identically-named fee rows aren't shown as indistinguishable duplicates.
            $by_fee_group = [];
            foreach ($result as $result_value) {
                $by_fee_group[$result_value->fsg_fee_groups_id][] = $result_value;
            }
            foreach ($by_fee_group as $rows) {
                if (count($rows) != 2) {
                    continue;
                }
                usort($rows, function ($a, $b) {
                    return $a->fee_session_group_id - $b->fee_session_group_id;
                });
                $rows[0]->installment_label = 'Odd Sem';
                $rows[1]->installment_label = 'Even Sem';
            }
        }

        return $result;
    }

    public function getTransStudentFees($student_session_id)
    {
        $sql    = "SELECT `student_fees_master`.*,fee_groups.name FROM `student_fees_master` INNER JOIN fee_session_groups on student_fees_master.fee_session_group_id=fee_session_groups.id INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  WHERE `student_session_id` = " . $student_session_id . " ORDER BY `student_fees_master`.`id`";
        $query  = $this->db->query($sql);
        $result_value = $query->result();

        $class_id = "";
        if (isset($_POST['class_id']) && !empty($_POST['class_id'])) {
            $class_id = $_POST['class_id'];
        }
        $section_id = "";
        if (isset($_POST['section_id']) && !empty($_POST['section_id'])) {
            $section_id = $_POST['section_id'];
        }
        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $this->db->select('`student_fees_deposite`.*,0 as previous_balance_amount,COALESCE(student_transport_fees.fee_override, route_pickup_point.fees) as amount,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as fee_group,"Transport Fees" as name, "Transport Fees" as `fee_type`, "" as `fee_code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no, `student_session`.`id` as `student_session_id`,0 as is_system, "" as fee_session_group_id')->from('student_transport_fees');

            $this->db->join('student_fees_deposite', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`', 'left');
            $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
            $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
            $this->db->join('route_pickup_point', 'route_pickup_point.id = student_transport_fees.route_pickup_point_id');

            $this->db->join('classes', 'classes.id= student_session.class_id');
            $this->db->join('sections', 'sections.id= student_session.section_id');
            $this->db->join('students', 'students.id=student_session.student_id');
            $this->db->where('student_session.session_id', $this->current_session);
            $this->db->where('student_session.id', $student_session_id);
            $this->db->order_by('student_fees_deposite.id', 'desc');

            if ($class_id != null) {
                if (is_array($class_id)) {
                    $this->db->where_in('student_session.class_id', $class_id);
                } else {
                    $this->db->where('student_session.class_id', $class_id);
                }
            }

            if ($section_id != null) {
                $this->db->where('student_session.section_id', $section_id);
            }

            $query1        = $this->db->get();
            $result_value1 = $query1->result();
        } else {
            $result_value1 = array();
        }
        if (empty($result_value)) {
            $result_value2 = $result_value1;
        } elseif (empty($result_value1)) {
            $result_value2 = $result_value;
        } else {
            $result_value2 = array_merge($result_value, $result_value1);
        }

        if (!empty($result_value2)) {
            foreach ($result_value2 as $result_key => $result_value) {
                $result_value->fees = array();
                $fee_session_group_id   = $result_value->fee_session_group_id;
                $student_fees_master_id = $result_value->id;
                if (empty($result_value->fee_session_group_id)) {
                    $result_value->fees[0]     = (object)array('amount_detail' => $result_value->amount_detail, 'amount' => $result_value->amount);
                } else {
                    $result_value->fees     = (object)$this->getDueFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id);
                }

                if ($result_value->is_system != 0) {                  
                    $result_value->fees->{"0"}->{'amount'} = $result_value->amount;
                }
            }
        }
        return $result_value2;
    }

    public function getStudentProcessingFees($student_session_id)
    {
        $sql    = "SELECT `student_fees_master`.*,fee_groups.name FROM `student_fees_master` INNER JOIN fee_session_groups on student_fees_master.fee_session_group_id=fee_session_groups.id INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  WHERE `student_session_id` = " . $student_session_id . " ORDER BY `student_fees_master`.`id`";
        $query  = $this->db->query($sql);
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $result_key => $result_value) {

                $fee_session_group_id   = $result_value->fee_session_group_id;
                $student_fees_master_id = $result_value->id;
                $result_value->fees     = $this->getProcessingFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id);
                if (!empty($result_value->fees)) {
                    if ($result_value->is_system != 0) {
                        $result_value->fees[0]->amount = $result_value->amount;
                    }
                }
            }
        }

        return $result;
    }
	
    public function getTransDueFeeByFeeSessionGroup($fee_session_groups_id, $student_fees_master_id)
    {
        $class_id = "";
        if (isset($_POST['class_id']) && !empty($_POST['class_id'])) {
            $class_id = $_POST['class_id'];
        }
        $section_id = "";
        if (isset($_POST['section_id']) && !empty($_POST['section_id'])) {
            $section_id = $_POST['section_id'];
        }

        $this->db->select('`student_fees_deposite`.*,0 as previous_balance_amount,route_pickup_point.fees as amount,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as fee_group,"Transport Fees" as name, "Transport Fees" as `fee_type`, "" as `fee_code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no, `student_session`.`id` as `student_session_id`,0 as is_system')->from('student_transport_fees');

        $this->db->join('student_fees_deposite', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`', 'left');
        $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
        $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_transport_fees.route_pickup_point_id');
        $this->db->join('classes', 'classes.id= student_session.class_id');
        $this->db->join('sections', 'sections.id= student_session.section_id');
        $this->db->join('students', 'students.id=student_session.student_id');

        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->order_by('student_fees_deposite.id', 'desc');

                    if ($class_id != null) {
                        if (is_array($class_id)) {
                            $this->db->where_in('student_session.class_id', $class_id);
                        } else {
                            $this->db->where('student_session.class_id', $class_id);
                        }
                    }
        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }

        $query1        = $this->db->get();
        $result_value1 = $query1->result();

        return $result_value1;
    }
   
    public function getDueFeeByFeeSessionGroup($fee_session_groups_id, $student_fees_master_id)
    {
        $sql = "SELECT student_fees_master.*, fee_groups_feetype.fine_type,fee_groups_feetype.id as `fee_groups_feetype_id`, (COALESCE(sfo.override_amount, fee_groups_feetype.amount) - IFNULL(student_fees_discounts.custom_amount, 0)) as amount, sfo.override_amount as fee_override_amount, fee_groups_feetype.amount as base_fee_amount, fee_groups_feetype.due_date,fee_groups_feetype.fine_amount,fee_groups_feetype.fee_groups_id,fee_groups.name,fee_groups_feetype.feetype_id,feetype.code,feetype.type, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id  INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id LEFT JOIN student_fees_discounts on student_fees_discounts.student_session_id = student_fees_master.student_session_id AND student_fees_discounts.fees_discount_id = fee_groups.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_fees_master.fee_session_group_id =" . $fee_session_groups_id . " and student_fees_master.id=" . $student_fees_master_id . " order by feetype.id asc";
        $query = $this->db->query($sql);
        $result_value = $query->result();
        return $result_value;
    }

    private function getDueFeesForMultipleMasters(array $master_ids)
    {
        if (empty($master_ids)) return [];
        $ids = implode(',', array_map('intval', $master_ids));
        $sql = "SELECT student_fees_master.*, student_fees_master.id as sfm_id, fee_groups_feetype.fine_type, fee_groups_feetype.id as fee_groups_feetype_id, (COALESCE(sfo.override_amount, fee_groups_feetype.amount) - IFNULL(student_fees_discounts.custom_amount, 0)) as amount, sfo.override_amount as fee_override_amount, fee_groups_feetype.amount as base_fee_amount, fee_groups_feetype.due_date, fee_groups_feetype.fine_amount, fee_groups_feetype.fee_groups_id, fee_groups.name, fee_groups_feetype.feetype_id, feetype.code, feetype.type, IFNULL(student_fees_deposite.id,0) as student_fees_deposite_id, IFNULL(student_fees_deposite.amount_detail,0) as amount_detail FROM student_fees_master INNER JOIN fee_session_groups ON fee_session_groups.id = student_fees_master.fee_session_group_id INNER JOIN fee_groups_feetype ON fee_groups_feetype.fee_session_group_id = fee_session_groups.id INNER JOIN fee_groups ON fee_groups.id = fee_groups_feetype.fee_groups_id INNER JOIN feetype ON feetype.id = fee_groups_feetype.feetype_id LEFT JOIN student_fees_deposite ON student_fees_deposite.student_fees_master_id = student_fees_master.id AND student_fees_deposite.fee_groups_feetype_id = fee_groups_feetype.id LEFT JOIN student_fees_discounts ON student_fees_discounts.student_session_id = student_fees_master.student_session_id AND student_fees_discounts.fees_discount_id = fee_groups.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_fees_master.id IN ($ids) ORDER BY student_fees_master.id, feetype.id ASC";
        $rows    = $this->db->query($sql)->result();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->sfm_id][] = $row;
        }
        return $grouped;
    }

    public function getStudentTransportFeesBatch(array $student_session_ids)
    {
        if (empty($student_session_ids)) return [];
        $ids = implode(',', array_map('intval', $student_session_ids));
        $sql = "SELECT student_transport_fees.*, transport_feemaster.month, transport_feemaster.due_date, COALESCE(student_transport_fees.fee_override, route_pickup_point.fees) AS fees, transport_feemaster.fine_amount, transport_feemaster.fine_type, transport_feemaster.fine_percentage, IFNULL(student_fees_deposite.id,0) as student_fees_deposite_id, IFNULL(student_fees_deposite.amount_detail,0) as amount_detail FROM student_transport_fees INNER JOIN transport_feemaster ON transport_feemaster.id = student_transport_fees.transport_feemaster_id LEFT JOIN student_fees_deposite ON student_fees_deposite.student_transport_fee_id = student_transport_fees.id INNER JOIN route_pickup_point ON route_pickup_point.id = student_transport_fees.route_pickup_point_id WHERE student_transport_fees.student_session_id IN ($ids) ORDER BY student_transport_fees.student_session_id, student_transport_fees.id ASC";
        $rows    = $this->db->query($sql)->result();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->student_session_id][] = $row;
        }
        return $grouped;
    }

    public function getProcessingFeeByFeeSessionGroup($fee_session_groups_id, $student_fees_master_id)
    {
        $sql = "SELECT student_fees_master.*,fee_groups_feetype.fine_type,fee_groups_feetype.id as `fee_groups_feetype_id`,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as amount,fee_groups_feetype.due_date,fee_groups_feetype.fine_amount,fee_groups_feetype.fee_groups_id,fee_groups.name,fee_groups_feetype.feetype_id,feetype.code,feetype.type, IFNULL(student_fees_processing.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_processing.amount_detail,0) as `amount_detail`,gateway_ins.unique_id FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id  INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id INNER JOIN student_fees_processing on student_fees_processing.student_fees_master_id=student_fees_master.id and student_fees_processing.fee_groups_feetype_id=fee_groups_feetype.id inner join gateway_ins on gateway_ins.id=student_fees_processing.gateway_ins_id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_fees_master.fee_session_group_id =" . $fee_session_groups_id . " and student_fees_master.id=" . $student_fees_master_id . " order by fee_groups_feetype.due_date ASC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function getProcessingTransportFees($student_session_id, $route_pickup_point_id)
    {
        $sql = "SELECT student_transport_fees.*,transport_feemaster.month,transport_feemaster.due_date ,route_pickup_point.fees,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,IFNULL(student_fees_processing.id,0) as `student_fees_processing_id`, IFNULL(student_fees_processing.amount_detail,0) as `amount_detail`,gateway_ins.unique_id
        FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id INNER JOIN student_fees_processing on student_fees_processing.student_transport_fee_id=student_transport_fees.id INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id inner join gateway_ins on gateway_ins.id=student_fees_processing.gateway_ins_id where student_transport_fees.student_session_id=" . $student_session_id . " and student_transport_fees.route_pickup_point_id=" . $route_pickup_point_id . " ORDER BY student_transport_fees.id asc";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function getDueFeesByStudent($student_session_id, $date)
    {
        $sql = "SELECT student_fees_master.*,fee_session_groups.fee_groups_id,fee_session_groups.session_id,fee_groups.name,fee_groups.is_system,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as `fee_amount`,fee_groups_feetype.id as fee_groups_feetype_id,fee_groups_feetype.fine_type,fee_groups_feetype.due_date,fee_groups_feetype.fine_percentage,fee_groups_feetype.fine_amount,IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail`,students.is_active,classes.class,sections.section,feetype.type,feetype.code FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id=student_fees_master.fee_session_group_id INNER JOIN student_session on student_session.id=student_fees_master.student_session_id INNER JOIN students on students.id=student_session.student_id inner join classes on student_session.class_id=classes.id INNER JOIN sections on sections.id=student_session.section_id  INNER JOIN fee_groups_feetype on student_fees_master.fee_session_group_id=fee_groups_feetype.fee_session_group_id inner join fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  INNER JOIN feetype on feetype.id= fee_groups_feetype.feetype_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_fees_master.student_session_id='" . $student_session_id . "' AND student_session.session_id='" . $this->current_session . "' and  fee_session_groups.session_id='" . $this->current_session . "'  and fee_groups_feetype.due_date <  '" . $date . "' ORDER BY `student_fees_master`.`id` DESC";
        $query = $this->db->query($sql);
        return $query->result();
    }

    public function getDueTransportFeeByStudent($student_session_id, $route_pickup_point_id, $date)
    {
        if ($student_session_id != NULL && $route_pickup_point_id != NULL) {

            $sql = "SELECT student_transport_fees.*,transport_feemaster.month,transport_feemaster.due_date ,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` ,route_pickup_point.fees FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_transport_fee_id=student_transport_fees.id  INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id where student_transport_fees.student_session_id=" . $student_session_id . " and student_transport_fees.route_pickup_point_id=" . $route_pickup_point_id . " and transport_feemaster.due_date < '" . $date . "' ORDER BY student_transport_fees.id asc";

            $query = $this->db->query($sql);

            return $query->result();
        }
        return false;
    }

    public function getTransportFeesByDueDate($start_date, $end_date)
    {
        $sql    = "SELECT student_transport_fees.*,route_pickup_point.fees,transport_feemaster.month,transport_feemaster.due_date ,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail`,students.id as `student_id`, students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,students.dob ,students.current_address,    students.permanent_address,students.category_id, IFNULL(categories.category, '') as `category`,   students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name, students.guardian_relation,students.guardian_phone,students.guardian_email,`classes`.`class`,students.guardian_address,students.is_active,`students`.`father_name`,`students`.`app_key`,`students`.`parent_app_key`,`students`.`gender`  FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id   LEFT JOIN student_fees_deposite on student_fees_deposite.student_transport_fee_id=student_transport_fees.id INNER JOIN student_session on student_session.id= student_transport_fees.student_session_id INNER JOIN classes on classes.id= student_session.class_id INNER JOIN sections on sections.id= student_session.section_id INNER JOIN students on students.id=student_session.student_id INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id` WHERE transport_feemaster.due_date BETWEEN " . $this->db->escape($start_date) . " and " . $this->db->escape($end_date);
        $query  = $this->db->query($sql);
        $result = $query->result();

        return $result;
    }

    public function getFeesByStudentFeeMasterAndFeetype($student_fees_master_id, $fee_groups_feetype_id)
    {
        $sql = "SELECT student_fees_master.id,student_fees_master.is_system,student_fees_master.student_session_id,student_fees_master.fee_session_group_id,student_fees_master.amount as `student_fees_master_amount`,fee_groups_feetype.id as `fee_groups_feetype_id`,students.id as student_id,students.firstname,students.middlename,students.admission_no,students.lastname,student_session.class_id,classes.class,sections.section,students.guardian_name,students.guardian_phone,students.father_name,student_session.section_id,student_session.student_id,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as amount,fee_groups_feetype.due_date,fee_groups_feetype.fine_amount,fee_groups_feetype.fine_type,fee_groups_feetype.fee_groups_id,fee_groups.name,fee_groups_feetype.feetype_id,feetype.code,feetype.type, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id  INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id INNER JOIN student_session on student_session.id= student_fees_master.student_session_id INNER JOIN classes on classes.id= student_session.class_id INNER JOIN sections on sections.id= student_session.section_id INNER JOIN students on students.id=student_session.student_id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE  student_fees_master.id=" . $student_fees_master_id . " and fee_groups_feetype.id= " . $fee_groups_feetype_id;

        $query = $this->db->query($sql);
        return $query->row();
    }

    public function getDueFeeByFeeSessionGroupFeetype($fee_session_groups_id, $student_fees_master_id, $fee_groups_feetype_id)
    {
        $sql = "SELECT fee_groups_feetype.fine_type,student_fees_master.id,student_fees_master.is_system,student_fees_master.student_session_id,student_fees_master.fee_session_group_id,student_fees_master.amount as `student_fees_master_amount`,fee_groups_feetype.id as `fee_groups_feetype_id`,students.id as student_id,students.firstname,students.middlename,students.admission_no,students.lastname,student_session.class_id,classes.class,sections.section,students.guardian_name,students.guardian_phone,students.father_name,student_session.section_id,student_session.student_id,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as amount,fee_groups_feetype.due_date,fee_groups_feetype.fine_amount,fee_groups_feetype.fee_groups_id,fee_groups.name,fee_groups_feetype.feetype_id,feetype.code,feetype.type, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id  INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id INNER JOIN student_session on student_session.id= student_fees_master.student_session_id INNER JOIN classes on classes.id= student_session.class_id INNER JOIN sections on sections.id= student_session.section_id INNER JOIN students on students.id=student_session.student_id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_fees_master.fee_session_group_id =" . $fee_session_groups_id . " and student_fees_master.id=" . $student_fees_master_id . " and fee_groups_feetype.id= " . $fee_groups_feetype_id;

        $query = $this->db->query($sql);
        return $query->row();
    }

            public function fee_deposit_bulk($bulk_data)
            {
                $this->db->trans_start();
                $this->db->trans_strict(FALSE);
        
                foreach ($bulk_data as $fee_data) {
                    log_message('error', 'FEE_DATA_CONTENT: ' . print_r($fee_data, true));
                    if (isset($fee_data['student_session_id'])) {
                        unset($fee_data['student_session_id']);
                    }
                    if (isset($fee_data['date'])) {
                        unset($fee_data['date']);
                    }
                    log_message('debug', 'Fee data being inserted: ' . json_encode($fee_data));
                    $this->db->insert('student_fees_deposite', $fee_data);
                }
        
                $this->db->trans_complete();
        
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    return false;
                } else {
                    $this->db->trans_commit();
                    return true;
                }
            }    public function fee_deposit($data, $send_to, $fee_discounts,$date)
    {
        if ($data['fee_category'] == "fees") {
            # code...
            $this->db->where('student_fees_master_id', $data['student_fees_master_id']);
            $this->db->where('fee_groups_feetype_id', $data['fee_groups_feetype_id']);
        } elseif ($data['student_transport_fee_id'] > 0 && $data['fee_category'] == "transport") {
            $this->db->where('student_transport_fee_id', $data['student_transport_fee_id']);
        }

        unset($data['fee_category']);
        if (isset($data['date'])) {
            unset($data['date']);
        }
        $q = $this->db->get('student_fees_deposite');
        if ($q->num_rows() > 0) {
            $desc = $data['amount_detail']['description'];
            $this->db->trans_start(); // Query will be rolled back
            $row = $q->row();
            $this->db->where('id', $row->id);
            $a                               = json_decode($row->amount_detail, true);
            $inv_no                          = max(array_keys($a)) + 1;
            $data['amount_detail']['inv_no'] = $inv_no;
            $a[$inv_no]                      = $data['amount_detail'];
            $data['amount_detail']           = json_encode($a);
            $this->db->update('student_fees_deposite', $data);

            if (!empty($fee_discounts)) {
                $discount_array_bulk=[];
                foreach ($fee_discounts as $fee_discount_key => $fee_discount_value) {
                $discount_array_bulk[]=array('student_fees_deposite_id'=>$row->id,'student_fees_discount_id'=>$fee_discount_value,'date'=>$date,'invoice_id' => $row->id, 'sub_invoice_id' => $inv_no);
               }
            $this->db->insert_batch('student_applied_discounts', $discount_array_bulk);
            }

            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();

                return false;
            } else {
                $this->db->trans_commit();
                return json_encode(array('invoice_id' => $row->id, 'sub_invoice_id' => $inv_no));
            }
        } else {

            $this->db->trans_start(); // Query will be rolled back
            $data['amount_detail']['inv_no'] = 1;
            $desc                            = $data['amount_detail']['description'];
            $data['amount_detail']           = json_encode(array('1' => $data['amount_detail']));
            $this->db->insert('student_fees_deposite', $data);
      
            $inserted_id = $this->db->insert_id();
            if (!empty($fee_discounts)) {
                $discount_array_bulk=[];
             foreach ($fee_discounts as $fee_discount_key => $fee_discount_value) {
                $discount_array_bulk[]=array('student_fees_deposite_id'=>$inserted_id,'student_fees_discount_id'=>$fee_discount_value,'date'=>$date,'invoice_id' => $inserted_id, 'sub_invoice_id' => 1);
            }
            $this->db->insert_batch('student_applied_discounts', $discount_array_bulk);
            }

            $this->db->trans_complete(); # Completing transaction

            if ($this->db->trans_status() === false) {

                $this->db->trans_rollback();
                return false;
            } else {
                $this->db->trans_commit();
                return json_encode(array('invoice_id' => $inserted_id, 'sub_invoice_id' => 1));
            }
        }
    }

    public function get_discount_amount($discount_id_array){

        $discount_id_string= implode(",",$discount_id_array);
        $sql = "select fees_discounts.* from student_fees_discounts 
        left join fees_discounts on student_fees_discounts.fees_discount_id = fees_discounts.id
        where student_fees_discounts.id in ($discount_id_string)";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_feesreceived_by()
    {
        if ($this->session->has_userdata('admin')) {
            $getStaffRole     = $this->customlib->getStaffRole();
            $staffrole   =   json_decode($getStaffRole);

            $superadmin_visible = $this->customlib->superadmin_visible();
            if ($superadmin_visible == 'disabled' && $staffrole->id != 7) {
                $this->db->where("staff_roles.role_id !=", 7);
            }
        }

        $result = $this->db->select('CONCAT_WS(" ",staff.name,staff.surname) as name, staff.employee_id,staff.id')->from('staff')->join('staff_roles', 'staff.id=staff_roles.staff_id')->where('staff.is_active', '1')->get()->result_array();
        foreach ($result as $key => $value) {
            $data[$value['id']] = $value['name'] . " (" . $value['employee_id'] . ")";
        }
        return $data;
    }

    public function getFeeCollectionReport($start_date, $end_date, $feetype_id = null, $received_by = null, $group = null, $class_id = null, $section_id = null)
    {
        $this->db->select('`student_fees_deposite`.*,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,`fee_groups`.`name`, `feetype`.`type`, `feetype`.`code`,feetype.is_system,student_fees_master.student_session_id,students.admission_no')->from('student_fees_deposite');
        $this->db->join('fee_groups_feetype', 'fee_groups_feetype.id = student_fees_deposite.fee_groups_feetype_id');
        $this->db->join('fee_groups', 'fee_groups.id = fee_groups_feetype.fee_groups_id');
        $this->db->join('feetype', 'feetype.id = fee_groups_feetype.feetype_id');
        $this->db->join('student_fees_master', 'student_fees_master.id=student_fees_deposite.student_fees_master_id');
        $this->db->join('student_session', 'student_session.id= student_fees_master.student_session_id', 'left');
        $this->db->join('classes', 'classes.id= student_session.class_id');
        $this->db->join('sections', 'sections.id= student_session.section_id');
        $this->db->join('students', 'students.id=student_session.student_id');
        if ($feetype_id != null) {
            $this->db->where('fee_groups_feetype.feetype_id', $feetype_id);
        }
        $this->db->where('fee_groups_feetype.session_id', $this->current_session);
        $this->db->group_start();
        $this->db->where('student_session.is_alumni', 0);
        $this->db->or_where('student_session.is_alumni IS NULL', null, false);
        $this->db->group_end();
        if ($class_id != null) {
            if (is_array($class_id)) {
                $this->db->where_in('student_session.class_id', $class_id);
            } else {
                $this->db->where('student_session.class_id', $class_id);
            }
        }

        if ($section_id != null) {
            $this->db->where('student_session.section_id', $section_id);
        }

        $query        = $this->db->get();
        $result_value = $query->result();
        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $this->db->select('`student_fees_deposite`.*,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as name, "Transport Fees" as `type`, "" as `code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no')->from('student_fees_deposite');

            $this->db->join('student_transport_fees', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`');
            $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
            $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
            $this->db->join('classes', 'classes.id= student_session.class_id');
            $this->db->join('sections', 'sections.id= student_session.section_id');
            $this->db->join('students', 'students.id=student_session.student_id');
            $this->db->group_start();
            $this->db->where('student_session.is_alumni', 0);
            $this->db->or_where('student_session.is_alumni IS NULL', null, false);
            $this->db->group_end();
            if ($class_id != null) {
                if (is_array($class_id)) {
                    $this->db->where_in('student_session.class_id', $class_id);
                } else {
                    $this->db->where('student_session.class_id', $class_id);
                }
            }

            if ($section_id != null) {
                $this->db->where('student_session.section_id', $section_id);
            }

            $query1        = $this->db->get();
            $result_value1 = $query1->result();
        } else {
            $result_value1 = array();
        }
        if ($feetype_id != null) {
            if ($feetype_id != 'transport_fees') {
                $result_value1 = array();
            }
        }
        if (empty($result_value)) {
            $result_value2 = $result_value1;
        } elseif (empty($result_value1)) {
            $result_value2 = $result_value;
        } else {
            $result_value2 = array_merge($result_value, $result_value1);
        }


        $return_array = array();
        if (!empty($result_value2)) {
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            foreach ($result_value2 as $key => $value) {
                if ($received_by != null) {
                    $return = $this->findObjectByCollectId($value, $st_date, $ed_date, $received_by);
                } else {
                    $return = $this->findObjectById($value, $st_date, $ed_date);
                }

                if (!empty($return)) {
                    foreach ($return as $r_key => $r_value) {

                        $a['id']                     = $value->id;
                        $a['student_fees_master_id'] = $value->student_fees_master_id;
                        $a['fee_groups_feetype_id']  = $value->fee_groups_feetype_id;
                        $a['admission_no']           = $value->admission_no;
                        $a['firstname']              = $value->firstname;
                        $a['middlename']             = $value->middlename;
                        $a['lastname']               = $value->lastname;
                        $a['class_id']               = $value->class_id;
                        $a['class']                  = $value->class;
                        $a['section']                = $value->section;
                        $a['section_id']             = $value->section_id;
                        $a['student_id']             = $value->student_id;
                        $a['name']                   = $value->name;
                        $a['type']                   = $value->type;
                        $a['code']                   = $value->code;
                        $a['student_session_id']     = $value->student_session_id;
                        $a['is_system']              = $value->is_system;
                        $a['amount']                 = $r_value->amount;
                        $a['date']                   = $r_value->date;
                        $a['amount_discount']        = isset($r_value->amount_discount) ? $r_value->amount_discount : 0;
                        $a['amount_fine']            = isset($r_value->amount_fine) ? $r_value->amount_fine : 0;
                        $a['description']            = $r_value->description;
                        $a['payment_mode']           = $r_value->payment_mode;
                        $a['inv_no']                 = $r_value->inv_no;
                        $a['received_by']            = $r_value->received_by;
                        if (isset($r_value->received_by)) {

                            $a['received_by']     = $r_value->received_by;
                            $a['received_byname'] = $this->staff_model->get_StaffNameById($r_value->received_by);
                        } else {

                            $a['received_by']     = '';
                            $a['received_byname'] = array('name' => '', 'employee_id' => '', 'id' => '');
                        }

                        $return_array[] = $a;
                    }
                }
            }
        }

        return $return_array;
    }

    public function getFeeBetweenDate($start_date, $end_date)
    {
        $this->db->select('`student_fees_deposite`.*,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,`fee_groups`.`name`, `feetype`.`type`, `feetype`.`code`,student_fees_master.student_session_id')->from('student_fees_deposite');
        $this->db->join('fee_groups_feetype', 'fee_groups_feetype.id = student_fees_deposite.fee_groups_feetype_id');
        $this->db->join('fee_groups', 'fee_groups.id = fee_groups_feetype.fee_groups_id');
        $this->db->join('feetype', 'feetype.id = fee_groups_feetype.feetype_id');
        $this->db->join('student_fees_master', 'student_fees_master.id=student_fees_deposite.student_fees_master_id');
        $this->db->join('student_session', 'student_session.id= student_fees_master.student_session_id');
        $this->db->join('classes', 'classes.id= student_session.class_id');
        $this->db->join('sections', 'sections.id= student_session.section_id');
        $this->db->join('students', 'students.id=student_session.student_id');
        $this->db->order_by('student_fees_deposite.id');
        $query        = $this->db->get();
        $result_value = $query->result();
        $return_array = array();
        if (!empty($result_value)) {
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            foreach ($result_value as $key => $value) {
                $return = $this->findObjectById($value, $st_date, $ed_date);
                if (!empty($return)) {
                    foreach ($return as $r_key => $r_value) {
                        $a['id']                     = $value->id;
                        $a['student_fees_master_id'] = $value->student_fees_master_id;
                        $a['fee_groups_feetype_id']  = $value->fee_groups_feetype_id;
                        $a['firstname']              = $value->firstname;
                        $a['lastname']               = $value->lastname;
                        $a['class_id']               = $value->class_id;
                        $a['class']                  = $value->class;
                        $a['section']                = $value->section;
                        $a['section_id']             = $value->section_id;
                        $a['student_id']             = $value->student_id;
                        $a['name']                   = $value->name;
                        $a['type']                   = $value->type;
                        $a['code']                   = $value->code;
                        $a['student_session_id']     = $value->student_session_id;
                        $a['amount']                 = $r_value->amount;
                        $a['date']                   = $r_value->date;
                        $a['amount_discount']        = $r_value->amount_discount;
                        $a['amount_fine']            = $r_value->amount_fine;
                        $a['description']            = $r_value->description;
                        $a['payment_mode']           = $r_value->payment_mode;
                        $a['inv_no']                 = $r_value->inv_no;

                        $return_array[] = $a;
                    }
                }
            }
        }

        return $return_array;
    }

    public function getDepositAmountBetweenDate($start_date, $end_date)
    {
        $this->db->select('`student_fees_deposite`.*')->from('student_fees_deposite')->join('fee_groups_feetype', 'fee_groups_feetype.id=student_fees_deposite.fee_groups_feetype_id')->where('fee_groups_feetype.session_id', $this->current_session);
        $this->db->order_by('student_fees_deposite.id');
        $query        = $this->db->get();
        $result_value = $query->result();
        $return_array = array();
        if (!empty($result_value)) {
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
            foreach ($result_value as $key => $value) {
                $return = $this->findObjectById($value, $st_date, $ed_date);

                if (!empty($return)) {
                    foreach ($return as $r_key => $r_value) {
                        $a                    = array();
                        $a['amount']          = $r_value->amount;
                        $a['date']            = $r_value->date;
                        $a['amount_discount'] = isset($r_value->amount_discount) ? $r_value->amount_discount : 0;
                        $a['amount_fine']     = isset($r_value->amount_fine) ? $r_value->amount_fine : 0;
                        $a['description']     = $r_value->description;
                        $a['payment_mode']    = $r_value->payment_mode;
                        $a['inv_no']          = $r_value->inv_no;
                        $return_array[]       = $a;
                    }
                }
            }
        }

        return $return_array;
    }

    public function findObjectAmount($array, $st_date, $ed_date)
    {
        $ar     = json_decode($array->amount_detail);
        $array  = array();
        $amount = 0;
        for ($i = $st_date; $i <= $ed_date; $i += 86400) {
            $find = date('Y-m-d', $i);
            foreach ($ar as $row_key => $row_value) {
                if ($row_value->date == $find) {
                    $array[] = $row_value;
                }
            }
        }
        return $array;
    }

    public function findObjectById($array, $st_date, $ed_date)
    {
        $ar = json_decode($array->amount_detail);

        $array = array();
        for ($i = $st_date; $i <= $ed_date; $i += 86400) {
            $find = date('Y-m-d', $i);
            foreach ($ar as $row_key => $row_value) {
                if ($row_value->date == $find) {
                    $array[] = $row_value;
                }
            }
        }

        return $array;
    }

    public function findObjectByCollectId($array, $st_date, $ed_date, $receivedBy)
    {
        $ar = json_decode($array->amount_detail);

        $array = array();
        for ($i = $st_date; $i <= $ed_date; $i += 86400) {
            $find = date('Y-m-d', $i);
            foreach ($ar as $row_key => $row_value) {
                if (isset($row_value->received_by)) {
                    if ($row_value->date == $find && $row_value->received_by == $receivedBy) {
                        $array[] = $row_value;
                    }
                }
            }
        }

        return $array;
    }	

    public function getTransportFeeByID($trans_fee_id) 
    {
        $sql = "SELECT student_transport_fees.*,route_pickup_point.fees,transport_feemaster.month,transport_feemaster.due_date ,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,students.id as student_id,students.firstname,students.middlename,students.admission_no,students.lastname,student_session.class_id,classes.class,sections.section,students.guardian_name,students.guardian_phone,students.father_name,student_session.section_id,student_session.student_id, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id   LEFT JOIN student_fees_deposite on student_fees_deposite.student_transport_fee_id=student_transport_fees.id INNER JOIN student_session on student_session.id= student_transport_fees.student_session_id INNER JOIN classes on classes.id= student_session.class_id INNER JOIN sections on sections.id= student_session.section_id INNER JOIN students on students.id=student_session.student_id INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id  WHERE student_transport_fees.id=" . $trans_fee_id;
        $query = $this->db->query($sql);
        return $query->row();
    }
	
    public function getTransportFeeByInvoice($invoice_id, $sub_invoice_id)
    {
        $this->db->select('`student_fees_deposite`.*,students.id as std_id,students.firstname,students.middlename,students.lastname,students.admission_no,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,pickup_point.name as `pickup_name`,transport_route.route_title,transport_route_id,pickup_point_id,transport_feemaster.month,transport_feemaster.due_date,transport_feemaster.fine_amount,transport_feemaster.fine_type,route_pickup_point.fees')->from('student_fees_deposite');
        $this->db->join('student_transport_fees', 'student_transport_fees.id = student_fees_deposite.student_transport_fee_id');
        $this->db->join('transport_feemaster', 'transport_feemaster.id = student_transport_fees.transport_feemaster_id');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_transport_fees.route_pickup_point_id');
        $this->db->join('pickup_point', 'route_pickup_point.pickup_point_id = pickup_point.id');
        $this->db->join('transport_route', 'route_pickup_point.transport_route_id = transport_route.id');
        $this->db->join('student_session', 'student_session.id= student_transport_fees.student_session_id');
        $this->db->join('classes', 'classes.id= student_session.class_id');
        $this->db->join('sections', 'sections.id= student_session.section_id');
        $this->db->join('students', 'students.id=student_session.student_id');
        $this->db->where('student_fees_deposite.id', $invoice_id);
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            $result = $q->row();
            $res    = json_decode($result->amount_detail);
            $a      = (array) $res;

            foreach ($a as $key => $value) {
                if ($key == $sub_invoice_id) {

                    return $result;
                }
            }
        }

        return false;
    }

    public function getFeeByInvoice($invoice_id, $sub_invoice_id)
    {
        $type = $this->db->select('`student_fees_deposite`.*')->from('`student_fees_deposite`')->where('id', $invoice_id)->get()->row_array();
        if (empty($type['student_transport_fee_id'])) {
            $this->db->select('`student_fees_deposite`.*,students.id as std_id,students.firstname,students.middlename,students.lastname,students.admission_no,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,`fee_groups`.`name`, `feetype`.`type`, `feetype`.`code`,feetype.is_system,student_fees_master.student_session_id,student_session.session_id,student_fees_master.amount as `student_fees_master_amount`,fee_groups_feetype.amount')->from('student_fees_deposite');
            $this->db->join('fee_groups_feetype', 'fee_groups_feetype.id = student_fees_deposite.fee_groups_feetype_id');
            $this->db->join('fee_groups', 'fee_groups.id = fee_groups_feetype.fee_groups_id');
            $this->db->join('feetype', 'feetype.id = fee_groups_feetype.feetype_id');
            $this->db->join('student_fees_master', 'student_fees_master.id=student_fees_deposite.student_fees_master_id');
            $this->db->join('student_session', 'student_session.id= student_fees_master.student_session_id');
            $this->db->join('classes', 'classes.id= student_session.class_id');
            $this->db->join('sections', 'sections.id= student_session.section_id');
            $this->db->join('students', 'students.id=student_session.student_id');
            $this->db->where('student_fees_deposite.id', $invoice_id);
            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                $result = $q->row();
                $res    = json_decode($result->amount_detail);
                $a      = (array) $res;

                foreach ($a as $key => $value) {
                    if ($key == $sub_invoice_id) {

                        return $result;
                    }
                }
            }
        } else {
            $module = $this->module_model->getPermissionByModulename('transport');
            if ($module['is_active']) {
                $this->db->select('`student_fees_deposite`.*,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as name, "Transport Fees" as `type`, transport_feemaster.month as `code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no,student_session.session_id')->from('student_fees_deposite');

                $this->db->join('student_transport_fees', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`');
                $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
                $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
                $this->db->join('classes', 'classes.id= student_session.class_id');
                $this->db->join('sections', 'sections.id= student_session.section_id');
                $this->db->join('students', 'students.id=student_session.student_id');
                $this->db->order_by('student_fees_deposite.id', 'desc');
                $this->db->where('student_fees_deposite.id', $invoice_id);
                $q        = $this->db->get();
                if ($q->num_rows() > 0) {
                    $result = $q->row();
                    $res    = json_decode($result->amount_detail);
                    $a      = (array) $res;

                    foreach ($a as $key => $value) {
                        if ($key == $sub_invoice_id) {

                            return $result;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function studentDeposit($data)
    {
        $sql = "SELECT fee_groups_feetype.fine_type,fee_groups.is_system,student_fees_master.amount as `student_fees_master_amount`, fee_groups.name as `fee_group_name`,feetype.code as `fee_type_code`,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as amount,fee_groups_feetype.fine_percentage,fee_groups_feetype.fine_amount,fee_groups_feetype.due_date,IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` from student_fees_master
               INNER JOIN fee_session_groups on fee_session_groups.id=student_fees_master.fee_session_group_id
              INNER JOIN fee_groups_feetype on fee_groups_feetype.fee_groups_id=fee_session_groups.fee_groups_id
              INNER JOIN fee_groups on fee_groups_feetype.fee_groups_id=fee_groups.id
              INNER JOIN feetype on fee_groups_feetype.feetype_id=feetype.id
         LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id
         LEFT JOIN student_fees_discounts on student_fees_discounts.student_session_id = student_fees_master.student_session_id AND student_fees_discounts.fees_discount_id = fee_groups.id
         LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id
         WHERE student_fees_master.id =" . $data['student_fees_master_id'] . " and fee_groups_feetype.id =" . $data['fee_groups_feetype_id'];
        $query = $this->db->query($sql);

        return $query->row();
    }
	
    public function studentTransportDeposit($student_transport_fee_id)
    {
        $sql = "SELECT student_transport_fees.*,transport_feemaster.month,transport_feemaster.due_date ,COALESCE(student_transport_fees.fee_override, route_pickup_point.fees) AS fees,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id  LEFT JOIN student_fees_deposite on student_fees_deposite.student_transport_fee_id=student_transport_fees.id INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id  where student_transport_fees.id=" . $this->db->escape($student_transport_fee_id);
        $query = $this->db->query($sql);
        return $query->row();
    }    

    public function getPreviousStudentFees($student_session_id)
    {
        $sql    = "SELECT `student_fees_master`.*,fee_groups.name FROM `student_fees_master` INNER JOIN fee_session_groups on student_fees_master.fee_session_group_id=fee_session_groups.id INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  WHERE `student_session_id` = " . $student_session_id . " ORDER BY `student_fees_master`.`id`";
        $query  = $this->db->query($sql);
        $result = $query->result();
        if (!empty($result)) {
            foreach ($result as $result_key => $result_value) {
                $fee_session_group_id   = $result_value->fee_session_group_id;
                $student_fees_master_id = $result_value->id;
                $result_value->fees     = $this->getDueFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id);

                if ($result_value->is_system != 0) {
                    $result_value->fees[0]->amount = $result_value->amount;
                }
            }
        }

        return $result;
    }

    public function fee_deposit_collections($data)
    {
        if (!empty($data)) {
            $collected_fees = array();
            foreach ($data as $d_key => $d_value) {
                if ($d_value['fee_category'] == "transport") {
                    $this->db->where('student_transport_fee_id', $data[$d_key]['student_transport_fee_id']);
                    $data[$d_key]['student_fees_master_id'] = NULL;
                    $data[$d_key]['fee_groups_feetype_id'] = NULL;
                } elseif ($d_value['fee_category'] == "fees") {
                    $data[$d_key]['student_transport_fee_id'] = NULL;
                    $this->db->where('student_fees_master_id', $data[$d_key]['student_fees_master_id']);
                    $this->db->where('fee_groups_feetype_id', $data[$d_key]['fee_groups_feetype_id']);
                }

                unset($data[$d_key]['fee_category']);

                $q = $this->db->get('student_fees_deposite');
                if ($q->num_rows() > 0) {
                    $desc = $data[$d_key]['amount_detail']['description'];
                    $row  = $q->row();
                    $this->db->where('id', $row->id);
                    $a                                       = json_decode($row->amount_detail, true);
                    $inv_no                                  = max(array_keys($a)) + 1;
                    $data[$d_key]['amount_detail']['inv_no'] = $inv_no;
                    $a[$inv_no]                              = $data[$d_key]['amount_detail'];
                    $data[$d_key]['amount_detail']           = json_encode($a);
                    $this->db->update('student_fees_deposite', $data[$d_key]);                   

                    $collected_fees[] = array(
                        'invoice_id' => $row->id,
                        'sub_invoice_id' => $inv_no,
                        'fee_groups_feetype_id' => $data[$d_key]['fee_groups_feetype_id'],
                        'student_transport_fee_id' => $data[$d_key]['student_transport_fee_id'],
                        'fee_category' => $d_value['fee_category']
                    );
                } else {

                    $data[$d_key]['amount_detail']['inv_no'] = 1;
                    $desc                                    = $data[$d_key]['amount_detail']['description'];
                    $data[$d_key]['amount_detail']           = json_encode(array('1' => $data[$d_key]['amount_detail']));
                    $this->db->insert('student_fees_deposite', $data[$d_key]);
                    $inserted_id      = $this->db->insert_id();

                    $collected_fees[] = array(
                        'invoice_id' => $inserted_id,
                        'sub_invoice_id' => 1,
                        'fee_groups_feetype_id' => $data[$d_key]['fee_groups_feetype_id'],
                        'student_transport_fee_id' => $data[$d_key]['student_transport_fee_id'],
                        'fee_category' => $d_value['fee_category']
                    );
                }
            }
            return $collected_fees;
        }
    }

    public function findOnlineObjectById($array, $st_date, $ed_date)
    {
        $ar    = json_decode($array->amount_detail);
        $gateway_modes = array('online', 'billdesk', 'card', 'razorpay', 'paytm', 'payumoney', 'ccavenue', 'instamojo', 'stripe', 'paypal', 'payu', 'upi', 'bank_transfer', 'neft', 'imps', 'rtgs', 'net_banking', 'netbanking');
        $array = array();
        for ($i = $st_date; $i <= $ed_date; $i += 86400) {
            $find = date('Y-m-d', $i);
            foreach ($ar as $row_key => $row_value) {
                if ($row_value->date == $find) {
                    $pm = strtolower(trim($row_value->payment_mode ?? ''));
                    if (in_array($pm, $gateway_modes, true)) {
                        $array[] = $row_value;
                    }
                }
            }
        }
        return $array;
    }

    public function getOnlineFeeCollectionReport($start_date, $end_date)
    {
        $this->db->select('`student_fees_deposite`.*,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,`fee_groups`.`name`, `feetype`.`type`, `feetype`.`code`,feetype.is_system,student_fees_master.student_session_id,students.admission_no')->from('student_fees_deposite');
        $this->db->join('fee_groups_feetype', 'fee_groups_feetype.id = student_fees_deposite.fee_groups_feetype_id');
        $this->db->join('fee_groups', 'fee_groups.id = fee_groups_feetype.fee_groups_id');
        $this->db->join('feetype', 'feetype.id = fee_groups_feetype.feetype_id');
        $this->db->join('student_fees_master', 'student_fees_master.id=student_fees_deposite.student_fees_master_id');
        $this->db->join('student_session', 'student_session.id= student_fees_master.student_session_id');
        $this->db->join('classes', 'classes.id= student_session.class_id');
        $this->db->join('sections', 'sections.id= student_session.section_id');
        $this->db->join('students', 'students.id=student_session.student_id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->group_start();
        $this->db->where('student_session.is_alumni', 0);
        $this->db->or_where('student_session.is_alumni IS NULL', null, false);
        $this->db->group_end();
        $this->db->order_by('student_fees_deposite.id');

        $query        = $this->db->get();
        $result_value = $query->result();
        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $this->db->select('`student_fees_deposite`.*,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as name, "Transport Fees" as `type`, "" as `code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no')->from('student_fees_deposite');

            $this->db->join('student_transport_fees', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`');
            $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
            $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
            $this->db->join('classes', 'classes.id= student_session.class_id');
            $this->db->join('sections', 'sections.id= student_session.section_id');
            $this->db->join('students', 'students.id=student_session.student_id');
            $this->db->where('student_session.session_id', $this->current_session);
            $this->db->group_start();
            $this->db->where('student_session.is_alumni', 0);
            $this->db->or_where('student_session.is_alumni IS NULL', null, false);
            $this->db->group_end();
            $this->db->order_by('student_fees_deposite.id', 'desc');

            $query1        = $this->db->get();
            $result_value1 = $query1->result();
        } else {
            $result_value1 = array();
        }
        if (empty($result_value)) {
            $result_value2 = $result_value1;
        } elseif (empty($result_value1)) {
            $result_value2 = $result_value;
        } else {
            $result_value2 = array_merge($result_value, $result_value1);
        }
        $return_array = array();
        if (!empty($result_value2)) {
            $st_date = strtotime($start_date);
            $ed_date = strtotime($end_date);
			 
            foreach ($result_value2 as $key => $value) {
                $return = $this->findOnlineObjectById($value, $st_date, $ed_date);
				
                if (!empty($return)) {

                    foreach ($return as $r_key => $r_value) {
                        $a['id']                     = $value->id;
                        $a['student_fees_master_id'] = $value->student_fees_master_id;
                        $a['fee_groups_feetype_id']  = $value->fee_groups_feetype_id;
                        $a['firstname']              = $value->firstname;
                        $a['middlename']             = $value->middlename;
                        $a['lastname']               = $value->lastname;
                        $a['class_id']               = $value->class_id;
                        $a['class']                  = $value->class;
                        $a['section']                = $value->section;
                        $a['section_id']             = $value->section_id;
                        $a['student_id']             = $value->student_id;
                        $a['name']                   = $value->name;
                        $a['type']                   = $value->type;
                        $a['code']                   = $value->code;
                        $a['student_session_id']     = $value->student_session_id;
                        $a['admission_no']           = $value->admission_no;
                        $a['amount']                 = $r_value->amount;
                        $a['date']                   = $r_value->date;
                        $a['amount_discount']        = $r_value->amount_discount;
                        $a['amount_fine']            = $r_value->amount_fine;
                        $a['description']            = $r_value->description;
                        $a['payment_mode']           = $r_value->payment_mode;
                        $a['payment_description']    = $r_value->description;						
                        $a['inv_no']                 = $r_value->inv_no;
                        $a['received_by']            = $r_value->received_by;
                        $a['is_system']              = $value->is_system;
                        $a['received_byname']        = $this->staff_model->get_StaffNameById($r_value->received_by);
                        $return_array[]              = $a;
                    }
                }
            }
        }

        return $return_array;
    }

    public function getFeesAwaiting($start_date, $end_date)
    {
        // Show all unpaid fees with due_date up to and including end_date
        $sql = "SELECT student_fees_master.*,fee_session_groups.fee_groups_id,fee_session_groups.session_id,fee_groups.name,fee_groups.is_system,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as `fee_amount`,fee_groups_feetype.id as fee_groups_feetype_id,student_fees_deposite.amount_detail,students.firstname,students.middlename,students.is_active, fee_groups_feetype.due_date, feetype.code FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id=student_fees_master.fee_session_group_id INNER JOIN student_session on student_session.id=student_fees_master.student_session_id INNER JOIN students on students.id=student_session.student_id INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id INNER JOIN fee_groups_feetype on fee_groups.id=fee_groups_feetype.fee_groups_id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_session.session_id='" . $this->current_session . "' and  fee_session_groups.session_id='" . $this->current_session . "' and fee_groups_feetype.due_date <= '" . $end_date . "' and students.is_active='yes' and student_fees_deposite.id IS NULL order by fee_groups_feetype.due_date asc";
        $query  = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    public function getCurrentSessionStudentFees()
    {
        $sql = "SELECT student_fees_master.*,fee_session_groups.fee_groups_id,fee_session_groups.session_id,fee_groups.name,fee_groups.is_system,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as `fee_amount`,fee_groups_feetype.id as fee_groups_feetype_id,student_fees_deposite.id as `student_fees_deposite_id`,student_fees_deposite.amount_detail,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.father_name,students.image, students.mobileno, students.email ,students.state ,   students.city , students.pincode ,students.is_active,classes.class,sections.section FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id=student_fees_master.fee_session_group_id INNER JOIN student_session on student_session.id=student_fees_master.student_session_id INNER JOIN students on students.id=student_session.student_id inner join classes on student_session.class_id=classes.id INNER JOIN sections on sections.id=student_session.section_id inner join fee_groups on fee_groups.id=fee_session_groups.fee_groups_id INNER JOIN fee_groups_feetype on fee_groups.id=fee_groups_feetype.fee_groups_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_session.session_id='" . $this->current_session . "' and  fee_session_groups.session_id='" . $this->current_session . "' AND (student_session.is_alumni = 0 OR student_session.is_alumni IS NULL)";

        $query  = $this->db->query($sql);
        $result_value = $query->result();
        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $this->db->select('`student_fees_deposite`.*,student_fees_deposite.id as `student_fees_deposite_id`,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as name, "Transport Fees" as `type`, "" as `code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no')->from('student_fees_deposite');
            $this->db->join('student_transport_fees', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`');
            $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
            $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
            $this->db->join('classes', 'classes.id= student_session.class_id');
            $this->db->join('sections', 'sections.id= student_session.section_id');
            $this->db->join('students', 'students.id=student_session.student_id');
            $this->db->where('student_session.session_id', $this->current_session);
            $this->db->group_start();
            $this->db->where('student_session.is_alumni', 0);
            $this->db->or_where('student_session.is_alumni IS NULL', null, false);
            $this->db->group_end();
            $this->db->order_by('student_fees_deposite.id', 'desc');

            $query1        = $this->db->get();
            $result_value1 = $query1->result();
        } else {
            $result_value1 = array();
        }
        if (empty($result_value)) {
            $result_value2 = $result_value1;
        } elseif (empty($result_value1)) {
            $result_value2 = $result_value;
        } else {
            $result_value2 = array_merge($result_value, $result_value1);
        }

        return $result_value2;
    }

    public function getFeesDepositeByIdArray($id_array = array())
    {
        $id_implode = $imp = "'" . implode("','", $id_array) . "'";
      
        $sql = "SELECT student_fees_master.*,fee_session_groups.fee_groups_id,fee_session_groups.session_id,fee_groups.name,fee_groups.is_system,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as `fee_amount`,fee_groups_feetype.id as fee_groups_feetype_id,student_fees_deposite.id as `student_fees_deposite_id`,student_fees_deposite.amount_detail,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.father_name,students.image, students.mobileno, students.email ,students.state ,   students.city , students.pincode ,students.is_active,classes.class,sections.section FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id=student_fees_master.fee_session_group_id INNER JOIN student_session on student_session.id=student_fees_master.student_session_id INNER JOIN students on students.id=student_session.student_id inner join classes on student_session.class_id=classes.id INNER JOIN sections on sections.id=student_session.section_id inner join fee_groups on fee_groups.id=fee_session_groups.fee_groups_id INNER JOIN fee_groups_feetype on fee_groups.id=fee_groups_feetype.fee_groups_id  JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_session.session_id='" . $this->current_session . "' and  fee_session_groups.session_id='" . $this->current_session . "' AND (student_session.is_alumni = 0 OR student_session.is_alumni IS NULL) and student_fees_deposite.id in (" . $id_implode . ")";

        $query  = $this->db->query($sql);
        $result_value = $query->result();
        $module = $this->module_model->getPermissionByModulename('transport');
        if ($module['is_active']) {
            $this->db->select('`student_fees_deposite`.*,student_fees_deposite.id as `student_fees_deposite_id`,students.firstname,students.middlename,students.lastname,student_session.class_id,classes.class,sections.section,student_session.section_id,student_session.student_id,"Transport Fees" as name, "Transport Fees" as `type`, "" as `code`,0 as is_system,student_transport_fees.student_session_id,students.admission_no,students.father_name')->from('student_fees_deposite');

            $this->db->join('student_transport_fees', 'student_transport_fees.id = `student_fees_deposite`.`student_transport_fee_id`');
            $this->db->join('transport_feemaster', '`student_transport_fees`.`transport_feemaster_id` = `transport_feemaster`.`id`');
            $this->db->join('student_session', 'student_session.id= `student_transport_fees`.`student_session_id`', 'INNER');
            $this->db->join('classes', 'classes.id= student_session.class_id');
            $this->db->join('sections', 'sections.id= student_session.section_id');
            $this->db->join('students', 'students.id=student_session.student_id');

            $this->db->where('student_session.session_id', $this->current_session);
            $this->db->group_start();
            $this->db->where('student_session.is_alumni', 0);
            $this->db->or_where('student_session.is_alumni IS NULL', null, false);
            $this->db->group_end();
            $this->db->where_in('student_fees_deposite.id', $id_array);
			$query1        = $this->db->get();
            $result_value1 = $query1->result();
        } else {
            $result_value1 = array();
        }
        if (empty($result_value)) {
            $result_value2 = $result_value1;
        } elseif (empty($result_value1)) {
            $result_value2 = $result_value;
        } else {
            $result_value2 = array_merge($result_value, $result_value1);
        }
        return $result_value2;
    }

    public function getStudentDueFeeTypesByDate($date, $class_id = null, $section_id = null, $department_id = null)
    {
        $where_condition = array();
        if ($class_id != null) {
            $where_condition[] = "student_session.class_id=" . $this->db->escape($class_id);
        }
        if ($section_id != null) {
            $where_condition[] = "student_session.section_id=" . $this->db->escape($section_id);
        }
        if ($department_id != null) {
            $where_condition[] = "classes.department_id=" . $this->db->escape($department_id);
        }
        $where_condition_string = "";
        if (!empty($where_condition)) {
            $where_condition_string = " AND " . implode(" AND ", $where_condition);
        }

        $sql = "SELECT student_fees_master.*,fee_session_groups.fee_groups_id,fee_session_groups.session_id,fee_groups.name,fee_groups.is_system,COALESCE(sfo.override_amount, fee_groups_feetype.amount) as `fee_amount`,fee_groups_feetype.id as fee_groups_feetype_id,student_fees_deposite.amount_detail,students.admission_no , students.roll_no,students.admission_date,students.firstname,students.middlename,  students.lastname,students.father_name,students.image, students.mobileno, students.email ,students.state ,   students.city , students.pincode ,students.is_active,classes.class,classes.id as class_id,sections.section,sections.id as section_id,students.id as student_id FROM `student_fees_master` INNER JOIN fee_session_groups on fee_session_groups.id=student_fees_master.fee_session_group_id INNER JOIN student_session on student_session.id=student_fees_master.student_session_id INNER JOIN students on students.id=student_session.student_id inner join classes on student_session.class_id=classes.id INNER JOIN sections on sections.id=student_session.section_id inner join fee_groups on fee_groups.id=fee_session_groups.fee_groups_id INNER JOIN fee_groups_feetype on fee_groups.id=fee_groups_feetype.fee_groups_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = student_fees_master.student_session_id AND sfo.fee_groups_feetype_id = fee_groups_feetype.id WHERE student_session.session_id='" . $this->current_session . "' and  fee_session_groups.session_id='" . $this->current_session . "' and fee_groups_feetype.due_date <=" . $this->db->escape($date) . " AND (student_session.is_alumni = 0 OR student_session.is_alumni IS NULL)" . $where_condition_string;

        $query  = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
	
	public function getStudentTransportFees($student_session_id, $route_pickup_point_id)
    {
		$date               = date('Y-m-d');
        if ($student_session_id != NULL && $route_pickup_point_id != NULL) {
            $sql = "SELECT student_transport_fees.*,transport_feemaster.month,transport_feemaster.due_date ,COALESCE(student_transport_fees.fee_override, route_pickup_point.fees) AS fees,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_transport_fee_id=student_transport_fees.id INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id  where student_transport_fees.student_session_id=" . $student_session_id . " and student_transport_fees.route_pickup_point_id=" . $route_pickup_point_id . " and transport_feemaster.due_date <=" . $this->db->escape($date) . " ORDER BY student_transport_fees.id asc";
            $query = $this->db->query($sql);
            return $query->result();
        }
        return false;
    }
	
	public function getStudentTransportFeesByStudentSessionId($student_session_id, $route_pickup_point_id)
    {		 
        if ($student_session_id != NULL && $route_pickup_point_id != NULL) {
            $sql = "SELECT student_transport_fees.*,transport_feemaster.month,transport_feemaster.due_date ,COALESCE(student_transport_fees.fee_override, route_pickup_point.fees) AS fees,transport_feemaster.fine_amount, transport_feemaster.fine_type,transport_feemaster.fine_percentage,IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_transport_fees` INNER JOIN transport_feemaster on transport_feemaster.id =student_transport_fees.transport_feemaster_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_transport_fee_id=student_transport_fees.id INNER JOIN route_pickup_point on route_pickup_point.id = student_transport_fees.route_pickup_point_id  where student_transport_fees.student_session_id=" . $student_session_id . " and student_transport_fees.route_pickup_point_id=" . $route_pickup_point_id . " ORDER BY student_transport_fees.id asc";
            $query = $this->db->query($sql);
            return $query->result();
        }
        return false;
    }

    public function studentDepositByFeeGroupFeeTypeArray($student_session_id, $fee_type_array)
    {
        $fee_groups_feetype_ids = implode(', ', $fee_type_array);
        $sql = "SELECT fee_groups_feetype.*,student_fees_master.student_session_id,student_fees_master.amount as `previous_amount`,student_fees_master.is_system,student_fees_master.id as student_fees_master_id,feetype.code,feetype.type, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`,student_fees_deposite.amount_detail,fee_groups.name as `fee_group_name` FROM `fee_groups_feetype` INNER join student_fees_master on student_fees_master.fee_session_group_id=fee_groups_feetype.fee_session_group_id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id WHERE fee_groups_feetype.id in (" . $fee_groups_feetype_ids . ") and student_fees_master.student_session_id=" . $this->db->escape($student_session_id) . "  order by fee_groups_feetype.due_date asc";
        $query                  = $this->db->query($sql);
        return $query->result();
    }

    public function fees_reminder($date, $fee_groups_feetype, $student_session)
    {
        $sql = "SELECT fee_groups_feetype.*,student_fees_master.id as `student_fees_master_id`,student_fees_master.student_session_id,IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`,student_fees_deposite.amount_detail,feetype.code,feetype.type FROM `fee_groups_feetype` INNER join student_fees_master on student_fees_master.fee_session_group_id=fee_groups_feetype.fee_session_group_id LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id =fee_groups_feetype.id INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id WHERE session_id=" . $this->current_session . " and due_date < '" . $date . "' and fee_groups_feetype.id not in " . $fee_groups_feetype . " AND student_fees_master.student_session_id in " . $student_session . " order by student_fees_master.student_session_id desc";

        $query  = $this->db->query($sql);
        return $query->result();
    }

      //**** fees master ****//
    public function unassignfees($fee_session_groups_id, $fee_groups_id, $student_session_id){

        $this->db->where('id', $fee_groups_id);        
        $this->db->delete('fee_groups');

        $this->db->where('id', $fee_session_groups_id);        
        $this->db->delete('fee_session_groups');        
        
        $this->db->where('student_session_id', $student_session_id);        
        $this->db->delete('feetype');   
        
    }
	
    // fees master fees collectiion
    public function get_cumulative_fine_amount($fee_groups_feetype_id)
    {
        $query  = $this->db->query("SELECT cumulative_fine.*,fee_groups_feetype.fine_per_day FROM `cumulative_fine` 
            left join fee_groups_feetype on fee_groups_feetype.id=cumulative_fine.fee_groups_feetype_id
            WHERE `fee_groups_feetype_id`=$fee_groups_feetype_id");
        $result = $query->result();
        return $result;
    }

    public function add_bulk_fee_deposit($bulk_data, $fee_discounts = null)
    {
        $this->db->trans_start();
        $fees_return = array();
        $date = date("Y-m-d");

        foreach ($bulk_data as $fee_data) {
            $student_fees_master_id = null;
            $fee_groups_feetype_id = null;
            $student_transport_fee_id = null;
            $fee_category = $fee_data['fee_category'];

            if ($fee_category == "fees") {
                $student_fees_master_id = $fee_data['student_fees_master_id'];
                $fee_groups_feetype_id = $fee_data['fee_groups_feetype_id'];
                $this->db->where('student_fees_master_id', $student_fees_master_id);
                $this->db->where('fee_groups_feetype_id', $fee_groups_feetype_id);
            } elseif ($fee_category == "transport") {
                $student_transport_fee_id = $fee_data['student_transport_fee_id'];
                $this->db->where('student_transport_fee_id', $student_transport_fee_id);
            }

            $q = $this->db->get('student_fees_deposite');

            $deposit_data = array(
                'amount_detail' => $fee_data['amount_detail'],
                'student_fees_master_id' => $student_fees_master_id,
                'fee_groups_feetype_id' => $fee_groups_feetype_id,
                'student_transport_fee_id' => $student_transport_fee_id
            );

            if ($q->num_rows() > 0) {
                $row = $q->row();
                $this->db->where('id', $row->id);
                $a = json_decode($row->amount_detail, true);
                $inv_no = max(array_keys($a)) + 1;
                $deposit_data['amount_detail']['inv_no'] = $inv_no;
                $a[$inv_no] = $deposit_data['amount_detail'];
                $deposit_data['amount_detail'] = json_encode($a);
                $this->db->update('student_fees_deposite', $deposit_data);

                if (!empty($fee_discounts)) {
                    $discount_array_bulk = [];
                    foreach ($fee_discounts as $fee_discount_value) {
                        $discount_array_bulk[] = array(
                            'student_fees_deposite_id' => $row->id,
                            'student_fees_discount_id' => $fee_discount_value,
                            'date' => $date,
                            'invoice_id' => $row->id,
                            'sub_invoice_id' => $inv_no
                        );
                    }
                    $this->db->insert_batch('student_applied_discounts', $discount_array_bulk);
                }

                $fees_return[] = array(
                    'invoice_id' => $row->id,
                    'sub_invoice_id' => $inv_no,
                    'fee_groups_feetype_id' => $fee_groups_feetype_id,
                    'student_transport_fee_id' => $student_transport_fee_id,
                    'fee_category' => $fee_category
                );
            } else {
                $deposit_data['amount_detail']['inv_no'] = 1;
                $deposit_data['amount_detail'] = json_encode(array('1' => $deposit_data['amount_detail']));
                $this->db->insert('student_fees_deposite', $deposit_data);
                $inserted_id = $this->db->insert_id();

                if (!empty($fee_discounts)) {
                    $discount_array_bulk = [];
                    foreach ($fee_discounts as $fee_discount_value) {
                        $discount_array_bulk[] = array(
                            'student_fees_deposite_id' => $inserted_id,
                            'student_fees_discount_id' => $fee_discount_value,
                            'date' => $date,
                            'invoice_id' => $inserted_id,
                            'sub_invoice_id' => 1
                        );
                    }
                    $this->db->insert_batch('student_applied_discounts', $discount_array_bulk);
                }

                $fees_return[] = array(
                    'invoice_id' => $inserted_id,
                    'sub_invoice_id' => 1,
                    'fee_groups_feetype_id' => $fee_groups_feetype_id,
                    'student_transport_fee_id' => $student_transport_fee_id,
                    'fee_category' => $fee_category
                );
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return $fees_return;
        }
    }

    public function getOutstandingFeesByStudentSessionId($student_session_id)
    {
        $fees = [];

        $sql = "SELECT
                    sfm.id as student_fees_master_id,
                    fgf.id as fee_groups_feetype_id,
                    fg.name as fee_group_name,
                    ft.type as fee_type_name,
                    COALESCE(sfo.override_amount, fgf.amount) as fee_amount,
                    fgf.due_date
                FROM student_fees_master sfm
                JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
                JOIN fee_groups fg ON fg.id = fgf.fee_groups_id
                JOIN feetype ft ON ft.id = fgf.feetype_id
                LEFT JOIN student_fee_overrides sfo ON sfo.student_session_id = sfm.student_session_id
                    AND sfo.fee_groups_feetype_id = fgf.id
                WHERE sfm.student_session_id = " . $this->db->escape($student_session_id);

        $query = $this->db->query($sql);
        $all_fees = $query->result();

        // Fetch all deposits for the student in one go
        $deposit_sql = "SELECT student_fees_master_id, fee_groups_feetype_id, amount_detail
                        FROM student_fees_deposite sfd
                        JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                        WHERE sfm.student_session_id = " . $this->db->escape($student_session_id);
        $deposit_query = $this->db->query($deposit_sql);
        $all_deposits = $deposit_query->result_array();

        $paid_amounts = [];
        foreach ($all_deposits as $deposit) {
            $master_id = $deposit['student_fees_master_id'];
            $feetype_id = $deposit['fee_groups_feetype_id'];
            $amount_detail = json_decode($deposit['amount_detail'], true);

            if (!isset($paid_amounts[$master_id][$feetype_id])) {
                $paid_amounts[$master_id][$feetype_id] = 0;
            }

            foreach ($amount_detail as $detail) {
                $paid_amounts[$master_id][$feetype_id] += $detail['amount'];
            }
        }

        foreach ($all_fees as $fee) {
            $amount_paid = isset($paid_amounts[$fee->student_fees_master_id][$fee->fee_groups_feetype_id]) ? $paid_amounts[$fee->student_fees_master_id][$fee->fee_groups_feetype_id] : 0;

            $outstanding_amount = $fee->fee_amount - $amount_paid;

            if ($outstanding_amount > 0) {
                $fee->outstanding_amount = $outstanding_amount;

                // Categorize fees for easier processing
                if (strpos(strtolower($fee->fee_group_name), 'carry forwarded') !== false) {
                    $fees['carry_forwarded'][] = $fee;
                } elseif (strpos(strtolower($fee->fee_type_name), 'tuition') !== false) {
                    $fees['tuition'][] = $fee;
                } elseif (strpos(strtolower($fee->fee_type_name), 'other') !== false) {
                    $fees['other'][] = $fee;
                } elseif (strpos(strtolower($fee->fee_type_name), 'hostel') !== false) {
                    $fees['hostel'][] = $fee;
                } else {
                    $fees['other_fees'][] = $fee;
                }
            }
        }

        return $fees;
    }

    public function checkDuplicateBillNumber($old_bill_number)
    {
        $this->db->select('id');
        $this->db->from('student_fees_deposite');
        $this->db->where('old_bill_number', $old_bill_number);
        $query = $this->db->get();
        return ($query->num_rows() > 0);
    }

    public function getFeeByFeeType($student_session_id, $feetype_id)
    {
        $this->db->select('sfm.id, fgf.id as fee_groups_feetype_id');
        $this->db->from('student_fees_master as sfm');
        $this->db->join('fee_session_groups as fsg', 'fsg.id = sfm.fee_session_group_id');
        $this->db->join('fee_groups_feetype as fgf', 'fgf.fee_session_group_id = fsg.id');
        $this->db->where('sfm.student_session_id', $student_session_id);
        $this->db->where('fgf.feetype_id', $feetype_id);
        $query = $this->db->get();
        return $query->row();
    }

    public function get_or_create_advance_fee_ids($student_session_id)
    {
        $this->load->model('feegroup_model');
        $this->load->model('feetype_model');
        $this->load->model('feesessiongroup_model');

        $fee_group_name = "Advance Payments";
        $fee_type_name = "Advance Payments";

        // 1. Get or create Fee Group
        $fee_group = $this->feegroup_model->checkGroupExistsByName($fee_group_name);
        if (!$fee_group) {
            $this->db->insert('fee_groups', ['name' => $fee_group_name, 'description' => 'Advance Payments']);
            $fee_group_id = $this->db->insert_id();
        } else {
            $fee_group_id = $fee_group->id;
        }

        // 2. Get or create Fee Type
        $fee_type = $this->feetype_model->checkFeetypeByName($fee_type_name);
        if (!$fee_type) {
            $this->db->insert('feetype', ['type' => $fee_type_name, 'code' => 'ADV']);
            $fee_type_id = $this->db->insert_id();
        } else {
            $fee_type_id = $fee_type->id;
        }

        // 3. Get or create Fee Session Group
        $fee_session_group_id = $this->feesessiongroup_model->group_exists($fee_group_id);

        // 4. Get or create Student Fee Master
        $student_fees_master = $this->db->where(['student_session_id' => $student_session_id, 'fee_session_group_id' => $fee_session_group_id])->get('student_fees_master')->row();
        if (!$student_fees_master) {
            $this->db->insert('student_fees_master', ['student_session_id' => $student_session_id, 'fee_session_group_id' => $fee_session_group_id]);
            $student_fees_master_id = $this->db->insert_id();
        } else {
            $student_fees_master_id = $student_fees_master->id;
        }

        // 5. Get or create Fee Groups Feetype
        $fee_groups_feetype = $this->db->where(['fee_session_group_id' => $fee_session_group_id, 'feetype_id' => $fee_type_id])->get('fee_groups_feetype')->row();
        if (!$fee_groups_feetype) {
            $this->db->insert('fee_groups_feetype', ['fee_session_group_id' => $fee_session_group_id, 'feetype_id' => $fee_type_id, 'fee_groups_id' => $fee_group_id, 'amount' => 0, 'due_date' => null]);
            $fee_groups_feetype_id = $this->db->insert_id();
        } else {
            $fee_groups_feetype_id = $fee_groups_feetype->id;
        }

        return (object)['student_fees_master_id' => $student_fees_master_id, 'fee_groups_feetype_id' => $fee_groups_feetype_id];
    }

    public function get_advance_balance($student_session_id)
    {
        $this->load->model('feetype_model');
        $fee_type = $this->feetype_model->checkFeetypeByName("Advance Payments");
        if (!$fee_type) {
            return ['paid_advance_balance' => 0, 'discount_advance_balance' => 0];
        }
        $feetype_id = $fee_type->id;

        $this->db->select('student_fees_deposite.amount_detail');
        $this->db->from('student_fees_deposite');
        $this->db->join('student_fees_master', 'student_fees_master.id = student_fees_deposite.student_fees_master_id');
        $this->db->join('fee_groups_feetype', 'fee_groups_feetype.id = student_fees_deposite.fee_groups_feetype_id');
        $this->db->where('student_fees_master.student_session_id', $student_session_id);
        $this->db->where('fee_groups_feetype.feetype_id', $feetype_id);
        $query = $this->db->get();
        $result = $query->result();

        $paid_advance_balance = 0;
        $discount_advance_balance = 0;
        if (!empty($result)) {
            foreach ($result as $row) {
                $amount_detail = json_decode($row->amount_detail);
                foreach ($amount_detail as $amount) {
                    $paid_advance_balance += $amount->amount;
                    // Add amount_discount if it exists, otherwise 0
                    $discount_advance_balance += isset($amount->amount_discount) ? $amount->amount_discount : 0;
                }
            }
        }

        return [
            'paid_advance_balance' => $paid_advance_balance,
            'discount_advance_balance' => $discount_advance_balance
        ];
    }

    public function reallocate_payments($student_session_id, $old_fee_session_group_id, $new_fee_session_group_id = null)
    {
        $this->db->trans_start();

        $old_deposits_query = $this->db->select('sfd.id, sfd.amount_detail, fgf.feetype_id')
            ->from('student_fees_deposite sfd')
            ->join('student_fees_master sfm', 'sfm.id = sfd.student_fees_master_id')
            ->join('fee_groups_feetype fgf', 'fgf.id = sfd.fee_groups_feetype_id')
            ->where('sfm.student_session_id', $student_session_id)
            ->where('sfm.fee_session_group_id', $old_fee_session_group_id)
            ->get();
        $old_deposits = $old_deposits_query->result();

        if (empty($old_deposits)) {
            $this->db->trans_complete();
            return true; 
        }

        $paid_per_feetype = [];
        foreach ($old_deposits as $deposit) {
            if (!isset($paid_per_feetype[$deposit->feetype_id])) {
                $paid_per_feetype[$deposit->feetype_id] = 0;
            }
            $amount_details = json_decode($deposit->amount_detail, true);
            if (is_array($amount_details)) {
                foreach ($amount_details as $payment) {
                    $paid_per_feetype[$deposit->feetype_id] += $payment['amount'];
                }
            }
        }

        $new_fee_structure_map = [];
        $new_student_fees_master_id = null;
        if ($new_fee_session_group_id) {
            $new_sfm_q = $this->db->select('id')->from('student_fees_master')
                ->where('student_session_id', $student_session_id)
                ->where('fee_session_group_id', $new_fee_session_group_id)
                ->get();
            if ($new_sfm_q->num_rows() > 0) {
                $new_student_fees_master_id = $new_sfm_q->row()->id;
                $new_fee_structure_q = $this->db->select('id, feetype_id, amount')->from('fee_groups_feetype')
                    ->where('fee_session_group_id', $new_fee_session_group_id)
                    ->get();
                foreach ($new_fee_structure_q->result() as $item) {
                    $new_fee_structure_map[$item->feetype_id] = ['fgf_id' => $item->id, 'amount' => $item->amount];
                }
            }
        }

        $advance_fee_ids = $this->get_or_create_advance_fee_ids($student_session_id);

        foreach ($paid_per_feetype as $feetype_id => $total_paid) {
            if ($total_paid <= 0) continue;

            $match_found = $new_student_fees_master_id && isset($new_fee_structure_map[$feetype_id]);

            if ($match_found) {
                $new_fee_info = $new_fee_structure_map[$feetype_id];
                $amount_due_in_new = $new_fee_info['amount'];
                $amount_to_reallocate = min($total_paid, $amount_due_in_new);
                $excess_amount = $total_paid - $amount_to_reallocate;

                if ($amount_to_reallocate > 0) {
                    $deposit_data = [
                        'fee_category' => 'fees',
                        'student_fees_master_id' => $new_student_fees_master_id,
                        'fee_groups_feetype_id' => $new_fee_info['fgf_id'],
                        'amount_detail' => ['amount' => $amount_to_reallocate, 'amount_discount' => 0, 'amount_fine' => 0, 'date' => date('Y-m-d'), 'description' => 'Reallocated from previous fee group', 'collected_by' => 'System', 'payment_mode' => 'Transferred']
                    ];
                    if (!$this->fee_deposit($deposit_data, null, [], date('Y-m-d'))) {
                         $this->db->trans_rollback(); return false;
                    }
                }

                if ($excess_amount > 0) {
                    $deposit_data_adv = [
                        'fee_category' => 'fees',
                        'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                        'fee_groups_feetype_id' => $advance_fee_ids->fee_groups_feetype_id,
                        'amount_detail' => ['amount' => $excess_amount, 'amount_discount' => 0, 'amount_fine' => 0, 'date' => date('Y-m-d'), 'description' => 'Excess amount from fee group change', 'collected_by' => 'System', 'payment_mode' => 'Transferred']
                    ];
                    if (!$this->fee_deposit($deposit_data_adv, null, [], date('Y-m-d'))) {
                        $this->db->trans_rollback(); return false;
                    }
                }
            } else {
                $deposit_data_adv = [
                    'fee_category' => 'fees',
                    'student_fees_master_id' => $advance_fee_ids->student_fees_master_id,
                    'fee_groups_feetype_id' => $advance_fee_ids->fee_groups_feetype_id,
                    'amount_detail' => ['amount' => $total_paid, 'amount_discount' => 0, 'amount_fine' => 0, 'date' => date('Y-m-d'), 'description' => 'Reallocated from previous fee group (fee type not found in new group)', 'collected_by' => 'System', 'payment_mode' => 'Transferred']
                ];
                if (!$this->fee_deposit($deposit_data_adv, null, [], date('Y-m-d'))) {
                    $this->db->trans_rollback(); return false;
                }
            }
        }

        foreach ($old_deposits as $deposit) {
            $this->db->where('id', $deposit->id)->delete('student_fees_deposite');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    public function searchStudentsByFeeGroups($feegroup_ids, $class_id, $section_id)
    {
        $this->db->select("students.id as student_id, students.firstname, students.middlename, students.lastname, students.admission_no, students.roll_no, students.father_name, students.mobileno, students.rte, students.image, students.category_id, student_session.id as student_session_id, student_session.class_id, classes.class, sections.section, categories.category, sfm.id as student_fees_master_id, sfm.fee_session_group_id");
        $this->db->from("students");
        $this->db->join("student_session", "students.id = student_session.student_id");
        $this->db->join("classes", "student_session.class_id = classes.id");
        $this->db->join("sections", "student_session.section_id = sections.id");
        $this->db->join("categories", "students.category_id = categories.id", "left");

        $fee_session_group_ids = array();
        if(!empty($feegroup_ids)){
            foreach($feegroup_ids as $group_feetype){
                $parts = explode("-", $group_feetype);
                $fee_session_group_ids[] = $parts[0];
            }
        }
        $unique_fee_session_group_ids = array_unique($fee_session_group_ids);

        $join_condition = "sfm.student_session_id = student_session.id";
        if (!empty($unique_fee_session_group_ids)) {
            $in_clause = implode(",", array_map("intval", $unique_fee_session_group_ids));
            $join_condition .= " AND sfm.fee_session_group_id IN (" . $in_clause . ")";
        }

        $this->db->join("student_fees_master as sfm", $join_condition, "inner");

        $this->db->where("student_session.session_id", $this->current_session);
        $this->db->where("students.is_active", "yes");
        $this->db->where("students.disable_at IS NULL", null, false);
        $this->db->group_start();
        $this->db->where("student_session.is_alumni", 0);
        $this->db->or_where("student_session.is_alumni IS NULL", null, false);
        $this->db->group_end();

        if ($class_id != null) {
            $this->db->where("student_session.class_id", $class_id);
        }
        if ($section_id != null) {
            $this->db->where("student_session.section_id", $section_id);
        }

        $this->db->order_by("students.id");

        $query = $this->db->get();
        $result = $query->result_array();

        $student_fees = array();
        if (!empty($result)) {
            foreach ($result as $result_key => $result_value) {
                $fee_session_group_id = $result_value["fee_session_group_id"];
                $student_fees_master_id = $result_value["student_fees_master_id"];
                $raw_fees = array();
                if ($fee_session_group_id != null) {
                    $raw_fees = $this->getDueFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id);
                }

                $processed_fees = [];
                foreach($raw_fees as $fee_value){
                    $amount_deposite = 0;
                    $amount_discount = 0;
                    $amount_fine = 0;

                    if ($fee_value->amount_detail) {
                        $amount_detail = json_decode($fee_value->amount_detail);
                        if(is_object($amount_detail)){
                            foreach ($amount_detail as $detail) {
                                $amount_deposite += $detail->amount;
                                $amount_discount += $detail->amount_discount;
                                $amount_fine += $detail->amount_fine;
                            }
                        }
                    }
                    
                    $processed_fee = array(
                        "amount" => $fee_value->amount,
                        "amount_deposite" => $amount_deposite,
                        "amount_discount" => $amount_discount,
                        "amount_fine" => $amount_fine,
                        "fee_group" => $fee_value->name,
                        "fee_type" => $fee_value->type,
                        "fee_code" => $fee_value->code,
                        "is_system" => $fee_value->is_system,
                    );
                    $processed_fees[] = $processed_fee;
                }

                if (!array_key_exists($result_value["student_session_id"], $student_fees)) {
                    $student_fees[$result_value["student_session_id"]] = array(
                        "student_session_id" => $result_value["student_session_id"],
                        "firstname" => $result_value["firstname"],
                        "student_id" => $result_value["student_id"],
                        "middlename" => $result_value["middlename"],
                        "lastname" => $result_value["lastname"],
                        "class_id" => $result_value["class_id"],
                        "class" => $result_value["class"],
                        "section" => $result_value["section"],
                        "father_name" => $result_value["father_name"],
                        "admission_no" => $result_value["admission_no"],
                        "mobileno" => $result_value["mobileno"],
                        "roll_no" => $result_value["roll_no"],
                        "category_id" => $result_value["category_id"],
                        "category" => $result_value["category"],
                        "rte" => $result_value["rte"],
                        "image" => $result_value["image"],
                        "student_discount_fee" => $this->feediscount_model->getStudentFeesDiscount($result_value["student_session_id"]),
                        "fees" => []
                    );
                }
                $student_fees[$result_value["student_session_id"]]["fees"] = array_merge($student_fees[$result_value["student_session_id"]]["fees"], $processed_fees);
            }
        }
        return $student_fees;
    }

}
