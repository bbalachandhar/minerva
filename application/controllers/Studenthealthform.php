<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Studenthealthform extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Student_health_model');
        $this->load->model('Setting_model');
        $this->load->library('media_storage');
        $this->load->helper(['url', 'form']);
    }

    private function _headerData()
    {
        $setting = $this->Setting_model->getSetting();
        $header_img = $this->Setting_model->get_general_purpose_header();
        $header_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;
        return [
            'school_name' => $setting->name ?? '',
            'header_img'  => $header_url,
        ];
    }

    public function index()
    {
        $data = $this->_headerData();
        $this->load->view('studenthealthform/index', $data);
    }

    public function form()
    {
        $data = $this->_headerData();
        $this->load->view('studenthealthform/form', $data);
    }

    public function confirmed()
    {
        $data = $this->_headerData();
        $this->load->view('studenthealthform/confirmed', $data);
    }

    public function fetch()
    {
        $admission_no = trim($this->input->post('admission_no'));
        if (!$admission_no) {
            echo json_encode(['status' => '0', 'msg' => 'Please enter an admission number.']);
            return;
        }
        $student = $this->Student_health_model->getStudentByAdmissionNo($admission_no);
        if (!$student) {
            echo json_encode(['status' => '0', 'msg' => 'Student not found. Please check the admission number.']);
            return;
        }
        $siblings = $this->Student_health_model->getSiblings($student->id, $student->father_phone);
        $record   = $this->Student_health_model->getRecord($student->id);
        echo json_encode([
            'status'   => '1',
            'student'  => $student,
            'siblings' => $siblings,
            'record'   => $record,
        ]);
    }

    public function submit()
    {
        $student_id = (int) $this->input->post('student_id');
        if (!$student_id) {
            echo json_encode(['status' => '0', 'msg' => 'Invalid submission.']); return;
        }

        $data = [
            'emergency_contact_name'     => $this->input->post('emergency_contact_name'),
            'emergency_contact_relation' => $this->input->post('emergency_contact_relation'),
            'emergency_contact_mobile'   => $this->input->post('emergency_contact_mobile'),
            'emergency_contact_alt_mobile' => $this->input->post('emergency_contact_alt_mobile'),
            'wears_spectacles'           => (int)(bool)$this->input->post('wears_spectacles'),
            'vision_difficulty'          => (int)(bool)$this->input->post('vision_difficulty'),
            'hearing_difficulty'         => (int)(bool)$this->input->post('hearing_difficulty'),
            'speech_difficulty'          => (int)(bool)$this->input->post('speech_difficulty'),
            'special_assistance'         => (int)(bool)$this->input->post('special_assistance'),
            'special_assistance_details' => $this->input->post('special_assistance_details'),
            'allergy_food'               => (int)(bool)$this->input->post('allergy_food'),
            'allergy_medication'         => (int)(bool)$this->input->post('allergy_medication'),
            'allergy_insect'             => (int)(bool)$this->input->post('allergy_insect'),
            'allergy_dust'               => (int)(bool)$this->input->post('allergy_dust'),
            'allergy_other'              => (int)(bool)$this->input->post('allergy_other'),
            'allergy_none'               => (int)(bool)$this->input->post('allergy_none'),
            'allergy_details'            => $this->input->post('allergy_details'),
            'med_asthma'                 => (int)(bool)$this->input->post('med_asthma'),
            'med_diabetes'               => (int)(bool)$this->input->post('med_diabetes'),
            'med_epilepsy'               => (int)(bool)$this->input->post('med_epilepsy'),
            'med_heart'                  => (int)(bool)$this->input->post('med_heart'),
            'med_kidney'                 => (int)(bool)$this->input->post('med_kidney'),
            'med_thyroid'                => (int)(bool)$this->input->post('med_thyroid'),
            'med_physical_disability'    => (int)(bool)$this->input->post('med_physical_disability'),
            'med_learning_difficulty'    => (int)(bool)$this->input->post('med_learning_difficulty'),
            'med_vision_impairment'      => (int)(bool)$this->input->post('med_vision_impairment'),
            'med_hearing_impairment'     => (int)(bool)$this->input->post('med_hearing_impairment'),
            'med_other'                  => (int)(bool)$this->input->post('med_other'),
            'med_details'                => $this->input->post('med_details'),
            'surgery_history'            => (int)(bool)$this->input->post('surgery_history'),
            'surgery_details'            => $this->input->post('surgery_details'),
            'current_medications'        => $this->input->post('current_medications'),
            'vaccinations_uptodate'      => $this->input->post('vaccinations_uptodate') ?: null,
            'vaccination_remarks'        => $this->input->post('vaccination_remarks'),
            'pe_fit'                     => (int)(bool)$this->input->post('pe_fit'),
            'pe_restrictions'            => $this->input->post('pe_restrictions'),
            'special_health_instructions'=> $this->input->post('special_health_instructions'),
            'has_sibling'                => (int)(bool)$this->input->post('has_sibling'),
            'sibling_details'            => $this->input->post('sibling_details'),
            'declaration_name'           => $this->input->post('declaration_name'),
            'declaration_date'           => $this->input->post('declaration_date') ?: date('Y-m-d'),
        ];

        $token = $this->Student_health_model->saveRecord($student_id, $data);
        echo json_encode([
            'status' => '1',
            'token'  => $token,
            'pdf_url'=> site_url('studenthealthform/pdf/' . $token),
        ]);
    }

    public function pdf($token = '')
    {
        if (!$token) show_404();
        $record  = $this->Student_health_model->getRecordByToken($token);
        if (!$record) show_404();
        $student = $this->Student_health_model->getStudentById($record->student_id);
        if (!$student) show_404();

        $setting    = $this->Setting_model->getSetting();
        $header_img = $this->Setting_model->get_general_purpose_header();
        $header_url = $header_img
            ? $this->media_storage->getImageURL('/uploads/print_headerfooter/general_purpose/' . $header_img)
            : null;

        $data = [
            'student'     => $student,
            'record'      => $record,
            'school_name' => $setting->name ?? '',
            'header_img'  => $header_url,
        ];

        $html = $this->load->view('studenthealthform/pdf_template', $data, true);

        $this->load->library('M_pdf');
        $mpdf = $this->m_pdf->load([
            'tempDir'        => APPPATH . 'tmp',
            'mode'           => 'utf-8',
            'default_font'   => 'dejavusans',
            'margin_left'    => 12,
            'margin_right'   => 12,
            'margin_top'     => 8,
            'margin_bottom'  => 8,
            'format'         => 'A4',
        ]);
        $mpdf->WriteHTML($html);
        $filename = 'health_form_' . $student->admission_no . '.pdf';
        $mpdf->Output($filename, 'D');
    }
}
