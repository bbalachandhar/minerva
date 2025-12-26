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

        $debug_subjectlist_initial = 'Subject Controller Index: Initial $data[\'subjectlist\'] after model get: ' . print_r($data['subjectlist'], true);
        log_message('debug', $debug_subjectlist_initial);
        log_message('debug', 'Subject Controller Index: institution_type: ' . $this->sch_setting_detail->institution_type);

        $this->load->model('staff_model'); // Load staff model unconditionally
        $this->load->model('department_model'); // Load department model
        $data['departmentlist'] = $this->department_model->getDepartmentType(); // Fetch all department types

        foreach ($data['subjectlist'] as $key => $value) {
            $data['subjectlist'][$key]['teacher_name'] = ''; // Initialize to empty string by default
            log_message('debug', 'Subject Controller Index: Processing subject ID ' . $value['id'] . ', initial teacher_id: ' . $value['teacher_id']);

            if ($this->sch_setting_detail->institution_type == 'college') {
                $teacher_ids = json_decode($value['teacher_id'], true);
                $debug_decoded_teacher_ids = 'Subject Controller Index: Decoded teacher_ids for subject ' . $value['id'] . ': ' . print_r($teacher_ids, true);
                log_message('debug', $debug_decoded_teacher_ids);

                if (is_array($teacher_ids) && !empty($teacher_ids)) {
                    $teachers = $this->staff_model->getTeachersByIds($teacher_ids);
                    $debug_teachers_found = 'Subject Controller Index: Teachers found by IDs for subject ' . $value['id'] . ': ' . print_r($teachers, true);
                    log_message('debug', $debug_teachers_found);
                    $teacher_names = array();
                    foreach ($teachers as $teacher) {
                        $teacher_names[] = $teacher['name'];
                    }
                    $data['subjectlist'][$key]['teacher_name'] = implode(', ', $teacher_names);
                }
            }
            log_message('debug', 'Subject Controller Index: Final teacher_name for subject ID ' . $value['id'] . ': ' . $data['subjectlist'][$key]['teacher_name']);
        }

        if ($this->sch_setting_detail->institution_type == 'college') {
            $data['teacherlist'] = $this->staff_model->getStaffbyrole(2); // For the dropdown in add form
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
                'department_id' => $this->input->post('department_id'), // Add department_id
            );
            if ($this->sch_setting_detail->institution_type == 'college') {
                $data['teacher_id'] = json_encode($this->input->post('teacher_id'));
            } else {
                // For non-college institutions, ensure teacher_id is explicitly set to NULL or empty.
                // This prevents JSON encoding of a single teacher_id when it should not be an array.
                $data['teacher_id'] = null; 
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
        $data['subjectlist']   = $subject_result; // This is used for the table in the right pane.
        $data['title']         = 'Edit Subject';
        $data['id']            = $id;
        $subject               = $this->subject_model->get($id); // Subject being edited.
        
        $this->load->model('staff_model'); // Load staff model unconditionally
        $this->load->model('department_model'); // Load department model
        $data['departmentlist'] = $this->department_model->getDepartmentType(); // Fetch all department types

        log_message('debug', 'Subject Controller Edit: institution_type: ' . $this->sch_setting_detail->institution_type);
        log_message('debug', 'Subject Controller Edit: Initial $data[\'subjectlist\'] after model get: ' . print_r($data['subjectlist'], true));
        log_message('debug', 'Subject Controller Edit: Subject being edited - initial teacher_id: ' . $subject['teacher_id']);

        // Process teacher names for subjectlist table in edit view
        foreach ($data['subjectlist'] as $key => $value) {
            $data['subjectlist'][$key]['teacher_name'] = ''; // Initialize to empty string by default
            log_message('debug', 'Subject Controller Edit: Processing subject ID ' . $value['id'] . ', initial teacher_id: ' . $value['teacher_id']);

            if ($this->sch_setting_detail->institution_type == 'college') {
                $teacher_ids = json_decode($value['teacher_id'], true); // Decode as associative array
                if (!is_array($teacher_ids)) {
                    $teacher_ids = array();
                }
                log_message('debug', 'Subject Controller Edit: Decoded teacher_ids for subject ' . $value['id'] . ': ' . print_r($teacher_ids, true));

                if (is_array($teacher_ids) && !empty($teacher_ids)) {
                    $teachers = $this->staff_model->getTeachersByIds($teacher_ids);
                    log_message('debug', 'Subject Controller Edit: Teachers found by IDs for subject ' . $value['id'] . ': ' . print_r($teachers, true));
                    $teacher_names = array();
                    foreach ($teachers as $teacher) {
                        $teacher_names[] = $teacher['name'];
                    }
                    $data['subjectlist'][$key]['teacher_name'] = implode(', ', $teacher_names);
                }
            }
            log_message('debug', 'Subject Controller Edit: Final teacher_name for subject ID ' . $value['id'] . ': ' . $data['subjectlist'][$key]['teacher_name']);
        }
        
        if ($this->sch_setting_detail->institution_type == 'college') {
            $decoded_teacher_id = json_decode($subject['teacher_id'], true); // Decode as associative array for consistent handling
            if (!is_array($decoded_teacher_id)) {
                $decoded_teacher_id = array();
            }
            $subject['teacher_id'] = $decoded_teacher_id;
            log_message('debug', 'Subject Controller Edit: Decoded teacher_id for subject being edited (after json_decode): ' . print_r($subject['teacher_id'], true));
            $data['teacherlist'] = $this->staff_model->getStaffbyrole(2); // For the dropdown in edit form
        }

        $data['subject']       = $subject;
        $data['subject_types'] = $this->customlib->subjectType();
        
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
                'department_id' => $this->input->post('department_id'), // Add department_id
            );
            if ($this->sch_setting_detail->institution_type == 'college') {
                $data['teacher_id'] = json_encode($this->input->post('teacher_id'));
            } else {
                // For non-college institutions, ensure teacher_id is explicitly set to NULL or empty.
                // This prevents JSON encoding of a single teacher_id when it should not be an array.
                $data['teacher_id'] = null;
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
                        $file_name = $_FILES['file']['name'];
                        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                        log_message('debug', 'Uploaded file extension: ' . $file_extension . ', path: ' . $file_path);
        
                        $result = [];
        
                        if ($file_extension == 'csv') {
                            $this->load->library('csvreader');
                            $result = $this->csvreader->parse_file($file_path);
                            log_message('debug', 'CSV parsed result: ' . print_r($result, true));
                        } elseif (in_array($file_extension, ['xlsx', 'xls'])) {
                            // Load PhpSpreadsheet library
                            // This assumes PhpSpreadsheet is installed and autoloaded.
                            // If not, you'll need to install it (e.g., via Composer: composer require phpoffice/phpspreadsheet)
                            // and ensure it's properly loaded (e.g., in config/autoload.php or here).
                            require_once APPPATH . 'third_party/vendor/autoload.php'; // Adjust path as necessary if not using Composer autoload
                            
                            try {
                                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                                $worksheet = $spreadsheet->getActiveSheet();
                                $excel_data = $worksheet->toArray(null, true, true, true);
        
                                if (!empty($excel_data)) {
                                    $header = array_map('strtolower', array_values(array_filter($excel_data[1]))); // Get header from the first row, filter empty values
                                    unset($excel_data[1]); // Remove header row from data
        
                                    foreach ($excel_data as $row_num => $row) {
                                        if (empty(array_filter($row))) { // Skip empty rows
                                            continue;
                                        }
                                        $temp = [];
                                        foreach ($header as $key => $col_name) {
                                            $col_index = chr(65 + $key); // Convert numerical index to Excel column letter (A, B, C, ...)
                                            $temp[$col_name] = $row[$col_index] ?? '';
                                        }
                                        $result[] = $temp;
                                    }
                                }
                                log_message('debug', 'Excel parsed result: ' . print_r($result, true));
                            } catch (Exception $e) {
                                log_message('error', 'Error parsing Excel file: ' . $e->getMessage());
                                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('error_processing_file') . ': ' . $e->getMessage() . '</div>');
                                redirect('admin/subject/bulk_upload');
                            }
                        } else {
                            log_message('error', 'Unsupported file type attempted: ' . $file_extension);
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
                                $department_name = trim($row['department_name'] ?? ''); // New field

                                if (empty($name) || empty($type)) {
                                    $error_msg = $this->lang->line('subject_name_and_type_required_in_row') . ' ' . $line_number;
                                    $error_messages[] = $error_msg;
                                    log_message('error', 'Validation error: ' . $error_msg . ' - Name: ' . $name . ', Type: ' . $type . ', Department: ' . $department_name);
                                    continue;
                                }

                                $department_id = null;
                                if (!empty($department_name)) {
                                    $this->load->model('department_model'); // Ensure model is loaded
                                    $department = $this->department_model->getDepartmentByName($department_name);
                                    if ($department) {
                                        $department_id = $department['id'];
                                    } else {
                                        $error_messages[] = 'Department not found for subject "' . $name . '" in row ' . $line_number . ': ' . $department_name;
                                        log_message('error', 'Validation error: Department not found for subject "' . $name . '" - Department: ' . $department_name);
                                        continue; // Stop processing this row if department is not found
                                    }
                                } else {
                                    // If department name is empty, you might want to make it required or handle it as null
                                    // For now, we'll allow it to be null if not provided
                                }
        
                                $subject_data = array(
                                    'name'          => $name,
                                    'type'          => $type,
                                    'code'          => $code,
                                    'department_id' => $department_id, // Add department_id
                                );

                                if ($this->sch_setting_detail->institution_type == 'college') {
                                    $teacher_ids_csv = trim($row['teacher_ids'] ?? ''); // New: Expecting comma-separated teacher IDs
                                    $assigned_teacher_ids = [];
                                    if (!empty($teacher_ids_csv)) {
                                        $teacher_ids_array = explode(',', $teacher_ids_csv);
                                        foreach ($teacher_ids_array as $teacher_id) {
                                            $teacher_id = trim($teacher_id);
                                            if (!empty($teacher_id)) {
                                                // Assuming a method to get staff by ID or check if ID exists
                                                $teacher_exists = $this->staff_model->get($teacher_id); 
                                                if ($teacher_exists) {
                                                    $assigned_teacher_ids[] = $teacher_id;
                                                } else {
                                                    $error_messages[] = 'Teacher with ID "' . $teacher_id . '" not found for subject "' . $name . '" in row ' . $line_number . '.';
                                                    log_message('error', 'Validation error: Teacher with ID ' . $teacher_id . ' not found.');
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($assigned_teacher_ids)) {
                                        $subject_data['teacher_id'] = json_encode($assigned_teacher_ids); // Store as JSON array of IDs
                                    } else {
                                        $subject_data['teacher_id'] = null;
                                    }
                                } else {
                                    $subject_data['teacher_id'] = null;
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
            $allowed_mimes = array(
                'text/csv', 
                'text/x-comma-separated-values', 
                'application/vnd.ms-excel', // .xls
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // .xlsx
            );
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