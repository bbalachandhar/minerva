<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Student_fee_override extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_fee_override_model');
        $this->load->library('form_validation');
        $this->load->helper('download');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('student_fee_override', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/student_fee_override');

        $current_session_id = (int)$this->setting_model->getCurrentSession();

        $data['title']          = 'Student Fee Override';
        $data['sessions']       = $this->student_fee_override_model->getSessions();
        $data['classes']        = [];
        $data['student_list']   = null;
        $data['selected_session_id'] = $current_session_id;
        $data['selected_class_id']   = null;
        $data['selected_section_id'] = null;

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $session_id = (int)$this->input->post('session_id');
            $class_id   = (int)$this->input->post('class_id');
            $section_id = (int)$this->input->post('section_id') ?: null;

            $data['selected_session_id'] = $session_id ?: $current_session_id;
            $data['selected_class_id']   = $class_id;
            $data['selected_section_id'] = $section_id;

            $data['classes'] = $this->student_fee_override_model->getClassesForSession($data['selected_session_id']);

            if ($class_id) {
                $data['student_list'] = $this->student_fee_override_model->getStudentsWithFeeOverrides(
                    $data['selected_session_id'],
                    $class_id,
                    $section_id
                );
            }
        } else {
            $data['classes'] = $this->student_fee_override_model->getClassesForSession($current_session_id);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/student_fee_override/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX — save a single override.
     */
    public function save()
    {
        if (!$this->rbac->hasPrivilege('student_fee_override', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            return;
        }

        $student_session_id    = (int)$this->input->post('student_session_id');
        $fee_groups_feetype_id = (int)$this->input->post('fee_groups_feetype_id');
        $override_amount       = (float)$this->input->post('override_amount');
        $note                  = $this->input->post('note');

        if (!$student_session_id || !$fee_groups_feetype_id || $override_amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
            return;
        }

        $paid = $this->student_fee_override_model->getPaidAmount($student_session_id, $fee_groups_feetype_id);
        if ($override_amount < $paid) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Student has already paid more than the override amount you entered. The system cannot reduce the fee below what has already been collected.',
            ]);
            return;
        }

        $user_id = $this->customlib->getStaffID();
        $result  = $this->student_fee_override_model->saveOverride(
            $student_session_id,
            $fee_groups_feetype_id,
            $override_amount,
            $note,
            $user_id
        );

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Override saved successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save override.']);
        }
    }

    /**
     * AJAX — delete a single override.
     */
    public function delete()
    {
        if (!$this->rbac->hasPrivilege('student_fee_override', 'can_delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied.']);
            return;
        }

        $student_session_id    = (int)$this->input->post('student_session_id');
        $fee_groups_feetype_id = (int)$this->input->post('fee_groups_feetype_id');

        $paid = $this->student_fee_override_model->getPaidAmount($student_session_id, $fee_groups_feetype_id);
        if ($paid > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot remove override after a payment has already been made.']);
            return;
        }

        $result = $this->student_fee_override_model->deleteOverride($student_session_id, $fee_groups_feetype_id);
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Override removed successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No override found to remove.']);
        }
    }

    /**
     * Bulk CSV import form + processing.
     */
    public function bulk_import()
    {
        if (!$this->rbac->hasPrivilege('student_fee_override', 'can_add')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/student_fee_override');

        $data['title']    = 'Bulk Import Student Fee Override';
        $data['sessions'] = $this->student_fee_override_model->getSessions();
        $data['current_session_id'] = (int)$this->setting_model->getCurrentSession();

        $this->form_validation->set_rules('file', 'File', 'callback_handle_csv_upload');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/student_fee_override/bulk_import', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $session_id = (int)$this->input->post('session_id');
            if (!$session_id) {
                $session_id = (int)$this->setting_model->getCurrentSession();
            }

            if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
                $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'csv') {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please upload a CSV file only.</div>');
                    redirect('admin/student_fee_override/bulk_import');
                    return;
                }

                $handle = fopen($_FILES['file']['tmp_name'], 'r');
                $header = fgetcsv($handle, 1000, ',');

                // Normalise header keys
                $header = array_map('trim', $header);
                $required_headers = ['admission_no', 'fee_type_code', 'override_amount', 'note'];
                $missing = array_diff($required_headers, array_map('strtolower', $header));

                if (!empty($missing)) {
                    fclose($handle);
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">CSV header is missing columns: ' . implode(', ', $missing) . '. Please use the sample file format.</div>');
                    redirect('admin/student_fee_override/bulk_import');
                    return;
                }

                $rows = [];
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($header) === count($row)) {
                        $rows[] = array_combine($header, $row);
                    }
                }
                fclose($handle);

                if (empty($rows)) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No data rows found in the CSV file.</div>');
                    redirect('admin/student_fee_override/bulk_import');
                    return;
                }

                $user_id = $this->customlib->getStaffID();
                $result  = $this->student_fee_override_model->bulkImportOverrides($rows, $session_id, $user_id);

                $msg = '<div class="alert alert-success text-left">' . $result['imported'] . ' record(s) imported successfully.</div>';
                if (!empty($result['failed'])) {
                    $msg .= '<div class="alert alert-warning text-left"><strong>' . count($result['failed']) . ' record(s) failed:</strong><br>';
                    foreach ($result['failed'] as $f) {
                        $adm  = htmlspecialchars($f['row']['admission_no'] ?? '');
                        $code = htmlspecialchars($f['row']['fee_type_code'] ?? '');
                        $msg .= '&bull; Adm: ' . $adm . ' | Fee Type: ' . $code . ' — ' . htmlspecialchars($f['reason']) . '<br>';
                    }
                    $msg .= '</div>';
                }

                $this->session->set_flashdata('msg', $msg);
                redirect('admin/student_fee_override/bulk_import');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please select a file to upload.</div>');
                redirect('admin/student_fee_override/bulk_import');
            }
        }
    }

    /**
     * Download sample CSV file.
     */
    public function exportformat()
    {
        $filepath = './backend/import/import_student_fee_override_sample.csv';
        $content  = file_get_contents($filepath);
        force_download('import_student_fee_override_sample.csv', $content);
    }

    /**
     * Form validation callback — validates the uploaded CSV file.
     */
    public function handle_csv_upload()
    {
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $allowed_mimes = [
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt',
            ];
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if ($_FILES['file']['error'] > 0) {
                $this->form_validation->set_message('handle_csv_upload', 'Error reading the uploaded file.');
                return false;
            }
            if (!in_array($_FILES['file']['type'], $allowed_mimes)) {
                $this->form_validation->set_message('handle_csv_upload', 'File type not allowed. Please upload a CSV file.');
                return false;
            }
            if ($ext !== 'csv') {
                $this->form_validation->set_message('handle_csv_upload', 'Only .csv files are allowed.');
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_csv_upload', 'Please select a file.');
            return false;
        }
    }
}
