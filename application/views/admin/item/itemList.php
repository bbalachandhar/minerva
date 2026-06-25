<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-object-group"></i> <?php //echo $this->lang->line('inventory'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"> <?php echo $this->lang->line('item_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('item', 'can_add')) { ?>
                                <a href="<?php echo site_url('admin/inventoryimport/item'); ?>" class="btn btn-info btn-sm">
                                    <i class="fa fa-upload"></i> Bulk Upload
                                </a>
                                <a href="<?php echo site_url('admin/inventoryimport/downloadsample/item'); ?>" class="btn btn-default btn-sm">
                                    <i class="fa fa-download"></i> Sample CSV
                                </a>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addItemModal">
                                    <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_item'); ?>
                                </button>
                            <?php } ?>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) {?>
                            <?php echo $this->session->flashdata('msg');
                            $this->session->unset_userdata('msg'); ?>
                        <?php }?>
                        <?php if ($this->rbac->hasPrivilege('item', 'can_add')) { ?>
                        <div class="mailbox-messages table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('item_list'); ?></div>
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('item'); ?></th>
                                        <th width="40%"><?php echo $this->lang->line('description'); ?></th>
                                        <th><?php echo $this->lang->line('item_category'); ?>
                                        </th>
                                        <th class="text-right"><?php echo $this->lang->line('unit'); ?>
                                        </th>
                                        <th class="text-right"><?php echo $this->lang->line('available_quantity'); ?>
                                        </th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
if (empty($itemlist)) {
    ?>

                                        <?php
} else {
    foreach ($itemlist as $items) {
        ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $items['name'] ?> </td>
                                                <td class="mailbox-name">
                                                    <?php echo $items['description']; ?>
                                                </td>
                                                <td class="mailbox-name">
                                                    <?php echo $items['item_category']; ?>
                                                </td>
                                                <td class="mailbox-name text-right">
                                                    <?php echo $items['unit']; ?>
                                                </td>
                                                <td class="mailbox-name text-right">
                                                    <?php
echo $items['added_stock'] - $items['issued'];

        ?>
                                                </td>
                                                <td class="mailbox-date pull-right white-space-nowrap">
                                                    <?php if ($this->rbac->hasPrivilege('item', 'can_edit')) {?>
                                                        <a href="<?php echo base_url(); ?>admin/item/edit/<?php echo $items['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php }if ($this->rbac->hasPrivilege('item', 'can_delete')) {?>
                                                        <a href="<?php echo base_url(); ?>admin/item/delete/<?php echo $items['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php }?>
                                                </td>
                                            </tr>
                                            <?php
}
}
?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                        <?php } else { ?>
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('item_list'); ?></div>
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('item'); ?></th>
                                        <th><?php echo $this->lang->line('category'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('available_quantity'); ?>
                                        </th>
                                        <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
if (empty($itemlist)) {
    ?>

                                        <?php
} else {
    foreach ($itemlist as $items) {
        ?>
                                            <tr>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $items['name'] ?></a>

                                                    <div class="fee_detail_popover" style="display: none">
                                                        <?php
if ($items['description'] == "") {
            ?>
                                                            <p class="text text-danger"><?php echo $this->lang->line('no_description'); ?></p>
                                                            <?php
} else {
            ?>
                                                            <p class="text text-info"><?php echo $items['description']; ?></p>
                                                            <?php
}
        ?>
                                                    </div>
                                                </td>
                                                <td class="mailbox-name">
                                                    <?php echo $items['item_category']; ?>
                                                </td>
                                                <td class="mailbox-name">
                                                    <?php
echo $items['added_stock'] - $items['issued'];

        ?>
                                                </td>
                                                <td class="mailbox-date pull-right">
                                                    <?php if ($this->rbac->hasPrivilege('item', 'can_edit')) {?>
                                                        <a href="<?php echo base_url(); ?>admin/item/edit/<?php echo $items['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php }if ($this->rbac->hasPrivilege('item', 'can_delete')) {?>
                                                        <a href="<?php echo base_url(); ?>admin/item/delete/<?php echo $items['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php }?>
                                                </td>
                                            </tr>
                                            <?php
}
}
?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                        <?php } ?>
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<?php if ($this->rbac->hasPrivilege('item', 'can_add')) { ?>
<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="addItemModalLabel"><?php echo $this->lang->line('add_item'); ?></h4>
            </div>
            <form id="form1" action="<?php echo base_url() ?>admin/item" name="employeeform" method="post" accept-charset="utf-8">
                <div class="modal-body">
                    <?php
if (isset($error_message)) {
    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
}
?>
                    <?php echo $this->customlib->getCSRF(); ?>

                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('item'); ?></label><small class="req"> *</small>
                        <input autofocus="" id="name" name="name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('name'); ?>" />
                        <span class="text-danger"><?php echo form_error('name'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('item_category'); ?></label><small class="req"> *</small>
                        <select  id="item_category_id" name="item_category_id" class="form-control" >
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php
foreach ($itemcatlist as $item_category) {
    ?>
                                <option value="<?php echo $item_category['id'] ?>"<?php
if (set_value('item_category_id') == $item_category['id']) {
        echo "selected = selected";
    }
    ?>><?php echo $item_category['item_category'] ?></option>

                                <?php
$count++;
}
?>
                        </select>
                        <span class="text-danger"><?php echo form_error('item_category_id'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('unit') ?></label><small class="req"> *</small>
                        <input autofocus="" id="unit" name="unit" placeholder="" type="text" class="form-control"  value="<?php echo set_value('unit'); ?>" />
                        <span class="text-danger"><?php echo form_error('unit'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                        <textarea class="form-control" id="description" name="description" placeholder="" rows="3"><?php echo set_value('description'); ?></textarea>
                        <span class="text-danger"></span>
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
<?php } ?>

<script>
    $(document).ready(function () {
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });

        // Auto-open modal if validation errors exist
        <?php if (form_error('name') || form_error('item_category_id') || form_error('unit')) { ?>
            $('#addItemModal').modal('show');
        <?php } ?>
    });
</script>
