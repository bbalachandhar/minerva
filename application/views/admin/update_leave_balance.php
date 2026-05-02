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
                        <h3 class="box-title"><i class="fa fa-list"></i> Staff Opening Leave Balances <small class="text-muted" style="font-size:13px; font-weight:normal;">(Admin can update the opening balances, its not the total balance view window)</small></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" id="saveAllBtn">
                                <i class="fa fa-save"></i> Save All
                            </button>
                        </div>
                    </div>

                    <div class="box-body">

                        <!-- Month/Year picker -->
                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-md-12">
                                <form method="get" action="" class="form-inline" style="display:inline-flex; align-items:center; gap:8px;">
                                    <label style="font-weight:600; margin-bottom:0;"><i class="fa fa-calendar"></i> Select Month:</label>
                                    <select name="month" class="form-control input-sm" style="width:130px;">
                                        <?php
                                        $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                                                   7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                                        foreach ($months as $mn => $mlabel): ?>
                                            <option value="<?php echo $mn; ?>" <?php if ($mn == $sel_month) echo 'selected'; ?>>
                                                <?php echo $mlabel; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="year" class="form-control input-sm" style="width:90px;">
                                        <?php for ($y = date('Y') - 3; $y <= date('Y') + 1; $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php if ($y == $sel_year) echo 'selected'; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-arrow-right"></i> Load</button>
                                    <span class="text-muted" style="font-size:12px; margin-left:8px;">
                                        Showing balances for <strong><?php echo date('F Y', mktime(0,0,0,$sel_month,1,$sel_year)); ?></strong>
                                    </span>
                                </form>
                                <input type="hidden" id="selYear"  value="<?php echo $sel_year; ?>">
                                <input type="hidden" id="selMonth" value="<?php echo $sel_month; ?>">
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="row" style="margin-bottom:12px;">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input type="text" id="staffSearch" class="form-control" placeholder="Search by name or employee ID...">
                                </div>
                            </div>
                            <div class="col-md-8 text-right" style="padding-top:6px;">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Opening</strong> = system-cascaded from prior month (read-only).
                                    <strong>Adj</strong> = your +/&#8722; override, persists across payroll re-runs.
                                    Payroll uses <code>Opening + Adj + Monthly Credit</code> for LOP.
                                </small>
                            </div>
                        </div>

                        <div id="saveAllMsg" class="alert" style="display:none;"></div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-condensed" id="leaveBalanceTable">
                                <thead>
                                    <tr style="background:#f5f5f5;">
                                        <th style="min-width:160px;" rowspan="2"><?php echo $this->lang->line('name'); ?></th>
                                        <th style="min-width:120px;" rowspan="2"><?php echo $this->lang->line('employee_id_number'); ?></th>
                                        <th style="min-width:140px;" rowspan="2"><?php echo $this->lang->line('designation'); ?></th>
                                        <?php foreach ($leave_types as $lt): ?>
                                            <th style="min-width:160px; text-align:center;" colspan="2"><?php echo htmlspecialchars($lt['type']); ?></th>
                                        <?php endforeach; ?>
                                        <th style="min-width:80px; text-align:center;" rowspan="2"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                    <tr style="background:#fafafa; font-size:11px;">
                                        <?php foreach ($leave_types as $lt): ?>
                                            <th style="text-align:center; color:#555; font-weight:600;">Opening<br><small style="font-weight:400;">(system)</small></th>
                                            <th style="text-align:center; color:#1a6e9e; font-weight:600;">Adj<br><small style="font-weight:400;">(admin ±)</small></th>
                                        <?php endforeach; ?>
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
                                                $ltid      = $lt['id'];
                                                $entry     = isset($balances[$sid][$ltid]) ? $balances[$sid][$ltid] : null;
                                                $opening   = $entry !== null ? $entry['opening_balance'] : 0;
                                                $adj       = $entry !== null ? $entry['admin_adjustment'] : 0;
                                                $row_exists = $entry && $entry['row_exists'];
                                                $est_title  = 'Estimated from prior month closing — no row yet for this month. Saving creates one.';
                                            ?>
                                            <!-- Read-only opening -->
                                            <td style="text-align:center; padding:4px 6px; background:#f9f9f9; color:#555;">
                                                <?php if ($entry && !$row_exists): ?>
                                                    <span class="label label-default" style="font-size:9px; display:block; margin-bottom:2px;" title="<?php echo $est_title; ?>">EST</span>
                                                <?php endif; ?>
                                                <span style="font-size:13px; font-weight:600;"><?php echo number_format($opening, 1); ?></span>
                                            </td>
                                            <!-- Editable admin_adjustment -->
                                            <td style="text-align:center; padding:4px 6px;">
                                                <input type="number"
                                                    class="form-control input-sm balance-input"
                                                    style="width:75px; margin:auto; text-align:center;"
                                                    step="0.5"
                                                    name="balances[<?php echo $sid; ?>][<?php echo $ltid; ?>]"
                                                    data-staff-id="<?php echo $sid; ?>"
                                                    data-leave-type-id="<?php echo $ltid; ?>"
                                                    data-server-value="<?php echo htmlspecialchars($adj); ?>"
                                                    value="<?php echo htmlspecialchars($adj); ?>"
                                                    placeholder="0"
                                                    autocomplete="off"
                                                    title="Admin adjustment for <?php echo htmlspecialchars($lt['type']); ?> — positive or negative">
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
    var selYear     = $('#selYear').val();
    var selMonth    = $('#selMonth').val();

    // Force server-rendered values on load to avoid browser restoring stale form state.
    $('.balance-input').each(function () {
        var serverValue = $(this).attr('data-server-value');
        $(this).val(serverValue !== undefined ? serverValue : '');
    });

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
        var data = { year: selYear, month: selMonth };
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
        var data    = { staff_id: sid, year: selYear, month: selMonth };

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
