<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$is_college = ($sch_setting->institution_type == 'college');
?>
<link href="<?php echo base_url(); ?>backend/multiselect/css/jquery.multiselect.css" rel="stylesheet">
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/admission-wizard.css">

<div class="content-wrapper" style="min-height: 946px;">
<section class="content">
<div class="mn-admission-wizard">

    <!-- Page Header -->
    <div class="adm-page-header">
        <h1 class="adm-page-title"><i class="fa fa-user-plus" style="color:var(--primary);margin-right:8px;"></i><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('admission'); ?></h1>
        <div class="adm-page-actions">
            <?php if ($this->rbac->hasPrivilege('import_student', 'can_view')) { ?>
                <a href="<?php echo site_url('student/import') ?>" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> <?php echo $this->lang->line('import_student'); ?></a>
            <?php } ?>
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

    <!-- Step Indicator -->
    <div class="wizard-steps-bar">
        <button type="button" class="wizard-step-btn active" data-step="1">
            <span class="step-number">1</span>
            <span class="step-label"><?php echo $this->lang->line('class') ?: 'Academic'; ?> Info</span>
        </button>
        <button type="button" class="wizard-step-btn" data-step="2">
            <span class="step-number">2</span>
            <span class="step-label"><?php echo $this->lang->line('student') ?: 'Personal'; ?> Details</span>
        </button>
        <?php if (($sch_setting->father_name) || ($sch_setting->mother_name) || ($sch_setting->guardian_name)) { ?>
        <button type="button" class="wizard-step-btn" data-step="3">
            <span class="step-number">3</span>
            <span class="step-label"><?php echo $this->lang->line('parent_guardian_detail') ?: 'Parents'; ?></span>
        </button>
        <?php } ?>
        <button type="button" class="wizard-step-btn" data-step="4">
            <span class="step-number">4</span>
            <span class="step-label"><?php echo $this->lang->line('fees') ?: 'Fees'; ?> & More</span>
        </button>
    </div>

    <!-- FORM START -->
    <form id="form1" action="<?php echo site_url('student/create') ?>" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
        <?php echo $this->customlib->getCSRF(); ?>
        <input type="hidden" name="sibling_name" value="<?php echo set_value('sibling_name'); ?>" id="sibling_name_next">
        <input type="hidden" name="sibling_id" value="<?php echo set_value('sibling_id', 0); ?>" id="sibling_id">
        <input type="hidden" name="fees_discount" value="<?php echo set_value('fees_discount', 0); ?>">
        <input type="hidden" name="total_post_fees" value="<?php
            $view_total_fees = 0;
            foreach ($feesessiongroup_model as $fsg) {
                $tf = 0;
                foreach ($fsg->feetypes as $ft) { $tf += $ft->amount; }
                if (isset($_POST['fee_session_group_id']) && in_array($fsg->id, $_POST['fee_session_group_id'])) {
                    $view_total_fees += $tf;
                }
            }
            echo $view_total_fees;
        ?>">

        <!-- ============================================================
             STEP 1 — ACADEMIC INFORMATION
             ============================================================ -->
        <div class="wizard-panel active" data-panel="1">

            <!-- Admission & IDs -->
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-id-card-o"></i>
                    <h3><?php echo $this->lang->line('admission_no') ?: 'Admission'; ?> & <?php echo $this->lang->line('class') ?: 'Class'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('admission_no'); ?> <span class="req">*</span></label>
                                <?php if ($adm_auto_insert) { ?>
                                    <input id="admission_no" type="text" class="form-control" value="<?php echo $next_admission_no; ?>" readonly />
                                    <span class="field-hint">Auto-generated</span>
                                <?php } else { ?>
                                    <input autofocus id="admission_no" name="admission_no" type="text" class="form-control" value="<?php echo set_value('admission_no'); ?>" placeholder="e.g. STD2026001" />
                                    <span class="text-danger"><?php echo form_error('admission_no'); ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if ($is_college) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department <span class="req">*</span></label>
                                <select id="department_id" name="department_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($department_list as $department) { ?>
                                        <option value="<?php echo $department['id'] ?>" <?php if (set_value('department_id') == $department['id']) echo "selected"; ?>><?php echo $department['department_name'] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('department_id'); ?></span>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('class'); ?> <span class="req">*</span></label>
                                <select id="class_id" name="class_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($classlist as $class) { ?>
                                        <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) echo "selected"; ?>><?php echo $class['class'] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('section'); ?> <span class="req">*</span></label>
                                <select id="section_id" name="section_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($sch_setting->roll_no) { ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('roll_number'); ?></label>
                                <input id="roll_no" name="roll_no" type="text" class="form-control" value="<?php echo set_value('roll_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('roll_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Register No</label>
                                <input id="register_no" name="register_no" type="text" class="form-control" value="<?php echo set_value('register_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('register_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Regulation</label>
                                <input id="regulation_id" name="regulation_id" type="text" class="form-control" value="<?php echo set_value('regulation_id'); ?>" />
                                <span class="text-danger"><?php echo form_error('regulation_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>EMIS Number</label>
                                <input id="emis_num" name="emis_num" type="text" class="form-control" value="<?php echo set_value('emis_num'); ?>" />
                                <span class="text-danger"><?php echo form_error('emis_num'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>HSC Reg No</label>
                                <input id="hsc_reg_no" name="hsc_reg_no" type="text" class="form-control" value="<?php echo set_value('hsc_reg_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('hsc_reg_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>UG Reg No</label>
                                <input id="ug_reg_no" name="ug_reg_no" type="text" class="form-control" value="<?php echo set_value('ug_reg_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('ug_reg_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>ABC ID</label>
                                <input id="abc_id" name="abc_id" type="text" class="form-control" value="<?php echo set_value('abc_id'); ?>" />
                                <span class="text-danger"><?php echo form_error('abc_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Application No</label>
                                <input id="application_no" name="application_no" type="text" class="form-control" value="<?php echo set_value('application_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('application_no'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Allotment No</label>
                                <input id="allotment_no" name="allotment_no" type="text" class="form-control" value="<?php echo set_value('allotment_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('allotment_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Consortium No</label>
                                <input id="consortium_no" name="consortium_no" type="text" class="form-control" value="<?php echo set_value('consortium_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('consortium_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Medium</label>
                                <input id="medium" name="medium" type="text" class="form-control" value="<?php echo set_value('medium'); ?>" />
                                <span class="text-danger"><?php echo form_error('medium'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Migration Certificate Number</label>
                                <input id="migration_cert_num" name="migration_cert_num" type="text" class="form-control" value="<?php echo set_value('migration_cert_num'); ?>" />
                                <span class="text-danger"><?php echo form_error('migration_cert_num'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Father Adhar No</label>
                                <input id="father_adhar_no" name="father_adhar_no" type="text" class="form-control" value="<?php echo set_value('father_adhar_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('father_adhar_no'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Mother Adhar No</label>
                                <input id="mother_adhar_no" name="mother_adhar_no" type="text" class="form-control" value="<?php echo set_value('mother_adhar_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('mother_adhar_no'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <!-- Sibling -->
                    <div class="row" style="margin-top:8px;">
                        <div class="col-md-6">
                            <button type="button" class="btn-wizard mysiblings"><i class="fa fa-link"></i> <?php echo $this->lang->line('add_sibling'); ?></button>
                            <span id="sibling_name" class="sibling-badge" style="<?php echo set_value('sibling_name') ? '' : 'display:none;'; ?>"><?php echo set_value('sibling_name'); ?></span>
                        </div>
                    </div>
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
                    <h3><?php echo $this->lang->line('student') ?: 'Student'; ?> <?php echo $this->lang->line('profile') ?: 'Details'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('first_name'); ?> <span class="req">*</span></label>
                                <input id="firstname" name="firstname" type="text" class="form-control" value="<?php echo set_value('firstname'); ?>" placeholder="First Name" />
                                <span class="text-danger"><?php echo form_error('firstname'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->middlename) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('middle_name'); ?></label>
                                <input id="middlename" name="middlename" type="text" class="form-control" value="<?php echo set_value('middlename'); ?>" />
                                <span class="text-danger"><?php echo form_error('middlename'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->lastname) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('last_name'); ?></label>
                                <input id="lastname" name="lastname" type="text" class="form-control" value="<?php echo set_value('lastname'); ?>" />
                                <span class="text-danger"><?php echo form_error('lastname'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('gender'); ?> <span class="req">*</span></label>
                                <select class="form-control" name="gender">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($genderList as $key => $value) { ?>
                                        <option value="<?php echo $key; ?>" <?php if (set_value('gender') == $key) echo "selected"; ?>><?php echo $value; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date_of_birth'); ?> <span class="req">*</span></label>
                                <input id="dob" name="dob" type="text" class="form-control date" value="<?php echo set_value('dob'); ?>" placeholder="DD/MM/YYYY" />
                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->category) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('category'); ?></label>
                                <select id="category_id" name="category_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($categorylist as $category) { ?>
                                        <option value="<?php echo $category['id'] ?>" <?php if (set_value('category_id') == $category['id']) echo "selected"; ?>><?php echo $category['category'] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('category_id'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->religion) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('religion'); ?></label>
                                <input id="religion" name="religion" type="text" class="form-control" value="<?php echo set_value('religion'); ?>" />
                                <span class="text-danger"><?php echo form_error('religion'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->cast) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('caste'); ?></label>
                                <input id="cast" name="cast" type="text" class="form-control" value="<?php echo set_value('cast'); ?>" />
                                <span class="text-danger"><?php echo form_error('cast'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->mobile_no) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mobile_number'); ?></label>
                                <input id="mobileno" name="mobileno" type="text" class="form-control" value="<?php echo set_value('mobileno'); ?>" placeholder="Mobile Number" />
                                <span class="text-danger"><?php echo form_error('mobileno'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_email) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('email'); ?></label>
                                <input id="email" name="email" type="text" class="form-control" value="<?php echo set_value('email'); ?>" placeholder="student@email.com" />
                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->admission_date) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('admission_date'); ?></label>
                                <input id="admission_date" name="admission_date" type="text" class="form-control date" value="<?php echo set_value('admission_date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" />
                                <span class="text-danger"><?php echo form_error('admission_date'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->is_blood_group) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('blood_group'); ?></label>
                                <select class="form-control" name="blood_group">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($bloodgroup as $bgkey => $bgvalue) { ?>
                                        <option value="<?php echo $bgvalue ?>"><?php echo $bgvalue ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('blood_group'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <?php if ($sch_setting->is_student_house) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('house') ?></label>
                                <select class="form-control" name="house">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($houses as $hvalue) { ?>
                                        <option value="<?php echo $hvalue["id"] ?>"><?php echo $hvalue["house_name"] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('house'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_height) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('height'); ?></label>
                                <input type="text" name="height" class="form-control" value="<?php echo set_value('height'); ?>">
                                <span class="text-danger"><?php echo form_error('height'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_weight) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('weight'); ?></label>
                                <input type="text" name="weight" class="form-control" value="<?php echo set_value('weight'); ?>">
                                <span class="text-danger"><?php echo form_error('weight'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->measurement_date) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('measurement_date'); ?></label>
                                <input type="text" id="measure_date" value="<?php echo set_value('measure_date', date($this->customlib->getSchoolDateFormat())); ?>" name="measure_date" class="form-control date">
                                <span class="text-danger"><?php echo form_error('measure_date'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if ($sch_setting->student_photo) { ?>
                    <div class="row" style="margin-top:4px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('student_photo'); ?></label>
                                <div class="photo-upload-zone">
                                    <div class="photo-preview" id="student_photo_preview">
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
                    <?php $cf_html = display_custom_fields('students');
                    if (!empty(trim($cf_html))) { ?>
                    <div class="row" style="margin-top:8px;">
                        <?php echo $cf_html; ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- ============================================================
             STEP 3 — PARENT / GUARDIAN DETAILS
             ============================================================ -->
        <?php if (($sch_setting->father_name) || ($sch_setting->father_phone) || ($sch_setting->father_occupation) || ($sch_setting->father_pic) || ($sch_setting->mother_name) || ($sch_setting->mother_phone) || ($sch_setting->mother_occupation) || ($sch_setting->mother_pic) || ($sch_setting->guardian_name) || ($sch_setting->guardian_occupation) || ($sch_setting->guardian_relation) || ($sch_setting->guardian_phone) || ($sch_setting->guardian_email) || ($sch_setting->guardian_pic) || ($sch_setting->guardian_address)) { ?>
        <div class="wizard-panel" data-panel="3">

            <?php if (($sch_setting->father_name) || ($sch_setting->father_phone) || ($sch_setting->father_occupation) || ($sch_setting->father_pic)) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-male"></i>
                    <h3><?php echo $this->lang->line('father_name') ?: "Father"; ?> Details</h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if ($sch_setting->father_name) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_name'); ?></label>
                                <input id="father_name" name="father_name" type="text" class="form-control" value="<?php echo set_value('father_name'); ?>" />
                                <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->father_phone) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_phone'); ?></label>
                                <input id="father_phone" name="father_phone" type="text" class="form-control" value="<?php echo set_value('father_phone'); ?>" />
                                <span class="text-danger"><?php echo form_error('father_phone'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->father_occupation) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_occupation'); ?></label>
                                <input id="father_occupation" name="father_occupation" type="text" class="form-control" value="<?php echo set_value('father_occupation'); ?>" />
                                <span class="text-danger"><?php echo form_error('father_occupation'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->father_pic) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('father_photo'); ?></label>
                                <input class="form-control" type="file" name="father_pic" accept="image/*" />
                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php if (($sch_setting->mother_name) || ($sch_setting->mother_phone) || ($sch_setting->mother_occupation) || ($sch_setting->mother_pic)) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-female"></i>
                    <h3><?php echo $this->lang->line('mother_name') ?: "Mother"; ?> Details</h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if ($sch_setting->mother_name) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mother_name'); ?></label>
                                <input id="mother_name" name="mother_name" type="text" class="form-control" value="<?php echo set_value('mother_name'); ?>" />
                                <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mother_phone) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mother_phone'); ?></label>
                                <input id="mother_phone" name="mother_phone" type="text" class="form-control" value="<?php echo set_value('mother_phone'); ?>" />
                                <span class="text-danger"><?php echo form_error('mother_phone'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mother_occupation) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mother_occupation'); ?></label>
                                <input id="mother_occupation" name="mother_occupation" type="text" class="form-control" value="<?php echo set_value('mother_occupation'); ?>" />
                                <span class="text-danger"><?php echo form_error('mother_occupation'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mother_pic) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('mother_photo'); ?></label>
                                <input class="form-control" type="file" name="mother_pic" accept="image/*" />
                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php if ($sch_setting->guardian_name) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-shield"></i>
                    <h3><?php echo $this->lang->line('guardian_name') ?: 'Guardian'; ?> Details</h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('if_guardian_is'); ?> <span class="req">*</span></label>
                                <div class="guardian-radio-group">
                                    <label class="<?php echo set_value('guardian_is') == 'father' ? 'selected' : ''; ?>">
                                        <input type="radio" name="guardian_is" value="father" <?php echo set_value('guardian_is') == "father" ? "checked" : ""; ?>>
                                        <span><i class="fa fa-male"></i> <?php echo $this->lang->line('father'); ?></span>
                                    </label>
                                    <label class="<?php echo set_value('guardian_is') == 'mother' ? 'selected' : ''; ?>">
                                        <input type="radio" name="guardian_is" value="mother" <?php echo set_value('guardian_is') == "mother" ? "checked" : ""; ?>>
                                        <span><i class="fa fa-female"></i> <?php echo $this->lang->line('mother'); ?></span>
                                    </label>
                                    <label class="<?php echo set_value('guardian_is') == 'other' ? 'selected' : ''; ?>">
                                        <input type="radio" name="guardian_is" value="other" <?php echo set_value('guardian_is') == "other" ? "checked" : ""; ?>>
                                        <span><i class="fa fa-user"></i> <?php echo $this->lang->line('other'); ?></span>
                                    </label>
                                </div>
                                <span class="text-danger"><?php echo form_error('guardian_is'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_name'); ?> <span class="req">*</span></label>
                                <input id="guardian_name" name="guardian_name" type="text" class="form-control" value="<?php echo set_value('guardian_name'); ?>" />
                                <span class="text-danger"><?php echo form_error('guardian_name'); ?></span>
                            </div>
                        </div>
                        <?php if ($sch_setting->guardian_relation) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_relation'); ?></label>
                                <input id="guardian_relation" name="guardian_relation" type="text" class="form-control" value="<?php echo set_value('guardian_relation'); ?>" />
                                <span class="text-danger"><?php echo form_error('guardian_relation'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_phone) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_phone'); ?> <span class="req">*</span></label>
                                <input id="guardian_phone" name="guardian_phone" type="text" class="form-control" value="<?php echo set_value('guardian_phone'); ?>" />
                                <span class="text-danger"><?php echo form_error('guardian_phone'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_occupation) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_occupation'); ?></label>
                                <input id="guardian_occupation" name="guardian_occupation" type="text" class="form-control" value="<?php echo set_value('guardian_occupation'); ?>" />
                                <span class="text-danger"><?php echo form_error('guardian_occupation'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <?php if ($sch_setting->guardian_email) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_email'); ?></label>
                                <input id="guardian_email" name="guardian_email" type="text" class="form-control" value="<?php echo set_value('guardian_email'); ?>" />
                                <span class="text-danger"><?php echo form_error('guardian_email'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_pic) { ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_photo'); ?></label>
                                <input class="form-control" type="file" name="guardian_pic" accept="image/*" />
                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_address) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('guardian_address'); ?></label>
                                <textarea id="guardian_address" name="guardian_address" class="form-control" rows="2"><?php echo set_value('guardian_address'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('guardian_address'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <!-- ============================================================
             STEP 4 — FEES, TRANSPORT, ADDRESS & DOCUMENTS
             ============================================================ -->
        <div class="wizard-panel" data-panel="4">

            <!-- Transport -->
            <?php if ($sch_setting->route_list && $this->module_lib->hasActive('transport')) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-bus"></i>
                    <h3><?php echo $this->lang->line('transport_details'); ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('route_list'); ?></label>
                                <select class="form-control" id="vehroute_id" name="vehroute_id">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($vehroutelist as $vehroute) { ?>
                                        <optgroup label="<?php echo $vehroute['route_title']; ?>">
                                            <?php if (!empty($vehroute['vehicles'])) {
                                                foreach ($vehroute['vehicles'] as $value) { ?>
                                                    <option value="<?php echo $value->vec_route_id ?>" <?php echo set_select('vehroute_id', $value->vec_route_id); ?>><?php echo $value->vehicle_no ?></option>
                                            <?php }
                                            } ?>
                                        </optgroup>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('vehroute_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('pickup_point'); ?></label>
                                <select class="form-control" id="pickup_point" name="route_pickup_point_id"></select>
                                <span class="text-danger"><?php echo form_error('route_pickup_point_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('fees_month'); ?></label>
                                <select class="form-control" id="transport_feemaster_id" name="transport_feemaster_id[]" multiple="multiple">
                                    <?php foreach ($transport_fees as $value) { ?>
                                        <option <?php echo set_select('transport_feemaster_id[]', $value['id']); ?> value="<?php echo $value['id']; ?>"><?php echo $this->lang->line(strtolower($value['month'])); ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('transport_feemaster_id[]'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Hostel -->
            <?php if ($sch_setting->route_list && $this->module_lib->hasActive('hostel') && $sch_setting->hostel_id) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-building"></i>
                    <h3><?php echo $this->lang->line('hostel_details'); ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('hostel'); ?></label>
                                <select class="form-control" id="hostel_id" name="hostel_id">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($hostelList as $hostel_value) { ?>
                                        <option value="<?php echo $hostel_value['id'] ?>" <?php echo set_select('hostel_id', $hostel_value['id']); ?>><?php echo $hostel_value['hostel_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('hostel_id'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('room_no'); ?></label>
                                <select id="hostel_room_id" name="hostel_room_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('hostel_room_id'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Fee Groups -->
            <?php if (!empty($feesessiongroup_model)) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-money"></i>
                    <h3><?php echo $this->lang->line('fees_details'); ?></h3>
                </div>
                <div class="adm-card-body">
                    <?php foreach ($feesessiongroup_model as $fsg) {
                        $total_fees = 0;
                        foreach ($fsg->feetypes as $ft) { $total_fees += $ft->amount; }
                    ?>
                    <div class="fee-group-item">
                        <div class="fee-group-header">
                            <label>
                                <input class="fee_group_chk" type="checkbox" name="fee_session_group_id[]" value="<?php echo $fsg->id; ?>" <?php echo set_checkbox('fee_session_group_id[]', $fsg->id); ?>>
                                <?php echo $fsg->group_name; ?>
                            </label>
                            <span class="fee-amount fee_group_total" data-amount="<?php echo $total_fees; ?>"><?php echo amountFormat($total_fees); ?></span>
                        </div>
                        <div class="fee-group-details" style="display:none;">
                            <?php foreach ($fsg->feetypes as $ft) { ?>
                            <div class="fee-detail-row">
                                <span><?php echo $ft->type . " (" . $ft->code . ")"; ?></span>
                                <span style="color:var(--text-muted);font-size:12px;"><i class="fa fa-calendar"></i> <?php echo $this->customlib->dateformat($ft->due_date); ?></span>
                                <span style="font-weight:600;"><?php echo amountFormat($ft->amount); ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="total-fee-bar">
                        <span class="total-label"><?php echo $this->lang->line('total') ?: 'Total'; ?> <?php echo $this->lang->line('fees') ?: 'Fees'; ?></span>
                        <span class="total-amount total_fees_alloted"><?php if (!empty($view_total_fees)) echo amountFormat($view_total_fees); else echo amountFormat(0); ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Fee Discounts -->
            <?php if (!empty($feediscountList)) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-percent"></i>
                    <h3><?php echo $this->lang->line('fees_discount_details'); ?></h3>
                </div>
                <div class="adm-card-body">
                    <?php foreach ($feediscountList as $feediscount) { ?>
                    <div class="fee-group-item">
                        <div class="fee-group-header">
                            <label>
                                <input class="discount_group_chk" type="checkbox" name="discount_id[]" value="<?php echo $feediscount['id']; ?>" <?php echo set_checkbox('discount_id[]', $feediscount['id']); ?>>
                                <?php echo $feediscount['name'] . " - " . $feediscount['code']; ?>
                            </label>
                            <span class="fee-amount discount_group_total" data-discount="<?php echo $feediscount['amount']; ?>">
                                <?php
                                if (isset($feediscount['type']) && $feediscount['type'] == "percentage") {
                                    echo $feediscount['percentage'] . "%";
                                } elseif (isset($feediscount['amount']) && $feediscount['amount'] > 0) {
                                    echo amountFormat($feediscount['amount']);
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- Address -->
            <?php if ($sch_setting->current_address || $sch_setting->permanent_address) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-map-marker"></i>
                    <h3><?php echo $this->lang->line('student_address_details'); ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if ($sch_setting->current_address) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <?php echo $this->lang->line('current_address'); ?>
                                    <label style="font-weight:normal;text-transform:none;letter-spacing:0;display:inline;margin-left:12px;font-size:12px;">
                                        <input type="checkbox" id="autofill_current_address" onclick="return auto_fill_guardian_address();">
                                        <?php echo $this->lang->line('if_guardian_address_is_current_address'); ?>
                                    </label>
                                </label>
                                <textarea id="current_address" name="current_address" class="form-control" rows="3"><?php echo set_value('current_address'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('current_address'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->permanent_address) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <?php echo $this->lang->line('permanent_address'); ?>
                                    <label style="font-weight:normal;text-transform:none;letter-spacing:0;display:inline;margin-left:12px;font-size:12px;">
                                        <input type="checkbox" id="autofill_address" onclick="return auto_fill_address();">
                                        <?php echo $this->lang->line('if_permanent_address_is_current_address'); ?>
                                    </label>
                                </label>
                                <textarea id="permanent_address" name="permanent_address" class="form-control" rows="3"><?php echo set_value('permanent_address'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('permanent_address'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Bank & IDs -->
            <?php if ($sch_setting->bank_account_no || $sch_setting->bank_name || $sch_setting->ifsc_code || $sch_setting->national_identification_no || $sch_setting->local_identification_no || $sch_setting->rte) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-university"></i>
                    <h3><?php echo $this->lang->line('miscellaneous_details') ?: 'Bank & ID Details'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if ($sch_setting->bank_account_no) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_account_number'); ?></label>
                                <input id="bank_account_no" name="bank_account_no" type="text" class="form-control" value="<?php echo set_value('bank_account_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->bank_name) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('bank_name'); ?></label>
                                <input id="bank_name" name="bank_name" type="text" class="form-control" value="<?php echo set_value('bank_name'); ?>" />
                                <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->ifsc_code) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('ifsc_code'); ?></label>
                                <input id="ifsc_code" name="ifsc_code" type="text" class="form-control" value="<?php echo set_value('ifsc_code'); ?>" />
                                <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <?php if ($sch_setting->national_identification_no) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('national_identification_number'); ?></label>
                                <input id="adhar_no" name="adhar_no" type="text" class="form-control" value="<?php echo set_value('adhar_no'); ?>" />
                                <span class="text-danger"><?php echo form_error('adhar_no'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->local_identification_no) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('local_identification_number'); ?></label>
                                <input id="samagra_id" name="samagra_id" type="text" class="form-control" value="<?php echo set_value('samagra_id'); ?>" />
                                <span class="text-danger"><?php echo form_error('samagra_id'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->rte) { ?>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('rte'); ?></label>
                                <div style="padding-top:6px;">
                                    <label style="font-weight:normal;text-transform:none;letter-spacing:0;display:inline;margin-right:16px;">
                                        <input type="radio" name="rte" value="Yes" <?php echo set_value('rte') == "yes" ? "checked" : ""; ?>> <?php echo $this->lang->line('yes'); ?>
                                    </label>
                                    <label style="font-weight:normal;text-transform:none;letter-spacing:0;display:inline;">
                                        <input type="radio" name="rte" value="No" checked <?php echo set_value('rte') == "no" ? "checked" : ""; ?>> <?php echo $this->lang->line('no'); ?>
                                    </label>
                                </div>
                                <span class="text-danger"><?php echo form_error('rte'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Previous School & Notes -->
            <?php if ($sch_setting->previous_school_details || $sch_setting->student_note) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-file-text-o"></i>
                    <h3><?php echo $this->lang->line('note') ?: 'Notes'; ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <?php if ($sch_setting->previous_school_details) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('previous_school_details'); ?></label>
                                <textarea class="form-control" rows="3" name="previous_school"><?php echo set_value('previous_school'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('previous_school'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_note) { ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('note'); ?></label>
                                <textarea class="form-control" rows="3" name="note"><?php echo set_value('note'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('note'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- Documents -->
            <?php if ($sch_setting->upload_documents) { ?>
            <div class="adm-card">
                <div class="adm-card-header">
                    <i class="fa fa-paperclip"></i>
                    <h3><?php echo $this->lang->line('upload_documents'); ?></h3>
                </div>
                <div class="adm-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="doc-upload-row">
                                <span class="doc-num">1</span>
                                <input type="text" name="first_title" class="form-control" placeholder="Document title">
                                <input class="form-control" type="file" name="first_doc">
                            </div>
                            <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                            <div class="doc-upload-row">
                                <span class="doc-num">2</span>
                                <input type="text" name="second_title" class="form-control" placeholder="Document title">
                                <input class="form-control" type="file" name="second_doc">
                            </div>
                            <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                        </div>
                        <div class="col-md-6">
                            <div class="doc-upload-row">
                                <span class="doc-num">3</span>
                                <input type="text" name="fourth_title" class="form-control" placeholder="Document title">
                                <input class="form-control" type="file" name="fourth_doc">
                            </div>
                            <span class="text-danger"><?php echo form_error('fourth_doc'); ?></span>
                            <div class="doc-upload-row">
                                <span class="doc-num">4</span>
                                <input type="text" name="fifth_title" class="form-control" placeholder="Document title">
                                <input class="form-control" type="file" name="fifth_doc">
                            </div>
                            <span class="text-danger"><?php echo form_error('fifth_doc'); ?></span>
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
                <button type="submit" class="btn-wizard btn-wizard-success" id="addloader" style="display:none;">
                    <i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?> <?php echo $this->lang->line('student') ?: 'Student'; ?>
                </button>
            </div>
        </div>
    </form>

</div>
</section>
</div>

<!-- Sibling Modal -->
<div class="modal fade" id="mySiblingModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title modal_title"></h4>
            </div>
            <div class="modal-body pb0">
                <div class="form-horizontal">
                    <div class="box-body pt0 pb0">
                        <input type="hidden" class="form-control" id="transport_student_session_id" value="0" readonly="readonly"/>
                        <div class="form-group">
                            <div class="sibling_msg"></div>
                            <label for="inputEmail3" class="col-sm-2 control-label"><?php echo $this->lang->line('class'); ?></label>
                            <div class="col-sm-10">
                                <select id="sibiling_class_id" name="sibiling_class_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($classlist as $class) { ?>
                                        <option value="<?php echo $class['id'] ?>" <?php if (set_value('sibiling_class_id') == $class['id']) echo "selected"; ?>><?php echo $class['class'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label"><?php echo $this->lang->line('section'); ?></label>
                            <div class="col-sm-10">
                                <select id="sibiling_section_id" name="sibiling_section_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label"><?php echo $this->lang->line('student'); ?></label>
                            <div class="col-sm-10">
                                <select id="sibiling_student_id" name="sibiling_student_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary add_sibling" id="load"><i class="fa fa-user"></i> <?php echo $this->lang->line('add'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     JAVASCRIPT
     ============================================================ -->
<script>
$(document).ready(function () {
    // ---- Wizard Navigation ----
    var totalSteps = $('.wizard-step-btn').length;
    var currentStep = 1;

    <?php if (!empty($validation_errors)) { ?>
    var errorFields = '<?php echo addslashes($validation_errors); ?>';
    <?php
    $step_for_error = 1;
    if (form_error('firstname') || form_error('gender') || form_error('dob') || form_error('email') || form_error('mobileno')) {
        $step_for_error = 2;
    } elseif (form_error('guardian_name') || form_error('guardian_is') || form_error('guardian_phone')) {
        $step_for_error = 3;
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
            $('#addloader').show();
        } else {
            $('#wizardNext').show();
            $('#addloader').hide();
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

    // ---- Form Validation on Submit ----
    $('#form1').on('submit', function(e) {
        var errors = [];
        var errorStep = 0;
        var institution_type = '<?php echo $sch_setting->institution_type; ?>';

        <?php if (!$adm_auto_insert) { ?>
        if (!$.trim($('#admission_no').val())) { errors.push('<?php echo $this->lang->line("admission_no"); ?>'); if (!errorStep) errorStep = 1; }
        <?php } ?>
        if (institution_type === 'college' && !$('#department_id').val()) { errors.push('<?php echo $this->lang->line("department") ?: "Department"; ?>'); if (!errorStep) errorStep = 1; }
        if (!$('#class_id').val()) { errors.push('<?php echo $this->lang->line("class"); ?>'); if (!errorStep) errorStep = 1; }
        if (!$('#section_id').val()) { errors.push('<?php echo $this->lang->line("section"); ?>'); if (!errorStep) errorStep = 1; }

        if (!$.trim($('#firstname').val())) { errors.push('<?php echo $this->lang->line("first_name"); ?>'); if (!errorStep) errorStep = 2; }
        if (!$('select[name="gender"]').val()) { errors.push('<?php echo $this->lang->line("gender"); ?>'); if (!errorStep) errorStep = 2; }
        if (!$.trim($('#dob').val())) { errors.push('<?php echo $this->lang->line("date_of_birth"); ?>'); if (!errorStep) errorStep = 2; }

        <?php if ($sch_setting->guardian_name) { ?>
        if (!$('input[name="guardian_is"]:checked').val()) { errors.push('<?php echo $this->lang->line("if_guardian_is") ?: "Guardian Is"; ?>'); if (!errorStep) errorStep = 3; }
        if (!$.trim($('#guardian_name').val())) { errors.push('<?php echo $this->lang->line("guardian_name"); ?>'); if (!errorStep) errorStep = 3; }
        <?php } ?>
        <?php if ($sch_setting->guardian_phone) { ?>
        if (!$.trim($('#guardian_phone').val())) { errors.push('<?php echo $this->lang->line("guardian_phone"); ?>'); if (!errorStep) errorStep = 3; }
        <?php } ?>

        if (errors.length > 0) {
            e.preventDefault();
            if (errorStep) goToStep(errorStep);
            var msg = '<ul style="text-align:left;margin:10px 0;">';
            for (var i = 0; i < errors.length; i++) {
                msg += '<li>' + errors[i] + ' <?php echo $this->lang->line("is_required") ?: "is required"; ?></li>';
            }
            msg += '</ul>';
            swal({ title: '<?php echo $this->lang->line("required_field") ?: "Required Fields Missing"; ?>', html: msg, type: 'warning', confirmButtonText: 'OK' });
            return false;
        }
        $('#addloader').html('<i class="fa fa-spinner fa-spin"></i> <?php echo $this->lang->line("loading") ?: "Saving..."; ?>');
    });

    // ---- Section loading ----
    var date_format = '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']); ?>';
    var class_id = $('#class_id').val();
    var section_id = '<?php echo set_value('section_id', 0); ?>';
    var hostel_id = $('#hostel_id').val();
    var hostel_room_id = '<?php echo set_value('hostel_room_id', 0); ?>';
    var vehroute_id = '<?php echo set_value('vehroute_id', 0); ?>';
    var route_pickup_point_id = '<?php echo set_value('route_pickup_point_id', 0); ?>';

    getSectionByClass(class_id, section_id);
    getHostel(hostel_id, hostel_room_id);
    get_pickup_point(vehroute_id, route_pickup_point_id);

    $(document).on('change', '#class_id', function () {
        $('#section_id').html("");
        getSectionByClass($(this).val(), 0);
    });

    $(document).on('change', '#hostel_id', function () {
        getHostel($(this).val(), 0);
    });

    $(document).on('change', '#vehroute_id', function () {
        get_pickup_point($(this).val(), 0);
    });

    function getSectionByClass(class_id, section_id) {
        if (class_id != "") {
            $('#section_id').html("");
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: "<?php echo base_url(); ?>sections/getByClass",
                data: { 'class_id': class_id },
                dataType: "json",
                beforeSend: function () { $('#section_id').addClass('dropdownloading'); },
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = (section_id == obj.section_id) ? "selected" : "";
                        div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                },
                complete: function () { $('#section_id').removeClass('dropdownloading'); }
            });
        }
    }

    function getHostel(hostel_id, hostel_room_id) {
        if (!hostel_room_id) hostel_room_id = 0;
        if (hostel_id != "" && hostel_id != null) {
            $('#hostel_room_id').html("");
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: baseurl + "admin/hostelroom/getRoom",
                data: { 'hostel_id': hostel_id },
                dataType: "json",
                beforeSend: function () { $('#hostel_room_id').addClass('dropdownloading'); },
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = (hostel_room_id == obj.id) ? "selected" : "";
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.room_no + " (" + obj.room_type + ")" + "</option>";
                    });
                    $('#hostel_room_id').append(div_data);
                },
                complete: function () { $('#hostel_room_id').removeClass('dropdownloading'); }
            });
        }
    }

    function get_pickup_point(vehroute_id, pickuppoint_id) {
        if (vehroute_id != "" && vehroute_id != null) {
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                url: baseurl + 'admin/pickuppoint/get_pickupdropdownlist',
                type: "POST",
                data: { vehroute_id: vehroute_id },
                dataType: 'json',
                beforeSend: function () { $('#pickup_point').html(''); },
                success: function (res) {
                    $.each(res, function (index, value) {
                        var sel = (pickuppoint_id == value.route_pickup_point_id) ? "selected" : "";
                        div_data += "<option value=" + value.route_pickup_point_id + " " + sel + ">" + value.name + "</option>";
                    });
                    $('#pickup_point').html(div_data);
                }
            });
        }
    }

    // ---- College: Department -> Class cascade ----
    <?php if ($is_college) { ?>
    $(document).on('change', '#department_id', function () {
        $('#class_id').html("");
        $('#section_id').html("");
        var department_id = $(this).val();
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: "<?php echo base_url(); ?>admin/classes/getClassesByDepartment",
            data: { 'department_id': department_id },
            dataType: "json",
            beforeSend: function () { $('#class_id').addClass('dropdownloading'); },
            success: function (data) {
                $.each(data, function (i, obj) {
                    div_data += "<option value=" + obj.id + ">" + obj.class + "</option>";
                });
                $('#class_id').append(div_data);
            },
            complete: function () { $('#class_id').removeClass('dropdownloading'); }
        });
    });
    <?php } ?>

    // ---- Guardian auto-fill ----
    $('input[name="guardian_is"]').change(function () {
        var val = $(this).val();
        $('.guardian-radio-group label').removeClass('selected');
        $(this).closest('label').addClass('selected');
        if (val == "father") {
            $('#guardian_name').val($('#father_name').val());
            $('#guardian_phone').val($('#father_phone').val());
            $('#guardian_occupation').val($('#father_occupation').val());
            $('#guardian_relation').val("<?php echo $this->lang->line('father'); ?>");
        } else if (val == "mother") {
            $('#guardian_name').val($('#mother_name').val());
            $('#guardian_phone').val($('#mother_phone').val());
            $('#guardian_occupation').val($('#mother_occupation').val());
            $('#guardian_relation').val("<?php echo $this->lang->line('mother'); ?>");
        } else {
            $('#guardian_name').val(""); $('#guardian_phone').val(""); $('#guardian_occupation').val(""); $('#guardian_relation').val("");
        }
    });

    // ---- Photo preview ----
    $('#file').on('change', function () {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#student_photo_preview').html('<img src="' + e.target.result + '">');
        };
        if (this.files[0]) reader.readAsDataURL(this.files[0]);
    });

    // ---- Fee group toggle details ----
    $('.fee-group-header').on('click', function (e) {
        if ($(e.target).is('input')) return;
        $(this).closest('.fee-group-item').find('.fee-group-details').slideToggle(200);
    });

    // ---- Fee total calculation ----
    var total_fees_alloted = parseFloat($("input[name='total_post_fees']").val()) || 0;
    $(document).on('change', '.fee_group_chk', function () {
        var amt = parseFloat($(this).closest('.fee-group-header').find('.fee_group_total').data('amount'));
        if ($(this).prop("checked")) { total_fees_alloted += amt; } else { total_fees_alloted -= amt; }
        $("input[name='total_post_fees']").val(total_fees_alloted);
        $.ajax({
            type: "POST",
            url: base_url + "admin/currency/getAmountFormat",
            data: { 'total_fees_alloted': total_fees_alloted },
            dataType: "json",
            success: function (data) { $('.total_fees_alloted').text(data.amount); }
        });
    });

    // ---- Sibling Modal ----
    $(".mysiblings").click(function () {
        $('.sibling_msg').html("");
        $('.modal_title').html('<b><?php echo $this->lang->line('add_sibling'); ?></b>');
        $('#mySiblingModal').modal({ backdrop: 'static', keyboard: false, show: true });
    });

    $(document).on('change', '#sibiling_class_id', function () {
        $('#sibiling_section_id').html("");
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: "<?php echo base_url(); ?>sections/getByClass",
            data: { 'class_id': $(this).val() },
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj) {
                    div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                });
                $('#sibiling_section_id').append(div_data);
            }
        });
    });

    $(document).on('change', '#sibiling_section_id', function () {
        $('#sibiling_student_id').html("");
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: "<?php echo base_url(); ?>student/getByClassAndSection",
            data: { 'class_id': $('#sibiling_class_id').val(), 'section_id': $(this).val() },
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj) {
                    var label = obj.admission_no ? obj.full_name + " (" + obj.admission_no + ")" : obj.full_name;
                    div_data += "<option value=" + obj.id + ">" + label + "</option>";
                });
                $('#sibiling_student_id').append(div_data);
            }
        });
    });

    $(document).on('click', '.add_sibling', function () {
        var student_id = $('#sibiling_student_id').val();
        if (student_id && student_id.length > 0) {
            $.ajax({
                type: "GET",
                url: "<?php echo base_url(); ?>student/getStudentRecordByID",
                data: { 'student_id': student_id },
                dataType: "json",
                success: function (data) {
                    $('#sibling_name').text("<?php echo $this->lang->line('sibling'); ?> : " + data.full_name).show();
                    $('#sibling_name_next').val(data.firstname + " " + data.lastname);
                    $('input[name="sibling_id"]').val(student_id);
                    $('#father_name').val(data.father_name);
                    $('#father_phone').val(data.father_phone);
                    $('#father_occupation').val(data.father_occupation);
                    $('#mother_name').val(data.mother_name);
                    $('#mother_phone').val(data.mother_phone);
                    $('#mother_occupation').val(data.mother_occupation);
                    $('#guardian_name').val(data.guardian_name);
                    $('#guardian_relation').val(data.guardian_relation);
                    $('#guardian_address').val(data.guardian_address);
                    $('#guardian_phone').val(data.guardian_phone);
                    $('#current_address').val(data.current_address);
                    $('#permanent_address').val(data.permanent_address);
                    $('#guardian_occupation').val(data.guardian_occupation);
                    $("input[name=guardian_is][value='" + data.guardian_is + "']").prop("checked", true).closest('label').addClass('selected');
                    $('#mySiblingModal').modal('hide');
                }
            });
        } else {
            $('.sibling_msg').html("<div class='alert alert-danger text-center'><?php echo $this->lang->line('no_student_selected'); ?></div>");
        }
    });

    // ---- Multiselect init ----
    if ($('#transport_feemaster_id').length) {
        $('#transport_feemaster_id').multiselect({ columns: 1, placeholder: '<?php echo $this->lang->line('select_month'); ?>', search: true });
    }
});

function auto_fill_guardian_address() {
    if ($("#autofill_current_address").is(':checked')) {
        $('#current_address').val($('#guardian_address').val());
    }
}

function auto_fill_address() {
    if ($("#autofill_address").is(':checked')) {
        $('#permanent_address').val($('#current_address').val());
    }
}
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>
