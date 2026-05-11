<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_eligibility
 *
 * Runs eligibility engine (attendance + fee dues), shows results,
 * and provides override functionality for ineligible students.
 */
class Coe_eligibility extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // INDEX — show eligibility summary per batch exam, selectable by event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_eligibility', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_eligibility');

        $session_id = $this->input->get('session_id') ?: $this->current_session;
        $batch_exam_id = (int) $this->input->get('batch_exam_id');

        $data['title']            = $this->lang->line('coe_eligibility');
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = (int) $session_id;
        $data['events']           = $this->Coe_application_model->getExamEventsBySession($session_id);
        $data['selected_event']   = $batch_exam_id;
        $data['summary']          = null;
        $data['ineligible_list']  = [];

        if ($batch_exam_id) {
            $data['summary']         = $this->Coe_eligibility_model->getSummary($batch_exam_id);
            $data['ineligible_list'] = $this->Coe_eligibility_model->getIneligibleStudents($batch_exam_id);
            $data['event_detail']    = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_eligibility/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // RUN eligibility engine for a batch exam
    // ------------------------------------------------------------------
    public function run($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_eligibility', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        if ($event->coe_locked) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">Exam is locked. Cannot re-run eligibility.</div>');
            redirect('coe/coe_eligibility?batch_exam_id=' . $batch_exam_id);
        }

        // class_id is now stored directly on egcbe
        $egcbe        = $this->db->where('id', $batch_exam_id)->get('exam_group_class_batch_exams')->row();
        $class_id_val = !empty($egcbe->class_id) ? (int) $egcbe->class_id : null;

        // Fallback for records created before class_id column was added
        if (!$class_id_val) {
            $class_row    = $this->db->query(
                "SELECT DISTINCT ss.class_id FROM exam_group_class_batch_exam_students egcbes
                 JOIN student_session ss ON ss.id = egcbes.student_session_id
                 WHERE egcbes.exam_group_class_batch_exam_id = ? LIMIT 1",
                [$batch_exam_id]
            )->row();
            $class_id_val = $class_row ? (int) $class_row->class_id : null;
        }

        $regulation = $class_id_val
            ? $this->Coe_setup_model->getByClassSession($class_id_val, $egcbe->session_id)
            : null;

        if (empty($regulation)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No CoE exam regulation found for this class/session. Please create one in <a href="' . site_url('coe/coe_setup') . '">Exam Regulations</a> first.</div>');
            redirect('coe/coe_eligibility?batch_exam_id=' . $batch_exam_id);
        }

        // Augment regulation object with session_id
        $regulation->session_id = $egcbe->session_id;

        $result = $this->Coe_eligibility_model->runEligibility($batch_exam_id, $regulation);

        if (isset($result['error'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">No applications to process. Please generate applications first.</div>');
        } else {
            $this->Coe_audit_model->log('eligibility_run', 'exam_group_class_batch_exams', $batch_exam_id, null, $result);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('coe_eligibility_processed') . ' Processed: ' . $result['processed'] . ' | Eligible: ' . $result['eligible'] . ' | Ineligible: ' . $result['ineligible'] . '</div>');
        }

        redirect('coe/coe_eligibility?batch_exam_id=' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // OVERRIDE eligibility for a single ineligible student
    // ------------------------------------------------------------------
    public function override()
    {
        if (!$this->rbac->hasPrivilege('coe_override', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('application_id', 'Application', 'trim|required|integer');
        $this->form_validation->set_rules('override_reason', $this->lang->line('coe_override_reason'), 'trim|required|max_length[500]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            redirect($this->input->server('HTTP_REFERER'));
        }

        $application_id = (int) $this->input->post('application_id');
        $reason         = $this->input->post('override_reason');
        $batch_exam_id  = (int) $this->input->post('batch_exam_id');

        $this->Coe_eligibility_model->overrideEligibility($application_id, $reason, $this->customlib->getStaffID());
        $this->Coe_audit_model->log('eligibility_override', 'coe_exam_applications', $application_id, null, ['reason' => $reason]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('coe_eligibility_processed') . '</div>');
        redirect('coe/coe_eligibility?batch_exam_id=' . $batch_exam_id);
    }
}
