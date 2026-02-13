<?php if (!empty($staff_birthdays)) { ?>
    <div class="birthday-ticker-clipper">
        <div class="birthday-ticker-content" style="animation-duration: 20s;">
            <div class="mediarow">
                <div class="row">
                    <?php foreach (array_merge($staff_birthdays, $staff_birthdays) as $staff) { ?>
                        <div class="col-lg-12 col-md-12 col-sm-12 img_div_modal">
                            <div class="staffinfo-box">
                                <div class="staffleft-box">
                                    <?php
                                    $gender_icon = 'fa-user';
                                    if (!empty($staff["gender"])) {
                                        if (strtolower($staff["gender"]) === 'male') {
                                            $gender_icon = 'fa-male';
                                        } elseif (strtolower($staff["gender"]) === 'female') {
                                            $gender_icon = 'fa-female';
                                        }
                                    }
                                    
                                    if (!empty($staff["image"])) {
                                        $image = "uploads/staff_images/" . $staff["image"];
                                        echo '<img src="' . base_url() . $image . '" alt="User Image">';
                                    } else {
                                        echo '<div style="display: inline-block; width: 60px; height: 60px; background: #f8f9fa; border-radius: 50%; text-align: center; line-height: 60px;">';
                                        echo '<i class="fa ' . $gender_icon . '" style="font-size: 30px; color: #999;"></i>';
                                        echo '</div>';
                                    }
                                    ?>
                                    <div class="birthday-date">
                                        <?php echo date('d M', strtotime($staff['dob'])); ?>
                                    </div>
                                </div>
                                <div class="staffleft-content">
                                    <h5><span><?php echo $staff["name"] . " " . $staff["surname"]; ?></span></h5>
                                    <p><font><?php echo $staff["employee_id"]; ?></font></p>
                                    <p><font><?php echo $staff["contact_no"]; ?></font></p>
                                    <p><font><?php echo $staff["department"]; ?></font></p>
                                    <p class="staffsub"><span data-toggle="tooltip" title="<?php echo $this->lang->line('role'); ?>"><?php echo $staff["role"]; ?></span> <span data-toggle="tooltip" title="<?php echo 'Designation'; ?>"> <?php echo $staff["designation"]; ?></span></p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="birthday-ticker-clipper">
        <div class="birthday-ticker-content" style="animation-duration: 20s;">
            <p class="text-center"><?php echo $this->lang->line('no_record_found'); ?></p>
        </div>
    </div>
<?php } ?>
