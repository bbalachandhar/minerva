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
        $data['department_list'] = $this->department_model->getDepartmentType();
        // Classes grouped by department for multi-select UI
        $data['class_list_grouped'] = $this->db
            ->select('c.id, c.class, d.department_name')
            ->from('classes c')
            ->join('department d', 'd.id = c.department_id', 'left')
            ->where('c.class_type', 'academic')
            ->order_by('d.department_name ASC, c.class ASC')
            ->get()->result_array();

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

        $base_data = [
            'session_id'            => (int) $this->input->post('session_id'),
            'regulation_type'       => $this->input->post('regulation_type'),
            'affiliated_university' => $this->input->post('affiliated_university') ?: 'Anna University',
            'min_attendance_pct'    => (float) $this->input->post('min_attendance_pct'),
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
            // Edit always operates on a single class_id
            $data = $base_data;
            $data['class_id']      = (int) $this->input->post('class_id');
            $data['department_id'] = $this->input->post('department_id') ?: null;
            $this->Coe_setup_model->update($id, $data);
            $this->Coe_audit_model->log('regulation_updated', 'coe_exam_regulations', $id, null, $data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('coe_regulation_saved') . '</div>');
        } else {
            // Add supports multiple classes
            $class_ids = $this->input->post('class_id');
            if (empty($class_ids) || !is_array($class_ids)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please select at least one class.</div>');
                redirect('coe/coe_setup/add');
            }

            $saved = 0;
            $skipped = 0;
            foreach ($class_ids as $class_id) {
                $class_id = (int) $class_id;
                if ($class_id <= 0) { continue; }
                // Skip if regulation already exists for this class+session
                $existing = $this->Coe_setup_model->getByClassSession($class_id, $base_data['session_id']);
                if ($existing) { $skipped++; continue; }

                // Derive department_id from class
                $class_row = $this->db->select('department_id')->where('id', $class_id)->get('classes')->row();
                $data = $base_data;
                $data['class_id']      = $class_id;
                $data['department_id'] = $class_row ? $class_row->department_id : null;

                $inserted_id = $this->Coe_setup_model->insert($data);
                $this->Coe_audit_model->log('regulation_created', 'coe_exam_regulations', $inserted_id, null, $data);
                $saved++;
            }

            $msg = '<div class="alert alert-success text-left">'.$saved.' regulation(s) saved.';
            if ($skipped > 0) {
                $msg .= ' '.$skipped.' class(es) skipped — regulation already exists for this session.';
            }
            $msg .= '</div>';
            $this->session->set_flashdata('msg', $msg);
        }

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

    // ------------------------------------------------------------------
    // CLONE SESSION — copy all regulations from one session to another
    // POST only. Skips classes that already have a regulation in the
    // target session. Preserves all settings, updates session_id only.
    // ------------------------------------------------------------------
    public function clone_session()
    {
        if (!$this->rbac->hasPrivilege('coe_setup', 'can_add')) {
            access_denied();
        }

        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
        }

        $from_session_id = (int) $this->input->post('from_session_id');
        $to_session_id   = (int) $this->input->post('to_session_id');

        if (!$from_session_id || !$to_session_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please select both source and target sessions.</div>');
            redirect('coe/coe_setup');
        }

        if ($from_session_id === $to_session_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">Source and target sessions cannot be the same.</div>');
            redirect('coe/coe_setup');
        }

        $source_regs = $this->Coe_setup_model->getBySession($from_session_id);

        if (empty($source_regs)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">No regulations found in the source session to clone.</div>');
            redirect('coe/coe_setup');
        }

        $cloned  = 0;
        $skipped = 0;

        foreach ($source_regs as $reg) {
            // Skip if regulation already exists for this class in target session
            $existing = $this->Coe_setup_model->getByClassSession($reg->class_id, $to_session_id);
            if ($existing) {
                $skipped++;
                continue;
            }

            $new_reg = [
                'session_id'            => $to_session_id,
                'class_id'              => $reg->class_id,
                'department_id'         => $reg->department_id,
                'regulation_type'       => $reg->regulation_type,
                'affiliated_university' => $reg->affiliated_university,
                'min_attendance_pct'    => $reg->min_attendance_pct,
                'internal_marks_pct'    => $reg->internal_marks_pct,
                'external_marks_pct'    => $reg->external_marks_pct,
                'pass_marks_pct'        => $reg->pass_marks_pct,
                'has_credit_system'     => $reg->has_credit_system,
                'grading_scheme'        => $reg->grading_scheme,
                'arrear_allowed'        => $reg->arrear_allowed,
                'supplementary_allowed' => $reg->supplementary_allowed,
                'check_fee_dues'        => $reg->check_fee_dues,
                'is_active'             => 1,
                'created_by'            => $this->customlib->getStaffID(),
            ];

            $inserted_id = $this->Coe_setup_model->insert($new_reg);
            $this->Coe_audit_model->log('regulation_cloned', 'coe_exam_regulations', $inserted_id, null, [
                'from_session' => $from_session_id,
                'to_session'   => $to_session_id,
            ]);
            $cloned++;
        }

        $msg = '<div class="alert alert-success text-left">Clone complete. ' . $cloned . ' regulation(s) copied to target session.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' skipped (already exist in target session).';
        }
        $msg .= '</div>';
        $this->session->set_flashdata('msg', $msg);
        redirect('coe/coe_setup?session_id=' . $to_session_id);
    }
}
