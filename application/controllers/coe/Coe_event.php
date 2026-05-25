<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_event
 *
 * CoE-isolated CRUD for exam groups (events) and their per-class batch exams.
 * Replaces the dependency on the Examination module for creating exam events.
 *
 * Hierarchy:
 *   Exam Group  (exam_groups, is_end_semester=1)
 *     └─ Batch Exam  (exam_group_class_batch_exams)   — one per class/session
 *          └─ Students auto-enrolled from student_session on batch creation
 */
class Coe_event extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // EXAM GROUP — index / add / edit / delete
    // =========================================================================

    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_event');

        $selected_session = (int)($this->input->get('session_id') ?: $this->current_session);

        $data['title']            = 'CoE Exam Events';
        $data['session_list']     = $this->session_model->getAllSession();
        $data['selected_session'] = $selected_session;

        // Events for selected session, with per-event progress counters
        $data['events'] = $this->db->query("
            SELECT eg.id, eg.name, eg.exam_type, eg.exam_category, eg.description, eg.is_active,
                   COUNT(DISTINCT egcbe.id)                                                   AS batch_count,
                   MIN(egcbe.date_from)                                                       AS earliest_date,
                   MAX(egcbe.date_to)                                                         AS latest_date,
                   -- Subjects: batches with at least 1 active subject
                   SUM(CASE WHEN (
                     SELECT COUNT(*) FROM exam_group_class_batch_exam_subjects
                     WHERE exam_group_class_batch_exams_id = egcbe.id AND is_active = 1
                   ) > 0 THEN 1 ELSE 0 END)                                                  AS batches_with_subjects,
                   -- Applications: batches with at least 1 application
                   SUM(CASE WHEN (
                     SELECT COUNT(*) FROM coe_exam_applications
                     WHERE exam_group_class_batch_exam_id = egcbe.id
                   ) > 0 THEN 1 ELSE 0 END)                                                  AS batches_with_apps,
                   -- Eligibility: batches where eligibility has been run (any non-pending status)
                   SUM(CASE WHEN (
                     SELECT COUNT(*) FROM coe_exam_applications
                     WHERE exam_group_class_batch_exam_id = egcbe.id
                       AND application_status != 'pending'
                   ) > 0 THEN 1 ELSE 0 END)                                                  AS batches_with_eligibility,
                   -- Hall tickets: batches with at least 1 hall ticket
                   SUM(CASE WHEN (
                     SELECT COUNT(*) FROM coe_hall_tickets
                     WHERE exam_group_class_batch_exam_id = egcbe.id
                   ) > 0 THEN 1 ELSE 0 END)                                                  AS batches_with_halltickets
            FROM exam_groups eg
            LEFT JOIN exam_group_class_batch_exams egcbe ON egcbe.exam_group_id = eg.id
            WHERE eg.is_end_semester = 1
              AND eg.session_id = ?
            GROUP BY eg.id
            ORDER BY eg.exam_category ASC, eg.id DESC
        ", [$selected_session])->result();

        // Stats for header cards
        $data['stats'] = [
            'total'         => count($data['events']),
            'main'          => count(array_filter($data['events'], fn($e) => $e->exam_category === 'main')),
            'arrear'        => count(array_filter($data['events'], fn($e) => $e->exam_category === 'arrear')),
            'supplementary' => count(array_filter($data['events'], fn($e) => $e->exam_category === 'supplementary')),
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_event/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    public function add()
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_add')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_event');

        $data['title']            = 'Create Exam Event';
        $data['session_list']     = $this->session_model->getAllSession();
        $data['current_session']  = $this->current_session;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_event/add', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    public function save()
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('name',          'Event Name', 'trim|required|max_length[250]');
        $this->form_validation->set_rules('session_id',    'Session',    'trim|required|integer');
        $this->form_validation->set_rules('exam_category', 'Category',   'trim|required|in_list[main,arrear,supplementary]');
        $this->form_validation->set_rules('exam_type',     'Mode',       'trim|required|in_list[theory,practical,project,viva,online]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('coe/coe_event/add');
        }

        $this->db->insert('exam_groups', [
            'name'            => $this->input->post('name'),
            'session_id'      => (int)$this->input->post('session_id'),
            'exam_category'   => $this->input->post('exam_category'),
            'exam_type'       => $this->input->post('exam_type'),
            'description'     => $this->input->post('description'),
            'is_end_semester' => 1,
            'is_active'       => 1,
        ]);
        $group_id = $this->db->insert_id();

        $this->Coe_audit_model->log('coe_event_created', 'exam_groups', $group_id, null, [
            'name'          => $this->input->post('name'),
            'exam_category' => $this->input->post('exam_category'),
        ]);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Exam event created. Add class batch exams below.</div>');
        redirect('coe/coe_event/manage/' . $group_id);
    }

    // ------------------------------------------------------------------
    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_event');

        $data['event'] = $this->db->where('id', (int)$id)->where('is_end_semester', 1)->get('exam_groups')->row();
        if (!$data['event']) {
            show_404();
        }

        $data['title'] = 'Edit Exam Event';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_event/edit', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    public function update($id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_edit')) {
            if ($this->input->is_ajax_request()) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Access denied']));
            }
            access_denied();
        }

        $id    = (int) $id;
        $event = $this->db->where('id', $id)->where('is_end_semester', 1)->get('exam_groups')->row();
        if (!$event) {
            if ($this->input->is_ajax_request()) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => 'Event not found']));
            }
            show_404();
        }

        $this->form_validation->set_rules('name',          'Event Name', 'trim|required|max_length[250]');
        $this->form_validation->set_rules('exam_category', 'Category',   'trim|required|in_list[main,arrear,supplementary]');
        $this->form_validation->set_rules('exam_type',     'Mode',       'trim|required|in_list[theory,practical,project,viva,online]');

        if ($this->form_validation->run() === false) {
            if ($this->input->is_ajax_request()) {
                return $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'message' => strip_tags(validation_errors())]));
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('coe/coe_event/edit/' . $id);
        }

        $this->db->where('id', $id)->update('exam_groups', [
            'name'          => $this->input->post('name'),
            'exam_category' => $this->input->post('exam_category'),
            'exam_type'     => $this->input->post('exam_type'),
            'description'   => $this->input->post('description'),
        ]);

        $this->Coe_audit_model->log('coe_event_updated', 'exam_groups', $id, null, ['name' => $this->input->post('name')]);

        if ($this->input->is_ajax_request()) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Exam event updated successfully.']));
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Exam event updated.</div>');
        redirect('coe/coe_event/manage/' . $id);
    }

    // ------------------------------------------------------------------
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_delete')) {
            access_denied();
        }

        $id = (int) $id;
        // Block delete if any batch exam has applications
        $has_apps = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM coe_exam_applications capp
             JOIN exam_group_class_batch_exams egcbe ON egcbe.id = capp.exam_group_class_batch_exam_id
             WHERE egcbe.exam_group_id = ?",
            [$id]
        )->row()->cnt;

        if ((int)$has_apps > 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Cannot delete: this event has student applications. Deactivate it instead.</div>');
            redirect('coe/coe_event');
        }

        $this->db->where('id', $id)->update('exam_groups', ['is_active' => 0]);
        $this->Coe_audit_model->log('coe_event_deleted', 'exam_groups', $id, null, []);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Exam event deactivated.</div>');
        redirect('coe/coe_event');
    }

    // =========================================================================
    // BATCH EXAMS — manage / save / update / delete (per group)
    // =========================================================================

    /**
     * Manage (view + add/edit/delete) batch exams inside an exam group.
     */
    public function manage($group_id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_event');

        $group_id = (int) $group_id;
        $data['event'] = $this->db->where('id', $group_id)->where('is_end_semester', 1)->get('exam_groups')->row();
        if (!$data['event']) {
            show_404();
        }

        $data['batches'] = $this->db
            ->select('egcbe.*, s.session, c.class, d.department_name,
                      (SELECT COUNT(*) FROM coe_exam_applications WHERE exam_group_class_batch_exam_id = egcbe.id) AS app_count,
                      (SELECT COUNT(*) FROM exam_group_class_batch_exam_students WHERE exam_group_class_batch_exam_id = egcbe.id AND is_active = 1) AS student_count,
                      (SELECT COUNT(*) FROM exam_group_class_batch_exam_subjects WHERE exam_group_class_batch_exams_id = egcbe.id AND is_active = 1) AS subject_count')
            ->from('exam_group_class_batch_exams egcbe')
            ->join('sessions s',    's.id = egcbe.session_id', 'left')
            ->join('classes c',     'c.id = egcbe.class_id',   'left')
            ->join('department d',  'd.id = c.department_id',  'left')
            ->where('egcbe.exam_group_id', $group_id)
            ->order_by('d.department_name, c.class, egcbe.date_from')
            ->get()->result();

        $data['session_list']     = $this->session_model->getAllSession();
        $data['current_session']  = $this->current_session;
        $data['class_list']       = $this->db
            ->select('c.id, c.class, d.department_name')
            ->from('classes c')
            ->join('department d', 'd.id = c.department_id', 'left')
            ->where('c.class_type', 'academic')
            ->order_by('d.department_name, c.class')
            ->get()->result_array();

        $data['title'] = 'Manage: ' . $data['event']->name;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_event/manage', $data);
        $this->load->view('layout/footer', $data);
    }

    // ------------------------------------------------------------------
    public function save_batch($group_id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_add')) {
            access_denied();
        }

        $group_id = (int) $group_id;
        $group = $this->db->where('id', $group_id)->where('is_end_semester', 1)->get('exam_groups')->row();
        if (!$group) {
            show_404();
        }

        $this->form_validation->set_rules('session_id', 'Session',    'trim|required|integer');
        $this->form_validation->set_rules('exam',       'Batch Label','trim|required|max_length[250]');
        $this->form_validation->set_rules('date_from',  'Date From',  'trim|required');
        $this->form_validation->set_rules('date_to',    'Date To',    'trim|required');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('coe/coe_event/manage/' . $group_id);
        }

        // Validate class_id (single select)
        $class_id = (int) $this->input->post('class_id');
        if ($class_id <= 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please select a class.</div>');
            redirect('coe/coe_event/manage/' . $group_id);
        }

        $session_id     = (int) $this->input->post('session_id');
        $total_enrolled = 0;
        $created        = 0;
        $skipped        = 0;

        foreach ([$class_id] as $class_id) {
            // Skip duplicates: same group + class + session
            $dup = $this->db->where('exam_group_id', $group_id)->where('class_id', $class_id)->where('session_id', $session_id)->count_all_results('exam_group_class_batch_exams');
            if ($dup > 0) {
                $skipped++;
                continue;
            }

            $this->db->insert('exam_group_class_batch_exams', [
                'exam'               => $this->input->post('exam'),
                'exam_group_id'      => $group_id,
                'class_id'           => $class_id,
                'session_id'         => $session_id,
                'date_from'          => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date_from'))),
                'date_to'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date_to'))),
                'passing_percentage' => (float)($this->input->post('passing_percentage') ?: 50),
                'is_end_semester'    => 1,
                'is_active'          => 1,
                'coe_locked'         => 0,
                'is_publish'         => 0,
            ]);
            $batch_id = $this->db->insert_id();
            $created++;

            $enrolled        = $this->_auto_enroll_students($batch_id, $class_id, $session_id);
            $total_enrolled += $enrolled;

            $this->Coe_audit_model->log('coe_batch_created', 'exam_group_class_batch_exams', $batch_id, null, [
                'group_id'       => $group_id,
                'class_id'       => $class_id,
                'students_added' => $enrolled,
            ]);
        }

        $msg = '';
        if ($created > 0) {
            $msg .= '<div class="alert alert-success">Batch created, <strong>' . $total_enrolled . '</strong> student' . ($total_enrolled == 1 ? '' : 's') . ' auto-enrolled.</div>';
        }
        if ($skipped > 0) {
            $msg .= '<div class="alert alert-warning">Batch already exists for that class/session — skipped.</div>';
        }
        $this->session->set_flashdata('msg', $msg);
        redirect('coe/coe_event/manage/' . $group_id);
    }

    // ------------------------------------------------------------------
    public function update_batch($batch_id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_edit')) {
            access_denied();
        }

        $batch_id = (int) $batch_id;
        $batch = $this->db->where('id', $batch_id)->get('exam_group_class_batch_exams')->row();
        if (!$batch) {
            show_404();
        }

        $this->form_validation->set_rules('exam',      'Batch Label', 'trim|required|max_length[250]');
        $this->form_validation->set_rules('date_from', 'Date From',   'trim|required');
        $this->form_validation->set_rules('date_to',   'Date To',     'trim|required');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('coe/coe_event/manage/' . $batch->exam_group_id);
        }

        // Pre-compute dates BEFORE ->where()->update() chain.
        // datetostrtotime() calls getSchoolDateFormat() → setting_model->get() → reset_query(),
        // which would wipe any QB WHERE set before the update() call if evaluated lazily inside the array.
        $date_from = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date_from')));
        $date_to   = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date_to')));

        $this->db->where('id', $batch_id)->update('exam_group_class_batch_exams', [
            'exam'               => $this->input->post('exam'),
            'date_from'          => $date_from,
            'date_to'            => $date_to,
            'passing_percentage' => (float)($this->input->post('passing_percentage') ?: 50),
            'description'        => $this->input->post('description'),
        ]);

        $this->Coe_audit_model->log('coe_batch_updated', 'exam_group_class_batch_exams', $batch_id, null, ['exam' => $this->input->post('exam')]);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Batch exam updated.</div>');
        redirect('coe/coe_event/manage/' . $batch->exam_group_id);
    }

    // ------------------------------------------------------------------
    public function delete_batch($batch_id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_delete')) {
            access_denied();
        }

        $batch_id = (int) $batch_id;
        $batch = $this->db->where('id', $batch_id)->get('exam_group_class_batch_exams')->row();
        if (!$batch) {
            show_404();
        }

        // Block if any applications exist
        $app_count = $this->db->where('exam_group_class_batch_exam_id', $batch_id)->count_all_results('coe_exam_applications');
        if ($app_count > 0) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Cannot delete: this batch has ' . $app_count . ' student application(s). Generate fresh from Exam Events if needed.</div>');
            redirect('coe/coe_event/manage/' . $batch->exam_group_id);
        }

        // Delete enrolled students too (cascade cleanup)
        $this->db->where('exam_group_class_batch_exam_id', $batch_id)->delete('exam_group_class_batch_exam_students');
        $this->db->where('id', $batch_id)->delete('exam_group_class_batch_exams');

        $this->Coe_audit_model->log('coe_batch_deleted', 'exam_group_class_batch_exams', $batch_id, null, []);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Batch exam removed.</div>');
        redirect('coe/coe_event/manage/' . $batch->exam_group_id);
    }

    // ------------------------------------------------------------------
    public function toggle_lock_batch($batch_id)
    {
        if (!$this->rbac->hasPrivilege('coe_event', 'can_edit')) {
            access_denied();
        }

        $batch_id = (int) $batch_id;
        $batch = $this->db->where('id', $batch_id)->get('exam_group_class_batch_exams')->row();
        if (!$batch) {
            show_404();
        }

        $new_state = $batch->coe_locked ? 0 : 1;
        $this->db->where('id', $batch_id)->update('exam_group_class_batch_exams', ['coe_locked' => $new_state]);

        $action = $new_state ? 'locked' : 'unlocked';
        $this->Coe_audit_model->log('coe_batch_' . $action, 'exam_group_class_batch_exams', $batch_id, null, [
            'coe_locked' => $new_state,
        ]);

        $color = $new_state ? 'alert-warning' : 'alert-success';
        $icon  = $new_state ? 'lock' : 'unlock';
        $this->session->set_flashdata('msg', '<div class="alert ' . $color . '"><i class="fa fa-' . $icon . '"></i> Batch <strong>' . htmlspecialchars($batch->exam) . '</strong> has been <strong>' . $action . '</strong>.</div>');
        redirect('coe/coe_event/manage/' . $batch->exam_group_id);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Auto-enroll all active students from a class/session into a batch exam.
     * Returns the count of students inserted.
     */
    private function _auto_enroll_students($batch_id, $class_id, $session_id)
    {
        $students = $this->db
            ->select('id AS student_session_id, student_id')
            ->from('student_session')
            ->where('class_id',   $class_id)
            ->where('session_id', $session_id)
            ->where('is_active !=', 'no')
            ->get()->result();

        if (empty($students)) {
            return 0;
        }

        $rows = [];
        foreach ($students as $s) {
            $rows[] = [
                'exam_group_class_batch_exam_id' => $batch_id,
                'student_id'                     => $s->student_id,
                'student_session_id'             => $s->student_session_id,
                'is_active'                      => 1,
            ];
        }
        $this->db->insert_batch('exam_group_class_batch_exam_students', $rows);
        return count($rows);
    }
}
