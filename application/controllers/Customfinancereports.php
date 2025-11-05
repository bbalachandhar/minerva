<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Customfinancereports extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->config('ci-blog');
        $this->balance_group = $this->config->item('ci_balance_group');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model("module_model");
        $this->load->model("customstudentfeemaster_model");
        $this->load->model("student_model");
        $this->load->model("class_model");
        $this->load->model("feediscount_model"); // Load the discount model
    }

    public function custombalancefeesreport()
    {
        if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/custombalancefeesreport');
        $data['title']           = 'Custom Balance Fees Report';
        $data['payment_type']    = $this->customlib->getPaymenttype();
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        // Get all discount types for table headers
        $data['discount_list'] = $this->feediscount_model->get();

        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['student_due_fee'] = array();
            $data['resultarray']     = array();
        } else {
            $student_Array = array();
            $search_type   = $this->input->post('search_type');
            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');

            if (isset($class_id)) {
                $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id);
            } else {
                $studentlist = $this->student_model->getStudents();
            }

            $student_Array = array();
            if (!empty($studentlist)) {
                foreach ($studentlist as $key => $eachstudent) {
                    $obj                = new stdClass();
                    $obj->name          = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
                    $obj->class         = $eachstudent['class'];
                    $obj->section       = $eachstudent['section'];
                    $obj->admission_no  = $eachstudent['admission_no'];
                    $obj->advance_balance = $eachstudent['advance_balance'] ?? 0;
                    $student_session_id = $eachstudent['student_session_id'];

                    // Get all fees and discounts for the student
                    $fees_data = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id);
                    $obj->tuition_demand = $fees_data->tuition_demand;
                    $obj->tuition_paid = $fees_data->tuition_paid;
                    $obj->other_demand = $fees_data->other_demand;
                    $obj->other_paid = $fees_data->other_paid;
                    $obj->hostel_demand = $fees_data->hostel_demand;
                    $obj->hostel_paid = $fees_data->hostel_paid;
                    $obj->transport_demand = $fees_data->transport_demand;
                    $obj->transport_paid = $fees_data->transport_paid;
                    $obj->advance_paid = $fees_data->advance_paid;
                    $student_total_fees = $fees_data->fees;
                    $obj->applied_discounts = $this->feediscount_model->getStudentFeesDiscount($student_session_id);

                    // Get the previous session balance (CF-Demand)
                    $previous_session_balance_data = $this->customstudentfeemaster_model->getPreviousSessionBalance($student_session_id);
                    $obj->last_yr_cf = !empty($previous_session_balance_data) ? $previous_session_balance_data->amount : 0;

                    // Get the amount paid against the previous session balance (CF-Paid)
                    $obj->cf_paid = $this->customstudentfeemaster_model->getPreviousSessionPaid($student_session_id);

                    // Calculate CF-Balance
                    $obj->cf_balance = $obj->last_yr_cf - $obj->cf_paid;

                    // Calculate base fee totals
                    $totalfee = 0;
                    $total_paid_sum = 0;
                    $total_fine_sum = 0;
                    $total_discount_sum = 0;

                    if (!empty($fees_data->fees)) {
                        foreach ($fees_data->fees as $fee_item) {
                            $totalfee += $fee_item->amount;
                            $total_paid_sum += $fee_item->total_paid;
                            $total_fine_sum += $fee_item->total_fine;
                            $total_discount_sum += $fee_item->total_discount;
                        }
                    }

                    $obj->totalfee = $totalfee;
                    $obj->deposit  = $total_paid_sum;
                    $obj->fine     = $total_fine_sum;
                    $obj->discount = $total_discount_sum;
                    $obj->balance  = $totalfee - ($total_paid_sum + $total_discount_sum);

                    // Filter based on search type
                    $include_student = false;
                    if ($search_type == 'all') {
                        $include_student = true;
                    } elseif ($search_type == 'balance') {
                        if ($obj->balance > 0) {
                            $include_student = true;
                        }
                    } elseif ($search_type == 'paid') {
                        if ($obj->balance <= 0) {
                            $include_student = true;
                        }
                    }

                    if($include_student){
                        $student_Array[] = $obj;
                    }
                }
            }

            $data['student_due_fee'] = $student_Array;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/customstudentAcademicReport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function dtcustombalancefeesreport()
    {
        if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {
            access_denied();
        }

        $student_Array = array();
        $search_type   = $this->input->post('search_type');
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        if (isset($class_id)) {
            $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id);
        } else {
            $studentlist = $this->student_model->getStudents();
        }

        $student_Array = array();
        if (!empty($studentlist)) {
            foreach ($studentlist as $key => $eachstudent) {
                $obj                = new stdClass();
                $obj->name          = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
                $obj->class         = $eachstudent['class'];
                $obj->section       = $eachstudent['section'];
                $obj->admission_no  = $eachstudent['admission_no'];
                $obj->advance_balance = $eachstudent['advance_balance'] ?? 0;
                $student_session_id = $eachstudent['student_session_id'];

                // Get all fees and discounts for the student
                $fees_data = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id);
                $obj->tuition_demand = $fees_data->tuition_demand;
                $obj->tuition_paid = $fees_data->tuition_paid;
                $obj->other_demand = $fees_data->other_demand;
                $obj->other_paid = $fees_data->other_paid;
                $obj->hostel_demand = $fees_data->hostel_demand;
                $obj->hostel_paid = $fees_data->hostel_paid;
                $obj->transport_demand = $fees_data->transport_demand;
                $obj->transport_paid = $fees_data->transport_paid;
                $student_total_fees = $fees_data->fees;
                $obj->applied_discounts = $this->feediscount_model->getStudentFeesDiscount($student_session_id);

                // Get the previous session balance
                $balance_record = $this->customstudentfeemaster_model->getBalanceMasterRecord($this->balance_group, '(' . $student_session_id . ')');
                $obj->last_yr_cf = !empty($balance_record) ? $balance_record[0]->amount : 0;

                // Calculate base fee totals
                $totalfee = 0;
                $total_paid_sum = 0;
                $total_fine_sum = 0;
                $total_discount_sum = 0;

                if (!empty($fees_data->fees)) {
                    foreach ($fees_data->fees as $fee_item) {
                        $totalfee += $fee_item->amount;
                        $total_paid_sum += $fee_item->total_paid;
                        $total_fine_sum += $fee_item->total_fine;
                        $total_discount_sum += $fee_item->total_discount;
                    }
                }

                $obj->totalfee = $totalfee;
                $obj->deposit  = $total_paid_sum;
                $obj->fine     = $total_fine_sum;
                $obj->discount = $total_discount_sum;
                $obj->balance  = $totalfee - ($total_paid_sum + $total_discount_sum);

                // Filter based on search type
                $include_student = false;
                if ($search_type == 'all') {
                    $include_student = true;
                } elseif ($search_type == 'balance') {
                    if ($obj->balance > 0) {
                        $include_student = true;
                    }
                } elseif ($search_type == 'paid') {
                    if ($obj->balance <= 0) {
                        $include_student = true;
                    }
                }

                if($include_student){
                    $student_Array[] = $obj;
                }
            }
        }

        $json_data = array(
            "draw"            => intval($this->input->post('draw')),
            "recordsTotal"    => intval(count($student_Array)),
            "recordsFiltered" => intval(count($student_Array)),
            "data"            => $student_Array   // total data array
            );

        echo json_encode($json_data);  // send data as json format
    }
}
