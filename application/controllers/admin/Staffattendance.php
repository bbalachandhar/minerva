<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Staffattendance extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
        $this->config->load("mailsms");
        $this->config->load("payroll");
        $this->load->library('mailsmsconf');
        $this->config_attendance = $this->config->item('attendence');
        $this->staff_attendance  = $this->config->item('staffattendance');
        $this->load->model("staffattendancemodel");
        $this->load->model("staff_model");
        $this->load->model("payroll_model"); 
        $this->load->model("staffAttendaceSetting_model"); 
        $this->load->model("biometric_device_model"); // Load the new model
        $this->load->model("staff_biometric_punches_model"); // Load the new model
        $this->load->library('biometric_api_client'); // Load the new library
        $this->load->model("staffpermission_model");
    }

    public function index(){
        if (!($this->rbac->hasPrivilege('staff_attendance', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staffattendance');
        $data['title']        = 'Staff Attendance List';
        $data['title_list']   = 'Staff Attendance List';
        $user_type            = $this->staff_model->getStaffRole();
        $data['sch_setting']  = $this->setting_model->getSetting();
        $data['classlist']    = $user_type;
        $data['class_id']     = "";
        $data['section_id']   = "";
        $data['date']         = "";
        $user_type_id         = $this->input->post('user_id');
        $data["user_type_id"] = $user_type_id;
        $staff_settings           = $this->staffAttendaceSetting_model->getRoleWiseAttendanceSetting($user_type_id);
        $data['staff_settings']   = $staff_settings;   

        if (!(isset($user_type_id))) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/staffattendance/staffattendancelist', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $user_type            = $this->input->post('user_id');
            $date                 = $this->input->post('date');
            $user_list            = $this->staffattendancemodel->get();
            $data['userlist']     = $user_list;
            $data['class_id']     = $user_list;
            $data['user_type_id'] = $user_type_id;
            $data['section_id']   = "";
            $data['date']         = $date;
            $is_first_time_attendance      = true;
            $search               = $this->input->post('search');
            $holiday              = $this->input->post('holiday');
            $this->session->set_flashdata('msg', '');
            if ($search == "saveattendence") {
            
                $user_type_ary       = $this->input->post('student_session');
                $attendance_array=[];
                $absent_staff_list=[];
                foreach ($user_type_ary as $key => $value) {
                    
                    $attendencetype = $this->input->post('attendencetype' . $value);
                  $in_time    =   $this->input->post("in_time_" . $value);
                  $out_time   =   $this->input->post("out_time_" . $value);

                    if((!isset($in_time) || $in_time=="") && (!isset($out_time) || $out_time=="")){
                        $in_time  = null;
                        $out_time = null;
                    }else{
                        $in_time=date('H:i:s', strtotime($this->input->post("in_time_" . $value)));
                        $out_time=date('H:i:s', strtotime($this->input->post("out_time_" . $value)));
                    }

                    $absent_config = $this->staff_attendance['absent'];
                
                    if ($attendencetype == $absent_config) {
                        $absent_staff_list[] = $value;
                    }

                    $attendance_array[] = array(                       
                        'staff_id'                 => $value,
                        'staff_attendance_type_id' => $this->input->post('attendencetype' . $value),
                        'remark'                   => $this->input->post("remark" . $value),
                        'in_time'                  => $in_time,
                        'out_time'                 => $out_time, 
                        'date'                     => date('Y-m-d', $this->customlib->datetostrtotime($date)),
                        'updated_at'               => date('Y-m-d', $this->customlib->datetostrtotime($date)),
                    );
                }
               
                $this->staffattendancemodel->addorUpdate($attendance_array);
                //added mail sms code //
                if (!empty($absent_staff_list)) {
                    $this->mailsmsconf->mailsms('staff_absent_attendence', $absent_staff_list, $date);
                }
                if (!empty($present_staff_list)) {
                    $this->mailsmsconf->mailsms('staff_present_attendence', $present_staff_list, $date);
                }
                // added mail sms code //

                $absent_config = $this->config_attendance['absent'];
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                redirect('admin/staffattendance/index');
            }

            $attendencetypes             = $this->attendencetype_model->getStaffAttendanceType();
            $data['attendencetypeslist'] = $attendencetypes;        
            $resultlist                  = $this->staffattendancemodel->searchAttendenceUserType($user_type, date('Y-m-d', $this->customlib->datetostrtotime($date)));
            
            $this->load->model('attendance_model'); // Load the new attendance model

            if (!empty($resultlist)) {
                foreach ($resultlist as $key => &$value) { // Use & to modify $value by reference
                    if (!IsNullOrEmptyString($value['staff_attendance_type_id'])) {
                        $is_first_time_attendance = false;
                    }
                    // Fetch raw biometric punches for each staff member
                    $staff_id = $value['staff_id'];
                    $current_date = date('Y-m-d', $this->customlib->datetostrtotime($date));
                    $raw_punches = $this->attendance_model->get_raw_biometric_punches_by_staff_id_and_date($staff_id, $current_date);
                    $value['biometric_raw_punches'] = $raw_punches;
                }
            }
            $data['is_first_time_attendance']  = $is_first_time_attendance;
            $data['resultlist']  = $resultlist;

            $this->load->view('layout/header', $data);
            $this->load->view('admin/staffattendance/staffattendancelist', $data);
            $this->load->view('layout/footer', $data);
        }
    }    

    public function monthAttendance($st_month, $no_of_months, $emp)
    {
        $this->load->model("payroll_model");
        $record = array();
        $r     = array();
        $month = date('m', strtotime($st_month));
        $year  = date('Y', strtotime($st_month));
        foreach ($this->staff_attendance as $att_key => $att_value) {
            $s = $this->payroll_model->count_attendance_obj($month, $year, $emp, $att_value);
            $r[$att_key] = $s;
        }

        $record[$emp] = $r;
        return $record;
    }

    public function profileattendance()
    {
        $monthlist             = $this->customlib->getMonthDropdown();
        $startMonth            = $this->setting_model->getStartMonth();
        $data["monthlist"]     = $monthlist;
        $data['yearlist']      = $this->staffattendancemodel->attendanceYearCount();
        $staffRole             = $this->staff_model->getStaffRole();
        $data["role"]          = $staffRole;
        $data["role_selected"] = "";
        $j                     = 0;
        for ($i = 1; $i <= 31; $i++) {
            $att_date = sprintf("%02d", $i);
            $attendence_array[] = $att_date;
            foreach ($monthlist as $key => $value) {
                $datemonth       = date("m", strtotime($value));
                $att_dates       = date("Y") . "-" . $datemonth . "-" . sprintf("%02d", $i);
                $date_array[]    = $att_dates;
                $res[$att_dates] = $this->staffattendancemodel->searchStaffattendance($att_dates, $staff_id = 8);
            }

            $j++;
        }

        $data["resultlist"]       = $res;
        $data["attendence_array"] = $attendence_array;
        $data["date_array"]       = $date_array;
        $this->load->view("layout/header");
        $this->load->view("admin/staff/staffattendance", $data);
        $this->load->view("layout/footer");
    }

    public function sync_biometric_attendance()
    {
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staffattendance/sync_biometric_attendance');
        $data['title'] = $this->lang->line('sync_biometric_attendance');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('full_day_present_threshold', 'Full Day Present Threshold', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $data['last_sync_datetime'] = $this->setting_model->getSetting()->last_biometric_sync_datetime;
            $data['full_day_present_threshold'] = $this->setting_model->getSetting()->full_day_present_threshold;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/staffattendance/biometric_sync_settings', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $full_day_present_threshold = $this->input->post('full_day_present_threshold');
            $this->setting_model->update(array('full_day_present_threshold' => $full_day_present_threshold));

            $active_device = $this->biometric_device_model->getActiveDevice();

            if (empty($active_device)) {
                log_message('error', 'Staffattendance::sync_biometric_attendance - No active biometric device. Loading biometric_sync_settings view.');
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">No active biometric device configured.</div>');
                redirect('admin/staffattendance/index');
            }

            $this->biometric_api_client->initialize([
                'api_endpoint' => $active_device['api_endpoint'],
                'serial_number' => $active_device['serial_number'],
                'username' => $active_device['username'],
                'password' => $active_device['password'],
            ]);

            $last_sync_datetime = $this->setting_model->getSetting()->last_biometric_sync_datetime;
            $fromDateTime = $last_sync_datetime ? date('Y-m-d H:i:s', strtotime($last_sync_datetime)) : date('Y-m-d 00:00:00', strtotime('-1 day'));
            $toDateTime = date('Y-m-d H:i:s');

            $raw_punches = $this->biometric_api_client->getAttendanceLogs($fromDateTime, $toDateTime);

            if ($raw_punches === false) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Failed to fetch attendance logs from biometric device. Check API configuration and logs.</div>');
                log_message('error', 'Staffattendance::sync_biometric_attendance - Failed to fetch attendance logs. Redirecting to index.');
                redirect('admin/staffattendance/index');
            }
            
            $inserted_count = 0;
            foreach ($raw_punches as $punch) {
                $staff_id = $punch['staff_id'];
                $punch_time = $punch['punch_time'];
                $punch_date = date('Y-m-d', strtotime($punch_time));

                $this->staff_biometric_punches_model->add([
                    'staff_id' => $staff_id,
                    'punch_time' => $punch_time,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $inserted_count++;
            }

            $this->setting_model->update(array('last_biometric_sync_datetime' => $toDateTime));

            $msg = '<div class="alert alert-success">Biometric raw punches synchronized successfully. ' . $inserted_count . ' new punches recorded.</div>';
            $this->session->set_flashdata('msg', $msg);
            log_message('info', 'Staffattendance::sync_biometric_attendance - Raw punches synced. Redirecting to index.');
            redirect('admin/staffattendance/index');
        }
    }

    private function _process_staff_attendance_from_punches($date_to_process)
    {
        $staff_punches_by_day = $this->staff_biometric_punches_model->get_punches_by_date($date_to_process);
        $unmatched_staff_ids = [];

        foreach ($staff_punches_by_day as $staff_id => $dates) {
            $staff_detail = $this->staff_model->get($staff_id);
            if (empty($staff_detail)) {
                $unmatched_staff_ids[] = $staff_id;
                continue;
            }
            $role_id = $staff_detail['role_id'];

            foreach ($dates as $date => $timestamps) {
                sort($timestamps);

                $in_time = date('H:i:s', $timestamps[0]);
                $out_time = date('H:i:s', end($timestamps));
                $punches = count($timestamps);

                $attendance_type_id = 3; // Default to Absent
                $remark = '';

                if ($punches == 0) {
                    $attendance_type_id = 3; // Absent
                    $remark = 'No punch found';
                } elseif ($punches == 1) {
                    $attendance_type_id = 3; // Absent
                    $remark = 'No exit punch';
                } else {
                    // --- Morning Session Logic ---
                    $morning_session_status_set = false;
                    if ($in_time >= '09:29:00' && $in_time <= '10:15:00') {
                        $permission_count_monthly = $this->staffpermission_model->get_permission_count($staff_id, $date)['count'];
                        if ($permission_count_monthly < 2) {
                            $attendance_type_id = 7; // First Half Permission
                            $this->staffpermission_model->add_permission(['staff_id' => $staff_id, 'date' => $date]); // Add permission
                            $morning_session_status_set = true;
                        } else {
                            $attendance_type_id = 10; // First Half Absent (permissions exhausted)
                            $morning_session_status_set = true;
                        }
                    } elseif ($in_time <= '09:17:00') {
                        $attendance_type_id = 1; // Present
                        $morning_session_status_set = true;
                    } elseif ($in_time <= '09:28:00') {
                        $attendance_type_id = 2; // Late
                        $morning_session_status_set = true;
                    } else { // Arrived after 10:15, so automatically First Half Absent
                         $attendance_type_id = 10; // First Half Absent
                         $morning_session_status_set = true;
                    }

                    // --- Afternoon Session Logic ---
                    // This logic is independent of morning session for permission consumption,
                    // but determines the final attendance_type_id for the day.
                    $afternoon_session_status_set = false;

                    // If morning attendance was Present or Late, we can still have a Second Half Permission/Absent
                    if ($attendance_type_id == 1 || $attendance_type_id == 2) {
                         if ($out_time >= '15:15:00' && $out_time <= '16:30:00') { // Out_time falls within Second Half Permission window
                            $permission_count_monthly = $this->staffpermission_model->get_permission_count($staff_id, $date)['count'];
                            if ($permission_count_monthly < 2) {
                                $attendance_type_id = 9; // Second Half Permission
                                $this->staffpermission_model->add_permission(['staff_id' => $staff_id, 'date' => $date]); // Add permission
                                $afternoon_session_status_set = true;
                            } else {
                                $attendance_type_id = 11; // Second Half Absent (permissions exhausted)
                                $afternoon_session_status_set = true;
                            }
                        } elseif ($out_time < '15:15:00') { // Left before second half permission window
                            $attendance_type_id = 11; // Second Half Absent
                            $afternoon_session_status_set = true;
                        }
                        // If out_time > 16:30, it's valid, so morning Present/Late status remains.
                    }

                    // If morning was a permission or absent, and afternoon triggered a status, which one is final?
                    // User's example: morning 9:29 -> FHP, evening 4:57 -> valid. Final: FHP.
                    // This suggests morning status takes precedence if it's a permission/absent.
                    // If morning was FHP (7) or FHA (10) or SHA (11), it typically defines the day.
                    // If morning was Present (1) or Late (2), then afternoon can change it to SHP (9) or SHA (11).

                    // If morning status was set and is a permission/absent, that is the primary attendance for the day.
                    // The only time afternoon status should change it is if morning was Present/Late and afternoon is Absent/Permission.
                    // But if morning was already Present/Late, the above afternoon logic applies.

                } // end else (punches > 1)

                $existing_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $date);

                $attendance_record = [
                    'staff_id' => $staff_id,
                    'staff_attendance_type_id' => $attendance_type_id,
                    'remark' => $remark,
                    'in_time' => $in_time,
                    'out_time' => $out_time,
                    'date' => $date,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if ($existing_attendance) {
                    $attendance_record['id'] = $existing_attendance['id'];
                    $this->staffattendancemodel->add($attendance_record);
                } else {
                    $this->staffattendancemodel->add($attendance_record);
                }
            }
        }
        return ['unmatched_staff_ids' => $unmatched_staff_ids];
    }



    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt');
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('error_opening_the_file'));
                return false;
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

    public function trigger_process_biometric_attendance() {
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_view'))) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/staffattendance/trigger_process_biometric_attendance');
        $data['title'] = $this->lang->line('process_biometric_attendance');

        $setting = $this->setting_model->getSetting();
        $last_processed_attendance_date = isset($setting->last_processed_attendance_date) ? $setting->last_processed_attendance_date : null;

        $today = date('Y-m-d');
        $this_month_first_day = date('Y-m-01');

        if (empty($last_processed_attendance_date)) {
            // First time processing, process from the beginning of the current month
            $from_date = $this_month_first_day;
        } else {
            // Process from the day after the last processed date
            $from_date = date('Y-m-d', strtotime($last_processed_attendance_date . ' +1 day'));
        }

        $to_date = $today; // Always process up to today

        $messages = [];
        $overall_unmatched_staff_ids = [];
        $processed_dates_count = 0;

        // Loop through each day from from_date to to_date (inclusive)
        $current_date = $from_date;
        while (strtotime($current_date) <= strtotime($to_date)) {
            $result = $this->_process_staff_attendance_from_punches($current_date);
            if (!empty($result['unmatched_staff_ids'])) {
                $overall_unmatched_staff_ids = array_merge($overall_unmatched_staff_ids, $result['unmatched_staff_ids']);
            }
            $messages[] = 'Processed attendance for ' . $current_date . '.';
            $processed_dates_count++;
            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }

        // Update last_processed_attendance_date only if some dates were processed
        if ($processed_dates_count > 0) {
            $this->setting_model->add(array('last_processed_attendance_date' => $to_date));
            $final_msg = '<div class="alert alert-success">Biometric attendance processed successfully for ' . $processed_dates_count . ' day(s) up to ' . $to_date . '.</div>';
        } else {
            $final_msg = '<div class="alert alert-info">No new attendance data to process. Last processed date: ' . ($last_processed_attendance_date ?: 'N/A') . '</div>';
        }

        if (!empty($overall_unmatched_staff_ids)) {
            $final_msg .= '<div class="alert alert-warning">Warning: Unmatched staff IDs: ' . implode(', ', array_unique($overall_unmatched_staff_ids)) . '</div>';
        }
        $this->session->set_flashdata('msg', $final_msg);

        redirect('admin/staffattendance/index');
    }

}