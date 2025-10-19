<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Naac extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('naac_model');
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('rbac');
        $this->load->library('breadcrumb');
    }

    public function index() {
        $this->breadcrumb->add('Home', base_url());
        $this->breadcrumb->add('NAAC', base_url('naac'));
        $data['breadcrumb'] = $this->breadcrumb->output();

        $data['page_title'] = 'NAAC Module Home';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/index');
        $this->load->view('layout/footer');
    }

    public function criterion1() {
        $data['page_title'] = 'NAAC Criterion 1: Curricular Aspects';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion1_landing');
        $this->load->view('layout/footer');
    }

    public function criterion2() {
        $data['page_title'] = 'NAAC Criterion 2: Teaching-Learning and Evaluation';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion2_landing');
        $this->load->view('layout/footer');
    }

    public function criterion3() {
        $data['page_title'] = 'NAAC Criterion 3: Research, Innovations and Extension';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion3_landing');
        $this->load->view('layout/footer');
    }

    public function criterion4() {
        $data['page_title'] = 'NAAC Criterion 4: Infrastructure and Learning Resources';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion4_landing');
        $this->load->view('layout/footer');
    }

    public function criterion5() {
        $data['page_title'] = 'NAAC Criterion 5: Student Support and Progression';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion5_landing');
        $this->load->view('layout/footer');
    }

    public function criterion6() {
        $data['page_title'] = 'NAAC Criterion 6: Governance, Leadership and Management';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion6_landing');
        $this->load->view('layout/footer');
    }

    public function criterion7() {
        $data['page_title'] = 'NAAC Criterion 7: Institutional Values and Best Practices';
        $this->load->view('layout/header', $data);
        $this->load->view('naac/criterion7_landing');
        $this->load->view('layout/footer');
    }

    // --- KI 1.1: Curriculum Design and Development ---
    public function c1_1_curriculum_design() {
        $data['page_title'] = 'NAAC Criterion 1.1: Curriculum Design and Development';
        $data['c1_1_data'] = $this->naac_model->get_c1_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c1_1_curriculum_design/list', $data);
        $this->load->view('layout/footer');
    }

    public function c1_1_add() {
        $data['page_title'] = 'Add Entry for C1.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');
            $this->form_validation->set_rules('course_code', 'Course Code', 'required');
            $this->form_validation->set_rules('course_title', 'Course Title', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'course_code' => $this->input->post('course_code'),
                    'course_title' => $this->input->post('course_title'),
                    'po_pso_co_relevance' => $this->input->post('po_pso_co_relevance'),
                    'curriculum_development_process' => $this->input->post('curriculum_development_process'),
                    'curriculum_revision_date' => $this->input->post('curriculum_revision_date'),
                    'document_link_syllabus' => $this->input->post('document_link_syllabus'),
                    'document_link_minutes' => $this->input->post('document_link_minutes'),
                );
                $this->naac_model->add_c1_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c1_1_curriculum_design');
            } else {
                $this->load->view('layout/header', $data);
                $this->load->view('naac/c1_1_curriculum_design/add', $data);
                $this->load->view('layout/footer');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_1_curriculum_design/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C1.1';
        $data['entry'] = $this->naac_model->get_c1_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');
            $this->form_validation->set_rules('course_code', 'Course Code', 'required');
            $this->form_validation->set_rules('course_title', 'Course Title', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'course_code' => $this->input->post('course_code'),
                    'course_title' => $this->input->post('course_title'),
                    'po_pso_co_relevance' => $this->input->post('po_pso_co_relevance'),
                    'curriculum_development_process' => $this->input->post('curriculum_development_process'),
                    'curriculum_revision_date' => $this->input->post('curriculum_revision_date'),
                    'document_link_syllabus' => $this->input->post('document_link_syllabus'),
                    'document_link_minutes' => $this->input->post('document_link_minutes'),
                );
                $this->naac_model->update_c1_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c1_1_curriculum_design');
            } else {
                $this->load->view('layout/header', $data);
                $this->load->view('naac/c1_1_curriculum_design/edit', $data);
                $this->load->view('layout/footer');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_1_curriculum_design/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_1_delete($id) {
        $this->naac_model->delete_c1_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c1_1_curriculum_design');
    }

    // --- KI 1.2: Academic Flexibility ---
    public function c1_2_academic_flexibility() {
        $data['page_title'] = 'NAAC Criterion 1.2: Academic Flexibility';
        $data['c1_2_data'] = $this->naac_model->get_c1_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c1_2_academic_flexibility/list', $data);
        $this->load->view('layout/footer');
    }

    public function c1_2_add() {
        $data['page_title'] = 'Add Entry for C1.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'elective_courses_offered' => $this->input->post('elective_courses_offered'),
                    'interdisciplinary_courses_offered' => $this->input->post('interdisciplinary_courses_offered'),
                    'credit_transfer_details' => $this->input->post('credit_transfer_details'),
                    'experiential_learning_details' => $this->input->post('experiential_learning_details'),
                    'students_undertaking_internships' => $this->input->post('students_undertaking_internships'),
                    'document_link_electives' => $this->input->post('document_link_electives'),
                    'document_link_internship_policy' => $this->input->post('document_link_internship_policy'),
                );
                $this->naac_model->add_c1_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c1_2_academic_flexibility');
            } else {
                $this->load->view('layout/header', $data);
                $this->load->view('naac/c1_2_academic_flexibility/add', $data);
                $this->load->view('layout/footer');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_2_academic_flexibility/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C1.2';
        $data['entry'] = $this->naac_model->get_c1_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'elective_courses_offered' => $this->input->post('elective_courses_offered'),
                    'interdisciplinary_courses_offered' => $this->input->post('interdisciplinary_courses_offered'),
                    'credit_transfer_details' => $this->input->post('credit_transfer_details'),
                    'experiential_learning_details' => $this->input->post('experiential_learning_details'),
                    'students_undertaking_internships' => $this->input->post('students_undertaking_internships'),
                    'document_link_electives' => $this->input->post('document_link_electives'),
                    'document_link_internship_policy' => $this->input->post('document_link_internship_policy'),
                );
                $this->naac_model->update_c1_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c1_2_academic_flexibility');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_2_academic_flexibility/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_2_delete($id) {
        $this->naac_model->delete_c1_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c1_2_academic_flexibility');
    }

    // --- KI 1.3: Curriculum Enrichment ---
    public function c1_3_curriculum_enrichment() {
        $data['page_title'] = 'NAAC Criterion 1.3: Curriculum Enrichment';
        $data['c1_3_data'] = $this->naac_model->get_c1_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c1_3_curriculum_enrichment/list', $data);
        $this->load->view('layout/footer');
    }

    public function c1_3_add() {
        $data['page_title'] = 'Add Entry for C1.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'cross_cutting_issues_integrated' => $this->input->post('cross_cutting_issues_integrated'),
                    'value_added_courses_offered' => $this->input->post('value_added_courses_offered'),
                    'students_enrolled_value_added' => $this->input->post('students_enrolled_value_added'),
                    'project_field_work_details' => $this->input->post('project_field_work_details'),
                    'document_link_value_added_syllabus' => $this->input->post('document_link_value_added_syllabus'),
                    'document_link_project_reports' => $this->input->post('document_link_project_reports'),
                );
                $this->naac_model->add_c1_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c1_3_curriculum_enrichment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_3_curriculum_enrichment/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C1.3';
        $data['entry'] = $this->naac_model->get_c1_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'cross_cutting_issues_integrated' => $this->input->post('cross_cutting_issues_integrated'),
                    'value_added_courses_offered' => $this->input->post('value_added_courses_offered'),
                    'students_enrolled_value_added' => $this->input->post('students_enrolled_value_added'),
                    'project_field_work_details' => $this->input->post('project_field_work_details'),
                    'document_link_value_added_syllabus' => $this->input->post('document_link_value_added_syllabus'),
                    'document_link_project_reports' => $this->input->post('document_link_project_reports'),
                );
                $this->naac_model->update_c1_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c1_3_curriculum_enrichment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_3_curriculum_enrichment/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_3_delete($id) {
        $this->naac_model->delete_c1_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c1_3_curriculum_enrichment');
    }

    // --- KI 1.4: Feedback System ---
    public function c1_4_feedback_system() {
        $data['page_title'] = 'NAAC Criterion 1.4: Feedback System';
        $data['c1_4_data'] = $this->naac_model->get_c1_4_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c1_4_feedback_system/list', $data);
        $this->load->view('layout/footer');
    }

    public function c1_4_add() {
        $data['page_title'] = 'Add Entry for C1.4';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('stakeholder_type', 'Stakeholder Type', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'stakeholder_type' => $this->input->post('stakeholder_type'),
                    'feedback_mechanism' => $this->input->post('feedback_mechanism'),
                    'feedback_analysis_report' => $this->input->post('feedback_analysis_report'),
                    'action_taken_report' => $this->input->post('action_taken_report'),
                    'document_link_feedback_forms' => $this->input->post('document_link_feedback_forms'),
                    'document_link_analysis_report' => $this->input->post('document_link_analysis_report'),
                );
                $this->naac_model->add_c1_4_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c1_4_feedback_system');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_4_feedback_system/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_4_edit($id) {
        $data['page_title'] = 'Edit Entry for C1.4';
        $data['entry'] = $this->naac_model->get_c1_4_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('stakeholder_type', 'Stakeholder Type', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'stakeholder_type' => $this->input->post('stakeholder_type'),
                    'feedback_mechanism' => $this->input->post('feedback_mechanism'),
                    'feedback_analysis_report' => $this->input->post('feedback_analysis_report'),
                    'action_taken_report' => $this->input->post('action_taken_report'),
                    'document_link_feedback_forms' => $this->input->post('document_link_feedback_forms'),
                    'document_link_analysis_report' => $this->input->post('document_link_analysis_report'),
                );
                $this->naac_model->update_c1_4_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c1_4_feedback_system');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c1_4_feedback_system/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c1_4_delete($id) {
        $this->naac_model->delete_c1_4_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c1_4_feedback_system');
    }

    // --- KI 2.1: Student Enrolment and Profile ---
    public function c2_1_student_enrollment() {
        $data['page_title'] = 'NAAC Criterion 2.1: Student Enrolment and Profile';
        $data['c2_1_data'] = $this->naac_model->get_c2_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_1_student_enrollment/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_1_add() {
        $data['page_title'] = 'Add Entry for C2.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'total_sanctioned_seats' => $this->input->post('total_sanctioned_seats'),
                    'total_students_admitted' => $this->input->post('total_students_admitted'),
                    'students_from_other_states' => $this->input->post('students_from_other_states'),
                    'students_from_other_countries' => $this->input->post('students_from_other_countries'),
                    'reserved_category_seats_filled' => $this->input->post('reserved_category_seats_filled'),
                    'admission_process_description' => $this->input->post('admission_process_description'),
                    'document_link_admission_policy' => $this->input->post('document_link_admission_policy'),
                );
                $this->naac_model->add_c2_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_1_student_enrollment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_1_student_enrollment/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.1';
        $data['entry'] = $this->naac_model->get_c2_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'total_sanctioned_seats' => $this->input->post('total_sanctioned_seats'),
                    'total_students_admitted' => $this->input->post('total_students_admitted'),
                    'students_from_other_states' => $this->input->post('students_from_other_states'),
                    'students_from_other_countries' => $this->input->post('students_from_other_countries'),
                    'reserved_category_seats_filled' => $this->input->post('reserved_category_seats_filled'),
                    'admission_process_description' => $this->input->post('admission_process_description'),
                    'document_link_admission_policy' => $this->input->post('document_link_admission_policy'),
                );
                $this->naac_model->update_c2_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_1_student_enrollment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_1_student_enrollment/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_1_delete($id) {
        $this->naac_model->delete_c2_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_1_student_enrollment');
    }

    // --- KI 2.2: Catering to Student Diversity ---
    public function c2_2_student_diversity() {
        $data['page_title'] = 'NAAC Criterion 2.2: Catering to Student Diversity';
        $data['c2_2_data'] = $this->naac_model->get_c2_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_2_student_diversity/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_2_add() {
        $data['page_title'] = 'Add Entry for C2.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'learning_level_assessment_methods' => $this->input->post('learning_level_assessment_methods'),
                    'advanced_learner_programs' => $this->input->post('advanced_learner_programs'),
                    'slow_learner_programs' => $this->input->post('slow_learner_programs'),
                    'support_for_diverse_learners' => $this->input->post('support_for_diverse_learners'),
                    'document_link_support_policy' => $this->input->post('document_link_support_policy'),
                );
                $this->naac_model->add_c2_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_2_student_diversity');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_2_student_diversity/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.2';
        $data['entry'] = $this->naac_model->get_c2_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'learning_level_assessment_methods' => $this->input->post('learning_level_assessment_methods'),
                    'advanced_learner_programs' => $this->input->post('advanced_learner_programs'),
                    'slow_learner_programs' => $this->input->post('slow_learner_programs'),
                    'support_for_diverse_learners' => $this->input->post('support_for_diverse_learners'),
                    'document_link_support_policy' => $this->input->post('document_link_support_policy'),
                );
                $this->naac_model->update_c2_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_2_student_diversity');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_2_student_diversity/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_2_delete($id) {
        $this->naac_model->delete_c2_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_2_student_diversity');
    }

    // --- KI 2.3: Teaching-Learning Process ---
    public function c2_3_teaching_learning_process() {
        $data['page_title'] = 'NAAC Criterion 2.3: Teaching-Learning Process';
        $data['c2_3_data'] = $this->naac_model->get_c2_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_3_teaching_learning_process/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_3_add() {
        $data['page_title'] = 'Add Entry for C2.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');
            $this->form_validation->set_rules('course_code', 'Course Code', 'required');
            $this->form_validation->set_rules('teacher_name', 'Teacher Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'course_code' => $this->input->post('course_code'),
                    'teacher_name' => $this->input->post('teacher_name'),
                    'teaching_methodologies_used' => $this->input->post('teaching_methodologies_used'),
                    'ict_tools_used' => $this->input->post('ict_tools_used'),
                    'percentage_teachers_using_ict' => $this->input->post('percentage_teachers_using_ict'),
                    'document_link_teaching_plan' => $this->input->post('document_link_teaching_plan'),
                );
                $this->naac_model->add_c2_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_3_teaching_learning_process');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_3_teaching_learning_process/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.3';
        $data['entry'] = $this->naac_model->get_c2_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');
            $this->form_validation->set_rules('course_code', 'Course Code', 'required');
            $this->form_validation->set_rules('teacher_name', 'Teacher Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'course_code' => $this->input->post('course_code'),
                    'teacher_name' => $this->input->post('teacher_name'),
                    'teaching_methodologies_used' => $this->input->post('teaching_methodologies_used'),
                    'ict_tools_used' => $this->input->post('ict_tools_used'),
                    'percentage_teachers_using_ict' => $this->input->post('percentage_teachers_using_ict'),
                    'document_link_teaching_plan' => $this->input->post('document_link_teaching_plan'),
                );
                $this->naac_model->update_c2_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_3_teaching_learning_process');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_3_teaching_learning_process/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_3_delete($id) {
        $this->naac_model->delete_c2_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_3_teaching_learning_process');
    }

    // --- KI 2.4: Teacher Profile and Quality ---
    public function c2_4_teacher_profile_quality() {
        $data['page_title'] = 'NAAC Criterion 2.4: Teacher Profile and Quality';
        $data['c2_4_data'] = $this->naac_model->get_c2_4_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_4_teacher_profile_quality/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_4_add() {
        $data['page_title'] = 'Add Entry for C2.4';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('teacher_name', 'Teacher Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'teacher_name' => $this->input->post('teacher_name'),
                    'highest_qualification' => $this->input->post('highest_qualification'),
                    'years_of_experience' => $this->input->post('years_of_experience'),
                    'phd_status' => $this->input->post('phd_status'),
                    'professional_development_activities' => $this->input->post('professional_development_activities'),
                    'document_link_cv' => $this->input->post('document_link_cv'),
                );
                $this->naac_model->add_c2_4_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_4_teacher_profile_quality');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_4_teacher_profile_quality/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_4_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.4';
        $data['entry'] = $this->naac_model->get_c2_4_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('teacher_name', 'Teacher Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'teacher_name' => $this->input->post('teacher_name'),
                    'highest_qualification' => $this->input->post('highest_qualification'),
                    'years_of_experience' => $this->input->post('years_of_experience'),
                    'phd_status' => $this->input->post('phd_status'),
                    'professional_development_activities' => $this->input->post('professional_development_activities'),
                    'document_link_cv' => $this->input->post('document_link_cv'),
                );
                $this->naac_model->update_c2_4_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_4_teacher_profile_quality');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_4_teacher_profile_quality/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_4_delete($id) {
        $this->naac_model->delete_c2_4_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_4_teacher_profile_quality');
    }

    // --- KI 2.5: Evaluation Process and Reforms ---
    public function c2_5_evaluation_process() {
        $data['page_title'] = 'NAAC Criterion 2.5: Evaluation Process and Reforms';
        $data['c2_5_data'] = $this->naac_model->get_c2_5_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_5_evaluation_process/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_5_add() {
        $data['page_title'] = 'Add Entry for C2.5';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'evaluation_reforms_description' => $this->input->post('evaluation_reforms_description'),
                    'transparency_in_evaluation' => $this->input->post('transparency_in_evaluation'),
                    'grievance_redressal_mechanism' => $this->input->post('grievance_redressal_mechanism'),
                    'document_link_evaluation_policy' => $this->input->post('document_link_evaluation_policy'),
                );
                $this->naac_model->add_c2_5_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_5_evaluation_process');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_5_evaluation_process/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_5_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.5';
        $data['entry'] = $this->naac_model->get_c2_5_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'evaluation_reforms_description' => $this->input->post('evaluation_reforms_description'),
                    'transparency_in_evaluation' => $this->input->post('transparency_in_evaluation'),
                    'grievance_redressal_mechanism' => $this->input->post('grievance_redressal_mechanism'),
                    'document_link_evaluation_policy' => $this->input->post('document_link_evaluation_policy'),
                );
                $this->naac_model->update_c2_5_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_5_evaluation_process');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_5_evaluation_process/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_5_delete($id) {
        $this->naac_model->delete_c2_5_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_5_evaluation_process');
    }

    // --- KI 2.6: Student Performance and Learning Outcome ---
    public function c2_6_student_performance() {
        $data['page_title'] = 'NAAC Criterion 2.6: Student Performance and Learning Outcome';
        $data['c2_6_data'] = $this->naac_model->get_c2_6_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_6_student_performance/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_6_add() {
        $data['page_title'] = 'Add Entry for C2.6';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');
            $this->form_validation->set_rules('course_code', 'Course Code', 'required');
            $this->form_validation->set_rules('student_id', 'Student ID', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'course_code' => $this->input->post('course_code'),
                    'student_id' => $this->input->post('student_id'),
                    'grade_percentage' => $this->input->post('grade_percentage'),
                    'po_co_attainment_description' => $this->input->post('po_co_attainment_description'),
                    'document_link_results' => $this->input->post('document_link_results'),
                );
                $this->naac_model->add_c2_6_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_6_student_performance');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_6_student_performance/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_6_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.6';
        $data['entry'] = $this->naac_model->get_c2_6_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');
            $this->form_validation->set_rules('course_code', 'Course Code', 'required');
            $this->form_validation->set_rules('student_id', 'Student ID', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'course_code' => $this->input->post('course_code'),
                    'student_id' => $this->input->post('student_id'),
                    'grade_percentage' => $this->input->post('grade_percentage'),
                    'po_co_attainment_description' => $this->input->post('po_co_attainment_description'),
                    'document_link_results' => $this->input->post('document_link_results'),
                );
                $this->naac_model->update_c2_6_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_6_student_performance');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_6_student_performance/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_6_delete($id) {
        $this->naac_model->delete_c2_6_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_6_student_performance');
    }

    // --- KI 2.7: Student Satisfaction Survey ---
    public function c2_7_student_satisfaction_survey() {
        $data['page_title'] = 'NAAC Criterion 2.7: Student Satisfaction Survey';
        $data['c2_7_data'] = $this->naac_model->get_c2_7_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c2_7_student_satisfaction_survey/list', $data);
        $this->load->view('layout/footer');
    }

    public function c2_7_add() {
        $data['page_title'] = 'Add Entry for C2.7';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'survey_methodology' => $this->input->post('survey_methodology'),
                    'total_students_enrolled' => $this->input->post('total_students_enrolled'),
                    'total_students_surveyed' => $this->input->post('total_students_surveyed'),
                    'sss_analysis_report' => $this->input->post('sss_analysis_report'),
                    'action_taken_on_sss' => $this->input->post('action_taken_on_sss'),
                    'document_link_survey_report' => $this->input->post('document_link_survey_report'),
                );
                $this->naac_model->add_c2_7_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c2_7_student_satisfaction_survey');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_7_student_satisfaction_survey/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_7_edit($id) {
        $data['page_title'] = 'Edit Entry for C2.7';
        $data['entry'] = $this->naac_model->get_c2_7_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'survey_methodology' => $this->input->post('survey_methodology'),
                    'total_students_enrolled' => $this->input->post('total_students_enrolled'),
                    'total_students_surveyed' => $this->input->post('total_students_surveyed'),
                    'sss_analysis_report' => $this->input->post('sss_analysis_report'),
                    'action_taken_on_sss' => $this->input->post('action_taken_on_sss'),
                    'document_link_survey_report' => $this->input->post('document_link_survey_report'),
                );
                $this->naac_model->update_c2_7_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c2_7_student_satisfaction_survey');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c2_7_student_satisfaction_survey/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c2_7_delete($id) {
        $this->naac_model->delete_c2_7_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c2_7_student_satisfaction_survey');
    }

    // --- KI 3.1: Promotion of Research and Facilities ---
    public function c3_1_research_promotion() {
        $data['page_title'] = 'NAAC Criterion 3.1: Promotion of Research and Facilities';
        $data['c3_1_data'] = $this->naac_model->get_c3_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_1_research_promotion/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_1_add() {
        $data['page_title'] = 'Add Entry for C3.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'research_promotion_policy' => $this->input->post('research_promotion_policy'),
                    'research_facilities_description' => $this->input->post('research_facilities_description'),
                    'document_link_policy' => $this->input->post('document_link_policy'),
                );
                $this->naac_model->add_c3_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_1_research_promotion');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_1_research_promotion/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.1';
        $data['entry'] = $this->naac_model->get_c3_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'research_promotion_policy' => $this->input->post('research_promotion_policy'),
                    'research_facilities_description' => $this->input->post('research_facilities_description'),
                    'document_link_policy' => $this->input->post('document_link_policy'),
                );
                $this->naac_model->update_c3_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_1_research_promotion');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_1_research_promotion/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_1_delete($id) {
        $this->naac_model->delete_c3_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_1_research_promotion');
    }

    // --- KI 3.2: Resource Mobilization for Research ---
    public function c3_2_resource_mobilization() {
        $data['page_title'] = 'NAAC Criterion 3.2: Resource Mobilization for Research';
        $data['c3_2_data'] = $this->naac_model->get_c3_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_2_resource_mobilization/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_2_add() {
        $data['page_title'] = 'Add Entry for C3.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('teacher_name', 'Teacher Name', 'required');
            $this->form_validation->set_rules('project_title', 'Project Title', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'teacher_name' => $this->input->post('teacher_name'),
                    'project_title' => $this->input->post('project_title'),
                    'funding_agency' => $this->input->post('funding_agency'),
                    'amount_received_lakhs' => $this->input->post('amount_received_lakhs'),
                    'project_type' => $this->input->post('project_type'),
                    'document_link_sanction_letter' => $this->input->post('document_link_sanction_letter'),
                );
                $this->naac_model->add_c3_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_2_resource_mobilization');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_2_resource_mobilization/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.2';
        $data['entry'] = $this->naac_model->get_c3_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('teacher_name', 'Teacher Name', 'required');
            $this->form_validation->set_rules('project_title', 'Project Title', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'teacher_name' => $this->input->post('teacher_name'),
                    'project_title' => $this->input->post('project_title'),
                    'funding_agency' => $this->input->post('funding_agency'),
                    'amount_received_lakhs' => $this->input->post('amount_received_lakhs'),
                    'project_type' => $this->input->post('project_type'),
                    'document_link_sanction_letter' => $this->input->post('document_link_sanction_letter'),
                );
                $this->naac_model->update_c3_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_2_resource_mobilization');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_2_resource_mobilization/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_2_delete($id) {
        $this->naac_model->delete_c3_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_2_resource_mobilization');
    }

    // --- KI 3.3: Innovation Ecosystem ---
    public function c3_3_innovation_ecosystem() {
        $data['page_title'] = 'NAAC Criterion 3.3: Innovation Ecosystem';
        $data['c3_3_data'] = $this->naac_model->get_c3_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_3_innovation_ecosystem/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_3_add() {
        $data['page_title'] = 'Add Entry for C3.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'innovation_ecosystem_description' => $this->input->post('innovation_ecosystem_description'),
                    'number_of_startups' => $this->input->post('number_of_startups'),
                    'document_link_incubation_policy' => $this->input->post('document_link_incubation_policy'),
                );
                $this->naac_model->add_c3_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_3_innovation_ecosystem');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_3_innovation_ecosystem/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.3';
        $data['entry'] = $this->naac_model->get_c3_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'innovation_ecosystem_description' => $this->input->post('innovation_ecosystem_description'),
                    'number_of_startups' => $this->input->post('number_of_startups'),
                    'document_link_incubation_policy' => $this->input->post('document_link_incubation_policy'),
                );
                $this->naac_model->update_c3_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_3_innovation_ecosystem');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_3_innovation_ecosystem/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_3_delete($id) {
        $this->naac_model->delete_c3_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_3_innovation_ecosystem');
    }

    // --- KI 3.4: Research Publications and Awards ---
    public function c3_4_research_publications_awards() {
        $data['page_title'] = 'NAAC Criterion 3.4: Research Publications and Awards';
        $data['c3_4_data'] = $this->naac_model->get_c3_4_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_4_research_publications_awards/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_4_add() {
        $data['page_title'] = 'Add Entry for C3.4';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('author_name', 'Author Name', 'required');
            $this->form_validation->set_rules('publication_title', 'Publication Title', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'author_name' => $this->input->post('author_name'),
                    'publication_title' => $this->input->post('publication_title'),
                    'journal_name' => $this->input->post('journal_name'),
                    'ugc_care_list' => $this->input->post('ugc_care_list'),
                    'indexed_in' => $this->input->post('indexed_in'),
                    'award_name' => $this->input->post('award_name'),
                    'awarding_agency' => $this->input->post('awarding_agency'),
                    'document_link_publication' => $this->input->post('document_link_publication'),
                    'document_link_award' => $this->input->post('document_link_award'),
                );
                $this->naac_model->add_c3_4_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_4_research_publications_awards');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_4_research_publications_awards/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_4_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.4';
        $data['entry'] = $this->naac_model->get_c3_4_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('author_name', 'Author Name', 'required');
            $this->form_validation->set_rules('publication_title', 'Publication Title', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'author_name' => $this->input->post('author_name'),
                    'publication_title' => $this->input->post('publication_title'),
                    'journal_name' => $this->input->post('journal_name'),
                    'ugc_care_list' => $this->input->post('ugc_care_list'),
                    'indexed_in' => $this->input->post('indexed_in'),
                    'award_name' => $this->input->post('award_name'),
                    'awarding_agency' => $this->input->post('awarding_agency'),
                    'document_link_publication' => $this->input->post('document_link_publication'),
                    'document_link_award' => $this->input->post('document_link_award'),
                );
                $this->naac_model->update_c3_4_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_4_research_publications_awards');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_4_research_publications_awards/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_4_delete($id) {
        $this->naac_model->delete_c3_4_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_4_research_publications_awards');
    }

    // --- KI 3.5: Consultancy ---
    public function c3_5_consultancy() {
        $data['page_title'] = 'NAAC Criterion 3.5: Consultancy';
        $data['c3_5_data'] = $this->naac_model->get_c3_5_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_5_consultancy/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_5_add() {
        $data['page_title'] = 'Add Entry for C3.5';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('consultant_name', 'Consultant Name', 'required');
            $this->form_validation->set_rules('client_organization', 'Client Organization', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'consultant_name' => $this->input->post('consultant_name'),
                    'client_organization' => $this->input->post('client_organization'),
                    'consultancy_area' => $this->input->post('consultancy_area'),
                    'revenue_generated_lakhs' => $this->input->post('revenue_generated_lakhs'),
                    'document_link_report' => $this->input->post('document_link_report'),
                );
                $this->naac_model->add_c3_5_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_5_consultancy');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_5_consultancy/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_5_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.5';
        $data['entry'] = $this->naac_model->get_c3_5_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('consultant_name', 'Consultant Name', 'required');
            $this->form_validation->set_rules('client_organization', 'Client Organization', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'consultant_name' => $this->input->post('consultant_name'),
                    'client_organization' => $this->input->post('client_organization'),
                    'consultancy_area' => $this->input->post('consultancy_area'),
                    'revenue_generated_lakhs' => $this->input->post('revenue_generated_lakhs'),
                    'document_link_report' => $this->input->post('document_link_report'),
                );
                $this->naac_model->update_c3_5_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_5_consultancy');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_5_consultancy/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_5_delete($id) {
        $this->naac_model->delete_c3_5_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_5_consultancy');
    }

    // --- KI 3.6: Extension Activities ---
    public function c3_6_extension_activities() {
        $data['page_title'] = 'NAAC Criterion 3.6: Extension Activities';
        $data['c3_6_data'] = $this->naac_model->get_c3_6_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_6_extension_activities/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_6_add() {
        $data['page_title'] = 'Add Entry for C3.6';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('activity_name', 'Activity Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'activity_name' => $this->input->post('activity_name'),
                    'organizing_unit' => $this->input->post('organizing_unit'),
                    'number_of_students_participated' => $this->input->post('number_of_students_participated'),
                    'number_of_public_benefited' => $this->input->post('number_of_public_benefited'),
                    'extension_activity_impact' => $this->input->post('extension_activity_impact'),
                    'document_link_report' => $this->input->post('document_link_report'),
                );
                $this->naac_model->add_c3_6_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_6_extension_activities');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_6_extension_activities/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_6_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.6';
        $data['entry'] = $this->naac_model->get_c3_6_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('activity_name', 'Activity Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'activity_name' => $this->input->post('activity_name'),
                    'organizing_unit' => $this->input->post('organizing_unit'),
                    'number_of_students_participated' => $this->input->post('number_of_students_participated'),
                    'number_of_public_benefited' => $this->input->post('number_of_public_benefited'),
                    'extension_activity_impact' => $this->input->post('extension_activity_impact'),
                    'document_link_report' => $this->input->post('document_link_report'),
                );
                $this->naac_model->update_c3_6_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_6_extension_activities');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_6_extension_activities/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_6_delete($id) {
        $this->naac_model->delete_c3_6_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_6_extension_activities');
    }

    // --- KI 3.7: Collaboration ---
    public function c3_7_collaboration() {
        $data['page_title'] = 'NAAC Criterion 3.7: Collaboration';
        $data['c3_7_data'] = $this->naac_model->get_c3_7_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c3_7_collaboration/list', $data);
        $this->load->view('layout/footer');
    }

    public function c3_7_add() {
        $data['page_title'] = 'Add Entry for C3.7';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('partner_organization', 'Partner Organization', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'partner_organization' => $this->input->post('partner_organization'),
                    'type_of_collaboration' => $this->input->post('type_of_collaboration'),
                    'purpose_of_collaboration' => $this->input->post('purpose_of_collaboration'),
                    'document_link_mou' => $this->input->post('document_link_mou'),
                );
                $this->naac_model->add_c3_7_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c3_7_collaboration');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_7_collaboration/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_7_edit($id) {
        $data['page_title'] = 'Edit Entry for C3.7';
        $data['entry'] = $this->naac_model->get_c3_7_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('partner_organization', 'Partner Organization', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'partner_organization' => $this->input->post('partner_organization'),
                    'type_of_collaboration' => $this->input->post('type_of_collaboration'),
                    'purpose_of_collaboration' => $this->input->post('purpose_of_collaboration'),
                    'document_link_mou' => $this->input->post('document_link_mou'),
                );
                $this->naac_model->update_c3_7_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c3_7_collaboration');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c3_7_collaboration/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c3_7_delete($id) {
        $this->naac_model->delete_c3_7_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c3_7_collaboration');
    }

    // --- KI 4.1: Physical Facilities ---
    public function c4_1_physical_facilities() {
        $data['page_title'] = 'NAAC Criterion 4.1: Physical Facilities';
        $data['c4_1_data'] = $this->naac_model->get_c4_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c4_1_physical_facilities/list', $data);
        $this->load->view('layout/footer');
    }

    public function c4_1_add() {
        $data['page_title'] = 'Add Entry for C4.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'classrooms_ict_enabled_percentage' => $this->input->post('classrooms_ict_enabled_percentage'),
                    'seminar_halls_ict_enabled_percentage' => $this->input->post('seminar_halls_ict_enabled_percentage'),
                    'physical_facilities_description' => $this->input->post('physical_facilities_description'),
                    'facilities_for_cultural_sports' => $this->input->post('facilities_for_cultural_sports'),
                    'document_link_facilities_audit' => $this->input->post('document_link_facilities_audit'),
                );
                $this->naac_model->add_c4_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c4_1_physical_facilities');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_1_physical_facilities/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C4.1';
        $data['entry'] = $this->naac_model->get_c4_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'classrooms_ict_enabled_percentage' => $this->input->post('classrooms_ict_enabled_percentage'),
                    'seminar_halls_ict_enabled_percentage' => $this->input->post('seminar_halls_ict_enabled_percentage'),
                    'physical_facilities_description' => $this->input->post('physical_facilities_description'),
                    'facilities_for_cultural_sports' => $this->input->post('facilities_for_cultural_sports'),
                    'document_link_facilities_audit' => $this->input->post('document_link_facilities_audit'),
                );
                $this->naac_model->update_c4_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c4_1_physical_facilities');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_1_physical_facilities/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_1_delete($id) {
        $this->naac_model->delete_c4_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c4_1_physical_facilities');
    }

    // --- KI 4.2: Library as a Learning Resource ---
    public function c4_2_library_resources() {
        $data['page_title'] = 'NAAC Criterion 4.2: Library as a Learning Resource';
        $data['c4_2_data'] = $this->naac_model->get_c4_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c4_2_library_resources/list', $data);
        $this->load->view('layout/footer');
    }

    public function c4_2_add() {
        $data['page_title'] = 'Add Entry for C4.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'number_of_books' => $this->input->post('number_of_books'),
                    'number_of_e_journals' => $this->input->post('number_of_e_journals'),
                    'integrated_library_management_system' => $this->input->post('integrated_library_management_system'),
                    'library_e_resources_description' => $this->input->post('library_e_resources_description'),
                    'library_usage_details' => $this->input->post('library_usage_details'),
                    'document_link_library_report' => $this->input->post('document_link_library_report'),
                );
                $this->naac_model->add_c4_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c4_2_library_resources');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_2_library_resources/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C4.2';
        $data['entry'] = $this->naac_model->get_c4_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'number_of_books' => $this->input->post('number_of_books'),
                    'number_of_e_journals' => $this->input->post('number_of_e_journals'),
                    'integrated_library_management_system' => $this->input->post('integrated_library_management_system'),
                    'library_e_resources_description' => $this->input->post('library_e_resources_description'),
                    'library_usage_details' => $this->input->post('library_usage_details'),
                    'document_link_library_report' => $this->input->post('document_link_library_report'),
                );
                $this->naac_model->update_c4_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c4_2_library_resources');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_2_library_resources/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_2_delete($id) {
        $this->naac_model->delete_c4_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c4_2_library_resources');
    }

    // --- KI 4.3: IT Infrastructure ---
    public function c4_3_it_infrastructure() {
        $data['page_title'] = 'NAAC Criterion 4.3: IT Infrastructure';
        $data['c4_3_data'] = $this->naac_model->get_c4_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c4_3_it_infrastructure/list', $data);
        $this->load->view('layout/footer');
    }

    public function c4_3_add() {
        $data['page_title'] = 'Add Entry for C4.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'computer_student_ratio' => $this->input->post('computer_student_ratio'),
                    'internet_bandwidth_mbps' => $this->input->post('internet_bandwidth_mbps'),
                    'it_policy_description' => $this->input->post('it_policy_description'),
                    'e_content_development_facilities' => $this->input->post('e_content_development_facilities'),
                    'wifi_availability_description' => $this->input->post('wifi_availability_description'),
                    'document_link_it_policy' => $this->input->post('document_link_it_policy'),
                );
                $this->naac_model->add_c4_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c4_3_it_infrastructure');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_3_it_infrastructure/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C4.3';
        $data['entry'] = $this->naac_model->get_c4_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'computer_student_ratio' => $this->input->post('computer_student_ratio'),
                    'internet_bandwidth_mbps' => $this->input->post('internet_bandwidth_mbps'),
                    'it_policy_description' => $this->input->post('it_policy_description'),
                    'e_content_development_facilities' => $this->input->post('e_content_development_facilities'),
                    'wifi_availability_description' => $this->input->post('wifi_availability_description'),
                    'document_link_it_policy' => $this->input->post('document_link_it_policy'),
                );
                $this->naac_model->update_c4_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c4_3_it_infrastructure');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_3_it_infrastructure/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_3_delete($id) {
        $this->naac_model->delete_c4_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c4_3_it_infrastructure');
    }

    // --- KI 4.4: Maintenance of Campus Infrastructure ---
    public function c4_4_campus_maintenance() {
        $data['page_title'] = 'NAAC Criterion 4.4: Maintenance of Campus Infrastructure';
        $data['c4_4_data'] = $this->naac_model->get_c4_4_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c4_4_campus_maintenance/list', $data);
        $this->load->view('layout/footer');
    }

    public function c4_4_add() {
        $data['page_title'] = 'Add Entry for C4.4';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'expenditure_on_maintenance_lakhs' => $this->input->post('expenditure_on_maintenance_lakhs'),
                    'maintenance_systems_procedures' => $this->input->post('maintenance_systems_procedures'),
                    'utilization_of_facilities' => $this->input->post('utilization_of_facilities'),
                    'document_link_audited_statements' => $this->input->post('document_link_audited_statements'),
                );
                $this->naac_model->add_c4_4_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c4_4_campus_maintenance');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_4_campus_maintenance/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_4_edit($id) {
        $data['page_title'] = 'Edit Entry for C4.4';
        $data['entry'] = $this->naac_model->get_c4_4_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'expenditure_on_maintenance_lakhs' => $this->input->post('expenditure_on_maintenance_lakhs'),
                    'maintenance_systems_procedures' => $this->input->post('maintenance_systems_procedures'),
                    'utilization_of_facilities' => $this->input->post('utilization_of_facilities'),
                    'document_link_audited_statements' => $this->input->post('document_link_audited_statements'),
                );
                $this->naac_model->update_c4_4_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c4_4_campus_maintenance');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c4_4_campus_maintenance/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c4_4_delete($id) {
        $this->naac_model->delete_c4_4_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c4_4_campus_maintenance');
    }

    // --- KI 5.1: Student Support ---
    public function c5_1_student_support() {
        $data['page_title'] = 'NAAC Criterion 5.1: Student Support';
        $data['c5_1_data'] = $this->naac_model->get_c5_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c5_1_student_support/list', $data);
        $this->load->view('layout/footer');
    }

    public function c5_1_add() {
        $data['page_title'] = 'Add Entry for C5.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'total_students_benefited_scholarships' => $this->input->post('total_students_benefited_scholarships'),
                    'total_amount_scholarships_lakhs' => $this->input->post('total_amount_scholarships_lakhs'),
                    'support_mechanisms_description' => $this->input->post('support_mechanisms_description'),
                    'capacity_building_skills_enhancement' => $this->input->post('capacity_building_skills_enhancement'),
                    'document_link_scholarship_policy' => $this->input->post('document_link_scholarship_policy'),
                    'document_link_support_services' => $this->input->post('document_link_support_services'),
                );
                $this->naac_model->add_c5_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c5_1_student_support');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_1_student_support/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C5.1';
        $data['entry'] = $this->naac_model->get_c5_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'total_students_benefited_scholarships' => $this->input->post('total_students_benefited_scholarships'),
                    'total_amount_scholarships_lakhs' => $this->input->post('total_amount_scholarships_lakhs'),
                    'support_mechanisms_description' => $this->input->post('support_mechanisms_description'),
                    'capacity_building_skills_enhancement' => $this->input->post('capacity_building_skills_enhancement'),
                    'document_link_scholarship_policy' => $this->input->post('document_link_scholarship_policy'),
                    'document_link_support_services' => $this->input->post('document_link_support_services'),
                );
                $this->naac_model->update_c5_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c5_1_student_support');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_1_student_support/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_1_delete($id) {
        $this->naac_model->delete_c5_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c5_1_student_support');
    }

    // --- KI 5.2: Student Progression ---
    public function c5_2_student_progression() {
        $data['page_title'] = 'NAAC Criterion 5.2: Student Progression';
        $data['c5_2_data'] = $this->naac_model->get_c5_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c5_2_student_progression/list', $data);
        $this->load->view('layout/footer');
    }

    public function c5_2_add() {
        $data['page_title'] = 'Add Entry for C5.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'total_outgoing_students' => $this->input->post('total_outgoing_students'),
                    'students_placed' => $this->input->post('students_placed'),
                    'students_to_higher_education' => $this->input->post('students_to_higher_education'),
                    'students_qualified_competitive_exams' => $this->input->post('students_qualified_competitive_exams'),
                    'progression_facilitation_description' => $this->input->post('progression_facilitation_description'),
                    'document_link_placement_report' => $this->input->post('document_link_placement_report'),
                    'document_link_higher_education_data' => $this->input->post('document_link_higher_education_data'),
                );
                $this->naac_model->add_c5_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c5_2_student_progression');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_2_student_progression/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C5.2';
        $data['entry'] = $this->naac_model->get_c5_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('program_name', 'Program Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'program_name' => $this->input->post('program_name'),
                    'total_outgoing_students' => $this->input->post('total_outgoing_students'),
                    'students_placed' => $this->input->post('students_placed'),
                    'students_to_higher_education' => $this->input->post('students_to_higher_education'),
                    'students_qualified_competitive_exams' => $this->input->post('students_qualified_competitive_exams'),
                    'progression_facilitation_description' => $this->input->post('progression_facilitation_description'),
                    'document_link_placement_report' => $this->input->post('document_link_placement_report'),
                    'document_link_higher_education_data' => $this->input->post('document_link_higher_education_data'),
                );
                $this->naac_model->update_c5_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c5_2_student_progression');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_2_student_progression/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_2_delete($id) {
        $this->naac_model->delete_c5_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c5_2_student_progression');
    }

    // --- KI 5.3: Student Participation and Activities ---
    public function c5_3_student_participation() {
        $data['page_title'] = 'NAAC Criterion 5.3: Student Participation and Activities';
        $data['c5_3_data'] = $this->naac_model->get_c5_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c5_3_student_participation/list', $data);
        $this->load->view('layout/footer');
    }

    public function c5_3_add() {
        $data['page_title'] = 'Add Entry for C5.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('activity_name', 'Activity Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'activity_name' => $this->input->post('activity_name'),
                    'activity_type' => $this->input->post('activity_type'),
                    'number_of_students_participated' => $this->input->post('number_of_students_participated'),
                    'awards_medals_won' => $this->input->post('awards_medals_won'),
                    'promotion_of_activities_description' => $this->input->post('promotion_of_activities_description'),
                    'document_link_activity_report' => $this->input->post('document_link_activity_report'),
                );
                $this->naac_model->add_c5_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c5_3_student_participation');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_3_student_participation/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C5.3';
        $data['entry'] = $this->naac_model->get_c5_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('activity_name', 'Activity Name', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'activity_name' => $this->input->post('activity_name'),
                    'activity_type' => $this->input->post('activity_type'),
                    'number_of_students_participated' => $this->input->post('number_of_students_participated'),
                    'awards_medals_won' => $this->input->post('awards_medals_won'),
                    'promotion_of_activities_description' => $this->input->post('promotion_of_activities_description'),
                    'document_link_activity_report' => $this->input->post('document_link_activity_report'),
                );
                $this->naac_model->update_c5_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c5_3_student_participation');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_3_student_participation/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_3_delete($id) {
        $this->naac_model->delete_c5_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c5_3_student_participation');
    }

    // --- KI 5.4: Alumni Engagement ---
    public function c5_4_alumni_engagement() {
        $data['page_title'] = 'NAAC Criterion 5.4: Alumni Engagement';
        $data['c5_4_data'] = $this->naac_model->get_c5_4_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c5_4_alumni_engagement/list', $data);
        $this->load->view('layout/footer');
    }

    public function c5_4_add() {
        $data['page_title'] = 'Add Entry for C5.4';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'alumni_association_registered' => $this->input->post('alumni_association_registered'),
                    'alumni_contribution_description' => $this->input->post('alumni_contribution_description'),
                    'alumni_engagement_activities' => $this->input->post('alumni_engagement_activities'),
                    'document_link_alumni_report' => $this->input->post('document_link_alumni_report'),
                );
                $this->naac_model->add_c5_4_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c5_4_alumni_engagement');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_4_alumni_engagement/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_4_edit($id) {
        $data['page_title'] = 'Edit Entry for C5.4';
        $data['entry'] = $this->naac_model->get_c5_4_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'alumni_association_registered' => $this->input->post('alumni_association_registered'),
                    'alumni_contribution_description' => $this->input->post('alumni_contribution_description'),
                    'alumni_engagement_activities' => $this->input->post('alumni_engagement_activities'),
                    'document_link_alumni_report' => $this->input->post('document_link_alumni_report'),
                );
                $this->naac_model->update_c5_4_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c5_4_alumni_engagement');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c5_4_alumni_engagement/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c5_4_delete($id) {
        $this->naac_model->delete_c5_4_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c5_4_alumni_engagement');
    }

    // --- KI 6.1: Institutional Vision and Leadership ---
    public function c6_1_vision_leadership() {
        $data['page_title'] = 'NAAC Criterion 6.1: Institutional Vision and Leadership';
        $data['c6_1_data'] = $this->naac_model->get_c6_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c6_1_vision_leadership/list', $data);
        $this->load->view('layout/footer');
    }

    public function c6_1_add() {
        $data['page_title'] = 'Add Entry for C6.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'governance_vision_mission_alignment' => $this->input->post('governance_vision_mission_alignment'),
                    'leadership_effectiveness_description' => $this->input->post('leadership_effectiveness_description'),
                    'decentralization_participative_management' => $this->input->post('decentralization_participative_management'),
                    'document_link_vision_mission' => $this->input->post('document_link_vision_mission'),
                );
                $this->naac_model->add_c6_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c6_1_vision_leadership');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_1_vision_leadership/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C6.1';
        $data['entry'] = $this->naac_model->get_c6_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'governance_vision_mission_alignment' => $this->input->post('governance_vision_mission_alignment'),
                    'leadership_effectiveness_description' => $this->input->post('leadership_effectiveness_description'),
                    'decentralization_participative_management' => $this->input->post('decentralization_participative_management'),
                    'document_link_vision_mission' => $this->input->post('document_link_vision_mission'),
                );
                $this->naac_model->update_c6_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c6_1_vision_leadership');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_1_vision_leadership/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_1_delete($id) {
        $this->naac_model->delete_c6_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c6_1_vision_leadership');
    }

    // --- KI 6.2: Strategy Development and Deployment ---
    public function c6_2_strategy_deployment() {
        $data['page_title'] = 'NAAC Criterion 6.2: Strategy Development and Deployment';
        $data['c6_2_data'] = $this->naac_model->get_c6_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c6_2_strategy_deployment/list', $data);
        $this->load->view('layout/footer');
    }

    public function c6_2_add() {
        $data['page_title'] = 'Add Entry for C6.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'strategic_plan_description' => $this->input->post('strategic_plan_description'),
                    'e_governance_implementation_areas' => $this->input->post('e_governance_implementation_areas'),
                    'document_link_strategic_plan' => $this->input->post('document_link_strategic_plan'),
                    'document_link_e_governance_report' => $this->input->post('document_link_e_governance_report'),
                );
                $this->naac_model->add_c6_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c6_2_strategy_deployment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_2_strategy_deployment/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C6.2';
        $data['entry'] = $this->naac_model->get_c6_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'strategic_plan_description' => $this->input->post('strategic_plan_description'),
                    'e_governance_implementation_areas' => $this->input->post('e_governance_implementation_areas'),
                    'document_link_strategic_plan' => $this->input->post('document_link_strategic_plan'),
                    'document_link_e_governance_report' => $this->input->post('document_link_e_governance_report'),
                );
                $this->naac_model->update_c6_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c6_2_strategy_deployment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_2_strategy_deployment/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_2_delete($id) {
        $this->naac_model->delete_c6_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c6_2_strategy_deployment');
    }

    // --- KI 6.3: Faculty Empowerment Strategies ---
    public function c6_3_faculty_empowerment() {
        $data['page_title'] = 'NAAC Criterion 6.3: Faculty Empowerment Strategies';
        $data['c6_3_data'] = $this->naac_model->get_c6_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c6_3_faculty_empowerment/list', $data);
        $this->load->view('layout/footer');
    }

    public function c6_3_add() {
        $data['page_title'] = 'Add Entry for C6.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'welfare_measures_description' => $this->input->post('welfare_measures_description'),
                    'teachers_received_financial_support' => $this->input->post('teachers_received_financial_support'),
                    'professional_development_programs_organized' => $this->input->post('professional_development_programs_organized'),
                    'teachers_undergoing_fdp' => $this->input->post('teachers_undergoing_fdp'),
                    'performance_appraisal_system' => $this->input->post('performance_appraisal_system'),
                    'document_link_welfare_policy' => $this->input->post('document_link_welfare_policy'),
                );
                $this->naac_model->add_c6_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c6_3_faculty_empowerment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_3_faculty_empowerment/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C6.3';
        $data['entry'] = $this->naac_model->get_c6_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'welfare_measures_description' => $this->input->post('welfare_measures_description'),
                    'teachers_received_financial_support' => $this->input->post('teachers_received_financial_support'),
                    'professional_development_programs_organized' => $this->input->post('professional_development_programs_organized'),
                    'teachers_undergoing_fdp' => $this->input->post('teachers_undergoing_fdp'),
                    'performance_appraisal_system' => $this->input->post('performance_appraisal_system'),
                    'document_link_welfare_policy' => $this->input->post('document_link_welfare_policy'),
                );
                $this->naac_model->update_c6_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c6_3_faculty_empowerment');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_3_faculty_empowerment/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_3_delete($id) {
        $this->naac_model->delete_c6_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c6_3_faculty_empowerment');
    }

    // --- KI 6.4: Financial Management and Resource Mobilization ---
    public function c6_4_financial_management() {
        $data['page_title'] = 'NAAC Criterion 6.4: Financial Management and Resource Mobilization';
        $data['c6_4_data'] = $this->naac_model->get_c6_4_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c6_4_financial_management/list', $data);
        $this->load->view('layout/footer');
    }

    public function c6_4_add() {
        $data['page_title'] = 'Add Entry for C6.4';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'internal_audits_regularity' => $this->input->post('internal_audits_regularity'),
                    'external_audits_regularity' => $this->input->post('external_audits_regularity'),
                    'funds_grants_received_lakhs' => $this->input->post('funds_grants_received_lakhs'),
                    'resource_mobilization_strategies' => $this->input->post('resource_mobilization_strategies'),
                    'document_link_audit_reports' => $this->input->post('document_link_audit_reports'),
                );
                $this->naac_model->add_c6_4_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c6_4_financial_management');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_4_financial_management/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_4_edit($id) {
        $data['page_title'] = 'Edit Entry for C6.4';
        $data['entry'] = $this->naac_model->get_c6_4_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'internal_audits_regularity' => $this->input->post('internal_audits_regularity'),
                    'external_audits_regularity' => $this->input->post('external_audits_regularity'),
                    'funds_grants_received_lakhs' => $this->input->post('funds_grants_received_lakhs'),
                    'resource_mobilization_strategies' => $this->input->post('resource_mobilization_strategies'),
                    'document_link_audit_reports' => $this->input->post('document_link_audit_reports'),
                );
                $this->naac_model->update_c6_4_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c6_4_financial_management');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_4_financial_management/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_4_delete($id) {
        $this->naac_model->delete_c6_4_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c6_4_financial_management');
    }

    // --- KI 6.5: Internal Quality Assurance System (IQAS) ---
    public function c6_5_iqas() {
        $data['page_title'] = 'NAAC Criterion 6.5: Internal Quality Assurance System (IQAS)';
        $data['c6_5_data'] = $this->naac_model->get_c6_5_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c6_5_iqas/list', $data);
        $this->load->view('layout/footer');
    }

    public function c6_5_add() {
        $data['page_title'] = 'Add Entry for C6.5';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'iqac_initiatives_description' => $this->input->post('iqac_initiatives_description'),
                    'quality_assurance_initiatives' => $this->input->post('quality_assurance_initiatives'),
                    'document_link_iqac_report' => $this->input->post('document_link_iqac_report'),
                );
                $this->naac_model->add_c6_5_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c6_5_iqas');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_5_iqas/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_5_edit($id) {
        $data['page_title'] = 'Edit Entry for C6.5';
        $data['entry'] = $this->naac_model->get_c6_5_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'iqac_initiatives_description' => $this->input->post('iqac_initiatives_description'),
                    'quality_assurance_initiatives' => $this->input->post('quality_assurance_initiatives'),
                    'document_link_iqac_report' => $this->input->post('document_link_iqac_report'),
                );
                $this->naac_model->update_c6_5_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c6_5_iqas');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c6_5_iqas/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c6_5_delete($id) {
        $this->naac_model->delete_c6_5_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c6_5_iqas');
    }

    // --- KI 7.1: Institutional Values and Social Responsibilities ---
    public function c7_1_values_social_responsibilities() {
        $data['page_title'] = 'NAAC Criterion 7.1: Institutional Values and Social Responsibilities';
        $data['c7_1_data'] = $this->naac_model->get_c7_1_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c7_1_values_social_responsibilities/list', $data);
        $this->load->view('layout/footer');
    }

    public function c7_1_add() {
        $data['page_title'] = 'Add Entry for C7.1';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'gender_equity_measures' => $this->input->post('gender_equity_measures'),
                    'disabled_friendly_campus_description' => $this->input->post('disabled_friendly_campus_description'),
                    'inclusive_environment_initiatives' => $this->input->post('inclusive_environment_initiatives'),
                    'human_values_ethics_activities' => $this->input->post('human_values_ethics_activities'),
                    'commemorative_events_details' => $this->input->post('commemorative_events_details'),
                    'alternate_energy_conservation_details' => $this->input->post('alternate_energy_conservation_details'),
                    'waste_management_details' => $this->input->post('waste_management_details'),
                    'water_conservation_details' => $this->input->post('water_conservation_details'),
                    'green_campus_initiatives' => $this->input->post('green_campus_initiatives'),
                    'quality_audits_environment_energy' => $this->input->post('quality_audits_environment_energy'),
                    'code_of_conduct_details' => $this->input->post('code_of_conduct_details'),
                    'document_link_gender_equity_policy' => $this->input->post('document_link_gender_equity_policy'),
                    'document_link_disabled_friendly_policy' => $this->input->post('document_link_disabled_friendly_policy'),
                    'document_link_environmental_audit' => $this->input->post('document_link_environmental_audit'),
                    'document_link_code_of_conduct' => $this->input->post('document_link_code_of_conduct'),
                );
                $this->naac_model->add_c7_1_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c7_1_values_social_responsibilities');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c7_1_values_social_responsibilities/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c7_1_edit($id) {
        $data['page_title'] = 'Edit Entry for C7.1';
        $data['entry'] = $this->naac_model->get_c7_1_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'gender_equity_measures' => $this->input->post('gender_equity_measures'),
                    'disabled_friendly_campus_description' => $this->input->post('disabled_friendly_campus_description'),
                    'inclusive_environment_initiatives' => $this->input->post('inclusive_environment_initiatives'),
                    'human_values_ethics_activities' => $this->input->post('human_values_ethics_activities'),
                    'commemorative_events_details' => $this->input->post('commemorative_events_details'),
                    'alternate_energy_conservation_details' => $this->input->post('alternate_energy_conservation_details'),
                    'waste_management_details' => $this->input->post('waste_management_details'),
                    'water_conservation_details' => $this->input->post('water_conservation_details'),
                    'green_campus_initiatives' => $this->input->post('green_campus_initiatives'),
                    'quality_audits_environment_energy' => $this->input->post('quality_audits_environment_energy'),
                    'code_of_conduct_details' => $this->input->post('code_of_conduct_details'),
                    'document_link_gender_equity_policy' => $this->input->post('document_link_gender_equity_policy'),
                    'document_link_disabled_friendly_policy' => $this->input->post('document_link_disabled_friendly_policy'),
                    'document_link_environmental_audit' => $this->input->post('document_link_environmental_audit'),
                    'document_link_code_of_conduct' => $this->input->post('document_link_code_of_conduct'),
                );
                $this->naac_model->update_c7_1_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c7_1_values_social_responsibilities');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c7_1_values_social_responsibilities/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c7_1_delete($id) {
        $this->naac_model->delete_c7_1_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c7_1_values_social_responsibilities');
    }

    // --- KI 7.2: Best Practices ---
    public function c7_2_best_practices() {
        $data['page_title'] = 'NAAC Criterion 7.2: Best Practices';
        $data['c7_2_data'] = $this->naac_model->get_c7_2_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c7_2_best_practices/list', $data);
        $this->load->view('layout/footer');
    }

    public function c7_2_add() {
        $data['page_title'] = 'Add Entry for C7.2';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('best_practice_title_1', 'Best Practice 1 Title', 'required');
            $this->form_validation->set_rules('best_practice_description_1', 'Best Practice 1 Description', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'best_practice_title_1' => $this->input->post('best_practice_title_1'),
                    'best_practice_description_1' => $this->input->post('best_practice_description_1'),
                    'best_practice_title_2' => $this->input->post('best_practice_title_2'),
                    'best_practice_description_2' => $this->input->post('best_practice_description_2'),
                    'document_link_best_practice_1' => $this->input->post('document_link_best_practice_1'),
                    'document_link_best_practice_2' => $this->input->post('document_link_best_practice_2'),
                );
                $this->naac_model->add_c7_2_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c7_2_best_practices');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c7_2_best_practices/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c7_2_edit($id) {
        $data['page_title'] = 'Edit Entry for C7.2';
        $data['entry'] = $this->naac_model->get_c7_2_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('best_practice_title_1', 'Best Practice 1 Title', 'required');
            $this->form_validation->set_rules('best_practice_description_1', 'Best Practice 1 Description', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'best_practice_title_1' => $this->input->post('best_practice_title_1'),
                    'best_practice_description_1' => $this->input->post('best_practice_description_1'),
                    'best_practice_title_2' => $this->input->post('best_practice_title_2'),
                    'best_practice_description_2' => $this->input->post('best_practice_description_2'),
                    'document_link_best_practice_1' => $this->input->post('document_link_best_practice_1'),
                    'document_link_best_practice_2' => $this->input->post('document_link_best_practice_2'),
                );
                $this->naac_model->update_c7_2_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c7_2_best_practices');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c7_2_best_practices/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c7_2_delete($id) {
        $this->naac_model->delete_c7_2_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c7_2_best_practices');
    }

    // --- KI 7.3: Institutional Distinctiveness ---
    public function c7_3_institutional_distinctiveness() {
        $data['page_title'] = 'NAAC Criterion 7.3: Institutional Distinctiveness';
        $data['c7_3_data'] = $this->naac_model->get_c7_3_data();

        $this->load->view('layout/header', $data);
        $this->load->view('naac/c7_3_institutional_distinctiveness/list', $data);
        $this->load->view('layout/footer');
    }

    public function c7_3_add() {
        $data['page_title'] = 'Add Entry for C7.3';

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('distinctive_area_description', 'Distinctive Area Description', 'required');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'distinctive_area_description' => $this->input->post('distinctive_area_description'),
                    'document_link_distinctiveness_report' => $this->input->post('document_link_distinctiveness_report'),
                );
                $this->naac_model->add_c7_3_data($insert_data);
                $this->session->set_flashdata('msg', 'Entry added successfully');
                redirect('naac/c7_3_institutional_distinctiveness');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c7_3_institutional_distinctiveness/add', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c7_3_edit($id) {
        $data['page_title'] = 'Edit Entry for C7.3';
        $data['entry'] = $this->naac_model->get_c7_3_data($id);

        if (empty($data['entry'])) {
            show_404();
        }

        if ($this->input->post()) {
            $this->form_validation->set_rules('academic_year', 'Academic Year', 'required');
            $this->form_validation->set_rules('distinctive_area_description', 'Distinctive Area Description', 'required');

            if ($this->form_validation->run() == TRUE) {
                $update_data = array(
                    'academic_year' => $this->input->post('academic_year'),
                    'distinctive_area_description' => $this->input->post('distinctive_area_description'),
                    'document_link_distinctiveness_report' => $this->input->post('document_link_distinctiveness_report'),
                );
                $this->naac_model->update_c7_3_data($id, $update_data);
                $this->session->set_flashdata('msg', 'Entry updated successfully');
                redirect('naac/c7_3_institutional_distinctiveness');
            }
        } else {
            $this->load->view('layout/header', $data);
            $this->load->view('naac/c7_3_institutional_distinctiveness/edit', $data);
            $this->load->view('layout/footer');
        }
    }

    public function c7_3_delete($id) {
        $this->naac_model->delete_c7_3_data($id);
        $this->session->set_flashdata('msg', 'Entry deleted successfully');
        redirect('naac/c7_3_institutional_distinctiveness');
    }

}
