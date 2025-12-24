<?php

class LeaveTypes extends Admin_Controller
{

    public function __construct()
    {

        parent::__construct();
        $this->load->helper('file');
        $this->config->load("payroll");
        $this->load->model('leavetypes_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leavetypes');
        $data["title"]     = $this->lang->line('add_leave_type');
        $LeaveTypes        = $this->leavetypes_model->getLeaveType();
        $data["leavetype"] = $LeaveTypes;
        $this->load->view("layout/header");
        $this->load->view("admin/staff/leavetypes", $data);
        $this->load->view("layout/footer");
    }

    public function createleavetype()
    {
        $this->form_validation->set_rules(
            'type', $this->lang->line('name'), array('required',
                array('check_exists', array($this->leavetypes_model, 'valid_leave_type')),
            )
        );
        
        $leavetypeid = $this->input->post("leavetypeid");
        
        if (!empty($leavetypeid)) {
            $data["title"] = $this->lang->line('edit_leave_type');            
            $result            = $this->staff_model->getLeaveType($leavetypeid);        
            $data["result"]    = $result;        
        } else {
            $data["title"] = $this->lang->line('add_leave_type');
        }  
        
        if ($this->form_validation->run()) {

            $type = $this->input->post("type");
            $leavetypeid = $this->input->post("leavetypeid");
            $is_lop = $this->input->post("is_lop");
            $is_carry_forward = $this->input->post("is_carry_forward");
            $max_carry_forward = $this->input->post("max_carry_forward");
            $gender_specific = $this->input->post("gender_specific");
            $leave_encashment = $this->input->post("leave_encashment");
            $is_staff_specific = $this->input->post("is_staff_specific");
            $max_leave_days = $this->input->post("max_leave_days");

            if (empty($leavetypeid)) {

                if (!$this->rbac->hasPrivilege('leave_types', 'can_add')) {
                    access_denied();
                }
            } else {

                if (!$this->rbac->hasPrivilege('leave_types', 'can_edit')) {
                    access_denied();
                }
            }

            $data = array(
                'type' => $type,
                'is_lop' => $is_lop ? 1 : 0,
                'is_carry_forward' => $is_carry_forward ? 1 : 0,
                'max_carry_forward' => $is_carry_forward ? $max_carry_forward : 0,
                'gender_specific' => $gender_specific,
                'leave_encashment' => $leave_encashment ? 1 : 0,
                'is_staff_specific' => $is_staff_specific,
                'max_leave_days' => $max_leave_days,
                'is_active' => 'yes'
            );

            if (!empty($leavetypeid)) {
                $data['id'] = $leavetypeid;
            }

            $this->leavetypes_model->addLeaveType($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
            redirect("admin/leavetypes");
        } else {

            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            $LeaveTypes = $this->leavetypes_model->getLeaveType();
            $data["leavetype"] = $LeaveTypes;
            $this->load->view("layout/header");
            $this->load->view("admin/staff/leavetypes", $data);
            $this->load->view("layout/footer");
        }
    }

    public function leaveedit($id)
    {
        $result            = $this->staff_model->getLeaveType($id);
        $data["title"]     = $this->lang->line('edit_leave_type');
        $data["result"]    = $result;
        $LeaveTypes        = $this->leavetypes_model->getLeaveType();
        $data["leavetype"] = $LeaveTypes;
        $this->load->view("layout/header");
        $this->load->view("admin/staff/leavetypes", $data);
        $this->load->view("layout/footer");
    }

    public function leavedelete($id)
    {
        $this->leavetypes_model->deleteLeaveType($id);
        redirect('admin/leavetypes');
    }

    public function applyLeaveToAll()
    {
        $this->form_validation->set_rules('leave_type_id', $this->lang->line('leave_type'), 'required');
        $this->form_validation->set_rules('days', $this->lang->line('days'), 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $array = array('status' => 'fail', 'message' => validation_errors());
            echo json_encode($array);
        } else {
            $leave_type_id = $this->input->post('leave_type_id');
            $days = $this->input->post('days');
            $overwrite = $this->input->post('overwrite') ? true : false;

            $staff_list = $this->staff_model->get();

            foreach ($staff_list as $staff) {
                $this->leavetypes_model->update_staff_leave_details($staff['id'], $leave_type_id, $days, $overwrite);
            }

            $array = array('status' => 'success', 'message' => $this->lang->line('record_updated_successfully'));
            echo json_encode($array);
        }
    }

    public function bulk_upload()
    {
        $this->load->view("layout/header");
        $this->load->view("admin/staff/leavetypes_bulk_upload");
        $this->load->view("layout/footer");
    }

    public function handle_bulk_upload()
    {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $this->load->library('csvreader');
            $result = $this->csvreader->parse_file($_FILES['file']['tmp_name']);

            foreach ($result as $row) {
                $staff_id = $row['staff_id'];
                $leave_type_id = $row['leave_type_id'];
                $days = $row['days'];
                $this->leavetypes_model->update_staff_leave_details($staff_id, $leave_type_id, $days, true);
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('bulk_upload_successfully') . '</div>');
            redirect("admin/leavetypes/bulk_upload");
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line('please_upload_a_csv_file') . '</div>');
            redirect("admin/leavetypes/bulk_upload");
        }
    }
    
    public function download_sample()
    {
        $this->load->helper('download');
        $filepath = "uploads/sample_leave_allotment.csv";
        $data     = file_get_contents($filepath);
        $name     = 'sample_leave_allotment.csv';
        force_download($name, $data);
    }
}
