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
                        <div class="row" style="display: flex; align-items: stretch;">
                            <div class="col-md-6 col-sm-12" style="display: flex;">
                                <div class="sfborder" style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; width: 100%; display: flex; flex-direction: column; position: relative;">
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.9); padding: 8px 16px; border-radius: 20px; font-weight: 600; color: #667eea; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        STAFF INFO
                                    </div>
                                    <div style="display: flex; gap: 20px; align-items: flex-start; margin-top: 40px;">
                                        <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 120px; height: 120px; background: rgba(255,255,255,0.15); border-radius: 12px; border: 3px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                            <?php
$image = $result['image'];
if (!empty($image)) {

    $file = $result['image'];
} else {

    $file = "no_image.png";
}
$image=$this->media_storage->getImageURL("uploads/staff_images/" . $file);
?>
                                            <img width="120" height="120" style="border-radius: 10px; object-fit: cover;" src="<?php echo $image ?>" alt="Staff Image">
                                        </div>

                                        <div style="flex: 1; min-width: 0; overflow: hidden;">
                                            <table class="table mb0" style="background: rgba(255,255,255,0.95); border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 100%; min-height: 250px;">
                                                <tbody>
                                                    <tr style="background: #f8f9fa;">
                                                        <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line("name"); ?></th>
                                                        <td style="padding: 10px 8px; font-size: 12px; color: #667eea; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["name"] . " " . $result["surname"] ?></td>
                                                        <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('staff_id'); ?></th>
                                                        <td style="padding: 10px 8px; font-size: 12px; color: #667eea; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["employee_id"] ?></td>
                                                    </tr>
                                                    <tr style="background: #ffffff;">
                                                        <?php if ($sch_setting->staff_phone) {?>
                                                            <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('phone'); ?></th>
                                                        <?php }?>
                                                        <td style="padding: 10px 8px; font-size: 12px; color: #17a2b8; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["contact_no"] ?></td>
                                                        <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('email'); ?></th>
                                                        <td style="padding: 10px 8px; font-size: 12px; color: #17a2b8; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["email"] ?></td>
                                                    </tr>
                                                    <tr style="background: #f8f9fa;">
                                                        <?php if ($sch_setting->staff_epf_no) {?>
                                                            <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('epf_no'); ?></th>
                                                            <td style="padding: 10px 8px; font-size: 12px; color: #28a745; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["epf_no"] ?></td>
                                                        <?php }?>
                                                        <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('role'); ?></th>
                                                        <td style="padding: 10px 8px; font-size: 12px; color: #fd7e14; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["user_type"] ?></td>
                                                    </tr>
                                                    <tr style="background: #ffffff;">
                                                        <?php if ($sch_setting->staff_department) {?>
                                                            <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('department'); ?></th>
                                                            <td style="padding: 10px 8px; font-size: 12px; color: #6f42c1; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["department"] ?></td>
                                                        <?php }if ($sch_setting->staff_designation) {?>
                                                            <th style="padding: 10px 8px; font-size: 11px; color: #495057; border: none; font-weight: 600;"><?php echo $this->lang->line('designation'); ?></th>
                                                            <td style="padding: 10px 8px; font-size: 12px; color: #e83e8c; border: none; font-weight: 600; word-break: break-word;"><?php echo $result["designation"] ?>   </td>
                                                        <?php }?>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div><!--./col-md-6-->
                            <div class="col-md-6 col-sm-12" style="display: flex;">
                                <div class="sfborder relative" style="width: 100%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 8px; padding: 20px; overflow: visible;">
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.9); padding: 8px 16px; border-radius: 20px; font-weight: 600; color: #e91e63; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <?php echo $this->lang->line("attendance") ?>
                                    </div>
                                    <div style="background: rgba(255,255,255,0.95); border-radius: 8px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow-x: auto; margin-top: 40px; min-height: 250px;">
                                        <table class="table mb0" style="font-size: 11px; margin-bottom: 0; table-layout: auto; border-collapse: separate; border-spacing: 0;">
                                            <thead style="position: sticky; top: 0; z-index: 10;">
                                                <tr style="background: #2c3e50 !important;">
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><?php echo $this->lang->line('month'); ?></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Total Working Days">WD</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Total Present (Including Half Day)">P*</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Total Absent (Including Half Day)">A*</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Half Day">HD</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Total Holidays">H</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Total Late">L</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Total Permissions">PR</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Approved Paid Leaves">APR</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Actual Loss Of Pay Days">LOP</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Adjusted LOP Days">AdjLOP</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Net LOP Days (Used for Deduction)">NetLOP</span></th>
                                                    <th style="padding: 10px 6px !important; color: #ffffff !important; border: none !important; font-weight: 700 !important; text-align: center !important; background: #2c3e50 !important;"><span data-toggle="tooltip" title="Weekends">WE</span></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                            $attendence_key_index = 0;
                                            foreach ($monthAttendance as $attendence_key => $attendence_value) {
                                            ?>
                                            <?php
$lop_rules = $this->config->item('lop_rules');
$half_day_weight = isset($lop_rules['half_day_weight']) ? (float) $lop_rules['half_day_weight'] : 0.5;
$max_late_allowed = isset($sch_setting->max_late_allowed) ? (int) $sch_setting->max_late_allowed : 0;
$max_permission_allowed = isset($sch_setting->max_permission_allowed) ? (int) $sch_setting->max_permission_allowed : 0;
$present = (int) ($attendence_value['present'] ?? 0);
$half_day = (int) ($attendence_value['half_day'] ?? 0);
$total_present = $present + ($half_day * $half_day_weight);
$total_absent = $month_absent_total[$attendence_key] ?? 0;
$holiday_count = (int) ($attendence_value['holiday'] ?? 0);
$late_count = (int) ($attendence_value['late'] ?? 0);
$permission_count = (int) ($attendence_value['first_half_permission'] ?? 0) + (int) ($attendence_value['second_half_permission'] ?? 0);
$approved_leave = $monthLeaves[date("m", strtotime($attendence_key))] ?? 0;
$weekend_count = (int) ($attendence_value['sunday'] ?? 0);
$first_half_absent = (int) ($attendence_value['first_half_absent'] ?? 0);
$second_half_absent = (int) ($attendence_value['second_half_absent'] ?? 0);
$paid_leave_absent = $month_paid_leave_absent[$attendence_key] ?? 0;
$days_in_period = (int) ($attendence_value['days_in_period'] ?? 0);
if ($days_in_period <= 0) {
    $days_in_period = cal_days_in_month(CAL_GREGORIAN, (int) date("m", strtotime($attendence_key)), (int) date("Y", strtotime($attendence_key)));
}
$working_days = (int) ($attendence_value['working_days'] ?? 0);
if ($working_days <= 0) {
    $working_days = max(0, $days_in_period - $holiday_count - $weekend_count);
}

$excess_late = $late_count > $max_late_allowed ? ($late_count - $max_late_allowed) : 0;
$excess_permission = $permission_count > $max_permission_allowed ? ($permission_count - $max_permission_allowed) : 0;
$late_permission_penalty = ($excess_late + $excess_permission) * $half_day_weight;
$total_present = max(0, $total_present - $late_permission_penalty + $paid_leave_absent);
$total_absent = $total_absent + $late_permission_penalty;
$lop_days = $total_absent + (($first_half_absent + $second_half_absent) * $half_day_weight);
?>
                                            <tr style="background: <?php echo ($attendence_key_index % 2 == 0) ? '#f8f9fa' : '#ffffff'; ?>; transition: all 0.2s;" onmouseover="this.style.background='#e3f2fd';" onmouseout="this.style.background='<?php echo ($attendence_key_index % 2 == 0) ? '#f8f9fa' : '#ffffff'; ?>';">
                                                <td style="padding: 8px 6px; text-align: center; border: none; font-weight: 600; color: #495057;"><?php echo date("F", strtotime($attendence_key)); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #212529;"><?php echo $working_days; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #28a745; font-weight: 600;"><?php echo rtrim(rtrim(number_format((float) $total_present, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #dc3545; font-weight: 600;"><?php echo rtrim(rtrim(number_format((float) $total_absent, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #fd7e14;"><?php echo $half_day; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #6c757d;"><?php echo $holiday_count; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #ffc107; font-weight: 600;"><?php echo $late_count; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #17a2b8;"><?php echo $permission_count; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #20c997; font-weight: 600;"><?php echo $approved_leave; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #dc3545; font-weight: 600; background: rgba(220, 53, 69, 0.1);"><?php echo rtrim(rtrim(number_format((float) $lop_days, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #6c757d;">-</td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #6c757d;">-</td>
                                                <td style="padding: 8px 6px; text-align: center; border: none; color: #6c757d;"><?php echo $weekend_count; ?></td>
                                            </tr>
                                            <?php
                                            $attendence_key_index++;
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!--./col-md-6-->
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-body" style="padding: 15px; background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                        <div class="text-muted" style="font-size: 12px; line-height: 1.6;">
                            <strong>Legend:</strong> 
                            P*: Present incl. half-day and approved paid leave on absent days. 
                            A*: Absent incl. half-day and late/permission penalty beyond limits. 
                            L: Late count. 
                            PR: Permission count. 
                            APR: Approved paid leaves. 
                            LOP: Actual Loss Of Pay days. 
                            AdjLOP: Days adjusted with paid leaves. 
                            NetLOP: Final LOP for salary deduction.
                        </div>
                    </div>
                    <form class="form-horizontal" action="<?php echo site_url('admin/payroll/editpayroll') ?>" method="post"  id="employeeform">
                        <input type="hidden" name="role" value="<?php echo $result["user_type"] ?>">
                        <input type="hidden" name="id" value="<?php echo $employee_payroll["id"] ?>">

                        <div class="box-header">
                            <div class="row display-flex">
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title"><?php echo $this->lang->line('earning'); ?></h3>
                                    <button type="button" onclick="add_more()" class="plusign"><i class="fa fa-plus"></i></button>
                                    <div class="sameheight">
                                        <div class="feebox">
                                            <table class="table3" id="tableID">
                                                <?php
if (!empty($earnings)) {
    $earning_count = 0;
    foreach ($earnings as $earning_key => $earning_value) {
        ?>
                                                        <input type="hidden" name="allowance_prev_id[]" value="<?php echo $earning_value['id'] ?>" />
  <tr id="row<?php echo $earning_count; ?>">
                                                    <td>
                                                        <input type="text" class="form-control" value="<?php echo $earning_value['allowance_type'] ?>" id="allowance_type" name="allowance_type[]" placeholder="<?php echo $this->lang->line('type'); ?>"></td>
                                                    <td><input type="text" id="allowance_amount" name="allowance_amount[]" class="form-control" value="<?php echo convertBaseAmountCurrencyFormat($earning_value['amount']) ?>"></td>
<td><button type="button" onclick="delete_row(<?php echo $earning_count ?>)" class="closebtn" autocomplete="off"><i class="fa fa-remove"></i></button></td>
                                                </tr>
  <?php
$earning_count++;
    }
} else {
    ?>
  <tr id="row0">
                                                    <td>
                                                         <input type="hidden" name="allowance_prev_id[]" value="0" />
                                                         <input type="text" class="form-control" id="allowance_type" name="allowance_type[]" placeholder="<?php echo $this->lang->line('type'); ?>"></td>
                                                    <td><input type="text" id="allowance_amount" name="allowance_amount[]" class="form-control" value="0"></td>
<td><button type="button" onclick="delete_row(0)" class="closebtn" autocomplete="off"><i class="fa fa-remove"></i></button></td>
                                                </tr>
    <?php
}
?>

                                            </table>
                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title"><?php echo $this->lang->line('deduction'); ?></h3>
                                    <button type="button" onclick="add_more_deduction()" class="plusign"><i class="fa fa-plus"></i></button>
                                    <div class="sameheight">
                                        <div class="feebox">
                                            <table class="table3" id="tableID2">
                                                  <?php
if (!empty($deductions)) {
    $deduction_count = 0;
    foreach ($deductions as $deduction_key => $deduction_value) {
        ?>
                                                        <input type="hidden" name="deduction_prev_id[]" value="<?php echo $deduction_value['id'] ?>" />

                                                <tr id="deduction_row<?php echo $deduction_count; ?>">
                                                    <td>
                                                        <input type="text" id="deduction_type" name="deduction_type[]" class="form-control" value="<?php echo $deduction_value['allowance_type'] ?>"  placeholder="<?php echo $this->lang->line('type'); ?>"></td>
                                                    <td>
                                                        <input type="text" id="deduction_amount" name="deduction_amount[]" class="form-control" value="<?php echo convertBaseAmountCurrencyFormat($deduction_value['amount']) ?>">
                                                    </td>

<td><button type="button" onclick="delete_deduction_row(<?php echo $deduction_count ?>)" class="closebtn" autocomplete="off"><i class="fa fa-remove"></i></button></td>
                                                </tr>
  <?php
$deduction_count++;
    }
} else {
    ?>
                                                <tr id="deduction_row0">
                                                    <td>
                                                          <input type="hidden" name="deduction_prev_id[]" value="0" />
                                                        <input type="text" id="deduction_type" name="deduction_type[]" class="form-control" placeholder="<?php echo $this->lang->line('type'); ?>"></td>
                                                    <td><input type="text" id="deduction_amount" name="deduction_amount[]" class="form-control" value="0"></td>
<td><button type="button" onclick="delete_deduction_row(0)" class="closebtn" autocomplete="off"><i class="fa fa-remove"></i></button></td>
                                                </tr>
    <?php
}
?>
                                            </table>
                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title"><?php echo $this->lang->line('payroll_summary'); ?>(<?php echo $currency_symbol ?>)</h3>
                                    <button type="button" onclick="add_allowance()" class="plusign"><i class="fa fa-calculator"></i> <?php echo $this->lang->line('calculate'); ?></button>
                                    <div class="sameheight">
                                        <div class="payrollbox feebox">
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('basic_salary'); ?></label>
                                                <div class="col-sm-8">
                                                    <input class="form-control" name="basic" value="<?php echo $employee_payroll['basic']; ?>" id="basic"  type="text" />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('earning'); ?></label>
                                                <div class="col-sm-8">
                                                    <input class="form-control" name="total_allowance" id="total_allowance"  type="text" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['total_allowance']); ?>" />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('gross_salary'); ?></label>
                                                <div class="col-sm-8">
                    <input class="form-control" name="gross_salary" id="gross_salary" type="text" value="<?php echo convertBaseAmountCurrencyFormat(($employee_payroll['basic'] + $employee_payroll['total_allowance']) - $employee_payroll['total_deduction']); ?>"/>
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('deduction'); ?></label>
                                                <div class="col-sm-8 deductiondred">
                                                    <input class="form-control" name="total_deduction" id="total_deduction" type="text" style="color:#f50000" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['total_deduction']); ?>"/>
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label">LOP</label>
                                                <div class="col-sm-8 deductiondred">
                                                    <input class="form-control" name="leave_deduction" id="lop_deduction" type="text" style="color:#f50000" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['leave_deduction']); ?>" readonly />
                                                </div>
                                            </div><!--./form-group-->
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('tax'); ?></label>
                                                <div class="col-sm-8 deductiondred">
                                                    <input class="form-control" name="tax_percent" id="tax_percent"  type="text" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['tax']); ?>"/>
                                                </div>
                                            </div><!--./form-group-->
                                            <hr/>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"><?php echo $this->lang->line('net_salary'); ?></label>
                                                <div class="col-sm-8 net_green">
                                                    <input class="form-control greentest"  name="net_salary" id="net_salary"  type="text" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['net_salary']); ?>"/>
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
                                    <button type="submit" id="contact_submit" class="btn btn-info pull-right"><i class="fa fa-check-circle"></i> <?php echo $this->lang->line('save'); ?></button>
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
    function findBasicPayRow() {
        var allowance_type = document.getElementsByName('allowance_type[]');
        var allowance_amount = document.getElementsByName('allowance_amount[]');
        for (var i = 0; i < allowance_type.length; i++) {
            var label = (allowance_type[i].value || '').trim().toLowerCase();
            if (label === 'basic pay' || label === 'basic salary') {
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

    function add_allowance() {
        $("#net_salary").val('');
        $("#gross_salary").val('');
        syncBasicFromEarnings();
        var basic_pay = $("#basic").val();
       if(basic_pay > 0){ 
        var allowance_type = document.getElementsByName('allowance_type[]');
        var allowance_amount = document.getElementsByName('allowance_amount[]');
        var total_allowance = 0;
        var deduction_type = document.getElementsByName('deduction_type[]');
        var deduction_amount = document.getElementsByName('deduction_amount[]');
        var total_deduction = 0;
        for (var i = 0; i < allowance_amount.length; i++) {
            var inp = allowance_amount[i];
            if (inp.value == '') {
                var inpvalue = 0;
            } else {
                var inpvalue = inp.value;
            }

            var label = (allowance_type[i].value || '').trim().toLowerCase();
            if (label === 'basic pay' || label === 'basic salary') {
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
            total_deduction += parseFloat(inpdvalue);
        }

        var working_days = parseFloat($("#working_days").val()) || 0;
        var lop_days = parseFloat($("#lop_days").val()) || 0;

        var gross_salary = parseFloat(basic_pay) + parseFloat(total_allowance);
        var lop_deduction = 0;
        if (working_days > 0 && lop_days > 0) {
            lop_deduction = (gross_salary / working_days) * lop_days;
        }

        var tax = $("#tax_percent").val();
        if (tax == '') {
            var tax = 0;
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
    }
    }

    function add_more() {
        var table = document.getElementById("tableID");
        var table_len = (table.rows.length);
        var id = parseInt(table_len);
        var row = table.insertRow(table_len).outerHTML = "<tr id='row" + id + "'><td><input type='hidden' name='allowance_prev_id[]' value='0' /><input type='text' class='form-control' id='allowance_type' name='allowance_type[]' placeholder='<?php echo $this->lang->line("type"); ?>'></td><td><input type='text' class='form-control' id='allowance_amount' name='allowance_amount[]'  value='0'></td><td><button type='button' onclick='delete_row(" + id + ")' class='closebtn'><i class='fa fa-remove'></i></button></td></tr>";
    }

    function delete_row(id) {
        var table = document.getElementById("tableID");
        var rowCount = table.rows.length;
        $("#row" + id).remove();
    }

    function add_more_deduction() {
        var table = document.getElementById("tableID2");
        var table_len = (table.rows.length);
        var id = parseInt(table_len);
        var row = table.insertRow(table_len).outerHTML = "<tr id='deduction_row" + id + "'><td><input type='hidden' name='deduction_prev_id[]' value='0' /><input type='text' class='form-control' id='deduction_type' name='deduction_type[]' placeholder='<?php echo $this->lang->line("type"); ?>'></td><td><input type='text' id='deduction_amount' name='deduction_amount[]' class='form-control' value='0'></td><td><button type='button' onclick='delete_deduction_row(" + id + ")' class='closebtn'><i class='fa fa-remove'></i></button></td></tr>";
    }

    function delete_deduction_row(id) {
        var table = document.getElementById("tableID2");
        var rowCount = table.rows.length;
        $("#deduction_row" + id).html("");
    }

    $("#basic").on("input", function () {
        syncEarningsFromBasic();
    });

    $("#contact_submit").click(function (event) {
        var net = $("#net_salary").val();
        if (net == "") {
            $("#err").html("<?php echo $this->lang->line('net_salary_should_not_be_empty') ?>");
            $("#net_salary").focus();
            return false;
            event.preventDefault();
        } else {
            $("#err").html("");
        }
    });
</script>