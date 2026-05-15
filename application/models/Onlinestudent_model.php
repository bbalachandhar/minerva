<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Onlinestudent_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function add($data)
    {
        // Safety net: ensure applicant_password is always set for applicant-portal logins
        if (empty($data['applicant_password']) && !empty($data['reference_no'])) {
            $data['applicant_password'] = md5($data['reference_no'] . '@ApplicantPortal' . date('Y'));
        }
        $this->db->insert('online_admissions', $data);
        return $this->db->insert_id();
    }

    public function edit($data)
    {
        $this->db->where('id', $data['id']);
        $this->db->update('online_admissions', $data);
    }

    public function get($id = null, $carray = null)
    {
        $this->db->select('online_admissions.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type,online_admissions.hostel_room_id,class_sections.id as class_section_id,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,online_admissions.id,online_admissions.admission_no, online_admissions.roll_no,online_admissions.admission_date,online_admissions.firstname,online_admissions.middlename, online_admissions.lastname,online_admissions.image,online_admissions.mobileno,online_admissions.email,online_admissions.state,   online_admissions.city,online_admissions.pincode,online_admissions.note,online_admissions.religion,online_admissions.cast, school_houses.house_name,online_admissions.dob,online_admissions.current_address,online_admissions.previous_school,
            online_admissions.guardian_is,
            online_admissions.permanent_address,IFNULL(online_admissions.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,online_admissions.adhar_no,online_admissions.samagra_id,online_admissions.bank_account_no,online_admissions.bank_name, online_admissions.ifsc_code,online_admissions.guardian_name,online_admissions.father_pic,online_admissions.height ,online_admissions.weight,online_admissions.measurement_date,online_admissions.mother_pic,online_admissions.guardian_pic, online_admissions.guardian_relation,online_admissions.guardian_phone,online_admissions.guardian_address,online_admissions.is_enroll ,online_admissions.created_at,online_admissions.document ,online_admissions.updated_at,online_admissions.father_name,online_admissions.father_phone,online_admissions.blood_group,online_admissions.school_house_id,online_admissions.father_occupation,online_admissions.mother_name,online_admissions.mother_phone,online_admissions.mother_occupation,online_admissions.guardian_occupation,online_admissions.gender,online_admissions.guardian_is,online_admissions.rte,online_admissions.guardian_email,online_admissions.paid_status,online_admissions.form_status,online_admissions.reference_no,online_admissions.class_section_id,online_admissions.ug_course_id,online_admissions.admission_course_id,online_admissions.course_level,online_admissions.admission_type,online_admissions.quota_type,online_admissions.course_fee_total,
            online_admissions.total_maths,online_admissions.maths_marks,online_admissions.maths_perc,online_admissions.total_physics,online_admissions.physics_marks,online_admissions.physics_perc,online_admissions.total_chemistry,online_admissions.chemistry_marks,online_admissions.chemistry_perc,online_admissions.average_marks,online_admissions.cutoff_marks,online_admissions.school_name_x,online_admissions.passing_year_x,online_admissions.tenth_marks_percentage')->from('online_admissions');

        $this->db->join('class_sections', 'class_sections.id = online_admissions.class_section_id', 'left');
        $this->db->join('classes', 'class_sections.class_id = classes.id', 'left');
        $this->db->join('sections', 'sections.id = class_sections.section_id', 'left');
        $this->db->join('hostel_rooms', 'hostel_rooms.id = online_admissions.hostel_room_id', 'left');
        $this->db->join('hostel', 'hostel.id = hostel_rooms.hostel_id', 'left');
        $this->db->join('room_types', 'room_types.id = hostel_rooms.room_type_id', 'left');
        $this->db->join('categories', 'online_admissions.category_id = categories.id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = online_admissions.vehroute_id', 'left');
        $this->db->join('transport_route', 'vehicle_routes.route_id = transport_route.id', 'left');
        $this->db->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left');
        $this->db->join('school_houses', 'school_houses.id = online_admissions.school_house_id', 'left');

        if ($carray != null) {
            $this->db->where_in('classes.id', $carray);
        }

        if ($id != null) {
            $this->db->where('online_admissions.id', $id);
        } else {

            $this->db->order_by('online_admissions.id', 'desc');
        }
        $query = $this->db->get();
        if ($query === false) {
            $db_error = $this->db->error();
            log_message('error', "Onlinestudent_model::get failed SQL: " . $this->db->last_query());
            log_message('error', "Onlinestudent_model::get error: " . $db_error['message']);
            // return empty result to avoid fatal
            if ($id != null) {
                return array();
            } else {
                return array();
            }
        }
        if ($id != null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function getstudentlist($carray = null, $id = null, $quota_type_filter = null, $paid_status_filter = null, $submitted_by_filter = null, $submit_date_from = null, $submit_date_to = null, $last_payment_date = null, $course_id_filter = null, $course_level_filter = null, $admission_type_filter = null, $cutoff_from = null, $cutoff_to = null, $community_filter = null)
    {
        $class_section_array=$this->customlib->get_myClassSection();        

        if ($id != null) {
            $this->datatables->where('online_admissions.id', $id);
        } else {
            $this->datatables->orderable('online_admissions.id', 'desc');
        }

        if (!empty($quota_type_filter)) {
            if (is_array($quota_type_filter) && count($quota_type_filter) > 1) {
                $this->datatables->where_in('online_admissions.quota_type', $quota_type_filter);
            } else {
                $val = is_array($quota_type_filter) ? $quota_type_filter[0] : $quota_type_filter;
                $this->datatables->where('online_admissions.quota_type', $val);
            }
        }
        if ($submitted_by_filter === 'student') {
            $this->datatables->where('(online_admissions.referred_by_employee_id IS NULL OR online_admissions.referred_by_employee_id = 0)', null, false);
        } elseif ($submitted_by_filter === 'staff') {
            $this->datatables->where('online_admissions.referred_by_employee_id IS NOT NULL', null, false);
            $this->datatables->where('online_admissions.referred_by_employee_id !=', 0);
        }
        if (!empty($course_id_filter)) {
            $this->datatables->where('COALESCE(online_admissions.admission_course_id, online_admissions.ug_course_id) = ' . intval($course_id_filter), null, false);
        }
        if (!empty($course_level_filter)) {
            $this->datatables->where('online_admissions.course_level', $course_level_filter);
        }
        if (!empty($admission_type_filter)) {
            $this->datatables->where('online_admissions.admission_type', $admission_type_filter);
        }
        if (!empty($paid_status_filter) && $paid_status_filter !== false) {
            $paid_status_list = is_array($paid_status_filter) ? $paid_status_filter : [$paid_status_filter];
            // Filter by Course Fee Status column
            $app_fee_subquery = "(SELECT COUNT(*) "
                . "FROM incidental_fee_collections ifc_app "
                . "INNER JOIN incidental_fee_types ift_app ON ift_app.id = ifc_app.incidental_fee_type_id "
                . "WHERE REPLACE(ifc_app.application_ref_no, ' ', '') = REPLACE(online_admissions.reference_no, ' ', '') "
                . "AND ifc_app.application_ref_no IS NOT NULL AND ifc_app.application_ref_no != '' "
                . "AND LOWER(ift_app.title) LIKE '%application%')";
            $paid_subquery = "(SELECT COALESCE(SUM(ifc2.amount_collected), 0) "
                . "FROM incidental_fee_collections ifc2 "
                . "LEFT JOIN incidental_fee_types ift2 ON ift2.id = ifc2.incidental_fee_type_id "
                . "WHERE REPLACE(ifc2.application_ref_no, ' ', '') = REPLACE(online_admissions.reference_no, ' ', '') "
                . "AND ifc2.application_ref_no IS NOT NULL AND ifc2.application_ref_no != '' "
                . "AND (LOWER(ift2.title) LIKE '%tuition%' OR LOWER(ift2.title) LIKE '%tution%' OR LOWER(ift2.title) LIKE '%other fee%'))";
            $course_fee_expr = "COALESCE(online_admissions.course_fee_total, IF(online_admissions.quota_type = 'management', online_admission_courses.mgt_fee, online_admission_courses.govt_fee))";
            $conditions = [];
            foreach ($paid_status_list as $psf) {
                if ($psf === 'applied') {
                    $conditions[] = "(($app_fee_subquery > 0 OR online_admissions.paid_status = 1) AND $paid_subquery <= 0)";
                } elseif ($psf === '0') {
                    $conditions[] = "($app_fee_subquery = 0 AND online_admissions.paid_status != 1 AND $paid_subquery <= 0)";
                } elseif ($psf === '2') {
                    $conditions[] = "($paid_subquery > 0 AND ($course_fee_expr <= 0 OR $paid_subquery < $course_fee_expr))";
                } elseif ($psf === '1') {
                    $conditions[] = "($course_fee_expr > 0 AND $paid_subquery >= $course_fee_expr)";
                }
            }
            if (!empty($conditions)) {
                $this->datatables->where('(' . implode(' OR ', $conditions) . ')', null, false);
            }
        }

        // Submission date range filter
        if (!empty($submit_date_from)) {
            $this->datatables->where('COALESCE(online_admissions.submit_date, DATE(online_admissions.created_at)) >=', $submit_date_from);
        }
        if (!empty($submit_date_to)) {
            $this->datatables->where('COALESCE(online_admissions.submit_date, DATE(online_admissions.created_at)) <=', $submit_date_to);
        }

        // Last payment received date filter — match against either gateway payment or incidental collection
        if (!empty($last_payment_date)) {
            $lpd = $this->db->escape($last_payment_date);
            $this->datatables->where(
                "(EXISTS (SELECT 1 FROM online_admission_payment _lp"
                . " WHERE _lp.online_admission_id = online_admissions.id AND DATE(_lp.date) = $lpd)"
                . " OR EXISTS (SELECT 1 FROM incidental_fee_collections _lfc"
                . " WHERE REPLACE(_lfc.application_ref_no,' ','') = REPLACE(online_admissions.reference_no,' ','')"
                . " AND _lfc.application_ref_no IS NOT NULL AND _lfc.application_ref_no != ''"
                . " AND DATE(_lfc.date_collected) = $lpd))",
                null, false
            );
        }
        if (!empty($community_filter)) {
            $this->datatables->where('online_admissions.cast', $community_filter);
        }
        $this->datatables
            ->select('online_admissions.vehroute_id,vehicle_routes.route_id,vehicle_routes.vehicle_id,transport_route.route_title,vehicles.vehicle_no,hostel_rooms.room_no,vehicles.driver_name,vehicles.driver_contact,hostel.id as `hostel_id`,hostel.hostel_name,room_types.id as `room_type_id`,room_types.room_type ,online_admissions.hostel_room_id,class_sections.id as class_section_id,classes.id AS `class_id`,classes.class,sections.id AS `section_id`,sections.section,online_admissions.id,online_admissions.admission_no, online_admissions.roll_no,online_admissions.admission_date,online_admissions.firstname, online_admissions.lastname,online_admissions.image,    online_admissions.mobileno,online_admissions.email,online_admissions.state,online_admissions.city , online_admissions.pincode , online_admissions.note, online_admissions.religion,online_admissions.cast, school_houses.house_name,online_admissions.dob ,online_admissions.current_address, online_admissions.previous_school,
            online_admissions.guardian_is,
            online_admissions.permanent_address,IFNULL(online_admissions.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,online_admissions.adhar_no,online_admissions.samagra_id,online_admissions.bank_account_no,online_admissions.bank_name, online_admissions.ifsc_code , online_admissions.guardian_name,online_admissions.father_pic,online_admissions.height ,online_admissions.weight,online_admissions.measurement_date, online_admissions.mother_pic,online_admissions.guardian_pic, online_admissions.guardian_relation,online_admissions.guardian_phone,online_admissions.guardian_address,online_admissions.is_enroll ,online_admissions.created_at,online_admissions.document ,online_admissions.updated_at,online_admissions.father_name,online_admissions.father_phone,online_admissions.blood_group,online_admissions.school_house_id,online_admissions.father_occupation,online_admissions.mother_name,online_admissions.mother_phone,online_admissions.mother_occupation,online_admissions.guardian_occupation,online_admissions.gender,online_admissions.guardian_is,online_admissions.rte,online_admissions.guardian_email,online_admissions.reference_no,online_admissions.paid_status,online_admissions.form_status,online_admissions.submit_date,online_admissions.middlename,online_admissions.admission_course_id,online_admissions.course_level,online_admissions.admission_type,online_admissions.quota_type, online_admissions.referred_by_employee_id, COALESCE(online_admissions.course_fee_total, IF(online_admissions.quota_type = "management", online_admission_courses.mgt_fee, online_admission_courses.govt_fee)) as course_fee_total, CONCAT(staff.name, " ", staff.surname) as payment_updated_by_name, online_admissions.payment_updated_at, IFNULL(online_admission_courses.course_name, "N/A") as `course_name`, CONCAT(submitter_staff.name, " ", submitter_staff.surname) as submitted_by_name, COALESCE(online_admissions.admission_status, "active") as admission_status, online_admissions.cutoff_marks')
->orderable('online_admissions.reference_no,online_admissions.firstname,classes.class,online_admissions.father_name,online_admissions.created_at,submitter_staff.name,online_admissions.gender,online_admissions.quota_type,online_admissions.mobileno,online_admissions.form_status,online_admissions.paid_status,online_admissions.is_enroll,online_admissions.created_at,online_admissions.payment_updated_at,online_admission_courses.course_name,payment_updated_by_name," " ')
->searchable('online_admissions.reference_no,online_admissions.firstname,classes.class,online_admissions.father_name,online_admissions.dob,online_admissions.gender,online_admissions.quota_type,online_admissions.mobileno,online_admission_courses.course_name,staff.name,staff.surname,submitter_staff.name,submitter_staff.surname')
           
            ->join('class_sections','class_sections.id = online_admissions.class_section_id', 'left')
            ->join('classes','class_sections.class_id = classes.id', 'left')
            ->join('sections','sections.id = class_sections.section_id', 'left')
            ->join('hostel_rooms','hostel_rooms.id = online_admissions.hostel_room_id', 'left')
            ->join('hostel','hostel.id = hostel_rooms.hostel_id', 'left')
            ->join('room_types','room_types.id = hostel_rooms.room_type_id', 'left')
            ->join('categories','online_admissions.category_id = categories.id', 'left')
            ->join('vehicle_routes','vehicle_routes.id = online_admissions.vehroute_id', 'left')
            ->join('transport_route','vehicle_routes.route_id = transport_route.id', 'left')
            ->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left')
            ->join('school_houses', 'school_houses.id = online_admissions.school_house_id', 'left')
            ->join('staff', 'staff.id = online_admissions.payment_updated_by', 'left')
            ->join('staff submitter_staff', 'submitter_staff.id = online_admissions.referred_by_employee_id', 'left')
            ->join('online_admission_courses', 'online_admission_courses.id = COALESCE(online_admissions.admission_course_id, online_admissions.ug_course_id)', 'left');            
            if(!empty($class_section_array)){
                foreach ($class_section_array as $class_sectionkey => $class_sectionvalue) {
                    $query_string="";
                        foreach ($class_sectionvalue as $class_sectionvaluekey => $class_sectionvaluevalue) {
                            $query_string="( class_sections.class_id=".$class_sectionkey." and class_sections.section_id=".$class_sectionvaluevalue." )";
                            $this->datatables->or_where($query_string);
                        }    
                }
            }

            $this->datatables->from('online_admissions');
            $this->datatables->sort('online_admissions.id','desc');
            // Exclude cancelled admissions from this list — they appear on the Revoked Admissions page
            $this->datatables->where("COALESCE(online_admissions.admission_status, 'active') != 'cancelled'", null, false);

        if ($cutoff_from !== null && $cutoff_from !== '') {
            $this->datatables->where('online_admissions.cutoff_marks >=', (float) $cutoff_from);
        }
        if ($cutoff_to !== null && $cutoff_to !== '') {
            $this->datatables->where('online_admissions.cutoff_marks <=', (float) $cutoff_to);
        }

        return $this->datatables->generate('json');

    }

    public function checkpaymentstatus($id)
    {
        $this->db->select('paid_status,form_status');
        $this->db->from('online_admissions');
        $this->db->where('id', $id);
        $query  = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    public function get_payment_history_by_ref($ref_no_clean, $date_format = 'Y-m-d')
    {
        // --- 1. Manual/incidental payments (incidental_fee_collections) ---
        $this->db->select('ifc.id, ifc.receipt_no, ifc.amount_collected, ifc.date_collected, ifc.bill_date,
            ifc.payment_mode, ifc.txn_id, ifc.bank_name, ifc.cheque_no, ifc.notes,
            ift.title as fee_type,
            CONCAT(s.name, " ", s.surname) as received_by_name');
        $this->db->from('incidental_fee_collections ifc');
        $this->db->join('incidental_fee_types ift', 'ift.id = ifc.incidental_fee_type_id', 'left');
        $this->db->join('staff s', 's.id = ifc.received_by', 'left');
        $this->db->where("REPLACE(ifc.application_ref_no, ' ', '') = " . $this->db->escape($ref_no_clean), null, false);
        $this->db->where('ifc.application_ref_no IS NOT NULL', null, false);
        $this->db->where('ifc.application_ref_no !=', '');
        $this->db->order_by('ifc.date_collected', 'ASC');
        $incidental_rows = $this->db->get()->result_array();

        $payments = [];
        foreach ($incidental_rows as $row) {
            $date_display = !empty($row['date_collected']) ? date($date_format, strtotime($row['date_collected'])) : (!empty($row['bill_date']) ? date($date_format, strtotime($row['bill_date'])) : '');
            $payments[] = [
                'receipt_no'   => $row['receipt_no'],
                'fee_type'     => $row['fee_type'],
                'amount'       => number_format((float) $row['amount_collected'], 2),
                'amount_raw'   => (float) $row['amount_collected'],
                'date'         => $date_display,
                'payment_mode' => $row['payment_mode'],
                'txn_id'       => $row['txn_id'],
                'bank_name'    => $row['bank_name'],
                'cheque_no'    => $row['cheque_no'],
                'notes'        => $row['notes'],
                'received_by'  => $row['received_by_name'],
                'source'       => 'incidental',
            ];
        }

        // --- 2. Online/gateway payments (online_admission_payment) ---
        // Deduplicate by transaction_id so retries don't show multiple times.
        $gateway_sql = "SELECT MIN(oap.id) as id, oap.transaction_id, oap.payment_mode, oap.payment_type,
                               oap.paid_amount, MIN(oap.date) as payment_date, oap.note
                        FROM online_admission_payment oap
                        INNER JOIN online_admissions oa ON oa.id = oap.online_admission_id
                        WHERE REPLACE(oa.reference_no, ' ', '') = " . $this->db->escape($ref_no_clean) . "
                        GROUP BY oap.transaction_id, oap.payment_mode, oap.payment_type, oap.paid_amount, oap.note
                        ORDER BY payment_date ASC";
        $gateway_rows = $this->db->query($gateway_sql)->result_array();

        // Collect known incidental txn_ids to avoid double-counting if also recorded in incidental
        $known_txn_ids = array_filter(array_column($payments, 'txn_id'));

        foreach ($gateway_rows as $row) {
            $txn = $row['transaction_id'] ?? '';
            // Skip if same transaction already present from incidental_fee_collections
            if ($txn && in_array($txn, $known_txn_ids)) {
                continue;
            }
            $date_display = !empty($row['payment_date']) ? date($date_format, strtotime($row['payment_date'])) : '';
            // Derive a human label from payment_type
            $type_label = $row['payment_type'] ?? 'Online Payment';
            if (stripos($type_label, 'online_admission') !== false) {
                $type_label = 'Application Fee';
            } elseif (stripos($type_label, 'course_fee') !== false) {
                $type_label = 'Course Fee';
            }
            $payments[] = [
                'receipt_no'   => '',
                'fee_type'     => $type_label,
                'amount'       => number_format((float) $row['paid_amount'], 2),
                'amount_raw'   => (float) $row['paid_amount'],
                'date'         => $date_display,
                'payment_mode' => $row['payment_mode'],
                'txn_id'       => $txn,
                'bank_name'    => '',
                'cheque_no'    => '',
                'notes'        => $row['note'],
                'received_by'  => '',
                'source'       => 'gateway',
            ];
            if ($txn) {
                $known_txn_ids[] = $txn;
            }
        }

        // Sort merged list by date ascending
        usort($payments, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $payments;
    }

    public function get_application_fee_paid_refs($reference_nos = array())
    {
        if (empty($reference_nos)) {
            return array();
        }

        $normalized_refs = array();
        foreach ($reference_nos as $ref) {
            $ref = preg_replace('/\s+/', '', (string) $ref);
            if ($ref !== '') {
                $normalized_refs[] = $ref;
            }
        }
        $normalized_refs = array_values(array_unique($normalized_refs));

        if (empty($normalized_refs)) {
            return array();
        }

        $this->db->select('REPLACE(incidental_fee_collections.application_ref_no, " ", "") as ref_clean', false);
        $this->db->from('incidental_fee_collections');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'inner');
        $this->db->where("REPLACE(incidental_fee_collections.application_ref_no, ' ', '') IN ('" . implode("','", array_map('addslashes', $normalized_refs)) . "')", null, false);
        $this->db->where('incidental_fee_collections.application_ref_no IS NOT NULL', null, false);
        $this->db->where('incidental_fee_collections.application_ref_no !=', '');
        $this->db->where("LOWER(incidental_fee_types.title) LIKE '%application%'", null, false);
        $this->db->group_by('REPLACE(incidental_fee_collections.application_ref_no, " ", "")');

        $result = $this->db->get()->result_array();
        $paid_refs = array();
        foreach ($result as $row) {
            $paid_refs[$row['ref_clean']] = true;
        }

        return $paid_refs;
    }

    public function get_incidental_paid_amount_by_application_refs($reference_nos = array())
    {
        if (empty($reference_nos)) {
            return array();
        }

        $normalized_refs = array();
        foreach ($reference_nos as $ref) {
            $ref = preg_replace('/\s+/', '', (string) $ref);
            if ($ref !== '') {
                $normalized_refs[] = $ref;
            }
        }
        $normalized_refs = array_values(array_unique($normalized_refs));

        if (empty($normalized_refs)) {
            return array();
        }

        $this->db->select('incidental_fee_collections.application_ref_no, SUM(incidental_fee_collections.amount_collected) as paid_amount');
        $this->db->from('incidental_fee_collections');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'left');
        $this->db->where_in('REPLACE(incidental_fee_collections.application_ref_no, " ", "")', $normalized_refs, false);
        $this->db->where('incidental_fee_collections.application_ref_no IS NOT NULL', null, false);
        $this->db->where('incidental_fee_collections.application_ref_no !=', '');
        $this->db->where('(LOWER(incidental_fee_types.title) LIKE "%tuition%" OR LOWER(incidental_fee_types.title) LIKE "%tution%" OR LOWER(incidental_fee_types.title) LIKE "%other fee%")', null, false);
        $this->db->group_by('incidental_fee_collections.application_ref_no');

        $result = $this->db->get()->result_array();
        $summary = array();
        foreach ($result as $row) {
            $normalized_key = preg_replace('/\s+/', '', (string) $row['application_ref_no']);
            if (!isset($summary[$normalized_key])) {
                $summary[$normalized_key] = 0;
            }
            $summary[$normalized_key] += (float) $row['paid_amount'];
        }

        return $summary;
    }

    public function update($data,$fee_session_group_id,$transport_feemaster_id,$discount_id,$action = "save")
    {
        $record_update_status = true;
        $student_id           = "";
        $user_password        = "";
        $parent_password      = "";
        if (isset($data['id'])) {
            $this->db->trans_begin();
            $data_id          = $data['id'];
            if ($action == "enroll") {
                $class_section_id = $data['class_section_id'];
                //==========================
                $insert             = true;
                $sch_setting_detail = $this->setting_model->getSetting();

                if ($sch_setting_detail->adm_auto_insert) {
                    if ($sch_setting_detail->adm_update_status) {

                        $admission_no = $sch_setting_detail->adm_prefix . $sch_setting_detail->adm_start_from;

                        $last_student = $this->student_model->lastRecord();
                        if (empty($last_student)) {
                            $admission_no         = $sch_setting_detail->adm_prefix . $sch_setting_detail->adm_start_from;
                            $data['admission_no'] = $admission_no;
                        } else {
                            $last_admission_digit = str_replace($sch_setting_detail->adm_prefix, "", $last_student->admission_no);

                            $admission_no = $sch_setting_detail->adm_prefix . sprintf("%0" . $sch_setting_detail->adm_no_digit . "d", $last_admission_digit + 1);

                            $data['admission_no'] = $admission_no;
                        }

                    } else {
                        $admission_no         = $sch_setting_detail->adm_prefix . $sch_setting_detail->adm_start_from;
                        $data['admission_no'] = $admission_no;
                    }
                }

                $admission_no_exists = $this->student_model->check_adm_exists($data['admission_no']);
                if ($admission_no_exists) {
                    $insert               = false;
                    $record_update_status = false;
                }

                //============================
                if ($insert) {
                    $this->db->select('class_sections.*')->from('class_sections');
                    $this->db->where('class_sections.id', $data['class_section_id']);
                    $query                 = $this->db->get();
                    $classs_section_result = $query->row();
                    $route_pickup_point_id=$data['route_pickup_point_id'];
                    $vehroute_id=$data['vehroute_id'];
                    unset($data['route_pickup_point_id']);
                    unset($data['vehroute_id']);
                    unset($data['class_section_id']);
                    unset($data['id']);
                    $this->db->insert('students', $data);
                    $student_id = $this->db->insert_id();

                    //save created by staff/user id on student enroll
                    $created_by_data['id'] = $student_id;
                    $created_by_data['created_by'] = $this->session->userdata['admin']['id'];
                    $this->student_model->add($created_by_data);
                    //save created by staff/user id on student enroll

                    $data_new   = array(
                        'student_id' => $student_id,
                        'class_id'   => $classs_section_result->class_id,
                        'section_id' => $classs_section_result->section_id,
                        'session_id' => $this->current_session,
                        'route_pickup_point_id' => $route_pickup_point_id,
                        'vehroute_id'           => $vehroute_id,
                    );
                    $this->db->insert('student_session', $data_new);
                    $student_session_id = $this->db->insert_id();

                    if ($fee_session_group_id) {
                        $this->studentfeemaster_model->assign_bulk_fees($fee_session_group_id, $student_session_id, array());
                    }

                    //*** Assign Fees Discount ***//
                    if (!empty($discount_id)){
                        foreach($discount_id as $key => $value){
                            $insert_array = array(
                                'student_session_id' => $student_session_id,
                                'fees_discount_id'   => $value,
                            );
                        $this->feediscount_model->allotdiscount($insert_array);
                        }
                    }
                    //*** Assign Fees Discount ***//

                if (!empty($transport_feemaster_id)) {
                    $trns_data_insert = array();
                    foreach ($transport_feemaster_id as $transport_feemaster_key => $transport_feemaster_value) {
                        $trns_data_insert[] = array(
                            'student_session_id'     => $student_session_id,
                            'route_pickup_point_id'  => $route_pickup_point_id,
                            'transport_feemaster_id' => $transport_feemaster_value
                        );
                    }

                    $student_session_is = $this->studenttransportfee_model->add($trns_data_insert, $student_session_id, array(), $route_pickup_point_id);
                }

                    //===============Start Student ID===========
                    $user_password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);

                    $data_student_login = array(
                        'username' => $this->student_login_prefix . $student_id,
                        'password' => $user_password,
                        'user_id'  => $student_id,
                        'role'     => 'student',
                    );

                    $this->user_model->add($data_student_login);
                    //===============End Student ID===========
                    //===============Start Parent ID===========

                    $parent_password   = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
                    $temp              = $student_id;
                    $data_parent_login = array(
                        'username' => $this->parent_login_prefix . $student_id,
                        'password' => $parent_password,
                        'user_id'  => 0,
                        'role'     => 'parent',
                        'childs'   => $temp,
                    );
                    $ins_parent_id  = $this->user_model->add($data_parent_login);
                    $update_student = array(
                        'id'        => $student_id,
                        'parent_id' => $ins_parent_id,
                    );
                    $this->student_model->add($update_student);

                    //===============End Parent ID===========
                    //============== Update setting modal===============================

                    if ($sch_setting_detail->adm_auto_insert) {
                        if ($sch_setting_detail->adm_update_status == 0) {
                            $data_setting                      = array();
                            $data_setting['id']                = $sch_setting_detail->id;
                            $data_setting['adm_update_status'] = 1;
                            $this->setting_model->add($data_setting);
                        }
                    }

                    //=============================================

                    $data['is_enroll']        = 1;
                    $data['class_section_id'] = $class_section_id;
                }
            }
            
            unset($data['route_pickup_point_id']);
            unset($data['vehroute_id']);
            $this->db->where('id', $data_id);
            $this->db->update('online_admissions', $data);

            $message   = UPDATE_RECORD_CONSTANT . " On  online admissions id " . $data_id;
            $action    = "Update";
            $record_id = $data_id;
            $this->log($message, $record_id, $action);

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
            }
        }
        return json_encode(array(
            'record_update_status' => $record_update_status,
            'admission_no' => isset($data['admission_no']) ? $data['admission_no'] : null,
            'student_id' => $student_id,
            'user_password' => $user_password,
            'parent_password' => $parent_password
        ));
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('belong_table_id', $id);
        $this->db->delete('online_admission_custom_field_value');
        
        $this->db->where('id', $id);
        $this->db->delete('online_admissions');
        $message   = DELETE_RECORD_CONSTANT . " On online admissions id " . $id;
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

    public function checkadmissionstatus($reference_no, $dob)
    {
        $arr = array('reference_no' => $reference_no, 'dob' => $dob);

        $this->db->select('id')->from('online_admissions')->where($arr);
        $query  = $this->db->get();
        $result = $query->row_array();
        if (!empty($result)) {
            return $result['id'];
        } else {
            return 0;
        }
    }

    public function getformfields()
    {
        $this->db->select('*');
        $this->db->from('online_admission_fields');
        $query = $this->db->get();
        return $query->result();
    }

    public function getfieldstatus($fieldname)
    {
        $this->db->where('name', $fieldname);
        $this->db->select('status');
        $this->db->from('online_admission_fields');
        $query  = $this->db->get();
        $result = $query->row_array();
        if(!empty($result)){
        return $result['status'];
        }
    }

    public function checkfieldexist($fieldname)
    {
        $this->db->where('name', $fieldname);
        $this->db->select("*");
        $this->db->from('online_admission_fields');
        $query  = $this->db->get();
        $result = $query->row_array();
        if (!empty($result)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function addformfields($record)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well

        $this->db->where('name', $record['name']);
        $q = $this->db->get('online_admission_fields');

        if ($q->num_rows() > 0) {
            $results = $q->row();
            $this->db->where('id', $results->id);
            $this->db->update('online_admission_fields', $record);
            $message   = UPDATE_RECORD_CONSTANT . " On  online_admission_fields id " . $results->id;
            $action    = "Update";
            $record_id = $insert_id = $results->id;
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('online_admission_fields', $record);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On online_admission_fields id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
    }

    public function getAdmissionData($admission_no)
    {
        $result = $this->db->select("*")
            ->where("id", $admission_no)
            ->get("online_admissions")
            ->row();
        return $result;
    }

    public function paymentSuccess($payment)
    {
        $paid_status = 1;
        if (isset($payment['paid_status']) && !empty($payment['paid_status'])) {
            $paid_status = $payment['paid_status'];
        }
        unset($payment['paid_status']);

        $this->db->update("online_admissions", array("paid_status" => $paid_status, "form_status" => 1, "submit_date" => date('Y-m-d')), array("id" => $payment['online_admission_id']));
        $this->db->insert("online_admission_payment", $payment);

        // If applicant_password was never set (gateway paid before form submission step),
        // set it now so the applicant can log in immediately after payment.
        if ((int) $paid_status === 1) {
            $row = $this->db->select('reference_no, applicant_password')
                            ->get_where('online_admissions', array('id' => $payment['online_admission_id']))
                            ->row_array();
            if (!empty($row['reference_no']) && empty($row['applicant_password'])) {
                $plain = $row['reference_no'] . '@ApplicantPortal' . date('Y');
                $this->db->where('id', $payment['online_admission_id'])
                         ->update('online_admissions', array('applicant_password' => md5($plain)));
            }
        }

        // Also record in incidental_fee_collections for unified daily/weekly/monthly reports.
        // Only do this for fully-paid gateway transactions (paid_status=1).
        if ((int) $paid_status === 1) {
            $this->_insertGatewayPaymentToIncidental($payment);
        }
    }

    /**
     * Insert a gateway application-fee payment into incidental_fee_collections
     * so it shows up in daily/weekly/monthly collection reports alongside manual payments.
     */
    private function _insertGatewayPaymentToIncidental(array $payment)
    {
        $admission_id = (int) ($payment['online_admission_id'] ?? 0);
        if ($admission_id === 0) {
            return;
        }

        // Get reference_no and name for this admission
        $row = $this->db->select('reference_no, firstname, middlename, lastname')
                        ->get_where('online_admissions', ['id' => $admission_id])
                        ->row_array();
        if (empty($row['reference_no'])) {
            return;
        }
        $ref_no      = trim($row['reference_no']);
        $applicant_name = trim(implode(' ', array_filter([
            $row['firstname'] ?? '',
            $row['middlename'] ?? '',
            $row['lastname']  ?? '',
        ])));

        // Avoid duplicate: skip if an application-fee incidental record already exists for this ref
        $ref_no_clean = preg_replace('/\s+/', '', $ref_no);
        $existing = $this->db
            ->select('ifc.id')
            ->from('incidental_fee_collections ifc')
            ->join('incidental_fee_types ift', 'ift.id = ifc.incidental_fee_type_id', 'inner')
            ->where("REPLACE(ifc.application_ref_no,' ','') = " . $this->db->escape($ref_no_clean), null, false)
            ->where("LOWER(ift.title) LIKE '%application%'", null, false)
            ->get()->row_array();
        if (!empty($existing)) {
            return; // already recorded (e.g. manual payment was made first)
        }

        // Find the application fee type (title LIKE '%application%')
        $fee_type = $this->db
            ->where("LOWER(title) LIKE '%application%'", null, false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('incidental_fee_types')
            ->row_array();
        if (empty($fee_type)) {
            log_message('error', 'Onlinestudent_model::_insertGatewayPaymentToIncidental — no application fee type found');
            return;
        }

        // Get session_id and the configured application fee amount from sch_settings.
        // Using online_admission_amount directly (never the gateway total which includes processing charge).
        $settings   = $this->db->select('session_id, online_admission_amount')->get('sch_settings')->row_array();
        $session_id = (int)   ($settings['session_id']              ?? 0);
        $amount     = (float) ($settings['online_admission_amount'] ?? $payment['paid_amount'] ?? 0);

        // Generate next receipt number using max numeric suffix (same as Incidental_fee_collection_model::get_receipt_no)
        $prefix = 'IFC-';
        $suffix_row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(receipt_no, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) AS max_num FROM incidental_fee_collections WHERE receipt_no LIKE ?",
            [$prefix . '%']
        )->row();
        $new_num    = ($suffix_row && $suffix_row->max_num) ? (int) $suffix_row->max_num + 1 : 1;
        $receipt_no = $prefix . str_pad($new_num, 6, '0', STR_PAD_LEFT);

        $txn_id   = isset($payment['transaction_id']) ? (string) $payment['transaction_id'] : null;
        $pay_mode = isset($payment['payment_mode'])   ? (string) $payment['payment_mode']   : 'online';
        $pay_date = isset($payment['date'])           ? date('Y-m-d', strtotime($payment['date'])) : date('Y-m-d');

        $insert_data = [
            'incidental_fee_type_id' => (int) $fee_type['id'],
            'session_id'             => $session_id,
            'application_ref_no'     => $ref_no,
            'non_student_name'       => $applicant_name,
            'amount_collected'       => $amount,
            'bill_date'              => $pay_date,
            'date_collected'         => $payment['date'] ?? date('Y-m-d H:i:s'),
            'receipt_no'             => $receipt_no,
            'payment_mode'           => $pay_mode,
            'txn_id'                 => $txn_id,
            'notes'                  => $payment['note'] ?? ('Online gateway payment — ' . $pay_mode),
        ];

        $insert_ok = $this->db->insert('incidental_fee_collections', $insert_data);
        if (!$insert_ok) {
            $err = $this->db->error();
            log_message('error', 'Onlinestudent_model::_insertGatewayPaymentToIncidental — INSERT failed for ref_no=' . $ref_no
                . ' receipt_no=' . $receipt_no . ' error=[' . $err['code'] . '] ' . $err['message']);
        } else {
            log_message('debug', 'Onlinestudent_model::_insertGatewayPaymentToIncidental — created incidental receipt ' . $receipt_no . ' for ref_no=' . $ref_no);
        }
    }

    /**
     * Record a successful course-fee gateway payment and create the IFC entry.
     */
    public function courseFeePaidSuccess(array $payment)
    {
        $insert = [
            'online_admission_id' => $payment['online_admission_id'],
            'transaction_id'      => $payment['transaction_id'],
            'paid_amount'         => $payment['paid_amount'],
            'payment_mode'        => $payment['payment_mode'],
            'payment_type'        => 'course_fee',
            'note'                => $payment['note'] ?? '',
            'date'                => $payment['date'] ?? date('Y-m-d H:i:s'),
        ];
        $this->db->insert('online_admission_payment', $insert);
        $this->_insertCourseFeeToIncidental($payment);
    }

    /**
     * Insert a gateway course-fee payment into incidental_fee_collections
     * using the TUITION FEE type and the actual paid amount.
     */
    private function _insertCourseFeeToIncidental(array $payment)
    {
        $admission_id = (int) ($payment['online_admission_id'] ?? 0);
        if ($admission_id === 0) {
            return;
        }

        $row = $this->db->select('reference_no, firstname, middlename, lastname')
                        ->get_where('online_admissions', ['id' => $admission_id])
                        ->row_array();
        if (empty($row['reference_no'])) {
            return;
        }
        $ref_no         = trim($row['reference_no']);
        $applicant_name = trim(implode(' ', array_filter([
            $row['firstname']  ?? '',
            $row['middlename'] ?? '',
            $row['lastname']   ?? '',
        ])));

        // Avoid duplicate: skip if a tuition-fee incidental record already exists for this ref
        $ref_no_clean = preg_replace('/\s+/', '', $ref_no);
        $existing_course = $this->db
            ->select('ifc.id')
            ->from('incidental_fee_collections ifc')
            ->join('incidental_fee_types ift', 'ift.id = ifc.incidental_fee_type_id', 'inner')
            ->where("REPLACE(ifc.application_ref_no,' ','') = " . $this->db->escape($ref_no_clean), null, false)
            ->where("LOWER(ift.title) LIKE '%tuition%'", null, false)
            ->get()->row_array();
        if (!empty($existing_course)) {
            return; // already recorded
        }

        // Find the tuition/course fee type
        $fee_type = $this->db
            ->where("LOWER(title) LIKE '%tuition%'", null, false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('incidental_fee_types')
            ->row_array();
        if (empty($fee_type)) {
            log_message('error', 'Onlinestudent_model::_insertCourseFeeToIncidental — no tuition fee type found');
            return;
        }

        $settings   = $this->db->select('session_id')->get('sch_settings')->row_array();
        $session_id = (int) ($settings['session_id'] ?? 0);
        $amount     = (float) $payment['paid_amount'];

        $prefix     = 'IFC-';
        $suffix_row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(receipt_no, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) AS max_num FROM incidental_fee_collections WHERE receipt_no LIKE ?",
            [$prefix . '%']
        )->row();
        $new_num    = ($suffix_row && $suffix_row->max_num) ? (int) $suffix_row->max_num + 1 : 1;
        $receipt_no = $prefix . str_pad($new_num, 6, '0', STR_PAD_LEFT);

        $txn_id   = isset($payment['transaction_id']) ? (string) $payment['transaction_id'] : null;
        $pay_mode = isset($payment['payment_mode'])   ? (string) $payment['payment_mode']   : 'online';
        $pay_date = isset($payment['date'])           ? date('Y-m-d', strtotime($payment['date'])) : date('Y-m-d');

        $insert_data = [
            'incidental_fee_type_id' => (int) $fee_type['id'],
            'session_id'             => $session_id,
            'application_ref_no'     => $ref_no,
            'non_student_name'       => $applicant_name,
            'amount_collected'       => $amount,
            'bill_date'              => $pay_date,
            'date_collected'         => $payment['date'] ?? date('Y-m-d H:i:s'),
            'receipt_no'             => $receipt_no,
            'payment_mode'           => $pay_mode,
            'txn_id'                 => $txn_id,
            'notes'                  => $payment['note'] ?? ('Course fee online gateway payment — ' . $pay_mode),
        ];

        $insert_ok = $this->db->insert('incidental_fee_collections', $insert_data);
        if (!$insert_ok) {
            $err = $this->db->error();
            log_message('error', 'Onlinestudent_model::_insertCourseFeeToIncidental — INSERT failed for ref_no=' . $ref_no
                . ' error=[' . $err['code'] . '] ' . $err['message']);
        } else {
            log_message('debug', 'Onlinestudent_model::_insertCourseFeeToIncidental — created receipt ' . $receipt_no . ' for ref_no=' . $ref_no);
        }
    }

    public function getclassbyclasssectionid($class_section_id)
    {
        $this->db->select("class_id,class");
        $this->db->from('class_sections');
        $this->db->join('classes', "classes.id=class_sections.class_id", "inner");
        $this->db->where("class_sections.id", $class_section_id);
        $query  = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    public function checkreferenceno($reference_no)
    {
        $this->db->select("*");
        $this->db->from('online_admissions');
        $this->db->where("reference_no", $reference_no);
        $query  = $this->db->get();
        $result = $query->row_array();
        if (!empty($result)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getcustomfields()
    {
        $this->db->select("name");
        $this->db->from('custom_fields');
        $this->db->where("belong_to", 'students');
        $query  = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    /**
     * this function is used to validate movie sell price
     */
    public function validate_paymentamount()
    {
        $str = $this->input->post('online_admission_amount');
        if ($str > 0) {
            return true;
        } elseif ($str == 0) {
            $this->form_validation->set_message('check_exists', $this->lang->line('invalid_payment_amount'));
            // return false;
        } elseif ($str = "") {
            $this->form_validation->set_message('check_exists', $this->lang->line('required'));
            // return false;
        }
    }

    //===========
    public function check_student_email_exists($str)
    {
        $email = $this->security->xss_clean($str);
        if ($email != "") {
            $id = $this->input->post('admission_id');
            if (!isset($id)) {
                $id = 0;
            }

            if ($this->check_data_exists($email, $id)) {
                $this->form_validation->set_message('check_student_email_exists', $this->lang->line('record_already_exist'));
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function check_data_exists($email, $id = null)
    {
        $this->db->where('email', $email);
        if ($id != null) {
            $this->db->where('id !=', $id);
        }
        $query = $this->db->get('online_admissions');
        $student_query = $this->db->query("select email from students where email='" . $email . "' ");
        if ($query->num_rows() > 0 || $student_query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function editguardianfield($status)
    {
        $data = array('guardian_relation', 'guardian_name', 'guardian_phone', 'guardian_photo', 'guardian_occupation', 'guardian_email', 'guardian_address');
        foreach ($data as $value) {
            $this->db->query("update online_admission_fields set status=" . $status . " where name='" . $value . "'   ");
        }
    }

    public function getidbyrefno($reference_no)
    {
        $query  = $this->db->query("SELECT id FROM online_admissions WHERE reference_no = ?", array($reference_no));
        $result = $query->row_array();
        return $result ? $result['id'] : null;
    }

    public function gethousename($id)
    {
        $query  = $this->db->query("select house_name from school_houses where id=" . $id . "   ");
        $result = $query->row_array();
        return $result['house_name'];
    }

    public function gettransactionid($id)
    {
        $query = $this->db->query("select transaction_id from online_admission_payment where online_admission_id=" . $id . "   ");
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result['transaction_id'];
        } else {
            return 0;
        }
    }
    
    public function gettransactionpaidamount($id)
    {
        $query = $this->db->query("select paid_amount from online_admission_payment where online_admission_id=" . $id . "   ");
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result['paid_amount'];
        } else {
            return 0;
        }
    }

    public function getOnlineAdmissionFeeCollectionReport($start_date, $end_date)
    {
        $query = "SELECT online_admissions.*,online_admission_payment.*,classes.class,sections.section FROM online_admissions
        join online_admission_payment on online_admissions.id = online_admission_payment.online_admission_id
        left join class_sections on class_sections.id = online_admissions.class_section_id
        left join classes on class_sections.class_id = classes.id
        left join sections on sections.id = class_sections.section_id
        left join hostel_rooms on hostel_rooms.id = online_admissions.hostel_room_id
        left join hostel on hostel.id = hostel_rooms.hostel_id
        left join room_types on room_types.id = hostel_rooms.room_type_id
        left join categories on online_admissions.category_id = categories.id
        left join vehicle_routes on vehicle_routes.id = online_admissions.vehroute_id
        left join transport_route on vehicle_routes.route_id = transport_route.id
        left join vehicles on vehicles.id = vehicle_routes.vehicle_id
        left join school_houses on school_houses.id = online_admissions.school_house_id
        where DATE_FORMAT(online_admission_payment.date, '%Y-%m-%d') >= '$start_date'
        and DATE_FORMAT(online_admission_payment.date, '%Y-%m-%d') <= '$end_date'
        ";
        $query = $this->db->query($query);
        return $query->result();
    }

    public function checkisenroll($reference_no)
    {
        $this->db->select("is_enroll");
        $this->db->from('online_admissions');
        $this->db->where("reference_no", $reference_no);
        $query  = $this->db->get();
        $result = $query->row_array();
        if ($result['is_enroll'] == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    public function check_admissions_data_exists($key, $value)
    {
        $this->db->where($key, $value);
        $query = $this->db->get('online_admissions');
        $student_query = $this->db->query("select * from students where ".$key."='" . $value . "' ");
        if ($query->num_rows() > 0 || $student_query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_admission_by_field($field, $value)
    {
        $this->db->select('id, reference_no');
        $this->db->where($field, $value);
        $query = $this->db->get('online_admissions');
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    public function count_submissions_by_email_and_year($email, $academic_year)
    {
        // Example: Academic year "2026-2027" means from 2026-08-01 to 2027-07-31
        list($start_year, $end_year) = explode('-', $academic_year);
        $start_date = $start_year . '-08-01'; // Assuming August 1st start
        $end_date = $end_year . '-07-31';   // Assuming July 31st end

        $this->db->where('email', $email);
        $this->db->where('created_at >=', $start_date);
        $this->db->where('created_at <=', $end_date);
        $this->db->from('online_admissions');
        return $this->db->count_all_results();
    }

    /**
     * Update payment status specifically
     * This is a simpler, direct update that bypasses complex transaction logic
     */
    public function update_payment_status($online_admission_id, $transaction_id, $note, $payment_updated_by, $payment_updated_at) {
        $data = array(
            'paid_status' => 1,
            'transaction_id' => $transaction_id,
            'note' => $note,
            'payment_updated_by' => $payment_updated_by,
            'payment_updated_at' => $payment_updated_at,
        );
        
        $this->db->where('id', $online_admission_id);
        $result = $this->db->update('online_admissions', $data);
        
        if ($result) {
            // Log the action
            $message = "Payment status updated for online admission id " . $online_admission_id;
            $action = "Update";
            $this->log($message, $online_admission_id, $action);
            return true;
        }
        return false;
    }
}

