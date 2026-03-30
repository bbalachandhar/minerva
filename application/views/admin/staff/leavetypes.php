<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">

            <?php if (($this->rbac->hasPrivilege('leave_types', 'can_add'))) {
    ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $title; ?></h3>
                        </div>
                        <form id="form1" action="<?php echo site_url('admin/leavetypes/createleavetype') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8"  enctype="multipart/form-data">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) {?>
                                    <?php echo $this->session->flashdata('msg');
        $this->session->unset_userdata('msg'); ?>
                                <?php }?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="type"  name="type" placeholder="" type="text" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["type"];
    }
    ?>" />
                                    <span class="text-danger"><?php echo form_error('type'); ?></span>

                                    <input autofocus="" id="type"  name="leavetypeid" placeholder="" type="hidden" class="form-control"  value="<?php
if (isset($result)) {
        echo $result["id"];
    }
    ?>" />
                                </div>
                                <div class="form-group">
                                    <label for="is_staff_specific"><?php echo $this->lang->line('applicable_for'); ?></label>
                                    <select name="is_staff_specific" class="form-control">
                                        <option value="All" <?php if (isset($result) && $result['is_staff_specific'] == 'All') {
    echo 'selected';
}
?>><?php echo $this->lang->line('all'); ?></option>
                                        <option value="Student" <?php if (isset($result) && $result['is_staff_specific'] == 'Student') {
    echo 'selected';
}
?>><?php echo $this->lang->line('student'); ?></option>
                                        <option value="Staff" <?php if (isset($result) && $result['is_staff_specific'] == 'Staff') {
    echo 'selected';
}
?>><?php echo $this->lang->line('staff'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="max_leave_days"><?php echo $this->lang->line('max_leave_days'); ?></label>
                                    <input type="number" name="max_leave_days" class="form-control" value="<?php if (isset($result)) {
    echo $result['max_leave_days'];
} else {
    echo 0;
}
?>">
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="is_lop" value="1" <?php if (isset($result) && $result['is_lop'] == 1) {
    echo 'checked';
}
?>> <?php echo $this->lang->line('loss_of_pay'); ?>
                                    </label>
                                </div>
                                <?php if (!empty($has_balance_check_flag)) { ?>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="requires_balance_check" value="1" <?php if (!isset($result) || (isset($result['requires_balance_check']) && (int) $result['requires_balance_check'] === 1)) {
    echo 'checked';
}
?>> Requires Balance Check
                                    </label>
                                    <p class="help-block" style="margin-bottom:0;">Uncheck for OD/claim-based leaves that should allow apply without leave balance.</p>
                                </div>
                                <?php } ?>
                                <?php if (!empty($has_credit_source_flag)) { ?>
                                <div class="form-group">
                                    <label for="credit_source_type_id">Consumes Credit From</label>
                                    <select name="credit_source_type_id" class="form-control">
                                        <option value="">-- None (independent leave type) --</option>
                                        <?php foreach ($leavetype as $lt): ?>
                                            <?php if (isset($result) && (int)$lt['id'] === (int)$result['id']) continue; ?>
                                            <option value="<?php echo $lt['id']; ?>" <?php if (isset($result) && isset($result['credit_source_type_id']) && (int)$result['credit_source_type_id'] === (int)$lt['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($lt['type']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="help-block" style="margin-bottom:0;">Select if this leave type consumes credit earned by another leave type (e.g. CPL consumes OD balance).</p>
                                </div>
                                <?php } ?>
                                <?php if (!empty($has_day_type_flag)) { ?>
                                <div class="form-group">
                                    <label for="day_type_restriction">Day-Type Restriction</label>
                                    <select name="day_type_restriction" class="form-control">
                                        <option value="">-- No restriction --</option>
                                        <option value="working_day" <?php if (isset($result) && $result['day_type_restriction'] === 'working_day') echo 'selected'; ?>>Working Days Only (e.g. OD)</option>
                                        <option value="holiday" <?php if (isset($result) && $result['day_type_restriction'] === 'holiday') echo 'selected'; ?>>Holidays Only (e.g. CPL)</option>
                                    </select>
                                    <p class="help-block" style="margin-bottom:0;">Restrict this leave type to working days or holidays only. Leave blank for no restriction.</p>
                                </div>
                                <?php } ?>
                                <?php if (!empty($has_strict_day_lock_flag)) { ?>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="strict_day_lock" value="1" <?php if (isset($result) && (int) $result['strict_day_lock'] === 1) echo 'checked'; ?>>
                                        Lock Day on Approval <small>(Day Lock)</small>
                                    </label>
                                    <p class="help-block" style="margin-bottom:0;">
                                        When checked, approving a leave of this type writes a <strong>day-lock record</strong> for each date in the request.
                                        Payroll uses this to override biometric attendance — preventing double-benefit (present count + leave credit on the same day).
                                        Disable for standard leave types (CL, SL, etc.) that rely solely on biometric data.
                                        <strong>Enable for OD, CPL, and similar claim-based types only.</strong>
                                    </p>
                                </div>
                                <?php } ?>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="is_carry_forward" name="is_carry_forward" value="1" <?php if (isset($result) && $result['is_carry_forward'] == 1) {
    echo 'checked';
}
?>> <?php echo $this->lang->line('carry_forward'); ?>
                                    </label>
                                </div>
                                <div class="form-group" id="max_carry_forward_group" <?php if (!isset($result) || $result['is_carry_forward'] != 1) {
    echo 'style="display: none;"';
}
?>>
                                    <label for="max_carry_forward"><?php echo $this->lang->line('max_carry_forward'); ?></label>
                                    <input type="number" name="max_carry_forward" class="form-control" value="<?php if (isset($result)) {
    echo $result['max_carry_forward'];
}
?>">
                                </div>
                                <div class="form-group">
                                    <label for="gender_specific"><?php echo $this->lang->line('gender_specific'); ?></label>
                                    <select name="gender_specific" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <option value="All" <?php if (isset($result) && ($result['gender_specific'] == 'All' || $result['gender_specific'] == '')) {
    echo 'selected';
}
?>><?php echo $this->lang->line('all'); ?></option>
                                        <option value="Male" <?php if (isset($result) && $result['gender_specific'] == 'Male') {
    echo 'selected';
}
?>><?php echo $this->lang->line('male'); ?></option>
                                        <option value="Female" <?php if (isset($result) && $result['gender_specific'] == 'Female') {
    echo 'selected';
}
?>><?php echo $this->lang->line('female'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="leave_encashment" value="1" <?php if (isset($result) && $result['leave_encashment'] == 1) {
    echo 'checked';
}
?>> <?php echo $this->lang->line('leave_encashment'); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php }?>
            <div class="col-md-<?php
if ($this->rbac->hasPrivilege('leave_types', 'can_add')) {
    echo "8";
} else {
    echo "12";
}
?>">
                <div class="box box-primary" id="tachelist">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('leave_type_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/leavetypes/bulk_upload'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_leaves'); ?>
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="mailbox-controls">
                        </div>
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('leave_type_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('id'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('applicable_for'); ?></th>
                                        <th><?php echo $this->lang->line('max_leave_days'); ?></th>
                                        <th><?php echo $this->lang->line('loss_of_pay'); ?></th>
                                        <?php if (!empty($has_balance_check_flag)) { ?><th>Requires Balance Check</th><?php } ?>
                                        <?php if (!empty($has_credit_source_flag)) { ?><th>Consumes Credit From</th><?php } ?>
                                        <?php if (!empty($has_day_type_flag)) { ?><th>Day-Type Restriction</th><?php } ?>
                                        <?php if (!empty($has_strict_day_lock_flag)) { ?><th>Day Lock</th><?php } ?>
                                        <th><?php echo $this->lang->line('carry_forward'); ?></th>
                                        <th><?php echo $this->lang->line('max_carry_forward'); ?></th>
                                        <th><?php echo $this->lang->line('gender_specific'); ?></th>
                                        <th><?php echo $this->lang->line('leave_encashment'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
$count = 1;
foreach ($leavetype as $value) {
    ?>
                                        <tr>
                                            <td class="mailbox-name"> <?php echo $value['id'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['type'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['is_staff_specific'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['max_leave_days'] ?></td>
                                            <td class="mailbox-name"> <?php echo ($value['is_lop']) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <?php if (!empty($has_balance_check_flag)) { ?><td class="mailbox-name"><?php echo (!isset($value['requires_balance_check']) || (int) $value['requires_balance_check'] === 1) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td><?php } ?>
                                            <?php if (!empty($has_credit_source_flag)) { ?>
                                            <td class="mailbox-name">
                                                <?php
                                                if (!empty($value['credit_source_type_id'])) {
                                                    $src = array_filter($leavetype, function($lt) use ($value) { return (int)$lt['id'] === (int)$value['credit_source_type_id']; });
                                                    $src = reset($src);
                                                    echo $src ? htmlspecialchars($src['type']) : '—';
                                                } else {
                                                    echo '—';
                                                }
                                                ?>
                                            </td>
                                            <?php } ?>
                                            <?php if (!empty($has_day_type_flag)) { ?>
                                            <td class="mailbox-name">
                                                <?php
                                                $dtr = isset($value['day_type_restriction']) ? $value['day_type_restriction'] : null;
                                                if ($dtr === 'working_day') echo 'Working Days Only';
                                                elseif ($dtr === 'holiday') echo 'Holidays Only';
                                                else echo '—';
                                                ?>
                                            </td>
                                            <?php } ?>
                                            <?php if (!empty($has_strict_day_lock_flag)) { ?>
                                            <td class="mailbox-name">
                                                <?php echo (!empty($value['strict_day_lock'])) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?>
                                            </td>
                                            <?php } ?>
                                            <td class="mailbox-name"> <?php echo ($value['is_carry_forward']) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <td class="mailbox-name"> <?php echo $value['max_carry_forward'] ?></td>
                                            <td class="mailbox-name"> <?php echo $value['gender_specific'] ?></td>
                                            <td class="mailbox-name"> <?php echo ($value['leave_encashment']) ? $this->lang->line('yes') : $this->lang->line('no'); ?></td>
                                            <td class="mailbox-date pull-right no-print">
                                                <a class="btn btn-info btn-xs apply_to_all" data-toggle="tooltip" title="<?php echo $this->lang->line('apply_to_all'); ?>" data-original-title="<?php echo $this->lang->line('apply_to_all'); ?>" data-leave-type-id="<?php echo $value['id']; ?>">
                                                    <i class="fa fa-solid fa-square-check"></i>
                                                </a>
                                                <?php if ($this->rbac->hasPrivilege('leave_types', 'can_edit')) {?>
                                                    <a href="<?php echo base_url(); ?>admin/leavetypes/leaveedit/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                <?php }if ($this->rbac->hasPrivilege('leave_types', 'can_delete')) {?>
                                                    <a href="<?php echo base_url(); ?>admin/leavetypes/leavedelete/<?php echo $value['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')";>
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php }?>
                                            </td>
                                        </tr>
                                        <?php
}
$count++;
?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="">
                        <div class="mailbox-controls">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================
     DEVELOPER DOCUMENTATION PANEL — Leave Types Implementation
     URL: /admin/leavetypes
     ============================================================ -->
<div class="content-wrapper" style="margin-top:0; padding-top:0;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info collapsed-box">
                    <div class="box-header with-border" style="cursor:pointer;" data-widget="collapse">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Developer Reference: Leave Types — Payroll, Credit & Day-Lock Logic</h3>
                        <div class="box-tools pull-right"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
                    </div>
                    <div class="box-body" style="display:none;">
                        <style>
                            .devdoc h4 { margin-top:18px; border-bottom:1px solid #ddd; padding-bottom:4px; }
                            .devdoc code { background:#f5f5f5; padding:2px 5px; border-radius:3px; font-size:12px; }
                            .devdoc table { width:100%; border-collapse:collapse; margin-bottom:12px; font-size:13px; }
                            .devdoc table th { background:#f0f0f0; padding:6px 10px; text-align:left; }
                            .devdoc table td { padding:5px 10px; border-bottom:1px solid #eee; vertical-align:top; }
                            .devdoc .impact-badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:11px; font-weight:bold; }
                            .devdoc .impact-present { background:#dff0d8; color:#3c763d; }
                            .devdoc .impact-absent  { background:#fcf8e3; color:#8a6d3b; }
                            .devdoc .impact-lop     { background:#f2dede; color:#a94442; }
                            .devdoc .flow-box { background:#f9f9f9; border-left:4px solid #31b0d5; padding:8px 14px; margin:8px 0; border-radius:3px; font-size:13px; }
                        </style>
                        <div class="devdoc">

                            <h4>Overview</h4>
                            <p>
                                This page manages all leave types. Each leave type participates in three systems simultaneously:
                                <strong>Payroll LOP calculation</strong>, <strong>Leave credit adjustment</strong>, and <strong>Day-lock attendance override</strong>.
                                Understanding how these interoperate is critical before adding or editing leave types.
                            </p>
                            <p><strong>Active leave types and their roles:</strong></p>
                            <table>
                                <tr><th style="width:10%">Type</th><th>Purpose</th><th>Day Lock</th><th>Credit Pool</th></tr>
                                <tr><td><code>OD</code></td><td>Staff works off-campus on a <em>working day</em>. Earns its own credit balance (no attendance deduction).</td><td>PAID_PRESENT</td><td>Earns OD balance</td></tr>
                                <tr><td><code>HOD</code></td><td>Staff works on a <em>holiday</em>. Earns HOD balance on approval → staff can spend it as CPL later.</td><td>PAID_PRESENT (on holiday date — display only)</td><td>Earns HOD balance</td></tr>
                                <tr><td><code>CPL</code></td><td>Staff takes a compensatory leave day. Consumes HOD credit pool. No LOP if HOD balance is sufficient.</td><td>PAID_ABSENT</td><td>Consumes HOD balance</td></tr>
                                <tr><td><code>CL / ML</code></td><td>Standard leave types. Consume their own allotted balance. No day lock.</td><td>—</td><td>Own allotment</td></tr>
                            </table>

                            <h4>Leave Type Fields — What Each Field Controls</h4>
                            <table>
                                <tr><th style="width:24%">Field</th><th>Effect on Payroll &amp; Attendance</th></tr>
                                <tr><td><code>is_lop</code> (Loss of Pay)</td><td>
                                    If checked, absences on requested dates are LOP — salary deducted proportional to <code>absent_days / working_days</code>.
                                    LOP types never earn or consume credits.
                                </td></tr>
                                <tr><td><code>requires_balance_check</code></td><td>
                                    If checked (default), staff must have sufficient balance before applying. Uncheck for claim-based types (OD, HOD) where credit is earned on approval, not before.
                                </td></tr>
                                <tr><td><code>credit_source_type_id</code> (Consumes Credit From)</td><td>
                                    Designates this type as a <em>credit consumer</em>. When an approved leave of this type results in an absent day, the system debits the source type's monthly balance instead of triggering LOP.
                                    <br><strong>Current config:</strong> CPL → consumes HOD balance (<code>credit_source_type_id = HOD.id</code>). If HOD balance is zero, falls back to LOP.
                                    <br>Leave NULL for independent types (OD, HOD, CL, ML).
                                </td></tr>
                                <tr><td><code>strict_day_lock</code> (Lock Day on Approval)</td><td>
                                    When enabled, approving a leave writes rows to <code>staff_day_status</code> for each date in range. Payroll and attendance report read <code>staff_day_status</code> FIRST — biometric is ignored for those dates.
                                    <br><strong>Enable for: OD, HOD, CPL.</strong> Do not enable for CL, ML or other standard types.
                                </td></tr>
                            </table>

                            <h4>Day-Lock System — Detailed Logic</h4>
                            <p>Fixes the <em>double-benefit bug</em>: without day lock, a staff member on OD who also punched in at campus would get paid as Present (biometric) AND earn OD credit — effectively paid twice. Day lock overrides biometric for those dates.</p>
                            <table>
                                <tr><th>Leave Status Event</th><th>Action on <code>staff_day_status</code></th></tr>
                                <tr><td>Leave approved</td><td>INSERT rows for each date (<code>ON DUPLICATE KEY UPDATE</code> — safe for re-approvals after edit)</td></tr>
                                <tr><td>Leave disapproved / reverted to pending</td><td>DELETE all rows linked to this leave request via <code>leave_id</code> FK</td></tr>
                                <tr><td>Status changed away from approved (edit)</td><td>DELETE first, then re-write if new status is approved</td></tr>
                            </table>
                            <p><strong>Payroll impact values written to <code>staff_day_status.payroll_impact</code>:</strong></p>
                            <table>
                                <tr><th>Leave Type</th><th>payroll_impact</th><th>Payroll Effect</th><th>Attendance Report</th></tr>
                                <tr>
                                    <td>OD, HOD</td>
                                    <td><span class="impact-badge impact-present">PAID_PRESENT</span></td>
                                    <td>Day counted as present. No LOP. No credit consumed. Biometric ignored.</td>
                                    <td>Cell shows <code>OD</code> or <code>HOD</code> instead of P/A</td>
                                </tr>
                                <tr>
                                    <td>CPL</td>
                                    <td><span class="impact-badge impact-absent">PAID_ABSENT</span></td>
                                    <td>Day counted as absent. HOD credit absorbs the LOP. If HOD balance = 0, becomes regular LOP. Biometric ignored.</td>
                                    <td>Cell shows <code>CPL</code></td>
                                </tr>
                                <tr>
                                    <td>LOP-type with day lock (rare)</td>
                                    <td><span class="impact-badge impact-lop">LOP</span></td>
                                    <td>Day is LOP regardless of punch.</td>
                                    <td>Cell shows leave type label</td>
                                </tr>
                            </table>
                            <p><em>Half-day leaves</em>: status label gets prefix <code>FH-</code> or <code>SH-</code> (e.g. <code>FH-OD</code>, <code>SH-CPL</code>). Credit amount = <code>leave_days</code> value on the request (0.5).</p>

                            <h4>HOD → CPL Credit Flow (Holiday Worked)</h4>
                            <div class="flow-box">
                                <strong>Step 1 — Staff works on holiday:</strong> Staff submits an <strong>HOD</strong> leave request for the holiday date.<br>
                                <strong>Step 2 — HR approves HOD:</strong> <code>logLeaveApprovalCredit()</code> runs → <code>staff_monthly_leave_balance</code> for HOD gains <code>earned_in_month += N days</code>. Day-lock written as <code>PAID_PRESENT</code> (attendance report shows <code>HOD</code> on holiday).<br>
                                <strong>Step 3 — Staff applies CPL:</strong> System calls <code>getAvailableCreditPoolBalance(staff_id, HOD_type_id)</code> → returns Σ approved HOD days − Σ consumed CPL days. This is the staff's available CPL balance.<br>
                                <strong>Step 4 — HR approves CPL:</strong> <code>logLeaveApprovalCredit()</code> runs the <code>is_credit_consumer</code> branch → debits HOD's <code>staff_monthly_leave_balance</code> (reduces <code>closing_balance</code> and increments <code>used_for_leave_application</code>). Day-lock written as <code>PAID_ABSENT</code>. Payroll sees PAID_ABSENT → no LOP.<br>
                                <strong>Audit records:</strong> <code>LEAVE_APPROVED_CREDIT</code> (HOD earn), <code>CREDIT_POOL_DEBIT</code> (CPL consume).
                            </div>

                            <h4>HOD Revert — Block Rule</h4>
                            <p>
                                <code>revertLeave()</code> checks: if the leave being reverted is HOD-type, it queries <code>staff_leave_request</code> for any <em>approved</em> CPL records for that staff.
                                If found → revert is <strong>blocked</strong> with an error message. HR must disapprove those CPL leaves first, which restores the HOD balance, then revert the HOD.
                                This prevents the HOD balance going negative (credit already spent cannot be reclaimed unilaterally).
                            </p>

                            <h4>OD Credit Flow (Working Day Off-Campus)</h4>
                            <div class="flow-box">
                                <strong>OD approved:</strong> <code>logLeaveApprovalCredit()</code> → credits OD's own <code>staff_monthly_leave_balance.earned_in_month</code>. Day-lock written as <code>PAID_PRESENT</code>. Attendance shows <code>OD</code> instead of biometric punch.<br>
                                <strong>Payroll:</strong> <code>getAbsentWorkingDayCount()</code> excludes PAID_PRESENT locked dates from the absent count → no LOP for those days.<br>
                                <strong>OD balance usage:</strong> OD balance is currently not linked to CPL. OD is a standalone earned credit (used for working day duty claims only). CPL is fed exclusively by HOD.
                            </div>

                            <h4>Payroll LOP Calculation Flow</h4>
                            <p>Entry: <code>Payroll.php::getAbsentWorkingDayCount()</code> and <code>getPaidLeaveAbsentCountRange()</code></p>
                            <ol>
                                <li><code>getAbsentWorkingDayCount()</code> — queries biometric for absent days in the pay period. For each working day with a <code>PAID_PRESENT</code> day-lock, that day is subtracted from the absent count (OD / HOD days on working days are treated as present).</li>
                                <li><code>getPaidLeaveAbsentCountRange()</code> — for each date in the period, checks <code>staff_day_status</code>: if <code>PAID_ABSENT</code> (CPL day), the day is counted as paid-absent (credit absorbed, no LOP) without consulting biometric.</li>
                                <li>Net LOP = Raw absent days − PAID_PRESENT offsets − PAID_ABSENT (credit-absorbed) days − standard CL/ML deductions</li>
                                <li>Salary deduction = Net LOP × (monthly_salary ÷ total_working_days)</li>
                            </ol>

                            <h4>Attendance Report Overlay</h4>
                            <p>
                                <code>Attendencereports.php::staffattendancereport()</code> calls <code>getDayStatusRangeMultiStaff()</code> in batch after the biometric punch derivation loop.
                                Any day with a lock overwrites <code>$date_result[date][staff_id]['key']</code> with the lock status label.
                                The summary counters then correctly add to <code>present_equivalent</code> (PAID_PRESENT) or <code>absent_equivalent</code> (PAID_ABSENT) instead of falling into the raw biometric branch.
                                <code>absent_working_day_counts</code> (LOP-relevant) only increments when <code>key === 'A'</code> — locked days never match, so no LOP is generated from them.
                            </p>

                            <h4>Key DB Tables</h4>
                            <table>
                                <tr><th>Table</th><th>Purpose</th></tr>
                                <tr><td><code>leave_types</code></td><td>Leave type master — all flags described above. <code>credit_source_type_id</code>: CPL → HOD id</td></tr>
                                <tr><td><code>staff_leave_request</code></td><td>Individual leave applications (leave_from, leave_to, leave_days, leave_duration_type, leave_direction)</td></tr>
                                <tr><td><code>staff_monthly_leave_balance</code></td><td>Per-staff per-type per-month balance ledger (opening, earned, used_lop, used_leave, closing)</td></tr>
                                <tr><td><code>staff_leave_balance_audit</code></td><td>Full audit log: LEAVE_APPROVED_CREDIT, CREDIT_POOL_DEBIT, LEAVE_APPROVAL_REVERTED</td></tr>
                                <tr><td><code>staff_day_status</code></td><td>Day-lock records; UNIQUE per (staff_id, date). Payroll and report read this first.</td></tr>
                                <tr><td><code>staff_leave_details</code></td><td>HR-allotted leave balances (used for CL/ML balance checks)</td></tr>
                                <tr><td><code>staff_attendance</code></td><td>Raw biometric punches per staff per date (ignored for day-locked dates)</td></tr>
                            </table>

                            <h4>Key Code Locations</h4>
                            <table>
                                <tr><th>File</th><th>Function</th><th>Role</th></tr>
                                <tr><td><code>controllers/admin/Leaverequest.php</code></td><td><code>leaveStatus()</code></td><td>Approval / disapproval hook — calls deleteDayLock() always, writeDayLock() if approved, logLeaveApprovalCredit() if approved</td></tr>
                                <tr><td><code>controllers/admin/Leaverequest.php</code></td><td><code>revertLeave()</code></td><td>Blocked for HOD if CPL is consumed. Otherwise: revertLeaveApproval() + deleteDayLock() + reset to pending</td></tr>
                                <tr><td><code>models/Day_status_model.php</code></td><td><code>writeDayLock()</code></td><td>Writes staff_day_status rows; resolves payroll_impact and status label from leave type flags</td></tr>
                                <tr><td><code>models/Day_status_model.php</code></td><td><code>deleteDayLock()</code></td><td>Deletes all staff_day_status rows for a leave_id</td></tr>
                                <tr><td><code>models/Day_status_model.php</code></td><td><code>getDayStatusRange()</code></td><td>Single-staff date-range lookup (payroll, staff profile)</td></tr>
                                <tr><td><code>models/Day_status_model.php</code></td><td><code>getDayStatusRangeMultiStaff()</code></td><td>Batch multi-staff lookup (attendance report)</td></tr>
                                <tr><td><code>controllers/admin/Payroll.php</code></td><td><code>getAbsentWorkingDayCount()</code></td><td>Excludes PAID_PRESENT locked days from absent count</td></tr>
                                <tr><td><code>controllers/admin/Payroll.php</code></td><td><code>getPaidLeaveAbsentCountRange()</code></td><td>Counts PAID_ABSENT locked days as credit-absorbed (no LOP)</td></tr>
                                <tr><td><code>controllers/Attendencereports.php</code></td><td><code>staffattendancereport()</code></td><td>Overlays day-lock labels; fixes present/absent summary counts for locked days</td></tr>
                                <tr><td><code>models/Leaverequest_model.php</code></td><td><code>logLeaveApprovalCredit()</code></td><td>On approval: credits earner types (OD, HOD); debits HOD pool for CPL (is_credit_consumer path)</td></tr>
                                <tr><td><code>models/Leaverequest_model.php</code></td><td><code>revertLeaveApproval()</code></td><td>Reverses credits/debits; restores HOD pool when CPL is reverted</td></tr>
                                <tr><td><code>models/Leaverequest_model.php</code></td><td><code>getAvailableCreditPoolBalance()</code></td><td>CPL available = Σ approved HOD days − Σ non-disapproved CPL days</td></tr>
                            </table>

                        </div><!-- /.devdoc -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>
    </section>
</div>
<div id="applyLeaveModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('apply_leave_to_all_staff'); ?></h4>
            </div>
            <form id="applyLeaveForm" method="post" action="<?php echo site_url('admin/leavetypes/applyLeaveToAll'); ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="days"><?php echo $this->lang->line('number_of_days'); ?></label>
                        <input type="number" class="form-control" id="days" name="days" required>
                        <input type="hidden" id="leave_type_id" name="leave_type_id">
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" id="overwrite" name="overwrite" value="1"> <?php echo $this->lang->line('overwrite_existing_leave_days_that_are_not_zero'); ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#is_carry_forward').change(function () {
            if (this.checked) {
                $('#max_carry_forward_group').show();
            } else {
                $('#max_carry_forward_group').hide();
            }
        });

        $('.apply_to_all').click(function () {
            var leave_type_id = $(this).data('leave-type-id');
            $('#leave_type_id').val(leave_type_id);
            $('#applyLeaveModal').modal('show');
        });

        $('#applyLeaveForm').submit(function (e) {
            e.preventDefault();
            var form = $(this);
            var overwrite = form.find('#overwrite').is(':checked');
            var confirmation_message = overwrite ? "<?php echo $this->lang->line('are_you_sure_you_want_to_overwrite_existing_leave_days'); ?>" : "<?php echo $this->lang->line('you_are_applying_this_leave_type_with_given_days_to_all_the_employee_those_who_are_not_having_value'); ?>";

            if (confirm(confirmation_message)) {
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            successMsg(response.message);
                            $('#applyLeaveModal').modal('hide');
                        } else {
                            errorMsg(response.message);
                        }
                    },
                    error: function () {
                        errorMsg('<?php echo $this->lang->line('an_error_occurred'); ?>');
                    }
                });
            }
        });
    });
</script>

