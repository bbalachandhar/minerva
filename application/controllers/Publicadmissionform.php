<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class PublicAdmissionForm extends CI_Controller
{
    /**
     * Summary of sch_setting_detail
     * @var 
     */
    public $sch_setting_detail;
    public $front_setting;
    public $base_assets_url;
    public $data = array(); // Initialize $this->data

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->helper('language');
        $this->load->database();
        $this->load->model('language_model');
        $this->load->model('setting_model');
        $this->sch_setting_detail = $this->setting_model->getSetting(); // Load school settings
        $this->load->model(array('frontcms_setting_model', 'complaint_Model', 'Visitors_model', 'onlinestudent_model', 'filetype_model', 'customfield_model', 'examgroupstudent_model', 'examgroup_model', 'grade_model', 'marksdivision_model', 'currency_model', 'section_model','holiday_model', 'class_model', 'category_model', 'student_model', 'Online_admission_ug_details_model', 'Online_admission_pg_details_model', 'Online_admission_lateral_details_model', 'Online_admission_references_model', 'Online_admission_nata_details_model', 'notificationsetting_model', 'enquiry_model', 'Onlineadmissioncourses_model'));
        $this->load->model('examstudent_model');
        $this->load->config('form-builder');
        $this->load->config('app-config');
        $this->load->library(array('mailer', 'form_builder', 'mailsmsconf'));
        $this->blood_group = $this->config->item('bloodgroup');
        $this->load->library('Ajax_pagination');
        $this->load->library('module_lib');
        $this->load->library('captchalib');
        $this->load->library('customlib');
        $this->load->helper('customfield');
        $this->load->helper('custom');
        $this->load->library(array('enc_lib', 'cart', 'auth'));
        $this->load->library('gateway_ins/billdesk_lib'); // Load BillDesk Library
        $this->load->model('gateway_ins_model'); // For tracking gateway transactions
        $this->load->model('paymentsetting_model'); // For getting active payment gateway settings

        // Initialize front_setting as it's used in Public_admission controller
        $this->front_setting = $this->frontcms_setting_model->get();

        // Initialize base_assets_url as it's used in Public_admission controller
        // THEMES_DIR is defined in MY_Controller.php, need to define it here or fetch
        // For now, let's hardcode 'default' theme for base_assets_url
        $theme = 'default'; // Assuming 'default' theme, adjust if front_setting->theme is available
        if (is_object($this->front_setting) && !empty($this->front_setting->theme)) {
            $theme = $this->front_setting->theme;
        }
        $this->base_assets_url = 'backend/themes/' . $theme . '/';
        $this->data['base_assets_url'] = base_url() . $this->base_assets_url;

        // Ensure $this->data['sch_setting'] is available for views
        $this->data['sch_setting'] = $this->sch_setting_detail;
        
        // --- START Language Loading Logic (copied from Front_Controller) ---
        $this->school_details = $this->setting_model->getSchoolDetail();
        $language = ($this->school_details->language); // Get the active language
        $this->config->set_item('language', $language);
        $this->load->helper(array('directory', 'custom'));
        $lang_array = array('form_validation_lang');
        $map        = directory_map(APPPATH . "./language/" . $language . "/app_files");
        foreach ($map as $lang_key => $lang_value) {
            $lang_array[] = 'app_files/' . str_replace(".php", "", $lang_value);
        }
        $this->load->language($lang_array, $language); // Load all language files
        // --- END Language Loading Logic ---
        
        // Ensure $this->data is initialized
        // Removed: $this->data = array(); // Already initialized above, avoid overwriting
    }

    public function index()
    {
        $this->data['name'] = $this->input->get('name');
        $this->data['email'] = $this->input->get('email');
        $this->data['mobileno'] = $this->input->get('mobileno');
        $this->data['enquiry_id'] = $this->input->get('enquiry_id');
        // This is a direct copy from Public_admission::index(), adapted for CI_Controller
        // No checks for module_lib->hasActive('online_admission') here, assume always active for this controller
        $this->data['active_menu'] = 'online_admission';
        $page                      = array('title' => 'Online Admission Form', 'meta_title' => 'online admission form', 'meta_keyword' => 'online admission form', 'meta_description' => 'online admission form');
        $this->data['page_side_bar']  = false;
        $this->data['featured_image'] = false;
        $this->data['page']           = $page;

        $header_footer = $this->setting_model->get_printheader();
        $this->data['header_image'] = '';
        if ($header_footer) {
            foreach($header_footer as $head_foot){
                if($head_foot['print_type'] == 'general_purpose'){ // Fixed array access
                    $this->data['header_image'] = $head_foot['header_image']; // Fixed array access
                    break;
                }
            }
        }
        
        ///============
        $this->data['form_admission'] = $this->setting_model->getOnlineAdmissionStatus();
        $genderList                   = $this->customlib->getGender();
        $this->data['genderList']     = $genderList;
        $this->data['title']          = 'Add Student';
        $this->data['title_list']     = 'Online Admission Form';
        $data_internal["student_categorize"]   = 'class'; // Renamed to avoid conflict with $this->data
        $session                      = $this->setting_model->getCurrentSession();
        $class                        = $this->class_model->getAll();
        $this->data['classlist']      = $class;
        // $this->data['sch_setting'] is set in constructor
        $category                     = $this->category_model->get();
        $this->data['categorylist']   = $category;
        $this->data["bloodgroup"]     = $this->blood_group;
        $houses                       = $this->student_model->gethouselist();
        $this->data['houses']         = $houses;
        $reference_no                 = "";
        $refence_status               = "";
        $sch_setting                  = $this->sch_setting_detail; // Already set in constructor
        $setting_data               = $this->setting_model->get();
        
        $this->data['setting_data'] = $setting_data;
        $this->data['online_admission_instruction']      = $sch_setting->online_admission_instruction;
        $this->data['online_admission_application_form'] = $sch_setting->online_admission_application_form;
        $this->data['ug_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year');
        $this->data['ug_lateral_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral');
        $this->data['pg_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year');
        
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }
        
        $is_captcha = $this->captchalib->is_captcha('admission');
        $this->data["is_captcha"]      = $is_captcha;
    
        if ($this->captchalib->is_captcha('admission')) {
            if($this->input->post('captcha')){
                $this->form_validation->set_rules('captcha', $this->lang->line('captcha'), 'trim|required|callback_check_captcha');
            }else{
                $this->form_validation->set_rules('captcha', $this->lang->line('captcha'), 'trim|required');
            }
        }

        if ($this->customlib->getfieldstatus('student_email')) {
            $this->form_validation->set_rules(
                'email', $this->lang->line('email'), array(
                    'trim', 'valid_email', 'required',
                    array('check_student_email_exists', array($this->onlinestudent_model, 'check_student_email_exists')),
                )
            );
        }

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('firstname', $this->lang->line('first_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');          
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('community', 'Community', 'trim|required|xss_clean');

        //remove script from other fields (these are just form validation rules)
            $this->form_validation->set_rules('middlename', $this->lang->line('middlename'), 'trim|xss_clean');
            $this->form_validation->set_rules('lastname', $this->lang->line('lastname'), 'trim|xss_clean');
            $this->form_validation->set_rules('mobileno', $this->lang->line('mobileno'), 'trim|xss_clean');
            $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|xss_clean');
            $this->form_validation->set_rules('category_id', $this->lang->line('category_id'), 'trim|xss_clean');
            $this->form_validation->set_rules('religion', $this->lang->line('religion'), 'trim|xss_clean');
            $this->form_validation->set_rules('cast', $this->lang->line('cast'), 'trim|xss_clean');;
            $this->form_validation->set_rules('community', 'Community', 'trim|required|xss_clean');
            $this->form_validation->set_rules('house', $this->lang->line('house'), 'trim|xss_clean');
            $this->form_validation->set_rules('blood_group', $this->lang->line('blood_group'), 'trim|xss_clean');
            $this->form_validation->set_rules('height', $this->lang->line('height'), 'trim|xss_clean');
            $this->form_validation->set_rules('weight', $this->lang->line('weight'), 'trim|xss_clean');
            $this->form_validation->set_rules('measure_date', $this->lang->line('measure_date'), 'trim|xss_clean');
            $this->form_validation->set_rules('father_name', $this->lang->line('father_name'), 'trim|xss_clean');
            $this->form_validation->set_rules('father_phone', $this->lang->line('father_phone'), 'trim|xss_clean');
            $this->form_validation->set_rules('father_occupation', $this->lang->line('father_occupation'), 'trim|xss_clean');
            $this->form_validation->set_rules('mother_name', $this->lang->line('mother_name'), 'trim|xss_clean');
            $this->form_validation->set_rules('mother_phone', $this->lang->line('mother_phone'), 'trim|xss_clean');
            $this->form_validation->set_rules('mother_occupation', $this->lang->line('mother_occupation'), 'trim|xss_clean');
            $this->form_validation->set_rules('previous_school', $this->lang->line('previous_school'), 'trim|xss_clean');
            $this->form_validation->set_rules('note', $this->lang->line('note'), 'trim|xss_clean');
            $this->form_validation->set_rules('current_address', $this->lang->line('current_address'), 'trim|xss_clean');
            $this->form_validation->set_rules('permanent_address', $this->lang->line('permanent_address'), 'trim|xss_clean');
            $this->form_validation->set_rules('bank_account_no', $this->lang->line('bank_account_no'), 'trim|xss_clean');
            $this->form_validation->set_rules('bank_name', $this->lang->line('bank_name'), 'trim|xss_clean');
            $this->form_validation->set_rules('ifsc_code', $this->lang->line('ifsc_code'), 'trim|xss_clean');
            $this->form_validation->set_rules('adhar_no', $this->lang->line('adhar_no'), 'trim|xss_clean');
            $this->form_validation->set_rules('samagra_id', $this->lang->line('samagra_id'), 'trim|xss_clean');
            $this->form_validation->set_rules('rte', $this->lang->line('rte'), 'trim|xss_clean');
            $this->form_validation->set_rules('guardian_email', $this->lang->line('guardian_email'), 'trim|xss_clean');
            $this->form_validation->set_rules('guardian_phone', $this->lang->line('guardian_phone'), 'trim|xss_clean');
            $this->form_validation->set_rules('guardian_occupation', $this->lang->line('guardian_occupation'), 'trim|xss_clean');
            $this->form_validation->set_rules('guardian_address', $this->lang->line('guardian_address'), 'trim|xss_clean');
        //remove script from other fields

        if ($this->customlib->getfieldstatus('if_guardian_is')) {
            $this->form_validation->set_rules('guardian_is', $this->lang->line('guardian'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('guardian_name', $this->lang->line('guardian_name'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('guardian_relation', $this->lang->line('guardian_relation'), 'trim|required|xss_clean'); 
        }

        if (!empty($_FILES['document']['name'])) {
            $this->form_validation->set_rules('document', $this->lang->line('documents'), 'callback_document_handle_upload[document]');
        }

        if (!empty($_FILES['father_pic']['name'])) {
            $this->form_validation->set_rules('father_pic', $this->lang->line('father_photo'), 'callback_image_handle_upload[father_pic]');
        }

        if (!empty($_FILES['mother_pic']['name'])) {
            $this->form_validation->set_rules('mother_pic', $this->lang->line('mother_photo'), 'callback_image_handle_upload[mother_pic]');
        }

        if (!empty($_FILES['file']['name'])) {
            $this->form_validation->set_rules('file', $this->lang->line('student_photo'), 'callback_image_handle_upload[file]');
        }

        if (!empty($_FILES['guardian_pic']['name'])) {
            $this->form_validation->set_rules('guardian_pic', $this->lang->line('guardian_photo'), 'callback_image_handle_upload[guardian_pic]');
        }

        $custom_fields = $this->customfield_model->getByBelong('students');
        
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation'] && $this->customlib->getfieldstatus($custom_fields_value['name'])) {
                $custom_fields_id   = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[students][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required|xss_clean');
            }
        }
     
        // Explicitly set base_assets_url - it's usually set by load_theme
        // THEMES_DIR is defined in MY_Controller.php (assuming it's loaded in some way or hardcoded)
        if (!defined('THEMES_DIR')) {
            define('THEMES_DIR', 'themes'); // Define if not already defined
        }

        // The theme is determined by front_setting->theme
        $theme = 'default';
        if (is_object($this->front_setting) && !empty($this->front_setting->theme)) {
            $theme = $this->front_setting->theme;
        }

        $this->base_assets_url = 'backend/' . THEMES_DIR . '/' . $theme . '/';
        $this->data['base_assets_url'] = base_url() . $this->base_assets_url;

        // Ensure $front_setting is available in $this->data
        $this->data['front_setting'] = $this->front_setting; // This is already set in Front_Controller::__construct() and Public_admission::__construct()

        // Ensure $school_setting is available. Front_Controller::__construct() sets $this->school_details.
        $this->data['school_setting'] = $this->sch_setting_detail;
        
                        $this->load->model('Onlineadmissioncourse_model');
                        $this->data['admission_courses'] = $this->Onlineadmissioncourse_model->get(); // Fetch all courses from the new table
                        
                        // Handle form validation
                        if ($this->sch_setting_detail->institution_type == 'school') {                    if ($this->form_validation->run() == false) {
                        // Render the main form content (pages/admission) into a variable
                        $this->data['content'] = $this->load->view('themes/' . $theme . '/pages/admission', $this->data, true);
                        
                        // Now load the custom template, passing all collected data
                        $this->load->view('themes/' . $theme . '/pages/public_admission_template', $this->data);
                    } else {
                        $document_validate  = true;
                        $custom_field_post  = $this->input->post("custom_fields[students]");
                        $custom_value_array = array();
                        if (!empty($custom_field_post)) {
        
                            foreach ($custom_field_post as $key => $value) {
                                $check_field_type = $this->input->post("custom_fields[students][" . $key . "]");
                                $field_value      = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                                $array_custom     = array(
                                    'belong_table_id' => 0,
                                    'custom_field_id' => $key,
                                    'field_value'     => $field_value,
                                );
                                $custom_value_array[] = $array_custom;
                            }
                        }
        
                        if ($document_validate) {
        
                            $class_id   = $this->input->post('class_id');
                            $section_id = $this->input->post('section_id');
        
                            $data_db = array( // Renamed to avoid conflict with $data property
                                'firstname'        => $this->input->post('firstname'),
                                'class_section_id' => $this->input->post('section_id'),
                                'dob'              => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob'))),
                                'gender'           => $this->input->post('gender'),
                            );
                            // for inserting system fields
        
                            if ($this->customlib->getfieldstatus('if_guardian_is')) {
                                $data_db['guardian_is'] = $this->input->post('guardian_is');
        
                                $data_db['guardian_name']     = $this->input->post('guardian_name');
                                $data_db['guardian_relation'] = $this->input->post('guardian_relation');
                                $data_db['guardian_phone']    = $this->input->post('guardian_phone');
        
                                if ($this->customlib->getfieldstatus('guardian_occupation')) {
                                    $data_db['guardian_occupation'] = $this->input->post('guardian_occupation');
                                }
                                if ($this->customlib->getfieldstatus('guardian_email')) {
                                    $data_db['guardian_email'] = $this->input->post('guardian_email');
                                }
                                if ($this->customlib->getfieldstatus('guardian_address')) {
                                    $data_db['guardian_address'] = $this->input->post('guardian_address');
                                }
                            }
        
                            $middlename       = $this->input->post('middlename');
                            $lastname         = $this->input->post('lastname');
                            $mobileno         = $this->input->post('mobileno');
                            $email            = $this->input->post('email');
                            $category_id      = $this->input->post('category_id');
                            $religion         = $this->input->post('religion');
                            $cast             = $this->input->post('cast');
                            $community        = $this->input->post('community');
                            $house            = empty2null($this->input->post('house'));
                            $blood_group      = $this->input->post('blood_group');
                            $height           = $this->input->post('height');
                            $weight           = $this->input->post('weight');
                            $measurement_date = $this->input->post('measure_date');
        
                            $father_name       = $this->input->post('father_name');
                            $father_phone      = $this->input->post('father_phone');
                            $father_occupation = $this->input->post('father_occupation');
        
                            $mother_name       = $this->input->post('mother_name');
                            $mother_phone      = $this->input->post('mother_phone');
                            $mother_occupation = $this->input->post('mother_occupation');
                            $previous_school   = $this->input->post('previous_school');
                            $note              = $this->input->post('note');
        
                            $current_address   = $this->input->post('current_address');
                            $permanent_address = $this->input->post('permanent_address');
        
                            $bank_account_no = $this->input->post('bank_account_no');
                            $bank_name       = $this->input->post('bank_name');
                            $ifsc_code       = $this->input->post('ifsc_code');
                            $adhar_no        = $this->input->post('adhar_no');
                            $samagra_id      = $this->input->post('samagra_id');
                            $rte             = $this->input->post('rte');
        
                            if (isset($middlename)) {
                                $data_db['middlename'] = $this->input->post('middlename');
                            }
                            if (isset($lastname)) {
                                $data_db['lastname'] = $this->input->post('lastname');
                            }
                            if (isset($mobileno)) {
                                $data_db['mobileno'] = $this->input->post('mobileno');
                            }
                            if (isset($email)) {
                                $data_db['email'] = $this->input->post('email');
                            }  
                            if ($category_id) {
                                $data_db['category_id'] = $this->input->post('category_id');
                            }else{
                                $data_db['category_id'] = NULL;
                            }
                            if (isset($religion)) {
                                $data_db['religion'] = $this->input->post('religion');
                            }
                            if (isset($cast)) {
                                $data_db['cast'] = $this->input->post('cast');
                            }
                            if (isset($community)) {
                                $data_db['community'] = $this->input->post('community');
                            }
                            if (isset($house)) {
                                $data_db['school_house_id'] = $this->input->post('house');
                            }
                            if (isset($blood_group)) {
                                $data_db['blood_group'] = $this->input->post('blood_group');
                            }
                            if (isset($height)) {
                                $data_db['height'] = $this->input->post('height');
                            }
                            if (isset($weight)) {
                                $data_db['weight'] = $this->input->post('weight');
                            }
                            if (isset($weight)) {
                                $data_db['weight'] = $this->input->post('weight');
                            }
                            if (!empty($measurement_date)) {
                                $data_db['measurement_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('measure_date')));
                            }
                            if (isset($father_name)) {
                                $data_db['father_name'] = $this->input->post('father_name');
                            }
                            if (isset($father_phone)) {
                                $data_db['father_phone'] = $this->input->post('father_phone');
                            }
                            if (isset($father_occupation)) {
                                $data_db['father_occupation'] = $this->input->post('father_occupation');
                            }
                            if (isset($mother_name)) {
                                $data_db['mother_name'] = $this->input->post('mother_name');
                            }
                            if (isset($mother_phone)) {
                                $data_db['mother_phone'] = $this->input->post('mother_phone');
                            }
                            if (isset($mother_occupation)) {
                                $data_db['mother_occupation'] = $this->input->post('mother_occupation');
                            }
                            if ($current_address) {
                                $data_db['current_address'] = $this->input->post('current_address');
                            }
                            if ($permanent_address) {
                                $data_db['permanent_address'] = $this->input->post('permanent_address');
                            }
                            if (isset($bank_account_no)) {
                                $data_db['bank_account_no'] = $this->input->post('bank_account_no');
                            }
                            if (isset($bank_name)) {
                                $data_db['bank_name'] = $this->input->post('bank_name');
                            }
                            if (isset($ifsc_code)) {
                                $data_db['ifsc_code'] = $this->input->post('ifsc_code');
                            }
                            if (isset($adhar_no)) {
                                $data_db['adhar_no'] = $this->input->post('adhar_no');
                            }
                            if (isset($samagra_id)) {
                                $data_db['samagra_id'] = $this->input->post('samagra_id');
                            }
                            if (isset($note)) {
                                $data_db['note'] = $this->input->post('note');
                            }
                            if (isset($previous_school)) {
                                $data_db['previous_school'] = $this->input->post('previous_school');
                            }
                            if (isset($rte)) {
                                $data_db['rte'] = $this->input->post('rte');
                            }
        
                            do {
                                $reference_no   = mt_rand(100000, 999999);
                                $refence_status = $this->onlinestudent_model->checkreferenceno($reference_no);
                            } while ($refence_status);
        
                            $data_db['reference_no'] = $reference_no;
        
                            if (isset($_FILES["document"]) && !empty($_FILES['document']['name'])) {
                                $upload_result = $this->media_storage->fileupload("document", "./uploads/student_documents/online_admission_doc/");
                                if ($upload_result['status'] === false) {
                                    $this->session->set_flashdata('error', $upload_result['message']);
                                    redirect('public_admission');
                                }
                                $img_name         = $upload_result['message'];
                                
                                $data_db['document'] = $img_name;
                            }
        
                            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                                $upload_result = $this->media_storage->fileupload("file", "./uploads/student_images/online_admission_image/");
                                if ($upload_result['status'] === false) {
                                    $this->session->set_flashdata('error', $upload_result['message']);
                                    redirect('public_admission');
                                }
                                $img_name      = $upload_result['message'];
                                $data_db['image'] = 'uploads/student_images/online_admission_image/' . $img_name;
                            }
        
                            if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                                $upload_result = $this->media_storage->fileupload("father_pic", "./uploads/student_images/online_admission_image/");
                                if ($upload_result['status'] === false) {
                                    $this->session->set_flashdata('error', $upload_result['message']);
                                    redirect('public_admission');
                                }
                                $img_name           = $upload_result['message'];
                                $data_db['father_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                            }
        
                            if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                                $upload_result = $this->media_storage->fileupload("mother_pic", "./uploads/student_images/online_admission_image/");
                                if ($upload_result['status'] === false) {
                                    $this->session->set_flashdata('error', $upload_result['message']);
                                    redirect('public_admission');
                                }
                                $img_name           = $upload_result['message'];
                                $data_db['mother_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                            }
        
                                                if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
        
                                                    $upload_result = $this->media_storage->fileupload("guardian_pic", "./uploads/student_images/online_admission_image/");
        
                                                    if ($upload_result['status'] === false) {
        
                                                        $this->session->set_flashdata('error', $upload_result['message']);
        
                                                        redirect('public_admission');
        
                                                    }
        
                                                    $img_name             = $upload_result['message'];
        
                                                    $data_db['guardian_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
        
                                                }
                            $data_db['hostel_room_id']      = null;
                            
                            
                            $insert_id = $this->onlinestudent_model->add($data_db); // Renamed to avoid conflict with $data property
                            if (!empty($custom_value_array)) {
                                $this->customfield_model->onlineadmissioninsertRecord($custom_value_array, $insert_id);
                            }
        
                            $this->data['class_id']            = $class_id;
                            $this->data['section_id']          = $section_id;
                            $this->data['roll_no']             = $this->input->post('roll_no');
                            $this->data['mobileno']            = $this->input->post('mobileno');
                            $this->data['email']               = $this->input->post('email');
                            $this->data['firstname']           = $this->input->post('firstname');
                            $this->data['lastname']            = $this->input->post('lastname');
                            $this->data['mobileno']            = $this->input->post('mobileno');
                            $this->data['class_section_id']    = $this->input->post('section_id');
                            $this->data['guardian_is']         = $this->input->post('guardian_is');
                            $this->data['dob']                 = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob')));
                            $this->data['ifsc_code']           = $this->input->post('ifsc_code');
                            $this->data['bank_account_no']     = $this->input->post('bank_account_no');
                            $this->data['bank_name']           = $this->input->post('bank_name');
                            $this->data['current_address']     = $this->input->post('current_address');
                            $this->data['permanent_address']   = $this->input->post('permanent_address');
                            $this->data['father_name']         = $this->input->post('father_name');
                            $this->data['father_phone']        = $this->input->post('father_phone');
                            $this->data['father_occupation']   = $this->input->post('father_occupation');
                            $this->data['mother_name']         = $this->input->post('mother_name');
                            $this->data['mother_phone']        = $this->input->post('mother_phone');
                            $this->data['mother_occupation']   = $this->input->post('mother_occupation');
                            $this->data['guardian_occupation'] = $this->input->post('guardian_occupation');
                            $this->data['guardian_email']      = $this->input->post('guardian_email');
                            $this->data['gender']              = $this->input->post('gender');
                            $this->data['guardian_name']       = $this->input->post('guardian_name');
                            $this->data['guardian_relation']   = $this->input->post('guardian_relation');
                            $this->data['guardian_phone']      = $this->input->post('guardian_phone');
                            $this->data['guardian_address']    = $this->input->post('guardian_address');
                            $this->data['note']                = $this->input->post('note');
                            $this->data['previous_school']     = $this->input->post('previous_school');
                            $this->data['house']               = $this->input->post('house');
                            $this->data['blood_group']         = $this->input->post('blood_group');
                            $this->data['measure_date']         = $this->input->post('measure_date');
                            
                            $this->data['admission_id']        = $insert_id;
                            $this->session->set_userdata('validlogin', $reference_no);
                            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . ' ' . $this->lang->line('thanks_for_registration_please_note_your_reference_number') . ' ' . $reference_no . ' ' . $this->lang->line('for_further_communication') . '</div>');
                                                redirect('public_admission/online_admission_review/' . $reference_no);
                        }
                    }
                } else {
                    $this->load->view('public_admission/college_admission', $this->data);
                }
    }
                
                    public function download($id)
                    {
                        $settinglist = $this->setting_model->get($id);         
                        $this->media_storage->filedownload($settinglist['online_admission_application_form'], "./uploads/admission_form");
                    }

    public function image_handle_upload($str, $var)
    {
        $result         = $this->filetype_model->get();

        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {

            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];  
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES[$var]['tmp_name']);

            error_log("Debug image_handle_upload: file_name=" . $file_name . ", file_type=" . $file_type . ", ext=" . $ext . ", mtype=" . $mtype . ", allowed_extension=" . implode(',', $allowed_extension) . ", allowed_mime_type=" . implode(',', $allowed_mime_type));

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('image_handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('image_handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }

            // The effective_max_size for image is 300KB
            $configured_max_size = (isset($result->image_size) && $result->image_size > 0) ? $result->image_size : (300 * 1024); // Use 300KB if not configured or 0
            $effective_max_size = min($configured_max_size, (300 * 1024)); // Ensure it's not greater than 300KB
            
            if ($file_size > $effective_max_size) {
                $this->form_validation->set_message('image_handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($effective_max_size / 1024, 0) . " KB");
                return false;
            }
            return true;
        }
        return true;
    }
    
    public function document_handle_upload($str, $var)
    {
        $result         = $this->filetype_model->get();

        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {

            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES[$var]['tmp_name']);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('document_handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('document_handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }

            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('document_handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                return false;
            }
            return true;
        }
        return true;
    }

    public function getSections()
    {
        $class_id = $this->input->post('class_id');
        $data     = $this->section_model->getClassBySectionAll($class_id);
        echo json_encode($data);
    }

        public function online_admission_review($reference_no=null)
        {
            $ref_status    = $this->onlinestudent_model->checkreferenceno($reference_no);
            $admin_session = $this->session->userdata('admin');
    
            $status = "";
            if (!empty($admin_session)) {
                $status = "admin";
            }
            if ($this->session->userdata('validlogin') != $reference_no && $status != "admin" ) {
                exit('No direct script access allowed');
            }
            $currencies = get_currency_list();
            if ($ref_status) {
    
                $this->data['active_menu'] = 'online_admission';
                $page                      = array('title' => 'online admission review', 'meta_title' => 'online admission review', 'meta_keyword' => 'online admission review', 'meta_description' => 'online admission review');
    
                $this->data['page_side_bar']  = false;
                $this->data['featured_image'] = false;
                $this->data['page']           = $page;
                $this->data['meta_title']     = 'Oline Admission Review';
                ///============
                $this->data['status']           = $status;
                $this->data['form_admission']   = $this->setting_model->getOnlineAdmissionStatus();
                $genderList                     = $this->customlib->getGender();
                $this->data['genderList']       = $genderList;
                $this->data['title']            = 'Add Student';
                $this->data['title_list']       = 'Recently Added Student';
                $data_internal["student_categorize"]     = 'class';
                $session                        = $this->setting_model->getCurrentSession();
                $id                             = $this->onlinestudent_model->getidbyrefno($reference_no);
                $class                          = $this->class_model->getAll();
                $this->data['classlist']        = $class;
                $this->data['sch_setting']      = $this->sch_setting_detail;
                $category                       = $this->category_model->get();
                $this->data['categorylist']     = $category;
                $result                         = $this->onlinestudent_model->get($id);
                $classresult = $this->onlinestudent_model->getclassbyclasssectionid($result['class_section_id']);
                if ($classresult) {
                    $class_id   = $classresult['class_id'];
                    $class_name = $classresult['class'];
                } else {
                    $class_id   = "";
                    $class_name = "";
                }
                $this->data['class_name']       = $class_name;
                $this->data['class_section_id'] = $result['section_id'];
                $this->data['firstname']        = $result['firstname'];
                $this->data['middlename']       = $result['middlename'];
                $this->data['lastname']         = $result['lastname'];
                $this->data['gender']           = $result['gender'];
                if ($result['dob'] != null && $result['dob'] != '0000-00-00') {
                    $this->data['dob'] = $result['dob'];
                } else {
                    $this->data['dob'] = "";
                }
                $this->data['mobileno']    = $result['mobileno'];
                $this->data['email']       = $result['email'];
                $this->data['category_id'] = $result['category_id'];
                $this->data['category']    = $result['category'];
                $this->data['religion']    = $result['religion'];
                $this->data['cast']        = $result['cast'];
                if ($result['school_house_id'] != 0) {
                    $this->data['house_name'] = $this->customlib->gethousename($result['school_house_id']);
                } else {
                    $this->data['house_name'] = "";
                }
                $this->data['house_id']    = $result['school_house_id'];
                $this->data['blood_group'] = $result['blood_group'];
                $this->data['height']      = $result['height'];
                $this->data['weight']      = $result['weight'];
    
                if ($result['measurement_date'] != null && $result['measurement_date'] != '0000-00-00') {
                    $this->data['measurement_date'] = date($this->customlib->dateformat($result['measurement_date']));
                } else {
                    $this->data['measurement_date'] = "";
                }
    
                $this->data['application_date'] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat(date("Y-m-d", strtotime($result['created_at']))));
    
                $this->data['student_pic'] = $result['image'];
                $this->data['father_name']       = $result['father_name'];
                $this->data['father_phone']      = $result['father_phone'];
                $this->data['father_occupation'] = $result['father_occupation'];
                $this->data['father_pic']        = $result['father_pic'];
                $this->data['mother_name']       = $result['mother_name'];
                $this->data['mother_phone']      = $result['mother_phone'];
                $this->data['mother_occupation'] = $result['mother_occupation'];
                $this->data['mother_pic']        = $result['mother_pic'];
                $this->data['guardian_is']         = $result['guardian_is'];
                $this->data['guardian_name']       = $result['guardian_name'];
                $this->data['guardian_relation']   = $result['guardian_relation'];
                $this->data['guardian_email']      = $result['guardian_email'];
                $this->data['guardian_pic']        = $result['guardian_pic'];
                $this->data['guardian_phone']      = $result['guardian_phone'];
                $this->data['guardian_occupation'] = $result['guardian_occupation'];
                $this->data['guardian_address']    = $result['guardian_address'];
                $this->data['current_address']   = $result['current_address'];
                $this->data['permanent_address'] = $result['permanent_address'];
                $this->data['bank_account_no'] = $result['bank_account_no'];
                $this->data['bank_name']       = $result['bank_name'];
                $this->data['ifsc_code']       = $result['ifsc_code'];
                $this->data['adhar_no']        = $result['adhar_no'];
                $this->data['samagra_id']      = $result['samagra_id'];
                $this->data['previous_school'] = $result['previous_school'];
                $this->data['note']            = $result['note'];
                $this->data['rte']             = $result['rte'];
                $this->data['reference_no']    = $result['reference_no'];
                $this->data['transaction_id']  = $this->customlib->gettransactionid($result['id']);
                $this->data['transaction_paid_amount']  = $this->customlib->gettransactionpaidamount($result['id']);
                $this->data['form_status']  = $result['form_status'];
                $this->data['paid_status']  = $result['paid_status'];
                $this->data['admission_id'] = $id;
                $this->data['reference_no'] = $result['reference_no'];
                $this->data['id']           = $id;
                $this->data['online_admission_payment'] = $this->sch_setting_detail->online_admission_payment;
                $this->data['online_admission_amount']  = $this->sch_setting_detail->online_admission_amount;
                $this->data['online_admission_conditions'] = $this->sch_setting_detail->online_admission_conditions;
                $setting_data                              = $this->setting_model->get();
                $this->data['setting_data'] = $setting_data;
                $this->data['currencies'] = $currencies;
                
                if ($this->module_lib->hasModule('online_course')) {
                    $this->load->model('course_model');
                    $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
                }
            
                $this->load->view('public_admission/online_admission_review', $this->data);
     
            } else {
                $this->show_404();
            }
        }
    public function check_captcha($str)
    {
        if ($str == $this->session->userdata('captcha_code')) {
            return TRUE;
        } else {
            $this->form_validation->set_message('check_captcha', 'Incorrect captcha code.');
            return FALSE;
        }
    }

    public function refreshCaptcha()
    {
        $captcha = $this->captchalib->generate_captcha();
        echo $captcha['image'];
    }

    public function checkadmissionstatus()
    {
        $this->form_validation->set_rules('refno', $this->lang->line('reference_no'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('student_dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

            $msg = array(
                'refno' => form_error('refno'),
                'dob'   => form_error('student_dob'),
            );
            $array = array('status' => '0', 'error' => $msg, 'msg' => $this->lang->line('something_went_wrong'));
        } else {

            $refno  = $this->input->post('refno');
            $dob    = $this->customlib->dateFormatToYYYYMMDD($this->input->post('student_dob'));
            $status = $this->onlinestudent_model->checkadmissionstatus($refno, $dob);

            if ($status == 0) {
                $array = array('status' => '2', 'error' => $this->lang->line('invalid_reference_number_or_date_of_birth'), 'msg' => '', 'refno' => $refno);
            } else {

                $is_enroll = $this->customlib->checkisenroll($refno);
                if ($is_enroll == 0) {
                    if (!empty($this->session->userdata('validlogin'))) {
                        $this->session->unset_userdata('validlogin');
                    }
                    $this->session->set_userdata('validlogin', $refno
                    );
                    $array = array('status' => '1', 'error' => '', 'msg' => '', 'id' => $status, 'refno' => $refno);
                } else {
                    $array = array('status' => '2', 'error' => $this->lang->line('you_enrollment_has_been_done_please_contact_to_school_administrator'), 'msg' => '', 'refno' => $refno);
                }

            }

        }

        echo json_encode($array);

    }

    public function submitadmission()
    {
        $this->form_validation->set_rules('checkterm', $this->lang->line('terms_conditions'), 'trim|required|xss_clean');
        $admission_id = $this->input->post('admission_id');

        if ($this->form_validation->run() == true) {
            $date = date('Y-m-d');

            $data = array('id' => $admission_id, 'form_status' => 1, 'submit_date' => $date);

            $this->onlinestudent_model->edit($data);
            $result = $this->onlinestudent_model->get($admission_id);

            $firstname    = $result['firstname'];
            $lastname     = $result['lastname'];
            $date         = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
            $reference_no = $result['reference_no'];
            $mobileno     = $result['mobileno'];
            $email        = $result['email'];

            $sender_details = array('firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'date' => $date, 'reference_no' => $reference_no, 'mobileno' => $mobileno, 'guardian_email' => $result['guardian_email'], 'guardian_phone' => $result['guardian_phone']);

            $this->mailsmsconf->mailsms('online_admission_form_submission', $sender_details);

            $array = array('status' => '1', 'error' => '', 'id' => $admission_id, 'msg' => '', 'reference_no' => $reference_no);

        } else {
            $array = array('status' => '0', 'error' => form_error('checkterm'), 'msg' => '');
        }
        echo json_encode($array);
    }

    public function checktermcondition()
    {
        $this->form_validation->set_rules('checkterm', $this->lang->line('terms_conditions'), 'trim|required|xss_clean');
        $admission_id = $this->input->post('admission_id');

        if ($this->form_validation->run() == true) {
            $array = array('status' => '1', 'error' => '');
        } else {
            $array = array('status' => '0', 'error' => form_error('checkterm'));
            echo json_encode($array);
        }
    }

    public function editonlineadmission($reference_no)
    {
        $ref_status = $this->onlinestudent_model->checkreferenceno($reference_no);
        
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }        
        
        if ($ref_status) {
             
            $this->data['active_menu'] = 'online_admission';
            $page                      = array('title' => 'Online Admission Form', 'meta_title' => 'online admission form', 'meta_keyword' => 'online admission form', 'meta_description' => 'online admission form');

            $this->data['page_side_bar']  = false;
            $this->data['featured_image'] = false;
            $this->data['page']           = $page;
            ///============
            $this->data['form_admission'] = $this->setting_model->getOnlineAdmissionStatus();
            $genderList                   = $this->customlib->getGender();
            $this->data['genderList']     = $genderList;
            $this->data['title']          = 'Add Student';
            $this->data['title_list']     = 'Recently Added Student';
            $data_internal["student_categorize"]   = 'class';
            $session                      = $this->setting_model->getCurrentSession();
            $class                        = $this->class_model->getAll();
            $this->data['classlist']      = $class;
            $this->data['sch_setting']    = $this->sch_setting_detail;
            $category                     = $this->category_model->get();
            $this->data['categorylist']   = $category;
            $class_id                     = $this->input->post('class_id');
            $section_id                   = $this->input->post('section_id');
            $this->data['form_admission'] = $this->setting_model->getOnlineAdmissionStatus();
            $genderList                   = $this->customlib->getGender();
            $this->data['genderList']     = $genderList;
            $this->data['title']          = 'Add Student';
            $this->data['title_list']     = 'Recently Added Student';
            $data_internal["student_categorize"]   = 'class';
            $session                      = $this->setting_model->getCurrentSession();
            $class                        = $this->class_model->getAll();
            $this->data['classlist']      = $class;
            $this->data['sch_setting']    = $this->sch_setting_detail;
            $id                           = $this->onlinestudent_model->getidbyrefno($reference_no);
            $category                     = $this->category_model->get();
            $this->data['categorylist']   = $category;
            $this->data["bloodgroup"]     = $this->blood_group;
            $houses                       = $this->student_model->gethouselist();
            $this->data['houses']         = $houses;
            $result = $this->onlinestudent_model->get($id);
            $classresult = $this->onlinestudent_model->getclassbyclasssectionid($result['class_section_id']);
            if($classresult){
                $class_section_id             = $classresult['class_id'];
                $class                        = $classresult['class'];
            }else{
                $class_section_id = "";
                $class = "";
            }
            $custom_fields                = $this->customfield_model->getByBelong('students');
            //-------------------------------------
            $this->data['class_id'] = $class_section_id;
            $this->data['class_section_id'] = $result['section_id'];
            $this->data['class_name']       = $class;
            $this->data['section_id']       = $section_id;
            $this->data['firstname']  = $result['firstname'];
            $this->data['middlename'] = $result['middlename'];
            $this->data['lastname']   = $result['lastname'];
            $this->data['gender']     = $result['gender'];
            if ($result['dob'] != null && $result['dob'] != '0000-00-00') {
                $this->data['dob'] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result['dob']));
            } else {
                $this->data['dob'] = "";
            }
            $this->data['mobileno']    = $result['mobileno'];
            $this->data['email']       = $result['email'];
            $this->data['category_id'] = $result['category_id'];
            $this->data['religion']    = $result['religion'];
            $this->data['cast']        = $result['cast'];
            $this->data['house_id']    = $result['school_house_id'];
            $this->data['blood_group'] = $result['blood_group'];
            $this->data['height']      = $result['height'];
            $this->data['weight']      = $result['weight'];
            if ($result['measurement_date'] != null && $result['measurement_date'] != '0000-00-00' && $result['measurement_date'] != '1970-01-01') {
                $this->data['measurement_date'] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($result['measurement_date']));
            } else {
                $this->data['measurement_date'] = "";
            }

            $this->data['father_name']       = $result['father_name'];
            $this->data['father_phone']      = $result['father_phone'];
            $this->data['father_occupation'] = $result['father_occupation'];
            $this->data['mother_name']       = $result['mother_name'];
            $this->data['mother_phone']      = $result['mother_phone'];
            $this->data['mother_occupation'] = $result['mother_occupation'];
            $this->data['guardian_is']         = $result['guardian_is'];
            $this->data['guardian_name']       = $result['guardian_name'];
            $this->data['guardian_relation']   = $result['guardian_relation'];
            $this->data['guardian_email']      = $result['guardian_email'];
            $this->data['guardian_phone']      = $result['guardian_phone'];
            $this->data['guardian_occupation'] = $result['guardian_occupation'];
            $this->data['guardian_address']    = $result['guardian_address'];
            $this->data['current_address']   = $result['current_address'];
            $this->data['permanent_address'] = $result['permanent_address'];
            $this->data['ifsc_code']               = $result['ifsc_code'];
            $this->data['bank_account_no']         = $result['bank_account_no'];
            $this->data['bank_name']               = $result['bank_name'];
            $this->data['adhar_no']                = $result['adhar_no'];
            $this->data['samagra_id']              = $result['samagra_id'];
            $this->data['previous_school']         = $result['previous_school'];
            $this->data['note']                    = $result['note'];
            $this->data['rte']                     = $result['rte'];
            $this->data['reference_no']            = $result['reference_no'];
            $this->data['online_admission_amount'] = $this->sch_setting_detail->online_admission_amount;
            $this->data['id']                      = $id;
            $setting_data                          = $this->setting_model->get();
            $this->data['setting_data']            = $setting_data;            
        
            if (!empty($this->input->post('admission_id'))) {
                $this->form_validation->set_rules('firstname', $this->lang->line('first_name'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');

                if ($this->customlib->getfieldstatus('if_guardian_is')) {
                    $this->form_validation->set_rules('guardian_is', $this->lang->line('guardian'), 'trim|required|xss_clean');
                    $this->form_validation->set_rules('guardian_name', $this->lang->line('guardian_name'), 'trim|required|xss_clean');
                    $this->form_validation->set_rules('guardian_relation', $this->lang->line('guardian_relation'), 'trim|required|xss_clean'); 
                }
                if ($this->customlib->getfieldstatus('student_email')) {
                    $this->form_validation->set_rules(
                        'email', $this->lang->line('email'), array(
                            'trim', 'valid_email', 'required',
                            array('check_student_email_exists', array($this->onlinestudent_model, 'check_student_email_exists')),
                        )
                    );
                }

                foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
                    if ($custom_fields_value['validation'] && $this->customlib->getfieldstatus($custom_fields_value['name'])) {
                        $custom_fields_id   = $custom_fields_value['id'];
                        $custom_fields_name = $custom_fields_value['name'];
                        $this->form_validation->set_rules("custom_fields[students][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
                    }
                }

                if (!empty($_FILES['document']['name'])) {
                    $this->form_validation->set_rules('document', $this->lang->line('documents'), 'callback_document_handle_upload[document]');
                }

                if (!empty($_FILES['father_pic']['name'])) {
                    $this->form_validation->set_rules('father_pic', $this->lang->line('father_photo'), 'callback_image_handle_upload[father_pic]');
                }
                if (!empty($_FILES['mother_pic']['name'])) {
                    $this->form_validation->set_rules('mother_pic', $this->lang->line('mother_photo'), 'callback_image_handle_upload[mother_pic]');
                }

                if (!empty($_FILES['file']['name'])) {
                    $this->form_validation->set_rules('file', $this->lang->line('student_photo'), 'callback_image_handle_upload[file]');
                }
                if (!empty($_FILES['guardian_pic']['name'])) {
                    $this->form_validation->set_rules('guardian_pic', $this->lang->line('guardian_photo'), 'callback_image_handle_upload[guardian_pic]');
                }

                if ($this->form_validation->run() == false) {
                    $this->load->view('public_admission/editonlineadmission', $this->data);
                } else {
                    $document_validate = true;
                    $custom_field_post = $this->input->post("custom_fields[students]");
                    if (isset($custom_field_post)) {
                        $custom_value_array = array();
                        foreach ($custom_field_post as $key => $value) {
                            $check_field_type = $this->input->post("custom_fields[students][" . $key . "]");
                            $field_value      = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                            $array_custom     = array(
                                'belong_table_id' => $id,
                                'custom_field_id' => $key,
                                'field_value'     => $field_value,
                            );
                            $custom_value_array[] = $array_custom;
                        }
                        $this->customfield_model->onlineadmissionupdateRecord($custom_value_array, $id, 'students');
                    }
                    if ($document_validate) {

                        $class_id   = $this->input->post('class_id');
                        $section_id = $this->input->post('section_id');

                        $data_db = array( // Renamed to avoid conflict with $data property
                            'id'               => $id,
                            'firstname'        => $this->input->post('firstname'),
                            'class_section_id' => $this->input->post('section_id'),
                            'dob'              => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob'))),
                            'gender'           => $this->input->post('gender'),
                        );

                        if ($this->customlib->getfieldstatus('if_guardian_is')) {
                            $data_db['guardian_is'] = $this->input->post('guardian_is');

                            $data_db['guardian_name']     = $this->input->post('guardian_name');
                            $data_db['guardian_relation'] = $this->input->post('guardian_relation');
                            $data_db['guardian_phone']    = $this->input->post('guardian_phone');

                            if ($this->customlib->getfieldstatus('guardian_occupation')) {
                                $data_db['guardian_occupation'] = $this->input->post('guardian_occupation');
                            }
                            if ($this->customlib->getfieldstatus('guardian_email')) {
                                $data_db['guardian_email'] = $this->input->post('guardian_email');
                            }
                            if ($this->customlib->getfieldstatus('guardian_address')) {
                                $data_db['guardian_address'] = $this->input->post('guardian_address');
                            }
                        }
                        
                        if($this->input->post('category_id')){
                            $category_id = $this->input->post('category_id');
                        }else{
                            $category_id = NULL;
                        }
                        
                        $middlename       = $this->input->post('middlename');
                        $lastname         = $this->input->post('lastname');
                        $mobileno         = $this->input->post('mobileno');
                        $email            = $this->input->post('email');
                        $category_id      = $category_id;
                        $religion         = $this->input->post('religion');
                        $cast             = $this->input->post('cast');
                        $house            = empty2null($this->input->post('house'));
                        $blood_group      = $this->input->post('blood_group');
                        $height           = $this->input->post('height');
                        $weight           = $this->input->post('weight');
                        $measurement_date = $this->input->post('measure_date');
                        $father_name       = $this->input->post('father_name');
                        $father_phone      = $this->input->post('father_phone');
                        $father_occupation = $this->input->post('father_occupation');
                        $mother_name       = $this->input->post('mother_name');
                        $mother_phone      = $this->input->post('mother_phone');
                        $mother_occupation = $this->input->post('mother_occupation');
                        $bank_account_no   = $this->input->post('bank_account_no');
                        $ifsc_code         = $this->input->post('ifsc_code');
                        $bank_name         = $this->input->post('bank_name');
                        $current_address   = $this->input->post('current_address');
                        $permanent_address = $this->input->post('permanent_address');
                        $previous_school   = $this->input->post('previous_school');
                        $note              = $this->input->post('note');
                        $rte               = $this->input->post('rte');
                        $adhar_no          = $this->input->post('adhar_no');
                        $samagra_id        = $this->input->post('samagra_id');

                        if (isset($adhar_no)) {
                            $data_db['adhar_no'] = $this->input->post('adhar_no');
                        }                        
                        if (isset($samagra_id)) {
                            $data_db['samagra_id'] = $this->input->post('samagra_id');
                        }                        
                        if (isset($middlename)) {
                            $data_db['middlename'] = $this->input->post('middlename');
                        }
                        if (isset($lastname)) {
                            $data_db['lastname'] = $this->input->post('lastname');
                        }
                        if (isset($mobile_no)) {
                            $data_db['mobileno'] = $this->input->post('mobileno');
                        }
                        if (isset($email)) {
                            $data_db['email'] = $this->input->post('email');
                        }
                        if (isset($category_id)) {
                            $data_db['category_id'] = $this->input->post('category_id');
                        }
                        if (isset($religion)) {
                            $data_db['religion'] = $this->input->post('religion');
                        }
                        if (isset($cast)) {
                            $data_db['cast'] = $this->input->post('cast');
                        } 
                        if (isset($house)) {
                            $data_db['school_house_id'] = $this->input->post('house');
                        }
                        if (isset($blood_group)) {
                            $data_db['blood_group'] = $this->input->post('blood_group');
                        }
                        if (isset($height)) {
                            $data_db['height'] = $this->input->post('height');
                        }
                        if (isset($weight)) {
                            $data_db['weight'] = $this->input->post('weight');
                        }
                        
                        if ($measurement_date) {
                            $data_db['measurement_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('measure_date')));
                        }else{
                            $data_db['measurement_date'] = NULL ;
                        }
                        
                        if (isset($father_name)) {
                            $data_db['father_name'] = $this->input->post('father_name');
                        }
                        if (isset($father_phone)) {
                            $data_db['father_phone'] = $this->input->post('father_phone');
                        }
                        if (isset($father_occupation)) {
                            $data_db['father_occupation'] = $this->input->post('father_occupation');
                        }
                        if (isset($mother_name)) {
                            $data_db['mother_name'] = $this->input->post('mother_name');
                        }
                        if (isset($mother_phone)) {
                            $data_db['mother_phone'] = $this->input->post('mother_phone');
                        }
                        if (isset($mother_occupation)) {
                            $data_db['mother_occupation'] = $this->input->post('mother_occupation');
                        }
                        if (isset($current_address)) {
                            $data_db['current_address'] = $this->input->post('current_address');
                        }
                        if (isset($permanent_address)) {
                            $data_db['permanent_address'] = $this->input->post('permanent_address');
                        }
                        if (isset($bank_account_no)) {
                            $data_db['bank_account_no'] = $this->input->post('bank_account_no');
                        }
                        if (isset($ifsc_code)) {
                            $data_db['ifsc_code'] = $this->input->post('ifsc_code');
                        }
                        if (isset($bank_name)) {
                            $data_db['bank_name'] = $this->input->post('bank_name');
                        }
                        if (isset($previous_school)) {
                            $data_db['previous_school'] = $this->input->post('previous_school');
                        }
                        if (isset($note)) {
                            $data_db['note'] = $this->input->post('note');
                        }
                        if (isset($rte)) {
                            $data_db['rte'] = $this->input->post('rte');
                        }
                                            if (isset($_FILES["document"]) && !empty($_FILES['document']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("document", "./uploads/student_documents/online_admission_doc/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('public_admission');
                                                }
                                                $img_name         = $upload_result['message'];
                                                $data_db['document'] = $img_name;
                                            }                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $upload_result = $this->media_storage->fileupload("file", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('public_admission');
                        }
                        $img_name      = $upload_result['message'];
                        $data_db['image'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }
                                            if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("father_pic", "./uploads/student_images/online_admission_image/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('public_admission');
                                                }
                                                $img_name           = $upload_result['message'];
                                                $data_db['father_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                                            }                        if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                            $img_name           = $this->media_storage->fileupload("mother_pic", "./uploads/student_images/online_admission_image/");
                            $data_db['mother_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                        }
                                            if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("guardian_pic", "./uploads/student_images/online_admission_image/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('public_admission');
                                                }
                                                $img_name             = $upload_result['message'];
                                                $data_db['guardian_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                                            }

                        $this->onlinestudent_model->edit($data_db);
                        $sch_setting = $this->sch_setting_detail;

                        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line("update_message") . '</div>');

                        redirect('public_admission/online_admission_review/' . $reference_no);
                    }
                }
            } else {
                $this->load->view('public_admission/editonlineadmission', $this->data);
            }
        } else {
            $this->show_404();
        }
    }

    public function changeCurrencyFormat()
    {
        $currency_id = $this->input->post('currency_id');
        //================
        $currency = $this->currency_model->get($currency_id);

        if ($this->session->has_userdata('student')) {
            $logged_session = $this->session->userdata('student');
            if ($logged_session['role'] == "guest") {
                $user_id = $this->customlib->getUsersID();
                $this->load->model('guest_model');
                $update_data = array('id' => $user_id, 'currency_id' => $currency_id);
                $this->guest_model->add($update_data);

            } else {

                $user_id     = $this->customlib->getUsersID();
                $update_data = array('id' => $user_id, 'currency_id' => $currency_id);
                $this->user_model->add($update_data);
            }

            $this->session->userdata['student']['currency_base_price'] = $currency->base_price;
            $this->session->userdata['student']['currency_symbol']     = $currency->symbol;
            $this->session->userdata['student']['currency']            = $currency_id;
        } else {

            $this->session->userdata['front_site']['currency_base_price'] = $currency->base_price;
            $this->session->userdata['front_site']['currency_symbol']     = $currency->symbol;
            $this->session->userdata['front_site']['currency']            = $currency_id;
        }

        echo json_encode(['status' => 1, 'message' => $this->lang->line('currency_changed_successfully')]);

    }

    public function add_college_admission()
    {
        $this->normalize_admission_year_inputs();

        $this->form_validation->set_rules('user_name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('father_name', 'Father\'s Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('father_mobile', 'Father\'s Mobile Number', 'trim|required|min_length[10]|max_length[10]|xss_clean');
        $this->form_validation->set_rules('father_occupation', 'Father\'s Occupation', 'trim|required|xss_clean');
        $this->form_validation->set_rules('mother_name', 'Mother\'s Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('mother_mobile', 'Mother\'s Mobile Number', 'trim|required|min_length[10]|max_length[10]|xss_clean');
        $this->form_validation->set_rules('mother_occupation', 'Mother\'s Occupation', 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
        $this->form_validation->set_rules('community', 'Community', 'trim|required|xss_clean');
        $this->form_validation->set_rules('student_email', 'Email ID', 'trim|required|valid_email|xss_clean|callback_check_duplicate_email');
        $this->form_validation->set_rules('student_mobile', 'Student\'s Mobile Number', 'trim|required|min_length[10]|max_length[10]|xss_clean|callback_check_duplicate_mobile');
        $this->form_validation->set_rules('dob', 'D.O.B', 'trim|required|xss_clean');
        $this->form_validation->set_rules('aadhaar', 'Aadhaar Number', 'trim|required|min_length[12]|max_length[12]|xss_clean|callback_check_duplicate_aadhaar');
        $this->form_validation->set_rules('comm_addr', 'Address for Communication', 'trim|required|xss_clean');
        $this->form_validation->set_rules('perm_addr', 'Permanent Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('state', 'State', 'trim|required|xss_clean');
        $this->form_validation->set_rules('city', 'City', 'trim|required|xss_clean');
        $this->form_validation->set_rules('state', 'State', 'trim|required|xss_clean');
        $this->form_validation->set_rules('city', 'City', 'trim|required|xss_clean');
        $this->form_validation->set_rules('user_image', 'Photo', 'callback_image_handle_upload[user_image]');

        $courseLevel = $this->input->post('courseLevel');
        $this->form_validation->set_rules('quota_type', 'Quota Type', 'trim|required|xss_clean');

        if ($courseLevel == 'ug') {
            $this->form_validation->set_rules('maths_marks', 'Maths Marks', 'trim|required|numeric|less_than_equal_to[total_maths]|xss_clean');
            $this->form_validation->set_rules('total_maths', 'Total Maths Marks', 'trim|required|numeric|less_than_equal_to[100]|xss_clean');
            $this->form_validation->set_rules('physics_marks', 'Physics Marks', 'trim|required|numeric|less_than_equal_to[total_physics]|xss_clean');
            $this->form_validation->set_rules('total_physics', 'Total Physics Marks', 'trim|required|numeric|less_than_equal_to[100]|xss_clean');
            $this->form_validation->set_rules('chemistry_marks', 'Chemistry Marks', 'trim|required|numeric|less_than_equal_to[total_chemistry]|xss_clean');
            $this->form_validation->set_rules('total_chemistry', 'Total Chemistry Marks', 'trim|required|numeric|less_than_equal_to[100]|xss_clean');
            $this->form_validation->set_rules('chemistry_perc', 'Chemistry Percentage', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('average_marks', 'Average Marks', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('cutoff_marks', 'Cut Off Marks', 'trim|numeric|xss_clean');
        }

        if ($courseLevel == 'ug') {
            $this->form_validation->set_rules('maths_marks', 'Maths Marks', 'trim|required|numeric|less_than_equal_to[total_maths]|xss_clean');
            $this->form_validation->set_rules('total_maths', 'Total Maths Marks', 'trim|required|numeric|less_than_equal_to[100]|xss_clean');
            $this->form_validation->set_rules('physics_marks', 'Physics Marks', 'trim|required|numeric|less_than_equal_to[total_physics]|xss_clean');
            $this->form_validation->set_rules('total_physics', 'Total Physics Marks', 'trim|required|numeric|less_than_equal_to[100]|xss_clean');
            $this->form_validation->set_rules('chemistry_marks', 'Chemistry Marks', 'trim|required|numeric|less_than_equal_to[total_chemistry]|xss_clean');
            $this->form_validation->set_rules('total_chemistry', 'Total Chemistry Marks', 'trim|required|numeric|less_than_equal_to[100]|xss_clean');
            $this->form_validation->set_rules('chemistry_perc', 'Chemistry Percentage', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('average_marks', 'Average Marks', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('cutoff_marks', 'Cut Off Marks', 'trim|numeric|xss_clean');
        }

        if ($courseLevel == 'ug') {
            $this->form_validation->set_rules('ug_course', 'UG Course', 'trim|required|xss_clean');
            $this->form_validation->set_rules('school_name', 'Name of the school of X std', 'trim|required|xss_clean');
            $this->form_validation->set_rules('tenth_passing', 'Year of passing of X std', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('tenth_marks_percentage', 'X marks (in %)', 'trim|required|numeric|greater_than[0]|less_than_equal_to[100]|xss_clean');
            
            // Check if selected course is B.ARCH by checking course name
            $ug_course_id = $this->input->post('ug_course');
            if (!empty($ug_course_id)) {
                $course = $this->Onlineadmissioncourses_model->getById($ug_course_id);
                if ($course && stripos($course->course_name, 'ARCH') !== false) {
                    $this->form_validation->set_rules('nata_score', 'NATA Score', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('application_number', 'NATA Application Form', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('nata_year', 'NATA Year', 'trim|required|xss_clean');
                }
            }
        } elseif ($courseLevel == 'lateral') {
            $this->form_validation->set_rules('lateral_course', 'Lateral Entry Course', 'trim|required|xss_clean');
            $this->form_validation->set_rules('lateral_school_name', 'Name of the school of X std', 'trim|required|xss_clean');
            $this->form_validation->set_rules('lateral_tenth_passing', 'Year of passing of X std', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('lateral_tenth_marks_percentage', 'X marks (in %)', 'trim|required|numeric|greater_than[0]|less_than_equal_to[100]|xss_clean');
            for ($i = 1; $i <= 6; $i++) {
                $this->form_validation->set_rules('presub' . $i, 'Pre-Final Semester Subject ' . $i, 'trim|xss_clean');
                $this->form_validation->set_rules('premark' . $i, 'Pre-Final Semester Marks ' . $i, 'trim|numeric|less_than_equal_to[preout' . $i . ']|xss_clean');
                $this->form_validation->set_rules('preout' . $i, 'Pre-Final Semester Total Marks ' . $i, 'trim|numeric|less_than_equal_to[100]|xss_clean');
                $this->form_validation->set_rules('finalsub' . $i, 'Final Semester Subject ' . $i, 'trim|xss_clean');
                $this->form_validation->set_rules('finalmark' . $i, 'Final Semester Marks ' . $i, 'trim|numeric|less_than_equal_to[finalout' . $i . ']|xss_clean');
                $this->form_validation->set_rules('finalout' . $i, 'Final Semester Total Marks ' . $i, 'trim|numeric|less_than_equal_to[100]|xss_clean');
            }
        } elseif ($courseLevel == 'pg') {
            $this->form_validation->set_rules('pg_course', 'PG Course', 'trim|required|xss_clean');
            $this->form_validation->set_rules('exam_passed', 'UG Course Passed', 'trim|required|xss_clean');
            $this->form_validation->set_rules('branch', 'Main Stream', 'trim|required|xss_clean');
            $this->form_validation->set_rules('yop', 'Year of Passing', 'trim|required|xss_clean');
            $this->form_validation->set_rules('noc', 'Name of the College', 'trim|required|xss_clean');
            $this->form_validation->set_rules('university_id', 'University', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('pg_app_num', 'TANCET / PGETA Exam Application Number', 'trim|required|xss_clean');
            $this->form_validation->set_rules('exam_year', 'TANCET / PGETA Examination Year', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('exam_score', 'TANCET / PGETA Exam Score', 'trim|required|numeric|xss_clean');
            if (!empty($_FILES['bonafide']['name'])) {
                $this->form_validation->set_rules('bonafide', 'Bonafide Certificate', 'callback_document_handle_upload[bonafide]');
            }
        }

        if ($this->form_validation->run() == false) {
            $msg = array(
                'user_name' => form_error('user_name'),
                'father_name' => form_error('father_name'),
                'father_mobile' => form_error('father_mobile'),
                'father_occupation' => form_error('father_occupation'),
                'mother_name' => form_error('mother_name'),
                'mother_mobile' => form_error('mother_mobile'),
                'mother_occupation' => form_error('mother_occupation'),
                'gender' => form_error('gender'),
                'student_email' => form_error('student_email'),
                'student_mobile' => form_error('student_mobile'),
                'dob' => form_error('dob'),
                'aadhaar' => form_error('aadhaar'),
                'comm_addr' => form_error('comm_addr'),
                'perm_addr' => form_error('perm_addr'),
                'quota_type' => form_error('quota_type'),
                'user_image' => form_error('user_image'),
                'ug_course' => form_error('ug_course'),
                'school_name' => form_error('school_name'),
                'tenth_passing' => form_error('tenth_passing'),
                'tenth_marks_percentage' => form_error('tenth_marks_percentage'),
                'maths_marks' => form_error('maths_marks'),
                'total_maths' => form_error('total_maths'),
                'physics_marks' => form_error('physics_marks'),
                'total_physics' => form_error('total_physics'),
                'nata_score' => form_error('nata_score'),
                'application_number' => form_error('application_number'),
                'nata_year' => form_error('nata_year'),
                'lateral_course' => form_error('lateral_course'),
                'lateral_school_name' => form_error('lateral_school_name'),
                'lateral_tenth_passing' => form_error('lateral_tenth_passing'),
                'lateral_tenth_marks_percentage' => form_error('lateral_tenth_marks_percentage'),
                'pg_course' => form_error('pg_course'),
                'exam_passed' => form_error('exam_passed'),
                'branch' => form_error('branch'),
                'yop' => form_error('yop'),
                'noc' => form_error('noc'),
                'nou' => form_error('nou'),
                'pg_app_num' => form_error('pg_app_num'),
                'exam_year' => form_error('exam_year'),
                'exam_score' => form_error('exam_score'),
                'bonafide' => form_error('bonafide'),
            );
            foreach ($msg as $key => $value) {
                if (empty($value)) {
                    unset($msg[$key]);
                }
            }
            $array = array('status' => 'fail', 'error' => $msg);
            echo json_encode($array);
        } else {
            $data = $this->input->post();
            if (($data['city'] ?? '') === 'Others') {
                $data['city'] = trim($data['city_custom'] ?? '');
            }
            $photo_name = '';
            if (isset($_FILES["user_image"]) && !empty($_FILES['user_image']['name'])) {
                $upload_result = $this->media_storage->fileupload("user_image", "./uploads/student_images/online_admission_image/");
                if ($upload_result['status']) {
                    $photo_name = 'uploads/student_images/online_admission_image/' . $upload_result['message'];
                }
            }

            $existing_email = $this->onlinestudent_model->get_admission_by_field('email', $data['student_email']);
            if ($existing_email) {
                $ref_no = !empty($existing_email->reference_no) ? ' (Ref No: ' . $existing_email->reference_no . ')' : '';
                $array = array('status' => 'fail', 'error' => array('student_email' => 'Email ID already exists' . $ref_no . '.'));
                echo json_encode($array);
                return;
            }

            $existing_email = $this->onlinestudent_model->get_admission_by_field('email', $data['student_email']);
            if ($existing_email) {
                $ref_no = !empty($existing_email->reference_no) ? ' (Ref No: ' . $existing_email->reference_no . ')' : '';
                $array = array('status' => 'fail', 'error' => array('student_email' => 'Email ID already exists' . $ref_no . '.'));
                echo json_encode($array);
                return;
            }

            do {
                $reference_no   = mt_rand(100000, 999999);
                $refence_status = $this->onlinestudent_model->checkreferenceno($reference_no);
            } while ($refence_status);

            $normalized_dob = $this->normalize_dob_value($data['dob'] ?? '');

            $insert_data_online_admission = array(
                'reference_no' => $reference_no,
                'firstname' => $data['user_name'],
                'mobileno' => $data['student_mobile'],
                'email' => $data['student_email'],
                'dob' => !empty($normalized_dob) ? $normalized_dob : null,
                'gender' => $data['gender'],
                'cast' => $data['community'],
                'father_name' => $data['father_name'],
                'father_phone' => $data['father_mobile'],
                'father_occupation' => $data['father_occupation'],
                'mother_name' => $data['mother_name'],
                'mother_phone' => $data['mother_mobile'],
                'mother_occupation' => $data['mother_occupation'],
                'current_address' => $data['comm_addr'],
                'permanent_address' => $data['perm_addr'],
                'state' => $data['state'],
                'city' => $data['city'],
                'adhar_no' => $data['aadhaar'],
                'image' => $photo_name,
                'form_status' => 0, // 0 for pending, 1 for submitted
                'paid_status' => 0, // 0 for unpaid, 1 for paid
            );

            if ($courseLevel == 'ug') {
                $insert_data_online_admission = array_merge(
                    $insert_data_online_admission,
                    $this->build_hsc_payload($data)
                );
                $insert_data_online_admission['ug_course_id'] = $data['ug_course'];
                $insert_data_online_admission['school_name_x'] = !empty($data['school_name']) ? $data['school_name'] : null;
                $insert_data_online_admission['passing_year_x'] = !empty($data['tenth_passing']) ? $data['tenth_passing'] : null;
                $insert_data_online_admission['tenth_marks_percentage'] = !empty($data['tenth_marks_percentage']) ? $data['tenth_marks_percentage'] : null;
            } elseif ($courseLevel == 'lateral') {
                // For lateral entry, save X std details to main table
                $insert_data_online_admission['school_name_x'] = !empty($data['lateral_school_name']) ? $data['lateral_school_name'] : null;
                $insert_data_online_admission['passing_year_x'] = !empty($data['lateral_tenth_passing']) ? $data['lateral_tenth_passing'] : null;
                $insert_data_online_admission['tenth_marks_percentage'] = !empty($data['lateral_tenth_marks_percentage']) ? $data['lateral_tenth_marks_percentage'] : null;
            }

            $course_meta = $this->get_selected_course_meta($courseLevel, $data);
            $insert_data_online_admission = array_merge($insert_data_online_admission, $course_meta);

            $online_admission_id = $this->onlinestudent_model->add($insert_data_online_admission);

            // Update enquiry status only when a valid enquiry_id is provided
            $enquiry_id = trim((string)$this->input->post('enquiry_id'));
            if ($enquiry_id !== '' && ctype_digit($enquiry_id)) {
                $this->enquiry_model->enquiry_update($enquiry_id, array('status' => 'application_done'));
            }

            // Save reference details
            if (!empty($data['referral_name'])) {
                $insert_ref_data = array(
                    'online_admission_id' => $online_admission_id,
                    'referrer_name' => $data['referral_name'],
                    'relationship' => $data['relationship'],
                    'phone_no' => $data['phone_no'],
                );
                $this->Online_admission_references_model->add($insert_ref_data);
            }

            if ($courseLevel == 'ug') {
                // HSC marks already saved to online_admissions table above
                // online_admission_ug_details table is for PG applicants' UG degree info
                // So we don't insert into online_admission_ug_details for UG applicants

                if ($this->is_barch_course($data['ug_course'] ?? null)) {
                    $insert_nata_data = array(
                        'online_admission_id' => $online_admission_id,
                        'nata_score' => $data['nata_score'],
                        'application_number' => $data['application_number'],
                        'nata_year' => $data['nata_year'],
                    );
                    $this->Online_admission_nata_details_model->add($insert_nata_data);
                }
            } elseif ($courseLevel == 'lateral') {
                $pre_sem_subjects = array();
                for ($i = 1; $i <= 6; $i++) {
                    $pre_sem_subjects[] = array(
                        'subject' => $data['presub' . $i],
                        'marks' => $data['premark' . $i],
                        'total_marks' => $data['preout' . $i],
                    );
                }
                $final_sem_subjects = array();
                for ($i = 1; $i <= 6; $i++) {
                    $final_sem_subjects[] = array(
                        'subject' => $data['finalsub' . $i],
                        'marks' => $data['finalmark' . $i],
                        'total_marks' => $data['finalout' . $i],
                    );
                }

                $insert_lateral_data = array(
                    'online_admission_id' => $online_admission_id,
                    'lateral_course_id' => $data['lateral_course'],
                    'pre_final_sem_subjects' => json_encode($pre_sem_subjects),
                    'final_sem_subjects' => json_encode($final_sem_subjects),
                );
                $this->Online_admission_lateral_details_model->add($insert_lateral_data);
            } elseif ($courseLevel == 'pg') {
                $bonafide_cert_path = '';
                if (isset($_FILES["bonafide"]) && !empty($_FILES['bonafide']['name'])) {
                    $upload_result = $this->media_storage->fileupload("bonafide", "./uploads/bonafide_certificates/");
                    if ($upload_result['status']) {
                        $bonafide_cert_path = 'uploads/bonafide_certificates/' . $upload_result['message'];
                    }
                }
                
                // Get university name from ID
                $university_name = null;
                if (!empty($data['university_id'])) {
                    $this->load->model('Online_admission_universities_model');
                    $university = $this->Online_admission_universities_model->get($data['university_id']);
                    $university_name = $university ? $university->name : null;
                }
                
                $insert_pg_data = array(
                    'online_admission_id' => $online_admission_id,
                    'pg_course_id' => $data['pg_course'],
                    'qualifying_exam' => $data['exam_passed'],
                    'branch' => $data['branch'],
                    'year_of_passing' => $data['yop'],
                    'college_name' => $data['noc'],
                    'university_id' => !empty($data['university_id']) ? $data['university_id'] : null,
                    'university_name' => $university_name,
                    'tancet_pgeta_app_no' => $data['pg_app_num'],
                    'tancet_pgeta_year' => $data['exam_year'],
                    'tancet_pgeta_score' => $data['exam_score'],
                    'is_alumni' => isset($data['alumni_check']) ? 1 : 0,
                    'bonafide_cert_path' => $bonafide_cert_path,
                    'is_sports_person' => $data['sports'] == 'Yes' ? 1 : 0,
                    'sports_level' => $data['sports'] == 'Yes' ? $data['sports_level'] : NULL,
                    'is_ex_service' => $data['exservice'] == 'Yes' ? 1 : 0,
                    'is_differently_abled' => $data['differently_abled'] == 'Yes' ? 1 : 0,
                    'disability_type' => $data['differently_abled'] == 'Yes' ? $data['disability_type'] : NULL,
                );
                $this->Online_admission_pg_details_model->add($insert_pg_data);
            }
            $sender_details = array(
                'firstname' => $data['user_name'],
                'lastname' => $data['father_name'],
                'email' => $data['student_email'],
                'date' => date('Y-m-d'),
                'reference_no' => $reference_no,
                'mobileno' => $data['student_mobile'],
                'guardian_email' => '',
                'guardian_phone' => $data['father_mobile']
            );

            $payment_option = trim((string)$this->input->post('payment_option'));

            // If payment is not required OR payment_option is 'pay_later'
            if ($this->sch_setting_detail->online_admission_payment != 'yes' || $this->sch_setting_detail->online_admission_amount <= 0 || $payment_option == 'pay_later') {
                $paid_status_value = ($payment_option == 'pay_later') ? 2 : 0; // 2 for 'offline' (Pay Later), 0 for no payment required/unpaid

                // Update online_admissions table with the chosen paid_status
                $update_data = array(
                    'id' => $online_admission_id,
                    'paid_status' => $paid_status_value,
                );
                $this->onlinestudent_model->edit($update_data); // Use the edit method to update status

                $this->mailsmsconf->mailsms('online_admission_form_submission', $sender_details);
                
                $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('thanks_for_registration_please_note_your_reference_number') . ' ' . $reference_no . ' ' . $this->lang->line('for_further_communication') . '</div>');
                redirect('publicadmissionform/pay_later_confirmation/' . $reference_no); // Redirect to Pay Later specific confirmation page

            } else { // Payment is required AND payment_option is 'pay_online'
                $total_admission_amount = $this->sch_setting_detail->online_admission_amount; // The base admission fee

                // Calculate processing charges
                $processing_charge = 0; // Default
                if (isset($this->sch_setting_detail->online_admission_processing_charge_type) && isset($this->sch_setting_detail->online_admission_processing_charge) && $this->sch_setting_detail->online_admission_processing_charge > 0) {
                    $processing_charge_config_type = $this->sch_setting_detail->online_admission_processing_charge_type;
                    $processing_charge_config_value = $this->sch_setting_detail->online_admission_processing_charge;
                    
                    if ($processing_charge_config_type == 'percentage') {
                        $processing_charge = ($total_admission_amount * $processing_charge_config_value) / 100;
                    } elseif ($processing_charge_config_type == 'fixed') {
                        $processing_charge = $processing_charge_config_value;
                    }
                }
                
                $final_amount_to_pay = $total_admission_amount + $processing_charge;

                // Prepare parameters for the intermediate payment confirmation page
                $payment_params = array(
                    'online_admission_id' => $online_admission_id,
                    'reference_no' => $reference_no,
                    'total' => $final_amount_to_pay, // Final amount including any processing fees
                    'admission_amount' => $total_admission_amount,
                    'processing_charge' => $processing_charge,
                    'name' => $data['user_name'],
                    'guardian_phone' => $data['father_mobile'],
                    'email' => $data['student_email'],
                    'item_type' => 'online_admission_fee', // Custom identifier for callback
                    'sch_setting_detail' => $this->sch_setting_detail,
                );

                // Store all payment details in session for `confirm_payment` and `initiate_gateway_payment`
                $this->session->set_userdata('online_admission_payment_params', $payment_params);
                $this->session->set_userdata('online_admission_id', $online_admission_id); // Store ID separately for clarity

                // Set paid_status to 'pending' (0) for online payment
                $update_data = array(
                    'id' => $online_admission_id,
                    'paid_status' => 0, // 0 for pending online payment
                );
                $this->onlinestudent_model->edit($update_data);

                // Redirect to the intermediate confirmation page
                redirect('publicadmissionform/confirm_payment');
            }
        }
    }

    public function check_admissions_data()
    {
        if ($this->input->post('email_id')) {
            $result = $this->onlinestudent_model->check_admissions_data_exists('email', $this->input->post('email_id'));
            echo json_encode(array('total' => ($result ? 0 : 1)));
        } elseif ($this->input->post('mobile_no')) {
            $result = $this->onlinestudent_model->check_admissions_data_exists('mobileno', $this->input->post('mobile_no'));
            echo json_encode(array('total' => ($result ? 0 : 1)));
        } elseif ($this->input->post('aadhaar_no')) {
            $result = $this->onlinestudent_model->check_admissions_data_exists('adhar_no', $this->input->post('aadhaar_no'));
            echo json_encode(array('total' => ($result ? 0 : 1)));
        } else {
            echo json_encode(array('count' => 0)); // Default or error case
        }
    }

    public function check_duplicate_email($email)
    {
        $existing = $this->onlinestudent_model->get_admission_by_field('email', $email);
        if ($existing) {
            $ref_no = !empty($existing->reference_no) ? ' (Ref No: ' . $existing->reference_no . ')' : '';
            $this->form_validation->set_message('check_duplicate_email', 'Email ID already exists' . $ref_no . '.');
            return false;
        }
        return true;
    }

    private function get_selected_course_meta($courseLevel, $data)
    {
        $course_id = null;
        if ($courseLevel == 'ug') {
            $course_id = $data['ug_course'] ?? null;
        } elseif ($courseLevel == 'lateral') {
            $course_id = $data['lateral_course'] ?? null;
        } elseif ($courseLevel == 'pg') {
            $course_id = $data['pg_course'] ?? null;
        }

        $course = null;
        if (!empty($course_id)) {
            $course = $this->Onlineadmissioncourses_model->getById($course_id);
        }

        $quota_type = $data['quota_type'] ?? null;
        $course_fee_total = null;
        if (!empty($quota_type) && !empty($course)) {
            if ($quota_type === 'government') {
                $course_fee_total = $course['govt_fee'];
            } elseif ($quota_type === 'management') {
                $course_fee_total = $course['mgt_fee'];
            }
        }

        if ($course_fee_total === null && isset($data['course_fee_total']) && $data['course_fee_total'] !== '') {
            $course_fee_total = (float)$data['course_fee_total'];
        }

        $admission_type = ($courseLevel == 'lateral') ? 'lateral' : 'first_year';
        $course_level = ($courseLevel == 'lateral') ? 'ug' : $courseLevel;

        return array(
            'admission_course_id' => $course_id,
            'course_level' => $course_level,
            'admission_type' => $admission_type,
            'quota_type' => $quota_type,
            'course_fee_total' => $course_fee_total,
        );
    }

    private function calculate_percentage($marks, $total)
    {
        if (!is_numeric($marks) || !is_numeric($total)) {
            return null;
        }

        $total = (float)$total;
        if ($total <= 0) {
            return null;
        }

        return round(((float)$marks / $total) * 100, 2);
    }

    private function build_hsc_payload($data)
    {
        $normalize = function ($value) {
            return ($value === '' || $value === null) ? null : $value;
        };

        $total_maths = $normalize($data['total_maths'] ?? null);
        $maths_marks = $normalize($data['maths_marks'] ?? null);
        $total_physics = $normalize($data['total_physics'] ?? null);
        $physics_marks = $normalize($data['physics_marks'] ?? null);
        $total_chemistry = $normalize($data['total_chemistry'] ?? null);
        $chemistry_marks = $normalize($data['chemistry_marks'] ?? null);

        $maths_perc = $this->calculate_percentage($maths_marks, $total_maths);
        $physics_perc = $this->calculate_percentage($physics_marks, $total_physics);
        $chemistry_perc = $this->calculate_percentage($chemistry_marks, $total_chemistry);

        $average_marks = null;
        $cutoff_marks = null;
        if (is_numeric($maths_perc) && is_numeric($physics_perc) && is_numeric($chemistry_perc)) {
            $average_marks = round(((float)$maths_perc + (float)$physics_perc + (float)$chemistry_perc) / 3, 2);
            $cutoff_marks = round((((float)$physics_perc + (float)$chemistry_perc) / 2) + (float)$maths_perc, 2);
        }

        return array(
            'total_maths' => $total_maths,
            'maths_marks' => $maths_marks,
            'maths_perc' => $maths_perc,
            'total_physics' => $total_physics,
            'physics_marks' => $physics_marks,
            'physics_perc' => $physics_perc,
            'total_chemistry' => $total_chemistry,
            'chemistry_marks' => $chemistry_marks,
            'chemistry_perc' => $chemistry_perc,
            'average_marks' => $average_marks,
            'cutoff_marks' => $cutoff_marks,
        );
    }

    public function check_duplicate_mobile($mobile)
    {
        $existing = $this->onlinestudent_model->get_admission_by_field('mobileno', $mobile);
        if ($existing) {
            $ref_no = !empty($existing->reference_no) ? ' (Ref No: ' . $existing->reference_no . ')' : '';
            $this->form_validation->set_message('check_duplicate_mobile', 'Mobile number already exists' . $ref_no . '.');
            return false;
        }
        return true;
    }

    public function check_duplicate_aadhaar($aadhaar)
    {
        $existing = $this->onlinestudent_model->get_admission_by_field('adhar_no', $aadhaar);
        if ($existing) {
            $ref_no = !empty($existing->reference_no) ? ' (Ref No: ' . $existing->reference_no . ')' : '';
            $this->form_validation->set_message('check_duplicate_aadhaar', 'Aadhaar number already exists' . $ref_no . '.');
            return false;
        }
        return true;
    }


    public function confirm_payment()
    {
        $payment_params = $this->session->userdata('online_admission_payment_params');
        $online_admission_id = $this->session->userdata('online_admission_id');

        // Check if session data exists
        if (empty($payment_params) || empty($online_admission_id)) {
            $this->session->set_flashdata('error', $this->lang->line('payment_details_not_found'));
            redirect('publicadmissionform'); // Redirect back to form or an error page
        }

        $data['online_admission_id'] = $online_admission_id;
        $data['total_amount_to_pay_currency'] = $this->customlib->getSchoolCurrencyWithPlace($payment_params['total']);
        $data['admission_amount'] = $this->customlib->getSchoolCurrencyWithPlace($payment_params['admission_amount']);
        $data['processing_charge'] = $this->customlib->getSchoolCurrencyWithPlace($payment_params['processing_charge']);
        $data['total_amount'] = $payment_params['total'];

        $this->load->view('public_admission/payment_confirmation', $data);
    }

    public function initiate_gateway_payment()
    {
        $online_admission_id = $this->input->post('online_admission_id'); // From hidden field in form
        $payment_params = $this->session->userdata('online_admission_payment_params');

        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: --- initiate_gateway_payment START ---');
        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: online_admission_id POST: ' . $online_admission_id);
        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: payment_params SESSION: ' . json_encode($payment_params));
        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: sch_setting_detail->online_admission_payment: ' . $this->sch_setting_detail->online_admission_payment);
        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: sch_setting_detail->online_admission_amount: ' . $this->sch_setting_detail->online_admission_amount);

        // Validate session data and POST ID match
        if (empty($payment_params) || empty($online_admission_id) || (isset($payment_params['online_admission_id']) && $payment_params['online_admission_id'] != $online_admission_id)) { // Added isset check for robustness
            log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: Failed condition: payment_params/online_admission_id mismatch.');
            $this->session->set_flashdata('error', $this->lang->line('payment_details_not_found'));
            redirect('publicadmissionform');
        }

        // Re-check payment settings (security measure against session manipulation)
        if ($this->sch_setting_detail->online_admission_payment != 'yes' || $this->sch_setting_detail->online_admission_amount <= 0) {
            log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: Failed condition: Online admission payment not enabled or amount <= 0.');
            $this->session->set_flashdata('error', $this->lang->line('online_admission_payment_not_enabled_or_amount_zero'));
            redirect('publicadmissionform');
        }
        
        // Get active payment gateway settings
        $api_config = $this->paymentsetting_model->getActiveMethod();
        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: API Config: ' . json_encode($api_config));

        if (empty($api_config) || $api_config->payment_type != 'billdesk') {
            log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: Failed condition: Billdesk gateway not configured or active. API Type: ' . (isset($api_config->payment_type) ? $api_config->payment_type : 'N/A'));
            $this->session->set_flashdata('error', $this->lang->line('billdesk_gateway_not_configured_or_active'));
            redirect('publicadmissionform');
        }

        log_message('error', 'BILLDESK_DEBUG_INIT_GATEWAY: All conditions passed. Redirecting to Billdesk. --- END ---');
        // All checks passed, proceed to insert gateway_ins and redirect
        // The $payment_params already contain all necessary data
        // For gateway_ins, we need a unique_id (online_admission_id) and other details
        $ins_data = array(
            'unique_id' => $online_admission_id, 
            'parameter_details' => json_encode($payment_params),
            'gateway_name' => 'billdesk',
            'module' => 'online_admission',
            'payment_status' => 'processing',
            'created_at' => date('Y-m-d H:i:s'),
        );
        $gateway_ins_id = $this->gateway_ins_model->add_gateway_ins($ins_data);

        // Update payment_params with gateway_ins_id for callback linkage and store in 'params' for gateway controller
        $payment_params['gateway_ins_id'] = $gateway_ins_id;
        $this->session->set_userdata('params', $payment_params);
        $this->session->set_userdata('reference', $online_admission_id); // Used by onlineadmission/billdesk/callback

        // Redirect to the onlineadmission BillDesk payment initiation page
        redirect('user/gateway/billdesk/index');
    }
    public function pay_later_confirmation($reference_no = null)
    {
        if (empty($reference_no)) {
            $this->session->set_flashdata('error', $this->lang->line('invalid_reference_number'));
            redirect('publicadmissionform');
        }
        $data['reference_no'] = $reference_no;
        $this->load->view('public_admission/pay_later_confirmation', $data);

    }

    public function ajax_add_college_admission()
    {
        $this->normalize_admission_year_inputs();

        $this->form_validation->set_rules('user_name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('father_name', 'Father\'s Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('father_mobile', 'Father\'s Mobile Number', 'trim|required|min_length[10]|max_length[10]|xss_clean');
        $this->form_validation->set_rules('father_occupation', 'Father\'s Occupation', 'trim|required|xss_clean');
        $this->form_validation->set_rules('mother_name', 'Mother\'s Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('mother_mobile', 'Mother\'s Mobile Number', 'trim|required|min_length[10]|max_length[10]|xss_clean');
        $this->form_validation->set_rules('mother_occupation', 'Mother\'s Occupation', 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
        $this->form_validation->set_rules('community', 'Community', 'trim|required|xss_clean');
        $this->form_validation->set_rules('student_email', 'Email ID', 'trim|required|valid_email|xss_clean|callback_check_duplicate_email');
        $this->form_validation->set_rules('student_mobile', 'Student\'s Mobile Number', 'trim|required|min_length[10]|max_length[10]|xss_clean|callback_check_duplicate_mobile');
        $this->form_validation->set_rules('dob', 'D.O.B', 'trim|required|xss_clean');
        $this->form_validation->set_rules('aadhaar', 'Aadhaar Number', 'trim|required|min_length[12]|max_length[12]|xss_clean|callback_check_duplicate_aadhaar');
        $this->form_validation->set_rules('comm_addr', 'Address for Communication', 'trim|required|xss_clean');
        $this->form_validation->set_rules('perm_addr', 'Permanent Address', 'trim|required|xss_clean');

        if (empty($_FILES['user_image']['name'])) {
            $this->form_validation->set_rules('user_image', 'Photo', 'required');
        } else {
            $this->form_validation->set_rules('user_image', 'Photo', 'callback_image_handle_upload[user_image]');
        }

        $courseLevel = $this->input->post('courseLevel');
        $this->form_validation->set_rules('quota_type', 'Quota Type', 'trim|required|xss_clean');

        if ($courseLevel == 'ug') {
            $this->form_validation->set_rules('ug_course', 'UG Course', 'trim|required|xss_clean');
            $this->form_validation->set_rules('school_name', 'Name of the school of X std', 'trim|required|xss_clean');
            $this->form_validation->set_rules('tenth_passing', 'Year of passing of X std', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('tenth_marks_percentage', 'X marks (in %)', 'trim|required|numeric|greater_than[0]|less_than_equal_to[100]|xss_clean');
            // Check if selected course is B.ARCH by checking course name
            $ug_course_id = $this->input->post('ug_course');
            if (!empty($ug_course_id)) {
                $course = $this->Onlineadmissioncourses_model->getById($ug_course_id);
                if ($course && stripos($course->course_name, 'ARCH') !== false) {
                    $this->form_validation->set_rules('nata_score', 'NATA Score', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('application_number', 'NATA Application Form', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('nata_year', 'NATA Year', 'trim|required|xss_clean');
                }
            }
        } elseif ($courseLevel == 'lateral') {
            $this->form_validation->set_rules('lateral_course', 'Lateral Entry Course', 'trim|required|xss_clean');
            $this->form_validation->set_rules('lateral_school_name', 'Name of the school of X std', 'trim|required|xss_clean');
            $this->form_validation->set_rules('lateral_tenth_passing', 'Year of passing of X std', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('lateral_tenth_marks_percentage', 'X marks (in %)', 'trim|required|numeric|greater_than[0]|less_than_equal_to[100]|xss_clean');
            for ($i = 1; $i <= 6; $i++) {
                $this->form_validation->set_rules('presub' . $i, 'Pre-Final Semester Subject ' . $i, 'trim|xss_clean');
                $this->form_validation->set_rules('premark' . $i, 'Pre-Final Semester Marks ' . $i, 'trim|numeric|xss_clean');
                $this->form_validation->set_rules('preout' . $i, 'Pre-Final Semester Total Marks ' . $i, 'trim|numeric|xss_clean');
                $this->form_validation->set_rules('finalsub' . $i, 'Final Semester Subject ' . $i, 'trim|xss_clean');
                $this->form_validation->set_rules('finalmark' . $i, 'Final Semester Marks ' . $i, 'trim|numeric|xss_clean');
                $this->form_validation->set_rules('finalout' . $i, 'Final Semester Total Marks ' . $i, 'trim|numeric|xss_clean');
            }
        } elseif ($courseLevel == 'pg') {
            $this->form_validation->set_rules('pg_course', 'PG Course', 'trim|required|xss_clean');
            $this->form_validation->set_rules('exam_passed', 'UG Course Passed', 'trim|required|xss_clean');
            $this->form_validation->set_rules('branch', 'Main Stream', 'trim|required|xss_clean');
            $this->form_validation->set_rules('yop', 'Year of Passing', 'trim|required|xss_clean');
            $this->form_validation->set_rules('noc', 'Name of the College', 'trim|required|xss_clean');
            $this->form_validation->set_rules('university_id', 'University', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('pg_app_num', 'TANCET / PGETA Exam Application Number', 'trim|required|xss_clean');
            $this->form_validation->set_rules('exam_year', 'TANCET / PGETA Examination Year', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('exam_score', 'TANCET / PGETA Exam Score', 'trim|required|numeric|xss_clean');
            if (!empty($_FILES['bonafide']['name'])) {
                $this->form_validation->set_rules('bonafide', 'Bonafide Certificate', 'callback_document_handle_upload[bonafide]');
            }
        }

        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            $array = array('status' => 'fail', 'error' => $errors);
            echo json_encode($array);
        } else {
            $data = $this->input->post();
            if (($data['city'] ?? '') === 'Others') {
                $data['city'] = trim($data['city_custom'] ?? '');
            }
            $photo_name = '';
            if (isset($_FILES["user_image"]) && !empty($_FILES['user_image']['name'])) {
                $upload_result = $this->media_storage->fileupload("user_image", "./uploads/student_images/online_admission_image/");
                if ($upload_result['status']) {
                    $photo_name = 'uploads/student_images/online_admission_image/' . $upload_result['message'];
                }
            }

            do {
                $reference_no   = mt_rand(100000, 999999);
                $refence_status = $this->onlinestudent_model->checkreferenceno($reference_no);
            } while ($refence_status);

            $normalized_dob = $this->normalize_dob_value($data['dob'] ?? '');

            $insert_data_online_admission = array(
                'reference_no' => $reference_no,
                'firstname' => $data['user_name'],
                'mobileno' => $data['student_mobile'],
                'email' => $data['student_email'],
                'dob' => !empty($normalized_dob) ? $normalized_dob : null,
                'gender' => $data['gender'],
                'cast' => $data['community'],
                'father_name' => $data['father_name'],
                'father_phone' => $data['father_mobile'],
                'father_occupation' => $data['father_occupation'],
                'mother_name' => $data['mother_name'],
                'mother_phone' => $data['mother_mobile'],
                'mother_occupation' => $data['mother_occupation'],
                'current_address' => $data['comm_addr'],
                'permanent_address' => $data['perm_addr'],
                'state' => $data['state'],
                'city' => $data['city'],
                'adhar_no' => $data['aadhaar'],
                'image' => $photo_name,
                'form_status' => 0, // 0 for pending, 1 for submitted
                'paid_status' => 0, // 0 for unpaid, 1 for paid
            ); // Correct closing of array

            if ($courseLevel == 'ug') {
                $insert_data_online_admission = array_merge(
                    $insert_data_online_admission,
                    $this->build_hsc_payload($data)
                );
                $insert_data_online_admission['ug_course_id'] = $data['ug_course'];
                $insert_data_online_admission['school_name_x'] = !empty($data['school_name']) ? $data['school_name'] : null;
                $insert_data_online_admission['passing_year_x'] = !empty($data['tenth_passing']) ? $data['tenth_passing'] : null;
                $insert_data_online_admission['tenth_marks_percentage'] = !empty($data['tenth_marks_percentage']) ? $data['tenth_marks_percentage'] : null;
            } elseif ($courseLevel == 'lateral') {
                // For lateral entry, save X std details to main table
                $insert_data_online_admission['school_name_x'] = !empty($data['lateral_school_name']) ? $data['lateral_school_name'] : null;
                $insert_data_online_admission['passing_year_x'] = !empty($data['lateral_tenth_passing']) ? $data['lateral_tenth_passing'] : null;
                $insert_data_online_admission['tenth_marks_percentage'] = !empty($data['lateral_tenth_marks_percentage']) ? $data['lateral_tenth_marks_percentage'] : null;
            }

            $course_meta = $this->get_selected_course_meta($courseLevel, $data);
            $insert_data_online_admission = array_merge($insert_data_online_admission, $course_meta);

            $online_admission_id = $this->onlinestudent_model->add($insert_data_online_admission);
            
            // Debug: Check if insert was successful
            if (!$online_admission_id) {
                log_message('error', 'AJAX_ADMISSION_ERROR: Insert failed! online_admission_id is: ' . var_export($online_admission_id, true));
                // Check for database errors
                if (isset($this->db) && $this->db->error()['code'] != 0) {
                    $db_error = $this->db->error();
                    log_message('error', 'AJAX_ADMISSION_DB_ERROR CODE: ' . $db_error['code']);
                    log_message('error', 'AJAX_ADMISSION_DB_ERROR MESSAGE: ' . $db_error['message']);
                    $error_array = array('database_error' => 'Database error: ' . $db_error['message']);
                    echo json_encode(['status' => 'fail', 'error' => $error_array]);
                    exit();
                } else {
                    $error_array = array('database_error' => 'Failed to insert admission record. Please try again.');
                    echo json_encode(['status' => 'fail', 'error' => $error_array]);
                    exit();
                }
            } else {
                log_message('error', 'AJAX_ADMISSION_SUCCESS: Record inserted with ID: ' . $online_admission_id . ', Reference: ' . $reference_no);
            }

            // Update enquiry status only when a valid enquiry_id is provided
            $enquiry_id = trim((string)$this->input->post('enquiry_id'));
            if ($enquiry_id !== '' && ctype_digit($enquiry_id)) {
                $this->enquiry_model->enquiry_update($enquiry_id, array('status' => 'application_done'));
            }

            if (!empty($data['referral_name'])) {
                $insert_ref_data = array(
                    'online_admission_id' => $online_admission_id,
                    'referrer_name' => $data['referral_name'],
                    'relationship' => $data['relationship'],
                    'phone_no' => $data['phone_no'],
                );
                $this->Online_admission_references_model->add($insert_ref_data);
            }

            if ($courseLevel == 'ug') {
                // HSC marks already saved to online_admissions table above
                // online_admission_ug_details is for PG applicants' UG degree info
                /*
                $this->Online_admission_ug_details_model->add([
                    'online_admission_id' => $online_admission_id,
                    'ug_course_id' => $data['ug_course'],
                    'school_name_x' => $data['school_name'],
                    'passing_year_x' => $data['tenth_passing'],
                    'maths_marks' => $data['maths_marks'], 'total_maths' => $data['total_maths'],
                    'physics_marks' => $data['physics_marks'], 'total_physics' => $data['total_physics'],
                    'chemistry_marks' => $data['chemistry_marks'], 'total_chemistry' => $data['total_chemistry'],
                    'chemistry_perc' => $data['chemistry_perc'],
                    'average_marks' => $data['average_marks'],
                    'cutoff_marks' => $data['cutoff_marks'],
                ]);
                */
                if ($this->is_barch_course($data['ug_course'] ?? null)) {
                    $this->Online_admission_nata_details_model->add([
                        'online_admission_id' => $online_admission_id,
                        'nata_score' => $data['nata_score'],
                        'application_number' => $data['application_number'],
                        'nata_year' => $data['nata_year'],
                    ]);
                }
            } elseif ($courseLevel == 'lateral') {
                $pre_sem_subjects = array();
                for ($i = 1; $i <= 6; $i++) {
                    $pre_sem_subjects[] = array(
                        'subject' => $data['presub' . $i],
                        'marks' => $data['premark' . $i],
                        'total_marks' => $data['preout' . $i],
                    );
                }
                $final_sem_subjects = array();
                for ($i = 1; $i <= 6; $i++) {
                    $final_sem_subjects[] = array(
                        'subject' => $data['finalsub' . $i],
                        'marks' => $data['finalmark' . $i],
                        'total_marks' => $data['finalout' . $i],
                    );
                }

                $insert_lateral_data = array(
                    'online_admission_id' => $online_admission_id,
                    'lateral_course_id' => $data['lateral_course'],
                    'pre_final_sem_subjects' => json_encode($pre_sem_subjects),
                    'final_sem_subjects' => json_encode($final_sem_subjects),
                );
                $this->Online_admission_lateral_details_model->add($insert_lateral_data);
            } elseif ($courseLevel == 'pg') {
                $bonafide_cert_path = '';
                if (isset($_FILES["bonafide"]) && !empty($_FILES['bonafide']['name'])) {
                    $upload_result = $this->media_storage->fileupload("bonafide", "./uploads/bonafide_certificates/");
                    if ($upload_result['status']) {
                        $bonafide_cert_path = 'uploads/bonafide_certificates/' . $upload_result['message'];
                    }
                }
                
                // Get university name from ID
                $university_name = null;
                if (!empty($data['university_id'])) {
                    $this->load->model('Online_admission_universities_model');
                    $university = $this->Online_admission_universities_model->get($data['university_id']);
                    $university_name = $university ? $university->name : null;
                }
                
                $insert_pg_data = array(
                    'online_admission_id' => $online_admission_id,
                    'pg_course_id' => $data['pg_course'],
                    'qualifying_exam' => $data['exam_passed'],
                    'branch' => $data['branch'],
                    'year_of_passing' => $data['yop'],
                    'college_name' => $data['noc'],
                    'university_id' => !empty($data['university_id']) ? $data['university_id'] : null,
                    'university_name' => $university_name,
                    'tancet_pgeta_app_no' => $data['pg_app_num'],
                    'tancet_pgeta_year' => $data['exam_year'],
                    'tancet_pgeta_score' => $data['exam_score'],
                    'is_alumni' => isset($data['alumni_check']) ? 1 : 0,
                    'bonafide_cert_path' => $bonafide_cert_path,
                    'is_sports_person' => $data['sports'] == 'Yes' ? 1 : 0,
                    'sports_level' => $data['sports'] == 'Yes' ? $data['sports_level'] : NULL,
                    'is_ex_service' => $data['exservice'] == 'Yes' ? 1 : 0,
                    'is_differently_abled' => $data['differently_abled'] == 'Yes' ? 1 : 0,
                    'disability_type' => $data['differently_abled'] == 'Yes' ? $data['disability_type'] : NULL,
                );
                $this->Online_admission_pg_details_model->add($insert_pg_data);
            }
            
            $sender_details = ['firstname' => $data['user_name'], 'lastname' => $data['father_name'], 'email' => $data['student_email'], 'date' => date('Y-m-d'), 'reference_no' => $reference_no, 'mobileno' => $data['student_mobile'], 'guardian_email' => '', 'guardian_phone' => $data['father_mobile']];
            $payment_option = $this->input->post('payment_option');

            if ($this->sch_setting_detail->online_admission_payment != 'yes' || $this->sch_setting_detail->online_admission_amount <= 0 || $payment_option == 'pay_later') {
                $this->onlinestudent_model->edit(['id' => $online_admission_id, 'paid_status' => ($payment_option == 'pay_later') ? 2 : 0]);
                $this->mailsmsconf->mailsms('online_admission_form_submission', $sender_details);
                $this->session->set_userdata('validlogin', $reference_no);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'redirect_url' => site_url('publicadmissionform/pay_later_confirmation/' . $reference_no)]);
                exit();
            } else {
                $total_admission_amount = $this->sch_setting_detail->online_admission_amount;
                $processing_charge = 0;
                if (isset($this->sch_setting_detail->online_admission_processing_charge_type) && $this->sch_setting_detail->online_admission_processing_charge > 0) {
                    if ($this->sch_setting_detail->online_admission_processing_charge_type == 'percentage') {
                        $processing_charge = ($total_admission_amount * $this->sch_setting_detail->online_admission_processing_charge) / 100;
                    } else {
                        $processing_charge = $this->sch_setting_detail->online_admission_processing_charge;
                    }
                }
                $final_amount_to_pay = $total_admission_amount + $processing_charge;
                $payment_params = ['online_admission_id' => $online_admission_id, 'reference_no' => $reference_no, 'total' => $final_amount_to_pay, 'admission_amount' => $total_admission_amount, 'processing_charge' => $processing_charge, 'name' => $data['user_name'], 'guardian_phone' => $data['father_mobile'], 'email' => $data['student_email'], 'item_type' => 'online_admission_fee', 'sch_setting_detail' => $this->sch_setting_detail];
                $this->session->set_userdata('online_admission_payment_params', $payment_params);
                $this->session->set_userdata('online_admission_id', $online_admission_id);
                $this->session->set_userdata('validlogin', $reference_no);
                $this->onlinestudent_model->edit(['id' => $online_admission_id, 'paid_status' => 0]);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'redirect_url' => site_url('publicadmissionform/confirm_payment')]);
                exit();
            }
        }
    }

    /**
     * Get universities for PG dropdown - AJAX endpoint
     */
    public function get_universities()
    {
        $this->load->model('Online_admission_universities_model');
        $universities = $this->Online_admission_universities_model->get_all_active();
        header('Content-Type: application/json');
        echo json_encode($universities);
        exit();
    }

    private function normalize_admission_year_inputs()
    {
        if (isset($_POST['dob'])) {
            $_POST['dob'] = $this->normalize_dob_value($_POST['dob']);
        }

        $year_fields = array('tenth_passing', 'lateral_tenth_passing', 'yop', 'exam_year', 'nata_year');
        foreach ($year_fields as $field) {
            if (isset($_POST[$field])) {
                $_POST[$field] = $this->normalize_year_value($_POST[$field]);
            }
        }
    }

    private function normalize_year_value($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return $value;
        }

        if (preg_match('/\b(19|20)\d{2}\b/', $value, $matches)) {
            return $matches[0];
        }

        $digits_only = preg_replace('/\D+/', '', $value);
        if (strlen($digits_only) >= 4) {
            return substr($digits_only, 0, 4);
        }

        return $digits_only;
    }

    private function normalize_dob_value($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }

        $formats = array('d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y');
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return '';
    }

    private function is_barch_course($course_id)
    {
        if (empty($course_id)) {
            return false;
        }

        $course = $this->Onlineadmissioncourses_model->getById($course_id);
        if (empty($course)) {
            return false;
        }

        $course_name = '';
        if (is_array($course) && isset($course['course_name'])) {
            $course_name = $course['course_name'];
        } elseif (is_object($course) && isset($course->course_name)) {
            $course_name = $course->course_name;
        }

        return ($course_name !== '' && stripos($course_name, 'ARCH') !== false);
    }
}
