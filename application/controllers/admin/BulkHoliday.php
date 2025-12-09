<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class BulkHoliday extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('csvreader');
        $this->load->model('holiday_model');
        $this->load->model('setting_model');
        $this->load->library('form_validation');
        $this->load->library('customlib');
    }

    public function index()
    {
                if (!$this->rbac->hasPrivilege('annual_calendar', 'can_add')) {
                    access_denied();
                }
        $data['title'] = $this->lang->line('bulk_upload_holidays');
        $data['holiday_types'] = $this->holiday_model->get_holiday_type(); // Fetch holiday types for dropdown

        $this->session->set_userdata('top_menu', $this->lang->line('academics'));
        $this->session->set_userdata('sub_menu', 'admin/holiday'); // Highlight holiday menu

        $this->load->view('layout/header', $data);
        $this->load->view('admin/holiday/bulk_upload', $data);
        $this->load->view('layout/footer', $data);
    }

    public function bulk_upload()
    {
        if (!$this->rbac->hasPrivilege('annual_calendar', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');
        $this->form_validation->set_rules('default_holiday_type_id', $this->lang->line('default_holiday_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['title'] = $this->lang->line('bulk_upload_holidays');
            $data['holiday_types'] = $this->holiday_model->get_holiday_type();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/holiday/bulk_upload', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $file_path = $_FILES['file']['tmp_name'];
            $result = $this->csvreader->parse_file($file_path);

            if (!empty($result)) {
                $holidaysToAdd = [];
                $error_messages = [];
                $existingHolidaysCount = 0;
                $addedHolidaysCount = 0;

                $session_id = $this->setting_model->getCurrentSession();
                $staff_id = $this->customlib->getStaffID();
                $default_holiday_type_id = $this->input->post('default_holiday_type_id');

                foreach ($result as $row_num => $row) {
                    $current_row_errors = [];

                    $from_date_str = trim($row['from_date'] ?? '');
                    $to_date_str = trim($row['to_date'] ?? '');
                    $description = trim($row['description'] ?? '');
                    $holiday_type_name = trim($row['holiday_type'] ?? ''); // Optional: if CSV specifies type name

                    // Basic validation for required fields
                    if (empty($from_date_str) || empty($to_date_str) || empty($description)) {
                        $current_row_errors[] = $this->lang->line('missing_required_fields') . ' (from_date, to_date, description).';
                    }

                    // Validate date format (YYYY-MM-DD or MM/DD/YYYY)
                    $from_date = null;
                    $to_date = null;

                    // Attempt to parse dates, allowing YYYY-MM-DD or MM/DD/YYYY
                    if (!empty($from_date_str)) {
                        $from_date = $this->customlib->datetostrtotime($from_date_str);
                        if ($from_date === false) {
                            $current_row_errors[] = $this->lang->line('invalid_from_date_format') . ' (' . $from_date_str . '). ' . $this->lang->line('expected_format_yyyy_mm_dd_or_mm_dd_yyyy');
                        } else {
                            $from_date = date('Y-m-d', $from_date);
                        }
                    }

                    if (!empty($to_date_str)) {
                        $to_date = $this->customlib->datetostrtotime($to_date_str);
                        if ($to_date === false) {
                            $current_row_errors[] = $this->lang->line('invalid_to_date_format') . ' (' . $to_date_str . '). ' . $this->lang->line('expected_format_yyyy_mm_dd_or_mm_dd_yyyy');
                        } else {
                            $to_date = date('Y-m-d 23:59:00', $to_date); // End of day for to_date
                        }
                    }

                    // Ensure to_date is not before from_date
                    if ($from_date && $to_date && strtotime($from_date) > strtotime($to_date)) {
                        $current_row_errors[] = $this->lang->line('to_date_cannot_be_before_from_date');
                    }

                    // Determine holiday_type_id
                    $holiday_type_id = $default_holiday_type_id; // Start with default from form
                    if (!empty($holiday_type_name)) {
                        // Try to find holiday type by name
                        $found_type = $this->holiday_model->get_holiday_type_by_name($holiday_type_name);
                        if ($found_type) {
                            $holiday_type_id = $found_type['id'];
                        } else {
                            $current_row_errors[] = "Holiday Type not found" . ' (' . $holiday_type_name . '). ' . "Using default holiday type." . ' (Default ID: ' . $default_holiday_type_id . ')';
                        }
                    }

                    if (!empty($current_row_errors)) {
                        $error_messages[] = $this->lang->line('row') . ' ' . ($row_num + 2) . ': ' . implode(", ", $current_row_errors);
                        continue;
                    }

                    // Final check before adding: ensure valid dates
                    if ($from_date && $to_date) {
                        if (!$this->holiday_model->checkHolidayExists($from_date, $description)) {
                            $holidaysToAdd[] = [
                                'holiday_type'  => $holiday_type_id,
                                'from_date'     => $from_date,
                                'to_date'       => $to_date,
                                'description'   => $description,
                                'front_site'    => 1, // Assume imported holidays are visible on front site
                                'created_by'    => $staff_id,
                                'holiday_color' => '#FF0000', // Default color, can be made configurable
                                'session_id'    => $session_id,
                                'created_at'    => date('Y-m-d H:i:s'),
                            ];
                            $addedHolidaysCount++;
                        } else {
                            $existingHolidaysCount++;
                        }
                    }
                }

                if (!empty($holidaysToAdd)) {
                    $this->holiday_model->addBatch($holidaysToAdd);
                }

                if (empty($error_messages)) {
                    $message = sprintf($this->lang->line('holidays_imported_successfully_added_s_existing_s'), $addedHolidaysCount, $existingHolidaysCount);
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $message . '</div>');
                    redirect('admin/bulkholliday/index'); // Redirect to the bulk upload page
                } else {
                    $message = sprintf($this->lang->line('holidays_imported_with_errors_added_s_existing_s'), $addedHolidaysCount, $existingHolidaysCount);
                    $this->session->set_flashdata('msg', '<div class="alert alert-warning text-center">' . $message . '</div>');
                    $data['error_messages'] = $error_messages;
                    $data['title'] = $this->lang->line('bulk_upload_holidays');
                    $data['holiday_types'] = $this->holiday_model->get_holiday_type();
                    $this->load->view('layout/header', $data);
                    $this->load->view('admin/holiday/bulk_upload', $data);
                    $this->load->view('layout/footer', $data);
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('error_processing_file_empty_or_invalid') . '</div>');
                redirect('admin/bulkholliday/index');
            }
        }
    }

    public function handle_csv_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedMimeTypes = array(
                'text/csv',
                'application/vnd.ms-excel',
                'application/csv',
                'application/x-csv',
                'text/x-csv',
                'text/plain',
                'text/x-comma-separated-values', // Added this MIME type
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // For .xlsx
            );
            $mime = get_mime_by_extension($_FILES['file']['name']);

            if (!in_array($mime, $allowedMimeTypes)) {
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }
    

}
