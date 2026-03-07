<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Template extends MY_Addon_CBSEController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('SaasValidation');
        $this->load->library('media_storage');
    }

    public function validateCanUploadFile($str, $params_string)
    {
        $params_array = array_map('trim', explode(',', $params_string));
        return $this->saasvalidation->validateCanUploadFile($str, $params_array);
    }

    private function getUploadFileName($upload_result)
    {
        if (is_array($upload_result)) {
            if (!empty($upload_result['status']) && isset($upload_result['message']) && is_string($upload_result['message'])) {
                return $upload_result['message'];
            }
            $message = isset($upload_result['message']) ? (string) $upload_result['message'] : $this->lang->line('error_occurred_please_try_again');
            throw new Exception($message);
        }

        if (is_string($upload_result)) {
            return $upload_result;
        }

        throw new Exception($this->lang->line('error_occurred_please_try_again'));
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('cbse_exam_template', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'cbse_exam');
        $this->session->set_userdata('sub_menu', 'cbse_exam/template');
        $data['title'] = 'Category List';
        $category_result = $this->category_model->get();
        $data['categorylist'] = $category_result;
        $subjectgroupList = $this->subjectgroup_model->getByID();
        $data['subjectgroupList'] = $subjectgroupList;
        $data['result'] = $this->cbseexam_template_model->gettemplatelist();
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $data['marksheet'] = $this->cbseexam_result_model->marksheet_type();
        $this->load->view('layout/header', $data);
        $this->load->view('cbseexam/template/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('cbse_exam_template', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('name', $this->lang->line('template'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('left_logo', $this->lang->line('left_logo'), 'callback_handle_upload[left_logo]');
        $this->form_validation->set_rules('right_logo', $this->lang->line('right_logo'), 'callback_handle_upload[right_logo]');
        $this->form_validation->set_rules('background_img', $this->lang->line('sign_image'), 'callback_handle_upload[background_img]');
        $this->form_validation->set_rules('left_sign', $this->lang->line('left_sign'), 'callback_handle_upload[left_sign]');
        $this->form_validation->set_rules('middle_sign', $this->lang->line('middle_sign'), 'callback_handle_upload[middle_sign]');
        $this->form_validation->set_rules('right_sign', $this->lang->line('right_sign'), 'callback_handle_upload[right_sign]');

        $storage_array = "header_image,left_logo,right_logo,background_img,left_sign,middle_sign,right_sign"; // use comma for multiple files  
        $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");      
 
        if ($this->form_validation->run() == false) {

            $msg['name'] = form_error('name');
            $msg['class_id'] = form_error('class_id');
            $msg['left_logo'] = form_error('left_logo');
            $msg['right_logo'] = form_error('right_logo');
            $msg['background_img'] = form_error('background_img');
            $msg['left_sign'] = form_error('left_sign');
            $msg['middle_sign'] = form_error('middle_sign');
            $msg['right_sign'] = form_error('right_sign');

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
         try {
            if (isset($_POST['is_name'])) {
                $is_name = 1;
            } else {
                $is_name = 0;
            }
            if (isset($_POST['is_father_name'])) {
                $is_father_name = 1;
            } else {
                $is_father_name = 0;
            }
            if (isset($_POST['is_mother_name'])) {
                $is_mother_name = 1;
            } else {
                $is_mother_name = 0;
            }
            if (isset($_POST['is_admission_no'])) {
                $is_admission_no = 1;
            } else {
                $is_admission_no = 0;
            }
            if (isset($_POST['exam_session'])) {
                $exam_session = 1;
            } else {
                $exam_session = 0;
            }
            if (isset($_POST['is_roll_no'])) {
                $is_roll_no = 1;
            } else {
                $is_roll_no = 0;
            }
            if (isset($_POST['is_photo'])) {
                $is_photo = 1;
            } else {
                $is_photo = 0;
            }
            if (isset($_POST['is_division'])) {
                $is_division = 1;
            } else {
                $is_division = 0;
            }
            if (isset($_POST['is_class'])) {
                $is_class = 1;
            } else {
                $is_class = 0;
            }
            if (isset($_POST['is_section'])) {
                $is_section = 1;
            } else {
                $is_section = 0;
            }
            if (isset($_POST['is_dob'])) {
                $is_dob = 1;
            } else {
                $is_dob = 0;
            }
            if (isset($_POST['remark'])) {
                $is_remark = 1;
            } else {
                $is_remark = 0;
            }
            if (isset($_POST['subject_note'])) {
                $is_subject_note = 1;
            } else {
                $is_subject_note = 0;
            }

            $data = array(

                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'created_by' => $this->customlib->getStaffID(),
                'school_name' => $this->input->post('school_name'),
                'exam_center' => $this->input->post('exam_center'),
                'date' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('date')),
                'is_name' => $is_name,
                'is_father_name' => $is_father_name,
                'is_mother_name' => $is_mother_name,
                'is_admission_no' => $is_admission_no,
                'is_roll_no' => $is_roll_no,
                'is_photo' => $is_photo,
                'is_class' => $is_class,
                'is_division' => $is_division,
                'is_section' => $is_section,
                'is_dob' => $is_dob,
                'is_remark' => $is_remark,
                'is_subject_note' => $is_subject_note,
                'session_id' => $this->setting_model->getCurrentSession(),
                'content' => $this->input->post('content'),
                'content_footer' => $this->input->post('content_footer'),
                'exam_session' => $exam_session,
                'orientation' => $this->input->post('orientation'),
            );

            $total_documents_failed_size = 0;
            $storage_array = ["header_image","left_logo","right_logo","background_img","left_sign","middle_sign","right_sign"];
            $this->saasvalidation->updateStorageLimit('storage', $storage_array); // update resource quota initially 

            if (isset($_FILES["header_image"]) && !empty($_FILES["header_image"]['name'])) {
                
                $upload_result = $this->media_storage->fileupload("header_image", "./uploads/cbseexam/template/header_image/");
                $img_name = $this->getUploadFileName($upload_result);
                $data['header_image'] = $img_name;
                if (IsNullOrEmptyString($img_name)) {  // check upload image has not uploaded successfully
                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('header_image');  // get temp size of image because of image not uploaded 
                }
            }
            if (isset($_FILES["left_sign"]) && !empty($_FILES["left_sign"]['name'])) {
                $upload_result = $this->media_storage->fileupload("left_sign", "./uploads/cbseexam/template/left_sign/");
                $img_name = $this->getUploadFileName($upload_result);
                $data['left_sign'] = $img_name;
                if (IsNullOrEmptyString($img_name)) {  // check upload image has not uploaded successfully
                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('left_sign');  // get temp size of image because of image not uploaded 
                }
            }
            if (isset($_FILES["middle_sign"]) && !empty($_FILES["middle_sign"]['name'])) {
               
                $upload_result = $this->media_storage->fileupload("middle_sign", "./uploads/cbseexam/template/middle_sign/");
                $img_name = $this->getUploadFileName($upload_result);
                $data['middle_sign'] = $img_name;
                if (IsNullOrEmptyString($img_name)) {  // check upload image has not uploaded successfully
                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('middle_sign');  // get temp size of image because of image not uploaded 
                }
            }
            if (isset($_FILES["right_sign"]) && !empty($_FILES["right_sign"]['name'])) {

                $upload_result = $this->media_storage->fileupload("right_sign", "./uploads/cbseexam/template/right_sign/");
                $img_name = $this->getUploadFileName($upload_result);
                $data['right_sign'] = $img_name;
                if (IsNullOrEmptyString($img_name)) {  // check upload image has not uploaded successfully
                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('right_sign');  // get temp size of image because of image not uploaded 
                }
            }
            if (isset($_FILES["background_img"]) && !empty($_FILES["background_img"]['name'])) {

                $upload_result = $this->media_storage->fileupload("background_img", "./uploads/cbseexam/template/background_img/");
                $img_name = $this->getUploadFileName($upload_result);
                $data['background_img'] = $img_name;
                if (IsNullOrEmptyString($img_name)) {  // check upload image has not uploaded successfully
                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('background_img');  // get temp size of image because of image not uploaded 
                }
            }

            if ($total_documents_failed_size > 0) {
                 $this->saasvalidation->deleteResouceQuota('storage', $total_documents_failed_size);
            }   

            if (!empty($_POST['section'])) {
                $template_id = $this->cbseexam_template_model->add($data);
                foreach ($_POST['section'] as $key => $value) {
                    $template_class_section = array(
                        'cbse_template_id' => $template_id,
                        'class_section_id' => $value
                    );
                    $this->cbseexam_template_model->add_class_section($template_class_section);
                }
                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            } else {
                $msg['section'] = $this->lang->line('please_select_atleast_one_section');
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }

         } catch (Exception $e) {
            $array = array('status' => 'fail', 'error' =>  $e->getMessage(), 'message' => '');
         }  
        }
        echo json_encode($array);
    }

    public function edit()
    {
        if (!$this->rbac->hasPrivilege('cbse_exam_template', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('name', $this->lang->line('template'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('left_logo', $this->lang->line('left_logo'), 'callback_handle_upload[left_logo]');
        $this->form_validation->set_rules('right_logo', $this->lang->line('right_logo'), 'callback_handle_upload[right_logo]');
        $this->form_validation->set_rules('background_img', $this->lang->line('background_image'), 'callback_handle_upload[background_img]');
        $this->form_validation->set_rules('left_sign', $this->lang->line('left_sign'), 'callback_handle_upload[left_sign]');
        $this->form_validation->set_rules('middle_sign', $this->lang->line('middle_sign'), 'callback_handle_upload[middle_sign]');
        $this->form_validation->set_rules('right_sign', $this->lang->line('right_sign'), 'callback_handle_upload[right_sign]');
        
        $storage_array = "header_image,left_logo,right_logo,background_img,left_sign,middle_sign,right_sign"; // use comma for multiple files  
        $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");     

        if ($this->form_validation->run() == false) {
            $msg['name'] = form_error('name');
            $msg['class_id'] = form_error('class_id');
            $msg['left_logo'] = form_error('left_logo');
            $msg['right_logo'] = form_error('right_logo');
            $msg['background_img'] = form_error('background_img');
            $msg['left_sign'] = form_error('left_sign');
            $msg['middle_sign'] = form_error('middle_sign');
            $msg['right_sign'] = form_error('right_sign');

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
        try {
            if (isset($_POST['stu_name'])) {
                $is_name = 1;
            } else {
                $is_name = 0;
            }
            if (isset($_POST['father_name'])) {
                $is_father_name = 1;
            } else {
                $is_father_name = 0;
            }
            if (isset($_POST['mother_name'])) {
                $is_mother_name = 1;
            } else {
                $is_mother_name = 0;
            }
            if (isset($_POST['admission_no'])) {
                $is_admission_no = 1;
            } else {
                $is_admission_no = 0;
            }
            if (isset($_POST['examsession'])) {
                $exam_session = 1;
            } else {
                $exam_session = 0;
            }
            if (isset($_POST['roll_no'])) {
                $is_roll_no = 1;
            } else {
                $is_roll_no = 0;
            }
            if (isset($_POST['division'])) {
                $is_division = 1;
            } else {
                $is_division = 0;
            }
            if (isset($_POST['photo'])) {
                $is_photo = 1;
            } else {
                $is_photo = 0;
            }
            if (isset($_POST['class'])) {
                $is_class = 1;
            } else {
                $is_class = 0;
            }
            if (isset($_POST['is_section'])) {
                $is_section = 1;
            } else {
                $is_section = 0;
            }
            if (isset($_POST['date_of_birth'])) {
                $is_dob = 1;
            } else {
                $is_dob = 0;
            }
            if (isset($_POST['is_remark'])) {
                $is_remark = 1;
            } else {
                $is_remark = 0;
            }
            if (isset($_POST['is_subject_note'])) {
                $is_subject_note = 1;
            } else {
                $is_subject_note = 0;
            }

            $data = array(
                'id' => $this->input->post('templateid'),
                'name' => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'created_by' => $this->customlib->getStaffID(),
                'school_name' => $this->input->post('school_name'),
                'exam_center' => $this->input->post('exam_center'),
                'date' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('date')),
                'is_name' => $is_name,
                'is_father_name' => $is_father_name,
                'is_division' => $is_division,
                'is_mother_name' => $is_mother_name,
                'is_admission_no' => $is_admission_no,
                'is_roll_no' => $is_roll_no,
                'is_photo' => $is_photo,
                'is_class' => $is_class,
                'is_section' => $is_section,
                'is_dob' => $is_dob,
                'is_remark' => $is_remark,
                'is_subject_note' => $is_subject_note,
                'content' => $this->input->post('content'),
                'content_footer' => $this->input->post('content_footer'),
                'exam_session' => $exam_session,
                'orientation' => $this->input->post('orientation'),
            );

            $result = $this->cbseexam_template_model->get($this->input->post('templateid'));
            $prev_file_size = 0;
            $total_image_upload_size = 0;

            if (isset($_FILES["header_image"]) && !empty($_FILES["header_image"]['name'])) {
                $prev_file_size += $this->media_storage->getUploadedFileSize($result['header_image'],'uploads/cbseexam/template/header_image');
                $upload_result = $this->media_storage->fileupload("header_image", "./uploads/cbseexam/template/header_image/");
                $header_image = $this->getUploadFileName($upload_result);
                $insert_data['header_image'] = $header_image;
                if (!IsNullOrEmptyString($header_image)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('header_image');
                }
            } else {
                $data['header_image'] = $result['header_image'];
            }

            if (isset($_FILES["left_logo"]) && !empty($_FILES["left_logo"]['name'])) {

                $prev_file_size += $this->media_storage->getUploadedFileSize($result['left_logo'],'uploads/cbseexam/template/left_logo');
                $upload_result = $this->media_storage->fileupload("left_logo", "./uploads/cbseexam/template/left_logo/");
                $left_logo = $this->getUploadFileName($upload_result);
                $insert_data['left_logo'] = $left_logo;
                if (!IsNullOrEmptyString($left_logo)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('left_logo');
                }
            } else {
                $data['left_logo'] = $result['left_logo'];
            }
            
            if (isset($_FILES["right_logo"]) && !empty($_FILES["right_logo"]['name'])) {
                $prev_file_size += $this->media_storage->getUploadedFileSize($result['right_logo'],'uploads/cbseexam/template/right_logo');
                $upload_result = $this->media_storage->fileupload("right_logo", "./uploads/cbseexam/template/right_logo/");
                $right_logo = $this->getUploadFileName($upload_result);
                $insert_data['right_logo'] = $right_logo;
                if (!IsNullOrEmptyString($right_logo)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('right_logo');
                }
            } else {
                $data['right_logo'] = $result['right_logo'];
            }
            
            if (isset($_FILES["left_sign"]) && !empty($_FILES["left_sign"]['name'])) {
                $prev_file_size += $this->media_storage->getUploadedFileSize($result['left_sign'],'uploads/cbseexam/template/left_sign');
                $upload_result = $this->media_storage->fileupload("left_sign", "./uploads/cbseexam/template/left_sign/");
                $left_sign = $this->getUploadFileName($upload_result);
                $insert_data['left_sign'] = $left_sign;
                if (!IsNullOrEmptyString($left_sign)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('left_sign');
                }
            } else {
                $data['left_sign'] = $result['left_sign'];
            }
            
            if (isset($_FILES["middle_sign"]) && !empty($_FILES["middle_sign"]['name'])) {
                $prev_file_size += $this->media_storage->getUploadedFileSize($result['middle_sign'],'uploads/cbseexam/template/middle_sign');
                $upload_result = $this->media_storage->fileupload("middle_sign", "./uploads/cbseexam/template/middle_sign/");
                $middle_sign = $this->getUploadFileName($upload_result);
                $insert_data['middle_sign'] = $middle_sign;
                if (!IsNullOrEmptyString($middle_sign)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('middle_sign');
                }
            } else {
                $data['middle_sign'] = $result['middle_sign'];
            }
            
            if (isset($_FILES["right_sign"]) && !empty($_FILES["right_sign"]['name'])) {
                $prev_file_size += $this->media_storage->getUploadedFileSize($result['right_sign'],'uploads/cbseexam/template/right_sign');
                $upload_result = $this->media_storage->fileupload("right_sign", "./uploads/cbseexam/template/right_sign/");
                $right_sign = $this->getUploadFileName($upload_result);
                $insert_data['right_sign'] = $right_sign;
                if (!IsNullOrEmptyString($right_sign)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('right_sign');
                }
            } else {
                $data['right_sign'] = $result['right_sign'];
            }
            
            if (isset($_FILES["background_img"]) && !empty($_FILES["background_img"]['name'])) {
                $prev_file_size += $this->media_storage->getUploadedFileSize($result['background_img'],'uploads/cbseexam/template/background_img');
                $upload_result = $this->media_storage->fileupload("background_img", "./uploads/cbseexam/template/background_img/");
                $background_img = $this->getUploadFileName($upload_result);
                $insert_data['background_img'] = $background_img;
                if (!IsNullOrEmptyString($background_img)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('background_img');
                }
            } else {
                $data['background_img'] = $result['background_img'];
            }
            
            //===========
            if ($prev_file_size > $total_image_upload_size) {
                // Previous file was larger 
                $size_difference = $prev_file_size - $total_image_upload_size;
                $this->saasvalidation->deleteResouceQuota('storage', $size_difference);
            } elseif ($prev_file_size < $total_image_upload_size) {
                // New file is larger 
                $size_difference = $total_image_upload_size - $prev_file_size;
                $this->saasvalidation->updateResouceQuota('storage', $size_difference);
            } else {
                // File size unchanged → no quota adjustment needed 
            }
            //===========

            if (!empty($_POST['section'])) {
                $this->cbseexam_template_model->add($data);
                $this->cbseexam_template_model->deleteclasssectionbytemplateid($this->input->post('templateid'));

                foreach ($_POST['section'] as $key => $value) {

                    $template_class_section = array(
                        'cbse_template_id' => $this->input->post('templateid'),
                        'class_section_id' => $value
                    );
                    $this->cbseexam_template_model->add_class_section($template_class_section);
                }

                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('record_updated_successfully'));
            } else {
                $msg['section'] = $this->lang->line('please_select_atleast_one_section');
                $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
            }
         } catch (Exception $e) {
            $array = array('status' => 'fail', 'error' =>  $e->getMessage(), 'message' => '');
         } 
        }
         
        echo json_encode($array);
    }

    public function getdata()
    {
        $template_id = $this->input->post('template_id');
        $result = $this->cbseexam_template_model->get($template_id);
        $data['classlist'] = $this->class_model->get();
        $data['result'] = $result;
        $data['sections'] = $this->cbseexam_template_model->getclasssection($template_id);
        $data['selected_class_id'] = $data['sections'][0]['class_id'];
        $data['selected_section_id'] = json_encode($data['sections']);
        $page = $this->load->view('cbseexam/template/edit', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function viewtemplate()
    {
        $template_id = $this->input->post('template_id');
        $result = $this->cbseexam_template_model->get($template_id);
        $data = [];
        $data['template'] = $result;
        $page = $this->load->view('cbseexam/template/viewtemplate', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function get_ClassSectionByTermId($termid)
    {
        $result = $this->cbseexam_term_model->gettermbyid($termid);
        $data['class_id'] = $result[0]['class_id'];
        $data['sections'] = $this->section_model->getClassBySection($result[0]['class_id']);
        echo json_encode($data);
    }

    public function remove()
    {
        if (!$this->rbac->hasPrivilege('cbse_exam_template', 'can_delete')) {
            access_denied();
        }

        $templateid = $this->input->post('templateid');
		
		$result = $this->cbseexam_template_model->get($templateid);	
		$delete_file_size = 0;
		
		// if ($result['background_img'] != '') {
			// $delete_file_size += $this->media_storage->getUploadedFileSize($result['background_img'], 'uploads/cbseexam/template/background_img');
																											 
            // $this->media_storage->filedelete($result['background_img'], "uploads/cbseexam/template/background_img/");
        // }
		
		// if ($result['header_image'] != '') {
			// $delete_file_size += $this->media_storage->getUploadedFileSize($result['header_image'], 'uploads/cbseexam/template/header_image');
			 																								 
            // $this->media_storage->filedelete($result['header_image'], "uploads/cbseexam/template/header_image/");
        // }
		
		// if ($result['left_sign'] != '') {
			// $delete_file_size += $this->media_storage->getUploadedFileSize($result['left_sign'], 'uploads/cbseexam/template/left_sign');
			 																								 
            // $this->media_storage->filedelete($result['left_sign'], "uploads/cbseexam/template/left_sign/");
        // }
		
		// if ($result['middle_sign'] != '') {
			// $delete_file_size += $this->media_storage->getUploadedFileSize($result['middle_sign'], 'uploads/cbseexam/template/middle_sign');
			 																							 
            // $this->media_storage->filedelete($result['middle_sign'], "uploads/cbseexam/template/middle_sign/");
        // }
		
		// if ($result['right_sign'] != '') {
			// $delete_file_size += $this->media_storage->getUploadedFileSize($result['right_sign'], 'uploads/cbseexam/template/right_sign');
			 																								 
            // $this->media_storage->filedelete($result['right_sign'], "uploads/cbseexam/template/right_sign/");
        // }
		
		$files = [
			'background_img' => 'uploads/cbseexam/template/background_img',
			'header_image' => 'uploads/cbseexam/template/header_image',
			'left_sign'    => 'uploads/cbseexam/template/left_sign',
			'middle_sign'  => 'uploads/cbseexam/template/middle_sign',
			'right_sign'   => 'uploads/cbseexam/template/right_sign',
		];

		foreach ($files as $key => $path) {
			if (!empty($result[$key])) {
				$delete_file_size += $this->media_storage->getUploadedFileSize($result[$key], $path);
				$this->media_storage->filedelete($result[$key], $path . '/');
			}
		}

		if($delete_file_size > 0){
			$this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
		}
		
        $this->cbseexam_template_model->remove($templateid);
		
        $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('delete_message'));
        echo json_encode($array);
    }

    public function get_examdata()
    {
        $marksheet_type = $_POST['marksheet_type'];
        $template_id = $_POST['template_id'];

        $result = $this->cbseexam_template_model->all_term($template_id);
        $data['templatedata'] = $this->cbseexam_template_model->get_templatedata($template_id);

        if ($marksheet_type == 'all_term') {
            $data['result'] = $result;
            $data['examdata'] = $this->load->view('cbseexam/template/_all_term', $data, true);
        } elseif ($marksheet_type == 'term_wise') {
            $data['result'] = $result;
            $data['examdata'] = $this->load->view('cbseexam/template/_term_wise', $data, true);
        } elseif ($marksheet_type == 'without_term') {
            $data['result'] = $result;
            $data['examdata'] = $this->load->view('cbseexam/template/_without_term', $data, true);
        } elseif ($marksheet_type == 'exam_wise') {
            $data['result'] = $result;
            $data['examdata'] = $this->load->view('cbseexam/template/_exam_wise', $data, true);
        }
        echo json_encode($data);
    }
    
    public function deleteimg()
    {
        $img_type = $this->input->post('img_type');
        $template_id = $this->input->post('template_id');
        $this->form_validation->set_rules('img_type', $this->lang->line('image'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('template_id', $this->lang->line('template'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == true) {
            $this->cbseexam_template_model->delete_img(['id' => $template_id, 'img_type' => $img_type]);
            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('delete_message'));
        } else {
            $msg = array(
                'exam' => form_error('exam')
            );
            $array = array('status' => 0, 'error' => $msg, 'message' => '');
        }
        echo json_encode($array);
    }

    public function linkexams()
    {
        if (!$this->rbac->hasPrivilege('cbse_exam_link_exam', 'can_view')) {
            access_denied();
        }

        $this->form_validation->set_rules('marksheet', $this->lang->line('marksheet_type'), 'trim|required|xss_clean');
        if ($this->input->post('marksheet') == 'term_wise') {

            $this->form_validation->set_rules('teacher_remark', $this->lang->line('teacher_remark'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('grading', $this->lang->line('grading'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('terms[]', $this->lang->line('term'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('subject_note', $this->lang->line('subject_note'), 'trim|required|xss_clean');
            $selected_exams = [];
            $term_exam_weightage = "";
            if ($this->input->post('terms')) {
                foreach ($_POST['terms'] as $term_key => $term_value) {

                    if (!empty($_POST['exam'][$term_value])) {
                        $term_exam_weightage = 0;
                        foreach ($_POST['exam'][$term_value] as $exam_key => $exam_value) {
                            $selected_exams[] = $exam_value;

                            if ($_POST['weightage'][$term_value][$exam_value] != "") {

                                $term_exam_weightage = $term_exam_weightage + $_POST['weightage'][$term_value][$exam_value];
                            }
                        }
                    }
                }
            }

            $teacher_remark = $this->input->post('teacher_remark');
            if (!empty($selected_exams) && isset($teacher_remark)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('teacher_remark', $this->lang->line('teacher_remark'), "callback_check_teacher_remark[" . $selectable . "]");
            }

            $exam_grading = $this->input->post('grading');
            if (!empty($selected_exams) && isset($exam_grading)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('grading', $this->lang->line('exam_grading'), "callback_check_exam_grading[" . $selectable . "]");
            }

            $subject_note = $this->input->post('subject_note');
            if (!empty($selected_exams) && isset($subject_note)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('subject_note', $this->lang->line('subject_note'), "callback_check_exam_subject_note[" . $selectable . "]");
            }

            if ($term_exam_weightage != 100 || $term_exam_weightage != 0) {
                $this->form_validation->set_rules('max_weightage', $this->lang->line('weightage'), 'callback_check_weightage[' . $term_exam_weightage . ']');
            }
        } elseif ($this->input->post('marksheet') == 'all_term') {

            $this->form_validation->set_rules('teacher_remark', $this->lang->line('teacher_remark'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('grading', $this->lang->line('grading'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('subject_note', $this->lang->line('subject_note'), 'trim|required|xss_clean');
            
            if (!$this->input->post('terms')) {
                $this->form_validation->set_rules('terms[]', $this->lang->line('term'), 'trim|required|xss_clean');
            }

            $selected_exams = [];
            $term_exam_weightage = "";

            if ($this->input->post('terms')) {
                $term_exam_weightage = 0;
                foreach ($_POST['terms'] as $term_key => $term_value) {
                    $term_exam_weightage += (int) $this->input->post('term_weightage')[$term_value];
                    if (!empty($_POST['exam'][$term_value])) {
                        foreach ($_POST['exam'][$term_value] as $exam_key => $exam_value) {
                            $selected_exams[] = $exam_value;
                        }
                    }
                }
            }

            if ($term_exam_weightage != 100) {
                $this->form_validation->set_rules('max_weightage', $this->lang->line('weightage'), 'callback_check_weightage[' . $term_exam_weightage . ']');
            }

            $teacher_remark = $this->input->post('teacher_remark');
            if (!empty($selected_exams) && isset($teacher_remark)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('teacher_remark', $this->lang->line('teacher_remark'), "callback_check_teacher_remark[" . $selectable . "]");
            }

            $exam_grading = $this->input->post('grading');
            if (!empty($selected_exams) && isset($exam_grading)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('grading', $this->lang->line('exam_grading'), "callback_check_exam_grading[" . $selectable . "]");
            }

            $subject_note = $this->input->post('subject_note');
            if (!empty($selected_exams) && isset($subject_note)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('subject_note',$this->lang->line('subject_note'), "callback_check_exam_subject_note[" . $selectable . "]");
            }


        } elseif ($_POST['marksheet'] == 'without_term') {

            $this->form_validation->set_rules('teacher_remark', $this->lang->line('teacher_remark'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('grading', $this->lang->line('grading'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('subject_note', $this->lang->line('subject_note'), 'trim|required|xss_clean');

            $selected_exams = [];
            $term_exam_weightage = 0;
            if (!empty($_POST['exam'])) {
                if (count($_POST['exam']) <= 1) {
                    $this->form_validation->set_rules('multiple_exams', $this->lang->line('exams'), 'callback_check_exams[' . count($_POST['exam']) . ']');
                }
                foreach ($_POST['exam'] as $exam_key => $exam_value) {
                    $selected_exams[] = $exam_value;
                    if ($_POST['weightage'][$exam_value] != "") {
                        $term_exam_weightage += $_POST['weightage'][$exam_value];
                    }
                }
            }

            if ($term_exam_weightage != 100) {
                $this->form_validation->set_rules('max_weightage', $this->lang->line('weightage'), 'callback_check_weightage[' . $term_exam_weightage . ']');
            }

            $teacher_remark = $this->input->post('teacher_remark');
            if (!empty($selected_exams) && isset($teacher_remark)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('teacher_remark', $this->lang->line('teacher_remark'), "callback_check_teacher_remark[" . $selectable . "]");
            }

            $exam_grading = $this->input->post('grading');
            if (!empty($selected_exams) && isset($exam_grading)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('grading', $this->lang->line('exam_grading'), "callback_check_exam_grading[" . $selectable . "]");
            }

            $subject_note = $this->input->post('subject_note');
            if (!empty($selected_exams) && isset($subject_note)) {
                $selectable = implode("|", $selected_exams);
                $this->form_validation->set_rules('subject_note',$this->lang->line('subject_note'), "callback_check_exam_subject_note[" . $selectable . "]");
            }
        }

        if ($this->form_validation->run() == false) {
            $msg['marksheet'] = form_error('marksheet');
            $msg['max_weightage'] = form_error('max_weightage');
            $msg['teacher_remark'] = form_error('teacher_remark');
            $msg['multiple_exams'] = form_error('multiple_exams');
            $msg['terms'] = form_error('terms[]');
            $msg['grading'] = form_error('grading');
            $msg['subject_note'] = form_error('subject_note'); //added
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $templatedata = array(
                'marksheet_type' => $_POST['marksheet'],
                'id' => $_POST['template_id'],
            );
            if (isset($_POST['subject_note'])) {
                $templatedata['subjectnoteexam_id'] = $_POST['subject_note'];
            }
            if (isset($_POST['grading'])) {
                $templatedata['gradeexam_id'] = $_POST['grading'];
            }
            if (isset($_POST['teacher_remark'])) {
                $templatedata['remarkexam_id'] = $_POST['teacher_remark'];
            }
            if (($_POST['marksheet'] == 'exam_wise')) {
                $exam_first = $this->input->post('exam');
                $templatedata['gradeexam_id'] = $exam_first[0];
                $templatedata['remarkexam_id'] = $exam_first[0];
                $templatedata['subjectnoteexam_id'] = $exam_first[0];
            }
            $template_id = $this->cbseexam_template_model->add($templatedata);
            $this->cbseexam_template_model->delete_template_record($_POST['template_id']);

            if (($_POST['marksheet'] == 'all_term') || ($_POST['marksheet'] == 'term_wise')) {
                $cbse_template_terms = array();
                if (!empty($_POST['terms'])) {

                    foreach ($_POST['terms'] as $key => $value) {
                        $cbse_template_terms = array(
                            'cbse_template_id' => $_POST['template_id'],
                            'cbse_term_id' => $value,
                        );

                        if (isset($_POST['term_weightage'])) {
                            $cbse_template_terms['weightage'] = $_POST['term_weightage'][$value];
                        }

                        $cbse_template_term_id = $this->cbseexam_template_model->cbse_template_terms($cbse_template_terms);

                        if (!empty($_POST['exam'][$value])) {
                            foreach ($_POST['exam'][$value] as $exam_key => $exam_value) {

                                $cbse_template_term_exam = array(
                                    'cbse_template_term_id' => $cbse_template_term_id,
                                    'cbse_exam_id' => $exam_value,
                                    'cbse_template_id' => $_POST['template_id'],
                                );

                                if (isset($_POST['weightage'])) {
                                    $cbse_template_term_exam['weightage'] = $_POST['weightage'][$value][$exam_value];
                                }

                                $this->cbseexam_template_model->cbse_template_term_exams($cbse_template_term_exam);
                                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
                            }
                        } else {
                            $msg['section'] = $this->lang->line('please_select_exam');
                            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                        }
                    }
                } else {
                    $msg['section'] = $this->lang->line('please_select_term');
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                }
            } elseif (($_POST['marksheet'] == 'exam_wise') || ($_POST['marksheet'] == 'without_term')) {

                if (!empty($_POST['exam'])) {
                    foreach ($_POST['exam'] as $exam_key => $exam_value) {

                        $cbse_template_term_exam['cbse_exam_id'] = $exam_value;
                        $cbse_template_term_exam['cbse_template_id'] = $_POST['template_id'];

                        if (isset($_POST['weightage'])) {
                            $cbse_template_term_exam['weightage'] = $_POST['weightage'][$exam_value];
                        }
                        $this->cbseexam_template_model->cbse_template_term_exams($cbse_template_term_exam);
                        $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
                        # code...
                    }
                } else {
                    $msg['section'] = $this->lang->line('please_select_exam');
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                }
            }
        }

        echo json_encode($array);
    }

    public function get()
    {
        $class_section_id = $_POST['class_section_id'];

        $template_list = $this->cbseexam_template_model->getTemplateListbyclasssectionid($class_section_id);
        echo json_encode($template_list);
    }

    public function check_exams($field, $exams)
    {
        if ($exams >= 2) {
            // user exists
            return true;
        } else {
            $this->form_validation->set_message('check_exams', $this->lang->line('select_multiple_exams'));
            return false;
        }
    }

    public function check_exam_subject_note($field, $exams)
    {
        $exams = explode("|", $exams);
        if (in_array($field, $exams)) {
            // user exists
            return true;
        } else {
            $this->form_validation->set_message('check_exam_subject_note', $this->lang->line('subject_note_should_be_choosen_from_selected_exam'));
            return false;
        }
        return true;
    }

  public function check_exam_grading($field, $exams)
    {
        $exams = explode("|", $exams);
        if (in_array($field, $exams)) {
            // user exists
            return true;
        } else {
            $this->form_validation->set_message('check_exam_grading', $this->lang->line('grading_should_be_choose_from_selected_exam'));
            return false;
        }
        return true;
    }

    public function check_teacher_remark($field, $exams)
    {
        $exams = explode("|", $exams);
        if (in_array($field, $exams)) {
            // user exists
            return true;
        } else {
            $this->form_validation->set_message('check_teacher_remark', $this->lang->line('teacher_remark_should_be_choose_from_selected_exam'));
            return false;
        }
        return true;
    }

    public function check_weightage($field, $weightage)
    {
        if ($weightage == 100) {
            // user exists
            return true;
        } else {
            if (strlen($weightage) != 0) {
                if ($weightage == 0) {
                    $this->form_validation->set_message('check_weightage', $this->lang->line('select_term_exam_weightage_should_not_be_zero_or_empty'));
                    return false;
                } elseif ($weightage > 100 || $weightage < 100) {
                    $this->form_validation->set_message('check_weightage', $this->lang->line('select_term_exam_weightage_should_not_be_greater_or_less'));
                    return false;
                }
            }
        }
        return true;
    }

    public function handle_upload($str, $var)
    {
        $result = $this->filetype_model->get();
        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {

            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES[$var]['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->image_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed') . " " . $this->lang->line('or') . " " . $this->lang->line('extension_not_allowed'));
                return false;
            }

            return true;
        }
        return true;
    }

    public function templatewiserank($template_id)
    {
        $data = array();
        $data['sch_setting'] = $this->setting_model->getSetting();
        $class_section_id = $this->input->post('class_section_id');
        $data['template'] = $this->cbseexam_template_model->get($template_id);
        $data['studentList'] = $this->cbseexam_result_model->getTemplateStudents($template_id);
        $data['cbse_template_id'] = $template_id;
        $this->load->view('layout/header', $data);
        $this->load->view('cbseexam/template/templatewiserank', $data);
        $this->load->view('layout/footer', $data);
    }
}
