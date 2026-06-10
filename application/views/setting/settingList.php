<div class="content-wrapper">  
    <section class="content">
        <div class="row">
            
            <?php $this->load->view('setting/_settingmenu'); ?>
            
            <!-- left column -->
            <div class="col-md-10">            
                            
                <!-- general form elements -->
                <div class="box box-primary">                    
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-gear"></i> <?php echo $this->lang->line('general_setting'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    
                    <br>
                    
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <div class="alert alert-info"><?php echo $this->lang->line('note'); ?>: <?php echo $this->lang->line('after_saving_general_setting_please_once_logout_then_relogin_so_changes_will_be_come_in_effect'); ?> </div>
                        </div>
                    </div>            
            
                    <div class="">
                        <form role="form" id="schsetting_form" action="<?php //echo site_url('schsettings/ajax_schedit_new'); ?>" class="" method="post" enctype="multipart/form-data">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('school_name'); ?><small class="req"> *</small> </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="name" name="sch_name" value="<?php echo $result->name; ?>">
                                                <span class="text-danger"><?php echo form_error('name'); ?></span> <input type="hidden" name="sch_id" value="<?php echo $result->id; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('school_code'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="dise_code" name="sch_dise_code" value="<?php echo $result->dise_code; ?>">
                                                <span class="text-danger"><?php echo form_error('dise_code'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                     <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4">Institution Type<small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select id="institution_type" name="institution_type" class="form-control">
                                                    <option value="school" <?php if ($result->institution_type == 'school') echo 'selected'; ?>>School (K-12)</option>
                                                    <option value="college" <?php if ($result->institution_type == 'college') echo 'selected'; ?>>College/Higher Education</option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('institution_type'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end">Enable Staff Self Profile Edit</label>
                                            <div class="col-sm-8">
                                                <div class="material-switch">
                                                    <input id="staff_self_edit" name="staff_self_edit" type="checkbox" class="chk" value="1" <?php echo ($result->staff_self_edit == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="staff_self_edit" class="label-success"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-2"><?php echo $this->lang->line('address'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="address" name="sch_address" value="<?php echo $result->address; ?>"> <span class="text-danger"><?php echo form_error('address'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <input type="hidden" name="base_url" value="<?php echo $result->base_url; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('phone'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="phone" name="sch_phone" value="<?php echo $result->phone; ?>"><span class="text-danger"><?php echo form_error('phone'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('email'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control"  id="email" name="sch_email" value="<?php echo $result->email; ?>">
                                                <span class="text-danger"><?php echo form_error('email'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('website'); ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="website" name="sch_website" value="<?php echo isset($result->website) ? $result->website : ''; ?>">
                                                <span class="text-danger"><?php echo form_error('sch_website'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('academic_session'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('session'); ?><small class="req"> *</small> </label>
                                            <div class="col-sm-8">
                                                <select  id="session_id" name="sch_session_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($sessionlist as $session) {
                                                        ?>
                                                        <option value="<?php echo $session['id'] ?>" <?php
                                                        if ($session['id'] == $result->session_id) {
                                                            echo "selected";
                                                        }
                                                        ?>><?php echo $session['session'] ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('session_start_month'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select  id="start_month" name="sch_start_month" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($monthList as $key => $month) {
                                                        ?>
                                                        <option value="<?php echo $key ?>" <?php
                                                        if ($key == $result->start_month) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $month ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('start_month'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('transport_fee_type'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select id="transport_fee_type" name="transport_fee_type" class="form-control">
                                                    <option value="monthly" <?php if ($result->transport_fee_type == 'monthly') echo 'selected'; ?>>Monthly</option>
                                                    <option value="yearly" <?php if ($result->transport_fee_type == 'yearly') echo 'selected'; ?>>Yearly</option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('transport_fee_type'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('final_leave_approver'); ?></label>
                                            <div class="col-sm-8">
                                                <select id="leave_approver_id" name="leave_approver_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($staff_list as $staff) { ?>
                                                        <option value="<?php echo $staff['id']; ?>" <?php if (isset($result->leave_approver_id) && $result->leave_approver_id == $staff['id']) echo 'selected'; ?>>
                                                            <?php echo $staff['name'] . " " . $staff['surname'] . " (" . $staff['employee_id'] . ")"; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('leave_approver_id'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('date_time'); ?></h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('date_format'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                <select  id="date_format" name="sch_date_format" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($dateFormatList as $key => $dateformat) {
                                                        ?>
                                                        <option value="<?php echo $key ?>" <?php
                                                        if ($key == $result->date_format) {
                                                            echo "selected";
                                                        }
                                                        ?>><?php echo $dateformat; ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('date_format'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end"><?php echo $this->lang->line('timezone'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8"> 
                                                <select  id="language_id" name="sch_timezone" class="form-control" >
                                                    <option value="">--<?php echo $this->lang->line('select') ?>--</option>
                                                    <?php foreach ($timezoneList as $key => $timezone) {
                                                        ?>
                                                        <option value="<?php echo $key ?>" <?php
                                                        if ($key == $result->timezone) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $timezone ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('timezone'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-sm-5 text-lg-end"><?php echo $this->lang->line('start_day_of_week') ?><small class="req"> *</small></label>
                                            <div class="col-sm-7">
                                                <select  id="start_week" name="sch_start_week" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($daysList as $day_key => $day_value) {
                                                        ?>
                                                        <option value="<?php echo $day_key ?>" <?php
                                                        if ($day_key == $result->start_week) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $day_value ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('sch_start_week'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head"><?php echo $this->lang->line('currency') ?></h4>
                                    </div><!--./col-md-12-->                                    
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4"><?php echo $this->lang->line('currency_format'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-8">
                                                    <select  id="currency_format" name="currency_format" class="form-control" >
                                                    <option value="">
                                                    <?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($currency_formats as $cur_format_key => $cur_format) {
                                                        ?>
                                                        <option value="<?php echo $cur_format_key ?>" <?php
                                                        if ($cur_format_key == $result->currency_format) {
                                                            echo "selected";
                                                        }
                                                        ?> ><?php echo $cur_format; ?></option>
                                                            <?php } ?>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('currency_format'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 hidden">
                                        <div class="form-group row">
                                            <label class="col-sm-3"><?php echo $this->lang->line('currency_symbol_place'); ?><small class="req"> *</small></label>
                                            <div class="col-sm-9">
                                                <?php foreach ($currencyPlace as $currency_place_k => $currency_place_v) {
                                                    ?>
                                                    <label class="radio-inline hidden">
                                                        <input type="hidden" name="currency_place" value="<?php echo $currency_place_k; ?>" <?php
                                                        if ($result->currency_place == $currency_place_k) {
                                                            echo "checked";
                                                        }
                                                        ?>  ><?php echo $currency_place_v; ?>
                                                    </label>

                                                <?php } ?>
                                            </div>
                                            <span class="text-danger"><?php echo form_error('currency_symbol'); ?></span>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-3">Weekend Days <small class="req">*</small></label>
                                            <div class="col-sm-9">
                                                <div style="padding: 10px;">
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" name="weekend_days[]" value="0" 
                                                            <?php if (isset($result->weekend_days) && strpos($result->weekend_days, '0') !== false) echo 'checked'; ?>> Sunday
                                                    </label>
                                                    <label class="checkbox-inline">
                                                        <input type="checkbox" name="weekend_days[]" value="6" 
                                                            <?php if (isset($result->weekend_days) && strpos($result->weekend_days, '6') !== false) echo 'checked'; ?>> Saturday
                                                    </label>
                                                </div>
                                                <small class="text-muted">Select which days are considered weekends for working days calculation</small>
                                                <span class="text-danger"><?php echo form_error('weekend_days'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-3">Second Saturday Weekend</label>
                                            <div class="col-sm-9">
                                                <div class="material-switch">
                                                    <input id="isSecondSaturdayHoliday" name="isSecondSaturdayHoliday" type="checkbox" class="chk" value="1" <?php echo (isset($result->isSecondSaturdayHoliday) && $result->isSecondSaturdayHoliday == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="isSecondSaturdayHoliday" class="label-success"></label>
                                                </div>
                                                <small class="text-muted">Enable to mark second Saturday of every month as weekend</small>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-3">Fourth Saturday Weekend</label>
                                            <div class="col-sm-9">
                                                <div class="material-switch">
                                                    <input id="isFourthSaturdayHoliday" name="isFourthSaturdayHoliday" type="checkbox" class="chk" value="1" <?php echo (isset($result->isFourthSaturdayHoliday) && $result->isFourthSaturdayHoliday == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="isFourthSaturdayHoliday" class="label-success"></label>
                                                </div>
                                                <small class="text-muted">Enable to mark fourth Saturday of every month as weekend</small>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head">Monthly Leave Increment Automation</h4>
                                    </div><!--./col-md-12-->
                                    <div class="col-md-12">
                                        <div class="alert alert-info">
                                            <strong>Note:</strong> This feature automatically increments specified leave type by configured days each month and resets it to 0 at specified month.
                                            <br><strong>Example:</strong> CL (Casual Leave) can be set to increase by 1 day each month and reset to 0 in January.
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-sm-3">Enable Monthly Leave Increment</label>
                                            <div class="col-sm-9">
                                                <div class="material-switch">
                                                    <input id="monthly_leave_increment_enabled" name="monthly_leave_increment_enabled" type="checkbox" class="chk" value="1" <?php echo (isset($result->monthly_leave_increment_enabled) && $result->monthly_leave_increment_enabled == 1) ? 'checked="checked"' : ''; ?>>
                                                    <label for="monthly_leave_increment_enabled" class="label-success"></label>
                                                </div>
                                                <small class="text-muted">Enable automatic monthly leave increment and annual reset</small>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4">Leave Type to Increment</label>
                                            <div class="col-sm-8">
                                                <select id="monthly_increment_leave_type_id" name="monthly_increment_leave_type_id" class="form-control">
                                                    <option value="">Select Leave Type</option>
                                                    <?php foreach ($leave_types as $leave_type): ?>
                                                        <option value="<?php echo $leave_type['id']; ?>" <?php echo (isset($result->monthly_increment_leave_type_id) && $result->monthly_increment_leave_type_id == $leave_type['id']) ? 'selected' : ''; ?>>
                                                            <?php echo $leave_type['type']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted">Choose which leave type should be incremented monthly (e.g., Casual Leave)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end">Days to Increment per Month</label>
                                            <div class="col-sm-8">
                                                <input type="number" step="0.5" min="0" max="31" class="form-control" id="monthly_increment_days" name="monthly_increment_days" value="<?php echo isset($result->monthly_increment_days) ? $result->monthly_increment_days : 1.00; ?>">
                                                <small class="text-muted">Number of days to add each month (e.g., 1 for 1 day/month)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4">Reset Month <small class="text-muted">(optional)</small></label>
                                            <div class="col-sm-8">
                                                <select id="leave_reset_month" name="leave_reset_month" class="form-control">
                                                    <option value="" <?php echo (empty($result->leave_reset_month)) ? 'selected' : ''; ?>>— No automatic reset —</option>
                                                    <?php
                                                    $months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
                                                                   7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
                                                    foreach ($months as $month_num => $month_name):
                                                    ?>
                                                        <option value="<?php echo $month_num; ?>" <?php echo (isset($result->leave_reset_month) && $result->leave_reset_month == $month_num) ? 'selected' : ''; ?>>
                                                            <?php echo $month_name; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted">Month when leave count resets to 0 (typically start of financial/academic year). Leave blank to disable annual reset.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 text-lg-end">Last Processed</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" value="<?php echo isset($result->last_leave_increment_processed) && $result->last_leave_increment_processed ? date('d-M-Y', strtotime($result->last_leave_increment_processed)) : 'Never'; ?>" readonly>
                                                <small class="text-muted">Last date when monthly increment was processed</small>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-warning">
                                            <strong>Important:</strong> After enabling this feature, set up a cron job to run daily:<br>
                                            <code>0 2 * * * curl "<?php echo base_url(); ?>cron_leave_increment/process?secret_key=<?php echo $result->cron_secret_key; ?>"</code>
                                            <br><br>
                                            <strong>Backfill past months</strong> (run once per missed month, oldest first):<br>
                                            <code><?php echo base_url('cron_leave_increment/manual_process'); ?>?year=YYYY&amp;month=M</code><br>
                                            <em>Example — backfill March 2026:</em>
                                            <a href="<?php echo base_url('cron_leave_increment/manual_process'); ?>?year=<?php echo date('Y'); ?>&amp;month=3" target="_blank">
                                                <?php echo base_url('cron_leave_increment/manual_process'); ?>?year=<?php echo date('Y'); ?>&amp;month=3
                                            </a><br>
                                            This creates a <code>staff_monthly_leave_balance</code> row per staff member:
                                            <code>opening=prev_closing, earned=rule_days, closing=opening+earned</code>.<br><br>
                                            Or trigger for the <strong>current month</strong>:
                                            <a href="<?php echo base_url('cron_leave_increment/manual_process'); ?>" target="_blank">Run Manual Process</a>
                                        </div>
                                    </div>
                                </div><!--./row-->

                                <!-- Leave Increment Processing Monitor -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel panel-default" style="margin-top:10px;">
                                            <div class="panel-heading">
                                                <strong><i class="fa fa-bar-chart"></i> Leave Increment Processing Monitor</strong>
                                                <?php
                                                    $monitor_ts = mktime(0, 0, 0, (int)$leave_monitor_month, 1, (int)$leave_monitor_year);
                                                    $prev_ts = strtotime('-1 month', $monitor_ts);
                                                    $next_ts = strtotime('+1 month', $monitor_ts);
                                                    $prev_link = base_url('schsettings/index?leave_monitor_year=' . date('Y', $prev_ts) . '&leave_monitor_month=' . date('n', $prev_ts));
                                                    $curr_link = base_url('schsettings/index?leave_monitor_year=' . date('Y') . '&leave_monitor_month=' . date('n'));
                                                    $next_link = base_url('schsettings/index?leave_monitor_year=' . date('Y', $next_ts) . '&leave_monitor_month=' . date('n', $next_ts));
                                                ?>
                                                <span class="pull-right" style="font-weight:normal;">
                                                    <a href="<?php echo $prev_link; ?>">&larr; Previous</a>
                                                    &nbsp;|&nbsp;
                                                    <a href="<?php echo $curr_link; ?>">Current Month</a>
                                                    &nbsp;|&nbsp;
                                                    <a href="<?php echo $next_link; ?>">Next &rarr;</a>
                                                </span>
                                            </div>
                                            <div class="panel-body">
                                                <p style="margin-bottom:10px;">
                                                    <strong>Target Month:</strong>
                                                    <?php echo date('F Y', $monitor_ts); ?>
                                                    &nbsp;&nbsp;|&nbsp;&nbsp;
                                                    <strong>Active Staff:</strong>
                                                    <?php echo (int)$leave_increment_monitor['active_staff_count']; ?>
                                                </p>

                                                <?php if (empty($leave_increment_monitor['rows'])): ?>
                                                    <div class="alert alert-info" style="margin-bottom:0;">
                                                        No enabled leave increment rules found.
                                                    </div>
                                                <?php else: ?>
                                                    <?php if ($leave_increment_monitor['is_complete']): ?>
                                                        <div class="alert alert-success" style="margin-bottom:10px;">
                                                            <strong>Complete:</strong> All enabled leave types are fully processed for this month.
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-danger" style="margin-bottom:10px;">
                                                            <strong>Partial:</strong> Some leave types are not fully processed.
                                                            Run manual process for this month to auto-heal missing rows:
                                                            <a href="<?php echo base_url('cron_leave_increment/manual_process?year=' . (int)$leave_monitor_year . '&month=' . (int)$leave_monitor_month); ?>" target="_blank">
                                                                Run <?php echo date('M Y', $monitor_ts); ?> Manual Process
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                                                            <thead>
                                                                <tr style="background:#f5f5f5;">
                                                                    <th>Leave Type</th>
                                                                    <th style="text-align:center;">Rule (Days/Month)</th>
                                                                    <th style="text-align:center;">Processed</th>
                                                                    <th style="text-align:center;">Expected</th>
                                                                    <th style="text-align:center;">Missing</th>
                                                                    <th style="text-align:center;">Completion</th>
                                                                    <th style="text-align:center;">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($leave_increment_monitor['rows'] as $mr): ?>
                                                                    <?php
                                                                        $status_class = 'label-default';
                                                                        $status_text  = 'Not Started';
                                                                        if ($mr['status'] === 'complete') {
                                                                            $status_class = 'label-success';
                                                                            $status_text  = 'Complete';
                                                                        } elseif ($mr['status'] === 'partial') {
                                                                            $status_class = 'label-danger';
                                                                            $status_text  = 'Partial';
                                                                        }
                                                                    ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($mr['leave_type_name']); ?></td>
                                                                        <td style="text-align:center;"><?php echo number_format((float)$mr['increment_days'], 2); ?></td>
                                                                        <td style="text-align:center;"><?php echo (int)$mr['processed_rows']; ?></td>
                                                                        <td style="text-align:center;"><?php echo (int)$mr['expected_rows']; ?></td>
                                                                        <td style="text-align:center;"><?php echo (int)$mr['missing_rows']; ?></td>
                                                                        <td style="text-align:center;"><?php echo number_format((float)$mr['completion_percent'], 2); ?>%</td>
                                                                        <td style="text-align:center;"><span class="label <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                
                                <!-- Monthly Leave Increment Rules Table -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="settinghr"></div>
                                        <h4 class="session-head">
                                            Configured Leave Types for Auto Increment
                                            <button type="button" class="btn btn-sm btn-success pull-right" id="addLeaveRuleBtn">
                                                <i class="fa fa-plus"></i> Add New Rule
                                            </button>
                                        </h4>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" id="leaveRulesTable">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="35%">Leave Type</th>
                                                        <th width="20%">Days/Month</th>
                                                        <th width="15%">Status</th>
                                                        <th width="25%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($leave_increment_rules)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">
                                                                <i class="fa fa-info-circle"></i> No rules configured yet. Click "Add New Rule" to configure a leave type for auto increment.
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($leave_increment_rules as $index => $rule): ?>
                                                            <tr data-rule-id="<?php echo $rule['id']; ?>">
                                                                <td><?php echo $index + 1; ?></td>
                                                                <td>
                                                                    <strong><?php echo htmlspecialchars($rule['leave_type_name']); ?></strong>
                                                                </td>
                                                                <td>
                                                                    <span class="days-display"><?php echo number_format($rule['increment_days'], 2); ?></span>
                                                                    <input type="number" class="form-control form-control-sm days-edit" style="display:none; width: 100px;" 
                                                                           value="<?php echo $rule['increment_days']; ?>" step="0.5" min="0" max="31">
                                                                </td>
                                                                <td>
                                                                    <span class="label <?php echo $rule['enabled'] ? 'label-success' : 'label-danger'; ?> status-badge">
                                                                        <?php echo $rule['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-xs btn-primary editRuleBtn" title="Edit">
                                                                        <i class="fa fa-pencil"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-xs btn-success saveRuleBtn" style="display:none;" title="Save">
                                                                        <i class="fa fa-check"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-xs btn-default cancelRuleBtn" style="display:none;" title="Cancel">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-xs btn-<?php echo $rule['enabled'] ? 'warning' : 'info'; ?> toggleRuleBtn" 
                                                                            data-enabled="<?php echo $rule['enabled']; ?>" title="<?php echo $rule['enabled'] ? 'Disable' : 'Enable'; ?>">
                                                                        <i class="fa fa-<?php echo $rule['enabled'] ? 'ban' : 'check'; ?>"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-xs btn-danger deleteRuleBtn" title="Delete">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div><!--./row-->
                                
                                <input type="hidden" id="folder_path" name="folder_path" value="<?php echo FCPATH; ?>">                               
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <?php
                                if ($this->rbac->hasPrivilege('general_setting', 'can_edit')) {
                                    ?>
                                    <button type="button" class="btn btn-primary submit_schsetting pull-right edit_setting" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('save'); ?></button>
                                    <?php
                                }
                                ?>
                            </div>
                        </form>
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- new END -->

</div><!-- /.content-wrapper -->

<script type="text/javascript">

    var base_url = '<?php echo base_url(); ?>';

    $(".edit_setting").on('click', function (e) {
        var $this = $(this);
        $this.button('loading');
        $.ajax({
            url: '<?php echo site_url("schsettings/generalsetting") ?>',
            type: 'POST',
            data: $('#schsetting_form').serialize(),
            dataType: 'json',

            success: function (data) {

                if (data.status == "fail") {
                    var message = "";
                    $.each(data.error, function (index, value) {

                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(data.message);
                }

                $this.button('reset');
            }
        });
    });
</script><?php $this->load->view("setting/_leave_rules_scripts"); ?>
