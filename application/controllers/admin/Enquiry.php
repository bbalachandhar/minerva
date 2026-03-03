<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Enquiry extends Admin_Controller
{
    public $enquiry_status;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model("enquiry_model");
        $this->load->model("Onlineadmissioncourses_model");
        $this->config->load("payroll");
        $this->enquiry_status = $this->config->item('enquiry_status');
    }

    public function index()
    {

        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'front_office');
        $this->session->set_userdata('sub_menu', 'admin/enquiry');
        $data['class_list']     = $this->class_model->get();
        $data['department_list'] = $this->department_model->getDepartmentType();
        $data["selected_class"] = "";
        $data["selected_department"] = "";
        $data["source_select"]  = "";
        $data["status"]         = "active";
        $data['stff_list']      = $this->staff_model->get();

        if ($this->input->post('search') === 'search_filter') {
            $class                  = $this->input->post("class");
            $department_id          = $this->input->post("department_id");
            $source                 = $this->input->post("source");
            $status                 = $this->input->post("status");
            $raw_date_from          = trim((string) $this->input->post("from_date"));
            $raw_date_to            = trim((string) $this->input->post("to_date"));
            $date_from              = !empty($raw_date_from) ? date("Y-m-d", $this->customlib->datetostrtotime($raw_date_from)) : '';
            $date_to                = !empty($raw_date_to) ? date("Y-m-d", $this->customlib->datetostrtotime($raw_date_to)) : '';
            $data["source_select"]  = $source;
            $data["status"]         = $status;
            $data["selected_class"] = $class;
            $data["selected_department"] = $department_id;
            $enquiry_list           = $this->enquiry_model->searchEnquiry($class, $source, $date_from, $date_to, $status, $department_id);
        } else {
            $enquiry_list = $this->enquiry_model->getenquiry_list(null, array('active'));
        }
        
        foreach ($enquiry_list as $key => $value) {
            $follow_up                          = $this->enquiry_model->getFollowByEnquiry($value["id"]);
            $enquiry_list[$key]["followupdate"] = isset($follow_up["date"]) ? $follow_up["date"] : '';
            $enquiry_list[$key]["next_date"]    = isset($follow_up["next_date"]) ? $follow_up["next_date"] : '';
            $enquiry_list[$key]["response"]     = isset($follow_up["response"]) ? $follow_up["response"] : '';
            $enquiry_list[$key]["note"]         = isset($follow_up["note"]) ? $follow_up["note"] : '';
            $enquiry_list[$key]["followup_by"]  = isset($follow_up["followup_by"]) ? $follow_up["followup_by"] : '';
        }
   
        $data['prefill_name']    = $this->input->get('name', TRUE);
        $data['prefill_email']   = $this->input->get('email', TRUE);
        $data['prefill_contact'] = $this->input->get('mobileno', TRUE);
        
        
        foreach ($enquiry_list as $key => $value) {
            $follow_up                          = $this->enquiry_model->getFollowByEnquiry($value["id"]);
            $enquiry_list[$key]["followupdate"] = isset($follow_up["date"]) ? $follow_up["date"] : '';
            $enquiry_list[$key]["next_date"]    = isset($follow_up["next_date"]) ? $follow_up["next_date"] : '';
            $enquiry_list[$key]["response"]     = isset($follow_up["response"]) ? $follow_up["response"] : '';
            $enquiry_list[$key]["note"]         = isset($follow_up["note"]) ? $follow_up["note"] : '';
            $enquiry_list[$key]["followup_by"]  = isset($follow_up["followup_by"]) ? $follow_up["followup_by"] : '';
        }
   
        
        // enforce newest-first ordering by enquiry `date` (in case data source/order differs)
        usort($enquiry_list, function ($a, $b) {
            $da = !empty($a['date']) ? $a['date'] : '0000-00-00';
            $db = !empty($b['date']) ? $b['date'] : '0000-00-00';
            return strcmp($db, $da); // descending by YYYY-MM-DD string
        });

        $data['enquiry_list']   = $enquiry_list;
        $data['enquiry_status'] = $this->enquiry_status;
        $data['Reference']      = $this->enquiry_model->get_reference();
        $data['sourcelist']     = $this->enquiry_model->getComplaintSource();
        
        // Load course data for dropdowns
        $data['ug_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year');
        $data['ug_lateral_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral');
        $data['pg_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year');
        
        $this->load->view('layout/header');
        $this->load->view('admin/frontoffice/enquiryview', $data);
        $this->load->view('layout/footer');
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact', $this->lang->line('phone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('source', $this->lang->line('source'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('follow_up_date', $this->lang->line('next_follow_up_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('course_type', 'Course Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('admission_course_id', 'Course', 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'name'    => form_error('name'),
                'contact' => form_error('contact'),
                'source'  => form_error('source'),
                'date'    => form_error('date'),
                'follow_up_date' => form_error('follow_up_date'),
                'course_type' => form_error('course_type'),
                'admission_course_id' => form_error('admission_course_id'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            $userdata   = $this->customlib->getUserData();
            $created_by = $userdata["id"];

            // Get course metadata and derive course_level
            $admission_course_id = $this->input->post('admission_course_id');
            $course_data = $this->Onlineadmissioncourses_model->getById($admission_course_id);
            $course_level = $course_data ? $course_data['course_level'] : null;
            $admission_type = $course_data ? $course_data['admission_type'] : null;

            $reference_no = 'ENQ-' . date('YmdHis') . rand(100, 999);
            $enquiry = array(
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'address'        => $this->input->post('address'),
                'state'          => $this->input->post('state'),
                'city'           => $this->input->post('city'),
                'reference'      => $this->input->post('reference'),
                'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'    => $this->input->post('description'),
                'follow_up_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
                'note'           => $this->input->post('referencer_details'),
                'source'         => $this->input->post('source'),
                'email'          => $this->input->post('email'),
                'assigned'       => IsNullOrEmptyString($this->input->post('assigned')) ? NULL : $this->input->post('assigned'),
                'class_id'       => null, // Legacy field
                'admission_course_id' => $admission_course_id,
                'course_level'   => $course_level,
                'admission_type' => $admission_type,
                'status'         => 'active',
                'created_by'     => $created_by,
                'ref_no'         => $reference_no,
            );
            $this->enquiry_model->add($enquiry);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_delete')) {
            access_denied();
        }
        if (!empty($id)) {
            $this->enquiry_model->enquiry_delete($id);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('delete_message'));
        }
        echo json_encode($array);
    }

    public function follow_up($enquiry_id, $status, $created_by)
    {

        if (!$this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_view')) {
            access_denied();
        }
        $data['id']              = $enquiry_id;
        $data['enquiry_data']    = $this->enquiry_model->getenquiry_list($enquiry_id, $status);
        
         
        if(!empty($data['enquiry_data']['assigned'])){
            $data['assigned_staff'] = $this->staff_model->get($data['enquiry_data']['assigned']);
        
        }else{
            $data['assigned_staff'] = '';  
        } 
        $data['next_date']       = $this->enquiry_model->next_follow_up_date($enquiry_id);
        $data['created_by']      = $this->staff_model->get($created_by);
        $data['enquiry_status']  = $this->enquiry_status;
        $userdata                = $this->customlib->getUserData();
        $data['login_staff_id']  = $userdata["id"];
        $getStaffRole            = $this->customlib->getStaffRole();
        $staffrole               = json_decode($getStaffRole);
        $data['staff_role']      = $staffrole->id;
         
        $data['superadmin_rest'] = $this->session->userdata['admin']['superadmin_restriction']; 
        $this->load->view('admin/frontoffice/follow_up_modal', $data);
    }

    public function follow_up_insert()
    {
        if (!$this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('response', $this->lang->line('response'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('follow_up_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('follow_up_date', $this->lang->line('next_follow_up_date'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                'response'       => form_error('response'),
                'follow_up_date' => form_error('follow_up_date'),
                'date'           => form_error('date'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $staff_id = $this->customlib->getStaffID();

            $follow_up = array(
                'date'        => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'next_date'   => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
                'response'    => $this->input->post('response'),
                'note'        => $this->input->post('note'),
                'followup_by' => $staff_id,
                'enquiry_id'  => $this->input->post('enquiry_id'),
            );
            $this->enquiry_model->add_follow_up($follow_up);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }

        echo json_encode($array);
    }

    public function follow_up_list($id)
    {
        $data['id']             = $id;
        $data['follow_up_list'] = $this->enquiry_model->getfollow_up_list($id);
        $this->load->view('admin/frontoffice/followuplist', $data);
    }

    public function details($id, $status)
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_view')) {
            access_denied();
        }
        $data['source']       = $this->enquiry_model->getComplaintSource();
        $data['enquiry_type'] = $this->enquiry_model->get_enquiry_type();
        $data['Reference']    = $this->enquiry_model->get_reference();        
        $data['class_list']   = $this->enquiry_model->getclasses();        
        $data['enquiry_data'] = $this->enquiry_model->getenquiry_list($id, $status);
        $data['stff_list']    = $this->staff_model->get();
        
        // Load course data for dropdowns
        $data['ug_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'first_year');
        $data['ug_lateral_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('ug', 'lateral');
        $data['pg_first_year_courses'] = $this->Onlineadmissioncourses_model->getActiveCourses('pg', 'first_year');
        
        $this->load->view('admin/frontoffice/enquiryeditmodalview', $data);
    }

    public function editpost($id)
    {
        if (!$this->rbac->hasPrivilege('admission_enquiry', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('name', $this->lang->line('name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact', $this->lang->line('phone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('source', $this->lang->line('source'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('follow_up_date', $this->lang->line('next_follow_up_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('admission_course_id', 'Course', 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'name'    => form_error('name'),
                'contact' => form_error('contact'),
                'source'  => form_error('source'),
                'date'    => form_error('date'),
                'follow_up_date' => form_error('follow_up_date'),
                'admission_course_id' => form_error('admission_course_id'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            // Get course metadata and derive course_level
            $admission_course_id = $this->input->post('admission_course_id');
            $course_data = $this->Onlineadmissioncourses_model->getById($admission_course_id);
            $course_level = $course_data ? $course_data['course_level'] : null;
            $admission_type = $course_data ? $course_data['admission_type'] : null;
            
            $enquiry_update = array(
                'name'           => $this->input->post('name'),
                'contact'        => $this->input->post('contact'),
                'address'        => $this->input->post('address'),
                'state'          => $this->input->post('state'),
                'city'           => $this->input->post('city'),
                'reference'      => $this->input->post('reference'),
                'date'           => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),
                'description'    => $this->input->post('description'),
                'follow_up_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('follow_up_date'))),
                'note'           => $this->input->post('referencer_details'),
                'source'         => $this->input->post('source'),
                'email'          => $this->input->post('email'),
                'assigned'       => empty2null($this->input->post('assigned')),
                'class_id'       => null, // Legacy field
                'admission_course_id' => $admission_course_id,
                'course_level'   => $course_level,
                'admission_type' => $admission_type,
            );
            $this->enquiry_model->enquiry_update($id, $enquiry_update);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('update_message'));
        }
        echo json_encode($array);
    }

    public function follow_up_delete($follow_up_id, $enquiry_id)
    {
        if (!$this->rbac->hasPrivilege('follow_up_admission_enquiry', 'can_delete')) {
            access_denied();
        }
        $this->enquiry_model->delete_follow_up($follow_up_id);
        $data['id']             = $enquiry_id;
        $data['follow_up_list'] = $this->enquiry_model->getfollow_up_list($enquiry_id);
        $this->load->view('admin/frontoffice/followuplist', $data);
    }

    public function check_default($post_string)
    {
        return $post_string == '' ? false : true;
    }

    public function change_status()
    {
        $id     = $this->input->post("id");
        $status = $this->input->post("status");
        if (!empty($id)) {
            $data = array('id' => $id, 'status' => $status);
            $this->enquiry_model->changeStatus($data);
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => $this->lang->line('update_message'));
        }

        echo json_encode($array);
    }

    public function check_number()
    {
        $phone_number = $this->input->post("phone_number");
        $check_number = $this->enquiry_model->check_number($phone_number);
        if (!empty($check_number)) {
            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('number_is_already_exists_and_name_is') . '  ' . $check_number['name']);
        } else {
            $array = array('status' => 'fail', 'error' => '', 'message' => '');
        }
        echo json_encode($array);
    }

}
