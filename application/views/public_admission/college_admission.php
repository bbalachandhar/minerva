<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form - Meenakshi College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;

            position: relative;
            height: 100vh;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            position: relative;
        }
        #particles-js {
            position: fixed; 
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1; 
        }
        body::before {
            content: "";
            position: absolute;
            top: -50px;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: 1;
        }
        .form-container {
            position: relative;
            z-index: 2;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.9); 
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .header {
            background: #253976;
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 12px 12px 0 0;
            position: relative;
        }
        .header img {
            top: 15px;
        }
        .logo-left {
            width: 60%;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
            margin-top: 20px;
        }
        .logo-right {
            right: 20px;
            width: 80%;
            padding: 10px;
            border-radius: 10px;
            background: #fff;
            margin-top: 20px;
        }
        .section-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            border-left: 5px solid #253976;
        }
        .admission-type-options {
            display: flex;
            width: 100%;
            gap: 12px;
            flex-wrap: nowrap;
        }
        .admission-type-options .form-check {
            flex: 1 1 0;
            margin-right: 0;
        }
        .passport-upload-frame {
            width: 35mm;
            height: 45mm;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            margin: 0 auto;
            background: #f8f9fa;
        }.form-control {
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }
        .form-control:focus {
            border-color: #253976;
            box-shadow: 0 0 5px rgba(37, 57, 118, 0.5);
        }
        .btn-submit {
            background: #253976;
            color: white;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            width: 100%;
            transition: 0.3s;
            border: 2px solid #5d78ff; /* Added blue border */
        }
        .btn-submit:hover {
            background: #d0d2d8;
            color:#253976;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        .table th {
            background: #253976;
            color: white;
            text-align: center;
        }
        .nav-pills .nav-link {
            background: #f1f1f1;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s;
        }
        .nav-pills .nav-link.active {
            background: #253976;
            color: white;
        }
        .course-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease-in-out;
            cursor: pointer;
            border: 1px solid #ddd;
            font-weight: 500;
            margin-bottom: 10px;
        }
        .course-card:hover {
            background: #f5f5f5;
            border-color: #253976;
        }
        .course-card input {
            margin-right: 10px;
        }
        .cus_form{
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .Upload_pic{
            width:190px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="container">
        <div class="form-container">
            <div class="header row">
                <div class="col-md-2">
                    <img src="<?php echo base_url('uploads/logos/' . $sch_setting->admission_logo_left); ?>" alt="College Logo" class="logo-left">
                </div>
                <div class="col-md-7">
                    <h2 class="mb-1"><?php echo $sch_setting->name; ?></h2>
                    <p class="mb-0"><?php echo $sch_setting->address; ?></p>
                    <p>Ph: <?php echo $sch_setting->phone; ?> | Email: <?php echo $sch_setting->email; ?> | Website: <?php echo isset($sch_setting->website) ? $sch_setting->website : ''; ?></p>
                </div>
                <div class="col-md-3">
                    <img src="<?php echo base_url('uploads/logos/' . $sch_setting->admission_logo_right); ?>" alt="College Logo" class="logo-right">
                </div>
            </div>
            <form action="<?php echo site_url('publicadmissionform/add_college_admission'); ?>" method="POST" enctype="multipart/form-data" id="admission_form">
                <?php if (!empty($enquiry_id)) { ?>
                    <input type="hidden" name="enquiry_id" value="<?php echo htmlspecialchars($enquiry_id); ?>">
                <?php } ?>
                <div class="section-card">
                    <h5 class="text-center mb-4">APPLICATION FORM FOR ADMISSION</h5>
                    <div class="mb-4">
                    <!-- Academic Year and Course Level Row -->
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Academic Year:</label>
                                <input type="text" class="form-control" name="academic_year" id="academic_year" value="2026-2027" readonly tabindex="-1">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label class="form-label">Admission Type*</label>
                                <div class="admission-type-options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="courseLevel" id="ugRadio" value="ug" checked tabindex="7">
                                        <label class="form-check-label" for="ugRadio">Undergraduate (UG)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="courseLevel" id="lateralRadio" value="lateral" tabindex="8">
                                        <label class="form-check-label" for="lateralRadio">Lateral Entry</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="courseLevel" id="pgRadio" value="pg" tabindex="9">
                                        <label class="form-check-label" for="pgRadio">Postgraduate (PG)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <div id="image-upload-area" class="passport-upload-frame border rounded d-flex flex-column align-items-center justify-content-center">
                                    <input type="file" id="imageUpload" name="user_image" accept="image/*" required tabindex="4" style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                                    <img id="previewImage" src="" alt="Preview" class="d-none" style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px;">
                                    <i id="uploadIcon" class="bi bi-cloud-upload-fill text-primary" style="font-size: 20px;"></i>
                                </div>
                                <small id="uploadNote" class="text-muted text-center d-block mt-1">Max size: 300KB *</small>
                                <span id="image_upload_error" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Course Selection Row -->
                    <div class="row" id="courseSelectionRow">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Course apply*</label>
                                <select class="form-control" name="ug_course" id="ug_course" tabindex="5" required>
                                    <option value="">Select a Course</option>
                                    <?php if (!empty($ug_first_year_courses)) { ?>
                                        <?php foreach ($ug_first_year_courses as $course) { ?>
                                            <option value="<?php echo (int)$course['id']; ?>" data-govt-fee="<?php echo (float)$course['govt_fee']; ?>" data-mgt-fee="<?php echo (float)$course['mgt_fee']; ?>" data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <select class="form-control" name="lateral_course" id="lateral_course" style="display:none;" tabindex="5">
                                    <option value="">Select a Course</option>
                                    <?php if (!empty($ug_lateral_courses)) { ?>
                                        <?php foreach ($ug_lateral_courses as $course) { ?>
                                            <option value="<?php echo (int)$course['id']; ?>" data-govt-fee="<?php echo (float)$course['govt_fee']; ?>" data-mgt-fee="<?php echo (float)$course['mgt_fee']; ?>" data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <select class="form-control" name="pg_course" id="pg_course" style="display:none;" tabindex="5">
                                    <option value="">Select a Course</option>
                                    <?php if (!empty($pg_first_year_courses)) { ?>
                                        <?php foreach ($pg_first_year_courses as $course) { ?>
                                            <option value="<?php echo (int)$course['id']; ?>" data-govt-fee="<?php echo (float)$course['govt_fee']; ?>" data-mgt-fee="<?php echo (float)$course['mgt_fee']; ?>" data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Quota Type*</label>
                                <select class="form-control" id="quota_type" name="quota_type" required tabindex="6">
                                    <option value="">Select Quota</option>
                                    <option value="government">Government</option>
                                    <option value="management" selected>Management</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Course Fee (Auto)</label>
                                <input type="text" class="form-control" id="course_fee_display" readonly tabindex="-1" placeholder="Select course + quota">
                                <input type="hidden" id="course_fee_total" name="course_fee_total" value="">
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                <div class="section-card">
                    <h5 class="mb-2">PERSONAL DETAILS</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Name (In block letters with initial at the end)*</label>
                                <input type="text" class="form-control" name="user_name" id="user_name" onkeydown="return allowAlphabets(event)" placeholder="Enter your full name" required tabindex="5" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Father's Name*</label>
                            <input type="text" class="form-control" placeholder="Enter your Father's Name" name="father_name" onkeydown="return allowAlphabets(event)" id="father_name" required tabindex="6">
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Father's/Guardian's Mobile Number*</label>
                            <input type="text" class="form-control" minlength="10" maxlength="10" placeholder="Enter your Father's Mobile Number"  onchange="validateMobile(this)" name="father_mobile" id="father_mobile"  onKeyPress="return checkIt(event);" required tabindex="7">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Father's Occupation*</label>
                            <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter your Father's Occupation"   name="father_occupation" id="father_occupation" required tabindex="8">
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Mother's Name*</label>
                            <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter your Mother's Name" name="mother_name" id="mother_name" required tabindex="9">
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Mother's/Guardian's Mobile Number*</label>
                            <input type="text" class="form-control" placeholder="Enter your Mother's Mobile Number" name="mother_mobile" id="mother_mobile"  onchange="validateMobile(this)" required minlength="10" maxlength="10" onKeyPress="return checkIt(event);" tabindex="10">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Mother's Occupation*</label>
                            <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter your Mother's Occupation" name="mother_occupation" id="mother_occupation" required tabindex="11">
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="gender">Gender*</label>
                                <select class="form-select" id="gender" name="gender" required tabindex="12">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Community*</label>
                            <select class="form-control" id="community" name="community" required tabindex="12a">
                                <option value="">Select Community</option>
                                <option value="OC">OC (General)</option>
                                <option value="BC">BC</option>
                                <option value="MBC">MBC</option>
                                <option value="BCM">BCM</option>
                                <option value="SC">SC</option>
                                <option value="SCA">SCA</option>
                                <option value="ST">ST</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Student's Email ID*</label>
                            <input type="email" class="form-control" placeholder="Enter your Email"  id="student_email" name="student_email" required tabindex="13" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                            <span id="email_error" class="text-danger"></span>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Student's Mobile Number*</label>
                            <input type="text" step="any" class="form-control" placeholder="Enter Student's Mobile Number" id="student_mobile" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" name="student_mobile" required minlength="10" maxlength="10" tabindex="14" value="<?php echo isset($mobileno) ? htmlspecialchars($mobileno) : ''; ?>">
                            <span id="mobile_error" class="text-danger"></span>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Student's D.O.B*</label>
                            <input type="text" class="form-control" placeholder="Select Date of Birth (DD/MM/YYYY)"  id="dob" name="dob" required tabindex="15">
                            <span id="dob_error" class="text-danger"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label class="form-label">Aadhaar Number*</label>
                            <input type="text" step="any" class="form-control" placeholder="Enter your Aadhar Number" id="aadhaar" name="aadhaar" required minlength="12" maxlength="12" onKeyPress="return checkIt(event);" tabindex="16">
                            <span id="aadhaar_error" class="text-danger"></span>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">State*</label>
                            <select class="form-control" id="state" name="state" required tabindex="17">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="mb-3 col-md-4">
                            <label class="form-label">City*</label>
                            <select class="form-control" id="city" name="city" required tabindex="18">
                                <option value="">Select City</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="city_other_text" name="city_custom" placeholder="Enter your city" style="display:none;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Address for Communication*</label>
                                <textarea class="form-control" placeholder="Enter your Communication Address"  name="comm_addr" id="comm_addr" required tabindex="19"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Permanent Address*</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="same_as_comm" name="same_as_comm">
                                        <label class="form-check-label" for="same_as_comm">
                                            Same as Communication Address
                                        </label>
                                    </div>
                                </div>
                                <textarea class="form-control" placeholder="Enter your Permanent Address" name="perm_addr" id="perm_addr" required tabindex="20"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-card">
                    <h5 class="mb-2">REFERENCES DETAILS (OPTIONAL)</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Referrer Name</th>
                                    <th>Relationship</th>
                                    <th>Phone No.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" class="form-control" name="referral_name" id="referral_name" onkeydown="return allowAlphabets(event)" tabindex="19"></td>
                                    <td><input type="text" class="form-control" name="relationship" id="relationship" onkeydown="return allowAlphabets(event)" tabindex="20"></td>
                                    <td><input type="text" class="form-control" name="phone_no" id="phone_no" minlength="10" maxlength="10"  onKeyPress="return checkIt(event);" tabindex="21"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="section-card" id="hscDetails">
                    <h5 class="mb-3">HSC Examination Details</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject</th>
                                    <th>Maximum Marks</th>
                                    <th>Marks Obtained</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Maths (M)</td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_maths" id="total_maths" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="25"></td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="maths_marks" id="maths_marks" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="26"></td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="maths_perc" id="maths_perc" readonly tabindex="-1"></td>
                                </tr>
                                <tr>
                                    <td>Physics (P)</td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_physics" id="total_physics" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="27"></td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="physics_marks" id="physics_marks" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="28"></td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="physics_perc" id="physics_perc" readonly tabindex="-1"></td>
                                </tr>
                                <tr>
                                    <td>Chemistry (C)</td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_chemistry" id="total_chemistry" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="29"></td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="chemistry_marks" id="chemistry_marks" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="30"></td>
                                    <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="chemistry_perc" id="chemistry_perc" readonly tabindex="-1"></td>
                                </tr>
                                <tr>
                                    <td><strong>Average: (P+C+M)/3</strong></td>
                                    <td colspan="3"><input type="number" step="0.01" min="0" max="100" value="0" class="form-control text-center" name="average_marks" id="average_marks" readonly tabindex="-1"></td>
                                </tr>
                                <tr>
                                    <td><strong>Cut Off: (P+C)/2 + M</strong></td>
                                    <td colspan="3"><input type="number" step="0.01" min="0" max="200" value="0" class="form-control text-center" name="cutoff_marks" id="cutoff_marks" readonly tabindex="-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="">

                    <div id="ugDetails">
                        <div class="section-card">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Name of the school of X std*</label>
                                        <input type="text" class="form-control" placeholder="Enter school name"  name="school_name" id="school_name" onkeydown="return allowAlphabets(event)" tabindex="22">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Year of passing of X std*</label>
                                        <input  type="text" class="form-control" placeholder="Select year" name="tenth_passing" id="tenth_passing" tabindex="23">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">X marks (in %)*</label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" placeholder="Enter marks %" name="tenth_marks_percentage" id="tenth_marks_percentage" tabindex="24">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="lateralDetails" style="display:none">
                        <div class="section-card">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Name of the school of X std*</label>
                                        <input type="text" class="form-control" placeholder="Enter school name"  name="lateral_school_name" id="lateral_school_name" onkeydown="return allowAlphabets(event)" tabindex="29">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Year of passing of X std*</label>
                                        <input  type="text" class="form-control" placeholder="Select year" name="lateral_tenth_passing" id="lateral_tenth_passing" tabindex="30">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">X marks (in %)*</label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" placeholder="Enter marks %" name="lateral_tenth_marks_percentage" id="lateral_tenth_marks_percentage" tabindex="31">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="section-card mt-4">
                            <h5 class="text-center mb-3">Lateral Entry - Semester Marks</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="border p-3">
                                        <h6 class="text-center">Pre-Final Semester Subjects</h6>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        1. <input type="text" name="presub1" id="presub1" class="form-control d-inline w-25" placeholder="Subject" tabindex="32"> : <input type="number" step="1"  class="form-control d-inline w-25" max="100" min="0" name="preout1" id="preout1" value="0" onKeyPress="return checkIt(event);" tabindex="33"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="premark1" id="premark1" onKeyPress="return checkIt(event);" tabindex="34">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        2. <input type="text" name="presub2" id="presub2" class="form-control d-inline w-25" placeholder="Subject" tabindex="35"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="preout2" id="preout2" value="0" onKeyPress="return checkIt(event);" tabindex="36"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="premark2" id="premark2" onKeyPress="return checkIt(event);" tabindex="37">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        3. <input type="text" name="presub3" id="presub3" class="form-control d-inline w-25" placeholder="Subject" tabindex="38"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="preout3" id="preout3" value="0" onKeyPress="return checkIt(event);" tabindex="39"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="premark3" id="premark3" onKeyPress="return checkIt(event);" tabindex="40">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        4. <input type="text" name="presub4" id="presub4" class="form-control d-inline w-25" placeholder="Subject" tabindex="41"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="preout4" id="preout4" value="0" onKeyPress="return checkIt(event);" tabindex="42"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="premark4" id="premark4" onKeyPress="return checkIt(event);" tabindex="43">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        5. <input type="text" name="presub5" id="presub5" class="form-control d-inline w-25" placeholder="Subject" tabindex="44"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="preout5" id="preout5" value="0" onKeyPress="return checkIt(event);" tabindex="45"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="premark5" id="premark5" onKeyPress="return checkIt(event);" tabindex="46">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        6. <input type="text" name="presub6" id="presub6" class="form-control d-inline w-25" placeholder="Subject" tabindex="47"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="preout6" id="preout6" value="0" onKeyPress="return checkIt(event);" tabindex="48"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="premark6" id="premark6" onKeyPress="return checkIt(event);" tabindex="49">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end fw-bold" colspan="2">Total : <input type="number" step="1" class="form-control d-inline w-25" name="pretotal" id="pretotal" value="0" onKeyPress="return checkIt(event);" readonly tabindex="-1"> out of <input type="number" step="1" class="form-control d-inline w-25" name="pretotal1" id="pretotal1" readonly onKeyPress="return checkIt(event);" value="0" tabindex="-1">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="border p-3">
                                        <h6 class="text-center">Final Semester Subjects</h6>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        1. <input type="text" name="finalsub1" id="finalsub1" class="form-control d-inline w-25" placeholder="Subject" tabindex="50"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="finalout1" id="finalout1" value="0" onKeyPress="return checkIt(event);" tabindex="51"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="finalmark1" id="finalmark1" onKeyPress="return checkIt(event);" tabindex="52">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        2. <input type="text" name="finalsub2" id="finalsub2" class="form-control d-inline w-25" placeholder="Subject" tabindex="53"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="finalout2" id="finalout2" value="0" onKeyPress="return checkIt(event);" tabindex="54"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="finalmark2" id="finalmark2" onKeyPress="return checkIt(event);" tabindex="55">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        3. <input type="text" name="finalsub3" id="finalsub3" class="form-control d-inline w-25" placeholder="Subject" tabindex="56"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="finalout3" id="finalout3" value="0" onKeyPress="return checkIt(event);" tabindex="57"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="finalmark3" id="finalmark3" onKeyPress="return checkIt(event);" tabindex="58">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        4. <input type="text" name="finalsub4" id="finalsub4" class="form-control d-inline w-25" placeholder="Subject" tabindex="59"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="finalout4" id="finalout4" value="0" onKeyPress="return checkIt(event);" tabindex="60"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="finalmark4" id="finalmark4" onKeyPress="return checkIt(event);" tabindex="61">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        5. <input type="text" name="finalsub5" id="finalsub5" class="form-control d-inline w-25" placeholder="Subject" tabindex="62"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="finalout5" id="finalout5" value="0" onKeyPress="return checkIt(event);" tabindex="63"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="finalmark5" id="finalmark5" onKeyPress="return checkIt(event);" tabindex="64">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        6. <input type="text" name="finalsub6" id="finalsub6" class="form-control d-inline w-25" placeholder="Subject" tabindex="65"> : <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" name="finalout6" id="finalout6" value="0" onKeyPress="return checkIt(event);" tabindex="66"> out of <input type="number" step="1" class="form-control d-inline w-25" max="100" min="0" placeholder="Marks"  value="0" name="finalmark6" id="finalmark6" onKeyPress="return checkIt(event);" tabindex="67">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end fw-bold" colspan="2">Total : <input type="number" step="1" class="form-control d-inline w-25" name="finaltotal" id="finaltotal" value="0" readonly onKeyPress="return checkIt(event);" tabindex="-1"> out of <input type="number" step="1" class="form-control d-inline w-25" name="finaltotal1" id="finaltotal1" readonly onKeyPress="return checkIt(event);" value="0" tabindex="-1">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="nata_sec" style="display:none">
                        <div class="section-card">
                            <h5 class="mb-2">NATA/JEE2 (for B.Arch only)</h5>
                            <div class="mb-3">
                                <label class="form-label">Score</label>
                                <input class="form-control" placeholder="Enter Score" name="nata_score" id="nata_score" tabindex="68">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Application Form</label>
                                <input class="form-control" placeholder="Enter Application Form" name="application_number" id="application_number" tabindex="69">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year</label>
                                <input class="form-control" placeholder="Enter Year" name="nata_year" id="nata_year" tabindex="70">
                            </div>
                        </div>
                    </div>
                    
                    <div id="pgDetails" style="display: none;">
                        <div class="section-card">
                            <h5 class="mb-3">Academic  Details</h5>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">UG Course Studied</label>
                                    <input class="form-control" type="text" placeholder="Enter your UG course" name="exam_passed" id="exam_passed" tabindex="71">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Major Stream</label>
                                    <input type="text" class="form-control" placeholder="Enter your major stream" name="branch" id="branch" tabindex="72">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Year of Passing</label>
                                    <input type="text" class="form-control" placeholder="Enter your Year" onKeyPress="return checkIt(event);" name="yop" id="yop" tabindex="74">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Name of the College</label>
                                    <input type="text" class="form-control" minlength="2" maxlength="200" placeholder="Enter your College" name="noc" id="noc" tabindex="75">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">University*</label>
                                    <select class="form-control" id="university_id" name="university_id" tabindex="76" required>
                                        <option value="">Select University</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">TANCET / PGETA Exam Application Number</label>
                                    <input type="text" step="any" class="form-control" placeholder="Enter your Application Number" name="pg_app_num" id="pg_app_num" tabindex="77">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">TANCET / PGETA Examination Year</label>
                                    <input type="text" step="any" class="form-control" minlength="4" maxlength="4" onKeyPress="return checkIt(event);" placeholder="Enter your examination year" name="exam_year" id="exam_year" tabindex="78">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">TANCET / PGETA Exam Score</label>
                                    <input type="number" step="any" class="form-control" placeholder="Enter your Score" name="exam_score" id="exam_score" tabindex="79">
                                </div>
                            </div>
                        </div>
                        <div class="section-card">
                            <h5 class="mb-3">Additional Information</h5>
                            <div class="mb-4">
                                <label class="form-label">UG Alumni of Meenakshi Group of Institutions</label>
                                <input type="file" class="form-control" name="bonafide" id="bonafide" tabindex="80">
                                <small class="text-muted">Attach Bonafide Certificate</small>
                            </div>
                            <div class="row">
                                <div class="mb-4">
                                    <label class="form-label">Eminent Sports Person</label><br>
                                    <input type="radio" name="sports" id="sports" value="Yes" tabindex="81"> Yes
                                    <input type="radio" name="sports" id="sports" value="No" checked tabindex="82"> No
                                    <br>
                                    <div id="level">
                                        <label class="form-label mt-4">Level</label>
                                        <select class="form-control" name="sports_level" id="sports_level" tabindex="83">
                                            <option value="">Select Level</option>
                                            <option value="District">District Level</option>
                                            <option value="State">State Level</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Ward of Ex-Service Men</label><br>
                                    <input type="radio" name="exservice" value="Yes" tabindex="84"> Yes
                                    <input type="radio" name="exservice" value="No" checked tabindex="85"> No
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Differently Abled Person</label><br>
                                <input type="radio" name="differently_abled" value="Yes" onclick="showDisabilityType(true)" tabindex="86"> Yes
                                <input type="radio" name="differently_abled" value="No" onclick="showDisabilityType(false)" checked tabindex="87"> No
                            </div>
                            <div class="mb-3" id="disabilityType" style="display: none;">
                                <label class="form-label">If Yes, Differently Abled Type</label>
                                <input type="text" class="form-control" placeholder="Enter type of disability" name="disability_type" id="disability_type" tabindex="88">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="payment_option" id="payment_option" value="">
                <button class="btn btn-submit mt-3" type="button" id="submit_application_btn" name="submit" tabindex="89">Submit Application</button>
            </form>
        </div>
    </div>
    <!-- Payment Option Modal -->
    <div class="modal fade" id="paymentOptionModal" tabindex="-1" aria-labelledby="paymentOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentOptionModalLabel">Complete Your Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>How would you like to proceed with the application fee?</p>
                    <button type="button" class="btn btn-primary btn-lg mt-3" id="payOnlineBtn">Pay Fee Online</button>
                    <button type="button" class="btn btn-secondary btn-lg mt-3" id="payLaterBtn">Pay Later</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Your application has been submitted, please contact Admission team!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="errorModalContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showDisabilityType(show) {
        document.getElementById("disabilityType").style.display = show ? "block" : "none";
    }
</script>
<script>
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 100,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#000000"
            },
            "shape": {
                "type": "circle"
            },
            "opacity": {
                "value": 0.5,
                "random": false
            },
            "size": {
                "value": 3,
                "random": true
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#000000",
                "opacity": 0.4,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 3,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out"
            }
        },
        "interactivity": {
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "repulse"
                }
            }
        }
    });
</script>

<script>
    // Initialize with UG selected by default
    document.addEventListener("DOMContentLoaded", function() {
        toggleCourseSelection();
        loadUniversities();
    });

    function toggleCourseSelection() {
        const ugRadio = document.getElementById("ugRadio");
        const lateralRadio = document.getElementById("lateralRadio");
        const pgRadio = document.getElementById("pgRadio");
        
        const ugDetails = document.getElementById("ugDetails");
        const lateralDetails = document.getElementById("lateralDetails");
        const pgDetails = document.getElementById("pgDetails");
        const hscDetails = document.getElementById("hscDetails");
        
        if(ugRadio.checked) {
            // Show UG details, hide others
            ugDetails.style.display = "block";
            lateralDetails.style.display = "none";
            pgDetails.style.display = "none";
            hscDetails.style.display = "block";
            
            // Show/hide course dropdowns in top row
            document.getElementById("ug_course").style.display = "block";
            document.getElementById("lateral_course").style.display = "none";
            document.getElementById("pg_course").style.display = "none";
            
            // Set required fields for UG
            setRequiredFields(true, false, false);
            setHscRequired(true);
        } 
        else if(lateralRadio.checked) {
            // Show Lateral details, hide others
            ugDetails.style.display = "none";
            lateralDetails.style.display = "block";
            pgDetails.style.display = "none";
            hscDetails.style.display = "none";
            
            // Show/hide course dropdowns in top row
            document.getElementById("ug_course").style.display = "none";
            document.getElementById("lateral_course").style.display = "block";
            document.getElementById("pg_course").style.display = "none";
            
            // Set required fields for Lateral
            setRequiredFields(false, true, false);
            setHscRequired(false);
            clearHscFields();
        }
        else if(pgRadio.checked) {
            // Show PG details, hide others
            ugDetails.style.display = "none";
            lateralDetails.style.display = "none";
            pgDetails.style.display = "block";
            hscDetails.style.display = "none";
            
            // Show/hide course dropdowns in top row
            document.getElementById("ug_course").style.display = "none";
            document.getElementById("lateral_course").style.display = "none";
            document.getElementById("pg_course").style.display = "block";
            
            // Set required fields for PG
            setRequiredFields(false, false, true);
            setHscRequired(false);
            clearHscFields();
        }
    }

    function setRequiredFields(ugRequired, lateralRequired, pgRequired) {
        // UG fields
        $("#ug_course").prop("required", ugRequired);
        $("#school_name").prop("required", ugRequired);
        $("#tenth_passing").prop("required", ugRequired);
        $("#tenth_marks_percentage").prop("required", ugRequired);
        
        // Lateral fields
        $("#lateral_course").prop("required", lateralRequired);
        $("#lateral_school_name").prop("required", lateralRequired);
        $("#lateral_tenth_passing").prop("required", lateralRequired);
        $("#lateral_tenth_marks_percentage").prop("required", lateralRequired);
        for (let i = 1; i <= 6; i++) {
            $(`#presub${i}`).prop("required", lateralRequired);
            $(`#preout${i}`).prop("required", lateralRequired);
            $(`#premark${i}`).prop("required", lateralRequired);
            $(`#finalsub${i}`).prop("required", lateralRequired);
            $(`#finalout${i}`).prop("required", lateralRequired);
            $(`#finalmark${i}`).prop("required", lateralRequired);
        }
        
        // PG fields
        $("#pg_course").prop("required", pgRequired);
        $("#exam_passed").prop("required", pgRequired);
        $("#branch").prop("required", pgRequired);
        $("#yop").prop("required", pgRequired);
        $("#noc").prop("required", pgRequired);
        $("#university_id").prop("required", pgRequired);
        $("#exam_score").prop("required", pgRequired);
        $("#exam_year").prop("required", pgRequired);
        $("#pg_app_num").prop("required", pgRequired);
    }

    function setHscRequired(isRequired) {
        $("#maths_marks").prop("required", isRequired);
        $("#total_maths").prop("required", isRequired);
        $("#physics_marks").prop("required", isRequired);
        $("#total_physics").prop("required", isRequired);
        $("#chemistry_marks").prop("required", isRequired);
        $("#total_chemistry").prop("required", isRequired);
    }

    function clearHscFields() {
        const hscFieldIds = [
            "total_maths",
            "maths_marks",
            "maths_perc",
            "total_physics",
            "physics_marks",
            "physics_perc",
            "total_chemistry",
            "chemistry_marks",
            "chemistry_perc",
            "average_marks",
            "cutoff_marks"
        ];

        hscFieldIds.forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = "";
            }
        });
    }

    // Add event listeners to all radio buttons
    document.getElementById("ugRadio").addEventListener("change", toggleCourseSelection);
    document.getElementById("lateralRadio").addEventListener("change", toggleCourseSelection);
    document.getElementById("pgRadio").addEventListener("change", toggleCourseSelection);

    function getSelectedCourseOption() {
        const courseLevel = $("input[name='courseLevel']:checked").val();
        if (courseLevel === 'ug') {
            return $('#ug_course option:selected');
        }
        if (courseLevel === 'lateral') {
            return $('#lateral_course option:selected');
        }
        if (courseLevel === 'pg') {
            return $('#pg_course option:selected');
        }
        return null;
    }

    function updateNataVisibility() {
        const selectedOption = getSelectedCourseOption();
        const isBarch = selectedOption && selectedOption.data('is-barch') == 1;
        if (isBarch) {
            $("#nata_sec").show();
            $("#nata_score").prop('required', true);
            $("#application_number").prop('required', true);
            $("#nata_year").prop('required', true);
        } else {
            $("#nata_sec").hide();
            $("#nata_score").prop('required', false);
            $("#application_number").prop('required', false);
            $("#nata_year").prop('required', false);
        }
    }

    function updateCourseFee() {
        const quotaType = $('#quota_type').val();
        const selectedOption = getSelectedCourseOption();
        if (!selectedOption || !selectedOption.val() || !quotaType) {
            $('#course_fee_display').val('');
            $('#course_fee_total').val('');
            return;
        }

        const govtFee = parseFloat(selectedOption.data('govt-fee') || 0);
        const mgtFee = parseFloat(selectedOption.data('mgt-fee') || 0);
        const fee = quotaType === 'government' ? govtFee : mgtFee;

        $('#course_fee_total').val(fee.toFixed(2));
        $('#course_fee_display').val(new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(fee));
    }

    // NATA section toggle for B.Arch and fee auto-calc
    $("#ug_course, #lateral_course, #pg_course, #quota_type, input[name='courseLevel']").on('change', function(){
        updateNataVisibility();
        updateCourseFee();
    });

    updateNataVisibility();
    updateCourseFee();

    // Load universities for PG dropdown
    function loadUniversities() {
        $.ajax({
            url: '<?php echo base_url("publicadmissionform/get_universities"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && Array.isArray(data)) {
                    let select = $('#university_id');
                    select.empty();
                    select.append('<option value="">Select University</option>');
                    
                    data.forEach(function(uni) {
                        select.append(`<option value="${uni.id}">${uni.name}</option>`);
                    });
                }
            },
            error: function(err) {
                console.error('Error loading universities:', err);
            }
        });
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#disabilityType').hide();
        $('#level').hide();

        $('input[name="differently_abled"]').change(function() {
            if ($(this).val() === 'Yes') {
                $('#disabilityType').show();
                $('#disabilityType input').prop('required', true);
            } else {
                $('#disabilityType').hide();
                $('#disabilityType input').prop('required', false);
            }
        });

        $('input[name="sports"]').change(function() {
            if ($(this).val() === 'Yes') {
                $('select.form-control').parent().show();
                $('select.form-control').prop('required', true);
            } else {
                $('select.form-control').parent().hide();
                $('select.form-control').prop('required', false);
            }
        });
    });

</script>
<script>
    $(document).ready(function () {

        $("#imageUpload").change(function (event) {
            let file = event.target.files[0];
            if (file) {
                let fileType = file.type;
                let fileSize = file.size; // in bytes
                const maxFileSize = 300 * 1024; // 300KB

                let validTypes = ["image/jpeg", "image/png"];
                if (!validTypes.includes(fileType)) {
                    alert("Only JPG and PNG images are allowed.");
                    $("#imageUpload").val(""); // Reset input
                    return;
                }
                if (fileSize > maxFileSize) {
                    alert("File size must be less than 300KB.");
                    $("#imageUpload").val(""); // Reset input
                    return;
                }
                let reader = new FileReader();
                reader.onload = function (e) {
                    $("#previewImage").attr("src", e.target.result).removeClass("d-none");
                    $("#uploadIcon, #uploadText, #uploadNote").addClass("d-none");
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
<script>
    function checkIt(evt) {
        evt = (evt) ? evt : window.event
        var charCode = (evt.which) ? evt.which : evt.keyCode
        // Allow tab key (charCode 9)
        if (charCode == 9) {
            return true;
        }
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            status = "This field accepts numbers only."
            return false
        }
        status = "";
        return true
    }
</script>
<script>
        function allowAlphabets(evt) {
            evt = evt || window.event;
            var charCode = evt.which || evt.keyCode;
            // Allow tab key (charCode 9)
            if (charCode == 9) {
                return true;
            }
            if ((charCode >= 65 && charCode <= 90) || 
                (charCode >= 97 && charCode <= 122) || 
                charCode == 8 || 
                charCode == 32) {
                return true;
            }   
            return false;
        }</script>
<script type="text/javascript">
   function validateMobile(input) {
    var mobileNumber = input.value.trim();
    if (!/^\d{10}$/.test(mobileNumber)) {
        alert("Please enter a valid 10-digit mobile number.");
        input.value = "";
        return false;
    }
    return true;
}
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#bonafide').change(function() {
            var file = this.files[0];
            var fileName = file.name;
            var fileExt = fileName.split('.').pop().toLowerCase();
            var fileSize = file.size;
        var maxSize = 5 * 1024 * 1024; // 5MBs
        if (fileExt !== 'pdf' || (file.type !== '' && file.type !== 'application/pdf')) {
            alert('Only PDF files are allowed!');
            $(this).val('');
            return false;
        }
        if (fileSize > maxSize) {
            alert('File size exceeds 5MB limit!');
            $(this).val('');
            return false;
        }
        return true;
    });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        function calculatePercentage(marksId, totalId, percId) {
            let marks = parseFloat($(marksId).val());
            let total = parseFloat($(totalId).val());

            // Validate total marks
            if (total > 100) {
                alert("Maximum Marks cannot exceed 100.");
                $(totalId).val(100);
                total = 100;
            }
            if (total < 0) {
                alert("Maximum Marks cannot be negative.");
                $(totalId).val(0);
                total = 0;
            }

            // Validate marks obtained
            if (marks > total) {
                alert("Marks Obtained cannot exceed Maximum Marks.");
                $(marksId).val(total);
                marks = total;
            }
            if (marks < 0) {
                alert("Marks Obtained cannot be negative.");
                $(marksId).val(0);
                marks = 0;
            }

            if (total === 0) {
                $(percId).val("0.00");
                return 0;
            }
            let percentage = (marks / total) * 100;
            $(percId).val(percentage.toFixed(2));
            return marks; 
        }
        function calculateTotal() {
            let mathsMarks     = calculatePercentage("#maths_marks", "#total_maths", "#maths_perc");
            let physicsMarks   = calculatePercentage("#physics_marks", "#total_physics", "#physics_perc");
            let chemistryMarks = calculatePercentage("#chemistry_marks", "#total_chemistry", "#chemistry_perc");
            
            // Calculate Cut Off: (P+C)/2 + M
            // And Average: (P+C+M)/3
            setTimeout(function() {
                let mathsPerc = parseFloat($("#maths_perc").val() || 0);
                let physicsPerc = parseFloat($("#physics_perc").val() || 0);
                let chemistryPerc = parseFloat($("#chemistry_perc").val() || 0);
                
                // Cut Off = (P+C)/2 + M
                let PCAvg = (physicsPerc + chemistryPerc) / 2;
                let cutoff = PCAvg + mathsPerc;
                $("#cutoff_marks").val(cutoff.toFixed(2));
                
                // Average = (P+C+M)/3
                let average = (physicsPerc + chemistryPerc + mathsPerc) / 3;
                $("#average_marks").val(average.toFixed(2));
            }, 10);
        }
        $("#maths_marks, #total_maths, #physics_marks, #total_physics, #chemistry_marks, #total_chemistry").on("input", function() {
            calculateTotal();
        });
    });
    $(document).ready(function() {
        function calculateTotalPreSem() {
            let totalMarksObtained = 0;
            let totalMaxMarks = 0;
            for (let i = 1; i <= 6; i++) {
                let markId = "#premark" + i;
                let outId = "#preout" + i;
                let marks = parseFloat($(markId).val());
                let total = parseFloat($(outId).val());

                // Validate total marks (preoutX)
                if (total > 100) {
                    alert("Pre-Final Semester Maximum Marks cannot exceed 100.");
                    $(outId).val(100);
                    total = 100;
                }
                if (total < 0) {
                    alert("Pre-Final Semester Maximum Marks cannot be negative.");
                    $(outId).val(0);
                    total = 0;
                }
                
                // Validate marks obtained (premarkX)
                if (marks > total) {
                    alert("Pre-Final Semester Marks Obtained cannot exceed Maximum Marks.");
                    $(markId).val(total);
                    marks = total;
                }
                if (marks < 0) {
                    alert("Pre-Final Semester Marks Obtained cannot be negative.");
                    $(markId).val(0);
                    marks = 0;
                }

                totalMarksObtained += marks;
                totalMaxMarks += total;
            }
            $("#pretotal").val(totalMarksObtained);
            $("#pretotal1").val(totalMaxMarks);
        }
        $("input[id^='premark'], input[id^='preout']").on("input", function() {
            calculateTotalPreSem();
        });
    });
    $(document).ready(function() {
        function calculateFinalTotalSem() {
            let totalMarksObtained = 0;
            let totalMaxMarks = 0;
            for (let i = 1; i <= 6; i++) {
                let markId = "#finalmark" + i;
                let outId = "#finalout" + i;
                let marks = parseFloat($(markId).val());
                let total = parseFloat($(outId).val());

                // Validate total marks (finaloutX)
                if (total > 100) {
                    alert("Final Semester Maximum Marks cannot exceed 100.");
                    $(outId).val(100);
                    total = 100;
                }
                if (total < 0) {
                    alert("Final Semester Maximum Marks cannot be negative.");
                    $(outId).val(0);
                    total = 0;
                }

                // Validate marks obtained (finalmarkX)
                if (marks > total) {
                    alert("Final Semester Marks Obtained cannot exceed Maximum Marks.");
                    $(markId).val(total);
                    marks = total;
                }
                if (marks < 0) {
                    alert("Final Semester Marks Obtained cannot be negative.");
                    $(markId).val(0);
                    marks = 0;
                }

                totalMarksObtained += marks;
                totalMaxMarks += total;
            }
            $("#finaltotal").val(totalMarksObtained);
            $("#finaltotal1").val(totalMaxMarks);
        }
        $("input[id^='finalmark'], input[id^='finalout']").on("input", function() {
            calculateFinalTotalSem();
        });
    });
</script>
<script>
$(document).ready(function() {
    // Function to clear all previous error messages
    function clearErrors() {
        $('.form-error').remove(); // Remove dynamically added error spans
        $('#email_error, #image_upload_error, #mobile_error, #dob_error, #aadhaar_error').text('');
    }

    // Function to display errors from the server
    function displayErrors(errors) {
        clearErrors();
        $.each(errors, function(key, value) {
            if (value) {
                const field = $('[name="' + key + '"]');
                let errorSpan = '<span class="text-danger form-error">' + value + '</span>';
                
                if (key === 'user_image') {
                    $('#image-upload-area').after(errorSpan);
                } else if (field.length > 0) {
                    field.last().after(errorSpan); // Place error after the field
                }
            }
        });
    }

    // Handler for the main submit button, which now just opens the modal
    $('#submit_application_btn').on('click', function(e) {
        e.preventDefault();
        console.log('Submit Application button clicked.');

        const form = document.getElementById('admission_form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        console.log('Client-side validation passed. Showing payment modal.');
        
        // Clean up any existing modal instance first
        const modalEl = document.getElementById('paymentOptionModal');
        const existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) {
            existingModal.dispose();
        }
        
        // Remove any backdrops
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
        
        // Create fresh modal instance
        const paymentOptionModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        paymentOptionModal.show();
    });

    // Function to handle form submission via AJAX
    function submitForm(paymentOption) {
        console.log('Submitting form with payment option:', paymentOption);
        $('#payment_option').val(paymentOption);
        
        // Close the modal first
        const modalEl = document.getElementById('paymentOptionModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
        
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
        
        var formData = new FormData(document.getElementById('admission_form'));
        const submitBtn = $('#submit_application_btn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');
        
        clearErrors();

        $.ajax({
            url: '<?php echo site_url('publicadmissionform/ajax_add_college_admission'); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.status === 'success') {
                    console.log('Success, redirecting to:', response.redirect_url);
                    window.location.href = response.redirect_url;
                } else if (response.status === 'fail') {
                    console.log('Validation errors:', response.error);
                    displayErrors(response.error);
                    
                    // Show errors in modal
                    let errorHtml = '<ul class="list-unstyled mb-0">';
                    $.each(response.error, function(field, message) {
                        errorHtml += '<li class="mb-2"><i class="bi bi-x-circle-fill text-danger me-2"></i>' + message + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#errorModalContent').html(errorHtml);
                    
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    
                    submitBtn.prop('disabled', false).text('Submit Application');
                } else {
                    console.error('Unknown response:', response);
                    $('#errorModalContent').html('<p><i class="bi bi-x-circle-fill text-danger me-2"></i>An unknown error occurred. Please try again.</p>');
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    submitBtn.prop('disabled', false).text('Submit Application');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response Text:', jqXHR.responseText);
                $('#errorModalContent').html('<p><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>A server error occurred. Please try again later.</p>');
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
                submitBtn.prop('disabled', false).text('Submit Application');
            }
        });
    }

    // Handlers for modal buttons
    $(document).on('click', '#payOnlineBtn', function() { 
        console.log('Pay Online button clicked - event delegated');
        submitForm('pay_online'); 
    });
    $(document).on('click', '#payLaterBtn', function() { 
        console.log('Pay Later button clicked - event delegated');
        submitForm('pay_later'); 
    });

    // Reset state when navigating back to this page (bfcache)
    window.addEventListener('pageshow', function(event) {
        console.log('pageshow event fired, persisted:', event.persisted);
        
        if (event.persisted) {
            // Page was restored from bfcache (back button was used)
            console.log('Resetting form state after back button navigation');
            
            // Re-enable submit button and reset text
            $('#submit_application_btn').prop('disabled', false).text('Submit Application');
            
            // Clear payment option
            $('#payment_option').val('');
            
            // Destroy any existing modal instance and remove backdrops
            const modalEl = document.getElementById('paymentOptionModal');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.dispose(); // Completely destroy the modal instance
                }
            }
            
            // Force remove any modal artifacts
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');
            
            // Clear any error messages
            clearErrors();
        }
    });
    
    // --- Existing client-side validation handlers for instant feedback ---
    $('#student_email').on('change', function() {
        var email = $(this).val(), academic_year = $('#academic_year').val();
        $('#email_error').text('');
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $.post("<?php echo site_url('publicadmissionform/check_admissions_data'); ?>", { email_id: email, academic_year: academic_year }, function(result) {
                if (JSON.parse(result).count > 0) {
                    $('#email_error').text('Email already submitted for this academic year.');
                    $('#student_email').val("");
                }
            });
        } else if (email) { $('#email_error').text('Invalid email format.'); }
    });

    $('#student_mobile').on('change', function() {
        var mobile = $(this).val();
        $('#mobile_error').text('');
        if (/^[0-9]{10}$/.test(mobile)) {
            $.post("<?php echo site_url('publicadmissionform/check_admissions_data'); ?>", { mobile_no: mobile }, function(result) {
                if (JSON.parse(result).total != 1) {
                    $('#mobile_error').text('Mobile number already exists.');
                    $('#student_mobile').val("");
                }
            });
        } else if (mobile) { $('#mobile_error').text('Must be 10 digits.'); }
    });

    $('#aadhaar').on('change', function() {
        var aadhaar = $(this).val();
        $('#aadhaar_error').text('');
        if (/^[0-9]{12}$/.test(aadhaar)) {
            $.post("<?php echo site_url('publicadmissionform/check_admissions_data'); ?>", { aadhaar_no: aadhaar }, function(result) {
                if (JSON.parse(result).total != 1) {
                    $('#aadhaar_error').text('Aadhaar number already exists.');
                    $('#aadhaar').val("");
                }
            });
        } else if (aadhaar) { $('#aadhaar_error').text('Must be 12 digits.'); }
    });

    $('#dob').on('change', function() {
        $('#dob_error').text('');
    });

    // Load India states and cities
    let statesData = {};
    
    $.ajax({
        url: '<?php echo base_url("backend/json-files/india_states_cities.json"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            const stateSelect = document.getElementById('state');
            
            // Sort states alphabetically
            data.states.sort((a, b) => a.name.localeCompare(b.name));
            
            data.states.forEach(function(state) {
                // Sort cities alphabetically
                state.cities.sort((a, b) => a.localeCompare(b));
                statesData[state.name] = state.cities;
                
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });
        }
    });

    // Populate cities when state is selected
    document.getElementById('state').addEventListener('change', function() {
        const selectedState = this.value;
        const citySelect = document.getElementById('city');
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (statesData[selectedState]) {
            statesData[selectedState].forEach(function(city) {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
        const othersOpt = document.createElement('option');
        othersOpt.value = 'Others';
        othersOpt.textContent = 'Others';
        citySelect.appendChild(othersOpt);
        $('#city_other_text').hide().val('').removeAttr('required');
    });

    $('#city').on('change', function() {
        if ($(this).val() === 'Others') {
            $('#city_other_text').show().attr('required', true);
        } else {
            $('#city_other_text').hide().val('').removeAttr('required');
        }
    });

    function copyCommunicationToPermanent() {
        var checkbox = document.getElementById('same_as_comm');
        var commAddr = document.getElementById('comm_addr');
        var permAddr = document.getElementById('perm_addr');
        
        console.log('Checkbox checked:', checkbox.checked);
        console.log('Comm Address:', commAddr.value);
        
        if (checkbox.checked) {
            permAddr.value = commAddr.value;
            permAddr.style.backgroundColor = '#f0f8ff';
            console.log('Copied to Permanent Address:', permAddr.value);
        } else {
            permAddr.value = '';
            permAddr.style.backgroundColor = '';
        }
    }

    // Listen for changes on checkbox
    document.getElementById('same_as_comm').addEventListener('change', function() {
        copyCommunicationToPermanent();
    });

    // Listen for changes on communication address if checkbox is checked
    document.getElementById('comm_addr').addEventListener('input', function() {
        var checkbox = document.getElementById('same_as_comm');
        if (checkbox.checked) {
            document.getElementById('perm_addr').value = this.value;
        }
    });

    // Initialize Flatpickr for DOB picker
    flatpickr('#dob', {
        mode: 'single',
        dateFormat: 'd/m/Y',
        altInput: false,
        maxDate: new Date(),
        yearRange: [1950, new Date().getFullYear()],
        monthSelectorType: 'dropdown',
        allowInput: true,
        enableTime: false,
        time_24hr: false,
        locale: 'en'
    });

    // Initialize Flatpickr for year inputs (tenth_passing and lateral_tenth_passing)
    flatpickr('#tenth_passing', {
        mode: 'single',
        dateFormat: 'Y',
        altInput: false,
        maxDate: new Date(),
        yearRange: [1950, new Date().getFullYear()],
        monthSelectorType: 'dropdown',
        allowInput: false,
        enableTime: false,
        locale: 'en'
    });

    flatpickr('#lateral_tenth_passing', {
        mode: 'single',
        dateFormat: 'Y',
        altInput: false,
        maxDate: new Date(),
        yearRange: [1950, new Date().getFullYear()],
        monthSelectorType: 'dropdown',
        allowInput: false,
        enableTime: false,
        locale: 'en'
    });
});
</script>
</html>