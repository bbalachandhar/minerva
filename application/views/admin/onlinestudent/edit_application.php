<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Initialize variables to prevent undefined warnings
$ug_details = isset($ug_details) && !empty($ug_details) ? $ug_details : array();
$reference_details = isset($reference_details) && !empty($reference_details) ? $reference_details : array();
$current_gender = strtolower(trim(isset($student['gender']) ? $student['gender'] : ''));
$current_community = isset($student['cast']) ? $student['cast'] : '';
$form_status = ($student['form_status'] == 1) ? 'Submitted' : 'Draft';
$form_status_class = ($student['form_status'] == 1) ? 'ea-badge--success' : 'ea-badge--draft';
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
:root {
    --ea-primary: #4361ee;
    --ea-primary-light: #eef1ff;
    --ea-dark: #1e293b;
    --ea-text: #334155;
    --ea-text-light: #64748b;
    --ea-border: #e2e8f0;
    --ea-bg: #f1f5f9;
    --ea-white: #ffffff;
    --ea-danger: #ef4444;
    --ea-success: #10b981;
    --ea-warning: #f59e0b;
    --ea-radius: 10px;
    --ea-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --ea-shadow-md: 0 4px 12px rgba(0,0,0,0.07);
}

.ea-page { background: var(--ea-bg); padding: 24px; min-height: 100vh; }
.ea-page .content-header { display: none; }

/* ── Top Bar ── */
.ea-topbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.ea-topbar__left { display: flex; align-items: center; gap: 16px; }
.ea-topbar__title { font-size: 22px; font-weight: 700; color: var(--ea-dark); margin: 0; letter-spacing: -0.3px; }
.ea-topbar__ref { font-size: 13px; color: var(--ea-text-light); font-weight: 500; }
.ea-badge {
    display: inline-block; padding: 4px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase;
}
.ea-badge--success { background: #d1fae5; color: #065f46; }
.ea-badge--draft   { background: #fef3c7; color: #92400e; }
.ea-btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
    color: var(--ea-text); background: var(--ea-white); border: 1px solid var(--ea-border);
    text-decoration: none; transition: all 0.15s;
}
.ea-btn-back:hover { background: var(--ea-bg); color: var(--ea-dark); text-decoration: none; border-color: #cbd5e1; }

/* ── Section Card ── */
.ea-card {
    background: var(--ea-white); border-radius: var(--ea-radius);
    box-shadow: var(--ea-shadow); margin-bottom: 20px; border: 1px solid var(--ea-border);
    overflow: hidden;
}
.ea-card__header {
    display: flex; align-items: center; gap: 10px;
    padding: 16px 24px; border-bottom: 1px solid var(--ea-border); background: #fafbfc;
}
.ea-card__icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
}
.ea-card__icon--blue    { background: #dbeafe; color: #2563eb; }
.ea-card__icon--purple  { background: #ede9fe; color: #7c3aed; }
.ea-card__icon--green   { background: #d1fae5; color: #059669; }
.ea-card__icon--orange  { background: #ffedd5; color: #ea580c; }
.ea-card__icon--pink    { background: #fce7f3; color: #db2777; }
.ea-card__icon--teal    { background: #ccfbf1; color: #0d9488; }
.ea-card__icon--slate   { background: #e2e8f0; color: #475569; }
.ea-card__icon--amber   { background: #fef3c7; color: #d97706; }
.ea-card__title { font-size: 15px; font-weight: 700; color: var(--ea-dark); margin: 0; }
.ea-card__subtitle { font-size: 12px; color: var(--ea-text-light); margin: 0; }
.ea-card__body { padding: 20px 24px; }

/* ── Form Fields ── */
.ea-field { margin-bottom: 18px; }
.ea-field label {
    display: block; font-size: 12px; font-weight: 600; color: var(--ea-text-light);
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
}
.ea-field label .ea-req { color: var(--ea-danger); }
.ea-field .form-control {
    border: 1.5px solid var(--ea-border); border-radius: 8px; padding: 9px 14px;
    font-size: 14px; color: var(--ea-dark); transition: border-color 0.15s, box-shadow 0.15s;
    height: auto; box-shadow: none;
}
.ea-field .form-control:focus {
    border-color: var(--ea-primary); box-shadow: 0 0 0 3px rgba(67,97,238,0.1); outline: none;
}
.ea-field .form-control[readonly] { background: #f8fafc; color: var(--ea-text-light); cursor: default; }
.ea-field textarea.form-control { min-height: 80px; resize: vertical; }
.ea-field .select2-container .select2-choice,
.ea-field .select2-container--default .select2-selection--single {
    border: 1.5px solid var(--ea-border) !important; border-radius: 8px !important;
    height: 40px !important; line-height: 38px !important;
}
.ea-field .text-danger { font-size: 12px; margin-top: 4px; display: block; }

/* ── Read-only pill ── */
.ea-readonly-value {
    background: #f8fafc; border: 1.5px solid var(--ea-border); border-radius: 8px;
    padding: 9px 14px; font-size: 14px; color: var(--ea-text-light); font-weight: 500;
}

/* ── Photo Upload ── */
.ea-photo-upload {
    display: flex; align-items: center; gap: 16px;
    padding: 12px 16px; border: 2px dashed var(--ea-border); border-radius: 10px;
    background: #fafbfc; transition: border-color 0.15s; cursor: pointer; position: relative;
}
.ea-photo-upload:hover { border-color: var(--ea-primary); background: var(--ea-primary-light); }
.ea-photo-upload__icon {
    width: 48px; height: 48px; border-radius: 50%; background: var(--ea-primary-light);
    display: flex; align-items: center; justify-content: center; color: var(--ea-primary);
    font-size: 20px; flex-shrink: 0;
}
.ea-photo-upload__text { font-size: 13px; color: var(--ea-text-light); line-height: 1.5; }
.ea-photo-upload__text strong { color: var(--ea-primary); font-weight: 600; }
.ea-photo-upload input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}

/* ── HSC Table ── */
.ea-marks-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.ea-marks-table th {
    padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.5px; color: var(--ea-text-light); background: #f8fafc;
    border-bottom: 2px solid var(--ea-border);
}
.ea-marks-table td { padding: 10px 14px; border-bottom: 1px solid var(--ea-border); vertical-align: middle; }
.ea-marks-table tr:last-child td { border-bottom: none; }
.ea-marks-table .ea-subject {
    font-weight: 600; color: var(--ea-dark); font-size: 13px; white-space: nowrap;
}
.ea-marks-table .form-control {
    border: 1.5px solid var(--ea-border); border-radius: 6px; padding: 7px 10px;
    text-align: center; font-size: 14px; font-weight: 500; width: 100%;
    box-shadow: none; height: auto;
}
.ea-marks-table .form-control:focus { border-color: var(--ea-primary); box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.ea-marks-table .form-control[readonly] { background: #f8fafc; color: var(--ea-text-light); font-weight: 600; }
.ea-marks-table .ea-row-highlight { background: #f8fafc; }
.ea-marks-table .ea-avg-cell {
    font-size: 18px; font-weight: 700; color: var(--ea-primary); text-align: center;
}

/* ── Footer Actions ── */
.ea-actions {
    display: flex; align-items: center; gap: 12px; padding: 20px 24px;
    background: #fafbfc; border-top: 1px solid var(--ea-border); border-radius: 0 0 var(--ea-radius) var(--ea-radius);
}
.ea-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;
    border: none; cursor: pointer; transition: all 0.15s; text-decoration: none;
}
.ea-btn--primary { background: var(--ea-primary); color: #fff; }
.ea-btn--primary:hover { background: #3451d1; color: #fff; text-decoration: none; box-shadow: var(--ea-shadow-md); }
.ea-btn--ghost {
    background: transparent; color: var(--ea-text-light); border: 1px solid var(--ea-border);
}
.ea-btn--ghost:hover { background: var(--ea-bg); color: var(--ea-text); text-decoration: none; }

/* ── Follow-up Panel ── */
.ea-followup-form { display: grid; grid-template-columns: 1fr 200px 120px; gap: 12px; align-items: end; }
@media (max-width: 768px) { .ea-followup-form { grid-template-columns: 1fr; } }

/* ── Scholarship Table ── */
.ea-sch-table { width: 100%; border-collapse: collapse; }
.ea-sch-table th {
    padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.5px; color: var(--ea-text-light); background: #f8fafc;
    border-bottom: 2px solid var(--ea-border); text-align: left;
}
.ea-sch-table td { padding: 12px 14px; border-bottom: 1px solid var(--ea-border); font-size: 14px; }
.ea-sch-table tr:last-child td { border-bottom: none; }

/* ── Two-column grid ── */
.ea-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; }
.ea-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 24px; }
.ea-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0 24px; }
@media (max-width: 992px) { .ea-grid-3, .ea-grid-4 { grid-template-columns: 1fr 1fr; } }
@media (max-width: 768px) { .ea-grid-2, .ea-grid-3, .ea-grid-4 { grid-template-columns: 1fr; } }

/* ── Flash message ── */
.ea-flash { margin-bottom: 20px; }

/* ── Utility ── */
.ea-colspan-full { grid-column: 1 / -1; }
.ea-divider { height: 1px; background: var(--ea-border); margin: 4px 0 18px; grid-column: 1 / -1; }

/* Override AdminLTE content-wrapper bg */
.ea-page.content-wrapper { background: var(--ea-bg) !important; }
</style>

<div class="content-wrapper ea-page">
    <section class="content" style="max-width: 960px; margin: 0 auto;">

        <!-- Top Bar -->
        <div class="ea-topbar">
            <div class="ea-topbar__left">
                <div>
                    <h1 class="ea-topbar__title">Edit Application</h1>
                    <span class="ea-topbar__ref">#<?php echo $student['reference_no']; ?></span>
                </div>
                <span class="ea-badge <?php echo $form_status_class; ?>"><?php echo $form_status; ?></span>
            </div>
            <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="ea-btn-back">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>

        <?php if ($this->session->flashdata('msg')) { ?>
            <div class="ea-flash"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php } ?>

        <form action="<?php echo site_url("admin/onlinestudent/edit_application/" . $id) ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <?php echo $this->customlib->getCSRF(); ?>

            <!-- ━━ Application Details ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--blue"><i class="fa fa-file-text-o"></i></div>
                    <div>
                        <h3 class="ea-card__title">Application Details</h3>
                        <p class="ea-card__subtitle">Course and admission type</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>Application Ref No</label>
                            <div class="ea-readonly-value"><?php echo $student['reference_no']; ?></div>
                        </div>
                        <div class="ea-field">
                            <label>Form Status</label>
                            <div class="ea-readonly-value"><?php echo $form_status; ?></div>
                        </div>
                        <div class="ea-field">
                            <label>Course Applied</label>
                            <select class="form-control select2-course" name="admission_course_id" id="admission_course_id" style="width:100%;">
                                <option value="">-- Select Course --</option>
                                <?php if (!empty($all_courses)) { foreach ($all_courses as $course) {
                                    $type_raw   = strtolower($course['admission_type'] ?? '');
                                    $type_label = ($type_raw === 'lateral') ? 'Lateral Entry' : 'First Year';
                                    $display    = htmlspecialchars($course['course_name']) . ' (' . $type_label . ')';
                                ?>
                                <option value="<?php echo (int)$course['id']; ?>"
                                    data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>"
                                    <?php echo (isset($selected_course_id) && (int)$selected_course_id === (int)$course['id']) ? 'selected' : ''; ?>>
                                    <?php echo $display; ?>
                                </option>
                                <?php } } ?>
                            </select>
                        </div>
                        <div class="ea-field">
                            <label>Quota Type</label>
                            <select class="form-control" name="quota_type" id="quota_type">
                                <option value="">-- Select --</option>
                                <?php $current_quota = isset($student['quota_type']) ? $student['quota_type'] : ''; ?>
                                <option value="government" <?php echo ($current_quota === 'government') ? 'selected' : ''; ?>>Government</option>
                                <option value="management" <?php echo ($current_quota === 'management') ? 'selected' : ''; ?>>Management</option>
                            </select>
                        </div>
                        <div class="ea-field">
                            <label>Admission Type</label>
                            <select class="form-control" name="admission_type" id="admission_type">
                                <option value="">-- Select --</option>
                                <?php $current_at = isset($student['admission_type']) ? $student['admission_type'] : ''; ?>
                                <option value="first_year" <?php echo ($current_at === 'first_year') ? 'selected' : ''; ?>>First Year</option>
                                <option value="lateral" <?php echo ($current_at === 'lateral') ? 'selected' : ''; ?>>Lateral</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Personal Information ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--purple"><i class="fa fa-user"></i></div>
                    <div>
                        <h3 class="ea-card__title">Personal Information</h3>
                        <p class="ea-card__subtitle">Student details and identity</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <!-- Photo Upload -->
                    <div class="ea-field" style="max-width: 400px;">
                        <label>Applicant Photo</label>
                        <div class="ea-photo-upload">
                            <div class="ea-photo-upload__icon"><i class="fa fa-camera"></i></div>
                            <div class="ea-photo-upload__text">
                                <strong>Click to upload</strong> or drag and drop<br>
                                JPG, PNG &middot; Max 300KB &middot; 100&times;100px
                            </div>
                            <input type="file" name="applicant_photo" id="applicant_photo" accept="image/*">
                        </div>
                        <span class="text-danger"><?php echo form_error('applicant_photo'); ?></span>
                    </div>

                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>Full Name <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Enter full name" value="<?php echo set_value('user_name', $student['firstname']); ?>" required>
                            <span class="text-danger"><?php echo form_error('user_name'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Gender <span class="ea-req">*</span></label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male" <?php echo set_select('gender', 'Male', $current_gender == 'male'); ?>>Male</option>
                                <option value="Female" <?php echo set_select('gender', 'Female', $current_gender == 'female'); ?>>Female</option>
                                <option value="Other" <?php echo set_select('gender', 'Other', $current_gender == 'other'); ?>>Other</option>
                            </select>
                            <span class="text-danger"><?php echo form_error('gender'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Community</label>
                            <select class="form-control" id="community" name="community">
                                <option value="">Select</option>
                                <option value="OC" <?php echo set_select('community', 'OC', $current_community == 'OC'); ?>>OC (General)</option>
                                <option value="BC" <?php echo set_select('community', 'BC', $current_community == 'BC'); ?>>BC</option>
                                <option value="MBC" <?php echo set_select('community', 'MBC', $current_community == 'MBC'); ?>>MBC</option>
                                <option value="BCM" <?php echo set_select('community', 'BCM', $current_community == 'BCM'); ?>>BCM</option>
                                <option value="SC" <?php echo set_select('community', 'SC', $current_community == 'SC'); ?>>SC</option>
                                <option value="SCA" <?php echo set_select('community', 'SCA', $current_community == 'SCA'); ?>>SCA</option>
                                <option value="ST" <?php echo set_select('community', 'ST', $current_community == 'ST'); ?>>ST</option>
                            </select>
                            <span class="text-danger"><?php echo form_error('community'); ?></span>
                        </div>
                    </div>
                    <div class="ea-grid-2">
                        <div class="ea-field">
                            <label>Date of Birth <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="dob" name="dob" value="<?php echo set_value('dob', $student['dob']); ?>" required>
                            <span class="text-danger"><?php echo form_error('dob'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Aadhaar Number <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="aadhaar" name="aadhaar" placeholder="12-digit number" minlength="12" maxlength="12" value="<?php echo set_value('aadhaar', $student['adhar_no']); ?>" onKeyPress="return checkIt(event);" required>
                            <span class="text-danger"><?php echo form_error('aadhaar'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Contact Information ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--green"><i class="fa fa-phone"></i></div>
                    <div>
                        <h3 class="ea-card__title">Contact Information</h3>
                        <p class="ea-card__subtitle">Email and mobile number</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-2">
                        <div class="ea-field">
                            <label>Email <span class="ea-req">*</span></label>
                            <input type="email" class="form-control" id="student_email" name="student_email" placeholder="Enter email" value="<?php echo set_value('student_email', $student['email']); ?>" required>
                            <span class="text-danger"><?php echo form_error('student_email'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Student Mobile <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="student_mobile" name="student_mobile" placeholder="10-digit number" minlength="10" maxlength="10" value="<?php echo set_value('student_mobile', $student['mobileno']); ?>" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" required>
                            <span class="text-danger"><?php echo form_error('student_mobile'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Family Information ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--orange"><i class="fa fa-users"></i></div>
                    <div>
                        <h3 class="ea-card__title">Family Information</h3>
                        <p class="ea-card__subtitle">Parent / guardian details</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>Father's Name <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Enter name" value="<?php echo set_value('father_name', $student['father_name']); ?>" onkeydown="return allowAlphabets(event);" required>
                            <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Father's Mobile <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="father_mobile" name="father_mobile" placeholder="10-digit number" minlength="10" maxlength="10" value="<?php echo set_value('father_mobile', $student['father_phone']); ?>" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" required>
                            <span class="text-danger"><?php echo form_error('father_mobile'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Father's Occupation</label>
                            <input type="text" class="form-control" id="father_occupation" name="father_occupation" placeholder="Enter occupation" value="<?php echo set_value('father_occupation', $student['father_occupation']); ?>" onkeydown="return allowAlphabets(event);">
                        </div>
                    </div>
                    <div class="ea-divider"></div>
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>Mother's Name <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="mother_name" name="mother_name" placeholder="Enter name" value="<?php echo set_value('mother_name', $student['mother_name']); ?>" onkeydown="return allowAlphabets(event);" required>
                            <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Mother's Mobile <span class="ea-req">*</span></label>
                            <input type="text" class="form-control" id="mother_mobile" name="mother_mobile" placeholder="10-digit number" minlength="10" maxlength="10" value="<?php echo set_value('mother_mobile', $student['mother_phone']); ?>" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" required>
                            <span class="text-danger"><?php echo form_error('mother_mobile'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label>Mother's Occupation</label>
                            <input type="text" class="form-control" id="mother_occupation" name="mother_occupation" placeholder="Enter occupation" value="<?php echo set_value('mother_occupation', $student['mother_occupation']); ?>" onkeydown="return allowAlphabets(event);">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Address ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--teal"><i class="fa fa-map-marker"></i></div>
                    <div>
                        <h3 class="ea-card__title">Address</h3>
                        <p class="ea-card__subtitle">Current and permanent address</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-2">
                        <div class="ea-field">
                            <label>Current Address</label>
                            <textarea class="form-control" id="current_address" name="current_address" rows="3" placeholder="Enter current address"><?php echo set_value('current_address', $student['current_address']); ?></textarea>
                        </div>
                        <div class="ea-field">
                            <label>Permanent Address</label>
                            <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3" placeholder="Enter permanent address"><?php echo set_value('permanent_address', $student['permanent_address']); ?></textarea>
                        </div>
                        <div class="ea-field">
                            <label>State</label>
                            <input type="text" class="form-control" name="state" placeholder="Enter state" value="<?php echo set_value('state', $student['state']); ?>">
                        </div>
                        <div class="ea-field">
                            <label>City</label>
                            <input type="text" class="form-control" name="city" placeholder="Enter city" value="<?php echo set_value('city', $student['city']); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Reference Details ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--slate"><i class="fa fa-link"></i></div>
                    <div>
                        <h3 class="ea-card__title">Reference Details</h3>
                        <p class="ea-card__subtitle">Optional referrer information</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>Referrer Name</label>
                            <input type="text" class="form-control" id="referral_name" name="referral_name" placeholder="Enter name" value="<?php echo set_value('referral_name', (isset($reference_details['referrer_name']) ? $reference_details['referrer_name'] : '')); ?>" onkeydown="return allowAlphabets(event);">
                        </div>
                        <div class="ea-field">
                            <label>Relationship</label>
                            <input type="text" class="form-control" id="relationship" name="relationship" placeholder="Enter relationship" value="<?php echo set_value('relationship', (isset($reference_details['relationship']) ? $reference_details['relationship'] : '')); ?>" onkeydown="return allowAlphabets(event);">
                        </div>
                        <div class="ea-field">
                            <label>Phone No</label>
                            <input type="text" class="form-control" id="phone_no" name="phone_no" placeholder="10-digit number" minlength="10" maxlength="10" value="<?php echo set_value('phone_no', (isset($reference_details['phone_no']) ? $reference_details['phone_no'] : '')); ?>" onKeyPress="return checkIt(event);">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ HSC Examination Details ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--pink"><i class="fa fa-graduation-cap"></i></div>
                    <div>
                        <h3 class="ea-card__title">HSC Examination Details</h3>
                        <p class="ea-card__subtitle">Higher secondary marks and cut-off</p>
                    </div>
                </div>
                <div class="ea-card__body" style="padding: 0;">
                    <div style="overflow-x: auto;">
                        <table class="ea-marks-table">
                            <thead>
                                <tr>
                                    <th style="width: 160px;">Subject</th>
                                    <th>Total Marks</th>
                                    <th>Marks Obtained</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ea-subject">Maths (M)</td>
                                    <td><input type="number" step="1" class="form-control" id="total_maths" name="total_maths" value="<?php echo set_value('total_maths', $student['total_maths']); ?>"></td>
                                    <td><input type="number" step="1" class="form-control" id="maths_marks" name="maths_marks" value="<?php echo set_value('maths_marks', $student['maths_marks']); ?>"></td>
                                    <td><input type="number" step="0.01" class="form-control" id="maths_perc" name="maths_perc" value="<?php echo set_value('maths_perc', $student['maths_perc']); ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td class="ea-subject">Physics (P)</td>
                                    <td><input type="number" step="1" class="form-control" id="total_physics" name="total_physics" value="<?php echo set_value('total_physics', $student['total_physics']); ?>"></td>
                                    <td><input type="number" step="1" class="form-control" id="physics_marks" name="physics_marks" value="<?php echo set_value('physics_marks', $student['physics_marks']); ?>"></td>
                                    <td><input type="number" step="0.01" class="form-control" id="physics_perc" name="physics_perc" value="<?php echo set_value('physics_perc', $student['physics_perc']); ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td class="ea-subject">Chemistry (C)</td>
                                    <td><input type="number" step="1" class="form-control" id="total_chemistry" name="total_chemistry" value="<?php echo set_value('total_chemistry', $student['total_chemistry']); ?>"></td>
                                    <td><input type="number" step="1" class="form-control" id="chemistry_marks" name="chemistry_marks" value="<?php echo set_value('chemistry_marks', $student['chemistry_marks']); ?>"></td>
                                    <td><input type="number" step="0.01" class="form-control" id="chemistry_perc" name="chemistry_perc" value="<?php echo set_value('chemistry_perc', $student['chemistry_perc']); ?>" readonly></td>
                                </tr>
                                <tr class="ea-row-highlight">
                                    <td class="ea-subject">Average (P+C+M)/3</td>
                                    <td colspan="3"><div class="ea-avg-cell"><span id="average_display"><?php echo set_value('average_marks', $student['average_marks']); ?></span></div><input type="hidden" id="average_marks" name="average_marks" value="<?php echo set_value('average_marks', $student['average_marks']); ?>"></td>
                                </tr>
                                <tr id="barch_hsc_row" style="display:none;">
                                    <td class="ea-subject">Total (HSC) <span class="ea-req">*</span><br><small style="font-weight:400;color:var(--ea-text-light);">All subjects combined</small></td>
                                    <td><input type="number" step="1" min="1" class="form-control" id="hsc_total_marks" name="hsc_total_marks" value="<?php echo set_value('hsc_total_marks', isset($student['hsc_total_marks']) ? $student['hsc_total_marks'] : ''); ?>"></td>
                                    <td><input type="number" step="1" min="0" class="form-control" id="hsc_marks_obtained" name="hsc_marks_obtained" value="<?php echo set_value('hsc_marks_obtained', isset($student['hsc_marks_obtained']) ? $student['hsc_marks_obtained'] : ''); ?>"></td>
                                    <td></td>
                                </tr>
                                <tr class="ea-row-highlight">
                                    <td class="ea-subject"><span id="cutoff_label_edit">Cut Off: (P+C)/2 + M</span></td>
                                    <td colspan="3"><div class="ea-avg-cell"><span id="cutoff_display"><?php echo set_value('cutoff_marks', $student['cutoff_marks']); ?></span></div><input type="hidden" id="cutoff_marks" name="cutoff_marks" value="<?php echo set_value('cutoff_marks', $student['cutoff_marks']); ?>"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ━━ NATA Details (B.Arch) ━━ -->
            <div class="ea-card" id="barch_nata_section" style="display:none;">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--amber"><i class="fa fa-pencil-square-o"></i></div>
                    <div>
                        <h3 class="ea-card__title">NATA Details</h3>
                        <p class="ea-card__subtitle">B.Arch specific examination scores</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>NATA Score % <span class="ea-req">*</span></label>
                            <input type="number" step="0.01" min="0" max="200" class="form-control" id="nata_score_edit" name="nata_score" placeholder="Enter score" value="<?php echo isset($nata_details['nata_score']) ? htmlspecialchars($nata_details['nata_score']) : ''; ?>">
                        </div>
                        <div class="ea-field">
                            <label>NATA Application No</label>
                            <input type="text" class="form-control" id="nata_application_number" name="nata_application_number" placeholder="Enter application no." value="<?php echo isset($nata_details['application_number']) ? htmlspecialchars($nata_details['application_number']) : ''; ?>">
                        </div>
                        <div class="ea-field">
                            <label>NATA Year</label>
                            <input type="text" class="form-control" id="nata_year" name="nata_year" placeholder="e.g. 2026" value="<?php echo isset($nata_details['nata_year']) ? htmlspecialchars($nata_details['nata_year']) : ''; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Additional Information ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--slate"><i class="fa fa-info-circle"></i></div>
                    <div>
                        <h3 class="ea-card__title">Additional Information</h3>
                        <p class="ea-card__subtitle">Previous education details</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label>School Name (X Std)</label>
                            <input type="text" class="form-control" id="school_name" name="school_name" placeholder="Enter school name" value="<?php echo set_value('school_name', (isset($student['school_name_x']) ? $student['school_name_x'] : '')); ?>">
                        </div>
                        <div class="ea-field">
                            <label>Year of Passing (X Std)</label>
                            <input type="text" class="form-control" id="tenth_passing" name="tenth_passing" placeholder="YYYY-MM" maxlength="20" value="<?php echo set_value('tenth_passing', (isset($student['passing_year_x']) ? $student['passing_year_x'] : '')); ?>">
                        </div>
                        <div class="ea-field">
                            <label>X Marks (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="tenth_marks_percentage" name="tenth_marks_percentage" placeholder="Enter %" value="<?php echo set_value('tenth_marks_percentage', (isset($student['tenth_marks_percentage']) ? $student['tenth_marks_percentage'] : '')); ?>">
                        </div>
                        <div class="ea-field">
                            <label>School Name (XII Std)</label>
                            <input type="text" class="form-control" id="school_name_xii" name="school_name_xii" placeholder="Enter 12th school name" value="<?php echo set_value('school_name_xii', (isset($student['school_name_xii']) ? $student['school_name_xii'] : '')); ?>">
                        </div>
                        <div class="ea-field">
                            <label>UG Degree Score / Percentage</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="ug_degree_score" name="ug_degree_score" placeholder="0 – 100 %" value="<?php echo set_value('ug_degree_score', (isset($pg_details['ug_degree_score']) ? $pg_details['ug_degree_score'] : '')); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Save Actions ━━ -->
            <div class="ea-card" style="overflow: hidden;">
                <div class="ea-actions">
                    <button type="submit" class="ea-btn ea-btn--primary">
                        <i class="fa fa-check"></i> Save Changes
                    </button>
                    <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="ea-btn ea-btn--ghost">
                        Cancel
                    </a>
                </div>
            </div>
        </form>

        <!-- ━━ Scholarship Applications ━━ -->
        <?php if (!empty($scholarships)): ?>
        <div class="ea-card">
            <div class="ea-card__header">
                <div class="ea-card__icon ea-card__icon--amber"><i class="fa fa-trophy"></i></div>
                <div>
                    <h3 class="ea-card__title">Scholarship Applications</h3>
                    <p class="ea-card__subtitle">Applied scholarships and status</p>
                </div>
            </div>
            <div class="ea-card__body" style="padding: 0;">
                <table class="ea-sch-table">
                    <thead>
                        <tr>
                            <th>Scholarship Name</th>
                            <th style="width:140px;">Status</th>
                            <th style="width:160px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scholarships as $sch):
                            $effective = (isset($sch['override_amount']) && $sch['override_amount'] !== null)
                                ? $sch['override_amount'] : $sch['default_amount'];
                            $is_not_eligible = ((float)($effective ?? 0) === 0.0 && !empty($sch['override_comment']));
                            $status_labels = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
                            $status_class  = isset($status_labels[$sch['status']]) ? $status_labels[$sch['status']] : 'default';
                        ?>
                        <tr>
                            <td style="font-weight:500;"><?php echo htmlspecialchars($sch['scholarship_name']); ?></td>
                            <td>
                                <span class="label label-<?php echo $status_class; ?>"><?php echo ucfirst($sch['status']); ?></span>
                            </td>
                            <td>
                                <?php if ($is_not_eligible): ?>
                                    <span class="text-danger"><i class="fa fa-times-circle"></i> Not Eligible</span>
                                    <?php if (!empty($sch['override_comment'])): ?>
                                        <small class="text-muted"> &mdash; <?php echo htmlspecialchars($sch['override_comment']); ?></small>
                                    <?php endif; ?>
                                <?php elseif ($effective !== null && $effective !== ''): ?>
                                    <strong>&#8377; <?php echo number_format((float)$effective, 2); ?></strong>
                                    <?php if (isset($sch['override_amount']) && $sch['override_amount'] !== null && $sch['override_amount'] != $sch['default_amount'] && !empty($sch['default_amount'])): ?>
                                        <small class="text-muted">(default: &#8377; <?php echo number_format((float)$sch['default_amount'], 2); ?>)</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Amount TBD</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding: 12px 16px; border-top: 1px solid var(--ea-border);">
                    <a href="<?php echo site_url('admin/scholarshipapplication'); ?>" class="ea-btn ea-btn--ghost" style="padding: 6px 14px; font-size: 12px;" target="_blank"><i class="fa fa-external-link"></i> Manage Scholarships</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ━━ Follow-Up Notes ━━ -->
        <div class="ea-card" id="followup-panel">
            <div class="ea-card__header">
                <div class="ea-card__icon ea-card__icon--blue"><i class="fa fa-comments-o"></i></div>
                <div>
                    <h3 class="ea-card__title">Follow-Up Notes</h3>
                    <p class="ea-card__subtitle">Communication history and next steps</p>
                </div>
            </div>
            <div class="ea-card__body">
                <div class="ea-followup-form">
                    <div class="ea-field" style="margin-bottom:0;">
                        <label>Note <span class="ea-req">*</span></label>
                        <textarea id="fu_note" class="form-control" rows="2" placeholder="Enter follow-up note..."></textarea>
                    </div>
                    <div class="ea-field" style="margin-bottom:0;">
                        <label>Next Contact Date</label>
                        <input type="text" id="fu_next_date" class="form-control date" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>" autocomplete="off">
                    </div>
                    <div style="padding-bottom:2px;">
                        <button type="button" class="ea-btn ea-btn--primary" style="width:100%;justify-content:center;" id="fu_save_btn" onclick="saveFollowup(<?php echo (int)$id; ?>)">
                            <i class="fa fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div style="margin-top: 20px;" id="fu-history-wrap">
                    <div class="text-center" style="padding: 20px; color: var(--ea-text-light);"><i class="fa fa-spinner fa-spin"></i> Loading&hellip;</div>
                </div>
            </div>
        </div>

    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script>
function checkIt(e) {
    return (e.charCode >= 48 && e.charCode <= 57);
}

function validateMobile(el) {
    if (el.value.length !== 10 && el.value.length > 0) {
        $(el).closest('.ea-field').find('.text-danger').text('Mobile number must be 10 digits');
        el.focus();
    } else {
        $(el).closest('.ea-field').find('.text-danger').text('');
    }
}

function allowAlphabets(event) {
    var key = event.keyCode;
    return (key >= 65 && key <= 90) || (key >= 97 && key <= 122) || key == 32 || key == 8 || key == 0;
}

function calculatePercentage(marksId, totalId, percId) {
    var marks = parseFloat($(marksId).val());
    var total = parseFloat($(totalId).val());

    if (total > 100) { $(totalId).val(100); total = 100; }
    if (total < 0) { $(totalId).val(0); total = 0; }
    if (marks > total) { $(marksId).val(total); marks = total; }
    if (marks < 0) { $(marksId).val(0); marks = 0; }

    if (!total) { $(percId).val("0.00"); return 0; }
    var percentage = (marks / total) * 100;
    $(percId).val(percentage.toFixed(2));
    return marks;
}

function isBarchEdit() {
    var sel = $("#admission_course_id option:selected");
    return sel.length && sel.data('is-barch') == 1;
}

function calculateTotal() {
    calculatePercentage("#maths_marks", "#total_maths", "#maths_perc");
    calculatePercentage("#physics_marks", "#total_physics", "#physics_perc");
    calculatePercentage("#chemistry_marks", "#total_chemistry", "#chemistry_perc");

    setTimeout(function() {
        var mathsPerc     = parseFloat($("#maths_perc").val() || 0);
        var physicsPerc   = parseFloat($("#physics_perc").val() || 0);
        var chemistryPerc = parseFloat($("#chemistry_perc").val() || 0);

        var average = (mathsPerc + physicsPerc + chemistryPerc) / 3;
        $("#average_marks").val(average.toFixed(2));
        $("#average_display").text(average.toFixed(2));

        var cutoff;
        if (isBarchEdit()) {
            var nataScore    = parseFloat($("#nata_score_edit").val() || 0);
            var hscTotal     = parseFloat($("#hsc_total_marks").val() || 0);
            var hscObtained  = parseFloat($("#hsc_marks_obtained").val() || 0);
            cutoff = (hscTotal > 0) ? nataScore + ((hscObtained / hscTotal) * 200) : 0;
            $("#cutoff_label_edit").text('Cut Off: NATA + (Obtained/Total)×200');
        } else {
            cutoff = ((physicsPerc + chemistryPerc) / 2) + mathsPerc;
            $("#cutoff_label_edit").text('Cut Off: (P+C)/2 + M');
        }
        $("#cutoff_marks").val(cutoff.toFixed(2));
        $("#cutoff_display").text(cutoff.toFixed(2));
    }, 10);
}

$(document).ready(function() {
    flatpickr('#dob', { dateFormat: 'Y-m-d', allowInput: false, maxDate: new Date() });

    var tenthField = $('#tenth_passing');
    var tenthValue = (tenthField.val() || '').trim();
    if (/^\d{4}$/.test(tenthValue)) { tenthField.val(tenthValue + '-01'); }

    flatpickr('#tenth_passing', {
        dateFormat: 'Y-m',
        allowInput: false,
        plugins: [ new monthSelectPlugin({ shorthand: true, dateFormat: 'Y-m', altFormat: 'F Y' }) ]
    });

    $("#maths_marks, #total_maths, #physics_marks, #total_physics, #chemistry_marks, #total_chemistry").on("input", calculateTotal);
    $("#nata_score_edit, #hsc_total_marks, #hsc_marks_obtained").on("input", calculateTotal);

    function updateBarchVisibility() {
        if (isBarchEdit()) {
            $("#barch_hsc_row").show();
            $("#barch_nata_section").show();
        } else {
            $("#barch_hsc_row").hide();
            $("#barch_nata_section").hide();
        }
        calculateTotal();
    }

    $("#admission_course_id").on("change", updateBarchVisibility);
    updateBarchVisibility();

    $('.select2-course').select2({ placeholder: '-- Select Course --', allowClear: true, width: '100%' });
});
</script>
<script>
var _fuBase = '<?php echo base_url("admin/onlinestudent"); ?>';

function loadFollowupHistory(admissionId, targetDiv) {
    $(targetDiv).html('<div class="text-center" style="padding:20px;color:var(--ea-text-light);"><i class="fa fa-spinner fa-spin"></i></div>');
    $.get(_fuBase + '/followup_history/' + admissionId, function(html) { $(targetDiv).html(html); });
}

function saveFollowup(admissionId) {
    var note = $('#fu_note').val().trim();
    if (!note) { $('#fu_note').focus(); return; }
    $('#fu_save_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post(_fuBase + '/followup_add', {
        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>',
        online_admission_id: admissionId,
        note: note,
        next_contact_date: $('#fu_next_date').val()
    }, function(res) {
        $('#fu_save_btn').prop('disabled', false).html('<i class="fa fa-plus"></i> Add');
        if (res.success) {
            $('#fu_note').val('');
            $('#fu_next_date').val('');
            loadFollowupHistory(admissionId, '#fu-history-wrap');
        } else {
            alert(res.msg || 'Error saving note.');
        }
    }, 'json');
}

function deleteFollowup(fid, admissionId) {
    if (!confirm('Delete this follow-up note?')) return;
    $.get(_fuBase + '/followup_delete/' + fid + '/' + admissionId, function(html) { $('#fu-history-wrap').html(html); });
}

$(document).ready(function() { loadFollowupHistory(<?php echo (int)$id; ?>, '#fu-history-wrap'); });
</script>
