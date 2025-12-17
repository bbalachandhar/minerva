<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Staff extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('staff_model');
        $this->load->model('setting_model'); // Load the setting_model
    }

    public function profile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $auth = $this->auth_model->auth();
        if ($auth['status'] != 200) {
            json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
            return;
        }

        $staff_id = $this->input->get_request_header('User-ID', true);
        $profile_data = $this->staff_model->getProfile($staff_id);

        $setting = $this->setting_model->getSetting(); // Get settings
        $can_edit_profile = (isset($setting->staff_profile_edit) && $setting->staff_profile_edit == '1') ? true : false; // Use the flag

        error_log("DEBUG: can_edit_profile sent to mobile app: " . var_export($can_edit_profile, true));

        if ($profile_data) {
            json_output(200, array('status' => 200, 'message' => 'Profile data retrieved successfully', 'data' => $profile_data, 'can_edit_profile' => $can_edit_profile));
        } else {
            json_output(404, array('status' => 404, 'message' => 'Profile not found'));
        }
    }

    public function edit_profile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $auth = $this->auth_model->auth();
        if ($auth['status'] != 200) {
            json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
            return;
        }

        $staff_id = $this->input->get_request_header('User-ID', true);
        if (empty($staff_id)) {
            json_output(400, array('status' => 400, 'message' => 'User-ID header is missing.'));
            return;
        }

        // Load form validation library
        $this->load->library('form_validation');

        // Stage 1: Basic & Personal Information
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('surname', 'Surname', 'trim|xss_clean');
        $this->form_validation->set_rules('gender', 'Gender', 'trim|xss_clean');
        $this->form_validation->set_rules('dob', 'Date of Birth', 'trim|xss_clean');
        $this->form_validation->set_rules('marital_status', 'Marital Status', 'trim|xss_clean');
        $this->form_validation->set_rules('employee_id', 'Employee ID', 'trim|xss_clean');
        $this->form_validation->set_rules('biometric_id', 'Biometric ID', 'trim|xss_clean');
        
        // Stage 2: Contact Details
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|xss_clean');
        $this->form_validation->set_rules('contact_no', 'Contact No', 'trim|xss_clean');
        $this->form_validation->set_rules('emergency_contact_no', 'Emergency Contact No', 'trim|xss_clean');
        $this->form_validation->set_rules('local_address', 'Local Address', 'trim|xss_clean');
        $this->form_validation->set_rules('permanent_address', 'Permanent Address', 'trim|xss_clean');

        // Stage 3: Professional & Academic Information
        $this->form_validation->set_rules('designation', 'Designation', 'trim|xss_clean');
        $this->form_validation->set_rules('department', 'Department', 'trim|xss_clean');
        $this->form_validation->set_rules('qualification', 'Qualification', 'trim|xss_clean');
        $this->form_validation->set_rules('ug_qualification', 'UG Qualification', 'trim|xss_clean');
        $this->form_validation->set_rules('pg_qualification', 'PG Qualification', 'trim|xss_clean');
        $this->form_validation->set_rules('higher_qualification', 'Higher Qualification', 'trim|xss_clean');
        $this->form_validation->set_rules('qualified_exam', 'Qualified Exam', 'trim|xss_clean');
        $this->form_validation->set_rules('subject_specialization', 'Subject Specialization', 'trim|xss_clean');
        $this->form_validation->set_rules('additional_qualification', 'Additional Qualification', 'trim|xss_clean');
        $this->form_validation->set_rules('work_exp', 'Work Experience', 'trim|xss_clean');
        $this->form_validation->set_rules('date_of_joining', 'Date of Joining', 'trim|xss_clean');
        $this->form_validation->set_rules('date_of_leaving', 'Date of Leaving', 'trim|xss_clean');
        $this->form_validation->set_rules('shift', 'Shift', 'trim|xss_clean');
        $this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');

        // Stage 4: Financial & Employment Details
        $this->form_validation->set_rules('account_title', 'Account Title', 'trim|xss_clean');
        $this->form_validation->set_rules('bank_account_no', 'Bank Account No', 'trim|xss_clean');
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|xss_clean');
        $this->form_validation->set_rules('ifsc_code', 'IFSC Code', 'trim|xss_clean');
        $this->form_validation->set_rules('bank_branch', 'Bank Branch', 'trim|xss_clean');
        $this->form_validation->set_rules('payscale', 'Payscale', 'trim|xss_clean');
        $this->form_validation->set_rules('basic_salary', 'Basic Salary', 'trim|xss_clean');
        $this->form_validation->set_rules('epf_no', 'EPF No', 'trim|xss_clean');
        $this->form_validation->set_rules('contract_type', 'Contract Type', 'trim|xss_clean');

        // Stage 5: Social Media & Other
        $this->form_validation->set_rules('facebook', 'Facebook', 'trim|xss_clean');
        $this->form_validation->set_rules('twitter', 'Twitter', 'trim|xss_clean');
        $this->form_validation->set_rules('linkedin', 'LinkedIn', 'trim|xss_clean');
        $this->form_validation->set_rules('instagram', 'Instagram', 'trim|xss_clean');
        $this->form_validation->set_rules('note', 'Note', 'trim|xss_clean');
        // Resume, joiningLetter, resignationLetter, otherDocumentName, otherDocumentFile are file uploads and should be handled separately or with specific validation rules

        if ($this->form_validation->run() == FALSE) {
            $errors = validation_errors();
            json_output(400, array('status' => 400, 'message' => 'Validation failed', 'errors' => $errors));
            return;
        }

        // Prepare data for update - only include fields that were passed in the POST request
        $data = array('id' => $staff_id);
        
        $fields_to_update = [
            'name', 'surname', 'gender', 'dob', 'marital_status', 'employee_id', 'biometric_id',
            'email', 'contact_no', 'emergency_contact_no', 'local_address', 'permanent_address',
            'designation', 'department', 'qualification', 'ug_qualification', 'pg_qualification',
            'higher_qualification', 'qualified_exam', 'subject_specialization', 'additional_qualification',
            'work_exp', 'date_of_joining', 'date_of_leaving', 'shift', 'location',
            'account_title', 'bank_account_no', 'bank_name', 'ifsc_code', 'bank_branch',
            'payscale', 'basic_salary', 'epf_no', 'contract_type',
            'facebook', 'twitter', 'linkedin', 'instagram', 'note',
            // Files (image, resume, etc.) handled by separate upload endpoints
        ];

        foreach ($fields_to_update as $field) {
            if ($this->input->post($field) !== NULL) { // Check if the field was sent in the POST request
                $data[$field] = $this->input->post($field);
            }
        }

        $update_status = $this->staff_model->update($data);

        if ($update_status) {
            json_output(200, array('status' => 200, 'message' => 'Profile updated successfully.'));
        } else {
            json_output(500, array('status' => 500, 'message' => 'Failed to update profile.'));
        }
    }

    public function upload_profile_picture()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $auth = $this->auth_model->auth();
        if ($auth['status'] != 200) {
            json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
            return;
        }

        $staff_id = $this->input->get_request_header('User-ID', true);
        if (empty($staff_id)) {
            json_output(400, array('status' => 400, 'message' => 'User-ID header is missing.'));
            return;
        }

        // Load media_storage library and form_validation
        $this->load->library('media_storage');
        $this->load->library('form_validation');

        // Set validation rules for the file upload
        // Assuming 'profile_image' is the name of the input field for the file
        $this->form_validation->set_rules('profile_image', 'Profile Image', 'callback_handle_image_upload');

        if ($this->form_validation->run() == FALSE) {
            $errors = validation_errors();
            json_output(400, array('status' => 400, 'message' => 'Validation failed', 'errors' => $errors));
            return;
        }

        // Handle the actual file upload
        if (isset($_FILES["profile_image"]) && $_FILES['profile_image']['name'] != '') {
            $upload_path = "./uploads/staff_images/";
            $upload_result = $this->media_storage->fileupload("profile_image", $upload_path);

            if ($upload_result['status'] === FALSE) {
                json_output(500, array('status' => 500, 'message' => 'Image upload failed', 'error' => $upload_result['message']));
                return;
            }

            $image_name = $upload_result['message']; // This is the new image file name

            // Update staff table with the new image name
            $data = array(
                'id'    => $staff_id,
                'image' => $image_name,
            );
            $update_status = $this->staff_model->update($data);

            if ($update_status) {
                json_output(200, array('status' => 200, 'message' => 'Profile picture updated successfully.', 'image_url' => base_url($upload_path . $image_name)));
            } else {
                // If DB update fails, consider deleting the uploaded file
                $this->media_storage->filedelete($image_name, $upload_path);
                json_output(500, array('status' => 500, 'message' => 'Failed to update profile picture in database.'));
            }
        } else {
            json_output(400, array('status' => 400, 'message' => 'No image file uploaded.'));
        }
    }

    // Callback for image validation - can be reused from Schsettings or customized
    public function handle_image_upload()
    {
        if (isset($_FILES["profile_image"]) && !empty($_FILES['profile_image']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png');
            $temp = explode(".", $_FILES["profile_image"]["name"]);
            $extension = end($temp);

            if ($_FILES["profile_image"]["error"] > 0) {
                $this->form_validation->set_message('handle_image_upload', 'Error opening the file.');
                return false;
            }
            if (!in_array(strtolower($extension), $allowedExts)) {
                $this->form_validation->set_message('handle_image_upload', 'File type not allowed. Only JPG, JPEG, PNG are permitted.');
                return false;
            }
            if ($_FILES["profile_image"]["size"] > 2097152) { // 2MB max size
                $this->form_validation->set_message('handle_image_upload', 'File size should be less than 2MB.');
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_image_upload', 'The profile image field is required.');
            return false;
        }
    }

    public function profile_completion()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $auth = $this->auth_model->auth();
        if ($auth['status'] != 200) {
            json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
            return;
        }

        $staff_id = $this->input->get_request_header('User-ID', true);
        if (empty($staff_id)) {
            json_output(400, array('status' => 400, 'message' => 'User-ID header is missing.'));
            return;
        }

        $profile_data = $this->staff_model->getProfile($staff_id);

        if ($profile_data) {
            // Define a list of important fields that contribute to profile completion
            // This list should ideally match the fields in your StaffProfile entity
            $total_fields = 0;
            $filled_fields = 0;

            // Fields from StaffProfile entity (excluding auto-generated or non-editable fields)
            $important_fields = [
                'name', 'surname', 'gender', 'dob', 'marital_status',
                'email', 'contact_no', 'emergency_contact_no', 'local_address', 'permanent_address',
                'designation', 'department', 'qualification', 'ug_qualification', 'pg_qualification',
                'higher_qualification', 'qualified_exam', 'subject_specialization', 'additional_qualification',
                'work_exp', 'date_of_joining', 'date_of_leaving', 'shift', 'location',
                'account_title', 'bank_account_no', 'bank_name', 'ifsc_code', 'bank_branch',
                'payscale', 'basic_salary', 'epf_no', 'contract_type',
                'facebook', 'twitter', 'linkedin', 'instagram', 'note',
                // Exclude 'id', 'employee_id', 'biometric_id' if system-generated and not editable
                // Exclude 'image' as it's handled separately
                // Exclude 'resume', 'joining_letter', 'resignation_letter', 'other_document_name', 'other_document_file' as file uploads
                // Exclude internal system fields like 'password', 'user_id', 'is_active', 'created_at', 'updated_at', etc.
            ];

            foreach ($important_fields as $field) {
                $total_fields++;
                if (!empty($profile_data[$field])) {
                    $filled_fields++;
                }
            }

            // Include image in completion calculation if it's considered an editable part of the profile
            $total_fields++; // Profile picture counts as one field
            if (!empty($profile_data['image']) && $profile_data['image'] != 'no_image.png') { // Assuming 'no_image.png' is the default
                $filled_fields++;
            }
            
            // Add consideration for documents if they contribute to completion
            // $total_fields++; if (!empty($profile_data['resume'])) { $filled_fields++; }

            $percentage = ($total_fields > 0) ? round(($filled_fields / $total_fields) * 100) : 0;

            json_output(200, array('status' => 200, 'message' => 'Profile completion calculated successfully', 'percentage' => $percentage));
        } else {
            json_output(404, array('status' => 404, 'message' => 'Profile not found'));
        }
    }
}
?>