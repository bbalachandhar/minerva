<?php

class Leaverequest extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->config->load("payroll");
        $this->load->library('media_storage');

        $this->load->model("staff_model");
        $this->load->model("leaverequest_model");
        $this->load->model("timetable_model"); // Load the timetable model
        $this->contract_type    = $this->config->item('contracttype');
        $this->marital_status   = $this->config->item('marital_status');
        $this->staff_attendance = $this->config->item('staffattendance');
        $this->payroll_status   = $this->config->item('payroll_status');
        $this->payment_mode     = $this->config->item('payment_mode');
        $this->status           = $this->config->item('status');
        $this->load->library('mailsmsconf');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function leaverequest()
    {
        if (!$this->rbac->hasPrivilege('approve_leave_request', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leaverequest/leaverequest');
        $leave_request         = $this->leaverequest_model->staff_leave_request();
        $data["leave_request"] = $leave_request;
        $LeaveTypes            = $this->staff_model->getLeaveType();
        $userdata              = $this->customlib->getUserData();
        $data['staff_id'] = $userdata['id'];
        $data["leavetype"]     = $LeaveTypes;
        $staffRole             = $this->staff_model->getStaffRole();
        $data["staffrole"]     = $staffRole;
        $data["status"]        = $this->status;

        // Fetch staff details, timetable, and potential substitutes
        $current_staff_id = $userdata['id'];
        $staff_details = $this->staff_model->get($current_staff_id);
        $data['current_staff_details'] = $staff_details;

        // Load Subjecttimetable_model to get staff timetable
        $this->load->model('subjecttimetable_model');

        // Fetch timetable for the current staff (for a default range, e.g., current month)
        // This is a placeholder; actual leave application might use selected leave dates
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $data['staff_timetable'] = $this->subjecttimetable_model->getStaffTimetable($current_staff_id, $start_date, $end_date);

        $potential_substitutes = [];
        if ($staff_details && $staff_details['department']) {
            $potential_substitutes = $this->staff_model->getEmployeeByDepartment($staff_details['department'], $current_staff_id);

            // Fetch Recommender (HOD) details
            $this->load->model('department_model');
            $department = $this->department_model->getDepartmentType($staff_details['department']);
            if ($department && $department['dept_head_id']) {
                $recommender_details = $this->staff_model->get($department['dept_head_id']);
                $data['recommender_info'] = $recommender_details['name'] . ' ' . $recommender_details['surname'] . ' (' . $recommender_details['designation'] . ')';
            } else {
                $data['recommender_info'] = $this->lang->line('not_assigned');
            }
        } else {
            $data['recommender_info'] = $this->lang->line('not_assigned');
        }
        $data['potential_substitutes'] = $potential_substitutes;

        // Fetch Approver details (from school settings)
        $setting = $this->setting_model->getSetting();
        if ($setting && $setting->leave_approver_id) {
            $approver_details = $this->staff_model->get($setting->leave_approver_id);
            $data['approver_info'] = $approver_details['name'] . ' ' . $approver_details['surname'] . ' (' . $approver_details['designation'] . ')';
        } else {
            $data['approver_info'] = $this->lang->line('not_assigned');
        }


        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/staffleaverequest", $data);
        $this->load->view("layout/footer", $data);
    }

    public function countLeave($id)
    {
        $lid               = $this->input->post("lid");
        $alloted_leavetype = $this->leaverequest_model->allotedLeaveType($id);

        $i    = 0;
        $html = "<select  name='leave_type' id='leave_type' class='form-control'><option value=''>" . $this->lang->line('select') . "</option>";
        $data = array();

        foreach ($alloted_leavetype as $key => $value) {
            $count_leaves[]            = $this->leaverequest_model->countLeavesData($id, $value["leave_type_id"]);
            $data[$i]['type']          = $value["type"];
            $data[$i]['id']            = $value["leave_type_id"];
            $data[$i]['alloted_leave'] = $value["alloted_leave"];
            $data[$i]['approve_leave'] = $count_leaves[$i]['approve_leave'];

            $i++;
        }

        foreach ($data as $dkey => $dvalue) {
            if (!empty($dvalue["alloted_leave"])) {
                if ($lid == $dvalue["id"]) {
                    $a = "selected";
                } else {
                    $a = "";
                }

                if ($dvalue["alloted_leave"] == "") {

                    $available = $dvalue["approve_leave"];
                } else {
                    $available = $dvalue["alloted_leave"] - $dvalue["approve_leave"];
                }
                if ($available > 0) {

                    $html .= "<option value=" . $dvalue["id"] . " $a>" . $dvalue["type"] . " (" . $available . ")" . "</option>";
                }
            }
        }

        $html .= "</select>";
        echo $html;
    }

    public function leaveStatus()
    {
        if ((!$this->rbac->hasPrivilege('approve_leave_request', 'can_edit'))) {
            access_denied();
        }

        $leave_request_id = $this->input->post("leave_request_id");
        $status           = $this->input->post("status");
        $remark           = $this->input->post("detailremark");
        
        $current_user_id = $this->customlib->getStaffID();
        $leave_request = $this->leaverequest_model->get_staff_leave($leave_request_id);

        $data = [];
        $is_recommender = ($leave_request['recommender_id'] == $current_user_id);
        $is_approver = ($leave_request['approver_id'] == $current_user_id);

        if ($is_recommender) {
            $data['recommender_status'] = $status;
            $data['recommender_remark'] = $remark;
            $data['recommender_action_date'] = date('Y-m-d');
            
            if ($status == 'disapproved') {
                $data['status'] = 'disapproved';
            }
        } elseif ($is_approver) {
            if ($leave_request['recommender_status'] == 'approved') {
                $data['approver_status'] = $status;
                $data['approver_remark'] = $remark;
                $data['approver_action_date'] = date('Y-m-d');
                $data['status'] = $status; // Final status
                if ($status == 'approved') {
                    $data['approve_date'] = date('Y-m-d');
                } else {
                    $data['approve_date'] = null;
                }
            } else {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('recommender_approval_pending'));
                echo json_encode($array);
                return;
            }
        } else {
            // Fallback for admin or other privileged users
            $data['status'] = $status;
            $data['admin_remark'] = $remark;
            if ($status != 'pending') {
                $data['approve_date'] = date('Y-m-d');
            } else {
                $data['approve_date'] = null;
            }
        }

        if (!empty($data)) {
            $this->leaverequest_model->changeLeaveStatus($data, $leave_request_id);

            // Send notification to applicant on final decision
            if ($is_approver && ($status == 'approved' || $status == 'disapproved')) {
                $applicant_details = $this->staff_model->get($leave_request['staff_id']);
                $message_to_applicant = "Dear " . $applicant_details['name'] . ",<br><br>Your leave request has been " . $status . ".<br><br>Thank you.";
                $this->mailer->send_mail($applicant_details['email'], 'Leave Request Status Updated', $message_to_applicant);
            }

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('unauthorized_action'));
            echo json_encode($array);
        }
    }

    public function remove($id, $staff_id)
    {
        $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
        $row       = $this->leaverequest_model->get_staff_leave($id);
        if ($row['document_file'] != '') {
            $this->media_storage->filedelete($row['document_file'], $uploaddir);
        }
        $this->leaverequest_model->leave_remove($id);
    }

    public function leaveRecord()
    {
        $id                   = $this->input->post("id");
        $result               = $this->staff_model->getLeaveRecord($id);
        $leave_from           = date("m/d/Y", strtotime($result->leave_from));
        $result->leavefrom    = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result->leave_from));
        $result->date         = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result->date));
        $leave_to             = date("m/d/Y", strtotime($result->leave_to));
        $result->leaveto      = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result->leave_to));
        $result->days         = $this->dateDifference($leave_from, $leave_to);
        $result->leave_status = $this->lang->line($result->status);
        
        // Get recommender and approver names
        if ($result->recommender_id) {
            $recommender = $this->staff_model->get($result->recommender_id);
            $result->recommender_name = $recommender['name'];
            $result->recommender_surname = $recommender['surname'];
        }
        if ($result->approver_id) {
            $approver = $this->staff_model->get($result->approver_id);
            $result->approver_name = $approver['name'];
            $result->approver_surname = $approver['surname'];
        }

        echo json_encode($result);
    }

    public function dateDifference($date_1, $date_2, $differenceFormat = '%a')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval  = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat) + 1;
    }

    public function addLeave()
    {
        $role         = $this->input->post("role");
        $empid        = $this->input->post("empname");
        $applied_date = $this->input->post("applieddate");
        $leavetype    = $this->input->post("leave_type");
        $reason       = $this->input->post("reason");
        $remark       = $this->input->post("remark");
        $status       = $this->input->post("addstatus");
        $request_id   = $this->input->post("leaverequestid");
        $this->form_validation->set_rules('role', $this->lang->line('role'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('empname', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('applieddate', $this->lang->line('applied_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_from_date', $this->lang->line('leave_from_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_to_date', $this->lang->line('leave_to_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_type', $this->lang->line('available_leave'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_type', $this->lang->line('leave_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('userfile', $this->lang->line('file'), 'callback_handle_upload[userfile]');

        if ($this->form_validation->run() == false) {

            $msg = array(
                'role'            => form_error('role'),
                'empname'         => form_error('empname'),
                'applieddate'     => form_error('applieddate'),
                'leavedates'      => form_error('leavedates'),
                'leave_type'      => form_error('leave_type'),
                'leave_from_date' => form_error('leave_from_date'),
                'leave_to_date'   => form_error('leave_to_date'),
                'userfile'        => form_error('userfile'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            $leavefrom    = date("Y-m-d", $this->customlib->datetostrtotime($this->input->post('leave_from_date')));
            $leaveto      = date("Y-m-d", $this->customlib->datetostrtotime($this->input->post('leave_to_date')));
            $applied_by   = $this->customlib->getStaffID();
            $leave_days   = $this->dateDifference($leavefrom, $leaveto);
            $staff_id     = $empid;
            $my_laeve     = $this->leaverequest_model->myallotedLeaveType($staff_id, $leavetype);
            $total_remain = $my_laeve['alloted_leave'] - $my_laeve['total_applied'];
            if ($total_remain >= $leave_days) {

                if (isset($_FILES["userfile"]) && !empty($_FILES['userfile']['name'])) {
                    $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
                    if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                        die("Error creating folder $uploaddir");
                    }
                    $document = $this->media_storage->fileupload("userfile", $uploaddir);
                } else {
                    $document = '';
                }
				
					if($status == 'approved'){
						$approve_date = date('Y-m-d');
					}else{
						$approve_date = null;
					}	
					
                    // Determine Recommender and Approver
                    $staff_details = $this->staff_model->get($staff_id);
                    $recommender_id = null;
                    if ($staff_details && $staff_details['department']) {
                        $this->load->model('departmenthead_model');
                        $hod = $this->departmenthead_model->get_department_head_by_department_id($staff_details['department']);
                        if ($hod) {
                            $recommender_id = $hod['staff_id'];
                        }
                    }

                    $setting = $this->setting_model->getSetting();
                    $approver_id = $setting->leave_approver_id;

                if (!empty($request_id)) {				 
					 
                    $data = array(
                        'id'              => $request_id,
                        'staff_id'        => $staff_id,
                        'date'            => date('Y-m-d', $this->customlib->datetostrtotime($applied_date)),
                        'leave_type_id'   => $leavetype,
                        'leave_days'      => $leave_days,
                        'leave_from'      => $leavefrom,
                        'leave_to'        => $leaveto,
                        'employee_remark' => $reason,
                        'status'          => $status,
                        'admin_remark'    => $remark,
                        'applied_by'      => $applied_by,
                        'document_file'   => $document,
                        'approve_date'   => $approve_date,
                        'recommender_id' => $recommender_id,
                        'approver_id' => $approver_id,
                        'recommender_status' => 'pending', // Initial status
                        'approver_status' => 'pending', // Initial status
                    );
					
                } else {
					 
                    $data = array('staff_id' => $staff_id, 'date' => date("Y-m-d", $this->customlib->datetostrtotime($applied_date)), 'leave_days' => $leave_days, 'leave_type_id' => $leavetype, 'leave_from' => $leavefrom, 'leave_to' => $leaveto, 'employee_remark' => $reason, 'status' => $status, 'admin_remark' => $remark, 'applied_by' => $applied_by, 'document_file' => $document, 'approve_date' => $approve_date,
                        'recommender_id' => $recommender_id,
                        'approver_id' => $approver_id,
                        'recommender_status' => 'pending', // Initial status
                        'approver_status' => 'pending', // Initial status
                    );
                }

                $this->leaverequest_model->addLeaveRequest($data);
                $leave_request_id = !empty($request_id) ? $request_id : $this->db->insert_id();

                // Process and save substitution data
                $substitutions_data = [];
                foreach ($this->input->post() as $key => $value) {
                    if (strpos($key, 'substitute_') === 0 && !empty($value)) {
                        $parts = explode('_', $key);
                        $date_part = $parts[1];
                        $time_from_part = $parts[2];
                        $time_to_part = $parts[3];

                        $substitutions_data[] = [
                            'substitute_staff_id' => $value,
                            'date' => $date_part,
                            'period' => $time_from_part . '-' . $time_to_part
                        ];
                    }
                }
                if (!empty($substitutions_data)) {
                    $this->leaverequest_model->addLeaveSubstitutions($leave_request_id, $substitutions_data);
                }

                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            } else {
                $msg = array(
                    'applieddate' => $this->lang->line('selected_leave_days') . " > " . $this->lang->line('available_leaves'),
                );

                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }

        }
        echo json_encode($array);
    }

    public function add_staff_leave()
    {
        $userdata     = $this->customlib->getUserData();
        $applied_date = $this->input->post("applieddate");
        $leavetype    = $this->input->post("leave_type");
        $reason       = $this->input->post("reason");
        $remark       = '';
        $status       = 'pending';
        $request_id   = $this->input->post("leaverequestid");
        $this->form_validation->set_rules('applieddate', $this->lang->line('applied_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_from_date', $this->lang->line('leave_from_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_to_date', $this->lang->line('leave_to_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('leave_type', $this->lang->line('available_leave'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('userfile', $this->lang->line('file'), 'callback_handle_upload[userfile]');

        if ($this->form_validation->run() == false) {

            $msg = array(
                'applieddate'     => form_error('applieddate'),
                'leave_from_date' => form_error('leave_from_date'),
                'leave_to_date'   => form_error('leave_to_date'),
                'leave_type'      => form_error('leave_type'),
                'userfile'        => form_error('userfile'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            $leavefrom = date("Y-m-d", $this->customlib->datetostrtotime($this->input->post('leave_from_date')));
            $leaveto   = date("Y-m-d", $this->customlib->datetostrtotime($this->input->post('leave_to_date')));

            $staff_id     = $userdata["id"];
            $applied_by   = $this->customlib->getStaffID();
            $leave_days   = $this->dateDifference($leavefrom, $leaveto);
            $my_laeve     = $this->leaverequest_model->myallotedLeaveType($staff_id, $leavetype);
            $total_remain = $my_laeve['alloted_leave'] - $my_laeve['total_applied'];

            if ($total_remain >= $leave_days) {

                if (isset($_FILES["userfile"]) && !empty($_FILES['userfile']['name'])) {
                    $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
                    if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                        die("Error creating folder $uploaddir");
                    }
                    $document = $this->media_storage->fileupload("userfile", $uploaddir);
                } else {
                    $document = '';
                }

                // Determine Recommender and Approver
                $staff_details = $this->staff_model->get($staff_id);
                $recommender_id = null;
                if ($staff_details && $staff_details['department']) {
                    $this->load->model('departmenthead_model');
                    $hod = $this->departmenthead_model->get_department_head_by_department_id($staff_details['department']);
                    if ($hod) {
                        $recommender_id = $hod['staff_id'];
                    }
                }

                $setting = $this->setting_model->getSetting();
                $approver_id = $setting->leave_approver_id;

                if (!empty($request_id)) {
                    $data = array('id' => $request_id,
                        'staff_id'         => $staff_id,
                        'date'             => date('Y-m-d', $this->customlib->datetostrtotime($applied_date)),
                        'leave_type_id'    => $leavetype,
                        'leave_days'       => $leave_days,
                        'leave_from'       => $leavefrom,
                        'leave_to'         => $leaveto,
                        'employee_remark'  => $reason,
                        'status'           => $status,
                        'admin_remark'     => $remark,
                        'applied_by'       => $applied_by,
                        'document_file'    => $document,
                        'recommender_id' => $recommender_id,
                        'approver_id' => $approver_id,
                        'recommender_status' => 'pending', // Initial status
                        'approver_status' => 'pending', // Initial status
                    );
                } else {

                    $data = array('staff_id' => $staff_id, 'date' => date("Y-m-d", $this->customlib->datetostrtotime($applied_date)), 'leave_days' => $leave_days, 'leave_type_id' => $leavetype, 'leave_from' => $leavefrom, 'leave_to' => $leaveto, 'employee_remark' => $reason, 'status' => $status, 'admin_remark' => $remark, 'applied_by' => $applied_by, 'document_file' => $document,
                        'recommender_id' => $recommender_id,
                        'approver_id' => $approver_id,
                        'recommender_status' => 'pending', // Initial status
                        'approver_status' => 'pending', // Initial status
                    );
                }

                $this->leaverequest_model->addLeaveRequest($data);
                $leave_request_id = !empty($request_id) ? $request_id : $this->db->insert_id();

                // Process and save substitution data
                $substitutions_data = [];
                foreach ($this->input->post() as $key => $value) {
                    if (strpos($key, 'substitute_') === 0 && !empty($value)) {
                        $parts = explode('_', $key);
                        $date_part = $parts[1];
                        $time_from_part = $parts[2];
                        $time_to_part = $parts[3];

                        $substitutions_data[] = [
                            'leave_request_id' => $leave_request_id,
                            'substitute_staff_id' => $value,
                            'date' => $date_part,
                            'period' => $time_from_part . ':' . $time_to_part // Store as HH:MM-HH:MM
                        ];
                    }
                }
                if (!empty($substitutions_data)) {
                    $this->leaverequest_model->addLeaveSubstitutions($leave_request_id, $substitutions_data);
                }

                // Send email notifications
                $applicant_details = $this->staff_model->get($staff_id);
                $recommender = $this->staff_model->get($recommender_id);
                $approver = $this->staff_model->get($approver_id);

                $message_to_recommender = "Dear " . $recommender['name'] . ",<br><br>A new leave request from " . $applicant_details['name'] . " " . $applicant_details['surname'] . " is awaiting your recommendation.<br><br>Thank you.";
                $this->mailer->send_mail($recommender['email'], 'New Leave Request for Recommendation', $message_to_recommender);

                $message_to_approver = "Dear " . $approver['name'] . ",<br><br>A new leave request from " . $applicant_details['name'] . " " . $applicant_details['surname'] . " has been submitted and is awaiting recommendation.<br><br>Thank you.";
                $this->mailer->send_mail($approver['email'], 'New Leave Request Submitted', $message_to_approver);

                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
    }

    public function handle_upload($str, $var)
    {

        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {

            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES[$var]['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }

            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_extension_error_uploading_image'));
                return false;
            }

            return true;
        }
        return true;

    }
    
        public function downloadleaverequestdoc($staff_id, $id)
    
        {
    
            $doc = $this->leaverequest_model->get_staff_leave($id);        
    
            $this->media_storage->filedownload($doc['document_file'], "./uploads/staff_documents/$staff_id");
    
    
    
        }
    
    
    
            public function getTimetableAndSubstitutes()
    
    
    
            {
    
    
    
                $staff_id        = $this->input->post('staff_id');
    
    
    
                $leave_from_date = $this->input->post('leave_from_date');
    
    
    
                $leave_to_date   = $this->input->post('leave_to_date');
    
    
    
        
    
    
    
                $staff_details = $this->staff_model->get($staff_id);
    
    
    
                
    
    
    
                $timetable_html = '';
    
    
    
                $substitution_html = '';
    
    
    
                $status = 'fail';
    
    
    
                $message = $this->lang->line('no_timetable_found_for_this_period');
    
    
    
        
    
    
    
                if ($staff_details) {
    
    
    
                    $this->load->model('subjecttimetable_model');
    
    
    
                    $staff_timetable = $this->subjecttimetable_model->getStaffTimetable($staff_id, $leave_from_date, $leave_to_date);
    
    
    
                    $potential_substitutes = $this->staff_model->getEmployeeByDepartment($staff_details['department'], $staff_id);
    
    
    
        
    
    
    
        
    
    
    
                    if (!empty($staff_timetable)) {
    
    
    
                        $timetable_html .= '<table class="table table-bordered table-striped">';
    
    
    
                        $timetable_html .= '<thead><tr><th>' . $this->lang->line('date') . '</th><th>' . $this->lang->line('day') . '</th><th>' . $this->lang->line('class') . '</th><th>' . $this->lang->line('section') . '</th><th>' . $this->lang->line('subject') . '</th><th>' . $this->lang->line('time') . '</th><th>' . $this->lang->line('room_no') . '</th></tr></thead>';
    
    
    
                        $timetable_html .= '<tbody>';
    
    
    
        
    
    
    
        
    
    
    
                        $substitution_html .= '<table class="table table-bordered table-striped">';
    
    
    
                        $substitution_html .= '<thead><tr><th>' . $this->lang->line('date') . '</th><th>' . $this->lang->line('class') . ' - ' . $this->lang->line('subject') . ' (' . $this->lang->line('time') . ')</th><th>' . $this->lang->line('select_substitute') . '</th></tr></thead>';
    
    
    
                        $substitution_html .= '<tbody>';
    
    
    
                        
    
    
    
                        foreach ($staff_timetable as $date => $daily_schedule) {
    
    
    
                            $day_name = date('l', strtotime($date));
    
    
    
                            if (!empty($daily_schedule)) {
    
    
    
                                foreach ($daily_schedule as $period) {
    
    
    
                                    $timetable_html .= '<tr>';
    
    
    
                                    $timetable_html .= '<td>' . $this->customlib->dateformat($date) . '</td>';
    
    
    
                                    $timetable_html .= '<td>' . $this->lang->line(strtolower($day_name)) . '</td>';
    
    
    
                                    $timetable_html .= '<td>' . $period->class . '</td>';
    
    
    
                                    $timetable_html .= '<td>' . $period->section . '</td>';
    
    
    
                                    $timetable_html .= '<td>' . $period->subject_name . ' (' . $period->subject_code . ')</td>';
    
    
    
                                    $timetable_html .= '<td>' . $period->time_from . ' - ' . $period->time_to . '</td>';
    
    
    
                                    $timetable_html .= '<td>' . $period->room_no . '</td>';
    
    
    
                                    $timetable_html .= '</tr>';
    
    
    
        
    
    
    
        
    
    
    
                                    // Substitution field generation
    
    
    
                                    $substitution_html .= '<tr>';
    
    
    
                                    $substitution_html .= '<td>' . $this->customlib->dateformat($date) . '</td>';
    
    
    
                                    $substitution_html .= '<td>' . $period->class . ' - ' . $period->subject_name . ' (' . $period->time_from . ' - ' . $period->time_to . ')</td>';
    
    
    
                                    $substitution_html .= '<td>';
    
    
    
                                    $substitution_html .= '<select name="substitute_' . $date . '_' . str_replace([' ', ':'], '_', $period->time_from) . '_' . str_replace([' ', ':'], '_', $period->time_to) . '" class="form-control">';
    
    
    
                                    $substitution_html .= '<option value="">' . $this->lang->line('select_substitute') . '</option>';
    
    
    
                                    foreach ($potential_substitutes as $substitute) {
    
    
    
                                        $substitution_html .= '<option value="' . $substitute['id'] . '">' . $substitute['name'] . ' ' . $substitute['surname'] . ' (' . $substitute['employee_id'] . ')</option>';
    
    
    
                                    }
    
    
    
                                    $substitution_html .= '</select>';
    
    
    
                                    $substitution_html .= '</td></tr>';
    
    
    
                                }
    
    
    
                            } else {
    
    
    
                                $timetable_html .= '<tr><td colspan="7">' . $this->lang->line('no_classes_scheduled_for_this_day') . '</td></tr>';
    
    
    
                                $substitution_html .= '<tr><td colspan="3">' . $this->lang->line('no_substitutions_needed_for_this_day') . '</td></tr>';
    
    
    
                            }
    
    
    
                        }
    
    
    
                        $timetable_html .= '</tbody></table>';
    
    
    
                        $substitution_html .= '</tbody></table>';
    
    
    
                        $status = 'success';
    
    
    
                        $message = $this->lang->line('timetable_fetched_successfully');
    
    
    
        
    
    
    
        
    
    
    
                    } else {
    
    
    
                        $timetable_html = '<div class="alert alert-info">' . $this->lang->line('no_timetable_found_for_this_period') . '</div>';
    
    
    
                        $substitution_html = '<div class="alert alert-info">' . $this->lang->line('no_substitutions_needed_for_this_period') . '</div>';
    
    
    
                    }
    
    
    
                } else {
    
    
    
                    $message = $this->lang->line('staff_details_not_found');
    
    
    
                }
    
    
    
        
    
    
    
        
    
    
    
                echo json_encode(['status' => $status, 'message' => $message, 'timetable_html' => $timetable_html, 'substitution_html' => $substitution_html]);
    
    
    
            }
    
    
    
            
    
    
    
            public function getRecommenderApproverInfo()
    
    
    
            {
    
    
    
                $staff_id = $this->input->post('staff_id');
    
    
    
                $recommender_info = $this->lang->line('not_assigned');
    
    
    
                $approver_info = $this->lang->line('not_assigned');
    
    
    
        
    
    
    
                $staff_details = $this->staff_model->get($staff_id);
    
    
    
        
    
    
    
                if ($staff_details) {
    
    
    
                    // Fetch Recommender (HOD) details
    
    
    
                    $this->load->model('department_model');
    
    
    
                    $department = $this->department_model->getDepartmentType($staff_details['department']);
    
    
    
                    if ($department && $department['dept_head_id']) {
    
    
    
                        $recommender_details = $this->staff_model->get($department['dept_head_id']);
    
    
    
                        $recommender_info = $recommender_details['name'] . ' ' . $recommender_details['surname'] . ' (' . $recommender_details['designation'] . ')';
    
    
    
                    }
    
    
    
        
    
    
    
                    // Fetch Approver details (from school settings)
    
    
    
                    $setting = $this->setting_model->getSetting();
    
    
    
                    if ($setting && $setting->leave_approver_id) {
    
    
    
                        $approver_details = $this->staff_model->get($setting->leave_approver_id);
    
    
    
                        $approver_info = $approver_details['name'] . ' ' . $approver_details['surname'] . ' (' . $approver_details['designation'] . ')';
    
    
    
                    }
    
    
    
                }
    
    
    
        
    
    
    
                echo json_encode([
    
    
    
                    'status' => 'success',
    
    
    
                    'recommender_info' => $recommender_info,
    
    
    
                    'approver_info' => $approver_info
    
    
    
                ]);
    
    
    
            }
    
    
    
        }
    
    
