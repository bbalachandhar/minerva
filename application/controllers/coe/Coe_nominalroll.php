<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Coe_nominalroll extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // INDEX — list CoE events with nominal roll generation status
    // =========================================================================
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_nominalroll', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_nominalroll');

        $selected_session = (int)($this->input->get('session_id') ?: $this->current_session);
        $events           = $this->Coe_nominalroll_model->getCoeEvents($selected_session);
        $summaries        = [];
        foreach ($events as $ev) {
            $summaries[$ev->id] = $this->Coe_nominalroll_model->getSummary($ev->id);
        }

        $sessions = $this->db->order_by('id', 'DESC')->get('sessions')->result_array();

        $data = [
            'title'       => lang('coe_nominalroll'),
            'events'           => $events,
            'summaries'        => $summaries,
            'sessions'         => $sessions,
            'selected_session' => $selected_session,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_nominalroll/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // GENERATE — generate/refresh nominal rolls for all subjects in a batch exam
    // =========================================================================
    public function generate($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_nominalroll', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $staff_id      = (int)$this->session->userdata('staff_id');
        $result        = $this->Coe_nominalroll_model->generate($batch_exam_id, $staff_id);

        $this->Coe_audit_model->log(
            'nominal_rolls_generated',
            'coe_nominal_rolls',
            $batch_exam_id,
            null,
            $result
        );

        if (isset($result['error'])) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $result['error'] . '</div>');
        } else {
            $this->session->set_flashdata('msg',
                '<div class="alert alert-success">Nominal rolls: <strong>' . $result['created'] . '</strong> created, <strong>' . $result['updated'] . '</strong> updated. Finalized rolls were not changed.</div>'
            );
        }

        redirect('coe/coe_nominalroll/view/' . $batch_exam_id);
    }

    // =========================================================================
    // VIEW — list all nominal rolls (per subject) for a batch exam
    // =========================================================================
    public function view($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_nominalroll', 'can_view')) {
            access_denied();
        }

        $batch_exam_id = (int)$batch_exam_id;
        $batch_exam    = $this->db->get_where('exam_group_class_batch_exams', ['id' => $batch_exam_id])->row();
        if (!$batch_exam) {
            show_404();
        }

        $rolls   = $this->Coe_nominalroll_model->getByBatchExam($batch_exam_id);
        $summary = $this->Coe_nominalroll_model->getSummary($batch_exam_id);

        $data = [
            'title'  => lang('coe_nominalroll'),
            'batch_exam'  => $batch_exam,
            'rolls'       => $rolls,
            'summary'     => $summary,
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_nominalroll/view', $data);
        $this->load->view('layout/footer', $data);
    }

    // =========================================================================
    // PRINT_PDF — print nominal roll PDF for one subject
    // =========================================================================
    public function print_pdf($roll_id)
    {
        if (!$this->rbac->hasPrivilege('coe_nominalroll', 'can_view')) {
            access_denied();
        }

        $roll_id = (int)$roll_id;
        $roll    = $this->Coe_nominalroll_model->getById($roll_id);
        if (!$roll) {
            show_404();
        }

        $students = json_decode($roll->roll_snapshot ?: '[]', true);

        $sch_setting     = $this->sch_setting_detail;
        $logo_filename   = $sch_setting->admission_logo_left ?? '';
        $logo_path       = null;
        if ($logo_filename) {
            $full = FCPATH . 'uploads/logos/' . $logo_filename;
            if (is_file($full)) {
                $mime      = mime_content_type($full) ?: 'image/png';
                $logo_path = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
            }
        }

        $html = $this->load->view('admin/coe/coe_nominalroll/print', [
            'roll'        => $roll,
            'students'    => $students,
            'logo_path'   => $logo_path,
            'sch_setting' => $sch_setting,
        ], true);

        $this->load->library('m_pdf');
        $mpdf = $this->m_pdf->load([
            'format'        => 'A4',
            'margin_left'   => 15,
            'margin_right'  => 15,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);
        $mpdf->WriteHTML($html, 0);
        $mpdf->Output('NominalRoll_' . preg_replace('/[^A-Za-z0-9_]/', '_', $roll->subject_name) . '.pdf', 'I');
    }

    // =========================================================================
    // FINALIZE — lock a nominal roll
    // =========================================================================
    public function finalize($roll_id)
    {
        if (!$this->rbac->hasPrivilege('coe_nominalroll', 'can_edit')) {
            access_denied();
        }

        $roll_id = (int)$roll_id;
        $roll    = $this->Coe_nominalroll_model->getById($roll_id);
        if (!$roll) {
            show_404();
        }

        $this->Coe_nominalroll_model->finalize($roll_id);
        $this->Coe_audit_model->log(
            'nominal_roll_finalized',
            'coe_nominal_rolls',
            $roll_id,
            ['is_final' => 0],
            ['is_final' => 1]
        );

        $this->session->set_flashdata('msg',
            '<div class="alert alert-success">Nominal roll for <strong>' . htmlspecialchars($roll->subject_name) . '</strong> has been finalized and locked.</div>'
        );
        redirect('coe/coe_nominalroll/view/' . $roll->exam_group_class_batch_exam_id);
    }
}
