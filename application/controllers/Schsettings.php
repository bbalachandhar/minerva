<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Schsettings extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->library('upload');
        $this->load->library('form_validation');
        $this->load->model(array('class_section_time_model','sidebarmenu_model','staffAttendaceSetting_model','attendencetype_model','studentAttendaceSetting_model'));
    }

    public function index()
    { 
        if (!$this->rbac->hasPrivilege('general_setting', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/index');

        $timezoneList             = $this->customlib->timezone_list();
        $session_result           = $this->session_model->get();
        $data['sessionlist']      = $session_result;
        $currency_formats         = $this->customlib->currency_format();
        $month_list               = $this->customlib->getMonthList();
        $days_list                = $this->customlib->getDayList();
        $data['currency_formats'] = $currency_formats;
        $data['daysList']         = $days_list;
        $data['timezoneList']     = $timezoneList;
        $data['monthList']        = $month_list;
        $dateFormat               = $this->customlib->getDateFormat();
        $currency                 = $this->customlib->getCurrency();
        $data['dateFormatList']   = $dateFormat;
        $data['currencyList']     = $currency;
        $currencyPlace            = $this->customlib->getCurrencyPlace();
        $data['currencyPlace']    = $currencyPlace;
        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']           = $setting;
        
        $this->load->model('staff_model');
        $data['staff_list'] = $this->staff_model->get(null, 1);
        
        $this->load->model('leavetypes_model');
        $data['leave_types'] = $this->leavetypes_model->getLeaveType();
        
        // Fetch monthly leave increment rules
        $data['leave_increment_rules'] = $this->get_leave_increment_rules();
	 
        $this->load->view('layout/header', $data);
        $this->load->view('setting/settingList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function ajax_editlogo()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->input->post('id');

            $setting = $this->setting_model->getSetting();

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {

                $upload_result = $this->media_storage->fileupload("file", "./uploads/school_content/logo/");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
            } else {
                $img_name = $setting->image;
            }
            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {

                $this->media_storage->filedelete($setting->image, "uploads/school_content/logo");
            }

            $data_record = array('id' => $id, 'image' => $img_name);
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function ajax_editadmin_smalllogo()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->input->post('id');

            $setting = $this->setting_model->getSetting();

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {

                $upload_result = $this->media_storage->fileupload("file", "./uploads/school_content/admin_small_logo/");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
            } else {
                $img_name = $setting->admin_small_logo;
            }
            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {

                $this->media_storage->filedelete($setting->admin_small_logo, "uploads/school_content/admin_small_logo");
            }
            $data_record = array('id' => $id, 'admin_small_logo' => $img_name);
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function ajax_editadmin_adminlogo()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->input->post('id');

            $setting = $this->setting_model->getSetting();

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {

                $upload_result = $this->media_storage->fileupload("file", "./uploads/school_content/admin_logo/");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
            } else {
                $img_name = $setting->admin_logo;
            }
            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                if ($setting->admin_logo != '') {
                    $this->media_storage->filedelete($setting->admin_logo, "uploads/school_content/admin_logo");
                }
            }

            $data_record = array('id' => $id, 'admin_logo' => $img_name);
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function editLogo($id)
    {
        $data['title']       = 'School Logo';
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $data['id']          = $id;
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('setting/editLogo', $data);
            $this->load->view('layout/footer', $data);
        } else {
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $fileInfo = pathinfo($_FILES["file"]["name"]);
                $img_name = $id . '.' . $fileInfo['extension'];
                $upload_dir = "./uploads/school_content/logo/";
                $this->customlib->ensureDirectoryExists($upload_dir);
                move_uploaded_file($_FILES["file"]["tmp_name"], $upload_dir . $img_name);
            }
            $data_record = array('id' => $id, 'image' => $img_name);
            $this->setting_model->add($data_record);
            $this->session->set_flashdata('msg', '<div class="alert alert-left">' . $this->lang->line('update_message') . '</div>');
            redirect('schsettings/index');
        }
    }

    public function handle_upload()
    {   
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png');
            $temp        = explode(".", $_FILES["file"]["name"]);
            $extension   = end($temp);            
            
            if ($_FILES["file"]["error"] > 0) {
                
                $error .= "Error opening the file<br />";
                
            }
            if ($_FILES["file"]["type"] != 'image/gif' &&
                $_FILES["file"]["type"] != 'image/jpeg' &&
                $_FILES["file"]["type"] != 'image/png') {
                
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
               
                $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($_FILES["file"]["size"] > 1024000) {
                
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . " 1MB");
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }

    public function view($id)
    {
        $data['title']   = 'Setting List';
        $setting         = $this->setting_model->get($id);
        $data['setting'] = $setting;
        $this->load->view('layout/header', $data);
        $this->load->view('setting/settingShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getSchsetting()
    {
        $data = $this->setting_model->getSetting();
        echo json_encode($data);
    }

    public function generalsetting()
    {
        $this->form_validation->set_rules('currency_format', $this->lang->line('currency_format'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_name', $this->lang->line('school_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_phone', $this->lang->line('phone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_start_month', $this->lang->line('start_month'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_address', $this->lang->line('address'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_email', $this->lang->line('email'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_timezone', $this->lang->line('timezone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('currency_place', $this->lang->line('currency_place'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_date_format', $this->lang->line('date_format'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_start_week', $this->lang->line('start_day_of_week'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('institution_type', 'Institution Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('transport_fee_type', 'Transport Fee Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_website', 'Website', 'trim|xss_clean');


        if ($this->form_validation->run() == false) {
            $data = array(
                'sch_session_id'  => form_error('sch_session_id'),
                'sch_name'        => form_error('sch_name'),
                'sch_phone'       => form_error('sch_phone'),
                'sch_start_month' => form_error('sch_start_month'),
                'sch_start_week'  => form_error('sch_start_week'),
                'sch_address'     => form_error('sch_address'),
                'sch_email'       => form_error('sch_email'),
                'sch_timezone'    => form_error('sch_timezone'),
                'currency_place'  => form_error('currency_place'),
                'currency_format' => form_error('currency_format'),
                'sch_date_format' => form_error('sch_date_format'),

            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {

            $data = array(
                'id'              => $this->input->post('sch_id'),
                'institution_type' => $this->input->post('institution_type'),
                'transport_fee_type' => $this->input->post('transport_fee_type'),
                'staff_self_edit' => $this->input->post('staff_self_edit') ? 1 : 0,
                'session_id'      => $this->input->post('sch_session_id'),
                'name'            => $this->input->post('sch_name'),
                'phone'           => $this->input->post('sch_phone'),
                'dise_code'       => $this->input->post('sch_dise_code'),
                'start_month'     => $this->input->post('sch_start_month'),
                'start_week'      => $this->input->post('sch_start_week'),
                'address'         => $this->input->post('sch_address'),
                'email'           => $this->input->post('sch_email'),
                'timezone'        => $this->input->post('sch_timezone'),
                'date_format'     => $this->input->post('sch_date_format'),
                'currency_format' => $this->input->post('currency_format'),
                'currency_place'  => $this->input->post('currency_place'),
                'base_url'        => $this->input->post('base_url'),
                'website'         => $this->input->post('sch_website'),
                'leave_approver_id' => $this->input->post('leave_approver_id'),
                'weekend_days'    => implode(',', $this->input->post('weekend_days') ?? ['0']),
                'isSecondSaturdayHoliday' => $this->input->post('isSecondSaturdayHoliday') ? 1 : 0,
                'monthly_leave_increment_enabled' => $this->input->post('monthly_leave_increment_enabled') ? 1 : 0,
                'monthly_increment_leave_type_id' => $this->input->post('monthly_increment_leave_type_id') ?: null,
                'monthly_increment_days' => $this->input->post('monthly_increment_days') ?: 1.00,
                'leave_reset_month' => $this->input->post('leave_reset_month') ?: 1,
            );
            
            if (isset($_FILES["admission_logo_left"]) && !empty($_FILES['admission_logo_left']['name'])) {
                $upload_result = $this->media_storage->fileupload("admission_logo_left", "./uploads/logos/");
                if ($upload_result['status']) {
                    $data['admission_logo_left'] = $upload_result['message'];
                }
            }

            if (isset($_FILES["admission_logo_right"]) && !empty($_FILES['admission_logo_right']['name'])) {
                $upload_result = $this->media_storage->fileupload("admission_logo_right", "./uploads/logos/");
                if ($upload_result['status']) {
                    $data['admission_logo_right'] = $upload_result['message'];
                }
            }

            $this->setting_model->add($data);

            $this->session->userdata['admin']['base_url']        = $this->input->post('base_url');

            $this->session->userdata['admin']['currency_format'] = $this->input->post('currency_format');
            $this->session->userdata['admin']['date_format']     = $this->input->post('sch_date_format');
            $this->session->userdata['admin']['start_week']      = date("w", strtotime($this->input->post('sch_start_week')));
            $this->session->userdata['admin']['timezone']        = $this->input->post('sch_timezone');
            $this->session->userdata['admin']['currency_place']  = $this->input->post('currency_place');
            $this->session->userdata['admin']['sch_name'] = $this->input->post('sch_name');
            $array                                               = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function ajax_applogo()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');

        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {

            $id      = $this->input->post('id');
            $setting = $this->setting_model->getSetting();

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {

                $upload_result = $this->media_storage->fileupload("file", "./uploads/school_content/logo/app_logo//");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
            } else {
                $img_name = $setting->app_logo;
            }
            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                if ($setting->app_logo != '') {
                    $this->media_storage->filedelete($setting->app_logo, "uploads/school_content/logo/app_logo/");
                }
            }

            $data_record = array('id' => $id, 'app_logo' => $img_name);

            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('update_message'));
            echo json_encode($array);
        }
    }
    
    public function ajax_edit_admission_left_logo()
    {
        log_message('debug', 'ajax_edit_admission_left_logo: Method called.');
        log_message('debug', 'ajax_edit_admission_left_logo: POST data: ' . print_r($_POST, true));
        log_message('debug', 'ajax_edit_admission_left_logo: FILES data: ' . print_r($_FILES, true));

        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            log_message('debug', 'ajax_edit_admission_left_logo: Form validation failed: ' . json_encode($array));
            echo json_encode($array);
        } else {
            log_message('debug', 'ajax_edit_admission_left_logo: Form validation passed.');
            $id = $this->input->post('id');
            $setting = $this->setting_model->getSetting();
            $old_logo = isset($setting->admission_logo_left) ? $setting->admission_logo_left : '';

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                log_message('debug', 'ajax_edit_admission_left_logo: File upload initiated.');
                $upload_result = $this->media_storage->fileupload("file", "./uploads/logos/");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    log_message('debug', 'ajax_edit_admission_left_logo: File upload failed: ' . json_encode($array));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
                log_message('debug', 'ajax_edit_admission_left_logo: File upload successful. New image name: ' . $img_name);
            } else {
                $img_name = $old_logo;
                log_message('debug', 'ajax_edit_admission_left_logo: No new file uploaded. Using old logo: ' . $img_name);
            }

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                if ($old_logo != '') {
                    log_message('debug', 'ajax_edit_admission_left_logo: Deleting old logo: ' . $old_logo);
                    $this->media_storage->filedelete($old_logo, "uploads/logos");
                }
            }
            $data_record = array('id' => $id, 'admission_logo_left' => $img_name);
            log_message('debug', 'ajax_edit_admission_left_logo: Updating database with: ' . print_r($data_record, true));
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            log_message('debug', 'ajax_edit_admission_left_logo: Process completed successfully.');
            echo json_encode($array);
        }
    }

    public function ajax_edit_admission_right_logo()
    {
        log_message('debug', 'ajax_edit_admission_right_logo: Method called.');
        log_message('debug', 'ajax_edit_admission_right_logo: POST data: ' . print_r($_POST, true));
        log_message('debug', 'ajax_edit_admission_right_logo: FILES data: ' . print_r($_FILES, true));

        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            log_message('debug', 'ajax_edit_admission_right_logo: Form validation failed: ' . json_encode($array));
            echo json_encode($array);
        } else {
            log_message('debug', 'ajax_edit_admission_right_logo: Form validation passed.');
            $id = $this->input->post('id');
            $setting = $this->setting_model->getSetting();
            $old_logo = isset($setting->admission_logo_right) ? $setting->admission_logo_right : '';

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                log_message('debug', 'ajax_edit_admission_right_logo: File upload initiated.');
                $upload_result = $this->media_storage->fileupload("file", "./uploads/logos/");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    log_message('debug', 'ajax_edit_admission_right_logo: File upload failed: ' . json_encode($array));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
                log_message('debug', 'ajax_edit_admission_right_logo: File upload successful. New image name: ' . $img_name);
            } else {
                $img_name = $old_logo;
                log_message('debug', 'ajax_edit_admission_right_logo: No new file uploaded. Using old logo: ' . $img_name);
            }

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                if ($old_logo != '') {
                    log_message('debug', 'ajax_edit_admission_right_logo: Deleting old logo: ' . $old_logo);
                    $this->media_storage->filedelete($old_logo, "uploads/logos");
                }
            }
            $data_record = array('id' => $id, 'admission_logo_right' => $img_name);
            log_message('debug', 'ajax_edit_admission_right_logo: Updating database with: ' . print_r($data_record, true));
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            log_message('debug', 'ajax_edit_admission_right_logo: Process completed successfully.');
            echo json_encode($array);
        }
    }

    public function check_admission_digit()
    {
        $adm_start_from = $this->input->post('adm_start_from');
        $adm_no_digit   = $this->input->post('adm_no_digit');
        $adm_prefix     = $this->input->post('adm_prefix');
        $adm_include_current_year = $this->input->post('adm_include_current_year') ? 1 : 0;
        
        if ($adm_no_digit != "") {
            $prefix_length = strlen($adm_prefix);
            if ($adm_include_current_year) {
                $prefix_length += 4;
            }
            $available_digits = $adm_no_digit - $prefix_length;
            
            // Check if start_from number fits within available digit space
            if (strlen($adm_start_from) <= $available_digits) {
                return true;
            }
            $this->form_validation->set_message('check_admission_digit', 'Admission Start From must be ' . $available_digits . ' digits or less (Total: ' . $adm_no_digit . ' - Prefix: ' . $prefix_length . ' chars)');
            return false;
        }
        return true;
    }

    public function check_staff_id_digit()
    {
        $adm_start_from   = $this->input->post('staffid_start_from');
        $staffid_no_digit = $this->input->post('staffid_no_digit');
        $staffid_prefix   = $this->input->post('staffid_prefix');
        $staffid_include_current_year = $this->input->post('staffid_include_current_year') ? 1 : 0;
        
        if ($staffid_no_digit != "") {
            $prefix_length = strlen($staffid_prefix);
            if ($staffid_include_current_year) {
                $prefix_length += 4;
            }
            $available_digits = $staffid_no_digit - $prefix_length;
            
            // Check if start_from number fits within available digit space
            if (strlen($adm_start_from) <= $available_digits) {
                return true;
            }
            $this->form_validation->set_message('check_staff_id_digit', 'Staff ID Start From must be ' . $available_digits . ' digits or less (Total: ' . $staffid_no_digit . ' - Prefix: ' . $prefix_length . ' chars)');
            return false;
        }
        return true;
    }

    public function logo()
    {        
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/logo');
    
        $setting              = $this->setting_model->getSetting();
        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/logo', $data);
        $this->load->view('layout/footer');
    }

    public function miscellaneous()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/miscellaneous');
                $setting                  = $this->setting_model->getSetting();
                if (is_null($setting)) {
                    $setting = new stdClass();
                    $setting->base_url = '';
                    $setting->folder_path = '';
                }
                $setting->base_url        = ($setting->base_url == "") ? base_url() : $setting->base_url;
                $setting->folder_path     = FCPATH;
        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/miscellaneous', $data);
        $this->load->view('layout/footer');
    }

    public function savemiscellaneous()
    {
        $event_reminder = $this->input->post('event_reminder');
        if ($event_reminder == 'enabled') {
            $calendar_event_reminder = $this->input->post('calendar_event_reminder');
        } else {
            $calendar_event_reminder = '0';
        }

        $data = array(
            'id'                       => $this->input->post('sch_id'),
            'my_question'              => $this->input->post('my_question'),
            'exam_result'              => $this->input->post('exam_result'),
            'class_teacher'            => $this->input->post('class_teacher'),
            'superadmin_restriction'   => $this->input->post('superadmin_restriction_mode'),
            'calendar_event_reminder'  => $calendar_event_reminder,
            'event_reminder'           => $this->input->post('event_reminder'),
            'staff_notification_email' => $this->input->post('staff_notification_email'),
            'scan_code_type'           => $this->input->post('scan_code_type'),
            'download_admit_card'      => $this->input->post('download_admit_card'),
        );

        $this->setting_model->add($data);
        $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        echo json_encode($array);

    }

    public function backendtheme()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
                $setting              = $this->setting_model->getSetting();
                if (is_null($setting)) {
                    $setting = new stdClass();
                    $setting->base_url = '';
                    $setting->folder_path = '';
                }
                $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
                $setting->folder_path = FCPATH;        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/backendtheme', $data);
        $this->load->view('layout/footer');
    }

    public function savebackendtheme()
    {
        $this->form_validation->set_rules('theme', $this->lang->line('theme'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'theme' => form_error('theme'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {

            $data = array(
                'id'    => $this->input->post('sch_id'),
                'theme' => $this->input->post('theme'),
            );

            $this->setting_model->add($data);
            $this->session->userdata['admin']['theme'] = $this->input->post('theme');
            $array                                     = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function mobileapp()
    {
        $app_ver = $this->config->item('app_ver');
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
                        $setting              = $this->setting_model->getSetting();
                        if (is_null($setting)) {
                            $setting = new stdClass();
                            $setting->base_url = '';
                            $setting->folder_path = '';
                            // It might be good to also initialize staff_profile_edit here if setting was initially null
                            $setting->staff_profile_edit = 0; 
                            $setting->staff_self_edit = 0;
                        }        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']       = $setting;
        $data['app_response'] = $this->auth->andapp_validate();
        $this->load->view('layout/header');
        $this->load->view('setting/mobileapp', $data);
        $this->load->view('layout/footer');
    }

    public function savemobileapp()
    {
        $this->form_validation->set_rules('mobile_api_url', 'Mobile App API URL', 'trim|xss_clean');
        $this->form_validation->set_rules('app_primary_color_code', 'App Primary Color Code', 'trim|xss_clean');
        $this->form_validation->set_rules('app_secondary_color_code', 'App Secondary Color Code', 'trim|xss_clean');
        $this->form_validation->set_rules('admin_app_primary_color_code', 'Admin App Primary Color Code', 'trim|xss_clean');
        $this->form_validation->set_rules('admin_app_secondary_color_code', 'Admin App Secondary Color Code', 'trim|xss_clean');
        $this->form_validation->set_rules('admin_mobile_api_url', 'Admin Mobile App API URL', 'trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'mobile_api_url' => form_error('mobile_api_url'),
                'app_primary_color_code' => form_error('app_primary_color_code'),
                'app_secondary_color_code' => form_error('app_secondary_color_code'),
                'admin_app_primary_color_code' => form_error('admin_app_primary_color_code'),
                'admin_app_secondary_color_code' => form_error('admin_app_secondary_color_code'),
                'admin_mobile_api_url' => form_error('admin_mobile_api_url'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $staff_profile_edit = $this->input->post('staff_profile_edit') ? 1 : 0;
            $staff_self_edit = $this->input->post('staff_self_edit') ? 1 : 0;
            
            // --- START Debug Logging ---
            log_message('debug', 'Schsettings::savemobileapp - POST data: ' . print_r($this->input->post(), true));
            log_message('debug', 'Schsettings::savemobileapp - staff_self_edit from POST (raw): ' . var_export($this->input->post('staff_self_edit'), true));
            log_message('debug', 'Schsettings::savemobileapp - staff_self_edit (processed): ' . $staff_self_edit);
            // --- END Debug Logging ---

            $data = array(
                'id'                             => $this->input->post('sch_id'),
                'mobile_api_url'                 => $this->input->post('mobile_api_url'),
                'app_primary_color_code'         => $this->input->post('app_primary_color_code'),
                'app_secondary_color_code'       => $this->input->post('app_secondary_color_code'),
                'admin_app_primary_color_code'   => $this->input->post('admin_app_primary_color_code'),
                'admin_app_secondary_color_code' => $this->input->post('admin_app_secondary_color_code'),
                'admin_mobile_api_url'           => $this->input->post('admin_mobile_api_url'),
                'staff_profile_edit'             => $staff_profile_edit,
                'staff_self_edit'                => $staff_self_edit,
            );

            $this->setting_model->add($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function studentguardianpanel()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;

        // Payroll FY start/end are optional columns; default to Apr-Mar when not present.
        if (!isset($setting->payroll_fy_start_month)) {
            $setting->payroll_fy_start_month = 4;
        }
        if (!isset($setting->payroll_fy_end_month)) {
            $setting->payroll_fy_end_month = 3;
        }
        if ($this->db->field_exists('payroll_fy_start_month', 'sch_settings') && $this->db->field_exists('payroll_fy_end_month', 'sch_settings')) {
            $fy_row = $this->db->select('payroll_fy_start_month, payroll_fy_end_month')->from('sch_settings')->limit(1)->get()->row();
            if ($fy_row) {
                $start_m = (int) $fy_row->payroll_fy_start_month;
                $end_m = (int) $fy_row->payroll_fy_end_month;
                if ($start_m >= 1 && $start_m <= 12) {
                    $setting->payroll_fy_start_month = $start_m;
                }
                if ($end_m >= 1 && $end_m <= 12) {
                    $setting->payroll_fy_end_month = $end_m;
                }
            }
        }
        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/studentguardianpanel', $data);
        $this->load->view('layout/footer');
    }

    public function studentguardian()
    {
        $parent_panel_login  = 0;
        $student_panel_login = 0;

        if(isset($_POST['student_panel_login'])) {
            $student_panel_login = 1;
        }
        if(isset($_POST['parent_panel_login'])) {
            $parent_panel_login = 1;
        }

        $data = array(
            'id'                  => $this->input->post('sch_id'),
            'student_timeline'    => $this->input->post('student_timeline'),
            'student_login'       => json_encode($this->input->post('student_login')),
            'parent_login'        => json_encode($this->input->post('parent_login')),
            'student_panel_login' => $student_panel_login,
            'parent_panel_login'  => $parent_panel_login,
        );

        $this->setting_model->add($data);

        $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        echo json_encode($array);
    }

    public function fees()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/fees');

        $setting                        = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url              = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path           = FCPATH;
        $data['result']                 = $setting;
        $data['duplicate_fees_invoice'] = explode(",", $setting->is_duplicate_fees_invoice);
        $this->load->view('layout/header');
        $this->load->view('setting/fees', $data);
        $this->load->view('layout/footer');
    }

    public function savefees()
    {
        $this->form_validation->set_rules('is_student_feature_lock', $this->lang->line('is_student_feature_lock'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('is_offline_fee_payment', $this->lang->line('offline_bank_payment_in_student_panel'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('is_duplicate_fees_invoice[]', $this->lang->line('print_fees_receipt_for'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('lock_grace_period', $this->lang->line('fees_payment_grace_period'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('fee_due_days', $this->lang->line('carry_forward_fees_due_days'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('single_page_print', $this->lang->line('single_page_print'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'is_duplicate_fees_invoice' => form_error('is_duplicate_fees_invoice[]'),
                'single_page_print'         => form_error('single_page_print'),
                'fee_due_days'              => form_error('fee_due_days'),
                'lock_grace_period'         => form_error('lock_grace_period'),
                'is_student_feature_lock'   => form_error('is_student_feature_lock'),
                'is_offline_fee_payment'    => form_error('is_offline_fee_payment'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {

            $is_duplicate_fees_invoice = implode(",", $this->input->post('is_duplicate_fees_invoice'));
            $data                      = array(
                'id'                                => $this->input->post('sch_id'),
                'is_duplicate_fees_invoice'         => $is_duplicate_fees_invoice,
                'single_page_print'                 => $this->input->post('single_page_print'),
                'fee_due_days'                      => $this->input->post('fee_due_days'),
                'lock_grace_period'                 => $this->input->post('lock_grace_period'),
                'collect_back_date_fees'            => $this->input->post('collect_back_date_fees'),
                'is_student_feature_lock'           => $this->input->post('is_student_feature_lock'),
                'is_offline_fee_payment'            => $this->input->post('is_offline_fee_payment'),
                'offline_bank_payment_instruction'  => $this->input->post('offline_bank_payment_instruction'),               
                'fees_discount'                     => $this->input->post('fees_discount'),               
            );

            $this->setting_model->add($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function idautogeneration()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/idautogeneration');

        $digit                = $this->customlib->getDigits();
        $data['digitList']    = $digit;
        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/idautogeneration', $data);
        $this->load->view('layout/footer');
    }

    public function saveidautogeneration()
    {
        $this->form_validation->set_rules('sch_id', 'Id', 'trim|required|xss_clean');

        if ($this->input->post('adm_auto_insert')) {
            $this->form_validation->set_rules('adm_prefix', $this->lang->line('admission_no_prefix'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('adm_start_from', $this->lang->line('admission_start_from'), 'trim|integer|required|xss_clean');
            $this->form_validation->set_rules('adm_no_digit', $this->lang->line('admission_no_digit'), 'trim|integer|required|xss_clean|callback_check_admission_digit');
        }
        if ($this->input->post('staffid_auto_insert')) {

            $this->form_validation->set_rules('staffid_prefix', $this->lang->line('staff_id_prefix'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('staffid_start_from', $this->lang->line('staff_id_start_from'), 'trim|integer|required|xss_clean');
            $this->form_validation->set_rules('staffid_no_digit', $this->lang->line('staff_id_digit'), 'trim|integer|required|xss_clean|callback_check_staff_id_digit');
        }

        if ($this->form_validation->run() == false) {
            $data = array(
                'adm_start_from'     => form_error('adm_start_from'),
                'adm_prefix'         => form_error('adm_prefix'),
                'adm_no_digit'       => form_error('adm_no_digit'),
                'staffid_start_from' => form_error('staffid_start_from'),
                'staffid_prefix'     => form_error('staffid_prefix'),
                'staffid_no_digit'   => form_error('staffid_no_digit'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $setting_result = $this->setting_model->getSetting();

            $data = array(
                'id'                  => $this->input->post('sch_id'),
                'adm_start_from'      => $this->input->post('adm_start_from'),
                'adm_prefix'          => $this->input->post('adm_prefix'),
                'adm_no_digit'        => $this->input->post('adm_no_digit'),
                'adm_auto_insert'     => $this->input->post('adm_auto_insert'),
                'adm_include_current_year' => $this->input->post('adm_include_current_year') ? 1 : 0,
                'staffid_start_from'  => $this->input->post('staffid_start_from'),
                'staffid_prefix'      => $this->input->post('staffid_prefix'),
                'staffid_no_digit'    => $this->input->post('staffid_no_digit'),
                'staffid_auto_insert' => $this->input->post('staffid_auto_insert'),
                'staffid_include_current_year' => $this->input->post('staffid_include_current_year') ? 1 : 0,
            );

            $data['adm_update_status']     = 1;
            $data['staffid_update_status'] = 1;
            if ($this->input->post('adm_auto_insert')) {
                if ($setting_result->adm_prefix != $this->input->post('adm_prefix') ||
                    $setting_result->adm_start_from != $this->input->post('adm_start_from') ||
                    $setting_result->adm_no_digit != $this->input->post('adm_no_digit')
                ) {
                    $data['adm_update_status'] = 0;
                }
            }

            if ($this->input->post('staffid_auto_insert')) {
                if ($setting_result->staffid_prefix != $this->input->post('staffid_prefix') ||
                    $setting_result->staffid_start_from != $this->input->post('staffid_start_from') ||
                    $setting_result->staffid_no_digit != $this->input->post('staffid_no_digit')
                ) {
                    $data['staffid_update_status'] = 0;
                }
            }

            $data['adm_update_status'];
            $this->setting_model->add($data);

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function attendancetype()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/attendancetype');
        
        $data['classid']=$classid=$this->input->post('class_id');
        if(isset($classid)==false){
            $data['classid']=0;
        }
        
        $class_list=$this->class_section_time_model->allClassSections();
        $data['class_list'] = $class_list;
        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']       = $setting;

        //staff attedance settings
        $staff_attendance_data   = $this->staffAttendaceSetting_model->getRoleAttendanceSetting();  
        $attendance_type         = $this->attendencetype_model->getScheduleTypeStaffAttendance();
        
        $user_roles              = $this->staff_model->getStaffRole();
        $data['user_roles']      = $user_roles;    
        $data['attendance_type'] = $attendance_type;
        $new_list_attendance     = array();

        foreach ($staff_attendance_data as $key => $value) {
            if (array_key_exists($value->id, $new_list_attendance)) {
                $new_list_attendance[$value->id]['schedule'][] = $value;
            } else {
                $new_list_attendance[$value->id] = [
                    'role_id' => $value->id,
                    'role' => $value->role_name,
                    'schedule' => array($value)
                ];
            }
        }
        $data['list_attendance'] = $new_list_attendance;     
        $data['all_roles_attendance_setting'] = $this->staffAttendaceSetting_model->getAllRolesAttendanceSetting();
        // staff attedance settings

        //student attedance settings
        $student_class_section_data = $this->studentAttendaceSetting_model->getClassWiseAttendanceSetting($classid);
        $student_attendance_type            = $this->attendencetype_model->getScheduleTypeAttendance();
        // $data = array();
        $data['student_attendance_type'] = $student_attendance_type;
        $student_new_list_attendance = array();

        foreach ($student_class_section_data as $student_class_key => $student_class_value) {
            if (array_key_exists($student_class_value->class_id, $student_new_list_attendance)) {

                if (array_key_exists($student_class_value->section_id, $student_new_list_attendance[$student_class_value->class_id]['sections'])) {

                    $student_new_list_attendance[$student_class_value->class_id]['sections'][$student_class_value->section_id]['student_schedule'][] = $student_class_value;
                } else {

                    $student_new_list_attendance[$student_class_value->class_id]['sections'][$student_class_value->section_id] = array(
                        'class_section_id' => $student_class_value->id,
                        'section_id' => $student_class_value->section_id,
                        'section' => $student_class_value->section,
                        'student_schedule' => array($student_class_value)
                    );
                }
            } else {
                $student_new_list_attendance[$student_class_value->class_id] = [
                    'class_id' => $student_class_value->class_id,
                    'class' => $student_class_value->class,
                    'sections' => array($student_class_value->section_id =>
                    array(
                        'class_section_id' => $student_class_value->id,
                        'section_id' => $student_class_value->section_id,
                        'section' => $student_class_value->section,
                        'student_schedule' => array($student_class_value)
                    ))
                ];
            }
        }
        $data['student_list_attendance'] = $student_new_list_attendance;
        //student attedance settings

        $class                   = $this->class_model->get();
        $data['classlist']       = $class;

        $this->load->view('layout/header', $data);
        $this->load->view('setting/attendancetype', $data);
        $this->load->view('layout/footer', $data);
    }

    public function maintenance()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/maintenance');

        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']       = $setting;
        $this->load->view('layout/header', $data);
        $this->load->view('setting/maintenance', $data);
        $this->load->view('layout/footer');
    }

    public function saveattendancetype()
    {
        $this->form_validation->set_rules('attendence_type', $this->lang->line('attendance_type'), 'trim|required|xss_clean');        

        if ($this->form_validation->run() == false) {
            $data = array(
                'attendence_type' => form_error('attendence_type'),
                 
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $cutoff_day = $this->input->post('payroll_cutoff_day');
            if ($cutoff_day === '' || $cutoff_day === null) {
                $cutoff_day = 0;
            } else {
                $cutoff_day = (int) $cutoff_day;
                if ($cutoff_day < 0 || $cutoff_day > 27) {
                    $cutoff_day = 0;
                }
            }

            $payroll_fy_start_month = (int) $this->input->post('payroll_fy_start_month');
            if ($payroll_fy_start_month < 1 || $payroll_fy_start_month > 12) {
                $payroll_fy_start_month = 4;
            }

            // Keep FY range consistent: end month is always start month - 1.
            $payroll_fy_end_month = (($payroll_fy_start_month + 10) % 12) + 1;

            $data = array(
                'id'               => $this->input->post('sch_id'),
                'attendence_type'  => $this->input->post('attendence_type'),
                'biometric_device' => $this->input->post('biometric_device'),
                'student_biometric'        => $this->input->post('student_biometric'),
                'staff_biometric'        => $this->input->post('staff_biometric'),
                'low_attendance_limit' => $this->input->post('low_attendance_limit'),
                'office_end_time' => $this->input->post('office_end_time'),
                'morning_session_end_time' => $this->input->post('morning_session_end_time'),
                'evening_session_end_time' => $this->input->post('evening_session_end_time'),
                'max_late_allowed' => $this->input->post('max_late_allowed'),
                'max_permission_allowed' => $this->input->post('max_permission_allowed'),
                'payroll_cutoff_day' => $cutoff_day,
                'auto_adjust_lop_with_leaves' => $this->input->post('auto_adjust_lop_with_leaves') ? 1 : 0,
            );

            if ($this->db->field_exists('auto_adjust_lop_with_preallotted_leaves', 'sch_settings')) {
                $data['auto_adjust_lop_with_preallotted_leaves'] = $this->input->post('auto_adjust_lop_with_preallotted_leaves') ? 1 : 0;
            }

            if ($this->db->field_exists('payroll_fy_start_month', 'sch_settings')) {
                $data['payroll_fy_start_month'] = $payroll_fy_start_month;
            }
            if ($this->db->field_exists('payroll_fy_end_month', 'sch_settings')) {
                $data['payroll_fy_end_month'] = $payroll_fy_end_month;
            }
            
            $this->setting_model->add($data);
                    $period_attendance=0;
                    $student_attendance=1;
                     if($this->input->post('attendence_type')){
                          $period_attendance=1;
                          $student_attendance=0;
                     }

              $this->sidebarmenu_model->update_submenu_by_key(
                  [
                      ['key'=>'period_attendance_by_date','is_active'=>$period_attendance],
                      ['key'=>'period_attendance','is_active'=>$period_attendance],
                      ['key'=>'student_attendance','is_active'=>$student_attendance],
                      ['key'=>'attendance_by_date','is_active'=>$student_attendance]
                  ]
                );

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function save_maintenance()
    {
        $this->form_validation->set_rules('maintenance_mode', $this->lang->line('maintenance_mode'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'maintenance_mode' => form_error('maintenance_mode'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);
        } else {
            $data = array(
                'id'               => $this->input->post('sch_id'),
                'maintenance_mode' => $this->input->post('maintenance_mode'),
            );
            $this->setting_model->add($data);

            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }
    
    public function login_page_background()
    {        
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/login_page_background');
    
        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/login_page_background', $data);
        $this->load->view('layout/footer');
    }
    
    public function add_admin_login_background()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->input->post('id');
            $logo_type = $this->input->post('logo_type');
 
            $setting = $this->setting_model->getSetting();
            if($logo_type != 'admin_logo'){                
                $background =   $setting->user_login_page_background;
            }else {
                $background =   $setting->admin_login_page_background;
            }
            
            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                $upload_result = $this->media_storage->fileupload("file", "./uploads/school_content/login_image/");
                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
            } else {
                $img_name = $background;
            }
            
            if (isset($background)) {
                $this->media_storage->filedelete($background, "uploads/school_content/login_image");
            }
            
            if($logo_type != 'admin_logo'){                
                $data_record = array('id' => $id, 'user_login_page_background' => $img_name);
            }else {                 
                $data_record = array('id' => $id, 'admin_login_page_background' => $img_name);
            }          
            
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function saveallrolessetting(){
        $this->form_validation->set_rules('row[]', $this->lang->line('row'), 'trim|required|xss_clean');
        $row = $this->input->post('row');
        $time_valid = true;

        if (!empty($row) && isset($row)) {
            foreach ($row as $row_key => $row_value) {
                $attendance_type      = $this->input->post('attendance_type_id_' . $row_value);
                $entry_time_from      = $this->input->post('entry_time_from_' . $row_value);
                $entry_time_to        = $this->input->post('entry_time_to_' . $row_value);
                $total_institute_hour = $this->input->post('total_institute_hour_' . $row_value);
             
                if ($entry_time_from == "" || $entry_time_to == "" || $total_institute_hour == "" || $attendance_type == "") {
                    $this->form_validation->set_rules(
                        'fields',
                        'fields --r',
                        'trim|required|xss_clean',
                        array('required' => $this->lang->line('fields_values_required'))
                    );
                    $time_valid = false;
                    break;
                }
            }
        }

        if ($this->form_validation->run() == false){
            $msg = array(
                'row' => form_error('row'),
                'fields' => form_error('fields')
            );
            $array = array('status' => 0, 'error' => $msg, 'message' => '');
        } else {
            $insert_array = array();
            $user_roles = $this->staff_model->getStaffRole();
            foreach ($user_roles as $role_key => $role_value) {
                foreach ($row as $row_key => $row_value) {
                    $attendance_type = $this->input->post('attendance_type_id_' . $row_value);
                    $entry_time_from = $this->input->post('entry_time_from_' . $row_value);
                    $entry_time_to = $this->input->post('entry_time_to_' . $row_value);
                    $total_institute_hour = $this->input->post('total_institute_hour_' . $row_value);
           
                    $insert_array[] = array(
                        'staff_attendence_type_id' => $attendance_type,
                        'role_id'                  => $role_value['id'],
                        'entry_time_from'          => $entry_time_from,
                        'entry_time_to'            => $entry_time_to,
                        'total_institute_hour'     => ($total_institute_hour)
                    );
                }
            }

            $this->staffAttendaceSetting_model->add_batch($insert_array);
            $array = array('status' => 1, 'message' => $this->lang->line('update_message'));
        }
        echo json_encode($array);
    }

    //****staff attendance settings****//
    public function savestaffsetting(){
        $this->form_validation->set_rules('row[]', $this->lang->line('row'), 'trim|required|xss_clean');
        $row = $this->input->post('row');
        $time_valid = true;

        if (!empty($row) && isset($row)) {
            foreach ($row as $row_key => $row_value) {
                $attendance_type      = $this->input->post('attendance_type_id_' . $row_value);
                $class_section        = $this->input->post('role_id_' . $row_value);
                $entry_time_from      = $this->input->post('entry_time_from_' . $row_value);
                $entry_time_to        = $this->input->post('entry_time_to_' . $row_value);
             
                if ($class_section == "" || $entry_time_from == "" || $entry_time_to == "" || $attendance_type == "") {
                    $this->form_validation->set_rules(
                        'fields',
                        'fields --r',
                        'trim|required|xss_clean',
                        array('required' => $this->lang->line('fields_values_required'))
                    );
                    $time_valid = false;
                    break;
                }
            }
        }

        if ($this->form_validation->run() == false){
            $msg = array(
                'row' => form_error('row'),
                'fields' => form_error('fields')
            );
            $array = array('status' => 0, 'error' => $msg, 'message' => '');
        } else {
            $insert_array = array();
            $role_array = array();
            foreach ($row as $row_key => $row_value) {
                $role_array[] = ($this->input->post('role_id_' . $row_value));
                $role_id = $this->input->post('role_id_' . $row_value);
                $attendance_type = $this->input->post('attendance_type_id_' . $row_value);
                $entry_time_from = $this->input->post('entry_time_from_' . $row_value);
                $entry_time_to = $this->input->post('entry_time_to_' . $row_value);
       
                $insert_array[] = array(
                    'staff_attendence_type_id' => $attendance_type,
                    'role_id'                  => $class_section,
                    'entry_time_from'          => $entry_time_from,
                    'entry_time_to'            => $entry_time_to
                );
            }

            $this->staffAttendaceSetting_model->add($insert_array, $role_array);
            $array = array('status' => 1, 'message' => $this->lang->line('update_message'));
        }
        echo json_encode($array);
    }

	public function whatsappsettings()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/whatsappsettings');
        $setting              = $this->setting_model->getSetting();
        if (is_null($setting)) {
            $setting = new stdClass();
            $setting->base_url = '';
            $setting->folder_path = '';
        }
        $setting->base_url    = ($setting->base_url == "") ? base_url() : $setting->base_url;
        $setting->folder_path = FCPATH;
        $data['result']       = $setting;
        $this->load->view('layout/header');
        $this->load->view('setting/whatsappsettings', $data);
        $this->load->view('layout/footer');
    }

	public function savewhatsappsettings()
    {
        $this->form_validation->set_rules('sch_id', ('sch_id'), 'trim|required|xss_clean');
		$this->form_validation->set_rules('time_to', $this->lang->line('time_to'), 'callback_time_check');        	
		
		$whatsapp_fields = [
			'front_side_whatsapp' => 'front_side_whatsapp_mobile',
			'admin_panel_whatsapp' => 'admin_panel_whatsapp_mobile',
			'student_panel_whatsapp' => 'student_panel_whatsapp_mobile'
		];

		foreach ($whatsapp_fields as $input_name => $field_name) {
			
			$this->form_validation->set_rules($input_name, $this->lang->line('whatsapp_link'), 'trim|required|xss_clean');
			
			if ($this->input->post($input_name)) {
				$this->form_validation->set_rules($field_name, $this->lang->line('mobile_no'), 'trim|required|xss_clean');
			}
			
			// Check time fields
			$from_field = "{$input_name}_from";
			$to_field = "{$input_name}_to";
			
			if (empty($this->input->post($from_field)) && !empty($this->input->post($to_field))) {
				$this->form_validation->set_rules($from_field, $this->lang->line('time_from'), 'trim|required|xss_clean');
			}
			
			if (!empty($this->input->post($from_field)) && empty($this->input->post($to_field))) {
				$this->form_validation->set_rules($to_field, $this->lang->line('time_to'), 'trim|required|xss_clean');
			}
		}
		
        if ($this->form_validation->run() == false) {            
			
			$fields = ['sch_id', 'front_side_whatsapp', 'admin_panel_whatsapp', 'student_panel_whatsapp', 'front_side_whatsapp_mobile', 'admin_panel_whatsapp_mobile', 'student_panel_whatsapp_mobile',  'front_side_whatsapp_from', 'front_side_whatsapp_to', 'admin_panel_whatsapp_from', 'admin_panel_whatsapp_to', 'student_panel_whatsapp_from', 'student_panel_whatsapp_to', 'time_to'];

			$error = array();
			
			foreach ($fields as $field) {
				$error[$field] = form_error($field);
			}			
			
            $array = array('status' => 'fail', 'error' => $error);
            echo json_encode($array);
        } else {			
			
			$fields = ['front_side_whatsapp_from', 'front_side_whatsapp_to', 'admin_panel_whatsapp_from', 'admin_panel_whatsapp_to', 'student_panel_whatsapp_from', 'student_panel_whatsapp_to'];

			foreach ($fields as $field) {
				$$field = $this->input->post($field) ?: null;
			}
			
            $data = array(
				'id'                       		=> $this->input->post('sch_id'),
				'front_side_whatsapp'           => $this->input->post('front_side_whatsapp'),
				'front_side_whatsapp_mobile'    => $this->input->post('front_side_whatsapp_mobile'),
				'front_side_whatsapp_from'      => $front_side_whatsapp_from,
				'front_side_whatsapp_to'        => $front_side_whatsapp_to,             
				'admin_panel_whatsapp'        	=> $this->input->post('admin_panel_whatsapp'),             
				'admin_panel_whatsapp_mobile'   => $this->input->post('admin_panel_whatsapp_mobile'),             
				'admin_panel_whatsapp_from'     => $admin_panel_whatsapp_from,             
				'admin_panel_whatsapp_to'       => $admin_panel_whatsapp_to,             
				'student_panel_whatsapp'        => $this->input->post('student_panel_whatsapp'),             
				'student_panel_whatsapp_mobile' => $this->input->post('student_panel_whatsapp_mobile'),             
				'student_panel_whatsapp_from'   => $student_panel_whatsapp_from,             
				'student_panel_whatsapp_to'     => $student_panel_whatsapp_to,             
			);
			
            $this->setting_model->add($data);		
			
			$this->session->userdata['admin']['admin_panel_whatsapp'] = $this->input->post('admin_panel_whatsapp');
			$this->session->userdata['admin']['admin_panel_whatsapp_mobile'] = $this->input->post('admin_panel_whatsapp_mobile');
			$this->session->userdata['admin']['admin_panel_whatsapp_from'] = $admin_panel_whatsapp_from;
			$this->session->userdata['admin']['admin_panel_whatsapp_to'] = $admin_panel_whatsapp_to;	

            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }
	
	function time_check()
	{
		$fields = [
			'front_side_whatsapp',
			'admin_panel_whatsapp',
			'student_panel_whatsapp'
		];
	
		foreach ($fields as $field) {
			$from = strtotime($this->input->post("{$field}_from"));
			$to = strtotime($this->input->post("{$field}_to"));
			
			if (!empty($from) && !empty($to) && $from >= $to) {
				$this->form_validation->set_message('time_check', '%s cannot less than from time %s');
				return FALSE;
			}
		}
	
		return TRUE;
    }
		
    public function hiddenforms()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/hiddenforms');

        $this->load->view('layout/header');
        $this->load->view('setting/hiddenforms');
        $this->load->view('layout/footer');
    }

    public function leavepolicy()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/leavepolicy');

        $this->load->model('leavetypes_model');
        $this->load->model('staff_model');
        $setting = $this->setting_model->getSetting();
        $all_roles = $this->role_model->get();
        $leave_types = $this->leavetypes_model->getLeaveType();
        $staff_list = $this->staff_model->get(null, 1);

        // Ensure leave policy columns exist in DB, then fetch them separately
        // because getSetting()'s hardcoded SELECT does not include these columns.
        $this->ensureLeavePolicyColumns();
        $lp_cols = $this->db
            ->select('leave_substitution_required_roles, leave_self_approve_roles, leave_past_date_allowed_roles, leave_workday_override_types, leave_enable_half_day, leave_half_day_allowed_roles, leave_half_day_allowed_types')
            ->from('sch_settings')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row();
        if ($lp_cols) {
            foreach ((array) $lp_cols as $col => $val) {
                $setting->$col = $val;
            }
        }

        $data = [];
        $data['result'] = $setting;
        $data['all_roles'] = $all_roles;
        $data['leave_types'] = $leave_types;
        $data['staff_list'] = $staff_list;
        $data['leave_policy'] = $this->buildLeavePolicyForView($setting, $all_roles, $leave_types);

        $this->load->view('layout/header', $data);
        $this->load->view('setting/leavepolicy', $data);
        $this->load->view('layout/footer', $data);
    }
    private function ensureLeavePolicyColumns()
    {
        $required = [
            'leave_substitution_required_roles' => 'TEXT NULL',
            'leave_substitution_exempt_types' => 'TEXT NULL',
            'leave_self_approve_roles' => 'TEXT NULL',
            'leave_workday_override_types' => 'VARCHAR(255) NULL',
            'leave_past_date_allowed_roles' => 'TEXT NULL',
            'leave_enable_half_day' => 'TINYINT(1) NOT NULL DEFAULT 1',
            'leave_half_day_allowed_roles' => 'TEXT NULL',
            'leave_half_day_allowed_types' => 'TEXT NULL',
        ];

        $existing_rows = $this->db->query("SHOW COLUMNS FROM sch_settings")->result_array();
        $existing_cols = [];
        foreach ($existing_rows as $row) {
            $existing_cols[] = $row['Field'];
        }

        foreach ($required as $column => $definition) {
            if (!in_array($column, $existing_cols, true)) {
                $this->db->query("ALTER TABLE sch_settings ADD COLUMN {$column} {$definition}");
            }
        }
    }

    private function sanitizeIdCsvFromPost($post_key)
    {
        $value = $this->input->post($post_key);
        $ids = is_array($value) ? $value : [];
        $clean = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0 && !in_array($id, $clean, true)) {
                $clean[] = $id;
            }
        }
        return implode(',', $clean);
    }

    private function normalizeOverrideLabels($value)
    {
        $parts = array_filter(array_map('trim', explode(',', (string) $value)));
        $clean = [];
        foreach ($parts as $part) {
            $part = strtolower($part);
            if ($part !== '' && !in_array($part, $clean, true)) {
                $clean[] = $part;
            }
        }
        return implode(',', $clean);
    }

    private function roleIdsByNames($all_roles, $names)
    {
        $target = array_map('strtolower', $names);
        $ids = [];
        foreach ($all_roles as $role) {
            $name = strtolower(trim((string) ($role['name'] ?? '')));
            if (in_array($name, $target, true)) {
                $id = (int) ($role['id'] ?? 0);
                if ($id > 0 && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    private function leaveTypeIdsByNames($leave_types, $names)
    {
        $target = array_map('strtolower', $names);
        $ids = [];
        foreach ($leave_types as $leave_type) {
            $name = strtolower(trim((string) ($leave_type['type'] ?? '')));
            if (in_array($name, $target, true)) {
                $id = (int) ($leave_type['id'] ?? 0);
                if ($id > 0 && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    private function csvToIntArray($csv)
    {
        $parts = array_filter(array_map('trim', explode(',', (string) $csv)));
        $result = [];
        foreach ($parts as $part) {
            $id = (int) $part;
            if ($id > 0 && !in_array($id, $result, true)) {
                $result[] = $id;
            }
        }
        return $result;
    }

    private function buildLeavePolicyForView($setting, $all_roles, $leave_types)
    {
        $required_roles_csv = (string) ($setting->leave_substitution_required_roles ?? '');
        $self_approve_roles_csv = (string) ($setting->leave_self_approve_roles ?? '');
        $override_types = trim((string) ($setting->leave_workday_override_types ?? ''));
        $past_date_allowed_roles_csv = (string) ($setting->leave_past_date_allowed_roles ?? '');
        $half_day_enabled = isset($setting->leave_enable_half_day) ? (int) $setting->leave_enable_half_day : 1;
        $half_day_allowed_roles_csv = (string) ($setting->leave_half_day_allowed_roles ?? '');
        $half_day_allowed_types_csv = (string) ($setting->leave_half_day_allowed_types ?? '');

        if ($required_roles_csv === '') {
            $required_roles_csv = implode(',', $this->roleIdsByNames($all_roles, ['teacher']));
        }
        if ($self_approve_roles_csv === '') {
            $self_approve_roles_csv = implode(',', $this->roleIdsByNames($all_roles, ['principal']));
        }
        if ($past_date_allowed_roles_csv === '') {
            $past_date_allowed_roles_csv = implode(',', $this->roleIdsByNames($all_roles, ['admin', 'super admin']));
        }
        if ($override_types === '') {
            $override_types = 'compensation,comp-off,compoff,compensatory off';
        }

        return [
            'substitution_required_roles' => $this->csvToIntArray($required_roles_csv),
            'self_approve_roles' => $this->csvToIntArray($self_approve_roles_csv),
            'past_date_allowed_roles' => $this->csvToIntArray($past_date_allowed_roles_csv),
            'workday_override_types' => $override_types,
            'half_day_enabled' => $half_day_enabled === 1,
            'half_day_allowed_roles' => $this->csvToIntArray($half_day_allowed_roles_csv),
            'half_day_allowed_types' => $this->csvToIntArray($half_day_allowed_types_csv),
            'leave_approver_id' => isset($setting->leave_approver_id) ? (int) $setting->leave_approver_id : 0,
        ];
    }

    public function saveleavepolicy()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_edit')) {
            access_denied();
        }

        $this->ensureLeavePolicyColumns();

        $setting = $this->setting_model->getSetting();
        $setting_id = (int) ($setting->id ?? 1);

        $data = [
            'id' => $setting_id,
            'leave_substitution_required_roles' => $this->sanitizeIdCsvFromPost('leave_substitution_required_roles'),
            'leave_self_approve_roles' => $this->sanitizeIdCsvFromPost('leave_self_approve_roles'),
            'leave_past_date_allowed_roles' => $this->sanitizeIdCsvFromPost('leave_past_date_allowed_roles'),
            'leave_workday_override_types' => $this->normalizeOverrideLabels($this->input->post('leave_workday_override_types')),
            'leave_enable_half_day' => $this->input->post('leave_enable_half_day') ? 1 : 0,
            'leave_half_day_allowed_roles' => $this->sanitizeIdCsvFromPost('leave_half_day_allowed_roles'),
            'leave_half_day_allowed_types' => $this->sanitizeIdCsvFromPost('leave_half_day_allowed_types'),
            'leave_approver_id' => max(0, (int) $this->input->post('leave_approver_id')),
        ];

        $this->setting_model->add($data);
        echo json_encode(['status' => 1, 'message' => $this->lang->line('success_message')]);
    }

    // ========================================================================
    // Enquiry Lead Gen Vendor Management
    // ========================================================================

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

    private function getLeadVendors()
    {
        return $this->db
            ->select('id, vendor_code, vendor_name, is_active, created_by, last_used_at, created_at, updated_at')
            ->from('lead_api_vendors')
            ->order_by('vendor_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function enquiryleadvendors()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/enquiryleadvendors');

        $this->ensureLeadVendorTable();

        $data = [];
        $data['lead_vendors'] = $this->getLeadVendors();

        $this->load->view('layout/header', $data);
        $this->load->view('setting/enquiryleadvendors', $data);
        $this->load->view('layout/footer', $data);
    }

    public function enquiryleadvendorinstructions()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('subsub_menu', 'schsettings/enquiryleadvendors');

        $doc_path = FCPATH . 'docs/lead_enquiry_api_vendor_integration.md';
        $doc_text = 'Instruction document not found at: ' . $doc_path;
        if (is_file($doc_path) && is_readable($doc_path)) {
            $content = file_get_contents($doc_path);
            if ($content !== false && trim($content) !== '') {
                $doc_text = $content;
            }
        }

        $data = [];
        $data['doc_text'] = $doc_text;

        $this->load->view('layout/header', $data);
        $this->load->view('setting/enquiryleadvendorinstructions', $data);
        $this->load->view('layout/footer', $data);
    }

    public function ajax_save_lead_vendor()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_edit')) {
            access_denied();
        }

        $this->ensureLeadVendorTable();

        $id = (int) $this->input->post('id');
        $vendor_name = trim((string) $this->input->post('vendor_name'));
        $vendor_code_raw = trim((string) $this->input->post('vendor_code'));
        $vendor_code = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $vendor_code_raw));
        $api_key = trim((string) $this->input->post('api_key'));
        $is_active = $this->input->post('is_active') ? 1 : 0;

        if ($vendor_name === '') {
            echo json_encode(['status' => 'fail', 'message' => 'Vendor name is required.']);
            return;
        }

        if ($vendor_code === '') {
            echo json_encode(['status' => 'fail', 'message' => 'Vendor code is required (letters/numbers/_/-).']);
            return;
        }

        if ($id <= 0 && $api_key === '') {
            echo json_encode(['status' => 'fail', 'message' => 'API key is required while creating a vendor.']);
            return;
        }

        $this->db->from('lead_api_vendors');
        $this->db->where('vendor_code', $vendor_code);
        if ($id > 0) {
            $this->db->where('id !=', $id);
        }
        $exists = $this->db->count_all_results();

        if ($exists > 0) {
            echo json_encode(['status' => 'fail', 'message' => 'Vendor code already exists. Use a unique code.']);
            return;
        }

        $data = [
            'vendor_code' => $vendor_code,
            'vendor_name' => $vendor_name,
            'is_active' => $is_active,
        ];

        $api_key_updated = false;
        if ($api_key !== '') {
            $data['api_key_hash'] = password_hash($api_key, PASSWORD_BCRYPT);
            $api_key_updated = true;
        }

        if ($id > 0) {
            $this->db->where('id', $id);
            $this->db->update('lead_api_vendors', $data);
            $message = 'Vendor updated successfully.';
        } else {
            $created_by = (int) $this->customlib->getStaffID();
            $data['created_by'] = $created_by > 0 ? $created_by : 1;
            $this->db->insert('lead_api_vendors', $data);
            $id = (int) $this->db->insert_id();
            $message = 'Vendor created successfully.';
        }

        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'id' => $id,
            'api_key_updated' => $api_key_updated ? 1 : 0,
        ]);
    }

    public function ajax_toggle_lead_vendor()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_edit')) {
            access_denied();
        }

        $this->ensureLeadVendorTable();

        $id = (int) $this->input->post('id');
        $is_active = $this->input->post('is_active') ? 1 : 0;

        if ($id <= 0) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid vendor id.']);
            return;
        }

        $this->db->where('id', $id);
        $this->db->update('lead_api_vendors', ['is_active' => $is_active]);

        echo json_encode([
            'status' => 'success',
            'message' => $is_active ? 'Vendor activated successfully.' : 'Vendor deactivated successfully.',
        ]);
    }

    public function ajax_delete_lead_vendor()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_edit')) {
            access_denied();
        }

        $this->ensureLeadVendorTable();

        $id = (int) $this->input->post('id');
        if ($id <= 0) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid vendor id.']);
            return;
        }

        $this->db->where('id', $id);
        $this->db->delete('lead_api_vendors');

        echo json_encode(['status' => 'success', 'message' => 'Vendor deleted successfully.']);
    }
    

    // ========================================================================
    // Monthly Leave Increment Rules Management
    // ========================================================================
    
    /**
     * Get all leave increment rules
     */
    private function get_leave_increment_rules()
    {
        return $this->db
            ->select('mlr.*, lt.type as leave_type_name')
            ->from('monthly_leave_increment_rules mlr')
            ->join('leave_types lt', 'mlr.leave_type_id = lt.id', 'left')
            ->order_by('lt.type', 'asc')
            ->get()
            ->result_array();
    }
    
    /**
     * AJAX: Get leave increment rules (for datatable)
     */
    public function ajax_get_leave_rules()
    {
        $rules = $this->get_leave_increment_rules();
        echo json_encode(['status' => 'success', 'data' => $rules]);
    }
    
    /**
     * AJAX: Save/Update leave increment rule
     */
    public function ajax_save_leave_rule()
    {
        $id = $this->input->post('id');
        $leave_type_id = $this->input->post('leave_type_id');
        $increment_days = $this->input->post('increment_days');
        $enabled = $this->input->post('enabled') ? 1 : 0;
        
        if ($id) {
            // Update existing rule - only update increment_days and enabled
            $data = [
                'increment_days' => $increment_days,
                'enabled' => $enabled
            ];
            $this->db->where('id', $id);
            $this->db->update('monthly_leave_increment_rules', $data);
            $message = 'Rule updated successfully';
        } else {
            // Check if rule already exists for this leave type
            $existing = $this->db->where('leave_type_id', $leave_type_id)
                ->get('monthly_leave_increment_rules')
                ->row();
            
            if ($existing) {
                echo json_encode(['status' => 'fail', 'message' => 'Rule already exists for this leave type']);
                return;
            }
            
            // Insert new rule - include all fields
            $data = [
                'leave_type_id' => $leave_type_id,
                'increment_days' => $increment_days,
                'enabled' => $enabled
            ];
            $this->db->insert('monthly_leave_increment_rules', $data);
            $message = 'Rule added successfully';
        }
        
        echo json_encode(['status' => 'success', 'message' => $message]);
    }
    
    /**
     * AJAX: Delete leave increment rule
     */
    public function ajax_delete_leave_rule()
    {
        $id = $this->input->post('id');
        
        if (!$id) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid rule ID']);
            return;
        }
        
        $this->db->where('id', $id);
        $this->db->delete('monthly_leave_increment_rules');
        
        echo json_encode(['status' => 'success', 'message' => 'Rule deleted successfully']);
    }
    
    /**
     * AJAX: Toggle rule status (enable/disable)
     */
    public function ajax_toggle_leave_rule()
    {
        $id = $this->input->post('id');
        $enabled = $this->input->post('enabled') ? 1 : 0;
        
        if (!$id) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid rule ID']);
            return;
        }
        
        $this->db->where('id', $id);
        $this->db->update('monthly_leave_increment_rules', ['enabled' => $enabled]);
        
        $status_text = $enabled ? 'enabled' : 'disabled';
        echo json_encode(['status' => 'success', 'message' => "Rule {$status_text} successfully"]);
    }
    
}
