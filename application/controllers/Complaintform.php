<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Complaintform extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->model('language_model');
        $this->load->model('setting_model');
        $this->load->model('complaint_Model');
        $this->load->helper('url');
        $this->load->helper('form');
    }

    public function index()
    {
        $this->form_validation->set_rules('name',        'Name',        'trim|required|max_length[100]|xss_clean');
        $this->form_validation->set_rules('complaint_type', 'Complaint Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact',     'Contact',     'trim|max_length[15]|xss_clean');
        $this->form_validation->set_rules('email',       'Email',       'trim|valid_email|xss_clean');

        $data['complaint_types'] = $this->complaint_Model->getComplaintType();
        $data['header_image']    = $this->_get_header_image();

        if ($this->form_validation->run() == FALSE) {
            $data['main_content'] = 'complaintform/index';
            $this->load->view('complaintform/complaint_template', $data);
        } else {
            $ticket_no = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $this->complaint_Model->add([
                'complaint_type' => $this->input->post('complaint_type'),
                'source'         => 'Public Form',
                'submitted_by'   => 'external',
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact') ?: '',
                'email'          => $this->input->post('email') ?: '',
                'description'    => $this->input->post('description'),
                'date'           => date('Y-m-d'),
                'status'         => 'open',
                'priority'       => 'medium',
                'ticket_no'      => $ticket_no,
                'admission_no'   => '',
                'class_name'     => '',
                'section_name'   => '',
                'parent_name'    => '',
                'employee_id'    => '',
                'assigned'       => '',
                'action_taken'   => '',
            ]);

            $this->session->set_flashdata('ticket_no', $ticket_no);
            redirect('complaint/success');
        }
    }

    public function success()
    {
        $setting             = $this->setting_model->getSetting();
        $data['website_url'] = !empty($setting->website) ? $setting->website : (!empty($setting->base_url) ? $setting->base_url : base_url());
        $data['ticket_no']   = $this->session->flashdata('ticket_no');
        $data['header_image']= $this->_get_header_image();
        $data['main_content']= 'complaintform/success';
        $this->load->view('complaintform/complaint_template', $data);
    }

    // ── helpers ────────────────────────────────────────────────────────────────

    private function _get_header_image()
    {
        $header_footer = $this->setting_model->get_printheader();
        if ($header_footer) {
            foreach ($header_footer as $hf) {
                if ($hf['print_type'] === 'general_purpose') {
                    return $hf['header_image'];
                }
            }
        }
        return '';
    }
}
