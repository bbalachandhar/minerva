<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Studenthealthform extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Student_health_model');
        $this->load->model('class_model');
        $this->load->model('section_model');
        $this->load->model('Setting_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('student_health_form', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'admin/studenthealthform');

        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        $data['classlist']  = $this->class_model->get();
        $data['class_id']   = $class_id;
        $data['section_id'] = $section_id;
        $data['students']   = $this->Student_health_model->getStudentsWithHealthStatus(
            $this->setting_model->getCurrentSession(),
            $class_id,
            $section_id
        );

        $this->load->view('layout/header', $data);
        $this->load->view('admin/studenthealthform/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function view($student_id)
    {
        if (!$this->rbac->hasPrivilege('student_health_form', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'admin/studenthealthform');

        $student = $this->Student_health_model->getStudentById($student_id);
        $record  = $this->Student_health_model->getRecord($student_id);

        if (!$student) show_404();

        $header_img = $this->Setting_model->get_general_purpose_header();
        $data['header_img']  = $header_img ? base_url('uploads/print_headerfooter/general_purpose/' . $header_img) : null;
        $data['student']     = $student;
        $data['record']      = $record;
        $data['can_edit']    = $this->rbac->hasPrivilege('student_health_form', 'can_edit');

        $this->load->view('layout/header', $data);
        $this->load->view('admin/studenthealthform/view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function edit($student_id)
    {
        if (!$this->rbac->hasPrivilege('student_health_form', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'admin/studenthealthform');

        $student = $this->Student_health_model->getStudentById($student_id);
        if (!$student) show_404();

        $record = $this->Student_health_model->getRecord($student_id);

        $data['student'] = $student;
        $data['record']  = $record ?: new stdClass();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/studenthealthform/edit', $data);
        $this->load->view('layout/footer', $data);
    }

    public function update($student_id)
    {
        if (!$this->rbac->hasPrivilege('student_health_form', 'can_edit')) {
            access_denied();
        }

        $p = function($k) { return $this->input->post($k); };
        $b = function($k) use ($p) { return (int)(bool)$p($k); };

        $record = [
            'emergency_contact_name'       => $p('emergency_contact_name'),
            'emergency_contact_relation'   => $p('emergency_contact_relation'),
            'emergency_contact_mobile'     => $p('emergency_contact_mobile'),
            'emergency_contact_alt_mobile' => $p('emergency_contact_alt_mobile'),
            'wears_spectacles'             => $b('wears_spectacles'),
            'vision_difficulty'            => $b('vision_difficulty'),
            'hearing_difficulty'           => $b('hearing_difficulty'),
            'speech_difficulty'            => $b('speech_difficulty'),
            'special_assistance'           => $b('special_assistance'),
            'special_assistance_details'   => $p('special_assistance_details'),
            'allergy_food'                 => $b('allergy_food'),
            'allergy_medication'           => $b('allergy_medication'),
            'allergy_insect'               => $b('allergy_insect'),
            'allergy_dust'                 => $b('allergy_dust'),
            'allergy_other'                => $b('allergy_other'),
            'allergy_none'                 => $b('allergy_none'),
            'allergy_details'              => $p('allergy_details'),
            'med_asthma'                   => $b('med_asthma'),
            'med_diabetes'                 => $b('med_diabetes'),
            'med_epilepsy'                 => $b('med_epilepsy'),
            'med_heart'                    => $b('med_heart'),
            'med_kidney'                   => $b('med_kidney'),
            'med_thyroid'                  => $b('med_thyroid'),
            'med_physical_disability'      => $b('med_physical_disability'),
            'med_learning_difficulty'      => $b('med_learning_difficulty'),
            'med_vision_impairment'        => $b('med_vision_impairment'),
            'med_hearing_impairment'       => $b('med_hearing_impairment'),
            'med_other'                    => $b('med_other'),
            'med_details'                  => $p('med_details'),
            'surgery_history'              => $b('surgery_history'),
            'surgery_details'              => $p('surgery_details'),
            'current_medications'          => $p('current_medications'),
            'vaccinations_uptodate'        => $p('vaccinations_uptodate') ?: null,
            'vaccination_remarks'          => $p('vaccination_remarks'),
            'pe_fit'                       => $b('pe_fit'),
            'pe_restrictions'              => $p('pe_restrictions'),
            'special_health_instructions'  => $p('special_health_instructions'),
            'has_sibling'                  => $b('has_sibling'),
            'sibling_details'              => $p('sibling_details'),
            'declaration_name'             => $p('declaration_name'),
            'declaration_date'             => $p('declaration_date') ?: date('Y-m-d'),
        ];

        $this->Student_health_model->saveRecord($student_id, $record);

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Health form updated successfully.</div>');
        redirect('admin/studenthealthform/view/' . $student_id);
    }
}
