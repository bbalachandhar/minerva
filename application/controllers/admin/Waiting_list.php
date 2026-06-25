<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Waiting_list extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'admissions');
        $this->session->set_userdata('sub_menu', 'admin/waiting_list');

        $session_id = $this->setting_model->getCurrentSession();

        $waiting = $this->db
            ->select('oa.*, IFNULL(oac.course_name, "N/A") AS course_name')
            ->from('online_admissions oa')
            ->join('online_admission_courses oac', 'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left')
            ->where('oa.session_id', $session_id)
            ->where('oa.admission_status', 'waiting_list')
            ->order_by('oa.id', 'DESC')
            ->get()->result_array();

        $data['title']        = 'Waiting List';
        $data['waiting_list'] = $waiting;
        $data['sch_setting']  = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/waiting_list/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function activate($id = null)
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $id = (int) $id;
        $admission = $this->db->where('id', $id)->get('online_admissions')->row_array();

        if (empty($admission) || $admission['admission_status'] !== 'waiting_list') {
            echo json_encode(['status' => 'error', 'message' => 'Record not found or not in waiting list.']);
            return;
        }

        $this->db->where('id', $id)->update('online_admissions', ['admission_status' => 'active']);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Application #' . $admission['reference_no'] . ' moved to active admissions.</div>');
        echo json_encode(['status' => 'success', 'message' => 'Application activated successfully.']);
    }

    public function delete()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_delete')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $id = (int) $this->input->post('id');
        $admission = $this->db->where('id', $id)->get('online_admissions')->row_array();

        if (empty($admission) || $admission['admission_status'] !== 'waiting_list') {
            echo json_encode(['status' => 'error', 'message' => 'Record not found or not in waiting list.']);
            return;
        }

        $this->db->where('online_admission_id', $id)->where('candidate_type', 'applicant')->delete('onlineexam_students');
        $this->db->where('id', $id)->delete('online_admissions');

        echo json_encode(['status' => 'success', 'message' => 'Application #' . $admission['reference_no'] . ' deleted.']);
    }

    public function update_comment()
    {
        if (!$this->rbac->hasPrivilege('online_admission', 'can_edit')) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            return;
        }

        $id = (int) $this->input->post('id');
        $comment = $this->input->post('comment');

        $row = $this->db->where('id', $id)->where('admission_status', 'waiting_list')->get('online_admissions')->row();
        if (!$row) {
            echo json_encode(['status' => 'error', 'message' => 'Record not found']);
            return;
        }

        $this->db->where('id', $id)->update('online_admissions', ['waiting_list_comment' => $comment ?: null]);
        echo json_encode(['status' => 'success']);
    }
}
