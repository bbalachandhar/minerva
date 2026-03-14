<style type="text/css">
    #div_avail {
        display: none;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-building-o"></i> <?php //echo $this->lang->line('inventory'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('issue_item'); ?></h3>
                    </div>

                    <form action="<?php echo site_url('admin/issueitem/add') ?>" id="issueitem" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <div class="row">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                                <?php } ?>
                                <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>" . $error_message . "</div>"; } ?>
                                <?php echo $this->customlib->getCSRF(); ?>

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="issue_target_type">Issue Target</label><small class="req"> *</small>
                                    <select name="issue_target_type" id="issue_target_type" class="form-control">
                                        <option value="staff" selected>Staff Person</option>
                                        <option value="place">Place / Location</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4 col-sm-4 issue-to-staff-only">
                                    <label for="input-type-student"><?php echo $this->lang->line('user_type'); ?></label><small class="req"> *</small>
                                    <select name="account_type" onchange="getIssueUser(this.value)" id="input-type-student" class="form-control ac_type">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($roles as $role_value) { ?>
                                            <option value="<?php echo $role_value['id']; ?>"><?php echo $role_value['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-4 col-sm-4 issue-to-staff-only">
                                    <label for="issue_to"><?php echo $this->lang->line('issue_to'); ?></label><small class="req"> *</small>
                                    <select id="issue_to" name="issue_to" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3 col-sm-3 issue-to-place-only" style="display:none;">
                                    <label for="issue_location_type">Location Type</label><small class="req"> *</small>
                                    <select id="issue_location_type" name="issue_location_type" class="form-control">
                                        <option value="">Select</option>
                                        <option value="Lab">Lab</option>
                                        <option value="Class Room">Class Room</option>
                                        <option value="Department">Department</option>
                                        <option value="Office">Office</option>
                                        <option value="Library">Library</option>
                                        <option value="Store">Store</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-3 col-sm-3 issue-to-place-only" style="display:none;">
                                    <label for="issue_place_name">Place Name</label><small class="req"> *</small>
                                    <input type="text" id="issue_place_name" name="issue_place_name" class="form-control" placeholder="e.g. Chemistry Lab">
                                </div>

                                <div class="form-group col-md-2 col-sm-2 issue-to-place-only" style="display:none;">
                                    <label for="issue_floor">Floor</label>
                                    <input type="text" id="issue_floor" name="issue_floor" class="form-control" placeholder="e.g. 2">
                                </div>

                                <div class="form-group col-md-2 col-sm-2 issue-to-place-only" style="display:none;">
                                    <label for="issue_room_no">Room No</label>
                                    <input type="text" id="issue_room_no" name="issue_room_no" class="form-control" placeholder="e.g. 204">
                                </div>

                                <div class="form-group col-md-2 col-sm-2 issue-to-place-only" style="display:none;">
                                    <label for="issue_block">Block/Wing</label>
                                    <input type="text" id="issue_block" name="issue_block" class="form-control" placeholder="e.g. A">
                                </div>

                                <div class="form-group col-md-3 col-sm-3 issue-to-place-only" style="display:none;">
                                    <label for="issue_building">Building</label>
                                    <input type="text" id="issue_building" name="issue_building" class="form-control" placeholder="e.g. Science Block">
                                </div>

                                <div class="form-group col-md-3 col-sm-3 issue-to-place-only" style="display:none;">
                                    <label for="issue_location_note">Location Note</label>
                                    <input type="text" id="issue_location_note" name="issue_location_note" class="form-control" placeholder="Optional landmark/details">
                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="issue_by"><?php echo $this->lang->line('issue_by'); ?></label><small class="req"> *</small>
                                    <select class="form-control" name="issue_by" id="issue_by">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($staff as $value) { ?>
                                            <option value="<?php echo $value['id']; ?>"><?php echo $value['name'] . ' (' . $value['employee_id'] . ')'; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="issue_date"><?php echo $this->lang->line('issue_date'); ?></label><small class="req"> *</small>
                                    <input id="issue_date" name="issue_date" type="text" class="form-control date" value="<?php echo set_value('issue_date'); ?>" readonly>
                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="return_date"><?php echo $this->lang->line('return_date'); ?></label>
                                    <input id="return_date" name="return_date" type="text" class="form-control date" value="<?php echo set_value('return_date'); ?>" readonly>
                                </div>

                                <div class="form-group col-md-12 col-sm-12">
                                    <label for="note"><?php echo $this->lang->line('note'); ?></label>
                                    <textarea name="note" class="form-control" id="note"><?php echo set_value('note'); ?></textarea>
                                </div>

                                <div class="clearfix"></div>
                                <hr>

                                <div class="form-group col-sm-4">
                                    <label for="item_category_id"><?php echo $this->lang->line('item_category'); ?></label><small class="req"> *</small>
                                    <select id="item_category_id" name="item_category_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($itemcatlist as $item_category) { ?>
                                            <option value="<?php echo $item_category['id']; ?>" <?php if (set_value('item_category_id') == $item_category['id']) { echo 'selected="selected"'; } ?>><?php echo $item_category['item_category']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label for="item_id"><?php echo $this->lang->line('item'); ?></label><small class="req"> *</small>
                                    <select id="item_id" name="item_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label for="quantity"><?php echo $this->lang->line('quantity'); ?></label><small class="req"> *</small>
                                    <input id="quantity" class="form-control" name="quantity">
                                    <div id="div_avail">
                                        <span><?php echo $this->lang->line('available_quantity'); ?> : </span>
                                        <span id="item_available_quantity">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="allot-fees btn btn-primary btn-sm pull-right" id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>">
                                <?php echo $this->lang->line('submit'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';

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
                        var select = "";
                        if (item_id_post == obj.id) {
                            select = "selected=selected";
                        }
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
                    var name = '';
                    if (data.usertype == "admin") {
                        name = obj.username;
                    } else {
                        name = obj.name + " " + obj.surname + " (" + obj.employee_id + ")";
                    }
                    div_data += "<option value=" + obj.id + ">" + name + "</option>";
                });
                $('#issue_to').append(div_data);
            }
        });
    }

    $('#issue_target_type').trigger('change');

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
                    $.each(data.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    $('#item_available_quantity').html("");
                    $('#div_avail').hide();
                    document.getElementById("issueitem").reset();
                    $('#issue_target_type').trigger('change');
                    successMsg(data.message);
                    location.reload();
                }
                $this.button('reset');
            },
            error: function () {
                $this.button('reset');
            }
        });
    });
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>
