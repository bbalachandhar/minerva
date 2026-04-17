<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Public_admission extends Front_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->helper('language');
        $this->load->database();
        $this->load->model('language_model');
        $this->load->model('setting_model');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model(array('frontcms_setting_model', 'complaint_Model', 'Visitors_model', 'onlinestudent_model', 'filetype_model', 'customfield_model', 'examgroupstudent_model', 'examgroup_model', 'grade_model', 'marksdivision_model', 'currency_model', 'section_model','holiday_model', 'class_model', 'category_model'));
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
    }

    public function index()
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
                    if((is_object($head_foot) ? $head_foot->print_type : $head_foot['print_type']) == 'general_purpose'){
                        $this->data['header_image'] = is_object($head_foot) ? $head_foot->header_image : $head_foot['header_image'];
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
         
            // Explicitly set base_assets_url - it's usually set by load_theme
            // THEMES_DIR is defined in MY_Controller.php
            $this->base_assets_url = 'backend/' . THEMES_DIR . '/' . $this->front_setting->theme . '/';
            $this->data['base_assets_url'] = base_url() . $this->base_assets_url;

            // Ensure $front_setting is available in $this->data
            $this->data['front_setting'] = $this->front_setting; // This is already set in Front_Controller::__construct() and Public_admission::__construct()

            // Ensure $school_setting is available. Front_Controller::__construct() sets $this->school_details.
            $this->data['school_setting'] = $this->sch_setting_detail;
            
            // Handle form validation
            if ($this->form_validation->run() == false) {
                // Render the main form content (pages/admission) into a variable
                $this->data['content'] = $this->load->view('themes/' . $this->front_setting->theme . '/pages/admission', $this->data, true);
                
                // Now load the custom template, passing all collected data
                $this->load->view('themes/' . $this->front_setting->theme . '/pages/public_admission_template', $this->data);
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

                    $data['reference_no']       = $reference_no;
                    $data['applicant_password'] = md5($reference_no . '@ApplicantPortal' . date('Y'));

                    if (isset($_FILES["document"]) && !empty($_FILES['document']['name'])) {
                        $upload_result = $this->media_storage->fileupload("document", "./uploads/student_documents/online_admission_doc/");
        if ($upload_result['status'] === false) {
            $this->session->set_flashdata('error', $upload_result['message']);
            redirect('public_admission');
        }
        $img_name         = $upload_result['message'];
                        
                        $data['document'] = $img_name;
                    }

                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $upload_result = $this->media_storage->fileupload("file", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('public_admission');
                        }
                        $img_name      = $upload_result['message'];
                        $data['image'] = 'uploads/student_images/online_admission_image/' . $img_name;
                    }

                    if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                        $upload_result = $this->media_storage->fileupload("father_pic", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('public_admission');
                        }
                        $img_name           = $upload_result['message'];
                        $data['father_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }

                    if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                        $upload_result = $this->media_storage->fileupload("mother_pic", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('public_admission');
                        }
                        $img_name           = $upload_result['message'];
                        $data['mother_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }

                                        if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {

                                            $upload_result = $this->media_storage->fileupload("guardian_pic", "./uploads/student_images/online_admission_image/");

                                            if ($upload_result['status'] === false) {

                                                $this->session->set_flashdata('error', $upload_result['message']);

                                                redirect('public_admission');

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
                    $this->_autoAssignApplicantExams($insert_id);
                    $this->session->set_userdata('validlogin', $reference_no);
                    $this->session->set_flashdata('msg', '<div class="alert alert-success">' . ' ' . $this->lang->line('thanks_for_registration_please_note_your_reference_number') . ' ' . $reference_no . ' ' . $this->lang->line('for_further_communication') . '</div>');
                    redirect('public_admission/online_admission_review/' . $reference_no);
                }
            }
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
                $data["student_categorize"]     = 'class';
                $session                        = $this->setting_model->getCurrentSession();
                $id                             = $this->onlinestudent_model->getidbyrefno($reference_no);
                $class                          = $this->class_model->getAll();
                $this->data['classlist']        = $class;
                $this->data['sch_setting']      = $this->sch_setting_detail;
                $category                       = $this->category_model->get();
                $this->data['categorylist']     = $category;
                $result                         = $this->onlinestudent_model->get($id);
                $classresult                    = $this->onlinestudent_model->getclassbyclasssectionid($result['class_section_id']);
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
                $current_year = date('Y');
                $this->data['applicant_login_url'] = site_url('site/applicantlogin');
                $this->data['applicant_username'] = $result['reference_no'];
                $this->data['applicant_password'] = $result['reference_no'] . '@ApplicantPortal' . $current_year;
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

            $current_year = date('Y');
            $login_url = site_url('site/applicantlogin');
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
            $classresult                  = $this->onlinestudent_model->getclassbyclasssectionid($result['class_section_id']);
            $class_section_id             = $classresult['class_id'];
            $class                        = $classresult['class'];
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
                                                    redirect('public_admission');
                                                }
                                                $img_name         = $upload_result['message'];
                                                $data['document'] = $img_name;
                                            }                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $upload_result = $this->media_storage->fileupload("file", "./uploads/student_images/online_admission_image/");
                        if ($upload_result['status'] === false) {
                            $this->session->set_flashdata('error', $upload_result['message']);
                            redirect('public_admission');
                        }
                        $img_name      = $upload_result['message'];
                        $data['image'] = 'uploads/student_images/online_admission_image/' .$img_name;
                    }
                                            if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                                                $upload_result = $this->media_storage->fileupload("father_pic", "./uploads/student_images/online_admission_image/");
                                                if ($upload_result['status'] === false) {
                                                    $this->session->set_flashdata('error', $upload_result['message']);
                                                    redirect('public_admission');
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
                                                    redirect('public_admission');
                                                }
                                                $img_name             = $upload_result['message'];
                                                $data['guardian_pic'] = 'uploads/student_images/online_admission_image/' .$img_name;
                                            }

                        $this->onlinestudent_model->edit($data);
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

    private function getExamApplicantBySession()
    {
        $reference_no = $this->session->userdata('validlogin');
        if (empty($reference_no)) {
            return null;
        }

        $admission_id = $this->onlinestudent_model->getidbyrefno($reference_no);
        if (empty($admission_id)) {
            return null;
        }

        $applicant = $this->onlinestudent_model->get($admission_id);
        if (empty($applicant)) {
            return null;
        }

        return $applicant;
    }

    public function exam_list()
    {
        $applicant = $this->getExamApplicantBySession();
        if (empty($applicant)) {
            redirect('public_admission');
        }

        $this->load->model('onlineexam_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">Online exam for applicants is not enabled yet. Please contact administrator.</div>');
            redirect('public_admission/online_admission_review/' . $applicant['reference_no']);
        }

        $data                     = array();
        $data['sch_setting']      = $this->sch_setting_detail;
        $data['sch_name']         = $this->sch_setting_detail->name ?? 'Applicant Portal';
        $data['applicant']        = $applicant;
        $data['onlineexam']       = $this->onlineexam_model->getApplicantexam($applicant['id']);
        $data['reference_no']     = $applicant['reference_no'];
        // Build applicant_info object for the layout header
        $applicant_obj            = new stdClass();
        $applicant_obj->firstname    = $applicant['firstname'];
        $applicant_obj->lastname     = $applicant['lastname'];
        $applicant_obj->reference_no = $applicant['reference_no'];
        $data['applicant_info']   = $applicant_obj;
        $data['title']            = 'Online Exams';
        $this->load->view('layout/applicant/header', $data);
        $this->load->view('public_admission/exam_list', $data);
        $this->load->view('layout/applicant/footer', $data);
    }

    public function exam_view($id)
    {
        $applicant = $this->getExamApplicantBySession();
        if (empty($applicant)) {
            redirect('public_admission');
        }

        $this->load->model('onlineexam_model');
        $this->load->model('onlineexamresult_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">Online exam for applicants is not enabled yet. Please contact administrator.</div>');
            redirect('public_admission/online_admission_review/' . $applicant['reference_no']);
        }

        $exam                 = $this->onlineexam_model->getexamdetails($id);
        $online_exam_validate = $this->onlineexam_model->examapplicantID($applicant['id'], $id);
        if (empty($exam) || empty($online_exam_validate)) {
            show_404();
        }

        // Block direct URL access before exam window opens
        if (strtotime(date('Y-m-d H:i:s')) < strtotime($exam->exam_from)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning"><i class="fa fa-clock-o"></i> The exam has not started yet. It is scheduled from <strong>' . $this->customlib->dateyyyymmddToDateTimeformat($exam->exam_from, false) . '</strong>. Please come back at that time.</div>');
            redirect('public_admission/exam_list');
        }

        $data                        = array();
        $data['sch_setting']         = $this->sch_setting_detail;
        $data['sch_name']            = $this->sch_setting_detail->name ?? 'Applicant Portal';
        $data['question_true_false'] = $this->config->item('question_true_false');
        $data['exam']                = $exam;
        $data['applicant']           = $applicant;
        $data['questionOpt']         = $this->customlib->getQuesOption();
        $data['online_exam_validate']= $online_exam_validate;
        $data['question_result']     = $this->onlineexamresult_model->getResultByStudent($online_exam_validate->id, $online_exam_validate->onlineexam_id);
        $data['result_prepare']      = $this->onlineexamresult_model->checkResultPrepare($online_exam_validate->id);

        $filetype                    = $this->filetype_model->get();
        $data['allowed_extension']   = array_map('trim', array_map('strtolower', explode(',', $filetype->file_extension)));
        $data['allowed_mime_type']   = array_map('trim', array_map('strtolower', explode(',', $filetype->file_mime)));
        $data['allowed_upload_size'] = $filetype->file_size;

        // Build applicant_info object required by the layout header
        $applicant_obj               = new stdClass();
        $applicant_obj->firstname    = $applicant['firstname'];
        $applicant_obj->lastname     = $applicant['lastname'];
        $applicant_obj->reference_no = $applicant['reference_no'];
        $data['applicant_info']      = $applicant_obj;
        $data['title']               = 'Exam: ' . $exam->exam;

        $this->load->view('layout/applicant/header', $data);
        $this->load->view('public_admission/exam_view', $data);
        $this->load->view('layout/applicant/footer', $data);
    }

    public function getApplicantExamForm()
    {
        $applicant = $this->getExamApplicantBySession();
        if (empty($applicant)) {
            echo json_encode(array('status' => 1, 'message' => 'Session expired'));
            return;
        }

        $this->load->model('onlineexam_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            echo json_encode(array('status' => 1, 'message' => 'Applicant exam schema missing'));
            return;
        }

        $question_status = 0;
        $recordid        = $this->input->post('recordid');
        $exam            = $this->onlineexam_model->getexamdetails($recordid);
        if (empty($exam)) {
            echo json_encode(array('status' => 1, 'message' => 'Exam not found'));
            return;
        }

        $data                 = array();
        $data['exam']         = $exam;
        $data['questions']    = $this->onlineexam_model->getExamQuestions($recordid, $exam->is_random_question);
        $onlineexam_student   = $this->onlineexam_model->examapplicantID($applicant['id'], $exam->id);
        if (empty($onlineexam_student)) {
            echo json_encode(array('status' => 1, 'message' => 'Applicant is not assigned to this exam'));
            return;
        }

        $data['onlineexam_student_id'] = $onlineexam_student;
        $data['question_status']       = 0;
        $data['exam_duration']         = $exam->duration;

        $now = strtotime(date('Y-m-d H:i:s'));
        if ($now < strtotime($exam->exam_from)) {
            // Exam has not started yet
            $question_status         = 1;
            $data['question_status'] = 1;
            $data['exam_not_started'] = true;
        } else if ($now >= strtotime($exam->exam_to)) {
            // Exam window has closed
            $question_status         = 1;
            $data['question_status'] = 1;
        } else if ($onlineexam_student->is_attempted) {
            // Applicant already submitted — block re-entry
            $question_status         = 1;
            $data['question_status'] = 1;
        } else {
            // Exam open and not yet submitted — allow access
            $question_status = 0;
        }

        $data['questionOpt'] = $this->customlib->getQuesOption();
        $pag_content         = $this->load->view('user/onlineexam/_searchQuestionByExamID', $data, true);

        $total_remaining_seconds = round((strtotime($exam->exam_to) - strtotime(date('Y-m-d H:i:s'))) / 3600 * 60 * 60, 1);
        $exam_duration           = ($total_remaining_seconds < getSecondsFromHMS($exam->duration)) ? getHMSFromSeconds($total_remaining_seconds) : $exam->duration;

        echo json_encode(array('status' => 0, 'exam' => $exam, 'duration' => $exam_duration, 'page' => $pag_content, 'question_status' => $question_status, 'total_question' => count($data['questions'])));
    }

    public function hall_ticket($exam_id = null)
    {
        $applicant = $this->getExamApplicantBySession();
        if (empty($applicant)) {
            redirect('public_admission');
            return;
        }

        $exam_id = (int) $exam_id;
        if ($exam_id <= 0) {
            redirect('public_admission/exam_list');
            return;
        }

        $this->load->model('onlineexam_model');

        // Verify this exam is actually assigned to this applicant
        $this->db->where('os.onlineexam_id', $exam_id);
        $this->db->where('os.online_admission_id', (int) $applicant['id']);
        $this->db->where('os.candidate_type', 'applicant');
        $this->db->from('onlineexam_students os');
        $this->db->join('onlineexam oe', 'oe.id = os.onlineexam_id', 'inner');
        $this->db->select('os.id as onlineexam_student_id, os.is_attempted, oe.*');
        $exam_assignment = $this->db->get()->row();

        if (empty($exam_assignment)) {
            redirect('public_admission/exam_list');
            return;
        }

        // Fetch print header for online_exam type
        $this->db->where('print_type', 'online_exam');
        $print_header = $this->db->get('print_headerfooter')->row();

        // School settings
        $sch_setting = $this->setting_model->getSetting();

        $data = array(
            'applicant'    => $applicant,
            'exam'         => $exam_assignment,
            'print_header' => $print_header,
            'sch_setting'  => $sch_setting,
        );

        $this->load->view('public_admission/hall_ticket', $data);
    }

    public function save_applicant_exam()
    {
        $applicant = $this->getExamApplicantBySession();
        if (empty($applicant)) {
            redirect('public_admission');
        }

        $this->load->model('onlineexam_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            redirect('public_admission/exam_list', 'refresh');
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $onlineexam_student_id = (int) $this->input->post('onlineexam_student_id');
            if ($onlineexam_student_id <= 0) {
                redirect('public_admission/exam_list', 'refresh');
            }

            $this->db->where('id', $onlineexam_student_id);
            $this->db->where('online_admission_id', $applicant['id']);
            $this->db->where('candidate_type', 'applicant');
            $exam_assignment = $this->db->get('onlineexam_students')->row();
            if (empty($exam_assignment)) {
                redirect('public_admission/exam_list', 'refresh');
            }

            if ((int) $exam_assignment->is_attempted === 1) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => false, 'message' => 'This exam has already been submitted.']);
                    return;
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-warning">This exam is already submitted.</div>');
                redirect('public_admission/exam_list', 'refresh');
            }

            $this->db->where('onlineexam_student_id', $onlineexam_student_id);
            $existing_result_count = (int) $this->db->count_all_results('onlineexam_student_results');
            if ($existing_result_count > 0) {
                $this->onlineexam_model->updateExamResult($onlineexam_student_id);
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => false, 'message' => 'This exam has already been submitted.']);
                    return;
                }
                $this->session->set_flashdata('msg', '<div class="alert alert-warning">This exam is already submitted.</div>');
                redirect('public_admission/exam_list', 'refresh');
            }

            $total_rows = $this->input->post('total_rows');
            if (!empty($total_rows)) {
                $save_result = array();
                foreach ($total_rows as $row_key => $row_value) {
                    if (($_POST['question_type_' . $row_value]) == "singlechoice") {
                        if (isset($_POST['radio' . $row_value])) {
                            $save_result[] = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => $_POST['radio' . $row_value],
                                'attachment_name'        => "",
                                'attachment_upload_name' => "",
                            );
                        }
                    } elseif (($_POST['question_type_' . $row_value]) == "true_false") {
                        if (isset($_POST['radio' . $row_value])) {
                            $save_result[] = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => $_POST['radio' . $row_value],
                                'attachment_name'        => "",
                                'attachment_upload_name' => "",
                            );
                        }
                    } elseif (($_POST['question_type_' . $row_value]) == "multichoice") {
                        if (isset($_POST['checkbox' . $row_value])) {
                            $save_result[] = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => json_encode($_POST['checkbox' . $row_value]),
                                'attachment_name'        => "",
                                'attachment_upload_name' => "",
                            );
                        }
                    } elseif (($_POST['question_type_' . $row_value]) == "descriptive") {
                        if (isset($_POST['answer' . $row_value]) || (isset($_FILES["attachment" . $row_value]) && !empty($_FILES["attachment" . $row_value]['name']))) {
                            $inst_array = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => $_POST['answer' . $row_value],
                            );

                            $file_name        = "";
                            $upload_file_name = "";
                            if (isset($_FILES["attachment" . $row_value]) && !empty($_FILES["attachment" . $row_value]['name'])) {
                                $file_name        = $_FILES["attachment" . $row_value]["name"];
                                $fileInfo         = pathinfo($_FILES["attachment" . $row_value]["name"]);
                                $upload_file_name = time() . uniqid(rand()) . '.' . $fileInfo['extension'];
                                $upload_dir = "./uploads/onlinexam_images/";
                                $this->customlib->ensureDirectoryExists($upload_dir);
                                move_uploaded_file($_FILES["attachment" . $row_value]["tmp_name"], $upload_dir . $upload_file_name);
                            }
                            $inst_array['attachment_name']        = $file_name;
                            $inst_array['attachment_upload_name'] = $upload_file_name;
                            $save_result[]                        = $inst_array;
                        }
                    }
                }

                $this->load->model('onlineexamresult_model');
                $this->onlineexamresult_model->add($save_result);
                $this->onlineexam_model->updateExamResult($onlineexam_student_id);
            }
        }

        // Return JSON for AJAX submissions (exam_view AJAX submit)
        if ($this->input->is_ajax_request()) {
            $exam_data      = $this->db->get_where('onlineexam', ['id' => $exam_assignment->onlineexam_id])->row();
            // For quiz with immediate result enabled, show score even before admin publishes
            if ($exam_data && $exam_data->is_quiz && !empty($exam_data->show_result_immediately)) {
                $publish_result = 1;
            } else {
                $publish_result = ($exam_data && (int) $exam_data->publish_result === 1) ? 1 : 0;
            }

            $this->db->select(
                'COUNT(oq.id)                                                                           AS total_questions,
                 SUM(oq.marks)                                                                          AS total_marks,
                 SUM(CASE WHEN osr.select_option = q.correct THEN oq.marks ELSE 0 END)                  AS obtained_marks,
                 SUM(CASE WHEN osr.select_option = q.correct THEN 1       ELSE 0 END)                   AS correct_count,
                 COUNT(osr.id)                                                                          AS answered',
                false
            );
            $this->db->from('onlineexam_questions oq');
            $this->db->join('questions q', 'q.id = oq.question_id', 'inner');
            $this->db->join(
                'onlineexam_student_results osr',
                'osr.onlineexam_question_id = oq.id AND osr.onlineexam_student_id = ' . (int) $onlineexam_student_id,
                'left'
            );
            $this->db->where('oq.onlineexam_id', (int) $exam_assignment->onlineexam_id);
            $score = $this->db->get()->row();

            echo json_encode([
                'success'         => true,
                'publish_result'  => $publish_result,
                'total_questions' => (int)   ($score->total_questions ?? 0),
                'total_marks'     => round((float) ($score->total_marks    ?? 0), 2),
                'obtained_marks'  => round((float) ($score->obtained_marks ?? 0), 2),
                'correct'         => (int)   ($score->correct_count         ?? 0),
                'answered'        => (int)   ($score->answered              ?? 0),
            ]);
            return;
        }

        redirect('public_admission/exam_list', 'refresh');
    }

    /**
     * Applicant dashboard - main portal page
     */
    public function applicant_dashboard()
    {
        // Check if applicant is logged in
        $reference_no = $this->session->userdata('validlogin');
        if (empty($reference_no)) {
            $this->session->set_flashdata('login_error', 'Please login first');
            redirect('site/userlogin');
            return;
        }

        // Get applicant info with course name and total fee (same as payment_history)
        $ref_clean = preg_replace('/\s+/', '', $reference_no);
        $this->db->select('oa.*, IFNULL(oac.course_name, "N/A") as course_name,
            COALESCE(oa.course_fee_total,
                IF(oa.quota_type = "management", oac.mgt_fee, oac.govt_fee)
            ) as total_fee');
        $this->db->from('online_admissions oa');
        $this->db->join('online_admission_courses oac',
            'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left');
        $this->db->where("REPLACE(oa.reference_no, ' ', '') = " . $this->db->escape($ref_clean), null, false);
        $applicant_info = $this->db->get()->row();

        if (!$applicant_info) {
            $this->session->unset_userdata('validlogin');
            redirect('site/userlogin');
            return;
        }

        // Get payment history from incidental_fee_collections (same source as admin eye icon)
        $this->load->model('Onlinestudent_model');
        $date_format     = $this->customlib->getSchoolDateFormat();
        $payment_history = $this->Onlinestudent_model->get_payment_history_by_ref($ref_clean, $date_format);
        $total_paid      = array_sum(array_column($payment_history, 'amount_raw'));

        // Total due = course fee + application fee (application fee is collected separately on top of course fee)
        $course_fee      = (float) ($applicant_info->total_fee ?? 0);
        $application_fee = 0.0;
        foreach ($payment_history as $p) {
            if (stripos($p['fee_type'], 'application') !== false) {
                $application_fee += (float) $p['amount_raw'];
            }
        }
        $total_fee = $course_fee + $application_fee;
        $balance   = $total_fee - $total_paid;

        // Get assigned exams — query directly to avoid loading onlineexam_model (which requires staff context)
        $has_schema = $this->db->field_exists('candidate_type', 'onlineexam_students')
                      && $this->db->field_exists('online_admission_id', 'onlineexam_students');
        if ($has_schema) {
            $assigned_exams = $this->db
                ->select('os.is_attempted, oe.id, oe.exam, oe.publish_result, oe.is_quiz, oe.show_result_immediately')
                ->from('onlineexam_students os')
                ->join('onlineexam oe', 'oe.id = os.onlineexam_id')
                ->where('os.online_admission_id', $applicant_info->id)
                ->where('os.candidate_type', 'applicant')
                ->order_by('os.id', 'DESC')
                ->get()
                ->result();
        } else {
            $assigned_exams = array();
        }

        $this->data['applicant_info']  = $applicant_info;
        $this->data['payment_history'] = $payment_history;
        $this->data['total_paid']      = $total_paid;
        $this->data['total_fee']       = $total_fee;
        $this->data['balance']         = $balance;
        $this->data['assigned_exams']  = $assigned_exams;
        $this->data['title']           = 'Applicant Portal';
        $this->data['sch_name']        = $this->sch_setting_detail->name ?? 'Applicant Portal';

        $this->load->view('layout/applicant/header', $this->data);
        $this->load->view('public_admission/applicant_dashboard', $this->data);
        $this->load->view('layout/applicant/footer', $this->data);
    }

    /**
     * Payment history page
     */
    public function payment_history()
    {
        // Check if applicant is logged in
        $reference_no = $this->session->userdata('validlogin');
        if (empty($reference_no)) {
            redirect('site/userlogin');
            return;
        }

        // Get applicant info with course name and fee total
        $this->db->select('oa.*, IFNULL(oac.course_name, "N/A") as course_name,
            COALESCE(oa.course_fee_total,
                IF(oa.quota_type = "management", oac.mgt_fee, oac.govt_fee)
            ) as total_fee');
        $this->db->from('online_admissions oa');
        $this->db->join('online_admission_courses oac',
            'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left');
        $ref_clean = preg_replace('/\s+/', '', $reference_no);
        $this->db->where("REPLACE(oa.reference_no, ' ', '') = " . $this->db->escape($ref_clean), null, false);
        $applicant_info = $this->db->get()->row();

        if (!$applicant_info) {
            $this->session->unset_userdata('validlogin');
            redirect('site/userlogin');
            return;
        }

        // Load same model used by admin eye icon
        $this->load->model('Onlinestudent_model');
        $date_format   = $this->customlib->getSchoolDateFormat();
        $payment_history = $this->Onlinestudent_model->get_payment_history_by_ref($ref_clean, $date_format);

        $total_paid = array_sum(array_column($payment_history, 'amount_raw'));
        $total_fee  = (float) ($applicant_info->total_fee ?? 0);
        $balance    = $total_fee - $total_paid;

        // Receipt header image
        $header_row = $this->db->select('header_image')->from('print_headerfooter')
            ->where('print_type', 'online_admission_receipt')->get()->row_array();
        $header_image = '';
        if (!empty($header_row['header_image'])) {
            $header_image = base_url('uploads/print_headerfooter/online_admission_receipt/' . $header_row['header_image']);
        }

        $this->data['applicant_info']  = $applicant_info;
        $this->data['payment_history'] = $payment_history;
        $this->data['total_paid']      = $total_paid;
        $this->data['total_fee']       = $total_fee;
        $this->data['balance']         = $balance;
        $this->data['header_image']    = $header_image;
        $this->data['sch_phone']       = $this->sch_setting_detail->phone ?? '';
        $this->data['sch_email']       = $this->sch_setting_detail->email ?? '';
        $this->data['title']           = 'Fee Payment Receipt';
        $this->data['sch_name']        = $this->sch_setting_detail->name ?? 'Applicant Portal';

        $this->load->view('layout/applicant/header', $this->data);
        $this->load->view('public_admission/payment_history', $this->data);
        $this->load->view('layout/applicant/footer', $this->data);
    }

    private function _autoAssignApplicantExams($admission_id)
    {
        if (empty($admission_id) || !$this->db->field_exists('candidate_type', 'onlineexam_students')
            || !$this->db->field_exists('online_admission_id', 'onlineexam_students')) {
            return;
        }

        $this->db->select('DISTINCT(os.onlineexam_id) AS onlineexam_id', false);
        $this->db->from('onlineexam_students os');
        $this->db->join('onlineexam oe', 'oe.id = os.onlineexam_id', 'inner');
        $this->db->where('os.candidate_type', 'applicant');
        $this->db->where('oe.is_active', 1);
        $this->db->where('oe.exam_to >=', date('Y-m-d H:i:s'));
        $active_exams = $this->db->get()->result_array();

        foreach ($active_exams as $exam_row) {
            $exam_id = $exam_row['onlineexam_id'];
            $already = $this->db->where('onlineexam_id', $exam_id)
                ->where('online_admission_id', $admission_id)
                ->where('candidate_type', 'applicant')
                ->count_all_results('onlineexam_students');
            if ($already == 0) {
                $this->db->insert('onlineexam_students', [
                    'onlineexam_id'       => $exam_id,
                    'online_admission_id' => $admission_id,
                    'candidate_type'      => 'applicant',
                    'is_attempted'        => 0,
                ]);
            }
        }
    }

    /**
     * Logout applicant
     */
    public function applicant_logout()
    {
        if (!empty($this->session->userdata('validlogin'))) {
            $this->session->unset_userdata('validlogin');
        }
        
        $this->session->set_flashdata('success_msg', 'Logged out successfully');
        redirect('site/userlogin');
    }
}
?>