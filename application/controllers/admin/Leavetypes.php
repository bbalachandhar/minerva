<?php

class LeaveTypes extends Admin_Controller
{
    private $hasBalanceCheckFlagColumn = null;
    private $hasStrictDayLockFlagColumn = null;

    public function __construct()
    {

        parent::__construct();
        $this->load->helper('file');
        $this->config->load("payroll");
        $this->load->model('leavetypes_model');
        $this->load->model('staff_model');
    }

    private function hasRequiresBalanceCheckColumn()
    {
        if ($this->hasBalanceCheckFlagColumn !== null) {
            return $this->hasBalanceCheckFlagColumn;
        }

        $row = $this->db->query("SHOW COLUMNS FROM leave_types LIKE 'requires_balance_check'")->row_array();
        $this->hasBalanceCheckFlagColumn = !empty($row);
        return $this->hasBalanceCheckFlagColumn;
    }

    private function hasCreditSourceColumn()
    {
        return $this->db->field_exists('credit_source_type_id', 'leave_types');
    }

    private function hasDayTypeColumn()
    {
        return $this->db->field_exists('day_type_restriction', 'leave_types');
    }

    private function hasStrictDayLockColumn()
    {
        if ($this->hasStrictDayLockFlagColumn !== null) {
            return $this->hasStrictDayLockFlagColumn;
        }
        $this->hasStrictDayLockFlagColumn = $this->db->field_exists('strict_day_lock', 'leave_types');
        return $this->hasStrictDayLockFlagColumn;
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'HR');
        $this->session->set_userdata('sub_menu', 'admin/leavetypes');
        $data["title"]     = $this->lang->line('add_leave_type');
        $LeaveTypes        = $this->leavetypes_model->getLeaveType();
        $data["leavetype"] = $LeaveTypes;
        $data['has_balance_check_flag']    = $this->hasRequiresBalanceCheckColumn();
        $data['has_credit_source_flag']     = $this->hasCreditSourceColumn();
        $data['has_day_type_flag']          = $this->hasDayTypeColumn();
        $data['has_strict_day_lock_flag']   = $this->hasStrictDayLockColumn();
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
            $requires_balance_check = $this->input->post("requires_balance_check") ? 1 : 0;
            $credit_source_type_id = (int) $this->input->post("credit_source_type_id");
            $day_type_restriction = $this->input->post("day_type_restriction");
            if (!in_array($day_type_restriction, ['working_day', 'holiday'], true)) {
                $day_type_restriction = null;
            }
            $strict_day_lock = $this->input->post("strict_day_lock") ? 1 : 0;

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

            if ($this->hasRequiresBalanceCheckColumn()) {
                $data['requires_balance_check'] = $requires_balance_check;
            }

            if ($this->hasCreditSourceColumn()) {
                $data['credit_source_type_id'] = ($credit_source_type_id > 0 && $credit_source_type_id != (int)$leavetypeid) ? $credit_source_type_id : null;
            }

            if ($this->hasDayTypeColumn()) {
                $data['day_type_restriction'] = $day_type_restriction;
            }

            if ($this->hasStrictDayLockColumn()) {
                $data['strict_day_lock'] = $strict_day_lock;
            }

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
            $data['has_balance_check_flag']    = $this->hasRequiresBalanceCheckColumn();
            $data['has_credit_source_flag']     = $this->hasCreditSourceColumn();
            $data['has_day_type_flag']          = $this->hasDayTypeColumn();
            $data['has_strict_day_lock_flag']   = $this->hasStrictDayLockColumn();
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
        $data['has_balance_check_flag']    = $this->hasRequiresBalanceCheckColumn();
        $data['has_credit_source_flag']     = $this->hasCreditSourceColumn();
        $data['has_day_type_flag']          = $this->hasDayTypeColumn();
        $data['has_strict_day_lock_flag']   = $this->hasStrictDayLockColumn();
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
            $this->load->library('CSVReader');
            $this->load->model('Payroll_model');
            $result = $this->csvreader->parse_file($_FILES['file']['tmp_name']);
            
            $processed_count = 0;
            $skipped_count = 0;
            $skipped_records = [];

            foreach ($result as $row_index => $row) {
                $employee_identifier = isset($row['employee_id']) ? trim((string) $row['employee_id']) : '';
                $leave_type_id = $row['leavetype_id'] ?? null;
                $days = $row['balance_days'] ?? null;
                $month = $row['month'] ?? date('n'); // Default to current month
                $year = $row['year'] ?? date('Y'); // Default to current year

                if ($employee_identifier === '' || $leave_type_id === null || $days === null || $leave_type_id === '' || $days === '') {
                    $skipped_count++;
                    $skipped_records[] = [
                        'row' => $row_index + 2, // +2 because CSV starts at line 1 (header) and array is 0-indexed
                        'employee_no' => $employee_identifier ?: 'N/A',
                        'reason' => 'Missing required fields (employee_id/biometric_id, leavetype_id, or balance_days)'
                    ];
                    continue;
                }

                $staff = $this->staff_model->get_by_employee_id($employee_identifier);
                if (empty($staff) || empty($staff['id'])) {
                    $staff_by_biometric = $this->staff_model->get_by_biometric_id($employee_identifier);
                    if (!empty($staff_by_biometric) && isset($staff_by_biometric->id)) {
                        $staff = (array) $staff_by_biometric;
                    }
                }

                if (empty($staff) || empty($staff['id'])) {
                    $skipped_count++;
                    $skipped_records[] = [
                        'row' => $row_index + 2,
                        'employee_no' => $employee_identifier,
                        'reason' => 'Employee not found by employee_id or biometric_id'
                    ];
                    continue;
                }

                // Update yearly allocation in staff_leave_details
                $this->leavetypes_model->update_staff_leave_details((int) $staff['id'], (int) $leave_type_id, $days, true);
                
                // Force reset mode: clear existing monthly history for this staff+leave type,
                // then insert one fresh balance row for the uploaded month/year.
                $this->db->where('staff_id', $staff['id']);
                $this->db->where('leave_type_id', $leave_type_id);
                $this->db->delete('staff_monthly_leave_balance');

                $this->db->insert('staff_monthly_leave_balance', [
                    'staff_id' => $staff['id'],
                    'leave_type_id' => $leave_type_id,
                    'year' => $year,
                    'month' => $month,
                    'opening_balance' => $days,
                    'earned_in_month' => 0,
                    'used_for_lop_adjustment' => 0,
                    'used_for_leave_application' => 0,
                    'other_deductions' => 0,
                    'closing_balance' => $days,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                $processed_count++;
            }

            // Build message with skip details
            $message = '<div class="alert alert-success"><strong>Upload Complete!</strong><br>';
            $message .= 'Records Processed: ' . $processed_count . ' | Skipped: ' . $skipped_count;
            
            if (!empty($skipped_records)) {
                $message .= '<br><br><strong>Skipped Records Details:</strong>';
                $message .= '<table class="table table-sm table-bordered" style="margin-top:10px; background:#fff;">';
                $message .= '<thead><tr><th>Row #</th><th>Employee No</th><th>Reason</th></tr></thead><tbody>';
                foreach ($skipped_records as $skip) {
                    $message .= '<tr>';
                    $message .= '<td>' . $skip['row'] . '</td>';
                    $message .= '<td>' . htmlspecialchars($skip['employee_no']) . '</td>';
                    $message .= '<td>' . htmlspecialchars($skip['reason']) . '</td>';
                    $message .= '</tr>';
                }
                $message .= '</tbody></table>';
            }
            
            $message .= '</div>';
            
            $this->session->set_flashdata('msg', $message);
            redirect("admin/leavetypes/bulk_upload");
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . $this->lang->line('please_upload_a_csv_file') . '</div>');
            redirect("admin/leavetypes/bulk_upload");
        }
    }
    
    public function download_sample()
    {
        $this->load->helper('download');
        $filepath = FCPATH . "backend/import/sample_leave_allotment.csv";

        $sample_content = "employee_id,leavetype_id,balance_days,month,year\n"
            . "EMP001,1,12,3,2026\n"
            . "BIO123,2,8,3,2026\n";
        
        if (!file_exists($filepath)) {
            if (write_file($filepath, $sample_content) === false) {
                show_error('Sample file not found and could not be created at: ' . $filepath);
                return;
            }
        }
        
        $data = file_get_contents($filepath);
        
        if ($data === false || empty($data)) {
            if (write_file($filepath, $sample_content) === false) {
                show_error('Unable to read file or file is empty. Path: ' . $filepath);
                return;
            }
            $data = file_get_contents($filepath);
        }
        
        $name = 'sample_leave_allotment.csv';
        force_download($name, $data);
    }
}
