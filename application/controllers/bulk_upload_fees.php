            public function bulk_upload_fees()
            {
                if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
                    access_denied();
                }
        
                $this->session->set_userdata('top_menu', $this->lang->line('fees_collection'));
                $this->session->set_userdata('sub_menu', 'studentfee/bulk_upload_fees');
                $data['title'] = $this->lang->line('bulk_upload_fees');
        
                if ($this->input->server('REQUEST_METHOD') == "POST") {
                    $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_csv_upload');
        
                    if ($this->form_validation->run() == false) {
                        $this->load->view('layout/header', $data);
                        $this->load->view('studentfee/bulk_upload_fees', $data);
                        $this->load->view('layout/footer', $data);
                    } else {
                        // File uploaded and validated, now process it
                        $file_path = $_FILES['file']['tmp_name'];
                        $this->load->library('csvreader');
                        $result = $this->csvreader->parse_file($file_path);
        
                        if (!empty($result)) {
                            $processed_records = [];
                            $error_messages = [];
                            $fees_to_deposit = [];

                            foreach ($result as $row_num => $row) {
                                $current_row_errors = [];
                                $admission_no = $row['admission_no'] ?? '';
                                $total_amount_paid = $row['total_amount_paid'] ?? '';
                                $old_bill_number = $row['old_bill_number'] ?? '';
                                $old_bill_date = $row['old_bill_date'] ?? '';
                                $payment_mode = $row['payment_mode'] ?? '';
                                $description = $row['description'] ?? '';

                                // Basic validation
                                if (empty($admission_no) || empty($total_amount_paid) || empty($old_bill_number) || empty($old_bill_date) || empty($payment_mode)) {
                                    $current_row_errors[] = "Missing required fields (admission_no, total_amount_paid, old_bill_number, old_bill_date, payment_mode).";
                                }

                                if (!is_numeric($total_amount_paid) || $total_amount_paid < 0) {
                                    $current_row_errors[] = "Invalid amount paid. Must be a non-negative number.";
                                }

                                // Validate date format
                                if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $old_bill_date)) {
                                    $current_row_errors[] = "Invalid bill date format. Expected YYYY-MM-DD.";
                                }

                                if (!empty($current_row_errors)) {
                                    $error_messages[] = "Row " . ($row_num + 2) . ": " . implode(", ", $current_row_errors);
                                    continue;
                                }

                                // Find student_session_id based on admission_no
                                $student = $this->student_model->findByAdmission($admission_no);
                                if (!$student) {
                                    $error_messages[] = "Row " . ($row_num + 2) . ": Student with Admission No. " . $admission_no . " not found in current session.";
                                    continue;
                                }
                                $student_session_id = $student->student_session_id;
                                $student_id = $student->id;

                                // Check for duplicate bill number
                                if ($this->studentfeemaster_model->checkDuplicateBillNumber($student_session_id, $old_bill_number)) {
                                    $error_messages[] = "Row " . ($row_num + 2) . ": Duplicate Bill Number '" . $old_bill_number . "' found for student " . $admission_no . ". Skipping this entry.";
                                    continue;
                                }

                                // Fetch outstanding fees for the student
                                $outstanding_fees = $this->studentfeemaster_model->getOutstandingFeesByStudentSessionId($student_session_id);

                                $remaining_payment = (float) $total_amount_paid;

                                // Get student's current advance balance
                                $student_advance_balance = isset($student->advance_balance) ? (float) $student->advance_balance : 0;

                                // Define fee type priority
                                $priority_order = ['carry_forwarded', 'tuition', 'other', 'hostel', 'other_fees'];

                                // --- Phase 1: Consume existing advance_balance first ---
                                if ($student_advance_balance > 0) {
                                    foreach ($priority_order as $priority_fee_type) {
                                        if ($student_advance_balance <= 0) {
                                            break;
                                        }
                                        if (isset($outstanding_fees[$priority_fee_type]) && !empty($outstanding_fees[$priority_fee_type])) {
                                            foreach ($outstanding_fees[$priority_fee_type] as $fee_item) {
                                                if ($student_advance_balance <= 0) {
                                                    break;
                                                }
                                                $outstanding_amount = (float) $fee_item->outstanding_amount;
                                                $amount_from_advance = min($student_advance_balance, $outstanding_amount);

                                                if ($amount_from_advance > 0) {
                                                    $amount_detail = [
                                                        'amount'                 => $amount_from_advance,
                                                        'date'                   => date('Y-m-d'), // Date when advance is consumed
                                                        'payment_mode'           => 'Advance', // Special payment mode for advance consumption
                                                        'description'            => 'Payment from advance balance',
                                                        'collected_by'           => $this->customlib->getAdminSessionUserName(),
                                                        'received_by'            => $this->customlib->getStaffID(),
                                                    ];

                                                    $fees_to_deposit[] = [
                                                        'student_fees_master_id' => $fee_item->student_fees_master_id,
                                                        'fee_groups_feetype_id'  => $fee_item->fee_groups_feetype_id,
                                                        'amount_detail'          => $amount_detail,
                                                        'fee_category'           => 'fees',
                                                        'date'                   => date('Y-m-d'),
                                                        'old_bill_number'        => null,
                                                        'old_bill_date'          => null,
                                                    ];
                                                    $student_advance_balance -= $amount_from_advance;
                                                }
                                            }
                                        }
                                    }
                                    // Update student's advance_balance in DB after consumption
                                    $this->db->set('advance_balance', $student_advance_balance);
                                    $this->db->where('id', $student_id);
                                    $this->db->update('students');
                                }

                                // --- Phase 2: Apply current payment from CSV ---
                                foreach ($priority_order as $priority_fee_type) {
                                    if ($remaining_payment <= 0) {
                                        break;
                                    }
                                    if (isset($outstanding_fees[$priority_fee_type]) && !empty($outstanding_fees[$priority_fee_type])) {
                                        foreach ($outstanding_fees[$priority_fee_type] as $fee_item) {
                                            if ($remaining_payment <= 0) {
                                                break;
                                            }
                                            $outstanding_amount = (float) $fee_item->outstanding_amount;
                                            $amount_to_pay = min($remaining_payment, $outstanding_amount);

                                            if ($amount_to_pay > 0) {
                                                $amount_detail = [
                                                    'amount'                 => $amount_to_pay,
                                                    'date'                   => date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)),
                                                    'payment_mode'           => $payment_mode,
                                                    'description'            => $description,
                                                    'collected_by'           => $this->customlib->getAdminSessionUserName(),
                                                    'received_by'            => $this->customlib->getStaffID(),
                                                ];

                                                $fees_to_deposit[] = [
                                                    'student_fees_master_id' => $fee_item->student_fees_master_id,
                                                    'fee_groups_feetype_id'  => $fee_item->fee_groups_feetype_id,
                                                    'amount_detail'          => $amount_detail,
                                                    'old_bill_number'        => $old_bill_number,
                                                    'old_bill_date'          => date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)),
                                                    'fee_category'           => 'fees',
                                                    'date'                   => date('Y-m-d', $this->customlib->datetostrtotime($old_bill_date)),
                                                ];
                                                $remaining_payment -= $amount_to_pay;
                                            }
                                        }
                                    }
                                }

                                // Handle any remaining payment as advance
                                if ($remaining_payment > 0) {
                                    $this->db->set('advance_balance', 'advance_balance + ' . $remaining_payment, FALSE);
                                    $this->db->where('id', $student_id);
                                    $this->db->update('students');
                                }
                            }

                            if (!empty($fees_to_deposit)) {
                                foreach($fees_to_deposit as $fee_to_deposit){
                                    $this->studentfeemaster_model->fee_deposit($fee_to_deposit, null, [], $fee_to_deposit['date']);
                                }
                            }

                            if (empty($error_messages)) {
                                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $this->lang->line('fees_uploaded_successfully') . '</div>');
                                redirect('studentfee/bulk_upload_fees');
                            } else {
                                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Some records failed to process. Please check the errors below.</div>');
                                $data['error_messages'] = $error_messages;
                                $this->load->view('layout/header', $data);
                                $this->load->view('studentfee/bulk_upload_fees', $data);
                                $this->load->view('layout/footer', $data);
                            }

                        } else {
                            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('error_processing_file') . '</div>');
                            redirect('studentfee/bulk_upload_fees');
                        }
                    }
                } else {
                    $this->load->view('layout/header', $data);
                    $this->load->view('studentfee/bulk_upload_fees', $data);
                    $this->load->view('layout/footer', $data);
                }
            }