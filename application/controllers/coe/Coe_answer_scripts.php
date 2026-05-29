<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Coe_answer_scripts
 * Controller for answer script upload, barcode anonymisation, and tracking.
 */
class Coe_answer_scripts extends MY_Addon_CoeController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_answer_scripts_model');
        $this->load->model('coe/Coe_application_model');
    }

    // ------------------------------------------------------------------
    // index() — Pick exam event
    // ------------------------------------------------------------------
    public function index()
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_view')) {
            access_denied();
        }

        $session_id        = $this->input->get('session_id') ?: $this->current_session;
        $data['session_id'] = $session_id;
        $data['events']    = $this->Coe_application_model->getExamEventsBySession($session_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_answer_scripts/index', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // listing($batch_exam_id) — List scripts for one exam event
    // ------------------------------------------------------------------
    public function listing($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_view')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $filters = [
            'batch_exam_id' => $batch_exam_id,
            'subject_id'    => $this->input->get('subject_id'),
            'scan_status'   => $this->input->get('scan_status'),
            'exam_date'     => $this->input->get('exam_date'),
        ];

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['scripts']       = $this->Coe_answer_scripts_model->getAll($filters);
        $data['subjects']      = $this->Coe_answer_scripts_model->getSubjectsByBatchExam($batch_exam_id);
        $data['counts']        = $this->Coe_answer_scripts_model->countByStatus($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_answer_scripts/listing', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // upload($batch_exam_id) — Upload form
    // ------------------------------------------------------------------
    public function upload($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')) {
            access_denied();
        }

        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_answer_scripts_model->getSubjectsByBatchExam($batch_exam_id);
        $data['hall_tickets']  = $this->Coe_answer_scripts_model->getHallTicketsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_answer_scripts/upload', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // save_upload() — Process upload POST (AJAX)
    // ------------------------------------------------------------------
    public function save_upload()
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $this->form_validation->set_rules('batch_exam_id',   'Exam Event',  'required|integer');
        $this->form_validation->set_rules('hall_ticket_id',  'Hall Ticket', 'required|integer');
        $this->form_validation->set_rules('subject_id',      'Subject',     'required|integer');
        $this->form_validation->set_rules('exam_date',       'Exam Date',   'required');
        $this->form_validation->set_rules('session_slot',    'Session',     'required|in_list[FN,AN]');

        if (!$this->form_validation->run()) {
            echo json_encode(['status' => 'error', 'msg' => validation_errors()]);
            return;
        }

        $batch_exam_id  = (int) $this->input->post('batch_exam_id');
        $hall_ticket_id = (int) $this->input->post('hall_ticket_id');
        $subject_id     = (int) $this->input->post('subject_id');

        // Duplicate check
        if ($this->Coe_answer_scripts_model->existsForHallTicketSubject($hall_ticket_id, $subject_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'A script for this hall ticket & subject already exists.']);
            return;
        }

        // Handle file upload (optional — script may be physical-only at first)
        $filename = null;
        if (!empty($_FILES['script_file']['name'])) {
            $upload_dir = FCPATH . 'uploads/answer_scripts/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $this->upload->initialize([
                'upload_path'   => $upload_dir,
                'allowed_types' => 'pdf|jpg|jpeg|png',
                'max_size'      => 20480, // 20 MB
                'encrypt_name'  => true,
            ]);
            if (!$this->upload->do_upload('script_file')) {
                echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors('', '')]);
                return;
            }
            $filename = $this->upload->data('file_name');
        }

        $barcode = $this->Coe_answer_scripts_model->generateBarcodeToken();
        $now     = date('Y-m-d H:i:s');

        $row = [
            'exam_group_class_batch_exam_id' => $batch_exam_id,
            'coe_hall_ticket_id'             => $hall_ticket_id,
            'subject_id'                     => $subject_id,
            'exam_date'                      => $this->input->post('exam_date'),
            'session_slot'                   => $this->input->post('session_slot'),
            'barcode_token'                  => $barcode,
            'scanned_filename'               => $filename,
            'scan_status'                    => $filename ? 'uploaded' : 'pending',
            'page_count'                     => $this->input->post('page_count') ?: null,
            'remarks'                        => $this->input->post('remarks'),
            'uploaded_by'                    => $this->session->userdata('staff_id'),
            'uploaded_at'                    => $filename ? $now : null,
        ];

        $id = $this->Coe_answer_scripts_model->insert($row);
        $this->Coe_audit_model->log('upload', 'coe_answer_scripts', $id, null, $row);

        echo json_encode(['status' => 'success', 'msg' => 'Script registered. Barcode: ' . $barcode]);
    }

    // ------------------------------------------------------------------
    // view($id) — View single script detail
    // ------------------------------------------------------------------
    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_view')) {
            access_denied();
        }

        $script = $this->Coe_answer_scripts_model->getById($id);
        if (empty($script)) {
            show_404();
        }

        $data['script'] = $script;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_answer_scripts/view', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // update_status($id) — AJAX status update (scanned / uploaded)
    // ------------------------------------------------------------------
    public function update_status($id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_edit')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $allowed = ['pending', 'scanned', 'uploaded'];
        $new_status = $this->input->post('scan_status');
        if (!in_array($new_status, $allowed)) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid status']);
            return;
        }

        $old = $this->Coe_answer_scripts_model->getById($id);
        if (empty($old)) {
            echo json_encode(['status' => 'error', 'msg' => 'Script not found']);
            return;
        }

        $upd = ['scan_status' => $new_status];
        $this->Coe_answer_scripts_model->update($id, $upd);
        $this->Coe_audit_model->log('status_update', 'coe_answer_scripts', $id, ['scan_status' => $old->scan_status], $upd);

        echo json_encode(['status' => 'success', 'msg' => 'Status updated']);
    }

    // ------------------------------------------------------------------
    // delete($id) — Delete script record
    // ------------------------------------------------------------------
    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_delete')) {
            access_denied();
        }

        $old = $this->Coe_answer_scripts_model->getById($id);
        if (empty($old)) {
            show_404();
        }

        $batch_exam_id = $old->exam_group_class_batch_exam_id;

        // Remove file if it exists
        if (!empty($old->scanned_filename)) {
            $file_path = FCPATH . 'uploads/answer_scripts/' . $old->scanned_filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $this->Coe_answer_scripts_model->delete($id);
        $this->Coe_audit_model->log('delete', 'coe_answer_scripts', $id, (array) $old, null);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Script deleted.</div>');
        redirect('coe/coe_answer_scripts/listing/' . $batch_exam_id);
    }

    // ------------------------------------------------------------------
    // modal_content($id) — AJAX-only: returns partial for Bootstrap modal
    // ------------------------------------------------------------------
    public function modal_content($id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('coe/coe_answer_scripts/view/' . (int) $id);
            return;
        }
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_view')) {
            echo '<p class="text-danger"><i class="fa fa-ban"></i> Access denied.</p>';
            return;
        }
        $script = $this->Coe_answer_scripts_model->getById((int) $id);
        if (empty($script)) {
            echo '<p class="text-danger">Record not found.</p>';
            return;
        }
        $this->load->view('admin/coe/coe_answer_scripts/_script_detail', ['script' => $script]);
    }

    // ------------------------------------------------------------------
    // upload_file($id) — AJAX POST: upload/replace scanned file for a script
    // ------------------------------------------------------------------
    public function upload_file($id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $script = $this->Coe_answer_scripts_model->getById((int) $id);
        if (empty($script)) {
            echo json_encode(['status' => 'error', 'msg' => 'Record not found']);
            return;
        }

        if (empty($_FILES['script_file']['name'])) {
            echo json_encode(['status' => 'error', 'msg' => 'No file selected.']);
            return;
        }

        $upload_dir = FCPATH . 'uploads/answer_scripts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $this->upload->initialize([
            'upload_path'   => $upload_dir,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size'      => 20480,
            'encrypt_name'  => true,
        ]);

        if (!$this->upload->do_upload('script_file')) {
            echo json_encode(['status' => 'error', 'msg' => $this->upload->display_errors('', '')]);
            return;
        }

        // Delete old file if exists
        if (!empty($script->scanned_filename)) {
            $old = FCPATH . 'uploads/answer_scripts/' . $script->scanned_filename;
            if (file_exists($old)) {
                unlink($old);
            }
        }

        $filename   = $this->upload->data('file_name');
        $page_count = (int) $this->input->post('page_count');
        $now        = date('Y-m-d H:i:s');

        $upd = [
            'scanned_filename' => $filename,
            'scan_status'      => 'uploaded',
            'uploaded_by'      => $this->customlib->getStaffID(),
            'uploaded_at'      => $now,
        ];
        if ($page_count > 0) {
            $upd['page_count'] = $page_count;
        }

        $this->Coe_answer_scripts_model->update((int) $id, $upd);
        $this->Coe_audit_model->log('upload_file', 'coe_answer_scripts', (int) $id,
            ['scanned_filename' => $script->scanned_filename], $upd);

        echo json_encode(['status' => 'success', 'msg' => 'File uploaded successfully. Status set to <strong>Uploaded</strong>.']);
    }

    // ------------------------------------------------------------------
    // bulk_register($batch_exam_id) — Bulk register page (select subject → check students)
    // ------------------------------------------------------------------
    public function bulk_register($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')) {
            access_denied();
        }

        $batch_exam_id = (int) $batch_exam_id;
        $event = $this->Coe_application_model->getExamEventByIdRow($batch_exam_id);
        if (empty($event)) {
            show_404();
        }

        $data['event']         = $event;
        $data['batch_exam_id'] = $batch_exam_id;
        $data['subjects']      = $this->Coe_answer_scripts_model->getSubjectsByBatchExam($batch_exam_id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/coe/coe_answer_scripts/bulk_register', $data);
        $this->load->view('layout/footer');
    }

    // ------------------------------------------------------------------
    // get_unregistered_tickets($batch_exam_id) — AJAX: unregistered hall tickets for a subject
    // ------------------------------------------------------------------
    public function get_unregistered_tickets($batch_exam_id)
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_view')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $subject_id = (int) $this->input->get('subject_id');
        if (!$subject_id) {
            echo json_encode(['status' => 'error', 'msg' => 'Subject required']);
            return;
        }

        $tickets = $this->Coe_answer_scripts_model->getUnregisteredHallTickets((int) $batch_exam_id, $subject_id);
        echo json_encode(['status' => 'success', 'tickets' => $tickets]);
    }

    // ------------------------------------------------------------------
    // bulk_save() — AJAX POST: save bulk-registered scripts
    // ------------------------------------------------------------------
    public function bulk_save()
    {
        if (!$this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')) {
            echo json_encode(['status' => 'error', 'msg' => 'Access denied']);
            return;
        }

        $batch_exam_id = (int) $this->input->post('batch_exam_id');
        $subject_id    = (int) $this->input->post('subject_id');
        $exam_date     = $this->input->post('exam_date');
        $session_slot  = $this->input->post('session_slot');
        $ticket_ids    = $this->input->post('ticket_ids');

        if (!$batch_exam_id || !$subject_id || empty($ticket_ids) || !is_array($ticket_ids)) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid input. Select a subject and at least one student.']);
            return;
        }

        if (!in_array($session_slot, ['FN', 'AN'])) {
            $session_slot = 'FN';
        }

        $staff_id = $this->customlib->getStaffID();
        $now      = date('Y-m-d H:i:s');
        $rows     = [];

        foreach ($ticket_ids as $ht_id) {
            $ht_id = (int) $ht_id;
            if (!$ht_id) continue;
            if ($this->Coe_answer_scripts_model->existsForHallTicketSubject($ht_id, $subject_id)) {
                continue; // skip duplicates silently
            }
            $rows[] = [
                'exam_group_class_batch_exam_id' => $batch_exam_id,
                'coe_hall_ticket_id'             => $ht_id,
                'subject_id'                     => $subject_id,
                'exam_date'                      => $exam_date ?: null,
                'session_slot'                   => $session_slot,
                'barcode_token'                  => $this->Coe_answer_scripts_model->generateBarcodeToken(),
                'scan_status'                    => 'pending',
                'uploaded_by'                    => $staff_id,
                'uploaded_at'                    => $now,
            ];
        }

        if (empty($rows)) {
            echo json_encode(['status' => 'warning', 'msg' => 'No new scripts to register — all selected students already have scripts for this subject.']);
            return;
        }

        $count = $this->Coe_answer_scripts_model->bulkInsert($rows);
        $this->Coe_audit_model->log('bulk_register', 'coe_answer_scripts', $batch_exam_id, null, ['subject_id' => $subject_id, 'count' => $count]);

        echo json_encode(['status' => 'success', 'msg' => $count . ' script(s) registered successfully.', 'count' => $count]);
    }
}
