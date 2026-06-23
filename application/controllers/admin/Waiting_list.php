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
            ->select('oa.*, CONCAT(COALESCE(c.class,""), " / ", COALESCE(s.section,"")) AS course_name')
            ->from('online_admissions oa')
            ->join('class_sections cs', 'cs.id = oa.class_section_id', 'left')
            ->join('classes c', 'c.id = cs.class_id', 'left')
            ->join('sections s', 's.id = cs.section_id', 'left')
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
}
