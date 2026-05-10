<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_setup
 *
 * Manages CoE Exam Regulations — per-programme (class) per session configuration:
 * regulation type (affiliated/autonomous), attendance threshold, marks weightage,
 * CBCS credit system, grading scheme, arrear/supplementary allowance.
 */
class Coe_setup extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // INDEX — list all regulations for the current session
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_setup', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_setup');

        $data['title']           = $this->lang->line('coe_exam_regulations');
        $data['session_list']    = $this->session_model->getAllSession();
        $data['current_session'] = $this->current_session;
        $data['class_list']      = $this->class_model->getAll();
        $data['department_list'] = $this->department_model->getDepartmentType();

        $session_id = $this->input->get('session_id') ?: $this->current_session;
        $data['selected_session']  = (int) $session_id;
        $data['regulations']       = $this->Coe_setup_model->getBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_setup/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // ADD — show add form
    // ------------------------------------------------------------------
    public function add()
    {
        if (!$this->rbac->hasPrivilege('coe_setup', 'can_add')) {
            access_denied();
        }
        $data['title']           = $this->lang->line('coe_add_regulation');
        $data['session_list']    = $this->session_model->getAllSession();
        $data['current_session'] = $this->current_session;
        $data['class_list']      = $this->class_model->getAll();
        $data['department_list'] = $this->department_model->getDepartmentType();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_setup/add', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // SAVE — insert or update regulation
    // ------------------------------------------------------------------
    public function save($id = null)
    {
        $is_edit = !empty($id);

        if ($is_edit) {
            if (!$this->rbac->hasPrivilege('coe_setup', 'can_edit')) {
                access_denied();
            }
        } else {
            if (!$this->rbac->hasPrivilege('coe_setup', 'can_add')) {
                access_denied();
            }
        }

        $this->form_validation->set_rules('session_id',          $this->lang->line('session'),              'trim|required|integer');
        $this->form_validation->set_rules('class_id',            $this->lang->line('class'),                'trim|required|integer');
        $this->form_validation->set_rules('regulation_type',     $this->lang->line('coe_regulation_type'),  'trim|required|in_list[affiliated,autonomous]');
        $this->form_validation->set_rules('min_attendance_pct',  $this->lang->line('coe_min_attendance_pct'), 'trim|required|decimal');
        $this->form_validation->set_rules('internal_marks_pct',  $this->lang->line('coe_internal_marks_pct'), 'trim|required|decimal');
        $this->form_validation->set_rules('external_marks_pct',  $this->lang->line('coe_external_marks_pct'), 'trim|required|decimal');
        $this->form_validation->set_rules('pass_marks_pct',      $this->lang->line('coe_pass_marks_pct'),    'trim|required|decimal');
        $this->form_validation->set_rules('grading_scheme',      $this->lang->line('coe_grading_scheme'),    'trim|required|in_list[ten_point,seven_point,percentage]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . validation_errors() . '</div>');
            redirect($is_edit ? 'coe/coe_setup/edit/' . $id : 'coe/coe_setup/add');
        }

        // Validate internal + external = 100
        $internal = (float) $this->input->post('internal_marks_pct');
        $external = (float) $this->input->post('external_marks_pct');
        if (abs(($internal + $external) - 100.0) > 0.01) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Internal + External marks must sum to 100%.</div>');
            redirect($is_edit ? 'coe/coe_setup/edit/' . $id : 'coe/coe_setup/add');
        }

        $data = [
            'session_id'            => (int) $this->input->post('session_id'),
            'class_id'              => (int) $this->input->post('class_id'),
            'department_id'         => $this->input->post('department_id') ?: null,
            'regulation_type'       => $this->input->post('regulation_type'),
            'affiliated_university' => $this->input->post('affiliated_university') ?: 'Anna University',
            'min_attendance_pct'    => $internal !== 0 ? (float) $this->input->post('min_attendance_pct') : 75.00,
            'internal_marks_pct'    => $internal,
            'external_marks_pct'    => $external,
            'pass_marks_pct'        => (float) $this->input->post('pass_marks_pct'),
            'has_credit_system'     => $this->input->post('has_credit_system') ? 1 : 0,
            'grading_scheme'        => $this->input->post('grading_scheme'),
            'arrear_allowed'        => $this->input->post('arrear_allowed') ? 1 : 0,
            'supplementary_allowed' => $this->input->post('supplementary_allowed') ? 1 : 0,
            'check_fee_dues'        => $this->input->post('check_fee_dues') ? 1 : 0,
            'is_active'             => 1,
            'created_by'            => $this->customlib->getStaffID(),
        ];

        if ($is_edit) {
            $this->Coe_setup_model->update($id, $data);
            $this->Coe_audit_model->log('regulation_updated', 'coe_exam_regulations', $id, null, $data);
        } else {
            $inserted_id = $this->Coe_setup_model->insert($data);
            $this->Coe_audit_model->log('regulation_created', 'coe_exam_regulations', $inserted_id, null, $data);
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('coe_regulation_saved') . '</div>');
        redirect('coe/coe_setup');
    }

    // ------------------------------------------------------------------
    // EDIT — show edit form
    // ------------------------------------------------------------------
    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('coe_setup', 'can_edit')) {
            access_denied();
        }
        $regulation = $this->Coe_setup_model->getById($id);
        if (empty($regulation)) {
            show_404();
        }

        $data['title']           = $this->lang->line('coe_edit_regulation');
        $data['regulation']      = $regulation;
        $data['session_list']    = $this->session_model->getAllSession();
        $data['current_session'] = $this->current_session;
        $data['class_list']      = $this->class_model->getAll();
        $data['department_list'] = $this->department_model->getDepartmentType();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_setup/edit', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    // DELETE
    // ------------------------------------------------------------------
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('coe_setup', 'can_delete')) {
            access_denied();
        }
        $this->Coe_setup_model->delete($id);
        $this->Coe_audit_model->log('regulation_deleted', 'coe_exam_regulations', $id, null, null);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
        redirect('coe/coe_setup');
    }

    // ------------------------------------------------------------------
    // AJAX: get regulation for a class+session (used by other CoE views)
    // ------------------------------------------------------------------
    public function get_regulation()
    {
        if (!$this->rbac->hasPrivilege('coe_setup', 'can_view')) {
            echo json_encode(['status' => 'error']); return;
        }
        $session_id = (int) $this->input->get('session_id');
        $class_id   = (int) $this->input->get('class_id');
        $reg = $this->Coe_setup_model->getByClassSession($class_id, $session_id);
        echo json_encode(['status' => 'success', 'data' => $reg]);
    }
}
