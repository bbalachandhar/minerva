<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Onlinestudent extends Admin_Controller
{

    public $sch_setting_detail = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->library('smsgateway');
        $this->load->library('mailsmsconf');
        $this->load->library('encoding_lib');
        $this->load->model(array("timeline_model", "classteacher_model", 'transportfee_model'));
        $this->blood_group        = $this->config->item('bloodgroup');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->role;
		$this->load->library('media_storage');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'onlinestudent');
        $data['title']       = 'Student List';
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/onlinestudent/studentList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function download($doc)
    {
        $this->load->helper('download');
        $filepath = "./uploads/student_documents/online_admission_doc/" . $doc;
        $data     = file_get_contents($filepath);
        $name     = $this->uri->segment(6);
        force_download($name, $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_delete')) {
            access_denied();
        }
        $this->onlinestudent_model->remove($id);

        redirect('admin/onlinestudent');
    }

    public function onlineadmission_download($id)
    {        
		$doc   = $this->onlinestudent_model->get($id);		
		$this->media_storage->filedownload($doc['document'], "uploads/student_documents/online_admission_doc");
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_edit')) {
            access_denied();
        }
        $data['adm_auto_insert']       = $this->sch_setting_detail->adm_auto_insert;
        $data['title']                 = $this->lang->line('edit_student');
        $session                       = $this->setting_model->getCurrentSession();
        $data['transport_fees']        = $this->transportfee_model->getSessionFees($session);
        $data['feesessiongroup_model'] = $this->feesessiongroup_model->getFeesByGroup();
        $data['id']                    = $id;
        $student                       = $this->onlinestudent_model->get($id);
        $genderList                    = $this->customlib->getGender();
        $data['student']               = $student;
        $data['genderList']            = $genderList;
        $session                       = $this->setting_model->getCurrentSession();
        $vehroute_result               = $this->vehroute_model->getRouteVehiclesList();
        $data['vehroutelist']          = $vehroute_result;
        $class                         = $this->class_model->get();
        $setting_result                = $this->setting_model->get();
        $data["bloodgroup"]            = $this->blood_group;
        $data["student_categorize"]    = 'class';
        $data['classlist']             = $class;
        $category                      = $this->category_model->get();
        $data['categorylist']          = $category;
        $hostelList                    = $this->hostel_model->get();
        $data['hostelList']            = $hostelList;
        $houses                        = $this->houselist_model->get();
        $data['houses']                = $houses;
        $data['sch_setting']           = $this->sch_setting_detail;

        //fees discount
        $feesdiscount_result           = $this->feediscount_model->get();
        $data['feediscountList']       = $feesdiscount_result;
        //fees discount

        if ($this->input->post('save') == 'enroll') {
            if (!$this->sch_setting_detail->adm_auto_insert) {

                $this->form_validation->set_rules('admission_no', $this->lang->line('admission_no'), array('required', array('check_admission_no_exists', array($this->student_model, 'valid_student_admission_no'))));
            }

            $this->form_validation->set_rules(
                'email', $this->lang->line('email'), array(
                    'valid_email',
                    array('check_student_email_exists', array($this->student_model, 'check_student_email_exists')),
                )
            );            
             
            $transport_feemaster_id = $this->input->post('transport_feemaster_id');
            if($transport_feemaster_id){
                $this->form_validation->set_rules('vehroute_id', $this->lang->line('route_list'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('route_pickup_point_id', $this->lang->line('pickup_point'), 'trim|required|xss_clean');
                $this->form_validation->set_rules('transport_feemaster_id[]', $this->lang->line('fees_month'), 'trim|required|xss_clean');
            }
            
        }

        $this->form_validation->set_rules('firstname', $this->lang->line('first_name'), 'trim|required|xss_clean');
        if ($this->sch_setting_detail->guardian_name) {
            $this->form_validation->set_rules('guardian_is', $this->lang->line('guardian'), 'trim|required|xss_clean');
        }

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload[file]');
        $this->form_validation->set_rules('father_pic', $this->lang->line('image'), 'callback_handle_upload[father_pic]');
        $this->form_validation->set_rules('mother_pic', $this->lang->line('image'), 'callback_handle_upload[mother_pic]');
        $this->form_validation->set_rules('guardian_pic', $this->lang->line('image'), 'callback_handle_upload[guardian_pic]');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');
        if ($this->sch_setting_detail->guardian_name) {
            $this->form_validation->set_rules('guardian_name', $this->lang->line('guardian_name'), 'trim|required|xss_clean');
        }
        if ($this->sch_setting_detail->rte) {
            $this->form_validation->set_rules('rte', $this->lang->line('rtl'), 'trim|required|xss_clean');
        }        

        $custom_fields = $this->customfield_model->getByBelong('students');
        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation'] && $this->customlib->getfieldstatus($custom_fields_value['name'])) {
                $custom_fields_id   = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[students][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }       

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/onlinestudent/studentEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $fee_session_group_id   = $this->input->post('fee_session_group_id');
            $transport_feemaster_id = $this->input->post('transport_feemaster_id');
            $discount_id            = $this->input->post('discount_id[]');

            $student_id     = $this->input->post('student_id');
            $class_id       = $this->input->post('class_id');
            $section_id     = $this->input->post('section_id');
            $hostel_room_id = empty2null($this->input->post('hostel_room_id'));
            $fees_discount  = $this->input->post('fees_discount');            

            $route_pickup_point_id = empty2null($this->input->post('route_pickup_point_id'));
            $vehroute_id           = empty2null($this->input->post('vehroute_id'));
            $category_id           = empty2null($this->input->post('category_id'));           

            $data = array(
                'id'                    => $student_id,
                'admission_no'          => $this->input->post('admission_no'),
                'roll_no'               => $this->input->post('roll_no'),
                'firstname'             => $this->input->post('firstname'),
                'middlename'            => $this->input->post('middlename'),
                'lastname'              => $this->input->post('lastname'),
                'rte'                   => $this->input->post('rte'),
                'mobileno'              => $this->input->post('mobileno'),
                'email'                 => $this->input->post('email'),
                'state'                 => $this->input->post('state'),
                'city'                  => $this->input->post('city'),
                'previous_school'       => $this->input->post('previous_school'),
                'pincode'               => $this->input->post('pincode'),
                'measurement_date'      => $this->customlib->dateFormatToYYYYMMDD($this->input->post('measure_date')),
                'religion'              => $this->input->post('religion'),
                'dob'                   => $this->customlib->dateFormatToYYYYMMDD($this->input->post('dob')),
                'admission_date'        => $this->customlib->dateFormatToYYYYMMDD($this->input->post('admission_date')),
                'current_address'       => $this->input->post('current_address'),
                'permanent_address'     => $this->input->post('permanent_address'),
                'category_id'           => $category_id,
                'adhar_no'              => $this->input->post('adhar_no'),
                'samagra_id'            => $this->input->post('samagra_id'),
                'bank_account_no'       => $this->input->post('bank_account_no'),
                'bank_name'             => $this->input->post('bank_name'),
                'ifsc_code'             => $this->input->post('ifsc_code'),
                'cast'                  => $this->input->post('cast'),
                'father_name'           => $this->input->post('father_name'),
                'father_phone'          => $this->input->post('father_phone'),
                'father_occupation'     => $this->input->post('father_occupation'),
                'mother_name'           => $this->input->post('mother_name'),
                'mother_phone'          => $this->input->post('mother_phone'),
                'mother_occupation'     => $this->input->post('mother_occupation'),
                'guardian_email'        => $this->input->post('guardian_email'),
                'gender'                => $this->input->post('gender'),
                'guardian_name'         => $this->input->post('guardian_name'),
                'guardian_relation'     => $this->input->post('guardian_relation'),
                'guardian_phone'        => $this->input->post('guardian_phone'),
                'guardian_address'      => $this->input->post('guardian_address'),
                'hostel_room_id'        => $hostel_room_id,
                'note'                  => $this->input->post('note'),
                'class_section_id'      => $section_id,
                'route_pickup_point_id' => $route_pickup_point_id,
                'vehroute_id'           => $vehroute_id,
            );
			
            if ($this->sch_setting_detail->guardian_name) {
                $data['guardian_is'] = $this->input->post('guardian_is');
            }

            if ($this->sch_setting_detail->is_student_house) {
                $data['school_house_id'] = empty2null($this->input->post('house'));
            }

            if ($this->sch_setting_detail->guardian_occupation) {
                $data['guardian_occupation'] = $this->input->post('guardian_occupation');
            }

            if ($this->sch_setting_detail->is_blood_group) {
                $data['blood_group'] = $this->input->post('blood_group');
            }

            if ($this->sch_setting_detail->student_height) {
                $data['height'] = $this->input->post('height');
            }

            if ($this->sch_setting_detail->student_weight) {
                $data['weight'] = $this->input->post('weight');
            }
            if ($this->sch_setting_detail->measurement_date) {
                $data['measurement_date'] = $this->customlib->dateFormatToYYYYMMDD($this->input->post('measure_date'));
            }

            $response = $this->onlinestudent_model->update($data, $fee_session_group_id, $transport_feemaster_id,$discount_id, $this->input->post('save'));

            if ($response) {
                $response = json_decode($response);
                $custom_field_post = $this->input->post("custom_fields[students]");
                if (isset($custom_field_post)) {
                    $custom_value_array = array();
                    foreach ($custom_field_post as $key => $value) {
                        $check_field_type = $this->input->post("custom_fields[students][" . $key . "]");
                        $field_value      = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                        $array_custom     = array( 
                            'custom_field_id' => $key,
                            'field_value'     => $field_value,
                        );

                        if ($this->input->post('save') == "enroll") {
                            $array_custom['belong_table_id'] = $response->student_id;
                        }

                        $custom_value_array[] = $array_custom;
                    }

                    if ($this->input->post('save') == "enroll") {

                        $this->customfield_model->updateRecord($custom_value_array, $id, 'students');
                    } else {

                        $this->customfield_model->onlineadmissionupdateRecord($custom_value_array, $id, 'students');
                    }

                }
                //to upload document from online student to main firl
                if (isset($student['document']) && !empty($student['document'])) {
                    $uploaddir = './uploads/student_documents/' . $response->student_id . '/';
                    $this->customlib->ensureDirectoryExists($uploaddir);

                    $file_name           = basename($student['document']);
                    $img_name            = $uploaddir . $file_name;
                    $filePath            = "./uploads/student_documents/online_admission_doc/" . $student['document'];
                    $destinationFilePath = $img_name;
                    copy($filePath, $destinationFilePath);

                    $data_img = array('student_id' => $response->student_id, 'doc' => $file_name);
                    $this->student_model->adddoc($data_img);
                }

                //generate student id card only student enrolled
                if ($this->input->post('save') == "enroll") {
                    $student_details  = $this->student_model->get($response->student_id);
                    $scan_type= $this->sch_setting_detail->scan_code_type;
                    $this->customlib->generatebarcode($student_details['admission_no'],$student_details['id'],$scan_type);
                }
                //generate student id card only student enrolled

                // to upload father mother student and guardian image
                if ($this->input->post('save') == "enroll") {
                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $fileInfo = pathinfo($_FILES["file"]["name"]);
                        $img_name = $response->student_id . '.' . $fileInfo['extension'];
                        $this->customlib->ensureDirectoryExists('./uploads/student_images/');
                        move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/student_images/" . $img_name);
                        $data_img = array('id' => $response->student_id, 'image' => 'uploads/student_images/' . $img_name);
                        $this->student_model->add($data_img);
                    } else {
                        if ($student['image'] != "") {
                            $filePath = $student['image'];

                            $fileInfo = pathinfo($student['image']);
                            $img_name = $response->student_id . '.' . $fileInfo['extension'];

                            $uploaddir           = './uploads/student_images/' . $img_name;
                            $destinationFilePath = $uploaddir;

                            copy($filePath, $destinationFilePath);
                            $data_img = array('id' => $response->student_id, 'image' => 'uploads/student_images/' . $img_name);
                            $this->student_model->add($data_img);
                        }
                    }

                    if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {

                        $fileInfo = pathinfo($_FILES["father_pic"]["name"]);
                        $img_name = $response->student_id . "father" . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES["father_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                        $data_img = array('id' => $response->student_id, 'father_pic' => 'uploads/student_images/' . $img_name);
                        $this->student_model->add($data_img);
                    } else {
                        if ($student['father_pic'] != "") {
                            $filePath            = $student['father_pic'];
                            $fileInfo            = pathinfo($student['father_pic']);
                            $img_name            = $response->student_id . "father" . '.' . $fileInfo['extension'];
                            $uploaddir           = './uploads/student_images/' . $img_name;
                            $destinationFilePath = $uploaddir;
                            copy($filePath, $destinationFilePath);
                            $data_img = array('id' => $response->student_id, 'father_pic' => 'uploads/student_images/' . $img_name);
                            $this->student_model->add($data_img);
                        }
                    }

                    if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                        $fileInfo = pathinfo($_FILES["mother_pic"]["name"]);
                        $img_name = $response->student_id . "mother" . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES["mother_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                        $data_img = array('id' => $response->student_id, 'mother_pic' => 'uploads/student_images/' . $img_name);
                        $this->student_model->add($data_img);
                    } else {
                        if ($student['mother_pic'] != "") {
                            $filePath            = $student['mother_pic'];
                            $fileInfo            = pathinfo($student['mother_pic']);
                            $img_name            = $response->student_id . "mother" . '.' . $fileInfo['extension'];
                            $uploaddir           = './uploads/student_images/' . $img_name;
                            $destinationFilePath = $uploaddir;
                            copy($filePath, $destinationFilePath);
                            $data_img = array('id' => $response->student_id, 'mother_pic' => 'uploads/student_images/' . $img_name);
                            $this->student_model->add($data_img);
                        }
                    }

                    if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
                        $fileInfo = pathinfo($_FILES["guardian_pic"]["name"]);
                        $img_name = $response->student_id . "guardian" . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES["guardian_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                        $data_img = array('id' => $response->student_id, 'guardian_pic' => 'uploads/student_images/' . $img_name);
                        $this->student_model->add($data_img);
                    } else {
                        if ($student['guardian_pic'] != "") {
                            $filePath = $student['guardian_pic'];
                            $fileInfo = pathinfo($student['guardian_pic']);
                            $img_name = $response->student_id . "guardian" . '.' . $fileInfo['extension'];
                            $uploaddir           = './uploads/student_images/' . $img_name;
                            $destinationFilePath = $uploaddir;
                            copy($filePath, $destinationFilePath);
                            $data_img = array('id' => $response->student_id, 'guardian_pic' => 'uploads/student_images/' . $img_name);
                            $this->student_model->add($data_img);
                        }
                    }

                } else {
                    // to update image in online student table
                    $this->customlib->ensureDirectoryExists('./uploads/student_images/online_admission_image/');

                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $fileInfo = pathinfo($_FILES["file"]["name"]);
                        $img_name = $student['id'] . '.' . $fileInfo['extension'];
                        $this->customlib->ensureDirectoryExists('./uploads/student_images/online_admission_image/');
                        move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/student_images/online_admission_image/" . $img_name);
                        $data_img = array('id' => $student['id'], 'image' => 'uploads/student_images/online_admission_image/' . $img_name);
                        $this->onlinestudent_model->edit($data_img);
                    }

                    if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                        $fileInfo = pathinfo($_FILES["father_pic"]["name"]);
                        $img_name = $student['id'] . "father" . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES["father_pic"]["tmp_name"], "./uploads/student_images/online_admission_image/" . $img_name);
                        $data_img = array('id' => $student['id'], 'father_pic' => 'uploads/student_images/online_admission_image/' . $img_name);
                        $this->onlinestudent_model->edit($data_img);
                    }

                    if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                        $fileInfo = pathinfo($_FILES["mother_pic"]["name"]);
                        $img_name = $student['id'] . "mother" . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES["mother_pic"]["tmp_name"], "./uploads/student_images/online_admission_image/" . $img_name);
                        $data_img = array('id' => $student['id'], 'mother_pic' => 'uploads/student_images/online_admission_image/' . $img_name);
                        $this->onlinestudent_model->edit($data_img);
                    }

                    if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
                        $fileInfo = pathinfo($_FILES["guardian_pic"]["name"]);
                        $img_name = $student['id'] . "guardian" . '.' . $fileInfo['extension'];
                        move_uploaded_file($_FILES["guardian_pic"]["tmp_name"], "./uploads/student_images/online_admission_image/" . $img_name);
                        $data_img = array('id' => $student['id'], 'guardian_pic' => 'uploads/student_images/online_admission_image/' . $img_name);
                        $this->onlinestudent_model->edit($data_img);
                    }

                }

                if ($response->student_id != "") {

                    $sender_details = array('student_id' => $response->student_id, 'contact_no' => $this->input->post('guardian_phone'), 'email' => $this->input->post('guardian_email'));
                    $this->mailsmsconf->mailsms('student_admission', $sender_details);

                    $student_login_detail = array('id' => $response->student_id, 'credential_for' => 'student', 'username' => $this->student_login_prefix . $response->student_id, 'password' => $response->user_password, 'contact_no' => $this->input->post('mobileno'), 'email' => $this->input->post('email'), 'admission_no' => $response->admission_no);
                    $this->mailsmsconf->mailsms('student_login_credential', $student_login_detail);

                    $parent_login_detail = array('id' => $response->student_id, 'credential_for' => 'parent', 'username' => $this->parent_login_prefix . $response->student_id, 'password' => $response->parent_password, 'contact_no' => $this->input->post('guardian_phone'), 'email' => $this->input->post('guardian_email'));
                    $this->mailsmsconf->mailsms('login_credential', $parent_login_detail);
                }

                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
                redirect('admin/onlinestudent');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('please_check_student_admission_no') . '</div>');
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function getByClass()
    {
        $class_id = $this->input->post('class_id');
        $data     = $this->section_model->getClassBySection($class_id);
        $this->jsonlib->output(200, $data);
    }

    public function getstudentlist()
    {
        $sch_setting = $this->sch_setting_detail;

        $quota_type_filter    = $this->input->post('quota_type_filter');
        if ($quota_type_filter === 'govt') {
            $quota_type_filter = 'government';
        }
        $paid_status_filter   = $this->input->post('paid_status_filter');
        $submitted_by_filter  = $this->input->post('submitted_by_filter');

        $school_date_format  = $this->customlib->getSchoolDateFormat();
        $submit_date_from    = null;
        $submit_date_to      = null;
        $last_payment_date   = null;
        $raw_from = $this->input->post('submit_date_from');
        if (!empty($raw_from)) {
            $dt = DateTime::createFromFormat($school_date_format, $raw_from);
            if ($dt) { $submit_date_from = $dt->format('Y-m-d'); }
        }
        $raw_to = $this->input->post('submit_date_to');
        if (!empty($raw_to)) {
            $dt = DateTime::createFromFormat($school_date_format, $raw_to);
            if ($dt) { $submit_date_to = $dt->format('Y-m-d'); }
        }
        $raw_lpd = $this->input->post('last_payment_date');
        if (!empty($raw_lpd)) {
            $dt = DateTime::createFromFormat($school_date_format, $raw_lpd);
            if ($dt) { $last_payment_date = $dt->format('Y-m-d'); }
        }

        $student_result = $this->onlinestudent_model->getstudentlist(null, null, $quota_type_filter, $paid_status_filter, $submitted_by_filter, $submit_date_from, $submit_date_to, $last_payment_date);

        $m               = json_decode($student_result);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data         = array();

        $application_refs = array();
        if (!empty($m->data)) {
            foreach ($m->data as $entry) {
                if (!empty($entry->reference_no)) {
                    $application_refs[] = preg_replace('/\s+/', '', (string) $entry->reference_no);
                }
            }
        }
        $application_refs = array_values(array_unique($application_refs));
        $paid_amount_map = $this->onlinestudent_model->get_incidental_paid_amount_by_application_refs($application_refs);
        $app_fee_paid_refs = $this->onlinestudent_model->get_application_fee_paid_refs($application_refs);

        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $editbtn   = '';
                $deletebtn = '';
                $document  = '';
                $last_name = "";
                $mobileno  = "";
                $printbtn  = "";
                $status    = 'admin';

                if ($this->rbac->hasPrivilege('online_admission', 'can_edit')) {
                    if (!$value->is_enroll) {
                        // Edit Application Only button
                        $editbtn = "<a href='" . base_url() . 'admin/onlinestudent/edit_application/' . $value->id . "' class='btn btn-info btn-xs mt-5 pull-right' data-toggle='tooltip' title='Edit Application Details'><i class='fa fa-edit'></i></a>";
                        
                        // Edit & Enroll button
                        $editbtn .= " <a class='btn btn-warning btn-xs mt-5 pull-right' data-toggle='tooltip' title='Edit & Enroll' onclick='return checkpaymentstatus(" . '"' . $value->id . '"' . ")'><i class='fa fa-graduation-cap'></i></a>";
                    }
                }

                if ($this->rbac->hasPrivilege('online_admission', 'can_delete')) {
                    $deletebtn = '';

                    $deletebtn = "<a href='" . base_url() . 'admin/onlinestudent/delete/' . $value->id . "' class='btn btn-default btn-xs mt-5 pull-right' data-toggle='tooltip' title='" . $this->lang->line('delete') . "' onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . "  )'><i class='fa fa-remove'></i></a>";
                }

                if (!empty($value->reference_no)) {
                    $printbtn = "<a target='_blank' href='" . $this->customlib->getBaseUrl() . 'welcome/online_admission_review/' . $value->reference_no . "'  class='btn btn-default btn-xs mt-5 pull-right' data-toggle='tooltip' title='" . $this->lang->line('print') . "' ><i class='fa fa-print'></i></a>";
                } else {
                    $printbtn = "";
                }

                if (!empty($value->created_at)) {
                    $application_date = date($this->customlib->getSchoolDateFormat(), strtotime($value->created_at));
                } else {
                    $application_date = "";
                }

                if ($value->submit_date != null) {
                    $submit_date = " (" . date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->submit_date)) . ")";
                } else {
                    $submit_date = "";
                }

                if ($value->document) {
                    $document = "<a href='" . site_url("admin/onlinestudent/onlineadmission_download/" . $value->id) . "' class='btn btn-default btn-xs mt5'  data-toggle='tooltip' title='" . $this->lang->line('download') . "'>
                         <i class='fa fa-download'></i> </a>";
                }

                if ($sch_setting->lastname) {
                    $last_name = $value->lastname;
                }
                 $middlename ='';
                if ($sch_setting->middlename) {
                    $middlename = $value->middlename;
                }

                $row   = array();
                $row[] = $value->reference_no;
                $row[] = $value->firstname . " " . $middlename. " " . $last_name;
                $row[] = !empty($value->course_name) ? $value->course_name : "N/A";

                $row[] = $application_date;
                $submitted_by_name = trim((string) ($value->submitted_by_name ?? ''));
                if (!empty($value->referred_by_employee_id)) {
                    $row[] = $submitted_by_name !== '' ? $submitted_by_name : 'Staff';
                } else {
                    $row[] = 'Student';
                }
                $row[] = $this->lang->line(strtolower($value->gender));
                $row[] = !empty($value->quota_type) ? $value->quota_type : "N/A";

                $application_ref_no = !empty($value->reference_no) ? preg_replace('/\s+/', '', (string) $value->reference_no) : '';
                $course_fee = (isset($value->course_fee_total) && $value->course_fee_total !== null && $value->course_fee_total !== '') ? (float) $value->course_fee_total : 0;
                $paid_amount = (isset($paid_amount_map[$application_ref_no])) ? (float) $paid_amount_map[$application_ref_no] : 0;

                $row[] = number_format($course_fee, 2, '.', '');
                $row[] = number_format($paid_amount, 2, '.', '');

                if ($sch_setting->mobile_no) {
                    $row[] = $value->mobileno;
                }

                // App fee is paid if: manually recorded via incidental_fee_collections (fee type LIKE '%application%')
                // OR paid via online payment gateway (paid_status=1 on online_admissions row)
                $app_fee_is_paid = !empty($app_fee_paid_refs[$application_ref_no])
                    || (int) ($value->paid_status ?? 0) === 1;

                // Form Status: application fee paid or not (binary)
                if ($app_fee_is_paid) {
                    $row[] = '<span class="label label-success">Paid</span>';
                } else {
                    $row[] = '<span class="label label-danger">Not Paid</span>';
                }

                // Course Fee Status: based on course fee paid amount vs total
                if ($paid_amount <= 0 && $app_fee_is_paid) {
                    $row[] = '<span class="label label-info">Applied</span>';
                } elseif ($paid_amount <= 0) {
                    $row[] = '<span class="label label-danger">Not Paid</span>';
                } elseif ($course_fee > 0 && $paid_amount >= $course_fee) {
                    $row[] = '<span class="label label-success">Fully Paid</span>';
                } else {
                    $row[] = '<span class="label label-warning">Partially Paid</span>';
                }

                // Eye icon for paid / partially-paid records
                $eyebtn = "";
                if ($paid_amount > 0 && !empty($application_ref_no)) {
                    $eyebtn = "<a class='btn btn-primary btn-xs mt-5 pull-right' data-toggle='tooltip' title='View Fee Receipt' onclick='viewFeeReceipt(" . json_encode($application_ref_no) . ")'><i class='fa fa-eye'></i></a>";
                }

                $paybtn = "";
                if ($sch_setting->online_admission_payment == 'yes') {
                    if (!$app_fee_is_paid && $value->paid_status != 2) {
                        $paybtn = "<a class='btn btn-default btn-xs mt-5 pull-right' data-toggle='tooltip' title='" . $this->lang->line('add_payment') . "' onclick='addpayment(" . '"' . $value->id . '","' . $value->reference_no . '"' . "  )'><i class='fa fa-usd'></i></a>";
                    }
                    if ($app_fee_is_paid) {
                        $paybtn = '';
                    }
                }

                $row[]     = $document . ' ' . $printbtn . ' ' . $eyebtn . ' ' . $editbtn . ' ' . $deletebtn . ' ' . $paybtn;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function export_excel()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_view')) {
            access_denied();
        }

        $quota_type_filter   = $this->input->get('quota_type_filter');
        if ($quota_type_filter === 'govt') {
            $quota_type_filter = 'government';
        }
        $paid_status_filter  = $this->input->get('paid_status_filter');
        $submitted_by_filter = $this->input->get('submitted_by_filter');
        $sch_setting         = $this->sch_setting_detail;

        $school_date_format  = $this->customlib->getSchoolDateFormat();
        $submit_date_from    = null;
        $submit_date_to      = null;
        $last_payment_date   = null;
        $raw_from = $this->input->get('submit_date_from');
        if (!empty($raw_from)) {
            $dt = DateTime::createFromFormat($school_date_format, $raw_from);
            if ($dt) { $submit_date_from = $dt->format('Y-m-d'); }
        }
        $raw_to = $this->input->get('submit_date_to');
        if (!empty($raw_to)) {
            $dt = DateTime::createFromFormat($school_date_format, $raw_to);
            if ($dt) { $submit_date_to = $dt->format('Y-m-d'); }
        }
        $raw_lpd = $this->input->get('last_payment_date');
        if (!empty($raw_lpd)) {
            $dt = DateTime::createFromFormat($school_date_format, $raw_lpd);
            if ($dt) { $last_payment_date = $dt->format('Y-m-d'); }
        }

        // --- Build the base query (mirrors getstudentlist) ---
        $this->db->select(
            'oa.id, oa.reference_no, oa.firstname, oa.middlename, oa.lastname,
             oa.father_name, oa.gender, oa.mobileno, oa.email,
             oa.quota_type, oa.paid_status, oa.form_status, oa.created_at,
             oa.referred_by_employee_id,
             CONCAT(submitter_staff.name, " ", submitter_staff.surname) as submitted_by_name,
             COALESCE(oa.course_fee_total,
                 IF(oa.quota_type = "management", oac.mgt_fee, oac.govt_fee)
             ) AS course_fee_total,
             IFNULL(oac.course_name, "N/A") AS course_name',
            false
        );
        $this->db->from('online_admissions oa');
        $this->db->join('online_admission_courses oac',
            'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left');
        $this->db->join('staff submitter_staff', 'submitter_staff.id = oa.referred_by_employee_id', 'left');

        if (!empty($quota_type_filter)) {
            $this->db->where('oa.quota_type', $quota_type_filter);
        }

        if ($submitted_by_filter === 'student') {
            $this->db->where('(oa.referred_by_employee_id IS NULL OR oa.referred_by_employee_id = 0)', null, false);
        } elseif ($submitted_by_filter === 'staff') {
            $this->db->where('oa.referred_by_employee_id IS NOT NULL', null, false);
            $this->db->where('oa.referred_by_employee_id !=', 0);
        }

        // Course fee status filter (same logic as model)
        if ($paid_status_filter !== null && $paid_status_filter !== '') {
            $app_fee_sub  = "(SELECT COUNT(*) FROM incidental_fee_collections ifc_a"
                . " INNER JOIN incidental_fee_types ift_a ON ift_a.id = ifc_a.incidental_fee_type_id"
                . " WHERE REPLACE(ifc_a.application_ref_no,' ','') = REPLACE(oa.reference_no,' ','')"
                . " AND ifc_a.application_ref_no IS NOT NULL AND ifc_a.application_ref_no != ''"
                . " AND LOWER(ift_a.title) LIKE '%application%')";
            $paid_sub     = "(SELECT COALESCE(SUM(ifc2.amount_collected),0) FROM incidental_fee_collections ifc2"
                . " LEFT JOIN incidental_fee_types ift2 ON ift2.id = ifc2.incidental_fee_type_id"
                . " WHERE REPLACE(ifc2.application_ref_no,' ','') = REPLACE(oa.reference_no,' ','')"
                . " AND ifc2.application_ref_no IS NOT NULL AND ifc2.application_ref_no != ''"
                . " AND (LOWER(ift2.title) LIKE '%tuition%' OR LOWER(ift2.title) LIKE '%tution%' OR LOWER(ift2.title) LIKE '%other fee%'))";
            $course_fee   = "COALESCE(oa.course_fee_total, IF(oa.quota_type='management', oac.mgt_fee, oac.govt_fee))";
            if ($paid_status_filter === 'applied') {
                $this->db->where("($app_fee_sub > 0 OR oa.paid_status = 1) AND $paid_sub <= 0", null, false);
            } elseif ($paid_status_filter === '0') {
                $this->db->where("$app_fee_sub = 0 AND $paid_sub <= 0", null, false);
            } elseif ($paid_status_filter === '2') {
                $this->db->where("$paid_sub > 0 AND ($course_fee <= 0 OR $paid_sub < $course_fee)", null, false);
            } elseif ($paid_status_filter === '1') {
                $this->db->where("$course_fee > 0 AND $paid_sub >= $course_fee", null, false);
            }
        }

        // Submission date range
        if (!empty($submit_date_from)) {
            $this->db->where('COALESCE(oa.submit_date, DATE(oa.created_at)) >=', $submit_date_from);
        }
        if (!empty($submit_date_to)) {
            $this->db->where('COALESCE(oa.submit_date, DATE(oa.created_at)) <=', $submit_date_to);
        }

        // Last payment received date
        if (!empty($last_payment_date)) {
            $lpd = $this->db->escape($last_payment_date);
            $this->db->where(
                "(EXISTS (SELECT 1 FROM online_admission_payment _lp"
                . " WHERE _lp.online_admission_id = oa.id AND DATE(_lp.date) = $lpd)"
                . " OR EXISTS (SELECT 1 FROM incidental_fee_collections _lfc"
                . " WHERE REPLACE(_lfc.application_ref_no,' ','') = REPLACE(oa.reference_no,' ','')"
                . " AND _lfc.application_ref_no IS NOT NULL AND _lfc.application_ref_no != ''"
                . " AND DATE(_lfc.date_collected) = $lpd))",
                null, false
            );
        }

        $this->db->order_by('oa.id', 'DESC');
        $rows = $this->db->get()->result_array();

        // Collect reference numbers for bulk fee lookups
        $refs = array();
        foreach ($rows as $r) {
            if (!empty($r['reference_no'])) {
                $refs[] = preg_replace('/\s+/', '', (string) $r['reference_no']);
            }
        }
        $refs = array_values(array_unique($refs));

        $paid_amount_map = $this->onlinestudent_model->get_incidental_paid_amount_by_application_refs($refs);
        $app_fee_paid    = $this->onlinestudent_model->get_application_fee_paid_refs($refs);

        // --- Build Excel ---
        $this->load->library('excel');
        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle('Online Admissions');

        $headers = ['#', 'Reference No', 'Student Name', 'Course'];
        if ($sch_setting->father_name) {
            $headers[] = 'Father Name';
        }
        $headers[] = 'Application Date';
        $headers[] = 'Submitted By';
        $headers[] = 'Gender';
        $headers[] = 'Quota Type';
        $headers[] = 'Course Fee';
        $headers[] = 'Paid Amount';
        if ($sch_setting->mobile_no) {
            $headers[] = 'Mobile';
        }
        $headers[] = 'Form Status';
        $headers[] = 'Course Fee Status';
        if ($sch_setting->online_admission_payment == 'yes') {
            $headers[] = 'App. Fee';
        }

        $sheet->fromArray($headers, null, 'A1');

        $row_num  = 2;
        $date_fmt = $this->customlib->getSchoolDateFormat();
        $serial   = 1;

        foreach ($rows as $r) {
            $ref_clean   = !empty($r['reference_no']) ? preg_replace('/\s+/', '', (string) $r['reference_no']) : '';
            $course_fee  = ($r['course_fee_total'] !== null && $r['course_fee_total'] !== '') ? (float) $r['course_fee_total'] : 0;
            $paid_amount = isset($paid_amount_map[$ref_clean]) ? (float) $paid_amount_map[$ref_clean] : 0;
            // App fee is paid if: manually recorded via incidental_fee_collections
            // OR paid via online gateway (paid_status=1 on online_admissions row)
            $app_is_paid = !empty($app_fee_paid[$ref_clean])
                || (int) ($r['paid_status'] ?? 0) === 1;

            $middle    = ($sch_setting->middlename && !empty($r['middlename'])) ? $r['middlename'] . ' ' : '';
            $last      = ($sch_setting->lastname   && !empty($r['lastname']))   ? $r['lastname'] : '';
            $full_name = trim($r['firstname'] . ' ' . $middle . $last);

            if (!empty($r['created_at'])) {
                $app_date = date($date_fmt, strtotime($r['created_at']));
            } else {
                $app_date = '';
            }

            // Form Status (app fee paid?)
            $form_status_text = $app_is_paid ? 'Paid' : 'Not Paid';

            // Course Fee Status
            if ($paid_amount <= 0 && $app_is_paid) {
                $fee_status_text = 'Applied';
            } elseif ($paid_amount <= 0) {
                $fee_status_text = 'Not Paid';
            } elseif ($course_fee > 0 && $paid_amount >= $course_fee) {
                $fee_status_text = 'Fully Paid';
            } else {
                $fee_status_text = 'Partially Paid';
            }

            $cell_data = [
                $serial++,
                $r['reference_no'],
                $full_name,
                $r['course_name'],
            ];
            if ($sch_setting->father_name) {
                $cell_data[] = $r['father_name'];
            }
            $cell_data[] = $app_date;
            if (!empty($r['referred_by_employee_id'])) {
                $submitted_by_name = trim((string) ($r['submitted_by_name'] ?? ''));
                $cell_data[] = $submitted_by_name !== '' ? $submitted_by_name : 'Staff';
            } else {
                $cell_data[] = 'Student';
            }
            $cell_data[] = $r['gender'];
            $cell_data[] = !empty($r['quota_type']) ? $r['quota_type'] : 'N/A';
            $cell_data[] = number_format($course_fee, 2, '.', '');
            $cell_data[] = number_format($paid_amount, 2, '.', '');
            if ($sch_setting->mobile_no) {
                $cell_data[] = $r['mobileno'];
            }
            $cell_data[] = $form_status_text;
            $cell_data[] = $fee_status_text;
            if ($sch_setting->online_admission_payment == 'yes') {
                if ($app_is_paid) {
                    $cell_data[] = 'Paid';
                } elseif ($r['paid_status'] == 2) {
                    $cell_data[] = 'Processing';
                } else {
                    $cell_data[] = 'Unpaid';
                }
            }

            $sheet->fromArray($cell_data, null, 'A' . $row_num);
            $row_num++;
        }

        $filename = 'Online_Admissions_' . date('Y-m-d_H-i-s') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function addpayment()
    {
        if (!$this->rbac->hasPrivilege('online_admission_manual_payment', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('online_admission_id', $this->lang->line('student'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('transaction_id', $this->lang->line('transaction_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('note', $this->lang->line('note'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'online_admission_id' => form_error('online_admission_id'),
                'transaction_id'      => form_error('transaction_id'),
                'note'                => form_error('note'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $admin_session = $this->session->userdata('admin');
            $updated_by_staff_id = $admin_session['id'];
            $online_admission_id = $this->input->post('online_admission_id');
            $transaction_id = $this->input->post('transaction_id');
            $note = $this->input->post('note');
            $payment_updated_at = date('Y-m-d H:i:s');

            // Use the new simple update method for payment status
            $update_result = $this->onlinestudent_model->update_payment_status(
                $online_admission_id,
                $transaction_id,
                $note,
                $updated_by_staff_id,
                $payment_updated_at
            );

            if ($update_result) {
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            } else {
                $array = array('status' => 'fail', 'error' => array('Payment update failed'), 'message' => 'Failed to update payment status');
            }
            echo json_encode($array);
        }
    }

    public function checkpaymentstatus()
    {
        $id          = $_REQUEST['id'];
        $sch_setting = $this->sch_setting_detail;
        $and         = "";
        $result      = $this->onlinestudent_model->checkpaymentstatus($id);

        if ($result['form_status'] != 1 && $sch_setting->online_admission_payment == 'yes' && $result['paid_status'] == 0) {

            $message = $this->lang->line('form_status') . "         : " . $this->lang->line('not_submitted') . " \n" . $this->lang->line('payment_status') . "    : " . $this->lang->line('unpaid') . " \n \n" . $this->lang->line('do_you_still_want_to_enroll_it') . " ";

        } else if ($result['form_status'] != 1 && $sch_setting->online_admission_payment == 'no') {

            $message = $this->lang->line('form_status') . "         : " . $this->lang->line('not_submitted') . " \n \n " . $this->lang->line('do_you_still_want_to_enroll_it') . " ";

        } else if ($result['form_status'] == 1 && $sch_setting->online_admission_payment == 'yes' && $result['paid_status'] == 0) {

            $message = $this->lang->line('payment_status') . "   : " . $this->lang->line('unpaid') . " \n \n " . $this->lang->line('do_you_still_want_to_enroll_it') . " ";
        } else {
            $message = "";
        }

        echo $message;
    }

    /**
     * Preview an application by reference number.  Sets the "validlogin" session
     * value and redirects to the front‑end review page.  Admin users already
     * bypass the review check so no special privilege is required.
     */
    public function preview($reference_no = null)
    {
        if (empty($reference_no) || !$this->onlinestudent_model->checkreferenceno($reference_no)) {
            show_404();
        }

        // allow anyone (admin or public) to view by temporarily marking validlogin
        $this->session->set_userdata('validlogin', $reference_no);

        redirect('welcome/online_admission_review/' . $reference_no);
    }

    public function fee_receipt()
    {
        $this->output->set_content_type('application/json');
        if (!$this->rbac->hasPrivilege('online_admission', 'can_view')) {
            echo json_encode(['status' => 'fail', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $ref_no = $this->input->post('ref_no');
        if (empty($ref_no)) {
            echo json_encode(['status' => 'fail', 'message' => 'Invalid reference number.']);
            return;
        }

        $ref_no_clean = preg_replace('/\s+/', '', $ref_no);

        // Fetch applicant details
        $this->db->select('online_admissions.firstname, online_admissions.lastname, online_admissions.middlename,
            online_admissions.reference_no, online_admissions.email, online_admissions.mobileno,
            online_admissions.course_fee_total, online_admissions.quota_type,
            IFNULL(online_admission_courses.course_name, "N/A") as course_name,
            COALESCE(online_admissions.course_fee_total,
                IF(online_admissions.quota_type = "management", online_admission_courses.mgt_fee, online_admission_courses.govt_fee)
            ) as total_fee');
        $this->db->from('online_admissions');
        $this->db->join('online_admission_courses',
            'online_admission_courses.id = COALESCE(online_admissions.admission_course_id, online_admissions.ug_course_id)', 'left');
        $this->db->where("REPLACE(online_admissions.reference_no, ' ', '') = " . $this->db->escape($ref_no_clean), null, false);
        $student = $this->db->get()->row_array();

        if (!$student) {
            echo json_encode(['status' => 'fail', 'message' => 'Applicant not found.']);
            return;
        }

        // Fetch payment history
        $payments = $this->onlinestudent_model->get_payment_history_by_ref($ref_no_clean, $this->customlib->getSchoolDateFormat());

        $total_paid = array_sum(array_column($payments, 'amount_raw'));
        $total_fee  = (float) ($student['total_fee'] ?? 0);
        $balance    = $total_fee - $total_paid;

        $sch_setting = $this->sch_setting_detail;
        $date_fmt    = $this->customlib->getSchoolDateFormat();

        // Fetch online admission receipt header image
        $header_row = $this->db->select('header_image')->from('print_headerfooter')
            ->where('print_type', 'online_admission_receipt')->get()->row_array();
        $header_image_url = '';
        if (!empty($header_row['header_image'])) {
            $header_image_url = $this->media_storage->getImageURL('uploads/print_headerfooter/online_admission_receipt/' . $header_row['header_image']);
        }

        echo json_encode([
            'status'      => 'success',
            'student'     => [
                'name'       => trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']),
                'ref_no'     => $student['reference_no'],
                'email'      => $student['email'],
                'mobile'     => $student['mobileno'],
                'course'     => $student['course_name'],
                'quota_type' => $student['quota_type'],
            ],
            'total_fee'     => number_format($total_fee, 2),
            'total_paid'    => number_format($total_paid, 2),
            'balance'       => number_format($balance, 2),
            'payments'      => $payments,
            'school_name'   => $sch_setting->school_name,
            'school_phone'  => $sch_setting->phone,
            'school_email'  => $sch_setting->email,
            'header_image'  => $header_image_url,
        ]);
    }

    public function handle_upload($str, $var)
    {
        // $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {

            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES[$var]['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            return true;
        }
        return true;
    }

    /**
     * Edit application form details only (not enrollment)
     */
    public function edit_application($id = null)
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_edit')) {
            access_denied();
        }

        if (is_null($id)) {
            $this->show_404();
            return;
        }

        $student = $this->onlinestudent_model->get($id);
        if (!$student) {
            $this->show_404();
            return;
        }

        // Load related data
        $this->load->model('Online_admission_ug_details_model');
        $this->load->model('Online_admission_references_model');
        $ug_details = $this->Online_admission_ug_details_model->get_by_online_admission_id($id);
        $reference_details = $this->Online_admission_references_model->get_by_online_admission_id($id);
        $this->load->model('Onlineadmissioncourses_model');

        $selected_course_id = !empty($student['admission_course_id']) ? (int)$student['admission_course_id'] : (!empty($student['ug_course_id']) ? (int)$student['ug_course_id'] : null);
        $course_applied = 'N/A';
        if (!empty($selected_course_id)) {
            $course = $this->Onlineadmissioncourses_model->getById($selected_course_id);
            if (is_array($course) && !empty($course['course_name'])) {
                $course_applied = $course['course_name'];
            } elseif (is_object($course) && !empty($course->course_name)) {
                $course_applied = $course->course_name;
            }
        }
        $all_courses = array_merge(
            $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year'),
            $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral'),
            $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year')
        );

        // Set validation rules for essential fields only
        $this->form_validation->set_rules('user_name', 'Name', 'trim|xss_clean|min_length[3]');
        $this->form_validation->set_rules('student_email', 'Email', 'trim|xss_clean|valid_email');
        $this->form_validation->set_rules('student_mobile', 'Student Mobile', 'trim|xss_clean|exact_length[10]|numeric');
        $this->form_validation->set_rules('community', 'Community', 'trim|xss_clean');
        $this->form_validation->set_rules('tenth_passing', 'Year of Passing (X Std)', 'trim|xss_clean');
        $this->form_validation->set_rules('tenth_marks_percentage', 'X marks (in %)', 'trim|xss_clean');
        $this->form_validation->set_rules('applicant_photo', 'Applicant Photo', 'callback_validate_applicant_photo');

        if ($this->form_validation->run() == false) {
            // Show edit form with validation errors
            $data['student'] = $student;
            $data['id'] = $id;
            $data['ug_details'] = $ug_details;
            $data['reference_details'] = $reference_details;
            $data['course_applied'] = $course_applied;
            $data['selected_course_id'] = $selected_course_id;
            $data['all_courses'] = $all_courses;
            $data['title'] = 'Edit Application';
            
            $this->session->set_userdata('top_menu', 'Student Information');
            $this->session->set_userdata('sub_menu', 'onlinestudent');

            $this->load->view('layout/header', $data);
            $this->load->view('admin/onlinestudent/edit_application', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // Prepare update data for online_admissions table
            $update_data = array(
                'id' => $id,
                'firstname' => htmlspecialchars($this->input->post('user_name')),
                'gender' => $this->input->post('gender'),
                'cast' => $this->input->post('community'),
                'email' => $this->input->post('student_email'),
                'mobileno' => $this->input->post('student_mobile'),
                'dob' => $this->input->post('dob'),
                'adhar_no' => $this->input->post('aadhaar'),
                'current_address' => htmlspecialchars($this->input->post('current_address')),
                'permanent_address' => htmlspecialchars($this->input->post('permanent_address')),
                'state' => htmlspecialchars($this->input->post('state')),
                'city' => htmlspecialchars($this->input->post('city')),
                'father_name' => htmlspecialchars($this->input->post('father_name')),
                'father_phone' => $this->input->post('father_mobile'),
                'father_occupation' => htmlspecialchars($this->input->post('father_occupation')),
                'mother_name' => htmlspecialchars($this->input->post('mother_name')),
                'mother_phone' => $this->input->post('mother_mobile'),
                'mother_occupation' => htmlspecialchars($this->input->post('mother_occupation')),
                'total_maths' => $this->input->post('total_maths'),
                'maths_marks' => $this->input->post('maths_marks'),
                'maths_perc' => $this->input->post('maths_perc'),
                'total_physics' => $this->input->post('total_physics'),
                'physics_marks' => $this->input->post('physics_marks'),
                'physics_perc' => $this->input->post('physics_perc'),
                'total_chemistry' => $this->input->post('total_chemistry'),
                'chemistry_marks' => $this->input->post('chemistry_marks'),
                'chemistry_perc' => $this->input->post('chemistry_perc'),
                'average_marks' => $this->input->post('average_marks'),
                'cutoff_marks' => $this->input->post('cutoff_marks'),
                'school_name_x' => htmlspecialchars($this->input->post('school_name')),
                'passing_year_x' => $this->input->post('tenth_passing'),
                'tenth_marks_percentage' => $this->input->post('tenth_marks_percentage'),
                'updated_at' => date('Y-m-d H:i:s'),
                'admission_course_id' => (int)$this->input->post('admission_course_id') ?: null,
                'quota_type' => $this->input->post('quota_type') ?: null,
            );

            // Recalculate course_fee_total when course or quota changes
            $new_course_id = (int)$this->input->post('admission_course_id');
            if ($new_course_id > 0) {
                $new_course = $this->Onlineadmissioncourses_model->getById($new_course_id);
                if (!empty($new_course)) {
                    $quota = $this->input->post('quota_type') ?: (isset($student['quota_type']) ? $student['quota_type'] : '');
                    if ($quota === 'management' && isset($new_course['mgt_fee'])) {
                        $update_data['course_fee_total'] = (float)$new_course['mgt_fee'];
                    } elseif ($quota === 'government' && isset($new_course['govt_fee'])) {
                        $update_data['course_fee_total'] = (float)$new_course['govt_fee'];
                    }
                }
            }

            // Handle applicant photo upload
            if (isset($_FILES['applicant_photo']) && !empty($_FILES['applicant_photo']['name'])) {
                $upload_result = $this->media_storage->fileupload('applicant_photo', './uploads/student_images/online_admission_image/');
                if ($upload_result['status']) {
                    $update_data['image'] = 'uploads/student_images/online_admission_image/' . $upload_result['message'];
                }
            }

            // Update online_admissions table
            $this->onlinestudent_model->edit($update_data);

            // Update UG details if exists, otherwise create a new row
            $school_name = htmlspecialchars($this->input->post('school_name'));
            $tenth_passing = $this->input->post('tenth_passing');

            if ($ug_details) {
                $ug_data = array(
                    'id' => $ug_details['id'],
                    'school_name' => $school_name,
                    'tenth_passing' => $tenth_passing,
                );
                $this->db->where('id', $ug_data['id']);
                $this->db->update('online_admission_ug_details', $ug_data);
            } elseif ($school_name !== '' || $tenth_passing !== '') {
                $ug_data = array(
                    'online_admission_id' => $id,
                    'school_name' => $school_name,
                    'tenth_passing' => $tenth_passing,
                );
                $this->db->insert('online_admission_ug_details', $ug_data);
            }

            // Update reference details if provided
            if ($this->input->post('referral_name') || $this->input->post('relationship') || $this->input->post('phone_no')) {
                if ($reference_details) {
                    $ref_data = array(
                        'id' => $reference_details['id'],
                        'referrer_name' => htmlspecialchars($this->input->post('referral_name')),
                        'relationship' => htmlspecialchars($this->input->post('relationship')),
                        'phone_no' => $this->input->post('phone_no'),
                    );
                    $this->db->where('id', $ref_data['id']);
                    $this->db->update('online_admission_references', $ref_data);
                } else {
                    // Create new reference record
                    $ref_data = array(
                        'online_admission_id' => $id,
                        'referrer_name' => htmlspecialchars($this->input->post('referral_name')),
                        'relationship' => htmlspecialchars($this->input->post('relationship')),
                        'phone_no' => $this->input->post('phone_no'),
                    );
                    $this->db->insert('online_admission_references', $ref_data);
                }
            }
            
            // Log the action
            $message = "Application details updated for online admission id " . $id;
            $action = "Update";
            $this->onlinestudent_model->log($message, $id, $action);

            // Set success message and redirect
            $this->session->set_userdata('msg', '<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="fa fa-check-circle"></i> Application updated successfully!</div>');
            redirect('admin/onlinestudent');
        }
    }

    public function validate_applicant_photo($str)
    {
        if (!isset($_FILES['applicant_photo']) || empty($_FILES['applicant_photo']['name'])) {
            return true;
        }
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $file_ext           = strtolower(pathinfo($_FILES['applicant_photo']['name'], PATHINFO_EXTENSION));
        $finfo              = finfo_open(FILEINFO_MIME_TYPE);
        $file_mime          = finfo_file($finfo, $_FILES['applicant_photo']['tmp_name']);
        $allowed_mimes      = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
        if (!in_array($file_mime, $allowed_mimes) || !in_array($file_ext, $allowed_extensions)) {
            $this->form_validation->set_message('validate_applicant_photo', 'Only image files (JPG, PNG, GIF, BMP) are allowed.');
            return false;
        }
        if ($_FILES['applicant_photo']['size'] > 300 * 1024) {
            $this->form_validation->set_message('validate_applicant_photo', 'Photo must be less than 300 KB.');
            return false;
        }
        return true;
    }

}
