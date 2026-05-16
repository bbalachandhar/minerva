<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User_coe — Student portal for CoE results, CGPA transcript, and arrear applications.
 */
class User_coe extends Student_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('coe/Coe_results_model');
        $this->load->model('coe/Coe_arrear_model');
    }

    // ------------------------------------------------------------------
    // my_results() — Student views their own published results
    // ------------------------------------------------------------------
    public function my_results()
    {
        $stuid      = $this->session->userdata('student');
        $student_id = (int) $stuid['student_id'];

        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'user_coe/my_results');

        $data['title']      = 'My Exam Results';
        $data['transcript'] = $this->Coe_results_model->getTranscript($student_id);

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/coe/my_results', $data);
        $this->load->view('layout/student/footer', $data);
    }

    // ------------------------------------------------------------------
    // my_transcript() — Printable CGPA transcript
    // ------------------------------------------------------------------
    public function my_transcript()
    {
        $stuid      = $this->session->userdata('student');
        $student_id = (int) $stuid['student_id'];

        $transcript = $this->Coe_results_model->getTranscript($student_id);

        if ($this->input->get('print')) {
            // PDF output via mPDF
            $this->load->library('M_pdf');
            $data = ['transcript' => $transcript];
            $html = $this->load->view('admin/coe/coe_results/transcript', $data, true);

            $mpdf = $this->m_pdf->load(['format' => 'A4', 'margin_top' => 15, 'margin_bottom' => 15]);
            $mpdf->WriteHTML($html, 0);
            $mpdf->Output('CGPA_Transcript_' . ($transcript['student']->admission_no ?? $student_id) . '.pdf', 'I');
            exit;
        }

        $data['title']      = 'My Transcript';
        $data['transcript'] = $transcript;

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/coe/my_results', $data);
        $this->load->view('layout/student/footer', $data);
    }

    // ------------------------------------------------------------------
    // apply_arrear() — Student applies for arrear/supplementary exam
    // GET: show failed subjects
    // POST: submit application
    // ------------------------------------------------------------------
    public function apply_arrear()
    {
        $stuid      = $this->session->userdata('student');
        $student_id = (int) $stuid['student_id'];

        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'user_coe/apply_arrear');

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $batch_exam_id = (int) $this->input->post('batch_exam_id');
            $subject_ids   = $this->input->post('subject_ids');
            $app_type      = $this->input->post('application_type') ?: 'arrear';
            $remarks       = trim($this->input->post('remarks'));

            if (!$batch_exam_id || empty($subject_ids) || !is_array($subject_ids)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please select at least one subject.</div>');
                redirect('user_coe/apply_arrear');
            }

            $submitted = 0;
            foreach ($subject_ids as $subject_id) {
                $this->Coe_arrear_model->submitApplication($student_id, $batch_exam_id, (int) $subject_id, $app_type, $remarks);
                $submitted++;
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $submitted . ' arrear application(s) submitted successfully.</div>');
            redirect('user_coe/apply_arrear');
        }

        $data['title']        = 'Apply for Arrear / Supplementary Exam';
        $data['failed_subs']  = $this->Coe_arrear_model->getFailedSubjects($student_id);
        $data['applications'] = $this->Coe_arrear_model->getStudentApplications($student_id);

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/coe/apply_arrear', $data);
        $this->load->view('layout/student/footer', $data);
    }
}
