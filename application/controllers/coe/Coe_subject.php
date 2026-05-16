<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_subject
 *
 * CoE-internal subject assignment for a batch exam.
 * Manages which subjects are active in exam_group_class_batch_exam_subjects
 * so that generateApplications() can proceed.
 *
 * Routes:
 *  GET  coe/coe_subject/assign/{batch_exam_id}  — show assignment form
 *  POST coe/coe_subject/assign/{batch_exam_id}  — save subject list
 */
class Coe_subject extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
    }

    // ------------------------------------------------------------------
    // ASSIGN — GET: show form; POST: save subjects
    // ------------------------------------------------------------------
    public function assign($batch_exam_id = 0)
    {
        if (!$this->rbac->hasPrivilege('coe_application', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        if ($batch_exam_id <= 0) {
            show_404();
        }

        $batch = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($batch)) {
            show_404();
        }

        // POST: save subject list
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if ($batch->coe_locked) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left"><i class="fa fa-lock"></i> Cannot modify subjects: this batch exam is CoE-locked.</div>');
                redirect('coe/coe_subject/assign/' . $batch_exam_id);
            }

            $subject_ids = $this->input->post('subject_ids') ?: [];
            $subject_ids = array_values(array_unique(array_filter(array_map('intval', (array) $subject_ids))));

            $this->Coe_application_model->saveBatchSubjects($batch_exam_id, $subject_ids, $batch->date_from);
            $this->Coe_audit_model->log(
                'batch_subjects_saved',
                'exam_group_class_batch_exams',
                $batch_exam_id,
                null,
                ['subject_count' => count($subject_ids)]
            );

            $this->session->set_flashdata('msg',
                '<div class="alert alert-success text-left"><i class="fa fa-check-circle"></i> <strong>'
                . count($subject_ids)
                . ' subject(s)</strong> assigned to this batch exam.</div>'
            );
            redirect('coe/coe_subject/assign/' . $batch_exam_id);
        }

        // GET: build data for view
        $this->session->set_userdata('top_menu', 'coe');
        $this->session->set_userdata('sub_menu', 'coe/coe_application');

        // All subjects, ordered by type then name
        // (subjects.is_active uses varchar 'yes'/'no', not filtered here —
        //  subject visibility is controlled by the exam subject group config)
        $all_subjects = $this->db
            ->order_by('type', 'ASC')
            ->order_by('name', 'ASC')
            ->get('subjects')
            ->result();

        // Already-configured subject IDs for this batch
        $rows = $this->db
            ->select('subject_id')
            ->where('exam_group_class_batch_exams_id', $batch_exam_id)
            ->where('is_active', 1)
            ->get('exam_group_class_batch_exam_subjects')
            ->result();
        $configured_ids = array_map('intval', array_column((array) $rows, 'subject_id'));

        // Subject groups for this batch's class+session (for smart pre-filter hint)
        $class_subject_ids = [];
        $resolved_class_id = !empty($batch->class_id) ? (int)$batch->class_id : null;

        // If class_id is NULL on the batch (legacy examination-module batches),
        // fallback 1: derive from the majority class of enrolled students.
        if (!$resolved_class_id && !empty($batch->session_id)) {
            $inferred = $this->db->query(
                "SELECT ss.class_id, COUNT(*) AS cnt
                 FROM exam_group_class_batch_exam_students egbs
                 JOIN student_session ss ON ss.student_id = egbs.student_id AND ss.session_id = ?
                 WHERE egbs.exam_group_class_batch_exam_id = ?
                 GROUP BY ss.class_id
                 ORDER BY cnt DESC
                 LIMIT 1",
                [(int)$batch->session_id, $batch_exam_id]
            )->row();
            if ($inferred) {
                $resolved_class_id = (int)$inferred->class_id;
            }
        }

        // Fallback 2: if still no class_id, infer from already-configured subjects —
        // find whichever subject_group has the most overlap with configured subjects.
        if (!$resolved_class_id && !empty($batch->session_id) && !empty($configured_ids)) {
            $in_list = implode(',', array_map('intval', $configured_ids));
            $best = $this->db->query(
                "SELECT sg.class_id, COUNT(*) AS match_count
                 FROM subject_group_subjects sgs
                 JOIN subject_groups sg ON sg.id = sgs.subject_group_id
                 WHERE sgs.subject_id IN ($in_list)
                   AND sgs.session_id = " . (int)$batch->session_id . "
                 GROUP BY sg.class_id
                 ORDER BY match_count DESC
                 LIMIT 1"
            )->row();
            if ($best && $best->class_id) {
                $resolved_class_id = (int)$best->class_id;
            }
        }

        if ($resolved_class_id && !empty($batch->session_id)) {
            $sg_rows = $this->db
                ->select('sgs.subject_id')
                ->from('subject_groups sg')
                ->join('subject_group_subjects sgs', 'sgs.subject_group_id = sg.id', 'inner')
                ->where('sg.class_id', $resolved_class_id)
                ->where('sg.session_id', (int) $batch->session_id)
                ->get()
                ->result();
            $class_subject_ids = array_map('intval', array_column((array) $sg_rows, 'subject_id'));
        }

        $data['resolved_class_id'] = $resolved_class_id;

        $data['title']             = 'Assign Subjects — ' . htmlspecialchars($batch->exam);
        $data['batch']             = $batch;
        $data['all_subjects']      = $all_subjects;
        $data['configured_ids']    = $configured_ids;
        $data['class_subject_ids'] = $class_subject_ids; // IDs from subject_groups for this class

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_subject/assign', $data);
        $this->load->view('layout/footer', $data);
    }
}
