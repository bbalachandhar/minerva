<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Naac extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('naac_manual_configuration_model');
        $this->load->model('session_model');
    }

    public function configuration()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/configuration');
        $data['title'] = $this->lang->line('naac_configuration');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/configuration', $data);
        $this->load->view('layout/footer', $data);
    }

    public function manual_configuration($id = null)
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/configuration');
        $data['title'] = 'Manual Configuration';
        $data['sessionlist'] = $this->session_model->get();
        $data['manual_configuration_list'] = $this->naac_manual_configuration_model->get();

        if ($id) {
            $data['manual_configuration_record'] = $this->naac_manual_configuration_model->get($id);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/manual_configuration', $data);
        $this->load->view('layout/footer', $data);
    }

    public function manual_config_actions()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('manual_id', 'Manual ID', 'required');
        $this->form_validation->set_rules('institution_category', 'Institution Category', 'required');
        $this->form_validation->set_rules('manual_description', 'Manual Description', 'required');
        $this->form_validation->set_rules('month', 'Month', 'required');
        $this->form_validation->set_rules('year', 'Year', 'required');
        $this->form_validation->set_rules('total_criteria', 'Total Criteria', 'required');
        $this->form_validation->set_rules('total_key_indicators', 'Total Key Indicators (KIs)', 'required');
        $this->form_validation->set_rules('total_qualitative_metrics', 'Total Qualitative Metrics', 'required');
        $this->form_validation->set_rules('total_quantitative_metrics', 'Total Quantitative Metrics', 'required');
        $this->form_validation->set_rules('total_metrics', 'Total Metrics', 'required');
        $this->form_validation->set_rules('total_weightage', 'Total Weightage', 'required');
        $this->form_validation->set_rules('total_marks', 'Total Marks', 'required');
        $this->form_validation->set_rules('is_optional_metric', 'Optical Metrics', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->manual_configuration();
        } else {
            $data = array(
                'manual_id' => $this->input->post('manual_id'),
                'institution_category' => $this->input->post('institution_category'),
                'manual_description' => $this->input->post('manual_description'),
                'month' => $this->input->post('month'),
                'year' => $this->input->post('year'),
                'total_criteria' => $this->input->post('total_criteria'),
                'total_key_indicators' => $this->input->post('total_key_indicators'),
                'total_qualitative_metrics' => $this->input->post('total_qualitative_metrics'),
                'total_quantitative_metrics' => $this->input->post('total_quantitative_metrics'),
                'total_metrics' => $this->input->post('total_metrics'),
                'total_weightage' => $this->input->post('total_weightage'),
                'total_marks' => $this->input->post('total_marks'),
                'is_optional_metric' => $this->input->post('is_optional_metric'),
            );

            $id = $this->input->post('id');
            if ($id) {
                $this->naac_manual_configuration_model->update($id, $data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success">Manual Configuration updated successfully</div>');
            } else {
                $this->naac_manual_configuration_model->add($data);
                $this->session->set_flashdata('msg', '<div class="alert alert-success">Manual Configuration added successfully</div>');
            }
            redirect('admin/naac/manual_configuration');
        }
    }

    public function delete_manual_configuration($id)
    {
        $this->naac_manual_configuration_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Manual Configuration deleted successfully</div>');
        redirect('admin/naac/manual_configuration');
    }

    public function iiqa()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/iiqa');
        $data['title'] = $this->lang->line('naac_iiqa');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/iiqa', $data);
        $this->load->view('layout/footer', $data);
    }

    public function ssr()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/ssr');
        $data['title'] = $this->lang->line('naac_ssr');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/ssr', $data);
        $this->load->view('layout/footer', $data);
    }

    public function aqar()
    {
        $this->session->set_userdata('top_menu', 'NAAC');
        $this->session->set_userdata('sub_menu', 'naac/aqar');
        $data['title'] = $this->lang->line('naac_aqar');
        $this->load->view('layout/header', $data);
        $this->load->view('admin/naac/aqar', $data);
        $this->load->view('layout/footer', $data);
    }
}
