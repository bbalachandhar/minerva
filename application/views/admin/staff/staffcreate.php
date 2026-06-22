<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/admission-wizard.css">

<div class="content-wrapper" style="min-height: 946px;">
<section class="content">
<div class="mn-admission-wizard">

    <!-- Page Header -->
    <div class="adm-page-header">
        <h1 class="adm-page-title"><i class="fa fa-user-plus" style="color:var(--primary);margin-right:8px;"></i><?php echo $this->lang->line('add_staff') ?: 'Add Staff'; ?></h1>
        <div class="adm-page-actions">
            <a href="<?php echo base_url(); ?>admin/staff/import" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> <?php echo $this->lang->line('import_staff'); ?></a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="adm-alert" style="background:var(--info-light);border-left:4px solid var(--info);color:var(--text);">
        <i class="fa fa-info-circle" style="color:var(--info);margin-right:6px;"></i>
        Staff email is their login username, password is generated automatically and sent to staff email. Superadmin can change staff password on their staff profile page.
    </div>

    <!-- Validation Errors -->
    <?php
    $errors = [];
    if (form_error('validate_staff')) { $errors[] = form_error('validate_staff'); }
    if (form_error('validate_storage')) { $errors[] = form_error('validate_storage'); }
    if (!empty($errors)): ?>
        <div class="adm-alert adm-alert-danger">
            <strong><i class="fa fa-exclamation-triangle"></i> Please fix the following errors:</strong>
            <ol><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ol>
        </div>
    <?php endif; ?>

    <?php $validation_errors = validation_errors();
    if (!empty($validation_errors)): ?>
        <div class="adm-alert adm-alert-danger">
            <strong><i class="fa fa-exclamation-triangle"></i> Please fix the following errors:</strong>
            <?php echo $validation_errors; ?>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('msg')) {
        echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg');
    } ?>

    <?php if ($this->session->flashdata('error')) { ?>
        <div class="adm-alert adm-alert-danger">
            <strong><i class="fa fa-exclamation-triangle"></i> Error</strong>
            <p><?php echo $this->session->flashdata('error'); ?></p>
        </div>
    <?php } ?>

    <!-- Step Indicator -->
    <div class="wizard-steps-bar">
        <button type="button" class="wizard-step-btn active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-label"><?php echo $this->lang->line('basic_information') ?: 'Basic'; ?> Info</span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="2">
            <span class="step-number">2</span>
            <span class="step-label">Personal Details</span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="3">
            <span class="step-number">3</span>
            <span class="step-label">Address & Qualifications</span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="4">
            <span class="step-number">4</span>
            <span class="step-label">Payroll & More</span>
        </button>
    </div>

    <!-- FORM START -->
    <form id="form1" action="<?php echo site_url('admin/staff/create') ?>" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
        <?php echo $this->customlib->getCSRF(); ?>

        <!-- ============================================================
             STEP 1 — BASIC INFORMATION
             ============================================================ -->
        <div class="wizard-panel active" data-panel="1">

            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-id-card-o"></i>
                    <h3><?php echo $this->lang->line('staff_id') ?: 'Staff ID'; ?> & <?php echo $this->lang->line('role') ?: 'Role'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if (!$staffid_auto_insert) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('staff_id'); ?> <span class="req">*</span></label>
                                <input autofocus id="employee_id" name="employee_id" type="text" class="form-control" value="<?php echo set_value('employee_id') ?>" />
                                <span class="text-danger"><?php echo form_error('employee_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('biometric_id'); ?></label>
                                <input id="biometric_id" name="biometric_id" type="text" class="form-control" value="<?php echo set_value('biometric_id') ?>" />
                                <span class="text-danger"><?php echo form_error('biometric_id'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>AU FIN No.</label>
                                <input id="au_fin_no" name="au_fin_no" type="text" class="form-control" value="<?php echo set_value('au_fin_no'); ?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>AICTE / COA ID</label>
                                <input id="aicte_coa_id" name="aicte_coa_id" type="text" class="form-control" value="<?php echo set_value('aicte_coa_id'); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('role'); ?> <span class="req">*</span></label>
                                <select id="role" name="role" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($roles as $key => $role) { ?>
                                        <option value="<?php echo $role['id'] ?>" <?php echo set_select('role', $role['id'], set_value('role')); ?>><?php echo $role["name"] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('role'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->staff_designation) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('designation'); ?></label>
                                <select id="designation" name="designation" class="form-control" onchange="syncCategoryFromDesignation()">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($designation as $key => $value) { ?>
                                        <option value="<?php echo $value["id"] ?>" data-category-id="<?php echo $value['category_id'] ?? ''; ?>" <?php echo set_select('designation', $value['id'], set_value('designation')); ?>><?php echo $value["designation"] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('designation'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Staff Type / Category</label>
                                <select id="category_id" name="category_id" class="form-control">
                                    <option value="">-- None --</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" style="border-left: 4px solid <?php echo $category['color']; ?>;" <?php echo set_select('category_id', $category['id'], ''); ?>>
                                                <i class="fa <?php echo $category['icon']; ?>"></i> <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_department) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('department'); ?></label>
                                <select id="department" name="department" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($department as $key => $value) { ?>
                                        <option value="<?php echo $value["id"] ?>" <?php echo set_select('department', $value['id'], set_value('department')); ?>><?php echo $value["department_name"] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('department'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if (isset($sch_setting->contract_type) && $sch_setting->contract_type) { ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Contract Type</label>
                                <select class="form-control" name="contract_type">
                                    <option value="">Select</option>
                                    <option value="Visiting Faculty" <?php echo set_select('contract_type', 'Visiting Faculty'); ?>>Visiting Faculty</option>
                                    <option value="Part Time Faculty" <?php echo set_select('contract_type', 'Part Time Faculty'); ?>>Part Time Faculty</option>
                                    <option value="Full Time Faculty" <?php echo set_select('contract_type', 'Full Time Faculty'); ?>>Full Time Faculty</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('contract_type'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 2 — PERSONAL DETAILS
             ============================================================ -->
        <div class="wizard-panel" data-panel="2">

            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-user"></i>
                    <h3>Personal Information</h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('prefix'); ?></label>
                                <input id="prefix" name="prefix" type="text" class="form-control" value="<?php echo set_value('prefix') ?>" />
                                <span class="text-danger"><?php echo form_error('prefix'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('first_name'); ?> <span class="req">*</span></label>
                                <input id="name" name="name" type="text" class="form-control" value="<?php echo set_value('name') ?>" />
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->staff_last_name) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('last_name'); ?></label>
                                <input id="surname" name="surname" type="text" class="form-control" value="<?php echo set_value('surname') ?>" />
                                <span class="text-danger"><?php echo form_error('surname'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_father_name) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_name'); ?></label>
                                <input id="father_name" name="father_name" type="text" class="form-control" value="<?php echo set_value('father_name') ?>" />
                                <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_mother_name) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mother_name'); ?></label>
                                <input id="mother_name" name="mother_name" type="text" class="form-control" value="<?php echo set_value('mother_name') ?>" />
                                <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('email'); ?> (<?php echo $this->lang->line('login') . " " . $this->lang->line('username'); ?>) <span class="req">*</span></label>
                                <input id="email" name="email" type="text" class="form-control" value="<?php echo set_value('email') ?>" placeholder="staff@email.com" />
                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('gender'); ?> <span class="req">*</span></label>
                                <select class="form-control" name="gender">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($genderList as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php echo set_select('gender', $key, set_value('gender')); ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date_of_birth'); ?> <span class="req">*</span></label>
                                <input id="dob" name="dob" type="text" class="form-control date" value="<?php echo set_value('dob') ?>" placeholder="DD/MM/YYYY" />
                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->staff_date_of_joining) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date_of_joining'); ?></label>
                                <input id="date_of_joining" name="date_of_joining" type="text" class="form-control date" value="<?php echo set_value('date_of_joining') ?>" />
                                <span class="text-danger"><?php echo form_error('date_of_joining'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->staff_phone) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('phone'); ?></label>
                                <input id="mobileno" name="contactno" type="text" class="form-control" value="<?php echo set_value('contactno') ?>" />
                                <span class="text-danger"><?php echo form_error('contactno'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_emergency_contact) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('emergency_contact_number'); ?></label>
                                <input name="emergency_no" type="text" class="form-control" value="<?php echo set_value('emergency_no') ?>" />
                                <span class="text-danger"><?php echo form_error('emergency_no'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_marital_status) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('marital_status'); ?></label>
                                <select class="form-control" name="marital_status">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($marital_status as $makey => $mavalue) { ?>
                                        <option value="<?php echo $mavalue ?>" <?php echo set_select('marital_status', $mavalue, set_value('marital_status')); ?>><?php echo $mavalue; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('marital_status'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if ($sch_setting->staff_photo) { ?>
                    <div class="row" style="margin-top:4px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('photo'); ?></label>
                                <div class="photo-upload-zone">
                                    <div class="photo-preview" id="staff_photo_preview">
                                        <i class="fa fa-camera"></i>
                                    </div>
                                    <div style="flex:1;">
                                        <input class="form-control" type="file" name="file" id="file" accept="image/*" />
                                        <span class="field-hint">100px x 100px recommended</span>
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <!-- Custom Fields -->
                    <?php $cf_html = display_custom_fields('staff');
                    if (!empty(trim($cf_html))) { ?>
                    <div class="row" style="margin-top:8px;">
                        <?php echo $cf_html; ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 3 — ADDRESS & QUALIFICATIONS
             ============================================================ -->
        <div class="wizard-panel" data-panel="3">

            <!-- Address -->
            <?php if ($sch_setting->staff_current_address || $sch_setting->staff_permanent_address) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-map-marker"></i>
                    <h3><?php echo $this->lang->line('address') ?: 'Address'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if ($sch_setting->staff_current_address) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('current') . ' ' . $this->lang->line('address'); ?></label>
                                <textarea name="address" class="form-control" rows="3"><?php echo set_value('address'); ?></textarea>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_permanent_address) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('permanent_address'); ?></label>
                                <textarea name="permanent_address" class="form-control" rows="3"><?php echo set_value('permanent_address'); ?></textarea>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Qualifications -->
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-graduation-cap"></i>
                    <h3><?php echo $this->lang->line('qualification') ?: 'Qualifications'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <?php if ($sch_setting->staff_qualification || $sch_setting->staff_work_experience || $sch_setting->staff_note) { ?>
                    <div class="row">
                        <?php if ($sch_setting->staff_qualification) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('qualification'); ?></label>
                                <textarea id="qualification" name="qualification" class="form-control" rows="2"><?php echo set_value('qualification') ?></textarea>
                                <span class="text-danger"><?php echo form_error('qualification'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_work_experience) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('work_experience'); ?></label>
                                <textarea id="work_experience" name="work_experience" class="form-control" rows="2"><?php echo set_value('work_experience') ?></textarea>
                                <span class="text-danger"><?php echo form_error('work_experience'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_note) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('note'); ?></label>
                                <textarea name="note" class="form-control" rows="2"><?php echo set_value('note'); ?></textarea>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('ug_qualification'); ?></label>
                                <input id="ug_qualification" name="ug_qualification" type="text" class="form-control" value="<?php echo set_value('ug_qualification') ?>" />
                                <span class="text-danger"><?php echo form_error('ug_qualification'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('pg_qualification'); ?></label>
                                <input id="pg_qualification" name="pg_qualification" type="text" class="form-control" value="<?php echo set_value('pg_qualification') ?>" />
                                <span class="text-danger"><?php echo form_error('pg_qualification'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('higher_qualification'); ?></label>
                                <input id="higher_qualification" name="higher_qualification" type="text" class="form-control" value="<?php echo set_value('higher_qualification') ?>" />
                                <span class="text-danger"><?php echo form_error('higher_qualification'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('qualified_exam'); ?></label>
                                <input id="qualified_exam" name="qualified_exam" type="text" class="form-control" value="<?php echo set_value('qualified_exam') ?>" />
                                <span class="text-danger"><?php echo form_error('qualified_exam'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_specialization'); ?></label>
                                <input id="subject_specialization" name="subject_specialization" type="text" class="form-control" value="<?php echo set_value('subject_specialization') ?>" />
                                <span class="text-danger"><?php echo form_error('subject_specialization'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('additional_qualification'); ?></label>
                                <textarea id="additional_qualification" name="additional_qualification" class="form-control" rows="2"><?php echo set_value('additional_qualification') ?></textarea>
                                <span class="text-danger"><?php echo form_error('additional_qualification'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Information -->
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-info-circle"></i>
                    <h3>Other Information</h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Aadhaar No</label>
                                <input id="aadhaar_no" name="aadhaar_no" type="text" class="form-control" value="<?php echo set_value('aadhaar_no') ?>" />
                                <span class="text-danger"><?php echo form_error('aadhaar_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Religious</label>
                                <input id="religion" name="religion" type="text" class="form-control" value="<?php echo set_value('religion') ?>" />
                                <span class="text-danger"><?php echo form_error('religion'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Communal Category</label>
                                <input id="caste" name="caste" type="text" class="form-control" value="<?php echo set_value('caste') ?>" />
                                <span class="text-danger"><?php echo form_error('caste'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Blood Group</label>
                                <input id="blood_group" name="blood_group" type="text" class="form-control" value="<?php echo set_value('blood_group') ?>" />
                                <span class="text-danger"><?php echo form_error('blood_group'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Country</label>
                                <input id="country" name="country" type="text" class="form-control" value="<?php echo set_value('country') ?>" />
                                <span class="text-danger"><?php echo form_error('country'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>State</label>
                                <input id="state" name="state" type="text" class="form-control" value="<?php echo set_value('state') ?>" />
                                <span class="text-danger"><?php echo form_error('state'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Pincode</label>
                                <input id="pincode" name="pincode" type="text" class="form-control" value="<?php echo set_value('pincode') ?>" />
                                <span class="text-danger"><?php echo form_error('pincode'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Previous Salary</label>
                                <input id="previous_salary" name="previous_salary" type="text" class="form-control" value="<?php echo set_value('previous_salary') ?>" />
                                <span class="text-danger"><?php echo form_error('previous_salary'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>UAN Number</label>
                                <input id="uan_no" name="uan_no" type="text" class="form-control" value="<?php echo set_value('uan_no') ?>" />
                                <span class="text-danger"><?php echo form_error('uan_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>PAN Number</label>
                                <input id="pan_no" name="pan_no" type="text" class="form-control" value="<?php echo set_value('pan_no') ?>" />
                                <span class="text-danger"><?php echo form_error('pan_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Previous Institution</label>
                                <input id="previous_institution" name="previous_institution" type="text" class="form-control" value="<?php echo set_value('previous_institution') ?>" />
                                <span class="text-danger"><?php echo form_error('previous_institution'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Subject Expertise</label>
                                <input id="subject_expertise" name="subject_expertise" type="text" class="form-control" value="<?php echo set_value('subject_expertise') ?>" />
                                <span class="text-danger"><?php echo form_error('subject_expertise'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 4 — PAYROLL, LEAVES, BANK & DOCUMENTS
             ============================================================ -->
        <div class="wizard-panel" data-panel="4">

            <!-- Payroll -->
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-money"></i>
                    <h3><?php echo $this->lang->line('payroll') ?: 'Payroll'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>UAN Number</label>
                                <input name="uan_no" type="text" class="form-control" value="<?php echo set_value('uan_no') ?>" />
                                <span class="text-danger"><?php echo form_error('uan_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('esi_no') ?: 'ESI No.'; ?></label>
                                <input id="esi_no" name="esi_no" type="text" class="form-control" value="<?php echo set_value('esi_no') ?>" />
                                <span class="text-danger"><?php echo form_error('esi_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('epf_enabled') ?? 'EPF Enabled'; ?></label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_epf_enabled" value="1" <?php echo (set_value('is_epf_enabled') ? 'checked' : 'checked'); ?> />
                                        <?php echo $this->lang->line('enable') ?? 'Enable'; ?>
                                    </label>
                                </div>
                                <span class="text-danger"><?php echo form_error('is_epf_enabled'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('esi_enabled') ?? 'ESI Enabled'; ?></label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_esi_enabled" value="1" <?php echo (set_value('is_esi_enabled') ? 'checked' : 'checked'); ?> />
                                        <?php echo $this->lang->line('enable') ?? 'Enable'; ?>
                                    </label>
                                </div>
                                <span class="text-danger"><?php echo form_error('is_esi_enabled'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Skip Payroll Generation</label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="skip_payroll" value="1" />
                                        Exclude from payroll
                                    </label>
                                </div>
                                <small class="text-muted">If checked, this staff will be skipped during bulk payroll generation.</small>
                            </div>
                        </div>
                        <?php if ($sch_setting->staff_basic_salary) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <?php echo $this->lang->line('contract_basic_salary') ?: ($this->lang->line('basic_salary') . ' (Contract)'); ?>
                                    <small class="text-muted"><i class="fa fa-info-circle" data-toggle="tooltip" title="Contracted/appointed basic salary from employment letter. This is used as the starting point for first payslip."></i></small>
                                </label>
                                <input type="text" class="form-control" name="basic_salary" value="<?php echo set_value('basic_salary') ?>" placeholder="Enter contract basic salary">
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <?php if ($sch_setting->staff_work_shift) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('work_shift'); ?></label>
                                <input id="shift" name="shift" type="text" class="form-control" value="<?php echo set_value('shift') ?>" />
                                <span class="text-danger"><?php echo form_error('shift'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_work_location) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('work_location'); ?></label>
                                <input id="location" name="location" type="text" class="form-control" value="<?php echo set_value('location') ?>" />
                                <span class="text-danger"><?php echo form_error('location'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Leaves -->
            <?php if ($sch_setting->staff_leaves) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-calendar-check-o"></i>
                    <h3><?php echo $this->lang->line('leaves') ?: 'Leave Allocation'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php foreach ($leavetypeList as $key => $leave) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $leave["type"]; ?></label>
                                <input name="leave_type[]" type="hidden" readonly class="form-control" value="<?php echo $leave['id'] ?>" />
                                <input name="alloted_leave_<?php echo $leave['id'] ?>" placeholder="<?php echo $this->lang->line('number_of_leaves'); ?>" type="text" class="form-control" />
                                <span class="text-danger"><?php echo form_error('alloted_leave'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Bank Account Details -->
            <?php if ($sch_setting->staff_account_details) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-university"></i>
                    <h3><?php echo $this->lang->line('bank_account_details') ?: 'Bank Account Details'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('account_title'); ?></label>
                                <input id="account_title" name="account_title" type="text" class="form-control" value="<?php echo set_value('account_title') ?>" />
                                <span class="text-danger"><?php echo form_error('account_title'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_account_number'); ?></label>
                                <input id="bank_account_no" name="bank_account_no" type="text" class="form-control" value="<?php echo set_value('bank_account_no') ?>" />
                                <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_name'); ?></label>
                                <input id="bank_name" name="bank_name" type="text" class="form-control" value="<?php echo set_value('bank_name') ?>" />
                                <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('ifsc_code'); ?></label>
                                <input id="ifsc_code" name="ifsc_code" type="text" class="form-control" value="<?php echo set_value('ifsc_code') ?>" />
                                <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_branch_name'); ?></label>
                                <input id="bank_branch" name="bank_branch" type="text" class="form-control" value="<?php echo set_value('bank_branch') ?>" />
                                <span class="text-danger"><?php echo form_error('bank_branch'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Social Links -->
            <?php if ($sch_setting->staff_social_media) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-share-alt"></i>
                    <h3><?php echo $this->lang->line('social_media') ?: 'Social Links'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('facebook_url'); ?></label>
                                <input name="facebook" type="text" class="form-control" value="<?php echo set_value('facebook') ?>" />
                                <span class="text-danger"><?php echo form_error('facebook'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('twitter_url'); ?></label>
                                <input name="twitter" type="text" class="form-control" value="<?php echo set_value('twitter') ?>" />
                                <span class="text-danger"><?php echo form_error('twitter_profile'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('linkedin_url'); ?></label>
                                <input name="linkedin" type="text" class="form-control" value="<?php echo set_value('linkedin') ?>" />
                                <span class="text-danger"><?php echo form_error('linkedin'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('instagram_url'); ?></label>
                                <input name="instagram" type="text" class="form-control" value="<?php echo set_value('instagram') ?>" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Upload Documents -->
            <?php if ($sch_setting->staff_upload_documents) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-paperclip"></i>
                    <h3><?php echo $this->lang->line('upload_documents') ?: 'Upload Documents'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="doc-upload-row" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <span class="doc-num" style="min-width:24px;height:24px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">1</span>
                                <span style="min-width:120px;font-weight:600;font-size:13px;"><?php echo $this->lang->line('resume'); ?></span>
                                <input class="form-control" type="file" name="first_doc" id="doc1">
                            </div>
                            <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                            <div class="doc-upload-row" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <span class="doc-num" style="min-width:24px;height:24px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">3</span>
                                <span style="min-width:120px;font-weight:600;font-size:13px;"><?php echo $this->lang->line('resignation_letter'); ?></span>
                                <input class="form-control" type="file" name="third_doc" id="doc3">
                            </div>
                            <span class="text-danger"><?php echo form_error('third_doc'); ?></span>
                        </div>
                        <div class="col-md-6">
                            <div class="doc-upload-row" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <span class="doc-num" style="min-width:24px;height:24px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">2</span>
                                <span style="min-width:120px;font-weight:600;font-size:13px;"><?php echo $this->lang->line('joining_letter'); ?></span>
                                <input class="form-control" type="file" name="second_doc" id="doc2">
                            </div>
                            <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                            <div class="doc-upload-row" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <span class="doc-num" style="min-width:24px;height:24px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">4</span>
                                <span style="min-width:120px;font-weight:600;font-size:13px;"><?php echo $this->lang->line('other_documents'); ?></span>
                                <input type="hidden" name="fourth_title" class="form-control" placeholder="Other Documents">
                                <input class="form-control" type="file" name="fourth_doc" id="doc4">
                            </div>
                            <span class="text-danger"><?php echo form_error('fourth_doc'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Wizard Footer -->
        <div class="wizard-footer">
            <button type="button" class="btn-wizard" id="wizardPrev" style="visibility:hidden;">
                <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('previous') ?: 'Previous'; ?>
            </button>
            <div>
                <button type="button" class="btn-wizard btn-wizard-primary" id="wizardNext">
                    <?php echo $this->lang->line('next') ?: 'Next'; ?> <i class="fa fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn-wizard btn-wizard-success" id="submitbtn" style="display:none;">
                    <i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?> Staff
                </button>
            </div>
        </div>
    </form>

</div>
</section>
</div>

<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<script>
$(document).ready(function () {
    // ---- Wizard Navigation ----
    var totalSteps = $('.wizard-step-btn').length;
    var currentStep = 1;

    <?php if (!empty($validation_errors) || !empty($errors)) { ?>
    <?php
    $step_for_error = 1;
    if (form_error('name') || form_error('email') || form_error('gender') || form_error('dob') || form_error('contactno')) {
        $step_for_error = 2;
    } elseif (form_error('qualification') || form_error('aadhaar_no')) {
        $step_for_error = 3;
    } elseif (form_error('basic_salary') || form_error('bank_account_no')) {
        $step_for_error = 4;
    }
    ?>
    currentStep = <?php echo $step_for_error; ?>;
    goToStep(currentStep);
    <?php } ?>

    function goToStep(step) {
        currentStep = step;
        $('.wizard-panel').removeClass('active');
        $('.wizard-panel[data-panel="' + step + '"]').addClass('active');

        $('.wizard-step-btn').removeClass('active');
        $('.wizard-step-btn[data-step="' + step + '"]').addClass('active');

        // Mark completed steps
        $('.wizard-step-btn').each(function() {
            var s = parseInt($(this).data('step'));
            if (s < step) {
                $(this).addClass('completed');
            } else {
                $(this).removeClass('completed');
            }
        });

        // Show/hide prev/next/save
        $('#wizardPrev').css('visibility', step === 1 ? 'hidden' : 'visible');
        if (step === totalSteps) {
            $('#wizardNext').hide();
            $('#submitbtn').show();
        } else {
            $('#wizardNext').show();
            $('#submitbtn').hide();
        }

        $('html, body').animate({ scrollTop: $('.wizard-steps-bar').offset().top - 60 }, 300);
    }

    $('.wizard-step-btn').on('click', function() {
        goToStep(parseInt($(this).data('step')));
    });

    $('#wizardNext').on('click', function() {
        if (currentStep < totalSteps) {
            goToStep(currentStep + 1);
        }
    });

    $('#wizardPrev').on('click', function() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });

    // ---- Form Submit (traditional POST with loading state) ----
    $('#form1').on('submit', function() {
        var $btn = $('#submitbtn');
        $btn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo $this->lang->line("loading") ?: "Saving..."; ?>').prop('disabled', true);
    });

    // ---- Photo preview ----
    $('#file').on('change', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#staff_photo_preview').html('<img src="' + e.target.result + '">');
        };
        if (this.files[0]) reader.readAsDataURL(this.files[0]);
    });
});

// ---- Sync category from designation ----
function syncCategoryFromDesignation() {
    var designationSelect = document.getElementById('designation');
    var selectedOption = designationSelect.options[designationSelect.selectedIndex];
    var categoryId = selectedOption.getAttribute('data-category-id');
    var categoryDropdown = document.getElementById('category_id');

    if (categoryId) {
        categoryDropdown.value = categoryId;
    } else {
        categoryDropdown.value = '';
    }
}

// Trigger on page load if designation is already selected
window.addEventListener('DOMContentLoaded', function(){
    var designationSelect = document.getElementById('designation');
    if (designationSelect && designationSelect.value !== '') {
        syncCategoryFromDesignation();
    }
});
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>
