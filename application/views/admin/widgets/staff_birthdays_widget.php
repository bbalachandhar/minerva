<?php if (!empty($staff_birthdays)) { ?>
    <div class="birthday-ticker-clipper">
        <div class="birthday-ticker-content" style="animation-duration: 20s;">
            <?php foreach (array_merge($staff_birthdays, $staff_birthdays) as $staff) {
                $gender_icon = 'fa-user';
                $avatar_bg = 'linear-gradient(135deg, #94a3b8, #cbd5e1)';
                if (!empty($staff["gender"])) {
                    if (strtolower($staff["gender"]) === 'male') {
                        $gender_icon = 'fa-male';
                        $avatar_bg = 'linear-gradient(135deg, #3b82f6, #60a5fa)';
                    } elseif (strtolower($staff["gender"]) === 'female') {
                        $gender_icon = 'fa-female';
                        $avatar_bg = 'linear-gradient(135deg, #ec4899, #f472b6)';
                    }
                }
                $bday_display = date('d M', strtotime($staff['dob']));
            ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                <div style="flex-shrink:0;position:relative;">
                    <?php if (!empty($staff["image"])) { ?>
                        <img src="<?php echo base_url('uploads/staff_images/' . $staff["image"]); ?>" alt="" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                    <?php } else { ?>
                        <div style="width:42px;height:42px;border-radius:50%;background:<?php echo $avatar_bg; ?>;display:flex;align-items:center;justify-content:center;">
                            <i class="fa <?php echo $gender_icon; ?>" style="font-size:18px;color:#fff;"></i>
                        </div>
                    <?php } ?>
                    <div style="position:absolute;bottom:-2px;right:-4px;background:#4f46e5;color:#fff;font-size:8px;font-weight:700;padding:1px 5px;border-radius:8px;letter-spacing:0.3px;white-space:nowrap;"><?php echo $bday_display; ?></div>
                </div>
                <div style="flex:1;min-width:0;overflow:hidden;">
                    <div style="font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $staff["name"] . " " . $staff["surname"]; ?></div>
                    <div style="font-size:11px;color:#64748b;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo $staff["department"]; ?></div>
                    <div style="margin-top:3px;">
                        <?php if (!empty($staff["role"])) { ?><span style="font-size:9px;font-weight:600;color:#475569;background:#f1f5f9;padding:2px 6px;border-radius:4px;margin-right:3px;"><?php echo $staff["role"]; ?></span><?php } ?>
                        <?php if (!empty($staff["designation"])) { ?><span style="font-size:9px;font-weight:600;color:#475569;background:#f1f5f9;padding:2px 6px;border-radius:4px;"><?php echo $staff["designation"]; ?></span><?php } ?>
                    </div>
                </div>
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
