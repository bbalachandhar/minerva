<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Member extends Admin_Controller
{

    private function getLibraryPolicySettings()
    {
        $defaults = array(
            'student_max_books_allowed' => 3,
            'staff_max_books_allowed' => 5,
            'student_book_return_days' => 15,
            'staff_book_return_days' => 30,
        );

        $required_columns = array(
            'student_max_books_allowed',
            'staff_max_books_allowed',
            'student_book_return_days',
            'staff_book_return_days',
        );

        $existing_rows = $this->db->query('SHOW COLUMNS FROM sch_settings')->result_array();
        $existing_cols = array();
        foreach ($existing_rows as $row) {
            $existing_cols[] = $row['Field'];
        }

        foreach ($required_columns as $column) {
            if (!in_array($column, $existing_cols, true)) {
                return $defaults;
            }
        }

        $row = $this->db
            ->select('student_max_books_allowed, staff_max_books_allowed, student_book_return_days, staff_book_return_days')
            ->from('sch_settings')
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return $defaults;
        }

        return array(
            'student_max_books_allowed' => max(1, (int) ($row['student_max_books_allowed'] ?? $defaults['student_max_books_allowed'])),
            'staff_max_books_allowed' => max(1, (int) ($row['staff_max_books_allowed'] ?? $defaults['staff_max_books_allowed'])),
            'student_book_return_days' => max(1, (int) ($row['student_book_return_days'] ?? $defaults['student_book_return_days'])),
            'staff_book_return_days' => max(1, (int) ($row['staff_book_return_days'] ?? $defaults['staff_book_return_days'])),
        );
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model('book_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/index');
        $data['title']      = 'Member';
        $data['title_list'] = 'Members';
        $memberList         = $this->librarymember_model->get();

        $superadmin_visible = $this->customlib->superadmin_visible();

        if ($superadmin_visible == 'disabled') {
            $getStaffRole = $this->customlib->getStaffRole();
            $staffrole    = json_decode($getStaffRole);

            if ($staffrole->id != 7) {
                foreach ($memberList as $key => $member) {
                    if ($member['member_type'] != "student") {
                        $getrole = $this->staff_model->getAll($member['staff_id']);

                        if ($getrole['role_id'] != 7) {
                            $result[] = $member;
                        }

                    } else {
                        $result[] = $member;
                    }
                }
            } else {
                $result = $memberList;
            }
        } else {
            $result = $memberList;
        }

        $data['memberList']  = $result;
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header');
        $this->load->view('admin/librarian/index', $data);
        $this->load->view('layout/footer');
    }

    public function issue($id)
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/index');
        $data['title']        = 'Member';
        $data['title_list']   = 'Members';
        $memberList           = $this->librarymember_model->getByMemberID($id);
        $data['memberList']   = $memberList;
        $policy               = $this->getLibraryPolicySettings();
        $issue_date           = date('Y-m-d');
        $issue_date_display   = date($this->customlib->getSchoolDateFormat(), strtotime($issue_date));
        $is_student_member    = ($memberList && isset($memberList->member_type) && $memberList->member_type === 'student');
        $default_due_days     = $is_student_member ? (int) $policy['student_book_return_days'] : (int) $policy['staff_book_return_days'];
        $default_due_date     = date('Y-m-d', strtotime($issue_date . ' +' . $default_due_days . ' days'));
        $default_due_display  = date($this->customlib->getSchoolDateFormat(), strtotime($default_due_date));
        $data['issue_date_display']  = $issue_date_display;
        $data['default_due_display'] = $default_due_display;
        $issued_books         = $this->bookissue_model->getMemberBooks($id);
        $data['issued_books'] = $issued_books;

        $this->form_validation->set_rules('return_date', $this->lang->line('due_return_date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('book_id', $this->lang->line('books'), array('required', array('check_exists', array($this->bookissue_model, 'valid_check_exists')),
        )
        );
        if ($this->form_validation->run() == false) {

        } else {
            $member_id = $this->input->post('member_id');
            $member_type = $this->bookissue_model->getMemberTypeByLibraryMemberId($member_id);
            $allowed_limit = ($member_type === 'student') ? (int) $policy['student_max_books_allowed'] : (int) $policy['staff_max_books_allowed'];
            $active_issued = $this->bookissue_model->countActiveIssuedByMember($member_id);

            if ($active_issued >= $allowed_limit) {
                $member_label = ($member_type === 'student') ? 'student' : 'staff';
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Maximum allowed issued books reached for this ' . $member_label . ' (' . $allowed_limit . ').</div>');
                redirect('admin/member/issue/' . $member_id);
                return;
            }

            $data      = array(
                'book_id'        => $this->input->post('book_id'),
                'duereturn_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('return_date'))),
                'issue_date'     => date('Y-m-d'),
                'member_id'      => $this->input->post('member_id'),
            );
            $this->bookissue_model->add($data);

            // Update books.available to 'NO' after successful issue
            $book_id = $this->input->post('book_id'); // Get the book_id again
            $this->book_model->updateBookAvailability($book_id, 'NO');

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/member/issue/' . $member_id);
        }
       
        
        $selected_book      = null;
        $selected_book_id   = set_value('book_id');
        if (!empty($selected_book_id)) {
            $selected_book = $this->book_model->get((int) $selected_book_id);
        }
        $data['selected_book'] = $selected_book;
        
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view('layout/header');
        $this->load->view('admin/librarian/issue', $data);
        $this->load->view('layout/footer');
    }

    public function searchavailablebooks()
    {
        if (!$this->rbac->hasPrivilege('issue_return', 'can_view')) {
            access_denied();
        }

        $term   = trim((string) $this->input->get('term'));
        $page   = (int) $this->input->get('page');
        $limit  = 30;
        $page   = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $limit;

        $books   = $this->book_model->searchAvailableBooks($term, $limit, $offset);
        $results = array();

        foreach ($books as $book) {
            $label = trim((string) $book['book_title']);
            if (!empty($book['book_no'])) {
                $label .= ' (' . $book['book_no'] . ')';
            }
            $results[] = array(
                'id'   => (int) $book['id'],
                'text' => $label,
            );
        }

        $response = array(
            'results'    => $results,
            'pagination' => array('more' => count($books) === $limit),
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function bookreturn()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('member_id', $this->lang->line('member_id'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('return_date'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'id'        => form_error('id'),
                'member_id' => form_error('member_id'),
                'date'      => form_error('date'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $id        = $this->input->post('id');
            $member_id = $this->input->post('member_id');
            $date      = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date')));
            $data      = array(
                'id'          => $id,
                'return_date' => $date,
                'is_returned' => 1,
            );
            $this->bookissue_model->update($data);

            // Update books.available to 'YES' after book is returned
            $book_issue_record = $this->bookissue_model->get($id); // Assuming get($id) returns the book_issue record
            if ($book_issue_record) {
                $book_id = $book_issue_record['book_id'];
                $this->book_model->updateBookAvailability($book_id, 'YES');
            }

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function student()
    {
        if (!$this->rbac->hasPrivilege('add_student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'member/student');
        $data['title']     = 'Student Search';
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $button            = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/member/studentSearch', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class       = $this->input->post('class_id');
            $section     = $this->input->post('section_id');
            $search      = $this->input->post('search');
            $search_text = $this->input->post('search_text');
            if (isset($search)) {
                if ($search == 'search_filter') {
                    $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                    if ($this->form_validation->run() == false) {

                    } else {
                        $data['searchby']    = "filter";
                        $data['class_id']    = $this->input->post('class_id');
                        $data['section_id']  = $this->input->post('section_id');
                        $data['search_text'] = $this->input->post('search_text');
                        $resultlist          = $this->student_model->searchLibraryStudent($class, $section);

                        $data['resultlist'] = $resultlist;
                    }
                } else if ($search == 'search_full') {
                    $data['searchby']    = "text";
                    $data['class_id']    = $this->input->post('class_id');
                    $data['section_id']  = $this->input->post('section_id');
                    $data['search_text'] = trim($this->input->post('search_text'));
                    $resultlist          = $this->student_model->searchFullText($search_text);
                    $data['resultlist']  = $resultlist;
                }
            }
            $data['sch_setting'] = $this->sch_setting_detail;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/member/studentSearch', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    public function add()
    {
        if ($this->input->post('library_card_no') != "") {

            $this->form_validation->set_rules('library_card_no', $this->lang->line('library_card_number'), 'required|trim|xss_clean|callback_check_cardno_exists');
            if ($this->form_validation->run() == false) {
                $data = array(
                    'library_card_no' => form_error('library_card_no'),
                );
                $array = array('status' => 'fail', 'error' => $data);
                echo json_encode($array);
            } else {
                $library_card_no = $this->input->post('library_card_no');
                $student         = $this->input->post('member_id');
                $data            = array(
                    'member_type'     => 'student',
                    'member_id'       => $student,
                    'library_card_no' => $library_card_no,
                );

                $inserted_id = $this->librarymanagement_model->add($data);
                $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
                echo json_encode($array);
            }
        } else {
            $library_card_no = $this->input->post('library_card_no');
            $student         = $this->input->post('member_id');
            $data            = array(
                'member_type'     => 'student',
                'member_id'       => $student,
                'library_card_no' => $library_card_no,
            );

            $inserted_id = $this->librarymanagement_model->add($data);
            $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
            echo json_encode($array);
        }
    }

    public function check_cardno_exists()
    {
        $data['library_card_no'] = $this->security->xss_clean($this->input->post('library_card_no'));

        if ($this->librarymanagement_model->check_data_exists($data)) {
            $this->form_validation->set_message('check_cardno_exists', $this->lang->line('card_no_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function teacher()
    {
        $this->session->set_userdata('top_menu', 'Library');
        $this->session->set_userdata('sub_menu', 'Library/member/teacher');
        $data['title']       = 'Add Teacher';
        $data['teacherlist'] = $this->teacher_model->getLibraryTeacher(); 
        $data['genderList'] = $this->customlib->getGender();         
        $this->load->view('layout/header', $data);
        $this->load->view('admin/member/teacher', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addteacher()
    {
        if ($this->input->post('library_card_no') != "") {
            $this->form_validation->set_rules('library_card_no', $this->lang->line('library_card_number'), 'required|trim|xss_clean|callback_check_cardno_exists');
            if ($this->form_validation->run() == false) {
                $data = array(
                    'library_card_no' => form_error('library_card_no'),
                );
                $array = array('status' => 'fail', 'error' => $data);
                echo json_encode($array);
            } else {
                $library_card_no = $this->input->post('library_card_no');
                $student         = $this->input->post('member_id');
                $data            = array(
                    'member_type'     => 'teacher',
                    'member_id'       => $student,
                    'library_card_no' => $library_card_no,
                );

                $inserted_id = $this->librarymanagement_model->add($data);
                $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
                echo json_encode($array);
            }
        } else {
            $library_card_no = $this->input->post('library_card_no');
            $student         = $this->input->post('member_id');
            $data            = array(
                'member_type'     => 'teacher',
                'member_id'       => $student,
                'library_card_no' => $library_card_no,
            );

            $inserted_id = $this->librarymanagement_model->add($data);
            $array       = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'), 'inserted_id' => $inserted_id, 'library_card_no' => $library_card_no);
            echo json_encode($array);
        }
    }

    public function surrender()
    {
        $this->form_validation->set_rules('member_id', $this->lang->line('book'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

        } else {
            $member_id = $this->input->post('member_id');
              $row_affected=$this->librarymember_model->surrender($member_id);
            $array = array('status' => 'success', 'row_affected'=>$row_affected, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function bulk_add()
    {
        $students = $this->input->post('students');
        $added_count = 0;
        $skipped_count = 0;
        $errors = array();

        if (!empty($students)) {
            foreach ($students as $student) {
                $library_card_no = $student['admission_no'];
                $student_id = $student['student_id'];

                // Check if the student is already a member
                $data_exists = array('member_id' => $student_id, 'member_type' => 'student');
                if ($this->librarymanagement_model->check_data_exists($data_exists)) {
                    $skipped_count++;
                    continue;
                }

                // Check if the library card number already exists
                $data_card_exists = array('library_card_no' => $library_card_no);
                if ($this->librarymanagement_model->check_data_exists($data_card_exists)) {
                    $errors[] = "Library card number '" . $library_card_no . "' already exists for another member.";
                    continue;
                }

                $data = array(
                    'member_type'     => 'student',
                    'member_id'       => $student_id,
                    'library_card_no' => $library_card_no,
                );

                $inserted_id = $this->librarymanagement_model->add($data);
                if ($inserted_id) {
                    $added_count++;
                }
            }
        }

        if (empty($errors)) {
            $message = $this->lang->line('success_message');
            if ($added_count > 0) {
                $message = $added_count . " members added successfully.";
            }
            if ($skipped_count > 0) {
                $message .= " " . $skipped_count . " members were already added.";
            }
            $array = array('status' => 'success', 'error' => '', 'message' => $message);
        } else {
            $array = array('status' => 'fail', 'error' => implode("<br>", $errors));
        }

        echo json_encode($array);
    }

    public function bulk_add_teacher()
    {
        $staff_list = $this->input->post('staff');
        $added_count = 0;
        $skipped_count = 0;
        $errors = array();

        if (!empty($staff_list)) {
            foreach ($staff_list as $staff) {
                $staff_id = $staff['staff_id'];
                
                $staff_details = $this->staff_model->get($staff_id);

                if (!$staff_details || empty($staff_details['employee_id'])) {
                    $errors[] = "Staff with ID " . $staff_id . " not found or has no employee ID.";
                    continue;
                }

                $library_card_no = $staff_details['employee_id'];

                // Check if the staff is already a member
                $data_exists = array('member_id' => $staff_id, 'member_type' => 'teacher');
                if ($this->librarymanagement_model->check_data_exists($data_exists)) {
                    $skipped_count++;
                    continue;
                }

                // Check if the library card number already exists
                $data_card_exists = array('library_card_no' => $library_card_no);
                if ($this->librarymanagement_model->check_data_exists($data_card_exists)) {
                    $errors[] = "Library card number '" . $library_card_no . "' already exists for another member.";
                    continue;
                }

                $data = array(
                    'member_type'     => 'teacher',
                    'member_id'       => $staff_id,
                    'library_card_no' => $library_card_no,
                );

                $inserted_id = $this->librarymanagement_model->add($data);
                if ($inserted_id) {
                    $added_count++;
                }
            }
        }

        if (empty($errors)) {
            $message = $this->lang->line('success_message');
            if ($added_count > 0) {
                $message = $added_count . " members added successfully.";
            }
            if ($skipped_count > 0) {
                $message .= " " . $skipped_count . " members were already added.";
            }
            $array = array('status' => 'success', 'error' => '', 'message' => $message);
        } else {
            $array = array('status' => 'fail', 'error' => implode("<br>", $errors));
        }

        echo json_encode($array);
    }

}
