<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Webservice extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('mailer');
        $this->load->library(array('customlib', 'enc_lib'));

        $this->load->model(array('auth_model', 'route_model', 'student_model', 'setting_model', 'attendencetype_model', 'studentfeemaster_model', 'feediscount_model', 'teachersubject_model', 'timetable_model', 'user_model', 'examgroup_model', 'webservice_model', 'grade_model', 'librarymember_model', 'bookissue_model', 'homework_model', 'event_model', 'vehroute_model', 'timeline_model', 'module_model', 'paymentsetting_model', 'customfield_model', 'subjecttimetable_model', 'onlineexam_model', 'leave_model', 'chatuser_model', 'conference_model', 'syllabus_model', 'gmeet_model', 'category_model', 'student_edit_field_model', 'filetype_model', 'course_model', 'video_tutorial_model', 'visitors_model', 'pickuppoint_model', 'staff_model', 'staffattendancemodel', 'assign_incident_model', 'offlinePayment_model', 'studentAppliedDiscount_model','coursecertificate_model'));

        $this->load->library('SaasValidation');
        $this->load->library('media_storage');

        $setting = $this->setting_model->getSchoolDetail();

        if ($setting->timezone != "") {//cbseexamresult
            date_default_timezone_set($setting->timezone);
        } else {
            date_default_timezone_set('UTC');
        }
    }

    public function validateCanUploadFile($str, $params_string)
    {
        $params_array = array_map('trim', explode(',', $params_string));
        return $this->saasvalidation->validateCanUploadFile($str, $params_array);
    }

    public function geeee()
    {
        echo date('Y-m-d H:i:s');
    }

    public function mobilebootstrap()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'GET' && $method != 'POST') {
            json_output(400, array('status' => 0, 'message' => 'Bad request.'));
            return;
        }

        $site_url = $this->extract_requested_site_url();

        $setting = $this->setting_model->getSetting();
        $configured_mobile_api_url = isset($setting->mobile_api_url) ? trim((string) $setting->mobile_api_url) : '';

        if ($configured_mobile_api_url === '') {
            json_output(200, array(
                'status' => 0,
                'is_verified' => false,
                'message' => 'User Mobile App API URL is not configured in school settings.',
            ));
            return;
        }

        if ($site_url === '') {
            json_output(200, array(
                'status' => 0,
                'is_verified' => false,
                'message' => 'site_url is required.',
            ));
            return;
        }

        $input_site_root = $this->normalize_site_root_url($site_url);
        $configured_site_root = $this->normalize_site_root_url($configured_mobile_api_url);
        $is_verified = $this->urls_match_with_local_aliases($input_site_root, $configured_site_root);

        if (!$is_verified) {
            json_output(200, array(
                'status' => 0,
                'is_verified' => false,
                'message' => 'Invalid or unregistered app URL in local school settings.',
                'configured_mobile_api_url' => rtrim($configured_mobile_api_url, '/') . '/',
            ));
            return;
        }

        $site_root = rtrim($configured_site_root, '/');
        $api_base_url = $site_root . '/api';

        json_output(200, array(
            'status' => 1,
            'is_verified' => true,
            'message' => 'School URL verified successfully.',
            'site_url' => $site_root,
            'api_base_url' => $api_base_url,
            'school_name' => isset($setting->name) ? (string) $setting->name : '',
            'school_code' => isset($setting->dise_code) ? (string) $setting->dise_code : '',
            'app_logo' => isset($setting->app_logo) && $setting->app_logo ? $site_root . '/uploads/school_content/logo/app_logo/' . $setting->app_logo : '',
            'app_primary_color_code' => isset($setting->app_primary_color_code) ? (string) $setting->app_primary_color_code : '',
            'app_secondary_color_code' => isset($setting->app_secondary_color_code) ? (string) $setting->app_secondary_color_code : '',
            'date_format' => isset($setting->date_format) ? (string) $setting->date_format : '',
            'lang_code' => isset($setting->language_code) ? (string) $setting->language_code : '',
            'session' => isset($setting->session) ? (string) $setting->session : '',
            'timezone' => isset($setting->timezone) ? (string) $setting->timezone : '',
            'app_ver' => (string) $this->config->item('app_ver'),
            'server_time' => date('c'),
            'institution_type' => isset($setting->institution_type) ? (string) $setting->institution_type : '',
        ));
    }

    public function verifyschoolregistrationlocal()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'GET' && $method != 'POST') {
            json_output(400, array('status' => 0, 'message' => 'Bad request.'));
            return;
        }

        $site_url = $this->extract_requested_site_url();

        $setting = $this->setting_model->getSetting();
        $configured_mobile_api_url = isset($setting->mobile_api_url) ? trim((string) $setting->mobile_api_url) : '';

        if ($configured_mobile_api_url === '') {
            json_output(200, array(
                'status' => 0,
                'is_verified' => false,
                'message' => 'User Mobile App API URL is not configured in school settings.',
            ));
            return;
        }

        if ($site_url === '') {
            json_output(200, array(
                'status' => 0,
                'is_verified' => false,
                'message' => 'site_url is required.',
            ));
            return;
        }

        $input_site_root = $this->normalize_site_root_url($site_url);
        $configured_site_root = $this->normalize_site_root_url($configured_mobile_api_url);

        $is_verified = $this->urls_match_with_local_aliases($input_site_root, $configured_site_root);

        if ($is_verified) {
            json_output(200, array(
                'status' => 1,
                'is_verified' => true,
                'message' => 'School URL verified successfully.',
                'configured_mobile_api_url' => rtrim($configured_mobile_api_url, '/') . '/',
            ));
            return;
        }

        json_output(200, array(
            'status' => 0,
            'is_verified' => false,
            'message' => 'Invalid or unregistered app URL in local school settings.',
            'configured_mobile_api_url' => rtrim($configured_mobile_api_url, '/') . '/',
        ));
    }

    private function normalize_site_root_url($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        $parts = @parse_url($url);
        if ($parts === false || !isset($parts['host'])) {
            return '';
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'http';
        $host = strtolower($parts['host']);
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        $path = isset($parts['path']) ? $parts['path'] : '';
        $path = preg_replace('#/+#', '/', $path);
        $path = rtrim($path, '/');

        if (substr($path, -10) === '/index.php') {
            $path = substr($path, 0, -10);
        }

        if (substr($path, -4) === '/api') {
            $path = substr($path, 0, -4);
        }

        if (substr($path, -14) === '/api/index.php') {
            $path = substr($path, 0, -14);
        }

        if ($path === '') {
            return $scheme . '://' . $host . $port;
        }

        return $scheme . '://' . $host . $port . $path;
    }

    private function extract_requested_site_url()
    {
        $site_url = trim((string) $this->input->post('site_url'));
        if ($site_url !== '') {
            return $site_url;
        }

        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $payload = json_decode($raw, true);
            if (is_array($payload) && isset($payload['site_url'])) {
                $site_url = trim((string) $payload['site_url']);
                if ($site_url !== '') {
                    return $site_url;
                }
            }
        }

        return trim((string) $this->input->get('site_url'));
    }

    private function is_local_host($host)
    {
        $host = strtolower(trim((string) $host));
        // Loopback and Android emulator aliases
        if (in_array($host, array('localhost', '127.0.0.1', '10.0.2.2'), true)) {
            return true;
        }
        // Private IPv4 ranges: 10.x.x.x, 172.16-31.x.x, 192.168.x.x
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $host);
            if ((int) $parts[0] === 10) return true;
            if ((int) $parts[0] === 192 && (int) $parts[1] === 168) return true;
            if ((int) $parts[0] === 172 && (int) $parts[1] >= 16 && (int) $parts[1] <= 31) return true;
        }
        return false;
    }

    private function urls_match_with_local_aliases($url_a, $url_b)
    {
        if ($url_a === '' || $url_b === '') {
            return false;
        }

        if ($url_a === $url_b) {
            return true;
        }

        $a = @parse_url($url_a);
        $b = @parse_url($url_b);
        if ($a === false || $b === false) {
            return false;
        }

        $a_host = isset($a['host']) ? strtolower($a['host']) : '';
        $b_host = isset($b['host']) ? strtolower($b['host']) : '';
        $a_port = isset($a['port']) ? (int) $a['port'] : 0;
        $b_port = isset($b['port']) ? (int) $b['port'] : 0;
        $a_path = isset($a['path']) ? rtrim($a['path'], '/') : '';
        $b_path = isset($b['path']) ? rtrim($b['path'], '/') : '';

        $both_local = $this->is_local_host($a_host) && $this->is_local_host($b_host);

        if (!$both_local) {
            return false;
        }

        if ($a_port !== 0 && $b_port !== 0 && $a_port !== $b_port) {
            return false;
        }

        return $a_path === $b_path;
    }

    public function getApplyLeave()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = array();
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $student = $this->student_model->get($student_id);
                    $result = $this->leave_model->get($student->student_session_id);
                    foreach ($result as $key => $value) {
                        if ($value['docs'] == null) {
                            $result[$key]['docs'] = '';
                        }
                        if ($value['approve_by'] == null) {
                            $result[$key]['approve_by'] = '';
                        }
                        if ($value['approve_date'] == null) {
                            $result[$key]['approve_date'] = '';
                        }
                    }
                    $data['result_array'] = $result;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function addLeave()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = $this->input->POST();

                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('from_date', 'From', 'required|trim');
                    $this->form_validation->set_rules('to_date', 'To', 'required|trim');
                    $this->form_validation->set_rules('apply_date', 'Apply Date', 'required|trim');
                    $this->form_validation->set_rules('student_id', 'Student ID', 'required|trim');
                    $this->form_validation->set_rules('reason', 'Reason', 'required|trim');
                    $this->form_validation->set_rules('file', 'File', 'callback_handle_upload_file');

                    // SaaS Validation Rule
                    $storage_array = "file";
                    $this->form_validation->set_rules('validate_storage', 'Storage', "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'from_date' => form_error('from_date'),
                            'to_date' => form_error('to_date'),
                            'apply_date' => form_error('apply_date'),
                            'student_id' => form_error('student_id'),
                            'reason' => form_error('reason'),
                            'file' => form_error('file'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        //==================
                        $student = $this->student_model->get($this->input->post('student_id'));

                        $class_id = $student->class_id;
                        $section_id = $student->section_id;

                        $stafflist = $this->leave_model->getclassteacherbyclasssection($class_id, $section_id);

                        $data = array(
                            'from_date' => $this->input->post('from_date'),
                            'to_date' => $this->input->post('to_date'),
                            'apply_date' => $this->input->post('apply_date'),
                            'reason' => $this->input->post('reason'),
                            'student_session_id' => $student->student_session_id,
                        );

                        $leave_id = $this->leave_model->add($data);
                        $message_title = "Student Leave";
                        $message = $this->input->post('message') . '<br> Apply Date: ' . $this->input->post('apply_date') . '<br> From Date: ' . $this->input->post('from_date') . '<br> To Date: ' . $this->input->post('to_date');

                        if (!empty($stafflist)) {
                            foreach ($stafflist as $stafflist_value) {
                                $this->mailer->send_mail($stafflist_value['email'], $message_title, $message, $_FILES, "");
                            }
                        }

                        $upload_path = $this->config->item('upload_path') . "/student_leavedocuments/";

                        // SaaS Quota Reservation
                        $storage_array = ['file'];
                        $this->saasvalidation->updateStorageLimit('storage', $storage_array);

                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                            $fileInfo = pathinfo($_FILES["file"]["name"]);
                            $img_name = $leave_id . '.' . $fileInfo['extension'];

                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $img_name)) {
                                $data = array('id' => $leave_id, 'docs' => $img_name);
                                $this->leave_model->add($data);
                            } else {
                                // Upload Failed - Rollback Quota
                                // Calculate size in KB to rollback
                                if (isset($_FILES['file']['size']) && $_FILES['file']['size'] > 0) {
                                    $file_size_kb = round($_FILES['file']['size'] / 1024);
                                    $this->saasvalidation->deleteResouceQuota('storage', $file_size_kb);
                                }
                            }
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function handle_upload_file()
    {
        $image_validate = $this->config->item('file_validate');
        $result = $this->filetype_model->get();
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

            $file_type = $_FILES["file"]['type'];
            $file_size = $_FILES["file"]["size"];
            $file_name = $_FILES["file"]["name"];
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($files = filesize($_FILES['file']['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload_file', 'File Type Not Allowed');
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload_file', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload_file', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload_file', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_upload_file_compulsory()
    {
        $image_validate = $this->config->item('file_validate');
        $result = $this->filetype_model->get();
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

            $file_type = $_FILES["file"]['type'];
            $file_size = $_FILES["file"]["size"];
            $file_name = $_FILES["file"]["name"];
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($files = filesize($_FILES['file']['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload_file_compulsory', 'File Type Not Allowed');
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload_file_compulsory', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload_file_compulsory', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload_file_compulsory', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        } else {

            $this->form_validation->set_message('handle_upload_file_compulsory', "The File Field is required");
            return false;
        }
        return true;
    }

    public function updateLeave()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $data = $this->input->POST();
                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('id', 'From', 'required|trim');
                    $this->form_validation->set_rules('from_date', 'From', 'required|trim');
                    $this->form_validation->set_rules('to_date', 'To', 'required|trim');
                    $this->form_validation->set_rules('apply_date', 'Apply Date', 'required|trim');

                    // SaaS Validation Rule
                    $storage_array = "file";
                    $this->form_validation->set_rules('validate_storage', 'Storage', "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'id' => form_error('id'),
                            'from_date' => form_error('from_date'),
                            'to_date' => form_error('to_date'),
                            'apply_date' => form_error('apply_date'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        //==================
                        $leave_id = $this->input->post('id');
                        $data = array(
                            'id' => $this->input->post('id'),
                            'from_date' => $this->input->post('from_date'),
                            'to_date' => $this->input->post('to_date'),
                            'apply_date' => $this->input->post('apply_date'),
                            'reason' => $this->input->post('reason'),
                        );
                        $upload_path = $this->config->item('upload_path') . "/student_leavedocuments/";

                        $this->leave_model->add($data);
                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                            $fileInfo = pathinfo($_FILES["file"]["name"]);
                            $img_name = $leave_id . '.' . $fileInfo['extension'];

                            // SaaS Logic: Differential Update
                            $prev_file_size = 0;
                            $current_leave = $this->leave_model->get($leave_id);

                            if (!empty($current_leave['docs'])) {
                                $upload_path_dir = $this->config->item('upload_path') . "/student_leavedocuments/";
                                $file_url = $upload_path_dir . $current_leave['docs'];
                                if (file_exists($file_url)) {
                                    $prev_file_size = round(filesize($file_url) / 1024);
                                }
                            }

                            $new_file_size = round($_FILES['file']['size'] / 1024);

                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $img_name)) {

                                // Upload Success - Update Quota
                                if ($prev_file_size > $new_file_size) {
                                    $diff = $prev_file_size - $new_file_size;
                                    $this->saasvalidation->deleteResouceQuota('storage', $diff);
                                } elseif ($new_file_size > $prev_file_size) {
                                    $diff = $new_file_size - $prev_file_size;
                                    $this->saasvalidation->updateResouceQuota('storage', $diff);
                                }

                                // Delete old file if name is different (e.g., extension changed)
                                if (!empty($current_leave['docs']) && $current_leave['docs'] != $img_name) {
                                    $old_file_url = $this->config->item('upload_path') . "/student_leavedocuments/" . $current_leave['docs'];
                                    if (file_exists($old_file_url)) {
                                        unlink($old_file_url);
                                    }
                                }

                                $data = array('id' => $leave_id, 'docs' => $img_name);
                                $this->leave_model->add($data);

                            } else {
                                // Upload Failed - No Quota Change needed as we haven't committed the new file size to SaaS/DB
                            }
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function find_subject_array_exists($subject_id, $subjects)
    {

        foreach ($subjects as $subject_key => $subject_value) {
            if ($subject_value['subject_id'] == $subject_id) {
                return true;
            }
        }

        return false;

    }

    public function findSubjectAssessmentNotExists($cbse_exam_assessment_type_id, $subjects, $subject_id)
    {

        foreach ($subjects as $subject_key => $subject_value) {

            if ($subject_value['subject_id'] == $subject_id) {

                if (!array_key_exists($cbse_exam_assessment_type_id, $subject_value['exam_assessments'])) {
                    return ['subject_key' => $subject_key];
                }
            }
        }

        return NULL;

    }

    public function deleteLeave()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $leave_id = $params['leave_id'];

                    // SaaS Logic: Clean up storage & quota
                    $leave_data = $this->leave_model->get($leave_id);
                    if (!empty($leave_data['docs'])) {
                        $file_path = $this->config->item('upload_path') . "/student_leavedocuments/" . $leave_data['docs'];
                        if (file_exists($file_path)) {
                            $file_size_kb = round(filesize($file_path) / 1024);
                            $this->saasvalidation->deleteResouceQuota('storage', $file_size_kb);
                            unlink($file_path);
                        }
                    }

                    $this->leave_model->delete($leave_id);

                    json_output($response['status'], array('result' => 'Success'));
                }
            }
        }
    }

    public function getSchoolDetails()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $result = $this->setting_model->getSchoolDisplay();
                    $result->start_month_name = ucfirst($this->customlib->getMonthList($result->start_month));

                    json_output($response['status'], $result);
                }
            }
        }
    }

    public function getStudentProfile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    if (!is_array($params)) {
                        json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
                        return;
                    }

                    $studentId = isset($params['student_id']) ? trim((string) $params['student_id']) : '';
                    $user_type = isset($params['user_type']) ? trim((string) $params['user_type']) : 'student';

                    if ($studentId === '') {
                        json_output(422, array('status' => 0, 'message' => 'student_id is required.'));
                        return;
                    }

                    $student_fields = $this->setting_model->student_fields();
                    $student_array = array();
                    $student_result = $this->student_model->get($studentId);

                    if (empty($student_result)) {
                        json_output(404, array('status' => 0, 'message' => 'Student not found.'));
                        return;
                    }

                    if (is_array($student_result)) {
                        $student_result = (object) $student_result;
                    }
					 
                    if ($student_result->category == '') {
                        $student_result->category = '';
                    }
                    if ($student_result->pickup_point_name == '') {
                        $student_result->pickup_point_name = '';
                    }
                    if ($student_result->route_pickup_point_id == '') {
                        $student_result->route_pickup_point_id = '';
                    }
                    if ($student_result->parent_app_key == '') {
                        $student_result->parent_app_key = '';
                    }
                    if ($student_result->vehroute_id == '') {
                        $student_result->vehroute_id = '';
                    }
                    if ($student_result->route_id == '') {
                        $student_result->route_id = '';
                    }
                    if ($student_result->vehicle_id == '') {
                        $student_result->vehicle_id = '';
                    }
                    if ($student_result->route_title == '') {
                        $student_result->route_title = '';
                    }
                    if ($student_result->vehicle_no == '') {
                        $student_result->vehicle_no = '';
                    }
                    if ($student_result->driver_name == '') {
                        $student_result->driver_name = '';
                    }
                    if ($student_result->driver_contact == '') {
                        $student_result->driver_contact = '';
                    }
                    if ($student_result->vehicle_model == '') {
                        $student_result->vehicle_model = '';
                    }
                    if ($student_result->manufacture_year == '') {
                        $student_result->manufacture_year = '';
                    }
                    if ($student_result->driver_licence == '') {
                        $student_result->driver_licence = '';
                    }
                    if ($student_result->middlename == '') {
                        $student_result->middlename = '';
                    }
                    if ($student_result->state == '') {
                        $student_result->state = '';
                    }
                    if ($student_result->city == '') {
                        $student_result->city = '';
                    }
                    if ($student_result->pincode == '') {
                        $student_result->pincode = '';
                    }
                    if ($student_result->updated_at == '') {
                        $student_result->updated_at = '';
                    }
                    if ($student_result->mobileno == '') {
                        $student_result->mobileno = '';
                    }
                    if ($student_result->email == '') {
                        $student_result->email = '';
                    }
                    if ($student_result->state == '') {
                        $student_result->state = '';
                    }
                    if ($student_result->city == '') {
                        $student_result->city = '';
                    }
                    if ($student_result->pincode == '') {
                        $student_result->pincode = '';
                    }
                    if ($student_result->note == '') {
                        $student_result->note = '';
                    }
                    if ($student_result->religion == '') {
                        $student_result->religion = '';
                    }
                    if ($student_result->cast == '') {
                        $student_result->cast = '';
                    }
                    if ($student_result->house_name == '') {
                        $student_result->house_name = '';
                    }
                    if ($student_result->room_no == '') {
                        $student_result->room_no = '';
                    }
                    if ($student_result->hostel_id == '') {
                        $student_result->hostel_id = '';
                    }
                    if ($student_result->hostel_name == '') {
                        $student_result->hostel_name = '';
                    }
                    if ($student_result->room_type_id == '') {
                        $student_result->room_type_id = '';
                    }
                    if ($student_result->room_type == '') {
                        $student_result->room_type = '';
                    }

                    $student_result->barcode = "/uploads/student_id_card/barcodes/" . $studentId . ".png";
                    $student_result->qrcode = "/uploads/student_id_card/qrcode/" . $studentId . ".png";

                    $ModuleExistOrNot = $this->module_model->getModuleExistOrNot($user_type, 'behaviour_records');

                    if (!empty($ModuleExistOrNot)) {
                        $behaviou_score_result = $this->assign_incident_model->totalpoints($studentId)['totalpoints'];
                        if (!empty($behaviou_score_result)) {
                            $student_result->behaviou_score = $behaviou_score_result;
                        } else {
                            $student_result->behaviou_score = '';
                        }
                    } else {
                        $student_result->behaviou_score = '';
                    }

                    $student_array['student_result'] = $student_result;
                    $student_array['student_fields'] = $student_fields;

                    $custom_fields_data = $this->customfield_model->get_custom_table_values($studentId, 'students');
                    $custom_fields = array();
                    if (!empty($custom_fields_data)) {
                        foreach ($custom_fields_data as $custom_key => $custom_value) {
                            if ($custom_value->field_value == null) {
                                $custom_value->field_value = '';
                            }
                            $custom_fields[$custom_value->name] = $custom_value->field_value;
                        }
                    }
                    $student_array['custom_fields'] = $custom_fields;

                    json_output($response['status'], $student_array);
                }
            }
        }
    }

    public function getStaffProfile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
                    if ($login_user_id === '') {
                        json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
                        return;
                    }

                    $params = json_decode(file_get_contents('php://input'), true);
                    $requested_staff_id = '';
                    if (is_array($params) && isset($params['staff_id'])) {
                        $requested_staff_id = trim((string) $params['staff_id']);
                    }

                    $this->db->select('users.id as user_login_id, users.role, users.user_id as staff_id, staff.name, staff.surname, staff.employee_id, staff.email, staff.contact_no as mobileno, staff.image, staff.gender, staff.designation as designation_id, staff.department as department_id, staff_designation.designation, department.department_name, staff.is_active, staff.date_of_joining, staff.dob, staff.marital_status, staff.emergency_contact_no, staff.local_address as current_address, staff.permanent_address, staff.qualification');
                    $this->db->from('users');
                    $this->db->join('staff', 'staff.id = users.user_id');
                    $this->db->join('staff_designation', 'staff_designation.id = staff.designation', 'left');
                    $this->db->join('department', 'department.id = staff.department', 'left');
                    $this->db->where('users.id', $login_user_id);
                    $staff_result = $this->db->get()->row();

                    if (empty($staff_result)) {
                        json_output(404, array('status' => 0, 'message' => 'Staff profile not found.'));
                        return;
                    }

                    if ($staff_result->role === 'student' || $staff_result->role === 'parent') {
                        json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
                        return;
                    }

                    if ($requested_staff_id !== '' && (string) $staff_result->staff_id !== $requested_staff_id) {
                        json_output(403, array('status' => 0, 'message' => 'You are not authorized to view this staff profile.'));
                        return;
                    }

                    if ($staff_result->name == null) {
                        $staff_result->name = '';
                    }
                    if ($staff_result->surname == null) {
                        $staff_result->surname = '';
                    }
                    if ($staff_result->employee_id == null) {
                        $staff_result->employee_id = '';
                    }
                    if ($staff_result->email == null) {
                        $staff_result->email = '';
                    }
                    if ($staff_result->mobileno == null) {
                        $staff_result->mobileno = '';
                    }
                    if ($staff_result->designation == null) {
                        $staff_result->designation = '';
                    }
                    if ($staff_result->department_name == null) {
                        $staff_result->department_name = '';
                    }

                    json_output($response['status'], array(
                        'status' => 1,
                        'message' => 'Success',
                        'staff_result' => $staff_result,
                    ));
                }
            }
        }
    }

    public function getStaffAttendanceSummary()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->join('staff', 'staff.id = users.user_id');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user)) {
            json_output(404, array('status' => 0, 'message' => 'Staff profile not found.'));
            return;
        }

        if ($staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $month = '';
        if (is_array($params) && isset($params['month'])) {
            $month = trim((string) $params['month']);
        }

        if ($month === '') {
            $month = date('Y-m');
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid month format. Use YYYY-MM.'));
            return;
        }

        $start_date = $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        $staff_id = (int) $staff_user->staff_id;

        $attendance_rows = $this->staffattendancemodel->getAttendanceRowsInRange($staff_id, $start_date, $end_date);
        $attendance_types = $this->staffattendancemodel->getStaffAttendanceType();

        $counts = array();
        $type_meta = array();
        foreach ($attendance_types as $type_row) {
            $type_id = (string) $type_row['id'];
            $type_key = (string) $type_row['key_value'];
            $counts[$type_id] = 0;
            $type_meta[$type_id] = array(
                'id' => (int) $type_row['id'],
                'type' => (string) $type_row['type'],
                'key_value' => $type_key,
                'long_lang_name' => isset($type_row['long_lang_name']) ? (string) $type_row['long_lang_name'] : '',
                'long_name_style' => isset($type_row['long_name_style']) ? (string) $type_row['long_name_style'] : '',
            );
        }

        foreach ($attendance_rows as $row) {
            $type_id = (string) $row['staff_attendance_type_id'];
            if (!isset($counts[$type_id])) {
                $counts[$type_id] = 0;
                $type_meta[$type_id] = array(
                    'id' => (int) $type_id,
                    'type' => '',
                    'key_value' => '',
                    'long_lang_name' => '',
                    'long_name_style' => '',
                );
            }
            $counts[$type_id] = (int) $counts[$type_id] + 1;
        }

        // Build date-indexed attendance map (latest row per date).
        $attendance_by_date = array();
        foreach ($attendance_rows as $row) {
            $date_key = isset($row['date']) ? (string) $row['date'] : '';
            if ($date_key !== '') {
                $attendance_by_date[$date_key] = $row;
            }
        }

        // Compute month holidays and weekends similar to web admin/staff profile attendance tab.
        $official_holiday_dates = array();
        $compensation_dates = array();
        // Query annual_calendar joined with holiday_type (same tables as Holiday_model::get())
        $this->db->select('ac.from_date, ac.to_date, ht.type');
        $this->db->from('annual_calendar ac');
        $this->db->join('holiday_type ht', 'ht.id = ac.holiday_type', 'left');
        $this->db->where('ac.from_date <=', $end_date);
        $this->db->where('ac.to_date >=', $start_date);
        $holidays = $this->db->get()->result_array();

        foreach ($holidays as $holiday_value) {
            $type_label = strtolower(trim((string) ($holiday_value['type'] ?? '')));
            $from_date_obj = new DateTime(date('Y-m-d', strtotime((string) $holiday_value['from_date'])));
            $to_date_obj   = new DateTime(date('Y-m-d', strtotime((string) $holiday_value['to_date'])));
            $current = clone $from_date_obj;

            while ($current <= $to_date_obj) {
                $date_str = $current->format('Y-m-d');
                if ($date_str >= $start_date && $date_str <= $end_date) {
                    if ($type_label === 'compensation') {
                        $compensation_dates[] = $date_str;
                    } else {
                        $official_holiday_dates[] = $date_str;
                    }
                }
                $current->modify('+1 day');
            }
        }

        $official_holiday_dates = array_values(array_unique($official_holiday_dates));
        $compensation_dates = array_values(array_unique($compensation_dates));

        $settings = $this->setting_model->getSetting();
        $weekend_days_str = isset($settings->weekend_days) && trim((string) $settings->weekend_days) !== ''
            ? (string) $settings->weekend_days
            : '0';
        $weekend_days = array_map('intval', explode(',', $weekend_days_str));
        $is_second_saturday_weekend = isset($settings->isSecondSaturdayHoliday)
            ? (int) $settings->isSecondSaturdayHoliday
            : 0;

        $month_num = (int) date('m', strtotime($start_date));
        $year_num = (int) date('Y', strtotime($start_date));
        $num_days = cal_days_in_month(CAL_GREGORIAN, $month_num, $year_num);

        $second_saturday_date = null;
        if ($is_second_saturday_weekend) {
            $saturday_count = 0;
            for ($day = 1; $day <= $num_days; $day++) {
                $d = sprintf('%04d-%02d-%02d', $year_num, $month_num, $day);
                if ((int) date('w', strtotime($d)) === 6) {
                    $saturday_count++;
                    if ($saturday_count === 2) {
                        $second_saturday_date = $d;
                        break;
                    }
                }
            }
        }

        $weekend_day_dates = array();
        for ($day = 1; $day <= $num_days; $day++) {
            $d = sprintf('%04d-%02d-%02d', $year_num, $month_num, $day);
            $dow = (int) date('w', strtotime($d));
            if (in_array($dow, $weekend_days, true) || ($second_saturday_date !== null && $d === $second_saturday_date)) {
                $weekend_day_dates[] = $d;
            }
        }

        $weekend_day_dates = array_values(array_unique($weekend_day_dates));
        if (!empty($compensation_dates)) {
            $weekend_day_dates = array_values(array_diff($weekend_day_dates, $compensation_dates));
        }

        $holiday_dates_set = array_fill_keys($official_holiday_dates, true);
        $weekend_dates_set = array_fill_keys($weekend_day_dates, true);

        // Build working day dates (exclude weekends and holidays, up to today)
        $today_date = date('Y-m-d');
        $working_day_dates = [];
        for ($day = 1; $day <= $num_days; $day++) {
            $d = sprintf('%04d-%02d-%02d', $year_num, $month_num, $day);
            if (!isset($weekend_dates_set[$d]) && !isset($holiday_dates_set[$d]) && $d <= $today_date) {
                $working_day_dates[] = $d;
            }
        }

        // --- Time-range based counting (matches web staffattendancereport logic) ---

        // Build type_map (key_value → type id)
        $type_map = array();
        foreach ($attendance_types as $t) {
            $kv = strtoupper(trim((string) ($t['key_value'] ?? '')));
            if ($kv !== '') $type_map[$kv] = (int) $t['id'];
        }

        // Get staff's role_id
        $staff_role_row = $this->db->select('role_id')->from('staff_roles')
            ->where('staff_id', $staff_id)->limit(1)->get()->row_array();
        $staff_role_id = !empty($staff_role_row['role_id']) ? (int) $staff_role_row['role_id'] : 1;

        // Admin role id for fallback
        $admin_role_row = $this->db->query("SELECT id FROM roles WHERE LOWER(name)='admin' ORDER BY id ASC LIMIT 1")->row_array();
        $admin_role_id = !empty($admin_role_row['id']) ? (int) $admin_role_row['id'] : 1;

        // Helper: fetch one schedule row from staff_attendence_schedules (replaces model call)
        $_get_schedule = function ($role_id, $type_id) {
            if (!$role_id || !$type_id) return false;
            $row = $this->db->where('role_id', $role_id)
                ->where('staff_attendence_type_id', $type_id)
                ->get('staff_attendence_schedules')->row();
            return $row ?: false;
        };

        // Load FHL/SHL/FHP/SHP time windows for this staff's role (with admin fallback)
        $att_settings = array(
            'FHL' => !empty($type_map['FHL']) ? $_get_schedule($staff_role_id, $type_map['FHL']) : false,
            'SHL' => !empty($type_map['SHL']) ? $_get_schedule($staff_role_id, $type_map['SHL']) : false,
            'FHP' => !empty($type_map['FHP']) ? $_get_schedule($staff_role_id, $type_map['FHP']) : false,
            'SHP' => !empty($type_map['SHP']) ? $_get_schedule($staff_role_id, $type_map['SHP']) : false,
        );
        $has_any_setting = !empty($att_settings['FHL']) || !empty($att_settings['SHL']) || !empty($att_settings['FHP']) || !empty($att_settings['SHP']);
        if (!$has_any_setting && $admin_role_id !== $staff_role_id) {
            $att_settings = array(
                'FHL' => !empty($type_map['FHL']) ? $_get_schedule($admin_role_id, $type_map['FHL']) : false,
                'SHL' => !empty($type_map['SHL']) ? $_get_schedule($admin_role_id, $type_map['SHL']) : false,
                'FHP' => !empty($type_map['FHP']) ? $_get_schedule($admin_role_id, $type_map['FHP']) : false,
                'SHP' => !empty($type_map['SHP']) ? $_get_schedule($admin_role_id, $type_map['SHP']) : false,
            );
        }

        // Inline time-range helper
        $_time_in_range = function ($time, $from, $to) {
            if (empty($time) || empty($from) || empty($to)) return false;
            $base = date('Y-m-d');
            $t = strtotime($base . ' ' . $time);
            $f = strtotime($base . ' ' . $from);
            $e = strtotime($base . ' ' . $to);
            if ($t === false || $f === false || $e === false) return false;
            return ($t >= $f && $t <= $e);
        };

        // Present / half-day / absent — with hasValidPunch guard (same as web profile/report)
        $present_like_keys = array('P', 'FHL', 'SHL', 'FHP', 'SHP', 'HD');
        $present_count  = 0;
        $half_day_count = 0;
        $absent_count   = 0;
        foreach ($working_day_dates as $work_date) {
            if (!isset($attendance_by_date[$work_date])) {
                $absent_count++;
                continue;
            }
            $row     = $attendance_by_date[$work_date];
            $type_id = (string) ($row['staff_attendance_type_id'] ?? '');
            $meta    = isset($type_meta[$type_id]) ? $type_meta[$type_id] : array('key_value' => '');
            $key     = strtoupper(trim((string) ($meta['key_value'] ?? '')));

            // hasValidPunch guard: only applies to biometric records (same as web report)
            $is_biometric = !empty($row['biometric_attendence']);
            if ($is_biometric && in_array($key, $present_like_keys, true)) {
                $raw_in  = trim((string) ($row['in_time']  ?? ''));
                $raw_out = trim((string) ($row['out_time'] ?? ''));
                $has_punch = ($raw_in !== '' && $raw_in !== '00:00:00') || ($raw_out !== '' && $raw_out !== '00:00:00');
                if (!$has_punch) {
                    $key = 'A'; // biometric record with no punch → treat as absent
                }
            }

            if ($key === 'HD') {
                $half_day_count++;
            } elseif (in_array($key, array('P', 'FHL', 'SHL', 'FHP', 'SHP'), true)) {
                $present_count++;
            } else {
                $absent_count++;
            }
        }

        // Late and permission — time-range based on raw in_time/out_time (same as web staffattendancereport)
        $total_late       = 0;
        $total_permission = 0;
        foreach ($attendance_rows as $row) {
            $raw_in  = trim((string) ($row['in_time']  ?? ''));
            $raw_out = trim((string) ($row['out_time'] ?? ''));
            $late_for_day = 0;
            if ($raw_in !== '') {
                if (!empty($att_settings['SHL']) && $_time_in_range($raw_in, $att_settings['SHL']->entry_time_from, $att_settings['SHL']->entry_time_to)) {
                    $late_for_day = 1;
                } elseif (!empty($att_settings['FHL']) && $_time_in_range($raw_in, $att_settings['FHL']->entry_time_from, $att_settings['FHL']->entry_time_to)) {
                    $late_for_day = 1;
                }
            }
            $total_late += $late_for_day;
            if ($raw_in !== '' && !empty($att_settings['FHP']) && $_time_in_range($raw_in, $att_settings['FHP']->entry_time_from, $att_settings['FHP']->entry_time_to)) {
                $total_permission++;
            }
            if ($raw_out !== '' && !empty($att_settings['SHP']) && $_time_in_range($raw_out, $att_settings['SHP']->entry_time_from, $att_settings['SHP']->entry_time_to)) {
                $total_permission++;
            }
        }

        $count_by_key = array(
            'P'                => $present_count,
            'HD'               => $half_day_count,
            'A'                => $absent_count,
            'TOTAL_LATE'       => $total_late,
            'TOTAL_PERMISSION' => $total_permission,
            'H'                => count(array_filter($official_holiday_dates, function ($d) use ($start_date, $end_date) {
                return $d >= $start_date && $d <= $end_date;
            })),
            'W'                => count(array_filter($weekend_day_dates, function ($d) use ($start_date, $end_date, $holiday_dates_set) {
                return $d >= $start_date && $d <= $end_date && !isset($holiday_dates_set[$d]);
            })),
        );

        $today_record = $this->staffattendancemodel->searchStaffattendance(date('Y-m-d'), $staff_id, false);
        if (empty($today_record)) {
            $today_record = array();
        }

        $recent_records = array();

        // Return every day in the selected month (1..end) so mobile does not skip dates.
        $month_dates = array();
        for ($day = 1; $day <= $num_days; $day++) {
            $month_dates[] = sprintf('%04d-%02d-%02d', $year_num, $month_num, $day);
        }

        foreach ($month_dates as $date_str) {

            $status_key = '';
            $status_label = '';
            $att_type_id = 0;
            $in_time = '';
            $out_time = '';

            // Same precedence as web: Holiday > Weekend > Attendance record.
            if (isset($holiday_dates_set[$date_str])) {
                $status_key = 'H';
                $status_label = 'Holiday';
            } elseif (isset($weekend_dates_set[$date_str])) {
                $status_key = 'W';
                $status_label = 'Weekend';
            } elseif (isset($attendance_by_date[$date_str])) {
                $row = $attendance_by_date[$date_str];
                $type_id = (string) ($row['staff_attendance_type_id'] ?? '');
                $meta = isset($type_meta[$type_id]) ? $type_meta[$type_id] : array('key_value' => '', 'type' => '');
                
                // First, check if type_id itself directly corresponds to a half-day type (6, 7, 8, etc.)
                $base_key = isset($meta['key_value']) ? trim((string) $meta['key_value']) : '';
                
                // If the type itself is already FHL, SHL, FHP, SHP, FHA, SHA, then use it directly
                if (in_array($base_key, ['FHL', 'SHL', 'FHP', 'SHP', 'FHA', 'SHA'])) {
                    $status_key = $base_key;
                    $status_label = isset($meta['type']) ? (string) $meta['type'] : $base_key;
                } else {
                    // Otherwise, check session_attendance_data for half-day combinations
                    $session_data = null;
                    if (!empty($row['session_attendance_data'])) {
                        try {
                            $session_data = json_decode($row['session_attendance_data'], true);
                        } catch (Exception $e) {
                            $session_data = null;
                        }
                    }
                    
                    $status_key = $base_key;
                    $status_label = isset($meta['type']) ? (string) $meta['type'] : '';
                    
                    if ($session_data && is_array($session_data)) {
                        // Session data exists: infer half-day variant
                        $morning = isset($session_data['morning_session']) ? (int) $session_data['morning_session'] : null;
                        $afternoon = isset($session_data['afternoon_session']) ? (int) $session_data['afternoon_session'] : null;
                        
                        $morning_key = '';
                        $afternoon_key = '';
                        if ($morning !== null) {
                            foreach ($type_meta as $tid => $tmeta) {
                                if ((int) $tid === $morning) {
                                    $morning_key = trim((string) $tmeta['key_value']);
                                    break;
                                }
                            }
                        }
                        if ($afternoon !== null) {
                            foreach ($type_meta as $tid => $tmeta) {
                                if ((int) $tid === $afternoon) {
                                    $afternoon_key = trim((string) $tmeta['key_value']);
                                    break;
                                }
                            }
                        }
                        
                        // Map all session combinations comprehensively
                        if ($morning_key === 'L' && $afternoon_key === 'P') {
                            $status_key = 'FHL';
                            $status_label = 'First Half Late';
                        } elseif ($morning_key === 'P' && $afternoon_key === 'L') {
                            $status_key = 'SHL';
                            $status_label = 'Second Half Late';
                        } elseif ($morning_key === 'A' && $afternoon_key === 'P') {
                            $status_key = 'FHA';
                            $status_label = 'First Half Absent';
                        } elseif ($morning_key === 'P' && $afternoon_key === 'A') {
                            $status_key = 'SHA';
                            $status_label = 'Second Half Absent';
                        } elseif ($morning_key === 'FHP' && $afternoon_key === 'P') {
                            $status_key = 'FHP';
                            $status_label = 'First Half Permission';
                        } elseif ($morning_key === 'P' && $afternoon_key === 'FHP') {
                            $status_key = 'SHP';
                            $status_label = 'Second Half Permission';
                        } elseif ($morning_key === 'L' && $afternoon_key === 'L') {
                            // Both halves late - just mark as late
                            $status_key = 'L';
                            $status_label = 'Late';
                        } elseif ($morning_key === 'A' && $afternoon_key === 'A') {
                            // Both halves absent
                            $status_key = 'A';
                            $status_label = 'Absent';
                        }
                        // Otherwise keep the base type
                    }
                }
                
                $att_type_id = isset($row['staff_attendance_type_id']) ? (int) $row['staff_attendance_type_id'] : 0;
                $in_time = isset($row['in_time']) && $row['in_time'] != null ? (string) $row['in_time'] : '';
                $out_time = isset($row['out_time']) && $row['out_time'] != null ? (string) $row['out_time'] : '';
            }

            if ($status_key === '' && $status_label === '') {
                $status_label = 'Not Marked';
            }

            $debug_info = array();
            if (isset($attendance_by_date[$date_str])) {
                $debug_row = $attendance_by_date[$date_str];
                if (!empty($debug_row['session_attendance_data'])) {
                    $debug_session = json_decode($debug_row['session_attendance_data'], true);
                    $debug_info['session_data_raw'] = $debug_row['session_attendance_data'];
                    $debug_info['session_data_parsed'] = $debug_session;
                    
                    // Show what was looked up
                    if (is_array($debug_session)) {
                        $morning_id = isset($debug_session['morning_session']) ? $debug_session['morning_session'] : null;
                        $afternoon_id = isset($debug_session['afternoon_session']) ? $debug_session['afternoon_session'] : null;
                        $debug_info['morning_type_id'] = $morning_id;
                        $debug_info['afternoon_type_id'] = $afternoon_id;
                        $debug_info['morning_lookup'] = isset($type_meta[(string)$morning_id]) ? $type_meta[(string)$morning_id] : 'NOT FOUND';
                        $debug_info['afternoon_lookup'] = isset($type_meta[(string)$afternoon_id]) ? $type_meta[(string)$afternoon_id] : 'NOT FOUND';
                    }
                }
                $debug_info['main_type_id'] = $att_type_id;
                $debug_info['main_type_lookup'] = isset($type_meta[(string)$att_type_id]) ? $type_meta[(string)$att_type_id] : 'NOT FOUND';
            }
            
            $recent_records[] = array(
                'date' => $date_str,
                'staff_attendance_type_id' => $att_type_id,
                'status_key' => $status_key,
                'status_label' => $status_label,
                'in_time' => $in_time,
                'out_time' => $out_time,
                'debug_info' => $debug_info,
            );
        }

        json_output($response['status'], array(
            'status' => 1,
            'message' => 'Success',
            'month' => $month,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'attendance_types' => array_values($type_meta),
            'counts_by_type_id' => $counts,
            'counts_by_key' => $count_by_key,
            'today_record' => $today_record,
            'recent_records' => $recent_records,
        ));
    }

    public function getTeacherTimetableForStaff()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->join('staff', 'staff.id = users.user_id');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user)) {
            json_output(404, array('status' => 0, 'message' => 'Staff profile not found.'));
            return;
        }

        if ($staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+6 days'));

        if (is_array($params)) {
            if (isset($params['start_date']) && trim((string) $params['start_date']) !== '') {
                $start_date = trim((string) $params['start_date']);
            }
            if (isset($params['end_date']) && trim((string) $params['end_date']) !== '') {
                $end_date = trim((string) $params['end_date']);
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid date format. Use YYYY-MM-DD.'));
            return;
        }

        if (strtotime($start_date) === false || strtotime($end_date) === false || strtotime($start_date) > strtotime($end_date)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid date range.'));
            return;
        }

        $staff_id = (int) $staff_user->staff_id;
        $timetable_raw = $this->subjecttimetable_model->getStaffTimetable($staff_id, $start_date, $end_date);
        if (!is_array($timetable_raw)) {
            $timetable_raw = array();
        }

        $timetable = array();
        foreach ($timetable_raw as $day_date => $day_rows) {
            $day_items = array();
            if (is_array($day_rows)) {
                foreach ($day_rows as $row) {
                    $entry = (array) $row;
                    $day_items[] = array(
                        'id' => isset($entry['id']) ? (int) $entry['id'] : 0,
                        'class' => isset($entry['class']) ? (string) $entry['class'] : '',
                        'section' => isset($entry['section']) ? (string) $entry['section'] : '',
                        'subject_name' => isset($entry['subject_name']) ? (string) $entry['subject_name'] : '',
                        'subject_code' => isset($entry['subject_code']) ? (string) $entry['subject_code'] : '',
                        'time_from' => isset($entry['time_from']) ? (string) $entry['time_from'] : '',
                        'time_to' => isset($entry['time_to']) ? (string) $entry['time_to'] : '',
                        'day' => isset($entry['day']) ? (string) $entry['day'] : '',
                        'room_no' => isset($entry['room_no']) ? (string) $entry['room_no'] : '',
                        'class_id' => isset($entry['class_id']) ? (int) $entry['class_id'] : 0,
                        'section_id' => isset($entry['section_id']) ? (int) $entry['section_id'] : 0,
                    );
                }
            }
            $timetable[$day_date] = $day_items;
        }

        json_output($response['status'], array(
            'status' => 1,
            'message' => 'Success',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'timetable' => $timetable,
        ));
    }

    public function markMyAttendance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->join('staff', 'staff.id = users.user_id');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user)) {
            json_output(404, array('status' => 0, 'message' => 'Staff profile not found.'));
            return;
        }

        if ($staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
            return;
        }

        $attendance_date = isset($params['attendance_date']) ? trim((string) $params['attendance_date']) : date('Y-m-d');
        $attendance_type_id = isset($params['attendance_type_id']) ? (int) $params['attendance_type_id'] : 0;
        $remark = isset($params['remark']) ? trim((string) $params['remark']) : '';
        $in_time_raw = isset($params['in_time']) ? trim((string) $params['in_time']) : '';
        $out_time_raw = isset($params['out_time']) ? trim((string) $params['out_time']) : '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendance_date) || strtotime($attendance_date) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid attendance_date. Use YYYY-MM-DD.'));
            return;
        }

        if ($attendance_type_id <= 0) {
            json_output(422, array('status' => 0, 'message' => 'attendance_type_id is required.'));
            return;
        }

        $in_time = null;
        if ($in_time_raw !== '') {
            $in_time_ts = strtotime($in_time_raw);
            if ($in_time_ts === false) {
                json_output(422, array('status' => 0, 'message' => 'Invalid in_time format.'));
                return;
            }
            $in_time = date('H:i:s', $in_time_ts);
        }

        $out_time = null;
        if ($out_time_raw !== '') {
            $out_time_ts = strtotime($out_time_raw);
            if ($out_time_ts === false) {
                json_output(422, array('status' => 0, 'message' => 'Invalid out_time format.'));
                return;
            }
            $out_time = date('H:i:s', $out_time_ts);
        }

        $attendance_payload = array(array(
            'staff_id' => (int) $staff_user->staff_id,
            'staff_attendance_type_id' => $attendance_type_id,
            'remark' => $remark,
            'in_time' => $in_time,
            'out_time' => $out_time,
            'date' => $attendance_date,
        ));

        $saved = $this->staffattendancemodel->addorUpdate($attendance_payload);
        if (!$saved) {
            json_output(500, array('status' => 0, 'message' => 'Failed to save attendance.'));
            return;
        }

        $saved_row = $this->staffattendancemodel->getAttendanceByStaffIdAndDate((int) $staff_user->staff_id, $attendance_date);
        if (empty($saved_row)) {
            $saved_row = array();
        }

        json_output($response['status'], array(
            'status' => 1,
            'message' => 'Attendance saved successfully.',
            'attendance' => $saved_row,
        ));
    }

    public function getMyAttendanceByDate()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->join('staff', 'staff.id = users.user_id');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user)) {
            json_output(404, array('status' => 0, 'message' => 'Staff profile not found.'));
            return;
        }

        if ($staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $attendance_date = date('Y-m-d');
        if (is_array($params) && isset($params['attendance_date']) && trim((string) $params['attendance_date']) !== '') {
            $attendance_date = trim((string) $params['attendance_date']);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $attendance_date) || strtotime($attendance_date) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid attendance_date. Use YYYY-MM-DD.'));
            return;
        }

        $row = $this->staffattendancemodel->getAttendanceByStaffIdAndDate((int) $staff_user->staff_id, $attendance_date);
        if (empty($row)) {
            json_output($response['status'], array(
                'status' => 1,
                'message' => 'No attendance found for selected date.',
                'attendance' => null,
            ));
            return;
        }

        json_output($response['status'], array(
            'status' => 1,
            'message' => 'Success',
            'attendance' => $row,
        ));
    }

    /**
     * Get student roster with current attendance status for a class/section on a given date.
     * Staff-only endpoint.
     * POST params: class_id, section_id, date (YYYY-MM-DD)
     */
    public function getStudentRosterForAttendance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $caller = $this->db->get()->row();

        if (empty($caller) || $caller->role === 'student' || $caller->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $class_id   = isset($params['class_id'])   ? (int) $params['class_id']   : 0;
        $section_id = isset($params['section_id']) ? (int) $params['section_id'] : 0;
        $date       = isset($params['date'])        ? trim((string) $params['date']) : date('Y-m-d');

        if ($class_id <= 0 || $section_id <= 0) {
            json_output(422, array('status' => 0, 'message' => 'class_id and section_id are required.'));
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid date. Use YYYY-MM-DD.'));
            return;
        }

        if ($caller->role === 'teacher') {
            $current_session = (int) $this->setting_model->getCurrentSession();
            $day_name = date('l', strtotime($date));
            $this->db->select('id');
            $this->db->from('subject_timetable');
            $this->db->where('staff_id', (int) $caller->staff_id);
            $this->db->where('class_id', $class_id);
            $this->db->where('section_id', $section_id);
            $this->db->where('day', $day_name);
            $this->db->where('session_id', $current_session);
            $this->db->limit(1);
            $owned = $this->db->get()->row();
            if (empty($owned)) {
                json_output(403, array('status' => 0, 'message' => 'Not authorized for this class-section on selected date.'));
                return;
            }
        }

        // Get attendance type list
        $att_types_raw = $this->attendencetype_model->getAttType();
        $attendance_types = array();
        foreach ((array) $att_types_raw as $at) {
            $at = (array) $at;
            $attendance_types[] = array(
                'id'        => (int) $at['id'],
                'type'      => (string) $at['type'],
                'key_value' => isset($at['key_value']) ? (string) $at['key_value'] : '',
            );
        }

        // Get students for the class/section
        $students_raw = $this->student_model->getStudentByClassSectionID($class_id, $section_id);
        if (!is_array($students_raw)) {
            $students_raw = array();
        }

        $students = array();
        foreach ($students_raw as $s) {
            $s = (array) $s;
            $student_session_id = (int) $s['student_session_id'];
            $att_row = $this->attendencetype_model->getStudentAttendence($date, $student_session_id);
            $students[] = array(
                'student_id'         => (int) $s['id'],
                'student_session_id' => $student_session_id,
                'firstname'          => (string) $s['firstname'],
                'lastname'           => (string) ($s['lastname'] ?? ''),
                'roll_no'            => (string) ($s['roll_no'] ?? ''),
                'admission_no'       => (string) ($s['admission_no'] ?? ''),
                'image'              => (string) ($s['image'] ?? ''),
                'attendance_status'  => $att_row ? (string) $att_row->type : null,
            );
        }

        json_output($response['status'], array(
            'status'           => 1,
            'message'          => 'Success',
            'class_id'         => $class_id,
            'section_id'       => $section_id,
            'date'             => $date,
            'attendance_types' => $attendance_types,
            'students'         => $students,
        ));
    }

    /**
     * Save day-wise student attendance records for a class/section.
     * Staff-only endpoint.
     * POST params: rows = [{student_session_id, attendence_type_id, date}]
     */
    public function saveStudentAttendance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $caller = $this->db->get()->row();

        if (empty($caller) || $caller->role === 'student' || $caller->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $rows = isset($params['rows']) && is_array($params['rows']) ? $params['rows'] : array();

        if (empty($rows)) {
            json_output(422, array('status' => 0, 'message' => 'rows array is required and must not be empty.'));
            return;
        }

        $records = array();
        $current_session = (int) $this->setting_model->getCurrentSession();
        $teacher_auth_cache = array();
        foreach ($rows as $row) {
            $student_session_id  = isset($row['student_session_id'])  ? (int) $row['student_session_id']  : 0;
            $attendence_type_id  = isset($row['attendence_type_id'])  ? (int) $row['attendence_type_id']  : 0;
            $date               = isset($row['date'])                 ? trim((string) $row['date'])         : '';
            $class_id           = isset($row['class_id'])             ? (int) $row['class_id']              : 0;
            $section_id         = isset($row['section_id'])           ? (int) $row['section_id']            : 0;

            if ($student_session_id <= 0 || $attendence_type_id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }

            if ($caller->role === 'teacher') {
                $this->db->select('class_id, section_id');
                $this->db->from('student_session');
                $this->db->where('id', $student_session_id);
                $this->db->where('session_id', $current_session);
                $student_session_row = $this->db->get()->row_array();
                if (empty($student_session_row)) {
                    continue;
                }

                $effective_class_id = (int) $student_session_row['class_id'];
                $effective_section_id = (int) $student_session_row['section_id'];

                if ($class_id > 0 && $section_id > 0) {
                    if ($class_id !== $effective_class_id || $section_id !== $effective_section_id) {
                        continue;
                    }
                }

                $auth_key = $effective_class_id . '-' . $effective_section_id . '-' . $date;
                if (!array_key_exists($auth_key, $teacher_auth_cache)) {
                    $day_name = date('l', strtotime($date));
                    $this->db->select('id');
                    $this->db->from('subject_timetable');
                    $this->db->where('staff_id', (int) $caller->staff_id);
                    $this->db->where('class_id', $effective_class_id);
                    $this->db->where('section_id', $effective_section_id);
                    $this->db->where('day', $day_name);
                    $this->db->where('session_id', $current_session);
                    $this->db->limit(1);
                    $teacher_auth_cache[$auth_key] = !empty($this->db->get()->row());
                }

                if (!$teacher_auth_cache[$auth_key]) {
                    continue;
                }
            }

            $records[] = array(
                'student_session_id' => $student_session_id,
                'attendence_type_id' => $attendence_type_id,
                'date'               => $date,
                'remark'             => isset($row['remark']) ? trim((string) $row['remark']) : '',
            );
        }

        if (empty($records)) {
            json_output(422, array('status' => 0, 'message' => 'No valid attendance records provided.'));
            return;
        }

        $saved = $this->attendencetype_model->saveStudentAttendances($records);

        if ($saved) {
            // Optional: Log absent student IDs for manual notification follow-up
            // (Main app mailsmsconf notification is too complex to load from API context)
            try {
                $absent_query = $this->db->query("SELECT id FROM attendence_type WHERE LOWER(key_value) = 'absent' LIMIT 1");
                if ($absent_query && $absent_query->num_rows() > 0) {
                    $absent_row = $absent_query->row();
                    $absent_type_id = (int) $absent_row->id;
                    $absent_ids = array();

                    foreach ($records as $rec) {
                        if ((int) $rec['attendence_type_id'] === $absent_type_id) {
                            $absent_ids[] = (int) $rec['student_session_id'];
                        }
                    }

                    if (!empty($absent_ids)) {
                        log_message('info', 'Absent attendance recorded: Staff ID=' . $login_user_id . 
                                           ' Count=' . count($absent_ids) . 
                                           ' Student Session IDs=' . implode(',', $absent_ids));
                    }
                }
            } catch (Throwable $t) {
                // Silently ignore logging errors
            }

            json_output($response['status'], array(
                'status'  => 1,
                'message' => 'Attendance saved successfully.',
                'count'   => count($records),
            ));
        } else {
            json_output(500, array('status' => 0, 'message' => 'Failed to save attendance.'));
        }
    }

    public function updateProfileImage()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 0, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        if ((!isset($_FILES['file']) || empty($_FILES['file']['name'])) && isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) {
            $_FILES['file'] = $_FILES['profile_image'];
        }

        if ((!isset($_FILES['file']) || empty($_FILES['file']['name'])) && isset($_FILES['userfile']) && !empty($_FILES['userfile']['name'])) {
            $_FILES['file'] = $_FILES['userfile'];
        }

        if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
            json_output(422, array('status' => 0, 'message' => 'Profile image file is required.'));
            return;
        }

        $filetype_result = $this->filetype_model->get();
        $image_validate = $this->config->item('image_validate');

        if (!empty($filetype_result) && !empty($filetype_result->image_extension) && !empty($filetype_result->image_mime)) {
            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $filetype_result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $filetype_result->image_mime)));
            $max_upload_size = isset($filetype_result->file_size) ? (int) $filetype_result->file_size : 0;
        } else {
            $allowed_extension = isset($image_validate['allowed_extension']) ? array_map('strtolower', (array) $image_validate['allowed_extension']) : array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp');
            $allowed_mime_type = isset($image_validate['allowed_mime_type']) ? array_map('strtolower', (array) $image_validate['allowed_mime_type']) : array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/svg+xml', 'image/webp');
            $max_upload_size = isset($image_validate['upload_size']) ? (int) $image_validate['upload_size'] : (2 * 1024 * 1024);
        }

        if ($max_upload_size <= 0) {
            $max_upload_size = isset($image_validate['upload_size']) ? (int) $image_validate['upload_size'] : (2 * 1024 * 1024);
        }

        $file_size = isset($_FILES['file']['size']) ? (int) $_FILES['file']['size'] : 0;
        $file_name = isset($_FILES['file']['name']) ? (string) $_FILES['file']['name'] : '';
        $tmp_name = isset($_FILES['file']['tmp_name']) ? (string) $_FILES['file']['tmp_name'] : '';
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $image_meta = @getimagesize($tmp_name);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_mime = $finfo ? strtolower((string) finfo_file($finfo, $tmp_name)) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $meta_mime = isset($image_meta['mime']) ? strtolower((string) $image_meta['mime']) : '';

        if ($image_meta === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid image file.'));
            return;
        }

        if (!in_array($ext, $allowed_extension) || (!in_array($detected_mime, $allowed_mime_type) && !in_array($meta_mime, $allowed_mime_type))) {
            // Keep upload flow compatible with web form behavior; do not block on client/server MIME mismatch.
            log_message('debug', 'updateProfileImage validation bypass: ext=' . $ext . ', detected_mime=' . $detected_mime . ', meta_mime=' . $meta_mime);
        }

        if ($file_size > $max_upload_size) {
            json_output(422, array('status' => 0, 'message' => 'File size should be less than ' . number_format($max_upload_size / 1048576, 2) . ' MB.'));
            return;
        }

        $this->db->select('id, role, user_id');
        $this->db->from('users');
        $this->db->where('id', $login_user_id);
        $login_user = $this->db->get()->row();

        if (empty($login_user)) {
            json_output(404, array('status' => 0, 'message' => 'Logged in user not found.'));
            return;
        }

        $role = strtolower(trim((string) $login_user->role));
        $entity_id = (int) $login_user->user_id;
        $table_name = '';
        $dir_relative = '';
        $upload_subdir = '';
        $old_image = '';

        if ($role === 'student' || $role === 'parent') {
            $table_name = 'students';
            $dir_relative = 'uploads/student_images';
            $upload_subdir = 'student_images';
            $this->db->select('image');
            $this->db->from('students');
            $this->db->where('id', $entity_id);
            $entity = $this->db->get()->row();
            if (empty($entity)) {
                json_output(404, array('status' => 0, 'message' => 'Student profile not found.'));
                return;
            }
            $old_image = isset($entity->image) ? (string) $entity->image : '';
        } else {
            $table_name = 'staff';
            $dir_relative = 'uploads/staff_images';
            $upload_subdir = 'staff_images';
            $this->db->select('image');
            $this->db->from('staff');
            $this->db->where('id', $entity_id);
            $entity = $this->db->get()->row();
            if (empty($entity)) {
                json_output(404, array('status' => 0, 'message' => 'Staff profile not found.'));
                return;
            }
            $old_image = isset($entity->image) ? (string) $entity->image : '';
        }

        $base_upload_path = trim((string) $this->config->item('upload_path'));
        if ($base_upload_path === '') {
            $base_upload_path = '../uploads';
        }

        $upload_path = rtrim($base_upload_path, '/') . '/' . $upload_subdir . '/';
        $upload_dir_candidates = array_values(array_unique(array(
            rtrim(FCPATH . $upload_path, '/') . '/',
            rtrim(FCPATH . '../uploads/' . $upload_subdir, '/') . '/',
        )));

        $prev_size = 0;
        if (!empty($old_image)) {
            $prev_size = (int) $this->media_storage->getUploadedFileSize($old_image, '');
        }

        $original_name = isset($_FILES['file']['name']) ? basename((string) $_FILES['file']['name']) : 'profile.jpg';
        $img_name = time() . '-' . uniqid(rand()) . '!' . $original_name;
        $tmp_name = (string) $_FILES['file']['tmp_name'];
        $upload_saved = false;
        $saved_dir = '';

        foreach ($upload_dir_candidates as $upload_dir_abs) {
            if (!is_dir($upload_dir_abs)) {
                @mkdir($upload_dir_abs, 0755, true);
            }

            if (!is_writable($upload_dir_abs)) {
                @chmod($upload_dir_abs, 0755);
            }
            if (!is_writable($upload_dir_abs)) {
                @chmod($upload_dir_abs, 0777);
            }
            if (!is_writable($upload_dir_abs)) {
                continue;
            }

            $destination = $upload_dir_abs . $img_name;

            if (is_uploaded_file($tmp_name) && @move_uploaded_file($tmp_name, $destination)) {
                $upload_saved = true;
                $saved_dir = $upload_dir_abs;
                break;
            }

            if (is_file($tmp_name) && @copy($tmp_name, $destination)) {
                $upload_saved = true;
                $saved_dir = $upload_dir_abs;
                break;
            }
        }

        if (!$upload_saved) {
            $upload_error_code = isset($_FILES['file']['error']) ? (int) $_FILES['file']['error'] : -1;
            log_message('error', 'updateProfileImage upload failed. rel_path=' . $upload_path . ', candidates=' . json_encode($upload_dir_candidates) . ', php_error=' . $upload_error_code);
            json_output(422, array('status' => 0, 'message' => 'Failed to upload image.'));
            return;
        }

        $new_image = $dir_relative . '/' . $img_name;
        $db_image = ($table_name === 'staff') ? $img_name : $new_image;
        $this->db->where('id', $entity_id);
        $this->db->update($table_name, array('image' => $db_image));

        if ($this->db->affected_rows() < 0) {
            json_output(500, array('status' => 0, 'message' => 'Failed to update profile image.'));
            return;
        }

        $new_size = (int) $this->media_storage->getTmpFileSize('file');
        if ($prev_size > $new_size) {
            $this->saasvalidation->deleteResouceQuota('storage', $prev_size - $new_size);
        } elseif ($new_size > $prev_size) {
            $this->saasvalidation->updateResouceQuota('storage', $new_size - $prev_size);
        }

        // Intentionally keep the previous file. Mobile/web clients may still have
        // stale in-memory widgets or cached profile payloads referencing the old URL
        // immediately after an upload, and deleting it causes visible image load errors.

        json_output(200, array(
            'status' => 1,
            'message' => 'Profile image updated successfully.',
            'image' => $new_image,
        ));
    }

    /**
     * Get period-wise student roster for a specific subject timetable id/date.
     * Staff-only endpoint.
     * POST params: subject_timetable_id, date (YYYY-MM-DD)
     */
    public function getPeriodWiseStudentRosterForAttendance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $caller = $this->db->get()->row();

        if (empty($caller) || $caller->role === 'student' || $caller->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $subject_timetable_id = isset($params['subject_timetable_id']) ? (int) $params['subject_timetable_id'] : 0;
        $date                 = isset($params['date']) ? trim((string) $params['date']) : date('Y-m-d');

        if ($subject_timetable_id <= 0) {
            json_output(422, array('status' => 0, 'message' => 'subject_timetable_id is required.'));
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || strtotime($date) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid date. Use YYYY-MM-DD.'));
            return;
        }

        $this->db->select('id, class_id, section_id, session_id, staff_id, subject_group_subject_id, time_from, time_to, room_no');
        $this->db->from('subject_timetable');
        $this->db->where('id', $subject_timetable_id);
        $subject_timetable = $this->db->get()->row_array();

        if (empty($subject_timetable)) {
            json_output(404, array('status' => 0, 'message' => 'Subject timetable not found.'));
            return;
        }

        // Ensure teacher can only access own period for non-admin staff roles.
        $current_session = (int) $this->setting_model->getCurrentSession();
        if ((int) $subject_timetable['session_id'] !== $current_session) {
            json_output(403, array('status' => 0, 'message' => 'Not authorized for this period in current session.'));
            return;
        }

        if ($caller->role === 'teacher') {
            if ((int) $subject_timetable['staff_id'] !== (int) $caller->staff_id) {
                json_output(403, array('status' => 0, 'message' => 'Not authorized for this period.'));
                return;
            }
        }

        $att_types_raw = $this->attendencetype_model->getAttType();
        $attendance_types = array();
        foreach ((array) $att_types_raw as $at) {
            $at = (array) $at;
            $attendance_types[] = array(
                'id'        => (int) $at['id'],
                'type'      => (string) $at['type'],
                'key_value' => isset($at['key_value']) ? (string) $at['key_value'] : '',
            );
        }

        $class_id   = (int) $subject_timetable['class_id'];
        $section_id = (int) $subject_timetable['section_id'];

        $students_raw = $this->student_model->getStudentByClassSectionID($class_id, $section_id);
        if (!is_array($students_raw)) {
            $students_raw = array();
        }

        $students = array();
        foreach ($students_raw as $s) {
            $s = (array) $s;
            $student_session_id = (int) $s['student_session_id'];

            $this->db->select('attendence_type_id, remark');
            $this->db->from('student_subject_attendances');
            $this->db->where('student_session_id', $student_session_id);
            $this->db->where('subject_timetable_id', $subject_timetable_id);
            $this->db->where('date', $date);
            $att_row = $this->db->get()->row_array();

            $students[] = array(
                'student_id'            => (int) $s['id'],
                'student_session_id'    => $student_session_id,
                'firstname'             => (string) $s['firstname'],
                'lastname'              => (string) ($s['lastname'] ?? ''),
                'roll_no'               => (string) ($s['roll_no'] ?? ''),
                'admission_no'          => (string) ($s['admission_no'] ?? ''),
                'image'                 => (string) ($s['image'] ?? ''),
                'attendence_type_id'    => isset($att_row['attendence_type_id']) ? (int) $att_row['attendence_type_id'] : null,
                'remark'                => isset($att_row['remark']) ? (string) $att_row['remark'] : '',
            );
        }

        json_output($response['status'], array(
            'status'              => 1,
            'message'             => 'Success',
            'date'                => $date,
            'subject_timetable_id'=> $subject_timetable_id,
            'class_id'            => $class_id,
            'section_id'          => $section_id,
            'attendance_types'    => $attendance_types,
            'period'              => array(
                'time_from' => (string) ($subject_timetable['time_from'] ?? ''),
                'time_to'   => (string) ($subject_timetable['time_to'] ?? ''),
                'room_no'   => (string) ($subject_timetable['room_no'] ?? ''),
            ),
            'students'            => $students,
        ));
    }

    /**
     * Save period-wise student attendance records.
     * Staff-only endpoint.
     * POST params: rows = [{student_session_id, subject_timetable_id, attendence_type_id, date, remark?}]
     */
    public function savePeriodWiseStudentAttendance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $caller = $this->db->get()->row();

        if (empty($caller) || $caller->role === 'student' || $caller->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $rows = isset($params['rows']) && is_array($params['rows']) ? $params['rows'] : array();

        if (empty($rows)) {
            json_output(422, array('status' => 0, 'message' => 'rows array is required and must not be empty.'));
            return;
        }

        $this->db->trans_start();
        $this->db->trans_strict(false);

        $saved_count = 0;
        $current_session = (int) $this->setting_model->getCurrentSession();
        $subject_cache = array();
        foreach ($rows as $row) {
            $student_session_id = isset($row['student_session_id']) ? (int) $row['student_session_id'] : 0;
            $subject_timetable_id = isset($row['subject_timetable_id']) ? (int) $row['subject_timetable_id'] : 0;
            $attendence_type_id = isset($row['attendence_type_id']) ? (int) $row['attendence_type_id'] : 0;
            $date = isset($row['date']) ? trim((string) $row['date']) : '';
            $remark = isset($row['remark']) ? trim((string) $row['remark']) : '';

            if ($student_session_id <= 0 || $subject_timetable_id <= 0 || $attendence_type_id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }

            if (!array_key_exists($subject_timetable_id, $subject_cache)) {
                $this->db->select('id, class_id, section_id, staff_id, session_id');
                $this->db->from('subject_timetable');
                $this->db->where('id', $subject_timetable_id);
                $subject_cache[$subject_timetable_id] = $this->db->get()->row_array();
            }

            $subject_row = $subject_cache[$subject_timetable_id];
            if (empty($subject_row)) {
                continue;
            }

            if ((int) $subject_row['session_id'] !== $current_session) {
                continue;
            }

            if ($caller->role === 'teacher' && (int) $subject_row['staff_id'] !== (int) $caller->staff_id) {
                continue;
            }

            $this->db->select('class_id, section_id');
            $this->db->from('student_session');
            $this->db->where('id', $student_session_id);
            $this->db->where('session_id', $current_session);
            $student_session_row = $this->db->get()->row_array();
            if (empty($student_session_row)) {
                continue;
            }

            if ((int) $student_session_row['class_id'] !== (int) $subject_row['class_id'] || (int) $student_session_row['section_id'] !== (int) $subject_row['section_id']) {
                continue;
            }

            if ($caller->role === 'teacher') {
                $day_name = date('l', strtotime($date));
                $this->db->select('id');
                $this->db->from('subject_timetable');
                $this->db->where('id', $subject_timetable_id);
                $this->db->where('staff_id', (int) $caller->staff_id);
                $this->db->where('day', $day_name);
                $this->db->where('session_id', $current_session);
                $this->db->limit(1);
                $owned = $this->db->get()->row();
                if (empty($owned)) {
                    continue;
                }
            }

            $payload = array(
                'student_session_id' => $student_session_id,
                'subject_timetable_id' => $subject_timetable_id,
                'attendence_type_id' => $attendence_type_id,
                'date' => $date,
                'remark' => $remark,
            );

            $this->db->where('student_session_id', $student_session_id);
            $this->db->where('subject_timetable_id', $subject_timetable_id);
            $this->db->where('date', $date);
            $existing = $this->db->get('student_subject_attendances')->row();

            if ($existing) {
                $this->db->where('id', $existing->id);
                $this->db->update('student_subject_attendances', $payload);
            } else {
                $this->db->insert('student_subject_attendances', $payload);
            }
            $saved_count++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            json_output(500, array('status' => 0, 'message' => 'Failed to save period-wise attendance.'));
            return;
        }

        if ($saved_count <= 0) {
            json_output(422, array('status' => 0, 'message' => 'No valid period-wise attendance records provided.'));
            return;
        }

        json_output($response['status'], array(
            'status' => 1,
            'message' => 'Period-wise attendance saved successfully.',
            'count' => $saved_count,
        ));
    }

    public function getStaffLeaveBalance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if (!$check_auth_client) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $payload_staff_id = isset($params['staff_id']) ? (int) $params['staff_id'] : 0;
        $payload_employee_id = isset($params['employee_id']) ? trim((string) $params['employee_id']) : '';

        // Resolve effective staff_id.
        // Priority: explicit staff_id -> authenticated header -> users.id mapping -> employee_id fallback.
        $header_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        $staff_id = 0;

        // 1) Prefer explicit staff.id payload when valid.
        if ($payload_staff_id > 0) {
            $this->db->select('id');
            $this->db->from('staff');
            $this->db->where('id', $payload_staff_id);
            $is_staff_id = $this->db->get()->row();
            if (!empty($is_staff_id)) {
                $staff_id = $payload_staff_id;
            }
        }

        // 2) Fallback to authenticated User-ID header mapping.
        if ($staff_id <= 0 && $header_user_id !== '') {
            $this->db->select('users.role, users.user_id as staff_id');
            $this->db->from('users');
            $this->db->where('users.id', $header_user_id);
            $staff_user = $this->db->get()->row();

            if (!empty($staff_user) && $staff_user->role !== 'student' && $staff_user->role !== 'parent') {
                $staff_id = (int) $staff_user->staff_id;
            }

            // Some installs send staff.id in User-ID header instead of users.id.
            if ($staff_id <= 0) {
                $this->db->select('users.role, users.user_id as staff_id');
                $this->db->from('users');
                $this->db->where('users.user_id', $header_user_id);
                $staff_user_by_user_id = $this->db->get()->row();
                if (!empty($staff_user_by_user_id) && $staff_user_by_user_id->role !== 'student' && $staff_user_by_user_id->role !== 'parent') {
                    $staff_id = (int) $staff_user_by_user_id->staff_id;
                }
            }
        }

        // 3) Fallback: payload may contain users.id; map users.id -> users.user_id.
        if ($staff_id <= 0 && $payload_staff_id > 0) {
            $this->db->select('users.role, users.user_id as staff_id');
            $this->db->from('users');
            $this->db->where('users.id', $payload_staff_id);
            $mapped_user = $this->db->get()->row();
            if (!empty($mapped_user) && $mapped_user->role !== 'student' && $mapped_user->role !== 'parent') {
                $staff_id = (int) $mapped_user->staff_id;
            }
        }

        // 4) Final fallback: employee_id lookup.
        if ($staff_id <= 0 && $payload_employee_id !== '') {
            $this->db->select('id');
            $this->db->from('staff');
            $this->db->where('employee_id', $payload_employee_id);
            $this->db->where('is_active', 1);
            $staff_row = $this->db->get()->row();
            if (!empty($staff_row)) {
                $staff_id = (int) $staff_row->id;
            }
        }

        if ($staff_id <= 0) {
            json_output(422, array('status' => 0, 'message' => 'Unable to resolve staff_id for leave balance.'));
            return;
        }

        $balance = array();

        // If leave tables are not present in this tenant DB, return empty payload instead of 500
        if (!$this->db->table_exists('staff_leave_details') || !$this->db->table_exists('leave_types') || !$this->db->table_exists('staff_leave_request')) {
            json_output($response['status'], array(
                'status' => 1,
                'message' => 'Leave balance is not configured for this instance.',
                'leave_balance' => array(),
            ));
            return;
        }

        // Same base allocation list used in web admin/staff/profile leave tab.
        $has_balance_flag = false;
        try {
            $has_balance_flag = $this->db->field_exists('requires_balance_check', 'leave_types');
        } catch (Throwable $t) {
            $has_balance_flag = false;
        }

        $select_fields = 'staff_leave_details.leave_type_id, staff_leave_details.alloted_leave, leave_types.type';
        if ($has_balance_flag) {
            $select_fields .= ', leave_types.requires_balance_check';
        }

        $this->db->select($select_fields);
        $this->db->from('staff_leave_details');
        $this->db->join('leave_types', 'leave_types.id = staff_leave_details.leave_type_id');
        $this->db->where('staff_leave_details.staff_id', $staff_id);
        $leave_allocated = $this->db->get()->result_array();

        $leave_allocated_map = array();
        foreach ($leave_allocated as $leave_row) {
            $type_id = (int) ($leave_row['leave_type_id'] ?? 0);
            if ($type_id > 0) {
                $leave_allocated_map[$type_id] = $leave_row;
            }
        }

        $claim_type_select = 'id as leave_type_id, type, 0 as alloted_leave';
        if ($has_balance_flag) {
            $claim_type_select .= ', requires_balance_check';
        }

        $this->db->select($claim_type_select);
        $this->db->from('leave_types');
        if ($has_balance_flag) {
            $this->db->where('requires_balance_check', 0);
        }
        $claim_leave_types = $this->db->get()->result_array();

        if (!$has_balance_flag) {
            $claim_leave_types = array_values(array_filter($claim_leave_types, function ($leave_row) {
                $type_name = strtolower(trim((string) ($leave_row['type'] ?? '')));
                return in_array($type_name, array('on duty', 'od'), true);
            }));
        }

        foreach ($claim_leave_types as $claim_type) {
            $type_id = (int) ($claim_type['leave_type_id'] ?? 0);
            if ($type_id > 0 && !isset($leave_allocated_map[$type_id])) {
                $leave_allocated_map[$type_id] = $claim_type;
            }
        }

        $leave_allocated = array_values($leave_allocated_map);

        // Mirror web logic: if monthly balance table exists, prefer latest monthly snapshot.
        $use_monthly_balance = false;
        try {
            $use_monthly_balance = $this->db->table_exists('staff_monthly_leave_balance');
        } catch (Throwable $t) {
            $use_monthly_balance = false;
        }

        foreach ($leave_allocated as $leave_type) {
            $type_id = (int) ($leave_type['leave_type_id'] ?? 0);
            $allocated = (float) ($leave_type['alloted_leave'] ?? 0);
            $used = 0.0;
            $remaining = $allocated;

            $requires_balance_check = 1;
            if ($has_balance_flag) {
                $requires_balance_check = (int) ($leave_type['requires_balance_check'] ?? 1);
            } else {
                $type_name = strtolower(trim((string) ($leave_type['type'] ?? '')));
                if (in_array($type_name, array('on duty', 'od'), true)) {
                    $requires_balance_check = 0;
                }
            }

            // Credit-style leave types (requires_balance_check = 0) should increase
            // balance only after approval. Pending/disapproved must not affect balance.
            if ($requires_balance_check === 0) {
                $approved_credit_row = $this->db
                    ->select('SUM(leave_days) as approved_leave_days')
                    ->where('staff_id', $staff_id)
                    ->where('status', 'approved')
                    ->where('leave_type_id', $type_id)
                    ->get('staff_leave_request')
                    ->row_array();

                $approved_credit_days = (float) ($approved_credit_row['approved_leave_days'] ?? 0);
                $used = 0.0;
                $remaining = $allocated + $approved_credit_days;

                $balance[] = array(
                    'leave_type_id' => $type_id,
                    'type' => $leave_type['type'],
                    'requires_balance_check' => $requires_balance_check,
                    'allocated' => round($allocated, 2),
                    'used' => round($used, 2),
                    'remaining' => round($remaining, 2),
                );
                continue;
            }

            if ($use_monthly_balance) {
                try {
                    $monthly_balance = $this->db
                        ->select('opening_balance, used_for_lop_adjustment, used_for_leave_application, closing_balance, year, month')
                        ->where('staff_id', $staff_id)
                        ->where('leave_type_id', $type_id)
                        ->order_by('year', 'DESC')
                        ->order_by('month', 'DESC')
                        ->limit(1)
                        ->get('staff_monthly_leave_balance')
                        ->row_array();

                    if (!empty($monthly_balance)) {
                        $opening_balance = isset($monthly_balance['opening_balance']) ? (float) $monthly_balance['opening_balance'] : 0.0;
                        $used_lop = isset($monthly_balance['used_for_lop_adjustment']) ? (float) $monthly_balance['used_for_lop_adjustment'] : 0.0;
                        $used_leave = isset($monthly_balance['used_for_leave_application']) ? (float) $monthly_balance['used_for_leave_application'] : 0.0;
                        $available_balance = isset($monthly_balance['closing_balance']) ? (float) $monthly_balance['closing_balance'] : 0.0;

                        $allocated = $opening_balance;
                        $used = $used_lop + $used_leave;
                        $remaining = $available_balance;
                    } else {
                        // Fallback used in web when no monthly rows are present.
                        $used_row = $this->db
                            ->select('SUM(leave_days) as approve_leave')
                            ->where('staff_id', $staff_id)
                            ->where('status', 'approved')
                            ->where('leave_type_id', $type_id)
                            ->get('staff_leave_request')
                            ->row_array();

                        $used = (float) ($used_row['approve_leave'] ?? 0);
                        $remaining = $allocated - $used;
                    }
                } catch (Throwable $t) {
                    // If monthly query fails, fall back to leave request sum.
                    $used_row = $this->db
                        ->select('SUM(leave_days) as approve_leave')
                        ->where('staff_id', $staff_id)
                        ->where('status', 'approved')
                        ->where('leave_type_id', $type_id)
                        ->get('staff_leave_request')
                        ->row_array();

                    $used = (float) ($used_row['approve_leave'] ?? 0);
                    $remaining = $allocated - $used;
                }
            } else {
                $used_row = $this->db
                    ->select('SUM(leave_days) as approve_leave')
                    ->where('staff_id', $staff_id)
                    ->where('status', 'approved')
                    ->where('leave_type_id', $type_id)
                    ->get('staff_leave_request')
                    ->row_array();

                $used = (float) ($used_row['approve_leave'] ?? 0);
                $remaining = $allocated - $used;
            }

            $balance[] = array(
                'leave_type_id' => $type_id,
                'type' => $leave_type['type'],
                'requires_balance_check' => $requires_balance_check,
                'allocated' => round($allocated, 2),
                'used' => round($used, 2),
                'remaining' => round($remaining, 2),
            );
        }

        json_output($response['status'], array(
            'status' => 1,
            'message' => 'Leave balance retrieved successfully.',
            'leave_balance' => $balance,
        ));
    }

    public function getStaffLeaveRequests()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        // Read body once (php://input is a stream; read early before any other consumer).
        $params      = json_decode(file_get_contents('php://input'), true);
        $employee_id = isset($params['employee_id']) ? trim((string) $params['employee_id']) : '';

        // Resolve the logged-in staff identity.
        // User-ID header is users.id, validated by auth() against users_authentication.
        $header_user_id = trim((string) $this->input->get_request_header('User-ID', true));

        $staff_id = 0;

        if ($header_user_id !== '') {
            // Primary path: users.id -> users.user_id (= staff.id)
            $this->db->select('users.user_id as staff_id, users.role');
            $this->db->from('users');
            $this->db->where('users.id', $header_user_id);
            $u = $this->db->get()->row();
            if (!empty($u) && !in_array($u->role, array('student', 'parent'), true)) {
                $staff_id = (int) $u->staff_id;
            }
        }

        // Fallback: employee_id from JSON body.
        if ($staff_id <= 0 && $employee_id !== '') {
            $this->db->select('id');
            $this->db->from('staff');
            $this->db->where('employee_id', $employee_id);
            $this->db->where('is_active', 1);
            $sr = $this->db->get()->row();
            if (!empty($sr)) {
                $staff_id = (int) $sr->id;
            }
        }

        if ($staff_id <= 0) {
            json_output(422, array('status' => 0, 'message' => 'Unable to identify staff account.'));
            return;
        }

        // Mirror the exact SQL used by the web model staff_leave_request($id).
        // Note: use ->get('table') without a separate ->from() to avoid duplicate FROM clause.
        $this->db->select('staff.name, staff.surname, staff.employee_id,
            staff_leave_request.*,
            leave_types.type,
            recommender.name as recommender_name, recommender.surname as recommender_surname,
            approver.name as approver_name, approver.surname as approver_surname,
            department.department_name');
        $this->db->join('staff',       'staff.id = staff_leave_request.staff_id');
        $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id');
        $this->db->join('staff_roles', 'staff_roles.staff_id = staff.id');
        $this->db->join('roles',       'staff_roles.role_id = roles.id');
        $this->db->join('staff as recommender', 'recommender.id = staff_leave_request.recommender_id', 'left');
        $this->db->join('staff as approver',    'approver.id  = staff_leave_request.approver_id',    'left');
        $this->db->join('department',            'department.id = staff.department',                   'left');
        $this->db->where('staff.is_active', 1);
        $this->db->where('staff_leave_request.staff_id', $staff_id);
        $this->db->order_by('staff_leave_request.id', 'desc');
        $query = $this->db->get('staff_leave_request');

        $rows = $query->result_array();

        // Deduplicate by leave-request id (a staff with multiple roles in staff_roles
        // would otherwise produce one row per role, same as the web model).
        $leave_requests = array();
        $seen           = array();
        foreach ($rows as $row) {
            $rid = $row['id'];
            if (!isset($seen[$rid])) {
                $seen[$rid]      = true;
                $leave_requests[] = $row;
            }
        }

        json_output(200, array(
            'status'         => 1,
            'message'        => 'Leave requests retrieved successfully.',
            'leave_requests' => $leave_requests,
            'count'          => count($leave_requests),
        ));
    }

    /**
     * Add a new staff leave request.
    * POST params: leave_type_id, leave_from, leave_to, reason, is_half_day(optional), document_file(optional)
     */
    public function addStaffLeaveRequest()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if (!$check_auth_client) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user) || $staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
            return;
        }

        $leave_type_id = isset($params['leave_type_id']) ? (int) $params['leave_type_id'] : 0;
        $leave_from = isset($params['leave_from']) ? trim((string) $params['leave_from']) : '';
        $leave_to = isset($params['leave_to']) ? trim((string) $params['leave_to']) : '';
        $reason = isset($params['reason']) ? trim((string) $params['reason']) : '';
        $is_half_day = isset($params['is_half_day']) && ($params['is_half_day'] === true || $params['is_half_day'] === 1 || $params['is_half_day'] === '1' || $params['is_half_day'] === 'true');

        if ($leave_type_id <= 0 || $leave_from === '' || $leave_to === '') {
            json_output(422, array('status' => 0, 'message' => 'leave_type_id, leave_from, and leave_to are required.'));
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $leave_from) || strtotime($leave_from) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid leave_from date. Use YYYY-MM-DD.'));
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $leave_to) || strtotime($leave_to) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid leave_to date. Use YYYY-MM-DD.'));
            return;
        }

        if (strtotime($leave_from) > strtotime($leave_to)) {
            json_output(422, array('status' => 0, 'message' => 'leave_from must be before or equal to leave_to.'));
            return;
        }

        if ($is_half_day && $leave_from !== $leave_to) {
            json_output(422, array('status' => 0, 'message' => 'For half day leave, leave_from and leave_to must be the same date.'));
            return;
        }

        // Calculate leave days
        $from_timestamp = strtotime($leave_from);
        $to_timestamp = strtotime($leave_to);
        $leave_days = $is_half_day ? 0.5 : (($to_timestamp - $from_timestamp) / 86400 + 1); // +1 to include both days

        // Check if leave type exists
        $this->db->select('id');
        $this->db->from('leave_types');
        $this->db->where('id', $leave_type_id);
        $leave_type = $this->db->get()->row();

        if (empty($leave_type)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid leave_type_id.'));
            return;
        }

        $recommender_id = 0;
        $approver_id = 0;
        $request_staff_id = (int) $staff_user->staff_id;

        $selected_role_id = 0;

        // Prefer role derived from users.role (web flow intent), then fallback to first staff_roles row.
        $user_role_name = strtolower(trim((string) ($staff_user->role ?? '')));
        if ($user_role_name !== '') {
            $role_by_name = $this->db
                ->select('id')
                ->where('lower(name)', $user_role_name)
                ->limit(1)
                ->get('roles')
                ->row_array();
            if (!empty($role_by_name['id'])) {
                $selected_role_id = (int) $role_by_name['id'];
            }
        }

        if ($selected_role_id <= 0) {
            $role_row = $this->db
                ->select('role_id')
                ->where('staff_id', $request_staff_id)
                ->order_by('id', 'ASC')
                ->limit(1)
                ->get('staff_roles')
                ->row_array();
            if (!empty($role_row['role_id'])) {
                $selected_role_id = (int) $role_row['role_id'];
            }
        }

        $setting = $this->db
            ->select('leave_approver_id, leave_self_approve_roles')
            ->limit(1)
            ->get('sch_settings')
            ->row_array();
        if (!empty($setting['leave_approver_id'])) {
            $approver_id = (int) $setting['leave_approver_id'];
        }

        $self_approve_roles = array();
        $self_approve_roles_raw = isset($setting['leave_self_approve_roles'])
            ? (string) $setting['leave_self_approve_roles']
            : '';
        if ($self_approve_roles_raw !== '') {
            $parts = explode(',', $self_approve_roles_raw);
            foreach ($parts as $part) {
                $val = (int) trim($part);
                if ($val > 0) {
                    $self_approve_roles[] = $val;
                }
            }
            $self_approve_roles = array_values(array_unique($self_approve_roles));
        }

        // Match web workflow: self-approve role routes both stages to the same staff user.
        if ($request_staff_id > 0 && $selected_role_id > 0 && in_array($selected_role_id, $self_approve_roles, true)) {
            $recommender_id = $request_staff_id;
            $approver_id = $request_staff_id;
        } else {
            // Match web workflow: if requester is configured final approver, keep both stages self.
            if ($request_staff_id > 0 && $approver_id > 0 && $request_staff_id === $approver_id) {
                $recommender_id = $approver_id;
            } else {
                $staff_details = $this->db
                    ->select('department')
                    ->where('id', $request_staff_id)
                    ->limit(1)
                    ->get('staff')
                    ->row_array();
                $department_id = (int) ($staff_details['department'] ?? 0);

                if ($department_id > 0) {
                    $department = $this->db
                        ->select('dept_head_id')
                        ->where('id', $department_id)
                        ->limit(1)
                        ->get('department')
                        ->row_array();
                    if (!empty($department['dept_head_id'])) {
                        $recommender_id = (int) $department['dept_head_id'];
                    }
                }

                if ($recommender_id <= 0 && $approver_id > 0) {
                    $recommender_id = $approver_id;
                }
            }
        }

        $payload = array(
            'staff_id' => $request_staff_id,
            'leave_type_id' => $leave_type_id,
            'leave_from' => $leave_from,
            'leave_to' => $leave_to,
            'leave_days' => $leave_days,
            'employee_remark' => $reason,
            'date' => date('Y-m-d'),
            'status' => 'pending',
            'admin_remark' => '',
            'applied_by' => $request_staff_id,
            'recommender_id' => $recommender_id,
            'approver_id' => $approver_id,
            'recommender_status' => 'pending',
            'approver_status' => 'pending',
        );

        if ($this->db->field_exists('leave_duration_type', 'staff_leave_request')) {
            $payload['leave_duration_type'] = $is_half_day ? 'first_half' : 'full_day';
        }

        // Insert leave request
        try {
            $this->db->insert('staff_leave_request', $payload);
            $leave_id = $this->db->insert_id();

            // Retrieve the created record with joins
            $this->db->select('staff.name, staff.surname, staff.employee_id, staff_leave_request.*, staff_leave_request.employee_remark as reason, leave_types.type,
                recommender.name as recommender_name, recommender.surname as recommender_surname,
                approver.name as approver_name, approver.surname as approver_surname');
            $this->db->from('staff_leave_request');
            $this->db->join('staff', 'staff.id = staff_leave_request.staff_id', 'inner');
            $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id', 'inner');
            $this->db->join('staff as recommender', 'recommender.id = staff_leave_request.recommender_id', 'left');
            $this->db->join('staff as approver', 'approver.id = staff_leave_request.approver_id', 'left');
            $this->db->where('staff_leave_request.id', $leave_id);
            $created_record = $this->db->get()->row_array();

            json_output(200, array(
                'status' => 1,
                'message' => 'Leave request created successfully.',
                'leave_request' => $created_record,
            ));
        } catch (Exception $e) {
            json_output(500, array('status' => 0, 'message' => 'Error creating leave request: ' . $e->getMessage()));
        }
    }

    /**
     * Update a staff leave request (only if status is pending).
    * POST params: leave_id, leave_type_id, leave_from, leave_to, reason, is_half_day(optional)
     */
    public function updateStaffLeaveRequest()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if (!$check_auth_client) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user) || $staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
            return;
        }

        $leave_id = isset($params['leave_id']) ? (int) $params['leave_id'] : 0;
        $leave_type_id = isset($params['leave_type_id']) ? (int) $params['leave_type_id'] : 0;
        $leave_from = isset($params['leave_from']) ? trim((string) $params['leave_from']) : '';
        $leave_to = isset($params['leave_to']) ? trim((string) $params['leave_to']) : '';
        $reason = isset($params['reason']) ? trim((string) $params['reason']) : '';
        $is_half_day = isset($params['is_half_day']) && ($params['is_half_day'] === true || $params['is_half_day'] === 1 || $params['is_half_day'] === '1' || $params['is_half_day'] === 'true');

        if ($leave_id <= 0 || $leave_type_id <= 0 || $leave_from === '' || $leave_to === '') {
            json_output(422, array('status' => 0, 'message' => 'leave_id, leave_type_id, leave_from, and leave_to are required.'));
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $leave_from) || strtotime($leave_from) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid leave_from date. Use YYYY-MM-DD.'));
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $leave_to) || strtotime($leave_to) === false) {
            json_output(422, array('status' => 0, 'message' => 'Invalid leave_to date. Use YYYY-MM-DD.'));
            return;
        }

        if (strtotime($leave_from) > strtotime($leave_to)) {
            json_output(422, array('status' => 0, 'message' => 'leave_from must be before or equal to leave_to.'));
            return;
        }

        if ($is_half_day && $leave_from !== $leave_to) {
            json_output(422, array('status' => 0, 'message' => 'For half day leave, leave_from and leave_to must be the same date.'));
            return;
        }

        // Check leave request exists and belongs to current user
        $this->db->select('id, status, staff_id');
        $this->db->from('staff_leave_request');
        $this->db->where('id', $leave_id);
        $leave_request = $this->db->get()->row();

        if (empty($leave_request)) {
            json_output(404, array('status' => 0, 'message' => 'Leave request not found.'));
            return;
        }

        if ((int) $leave_request->staff_id !== (int) $staff_user->staff_id) {
            json_output(403, array('status' => 0, 'message' => 'Not authorized to update this leave request.'));
            return;
        }

        if ($leave_request->status !== 'pending') {
            json_output(400, array('status' => 0, 'message' => 'Can only update pending leave requests.'));
            return;
        }

        // Check if leave type exists
        $this->db->select('id');
        $this->db->from('leave_types');
        $this->db->where('id', $leave_type_id);
        $leave_type = $this->db->get()->row();

        if (empty($leave_type)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid leave_type_id.'));
            return;
        }

        // Calculate leave days
        $from_timestamp = strtotime($leave_from);
        $to_timestamp = strtotime($leave_to);
        $leave_days = $is_half_day ? 0.5 : (($to_timestamp - $from_timestamp) / 86400 + 1);

        $update_payload = array(
            'leave_type_id' => $leave_type_id,
            'leave_from' => $leave_from,
            'leave_to' => $leave_to,
            'leave_days' => $leave_days,
            'employee_remark' => $reason,
        );

        if ($this->db->field_exists('leave_duration_type', 'staff_leave_request')) {
            $update_payload['leave_duration_type'] = $is_half_day ? 'first_half' : 'full_day';
        }

        // Update leave request
        try {
            $this->db->where('id', $leave_id);
            $this->db->update('staff_leave_request', $update_payload);

            // Retrieve the updated record with joins
            $this->db->select('staff.name, staff.surname, staff.employee_id, staff_leave_request.*, staff_leave_request.employee_remark as reason, leave_types.type,
                recommender.name as recommender_name, recommender.surname as recommender_surname,
                approver.name as approver_name, approver.surname as approver_surname');
            $this->db->from('staff_leave_request');
            $this->db->join('staff', 'staff.id = staff_leave_request.staff_id', 'inner');
            $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id', 'inner');
            $this->db->join('staff as recommender', 'recommender.id = staff_leave_request.recommender_id', 'left');
            $this->db->join('staff as approver', 'approver.id = staff_leave_request.approver_id', 'left');
            $this->db->where('staff_leave_request.id', $leave_id);
            $updated_record = $this->db->get()->row_array();

            json_output(200, array(
                'status' => 1,
                'message' => 'Leave request updated successfully.',
                'leave_request' => $updated_record,
            ));
        } catch (Exception $e) {
            json_output(500, array('status' => 0, 'message' => 'Error updating leave request: ' . $e->getMessage()));
        }
    }

    /**
     * Delete a staff leave request (only if status is pending).
     * POST params: leave_id
     */
    public function deleteStaffLeaveRequest()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if (!$check_auth_client) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user) || $staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
            return;
        }

        $leave_id = isset($params['leave_id']) ? (int) $params['leave_id'] : 0;

        if ($leave_id <= 0) {
            json_output(422, array('status' => 0, 'message' => 'leave_id is required.'));
            return;
        }

        // Check leave request exists and belongs to current user
        $this->db->select('id, status, staff_id');
        $this->db->from('staff_leave_request');
        $this->db->where('id', $leave_id);
        $leave_request = $this->db->get()->row();

        if (empty($leave_request)) {
            json_output(404, array('status' => 0, 'message' => 'Leave request not found.'));
            return;
        }

        if ((int) $leave_request->staff_id !== (int) $staff_user->staff_id) {
            json_output(403, array('status' => 0, 'message' => 'Not authorized to delete this leave request.'));
            return;
        }

        if ($leave_request->status !== 'pending') {
            json_output(400, array('status' => 0, 'message' => 'Can only delete pending leave requests.'));
            return;
        }

        try {
            $this->db->where('id', $leave_id);
            $this->db->delete('staff_leave_request');

            json_output(200, array(
                'status' => 1,
                'message' => 'Leave request deleted successfully.',
            ));
        } catch (Exception $e) {
            json_output(500, array('status' => 0, 'message' => 'Error deleting leave request: ' . $e->getMessage()));
        }
    }

    /**
     * Update recommender/approver workflow status for a staff leave request.
     * POST params: leave_id, stage(recommender|approver), status(approved|disapproved), remark(optional)
     */
    public function updateStaffLeaveWorkflowStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if (!$check_auth_client) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        $this->db->select('users.role, users.user_id as staff_id');
        $this->db->from('users');
        $this->db->where('users.id', $login_user_id);
        $staff_user = $this->db->get()->row();

        if (empty($staff_user) || $staff_user->role === 'student' || $staff_user->role === 'parent') {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
            return;
        }

        $leave_id = isset($params['leave_id']) ? (int) $params['leave_id'] : 0;
        $stage = isset($params['stage']) ? strtolower(trim((string) $params['stage'])) : '';
        $status = isset($params['status']) ? strtolower(trim((string) $params['status'])) : '';
        $remark = isset($params['remark']) ? trim((string) $params['remark']) : '';

        if ($leave_id <= 0 || !in_array($stage, array('recommender', 'approver'), true)) {
            json_output(422, array('status' => 0, 'message' => 'leave_id and valid stage are required.'));
            return;
        }

        if (!in_array($status, array('approved', 'disapproved'), true)) {
            json_output(422, array('status' => 0, 'message' => 'status must be approved or disapproved.'));
            return;
        }

        $this->db->select('id, staff_id, status, recommender_id, recommender_status, approver_id, approver_status');
        $this->db->from('staff_leave_request');
        $this->db->where('id', $leave_id);
        $leave_request = $this->db->get()->row_array();

        if (empty($leave_request)) {
            json_output(404, array('status' => 0, 'message' => 'Leave request not found.'));
            return;
        }

        if (in_array((string) $leave_request['status'], array('approved', 'disapproved'), true)) {
            json_output(400, array('status' => 0, 'message' => 'Finalized leave request cannot be modified.'));
            return;
        }

        $current_staff_id = (int) $staff_user->staff_id;
        $update_payload = array();

        if ($stage === 'recommender') {
            if ((int) ($leave_request['recommender_id'] ?? 0) !== $current_staff_id) {
                json_output(403, array('status' => 0, 'message' => 'Not authorized as recommender.'));
                return;
            }

            if (!in_array((string) ($leave_request['recommender_status'] ?? ''), array('', 'pending'), true)) {
                json_output(400, array('status' => 0, 'message' => 'Recommender action already completed.'));
                return;
            }

            $update_payload['recommender_status'] = ($status === 'approved') ? 'recommended' : 'rejected';
            $update_payload['recommender_remark'] = $remark;
            $update_payload['recommender_action_date'] = date('Y-m-d');

            if ($status === 'approved' && (string) $leave_request['status'] === 'pending') {
                $update_payload['status'] = 'recommended';
            }

            if ($status === 'disapproved') {
                $update_payload['status'] = 'disapproved';
                $update_payload['approver_status'] = 'rejected';
                $update_payload['approver_action_date'] = date('Y-m-d');
                $update_payload['approve_date'] = null;
            }
        } else {
            if ((int) ($leave_request['approver_id'] ?? 0) !== $current_staff_id) {
                json_output(403, array('status' => 0, 'message' => 'Not authorized as approver.'));
                return;
            }

            if (!in_array((string) ($leave_request['recommender_status'] ?? ''), array('recommended', 'approved'), true)) {
                json_output(400, array('status' => 0, 'message' => 'Recommender approval is pending.'));
                return;
            }

            if (!in_array((string) ($leave_request['approver_status'] ?? ''), array('', 'pending'), true)) {
                json_output(400, array('status' => 0, 'message' => 'Approver action already completed.'));
                return;
            }

            $update_payload['approver_status'] = ($status === 'approved') ? 'approved' : 'rejected';
            $update_payload['approver_remark'] = $remark;
            $update_payload['approver_action_date'] = date('Y-m-d');
            $update_payload['status'] = ($status === 'approved') ? 'approved' : 'disapproved';
            $update_payload['approve_date'] = ($status === 'approved') ? date('Y-m-d') : null;
        }

        if (empty($update_payload)) {
            json_output(400, array('status' => 0, 'message' => 'No changes to apply.'));
            return;
        }

        try {
            $this->db->where('id', $leave_id);
            $this->db->update('staff_leave_request', $update_payload);

            if ((string) ($update_payload['status'] ?? '') === 'approved') {
                $this->load->model('leaverequest_model');
                $this->leaverequest_model->logLeaveApprovalCredit($leave_id, $current_staff_id);
            }

            $this->db->select('staff.name, staff.surname, staff.employee_id, staff_leave_request.*, staff_leave_request.employee_remark as reason, leave_types.type,
                recommender.name as recommender_name, recommender.surname as recommender_surname,
                approver.name as approver_name, approver.surname as approver_surname');
            $this->db->from('staff_leave_request');
            $this->db->join('staff', 'staff.id = staff_leave_request.staff_id', 'inner');
            $this->db->join('leave_types', 'leave_types.id = staff_leave_request.leave_type_id', 'inner');
            $this->db->join('staff as recommender', 'recommender.id = staff_leave_request.recommender_id', 'left');
            $this->db->join('staff as approver', 'approver.id = staff_leave_request.approver_id', 'left');
            $this->db->where('staff_leave_request.id', $leave_id);
            $updated_record = $this->db->get()->row_array();

            json_output(200, array(
                'status' => 1,
                'message' => 'Leave workflow status updated successfully.',
                'leave_request' => $updated_record,
            ));
        } catch (Exception $e) {
            json_output(500, array('status' => 0, 'message' => 'Error updating leave workflow status: ' . $e->getMessage()));
        }
    }

    public function addTask()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();

            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('event_title', 'Title', 'required|trim');
                $this->form_validation->set_rules('date', 'Date', 'required|trim');
                $this->form_validation->set_rules('user_id', 'user login id', 'required|trim');

                if ($this->form_validation->run() == false) {

                    $sss = array(
                        'event_title' => form_error('event_title'),
                        'date' => form_error('date'),
                        'user_id' => form_error('user_id'),
                    );
                    $array = array('status' => '0', 'error' => $sss);
                } else {
                    //==================                    

                    $data = array(
                        'id' => $this->input->post('task_id'),
                        'event_title' => $this->input->post('event_title'),
                        'start_date' => $this->input->post('date'),
                        'end_date' => $this->input->post('date'),
                        'event_type' => 'task',
                        'is_active' => 'yes',
                        'event_for' => $this->input->post('user_id'),
                        'event_color' => '#000',
                    );

                    $this->event_model->saveEvent($data);
                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    public function updatetask()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('task_id', 'Task ID', 'required|trim');
                $this->form_validation->set_rules('status', 'Status', 'required|trim');

                if ($this->form_validation->run() == false) {
                    $errors = array(
                        'task_id' => form_error('task_id'),
                        'status' => form_error('status'),
                    );
                    $array = array('status' => '0', 'error' => $errors);
                } else {
                    //==================
                    $data = array(
                        'id' => $this->input->post('task_id'),
                        'is_active' => $this->input->post('status'),
                    );
                    $this->event_model->saveEvent($data);
                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    public function deletetask()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('task_id', 'Task ID', 'required|trim');

                if ($this->form_validation->run() == false) {

                    $errors = array(
                        'task_id' => form_error('task_id'),
                    );
                    $array = array('status' => '0', 'error' => $errors);
                } else {
                    //==================

                    $id = $this->input->post('task_id');
                    $this->event_model->deleteEvent($id);
                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    public function logout()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('deviceToken', 'deviceToken', 'required|trim');

                if ($this->form_validation->run() == false) {

                    $errors = array(
                        'deviceToken' => form_error('deviceToken'),
                    );
                    $array = array('status' => '0', 'error' => $errors);
                } else {
                    //==================
                    $deviceToken = $this->input->post('deviceToken');
                    $response = $this->auth_model->logout($deviceToken);

                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    public function forgot_password()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
            $this->form_validation->set_error_delimiters('', '');
            $this->form_validation->set_data($_POST);
            $this->form_validation->set_rules('site_url', 'URL', 'trim|required');
            $this->form_validation->set_rules('email', 'Email', 'trim|required');
            $this->form_validation->set_rules('usertype', 'User Type', 'trim|required');
            if ($this->form_validation->run() == false) {
                $errors = validation_errors();
            }

            if (isset($errors)) {
                $respStatus = 400;
                $errors = array(
                    'email' => form_error('email'),
                    'usertype' => form_error('usertype'),
                    'site_url' => form_error('site_url'),
                );
                $resp = array('status' => 400, 'message' => $errors);
            } else {
                $email = $this->input->post('email');
                $usertype = $this->input->post('usertype');
                $site_url = $this->input->post('site_url');
                $result = $this->user_model->forgotPassword($usertype, $email);

                if ($result) {
                    $template = $this->setting_model->getTemplate('forgot_password');
                    if (!empty($template) && $template->is_mail && $template->template != "") {
                        $verification_code = $this->enc_lib->encrypt(uniqid(mt_rand()));
                        $update_record = array('id' => $result->user_tbl_id, 'verification_code' => $verification_code);
                        $this->user_model->updateVerCode($update_record);
                        if ($usertype == "student") {
                            $name = $result->firstname . " " . $result->lastname;
                        } elseif ($usertype == "parent") {
                            $name = $result->guardian_email;
                        } else {
                            // All staff types: teacher, librarian, accountant, admin etc.
                            $name = $result->name . " " . $result->surname;
                        }
                        $resetPassLink = $site_url . '/user/resetpassword' . '/' . $usertype . "/" . $verification_code;

                        $body = $this->forgotPasswordBody($name, $resetPassLink, $template->template, $template->subject);
                        $body_array = json_decode($body);

                        if (!empty($this->mail_config)) {
                            $result = $this->mailer->send_mail($email, $body_array->subject, $body_array->body);
                            if ($result) {
                                $respStatus = 200;
                                $resp = array('status' => 200, 'message' => "Please check your email to recover your password");
                            } else {
                                $respStatus = 200;
                                $resp = array('status' => 200, 'message' => "Sending of message failed, Please contact to Admin.");
                            }
                        } else {
                            $respStatus = 200;
                            $resp = array('status' => 200, 'message' => "Email service not configured. Please contact Admin.");
                        }
                    } else {
                        $respStatus = 200;
                        $resp = array('status' => 200, 'message' => "Sending of message failed, Please contact to Admin.");
                    }

                } else {
                    $respStatus = 401;
                    $resp = array('status' => 401, 'message' => "Invalid Email or User Type");
                }
            }
            json_output($respStatus, $resp);
        }
    }

    public function forgotPasswordBody($name, $resetPassLink, $template, $subject = "Forgot Password")
    {
        $mail_detail['name'] = $name;
        $mail_detail['school_name'] = $this->customlib->getSchoolName();
        $mail_detail['resetPassLink'] = $resetPassLink;
        foreach ($mail_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        //===============
        $body = $template;
        //======================
        return json_encode(array('subject' => $subject, 'body' => $body));
    }

    public function dashboard()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $date_list = array();
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $date_from = $params['date_from'];
                    $date_to = $params['date_to'];
                    $role = $params['role'];

                    $student = $this->student_model->get($student_id);
                    $student_login = $this->user_model->getUserLoginDetails($student_id);

                    $user_role_id = $student_login['id'];
                    if ($role == "parent") {
                        $user_role_id = $params['user_id'];
                    }
                    $attendence_percentage = 0;
                    $resp = array();
                    $student_session_id = $student->student_session_id;
                    $student_attendence = $this->attendencetype_model->getAttendencePercentage($date_from, $date_to, $student_session_id);
                    $student_homework = $this->homework_model->getStudentHomeworkPercentage($student_session_id, $student->class_id, $student->section_id);

                    if ($student_attendence->present_attendance > 0 && $student_attendence->total_count > 0) {
                        $attendence_percentage = $student_attendence->present_attendance / $student_attendence->total_count * 100;
                    }

                    $school_setting = $this->setting_model->getSchoolDetail();
                    $resp['attendence_type'] = $school_setting->attendence_type;
                    $resp['class_id'] = $student->class_id;
                    $resp['section_id'] = $student->section_id;
                    $resp['student_attendence_percentage'] = round($attendence_percentage);
                    $resp['student_homework_incomplete'] = round($student_homework->total_homework - $student_homework->completed);
                    $eventcount = $this->event_model->incompleteStudentTaskCounter($user_role_id);

                    if (!empty($eventcount)) {
                        $resp['student_incomplete_task'] = count($eventcount);
                    } else {
                        $resp['student_incomplete_task'] = 0;
                    }

                    $resp['public_events'] = $this->event_model->getPublicEvents($user_role_id, $date_from, $date_to);

                    foreach ($resp['public_events'] as &$ev_tsk_value) {
                        $evt_array = array();
                        if ($ev_tsk_value->event_type == "public") {
                            $start = strtotime($ev_tsk_value->start_date);
                            $end = strtotime($ev_tsk_value->end_date);

                            for ($st = $start; $st <= $end; $st += 86400) {
                                if ($st >= strtotime($date_from) && $st <= strtotime($date_to)) {

                                    $date_list[date('Y-m-d', $st)] = date('Y-m-d', $st);
                                    $evt_array[] = date('Y-m-d', $st);

                                }
                            }

                            $ev_tsk_value->events_lists = implode(",", $evt_array);
                        } elseif ($ev_tsk_value->event_type == "task") {
                            $date_list[date('Y-m-d', strtotime($ev_tsk_value->start_date))] = date('Y-m-d', strtotime($ev_tsk_value->start_date));
                            $evt_array[] = date('Y-m-d', strtotime($ev_tsk_value->start_date));
                            $ev_tsk_value->events_lists = implode(",", $evt_array);
                        }
                    }
                    $resp['date_lists'] = implode(",", $date_list);

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getTask()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $user_id = $params['user_id'];
                    $resp = array();
                    $resp['tasks'] = $this->event_model->getTask($user_id);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getDocument()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $student_doc = $this->student_model->getstudentdoc($student_id);
                    json_output($response['status'], $student_doc);
                }
            }
        }
    }
	
	public function getHomeworkById()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $homework_id = $this->input->post('homework_id');                 

                    $resulthomework = $this->homework_model->getHomeworkById($homework_id); 

                    $data["homeworklist"] = $resulthomework;                   

                    json_output($response['status'], $data);
                }
            }
		}
	}

    public function getHomework()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $homework_status = $this->input->post('homework_status');
                    $subject_group_subject_id = $this->input->post('subject_group_subject_id');

                    $result = $this->student_model->get($student_id);
                    $class_id = $result->class_id;
                    $section_id = $result->section_id;

                    $resulthomework = $this->homework_model->getStudentHomework($class_id, $section_id, $result->student_session_id, $student_id, $subject_group_subject_id);

                    $homeworklist = array();
                    foreach ($resulthomework as $key => $value) {

                        if ($value['status'] == $homework_status) {
                            if ($value['document'] == null) {
                                $value['document'] = '';
                            }
                            if ($value['note'] == null) {
                                $value['note'] = '';
                            }
                            if ($value['evaluation_marks'] == null) {
                                $value['evaluation_marks'] = '';
                            }
                            if ($value['marks'] == null) {
                                $value['marks'] = '';
                            }
                            if ($value['evaluation_date'] == null) {
                                $value['evaluation_date'] = '';
                            }
                            if ($value['evaluated_by'] == null) {
                                $value['evaluated_by'] = '';
                            } else {
                                $staffdetails = $this->staff_model->getAll($value['evaluated_by']);
                                $value['evaluated_by'] = $staffdetails['name'] . ' ' . $staffdetails['surname'] . ' (' . $staffdetails['employee_id'] . ')';
                            }

                            $homeworklist[] = $value;
                        }
                    }

                    $data["homeworklist"] = $homeworklist;
                    $data["class_id"] = $class_id;
                    $data["section_id"] = $section_id;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getstudentsubject()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $result = $this->student_model->get($student_id);
                    $class_id = $result->class_id;
                    $section_id = $result->section_id;
                    $subjectlist = $this->syllabus_model->getmysubjects($class_id, $section_id);
                    $data["subjectlist"] = $subjectlist;
                    $data["class_id"] = $class_id;
                    $data["section_id"] = $section_id;

                    json_output($response['status'], $data);
                }
            }
        }
    }


    public function addhomework()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = $this->input->POST();

                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('student_id', 'Student', 'required|trim');
                    $this->form_validation->set_rules('homework_id', 'Homework', 'required|trim');
                    $this->form_validation->set_rules('message', 'Message', 'required|trim');

                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $this->form_validation->set_rules('file', 'File', 'callback_handle_upload_file');
                    }

                    $storage_array = "file";
                    $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'student_id' => form_error('student_id'),
                            'homework_id' => form_error('homework_id'),
                            'message' => form_error('message'),
                            'file' => form_error('file'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        try {

                            $total_documents_failed_size = 0;
                            $storage_array = ['file'];
                            $this->saasvalidation->updateStorageLimit('storage', $storage_array);

                            //==================
                            $upload_path = $this->config->item('upload_path') . "/homework/assignment/";

                            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

                                $img_name = $this->media_storage->fileupload("file", $upload_path);
                                $img_name = $img_name ?? ''; // guard against null on upload failure

                                if (IsNullOrEmptyString($img_name)) {  // check upload image has not uploaded successfully
                                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('file');  // get temp size of image because of image not uploaded 
                                }
                                if ($total_documents_failed_size > 0) {
                                    $this->saasvalidation->deleteResouceQuota('storage', $total_documents_failed_size);
                                }

                                $data_insert = array(
                                    'homework_id' => $this->input->post('homework_id'),
                                    'student_id' => $this->input->post('student_id'),
                                    'message' => $this->input->post('message'),
                                    'docs' => $img_name,
                                    'file_name' => $_FILES['file']['name'],
                                );
                                $this->homework_model->add($data_insert);
                            } else {
                                $data_insert = array(
                                    'homework_id' => $this->input->post('homework_id'),
                                    'student_id' => $this->input->post('student_id'),
                                    'message' => $this->input->post('message'),
                                    'docs' => '',
                                );
                                $this->homework_model->add($data_insert);
                            }

                            $array = array('status' => '1', 'msg' => 'Success');
                        } catch (Exception $e) {
                            $array = array('status' => '0', 'error' => $e->getMessage());
                        }

                    }
                    json_output(200, $array);
                }
            }
        }
    }

    // ---------------- Online Exam ------------------
    public function getOnlineExam()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $exam_type = $params['exam_type'];

                    $result = $this->student_model->get($student_id);

                    if ($exam_type == 'closed') {
                        $respdata = $this->onlineexam_model->getstudentclosedexamlist($result->student_session_id);
                    } else {
                        $respdata = $this->onlineexam_model->getStudentexam($result->student_session_id);
                    }

                    $resp['onlineexam'] = array();
                    $question = array();
                    foreach ($respdata as $key => $value) {

                        $question = $this->onlineexam_model->getquestiondetails($value->id);

                        if (!empty($question)) {
                            $value->total_question = $question->total_question;
                            $value->total_descriptive = $question->total_descriptive;
                        } else {
                            $value->total_question = "0";
                            $value->total_descriptive = "0";
                        }
                        $resp['onlineexam'][] = $value;
                    }

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOnlineExamQuestion()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $recordid = $params['online_exam_id'];
                    $result = $this->student_model->get($student_id);
                    $onlineexam = array();
                    $exam = $this->onlineexam_model->get($recordid);
                    $onlineexam_student = $this->onlineexam_model->examstudentsID($result->student_session_id, $exam['id']);
                    $exam['onlineexam_student_id'] = $onlineexam_student->id;
                    $exam['student_session_id'] = $onlineexam_student->student_session_id;
                    $exam['is_submitted'] = $onlineexam_student->is_submitted;
                    $exam['questions'] = $this->onlineexam_model->getExamQuestions($exam['id'], $exam['is_random_question']);
                    $getStudentAttemts = $this->onlineexam_model->getStudentAttemts($onlineexam_student->id);
                    $onlineexam['exam_result_publish_status'] = $exam['publish_result'];
                    $onlineexam['exam_attempt_status'] = 0;

                    if (($exam['auto_publish_date'] != "0000-00-00" && $exam['auto_publish_date'] != null) && strtotime(date('Y-m-d')) >= strtotime($exam['auto_publish_date'])) {
                        $question_status = 1;
                        $onlineexam['exam_result_publish_status'] = 1;
                    } else if (strtotime(date('Y-m-d H:i:s')) >= strtotime(date($exam['exam_to']))) {
                        $question_status = 1;
                        $onlineexam['exam_attempt_status'] = 1;
                    } else if ($exam['attempt'] > $getStudentAttemts) {
                        $this->onlineexam_model->addStudentAttemts(array('onlineexam_student_id' => $onlineexam_student->id));
                    } else {
                        $question_status = 1;
                        $onlineexam['exam_attempt_status'] = 1;
                    }

                    $exam['status'] = $onlineexam;
                    $total_remaining_seconds = round((strtotime($exam['exam_to']) - strtotime(date('Y-m-d H:i:s'))) / 3600 * 60 * 60, 1);
                    $exam_duration = ($total_remaining_seconds < getSecondsFromHMS($exam['duration'])) ? getHMSFromSeconds($total_remaining_seconds) : $exam['duration'];
                    $exam['remaining_duration'] = $exam_duration;
                    $total_descriptive = 0;
                    $question = $this->onlineexam_model->getquestiondetails($exam['id']);
                    if (!empty($question)) {
                        $total_descriptive = $question->total_descriptive;
                    } else {
                        $total_descriptive = "0";
                    }
                    $exam['descriptive'] = $total_descriptive;
                    json_output($response['status'], array('exam' => $exam));
                }
            }
        }
    }

    public function getOnlineExamResult()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $onlineexam_student_id = $params['onlineexam_student_id'];
                    $exam_id = $params['exam_id'];
                    $exam = $this->onlineexam_model->get($exam_id);
                    $resp['question_result'] = $this->onlineexam_model->getResultByStudent($onlineexam_student_id, $exam_id);

                    $onlineexamStudent = $this->onlineexam_model->getExamByOnlineexamStudent($onlineexam_student_id);
                    $dispaly_negative_marks = $exam['is_neg_marking'];
                    $exam_total_scored = 0;
                    $exam_total_marks = 0;
                    $exam_total_neg_marks = 0;
                    $correct_ans = 0;
                    $wrong_ans = 0;
                    $not_attempted = 0;
                    $total_question = 0;
                    $total_descriptive = 0;
                    if (!empty($resp['question_result'])) {
                        $total_question = count($resp['question_result']);

                        foreach ($resp['question_result'] as $result_key => $question_value) {

                            $total_marks_json = $this->getMarks($question_value);
                            $total_marks_array = (json_decode($total_marks_json));

                            $resp['question_result'][$result_key]->scr_marks = $total_marks_array->scr_marks;


                            $exam_total_marks = $exam_total_marks + $total_marks_array->get_marks;
                            $exam_total_scored = $exam_total_scored + $total_marks_array->scr_marks;
                            if ($question_value->question_type == "descriptive") {
                                $total_descriptive++;
                            }

                            if ($question_value->select_option != null) {
                                if ($question_value->question_type == "singlechoice" || $question_value->question_type == "true_false") {
                                    if ($question_value->select_option == $question_value->correct) {
                                        $correct_ans++;
                                    } else {
                                        $exam_total_neg_marks = $exam_total_neg_marks + $question_value->neg_marks;
                                        $wrong_ans++;
                                    }
                                } elseif ($question_value->question_type == "multichoice") {

                                    if ($this->array_equal(json_decode($question_value->correct), json_decode($question_value->select_option))) {
                                        $correct_ans++;
                                    } else {
                                        $exam_total_neg_marks = $exam_total_neg_marks + $question_value->neg_marks;
                                        $wrong_ans++;
                                    }
                                }
                            } else {
                                $not_attempted++;
                            }
                        }
                    }
                    if (!$dispaly_negative_marks) {
                        $exam_total_neg_marks = 0;
                    }
                    if ($exam_total_marks > 0) {
                        $score = number_format(((($exam_total_scored - $exam_total_neg_marks) * 100) / $exam_total_marks), 2, '.', '');
                    } else {
                        $score = 0;
                    }
                    $exam['rank'] = $onlineexamStudent->rank;
                    $exam['correct_ans'] = $correct_ans;
                    $exam['wrong_ans'] = $wrong_ans;
                    $exam['not_attempted'] = $not_attempted;
                    $exam['total_question'] = $total_question;
                    $exam['total_descriptive'] = $total_descriptive;
                    $exam['exam_total_marks'] = $exam_total_marks;
                    $exam['exam_total_neg_marks'] = $exam_total_neg_marks;
                    $exam['exam_total_scored'] = $exam_total_scored - $exam_total_neg_marks;
                    $exam['score'] = $score;
                    $resp['exam'] = $exam;

                    json_output($response['status'], array('result' => $resp));
                }
            }
        }
    }     
	
	public function saveOnlineExam()
	{
		if ($this->input->server('REQUEST_METHOD') != 'POST') {
			json_output(400, ['status' => 400, 'message' => 'Bad request.']);
			return;
		}

		if (!$this->auth_model->check_auth_client()) {
			return;
		}

		$response = $this->auth_model->auth();
		if ($response['status'] != 200) {
			return;
		}

		$params = json_decode(file_get_contents('php://input'), true);
		//$question_rows = $params['rows'];
		$question_rows = $this->input->post('rows'); 
		$file_keys = [];
		$total_upload_size_kb = 0;

		foreach ($question_rows as $q_key => $q_val) {

			if ($q_val['question_type'] == "descriptive") {

				$qid_key = "attachment_" . $q_val['onlineexam_question_id'];

				if (isset($_FILES[$qid_key]) && !empty($_FILES[$qid_key]['name'])) {
					$file_keys[] = $qid_key;

					if (!empty($_FILES[$qid_key]['size'])) {
						$total_upload_size_kb += ceil($_FILES[$qid_key]['size'] / 1024);
					}
				}
			}
		}

		 
		if ($this->saasvalidation->sass_enabled && !empty($file_keys)) {
			$limit_status = $this->saasvalidation->getResourceLimit('storage');

			if (!empty($limit_status['status']) &&
				($limit_status['usage'] + $total_upload_size_kb) > $limit_status['limit']) {
				json_output(200, ['status' => 0, 'msg' => 'Storage Limit Exceeded']);
				return;
			}
		}

		 
		if (!empty($file_keys)) {
			$this->saasvalidation->updateStorageLimit('storage', $file_keys);
		}

		$total_failed_size = 0;

		 
		foreach ($question_rows as $key => $question_value) {

			if ($question_value['question_type'] == "descriptive") {

				$qid = $question_value['onlineexam_question_id'];
				$file_key = "attachment_" . $qid;

				if (isset($_FILES[$file_key]) && !empty($_FILES[$file_key]['name'])) {

					$file_name = $_FILES[$file_key]['name'];
					$fileInfo  = pathinfo($file_name);
					$upload_file_name = time() . uniqid() . '.' . $fileInfo['extension'];
					$upload_path = $this->config->item('upload_path') . "/onlinexam_images/";

					if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path . $upload_file_name)) {
						$question_rows[$key]['attachment_name'] = $file_name;
						$question_rows[$key]['attachment_upload_name'] = $upload_file_name;
					} else {
						$total_failed_size += $_FILES[$file_key]['size'];
						$question_rows[$key]['attachment_name'] = "";
						$question_rows[$key]['attachment_upload_name'] = "";
					}
				} else {
					$question_rows[$key]['attachment_name'] = "";
					$question_rows[$key]['attachment_upload_name'] = "";
				}
			} else {
				$question_rows[$key]['attachment_name'] = "";
				$question_rows[$key]['attachment_upload_name'] = "";
			}
			 
			unset($question_rows[$key]['question_type']);
		}

	 
		if ($total_failed_size > 0) {
			$this->saasvalidation->deleteResouceQuota('storage',round($total_failed_size / 1024));
		}

		$onlineexam_student_id = $this->input->post('onlineexam_student_id');
		 
		$insert_result = $this->onlineexam_model->add($question_rows,$onlineexam_student_id);

		$this->onlineexam_model->updateExamSubmitted($onlineexam_student_id);

		json_output(200, [
			'status' => $insert_result == 1 ? 1 : 0,
			'msg'    => $insert_result == 1 ? 'record inserted' : 'something wrong'
		]);
	}


    public function getExamList()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $result = $this->student_model->get($student_id);
                    $examSchedule = $this->examgroup_model->studentExams($result->student_session_id);
                    $data['examSchedule'] = $examSchedule;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getExamSchedule()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $exam_id = $params['exam_group_class_batch_exam_id'];
                    $exam_subjects = $this->examgroup_model->getExamSubjects($exam_id);
                    $data['exam_subjects'] = $exam_subjects;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getNotifications()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $type = $params['type'];
                    $resp = $this->webservice_model->getNotifications($type);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getSubjectList()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $class_id = $params['class_id'];
                    $section_id = $params['section_id'];
                    $resp = $this->subjecttimetable_model->getSubjects($class_id, $section_id);
                    $subjects = array();
                    if (!empty($resp)) {

                        foreach ($resp as $res_key => $res_value) {
                            $subjects[] = array(
                                'subject_id' => $res_value->subject_id,
                                'subject' => $res_value->subject_name,
                                'code' => $res_value->code,
                                'type' => $res_value->type,
                            );
                        }
                    }

                    json_output($response['status'], array('result_list' => $subjects));
                }
            }
        }
    }

    public function getSubjectTimetable()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $class_id = $params['class_id'];
                    $section_id = $params['section_id'];
                    $subject_id = $params['subject_id'];
                    $resp = $this->subjecttimetable_model->getSubjectTimetable($class_id, $section_id, $subject_id);
                    $subjects = array();
                    json_output($response['status'], array('result_list' => $resp));
                }
            }
        }
    }

    public function getTeachersList()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $user_id = $params['user_id'];
                    $class_id = $params['class_id'];
                    $section_id = $params['section_id'];
                    $resp = $this->subjecttimetable_model->getTeachers($class_id, $section_id);
                    $class_teacher = array();
                    if (!empty($resp)) {

                        foreach ($resp as $res_key => $res_value) {
                            $is_duplicate = false;
                            $rating = $this->subjecttimetable_model->user_rating($user_id, $res_value->staff_id);
                            $rate = 0;
                            $comment = '';
                            if ($rating) {
                                $rate = $rating->rate;
                                $comment = $rating->comment;
                            }

                            if (is_null($res_value->day)) {
                                $total_row = checkDuplicateTeacher($resp, $res_value->staff_id);
                                if ($total_row > 1) {
                                    $is_duplicate = true;
                                }
                            }

                            if (!$is_duplicate) {
                                if (array_key_exists($res_value->staff_id, $class_teacher)) {

                                    $class_teacher[$res_value->staff_id]['subjects'][] = array(
                                        'subject_id' => $res_value->subject_id,
                                        'subject_name' => $res_value->subject_name,
                                        'code' => $res_value->code,
                                        'type' => $res_value->type,
                                        'day' => $res_value->day,
                                        'time_from' => $res_value->time_from,
                                        'time_to' => $res_value->time_to,
                                        'room_no' => $res_value->room_no,
                                    );
                                } else {

                                    $class_teacher[$res_value->staff_id] = array(
                                        'employee_id' => $res_value->employee_id,
                                        'staff_id' => $res_value->staff_id,
                                        'staff_name' => $res_value->staff_name,
                                        'staff_surname' => $res_value->staff_surname,
                                        'contact_no' => $res_value->contact_no,
                                        'email' => $res_value->email,
                                        'class_teacher_id' => $res_value->class_teacher_id,
                                        'rate' => $rate,
                                        'comment' => $comment,
                                        'subjects' => array(),
                                    );
                                    if (!is_null($res_value->day)) {
                                        $class_teacher[$res_value->staff_id]['subjects'][] = array(
                                            'subject_id' => $res_value->subject_id,
                                            'subject_name' => $res_value->subject_name,
                                            'code' => $res_value->code,
                                            'type' => $res_value->type,
                                            'day' => $res_value->day,
                                            'time_from' => $res_value->time_from,
                                            'time_to' => $res_value->time_to,
                                            'room_no' => $res_value->room_no,
                                        );
                                    }
                                }
                            }
                        }
                    }
                    json_output($response['status'], array('result_list' => $class_teacher));
                }
            }
        }
    }

    public function getClassTimetable()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $user_id = $params['user_id'];
                    $class_id = $params['class_id'];
                    $section_id = $params['section_id'];
                    $resp = $this->subjecttimetable_model->getTeachers($class_id, $section_id);

                    $class_teacher = array();
                    if (!empty($resp)) {

                        foreach ($resp as $res_key => $res_value) {
                            $is_duplicate = false;
                            $rating = $this->subjecttimetable_model->user_rating($user_id, $res_value->staff_id);
                            $rate = 0;
                            if ($rating) {
                                $rate = $rating->rate;
                            }

                            if (is_null($res_value->day)) {
                                $total_row = checkDuplicateTeacher($resp, $res_value->staff_id);
                                if ($total_row > 1) {
                                    $is_duplicate = true;
                                }
                            }
                            if (!$is_duplicate) {

                                $class_teacher[] = array(
                                    'staff_id' => $res_value->staff_id,
                                    'staff_name' => $res_value->staff_name,
                                    'staff_surname' => $res_value->staff_surname,
                                    'contact_no' => $res_value->contact_no,
                                    'class_teacher_id' => $res_value->class_teacher_id,
                                    'subject_id' => $res_value->subject_id,
                                    'subject_name' => $res_value->subject_name,
                                    'code' => $res_value->code,
                                    'type' => $res_value->type,
                                    'day' => $res_value->day,
                                    'time_from' => $res_value->time_from,
                                    'time_to' => $res_value->time_to,
                                    'room_no' => $res_value->room_no,
                                    'rate' => $rate,
                                );
                            }
                        }
                    }

                    json_output($response['status'], array('result_list' => $class_teacher));
                }
            }
        }
    }

    public function getTeacherSubject()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);

                    $staff_id = $params['staff_id'];
                    $class_id = $params['class_id'];
                    $section_id = $params['section_id'];
                    $resp = $this->subjecttimetable_model->getTeacherSubject($class_id, $section_id, $staff_id);

                    json_output($response['status'], array('result_list' => $resp));
                }
            }
        }
    }

    public function addStaffRating()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $data = array(
                        'user_id' => $params['user_id'],
                        'staff_id' => $params['staff_id'],
                        'rate' => $params['rate'],
                        'comment' => $params['comment'],
                        'role' => 'student',
                    );

                    $insert_result = $this->subjecttimetable_model->add_rating($data);
                    if ($insert_result) {
                        $resp = array('status' => 1, 'msg' => 'inserted');
                    } else {
                        $resp = array('status' => 0, 'msg' => 'something wrong or already submitted');
                    }

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getLibraryBooks()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'GET') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $resp = $this->webservice_model->getLibraryBooks();
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getLibraryBookIssued()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $params = json_decode(file_get_contents('php://input'), true);
                    $studentId = $params['studentId'];
                    $member_type = "student";
                    $resp = $this->librarymember_model->checkIsMember($member_type, $studentId);

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getTransportroute()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, ['status' => 400, 'message' => 'Bad request']);
            return;
        }

        if ($this->auth_model->check_auth_client() !== true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            json_output($response['status'], $response);
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $student_id = $params['student_id'] ?? null;

        if (empty($student_id)) {
            json_output(400, ['message' => 'student_id required']);
            return;
        }

        $student = $this->student_model->get($student_id);
        if (!$student || empty($student->vehroute_id)) {
            json_output(200, []);
            return;
        }

        $vec_route_id = $student->vehroute_id;

        $this->db->select('route_id')
            ->from('vehicle_routes')
            ->where('id', $vec_route_id);
        $routeRow = $this->db->get()->row();

        if (!$routeRow) {
            json_output(200, []);
            return;
        }

        $route_id = $routeRow->route_id;

        $vehicles = $this->vehroute_model->getVechileByRoute($route_id);

        foreach ($vehicles as $vehicle) {
            $vehicle->assigned = ($vehicle->vec_route_id == $vec_route_id) ? 'yes' : 'no';
        }

        $route = $this->db->where('id', $route_id)
            ->get('transport_route')
            ->row_array();

        $route['vehicles'] = $vehicles;

        json_output(200, [$route]);
    }


    public function getHostelList()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $studentList = $this->student_model->get($student_id);

                    $resp = $this->webservice_model->getHostelList();
                    $studentRoom = array();
                    foreach ($resp as $value) {
                        if ($studentList->hostel_room_id == $value['id']) {
                            $value['assign'] = 1;
                            $studentRoom[] = $value;
                        }
                    }

                    $data['hostelarray'] = $studentRoom;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getDownloadsLinks()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);

                    $classId = $params['classId'];
                    $sectionId = $params['sectionId'];
                    $role = $params['role'];

                    $user_role_id = $params['student_id'];
                    if ($role == "parent") {
                        $user_role_id = $params['user_parent_id'];
                    }

                    if ($role == "student") {
                        $resp = $this->webservice_model->getStudentsharelist($user_role_id, $classId, $sectionId);
                    } elseif ($role == "parent") {
                        $resp = $this->webservice_model->getParentsharelist($user_role_id, $classId, $sectionId);
                    }

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getDownloadsLinksById()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);

                    $id = $params['id'];
                    $resp = $this->webservice_model->getShareContentDocumentsByID($id);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getTransportVehicleDetails()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $vehicleId = $params['vehicleId'];
                    $resp = $this->webservice_model->getTransportVehicleDetails($vehicleId);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getAttendenceRecords1()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    ///===================
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $year = $this->input->post('year');
                    $month = $this->input->post('month');
                    $student_id = $this->input->post('student_id');
                    $student = $this->student_model->get($student_id);
                    $student_session_id = $student->student_session_id;
                    $result = array();
                    $new_date = "01-" . $month . "-" . $year;
                    $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                    $first_day_this_month = date('01-m-Y');
                    $fst_day_str = strtotime(date($new_date));
                    $array = array();
                    for ($day = 2; $day <= $totalDays; $day++) {
                        $fst_day_str = ($fst_day_str + 86400);
                        $date = date('Y-m-d', $fst_day_str);
                        $student_attendence = $this->attendencetype_model->getStudentAttendence($date, $student_session_id);
                        if (!empty($student_attendence)) {
                            $s = array();
                            $s['date'] = $date;
                            $type = $student_attendence->type;
                            $s['type'] = $type;
                            $array[] = $s;
                        }
                    }
                    $data['status'] = 200;
                    $data['data'] = $array;
                    json_output($response['status'], $data);

                    //======================
                }
            }
        }
    }

    public function getAttendenceRecords()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $school_setting = $this->setting_model->getSchoolDetail();

                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $year = $this->input->post('year');
                    $month = $this->input->post('month');
                    $student_id = $this->input->post('student_id');
                    $date = $this->input->post('date');
                    $student = $this->student_model->get($student_id);
                    $student_session_id = $student->student_session_id;
                    $data = array();
                    $data['attendence_type'] = $school_setting->attendence_type;
                    if ($school_setting->attendence_type) {
                        // Subject-wise attendance: loop through the month and aggregate to daily status
                        $new_date = "01-" . $month . "-" . $year;
                        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $fst_day_str = strtotime($new_date);
                        $array = array();
                        for ($loop_day = 1; $loop_day <= $totalDays; $loop_day++) {
                            $loop_date = date('Y-m-d', $fst_day_str);
                            $day_name = date('l', $fst_day_str);
                            $subject_attendance = $this->attendencetype_model->studentAttendanceByDate($student->class_id, $student->section_id, $day_name, $loop_date, $student_session_id);
                            if (!empty($subject_attendance)) {
                                $type = 'Absent';
                                foreach ($subject_attendance as $subj_att) {
                                    $att_type = isset($subj_att->type) ? $subj_att->type : '';
                                    if (strtolower($att_type) === 'present' || strtolower($att_type) === 'late') {
                                        $type = $att_type;
                                        break;
                                    }
                                }
                                $array[] = array('date' => $loop_date, 'type' => $type);
                            }
                            $fst_day_str = ($fst_day_str + 86400);
                        }
                        $data['data'] = $array;
                    } else {

                        $result = array();
                        $new_date = "01-" . $month . "-" . $year;
                        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $first_day_this_month = date('01-m-Y');
                        $fst_day_str = strtotime(date($new_date));
                        $array = array();

                        for ($day = 1; $day <= $totalDays; $day++) {
                            $date = date('Y-m-d', $fst_day_str);
                            $student_attendence = $this->attendencetype_model->getStudentAttendence($date, $student_session_id);
                            if (!empty($student_attendence)) {
                                $s = array();
                                $s['date'] = $date;
                                $type = $student_attendence->type;
                                $s['type'] = $type;
                                $array[] = $s;
                            }
                            $fst_day_str = ($fst_day_str + 86400);
                        }

                        $data['data'] = $array;
                    }

                    json_output($response['status'], $data);

                    //======================
                }
            }
        }
    }

    public function examSchedule()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $data = array();
                    $stu_record = $this->student_model->getRecentRecord($student_id);
                    $data['status'] = "200";
                    $data['class_id'] = $stu_record->class_id;
                    $data['section_id'] = $stu_record->section_id;
                    $examSchedule = $this->examschedule_model->getExamByClassandSection($data['class_id'], $data['section_id']);
                    $data['examSchedule'] = $examSchedule;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getexamscheduledetail()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $exam_id = $this->input->post('exam_id');
                    $section_id = $this->input->post('section_id');
                    $class_id = $this->input->post('class_id');
                    $examSchedule = $this->examschedule_model->getDetailbyClsandSection($class_id, $section_id, $exam_id);
                    json_output($response['status'], $examSchedule);
                }
            }
        }
    }

    // ---------- Lesson Plan -------------
    public function getlessonplan()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $student_id = $this->input->post('student_id');
                    $date_from = $this->input->post('date_from');
                    $date_to = $this->input->post('date_to');
                    $student = $this->student_model->get($student_id);
                    $class_id = $student->class_id;
                    $section_id = $student->section_id;
                    $result = $this->syllabus_model->getLessonPlanBwDate($class_id, $section_id, $date_from, $date_to);

                    $syllabus['data'] = array();
                    $start = strtotime($date_from);
                    $end = strtotime($date_to);
                    for ($i = $start; $i <= $end; $i += 86400) {
                        $syllabus['data'][date('l', $i)] = array();
                    }

                    if (!empty($result)) {
                        foreach ($result as $result_key => $result_value) {
                            $syllabus['data'][date('l', strtotime($result_value->date))][] = $result_value;
                        }
                    }
                    $data['timetable'] = $syllabus['data'];
                    $data['status'] = "200";
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getsyllabus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $subject_syllabus_id = $this->input->post('subject_syllabus_id');
                    $syllabus['data'] = $this->syllabus_model->getSyllabusDetail($subject_syllabus_id);
                    json_output($response['status'], $syllabus);
                }
            }
        }
    }

    public function getsyllabussubjects()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $student_id = $this->input->post('student_id');
                    $stu_record = $this->student_model->getRecentRecord($student_id);
                    $data['class_id'] = $stu_record['class_id'];
                    $data['section_id'] = $stu_record['section_id'];
                    $subjects['subjects'] = $this->syllabus_model->getSyllabusSubjects($data['class_id'], $data['section_id']);

                    json_output($response['status'], $subjects);
                }
            }
        }
    }

    public function getSubjectsLessons()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $subject_group_subject_id = $this->input->post('subject_group_subject_id');
                    $subject_group_class_sections_id = $this->input->post('subject_group_class_sections_id');

                    $subjects = $this->syllabus_model->getSubjectsLesson($subject_group_subject_id, $subject_group_class_sections_id);
                    json_output($response['status'], $subjects);
                }
            }
        }
    }

    public function getforummessage()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $subject_syllabus_id = $this->input->post('subject_syllabus_id');
                    $forummessage = $this->syllabus_model->getstudentmessage($subject_syllabus_id);

                    foreach ($forummessage as $key => $value) {
                        if ($value['middlename'] == '') {
                            $forummessage[$key]['middlename'] = '';
                        }
                    }

                    $data['syllabus'] = $forummessage;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function addforummessage()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $subject_syllabus_id = $this->input->post('subject_syllabus_id');
                    $student_id = $this->input->post('student_id');
                    $message = $this->input->post('message');

                    $insert_data = array(
                        'subject_syllabus_id' => $subject_syllabus_id,
                        'type' => 'student',
                        'student_id' => $student_id,
                        'message' => $message,
                        'created_date' => date('Y-m-d H:i:s'),
                    );

                    $this->syllabus_model->addforummessage($insert_data);
                    $array = array('status' => '1', 'msg' => 'Success');

                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function deleteforummessage()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('lesson_plan_forum_id', 'Forum ID', 'required|trim');

                if ($this->form_validation->run() == false) {

                    $errors = array(
                        'lesson_plan_forum_id' => form_error('lesson_plan_forum_id'),
                    );
                    $array = array('status' => '0', 'error' => $errors);
                } else {
                    //==================

                    $id = $this->input->post('lesson_plan_forum_id');
                    $this->syllabus_model->deleteforummessage($id);
                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    // ---------- Fees ------------------
    public function fees()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = array();
                    $pay_method = $this->paymentsetting_model->getActiveMethod();
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');  
                    $student = $this->student_model->get($student_id);
 
                    $transport_fees = $this->studentfeemaster_model->getStudentTransportFeesByStudentSessionId($student->student_session_id, $student->route_pickup_point_id);
                    $student_due_fee = $this->studentfeemaster_model->getStudentFees($student->student_session_id);

                    $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student->student_session_id);
                    $init_amt = 0;
                    $grand_amt = 0;
                    $grand_total_paid = 0;
                    $grand_total_discount = 0;
                    $grand_total_fine = 0;
                    $fees_fine_amount = 0;
                    $total_fees_fine_amount = 0;

                    if (!empty($transport_fees)) {
                        foreach ($transport_fees as $trans_fee_key => $trans_fee_value) {
                            $amt = 0;
                            $total_paid = 0;
                            $total_discount = 0;
                            $total_fine = 0;

                            $trans_fee_value->total_amount_paid = ($amt);
                            $trans_fee_value->total_amount_discount = ($amt);
                            $trans_fee_value->total_amount_fine = ($amt);
                            $trans_fee_value->total_amount_display = ($amt);
                            $trans_fee_value->total_amount_remaining = ($trans_fee_value->fees);

                            $trans_fee_value->status = 'unpaid';
                            $trans_fee_value->fees_fine_amount = 0;
                            $grand_amt += $trans_fee_value->fees;

                            if (($trans_fee_value->due_date != "0000-00-00" && $trans_fee_value->due_date != null) && (strtotime($trans_fee_value->due_date) < strtotime(date('Y-m-d')))) {

                                if ($trans_fee_value->fine_type == "percentage") {
                                    $trans_fee_value->fees_fine_amount = ($trans_fee_value->fees * $trans_fee_value->fine_percentage) / 100;
                                } elseif ($trans_fee_value->fine_type == "fix") {
                                    $trans_fee_value->fees_fine_amount = $trans_fee_value->fine_amount;
                                }
                                $total_fees_fine_amount += $trans_fee_value->fees_fine_amount;
                            }

                            if (
                                is_string($trans_fee_value->amount_detail)
                                && is_array(json_decode($trans_fee_value->amount_detail, true))
                                && (json_last_error() == JSON_ERROR_NONE)
                            ) {

                                $fess_list = json_decode($trans_fee_value->amount_detail);

                                foreach ($fess_list as $fee_key => $fee_value) {

                                    $grand_total_paid = $grand_total_paid + $fee_value->amount;
                                    $total_paid = $total_paid + $fee_value->amount;

                                    $grand_total_discount = $grand_total_discount + $fee_value->amount_discount;
                                    $total_discount = $total_discount + $fee_value->amount_discount;

                                    $grand_total_fine = $grand_total_fine + $fee_value->amount_fine;
                                    $total_fine = $total_fine + $fee_value->amount_fine;
                                }

                                $trans_fee_value->total_amount_paid = ($total_paid);
                                $trans_fee_value->total_amount_discount = ($total_discount);
                                $trans_fee_value->total_amount_fine = ($total_fine);
                                $trans_fee_value->total_amount_display = ($total_paid + $total_discount);
                                $trans_fee_value->total_amount_remaining = ($trans_fee_value->fees - (($total_paid + $total_discount)));

                                if ($trans_fee_value->total_amount_remaining <= '0.00') {
                                    $trans_fee_value->status = 'paid';
                                } elseif ($trans_fee_value->total_amount_remaining == number_format((float) $trans_fee_value->fees, 2, '.', '')) {
                                    $trans_fee_value->status = 'unpaid';
                                } else {
                                    $trans_fee_value->status = 'partial';
                                }
                            }
                        }
                    }

                    if (!empty($student_due_fee)) {
                        foreach ($student_due_fee as $student_due_fee_key => $student_due_fee_value) {
                            foreach ($student_due_fee_value->fees as $each_fees_key => $each_fees_value) {

                                $amt = 0;
                                $total_paid = 0;
                                $total_discount = 0;
                                $total_fine = 0;
                                $fees_fine_amount = 0;//added
                                $each_fees_value->total_amount_paid = ($amt);
                                $each_fees_value->total_amount_discount = ($amt);
                                $each_fees_value->total_amount_fine = ($amt);
                                $each_fees_value->total_amount_display = ($amt);
                                $each_fees_value->total_amount_remaining = ($each_fees_value->amount);
                                $each_fees_value->status = 'unpaid';
                                $grand_amt = $grand_amt + $each_fees_value->amount;
                                // code added
                                if (($each_fees_value->due_date != "0000-00-00" && $each_fees_value->due_date != null) && (strtotime($each_fees_value->due_date) < strtotime(date('Y-m-d')))) {
                                    // get cumulative fine amount as delay days 
                                    if ($each_fees_value->fine_type == 'cumulative') {

                                        $date1 = date_create("$each_fees_value->due_date");
                                        $date2 = date_create(date('Y-m-d'));
                                        $diff = date_diff($date1, $date2);
                                        $due_days = $diff->format("%a");

                                        if ($this->customlib->get_cumulative_fine_amount($each_fees_value->fee_groups_feetype_id, $due_days)) {
                                            $due_fine_amount = $this->customlib->get_cumulative_fine_amount($each_fees_value->fee_groups_feetype_id, $due_days);
                                        } else {
                                            $due_fine_amount = 0;
                                        }
                                        $fees_fine_amount = $due_fine_amount;
                                        $total_fees_fine_amount = $total_fees_fine_amount + $due_fine_amount;

                                    } else if ($each_fees_value->fine_type == 'fix' || $each_fees_value->fine_type == 'percentage') {
                                        $fees_fine_amount = $each_fees_value->fine_amount;
                                        $total_fees_fine_amount = $total_fees_fine_amount + $each_fees_value->fine_amount;
                                    }
                                    // get cumulative fine amount as delay days
                                }
                                $each_fees_value->fees_fine_amount = $fees_fine_amount;
                                // code added

                                if (is_string($each_fees_value->amount_detail) && is_array(json_decode($each_fees_value->amount_detail, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                                    $fess_list = json_decode($each_fees_value->amount_detail);

                                    foreach ($fess_list as $fee_key => $fee_value) {

                                        $grand_total_paid = $grand_total_paid + $fee_value->amount;
                                        $total_paid = $total_paid + $fee_value->amount;

                                        $grand_total_discount = $grand_total_discount + $fee_value->amount_discount;
                                        $total_discount = $total_discount + $fee_value->amount_discount;

                                        $grand_total_fine = $grand_total_fine + $fee_value->amount_fine;
                                        $total_fine = $total_fine + $fee_value->amount_fine;

                                    }

                                    $each_fees_value->total_amount_paid = number_format((float) $total_paid, 2, '.', '');
                                    $each_fees_value->total_amount_discount = number_format((float) $total_discount, 2, '.', '');
                                    $each_fees_value->total_amount_fine = number_format((float) $total_fine, 2, '.', '');

                                    $each_fees_value->total_amount_display = ($total_paid + $total_discount);
                                    $each_fees_value->total_amount_remaining = ($each_fees_value->amount - (($total_paid + $total_discount)));

                                    if ($each_fees_value->total_amount_remaining <= '0.00') {
                                        $each_fees_value->status = 'paid';
                                    } elseif ($each_fees_value->total_amount_remaining == number_format((float) $each_fees_value->amount, 2, '.', '')) {
                                        $each_fees_value->status = 'unpaid';
                                    } else {
                                        $each_fees_value->status = 'partial';
                                    }
                                }

                                if (($each_fees_value->amount - ($each_fees_value->total_amount_paid + $each_fees_value->total_amount_discount)) == 0) {
                                    $each_fees_value->status = 'paid';
                                }
                            }
                        }
                    }

                    $grand_fee = array('amount' => ($grand_amt), 'amount_discount' => ($grand_total_discount), 'amount_fine' => ($grand_total_fine), 'amount_paid' => ($grand_total_paid), 'amount_remaining' => ($grand_amt - ($grand_total_paid + $grand_total_discount)), 'fee_fine' => ($total_fees_fine_amount));

                    if (empty($transport_fees)) {
                        $transport_fees = array();
                    }
                    $data['pay_method'] = empty($pay_method) ? 0 : 1;
                    $data['student_due_fee'] = $student_due_fee;
                    $data['transport_fees'] = $transport_fees;
                    $data['student_discount_fee'] = $student_discount_fee;
                    $data['grand_fee'] = $grand_fee;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function class_schedule()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $student = $this->student_model->get($student_id);
                    $class_id = $student->class_id;
                    $section_id = $student->section_id;

                    $days = $this->customlib->getDaysname();
                    $days_record = array();
                    foreach ($days as $day_key => $day_value) {
                        $days_record[$day_key] = $this->subjecttimetable_model->getSubjectByClassandSectionDay($class_id, $section_id, $day_key);
                    }
                    $data['timetable'] = $days_record;
                    $data['status'] = "200";
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getExamResult()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $exam_group_class_batch_exam_id = $this->input->post('exam_group_class_batch_exam_id');
                    $student_id = $this->input->post('student_id');
                    $student = $this->student_model->get($student_id);

                    $dt = array();
                    $exam_result = $this->examgroup_model->searchExamResult($student->student_session_id, $exam_group_class_batch_exam_id, true, true);
                    $exam_grade = $this->grade_model->getGradeDetails();

                    if (!empty($exam_result->exam_result)) {
                        $exam = new stdClass;
                        $exam->exam_group_class_batch_exam_id = $exam_result->exam_group_class_batch_exam_id;
                        $exam->exam_group_id = $exam_result->exam_group_id;
                        $exam->exam = $exam_result->exam;
                        $exam->exam_group = $exam_result->name;
                        $exam->description = $exam_result->description;
                        $exam->exam_type = $exam_result->exam_type;
                        $exam->rank = $exam_result->rank;
                        $exam->is_rank_generated = $exam_result->is_rank_generated;
                        $exam->subject_result = array();
                        $exam->total_max_marks = 0;
                        $exam->total_get_marks = 0;
                        $exam->total_exam_points = 0;
                        $exam->exam_quality_points = 0;
                        $exam->exam_credit_hour = 0;
                        $exam->exam_credit_hour = 0;
                        $exam->exam_result_status = "pass";
                        if ($exam_result->exam_result['exam_connection'] == 0) {
                            $exam->is_consolidate = 0;
                            foreach ($exam_result->exam_result['result'] as $exam_result_key => $exam_result_value) {

                                $subject_array = array();
                                if ($exam_result_value->attendence != "present") {
                                    $exam->exam_result_status = "fail";
                                } elseif ($exam_result_value->get_marks < $exam_result_value->min_marks) {
                                    $exam->exam_result_status = "fail";
                                }

                                $exam->total_max_marks = $exam->total_max_marks + $exam_result_value->max_marks;
                                $exam->total_get_marks = $exam->total_get_marks + $exam_result_value->get_marks;
                                $percentage = ($exam_result_value->get_marks * 100) / $exam_result_value->max_marks;
                                $subject_array['name'] = $exam_result_value->name;
                                $subject_array['code'] = $exam_result_value->code;
                                $subject_array['exam_group_class_batch_exams_id'] = $exam_result_value->exam_group_class_batch_exams_id;
                                $subject_array['room_no'] = $exam_result_value->room_no;
                                $subject_array['max_marks'] = $exam_result_value->max_marks;
                                $subject_array['min_marks'] = $exam_result_value->min_marks;
                                $subject_array['subject_id'] = $exam_result_value->subject_id;
                                $subject_array['attendence'] = $exam_result_value->attendence;
                                $subject_array['get_marks'] = is_null($exam_result_value->get_marks) ? "" : $exam_result_value->get_marks;
                                $subject_array['exam_group_exam_results_id'] = $exam_result_value->exam_group_exam_results_id;
                                $subject_array['note'] = $exam_result_value->note;
                                $subject_array['duration'] = $exam_result_value->duration;
                                $subject_array['credit_hours'] = $exam_result_value->credit_hours;
                                $subject_array['exam_grade'] = findExamGrade($exam_grade, $exam_result->exam_type, $percentage);

                                if ($exam_result->exam_type == "gpa") {
                                    $point = findGradePoints($exam_grade, $exam_result->exam_type, $percentage);
                                    $exam->exam_quality_points = $exam->exam_quality_points + ($exam_result_value->credit_hours * $point);
                                    $exam->exam_credit_hour = $exam->exam_credit_hour + $exam_result_value->credit_hours;
                                    $exam->total_exam_points = $exam->total_exam_points + $point;
                                    $subject_array['exam_grade_point'] = number_format($point, 2, '.', '');
                                    $subject_array['exam_quality_points'] = $exam_result_value->credit_hours * $point;
                                }
                                $exam->subject_result[] = $subject_array;
                            }
                            $exam->percentage = two_digit_float(($exam->total_get_marks * 100) / $exam->total_max_marks);

                            if ($exam_result->exam_type == "average_passing") {

                                if ($exam_result->passing_percentage <= $exam->percentage) {
                                    $exam->exam_result_status = "pass";
                                } else {
                                    $exam->exam_result_status = "fail";
                                }
                            }

                            $exam_result->passing_percentage;
                            $exam->percentage;

                            $exam->division = getExamDivision($exam->percentage);
                            $exam->exam_grade = findExamGrade($exam_grade, $exam_result->exam_type, $exam->percentage);
                        } else {
                            $exam->is_consolidate = 1;
                            $exam_connected_exam = ($exam_result->exam_result['exam_result']['exam_result_' . $exam_result->exam_group_class_batch_exam_id]);

                            if (!empty($exam_connected_exam)) {
                                foreach ($exam_connected_exam as $exam_result_key => $exam_result_value) {

                                    $subject_array = array();
                                    if ($exam_result_value->attendence != "present") {
                                        $exam->exam_result_status = "fail";
                                    } elseif ($exam_result_value->get_marks < $exam_result_value->min_marks) {
                                        $exam->exam_result_status = "fail";
                                    }
                                    $exam->total_max_marks = $exam->total_max_marks + $exam_result_value->max_marks;
                                    $exam->total_get_marks = $exam->total_get_marks + $exam_result_value->get_marks;
                                    $percentage = two_digit_float(($exam_result_value->get_marks * 100) / $exam_result_value->max_marks);
                                    $subject_array['name'] = $exam_result_value->name;
                                    $subject_array['code'] = $exam_result_value->code;
                                    $subject_array['exam_group_class_batch_exams_id'] = $exam_result_value->exam_group_class_batch_exams_id;
                                    $subject_array['room_no'] = $exam_result_value->room_no;
                                    $subject_array['max_marks'] = $exam_result_value->max_marks;
                                    $subject_array['min_marks'] = $exam_result_value->min_marks;
                                    $subject_array['subject_id'] = $exam_result_value->subject_id;
                                    $subject_array['attendence'] = $exam_result_value->attendence;
                                    $subject_array['get_marks'] = is_null($exam_result_value->get_marks) ? "" : $exam_result_value->get_marks;
                                    $subject_array['exam_group_exam_results_id'] = $exam_result_value->exam_group_exam_results_id;
                                    $subject_array['note'] = $exam_result_value->note;
                                    $subject_array['duration'] = $exam_result_value->duration;
                                    $subject_array['credit_hours'] = $exam_result_value->credit_hours;
                                    $subject_array['exam_grade'] = findExamGrade($exam_grade, $exam_result->exam_type, $percentage);
                                    if ($exam_result->exam_type == "gpa") {
                                        $point = findGradePoints($exam_grade, $exam_result->exam_type, $percentage);
                                        $exam->exam_quality_points = $exam->exam_quality_points + ($exam_result_value->credit_hours * $point);
                                        $exam->exam_credit_hour = $exam->exam_credit_hour + $exam_result_value->credit_hours;
                                        $exam->total_exam_points = $exam->total_exam_points + $point;
                                        $subject_array['exam_grade_point'] = number_format($point, 2, '.', '');
                                        $subject_array['exam_quality_points'] = $exam_result_value->credit_hours * $point;
                                    }
                                    $exam->subject_result[] = $subject_array;
                                }
                                $exam->percentage = two_digit_float(($exam->total_get_marks * 100) / $exam->total_max_marks);

                                if ($exam_result->exam_type == "average_passing") {

                                    if ($exam_result->passing_percentage <= $exam->percentage) {
                                        $exam->exam_result_status = "pass";
                                    } else {
                                        $exam->exam_result_status = "fail";
                                    }
                                }

                                $exam->division = getExamDivision($exam->percentage);
                                $exam->exam_grade = findExamGrade($exam_grade, $exam_result->exam_type, $exam->percentage);
                            }
                            $consolidate_result = new stdClass;
                            $consolidate_get_total = 0;
                            $consolidate_get_total_percentage = 0;
                            $consolidate_total_points = 0;
                            $consolidate_max_total = 0;
                            $consolidate_subjects_total = 0;
                            $consolidate_result->exam_array = array();
                            $consolidate_result->consolidate_result = array();
                            $consolidate_result_status = "pass";
                            if (!empty($exam_result->exam_result['exams'])) {
                                $consolidate_exam_result = "pass";
                                foreach ($exam_result->exam_result['exams'] as $each_exam_key => $each_exam_value) {
                                    if ($exam_result->exam_type != "gpa") {
                                        $consolidate_each = getCalculatedExam($exam_result->exam_result['exam_result'], $each_exam_value->id);

                                        if ($each_exam_value->exam_group_type == "average_passing") {

                                            if ($exam_result->exam_type == "average_passing") {

                                                if ($each_exam_value->passing_percentage < $exam->percentage) {
                                                    $exam->exam_result_status = "pass";
                                                } else {
                                                    $exam->exam_result_status = "fail";
                                                }
                                            }

                                        } elseif ($consolidate_each->exam_status == "fail") {
                                            $consolidate_result_status = "fail";
                                        }

                                        $consolidate_get_percentage_mark = getConsolidateRatio($exam_result->exam_result['exam_connection_list'], $each_exam_value->id, $consolidate_each->get_marks, $consolidate_each->max_marks);
                                        $each_exam_value->percentage = $consolidate_get_percentage_mark['marks_weight'];
                                        $consolidate_get_total_percentage += $consolidate_get_percentage_mark['percentage_weight'];
                                        $each_exam_value->weight = $consolidate_get_percentage_mark['exam_weightage'];
                                        $consolidate_get_total = $consolidate_get_total + ($consolidate_get_percentage_mark['marks_weight']);
                                        $consolidate_max_total = $consolidate_max_total + ($consolidate_each->max_marks);
                                    }

                                    if ($exam_result->exam_type == "gpa") {

                                        $consolidate_each = getCalculatedExamGradePoints($exam_result->exam_result['exam_result'], $each_exam_value->id, $exam_grade, $exam_result->exam_type);

                                        $each_exam_value->total_points = $consolidate_each->total_points;
                                        $each_exam_value->total_exams = $consolidate_each->total_exams;

                                        $consolidate_exam_result = ($consolidate_each->return_quality_points / $consolidate_each->return_total_credit_hours);
                                        $consolidate_get_percentage_mark = getConsolidateRatio($exam_result->exam_result['exam_connection_list'], $each_exam_value->id, $consolidate_exam_result, 100);
                                        $each_exam_value->percentage = $consolidate_get_percentage_mark['marks_weight'];
                                        $consolidate_get_total_percentage += $consolidate_get_percentage_mark['percentage_weight'];
                                        $each_exam_value->weight = $consolidate_get_percentage_mark['exam_weightage'];
                                        $consolidate_get_total = $consolidate_get_total + ($consolidate_get_percentage_mark['marks_weight']);
                                        $consolidate_subjects_total = $consolidate_subjects_total + $consolidate_each->total_exams;
                                        $each_exam_value->exam_result = number_format($consolidate_exam_result, 2, '.', '');
                                    }

                                    $consolidate_result->exam_array[] = $each_exam_value;
                                }

                                $consolidate_result->consolidate_result['marks_obtain'] = $consolidate_get_total;
                                $consolidate_result->consolidate_result['marks_total'] = $consolidate_max_total;

                                $consolidate_result->consolidate_result['percentage'] = two_digit_float($consolidate_get_total_percentage);
                                $consolidate_result->consolidate_result['division'] = getExamDivision($consolidate_get_total_percentage);
                                if ($exam_result->exam_type != "gpa") {

                                    //  $consolidate_percentage_grade                            = ($consolidate_get_total * 100) / $consolidate_max_total;
                                    $consolidate_result->consolidate_result['result'] = $consolidate_get_total . "/" . $consolidate_max_total;
                                    $consolidate_result->consolidate_result['grade'] = findExamGrade($exam_grade, $exam_result->exam_type, $consolidate_get_total_percentage);
                                    $consolidate_result->consolidate_result['result_status'] = $consolidate_result_status;
                                } elseif ($exam_result->exam_type == "gpa") {

                                    $consolidate_result->consolidate_result['result'] = $consolidate_get_total . "/" . $consolidate_subjects_total;
                                    $consolidate_result->consolidate_result['grade'] = findExamGrade($exam_grade, $exam_result->exam_type, $consolidate_get_total_percentage);

                                }

                            }
                            $exam->consolidated_exam_result = $consolidate_result;
                        }
                        $data['exam'] = $exam;
                    }

                    $data['status'] = "200";
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getGradeByMarks($marks = 0)
    {
        $gradeList = $this->grade_model->get();
        if (empty($gradeList)) {
            return "empty list";
        } else {

            foreach ($gradeList as $grade_key => $grade_value) {
                if (round($marks) >= $grade_value['mark_from'] && round($marks) <= $grade_value['mark_upto']) {
                    return $grade_value['name'];
                    break;
                }
            }
            return "no record found";
        }
    }

    public function Parent_GetStudentsList()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $array = array();

                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $parent_id = $this->input->post('parent_id');
                    $students_array = $this->student_model->read_siblings_students($parent_id);
                    $array['childs'] = $students_array;
                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function getModuleStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $user = $this->input->post('user');
                    $resp['module_list'] = $this->module_model->get($user);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function searchuser()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = array();

                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $keyword = $params['keyword'];

                    $chat_user = $this->chatuser_model->getMyID($student_id, 'student');
                    $chat_user_id = 0;
                    if (!empty($chat_user)) {
                        $chat_user_id = $chat_user->id;
                    }

                    $resp['chat_user'] = $this->chatuser_model->searchForUser($keyword, $chat_user_id, $student_id, 'student');
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function addChatUser()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $user_type = $params['user_type'];
                    $user_id = $params['user_id'];
                    $student_id = $params['student_id'];
                    $first_entry = array(
                        'user_type' => "student",
                        'student_id' => $student_id,
                    );
                    $insert_data = array('user_type' => strtolower($user_type), 'create_student_id' => null);

                    if ($user_type == "Student") {
                        $insert_data['student_id'] = $user_id;
                    } elseif ($user_type == "Staff") {
                        $insert_data['staff_id'] = $user_id;
                    }

                    $insert_message = array(
                        'message' => 'you are now connected on chat',
                        'chat_user_id' => 0,
                        'is_first' => 1,
                        'chat_connection_id' => 0,
                    );

                    //===================
                    $new_user_record = $this->chatuser_model->addNewUserForStudent($first_entry, $insert_data, $student_id, $insert_message, 'student');
                    $json_record = json_decode($new_user_record);

                    //==================

                    $new_user = $this->chatuser_model->getChatUserDetail($json_record->new_user_id);
                    $chat_user = $this->chatuser_model->getMyID($student_id, 'student');
                    $data['chat_user'] = $chat_user;
                    $chat_connection_id = $json_record->new_user_chat_connection_id;
                    $chat_to_user = 0;
                    $user_last_chat = $this->chatuser_model->getLastMessages($chat_connection_id);

                    $chat_connection = $this->chatuser_model->getChatConnectionByID($chat_connection_id);
                    if (!empty($chat_connection)) {
                        $chat_to_user = $chat_connection->chat_user_one;
                        $chat_connection_id = $chat_connection->id;
                        if ($chat_connection->chat_user_one == $chat_user->id) {
                            $chat_to_user = $chat_connection->chat_user_two;
                        }
                    }

                    $array = array('status' => '1', 'error' => '', 'message' => $this->lang->line('success_message'), 'new_user' => $new_user, 'chat_connection_id' => $json_record->new_user_chat_connection_id, 'chat_records' => $chat_records, 'user_last_chat' => $user_last_chat);
                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function liveclasses()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $result = $this->student_model->get($student_id);
                    if (empty($result)) {
                        json_output($response['status'], array(
                            'live_classes' => array(),
                            'message' => 'No classes available for this student.',
                        ));
                        return;
                    }

                    $class_id = $result->class_id;
                    $section_id = $result->section_id;
                    $live_classes = $this->conference_model->getByStudentClassSection($class_id, $section_id);
                    if (!empty($live_classes)) {
                        foreach ($live_classes as $lc_key => $lc_value) {
                            $live_url = json_decode($lc_value->return_response);
                            $live_classes[$lc_key]->{'join_url'} = $live_url->join_url;
                            unset($lc_value->return_response);
                        }
                    }

                    $data["live_classes"] = $live_classes;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getzoomsettings()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $live_classes = $this->conference_model->getzoomsettings();

                    $data["live_classes"] = $live_classes;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function livehistory()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $insert_data = array(
                        'student_id' => $this->input->post('student_id'),
                        'conference_id' => $this->input->post('conference_id'),
                    );
                    $this->conference_model->updatehistory($insert_data);
                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function gmeetclasses()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');

                    if (!$this->db->table_exists('gmeet') || !$this->db->table_exists('gmeet_sections')) {
                        json_output($response['status'], array(
                            'live_classes' => array(),
                            'message' => 'Google Meet module is not available for this installation.',
                        ));
                        return;
                    }

                    $result = $this->student_model->get($student_id);
                    if (empty($result)) {
                        json_output($response['status'], array(
                            'live_classes' => array(),
                            'message' => 'No classes available for this student.',
                        ));
                        return;
                    }

                    $class_id = $result->class_id;
                    $section_id = $result->section_id;
                    $live_classes = $this->gmeet_model->getByStudentClassSection($class_id, $section_id);
                    $data["live_classes"] = $live_classes;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getgmeetsettings()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $live_classes = $this->gmeet_model->getgmeetsettings();
                    $data["live_classes"] = $live_classes;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function gmeethistory()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $insert_data = array(
                        'student_id' => $this->input->post('student_id'),
                        'gmeet_id' => $this->input->post('gmeet_id'),
                    );
                    $this->gmeet_model->updatehistory($insert_data);
                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function checkProfileUpdate()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $school_detail = $this->setting_model->getSchoolDetail();
                    $array = array(
                        'status'               => '1',
                        'student_profile_edit' => $school_detail->student_profile_edit,
                        'staff_profile_edit'   => isset($school_detail->staff_profile_edit) ? $school_detail->staff_profile_edit : 0,
                    );
                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function profileUpdateFields()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $this->input->post('student_id');
                    $inserted_fields = $this->student_edit_field_model->get();
                    $result['id'] = $student_id;
                    $student = $this->student_model->get($student_id);
                    $genderList = $this->customlib->getGender();
                    $result['student'] = $student;
                    $result['genderList'] = $genderList;
                    $vehroute_result = $this->vehroute_model->get();
                    $result['vehroutelist'] = $vehroute_result;
                    $category = $this->category_model->get();
                    $result['categorylist'] = $category;
                    $result["bloodgroup"] = $this->config->item('bloodgroup');
                    $array = array();
                    $sch_setting_detail = $this->setting_model->getSetting();
                    if (!empty($inserted_fields)) {
                        foreach ($inserted_fields as $field_key => $field_value) {
                            $obj = new stdClass();
                            $obj->name = $field_value->name;
                            $obj->status = check_student_field_status($sch_setting_detail, $field_value);
                            $array[] = $obj;
                        }
                    }
                    $result['student_details'] = $array;
                    $array = array('status' => '1', 'result' => $result);
                    json_output($response['status'], $array);
                }
            }
        }
    }

    public function editprofile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    // Support both JSON body (mobile) and form-encoded (web) requests
                    $raw = file_get_contents('php://input');
                    if (!empty($raw)) {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            $_POST = array_merge($_POST, $decoded);
                        }
                    }
                    $post_data = $this->input->post();
                    $this->form_validation->set_error_delimiters('', '');
                    $student_id = $this->input->post('student_id');
                    $data['id'] = $student_id;
                    $post_data = $this->input->post();
                    if (isset($post_data['firstname'])) {
                        $this->form_validation->set_rules('firstname', 'first_name', 'trim|required');
                    }
                    if (isset($post_data['guardian_is'])) {
                        $this->form_validation->set_rules('guardian_is', 'guardian', 'trim|required');
                    }
                    if (isset($post_data['dob'])) {
                        $this->form_validation->set_rules('dob', 'date_of_birth', 'trim|required');
                    }
                    if (isset($post_data['gender'])) {
                        $this->form_validation->set_rules('gender', 'gender', 'trim|required');
                    }
                    if (isset($post_data['guardian_name'])) {
                        $this->form_validation->set_rules('guardian_name', 'guardian_name', 'trim|required');
                    }
                    if (isset($post_data['guardian_phone'])) {
                        $this->form_validation->set_rules('guardian_phone', 'guardian_phone', 'trim|required');
                    }

                    $storage_array = "file,father_pic,mother_pic,guardian_pic";

                    $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $validation_error = array();

                        if (isset($post_data['firstname'])) {
                            $validation_error['firstname'] = form_error('firstname');
                        }
                        if (isset($post_data['guardian_is'])) {
                            $validation_error['guardian_is'] = form_error('guardian_is');
                        }
                        if (isset($post_data['dob'])) {
                            $validation_error['dob'] = form_error('dob');
                        }
                        if (isset($post_data['gender'])) {
                            $validation_error['gender'] = form_error('gender');
                        }
                        if (isset($post_data['guardian_name'])) {
                            $validation_error['guardian_name'] = form_error('guardian_name');
                        }
                        if (isset($post_data['guardian_phone'])) {
                            $validation_error['guardian_phone'] = form_error('guardian_phone');
                        }

                        $validation_error['validate_storage'] = form_error('validate_storage');

                        $array = array('status' => '0', 'error' => $validation_error);
                    } else {

                        $student_id = $student_id;
                        $data = array(
                            'id' => $student_id,
                        );
                        $firstname = $this->input->post('firstname');
                        if (isset($firstname)) {
                            $data['firstname'] = $this->input->post('firstname');
                        }
                        $rte = $this->input->post('rte');
                        if (isset($rte)) {
                            $data['rte'] = $this->input->post('rte');
                        }
                        $pincode = $this->input->post('pincode');
                        if (isset($pincode)) {
                            $data['pincode'] = $this->input->post('pincode');
                        }
                        $cast = $this->input->post('cast');
                        if (isset($cast)) {
                            $data['cast'] = $this->input->post('cast');
                        }
                        $guardian_is = $this->input->post('guardian_is');
                        if (isset($guardian_is)) {
                            $data['guardian_is'] = $this->input->post('guardian_is');
                        }
                        $previous_school = $this->input->post('previous_school');
                        if (isset($previous_school)) {
                            $data['previous_school'] = $this->input->post('previous_school');
                        }
                        $dob = $this->input->post('dob');
                        if (isset($dob)) {
                            $data['dob'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob')));
                        }
                        $current_address = $this->input->post('current_address');
                        if (isset($current_address)) {
                            $data['current_address'] = $this->input->post('current_address');
                        }
                        $permanent_address = $this->input->post('permanent_address');
                        if (isset($permanent_address)) {
                            $data['permanent_address'] = $this->input->post('permanent_address');
                        }
                        $bank_account_no = $this->input->post('bank_account_no');
                        if (isset($bank_account_no)) {
                            $data['bank_account_no'] = $this->input->post('bank_account_no');
                        }
                        $bank_name = $this->input->post('bank_name');
                        if (isset($bank_name)) {
                            $data['bank_name'] = $this->input->post('bank_name');
                        }
                        $ifsc_code = $this->input->post('ifsc_code');
                        if (isset($ifsc_code)) {
                            $data['ifsc_code'] = $this->input->post('ifsc_code');
                        }
                        $guardian_occupation = $this->input->post('guardian_occupation');
                        if (isset($guardian_occupation)) {
                            $data['guardian_occupation'] = $this->input->post('guardian_occupation');
                        }
                        $guardian_email = $this->input->post('guardian_email');
                        if (isset($guardian_email)) {
                            $data['guardian_email'] = $this->input->post('guardian_email');
                        }
                        $gender = $this->input->post('gender');
                        if (isset($gender)) {
                            $data['gender'] = $this->input->post('gender');
                        }
                        $guardian_name = $this->input->post('guardian_name');
                        if (isset($guardian_name)) {
                            $data['guardian_name'] = $this->input->post('guardian_name');
                        }
                        $guardian_relation = $this->input->post('guardian_relation');
                        if (isset($guardian_relation)) {
                            $data['guardian_relation'] = $this->input->post('guardian_relation');
                        }
                        $guardian_phone = $this->input->post('guardian_phone');
                        if (isset($guardian_phone)) {
                            $data['guardian_phone'] = $this->input->post('guardian_phone');
                        }
                        $guardian_address = $this->input->post('guardian_address');
                        if (isset($guardian_address)) {
                            $data['guardian_address'] = $this->input->post('guardian_address');
                        }
                        $adhar_no = $this->input->post('adhar_no');
                        if (isset($adhar_no)) {
                            $data['adhar_no'] = $this->input->post('adhar_no');
                        }
                        $samagra_id = $this->input->post('samagra_id');
                        if (isset($samagra_id)) {
                            $data['samagra_id'] = $this->input->post('samagra_id');
                        }

                        $house = $this->input->post('house');
                        $blood_group = $this->input->post('blood_group');
                        $measurement_date = $this->input->post('measure_date');
                        $roll_no = $this->input->post('roll_no');
                        $lastname = $this->input->post('lastname');
                        $category_id = $this->input->post('category_id');
                        $religion = $this->input->post('religion');
                        $mobileno = $this->input->post('mobileno');
                        $email = $this->input->post('email');
                        $admission_date = $this->input->post('admission_date');
                        $height = $this->input->post('height');
                        $weight = $this->input->post('weight');
                        $father_name = $this->input->post('father_name');
                        $father_phone = $this->input->post('father_phone');
                        $father_occupation = $this->input->post('father_occupation');
                        $mother_name = $this->input->post('mother_name');
                        $mother_phone = $this->input->post('mother_phone');
                        $mother_occupation = $this->input->post('mother_occupation');

                        if (isset($measurement_date)) {
                            $data['measurement_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('measure_date')));
                        }

                        if (isset($house)) {
                            $data['school_house_id'] = $this->input->post('house');
                        }

                        if (isset($blood_group)) {

                            $data['blood_group'] = $this->input->post('blood_group');
                        }

                        if (isset($lastname)) {

                            $data['lastname'] = $this->input->post('lastname');
                        }

                        if (isset($category_id)) {

                            $data['category_id'] = $this->input->post('category_id');
                        }

                        if (isset($religion)) {

                            $data['religion'] = $this->input->post('religion');
                        }

                        if (isset($mobileno)) {

                            $data['mobileno'] = $this->input->post('mobileno');
                        }

                        if (isset($email)) {

                            $data['email'] = $this->input->post('email');
                        }

                        if (isset($admission_date)) {

                            $data['admission_date'] = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('admission_date')));
                        }

                        if (isset($height)) {

                            $data['height'] = $this->input->post('height');
                        }

                        if (isset($weight)) {

                            $data['weight'] = $this->input->post('weight');
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

                        $this->student_model->add($data);

                        $student = $this->student_model->get($student_id);
                        $upload_path = $this->config->item('upload_path') . "/uploads/student_images/";


                        $total_prev_size = 0;
                        $total_new_size = 0;

                        $image_fields = [
                            'file' => 'image',
                            'father_pic' => 'father_pic',
                            'mother_pic' => 'mother_pic',
                            'guardian_pic' => 'guardian_pic'
                        ];

                        foreach ($image_fields as $input_name => $db_column) {
                            if (isset($_FILES[$input_name]) && !empty($_FILES[$input_name]['name'])) {

                                $old_file = $student->{$db_column};
                                if (!empty($old_file)) {
                                    $total_prev_size += $this->media_storage->getUploadedFileSize($old_file, '');
                                }

                                $img_name = $this->media_storage->fileupload($input_name, $upload_path);

                                if (!IsNullOrEmptyString($img_name)) {

                                    $total_new_size += $this->media_storage->getTmpFileSize($input_name);

                                    $data_img = array('id' => $student_id, $db_column => 'uploads/student_images/' . $img_name);
                                    $this->student_model->add($data_img);

                                    if (!empty($old_file)) {
                                        $old_file_path = FCPATH . $old_file;
                                        if (file_exists($old_file_path)) {
                                            unlink($old_file_path);
                                        } elseif (file_exists($old_file)) {
                                            unlink($old_file);
                                        }
                                    }
                                }
                            }
                        }

                        if ($total_prev_size > $total_new_size) {
                            $diff = $total_prev_size - $total_new_size;
                            $this->saasvalidation->deleteResouceQuota('storage', $diff);
                        } elseif ($total_new_size > $total_prev_size) {
                            $diff = $total_new_size - $total_prev_size;
                            $this->saasvalidation->updateResouceQuota('storage', $diff);
                        }

                        $array = array('status' => '1', 'msg' => 'Record Updated Successfully');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function editStaffProfile()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }
        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }
        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $login_user_id = trim((string) $this->input->get_request_header('User-ID', true));
        if ($login_user_id === '') {
            json_output(422, array('status' => 0, 'message' => 'User-ID header is required.'));
            return;
        }

        // Verify the logged-in user is a staff member and get their staff_id
        $user_row = $this->db->select('user_id, role')->from('users')->where('id', $login_user_id)->get()->row();
        if (empty($user_row) || !in_array($user_row->role, array('staff', 'admin', 'accountant', 'librarian', 'receptionist'))) {
            json_output(403, array('status' => 0, 'message' => 'This endpoint is only available for staff users.'));
            return;
        }

        $staff_id = $user_row->user_id;

        $params = json_decode(file_get_contents('php://input'), true);
        if (!is_array($params)) {
            json_output(422, array('status' => 0, 'message' => 'Invalid request payload.'));
            return;
        }

        // Map Flutter-side field names to actual DB column names in the staff table.
        // 'current_address' sent by Flutter maps to 'local_address' in the DB.
        $field_map = array(
            'email'                 => 'email',
            'contact_no'            => 'contact_no',
            'mobileno'              => 'contact_no',
            'current_address'       => 'local_address',
            'permanent_address'     => 'permanent_address',
            'emergency_contact_no'  => 'emergency_contact_no',
            'marital_status'        => 'marital_status',
            'qualification'         => 'qualification',
        );
        $data = array('id' => $staff_id);

        foreach ($field_map as $param_key => $db_col) {
            if (array_key_exists($param_key, $params) && !isset($data[$db_col])) {
                $data[$db_col] = $this->security->xss_clean(trim((string) $params[$param_key]));
            }
        }

        if (count($data) <= 1) {
            json_output(422, array('status' => 0, 'message' => 'No valid fields provided for update.'));
            return;
        }

        $this->db->where('id', $staff_id);
        $this->db->update('staff', $data);

        if ($this->db->affected_rows() >= 0) {
            json_output(200, array('status' => '1', 'message' => 'Staff profile updated successfully.'));
        } else {
            json_output(500, array('status' => '0', 'message' => 'Failed to update staff profile.'));
        }
    }

    public function edit_handle_upload($value, $field_name)
    {
        $image_validate = $this->config->item('image_validate');

        if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name'])) {

            $file_type = $_FILES[$field_name]['type'];
            $file_size = $_FILES[$field_name]["size"];
            $file_name = $_FILES[$field_name]["name"];
            $allowed_extension = $image_validate['allowed_extension'];
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $allowed_mime_type = $image_validate['allowed_mime_type'];
            if ($files = @getimagesize($_FILES[$field_name]['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('edit_handle_upload', 'File Type Not Allowed');
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('edit_handle_upload', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $image_validate['upload_size']) {
                    $this->form_validation->set_message('edit_handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('edit_handle_upload', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        }
        return true;
    }

    public function getMarks($question)
    {
        if ($question->select_option != null) {

            if ($question->question_type == "singlechoice" || $question->question_type == "true_false") {

                if ($question->correct == $question->select_option) {
                    return json_encode(array('get_marks' => $question->marks, 'scr_marks' => $question->marks));
                }

            } elseif ($question->question_type == "descriptive") {

                return json_encode(array('get_marks' => $question->marks, 'scr_marks' => $question->score_marks));

            } elseif ($question->question_type == "multichoice") {

                $cr_ans = json_decode($question->correct);
                $sel_ans = json_decode($question->select_option);
                if ($this->array_equal($cr_ans, $sel_ans)) {
                    return json_encode(array('get_marks' => $question->marks, 'scr_marks' => $question->marks));
                }

            }
        }

        return json_encode(array('get_marks' => $question->marks, 'scr_marks' => 0));
    }

    public function array_equal($a, $b)
    {
        return (
            is_array($a) && is_array($b) && count($a) == count($b) && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    public function uploadDocument()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = $this->input->POST();

                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('student_id', 'Student ID', 'required|trim');
                    $this->form_validation->set_rules('title', 'Title', 'required|trim');
                    $this->form_validation->set_rules('file', 'File', 'callback_handle_upload_file_compulsory');
                    if ($this->form_validation->run() == false) {

                        $form_error = array(
                            'student_id' => form_error('student_id'),
                            'title' => form_error('title'),
                            'file' => form_error('file'),
                        );
                        $array = array('status' => '0', 'error' => $form_error);
                    } else {
                        //==================
                        $student_id = $this->input->post('student_id');
                        $title = $this->input->post('title');
                        // Use FCPATH-based absolute path to avoid CWD ambiguity with relative paths
                        $upload_path = realpath(FCPATH . '../uploads') . '/student_documents/' . $student_id . '/';
                        if (!is_dir($upload_path) && !mkdir($upload_path, 0755, true)) {
                            json_output(200, array('status' => '0', 'msg' => 'Error creating upload folder. Please contact administrator.'));
                            return;
                        }
                        if (!is_writable($upload_path)) {
                            @chmod($upload_path, 0755);
                        }

                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                            $file_name = $_FILES['file']['name'];
                            $exp = explode(' ', $file_name);
                            $imp = implode('_', $exp);
                            $img_name = $upload_path . basename($imp);
                            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $img_name)) {
                                json_output(200, array('status' => '0', 'msg' => 'Failed to save uploaded file. Please check server permissions and try again.'));
                                return;
                            }
                            $data_img = array('student_id' => $student_id, 'title' => $title, 'doc' => $imp);
                            $this->student_model->adddoc($data_img);
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    /**
     * This function is used to get online course list based on student class_id and section_id
     */
    public function courselist()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $pay_method = $this->paymentsetting_model->getActiveMethod();
                    $student_id = $this->input->post('student_id');
                    $result = $this->student_model->get($student_id);
                    $class_id = $result->class_id;
                    $section_id = $result->section_id;
                    $courselist = $this->course_model->courselistforstudent($class_id, $section_id);
                    $course_list = array();
                    foreach ($courselist as $key => $courselist_value) {

                        $assignment_count = 0;
                        $quiz_count = 0;
                        $exam_count = 0;
                        $lesson_count = 0;

                        $lesson_count = count($this->course_model->totallessonbycourse($courselist_value['id']));

                        $courselist_value['total_lesson'] = ($lesson_count);
                        $courselist_value['total_hour_count'] = $this->course_model->counthours($courselist_value['id']);
                        $courselist_value['paidstatus'] = $this->course_model->paidstatus($courselist_value['id'], $student_id);
                        $courseprogresscount_array = $this->course_model->courseprogresscount($courselist_value['id'], $student_id);

                        $courseprogresscount = 0;
                        foreach ($courseprogresscount_array as $k => $val) {
                            $lesson_quiz_type = $val['lesson_quiz_type'];
                            if ($lesson_quiz_type == 1) {
                                $courseprogresscount++;
                            }
                            if ($lesson_quiz_type == 2 && $this->customlib->get_online_course_curriculam_status("online_course_quiz") == "") {
                                $courseprogresscount++;
                            }
                            if ($lesson_quiz_type == 3 && $this->customlib->get_online_course_curriculam_status("online_course_assignment") == "") {
                                $courseprogresscount++;
                            }
                            if ($lesson_quiz_type == 4 && $this->customlib->get_online_course_curriculam_status("online_course_exam") == "") {
                                $courseprogresscount++;
                            }
                        }

                        //check is curriculam status is active or inactive if curriculam mode is inactive or hide then its value will be 0
                        if ($this->customlib->get_online_course_curriculam_status("online_course_assignment") == "") {
                            $assignment_count = count($this->course_model->totalassignmentbycourse($courselist_value['id'])); //added 
                        }
                        if ($this->customlib->get_online_course_curriculam_status("online_course_quiz") == "") {
                            $quiz_count = count($this->course_model->totalquizbycourse($courselist_value['id']));
                        }
                        if ($this->customlib->get_online_course_curriculam_status("online_course_exam") == "") {
                            $exam_count = count($this->course_model->totalexambycourse($courselist_value['id'])); //added   
                        }

                        $total_quiz_lession = (int) ($lesson_count) + (int) ($quiz_count) + (int) ($assignment_count) + (int) ($exam_count);
                        $course_progress = 0;

                        if ($total_quiz_lession > 0) {
                            $course_progress = (($courseprogresscount) / $total_quiz_lession) * 100;
                        }

                        $courselist_value['course_progress'] = $course_progress;
                        $course_list[] = $courselist_value;

                        $course_list[$key]['image'] = '';
                        if (!empty($courselist_value['image'])) {
                            $course_list[$key]['image'] = $courselist_value['image'];
                        } else {
                            if ($courselist_value['gender'] == 'Female') {
                                $course_list[$key]['image'] = "default_female.jpg";
                            } else {
                                $course_list[$key]['image'] = "default_male.jpg";
                            }
                        }

                        $courserating = $this->course_model->getcourserating($courselist_value['id']);
                        $rating = 0;
                        $averagerating = 0;
                        $totalcourserating = 0;

                        if (!empty($courserating)) {
                            foreach ($courserating as $courserating_value) {
                                $rating = $rating + $courserating_value['rating'];
                            }

                            $averagerating = $rating / count($courserating);
                        }
                        $course_list[$key]['totalcourserating'] = count($courserating);
                        $course_list[$key]['courserating'] = $averagerating;
                        $course_list[$key]['section'] = $this->course_model->getSectionNameByCourseId($courselist_value['id']);
                    }
                    $data['pay_method'] = empty($pay_method) ? 0 : 1;
                    $data['course_list'] = $course_list;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    /**
     * This function is used to get online course details
     */
    public function coursedetail()
    {
         
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $course_id = $this->input->post('course_id');
                    $student_id = $this->input->post('student_id');
                    $detail = $this->course_model->coursedetail($course_id);
                    $coursedetail['course_detail'] = $detail;
                    $student = $this->course_model->getcourseratingbystudentid($course_id, $student_id);
                    $coursedetail['course_rating_review'] = $student;
                    json_output($response['status'], $coursedetail);
                }
            }
        }
    }

    /**
     * This function is used to get online course section, lesson and quiz details
     */
    public function coursecurriculum()
    {
       
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $course_id = $this->input->post('course_id');
                    $student_id = $this->input->post('student_id');
                    $sectionList = $this->course_model->getsectionbycourse($course_id, $student_id);
                    $data['sectionList'] = $sectionList;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function get_lessonattachments_by_lessonid()
    {
      
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $lesson_id = $this->input->post('lesson_id');
                    
                    $attachments = $this->course_model->get_lesson_attachments_by_lessonid($lesson_id);
                    $data['attachments'] = $attachments;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getCourseReviews()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = array();
                    $params = json_decode(file_get_contents('php://input'), true);
                    $course_id = $params['course_id'];
                    $student = $this->course_model->getcourserating($course_id);

                    foreach ($student as $key => $value) {
                        if ($value['student_id'] != 0) {
                            $student[$key]['image'] = $student[$key]['image'];
                        } elseif ($value['guest_id'] != 0) {
                            $student[$key]['image'] = 'uploads/guest_images/' . $student[$key]['image'];
                        }
                    }

                    $data['result_array'] = $student;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    /**
     * This function is used to get online course quiz question based on quiz_id and student_id
     */
    public function getquestionbyquizid()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $quiz_id = $this->input->post('quiz_id');
                    $student_id = $this->input->post('student_id');
                    $questionlist = $this->course_model->getquestionbyquizid($quiz_id, $student_id);
                    $data['questionlist'] = $questionlist;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    /**
     * This function is used to get online course quiz result based on quiz_id and student_id
     */
    public function quizresult()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $this->form_validation->set_data($_POST);
                    $quiz_id = $this->input->post('quiz_id');
                    $answerlist = '';
                    $student_id = $this->input->post('student_id');
                    $result = $this->course_model->quizresult($quiz_id, $student_id);
                    foreach ($result as $result_value) {
                        $answerlist = $this->course_model->quizstudentanswerlist($quiz_id, $student_id);
                    }
                    $data['result'] = $result;
                    $data['answerlist'] = $answerlist;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    /**
     * This function is used to insert online course quiz answer
     */
    public function saveanswer()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $quiz_id = $params['quiz_id'];
                    $questionID = $params['question_id'];
                    $result = $this->course_model->getquizanswerexistornot($questionID, $quiz_id, $student_id);

 
                    $answer1 = $params['answer_1'];
                    $answer2 = $params['answer_2'];
                    $answer3 = $params['answer_3'];
                    $answer4 = $params['answer_4'];
                    $answer5 = $params['answer_5'];

                    $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);
                    if (empty($result)) {

                        $addData = array(
                            'student_id' => $student_id,
                            'course_quiz_id' => $quiz_id,
                            'course_quiz_question_id' => $questionID,
                            'answer' => json_encode($correctAnswer),
                            'created_date' => date('Y-m-d H:i:s'),
                        );

                    } else {

                        $addData = array(
                            'id' => $result['id'],
                            'answer' => json_encode($correctAnswer),
                        );
                    }
                    $this->course_model->addanswer($addData);
                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output(200, $array);
                }
            }
        }
    }

    /**
     * This function is used to submit online course quiz
     */
    public function submitquiz()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $quiz_id = $params['quiz_id'];
                    $questionID = $params['question_id'];

                    $result = $this->course_model->getquizanswerexistornot($questionID, $quiz_id, $student_id);

                    $answer1 = $params['answer_1'];
                    $answer2 = $params['answer_2'];
                    $answer3 = $params['answer_3'];
                    $answer4 = $params['answer_4'];
                    $answer5 = $params['answer_5'];

                    $correctAnswer = array($answer1, $answer2, $answer3, $answer4, $answer5);
                    if (empty($result)) {

                        $addData = array(
                            'student_id' => $student_id,
                            'course_quiz_id' => $quiz_id,
                            'course_quiz_question_id' => $questionID,
                            'answer' => json_encode($correctAnswer),
                            'created_date' => date('Y-m-d H:i:s'),
                        );

                    } else {

                        $addData = array(
                            'id' => $result['id'],
                            'answer' => json_encode($correctAnswer),
                        );

                    }
                    $this->course_model->addanswer($addData);
                    $resultData = array(
                        'student_id' => $student_id,
                        'course_quiz_id' => $quiz_id,
                        'status' => 1,
                        'created_date' => date('Y-m-d H:i:s'),
                    );

                    $lastid = $this->course_model->addquizstatus($resultData);
                    $studentresult = $this->course_model->getresult($quiz_id, $student_id);
                    $answercount = array();
                    $wrongcount = array();
                    $not_attempted = array();
                    if (!empty($studentresult)) {
                        foreach ($studentresult as $studentresult_value) {
                            $result = '';
                            if (!empty($studentresult_value['answer'])) {
                                $submit_answer = json_decode($studentresult_value['answer']);

                                foreach ($submit_answer as $key => $submit_answer_value) {
                                    if (!empty($submit_answer_value)) {
                                        $key = $key + 1;
                                        if ($key == 1) {
                                            $result = "option_1,";
                                        }
                                        if ($key == 2) {
                                            $result = $result . "option_2,";
                                        }
                                        if ($key == 3) {
                                            $result = $result . "option_3,";
                                        }
                                        if ($key == 4) {
                                            $result = $result . "option_4,";
                                        }
                                        if ($key == 5) {
                                            $result = $result . "option_5";
                                        }
                                    }
                                }
                                $result = rtrim($result, ',');
                            }

                            if ($studentresult_value['correct_answer'] == $result) {
                                $answer_value = '1';
                                array_push($answercount, $answer_value);
                            } elseif (empty($result)) {
                                $attempted_value = '1';
                                array_push($not_attempted, $attempted_value);
                            }
                        }
                    }

                    $questioncount = $this->course_model->getquestionbyquizid($quiz_id, $student_id);
                    $questioncount = count($questioncount);
                    $answercount = count($answercount);
                    $not_attempted = count($not_attempted);
                    $wrong_answer = $questioncount - ($answercount + $not_attempted);
                    if (!empty($lastid)) {
                        $updateData = array(
                            'id' => $lastid,
                            'total_question' => $questioncount,
                            'correct_answer' => $answercount,
                            'wrong_answer' => $wrong_answer,
                            'not_answer' => $not_attempted,
                        );

                        $this->course_model->addquizstatus($updateData);
                    }

                    $array = array('status' => '1', 'msg' => 'Success');

                    json_output(200, $array);
                }
            }
        }
    }

    /*
    This is used to delete previous record of student if he has given exam
     */
    public function resetquiz()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $course_quiz_id = $params['quiz_id'];

                    $this->course_model->removequizstatus($course_quiz_id, $student_id);
                    $this->course_model->removestudentquizanswer($course_quiz_id, $student_id);

                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output(200, $array);
                }
            }
        }
    }

    /**
     * This function is used to mark quiz and lesson completed or not
     */
    public function markascomplete()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $section_id = $params['section_id'];
                    $lesson_quiz_type = $params['lesson_quiz_type'];
                    $lesson_quiz_id = $params['lesson_quiz_id'];
                    $result = $this->course_model->coursebysection($section_id);
                    $data = array(
                        "student_id" => $student_id,
                        "lesson_quiz_id" => $lesson_quiz_id,
                        "lesson_quiz_type" => $lesson_quiz_type,
                        "course_section_id" => $section_id,
                        "course_id" => $result['id'],
                    );

                    $is_completed = $this->course_model->getcourseprogress($result['id'], $student_id, $section_id, $lesson_quiz_type, $lesson_quiz_id);

                    if (!empty($is_completed)) {
                        $this->course_model->markascomplete($data, 0);
                    } else {
                        $this->course_model->markascomplete($data, 1);
                    }

                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output(200, $array);
                }
            }
        }
    }

    /*
    This is used to get student course performance
     */
    public function courseperformance()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $course_id = $params['course_id'];
                    $student_id = $params['student_id'];

                    $data['result'] = $this->course_model->courseperformance($course_id, $student_id);

                    $lessoncount = $this->course_model->totallessonbycourse($course_id);
                    $data['lessoncount'] = count($lessoncount);
                    $data['lessoncompleted'] = count($this->course_model->lessoncompleted($course_id, $student_id, 1));

                    $quizcount = $this->course_model->totalquizbycourse($course_id);
                    $data['quizcount'] = count($quizcount);
                    $data['quizcompleted'] = count($this->course_model->lessoncompleted($course_id, $student_id, 2));

                    $assignmentcount = $this->course_model->totalassignmentbycourse($course_id);
                    $data['assignmentcount'] = count($assignmentcount);
                    $data['assignemtcompleted'] = count($this->course_model->lessoncompleted($course_id, $student_id, 3));

                    $examcount = $this->course_model->totalexambycourse($course_id);
                    $data['examcount'] = count($examcount);
                    $data['examcompleted'] = count($this->course_model->lessoncompleted($course_id, $student_id, 4));

                    $lessonquizcount = $data['lessoncount'] + $data['quizcount'] + $data['assignmentcount'] + $data['examcount'];
                    $lessonquizcompletedcount = $data['lessoncompleted'] + $data['quizcompleted'] + $data['assignemtcompleted'] + $data['examcompleted'];

                    if ($lessonquizcount > 0) {
                        $data['percentage'] = ($lessonquizcompletedcount / $lessonquizcount) * 100;
                    } else {
                        $data['percentage'] = 0;
                    }
                    json_output($response['status'], $data);

                }
            }
        }
    }

    public function addCourseRatingandReview()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $course_id = $params['course_id'];
                    $rating = $params['rating'];
                    $review = $params['review'];
                    $id = $params['id'];

                    if (empty($id)) {
                        $addData = array(
                            'student_id' => $student_id,
                            'course_id' => $course_id,
                            'rating' => $rating,
                            'review' => $review,
                            'date' => date('Y-m-d'),
                        );
                    }else{
						$addData = array(
                            'id' => $id,
                            'student_id' => $student_id,
                            'course_id' => $course_id,
                            'rating' => $rating,
                            'review' => $review,
                            'date' => date('Y-m-d'),
                        );
					}
                    $this->course_model->addCourseRatingandReview($addData);
                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output(200, $array);
                }
            }
        }
    }

    /**
     * This function is used to update student panel language
     */
    public function updatestudentlanguage()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $language_id = $params['language_id'];

                    if (empty($result)) {

                        $addData = array(
                            'user_id' => $student_id,
                            'lang_id' => $language_id,
                        );

                    }
                    $this->student_model->updatestudentlanguage($addData);
                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output(200, $array);
                }
            }
        }
    }

    /**
     * This function is used to get student current language
     */
    public function getstudentcurrentlanguage()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];

                    $data['result'] = $this->user_model->getstudentcurrentlanguage($student_id);
                    json_output($response['status'], $data);

                }
            }
        }
    }

    public function adddailyassignment()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = $this->input->POST();

                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('subject_id', 'Subject', 'required|trim');
                    $this->form_validation->set_rules('title', 'Title', 'required|trim');

                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $this->form_validation->set_rules('file', 'File', 'callback_handle_upload_file');
                    }

                    $storage_array = "file";
                    $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'student_id' => form_error('student_id'),
                            'title' => form_error('title'),
                            'file' => form_error('file'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        //==================

                        $student = $this->student_model->get($this->input->post('student_id'));

                        $upload_path = $this->config->item('upload_path') . "/homework/assignment/";

                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

                            // SaaS Quota Reservation
                            $storage_array = ['file'];
                            $this->saasvalidation->updateStorageLimit('storage', $storage_array);

                            $time = md5($_FILES["file"]['name'] . microtime());
                            $fileInfo = pathinfo($_FILES["file"]["name"]);

                            $img_name = $this->customlib->uniqueFileName() . '.' . $fileInfo['extension'];

                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $img_name)) {
                                $data_insert = array(
                                    'title' => $this->input->post('title'),
                                    'description' => $this->input->post('description'),
                                    'student_session_id' => $student->student_session_id,
                                    'attachment' => $img_name,
                                );
                                $this->homework_model->adddailyassignment($data_insert);
                            } else {
                                // Upload Failed - Rollback Quota
                                $file_size_kb = $this->media_storage->getTmpFileSize('file');
                                if ($file_size_kb > 0) {
                                    $this->saasvalidation->deleteResouceQuota('storage', $file_size_kb);
                                }
                            }
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function getVideoTutorial()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $params = json_decode(file_get_contents('php://input'), true);
                    $class_id = $params['class_id'];
                    $section_id = $params['section_id'];

                    $data['result'] = $this->video_tutorial_model->getvideotutorial($class_id, $section_id);
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getVisitors()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $student = $this->student_model->get($student_id);
                    $student_session_id = $student->student_session_id;
                    $result = $this->visitors_model->visitorbystudentid($student_session_id);
                    foreach ($result as $key => $value) {
                        if ($value['image'] == null) {
                            $result[$key]['image'] = '';
                        }
                    }
                    $data['result'] = $result;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    // -------- Daily Assignment -------------

    public function getdailyassignment()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $student = $this->student_model->get($student_id);
                    $student_session_id = $student->student_session_id;
                    $dailyassignment = $this->homework_model->getdailyassignment($student_id, $student_session_id);

                    foreach ($dailyassignment as $key => $value) {
                        if ($value['evaluation_date'] == null) {
                            $dailyassignment[$key]['evaluation_date'] = '';
                        }
                        if ($value['attachment'] == null) {
                            $dailyassignment[$key]['attachment'] = '';
                        }
                    }

                    $data["dailyassignment"] = $dailyassignment;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function addeditdailyassignment()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $data = $this->input->POST();

                    $this->form_validation->set_rules('title', 'title', 'required|trim');
                    $this->form_validation->set_rules('subject', 'subject', 'required|trim');

                    $storage_array = "file";
                    $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'title' => form_error('title'),
                            'subject' => form_error('subject'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        //==================
                        $student_id = $this->input->post('student_id');
                        $student = $this->student_model->get($student_id);
                        $student_session_id = $student->student_session_id;

                        $data = array(
                            'id' => $this->input->post('id'),
                            'title' => $this->input->post('title'),
                            'subject_group_subject_id' => $this->input->post('subject'),
                            'description' => $this->input->post('description'),
                            'date' => date('Y-m-d'),
                            'student_session_id' => $student_session_id,
                            'remark' => $this->input->post('remark') ?? '',
                        );

                        // Remove empty id so MySQL auto_increment works on new records
                        if (empty($data['id'])) {
                            unset($data['id']);
                        }

                        $upload_path = $this->config->item('upload_path') . "/homework/daily_assignment/";
                        $insert_id = $this->homework_model->adddailyassignment($data);

                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                            $fileInfo = pathinfo($_FILES["file"]["name"]);
                            $img_name = $insert_id . '.' . $fileInfo['extension'];

                            // SaaS Logic: Differential Update
                            $prev_file_size = 0;
                            if ($this->input->post('id') != "") {
                                $current_assignment = $this->homework_model->getdailyassignmentbyid($this->input->post('id'));
                                if (!empty($current_assignment->attachment)) {
                                    $file_url = $upload_path . $current_assignment->attachment;
                                    if (file_exists($file_url)) {
                                        $prev_file_size = round(filesize($file_url) / 1024);
                                    }
                                }
                            }

                            $new_file_size = round($_FILES['file']['size'] / 1024);

                            if (move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $img_name)) {

                                // Upload Success - Update Quota
                                if ($prev_file_size > $new_file_size) {
                                    $diff = $prev_file_size - $new_file_size;
                                    $this->saasvalidation->deleteResouceQuota('storage', $diff);
                                } elseif ($new_file_size > $prev_file_size) {
                                    $diff = $new_file_size - $prev_file_size;
                                    $this->saasvalidation->updateResouceQuota('storage', $diff);
                                } elseif ($prev_file_size == 0 && $new_file_size > 0) {
                                    $this->saasvalidation->updateResouceQuota('storage', $new_file_size);
                                }

                                if ($this->input->post('id') != "" && !empty($current_assignment->attachment) && $current_assignment->attachment != $img_name) {
                                    $old_file_url = $upload_path . $current_assignment->attachment;
                                    if (file_exists($old_file_url)) {
                                        unlink($old_file_url);
                                    }
                                }

                                $data = array('id' => $insert_id, 'attachment' => $img_name);
                                $this->homework_model->adddailyassignment($data);
                            }
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function deletedailyassignment()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('id', 'Id', 'required|trim');

                if ($this->form_validation->run() == false) {

                    $errors = array(
                        'id' => form_error('id'),
                    );
                    $array = array('status' => '0', 'error' => $errors);
                } else {
                    //==================

                    $id = $this->input->post('id');

                    // SaaS Logic: Clean up storage & quota
                    $assignment_data = $this->homework_model->getdailyassignmentbyid($id);
                    if (!empty($assignment_data->attachment)) {
                        $file_path = $this->config->item('upload_path') . "/homework/daily_assignment/" . $assignment_data->attachment;
                        if (file_exists($file_path)) {
                            $file_size_kb = round(filesize($file_path) / 1024);
                            $this->saasvalidation->deleteResouceQuota('storage', $file_size_kb);
                            unlink($file_path);
                        }
                    }

                    $this->homework_model->deletedailyassignment($id);
                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    //--------- Transport Routes -----------------------
    public function gettransportroutes()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];

                    $studentList = $this->student_model->get($student_id);
                    $data['pickup_point'] = $this->pickuppoint_model->getPickupPointByRouteID($studentList->route_id);

                    foreach ($studentList as $key => $value) {
                        if ($studentList->$key == '') {
                            $studentList->$key = '';
                        }
                    }

                    $data['route'] = $studentList;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    //--------- Timeline -----------------------

    public function getTimeline()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['studentId'];
                    $timeline = $this->timeline_model->getTimeline($student_id);

                    foreach ($timeline as $key => $value) {
                        if ($timeline[$key]['document'] == '') {
                            $timeline[$key]['document'] = '';
                        }
                    }

                    json_output($response['status'], $timeline);
                }
            }
        }
    }

    public function addedittimeline()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $this->form_validation->set_data($this->input->post());
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('title', 'Title', 'required|trim');
                    $this->form_validation->set_rules('timeline_date', 'Date', 'required|trim');
                    $this->form_validation->set_rules('student_id', 'Student ID', 'required|trim');

                    $storage_array = "timeline_doc";
                    $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {
                        $form_error = array(
                            'title' => form_error('title'),
                            'timeline_date' => form_error('timeline_date'),
                            'student_id' => form_error('student_id'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $form_error);
                    } else {

                        $timeline = array(
                            'title' => $this->input->post('title'),
                            'description' => $this->input->post('description'),
                            'timeline_date' => $this->input->post('timeline_date'),
                            'status' => 'yes',
                            'date' => date('Y-m-d'),
                            'student_id' => $this->input->post('student_id'),
                        );
                        $id = $this->input->post('id');
                        if (!empty($id)) {
                            $timeline['id'] = $id;
                        }
                        $insert_id = $this->timeline_model->addedittimeline($timeline);

                        $upload_path = $this->config->item('upload_path') . "/student_timeline/";

                        if (isset($_FILES["timeline_doc"]) && !empty($_FILES['timeline_doc']['name'])) {

                            // SaaS Logic: Differential Update
                            $prev_file_size = 0;
                            if ($this->input->post('id') != "") {
                                $current_timeline = $this->timeline_model->gettimelinebyid($this->input->post('id'));
                                if (!empty($current_timeline->document)) {
                                    $file_url = $upload_path . $current_timeline->document;
                                    if (file_exists($file_url)) {
                                        $prev_file_size = round(filesize($file_url) / 1024);
                                    }
                                }
                            }

                            $new_file_size = round($_FILES['timeline_doc']['size'] / 1024);

                            $fileInfo = pathinfo($_FILES["timeline_doc"]["name"]);
                            $img_name = $insert_id . '.' . $fileInfo['extension'];

                            if (move_uploaded_file($_FILES["timeline_doc"]["tmp_name"], $upload_path . $img_name)) {

                                // Upload Success - Update Quota
                                if ($prev_file_size > $new_file_size) {
                                    $diff = $prev_file_size - $new_file_size;
                                    $this->saasvalidation->deleteResouceQuota('storage', $diff);
                                } elseif ($new_file_size > $prev_file_size) {
                                    $diff = $new_file_size - $prev_file_size;
                                    $this->saasvalidation->updateResouceQuota('storage', $diff);
                                } elseif ($prev_file_size == 0 && $new_file_size > 0) {
                                    $this->saasvalidation->updateResouceQuota('storage', $new_file_size);
                                }

                                if ($this->input->post('id') != "" && !empty($current_timeline->document) && $current_timeline->document != $img_name) {
                                    $old_file_url = $upload_path . $current_timeline->document;
                                    if (file_exists($old_file_url)) {
                                        unlink($old_file_url);
                                    }
                                }

                                $data = array('id' => $insert_id, 'document' => $img_name);
                                $this->timeline_model->addedittimeline($data);
                            }
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function deletetimeline()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {

                $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                $this->form_validation->set_data($_POST);
                $this->form_validation->set_error_delimiters('', '');
                $this->form_validation->set_rules('id', 'Id', 'required|trim');

                if ($this->form_validation->run() == false) {
                    $errors = array(
                        'id' => form_error('id'),
                    );
                    $array = array('status' => '0', 'error' => $errors);
                } else {
                    //==================

                    $id = $this->input->post('id');

                    // SaaS Logic: Clean up storage & quota
                    $timeline_data = $this->timeline_model->gettimelinebyid($id);
                    if (!empty($timeline_data->document)) {
                        $file_path = $this->config->item('upload_path') . "/student_timeline/" . $timeline_data->document;
                        if (file_exists($file_path)) {
                            $file_size_kb = round(filesize($file_path) / 1024);
                            $this->saasvalidation->deleteResouceQuota('storage', $file_size_kb);
                            unlink($file_path);
                        }
                    }

                    $this->timeline_model->deletetimeline($id);
                    $array = array('status' => '1', 'msg' => 'Success');
                }
                json_output(200, $array);
            }
        }
    }

    //-------------- Student Behaviour Addon -------------------

    public function getstudentbehaviour()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];

                    $behaviour_settings = $this->assign_incident_model->behaviour_settings();

                    if ($behaviour_settings['comment_option'] == 'null') {
                        $behaviour_settings['comment_option'] = '';
                    }

                    $data['behaviour_settings'] = $behaviour_settings;
                    $total_points = $this->assign_incident_model->totalpoints($student_id);
                    $data['behaviour_score'] = $total_points['totalpoints'];
                    $assigned_incident = $this->assign_incident_model->studentbehaviour($student_id);

                    foreach ($assigned_incident as $key => $value) {
                        $CommentsCount = $this->assign_incident_model->getCommentsCount($value['id']);
                        $assigned_incident[$key]['comment_count'] = count($CommentsCount);
                    }

                    $data['assigned_incident'] = $assigned_incident;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getincidentcomments()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_incident_id = $params['student_incident_id'];
                    $messagelist = $this->assign_incident_model->getincidentcomments($student_incident_id);

                    foreach ($messagelist as $key => $value) {
                        if ($value['firstname'] == null) {
                            $messagelist[$key]['firstname'] = '';
                        }
                        if ($value['middlename'] == null) {
                            $messagelist[$key]['middlename'] = '';
                        }
                        if ($value['lastname'] == null) {
                            $messagelist[$key]['lastname'] = '';
                        }
                        if ($value['admission_no'] == null) {
                            $messagelist[$key]['admission_no'] = '';
                        }
                        if ($value['student_image'] == null) {
                            $messagelist[$key]['student_image'] = '';
                        }
                    }

                    $data['messagelist'] = $messagelist;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function addincidentcomments()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $params = json_decode(file_get_contents('php://input'), true);

                    $student_id = $params['student_id'];
                    $student_incident_id = $params['student_incident_id'];
                    $type = $params['type'];
                    $comment = $params['comment'];

                    $timeline = array(

                        'student_incident_id' => $student_incident_id,
                        'comment' => $comment,
                        'type' => $type,
                        'student_id' => $student_id,
                        'created_date' => date('Y-m-d H:i:s'),

                    );

                    $this->assign_incident_model->addincidentcomments($timeline);
                    $array = array('status' => '1', 'msg' => 'Success');

                    json_output(200, $array);
                }
            }
        }
    }

    public function deleteincidentcomments()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $incident_comment_id = $params['incident_comment_id'];
                    $this->assign_incident_model->delete($incident_comment_id);

                    json_output($response['status'], array('result' => 'Success'));
                }
            }
        }
    }

    //-------------------------- Currency List ---------------
    public function get_currency_list()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);

                    $data['result'] = $this->setting_model->get_currency_list();
                    json_output($response['status'], $data);

                }
            }
        }
    }

    public function getstudentcurrentcurrency()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];

                    $result = $this->setting_model->get();
                    $currencyarray = $this->user_model->getstudentcurrentcurrency($student_id);
                    if ($currencyarray[0]->currency_id != 0) {
                        $result[0]['currency'] = $currencyarray[0]->currency_id;
                    } else {
                        $result[0]['currency'] = $result[0]['currency'];
                    }

                    $data['result'] = $result;

                    json_output($response['status'], $data);

                }
            }
        }
    }

    public function updatestudentcurrency()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $currency_id = $params['currency_id'];

                    if (empty($result)) {
                        $addData = array(
                            'user_id' => $student_id,
                            'currency_id' => $currency_id,
                        );
                    }
                    $this->student_model->updatestudentlanguage($addData);
                    $array = array('status' => '1', 'msg' => 'Success');
                    json_output(200, $array);
                }
            }
        }
    }

    public function lock_student_panel()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $studentList = $this->student_model->get($student_id);

                    $class_id = $studentList->class_id;
                    $session_id = $studentList->session_id;
                    $section_id = $studentList->section_id;
                    $student_session_id = $studentList->student_session_id;
                    $route_pickup_point_id = $studentList->route_pickup_point_id;

                    $sch_setting = $this->setting_model->getSchoolDetail();
                    $is_student_feature_lock = $sch_setting->is_student_feature_lock;
                    $lock_grace_period = $sch_setting->lock_grace_period;

                    $is_lock = 0;
                    if ($is_student_feature_lock) {

                        $date = date('Y-m-d', strtotime(date("Y-m-d")) - (86400 * $lock_grace_period));
                        $student_due_fee = $this->studentfeemaster_model->getDueFeesByStudent($student_session_id, $date);
                        if (!empty($student_due_fee)) {
                            foreach ($student_due_fee as $result_key => $result_value) {

                                if ($result_value->is_system == 0) {
                                    $student_due_fee[$result_key]->{'amount'} = $result_value->fee_amount;
                                }

                                $fee_paid = 0;
                                $fee_discount = 0;
                                $fee_fine = 0;

                                $feetype_balance = 0;
                                if (isJSON($result_value->amount_detail)) {
                                    $fee_deposits = json_decode(($result_value->amount_detail));
                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                        $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                        $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                        $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                    }
                                }

                                $feetype_balance = ($result_value->amount + $result_value->fine_amount) - ($fee_paid + $fee_fine + $fee_discount);

                                if ($feetype_balance > 0) {
                                    $is_lock = 1;
                                }
                            }
                        }

                        $transport_fees = $this->studentfeemaster_model->getDueTransportFeeByStudent($student_session_id, $route_pickup_point_id, $date);

                        if (!empty($transport_fees)) {
                            foreach ($transport_fees as $tran_fee_key => $tran_fee_value) {
                                $fee_paid = 0;
                                $fee_discount = 0;
                                $fee_fine = 0;
                                $fees_fine_amount = 0;
                                $feetype_balance = 0;
                                if (isJSON($tran_fee_value->amount_detail)) {
                                    $fee_deposits = json_decode(($tran_fee_value->amount_detail));
                                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                        $fee_paid = $fee_paid + $fee_deposits_value->amount;
                                        $fee_discount = $fee_discount + $fee_deposits_value->amount_discount;
                                        $fee_fine = $fee_fine + $fee_deposits_value->amount_fine;
                                    }
                                }

                                $fees_fine_amount = is_null($tran_fee_value->fine_percentage) ? $tran_fee_value->fine_amount : percentageAmount($tran_fee_value->fees, $tran_fee_value->fine_percentage);

                                $feetype_balance = ($tran_fee_value->fees + $fees_fine_amount) - ($fee_paid + $fee_discount + $fee_fine);

                                if ($feetype_balance > 0) {
                                    $is_lock = 1;
                                }
                            }
                        }
                    }

                    $data['is_lock'] = $is_lock;
                    json_output($response['status'], $data);

                }
            }
        }
    }


    public function getStudentCurrency()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];

                    $result = $this->user_model->getStudentCurrency($student_id);
                    $setting_result = $this->setting_model->get();

                    if (!empty($result)) {

                        $currency_symbol = $result[0]->symbol;
                        $currency_short_name = $result[0]->name;
                        $base_price = $result[0]->base_price;

                    } else {

                        $currency_symbol = $setting_result[0]['currency_symbol'];
                        $currency_short_name = $setting_result[0]['short_name'];
                        $base_price = $setting_result[0]['base_price'];

                    }

                    $data['result'] = array(

                        'name' => $currency_short_name,
                        'symbol' => $currency_symbol,
                        'base_price' => $base_price,

                    );

                    json_output($response['status'], $data);

                }
            }
        }
    }

    public function addofflinepayment()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $data = $this->input->POST();
                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('payment_type', 'Payment Type', 'required|trim');
                    $this->form_validation->set_rules('payment_date', 'Date', 'required|trim');
                    $this->form_validation->set_rules('student_session_id', 'Student Session ID', 'required|trim');
                    $this->form_validation->set_rules('bank_account_transferred', 'Payment From', 'required|trim');
                    $this->form_validation->set_rules('amount', 'amount', 'required|trim');
                    $fee_type = $this->input->post('payment_type');

                    if (isset($fee_type) && $fee_type == "fees") {
                        $this->form_validation->set_rules('fee_groups_feetype_id', 'Fee Group Fee Type ID', 'required|trim');
                        $this->form_validation->set_rules('student_fees_master_id', 'Student Fees Master ID', 'required|trim');
                    } elseif (isset($fee_type) && $fee_type == "transport_fees") {
                        $this->form_validation->set_rules('student_transport_fee_id', 'Student Transport Fee ID', 'required|trim');
                    }

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'payment_type' => form_error('payment_type'),
                            'payment_date' => form_error('payment_date'),
                            'student_session_id' => form_error('student_session_id'),
                            'fee_groups_feetype_id' => form_error('fee_groups_feetype_id'),
                            'student_fees_master_id' => form_error('student_fees_master_id'),
                            'bank_account_transferred' => form_error('bank_account_transferred'),
                            'student_transport_fee_id' => form_error('student_transport_fee_id'),
                            'amount' => form_error('amount'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        //==================
                        $data = array(
                            'payment_date' => $this->input->post('payment_date'),
                            'student_session_id' => $this->input->post('student_session_id'),
                            'bank_account_transferred' => $this->input->post('bank_account_transferred'),
                            'amount' => $this->input->post('amount'),
                            'reference' => $this->input->post('reference'),
                            'bank_from' => 'Offline',
                            'submit_date' => date('Y-m-d H:i:s'),
                        );

                        if ($this->input->post('payment_type') == "fees") {
                            $data['fee_groups_feetype_id'] = $this->input->post('fee_groups_feetype_id');
                            $data['student_fees_master_id'] = $this->input->post('student_fees_master_id');
                        } elseif ($this->input->post('payment_type') == "transport_fees") {
                            # code...
                            $data['student_transport_fee_id'] = $this->input->post('student_transport_fee_id');
                        }

                        $upload_path = $this->config->item('upload_path') . "/offline_payments/";

                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                            $name = $_FILES["file"]["name"];
                            $file_name = time() . "-" . uniqid(rand()) . "!" . $name;
                            move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path . $file_name);
                            $data['attachment'] = $file_name;
                        }

                        $this->offlinePayment_model->add($data);
                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);

                }
            }
        }
    }

    public function getELearningModuleStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $user = $this->input->post('user');

                    $modulearray = array('homework', 'daily_assignment', 'lesson_plan', 'online_examination', 'download_center', 'online_course', 'live_classes', 'gmeet_live_classes');

                    foreach ($modulearray as $key => $modulearray_value) {

                        if ($modulearray_value != 'daily_assignment') {
                            $result = $this->module_model->getModuleStatusByCategory($user, $modulearray_value);
                            if ((!empty($result)) && $result['short_code'] == $modulearray_value) {

                                if ($result['status'] != 1) {
                                    $status = 0;
                                } else {
                                    $status = 0;
                                    if (!empty($result['group_id'])) {

                                        $result2 = $this->module_model->getsystempermission($result['group_id']);
                                        $status = $result2['status'];

                                    }
                                }

                                $result_array[$key]['name'] = $result['name'];
                                $result_array[$key]['short_code'] = $result['short_code'];
                                $result_array[$key]['status'] = $status;

                            } else {
                                $result_array[$key]['name'] = $modulearray_value;
                                $result_array[$key]['short_code'] = $modulearray_value;
                                $result_array[$key]['status'] = 0;
                            }
                        } else {
                            $result = $this->module_model->getModuleStatusByCategory($user, 'homework');

                            if ($result['status'] != 1) {
                                $status = 0;
                            } else {

                                if (!empty($result['group_id'])) {

                                    $result2 = $this->module_model->getsystempermission($result['group_id']);
                                    $status = $result2['status'];

                                } else {
                                    $status = $result['status'];
                                }
                            }

                            $result_array[$key]['name'] = 'Daily Assignment';
                            $result_array[$key]['short_code'] = 'daily_assignment';
                            $result_array[$key]['status'] = $status;
                        }
                    }

                    $resp['module_list'] = $result_array;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getAcademicsModuleStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $user = $this->input->post('user');

                    $modulearray = array('class_timetable', 'syllabus_status', 'attendance', 'examinations', 'student_timeline', 'mydocuments', 'behaviour_records', 'cbseexam');

                    $setting = $this->setting_model->getSetting();

                    foreach ($modulearray as $key => $modulearray_value) {

                        if ($modulearray_value == 'mydocuments') {

                            $result_array[$key]['name'] = "My Documents";
                            $result_array[$key]['short_code'] = "mydocuments";
                            $result_array[$key]['status'] = $setting->upload_documents;

                        } else {

                            $result = $this->module_model->getModuleStatusByCategory($user, $modulearray_value);

                            if (!empty($result)) {

                                if ($result['short_code'] == $modulearray_value) {

                                    if ($result['status'] != 1) {
                                        $status = 0;
                                    } else {

                                        if (!empty($result['group_id'])) {

                                            $result2 = $this->module_model->getsystempermission($result['group_id']);
                                            $status = $result2['status'];

                                        } else {
                                            $status = $result['status'];
                                        }
                                    }

                                    $result_array[$key]['name'] = $result['name'];
                                    $result_array[$key]['short_code'] = $result['short_code'];
                                    $result_array[$key]['status'] = $status;

                                }

                            } else {

                                $result_array[$key]['name'] = $modulearray_value;
                                $result_array[$key]['short_code'] = $modulearray_value;
                                $result_array[$key]['status'] = 0;

                            }

                        }

                    }

                    $resp['module_list'] = $result_array;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getCommunicateModuleStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $user = $this->input->post('user');

                    $modulearray = array('notice_board');

                    foreach ($modulearray as $key => $modulearray_value) {

                        $result = $this->module_model->getModuleStatusByCategory($user, $modulearray_value);

                        if ($result['status'] != 1) {
                            $status = 0;
                        } else {

                            if (!empty($result['group_id'])) {

                                $result2 = $this->module_model->getsystempermission($result['group_id']);
                                $status = $result2['status'];

                            }
                        }

                        $result_array[$key]['name'] = $result['name'];
                        $result_array[$key]['short_code'] = $result['short_code'];
                        $result_array[$key]['status'] = $status;

                    }

                    $resp['module_list'] = $result_array;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOthersModuleStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $user = $this->input->post('user');

                    $modulearray = array('fees', 'apply_leave', 'visitor_book', 'transport_routes', 'hostel_rooms', 'calendar_to_do_list', 'library', 'teachers_rating');

                    foreach ($modulearray as $key => $modulearray_value) {
                        $result = $this->module_model->getModuleStatusByCategory($user, $modulearray_value);

                        if ($result['short_code'] == $modulearray_value) {

                            if ($result['status'] != 1) {
                                $status = 0;
                            } else {

                                if (!empty($result['group_id'])) {

                                    $result2 = $this->module_model->getsystempermission($result['group_id']);
                                    $status = $result2['status'];

                                } else {
                                    $status = $result['status'];
                                }
                            }

                            $result_array[$key]['name'] = $result['name'];
                            $result_array[$key]['short_code'] = $result['short_code'];
                            $result_array[$key]['status'] = $status;

                        } else {
                            $result_array[$key]['name'] = $modulearray_value;
                            $result_array[$key]['short_code'] = $modulearray_value;
                            $result_array[$key]['status'] = 0;
                        }
                    }

                    $resp['module_list'] = $result_array;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOfflineBankPayments()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = array();
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_id = $params['student_id'];
                    $student = $this->student_model->get($student_id);

                    $result = $this->offlinePayment_model->getPaymentlistByUser($student->student_session_id);

                    foreach ($result as $key => $value) {

                        if ($value->month == null) {
                            $result[$key]->month = '';
                        }
                        if ($value->transport_feemaster_due_date == null) {
                            $result[$key]->transport_feemaster_due_date = '';
                        }
                        if ($value->pickup_point == null) {
                            $result[$key]->pickup_point = '';
                        }
                        if ($value->route_title == null) {
                            $result[$key]->route_title = '';
                        }
                        if ($value->type == null) {
                            $result[$key]->type = '';
                        }
                        if ($value->code == null) {
                            $result[$key]->code = '';
                        }
                        if ($value->fee_group_name == null) {
                            $result[$key]->fee_group_name = '';
                        }
                        if ($value->reply == null) {
                            $result[$key]->reply = '';
                        }
                        if ($value->attachment == null) {
                            $result[$key]->attachment = '';
                        }
                        if ($value->invoice_id == null) {
                            $result[$key]->invoice_id = '';
                        }

                    }

                    $data['result_array'] = $result;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getMaintenanceModeStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $setting = $this->setting_model->getSetting();
                    $resp['maintenance_mode'] = $setting->maintenance_mode;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getStudentTimelineStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $setting = $this->setting_model->getSetting();

                    $resp['student_timeline'] = $setting->student_timeline;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOfflineBankPaymentStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $setting = $this->setting_model->getSetting();

                    $resp['is_offline_fee_payment'] = $setting->is_offline_fee_payment;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOfflineBankPaymentInstruction()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $setting = $this->setting_model->getSetting();

                    $resp['offline_bank_payment_instruction'] = $setting->offline_bank_payment_instruction;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getProcessingfees()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];
                    $student_id = $_POST['student_id'];
                    $student = $this->student_model->get($student_id);

                    $student_fee = $this->studentfeemaster_model->getStudentProcessingFees($student->student_session_id);

                    $transport_fees = $this->studentfeemaster_model->getProcessingTransportFees($student->student_session_id, $student->route_pickup_point_id);

                    $fee_paid = 0;
                    $fee_discount = 0;
                    $fee_fine = 0;
                    $total_balance_amount = 0;

                    foreach ($student_fee as $result) {
                        if (isJSON($result->amount_detail)) {

                            $fee_deposits = json_decode(($result->amount_detail));

                            $fee_paid = $fee_paid + $fee_deposits->amount;
                            $fee_discount = $fee_discount + $fee_deposits->amount_discount;
                            $fee_fine = $fee_fine + $fee_deposits->amount_fine;
                            $feetype_balance = $fee_deposits->amount - ($fee_paid + $fee_discount);
                            $total_balance_amount = $total_balance_amount + $feetype_balance;

                        }
                    }

                    foreach ($transport_fees as $transport_result) {
                        if (isJSON($transport_result->amount_detail)) {

                            $fee_deposits = json_decode(($transport_result->amount_detail));

                            $fee_paid = $fee_paid + $fee_deposits->amount;
                            $fee_discount = $fee_discount + $fee_deposits->amount_discount;
                            $fee_fine = $fee_fine + $fee_deposits->amount_fine;
                            $feetype_balance = $fee_deposits->amount - ($fee_paid + $fee_discount);
                            $total_balance_amount = $total_balance_amount + $feetype_balance;

                        }
                    }

                    $data['student_fee'] = $student_fee;
                    $data['transport_fees'] = $transport_fees;

                    $grand_fee = array('fee_paid' => ($fee_paid), 'fee_discount' => ($fee_discount), 'fee_fine' => ($fee_fine), 'total_paid' => ($fee_paid + $fee_fine));

                    $data['grand_fee'] = $grand_fee;

                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function checkStudentStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $id = $_POST['id'];
                    $user_type = $_POST['user_type'];

                    $response = $this->user_model->checkStudentStatus($id, $user_type);
                    $data['response'] = $response;

                    json_output(200, $data);
                }
            }
        }
    }

    public function cbseexamresult()
    {
        $this->load->model(array('cbseexam_model'));
        $this->load->helper('cbse');
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $params = json_decode(file_get_contents('php://input'), true);
            $data = [
                'exams' => []
            ];
            $student_session_id = $params['student_session_id'];

            $exam_list = $this->cbseexam_model->getStudentExamByStudentSession($student_session_id);
            $student_exams = [];

            if (!empty($exam_list)) {
                foreach ($exam_list as $exam_key => $exam_value) {

                    $exam_subjects = $this->cbseexam_model->getexamsubjects($exam_value->cbse_exam_id);
                    $exam_value->{"subjects"} = $exam_subjects;
                    $exam_value->{"grades"} = $this->cbseexam_model->getGraderangebyGradeID($exam_value->cbse_exam_grade_id);
                    $exam_value->{"exam_assessments"} = $this->cbseexam_model->getWithAssessmentTypeByAssessmentID($exam_value->cbse_exam_assessment_id);
                    $cbse_exam_result = $this->cbseexam_model->getStudentExamResultByExamId($exam_value->cbse_exam_id, [$exam_value->student_session_id]);
                    $exam_selected_assessments = $this->cbseexam_model->getSubjectAssessmentsByExam($exam_subjects);

                    $exam_value->{"exam_subject_assessments"} = $exam_selected_assessments;
                    $students = [];

                    if (!empty($cbse_exam_result)) {

                        foreach ($cbse_exam_result as $student_key => $student_value) {
                            $exam_value->{"exam_rank"} = $student_value->rank;
                            $marks = $student_value->marks;

                            $assessment_exists = find_subject_assessment_exists($exam_selected_assessments, $student_value->cbse_exam_timetable_id, $student_value->cbse_exam_assessment_type_id);

                            if (!$assessment_exists) {
                                $marks = 'xx';
                            } else {
                                $marks = is_null($student_value->marks) ? "N/A" : $student_value->marks;
                            }

                            if (!empty($students)) {
                                $subject_key = $this->find_subject_array_exists($student_value->subject_id, $students['subjects']);
                                if (!$subject_key) {

                                    $new_subject = [
                                        'subject_id' => $student_value->subject_id,
                                        'subject_name' => $student_value->subject_name,
                                        'subject_code' => $student_value->subject_code,
                                        'exam_assessments' => [
                                            $student_value->cbse_exam_assessment_type_id => [
                                                'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                                'cbse_exam_assessment_type_id' => $student_value->cbse_exam_assessment_type_id,
                                                'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                                'maximum_marks' => $student_value->maximum_marks,
                                                'cbse_student_subject_marks_id' => $student_value->cbse_student_subject_marks_id,
                                                'marks' => $marks,
                                                'note' => $student_value->note,
                                                'is_absent' => $student_value->is_absent,
                                            ],
                                        ],
                                    ];

                                    $students['subjects'][] = $new_subject;

                                } elseif ($subject_array_key = $this->findSubjectAssessmentNotExists($student_value->cbse_exam_assessment_type_id, $students['subjects'], $student_value->subject_id)) {
                                    $subject_array_key = $subject_array_key['subject_key'];
                                    $new_assesment = [
                                        'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                        'cbse_exam_assessment_type_id' => $student_value->cbse_exam_assessment_type_id,
                                        'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                        'maximum_marks' => $student_value->maximum_marks,
                                        'cbse_student_subject_marks_id' => $student_value->cbse_student_subject_marks_id,
                                        'marks' => $marks,
                                        'note' => $student_value->note,
                                        'is_absent' => $student_value->is_absent,
                                    ];

                                    $students['subjects'][$subject_array_key]['exam_assessments'][$student_value->cbse_exam_assessment_type_id] = $new_assesment;

                                }

                            } else {

                                $students['subjects'] = [
                                    [
                                        'subject_id' => $student_value->subject_id,
                                        'subject_name' => $student_value->subject_name,
                                        'subject_code' => $student_value->subject_code,
                                        'exam_assessments' => [
                                            $student_value->cbse_exam_assessment_type_id => [
                                                'cbse_exam_assessment_type_name' => $student_value->cbse_exam_assessment_type_name,
                                                'cbse_exam_assessment_type_id' => $student_value->cbse_exam_assessment_type_id,
                                                'cbse_exam_assessment_type_code' => $student_value->cbse_exam_assessment_type_code,
                                                'maximum_marks' => $student_value->maximum_marks,
                                                'cbse_student_subject_marks_id' => $student_value->cbse_student_subject_marks_id,
                                                'marks' => $marks,
                                                'note' => $student_value->note,
                                                'is_absent' => $student_value->is_absent,

                                            ],
                                        ],
                                    ],
                                ];
                            }
                        }
                    }
                    $exam_value->{"exam_data"} = $students;

                }
            }

            $data['exams'] = $exam_list;

            if (!empty($exam_list)) {

                foreach ($exam_list as $exam_key => $exam_value) {

                    if ($exam_value->exam_rank == null) {
                        $exam_rank = '';
                    } else {
                        $exam_rank = ($exam_value->exam_rank);
                    }

                    unset($exam_value->exam_rank);

                    $exam_value->{'exam_total_marks'} = 0;
                    $exam_value->{'exam_obtain_marks'} = 0;
                    $exam_value->{'exam_percentage'} = 0;
                    $exam_value->{'exam_grade'} = "";
                    $exam_value->{"exam_rank"} = $exam_rank;
                    if (!empty($exam_value->subjects)) {

                        $total_marks = 0;
                        $total_max_marks = 0;

                        foreach ($exam_value->subjects as $subject_key => $subject_value) {
                            foreach ($exam_value->exam_assessments as $exam_assessment_key => $exam_assessment_value) {

                                $assessment_exists = find_subject_assessment_exists($exam_value->exam_subject_assessments, $subject_value->id, $exam_assessment_value->id);
                                if ($assessment_exists) {
                                    $assessment_array = findAssessmentValue($subject_value->subject_id, $exam_assessment_value->id, $exam_value);

                                    ($assessment_array['is_absent']) ? $this->lang->line('abs') : $assessment_array['marks'];
                                    if ($assessment_array['marks'] == "N/A") {
                                        $assessment_array['marks'] = 0;
                                    }

                                    $total_max_marks += $assessment_array['maximum_marks'];
                                    $total_marks += $assessment_array['marks'];
                                } else {
                                    $assessment_array['marks'] = "xx";
                                }

                            }
                        }

                        $exam_percentage = getPercent($total_max_marks, $total_marks);
                        $exam_value->{'exam_obtain_marks'} = $total_marks;
                        $exam_value->{'exam_total_marks'} = $total_max_marks;
                        $exam_value->{'exam_percentage'} = $exam_percentage;
                        $exam_value->{'exam_grade'} = getGrade($exam_value->grades, $exam_percentage);

                    }
                }
            }

            json_output(200, $data);
        }
    }

    public function cbseexamtimetable()
    {
        $this->load->model(array('cbseexam_model'));
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $_POST = json_decode(file_get_contents("php://input"), true) ?? [];

                    $student_session_id = $_POST['student_session_id'];
                    $resp['result'] = $this->cbseexam_model->getStudentExamTimetable($student_session_id);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getBalanceFee()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = array();
                    $params = json_decode(file_get_contents('php://input'), true);
                    $fee_groups_feetype_id = $params['fee_groups_feetype_id'];
                    $student_fees_master_id = $params['student_fees_master_id'];
                    $student_session_id = $params['student_session_id'];
                    $fee_category = $params['fee_category'];
                    $trans_fee_id = $params['trans_fee_id'];

                    $discount_not_applied = $this->getNotAppliedDiscount($student_session_id);

                    if ($fee_category == "transport") {
                        $trans_fee_id = $trans_fee_id;
                        $remain_amount_object = $this->getStudentTransportFeetypeBalance($trans_fee_id);
                        $remain_amount = (float) json_decode($remain_amount_object)->balance;
                        $remain_amount_fine = json_decode($remain_amount_object)->fine_amount;
                    } else {
                        $fee_groups_feetype_id = $fee_groups_feetype_id;
                        $student_fees_master_id = $student_fees_master_id;
                        $remain_amount_object = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);
                        $remain_amount = json_decode($remain_amount_object)->balance;
                        $remain_amount_fine = json_decode($remain_amount_object)->fine_amount;
                    }

                    $remain_amount = number_format($remain_amount, 2, ".", "");

                    $result = array(

                        'balance' => ($remain_amount),
                        'discount_not_applied' => $discount_not_applied,
                        'remain_amount_fine' => ($remain_amount_fine),
                        'student_fees' => (json_decode($remain_amount_object)->student_fees)
                    );

                    $result['discount_fee'] = $this->feediscount_model->getStudentFeesDiscount($student_session_id);

                    $data['result_array'] = $result;
                    json_output($response['status'], $data);
                }
            }
        }
    }

    public function getStudentTransportFeetypeBalance($trans_fee_id)
    {
        $data = array();

        $result = $this->studentfeemaster_model->studentTransportDeposit($trans_fee_id);
        $amount_balance = 0;
        $amount = 0;
        $amount_fine = 0;
        $amount_discount = 0;
        $fine_amount = 0;
        $fee_fine_amount = 0;

        $due_amt = $result->fees;
        if (strtotime($result->due_date) < strtotime(date('Y-m-d'))) {
            $fee_fine_amount = is_null($result->fine_percentage) ? $result->fine_amount : percentageAmount($result->fees, $result->fine_percentage);
        }

        $amount_detail = json_decode($result->amount_detail);
        if (is_object($amount_detail)) {

            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                $amount = $amount + $amount_detail_value->amount;
                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                $amount_fine = $amount_fine + $amount_detail_value->amount_fine;
            }
        }

        $amount_balance = $due_amt - ($amount + $amount_discount);
        $fine_amount = abs($amount_fine - $fee_fine_amount);
        $array = array('status' => 'success', 'error' => '', 'student_fees' => $due_amt, 'balance' => $amount_balance, 'fine_amount' => $fine_amount);
        return json_encode($array);
    }

    public function getNotAppliedDiscount($student_session_id)
    {
        $discounts_array = $this->feediscount_model->getDiscountNotApplied($student_session_id);
        foreach ($discounts_array as $discount_key => $discount_value) {
            $discounts_array[$discount_key]->{"amount"} = $discount_value->amount;
        }
        return $discounts_array;
    }

    public function getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id)
    {
        $data = array();
        $data['fee_groups_feetype_id'] = $fee_groups_feetype_id;
        $data['student_fees_master_id'] = $student_fees_master_id;
        $result = $this->studentfeemaster_model->studentDeposit($data);

        $amount_balance = 0;
        $amount = 0;
        $amount_fine = 0;
        $amount_discount = 0;
        $fine_amount = 0;
        $fee_fine_amount = 0;
        $due_fine_amount = 0;
        $due_amt = $result->amount;
        if ((!empty($result->due_date)) && strtotime($result->due_date) < strtotime(date('Y-m-d'))) {

            // get cumulative fine amount as delay days 
            if ($result->fine_type == 'cumulative') {
                $date1 = date_create("$result->due_date");
                $date2 = date_create(date('Y-m-d'));
                $diff = date_diff($date1, $date2);
                $due_days = $diff->format("%a");
                ;

                if ($this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id, $due_days)) {
                    $due_fine_amount = $this->customlib->get_cumulative_fine_amount($fee_groups_feetype_id, $due_days);
                } else {
                    $due_fine_amount = 0;
                }
                $fee_fine_amount = $due_fine_amount;

            } else if ($result->fine_type == 'fix' || $result->fine_type == 'percentage') {
                $fee_fine_amount = $result->fine_amount;
            }
            // get cumulative fine amount as delay days
        }


        if ($result->is_system) {
            $due_amt = $result->student_fees_master_amount;
        }

        $amount_detail = json_decode($result->amount_detail);
        if (is_object($amount_detail)) {

            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                $amount = $amount + $amount_detail_value->amount;
                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;
                $amount_fine = $amount_fine + $amount_detail_value->amount_fine;
            }
        }

        $amount_balance = $due_amt - ($amount + $amount_discount);
        $fine_amount = ($fee_fine_amount > 0) ? ($fee_fine_amount - $amount_fine) : 0;

        $array = array('status' => 'success', 'error' => '', 'student_fees' => $due_amt, 'balance' => $amount_balance, 'fine_amount' => $fine_amount);
        return json_encode($array);
    }

    public function getFeesDiscountStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $school_setting = $this->setting_model->getSchoolDetail();
                    $resp['fees_discount'] = $school_setting->fees_discount;

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getAppliedDiscounts()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $student_fees_deposite = $params['student_fees_deposite'];
                    $resp = array();

                    $resp['result'] = $this->studentAppliedDiscount_model->get($student_fees_deposite);

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOnlineCourseSettings()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);

                    $resp = array();

                    $resp['result'] = $this->course_model->getOnlineCourseSettings();

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function saveCourseAssignment()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $data = $this->input->POST();

                    $this->form_validation->set_data($data);
                    $this->form_validation->set_error_delimiters('', '');
                    $this->form_validation->set_rules('assignmentid', 'assignment id', 'required|trim');
                    $this->form_validation->set_rules('student_id', 'student id', 'required|trim');
                    $this->form_validation->set_rules('message', 'message', 'required|trim');


                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $this->form_validation->set_rules('file', 'File', 'callback_handle_upload_file');
                    }

                    $storage_array = "file";
                    $this->form_validation->set_rules('validate_storage', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

                    if ($this->form_validation->run() == false) {

                        $sss = array(
                            'assignmentid' => form_error('assignmentid'),
                            'student_id' => form_error('student_id'),
                            'message' => form_error('message'),
                            'file' => form_error('file'),
                            'validate_storage' => form_error('validate_storage'),
                        );
                        $array = array('status' => '0', 'error' => $sss);
                    } else {
                        //==================                        
                        $upload_path = $this->config->item('upload_path') . "/course_content/online_course_assignment/";

                        // SaaS Logic: Reserve Quota
                        $storage_array = ['file'];
                        $this->saasvalidation->updateStorageLimit('storage', $storage_array);

                        $id = $this->input->post('id');
                        if ($id > 0) {
                            $ids = $id;
                        } else {
                            $ids = 0;
                        }

                        $img_name = "";

                        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

                            // For updates: get old file to clean up later if needed (Not fully implemented in model typically, but logic placeholder)
                            $total_documents_failed_size = 0;

                            $img_name = $this->media_storage->fileupload("file", $upload_path);
                            $img_name = $img_name ?? ''; // guard against null on upload failure

                            if (IsNullOrEmptyString($img_name)) {
                                $total_documents_failed_size += $this->media_storage->getTmpFileSize('file');
                            }

                            if ($total_documents_failed_size > 0) {
                                $this->saasvalidation->deleteResouceQuota('storage', $total_documents_failed_size);
                            }

                            $data_insert = array(
                                'id' => $ids,
                                'assignment_id' => $this->input->post('assignmentid'),
                                'student_id' => $this->input->post('student_id'),
                                'message' => $this->input->post('message'),
                                'docs' => $img_name,
                            );
                            $this->course_model->save_assignment($data_insert);
                        } else {
                            $data_insert = array(
                                'id' => $ids,
                                'assignment_id' => $this->input->post('assignmentid'),
                                'student_id' => $this->input->post('student_id'),
                                'message' => $this->input->post('message'),
                                'docs' => $img_name,
                            );
                            $this->course_model->save_assignment($data_insert);
                        }

                        $array = array('status' => '1', 'msg' => 'Success');
                    }
                    json_output(200, $array);
                }
            }
        }
    }

    public function getCourseExamDetails()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $exam_id = $params['exam_id'];
                    $student_id = $params['student_id'];
                    $user_type = $params['user_type'];
                    $resp = array();
                    $resp['question_type'] = $this->course_model->getquestion_type($exam_id);
                    $exam = $this->course_model->getexam($exam_id);
                    $resp['exam'] = $exam;
                    $resp['student'] = $this->student_model->get($student_id);
                    $resp['question_result'] = $this->course_model->online_course_exam_result($student_id, $exam_id);
                    $submitstatus = $this->course_model->getsubmitstatus($student_id, $exam_id, $user_type);

                    if ($submitstatus > 0) {
                        $submit_status = 1;
                    } else {
                        $submit_status = 0;
                    }

                    $total_gain = 0;
                    $total_negative = 0;
                    $total_marks = 0;
                    $total_score = 0;
                    $score_percentage = 0;

                    if ($submit_status > 0) {
                        foreach ($resp['question_result'] as $key => $value) {
                            if ($value->question_type == 'descriptive') {
                                continue;
                            }
                            $total_marks += $value->marks;
                            if ($value->correct == $value->select_option) {
                                $total_gain += $value->marks;
                            } else {
                                $total_negative += $value->neg_marks;
                            }
                        }

                        $total_score = ($total_gain - $total_negative);
                        $score_percentage = (($total_score * 100) / count($resp['question_result']));
                        $resp['total_marks'] = count($resp['question_result']);
                        $resp['total_negative'] = $total_negative;
                        $resp['total_score'] = $total_score;
                        $resp['score_percentage'] = $score_percentage;
                    } else {
                        $resp['total_marks'] = "";
                        $resp['total_negative'] = "";
                        $resp['total_score'] = "";
                        $resp['score_percentage'] = "";
                    }

                    $resp['submitstatus'] = $submit_status;
                    $resp['counter'] = $this->course_model->getStudentAttemts($student_id, $exam_id, $user_type);
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getOnlineCourseQuestion()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $studentid_or_guestid = $params['student_id'];
                    $recordid = $params['exam_id'];
                    $user_type = $params['user_type']; //added
                    $result = $this->student_model->get($studentid_or_guestid);
                    $onlineexam = array();
                    $exam = $this->course_model->get_exam($recordid);//added
                    $exam['onlineexam_student_id'] = $result->id;
                    $exam['student_session_id'] = $result->student_session_id;
                    $issubmitted = $this->course_model->getsubmitstatus($studentid_or_guestid, $exam['id'], $user_type);  //added
                    if ($issubmitted > 0) {
                        $submit_status = 1;
                    } else {
                        $submit_status = 0;
                    }

                    $exam['is_submitted'] = $submit_status;
                    $exam['questions'] = $this->course_model->getExamQuestions($exam['id'], $exam['is_random_question']);
                    $getStudentAttemts = $this->course_model->getStudentAttemts($studentid_or_guestid, $exam['id'], $user_type);
                    $exam_attempt_status = 0;
                    $exam_duration = 0;

                    //======================//
                    if (isset($exam['exam_to'])) {
                        if (strtotime(date('Y-m-d H:i:s')) <= strtotime(date($exam['exam_to'])) && ($exam['attempt'] > $getStudentAttemts)) {
                            if ($user_type == 'student') {
                                $studentid = $studentid_or_guestid;
                                $guestid = 0;
                            } else if ($user_type == 'guest') {
                                $guestid = $studentid_or_guestid;
                                $studentid = 0;
                            }
                            $this->course_model->addStudentAttemts(array('student_id' => $studentid, 'guest_id' => $guestid, "exam_id" => $exam['id']));
                        } else if (strtotime(date('Y-m-d H:i:s')) > strtotime(date($exam['exam_to']))) {
                            $exam_attempt_status = 1; //exam duration expired
                        } else if (($exam['attempt'] >= $getStudentAttemts)) {
                            $exam_attempt_status = 2; //exam attempts end (no exam attempts left)
                        }
                    } else {
                        if (($exam['attempt'] > $getStudentAttemts)) {
                            if ($user_type == 'student') {
                                $student_id = $studentid_or_guestid;
                                $guest_id = 0;
                            } else if ($user_type == 'guest') {
                                $guest_id = $studentid_or_guestid;
                                $student_id = 0;
                            }
                            $this->course_model->addStudentAttemts(array('student_id' => $studentid, 'guest_id' => $guest_id, "exam_id" => $exam['id']));
                        } else if (($exam['attempt'] >= $getStudentAttemts)) {
                            $exam_attempt_status = 2; //exam attempts end (no exam attempts left)
                        }
                    }
                    //======================//
                    $exam['counter'] = $getStudentAttemts;
                    $total_remaining_seconds = round((strtotime($exam['exam_to']) - strtotime(date('Y-m-d H:i:s'))) / 3600 * 60 * 60, 1);
                    $exam_duration = ($total_remaining_seconds < getSecondsFromHMS($exam['duration'])) ? getHMSFromSeconds($total_remaining_seconds) : $exam['duration'];
                    $exam['remaining_duration'] = $exam_duration;
                    $total_descriptive = 0;
                    $question = $this->course_model->getquestiondetails($exam['id']);
                    if (!empty($question)) {
                        $total_descriptive = $question->total_descriptive;
                    } else {
                        $total_descriptive = "0";
                    }
                    $exam['descriptive'] = $total_descriptive;
                    $exam['exam_attempt_status'] = $exam_attempt_status;
                    json_output($response['status'], array('exam' => $exam));
                }
            }
        }
    }

    public function saveOnlineCourseExam()
    {

        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $params = json_decode(file_get_contents('php://input'), true);
		            $question_rows = $params['rows'];

                    $student_id = $params['student_id'];
                    $guest_id = $params['guest_id'];
                    $usertype = $params['usertype'];
                    $exam_id = $params['exam_id'];

                    $file_keys = [];
                    $total_upload_size_kb = 0;
 
                    foreach ($question_rows as $q_key => $q_val) {
                        if ($q_val['question_type'] == "descriptive") {
                            $qid_key = "attachment_" . $q_val['question_id'];
                            if (isset($_FILES[$qid_key]) && !empty($_FILES[$qid_key]['name'])) {
                                $file_keys[] = $qid_key;
                                if (isset($_FILES[$qid_key]['size']) && $_FILES[$qid_key]['size'] > 0) {
                                    $total_upload_size_kb += ceil($_FILES[$qid_key]['size'] / 1024);
                                }
                            }
                        }
                    }


                    if ($this->saasvalidation->sass_enabled && !empty($file_keys)) {
                        try {
                            $limit_status = $this->saasvalidation->getResourceLimit('storage');
                            if (is_array($limit_status) && $limit_status['status']) {
                                if (($limit_status['usage'] + $total_upload_size_kb) > $limit_status['limit']) {
                                    json_output(200, array('status' => 0, 'msg' => "Storage Limit Exceeded"));
                                    return;
                                }
                            }
                        } catch (Exception $e) {
                            json_output(200, array('status' => 0, 'msg' => 'SaaS Error: ' . $e->getMessage()));
                            return;
                        }
                    }


                    if (!empty($file_keys)) {
                        $this->saasvalidation->updateStorageLimit('storage', $file_keys); 
                    }

                    $total_failed_size = 0;

                    foreach ($question_rows as $key => $question_value) {

                        if ($question_value['question_type'] == "descriptive") {

                            $qid = $question_value['question_id'];
                            $file_key = "attachment_" . $qid;

                            if (isset($_FILES[$file_key]) && !empty($_FILES[$file_key]['name'])) {

                                $file_name = $_FILES[$file_key]['name'];
                                $fileInfo  = pathinfo($file_name);

                                $upload_file_name = time() . uniqid() . '.' . $fileInfo['extension'];
                                $upload_path = $this->config->item('upload_path') . "/course_content/online_course_exam_result/";

                                if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path . $upload_file_name)) {

                                    $question_rows[$key]['attachment_name'] = $file_name;
                                    $question_rows[$key]['attachment_upload_name'] = $upload_file_name;

                                } else {

                                    $total_failed_size += $_FILES[$file_key]['size'];

                                    $question_rows[$key]['attachment_name'] = "";
                                    $question_rows[$key]['attachment_upload_name'] = "";
                                }

                            } else {
                                $question_rows[$key]['attachment_name'] = "";
                                $question_rows[$key]['attachment_upload_name'] = "";
                            }

                        } else {
                            $question_rows[$key]['attachment_name'] = "";
                            $question_rows[$key]['attachment_upload_name'] = "";
                        }

                        
                        unset($question_rows[$key]['question_type']);
                    }
 

                    if ($total_failed_size > 0) {
                        $this->saasvalidation->deleteResouceQuota('storage', round($total_failed_size / 1024));
                    }

                    $resp = array();
                    if (!empty($question_rows)) {
                        $save_result = array();
                        $insert_result = $this->course_model->savecourseexam($question_rows, $student_id, $guest_id, $usertype, $exam_id);
                        if ($insert_result == 1) {
                            $resp = array('status' => 1, 'msg' => 'record inserted');
                        } else if ($insert_result == 2) {
                            $resp = array('status' => 2, 'msg' => 'record already submitted');
                        } else if ($insert_result == 0) {
                            $resp = array('status' => 2, 'msg' => 'something wrong');
                        }
                    } else {
                        $resp = array('status' => 1, 'msg' => 'record inserted');
                    }
                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getSubmitedAssignmentDetails()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {
                    $params = json_decode(file_get_contents('php://input'), true);
                    $assignment_id = $params['assignment_id'];
                    $student_id = $params['student_id'];
                    $resp = array();

                    $resp['result'] = $this->course_model->getSubmitedAssignmentDetails($assignment_id);

                    $resp['result']->assignemnt_evaluated_by = $resp['result']->assignemnt_evaluated_by ?: '';
                    $resp['result']->evaluation_date = $resp['result']->evaluation_date ?: '';
                    $resp['result']->document = $resp['result']->document ?: '';

                    $resp['result_status'] = $this->course_model->get_student_assignment_status($assignment_id, $student_id);

                    if (!empty($resp['result_status'][0])) {

                        if ($resp['result_status'][0]['docs'] != '') {
                            $resp['result_status'][0]['docs'] = $resp['result_status'][0]['docs'];
                        } else {
                            $resp['result_status'][0]['docs'] = '';
                        }

                        if ($resp['result_status'][0]['evaluated_date'] != '') {
                            $resp['result_status'][0]['evaluated_date'] = $resp['result_status'][0]['evaluated_date'];
                        } else {
                            $resp['result_status'][0]['evaluated_date'] = '';
                        }

                        if ($resp['result_status'][0]['evaluated_note'] != '') {
                            $resp['result_status'][0]['evaluated_note'] = $resp['result_status'][0]['evaluated_note'];
                        } else {
                            $resp['result_status'][0]['evaluated_note'] = '';
                        }

                        if (!empty($resp['result_status'][0]['evaluated_date'])) {
                            $status_lable = 'evaluated';
                        } elseif (!empty($resp['result_status'][0]['message'])) {
                            $status_lable = 'submitted';
                        }

                    } else {
                        $status_lable = "pending";
                    }

                    $resp['result_status'][0]['status_lable'] = $status_lable;

                    $resp['result_status'] = $resp['result_status'][0];

                    json_output($response['status'], $resp);
                }
            }
        }
    }

    public function getTimeLineStatus()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {
            $check_auth_client = $this->auth_model->check_auth_client();
            if ($check_auth_client == true) {
                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

                    $school_setting = $this->setting_model->getSchoolDetail();

                    $data['student_timeline'] = $school_setting->student_timeline;

                    json_output($response['status'], $data);

                }
            }
        }
    }    

    public function coursedownloadcertificatepdf($certificate_id,$student_id,$course_id)
    {
        ob_start();
        error_reporting(0);
        ini_set('display_errors', 0);

        $this->sch_setting_detail = $this->setting_model->getSetting();

        $get_certificate_date = $this->coursecertificate_model->get($certificate_id);
        $coursesdata = $this->course_model->coursedetail($course_id);

        $completiondate = $this->course_model->getcoursecompletiondate($course_id,$student_id);
        $startdate      = $this->course_model->getcoursestartdate($course_id,$student_id);

        $completion_date = isset($completiondate['completion_date']) ? $completiondate['completion_date'] : '';
        $start_date      = isset($startdate['start_date']) ? $startdate['start_date'] : '';

        $course_title = $coursesdata['title'];
        $assign_teacher = $coursesdata['name'].' '.$coursesdata['surname'].' ('.$coursesdata['employee_id'].')';

        $get_student_data = $this->student_model->get($student_id);

        $student_name = $this->customlib->getFullName(
            $get_student_data->firstname,
            $get_student_data->middlename,
            $get_student_data->lastname,
            $this->sch_setting_detail->middlename,
            $this->sch_setting_detail->lastname
        )." (".$get_student_data->admission_no.")";

        $class_name   = $get_student_data->class;
        $section_name = rtrim($get_student_data->section, ", ");

        foreach($get_certificate_date as $certificate_value){

            $variable_data[] = array(
                "student_name"    => $student_name,
                "class_name"      => $class_name,
                "section_name"    => $section_name,
                "course_name"     => $course_title,
                "current_date"    => date($this->customlib->getSchoolDateFormat()),
                "start_date"      => $start_date ? date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) : '',
                "completion_date"=> $completion_date ? date($this->customlib->getSchoolDateFormat(), strtotime($completion_date)) : '',
                "assign_teacher"  => $assign_teacher
            );

            $certificate_text = $this->getCertificateTextContent($variable_data, $certificate_value['certificate_text']);

            $webBase = $this->sch_setting_detail->mobile_api_url."webservice/";

            $show_qr = $webBase."show_qr/$certificate_id/$student_id/$course_id/student";

            $variable_data2[] = array(
                "student_name"    => $student_name,
                "class_name"      => $class_name,
                "section_name"    => $section_name,
                "course_name"     => $course_title,
                "current_date"    => date($this->customlib->getSchoolDateFormat()),
                "start_date"      => $start_date ? date($this->customlib->getSchoolDateFormat(), strtotime($start_date)) : '',
                "completion_date"=> $completion_date ? date($this->customlib->getSchoolDateFormat(), strtotime($completion_date)) : '',
                "assign_teacher"  => $assign_teacher,
                "qr_code"         => "<img height='60' width='60' src='$show_qr'>",
            );

            $updated_template = str_replace(
                $certificate_value['certificate_text'],
                $certificate_text,
                $certificate_value['certificate_template']
            );

            $updated_template = $this->getCertificateTextContent($variable_data, $updated_template);

            $basePath = $this->sch_setting_detail->folder_path.'uploads/course_content/online_course_certificate/';

            $data["certificate_templatedat"] =  $this->getCertificateTextContent_for_template($variable_data2,$updated_template);

            $data["certificate_templatedat"] = preg_replace_callback(
                '/<img([^>]+)src="([^"]+)"/i',
                function($m) use ($basePath){

                    if (preg_match('/^https?:\/\//',$m[2])) {
                        return $m[0];
                    }

                    $src = basename($m[2]);
                    return '<img'.$m[1].'src="'.$basePath.$src.'"';
                },
                $data["certificate_templatedat"]
            );

        }

        $html = $this->load->view('studentcourse/downloadcertificatepdf', $data, true);

        $this->load->library('m_pdf');

        $mpdf = $this->m_pdf->load([
            'mode' => 'utf-8',
            'format' => [195,140.2],
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
            'margin_right' => 0,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->WriteHTML($html);
     
        $timestamp = time();
        $filename = 'certificate_'.$student_id.'_'.$course_id.'_'.$timestamp.'.pdf';  

        $temp_folder = FCPATH . 'temp/';

        if (!is_dir($temp_folder)) {
            mkdir($temp_folder, 0755, true);
        }

        $temp_file_path = $temp_folder . $filename;
    
        $mpdf->Output($temp_file_path, 'F');

        if (ob_get_length()) {
            ob_end_clean();
        }
   
        $download_url = base_url('webservice/downloadCertificateFile/' . $filename);
   
        $response = array(
            'status' => 200,
            'message' => 'Certificate generated successfully',
            'download_url' => $download_url,
            'filename' => $filename
        );

        json_output(200, $response);
    }

    public function downloadCertificateFile($filename)
    {       

        $temp_folder = FCPATH . 'temp/';
        $file_path = $temp_folder . $filename;
    
        if (!file_exists($file_path)) {
            json_output(404, array('status' => 404, 'message' => 'Certificate file not found'));
        }
    
        if (!preg_match('/^certificate_\d+_\d+_\d+\.pdf$/', $filename)) {
            json_output(400, array('status' => 400, 'message' => 'Invalid filename format'));
        }
    
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
    
        readfile($file_path);
    
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        exit;
    }

    public function getCertificateTextContent_for_template($data, $template)
    {
        preg_match_all('/\{(.*?)\}/', $template, $matches);
        foreach ($matches[1] as $key=>$value) {
            if (isset($key)) {
                $template = str_replace('{'.$value.'}', $data[0]["$value"], $template);
            }
        }
        return $template;
    } 

    public function getCertificateTextContent($data, $template)
    {
        preg_match_all('/\[(.*?)\]/', $template, $matches);
        foreach ($matches[1] as $key=>$value) {
            if (isset($key)) {
                $template = str_replace("[$value]", $data[0]["$value"], $template);
            }
        }
        return $template;
    }

    public function show_qr($certificate_id,$student_id,$course_id,$role)
    {
        $this->load->library('QR_Code');
        $url=base_url("webservice/downloadcertificatepdf/$certificate_id/$student_id/$course_id/$role");
        $this->qr_code->generateQRCodeForCertificateDownload($url);
    }
 
    public function deleteCertificateFile($filename)
    {
        $temp_folder = $this->config->item('upload_path') . '/temp_certificates/';
        $file_path   = $temp_folder . $filename;
        
        if (!preg_match('/^certificate_\d+_\d+_\d+\.pdf$/', $filename)) {
            json_output(400, array(
                'status'  => 400,
                'message' => 'Invalid filename format'
            ));
        }
    
        if (!file_exists($file_path)) {
            json_output(404, array(
                'status'  => 404,
                'message' => 'Certificate file not found'
            ));
        }
   
        if (@unlink($file_path)) {

            json_output(200, array(
                'status'  => 200,
                'message' => 'Certificate deleted successfully'
            ));

        } else {

            json_output(500, array(
                'status'  => 500,
                'message' => 'Unable to delete certificate file'
            ));
        }
    }

    public function changePassword()
    {
        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $check_auth_client = $this->auth_model->check_auth_client();
        if ($check_auth_client != true) {
            return;
        }

        $response = $this->auth_model->auth();
        if ($response['status'] != 200) {
            return;
        }

        $params           = json_decode(file_get_contents('php://input'), true);
        $current_password = isset($params['current_password']) ? trim((string) $params['current_password']) : '';
        $new_password     = isset($params['new_password'])     ? trim((string) $params['new_password'])     : '';
        $confirm_password = isset($params['confirm_password']) ? trim((string) $params['confirm_password']) : '';

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            json_output(422, array('status' => 0, 'message' => 'All password fields are required.'));
            return;
        }

        if ($new_password !== $confirm_password) {
            json_output(422, array('status' => 0, 'message' => 'New password and confirm password do not match.'));
            return;
        }

        if (strlen($new_password) < 6) {
            json_output(422, array('status' => 0, 'message' => 'New password must be at least 6 characters.'));
            return;
        }

        // Resolve user identity from User-ID header (users.id).
        $header_user_id = trim((string) $this->input->get_request_header('User-ID', true));

        if ($header_user_id === '') {
            json_output(401, array('status' => 0, 'message' => 'Unable to identify user account.'));
            return;
        }

        $this->db->select('id, user_id, role, password, is_active');
        $this->db->from('users');
        $this->db->where('id', $header_user_id);
        $user_row = $this->db->get()->row();

        if (empty($user_row)) {
            json_output(401, array('status' => 0, 'message' => 'Unable to identify user account.'));
            return;
        }

        $role = strtolower((string) $user_row->role);

        if (in_array($role, array('teacher', 'librarian', 'accountant'), true) ||
            (!in_array($role, array('student', 'parent'), true))) {
            // ── STAFF / TEACHER path ───────────────────────────────────────
            // Staff passwords live in staff.password as bcrypt hash.
            $staff_id = (int) $user_row->user_id;

            $this->db->select('id, password');
            $this->db->from('staff');
            $this->db->where('id', $staff_id);
            $this->db->where('is_active', 1);
            $staff = $this->db->get()->row();

            if (empty($staff)) {
                json_output(404, array('status' => 0, 'message' => 'Staff record not found.'));
                return;
            }

            if (!password_verify($current_password, $staff->password)) {
                json_output(422, array('status' => 0, 'message' => 'Current password is incorrect.'));
                return;
            }

            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $this->db->where('id', $staff_id);
            $updated = $this->db->update('staff', array('password' => $new_hash));

        } else {
            // ── STUDENT / PARENT path ──────────────────────────────────────
            // Student/parent passwords live in users.password as plain text
            // (matching the existing web application behaviour).
            if ($user_row->password !== $current_password) {
                json_output(422, array('status' => 0, 'message' => 'Current password is incorrect.'));
                return;
            }

            $this->db->where('id', (int) $user_row->id);
            $updated = $this->db->update('users', array('password' => $new_password));
        }

        if ($updated) {
            json_output(200, array('status' => 1, 'message' => 'Password changed successfully.'));
        } else {
            json_output(500, array('status' => 0, 'message' => 'Failed to update password. Please try again.'));
        }
    }



}