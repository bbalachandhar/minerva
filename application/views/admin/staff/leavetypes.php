<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">

            <?php if (($this->rbac->hasPrivilege('leave_types', 'can_add'))) {
    ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $title; ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/leavetypes/createleavetype') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8"  enctype="multipart/form-data">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="type"  name="type" placeholder="" type="text" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["type"];
    }
    ?>" />
                                    <span class="text-danger"><?php echo form_error('type'); ?></span>

                                    <input autofocus="" id="type"  name="leavetypeid" placeholder="" type="hidden" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["id"];
    }
    ?>" />
                                </div>
                                <div class="form-group">
                                    <label for="is_staff_specific"><?php echo $this->lang->line('applicable_for'); ?></label>
                                    <select name="is_staff_specific" class="form-control">
                                        <option value="All" <?php if (isset($result) && $result['is_staff_specific'] == 'All') {
    echo 'selected';
}
?>><?php echo $this->lang->line('all'); ?></option>
                                        <option value="Student" <?php if (isset($result) && $result['is_staff_specific'] == 'Student') {
    echo 'selected';
}
?>><?php echo $this->lang->line('student'); ?></option>
                                        <option value="Staff" <?php if (isset($result) && $result['is_staff_specific'] == 'Staff') {
    echo 'selected';
}
?>><?php echo $this->lang->line('staff'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="max_leave_days"><?php echo $this->lang->line('max_leave_days'); ?></label>
                                    <input type="number" name="max_leave_days" class="form-control" value="<?php if (isset($result)) {
    echo $result['max_leave_days'];
} else {
    echo 0;
}
?>">
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="is_lop" value="1" <?php if (isset($result) && $result['is_lop'] == 1) {
    echo 'checked';
}
?>> <?php echo $this->lang->line('loss_of_pay'); ?>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="is_carry_forward" name="is_carry_forward" value="1" <?php if (isset($result) && $result['is_carry_forward'] == 1) {
    echo 'checked';
}
?>> <?php echo $this->lang->line('carry_forward'); ?>
                                    </label>
                                </div>
                                <div class="form-group" id="max_carry_forward_group" <?php if (!isset($result) || $result['is_carry_forward'] != 1) {
    echo 'style="display: none;"';
}
?>>
                                    <label for="max_carry_forward"><?php echo $this->lang->line('max_carry_forward'); ?></label>
                                    <input type="number" name="max_carry_forward" class="form-control" value="<?php if (isset($result)) {
    echo $result['max_carry_forward'];
}
?>">
                                </div>
                                <div class="form-group">
                                    <label for="gender_specific"><?php echo $this->lang->line('gender_specific'); ?></label>
                                    <select name="gender_specific" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <option value="All" <?php if (isset($result) && ($result['gender_specific'] == 'All' || $result['gender_specific'] == '')) {
    echo 'selected';
}
?>><?php echo $this->lang->line('all'); ?></option>
                                        <option value="Male" <?php if (isset($result) && $result['gender_specific'] == 'Male') {
    echo 'selected';
}
?>><?php echo $this->lang->line('male'); ?></option>
                                        <option value="Female" <?php if (isset($result) && $result['gender_specific'] == 'Female') {
    echo 'selected';
}
?>><?php echo $this->lang->line('female'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="leave_encashment" value="1" <?php if (isset($result) && $result['leave_encashment'] == 1) {
    echo 'checked';
}
?>> <?php echo $this->lang->line('leave_encashment'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php }?>
            <div class="col-md-<?php
if ($this->rbac->hasPrivilege('leave_types', 'can_add')) {
    echo "8";
} else {
    echo "12";
}
?>">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('leave_type_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                        </div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('leave_type_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('applicable_for'); ?></th>
                                        <th><?php echo $this->lang->line('max_leave_days'); ?></th>
                                        <th><?php echo $this->lang->line('loss_of_pay'); ?></th>
                                        <th><?php echo $this->lang->line('carry_forward'); ?></th>
                                        <th><?php echo $this->lang->line('max_carry_forward'); ?></th>
                                        <th><?php echo $this->lang->line('gender_specific'); ?></th>
                                        <th><?php echo $this->lang->line('leave_encashment'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$count = 1;
foreach ($leavetype as $value) {
    ?>
                                        <tr>
                                            <td class="mailbox-name"> <?php echo $value['type'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['is_staff_specific'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['max_leave_days'] ?></td>
                                            <td class="mailbox-name"> <?php echo ($value['is_lop']) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <td class="mailbox-name"> <?php echo ($value['is_carry_forward']) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <td class="mailbox-name"> <?php echo $value['max_carry_forward'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['gender_specific'] ?></td>
                                            <td class="mailbox-name"> <?php echo ($value['leave_encashment']) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <td class="mailbox-date pull-right no-print">
                                                <?php if ($this->rbac->hasPrivilege('leave_types', 'can_edit')) {?>
                                                    <a href="<?php echo base_url(); ?>admin/leavetypes/leaveedit/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                <?php }if ($this->rbac->hasPrivilege('leave_types', 'can_delete')) {?>
                                                    <a href="<?php echo base_url(); ?>admin/leavetypes/leavedelete/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')";>
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php }?>
                                            </td>
                                        </tr>
                                        <?php
}
$count++;
?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="">
                        <div class="mailbox-controls">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(document).ready(function () {
        $('#is_carry_forward').change(function () {
            if (this.checked) {
                $('#max_carry_forward_group').show();
            } else {
                $('#max_carry_forward_group').hide();
            }
        });
    });
</script>

