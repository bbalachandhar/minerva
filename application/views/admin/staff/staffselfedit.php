<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('edit_my_profile'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form id="form1" action="<?php echo site_url('admin/staff/selfedit/' . $staff["id"]) ?>"  id="staffselfeditform" name="staffselfeditform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="tshadow mb25 bozero">
                                <h4 class="pagetitleh2"><?php echo $this->lang->line('basic_information'); ?> </h4>
                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');
}?>
                                    <?php echo $this->customlib->getCSRF(); ?>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('staff_id'); ?></label>
                                                <input id="employee_id" name="employee_id" type="text" class="form-control"  value="<?php echo $staff["employee_id"] ?>" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="biometric_id"><?php echo $this->lang->line('biometric_id'); ?></label>
                                                <input id="biometric_id" name="biometric_id" type="text" class="form-control"  value="<?php echo set_value('biometric_id', $staff['biometric_id']); ?>" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('role'); ?></label>
                                                <input id="role_name" name="role_name" type="text" class="form-control"  value="<?php echo $staff["user_type"]; ?>" readonly />
                                                <input type="hidden" name="role" value="<?php echo $staff["role_id"]; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('first_name'); ?></label>
                                                <input id="firstname" name="name" type="text" class="form-control"  value="<?php echo set_value('name', $staff["name"]); ?>" readonly />
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_last_name) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('last_name'); ?></label>
                                                    <input id="surname" name="surname" type="text" class="form-control"  value="<?php echo set_value('surname', $staff["surname"]); ?>" readonly />
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <!-- add gender and dob row for self-edit -->
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gender"><?php echo $this->lang->line('gender'); ?></label><small class="req"> *</small>
                                                <select name="gender" class="form-control">
                                                    <?php foreach ($genderList as $gkey => $gval) {
                                                        $sel = ($staff['gender'] == $gkey) ? 'selected' : '';
                                                        echo "<option value='" . $gkey . "' " . $sel . ">" . $gval . "</option>";
                                                    } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('gender'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="dob"><?php echo $this->lang->line('date_of_birth'); ?></label><small class="req"> *</small>
                                                <input id="dob" name="dob" placeholder="" type="text" class="form-control date" value="<?php if (!empty($staff['dob'])) { echo date($this->customlib->getSchoolDateFormat(), strtotime($staff['dob'])); } ?>" />
                                                <span class="text-danger"><?php echo form_error('dob'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_date_of_joining) { ?>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="date_of_joining"><?php echo $this->lang->line('date_of_joining'); ?></label>
                                                <input id="date_of_joining" name="date_of_joining" placeholder="" type="text" class="form-control date" value="<?php if (!empty($staff['date_of_joining'])) { echo date($this->customlib->getSchoolDateFormat(), strtotime($staff['date_of_joining'])); } ?>" />
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo $this->lang->line('email'); ?></label><small class="req"> *</small>
                                                <input id="email" name="email" placeholder="" type="text" class="form-control"  value="<?php echo set_value('email', $staff["email"]); ?>" />
                                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                                            </div>
                                        </div>
                                        <?php if ($sch_setting->staff_phone) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('phone'); ?></label>
                                                    <input id="mobileno" name="contactno" placeholder="" type="text" class="form-control"  value="<?php echo set_value('contactno', $staff["contact_no"]); ?>" />
                                                    <input id="editid" name="editid" placeholder="" type="hidden" class="form-control"  value="<?php echo $staff["id"]; ?>" />
                                                    <span class="text-danger"><?php echo form_error('contactno'); ?></span>
                                                </div>
                                            </div>
                                        <?php }if ($sch_setting->staff_emergency_contact) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('emergency_contact_number'); ?></label>
                                                    <input id="emergency_no" name="emergency_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('emergency_no', isset($staff["emergency_contact_number"]) ? $staff["emergency_contact_number"] : ''); ?>" />
                                                    <span class="text-danger"><?php echo form_error('emergency_no'); ?></span>
                                                </div>
                                            </div>
                                        <?php }if ($sch_setting->staff_photo) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('photo'); ?></label>
                                                    <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                                    </div>
                                                    <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->staff_current_address) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('current_address'); ?></label>
                                                    <div><textarea name="address" class="form-control"><?php echo set_value('address', $staff["local_address"]) ?></textarea>
                                                    </div>
                                                    <span class="text-danger"></span></div>
                                            </div>
                                        <?php }if ($sch_setting->staff_permanent_address) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="exampleInputFile"><?php echo $this->lang->line('permanent_address'); ?></label>
                                                    <div><textarea name="permanent_address" class="form-control"><?php echo set_value('permanent_address', $staff["permanent_address"]); ?></textarea>
                                                    </div>
                                                    <span class="text-danger"></span></div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->staff_qualification) {?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="ug_qualification"><?php echo $this->lang->line('ug_qualification'); ?></label>
                                                    <input id="ug_qualification" name="ug_qualification" placeholder="" type="text" class="form-control"  value="<?php echo set_value('ug_qualification', $staff["ug_qualification"]) ?>" />
                                                    <span class="text-danger"><?php echo form_error('ug_qualification'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="pg_qualification"><?php echo $this->lang->line('pg_qualification'); ?></label>
                                                    <input id="pg_qualification" name="pg_qualification" placeholder="" type="text" class="form-control"  value="<?php echo set_value('pg_qualification', $staff["pg_qualification"]) ?>" />
                                                    <span class="text-danger"><?php echo form_error('pg_qualification'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="higher_qualification"><?php echo $this->lang->line('higher_qualification'); ?></label>
                                                    <input id="higher_qualification" name="higher_qualification" placeholder="" type="text" class="form-control"  value="<?php echo set_value('higher_qualification', $staff["higher_qualification"]) ?>" />
                                                    <span class="text-danger"><?php echo form_error('higher_qualification'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="qualified_exam"><?php echo $this->lang->line('qualified_exam'); ?></label>
                                                    <input id="qualified_exam" name="qualified_exam" placeholder="" type="text" class="form-control"  value="<?php echo set_value('qualified_exam', $staff["qualified_exam"]) ?>" />
                                                    <span class="text-danger"><?php echo form_error('qualified_exam'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <?php if ($sch_setting->staff_qualification) {?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="subject_specialization"><?php echo $this->lang->line('subject_specialization'); ?></label>
                                                    <input id="subject_specialization" name="subject_specialization" placeholder="" type="text" class="form-control"  value="<?php echo set_value('subject_specialization', $staff["subject_specialization"]) ?>" />
                                                    <span class="text-danger"><?php echo form_error('subject_specialization'); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="additional_qualification"><?php echo $this->lang->line('additional_qualification'); ?></label>
                                                    <textarea id="additional_qualification" name="additional_qualification" placeholder="" class="form-control"><?php echo set_value('additional_qualification', $staff["additional_qualification"]) ?></textarea>
                                                    <span class="text-danger"><?php echo form_error('additional_qualification'); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="row">
                                        <?php
                                            echo display_custom_fields('staff', $staff['id']);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($sch_setting->staff_social_media) {?>
                                <div class="tshadow mb25 bozero">
                                    <h4 class="pagetitleh2"><?php echo $this->lang->line('social_media_link'); ?></h4>
                                    <div class="row around10">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="facebook"><?php echo $this->lang->line('facebook_url'); ?></label>
                                                <input id="facebook" name="facebook" placeholder="" type="text" class="form-control"  value="<?php echo $staff["facebook"] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="twitter"><?php echo $this->lang->line('twitter_url'); ?></label>
                                                <input id="twitter" name="twitter" placeholder="" type="text" class="form-control"  value="<?php echo $staff["twitter"] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="linkedin"><?php echo $this->lang->line('linkedin_url'); ?></label>
                                                <input id="linkedin" name="linkedin" placeholder="" type="text" class="form-control"  value="<?php echo $staff["linkedin"] ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="instagram"><?php echo $this->lang->line('instagram_url'); ?></label>
                                                <input id="instagram" name="instagram" placeholder="" type="text" class="form-control"  value="<?php echo $staff["instagram"] ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                                            <div class="tshadow mb25 bozero">
                                                <h4 class="pagetitleh2"><?php echo $this->lang->line('bank_account_details'); ?>
                                                </h4>
                                                <div class="row around10">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('account_title'); ?></label>
                                                            <input id="account_title" name="account_title" placeholder="" type="text" class="form-control"  value="<?php echo $staff["account_title"] ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_account_number'); ?></label>
                                                            <input id="bank_account_no" name="bank_account_no" placeholder="" type="text" class="form-control"  value="<?php echo $staff["bank_account_no"] ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_account_no'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_name'); ?></label>
                                                            <input id="bank_name" name="bank_name" placeholder="" type="text" class="form-control"  value="<?php echo $staff["bank_name"] ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_name'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('ifsc_code'); ?></label>
                                                            <input id="ifsc_code" name="ifsc_code" placeholder="" type="text" class="form-control"  value="<?php echo $staff["ifsc_code"] ?>" />
                                                            <span class="text-danger"><?php echo form_error('ifsc_code'); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('bank_branch_name'); ?></label>
                                                            <input id="bank_branch" name="bank_branch" placeholder="" type="text" class="form-control"  value="<?php echo $staff["bank_branch"] ?>" />
                                                            <span class="text-danger"><?php echo form_error('bank_branch'); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
                                                                                    <input class=" form-control" type='hidden' name='resume' value="<?php echo $staff["resume"] ?>" >
                                                                                    <span class="text-danger"><?php echo form_error('first_doc'); ?></span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>3.</td>
                                                                                <td><?php echo $this->lang->line('resignation_letter'); ?></td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='third_doc' id="doc3" >
                                                                                    <input class=" form-control" type='hidden' name='resignation_letter' value="<?php echo $staff["resignation_letter"] ?>" >
                                                                                    <span class="text-danger"><?php echo form_error('third_doc'); ?></span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <table class="table">
                                                                        <tbody><tr>
                                                                                <th style="width: 10px">#</th>
                                                                                <th><?php echo $this->lang->line('title'); ?></th>
                                                                                <th><?php echo $this->lang->line('documents'); ?></th>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>2.</td>
                                                                                <td><?php echo $this->lang->line('joining_letter'); ?></td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='second_doc' id="doc2" >
                                                                                    <input class=" form-control" type='hidden' name='joining_letter' value="<?php echo $staff["joining_letter"] ?>" >
                                                                                    <span class="text-danger"><?php echo form_error('second_doc'); ?></span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>4.</td>
                                                                                <td><?php echo $this->lang->line('other_documents'); ?><input type="hidden" name='fourth_title' value="<?php echo $staff["other_document_file"] ?>" class="form-control" placeholder="Other Documents">
                                                                                </td>
                                                                                <td>
                                                                                    <input class="filestyle form-control" type='file' name='fourth_doc'  id="doc4" >
                                                                                    <input class=" form-control" type='hidden' name='other_document_file' value="<?php echo $staff["other_document_file"] ?>" >
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
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>