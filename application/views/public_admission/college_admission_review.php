<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form Review</title>
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
                         <p><span class="data-label">Course Level:</span> <span class="data-value text-uppercase"><?php echo $course_level; ?></span></p>
                         <p><span class="data-label">Academic Year:</span> <span class="data-value">2025-2026</span></p>
                    </div>
                    <div class="col-md-3">
                         <?php if (isset($student_pic) && !empty($student_pic)): ?>
                            <img src="<?php echo base_url() . $student_pic; ?>" alt="Student Photo" style="width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;">
                        <?php else: ?>
                            <p>No Photo</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h5 class="mb-2">PERSONAL DETAILS</h5>
                <div class="row">
                    <div class="col-md-4"><p><span class="data-label">Name:</span> <span class="data-value"><?php echo $firstname; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Father's Name:</span> <span class="data-value"><?php echo $father_name; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Father's Mobile:</span> <span class="data-value"><?php echo $father_phone; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Father's Occupation:</span> <span class="data-value"><?php echo $father_occupation; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Mother's Name:</span> <span class="data-value"><?php echo $mother_name; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Mother's Mobile:</span> <span class="data-value"><?php echo $mother_phone; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Mother's Occupation:</span> <span class="data-value"><?php echo $mother_occupation; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Gender:</span> <span class="data-value"><?php echo $gender; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Email ID:</span> <span class="data-value"><?php echo $email; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Student's Mobile:</span> <span class="data-value"><?php echo $mobileno; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Date of Birth:</span> <span class="data-value"><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($dob)); ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Aadhaar Number:</span> <span class="data-value"><?php echo $adhar_no; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Communication Address:</span> <span class="data-value"><?php echo $current_address; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">Permanent Address:</span> <span class="data-value"><?php echo $permanent_address; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">State:</span> <span class="data-value"><?php echo $state; ?></span></p></div>
                    <div class="col-md-4"><p><span class="data-label">City:</span> <span class="data-value"><?php echo $city; ?></span></p></div>
                </div>
            </div>

            <?php if ($reference_details): ?>
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

            <?php if ($course_level == 'ug' && ($ug_details || $total_maths !== null || $total_physics !== null || $total_chemistry !== null)): ?>
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
                        <p><span class="data-label">Courses applied:</span> <span class="data-value"><?php echo $course_name; ?></span></p>
                    </div>
                </div>
            </div>
            <div class="section-card">
                <h5 class="mb-3">HSC Examination Details</h5>
                <?php
                $hsc_details = $ug_details ? $ug_details : array(
                    'maths_marks' => $maths_marks,
                    'total_maths' => $total_maths,
                    'maths_perc' => $maths_perc,
                    'physics_marks' => $physics_marks,
                    'total_physics' => $total_physics,
                    'physics_perc' => $physics_perc,
                    'chemistry_marks' => $chemistry_marks,
                    'total_chemistry' => $total_chemistry,
                    'chemistry_perc' => $chemistry_perc,
                    'average_marks' => $average_marks,
                    'cutoff_marks' => $cutoff_marks,
                );
                ?>
                <table class="table table-bordered">
                    <thead><tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th><th>Percentage</th></tr></thead>
                    <tbody>
                        <tr><td>Maths (M)</td><td><?php echo $hsc_details['maths_marks']; ?></td><td><?php echo $hsc_details['total_maths']; ?></td><td><?php echo $hsc_details['maths_perc']; ?>%</td></tr>
                        <tr><td>Physics (P)</td><td><?php echo $hsc_details['physics_marks']; ?></td><td><?php echo $hsc_details['total_physics']; ?></td><td><?php echo $hsc_details['physics_perc']; ?>%</td></tr>
                        <tr><td>Chemistry (C)</td><td><?php echo $hsc_details['chemistry_marks']; ?></td><td><?php echo $hsc_details['total_chemistry']; ?></td><td><?php echo $hsc_details['chemistry_perc']; ?>%</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="section-card">
                <h5 class="mb-3">Calculated Values</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="data-label">Average Marks (P+C+M)/3:</span> <span class="data-value"><?php echo $hsc_details['average_marks']; ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="data-label">Cut Off Marks (P+C)/2 + M:</span> <span class="data-value"><?php echo $hsc_details['cutoff_marks']; ?></span></p>
                    </div>
                </div>
            </div>
                <?php if ($nata_details): ?>
                <div class="section-card">
                    <h5 class="mb-2">NATA Details</h5>
                    <p><span class="data-label">Score:</span> <span class="data-value"><?php echo $nata_details['nata_score']; ?></span></p>
                    <p><span class="data-label">Application Number:</span> <span class="data-value"><?php echo $nata_details['application_number']; ?></span></p>
                    <p><span class="data-label">Year:</span> <span class="data-value"><?php echo $nata_details['nata_year']; ?></span></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($course_level == 'lateral' && $lateral_details): ?>
            <div class="section-card">
                <h5 class="mb-3">LATERAL ENTRY DETAILS</h5>
                <?php
                $course_id = $lateral_details['lateral_course_id'];
                $course_name = isset($courses[$course_id]) ? $courses[$course_id] : 'Unknown Course';
                ?>
                 <p><span class="data-label">Courses Offered:</span> <span class="data-value"><?php echo $course_name; ?></span></p>
                <p><span class="data-label">Name of the school of X std:</span> <span class="data-value"><?php echo $lateral_details['school_name_x']; ?></span></p>
                <p><span class="data-label">Year of passing of X std:</span> <span class="data-value"><?php echo $lateral_details['passing_year_x']; ?></span></p>

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

            <?php if ($course_level == 'pg' && $pg_details): ?>
            <div class="section-card">
                 <h5 class="mb-3">POSTGRADUATE DETAILS</h5>
                <?php
                $course_id = $pg_details['pg_course_id'];
                $course_name = isset($courses[$course_id]) ? $courses[$course_id] : 'Unknown Course';
                ?>
                 <p><span class="data-label">Courses Offered:</span> <span class="data-value"><?php echo $course_name; ?></span></p>
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
            
            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-primary">Print Application</button>
            </div>
        </div>
    </div>
</body>
</html>