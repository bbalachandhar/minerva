<?php
    $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
    $batch_no = isset($item['batch_no']) ? $item['batch_no'] : '';
    $license_key = isset($item['license_key']) ? $item['license_key'] : '';
    $manufacturing_date = (isset($item['manufacturing_date']) && $item['manufacturing_date'] != '' && $item['manufacturing_date'] != '0000-00-00')
    ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($item['manufacturing_date']))
    : '';
    $expiry_date = (isset($item['expiry_date']) && $item['expiry_date'] != '' && $item['expiry_date'] != '0000-00-00')
    ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($item['expiry_date']))
    : '';
    $warranty_upto = (isset($item['warranty_upto']) && $item['warranty_upto'] != '' && $item['warranty_upto'] != '0000-00-00')
    ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($item['warranty_upto']))
    : '';
    $license_valid_from = (isset($item['license_valid_from']) && $item['license_valid_from'] != '' && $item['license_valid_from'] != '0000-00-00')
    ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($item['license_valid_from']))
    : '';
    $license_valid_till = (isset($item['license_valid_till']) && $item['license_valid_till'] != '' && $item['license_valid_till'] != '0000-00-00')
    ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($item['license_valid_till']))
    : '';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-object-group"></i> <?php //echo $this->lang->line('inventory'); ?></h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('item_stock', 'can_add') || $this->rbac->hasPrivilege('item_stock', 'can_edit')) {
    ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('edit_item_stock'); ?></h3>
                        </div><!-- /.box-header -->
                        <form id="form1" action="<?php echo base_url() ?>admin/itemstock/edit/<?php echo $item['id'] ?>"  id="itemstockform" name="itemstockform" method="post" accept-charset="utf-8" enctype="multipart/form-data">

                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php
if (isset($error_message)) {
        echo "<div class='alert alert-danger'>" . $error_message . "</div>";
    }
    ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('item_category'); ?></label><small class="req"> *</small>

                                    <select autofocus="" id="item_category_id" name="item_category_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
foreach ($itemcatlist as $item_category) {
        ?>
                                            <option value="<?php echo $item_category['id'] ?>"<?php
if (set_value('item_category_id', $item['item_category_id']) == $item_category['id']) {
            echo "selected = selected";
        }
        ?>><?php echo $item_category['item_category'] ?></option>
                                            <?php
}
    ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('item_category_id'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('item'); ?></label><small class="req"> *</small>
                                    <select  id="item_id" name="item_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('item_id'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('supplier'); ?></label>
                                    <select  id="supplier_id" name="supplier_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
foreach ($itemsupplier as $itemsup) {
        ?>
                                            <option value="<?php echo $itemsup['id'] ?>"<?php
if (set_value('supplier_id', $item['supplier_id']) == $itemsup['id']) {
            echo "selected = selected";
        }
        ?>><?php echo $itemsup['item_supplier'] ?></option>

                                            <?php
}
    ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('supplier_id'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('store'); ?></label>

                                    <select  id="store_id" name="store_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
foreach ($itemstore as $itemstore) {
        ?>
                                            <option value="<?php echo $itemstore['id'] ?>"<?php
if (set_value('store_id', $item['store_id']) == $itemstore['id']) {
            echo "selected = selected";
        }
        ?>><?php echo $itemstore['item_store'] ?> <?php if($itemstore['code']){ echo ' ('.$itemstore['code'].')'; } ?></option>
                                            <?php
}
    ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('store_id'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('quantity'); ?></label><small class="req"> *</small><span id="item_unit"></span>
                                    <div class="">
                                        <span class="miplus">
                                            <select class="form-control" name="symbol">
                                                <option value="+" <?php echo set_select('symbol', '+', ($item['symbol'] == "+") ? true : false); ?>>+</option>
                                                <option value="-" <?php echo set_select('symbol', '-', ($item['symbol'] == "-") ? true : false); ?>>-</option>
                                            </select>
                                        </span>
                                        <input id="quantity" name="quantity" placeholder="" type="text" class="form-control miplusinput"  value="<?php echo set_value('quantity', preg_replace('/[\s\-+]/', '', $item['quantity'])); ?>" />
                                    </div>
                                    <span class="text-danger"><?php echo form_error('quantity'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('purchase_price'); ?> (<?php echo $currency_symbol; ?>)</label>
                                    <input id="purchase_price" name="purchase_price" placeholder="" type="text" class="form-control purchase_price"  value="<?php echo convertBaseAmountCurrencyFormat($item['purchase_price']); ?>" />
                                    <span class="text-danger"><?php echo form_error('purchase_price'); ?></span>
                                </div>
                                <?php
if ($item['date'] != '0000-00-00') {

        $item_date = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($item['date']));
    }
    ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('date'); ?></label><small class="req"> *</small>
                                    <input id="date" name="date" placeholder="" type="text" class="form-control date"  value="<?php if ($item['date'] != '0000-00-00') {
        echo set_value('date', $item_date);}?>" readonly="readonly" />
                                    <span class="text-danger"><?php echo form_error('date'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="batch_no">Batch No</label>
                                    <input id="batch_no" name="batch_no" placeholder="e.g. BATCH-2026-001" type="text" class="form-control" value="<?php echo set_value('batch_no', $batch_no); ?>" />
                                </div>
                                <div class="form-group">
                                    <label for="manufacturing_date">Manufacturing Date</label>
                                    <input id="manufacturing_date" name="manufacturing_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('manufacturing_date', $manufacturing_date); ?>" readonly="readonly" />
                                </div>
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input id="expiry_date" name="expiry_date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('expiry_date', $expiry_date); ?>" readonly="readonly" />
                                </div>
                                <div class="form-group">
                                    <label for="warranty_upto">Warranty Upto</label>
                                    <input id="warranty_upto" name="warranty_upto" placeholder="" type="text" class="form-control date" value="<?php echo set_value('warranty_upto', $warranty_upto); ?>" readonly="readonly" />
                                </div>
                                <div class="form-group">
                                    <label for="license_key">License Number/Key</label>
                                    <input id="license_key" name="license_key" placeholder="e.g. LIC-XYZ-2048" type="text" class="form-control" value="<?php echo set_value('license_key', $license_key); ?>" />
                                </div>
                                <div class="form-group">
                                    <label for="license_valid_from">License Valid From</label>
                                    <input id="license_valid_from" name="license_valid_from" placeholder="" type="text" class="form-control date" value="<?php echo set_value('license_valid_from', $license_valid_from); ?>" readonly="readonly" />
                                </div>
                                <div class="form-group">
                                    <label for="license_valid_till">License Valid Till</label>
                                    <input id="license_valid_till" name="license_valid_till" placeholder="" type="text" class="form-control date" value="<?php echo set_value('license_valid_till', $license_valid_till); ?>" readonly="readonly" />
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('attach_document'); ?></label>
                                    <input id="item_photo" name="item_photo" placeholder="" type="file" class="filestyle form-control"  value="<?php echo set_value('item_photo'); ?>" />
                                    <span class="text-danger"><?php echo form_error('item_photo'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description" placeholder="" rows="3"><?php echo set_value('description', $item['description']); ?></textarea>
                                    <span class="text-danger"></span>
                                </div>
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div><!--/.col (right) -->
                <!-- left column -->
            <?php }?>
            <div class="col-md-<?php
if ($this->rbac->hasPrivilege('item_stock', 'can_add') || $this->rbac->hasPrivilege('item_stock', 'can_edit')) {
    echo "8";
} else {
    echo "12";
}
?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"> <?php echo $this->lang->line('item_stock_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="mailbox-messages table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('item_stock_list'); ?></div>
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('item'); ?></th>
                                        <th><?php echo $this->lang->line('category'); ?></th>
                                        <th><?php echo $this->lang->line('supplier'); ?></th>
                                        <th><?php echo $this->lang->line('store'); ?></th>
                                        <th><?php echo $this->lang->line('quantity'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('purchase_price'); ?>  (<?php echo $currency_symbol; ?>)</th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th width="12%" class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
if (empty($itemlist)) {
    ?>

                                        <?php
} else {
    $today_ts = strtotime(date('Y-m-d'));
    $expiring_threshold_ts = strtotime('+30 days', $today_ts);
    foreach ($itemlist as $items) {
        $expiry_status_label = '';
        $expiry_status_class = '';
        $license_status_label = '';
        $license_status_class = '';
        $row_has_expiry_alert = false;

        if (isset($items['expiry_date']) && $items['expiry_date'] !== '' && $items['expiry_date'] != '0000-00-00') {
            $expiry_ts = strtotime($items['expiry_date']);
            if ($expiry_ts !== false) {
                if ($expiry_ts < $today_ts) {
                    $expiry_status_label = 'Expired';
                    $expiry_status_class = 'label-danger';
                    $row_has_expiry_alert = true;
                } elseif ($expiry_ts <= $expiring_threshold_ts) {
                    $days_left = (int) floor(($expiry_ts - $today_ts) / 86400);
                    $expiry_status_label = 'Expiring in ' . $days_left . ' day' . ($days_left == 1 ? '' : 's');
                    $expiry_status_class = 'label-warning';
                    $row_has_expiry_alert = true;
                }
            }
        }

        if (isset($items['license_valid_till']) && $items['license_valid_till'] !== '' && $items['license_valid_till'] != '0000-00-00') {
            $license_ts = strtotime($items['license_valid_till']);
            if ($license_ts !== false) {
                if ($license_ts < $today_ts) {
                    $license_status_label = 'License Expired';
                    $license_status_class = 'label-danger';
                    $row_has_expiry_alert = true;
                } elseif ($license_ts <= $expiring_threshold_ts) {
                    $days_left = (int) floor(($license_ts - $today_ts) / 86400);
                    $license_status_label = 'License expires in ' . $days_left . ' day' . ($days_left == 1 ? '' : 's');
                    $license_status_class = 'label-warning';
                    $row_has_expiry_alert = true;
                }
            }
        }
        ?>
                                            <tr class="<?php echo $row_has_expiry_alert ? 'expiry-alert-row' : ''; ?>">
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $items['name'] ?></a>
                                                    <?php if ($expiry_status_label !== '') { ?><br><span class="label <?php echo $expiry_status_class; ?>"><?php echo $expiry_status_label; ?></span><?php } ?>
                                                    <?php if ($license_status_label !== '') { ?><br><span class="label <?php echo $license_status_class; ?>"><?php echo $license_status_label; ?></span><?php } ?>

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
                                                        <?php if (isset($items['batch_no']) && $items['batch_no'] !== '') { ?><p><strong>Batch:</strong> <?php echo $items['batch_no']; ?></p><?php } ?>
                                                        <?php if (isset($items['manufacturing_date']) && $items['manufacturing_date'] !== '' && $items['manufacturing_date'] != '0000-00-00') { ?><p><strong>Mfg Date:</strong> <?php echo $this->customlib->dateformat($items['manufacturing_date']); ?></p><?php } ?>
                                                        <?php if (isset($items['expiry_date']) && $items['expiry_date'] !== '' && $items['expiry_date'] != '0000-00-00') { ?><p><strong>Expiry:</strong> <?php echo $this->customlib->dateformat($items['expiry_date']); ?></p><?php } ?>
                                                        <?php if (isset($items['warranty_upto']) && $items['warranty_upto'] !== '' && $items['warranty_upto'] != '0000-00-00') { ?><p><strong>Warranty Upto:</strong> <?php echo $this->customlib->dateformat($items['warranty_upto']); ?></p><?php } ?>
                                                        <?php if (isset($items['license_key']) && $items['license_key'] !== '') { ?><p><strong>License No/Key:</strong> <?php echo $items['license_key']; ?></p><?php } ?>
                                                        <?php if (isset($items['license_valid_from']) && $items['license_valid_from'] !== '' && $items['license_valid_from'] != '0000-00-00') { ?><p><strong>License From:</strong> <?php echo $this->customlib->dateformat($items['license_valid_from']); ?></p><?php } ?>
                                                        <?php if (isset($items['license_valid_till']) && $items['license_valid_till'] !== '' && $items['license_valid_till'] != '0000-00-00') { ?><p><strong>License Till:</strong> <?php echo $this->customlib->dateformat($items['license_valid_till']); ?></p><?php } ?>
                                                    </div>
                                                </td>
                                                <td class="mailbox-name">
                                                    <?php echo $items['item_category']; ?>
                                                </td>

                                                <td class="mailbox-name">
                                                    <?php echo $items['item_supplier']; ?>
                                                </td>

                                                <td class="mailbox-name">
                                                    <?php echo $items['item_store']; ?> <?php if($items['code']){ echo ' ('.$items['code'].')'; } ?>
                                                </td>

                                                <td class="mailbox-name">
                                                    <?php echo $items['quantity']; ?>
                                                </td>

                                                <td class="mailbox-name text-right">
                                                    <?php echo amountFormat($items['purchase_price']); ?>
                                                </td>

                                                <td class="mailbox-name">
                                                    <?php if ($items['date'] != '0000-00-00') {echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($items['date']));}?>
                                                </td>

                                                <td class="mailbox-date pull-right">
                                                    <?php if ($items['attachment']) {
            ?>
                                                        <a href="<?php echo base_url(); ?>admin/itemstock/download/<?php echo $items['attachment'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                            <i class="fa fa-download"></i>
                                                        </a>
                                                    <?php }
        ?>

                                                    <a href="<?php echo base_url(); ?>admin/itemstock/edit/<?php echo $items['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" data-record-id="<?php echo $items['quantity']; ?>" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>

                                                    <a href="<?php echo base_url(); ?>admin/itemstock/delete/<?php echo $items['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
}
}
?>

                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
        <div class="row">
            <!-- left column -->
            <!-- right column -->
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    $(function(){
        $("#item_unit").html("<?php echo $item_unit; ?>");
    });

    $(document).ready(function () {
        var item_id_post = '<?php echo $item['item_id']; ?>';
        item_id_post = (item_id_post != "") ? item_id_post : 0;
        var item_category_id_post = '<?php echo $item['item_category_id']; ?>';
        item_category_id_post = (item_category_id_post != "") ? item_category_id_post : 0;
        populateItem(item_id_post, item_category_id_post);

        function populateItem(item_id_post, item_category_id_post) {
            if (item_category_id_post != "") {
                $('#item_id').html("");

                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "GET",
                    url: base_url + "admin/itemstock/getItemByCategory",
                    data: {'item_category_id': item_category_id_post},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var select = "";
                            if (item_id_post == obj.id) {
                                var select = "selected=selected";
                            }
                            div_data += "<option value=" + obj.id + " " + select + ">" + obj.name + "</option>";
                        });
                        $('#item_id').append(div_data);
                    }
                });
            }
        }

        $("#btnreset").click(function () {
            $("#form1")[0].reset();
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

        $(document).on('change', '#item_category_id', function (e) {
            $('#item_id').html("");
            var item_category_id = $(this).val();
            populateItem(0, item_category_id);
        });

        $(document).on('change', '#item_id', function (e) {
            var item_category_id = $(this).val();
            $('#item_unit').html("");
            $.ajax({
            type: "GET",
            url: base_url + "admin/itemstock/getItemunit",
            data: {'id': item_category_id},
            dataType: "json",
            success: function (data) {
                $('#item_unit').html(data.unit);
            }

            });
        });
    });
</script>

<style>
    .expiry-alert-row td {
        background-color: #fff8e1;
    }
</style>