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
        $data['eligibility_run_at'] = null;

        if ($batch_exam_id) {
            $data['summary']         = $this->Coe_eligibility_model->getSummary($batch_exam_id);
            $data['ineligible_list'] = $this->Coe_eligibility_model->getIneligibleStudents($batch_exam_id);
            $data['event_detail']    = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
            $egcbe_row               = $this->db->where('id', $batch_exam_id)->get('exam_group_class_batch_exams')->row();
            $data['eligibility_run_at'] = $egcbe_row->eligibility_run_at ?? null;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_eligibility/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // RUN eligibility engine for a batch exam — POST only
    // ------------------------------------------------------------------
    public function run()
    {
        if (!$this->rbac->hasPrivilege('coe_eligibility', 'can_add')) {
            access_denied();
        }

        // Reject non-POST requests to prevent CSRF via GET
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
        }

        $batch_exam_id = (int) $this->input->post('batch_exam_id');
        if (!$batch_exam_id) {
            show_404();
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
            // Stamp last-run time
            $this->db->where('id', $batch_exam_id)->update('exam_group_class_batch_exams', [
                'eligibility_run_at' => date('Y-m-d H:i:s'),
            ]);
            $this->Coe_audit_model->log('eligibility_run', 'exam_group_class_batch_exams', $batch_exam_id, null, $result);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('coe_eligibility_processed') . ' Processed: ' . $result['processed'] . ' | Eligible: ' . $result['eligible'] . ' | Ineligible: ' . $result['ineligible'] . '</div>');
        }

        redirect('coe/coe_eligibility?batch_exam_id=' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // RUN ALL — run eligibility for every batch exam in an exam group/event
    // POST only. Accepts exam_group_id + session_id.
    // ------------------------------------------------------------------
    public function run_all()
    {
        if (!$this->rbac->hasPrivilege('coe_eligibility', 'can_add')) {
            access_denied();
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
        }

        $exam_group_id = (int) $this->input->post('exam_group_id');
        $session_id    = (int) $this->input->post('session_id');

        if (!$exam_group_id || !$session_id) {
            show_404();
        }

        $batches = $this->db
            ->where('exam_group_id', $exam_group_id)
            ->where('session_id', $session_id)
            ->get('exam_group_class_batch_exams')->result();

        if (empty($batches)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">No batches found for this event/session.</div>');
            redirect('coe/coe_eligibility?session_id=' . $session_id);
        }

        $total_processed = 0;
        $total_eligible  = 0;
        $total_inelig    = 0;
        $skipped         = 0;

        foreach ($batches as $egcbe) {
            if ($egcbe->coe_locked) {
                $skipped++;
                continue;
            }

            $class_id_val = !empty($egcbe->class_id) ? (int) $egcbe->class_id : null;
            if (!$class_id_val) {
                $class_row    = $this->db->query(
                    "SELECT DISTINCT ss.class_id FROM exam_group_class_batch_exam_students egcbes
                     JOIN student_session ss ON ss.id = egcbes.student_session_id
                     WHERE egcbes.exam_group_class_batch_exam_id = ? LIMIT 1",
                    [$egcbe->id]
                )->row();
                $class_id_val = $class_row ? (int) $class_row->class_id : null;
            }

            $regulation = $class_id_val
                ? $this->Coe_setup_model->getByClassSession($class_id_val, $egcbe->session_id)
                : null;

            if (empty($regulation)) {
                $skipped++;
                continue;
            }

            $regulation->session_id = $egcbe->session_id;
            $result = $this->Coe_eligibility_model->runEligibility($egcbe->id, $regulation);

            if (!isset($result['error'])) {
                $this->db->where('id', $egcbe->id)->update('exam_group_class_batch_exams', [
                    'eligibility_run_at' => date('Y-m-d H:i:s'),
                ]);
                $total_processed += $result['processed'];
                $total_eligible  += $result['eligible'];
                $total_inelig    += $result['ineligible'];
                $this->Coe_audit_model->log('eligibility_run_all', 'exam_group_class_batch_exams', $egcbe->id, null, $result);
            }
        }

        $msg = 'Run All complete. Processed: ' . $total_processed . ' | Eligible: ' . $total_eligible . ' | Ineligible: ' . $total_inelig;
        if ($skipped) {
            $msg .= ' | Skipped (locked or no regulation): ' . $skipped;
        }
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $msg . '</div>');
        redirect('coe/coe_eligibility?session_id=' . $session_id);
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
