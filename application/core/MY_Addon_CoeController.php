<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * MY_Addon_CoeController
 *
 * Base controller for all CoE (Controller of Examinations) module controllers.
 * Extends Admin_Controller — inherits all Minerva models, libraries, rbac, etc.
 * Enforces: college institution type only, CoE module active, URL segment check.
 */
class MY_Addon_CoeController extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->config('coe_config');

        // CoE is college-only
        $sch = $this->setting_model->getSetting();
        if ($sch->institution_type !== 'college') {
            redirect('admin/unauthorized');
        }

        // CoE module must be active in permission_group
        if (!$this->module_lib->hasModule('coe')) {
            redirect('admin/unauthorized');
        }

        // Only allow requests routed through the coe/ subdirectory
        if ($this->uri->segment(1) !== 'coe') {
            redirect('admin/unauthorized');
        }

        // Load all CoE models
        $this->load->model([
            'coe/Coe_setup_model',
            'coe/Coe_application_model',
            'coe/Coe_eligibility_model',
            'coe/Coe_hallticket_model',
            'coe/Coe_nominalroll_model',
            'coe/Coe_seating_model',
            'coe/Coe_invigilation_model',
            'coe/Coe_audit_model',
            'coe/Coe_qpd_model',
            'coe/Coe_attendance_model',
            'coe/Coe_ufm_model',
        ]);

        // Convenience shortcuts
        $this->current_session    = $this->setting_model->getCurrentSession();
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    // ------------------------------------------------------------------
    // PREREQUISITE GUARD HELPERS
    // Call these in controller actions to give contextual warnings when
    // a required earlier step is not yet complete.
    //
    // Each returns an array with keys: ['ok' => bool, 'msg' => string]
    // On failure, sets flashdata and redirects — so call like:
    //   $this->_require_regulation($batch_exam_id);  // redirects if missing
    // ------------------------------------------------------------------

    /**
     * Guard: a CoE exam regulation must exist for the batch's class+session.
     * Redirects to coe_setup if missing.
     */
    protected function _require_regulation($batch_exam_id)
    {
        $egcbe = $this->db->where('id', (int)$batch_exam_id)
            ->get('exam_group_class_batch_exams')->row();
        if (!$egcbe) {
            show_404();
        }

        $regulation = ($egcbe->class_id)
            ? $this->Coe_setup_model->getByClassSession($egcbe->class_id, $egcbe->session_id)
            : null;

        if (empty($regulation)) {
            $this->session->set_flashdata('msg',
                '<div class="alert alert-danger text-left">'
                . '<strong><i class="fa fa-exclamation-triangle"></i> Missing Prerequisite:</strong> '
                . 'No exam regulation found for this class/session. '
                . '<a href="' . site_url('coe/coe_setup') . '">Create a regulation</a> first.'
                . '</div>'
            );
            redirect('coe/coe_eligibility?batch_exam_id=' . (int)$batch_exam_id);
        }

        return $regulation;
    }

    /**
     * Guard: at least one application must exist for the batch.
     * Redirects back with a warning if none.
     */
    protected function _require_applications($batch_exam_id)
    {
        $count = $this->db
            ->where('exam_group_class_batch_exam_id', (int)$batch_exam_id)
            ->count_all_results('coe_exam_applications');

        if ($count === 0) {
            $this->session->set_flashdata('msg',
                '<div class="alert alert-warning text-left">'
                . '<strong><i class="fa fa-exclamation-triangle"></i> Missing Prerequisite:</strong> '
                . 'No applications generated for this batch yet. '
                . '<a href="' . site_url('coe/coe_application/view/' . (int)$batch_exam_id) . '">Generate applications</a> first.'
                . '</div>'
            );
            return false;
        }

        return true;
    }

    /**
     * Guard: eligibility engine must have been run at least once for the batch.
     * Returns false (with flashdata) if eligibility_run_at is NULL.
     */
    protected function _require_eligibility_run($batch_exam_id)
    {
        $egcbe = $this->db
            ->select('eligibility_run_at')
            ->where('id', (int)$batch_exam_id)
            ->get('exam_group_class_batch_exams')->row();

        if (!$egcbe || empty($egcbe->eligibility_run_at)) {
            $this->session->set_flashdata('msg',
                '<div class="alert alert-warning text-left">'
                . '<strong><i class="fa fa-exclamation-triangle"></i> Missing Prerequisite:</strong> '
                . 'Eligibility has not been run for this batch yet. '
                . '<a href="' . site_url('coe/coe_eligibility?batch_exam_id=' . (int)$batch_exam_id) . '">Run Eligibility</a> first.'
                . '</div>'
            );
            return false;
        }

        return true;
    }
}
