<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            <section class="content-header">
                <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); 
                                                    ?></h1>
            </section>
        </div>
        <div>
            <?php if ($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view')) { ?>
                <a id="sidebarCollapse" class="studentsideopen"><i class="fa fa-navicon"></i></a>
            <?php } ?>

            <?php
            $employee_id = '';
            if ($staff["employee_id"] != '') {
                $employee_id = ' (' . $staff["employee_id"] . ')';
            }
            ?>
            <aside class="studentsidebar">
                <div class="stutop" id="">
                    <!-- Create the tabs -->
                    <div class="studentsidetopfixed">
                        <p class="classtap"><?php echo $this->lang->line('staff'); ?> <a href="#" data-toggle="control-sidebar" class="studentsideclose"><i class="fa fa-times"></i>
                            </a>
                        </p>
                        <ul class="nav nav-justified studenttaps">
                            <?php foreach ($roles as $role_key => $role_value) {
                            ?>
                                <li <?php
                                    if ($staff["role_id"] == $role_value["id"]) {
                                        echo "class='active'";
                                    }
                                    ?>><a href="#role<?php echo $role_value["id"] ?>" data-toggle="tab"><?php echo $role_value["name"] ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <?php foreach ($roles as $rolet_key => $rolet_value) {
                        ?>
                            <div class="tab-pane <?php
                                                    if ($staff["role_id"] == $rolet_value["id"]) {
                                                        echo "active";
                                                    }
                                                    ?>" id="role<?php echo $rolet_value['id'] ?>">

                                <?php
                                foreach ($stafflist as $skey => $svalue) {

                                    if ($rolet_value['id'] == $svalue["role_id"]) {

                                        if (!empty($svalue["image"])) {
                                            $image = ltrim($svalue['image'], '/');
                                            if (strpos($image, 'uploads/staff_images/') === 0) {
                                                $image = substr($image, strlen('uploads/staff_images/'));
                                            }
                                        } else {
                                            if ($svalue['gender'] == 'Male') {
                                                $image = "default_male.jpg";
                                            } else {
                                                $image = "default_female.jpg";
                                            }
                                        }
                                ?>
                                        <div class="studentname">
                                            <a href="<?php echo base_url() . "admin/staff/profile/" . $svalue["id"] ?>">
                                                <div class="icon"><img src="<?php echo $this->media_storage->getImageURL("uploads/staff_images/" . $image); ?>" alt="<?php echo $this->lang->line('user_image'); ?>"></div>
                                                <div class="student-tittle"><?php echo $svalue['name'] . " " . $svalue['surname']; ?></div>
                                            </a>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    <section class="content">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/profile-v2.css">
        <script src="<?php echo base_url(); ?>backend/dist/js/profile-v2.js?v=<?php echo time(); ?>"></script>
        <div class="mn-profile-v2">
            <?php if ($this->session->flashdata('msg')) {
                echo $this->session->flashdata('msg');
                $this->session->unset_userdata('msg');
            } ?>

            <?php
            $userdata            = $this->customlib->getUserData();
            $logged_in_User      = $this->customlib->getLoggedInUserData();
            $logged_in_User_Role = json_decode($this->customlib->getStaffRole());
            $a                   = false;
            if ($staff['id'] == $logged_in_User['id']) {
                $a = true;
            } elseif ($logged_in_User_Role->id == 7 && $logged_in_User_Role->name == "Super Admin") {
                if ($staff["role_id"] == 7) {
                    if ($staff["role_id"] == 7 && $staff['id'] != $logged_in_User['id']) {
                        $a = false;
                    } else {
                        $a = true;
                    }
                } else {
                    $a = true;
                }
            }
            $logged_in_staff_id = $this->customlib->getStaffID();
            $logged_in_user_role_id = $logged_in_User_Role->id;

            $image = $staff['image'];
            if (!empty($image)) {
                $file = ltrim($staff['image'], '/');
                if (strpos($file, 'uploads/staff_images/') !== 0) {
                    $file = 'uploads/staff_images/' . $file;
                }
            } else {
                if ($staff['gender'] == 'Male') {
                    $file = "uploads/staff_images/default_male.jpg";
                } else {
                    $file = "uploads/staff_images/default_female.jpg";
                }
            }
            ?>

            <section class="student-hero-card">
                <div class="hero-left">
                    <div class="profile-image-container">
                        <img src="<?php echo $this->media_storage->getImageURL($file); ?>" alt="User profile picture">
                        <div class="profile-status-badge <?php echo ($staff['is_active'] == 1) ? 'active' : 'inactive'; ?>"><?php echo ($staff['is_active'] == 1) ? 'Active' : 'Inactive'; ?></div>
                    </div>

                    <div class="student-main-details">
                        <div class="name-section">
                            <h1><?php echo $staff['name'] . " " . $staff['surname']; ?></h1>
                            <?php if (!empty($staff['staff_type'])): ?>
                                <span class="rte-badge" style="background-color:transparent;border-color:<?php echo $staff['staff_type_color'] ?? '#ccc'; ?>;color:<?php echo $staff['staff_type_color'] ?? '#666'; ?>;">
                                    <i class="fa <?php echo $staff['staff_type_icon'] ?? 'fa-folder'; ?>"></i> <?php echo $staff['staff_type']; ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($staff['user_type'] == 'Teacher' && $rate_canview == 1) {
                                $stage     = (int) ($rate);
                                $stagehalf = "";
                                $half      = fmod($rate, 1);
                                if ($half != 0) { $stagehalf = $stage + 1; }
                            ?>
                                <span class="behaviour-score-badge">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <span class="fa fa-star<?php if ($i == $stagehalf && ($half > 0 && $half < 1)) { echo '-half-o'; } ?>" <?php if ($stage >= $i) { ?>style="color:#f59e0b;"<?php } ?>></span>
                                    <?php } ?>
                                    <?php echo substr($rate, 0, 3); ?> (<?php echo $reviews; ?> <?php echo $this->lang->line('reviews'); ?>)
                                </span>
                            <?php } ?>
                        </div>

                        <div class="primary-specs-grid">
                            <div class="spec-item">
                                <span class="spec-lbl"><?php echo $this->lang->line('staff_id'); ?></span>
                                <span class="spec-val highlight"><?php echo $staff['employee_id']; ?></span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-lbl"><?php echo $this->lang->line('role'); ?></span>
                                <span class="spec-val"><?php echo $staff['user_type']; ?></span>
                            </div>
                            <?php if ($sch_setting->staff_designation) { ?>
                                <div class="spec-item">
                                    <span class="spec-lbl"><?php echo $this->lang->line('designation'); ?></span>
                                    <span class="spec-val"><?php echo $staff['designation']; ?></span>
                                </div>
                            <?php } ?>
                            <?php if ($sch_setting->staff_department) { ?>
                                <div class="spec-item">
                                    <span class="spec-lbl"><?php echo $this->lang->line('department'); ?></span>
                                    <span class="spec-val"><?php echo $staff['department']; ?></span>
                                </div>
                            <?php } ?>
                            <?php if ($sch_setting->staff_date_of_joining) { ?>
                                <div class="spec-item">
                                    <span class="spec-lbl"><?php echo $this->lang->line('date_of_joining'); ?></span>
                                    <span class="spec-val"><?php
                                        if (!empty($staff["date_of_joining"]) && $staff["date_of_joining"] != '0000-00-00') {
                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($staff['date_of_joining']));
                                        }
                                    ?></span>
                                </div>
                            <?php } ?>
                        </div>

                        <?php if ($sch_setting->staff_barcode):
                            $bc_exists = file_exists("./uploads/staff_id_card/barcodes/" . $staff['id'] . ".png");
                            $qr_exists = file_exists("./uploads/staff_id_card/qrcode/" . $staff['id'] . ".png");
                        ?>
                        <div class="codes-flex-container hero-codes">
                            <?php if ($bc_exists): ?>
                                <div class="code-wrapper">
                                    <span class="code-title"><?php echo $this->lang->line('barcode'); ?></span>
                                    <a id="staff-barcode-img-link" href="<?php echo $this->media_storage->getImageURL('uploads/staff_id_card/barcodes/' . $staff['id'] . '.png'); ?>" target="_blank">
                                        <img id="staff-barcode-img" src="<?php echo $this->media_storage->getImageURL('uploads/staff_id_card/barcodes/' . $staff['id'] . '.png'); ?>">
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($qr_exists): ?>
                                <div class="code-wrapper">
                                    <span class="code-title"><?php echo $this->lang->line('qrcode'); ?></span>
                                    <a id="staff-qrcode-img-link" href="<?php echo $this->media_storage->getImageURL('uploads/staff_id_card/qrcode/' . $staff['id'] . '.png'); ?>" target="_blank">
                                        <img id="staff-qrcode-img" src="<?php echo $this->media_storage->getImageURL('uploads/staff_id_card/qrcode/' . $staff['id'] . '.png'); ?>">
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (!$bc_exists && !$qr_exists): ?>
                                <div class="code-wrapper" id="staff-generate-codes-row">
                                    <span class="code-title">Barcode / QR</span>
                                    <button type="button" class="mnp-btn" id="btn-generate-staff-codes" data-staff-id="<?php echo $staff['id']; ?>">
                                        <i class="fa fa-qrcode"></i> Generate
                                    </button>
                                </div>
                                <div class="code-wrapper" id="staff-generated-barcode-row" style="display:none;">
                                    <span class="code-title"><?php echo $this->lang->line('barcode'); ?></span>
                                    <a id="staff-barcode-img-link" href="#" target="_blank"><img id="staff-barcode-img" src=""></a>
                                </div>
                                <div class="code-wrapper" id="staff-generated-qrcode-row" style="display:none;">
                                    <span class="code-title"><?php echo $this->lang->line('qrcode'); ?></span>
                                    <a id="staff-qrcode-img-link" href="#" target="_blank"><img id="staff-qrcode-img" src=""></a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="hero-right-actions">
                    <div class="actions-header"><?php echo $this->lang->line('action'); ?></div>
                    <div class="actions-buttons-grid">
                        <?php
                        if ($this->rbac->hasPrivilege('staff', 'can_edit')) {
                            if ($logged_in_User_Role->id != 7 || $a) {
                        ?>
                                <a href="<?php echo base_url('admin/staff/edit/' . $id); ?>" class="action-btn mnp-primary"><i class="fa fa-pencil"></i><span><?php echo $this->lang->line('edit'); ?></span></a>
                        <?php
                            }
                        }
                        if ($sch_setting->staff_self_edit == 1 && $logged_in_staff_id == $id && $logged_in_user_role_id != 7) {
                        ?>
                            <a href="<?php echo base_url('admin/staff/selfedit/' . $id); ?>" class="action-btn mnp-primary"><i class="fa fa-pencil"></i><span><?php echo $this->lang->line('edit_my_profile'); ?></span></a>
                        <?php } ?>

                        <?php if ($a) { ?>
                            <a href="javascript:void(0)" class="action-btn change_password"><i class="fa fa-key"></i><span><?php echo $this->lang->line('change_password'); ?></span></a>
                        <?php } ?>

                        <?php
                        if ($enable_disable == 1) {
                            if ($staff["is_active"] == 1) {
                                if ($this->rbac->hasPrivilege('disable_staff', 'can_view')) {
                                    if ($logged_in_User_Role->id == 7) {
                                        if ($a) {
                        ?>
                                            <a href="javascript:void(0)" class="action-btn mnp-danger" onclick="disable_staff('<?php echo $id; ?>');"><i class="fa fa-thumbs-o-down"></i><span><?php echo $this->lang->line('disable'); ?></span></a>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <a href="<?php echo base_url('admin/staff/disablestaff/' . $id); ?>" class="action-btn mnp-danger" onclick="return confirm('<?php echo $this->lang->line('are_you_sure_you_want_to_disable_this_record'); ?>')"><i class="fa fa-thumbs-o-down"></i><span><?php echo $this->lang->line('disable'); ?></span></a>
                                    <?php
                                    }
                                }
                            } else if ($staff["is_active"] == 0) {
                                if ($logged_in_User_Role->id == 7) {
                                    if ($a) {
                                    ?>
                                        <a href="<?php echo base_url('admin/staff/delete/' . $id); ?>" class="action-btn mnp-danger" onclick="return confirm('<?php echo $this->lang->line('are_you_sure_want_to_delete'); ?>');"><i class="fa fa-trash"></i><span><?php echo $this->lang->line('delete'); ?></span></a>
                                        <a href="<?php echo base_url('admin/staff/enablestaff/' . $id); ?>" class="action-btn mnp-success" onclick="return confirm('<?php echo $this->lang->line('are_you_sure_you_want_to_enable_this_record'); ?>');"><i class="fa fa-thumbs-o-up"></i><span><?php echo $this->lang->line('enable'); ?></span></a>
                                    <?php
                                    }
                                } else {
                                    if ($this->rbac->hasPrivilege('staff', 'can_delete')) {
                                    ?>
                                        <a href="<?php echo base_url('admin/staff/delete/' . $id); ?>" class="action-btn mnp-danger" onclick="return confirm('<?php echo $this->lang->line('are_you_sure_want_to_delete'); ?>');"><i class="fa fa-trash"></i><span><?php echo $this->lang->line('delete'); ?></span></a>
                                    <?php }
                                    if ($this->rbac->hasPrivilege('disable_staff', 'can_view')) {
                                    ?>
                                        <a href="<?php echo base_url('admin/staff/enablestaff/' . $id); ?>" class="action-btn mnp-success" onclick="return confirm('<?php echo $this->lang->line('are_you_sure_you_want_to_enable_this_record'); ?>');"><i class="fa fa-thumbs-o-up"></i><span><?php echo $this->lang->line('enable'); ?></span></a>
                                    <?php
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </section>

            <?php if ($staff["is_active"] == 0) { ?>
                <div class="info-card" style="margin-top:-4px;">
                    <div class="card-body">
                        <div class="details-list">
                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('date_of_leaving'); ?></span><span class="mnp-value"><?php echo $this->customlib->dateformat($staff['date_of_leaving']); ?></span></div>
                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('disable_date'); ?></span><span class="mnp-value"><?php echo $this->customlib->dateformat($staff['disable_at']); ?></span></div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <nav class="navigation-tabs-bar">
            <div class="tab-bar-links">
                <button class="tab-link active" data-tab-target="activity"><?php echo $this->lang->line('profile'); ?></button>
                <button class="tab-link" data-tab-target="payroll"><?php echo $this->lang->line('payroll'); ?></button>
                <button class="tab-link" data-tab-target="leaves"><?php echo $this->lang->line('leaves'); ?></button>
                <button class="tab-link" data-tab-target="attendance"><?php echo $this->lang->line('attendance'); ?></button>
                <?php if ($sch_setting->staff_upload_documents) { ?>
                    <button class="tab-link" data-tab-target="documents"><?php echo $this->lang->line('documents'); ?></button>
                <?php } ?>
                <?php if ($this->rbac->hasPrivilege('staff_timeline', 'can_view')) { ?>
                    <button class="tab-link" data-tab-target="timelineh"><?php echo $this->lang->line('timeline'); ?></button>
                <?php } ?>
                <?php if ($staff['user_type'] == 2) { ?>
                    <button class="tab-link" data-tab-target="reviews"><?php echo $this->lang->line('reviews'); ?></button>
                <?php } ?>
            </div>
            <div class="tab-bar-actions">
                <?php if ($this->rbac->hasPrivilege('staff_timeline', 'can_view') && $this->rbac->hasPrivilege('staff_timeline', 'can_add')) { ?>
                    <button type="button" id="myTimelineButton" data-tab-actions="timelineh" class="action-btn mnp-primary"><i class="fa fa-plus"></i> <span><?php echo $this->lang->line('add') ?></span></button>
                <?php } ?>
            </div>
            </nav>

            <div>
                <div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="activity">
                            <div class="profile-grid">
                                <div class="grid-column">
                                <div class="info-card">
                                    <div class="card-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <h2>Contact &amp; Personal Details</h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="details-list">
                                            <?php if ($sch_setting->staff_phone) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('phone'); ?></span><span class="mnp-value copyable" onclick="mnpCopyText('<?php echo htmlspecialchars($staff['contact_no'], ENT_QUOTES); ?>', 'Phone')"><?php echo $staff['contact_no']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_emergency_contact) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('emergency_contact_number'); ?></span><span class="mnp-value"><?php echo $staff['emergency_contact_no']; ?></span></div>
                                            <?php } ?>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('email'); ?></span><span class="mnp-value copyable" onclick="mnpCopyText('<?php echo htmlspecialchars($staff['email'], ENT_QUOTES); ?>', 'Email')"><?php echo $staff['email']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('gender'); ?></span><span class="mnp-value"><?php echo $this->lang->line(strtolower($staff['gender'])); ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('date_of_birth'); ?></span><span class="mnp-value"><?php
                                                if (!empty($staff["dob"]) && $staff["dob"] != '0000-00-00') {
                                                    echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($staff['dob']));
                                                }
                                            ?></span></div>
                                            <?php if ($sch_setting->staff_marital_status) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('marital_status'); ?></span><span class="mnp-value"><?php echo $staff['marital_status']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_father_name) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('father_name'); ?></span><span class="mnp-value"><?php echo $staff['father_name']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_mother_name) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('mother_name'); ?></span><span class="mnp-value"><?php echo $staff['mother_name']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_qualification) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('qualification'); ?></span><span class="mnp-value"><?php echo $staff['qualification']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_work_experience) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('work_experience'); ?></span><span class="mnp-value"><?php echo $staff['work_exp']; ?></span></div>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('ug_qualification'); ?></span><span class="mnp-value"><?php echo $staff['ug_qualification']; ?></span></div>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('pg_qualification'); ?></span><span class="mnp-value"><?php echo $staff['pg_qualification']; ?></span></div>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('higher_qualification'); ?></span><span class="mnp-value"><?php echo $staff['higher_qualification']; ?></span></div>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('qualified_exam'); ?></span><span class="mnp-value"><?php echo $staff['qualified_exam']; ?></span></div>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('subject_specialization'); ?></span><span class="mnp-value"><?php echo $staff['subject_specialization']; ?></span></div>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('additional_qualification'); ?></span><span class="mnp-value"><?php echo $staff['additional_qualification']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_note && !empty($staff['note'])) { ?>
                                                <div class="detail-row full-width"><span class="mnp-label"><?php echo $this->lang->line('note'); ?></span><span class="mnp-value note-text"><?php echo $staff['note']; ?></span></div>
                                            <?php }
                                            $cutom_fields_data = get_custom_table_values($staff['id'], 'staff');
                                            if (!empty($cutom_fields_data)) {
                                                foreach ($cutom_fields_data as $field_key => $field_value) {
                                            ?>
                                                <div class="detail-row">
                                                    <span class="mnp-label"><?php echo $field_value->name; ?></span>
                                                    <span class="mnp-value">
                                                        <?php
                                                        if (is_string($field_value->field_value) && is_array(json_decode($field_value->field_value, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                                                            $field_array = json_decode($field_value->field_value);
                                                            echo "<ul>";
                                                            foreach ($field_array as $each_key => $each_value) {
                                                                echo "<li>" . $each_value . "</li>";
                                                            }
                                                            echo "</ul>";
                                                        } else {
                                                            $display_field = $field_value->field_value;
                                                            if ($field_value->type == "link") {
                                                                $display_field = "<a href=" . $field_value->field_value . " target='_blank'>" . $field_value->field_value . "</a>";
                                                            }
                                                            echo $display_field;
                                                        }
                                                        ?>
                                                    </span>
                                                </div>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="card-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <h2><?php echo $this->lang->line('address_details'); ?></h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="details-list">
                                            <?php if ($sch_setting->staff_current_address) { ?>
                                                <div class="detail-row full-width"><span class="mnp-label"><?php echo $this->lang->line('current_address'); ?></span><span class="mnp-value address-val"><?php echo $staff['local_address']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_permanent_address) { ?>
                                                <div class="detail-row full-width"><span class="mnp-label"><?php echo $this->lang->line('permanent_address'); ?></span><span class="mnp-value address-val"><?php echo $staff['permanent_address']; ?></span></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($sch_setting->staff_account_details) { ?>
                                <div class="info-card">
                                    <div class="card-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                        <h2><?php echo $this->lang->line('bank_account_details'); ?></h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="details-list">
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('account_title'); ?></span><span class="mnp-value"><?php echo $staff['account_title']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('bank_name'); ?></span><span class="mnp-value"><?php echo $staff['bank_name']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('bank_branch_name'); ?></span><span class="mnp-value"><?php echo $staff['bank_branch']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('bank_account_number'); ?></span><span class="mnp-value copyable" onclick="mnpCopyText('<?php echo htmlspecialchars($staff['bank_account_no'], ENT_QUOTES); ?>', 'Account Number')"><?php echo $staff['bank_account_no']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('ifsc_code'); ?></span><span class="mnp-value"><?php echo $staff['ifsc_code']; ?></span></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>

                                <?php if ($sch_setting->staff_social_media) { ?>
                                <div class="info-card">
                                    <div class="card-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        <h2><?php echo $this->lang->line('social_media_link'); ?></h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="details-list">
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('facebook_url'); ?></span><span class="mnp-value"><a href="<?php echo $staff['facebook']; ?>" target="_blank"><?php echo $staff['facebook']; ?></a></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('twitter_url'); ?></span><span class="mnp-value"><a href="<?php echo $staff['twitter']; ?>" target="_blank"><?php echo $staff['twitter']; ?></a></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('linkedin_url'); ?></span><span class="mnp-value"><a href="<?php echo $staff['linkedin']; ?>" target="_blank"><?php echo $staff['linkedin']; ?></a></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('instagram_url'); ?></span><span class="mnp-value"><a href="<?php echo $staff['instagram']; ?>" target="_blank"><?php echo $staff['instagram']; ?></a></span></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                </div>

                                <div class="grid-column">
                                <div class="info-card">
                                    <div class="card-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        <h2>Employment &amp; Identification</h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="details-list">
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('biometric_id'); ?></span><span class="mnp-value"><?php echo $staff['biometric_id']; ?></span></div>
                                            <?php if (!empty($staff['au_fin_no'])): ?>
                                                <div class="detail-row"><span class="mnp-label">AU FIN No.</span><span class="mnp-value"><?php echo htmlspecialchars($staff['au_fin_no']); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($staff['aicte_coa_id'])): ?>
                                                <div class="detail-row"><span class="mnp-label">AICTE / COA ID</span><span class="mnp-value"><?php echo htmlspecialchars($staff['aicte_coa_id']); ?></span></div>
                                            <?php endif; ?>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('prefix'); ?></span><span class="mnp-value"><?php echo $staff['prefix']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('esi_no') ?: ($this->lang->line('epf_no') ?: 'ESI No.'); ?></span><span class="mnp-value"><?php echo $staff['esi_no']; ?></span></div>
                                            <div class="detail-row">
                                                <span class="mnp-label"><?php echo $this->lang->line('contract_basic_salary') ?: ($this->lang->line('basic_salary') . ' (Contract)'); ?></span>
                                                <span class="mnp-value"><?php if (!empty($staff['basic_salary'])) { echo amountFormat($staff['basic_salary']); } ?></span>
                                            </div>
                                            <?php if ($sch_setting->staff_contract_type) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('contract_type'); ?></span><span class="mnp-value"><?php echo $staff['contract_type']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_work_shift) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('work_shift'); ?></span><span class="mnp-value"><?php echo $staff['shift']; ?></span></div>
                                            <?php }
                                            if ($sch_setting->staff_work_location) { ?>
                                                <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('work_location'); ?></span><span class="mnp-value"><?php echo $staff['location']; ?></span></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-card">
                                    <div class="card-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                                        <h2><?php echo $this->lang->line('additional_details'); ?></h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="details-list">
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('payscale'); ?></span><span class="mnp-value"><?php echo $staff['payscale']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('aadhaar_no'); ?></span><span class="mnp-value"><?php echo $staff['aadhaar_no']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('religion'); ?></span><span class="mnp-value"><?php echo $staff['religion']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('caste'); ?></span><span class="mnp-value"><?php echo $staff['caste']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('blood_group'); ?></span><span class="mnp-value badge-blue"><?php echo $staff['blood_group']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('country'); ?></span><span class="mnp-value"><?php echo $staff['country']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('state'); ?></span><span class="mnp-value"><?php echo $staff['state']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('pincode'); ?></span><span class="mnp-value"><?php echo $staff['pincode']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('previous_salary'); ?></span><span class="mnp-value"><?php echo $staff['previous_salary']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('uan_no'); ?></span><span class="mnp-value"><?php echo $staff['uan_no']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('pan_no'); ?></span><span class="mnp-value"><?php echo $staff['pan_no']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('previous_institution'); ?></span><span class="mnp-value"><?php echo $staff['previous_institution']; ?></span></div>
                                            <div class="detail-row"><span class="mnp-label"><?php echo $this->lang->line('subject_expertise'); ?></span><span class="mnp-value"><?php echo $staff['subject_expertise']; ?></span></div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="payroll">
                            <div class="row row-flex">
                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                    <div class="staffprofile" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                                        <h5><?php echo $this->lang->line('total_gross_salary'); ?></h5>
                                        <h4><?php
                                            if (!empty($salary["earnings"]) || !empty($salary["basic_salary"])) {
                                                $gross_salary = (float) $salary["basic_salary"] + (float) $salary["earnings"];
                                                echo $currency_symbol . amountFormat($gross_salary);
                                            } else {
                                                echo $currency_symbol . "0.00";
                                            }
                                            ?></h4>
                                        <div class="icon mt12font40">
                                            <i class="fa fa-money"></i>
                                        </div>
                                    </div>
                                </div><!--./col-md-3-->
                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                    <div class="staffprofile" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                                        <h5>Deduction</h5>
                                        <h4><?php
                                            $deduction = $salary["deduction"] + $salary["tax"]
                                                       + (float)($salary["employee_epf"] ?? 0)
                                                       + (float)($salary["employee_esi"] ?? 0);

                                            if (!empty($deduction)) {
                                                echo $currency_symbol . amountFormat($deduction);
                                            } else {
                                                echo $currency_symbol . "0.00";
                                            } ?> </h4>
                                        <div class="icon mt12font40">
                                            <i class="fa fa-minus-circle"></i>
                                        </div>
                                    </div>
                                </div><!--./col-md-3-->
                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                    <div class="staffprofile" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                                        <h5>LOP Deduction</h5>
                                        <h4><?php
                                            if (!empty($salary["leave_deduction"])) {
                                                echo $currency_symbol . amountFormat($salary["leave_deduction"]);
                                            } else {
                                                echo $currency_symbol . "0.00";
                                            } ?> </h4>
                                        <div class="icon mt12font40">
                                            <i class="fa fa-calendar-times-o"></i>
                                        </div>
                                    </div>
                                </div><!--./col-md-3-->
                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                    <div class="staffprofile" style="background: linear-gradient(135deg, #95a5a6, #7f8c8d);">
                                        <h5>Total Deduction</h5>
                                        <h4><?php
                                            $deduction = $salary["deduction"] + $salary["tax"]
                                                       + (float)($salary["employee_epf"] ?? 0)
                                                       + (float)($salary["employee_esi"] ?? 0);
                                            $total_deduction = $deduction + $salary["leave_deduction"];

                                            if (!empty($total_deduction)) {
                                                echo $currency_symbol . amountFormat($total_deduction);
                                            } else {
                                                echo $currency_symbol . "0.00";
                                            } ?> </h4>
                                        <div class="icon mt12font40">
                                            <i class="fa fa-calculator"></i>
                                        </div>
                                    </div>
                                </div><!--./col-md-3-->
                            </div>
                            <div class="table-responsive">
                                <div class="download_label"><?php echo $this->lang->line('details_for'); ?> <?php echo $staff["name"] . " " . $staff["surname"] . $employee_id; ?></div>
                                <table class="table table-hover table-striped example">
                                    <thead>
                                        <tr>
                                            <th class="text text-left"><?php echo $this->lang->line('payslip'); ?> #</th>
                                            <th class="text text-left"><?php echo $this->lang->line('month_year'); ?><span></span></th>
                                            <th class="text text-left"><?php echo $this->lang->line('date'); ?></th>
                                            <th class="text text-left"><?php echo $this->lang->line('mode'); ?></th>
                                            <th class="text text-left"><?php echo $this->lang->line('status'); ?></th>
                                            <th class="text text-right"><?php echo $this->lang->line('net_salary'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($staff_payroll as $key => $payroll_value) {

                                            if ($payroll_value["status"] == "paid") {
                                                $label = "class='label label-success'";
                                            } else if ($payroll_value["status"] == "generated") {
                                                $label = "class='label label-warning'";
                                            } else {
                                                $label = "class='label label-default'";
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <a data-toggle="popover" href="#" class="detail_popover" data-original-title="" title=""><?php echo $payroll_value['id'] ?></a>
                                                    <div class="fee_detail_popover" style="display: none"><?php echo $payroll_value['remark']; ?></div>
                                                </td>
                                                <td><?php echo $this->lang->line(strtolower($payroll_value['month'])) . " - " . $payroll_value['year']; ?></td>
                                                <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($payroll_value['payment_date'])); ?></td>
                                                <td><?php
                                                    if (!empty($payroll_value['payment_mode'])) {
                                                        echo $payment_mode[$payroll_value['payment_mode']];
                                                    }
                                                    ?></td>
                                                <td><span <?php echo $label ?>><?php echo $payroll_status[$payroll_value['status']]; ?></span></td>
                                                <td class="text text-right"><?php echo amountFormat($payroll_value['net_salary']); ?></td>
                                                <td class="text-right">
                                                    <?php if ($payroll_value["status"] == "paid") {
                                                    ?>
                                                        <?php
                                                        if (
                                                            $this->rbac->hasPrivilege('staff', 'can_view')
                                                        ) {
                                                        ?>
                                                            <a href="#" onclick="getPayslip('<?php echo $payroll_value["id"]; ?>')" role="button" class="btn btn-primary btn-xs checkbox-toggle edit_setting" data-toggle="tooltip"><?php echo $this->lang->line('view_payslip'); ?></a>

                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if ($sch_setting->staff_upload_documents) {
                        ?>
                            <div class="tab-pane" id="documents">
                                <div class="timeline-header no-border">
                                    <div class="row">
                                        <?php if ((empty($staff["resume"])) && (empty($staff["joining_letter"])) && (empty($staff["resignation_letter"])) && (empty($staff["other_document_file"]))) {
                                        ?>
                                            <div class="col-md-12">
                                                <div class="alert alert-info"><?php echo $this->lang->line("no_record_found"); ?></div>
                                            </div>
                                        <?php } else {
                                        ?>
                                            <?php if (!empty($staff["resume"])) { ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="staffprofile">
                                                        <h5><?php echo $this->lang->line('resume'); ?></h5>
                                                        <a href="<?php echo base_url(); ?>admin/staff/download/<?php echo $staff['id'] . "/" . 'resume'; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                            <i class="fa fa-download"></i></a>
                                                        <?php
                                                        if (
                                                            $this->rbac->hasPrivilege('staff', 'can_edit')
                                                        ) {
                                                        ?>
                                                            <a href="<?php echo base_url(); ?>admin/staff/doc_delete/<?php echo $staff['id'] . "/resume"; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i></a>
                                                        <?php } ?>
                                                        <div class="icon">
                                                            <i class="fa fa-file-text-o"></i>
                                                        </div>
                                                    </div>
                                                </div><!--./col-md-3-->
                                            <?php } ?>
                                            <?php if (!empty($staff["joining_letter"])) {
                                            ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="staffprofile">
                                                        <h5><?php echo $this->lang->line('joining_letter'); ?></h5>
                                                        <a href="<?php echo base_url(); ?>admin/staff/download/<?php echo $staff['id'] . "/" . 'joining_letter'; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                            <i class="fa fa-download"></i></a>
                                                        <?php
                                                        if (
                                                            $this->rbac->hasPrivilege('staff', 'can_edit')
                                                        ) {
                                                        ?>
                                                            <a href="<?php echo base_url(); ?>admin/staff/doc_delete/<?php echo $staff['id'] . "/joining_letter"; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        <?php } ?>
                                                        <div class="icon">
                                                            <i class="fa fa-file-archive-o"></i>
                                                        </div>
                                                    </div>
                                                </div><!--./col-md-3-->
                                            <?php } ?>
                                            <?php if (!empty($staff["resignation_letter"])) {
                                            ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="staffprofile">
                                                        <h5><?php echo $this->lang->line('resignation_letter'); ?></h5>
                                                        <a href="<?php echo base_url(); ?>admin/staff/download/<?php echo $staff['id'] . "/" . 'resignation_letter'; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                            <i class="fa fa-download"></i></a>
                                                        <?php
                                                        if (
                                                            $this->rbac->hasPrivilege('staff', 'can_edit')
                                                        ) {
                                                        ?>
                                                            <a href="<?php echo base_url(); ?>admin/staff/doc_delete/<?php echo $staff['id'] . "/resignation_letter"; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i></a>
                                                        <?php } ?>
                                                        <div class="icon">
                                                            <i class="fa fa-file-archive-o"></i>
                                                        </div>
                                                    </div>
                                                </div><!--./col-md-3-->
                                            <?php } ?>
                                            <?php if (!empty($staff["other_document_file"])) {
                                            ?>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="staffprofile">
                                                        <h5><?php echo $this->lang->line('other_documents'); ?></h5>
                                                        <a href="<?php echo base_url(); ?>admin/staff/download/<?php echo $staff['id'] . "/" . 'other_document_file'; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                            <i class="fa fa-download"></i></a>
                                                        <?php
                                                        if (
                                                            $this->rbac->hasPrivilege('staff', 'can_edit')
                                                        ) {
                                                        ?>
                                                            <a href="<?php echo base_url(); ?>admin/staff/doc_delete/<?php echo $staff['id'] . "/other_document_file" ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i></a>
                                                        <?php } ?>
                                                        <div class="icon">
                                                            <i class="fa fa-file-archive-o"></i>
                                                        </div>
                                                    </div>
                                                </div><!--./col-md-3-->
                                            <?php } ?>
                                        <?php } ?>
                                    </div><!--./row-->
                                </div>
                                </table>
                            </div>
                        <?php } ?>

                        <div class="tab-pane" id="timelineh">
                            <div id="timeline_list">
                                <?php
                                if (empty($timeline_list)) {
                                ?>
                                    <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                                <?php } else { ?>
                                    <div class="timeline-container">
                                        <?php foreach ($timeline_list as $key => $value) { ?>
                                            <div class="timeline-event">
                                                <div class="event-marker blue"></div>
                                                <div class="event-content">
                                                    <div class="event-actions">
                                                        <?php if ($this->rbac->hasPrivilege('staff_timeline', 'can_edit')) { ?>
                                                            <a data-toggle="tooltip" class="edit_timeline" data-id="<?php echo $value["id"]; ?>" data-original-title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                                        <?php } ?>
                                                        <?php if (!empty($value["document"])) { ?>
                                                            <a data-toggle="tooltip" href="<?php echo base_url() . "admin/timeline/download_staff_timeline/" . $value["id"] ?>" data-original-title="Download"><i class="fa fa-download"></i></a>
                                                        <?php } ?>
                                                        <?php if ($this->rbac->hasPrivilege('staff_timeline', 'can_delete')) { ?>
                                                            <a data-toggle="tooltip" onclick="delete_timeline('<?php echo $value['id']; ?>')" data-original-title="<?php echo $this->lang->line('delete'); ?>"><i class="fa fa-trash"></i></a>
                                                        <?php } ?>
                                                    </div>
                                                    <span class="event-time"><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['timeline_date'])); ?></span>
                                                    <h4><?php echo $value['title']; ?></h4>
                                                    <p><?php echo $value['description']; ?></p>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="tab-pane" id="attendance">
                            <style type="text/css">
                                .att-cell {
                                    display: inline-block;
                                    min-width: 26px;
                                    padding: 3px 5px;
                                    border-radius: 4px;
                                    font-size: 11px;
                                    font-weight: 700;
                                    color: #fff;
                                    text-align: center;
                                    line-height: 1.3;
                                    text-decoration: none;
                                }
                                .att-cell-present      { background: #10b981; }
                                .att-cell-fhl          { background: #f59e0b; }
                                .att-cell-shl          { background: #ea580c; }
                                .att-cell-fhp          { background: #06b6d4; }
                                .att-cell-shp          { background: #3b82f6; }
                                .att-cell-fha          { background: #fca5a5; color: #991b1b; }
                                .att-cell-sha          { background: #fca5a5; color: #991b1b; }
                                .att-cell-absent       { background: #ef4444; }
                                .att-cell-halfday      { background: #8b5cf6; }
                                .att-cell-weekend      { background: #64748b; }
                                .att-cell-holiday      { background: #06b6d4; }
                                .att-cell-compensation { background: #f59e0b; }
                                a.att-cell:hover { opacity: 0.85; text-decoration: none; color: #fff; }
                                .att-cell-fha:hover, .att-cell-sha:hover { color: #991b1b; }
                                .att-stat-card {
                                    background: #fff;
                                    border: 1px solid #e2e8f0;
                                    border-radius: 10px;
                                    padding: 16px;
                                    text-align: center;
                                    transition: box-shadow 0.15s;
                                }
                                .att-stat-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
                                .att-stat-label {
                                    font-size: 11px;
                                    text-transform: uppercase;
                                    color: #94a3b8;
                                    font-weight: 600;
                                    letter-spacing: 0.5px;
                                    margin-bottom: 4px;
                                }
                                .att-stat-value {
                                    font-size: 28px;
                                    font-weight: 800;
                                    line-height: 1.2;
                                }
                                .att-stat-eye {
                                    cursor: pointer;
                                    font-size: 12px;
                                    margin-left: 4px;
                                    opacity: 0.5;
                                    transition: opacity 0.15s;
                                }
                                .att-stat-eye:hover { opacity: 1; }
                                .att-pill-toggle {
                                    display: inline-flex;
                                    border: 1.5px solid #e2e8f0;
                                    border-radius: 8px;
                                    overflow: hidden;
                                }
                                .att-pill-btn {
                                    padding: 8px 20px;
                                    font-size: 13px;
                                    font-weight: 600;
                                    border: none;
                                    cursor: pointer;
                                    transition: background 0.15s, color 0.15s;
                                    outline: none;
                                }
                                .att-legend-dot {
                                    display: inline-block;
                                    width: 10px;
                                    height: 10px;
                                    border-radius: 50%;
                                    margin-right: 4px;
                                    vertical-align: middle;
                                }
                            </style>

                            <?php
                            $summary = isset($month_summary) ? $month_summary : [
                                'label' => date('F Y'),
                                'working_days' => 0,
                                'weekends' => 0,
                                'holidays' => 0,
                                'present' => 0,
                                'half_day' => 0,
                                'absent' => 0,
                                'late' => 0,
                                'permission' => 0,
                            ];
                            $total_present = $summary['present'] + ($summary['half_day'] * 0.5);
                            $total_absent = $summary['absent'] + ($summary['half_day'] * 0.5);
                            $format_count = function ($value) {
                                return rtrim(rtrim(number_format((float)$value, 1, '.', ''), '0'), '.');
                            };
                            ?>

                            <!-- Summary Stats Grid -->
                            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:20px;">
                                <div class="att-stat-card">
                                    <div class="att-stat-label"><?php echo $this->lang->line('total_present'); ?></div>
                                    <div class="att-stat-value total_present" style="color:#10b981;"><?php echo $format_count($total_present); ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label"><?php echo $this->lang->line('total_absent'); ?></div>
                                    <div class="att-stat-value total_absent" style="color:#ef4444;"><?php echo $format_count($total_absent); ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label">
                                        <?php echo $this->lang->line('total_late'); ?>
                                        <?php if ((int)$summary['late'] > 0) { ?>
                                            <i class="fa fa-eye att-stat-eye" onclick="showAttendanceDetails('late', <?php echo $staff['id']; ?>)" title="View Details"></i>
                                        <?php } ?>
                                    </div>
                                    <div class="att-stat-value total_late" style="color:#f59e0b;"><?php echo $summary['late']; ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label">
                                        <?php echo $this->lang->line('total_permission'); ?>
                                        <?php if ((int)(isset($summary['permission']) ? $summary['permission'] : 0) > 0) { ?>
                                            <i class="fa fa-eye att-stat-eye" onclick="showAttendanceDetails('permission', <?php echo $staff['id']; ?>)" title="View Details"></i>
                                        <?php } ?>
                                    </div>
                                    <div class="att-stat-value total_permission" style="color:#3b82f6;"><?php echo isset($summary['permission']) ? $summary['permission'] : 0; ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label">
                                        <?php echo $this->lang->line('total_half_day'); ?>
                                        <?php if ((int)$summary['half_day'] > 0) { ?>
                                            <i class="fa fa-eye att-stat-eye" onclick="showAttendanceDetails('halfday', <?php echo $staff['id']; ?>)" title="View Details"></i>
                                        <?php } ?>
                                    </div>
                                    <div class="att-stat-value total_half_day" style="color:#8b5cf6;"><?php echo $summary['half_day']; ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label"><?php echo $this->lang->line('total_holiday'); ?></div>
                                    <div class="att-stat-value total_holiday" style="color:#06b6d4;"><?php echo $summary['holidays']; ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label">Working Days</div>
                                    <div class="att-stat-value total_working_days" style="color:#0f172a;"><?php echo $summary['working_days']; ?></div>
                                </div>
                                <div class="att-stat-card">
                                    <div class="att-stat-label">Weekends</div>
                                    <div class="att-stat-value total_weekends" style="color:#64748b;"><?php echo $summary['weekends']; ?></div>
                                </div>
                            </div>

                            <!-- Controls Bar -->
                            <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
                                <!-- Pill toggle -->
                                <div class="att-pill-toggle">
                                    <button type="button" id="btn_year_view" class="att-pill-btn" onclick="switchAttView('year')" style="background:#4f46e5; color:#fff;">
                                        <i class="fa fa-calendar"></i> Year View
                                    </button>
                                    <button type="button" id="btn_month_view" class="att-pill-btn" onclick="switchAttView('month')" style="background:#fff; color:#475569;">
                                        <i class="fa fa-list"></i> Month View
                                    </button>
                                </div>

                                <!-- Year dropdown -->
                                <select class="form-control" id="attendance_year" style="width:auto; display:inline-block;" name="year" onchange="ajax_attendance('<?php echo $staff["id"]; ?>', this.value)">
                                    <?php foreach ($yearlist as $yearkey => $yearvalue) { ?>
                                        <option <?php if ($yearvalue["year"] == date("Y")) { echo "selected"; } ?> value="<?php echo $yearvalue["year"]; ?>"><?php echo $yearvalue["year"]; ?></option>
                                    <?php } ?>
                                </select>

                                <!-- Month dropdown (hidden initially) -->
                                <div id="att_month_selector" style="display:none;">
                                    <select class="form-control" id="attendance_month" style="width:auto; display:inline-block;" onchange="renderMonthView()">
                                        <?php for ($mi = 1; $mi <= 12; $mi++) { ?>
                                            <option value="<?php echo $mi; ?>" <?php echo ($mi == date('n')) ? 'selected' : ''; ?>><?php echo date('F', mktime(0,0,0,$mi,1)); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Legend -->
                            <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:12px; font-size:12px; color:#64748b; align-items:center;">
                                <span><span class="att-legend-dot" style="background:#10b981;"></span>P Present</span>
                                <span><span class="att-legend-dot" style="background:#ef4444;"></span>A Absent</span>
                                <span><span class="att-legend-dot" style="background:#f59e0b;"></span>FHL First Half Late</span>
                                <span><span class="att-legend-dot" style="background:#ea580c;"></span>SHL Second Half Late</span>
                                <span><span class="att-legend-dot" style="background:#06b6d4;"></span>FHP First Half Permission</span>
                                <span><span class="att-legend-dot" style="background:#3b82f6;"></span>SHP Second Half Permission</span>
                                <span><span class="att-legend-dot" style="background:#fca5a5;"></span>FHA/SHA Half Absent</span>
                                <span><span class="att-legend-dot" style="background:#8b5cf6;"></span>HD Half Day</span>
                                <span><span class="att-legend-dot" style="background:#06b6d4;"></span>H Holiday</span>
                                <span><span class="att-legend-dot" style="background:#64748b;"></span>W Weekend</span>
                                <span><span class="att-legend-dot" style="background:#f59e0b;"></span>C Compensation</span>
                            </div>

                            <?php
                            // Embed attendance data as JS for client-side month view rendering
                            $att_js_data = [];
                            foreach ($resultlist as $d => $r) {
                                if (!empty($r['key'])) {
                                    $att_js_data[$d] = [
                                        'key'      => $r['key'],
                                        'in_time'  => isset($r['in_time'])  ? $r['in_time']  : '',
                                        'out_time' => isset($r['out_time']) ? $r['out_time'] : '',
                                    ];
                                }
                            }
                            ?>
                            <script>
                                window.staffAttData = <?php echo json_encode($att_js_data); ?>;
                                window.staffHolidayDates = <?php echo json_encode($holiday_dates_year); ?>;
                                window.staffWeekendDates = <?php echo json_encode($weekend_day_dates_year); ?>;
                                window.staffCompDates    = <?php echo json_encode($compensation_dates_year); ?>;
                                window.staffId           = <?php echo (int)$staff['id']; ?>;
                            </script>

                            <!-- Override switchAttView for pill toggle design -->
                            <script>
                                window.switchAttView = function(view) {
                                    if (view === 'year') {
                                        $('#btn_year_view').css({background:'#4f46e5', color:'#fff'});
                                        $('#btn_month_view').css({background:'#fff', color:'#475569'});
                                        $('#att_month_selector').hide();
                                        $('#ajaxattendance').show();
                                        $('#monthattendance').hide();
                                    } else {
                                        $('#btn_month_view').css({background:'#4f46e5', color:'#fff'});
                                        $('#btn_year_view').css({background:'#fff', color:'#475569'});
                                        $('#att_month_selector').show();
                                        $('#ajaxattendance').hide();
                                        $('#monthattendance').show();
                                        renderMonthView();
                                    }
                                };
                            </script>

                            <div style="position: relative;" class="row">
                                <div class="modal_inner_loader displaynone"></div>
                                <div id="ajaxattendance" class="table-responsive mt10">
                                    <div class="download_label"><?php echo $this->lang->line('details_for'); ?> <?php echo $staff["name"] . " " . $staff["surname"]; ?></div>
                                    <table class="table table-striped table-bordered table-hover" id="attendancetable">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('date_month'); ?></th>
                                                <?php foreach ($monthlist as $monthkey => $monthvalue) { ?>
                                                    <th><?php echo $monthvalue; ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $j = 0;
                                            for ($i = 1; $i <= 31; $i++) {
                                            ?>
                                                <tr>
                                                    <td><?php echo sprintf("%02d", $i) ?></td>
                                                    <?php
                                                    foreach ($monthlist as $key => $value) {
                                                        $datemonth = date("m", strtotime($key));
                                                        $att_dates = date("Y") . "-" . $datemonth . "-" . sprintf("%02d", $i);
                                                        $display_key = '';
                                                        $display_class = '';
                                                        if (!empty($compensation_dates_year) && in_array($att_dates, $compensation_dates_year, true)) {
                                                            $display_class = 'att-cell-compensation';
                                                        }
                                                        if (!empty($holiday_dates_year) && in_array($att_dates, $holiday_dates_year, true)) {
                                                            $display_key = 'H';
                                                            $display_class = 'att-cell-holiday';
                                                        } elseif (!empty($weekend_day_dates_year) && in_array($att_dates, $weekend_day_dates_year, true)) {
                                                            $display_key = 'W';
                                                            $display_class = 'att-cell-weekend';
                                                        } elseif (array_key_exists($att_dates, $resultlist) && !empty($resultlist[$att_dates]["key"]) && !in_array($att_dates, $holiday_dates_year, true)) {
                                                            $display_key = $resultlist[$att_dates]["key"];
                                                            $display_class = match($display_key) {
                                                                'P'   => 'att-cell-present',
                                                                'FHL' => 'att-cell-fhl',
                                                                'SHL' => 'att-cell-shl',
                                                                'FHP' => 'att-cell-fhp',
                                                                'SHP' => 'att-cell-shp',
                                                                'FHA' => 'att-cell-fha',
                                                                'SHA' => 'att-cell-sha',
                                                                'HD'  => 'att-cell-halfday',
                                                                'A'   => 'att-cell-absent',
                                                                default => '',
                                                            };
                                                        }
                                                        $tooltip_title = '';
                                                        if (!empty($display_key) && !in_array($display_key, ['H', 'W']) && isset($resultlist[$att_dates])) {
                                                            $in_t  = !empty($resultlist[$att_dates]['in_time'])  ? $resultlist[$att_dates]['in_time']  : '-';
                                                            $out_t = !empty($resultlist[$att_dates]['out_time']) ? $resultlist[$att_dates]['out_time'] : '-';
                                                            $tooltip_title = 'In: ' . $in_t . ' | Out: ' . $out_t;
                                                        }
                                                    ?>
                                                        <td>
                                                            <span <?php if ($tooltip_title): ?>data-toggle="tooltip" data-placement="top" title="<?php echo htmlspecialchars($tooltip_title, ENT_QUOTES); ?>"<?php endif; ?>><span class="att-cell <?php echo $display_class; ?>"><?php echo $display_key; ?></span></span>
                                                        </td>
                                                    <?php
                                                    } ?>
                                                </tr>
                                            <?php
                                                $j++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div id="monthattendance" style="display:none;" class="mt10">
                                    <table class="table table-bordered table-hover table-condensed" id="monthattendancetable">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Status</th>
                                                <th><i class="fa fa-sign-in"></i> In Time</th>
                                                <th><i class="fa fa-sign-out"></i> Out Time</th>
                                            </tr>
                                        </thead>
                                        <tbody id="monthattendancebody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php if ($staff['user_type'] == 2) {
                        ?>
                            <div class="tab-pane" id="reviews">
                                <div class="row">
                                </div>
                                <div class="timeline-header no-border">
                                    <div class="table-responsive" style="clear: both;">
                                        <div class="download_label"><?php echo $this->lang->line('details_for'); ?> <?php echo $staff["name"] . " " . $staff["surname"]; ?></div>
                                        <table class="table table-striped table-bordered table-hover example">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('name'); ?></th>
                                                    <th><?php echo $this->lang->line('role'); ?></th>
                                                    <th><?php echo $this->lang->line('rate'); ?></th>
                                                    <th><?php echo $this->lang->line('comment'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($user_reviewlist as $value) { ?>
                                                    <tr>
                                                        <td><?php
                                                            if ($value['role'] == 'student') {
                                                                echo $value['firstname'] . " " . $value['lastname'];
                                                            } else {
                                                                echo $value['guardian_name'];
                                                            }
                                                            ?></td>
                                                        <td><?php echo $value['role']; ?></td>
                                                        <td><?php
                                                            $j = 5;
                                                            for ($i = 1; $i <= $j; $i++) {
                                                            ?>
                                                                <span class="fa fa-star" <?php if ($i <= $value['rate']) { ?> style="color:orange" <?php } ?>></span>
                                                            <?php }
                                                            ?>
                                                        </td>
                                                        <td><?php echo $value['comment']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="tab-pane" id="leaves">
                            <?php
                            $format_leave_count = function ($value) {
                                $formatted = number_format((float) $value, 2, '.', '');
                                $formatted = rtrim(rtrim($formatted, '0'), '.');
                                return $formatted === '' ? '0' : $formatted;
                            };
                            $leave_transactions = isset($leave_transactions) && is_array($leave_transactions) ? $leave_transactions : [];
                            $leave_transactions_by_type = [];
                            foreach ($leave_transactions as $txn) {
                                $txn_leave_type_id = isset($txn['leave_type_id']) ? (int) $txn['leave_type_id'] : 0;
                                if (!isset($leave_transactions_by_type[$txn_leave_type_id])) {
                                    $leave_transactions_by_type[$txn_leave_type_id] = [];
                                }
                                $leave_transactions_by_type[$txn_leave_type_id][] = $txn;
                            }
                            ?>
                            <div class="row row-flex">
                                <?php foreach ($leavedetails as $ldkey => $ldvalue) {
                                    $leave_type_id = isset($ldvalue['id']) ? (int) $ldvalue['id'] : 0;
                                    $type_transactions = isset($leave_transactions_by_type[$leave_type_id]) ? $leave_transactions_by_type[$leave_type_id] : [];
                                    $request_style_history = !empty($type_transactions);
                                    foreach ($type_transactions as $type_txn) {
                                        $history_action = strtoupper(trim((string) ($type_txn['action_type'] ?? '')));
                                        if (!in_array($history_action, ['CREDIT_APPLIED', 'PAYROLL_DEBIT'], true)) {
                                            $request_style_history = false;
                                            break;
                                        }
                                    }
                                ?>
                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                            <div class="staffprofile" style="background: linear-gradient(135deg, #f8fbff, #eef6ff); border: 1px solid #d9e7ff; color: #2c3e50; min-height: 240px;">
                                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                                                    <div>
                                                        <h5 style="color:#1f3f75;font-weight:700;margin:0;"><?php echo htmlspecialchars((string) ($ldvalue["type"] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h5>
                                                        <p style="margin:6px 0 0 0;color:#1e8449;font-size:14px;"><strong>Available:</strong> <?php echo $format_leave_count(isset($ldvalue["available"]) ? $ldvalue["available"] : 0); ?></p>
                                                    </div>
                                                    <div style="display:flex;gap:6px;align-items:center;">
                                                        <span class="label label-primary" style="font-size:11px;padding:4px 8px;">Employee History</span>
                                                        <?php $tmp_breakdown = isset($leave_monthly_breakdown[$leave_type_id]) ? $leave_monthly_breakdown[$leave_type_id] : []; $earliest_month_row = !empty($tmp_breakdown) ? end($tmp_breakdown) : null; ?>
                                                        <?php if (!empty($earliest_month_row)): ?>
                                                        <button type="button" class="btn btn-xs btn-default recascade-balance-btn"
                                                            data-staff="<?php echo (int) $staff['id']; ?>"
                                                            data-type="<?php echo $leave_type_id; ?>"
                                                            data-year="<?php echo (int) ($earliest_month_row['year'] ?? 0); ?>"
                                                            data-month="<?php echo (int) ($earliest_month_row['month'] ?? 0); ?>"
                                                            title="Recalculate carry-forward opening balances from the earliest recorded month"
                                                            style="font-size:10px;padding:2px 6px;">
                                                            &#8635; Recalculate
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div style="margin-top:8px;font-size:12px;line-height:1.6;">
                                                    <div><strong>Opening:</strong> <?php echo $format_leave_count(isset($ldvalue['opening']) ? $ldvalue['opening'] : 0); ?></div>
                                                    <div><strong>Consumed:</strong> <?php echo $format_leave_count(isset($ldvalue['consumed']) ? $ldvalue['consumed'] : 0); ?></div>
                                                </div>

                                                <div style="margin-top:10px;border-top:1px solid #d9e7ff;padding-top:8px;">
                                                    <div style="font-weight:700;color:#1f3f75;margin-bottom:6px;font-size:12px;">Month-wise Summary</div>
                                                    <div style="max-height:220px;overflow:auto;">
                                                        <?php
                                                        $monthly_rows = isset($leave_monthly_breakdown[$leave_type_id])
                                                            ? $leave_monthly_breakdown[$leave_type_id] : [];
                                                        $month_names = ['','Jan','Feb','Mar','Apr','May','Jun',
                                                                        'Jul','Aug','Sep','Oct','Nov','Dec'];
                                                        ?>
                                                        <table class="table table-condensed" style="margin-bottom:0;background:#fff;font-size:11px;">
                                                            <thead style="background:#eef6ff;">
                                                                <tr>
                                                                    <th style="font-size:11px;white-space:nowrap;">Month</th>
                                                                    <th class="text-right" style="font-size:11px;" title="Balance carried from previous month">Opening</th>
                                                                    <th class="text-right" style="font-size:11px;color:#1e8449;" title="Leave credited this month">Credit</th>
                                                                    <th class="text-right" style="font-size:11px;color:#c0392b;" title="Days used for LOP adjustment">LOP Adj</th>
                                                                    <th class="text-right" style="font-size:11px;color:#c0392b;" title="Days used for approved leave">Leave Used</th>
                                                                    <th class="text-right" style="font-size:11px;font-weight:700;" title="Closing balance = Opening + Credit − LOP − Leave">Balance</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($monthly_rows)): ?>
                                                                    <?php foreach ($monthly_rows as $mr):
                                                                        $mr_month  = (int) ($mr['month'] ?? 0);
                                                                        $mr_year   = (int) ($mr['year']  ?? 0);
                                                                        $mr_open   = (float) ($mr['opening_balance'] ?? 0);
                                                                        $mr_admin  = (float) ($mr['admin_adjustment'] ?? 0);
                                                                        $mr_earned = (float) ($mr['earned_in_month'] ?? 0);
                                                                        $mr_lop    = (float) ($mr['used_for_lop_adjustment'] ?? 0);
                                                                        $mr_leave  = (float) ($mr['used_for_leave_application'] ?? 0);
                                                                        $mr_close  = (float) ($mr['closing_balance'] ?? 0);
                                                                        // Total credit = earned + positive admin adjustment
                                                                        $mr_credit = $mr_earned + max(0, $mr_admin);
                                                                        $mr_debit_admin = min(0, $mr_admin); // negative admin adj
                                                                        $month_label = ($mr_month >= 1 && $mr_month <= 12)
                                                                            ? $month_names[$mr_month] . ' ' . $mr_year : '-';
                                                                    ?>
                                                                    <tr>
                                                                        <td style="white-space:nowrap;font-weight:600;"><?php echo $month_label; ?></td>
                                                                        <td class="text-right"><?php echo $format_leave_count($mr_open); ?></td>
                                                                        <td class="text-right" style="color:#1e8449;font-weight:<?php echo $mr_credit > 0 ? '700' : 'normal'; ?>;">
                                                                            <?php echo $mr_credit > 0 ? '+' . $format_leave_count($mr_credit) : '—'; ?>
                                                                        </td>
                                                                        <td class="text-right" style="color:#c0392b;font-weight:<?php echo $mr_lop > 0 ? '700' : 'normal'; ?>;">
                                                                            <?php echo $mr_lop > 0 ? '−' . $format_leave_count($mr_lop) : '—'; ?>
                                                                        </td>
                                                                        <td class="text-right" style="color:#c0392b;font-weight:<?php echo $mr_leave > 0 ? '700' : 'normal'; ?>;">
                                                                            <?php echo $mr_leave > 0 ? '−' . $format_leave_count($mr_leave) : '—'; ?>
                                                                        </td>
                                                                        <td class="text-right" style="font-weight:700;color:<?php echo $mr_close > 0 ? '#1e8449' : ($mr_close < 0 ? '#c0392b' : '#666'); ?>;">
                                                                            <?php echo $format_leave_count($mr_close); ?>
                                                                        </td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center text-muted" style="font-size:11px;">No history for this leave type.</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--./col-md-3-->
                                <?php }
                                ?>
                            </div>
                            <div class="timeline-header no-border">
                                <div class="download_label"><?php echo $this->lang->line('details_for'); ?> <?php echo $staff["name"] . " " . $staff["surname"] . $staff['employee_id']; ?></div>
                                <div class="table-responsive overflow-visible">
                                    <table class="table table-striped table-bordered table-hover example">
                                        <thead>
                                            <th><?php echo $this->lang->line('leave_type'); ?></th>
                                            <th><?php echo $this->lang->line('leave_date'); ?></th>
                                            <th><?php echo $this->lang->line('days'); ?></th>
                                            <th><?php echo $this->lang->line('apply_date'); ?></th>
                                            <th><?php echo $this->lang->line("status") ?></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line("action") ?></th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($staff_leaves as $key => $value) {

                                                if ($value["status"] == "approved" || $value["status"] == "approve") {
                                                    $status1 = "approve";
                                                    $label = "class='label label-success'";
                                                } else if ($value["status"] == "pending") {
                                                    $status1 = "pending";
                                                    $label = "class='label label-warning'";
                                                } else if ($value["status"] == "disapproved" || $value["status"] == "disapprove") {
                                                    $status1 = "disapprove";
                                                    $label = "class='label label-danger'";
                                                }
                                            ?>
                                                <tr>
                                                    <td><?php echo $value["type"]; ?></td>
                                                    <td class="white-space-nowrap"><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['leave_from'])) . " - " . date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['leave_to'])); ?></td>
                                                    <td><?php echo $value["leave_days"]; ?></td>
                                                    <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['date'])); ?></td>
                                                    <td><small style="text-transform: capitalize;" <?php echo $label ?>><?php echo $status[$status1]; ?></small></td>
                                                    <td class="text-right white-space-nowrap"><a href="#leavedetails" onclick="getRecord('<?php echo $value["id"] ?>')" role="button" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>"><i class="fa fa-eye"></i></a>
                                                        <?php if (!empty($value['document_file'])) { ?>
                                                            <a href="<?php echo base_url(); ?>admin/leaverequest/downloadleaverequestdoc/<?php echo $value['staff_id'] . "/" . $value['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>

<div id="leavedetails" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-dialog modal-dialog2 modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('details'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <form role="form" id="leavedetails_form" action="">
                            <div class="col-md-12 table-responsive">
                                <table class="table mb0 table-striped table-bordered examples">
                                    <tr>
                                        <th width="15%"><?php echo $this->lang->line('name'); ?></th>
                                        <td width="35%"><span id='name'></span></td>
                                        <th width="15%"><?php echo $this->lang->line('staff_id'); ?></th>
                                        <td width="35%"><span id="employee_id"></span>
                                            <span class="text-danger"><?php echo form_error('leave_request_id'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo $this->lang->line('leave'); ?></th>
                                        <td><span id='leave_from'></span> - <label for="exampleInputEmail1"> </label><span id='leave_to'> </span> (<span id='days'></span>)
                                            <span class="text-danger"><?php echo form_error('leave_from'); ?></span>
                                        </td>
                                        <th><?php echo $this->lang->line('leave_type'); ?></th>
                                        <td><span id="leave_type"></span>
                                            <input id="leave_request_id" name="leave_request_id" placeholder="" type="hidden" class="form-control" />
                                            <span class="text-danger"><?php echo form_error('leave_request_id'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <td>
                                            <span id="status"></span>
                                        </td>
                                        <th><?php echo $this->lang->line('apply_date'); ?></th>
                                        <td><span id="applied_date"></span></td>
                                    </tr>
                                    <tr>
                                        <th><?php echo $this->lang->line('reason'); ?></th>
                                        <td><span id="reason"> </span></td>
                                        <th><?php echo $this->lang->line('note'); ?></th>
                                        <td>
                                            <span id="remark"> </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myTimelineModal" role="dialog">
    <div class="modal-dialog modal-sm400">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title title"><?php echo $this->lang->line('add_timeline'); ?> </h4>
            </div>
            <form id="timelineform" name="timelineform" method="post" action="<?php echo base_url() . "admin/timeline/add_staff_timeline" ?>" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div id='timeline_hide_show'>
                        <input type="hidden" name="staff_id" value="<?php echo $staff["id"] ?>" id="staff_id">
                        <h4></h4>
                        <div class="">
                            <div class="form-group">
                                <label for=""><?php echo $this->lang->line('title'); ?></label><small class="req"> *</small>
                                <input id="timeline_title" name="timeline_title" placeholder="" type="text" class="form-control" />
                                <span class="text-danger"><?php echo form_error('timeline_title'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for=""><?php echo $this->lang->line('date'); ?></label><small class="req"> *</small>
                                <input id="timeline_date" name="timeline_date" value="<?php echo set_value('timeline_date', date($this->customlib->getSchoolDateFormat())); ?>" placeholder="" type="text" class="form-control date" />
                                <span class="text-danger"><?php echo form_error('timeline_date'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for=""><?php echo $this->lang->line('description'); ?></label>
                                <textarea id="timeline_desc" name="timeline_desc" placeholder="" class="form-control"></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for=""><?php echo $this->lang->line('attach_document'); ?></label>
                                <div class=""><input id="timeline_doc_id" name="timeline_doc" placeholder="" type="file" class="filestyle form-control" data-height="40" value="<?php echo set_value('timeline_doc'); ?>" />
                                    <span class="text-danger"><?php echo form_error('timeline_doc'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="" class="col-align--top"><?php echo $this->lang->line('visible_to_this_person'); ?></label>
                                <input id="visible_check" checked="checked" name="visible_check" value="yes" placeholder="" type="checkbox" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="clear:both">
                    <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>

                    <button type="reset" id="reset" style="display: none" class="btn btn-info pull-right"><?php echo $this->lang->line('reset'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="scheduleModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title_logindetail"></h4>
            </div>
            <div class="modal-body_logindetail">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="payslipview" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('details'); ?> <span id="print" class=></span></h4>
            </div>
            <div class="modal-body" id="testdata">

            </div>
        </div>
    </div>
</div>

<div id="changepwdmodal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('change_password'); ?></h4>
            </div>
            <form method="post" id="changepassbtn" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email"><?php echo $this->lang->line('new_password'); ?> <small class="req"> *</small></label>
                        <input type="password" class="form-control" name="new_pass" id="pass">
                    </div>
                    <div class="form-group">
                        <label for="pwd"><?php echo $this->lang->line('confirm_password'); ?> <small class="req"> *</small></label>
                        <input type="password" class="form-control" name="confirm_pass" id="pwd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('saving'); ?>"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="disablemodal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('disable_staff'); ?></h4>
            </div>
            <form method="post" id="disablebtn" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email"><?php echo $this->lang->line('date'); ?> <small class="req"> *</small></label>
                        <input type="text" class="form-control date" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" name="date" readonly="readonly">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edittimelineModal" role="dialog">
    <div class="modal-dialog modal-sm400">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('edit_timeline'); ?></h4>
            </div>
            <form id="edittimelineform" name="timelineform" method="post" action="<?php echo base_url() . "admin/timeline/add_staff_timeline" ?>" enctype="multipart/form-data">
                <div class="modal-body pb0">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div id="edittimelinedata"></div>
                </div>
                <div class="modal-footer" style="clear:both">
                    <button type="submit" class="btn btn-info pull-right" id="submit" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>
                    <button type="reset" id="reset" style="display: none" class="btn btn-info pull-right"><?php echo $this->lang->line('reset'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function disable_staff(id) {
        $('#disablemodal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true

        });
    }

    $(".myTransportFeeBtn").click(function() {
        $("span[id$='_error']").html("");
        $('#transport_amount').val("");
        $('#transport_amount_discount').val("0");
        $('#transport_amount_fine').val("0");
        var student_session_id = $(this).data("student-session-id");
        $('.transport_fees_title').html("<b><?php echo $this->lang->line('upload_documents'); ?></b>");
        $('#transport_student_session_id').val(student_session_id);
        $('#myTransportFeesModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true

        });
    });
</script>

<script type="text/javascript">
    $("#myTimelineButton").click(function() {
        $("#reset").click();
        $('.transport_fees_title').html("<b><?php echo $this->lang->line('add_timeline'); ?></b>");
        $(".dropify-clear").click();
        $('#myTimelineModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true

        });
    });

    $("#timelineform").on('submit', (function(e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]:focus");
        $.ajax({
            url: "<?php echo site_url("admin/timeline/add_staff_timeline") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $this.button('loading');

            },
            success: function(res) {
                if (res.status == "fail") {
                    var message = "";
                    $.each(res.error, function(index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(res.message);
                    window.location.reload(true);
                }
            },
            error: function(xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function() {
                $this.button('reset');
            }
        });
    }));

    $(document).ready(function(e) {
        $("#disablebtn").on('submit', (function(e) {
            var staff_id = $("#staff_id").val();
            e.preventDefault();
            $.ajax({
                url: "<?php echo site_url('admin/staff/disablestaff/') ?>" + staff_id,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {

                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function(index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        window.location.reload(true);
                    }

                },
                error: function(e) {
                    alert("<?php echo $this->lang->line('fail'); ?>");
                    console.log(e);
                }
            });
        }));
    });

    $(document).ready(function(e) {
        $("form#changepassbtn").on('submit', (function(e) {

            var staff_id = $("#staff_id").val();
            var form = $(this);
            var $this = form.find("button[type=submit]:focus");
            e.preventDefault();

            $.ajax({
                url: "<?php echo site_url('admin/staff/change_password/') ?>" + staff_id,
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function() {
                    $this.button('loading');
                },
                success: function(res) {
                    if (res.status == "fail") {
                        var message = "";
                        $.each(res.error, function(index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(res.message);
                        window.location.reload(true);
                    }
                    $this.button('loading');
                },
                error: function(xhr) { // if error occured
                    alert("Error occured.please try again");
                    $this.button('reset');
                },
                complete: function() {
                    $this.button('reset');
                }



            });
        }));
    });

    function delete_timeline(id) {
        var staff_id = $("#staff_id").val();
        if (confirm('<?php echo $this->lang->line("delete_confirm") ?>')) {

            $.ajax({
                url: '<?php echo base_url(); ?>admin/timeline/delete_staff_timeline/' + id,
                success: function(res) {
                    $.ajax({
                        url: '<?php echo base_url(); ?>admin/timeline/staff_timeline/' + staff_id,
                        success: function(res) {
                            $('#timeline_list').html(res);
                            successMsg('<?php echo $this->lang->line('delete_message'); ?>');
                        },
                        error: function() {
                            alert("<?php echo $this->lang->line('fail'); ?>");
                        }
                    });
                },
                error: function() {
                    alert("<?php echo $this->lang->line('fail'); ?>");
                }
            });
        }
    }

    $(document).ready(function() {
        $(document).on('click', '.change_password', function() {
            $('#changepwdmodal').modal('show');
        });

        $("#attendancetable").DataTable({
            searching: false,
            ordering: false,
            paging: false,
            bSort: false,
            info: false,
            dom: "Bfrtip",
            buttons: [

                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',

                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    titleAttr: 'PDF',
                    title: $('.download_label').html(),
                    exportOptions: {
                        columns: ':visible'

                    }
                },

                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $('.download_label').html(),
                    customize: function(win) {
                        $(win.document.body)
                            .css('font-size', '10pt');

                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    },
                    exportOptions: {
                        columns: ':visible'
                    }
                },

                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    titleAttr: 'Columns',
                    title: $('.download_label').html(),
                    postfixButtons: ['colvisRestore']
                },
            ]
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Tooltips for in/out punch times on year view cells
        $('[data-toggle="tooltip"]').tooltip({container: 'body'});
    });

    function getRecord(id) {
        $('input:radio[name=status]').attr('checked', false);
        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/leaverequest/leaveRecord',
            type: 'POST',
            data: {
                id: id
            },
            dataType: "json",
            success: function(result) {

                $('inputs[name="leave_request_id"]').val(result.id);
                $('#name').html(result.name + ' ' + result.surname);
                $('#leave_from').html(result.leavefrom);
                $('#leave_to').html(result.leaveto);
                $('#leave_type').html(result.type);
                $('#reason').html(result.employee_remark);
                $('#applied_date').html(result.date);
                $('#days').html(result.leave_days + ' Days');
                $("#remark").html(result.admin_remark);
                $("#employee_id").html(' ' + result.employee_id);
                $("#status").html(' ' + result.leave_status);

            }
        });

        $('#leavedetails').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    };

    /* ---- Attendance view toggle ---- */
    function switchAttView(view) {
        if (view === 'year') {
            $('#btn_year_view').addClass('active btn-primary').removeClass('btn-default');
            $('#btn_month_view').removeClass('active btn-primary').addClass('btn-default');
            $('#att_month_selector').hide();
            $('#ajaxattendance').show();
            $('#monthattendance').hide();
        } else {
            $('#btn_month_view').addClass('active btn-primary').removeClass('btn-default');
            $('#btn_year_view').removeClass('active btn-primary').addClass('btn-default');
            $('#att_month_selector').show();
            $('#ajaxattendance').hide();
            $('#monthattendance').show();
            renderMonthView();
        }
    }

    function renderMonthView() {
        var year  = parseInt($('#attendance_year').val()) || new Date().getFullYear();
        var month = parseInt($('#attendance_month').val()) || (new Date().getMonth() + 1);
        var days  = new Date(year, month, 0).getDate();
        var dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        var now = new Date();
        var isCurrentMonth = (year === now.getFullYear() && month === (now.getMonth() + 1));
        var cutoffDay = isCurrentMonth ? now.getDate() : days;

        // Debug: log data availability
        console.log('[MonthView] year='+year+' month='+month+' days='+days);
        console.log('[MonthView] staffAttData=', window.staffAttData);
        console.log('[MonthView] holidayDates=', window.staffHolidayDates);
        console.log('[MonthView] weekendDates=', window.staffWeekendDates);

        // Map key codes to full descriptions
        var statusMap = {
            'P'   : ['Present',  'label-success'],
            'A'   : ['Absent',   'label-danger'],
            'HD'  : ['Half Day', 'label-warning'],
            'L'   : ['Leave',    'label-default'],
            'H'   : ['Holiday',  'label-info'],
            'W'   : ['Weekend',  'label-default'],
        };

        // Present-with-qualifier keys: rendered as P (description)
        var presentVariants = {
            'FHL' : 'First Half Late',
            'SHL' : 'Second Half Late',
            'FHP' : 'First Half Permission',
            'SHP' : 'Second Half Permission',
            'SHA' : 'Second Half Absent',
            'FHA' : 'First Half Absent',
        };

        var summary = {
            present: 0,
            absent: 0,
            late: 0,
            permission: 0,
            half_day: 0,
            holidays: 0,
            working_days: 0,
            weekends: 0
        };

        function isPresentLike(code) {
            return code === 'P' || code === 'FHL' || code === 'SHL' || code === 'FHP' || code === 'SHP';
        }

        function isHalfDayLike(code) {
            return code === 'HD' || code === 'FHA' || code === 'SHA';
        }

        function formatSummaryCount(value) {
            return (value % 1 === 0) ? String(parseInt(value, 10)) : value.toFixed(1);
        }

        var rows = '';
        for (var d = 1; d <= days; d++) {
            var dateStr = year + '-' + String(month).padStart(2,'0') + '-' + String(d).padStart(2,'0');
            var dayName = dayNames[new Date(dateStr).getDay()];
            var keyCode = '', labelClass = 'label-default', fullLabel = '', qualifier = '', inTime = '-', outTime = '-';
            var isHolidayDate = (window.staffHolidayDates && window.staffHolidayDates.indexOf(dateStr) !== -1);
            var isWeekendDate = (window.staffWeekendDates && window.staffWeekendDates.indexOf(dateStr) !== -1);

            if (isHolidayDate) {
                keyCode = 'H'; fullLabel = 'Holiday'; labelClass = 'label-info';
            } else if (isWeekendDate) {
                keyCode = 'W'; fullLabel = 'Weekend'; labelClass = 'label-default';
            } else if (window.staffAttData && window.staffAttData[dateStr]) {
                var rec = window.staffAttData[dateStr];
                keyCode = rec.key;
                if (presentVariants[keyCode]) {
                    // Render as P (qualifier)
                    fullLabel  = 'Present';
                    labelClass = 'label-success';
                    qualifier  = presentVariants[keyCode];
                } else if (statusMap[keyCode]) {
                    fullLabel  = statusMap[keyCode][0];
                    labelClass = statusMap[keyCode][1];
                } else {
                    fullLabel  = keyCode;
                    labelClass = 'label-default';
                }
                if (rec.in_time)  inTime  = rec.in_time;
                if (rec.out_time) outTime = rec.out_time;
            }

            if (d <= cutoffDay) {
                var isCountableHoliday = isHolidayDate && !isWeekendDate;
                if (isCountableHoliday) {
                    summary.holidays++;
                } else if (isWeekendDate) {
                    summary.weekends++;
                } else {
                    summary.working_days++;
                    if (isHalfDayLike(keyCode)) {
                        summary.half_day++;
                    } else if (isPresentLike(keyCode)) {
                        summary.present++;
                    } else if (keyCode === 'A') {
                        summary.absent++;
                    }

                    if (keyCode === 'FHL' || keyCode === 'SHL') {
                        summary.late++;
                    }
                    if (keyCode === 'FHP' || keyCode === 'SHP') {
                        summary.permission++;
                    }
                }
            }

            var statusCell = '';
            if (keyCode) {
                // For present-variants: show "P – First Half Late"
                // For others: show "A – Absent", "HD – Half Day", etc.
                var badgeText  = qualifier ? 'P' : keyCode;
                var detailText = qualifier ? qualifier : fullLabel;
                statusCell = '<span class="label ' + labelClass + '" style="font-size:11px;letter-spacing:0.5px;padding:3px 6px;">'
                           + badgeText + '</span>';
                if (detailText && detailText !== badgeText) {
                    statusCell += '<span style="font-size:12px;color:#444;"> &ndash; ' + detailText + '</span>';
                }
            } else {
                statusCell = '<span style="color:#bbb;font-size:11px;">–</span>';
            }

            var isToday = (dateStr === new Date().toISOString().slice(0,10));
            rows += '<tr' + (isToday ? ' class="info"' : '') + '>'
                  + '<td>' + dateStr + '</td>'
                  + '<td>' + dayName + '</td>'
                  + '<td style="white-space:nowrap;">' + statusCell + '</td>'
                  + '<td>' + inTime + '</td>'
                  + '<td>' + outTime + '</td>'
                  + '</tr>';
        }
        $('#monthattendancebody').html(rows);

        var totalPresent = summary.present + (summary.half_day * 0.5);
        var totalAbsent  = summary.absent + (summary.half_day * 0.5);

        $('.total_present').text(formatSummaryCount(totalPresent));
        $('.total_absent').text(formatSummaryCount(totalAbsent));
        $('.total_late').text(formatSummaryCount(summary.late));
        $('.total_permission').text(formatSummaryCount(summary.permission));
        $('.total_half_day').text(formatSummaryCount(summary.half_day));
        $('.total_holiday').text(formatSummaryCount(summary.holidays));
        $('.total_working_days').text(formatSummaryCount(summary.working_days));
        $('.total_weekends').text(formatSummaryCount(summary.weekends));
    }

    function ajax_attendance(id, year) {

        $.ajax({
            url: baseurl + 'admin/staff/ajax_attendance',
            type: 'POST',
            data: {
                "id": id,
                "year": year
            },
            dataType: "JSON",
            beforeSend: function() {
                $('.modal_inner_loader').css({
                    'display': 'block'
                });
            },
            success: function(result) {
                $("#ajaxattendance").html(result.page);
                // Update JS data for month view
                if (result.att_data)    window.staffAttData      = result.att_data;
                if (result.holidays)    window.staffHolidayDates = result.holidays;
                if (result.weekends)    window.staffWeekendDates = result.weekends;
                if (result.comp_dates)  window.staffCompDates    = result.comp_dates;
                // Re-init tooltips on newly loaded content
                $('#ajaxattendance [data-toggle="tooltip"]').tooltip({container:'body'});
                // Keep summary cards synced with selected month/year
                renderMonthView();
                $('.modal_inner_loader').fadeOut("slow");
            },
            error: function(xhr) {
                alert("Error occured.please try again");
                $('.modal_inner_loader').fadeOut("slow");
            },
            complete: function() {
                $('.modal_inner_loader').fadeOut("slow");
            }
        });
    }

    function getPayslip(id) {
        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/payroll/payslipView',
            type: 'POST',
            data: {
                payslipid: id
            },

            success: function(result) {
                $("#print").html("<a href='#' class='pull-right modal-title moprintblack ' onclick='printData(" + id + ")'  title='<?php echo $this->lang->line('print'); ?>'><i class='fa fa-print'></i></a>");
                $("#testdata").html(result);

            }
        });

        $('#payslipview').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    };

    function printData(id) {
        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/payroll/payslipView',
            type: 'POST',
            data: {
                payslipid: id
            },
            success: function(result) {
                $("#testdata").html(result);
                popup(result);
            }
        });
    }

    function popup(data) {
        var base_url = '<?php echo base_url() ?>';
        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({
            "position": "absolute",
            "top": "-1000000px"
        });
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function() {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);

        return true;
    }
</script>

<script>
    $('.edit_timeline').click(function() {
        $('#edittimelineModal').modal('show');
        var id = $(this).attr('data-id');
        $.ajax({
            url: "<?php echo site_url("admin/timeline/getstaffsingletimeline") ?>",
            type: "POST",
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                console.log(response);
                $('#edittimelinedata').html(response.page);
            }
        });
    })

    $("#edittimelineform").on('submit', (function(e) {
        e.preventDefault();
        var $this = $(this).find("button[type=submit]:focus");
        $.ajax({
            url: "<?php echo site_url("admin/timeline/editstafftimeline") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $this.button('loading');
            },
            success: function(res) {
                if (res.status == "fail") {
                    var message = "";
                    $.each(res.error, function(index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(res.message);
                    window.location.reload(true);
                }
            },
            error: function(xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function() {
                $this.button('reset');
            }
        });
    }));

    function showAttendanceDetails(type, staff_id) {
        var year = $('#attendance_year').val() || new Date().getFullYear();
        var month = new Date().getMonth() + 1; // Current month (1-12)
        
        $('#attendance_detail_body').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#attendance_detail_modal').modal('show');

        var typeMap = {
            'late': 'TL',
            'permission': 'TP',
            'halfday': 'HD'
        };

        $.ajax({
            url: baseurl + 'attendencereports/staffAttendanceDetail',
            type: 'POST',
            dataType: 'json',
            data: {
                staff_id: staff_id,
                month: month,
                year: year,
                type: typeMap[type] || type
            },
            success: function (response) {
                if (!response || response.status !== 'success' || !response.rows || response.rows.length === 0) {
                    $('#attendance_detail_body').html('<tr><td colspan="4" class="text-center">No records found.</td></tr>');
                    return;
                }

                var rowsHtml = '';
                $.each(response.rows, function (i, row) {
                    rowsHtml += '<tr>'
                        + '<td>' + row.date + '</td>'
                        + '<td>' + row.session + '</td>'
                        + '<td>' + row.in_time + '</td>'
                        + '<td>' + row.out_time + '</td>'
                        + '</tr>';
                });

                $('#attendance_detail_body').html(rowsHtml);
            },
            error: function () {
                $('#attendance_detail_body').html('<tr><td colspan="4" class="text-center">Failed to load data.</td></tr>');
            }
        });
    }
</script>

<div id="attendance_detail_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Attendance Details</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Session</th>
                                <th>Punch In</th>
                                <th>Punch Out</th>
                            </tr>
                        </thead>
                        <tbody id="attendance_detail_body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).on('click', '.recascade-balance-btn', function () {
    var btn     = $(this);
    var staffId = btn.data('staff');
    var year    = btn.data('year');
    var month   = btn.data('month');
    if (!confirm('Recalculate carry-forward balances from ' + year + '-' + ('0' + month).slice(-2) + ' onwards?\nThis will fix incorrect opening balances in subsequent months.')) return;
    btn.prop('disabled', true).text('Recalculating…');
    $.post('<?php echo site_url("admin/staff/ajax_recascade_leave_balances"); ?>', {
        staff_id:   staffId,
        from_year:  year,
        from_month: month
    }, function (res) {
        if (res && res.status === 'success') {
            alert('Done. Reload the page to see updated balances.');
        } else {
            alert('Error: ' + (res && res.message ? res.message : 'Unknown error'));
        }
        btn.prop('disabled', false).html('&#8635; Recalculate');
    }, 'json').fail(function () {
        alert('Request failed.');
        btn.prop('disabled', false).html('&#8635; Recalculate');
    });
});

$(document).on('click', '#btn-generate-staff-codes', function() {
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    var staff_id = $(this).data('staff-id');
    $.getJSON('<?php echo site_url('admin/staff/generate_codes/'); ?>' + staff_id, function(res) {
        if (res.status === '1') {
            var ts = '?t=' + Date.now();
            $('#staff-barcode-img').attr('src', res.barcode_url + ts);
            $('#staff-barcode-img-link').attr('href', res.barcode_url + ts);
            $('#staff-qrcode-img').attr('src', res.qrcode_url + ts);
            $('#staff-qrcode-img-link').attr('href', res.qrcode_url + ts);
            $('#staff-generate-codes-row').hide();
            $('#staff-generated-barcode-row, #staff-generated-qrcode-row').show();
        } else {
            $btn.prop('disabled', false).html('<i class="fa fa-qrcode"></i> Generate');
            alert('Failed to generate codes. Please try again.');
        }
    });
});
</script>
