<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-object-group"></i> <?php //echo $this->lang->line('inventory'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('issue_item_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('issue_item', 'can_add')) {
    ?>
                                <a href="<?php echo site_url('admin/issueitem/create') ?>" type="button" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo $this->lang->line('issue_item'); ?></a>
                            <?php }
?>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row" style="margin-bottom:12px;">
                            <div class="col-md-2 col-sm-6">
                                <label>Target</label>
                                <select id="filter_issue_target_type" class="form-control input-sm">
                                    <option value="">All</option>
                                    <option value="staff">Staff</option>
                                    <option value="place">Place</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label>Location Type</label>
                                <select id="filter_issue_location_type" class="form-control input-sm">
                                    <option value="">All</option>
                                    <option value="Lab">Lab</option>
                                    <option value="Class Room">Class Room</option>
                                    <option value="Department">Department</option>
                                    <option value="Office">Office</option>
                                    <option value="Library">Library</option>
                                    <option value="Store">Store</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label>Place Name</label>
                                <input type="text" id="filter_issue_place_name" class="form-control input-sm" placeholder="Chemistry Lab">
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label>Floor</label>
                                <input type="text" id="filter_issue_floor" class="form-control input-sm" placeholder="2">
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label>Room No</label>
                                <input type="text" id="filter_issue_room_no" class="form-control input-sm" placeholder="204">
                            </div>
                            <div class="col-md-2 col-sm-6" style="padding-top:24px;">
                                <button type="button" id="btn_issue_filter" class="btn btn-info btn-sm">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <button type="button" id="btn_issue_filter_reset" class="btn btn-default btn-sm">
                                    <i class="fa fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>

                        <div class="mailbox-messages table-responsive overflow-visible">
                                 <table class="table table-striped table-bordered table-hover item-list" data-export-title="<?php echo $this->lang->line('issue_item_list'); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('item'); ?></th>
                                        <th width="20%"><?php echo $this->lang->line('note'); ?></th>
                                        <th><?php echo $this->lang->line('item_category'); ?></th>
                                        <th><?php echo $this->lang->line('issue_return'); ?></th>
                                        <th><?php echo $this->lang->line('issue_to'); ?></th>
                                        <th><?php echo $this->lang->line('issued_by'); ?></th>
                                        <th><?php echo $this->lang->line('quantity'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (right) -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

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
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#confirm-delete').on('show.bs.modal', function (e) {
             
            $('#item_issue_id').val("");
            $('.debug-url').html('');
            $('#modal_item_quantity,#modal_item,#modal_item_cat').text("");
            var item_issue_id = $(e.relatedTarget).data('item');
            var item_category = $(e.relatedTarget).data('category');
            var quantity = $(e.relatedTarget).data('quantity');
            var item_name = $(e.relatedTarget).data('item_name');
            $('#item_issue_id').val(item_issue_id);
            $('#modal_item_cat').text(item_category);
            $('#modal_item').text(item_name);
            $('#modal_item_quantity').text(quantity);
        });
        $("#confirm-delete").modal({
            backdrop: false,
            show: false

        });
    });

    var base_url = '<?php echo base_url() ?>';

    $(document).on('change', '#item_category_id', function (e) {
        $('#item_id').html("");
        var item_category_id = $(this).val();
        populateItem(0, item_category_id);
    });

    $(document).on('click', '.btn-ok', function () {
        var $this = $('.btn-ok');
        $this.button('loading');
        var item_issue_id = $('#item_issue_id').val();
        $.ajax(
                {
                    url: "<?php echo site_url('admin/issueitem/returnItem') ?>",
                    type: "POST",
                    data: {'item_issue_id': item_issue_id},
                    dataType: 'Json',
                    success: function (data, textStatus, jqXHR)
                    {
                        if (data.status == "fail") {
                            errorMsg(data.message);
                        } else {
                            successMsg(data.message);
                            $("#confirm-delete").modal('hide');
                            location.reload();
                        }

                        $this.button('reset');
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        $this.button('reset');
                    }
                });
    });
</script>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('confirm_return'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="item_issue_id" name="item_issue_id" value="">
                <p><?php echo $this->lang->line('are_you_sure_to_return_this_item'); ?></p>

                <ul class="list2">
                    <li><?php echo $this->lang->line('item'); ?><span id="modal_item"></span></li>
                    <li><?php echo $this->lang->line('item_category'); ?><span id="modal_item_cat"></span></li>
                    <li><?php echo $this->lang->line('quantity'); ?><span id="modal_item_quantity"></span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <a class="btn cfees btn-ok" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('return'); ?></a>
            </div>
        </div>
    </div>
</div>

<script>
    ( function ( $ ) {
    'use strict';

    function buildIssueListParams() {
        return {
            issue_target_type: $('#filter_issue_target_type').val(),
            issue_location_type: $('#filter_issue_location_type').val(),
            issue_place_name: $('#filter_issue_place_name').val(),
            issue_floor: $('#filter_issue_floor').val(),
            issue_room_no: $('#filter_issue_room_no').val()
        };
    }

    function loadIssueList() {
        initDatatable('item-list', 'admin/issueitem/getitemlist', buildIssueListParams(), [], 100);
    }

    $(document).ready(function () {
        loadIssueList();

        $('#btn_issue_filter').on('click', function () {
            loadIssueList();
        });

        $('#btn_issue_filter_reset').on('click', function () {
            $('#filter_issue_target_type').val('');
            $('#filter_issue_location_type').val('');
            $('#filter_issue_place_name').val('');
            $('#filter_issue_floor').val('');
            $('#filter_issue_room_no').val('');
            loadIssueList();
        });
    });
} ( jQuery ) )
</script>