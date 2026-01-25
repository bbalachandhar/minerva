<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form - Meenakshi College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
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
            <form action="<?php echo site_url('publicadmissionform/add_college_admission'); ?>" method="POST" enctype="multipart/form-data">
                <div class="section-card">
                    <h5 class="text-center mb-4">APPLICATION FORM FOR ADMISSION</h5>
                    <div class="d-flex justify-content-center mb-4">
                        <div class="form-check mx-3">
                            <input class="form-check-input" type="radio" name="courseLevel" id="ugRadio" value="ug" checked tabindex="1">
                            <label class="form-check-label" for="ugRadio">Undergraduate (UG)</label>
                        </div>
                        <div class="form-check mx-3">
                            <input class="form-check-input" type="radio" name="courseLevel" id="lateralRadio" value="lateral" tabindex="2">
                            <label class="form-check-label" for="lateralRadio">Lateral Entry</label>
                        </div>
                        <div class="form-check mx-3">
                            <input class="form-check-input" type="radio" name="courseLevel" id="pgRadio" value="pg" tabindex="3">
                            <label class="form-check-label" for="pgRadio">Postgraduate (PG)</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 cus_form">
                            <div class="">
                                <label class="form-label">Academic Year:</label>
                                <input type="text" class="form-control" name="academic_year" id="academic_year" value="2026-2027" readonly tabindex="-1">
                            </div>
                            <div class="Upload_pic">
                                <label class="form-label">Upload Your Photo (Passport Size) *:</label>
                                <div id="image-upload-area" class="border rounded d-flex flex-column align-items-center justify-content-center p-3 bg-light"style="height: 200px; cursor: pointer; position: relative; overflow: hidden;">
                                    <input type="file" id="imageUpload" name="user_image" accept="image/*" required tabindex="4" style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;">
                                    <img id="previewImage" src="" alt="Preview" class="d-none border"
                                    style="width: 35mm; height: 45mm; object-fit: cover; border-radius: 5px;">
                                    <i id="uploadIcon" class="bi bi-cloud-upload-fill text-primary" style="font-size: 30px;"></i>
                                    <p id="uploadText" class="mb-0 text-muted">Drag & Drop or Click to Upload</p>
                                    <small id="uploadNote" class="text-muted">Max size: 300KB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section-card">
                    <h5 class="mb-2">PERSONAL DETAILS</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name (In block letters with initial at the end)*</label>
                                <input type="text" class="form-control" name="user_name" id="user_name" onkeydown="return allowAlphabets(event)" placeholder="Enter your full name" required tabindex="5">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Father's Name*</label>
                            <input type="text" class="form-control" placeholder="Enter your Father's Name" name="father_name" onkeydown="return allowAlphabets(event)" id="father_name" required tabindex="6">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Father's Mobile Number*</label>
                            <input type="text" class="form-control" minlength="10" maxlength="10" placeholder="Enter your Father's Mobile Number"  onchange="validateMobile(this)" name="father_mobile" id="father_mobile"  onKeyPress="return checkIt(event);" required tabindex="7">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Father's Occupation*</label>
                            <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter your Father's Occupation"   name="father_occupation" id="father_occupation" required tabindex="8">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter your Mother's Name" name="mother_name" id="mother_name" required tabindex="9">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Mother's Mobile Number*</label>
                            <input type="text" class="form-control" placeholder="Enter your Mother's Mobile Number" name="mother_mobile" id="mother_mobile"  onchange="validateMobile(this)" required minlength="10" maxlength="10" onKeyPress="return checkIt(event);" tabindex="10">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Mother's Occupation*</label>
                            <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter your Mother's Occupation" name="mother_occupation" id="mother_occupation" required tabindex="11">
                        </div>
                        <div class="col-md-6">
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
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Email ID*</label>
                            <input type="email" class="form-control" placeholder="Enter your Email"  id="student_email" name="student_email" required tabindex="13">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Student's Mobile Number*</label>
                            <input type="text" step="any" class="form-control" placeholder="Enter Student's Mobile Number" id="student_mobile" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" name="student_mobile" required minlength="10" maxlength="10" tabindex="14">
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">D.O.B*</label>
                            <input type="date" class="form-control" placeholder="Enter your D.O.B"  id="dob" name="dob" required tabindex="15">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Aadhaar Number*</label>
                            <input type="text" step="any" class="form-control" placeholder="Enter your Aadhar Number" id="aadhaar" name="aadhaar" required minlength="12" maxlength="12" onKeyPress="return checkIt(event);" tabindex="16">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Address for Communication*</label>
                                <textarea class="form-control" placeholder="Enter your Communication Address"  name="comm_addr" id="comm_addr" required tabindex="17"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Permanent Address*</label>
                                <textarea class="form-control" placeholder="Enter your Permanent Address" name="perm_addr" id="perm_addr" required tabindex="18"></textarea>
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
                <div class="">

                    <div id="ugDetails">
                        <div class="section-card">
                            <h5 class="mb-3">Courses Offered*</h5>
                                                        <select class="form-control" name="ug_course" id="ug_course" tabindex="22">
                                                            <option value="">Select a Course</option>
                                                                                                <option value="1">B.Arch - Bachelor of Architecture</option>
                                                                                                <option value="2">B.E. CIVIL - Civil Engineering</option>
                                                                                                <option value="3">B.E. CSE - Computer Science Engineering</option>
                                                                                                <option value="4">B.E. CSE(AIML) - CSE(Artificial Intelligence & Machine Learning)</option>
                                                                                                <option value="5">B.E. EEE - Electrical and Electronics Engineering</option>
                                                                                                <option value="6">B.E. ECE - Electronics and Communication Engineering</option>
                                                                                                <option value="7">B.E. EIE - Electronics and Instrumentation Engineering</option>
                                                                                                <option value="8">B.E. MECH - Mechanical Engineering</option>
                                                                                                <option value="9">B.TECH. AIDS - Artificial Intelligence and Data Science</option>
                                                                                                <option value="10">B.TECH. CSBS - Computer Science and Business System</option>
                                                                                                <option value="11">B.TECH. IT - Information Technology </option>
                                                                                                <option value="12">B.E. Cybersecurity and Bachelor of Design (B.Des)</option>
                                                        </select>                        </div>
                        <div class="section-card">
                            <div class="mb-3">
                                <label class="form-label">Name of the school of X std*</label>
                                <input type="text" class="form-control" placeholder="Enter your year"  name="school_name" id="school_name" onkeydown="return allowAlphabets(event)" tabindex="23">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year of passing of X std*</label>
                                <input  type="text" class="form-control" placeholder="Enter your year" name="tenth_passing" id="tenth_passing" minlength="4" maxlength="4" onKeyPress="return checkIt(event);" tabindex="24">
                            </div>
                        </div>
                        <div class="section-card">
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
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_maths" id="total_maths" onKeyPress="return checkIt(event);" tabindex="25"></td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="maths_marks" id="maths_marks" onKeyPress="return checkIt(event);" tabindex="26"></td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="maths_perc" id="maths_perc" readonly tabindex="-1"></td>
                                        </tr>
                                        <tr>
                                            <td>Physics (P) & Chemistry (C) put together</td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_physics" id="total_physics" onKeyPress="return checkIt(event);" tabindex="27"></td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="physics_marks" id="physics_marks" onKeyPress="return checkIt(event);" tabindex="28"></td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="physics_perc" id="physics_perc" readonly tabindex="-1"></td>
                                        </tr>
                                        <tr>
                                            <td>Total</td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_marks" id="total_marks" readonly tabindex="-1"></td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="obtain_marks" id="obtain_marks" readonly tabindex="-1"></td>
                                            <td><input type="number" step="1" min="0" max="100" value="0" class="form-control text-center" name="total_perc" id="total_perc" readonly tabindex="-1"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div id="lateralDetails" style="display:none">
                        <div class="section-card">
                            <h5 class="mb-3">Courses Offered*</h5>
                            <select class="form-control" name="lateral_course" id="lateral_course" tabindex="29">
                                <option value="">Select a Course</option>
                                                                    <option value="2">B.E. CIVIL - Civil Engineering</option>
                                                                    <option value="3">B.E. CSE - Computer Science Engineering</option>
                                                                    <option value="4">B.E. CSE(AIML) - CSE(Artificial Intelligence & Machine Learning)</option>
                                                                    <option value="5">B.E. EEE - Electrical and Electronics Engineering</option>
                                                                    <option value="6">B.E. ECE - Electronics and Communication Engineering</option>
                                                                    <option value="7">B.E. EIE - Electronics and Instrumentation Engineering</option>
                                                                    <option value="8">B.E. MECH - Mechanical Engineering</option>
                                                                    <option value="9">B.TECH. AIDS - Artificial Intelligence and Data Science</option>
                                                                    <option value="11">B.TECH. IT - Information Technology </option>
                                                                    <option value="12">B.E. Cybersecurity and Bachelor of Design (B.Des)</option>
                                                            </select>
                        </div>
                        <div class="section-card">
                            <div class="mb-3">
                                <label class="form-label">Name of the school of X std*</label>
                                <input type="text" class="form-control" placeholder="Enter your year"  name="lateral_school_name" id="lateral_school_name" onkeydown="return allowAlphabets(event)" tabindex="30">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year of passing of X std*</label>
                                <input  type="text" class="form-control" placeholder="Enter your year" name="lateral_tenth_passing" id="lateral_tenth_passing" minlength="4" maxlength="4" onKeyPress="return checkIt(event);" tabindex="31">
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
                            <h5 class="mb-2">NATA (for B.Arch only)</h5>
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
                            <h5 class="mb-3">Courses Offered</h5>
                            <select class="form-control" name="pg_course" id="pg_course" tabindex="71">
                                <option value="">Select a Course</option>
                                                                    <option value="12">M. Arch. - Master of Architecture</option>
                                                                    <option value="13">M.B.A - Master of Business Administration</option>
                                                                    <option value="14">M.C.A - Master of Computer Application</option>
                                                                    <option value="15">M.E. AE - Applied Electronics</option>
                                                                    <option value="16">M.E. CSE - Computer Science and Engineering</option>
                                                                    <option value="17">M.E. PED - Power Electronics and Drives</option>
                                                            </select>
                        </div>
                        <div class="section-card">
                            <h5 class="mb-3">Academic  Details</h5>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Qualifying Exam passed</label>
                                    <input class="form-control" type="text" placeholder="Enter your exam passed" name="exam_passed" id="exam_passed" tabindex="72">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Branch</label>
                                    <input type="text" class="form-control" placeholder="Enter your Branch" name="branch" id="branch" tabindex="73">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Year of Passing</label>
                                    <input type="text" class="form-control" placeholder="Enter your Year" onKeyPress="return checkIt(event);" name="yop" id="yop" tabindex="74">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Name of the College</label>
                                    <input type="text" class="form-control" minlength="4" maxlength="4" placeholder="Enter your College" name="noc" id="noc" tabindex="75">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">Name of the University</label>
                                    <input type="texts" step="any" class="form-control" placeholder="Enter your University" name="nou" id="nou" tabindex="76">
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
                <button class="btn btn-submit mt-3" type="Submit" name="submit" tabindex="89">Submit Application</button>
            </form>
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
    });

    function toggleCourseSelection() {
        const ugRadio = document.getElementById("ugRadio");
        const lateralRadio = document.getElementById("lateralRadio");
        const pgRadio = document.getElementById("pgRadio");
        
        const ugDetails = document.getElementById("ugDetails");
        const lateralDetails = document.getElementById("lateralDetails");
        const pgDetails = document.getElementById("pgDetails");
        
        if(ugRadio.checked) {
            // Show UG details, hide others
            ugDetails.style.display = "block";
            lateralDetails.style.display = "none";
            pgDetails.style.display = "none";
            
            // Set required fields for UG
            setRequiredFields(true, false, false);
        } 
        else if(lateralRadio.checked) {
            // Show Lateral details, hide others
            ugDetails.style.display = "none";
            lateralDetails.style.display = "block";
            pgDetails.style.display = "none";
            
            // Set required fields for Lateral
            setRequiredFields(false, true, false);
        }
        else if(pgRadio.checked) {
            // Show PG details, hide others
            ugDetails.style.display = "none";
            lateralDetails.style.display = "none";
            pgDetails.style.display = "block";
            
            // Set required fields for PG
            setRequiredFields(false, false, true);
        }
    }

    function setRequiredFields(ugRequired, lateralRequired, pgRequired) {
        // UG fields
        $("#ug_course").prop("required", ugRequired);
        $("#school_name").prop("required", ugRequired);
        $("#tenth_passing").prop("required", ugRequired);
        $("#maths_marks").prop("required", ugRequired);
        $("#total_maths").prop("required", ugRequired);
        $("#physics_marks").prop("required", ugRequired);
        $("#total_physics").prop("required", ugRequired);
        
        // Lateral fields
        $("#lateral_course").prop("required", lateralRequired);
        $("#lateral_school_name").prop("required", lateralRequired);
        $("#lateral_tenth_passing").prop("required", lateralRequired);
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
        $("#nou").prop("required", pgRequired);
        $("#exam_score").prop("required", pgRequired);
        $("#exam_year").prop("required", pgRequired);
        $("#pg_app_num").prop("required", pgRequired);
    }

    // Add event listeners to all radio buttons
    document.getElementById("ugRadio").addEventListener("change", toggleCourseSelection);
    document.getElementById("lateralRadio").addEventListener("change", toggleCourseSelection);
    document.getElementById("pgRadio").addEventListener("change", toggleCourseSelection);

    // NATA section toggle for B.Arch
    $("#ug_course, #lateral_course").change(function(){
        let course_val = $(this).val();
        if(course_val==1){ // Assuming 1 is B.Arch course ID
            $("#nata_sec").show();
            $("#nata_score").prop('required', true);
            $("#application_number").prop('required', true);
            $("#nata_year").prop('required', true);
        }else{
            $("#nata_sec").hide();
            $("#nata_score").prop('required', false);
            $("#application_number").prop('required', false);
            $("#nata_year").prop('required', false);
        }
    });
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
            let mathsMarks   = calculatePercentage("#maths_marks", "#total_maths", "#maths_perc");
            let physicsMarks = calculatePercentage("#physics_marks", "#total_physics", "#physics_perc");
            let totalMarksObtained = mathsMarks + physicsMarks;
            let totalMarks = parseFloat($("#total_maths").val() || 0) + parseFloat($("#total_physics").val() || 0);
            $("#obtain_marks").val(totalMarksObtained);
            $("#total_marks").val(totalMarks);
            if (totalMarks > 0) {
                let totalPercentage = (totalMarksObtained / totalMarks) * 100;
                $("#total_perc").val(totalPercentage.toFixed(2));
            } else {
                $("#total_perc").val("0.00");
            }
        }
        $("#maths_marks, #total_maths, #physics_marks, #total_physics").on("input", function() {
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
        $('#student_email').on('change', function() {
            var email = $(this).val();
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (regex.test(email) && email!='') {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('publicadmissionform/check_admissions_data'); ?>",
                    data: {
                        email_id: email,
                    },
                    dataType: "text",
                    success: function(result) {
                        var res = JSON.parse(result);
                        if (res.total != 1) {
                            alert('Email-ID already exists!');
                            $('#student_email').val("");
                        }
                    }
                });
            }else{
                alert('Invalid Email-ID');
                $('#student_email').val("");
            }
        });
    });
    $(document).ready(function() {
        $('#student_mobile').on('change', function() {
            var mobile = $(this).val();
            const regex = /^[0-9]{10}$/;
            if (regex.test(mobile) && mobile!='') {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('publicadmissionform/check_admissions_data'); ?>",
                    data: {
                        mobile_no: mobile,
                    },
                    dataType: "text",
                    success: function(result) {
                        var res = JSON.parse(result);
                        if (res.total != 1) {
                            alert('Mobile Number already exists!');
                            $('#student_mobile').val("");
                        }
                    }
                });
            }else{
                alert('Invalid Mobile Number');
                $('#student_mobile').val("");
            }
        });
    });
    $(document).ready(function() {
        $('#dob').on('change', function() {
            const dob = new Date($(this).val());
            const today = new Date();
            const minAgeDate = new Date();
            minAgeDate.setFullYear(today.getFullYear() - 17);
            if (dob >= minAgeDate) {
                alert('Invalid Date of Birth')
                $(this).val('');
            }
        });
    });
    $(document).ready(function() {
        $('#aadhaar').on('change', function() {
            var aadhaar = $(this).val();
            const regex = /^[0-9]{12}$/;
            if (regex.test(aadhaar) && aadhaar!='') {
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('publicadmissionform/check_admissions_data'); ?>",
                    data: {
                        aadhaar_no: aadhaar,
                    },
                    dataType: "text",
                    success: function(result) {
                        var res = JSON.parse(result);
                        if (res.total != 1) {
                            alert('Aadhaar Number already exists!');
                            $('#aadhaar').val("");
                        }
                    }
                });
            }else{
                alert('Invalid Aadhaar Number');
                $('#aadhaar').val("");
            }
        });
    });
</script>
</html>