<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'core/MY_Addon_MBController.php');

class Branch extends MY_Addon_MBController
{

    public function __construct()
    {
        parent::__construct();

    }

    /*
    Management Command Centre — lightweight page load; heavy data via AJAX
    */
    public function overview()
    {
        $data = array();
        $this->load->model("multibranch/multi_common_model");

        $branches = $this->multibranch_model->getSchoolCurrentSessions();

        // Student + staff counts are fast (COUNT queries) — include server-side
        // so institution cards render instantly without waiting for AJAX
        $school_students = $this->multi_common_model->getStudentCount($branches);
        $staff_list      = $this->multi_common_model->getStaff($branches);

        // Home branch real name (getSchoolCurrentSessions overwrites it with lang string)
        $home_row        = $this->db->select('name')->from('sch_settings')->limit(1)->get()->row();
        $data['home_name']       = $home_row ? $home_row->name : $this->lang->line('home_branch');
        $data['home_short_name'] = '';

        $data['branches']        = $branches;
        $data['school_students'] = $school_students;
        $data['staff_list']      = $staff_list;
        $data['branch_list']     = $this->multibranch_model->get();
        $data['month']           = date("F", strtotime('-1 month'));
        $data['year']            = date("Y", strtotime('-1 month'));

        $this->load->view('layout/header', $data);
        $this->load->view('admin/multibranch/overview', $data);
        $this->load->view('layout/footer', $data);
    }

    /*
    AJAX — HR & Payroll section data
    */
    public function hr_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close(); // release session lock so parallel AJAX calls don't queue

        $this->load->model("multibranch/multi_common_model");
        $branches = $this->multibranch_model->getSchoolCurrentSessions();

        // Find the most recently generated payroll month/year (not hardcoded to last month)
        $last_payroll = $this->db->query(
            "SELECT month, year FROM staff_payslip
             ORDER BY CAST(year AS UNSIGNED) DESC,
                      FIELD(month,'January','February','March','April','May','June',
                                 'July','August','September','October','November','December') DESC
             LIMIT 1"
        )->row();

        if ($last_payroll) {
            $month = $last_payroll->month;
            $year  = $last_payroll->year;
        } else {
            $month = date("F", strtotime('-1 month'));
            $year  = date("Y", strtotime('-1 month'));
        }

        $staff_payslip         = $this->multi_common_model->getStaffPayslipCount($month, $year, $branches);
        $staff_list            = $this->multi_common_model->getStaff($branches);
        $staff_attendance_list = $this->multi_common_model->getStaffAttendance(date('Y-m-d'), $branches);

        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $rows            = [];
        $chart_labels    = [];
        $chart_payroll   = [];
        $chart_paid      = [];

        foreach ($branches as $db_name => $branch_info) {
            $payroll_data      = $staff_payslip[$db_name]['total_payroll_record'];
            $total_payroll     = 0;
            $payroll_paid_amt  = 0;
            $payroll_generated = 0;
            $payroll_paid_cnt  = 0;

            if (!empty($payroll_data)) {
                foreach ($payroll_data as $p) {
                    $total_payroll += $p->net_salary;
                    if ($p->status === 'generated') {
                        $payroll_generated++;
                    } else {
                        $payroll_paid_cnt++;
                        $payroll_paid_amt += $p->net_salary;
                    }
                }
            }

            $staff_present = 0;
            $staff_absent  = 0;
            if (!empty($staff_attendance_list[$db_name])) {
                foreach ($staff_attendance_list[$db_name] as $att) {
                    if ($att->attendence_id > 0) {
                        if ($att->att_type === 'Absent') $staff_absent++;
                        else                             $staff_present++;
                    }
                }
            }

            $total_staff        = $staff_list[$db_name]['total_staff'];
            $payroll_not_gen    = max(0, $total_staff - $payroll_generated - $payroll_paid_cnt);

            $rows[] = [
                'db_name'           => $db_name,
                'name'              => $branch_info->name,
                'total_staff'       => $total_staff,
                'payroll_amount'    => $total_payroll,
                'payroll_paid'      => $payroll_paid_amt,
                'payroll_amount_fmt'=> $currency_symbol . amountFormat($total_payroll),
                'payroll_paid_fmt'  => $currency_symbol . amountFormat($payroll_paid_amt),
                'payroll_generated' => $payroll_generated,
                'payroll_paid_cnt'  => $payroll_paid_cnt,
                'payroll_not_gen'   => $payroll_not_gen,
                'staff_present'     => $staff_present,
                'staff_absent'      => $staff_absent,
            ];

            $chart_labels[]  = $branch_info->name;
            $chart_payroll[] = $total_payroll;
            $chart_paid[]    = $payroll_paid_amt;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'month'  => $month,
                'year'   => $year,
                'rows'   => $rows,
                'chart'  => ['labels' => $chart_labels, 'payroll' => $chart_payroll, 'paid' => $chart_paid],
            ]));
    }

    /*
    AJAX — Assets / Inventory section data
    */
    public function assets_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close(); // release session lock so parallel AJAX calls don't queue

        $this->load->model("multibranch/multi_common_model");
        $branches        = $this->multibranch_model->getSchoolCurrentSessions();
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        // Pass pre-loaded branch list to avoid redundant multi_branch query inside model
        $branch_list     = $this->multibranch_model->get();
        $inventory       = $this->multi_common_model->getInventorySummary($branches, $branch_list);

        $rows         = [];
        $chart_labels = [];
        $chart_values = [];

        foreach ($branches as $db_name => $branch_info) {
            $inv = $inventory[$db_name];
            $cats = [];
            foreach ($inv['categories'] as $cat) {
                $cats[] = [
                    'name'           => $cat->category_name,
                    'item_types'     => (int) $cat->item_types,
                    'total_stock'    => (int) $cat->total_stock,
                    'total_value'    => (float) $cat->total_value,
                    'total_value_fmt'=> $currency_symbol . amountFormat($cat->total_value),
                ];
            }
            $rows[] = [
                'db_name'        => $db_name,
                'name'           => $branch_info->name,
                'total_items'    => $inv['total_items'],
                'total_stock'    => $inv['total_stock'],
                'total_value'    => $inv['total_value'],
                'total_value_fmt'=> $currency_symbol . amountFormat($inv['total_value']),
                'categories'     => $cats,
            ];
            $chart_labels[] = $branch_info->name;
            $chart_values[] = $inv['total_value'];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'rows'   => $rows,
                'chart'  => ['labels' => $chart_labels, 'values' => $chart_values],
            ]));
    }

    // ================================================================
    // ASSETS DRILLDOWN  (institution → item → store/location breakdown)
    // ================================================================
    public function assets_drilldown_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close();

        $branches      = $this->multibranch_model->getSchoolCurrentSessions();
        $branches_list = $this->multibranch_model->get();

        $branch_id_map = [];
        foreach ($branches_list as $b) {
            $branch_id_map[$b->database_name] = $b->id;
        }

        $result = [];
        foreach ($branches as $db_name => $branch_info) {
            if ($db_name === $this->db->database) {
                $db = $this->db;
            } elseif (isset($branch_id_map[$db_name])) {
                $db = $this->load->database('branch_' . $branch_id_map[$db_name], true);
            } else {
                continue;
            }

            try {
                $result[$db_name] = $this->_mcc_assets_drilldown($db);
            } catch (Throwable $e) {
                log_message('error', '[MCC] assets_drilldown_async ' . $db_name . ': ' . $e->getMessage());
                $result[$db_name] = [];
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'drilldown' => $result]));
    }

    /**
     * Returns item-level breakdown with store/location split.
     * Shape: [ ['item_id'=>N,'name'=>'...','category'=>'...','total_stock'=>N,'total_value'=>N,'stores'=>[...]], ... ]
     */
    private function _mcc_assets_drilldown($db)
    {
        // ── 1. Items with total stock and value ────────────────────────────
        $item_rows = $db->query(
            "SELECT i.id AS item_id,
                    i.name AS item_name,
                    ic.item_category AS category_name,
                    SUM(ist.quantity) AS total_stock,
                    ROUND(SUM(ist.quantity * ist.purchase_price)) AS total_value
             FROM item i
             JOIN item_category ic ON ic.id = i.item_category_id AND ic.is_active = 'yes'
             JOIN item_stock ist ON ist.item_id = i.id AND ist.is_active = 'yes'
             GROUP BY i.id, i.name, ic.item_category
             ORDER BY ic.item_category, SUM(ist.quantity) DESC"
        )->result();

        if (empty($item_rows)) return [];

        // ── 2. Store/location breakdown per item ───────────────────────────
        $store_rows = $db->query(
            "SELECT ist.item_id,
                    COALESCE(s.item_store, 'Default Store') AS store_name,
                    SUM(ist.quantity) AS quantity
             FROM item_stock ist
             LEFT JOIN item_store s ON s.id = ist.store_id
             WHERE ist.is_active = 'yes'
             GROUP BY ist.item_id, ist.store_id
             ORDER BY ist.item_id, SUM(ist.quantity) DESC"
        )->result();

        $stores_by_item = [];
        foreach ($store_rows as $sr) {
            $stores_by_item[(int)$sr->item_id][] = [
                'store_name' => $sr->store_name,
                'quantity'   => (int)$sr->quantity,
            ];
        }

        $items = [];
        foreach ($item_rows as $row) {
            $iid     = (int)$row->item_id;
            $items[] = [
                'item_id'     => $iid,
                'name'        => $row->item_name,
                'category'    => $row->category_name,
                'total_stock' => (int)$row->total_stock,
                'total_value' => (float)$row->total_value,
                'stores'      => isset($stores_by_item[$iid]) ? $stores_by_item[$iid] : [],
            ];
        }

        return $items;
    }

    /*
    AJAX — Academics section data (library, admissions, alumni)
    */
    public function academics_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close(); // release session lock so parallel AJAX calls don't queue

        try {
            $this->load->model("multibranch/multi_common_model");
            $branches  = $this->multibranch_model->getSchoolCurrentSessions();
            // Single consolidated call — opens each branch DB once instead of 6 times
            $academics = $this->multi_common_model->getAcademicsSummary($branches);

            $rows = [];
            foreach ($branches as $db_name => $branch_info) {
                $ac     = $academics[$db_name];
                $rows[] = [
                    'db_name'          => $db_name,
                    'name'             => $branch_info->name,
                    'session'          => $branch_info->session,
                    'total_books'      => $ac['total_books'],
                    'library_members'  => $ac['total_members'],
                    'book_issued'      => $ac['total_book_issued'],
                    'offline_admission'=> $ac['offline_admission'],
                    'online_admission' => $ac['online_admission'],
                    'total_alumni'     => $ac['total_alumni_student'],
                ];
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'rows' => $rows]));

        } catch (Throwable $e) {
            log_message('error', 'academics_async: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        }
    }

    /*
    AJAX — Admissions & Complaints section data
    */
    public function admission_complaint_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close();

        try {
            $branches        = $this->multibranch_model->getSchoolCurrentSessions();
            $branches_list   = $this->multibranch_model->get();
            $default_db_name = $this->db->database;

            $branch_id_map = [];
            foreach ($branches_list as $b) {
                $branch_id_map[$b->database_name] = $b->id;
            }

            $rows = [];
            foreach ($branches as $db_name => $branch_info) {
                $session_id = (int)$branch_info->session_id;
                $adm_sid    = !empty($branch_info->online_admission_session_id)
                    ? (int)$branch_info->online_admission_session_id
                    : $session_id;

                if ($db_name === $default_db_name) {
                    $db = $this->db;
                } elseif (isset($branch_id_map[$db_name])) {
                    $db = $this->load->database('branch_' . $branch_id_map[$db_name], true);
                } else {
                    continue;
                }

                $r_admitted = $db->query(
                    "SELECT COUNT(*) AS c FROM students
                     WHERE admission_session_id = ? AND is_active = 'yes'",
                    [$session_id]
                );
                // Mirror dashboard getOnlineStudentPaymentOverview(): join incidental_fee_collections
                // to bucket by actual payment, not the stale paid_status column.
                $r_online = $db->query("
                    SELECT
                        SUM(CASE
                            WHEN t.tuition_paid IS NOT NULL AND t.tuition_paid > 0
                                 AND oa.course_fee_total > 0
                                 AND t.tuition_paid >= oa.course_fee_total THEN 1
                            ELSE 0 END) AS fully_paid,
                        SUM(CASE
                            WHEN t.tuition_paid IS NOT NULL AND t.tuition_paid > 0
                                 AND (oa.course_fee_total IS NULL OR oa.course_fee_total = 0
                                      OR t.tuition_paid < oa.course_fee_total) THEN 1
                            ELSE 0 END) AS partially_paid,
                        SUM(CASE
                            WHEN (t.tuition_paid IS NULL OR t.tuition_paid = 0)
                                 AND (af.app_ref IS NOT NULL OR oa.paid_status = 1) THEN 1
                            ELSE 0 END) AS app_fee_only,
                        COUNT(*) AS total_active
                    FROM online_admissions oa
                    LEFT JOIN (
                        SELECT REPLACE(ifc.application_ref_no,' ','') AS ref,
                               SUM(ifc.amount_collected) AS tuition_paid
                        FROM incidental_fee_collections ifc
                        JOIN incidental_fee_types ift ON ift.id = ifc.incidental_fee_type_id
                        WHERE ifc.application_ref_no IS NOT NULL
                          AND ifc.application_ref_no != ''
                          AND (LOWER(ift.title) LIKE '%tuition%'
                               OR LOWER(ift.title) LIKE '%tution%'
                               OR LOWER(ift.title) LIKE '%other fee%')
                          AND ifc.amount_collected > 0
                        GROUP BY REPLACE(ifc.application_ref_no,' ','')
                    ) t ON REPLACE(oa.reference_no,' ','') = t.ref
                    LEFT JOIN (
                        SELECT DISTINCT REPLACE(ifc.application_ref_no,' ','') AS app_ref
                        FROM incidental_fee_collections ifc
                        JOIN incidental_fee_types ift ON ift.id = ifc.incidental_fee_type_id
                        WHERE ifc.application_ref_no IS NOT NULL
                          AND ifc.application_ref_no != ''
                          AND LOWER(ift.title) LIKE '%application fee%'
                          AND ifc.amount_collected > 0
                    ) af ON REPLACE(oa.reference_no,' ','') = af.app_ref
                    WHERE oa.session_id = ?
                      AND COALESCE(oa.admission_status,'active') = 'active'
                ", [$adm_sid]);
                $r_revok  = $db->query("SELECT COUNT(*) AS c FROM online_admissions WHERE session_id = ? AND COALESCE(admission_status,'active') = 'cancelled'", [$adm_sid]);
                $r_cmp    = $db->query("SELECT status, COUNT(*) AS c FROM complaint GROUP BY status");

                $admitted          = ($r_admitted && $r_admitted->num_rows() > 0) ? (int)$r_admitted->row()->c : 0;
                $online_row        = ($r_online && $r_online->num_rows() > 0) ? $r_online->row_array() : [];
                $online_fully_paid = (int)($online_row['fully_paid']    ?? 0);
                $online_partially  = (int)($online_row['partially_paid'] ?? 0);
                $online_app_fee    = (int)($online_row['app_fee_only']   ?? 0);
                $online_received   = $online_fully_paid + $online_partially + $online_app_fee;
                $online_revoked    = ($r_revok && $r_revok->num_rows() > 0) ? (int)$r_revok->row()->c : 0;

                $complaints = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
                if ($r_cmp) {
                    foreach ($r_cmp->result_array() as $r) {
                        if (isset($complaints[$r['status']])) {
                            $complaints[$r['status']] = (int)$r['c'];
                        }
                    }
                }

                $rows[] = [
                    'db_name'              => $db_name,
                    'name'                 => $branch_info->name,
                    'session'              => $branch_info->session,
                    'admitted'             => $admitted,
                    'online_received'      => $online_received,
                    'online_fully_paid'    => $online_fully_paid,
                    'online_partially'     => $online_partially,
                    'online_app_fee'       => $online_app_fee,
                    'online_revoked'       => $online_revoked,
                    'complaints_open'      => $complaints['open'],
                    'complaints_inprogress'=> $complaints['in_progress'],
                    'complaints_resolved'  => $complaints['resolved'],
                    'complaints_closed'    => $complaints['closed'],
                    'complaints_total'     => array_sum($complaints),
                ];
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'rows' => $rows]));

        } catch (Throwable $e) {
            log_message('error', 'admission_complaint_async: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
        }
    }

    public function fees_overview_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close(); // release session lock so parallel AJAX calls don't queue

        $branches        = $this->multibranch_model->getSchoolCurrentSessions();
        $branches_list   = $this->multibranch_model->get();
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

        // Build branch_id lookup map
        $branch_id_map = [];
        foreach ($branches_list as $b) {
            $branch_id_map[$b->database_name] = $b->id;
        }

        $rows                = [];
        $chart_labels        = [];
        $chart_total_fees    = [];
        $chart_total_paid    = [];
        $chart_total_balance = [];

        foreach ($branches as $db_name => $branch_info) {
            $session_id = $branch_info->session_id;

            // Pick correct DB connection
            if ($db_name === $this->db->database) {
                $db = $this->db;
            } elseif (isset($branch_id_map[$db_name])) {
                $db = $this->load->database('branch_' . $branch_id_map[$db_name], true);
            } else {
                continue;
            }

            try {
                list($total_fees, $total_paid, $fee_type_breakdown) = $this->_mcc_fees_summary($db, $session_id);
                $counts = $this->_mcc_student_counts($db, $session_id);
            } catch (Throwable $e) {
                log_message('error', '[MCC] fees_overview_async ' . $db_name . ': ' . $e->getMessage());
                $total_fees = 0;
                $total_paid = 0;
                $fee_type_breakdown = [];
                $counts     = ['fully_paid' => 0, 'fully_paid_amt' => 0.0, 'partial' => 0, 'partial_amt' => 0.0, 'not_paid' => 0, 'not_paid_billed' => 0.0, 'by_class' => []];
            }
            $total_balance = $total_fees - $total_paid;

            $rows[] = [
                'db_name'                 => $db_name,
                'name'                    => $branch_info->name,
                'session'                 => $branch_info->session,
                'total_fees'              => $total_fees,
                'total_paid'              => $total_paid,
                'total_balance'           => $total_balance,
                'total_fees_formatted'    => $currency_symbol . amountFormat($total_fees),
                'total_paid_formatted'    => $currency_symbol . amountFormat($total_paid),
                'total_balance_formatted' => $currency_symbol . amountFormat($total_balance),
                'collection_pct'          => $total_fees > 0 ? round(($total_paid / $total_fees) * 100, 1) : 0,
                'fully_paid_count'        => $counts['fully_paid'],
                'fully_paid_amt'          => $counts['fully_paid_amt'],
                'partial_count'           => $counts['partial'],
                'partial_amt'             => $counts['partial_amt'],
                'not_paid_count'          => $counts['not_paid'],
                'not_paid_billed'         => $counts['not_paid_billed'],
                'fee_type_breakdown'      => $fee_type_breakdown,
            ];

            $chart_labels[]        = $branch_info->name;
            $chart_total_fees[]    = $total_fees;
            $chart_total_paid[]    = $total_paid;
            $chart_total_balance[] = $total_balance;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'rows'   => $rows,
                'chart'  => [
                    'labels'        => $chart_labels,
                    'total_fees'    => $chart_total_fees,
                    'total_paid'    => $chart_total_paid,
                    'total_balance' => $chart_total_balance,
                ],
            ]));
    }

    /**
     * Aggregate fees summary for one branch/session using 3 SQL queries
     * instead of N×4 per-student queries.
     * Returns [total_billed, total_collected].
     */
    private function _mcc_fees_summary($db, $session_id)
    {
        // ── 1. Total billed (tuition + other + hostel; no advance) ────────────
        $billed_row = $db->query(
            "SELECT SUM(COALESCE(sfo.override_amount, fgf.amount, sfm.amount)) AS total_billed
             FROM student_fees_master sfm
             JOIN student_session ss  ON ss.id  = sfm.student_session_id
             JOIN students s          ON s.id   = ss.student_id AND s.is_active = 'yes'
             JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
             JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
             JOIN feetype ft           ON ft.id  = fgf.feetype_id
                                      AND LOWER(ft.type) NOT IN ('advance payments')
             LEFT JOIN student_fee_overrides sfo
                    ON sfo.student_session_id   = sfm.student_session_id
                   AND sfo.fee_groups_feetype_id = fgf.id
             WHERE ss.session_id = ? AND sfm.is_active = 'yes'",
            [$session_id]
        )->row();
        $total_billed = $billed_row ? (float)$billed_row->total_billed : 0;

        // ── 1b. Billed per fee type ──────────────────────────────────────────
        $billed_by_type = $db->query(
            "SELECT ft.type AS fee_type,
                    SUM(COALESCE(sfo.override_amount, fgf.amount, sfm.amount)) AS billed
             FROM student_fees_master sfm
             JOIN student_session ss  ON ss.id  = sfm.student_session_id
             JOIN students s          ON s.id   = ss.student_id AND s.is_active = 'yes'
             JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
             JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
             JOIN feetype ft           ON ft.id  = fgf.feetype_id
                                      AND LOWER(ft.type) NOT IN ('advance payments')
             LEFT JOIN student_fee_overrides sfo
                    ON sfo.student_session_id   = sfm.student_session_id
                   AND sfo.fee_groups_feetype_id = fgf.id
             WHERE ss.session_id = ? AND sfm.is_active = 'yes'
             GROUP BY ft.type",
            [$session_id]
        )->result();
        $fee_type_billed = [];
        foreach ($billed_by_type as $row) {
            $fee_type_billed[$row->fee_type] = (float)$row->billed;
        }

        // ── 2. Transport billed ────────────────────────────────────────────────
        $transport_billed = 0;
        if ($db->table_exists('student_transport_fees') && $db->table_exists('route_pickup_point')) {
            $tb_row = $db->query(
                "SELECT SUM(COALESCE(stf.fee_override, rpp.fees)) AS transport_billed
                 FROM student_transport_fees stf
                 JOIN student_session ss ON ss.id = stf.student_session_id
                 JOIN students s         ON s.id  = ss.student_id AND s.is_active = 'yes'
                 JOIN route_pickup_point rpp ON rpp.id = stf.route_pickup_point_id
                 WHERE ss.session_id = ?",
                [$session_id]
            )->row();
            $transport_billed = $tb_row ? (float)$tb_row->transport_billed : 0;
        }
        $total_billed += $transport_billed;

        // ── 3. Total collected (parse JSON amount_detail in PHP) ──────────────
        $deposits = $db->query(
            "SELECT sfd.amount_detail, ft.type AS fee_type
             FROM student_fees_deposite sfd
             JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                                         AND sfm.is_active = 'yes'
             JOIN fee_groups_feetype fgf  ON fgf.id = sfd.fee_groups_feetype_id
             JOIN feetype ft             ON ft.id  = fgf.feetype_id
             JOIN student_session ss ON ss.id = sfm.student_session_id
             JOIN students s         ON s.id  = ss.student_id AND s.is_active = 'yes'
             WHERE ss.session_id = ?
               AND sfd.amount_detail IS NOT NULL AND sfd.amount_detail != '0'",
            [$session_id]
        )->result();

        $total_collected = 0;
        $fee_type_collected = [];
        foreach ($deposits as $dep) {
            $detail = json_decode($dep->amount_detail);
            if (!is_object($detail)) continue;
            $ft = $dep->fee_type;
            if (!isset($fee_type_collected[$ft])) $fee_type_collected[$ft] = 0;
            foreach ($detail as $payment) {
                $amt = (float)$payment->amount + (float)(isset($payment->amount_discount) ? $payment->amount_discount : 0);
                $total_collected += $amt;
                $fee_type_collected[$ft] += $amt;
            }
        }

        // Transport collected (stored with student_transport_fee_id)
        $transport_deposits = $db->query(
            "SELECT sfd.amount_detail
             FROM student_fees_deposite sfd
             JOIN student_transport_fees stf ON stf.id = sfd.student_transport_fee_id
             JOIN student_session ss ON ss.id = stf.student_session_id
             WHERE ss.session_id = ?
               AND sfd.amount_detail IS NOT NULL AND sfd.amount_detail != '0'",
            [$session_id]
        )->result();

        foreach ($transport_deposits as $dep) {
            $detail = json_decode($dep->amount_detail);
            if (!is_object($detail)) continue;
            foreach ($detail as $payment) {
                $total_collected += (float)$payment->amount;
                if (isset($payment->amount_discount)) {
                    $total_collected += (float)$payment->amount_discount;
                }
            }
        }

        // Build fee type breakdown
        $all_fee_types = array_unique(array_merge(array_keys($fee_type_billed), array_keys($fee_type_collected)));
        $fee_type_breakdown = [];
        foreach ($all_fee_types as $ft) {
            $b = isset($fee_type_billed[$ft]) ? $fee_type_billed[$ft] : 0;
            $c = isset($fee_type_collected[$ft]) ? $fee_type_collected[$ft] : 0;
            $fee_type_breakdown[$ft] = ['billed' => $b, 'collected' => $c, 'balance' => $b - $c];
        }

        return [$total_billed, $total_collected, $fee_type_breakdown];
    }

    /**
     * Categorises every enrolled student for a session into:
     *   fully_paid  — paid >= billed
     *   partial     — 0 < paid < billed
     *   not_paid    — paid = 0
     * Returns totals + a by_class[] array keyed by class_id for drilldown use.
     */
    private function _mcc_student_counts($db, $session_id)
    {
        // ── Billed per student_session (with class_id) ────────────────────────
        $billed_rows = $db->query(
            "SELECT sfm.student_session_id AS ss_id, ss.class_id,
                    SUM(COALESCE(sfo.override_amount, fgf.amount, sfm.amount)) AS billed
             FROM student_fees_master sfm
             JOIN student_session ss  ON ss.id  = sfm.student_session_id
             JOIN students s          ON s.id   = ss.student_id AND s.is_active = 'yes'
             JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
             JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
             JOIN feetype ft           ON ft.id  = fgf.feetype_id
                                      AND LOWER(ft.type) NOT IN ('advance payments')
             LEFT JOIN student_fee_overrides sfo
                    ON sfo.student_session_id   = sfm.student_session_id
                   AND sfo.fee_groups_feetype_id = fgf.id
             WHERE ss.session_id = ? AND sfm.is_active = 'yes'
             GROUP BY sfm.student_session_id, ss.class_id",
            [$session_id]
        )->result();

        // ── Deposits per student_session (parse JSON in PHP) ──────────────────
        $deposit_rows = $db->query(
            "SELECT sfm.student_session_id AS ss_id, sfd.amount_detail
             FROM student_fees_deposite sfd
             JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                                         AND sfm.is_active = 'yes'
             JOIN student_session ss ON ss.id = sfm.student_session_id
             JOIN students s         ON s.id  = ss.student_id AND s.is_active = 'yes'
             WHERE ss.session_id = ?
               AND sfd.amount_detail IS NOT NULL AND sfd.amount_detail != '0'",
            [$session_id]
        )->result();

        $paid_by_ss = [];
        foreach ($deposit_rows as $dep) {
            $detail = json_decode($dep->amount_detail);
            if (!is_object($detail)) continue;
            $ssid = (int)$dep->ss_id;
            if (!isset($paid_by_ss[$ssid])) $paid_by_ss[$ssid] = 0.0;
            foreach ($detail as $payment) {
                $paid_by_ss[$ssid] += (float)$payment->amount;
                if (isset($payment->amount_discount)) {
                    $paid_by_ss[$ssid] += (float)$payment->amount_discount;
                }
            }
        }

        $totals = [
            'fully_paid'     => 0, 'fully_paid_amt'    => 0.0,
            'partial'        => 0, 'partial_amt'       => 0.0,
            'not_paid'       => 0, 'not_paid_billed'   => 0.0,
            'by_class'       => [],
        ];

        foreach ($billed_rows as $row) {
            $ssid   = (int)$row->ss_id;
            $cid    = (int)$row->class_id;
            $billed = (float)$row->billed;
            $paid   = isset($paid_by_ss[$ssid]) ? $paid_by_ss[$ssid] : 0.0;

            if (!isset($totals['by_class'][$cid])) {
                $totals['by_class'][$cid] = [
                    'fully_paid' => 0, 'fully_paid_amt'  => 0.0,
                    'partial'    => 0, 'partial_amt'     => 0.0,
                    'not_paid'   => 0, 'not_paid_billed' => 0.0,
                ];
            }

            if ($billed > 0 && $paid >= $billed) {
                $totals['fully_paid']++;
                $totals['fully_paid_amt'] += $paid;
                $totals['by_class'][$cid]['fully_paid']++;
                $totals['by_class'][$cid]['fully_paid_amt'] += $paid;
            } elseif ($paid > 0) {
                $totals['partial']++;
                $totals['partial_amt'] += $paid;
                $totals['by_class'][$cid]['partial']++;
                $totals['by_class'][$cid]['partial_amt'] += $paid;
            } else {
                $totals['not_paid']++;
                $totals['not_paid_billed'] += $billed;
                $totals['by_class'][$cid]['not_paid']++;
                $totals['by_class'][$cid]['not_paid_billed'] += $billed;
            }
        }

        return $totals;
    }

    // ================================================================
    // FEES DRILLDOWN  (year → class breakdown per institution)
    // ================================================================
    public function fees_drilldown_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close();

        $branches      = $this->multibranch_model->getSchoolCurrentSessions();
        $branches_list = $this->multibranch_model->get();

        $branch_id_map = [];
        foreach ($branches_list as $b) {
            $branch_id_map[$b->database_name] = $b->id;
        }

        $result = [];
        foreach ($branches as $db_name => $branch_info) {
            $session_id = $branch_info->session_id;

            if ($db_name === $this->db->database) {
                $db = $this->db;
            } elseif (isset($branch_id_map[$db_name])) {
                $db = $this->load->database('branch_' . $branch_id_map[$db_name], true);
            } else {
                continue;
            }

            try {
                $result[$db_name] = $this->_mcc_fees_drilldown($db, $session_id);
            } catch (Throwable $e) {
                log_message('error', '[MCC] fees_drilldown_async ' . $db_name . ': ' . $e->getMessage());
                $result[$db_name] = [];
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'drilldown' => $result]));
    }

    // ================================================================
    // NOT-PAID STUDENTS LIST  (for eye-icon modal)
    // ================================================================
    /**
     * GET params: db (database name), class_id (optional — 0 = all classes)
     * Returns the list of not-paid students for the given branch + current session.
     */
    public function fees_not_paid_students_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close();

        $req_db = $this->input->get('db');

        // Accept comma-separated class IDs (e.g. "3,5,7"); "0" or empty = all classes
        $raw_ids    = $this->input->get('class_ids') ?: '0';
        $class_ids  = array_values(array_filter(array_map('intval', explode(',', $raw_ids))));
        $class_filter = !empty($class_ids)
            ? 'AND ss.class_id IN (' . implode(',', $class_ids) . ')'
            : '';

        $branches      = $this->multibranch_model->getSchoolCurrentSessions();
        $branches_list = $this->multibranch_model->get();

        $branch_id_map = [];
        foreach ($branches_list as $b) {
            $branch_id_map[$b->database_name] = $b->id;
        }

        if (!isset($branches[$req_db])) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Unknown branch']));
        }

        $session_id = $branches[$req_db]->session_id;
        if ($req_db === $this->db->database) {
            $db = $this->db;
        } elseif (isset($branch_id_map[$req_db])) {
            $db = $this->load->database('branch_' . $branch_id_map[$req_db], true);
        } else {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Branch not accessible']));
        }

        try {
            // ── Billed per student_session ─────────────────────────────────────
            $billed_rows  = $db->query(
                "SELECT sfm.student_session_id AS ss_id,
                        ss.class_id,
                        s.admission_no,
                        CONCAT(s.firstname, ' ', s.lastname) AS student_name,
                        c.class AS class_name,
                        COALESCE(sec.section, '—') AS section_name,
                        SUM(COALESCE(sfo.override_amount, fgf.amount, sfm.amount)) AS billed
                 FROM student_fees_master sfm
                 JOIN student_session ss  ON ss.id  = sfm.student_session_id $class_filter
                 JOIN students s          ON s.id   = ss.student_id AND s.is_active = 'yes'
                 JOIN classes c           ON c.id   = ss.class_id
                 LEFT JOIN sections sec   ON sec.id = ss.section_id
                 JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
                 JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
                 JOIN feetype ft           ON ft.id  = fgf.feetype_id
                                          AND LOWER(ft.type) NOT IN ('advance payments')
                 LEFT JOIN student_fee_overrides sfo
                        ON sfo.student_session_id    = sfm.student_session_id
                       AND sfo.fee_groups_feetype_id = fgf.id
                 WHERE ss.session_id = ? AND sfm.is_active = 'yes'
                 GROUP BY sfm.student_session_id, ss.class_id, s.admission_no,
                          s.firstname, s.lastname, c.class, sec.section
                 ORDER BY c.class, s.admission_no",
                [$session_id]
            )->result();

            // ── Deposits per student_session ───────────────────────────────────
            $deposit_rows = $db->query(
                "SELECT sfm.student_session_id AS ss_id, sfd.amount_detail
                 FROM student_fees_deposite sfd
                 JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                                             AND sfm.is_active = 'yes'
                 JOIN student_session ss ON ss.id = sfm.student_session_id
                 JOIN students s         ON s.id  = ss.student_id AND s.is_active = 'yes'
                 WHERE ss.session_id = ?
                   AND sfd.amount_detail IS NOT NULL AND sfd.amount_detail != '0'",
                [$session_id]
            )->result();

            $paid_by_ss = [];
            foreach ($deposit_rows as $dep) {
                $detail = json_decode($dep->amount_detail);
                if (!is_object($detail)) continue;
                $ssid = (int)$dep->ss_id;
                if (!isset($paid_by_ss[$ssid])) $paid_by_ss[$ssid] = 0.0;
                foreach ($detail as $payment) {
                    $paid_by_ss[$ssid] += (float)$payment->amount;
                    if (isset($payment->amount_discount)) {
                        $paid_by_ss[$ssid] += (float)$payment->amount_discount;
                    }
                }
            }

            $not_paid = [];
            foreach ($billed_rows as $row) {
                $ssid   = (int)$row->ss_id;
                $billed = (float)$row->billed;
                $paid   = isset($paid_by_ss[$ssid]) ? $paid_by_ss[$ssid] : 0.0;
                if ($paid == 0) {
                    // Strip the "ROMAN YEAR " prefix from class name for display
                    $class_parts = explode(' ', $row->class_name, 2);
                    $class_display = count($class_parts) > 1 ? trim($class_parts[1]) : $row->class_name;
                    $not_paid[] = [
                        'admission_no' => $row->admission_no,
                        'name'         => $row->student_name,
                        'class'        => $class_display,
                        'section'      => $row->section_name,
                        'billed'       => $billed,
                        'waived'       => ($billed == 0),
                    ];
                }
            }

            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'students' => $not_paid]));

        } catch (Throwable $e) {
            log_message('error', '[MCC] fees_not_paid_students_async: ' . $e->getMessage());
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to load data']));
        }
    }

    /**
     * Returns year → class breakdown of billed & collected amounts.
     * Shape: [ ['year'=>'I','billed'=>N,'collected'=>N,'classes'=>[...]], ... ]
     * "classes" entries use the class name with the "ROMAN YEAR " prefix stripped.
     */
    private function _mcc_fees_drilldown($db, $session_id)
    {
        // ── 1. Billed per class_id + fee type ─────────────────────────────────
        $billed_rows = $db->query(
            "SELECT
                ss.class_id,
                c.class                                                          AS class_full,
                SUBSTRING_INDEX(c.class, ' ', 1)                                AS year_ord,
                TRIM(REPLACE(
                    SUBSTRING(c.class, LENGTH(SUBSTRING_INDEX(c.class, ' ', 1)) + 2),
                    'YEAR ', ''))                                               AS class_display,
                ft.type                                                          AS fee_type,
                SUM(COALESCE(sfo.override_amount, fgf.amount, sfm.amount))      AS billed
             FROM student_fees_master sfm
             JOIN student_session ss  ON ss.id  = sfm.student_session_id
             JOIN students s          ON s.id   = ss.student_id AND s.is_active = 'yes'
             JOIN classes c           ON c.id   = ss.class_id
             JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
             JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
             JOIN feetype ft           ON ft.id  = fgf.feetype_id
                                      AND LOWER(ft.type) NOT IN ('advance payments')
             LEFT JOIN student_fee_overrides sfo
                    ON sfo.student_session_id    = sfm.student_session_id
                   AND sfo.fee_groups_feetype_id = fgf.id
             WHERE ss.session_id = ? AND sfm.is_active = 'yes'
             GROUP BY ss.class_id, c.class, ft.type
             ORDER BY FIELD(SUBSTRING_INDEX(c.class,' ',1),'I','II','III','IV','V'), c.class, ft.type",
            [$session_id]
        )->result();

        // ── 2. Collected per class_id + fee type (JSON parse) ──────────────────
        $deposit_rows = $db->query(
            "SELECT ss.class_id, sfd.amount_detail, ft.type AS fee_type
             FROM student_fees_deposite sfd
             JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                                         AND sfm.is_active = 'yes'
             JOIN fee_groups_feetype fgf  ON fgf.id = sfd.fee_groups_feetype_id
             JOIN feetype ft             ON ft.id  = fgf.feetype_id
             JOIN student_session ss ON ss.id = sfm.student_session_id
             JOIN students s         ON s.id  = ss.student_id AND s.is_active = 'yes'
             WHERE ss.session_id = ?
               AND sfd.amount_detail IS NOT NULL AND sfd.amount_detail != '0'",
            [$session_id]
        )->result();

        $collected_by_class = [];
        $collected_by_class_ft = [];
        foreach ($deposit_rows as $dep) {
            $detail = json_decode($dep->amount_detail);
            if (!is_object($detail)) continue;
            $cid = (int)$dep->class_id;
            $ft  = $dep->fee_type;
            if (!isset($collected_by_class[$cid])) $collected_by_class[$cid] = 0;
            if (!isset($collected_by_class_ft[$cid])) $collected_by_class_ft[$cid] = [];
            if (!isset($collected_by_class_ft[$cid][$ft])) $collected_by_class_ft[$cid][$ft] = 0;
            foreach ($detail as $payment) {
                $amt = (float)$payment->amount + (float)(isset($payment->amount_discount) ? $payment->amount_discount : 0);
                $collected_by_class[$cid] += $amt;
                $collected_by_class_ft[$cid][$ft] += $amt;
            }
        }

        // ── 3. Student payment status per student_session ─────────────────────
        $counts          = $this->_mcc_student_counts($db, $session_id);
        $counts_by_class = $counts['by_class'];

        // ── 4. Assemble year → classes tree with fee type breakdown ──────────
        $year_order = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5];
        $years      = [];
        $class_data = [];

        foreach ($billed_rows as $row) {
            $cid = (int)$row->class_id;
            $ft  = $row->fee_type;
            $b   = (float)$row->billed;

            if (!isset($class_data[$cid])) {
                $class_data[$cid] = [
                    'year_ord'      => $row->year_ord,
                    'class_display' => $row->class_display,
                    'billed'        => 0,
                    'ft_billed'     => [],
                ];
            }
            $class_data[$cid]['billed'] += $b;
            $class_data[$cid]['ft_billed'][$ft] = ($class_data[$cid]['ft_billed'][$ft] ?? 0) + $b;
        }

        foreach ($class_data as $cid => $cd) {
            $yr  = $cd['year_ord'];
            $b   = $cd['billed'];
            $col = isset($collected_by_class[$cid]) ? $collected_by_class[$cid] : 0;
            $cc  = isset($counts_by_class[$cid]) ? $counts_by_class[$cid]
                 : ['fully_paid' => 0, 'fully_paid_amt' => 0.0, 'partial' => 0, 'partial_amt' => 0.0, 'not_paid' => 0, 'not_paid_billed' => 0.0];

            $ft_breakdown = [];
            $all_fts = array_unique(array_merge(array_keys($cd['ft_billed']), array_keys($collected_by_class_ft[$cid] ?? [])));
            foreach ($all_fts as $ft) {
                $fb = $cd['ft_billed'][$ft] ?? 0;
                $fc = $collected_by_class_ft[$cid][$ft] ?? 0;
                $ft_breakdown[$ft] = ['billed' => $fb, 'collected' => $fc, 'balance' => $fb - $fc];
            }

            if (!isset($years[$yr])) {
                $years[$yr] = [
                    'year' => $yr, 'billed' => 0, 'collected' => 0, 'classes' => [],
                    'fully_paid' => 0, 'fully_paid_amt' => 0.0,
                    'partial'    => 0, 'partial_amt'    => 0.0,
                    'not_paid'   => 0, 'not_paid_billed' => 0.0,
                    'fee_type_breakdown' => [],
                ];
            }
            $years[$yr]['billed']           += $b;
            $years[$yr]['collected']        += $col;
            $years[$yr]['fully_paid']       += $cc['fully_paid'];
            $years[$yr]['fully_paid_amt']   += $cc['fully_paid_amt'];
            $years[$yr]['partial']          += $cc['partial'];
            $years[$yr]['partial_amt']      += $cc['partial_amt'];
            $years[$yr]['not_paid']         += $cc['not_paid'];
            $years[$yr]['not_paid_billed']  += $cc['not_paid_billed'];
            foreach ($ft_breakdown as $ft => $vals) {
                if (!isset($years[$yr]['fee_type_breakdown'][$ft])) {
                    $years[$yr]['fee_type_breakdown'][$ft] = ['billed' => 0, 'collected' => 0, 'balance' => 0];
                }
                $years[$yr]['fee_type_breakdown'][$ft]['billed']    += $vals['billed'];
                $years[$yr]['fee_type_breakdown'][$ft]['collected'] += $vals['collected'];
                $years[$yr]['fee_type_breakdown'][$ft]['balance']   += $vals['balance'];
            }
            $years[$yr]['classes'][] = [
                'class_id'           => $cid,
                'name'               => $cd['class_display'],
                'billed'             => $b,
                'collected'          => $col,
                'balance'            => $b - $col,
                'fully_paid'         => $cc['fully_paid'],
                'fully_paid_amt'     => $cc['fully_paid_amt'],
                'partial'            => $cc['partial'],
                'partial_amt'        => $cc['partial_amt'],
                'not_paid'           => $cc['not_paid'],
                'not_paid_billed'    => $cc['not_paid_billed'],
                'fee_type_breakdown' => $ft_breakdown,
            ];
        }

        uasort($years, function($a, $b) use ($year_order) {
            $ao = isset($year_order[$a['year']]) ? $year_order[$a['year']] : 99;
            $bo = isset($year_order[$b['year']]) ? $year_order[$b['year']] : 99;
            return $ao - $bo;
        });

        foreach ($years as &$y) {
            $y['balance'] = $y['billed'] - $y['collected'];
        }
        unset($y);

        return array_values($years);
    }

    public function upload()
    {

        $data             = array();
        $data['version']  = $this->config->item('version');
        $data['branches'] = $this->multibranch_model->get();

        if (isset($_POST['uploadBtn']) && $_POST['uploadBtn'] == 'Upload') {
            if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
                // get details of the uploaded file
                $fileTmpPath   = $_FILES['uploadedFile']['tmp_name'];
                $fileName      = $_FILES['uploadedFile']['name'];
                $fileSize      = $_FILES['uploadedFile']['size'];
                $fileType      = $_FILES['uploadedFile']['type'];
                $fileNameCmps  = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));

                // sanitize file-name
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

                // check if file has one of the following extensions
                $allowedfileExtensions = array('jpg', 'gif', 'png', 'zip', 'txt', 'xls', 'doc');

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    // directory in which the uploaded file will be moved
                    $uploadFileDir = dir_path() . '/uploads/';
                    $dest_path     = $uploadFileDir . $newFileName;
                    $this->customlib->ensureDirectoryExists($uploadFileDir);

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $message = 'File is successfully uploaded.';
                    } else {
                        $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                    }
                } else {
                    $message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
                }
            } else {
                $message = 'There is some error in the file upload. Please check the following error.<br>';
                $message .= 'Error:' . $_FILES['uploadedFile']['error'];
            }
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/multibranch/upload', $data);
        $this->load->view('layout/footer', $data);
    }

    /*
    This function is used to show all branch
    */
    public function index()
    {
        $data                                            = array();
        $data['version']                                 = $this->config->item('version');
        $data['branches']                                = $this->multibranch_model->get();
        $setting                                         = $this->setting_model->getSchoolDetail();
        
        $this->load->view('layout/header', $data);
        $this->load->view('admin/multibranch/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /*
    This function is used to load all branch datatabel
    */
    public function getlist()
    {
        $this->load->model("multibranch/multi_income_model");
        $m               = $this->multibranch_model->getlist();
        $m               = json_decode($m);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();
        if (!empty($m->data)) {
            foreach ($m->data as $branch_key => $branch_value) {
                $edit_btn   = "<button class='btn btn-default btn-xs edit_branch' data-toggle='tooltip' data-recordid=" . $branch_value->id . "    data-loading-text='<i class=" . '" fa fa-spinner fa-spin"' . "  ></i>' title='" . $this->lang->line('edit') . "' ><i class='fa fa fa-pencil'></i></button>";
                $delete_btn = "<button class='btn btn-default btn-xs delete_branch' data-toggle='tooltip' data-recordid=" . $branch_value->id . "    data-loading-text='<i class=" . '" fa fa-spinner fa-spin"' . "  ></i>' title='" . $this->lang->line('delete') . "' ><i class='fa fa fa-remove'></i></button>";

                $row   = array();
                $row[] = $branch_value->branch_name;
                $row[] = $branch_value->branch_url;
                $row[] = $edit_btn . $delete_btn;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    /*
    This function is used to switch branch
    */
    public function switchbranchlist()
    {
        $data          = array();
        $active_branch = "";
        $branch_cookie = get_cookie('branch_cookie');
        if (!is_null($branch_cookie) && $branch_cookie !== '') {

            if ($branch_cookie === 'default') {
                $active_branch = 0;
            } else {
                $active_branch = str_replace("branch_", "", $branch_cookie);
            }
        } else {
            $admin_userdata = $this->session->userdata('admin');
            if (is_array($admin_userdata) && isset($admin_userdata['db_array']['db_group'])) {
                $db_group = $admin_userdata['db_array']['db_group'];
                if ($db_group === 'default') {
                    $active_branch = 0;
                } else {
                    $active_branch = str_replace("branch_", "", $db_group);
                }
            } else {
                $active_branch = 0;
            }
        }

        $data['active_branch'] = $active_branch;
        $data['branches']      = $this->multibranch_model->get(null, 1);
        $page                  = $this->load->view('admin/multibranch/_switchbranchlist', $data, true);
        echo json_encode(array('page' => $page));
    }

    /*
    This function is used to verify database
    */
    public function verify()
    {
        $data     = array();
        $host     = $this->input->post('host_name');
        $database = $this->input->post('database');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $result   = $this->multibranch_model->verify_branch($host, $username, $password, $database);
        if (!$result) {
            $array = array('status' => '0', 'error' => '', 'message' => 'Please check Database parameter');
        } else {
            $array = array('status' => '1', 'error' => '', 'message' => 'Database connection verified');
        }

        echo json_encode($array);
    }

   /*
    This function is used to edit branch
    */
    public function edit()
    {
        $data   = array();
        $id     = $this->input->post('recordid');
        $branch = $this->multibranch_model->get($id);
        $array  = array('status' => '1', 'error' => '', 'result' => $branch);
        echo json_encode($array);
    }

    public function switch_branch() {
            $select_branch = $this->input->post('branch');
            $expire        = (60 * 60 * 24 * 365 * 2); //2 Year

            if ($select_branch === null || $select_branch === '') {
                echo json_encode(array('status' => '0', 'error' => '', 'message' => $this->lang->line('no_record_found')));
                return;
            }

            if ($select_branch != 0) {
                $branch = $this->multibranch_model->get($select_branch);
                if (empty($branch) || empty($branch->id)) {
                    echo json_encode(array('status' => '0', 'error' => '', 'message' => $this->lang->line('no_record_found')));
                    return;
                }
                $branch_group = 'branch_' . $branch->id;
                set_cookie(array(
                    'name'   => 'branch_cookie',
                    'value'  => 'branch_' . $branch->id,
                    'expire' => $expire,
                    'path'   => '/',
                ));
            } else {
                $branch_group = 'default';
                set_cookie(array(
                    'name'   => 'branch_cookie',
                    'value'  => 'default',
                    'expire' => $expire,
                    'path'   => '/',
                ));
            }

            $this->new_db = $this->load->database($branch_group, TRUE);
            if (!$this->new_db || $this->new_db->conn_id === false) {
                echo json_encode(array('status' => '0', 'error' => '', 'message' => $this->lang->line('something_went_wrong')));
                return;
            }

            if (!$this->new_db->table_exists('sch_settings')) {
                echo json_encode(array('status' => '0', 'error' => '', 'message' => $this->lang->line('something_went_wrong')));
                return;
            }

            $this->new_db->select('sch_settings.id,sch_settings.base_url,sch_settings.folder_path');
            $this->new_db->from('sch_settings');
            $query = $this->new_db->get();
            $db    = $query ? $query->row() : null;

            if (empty($db)) {
                $default_setting = $this->setting_model->getSchoolDetail();
                $db = new stdClass();
                $db->base_url    = !empty($default_setting->base_url) ? $default_setting->base_url : base_url();
                $db->folder_path = !empty($default_setting->folder_path) ? $default_setting->folder_path : FCPATH;
            }

            $admin_userdata = $this->session->userdata('admin');
            if (!is_array($admin_userdata)) {
                $admin_userdata = array();
            }
            if (!isset($admin_userdata['db_array']) || !is_array($admin_userdata['db_array'])) {
                $admin_userdata['db_array'] = array();
            }
            $admin_userdata['db_array']['db_group']    = $branch_group;
            $admin_userdata['db_array']['base_url']    = $db->base_url;
            $admin_userdata['db_array']['folder_path'] = $db->folder_path;
            $this->session->set_userdata('admin', $admin_userdata);

            //==================
            $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);

    }

    /*
    This function is used to add new branch
    */
    public function add()
    { 
        $this->form_validation->set_rules('host_name', $this->lang->line('hostname'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('branch_url', $this->lang->line('branch_url'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('database', $this->lang->line('database_name'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(                
                'host_name'     => form_error('host_name'),
                'branch_url'    => form_error('branch_url'),
                'database'      => form_error('database'),
                'username'      => form_error('username'),
                'password'      => form_error('password'),

            );
            $array = array('status' => '0', 'error' => $data);
            echo json_encode($array);
        } else {

           
            $branch_name = ($_POST['branch_name'] != "") ? $this->input->post('branch_name') : null;

            $insert_Arr = array(
                'branch_name' => $branch_name,
                'hostname'    => $this->input->post('host_name'),
                'branch_url'    => $this->input->post('branch_url'),
                'database_name'    => $this->input->post('database'),
                'username'    => $this->input->post('username'),
                'password'    => $this->input->post('password'),
            );
            $id            = $this->input->post('id');
            if ($id > 0) {
                $insert_Arr['id'] = $id;
            }

            $result = $this->multibranch_model->verify_branch($insert_Arr);
          

            if (!$result['status']) {
                $array = array('status' => '0', 'error' => array('error' => $result['message']));
            } else {

                $add_status = $this->multibranch_model->add($insert_Arr);

                if ($add_status) {

                    $response = json_decode($add_status);
                    if ($response->status) {
                        if (is_null($branch_name)) {

                            $branch      = $this->multibranch_model->getName($insert_Arr);
                            $branch_name = $branch->name;

                        }

                        $batch_update_data = array(
                            'id'          => $response->insert_id,
                            'branch_name' => $branch_name,
                           
                            'is_verified' => 1,
                        );

                        $this->multibranch_model->add($batch_update_data, true);

                        $array = array('status' => '1', 'error' => '', 'message' => 'Database connection verified');

                    } else {
                        $array = array('status' => '0', 'error' => array('error' => $response->response));
                    }
                } else {

                    $array = array('status' => '0', 'error' => array('error' => 'something went wrong Please contact to support'));
                }

            }
            echo json_encode($array);
        }
    }

    /*
    This function is used to delete branch
    */
    public function delete()
    {
        $id = $this->input->post('id');

        $branch = $this->multibranch_model->get($id);
     

        if ($this->db->database == $branch->database_name) {
            $array = array('status' => 0, 'error' => '', 'message' => 'Sorry, You can\'t delete this Database because it is already in Use.');
        } else {
            $this->multibranch_model->remove($id);
            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('delete_message'));
        }

        echo json_encode($array);
    }

    public function report()
    {
        echo "Report method is working!";
    }

    public function setting()
    {
        $data = array();
        $data['version'] = $this->config->item('version');
        $data['branches'] = $this->multibranch_model->get();
        $setting = $this->setting_model->getSchoolDetail();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/multibranch/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /*
    AJAX — Attendance section: today's student + staff attendance per institution
    */
    public function attendance_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close();

        $this->load->model('multibranch/multi_common_model');
        $branches = $this->multibranch_model->getSchoolCurrentSessions();
        $today    = date('Y-m-d');

        $summary = $this->multi_common_model->getAttendanceSummary($today, $branches);

        $rows = [];
        foreach ($branches as $db_name => $branch_info) {
            $data = isset($summary[$db_name]) ? $summary[$db_name] : [];
            $rows[] = [
                'db_name'               => $db_name,
                'name'                  => $branch_info->name,
                'student_present'       => $data['student_present']       ?? 0,
                'student_boys_present'  => $data['student_boys_present']  ?? 0,
                'student_girls_present' => $data['student_girls_present'] ?? 0,
                'student_absent'        => $data['student_absent']        ?? 0,
                'student_total'         => $data['student_total']         ?? 0,
                'staff_present'         => $data['staff_present']         ?? 0,
                'staff_absent'          => $data['staff_absent']          ?? 0,
                'staff_total'           => $data['staff_total']           ?? 0,
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'date'   => $today,
                'rows'   => $rows,
            ]));
    }

}
