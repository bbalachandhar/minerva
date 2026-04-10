<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends Front_Controller
{ 

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->config('form-builder');
        $this->load->config('app-config');
        $this->load->library(array('mailer', 'form_builder', 'mailsmsconf'));
        $this->load->model(array('frontcms_setting_model', 'complaint_Model', 'Visitors_model', 'onlinestudent_model', 'filetype_model', 'customfield_model', 'setting_model', 'examgroupstudent_model', 'examgroup_model', 'grade_model', 'marksdivision_model', 'currency_model', 'section_model','holiday_model', 'class_model', 'category_model', 'Onlineadmissioncourses_model'));
        $this->load->model('examstudent_model');
        $this->blood_group = $this->config->item('bloodgroup');
        $this->load->library('Ajax_pagination');
        $this->load->library('module_lib');
        $this->load->library('captchalib');
        $this->load->library('customlib');
        $this->load->helper('customfield');
        $this->load->helper('custom');

        $this->banner_content         = $this->config->item('ci_front_banner_content');
        $this->perPage                = 12;
        $ban_notice_type              = $this->config->item('ci_front_notice_content');
        $this->sch_setting_detail     = $this->setting_model->getSetting();
        $this->data['banner_notices'] = $this->cms_program_model->getByCategory($ban_notice_type, array('start' => 0, 'limit' => 5)); 
        $this->load->library(array('enc_lib', 'cart', 'auth'));
        
    }

    public function show_404()
    {
        if ($this->session->has_userdata('admin') || $this->session->has_userdata('student')) {
            $this->load->view('errors/error_message');
        } else {
            $front_setting = $this->frontcms_setting_model->get();
            if (!$front_setting) {
                $this->load->view('errors/error_message');
            } elseif ($this->front_setting->is_active_front_cms) {
                redirect('page/404-page', 'refresh');
            } else {
                $this->load->view('errors/error_message');
            }
        }
    }

    public function index()
    {
        $menu_list                = $this->cms_menu_model->getBySlug('main-menu');
        $this->data['main_menus'] = $this->cms_menuitems_model->getMenus($menu_list['id']);

        reset($this->data['main_menus']);
        $setting_data                 = $this->setting_model->get();
        $first_key                    = key($this->data['main_menus']);
        $home_page_slug               = $this->data['main_menus'][$first_key]['page_slug'];
        $setting                      = $this->frontcms_setting_model->get();
        $this->data['active_menu']    = $home_page_slug;
        $this->data['page_side_bar']  = $setting->is_active_sidebar;
        $this->data['cookie_consent'] = $setting->cookie_consent;
        $result                       = $this->cms_program_model->getByCategory($this->banner_content);
        $this->data['page']           = $this->cms_page_model->getBySlug($home_page_slug);
        if (!empty($result)) {
            $this->data['banner_images'] = $this->cms_program_model->front_cms_program_photos($result[0]['id']);
        }
        $this->data['setting_data'] = $setting_data;
        
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }
        
        $this->load_theme('home');
    }

    public function page($slug)
    {
        $page = $this->cms_page_model->getBySlug(urldecode($slug));
        if (!$page) {
            $this->data['page'] = $this->cms_page_model->getBySlug('404-page');
        } else {
            $this->data['page'] = $page;
        }

        if ($page['is_homepage']) {
            redirect('frontend');
        }
        $this->data['active_menu']       = $slug;
        $this->data['page_side_bar']     = $this->data['page']['sidebar'];
        $setting_data                    = $this->setting_model->get();
        $this->data['setting_data']      = $setting_data;
        
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }
        
        $this->data['page_content_type'] = "";
        if (!empty($this->data['page']['category_content'])) {
            $content_array = $this->data['page']['category_content'];

            reset($content_array);
            $first_key = key($content_array);
            $totalRec  = $this->cms_program_model->getByCategory($content_array[$first_key],[],1);
            if (!empty($totalRec)) {
                $totalRec = count($totalRec);
            } else {
                $totalRec = 0;
            }

            $config['target']     = '#postList';
            $config['base_url']   = base_url() . 'welcome/ajaxPaginationData';
            $config['total_rows'] = $totalRec;
            $config['per_page']   = $this->perPage;
            $config['link_func']  = 'searchFilter';
            $this->ajax_pagination->initialize($config);
            //get the posts data
            $this->data['page']['category_content'][$first_key] = $this->cms_program_model->getByCategory($content_array[$first_key], array('limit' => $this->perPage),1);
            $this->data['page_content_type'] = $content_array[$first_key];
            //load the view
        }
        $this->data['page_form'] = false;

        if (strpos($page['description'], '[form-builder:') !== false) {
            $this->data['page_form'] = true;
            $start                   = '[form-builder:';
            $end                     = ']';

            $form_name = $this->customlib->getFormString($page['description'], $start, $end);
            $form = $this->config->item($form_name);

            $this->data['form_name'] = $form_name;
            $this->data['form']      = $form;

            if (!empty($form)) {
                foreach ($form as $form_key => $form_value) {

                    if (isset($form_value['validation'])) {
                        $display_string = (preg_replace('/[^A-Za-z0-9\-]/', ' ', $form_value['id']));

                        if ($form_value['id'] == "captcha") {
                            $this->form_validation->set_rules($form_value['id'], $this->lang->line($display_string), $form_value['validation']);
                        } else {
                            $this->form_validation->set_rules($form_value['id'], $this->lang->line($display_string), $form_value['validation']);
                        }
                    }
                }

                if ($this->form_validation->run() == false) {

                } else {
                    $setting = $this->frontcms_setting_model->get();
                    $response_message = $form['email_title']['mail_response'];
                    $record           = $this->input->post();
                    if ($record['form_name'] == 'contact_us') {
                        $email     = $this->input->post('email');
                        $name      = $this->input->post('name');
                        $cont_data = array(
                            'name'    => $name,
                            'source'  => 'Online',
                            'email'   => $this->input->post('email'),
                            'purpose' => $this->input->post('subject'),
                            'date'    => date('Y-m-d'),
                            'note'    => $this->input->post('description') . " (Sent from online front site)",
                        );
                        $visitor_id = $this->Visitors_model->add($cont_data);
                    }

                    if ($record['form_name'] == 'complain') {
                        $complaint_data = array(
                            'complaint_type' => 'General',
                            'source'         => 'Online',
                            'name'           => $this->input->post('name'),
                            'email'          => $this->input->post('email'),
                            'contact'        => $this->input->post('contact_no'),
                            'date'           => date('Y-m-d'),
                            'description'    => $this->input->post('description'),
                        );
                        $complaint_id = $this->complaint_Model->add($complaint_data);
                    }

                    $email_subject = $record['email_title'];
                    $mail_body     = "";
                    unset($record['email_title']);
                    unset($record['submit']);
                    foreach ($record as $fetch_k_record => $fetch_v_record) {
                        $mail_body .= ucwords($fetch_k_record) . ": " . $fetch_v_record;
                        $mail_body .= "<br/>";
                    }
                    if (!empty($setting) && $setting->contact_us_email != "") {
                        $this->mailer->send_mail($setting->contact_us_email, $email_subject, $mail_body);
                    }

                    $this->session->set_flashdata('msg', $this->lang->line('success_message'));
                    redirect('page/' . $slug, 'refresh');
                }
            }
        }

        $this->load_theme('pages/page');
    }

    public function ajaxPaginationData()
    {
        $page              = $this->input->post('page');
        $page_content_type = $this->input->post('page_content_type');
        if (!$page) {
            $offset = 0;
        } else {
            $offset = $page;
        }
        $data['page_content_type'] = $page_content_type;
        //total rows count
        $totalRec = count($this->cms_program_model->getByCategory($page_content_type));
        //pagination configuration
        $config['target']     = '#postList';
        $config['base_url']   = base_url() . 'welcome/ajaxPaginationData';
        $config['total_rows'] = $totalRec;
        $config['per_page']   = $this->perPage;
        $config['link_func']  = 'searchFilter';
        $this->ajax_pagination->initialize($config);
        //get the posts data
        $data['category_content'] = $this->cms_program_model->getByCategory($page_content_type, array('start' => $offset, 'limit' => $this->perPage));
        $frontcmslist       = $this->frontcms_setting_model->get();
        //load the view
        $this->load->view('themes/'.$frontcmslist->theme.'/pages/ajax-pagination-data', $data, false);
    }

    public function read($slug)
    {
        $this->data['active_menu'] = 'home';
        $page                      = $this->cms_program_model->getBySlug(urldecode($slug));
        $this->data['page_side_bar']  = $page['sidebar'];
        $this->data['featured_image'] = $page['feature_image'];
        $this->data['page']           = $page;
        $setting_data                 = $this->setting_model->get();
        $this->data['setting_data']   = $setting_data;
        
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }    
        $this->load_theme('pages/read');
    }

    public function getSections()
    {
        $class_id = $this->input->post('class_id');
        $data     = $this->section_model->getClassBySectionAll($class_id);
        echo json_encode($data);
    }

    public function admission()
    {
        if ($this->module_lib->hasActive('online_admission')) {
            $this->data['active_menu'] = 'online_admission';
            $page                      = array('title' => 'Online Admission Form', 'meta_title' => 'online admission form', 'meta_keyword' => 'online admission form', 'meta_description' => 'online admission form');
            $this->data['page_side_bar']  = false;
            $this->data['featured_image'] = false;
            $this->data['page']           = $page;

            $header_footer = $this->setting_model->get_printheader();
            $this->data['header_image'] = '';
            if ($header_footer) {
                foreach($header_footer as $head_foot){
                    if($head_foot->print_type == 'general_purpose'){
                        $this->data['header_image'] = $head_foot->header_image;
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
            $data["student_categorize"]   = 'class';
            $session                      = $this->setting_model->getCurrentSession();
            $class                        = $this->class_model->getAll();
            $this->data['classlist']      = $class;
            $this->data['sch_setting']    = $this->sch_setting_detail;
            $category                     = $this->category_model->get();
            $this->data['categorylist']   = $category;
            $this->data["bloodgroup"]     = $this->blood_group;
            $houses                       = $this->student_model->gethouselist();
            $this->data['houses']         = $houses;
            $reference_no                 = "";
            $refence_status               = "";
            $sch_setting                  = $this->sch_setting_detail;
            $setting_data               = $this->setting_model->get();
            
            $this->data['setting_data'] = $setting_data;
            $this->data['online_admission_instruction']      = $sch_setting->online_admission_instruction;
            $this->data['online_admission_application_form'] = $sch_setting->online_admission_application_form;
            
            if ($this->module_lib->hasModule('online_course')) {
                $this->load->model('course_model');
                $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
            }
        
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

            //remove script from other fields
                $this->form_validation->set_rules('middlename', $this->lang->line('middlename'), 'trim|xss_clean');
                $this->form_validation->set_rules('lastname', $this->lang->line('lastname'), 'trim|xss_clean');
                $this->form_validation->set_rules('mobileno', $this->lang->line('mobileno'), 'trim|xss_clean');
                $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|xss_clean');
                $this->form_validation->set_rules('category_id', $this->lang->line('category_id'), 'trim|xss_clean');
                $this->form_validation->set_rules('religion', $this->lang->line('religion'), 'trim|xss_clean');
                $this->form_validation->set_rules('cast', $this->lang->line('cast'), 'trim|xss_clean');;
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
         
            if ($this->form_validation->run() == false) {
                 
                $this->load_theme('pages/admission', $this->config->item('front_layout'));
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

                    $data = array(
                        'firstname'        => $this->input->post('firstname'),
                        'class_section_id' => $this->input->post('section_id'),
                        'dob'              => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob'))),
                        'gender'           => $this->input->post('gender'),
                    );
                    // for inserting system fields

                    if ($this->customlib->getfieldstatus('if_guardian_is')) {
                        $data['guardian_is'] = $this->input->post('guardian_is');

                        $data['guardian_name']     = $this->input->post('guardian_name');
                        $data['guardian_relation'] = $this->input->post('guardian_relation');
                        $data['guardian_phone']    = $this->input->post('guardian_phone');

                        if ($this->customlib->getfieldstatus('guardian_occupation')) {
                            $data['guardian_occupation'] = $this->input->post('guardian_occupation');
                        }
                        if ($this->customlib->getfieldstatus('guardian_email')) {
                            $data['guardian_email'] = $this->input->post('guardian_email');
                        }
                        if ($this->customlib->getfieldstatus('guardian_address')) {
                            $data['guardian_address'] = $this->input->post('guardian_address');
                        }
                    }

                    $middlename       = $this->input->post('middlename');
                    $lastname         = $this->input->post('lastname');
                    $mobileno         = $this->input->post('mobileno');
                    $email            = $this->input->post('email');
                    $category_id      = $this->input->post('category_id');
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
                        $data['middlename'] = $this->input->post('middlename');
                    }
                    if (isset($lastname)) {
                        $data['lastname'] = $this->input->post('lastname');
                    }
                    if (isset($mobileno)) {
                        $data['mobileno'] = $this->input->post('mobileno');
                    }
                    if (isset($email)) {
                        $data['email'] = $this->input->post('email');
                    }  
                    if ($category_id) {
                        $data['category_id'] = $this->input->post('category_id');
                    }else{
                        $data['category_id'] = NULL;
                    }
                    if (isset($religion)) {
                        $data['religion'] = $this->input->post('religion');
                    }
                    if (isset($cast)) {
                        $data['cast'] = $this->input->post('cast');
                    }
                    if (isset($house)) {
                        $data['school_house_id'] = $this->input->post('house');
                    }
                    if (isset($blood_group)) {
                        $data['blood_group'] = $this->input->post('blood_group');
                    }
                    if (isset($height)) {
                        $data['height'] = $this->input->post('height');
                    }
                    if (isset($weight)) {
                        $data['weight'] = $this->input->post('weight');
                    }
                    if (isset($weight)) {
                        $data['weight'] = $this->input->post('weight');
                    }
                    if (!empty($measurement_date)) {
                        $data['measurement_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('measure_date')));
                    }
                    if (isset($father_name)) {
                        $data['father_name'] = $this->input->post('father_name');
                    }
                    if (isset($father_phone)) {
                        $data['father_phone'] = $this->input->post('father_phone');
                    }
                    if (isset($father_occupation)) {
                        $data['father_occupation'] = $this->input->post('father_occupation');
                    }
                    if (isset($mother_name)) {
                        $data['mother_name'] = $this->input->post('mother_name');
                    }
                    if (isset($mother_phone)) {
                        $data['mother_phone'] = $this->input->post('mother_phone');
                    }
                    if (isset($mother_occupation)) {
                        $data['mother_occupation'] = $this->input->post('mother_occupation');
                    }
                    if ($current_address) {
                        $data['current_address'] = $this->input->post('current_address');
                    }
                    if ($permanent_address) {
                        $data['permanent_address'] = $this->input->post('permanent_address');
                    }
                    if (isset($bank_account_no)) {
                        $data['bank_account_no'] = $this->input->post('bank_account_no');
                    }
                    if (isset($bank_name)) {
                        $data['bank_name'] = $this->input->post('bank_name');
                    }
                    if (isset($ifsc_code)) {
                        $data['ifsc_code'] = $this->input->post('ifsc_code');
                    }
                    if (isset($adhar_no)) {
                        $data['adhar_no'] = $this->input->post('adhar_no');
                    }
                    if (isset($samagra_id)) {
                        $data['samagra_id'] = $this->input->post('samagra_id');
                    }
                    if (isset($note)) {
                        $data['note'] = $this->input->post('note');
                    }
                    if (isset($previous_school)) {
                        $data['previous_school'] = $this->input->post('previous_school');
                    }
                    if (isset($rte)) {
                        $data['rte'] = $this->input->post('rte');
                    }

                    do {
                        $reference_no   = mt_rand(100000, 999999);
                        $refence_status = $this->onlinestudent_model->checkreferenceno($reference_no);
                    } while ($refence_status);

                    $data['reference_no'] = $reference_no;

                    if (isset($_FILES["document"]) && !empty($_FILES['document']['name'])) {
                        $upload_result = $this->media_storage->fileupload("document", "./uploads/student_documents/online_admission_doc/");
        if ($upload_result['status'] === false) {
            $this->session->set_flashdata('error', $upload_result['message']);
            redirect('welcome/admission');
        }
        $img_name         = $upload_result['message'];
                        
                        $data['document'] = $img_name;
                    }

                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $upload_result = $this->media_storage->fileupload("file", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('welcome/admission');
                        }
                        $img_name      = $upload_result['message'];
                        $data['image'] = 'uploads/student_images/online_admission_image/' . $img_name;
                    }

                    if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                        $upload_result = $this->media_storage->fileupload("father_pic", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('welcome/admission');
                        }
                        $img_name           = $upload_result['message'];
                        $data['father_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }

                    if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                        $upload_result = $this->media_storage->fileupload("mother_pic", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('welcome/admission');
                        }
                        $img_name           = $upload_result['message'];
                        $data['mother_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }

                                        if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {

                                            $upload_result = $this->media_storage->fileupload("guardian_pic", "./uploads/student_images/online_admission_image/");

                                            if ($upload_result['status'] === false) {

                                                $this->session->set_flashdata('error', $upload_result['message']);

                                                redirect('welcome/admission');

                                            }

                                            $img_name             = $upload_result['message'];

                                            $data['guardian_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;

                                        }
                    $data['hostel_room_id']      = null;
                    
                    
                    $insert_id = $this->onlinestudent_model->add($data);
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
                    redirect('welcome/online_admission_review/' . $reference_no);
                }
            }
        }
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
            $data["student_categorize"]     = 'class';
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
            $this->data['community']   = !empty($result['cast']) ? $result['cast'] : 'N/A';
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
            $this->data['state'] = $result['state'];
            $this->data['city'] = $result['city'];
            $this->data['bank_account_no'] = $result['bank_account_no'];
            $this->data['bank_name']       = $result['bank_name'];
            $this->data['ifsc_code']       = $result['ifsc_code'];
            $this->data['adhar_no']        = $result['adhar_no'];
            $this->data['samagra_id']      = $result['samagra_id'];
            $this->data['previous_school'] = $result['previous_school'];
            $this->data['note']            = $result['note'];
            $this->data['rte']             = $result['rte'];
            $this->data['ug_course_id'] = isset($result['ug_course_id']) ? $result['ug_course_id'] : null;
            $this->data['total_maths'] = isset($result['total_maths']) ? $result['total_maths'] : null;
            $this->data['maths_marks'] = isset($result['maths_marks']) ? $result['maths_marks'] : null;
            $this->data['maths_perc'] = isset($result['maths_perc']) ? $result['maths_perc'] : null;
            $this->data['total_physics'] = isset($result['total_physics']) ? $result['total_physics'] : null;
            $this->data['physics_marks'] = isset($result['physics_marks']) ? $result['physics_marks'] : null;
            $this->data['physics_perc'] = isset($result['physics_perc']) ? $result['physics_perc'] : null;
            $this->data['total_chemistry'] = isset($result['total_chemistry']) ? $result['total_chemistry'] : null;
            $this->data['chemistry_marks'] = isset($result['chemistry_marks']) ? $result['chemistry_marks'] : null;
            $this->data['chemistry_perc'] = isset($result['chemistry_perc']) ? $result['chemistry_perc'] : null;
            $this->data['average_marks'] = isset($result['average_marks']) ? $result['average_marks'] : null;
            $this->data['cutoff_marks'] = isset($result['cutoff_marks']) ? $result['cutoff_marks'] : null;
            $this->data['admission_course_id'] = isset($result['admission_course_id']) ? $result['admission_course_id'] : null;
            $this->data['stored_course_level'] = isset($result['course_level']) ? $result['course_level'] : null;
            $this->data['admission_type'] = isset($result['admission_type']) ? $result['admission_type'] : null;
            $this->data['quota_type'] = isset($result['quota_type']) ? $result['quota_type'] : null;
            $this->data['course_fee_total'] = isset($result['course_fee_total']) ? $result['course_fee_total'] : null;
            $this->data['school_name_x'] = isset($result['school_name_x']) ? $result['school_name_x'] : null;
            $this->data['passing_year_x'] = isset($result['passing_year_x']) ? $result['passing_year_x'] : null;
            $this->data['tenth_marks_percentage'] = isset($result['tenth_marks_percentage']) ? $result['tenth_marks_percentage'] : null;
            $this->data['reference_no']    = $result['reference_no'];
            $this->data['transaction_id']  = $this->customlib->gettransactionid($result['id']);
            $this->data['transaction_paid_amount']  = $this->customlib->gettransactionpaidamount($result['id']);
            $this->data['form_status']  = $result['form_status'];
            $this->data['paid_status']  = $result['paid_status'];
            $this->data['academic_year'] = $this->setting_model->getCurrentSessionName();
            $this->data['admission_id'] = $id;
            $this->data['reference_no'] = $result['reference_no'];
            $this->data['id']           = $id;
            $this->data['online_admission_payment'] = $this->sch_setting_detail->online_admission_payment;
            $this->data['online_admission_amount']  = $this->sch_setting_detail->online_admission_amount;
            $this->data['online_admission_conditions'] = $this->sch_setting_detail->online_admission_conditions;
            $current_year = date('Y');
            $this->data['applicant_login_url'] = site_url('site/userlogin');
            $this->data['applicant_username'] = $result['reference_no'];
            $this->data['applicant_password'] = $result['reference_no'] . '@ApplicantPortal' . $current_year;
            $setting_data                              = $this->setting_model->get();
            $this->data['setting_data'] = $setting_data;
            $this->data['currencies'] = $currencies;

            // Fetch general_purpose header image
            $header_footer = $this->setting_model->get_printheader();
            $this->data['general_purpose_header_image'] = '';
            if ($header_footer) {
                foreach($header_footer as $head_foot){
                    if($head_foot['print_type'] == 'general_purpose'){
                        $this->data['general_purpose_header_image'] = $head_foot['header_image'];
                        break;
                    }
                }
            }
            
            $this->load->model('Online_admission_ug_details_model');
            $this->load->model('Online_admission_pg_details_model');
            $this->load->model('Online_admission_lateral_details_model');
            $this->load->model('Online_admission_references_model');
            $this->load->model('Online_admission_nata_details_model');

            $this->data['ug_details'] = $this->Online_admission_ug_details_model->get_by_online_admission_id($id);
            $this->data['pg_details'] = $this->Online_admission_pg_details_model->get_by_online_admission_id($id);
            $this->data['lateral_details'] = $this->Online_admission_lateral_details_model->get_by_online_admission_id($id);
            $this->data['nata_details'] = $this->Online_admission_nata_details_model->get_by_online_admission_id($id);
            $this->data['reference_details'] = $this->Online_admission_references_model->get_by_online_admission_id($id);

            $all_courses = $this->Onlineadmissioncourses_model->get();
            $course_name_map = array();
            foreach ($all_courses as $course_row) {
                $course_name_map[$course_row['id']] = $course_row['course_name'];
            }
            $this->data['course_names'] = $course_name_map;

            if (!empty($this->data['pg_details']['pg_course_id']) && isset($course_name_map[$this->data['pg_details']['pg_course_id']])) {
                $this->data['pg_details']['pg_course_id'] = $course_name_map[$this->data['pg_details']['pg_course_id']];
            }

            if (!empty($this->data['lateral_details']['lateral_course_id']) && isset($course_name_map[$this->data['lateral_details']['lateral_course_id']])) {
                $this->data['lateral_details']['lateral_course_id'] = $course_name_map[$this->data['lateral_details']['lateral_course_id']];
            }

            if (!empty($this->data['stored_course_level'])) {
                $this->data['course_level'] = ($this->data['stored_course_level'] === 'ug' && $this->data['admission_type'] === 'lateral') ? 'lateral' : $this->data['stored_course_level'];
            } elseif($this->data['ug_details'] || !empty($this->data['ug_course_id'])){
                $this->data['course_level'] = 'ug';
            } elseif($this->data['pg_details']){
                $this->data['course_level'] = 'pg';
            } elseif($this->data['lateral_details']){
                $this->data['course_level'] = 'lateral';
            } elseif (isset($result['total_maths']) && $result['total_maths'] !== null || isset($result['total_physics']) && $result['total_physics'] !== null || isset($result['total_chemistry']) && $result['total_chemistry'] !== null) {
                // If there are HSC marks but no explicit course details, assume UG
                $this->data['course_level'] = 'ug';
            } else {
                $this->data['course_level'] = '';
            }

            if (!empty($this->data['ug_details'])) {
                $ug_details = $this->data['ug_details'];
                $use_ug_value = function ($current, $key) use ($ug_details) {
                    if (!isset($ug_details[$key])) {
                        return $current;
                    }
                    $ug_value = $ug_details[$key];
                    $has_ug_value = ($ug_value !== null && $ug_value !== '' && $ug_value !== 0 && $ug_value !== '0' && $ug_value !== '0.00');
                    $is_missing = ($current === null || $current === '' || $current === 0 || $current === '0' || $current === '0.00');
                    return ($has_ug_value && $is_missing) ? $ug_value : $current;
                };

                $this->data['total_maths'] = $use_ug_value($this->data['total_maths'], 'total_maths');
                $this->data['maths_marks'] = $use_ug_value($this->data['maths_marks'], 'maths_marks');
                $this->data['maths_perc'] = $use_ug_value($this->data['maths_perc'], 'maths_perc');
                $this->data['total_physics'] = $use_ug_value($this->data['total_physics'], 'total_physics');
                $this->data['physics_marks'] = $use_ug_value($this->data['physics_marks'], 'physics_marks');
                $this->data['physics_perc'] = $use_ug_value($this->data['physics_perc'], 'physics_perc');
                $this->data['total_chemistry'] = $use_ug_value($this->data['total_chemistry'], 'total_chemistry');
                $this->data['chemistry_marks'] = $use_ug_value($this->data['chemistry_marks'], 'chemistry_marks');
                $this->data['chemistry_perc'] = $use_ug_value($this->data['chemistry_perc'], 'chemistry_perc');
                $this->data['average_marks'] = $use_ug_value($this->data['average_marks'], 'average_marks');
                $this->data['cutoff_marks'] = $use_ug_value($this->data['cutoff_marks'], 'cutoff_marks');
            }

            if (empty($this->data['maths_perc']) && is_numeric($this->data['maths_marks']) && is_numeric($this->data['total_maths']) && (float)$this->data['total_maths'] > 0) {
                $this->data['maths_perc'] = number_format(((float)$this->data['maths_marks'] * 100) / (float)$this->data['total_maths'], 2, '.', '');
            }
            if (empty($this->data['physics_perc']) && is_numeric($this->data['physics_marks']) && is_numeric($this->data['total_physics']) && (float)$this->data['total_physics'] > 0) {
                $this->data['physics_perc'] = number_format(((float)$this->data['physics_marks'] * 100) / (float)$this->data['total_physics'], 2, '.', '');
            }
            if (empty($this->data['chemistry_perc']) && is_numeric($this->data['chemistry_marks']) && is_numeric($this->data['total_chemistry']) && (float)$this->data['total_chemistry'] > 0) {
                $this->data['chemistry_perc'] = number_format(((float)$this->data['chemistry_marks'] * 100) / (float)$this->data['total_chemistry'], 2, '.', '');
            }
            if (empty($this->data['average_marks']) && is_numeric($this->data['maths_marks']) && is_numeric($this->data['physics_marks']) && is_numeric($this->data['chemistry_marks'])) {
                $this->data['average_marks'] = number_format((((float)$this->data['maths_marks'] + (float)$this->data['physics_marks'] + (float)$this->data['chemistry_marks']) / 3), 2, '.', '');
            }
            if (empty($this->data['cutoff_marks']) && is_numeric($this->data['maths_marks']) && is_numeric($this->data['physics_marks']) && is_numeric($this->data['chemistry_marks'])) {
                $this->data['cutoff_marks'] = number_format(((((float)$this->data['physics_marks'] + (float)$this->data['chemistry_marks']) / 2) + (float)$this->data['maths_marks']), 2, '.', '');
            }

            if ($this->data['course_level'] === '' && !empty($this->data['ug_course_id'])) {
                $this->data['course_level'] = 'ug';
            }

            if ($this->data['course_level'] === '' && empty($this->data['pg_details']) && empty($this->data['lateral_details'])) {
                $has_hsc_value = function ($value) {
                    return ($value !== null && $value !== '' && $value !== 0 && $value !== '0' && $value !== '0.00');
                };

                if (
                    $has_hsc_value($this->data['total_maths']) ||
                    $has_hsc_value($this->data['maths_marks']) ||
                    $has_hsc_value($this->data['total_physics']) ||
                    $has_hsc_value($this->data['physics_marks']) ||
                    $has_hsc_value($this->data['total_chemistry']) ||
                    $has_hsc_value($this->data['chemistry_marks'])
                ) {
                    $this->data['course_level'] = 'ug';
                }
            }
            
            if ($this->module_lib->hasModule('online_course')) {
                $this->load->model('course_model');
                $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
            }
        
        if ($this->sch_setting_detail->institution_type == 'school') {
            $this->load_theme('pages/online_admission_review', $this->config->item('front_layout'));
        } elseif ($status == 'admin') {
            // Admin viewing: college_admission_review has its own standalone HTML shell
            $this->load->view('public_admission/college_admission_review', $this->data);
        } else {
            // Applicant viewing their own form: wrap with applicant portal layout
            $this->data['wrapped_layout'] = 'applicant';
            $this->data['sch_name'] = $this->sch_setting_detail->name ?? 'Applicant Portal';
            $applicant_obj = new stdClass();
            $applicant_obj->firstname   = $result['firstname'];
            $applicant_obj->lastname    = $result['lastname'];
            $applicant_obj->reference_no = $result['reference_no'];
            $this->data['applicant_info'] = $applicant_obj;
            $this->load->view('layout/applicant/header', $this->data);
            $this->load->view('public_admission/college_admission_review', $this->data);
            $this->load->view('layout/applicant/footer', $this->data);
        }
 
        } else {
            $this->show_404();
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
            $data["student_categorize"]   = 'class';
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
            $data["student_categorize"]   = 'class';
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
            $result                       = $this->onlinestudent_model->get($id);
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
                    $this->load_theme('pages/editadmission', $this->config->item('front_layout'));
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

                        $data = array(
                            'id'               => $id,
                            'firstname'        => $this->input->post('firstname'),
                            'class_section_id' => $this->input->post('section_id'),
                            'dob'              => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob'))),
                            'gender'           => $this->input->post('gender'),
                        );

                        if ($this->customlib->getfieldstatus('if_guardian_is')) {
                            $data['guardian_is'] = $this->input->post('guardian_is');

                            $data['guardian_name']     = $this->input->post('guardian_name');
                            $data['guardian_relation'] = $this->input->post('guardian_relation');
                            $data['guardian_phone']    = $this->input->post('guardian_phone');

                            if ($this->customlib->getfieldstatus('guardian_occupation')) {
                                $data['guardian_occupation'] = $this->input->post('guardian_occupation');
                            }
                            if ($this->customlib->getfieldstatus('guardian_email')) {
                                $data['guardian_email'] = $this->input->post('guardian_email');
                            }
                            if ($this->customlib->getfieldstatus('guardian_address')) {
                                $data['guardian_address'] = $this->input->post('guardian_address');
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
                            $data['adhar_no'] = $this->input->post('adhar_no');
                        }                        
                        if (isset($samagra_id)) {
                            $data['samagra_id'] = $this->input->post('samagra_id');
                        }                        
                        if (isset($middlename)) {
                            $data['middlename'] = $this->input->post('middlename');
                        }
                        if (isset($lastname)) {
                            $data['lastname'] = $this->input->post('lastname');
                        }
                        if (isset($mobile_no)) {
                            $data['mobileno'] = $this->input->post('mobileno');
                        }
                        if (isset($email)) {
                            $data['email'] = $this->input->post('email');
                        }
                        if (isset($category_id)) {
                            $data['category_id'] = $this->input->post('category_id');
                        }
                        if (isset($religion)) {
                            $data['religion'] = $this->input->post('religion');
                        }
                        if (isset($cast)) {
                            $data['cast'] = $this->input->post('cast');
                        } 
                        if (isset($house)) {
                            $data['school_house_id'] = $this->input->post('house');
                        }
                        if (isset($blood_group)) {
                            $data['blood_group'] = $this->input->post('blood_group');
                        }
                        if (isset($height)) {
                            $data['height'] = $this->input->post('height');
                        }
                        if (isset($weight)) {
                            $data['weight'] = $this->input->post('weight');
                        }
                        
                        if ($measurement_date) {
                            $data['measurement_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('measure_date')));
                        }else{
                            $data['measurement_date'] = NULL ;
                        }
                        
                        if (isset($father_name)) {
                            $data['father_name'] = $this->input->post('father_name');
                        }
                        if (isset($father_phone)) {
                            $data['father_phone'] = $this->input->post('father_phone');
                        }
                        if (isset($father_occupation)) {
                            $data['father_occupation'] = $this->input->post('father_occupation');
                        }
                        if (isset($mother_name)) {
                            $data['mother_name'] = $this->input->post('mother_name');
                        }
                        if (isset($mother_phone)) {
                            $data['mother_phone'] = $this->input->post('mother_phone');
                        }
                        if (isset($mother_occupation)) {
                            $data['mother_occupation'] = $this->input->post('mother_occupation');
                        }
                        if (isset($current_address)) {
                            $data['current_address'] = $this->input->post('current_address');
                        }
                        if (isset($permanent_address)) {
                            $data['permanent_address'] = $this->input->post('permanent_address');
                        }
                        if (isset($bank_account_no)) {
                            $data['bank_account_no'] = $this->input->post('bank_account_no');
                        }
                        if (isset($ifsc_code)) {
                            $data['ifsc_code'] = $this->input->post('ifsc_code');
                        }
                        if (isset($bank_name)) {
                            $data['bank_name'] = $this->input->post('bank_name');
                        }
                        if (isset($previous_school)) {
                            $data['previous_school'] = $this->input->post('previous_school');
                        }
                        if (isset($note)) {
                            $data['note'] = $this->input->post('note');
                        }
                        if (isset($rte)) {
                            $data['rte'] = $this->input->post('rte');
                        }
                                            if (isset($_FILES["document"]) && !empty($_FILES['document']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("document", "./uploads/student_documents/online_admission_doc/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('welcome/admission');
                                                }
                                                $img_name         = $upload_result['message'];
                                                $data['document'] = $img_name;
                                            }                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $upload_result = $this->media_storage->fileupload("file", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('welcome/admission');
                        }
                        $img_name      = $upload_result['message'];
                        $data['image'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }
                                            if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("father_pic", "./uploads/student_images/online_admission_image/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('welcome/admission');
                                                }
                                                $img_name           = $upload_result['message'];
                                                $data['father_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                                            }                        if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                            $img_name           = $this->media_storage->fileupload("mother_pic", "./uploads/student_images/online_admission_image/");
                            $data['mother_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                        }
                                            if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("guardian_pic", "./uploads/student_images/online_admission_image/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('welcome/admission');
                                                }
                                                $img_name             = $upload_result['message'];
                                                $data['guardian_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                                            }

                        $this->onlinestudent_model->edit($data);
                        $sch_setting = $this->sch_setting_detail;

                        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line("update_message") . '</div>');

                        redirect('welcome/online_admission_review/' . $reference_no);
                    }
                }
            } else {
                $this->load_theme('pages/editadmission', $this->config->item('front_layout'));
            }
        } else {
            $this->show_404();
        }
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

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('image_handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('image_handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }

            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('image_handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
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

    public function check_captcha($captcha)
    {
        if ($captcha != $this->session->userdata('captchaCode')):
            $this->form_validation->set_message('check_captcha', $this->lang->line('incorrect_captcha'));
            return false;
        else:
            return true;
        endif;
    }

    public function setsitecookies()
    {
        $cookie_name  = "sitecookies";
        $cookie_value = "1";
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
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

            $current_year = date('Y');
            $login_url = site_url('site/userlogin');
            $applicant_username = $reference_no;
            $applicant_password_plain = $reference_no . '@ApplicantPortal' . $current_year;

            if ($this->db->field_exists('applicant_password', 'online_admissions')) {
                $this->db->where('id', $admission_id);
                $this->db->update('online_admissions', array('applicant_password' => md5($applicant_password_plain)));
            }

            $sender_details = array(
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'date' => $date,
                'reference_no' => $reference_no,
                'mobileno' => $mobileno,
                'guardian_email' => $result['guardian_email'],
                'guardian_phone' => $result['guardian_phone'],
                'applicant_username' => $applicant_username,
                'applicant_password' => $applicant_password_plain,
                'login_url' => $login_url,
            );

            $this->mailsmsconf->mailsms('online_admission_form_submission', $sender_details);

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $subject = 'Applicant Portal Login Credentials';
                $message = '<p>Dear ' . htmlspecialchars(trim($firstname . ' ' . $lastname)) . ',</p>'
                    . '<p>Your application has been submitted successfully.</p>'
                    . '<p><strong>Reference No:</strong> ' . htmlspecialchars($reference_no) . '<br>'
                    . '<strong>Username:</strong> ' . htmlspecialchars($applicant_username) . '<br>'
                    . '<strong>Password:</strong> ' . htmlspecialchars($applicant_password_plain) . '<br>'
                    . '<strong>Login URL:</strong> <a href="' . $login_url . '">' . $login_url . '</a></p>'
                    . '<p>Please keep these credentials secure.</p>';
                $this->mailer->send_mail($email, $subject, $message);
            }

            $array = array(
                'status' => '1',
                'error' => '',
                'id' => $admission_id,
                'msg' => '',
                'reference_no' => $reference_no,
                'username' => $applicant_username,
                'password' => $applicant_password_plain,
                'login_url' => $login_url,
            );

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

    public function examresult()
    {
        $this->data['active_menu']    = 'examresult';
        $this->data['is_exam_result'] = $this->setting_model->getexamresultstatus();
        $page                         = array('title' => 'Student Exam Result', 'meta_title' => 'student exam result', 'meta_keyword' => 'student exam result', 'meta_description' => 'student exam result');
        $setting_data                 = $this->setting_model->get();
        $this->data['setting_data']   = $setting_data;
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }
        
        $marks_division               = $this->marksdivision_model->get();
        $this->data['marks_division'] = $marks_division;

        $this->data['page_side_bar']  = false;
        $this->data['featured_image'] = false;
        $this->data['page']           = $page;
        $this->data['exam_id']        = "";
        $this->data['exam_result']    = array();
        ///============

        $this->form_validation->set_rules('admission_no', $this->lang->line('admission_no'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load_theme('pages/examresult', $this->config->item('front_layout'));
        } else {
            $admission_no                  = $this->input->post('admission_no');
            $this->data['student_details'] = $this->examstudent_model->getstudentexam($admission_no);
            $exam_id                       = $_REQUEST['exam_id'];
            $student_session_id            = $this->examstudent_model->getstudentsessionidbyadmissionno($admission_no);

            $data['exam_grade']        = $this->grade_model->getGradeDetails();
            $this->data['exam_id']     = $exam_id;
            $this->data['exam_result'] = $this->examgroupstudent_model->getexamresult($student_session_id, $exam_id, true, true);
            $this->data['exam_grade']  = $this->grade_model->getGradeDetails();

            if (empty($this->data['exam_result'])) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line("no_record_found") . '</div>');
            }

            $this->load_theme('pages/examresult', $this->config->item('front_layout'));
        }
    }

    public function getstudentexam()
    {
        $admission_no = $_REQUEST['admission_no'];
        $result       = $this->examstudent_model->getstudentexam($admission_no);
        echo json_encode($result);
    }
    
    public function download($id)
    {
        $settinglist = $this->setting_model->get($id);         
        $this->media_storage->filedownload($settinglist['online_admission_application_form'], "./uploads/admission_form");
    }
    
    public function loadCaptcha()
    {
        $data['login_type'] = $_POST['login_type'];

        $data['is_captcha']    = $this->captchalib->is_captcha('guest_login_signup');
        $captcha               = $this->captchalib->generate_captcha();
        $data['captcha_image'] = isset($captcha['image']) ? $captcha['image'] : "";

        $this->load->view('themes/_loadcaptcha', $data, false);
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
    
    public function cbseexam()
    {
        $this->load->model(array('cbseexam/cbseexam_exam_model',"cbseexam/cbseexam_assessment_model"));
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('roll_no', $this->lang->line('admission_no'), 'trim|required|xss_clean');         
        $page = array('title' => 'CBSE Examination', 'meta_title' => 'CBSE Examination', 'meta_keyword' => 'CBSE Examination', 'meta_description' => 'CBSE Examination');
        $this->data['exams'] = $this->cbseexam_exam_model->getPublishexams();
        $this->data['page_side_bar']  = false;
        $this->data['featured_image'] = false;
        $this->data['active_menu'] = 'cbseexam';       
        $this->data['page']           = $page;      
        $setting_data                 = $this->setting_model->get();
        $this->data['setting_data']     = $setting_data;
        if ($this->module_lib->hasModule('online_course')) {
            $this->load->model('course_model');
            $this->data['course_setting'] = $this->course_model->getOnlineCourseSettings();
        }
        if ($this->form_validation->run() == true) {
            $exam_id          = $this->input->post('exam_id');
            $roll_no          = $this->input->post('roll_no');               
            $subjects         = $this->cbseexam_exam_model->getexamsubjects($exam_id);
            $exam             = $this->cbseexam_exam_model->getExamWithGrade($exam_id);
             $exam_assessments = $this->cbseexam_assessment_model->getWithAssessmentTypeByAssessmentID($exam->cbse_exam_assessment_id);    
            $cbse_exam_result = $this->cbseexam_exam_model->getStudentExamResultByExamIdAndAdmissionNo($exam_id,$roll_no);

            $subject_assessments = $this->cbseexam_assessment_model->getSubjectAssessmentsByExam($subjects);
            
            $this->data['exam']             = $exam;
            $this->data['subjects']         = $subjects;
            $this->data['subject_assessments'] = $subject_assessments;
            $this->data['exam_assessments'] = $exam_assessments;
    
            $student_result = [];
            $student_session_id=0;
            if (!empty($cbse_exam_result)) {
    
                foreach ($cbse_exam_result as $student_key => $student_value) {
                    $student_session_id=$student_value->student_session_id;
                    $exam_assessments[$student_value->cbse_exam_assessment_type_id] = $student_value->cbse_exam_assessment_type_id;
    
                    if (!empty($student_result)) {
    
                        if (!array_key_exists($student_value->subject_id, $student_result['subjects'])) {
    
                            $new_subject = [
                                'subject_id'       => $student_value->subject_id,
                                'subject_name'     => $student_value->subject_name,
                                'subject_code'     => $student_value->subject_code,
                                'exam_assessments' => [
                                    $student_value->cbse_exam_assessment_type_id => [
                                        'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                        'cbse_exam_assessment_type_id'   => $student_value->cbse_exam_assessment_type_id,
                                        'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                        'maximum_marks'                  => $student_value->maximum_marks,
                                        'cbse_student_subject_marks_id'  => $student_value->cbse_student_subject_marks_id,
                                        'marks'                          => $student_value->marks,
                                        'note'                           => $student_value->note,
                                        'is_absent'                      => $student_value->is_absent,
                                    ],
                                ],
                            ];
    
                            $student_result['subjects'][$student_value->subject_id] = $new_subject;
    
                        } elseif (!array_key_exists($student_value->cbse_exam_assessment_type_id, $student_result['subjects'][$student_value->subject_id]['exam_assessments'])) {
    
                            $new_assesment = [
                                'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                'cbse_exam_assessment_type_id'   => $student_value->cbse_exam_assessment_type_id,
                                'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                'maximum_marks'                  => $student_value->maximum_marks,
                                'cbse_student_subject_marks_id'  => $student_value->cbse_student_subject_marks_id,
                                'marks'                          => $student_value->marks,
                                'note'                           => $student_value->note,
                                'is_absent'                      => $student_value->is_absent,
                            ];
    
                            $student_result['subjects'][$student_value->subject_id]['exam_assessments'][$student_value->cbse_exam_assessment_type_id] = $new_assesment;
    
                        }
    
                    } else {
    
                        $student_result = [
                            'student_id'         => $student_value->student_id,
                            'student_session_id' => $student_value->student_session_id,
                            'firstname'          => $student_value->firstname,
                            'middlename'         => $student_value->middlename,
                            'lastname'           => $student_value->lastname,
                            'mobileno'           => $student_value->mobileno,
                            'email'              => $student_value->email,
                            'religion'           => $student_value->religion,
                            'guardian_name'      => $student_value->guardian_name,
                            'guardian_phone'     => $student_value->guardian_phone,
                            'dob'                => $student_value->dob,
                            'remark'             => $student_value->remark,
                            'admission_no'       => $student_value->admission_no,
                            'father_name'        => $student_value->father_name,
                            'mother_name'        => $student_value->mother_name,
                            'class_id'           => $student_value->class_id,
                            'class'              => $student_value->class,
                            'section_id'         => $student_value->section_id,
                            'section'            => $student_value->section,
                            'roll_no'            => $student_value->roll_no,
                            'student_image'      => $student_value->image,
                            'gender'             => $student_value->gender,
                            'total_present_days' => $student_value->total_present_days,
                            'total_working_days' => $student_value->total_working_days,
                            'rank' => $student_value->rank,
                            'subjects'           => [
                                $student_value->subject_id => [
                                    'subject_id'       => $student_value->subject_id,
                                    'subject_name'     => $student_value->subject_name,
                                    'subject_code'     => $student_value->subject_code,
                                    'exam_assessments' => [
                                        $student_value->cbse_exam_assessment_type_id => [
                                            'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                            'cbse_exam_assessment_type_id'   => $student_value->cbse_exam_assessment_type_id,
                                            'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                            'maximum_marks'                  => $student_value->maximum_marks,
                                            'cbse_student_subject_marks_id'  => $student_value->cbse_student_subject_marks_id,
                                            'marks'                          => $student_value->marks,
                                            'note'                           => $student_value->note,
                                            'is_absent'                      => $student_value->is_absent,
    
                                        ],
    
                                    ],
                                ],
    
                            ],
    
                        ];
    
                    }
                }
                $this->data['student']=$this->student_model->getByStudentSession($student_session_id);
            }
   
            $this->data['student_result'] = $student_result;
        }       
        $this->load_theme('pages/cbseexam', $this->config->item('front_layout'));
    }

    public function annual_calendar(){
        $holiday_arr    =   [];
        $holiday_type   = $this->holiday_model->get_holiday_type();
        foreach($holiday_type as $key=>$value){
            $holidaylist   =   $this->holiday_model->get(null,$value['id'],1); 
            $holiday_arr[$value['type']]=$holidaylist;
        }
        
        $this->data['holiday_arr']   = $holiday_arr;
		$setting_data                = $this->setting_model->get();
        $this->data['setting_data']  = $setting_data;		
        $setting                     = $this->frontcms_setting_model->get();
       
        $this->data['all_holidays']  = $this->holiday_model->get();
        $this->data['page_side_bar'] = $setting->is_active_sidebar;
        $this->data['active_menu']   = $this->lang->line("annual_calendar");
        $this->data['page']          = array('title' => $this->lang->line("annual_calendar"), 'meta_title' => '', 'meta_keyword' => '', 'meta_description' => '');      
        $this->load_theme('pages/annual_calendar', $this->config->item('front_layout'));
    }

}