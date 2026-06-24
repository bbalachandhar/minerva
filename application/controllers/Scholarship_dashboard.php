<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Scholarship_dashboard extends CI_Controller
{
    protected $sch_setting_detail;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
        $this->load->model('onlinestudent_model');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    private function getApplicant()
    {
        $reference_no = $this->session->userdata('validlogin');
        if (empty($reference_no)) {
            return null;
        }
        $ref_clean = preg_replace('/\s+/', '', $reference_no);
        $this->db->select('oa.*, IFNULL(oac.course_name, "N/A") as course_name');
        $this->db->from('online_admissions oa');
        $this->db->join('online_admission_courses oac', 'oac.id = COALESCE(oa.admission_course_id, oa.ug_course_id)', 'left');
        $this->db->where("REPLACE(oa.reference_no, ' ', '') = " . $this->db->escape($ref_clean), null, false);
        $this->db->where('oa.source', 'scholarship');
        $applicant = $this->db->get()->row();
        return $applicant;
    }

    private function getApplicantArray()
    {
        $reference_no = $this->session->userdata('validlogin');
        if (empty($reference_no)) {
            return null;
        }
        $admission_id = $this->onlinestudent_model->getidbyrefno($reference_no);
        if (empty($admission_id)) {
            return null;
        }
        return $this->onlinestudent_model->get($admission_id);
    }

    public function index()
    {
        $applicant = $this->getApplicant();
        if (empty($applicant)) {
            $this->session->unset_userdata('validlogin');
            redirect('site/applicantlogin');
            return;
        }

        $has_schema = $this->db->field_exists('candidate_type', 'onlineexam_students')
                      && $this->db->field_exists('online_admission_id', 'onlineexam_students');
        $assigned_exams = array();
        if ($has_schema) {
            $assigned_exams = $this->db
                ->select('os.is_attempted, os.id as onlineexam_student_id, oe.id, oe.exam, oe.exam_from, oe.exam_to, oe.duration, oe.attempt, oe.publish_result, oe.publish_result_no_answers, oe.is_quiz, oe.show_result_immediately')
                ->from('onlineexam_students os')
                ->join('onlineexam oe', 'oe.id = os.onlineexam_id')
                ->where('os.online_admission_id', $applicant->id)
                ->where('os.candidate_type', 'applicant')
                ->order_by('oe.exam_from', 'ASC')
                ->get()
                ->result();
        }

        $data = array(
            'applicant_info'  => $applicant,
            'assigned_exams'  => $assigned_exams,
            'sch_setting'     => $this->sch_setting_detail,
            'sch_name'        => $this->sch_setting_detail->name ?? 'Scholarship Portal',
            'title'           => 'Scholarship Exam Portal',
        );

        $this->load->view('layout/scholarship/header', $data);
        $this->load->view('scholarship/dashboard', $data);
        $this->load->view('layout/scholarship/footer', $data);
    }

    public function exam_list()
    {
        $applicant = $this->getApplicantArray();
        if (empty($applicant)) {
            redirect('scholarship_dashboard');
            return;
        }

        $this->load->model('onlineexam_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">Online exam is not enabled yet.</div>');
            redirect('scholarship_dashboard');
            return;
        }

        $applicant_obj = new stdClass();
        $applicant_obj->firstname    = $applicant['firstname'];
        $applicant_obj->lastname     = $applicant['lastname'] ?? '';
        $applicant_obj->reference_no = $applicant['reference_no'];

        $data = array(
            'sch_setting'    => $this->sch_setting_detail,
            'sch_name'       => $this->sch_setting_detail->name ?? 'Scholarship Portal',
            'applicant'      => $applicant,
            'applicant_info' => $applicant_obj,
            'onlineexam'     => $this->onlineexam_model->getApplicantexam($applicant['id']),
            'reference_no'   => $applicant['reference_no'],
            'title'          => 'My Exams',
            'portal_prefix'  => 'scholarship_dashboard',
        );

        $this->load->view('layout/scholarship/header', $data);
        $this->load->view('scholarship/exam_list', $data);
        $this->load->view('layout/scholarship/footer', $data);
    }

    public function exam_view($id)
    {
        $applicant = $this->getApplicantArray();
        if (empty($applicant)) {
            redirect('scholarship_dashboard');
            return;
        }

        $this->load->model('onlineexam_model');
        $this->load->model('onlineexamresult_model');
        $this->load->model('filetype_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            redirect('scholarship_dashboard/exam_list');
            return;
        }

        $exam                 = $this->onlineexam_model->getexamdetails($id);
        $online_exam_validate = $this->onlineexam_model->examapplicantID($applicant['id'], $id);
        if (empty($exam) || empty($online_exam_validate)) {
            show_404();
        }

        if (strtotime(date('Y-m-d H:i:s')) < strtotime($exam->exam_from)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning"><i class="fa fa-clock-o"></i> The exam has not started yet. Scheduled from <strong>' . $this->customlib->dateyyyymmddToDateTimeformat($exam->exam_from, false) . '</strong>.</div>');
            redirect('scholarship_dashboard/exam_list');
        }

        if (!empty($online_exam_validate->is_attempted)) {
            $this->db->where('onlineexam_student_id', $online_exam_validate->id);
            $attempt_count = (int)$this->db->count_all_results('onlineexam_attempts');
            $max_attempts  = (int)$exam->attempt;
            if ($max_attempts === 0 || $attempt_count < $max_attempts) {
                $this->db->where('onlineexam_student_id', $online_exam_validate->id);
                $this->db->delete('onlineexam_student_results');
                $this->db->where('id', $online_exam_validate->id);
                $this->db->update('onlineexam_students', array('is_attempted' => 0));
                $online_exam_validate->is_attempted = 0;
            }
        }

        $applicant_obj = new stdClass();
        $applicant_obj->firstname    = $applicant['firstname'];
        $applicant_obj->lastname     = $applicant['lastname'] ?? '';
        $applicant_obj->reference_no = $applicant['reference_no'];

        $filetype = $this->filetype_model->get();

        $data = array(
            'sch_setting'         => $this->sch_setting_detail,
            'sch_name'            => $this->sch_setting_detail->name ?? 'Scholarship Portal',
            'question_true_false' => $this->config->item('question_true_false'),
            'exam'                => $exam,
            'applicant'           => $applicant,
            'applicant_info'      => $applicant_obj,
            'questionOpt'         => $this->customlib->getQuesOption(),
            'online_exam_validate'=> $online_exam_validate,
            'question_result'     => $this->onlineexamresult_model->getResultByStudent($online_exam_validate->id, $online_exam_validate->onlineexam_id),
            'result_prepare'      => $this->onlineexamresult_model->checkResultPrepare($online_exam_validate->id),
            'allowed_extension'   => array_map('trim', array_map('strtolower', explode(',', $filetype->file_extension))),
            'allowed_mime_type'   => array_map('trim', array_map('strtolower', explode(',', $filetype->file_mime))),
            'allowed_upload_size' => $filetype->file_size,
            'title'               => 'Exam: ' . $exam->exam,
            'portal_prefix'       => 'scholarship_dashboard',
        );

        $this->load->view('layout/scholarship/header', $data);
        $this->load->view('public_admission/exam_view', $data);
        $this->load->view('layout/scholarship/footer', $data);
    }

    public function getExamForm()
    {
        $applicant = $this->getApplicantArray();
        if (empty($applicant)) {
            echo json_encode(array('status' => 1, 'message' => 'Session expired'));
            return;
        }

        $this->load->model('onlineexam_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            echo json_encode(array('status' => 1, 'message' => 'Exam schema missing'));
            return;
        }

        $recordid = $this->input->post('recordid');
        $exam     = $this->onlineexam_model->getexamdetails($recordid);
        if (empty($exam)) {
            echo json_encode(array('status' => 1, 'message' => 'Exam not found'));
            return;
        }

        $data             = array();
        $data['exam']     = $exam;
        $data['questions']= $this->onlineexam_model->getExamQuestions($recordid, $exam->is_random_question);
        $onlineexam_student = $this->onlineexam_model->examapplicantID($applicant['id'], $exam->id);
        if (empty($onlineexam_student)) {
            echo json_encode(array('status' => 1, 'message' => 'Not assigned to this exam'));
            return;
        }

        $data['onlineexam_student_id'] = $onlineexam_student;
        $question_status = 0;
        $now = strtotime(date('Y-m-d H:i:s'));
        if ($now < strtotime($exam->exam_from)) {
            $question_status = 1;
            $data['exam_not_started'] = true;
        } else if ($now >= strtotime($exam->exam_to)) {
            $question_status = 1;
        } else if ($onlineexam_student->is_attempted) {
            $this->db->where('onlineexam_student_id', $onlineexam_student->id);
            $attempt_count = (int)$this->db->count_all_results('onlineexam_attempts');
            $max_attempts  = (int)$exam->attempt;
            if ($max_attempts > 0 && $attempt_count >= $max_attempts) {
                $question_status = 1;
            }
        }

        $data['question_status'] = $question_status;
        $data['questionOpt']     = $this->customlib->getQuesOption();
        $pag_content = $this->load->view('user/onlineexam/_searchQuestionByExamID', $data, true);

        $total_remaining_seconds = round((strtotime($exam->exam_to) - strtotime(date('Y-m-d H:i:s'))) / 3600 * 60 * 60, 1);
        $exam_duration = ($total_remaining_seconds < getSecondsFromHMS($exam->duration)) ? getHMSFromSeconds($total_remaining_seconds) : $exam->duration;

        echo json_encode(array('status' => 0, 'exam' => $exam, 'duration' => $exam_duration, 'page' => $pag_content, 'question_status' => $question_status, 'total_question' => count($data['questions'])));
    }

    public function hall_ticket($exam_id = null)
    {
        $applicant = $this->getApplicantArray();
        if (empty($applicant)) {
            redirect('scholarship_dashboard');
            return;
        }

        $exam_id = (int) $exam_id;
        if ($exam_id <= 0) {
            redirect('scholarship_dashboard/exam_list');
            return;
        }

        $this->load->model('onlineexam_model');

        $this->db->where('os.onlineexam_id', $exam_id);
        $this->db->where('os.online_admission_id', (int) $applicant['id']);
        $this->db->where('os.candidate_type', 'applicant');
        $this->db->from('onlineexam_students os');
        $this->db->join('onlineexam oe', 'oe.id = os.onlineexam_id', 'inner');
        $this->db->select('os.id as onlineexam_student_id, os.is_attempted, oe.*');
        $exam_assignment = $this->db->get()->row();

        if (empty($exam_assignment)) {
            redirect('scholarship_dashboard/exam_list');
            return;
        }

        $this->db->where('print_type', 'online_exam');
        $print_header = $this->db->get('print_headerfooter')->row();
        $sch_setting  = $this->setting_model->getSetting();

        $data = array(
            'applicant'    => $applicant,
            'exam'         => $exam_assignment,
            'print_header' => $print_header,
            'sch_setting'  => $sch_setting,
        );

        $this->load->view('public_admission/hall_ticket', $data);
    }

    public function save_exam()
    {
        $applicant = $this->getApplicantArray();
        if (empty($applicant)) {
            redirect('scholarship_dashboard');
        }

        $this->load->model('onlineexam_model');
        $this->load->model('onlineexamresult_model');

        if (!$this->onlineexam_model->hasApplicantExamSchema()) {
            redirect('scholarship_dashboard/exam_list', 'refresh');
        }

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $onlineexam_student_id = (int) $this->input->post('onlineexam_student_id');
            if ($onlineexam_student_id <= 0) {
                redirect('scholarship_dashboard/exam_list', 'refresh');
            }

            $this->db->where('id', $onlineexam_student_id);
            $this->db->where('online_admission_id', $applicant['id']);
            $this->db->where('candidate_type', 'applicant');
            $exam_assignment = $this->db->get('onlineexam_students')->row();
            if (empty($exam_assignment)) {
                redirect('scholarship_dashboard/exam_list', 'refresh');
            }

            if ((int) $exam_assignment->is_attempted === 1) {
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => false, 'message' => 'Exam already submitted.']);
                    return;
                }
                redirect('scholarship_dashboard/exam_list', 'refresh');
            }

            $this->db->where('onlineexam_student_id', $onlineexam_student_id);
            $existing = (int) $this->db->count_all_results('onlineexam_student_results');
            if ($existing > 0) {
                $this->onlineexam_model->updateExamResult($onlineexam_student_id);
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => false, 'message' => 'Exam already submitted.']);
                    return;
                }
                redirect('scholarship_dashboard/exam_list', 'refresh');
            }

            $total_rows = $this->input->post('total_rows');
            if (!empty($total_rows)) {
                $save_result = array();
                foreach ($total_rows as $row_value) {
                    $q_type = $_POST['question_type_' . $row_value];
                    if ($q_type == "singlechoice" || $q_type == "true_false") {
                        if (isset($_POST['radio' . $row_value])) {
                            $save_result[] = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => $_POST['radio' . $row_value],
                                'attachment_name'        => '',
                                'attachment_upload_name' => '',
                            );
                        }
                    } elseif ($q_type == "multichoice") {
                        if (isset($_POST['checkbox' . $row_value])) {
                            $save_result[] = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => json_encode($_POST['checkbox' . $row_value]),
                                'attachment_name'        => '',
                                'attachment_upload_name' => '',
                            );
                        }
                    } elseif ($q_type == "descriptive") {
                        if (isset($_POST['answer' . $row_value]) || (isset($_FILES["attachment" . $row_value]) && !empty($_FILES["attachment" . $row_value]['name']))) {
                            $file_name = '';
                            $upload_file_name = '';
                            if (isset($_FILES["attachment" . $row_value]) && !empty($_FILES["attachment" . $row_value]['name'])) {
                                $file_name        = $_FILES["attachment" . $row_value]["name"];
                                $fileInfo         = pathinfo($file_name);
                                $upload_file_name = time() . uniqid(rand()) . '.' . $fileInfo['extension'];
                                $upload_dir = "./uploads/onlinexam_images/";
                                $this->customlib->ensureDirectoryExists($upload_dir);
                                move_uploaded_file($_FILES["attachment" . $row_value]["tmp_name"], $upload_dir . $upload_file_name);
                            }
                            $save_result[] = array(
                                'onlineexam_student_id'  => $onlineexam_student_id,
                                'onlineexam_question_id' => $this->input->post('question_id_' . $row_value),
                                'select_option'          => $_POST['answer' . $row_value] ?? '',
                                'attachment_name'        => $file_name,
                                'attachment_upload_name' => $upload_file_name,
                            );
                        }
                    }
                }

                $this->onlineexamresult_model->add($save_result);
                $this->onlineexam_model->updateExamResult($onlineexam_student_id);
                $this->onlineexam_model->addStudentAttemts(array('onlineexam_student_id' => $onlineexam_student_id));
            } else {
                $this->onlineexam_model->updateExamResult($onlineexam_student_id);
                $this->onlineexam_model->addStudentAttemts(array('onlineexam_student_id' => $onlineexam_student_id));
            }

            if ($this->input->is_ajax_request()) {
                $exam_data = $this->db->get_where('onlineexam', ['id' => $exam_assignment->onlineexam_id])->row();
                $publish_result = ($exam_data && $exam_data->is_quiz && !empty($exam_data->show_result_immediately)) ? 1 : (($exam_data && (int)$exam_data->publish_result === 1) ? 1 : 0);

                $this->db->select('COUNT(oq.id) AS total_questions, SUM(oq.marks) AS total_marks, SUM(CASE WHEN osr.select_option = q.correct THEN oq.marks ELSE 0 END) AS obtained_marks, SUM(CASE WHEN osr.select_option = q.correct THEN 1 ELSE 0 END) AS correct_count, COUNT(osr.id) AS answered', false);
                $this->db->from('onlineexam_questions oq');
                $this->db->join('questions q', 'q.id = oq.question_id', 'inner');
                $this->db->join('onlineexam_student_results osr', 'osr.onlineexam_question_id = oq.id AND osr.onlineexam_student_id = ' . (int)$onlineexam_student_id, 'left');
                $this->db->where('oq.onlineexam_id', (int)$exam_assignment->onlineexam_id);
                $score = $this->db->get()->row();

                echo json_encode([
                    'success'         => true,
                    'publish_result'  => $publish_result,
                    'total_questions' => (int)($score->total_questions ?? 0),
                    'total_marks'     => round((float)($score->total_marks ?? 0), 2),
                    'obtained_marks'  => round((float)($score->obtained_marks ?? 0), 2),
                    'correct'         => (int)($score->correct_count ?? 0),
                    'answered'        => (int)($score->answered ?? 0),
                ]);
                return;
            }

            redirect('scholarship_dashboard/exam_list', 'refresh');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('validlogin');
        redirect('site/applicantlogin');
    }
}
