<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_application
 *
 * Exam Events management:
 * - List all end-semester exam events for a session
 * - Mark an existing exam_group as end-semester (CoE)
 * - Generate student application rows in bulk
 * - View per-event application list
 */
class Coe_application extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // INDEX — list exam events for the active session
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_application');

        $session_id = $this->input->get('session_id') ?: $this->current_session;

        $data['title']            = $this->lang->line('coe_exam_events');
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = (int) $session_id;
        $data['events']           = $this->Coe_application_model->getExamEventsBySession($session_id);

        // All exam_groups available for linking (for the "mark as CoE" dropdown)
        $data['all_exam_groups']  = $this->db->where('is_active', 1)->get('exam_groups')->result();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_application/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // MARK an exam_group as end-semester CoE exam
    // ------------------------------------------------------------------
    public function mark_end_semester()
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('exam_group_id',  'Exam Group',      'trim|required|integer');
        $this->form_validation->set_rules('exam_category',  'Exam Category',   'trim|required|in_list[main,arrear,supplementary]');
        $this->form_validation->set_rules('exam_type',      'Exam Mode',       'trim|required|in_list[theory,practical,project,viva,online]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            redirect('coe/coe_application');
        }

        $exam_group_id = (int) $this->input->post('exam_group_id');
        $exam_category = $this->input->post('exam_category');
        $exam_type     = $this->input->post('exam_type');

        $this->Coe_application_model->markEndSemester($exam_group_id, $exam_category, $exam_type);
        $this->Coe_audit_model->log('exam_group_marked_coe', 'exam_groups', $exam_group_id, null, ['exam_category' => $exam_category, 'exam_type' => $exam_type]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Exam group marked as CoE end-semester exam.</div>');
        redirect('coe/coe_application');
    }

    // ------------------------------------------------------------------
    // SAVE SUBJECTS for a batch exam
    // ------------------------------------------------------------------
    public function save_subjects($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $batch = $this->db->where('id', $batch_exam_id)->get('exam_group_class_batch_exams')->row();
        if (!$batch) {
            show_404();
        }
        if ($batch->coe_locked) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Cannot modify subjects: exam is locked by CoE.</div>');
            redirect('coe/coe_application/view/' . $batch_exam_id);
        }

        $subject_ids = $this->input->post('subject_ids') ?: [];
        if (!is_array($subject_ids)) {
            $subject_ids = [];
        }
        $subject_ids = array_values(array_unique(array_filter(array_map('intval', $subject_ids))));

        $this->Coe_application_model->saveBatchSubjects($batch_exam_id, $subject_ids, $batch->date_from);
        $this->Coe_audit_model->log('batch_subjects_saved', 'exam_group_class_batch_exams', $batch_exam_id, null, ['subject_count' => count($subject_ids)]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left"><strong>' . count($subject_ids) . ' subjects</strong> configured for this batch exam.</div>');
        redirect('coe/coe_application/view/' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // GENERATE applications for a batch exam
    // ------------------------------------------------------------------
    public function generate($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        // Check: exam must not be coe_locked
        if ($event->coe_locked) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">Cannot generate applications: exam is locked by CoE.</div>');
            redirect('coe/coe_application');
        }

        // Check: regulation must allow arrear / supplementary exams
        if (in_array($event->exam_category, ['arrear', 'supplementary'], true)) {
            // class_id is stored directly on egcbe; fallback to student_session for pre-migration rows
            $class_id_for_reg = !empty($event->class_id) ? (int) $event->class_id : null;
            if (!$class_id_for_reg) {
                $class_row        = $this->db->query(
                    "SELECT DISTINCT ss.class_id FROM exam_group_class_batch_exam_students egcbes
                     JOIN student_session ss ON ss.id = egcbes.student_session_id
                     WHERE egcbes.exam_group_class_batch_exam_id = ? LIMIT 1",
                    [$batch_exam_id]
                )->row();
                $class_id_for_reg = $class_row ? (int) $class_row->class_id : null;
            }
            $regulation = $class_id_for_reg
                ? $this->Coe_setup_model->getByClassSession($class_id_for_reg, $event->session_id)
                : null;
            if (!empty($regulation)) {
                if ($event->exam_category === 'arrear' && empty($regulation->arrear_allowed)) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Arrear exams are not allowed for this class under the current exam regulation. Enable "Allow Arrear Exams" in <a href="' . site_url('coe/coe_setup') . '">Exam Regulations</a> first.</div>');
                    redirect('coe/coe_application/view/' . $batch_exam_id);
                }
                if ($event->exam_category === 'supplementary' && empty($regulation->supplementary_allowed)) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Supplementary exams are not allowed for this class under the current exam regulation. Enable "Allow Supplementary Exams" in <a href="' . site_url('coe/coe_setup') . '">Exam Regulations</a> first.</div>');
                    redirect('coe/coe_application/view/' . $batch_exam_id);
                }
            }
        }

        $result = $this->Coe_application_model->generateApplications($batch_exam_id, $event->exam_group_id);

        if (isset($result['error'])) {
            $msg_map = [
                'no_subjects' => 'No subjects assigned to this exam batch. Please assign exam subjects first.',
                'no_students' => 'No students found in this exam batch.',
            ];
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">' . ($msg_map[$result['error']] ?? 'Error generating applications.') . '</div>');
        } else {
            $this->Coe_audit_model->log('applications_generated', 'exam_group_class_batch_exams', $batch_exam_id, null, $result);
            $mode_label = ($result['mode'] ?? 'regular') === 'arrear'
                ? 'Arrear-smart mode: only students with active arrears in each subject were enrolled.'
                : 'Regular mode: all enrolled students added for all subjects.';
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">'
                . $this->lang->line('coe_applications_generated')
                . ' Inserted: ' . $result['inserted']
                . ', Skipped (duplicate): ' . $result['skipped']
                . '<br><small>' . $mode_label . '</small>'
                . '</div>');
        }

        redirect('coe/coe_application/view/' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // VIEW — applications for a specific batch exam
    // ------------------------------------------------------------------
    public function view($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_application');

        $filters = [
            'application_status' => $this->input->get('application_status'),
            'cbcs_category'      => $this->input->get('cbcs_category'),
        ];

        $is_arrear = in_array($event->exam_category, ['arrear', 'supplementary'], true);

        $data['title']          = $this->lang->line('coe_exam_events') . ' — ' . $event->exam;
        $data['event']          = $event;
        $data['applications']   = $this->Coe_application_model->getApplicationsByBatchExam($batch_exam_id, $filters);
        $data['stats']          = $this->Coe_application_model->getApplicationStats($batch_exam_id);
        $data['filters']        = $filters;
        $data['is_arrear']      = $is_arrear;

        // For arrear/supplementary: pass subject setup data and candidate preview
        if ($is_arrear) {
            $data['subjects_data'] = $this->Coe_application_model->getSubjectsWithArrears($batch_exam_id);
            $data['candidates']    = empty($data['subjects_data']->configured_ids)
                                     ? ['students' => [], 'total_pairs' => 0, 'subject_ids' => []]
                                     : $this->Coe_application_model->getArrearCandidates($batch_exam_id);
        } else {
            $data['subjects_data'] = null;
            $data['candidates']    = null;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_application/view', $data);
        $this->load->view('layout/footer', $data);
    }
}
