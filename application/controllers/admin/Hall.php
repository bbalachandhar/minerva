<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hall extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hall_model');
        $this->load->library('upload');
        $this->load->model('staff_model');
        $this->load->model('role_model');
    }

    public function hall_master()
    {
        if (!$this->rbac->hasPrivilege('hall_master', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/hall_master'); // Updated sub_menu

        $data['title'] = $this->lang->line('hall_list');
        $data['hallList'] = $this->Hall_model->get_all_halls();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/hall/hallList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('hall_master', 'can_add')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/hall_master');

        $data['title'] = $this->lang->line('add_hall');

        $this->form_validation->set_rules('name', $this->lang->line('hall_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('capacity', $this->lang->line('capacity'), 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('location', $this->lang->line('location'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('opening_time', $this->lang->line('opening_time'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('closing_time', $this->lang->line('closing_time'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $data['hallList'] = $this->Hall_model->get_all_halls();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/hall/hallList', $data); // Show list with validation errors
            $this->load->view('layout/footer', $data);
        } else {
            $insert_data = array(
                'name' => $this->input->post('name'),
                'capacity' => $this->input->post('capacity'),
                'location' => $this->input->post('location'),
                'description' => $this->input->post('description'),
                'available_equipment' => $this->input->post('available_equipment'),
                'hourly_rate' => $this->input->post('hourly_rate'),
                'min_booking_duration' => $this->input->post('min_booking_duration'),
                'max_booking_duration' => $this->input->post('max_booking_duration'),
                'opening_time' => $this->input->post('opening_time'),
                'closing_time' => $this->input->post('closing_time'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            // Handle image upload
            if (isset($_FILES["image"]) && $_FILES['image']['name'] != '') {
                $config['upload_path'] = './uploads/halls/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = '2048'; // 2MB
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $error = array('error' => $this->upload->display_errors());
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $error['error'] . '</div>');
                    redirect('admin/hall/index');
                } else {
                    $file_data = array('upload_data' => $this->upload->data());
                    $insert_data['image'] = 'uploads/halls/' . $file_data['upload_data']['file_name'];
                }
            }

            $this->Hall_model->add_hall($insert_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/hall/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('hall_master', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/hall_master');

        $data['title'] = $this->lang->line('edit_hall');
        $data['id'] = $id;
        $data['hall'] = $this->Hall_model->get_hall($id);

        $this->form_validation->set_rules('name', $this->lang->line('hall_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('capacity', $this->lang->line('capacity'), 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('location', $this->lang->line('location'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('opening_time', $this->lang->line('opening_time'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('closing_time', $this->lang->line('closing_time'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $data['hallList'] = $this->Hall_model->get_all_halls();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/hall/hallEdit', $data); // Separate view for editing
            $this->load->view('layout/footer', $data);
        } else {
            $update_data = array(
                'id' => $this->input->post('id'),
                'name' => $this->input->post('name'),
                'capacity' => $this->input->post('capacity'),
                'location' => $this->input->post('location'),
                'description' => $this->input->post('description'),
                'available_equipment' => $this->input->post('available_equipment'),
                'hourly_rate' => $this->input->post('hourly_rate'),
                'min_booking_duration' => $this->input->post('min_booking_duration'),
                'max_booking_duration' => $this->input->post('max_booking_duration'),
                'opening_time' => $this->input->post('opening_time'),
                'closing_time' => $this->input->post('closing_time'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            );

            // Handle image upload
            if (isset($_FILES["image"]) && $_FILES['image']['name'] != '') {
                $config['upload_path'] = './uploads/halls/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg';
                $config['max_size'] = '2048'; // 2MB
                $config['encrypt_name'] = TRUE;

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('image')) {
                    $error = array('error' => $this->upload->display_errors());
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $error['error'] . '</div>');
                    redirect('admin/hall/index');
                } else {
                    $file_data = array('upload_data' => $this->upload->data());
                    $update_data['image'] = 'uploads/halls/' . $file_data['upload_data']['file_name'];

                    // Delete old image if it exists
                    $old_hall = $this->Hall_model->get_hall($this->input->post('id'));
                    if (!empty($old_hall->image) && file_exists($old_hall->image)) {
                        unlink($old_hall->image);
                    }
                }
            }

            $this->Hall_model->update_hall($this->input->post('id'), $update_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/hall/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('hall_master', 'can_delete')) {
            access_denied();
        }
        $data['title'] = $this->lang->line('hall_list');
        $this->Hall_model->delete_hall($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/hall/index');
    }

    public function hall_bookings()
    {
        if (!$this->rbac->hasPrivilege('hall_bookings', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/hall_bookings'); // Updated sub_menu

        $data['title'] = $this->lang->line('hall_bookings');
        $data['bookingList'] = $this->Hall_model->get_all_bookings();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/hall/bookingList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function book()
    {
        if (!$this->rbac->hasPrivilege('book_hall', 'can_view')) { // Assuming a privilege for booking
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/book'); // Corresponds to the menu item 'Book Hall'

        $data['title'] = $this->lang->line('book_hall');
        $data['hallList'] = $this->Hall_model->get_all_halls(); // To select a hall

        $this->form_validation->set_rules('hall_id', $this->lang->line('hall'), 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('purpose', $this->lang->line('purpose'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('start_time', $this->lang->line('start_time'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('end_time', $this->lang->line('end_time'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/hall/bookHall', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $hall_id = $this->input->post('hall_id');
            $purpose = $this->input->post('purpose');
            $start_time_str = $this->input->post('start_time');
            $end_time_str = $this->input->post('end_time');

            // Convert to YYYY-MM-DD HH:MM:SS format for database
            $start_time = date('Y-m-d H:i:s', strtotime($start_time_str));
            $end_time = date('Y-m-d H:i:s', strtotime($end_time_str));

            // Get current logged in staff ID
            $staff_id = $this->customlib->getStaffID(); // Assuming this function exists and returns staff ID

            // Check availability
            if (!$this->Hall_model->check_availability($hall_id, $start_time, $end_time)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('hall_not_available') . '</div>');
                redirect('admin/hall/book');
            }

            $insert_data = array(
                'hall_id' => $hall_id,
                'booked_by_user_id' => $staff_id,
                'purpose' => $purpose,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'status' => 'pending', // Bookings usually start as pending approval
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            $this->Hall_model->add_booking($insert_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('booking_request_sent') . '</div>');
            redirect('admin/hall/bookings'); // Redirect to booking list to see pending request
        }
    }

    public function approval_configuration()
    {
        if (!$this->rbac->hasPrivilege('approval_configuration', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/approval_configuration'); // Updated sub_menu

        $data['title'] = $this->lang->line('approval_configuration');
        $data['configList'] = $this->Hall_model->get_all_approval_configs();
        $data['hallList'] = $this->Hall_model->get_all_halls(); // For selecting halls in the form
        $data['staffList'] = $this->staff_model->get(); // Using get() method to retrieve staff list
        $data['roleList'] = $this->role_model->get(); // Assuming role_model exists and has get()

        $this->form_validation->set_rules('approver_type', $this->lang->line('approver_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('approver_id', $this->lang->line('approver'), 'trim|required|numeric|xss_clean');
        // hall_id is optional, so no 'required' rule
        $this->form_validation->set_rules('can_approve', $this->lang->line('can_approve'), 'trim|required|numeric|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/hall/approvalConfig', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $approver_type = $this->input->post('approver_type');
            $approver_id = $this->input->post('approver_id');
            $hall_id = $this->input->post('hall_id'); // Can be empty
            $can_approve = $this->input->post('can_approve');

            $insert_data = array(
                'approver_type' => $approver_type,
                'hall_id' => !empty($hall_id) ? $hall_id : NULL,
                'can_approve' => $can_approve,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($approver_type == 'role') {
                $insert_data['role_id'] = $approver_id;
                $insert_data['staff_id'] = NULL;
            } else { // 'staff'
                $insert_data['staff_id'] = $approver_id;
                $insert_data['role_id'] = NULL;
            }

            $this->Hall_model->add_approval_config($insert_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/hall/approval_config');
        }
    }

    public function approve_booking($id)
    {
        if (!$this->rbac->hasPrivilege('hall_booking_approval', 'can_edit')) { // Assuming a privilege for approval
            access_denied();
        }
        $booking = $this->Hall_model->get_booking($id);
        if (empty($booking)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('no_record_found') . '</div>');
            redirect('admin/hall/bookings');
        }

        $update_data = array(
            'status' => 'approved',
            'remarks' => $this->input->post('remarks'), // Optional remarks from approver
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->Hall_model->update_booking($id, $update_data);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('booking_approved_successfully') . '</div>');
        redirect('admin/hall/bookings');
    }

    public function reject_booking($id)
    {
        if (!$this->rbac->hasPrivilege('hall_booking_approval', 'can_edit')) { // Assuming a privilege for approval
            access_denied();
        }
        $booking = $this->Hall_model->get_booking($id);
        if (empty($booking)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('no_record_found') . '</div>');
            redirect('admin/hall/bookings');
        }

        $update_data = array(
            'status' => 'rejected',
            'remarks' => $this->input->post('remarks'), // Optional remarks from approver
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->Hall_model->update_booking($id, $update_data);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('booking_rejected_successfully') . '</div>');
        redirect('admin/hall/bookings');
    }
}
