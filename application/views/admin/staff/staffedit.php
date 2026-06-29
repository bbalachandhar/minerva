<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/admission-wizard.css">

<div class="content-wrapper" style="min-height: 946px;">
<section class="content">
<div class="mn-admission-wizard">

    <!-- Page Header -->
    <div class="adm-page-header">
        <h1 class="adm-page-title"><i class="fa fa-pencil-square-o" style="color:var(--primary);margin-right:8px;"></i><?php echo $this->lang->line('edit_staff') ?: 'Edit Staff'; ?></h1>
        <div class="adm-page-actions">
            <span style="font-size:14px;color:var(--text-secondary);font-weight:600;">
                <?php echo htmlspecialchars($staff['name']); ?><?php if (!empty($staff['surname'])) echo ' ' . htmlspecialchars($staff['surname']); ?>
                &nbsp;&mdash;&nbsp;
                <?php echo $this->lang->line('staff_id') ?: 'Staff ID'; ?>: <strong style="color:var(--primary);"><?php echo htmlspecialchars($staff['employee_id']); ?></strong>
            </span>
        </div>
    </div>

    <!-- Validation Errors -->
    <?php $validation_errors = validation_errors();
    if (!empty($validation_errors)): ?>
        <div class="adm-alert adm-alert-danger">
            <strong><i class="fa fa-exclamation-triangle"></i> <?php echo $this->lang->line('please_check_the_form_below_for_errors') ?: 'Please fix the following errors:'; ?></strong>
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
            <span class="step-label"><?php echo $this->lang->line('basic_information') ?: 'Basic'; ?></span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="2">
            <span class="step-number">2</span>
            <span class="step-label"><?php echo $this->lang->line('personal_details') ?: 'Personal'; ?></span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="3">
            <span class="step-number">3</span>
            <span class="step-label"><?php echo $this->lang->line('address') ?: 'Address'; ?> & <?php echo $this->lang->line('qualification') ?: 'Qualifications'; ?></span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="4">
            <span class="step-number">4</span>
            <span class="step-label"><?php echo $this->lang->line('payroll') ?: 'Payroll'; ?> & More</span>
        </button>
    </div>

    <!-- FORM START -->
    <form id="form1" action="<?php echo site_url('admin/staff/edit/' . $staff["id"]) ?>" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
        <?php echo $this->customlib->getCSRF(); ?>
        <input type="hidden" name="editid" value="<?php echo $staff['id']; ?>">

        <!-- ============================================================
             STEP 1 - BASIC INFORMATION
             ============================================================ -->
        <div class="wizard-panel active" data-panel="1">

            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-id-card-o"></i>
                    <h3><?php echo $this->lang->line('basic_information') ?: 'Basic Information'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="alert alert-info" style="margin-bottom:16px;">
                        Staff email is their login username, password is generated automatically and sent to staff email. Superadmin can change staff password on their staff profile page.
                    </div>

                    <div class="row">
                        <?php if ($staffid_auto_insert) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('staff_id') ?: 'Staff ID'; ?></label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($staff['employee_id']); ?>" readonly />
                                    <span class="field-hint">Auto-generated</span>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('staff_id') ?: 'Staff ID'; ?> <span class="req">*</span></label>
                                    <input autofocus id="employee_id" name="employee_id" type="text" class="form-control" value="<?php echo $staff["employee_id"] ?>" />
                                    <span class="text-danger"><?php echo form_error('employee_id'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('biometric_id') ?: 'Biometric ID'; ?></label>
                                <input id="biometric_id" name="biometric_id" type="text" class="form-control" value="<?php echo set_value('biometric_id', $staff['biometric_id']); ?>" />
                                <span class="text-danger"><?php echo form_error('biometric_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>AU FIN No.</label>
                                <input id="au_fin_no" name="au_fin_no" type="text" class="form-control" value="<?php echo htmlspecialchars($staff['au_fin_no'] ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>AICTE / COA ID</label>
                                <input id="aicte_coa_id" name="aicte_coa_id" type="text" class="form-control" value="<?php echo htmlspecialchars($staff['aicte_coa_id'] ?? ''); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('role') ?: 'Role'; ?> <span class="req">*</span></label>
                                <select id="role" name="role[]" class="form-control select2-multi-role" multiple="multiple" style="width:100%;">
                                    <?php
                                    $staff_role_ids = isset($staff_role_ids) ? $staff_role_ids : [];
                                    foreach ($getStaffRole as $key => $role) { ?>
                                        <option value="<?php echo $role["id"] ?>" <?php echo in_array($role["id"], $staff_role_ids) ? 'selected' : ''; ?>><?php echo $role["name"] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('role[]'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('status') ?: 'Status'; ?></label>
                                <select id="is_active" name="is_active" class="form-control">
                                    <option value="1" <?php if($staff['is_active'] == 1) echo 'selected'; ?>><?php echo $this->lang->line('active') ?: 'Active'; ?></option>
                                    <option value="0" <?php if($staff['is_active'] == 0) echo 'selected'; ?>><?php echo $this->lang->line('inactive') ?: 'Inactive'; ?></option>
                                </select>
                            </div>
                        </div>
                        <?php if ($sch_setting->staff_designation) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('designation') ?: 'Designation'; ?></label>
                                    <select id="designation" name="designation" class="form-control" onchange="updateStaffType()">
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($designation as $key => $value) { ?>
                                            <option value="<?php echo $value["id"] ?>" data-category="<?php echo $value['category_name'] ?? ''; ?>" data-color="<?php echo $value['color'] ?? '#ccc'; ?>" data-icon="<?php echo $value['icon'] ?? 'fa-folder'; ?>" <?php if ($staff["designation"] == $value["id"]) echo "selected"; ?>><?php echo $value["designation"] ?></option>
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
                                                <option value="<?php echo $category['id']; ?>" style="border-left: 4px solid <?php echo $category['color']; ?>;" <?php if (!empty($staff['category_id']) && $staff['category_id'] == $category['id']) echo 'selected'; ?>>
                                                    <i class="fa <?php echo $category['icon']; ?>"></i> <?php echo $category['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div id="staff_type_display" style="margin-top:4px;font-size:13px;"></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->staff_department) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('department') ?: 'Department'; ?></label>
                                    <select id="department" name="department" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($department as $key => $value) { ?>
                                            <option value="<?php echo $value["id"] ?>" <?php if ($staff["department"] == $value["id"]) echo "selected"; ?>><?php echo $value["department_name"] ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('department'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_contract_type) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('contract_type') ?: 'Contract Type'; ?></label>
                                    <select class="form-control" name="contract_type">
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($contract_type as $key => $value) { ?>
                                            <option value="<?php echo $key ?>" <?php if ($staff["contract_type"] == $key) echo "selected"; ?>><?php echo $value ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('contract_type'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 2 - PERSONAL DETAILS
             ============================================================ -->
        <div class="wizard-panel" data-panel="2">

            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-user"></i>
                    <h3><?php echo $this->lang->line('personal_details') ?: 'Personal Details'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('prefix') ?: 'Prefix'; ?></label>
                                <input id="prefix" name="prefix" type="text" class="form-control" value="<?php echo set_value('prefix', $staff["prefix"]); ?>" />
                                <span class="text-danger"><?php echo form_error('prefix'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('first_name') ?: 'First Name'; ?> <span class="req">*</span></label>
                                <input id="firstname" name="name" type="text" class="form-control" value="<?php echo set_value('name', $staff["name"]); ?>" />
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->staff_last_name) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('last_name') ?: 'Last Name'; ?></label>
                                    <input id="surname" name="surname" type="text" class="form-control" value="<?php echo set_value('surname', $staff["surname"]); ?>" />
                                    <span class="text-danger"><?php echo form_error('surname'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_father_name) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('father_name') ?: 'Father Name'; ?></label>
                                    <input id="father_name" name="father_name" type="text" class="form-control" value="<?php echo set_value('father_name', $staff["father_name"]); ?>" />
                                    <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->staff_mother_name) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('mother_name') ?: 'Mother Name'; ?></label>
                                    <input id="mother_name" name="mother_name" type="text" class="form-control" value="<?php echo set_value('mother_name', $staff["mother_name"]); ?>" />
                                    <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('email') ?: 'Email'; ?> <span class="req">*</span></label>
                                <input id="email" name="email" type="text" class="form-control" value="<?php echo set_value('email', $staff["email"]); ?>" />
                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('gender') ?: 'Gender'; ?> <span class="req">*</span></label>
                                <select class="form-control" name="gender">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($genderList as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php if ($staff['gender'] == $key) echo "selected"; ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date_of_birth') ?: 'Date of Birth'; ?> <span class="req">*</span></label>
                                <input id="dob" name="dob" type="text" class="form-control date" value="<?php
                                if (!empty($staff["dob"])) {
                                    echo date($this->customlib->getSchoolDateFormat(), strtotime($staff["dob"]));
                                }
                                ?>" />
                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->staff_date_of_joining) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('date_of_joining') ?: 'Date of Joining'; ?></label>
                                    <input id="date_of_joining" name="date_of_joining" type="text" class="form-control date" value="<?php
                                    if ($staff["date_of_joining"]) {
                                        echo date($this->customlib->getSchoolDateFormat(), strtotime($staff["date_of_joining"]));
                                    }
                                    ?>" />
                                    <span class="text-danger"><?php echo form_error('date_of_joining'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_phone) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('phone') ?: 'Phone'; ?></label>
                                    <input id="mobileno" name="contactno" type="text" class="form-control" value="<?php echo set_value('contactno', $staff["contact_no"]); ?>" />
                                    <span class="text-danger"><?php echo form_error('contactno'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_emergency_contact) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('emergency_contact_number') ?: 'Emergency Contact'; ?></label>
                                    <input name="emergency_no" type="text" class="form-control" value="<?php echo set_value('emergency_no', isset($staff["emergency_contact_number"]) ? $staff["emergency_contact_number"] : ''); ?>" />
                                    <span class="text-danger"><?php echo form_error('emergency_no'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_marital_status) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('marital_status') ?: 'Marital Status'; ?></label>
                                    <select class="form-control" name="marital_status">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($marital_status as $makey => $mavalue) { ?>
                                            <option <?php if ($staff["marital_status"] == $mavalue) echo "selected"; ?> value="<?php echo $mavalue; ?>"><?php echo $mavalue; ?></option>
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
                                <label><?php echo $this->lang->line('photo') ?: 'Photo'; ?></label>
                                <div class="photo-upload-zone">
                                    <div class="photo-preview">
                                        <?php
                                        $staff_photo = !empty($staff['image']) ? $staff['image'] : '';
                                        if ($staff_photo) { ?>
                                            <img id="staff_photo_preview" src="<?php echo base_url() . $staff_photo; ?>" style="max-width:80px;max-height:80px;border-radius:6px;" />
                                        <?php } else { ?>
                                            <img id="staff_photo_preview" src="<?php echo base_url(); ?>uploads/staff_images/default_male.jpg" style="max-width:80px;max-height:80px;border-radius:6px;" />
                                        <?php } ?>
                                    </div>
                                    <div style="flex:1;">
                                        <input class="form-control" type="file" name="file" id="file" accept="image/*" />
                                        <span class="field-hint">Upload new photo to replace existing</span>
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <!-- Custom Fields -->
                    <?php $cf_html = display_custom_fields('staff', $staff['id']);
                    if (!empty(trim($cf_html))) { ?>
                    <div class="row" style="margin-top:8px;">
                        <?php echo $cf_html; ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 3 - ADDRESS & QUALIFICATIONS
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
                                    <label><?php echo $this->lang->line('current_address') ?: 'Current Address'; ?></label>
                                    <textarea name="address" class="form-control" rows="3"><?php echo set_value('address', $staff["local_address"]) ?></textarea>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_permanent_address) { ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('permanent_address') ?: 'Permanent Address'; ?></label>
                                    <textarea name="permanent_address" class="form-control" rows="3"><?php echo set_value('permanent_address', $staff["permanent_address"]); ?></textarea>
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
                    <div class="row">
                        <?php if ($sch_setting->staff_qualification) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('qualification') ?: 'Qualification'; ?></label>
                                    <textarea id="qualification" name="qualification" class="form-control"><?php echo set_value('qualification', $staff["qualification"]); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('qualification'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_work_experience) { ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('work_experience') ?: 'Work Experience'; ?></label>
                                    <textarea name="work_experience" class="form-control"><?php echo set_value('work_experience', isset($staff["work_experience"]) ? $staff["work_experience"] : '') ?></textarea>
                                    <span class="text-danger"><?php echo form_error('work_experience'); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_note) { ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('note') ?: 'Note'; ?></label>
                                    <textarea name="note" class="form-control"><?php echo set_value('note', $staff["note"]) ?></textarea>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('ug_qualification') ?: 'UG Qualification'; ?></label>
                                <input id="ug_qualification" name="ug_qualification" type="text" class="form-control" value="<?php echo set_value('ug_qualification', $staff["ug_qualification"]) ?>" />
                                <span class="text-danger"><?php echo form_error('ug_qualification'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('pg_qualification') ?: 'PG Qualification'; ?></label>
                                <input id="pg_qualification" name="pg_qualification" type="text" class="form-control" value="<?php echo set_value('pg_qualification', $staff["pg_qualification"]) ?>" />
                                <span class="text-danger"><?php echo form_error('pg_qualification'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('higher_qualification') ?: 'Higher Qualification'; ?></label>
                                <input id="higher_qualification" name="higher_qualification" type="text" class="form-control" value="<?php echo set_value('higher_qualification', $staff["higher_qualification"]) ?>" />
                                <span class="text-danger"><?php echo form_error('higher_qualification'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('qualified_exam') ?: 'Qualified Exam'; ?></label>
                                <input id="qualified_exam" name="qualified_exam" type="text" class="form-control" value="<?php echo set_value('qualified_exam', $staff["qualified_exam"]) ?>" />
                                <span class="text-danger"><?php echo form_error('qualified_exam'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_specialization') ?: 'Subject Specialization'; ?></label>
                                <input id="subject_specialization" name="subject_specialization" type="text" class="form-control" value="<?php echo set_value('subject_specialization', $staff["subject_specialization"]) ?>" />
                                <span class="text-danger"><?php echo form_error('subject_specialization'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('additional_qualification') ?: 'Additional Qualification'; ?></label>
                                <textarea id="additional_qualification" name="additional_qualification" class="form-control"><?php echo set_value('additional_qualification', $staff["additional_qualification"]) ?></textarea>
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
                    <h3><?php echo $this->lang->line('other_information') ?: 'Other Information'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('aadhaar_no') ?: 'Aadhaar No'; ?></label>
                                <input id="aadhaar_no" name="aadhaar_no" type="text" class="form-control" value="<?php echo set_value('aadhaar_no', $staff["aadhaar_no"]) ?>" />
                                <span class="text-danger"><?php echo form_error('aadhaar_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('religion') ?: 'Religion'; ?></label>
                                <input id="religion" name="religion" type="text" class="form-control" value="<?php echo set_value('religion', $staff["religion"]) ?>" />
                                <span class="text-danger"><?php echo form_error('religion'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('caste') ?: 'Caste'; ?></label>
                                <input id="caste" name="caste" type="text" class="form-control" value="<?php echo set_value('caste', $staff["caste"]) ?>" />
                                <span class="text-danger"><?php echo form_error('caste'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('blood_group') ?: 'Blood Group'; ?></label>
                                <input id="blood_group" name="blood_group" type="text" class="form-control" value="<?php echo set_value('blood_group', $staff["blood_group"]) ?>" />
                                <span class="text-danger"><?php echo form_error('blood_group'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('country') ?: 'Country'; ?></label>
                                <input id="country" name="country" type="text" class="form-control" value="<?php echo set_value('country', $staff["country"]) ?>" />
                                <span class="text-danger"><?php echo form_error('country'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('state') ?: 'State'; ?></label>
                                <input id="state" name="state" type="text" class="form-control" value="<?php echo set_value('state', $staff["state"]) ?>" />
                                <span class="text-danger"><?php echo form_error('state'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('pincode') ?: 'Pincode'; ?></label>
                                <input id="pincode" name="pincode" type="text" class="form-control" value="<?php echo set_value('pincode', $staff["pincode"]) ?>" />
                                <span class="text-danger"><?php echo form_error('pincode'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('previous_salary') ?: 'Previous Salary'; ?></label>
                                <input id="previous_salary" name="previous_salary" type="text" class="form-control" value="<?php echo set_value('previous_salary', $staff["previous_salary"]) ?>" />
                                <span class="text-danger"><?php echo form_error('previous_salary'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('uan_no') ?: 'UAN No'; ?></label>
                                <input id="uan_no" name="uan_no" type="text" class="form-control" value="<?php echo set_value('uan_no', $staff["uan_no"]) ?>" />
                                <span class="text-danger"><?php echo form_error('uan_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('pan_no') ?: 'PAN No'; ?></label>
                                <input id="pan_no" name="pan_no" type="text" class="form-control" value="<?php echo set_value('pan_no', $staff["pan_no"]) ?>" />
                                <span class="text-danger"><?php echo form_error('pan_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('previous_institution') ?: 'Previous Institution'; ?></label>
                                <input id="previous_institution" name="previous_institution" type="text" class="form-control" value="<?php echo set_value('previous_institution', $staff["previous_institution"]) ?>" />
                                <span class="text-danger"><?php echo form_error('previous_institution'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('subject_expertise') ?: 'Subject Expertise'; ?></label>
                                <input id="subject_expertise" name="subject_expertise" type="text" class="form-control" value="<?php echo set_value('subject_expertise', $staff["subject_expertise"]) ?>" />
                                <span class="text-danger"><?php echo form_error('subject_expertise'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 4 - PAYROLL, LEAVES, BANK & DOCUMENTS
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
                                <label><?php echo $this->lang->line('esi_no') ?: 'ESI No.'; ?></label>
                                <input id="esi_no" name="esi_no" type="text" class="form-control" value="<?php echo $staff["esi_no"] ?>" />
                                <span class="text-danger"><?php echo form_error('esi_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('epf_enabled') ?? 'EPF Enabled'; ?></label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_epf_enabled" value="1" <?php echo ($staff["is_epf_enabled"] ? 'checked' : ''); ?> />
                                        <?php echo $this->lang->line('enable') ?? 'Enable'; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('esi_enabled') ?? 'ESI Enabled'; ?></label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_esi_enabled" value="1" <?php echo ($staff["is_esi_enabled"] ? 'checked' : ''); ?> />
                                        <?php echo $this->lang->line('enable') ?? 'Enable'; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Skip Payroll Generation</label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="skip_payroll" value="1" <?php echo (!empty($staff['skip_payroll']) ? 'checked' : ''); ?> />
                                        Exclude from payroll
                                    </label>
                                </div>
                                <small class="text-muted">If checked, this staff will be skipped during bulk payroll generation.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>TDS % (Flat Override)</label>
                                <input id="tds_percentage" name="tds_percentage" type="number" step="0.01" min="0" max="100" class="form-control" value="<?php echo isset($staff['tds_percentage']) ? $staff['tds_percentage'] : ''; ?>" placeholder="e.g. 5 or 10" />
                                <small class="text-muted">If set, applies flat % on gross salary instead of the new-regime slab. Leave blank for slab-based TDS.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Opening FY YTD Income (Apr to previous month)</label>
                                <input id="opening_ytd_income" name="opening_ytd_income" type="number" step="0.01" min="0" class="form-control" value="<?php echo isset($staff['opening_ytd_income']) ? $staff['opening_ytd_income'] : ''; ?>" placeholder="e.g. 990000" />
                                <small class="text-muted">Enter cumulative gross salary already paid from April to previous month (before this system go-live).</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Opening FY TDS Already Deducted</label>
                                <input id="opening_ytd_tax_deducted" name="opening_ytd_tax_deducted" type="number" step="0.01" min="0" class="form-control" value="<?php echo isset($staff['opening_ytd_tax_deducted']) ? $staff['opening_ytd_tax_deducted'] : ''; ?>" placeholder="e.g. 42500" />
                                <small class="text-muted">Enter only cumulative income-tax (TDS) already deducted. Do not enter net salary, PF, ESI, or other deductions.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Opening Balance FY Start Year</label>
                                <?php $default_opening_fy_year = ((int)date('n') >= 4) ? (int)date('Y') : ((int)date('Y') - 1); ?>
                                <input id="opening_ytd_fy_start_year" name="opening_ytd_fy_start_year" type="number" min="2000" max="2100" class="form-control" value="<?php echo !empty($staff['opening_ytd_fy_start_year']) ? (int)$staff['opening_ytd_fy_start_year'] : $default_opening_fy_year; ?>" placeholder="e.g. 2025" />
                                <small class="text-muted">Use FY start year only (for 2025-26 enter 2025). Opening values apply only for this FY and auto-stop in next FY.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->staff_basic_salary) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>
                                        <?php echo $this->lang->line('contract_basic_salary') ?: ($this->lang->line('basic_salary') . ' (Contract)'); ?>
                                        <small class="text-muted"><i class="fa fa-info-circle" data-toggle="tooltip" title="Contracted/appointed basic salary from employment letter. Monthly payslip may vary with increments/bonuses."></i></small>
                                    </label>
                                    <input type="text" value="<?php echo $staff["basic_salary"] ?>" class="form-control" name="basic_salary">
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_work_shift) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('work_shift') ?: 'Work Shift'; ?></label>
                                    <input id="shift" name="shift" type="text" class="form-control" value="<?php echo $staff["shift"] ?>" />
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($sch_setting->staff_work_location) { ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('work_location') ?: 'Work Location'; ?></label>
                                    <input id="location" name="location" type="text" class="form-control" value="<?php echo $staff["location"] ?>" />
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date_of_leaving') ?: 'Date of Leaving'; ?></label>
                                <input id="date_of_leaving" name="date_of_leaving" type="text" class="form-control date" value="<?php
                                if ($staff["date_of_leaving"]) {
                                    echo date($this->customlib->getSchoolDateFormat(), strtotime($staff["date_of_leaving"]));
                                }
                                ?>" />
                                <span class="text-danger"><?php echo form_error('date_of_leaving'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaves -->
            <?php if ($sch_setting->staff_leaves) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-calendar-check-o"></i>
                    <h3><?php echo $this->lang->line('leaves') ?: 'Leaves'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <p class="text-muted" style="margin: 0 0 10px;">Read-only. Update leave balances from Update Leave Balance module.</p>
                    <div class="row">
                        <?php
                        $j = 0;
                        foreach ($leavetypeList as $key => $leave) {
                        ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo $leave["type"]; ?></label>
                                    <?php if (!empty($can_edit_leave_section)) { ?>
                                        <input name="alloted_leave[]" placeholder="<?php echo $this->lang->line('number_of_leaves') ?: 'Number of Leaves'; ?>" type="text" class="form-control" value="<?php
                                        if (array_key_exists($j, $staffLeaveDetails)) {
                                            echo $staffLeaveDetails[$j]["alloted_leave"];
                                        }
                                        ?>" />
                                        <input name="leave_type[]" type="hidden" class="form-control" value="<?php echo $leave["type"] ?>" />
                                        <input name="altid[]" type="hidden" class="form-control" value="<?php
                                        if (array_key_exists($j, $staffLeaveDetails)) {
                                            echo $staffLeaveDetails[$j]["altid"];
                                        }
                                        ?>" />
                                        <input name="leave_type_id[]" type="hidden" class="form-control" value="<?php echo $leave["id"]; ?>" />
                                    <?php } else { ?>
                                        <input type="text" class="form-control" readonly value="<?php
                                        if (array_key_exists($j, $staffLeaveDetails)) {
                                            echo $staffLeaveDetails[$j]["alloted_leave"];
                                        }
                                        ?>" />
                                        <!-- Hidden inputs still submitted for server-side processing -->
                                        <input name="alloted_leave[]" type="hidden" value="<?php
                                        if (array_key_exists($j, $staffLeaveDetails)) {
                                            echo $staffLeaveDetails[$j]["alloted_leave"];
                                        }
                                        ?>" />
                                        <input name="leave_type[]" type="hidden" value="<?php echo $leave["type"] ?>" />
                                        <input name="altid[]" type="hidden" value="<?php
                                        if (array_key_exists($j, $staffLeaveDetails)) {
                                            echo $staffLeaveDetails[$j]["altid"];
                                        }
                                        ?>" />
                                        <input name="leave_type_id[]" type="hidden" value="<?php echo $leave["id"]; ?>" />
                                    <?php } ?>
                                </div>
                            </div>
                        <?php
                            $j++;
                        }
                        ?>
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
                                <label><?php echo $this->lang->line('account_title') ?: 'Account Title'; ?></label>
                                <input id="account_title" name="account_title" type="text" class="form-control" value="<?php echo $staff["account_title"] ?>" />
                                <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_account_number') ?: 'Bank Account Number'; ?></label>
                                <input id="bank_account_no" name="bank_account_no" type="text" class="form-control" value="<?php echo $staff["bank_account_no"] ?>" />
                                <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_name') ?: 'Bank Name'; ?></label>
                                <input id="bank_name" name="bank_name" type="text" class="form-control" value="<?php echo $staff["bank_name"] ?>" />
                                <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('ifsc_code') ?: 'IFSC Code'; ?></label>
                                <input id="ifsc_code" name="ifsc_code" type="text" class="form-control" value="<?php echo $staff["ifsc_code"] ?>" />
                                <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_branch_name') ?: 'Bank Branch'; ?></label>
                                <input id="bank_branch" name="bank_branch" type="text" class="form-control" value="<?php echo $staff["bank_branch"] ?>" />
                                <span class="text-danger"><?php echo form_error('bank_branch'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Social Media -->
            <?php if ($sch_setting->staff_social_media) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-share-alt"></i>
                    <h3><?php echo $this->lang->line('social_media_link') ?: 'Social Media Links'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('facebook_url') ?: 'Facebook URL'; ?></label>
                                <input name="facebook" type="text" class="form-control" value="<?php echo $staff["facebook"] ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('twitter_url') ?: 'Twitter URL'; ?></label>
                                <input name="twitter" type="text" class="form-control" value="<?php echo $staff["twitter"] ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('linkedin_url') ?: 'LinkedIn URL'; ?></label>
                                <input name="linkedin" type="text" class="form-control" value="<?php echo $staff["linkedin"] ?>" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('instagram_url') ?: 'Instagram URL'; ?></label>
                                <input name="instagram" type="text" class="form-control" value="<?php echo $staff["instagram"] ?>" />
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
                    <i class="fa fa-file-text-o"></i>
                    <h3><?php echo $this->lang->line('upload_documents') ?: 'Upload Documents'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>1. <?php echo $this->lang->line('resume') ?: 'Resume'; ?></label>
                                <input class="form-control" type="file" name="first_doc" id="doc1">
                                <input type="hidden" name="resume" value="<?php echo $staff["resume"] ?>">
                                <?php if (!empty($staff["resume"])) { ?>
                                    <span class="field-hint"><i class="fa fa-paperclip"></i> <?php echo basename($staff["resume"]); ?></span>
                                <?php } ?>
                                <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>2. <?php echo $this->lang->line('joining_letter') ?: 'Joining Letter'; ?></label>
                                <input class="form-control" type="file" name="second_doc" id="doc2">
                                <input type="hidden" name="joining_letter" value="<?php echo $staff["joining_letter"] ?>">
                                <?php if (!empty($staff["joining_letter"])) { ?>
                                    <span class="field-hint"><i class="fa fa-paperclip"></i> <?php echo basename($staff["joining_letter"]); ?></span>
                                <?php } ?>
                                <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>3. <?php echo $this->lang->line('resignation_letter') ?: 'Resignation Letter'; ?></label>
                                <input class="form-control" type="file" name="third_doc" id="doc3">
                                <input type="hidden" name="resignation_letter" value="<?php echo $staff["resignation_letter"] ?>">
                                <?php if (!empty($staff["resignation_letter"])) { ?>
                                    <span class="field-hint"><i class="fa fa-paperclip"></i> <?php echo basename($staff["resignation_letter"]); ?></span>
                                <?php } ?>
                                <span class="text-danger"><?php echo form_error('third_doc'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>4. <?php echo $this->lang->line('other_documents') ?: 'Other Documents'; ?></label>
                                <input class="form-control" type="file" name="fourth_doc" id="doc4">
                                <input type="hidden" name="fourth_title" value="<?php echo $staff["other_document_file"] ?>">
                                <input type="hidden" name="other_document_file" value="<?php echo $staff["other_document_file"] ?>">
                                <?php if (!empty($staff["other_document_file"])) { ?>
                                    <span class="field-hint"><i class="fa fa-paperclip"></i> <?php echo basename($staff["other_document_file"]); ?></span>
                                <?php } ?>
                                <span class="text-danger"><?php echo form_error('fourth_doc'); ?></span>
                            </div>
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
                    <i class="fa fa-check"></i> <?php echo $this->lang->line('save') ?: 'Save'; ?>
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
    $('.select2-multi-role').select2({ placeholder: 'Select Role(s)', width: '100%' });
    // ---- Wizard Navigation ----
    var totalSteps = $('.wizard-step-btn').length;
    var currentStep = 1;

    <?php if (!empty($validation_errors)) { ?>
    <?php
    $step_for_error = 1;
    if (form_error('name') || form_error('email') || form_error('gender') || form_error('dob') || form_error('contactno') || form_error('emergency_no') || form_error('marital_status')) {
        $step_for_error = 2;
    } elseif (form_error('qualification') || form_error('aadhaar_no') || form_error('religion') || form_error('caste')) {
        $step_for_error = 3;
    } elseif (form_error('esi_no') || form_error('bank_account_no') || form_error('ifsc_code') || form_error('date_of_leaving')) {
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

    // ---- Submit button loading state ----
    $('#form1').submit(function() {
        $("#submitbtn").html('<i class="fa fa-spinner fa-spin"></i> <?php echo $this->lang->line("loading") ?: "Saving..."; ?>').prop('disabled', true);
    });

    // ---- Photo preview ----
    var fileInput = document.getElementById('file');
    var photoPreview = document.getElementById('staff_photo_preview');
    if (fileInput && photoPreview) {
        fileInput.addEventListener('change', function(e) {
            var f = e.target.files && e.target.files[0] ? e.target.files[0] : null;
            if (!f) return;
            var reader = new FileReader();
            reader.onload = function(ev) { photoPreview.src = ev.target.result; };
            reader.readAsDataURL(f);
        });
    }

    // ---- Date format ----
    var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
});

function updateStaffType() {
    var designationSelect = document.getElementById('designation');
    if (!designationSelect) return;
    var selectedOption = designationSelect.options[designationSelect.selectedIndex];
    var categoryName = selectedOption.getAttribute('data-category');
    var categoryColor = selectedOption.getAttribute('data-color');
    var categoryIcon = selectedOption.getAttribute('data-icon');
    var staffTypeDisplay = document.getElementById('staff_type_display');

    if (!staffTypeDisplay) return;

    if (categoryName && categoryName.trim() !== '') {
        staffTypeDisplay.innerHTML = '<i class="fa ' + categoryIcon + '" style="color: ' + categoryColor + '; margin-right: 8px;"></i> <span style="border-left: 4px solid ' + categoryColor + '; padding-left: 8px; font-weight: 500;">' + categoryName + '</span>';
    } else {
        staffTypeDisplay.innerHTML = '<span class="text-muted">No category assigned to this designation</span>';
    }
}

// Trigger on page load if designation is already selected
window.addEventListener('DOMContentLoaded', function(){
    var designationSelect = document.getElementById('designation');
    if (designationSelect && designationSelect.value !== '') {
        updateStaffType();
    }
});
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>
