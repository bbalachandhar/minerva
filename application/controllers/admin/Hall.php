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
        $this->load->library('media_storage');
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
                'closing_time' => date('Y-m-d H:i:s'),
                'is_active' => $this->input->post('is_active') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'image' => NULL // Initialized here
            );

            $config['upload_path'] = FCPATH . 'uploads/halls/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size'] = '2048'; // 2MB
            $config['encrypt_name'] = TRUE;
            $this->load->library('upload', $config);

            // Handle image upload
            if (isset($_FILES["image"]) && $_FILES['image']['name'] != '') {
                $upload_result = $this->media_storage->fileupload('image', 'uploads/halls/');
                if ($upload_result['status'] === false) {
                    $error = $upload_result['message'];
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $error . '</div>');
                    redirect('admin/hall/hall_master');
                } else {
                    $insert_data['image'] = 'uploads/halls/' . $upload_result['message'];
                }
            }

            $this->Hall_model->add_hall($insert_data);
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
        if (empty($data['hall'])) {
            show_404();
        }

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

            $old_hall = $this->Hall_model->get_hall($this->input->post('id'));
            $update_data['image'] = $old_hall->image;

            // Handle image upload
            if (isset($_FILES["image"]) && $_FILES['image']['name'] != '') {
                $upload_result = $this->media_storage->fileupload('image', 'uploads/halls/');

                if ($upload_result['status'] === false) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $upload_result['message'] . '</div>');
                    redirect('admin/hall/hall_master');
                } else {
                    $update_data['image'] = 'uploads/halls/' . $upload_result['message'];

                    // Delete old image if it exists
                    if (!empty($old_hall->image)) {
                        $this->media_storage->filedelete($old_hall->image, 'uploads/halls/');
                    }
                }
            }

            $this->Hall_model->update_hall($this->input->post('id'), $update_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/hall/hall_master');
        }
    }

    public function ajax_upload_hall_image()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');

        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $upload_result = $this->_do_upload('file');

            if (isset($upload_result['error'])) {
                $array = array('success' => false, 'error' => array('file' => $upload_result['error']));
                echo json_encode($array);
                return;
            } else {
                $file_data = $upload_result['upload_data'];
                $img_name = 'uploads/halls/' . $file_data['file_name'];

                // Delete old image if it exists
                if (!empty($old_hall->image) && file_exists($old_hall->image)) {
                    unlink($old_hall->image);
                }

                $data_record = array('id' => $id, 'image' => $img_name);
                $this->Hall_model->update_hall($id, $data_record);
                $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'), 'image_url' => base_url($img_name));
                echo json_encode($array);
            }
        }

        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->input->post('id');
            $old_hall = $this->Hall_model->get_hall($id);

            $img_name = $old_hall->image; // Keep old image if no new one is uploaded

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $upload_result = $this->media_storage->fileupload('file', 'uploads/halls/');

                if ($upload_result['status'] === false) {
                    $array = array('success' => false, 'error' => array('file' => $upload_result['message']));
                    echo json_encode($array);
                    return;
                } else {
                    $img_name = 'uploads/halls/' . $upload_result['message'];

                    // Delete old image if it exists
                    if (!empty($old_hall->image)) {
                        $this->media_storage->filedelete($old_hall->image, 'uploads/halls/');
                    }
                }
            }

            $data_record = array('id' => $id, 'image' => $img_name);
            $this->Hall_model->update_hall($id, $data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'), 'image_url' => base_url($img_name));
            echo json_encode($array);
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
        redirect('admin/hall/hall_master');
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
            redirect(site_url('admin/hall/bookings')); // Redirect to booking list to see pending request
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
            redirect('admin/hall/approval_configuration');
        }
    }

    public function edit_approval_config($id)
    {
        if (!$this->rbac->hasPrivilege('approval_configuration', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Hall Management');
        $this->session->set_userdata('sub_menu', 'hall/approval_configuration');

        $data['title'] = $this->lang->line('edit_approval_configuration'); // New language key needed
        $data['config'] = $this->Hall_model->get_approval_config($id);
        if (empty($data['config'])) {
            show_404();
        }

        $data['configList'] = $this->Hall_model->get_all_approval_configs();
        $data['hallList'] = $this->Hall_model->get_all_halls();
        $data['staffList'] = $this->staff_model->get();
        $data['roleList'] = $this->role_model->get();

        $this->form_validation->set_rules('approver_type', $this->lang->line('approver_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('approver_id', $this->lang->line('approver'), 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('can_approve', $this->lang->line('can_approve'), 'trim|required|numeric|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/hall/approvalConfig', $data); // Re-use the same view for edit
            $this->load->view('layout/footer', $data);
        } else {
            $approver_type = $this->input->post('approver_type');
            $approver_id = $this->input->post('approver_id');
            $hall_id = $this->input->post('hall_id');
            $can_approve = $this->input->post('can_approve');

            $update_data = array(
                'id' => $id,
                'approver_type' => $approver_type,
                'hall_id' => !empty($hall_id) ? $hall_id : NULL,
                'can_approve' => $can_approve,
                'updated_at' => date('Y-m-d H:i:s')
            );

            if ($approver_type == 'role') {
                $update_data['role_id'] = $approver_id;
                $update_data['staff_id'] = NULL;
            } else { // 'staff'
                $update_data['staff_id'] = $approver_id;
                $update_data['role_id'] = NULL;
            }

            $this->Hall_model->update_approval_config($id, $update_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/hall/approval_configuration');
        }
    }

    public function getApprovalConfigs()
    {
        if (!$this->rbac->hasPrivilege('approval_configuration', 'can_view')) {
            access_denied();
        }
        $this->load->model('Hall_model');

        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $order = $this->input->post('order');
        $search = $this->input->post('search');
        $search_value = $search['value'];

        $total_records = $this->Hall_model->count_all_approval_configs();
        $filtered_records = $this->Hall_model->count_filtered_approval_configs($search_value);

        $configs = $this->Hall_model->get_all_approval_configs($start, $length, $order, $search_value);
        $list = array();

        foreach ($configs as $config) {
            $row = array();
            $row[] = ($config->approver_type == 'role') ? $this->lang->line('role') : $this->lang->line('staff');
            $row[] = ($config->approver_type == 'role') ? $config->role_name : $config->staff_name . ' (' . $config->employee_id . ')';
            $row[] = !empty($config->hall_name) ? $config->hall_name : $this->lang->line('all_halls');
            $row[] = ($config->can_approve == '1') ? $this->lang->line('yes') : $this->lang->line('no');
            $buttons = '<a href="' . base_url() . 'admin/hall/edit_approval_config/' . $config->id . '" class="btn btn-default btn-xs" data-toggle="tooltip" title="' . $this->lang->line('edit') . '"><i class="fa fa-pencil"></i></a>';
            $buttons .= ' <a href="javascript:void(0);" data-id="' . $config->id . '" class="btn btn-default btn-xs delete-approval-config" data-toggle="tooltip" title="' . $this->lang->line('delete') . '"><i class="fa fa-remove"></i></a>';
            $row[] = $buttons;

            $list[] = $row;
        }

        $output = array(
            "draw" => intval($draw),
            "recordsTotal" => intval($total_records),
            "recordsFiltered" => intval($filtered_records),
            "data" => $list,
        );

        echo json_encode($output);
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