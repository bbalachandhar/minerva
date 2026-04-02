<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-edit"></i> <?php echo $this->lang->line('update_leave_balance'); ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-dashboard"></i> <?php echo $this->lang->line('dashboard'); ?></a></li>
            <li><a href="#"><?php echo $this->lang->line('human_resource'); ?></a></li>
            <li class="active"><?php echo $this->lang->line('update_leave_balance'); ?></li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Staff Leave Balances</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" id="saveAllBtn">
                                <i class="fa fa-save"></i> Save All
                            </button>
                        </div>
                    </div>

                    <div class="box-body">

                        <!-- Filter bar -->
                        <div class="row" style="margin-bottom:12px;">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" id="staffSearch" class="form-control" placeholder="Search by name or employee ID...">
                                </div>
                            </div>
                            <div class="col-md-8 text-right" style="padding-top:6px;">
                                <small class="text-muted"><i class="fa fa-info-circle"></i>
                                    Showing <?php echo count($staff_list); ?> active staff members.
                                    Leave blank to clear a balance.
                                </small>
                            </div>
                        </div>

                        <div id="saveAllMsg" class="alert" style="display:none;"></div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-condensed" id="leaveBalanceTable">
                                <thead>
                                    <tr style="background:#f5f5f5;">
                                        <th style="min-width:160px;"><?php echo $this->lang->line('name'); ?></th>
                                        <th style="min-width:120px;"><?php echo $this->lang->line('employee_id_number'); ?></th>
                                        <th style="min-width:140px;"><?php echo $this->lang->line('designation'); ?></th>
                                        <?php foreach ($leave_types as $lt): ?>
                                            <th style="min-width:90px; text-align:center;"><?php echo htmlspecialchars($lt['type']); ?></th>
                                        <?php endforeach; ?>
                                        <th style="min-width:80px; text-align:center;"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($staff_list as $staff): ?>
                                    <?php $sid = $staff['id']; ?>
                                    <tr class="staff-row" data-search="<?php echo strtolower(htmlspecialchars($staff['name'] . ' ' . $staff['surname'] . ' ' . $staff['employee_id'])); ?>">
                                        <td><?php echo htmlspecialchars(trim($staff['name'] . ' ' . $staff['surname'])); ?></td>
                                        <td><?php echo htmlspecialchars($staff['employee_id']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['designation'] ?? '-'); ?></td>
                                        <?php foreach ($leave_types as $lt): ?>
                                            <?php
                                                $ltid    = $lt['id'];
                                                $current = isset($balances[$sid][$ltid]) ? $balances[$sid][$ltid] : '';
                                            ?>
                                            <td style="text-align:center; padding:4px 6px;">
                                                <input type="number"
                                                    class="form-control input-sm balance-input"
                                                    style="width:80px; margin:auto; text-align:center;"
                                                    min="0" step="0.5"
                                                    name="balances[<?php echo $sid; ?>][<?php echo $ltid; ?>]"
                                                    data-staff-id="<?php echo $sid; ?>"
                                                    data-leave-type-id="<?php echo $ltid; ?>"
                                                    value="<?php echo htmlspecialchars($current); ?>"
                                                    placeholder="0">
                                            </td>
                                        <?php endforeach; ?>
                                        <td style="text-align:center; padding:4px 6px;">
                                            <button type="button"
                                                class="btn btn-primary btn-xs save-one-btn"
                                                data-staff-id="<?php echo $sid; ?>"
                                                title="Save this row">
                                                <i class="fa fa-save"></i>
                                            </button>
                                            <span class="save-one-msg" data-staff-id="<?php echo $sid; ?>" style="display:none; font-size:11px;"></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div><!-- /.table-responsive -->

                    </div><!-- /.box-body -->

                    <div class="box-footer clearfix">
                        <button type="button" class="btn btn-success btn-sm pull-right" id="saveAllBtnBottom">
                            <i class="fa fa-save"></i> Save All
                        </button>
                    </div>

                </div><!-- /.box -->
            </div>
        </div>
    </section>
</div>

<script>
$(function () {

    var saveAllUrl  = '<?php echo site_url("admin/update_leave_balance/ajax_save_all"); ?>';
    var saveOneUrl  = '<?php echo site_url("admin/update_leave_balance/ajax_save_one"); ?>';

    // ── Live search ──────────────────────────────────────────────────
    $('#staffSearch').on('keyup', function () {
        var q = $(this).val().toLowerCase().trim();
        $('.staff-row').each(function () {
            $(this).toggle($(this).data('search').indexOf(q) !== -1);
        });
    });

    // ── Collect all balances from visible inputs ──────────────────────
    function collectAll() {
        var balances = {};
        $('.balance-input').each(function () {
            var sid  = $(this).data('staff-id');
            var ltid = $(this).data('leave-type-id');
            var val  = $(this).val();
            if (!balances[sid]) balances[sid] = {};
            balances[sid][ltid] = val;
        });
        // Convert to query-string-friendly flat array for $.ajax data
        var data = {};
        $.each(balances, function (sid, types) {
            $.each(types, function (ltid, val) {
                data['balances[' + sid + '][' + ltid + ']'] = val;
            });
        });
        return data;
    }

    // ── Save All ────────────────────────────────────────────────────
    function doSaveAll(btn) {
        $(btn).button('loading');
        $('#saveAllMsg').hide();

        $.ajax({
            url: saveAllUrl,
            type: 'POST',
            data: collectAll(),
            dataType: 'json',
            success: function (resp) {
                var cls = resp.status === 'success' ? 'alert-success' : 'alert-danger';
                $('#saveAllMsg').removeClass('alert-success alert-danger').addClass(cls)
                    .html('<i class="fa fa-' + (resp.status === 'success' ? 'check' : 'times') + '"></i> ' + resp.message)
                    .show();
                $('html, body').animate({ scrollTop: 0 }, 300);
            },
            error: function () {
                $('#saveAllMsg').removeClass('alert-success').addClass('alert alert-danger')
                    .html('<i class="fa fa-times"></i> Server error. Please try again.').show();
            },
            complete: function () {
                $(btn).button('reset');
            }
        });
    }

    $('#saveAllBtn, #saveAllBtnBottom').on('click', function () {
        doSaveAll(this);
    });

    // ── Save One row ────────────────────────────────────────────────
    $(document).on('click', '.save-one-btn', function () {
        var btn     = $(this);
        var sid     = btn.data('staff-id');
        var msgSpan = $('.save-one-msg[data-staff-id="' + sid + '"]');
        var data    = { staff_id: sid };

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        msgSpan.hide();

        // Collect just this row's inputs
        $('input.balance-input[data-staff-id="' + sid + '"]').each(function () {
            data['balances[' + $(this).data('leave-type-id') + ']'] = $(this).val();
        });

        $.ajax({
            url: saveOneUrl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function (resp) {
                var color = resp.status === 'success' ? 'green' : 'red';
                var icon  = resp.status === 'success' ? 'check' : 'times';
                msgSpan.html('<i class="fa fa-' + icon + '" style="color:' + color + ';"></i>')
                    .attr('title', resp.message).show();
                setTimeout(function () { msgSpan.fadeOut(); }, 3000);
            },
            error: function () {
                msgSpan.html('<i class="fa fa-times" style="color:red;"></i>').show();
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i>');
            }
        });
    });

});
</script>
