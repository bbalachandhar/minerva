<?php if (!empty($student_birthdays)) { ?>
    <div class="birthday-ticker-clipper">
        <div class="birthday-ticker-content" style="animation-duration: 20s;">
            <?php foreach (array_merge($student_birthdays, $student_birthdays) as $student) {
                $gender_icon = 'fa-user';
                $avatar_bg = 'linear-gradient(135deg, #94a3b8, #cbd5e1)';
                if (!empty($student["gender"])) {
                    if (strtolower($student["gender"]) === 'male') {
                        $gender_icon = 'fa-male';
                        $avatar_bg = 'linear-gradient(135deg, #3b82f6, #60a5fa)';
                    } elseif (strtolower($student["gender"]) === 'female') {
                        $gender_icon = 'fa-female';
                        $avatar_bg = 'linear-gradient(135deg, #ec4899, #f472b6)';
                    }
                }
            ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                <div style="flex-shrink:0;">
                    <?php if (!empty($student["image"])) { ?>
                        <img src="<?php echo base_url('uploads/student_images/' . $student["image"]); ?>" alt="" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                    <?php } else { ?>
                        <div style="width:42px;height:42px;border-radius:50%;background:<?php echo $avatar_bg; ?>;display:flex;align-items:center;justify-content:center;">
                            <i class="fa <?php echo $gender_icon; ?>" style="font-size:18px;color:#fff;"></i>
                        </div>
                    <?php } ?>
                </div>
                <div style="flex:1;min-width:0;overflow:hidden;">
                    <div style="font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $student["firstname"] . " " . $student["lastname"]; ?></div>
                    <div style="font-size:11px;color:#64748b;margin-top:1px;"><?php echo $student["class"] . " (" . $student["section"] . ")"; ?></div>
                </div>
                <?php if (!empty($student["mobileno"])) { ?>
                <div style="flex-shrink:0;font-size:10px;color:#94a3b8;background:#f8fafc;padding:2px 8px;border-radius:6px;"><?php echo $student["mobileno"]; ?></div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
<?php } else { ?>
    <div style="padding:30px 20px;text-align:center;">
        <div style="font-size:28px;margin-bottom:8px;">🎂</div>
        <div style="font-size:12px;color:#94a3b8;font-weight:500;"><?php echo $this->lang->line('no_record_found'); ?></div>
    </div>
<?php } ?>
