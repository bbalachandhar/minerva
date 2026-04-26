<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Complaint extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('media_storage');
        $this->load->model('complaint_Model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('complaint', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'human_resource');
        $this->session->set_userdata('sub_menu', 'admin/complaint');

        $this->form_validation->set_rules('name',    $this->lang->line('complain_by'), 'required');
        $this->form_validation->set_rules('contact', $this->lang->line('phone'),       'trim|regex_match[/^([0-9]{10})?$/]');
        $this->form_validation->set_rules('file',    $this->lang->line('file'),        'callback_handle_upload[file]');

        if ($this->form_validation->run() == false) {
            $filters = [];
            if ($this->input->get('session_id'))   $filters['session_id']   = (int)$this->input->get('session_id');
            if ($this->input->get('status'))       $filters['status']       = $this->input->get('status');
            if ($this->input->get('priority'))     $filters['priority']     = $this->input->get('priority');
            if ($this->input->get('submitted_by')) $filters['submitted_by'] = $this->input->get('submitted_by');

            $session_id_for_counts = !empty($filters['session_id']) ? $filters['session_id'] : null;
            $data['complaint_list']  = $this->complaint_Model->complaint_list(null, $filters);
            $data['complaint_type']  = $this->complaint_Model->getComplaintType();
            $data['complaintsource'] = $this->complaint_Model->getComplaintSource();
            $data['status_counts']   = $this->complaint_Model->getStatusCounts($session_id_for_counts);
            $data['sessions']        = $this->complaint_Model->getSessions();
            $data['current_session'] = $this->setting_model->getCurrentSession();
            $data['filters']         = $filters;

            // Prepopulate add form with logged-in staff info
            $admin_session = $this->session->userdata('admin');
            $staff_row     = $this->staff_model->get($admin_session['id'] ?? 0);
            $data['logged_in_name']    = $admin_session['username'] ?? '';
            $data['logged_in_contact'] = $staff_row['mobileno']    ?? '';
            $data['logged_in_empid']   = $staff_row['employee_id'] ?? '';

            $this->load->view('layout/header');
            $this->load->view('admin/frontoffice/complaintview', $data);
            $this->load->view('layout/footer');
        } else {
            $img_name = '';
            if (isset($_FILES['file']) && $_FILES['file']['name'] != '' && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE) {
                $upload_result = $this->media_storage->fileupload("file", "./uploads/front_office/complaints/");
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/complaint');
                }
                $img_name = $upload_result['message'];
            }
            $complaint = array(
                'complaint_type' => $this->input->post('complaint'),
                'source'         => 'Staff Portal',
                'submitted_by'   => 'staff',
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact') ?? '',
                'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'    => $this->input->post('description', true) ?? '',
                'action_taken'   => $this->input->post('action_taken', true) ?? '',
                'assigned'       => $this->input->post('assigned', true) ?? '',
                'note'           => $this->input->post('note', true) ?? '',
                'priority'       => $this->input->post('priority') ?: 'medium',
                'status'         => 'open',
                'image'          => $img_name,
            );
            $this->complaint_Model->add($complaint);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/complaint');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('complaint', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('name',    $this->lang->line('complaint_by'), 'required');
        $this->form_validation->set_rules('contact', $this->lang->line('phone'),        'trim|regex_match[/^([0-9]{10})?$/]');
        $this->form_validation->set_rules('file',    $this->lang->line('file'),         'callback_handle_upload[file]');

        $data['complaint_data'] = $this->complaint_Model->complaint_list($id);

        if ($this->form_validation->run() == false) {
            $session_id_edit         = (int)$this->input->get('session_id') ?: 0;
            $edit_filters            = $session_id_edit ? ['session_id' => $session_id_edit] : [];
            $data['complaint_list']  = $this->complaint_Model->complaint_list(null, $edit_filters);
            $data['complaint_type']  = $this->complaint_Model->getComplaintType();
            $data['complaintsource'] = $this->complaint_Model->getComplaintSource();
            $data['status_counts']   = $this->complaint_Model->getStatusCounts($session_id_edit ?: null);
            $data['sessions']        = $this->complaint_Model->getSessions();
            $data['current_session'] = $this->setting_model->getCurrentSession();
            $data['filters']         = $edit_filters;
            $data['staff_list']      = $this->staff_model->getAll(null, 1);
            $this->load->view('layout/header');
            $this->load->view('admin/frontoffice/complainteditview', $data);
            $this->load->view('layout/footer');
        } else {
            $complaint = array(
                'complaint_type' => $this->input->post('complaint'),
                'source'         => $this->input->post('source'),
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'    => $this->input->post('description', true),
                'action_taken'   => $this->input->post('action_taken', true),
                'assigned'       => $this->input->post('assigned', true),
                'note'           => $this->input->post('note', true),
                'priority'       => $this->input->post('priority') ?: 'medium',
                'status'         => $this->input->post('status') ?: 'open',
                'admin_response' => $this->input->post('admin_response', true),
            );

            if (isset($_FILES["file"]) && $_FILES['file']['name'] != '' && (!empty($_FILES['file']['name']))) {
                $upload_result = $this->media_storage->fileupload("file", "./uploads/front_office/complaints/");
                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('admin/complaint');
                }
                $complaint['image'] = $upload_result['message'];
                $this->media_storage->filedelete($data['complaint_data']['image'], "uploads/front_office/complaints/");
            }

            // Set responded_by/at when admin_response provided for first time
            if (!empty($complaint['admin_response']) && empty($data['complaint_data']['admin_response'])) {
                $complaint['responded_by'] = $this->session->userdata('user_id');
                $complaint['responded_at'] = date('Y-m-d H:i:s');
            }

            $this->complaint_Model->compalaint_update($id, $complaint);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/complaint');
        }
    }

    /**
     * AJAX: respond to a complaint (update status + admin_response).
     */
    public function respond($id)
    {
        if (!$this->rbac->hasPrivilege('complaint', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }
        $id = (int)$id;
        $update = [
            'status'         => $this->input->post('status'),
            'priority'       => $this->input->post('priority'),
            'admin_response' => $this->input->post('admin_response', true),
            'assigned'       => $this->input->post('assigned', true),
            'action_taken'   => $this->input->post('action_taken', true),
            'responded_by'   => $this->session->userdata('user_id'),
            'responded_at'   => date('Y-m-d H:i:s'),
        ];
        $this->complaint_Model->compalaint_update($id, $update);
        echo json_encode(['status' => 'success', 'message' => $this->lang->line('update_message')]);
    }

    /**
     * AJAX: details modal content.
     */
    public function details($id)
    {
        if (!$this->rbac->hasPrivilege('complaint', 'can_view')) {
            access_denied();
        }
        $data['complaint_data'] = $this->complaint_Model->complaint_list($id);
        $this->load->view('admin/frontoffice/Complaintmodalview', $data);
    }

    /**
     * AJAX: dashboard widget counts.
     */
    public function widget()
    {
        if (!$this->rbac->hasPrivilege('complaint', 'can_view')) {
            echo json_encode(['status' => 'fail', 'data' => ['open' => 0, 'total' => 0, 'percent' => 0]]);
            return;
        }
        $counts = $this->complaint_Model->getStatusCounts();
        $open   = (int)($counts['open_count'] ?? 0) + (int)($counts['in_progress_count'] ?? 0);
        $total  = (int)($counts['total_count'] ?? 0);
        $pct    = $total > 0 ? round(($open / $total) * 100) : 0;
        echo json_encode([
            'status' => 'success',
            'data'   => ['open' => $open, 'total' => $total, 'percent' => $pct],
        ]);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('complaint', 'can_delete')) {
            access_denied();
        }
        $row = $this->complaint_Model->complaint_list($id);
        if (!empty($row['image'])) {
            $this->media_storage->filedelete($row['image'], "uploads/front_office/complaints/");
        }
        $this->complaint_Model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/complaint');
    }

    public function download($id)
    {
        $complaint_list = $this->complaint_Model->complaint_list($id);
        $this->media_storage->filedownload($complaint_list['image'], "./uploads/front_office/complaints");
    }

    public function check_default($post_string)
    {
        return $post_string == "" ? false : true;
    }

    public function handle_upload($str, $var)
    {
        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES[$var]) && !empty($_FILES[$var]['name'])) {
            $file_type = $_FILES[$var]['type'];
            $file_size = $_FILES[$var]["size"];
            $file_name = $_FILES[$var]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES[$var]['tmp_name'])) {
                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }
                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                if ($file_size > $result->file_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->file_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line("file_type_extension_error_uploading_image"));
                return false;
            }
            return true;
        }
        return true;
    }

}
