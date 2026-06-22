<link rel="stylesheet" href="<?php echo base_url('backend/plugins/select2/select2.min.css'); ?>">
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-users"></i> Staff Directory
            <?php if ($this->rbac->hasPrivilege('staff', 'can_add')) { ?>
            <small class="pull-right">
                <a href="<?php echo base_url(); ?>admin/staff/create" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_staff'); ?>
                </a>
            </small>
            <?php } ?>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <?php if ($this->session->flashdata('msg')) { ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                </div>
                <?php } ?>

                <!-- Search Criteria Card -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/staff') ?>" method="post" class="">
                            <?php echo $this->customlib->getCSRF(); ?>

                            <!-- Row 1: Filter Search -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line("role"); ?></label>
                                        <select id="role" name="role" class="form-control">
                                            <option value=""><?php echo $this->lang->line("select"); ?></option>
                                            <?php foreach ($role as $key => $role_value) { ?>
                                            <option <?php if ($role_id == $role_value["id"]) { echo "selected"; } ?> value="<?php echo $role_value['id'] ?>"><?php echo $role_value['type'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger" id="error_role"><?php echo form_error('role'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Staff Type / Category</label>
                                        <select id="category" name="category" class="form-control">
                                            <option value=""><?php echo $this->lang->line("select"); ?></option>
                                            <?php if (!empty($categories)): ?>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" style="border-left: 4px solid <?php echo $category['color']; ?>;">
                                                    <i class="fa <?php echo $category['icon']; ?>"></i> <?php echo $category['name']; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select id="department" name="department" class="form-control">
                                            <option value="">All Departments</option>
                                            <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>"
                                                <?php echo (isset($department_selected) && $department_selected == $dept['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept['department_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-block checkbox-toggle"><i class="fa fa-filter"></i> Filter Search</button>
                                    </div>
                                </div>
                            </div>

                            <hr style="margin: 5px 0 15px;">

                            <!-- Row 2: Keyword Search -->
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('search_by_keyword'); ?></label>
                                        <input type="text" name="search_text" id="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>" placeholder="<?php echo $this->lang->line('search_by_staff'); ?>">
                                        <span class="text-danger" id="error_search_text"></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" name="search" value="search_full" class="btn btn-default btn-block checkbox-toggle"><i class="fa fa-search"></i> Keyword Search</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div><!-- /.box-body -->
                </div><!-- /.box box-primary (search) -->

                <?php if (isset($resultlist)) { ?>
                <!-- Results Card -->
                <div class="box box-primary">
                    <div class="nav-tabs-custom" style="margin-bottom: 0; box-shadow: none; border: none;">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-th-large"></i> <?php echo $this->lang->line('card_view'); ?></a></li>
                            <li><a href="#tab_2" data-toggle="tab" aria-expanded="true"><i class="fa fa-list"></i> <?php echo $this->lang->line('list_view'); ?></a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="download_label"><?php echo $title; ?></div>

                            <!-- Card View Tab -->
                            <div class="tab-pane active" id="tab_1">
                                <?php if (empty($resultlist)) { ?>
                                <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('no_record_found'); ?></div>
                                <?php } else {
                                    $count = 1;
                                ?>
                                <div class="row" style="display:flex; flex-wrap:wrap; margin:0 -8px;">
                                    <?php foreach ($resultlist as $staff) {
                                        if (!empty($staff["image"])) {
                                            $image = $staff["image"];
                                        } else {
                                            if ($staff['gender'] == 'Male') {
                                                $image = "default_male.jpg";
                                            } else {
                                                $image = "default_female.jpg";
                                            }
                                        }
                                        $photo_url = $this->media_storage->getImageURL("uploads/staff_images/" . $image);

                                        // Permission checks
                                        $userdata = $this->customlib->getUserData();
                                        $can_view = ($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view')) || ($userdata["id"] == $staff["id"]);
                                        $can_edit = false;
                                        $sessionData = $this->session->userdata('admin');
                                        if (($staff["user_type"] == "Super Admin") && $userdata["id"] == $staff["id"]) {
                                            $can_edit = true;
                                        } elseif (($this->rbac->hasPrivilege('staff', 'can_edit')) && ($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view'))) {
                                            $can_edit = true;
                                        }
                                    ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6" style="padding:8px; display:flex;">
                                        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; width:100%; min-height:320px; display:flex; flex-direction:column;">
                                            <!-- Avatar & Name -->
                                            <div style="padding:20px 20px 12px; text-align:center;">
                                                <div style="width:72px; height:72px; border-radius:50%; overflow:hidden; margin:0 auto 12px; border:3px solid #e2e8f0;">
                                                    <img src="<?php echo $photo_url; ?>" alt="<?php echo $staff['name'] . ' ' . $staff['surname']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                                </div>
                                                <?php if ($can_view) { ?>
                                                <a href="<?php echo base_url(); ?>admin/staff/profile/<?php echo $staff['id']; ?>" style="color:#1e293b; text-decoration:none;">
                                                    <strong style="font-size:14px;"><?php echo $staff["name"] . " " . $staff["surname"]; ?></strong>
                                                </a>
                                                <?php } else { ?>
                                                <strong style="font-size:14px; color:#1e293b;"><?php echo $staff["name"] . " " . $staff["surname"]; ?></strong>
                                                <?php } ?>
                                                <div style="color:#94a3b8; font-size:12px; margin-top:2px;"><?php echo $staff["employee_id"]; ?></div>
                                            </div>

                                            <!-- Details -->
                                            <div style="padding:0 20px 16px; font-size:13px; flex:1; overflow:hidden;">
                                                <?php if (!empty($staff["contact_no"])) { ?>
                                                <div style="margin-bottom:6px; color:#475569;">
                                                    <i class="fa fa-phone" style="width:16px; color:#94a3b8;"></i> <?php echo $staff["contact_no"]; ?>
                                                </div>
                                                <?php } ?>

                                                <?php if (!empty($staff["department"])) { ?>
                                                <div style="margin-bottom:6px; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($staff["department"]); ?>">
                                                    <i class="fa fa-building-o" style="width:16px; color:#94a3b8;"></i> <?php echo $staff["department"]; ?>
                                                </div>
                                                <?php } ?>

                                                <?php if (!empty($staff["location"])) { ?>
                                                <div style="margin-bottom:6px; color:#475569; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($staff["location"]); ?>">
                                                    <i class="fa fa-map-marker" style="width:16px; color:#94a3b8;"></i> <?php echo $staff["location"]; ?>
                                                </div>
                                                <?php } ?>

                                                <div style="margin-top:8px;">
                                                    <span style="display:inline-block; background:#eef2ff; color:#4338ca; font-size:11px; padding:2px 8px; border-radius:10px; margin-right:4px; margin-bottom:4px;"><?php echo $staff["user_type"]; ?></span>
                                                    <?php if (!empty($staff["designation"])) { ?>
                                                    <span style="display:inline-block; background:#f1f5f9; color:#475569; font-size:11px; padding:2px 8px; border-radius:10px; margin-bottom:4px;"><?php echo $staff["designation"]; ?></span>
                                                    <?php } ?>
                                                </div>

                                                <?php if (!empty($staff['staff_type'])): ?>
                                                <div style="margin-top:6px;">
                                                    <span style="display:inline-block; font-size:11px; padding:2px 8px; border-radius:10px; border-left:3px solid <?php echo $staff['staff_type_color'] ?? '#ccc'; ?>; background:#f8fafc;">
                                                        <i class="fa <?php echo $staff['staff_type_icon'] ?? 'fa-folder'; ?>" style="color:<?php echo $staff['staff_type_color'] ?? '#ccc'; ?>; margin-right:3px;"></i>
                                                        <?php echo $staff['staff_type']; ?>
                                                    </span>
                                                </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div style="border-top:1px solid #f1f5f9; padding:10px 20px; display:flex; justify-content:center; gap:8px;">
                                                <?php if ($can_view) { ?>
                                                <a href="<?php echo base_url(); ?>admin/staff/profile/<?php echo $staff['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                                    <i class="fa fa-reorder"></i> <?php echo $this->lang->line('view'); ?>
                                                </a>
                                                <?php } ?>
                                                <?php if ($can_edit) { ?>
                                                <a href="<?php echo base_url(); ?>admin/staff/edit/<?php echo $staff['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                    <i class="fa fa-pencil"></i> <?php echo $this->lang->line('edit'); ?>
                                                </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div><!--./col-->
                                    <?php
                                        $count++;
                                    }
                                    ?>
                                </div>
                                <?php } ?>
                            </div><!-- /#tab_1 -->

                            <!-- List View Tab -->
                            <div class="tab-pane table-responsive no-padding" id="tab_2">
                                <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('staff_id'); ?></th>
                                            <th><?php echo $this->lang->line('name'); ?></th>
                                            <th><?php echo $this->lang->line('role'); ?></th>
                                            <th><?php echo $this->lang->line('department'); ?></th>
                                            <th><?php echo $this->lang->line('designation'); ?></th>
                                            <th>Staff Type / Category</th>
                                            <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                            <?php
                                            if (!empty($fields)) {
                                                foreach ($fields as $fields_key => $fields_value) { ?>
                                            <th><?php echo $fields_value->name; ?></th>
                                            <?php }
                                            } ?>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($resultlist)) {
                                            // no records
                                        } else {
                                            $count = 1;
                                            foreach ($resultlist as $staff) { ?>
                                        <tr>
                                            <td><?php echo $staff['employee_id']; ?></td>
                                            <td>
                                                <a <?php if ($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view')) { ?> href="<?php echo base_url(); ?>admin/staff/profile/<?php echo $staff['id']; ?>"<?php } ?>><?php echo $staff['name'] . " " . $staff['surname']; ?></a>
                                            </td>
                                            <td><?php echo $staff['user_type']; ?></td>
                                            <td><?php echo $staff['department']; ?></td>
                                            <td><?php echo $staff['designation']; ?></td>
                                            <td>
                                                <?php if (!empty($staff['staff_type'])): ?>
                                                <span style="border-left: 4px solid <?php echo $staff['staff_type_color'] ?? '#ccc'; ?>; padding-left: 8px;">
                                                    <i class="fa <?php echo $staff['staff_type_icon'] ?? 'fa-folder'; ?>" style="color: <?php echo $staff['staff_type_color'] ?? '#ccc'; ?>; margin-right: 5px;"></i>
                                                    <span style="font-weight: 500;"><?php echo $staff['staff_type']; ?></span>
                                                </span>
                                                <?php else: ?>
                                                <span class="text-muted">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $staff['contact_no']; ?></td>
                                            <?php
                                            if (!empty($fields)) {
                                                foreach ($fields as $fields_key => $fields_value) {
                                                    $display_field = $staff[$fields_value->name];
                                                    if ($fields_value->type == "link") {
                                                        $display_field = "<a href=" . $staff[$fields_value->name] . " target='_blank'>" . $staff[$fields_value->name] . "</a>";
                                                    } ?>
                                            <td><?php echo $display_field; ?></td>
                                            <?php }
                                            } ?>
                                            <td class="pull-right white-space-nowrap">
                                                <?php
                                                $userdata = $this->customlib->getUserData();
                                                if (($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view')) || ($userdata["id"] == $staff["id"])) { ?>
                                                <a href="<?php echo base_url(); ?>admin/staff/profile/<?php echo $staff['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                                    <i class="fa fa-reorder"></i>
                                                </a>
                                                <?php }
                                                $a = 0;
                                                $sessionData = $this->session->userdata('admin');
                                                $staff["user_type"];
                                                if (($staff["user_type"] == "Super Admin") && $userdata["id"] == $staff["id"]) {
                                                    $a = 1;
                                                } elseif (($this->rbac->hasPrivilege('staff', 'can_edit')) && ($this->rbac->hasPrivilege('can_see_other_users_profile', 'can_view'))) {
                                                    $a = 1;
                                                }
                                                if ($a == 1) { ?>
                                                <a href="<?php echo base_url(); ?>admin/staff/edit/<?php echo $staff['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                                $count++;
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div><!-- /#tab_2 -->

                        </div><!-- /.tab-content -->
                    </div><!-- /.nav-tabs-custom -->
                </div><!-- /.box box-primary (results) -->
                <?php } ?>

            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function getSectionByClass(class_id, section_id) {
        if (class_id != "" && section_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        }
    }

    $(document).ready(function () {
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id') ?>';
        getSectionByClass(class_id, section_id);
        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        });
    });
</script>
<script src="<?php echo base_url('backend/plugins/select2/js/select2.min.js'); ?>"></script>
<script>
$(document).ready(function() {
    $('#role, #category, #department').select2({
        placeholder: 'Select...',
        allowClear: true,
        width: '100%'
    });
});
</script>
