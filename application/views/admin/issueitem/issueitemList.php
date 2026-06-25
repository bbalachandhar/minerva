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
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#issueItemModal"><i class="fa fa-plus"></i> <?php echo $this->lang->line('issue_item'); ?></button>
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

<!-- Issue Item Modal -->
<?php if ($this->rbac->hasPrivilege('issue_item', 'can_add')) { ?>
<div class="modal fade" id="issueItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> <?php echo $this->lang->line('issue_item'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/issueitem/add') ?>" id="issueitem" method="post" accept-charset="utf-8">
                <div class="modal-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Issue Target <small class="req">*</small></label>
                            <select name="issue_target_type" id="issue_target_type" class="form-control">
                                <option value="staff" selected>Staff Person</option>
                                <option value="place">Place / Location</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 issue-to-staff-only">
                            <label><?php echo $this->lang->line('user_type'); ?> <small class="req">*</small></label>
                            <select name="account_type" onchange="getIssueUser(this.value)" id="input-type-student" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($roles as $role_value) { ?>
                                    <option value="<?php echo $role_value['id']; ?>"><?php echo $role_value['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4 issue-to-staff-only">
                            <label><?php echo $this->lang->line('issue_to'); ?> <small class="req">*</small></label>
                            <select id="issue_to" name="issue_to" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 issue-to-place-only" style="display:none;">
                            <label>Location Type <small class="req">*</small></label>
                            <select id="issue_location_type" name="issue_location_type" class="form-control">
                                <option value="">Select</option>
                                <option value="Lab">Lab</option><option value="Class Room">Class Room</option>
                                <option value="Department">Department</option><option value="Office">Office</option>
                                <option value="Library">Library</option><option value="Store">Store</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 issue-to-place-only" style="display:none;">
                            <label>Place Name <small class="req">*</small></label>
                            <input type="text" id="issue_place_name" name="issue_place_name" class="form-control" placeholder="e.g. Chemistry Lab">
                        </div>
                        <div class="form-group col-md-2 issue-to-place-only" style="display:none;">
                            <label>Floor</label>
                            <input type="text" id="issue_floor" name="issue_floor" class="form-control" placeholder="e.g. 2">
                        </div>
                        <div class="form-group col-md-2 issue-to-place-only" style="display:none;">
                            <label>Room No</label>
                            <input type="text" id="issue_room_no" name="issue_room_no" class="form-control" placeholder="e.g. 204">
                        </div>
                        <div class="form-group col-md-2 issue-to-place-only" style="display:none;">
                            <label>Block/Wing</label>
                            <input type="text" id="issue_block" name="issue_block" class="form-control" placeholder="e.g. A">
                        </div>
                        <div class="form-group col-md-3 issue-to-place-only" style="display:none;">
                            <label>Building</label>
                            <input type="text" id="issue_building" name="issue_building" class="form-control" placeholder="e.g. Science Block">
                        </div>
                        <div class="form-group col-md-3 issue-to-place-only" style="display:none;">
                            <label>Location Note</label>
                            <input type="text" id="issue_location_note" name="issue_location_note" class="form-control" placeholder="Optional details">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label><?php echo $this->lang->line('issue_by'); ?> <small class="req">*</small></label>
                            <select class="form-control" name="issue_by" id="issue_by">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($staff as $value) { ?>
                                    <option value="<?php echo $value['id']; ?>"><?php echo $value['name'] . ' (' . $value['employee_id'] . ')'; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php echo $this->lang->line('issue_date'); ?> <small class="req">*</small></label>
                            <input id="issue_date" name="issue_date" type="text" class="form-control date" value="" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php echo $this->lang->line('return_date'); ?></label>
                            <input id="return_date" name="return_date" type="text" class="form-control date" value="" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label><?php echo $this->lang->line('note'); ?></label>
                            <textarea name="note" class="form-control" id="note"></textarea>
                        </div>
                    </div>
                    <hr style="margin:8px 0 16px;">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label><?php echo $this->lang->line('item_category'); ?> <small class="req">*</small></label>
                            <select id="item_category_id" name="item_category_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($itemcatlist as $item_category) { ?>
                                    <option value="<?php echo $item_category['id']; ?>"><?php echo $item_category['item_category']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php echo $this->lang->line('item'); ?> <small class="req">*</small></label>
                            <select id="item_id" name="item_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php echo $this->lang->line('quantity'); ?> <small class="req">*</small></label>
                            <input id="quantity" class="form-control" name="quantity">
                            <div id="div_avail" style="display:none; margin-top:4px;">
                                <span style="font-size:12px;"><?php echo $this->lang->line('available_quantity'); ?>: </span>
                                <span id="item_available_quantity" style="font-weight:600;">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="allot-fees btn btn-primary" id="load" data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('please_wait'); ?>">
                        <?php echo $this->lang->line('submit'); ?>
                    </button>
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

<script>
var base_url = '<?php echo base_url(); ?>';

function populateItem(item_id_post, item_category_id_post) {
    if (item_category_id_post != "") {
        $('#item_id').html("");
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: base_url + "admin/itemstock/getItemByCategory",
            data: {'item_category_id': item_category_id_post},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj) {
                    var select = (item_id_post == obj.id) ? "selected=selected" : "";
                    div_data += "<option value=" + obj.id + " " + select + ">" + obj.name + "</option>";
                });
                $('#item_id').append(div_data);
            }
        });
    }
}

$(document).on('change', '#item_category_id', function () {
    $('#item_id').html("");
    populateItem(0, $(this).val());
});

$(document).on('change', '#item_id', function () {
    $('#div_avail').hide();
    availableQuantity($(this).val());
});

function availableQuantity(item_id) {
    if (item_id != "") {
        $('#item_available_quantity').html("");
        $.ajax({
            type: "GET",
            url: base_url + "admin/item/getAvailQuantity",
            data: {'item_id': item_id},
            dataType: "json",
            success: function (data) {
                $('#item_available_quantity').html(data.available);
                $('#div_avail').show();
            }
        });
    }
}

$(document).on('change', '#issue_target_type', function () {
    var target = $(this).val();
    if (target === 'place') {
        $('.issue-to-staff-only').hide();
        $('.issue-to-place-only').show();
        $('#input-type-student').val('');
        $('#issue_to').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
    } else {
        $('.issue-to-place-only').hide();
        $('.issue-to-staff-only').show();
    }
});

function getIssueUser(usertype) {
    $('#issue_to').html("");
    var div_data = "<option value=''><?php echo $this->lang->line('select'); ?></option>";
    $.ajax({
        type: "POST",
        url: base_url + "admin/issueitem/getUser",
        data: {'usertype': usertype},
        dataType: "json",
        success: function (data) {
            $.each(data.result, function (i, obj) {
                var name = (data.usertype == "admin") ? obj.username : obj.name + " " + obj.surname + " (" + obj.employee_id + ")";
                div_data += "<option value=" + obj.id + ">" + name + "</option>";
            });
            $('#issue_to').append(div_data);
        }
    });
}

$('#issueItemModal').on('shown.bs.modal', function () {
    $('#issue_target_type').trigger('change');
});

$("#issueitem").submit(function (e) {
    var data = $(this).serializeArray();
    var issue_to = $('#issue_to option:selected').text();
    data.push({name: 'issue_to_name', value: issue_to});
    var $this = $('.allot-fees');
    $this.button('loading');
    e.preventDefault();
    $.ajax({
        url: $(this).attr("action"),
        type: "POST",
        data: data,
        dataType: 'Json',
        success: function (data) {
            if (data.status == "fail") {
                var message = "";
                $.each(data.error, function (index, value) { message += value; });
                errorMsg(message);
            } else {
                $('#item_available_quantity').html("");
                $('#div_avail').hide();
                document.getElementById("issueitem").reset();
                $('#issue_target_type').trigger('change');
                $('#issueItemModal').modal('hide');
                successMsg(data.message);
                loadIssueList();
            }
            $this.button('reset');
        },
        error: function () { $this.button('reset'); }
    });
});
</script>