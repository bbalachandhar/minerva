<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Coe_dashboard
 * Central dashboard for the Controller of Examinations module.
 * Shows KPIs, per-event pipeline status, and recent audit log.
 */
class Coe_dashboard extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_dashboard_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_dashboard', 'can_view')) {
            access_denied();
        }

        $session_id = (int) ($this->input->get('session_id') ?: $this->current_session);

        $data['session_id']    = $session_id;
        $data['kpis']          = $this->Coe_dashboard_model->getKPIs($session_id);
        $data['events']        = $this->Coe_dashboard_model->getEventPipeline($session_id);
        $data['recent_audit']  = $this->Coe_dashboard_model->getRecentAudit(12);
        $data['pending_tasks'] = $this->Coe_dashboard_model->getPendingTasks($session_id);

        // Sessions dropdown
        $data['sessions'] = $this->db
            ->select('id, session')
            ->from('sessions')
            ->order_by('id', 'DESC')
            ->limit(10)
            ->get()->result();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_dashboard/index', $data);
        $this->load->view('layout/footer');
    }
}
