<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Feemaster extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->sch_setting_detail = $this->setting_model->getSetting();
         $this->load->model('feesessiongroup_model');
    }
  
	public function index()
    {
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');
        $data['title']        = $this->lang->line('fees_master_list');
        $feegroup             = $this->feegroup_model->get();
        $data['feegroupList'] = $feegroup;
        $feetype              = $this->feetype_model->get();
        $data['feetypeList']  = $feetype;
        $feegroup_result       = $this->feesessiongroup_model->getFeesByGroup(null,0);
        $data['feemasterList'] = $feegroup_result;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/feemasterList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_data(){

        $this->form_validation->set_rules('feetype_id', $this->lang->line('fee_type'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|numeric');
        $this->form_validation->set_rules(
            'fee_groups_id', $this->lang->line('fee_group'), array(
                'required',
                array('check_exists', array($this->feesessiongroup_model, 'valid_check_exists')),
            )
        );

        if (isset($_POST['account_type']) && $_POST['account_type'] == 'fix') {
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fix_amount'), 'required|numeric');
            $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');
        } elseif (isset($_POST['account_type']) && ($_POST['account_type'] == 'percentage')) {
            $this->form_validation->set_rules('fine_percentage', $this->lang->line('percentage'), 'required|numeric');
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fix_amount'), 'required|numeric');
            $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');
        }

        if($_POST['account_type'] == 'cumulative'){
            $this->form_validation->set_rules("overdue_day[]", $this->lang->line('total_overdue'), 'required|numeric');
            $this->form_validation->set_rules("overdue_fine[]", $this->lang->line('fine_amount'), 'required|numeric');
        }

        if ($this->form_validation->run() == false) {
             $data = array(
                'fee_groups_id'     => form_error('fee_groups_id'),
                'feetype_id'        => form_error('feetype_id'),
                'amount'            => form_error('amount'),
                'fine_amount'       => form_error('fine_amount'),
                'due_date'          => form_error('due_date'),
                'overdue_day'       => form_error('overdue_day[]'),
                'overdue_fine'      => form_error('overdue_fine[]'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);

        } else {
            
            if($this->input->post('fine_amount')){
                $fine_amount    =   convertCurrencyFormatToBaseAmount($this->input->post('fine_amount'));
            }else{
                $fine_amount    = '';
            }

            if($this->input->post('fine_per_day')){
                $fine_per_day =   1;
            }else{
                $fine_per_day =   0;
            }  
            
            $insert_array = array(
                'fee_groups_id'   => $this->input->post('fee_groups_id'),
                'feetype_id'      => $this->input->post('feetype_id'),
                'amount'          => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
                'due_date'        => $this->customlib->dateFormatToYYYYMMDD($this->input->post('due_date')),
                'session_id'      => $this->setting_model->getCurrentSession(),
                'fine_type'       => $this->input->post('account_type'),
                'fine_percentage' => $this->input->post('fine_percentage'),
                'fine_amount'     => $fine_amount,
                'fine_per_day'    => $fine_per_day,
            );

            $feegroup_result = $this->feesessiongroup_model->add($insert_array);

            if($_POST['account_type'] == 'cumulative'){

            $overdue_day    =   $this->input->post('overdue_day[]');
            $overdue_fine   =   $this->input->post('overdue_fine[]');

            if(count($overdue_day)>0){
            for($i=0;$i<count($overdue_day);$i++){
                $insert_fine_array = array(
                'overdue_day'               => $this->input->post("overdue_day[$i]"),
                'fine_amount'               => $this->input->post("overdue_fine[$i]"),
                'fee_groups_feetype_id'     => $feegroup_result,
                'fee_session_group_id'      => $this->get_fee_session_group_id($this->input->post('fee_groups_id')),
            );
            $this->feesessiongroup_model->add_fine($insert_fine_array);
            }
            }
            }          

            echo json_encode(array('status' => 1, 'msg' => $this->lang->line('success_message'), 'error' => ''));  
        }
    }

    public function bulk_import()
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_add')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');

        $data['title'] = $this->lang->line('bulk_import_fees_master');

        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');

        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/feemaster/feemasterBulkImport', $data);
            $this->load->view('layout/footer', $data);
        } else {
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $handle = fopen($file, "r");
                    $header = fgetcsv($handle, 1000, ",");

                    $has_fee_group_name_header = false;
                    $has_fee_type_name_header = false;
                    $has_amount_header = false;
                    $has_due_date_header = false;
                    $has_fine_type_header = false;
                    $has_percentage_header = false;
                    $has_fix_amount_header = false;

                    foreach ($header as $header_key => $header_value) {
                        if (trim(strtolower($header_value)) === 'fee_group_name') {
                            $has_fee_group_name_header = true;
                        } elseif (trim(strtolower($header_value)) === 'fee_type_name') {
                            $has_fee_type_name_header = true;
                        } elseif (trim(strtolower($header_value)) === 'amount') {
                            $has_amount_header = true;
                        } elseif (trim(strtolower($header_value)) === 'due_date') {
                            $has_due_date_header = true;
                        } elseif (trim(strtolower($header_value)) === 'fine_type') {
                            $has_fine_type_header = true;
                        } elseif (trim(strtolower($header_value)) === 'percentage') {
                            $has_percentage_header = true;
                        } elseif (trim(strtolower($header_value)) === 'fix_amount') {
                            $has_fix_amount_header = true;
                        }
                    }

                    if (!$has_fee_group_name_header || !$has_fee_type_name_header || !$has_amount_header || !$has_due_date_header || !$has_fine_type_header || !$has_percentage_header || !$has_fix_amount_header) {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('csv_file_header_error') . '</div>');
                        redirect('admin/feemaster/bulk_import');
                    }

                    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (count($header) == count($row)) {
                            $feemasters[] = array_combine($header, $row);
                        }
                    }
                    fclose($handle);

                    if (!empty($feemasters)) {
                        $imported_count = 0;
                        $failed_records = [];

                        foreach ($feemasters as $feemaster_data) {
                            $fee_group_name = isset($feemaster_data['fee_group_name']) ? trim($feemaster_data['fee_group_name']) : '';
                            $fee_type_name = isset($feemaster_data['fee_type_name']) ? trim($feemaster_data['fee_type_name']) : '';
                            $amount = isset($feemaster_data['amount']) ? trim($feemaster_data['amount']) : '';
                            $due_date = isset($feemaster_data['due_date']) ? trim($feemaster_data['due_date']) : '';
                            $fine_type = isset($feemaster_data['fine_type']) ? trim(strtolower($feemaster_data['fine_type'])) : 'none';
                            $percentage = isset($feemaster_data['percentage']) ? trim($feemaster_data['percentage']) : 0;
                            $fix_amount = isset($feemaster_data['fix_amount']) ? trim($feemaster_data['fix_amount']) : 0;

                            if (empty($fee_group_name)) {
                                $feemaster_data['reason'] = $this->lang->line('missing_fee_group_name');
                                $failed_records[] = $feemaster_data;
                                continue;
                            }
                            if (empty($fee_type_name)) {
                                $feemaster_data['reason'] = $this->lang->line('missing_fee_type_name');
                                $failed_records[] = $feemaster_data;
                                continue;
                            }
                            if (empty($amount)) {
                                $feemaster_data['reason'] = $this->lang->line('missing_amount');
                                $failed_records[] = $feemaster_data;
                                continue;
                            }
                            if (empty($due_date)) {
                                $feemaster_data['reason'] = $this->lang->line('missing_due_date');
                                $failed_records[] = $feemaster_data;
                                continue;
                            }

                            if ($fine_type !== 'none') {
                                if ($fine_type === 'percentage' && empty($percentage)) {
                                    $feemaster_data['reason'] = $this->lang->line('missing_percentage_for_fine_type');
                                    $failed_records[] = $feemaster_data;
                                    continue;
                                }
                                if ($fine_type === 'fix' && empty($fix_amount)) {
                                    $feemaster_data['reason'] = $this->lang->line('missing_fix_amount_for_fine_type');
                                    $failed_records[] = $feemaster_data;
                                    continue;
                                }
                            }

                            // Check if fee group exists
                            $fee_group = $this->feegroup_model->checkGroupExistsByName($fee_group_name);
                            if (!$fee_group) {
                                $feemaster_data['reason'] = $this->lang->line('fee_group_not_found') . ': ' . $fee_group_name;
                                $failed_records[] = $feemaster_data;
                                continue;
                            }

                            // Check if fee type exists
                            $fee_type = $this->feetype_model->checkFeetypeByName($fee_type_name); 
                            if (!$fee_type) {
                                $feemaster_data['reason'] = $this->lang->line('fee_type_not_found') . ': ' . $fee_type_name;
                                $failed_records[] = $feemaster_data;
                                continue;
                            }

                            // Prepare data for upsert
                            $insert_array = array(
                                'fee_groups_id'   => $fee_group->id,
                                'feetype_id'      => $fee_type->id,
                                'amount'          => convertCurrencyFormatToBaseAmount($amount),
                                'due_date'        => $this->customlib->dateFormatToYYYYMMDD($due_date),
                                'session_id'      => $this->setting_model->getCurrentSession(),
                                'fine_type'       => $fine_type,
                                'fine_percentage' => ($fine_type === 'percentage') ? $percentage : 0,
                                'fine_amount'     => ($fine_type === 'fix') ? convertCurrencyFormatToBaseAmount($fix_amount) : 0,
                                'fine_per_day'    => 0, // Assuming fine_per_day is not part of bulk import for now
                            );

                            // Check if fee master record already exists for upsert
                            $existing_feemaster = $this->feesessiongroup_model->check_exists($fee_group->id, $fee_type->id); 
                            if ($existing_feemaster) {
                                $insert_array['id'] = $existing_feemaster->id;
                                $this->feesessiongroup_model->add($insert_array); 
                            } else {
                                $this->feesessiongroup_model->add($insert_array);
                            }
                            $imported_count++;
                        }

                        $message = '<div class="alert alert-success text-left">' . $imported_count . ' ' . $this->lang->line('records_imported_successfully') . '.</div>';

                        if (!empty($failed_records)) {
                            $message .= '<div class="alert alert-warning text-left">' . count($failed_records) . ' ' . $this->lang->line('records_not_imported') . ':<br>';
                            foreach ($failed_records as $record) {
                                $message .= 'Fee Group: ' . (isset($record['fee_group_name']) ? $record['fee_group_name'] : '') . ', Fee Type: ' . (isset($record['fee_type_name']) ? $record['fee_type_name'] : '') . ', Amount: ' . (isset($record['amount']) ? $record['amount'] : '') . ', Due Date: ' . (isset($record['due_date']) ? $record['due_date'] : '') . ', Fine Type: ' . (isset($record['fine_type']) ? $record['fine_type'] : '') . ', Percentage: ' . (isset($record['percentage']) ? $record['percentage'] : '') . ', Fix Amount: ' . (isset($record['fix_amount']) ? $record['fix_amount'] : '') . ' - Reason: ' . $record['reason'] . '<br>';
                            }
                            $message .= '</div>';
                        }

                        $this->session->set_flashdata('msg', $message);
                        redirect('admin/feemaster/index');
                    } else {
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('no_record_found') . '</div>');
                        redirect('admin/feemaster/bulk_import');
                    }
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('please_upload_csv_file_only') . '</div>');
                    redirect('admin/feemaster/bulk_import');
                }
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $this->lang->line('please_select_file') . '</div>');
                redirect('admin/feemaster/bulk_import');
            }
        }
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

    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_feemaster_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_feemaster_sample_file.csv';
        force_download($name, $data);
    }

    public function get_fee_session_group_id($fee_groups_id){
        return $this->feesessiongroup_model->group_exists($fee_groups_id);
    }

	 public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_delete')) {
            access_denied();
        }
        $data['title'] = $this->lang->line('fees_master_list');
        $this->feegrouptype_model->remove($id);
        $this->feegrouptype_model->remove_comulative_by_fee_groups_feetype_id($id);
        redirect('admin/feemaster/index');
    }

    public function deletegrp($id)
    {
        $data['title'] = $this->lang->line('fees_master_list');
        $this->feesessiongroup_model->remove($id);
        $this->feesessiongroup_model->remove_comulative_by_fee_groups_id($id);
        redirect('admin/feemaster');
    }	
	
	public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');
        $data['id']              = $id;
        $feegroup_details = $this->feegroup_model->get($id);
        log_message('debug', 'Feemaster::assign - feegroup_details type: ' . gettype($feegroup_details));
        if (is_array($feegroup_details) || is_object($feegroup_details)) {
            log_message('debug', 'Feemaster::assign - feegroup_details content: ' . print_r($feegroup_details, true));
        }
        $data['feegroup_name'] = $feegroup_details['name'];
        $cumulative_fine            =   $this->feesessiongroup_model->get_cumulative_fine($id);
        $data['cumulative_fine']    =   $cumulative_fine;
        $feegroup_type              =   $this->feegrouptype_model->get($id);
        $data['feegroup_type']      =   $feegroup_type;
        $feegroup                   =   $this->feegroup_model->get();
        $data['feegroupList']       =   $feegroup;
        $feetype                    =   $this->feetype_model->get();
        $data['feetypeList']        =   $feetype;
        $feegroup_result            =   $this->feesessiongroup_model->getFeesByGroup(null,0);
        $data['feemasterList']      =   $feegroup_result;
      
        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/feemasterEdit', $data);
        $this->load->view('layout/footer', $data);       
    }

    public function edit_data(){
        $this->form_validation->set_rules('feetype_id', $this->lang->line('fee_type'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|numeric');
        $this->form_validation->set_rules(
            'fee_groups_id', $this->lang->line('fee_group'), array(
                'required',
                array('check_exists', array($this->feesessiongroup_model, 'valid_check_exists')),
            )
        );

        if (isset($_POST['account_type']) && $_POST['account_type'] == 'fix') {
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fix_amount'), 'required|numeric');
            $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');
        } elseif (isset($_POST['account_type']) && ($_POST['account_type'] == 'percentage')) {
            $this->form_validation->set_rules('fine_percentage', $this->lang->line('percentage'), 'required|numeric');
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fix_amount'), 'required|numeric');
            $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');
        }

        if($_POST['account_type'] == 'cumulative'){
            $this->form_validation->set_rules("overdue_day[]", $this->lang->line('total_overdue'), 'required|numeric');
            $this->form_validation->set_rules("overdue_fine[]", $this->lang->line('fine_amount'), 'required|numeric'); 
        }

        if ($this->form_validation->run() == false) {
             $data = array(
                'fee_groups_id'     => form_error('fee_groups_id'),
                'feetype_id'        => form_error('feetype_id'),
                'amount'            => form_error('amount'),
                'fine_amount'       => form_error('fine_amount'),
                'due_date'          => form_error('due_date'),
                'overdue_day'       => form_error('overdue_day[]'),
                'overdue_fine'      => form_error('overdue_fine[]'),
            );
            $array = array('status' => 0, 'error' => $data);
            echo json_encode($array);

        } else {            
            if($this->input->post('fine_amount')){
                $fine_amount    =   convertCurrencyFormatToBaseAmount($this->input->post('fine_amount'));
            }else{
                $fine_amount    = '';
            }

            if($this->input->post('fine_per_day')){
                $fine_per_day =   1;
            }else{
                $fine_per_day =   0;
            }            

            $insert_array = array(
                'id'              => $this->input->post('id'),
                'feetype_id'      => $this->input->post('feetype_id'),
                'due_date'        => $this->customlib->dateFormatToYYYYMMDD($this->input->post('due_date')),
                'amount'          => convertCurrencyFormatToBaseAmount($this->input->post('amount')),
                'fine_type'       => $this->input->post('account_type'),
                'fine_percentage' => $this->input->post('fine_percentage'),
                'fine_amount'     => $fine_amount,
                'fine_per_day'    => $fine_per_day,
            );

            $feegroup_result = $this->feegrouptype_model->add($insert_array);

            if($_POST['account_type'] == 'cumulative'){

            $cumulative_id      =   $this->input->post('cumulative_id[]');
            $overdue_day        =   $this->input->post('overdue_day[]');
            $overdue_fine       =   $this->input->post('overdue_fine[]');

            if(count($overdue_day)>0){
            for($i=0;$i<count($overdue_day);$i++){
                $insert_fine_array = array(
                'id'                        => $this->input->post("cumulative_id[$i]"),
                'overdue_day'               => $this->input->post("overdue_day[$i]"),
                'fine_amount'               => $this->input->post("overdue_fine[$i]"),
                'fee_groups_feetype_id'     => $feegroup_result,
                'fee_session_group_id'      => $this->get_fee_session_group_id($this->input->post('fee_groups_id')),
            );
            $this->feesessiongroup_model->add_fine($insert_fine_array);

            }
            }
            }else{
                //if on edit fine type is not cumulative then we will remove all the cumulative fine recored from the table 
                $fee_groups_feetype_id=$this->input->post('id');
                $this->feesessiongroup_model->remove_cumulativeby_grouptypid($fee_groups_feetype_id);
            }          
            echo json_encode(array('status' => 1, 'msg' => $this->lang->line('success_message'), 'error' => ''));  
        }
    }

    public function remove_row(){
        $cumulative_id=$_POST['cumulative_id'];
        $this->feesessiongroup_model->remove_cumulative($cumulative_id);
        echo json_encode(array('status' => 1, 'msg' => $this->lang->line('success_message'), 'error' => ''));
    }

    public function getFeeGroupDetails()
    {
        $feegroup_id = $this->input->post('feegroup_id');
        $feegroup_result = $this->feesessiongroup_model->getFeesByGroup($feegroup_id);
        echo json_encode($feegroup_result);
    }

    public function assign()
    {
        if (!$this->rbac->hasPrivilege('fees_group_assign', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');
        
        $data['feegroups_for_dropdown']    = $this->feegroup_model->get();
        $data['title']           = $this->lang->line('student_fees');
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting']     = $this->sch_setting_detail;
        $genderList            = $this->customlib->getGender();
        $data['genderList']    = $genderList;
        $RTEstatusList         = $this->customlib->getRteStatus();
        $data['RTEstatusList'] = $RTEstatusList;

        $category             = $this->category_model->get();
        $data['categorylist'] = $category;
        $data['feegroup_name'] = "";
        
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $feegroup_id = $this->input->post('feegroup_id');
            $data['category_id'] = $this->input->post('category_id');
            $data['gender']      = $this->input->post('gender');
            $data['rte_status']  = $this->input->post('rte');
            $data['class_id']    = $this->input->post('class_id');
            $data['section_id']  = $this->input->post('section_id');

            if($feegroup_id){
                $feegroup_details = $this->feegroup_model->get($feegroup_id);
                $data['feegroup_name'] = $feegroup_details['name'];
                $data['selected_feegroup_details'] = $this->feesessiongroup_model->getFeesByGroup($feegroup_id);
            }

            $resultlist         = $this->studentfeemaster_model->searchAssignFeeByClassSection($data['class_id'], $data['section_id'], $feegroup_id, $data['category_id'], $data['gender'], $data['rte_status']);
            $data['resultlist'] = $resultlist;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/assign', $data);
        $this->load->view('layout/footer', $data);
    }    

}
