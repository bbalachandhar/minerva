<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-object-group"></i> <?php //echo $this->lang->line('item_category'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary" id="exphead">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('item_category_list'); ?></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('item_category_list'); ?></div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover example">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('item_category'); ?></th>
                                            <th><?php echo $this->lang->line('description'); ?></th>
                                            <?php if (!empty($supports_asset_fields)) { ?>
                                            <th>Asset Category</th>
                                            <th>Tracking Mode</th>
                                            <?php } ?>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($categorylist)) { ?>
                                        <?php } else {
                                            $count = 1;
                                            foreach ($categorylist as $category) { ?>
                                                <tr>
                                                    <td class="mailbox-name"><?php echo $category['item_category'] ?></td>
                                                    <td class="mailbox-name"><?php echo $category['description'] ?></td>
                                                    <?php if (!empty($supports_asset_fields)) { ?>
                                                    <td class="mailbox-name"><?php echo !empty($category['is_asset']) ? 'Yes' : 'No'; ?></td>
                                                    <td class="mailbox-name"><?php echo html_escape((string) ($category['asset_tracking_mode'] ?? 'bulk')); ?></td>
                                                    <?php } ?>
                                                    <td class="mailbox-date pull-right no-print">
                                                        <?php if ($this->rbac->hasPrivilege('item_category', 'can_edit')) { ?>
                                                            <a href="<?php echo base_url(); ?>admin/itemcategory/edit/<?php echo $category['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                        <?php } if ($this->rbac->hasPrivilege('item_category', 'can_delete')) { ?>
                                                            <a href="<?php echo base_url(); ?>admin/itemcategory/delete/<?php echo $category['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                        <?php }
                                            $count++;
                                        } ?>
                                    </tbody>
                                </table><!-- /.table -->
                            </div>
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div><!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- Edit Item Category Modal -->
<?php if ($this->rbac->hasPrivilege('item_category', 'can_edit')) { ?>
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="window.location.href='<?php echo site_url('admin/itemcategory'); ?>'" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editModalLabel"><?php echo $this->lang->line('edit_item_category'); ?></h4>
            </div>
            <form action="<?php echo site_url("admin/itemcategory/edit/" . $id) ?>" id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                <div class="modal-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <div class="form-group">
                        <label><?php echo $this->lang->line('item_category'); ?></label><small class="req"> *</small>
                        <input autofocus="" id="itemcategory" name="itemcategory" placeholder="" type="text" class="form-control" value="<?php echo set_value('itemcategory', $itemcategory['item_category']); ?>" />
                        <span class="text-danger"><?php echo form_error('itemcategory'); ?></span>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('description'); ?></label>
                        <textarea class="form-control" id="description" name="description" placeholder="" rows="3"><?php echo set_value('description', $itemcategory['description']); ?></textarea>
                        <span class="text-danger"><?php echo form_error('description'); ?></span>
                    </div>
                    <?php if (!empty($supports_asset_fields)) { ?>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_asset" value="1" <?php echo set_checkbox('is_asset', '1', !empty($itemcategory['is_asset'])); ?>>
                            Asset Category (Create assets from GRN)
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Asset Tracking Mode</label>
                        <select name="asset_tracking_mode" class="form-control">
                            <option value="bulk" <?php echo set_select('asset_tracking_mode', 'bulk', ((string) ($itemcategory['asset_tracking_mode'] ?? 'bulk') === 'bulk')); ?>>Bulk (single asset record for accepted quantity)</option>
                            <option value="unit" <?php echo set_select('asset_tracking_mode', 'unit', ((string) ($itemcategory['asset_tracking_mode'] ?? '') === 'unit')); ?>>Unit-wise (one asset record per unit)</option>
                        </select>
                    </div>
                    <?php } ?>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo site_url('admin/itemcategory'); ?>" class="btn btn-default"><?php echo $this->lang->line('cancel'); ?></a>
                    <button type="submit" class="btn btn-info"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<script>
    $(document).ready(function () {
        $('#editModal').modal({backdrop: 'static', keyboard: false});
        $('#editModal').on('hidden.bs.modal', function () {
            window.location.href = '<?php echo site_url("admin/itemcategory"); ?>';
        });
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });
    });
</script>
