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
            <?php if (($this->rbac->hasPrivilege('designation', 'can_add')) || ($this->rbac->hasPrivilege('designation', 'can_edit'))) {
    ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $title; ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/designation/designation') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8"  enctype="multipart/form-data">
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
        echo $result["designation"];
    }
    ?>" />
                                    <span class="text-danger"><?php echo form_error('type'); ?></span>
                                    <input autofocus="" id="type"  name="designationid" placeholder="" type="hidden" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["id"];
    }
    ?>" />
                                </div>
                                <div class="form-group">
                                    <label for="category_id"><?php echo $this->lang->line('staff_type'); ?> / Category</label>
                                    <select id="category_id" name="category_id" class="form-control">
                                        <option value="">Select Category</option>
                                        <?php if (isset($categories)): ?>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                    style="border-left: 4px solid <?php echo $cat['color']; ?>;"
                                                    <?php if (isset($result) && $result['category_id'] == $cat['id']) echo 'selected'; ?>>
                                                    <?php echo $cat['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('category_id'); ?></span>
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
if (($this->rbac->hasPrivilege('designation', 'can_add')) || ($this->rbac->hasPrivilege('designation', 'can_edit'))) {
    echo "8";
} else {
    echo "12";
}
?>">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('designation_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                        </div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('designation_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('designation'); ?></th>
                                        <th>Staff Type / Category</th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$count = 1;
foreach ($designation as $value) {
    $status = "";

    if ($value["is_active"] == "yes") {
        $status = "Active";
    } else {
        $status = "Inactive";
    }
    ?>
                                        <tr>
                                            <td class="mailbox-name"> <?php echo $value['designation'] ?></td>
                                            <td>
                                                <?php if (!empty($value['category_id'])): ?>
                                                    <span style="display: inline-block; padding: 4px 8px; border-radius: 3px; 
                                                                  border-left: 4px solid <?php echo $value['color'] ?? '#ccc'; ?>; 
                                                                  background: #f5f5f5;">
                                                        <i class="fa <?php echo $value['icon'] ?? 'fa-folder'; ?>" 
                                                           style="color: <?php echo $value['color'] ?? '#ccc'; ?>;"></i>
                                                        <?php echo $value['category_name'] ?? ''; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Uncategorized</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="mailbox-date pull-right no-print">
                                                <?php if ($this->rbac->hasPrivilege('designation', 'can_edit')) {?>
                                                    <a href="<?php echo base_url(); ?>admin/designation/designationedit/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                <?php }if ($this->rbac->hasPrivilege('designation', 'can_delete')) {?>
                                                    <a href="<?php echo base_url(); ?>admin/designation/designationdelete/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')";>
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