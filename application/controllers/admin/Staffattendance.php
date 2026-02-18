<?php
// Version: 2026-02-18-FINAL - getStaffName fix, summary logs only
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
        $this->load->model("holiday_model"); // Load holiday model to check for official holidays
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

        // Load attendance types (needed by the view for JavaScript rendering)
        $attendencetypes = $this->attendencetype_model->getStaffAttendanceType();
        $reordered_attendencetypes = [];

        foreach ($attendencetypes as $key => $type) {
            // Skip internal session status types (8, 9, 10, 11) from radio button display
            if (in_array($type['id'], [8, 9, 10, 11])) {
                continue;
            }
            $reordered_attendencetypes[] = $type;
        }

        $data['attendencetypeslist'] = $reordered_attendencetypes;

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
                    );
                }
               
                $this->staffattendancemodel->addorUpdate($attendance_array);

                // Ensure manual in/out punches are recorded in raw punches table so UI & processing reflect manual edits
                $dateStr = date('Y-m-d', $this->customlib->datetostrtotime($date));
                foreach ($attendance_array as $attRec) {
                    $staff_id = $attRec['staff_id'];
                    $in_time = $attRec['in_time'];
                    $out_time = $attRec['out_time'];

                    // fetch existing punches for the staff on that date
                    $existing = $this->staff_biometric_punches_model->get_punches_by_staff_and_date($staff_id, $dateStr);
                    $existing_times = array_map(function($r){ return date('Y-m-d H:i:s', strtotime($r['punch_time'])); }, $existing);

                    if (!empty($in_time)) {
                        $punch_dt = $dateStr . ' ' . $in_time;
                        if (!in_array($punch_dt, $existing_times, true)) {
                            $this->staff_biometric_punches_model->add([
                                'staff_id'   => $staff_id,
                                'punch_time' => $punch_dt,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }

                    if (!empty($out_time)) {
                        $punch_dt = $dateStr . ' ' . $out_time;
                        if (!in_array($punch_dt, $existing_times, true)) {
                            $this->staff_biometric_punches_model->add([
                                'staff_id'   => $staff_id,
                                'punch_time' => $punch_dt,
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                }

                // Re-run attendance processing from raw punches for this date so attendance type auto-updates
                // this will overwrite attendance_type based on punch-derived logic (desired behavior)
                $this->_process_staff_attendance_from_punches($dateStr);

                // Append audit remark (who & when) for each manually updated attendance row
                try {
                    $current_user = $this->customlib->getUserData();
                    $auditor_name = isset($current_user['name']) ? $current_user['name'] : (isset($current_user['firstname']) ? $current_user['firstname'] : '');
                    $auditor_role = isset($current_user['user_type']) ? $current_user['user_type'] : '';
                    $timestamp = date('d/m/Y h:i:s a');
                    $audit_text = $auditor_name . '(' . $auditor_role . ') updated on ' . $timestamp . '.';

                    foreach ($attendance_array as $attRec) {
                        $s_id = $attRec['staff_id'];
                        $db_att = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($s_id, $dateStr);
                        if ($db_att && isset($db_att['id'])) {
                            $existing_remark = trim(isset($db_att['remark']) ? $db_att['remark'] : '');
                            $new_remark = $existing_remark !== '' ? $existing_remark . ' | ' . $audit_text : $audit_text;
                            $this->staffattendancemodel->add(['id' => $db_att['id'], 'remark' => $new_remark]);
                        } else {
                            $this->staffattendancemodel->add(['staff_id' => $s_id, 'remark' => $audit_text, 'date' => $dateStr]);
                        }
                    }
                } catch (Exception $e) {
                    log_message('error', 'Failed to append audit remarks during bulk staffattendance save: ' . $e->getMessage());
                }

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
            $exception_count = 0;
            
            foreach ($raw_punches as $punch) {
                $staff_id = $punch['staff_id'];
                $punch_time = $punch['punch_time'];
                $punch_date = date('Y-m-d', strtotime($punch_time));

                // Check if punch is too early (earlier than 3 hours before morning window)
                $staff_member = $this->staff_model->getStaffDetails($staff_id);
                $is_exception = 0;
                $exception_reason = null;

                if ($staff_member) {
                    $role_id = $staff_member['role'];
                    // Get morning "Present" window start time
                    $present_type_id = 1; // Present
                    $present_setting = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $present_type_id);

                    if ($present_setting && !empty($present_setting->entry_time_from)) {
                        $morning_window_start = $present_setting->entry_time_from;
                        $three_hour_buffer = date('H:i:s', strtotime($morning_window_start) - (3 * 60 * 60));
                        
                        $punch_time_only = date('H:i:s', strtotime($punch_time));
                        
                        // If punch is earlier than 3 hours before morning window, mark as exception
                        if (strtotime($punch_time_only) < strtotime($three_hour_buffer)) {
                            $is_exception = 1;
                            $exception_reason = "Punch at {$punch_time_only} is earlier than 3-hour buffer ({$three_hour_buffer}) before morning window ({$morning_window_start})";
                            $exception_count++;
                            log_message('info', "Exception flagged: Staff {$staff_id} - {$exception_reason}");
                        }
                    }
                }

                $this->staff_biometric_punches_model->add([
                    'staff_id' => $staff_id,
                    'punch_time' => $punch_time,
                    'is_exception' => $is_exception,
                    'exception_reason' => $exception_reason,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $inserted_count++;
            }

            $this->setting_model->update(array('last_biometric_sync_datetime' => $toDateTime));

            // Add summary log for biometric sync
            $this->load->model('staff_biometric_punches_model');
            $admin_session = $this->session->userdata('admin');
            $user_name = !empty($admin_session['username']) ? $admin_session['username'] : 'System';
            $log_message = "Biometric attendance synced by {$user_name}: {$inserted_count} punches synchronized";
            if ($exception_count > 0) {
                $log_message .= ", {$exception_count} exceptions flagged";
            }
            $this->staff_biometric_punches_model->log($log_message, null, 'Sync');

            $msg = '<div class="alert alert-success">Biometric raw punches synchronized successfully. ' . $inserted_count . ' new punches recorded.';
            if ($exception_count > 0) {
                $msg .= ' <strong>' . $exception_count . ' exceptions flagged for review.</strong>';
            }
            $msg .= '</div>';
            $this->session->set_flashdata('msg', $msg);
            log_message('info', 'Staffattendance::sync_biometric_attendance - Raw punches synced. Exceptions: ' . $exception_count . '. Redirecting to index.');
            redirect('admin/staffattendance/index');
        }
    }

    /**
     * Fetch raw biometric punches between two dates (inclusive), reset existing raw punches in that range,
     * and insert fresh punches from the biometric device for the same range.
     * POST params: from_date, to_date (UI date format accepted)
     * Returns JSON { status: 'success'|'fail', message: '', inserted: int, exceptions: int }
     */
    public function fetch_punches_between_dates()
    {
        // Start output buffering and set proper JSON headers
        ob_start();
        header('Content-Type: application/json');
        
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_view'))) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Access Denied']);
            return;
        }

        // allow long-running fetch for large date ranges
        ignore_user_abort(true);
        @set_time_limit(0);

        $from_date_raw = $this->input->post('from_date');
        $to_date_raw = $this->input->post('to_date');

        if (empty($from_date_raw) || empty($to_date_raw)) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Invalid date range']);
            return;
        }

        // normalize dates to YYYY-MM-DD
        try {
            $from_date = date('Y-m-d', $this->customlib->datetostrtotime($from_date_raw));
            $to_date = date('Y-m-d', $this->customlib->datetostrtotime($to_date_raw));
            log_message('debug', "fetch_punches_between_dates: Raw dates from UI - From: {$from_date_raw}, To: {$to_date_raw}");
            log_message('debug', "fetch_punches_between_dates: Normalized dates - From: {$from_date}, To: {$to_date}");
        } catch (Exception $e) {
            log_message('error', "fetch_punches_between_dates: Date parsing error - " . $e->getMessage());
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Invalid date format']);
            return;
        }

        if (strtotime($from_date) > strtotime($to_date)) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'From date must be earlier than To date']);
            return;
        }

        $active_device = $this->biometric_device_model->getActiveDevice();
        if (empty($active_device)) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'No active biometric device configured']);
            return;
        }

        $fromDateTime = $from_date . ' 00:00:00';
        $toDateTime = $to_date . ' 23:59:59';

        $this->biometric_api_client->initialize([
            'api_endpoint' => $active_device['api_endpoint'],
            'serial_number' => $active_device['serial_number'],
            'username' => $active_device['username'],
            'password' => $active_device['password'],
        ]);

        // Reset existing raw punches in the range
        $this->staff_biometric_punches_model->delete_punches_between_dates($from_date, $to_date);

        log_message('debug', "fetch_punches_between_dates: Calling biometric API with FromDateTime: {$fromDateTime}, ToDateTime: {$toDateTime}");
        $start_time = microtime(true);
        
        $raw_punches = $this->biometric_api_client->getAttendanceLogs($fromDateTime, $toDateTime);
        
        $elapsed_time = round(microtime(true) - $start_time, 2);
        log_message('debug', "fetch_punches_between_dates: API call completed in {$elapsed_time} seconds");
        
        if ($raw_punches === false) {
            log_message('error', "fetch_punches_between_dates: Failed to fetch logs from biometric device");
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Failed to fetch logs from biometric device']);
            return;
        }

        log_message('debug', "fetch_punches_between_dates: Received " . count($raw_punches) . " punches from API");

        $inserted = 0;
        $exceptions = 0;

        foreach ($raw_punches as $punch) {
            $biometric_id = $punch['staff_id']; // This is the biometric device ID, not the database staff_id
            $punch_time = $punch['punch_time'];

            // Map biometric_id to actual staff_id
            $staff_member = $this->staff_model->get_by_biometric_id($biometric_id);
            
            if (!$staff_member) {
                log_message('error', "fetch_punches_between_dates: Staff with biometric_id {$biometric_id} not found. Skipping punch.");
                continue;
            }

            $staff_id = $staff_member->id; // Get the actual database staff_id
            $is_exception = 0;
            $exception_reason = null;

            // Exception detection logic
            $role_id = $staff_member->role_id;
            $present_setting = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, 1);
            if ($present_setting && !empty($present_setting->entry_time_from)) {
                $morning_window_start = $present_setting->entry_time_from;
                $three_hour_buffer = date('H:i:s', strtotime($morning_window_start) - (3 * 60 * 60));
                $punch_time_only = date('H:i:s', strtotime($punch_time));
                if (strtotime($punch_time_only) < strtotime($three_hour_buffer)) {
                    $is_exception = 1;
                    $exception_reason = "Punch at {$punch_time_only} is earlier than 3-hour buffer ({$three_hour_buffer}) before morning window ({$morning_window_start})";
                    $exceptions++;
                    log_message('info', "Exception flagged: Staff {$staff_id} (biometric_id: {$biometric_id}) - {$exception_reason}");
                }
            }

            $this->staff_biometric_punches_model->add([
                'staff_id' => $staff_id,
                'punch_time' => $punch_time,
                'is_exception' => $is_exception,
                'exception_reason' => $exception_reason,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $inserted++;
        }

        log_message('debug', "fetch_punches_between_dates: Completed. Inserted: {$inserted}, Exceptions: {$exceptions}");
        
        // Add summary log for fetching punches between dates
        $admin_session = $this->session->userdata('admin');
        $user_name = !empty($admin_session['username']) ? $admin_session['username'] : 'System';
        $log_message = "Biometric punches fetched by {$user_name}: {$inserted} punches from {$from_date} to {$to_date}";
        if ($exceptions > 0) {
            $log_message .= ", {$exceptions} exceptions flagged";
        }
        $this->staff_biometric_punches_model->log($log_message, null, 'Fetch');
        
        ob_end_clean();
        $message = $inserted . ' punch' . ($inserted != 1 ? 'es' : '') . ' fetched successfully!';
        echo json_encode(['status' => 'success', 'message' => $message, 'inserted' => $inserted, 'exceptions' => $exceptions]);
        return;
    }

    private function _process_staff_attendance_from_punches($date_to_process, $target_staff_id = null)
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

        $all_active_staff = $this->staff_model->get();
        $staff_punches_by_day = $this->staff_biometric_punches_model->get_punches_by_date($date_to_process);
        $unmatched_staff_ids = [];

        foreach ($all_active_staff as $staff_member) {
            $staff_id = $staff_member['id'];
            $role_id = $staff_member['role_id'];
            
            // Use Admin role (ID 1) as default if staff has no role assigned
            if (empty($role_id)) {
                $role_id = 1; // Admin role
                $this->logger->log("Staff ID: {$staff_id} has no role assigned. Using default Admin role (ID: 1)");
            }

            // If a target staff id is provided, skip all other staff for performance and correctness
            if ($target_staff_id !== null && (int)$staff_id !== (int)$target_staff_id) {
                continue;
            }

            if (isset($staff_punches_by_day[$staff_id]) && isset($staff_punches_by_day[$staff_id][$date_to_process])) {
                $timestamps = $staff_punches_by_day[$staff_id][$date_to_process];
                $date = $date_to_process;

                $this->logger->log("--- Processing Staff ID: {$staff_id}, Date: {$date} ---");
                sort($timestamps);

                $in_time = date('H:i:s', $timestamps[0]);
                $punches = count($timestamps);
                // If only one punch, treat out_time as null to avoid calculating afternoon status based on morning punch
                $out_time = ($punches > 1) ? date('H:i:s', end($timestamps)) : null;
                
                $this->logger->log("Raw timestamps for staff {$staff_id}: " . implode(', ', $timestamps));
                $this->logger->log("Calculated - In: {$in_time}, Out: " . ($out_time ?? 'NULL') . ", Punches: {$punches} for staff {$staff_id}");


                $remark = '';
                $morning_session_status = 8; // Default to First Half Absent
                $afternoon_session_status = 9; // Default to Second Half Absent
                $overall_attendance_type_id = 3; // Default to Full Day Absent

                // Always use schedule table for both punches
                $remark = ($punches == 1) ? 'Single punch - No exit time' : '';

                // Morning session: first punch
                $morning_type = $this->staffAttendaceSetting_model->getAttendanceTypeByRole($role_id, $in_time);
                $second_half_start = false;

                if ($morning_type) {
                    if ((int)$morning_type->staff_attendence_type_id === 4) { // Half Day window means second-half start
                        $morning_session_status = 8; // First Half Absent
                        $afternoon_session_status = 1; // Second Half Present
                        $second_half_start = true;
                    } elseif ((int)$morning_type->staff_attendence_type_id === 6) { // SHL is arrival-based
                        $morning_session_status = 8; // First Half Absent
                        $afternoon_session_status = 6; // Second Half Late
                        $second_half_start = true;
                    } else {
                        $morning_session_status = $morning_type->staff_attendence_type_id;
                    }
                } else {
                    // Check for early arrival (Present)
                    $present_id = 1;
                    $present_setting = $this->staffAttendaceSetting_model->getAttendanceTypeByRoleAndType($role_id, $present_id);

                    if ($present_setting && strtotime($in_time) < strtotime($present_setting->entry_time_from)) {
                        $morning_session_status = $present_id;
                    } else {
                        $shl_range = isset($role_settings[$role_id][6]) ? $role_settings[$role_id][6] : null;
                        if ($shl_range && !empty($shl_range['to']) && strtotime($in_time) > strtotime($shl_range['to'])) {
                            $morning_session_status = 8;
                            $afternoon_session_status = 9;
                            $second_half_start = true;
                        } else {
                            $morning_session_status = 8;
                        }
                    }
                }

                // Afternoon session: last punch
                if ($out_time !== null && !$second_half_start) {
                    $afternoon_type = $this->staffAttendaceSetting_model->getAttendanceTypeByRole($role_id, $out_time);
                    if ($afternoon_type) {
                        $afternoon_session_status = $afternoon_type->staff_attendence_type_id;
                    } else {
                        $shp_range = isset($role_settings[$role_id][7]) ? $role_settings[$role_id][7] : null;

                        if ($shp_range && $this->time_in_range($out_time, $shp_range['from'], $shp_range['to'])) {
                            $afternoon_session_status = 7;
                        } else {
                            $second_half_cutoff = !empty($settings->morning_session_end_time) ? $settings->morning_session_end_time : null;

                            if ($second_half_cutoff && strtotime($out_time) < strtotime($second_half_cutoff)) {
                                $afternoon_session_status = 9;
                            } else {
                                $present_cutoff = null;
                                if ($shp_range && !empty($shp_range['to'])) {
                                    $present_cutoff = $shp_range['to'];
                                } elseif (!empty($settings->evening_session_end_time)) {
                                    $present_cutoff = $settings->evening_session_end_time;
                                }

                                if ($present_cutoff && strtotime($out_time) >= strtotime($present_cutoff)) {
                                    $afternoon_session_status = 1;
                                } else {
                                    $afternoon_session_status = 9;
                                }
                            }
                        }
                    }
                } else {
                    // No exit punch - defaults to Second Half Absent (9)
                    $afternoon_session_status = 9; 
                }

                // Determine overall attendance type
                $morning_session_status = (int)$morning_session_status;
                $afternoon_session_status = (int)$afternoon_session_status;
                $first_half_present = in_array($morning_session_status, [1, 2, 5], true);
                $second_half_present = in_array($afternoon_session_status, [1, 6, 7], true);

                // Day Summary: Both halves present → Present; one half present → Half Day; both absent → Absent
                if ($first_half_present && $second_half_present) {
                    $overall_attendance_type_id = 1; // Present
                } elseif ($first_half_present || $second_half_present) {
                    $overall_attendance_type_id = 4; // Half Day
                } else {
                    $overall_attendance_type_id = 3; // Absent
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
                    'biometric_attendence' => 1,
                    'qrcode_attendance' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                // Delete any existing attendance records first to prevent duplicates
                $this->db->where('staff_id', $staff_id);
                $this->db->where('date', $date);
                $this->db->delete('staff_attendance');
                
                // Now insert the fresh record
                $this->staffattendancemodel->add($attendance_record);

            } else {
                // Staff has NO punches for this day. Check if it's a holiday first, then mark as Absent.
                // First, delete any existing attendance records to prevent duplicates
                $this->db->where('staff_id', $staff_id);
                $this->db->where('date', $date_to_process);
                $this->db->delete('staff_attendance');

                // Check if the date is an official holiday
                $is_holiday = $this->_is_official_holiday($date_to_process);
                
                if ($is_holiday) {
                    // Mark as Holiday
                    $attendance_type_id = $this->staff_attendance['holiday']; // Type ID 5
                    $remark = 'Official Holiday';
                    $this->logger->log("--- No punches found for Staff ID: {$staff_id}, Date: {$date_to_process}. Marking as Holiday. ---");
                } else {
                    // Mark as Absent
                    $attendance_type_id = 3; // 3 is for 'Absent'
                    $remark = 'No punch found';
                    $this->logger->log("--- No punches found for Staff ID: {$staff_id}, Date: {$date_to_process}. Marking as Absent. ---");
                }
                
                $attendance_record = [
                    'staff_id' => $staff_id,
                    'staff_attendance_type_id' => $attendance_type_id,
                    'remark' => $remark,
                    'date' => $date_to_process,
                    'biometric_attendence' => 1,
                    'qrcode_attendance' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->staffattendancemodel->add($attendance_record);
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

    private function time_in_range($time, $from, $to)
    {
        if (empty($time)) {
            return false;
        }

        $from_clean = is_string($from) ? trim($from) : $from;
        $to_clean = is_string($to) ? trim($to) : $to;
        if ($from_clean === '00:00:00' && $to_clean === '00:00:00') {
            return false;
        }

        $time_ts = strtotime($time);
        $from_ts = !empty($from) ? strtotime($from) : null;
        $to_ts = !empty($to) ? strtotime($to) : null;

        if ($from_ts && $to_ts) {
            return $time_ts >= $from_ts && $time_ts <= $to_ts;
        }

        if ($from_ts && !$to_ts) {
            return $time_ts >= $from_ts;
        }

        if (!$from_ts && $to_ts) {
            return $time_ts <= $to_ts;
        }

        return false;
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

        if ($last_processed_attendance_date === null || $last_processed_attendance_date === '') {
            // First time processing, process from the beginning of the current month
            $from_date = $this_month_first_day;
        } elseif ($last_processed_attendance_date === $today) {
            // Allow reprocessing today's attendance
            $from_date = $today;
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
            
            // Add summary log for attendance processing
            $admin_session = $this->session->userdata('admin');
            $user_name = !empty($admin_session['username']) ? $admin_session['username'] : 'System';
            $log_message = "Biometric attendance processed by {$user_name}: {$processed_dates_count} day(s) from {$from_date} to {$to_date}";
            $this->staffattendancemodel->log($log_message, null, 'Process');
            
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

    /**
     * Process attendance between two dates (inclusive). Existing processed (biometric) attendance
     * in that date range will be removed and reprocessed from raw punches.
     * POST params: from_date, to_date (UI date format accepted)
     * Returns JSON { status:'success'|'fail', message:'', processed_days: int, deleted_rows: int }
     */
    public function process_attendance_between_dates()
    {
        // Start output buffering and set proper JSON headers
        ob_start();
        header('Content-Type: application/json');
        
        if (!($this->rbac->hasPrivilege('biometric_attendance', 'can_view'))) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Access Denied']);
            return;
        }

        // allow long-running processing for large date ranges
        ignore_user_abort(true);
        @set_time_limit(0);

        $from_raw = $this->input->post('from_date');
        $to_raw = $this->input->post('to_date');

        if (empty($from_raw) || empty($to_raw)) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Invalid date range']);
            return;
        }

        try {
            $from_date = date('Y-m-d', $this->customlib->datetostrtotime($from_raw));
            $to_date = date('Y-m-d', $this->customlib->datetostrtotime($to_raw));
        } catch (Exception $e) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'Invalid date format']);
            return;
        }

        if (strtotime($from_date) > strtotime($to_date)) {
            ob_end_clean();
            echo json_encode(['status' => 'fail', 'message' => 'From date must be earlier than To date']);
            return;
        }

        // delete existing processed (biometric) attendance in range
        $deleted_rows = $this->staffattendancemodel->delete_processed_attendance_between_dates($from_date, $to_date);

        // reprocess day by day
        $current = $from_date;
        $processed_days = 0;
        while (strtotime($current) <= strtotime($to_date)) {
            $this->_process_staff_attendance_from_punches($current);
            $processed_days++;
            $current = date('Y-m-d', strtotime($current . ' +1 day'));
        }

        // update last_processed_attendance_date to the to_date
        $setting = $this->setting_model->getSetting();
        if ($setting && isset($setting->id)) {
            $this->setting_model->add(['id' => $setting->id, 'last_processed_attendance_date' => $to_date]);
        }

        // Add summary log for reprocessing attendance between dates
        $admin_session = $this->session->userdata('admin');
        $user_name = !empty($admin_session['username']) ? $admin_session['username'] : 'System';
        $log_message = "Attendance reprocessed by {$user_name}: {$processed_days} day(s) from {$from_date} to {$to_date}, {$deleted_rows} old records deleted";
        $this->staffattendancemodel->log($log_message, null, 'Reprocess');

        ob_end_clean();
        echo json_encode(['status' => 'success', 'message' => 'Attendance reprocessed for selected range', 'processed_days' => $processed_days, 'deleted_rows' => $deleted_rows]);
        return;
    }

    /**
     * Check if a given date is an official holiday
     * @param string $date Date in Y-m-d format
     * @return bool True if the date is an official holiday, false otherwise
     */
    private function _is_official_holiday($date)
    {
        try {
            $holidays = $this->holiday_model->get();
            
            foreach ($holidays as $holiday) {
                $from_date = new DateTime($holiday['from_date']);
                $to_date = new DateTime($holiday['to_date']);
                $check_date = new DateTime($date);
                
                if ($check_date >= $from_date && $check_date <= $to_date) {
                    return true;
                }
            }
        } catch (Exception $e) {
            $this->logger->log("Error checking holiday for date {$date}: " . $e->getMessage());
        }
        
        return false;
    }

    /**
     * AJAX endpoint - evaluate attendance for a single staff/date using supplied in/out times (non-persistent).
     * Returns JSON: { status: 'success', data: { attendance_type_id, in_time, out_time, total_hours_worked, session, remark, pending_out_punch } }
     */
    public function ajax_evaluate_attendance()
    {
        if (!($this->rbac->hasPrivilege('staff_attendance', 'can_view'))) {
            echo json_encode(['status' => 'fail', 'message' => 'Access Denied']);
            return;
        }

        $staff_id = $this->input->post('staff_id');
        $date     = $this->input->post('date');
        $in_time  = $this->input->post('in_time');
        $out_time = $this->input->post('out_time');
        $is_final = $this->input->post('is_final') ? true : false;

        if (empty($staff_id) || empty($date)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
            return;
        }

        // normalize date (accepts UI date format) to YYYY-MM-DD
        try {
            $dateStr = date('Y-m-d', $this->customlib->datetostrtotime($date));
        } catch (Exception $e) {
            $dateStr = $date;
        }

        $this->load->model('attendance_model');
        $result = $this->attendance_model->evaluate_attendance_for_times($staff_id, $dateStr, $in_time, $out_time, $is_final);

        if ($result === null) {
            echo json_encode(['status' => 'fail', 'message' => 'Unable to evaluate attendance']);
        } else {
            echo json_encode(['status' => 'success', 'data' => $result]);
        }
    }

    /**
     * AJAX endpoint — persist manual in/out punches for a staff/date, reprocess attendance for that date,
     * and return the updated attendance data for UI refresh. (Used by auto‑save on time change.)
     */
    public function ajax_save_and_process_attendance()
    {
        if (!($this->rbac->hasPrivilege('staff_attendance', 'can_add') || $this->rbac->hasPrivilege('staff_attendance', 'can_edit'))) {
            echo json_encode(['status' => 'fail', 'message' => 'Access Denied']);
            return;
        }

        $staff_id = $this->input->post('staff_id');
        $date     = $this->input->post('date');
        $in_time  = $this->input->post('in_time');
        $out_time = $this->input->post('out_time');

        if (empty($staff_id) || empty($date)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid request']);
            return;
        }

        // normalize date to YYYY-MM-DD
        try {
            $dateStr = date('Y-m-d', $this->customlib->datetostrtotime($date));
        } catch (Exception $e) {
            $dateStr = $date;
        }

        // ensure times are saved as H:i:s or null
        $in_time_val = (!isset($in_time) || trim($in_time) === '') ? null : date('H:i:s', strtotime($in_time));
        $out_time_val = (!isset($out_time) || trim($out_time) === '') ? null : date('H:i:s', strtotime($out_time));

        // fetch existing punches for the staff/date
        $existing = $this->staff_biometric_punches_model->get_punches_by_staff_and_date($staff_id, $dateStr);
        $existing_times = array_map(function($r){ return date('Y-m-d H:i:s', strtotime($r['punch_time'])); }, $existing);

        // insert manual punches if they do not exist
        if (!empty($in_time_val)) {
            $punch_dt = $dateStr . ' ' . $in_time_val;
            if (!in_array($punch_dt, $existing_times, true)) {
                $this->staff_biometric_punches_model->add([
                    'staff_id' => $staff_id,
                    'punch_time' => $punch_dt,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        if (!empty($out_time_val)) {
            $punch_dt = $dateStr . ' ' . $out_time_val;
            if (!in_array($punch_dt, $existing_times, true)) {
                $this->staff_biometric_punches_model->add([
                    'staff_id' => $staff_id,
                    'punch_time' => $punch_dt,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // Re-run the attendance processor for this date — only for the target staff (faster & isolated)
        $this->_process_staff_attendance_from_punches($dateStr, $staff_id);

        // Load updated data for UI
        $this->load->model('attendance_model');
        $evaluated = $this->attendance_model->evaluate_attendance_for_times($staff_id, $dateStr, $in_time_val, $out_time_val, false);
        $raw_punches = $this->attendance_model->get_raw_biometric_punches_by_staff_id_and_date($staff_id, $dateStr);
        $db_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $dateStr);

        // Append audit remark (who & when) to staff_attendance.remark for manual punch saves
        try {
            $current_user = $this->customlib->getUserData();
            $auditor_name = isset($current_user['name']) ? $current_user['name'] : (isset($current_user['firstname']) ? $current_user['firstname'] : '');
            $auditor_role = isset($current_user['user_type']) ? $current_user['user_type'] : '';
            $timestamp = date('d/m/Y h:i:s a'); // matches example: 15/02/2026 04:42:33 pm
            $audit_text = $auditor_name . '(' . $auditor_role . ') updated on ' . $timestamp . '.';

            if ($db_attendance && isset($db_attendance['id'])) {
                $existing_remark = trim(isset($db_attendance['remark']) ? $db_attendance['remark'] : '');
                $new_remark = $existing_remark !== '' ? $existing_remark . ' | ' . $audit_text : $audit_text;
                // persist updated remark
                $this->staffattendancemodel->add(['id' => $db_attendance['id'], 'remark' => $new_remark]);
                // refresh local copy
                $db_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $dateStr);
            } else {
                // No attendance row exists (unlikely after processing) - create one with remark
                $insert = [
                    'staff_id' => $staff_id,
                    'remark' => $audit_text,
                    'date' => $dateStr,
                ];
                $this->staffattendancemodel->add($insert);
                $db_attendance = $this->staffattendancemodel->getAttendanceByStaffIdAndDate($staff_id, $dateStr);
            }
        } catch (Exception $e) {
            // do not block the main flow if remark append fails; just log
            log_message('error', 'Failed to append audit remark for staffattendance: ' . $e->getMessage());
        }

        $response = [
            'evaluated' => $evaluated,
            'raw_punches' => $raw_punches,
            'db_attendance' => $db_attendance,
        ];

        echo json_encode(['status' => 'success', 'data' => $response]);
    }

}