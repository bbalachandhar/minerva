<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class FeeGroup extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('fees_group', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feegroup');

        $this->form_validation->set_rules(
            'name', $this->lang->line('name'), array(
                'required',
                array('check_exists', array($this->feegroup_model, 'check_exists')),
            )
        );
        if ($this->form_validation->run() == false) {

        } else {
            $data = array(
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
            );
            $this->feegroup_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/feegroup/index');
        }
        $current_session            = $this->setting_model->getCurrentSession();
        $selected_session           = (int)($this->input->get('session_id') ?: $current_session);
        $data['sessionList']        = $this->session_model->getAllSession();
        $data['selected_session']   = $selected_session;
        $data['feegroupList']       = $this->feegroup_model->getBySession($selected_session);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feegroup/feegroupList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('fees_group', 'can_delete')) {
            access_denied();
        }
        $this->feegroup_model->remove($id);
        redirect('admin/feegroup/index');
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('fees_group', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feegroup');
        $data['id']           = $id;
        $feegroup             = $this->feegroup_model->get($id);
        $data['feegroup']     = $feegroup;
        $feegroup_result      = $this->feegroup_model->get();
        $data['feegroupList'] = $feegroup_result;
        $this->form_validation->set_rules(
            'name', $this->lang->line('name'), array(
                'required',
                array('check_exists', array($this->feegroup_model, 'check_exists')),
            )
        );

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/feegroup/feegroupEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'id'          => $id,
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
            );
            $this->feegroup_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/feegroup/index');
        }
    }

    public function bulk_import()
    {
        if (!$this->rbac->hasPrivilege('fees_group', 'can_add')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feegroup');

        $data['title'] = $this->lang->line('bulk_import_fees_group');

        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/feegroup/feegroupBulkImport', $data);
            $this->load->view('layout/footer', $data);
        } else {
            // Process CSV file
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $handle = fopen($file, "r");
                    $header = fgetcsv($handle, 1000, ",");
                    $feegroups = [];
                    $has_name_header = false;
                    $has_description_header = false;

                    foreach ($header as $header_key => $header_value) {
                        if (trim(strtolower($header_value)) === 'name') {
                            $has_name_header = true;
                        } elseif (trim(strtolower($header_value)) === 'description') {
                            $has_description_header = true;
                        }
                    }

                    if (!$has_name_header) {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('csv_file_header_error') . '</div>');
                        redirect('admin/feegroup/bulk_import');
                    }

                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (count($header) == count($row)) {
                            $feegroups[] = array_combine($header, $row);
                        }
                    }
                    fclose($handle);

                    if (!empty($feegroups)) {
                        $imported_count = 0;
                        $failed_records = [];

                        foreach ($feegroups as $feegroup_data) {
                            $name = isset($feegroup_data['name']) ? trim($feegroup_data['name']) : '';
                            $description = isset($feegroup_data['description']) ? trim($feegroup_data['description']) : '';

                            if (!empty($name)) {
                                $existing_feegroup = $this->feegroup_model->checkGroupExistsByName($name);

                                $data = array(
                                    'name'        => $name,
                                    'description' => $description,
                                );

                                if ($existing_feegroup) {
                                    // Update existing record
                                    $data['id'] = $existing_feegroup->id;
                                    $this->feegroup_model->add($data); // Assuming add method handles update if ID is present
                                } else {
                                    // Insert new record
                                    $this->feegroup_model->add($data);
                                }
                                $imported_count++;
                            } else {
                                $feegroup_data['reason'] = $this->lang->line('missing_name_field');
                                $failed_records[] = $feegroup_data;
                            }
                        }

                        $message = '<div class="alert alert-success text-left">' . $imported_count . ' ' . $this->lang->line('records_imported_successfully') . '.</div>';

                        if (!empty($failed_records)) {
                            $message .= '<div class="alert alert-warning text-left">' . count($failed_records) . ' ' . $this->lang->line('records_not_imported') . ':<br>';
                            foreach ($failed_records as $record) {
                                $message .= 'Name: ' . (isset($record['name']) ? $record['name'] : '') . ', Description: ' . (isset($record['description']) ? $record['description'] : '') . ' - Reason: ' . $record['reason'] . '<br>';
                            }
                            $message .= '</div>';
                        }

                        $this->session->set_flashdata('msg', $message);
                        redirect('admin/feegroup/index');
                    } else {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('no_record_found') . '</div>');
                        redirect('admin/feegroup/bulk_import');
                    }
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('please_upload_csv_file_only') . '</div>');
                    redirect('admin/feegroup/bulk_import');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('please_select_file') . '</div>');
                redirect('admin/feegroup/bulk_import');
            }
        }
    }

    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_feegroup_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_feegroup_sample_file.csv';
        force_download($name, $data);
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array(
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt'
            );
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

}
