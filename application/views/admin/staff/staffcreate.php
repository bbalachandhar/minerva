<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form id="form1" action="<?php echo site_url('admin/staff/create') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="alert alert-info">
                                Staff email is their login username, password is generated automatically and send to staff email. Superadmin can change staff password on their staff profile page.
                            </div>
                            <div class="tshadow mb25 bozero">
                                <div class="box-tools pull-right pt3">
                                    <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/staff/import" autocomplete="off"><i class="fa fa-plus"></i> <?php echo $this->lang->line('import_staff'); ?></a>
                                </div>
                                <h4 class="pagetitleh2"><?php echo $this->lang->line('basic_information'); ?> </h4>

                                <div class="around10">
									
									<?php 
										$errors = [];
										if (form_error('validate_staff')) {
											$errors[] = form_error('validate_staff');
										}
										if (form_error('validate_storage')) {
											$errors[] = form_error('validate_storage');
										}
		
										if (!empty($errors)): ?>
											<div class="alert alert-danger">
												<ol>
													<?php foreach ($errors as $error): ?>
														<li><?php echo $error; ?></li>
													<?php endforeach; ?>
												</ol>
											</div>
										<?php endif;
                                
									?>


                                    <?php if ($this->session->flashdata('msg')) {
    ?>
                                        <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg'); ?>
                                    <?php }?>
                                    <?php echo $this->customlib->getCSRF(); ?>

                                    <div class="row">
                                        <?php
if (!$staffid_auto_insert) {
    ?>

                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('staff_id'); ?></label><small class="req"> *</small>
                                                    <input autofocus="" id="employee_id" name="employee_id"  placeholder="" type="text" class="form-control"  value="<?php echo set_value('employee_id') ?>" />
                                                    <span class="text-danger"><?php echo form_error('employee_id'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="biometric_id"><?php echo $this->lang->line('biometric_id'); ?></label>
                                                    <input id="biometric_id" name="biometric_id" placeholder="" type="text" class="form-control"  value="<?php echo set_value('biometric_id') ?>" />
                                                    <span class="text-danger"><?php echo form_error('biometric_id'); ?></span>
                                                </div>
                                            </div>
                                            <?php
}
?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('role'); ?></label><small class="req"> *</small>
                                                <select  id="role" name="role" class="form-control" >
                                                    <option value=""   ><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
foreach ($roles as $key => $role) {
    ?>
                                                        <option value="<?php echo $role['id'] ?>" <?php echo set_select('role', $role['id'], set_value('role')); ?>><?php echo $role["name"] ?></option>
                                                    <?php }
?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('role'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_designation) {
    ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('designation'); ?></label>

                                                    <select id="designation" name="designation" placeholder="" type="text" class="form-control" >
                                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                                        <?php foreach ($designation as $key => $value) {
        ?>
                                                            <option value="<?php echo $value["id"] ?>" <?php echo set_select('designation', $value['id'], set_value('designation')); ?> ><?php echo $value["designation"] ?></option>
                                                        <?php }
    ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('designation'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_department) {
    ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('department'); ?></label>
                                                    <select id="department" name="department" placeholder="" type="text" class="form-control" >
                                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                                        <?php foreach ($department as $key => $value) {
        ?>
                                                            <option value="<?php echo $value["id"] ?>" <?php echo set_select('department', $value['id'], set_value('department')); ?>><?php echo $value["department_name"] ?></option>
                                                        <?php }
    ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('department'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('prefix'); ?></label>
                                                <input id="prefix" name="prefix" placeholder="" type="text" class="form-control"  value="<?php echo set_value('prefix') ?>" />
                                                <span class="text-danger"><?php echo form_error('prefix'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('first_name'); ?></label><small class="req"> *</small>
                                                <input id="name" name="name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('name') ?>" />
                                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_last_name) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('last_name'); ?></label>
                                                    <input id="surname" name="surname" placeholder="" type="text" class="form-control"  value="<?php echo set_value('surname') ?>" />
                                                    <span class="text-danger"><?php echo form_error('surname'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_father_name) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('father_name'); ?></label>
                                                    <input id="father_name"  name="father_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('father_name') ?>" />
                                                    <span class="text-danger"><?php echo form_error('father_name'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_mother_name) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('mother_name'); ?></label>
                                                    <input id="mother_name" name="mother_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('mother_name') ?>" />
                                                    <span class="text-danger"><?php echo form_error('mother_name'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('email'); ?> (<?php echo $this->lang->line('login') . " " . $this->lang->line('username'); ?>)</label><small class="req"> *</small>
                                                <input id="email" name="email" placeholder="" type="text" class="form-control"  value="<?php echo set_value('email') ?>" />
                                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputFile"> <?php echo $this->lang->line('gender'); ?></label><small class="req"> *</small>
                                                <select class="form-control" name="gender">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
foreach ($genderList as $key => $value) {
    ?>
                                                        <option value="<?php echo $key; ?>" <?php echo set_select('gender', $key, set_value('gender')); ?>><?php echo $value; ?></option>
                                                        <?php
}
?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('date_of_birth'); ?></label><small class="req"> *</small>
                                                <input id="dob" name="dob" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('dob') ?>" />
                                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_date_of_joining) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('date_of_joining'); ?></label>
                                                    <input id="date_of_joining" name="date_of_joining" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('date_of_joining') ?>" />
                                                    <span class="text-danger"><?php echo form_error('date_of_joining'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->staff_phone) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('phone'); ?></label>
                                                    <input id="mobileno" name="contactno" placeholder="" type="text" class="form-control"  value="<?php echo set_value('contactno') ?>" />
                                                    <span class="text-danger"><?php echo form_error('contactno'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_emergency_contact) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('emergency_contact_number'); ?></label>
                                                    <input id="mobileno" name="emergency_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('emergency_no') ?>" />
                                                    <span class="text-danger"><?php echo form_error('emergency_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_marital_status) {
    ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('marital_status'); ?></label>
                                                    <select class="form-control" name="marital_status">
                                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                        <?php foreach ($marital_status as $makey => $mavalue) {
        ?>
                                                            <option value="<?php echo $mavalue ?>" <?php echo set_select('marital_status', $mavalue, set_value('marital_status')); ?>><?php echo $mavalue; ?></option>

                                                        <?php }?>

                                                    </select>
                                                    <span class="text-danger"><?php echo form_error('marital_status'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_photo) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('photo'); ?></label>
                                                    <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->staff_current_address) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('current'); ?> <?php echo $this->lang->line('address'); ?></label>
                                                    <div><textarea name="address" class="form-control"><?php echo set_value('address'); ?></textarea>
                                                    </div>
                                                    <span class="text-danger"></span></div>
                                            </div>
                                        <?php } if ($sch_setting->staff_permanent_address) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('permanent_address'); ?></label>
                                                    <div><textarea name="permanent_address" class="form-control"><?php echo set_value('permanent_address'); ?></textarea>
                                                    </div>
                                                    <span class="text-danger"></span></div>
                                            </div>
                                        <?php } if ($sch_setting->staff_qualification) {?>
                                            <div class="col-md-3">

                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('qualification'); ?></label>
                                                    <textarea id="qualification" name="qualification" placeholder=""  class="form-control" ><?php echo set_value('qualification') ?></textarea>
                                                    <span class="text-danger"><?php echo form_error('qualification'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_work_experience) {?>
                                            <div class="col-md-3">

                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('work_experience'); ?></label>
                                                    <textarea id="work_experience" name="work_experience" placeholder="" class="form-control"><?php echo set_value('work_experience') ?></textarea>
                                                    <span class="text-danger"><?php echo form_error('work_experience'); ?></span>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_note) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('note'); ?></label>
                                                    <div><textarea name="note" class="form-control"><?php echo set_value('note'); ?></textarea>
                                                    </div>
                                                    <span class="text-danger"></span></div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="ug_qualification"><?php echo $this->lang->line('ug_qualification'); ?></label>
                                                <input id="ug_qualification" name="ug_qualification" placeholder="" type="text" class="form-control"  value="<?php echo set_value('ug_qualification') ?>" />
                                                <span class="text-danger"><?php echo form_error('ug_qualification'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="pg_qualification"><?php echo $this->lang->line('pg_qualification'); ?></label>
                                                <input id="pg_qualification" name="pg_qualification" placeholder="" type="text" class="form-control"  value="<?php echo set_value('pg_qualification') ?>" />
                                                <span class="text-danger"><?php echo form_error('pg_qualification'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="higher_qualification"><?php echo $this->lang->line('higher_qualification'); ?></label>
                                                <input id="higher_qualification" name="higher_qualification" placeholder="" type="text" class="form-control"  value="<?php echo set_value('higher_qualification') ?>" />
                                                <span class="text-danger"><?php echo form_error('higher_qualification'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="qualified_exam"><?php echo $this->lang->line('qualified_exam'); ?></label>
                                                <input id="qualified_exam" name="qualified_exam" placeholder="" type="text" class="form-control"  value="<?php echo set_value('qualified_exam') ?>" />
                                                <span class="text-danger"><?php echo form_error('qualified_exam'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="subject_specialization"><?php echo $this->lang->line('subject_specialization'); ?></label>
                                                <input id="subject_specialization" name="subject_specialization" placeholder="" type="text" class="form-control"  value="<?php echo set_value('subject_specialization') ?>" />
                                                <span class="text-danger"><?php echo form_error('subject_specialization'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="additional_qualification"><?php echo $this->lang->line('additional_qualification'); ?></label>
                                                <textarea id="additional_qualification" name="additional_qualification" placeholder="" class="form-control"><?php echo set_value('additional_qualification') ?></textarea>
                                                <span class="text-danger"><?php echo form_error('additional_qualification'); ?></span>
                                            </div>
                                        </div>

                                    <div class="row">
                                        <?php
echo display_custom_fields('staff');
?>
                                    </div>
                                </div>
                            </div>

                            <div class="tshadow mb25 bozero">
                                <h4 class="pagetitleh2"><?php echo "Other Information"; ?></h4>
                                <div class="row around10">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="aadhaar_no">Aadhaar No</label>
                                            <input id="aadhaar_no" name="aadhaar_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('aadhaar_no') ?>" />
                                            <span class="text-danger"><?php echo form_error('aadhaar_no'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="religion">Religious</label>
                                            <input id="religion" name="religion" placeholder="" type="text" class="form-control"  value="<?php echo set_value('religion') ?>" />
                                            <span class="text-danger"><?php echo form_error('religion'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="caste">Communal Category</label>
                                            <input id="caste" name="caste" placeholder="" type="text" class="form-control"  value="<?php echo set_value('caste') ?>" />
                                            <span class="text-danger"><?php echo form_error('caste'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="blood_group">Blood Group</label>
                                            <input id="blood_group" name="blood_group" placeholder="" type="text" class="form-control"  value="<?php echo set_value('blood_group') ?>" />
                                            <span class="text-danger"><?php echo form_error('blood_group'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row around10">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <input id="country" name="country" placeholder="" type="text" class="form-control"  value="<?php echo set_value('country') ?>" />
                                            <span class="text-danger"><?php echo form_error('country'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="state">State</label>
                                            <input id="state" name="state" placeholder="" type="text" class="form-control"  value="<?php echo set_value('state') ?>" />
                                            <span class="text-danger"><?php echo form_error('state'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="pincode">Pincode</label>
                                            <input id="pincode" name="pincode" placeholder="" type="text" class="form-control"  value="<?php echo set_value('pincode') ?>" />
                                            <span class="text-danger"><?php echo form_error('pincode'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row around10">
                                    <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="contract_type">Contract Type</label>
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
                                <div class="row around10">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="previous_salary">Previous Salary</label>
                                            <input id="previous_salary" name="previous_salary" placeholder="" type="text" class="form-control"  value="<?php echo set_value('previous_salary') ?>" />
                                            <span class="text-danger"><?php echo form_error('previous_salary'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="uan_no">UAN Number</label>
                                            <input id="uan_no" name="uan_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('uan_no') ?>" />
                                            <span class="text-danger"><?php echo form_error('uan_no'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="pan_no">PAN Number</label>
                                            <input id="pan_no" name="pan_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('pan_no') ?>" />
                                            <span class="text-danger"><?php echo form_error('pan_no'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="previous_institution">Previous Institution</label>
                                            <input id="previous_institution" name="previous_institution" placeholder="" type="text" class="form-control"  value="<?php echo set_value('previous_institution') ?>" />
                                            <span class="text-danger"><?php echo form_error('previous_institution'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="subject_expertise">Subject Expertise</label>
                                            <input id="subject_expertise" name="subject_expertise" placeholder="" type="text" class="form-control"  value="<?php echo set_value('subject_expertise') ?>" />
                                            <span class="text-danger"><?php echo form_error('subject_expertise'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="box-group collapsed-box">
                                <div class="panel box box-success collapsed-box">
                                    <div class="box-header with-border">
                                        <a data-widget="collapse" data-original-title="Collapse" class="collapsed btn boxplus">
                                            <i class="fa fa-fw fa-plus"></i><?php echo $this->lang->line('add_more_details'); ?>
                                        </a>
                                    </div>
                                    <div class="box-body">
                                        <div class="tshadow mb25 bozero">
                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('payroll'); ?>
                                            </h4>
                                            <div class="row around10">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="uan_no">UAN Number</label>
                                                        <input id="uan_no" name="uan_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('uan_no') ?>" />
                                                        <span class="text-danger"><?php echo form_error('uan_no'); ?></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="esi_no"><?php echo $this->lang->line('esi_no') ?: 'ESI No.'; ?></label>
                                                        <input id="esi_no" name="esi_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('esi_no') ?>"  />
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
                                            <div class="row around10">
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
                                                <?php if ($sch_setting->staff_basic_salary) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1">
                                                                <?php echo $this->lang->line('contract_basic_salary') ?: ($this->lang->line('basic_salary') . ' (Contract)'); ?>
                                                                <small class="text-muted"><i class="fa fa-info-circle" data-toggle="tooltip" title="Contracted/appointed basic salary from employment letter. This is used as the starting point for first payslip."></i></small>
                                                            </label>
                                                            <input type="text" class="form-control" name="basic_salary" value="<?php echo set_value('basic_salary') ?>" placeholder="Enter contract basic salary">
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($sch_setting->staff_work_shift) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('work_shift'); ?></label>
                                                            <input id="shift" name="shift" placeholder="" type="text" class="form-control"  value="<?php echo set_value('shift') ?>" />
                                                            <span class="text-danger"><?php echo form_error('shift'); ?></span>
                                                        </div>
                                                    </div>
                                                <?php } if ($sch_setting->staff_work_location) {?>
                                                    <div class="col-md-4">
                                                        <div class="form-group">

                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('work_location'); ?></label>
                                                            <input id="location" name="location" placeholder="" type="text" class="form-control"  value="<?php echo set_value('location') ?>" />
                                                            <span class="text-danger"><?php echo form_error('location'); ?></span>
                                                        </div>
                                                    </div>
                                                <?php }?>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_leaves) {
    ?>
                                            <div class="tshadow mb25 bozero">
                                                <h4 class="pagetitleh2"><?php echo $this->lang->line('leaves'); ?>
                                                </h4>
                                                <div class="row around10" >
                                                    <?php
foreach ($leavetypeList as $key => $leave) {
        ?>

                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="exampleInputEmail1"><?php echo $leave["type"]; ?></label>

                                                                <input  name="leave_type[]" type="hidden" readonly class="form-control" value="<?php echo $leave['id'] ?>" />
                                                                <input  name="alloted_leave_<?php echo $leave['id'] ?>" placeholder="<?php echo $this->lang->line('number_of_leaves'); ?>" type="text" class="form-control" />

                                                                <span class="text-danger"><?php echo form_error('alloted_leave'); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php }?>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_account_details) {?>
                                            <div class="tshadow mb25 bozero">
                                                <h4 class="pagetitleh2"><?php echo $this->lang->line('bank_account_details'); ?>
                                                </h4>

                                                <div class="row around10">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('account_title'); ?></label>
                                                            <input id="account_title" name="account_title" placeholder="" type="text" class="form-control"  value="<?php echo set_value('account_title') ?>" />
                                                            <span class="text-danger"><?php echo form_error('account_title'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_account_number'); ?></label>
                                                            <input id="bank_account_no" name="bank_account_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('bank_account_no') ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_name'); ?></label>
                                                            <input id="bank_name" name="bank_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('bank_name') ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('ifsc_code'); ?></label>
                                                            <input id="ifsc_code" name="ifsc_code" placeholder="" type="text" class="form-control"  value="<?php echo set_value('ifsc_code') ?>" />
                                                            <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_branch_name'); ?></label>
                                                            <input id="bank_branch" name="bank_branch" placeholder="" type="text" class="form-control"  value="<?php echo set_value('bank_branch') ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_branch'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_social_media) {?>
                                            <div class="tshadow mb25 bozero">
                                                <h4 class="pagetitleh2"><?php echo $this->lang->line('social_media'); ?>
                                                </h4>

                                                <div class="row around10">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('facebook_url'); ?></label>
                                                            <input id="bank_account_no" name="facebook" placeholder="" type="text" class="form-control"  value="<?php echo set_value('facebook') ?>" />
                                                            <span class="text-danger"><?php echo form_error('facebook'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('twitter_url'); ?></label>
                                                            <input id="bank_account_no" name="twitter" placeholder="" type="text" class="form-control"  value="<?php echo set_value('twitter') ?>" />
                                                            <span class="text-danger"><?php echo form_error('twitter_profile'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('linkedin_url'); ?></label>
                                                            <input id="bank_name" name="linkedin" placeholder="" type="text" class="form-control"  value="<?php echo set_value('linkedin') ?>" />
                                                            <span class="text-danger"><?php echo form_error('linkedin'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('instagram_url'); ?></label>
                                                            <input id="instagram" name="instagram" placeholder="" type="text" class="form-control"  value="<?php echo set_value('instagram') ?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } if ($sch_setting->staff_upload_documents) {?>
                                            <div id='upload_documents_hide_show'>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="tshadow bozero">
                                                            <h4 class="pagetitleh2"><?php echo $this->lang->line('upload_documents'); ?></h4>

                                                            <div class="row around10">
                                                                <div class="col-md-6">
                                                                    <table class="table">
                                                                        <tbody><tr>
                                                                                <th style="width: 10px">#</th>
                                                                                <th><?php echo $this->lang->line('title'); ?></th>
                                                                                <th><?php echo $this->lang->line('documents'); ?></th>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>1.</td>
                                                                                <td><?php echo $this->lang->line('resume'); ?></td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='first_doc' id="doc1" >
                                                                                    <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>3.</td>
                                                                                <td><?php echo $this->lang->line('resignation_letter'); ?></td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='third_doc' id="doc3" >
                                                                                   
                                                                                    <span class="text-danger"><?php echo form_error('third_doc'); ?></span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <table class="table">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th style="width: 10px">#</th>
                                                                                <th><?php echo $this->lang->line('title'); ?></th>
                                                                                <th><?php echo $this->lang->line('documents'); ?></th>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>2.</td>
                                                                                <td><?php echo $this->lang->line('joining_letter'); ?></td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='second_doc' id="doc2" >
                                                                                    <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>4.</td>
                                                                                <td><?php echo $this->lang->line('other_documents'); ?><input type="hidden" name='fourth_title' class="form-control" placeholder="Other Documents"></td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='fourth_doc' id="doc4" >
                                                                                    <span class="text-danger"><?php echo form_error('fourth_doc'); ?></span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="submitbtn" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
</section>
</div>

<script>
    $(function(){
        $('#form1'). submit( function() {
            $("#submitbtn").button('loading');
        });
    })
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>