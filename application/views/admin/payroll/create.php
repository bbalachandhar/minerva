<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
    .payroll-summary-card {
        background: #f8f9fb;
        border: none;
        border-radius: 6px;
        padding: 12px 14px;
        margin-bottom: 8px;
    }
    .payroll-summary-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .payroll-summary-value {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
    }
    .payroll-summary-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .payroll-summary-row .payroll-summary-card {
        flex: 1 1 180px;
    }
</style>
<div class="content-wrapper" style="min-height: 393px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <div class="row">
                            <div class="col-md-4">
                                <h3 class="box-title">Payroll Summary</h3>
                            </div>
                            <div class="col-md-8 ">
                                <div class="btn-group pull-right">
                                    <a href="<?php echo base_url() ?>admin/payroll" type="button" class="btn btn-primary btn-xs">
                                        <i class="fa fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--./box-header-->
                    <div class="box-body" style="padding-top:0;">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="sfborder" style="padding: 10px 12px;">
                                    <div class="col-md-2">
                                        <div class="row">
                                            <?php
$image = $result['image'];
if (!empty($image)) {

    $file = $result['image'];
} else {

    $file = "no_image.png";
}
$image=$this->media_storage->getImageURL("uploads/staff_images/" . $file);
?>
    <img width="115" height="115" class="round5" src="<?php echo $image ?>" alt="No Image">
                                        </div>
                                    </div>

                                    <div class="col-md-10">
                                        <div class="row">
                                            <table class="table mb0 font13">
                                                <tbody>
                                                    <tr>
                                                        <th class="bozero"><?php echo $this->lang->line("name"); ?></th>
                                                        <td class="bozero"><?php echo $result["name"] . " " . $result["surname"] ?></td>
                                                        <th class="bozero"><?php echo $this->lang->line('staff_id'); ?></th>
                                                        <td class="bozero"><?php echo $result["employee_id"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <?php if ($sch_setting->staff_phone) {?>
                                                            <th><?php echo $this->lang->line('phone'); ?></th>
                                                        <?php }?>
                                                        <td><?php echo $result["contact_no"] ?></td>
                                                        <th><?php echo $this->lang->line('email'); ?></th>
                                                        <td><?php echo $result["email"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $this->lang->line('uan_no') ?: 'UAN No.'; ?></th>
                                                        <td><?php echo isset($result['uan_no']) ? $result['uan_no'] : ''; ?></td>
                                                        <th><?php echo $this->lang->line('role'); ?></th>
                                                        <td><?php echo $result["user_type"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th><?php echo $this->lang->line('esi_no') ?: ($this->lang->line('epf_no') ?: 'ESI No.'); ?></th>
                                                        <td><?php echo isset($result['esi_no']) ? $result['esi_no'] : ''; ?></td>
                                                        <th><?php echo $this->lang->line('role'); ?></th>
                                                        <td>
                                                            <strong>Statutory Deductions Status:</strong>
                                                            <i class="fa fa-info-circle text-muted" style="font-size: 11px; margin-left: 4px;" data-toggle="tooltip" title="Badge marking: If ESI is No and this month Gross Salary is ≤ ₹21,000, it auto-updates to Yes. Once Yes, it remains Yes."></i>
                                                            <br>
                                                            <?php
                                                            $epf_is_active = (!empty($result['uan_no']) && isset($result['is_epf_enabled']) && (int) $result['is_epf_enabled'] === 1);
                                                            $esi_is_active = (isset($result['is_esi_enabled']) && (int) $result['is_esi_enabled'] === 1);
                                                            $epf_status = $epf_is_active
                                                                ? '<span id="epf_status_badge" data-state="yes" style="color: #28a745;">✓ EPF Active</span>'
                                                                : '<span id="epf_status_badge" data-state="no" style="color: #dc3545;">✗ EPF Inactive</span>';
                                                            $esi_status = $esi_is_active
                                                                ? '<span id="esi_status_badge" data-state="yes" style="color: #28a745;">✓ ESI Active</span>'
                                                                : '<span id="esi_status_badge" data-state="no" style="color: #dc3545;">✗ ESI Inactive</span>';
                                                            echo $epf_status . ' | ' . $esi_status;
                                                            ?>
                                                            <div style="font-size:11px;color:#666;margin-top:4px;">
                                                                ESI applies only when Gross Salary ≤ ₹21,000 (ESI Wage = Gross - LOP).
                                                            </div>
                                                            <div style="font-size:11px;color:#666;margin-top:2px;">
                                                                Badge marking: if ESI is currently No and this month Gross Salary is ≤ ₹21,000, it auto-updates to Yes for payroll processing.
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <?php if ($sch_setting->staff_department) {?>
                                                            <th><?php echo $this->lang->line('department'); ?></th>
                                                            <td><?php echo $result["department"] ?></td>
                                                        <?php } else { ?>
                                                            <th>&nbsp;</th>
                                                            <td>&nbsp;</td>
                                                        <?php }
                                                        if ($sch_setting->staff_designation) {?>
                                                            <th><?php echo $this->lang->line('designation'); ?></th>
                                                            <td><?php echo $result["designation"] ?>   </td>
                                                        <?php } else { ?>
                                                            <th>&nbsp;</th>
                                                            <td>&nbsp;</td>
                                                        <?php }?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="sfborder relative overvisible">
                                    <div class="letest">
                                        <div class="rotatetest"><?php echo $this->lang->line("attendance") ?></div>
                                    </div>
                                    <div class="padd-en-rtl33">
                                        <div class="text-muted" style="font-size:11px; margin-bottom:6px;">
                                            P*: Present incl. half-day and approved paid leave on absent days. A*: Absent incl. half-day and late/permission penalty beyond limits. L: Late count. PR: Permission count. APR: Approved paid leaves. LOP: Loss Of Pay days.
                                        </div>
                                        <table class="table mb0 font13" >
                                            <thead>
                                            <tr>
                                                <th  class="bozero"><?php echo $this->lang->line('month'); ?></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Total Working Days">WD</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Total Present (Including Half Day)">P*</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Total Absent (Including Half Day)">A*</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Half Day">HD</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Total Holidays">H</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Total Late">L</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Total Permissions">PR</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Approved Paid Leaves">APR</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Loss Of Pay Days">LOP</span></th>
                                                <th class="bozero"><span data-toggle="tooltip" title="Weekends">WE</span></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
$lop_rules = $this->config->item('lop_rules');
$half_day_weight = isset($lop_rules['half_day_weight']) ? (float) $lop_rules['half_day_weight'] : 0.5;
$max_late_allowed = isset($sch_setting->max_late_allowed) ? (int) $sch_setting->max_late_allowed : 0;
$max_permission_allowed = isset($sch_setting->max_permission_allowed) ? (int) $sch_setting->max_permission_allowed : 0;
foreach ($monthAttendance as $attendence_key => $attendence_value) {
    $present = (int) ($attendence_value['present'] ?? 0);
    $half_day = (int) ($attendence_value['half_day'] ?? 0);
    $total_present = $present + ($half_day * $half_day_weight);
    $total_absent = $month_absent_total[$attendence_key] ?? 0;
    $holiday_count = (int) ($attendence_value['holiday'] ?? 0);
    $late_count = (int) ($attendence_value['late'] ?? 0);
    $permission_count = (int) ($attendence_value['first_half_permission'] ?? 0) + (int) ($attendence_value['second_half_permission'] ?? 0);
    $approved_leave = $monthLeaves[date("m", strtotime($attendence_key))] ?? 0;
    $weekend_count = (int) ($attendence_value['sunday'] ?? 0);
    $days_in_period = (int) ($attendence_value['days_in_period'] ?? 0);
    if ($days_in_period <= 0) {
        $days_in_period = cal_days_in_month(CAL_GREGORIAN, (int) date("m", strtotime($attendence_key)), (int) date("Y", strtotime($attendence_key)));
    }
    $working_days = (int) ($attendence_value['working_days'] ?? 0);
    if ($working_days <= 0) {
        $working_days = max(0, $days_in_period - $holiday_count - $weekend_count);
    }
    $first_half_absent = (int) ($attendence_value['first_half_absent'] ?? 0);
    $second_half_absent = (int) ($attendence_value['second_half_absent'] ?? 0);
    $paid_leave_absent = $month_paid_leave_absent[$attendence_key] ?? 0;

    $excess_late = $late_count > $max_late_allowed ? ($late_count - $max_late_allowed) : 0;
    $excess_permission = $permission_count > $max_permission_allowed ? ($permission_count - $max_permission_allowed) : 0;
    $late_permission_penalty = ($excess_late + $excess_permission) * $half_day_weight;
    $total_present = max(0, $total_present - $late_permission_penalty + $paid_leave_absent);
    $total_absent = $total_absent + $late_permission_penalty;
    $lop_days = $total_absent + (($first_half_absent + $second_half_absent) * $half_day_weight);

    $month_num = date('m', strtotime($attendence_key));
    $year_num = date('Y', strtotime($attendence_key));
    $month_name = date('F', strtotime($attendence_key));
    $is_current_payroll_month = (($month_name == $month || $month_num == $month) && $year_num == $year);

    if ($is_current_payroll_month && !empty($payroll_lop_summary)) {
        if (isset($payroll_lop_summary['working_days'])) {
            $working_days = (int) $payroll_lop_summary['working_days'];
        }
        if (isset($payroll_lop_summary['paid_days'])) {
            $total_present = (float) $payroll_lop_summary['paid_days'];
        }
        if (isset($payroll_lop_summary['absent'])) {
            $total_absent = (float) $payroll_lop_summary['absent'];
        }
        if (isset($payroll_lop_summary['half_day'])) {
            $half_day = (int) $payroll_lop_summary['half_day'];
        }
        if (isset($payroll_lop_summary['holidays'])) {
            $holiday_count = (int) $payroll_lop_summary['holidays'];
        }
        if (isset($payroll_lop_summary['sundays'])) {
            $weekend_count = (int) $payroll_lop_summary['sundays'];
        }
        if (isset($payroll_lop_summary['approved_leave'])) {
            $approved_leave = (int) $payroll_lop_summary['approved_leave'];
        }
        if (isset($payroll_lop_summary['first_half_absent'])) {
            $first_half_absent = (int) $payroll_lop_summary['first_half_absent'];
        }
        if (isset($payroll_lop_summary['second_half_absent'])) {
            $second_half_absent = (int) $payroll_lop_summary['second_half_absent'];
        }
        if (isset($payroll_lop_summary['late'])) {
            $late_count = (int) $payroll_lop_summary['late'];
        }
        if (isset($payroll_lop_summary['first_half_permission']) || isset($payroll_lop_summary['second_half_permission'])) {
            $permission_count = (int) ($payroll_lop_summary['first_half_permission'] ?? 0) + (int) ($payroll_lop_summary['second_half_permission'] ?? 0);
        }
        if (isset($payroll_lop_summary['lop_days'])) {
            $lop_days = (float) $payroll_lop_summary['lop_days'];
        }
    }
    ?>
                                                <tr>
                                                    <td><?php echo $this->lang->line(strtolower(date("F", strtotime($attendence_key)))); ?></td>
                                                    <td><?php echo $working_days; ?></td>
                                                    <td><?php echo rtrim(rtrim(number_format((float) $total_present, 1, '.', ''), '0'), '.'); ?></td>
                                                    <td><?php echo rtrim(rtrim(number_format((float) $total_absent, 1, '.', ''), '0'), '.'); ?></td>
                                                    <td><?php echo $half_day; ?></td>
                                                    <td><?php echo $holiday_count; ?></td>
                                                    <td><?php echo $late_count; ?></td>
                                                    <td><?php echo $permission_count; ?></td>
                                                    <td><?php echo $approved_leave; ?></td>
                                                    <td><?php echo rtrim(rtrim(number_format((float) $lop_days, 1, '.', ''), '0'), '.'); ?></td>
                                                    <td><?php echo $weekend_count; ?></td>
                                                </tr>
                                                <?php
}
?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!--./col-md-8-->
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <form class="form-horizontal" action="<?php echo site_url('admin/payroll/payslip') ?>" method="post"  id="employeeform">
                        <div class="box-header">
                            <div class="row display-flex">
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title"><?php echo $this->lang->line('earning'); ?></h3>
                                    <?php if(!$is_calculated): ?>
                                        <button type="button" onclick="add_more()" class="plusign"><i class="fa fa-plus"></i></button>
                                    <?php endif; ?>
                                                                         <div class="sameheight">
                                                                            <div class="feebox">
                                                                                <table class="table3" id="tableID">
                                                                                    <?php
                                    if (!empty($earnings)) {
                                        $count = 1;
                                        foreach ($earnings as $earning) {
                                            ?>
                                                                                            <tr id="row<?php echo $count; ?>">
                                                                                                <td>
                                                                                                    <?php if($is_calculated): ?>
                                                                                                        <!-- Read-only display for calculated payslip -->
                                                                                                        <input type="hidden" name="allowance_type_id[]" value="<?php echo !empty($earning['allowance_type_id']) ? $earning['allowance_type_id'] : ''; ?>">
                                                                                                        <div class="form-control" style="background-color: #f5f5f5; border: none; cursor: not-allowed;">
                                                                                                            <?php 
                                                                                                                // Show the allowance type name, with fallback to code if name is null
                                                                                                                $type_name = !empty($earning['allowance_type_name']) ? $earning['allowance_type_name'] : ucfirst(strtolower($earning['allowance_type']));
                                                                                                                $type_code = !empty($earning['allowance_code']) ? $earning['allowance_code'] : $earning['allowance_type'];
                                                                                                                echo $type_name . ' (' . $type_code . ')';
                                                                                                            ?>
                                                                                                        </div>
                                                                                                    <?php else: ?>
                                                                                                        <!-- Editable dropdown for new payslip -->
                                                                                                        <select class="form-control" name="allowance_type_id[]" id="allowance_type_<?php echo $count; ?>">
                                                                                                            <option value=""><?php echo $this->lang->line('type'); ?></option>
                                                                                                            <?php foreach($earning_types as $type): ?>
                                                                                                                <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>"
                                                                                                                    <?php echo ($type['id'] == $earning['allowance_type_id']) ? 'selected' : ''; ?>>
                                                                                                                    <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                                                                                </option>
                                                                                                            <?php endforeach; ?>
                                                                                                        </select>
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <?php if($is_calculated): ?>
                                                                                                        <!-- Read-only display for amount -->
                                                                                                        <div class="form-control" style="background-color: #f5f5f5; border: none; cursor: not-allowed; text-align: right;">
                                                                                                            <?php echo number_format($earning['amount'], 2); ?>
                                                                                                        </div>
                                                                                                        <input type="hidden" name="allowance_amount[]" value="<?php echo $earning['amount']; ?>">
                                                                                                    <?php else: ?>
                                                                                                        <input type="text" name="allowance_amount[]" id="allowance_amount_<?php echo $count; ?>" class="form-control" value="<?php echo $earning['amount']; ?>">
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <?php if(!$is_calculated): ?>
                                                                                                        <button type="button" onclick="delete_row(<?php echo $count; ?>)" class="closebtn"><i class="fa fa-remove"></i></button>
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <?php
                                    $count++;
                                        }
                                    } else {
                                        ?>
                                                                                        <tr id="row0">
                                                                                            <td>
                                                                                                <select class="form-control" id="allowance_type" name="allowance_type_id[]">
                                                                                                    <option value=""><?php echo $this->lang->line('type'); ?></option>
                                                                                                    <?php foreach($earning_types as $type): ?>
                                                                                                        <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>">
                                                                                                            <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                                                                        </option>
                                                                                                    <?php endforeach; ?>
                                                                                                </select>
                                                                                            </td>
                                                                                            <td><input type="text" id="allowance_amount" name="allowance_amount[]" class="form-control" value="0"></td>
                                    
                                                                                        </tr>
                                                                                    <?php }
                                    ?>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div><!--./col-md-4-->
                                                                    <div class="col-md-4 col-sm-4">
                                                                        <h3 class="box-title"><?php echo $this->lang->line('deduction'); ?></h3>
                                                                        <?php if(!$is_calculated): ?>
                                                                            <button type="button" onclick="add_more_deduction()" class="plusign"><i class="fa fa-plus"></i></button>
                                                                        <?php endif; ?>
                                                                        <div class="sameheight">
                                                                            <div class="feebox">
                                                                                <table class="table3" id="tableID2">
                                                                                    <?php
                                    if (!empty($deductions)) {
                                        $count = 1;
                                        foreach ($deductions as $deduction) {
                                            ?>
                                                                                            <tr id="deduction_row<?php echo $count; ?>">
                                                                                                <td>
                                                                                                    <?php if($is_calculated): ?>
                                                                                                        <!-- Read-only display for calculated payslip -->
                                                                                                        <input type="hidden" name="deduction_type_id[]" value="<?php echo !empty($deduction['allowance_type_id']) ? $deduction['allowance_type_id'] : ''; ?>">
                                                                                                        <div class="form-control" style="background-color: #f5f5f5; border: none; cursor: not-allowed;">
                                                                                                            <?php 
                                                                                                                // Show the allowance type name, with fallback to code if name is null
                                                                                                                $type_name = !empty($deduction['allowance_type_name']) ? $deduction['allowance_type_name'] : ucfirst(strtolower($deduction['allowance_type']));
                                                                                                                $type_code = !empty($deduction['allowance_code']) ? $deduction['allowance_code'] : $deduction['allowance_type'];
                                                                                                                echo $type_name . ' (' . $type_code . ')';
                                                                                                            ?>
                                                                                                        </div>
                                                                                                    <?php else: ?>
                                                                                                        <!-- Editable dropdown for new payslip -->
                                                                                                        <select class="form-control" id="deduction_type_<?php echo $count; ?>" name="deduction_type_id[]">
                                                                                                            <option value=""><?php echo $this->lang->line('type'); ?></option>
                                                                                                            <?php foreach($deduction_types as $type): ?>
                                                                                                                <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>"
                                                                                                                    <?php echo ($type['id'] == $deduction['allowance_type_id']) ? 'selected' : ''; ?>>
                                                                                                                    <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                                                                                </option>
                                                                                                            <?php endforeach; ?>
                                                                                                        </select>
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                                <td>
                                                                                                    <?php if($is_calculated): ?>
                                                                                                        <!-- Read-only display for amount -->
                                                                                                        <div class="form-control" style="background-color: #f5f5f5; border: none; cursor: not-allowed; text-align: right;">
                                                                                                            <?php echo number_format($deduction['amount'], 2); ?>
                                                                                                        </div>
                                                                                                        <input type="hidden" name="deduction_amount[]" value="<?php echo $deduction['amount']; ?>">
                                                                                                    <?php else: ?>
                                                                                                        <input type="text" id="deduction_amount_<?php echo $count; ?>" name="deduction_amount[]" class="form-control" value="<?php echo $deduction['amount']; ?>">
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                                 <td>
                                                                                                    <?php if(!$is_calculated): ?>
                                                                                                        <button type="button" onclick="delete_deduction_row(<?php echo $count; ?>)" class="closebtn"><i class="fa fa-remove"></i></button>
                                                                                                    <?php endif; ?>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <?php
                                    $count++;
                                        }
                                    } else {
                                        ?>
                                                                                     <tr id="deduction_row0">
                                                                                        <td>
                                                                                            <select class="form-control" id="deduction_type" name="deduction_type_id[]">
                                                                                                <option value=""><?php echo $this->lang->line('type'); ?></option>
                                                                                                <?php foreach($deduction_types as $type): ?>
                                                                                                    <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>">
                                                                                                        <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                                                                    </option>
                                                                                                <?php endforeach; ?>
                                                                                            </select>
                                                                                        </td>
                                                                                        <td><input type="text" id="deduction_amount" name="deduction_amount[]" class="form-control" value="0"></td>
                                                                                    </tr>
                                                                                    <?php
                                    }
                                    ?>
                                                                                </table>                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title"><?php echo $this->lang->line('payroll_summary'); ?> (<?php echo $currency_symbol ?>)</h3>
                                    <button type="button" onclick="add_allowance()" class="plusign"><i class="fa fa-calculator"></i> <?php echo $this->lang->line('calculate'); ?></button>
                                    <div class="sameheight">
                                        <div class="payrollbox feebox">
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label">
                                                    <?php echo $this->lang->line('basic_salary'); ?>
                                                    <small class="text-muted" style="font-size: 10px; display: block;">(This month's basic - may differ from contract)</small>
                                                </label>
                                                <div class="col-sm-8">
                                                    <input class="form-control" name="basic" value="<?php
if (!empty($result["basic_salary"])) {
    echo $result["basic_salary"];
} else {
    echo "0";
}
?>" id="basic"  type="text" />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('earning'); ?></label>
                                                <div class="col-sm-8">
                                                    <input class="form-control" name="total_allowance" id="total_allowance"  type="text" />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('gross_salary'); ?></label>
                                                <div class="col-sm-8">
                                                    <input class="form-control" name="gross_salary" id="gross_salary" value="0" type="text" />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('deduction'); ?></label>
                                                <div class="col-sm-8 deductiondred">
                                                    <input class="form-control" name="total_deduction" id="total_deduction" type="text" style="color:#f50000" />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label">LOP</label>
                                                <div class="col-sm-8 deductiondred">
                                                    <input class="form-control" name="leave_deduction" id="lop_deduction" type="text" style="color:#f50000" value="0" readonly />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('tax'); ?></label>
                                                <div class="col-sm-8 deductiondred">
                                                    <input class="form-control" name="tax" id="tax" value="0" type="text" />
                                                </div>
                                            </div><!--./form-group-->
                                            <hr/>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('net_salary'); ?></label>
                                                <div class="col-sm-8 net_green">
                                                    <input class="form-control greentest"  name="net_salary" id="net_salary"  type="text" />
                                                    <span class="text-danger" id="err"><?php echo form_error('net_salary'); ?></span>

                                                    <input class="form-control" name="staff_id" value="<?php echo $result["id"]; ?>"  type="hidden" />
                                                    <input class="form-control" name="month" value="<?php echo $month; ?>"  type="hidden" />
                                                    <input class="form-control" name="year" value="<?php echo $year; ?>"  type="hidden" />
                                                    <input class="form-control" name="status" value="generated"  type="hidden" />
                                                    <input class="form-control" id="working_days" value="<?php echo $payroll_lop_summary['working_days']; ?>" type="hidden" />
                                                    <input class="form-control" id="paid_days" value="<?php echo $payroll_lop_summary['paid_days']; ?>" type="hidden" />
                                                    <input class="form-control" id="lop_days" value="<?php echo $payroll_lop_summary['lop_days']; ?>" type="hidden" />
                                                </div>
                                            </div><!--./form-group-->
                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-12 col-sm-12">
                                    <?php if($is_calculated): ?>
                                        <button type="button" class="btn btn-secondary pull-right mt25" disabled><i class="fa fa-eye"></i> <?php echo 'Payslip Calculated'; ?></button>
                                        <a href="<?php echo base_url() ?>admin/payroll" class="btn btn-default pull-right mt25" style="margin-right: 10px;"><i class="fa fa-arrow-left"></i> Back</a>
                                    <?php else: ?>
                                        <button type="submit" id="contact_submit" class="btn btn-info pull-right mt25"><?php echo $this->lang->line('save'); ?></button>
                                    <?php endif; ?>
                                </div><!--./col-md-12-->
                                </form>
                            </div><!--./row-->
                        </div><!--./box-header-->
                </div>
            </div>
            <!--/.col (left) -->
        </div>
    </section>
</div>

<script type="text/javascript">
    // Convert PHP arrays to JavaScript for dynamic row generation
    var earning_types = <?php echo json_encode($earning_types); ?>;
    var deduction_types = <?php echo json_encode($deduction_types); ?>;

    function findBasicPayRow() {
        var allowance_type = document.getElementsByName('allowance_type_id[]');
        var allowance_amount = document.getElementsByName('allowance_amount[]');
        for (var i = 0; i < allowance_type.length; i++) {
            var selected = allowance_type[i].options[allowance_type[i].selectedIndex];
            var code = (selected && selected.getAttribute('data-code')) ? selected.getAttribute('data-code').toUpperCase() : '';
            if (code === 'BASIC') {
                return { typeEl: allowance_type[i], amountEl: allowance_amount[i] };
            }
        }
        return null;
    }

    function syncBasicFromEarnings() {
        var basicRow = findBasicPayRow();
        if (basicRow && basicRow.amountEl.value !== '') {
            $("#basic").val(basicRow.amountEl.value);
        }
    }

    function syncEarningsFromBasic() {
        var basicRow = findBasicPayRow();
        if (basicRow) {
            basicRow.amountEl.value = $("#basic").val();
        }
    }

    function updateStatutoryBadgesFromGross() {
        var epfBadge = $("#epf_status_badge");
        var esiBadge = $("#esi_status_badge");
        if (!epfBadge.length && !esiBadge.length) {
            return;
        }

        var gross = parseFloat($("#gross_salary").val()) || 0;
        var epfEligible = gross > 0;
        var esiEligible = gross > 0 && gross <= 21000;

        if (epfBadge.length) {
            epfBadge
                .data("state", epfEligible ? "yes" : "no")
                .attr("data-state", epfEligible ? "yes" : "no")
                .css("color", epfEligible ? "#28a745" : "#dc3545")
                .text(epfEligible ? "✓ EPF Active" : "✗ EPF Inactive");
        }

        if (esiBadge.length) {
            esiBadge
                .data("state", esiEligible ? "yes" : "no")
                .attr("data-state", esiEligible ? "yes" : "no")
                .css("color", esiEligible ? "#28a745" : "#dc3545")
                .text(esiEligible ? "✓ ESI Active" : "✗ ESI Inactive");
        }
    }

    function add_allowance() {
        syncBasicFromEarnings();
        var basic_pay = $("#basic").val();
        var allowance_type = document.getElementsByName('allowance_type_id[]');
        var allowance_amount = document.getElementsByName('allowance_amount[]');
        var tax = $("#tax").val();
        if (tax == '') {
            var tax = 0;
        }

        var total_allowance = 0;
        var deduction_type = document.getElementsByName('deduction_type_id[]');
        var deduction_amount = document.getElementsByName('deduction_amount[]');
        var total_deduction = 0;
        for (var i = 0; i < allowance_amount.length; i++) {
            var inp = allowance_amount[i];
            if (inp.value == '') {
                var inpvalue = 0;
            } else {
                var inpvalue = inp.value;
            }

            var selected = allowance_type[i].options[allowance_type[i].selectedIndex];
            var code = (selected && selected.getAttribute('data-code')) ? selected.getAttribute('data-code').toUpperCase() : '';
            if (!allowance_type[i].value || code === 'BASIC') {
                continue;
            }
            total_allowance += parseFloat(inpvalue);
        }

        for (var j = 0; j < deduction_amount.length; j++) {
            var inpd = deduction_amount[j];
            if (inpd.value == '') {
                var inpdvalue = 0;
            } else {
                var inpdvalue = inpd.value;
            }
            if (!deduction_type[j].value) {
                continue;
            }
            total_deduction += parseFloat(inpdvalue);
        }

        var working_days = parseFloat($("#working_days").val()) || 0;
        var lop_days = parseFloat($("#lop_days").val()) || 0;

        var gross_salary = parseFloat(basic_pay) + parseFloat(total_allowance);
        var lop_deduction = 0;
        if (working_days > 0 && lop_days > 0) {
            lop_deduction = (gross_salary / working_days) * lop_days;
        }

        var net_salary = gross_salary - parseFloat(total_deduction) - lop_deduction - parseFloat(tax);

        syncEarningsFromBasic();
        $("#total_allowance").val(total_allowance.toFixed(2));
        $("#total_deduction").val(total_deduction.toFixed(2));
        $("#total_allow").html(total_allowance.toFixed(2));
        $("#total_deduc").html(total_deduction.toFixed(2));
        $("#gross_salary").val(gross_salary.toFixed(2));
        $("#lop_deduction").val(lop_deduction.toFixed(2));
        $("#net_salary").val(net_salary.toFixed(2));
        updateStatutoryBadgesFromGross();
    }

    function add_more() {

        var table = document.getElementById("tableID");
        var table_len = (table.rows.length);
        var id = parseInt(table_len);
        var options = "<option value=''><?php echo $this->lang->line('type'); ?></option>";
        earning_types.forEach(function (type) {
            options += "<option value='" + type.id + "' data-code='" + type.allowance_code + "'>" + type.allowance_name + " (" + type.allowance_code + ")</option>";
        });
        var row = table.insertRow(table_len).outerHTML = "<tr id='row" + id + "'><td><select class='form-control' id='allowance_type' name='allowance_type_id[]'>" + options + "</select></td><td><input type='text' class='form-control' id='allowance_amount' name='allowance_amount[]'  value='0'></td><td><button type='button' onclick='delete_row(" + id + ")' class='closebtn'><i class='fa fa-remove'></i></button></td></tr>";
    }

    function delete_row(id) {
        var table = document.getElementById("tableID");
        var rowCount = table.rows.length;
        $("#row" + id).html("");
    }

    function add_more_deduction() {
        var table = document.getElementById("tableID2");
        var table_len = (table.rows.length);
        var id = parseInt(table_len);
        var options = "<option value=''><?php echo $this->lang->line('type'); ?></option>";
        deduction_types.forEach(function (type) {
            options += "<option value='" + type.id + "' data-code='" + type.allowance_code + "'>" + type.allowance_name + " (" + type.allowance_code + ")</option>";
        });
        var row = table.insertRow(table_len).outerHTML = "<tr id='deduction_row" + id + "'><td><select class='form-control' id='deduction_type' name='deduction_type_id[]'>" + options + "</select></td><td><input type='text' id='deduction_amount' name='deduction_amount[]' class='form-control' value='0'></td><td><button type='button' onclick='delete_deduction_row(" + id + ")' class='closebtn'><i class='fa fa-remove'></i></button></td></tr>";
    }

    function delete_deduction_row(id) {
        var table = document.getElementById("tableID2");
        var rowCount = table.rows.length;
        $("#deduction_row" + id).html("");
    }

    $("#basic").on("input", function () {
        syncEarningsFromBasic();
    });

    $("#gross_salary").on("input", function () {
        updateStatutoryBadgesFromGross();
    });

    $(document).ready(function () {
        updateStatutoryBadgesFromGross();
    });

    $("#contact_submit").click(function (event) {
        var net = $("#net_salary").val();
        if (net == "") {
            $("#err").html("<?php echo $this->lang->line('net_salary_should_not_be_empty'); ?>");
            $("#net_salary").focus();
            return false;
            event.preventDefault();
        } else {
            $("#err").html("");
        }
    });
</script>
