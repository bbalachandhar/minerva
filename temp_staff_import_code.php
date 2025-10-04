<?php

                    if (!empty($result)) {
                        // Skip the header row if it's included in the result
                        $header_skipped = false;
                        foreach ($result as $r_key => $r_value) {
                            if (!$header_skipped) {
                                $header_skipped = true;
                                continue; // Skip the first row (header)
                            }

                            $staff_data = [];
                            foreach ($data['field'] as $csv_header => $db_field) {
                                $staff_data[$db_field] = isset($r_value[$csv_header]) ? $this->encoding_lib->toUTF8($r_value[$csv_header]) : '';
                            }

                            // Date parsing for dob and date_of_joining
                            if (!empty($staff_data['dob'])) {
                                $parsed_date = strtotime($staff_data['dob']);
                                $staff_data['dob'] = ($parsed_date !== false) ? date('Y-m-d', $parsed_date) : null;
                            } else {
                                $staff_data['dob'] = null;
                            }

                            if (!empty($staff_data['date_of_joining'])) {
                                $parsed_date = strtotime($staff_data['date_of_joining']);
                                $staff_data['date_of_joining'] = ($parsed_date !== false) ? date('Y-m-d', $parsed_date) : null;
                            } else {
                                $staff_data['date_of_joining'] = null;
                            }

                            // Handle designation mapping
                            $csv_designation_name = strtolower(trim($staff_data['designation']));
                            if (isset($designation_map[$csv_designation_name])) {
                                $staff_data['designation'] = $designation_map[$csv_designation_name];
                            } else {
                                continue; // Skip to the next row in the CSV
                            }

                            // Handle department mapping
                            $csv_department_name = strtolower(trim($staff_data['department']));
                            if (isset($department_map[$csv_department_name])) {
                                $staff_data['department'] = $department_map[$csv_department_name];
                            } else {
                                continue; // Skip to the next row in the CSV
                            }

                            $staff_data['is_active'] = 1;
                            $password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
                            $staff_data['password'] = $this->enc_lib->passHashEnc($password);

                            $existing_staff_id = $this->staff_model->getStaffIdByEmployeeIdOrEmail($staff_data['employee_id'], $staff_data['email']);

                            if ($existing_staff_id) {
                                $staff_data['id'] = $existing_staff_id; // Add ID for update
                                $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => $existing_staff_id); // Update staff_id in role_array
                                $insert_id = $this->staff_model->batchInsert($staff_data, $role_array); // batchInsert calls add, which handles update if ID is present
                                $staff_id = $existing_staff_id; // Use existing ID for barcode/email
                                $updated_count++; // Keep track of updated records
                            } else {
                                $role_array = array('role_id' => $this->input->post('role'), 'staff_id' => 0);
                                $insert_id = $this->staff_model->batchInsert($staff_data, $role_array);
                                $staff_id  = $insert_id;
                                $inserted_count++; // Keep track of inserted records
                            }

                            if ($staff_id) {
                                //***** generate barcode and qrcode of staff ******//
                                $scan_type= $this->sch_setting_detail->scan_code_type;
                                $this->customlib->generatestaffbarcode($staff_data['employee_id'],$staff_id,$scan_type);
                                //***** generate barcode and qrcode of staff ******//
                            }

                            if ($staff_id && !$existing_staff_id) { // Only send login credential for new inserts
                                $teacher_login_detail = array('id' => $staff_id, 'credential_for' => 'staff', 'username' => $staff_data['email'], 'password' => $password, 'contact_no' => $staff_data['contact_no'], 'email' => $staff_data['email']);
                                $this->mailsmsconf->mailsms('login_credential', $teacher_login_detail);
                            }
                        } ///Result loop
                    } //Not emprty l

?>