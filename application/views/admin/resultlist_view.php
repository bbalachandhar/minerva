<?php if (empty($resultlist)) { ?>
<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('no_record_found'); ?></div>
<?php } else {
    $count = 1;
?>
<div class="row">
    <?php foreach ($resultlist as $student) {
        if (empty($student["image"])) {
            if ($student['gender'] == 'Female') {
                $image = "uploads/student_images/default_female.jpg";
            } else {
                $image = "uploads/student_images/default_male.jpg";
            }
        } else {
            $image = $student['image'];
        }
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="student-card">
            <div class="student-card-body">
                <div class="row">
                    <div class="col-xs-4 text-center">
                        <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>">
                            <?php if ($sch_setting->student_photo) { ?>
                            <img class="student-card-img img-responsive img-thumbnail" alt="<?php echo $student["firstname"] . " " . $student["lastname"] ?>" src="<?php echo base_url() . $image; ?>">
                            <?php } ?>
                        </a>
                    </div>
                    <div class="col-xs-8">
                        <h4 style="margin-top: 0;">
                            <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>">
                                <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
                            </a>
                        </h4>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('class'); ?>:</strong> <?php echo $student['class'] . "(" . $student['section'] . ")" ?></p>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('admission_no'); ?>:</strong> <?php echo $student['admission_no'] ?></p>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('date_of_birth'); ?>:</strong>
                            <?php if ($student["dob"] != null && $student["dob"] != '0000-00-00') { echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob'])); } ?>
                        </p>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('gender'); ?>:</strong> <?php echo $this->lang->line(strtolower($student['gender'])) ?></p>
                    </div>
                </div>
                <div class="row" style="margin-top: 8px;">
                    <div class="col-xs-6">
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('local_identification_no'); ?>:</strong> <?php echo $student['samagra_id'] ?></p>
                        <?php if ($sch_setting->guardian_name) { ?>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('guardian_name'); ?>:</strong> <?php echo $student['guardian_name'] ?></p>
                        <?php } ?>
                    </div>
                    <div class="col-xs-6">
                        <?php if ($sch_setting->guardian_name) { ?>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('guardian_phone'); ?>:</strong> <i class="fa fa-phone-square"></i> <?php echo $student['guardian_phone'] ?></p>
                        <?php } ?>
                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('current_address'); ?>:</strong> <?php echo $student['current_address'] ?> <?php echo $student['city'] ?></p>
                    </div>
                </div>
            </div>
            <div class="student-card-actions">
                <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                    <i class="fa fa-reorder"></i>
                </a>
                <?php if ($this->rbac->hasPrivilege('student', 'can_edit')) { ?>
                <a href="<?php echo base_url(); ?>student/edit/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                    <i class="fa fa-pencil"></i>
                </a>
                <?php } ?>
                <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                <a href="<?php echo base_url(); ?>studentfee/addfee/<?php echo $student['student_session_id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('add_fees'); ?>">
                    <?php echo $currency_symbol; ?>
                </a>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
        $count++;
    }
    ?>
</div>
<?php } ?>
