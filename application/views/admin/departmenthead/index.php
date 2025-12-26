<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('assign_department_head', 'can_add')) { ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('assign_department_head'); ?></h3>
                        </div>
                        <form action="<?php echo site_url('admin/departmenthead/assign') ?>" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg'); ?>
                                <?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="department_id"><?php echo $this->lang->line('department'); ?></label><small class="req"> *</small>
                                    <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($department_list as $department) {
                                            ?>
                                            <option value="<?php echo $department['id'] ?>"
                                                <?php if (set_value('department_id') == $department['id']) echo 'selected'; ?>>
                                                <?php echo $department['department_name'] ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('department_id'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="staff_id"><?php echo $this->lang->line('department_head'); ?></label><small class="req"> *</small>
                                    <select id="staff_id" name="staff_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($staff_list as $staff) {
                                            ?>
                                            <option value="<?php echo $staff['id'] ?>"
                                                <?php if (set_value('staff_id') == $staff['id']) echo 'selected'; ?>>
                                                <?php echo $staff['name'] . " " . $staff['surname'] . " (" . $staff['employee_id'] . ")"; ?>
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('staff_id'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-<?php echo ($this->rbac->hasPrivilege('assign_department_head', 'can_add')) ? '8' : '12'; ?>">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('department_head_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                        </div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('department_head_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('department'); ?></th>
                                        <th><?php echo $this->lang->line('department_head'); ?></th>
                                        <th><?php echo $this->lang->line('staff_id'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($department_heads as $head) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name"><?php echo $head['department_name']; ?></td>
                                            <td class="mailbox-name"><?php echo $head['staff_name'] . " " . $head['staff_surname']; ?></td>
                                            <td class="mailbox-name"><?php echo $head['employee_id']; ?></td>
                                            <td class="mailbox-date pull-right no-print">
                                                <?php if ($this->rbac->hasPrivilege('assign_department_head', 'can_delete')) { ?>
                                                    <a href="<?php echo base_url(); ?>admin/departmenthead/delete/<?php echo $head['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')";>
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
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
