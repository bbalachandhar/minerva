<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Enquiry extends Admin_Controller
{
    public $enquiry_status;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model("enquiry_model");
        $this->load->model("Onlineadmissioncourses_model");
        $this->config->load("payroll");
        $this->enquiry_status = $this->config->item('enquiry_status');
    }

    /**
     * Assigned dropdown source for admission enquiry screens.
     * Returns only staff whose role name is 'admission wing' (case-insensitive).
     */
    private function getAdmissionWingStaffList()
    {
        $staff_list = $this->staff_model->get();
        if (empty($staff_list) || !is_array($staff_list)) {
            return [];
        }

        return array_values(array_filter($staff_list, function ($staff) {
            $role_name = strtolower(trim((string) ($staff['user_type'] ?? '')));
            return $role_name === 'admission wing';
        }));
    }

    public function index()
    {

        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'front_office');
        $this->session->set_userdata('sub_menu', 'admin/enquiry');
        $data['class_list']     = $this->class_model->get();
        $data['department_list'] = $this->department_model->getDepartmentType();
        $data["selected_class"] = "";
        $data["selected_department"] = "";
        $data["source_select"]  = "";
        $data["selected_lead_vendor"] = "";
        $data["status"]         = "active";
        $data["last_follow_up_from"] = "";
        $data["last_follow_up_to"]   = "";
        $data['stff_list']      = $this->getAdmissionWingStaffList();
        $this->ensureLeadVendorTable();
        $data['lead_vendor_list'] = $this->db
            ->select('id, vendor_name, vendor_code')
            ->from('lead_api_vendors')
            ->order_by('vendor_name', 'ASC')
            ->get()
            ->result_array();

        $data['prefill_name']    = $this->input->get('name', TRUE);
        $data['prefill_email']   = $this->input->get('email', TRUE);
        $data['prefill_contact'] = $this->input->get('mobileno', TRUE);

        // Table rows are loaded asynchronously via dtenquirylist() SSP endpoint.
        $data['enquiry_list']   = [];
        $data['enquiry_status'] = $this->enquiry_status;
        $data['Reference']      = $this->enquiry_model->get_reference();
        $data['sourcelist']     = $this->enquiry_model->getComplaintSource();
        
        // Load course data for dropdowns
        $data['ug_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year');
        $data['ug_lateral_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral');
        $data['pg_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year');
        
        $this->load->view('layout/header');
        $this->load->view('admin/frontoffice/enquiryview', $data);
        $this->load->view('layout/footer');
    }

    private function ensureLeadVendorTable()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS lead_api_vendors (
            id INT(11) NOT NULL AUTO_INCREMENT,
            vendor_code VARCHAR(50) NOT NULL,
            vendor_name VARCHAR(100) NOT NULL,
            api_key_hash VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_by INT(11) NOT NULL DEFAULT 1,
            last_used_at DATETIME NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_vendor_code (vendor_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact', $this->lang->line('phone'), 'trim|required|xss_clean|callback_validate_phone_10digits');
        $this->form_validation->set_rules('source', $this->lang->line('source'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean|callback_validate_not_future_date');
        $this->form_validation->set_rules('follow_up_date', $this->lang->line('next_follow_up_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('course_type', 'Course Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('admission_course_id', 'Course', 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'name'    => form_error('name'),
                'contact' => form_error('contact'),
                'source'  => form_error('source'),
                'date'    => form_error('date'),
                'follow_up_date' => form_error('follow_up_date'),
                'course_type' => form_error('course_type'),
                'admission_course_id' => form_error('admission_course_id'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            $userdata   = $this->customlib->getUserData();
            $created_by = $userdata["id"];

            // Get course metadata and derive course_level
            $admission_course_id = $this->input->post('admission_course_id');
            $course_data = $this->Onlineadmissioncourses_model->getById($admission_course_id);
            $course_level = $course_data ? $course_data['course_level'] : null;
            $admission_type = $course_data ? $course_data['admission_type'] : null;

            $reference_no = 'ENQ-' . date('YmdHis') . rand(100, 999);
            $enquiry = array(
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'address'        => $this->input->post('address'),
                'state'          => $this->input->post('state'),
                'city'           => $this->input->post('city'),
                'reference'      => $this->input->post('reference'),
                'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'    => $this->input->post('description'),
                'follow_up_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
                'note'           => $this->input->post('referencer_details'),
                'source'         => $this->input->post('source'),
                'email'          => $this->input->post('email'),
                'assigned'       => IsNullOrEmptyString($this->input->post('assigned')) ? NULL : $this->input->post('assigned'),
                'class_id'       => null, // Legacy field
                'admission_course_id' => $admission_course_id,
                'course_level'   => $course_level,
                'admission_type' => $admission_type,
                'session_id'     => $this->setting_model->getOnlineAdmissionSessionId(),
                'status'         => 'active',
                'created_by'     => $created_by,
                'ref_no'         => $reference_no,
            );
            $this->enquiry_model->add($enquiry);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_delete')) {
            access_denied();
        }
        if (!empty($id)) {
            $this->enquiry_model->enquiry_delete($id);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('delete_message'));
        }
        echo json_encode($array);
    }

    public function follow_up($enquiry_id, $status, $created_by)
    {

        if (!$this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_view')) {
            access_denied();
        }
        $data['id']              = $enquiry_id;
        $data['enquiry_data']    = $this->enquiry_model->getenquiry_list($enquiry_id, $status);
        
         
        if(!empty($data['enquiry_data']['assigned'])){
            $data['assigned_staff'] = $this->staff_model->get($data['enquiry_data']['assigned']);
        
        }else{
            $data['assigned_staff'] = '';  
        } 
        $data['next_date']       = $this->enquiry_model->next_follow_up_date($enquiry_id);
        $data['created_by']      = $this->staff_model->get($created_by);
        $data['enquiry_status']  = $this->enquiry_status;
        $userdata                = $this->customlib->getUserData();
        $data['login_staff_id']  = $userdata["id"];
        $getStaffRole            = $this->customlib->getStaffRole();
        $staffrole               = json_decode($getStaffRole);
        $data['staff_role']      = $staffrole->id;
         
        $data['superadmin_rest'] = $this->session->userdata['admin']['superadmin_restriction']; 
        $this->load->view('admin/frontoffice/follow_up_modal', $data);
    }

    public function follow_up_insert()
    {
        if (!$this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('response', $this->lang->line('response'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('follow_up_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('follow_up_date', $this->lang->line('next_follow_up_date'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                'response'       => form_error('response'),
                'follow_up_date' => form_error('follow_up_date'),
                'date'           => form_error('date'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $staff_id = $this->customlib->getStaffID();

            $follow_up = array(
                'date'        => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'next_date'   => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
                'response'    => $this->input->post('response'),
                'note'        => $this->input->post('note'),
                'followup_by' => $staff_id,
                'enquiry_id'  => $this->input->post('enquiry_id'),
            );
            $this->enquiry_model->add_follow_up($follow_up);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }

        echo json_encode($array);
    }

    public function follow_up_list($id)
    {
        $data['id']             = $id;
        $data['follow_up_list'] = $this->enquiry_model->getfollow_up_list($id);
        $this->load->view('admin/frontoffice/followuplist', $data);
    }

    public function details($id, $status)
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }
        $data['source']       = $this->enquiry_model->getComplaintSource();
        $data['enquiry_type'] = $this->enquiry_model->get_enquiry_type();
        $data['Reference']    = $this->enquiry_model->get_reference();        
        $data['class_list']   = $this->enquiry_model->getclasses();        
        $data['enquiry_data'] = $this->enquiry_model->getenquiry_list($id, $status);
        $data['stff_list']    = $this->getAdmissionWingStaffList();
        
        // Load course data for dropdowns
        $data['ug_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year');
        $data['ug_lateral_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral');
        $data['pg_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year');
        
        $this->load->view('admin/frontoffice/enquiryeditmodalview', $data);
    }

    public function editpost($id)
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact', $this->lang->line('phone'), 'trim|required|xss_clean|callback_validate_phone_10digits');
        $this->form_validation->set_rules('source', $this->lang->line('source'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean|callback_validate_not_future_date');
        $this->form_validation->set_rules('follow_up_date', $this->lang->line('next_follow_up_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('admission_course_id', 'Course', 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'name'    => form_error('name'),
                'contact' => form_error('contact'),
                'source'  => form_error('source'),
                'date'    => form_error('date'),
                'follow_up_date' => form_error('follow_up_date'),
                'admission_course_id' => form_error('admission_course_id'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            // Get course metadata and derive course_level
            $admission_course_id = $this->input->post('admission_course_id');
            $course_data = $this->Onlineadmissioncourses_model->getById($admission_course_id);
            $course_level = $course_data ? $course_data['course_level'] : null;
            $admission_type = $course_data ? $course_data['admission_type'] : null;
            
            $enquiry_update = array(
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'address'        => $this->input->post('address'),
                'state'          => $this->input->post('state'),
                'city'           => $this->input->post('city'),
                'reference'      => $this->input->post('reference'),
                'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'    => $this->input->post('description'),
                'follow_up_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
                'note'           => $this->input->post('referencer_details'),
                'source'         => $this->input->post('source'),
                'email'          => $this->input->post('email'),
                'assigned'       => empty2null($this->input->post('assigned')),
                'class_id'       => null, // Legacy field
                'admission_course_id' => $admission_course_id,
                'course_level'   => $course_level,
                'admission_type' => $admission_type,
            );
            $this->enquiry_model->enquiry_update($id, $enquiry_update);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('update_message'));
        }
        echo json_encode($array);
    }

    public function follow_up_delete($follow_up_id, $enquiry_id)
    {
        if (!$this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_delete')) {
            access_denied();
        }
        $this->enquiry_model->delete_follow_up($follow_up_id);
        $data['id']             = $enquiry_id;
        $data['follow_up_list'] = $this->enquiry_model->getfollow_up_list($enquiry_id);
        $this->load->view('admin/frontoffice/followuplist', $data);
    }

    public function check_default($post_string)
    {
        return $post_string == '' ? false : true;
    }

    public function validate_not_future_date($date_input)
    {
        $date_input = trim((string) $date_input);
        if ($date_input === '') {
            return true;
        }

        $timestamp = $this->customlib->datetostrtotime($date_input);
        if (!$timestamp) {
            return true;
        }

        $input_date = date('Y-m-d', $timestamp);
        $today = date('Y-m-d');

        if ($input_date > $today) {
            $this->form_validation->set_message('validate_not_future_date', 'The {field} field cannot be a future date.');
            return false;
        }

        return true;
    }

    public function validate_phone_10digits($phone_input)
    {
        $phone_input = trim((string) $phone_input);
        if ($phone_input === '') {
            return true;
        }

        if (!preg_match('/^[0-9]{10}$/', $phone_input)) {
            $this->form_validation->set_message('validate_phone_10digits', 'The {field} field must be exactly 10 digits.');
            return false;
        }

        return true;
    }

    public function change_status()
    {
        $id     = $this->input->post("id");
        $status = $this->input->post("status");
        if (!empty($id)) {
            $data = array('id' => $id, 'status' => $status);
            $this->enquiry_model->changeStatus($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('update_message'));
        }

        echo json_encode($array);
    }

    public function check_number()
    {
        $phone_number = $this->input->post("phone_number");
        $check_number = $this->enquiry_model->check_number($phone_number);
        if (!empty($check_number)) {
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('number_is_already_exists_and_name_is') . '  ' . $check_number['name']);
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => '');
        }
        echo json_encode($array);
    }

    // -------------------------------------------------------------------------
    // DataTables Server-Side Processing endpoint
    // GET  admin/enquiry/dtenquirylist
    // -------------------------------------------------------------------------
    public function dtenquirylist()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }

        $draw       = (int) $this->input->get('draw');
        $start      = (int) $this->input->get('start');
        $length     = (int) $this->input->get('length');
        if ($length <= 0) {
            $length = 50;
        }

        $search_param = $this->input->get('search');
        $search_val   = isset($search_param['value']) ? trim((string) $search_param['value']) : '';

        $order_param = $this->input->get('order');
        $order_col   = isset($order_param[0]['column']) ? (int) $order_param[0]['column'] : 6;
        $order_dir   = isset($order_param[0]['dir'])    ? $order_param[0]['dir']           : 'desc';

        // Date inputs arrive in the school display format; convert to Y-m-d for SQL
        $raw_df  = trim((string) $this->input->get('filter_date_from'));
        $raw_dt  = trim((string) $this->input->get('filter_date_to'));
        $raw_ff  = trim((string) $this->input->get('filter_next_followup_from'));
        $raw_ft  = trim((string) $this->input->get('filter_next_followup_to'));

        $filter = [
            'status'               => $this->input->get('filter_status') ?: 'active',
            'admission_course_id'  => (int) $this->input->get('filter_admission_course_id'),
            'source'               => $this->input->get('filter_source'),
            'lead_vendor_id'       => (int) $this->input->get('filter_lead_vendor_id'),
            'is_duplicate'         => $this->input->get('filter_is_duplicate'),
            'date_from'        => !empty($raw_df) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_df)) : '',
            'date_to'          => !empty($raw_dt) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_dt)) : '',
            'next_followup_from' => !empty($raw_ff) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_ff)) : '',
            'next_followup_to'   => !empty($raw_ft) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_ft)) : '',
        ];

        $result = $this->enquiry_model->dtenquirylist_ssp($draw, $start, $length, $search_val, $order_col, $order_dir, $filter);

        $date_fmt = $this->customlib->getSchoolDateFormat();
        $fmt = function ($d) use ($date_fmt) {
            if (empty($d) || $d === '0000-00-00') {
                return '';
            }
            $ts = strtotime($d);
            return $ts ? date($date_fmt, $ts) : '';
        };

        $current_date   = date('Y-m-d');
        $enquiry_status = $this->enquiry_status;
        $rows = [];

        foreach ($result['data'] as $row) {
            $id         = (int) $row['id'];
            $status_key = strtolower(trim($row['status'] ?? ''));
            $ref_no     = !empty($row['ref_no'])
                ? htmlspecialchars($row['ref_no'], ENT_QUOTES)
                : date('Y') . str_pad($id, 6, '0', STR_PAD_LEFT);

            // Lead vendor cell
            $lv_html = '';
            if (!empty($row['duplicate_source_vendor_id'])) {
                $lv_html .= '<span class="label label-warning" title="This record was a duplicate when submitted">Duplicate</span><br>';
            }
            $lv_html .= !empty($row['lead_vendor_name']) ? htmlspecialchars($row['lead_vendor_name'], ENT_QUOTES) : '-';

            // Duplicate source cell
            $dup_html = !empty($row['duplicate_source_vendor_id'])
                ? '<span title="Original vendor">' . htmlspecialchars($row['duplicate_source_vendor_name'] ?? '', ENT_QUOTES) . '</span>'
                : '<span class="text-muted">-</span>';

            // Date values
            $enq_date     = $row['date'] ?? '';
            $last_fu      = $row['followupdate'] ?? '';
            $next_date    = !empty($row['next_date']) ? trim($row['next_date']) : '';
            $display_next = !empty($next_date) ? $next_date : ($row['follow_up_date'] ?? '');
            $next_order   = (!empty($display_next) && $display_next !== '0000-00-00') ? $display_next : '9999-12-31';

            // Cell colour class for next follow-up and status columns
            $fu_cls = '';
            $st_cls = '';
            if ($status_key === 'application_done') {
                $fu_cls = $st_cls = 'cell-alert-green';
            } elseif (!empty($display_next) && $display_next !== '0000-00-00') {
                if ($display_next === $current_date) {
                    $fu_cls = $st_cls = 'cell-alert-yellow';
                } elseif ($display_next < $current_date && $status_key === 'active') {
                    $fu_cls = $st_cls = 'cell-alert-red';
                }
            }

            $status_label = isset($enquiry_status[$status_key])
                ? htmlspecialchars($enquiry_status[$status_key], ENT_QUOTES)
                : htmlspecialchars($status_key, ENT_QUOTES);

            $rows[] = [
                $ref_no,                                                                                  // 0
                htmlspecialchars($row['name']    ?? '', ENT_QUOTES),                                      // 1
                htmlspecialchars($row['contact'] ?? '', ENT_QUOTES),                                      // 2
                htmlspecialchars($row['admission_course_name'] ?? '', ENT_QUOTES),                        // 3
                htmlspecialchars($row['source']  ?? '', ENT_QUOTES),                                      // 4
                $lv_html,                                                                                  // 5
                $dup_html,                                                                                 // 6
                '<span data-order="' . $enq_date . '">' . $fmt($enq_date) . '</span>',                    // 7
                $fmt($last_fu),                                                                            // 8
                '<span data-order="' . $next_order . '">' . $fmt($display_next !== '0000-00-00' ? $display_next : '') . '</span>', // 9
                $status_label,                                                                             // 10
                $this->_enquiry_action_html($id, $status_key, (int) ($row['created_by'] ?? 0), $row['email'] ?? '', $row['name'] ?? '', $row['contact'] ?? ''), // 11
                $fu_cls,   // 12 — hidden column, read by createdRow for follow-up cell colour
                $st_cls,   // 13 — hidden column, read by createdRow for status cell colour
            ];
        }

        $this->output->set_content_type('application/json');
        echo json_encode([
            'draw'            => $result['draw'],
            'recordsTotal'    => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data'            => $rows,
        ]);
    }

    private function _enquiry_action_html($id, $status, $created_by, $email, $name, $contact)
    {
        $staff_id = (int) $this->customlib->getStaffID();
        $s        = addslashes($status);
        $html     = "<div class='white-space-nowrap'>";

        if ($this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_view')) {
            $html .= "<a class='btn btn-default btn-xs' onclick=\"follow_up($id,'$s',$created_by);\" "
                   . "data-target='#follow_up' data-toggle='modal' title='Follow Up'>"
                   . "<i class='fa fa-phone'></i></a>";
            $conv_url = base_url('publicadmissionform?email=' . rawurlencode($email)
                . '&name=' . rawurlencode($name)
                . '&mobileno=' . rawurlencode($contact)
                . '&enquiry_id=' . $id
                . '&employee_id=' . $staff_id);
            $html .= "<a href='$conv_url' class='btn btn-default btn-xs' "
                   . "data-toggle='tooltip' title='Create Admission' target='_blank'>"
                   . "<i class='fa fa-user-plus'></i></a>";
        }
        if ($this->rbac->hasPrivilege('admission_enquiry', 'can_edit')) {
            $html .= "<a onclick=\"getRecord($id,'$s')\" class='btn btn-default btn-xs' "
                   . "data-target='#myModaledit' data-toggle='modal' title='Edit'>"
                   . "<i class='fa fa-pencil'></i></a>";
        }
        if ($this->rbac->hasPrivilege('admission_enquiry', 'can_delete')) {
            $html .= "<a href='#' class='btn btn-default btn-xs' "
                   . "onclick='delete_enquiry($id)' title='Delete'>"
                   . "<i class='fa fa-remove'></i></a>";
        }

        return $html . '</div>';
    }

    // -------------------------------------------------------------------------
    // CSV export — all filtered records (used by Excel/CSV DataTable buttons)
    // GET  admin/enquiry/exportenquiry
    // -------------------------------------------------------------------------
    public function exportenquiry()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }

        $raw_df = trim((string) $this->input->get('filter_date_from'));
        $raw_dt = trim((string) $this->input->get('filter_date_to'));
        $raw_ff = trim((string) $this->input->get('filter_next_followup_from'));
        $raw_ft = trim((string) $this->input->get('filter_next_followup_to'));

        $filter = [
            'status'               => $this->input->get('filter_status') ?: 'active',
            'admission_course_id'  => (int) $this->input->get('filter_admission_course_id'),
            'source'               => $this->input->get('filter_source'),
            'lead_vendor_id'       => (int) $this->input->get('filter_lead_vendor_id'),
            'is_duplicate'         => $this->input->get('filter_is_duplicate'),
            'date_from'        => !empty($raw_df) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_df)) : '',
            'date_to'          => !empty($raw_dt) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_dt)) : '',
            'next_followup_from' => !empty($raw_ff) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_ff)) : '',
            'next_followup_to'   => !empty($raw_ft) ? date('Y-m-d', $this->customlib->datetostrtotime($raw_ft)) : '',
        ];

        $result   = $this->enquiry_model->dtenquirylist_ssp(0, 0, 99999, '', 7, 'desc', $filter);
        $date_fmt = $this->customlib->getSchoolDateFormat();
        $fmt      = function ($d) use ($date_fmt) {
            if (empty($d) || $d === '0000-00-00') {
                return '';
            }
            $ts = strtotime($d);
            return $ts ? date($date_fmt, $ts) : '';
        };
        $es = $this->enquiry_status;

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="enquiry_export_' . date('Ymd_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Ref No', 'Name', 'Contact', 'Email', 'City', 'State', 'Source', 'Lead Vendor', 'Course', 'Enquiry Date', 'Last Follow-Up', 'Next Follow-Up', 'Status']);
        foreach ($result['data'] as $row) {
            $sk   = strtolower($row['status'] ?? '');
            $next = !empty($row['next_date']) ? $row['next_date'] : ($row['follow_up_date'] ?? '');
            fputcsv($out, [
                !empty($row['ref_no']) ? $row['ref_no'] : date('Y') . str_pad($row['id'], 6, '0', STR_PAD_LEFT),
                $row['name']    ?? '',
                $row['contact'] ?? '',
                $row['email']   ?? '',
                $row['city']    ?? '',
                $row['state']   ?? '',
                $row['source']  ?? '',
                $row['lead_vendor_name']      ?? '',
                $row['admission_course_name'] ?? '',
                $fmt($row['date']        ?? ''),
                $fmt($row['followupdate'] ?? ''),
                $fmt($next),
                isset($es[$sk]) ? $es[$sk] : $sk,
            ]);
        }
        fclose($out);
        exit;
    }

    public function bulk_meta_leads_upload()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'front_office');
        $this->session->set_userdata('sub_menu', 'admin/enquiry');

        $this->ensureLeadVendorTable();
        $data['lead_vendor_list'] = $this->db
            ->select('id, vendor_name, vendor_code')
            ->from('lead_api_vendors')
            ->where('is_active', 1)
            ->order_by('vendor_name', 'ASC')
            ->get()
            ->result_array();

        $data['preview_rows']      = [];
        $data['preview_columns']   = [];
        $data['preview_count']     = 0;
        $data['is_preview']        = false;
        $data['selected_vendor_id'] = 0;

        if ($this->input->method(TRUE) === 'POST') {
            // Clean up any previously uploaded tmp file from session
            $old_pending = $this->session->userdata('bulk_lead_import_pending');
            if (!empty($old_pending['tmp_path']) && is_file($old_pending['tmp_path'])) {
                @unlink($old_pending['tmp_path']);
            }
            $this->session->unset_userdata('bulk_lead_import_pending');

            if (!isset($_FILES['meta_leads_file']) || empty($_FILES['meta_leads_file']['name'])) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Please select a CSV file.</div>');
                redirect('admin/enquiry/bulk_meta_leads_upload');
            }

            $extension = strtolower(pathinfo($_FILES['meta_leads_file']['name'], PATHINFO_EXTENSION));
            if ($extension !== 'csv') {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Only CSV file is allowed.</div>');
                redirect('admin/enquiry/bulk_meta_leads_upload');
            }

            $upload_dir = 'uploads/tmp/';
            $this->customlib->ensureDirectoryExists($upload_dir);
            $temp_name = 'bulk_leads_' . time() . '_' . mt_rand(1000, 9999) . '.csv';
            $file_path = $upload_dir . $temp_name;

            if (!move_uploaded_file($_FILES['meta_leads_file']['tmp_name'], $file_path)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Unable to upload CSV file.</div>');
                redirect('admin/enquiry/bulk_meta_leads_upload');
            }

            $parsed = $this->parseMetaLeadsCsvForPreview($file_path);

            if (!$parsed['success']) {
                @unlink($file_path);
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $parsed['message'] . '</div>');
                redirect('admin/enquiry/bulk_meta_leads_upload');
            }

            $vendor_id = (int) $this->input->post('vendor_id');

            // Store pending import data in session so confirm step can process the file
            $this->session->set_userdata('bulk_lead_import_pending', [
                'tmp_path'  => $file_path,
                'vendor_id' => $vendor_id,
                'count'     => $parsed['count'],
            ]);

            $data['preview_rows']       = $parsed['rows'];
            $data['preview_columns']    = $parsed['columns'];
            $data['preview_count']      = $parsed['count'];
            $data['is_preview']         = true;
            $data['selected_vendor_id'] = $vendor_id;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/frontoffice/bulk_meta_leads_upload', $data);
        $this->load->view('layout/footer', $data);
    }

    public function confirm_meta_leads_import()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_add')) {
            access_denied();
        }

        if ($this->input->method(TRUE) !== 'POST') {
            redirect('admin/enquiry/bulk_meta_leads_upload');
        }

        $pending = $this->session->userdata('bulk_lead_import_pending');
        if (empty($pending) || empty($pending['tmp_path']) || !is_readable($pending['tmp_path'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Import session expired or file not found. Please upload again.</div>');
            redirect('admin/enquiry/bulk_meta_leads_upload');
        }

        $vendor_id   = (int) ($pending['vendor_id'] ?? 0);
        $vendor_name = '';
        if ($vendor_id > 0) {
            $v = $this->db->select('vendor_name')->where('id', $vendor_id)->get('lead_api_vendors')->row_array();
            $vendor_name = $v ? $v['vendor_name'] : '';
        }

        $file_path = $pending['tmp_path'];
        $handle    = fopen($file_path, 'r');
        $header    = fgetcsv($handle);
        $columns   = array_map('trim', $header);

        $userdata        = $this->customlib->getUserData();
        $created_by      = (int) $userdata['id'];
        $today           = date('Y-m-d');
        $default_followup = date('Y-m-d', strtotime('+3 days'));
        $allowed_columns = $this->db->list_fields('enquiry');

        $inserted = 0;
        $skipped  = 0;
        $seq      = 1;

        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null]) {
                continue;
            }

            // Skip blank rows
            $all_empty = true;
            foreach ($line as $cell) {
                if (trim((string) $cell) !== '') {
                    $all_empty = false;
                    break;
                }
            }
            if ($all_empty) {
                continue;
            }

            $row = [];
            foreach ($columns as $i => $col) {
                $row[$col] = isset($line[$i]) ? trim((string) $line[$i]) : '';
            }

            $name    = trim($row['name'] ?? '');
            $contact = preg_replace('/\D/', '', trim($row['contact'] ?? ''));

            if ($name === '' || $contact === '') {
                $skipped++;
                continue;
            }

            $source = trim($row['source'] ?? '');
            if ($source === '') {
                $source = $vendor_name !== '' ? $vendor_name : 'Bulk Upload';
            }

            $enq_date_raw = $row['enquiry_date'] ?? ($row['date'] ?? ($row['created date'] ?? ($row['created_date'] ?? '')));
            $enq_date = $this->_normalizeImportDate(trim($enq_date_raw), $today);

            $followup_raw = $row['follow_up_date'] ?? ($row['followup_date'] ?? ($row['follow up date']
                ?? ($row['next_follow_up_date'] ?? ($row['next follow up date'] ?? ($row['followupdate'] ?? '')))));
            $followup = $this->_normalizeImportDate(trim($followup_raw), $enq_date);

            $course_level   = trim($row['course_level'] ?? '');
            $admission_type = trim($row['admission_type'] ?? '');

            $enquiry_data = [
                'name'               => substr($name, 0, 100),
                'contact'            => substr($contact, 0, 20),
                'email'              => substr(trim($row['email'] ?? ''), 0, 50) ?: null,
                'address'            => '',
                'state'              => substr(trim($row['state'] ?? ''), 0, 100) ?: null,
                'city'               => substr(trim($row['city'] ?? ''), 0, 100) ?: null,
                'source'             => substr($source, 0, 50),
                'reference'          => substr($source, 0, 20),
                'date'               => $enq_date,
                'follow_up_date'     => $followup,
                'description'        => substr(trim($row['description'] ?? ''), 0, 500),
                'note'               => '',
                'class_id'           => null,
                'admission_course_id'=> null,
                'course_level'       => in_array($course_level, ['ug', 'pg'], true) ? $course_level : null,
                'admission_type'     => in_array($admission_type, ['first_year', 'lateral'], true) ? $admission_type : null,
                'session_id'         => $this->setting_model->getOnlineAdmissionSessionId(),
                'status'             => 'active',
                'created_by'         => $created_by,
                'ref_no'             => 'ENQ-' . date('YmdHis') . str_pad($seq, 4, '0', STR_PAD_LEFT),
            ];

            if ($vendor_id > 0 && in_array('lead_vendor_id', $allowed_columns, true)) {
                $enquiry_data['lead_vendor_id'] = $vendor_id;
            }

            // Strip columns not in enquiry table
            $enquiry_data = array_intersect_key($enquiry_data, array_flip($allowed_columns));

            $this->db->insert('enquiry', $enquiry_data);
            if ($this->db->affected_rows() > 0) {
                $inserted++;
            } else {
                $skipped++;
            }
            $seq++;
        }

        fclose($handle);
        @unlink($file_path);
        $this->session->unset_userdata('bulk_lead_import_pending');

        if ($inserted > 0) {
            $msg = '<div class="alert alert-success text-center">' . $inserted . ' lead(s) imported successfully.'
                 . ($skipped > 0 ? ' ' . $skipped . ' row(s) skipped (missing name/contact).' : '')
                 . '</div>';
        } else {
            $msg = '<div class="alert alert-warning text-center">No leads were imported. Ensure each row has a name and contact number.</div>';
        }

        $this->session->set_flashdata('msg', $msg);
        redirect('admin/enquiry');
    }

    public function download_meta_leads_template()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }

        $filename = 'meta_leads_sample_template.csv';
        $rows = [
            ['name', 'contact', 'email', 'source', 'enquiry_date', 'follow_up_date', 'city', 'state', 'course', 'course_level', 'admission_type', 'description'],
            ['Aarthi S', '9876543210', 'aarthi@example.com', 'Meta Ads', date('Y-m-d'), date('Y-m-d', strtotime('+2 days')), 'Chennai', 'Tamil Nadu', 'B.Com', 'ug', 'first_year', 'Interested in B.Com admissions'],
            ['Karthik R', '9123456780', 'karthik@example.com', 'Meta Campaign', date('Y-m-d'), date('Y-m-d', strtotime('+3 days')), 'Coimbatore', 'Tamil Nadu', 'MSc CS', 'pg', 'first_year', 'Asked for MSc course details'],
        ];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * Try to parse a date string in various formats and return YYYY-MM-DD.
     * Returns $fallback if the value is empty or unrecognisable.
     */
    private function _normalizeImportDate($val, $fallback)
    {
        if ($val === '' || $val === null) {
            return $fallback;
        }

        // Already YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }

        // DD/MM/YYYY or D/M/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // DD-MM-YYYY or D-M-YYYY
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // MM/DD/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[1], $m[2]);
        }

        // YYYY/MM/DD
        if (preg_match('/^(\d{4})\/(\d{2})\/(\d{2})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // Try PHP strtotime as last resort
        $ts = strtotime($val);
        if ($ts !== false && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return $fallback;
    }

    private function parseMetaLeadsCsvForPreview($file_path)
    {
        if (!is_readable($file_path)) {
            return ['success' => false, 'message' => 'Uploaded file is not readable.'];
        }

        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'Unable to open uploaded CSV file.'];
        }

        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            fclose($handle);
            return ['success' => false, 'message' => 'CSV file is empty.'];
        }

        $columns = array_map(function ($value) {
            return trim((string) $value);
        }, $header);

        $rows = [];
        $count = 0;
        while (($line = fgetcsv($handle)) !== false) {
            if ($line === [null] || $line === false) {
                continue;
            }

            $empty_line = true;
            foreach ($line as $cell) {
                if (trim((string) $cell) !== '') {
                    $empty_line = false;
                    break;
                }
            }
            if ($empty_line) {
                continue;
            }

            $count++;
            if (count($rows) < 20) {
                $assoc = [];
                foreach ($columns as $index => $column_name) {
                    $assoc[$column_name] = isset($line[$index]) ? trim((string) $line[$index]) : '';
                }
                $rows[] = $assoc;
            }
        }
        fclose($handle);

        if ($count === 0) {
            return ['success' => false, 'message' => 'No data rows found in CSV.'];
        }

        return [
            'success' => true,
            'columns' => $columns,
            'rows' => $rows,
            'count' => $count,
        ];
    }

}
