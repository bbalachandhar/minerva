<?php
// Version: 2026-02-18-FINAL - Complete ESI summary widget with all calculations
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

    /* Modern Summary Styling */
    .payrollbox {
        background: white;
        box-shadow: none;
    }
    
    .modern-summary-section {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .modern-summary-section:hover {
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }
    
    .section-title {
        font-weight: 800 !important;
        padding: 10px 12px !important;
    }
    
    .modern-summary-row input {
        transition: all 0.2s ease;
    }
    
    .modern-summary-row input:focus {
        color: inherit !important;
        font-weight: 700 !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modern-summary-label {
            font-size: 11px;
        }
        .modern-summary-value {
            font-size: 13px;
        }
        .modern-summary-row {
            padding: 8px 10px !important;
        }
    }
</style>
<div class="content-wrapper" style="min-height: 393px;">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->session->flashdata('msg')) { ?>
                <div class="col-md-12">
                    <?php echo $this->session->flashdata('msg'); ?>
                </div>
            <?php } ?>
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
                                    <a href="<?php echo site_url('admin/payroll/bulk_add_increment'); ?>" type="button" class="btn btn-success btn-xs" title="Bulk Add Salary Increment">
                                        <i class="fa fa-users"></i> Bulk Increment
                                    </a>
                                    <a href="<?php echo site_url('admin/payroll/add_increment/' . $result['id']); ?>" type="button" class="btn btn-default btn-xs" title="Add Salary Increment for <?php echo $result['name']; ?>">
                                        <i class="fa fa-plus-circle"></i> Add Increment
                                    </a>
                                    <a href="<?php echo site_url('admin/payroll/settings'); ?>" type="button" class="btn btn-info btn-xs" title="EPF & TDS Settings">
                                        <i class="fa fa-gear"></i> Settings
                                    </a>
                                    <a href="<?php echo base_url() ?>admin/payroll" type="button" class="btn btn-primary btn-xs">
                                        <i class="fa fa-arrow-left"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!--./box-header-->
                    <div class="box-body" style="padding: 15px;">
                        <!-- STAFF INFO Section -->
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-12">
                                <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px;">
                                    <h5 style="margin: 0 0 15px 0; font-weight: 600; color: #333;">STAFF INFO</h5>
                                    <div style="display: flex; gap: 20px;">
                                        <!-- Staff Image -->
                                        <div style="flex-shrink: 0;">
                                            <?php
$image = $result['image'];
if (!empty($image)) {
    $file = $result['image'];
} else {
    $file = "no_image.png";
}
$image=$this->media_storage->getImageURL("uploads/staff_images/" . $file);
?>
                                            <img width="100" height="100" style="border-radius: 6px; object-fit: cover; border: 1px solid #e0e0e0;" src="<?php echo $image ?>" alt="Staff Image">
                                        </div>
                                        <!-- Staff Details -->
                                        <div style="flex: 1;">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <tbody>
                                                    <tr>
                                                        <td style="padding: 6px 8px; width: 25%; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line("name"); ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333; font-weight: 600;"><?php echo $result["name"] . " " . $result["surname"] ?></td>
                                                        <td style="padding: 6px 8px; width: 25%; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('staff_id'); ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333; font-weight: 600;"><?php echo $result["employee_id"] ?></td>
                                                    </tr>
                                                    <tr style="background: #f9f9f9;">
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('phone'); ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333;"><?php echo $result["contact_no"] ?></td>
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('email'); ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333;"><?php echo $result["email"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('uan_no') ?: 'UAN No.'; ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333; font-weight: 600;"><?php echo isset($result['uan_no']) ? $result['uan_no'] : ''; ?></td>
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('role'); ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333;"><?php echo $result["user_type"] ?></td>
                                                    </tr>
                                                    <tr style="background: #f9f9f9;">
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('esi_no') ?: 'ESI No.'; ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333;"><?php echo isset($result['esi_no']) ? $result['esi_no'] : ''; ?></td>
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('department'); ?></td>
                                                        <td style="padding: 6px 8px; font-size: 13px; color: #333;"><?php echo $result["department"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 6px 8px; font-size: 12px; color: #666; font-weight: 600;"><?php echo $this->lang->line('designation'); ?></td>
                                                        <td colspan="3" style="padding: 6px 8px; font-size: 13px; color: #333;"><?php echo $result["designation"] ?></td>
                                                    </tr>
                                                    <tr style="background: #f9f9f9;">
                                                        <td colspan="4" style="padding: 8px; font-size: 12px; color: #666;">
                                                            <strong>Statutory Deductions Status:</strong>
                                                            <i class="fa fa-info-circle text-muted" style="font-size: 11px; margin-left: 4px;" data-toggle="tooltip" title="Badge marking: If ESI is No and this month Gross Salary is ≤ ₹21,000, it auto-updates to Yes. Once Yes, it remains Yes."></i>
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
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ATTENDANCE Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px;">
                                    <h5 style="margin: 0 0 15px 0; font-weight: 600; color: #333;"><?php echo $this->lang->line("attendance") ?></h5>
                                    <div class="text-muted" style="font-size:11px; margin: 0 0 8px 0;">
                                        Sandwich Weekend Rule: Weekend is payable only if both previous and next working days are present-like (P/L/HD/FHP/SHP); otherwise weekend is counted in LOP. LOP shown below includes working-day absence, half-day impact, and non-payable weekends before leave-credit adjustment.
                                    </div>
                                    <div style="overflow-x: auto;">
                                        <table class="table table-bordered" style="font-size: 11px; margin-bottom: 0; border-collapse: collapse;">
                                            <thead>
                                                <tr style="background: #f8f9fa;">
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><?php echo $this->lang->line('month'); ?></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Total Days In Month">WD</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Actual Working Days">AWD</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Total Present (Including Half Day)">P*</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Total Absent (Including Half Day)">A*</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Half Day">HD</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Total Holidays">H</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Total Late">L</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Total Permissions">PR</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Approved Paid Leaves">APR</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Actual Loss Of Pay Days">LOP</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Adjusted LOP Days">AdjLOP</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Net LOP Days (Used for Deduction)">NetLOP</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Leave-credit days adjusted in this month">CrAdj</span></th>
                                                    <th style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #495057;"><span data-toggle="tooltip" title="Carry-forward leave credit after this month">CrBal</span></th>
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
$weekend_lop_days = 0.0;
$days_in_period = (int) ($attendence_value['days_in_period'] ?? 0);
if ($days_in_period <= 0) {
    $days_in_period = cal_days_in_month(CAL_GREGORIAN, (int) date("m", strtotime($attendence_key)), (int) date("Y", strtotime($attendence_key)));
}
$month_days = cal_days_in_month(CAL_GREGORIAN, (int) date("m", strtotime($attendence_key)), (int) date("Y", strtotime($attendence_key)));
$working_days = (int) ($attendence_value['working_days'] ?? 0);
if ($working_days <= 0) {
    $working_days = max(0, $days_in_period - $holiday_count - $weekend_count);
}

$excess_late = $late_count > $max_late_allowed ? ($late_count - $max_late_allowed) : 0;
$excess_permission = $permission_count > $max_permission_allowed ? ($permission_count - $max_permission_allowed) : 0;
$late_permission_penalty = ($excess_late + $excess_permission) * $half_day_weight;
$total_present = max(0, $total_present - $late_permission_penalty);
$total_absent = $total_absent + $late_permission_penalty;
$lop_days = $total_absent + (($first_half_absent + $second_half_absent) * $half_day_weight);

// Check if this is the current payroll month
$month_num = date('m', strtotime($attendence_key));
$year_num = date('Y', strtotime($attendence_key));
$month_name = date('F', strtotime($attendence_key));
$is_current_payroll_month = (($month_name == $employee_payroll['month'] || $month_num == $employee_payroll['month']) && $year_num == $employee_payroll['year']);

// For current payroll month, display exactly what payroll calculation uses
if ($is_current_payroll_month && !empty($payroll_lop_summary)) {
    if (isset($payroll_lop_summary['days_in_month'])) {
        $month_days = (int) $payroll_lop_summary['days_in_month'];
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
    if (isset($payroll_lop_summary['weekend_lop_days'])) {
        $weekend_lop_days = (float) $payroll_lop_summary['weekend_lop_days'];
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
    if (isset($payroll_lop_summary['lop_days'])) {
        $lop_days = (float) $payroll_lop_summary['lop_days'];
    }
    if (isset($payroll_lop_summary['late'])) {
        $late_count = (int) $payroll_lop_summary['late'];
    }
    if (isset($payroll_lop_summary['first_half_permission']) || isset($payroll_lop_summary['second_half_permission'])) {
        $permission_count = (int) ($payroll_lop_summary['first_half_permission'] ?? 0) + (int) ($payroll_lop_summary['second_half_permission'] ?? 0);
    }
}

// Show A* as total absence including non-payable sandwich weekends.
$total_absent_display = (float) $total_absent + (float) $weekend_lop_days;

// For current payroll month, use values from payslip; for other months, query monthly balance
if ($is_current_payroll_month) {
    $adjusted_lop = floatval($employee_payroll['adjusted_lop_days'] ?? 0);
    $net_lop = max(0, round(((float) $lop_days - (float) $adjusted_lop), 2));
    $od_adjusted_days = 0;
    $od_carry_forward_days = 0;

    $CI =& get_instance();
    $CI->db->select('SUM(smlb.used_for_lop_adjustment) as credit_adjusted, SUM(smlb.closing_balance) as credit_carry');
    $CI->db->from('staff_monthly_leave_balance smlb');
    $CI->db->join('leave_types lt', 'lt.id = smlb.leave_type_id', 'inner');
    $CI->db->where('smlb.staff_id', (int) $result['id']);
    $CI->db->where('smlb.month', (int) $month_num);
    $CI->db->where('smlb.year', (int) $year_num);
    $CI->db->where('lt.is_lop', 0);
    $CI->db->where('lt.requires_balance_check', 0);
    $credit_row = $CI->db->get()->row_array();
    if (is_array($credit_row)) {
        $od_adjusted_days = floatval($credit_row['credit_adjusted'] ?? 0);
        $od_carry_forward_days = floatval($credit_row['credit_carry'] ?? 0);
    }
} else {
    // Get adjusted LOP from monthly balance for historical months
    $CI =& get_instance();
    $CI->db->select('SUM(used_for_lop_adjustment) as total_adjusted_lop');
    $CI->db->from('staff_monthly_leave_balance');
    $CI->db->where('staff_id', $result['id']);
    $CI->db->where('month', $month_num);
    $CI->db->where('year', $year_num);
    $lop_query = $CI->db->get();
    $lop_data = $lop_query->row_array();
    $adjusted_lop = floatval($lop_data['total_adjusted_lop'] ?? 0);
    $net_lop = max(0, $lop_days - $adjusted_lop);

    $CI->db->select('SUM(smlb.used_for_lop_adjustment) as od_adjusted, SUM(smlb.closing_balance) as od_carry');
    $CI->db->from('staff_monthly_leave_balance smlb');
    $CI->db->join('leave_types lt', 'lt.id = smlb.leave_type_id', 'inner');
    $CI->db->where('smlb.staff_id', $result['id']);
    $CI->db->where('smlb.month', $month_num);
    $CI->db->where('smlb.year', $year_num);
    $CI->db->where('lt.is_lop', 0);
    $CI->db->group_start();
    $CI->db->where('LOWER(TRIM(lt.type))', 'on duty');
    $CI->db->or_where('LOWER(TRIM(lt.type))', 'od');
    $CI->db->group_end();
    $od_query = $CI->db->get();
    $od_data = $od_query->row_array();
    $od_adjusted_days = floatval($od_data['od_adjusted'] ?? 0);
    $od_carry_forward_days = floatval($od_data['od_carry'] ?? 0);
}
?>
                                            <tr style="background: <?php echo ($attendence_key_index % 2 == 0) ? '#f9f9f9' : '#ffffff'; ?>;">
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; font-weight: 600; color: #333;"><?php echo date("F", strtotime($attendence_key)); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $month_days; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $working_days; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $total_present, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $total_absent_display, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $half_day; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $holiday_count; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $late_count; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $permission_count; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo $approved_leave; ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $lop_days, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $adjusted_lop, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $net_lop, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $od_adjusted_days, 1, '.', ''), '0'), '.'); ?></td>
                                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #e0e0e0; color: #666;"><?php echo rtrim(rtrim(number_format((float) $od_carry_forward_days, 1, '.', ''), '0'), '.'); ?></td>
                                            </tr>
                                            <?php
                                            $attendence_key_index++;
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-body" style="padding: 15px; background-color: #f9fafb; border-top: 1px solid #e5e7eb;">
                        <div class="text-muted" style="font-size: 12px; line-height: 1.6;">
                            <strong>Legend:</strong> 
                            P*: Present incl. half-day (after late/permission penalty). 
                            A*: Absent incl. half-day and late/permission penalty. 
                            Sandwich Rule: Weekend is payable only when both adjacent working days are present-like. 
                            LOP: Actual LOP days after applying sandwich rule. 
                            AdjLOP: LOP days adjusted with eligible leave credits. 
                            NetLOP: Final LOP used for month-day prorata salary deduction.
                        </div>
                    </div>
                    <form class="form-horizontal" action="<?php echo site_url('admin/payroll/editpayroll') ?>" method="post"  id="employeeform">
                        <input type="hidden" name="role" value="<?php echo $result["user_type"] ?>">
                        <input type="hidden" name="id" value="<?php echo $employee_payroll["id"] ?>">

                        <div class="box-header">
                            <div class="row display-flex">
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title">📊 <?php echo $this->lang->line('earning'); ?></h3>
                                    <?php if(!$is_calculated): ?>
                                        <button type="button" onclick="add_more()" class="plusign"><i class="fa fa-plus"></i> Add</button>
                                    <?php endif; ?>
                                    <div class="sameheight">
                                        <div class="feebox" style="padding: 0; background: white; border-radius: 6px; overflow: hidden;">
                                            <style>
                                                .modern-item-list {
                                                    list-style: none;
                                                    padding: 0;
                                                    margin: 0;
                                                }
                                                .modern-item-row {
                                                    display: flex;
                                                    gap: 8px;
                                                    padding: 12px;
                                                    background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
                                                    border-bottom: 1px solid rgba(0,0,0,0.05);
                                                    align-items: center;
                                                    transition: all 0.3s ease;
                                                }
                                                .modern-item-row:last-child {
                                                    border-bottom: none;
                                                }
                                                .modern-item-row:hover {
                                                    background: linear-gradient(135deg, #dcedc8 0%, #e6f0e8 100%);
                                                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                                                }
                                                .modern-item-type {
                                                    flex: 0 0 35%;
                                                }
                                                .modern-item-amount {
                                                    flex: 0 0 50%;
                                                }
                                                .modern-item-action {
                                                    flex: 0 0 15%;
                                                    text-align: right;
                                                }
                                                .modern-item-row input {
                                                    border: 1px solid rgba(46, 125, 50, 0.3);
                                                    background: white;
                                                    border-radius: 4px;
                                                    padding: 6px 8px;
                                                    font-size: 12px;
                                                }
                                                .modern-item-row input:focus {
                                                    border-color: #2e7d32;
                                                    box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.1);
                                                }
                                                .modern-item-delete {
                                                    background: #dc3545;
                                                    color: white;
                                                    border: none;
                                                    border-radius: 3px;
                                                    padding: 4px 6px;
                                                    cursor: pointer;
                                                    font-size: 12px;
                                                    transition: all 0.2s ease;
                                                }
                                                .modern-item-delete:hover {
                                                    background: #bb2d3b;
                                                    transform: scale(1.05);
                                                }
                                            </style>
                                                <div class="modern-item-list" id="tableID">
                                                <?php
if (!empty($earnings)) {
    $earning_count = 0;
    foreach ($earnings as $earning_key => $earning_value) {
        ?>
                                                <div class="modern-item-row" id="row<?php echo $earning_count; ?>">
                                                    <input type="hidden" name="allowance_prev_id[]" value="<?php echo $earning_value['id'] ?>" />
                                                    <?php if($is_calculated): ?>
                                                        <!-- Read-only display for calculated payslip -->
                                                        <input type="hidden" name="allowance_type_id[]" value="<?php echo !empty($earning_value['allowance_type_id']) ? $earning_value['allowance_type_id'] : ''; ?>">
                                                        <div class="form-control modern-item-type" style="background-color: #f5f5f5; border: none; cursor: not-allowed; padding: 8px 12px;">
                                                            <?php 
                                                                $type_name = !empty($earning_value['allowance_type_name']) ? $earning_value['allowance_type_name'] : ucfirst(strtolower($earning_value['allowance_type']));
                                                                $type_code = !empty($earning_value['allowance_code']) ? $earning_value['allowance_code'] : $earning_value['allowance_type'];
                                                                echo $type_name . ' (' . $type_code . ')';
                                                            ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Editable dropdown for new payslip -->
                                                        <select class="form-control modern-item-type" name="allowance_type_id[]" required>
                                                            <option value="">Select Type</option>
                                                            <?php foreach($earning_types as $type): ?>
                                                                <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>" 
                                                                    <?php echo ($type['id'] == $earning_value['allowance_type_id']) ? 'selected' : ''; ?>>
                                                                    <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php endif; ?>
                                                    <?php if($is_calculated): ?>
                                                        <div class="form-control modern-item-amount" style="background-color: #f5f5f5; border: none; cursor: not-allowed; text-align: right;">
                                                            <?php echo convertBaseAmountCurrencyFormat($earning_value['amount']) ?>
                                                        </div>
                                                        <input type="hidden" name="allowance_amount[]" value="<?php echo $earning_value['amount'] ?>">
                                                    <?php else: ?>
                                                        <input type="text" id="allowance_amount" name="allowance_amount[]" class="form-control modern-item-amount" value="<?php echo convertBaseAmountCurrencyFormat($earning_value['amount']) ?>" placeholder="Amount">
                                                    <?php endif; ?>
                                                    <?php if(!$is_calculated): ?>
                                                        <button type="button" onclick="delete_row(<?php echo $earning_count ?>)" class="modern-item-delete" autocomplete="off"><i class="fa fa-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
  <?php
$earning_count++;
    }
} else {
    ?>
                                                <div class="modern-item-row" id="row0">
                                                    <input type="hidden" name="allowance_prev_id[]" value="0" />
                                                    <select class="form-control modern-item-type" name="allowance_type_id[]">
                                                        <option value="">Select Type</option>
                                                        <?php foreach($earning_types as $type): ?>
                                                            <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>">
                                                                <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="text" id="allowance_amount" name="allowance_amount[]" class="form-control modern-item-amount" value="0" placeholder="Amount">
                                                    <button type="button" onclick="delete_row(0)" class="modern-item-delete" autocomplete="off"><i class="fa fa-trash"></i></button>
                                                </div>
    <?php
}
?>
                                                <!-- Increment/Bonus Display (if applicable) -->
                                                <?php if (!empty($employee_payroll['is_increment_month']) && !empty($employee_payroll['increment_amount']) && $employee_payroll['increment_amount'] > 0): 
                                                    $is_bonus = isset($employee_payroll['is_recurring']) && $employee_payroll['is_recurring'] == 0;
                                                    $label = $is_bonus ? 'Bonus (One-Time)' : 'Increment (Temporary)';
                                                    $badge_text = $is_bonus ? 'BONUS' : 'TEMP';
                                                    $badge_color = $is_bonus ? 'label-info' : 'label-warning';
                                                    $border_color = $is_bonus ? '#ff9800' : '#ffc107';
                                                    $bg_gradient = $is_bonus ? 'linear-gradient(135deg, #e3f2fd 0%, #f0f9ff 100%)' : 'linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%)';
                                                    $text_color = $is_bonus ? '#0288d1' : '#ff9800';
                                                    $merge_info = $is_bonus ? 'This bonus applies only to this month and will NOT be merged into salary.' : 'This increment will appear only this month. From next month, it will be merged into ' . (isset($employee_payroll['merge_with']) ? ucfirst(str_replace('_', ' ', $employee_payroll['merge_with'])) : 'Basic Salary') . '.';
                                                ?>
                                                <style>
                                                    .increment-row-highlight {
                                                        display: flex;
                                                        gap: 8px;
                                                        padding: 12px;
                                                        background: <?php echo $bg_gradient; ?> !important;
                                                        border-bottom: 1px solid rgba(0,0,0,0.05);
                                                        border-left: 4px solid <?php echo $border_color; ?> !important;
                                                        align-items: center;
                                                        transition: all 0.3s ease;
                                                    }
                                                    .increment-row-highlight:hover {
                                                        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.2);
                                                    }
                                                </style>
                                                <div class="increment-row-highlight">
                                                    <div style="flex: 0 0 35%; display: flex; align-items: center;">
                                                        <strong style="color: <?php echo $text_color; ?>; font-weight: 700;">
                                                            <i class="fa fa-<?php echo $is_bonus ? 'gift' : 'star'; ?>"></i> <?php echo $label; ?>
                                                        </strong>
                                                    </div>
                                                    <div style="flex: 0 0 50%;">
                                                        <div style="background: white; padding: 6px 8px; border-radius: 4px; border: 1px solid <?php echo $border_color; ?>; font-weight: 600; color: <?php echo $text_color; ?>;">
                                                            <?php echo $currency_symbol . number_format($employee_payroll['increment_amount'], 2); ?>
                                                        </div>
                                                    </div>
                                                    <div style="flex: 0 0 15%; text-align: center;">
                                                        <span class="label <?php echo $badge_color; ?>" style="font-size: 11px; padding: 4px 6px;">
                                                            <?php echo $badge_text; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div style="padding: 8px 12px; background: <?php echo $is_bonus ? '#e3f2fd' : '#fff9e6'; ?>; font-size: 11px; color: <?php echo $text_color; ?>; border-bottom: 1px solid rgba(255, 193, 7, 0.3);">
                                                    <i class="fa fa-info-circle"></i> 
                                                    <?php echo $merge_info; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title">💸 <?php echo $this->lang->line('deduction'); ?></h3>
                                    <?php if(!$is_calculated): ?>
                                        <button type="button" onclick="add_more_deduction()" class="plusign"><i class="fa fa-plus"></i> Add</button>
                                    <?php endif; ?>
                                    <div class="sameheight">
                                        <div class="feebox" style="padding: 0; background: white; border-radius: 6px; overflow: hidden;">
                                            <style>
                                                .modern-deduction-row {
                                                    display: flex;
                                                    gap: 8px;
                                                    padding: 12px;
                                                    background: linear-gradient(135deg, #ffebee 0%, #ffe0b2 100%);
                                                    border-bottom: 1px solid rgba(0,0,0,0.05);
                                                    align-items: center;
                                                    transition: all 0.3s ease;
                                                }
                                                .modern-deduction-row:last-child {
                                                    border-bottom: none;
                                                }
                                                .modern-deduction-row:hover {
                                                    background: linear-gradient(135deg, #ffcdd2 0%, #ffe0b2 100%);
                                                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                                                }
                                                .modern-deduction-type {
                                                    flex: 0 0 35%;
                                                }
                                                .modern-deduction-amount {
                                                    flex: 0 0 50%;
                                                }
                                                .modern-deduction-action {
                                                    flex: 0 0 15%;
                                                    text-align: right;
                                                }
                                                .modern-deduction-row input {
                                                    border: 1px solid rgba(220, 53, 69, 0.3);
                                                    background: white;
                                                    border-radius: 4px;
                                                    padding: 6px 8px;
                                                    font-size: 12px;
                                                }
                                                .modern-deduction-row input:focus {
                                                    border-color: #dc3545;
                                                    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
                                                }
                                            </style>
                                            <div class="modern-item-list" id="tableID2">
                                                  <?php
if (!empty($deductions)) {
    $deduction_count = 0;
    foreach ($deductions as $deduction_key => $deduction_value) {
        ?>
                                                <div class="modern-deduction-row" id="deduction_row<?php echo $deduction_count; ?>">
                                                    <input type="hidden" name="deduction_prev_id[]" value="<?php echo $deduction_value['id'] ?>" />
                                                    <?php if($is_calculated): ?>
                                                        <!-- Read-only display for calculated payslip -->
                                                        <input type="hidden" name="deduction_type_id[]" value="<?php echo !empty($deduction_value['allowance_type_id']) ? $deduction_value['allowance_type_id'] : ''; ?>">
                                                        <div class="form-control modern-deduction-type" style="background-color: #f5f5f5; border: none; cursor: not-allowed; padding: 8px 12px;">
                                                            <?php 
                                                                $type_name = !empty($deduction_value['allowance_type_name']) ? $deduction_value['allowance_type_name'] : ucfirst(strtolower($deduction_value['allowance_type']));
                                                                $type_code = !empty($deduction_value['allowance_code']) ? $deduction_value['allowance_code'] : $deduction_value['allowance_type'];
                                                                echo $type_name . ' (' . $type_code . ')';
                                                            ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Editable dropdown for new payslip -->
                                                        <select class="form-control modern-deduction-type" name="deduction_type_id[]" required>
                                                            <option value="">Select Type</option>
                                                            <?php foreach($deduction_types as $type): ?>
                                                                <?php
                                                                    $selected_by_id = !empty($deduction_value['allowance_type_id']) && ((int)$type['id'] === (int)$deduction_value['allowance_type_id']);
                                                                    $selected_by_code = empty($deduction_value['allowance_type_id'])
                                                                        && !empty($deduction_value['allowance_type'])
                                                                        && strtoupper(trim((string)$type['allowance_code'])) === strtoupper(trim((string)$deduction_value['allowance_type']));
                                                                ?>
                                                                <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>" 
                                                                    <?php echo ($selected_by_id || $selected_by_code) ? 'selected' : ''; ?>>
                                                                    <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php endif; ?>
                                                    <?php if($is_calculated): ?>
                                                        <div class="form-control modern-deduction-amount" style="background-color: #f5f5f5; border: none; cursor: not-allowed; text-align: right;">
                                                            <?php echo convertBaseAmountCurrencyFormat($deduction_value['amount']) ?>
                                                        </div>
                                                        <input type="hidden" name="deduction_amount[]" value="<?php echo $deduction_value['amount'] ?>">
                                                    <?php else: ?>
                                                        <input type="text" id="deduction_amount" name="deduction_amount[]" class="form-control modern-deduction-amount" value="<?php echo convertBaseAmountCurrencyFormat($deduction_value['amount']) ?>" placeholder="Amount">
                                                    <?php endif; ?>
                                                    <?php if(!$is_calculated): ?>
                                                        <button type="button" onclick="delete_deduction_row(<?php echo $deduction_count ?>)" class="modern-item-delete" autocomplete="off"><i class="fa fa-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
  <?php
$deduction_count++;
    }
} else {
    ?>
                                                <div class="modern-deduction-row" id="deduction_row0">
                                                    <input type="hidden" name="deduction_prev_id[]" value="0" />
                                                    <select class="form-control modern-deduction-type" name="deduction_type_id[]">
                                                        <option value="">Select Type</option>
                                                        <?php foreach($deduction_types as $type): ?>
                                                            <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['allowance_code']; ?>">
                                                                <?php echo $type['allowance_name']; ?> (<?php echo $type['allowance_code']; ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="text" id="deduction_amount" name="deduction_amount[]" class="form-control modern-deduction-amount" value="0" placeholder="Amount">
                                                    <button type="button" onclick="delete_deduction_row(0)" class="modern-item-delete" autocomplete="off"><i class="fa fa-trash"></i></button>
                                                </div>
    <?php
}
?>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <h3 class="box-title"><?php echo $this->lang->line('payroll_summary'); ?> (<?php echo $currency_symbol ?>)</h3>
                                    <button type="button" onclick="add_allowance()" class="plusign"><i class="fa fa-calculator"></i> <?php echo $this->lang->line('calculate'); ?></button>
                                    <div class="sameheight">
                                        <div class="payrollbox feebox" style="padding: 0; background: white;">
                                            <style>
                                                .modern-summary-section {
                                                    margin-bottom: 12px;
                                                    border-radius: 6px;
                                                    overflow: hidden;
                                                }
                                                .modern-summary-row {
                                                    display: flex;
                                                    justify-content: space-between;
                                                    align-items: center;
                                                    padding: 10px 12px;
                                                    border-bottom: 1px solid #f0f0f0;
                                                }
                                                .modern-summary-row:last-child {
                                                    border-bottom: none;
                                                }
                                                .modern-summary-label {
                                                    font-size: 12px;
                                                    color: #666;
                                                    font-weight: 500;
                                                    text-transform: uppercase;
                                                    letter-spacing: 0.3px;
                                                }
                                                .modern-summary-value {
                                                    font-size: 15px;
                                                    font-weight: 700;
                                                    color: #1a1a1a;
                                                    text-align: right;
                                                }
                                                .modern-summary-value input {
                                                    border: none;
                                                    background: transparent;
                                                    text-align: right;
                                                    font-size: 15px;
                                                    font-weight: 700;
                                                    padding: 0;
                                                    color: #1a1a1a;
                                                }
                                                .modern-summary-value input:focus {
                                                    outline: none;
                                                    background: transparent;
                                                }
                                                .section-title {
                                                    font-size: 11px;
                                                    font-weight: 700;
                                                    text-transform: uppercase;
                                                    letter-spacing: 0.5px;
                                                    color: #999;
                                                    padding: 8px 12px;
                                                    background: #fafafa;
                                                    margin: 0;
                                                }
                                                /* Earnings Section */
                                                .earnings-section { background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%); }
                                                /* Deductions Section */
                                                .deductions-section { background: linear-gradient(135deg, #ffebee 0%, #ffe0b2 100%); }
                                                /* EPF Section */
                                                .epf-section { background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); }
                                                /* Tax Section */
                                                .tax-section { background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%); }
                                                /* Net Section */
                                                .net-section { background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%); }
                                                .net-value {
                                                    color: #1b5e20 !important;
                                                    font-size: 18px !important;
                                                }
                                                .deduction-value {
                                                    color: #c62828 !important;
                                                }
                                            </style>

                                            <!-- EARNINGS SECTION -->
                                            <div class="modern-summary-section earnings-section">
                                                <div class="section-title">📊 Income Breakdown</div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">
                                                        <?php echo $this->lang->line('basic_salary'); ?>
                                                        <i class="fa fa-info-circle text-muted" style="font-size: 11px; margin-left: 4px;" data-toggle="tooltip" title="This month's actual basic pay (may differ from contract basic due to increments)"></i>
                                                    </span>
                                                    <span class="modern-summary-value">
                                                        <input type="text" name="basic" value="<?php echo $employee_payroll['basic']; ?>" id="basic" />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label"><?php echo $this->lang->line('earning'); ?></span>
                                                    <span class="modern-summary-value">
                                                        <input type="text" name="total_allowance" id="total_allowance" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['total_allowance']); ?>" />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row" style="background: rgba(0,0,0,0.02); border-top: 2px solid rgba(0,0,0,0.1); font-weight: 700;">
                                                    <span class="modern-summary-label">Gross Salary</span>
                                                    <span class="modern-summary-value" style="color: #2e7d32; font-size: 16px;">
                                                        <input type="text" name="gross_salary" id="gross_salary" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['basic'] + $employee_payroll['total_allowance']); ?>" />
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- DEDUCTIONS SECTION -->
                                            <div class="modern-summary-section deductions-section">
                                                <div class="section-title">💸 Deductions Applied</div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Other Deductions</span>
                                                    <span class="modern-summary-value deduction-value">
                                                        <input type="text" name="total_deduction" id="total_deduction" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['total_deduction']); ?>" />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Loss of Pay (LOP)</span>
                                                    <span class="modern-summary-value deduction-value">
                                                        <input type="text" name="leave_deduction" id="lop_deduction" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['leave_deduction']); ?>" readonly />
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- EPF SECTION -->
                                            <div class="modern-summary-section epf-section">
                                                <div class="section-title">🏦 Employees Provident Fund (EPF)</div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">EPF Wage Base</span>
                                                    <span class="modern-summary-value">
                                                        <input type="text" id="epf_wage" name="epf_wage" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['epf_wage'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employee Contribution (12%)</span>
                                                    <span class="modern-summary-value deduction-value" style="color: #1565c0 !important;">
                                                        <input type="text" id="employee_epf" name="employee_epf" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employee_epf'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employer PF (3.67%)</span>
                                                    <span class="modern-summary-value" style="color: #558b2f;">
                                                        <input type="text" id="employer_pf" name="employer_pf" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employer_pf'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employer EPS (8.33%)</span>
                                                    <span class="modern-summary-value" style="color: #6a1b9a;">
                                                        <input type="text" id="employer_eps" name="employer_eps" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employer_eps']); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employer EDLI (0.5%)</span>
                                                    <span class="modern-summary-value" style="color: #d84315;">
                                                        <input type="text" id="employer_edli" name="employer_edli" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employer_edli'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employer Admin (0.5%)</span>
                                                    <span class="modern-summary-value" style="color: #ef6c00;">
                                                        <input type="text" id="employer_admin" name="employer_admin" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employer_admin'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- ESI SECTION -->
                                            <div class="modern-summary-section esi-section">
                                                <div class="section-title">🏥 Employees State Insurance (ESI)</div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">ESI Wage Base</span>
                                                    <span class="modern-summary-value">
                                                        <input type="text" id="esi_wage" name="esi_wage" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['esi_wage'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employee Contribution (0.75%)</span>
                                                    <span class="modern-summary-value deduction-value" style="color: #c62828 !important;">
                                                        <input type="text" id="employee_esi" name="employee_esi" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employee_esi'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">Employer Contribution (3.25%)</span>
                                                    <span class="modern-summary-value" style="color: #d84315;">
                                                        <input type="text" id="employer_esi" name="employer_esi" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['employer_esi'] ?? 0); ?>" readonly />
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- TAX SECTION -->
                                            <?php if (!empty($employee_payroll['tax']) && $employee_payroll['tax'] > 0) { ?>
                                            <div class="modern-summary-section tax-section">
                                                <div class="section-title">📋 Income Tax Deduction</div>
                                                <div class="modern-summary-row">
                                                    <span class="modern-summary-label">TDS (New Regime FY 2025-26)</span>
                                                    <span class="modern-summary-value deduction-value">
                                                        <input type="text" name="tax_percent" id="tax_percent" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['tax']); ?>" />
                                                    </span>
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <!-- NET SALARY SECTION -->
                                            <div class="modern-summary-section net-section">
                                                <div class="modern-summary-row" style="padding: 14px 12px; font-weight: 700;">
                                                    <span class="modern-summary-label" style="color: #1b5e20;">💰 Net Salary</span>
                                                    <span class="modern-summary-value net-value">
                                                        <input type="text" name="net_salary" id="net_salary" value="<?php echo convertBaseAmountCurrencyFormat($employee_payroll['net_salary']); ?>" />
                                                    </span>
                                                </div>
                                                <span class="text-danger" id="err" style="display: block; padding: 0 12px 8px; font-size: 11px;"><?php echo form_error('net_salary'); ?></span>
                                            </div>

                                            <!-- Hidden Fields -->
                                            <input class="form-control" name="staff_id" value="<?php echo $result["id"]; ?>" type="hidden" />
                                            <input class="form-control" name="month" value="<?php echo $month; ?>" type="hidden" />
                                            <input class="form-control" name="year" value="<?php echo $year; ?>" type="hidden" />
                                            <input class="form-control" name="status" value="generated" type="hidden" />
                                            <input class="form-control" id="working_days" value="<?php echo $payroll_lop_summary['working_days']; ?>" type="hidden" />
                                            <input class="form-control" id="paid_days" value="<?php echo $payroll_lop_summary['paid_days']; ?>" type="hidden" />
                                            <input class="form-control" id="lop_days" value="<?php echo $payroll_lop_summary['lop_days']; ?>" type="hidden" />
                                        </div>
                                    </div>
                                </div><!--./col-md-4-->
                                <div class="col-md-12 col-sm-12">
                                    <?php if($is_calculated): ?>
                                        <button type="button" class="btn btn-secondary pull-right" disabled><i class="fa fa-eye"></i> <?php echo 'Payslip Calculated'; ?></button>
                                        <a href="<?php echo base_url() ?>admin/payroll" class="btn btn-default pull-right" style="margin-right: 10px;"><i class="fa fa-arrow-left"></i> Back</a>
                                    <?php else: ?>
                                        <button type="submit" id="contact_submit" class="btn btn-info pull-right"><i class="fa fa-check-circle"></i> <?php echo $this->lang->line('save'); ?></button>
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

    function parseAmount(value) {
        if (value === null || value === undefined) {
            return 0;
        }
        var normalized = String(value).replace(/,/g, '').trim();
        if (normalized === '') {
            return 0;
        }
        var parsed = parseFloat(normalized);
        return isNaN(parsed) ? 0 : parsed;
    }

    function findBasicPayRow() {
        var allowance_type = document.getElementsByName('allowance_type_id[]');
        var allowance_amount = document.getElementsByName('allowance_amount[]');
        for (var i = 0; i < allowance_type.length; i++) {
            if (!allowance_type[i] || !allowance_type[i].options || allowance_type[i].options.length === 0) {
                continue;
            }
            var selectedIndex = allowance_type[i].selectedIndex;
            if (selectedIndex < 0) {
                continue;
            }
            var selected = allowance_type[i].options[selectedIndex];
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

        var gross = parseAmount($("#gross_salary").val());
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
        var calcButton = $(".plusign");
        calcButton.prop('disabled', true);
        $("#err").text('');
        syncBasicFromEarnings();

        var payload = {
            payslip_id: $("input[name='id']").val(),  // Send payslip ID so calculatepreview can save to DB
            staff_id: $("input[name='staff_id']").val(),
            month: $("input[name='month']").val(),
            year: $("input[name='year']").val(),
            basic: $("#basic").val(),
            allowance_type_id: $("select[name='allowance_type_id[]']").map(function () { return $(this).val(); }).get(),
            allowance_amount: $("input[name='allowance_amount[]']").map(function () { return $(this).val(); }).get(),
            deduction_type_id: $("select[name='deduction_type_id[]']").map(function () { return $(this).val(); }).get(),
            deduction_amount: $("input[name='deduction_amount[]']").map(function () { return $(this).val(); }).get()
        };

        $.ajax({
            url: "<?php echo site_url('admin/payroll/calculatepreview'); ?>",
            method: "POST",
            dataType: "json",
            data: payload
        }).done(function (response) {
            if (!response || response.success !== true) {
                $("#err").text("Calculation failed. Please try again.");
                return;
            }

            syncEarningsFromBasic();
            $("#total_allowance").val(parseAmount(response.total_allowance).toFixed(2));
            $("#total_deduction").val(parseAmount(response.total_deduction).toFixed(2));
            $("#gross_salary").val(parseAmount(response.gross_salary).toFixed(2));
            $("#lop_deduction").val(parseAmount(response.leave_deduction).toFixed(2));
            $("#net_salary").val(parseAmount(response.net_salary).toFixed(2));

            if ($("#tax_percent").length) {
                $("#tax_percent").val(parseAmount(response.tds).toFixed(2));
            }

            // update EPF values (inputs always present now)
            $("#epf_wage").val(parseAmount(response.epf_wage).toFixed(2));
            $("#employee_epf").val(parseAmount(response.employee_epf).toFixed(2));
            $("#employer_pf").val(parseAmount(response.employer_pf).toFixed(2));
            $("#employer_eps").val(parseAmount(response.employer_eps).toFixed(2));
            $("#employer_edli").val(parseAmount(response.employer_edli).toFixed(2));
            $("#employer_admin").val(parseAmount(response.employer_admin).toFixed(2));

            // update ESI values
            if ($("#esi_wage").length) {
                $("#esi_wage").val(parseAmount(response.esi_wage).toFixed(2));
                $("#employee_esi").val(parseAmount(response.employee_esi).toFixed(2));
                $("#employer_esi").val(parseAmount(response.employer_esi).toFixed(2));
            }

            updateStatutoryBadgesFromGross();

            // if there's an existing payslip id, auto-submit the form to persist results
            if ($("input[name='id']").val()) {
                var netSalary = $("#net_salary").val();
                if (!netSalary) {
                    $("#err").text("<?php echo $this->lang->line('net_salary_should_not_be_empty') ?>");
                    return;
                }
                var form = document.getElementById('employeeform');
                if (form) {
                    form.submit();
                }
            }
        }).fail(function () {
            $("#err").text("Calculation failed. Please try again.");
        }).always(function () {
            calcButton.prop('disabled', false);
        });
    }

    function add_more() {
        var table = document.getElementById("tableID");
        var table_len = (table.children.length);
        var id = parseInt(table_len);
        
        // Build dropdown options for earning types
        var options = "<option value=''>Select Type</option>";
        earning_types.forEach(function(type) {
            options += "<option value='" + type.id + "' data-code='" + type.allowance_code + "'>" + type.allowance_name + " (" + type.allowance_code + ")</option>";
        });
        
        var row_html = "<div class='modern-item-row' id='row" + id + "'>" +
            "<input type='hidden' name='allowance_prev_id[]' value='0' />" +
            "<select class='form-control modern-item-type' name='allowance_type_id[]'>" + options + "</select>" +
            "<input type='text' class='form-control modern-item-amount' id='allowance_amount' name='allowance_amount[]' value='0' placeholder='Amount'>" +
            "<button type='button' onclick='delete_row(" + id + ")' class='modern-item-delete'><i class='fa fa-trash'></i></button>" +
            "</div>";
        table.insertAdjacentHTML('beforeend', row_html);
    }

    function delete_row(id) {
        $("#row" + id).remove();
    }

    function add_more_deduction() {
        var table = document.getElementById("tableID2");
        var table_len = (table.children.length);
        var id = parseInt(table_len);
        
        // Build dropdown options for deduction types
        var options = "<option value=''>Select Type</option>";
        deduction_types.forEach(function(type) {
            options += "<option value='" + type.id + "' data-code='" + type.allowance_code + "'>" + type.allowance_name + " (" + type.allowance_code + ")</option>";
        });
        
        var row_html = "<div class='modern-deduction-row' id='deduction_row" + id + "'>" +
            "<input type='hidden' name='deduction_prev_id[]' value='0' />" +
            "<select class='form-control modern-deduction-type' name='deduction_type_id[]'>" + options + "</select>" +
            "<input type='text' class='form-control modern-deduction-amount' id='deduction_amount' name='deduction_amount[]' value='0' placeholder='Amount'>" +
            "<button type='button' onclick='delete_deduction_row(" + id + ")' class='modern-item-delete'><i class='fa fa-trash'></i></button>" +
            "</div>";
        table.insertAdjacentHTML('beforeend', row_html);
    }

    function delete_deduction_row(id) {
        $("#deduction_row" + id).remove();
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
            $("#err").html("<?php echo $this->lang->line('net_salary_should_not_be_empty') ?>");
            $("#net_salary").focus();
            return false;
            event.preventDefault();
        } else {
            $("#err").html("");
        }
    });
</script>