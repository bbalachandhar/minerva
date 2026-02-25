<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form Review - <?php echo isset($reference_no) ? $reference_no : 'N/A'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .form-container {
            margin: 40px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .header {
            background: #253976;
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 12px 12px 0 0;
        }
        .logo-left {
            width: 70%;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
            margin-top: 20px;
            object-fit: contain;
        }
        .logo-right {
            width: 70%;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
            margin-top: 20px;
            object-fit: contain;
        }
        .section-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            border-left: 5px solid #253976;
        }
        .data-label {
            font-weight: bold;
        }
        .data-value {
            word-break: break-word;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body * {
                visibility: hidden;
            }
            .printable-area, .printable-area * {
                visibility: visible;
            }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
             .container {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0;
                margin: 0;
            }
            .form-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                border-radius: 0 !important;
                border: none !important;
            }
            .section-card {
                border: 1px solid #dee2e6 !important;
                page-break-inside: avoid;
            }
            h1, h2, h3, h4, h5, h6 {
                page-break-after: avoid;
            }
            body {
                font-size: 8pt;
            }
            p {
                line-height: 1.2;
                margin-bottom: 5px; /* Adjust as needed */
            }
            .print-only-header {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <?php
    $courses = [
        1 => 'B.Arch - Bachelor of Architecture',
        2 => 'B.E. CIVIL - Civil Engineering',
        3 => 'B.E. CSE - Computer Science Engineering',
        4 => 'B.E. CSE(AIML) - CSE(Artificial Intelligence & Machine Learning)',
        5 => 'B.E. EEE - Electrical and Electronics Engineering',
        6 => 'B.E. ECE - Electronics and Communication Engineering',
        7 => 'B.E. EIE - Electronics and Instrumentation Engineering',
        8 => 'B.E. MECH - Mechanical Engineering',
        9 => 'B.TECH. AIDS - Artificial Intelligence and Data Science',
        10 => 'B.TECH. CSBS - Computer Science and Business System',
        11 => 'B.TECH. IT - Information Technology ',
        12 => 'M. Arch. - Master of Architecture',
        13 => 'M.B.A - Master of Business Administration',
        14 => 'M.C.A - Master of Computer Application',
        15 => 'M.E. AE - Applied Electronics',
        16 => 'M.E. CSE - Computer Science and Engineering',
        17 => 'M.E. PED - Power Electronics and Drives',
    ];
    ?>
    <div class="container">
        <div class="form-container printable-area">
            <!-- Header for screen and print display -->
            <div class="review-header">
                <?php if (!empty($general_purpose_header_image)): ?>
                    <img src="<?php echo base_url('uploads/print_headerfooter/general_purpose/' . $general_purpose_header_image); ?>" style="width: 100%; height: auto;">
                <?php endif; ?>
            </div>

            
            <div class="section-card">
                <div class="row">
                    <div class="col-md-9">
                        <h5 class="text-center mb-4 fw-bold">APPLICATION FORM FOR ADMISSION</h5>
                         <p><span class="data-label">Course Level:</span> <span class="data-value text-uppercase"><?php echo (isset($course_level) && !empty($course_level)) ? $course_level : 'N/A'; ?></span></p>
                         <p><span class="data-label">Academic Year:</span> <span class="data-value">2026-2027</span></p>
                         <p><span class="data-label">Application Ref No:</span> <span class="data-value"><?php echo isset($reference_no) ? $reference_no : 'N/A'; ?></span></p>
                         <p><span class="data-label">Application Fee Status:</span> <span class="data-value"><?php echo (isset($paid_status) && $paid_status == 1) ? '<span style="color: #28a745; font-weight: bold;">PAID</span>' : '<span style="color: #dc3545; font-weight: bold;">PENDING</span>'; ?></span></p>
                    </div>
                    <div class="col-md-3">
                         <?php if (isset($student_pic) && !empty($student_pic)): ?>
                            <img src="<?php echo base_url() . $student_pic; ?>" alt="Student Photo" style="width: 120px; height: 150px; border: 1px solid #ddd; padding: 5px; object-fit: cover;">
                        <?php else: ?>
                            <p>No Photo</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h5 class="mb-2">PERSONAL DETAILS</h5>
                <div class="row">
                    <div class="col-md-4"><p><span class="data-label">Name:</span> <span class="data-value"><?php echo isset($firstname) ? $firstname : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Father's Name:</span> <span class="data-value"><?php echo isset($father_name) ? $father_name : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Father's Mobile:</span> <span class="data-value"><?php echo isset($father_phone) ? $father_phone : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Father's Occupation:</span> <span class="data-value"><?php echo isset($father_occupation) ? $father_occupation : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Mother's Name:</span> <span class="data-value"><?php echo isset($mother_name) ? $mother_name : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Mother's Mobile:</span> <span class="data-value"><?php echo isset($mother_phone) ? $mother_phone : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Mother's Occupation:</span> <span class="data-value"><?php echo isset($mother_occupation) ? $mother_occupation : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Gender:</span> <span class="data-value"><?php echo !empty($gender) ? ucfirst($gender) : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Community:</span> <span class="data-value"><?php echo !empty($community) ? $community : (!empty($cast) ? $cast : 'N/A'); ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Email ID:</span> <span class="data-value"><?php echo isset($email) ? $email : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Student's Mobile:</span> <span class="data-value"><?php echo isset($mobileno) ? $mobileno : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Date of Birth:</span> <span class="data-value"><?php echo (isset($dob) && !empty($dob)) ? date($this->customlib->getSchoolDateFormat(), strtotime($dob)) : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Aadhaar Number:</span> <span class="data-value"><?php echo isset($adhar_no) ? $adhar_no : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Communication Address:</span> <span class="data-value"><?php echo isset($current_address) ? $current_address : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Permanent Address:</span> <span class="data-value"><?php echo isset($permanent_address) ? $permanent_address : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">State:</span> <span class="data-value"><?php echo isset($state) ? $state : 'N/A'; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">City:</span> <span class="data-value"><?php echo isset($city) ? $city : 'N/A'; ?></span></p></div>
                </div>
            </div>

            <?php if (isset($reference_details) && $reference_details): ?>
            <div class="section-card">
                <h5 class="mb-2">REFERENCES DETAILS</h5>
                <div class="row">
                    <div class="col-md-4">
                        <p><span class="data-label">Referrer Name:</span> <span class="data-value"><?php echo $reference_details['referrer_name']; ?></span></p>
                    </div>
                    <div class="col-md-4">
                        <p><span class="data-label">Relationship:</span> <span class="data-value"><?php echo $reference_details['relationship']; ?></span></p>
                    </div>
                    <div class="col-md-4">
                        <p><span class="data-label">Phone No:</span> <span class="data-value"><?php echo $reference_details['phone_no']; ?></span></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            // Show course details if: (1) course_level is UG with HSC marks/details, OR (2) ug_course_id is set, OR (3) there are HSC marks
            $show_course_details = (isset($course_level) && $course_level == 'ug' && (isset($ug_details) && $ug_details || isset($total_maths) && $total_maths !== null || isset($total_physics) && $total_physics !== null || isset($total_chemistry) && $total_chemistry !== null)) 
                                   || (isset($ug_course_id) && $ug_course_id) 
                                   || (isset($total_maths) && $total_maths !== null) 
                                   || (isset($total_physics) && $total_physics !== null) 
                                   || (isset($total_chemistry) && $total_chemistry !== null);
            ?>
            <?php if ($show_course_details): ?>
            <div class="section-card">
                <h5 class="mb-3">COURSE DETAILS</h5>
                <div class="row">
                    <div class="col-md-12">
                        <?php 
                        $course_name = 'Not Selected';
                        if (isset($ug_course_id) && $ug_course_id && isset($course_names[$ug_course_id])) {
                            $course_name = $course_names[$ug_course_id];
                        }
                        ?>
                        <p><span class="data-label">Course applied:</span> <span class="data-value"><?php echo $course_name; ?></span></p>
                        <p><span class="data-label">Name of the school of X std:</span> <span class="data-value"><?php echo !empty($school_name_x) ? $school_name_x : 'N/A'; ?></span></p>
                        <p><span class="data-label">Year of passing of X std:</span> <span class="data-value"><?php echo !empty($passing_year_x) ? $passing_year_x : 'N/A'; ?></span></p>
                        <p><span class="data-label">X marks (in %):</span> <span class="data-value"><?php echo ($tenth_marks_percentage !== null && $tenth_marks_percentage !== '') ? $tenth_marks_percentage : 'N/A'; ?></span></p>
                    </div>
                </div>
            </div>
            <div class="section-card">
                <h5 class="mb-3">HSC Examination Details</h5>
                <?php
                $hsc_details = (isset($ug_details) && $ug_details && !empty($ug_details)) ? $ug_details : array(
                    'maths_marks' => isset($maths_marks) ? $maths_marks : '',
                    'total_maths' => isset($total_maths) ? $total_maths : '',
                    'maths_perc' => isset($maths_perc) ? $maths_perc : '',
                    'physics_marks' => isset($physics_marks) ? $physics_marks : '',
                    'total_physics' => isset($total_physics) ? $total_physics : '',
                    'physics_perc' => isset($physics_perc) ? $physics_perc : '',
                    'chemistry_marks' => isset($chemistry_marks) ? $chemistry_marks : '',
                    'total_chemistry' => isset($total_chemistry) ? $total_chemistry : '',
                    'chemistry_perc' => isset($chemistry_perc) ? $chemistry_perc : '',
                    'average_marks' => isset($average_marks) ? $average_marks : '',
                    'cutoff_marks' => isset($cutoff_marks) ? $cutoff_marks : '',
                );
                ?>
                <table class="table table-bordered">
                    <thead><tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th><th>Percentage</th></tr></thead>
                    <tbody>
                        <tr><td>Maths (M)</td><td><?php echo isset($hsc_details['maths_marks']) ? $hsc_details['maths_marks'] : ''; ?></td><td><?php echo isset($hsc_details['total_maths']) ? $hsc_details['total_maths'] : ''; ?></td><td><?php echo isset($hsc_details['maths_perc']) ? $hsc_details['maths_perc'] : ''; ?><?php if(isset($hsc_details['maths_perc']) && $hsc_details['maths_perc'] != '') echo '%'; ?></td></tr>
                        <tr><td>Physics (P)</td><td><?php echo isset($hsc_details['physics_marks']) ? $hsc_details['physics_marks'] : ''; ?></td><td><?php echo isset($hsc_details['total_physics']) ? $hsc_details['total_physics'] : ''; ?></td><td><?php echo isset($hsc_details['physics_perc']) ? $hsc_details['physics_perc'] : ''; ?><?php if(isset($hsc_details['physics_perc']) && $hsc_details['physics_perc'] != '') echo '%'; ?></td></tr>
                        <tr><td>Chemistry (C)</td><td><?php echo isset($hsc_details['chemistry_marks']) ? $hsc_details['chemistry_marks'] : ''; ?></td><td><?php echo isset($hsc_details['total_chemistry']) ? $hsc_details['total_chemistry'] : ''; ?></td><td><?php echo isset($hsc_details['chemistry_perc']) ? $hsc_details['chemistry_perc'] : ''; ?><?php if(isset($hsc_details['chemistry_perc']) && $hsc_details['chemistry_perc'] != '') echo '%'; ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="section-card">
                <h5 class="mb-3">Calculated Values</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="data-label">Average Marks (P+C+M)/3:</span> <span class="data-value"><?php echo isset($hsc_details['average_marks']) ? $hsc_details['average_marks'] : ''; ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="data-label">Cut Off Marks (P+C)/2 + M:</span> <span class="data-value"><?php echo isset($hsc_details['cutoff_marks']) ? $hsc_details['cutoff_marks'] : ''; ?></span></p>
                    </div>
                </div>
            </div>
                <?php
                $is_barch_course = (isset($course_name) && stripos($course_name, 'ARCH') !== false);
                $has_nata_data = (
                    isset($nata_details) &&
                    is_array($nata_details) &&
                    (
                        !empty($nata_details['nata_score']) ||
                        !empty($nata_details['application_number']) ||
                        !empty($nata_details['nata_year'])
                    )
                );
                ?>
                <?php if ($is_barch_course && $has_nata_data): ?>
                <div class="section-card">
                    <h5 class="mb-2">NATA Details</h5>
                    <p><span class="data-label">Score:</span> <span class="data-value"><?php echo $nata_details['nata_score']; ?></span></p>
                    <p><span class="data-label">Application Number:</span> <span class="data-value"><?php echo $nata_details['application_number']; ?></span></p>
                    <p><span class="data-label">Year:</span> <span class="data-value"><?php echo $nata_details['nata_year']; ?></span></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($course_level) && $course_level == 'lateral' && isset($lateral_details) && $lateral_details): ?>
            <div class="section-card">
                <h5 class="mb-3">LATERAL ENTRY DETAILS</h5>
                <?php
                $course_id = $lateral_details['lateral_course_id'];
                $course_name = isset($courses[$course_id]) ? $courses[$course_id] : 'Unknown Course';
                ?>
                 <p><span class="data-label">Course apply:</span> <span class="data-value"><?php echo $course_name; ?></span></p>
                <p><span class="data-label">Name of the school of X std:</span> <span class="data-value"><?php echo !empty($school_name_x) ? $school_name_x : 'N/A'; ?></span></p>
                <p><span class="data-label">Year of passing of X std:</span> <span class="data-value"><?php echo !empty($passing_year_x) ? $passing_year_x : 'N/A'; ?></span></p>
                <p><span class="data-label">X marks (in %):</span> <span class="data-value"><?php echo ($tenth_marks_percentage !== null && $tenth_marks_percentage !== '') ? $tenth_marks_percentage : 'N/A'; ?></span></p>

                <h5 class="mt-4">Semester Marks</h5>
                <h6>Pre-Final Semester</h6>
                <?php
                $pre_final_sem_subjects = json_decode($lateral_details['pre_final_sem_subjects']);
                if($pre_final_sem_subjects):
                ?>
                <table class="table table-bordered">
                    <thead><tr><th>Subject</th><th>Marks</th><th>Total Marks</th></tr></thead>
                    <tbody>
                    <?php foreach($pre_final_sem_subjects as $subject): ?>
                        <tr>
                            <td><?php echo $subject->subject; ?></td>
                            <td><?php echo $subject->marks; ?></td>
                            <td><?php echo $subject->total_marks; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <h6>Final Semester</h6>
                <?php
                $final_sem_subjects = json_decode($lateral_details['final_sem_subjects']);
                if($final_sem_subjects):
                ?>
                <table class="table table-bordered">
                    <thead><tr><th>Subject</th><th>Marks</th><th>Total Marks</th></tr></thead>
                    <tbody>
                    <?php foreach($final_sem_subjects as $subject): ?>
                        <tr>
                            <td><?php echo $subject->subject; ?></td>
                            <td><?php echo $subject->marks; ?></td>
                            <td><?php echo $subject->total_marks; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($course_level) && $course_level == 'pg' && isset($pg_details) && $pg_details): ?>
            <div class="section-card">
                 <h5 class="mb-3">POSTGRADUATE DETAILS</h5>
                <?php
                $course_id = $pg_details['pg_course_id'];
                $course_name = isset($courses[$course_id]) ? $courses[$course_id] : 'Unknown Course';
                ?>
                 <p><span class="data-label">Course apply:</span> <span class="data-value"><?php echo $course_name; ?></span></p>
                 <p><span class="data-label">Qualifying Exam:</span> <span class="data-value"><?php echo $pg_details['qualifying_exam']; ?></span></p>
                 <p><span class="data-label">Branch:</span> <span class="data-value"><?php echo $pg_details['branch']; ?></span></p>
                 <p><span class="data-label">Year of Passing:</span> <span class="data-value"><?php echo $pg_details['year_of_passing']; ?></span></p>
                 <p><span class="data-label">College:</span> <span class="data-value"><?php echo $pg_details['college_name']; ?></span></p>
                 <p><span class="data-label">University:</span> <span class="data-value"><?php echo $pg_details['university_name']; ?></span></p>
                 <p><span class="data-label">TANCET/PGETA App No:</span> <span class="data-value"><?php echo $pg_details['tancet_pgeta_app_no']; ?></span></p>
                 <p><span class="data-label">TANCET/PGETA Year:</span> <span class="data-value"><?php echo $pg_details['tancet_pgeta_year']; ?></span></p>
                 <p><span class="data-label">TANCET/PGETA Score:</span> <span class="data-value"><?php echo $pg_details['tancet_pgeta_score']; ?></span></p>
                 
            </div>
            <?php endif; ?>
            
            <!-- Declaration and Signature Section -->
            <div class="section-card" style="margin-top: 30px;">
                <div style="border: 2px solid #ddd; padding: 20px;">
                    <h4 style="text-transform: uppercase; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">Declaration</h4>
                    
                    <div style="margin-bottom: 30px;">
                        <p style="margin-bottom: 5px;"><strong>SIGNATURE OF STUDENT</strong> ________________________</p>
                        <ol style="margin-top: 15px; line-height: 1.8;">
                            <li>I hereby declare that I myself responsible for the timely payment of all fees payable to the College, as per the rules of the Management amended from time to time in respect of my ward Mr./Ms.__________________ during the period of his / her study at the Institution.</li>
                            <li>I assure you my ward will not discontinue the studies at the Institution under any circumstances after joining the course.</li>
                            <li>I hold myself responsible for the good behaviour of my ward and ensure he / she adheres to the rules and regulations of the college.</li>
                        </ol>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <p><strong>SIGNATURE OF THE PARENT</strong> ________________________</p>
                    </div>

                    <div style="background-color: #2c3e50; color: white; padding: 10px 20px; text-align: center; margin: 30px 0;">
                        <h4 style="margin: 0; color: white; text-transform: uppercase; font-weight: bold;">JOINT DECLARATION BY THE APPLICANT AND PARENT</h4>
                    </div>

                    <div style="margin-top: 20px;">
                        <p style="text-align: justify; line-height: 1.8; margin-bottom: 30px;">
                            The information furnished above is true and correct to the best of our knowledge. The original certificate will be produced at the time of admission or on demand. In case of any information furnished above, is found to be incorrect or false at later date on verification, we agree to forfeit the admission and shall not claim any compensation / refund.
                        </p>

                        <div class="row" style="margin-top: 30px;">
                            <div class="col-md-6">
                                <p><strong>Signature of the Student</strong></p>
                                <p style="border-bottom: 1px solid #000; padding-bottom: 30px; margin-bottom: 20px;">&nbsp;</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Signature of the Parent</strong></p>
                                <p style="border-bottom: 1px solid #000; padding-bottom: 30px; margin-bottom: 20px;">&nbsp;</p>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 20px;">
                            <div class="col-md-6">
                                <p><strong>Date</strong></p>
                                <p style="border-bottom: 1px solid #000; padding-bottom: 30px; margin-bottom: 20px;">&nbsp;</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Place</strong></p>
                                <p style="border-bottom: 1px solid #000; padding-bottom: 30px; margin-bottom: 20px;">&nbsp;</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Declaration and Signature Section -->
            
            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">Print Application</button>
            </div>
        </div>
    </div>
</body>
</html>