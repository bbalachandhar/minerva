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
        $this->load->library('logger');
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
                    if (empty($attendencetype)) {
                        $attendencetype = 3; // Default to 'Absent' if no attendance type is selected
                    }
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

                    $remark = $this->input->post("remark" . $value);
                    if (empty($remark)) {
                        $remark = "";
                    }

                    $attendance_array[] = array(                       
                        'staff_id'                 => $value,
                        'staff_attendance_type_id' => $attendencetype,
                        'remark'                   => $remark,
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
            
            // Reorder $attendencetypeslist
            $reordered_attendencetypes = [];
            $half_day_item = null;
            $late_index = -1;

            foreach ($attendencetypes as $key => $type) {
                if ($type['id'] == 6) { // Half Day Second Shift (now renamed to Half Day)
                    $half_day_item = $type;
                    // Also ensure the long_lang_name is 'half_day' for consistency
                    $half_day_item['long_lang_name'] = 'half_day';
                    continue; // Skip adding it for now
                }
                if ($type['id'] == 2) { // Late
                    $late_index = count($reordered_attendencetypes);
                }
                $reordered_attendencetypes[] = $type;
            }

            // Insert half_day_item after late_item
            if ($half_day_item !== null && $late_index !== -1) {
                array_splice($reordered_attendencetypes, $late_index + 1, 0, [$half_day_item]);
            } elseif ($half_day_item !== null) {
                // If 'Late' not found, just add 'Half Day' at the end
                $reordered_attendencetypes[] = $half_day_item;
            }

            $data['attendencetypeslist'] = $reordered_attendencetypes;        
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
        $all_role_settings_raw = $this->staffAttendaceSetting_model->getRoleAttendanceSetting();
        $role_settings = [];
        foreach ($all_role_settings_raw as $setting) {
            $role_settings[$setting->role_id][$setting->staff_attendence_type_id] = [
                'from' => $setting->entry_time_from,
                'to'   => $setting->entry_time_to,
            ];
        }

        $settings = $this->setting_model->getSetting();
        $max_permission_allowed = isset($settings->max_permission_allowed) ? $settings->max_permission_allowed : 2;
        $max_late_allowed = isset($settings->max_late_allowed) ? $settings->max_late_allowed : 2;

        $all_active_staff = $this->staff_model->get();
        $staff_punches_by_day = $this->staff_biometric_punches_model->get_punches_by_date($date_to_process);
        $unmatched_staff_ids = [];

        foreach ($all_active_staff as $staff_member) {
            $staff_id = $staff_member['id'];
            $role_id = $staff_member['role_id'];

            if (isset($staff_punches_by_day[$staff_id])) {
                $dates = $staff_punches_by_day[$staff_id];
                $timestamps = reset($dates);
                $date = key($dates);

                $this->logger->log("--- Processing Staff ID: {$staff_id}, Date: {$date} ---");
                sort($timestamps);

                $in_time = date('H:i:s', $timestamps[0]);
                $out_time = date('H:i:s', end($timestamps));
                $punches = count($timestamps);
                $this->logger->log("Raw timestamps for staff {$staff_id}: " . implode(', ', $timestamps));
                $this->logger->log("Calculated - In: {$in_time}, Out: {$out_time}, Punches: {$punches} for staff {$staff_id}");

                $morning_session_status = 10; // Default to First Half Absent
                $afternoon_session_status = 11; // Default to Second Half Absent
                $overall_attendance_type_id = 3; // Default to Full Day Absent
                $remark = '';

                if ($punches == 1) {
                    $this->logger->log("Scenario: One punch for staff {$staff_id}, Date: {$date}. Setting to Absent (no exit punch).");
                    $remark = 'No exit punch';
                } else {
                    $present_morning_to = isset($role_settings[$role_id][1]['to']) ? $role_settings[$role_id][1]['to'] : '09:17:00';
                    $late_to = isset($role_settings[$role_id][2]['to']) ? $role_settings[$role_id][2]['to'] : '09:28:00';
                    $fhp_from = isset($role_settings[$role_id][7]['from']) ? $role_settings[$role_id][7]['from'] : '09:29:00';
                    $fhp_to = isset($role_settings[$role_id][7]['to']) ? $role_settings[$role_id][7]['to'] : '10:15:00';

                    $this->logger->log("Morning Session: Evaluating in_time: {$in_time} for staff {$staff_id}, Date: {$date}");

                    if ($in_time <= $present_morning_to) {
                        $morning_session_status = 1; // Present
                    } elseif ($in_time > $present_morning_to && $in_time < $fhp_from) {
                        if ($in_time <= $late_to) {
                            $late_count_monthly = $this->staffattendancemodel->count_late_in_month($staff_id, $date)['count'];
                            if ($late_count_monthly < $max_late_allowed) {
                                $morning_session_status = 2; // Late
                            } else {
                                $morning_session_status = 10; // FHA
                            }
                        } else {
                            $morning_session_status = 10; // FHA (Gap between Late and FHP)
                        }
                    } elseif ($in_time >= $fhp_from && $in_time <= $fhp_to) {
                        $permission_count_monthly = $this->staffattendancemodel->count_permission_in_month($staff_id, $date)['count'];
                        if ($permission_count_monthly < $max_permission_allowed) {
                            $morning_session_status = 7; // FHP
                        } else {
                            $morning_session_status = 10; // FHA
                        }
                    } else {
                        $morning_session_status = 10; // FHA (After FHP window)
                    }

                    $shp_from = isset($role_settings[$role_id][9]['from']) ? $role_settings[$role_id][9]['from'] : '15:15:00';
                    $shp_to = isset($role_settings[$role_id][9]['to']) ? $role_settings[$role_id][9]['to'] : '16:15:00';

                    $this->logger->log("Afternoon Session: Evaluating out_time: {$out_time} for staff {$staff_id}, Date: {$date}");
                    if ($out_time < $shp_from) {
                        $afternoon_session_status = 11; // SHA
                    } elseif ($out_time >= $shp_from && $out_time <= $shp_to) {
                        $permission_count_monthly = $this->staffattendancemodel->count_permission_in_month($staff_id, $date)['count'];
                        if ($permission_count_monthly < $max_permission_allowed) {
                            $afternoon_session_status = 9; // SHP
                        } else {
                            $afternoon_session_status = 11; // SHA
                        }
                    } else { // This covers $out_time > $shp_to
                        $afternoon_session_status = 1; // Present
                    }

                    // Refined overall_attendance_type_id calculation
                    if ($morning_session_status == 1 && $afternoon_session_status == 1) {
                        $overall_attendance_type_id = 1; // Present
                    } elseif (($morning_session_status == 3 || $morning_session_status == 10) && ($afternoon_session_status == 3 || $afternoon_session_status == 11)) {
                         $overall_attendance_type_id = 3; // Full Day Absent
                    } elseif ($morning_session_status == 7 && $afternoon_session_status == 1) {
                        $overall_attendance_type_id = 7; // First Half Permission
                    } elseif ($morning_session_status == 1 && $afternoon_session_status == 9) {
                        $overall_attendance_type_id = 9; // Second Half Permission
                    } elseif ($morning_session_status == 10 && $afternoon_session_status == 1) {
                        $overall_attendance_type_id = 10; // First Half Absent
                    } elseif ($morning_session_status == 1 && $afternoon_session_status == 11) {
                        $overall_attendance_type_id = 11; // Second Half Absent
                    } elseif ($morning_session_status == 2) { // Morning Late is prioritized if it's the only special status
                        $overall_attendance_type_id = 2; // Late
                    } elseif ($morning_session_status == 7 && ($afternoon_session_status == 11 || $afternoon_session_status == 3 || $afternoon_session_status == 10)) { // FHP and afternoon absent/FHA
                        $overall_attendance_type_id = 7; // First Half Permission
                    } elseif (($morning_session_status == 10 || $morning_session_status == 3 || $morning_session_status == 11) && $afternoon_session_status == 9) { // morning absent/FHA/SHA and SHP
                        $overall_attendance_type_id = 9; // Second Half Permission
                    }
                    // Catch-all for other combinations not specifically handled, often resulting in Half Day
                    else {
                        $overall_attendance_type_id = 6; // Half Day
                    }
                }

                $session_attendance_data = json_encode(['morning_session' => $morning_session_status, 'afternoon_session' => $afternoon_session_status]);
                $attendance_record = [
                    'staff_id' => $staff_id,
                    'staff_attendance_type_id' => $overall_attendance_type_id,
                    'session_attendance_data' => $session_attendance_data,
                    'remark' => $remark,
                    'in_time' => $in_time ?? null,
                    'out_time' => $out_time ?? null,
                    'date' => $date,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                
                $existing_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $date);
                if ($existing_attendance) {
                    $attendance_record['id'] = $existing_attendance['id'];
                }
                $this->staffattendancemodel->add($attendance_record);

            } else {
                // Staff has NO punches for this day. Mark as Absent.
                $existing_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $date_to_process);

                if (!$existing_attendance) {
                    $this->logger->log("--- No punches found for Staff ID: {$staff_id}, Date: {$date_to_process}. Marking as Absent. ---");
                    $attendance_record = [
                        'staff_id' => $staff_id,
                        'staff_attendance_type_id' => 3, // 3 is for 'Absent'
                        'remark' => 'No punch found',
                        'date' => $date_to_process,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
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
            $setting = $this->setting_model->getSetting(); // Get the existing settings row
            $setting_id = $setting->id; // Get the ID of the existing settings row

            $update_data = [
                'id' => $setting_id, // Pass the ID for update
                'last_processed_attendance_date' => $to_date
            ];
            $this->setting_model->add($update_data); // Call add method with ID
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