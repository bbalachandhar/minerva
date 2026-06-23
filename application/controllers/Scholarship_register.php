<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scholarship_register extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form', 'security', 'language']);
        $this->load->library(['form_validation', 'session']);
        $this->load->model('language_model');
        $this->load->model('Setting_model', 'setting_model');
        $this->load->model('Onlinestudent_model', 'onlinestudent_model');
        $this->load->model('Onlineadmissioncourses_model');
        $this->load->library('customlib');
        $raw = $this->setting_model->getSetting();
        if (is_array($raw) && isset($raw[0])) {
            $this->sch_setting = is_object($raw[0]) ? (array) $raw[0] : $raw[0];
        } elseif (is_object($raw)) {
            $this->sch_setting = (array) $raw;
        } else {
            $this->sch_setting = $raw;
        }
    }

    public function index()
    {
        $session_id = $this->sch_setting['session_id'];

        $scholarship_exams = $this->db
            ->select('id, exam, scholarship_courses, exam_from, exam_to')
            ->where('is_scholarship', 1)
            ->where('session_id', $session_id)
            ->where('is_active', 1)
            ->get('onlineexam')->result();

        if (empty($scholarship_exams)) {
            $data['no_exams'] = true;
            $data['courses'] = [];
        } else {
            $data['no_exams'] = false;
            $all_course_ids = [];
            foreach ($scholarship_exams as $exam) {
                if (!empty($exam->scholarship_courses)) {
                    $ids = array_filter(array_map('intval', explode(',', $exam->scholarship_courses)));
                    $all_course_ids = array_merge($all_course_ids, $ids);
                }
            }
            $all_course_ids = array_unique($all_course_ids);

            if (!empty($all_course_ids)) {
                $data['courses'] = $this->db
                    ->where_in('id', $all_course_ids)
                    ->where('is_active', 1)
                    ->order_by('sort_order')
                    ->get('online_admission_courses')->result();
            } else {
                $data['courses'] = $this->Onlineadmissioncourses_model->getActiveCourses();
            }
        }

        $data['sch_setting'] = $this->sch_setting;
        $data['title'] = 'Scholarship Exam Registration';

        $this->load->view('scholarship/register', $data);
    }

    public function submit()
    {
        $this->form_validation->set_rules('firstname', 'First Name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('mobile', 'Mobile', 'required|trim|xss_clean');
        $this->form_validation->set_rules('preferred_course_id', 'Preferred Course', 'required');
        $this->form_validation->set_rules('gender', 'Gender', 'required');

        if ($this->form_validation->run() == false) {
            $this->index();
            return;
        }

        $session_id = $this->sch_setting['session_id'];
        $preferred_course_id = (int) $this->input->post('preferred_course_id');

        // Generate unique reference number
        do {
            $reference_no = mt_rand(100000, 999999);
            $exists = $this->onlinestudent_model->checkreferenceno($reference_no);
        } while (!empty($exists));

        $password_plain = $reference_no . '@ApplicantPortal' . date('Y');
        $password_hash  = md5($password_plain);

        // Create online_admissions record with source=scholarship
        $admission_data = [
            'session_id'          => $session_id,
            'reference_no'        => $reference_no,
            'firstname'           => $this->input->post('firstname'),
            'lastname'            => $this->input->post('lastname') ?: '',
            'middlename'          => '',
            'email'               => $this->input->post('email') ?: '',
            'mobileno'            => $this->input->post('mobile'),
            'gender'              => $this->input->post('gender'),
            'dob'                 => $this->input->post('dob') ? date('Y-m-d', strtotime($this->input->post('dob'))) : null,
            'father_name'         => $this->input->post('parent_name') ?: '',
            'guardian_name'       => $this->input->post('parent_name') ?: '',
            'guardian_phone'      => $this->input->post('parent_mobile') ?: '',
            'guardian_is'         => 'father',
            'applicant_password'  => $password_hash,
            'form_status'         => 1,
            'paid_status'         => 1,
            'admission_status'    => 'active',
            'source'              => 'scholarship',
            'admission_course_id' => $preferred_course_id,
            'created_at'          => date('Y-m-d H:i:s'),
        ];

        // Handle school info via note field
        $school_info = trim(($this->input->post('school_name') ?: '') . ', ' . ($this->input->post('school_city') ?: ''), ', ');
        if (!empty($school_info)) {
            $admission_data['previous_school'] = $school_info;
        }

        $this->db->insert('online_admissions', $admission_data);
        $admission_id = $this->db->insert_id();

        // Find all scholarship exams linked to this course and auto-assign
        $exams = $this->db
            ->select('id')
            ->where('is_scholarship', 1)
            ->where('is_active', 1)
            ->where('session_id', $session_id)
            ->like('scholarship_courses', (string) $preferred_course_id)
            ->get('onlineexam')->result();

        $assigned_count = 0;
        foreach ($exams as $exam) {
            $course_ids = array_map('intval', explode(',', $this->db->select('scholarship_courses')->where('id', $exam->id)->get('onlineexam')->row()->scholarship_courses ?? ''));
            if (in_array($preferred_course_id, $course_ids)) {
                $exists = $this->db->where('onlineexam_id', $exam->id)->where('online_admission_id', $admission_id)->get('onlineexam_students')->num_rows();
                if ($exists == 0) {
                    $this->db->insert('onlineexam_students', [
                        'onlineexam_id'       => $exam->id,
                        'online_admission_id' => $admission_id,
                        'candidate_type'      => 'applicant',
                        'is_attempted'        => 0,
                    ]);
                    $assigned_count++;
                }
            }
        }

        $data = [
            'reference_no'    => $reference_no,
            'password'        => $password_plain,
            'firstname'       => $this->input->post('firstname'),
            'assigned_exams'  => $assigned_count,
            'sch_setting'     => $this->sch_setting,
            'title'           => 'Registration Successful',
        ];

        $this->load->view('scholarship/register_success', $data);
    }
}
