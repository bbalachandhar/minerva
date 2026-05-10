<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_revaluation
 * Two-stage revaluation — request intake, payment, evaluator assignment, marks entry.
 */
class Coe_revaluation extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_revaluation_model');
        $this->load->model('coe/Coe_application_model');
    }

    // ------------------------------------------------------------------
    // index() — Pick exam event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_view')) {
            access_denied();
        }

        $session_id         = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']     = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_revaluation/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // listing($batch_exam_id) — List all requests for an exam event
    // ------------------------------------------------------------------
    public function listing($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = [
            'batch_exam_id'  => $batch_exam_id,
            'status'         => $this->input->get('status'),
            'payment_status' => $this->input->get('payment_status'),
            'subject_id'     => $this->input->get('subject_id'),
        ];

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['requests']      = $this->Coe_revaluation_model->getAll($filters);
        $data['subjects']      = $this->Coe_revaluation_model->getSubjectsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_revaluation/listing', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // add($batch_exam_id) — Add request form
    // ------------------------------------------------------------------
    public function add($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_revaluation_model->getSubjectsByBatchExam($batch_exam_id);

        // Get students enrolled in this batch
        $data['students'] = $this->db
            ->select('st.id, CONCAT(st.firstname, " ", st.lastname) AS full_name, st.admission_no')
            ->from('students st')
            ->join('coe_hall_tickets ht', 'ht.student_id = st.id', 'inner')
            ->where('ht.exam_group_class_batch_exam_id', (int) $batch_exam_id)
            ->order_by('st.firstname ASC')
            ->get()->result();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_revaluation/add', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_request() — Save new revaluation request (AJAX)
    // ------------------------------------------------------------------
    public function save_request()
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $this->form_validation->set_rules('batch_exam_id',    'Exam Event',      'required|integer');
        $this->form_validation->set_rules('student_id',       'Student',         'required|integer');
        $this->form_validation->set_rules('subject_id',       'Subject',         'required|integer');
        $this->form_validation->set_rules('request_date',     'Request Date',    'required');
        $this->form_validation->set_rules('original_marks',   'Original Marks',  'required|numeric');
        $this->form_validation->set_rules('payment_amount',   'Payment Amount',  'required|numeric');

        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $row = [
            'student_id'                     => (int) $this->input->post('student_id'),
            'exam_group_class_batch_exam_id' => (int) $this->input->post('batch_exam_id'),
            'subject_id'                     => (int) $this->input->post('subject_id'),
            'original_marks'                 => (float) $this->input->post('original_marks'),
            'request_date'                   => $this->input->post('request_date'),
            'payment_status'                 => $this->input->post('payment_status') ?: 'pending',
            'payment_ref'                    => $this->input->post('payment_ref'),
            'payment_amount'                 => (float) $this->input->post('payment_amount'),
            'payment_date'                   => $this->input->post('payment_date') ?: null,
            'stage'                          => 1,
            'status'                         => 'pending',
            'remarks'                        => $this->input->post('remarks'),
            'created_by'                     => (int) $this->session->userdata('staff_id'),
        ];

        $id = $this->Coe_revaluation_model->insertRequest($row);
        $this->Coe_audit_model->log('create', 'coe_revaluation_requests', $id, null, $row);

        echo json_encode(['status' => 'success', 'msg' => 'Revaluation request created. ID: ' . $id]);
    }

    // ------------------------------------------------------------------
    // view($id) — View request + assignments
    // ------------------------------------------------------------------
    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_view')) {
            access_denied();
        }

        $request = $this->Coe_revaluation_model->getById($id);
        if (empty($request)) {
            show_404();
        }

        $data['request']     = $request;
        $data['assignments'] = $this->Coe_revaluation_model->getAssignments($id);
        $data['staff']       = $this->Coe_revaluation_model->getStaff();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_revaluation/view', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // update_payment($id) — Record payment (AJAX POST)
    // ------------------------------------------------------------------
    public function update_payment($id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $request = $this->Coe_revaluation_model->getById($id);
        if (empty($request)) {
            echo json_encode(['status' => 'error', 'msg' => 'Request not found']);
            return;
        }

        $allowed = ['pending', 'paid', 'waived'];
        $payment_status = $this->input->post('payment_status');
        if (!in_array($payment_status, $allowed)) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid payment status']);
            return;
        }

        $upd = [
            'payment_status' => $payment_status,
            'payment_ref'    => $this->input->post('payment_ref'),
            'payment_date'   => $this->input->post('payment_date') ?: null,
        ];

        $this->Coe_revaluation_model->updateRequest($id, $upd);
        $this->Coe_audit_model->log('payment_update', 'coe_revaluation_requests', $id,
            ['payment_status' => $request->payment_status], $upd);

        echo json_encode(['status' => 'success', 'msg' => 'Payment status updated.']);
    }

    // ------------------------------------------------------------------
    // assign($id) — Assign evaluator to a request (AJAX POST)
    // ------------------------------------------------------------------
    public function assign($id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $request = $this->Coe_revaluation_model->getById($id);
        if (empty($request) || $request->payment_status !== 'paid') {
            echo json_encode(['status' => 'error', 'msg' => 'Request not found or payment not confirmed']);
            return;
        }

        $this->form_validation->set_rules('evaluator_id', 'Evaluator', 'required|integer');
        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $asgn = [
            'revaluation_request_id' => $id,
            'assigned_evaluator'     => (int) $this->input->post('evaluator_id'),
            'assigned_by'            => (int) $this->session->userdata('staff_id'),
            'assigned_at'            => $now,
            'original_marks'         => $request->original_marks,
            'status'                 => 'assigned',
        ];

        $asgn_id = $this->Coe_revaluation_model->insertAssignment($asgn);

        $this->Coe_revaluation_model->updateRequest($id, [
            'status' => 'assigned',
            'stage'  => $request->stage,
        ]);

        $this->Coe_audit_model->log('assign_evaluator', 'coe_revaluation_requests', $id, null, $asgn);
        echo json_encode(['status' => 'success', 'msg' => 'Evaluator assigned. Assignment ID: ' . $asgn_id]);
    }

    // ------------------------------------------------------------------
    // save_evaluation($asgn_id) — Save revised marks (AJAX POST)
    // ------------------------------------------------------------------
    public function save_evaluation($asgn_id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $asgn = $this->Coe_revaluation_model->getAssignmentById($asgn_id);
        if (empty($asgn) || $asgn->status === 'completed') {
            echo json_encode(['status' => 'error', 'msg' => 'Assignment not found or already completed']);
            return;
        }

        $this->form_validation->set_rules('revised_marks', 'Revised Marks', 'required|numeric');
        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $revised = (float) $this->input->post('revised_marks');
        $now     = date('Y-m-d H:i:s');

        $this->Coe_revaluation_model->updateAssignment($asgn_id, [
            'revised_marks' => $revised,
            'remarks'       => $this->input->post('remarks'),
            'status'        => 'completed',
            'completed_at'  => $now,
        ]);

        // Update request status to completed
        $this->Coe_revaluation_model->updateRequest($asgn->revaluation_request_id, [
            'status' => 'completed',
        ]);

        $this->Coe_audit_model->log('save_evaluation', 'coe_revaluation_assignments', $asgn_id,
            ['revised_marks' => null], ['revised_marks' => $revised]);

        echo json_encode(['status' => 'success', 'msg' => 'Revised marks saved: ' . $revised]);
    }

    // ------------------------------------------------------------------
    // reject($id) — Reject a revaluation request (AJAX POST)
    // ------------------------------------------------------------------
    public function reject($id)
    {
        if (!$this->rbac->hasPrivilege('coe_revaluation', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $request = $this->Coe_revaluation_model->getById($id);
        if (empty($request)) {
            echo json_encode(['status' => 'error', 'msg' => 'Request not found']);
            return;
        }

        $this->Coe_revaluation_model->updateRequest($id, [
            'status'  => 'rejected',
            'remarks' => $this->input->post('remarks'),
        ]);

        $this->Coe_audit_model->log('reject', 'coe_revaluation_requests', $id,
            ['status' => $request->status], ['status' => 'rejected']);

        echo json_encode(['status' => 'success', 'msg' => 'Request rejected.']);
    }
}
