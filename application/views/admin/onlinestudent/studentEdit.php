<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<link href="<?php echo base_url(); ?>backend/multiselect/css/jquery.multiselect.css" rel="stylesheet">
<script src="<?php echo base_url(); ?>backend/multiselect/js/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>backend/multiselect/js/jquery.multiselect.js"></script>
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

/* -- Top Bar -- */
.ea-topbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.ea-topbar__left { display: flex; align-items: center; gap: 16px; }
.ea-topbar__title { font-size: 22px; font-weight: 700; color: var(--ea-dark); margin: 0; letter-spacing: -0.3px; }
.ea-btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
    color: var(--ea-text); background: var(--ea-white); border: 1px solid var(--ea-border);
    text-decoration: none; transition: all 0.15s;
}
.ea-btn-back:hover { background: var(--ea-bg); color: var(--ea-dark); text-decoration: none; border-color: #cbd5e1; }

/* -- Section Card -- */
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

/* -- Form Fields -- */
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

/* -- Photo Upload -- */
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

/* -- Footer Actions -- */
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
.ea-btn--success { background: var(--ea-success); color: #fff; }
.ea-btn--success:hover { background: #059669; color: #fff; text-decoration: none; box-shadow: var(--ea-shadow-md); }
.ea-btn--ghost {
    background: transparent; color: var(--ea-text-light); border: 1px solid var(--ea-border);
}
.ea-btn--ghost:hover { background: var(--ea-bg); color: var(--ea-text); text-decoration: none; }

/* -- Two-column grid -- */
.ea-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; }
.ea-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 24px; }
.ea-grid-4 { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0 24px; }
@media (max-width: 992px) { .ea-grid-3, .ea-grid-4 { grid-template-columns: 1fr 1fr; } }
@media (max-width: 768px) { .ea-grid-2, .ea-grid-3, .ea-grid-4 { grid-template-columns: 1fr; } }

/* -- Flash message -- */
.ea-flash { margin-bottom: 20px; }

/* -- Utility -- */
.ea-colspan-full { grid-column: 1 / -1; }
.ea-divider { height: 1px; background: var(--ea-border); margin: 4px 0 18px; grid-column: 1 / -1; }

/* Override AdminLTE content-wrapper bg */
.ea-page.content-wrapper { background: var(--ea-bg) !important; }

/* -- Fee / Discount Panel Overrides -- */
.ea-fee-panel .panel-group1 { margin-bottom: 0; }
.ea-fee-panel .panel-default1 { border: 1.5px solid var(--ea-border); border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
.ea-fee-panel .panel-heading { background: #f8fafc; padding: 10px 16px !important; }
.ea-fee-panel .panel-title1 { font-size: 14px; font-weight: 600; color: var(--ea-dark); display: flex; align-items: center; gap: 8px; overflow: visible; }
.ea-fee-panel .panel-title1 input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--ea-primary); cursor: pointer; flex-shrink: 0; }
.ea-fee-panel .panel-title1 a { color: var(--ea-dark); text-decoration: none; flex: 1; }
.ea-fee-panel .panel-title1 a:hover { color: var(--ea-primary); }
.ea-fee-panel .panel-title1 .fee_group_total,
.ea-fee-panel .panel-title1 .discount_group_total { font-size: 14px; font-weight: 700; color: var(--ea-primary); white-space: nowrap; }
.ea-fee-panel .panel-collapse .list-group { margin: 0; border-radius: 0; }
.ea-fee-panel .panel-collapse .list-group-item { border-left: 0; border-right: 0; border-radius: 0; padding: 10px 16px; font-size: 13px; }
.ea-fee-panel .panel-collapse .list-group-item:first-child { border-top: 1px solid var(--ea-border); background: #f8fafc; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.3px; color: var(--ea-text-light); }
.ea-fee-panel .table { margin-bottom: 0; }
.ea-fee-panel .table td { border: none; padding: 0; }
.ea-fee-total-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 24px; background: var(--ea-primary-light); border-top: 1px solid var(--ea-border);
}
.ea-fee-total-bar span { font-size: 13px; font-weight: 600; color: var(--ea-text-light); text-transform: uppercase; letter-spacing: 0.3px; }
.ea-fee-total-bar strong { font-size: 18px; font-weight: 700; color: var(--ea-primary); }

/* -- Merit scholarship callout -- */
.ea-merit-callout {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
    background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; font-size: 13px;
}
.ea-merit-callout i { font-size: 16px; color: #f59e0b; flex-shrink: 0; }

/* -- Guardian radio group -- */
.ea-radio-group { display: flex; gap: 20px; flex-wrap: wrap; padding: 8px 0; }
.ea-radio-group label { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 500; color: var(--ea-text); cursor: pointer; text-transform: none; letter-spacing: 0; }
.ea-radio-group input[type="radio"] { width: 16px; height: 16px; accent-color: var(--ea-primary); }

/* -- RTE radio -- */
.ea-rte-radio { display: flex; gap: 16px; padding-top: 4px; }
.ea-rte-radio label { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 500; color: var(--ea-text); cursor: pointer; text-transform: none; letter-spacing: 0; }

/* -- Address checkbox -- */
.ea-addr-check { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 13px; color: var(--ea-text); }
.ea-addr-check input[type="checkbox"] { width: 15px; height: 15px; accent-color: var(--ea-primary); }
</style>

<div class="content-wrapper ea-page">
    <section class="content" style="max-width: 960px; margin: 0 auto;">

        <!-- Top Bar -->
        <div class="ea-topbar">
            <div class="ea-topbar__left">
                <div>
                    <h1 class="ea-topbar__title"><?php echo $this->lang->line('edit_online_admission'); ?></h1>
                </div>
            </div>
            <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="ea-btn-back">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>

        <?php if ($this->session->flashdata('msg')) { ?>
            <div class="ea-flash"><?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?></div>
        <?php } ?>

        <form action="<?php echo site_url("admin/onlinestudent/edit/" . $id) ?>" id="employeeform" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <input type="hidden" id="student_id" name="student_id" value="<?php echo set_value('id', $student['id']); ?>" />
            <?php echo $this->customlib->getCSRF(); ?>

            <!-- ━━ Academic Info ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--blue"><i class="fa fa-graduation-cap"></i></div>
                    <div>
                        <h3 class="ea-card__title">Academic Info</h3>
                        <p class="ea-card__subtitle">Admission, class and section</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-4">
                        <?php if (!$adm_auto_insert) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('admission_no'); ?> <span class="ea-req">*</span></label>
                            <input autofocus="" id="admission_no" name="admission_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('admission_no', $student['admission_no']); ?>" />
                            <span class="text-danger"><?php echo form_error('admission_no'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->roll_no) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('roll_number'); ?></label>
                            <input id="roll_no" name="roll_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('roll_no', $student['roll_no']); ?>" />
                            <span class="text-danger"><?php echo form_error('roll_no'); ?></span>
                        </div>
                        <?php } ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('class'); ?> <span class="ea-req">*</span></label>
                            <select id="class_id" name="class_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($classlist as $class) { ?>
                                <option value="<?php echo $class['id'] ?>" <?php if ($student['class_id'] == $class['id']) { echo "selected=selected"; } ?>><?php echo $class['class'] ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('section'); ?> <span class="ea-req">*</span></label>
                            <select id="section_id" name="section_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                            </select>
                            <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ━━ Student Details ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--purple"><i class="fa fa-user"></i></div>
                    <div>
                        <h3 class="ea-card__title">Student Details</h3>
                        <p class="ea-card__subtitle">Personal information, identity and custom fields</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('first_name'); ?> <span class="ea-req">*</span></label>
                            <input id="firstname" name="firstname" placeholder="" type="text" class="form-control" value="<?php echo set_value('firstname', $student['firstname']); ?>" />
                            <input type="hidden" name="studentid" value="<?php echo $student["id"] ?>">
                            <span class="text-danger"><?php echo form_error('firstname'); ?></span>
                        </div>
                        <?php if ($sch_setting->middlename) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('middle_name'); ?></label>
                            <input id="middlename" name="middlename" placeholder="" type="text" class="form-control" value="<?php echo set_value('middlename', $student['middlename']); ?>" />
                            <span class="text-danger"><?php echo form_error('middlename'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->lastname) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('last_name'); ?></label>
                            <input id="lastname" name="lastname" placeholder="" type="text" class="form-control" value="<?php echo set_value('lastname', $student['lastname']); ?>" />
                            <span class="text-danger"><?php echo form_error('lastname'); ?></span>
                        </div>
                        <?php } ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('gender'); ?> <span class="ea-req">*</span></label>
                            <select class="form-control" name="gender">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($genderList as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php if ($student['gender'] == $key) { echo "selected"; } ?>><?php echo $value; ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('gender'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('date_of_birth'); ?> <span class="ea-req">*</span></label>
                            <input id="dob" name="dob" placeholder="" type="text" class="form-control date" value="<?php echo set_value('dob', $this->customlib->dateformat(($student['dob']))); ?>" readonly="readonly"/>
                            <span class="text-danger"><?php echo form_error('dob'); ?></span>
                        </div>
                    </div>

                    <div class="ea-grid-4">
                        <?php if ($sch_setting->category) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('category'); ?></label>
                            <select id="category_id" name="category_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($categorylist as $category) { ?>
                                <option value="<?php echo $category['id'] ?>" <?php if ($student['category_id'] == $category['id']) { echo "selected=selected"; } ?>><?php echo $category['category']; ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('category_id'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->cast) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('caste'); ?></label>
                            <input id="cast" name="cast" placeholder="" type="text" class="form-control" value="<?php echo set_value('cast', $student['cast']); ?>" />
                            <span class="text-danger"><?php echo form_error('cast'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->religion) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('religion'); ?></label>
                            <input id="religion" name="religion" placeholder="" type="text" class="form-control" value="<?php echo set_value('religion', $student['religion']); ?>" />
                            <span class="text-danger"><?php echo form_error('religion'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mobile_no) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('mobile_number'); ?></label>
                            <input id="mobileno" name="mobileno" placeholder="" type="text" class="form-control" value="<?php echo set_value('mobileno', $student['mobileno']); ?>" />
                            <span class="text-danger"><?php echo form_error('mobileno'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-grid-3">
                        <?php if ($sch_setting->student_email) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('email'); ?></label>
                            <input id="email" name="email" placeholder="" type="text" class="form-control" value="<?php echo set_value('email', $student['email']); ?>" />
                            <span class="text-danger"><?php echo form_error('email'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->admission_date) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('admission_date'); ?></label>
                            <input id="admission_date" name="admission_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('admission_date', $this->customlib->dateformat(($student['admission_date']))); ?>" readonly="readonly" />
                            <span class="text-danger"><?php echo form_error('admission_date'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->is_blood_group) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('blood_group'); ?></label>
                            <select class="form-control" name="blood_group">
                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                <?php foreach ($bloodgroup as $bgkey => $bgvalue) { ?>
                                <option value="<?php echo $bgvalue ?>" <?php if ($bgvalue == $student["blood_group"]) { echo "selected"; } ?>><?php echo $bgvalue ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('house'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-grid-4">
                        <?php if ($sch_setting->is_student_house) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('house') ?></label>
                            <select class="form-control" name="house">
                                <option value=""><?php echo $this->lang->line('select') ?></option>
                                <?php foreach ($houses as $house_key => $house_value) { ?>
                                <option value="<?php echo $house_value->id; ?>" <?php echo set_select('house', $house_value->id, (set_value('house', $student['school_house_id']) == $house_value->id)); ?>><?php echo $house_value->house_name; ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('house'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_height) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('height'); ?></label>
                            <input type="text" value="<?php echo $student["height"] ?>" name="height" class="form-control" value="<?php echo set_value('height', $student['height']); ?>">
                            <span class="text-danger"><?php echo form_error('height'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_weight) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('weight'); ?></label>
                            <input type="text" value="<?php echo $student["weight"] ?>" name="weight" class="form-control" value="<?php echo set_value('weight', $student['weight']); ?>">
                            <span class="text-danger"><?php echo form_error('height'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->measurement_date) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('measurement_date'); ?></label>
                            <input id="measure_date" name="measure_date" placeholder="" type="text" class="form-control date" value="<?php if ($student['measurement_date'] != '0000-00-00' && $student['measurement_date'] != "" && $student['measurement_date'] != '1970-01-01') { echo set_value('measure_date', $this->customlib->dateformat($student['measurement_date'])); } ?>" readonly="readonly"/>
                            <span class="text-danger"><?php echo form_error('measure_date'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if ($sch_setting->student_photo) { ?>
                    <div class="ea-field" style="max-width: 400px;">
                        <label><?php echo $this->lang->line('student_photo'); ?></label>
                        <div class="ea-photo-upload">
                            <div class="ea-photo-upload__icon"><i class="fa fa-camera"></i></div>
                            <div class="ea-photo-upload__text">
                                <strong>Click to upload</strong> or drag and drop<br>
                                JPG, PNG &middot; 100&times;100px
                            </div>
                            <input class="filestyle form-control" type="file" name="file" id="file" size="20" <?php if ($student['image'] != "") { ?> data-default-file="<?php echo base_url() . $student['image'] ?>" <?php } ?> />
                        </div>
                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                    </div>
                    <?php } ?>

                    <!-- Custom Fields -->
                    <div class="ea-grid-3">
                        <?php echo display_onlineadmission_custom_fields('students', $id); ?>
                    </div>
                </div>
            </div>

            <!-- ━━ Fee Details ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--green"><i class="fa fa-money"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('fees_details'); ?></h3>
                        <p class="ea-card__subtitle">Select fee groups to assign</p>
                    </div>
                </div>
                <div class="ea-card__body ea-fee-panel">
                    <?php
                    if (!empty($feesessiongroup_model)) {
                        $view_total_fees = 0;
                        foreach ($feesessiongroup_model as $feesessiongroup_key => $feesessiongroup_value) {
                            $total_fees = 0;
                            foreach ($feesessiongroup_value->feetypes as $fee_type_key => $fee_type_value) {
                                $total_fees += $fee_type_value->amount;
                            }
                            if (isset($_POST['fee_session_group_id'])) {
                                if (in_array($feesessiongroup_value->id, $_POST['fee_session_group_id'])) {
                                    $view_total_fees += $total_fees;
                                }
                            }
                        }
                    ?>
                    <input type="hidden" name="total_post_fees" value="<?php echo $view_total_fees; ?>">
                    <?php
                        foreach ($feesessiongroup_model as $feesessiongroup_key => $feesessiongroup_value) {
                            $total_fees = 0;
                            foreach ($feesessiongroup_value->feetypes as $fee_type_key => $fee_type_value) {
                                $total_fees += $fee_type_value->amount;
                            }
                    ?>
                    <div class="panel-group1 mb0">
                        <div class="panel panel-default1">
                            <div class="panel-heading pt5 pb5">
                                <h6 class="panel-title panel-title1 overflow-hidden">
                                    <input class="fee_group_chk vertical-middle" type="checkbox" name="fee_session_group_id[]" value="<?php echo $feesessiongroup_value->id; ?>" <?php echo set_checkbox('fee_session_group_id[]', $feesessiongroup_value->id); ?>>
                                    <a class="display-inline collapsed box-plus-panel" data-toggle="collapse" href="#collapse_fees_<?php echo $feesessiongroup_value->id ?>"><span class="font14"><?php echo $feesessiongroup_value->group_name; ?></span></a>
                                    <span class="float-right bmedium pt3 fee_group_total" data-amount="<?php echo $total_fees; ?>"><?php echo amountFormat($total_fees); ?></span>
                                </h6>
                            </div>
                            <div id="collapse_fees_<?php echo $feesessiongroup_value->id ?>" class="panel-collapse collapse">
                                <ul class="list-group student_fee_list ui-sortable">
                                    <li class="list-group-item">
                                        <div class="displayinline stfirstdiv bmedium font14 pl-65"><?php echo $this->lang->line('fees'); ?></div>
                                        <div class="due_date bmedium font14"><?php echo $this->lang->line('due_date'); ?></div>
                                        <div class="tools bmedium font14"><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>)</div>
                                    </li>
                                    <?php foreach ($feesessiongroup_value->feetypes as $fee_type_key => $fee_type_value) { ?>
                                    <li class="list-group-item">
                                        <div class="displayinline stfirstdiv pl-65"><?php echo $fee_type_value->type . " (" . $fee_type_value->code . ")" ?></div>
                                        <small class="due_date"><i class="fa fa-calendar"></i> <?php echo $this->customlib->dateformat($fee_type_value->due_date); ?></small>
                                        <div class="tools"><?php echo amountFormat($fee_type_value->amount); ?></div>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    }
                    ?>
                </div>
                <?php if (!empty($feesessiongroup_model)) { ?>
                <div class="ea-fee-total-bar">
                    <span>Total Fees</span>
                    <strong class="total_fees_alloted"><?php echo amountFormat($view_total_fees); ?></strong>
                </div>
                <?php } ?>
            </div>

            <!-- ━━ Fee Discount ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--amber"><i class="fa fa-percent"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('fees_discount_details'); ?></h3>
                        <p class="ea-card__subtitle">Select applicable fee discounts</p>
                    </div>
                </div>
                <div class="ea-card__body ea-fee-panel">
                    <div class="mainstudent discount_div">
                        <div id="fade"></div>
                        <div id="modal">
                            <i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span>
                        </div>

                        <?php if (!empty($merit_scholarship)): ?>
                        <div class="ea-merit-callout">
                            <i class="fa fa-star"></i>
                            <div>
                                <strong>Merit Scholarship Approved:</strong>
                                <?php echo htmlspecialchars($merit_scholarship['sch_name']); ?>
                                &mdash; <strong>&#8377;<?php echo number_format((float)$merit_scholarship['sch_amount']); ?></strong>
                                has been pre-selected below. Please verify and proceed.
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($feediscountList)) {
                            foreach ($feediscountList as $key => $feediscount) {
                                $is_merit_pre_check = !empty($merit_discount_id) && (int)$feediscount['id'] === (int)$merit_discount_id;
                                $checked_attr = $is_merit_pre_check ? 'checked="checked"' : set_checkbox('discount_id[]', $feediscount['id']);
                        ?>
                        <div class="panel-group1 mb0">
                            <div class="panel panel-default1">
                                <div class="panel-heading pt5 pb5">
                                    <h6 class="panel-title panel-title1 overflow-hidden">
                                        <input class="discount_group_chk vertical-middle" type="checkbox" name="discount_id[]" value="<?php echo $feediscount['id']; ?>" <?php echo $checked_attr; ?>>
                                        <a class="display-inline collapsed box-plus-panel" data-toggle="collapse" href="#collapse_fees_<?php echo $feediscount['id'] ?>">
                                            <span class="font14"><?php echo $feediscount['name'] . " - " . $feediscount['code']; ?></span>
                                        </a>
                                        <span class="float-right bmedium pt3 discount_group_total" data-discount="<?php echo $feediscount['amount']; ?>"></span>
                                    </h6>
                                </div>
                                <div id="collapse_fees_<?php echo $feediscount['id']; ?>" class="panel-collapse collapse">
                                    <ul class="list-group student_fee_list ui-sortable">
                                        <li class="list-group-item">
                                            <div class="due_date bmedium font14 pl-65"><?php echo $this->lang->line('name'); ?></div>
                                            <div class="due_date bmedium font14 pl-65"><?php echo $this->lang->line('discount_code'); ?></div>
                                            <div class="due_date bmedium font14 pl-65"><?php echo $this->lang->line('type'); ?></div>
                                            <div class="tools bmedium font14"><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>)</div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="due_date font14 pl-65"><?php echo $feediscount['name']; ?></div>
                                            <div class="due_date font14 pl-65"><?php echo $feediscount['code']; ?></div>
                                            <div class="due_date font14 pl-65"><?php echo $this->lang->line($feediscount['type']); ?></div>
                                            <div class="tools">
                                                <?php
                                                if (isset($feediscount['type']) && $feediscount['type'] == "percentage") {
                                                    echo $feediscount['percentage'] . "%";
                                                } else if (isset($feediscount['amount'])) {
                                                    $amount = $feediscount['amount'];
                                                    if ($amount > 0.00) {
                                                        echo amountFormat($amount) . " (" . $currency_symbol . ")";
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- ━━ Transport Details ━━ -->
            <?php if ($this->module_lib->hasActive('transport')) { ?>
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--teal"><i class="fa fa-bus"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('transport_details'); ?></h3>
                        <p class="ea-card__subtitle">Route, pickup point and fees</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('route_list'); ?></label>
                            <select class="form-control" id="vehroute_id" name="vehroute_id">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($vehroutelist as $vehroute) { ?>
                                <optgroup label="<?php echo $vehroute['route_title']; ?>">
                                    <?php
                                    $vehicles = $vehroute['vehicles'];
                                    if (!empty($vehicles)) {
                                        foreach ($vehicles as $key => $value) {
                                    ?>
                                    <option value="<?php echo $value->vec_route_id ?>" <?php echo set_select('vehroute_id', $value->vec_route_id); ?> data-fee="">
                                        <?php echo $value->vehicle_no ?>
                                    </option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </optgroup>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('vehroute_id'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('pickup_point'); ?></label>
                            <select class="form-control" id="pickup_point" name="route_pickup_point_id">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                            </select>
                            <span class="text-danger"><?php echo form_error('route_pickup_point_id'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('fees_month'); ?></label>
                            <select id="specialistOpt" class="form-control" name="transport_feemaster_id[]" multiple="multiple">
                                <?php foreach ($transport_fees as $key => $value) { ?>
                                <option <?php echo set_select('transport_feemaster_id[]', $value['id']); ?> value="<?php echo $value['id']; ?>"><?php echo $this->lang->line(strtolower($value['month'])); ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('transport_feemaster_id'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- ━━ Hostel Details ━━ -->
            <?php if ($this->module_lib->hasActive('hostel')) { ?>
            <?php if ($sch_setting->route_list) { ?>
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--slate"><i class="fa fa-building"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('hostel_details'); ?></h3>
                        <p class="ea-card__subtitle">Hostel and room assignment</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-2">
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('hostel'); ?></label>
                            <select class="form-control" id="hostel_id" name="hostel_id">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($hostelList as $hostel_key => $hostel_value) { ?>
                                <option value="<?php echo $hostel_value['id'] ?>" <?php echo set_value('hostel_id', $student['hostel_id']) == $hostel_value['id'] ? "selected='selected'" : ""; ?>>
                                    <?php echo $hostel_value['hostel_name']; ?>
                                </option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('hostel_id'); ?></span>
                        </div>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('room_no'); ?></label>
                            <select id="hostel_room_id" name="hostel_room_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                            </select>
                            <span class="text-danger"><?php echo form_error('hostel_room_id'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php } ?>

            <!-- ━━ Parent / Guardian Details ━━ -->
            <?php if (($sch_setting->father_name) || ($sch_setting->father_phone) || ($sch_setting->father_occupation) || ($sch_setting->father_pic) || ($sch_setting->mother_name) || ($sch_setting->mother_phone) || ($sch_setting->mother_occupation) || ($sch_setting->mother_pic) || ($sch_setting->guardian_name) || ($sch_setting->guardian_occupation) || ($sch_setting->guardian_relation) || ($sch_setting->guardian_phone) || ($sch_setting->guardian_email) || ($sch_setting->guardian_pic) || ($sch_setting->guardian_address)) { ?>
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--orange"><i class="fa fa-users"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('parent_guardian_detail'); ?></h3>
                        <p class="ea-card__subtitle">Father, mother and guardian information</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <!-- Father -->
                    <div class="ea-grid-4">
                        <?php if ($sch_setting->father_name) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('father_name'); ?></label>
                            <input id="father_name" name="father_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('father_name', $student['father_name']); ?>" />
                            <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->father_phone) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('phone_number'); ?></label>
                            <input id="father_phone" name="father_phone" placeholder="" type="text" class="form-control" value="<?php echo set_value('father_phone', $student['father_phone']); ?>" />
                            <span class="text-danger"><?php echo form_error('father_phone'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->father_occupation) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('father_occupation'); ?></label>
                            <input id="father_occupation" name="father_occupation" placeholder="" type="text" class="form-control" value="<?php echo set_value('father_occupation', $student['father_occupation']); ?>" />
                            <span class="text-danger"><?php echo form_error('father_occupation'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->father_pic) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('father_photo'); ?> (100px X 100px)</label>
                            <div><input class="filestyle form-control" type="file" name="father_pic" size="20" <?php if ($student['father_pic'] != "") { ?> data-default-file="<?php echo base_url() . $student['father_pic']; ?>" <?php } ?> /></div>
                            <span class="text-danger"><?php echo form_error('father_pic'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-divider"></div>

                    <!-- Mother -->
                    <div class="ea-grid-4">
                        <?php if ($sch_setting->mother_name) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('mother_name'); ?></label>
                            <input id="mother_name" name="mother_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('mother_name', $student['mother_name']); ?>" />
                            <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mother_phone) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('mother_phone'); ?></label>
                            <input id="mother_phone" name="mother_phone" placeholder="" type="text" class="form-control" value="<?php echo set_value('mother_phone', $student['mother_phone']); ?>" />
                            <span class="text-danger"><?php echo form_error('mother_phone'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mother_occupation) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('mother_occupation'); ?></label>
                            <input id="mother_occupation" name="mother_occupation" placeholder="" type="text" class="form-control" value="<?php echo set_value('mother_occupation', $student['mother_occupation']); ?>" />
                            <span class="text-danger"><?php echo form_error('mother_occupation'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->mother_pic) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('mother_photo'); ?> (100px X 100px)</label>
                            <div><input class="filestyle form-control" type="file" name="mother_pic" size="20" <?php if ($student['mother_pic'] != "") { ?> data-default-file="<?php echo base_url() . $student['mother_pic']; ?>" <?php } ?> /></div>
                            <span class="text-danger"><?php echo form_error('mother_pic'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-divider"></div>

                    <!-- Guardian -->
                    <?php if ($sch_setting->guardian_name) { ?>
                    <div class="ea-field">
                        <label><?php echo $this->lang->line('if_guardian_is'); ?> <span class="ea-req">*</span></label>
                        <div class="ea-radio-group">
                            <label>
                                <input type="radio" name="guardian_is" <?php if ($student['guardian_is'] == "father") { echo "checked"; } ?> value="father"> <?php echo $this->lang->line('father'); ?>
                            </label>
                            <label>
                                <input type="radio" name="guardian_is" <?php if ($student['guardian_is'] == "mother") { echo "checked"; } ?> value="mother"> <?php echo $this->lang->line('mother'); ?>
                            </label>
                            <label>
                                <input type="radio" name="guardian_is" <?php if ($student['guardian_is'] == "other") { echo "checked"; } ?> value="other"> <?php echo $this->lang->line('other'); ?>
                            </label>
                        </div>
                        <span class="text-danger"><?php echo form_error('guardian_is'); ?></span>
                    </div>
                    <?php } ?>

                    <div class="ea-grid-4">
                        <?php if ($sch_setting->guardian_name) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('guardian_name'); ?> <span class="ea-req">*</span></label>
                            <input id="guardian_name" name="guardian_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('guardian_name', $student['guardian_name']); ?>" />
                            <span class="text-danger"><?php echo form_error('guardian_name'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_relation) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('guardian_relation'); ?></label>
                            <input id="guardian_relation" name="guardian_relation" placeholder="" type="text" class="form-control" value="<?php echo set_value('guardian_relation', $student['guardian_relation']); ?>" />
                            <span class="text-danger"><?php echo form_error('guardian_relation'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_email) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('guardian_email'); ?></label>
                            <input id="guardian_email" name="guardian_email" placeholder="" type="text" class="form-control" value="<?php echo set_value('guardian_email', $student['guardian_email']); ?>" />
                            <span class="text-danger"><?php echo form_error('guardian_email'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_pic) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('guardian_photo'); ?> (100px X 100px)</label>
                            <div><input class="filestyle form-control" type="file" name="guardian_pic" id="file" size="20" <?php if ($student['guardian_pic'] != "") { ?> data-default-file="<?php echo base_url() . $student['guardian_pic']; ?>" <?php } ?> /></div>
                            <span class="text-danger"><?php echo form_error('guardian_pic'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-grid-3">
                        <?php if ($sch_setting->guardian_phone) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('guardian_phone'); ?></label>
                            <input id="guardian_phone" name="guardian_phone" placeholder="" type="text" class="form-control" value="<?php echo set_value('guardian_phone', $student['guardian_phone']); ?>" />
                            <span class="text-danger"><?php echo form_error('guardian_phone'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->guardian_occupation) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('guardian_occupation'); ?></label>
                            <input id="guardian_occupation" name="guardian_occupation" placeholder="" type="text" class="form-control" value="<?php echo set_value('guardian_occupation', $student['guardian_occupation']); ?>" />
                            <span class="text-danger"><?php echo form_error('guardian_occupation'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <?php if ($sch_setting->guardian_address) { ?>
                    <div class="ea-field">
                        <label><?php echo $this->lang->line('guardian_address'); ?></label>
                        <textarea id="guardian_address" name="guardian_address" placeholder="" class="form-control" rows="4"><?php echo set_value('guardian_address', $student['guardian_address']); ?></textarea>
                        <span class="text-danger"><?php echo form_error('guardian_address'); ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- ━━ Address ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--teal"><i class="fa fa-map-marker"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('address_details'); ?></h3>
                        <p class="ea-card__subtitle">Current and permanent address</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-2">
                        <?php if ($sch_setting->current_address) { ?>
                        <div class="ea-field">
                            <label class="ea-addr-check">
                                <input type="checkbox" id="autofill_current_address" onclick="return auto_fill_guardian_address();">
                                <?php echo $this->lang->line('if_guardian_address_is_current_address'); ?>
                            </label>
                            <label><?php echo $this->lang->line('current_address'); ?></label>
                            <textarea id="current_address" name="current_address" placeholder="" class="form-control"><?php echo set_value('current_address', $student['current_address']); ?></textarea>
                            <span class="text-danger"><?php echo form_error('current_address'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->permanent_address) { ?>
                        <div class="ea-field">
                            <label class="ea-addr-check">
                                <input type="checkbox" id="autofill_address" onclick="return auto_fill_address();">
                                <?php echo $this->lang->line('if_permanent_address_is_current_address'); ?>
                            </label>
                            <label><?php echo $this->lang->line('permanent_address'); ?></label>
                            <textarea id="permanent_address" name="permanent_address" placeholder="" class="form-control"><?php echo set_value('permanent_address', $student['permanent_address']) ?></textarea>
                            <span class="text-danger"><?php echo form_error('permanent_address', $student['permanent_address']); ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- ━━ Miscellaneous Details ━━ -->
            <div class="ea-card">
                <div class="ea-card__header">
                    <div class="ea-card__icon ea-card__icon--slate"><i class="fa fa-info-circle"></i></div>
                    <div>
                        <h3 class="ea-card__title"><?php echo $this->lang->line('miscellaneous_details'); ?></h3>
                        <p class="ea-card__subtitle">Bank details, identification and notes</p>
                    </div>
                </div>
                <div class="ea-card__body">
                    <div class="ea-grid-3">
                        <?php if ($sch_setting->bank_account_no) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('bank_account_number'); ?></label>
                            <input id="bank_account_no" name="bank_account_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('bank_account_no', $student['bank_account_no']); ?>" />
                            <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->bank_name) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('bank_name'); ?></label>
                            <input id="bank_name" name="bank_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('bank_name', $student['bank_name']); ?>" />
                            <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->ifsc_code) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('ifsc_code'); ?></label>
                            <input id="ifsc_code" name="ifsc_code" placeholder="" type="text" class="form-control" value="<?php echo set_value('ifsc_code', $student['ifsc_code']); ?>" />
                            <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-grid-3">
                        <?php if ($sch_setting->national_identification_no) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('national_identification_number'); ?></label>
                            <input id="adhar_no" name="adhar_no" placeholder="" type="text" class="form-control" value="<?php echo set_value('adhar_no', $student['adhar_no']); ?>" />
                            <span class="text-danger"><?php echo form_error('adhar_no'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->local_identification_no) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('local_identification_number'); ?></label>
                            <input id="samagra_id" name="samagra_id" placeholder="" type="text" class="form-control" value="<?php echo set_value('samagra_id', $student['samagra_id']); ?>" />
                            <span class="text-danger"><?php echo form_error('samagra_id'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->rte) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('rte'); ?></label>
                            <div class="ea-rte-radio">
                                <label><input class="radio-inline" type="radio" name="rte" value="Yes" <?php echo set_value('rte', $student['rte']) == "Yes" ? "checked" : ""; ?>> <?php echo $this->lang->line('yes'); ?></label>
                                <label><input class="radio-inline" type="radio" name="rte" value="No" <?php echo set_value('rte', $student['rte']) == "No" ? "checked" : ""; ?>> <?php echo $this->lang->line('no'); ?></label>
                            </div>
                            <span class="text-danger"><?php echo form_error('rte'); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="ea-grid-2">
                        <?php if ($sch_setting->previous_school_details) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('previous_school_details'); ?></label>
                            <textarea class="form-control" rows="3" placeholder="" name="previous_school"><?php echo set_value('previous_school', $student['previous_school']); ?></textarea>
                            <span class="text-danger"><?php echo form_error('previous_school'); ?></span>
                        </div>
                        <?php } ?>
                        <?php if ($sch_setting->student_note) { ?>
                        <div class="ea-field">
                            <label><?php echo $this->lang->line('note'); ?></label>
                            <textarea class="form-control" rows="3" placeholder="" name="note"><?php echo set_value('note', $student['note']); ?></textarea>
                            <span class="text-danger"><?php echo form_error('previous_school'); ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- ━━ Save Actions ━━ -->
            <div class="ea-card" style="overflow: hidden;">
                <div class="ea-actions">
                    <button type="submit" class="ea-btn ea-btn--primary" name="save" value="save" id="submitbtn">
                        <i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?>
                    </button>
                    <button type="submit" class="ea-btn ea-btn--success" name="save" value="enroll" id="enrollbtn">
                        <i class="fa fa-user-plus"></i> <?php echo $this->lang->line('save_and_enroll'); ?>
                    </button>
                </div>
            </div>
        </form>

    </section>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', $student['section_id']) ?>';
        var hostel_id = $('#hostel_id').val();
        var hostel_room_id = '<?php echo set_value('hostel_room_id', $student['hostel_room_id']) ?>';
        getHostel(hostel_id, hostel_room_id);
        getSectionByClass(class_id, section_id, 'section_id');
        var vehroute_id = '<?php echo set_value('vehroute_id', 0) ?>';
        var route_pickup_point_id = '<?php echo set_value('route_pickup_point_id', 0) ?>';
        get_pickup_point(vehroute_id, route_pickup_point_id);

        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            getSectionByClass(class_id, 0, 'section_id');
        });

        $(document).on('change', '#hostel_id', function (e) {
            var hostel_id = $(this).val();
            getHostel(hostel_id, 0);
        });

        $(document).on('change', '#sibiling_section_id', function (e) {
            getStudentsByClassAndSection();
        });

        function getStudentsByClassAndSection() {
            $('#sibiling_student_id').html("");
            var class_id = $('#sibiling_class_id').val();
            var section_id = $('#sibiling_section_id').val();
            var current_student_id = $('.current_student_id').val();
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';

            $.ajax({
                type: "GET",
                url: baseurl + "student/getByClassAndSectionExcludeMe",
                data: {'class_id': class_id, 'section_id': section_id, 'current_student_id': current_student_id},
                dataType: "json",
                beforeSend: function () {
                    $('#sibiling_student_id').addClass('dropdownloading');
                },
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected=selected";
                        }
                        div_data += "<option value=" + obj.id + ">" + obj.firstname + " " + obj.lastname + "</option>";
                    });
                    $('#sibiling_student_id').append(div_data);
                },
                complete: function () {
                    $('#sibiling_student_id').removeClass('dropdownloading');
                }
            });
        }

        function getSectionByClass(class_id, section_id, select_control) {
            if (class_id != "") {
                $('#' + select_control).html("");
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/onlinestudent/getByClass",
                    data: {'class_id': class_id},
                    dataType: "JSON",
                    beforeSend: function () {
                        $('#' + select_control).addClass('dropdownloading');
                    },
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = "";
                            if (section_id == obj.section_id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.section + "</option>";
                        });
                        $('#' + select_control).append(div_data);
                    },
                    complete: function () {
                        $('#' + select_control).removeClass('dropdownloading');
                    }
                });
            }
        }

        function getHostel(hostel_id, hostel_room_id) {
            if (hostel_room_id == "") {
                hostel_room_id = 0;
            }

            if (hostel_id != "") {
                $('#hostel_room_id').html("");
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "GET",
                    url: baseurl + "admin/hostelroom/getRoom",
                    data: {'hostel_id': hostel_id},
                    dataType: "json",
                    beforeSend: function () {
                        $('#hostel_room_id').addClass('dropdownloading');
                    },
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = "";
                            if (hostel_room_id == obj.id) {
                                sel = "selected";
                            }

                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.room_no + " (" + obj.room_type + ")" + "</option>";

                        });
                        $('#hostel_room_id').append(div_data);
                    },
                    complete: function () {
                        $('#hostel_room_id').removeClass('dropdownloading');
                    }
                });
            }
        }
    });

    function auto_fill_guardian_address() {
        if ($("#autofill_current_address").is(':checked'))
        {
            $('#current_address').val($('#guardian_address').val());
        }
    }

    function auto_fill_address() {
        if ($("#autofill_address").is(':checked'))
        {
            $('#permanent_address').val($('#current_address').val());
        }
    }

    $('input:radio[name="guardian_is"]').change(
            function () {
                if ($(this).is(':checked')) {
                    var value = $(this).val();
                    if (value == "father") {
                        var father_relation = "<?php echo $this->lang->line('father'); ?>";
                        $('#guardian_name').val($('#father_name').val());
                        $('#guardian_phone').val($('#father_phone').val());
                        $('#guardian_occupation').val($('#father_occupation').val());
                        $('#guardian_relation').val(father_relation);
                    } else if (value == "mother") {
                        var mother_relation = "<?php echo $this->lang->line('mother'); ?>";
                        $('#guardian_name').val($('#mother_name').val());
                        $('#guardian_phone').val($('#mother_phone').val());
                        $('#guardian_occupation').val($('#mother_occupation').val());
                        $('#guardian_relation').val(mother_relation);
                    } else {
                        $('#guardian_name').val("");
                        $('#guardian_phone').val("");
                        $('#guardian_occupation').val("");
                        $('#guardian_relation').val("")
                    }
                }
            });

</script>

<script>

     $("form#employeeform button[type=submit]").click(function() {
        $("button[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });

    $(function(){
         $("form#employeeform").submit(function() {
          var sub_btn = $("button[type=submit][clicked=true]");
          sub_btn.button('loading');
    });

    })

    $('#specialistOpt').multiselect({
    columns: 1,
    placeholder: '<?php echo $this->lang->line('select_month'); ?>',
    search: true
   });

    $(document).on('change','#vehroute_id',function(){
       var vehroute_id=$(this).val();
       get_pickup_point(vehroute_id,0);
    });

    function get_pickup_point(vehroute_id,pickuppoint_id){
         if (vehroute_id != "") {
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
                url: baseurl+'admin/pickuppoint/get_pickupdropdownlist',
                type: "POST",
                data:{vehroute_id:vehroute_id},
                dataType: 'json',
                 beforeSend: function() {
                    $('#pickup_point').html('');
                },
                success: function(res) {
                    $.each(res, function (index, value) {
                         var sel = "";
                            if (pickuppoint_id == value.route_pickup_point_id) {
                                sel = "selected";
                            }
                        div_data += "<option  value=" + value.route_pickup_point_id + " " + sel + ">" + value.name + "</option>";
                    });

                    $('#pickup_point').html(div_data);
                },
                   error: function(xhr) { // if error occured
                   alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            },
            complete: function() {

            }
            });
        }
    }
</script>

<script type="text/javascript">
    var total_fees_alloted= parseFloat($("input[name='total_post_fees']").val());
    $(document).ready(function(){
        $(document).on('change','.fee_group_chk',function(){

        if ($(this).prop("checked")) {

        total_fees_alloted +=parseFloat($(this).closest('div').find('span.fee_group_total').data('amount'));
        }
        else {
          total_fees_alloted -=parseFloat($(this).closest('div').find('span.fee_group_total').data('amount'));
        }
      $('.total_fees_alloted').text(total_fees_alloted.toFixed(2));
    });
    });
</script>


<script type="text/javascript">
//fee discount
    $(document).ready(function(){
        $(document).on('change','.discount_group_chk',function(){
            $(".discount_div").find('#fade').css("display", "block");
            $(".discount_div").find('#modal').css("display", "block");
            $(".discount_div").find("#fade").fadeOut(1000);
            $(".discount_div").find("#modal").fadeOut(1000);
        });
    });
//fee discount
</script>
