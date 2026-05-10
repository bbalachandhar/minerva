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
}
