<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Collect_incidental_fee extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('incidental_fee_type_model');
        $this->load->model('incidental_fee_assignment_model');
        $this->load->model('incidental_fee_collection_model');
        $this->load->model('onlinestudent_model');
        $this->load->model('session_model');
        $this->load->model('class_model');
        $this->load->model('student_model');
        $this->load->model('setting_model');
        $this->load->library('form_validation');
        $this->load->library('media_storage');

    }

    public function index() {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            access_denied();
        }

        if (!$this->input->post('bill_date')) {
            $_POST['bill_date'] = date('Y-m-d');
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/collect_incidental_fee');

        $data['title'] = 'Collect Incidental Fee';
        $data['fee_types'] = $this->incidental_fee_type_model->get();
        $data['sessions'] = $this->session_model->get();
                        $data['classes'] = $this->class_model->get();
                        $data['student_detail'] = array();
                        $data['outstanding_assignments'] = array();
                        $data['sections'] = array();        $this->form_validation->set_rules('student_id', $this->lang->line('student'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('fee_type_id', $this->lang->line('fee_type'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount_collected', $this->lang->line('amount_collected'), 'required|numeric|trim|xss_clean');
        $this->form_validation->set_rules('bill_date', 'Bill Date', 'required|trim|regex_match[/^\d{4}-\d{2}-\d{2}$/]|xss_clean');
        $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required|trim|xss_clean');
        $this->form_validation->set_rules('application_ref_no', 'Application Ref No', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/incidental_fee_collection/collect_incidental_fee', $data);
            $this->load->view('layout/footer');
        } else {
            // Start database transaction for data consistency
            $this->db->trans_start();
            
            $student_id = $this->input->post('student_id');
            $session_id = $this->input->post('session_id');
            $fee_type_id = $this->input->post('fee_type_id');
            $amount_collected = $this->input->post('amount_collected');
            $incidental_fee_assignment_id = $this->input->post('incidental_fee_assignment_id'); // Can be NULL for ad-hoc
            $collected_by = $this->customlib->getStaffID();
            // Convert empty string to NULL so STRICT_TRANS_TABLES doesn't reject it for the INT column
            $collected_by = ($collected_by !== '' && $collected_by !== null) ? (int) $collected_by : NULL;
            $receipt_no = $this->incidental_fee_collection_model->get_receipt_no();
            $payment_mode = $this->input->post('payment_mode');
            $application_ref_no = $this->input->post('application_ref_no');

            $insert_data = array(
                'incidental_fee_type_id' => $fee_type_id,
                'incidental_fee_assignment_id' => $incidental_fee_assignment_id ? $incidental_fee_assignment_id : NULL,
                'session_id' => $session_id,
                'student_id' => $student_id,
                'amount_collected' => $amount_collected,
                'bill_date' => $this->input->post('bill_date'),
                'collected_by' => $collected_by,
                'receipt_no' => $receipt_no,
                'payment_mode' => $payment_mode,
                'application_ref_no' => $application_ref_no ? $application_ref_no : NULL,
                'notes' => $this->input->post('notes'),
            );

            $collection_id = $this->incidental_fee_collection_model->add($insert_data);

            if ($collection_id) {
                if ($incidental_fee_assignment_id) {
                    $assignment = $this->incidental_fee_assignment_model->get($incidental_fee_assignment_id);
                    if ($assignment) {
                        if ($amount_collected >= $assignment['amount_due']) {
                            $this->incidental_fee_assignment_model->update($incidental_fee_assignment_id, array('status' => 'paid'));
                        } else {
                            $this->incidental_fee_assignment_model->update($incidental_fee_assignment_id, array('status' => 'partially_paid'));
                        }
                    }
                }
                
                // Complete transaction
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status' => 'error', 'message' => $this->lang->line('error_collecting_fee')));
                } else {
                    $collection_id = (int) $collection_id;
                    $response = array(
                        'status' => 'success',
                        'message' => $this->lang->line('fee_collected_successfully'),
                        'collection_id' => $collection_id,
                        'id' => $collection_id,
                        'receipt_url' => site_url('financereports/print_incidental_receipt/' . $collection_id),
                        'receipt_no' => $receipt_no,
                        'payment_mode' => $payment_mode,
                        'amount_collected' => $amount_collected
                    );
                    echo json_encode($response);
                }
            } else {
                $this->db->trans_rollback();
                echo json_encode(array('status' => 'error', 'message' => $this->lang->line('error_collecting_fee')));
            }
        }
    }

    public function searchStudent() {
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            redirect('admin/collect_incidental_fee');
        }
        else {
            $this->session->set_userdata('top_menu', 'Fees Collection');
            $this->session->set_userdata('sub_menu', 'admin/collect_incidental_fee');

            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $search_text = $this->input->post('search_text');
            $session_id = $this->setting_model->getCurrentSession();

            if (empty($class_id) && empty($section_id) && empty($search_text)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please provide at least one search parameter.</div>');
                redirect('admin/collect_incidental_fee');
            }

            $data['student_list'] = $this->student_model->searchStudentsByClassSectionAndText($class_id, $section_id, $session_id, $search_text);
            $data['fee_types'] = $this->incidental_fee_type_model->get();
            $data['sessions'] = $this->session_model->get();
            $data['classes'] = $this->class_model->get();
            $data['sections'] = $this->class_model->get_section($class_id);

            $data['class_id'] = $class_id;
            $data['section_id'] = $section_id;
            $data['session_id'] = $session_id;
            $data['search_text'] = $search_text;

            $this->load->view('layout/header', $data);
            $this->load->view('admin/incidental_fee_collection/collect_incidental_fee', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function getStudentDetails() {
        $student_id = $this->input->post('student_id');
        $session_id = $this->input->post('session_id');

        $student_detail = $this->student_model->get($student_id);
        $outstanding_assignments = $this->incidental_fee_assignment_model->get_by_student_session($student_id, $session_id);

        echo json_encode(array('student_detail' => $student_detail, 'outstanding_assignments' => $outstanding_assignments));
    }

    public function collectNonStudentFee() {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_add')) {
            echo json_encode(array('status' => 'error', 'message' => $this->lang->line('access_denied')));
            exit();
        }

        if (!$this->input->post('bill_date')) {
            $_POST['bill_date'] = date('Y-m-d');
        }

        $this->form_validation->set_rules('non_student_name', $this->lang->line('name'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('fee_type_id', $this->lang->line('fee_type'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('amount_collected', $this->lang->line('amount_collected'), 'required|numeric|trim|xss_clean');
        $this->form_validation->set_rules('bill_date', 'Bill Date', 'required|trim|regex_match[/^\d{4}-\d{2}-\d{2}$/]|xss_clean');
        $this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required|trim|xss_clean');
        $this->form_validation->set_rules('application_ref_no', 'Application Ref No', 'trim|xss_clean');
        // Notes is optional, so no rule needed unless specific validation is required

        if ($this->form_validation->run() == FALSE) {
            echo json_encode(array('status' => 'error', 'message' => validation_errors()));
        } else {
            // Gather all data BEFORE starting the transaction so SELECT failures
            // do not corrupt the transaction status and cause a false "error collecting fee".
            $session_id = (int) $this->setting_model->getCurrentSession();
            $collected_by = $this->customlib->getStaffID();
            // Convert empty string to NULL so STRICT_TRANS_TABLES doesn't reject it for the INT column
            $collected_by = ($collected_by !== '' && $collected_by !== null) ? (int) $collected_by : NULL;
            $receipt_no = $this->incidental_fee_collection_model->get_receipt_no();
            $payment_mode = $this->input->post('payment_mode');
            $application_ref_no = $this->input->post('application_ref_no');
            $application_ref_no = trim((string) $application_ref_no);
            $fee_type_id = (int) $this->input->post('fee_type_id');
            $amount_collected = (float) $this->input->post('amount_collected');

            $fee_type = $this->incidental_fee_type_model->get($fee_type_id);
            $fee_type_title = isset($fee_type['title']) ? $fee_type['title'] : '';
            $application_ref_required = $this->isApplicationRefRequiredForFeeType($fee_type_title);

            $online_admission = null;
            if ($application_ref_required) {
                if ($application_ref_no === '') {
                    echo json_encode(array('status' => 'error', 'message' => 'Application Reference No is required for this fee type.'));
                    return;
                }

                $online_admission = $this->getOnlineAdmissionByReference($application_ref_no);
                if (empty($online_admission)) {
                    echo json_encode(array('status' => 'error', 'message' => 'No online application found for the given Application Reference No.'));
                    return;
                }

                $due_summary = $this->getApplicationDueSummary($online_admission, $application_ref_no);
                if ($amount_collected > (float) ($due_summary['remaining_fee'] ?? 0)) {
                    echo json_encode(array('status' => 'error', 'message' => 'Collected amount cannot exceed remaining payable amount.'));
                    return;
                }
            }

            $non_student_name = trim((string) $this->input->post('non_student_name'));
            if ($application_ref_required && empty($non_student_name) && !empty($online_admission)) {
                $non_student_name = trim((string) ($online_admission['applicant_name'] ?? ''));
            }

            $insert_data = array(
                'student_id'                   => NULL,
                'non_student_name'             => $non_student_name,
                'incidental_fee_type_id'       => $fee_type_id,
                'incidental_fee_assignment_id' => NULL,
                'session_id'                   => $session_id,
                'amount_collected'             => $amount_collected,
                'bill_date'                    => $this->input->post('bill_date'),
                'collected_by'                 => $collected_by,
                'receipt_no'                   => $receipt_no,
                'payment_mode'                 => $payment_mode,
                'application_ref_no'           => $application_ref_no ? $application_ref_no : NULL,
                'notes'                        => $this->input->post('notes'),
                'date_collected'               => date('Y-m-d H:i:s'),
            );

            $collection_id = $this->incidental_fee_collection_model->add($insert_data);

            if ($collection_id) {
                $collection_id = (int) $collection_id;
                $response = array(
                    'status' => 'success',
                    'message' => $this->lang->line('fee_collected_successfully'),
                    'collection_id' => $collection_id,
                    'id' => $collection_id,
                    'receipt_url' => site_url('financereports/print_incidental_receipt/' . $collection_id),
                    'receipt_no' => $receipt_no,
                    'payment_mode' => $payment_mode,
                    'amount_collected' => $amount_collected
                );
                echo json_encode($response);
            } else {
                echo json_encode(array('status' => 'error', 'message' => $this->lang->line('error_collecting_fee')));
            }
        }
    }

    public function findApplicationByReference()
    {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            echo json_encode(array('status' => 'error', 'message' => $this->lang->line('access_denied')));
            return;
        }

        $reference_no = trim((string) $this->input->post('application_ref_no'));
        if ($reference_no === '') {
            echo json_encode(array('status' => 'error', 'message' => 'Application Reference No is required.'));
            return;
        }

        $online_admission = $this->getOnlineAdmissionByReference($reference_no);
        if (empty($online_admission)) {
            echo json_encode(array('status' => 'error', 'message' => 'No online application found for the given Application Reference No.'));
            return;
        }

        $due_summary = $this->getApplicationDueSummary($online_admission, $reference_no);

        echo json_encode(array(
            'status' => 'success',
            'application' => array(
                'id' => (int) $online_admission['id'],
                'reference_no' => $online_admission['reference_no'],
                'applicant_name' => $online_admission['applicant_name'],
                'mobileno' => $online_admission['mobileno'],
                'course_name' => $online_admission['course_name'],
                'course_fee_total' => $due_summary['course_fee_total'],
                'application_fee_amount' => $due_summary['application_fee_amount'],
                'total_payable_amount' => $due_summary['total_payable_amount'],
                'total_paid_so_far' => $due_summary['total_paid_so_far'],
                'remaining_fee' => $due_summary['remaining_fee'],
            ),
            'online_payment_history' => $due_summary['online_payment_history'],
            'incidental_payment_history' => $due_summary['incidental_payment_history'],
        ));
    }

    public function getRecentCollections() {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('access_denied')]);
            return;
        }
        $collections = $this->incidental_fee_collection_model->get_collections_report(array());
        $collections = array_values(array_filter($collections, function ($row) {
            return isset($row['incidental_fee_type_id']) && (int) $row['incidental_fee_type_id'] > 0;
        }));
        usort($collections, function ($a, $b) {
            return (int) ($b['id'] ?? 0) - (int) ($a['id'] ?? 0);
        });
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $collections]);
    }

    private function isApplicationRefRequiredForFeeType($fee_type_title)
    {
        $title = strtolower(trim((string) $fee_type_title));
        if ($title === '') {
            return false;
        }

        return (strpos($title, 'application fee') !== false)
            || (strpos($title, 'tuition fee') !== false)
            || (strpos($title, 'tution fee') !== false)
            || (strpos($title, 'other fee') !== false);
    }

    private function getOnlineAdmissionByReference($reference_no)
    {
        $normalized = preg_replace('/\s+/', '', (string) $reference_no);

        $this->db->select('online_admissions.id, online_admissions.reference_no, online_admissions.firstname, online_admissions.middlename, online_admissions.lastname, online_admissions.mobileno, online_admissions.quota_type, online_admissions.course_fee_total, online_admission_courses.course_name, online_admission_courses.govt_fee, online_admission_courses.mgt_fee');
        $this->db->from('online_admissions');
        $this->db->join('online_admission_courses', 'online_admission_courses.id = COALESCE(online_admissions.admission_course_id, online_admissions.ug_course_id)', 'left');
        $this->db->where('REPLACE(online_admissions.reference_no, " ", "") =', $normalized, false);
        $this->db->limit(1);
        $row = $this->db->get()->row_array();

        if (empty($row)) {
            return null;
        }

        $course_fee_total = isset($row['course_fee_total']) ? (float) $row['course_fee_total'] : 0;
        if ($course_fee_total <= 0) {
            $quota_type = strtolower(trim((string) ($row['quota_type'] ?? '')));
            if ($quota_type === 'management') {
                $course_fee_total = (float) ($row['mgt_fee'] ?? 0);
            } else {
                $course_fee_total = (float) ($row['govt_fee'] ?? 0);
            }
        }

        $applicant_name = trim(
            (string) ($row['firstname'] ?? '') . ' ' .
            (string) ($row['middlename'] ?? '') . ' ' .
            (string) ($row['lastname'] ?? '')
        );

        $row['course_fee_total'] = $course_fee_total;
        $row['applicant_name'] = $applicant_name;

        return $row;
    }

    private function getOnlineApplicationPaymentHistory($online_admission_id)
    {
        if ((int) $online_admission_id <= 0) {
            return array();
        }

        $this->db->select('id, paid_amount, payment_mode, payment_type, transaction_id, note, date');
        $this->db->from('online_admission_payment');
        $this->db->where('online_admission_id', (int) $online_admission_id);
        $this->db->order_by('date', 'ASC');
        return $this->db->get()->result_array();
    }

    private function getIncidentalFeeHistoryByReference($reference_no)
    {
        $normalized = preg_replace('/\s+/', '', (string) $reference_no);
        if ($normalized === '') {
            return array();
        }

        $this->db->select('incidental_fee_collections.id, incidental_fee_collections.amount_collected, incidental_fee_collections.bill_date, incidental_fee_collections.payment_mode, incidental_fee_types.title as fee_type_title');
        $this->db->from('incidental_fee_collections');
        $this->db->join('incidental_fee_types', 'incidental_fee_types.id = incidental_fee_collections.incidental_fee_type_id', 'left');
        $this->db->where('REPLACE(incidental_fee_collections.application_ref_no, " ", "") =', $normalized, false);
        $this->db->order_by('incidental_fee_collections.bill_date', 'ASC');
        $this->db->order_by('incidental_fee_collections.id', 'ASC');
        return $this->db->get()->result_array();
    }

    private function getConfiguredOnlineApplicationAmount()
    {
        $setting = $this->setting_model->getSetting();
        return isset($setting->online_admission_amount) ? (float) $setting->online_admission_amount : 0.0;
    }

    private function getApplicationDueSummary($online_admission, $reference_no)
    {
        $online_payment_history = $this->getOnlineApplicationPaymentHistory((int) ($online_admission['id'] ?? 0));
        $incidental_history = $this->getIncidentalFeeHistoryByReference($reference_no);

        $online_paid_total = 0.0;
        foreach ($online_payment_history as $row) {
            $online_paid_total += (float) ($row['paid_amount'] ?? 0);
        }

        $incidental_paid_total = 0.0;
        foreach ($incidental_history as $row) {
            $incidental_paid_total += (float) ($row['amount_collected'] ?? 0);
        }

        $course_fee_total = (float) ($online_admission['course_fee_total'] ?? 0);
        $application_fee_amount = $this->getConfiguredOnlineApplicationAmount();
        $total_payable_amount = $course_fee_total + $application_fee_amount;
        $total_paid_so_far = $online_paid_total + $incidental_paid_total;
        $remaining_fee = $total_payable_amount - $total_paid_so_far;
        if ($remaining_fee < 0) {
            $remaining_fee = 0.0;
        }

        return array(
            'online_payment_history' => $online_payment_history,
            'incidental_payment_history' => $incidental_history,
            'course_fee_total' => $course_fee_total,
            'application_fee_amount' => $application_fee_amount,
            'total_payable_amount' => $total_payable_amount,
            'total_paid_so_far' => $total_paid_so_far,
            'remaining_fee' => $remaining_fee,
        );
    }

    public function receipt($collection_id) {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            access_denied();
        }
        $data['collection'] = $this->incidental_fee_collection_model->get_collection_by_id($collection_id);
        $data['sch_setting'] = $this->setting_model->getSetting();
        $data['receipt_header'] = $this->setting_model->get_receiptheader();

        $this->load->view('financereports/incidental_fee_print', $data);
    }

    public function getSectionsByClass() {
        $class_id = $this->input->post('class_id');
        log_message('error', 'getSectionsByClass: Received class_id = ' . $class_id);
        $sections = $this->class_model->get_section($class_id);
        log_message('error', 'getSectionsByClass: Sections returned = ' . json_encode($sections));
        echo json_encode($sections);
    }

    public function getStudentsForClassWiseCollection()
    {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_view')) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $incidental_fee_type_id = $this->input->post('incidental_fee_type_id');

        if (empty($class_id) || empty($section_id) || empty($incidental_fee_type_id)) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('invalid_input')]);
            return;
        }

        $session_id = $this->setting_model->getCurrentSession();
        $students = $this->student_model->getStudentByClassSectionID($class_id, $section_id, null, $session_id);

        $student_list = [];
        if (!empty($students)) {
            foreach ($students as $student) {
                $student_list[] = [
                    'student_session_id' => $student['student_session_id'],
                    'student_id' => $student['id'],
                    'admission_no' => $student['admission_no'],
                    'firstname' => $student['firstname'],
                    'lastname' => $student['lastname'],
                ];
            }
        }

        echo json_encode(['status' => 'success', 'students' => $student_list]);
    }

    public function saveClassWiseIncidentalFees()
    {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_add')) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('access_denied')]);
            return;
        }

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $incidental_fee_type_id = $this->input->post('incidental_fee_type_id');
        $amounts = $this->input->post('amounts');

        if (empty($class_id) || empty($section_id) || empty($incidental_fee_type_id) || empty($amounts) || !is_array($amounts)) {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('invalid_input')]);
            return;
        }

        $session_id = $this->setting_model->getCurrentSession();
        $collected_by = $this->customlib->getStaffID();
        $saved_count = 0;

        foreach ($amounts as $student_session_id => $amount) {
            $amount = (float)$amount;
            if ($amount <= 0) {
                continue;
            }

            $student_row = $this->student_model->getByStudentSession($student_session_id);
            if (empty($student_row) || empty($student_row['id'])) {
                continue;
            }

            $receipt_no = $this->incidental_fee_collection_model->get_receipt_no();
            $insert_data = [
                'incidental_fee_type_id' => $incidental_fee_type_id,
                'incidental_fee_assignment_id' => null,
                'session_id' => $session_id,
                'student_id' => $student_row['id'],
                'amount_collected' => $amount,
                'collected_by' => $collected_by,
                'receipt_no' => $receipt_no,
                'notes' => null,
                'date_collected' => date('Y-m-d H:i:s'),
            ];

            $collection_id = $this->incidental_fee_collection_model->add($insert_data);
            if ($collection_id) {
                $saved_count++;
            }
        }

        if ($saved_count > 0) {
            echo json_encode(['status' => 'success', 'message' => $this->lang->line('fee_collected_successfully')]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $this->lang->line('no_record_found')]);
        }
    }

    public function revert($collection_id) {
        if (!$this->rbac->hasPrivilege('collect_incidental_fee', 'can_delete')) {
            access_denied();
        }

        if (!$collection_id) {
            redirect('financereports/incidental_fee_report');
        }

        $success = $this->incidental_fee_collection_model->revert($collection_id);

        if ($success) {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fee collection reverted successfully.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error reverting fee collection.</div>');
        }

        redirect('financereports/incidental_fee_report');
    }

}