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
        $all_leave_request = $this->leaverequest_model->staff_leave_request();
        $filtered_leave_request = [];
        $current_user_id = $this->customlib->getStaffID();
        $role = json_decode($this->customlib->getStaffRole());

        // Super Admin (Role 7) sees all
        if ($role->id == 7) {
            $filtered_leave_request = $all_leave_request;
        } else {
            foreach ($all_leave_request as $request) {
                $show = false;
                
                // 1. Applier: Always see own
                if ($request['staff_id'] == $current_user_id) {
                    $show = true;
                }
                // 2. Recommender: See requests where they are recommender
                elseif ($request['recommender_id'] == $current_user_id) {
                    $show = true;
                }
                // 3. Approver: See requests where they are approver AND status is recommended/approved
                elseif ($request['approver_id'] == $current_user_id) {
                    // Strict requirement: "approver should only see only if a leave is recommended"
                    if ($request['recommender_status'] == 'recommended' || $request['recommender_status'] == 'approved') {
                        $show = true;
                    }
                }
                
                if ($show) {
                    $filtered_leave_request[] = $request;
                }
            }
        }
        $data["leave_request"] = $filtered_leave_request;
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
        if ($setting && isset($setting->leave_approver_id)) {
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
        $all_leavetypes    = $this->staff_model->getLeaveType();

        $html = "<select  name='leave_type' id='leave_type' class='form-control'><option value=''>" . $this->lang->line('select') . "</option>";
        
        $leave_types_to_display = array();

        // Create a map of allotted leaves for easier lookup
        $allotted_map = array();
        foreach($alloted_leavetype as $leave){
            $allotted_map[$leave['leave_type_id']] = $leave;
        }

        if (!empty($all_leavetypes)) {
            foreach ($all_leavetypes as $key => $value) {
                
                $is_allotted = isset($allotted_map[$value['id']]);
                $is_lop = isset($value['is_lop']) && $value['is_lop'] == 1;

                if ($is_lop) {
                    if ($is_allotted) {
                        // It's LOP, but has an allotted amount for tracking. Show it, with count (0 or negative is fine).
                        $allotted_leave_days = $allotted_map[$value['id']]['alloted_leave'];
                        $count_leaves = $this->leaverequest_model->countLeavesData($id, $value["id"]);
                        $approve_leave = !empty($count_leaves['approve_leave']) ? $count_leaves['approve_leave'] : 0;
                        $available = $allotted_leave_days - $approve_leave;

                        $leave_types_to_display[$value['id']] = array(
                            'id' => $value['id'],
                            'type' => $value['type'],
                            'display' => $value['type'] . " (" . $available . ")"
                        );
                    } else {
                        // It's a LOP leave not specifically allotted, so it's unlimited
                         $leave_types_to_display[$value['id']] = array(
                            'id' => $value['id'],
                            'type' => $value['type'],
                            'display' => $value['type']
                        );
                    }
                } else {
                    // Regular allotted leave
                    if ($is_allotted) {
                        $allotted_leave_days = $allotted_map[$value['id']]['alloted_leave'];
                        $count_leaves = $this->leaverequest_model->countLeavesData($id, $value["id"]);
                        $approve_leave = !empty($count_leaves['approve_leave']) ? $count_leaves['approve_leave'] : 0;
                        $available = $allotted_leave_days - $approve_leave;

                        if ($available >= 0) {
                            $leave_types_to_display[$value['id']] = array(
                                'id' => $value['id'],
                                'type' => $value['type'],
                               'display' => $value['type'] . " (" . $available . ")"
                            );
                        }
                    } else { // Not LOP and not allotted
                        // Display with 0 available
                        $leave_types_to_display[$value['id']] = array(
                            'id' => $value['id'],
                            'type' => $value['type'],
                           'display' => $value['type'] . " (0)"
                        );
                    }
                }
            }
        }

        // Generate HTML
        if (!empty($leave_types_to_display)) {
            foreach ($leave_types_to_display as $leave) {
                $selected = ($lid == $leave["id"]) ? "selected" : "";
                $html .= "<option value='" . $leave["id"] . "' " . $selected . ">" . $leave["display"] . "</option>";
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
            // Map form status to DB ENUM for recommender
            if ($status == 'approved') {
                $data['recommender_status'] = 'recommended';
            } elseif ($status == 'disapproved') {
                $data['recommender_status'] = 'rejected';
            } else {
                $data['recommender_status'] = $status;
            }
            
            $data['recommender_remark'] = $remark;
            $data['recommender_action_date'] = date('Y-m-d');
            
            if ($status == 'disapproved') {
                $data['status'] = 'disapproved';
            } elseif ($status == 'approved' && $leave_request['status'] == 'pending') {
                // If recommender approves, and overall status is still pending, mark it as recommended
                $data['status'] = 'recommended';
                
                // Send notification to approver
                $approver_id = $leave_request['approver_id'];
                if ($approver_id) {
                    $approver_details = $this->staff_model->get($approver_id);
                    $applicant_details = $this->staff_model->get($leave_request['staff_id']);
                    if ($approver_details && isset($approver_details['email'])) {
                        $message_to_approver = "Dear " . $approver_details['name'] . ",<br><br>A leave request from " . $applicant_details['name'] . " " . $applicant_details['surname'] . " has been recommended and is awaiting your final approval.<br><br>Thank you.";
                        $this->mailer->send_mail($approver_details['email'], 'Leave Request Recommended for Approval', $message_to_approver);
                    }
                }
            }
        } elseif ($is_approver) {
            if ($leave_request['recommender_status'] == 'approved' || $leave_request['recommender_status'] == 'recommended') {
                // Map form status to DB ENUM for approver
                if ($status == 'disapproved') {
                    $data['approver_status'] = 'rejected';
                } else {
                    $data['approver_status'] = $status;
                }
                
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
            if ($leave_request['status'] == 'approved' || $leave_request['status'] == 'disapproved') {
                $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('finalized_record_cannot_be_modified'));
                echo json_encode($array);
                return;
            }
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
        $row = $this->leaverequest_model->get_staff_leave($id);
        if ($row['status'] == 'pending') {
            $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
            if ($row['document_file'] != '') {
                $this->media_storage->filedelete($row['document_file'], $uploaddir);
            }
            $this->leaverequest_model->leave_remove($id);
        } else {
            log_message('error', 'Attempt to delete non-pending leave request ID: ' . $id);
        }
    }

    public function leaveRecord()
    {
        $id                   = $this->input->post("id");
        $result               = $this->staff_model->getLeaveRecord($id);

        // Self-Healing: If recommender_id is missing, try to fix it based on current staff department
        if (empty($result->recommender_id)) {
            $staff_details = $this->staff_model->get($result->staff_id);
            if ($staff_details && $staff_details['department']) {
                $this->load->model('department_model');
                $department = $this->department_model->getDepartmentType($staff_details['department']);
                $new_recommender_id = null;
                
                if ($department && !empty($department['dept_head_id'])) {
                    $new_recommender_id = $department['dept_head_id'];
                }
                
                // Fallback to Approver
                if (empty($new_recommender_id) && !empty($result->approver_id)) {
                    $new_recommender_id = $result->approver_id;
                }

                if (!empty($new_recommender_id)) {
                    // Update the database
                    $this->db->where('id', $id)->update('staff_leave_request', ['recommender_id' => $new_recommender_id]);
                    // Update the result object so the UI works immediately
                    $result->recommender_id = $new_recommender_id;
                    $recommender = $this->staff_model->get($new_recommender_id); // Fetch new details for UI
                    if($recommender){
                         $result->recommender_name = $recommender['name'];
                         $result->recommender_surname = $recommender['surname'];
                    }
                }
            }
        }

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

        if ($result->recommender_status) {
            $result->recommender_status_text = $this->lang->line(strtolower($result->recommender_status));
        }
        if ($result->approver_status) {
            $result->approver_status_text = $this->lang->line(strtolower($result->approver_status));
        }

        if ($result->alternative_teacher_id) {
            $alt_teacher = $this->staff_model->get($result->alternative_teacher_id);
            if ($alt_teacher) {
                $result->alternative_teacher_name = $alt_teacher['name'];
                $result->alternative_teacher_surname = $alt_teacher['surname'];
                $result->alternative_teacher_employee_id = $alt_teacher['employee_id'];
            }
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
            log_message('error', 'addLeave called');
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
            $this->form_validation->set_rules('alternative_teacher_id', $this->lang->line('alternative_teacher'), 'trim|xss_clean');
            $this->form_validation->set_rules('reason', $this->lang->line('reason'), 'trim|xss_clean'); // Added rule for cleaning, but not required
    
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
                log_message('error', 'Validation failed in addLeave: ' . json_encode($msg));
    
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            } else {
                log_message('error', 'Validation succeeded in addLeave');
    
                $alternative_teacher_id = $this->input->post('alternative_teacher_id');
                $leavefrom    = date("Y-m-d", $this->customlib->datetostrtotime($this->input->post('leave_from_date')));
                $leaveto      = date("Y-m-d", $this->customlib->datetostrtotime($this->input->post('leave_to_date')));
                $applied_by   = $this->customlib->getStaffID();
                $leave_days   = $this->dateDifference($leavefrom, $leaveto);
                $staff_id     = $empid;
    
                            $leave_type_details = $this->staff_model->getLeaveType($leavetype);
                            $is_lop_leave = (isset($leave_type_details['is_lop']) && $leave_type_details['is_lop'] == 1);
                            
                            log_message('error', "Checking balance: Staff ID: $staff_id, Leave Type: $leavetype, Is LOP: " . ($is_lop_leave ? 'Yes' : 'No') . ", Leave Days Requested: $leave_days");
                
                            $my_laeve     = $this->leaverequest_model->myallotedLeaveType($staff_id, $leavetype);
                            $alloted_leave = isset($my_laeve['alloted_leave']) ? $my_laeve['alloted_leave'] : 0;
                            $total_applied = isset($my_laeve['total_applied']) ? $my_laeve['total_applied'] : 0;
                            $total_remain = $alloted_leave - $total_applied;
                            
                            log_message('error', "Balance details: Allotted: $alloted_leave, Applied: $total_applied, Remaining: $total_remain");
                
                            if ($is_lop_leave || $total_remain >= $leave_days) {                    if (isset($_FILES["userfile"]) && !empty($_FILES['userfile']['name'])) {
                        $uploaddir = './uploads/staff_documents/' . $staff_id . '/';
                        if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                            log_message('error', 'Failed to create upload directory: ' . $uploaddir);
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
                            $this->load->model('department_model');
                            $department = $this->department_model->getDepartmentType($staff_details['department']);
                            if ($department && !empty($department['dept_head_id'])) {
                                $recommender_id = $department['dept_head_id'];
                            }
                        }
    
                        $setting = $this->setting_model->getSetting();
                        $approver_id = isset($setting->leave_approver_id) ? $setting->leave_approver_id : null;

                        // Fallback: If no recommender found (e.g. no Dept Head), use Approver as Recommender
                        if (empty($recommender_id) && !empty($approver_id)) {
                            $recommender_id = $approver_id;
                            log_message('error', 'No Recommender (HOD) found. Falling back to Approver ID: ' . $approver_id);
                        }
    
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
                            'alternative_teacher_id' => $alternative_teacher_id,
                        );
    					
                    } else {
    					 
                        $data = array('staff_id' => $staff_id, 'date' => date("Y-m-d", $this->customlib->datetostrtotime($applied_date)), 'leave_days' => $leave_days, 'leave_type_id' => $leavetype, 'leave_from' => $leavefrom, 'leave_to' => $leaveto, 'employee_remark' => $reason, 'status' => $status, 'admin_remark' => $remark, 'applied_by' => $applied_by, 'document_file' => $document, 'approve_date' => $approve_date,
                            'recommender_id' => $recommender_id,
                            'approver_id' => $approver_id,
                            'recommender_status' => 'pending', // Initial status
                            'alternative_teacher_id' => $alternative_teacher_id,
                        );
                    }
    
                    log_message('error', 'Calling addLeaveRequest with data: ' . json_encode($data));
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
                                    log_message('error', 'Sending success response from addLeave');
                                } else {
                                    $msg = array(
                                        'applieddate' => "Application Failed: You do not have enough leave balance for this request. Please check your available leaves or contact HR.",
                                    );
                                    log_message('error', 'Leave balance insufficient. Request denied. Staff ID: ' . $staff_id . ', Leave Type: ' . $leavetype);
                                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                                }    
            }
            echo json_encode($array);
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
            public function getTimetableAndSubstitutes()    
            {
                $staff_id        = $this->input->post('staff_id');
                $leave_from_date = $this->input->post('leave_from_date');
                $leave_to_date   = $this->input->post('leave_to_date');
                
                log_message('error', "getTimetableAndSubstitutes called: Staff: $staff_id, From: $leave_from_date, To: $leave_to_date");

                // Convert dates to Y-m-d for database model compatibility
                $leave_from_db = date("Y-m-d", $this->customlib->datetostrtotime($leave_from_date));
                $leave_to_db   = date("Y-m-d", $this->customlib->datetostrtotime($leave_to_date));

                $staff_details = $this->staff_model->get($staff_id);
                $timetable_html = '';
                $substitution_html = '';
                $status = 'fail';
                $message = $this->lang->line('no_timetable_found_for_this_period');
   
                if ($staff_details) {
                    $this->load->model('subjecttimetable_model');
                    $staff_timetable = $this->subjecttimetable_model->getStaffTimetable($staff_id, $leave_from_db, $leave_to_db);
                    $potential_substitutes = $this->staff_model->getEmployeeByDepartment($staff_details['department'], $staff_id);

    
                    if (!empty($staff_timetable)) {
 
                        $timetable_html .= '<table class="table table-bordered table-striped">';
            $timetable_html .= '<thead><tr><th>' . $this->lang->line('date') . '</th><th>' . $this->lang->line('day') . '</th><th>' . $this->lang->line('class') . '</th><th>' . $this->lang->line('section') . '</th><th>' . $this->lang->line('subject') . '</th><th>' . $this->lang->line('time') . '</th><th>' . $this->lang->line('room_no') . '</th></tr></thead>';
               $timetable_html .= '<tbody>';
    
      
                        $substitution_html .= '<table class="table table-bordered table-striped">';
      
                        $substitution_html .= '<thead><tr><th>' . $this->lang->line('date') . '</th><th>' . $this->lang->line('class') . ' - ' . $this->lang->line('subject') . '</th><th>' . $this->lang->line('select_substitute') . '</th></tr></thead>';
   
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
                                    $substitution_html .= '<label class="control-label">' . $this->lang->line('substitute') . ':</label>';
                                    $substitution_html .= '<select name="substitute_' . $date . '_' . str_replace([' ', ':'], '_', $period->time_from) . '_' . str_replace([' ', ':'], '_', $period->time_to) . '" class="form-control" aria-label="Select substitute for ' . $period->class . ' - ' . $period->subject_name . ' from ' . $period->time_from . ' to ' . $period->time_to . '">';
    
 
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
 
                    $department = $this->department_model->get_departments($staff_details['department']);
  
                    if ($department && $department['dept_head_id']) {
 
                        $recommender_details = $this->staff_model->get($department['dept_head_id']);
    
 
                        $recommender_info = $recommender_details['name'] . ' ' . $recommender_details['surname'] . ' (' . $recommender_details['designation'] . ')';

    
                    }

                    // Fetch Approver details (from school settings)
 
                    $setting = $this->setting_model->getSetting();
  
                    if ($setting && isset($setting->leave_approver_id)) {
 
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
    public function recommender_leave_requests()
    {
        if (!$this->rbac->hasPrivilege('approve_leave_request', 'can_view') || $this->sch_setting_detail->institution_type != 'college') {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'HR/staff/leaverequest');

        $current_user_id = $this->customlib->getStaffID();
        $leave_requests_for_recommender = $this->leaverequest_model->get_recommender_pending_leave_requests($current_user_id);
        
        $data["leave_request"] = $leave_requests_for_recommender;
        $data["status"] = $this->status; // Status array from payroll config
        $data['sch_setting_detail'] = $this->sch_setting_detail; // Pass sch_setting_detail to the view

        $LeaveTypes            = $this->staff_model->getLeaveType();
        $data["leavetype"]     = $LeaveTypes;
        $staffRole             = $this->staff_model->getStaffRole();
        $data["staffrole"]     = $staffRole;

        $userdata              = $this->customlib->getUserData();
        $data['staff_id'] = $userdata['id'];
        $staff_details = $this->staff_model->get($userdata['id']);
        $data['current_staff_details'] = $staff_details;

        $potential_substitutes = [];
        if ($staff_details && $staff_details['department']) {
            $potential_substitutes = $this->staff_model->getEmployeeByDepartment($staff_details['department'], $current_user_id);
        }
        $data['potential_substitutes'] = $potential_substitutes;

        if ($staff_details && $staff_details['department']) {
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

        $setting = $this->setting_model->getSetting();
        if ($setting && isset($setting->leave_approver_id)) {
            $approver_details = $this->staff_model->get($setting->leave_approver_id);
            $data['approver_info'] = $approver_details['name'] . ' ' . $approver_details['surname'] . ' (' . $approver_details['designation'] . ')';
        } else {
            $data['approver_info'] = $this->lang->line('not_assigned');
        }

        $this->load->view("layout/header", $data);
        $this->load->view("admin/staff/staffleaverequest", $data);
        $this->load->view("layout/footer", $data);
    }
}