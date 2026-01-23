<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Enquiry extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('form_validation');
        $this->load->model("enquiry_model");
        $this->load->model("setting_model");
        $this->load->model("class_model");
        $this->load->model("staff_model");
        $this->load->model("language_model");
        $this->load->model("notificationsetting_model");
        $this->load->library('customlib'); // Load customlib library
        $this->load->library('mailsmsconf');
        $this->load->helper('url');

        // Initialize sch_setting_detail as it's used by Mailsmsconf
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        // Load helpers and libraries
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->helper('captcha');

        // Get header image
        $header_footer = $this->setting_model->get_printheader();
        $data['header_image'] = '';
        if ($header_footer) {
            foreach($header_footer as $head_foot){
                if($head_foot['print_type'] == 'general_purpose'){
                    $data['header_image'] = $head_foot['header_image'];
                    break;
                }
            }
        }

        // Form validation rules
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact', 'Phone', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|xss_clean');
        $this->form_validation->set_rules('source', 'Source', 'trim|required|xss_clean');
        $this->form_validation->set_rules('class', 'Class', 'trim|required|xss_clean');
        $this->form_validation->set_rules('reference_name', 'Reference Name', 'trim|xss_clean');
        $this->form_validation->set_rules('reference_contact', 'Reference Contact', 'trim|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            // Load dropdown data
            $data['class_list'] = $this->class_model->get();
            $data['sourcelist'] = $this->enquiry_model->getComplaintSource();
            $data['references'] = $this->enquiry_model->get_reference();
            
            $data['main_content'] = 'enquiry/index';
            $this->load->view('enquiry/enquiry_template', $data);
        } else {
            // Save the enquiry
            $enquiry = array(
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'address'        => $this->input->post('address'),
                'reference'      => $this->input->post('reference'),
                'reference_name' => $this->input->post('reference_name'),
                'reference_contact' => $this->input->post('reference_contact'),
                'date'           => date('Y-m-d'),
                'description'    => $this->input->post('description'),
                'follow_up_date' => date('Y-m-d'),
                'note'           => $this->input->post('note'),
                'source'         => $this->input->post('source'),
                'email'          => $this->input->post('email'),
                'class_id'       => $this->input->post('class'),
                'no_of_child'    => 1,
                'created_by'     => 1,
                'status'         => 'active'
            );
            $this->enquiry_model->add($enquiry);
            
            // Send email notification
            $sender_details = array(
                'name'           => $enquiry['name'], // Changed from 'firstname' to 'name'
                'lastname'       => 'Enquiry', // Using a generic placeholder for lastname
                'email'          => $enquiry['email'],
                'date'           => $enquiry['date'],
                'reference_no'   => 'ENQ-' . time(), // Added for reference number
                'contact'        => $enquiry['contact'],
                'source'         => $enquiry['source'],
                'class'          => $enquiry['class_id'],
                'reference'      => $enquiry['reference'],
                'reference_name' => $enquiry['reference_name'],
                'reference_contact' => $enquiry['reference_contact']
            );
            $this->mailsmsconf->mailsms('enquiry_form_submission', $sender_details);

            $this->session->set_flashdata('success_message', 'Your enquiry has been submitted successfully. We will get back to you shortly.');
            redirect('enquiry/success');
        }
    }

    public function success()
    {
        $data['main_content'] = 'enquiry/success';
        $this->load->view('enquiry/enquiry_template', $data);
    }




}
?>
