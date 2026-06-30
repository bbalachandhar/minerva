<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cron extends MY_Controller
{

    protected $cron_key;

    /**
     * This is default constructor of the class
     */
    public function __construct($key = "")
    {
        parent::__construct();
        $setting_result = $this->setting_model->getSetting();
        $this->cron_key = $setting_result->cron_secret_key;
        $this->load->model('feereminder_model');
        $this->load->model('calendar_model');
        $this->load->model('customfield_model');
        $this->load->model('class_section_time_model');
        $this->load->model('stuattendence_model');
        $this->load->model('student_model');
        $this->load->model('staff_model');
        $this->load->library('customlib');
        $this->load->helper('custom');
        if ($this->customlib->getTimeZone()) {
            date_default_timezone_set($this->customlib->getTimeZone());
        } else {
            return date_default_timezone_set('UTC');
        }
    }

    public function index($key = '')
    {
        if ($key != "" && $this->cron_key == $key) {

            $this->autobackup($key);
            $this->feereminder($key);
            $this->eventreminder($key);
            $this->schedulesmsemails($key);
        } else {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }
    }

    public function student_attendance($key=""){

           if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }

          $time=date('H:i:s');
          $date=date('Y-m-d');
          $class_sections=$this->class_section_time_model->getAttendanceNotSubmittedByTime($date,$time);
         
          if(!empty($class_sections)){
            $attendance_data=array();
                foreach ($class_sections as $class_key => $class_value) {
                    $attendance_data[]=[
                                        'student_session_id'=>$class_value->student_session_id,
                                        'date'=>date('Y-m-d'),
                                        'attendence_type_id'=>4,
                                      ];
             
                }
            $this->stuattendence_model->batch_insert($attendance_data);
          }

    }

    public function autobackup($key = '')
    {
        if ($key != "") {
            if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }

            $this->load->dbutil();
            $version  = $this->customlib->getAppVersion();
            $filename = "db_ver_" . $version . '_' . date("Y-m-d_H-i-s") . ".sql";
            $prefs    = array(
                'ignore'     => array(),
                'format'     => 'txt',
                'filename'   => 'mybackup.sql',
                'add_drop'   => true,
                'add_insert' => true,
                'newline'    => "\n",
            );
            $backup = $this->dbutil->backup($prefs);
            $this->load->helper('file');
            write_file('./backup/database_backup/' . $filename, $backup);
        }
    }

    public function feereminder($key = "")
    {
        $setting_result = $this->setting_model->getSetting();
        if ($key != "") {
            if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }
       
            $this->load->library('mailsmsconf');
            $feereminder   = $this->feereminder_model->get(null, 1);

            $reminter_type = array();
            $studentList   = array();

            if (!empty($feereminder)) {
                foreach ($feereminder as $feereminder_key => $feereminder_value) {
                    if ($feereminder_value->reminder_type == "before") {

                        $date               = date('Y-m-d', strtotime('+' . $feereminder_value->day . ' days'));
                        $fees_type_reminder = $this->feegrouptype_model->getFeeTypeDueDateReminder($date);


                        if (!empty($fees_type_reminder)) {

                            foreach ($fees_type_reminder as $reminder_key => $reminder_value) {

                                $students = $this->feegrouptype_model->getFeeTypeStudents($reminder_value->fee_session_group_id, $reminder_value->id);

                                foreach ($students as $student_key => $student_value) {
                                    $students[$student_key]->{'fee_category'}       = "fees";       
                                    $students[$student_key]->{'fee_group_name'}       = $reminder_value->fee_group_name;
                                    $students[$student_key]->{'due_date'}       = $date;
                                    $students[$student_key]->{'fee_type'}       = $reminder_value->type;
                                    $students[$student_key]->{'fee_code'}       = $reminder_value->code;
                                    $students[$student_key]->{'fee_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'due_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($student_value->amount_detail);
                                    if (json_last_error() == JSON_ERROR_NONE) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {
                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $students[$student_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $students[$student_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };
                                    $students[$student_key]->{'student_name'} = $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $studentList[]                            = $student_value;
                                }
                            }
                        }
                        $dt="2022-09-09";
                       $transport_fees= $this->studentfeemaster_model->getTransportFeesByDueDate($dt, $dt);               


                           if (!empty($transport_fees)) {

                            foreach ($transport_fees as $reminder_key => $reminder_value) {

                                    $transport_fees[$reminder_key]->{'fee_category'}       ="transport";
                                    $transport_fees[$reminder_key]->{'fee_group_name'}   = "Transport";
                                    $transport_fees[$reminder_key]->{'due_date'}       = $date;
                                    $transport_fees[$reminder_key]->{'fee_type'}       = $reminder_value->month;
                                    $transport_fees[$reminder_key]->{'fee_code'}       = "-";
                                    $transport_fees[$reminder_key]->{'fee_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'due_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($reminder_value->amount_detail);
                                    if (isJSON($reminder_value->amount_detail)) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {
                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $transport_fees[$reminder_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };
                                    $transport_fees[$reminder_key]->{'student_name'} = $this->customlib->getFullName($reminder_value->firstname, $reminder_value->middlename, $reminder_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $studentList[]                            = $reminder_value;
                               
                            }
                        }


                    } else if ($feereminder_value->reminder_type == "after") {

                        $date               = date('Y-m-d', strtotime('-' . $feereminder_value->day . ' days'));
                        $fees_type_reminder = $this->feegrouptype_model->getFeeTypeDueDateReminder($date);

                        if (!empty($fees_type_reminder)) {
                            foreach ($fees_type_reminder as $reminder_key => $reminder_value) {

                                $students = $this->feegrouptype_model->getFeeTypeStudents($reminder_value->fee_session_group_id, $reminder_value->id);

                                foreach ($students as $student_key => $student_value) {
                                    $students[$student_key]->{'fee_category'}       = "fees";
                                    $students[$student_key]->{'due_date'}       = $date;
                                    $students[$student_key]->{'fee_group_name'}       = $reminder_value->fee_group_name;
                                    $students[$student_key]->{'fee_type'}       = $reminder_value->type;
                                    $students[$student_key]->{'fee_code'}       = $reminder_value->code;
                                    $students[$student_key]->{'fee_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'due_amount'}     = $reminder_value->amount;
                                    $students[$student_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($student_value->amount_detail);
                                    if (json_last_error() == JSON_ERROR_NONE) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {

                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $students[$student_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $students[$student_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };

                                    $students[$student_key]->{'student_name'} = $this->customlib->getFullName($student_value->firstname, $student_value->middlename, $student_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $students[$student_key]->{'school_name'}  = $this->customlib->getSchoolName();
                                    $studentList[]                            = $student_value;
                                }
                            }
                        }

                                   $dt="2022-09-09";
                       $transport_fees= $this->studentfeemaster_model->getTransportFeesByDueDate($dt, $dt);
					   
                           if (!empty($transport_fees)) {

                            foreach ($transport_fees as $reminder_key => $reminder_value) {

                                    $transport_fees[$reminder_key]->{'fee_category'}       ="transport";
                                    $transport_fees[$reminder_key]->{'fee_group_name'}   = "Transport";
                                    $transport_fees[$reminder_key]->{'due_date'}       = $date;
                                    $transport_fees[$reminder_key]->{'fee_type'}       = $reminder_value->month;
                                    $transport_fees[$reminder_key]->{'fee_code'}       = "-";
                                    $transport_fees[$reminder_key]->{'fee_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'due_amount'}     = $reminder_value->fees;
                                    $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) 0, 2, '.', '');
                                    $fees_array                                 = json_decode($reminder_value->amount_detail);
                                    if (isJSON($reminder_value->amount_detail)) {
                                        $deposit_amount = 0;
                                        foreach ($fees_array as $fee_collected_key => $fee_collected_value) {
                                            $deposit_amount = $deposit_amount + ($fee_collected_value->amount + $fee_collected_value->amount_discount);
                                        };
                                        $transport_fees[$reminder_key]->{'deposit_amount'} = number_format((float) ($deposit_amount), 2, '.', '');
                                        $transport_fees[$reminder_key]->{'due_amount'}     = number_format((float) ($reminder_value->amount - $deposit_amount), 2, '.', '');
                                    };
                                    $transport_fees[$reminder_key]->{'student_name'} = $this->customlib->getFullName($reminder_value->firstname, $reminder_value->middlename, $reminder_value->lastname, $setting_result->middlename, $setting_result->lastname);
                                    $studentList[]                            = $reminder_value;
                               
                            }
                        }
                    }
                }

                if (!empty($studentList)) {
                    foreach ($studentList as $eachStudent_key => $eachStudent_value) {
                        if ($eachStudent_value->due_amount <= 0) {
                            unset($studentList[$eachStudent_key]);
                        }
                    }
                }

                if (!empty($studentList)) {
                    foreach ($studentList as $eachStudent_key => $eachStudent_value) {

                        $this->mailsmsconf->mailsms('fees_reminder', $eachStudent_value);
                    
                    }
                }
            }
        }
    }

    public function eventreminder($key = "")
    {
        $setting_result = $this->setting_model->getSetting();

        if ($key != "") {
            if ($key != "" && $this->cron_key != $key) {
                echo "Invalid Key or Direct access is not allowed";
                return;
            }
            $this->load->library('mailsmsconf');

            $event_reminder = array();

            if ($setting_result->event_reminder == "enabled") {

                $date = date('Y-m-d', strtotime('+' . $setting_result->calendar_event_reminder . ' days'));

                $event_reminder = $this->calendar_model->geteventreminder($date);

                if (!empty($event_reminder)) {
                    foreach ($event_reminder as $event_reminder_key => $event_reminder_value) {

                        if ($event_reminder_value['event_type'] == 'private') {
                            $event_email = $this->staff_model->getstaffemail($event_reminder_value['event_for']);
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        } else if ($event_reminder_value['event_type'] == 'sameforall') {
                            $event_email = $this->staff_model->getEmployee($event_reminder_value['event_for'], 1);
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        } else if ($event_reminder_value['event_type'] == 'public') {
                            $event_email = $this->calendar_model->getstaffandstudentemail();
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        } else if ($event_reminder_value['event_type'] == 'protected') {
                            $event_email = $this->staff_model->searchFullText("", 1);
                            if (!empty($event_email)) {
                                foreach ($event_email as $event_email_key => $event_email_value) {
                                    $event_reminder[$event_reminder_key]['event_email_list'][] = $event_email_value['email'];
                                }
                            }
                        }

                        if (!empty($event_reminder)) {
                            foreach ($event_reminder as $event_reminder_value) {
                                $this->mailsmsconf->sendEailEventReminder($event_reminder_value);
                            }
                        }
                    }
                }
            }
        }
    }

    public function schedulesmsemails($key = "")
    {
        $this->load->library('mailer');
        $this->load->model('messages_model');
        $this->load->library('smsgateway');
        $userdata     = $this->messages_model->get_scheduledata(date('Y-m-d H:i'));
        
        $current_date = date('Y-m-d H:i:s');
        foreach ($userdata as $key => $value) {
               
            $user_list = json_decode($value['user_list'], true);

            if ($value['schedule_date_time'] <= $current_date) {
                $attachments = $this->messages_model->get_message_attachment($value['id']);                
 
                foreach ($user_list as $user_listkey => $user_listvalue) {
                    
                    if($user_listvalue['role']=='student'){
                        
                        $user_student   =   $this->student_model->getstudentdetailbyid($user_listvalue['user_id']);
                        $email  =   $user_student['email'];
                        $phone  =   $user_student['mobileno'];
                        
                    }elseif($user_listvalue['role']=='parent'){
                        
                        $user_parent   =   $this->student_model->getstudentdetailbyid($user_listvalue['user_id']);
                        $email  =   $user_parent['guardian_email'];
                        $phone  =   $user_parent['guardian_phone'];
                        
                    }elseif($user_listvalue['role']=='staff'){
                        
                        $user_staff   =   $this->staff_model->getProfile($user_listvalue['user_id']);
                        $email  =   $user_staff['email'];
                        $phone  =   $user_staff['contact_no'];
                        
                    }                 
                    
                    if (!empty($email) && $value['send_mail'] == 1) {
                        $this->mailer->compose_mail($email, $value['title'], $value['message'], $attachments);
                    }

                    if (!empty($phone) && $value['send_sms'] == 1) {
                        $this->smsgateway->sendSMS($phone, $value['message'], $value['title']);
                    }

                }
                
                $insert['id']   = $value['id'];
                $insert['sent'] = 1;
                $this->messages_model->add($insert);

            }
        }
    }

    /**
     * Vehicle expiry reminder cron.
     * Sends email + WhatsApp to the 3 configured staff assignees when any vehicle
     * validity end-date is exactly 15, 10, or 5 days away.
     *
     * URL: http://yourdomain.com/index.php/cron/vehicleExpiryReminder/{cron_secret_key}
     * Crontab (daily at 08:00):
     *   0 8 * * * curl -s "https://yourdomain.com/index.php/cron/vehicleExpiryReminder/{key}" > /dev/null 2>&1
     */
    /**
     * One-shot test: send a digest of all vehicles expiring in next 60 days
     * to all configured assignees, regardless of day thresholds.
     * URL: /cron/vehicleExpiryTest/{cron_key}
     */
    public function vehicleExpiryTest($key = '')
    {
        if ($key == "" || $this->cron_key != $key) {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }

        $this->load->model('vehicle_model');
        $this->load->library('mailer');

        $setting     = $this->setting_model->getSetting();
        $school_name = $setting->name ?? 'School';
        $date_fmt    = $this->customlib->getSchoolDateFormat();

        $assignees = $this->vehicle_model->getAssignees();
        if (empty($assignees)) { echo "No assignees configured.\n"; return; }

        $upcoming = $this->vehicle_model->getUpcomingExpiries(60);
        if (empty($upcoming)) { echo "No vehicles expiring in the next 60 days.\n"; return; }

        $rows = '';
        foreach ($upcoming as $v) {
            $d     = (int)$v['days_remaining'];
            $color = $d <= 5 ? '#d9534f' : ($d <= 15 ? '#e67e22' : '#3498db');
            $exp   = $v['expiry_date'] ? date($date_fmt, strtotime($v['expiry_date'])) : '—';
            $rows .= "<tr>
              <td style='padding:8px 12px;border:1px solid #e0e0e0;'><strong>{$v['vehicle_no']}</strong></td>
              <td style='padding:8px 12px;border:1px solid #e0e0e0;'>{$v['vehicle_model']}</td>
              <td style='padding:8px 12px;border:1px solid #e0e0e0;'>{$v['registration_number']}</td>
              <td style='padding:8px 12px;border:1px solid #e0e0e0;'>{$v['expiry_label']}</td>
              <td style='padding:8px 12px;border:1px solid #e0e0e0;'>{$exp}</td>
              <td style='padding:8px 12px;border:1px solid #e0e0e0;text-align:center;'>
                <span style='background:{$color};color:#fff;padding:3px 10px;border-radius:12px;font-weight:700;font-size:12px;'>{$d} day(s)</span>
              </td></tr>";
        }

        $cnt     = count($upcoming);
        $subject = "[TEST] [{$school_name}] Vehicle Expiry — {$cnt} document(s) expiring in next 60 days";
        $body    = "<html><body style='font-family:Arial,sans-serif;background:#f5f5f5;'>
<div style='max-width:680px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);'>
  <div style='background:#f0ad4e;padding:18px 24px;'>
    <h2 style='color:#fff;margin:0;'>&#128276; Vehicle Expiry Alert &mdash; TEST EMAIL</h2>
    <p style='color:#fff;margin:4px 0 0;opacity:.9;font-size:13px;'>{$school_name}</p>
  </div>
  <div style='padding:24px;'>
    <p style='font-size:14px;color:#555;'>
      This is a <strong>test notification</strong> listing all {$cnt} document(s) expiring in the next 60 days.<br>
      <em>In production, alerts fire only on the configured day thresholds before expiry.</em>
    </p>
    <table style='width:100%;border-collapse:collapse;font-size:13px;'>
      <thead><tr style='background:#f8f8f8;'>
        <th style='padding:9px 12px;border:1px solid #e0e0e0;text-align:left;'>Vehicle No.</th>
        <th style='padding:9px 12px;border:1px solid #e0e0e0;text-align:left;'>Model</th>
        <th style='padding:9px 12px;border:1px solid #e0e0e0;text-align:left;'>Registration</th>
        <th style='padding:9px 12px;border:1px solid #e0e0e0;text-align:left;'>Document</th>
        <th style='padding:9px 12px;border:1px solid #e0e0e0;text-align:left;'>Expiry Date</th>
        <th style='padding:9px 12px;border:1px solid #e0e0e0;text-align:center;'>Days Left</th>
      </tr></thead>
      <tbody>{$rows}</tbody>
    </table>
    <p style='color:#aaa;font-size:11px;margin-top:20px;border-top:1px solid #eee;padding-top:14px;'>
      Automated test from <strong>{$school_name}</strong> ERP Vehicle Expiry Alerts.
    </p>
  </div>
</div></body></html>";

        $sent = 0;
        foreach ($assignees as $a) {
            if (empty($a['email'])) continue;
            $this->mailer->compose_mail($a['email'], $subject, $body);
            echo "Sent to: {$a['name']} <{$a['email']}>\n";
            $sent++;
        }
        echo "\nTest complete. Emails sent: {$sent}\n";
    }

    public function vehicleExpiryReminder($key = '')
    {
        if ($key == "" || $this->cron_key != $key) {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }

        $this->load->model('vehicle_model');
        $this->load->library('mailer');

        $setting     = $this->setting_model->getSetting();
        $school_name = $setting->name ?? 'School';
        $date_fmt    = $this->customlib->getSchoolDateFormat();

        // Read admin-configurable notification days (default: 30,15,5,3)
        $days_str      = $this->vehicle_model->getNotificationConfig('notify_days') ?: '30,15,5,3';
        $enable_email  = (int)($this->vehicle_model->getNotificationConfig('enable_email') ?? 1);
        $wa_template   = $this->vehicle_model->getNotificationConfig('wa_template_id');
        $reminder_days = array_values(array_filter(array_map('intval', explode(',', $days_str))));

        if (empty($reminder_days)) {
            echo "No notification days configured.\n"; return;
        }

        $assignees = $this->vehicle_model->getAssignees();
        if (empty($assignees)) {
            echo "No assignees configured. Skipping.\n"; return;
        }

        $total_sent = 0;

        foreach ($reminder_days as $days) {
            $expiring = $this->vehicle_model->getExpiringVehicles($days);
            if (empty($expiring)) continue;

            foreach ($expiring as $v) {
                $raw_date    = $v[$v['expiry_field']] ?? '';
                $expiry_fmt  = $raw_date ? date($date_fmt, strtotime($raw_date)) : $raw_date;
                $urgency_hex = $days <= 5 ? '#d9534f' : ($days <= 15 ? '#e67e22' : '#3498db');

                $subject = "[{$school_name}] Vehicle Alert: {$v['expiry_label']} expires in {$days} day(s) — {$v['vehicle_no']}";

                $body = "
<html><body style='font-family:Arial,sans-serif;margin:0;padding:0;background:#f5f5f5;'>
<div style='max-width:600px;margin:24px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);'>
  <div style='background:{$urgency_hex};padding:20px 24px;'>
    <h2 style='color:#fff;margin:0;font-size:20px;'>&#9888;&#65039; Vehicle Document Expiry Alert</h2>
    <p style='color:#fff;margin:6px 0 0;opacity:.9;font-size:13px;'>{$school_name}</p>
  </div>
  <div style='padding:24px;'>
    <p style='font-size:15px;color:#333;'>The <strong>{$v['expiry_label']}</strong> document for vehicle
       <strong>{$v['vehicle_no']}</strong> is expiring in
       <strong style='color:{$urgency_hex};'>{$days} day(s)</strong> on
       <strong>{$expiry_fmt}</strong>. Please renew it immediately.</p>

    <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;'>
      <tr style='background:#f8f8f8;'><td style='padding:10px 14px;font-weight:bold;border:1px solid #e0e0e0;width:40%;'>Vehicle No.</td><td style='padding:10px 14px;border:1px solid #e0e0e0;'><strong>{$v['vehicle_no']}</strong></td></tr>
      <tr><td style='padding:10px 14px;font-weight:bold;border:1px solid #e0e0e0;'>Model</td><td style='padding:10px 14px;border:1px solid #e0e0e0;'>{$v['vehicle_model']}</td></tr>
      <tr style='background:#f8f8f8;'><td style='padding:10px 14px;font-weight:bold;border:1px solid #e0e0e0;'>Registration No.</td><td style='padding:10px 14px;border:1px solid #e0e0e0;'>{$v['registration_number']}</td></tr>
      <tr><td style='padding:10px 14px;font-weight:bold;border:1px solid #e0e0e0;'>Document</td><td style='padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;color:{$urgency_hex};'>{$v['expiry_label']}</td></tr>
      <tr style='background:#fff8e1;'><td style='padding:10px 14px;font-weight:bold;border:1px solid #e0e0e0;'>Expiry Date</td><td style='padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;font-size:16px;color:{$urgency_hex};'>{$expiry_fmt}</td></tr>
      <tr><td style='padding:10px 14px;font-weight:bold;border:1px solid #e0e0e0;'>Days Remaining</td><td style='padding:10px 14px;border:1px solid #e0e0e0;font-weight:bold;color:{$urgency_hex};'>{$days} day(s)</td></tr>
    </table>

    <p style='color:#888;font-size:12px;margin-top:20px;border-top:1px solid #eee;padding-top:14px;'>
      This is an automated alert from <strong>{$school_name}</strong> ERP.<br>
      Please log in to the transport module to update the document details after renewal.
    </p>
  </div>
</div>
</body></html>";

                $wa_vars = [
                    'vehicle_no'      => $v['vehicle_no'],
                    'vehicle_model'   => $v['vehicle_model'],
                    'registration_no' => $v['registration_number'],
                    'expiry_type'     => $v['expiry_label'],
                    'expiry_date'     => $expiry_fmt,
                    'days_remaining'  => (string)$days,
                ];

                foreach ($assignees as $assignee) {
                    if ($enable_email && !empty($assignee['email'])) {
                        $this->mailer->compose_mail($assignee['email'], $subject, $body);
                        $total_sent++;
                    }
                    if (!empty($wa_template) && !empty($assignee['contact_no'])) {
                        $this->load->library('whatsappgateway');
                        $phone = preg_replace('/\D/', '', $assignee['contact_no']);
                        $this->whatsappgateway->sendVehicleExpiryReminder($phone, $wa_vars, $wa_template);
                    }
                }
            }
        }

        echo "Vehicle expiry reminder completed. Days checked: " . implode(',', $reminder_days)
           . ". Emails sent: {$total_sent}\n";
    }

    /**
     * One-time bulk email: send online exam login credentials to all assigned applicants.
     *
     * URL: https://yourdomain.com/index.php/cron/sendExamInvitations/{cron_secret_key}/{exam_id}
     *
     * exam_id : the ID from the `onlineexam` table (default 1 if omitted)
     */
    public function sendExamInvitations($key = '', $exam_id = 1)
    {
        if ($key == '' || $this->cron_key != $key) {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }

        $exam_id = (int) $exam_id;
        if ($exam_id <= 0) {
            echo "Invalid exam_id";
            return;
        }

        $this->load->model('onlineexam_model');
        $this->load->library('mailer');

        // Fetch exam details
        $this->db->from('onlineexam');
        $this->db->where('id', $exam_id);
        $exam = $this->db->get()->row_array();

        if (empty($exam)) {
            echo "Exam not found for id={$exam_id}";
            return;
        }

        $exam_name   = $exam['exam'];
        $exam_from   = !empty($exam['exam_from'])  ? date('d M Y, h:i A', strtotime($exam['exam_from']))  : 'TBD';
        $exam_to     = !empty($exam['exam_to'])    ? date('d M Y, h:i A', strtotime($exam['exam_to']))    : 'TBD';
        $duration    = $exam['duration'];
        $login_url   = site_url('site/applicantlogin');
        $current_year = date('Y');

        // Fetch all applicants assigned to this exam
        $this->db->select('oa.id, oa.reference_no, oa.firstname, oa.lastname, oa.email, oa.mobileno, oa.form_status');
        $this->db->from('onlineexam_students es');
        $this->db->join('online_admissions oa', 'oa.id = es.online_admission_id');
        $this->db->where('es.onlineexam_id', $exam_id);
        $this->db->where('es.candidate_type', 'applicant');
        $this->db->order_by('oa.id', 'asc');
        $applicants = $this->db->get()->result_array();

        if (empty($applicants)) {
            echo "No applicants assigned to exam id={$exam_id}";
            return;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($applicants as $app) {
            $email = trim($app['email']);
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $firstname = trim($app['firstname']);
            $lastname  = trim($app['lastname']);
            $name      = trim($firstname . ' ' . $lastname);
            $username  = $app['reference_no'];
            $password  = $username . '@ApplicantPortal' . $current_year;

            $subject = 'Online Scholarship Exam – Login Credentials & Exam Details';

            $message = '
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;border:1px solid #e0e0e0;border-radius:6px;">
  <h2 style="color:#1F4E79;margin-bottom:4px;">Online Scholarship Exam</h2>
  <p style="color:#555;margin-top:0;">' . htmlspecialchars($exam_name) . '</p>
  <hr style="border:none;border-top:1px solid #e0e0e0;">
  <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
  <p>You have been registered for the <strong>' . htmlspecialchars($exam_name) . '</strong>. Please find the exam details and your login credentials below.</p>

  <table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <tr style="background:#f4f8fc;">
      <td style="padding:8px 12px;font-weight:bold;width:40%;">Exam Period</td>
      <td style="padding:8px 12px;">' . htmlspecialchars($exam_from) . ' &ndash; ' . htmlspecialchars($exam_to) . '</td>
    </tr>
    <tr>
      <td style="padding:8px 12px;font-weight:bold;">Duration</td>
      <td style="padding:8px 12px;">' . htmlspecialchars($duration) . ' hours</td>
    </tr>
    <tr style="background:#f4f8fc;">
      <td style="padding:8px 12px;font-weight:bold;">Username</td>
      <td style="padding:8px 12px;font-family:monospace;">' . htmlspecialchars($username) . '</td>
    </tr>
    <tr>
      <td style="padding:8px 12px;font-weight:bold;">Password</td>
      <td style="padding:8px 12px;font-family:monospace;">' . htmlspecialchars($password) . '</td>
    </tr>
    <tr style="background:#f4f8fc;">
      <td style="padding:8px 12px;font-weight:bold;">Login URL</td>
      <td style="padding:8px 12px;"><a href="' . $login_url . '" style="color:#1F4E79;">' . $login_url . '</a></td>
    </tr>
  </table>

  <p style="background:#fff8e1;border-left:4px solid #f0a500;padding:10px 14px;border-radius:4px;">
    Please log in before the exam window opens to verify your credentials. Keep this email for your reference.
  </p>
  <p style="color:#888;font-size:12px;margin-top:24px;">This is an automated email. Please do not reply.</p>
</div>';

            $this->mailer->compose_mail($email, $subject, $message);
            $sent++;
        }

        echo "Exam invitation emails sent: {$sent} | Skipped (no valid email): {$skipped} | Total applicants: " . count($applicants) . "\n";
    }

    /**
     * Cleanup old log/audit records to keep DB size in check.
     * Deletes: logs, userlog, gateway_ins (non-pending) older than 3 months.
     * Also cleans CI application log files older than 7 days.
     *
     * URL: /cron/cleanup_logs/{cron_secret_key}
     * Cron: monthly (1st of each month at 2 AM)
     */
    public function cleanup_logs($key = '')
    {
        if ($key == '' || $this->cron_key != $key) {
            echo "Invalid Key or Direct access is not allowed";
            return;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime('-3 months'));
        $results = [];

        // 1. Activity logs
        $this->db->where('time <', $cutoff);
        $this->db->delete('logs');
        $results['logs'] = $this->db->affected_rows();

        // 2. User login logs
        $this->db->where('login_datetime <', $cutoff);
        $this->db->delete('userlog');
        $results['userlog'] = $this->db->affected_rows();

        // 3. Gateway payment dumps (keep pending/processing, delete old completed/failed)
        $this->db->where('created_at <', $cutoff);
        $this->db->where_in('payment_status', ['success', 'failed']);
        $this->db->delete('gateway_ins');
        $results['gateway_ins'] = $this->db->affected_rows();

        // 4. Old gateway responses
        if ($this->db->table_exists('gateway_ins_response')) {
            $this->db->where('created_at <', $cutoff);
            $this->db->delete('gateway_ins_response');
            $results['gateway_ins_response'] = $this->db->affected_rows();
        }

        // 5. Clean CI application log files older than 7 days
        $log_path = APPPATH . 'logs/';
        $files_deleted = 0;
        if (is_dir($log_path)) {
            $cutoff_file = strtotime('-7 days');
            foreach (glob($log_path . 'log-*.php') as $file) {
                if (filemtime($file) < $cutoff_file) {
                    if (unlink($file)) {
                        $files_deleted++;
                    }
                }
            }
        }
        $results['log_files_deleted'] = $files_deleted;

        $summary = "DB cleanup complete (cutoff: {$cutoff}): ";
        foreach ($results as $table => $count) {
            $summary .= "{$table}={$count} ";
        }
        echo $summary . "\n";
    }

}
