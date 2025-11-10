<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assign_incidental_fee extends Admin_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('incidental_fee_type_model');
        $this->load->model('incidental_fee_assignment_model');
        $this->load->model('session_model'); // Assuming a session model exists
        $this->load->model('class_model');   // Assuming a class model exists
        $this->load->model('student_model'); // Assuming a student model exists
        $this->load->library('form_validation');

    }

    public function index() {
        if (!$this->rbac->hasPrivilege('assign_incidental_fee', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/assign_incidental_fee');

        $this->load->model('setting_model'); // Load setting_model
        $current_session_id = $this->setting_model->getCurrentSession(); // Get current session ID

        $data['title'] = 'Assign Incidental Fee';
        $data['fee_types'] = $this->incidental_fee_type_model->get();
        // $data['sessions'] = $this->session_model->get(); // Removed
        $data['classes'] = $this->class_model->get();
        $data['current_session_id'] = $current_session_id; // Pass current session ID to view

        $this->form_validation->set_rules('fee_type_id', $this->lang->line('fee_type'), 'required|trim|xss_clean');
        // $this->form_validation->set_rules('session_id', $this->lang->line('session'), 'required|trim|xss_clean'); // Removed
        $this->form_validation->set_rules('amount_due', $this->lang->line('amount_due'), 'numeric|trim|xss_clean');
        // Validation for student_id[] or class_id[] will be conditional in the view/JS

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header');
            $this->load->view('admin/incidental_fee_assignment/assign_incidental_fee', $data);
            $this->load->view('layout/footer');
        } else {
            $fee_type_id = $this->input->post('fee_type_id');
            $session_id = $this->input->post('session_id'); // This will now come from the hidden input
            $amount_due = $this->input->post('amount_due');
            $due_date = $this->input->post('due_date');
            $assigned_by = $this->customlib->getStaffID();

            $students = $this->input->post('student_id'); // Array of student IDs
            $classes = $this->input->post('class_id');   // Array of class IDs

            $assigned_count = 0;

            if (in_array('all', $classes)) {
                $all_students = $this->student_model->getStudents();
                foreach ($all_students as $student) {
                    $insert_data = array(
                        'incidental_fee_type_id' => $fee_type_id,
                        'session_id' => $session_id,
                        'student_id' => $student['id'],
                        'class_id' => $student['class_id'],
                        'amount_due' => $amount_due,
                        'due_date' => $due_date,
                        'assigned_by' => $assigned_by,
                    );
                    $this->incidental_fee_assignment_model->add($insert_data);
                    $assigned_count++;
                }
            } else {
                if (!empty($students)) {
                    foreach ($students as $student_id) {
                        $insert_data = array(
                            'incidental_fee_type_id' => $fee_type_id,
                            'session_id' => $session_id,
                            'student_id' => $student_id,
                            'class_id' => NULL, // Specific student, not class
                            'amount_due' => $amount_due,
                            'due_date' => $due_date,
                            'assigned_by' => $assigned_by,
                        );
                        $this->incidental_fee_assignment_model->add($insert_data);
                        $assigned_count++;
                    }
                } elseif (!empty($classes)) {
                    foreach ($classes as $class_id) {
                        // Get all students in this class for the selected session
                        $students_in_class = $this->student_model->getStudentsByClassAndSession($class_id, $session_id); // Assuming this method exists
                        foreach ($students_in_class as $student) {
                            $insert_data = array(
                                'incidental_fee_type_id' => $fee_type_id,
                                'session_id' => $session_id,
                                'student_id' => $student['id'],
                                'class_id' => $class_id,
                                'amount_due' => $amount_due,
                                'due_date' => $due_date,
                                'assigned_by' => $assigned_by,
                            );
                            $this->incidental_fee_assignment_model->add($insert_data);
                            $assigned_count++;
                        }
                    }
                } else {
                    // Handle case where no students or classes are selected
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('no_student_or_class_selected') . '</div>');
                    redirect('admin/assign_incidental_fee');
                }
            }

            if ($assigned_count > 0) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('fee_assigned_successfully') . '</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">' . $this->lang->line('no_fee_assigned') . '</div>');
            }
            redirect('admin/assign_incidental_fee');
        }
    }

    // AJAX method to get students by class and section (if needed for dynamic selection)
    public function getStudentsByClass() {
        $class_id = $this->input->post('class_id');
        $session_id = $this->input->post('session_id');
        $students = $this->student_model->getStudentsByClassAndSession($class_id, $session_id); // Assuming this method exists
        echo json_encode($students);
    }

    public function getFeeTypeDetails() {
        $fee_type_id = $this->input->post('fee_type_id');
        $fee_type = $this->incidental_fee_type_model->get($fee_type_id); // Assuming get() method can fetch by ID

        // Add logging to see what is returned
        $log_file = FCPATH . 'assign_incidental_fee_log.txt';
        file_put_contents($log_file, "Fee Type Details for ID " . $fee_type_id . ": " . print_r($fee_type, true) . "\n", FILE_APPEND);

        echo json_encode($fee_type);
    }
}

?>