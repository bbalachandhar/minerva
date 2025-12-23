<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subject extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'Academics/subject');
        $data['title']         = 'Add subject';
        $subject_result        = $this->subject_model->get();
        $data['subjectlist']   = $subject_result;
        $data['subject_types'] = $this->customlib->subjectType();
        if ($this->sch_setting_detail->institution_type == 'college') {
            $this->load->model('staff_model');
            $data['teacherlist'] = $this->staff_model->getStaffbyrole(2);
        }
        $this->form_validation->set_rules('name', $this->lang->line('subject_name'), 'trim|required|xss_clean|callback__check_name_exists');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        if ($this->input->post('code')) {
            $this->form_validation->set_rules('code', $this->lang->line('code'), 'trim|required|callback__check_code_exists');
        }
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subject/subjectList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'type' => strtolower($this->input->post('type')),
            );
            if ($this->sch_setting_detail->institution_type == 'college') {
                $data['teacher_id'] = $this->input->post('teacher_id');
            }
            $this->subject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/subject/index');
        }
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_view')) {
            access_denied();
        }
        $data['title']   = 'Subject List';
        $subject         = $this->subject_model->get($id);
        $data['subject'] = $subject;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/subject/subjectShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Subject List';
        $this->subject_model->remove($id);
        redirect('admin/subject/index');
    }

    public function _check_name_exists()
    {
        $data['name'] = $this->security->xss_clean($this->input->post('name'));
        if ($this->subject_model->check_data_exists($data)) {
            $this->form_validation->set_message('_check_name_exists', $this->lang->line('name_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function _check_code_exists()
    {
        $data['code'] = $this->security->xss_clean($this->input->post('code'));
        if ($this->subject_model->check_code_exists($data)) {
            $this->form_validation->set_message('_check_code_exists', $this->lang->line('code_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_edit')) {
            access_denied();
        }
        $subject_result        = $this->subject_model->get();
        $data['subjectlist']   = $subject_result;
        $data['title']         = 'Edit Subject';
        $data['id']            = $id;
        $subject               = $this->subject_model->get($id);
        $data['subject']       = $subject;
        $data['subject_types'] = $this->customlib->subjectType();
        if ($this->sch_setting_detail->institution_type == 'college') {
            $this->load->model('staff_model');
            $data['teacherlist'] = $this->staff_model->getStaffbyrole(2);
        }
        $this->form_validation->set_rules('name', $this->lang->line('subject'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subject/subjectEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'id'   => $id,
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'type' => strtolower($this->input->post('type')),
            );
            if ($this->sch_setting_detail->institution_type == 'college') {
                $data['teacher_id'] = $this->input->post('teacher_id');
            }
            $this->subject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/subject/index');
        }
    }

    public function getSubjctByClassandSection()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $date       = $this->teachersubject_model->getSubjectByClsandSection($class_id, $section_id);
        echo json_encode($data);
    }

    public function bulk_upload()
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_add')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'Academics/subject');
        $data['title'] = 'Bulk Upload Subjects';

        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');

                    if ($this->form_validation->run() == false) {
                        $this->load->view('layout/header', $data);
                        $this->load->view('admin/subject/subjectBulkUpload', $data);
                        $this->load->view('layout/footer', $data);
                    } else {
                        if ($this->sch_setting_detail->institution_type == 'college') {
                            $this->load->model('staff_model');
                        }
                        log_message('debug', 'Starting bulk upload process.');
                        // File has been uploaded and validated
                        // Process the file content
                        $file_path = $_FILES['file']['tmp_name'];
                        $file_type = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                        log_message('debug', 'Uploaded file type: ' . $file_type . ', path: ' . $file_path);
        
                        if ($file_type == 'csv') {
                            $this->load->library('csvreader');
                            $result = $this->csvreader->parse_file($file_path);
                            log_message('debug', 'CSV parsed result: ' . print_r($result, true));
                        } else { // Only CSV is supported for now
                            log_message('error', 'Unsupported file type attempted: ' . $file_type);
                            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('file_type_not_allowed') . '</div>');
                            redirect('admin/subject/bulk_upload');
                        }
        
                        $success_count = 0;
                        $error_messages = array();
                        $line_number = 1; // For tracking errors in the file
        
                        if (!empty($result)) {
                            foreach ($result as $row) {
                                $line_number++;
                                log_message('debug', 'Processing row ' . $line_number . ': ' . print_r($row, true));
                                // Basic validation
                                $name = trim($row['name'] ?? '');
                                $type = strtolower(trim($row['type'] ?? ''));
                                $code = trim($row['code'] ?? '');
        
                                if (empty($name) || empty($type)) {
                                    $error_msg = $this->lang->line('subject_name_and_type_required_in_row') . ' ' . $line_number;
                                    $error_messages[] = $error_msg;
                                    log_message('error', 'Validation error: ' . $error_msg . ' - Name: ' . $name . ', Type: ' . $type);
                                    continue;
                                }
        
                                $subject_data = array(
                                    'name' => $name,
                                    'type' => $type,
                                    'code' => $code,
                                );

                                if ($this->sch_setting_detail->institution_type == 'college') {
                                    $teacher_name = trim($row['teacher_name'] ?? '');
                                    if (!empty($teacher_name)) {
                                        $teacher = $this->staff_model->getTeacherByName($teacher_name);
                                        if ($teacher) {
                                            $subject_data['teacher_id'] = $teacher['id'];
                                        } else {
                                            $error_messages[] = 'Teacher not found for subject "' . $name . '" in row ' . $line_number . ': ' . $teacher_name;
                                        }
                                    }
                                }
        
                                // Check if subject name already exists
                                if ($this->subject_model->check_data_exists(array('name' => $name))) {
                                    $error_msg = $this->lang->line('subject_name_already_exists_in_row') . ' ' . $line_number . ': ' . $name;
                                    $error_messages[] = $error_msg;
                                    log_message('error', 'Validation error: ' . $error_msg);
                                    continue;
                                }
        
                                // Check if subject code already exists (if provided)
                                if (!empty($code) && $this->subject_model->check_code_exists(array('code' => $code))) {
                                    $error_msg = $this->lang->line('subject_code_already_exists_in_row') . ' ' . $line_number . ': ' . $code;
                                    $error_messages[] = $error_msg;
                                    log_message('error', 'Validation error: ' . $error_msg);
                                    continue;
                                }
        
                                $insert_id = $this->subject_model->add($subject_data);
                                if ($insert_id) {
                                    $success_count++;
                                    log_message('debug', 'Subject added successfully: ' . $name . ' (ID: ' . $insert_id . ')');
                                } else {
                                    $error_msg = 'Failed to add subject to database in row ' . $line_number . ': ' . $name;
                                    $error_messages[] = $error_msg;
                                    log_message('error', $error_msg);
                                }
                            }
                        } else {
                            $error_msg = 'CSV file is empty or could not be parsed.';
                            $error_messages[] = $error_msg;
                            log_message('error', $error_msg);
                        }
        
                        log_message('debug', 'Bulk upload finished. Success count: ' . $success_count . ', Errors: ' . count($error_messages));
        
                        if ($success_count > 0) {
                            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $success_count . ' ' . $this->lang->line('subjects_uploaded_successfully') . '</div>');
                        }
                        if (!empty($error_messages)) {
                            $error_string = implode('<br>', $error_messages);
                            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">' . $this->lang->line('errors_in_upload') . '<br>' . $error_string . '</div>');
                        }
        
                        redirect('admin/subject/bulk_upload');
                    }    }

    public function handle_csv_upload()
    {
        if (isset($_FILES["file"]) && $_FILES['file']['name'] != '') {
            $allowed_mimes = array('text/csv', 'text/x-comma-separated-values');
            $mime_type = get_mime_by_extension($_FILES['file']['name']);
            log_message('debug', 'Detected MIME type for uploaded file: ' . $mime_type);
            
            if (in_array($mime_type, $allowed_mimes)) {
                return true;
            } else {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }


}
