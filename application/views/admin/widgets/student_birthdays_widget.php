<?php if (!empty($student_birthdays)) { ?>
    <div class="birthday-ticker-clipper">
        <div class="birthday-ticker-content" style="animation-duration: 20s;">
            <div class="mediarow">
                <div class="row">
                    <?php foreach (array_merge($student_birthdays, $student_birthdays) as $student) { ?>
                        <div class="col-lg-12 col-md-12 col-sm-12 img_div_modal">
                            <div class="staffinfo-box">
                                <div class="staffleft-box">
                                    <?php
                                    if (!empty($student["image"])) {
                                        $image = "uploads/student_images/" . $student["image"];
                                    } else {
                                        $image = "uploads/student_images/no_image.png";
                                    }
                                    ?>
                                    <img src="<?php echo base_url() . $image; ?>" alt="User Image">
                                </div>
                                <div class="staffleft-content">
                                    <h5><span><?php echo $student["firstname"] . " " . $student["lastname"]; ?></span></h5>
                                    <p><font><?php echo $student["class"] . " (" . $student["section"] . ")"; ?></font></p>
                                    <p><font><?php echo $student["mobileno"]; ?></font></p>
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
