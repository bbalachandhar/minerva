<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

if (!empty($students->data)) {
?>
<div class="row" style="display:flex;flex-wrap:wrap;margin:0 -8px;">
<?php
    foreach ($students->data as $student_key => $student) {
        $gender_icon = 'fa-user';
        $gender_color = '#94a3b8';
        if (!empty($student->gender)) {
            if (strtolower($student->gender) === 'male') {
                $gender_icon = 'fa-male';
                $gender_color = '#3b82f6';
            } elseif (strtolower($student->gender) === 'female') {
                $gender_icon = 'fa-female';
                $gender_color = '#ec4899';
            }
        }

        $has_image = !empty($student->image);
        $full_name = $this->customlib->getFullName(
            $student->firstname,
            $student->middlename,
            $student->lastname,
            $sch_setting->middlename,
            $sch_setting->lastname
        );
        $view_url  = base_url() . 'student/view/' . $student->id;
        $edit_url  = base_url() . 'student/edit/' . $student->id;
        $fees_url  = base_url() . 'studentfee/addfee/' . $student->student_session_id;
?>
    <div class="col-md-6 col-lg-4" style="padding:8px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;height:100%;display:flex;flex-direction:column;transition:box-shadow .2s ease;">

            <!-- Header -->
            <div style="display:flex;align-items:center;gap:14px;padding:16px 16px 12px;">
                <a href="<?php echo $view_url; ?>" style="flex-shrink:0;text-decoration:none;">
                    <?php if ($sch_setting->student_photo && $has_image) { ?>
                        <img src="<?php echo $this->media_storage->getImageURL($student->image); ?>"
                             alt="<?php echo htmlspecialchars($full_name); ?>"
                             style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                    <?php } else { ?>
                        <div style="width:56px;height:56px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;border:2px solid #e2e8f0;">
                            <i class="fa <?php echo $gender_icon; ?>" style="font-size:24px;color:<?php echo $gender_color; ?>;"></i>
                        </div>
                    <?php } ?>
                </a>
                <div style="min-width:0;flex:1;">
                    <a href="<?php echo $view_url; ?>" style="font-size:16px;font-weight:700;color:#0f172a;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?php echo $full_name; ?>
                    </a>
                    <div style="margin-top:4px;">
                        <span style="background:#e0e7ff;color:#4f46e5;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:600;white-space:nowrap;">
                            <?php echo $student->class . ' (' . $student->section . ')'; ?>
                        </span>
                        <?php if (!empty($student->roll_no)) { ?>
                        <span style="background:#fef3c7;color:#92400e;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:600;margin-left:4px;white-space:nowrap;">
                            #<?php echo $student->roll_no; ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div style="padding:0 16px 14px;flex:1;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">

                    <div>
                        <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;letter-spacing:0.5px;margin-bottom:1px;">
                            <?php echo $this->lang->line('admission_no'); ?>
                        </div>
                        <div style="font-size:13px;color:#475569;font-weight:500;">
                            <?php echo $student->admission_no; ?>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;letter-spacing:0.5px;margin-bottom:1px;">
                            <?php echo $this->lang->line('date_of_birth'); ?>
                        </div>
                        <div style="font-size:13px;color:#475569;">
                            <?php
                            if ($student->dob != null && $student->dob != '0000-00-00') {
                                echo $this->customlib->dateFormat($student->dob);
                            } else {
                                echo '—';
                            }
                            ?>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;letter-spacing:0.5px;margin-bottom:1px;">
                            <?php echo $this->lang->line('gender'); ?>
                        </div>
                        <div style="font-size:13px;color:#475569;">
                            <i class="fa <?php echo $gender_icon; ?>" style="color:<?php echo $gender_color; ?>;margin-right:3px;"></i>
                            <?php echo !empty($student->gender) ? $this->lang->line(strtolower($student->gender)) : '—'; ?>
                        </div>
                    </div>

                    <?php if ($sch_setting->guardian_name && !empty($student->guardian_name)) { ?>
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;letter-spacing:0.5px;margin-bottom:1px;">
                            <?php echo $this->lang->line('guardian_name'); ?>
                        </div>
                        <div style="font-size:13px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?php echo $student->guardian_name; ?>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($sch_setting->guardian_name && !empty($student->guardian_phone)) { ?>
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;letter-spacing:0.5px;margin-bottom:1px;">
                            <?php echo $this->lang->line('guardian_phone'); ?>
                        </div>
                        <div style="font-size:13px;color:#475569;">
                            <i class="fa fa-phone" style="color:#94a3b8;margin-right:3px;font-size:11px;"></i>
                            <?php echo $student->guardian_phone; ?>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if (!empty($student->current_address) || !empty($student->city)) { ?>
                    <div style="grid-column:1/-1;">
                        <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:600;letter-spacing:0.5px;margin-bottom:1px;">
                            <?php echo $this->lang->line('current_address'); ?>
                        </div>
                        <div style="font-size:13px;color:#475569;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?php
                            $addr_parts = array_filter([$student->current_address, $student->city]);
                            echo !empty($addr_parts) ? implode(', ', $addr_parts) : '—';
                            ?>
                        </div>
                    </div>
                    <?php } ?>

                </div>
            </div>

            <!-- Actions -->
            <div style="border-top:1px solid #f1f5f9;padding:10px 16px;display:flex;align-items:center;gap:6px;margin-top:auto;">
                <a href="<?php echo $view_url; ?>"
                   style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 10px;color:#475569;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"
                   data-toggle="tooltip"
                   title="<?php echo $this->lang->line('view'); ?>">
                    <i class="fa fa-eye"></i>
                </a>

                <?php if ($this->rbac->hasPrivilege('student', 'can_edit')) { ?>
                <a href="<?php echo $edit_url; ?>"
                   style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 10px;color:#475569;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"
                   data-toggle="tooltip"
                   title="<?php echo $this->lang->line('edit'); ?>">
                    <i class="fa fa-pencil"></i>
                </a>
                <?php } ?>

                <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                <a href="<?php echo $fees_url; ?>"
                   style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 10px;color:#475569;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;"
                   data-toggle="tooltip"
                   title="<?php echo $this->lang->line('add_fees'); ?>">
                    <?php echo $currency_symbol; ?>
                </a>
                <?php } ?>

                <a type="button"
                   class="print_student_details shadow-none"
                   style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:5px 10px;color:#475569;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;cursor:pointer;"
                   data-student_id="<?php echo $student->id; ?>"
                   data-student_name="<?php echo $full_name; ?>"
                   data-admission_no="<?php echo $student->admission_no; ?>"
                   data-action="download"
                   data-placement="bottom"
                   data-toggle="tooltip"
                   data-original-title="print"
                   data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i>"
                   autocomplete="off">
                    <i class="fa fa-print"></i>
                </a>
            </div>

        </div>
    </div>
<?php
    }
?>
</div>
<?php
} else {
?>
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 20px;color:#94a3b8;">
    <div style="width:72px;height:72px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <i class="fa fa-search" style="font-size:28px;color:#cbd5e1;"></i>
    </div>
    <div style="font-size:16px;font-weight:600;color:#64748b;margin-bottom:4px;">
        <?php echo $this->lang->line('no_record_found'); ?>
    </div>
    <div style="font-size:13px;color:#94a3b8;">
        <?php echo $this->lang->line('try_different_search') ?? 'Try adjusting your search criteria'; ?>
    </div>
</div>
<?php
}
?>
