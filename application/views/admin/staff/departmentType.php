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
        <h1>
            <i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if (($this->rbac->hasPrivilege('department', 'can_add')) || ($this->rbac->hasPrivilege('department', 'can_edit'))) {
    ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $title; ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/department/department') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8"  enctype="multipart/form-data">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {
        ?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="type"  name="type" placeholder="" type="text" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["department_name"];
    }
    ?>" />
                                    <span class="text-danger"><?php echo form_error('type'); ?></span>
                                    <input autofocus="" id="type"  name="departmenttypeid" placeholder="" type="hidden" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["id"];
    }
    ?>" />
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('department_head'); ?></label><small class="req"> *</small>
                                    <select name="dept_head_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($stafflist as $staff): ?>
                                            <option value="<?php echo $staff['id']; ?>" <?php if (isset($result) && $result['dept_head_id'] == $staff['id']) echo 'selected'; ?>>
                                                <?php echo $staff['name'] . ' ' . $staff['surname'] . ' (' . $staff['employee_id'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('dept_head_id'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Academic Department</label>
                                    <div>
                                        <label class="radio-inline">
                                            <input type="radio" name="is_academic" value="1" <?php echo (!isset($result) || !isset($result['is_academic']) || $result['is_academic'] == 1) ? 'checked' : ''; ?>> Yes
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="is_academic" value="0" <?php echo (isset($result) && isset($result['is_academic']) && $result['is_academic'] == 0) ? 'checked' : ''; ?>> No
                                        </label>
                                    </div>
                                    <span class="text-muted" style="font-size:12px;">Non-academic departments (Admin, Transport, etc.) won't appear in academic screens</span>
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
if (($this->rbac->hasPrivilege('department', 'can_add')) || ($this->rbac->hasPrivilege('department', 'can_edit'))) {
    echo "8";
} else {
    echo "12";
}
?>  ">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('department_list'); ?>s</h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                        </div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('department_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('department_head'); ?></th>
                                        <th>Type</th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$count = 1;
foreach ($departmenttype as $value) {
    $status = "";

    if ($value["is_active"] == "yes") {
        $status = "Active";
    } else {
        $status = "Inactive";
    }
    ?>
                                        <tr>
                                            <td class="mailbox-name"> <?php echo $value['department_name'] ?></td>
                                            <td class="mailbox-name">
                                                <?php echo $value['dept_head_name']; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($value['is_academic']) && $value['is_academic'] == 0): ?>
                                                    <span class="label label-warning">Non-Academic</span>
                                                <?php else: ?>
                                                    <span class="label label-success">Academic</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="mailbox-date pull-right no-print">
                                                <?php if ($this->rbac->hasPrivilege('department', 'can_edit')) {
        ?>
                                                    <a href="<?php echo base_url(); ?>admin/department/departmentedit/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                <?php }if ($this->rbac->hasPrivilege('department', 'can_delete')) {
        ?>
                                                    <a href="<?php echo base_url(); ?>admin/department/departmentdelete/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')";>
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