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
        $this->load->model("Onlineadmissioncourses_model");
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
        $this->form_validation->set_rules('contact', 'Phone', 'trim|required|numeric|min_length[10]|max_length[10]|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|xss_clean');
        $this->form_validation->set_rules('source', 'Source', 'trim|required|xss_clean');
        $this->form_validation->set_rules('admission_course_id', 'Course', 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            // Load dropdown data
            $data['ug_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year');
            $data['ug_lateral_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral');
            $data['pg_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year');
            $data['sourcelist'] = $this->enquiry_model->getComplaintSource();
            $data['references'] = $this->enquiry_model->get_reference();
            $data['prefill_source'] = $this->input->get('source', TRUE);
            
            $data['main_content'] = 'enquiry/index';
            $this->load->view('enquiry/enquiry_template', $data);
        } else {
            // Save the enquiry
            // Generate unique reference number
            $reference_no = 'ENQ-' . date('YmdHis') . rand(100,999);
            
            // Get course metadata and derive course_level
            $admission_course_id = $this->input->post('admission_course_id');
            $course_data = $this->Onlineadmissioncourses_model->getById($admission_course_id);
            $course_level = $course_data ? $course_data['course_level'] : null;
            $admission_type = $course_data ? $course_data['admission_type'] : null;
            
            $city_raw = $this->input->post('city');
            $city = ($city_raw === 'Others') ? $this->input->post('city_custom') : $city_raw;

            // Get first valid staff ID for created_by (public form has no session user)
            $first_staff = $this->db->select('id')->from('staff')->order_by('id', 'ASC')->limit(1)->get()->row();
            $created_by = $first_staff ? (int)$first_staff->id : null;

            $enquiry = array(
                'session_id'     => $this->setting_model->getCurrentSession(),
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'address'        => $this->input->post('address') ?: ''
                'state'          => $this->input->post('state'),
                'city'           => $city,
                'reference'      => $this->input->post('reference') ?: '',
                'date'           => date('Y-m-d'),
                'description'    => $this->input->post('description') ?: '',
                'follow_up_date' => date('Y-m-d'),
                'note'           => $this->input->post('referencer_details') ?: '',
                'source'         => $this->input->post('source'),
                'email'          => $this->input->post('email'),
                'class_id'       => null,
                'admission_course_id' => $admission_course_id,
                'course_level'   => $course_level,
                'admission_type' => $admission_type,
                'created_by'     => $created_by,
                'status'         => 'active',
                'ref_no'         => $reference_no
            );
            $this->enquiry_model->add($enquiry);

            // Send email notification
            $sender_details = array(
                'name'           => $enquiry['name'],
                'lastname'       => 'Enquiry',
                'email'          => $enquiry['email'],
                'date'           => $enquiry['date'],
                'reference_no'   => $reference_no,
                'contact'        => $enquiry['contact'],
                'source'         => $enquiry['source'],
                'class'          => $course_data ? $course_data['course_name'] : '',
                'reference'      => $enquiry['reference'],
                'reference_name' => isset($enquiry['reference_name']) ? $enquiry['reference_name'] : '',
                'reference_contact' => isset($enquiry['reference_contact']) ? $enquiry['reference_contact'] : ''
            );
            $this->mailsmsconf->mailsms('enquiry_form_submission', $sender_details);

            $this->session->set_flashdata('success_message', 'Your enquiry has been submitted successfully. We will get back to you shortly.<br><strong>Your Reference Number: ' . $reference_no . '</strong>');
            redirect('enquiry/success');
        }
    }

    public function success()
    {
        // Get website URL from sch_settings
        $setting = $this->setting_model->getSetting();
        $data['website_url'] = isset($setting->website) ? $setting->website : base_url();
        $data['main_content'] = 'enquiry/success';
        $this->load->view('enquiry/enquiry_template', $data);
    }




}
?>
