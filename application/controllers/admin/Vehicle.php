<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vehicle extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'vehicle/index');
        $data['title']           = 'Add Vehicle';
        $listVehicle             = $this->vehicle_model->get();
        $data['listVehicle']     = $listVehicle;
        $data['staffList']       = $this->staff_model->getAll(null, 1);
        $data['assigneesBySlot'] = $this->vehicle_model->getAssigneesBySlot();
        $data['wa_template_id']  = $this->vehicle_model->getNotificationConfig('wa_template_id');
        $data['notify_days']     = $this->vehicle_model->getNotificationConfig('notify_days') ?: '30,15,5,3';
        $data['enable_email']    = (int)($this->vehicle_model->getNotificationConfig('enable_email') ?? 1);
        $data['upcoming_expiries'] = $this->vehicle_model->getUpcomingExpiries(30);
        $this->load->view('layout/header');
        $this->load->view('admin/vehicle/index', $data);
        $this->load->view('layout/footer');
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('vehicle_no', $this->lang->line('vehicle_number'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('vehicle_photo', $this->lang->line('vehicle_photo'), 'callback_handle_upload');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'vehicle_no' => form_error('vehicle_no'),
                'vehicle_photo' => form_error('vehicle_photo'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {            
            
            $vehicle_photo = '';
            if (isset($_FILES["vehicle_photo"]) && !empty($_FILES['vehicle_photo']['name'])) {
                $upload_result = $this->media_storage->fileupload("vehicle_photo", "./uploads/vehicle_photo/");
                if ($upload_result['status'] === false) {
                    $msg = array('vehicle_photo' => $upload_result['message']);
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                    echo json_encode($array);
                    return;
                }
                $vehicle_photo = $upload_result['message'];
            }
            
            $data = array(
                'vehicle_no'           => $this->input->post('vehicle_no'),
                'vehicle_model'        => $this->input->post('vehicle_model'),
                'driver_name'          => $this->input->post('driver_name'),
                'driver_licence'       => $this->input->post('driver_licence'),
                'driver_contact'       => $this->input->post('driver_contact'),
                'note'                 => $this->input->post('note'),
                'registration_number'  => $this->input->post('registration_number'),
                'chasis_number'        => $this->input->post('chasis_number'),
                'engine_number'        => $this->input->post('engine_number'),
                'max_seating_capacity' => $this->input->post('max_seating_capacity'),
                'manufacture_year'      => $this->input->post('manufacture_year'),
                'vehicle_photo'        => $vehicle_photo,
                'fc_validity_start'    => ($ts = $this->customlib->datetostrtotime($this->input->post('fc_validity_start'))) ? date('Y-m-d', $ts) : null,
                'fc_validity_end'      => ($ts = $this->customlib->datetostrtotime($this->input->post('fc_validity_end'))) ? date('Y-m-d', $ts) : null,
                'insurance_start'      => ($ts = $this->customlib->datetostrtotime($this->input->post('insurance_start'))) ? date('Y-m-d', $ts) : null,
                'insurance_end'        => ($ts = $this->customlib->datetostrtotime($this->input->post('insurance_end'))) ? date('Y-m-d', $ts) : null,
                'permit_expiry_start'  => ($ts = $this->customlib->datetostrtotime($this->input->post('permit_expiry_start'))) ? date('Y-m-d', $ts) : null,
                'permit_expiry_end'    => ($ts = $this->customlib->datetostrtotime($this->input->post('permit_expiry_end'))) ? date('Y-m-d', $ts) : null,
                'road_tax_start'       => ($ts = $this->customlib->datetostrtotime($this->input->post('road_tax_start'))) ? date('Y-m-d', $ts) : null,
                'road_tax_end'         => ($ts = $this->customlib->datetostrtotime($this->input->post('road_tax_end'))) ? date('Y-m-d', $ts) : null,
                'pollution_cert_start' => ($ts = $this->customlib->datetostrtotime($this->input->post('pollution_cert_start'))) ? date('Y-m-d', $ts) : null,
                'pollution_cert_end'   => ($ts = $this->customlib->datetostrtotime($this->input->post('pollution_cert_end'))) ? date('Y-m-d', $ts) : null,
                'green_tax_start'      => ($ts = $this->customlib->datetostrtotime($this->input->post('green_tax_start'))) ? date('Y-m-d', $ts) : null,
                'green_tax_end'        => ($ts = $this->customlib->datetostrtotime($this->input->post('green_tax_end'))) ? date('Y-m-d', $ts) : null,
            );
           
            $this->vehicle_model->add($data);

            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);
        }
        echo json_encode($array);
    }

    public function getsinglevehicledata()
    {
        $vehicleid           = $this->input->post('vehicleid');
        $data['editvehicle'] = $this->vehicle_model->get($vehicleid);
        $page                = $this->load->view('admin/vehicle/edit', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function edit()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_edit')) {
            access_denied();
        }
        
        $this->form_validation->set_rules('vehicle_no', $this->lang->line('vehicle_number'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('vehicle_photo', $this->lang->line('vehicle_photo'), 'callback_handle_upload');
        $id =   $this->input->post('id');
        
        $vehicle              = $this->vehicle_model->get($id);       
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'vehicle_no' => form_error('vehicle_no'),
                'vehicle_photo' => form_error('vehicle_photo'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {           

            $data = array(
                'id'                   => $this->input->post('id'),
                'vehicle_no'           => $this->input->post('vehicle_no'),
                'vehicle_model'        => $this->input->post('vehicle_model'),
                'driver_name'          => $this->input->post('driver_name'),
                'driver_licence'       => $this->input->post('driver_licence'),
                'driver_contact'       => $this->input->post('driver_contact'),
                'note'                 => $this->input->post('note'),
                'registration_number'  => $this->input->post('registration_number'),
                'chasis_number'        => $this->input->post('chasis_number'),
                'engine_number'        => $this->input->post('engine_number'),
                'max_seating_capacity' => $this->input->post('max_seating_capacity'),
                'manufacture_year'     => $this->input->post('manufacture_year'),
                'fc_validity_start'    => ($ts = $this->customlib->datetostrtotime($this->input->post('fc_validity_start'))) ? date('Y-m-d', $ts) : null,
                'fc_validity_end'      => ($ts = $this->customlib->datetostrtotime($this->input->post('fc_validity_end'))) ? date('Y-m-d', $ts) : null,
                'insurance_start'      => ($ts = $this->customlib->datetostrtotime($this->input->post('insurance_start'))) ? date('Y-m-d', $ts) : null,
                'insurance_end'        => ($ts = $this->customlib->datetostrtotime($this->input->post('insurance_end'))) ? date('Y-m-d', $ts) : null,
                'permit_expiry_start'  => ($ts = $this->customlib->datetostrtotime($this->input->post('permit_expiry_start'))) ? date('Y-m-d', $ts) : null,
                'permit_expiry_end'    => ($ts = $this->customlib->datetostrtotime($this->input->post('permit_expiry_end'))) ? date('Y-m-d', $ts) : null,
                'road_tax_start'       => ($ts = $this->customlib->datetostrtotime($this->input->post('road_tax_start'))) ? date('Y-m-d', $ts) : null,
                'road_tax_end'         => ($ts = $this->customlib->datetostrtotime($this->input->post('road_tax_end'))) ? date('Y-m-d', $ts) : null,
                'pollution_cert_start' => ($ts = $this->customlib->datetostrtotime($this->input->post('pollution_cert_start'))) ? date('Y-m-d', $ts) : null,
                'pollution_cert_end'   => ($ts = $this->customlib->datetostrtotime($this->input->post('pollution_cert_end'))) ? date('Y-m-d', $ts) : null,
                'green_tax_start'      => ($ts = $this->customlib->datetostrtotime($this->input->post('green_tax_start'))) ? date('Y-m-d', $ts) : null,
                'green_tax_end'        => ($ts = $this->customlib->datetostrtotime($this->input->post('green_tax_end'))) ? date('Y-m-d', $ts) : null,
            );            
            
            if (isset($_FILES["vehicle_photo"]) && $_FILES['vehicle_photo']['name'] != '' && (!empty($_FILES['vehicle_photo']['name']))) {

                $upload_result = $this->media_storage->fileupload("vehicle_photo", "./uploads/vehicle_photo/");
                if ($upload_result['status'] === false) {
                    $msg = array('vehicle_photo' => $upload_result['message']);
                    $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
                    echo json_encode($array);
                    return;
                }
                $img_name = $upload_result['message'];
            } else {
                $img_name = $vehicle->vehicle_photo;
            }

            $data['vehicle_photo'] = $img_name;

            if (isset($_FILES["vehicle_photo"]) && $_FILES['vehicle_photo']['name'] != '' && (!empty($_FILES['vehicle_photo']['name']))) {
                if ($vehicle->vehicle_photo != '') {
                    $this->media_storage->filedelete($vehicle->vehicle_photo, "uploads/school_income");
                }
            }
            
            $this->vehicle_model->add($data);

            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);
        }
        echo json_encode($array);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_delete')) {
            access_denied();
        }        
        $this->vehicle_model->remove($id);
        redirect('admin/vehicle/index');
    }
    
    public function vehicledetails()
    {
        $vehicleid           = $this->input->post('vehicleid');
        $data['editvehicle'] = $this->vehicle_model->get($vehicleid);
        $page                = $this->load->view('admin/vehicle/_vehicledetails', $data, true);
        echo json_encode(array('page' => $page));
    }
    
    public function saveAssignees()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_edit')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }
        $data = [
            1 => (int)$this->input->post('assignee_1'),
            2 => (int)$this->input->post('assignee_2'),
            3 => (int)$this->input->post('assignee_3'),
        ];
        $this->vehicle_model->saveAssignees($data);

        // Save all notification config fields together
        $days_raw    = $this->input->post('notify_days');          // array of day values
        $days_clean  = [];
        if (is_array($days_raw)) {
            foreach ($days_raw as $d) {
                $d = (int)$d;
                if ($d > 0) $days_clean[] = $d;
            }
        }
        sort($days_clean);
        $this->vehicle_model->saveNotificationConfigs([
            'notify_days'   => $days_clean ? implode(',', $days_clean) : '30,15,5,3',
            'enable_email'  => $this->input->post('enable_email') ? 1 : 0,
            'wa_template_id'=> $this->input->post('wa_template_id') ?: null,
        ]);

        echo json_encode(['status' => 'success', 'message' => $this->lang->line('success_message')]);
    }

    public function downloadVehicleTemplate()
    {
        $file = FCPATH . 'backend/import/import_vehicle_sample.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="import_vehicle_sample.csv"');
        readfile($file);
        exit;
    }

    public function import()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_add')) {
            echo json_encode(['status' => 'fail', 'message' => 'Access denied']);
            return;
        }
        if (empty($_FILES['vehicle_csv']['name'])) {
            echo json_encode(['status' => 'fail', 'message' => 'No file uploaded']);
            return;
        }
        $this->load->library('csvimport');
        $csv_array = $this->csvimport->get_array($_FILES['vehicle_csv']['tmp_name']);
        if (empty($csv_array)) {
            echo json_encode(['status' => 'fail', 'message' => 'Empty or invalid CSV file']);
            return;
        }
        $result  = $this->vehicle_model->bulkInsert($csv_array);
        $message = '<div class="alert alert-success text-left">' . $result['inserted'] . ' vehicle(s) imported successfully.</div>';
        if (!empty($result['skipped'])) {
            $message .= '<div class="alert alert-warning text-left">Skipped (' . count($result['skipped']) . '): ' . implode(', ', $result['skipped']) . '</div>';
        }
        echo json_encode(['status' => 'success', 'message' => $message]);
    }

    public function handle_upload()
    {
        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["vehicle_photo"]) && !empty($_FILES['vehicle_photo']['name'])) {

            $file_type = $_FILES["vehicle_photo"]['type'];
            $file_size = $_FILES["vehicle_photo"]["size"];
            $file_name = $_FILES["vehicle_photo"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES['vehicle_photo']['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->image_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_extension_error_uploading_image'));
                return false;
            }

            return true;
        }
        return true;
    }

}
