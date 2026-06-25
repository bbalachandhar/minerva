<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-columns"></i> <?php echo $this->lang->line('marks_division'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('division_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('marks_division', 'can_add')) { ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='<?php echo site_url('admin/marksdivision'); ?>'"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_marks_division'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                        <?php } ?>
                        <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>" . $error_message . "</div>"; } ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo $this->lang->line('division_name'); ?></th>
                                        <th><?php echo $this->lang->line('percent_from'); ?></th>
                                        <th><?php echo $this->lang->line('percent_upto'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1; foreach ($division_list as $division) { ?>
                                    <tr<?php if ($division->id == $editdivision->id) { echo ' style="background:#e3f2fd;"'; } ?>>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo $division->name; ?></td>
                                        <td><?php echo $division->percentage_from; ?></td>
                                        <td><?php echo $division->percentage_to; ?></td>
                                        <td class="text-right white-space-nowrap">
                                            <?php if ($this->rbac->hasPrivilege('marks_division', 'can_edit')) { ?>
                                            <a href="<?php echo site_url('admin/marksdivision/edit/' . $division->id); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <?php } if ($this->rbac->hasPrivilege('marks_division', 'can_delete')) { ?>
                                            <a href="<?php echo site_url('admin/marksdivision/delete/' . $division->id); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                                <i class="fa fa-remove"></i>
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
    </section>
</div>

<!-- Edit Division Modal -->
<div class="modal fade" id="addDivisionModal" tabindex="-1" role="dialog" aria-labelledby="editDivisionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editDivisionModalLabel"><?php echo $this->lang->line('edit_marks_division'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/marksdivision/edit/' . $id); ?>" method="post" accept-charset="utf-8">
                <div class="modal-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <input type="hidden" name="id" value="<?php echo set_value('id', $editdivision->id); ?>">

                    <div class="form-group">
                        <label><?php echo $this->lang->line('division_name'); ?><small class="req"> *</small></label>
                        <input id="name" name="name" type="text" class="form-control" value="<?php echo set_value('name', $editdivision->name); ?>" />
                        <span class="text-danger"><?php echo form_error('name'); ?></span>
                    </div>

                    <div class="form-group">
                        <label><?php echo $this->lang->line('percent_from'); ?><small class="req"> *</small></label>
                        <input id="percentage_from" name="percentage_from" type="text" class="form-control" value="<?php echo set_value('percentage_from', $editdivision->percentage_from); ?>" />
                        <span class="text-danger"><?php echo form_error('percentage_from'); ?></span>
                    </div>

                    <div class="form-group">
                        <label><?php echo $this->lang->line('percent_upto'); ?><small class="req"> *</small></label>
                        <input id="percentage_to" name="percentage_to" type="text" class="form-control" value="<?php echo set_value('percentage_to', $editdivision->percentage_to); ?>" />
                        <span class="text-danger"><?php echo form_error('percentage_to'); ?></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-info"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#addDivisionModal').modal('show');
});
</script>
