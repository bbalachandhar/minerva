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

        $month = date("F", strtotime('-1 month'));
        $year  = date("Y", strtotime('-1 month'));

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
        $inventory       = $this->multi_common_model->getInventorySummary($branches);

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

    /*
    AJAX — Academics section data (library, admissions, alumni)
    */
    public function academics_async()
    {
        if (!$this->rbac->hasPrivilege('multi_branch_overview', 'can_view')) {
            access_denied();
        }
        session_write_close(); // release session lock so parallel AJAX calls don't queue

        $this->load->model("multibranch/multi_common_model");
        $branches = $this->multibranch_model->getSchoolCurrentSessions();

        $books           = $this->multi_common_model->getBooks($branches);
        $members         = $this->multi_common_model->getLibararyMembers($branches);
        $issued          = $this->multi_common_model->getLibararyBookIssued($branches);
        $offline_adm     = $this->multi_common_model->getOfflineStudentAdmissions($branches);
        $online_adm      = $this->multi_common_model->getOnlineStudentAdmissions($branches);
        $alumni          = $this->multi_common_model->getAlumniStudents($branches);

        $rows = [];
        foreach ($branches as $db_name => $branch_info) {
            $rows[] = [
                'db_name'          => $db_name,
                'name'             => $branch_info->name,
                'session'          => $branch_info->session,
                'total_books'      => $books[$db_name]['total_books'],
                'library_members'  => $members[$db_name]['total_members'],
                'book_issued'      => $issued[$db_name]['total_book_issued'],
                'offline_admission'=> $offline_adm[$db_name]['offline_admission'],
                'online_admission' => $online_adm[$db_name]['online_admission'],
                'total_alumni'     => $alumni[$db_name]['total_alumni_student'],
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'rows' => $rows]));
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

            list($total_fees, $total_paid) = $this->_mcc_fees_summary($db, $session_id);
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
            "SELECT SUM(COALESCE(sfo.override_amount, fgf.amount)) AS total_billed
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
            "SELECT sfd.amount_detail
             FROM student_fees_deposite sfd
             JOIN student_fees_master sfm ON sfm.id = sfd.student_fees_master_id
                                         AND sfm.is_active = 'yes'
             JOIN student_session ss ON ss.id = sfm.student_session_id
             JOIN students s         ON s.id  = ss.student_id AND s.is_active = 'yes'
             WHERE ss.session_id = ?
               AND sfd.amount_detail IS NOT NULL AND sfd.amount_detail != '0'",
            [$session_id]
        )->result();

        $total_collected = 0;
        foreach ($deposits as $dep) {
            $detail = json_decode($dep->amount_detail);
            if (!is_object($detail)) continue;
            foreach ($detail as $payment) {
                $total_collected += (float)$payment->amount;
                if (isset($payment->amount_discount)) {
                    $total_collected += (float)$payment->amount_discount;
                }
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

        return [$total_billed, $total_collected];
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
