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
        $this->load->model("studentfeemaster_model");
        $this->load->model("student_model");
        $this->load->model("class_model");
        $this->load->model("feediscount_model"); // Load the discount model
        $this->load->model('Department_model');
        $this->current_session = $this->setting_model->getCurrentSession();
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
        $data['department_id_selected'] = $this->input->post('department_id'); // Keep track of selected department

        if (!empty($data['department_id_selected'])) {
            $data['classlist'] = $this->class_model->getClassesByDepartment($data['department_id_selected']);
        } else {
            $data['classlist'] = $this->class_model->get(); // All classes if no department selected
        }
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['department_list'] = $this->Department_model->getDepartmentType(); // Load department list

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
            $department_id = $this->input->post('department_id');
            $discount_type_filter = trim($this->input->post('discount_type_filter'));
            
            if (!empty($class_id)) {
                $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id, $this->current_session, $department_id);
            } else {
                $studentlist = $this->student_model->searchByClassSectionWithSession(null, null, $this->current_session, $department_id);
            }
            
            $student_Array    = array();
            $fee_type_columns = array(); // collect all unique fee types across all students

            if (!empty($studentlist)) {
                foreach ($studentlist as $key => $eachstudent) {
                    $obj                = new stdClass();
                    $obj->name          = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
                    $obj->class         = $eachstudent['class'];
                    $obj->section       = $eachstudent['section'];
                    $obj->category      = $eachstudent['category'];
                    $obj->admission_no  = $eachstudent['admission_no'];
                    $obj->advance_balance = $eachstudent['advance_balance'] ?? 0;
                    $student_session_id = $eachstudent['student_session_id'];

                    $fees_data = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id);

                    // Dynamic fee types from feetype table
                    $obj->fee_types        = $fees_data->fee_types; // [feetype_id => [id,name,demand,paid]]
                    $obj->transport_demand = $fees_data->transport_demand;
                    $obj->transport_paid   = $fees_data->transport_paid;
                    $obj->transport_balance = $fees_data->transport_demand - $fees_data->transport_paid;

                    // Collect unique fee type columns
                    foreach ($fees_data->fee_types as $tid => $ft) {
                        if (!isset($fee_type_columns[$tid])) {
                            $fee_type_columns[$tid] = $ft['name'];
                        }
                    }

                    $advance_balances  = $this->studentfeemaster_model->get_advance_balance($student_session_id);
                    $obj->advance_paid     = $advance_balances['paid_advance_balance'];
                    $obj->advance_discount = $advance_balances['discount_advance_balance'];
                    $obj->applied_discounts = $this->feediscount_model->getStudentFeesDiscount($student_session_id);

                    $previous_session_balance_data = $this->customstudentfeemaster_model->getPreviousSessionBalance($student_session_id);
                    $obj->last_yr_cf  = !empty($previous_session_balance_data) ? $previous_session_balance_data->amount : 0;
                    $obj->cf_paid     = $this->customstudentfeemaster_model->getPreviousSessionPaid($student_session_id);
                    $obj->cf_balance  = $obj->last_yr_cf - $obj->cf_paid;

                    // Totals
                    $total_ft_demand = 0;
                    $total_ft_paid   = 0;
                    foreach ($obj->fee_types as $ft) {
                        $total_ft_demand += $ft['demand'];
                        $total_ft_paid   += $ft['paid'];
                    }
                    $totalfee       = $total_ft_demand + $fees_data->transport_demand;
                    $total_paid_sum = $total_ft_paid  + $fees_data->transport_paid;

                    $total_fine_sum = $total_discount_sum = 0;
                    if (!empty($fees_data->fees)) {
                        foreach ($fees_data->fees as $fee_item) {
                            $total_fine_sum     += $fee_item->total_fine;
                            $total_discount_sum += $fee_item->total_discount;
                        }
                    }

                    $obj->totalfee = $totalfee;
                    $obj->deposit  = $total_paid_sum;
                    $obj->fine     = $total_fine_sum;
                    $obj->balance  = max(0, ($totalfee - $total_paid_sum) + $obj->cf_balance);
                    $obj->net_balance = max(0, $obj->balance - ($obj->advance_paid + $obj->advance_discount));

                    $total_student_discount_dynamic = 0;
                    if (!empty($obj->applied_discounts)) {
                        foreach ($obj->applied_discounts as $student_discount) {
                            $discount_amount = isset($student_discount['custom_amount']) && $student_discount['custom_amount'] != null
                                ? $student_discount['custom_amount'] : $student_discount['amount'];
                            $obj->{"discount_" . $student_discount['fees_discount_id']} = $discount_amount;
                            $total_student_discount_dynamic += $discount_amount;
                        }
                    }
                    $obj->discount = $total_student_discount_dynamic;

                    $has_discount_type = empty($discount_type_filter);
                    if (!$has_discount_type && !empty($obj->applied_discounts)) {
                        foreach ($obj->applied_discounts as $student_discount) {
                            if ((int)$student_discount['fees_discount_id'] == (int)$discount_type_filter) {
                                $has_discount_type = true;
                                break;
                            }
                        }
                    }

                    $include_student = ($search_type == 'all')
                        || ($search_type == 'balance' && $obj->balance > 0)
                        || ($search_type == 'paid'    && $obj->balance <= 0);

                    if ($include_student && $has_discount_type) {
                        $student_Array[] = $obj;
                    }
                }
            }

            $data['student_due_fee']    = $student_Array;
            $data['fee_type_columns']   = $fee_type_columns; // [feetype_id => name]
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/customstudentAcademicReport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function balancereportbetweendates()
    {
        if (!$this->rbac->hasPrivilege('balance_report_between_dates', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/finance');
        $this->session->set_userdata('subsub_menu', 'Reports/finance/balancereportbetweendates');
        $data['title']                  = 'Balance Report Between Dates';
        $data['payment_type']           = $this->customlib->getPaymenttype();
        $data['department_id_selected'] = $this->input->post('department_id');

        if (!empty($data['department_id_selected'])) {
            $data['classlist'] = $this->class_model->getClassesByDepartment($data['department_id_selected']);
        } else {
            $data['classlist'] = $this->class_model->get();
        }
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['department_list'] = $this->Department_model->getDepartmentType();
        $data['discount_list']   = $this->feediscount_model->get();

        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('start_date', 'Start Date', 'trim|required|xss_clean');
        $this->form_validation->set_rules('end_date', 'End Date', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['student_due_fee'] = array();
        } else {
            $search_type          = $this->input->post('search_type');
            $class_id             = $this->input->post('class_id');
            $section_id           = $this->input->post('section_id');
            $department_id        = $this->input->post('department_id');
            $discount_type_filter = trim($this->input->post('discount_type_filter'));
            $start_date           = $this->input->post('start_date');
            $end_date             = $this->input->post('end_date');

            $data['start_date'] = $start_date;
            $data['end_date']   = $end_date;

            if (!empty($class_id)) {
                $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id, $this->current_session, $department_id);
            } else {
                $studentlist = $this->student_model->searchByClassSectionWithSession(null, null, $this->current_session, $department_id);
            }

            $student_Array    = array();
            $fee_type_columns = array();

            if (!empty($studentlist)) {
                $all_session_ids = array_column($studentlist, 'student_session_id');

                $fee_summary     = $this->customstudentfeemaster_model->getStudentFeesSummaryByEndDateBatch($all_session_ids, $end_date);
                $discounts_batch = $this->feediscount_model->getStudentFeesDiscountBatch($all_session_ids);

                $empty_d = [
                    'fee_types' => [], 'transport_demand' => 0, 'transport_paid' => 0,
                    'advance_paid' => 0, 'advance_discount' => 0,
                    'last_yr_cf' => 0, 'cf_paid' => 0,
                    'total_fine' => 0, 'total_discount' => 0,
                ];

                foreach ($studentlist as $eachstudent) {
                    $ssid = $eachstudent['student_session_id'];
                    $d    = isset($fee_summary[$ssid]) ? $fee_summary[$ssid] : $empty_d;

                    $obj               = new stdClass();
                    $obj->name         = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
                    $obj->class        = $eachstudent['class'];
                    $obj->section      = $eachstudent['section'];
                    $obj->category     = $eachstudent['category'];
                    $obj->admission_no = $eachstudent['admission_no'];

                    $obj->fee_types         = $d['fee_types'];
                    $obj->transport_demand  = $d['transport_demand'];
                    $obj->transport_paid    = $d['transport_paid'];
                    $obj->transport_balance = $d['transport_demand'] - $d['transport_paid'];
                    $obj->advance_paid      = $d['advance_paid'];
                    $obj->advance_discount  = $d['advance_discount'];
                    $obj->last_yr_cf        = $d['last_yr_cf'];
                    $obj->cf_paid           = $d['cf_paid'];
                    $obj->cf_balance        = $d['last_yr_cf'] - $d['cf_paid'];
                    $obj->fine              = $d['total_fine'];

                    // Collect unique fee type columns
                    foreach ($d['fee_types'] as $tid => $ft) {
                        if (!isset($fee_type_columns[$tid])) $fee_type_columns[$tid] = $ft['name'];
                    }

                    $total_ft_demand = $total_ft_paid = 0;
                    foreach ($d['fee_types'] as $ft) {
                        $total_ft_demand += $ft['demand'];
                        $total_ft_paid   += $ft['paid'];
                    }
                    $totalfee         = $total_ft_demand + $d['transport_demand'];
                    $total_paid_sum   = $total_ft_paid   + $d['transport_paid'];
                    $obj->totalfee    = $totalfee;
                    $obj->deposit     = $total_paid_sum;
                    $obj->balance     = max(0, $totalfee - $total_paid_sum + $obj->cf_balance);
                    $obj->net_balance = max(0, $obj->balance - ($d['advance_paid'] + $d['advance_discount']));

                    $obj->applied_discounts = isset($discounts_batch[$ssid]) ? $discounts_batch[$ssid] : [];
                    $total_student_discount = 0;
                    foreach ($obj->applied_discounts as $student_discount) {
                        $discount_amount = (isset($student_discount['custom_amount']) && $student_discount['custom_amount'] !== null)
                            ? $student_discount['custom_amount']
                            : $student_discount['amount'];
                        $obj->{"discount_" . $student_discount['fees_discount_id']} = $discount_amount;
                        $total_student_discount += $discount_amount;
                    }
                    $obj->discount = $total_student_discount;

                    // Discount type filter
                    $has_discount_type = empty($discount_type_filter);
                    if (!$has_discount_type && !empty($obj->applied_discounts)) {
                        foreach ($obj->applied_discounts as $student_discount) {
                            if ((int)$student_discount['fees_discount_id'] == (int)$discount_type_filter) {
                                $has_discount_type = true;
                                break;
                            }
                        }
                    }

                    // Search type filter
                    $include = false;
                    if ($search_type == 'all') {
                        $include = true;
                    } elseif ($search_type == 'balance' && $obj->balance > 0) {
                        $include = true;
                    } elseif ($search_type == 'paid' && $obj->balance <= 0) {
                        $include = true;
                    }

                    if ($include && $has_discount_type) {
                        $student_Array[] = $obj;
                    }
                }
            }

            $data['student_due_fee']  = $student_Array;
            $data['fee_type_columns'] = $fee_type_columns;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('financereports/balance_report_between_dates', $data);
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
        $discount_type_filter = trim($this->input->post('discount_type_filter'));

        if (!empty($class_id)) {
            $studentlist = $this->student_model->searchByClassSectionWithSession($class_id, $section_id);
        } else {
            $studentlist = $this->student_model->searchByClassSectionWithSession(null, null, $this->current_session);
        }

        $student_Array = array();
        if (!empty($studentlist)) {
            foreach ($studentlist as $key => $eachstudent) {
                $obj                = new stdClass();
                $obj->name          = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
                $obj->class         = $eachstudent['class'];
                $obj->section       = $eachstudent['section'];
                $obj->category      = $eachstudent['category'];
                $obj->admission_no  = $eachstudent['admission_no'];
                $obj->advance_balance = $eachstudent['advance_balance'] ?? 0;
                $student_session_id = $eachstudent['student_session_id'];

                // Get all fees and discounts for the student
                $fees_data = $this->customstudentfeemaster_model->getTransStudentFees($student_session_id);

                $obj->fee_types         = $fees_data->fee_types;
                $obj->transport_demand  = $fees_data->transport_demand;
                $obj->transport_paid    = $fees_data->transport_paid;
                $obj->transport_balance = $fees_data->transport_demand - $fees_data->transport_paid;
                $obj->applied_discounts = $this->feediscount_model->getStudentFeesDiscount($student_session_id);

                $balance_record  = $this->customstudentfeemaster_model->getBalanceMasterRecord($this->balance_group, '(' . $student_session_id . ')');
                $obj->last_yr_cf = !empty($balance_record) ? $balance_record[0]->amount : 0;

                $total_ft_demand = $total_ft_paid = $totalfee = 0;
                foreach ($obj->fee_types as $ft) {
                    $total_ft_demand += $ft['demand'];
                    $total_ft_paid   += $ft['paid'];
                }
                $totalfee       = $total_ft_demand + $fees_data->transport_demand;
                $total_paid_sum = $total_ft_paid   + $fees_data->transport_paid;

                $total_fine_sum = $total_discount_sum = 0;
                if (!empty($fees_data->fees)) {
                    foreach ($fees_data->fees as $fee_item) {
                        $total_fine_sum     += $fee_item->total_fine;
                        $total_discount_sum += $fee_item->total_discount;
                    }
                }

                $obj->totalfee = $totalfee;
                $obj->deposit  = $total_paid_sum;
                $obj->fine     = $total_fine_sum;
                $obj->discount = $total_discount_sum;
                $obj->balance  = max(0, $totalfee - $total_paid_sum);

                $has_discount_type = empty($discount_type_filter);
                if (!$has_discount_type && !empty($obj->applied_discounts)) {
                    foreach ($obj->applied_discounts as $student_discount) {
                        if ((int)$student_discount['fees_discount_id'] == (int)$discount_type_filter) {
                            $has_discount_type = true;
                            break;
                        }
                    }
                }

                $include_student = ($search_type == 'all')
                    || ($search_type == 'balance' && $obj->balance > 0)
                    || ($search_type == 'paid'    && $obj->balance <= 0);

                if ($include_student && $has_discount_type) {
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

    public function get_classes_by_department()
    {
        $this->output->set_content_type('application/json');
        $department_id = $this->input->post('department_id');

        $classes = array();
        if (!empty($department_id)) {
            $classes = $this->class_model->getClassesByDepartment($department_id);
        } else {
            // If department_id is empty, return all classes (as per "Select All" logic)
            $classes = $this->class_model->get(); // Assuming get() without parameter returns all classes
        }
        
        echo json_encode($classes);
    }
}
