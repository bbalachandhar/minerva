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
                                    $gender_icon = 'fa-user';
                                    $bg_color = '#f8f9fa';
                                    $icon_color = '#999';
                                    if (!empty($student["gender"])) {
                                        if (strtolower($student["gender"]) === 'male') {
                                            $gender_icon = 'fa-male';
                                            $bg_color = '#1976d2';
                                            $icon_color = '#fff';
                                        } elseif (strtolower($student["gender"]) === 'female') {
                                            $gender_icon = 'fa-female';
                                            $bg_color = '#e91e8c';
                                            $icon_color = '#fff';
                                        }
                                    }
                                    
                                    if (!empty($student["image"])) {
                                        $image = "uploads/student_images/" . $student["image"];
                                        echo '<img src="' . base_url() . $image . '" alt="User Image">';
                                    } else {
                                        echo '<div style="display: inline-block; width: 60px; height: 60px; background: ' . $bg_color . '; border-radius: 50%; text-align: center; line-height: 60px;">';
                                        echo '<i class="fa ' . $gender_icon . '" style="font-size: 30px; color: ' . $icon_color . ';"></i>';
                                        echo '</div>';
                                    }
                                    ?>
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
