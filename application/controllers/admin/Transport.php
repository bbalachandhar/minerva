<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Transport extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array("transportfee_model", "routepickuppoint_model"));
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->library("datatables");
    }

    public function feemaster()
    {
        if (!($this->rbac->hasPrivilege('transport_fees_master', 'can_view'))) {
            access_denied();
        }
        
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'transport/feemaster');
        $current_session               = $this->setting_model->getCurrentSession();
        $data                          = array();

        $data['title']                 = 'student fees';
        
        $data['transportfees'] = $this->transportfee_model->transportfesstype($current_session);
        if (empty($data['transportfees'])) {
            $data['transportfees'] = array();
        } else {
            $data['transportfees'] = array($data['transportfees']);
        }
        
        $route_pickup_point_id         = $this->input->post('route_pickup_point_id');
        $data['route_pickup_point_id'] = $route_pickup_point_id;
        $route_pickup_point            = $this->routepickuppoint_model->get($route_pickup_point_id);
        $data['route_pickup_point']    = $route_pickup_point;

        $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|xss_clean');
        $this->form_validation->set_rules('fine_type', $this->lang->line('fine_type'), 'trim|xss_clean');
        $fine_type = $this->input->post('fine_type');
        if($fine_type == 'fix'){
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fix_amount'), 'trim|required|numeric|xss_clean'); 
        }elseif($fine_type == 'percentage'){
            $this->form_validation->set_rules('percentage', $this->lang->line('percentage'), 'trim|required|numeric|xss_clean'); 
        }            
        
        if ($this->form_validation->run() == true) {
            $insert_data = array();
            $update_data = array();

            $prev_id = $this->input->post('prev_id');
            $fine_amount    =   empty2null($this->input->post('fine_amount'));
            
            if($fine_amount){
               $fine_amount =  convertCurrencyFormatToBaseAmount($fine_amount);                    
            }
            
            if ($prev_id > 0) {
                
                $old_update                    = array();
                $old_update['id']              = $prev_id;
                $old_update['due_date']        = $this->customlib->dateFormatToYYYYMMDD($this->input->post('due_date'));
                $old_update['fine_type']       = $this->input->post('fine_type');
                $old_update['fine_percentage'] = empty2null($this->input->post('percentage'));
                $old_update['fine_amount']     = $fine_amount;
                $old_update['session_id']      = $current_session;
                $update_data[]                 = $old_update;

            } else {

                $new_insert                    = array();
                $new_insert['due_date']        = $this->customlib->dateFormatToYYYYMMDD($this->input->post('due_date'));
                $new_insert['fine_type']       = $this->input->post('fine_type');
                $new_insert['fine_percentage'] = empty2null($this->input->post('percentage'));
                $new_insert['fine_amount']     = $fine_amount;
                $new_insert['session_id']      = $current_session;
                $insert_data[]                 = $new_insert;
                
            }

            $this->transportfee_model->add($insert_data, $update_data);
            $this->session->set_flashdata('msg', $this->lang->line('success_message'));
            redirect('admin/transport/feemaster');
        }

        $this->load->view('layout/header');
        $this->load->view('admin/transport/feemaster', $data);
        $this->load->view('layout/footer');
    }

    public function bulk_upload()
    {
        if (!$this->rbac->hasPrivilege('transport_bulk_upload', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'transport/bulk_upload');
        $data['title'] = $this->lang->line('bulk_upload_transport');

        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/transport/bulk_upload', $data);
            $this->load->view('layout/footer', $data);
        } else {
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('csvreader');
                    $result = $this->csvreader->parse_file($file);

                    if (!empty($result)) {
                        $this->route_model->bulk_import_transport($result);
                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('records_imported_successfully') . '</div>');
                        redirect('admin/transport/bulk_upload');
                    } else {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('no_record_found') . '</div>');
                        redirect('admin/transport/bulk_upload');
                    } 
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('please_upload_csv_file_only') . '</div>');
                    redirect('admin/transport/bulk_upload');
                }
            }
        }
    }

    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_transport_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_transport_sample_file.csv';
        force_download($name, $data);
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array(
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt'
            );
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

}
