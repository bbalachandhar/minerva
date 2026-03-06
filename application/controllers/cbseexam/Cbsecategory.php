<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cbsecategory extends MY_Addon_CBSEController
{

    public function __construct()
    {
        parent::__construct();
    
        $this->current_session    = $this->setting_model->getCurrentSession();
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model("cbseexam/cbse_category_model");
    }

    public function index()
    {     
		if (!$this->rbac->hasPrivilege('cbse_exam_category', 'can_view')) {
            access_denied();
        }
		
        $this->session->set_userdata('subsub_menu', 'cbseexam/cbsecategory/index');
        $data['title'] = 'category List';
        $result      = $this->cbse_category_model->get();
        $data['category_data'] = $result;
        $this->form_validation->set_rules('category_name', $this->lang->line('category_name'), 'trim|required|xss_clean');  

         $this->form_validation->set_rules(
            'category_name', $this->lang->line('category_name'), array(
                'required',
                array('category_exists', array($this->cbse_category_model, 'category_exists')),
            )
        );

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('cbseexam/cbsecategory/index', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'name' => $this->input->post('category_name'),
            );
            $this->cbse_category_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('cbseexam/cbsecategory');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('cbse_exam_category', 'can_delete')) {
            access_denied();
        }
		
        $this->cbse_category_model->remove($id);           
        redirect('cbseexam/cbsecategory');
    }

    public function edit($id)
    {
      
		if (!$this->rbac->hasPrivilege('cbse_exam_category', 'can_edit')) {
            access_denied();
        }
        $category_data_list      = $this->cbse_category_model->get();
        $data['category_data_list'] = $category_data_list;
        $data['title']       = 'Edit Category';
        $data['id']          = $id;
        $category_data             = $this->cbse_category_model->get($id);
        $data['category_data']     = $category_data;

        $this->form_validation->set_rules(
            'category_name', $this->lang->line('category_name'), array(
                'required',
                array('category_exists', array($this->cbse_category_model, 'category_exists')),
            )
        );

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('cbseexam/cbsecategory/edit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'id'      => $id,
                'name' => $this->input->post('category_name'),
            );
            $this->cbse_category_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('cbseexam/cbsecategory');
        }
    }

}
