<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Scholarshipexam extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
        $this->load->model('Onlineadmissioncourses_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('scholarship_exam', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'admissions');
        $this->session->set_userdata('sub_menu', 'admin/scholarshipexam');

        $session_id = $this->setting_model->getCurrentSession();

        $exams = $this->db
            ->select('e.*, (SELECT COUNT(*) FROM onlineexam_students es WHERE es.onlineexam_id = e.id AND es.candidate_type = "applicant") AS candidate_count')
            ->from('onlineexam e')
            ->where('e.is_scholarship', 1)
            ->where('e.session_id', $session_id)
            ->order_by('e.id', 'DESC')
            ->get()->result();

        // Get course names for display
        $all_courses = $this->Onlineadmissioncourses_model->getActiveCourses();
        $course_map = [];
        foreach ($all_courses as $c) {
            $cid = is_array($c) ? $c['id'] : $c->id;
            $cname = is_array($c) ? $c['course_name'] : $c->course_name;
            $ccode = is_array($c) ? $c['course_code'] : $c->course_code;
            $course_map[$cid] = $cname . ' (' . $ccode . ')';
        }

        $data['title']      = 'Scholarship Exams';
        $data['exams']      = $exams;
        $data['course_map'] = $course_map;
        $data['sch_setting'] = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/scholarshipexam/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function candidates($exam_id = null)
    {
        if (!$this->rbac->hasPrivilege('scholarship_exam', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'admissions');
        $this->session->set_userdata('sub_menu', 'admin/scholarshipexam');

        $exam_id = (int) $exam_id;
        $exam = $this->db->where('id', $exam_id)->where('is_scholarship', 1)->get('onlineexam')->row();
        if (!$exam) {
            show_error('Exam not found', 404);
            return;
        }

        $candidates = $this->db
            ->select('oa.id, oa.reference_no, oa.firstname, oa.lastname, oa.mobileno, oa.email, oa.created_at, oa.previous_school, oac.course_name, oac.course_code, es.is_attempted, es.rank')
            ->from('onlineexam_students es')
            ->join('online_admissions oa', 'oa.id = es.online_admission_id')
            ->join('online_admission_courses oac', 'oac.id = oa.admission_course_id', 'left')
            ->where('es.onlineexam_id', $exam_id)
            ->where('es.candidate_type', 'applicant')
            ->where('oa.source', 'scholarship')
            ->order_by('oa.id', 'DESC')
            ->get()->result();

        $data['title']      = 'Scholarship Candidates — ' . $exam->exam;
        $data['exam']       = $exam;
        $data['candidates'] = $candidates;
        $data['sch_setting'] = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);
        $this->load->view('admin/scholarshipexam/candidates', $data);
        $this->load->view('layout/footer', $data);
    }
}
