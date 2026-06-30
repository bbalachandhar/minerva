<?php
$date_fmt = $this->customlib->getSchoolDateFormat();
$active_days = array_map('intval', explode(',', $notify_days));
?>
<style>
.alert-badge {
    display:inline-block;padding:4px 12px;border-radius:20px;
    font-size:12px;font-weight:700;color:#fff;
}
.expiry-section-title {
    font-size:15px;font-weight:700;color:#444;
    border-bottom:2px solid #f0ad4e;padding-bottom:6px;
    margin:0 0 16px;
}
.day-chip {
    display:inline-block;padding:6px 16px;border-radius:20px;
    border:2px solid #ddd;cursor:pointer;margin-right:8px;margin-bottom:8px;
    font-size:13px;font-weight:600;transition:all .15s;
    user-select:none;
}
.day-chip.selected { background:#f0ad4e;border-color:#f0ad4e;color:#fff; }
.day-chip:not(.selected):hover { border-color:#f0ad4e;color:#f0ad4e; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><i class="fa fa-bell text-warning"></i> Vehicle Expiry Alerts
        <small>Monitor documents &amp; configure who gets notified</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li>Transport</li>
        <li class="active">Vehicle Expiry Alerts</li>
    </ol>
  </section>

  <section class="content">

    <!-- ── EXPIRY STATUS DASHBOARD ── -->
    <?php
    $critical = array_filter($upcoming_expiries, fn($e) => (int)$e['days_remaining'] <= 5);
    $warning  = array_filter($upcoming_expiries, fn($e) => (int)$e['days_remaining'] > 5 && (int)$e['days_remaining'] <= 15);
    $upcoming = array_filter($upcoming_expiries, fn($e) => (int)$e['days_remaining'] > 15);
    ?>

    <!-- Summary cards -->
    <div class="row" style="margin-bottom:10px;">
        <div class="col-sm-4">
            <div class="info-box" style="border-left:4px solid #d9534f;">
                <span class="info-box-icon" style="background:#d9534f;"><i class="fa fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Critical (≤ 5 days)</span>
                    <span class="info-box-number"><?php echo count($critical); ?> document(s)</span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="info-box" style="border-left:4px solid #f0ad4e;">
                <span class="info-box-icon" style="background:#f0ad4e;"><i class="fa fa-warning"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Warning (6–15 days)</span>
                    <span class="info-box-number"><?php echo count($warning); ?> document(s)</span>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="info-box" style="border-left:4px solid #31b0d5;">
                <span class="info-box-icon" style="background:#31b0d5;"><i class="fa fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Upcoming (16–60 days)</span>
                    <span class="info-box-number"><?php echo count($upcoming); ?> document(s)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiry table -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-table"></i> Upcoming Expiries — Next 60 Days</h3>
            <?php if (empty($upcoming_expiries)): ?>
            <span class="label label-success" style="margin-left:8px;">All documents are valid beyond 60 days ✓</span>
            <?php endif; ?>
        </div>
        <div class="box-body" style="padding:0;">
        <?php if (!empty($upcoming_expiries)): ?>
            <div class="table-responsive">
            <table class="table table-bordered table-hover" style="font-size:13px;margin-bottom:0;">
                <thead>
                    <tr style="background:#f4f4f4;">
                        <th style="width:30px">#</th>
                        <th>Vehicle No.</th>
                        <th>Model</th>
                        <th>Registration No.</th>
                        <th>Document</th>
                        <th>Expiry Date</th>
                        <th style="width:130px;text-align:center;">Days Left</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i=1; foreach ($upcoming_expiries as $e):
                    $d = (int)$e['days_remaining'];
                    if      ($d <= 5)  { $row = 'danger';  $bg = '#d9534f'; }
                    elseif  ($d <= 15) { $row = 'warning'; $bg = '#f0ad4e'; }
                    else               { $row = '';         $bg = '#31b0d5'; }
                ?>
                <tr class="<?php echo $row; ?>">
                    <td><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($e['vehicle_no']); ?></strong></td>
                    <td><?php echo htmlspecialchars($e['vehicle_model']); ?></td>
                    <td><?php echo htmlspecialchars($e['registration_number']); ?></td>
                    <td><?php echo htmlspecialchars($e['expiry_label']); ?></td>
                    <td><?php echo $e['expiry_date'] ? date($date_fmt, strtotime($e['expiry_date'])) : '—'; ?></td>
                    <td style="text-align:center;">
                        <span class="alert-badge" style="background:<?php echo $bg; ?>;">
                            <?php echo $d; ?> day<?php echo $d != 1 ? 's' : ''; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding:40px;color:#aaa;">
                <i class="fa fa-check-circle" style="font-size:40px;color:#5cb85c;"></i>
                <p style="margin-top:10px;font-size:15px;">No vehicle documents are expiring in the next 60 days.</p>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- ── TEST NOTIFICATION BUTTON ── -->
    <div class="row" style="margin-bottom:4px;">
        <div class="col-md-12">
            <div class="alert alert-info" style="padding:10px 16px;margin-bottom:10px;">
                <strong><i class="fa fa-flask"></i> Test Notification</strong> —
                Send a test email to all configured recipients right now (bypasses the day-threshold filter — useful for verifying email delivery).
                &nbsp;
                <button id="btnTestNotification" class="btn btn-info btn-sm"
                        data-loading-text="<i class='fa fa-spinner fa-spin'></i> Sending...">
                    <i class="fa fa-send"></i> Send Test Email Now
                </button>
                <span id="testNotifResult" style="margin-left:10px;font-weight:600;"></span>
            </div>
        </div>
    </div>

    <!-- ── NOTIFICATION SETTINGS ── -->
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-cog"></i> Notification Settings</h3>
            <small class="text-muted" style="display:block;margin-top:4px;">
                Who should be notified and when — cron runs daily at 8:00 AM and emails on the configured days.
            </small>
        </div>
        <div class="box-body">
        <form id="vehicleAssigneesForm">
            <?php echo $this->customlib->getCSRF(); ?>

            <!-- 1. Recipients -->
            <p class="expiry-section-title"><i class="fa fa-users" style="color:#f0ad4e;"></i>&nbsp; Notification Recipients</p>
            <div class="row">
                <?php foreach ([1, 2, 3] as $slot): ?>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Recipient <?php echo $slot; ?></label>
                        <select name="assignee_<?php echo $slot; ?>" class="form-control veh-sel" style="width:100%;">
                            <option value="">— None —</option>
                            <?php foreach ($staffList as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo (isset($assigneesBySlot[$slot]) && $assigneesBySlot[$slot] == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name'] . ' ' . ($s['surname'] ?? '')); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-block" style="font-size:11px;">Receives email &amp; WhatsApp alerts</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 2. Alert timing -->
            <p class="expiry-section-title" style="margin-top:20px;"><i class="fa fa-clock-o" style="color:#f0ad4e;"></i>&nbsp; When to Send Alerts</p>
            <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Select how many days before expiry each alert should be sent. You can pick multiple.</p>
            <div style="margin-bottom:16px;">
                <?php foreach ([30, 15, 10, 5, 3] as $day):
                    $sel = in_array($day, $active_days);
                ?>
                <label class="day-chip <?php echo $sel ? 'selected' : ''; ?>" for="day_<?php echo $day; ?>">
                    <input type="checkbox" id="day_<?php echo $day; ?>" name="notify_days[]"
                           value="<?php echo $day; ?>" <?php echo $sel ? 'checked' : ''; ?>
                           style="position:absolute;opacity:0;width:0;">
                    <?php echo $day; ?> days
                </label>
                <?php endforeach; ?>
            </div>
            <p class="help-block" style="font-size:12px;margin-top:0;">
                <i class="fa fa-info-circle text-info"></i>
                The cron checks daily — alerts fire on the exact day that matches e.g. 30 days before expiry, 15 days before, etc. No duplicates.
            </p>

            <!-- 3. Channels -->
            <p class="expiry-section-title" style="margin-top:20px;"><i class="fa fa-paper-plane" style="color:#f0ad4e;"></i>&nbsp; Delivery Channels</p>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <div class="checkbox">
                            <label style="font-size:14px;">
                                <input type="checkbox" name="enable_email" value="1" <?php echo $enable_email ? 'checked' : ''; ?>>
                                <strong>Email</strong>
                                <small class="text-muted">(sent via configured email provider — <a href="<?php echo site_url('emailconfig'); ?>" target="_blank">Email Settings</a>)</small>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="form-group">
                        <label>WhatsApp Template ID <small class="text-muted">(optional)</small></label>
                        <input type="text" name="wa_template_id" class="form-control"
                               value="<?php echo htmlspecialchars($wa_template_id ?? ''); ?>"
                               placeholder="e.g. vehicle_expiry_reminder">
                        <p class="help-block" style="font-size:11px;">
                            Leave blank for email only. Template variables:
                            <code>{{vehicle_no}}</code> <code>{{expiry_type}}</code>
                            <code>{{expiry_date}}</code> <code>{{days_remaining}}</code>
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4. Save + Cron info -->
            <div class="row" style="margin-top:10px;">
                <div class="col-sm-5">
                    <button type="submit" class="btn btn-warning btn-lg btn-block" id="saveBtn"
                            data-loading-text="<i class='fa fa-spinner fa-spin'></i> Saving...">
                        <i class="fa fa-save"></i>&nbsp; Save Settings
                    </button>
                </div>
                <div class="col-sm-7">
                    <div style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:6px;padding:12px 14px;">
                        <strong><i class="fa fa-terminal text-muted"></i> EC2 Cron (already installed on all instances)</strong>
                        <code style="display:block;font-size:11px;word-break:break-all;margin-top:6px;color:#555;">
                            0 8 * * * curl -s "<?php echo base_url(); ?>index.php/cron/vehicleExpiryReminder/<?php echo htmlspecialchars($cron_key); ?>"
                        </code>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>

  </section>
</div>

<script>
$(function() {
    // Styled day chips — toggle checkbox + class on click
    $('.day-chip').on('click', function(e) {
        e.preventDefault();
        var $cb = $(this).find('input[type="checkbox"]');
        var checked = !$cb.prop('checked');
        $cb.prop('checked', checked);
        $(this).toggleClass('selected', checked);
    });

    // Select2 for staff dropdowns
    $('.veh-sel').select2({ placeholder: '— None —', allowClear: true, width: '100%' });

    // Test notification
    $('#btnTestNotification').on('click', function() {
        var $btn = $(this).button('loading');
        $('#testNotifResult').text('').css('color','');
        $.post('<?php echo site_url("admin/vehicle/send_test_notification"); ?>',
            {<?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'},
            function(res) {
                if (res.status === 'success') {
                    $('#testNotifResult').css('color','#3c763d').text('✓ ' + res.message);
                } else {
                    $('#testNotifResult').css('color','#a94442').text('✗ ' + res.message);
                }
                $btn.button('reset');
            }, 'json'
        ).fail(function() {
            $('#testNotifResult').css('color','#a94442').text('✗ Request failed. Check server logs.');
            $btn.button('reset');
        });
    });

    // Save form
    $('#vehicleAssigneesForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#saveBtn').button('loading');
        $.ajax({
            url: '<?php echo site_url("admin/vehicle/saveAssignees"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    successMsg(res.message || 'Settings saved.');
                } else {
                    errorMsg(res.message || 'Save failed.');
                }
            },
            error: function() { errorMsg('An error occurred. Please try again.'); },
            complete: function() { $btn.button('reset'); }
        });
    });
});
</script>
