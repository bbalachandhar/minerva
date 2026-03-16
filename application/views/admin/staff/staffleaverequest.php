<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php //echo $this->lang->line('human_resource'); ?>
            <?php
if ($this->rbac->hasPrivilege('apply_leave', 'can_add') && $this->uri->segment(2) == 'staff') {
    ?>
                <small class="pull-right"><a href="#addleave" onclick="addLeave()" role="button" class="btn btn-primary btn-sm checkbox-toggle pull-right edit_setting" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"><?php echo $this->lang->line('add_leave_request'); ?></a></small>
            <?php }?></h1>

    </section>
    <!-- Main content -->
    <section class="content">
        <div id="leave_feedback_container" style="display:none; margin-bottom:10px;"></div>
        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix pt5"><?php echo $this->lang->line('approve_leave_request'); ?></h3> <?php
if ($this->rbac->hasPrivilege('apply_leave', 'can_add') && $this->uri->segment(2) == 'staff') {
    ?>
                            <small class="pull-right"><a href="#addleave" onclick="addLeave()" role="button" class="btn btn-primary btn-sm checkbox-toggle pull-right edit_setting" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"><?php echo $this->lang->line('add_leave_request'); ?></a></small>
                        <?php }?>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="tab-pane active table-responsive no-padding">
                                    <div class="download_label"><?php echo $this->lang->line('approve_leave_request'); ?></div>
                                    <table class="table table-striped table-bordered table-hover example">
                                        <thead>
                                        <th><?php echo $this->lang->line('staff'); ?></th>
                                        <th><?php echo $this->lang->line('leave_type'); ?></th>
                                        <th><?php echo $this->lang->line('leave_date'); ?></th>
                                        <th><?php echo $this->lang->line('days'); ?></th>
                                        <th><?php echo $this->lang->line('apply_date'); ?></th>
                                        <th><?php echo $this->lang->line('recommender_status'); ?></th>
                                        <th><?php echo $this->lang->line('approver_status'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                        </thead>
                                        <tbody>
                                            <?php
$i = 0;
foreach ($leave_request as $key => $value) {
    ?>
                                                <tr>

                                                    <td><?php echo $value['name'] . " " . $value['surname'] . ' (' . $value['employee_id'] . ')'; ?></td>
                                                    <td><?php echo $value["type"] ?></td>
                                                    <td><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($value["leave_from"])) ?> - <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($value["leave_to"])) ?></td>

                                                    <td><?php echo $value["leave_days"]; ?></td>
                                                    <td><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($value["date"]));  ?></td>
                                                    <td><?php if(!empty($value['recommender_status'])){echo $this->lang->line(strtolower($value['recommender_status']));} ?></td>
                                                    <td><?php if(!empty($value['approver_status'])){echo $this->lang->line(strtolower($value['approver_status']));} ?></td>
                                                    <?php
$label = ''; // Initialize label
$status1 = ''; // Initialize status1
if ($value["status"] == "approved") {
        $status1 = 'approve';
        $label = "class='label label-success'";
    } else if ($value["status"] == "pending") {
        $status1 = 'pending';
        $label = "class='label label-warning'";
    } else if ($value["status"] == "disapprove" || $value["status"] == "disapproved" || $value["status"] == "rejected") {
        $status1 = 'disapprove';
        $label = "class='label label-danger'";
    } else if ($value["status"] == "recommended") {
        $status1 = 'recommended';
        $label = "class='label label-info'";
    }
    ?>
                                                    <td><span data-toggle="popover" class="detail_popover" data-original-title="" title=""><small <?php echo $label ?>><?php echo $status[$status1]; ?></small></span>

                                                        <div class="fee_detail_popover" style="display: none"><?php echo $this->lang->line('submitted_by'); ?>: <?php echo $value['applied_by']; ?></div></td>
                                                    <td class="pull-right no-print white-space-nowrap">
                                                        <a href="#leavedetails" onclick="getRecord('<?php echo $value["id"] ?>')" role="button" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>" ><i class="fa fa-reorder"></i></a>

                                                        <?php
                                                        $is_pre_recommender_stage = ($value['status'] == 'pending')
                                                            && (empty($value['recommender_status']) || $value['recommender_status'] == 'pending')
                                                            && (empty($value['approver_status']) || $value['approver_status'] == 'pending');
                                                        $is_owner = ((int) $value["staff_id"] === (int) $staff_id)
                                                            || ((int) ($value['applied_by'] ?? 0) === (int) $staff_id)
                                                            || ((string) ($value['applied_by'] ?? '') === (string) $this->customlib->getAdminSessionUserName());

                                                        if ($is_owner && $is_pre_recommender_stage) {
                                                        ?>
                                                            <a href="#addleave" onclick="editRecord('<?php echo $value["id"] ?>')" role="button" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>" ><i class="fa fa-pencil"></i></a>
                                                        <?php } ?>
                                                        <?php if (!empty($value['document_file'])) {?>
                                                            <a href="<?php echo base_url(); ?>admin/leaverequest/downloadleaverequestdoc/<?php echo $value['staff_id'] . "/" . $value['id']; ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                        <?php }
    ?>
                                                        <?php 
                                                        // Applicant can delete before recommender action; admin with delete privilege can also delete in same stage
                                                        if ($is_pre_recommender_stage) {
                                                            if ($is_owner || $this->rbac->hasPrivilege('approve_leave_request', 'can_delete')) { ?>
                                                                <a onclick="getDelete('<?php echo $value["id"] ?>','<?php echo $value["staff_id"] ?>')"  class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" ><i class="fa fa-remove"></i></a>
                                                            <?php }
                                                        }

                                                        if ($is_owner && !$is_pre_recommender_stage) { ?>
                                                            <span class="label label-default" data-toggle="tooltip" title="This request can no longer be edited/deleted once recommender action starts.">Locked after recommender action</span>
                                                        <?php }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
$i++;
}
?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="leavedetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="leavedetails_form" action="">
                        <div class="col-md-12 table-responsive">
                            <table class="table mb0 table-striped table-bordered examples">
                                <tr>
                                    <th width="15%"><?php echo $this->lang->line('name'); ?></th>
                                    <td width="35%"><span id='name'></span></td>
                                    <th width="15%"><?php echo $this->lang->line('staff_id'); ?></th>
                                    <td width="35%"><span id="employee_id"></span>
                                        <span class="text-danger"><?php echo form_error('leave_request_id'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('submitted_by'); ?></th>
                                    <td><span id="appliedby"></span></td>
                                    <th><?php echo $this->lang->line('leave_type'); ?></th>
                                    <td><span id="leave_type"></span>
                                        <input id="leave_request_id" name="leave_request_id" placeholder="" type="hidden" class="form-control" />
                                        <span class="text-danger"><?php echo form_error('leave_request_id'); ?></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('leave'); ?></th>
                                    <td><span id='leave_from'></span> - <label> </label><span id='leave_to'> </span> (<span id='days'></span>)
                                        <span class="text-danger"><?php echo form_error('leave_from'); ?></span></td>
                                    <th><?php echo $this->lang->line('apply_date'); ?></th>
                                    <td><span id="applied_date"></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('reason'); ?></th>
                                    <td><span id="remark"> </span></td>
                                    <th><?php echo $this->lang->line('recommender'); ?></th>
                                    <td><span id="recommender_name"></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('alternative_teacher'); ?></th>
                                    <td colspan="3"><span id="alternative_teacher_name"></span></td>
                                </tr>
                                <tr id="substitutions_row" style="display:none;">
                                    <td colspan="4">
                                        <h4 class="box-title"><?php echo $this->lang->line('substitution_details'); ?></h4>
                                        <table class="table table-striped table-bordered table-hover" id="substitutions_table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('date'); ?></th>
                                                    <th><?php echo $this->lang->line('time'); ?></th>
                                                    <th><?php echo $this->lang->line('substitute'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('recommender_status'); ?></th>
                                    <td><span id="recommender_status"></span></td>
                                    <th><?php echo $this->lang->line('recommender_remark'); ?></th>
                                    <td><span id="recommender_remark"></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('approver'); ?></th>
                                    <td><span id="approver_name"></span></td>
                                    <th><?php echo $this->lang->line('approver_status'); ?></th>
                                    <td><span id="approver_status"></span></td>
                                </tr>
                                <tr>
                                    <th><?php echo $this->lang->line('approver_remark'); ?></th>
                                    <td colspan="3"><span id="approver_remark"></span></td>
                                </tr>
                                <tr id="action_row" style="display: none;">
                                    <th><?php echo $this->lang->line('status'); ?></th>
                                    <td>
                                        <label class="radio-inline">
                                            <input type="radio" value="<?php echo "pending"; ?>" name="status" checked ><?php echo $this->lang->line('pending'); ?>
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" value="<?php echo "approved"; ?>" name="status"><?php echo $this->lang->line('approve'); ?>
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" value="<?php echo "disapproved"; ?>" name="status"><?php echo $this->lang->line('disapprove'); ?>
                                        </label>
                                        <span class="text-danger"><?php echo form_error('status'); ?></span>
                                    </td>
                                    <th id="note_label"><?php echo $this->lang->line('note'); ?></th>
                                    <td>
                                        <textarea class="form-control" style="resize: none;" rows="2" id="detailremark" name="detailremark" placeholder=""></textarea>
                                        <span class="text-danger"><?php echo form_error('address'); ?></span>
                                    </td>
                                </tr>
                                <tr id="action_button_row" style="display: none;">
                                    <td colspan="4">
                                        <button type="button" style="width: auto;"  class="btn btn-primary submit_schsetting pull-right" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('save'); ?></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addleave" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <?php
            $auto_adjust_paid_enabled = false;
            if (isset($sch_setting_detail) && is_object($sch_setting_detail)) {
                $auto_adjust_paid_enabled = ((int) ($sch_setting_detail->auto_adjust_lop_with_leaves ?? 0) === 1);
            } else {
                $live_setting = $this->setting_model->getSetting();
                $auto_adjust_paid_enabled = ((int) ($live_setting->auto_adjust_lop_with_leaves ?? 0) === 1);
            }
            ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('add_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="addleave_form" method="post" enctype="multipart/form-data" action="">
                        <!-- Request Type Picker -->
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-bottom:10px;">
                            <label><strong>Request Type</strong> <small class="req"> *</small></label>
                            <div class="btn-group btn-group-justified" data-toggle="buttons" style="margin-top:5px; display:table; width:100%;">
                                <?php if ($auto_adjust_paid_enabled) { ?>
                                <label class="btn btn-default active" id="btn_rt_claim" style="text-align:left; padding:10px 15px; white-space:normal;">
                                    <input type="radio" name="request_type" value="claim_leave" checked>
                                    <i class="fa fa-calendar-plus-o" style="font-size:15px;"></i> <strong>Claim Leave</strong>
                                    <div class="text-muted" style="font-weight:normal; font-size:12px; margin-top:2px;">Apply for claim-based leaves such as OD and CPL.</div>
                                </label>
                                <?php } else { ?>
                                <label class="btn btn-default active" id="btn_rt_lop" style="text-align:left; padding:10px 15px; white-space:normal;">
                                    <input type="radio" name="request_type" value="adjust_lop" checked>
                                    <i class="fa fa-exchange" style="font-size:15px;"></i> <strong>Apply Leave / Adjust Lop Absense</strong>
                                    <div class="text-muted" style="font-weight:normal; font-size:12px; margin-top:2px;">All leave types are available. Paid leaves adjust payroll LOP; non-paid leaves stay as leave-management records.</div>
                                </label>
                                <?php } ?>
                            </div>
                        </div>
<?php if ($this->rbac->hasPrivilege('approve_leave_request', 'can_add')) { ?>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label>
                                <?php echo $this->lang->line('role'); ?></label><small class="req"> *</small>
                            <select name="role" id="role"  class="form-control" onchange="getEmployeeName(this.value)">
                                <option value="" ><?php echo $this->lang->line('select') ?></option>
                                <?php foreach ($staffrole as $rolekey => $rolevalue) {
    ?>
                                    <option value="<?php echo $rolevalue["id"] ?>"><?php echo $rolevalue["type"] ?></option>
                                <?php }?>
                            </select>
                            <span class="text-danger"><?php echo form_error('role'); ?></span>
                        </div>
                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('name'); ?></label><small class="req"> *</small>
                            <select name="empname" id="empname" value=""onchange="getLeaveTypeDDL(this.value)"  class="form-control">
                                <option value="" selected><?php echo $this->lang->line('select') ?></option>
                            </select>
                            <span class="text-danger"><?php echo form_error('empname'); ?></span>
                        </div>
<?php } else { 
    $user_role = json_decode($this->customlib->getStaffRole());
?>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('role'); ?></label>
                            <input type="text" class="form-control" value="<?php echo $user_role->name; ?>" readonly>
                            <input type="hidden" name="role" id="role" value="<?php echo $user_role->id; ?>">
                        </div>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('name'); ?></label>
                            <input type="text" class="form-control" value="<?php echo $current_staff_details['name'] . ' ' . $current_staff_details['surname'] . ' (' . $current_staff_details['employee_id'] . ')'; ?>" readonly>
                            <input type="hidden" name="empname" id="empname" value="<?php echo $staff_id; ?>">
                        </div>
<?php } ?>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('apply_date'); ?></label><small class="req"> *</small>
                            <input type="text" id="applieddate" name="applieddate" value="<?php echo date($this->customlib->getSchoolDateFormat()) ?>" class="form-control" readonly>
                        </div>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6 ">
                            <label>
                                <?php echo $this->lang->line('leave_type'); ?></label><small class="req"> *</small>
                            <div id="leavetypeddl">
                                <select name="leave_type" id="leave_type" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($leavetype as $leave_key => $leave_value) {
    ?>
                                        <option value="<?php echo $leave_value["id"] ?>"><?php echo $leave_value["type"] ?></option>
                                    <?php }
?>
                                </select>
                            </div>
                            <small id="permission_quota_info" class="text-muted" style="display:none;"></small>
                            <small id="od_present_info" class="text-info" style="display:none; margin-top:4px;">
                                OD can be applied on Present attendance for official movement. Present-day OD is kept for audit and will not be used as payroll LOP credit.
                            </small>
                            <div id="permission_quota_warning" class="text-danger" style="display:none;"></div>
                            <div id="leave_balance_info_panel" style="display:none; margin-top:8px; padding:8px 12px; border-left:3px solid #ccc; background:#f9f9f9; font-size:13px;"></div>
                            <span class="text-danger"><?php echo form_error('leave_type'); ?></span>
                        </div>

                        <!-- LOP Adjust Section -->
                        <div id="lop_section" style="display:none;">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="alert alert-info" style="margin-bottom:10px;">
                                    <i class="fa fa-info-circle"></i>
                                    <strong>Adjust LOP Absence:</strong> Select the absent date in Leave From Date. Leave To Date will be auto-set to the same date.
                                </div>
                            </div>
                        </div>

                        <!-- Leave Date Range Section (used by both request types) -->
                        <div id="claim_dates_section">
                          <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6">
                                                        <label id="leave_from_date_label"><?php echo $this->lang->line('leave_from_date'); ?></label><small class="req"> *</small>
                                <input type="text" readonly id="leave_from_date" name="leave_from_date" class="form-control date" >
                                <input type="hidden" id="leave_from_date_iso" name="leave_from_date_iso" value="">
                            <!-- /.input group -->
                        </div>
                                                   <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6">
                                                                                                         <label id="leave_to_date_label"><?php echo $this->lang->line('leave_to_date'); ?></label><small class="req"> *</small>
                                                         <input type="text" readonly id="leave_to_date" name="leave_to_date" class="form-control date" >
                                                         <input type="hidden" id="leave_to_date_iso" name="leave_to_date_iso" value="">
                                                     <!-- /.input group -->
                                                 </div>
                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="help-block" style="margin-bottom:0;">Hint: Leave is not allowed on dates already marked Present. If attendance is marked Half Day for a date, only half-day leave can be applied for that date.</p>
                        </div>

                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6" id="leave_duration_group" style="display:none;">
                            <label>Leave Duration</label><small class="req"> *</small>
                            <select name="leave_duration_type" id="leave_duration_type" class="form-control">
                                <option value="full_day">Full Day</option>
                                <option value="first_half">First Half</option>
                                <option value="second_half">Second Half</option>
                            </select>
                            <p class="help-block" id="leave_duration_help" style="margin-bottom:0;">For half-day leave, from/to date must be same day.</p>
                            <p class="help-block" style="margin-bottom:0;">Hint: Leave is not allowed on dates already marked Present. If attendance is marked Half Day for a date, only half-day leave can be applied for that date.</p>
                        </div>
                        </div><!-- end #claim_dates_section -->
                         
                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('recommender'); ?></label>
                            <input type="text" class="form-control" id="recommender_display" value="<?php echo $recommender_info; ?>" readonly>
                        </div>
                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('approver'); ?></label>
                            <input type="text" class="form-control" id="approver_display" value="<?php echo $approver_info; ?>" readonly>
                            <div id="approver_config_warning" class="text-danger" style="margin-top:5px; display:none;">Leave approver is not configured. Please configure in System Settings → Leave Policy.</div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6" id="alternative_teacher_group" style="display:none;">
                            <label><?php echo $this->lang->line('alternative_teacher'); ?></label>
                            <select name="alternative_teacher_id" id="alternative_teacher_id" class="form-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($potential_substitutes as $substitute) { ?>
                                    <option value="<?php echo $substitute['id']; ?>"><?php echo $substitute['name'] . ' ' . $substitute['surname'] . ' (' . $substitute['employee_id'] . ')'; ?></option>
                                <?php } ?>
                            </select>
                            <span class="text-danger"><?php echo form_error('alternative_teacher_id'); ?></span>
                        </div>

                                                 <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                                     <h4 class="modal-title section-title" id="substitution_heading" style="display:none;"><?php echo $this->lang->line('substitution_details'); ?></h4>
                                                 </div>
                                                 <div id="timetable_section" class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="display: none;">
                                                     <div class="form-group">
                                                         <label><?php echo $this->lang->line('your_timetable'); ?></label>
                                                         <div id="timetable_display">
                                                             <!-- Timetable will be dynamically loaded here -->
                                                         </div>
                                                     </div>
                                                     <div class="form-group">
                                                         <label><?php echo $this->lang->line('suggest_substitute'); ?></label>
                                                         <div id="substitution_fields">
                                                             <!-- Substitution fields will be dynamically loaded here -->
                                                         </div>
                                                     </div>
                                                 </div>
                         
                                                 <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-6">
                                                     <label><?php echo $this->lang->line('reason'); ?></label><br/>
                                                     <textarea name="reason" id="reason" style="resize: none;" rows="4" class="form-control"></textarea>
                                                     <input type="hidden" name="leaverequestid" id="leaverequestid">
                                                 </div>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <label><?php echo $this->lang->line('attach_document'); ?></label>
                            <input type="file" id="file" name="userfile" class="filestyle form-control">
                            <input type="hidden" id="filename" name="filename" >
                        </div>
                        <div class="form-group  col-xs-12 col-sm-12 col-md-12 col-lg-6">
                            <input type="hidden" name="addstatus" id="addstatus" value="pending">
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <button type="submit" id="submitbtn" class="btn btn-primary submit_addLeave pull-right" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('save'); ?></button>
                            <input type="reset"  name="resetbutton" id="resetbutton" style="display:none">
                            <button type="button" style="display: none;" id="clearform" onclick="clearForm(this.form)" class="btn btn-primary submit_addLeave pull-right" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $this->lang->line('clear'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    /*--dropify--*/
    $(document).ready(function () {
        // Basic
        $('.filestyle').dropify();
    });
    /*--end dropify--*/
</script>

<script type="text/javascript">
    var LEAVE_DATE_FORMAT = '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']); ?>';
    var AUTO_ADJUST_LOP_WITH_PAID_LEAVES = <?php echo $auto_adjust_paid_enabled ? 'true' : 'false'; ?>;

    function formatDateToIso(dateObj) {
        if (!dateObj || Object.prototype.toString.call(dateObj) !== '[object Date]' || isNaN(dateObj.getTime())) {
            return '';
        }
        var y = dateObj.getFullYear();
        var m = ('0' + (dateObj.getMonth() + 1)).slice(-2);
        var d = ('0' + dateObj.getDate()).slice(-2);
        return y + '-' + m + '-' + d;
    }

    function syncLeaveIsoDates() {
        var fromDateObj = null;
        var toDateObj = null;

        if (typeof $('#leave_from_date').datepicker === 'function') {
            fromDateObj = $('#leave_from_date').datepicker('getDate');
        }
        if (typeof $('#leave_to_date').datepicker === 'function') {
            toDateObj = $('#leave_to_date').datepicker('getDate');
        }

        $('#leave_from_date_iso').val(formatDateToIso(fromDateObj));
        $('#leave_to_date_iso').val(formatDateToIso(toDateObj));
    }

    function initLeaveDatepickers() {
        if (typeof $('#leave_from_date').datepicker !== 'function' || typeof $('#leave_to_date').datepicker !== 'function') {
            return;
        }

        $('#leave_from_date').datepicker('destroy').datepicker({
            autoclose: true,
            format: LEAVE_DATE_FORMAT,
            todayHighlight: true
        }).on('changeDate', function () {
            syncLeaveIsoDates();
            if (($('input[name="request_type"]:checked').val() || 'claim_leave') === 'adjust_lop') {
                var fromDate = $('#leave_from_date').val();
                var fromIso = $('#leave_from_date_iso').val();
                $('#leave_to_date').val(fromDate);
                $('#leave_to_date_iso').val(fromIso);
            }
        });

        $('#leave_to_date').datepicker('destroy').datepicker({
            autoclose: true,
            format: LEAVE_DATE_FORMAT,
            todayHighlight: true
        }).on('changeDate', function () {
            syncLeaveIsoDates();
        });

        syncLeaveIsoDates();
    }

    var LEAVE_POLICY = {
        substitutionRequiredRoles: <?php echo json_encode(array_values($leave_management_policy['substitution_required_roles'] ?? [])); ?>,
        selfApproveRoles: <?php echo json_encode(array_values($leave_management_policy['self_approve_roles'] ?? [])); ?>,
        pastDateAllowedRoles: <?php echo json_encode(array_values($leave_management_policy['past_date_allowed_roles'] ?? [])); ?>,
        halfDayEnabled: <?php echo !empty($leave_management_policy['half_day_enabled']) ? 'true' : 'false'; ?>,
        halfDayAllowedRoles: <?php echo json_encode(array_values($leave_management_policy['half_day_allowed_roles'] ?? [])); ?>,
        halfDayAllowedTypes: <?php echo json_encode(array_values($leave_management_policy['half_day_allowed_types'] ?? [])); ?>
    };
    var CURRENT_USER_IS_ADMIN_OR_SUPERADMIN = <?php echo !empty($is_admin_or_super_admin) ? 'true' : 'false'; ?>;
    var INITIAL_APPROVER_CONFIGURED = <?php echo !empty($leave_approver_configured) ? 'true' : 'false'; ?>;

    function setPersistentLeaveFeedback(message, type) {
        if (!window.sessionStorage) {
            return;
        }
        var payload = {
            message: message || '',
            type: type || 'success'
        };
        sessionStorage.setItem('leave_feedback_message', JSON.stringify(payload));
    }

    function renderPersistentLeaveFeedback() {
        if (!window.sessionStorage) {
            return;
        }

        var raw = sessionStorage.getItem('leave_feedback_message');
        if (!raw) {
            return;
        }

        sessionStorage.removeItem('leave_feedback_message');

        var payload = null;
        try {
            payload = JSON.parse(raw);
        } catch (e) {
            payload = null;
        }

        if (!payload || !payload.message) {
            return;
        }

        var cssClass = payload.type === 'error' ? 'alert alert-danger alert-dismissible' : 'alert alert-success alert-dismissible';
        var html = '';
        html += '<div class="' + cssClass + '">';
        html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        html += payload.message;
        html += '</div>';

        $('#leave_feedback_container').html(html).show();

        setTimeout(function () {
            $('#leave_feedback_container .alert').fadeOut(300, function () {
                $('#leave_feedback_container').hide().empty();
            });
        }, 3000);
    }

    function syncApproverConfigWarning(isConfigured) {
        if (isConfigured) {
            $('#approver_config_warning').hide();
        } else {
            $('#approver_config_warning').show();
        }
    }

    function getSelectedRoleId() {
        return parseInt($('#role').val(), 10) || 0;
    }

    function getSelectedLeaveTypeId() {
        return parseInt($('#leave_type').val(), 10) || 0;
    }

    function shouldRequireSubstitution() {
        var roleId = getSelectedRoleId();
        var leaveTypeId = getSelectedLeaveTypeId();
        if (!roleId || !leaveTypeId) {
            return false;
        }
        if (LEAVE_POLICY.substitutionRequiredRoles.indexOf(roleId) === -1) {
            return false;
        }
        var leaveTypeText = ($('#leave_type option:selected').text() || '').toLowerCase().trim();
        if (leaveTypeText === 'on duty' || leaveTypeText === 'od') {
            return false;
        }
        return true;
    }

    function canApplyHalfDay() {
        if (!LEAVE_POLICY.halfDayEnabled) {
            return false;
        }

        var roleId = getSelectedRoleId();
        var leaveTypeId = getSelectedLeaveTypeId();
        if (!roleId || !leaveTypeId) {
            // Show half-day selector early; backend enforces exact role/type eligibility on submit.
            return true;
        }

        if (LEAVE_POLICY.halfDayAllowedRoles.length > 0 && LEAVE_POLICY.halfDayAllowedRoles.indexOf(roleId) === -1) {
            return false;
        }
        if (LEAVE_POLICY.halfDayAllowedTypes.length > 0 && LEAVE_POLICY.halfDayAllowedTypes.indexOf(leaveTypeId) === -1) {
            return false;
        }

        return true;
    }

    function syncHalfDayDateBehavior() {
        var duration = ($('#leave_duration_type').val() || 'full_day');
        var isHalfDay = (duration === 'first_half' || duration === 'second_half');
        if (isHalfDay) {
            var fromDate = $('#leave_from_date').val();
            if (fromDate) {
                $('#leave_to_date').val(fromDate);
            }
            $('#leave_to_date').prop('readonly', true);
            $('#leave_duration_help').text('Half-day leave applies only for a single date.');
        } else {
            $('#leave_to_date').prop('readonly', true);
            $('#leave_duration_help').text('For half-day leave, from/to date must be same day.');
        }
    }

    function toggleHalfDayUI() {
        if (canApplyHalfDay()) {
            $('#leave_duration_group').show();
        } else {
            $('#leave_duration_group').hide();
            $('#leave_duration_type').val('full_day');
        }
        syncHalfDayDateBehavior();
    }

    function canApplyPastDates() {
        if (CURRENT_USER_IS_ADMIN_OR_SUPERADMIN) {
            return true;
        }
        var roleId = getSelectedRoleId();
        if (!roleId) {
            return false;
        }
        return LEAVE_POLICY.pastDateAllowedRoles.indexOf(roleId) !== -1;
    }

    function initEmployeeSearchDropdown() {
        var $emp = $('#empname');
        if (!$emp.length || !$emp.is('select')) {
            return;
        }
        if (typeof $.fn.select2 !== 'function') {
            return;
        }

        if ($emp.hasClass('select2-hidden-accessible')) {
            $emp.select2('destroy');
        }

        $emp.select2({
            width: '100%',
            placeholder: '<?php echo addslashes($this->lang->line('name')); ?>',
            allowClear: true
        });
    }

    function applyPastDateRestrictionUI() {
        var allowPast = canApplyPastDates();
        var reqType = $('input[name="request_type"]:checked').val() || 'claim_leave';
        if (reqType === 'adjust_lop') {
            allowPast = true;
        }
        var $from = $('#leave_from_date');
        var $to = $('#leave_to_date');

        if (typeof $from.datepicker === 'function' && typeof $to.datepicker === 'function') {
            if (allowPast) {
                $from.datepicker('setStartDate', null);
                $to.datepicker('setStartDate', null);
            } else {
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                $from.datepicker('setStartDate', today);
                $to.datepicker('setStartDate', today);
            }
        }
    }

    function toggleSubstitutionUI() {
        var shouldShow = shouldRequireSubstitution();
        if (shouldShow) {
            $('#substitution_heading').show();
            $('#alternative_teacher_group').show();
        } else {
            $('#substitution_heading').hide();
            $('#alternative_teacher_group').hide();
            $('#timetable_section').hide();
            $('#timetable_display').html('');
            $('#substitution_fields').html('');
            $('#alternative_teacher_id').val('');
        }
    }

    function toggleRequestTypeUI(type) {
        if (type === 'adjust_lop') {
            $('#lop_section').show();
            $('#claim_dates_section').show();
            $('#leave_duration_group').hide();
            $('#leave_duration_type').val('full_day');
            $('#leave_from_date_label').text('Absent Date (LOP) to Adjust');
            $('#leave_to_date_label').text('Adjusted Through Leave Date');
            // Adjust mode is single-day only.
            var fromDate = $('#leave_from_date').val();
            var fromIso = $('#leave_from_date_iso').val();
            if (fromDate || fromIso) {
                $('#leave_to_date').val(fromDate);
                $('#leave_to_date_iso').val(fromIso);
            }

            // No substitution needed for a past-date LOP adjustment
            $('#substitution_heading').hide();
            $('#alternative_teacher_group').hide();
            $('#timetable_section').hide();
            $('#timetable_display').html('');
            $('#substitution_fields').html('');
            $('#alternative_teacher_id').val('');
        } else {
            $('#lop_section').hide();
            $('#claim_dates_section').show();
            $('#leave_from_date_label').text('<?php echo addslashes($this->lang->line('leave_from_date')); ?>');
            $('#leave_to_date_label').text('<?php echo addslashes($this->lang->line('leave_to_date')); ?>');
            toggleHalfDayUI();
        }
        // Reload leave type DDL filtered for the active mode
        var staffId = $('#empname').val();
        if (staffId) {
            getLeaveTypeDDL(staffId, '');
        }
        applyPastDateRestrictionUI();
    }

    function getDelete(id,staff_id) {
        var result = confirm("<?php echo $this->lang->line('delete_confirm'); ?>");
        if (result) {
            $.ajax({
                url: "<?php echo base_url(); ?>admin/leaverequest/remove/" + id+'/'+ staff_id,
                type: "POST",

                success: function (res)
                {
                    successMsg('<?php echo $this->lang->line("delete_message"); ?>');
                    window.location.reload(true);
                },
                error: function (xhr) { // if error occured
                    alert('<?php echo $this->lang->line("error_occurred_please_try_again"); ?>');
                },
                complete: function () {

                }
            });
        }
    }

    $(document).ready(function () {
        renderPersistentLeaveFeedback();
        getLeaveTypeDDL('<?php echo $staff_id ?>', '');
        $('.detail_popover').popover({
            placement: 'right',
            title: '',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });

        $('#reservation').daterangepicker({
            timePickerIncrement: 5, locale: {
                format: calendar_date_time_format
            }});
        });

    function addLeave() {
        $('.leave_title').html('<?php echo $this->lang->line('add_details'); ?>');
        $("#addleave_form")[0].reset(); // Reset the form fields to their initial state
        $('textarea[name="reason"]').text('');
        $('textarea[name="remark"]').text('');
        $('input[name="filename"]').val(''); // Clear filename if any

        // Clear recommender and approver info
        $('input[name="recommender"]').val('');
        $('input[name="approver"]').val('');

        // Ensure leave from/to dates are cleared/reset as well
        $('#leave_from_date').val('');
        $('#leave_to_date').val('');
        $('#leave_from_date_iso').val('');
        $('#leave_to_date_iso').val('');
        
        // Hide timetable section on new leave request
        $('#timetable_section').hide();
        $('#substitution_heading').hide();
        $('#alternative_teacher_group').hide();
        $('#recommender_display').val('<?php echo addslashes($recommender_info); ?>');
        $('#approver_display').val('<?php echo addslashes($approver_info); ?>');
        $('#approver_config_warning').hide();
        $('#leave_duration_type').val('full_day');

        // Show current date in UI for fresh form (system date remains enforced in backend)
        $('#applieddate').val('<?php echo date($this->customlib->getSchoolDateFormat()) ?>');

        // Reset request type based on attendance auto-adjust setting.
        // ON  => Claim Leave flow only
        // OFF => Apply Leave / Adjust LOP flow only
        var defaultReqType = AUTO_ADJUST_LOP_WITH_PAID_LEAVES ? 'claim_leave' : 'adjust_lop';
        $('input[name="request_type"][value="' + defaultReqType + '"]').prop('checked', true);
        if (defaultReqType === 'claim_leave') {
            $('#btn_rt_claim').addClass('active');
            $('#btn_rt_lop').removeClass('active');
        } else {
            $('#btn_rt_lop').addClass('active');
            $('#btn_rt_claim').removeClass('active');
        }
        toggleRequestTypeUI(defaultReqType);

        $('#addleave').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });

        toggleSubstitutionUI();
        toggleHalfDayUI();
        applyPastDateRestrictionUI();
    }

    function getRecord(id) {

        $('input:radio[name=status]').attr('checked', false);
        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/leaverequest/leaveRecord',
            type: 'POST',
            data: {id: id},
            dataType: "json",
            success: function (result) {

                $('input[name="leave_request_id"]').val(result.id);
                $('#employee_id').html(result.employee_id);
                $('#name').html(result.name + ' ' + result.surname);
                $('#leave_from').html(result.leavefrom);
                $('#leave_to').html(result.leaveto);
                $('#leave_type').html(result.type);
                $('#days').html(result.leave_days + ' Days');
                $('#remark').html(result.employee_remark);
                if (result.alternative_teacher_name) {
                    $('#alternative_teacher_name').html(result.alternative_teacher_name + ' ' + result.alternative_teacher_surname + ' (' + result.alternative_teacher_employee_id + ')');
                } else {
                    $('#alternative_teacher_name').html('<?php echo $this->lang->line('not_assigned'); ?>');
                }

                if (result.substitutions && result.substitutions.length > 0) {
                    var subHtml = '';
                    $.each(result.substitutions, function(i, sub) {
                        subHtml += '<tr>';
                        subHtml += '<td>' + sub.date + '</td>';
                        subHtml += '<td>' + sub.period + '</td>';
                        subHtml += '<td>' + (sub.name ? sub.name + ' ' + sub.surname + ' (' + sub.employee_id + ')' : '') + '</td>';
                        subHtml += '</tr>';
                    });
                    $('#substitutions_table tbody').html(subHtml);
                    $('#substitutions_row').show();
                } else {
                    $('#substitutions_row').hide();
                }

                $('#applied_date').html(result.date);
                $('#appliedby').html(result.applied_by);
                $("#detailremark").text(result.admin_remark);

                // Populate recommender and approver details
                $('#recommender_name').html(result.recommender_name ? result.recommender_name + ' ' + result.recommender_surname : '');
                $('#recommender_status').html(result.recommender_status_text ? result.recommender_status_text : '');
                $('#recommender_remark').html(result.recommender_remark);

                $('#approver_name').html(result.approver_name ? result.approver_name + ' ' + result.approver_surname : '');
                $('#approver_status').html(result.approver_status_text ? result.approver_status_text : '');
                $('#approver_remark').html(result.approver_remark);
                
                // Conditional display of action row and dynamic labels
                var current_user_id = <?php echo $this->customlib->getStaffID(); ?>;
                var can_manage_leave = <?php echo ($this->rbac->hasPrivilege('approve_leave_request', 'can_edit') || !empty($is_admin_or_super_admin)) ? 'true' : 'false'; ?>;
                var is_recommender = (result.recommender_id == current_user_id);
                var is_approver = (result.approver_id == current_user_id);

                var statusRadioHtml = '';
                var initialStatusValue = '';

                if (!can_manage_leave && is_recommender && result.approver_status == 'pending' && result.recommender_status != 'disapproved' && result.recommender_status != 'rejected' && !(is_approver && (result.recommender_status == 'approved' || result.recommender_status == 'recommended'))) {
                    $('#note_label').html('<?php echo $this->lang->line('recommender_remark'); ?>');
                    statusRadioHtml = `
                        <label class="radio-inline">
                            <input type="radio" value="pending" name="status" >${'<?php echo $this->lang->line('recommend_pending'); ?>'}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="approved" name="status" >${'<?php echo $this->lang->line('recommend_approve'); ?>'}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="disapproved" name="status" >${'<?php echo $this->lang->line('recommend_disapprove'); ?>'}
                        </label>
                    `;
                    // Set initial selected status based on recommender_status
                    if (result.recommender_status == 'approved' || result.recommender_status == 'recommended') {
                        initialStatusValue = 'approved';
                    } else if (result.recommender_status == 'disapproved' || result.recommender_status == 'rejected') {
                        initialStatusValue = 'disapproved';
                    } else {
                        initialStatusValue = 'pending'; // Default for pending recommendation
                    }
                    $('#action_row').show();
                    $('#action_button_row').show();
                } else if (!can_manage_leave && is_approver && (result.recommender_status == 'approved' || result.recommender_status == 'recommended') && result.approver_status == 'pending') {
                    $('#note_label').html('<?php echo $this->lang->line('approver_remark'); ?>');
                    statusRadioHtml = `
                        <label class="radio-inline">
                            <input type="radio" value="pending" name="status" >${'<?php echo $this->lang->line('final_pending'); ?>'}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="approved" name="status" >${'<?php echo $this->lang->line('final_approve'); ?>'}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="disapproved" name="status" >${'<?php echo $this->lang->line('final_disapprove'); ?>'}
                        </label>
                    `;
                     // Set initial selected status based on approver_status
                    if (result.approver_status == 'approved') {
                        initialStatusValue = 'approved';
                    } else if (result.approver_status == 'disapproved') {
                        initialStatusValue = 'disapproved';
                    } else {
                        initialStatusValue = 'pending'; // Default for pending approval
                    }
                    $('#action_row').show();
                    $('#action_button_row').show();
                } else if (can_manage_leave && result.status != 'approved' && result.status != 'disapproved') {
                    var recommenderStage = (result.recommender_status == 'pending' || result.recommender_status == '' || result.recommender_status == null) && result.approver_status == 'pending';
                    var approverStage = (result.recommender_status == 'approved' || result.recommender_status == 'recommended') && result.approver_status == 'pending';
                    var overrideManager = can_manage_leave && !is_recommender && !is_approver;

                    if (overrideManager) {
                        $('#note_label').html('<?php echo $this->lang->line('approver_remark'); ?>');
                        statusRadioHtml = `
                            <label class="radio-inline">
                                <input type="radio" value="pending" name="status" >${'<?php echo $this->lang->line('final_pending'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="approved" name="status" >${'<?php echo $this->lang->line('final_approve'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="disapproved" name="status" >${'<?php echo $this->lang->line('final_disapprove'); ?>'}
                            </label>
                        `;
                        if (result.approver_status == 'approved' || result.status == 'approved') {
                            initialStatusValue = 'approved';
                        } else if (result.approver_status == 'disapproved' || result.approver_status == 'rejected' || result.status == 'disapproved') {
                            initialStatusValue = 'disapproved';
                        } else {
                            initialStatusValue = 'pending';
                        }
                    } else if (recommenderStage) {
                        $('#note_label').html('<?php echo $this->lang->line('recommender_remark'); ?>');
                        statusRadioHtml = `
                            <label class="radio-inline">
                                <input type="radio" value="pending" name="status" >${'<?php echo $this->lang->line('recommend_pending'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="approved" name="status" >${'<?php echo $this->lang->line('recommend_approve'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="disapproved" name="status" >${'<?php echo $this->lang->line('recommend_disapprove'); ?>'}
                            </label>
                        `;
                        if (result.recommender_status == 'approved' || result.recommender_status == 'recommended') {
                            initialStatusValue = 'approved';
                        } else if (result.recommender_status == 'disapproved' || result.recommender_status == 'rejected') {
                            initialStatusValue = 'disapproved';
                        } else {
                            initialStatusValue = 'pending';
                        }
                    } else if (approverStage) {
                        $('#note_label').html('<?php echo $this->lang->line('approver_remark'); ?>');
                        statusRadioHtml = `
                            <label class="radio-inline">
                                <input type="radio" value="pending" name="status" >${'<?php echo $this->lang->line('final_pending'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="approved" name="status" >${'<?php echo $this->lang->line('final_approve'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="disapproved" name="status" >${'<?php echo $this->lang->line('final_disapprove'); ?>'}
                            </label>
                        `;
                        if (result.approver_status == 'approved') {
                            initialStatusValue = 'approved';
                        } else if (result.approver_status == 'disapproved' || result.approver_status == 'rejected') {
                            initialStatusValue = 'disapproved';
                        } else {
                            initialStatusValue = 'pending';
                        }
                    } else {
                        $('#note_label').html('<?php echo $this->lang->line('note'); ?>');
                        statusRadioHtml = `
                            <label class="radio-inline">
                                <input type="radio" value="pending" name="status" >${'<?php echo $this->lang->line('pending'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="approved" name="status" >${'<?php echo $this->lang->line('approve'); ?>'}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" value="disapproved" name="status" >${'<?php echo $this->lang->line('disapprove'); ?>'}
                            </label>
                        `;
                        if (result.status == 'approved') {
                            initialStatusValue = 'approved';
                        } else if (result.status == 'disapproved' || result.status == 'rejected') {
                            initialStatusValue = 'disapproved';
                        } else {
                            initialStatusValue = 'pending';
                        }
                    }

                    $('#action_row').show();
                    $('#action_button_row').show();
                } else {
                    $('#action_row').hide();
                    $('#action_button_row').hide();
                }

                // Append the generated radio buttons
                if (statusRadioHtml) {
                    $('#action_row td:first').html(statusRadioHtml);
                    $(`#action_row input[name=status][value='${initialStatusValue}']`).prop('checked', true);
                }

            }
        });

        $('#leavedetails').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    }
    ;

    $(document).on('click', '.submit_schsetting', function (e) {
        var $this = $(this);
        $this.button('loading');
        $.ajax({
            url: '<?php echo site_url("admin/leaverequest/leaveStatus") ?>',
            type: 'post',
            data: $('#leavedetails_form').serialize(),
            dataType: 'json',
            success: function (data) {

                if (data.status == "fail") {
                    var message = "";
                    $.each(data.error, function (index, value) {
                        message += value;
                    });
                    errorMsg(message);
                } else {
                    setPersistentLeaveFeedback(data.message, 'success');
                    window.location.reload(true);
                }

                $this.button('reset');
            }
        });
    });

    function checkStatus(status) {
        if (status == 'approved') {
            $("#reason").hide();
        } else if (status == 'pending') {
            $("#reason").hide();
        } else if (status == 'disapprove') {
            $("#reason").show();
        }
    }

    $(document).ready(function (e) {
        $("#addleave_form").on('submit', (function (e) {
            e.preventDefault();
            // If Adjust LOP mode, sync the single absent date to leave_from/to fields before submitting
            var reqType = $('input[name="request_type"]:checked').val() || 'claim_leave';
            if (reqType === 'adjust_lop') {
                var fromDisplay = $('#leave_from_date').val();
                var fromIso = $('#leave_from_date_iso').val();
                $('#leave_to_date').val(fromDisplay);
                $('#leave_to_date_iso').val(fromIso);
            }
            $.ajax({
                url: "<?php echo site_url("admin/leaverequest/addLeave") ?>",
                type: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                  beforeSend: function() {
                    $("#submitbtn").button('loading');
                 },
                success: function (data)
                {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                        $("#submitbtn").button('reset');
                    } else {
                        setPersistentLeaveFeedback(data.message, 'success');
                        window.location.reload(true);
                    }
                },
                error: function(xhr) { // if error occured
        $("#submitbtn").button('reset');
    },
    complete: function() {
        $("#submitbtn").button('reset');
    }
            });
        }));

        // Toggle UI when request type changes (Claim Leave vs Adjust LOP)
        $('input[name="request_type"]').on('change', function () {
            var type = $(this).val();
            $(this).closest('label').addClass('active').siblings('label').removeClass('active');
            toggleRequestTypeUI(type);
        });
    });

    function getEmployeeName(role) {
        var ne = "";
        var base_url = '<?php echo base_url() ?>';
        $("#empname").html('<option value=><?php echo $this->lang->line('select') ?></option>');
        var div_data = "";
        $.ajax({
            type: "POST",
            url: base_url + "admin/staff/getEmployeeByRole",
            data: {'role': role},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj)
                {
                    div_data += "<option value='" + obj.id + "' >" + obj.name + " " + obj.surname + " " + "(" + obj.employee_id + ")</option>";
                });

                $('#empname').append(div_data);
                initEmployeeSearchDropdown();
            }
        });
    }

    // Function to get and set recommender/approver info
    $(document).on('change', '#empname', function() {
        var staff_id = $(this).val();
        if (staff_id) {
            var base_url = '<?php echo base_url() ?>';
            $.ajax({
                type: "POST",
                url: base_url + "admin/leaverequest/getRecommenderApproverInfo",
                data: {'staff_id': staff_id, 'role_id': getSelectedRoleId()},
                dataType: "json",
                success: function (response) {
                    if (response.status === 'success') {
                        $('#recommender_display').val(response.recommender_info);
                        $('#approver_display').val(response.approver_info);
                        syncApproverConfigWarning(!!response.approver_configured);
                    } else {
                        $('#recommender_display').val('<?php echo $this->lang->line('not_assigned'); ?>');
                        $('#approver_display').val('<?php echo $this->lang->line('not_assigned'); ?>');
                        syncApproverConfigWarning(false);
                    }
                }
            });
        } else {
            $('#recommender_display').val('');
            $('#approver_display').val('');
            $('#approver_config_warning').hide();
        }

        toggleSubstitutionUI();
        // Reset balance panel when staff changes (leave type will also change)
        $('#leave_balance_info_panel').hide().html('');
    });

    function setEmployeeName(role, id = '') {
        var ne = "";
        var base_url = '<?php echo base_url() ?>';
        $("#empname").html("<option value=><?php echo $this->lang->line('select') ?></option>");
        var div_data = "";
        $.ajax({
            type: "POST",
            url: base_url + "admin/staff/getEmployeeByRole",
            data: {'role': role},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj)
                {
                    if (obj.employee_id == id) {
                        ne = 'selected';
                    } else {
                        ne = "";
                    }

                    div_data += "<option value='" + obj.id + "' " + ne + " >" + obj.name + " " + obj.surname + " " + "(" + obj.employee_id + ")</option>";
                });

                $('#empname').append(div_data);
                initEmployeeSearchDropdown();
            }
        });
    }

    function getLeaveTypeDDL(id, lid = '') {
        var base_url = '<?php echo base_url() ?>';
        var mode = $('input[name="request_type"]:checked').val() || 'claim_leave';
        $.ajax({
            url: base_url + 'admin/leaverequest/countLeave/' + id,
            type: 'POST',
            data: {lid: lid, mode: mode},
            success: function (result) {
                $("#leavetypeddl").html(result);
                updatePermissionQuota();
                toggleOdPresentInfo();
            }
        });
    }

    function editRecord(id) {
        $('.leave_title').html('<?php echo $this->lang->line('edit_details'); ?>');
        var leave_from = '05/01/2018';
        var leave_to = '05/10/2018';
        $('textarea[name="reason"]').text('');
        $('textarea[name="remark"]').text('');
        $('input:radio[name=addstatus]').attr('checked', false);

        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/leaverequest/leaveRecord',
            type: 'POST',
            data: {id: id},
            dataType: "json",
            success: function (result) {

                leave_from = result.leavefrom;
                leave_to = result.leaveto;

                setEmployeeName(result.staff_role, result.employee_id);
                getLeaveTypeDDL(result.staff_id, result.lid);
                $('#role').val(result.staff_role);

                $('input[name="applieddate"]').val(result.date);
                $('input[name="leavefrom"]').val(new Date(result.leave_from).toString(calendar_date_time_format));
                $('input[name="filename"]').val(result.document_file);

                $('#leave_from_date').val(result.leavefrom);
                $('#leave_to_date').val(result.leaveto);
                $('#leave_duration_type').val(result.leave_duration_type ? result.leave_duration_type : 'full_day');
                syncLeaveIsoDates();

                $('input[name="leaverequestid"]').val(id);
                $('textarea[name="reason"]').text(result.employee_remark);
                $('#addstatus').val(result.status);

                if (result.alternative_teacher_id) {
                    $('#alternative_teacher_id').val(result.alternative_teacher_id);
                } else {
                    $('#alternative_teacher_id').val('');
                }
                $('#reservation').daterangepicker({
                    startDate: leave_from,
                    endDate: leave_to,
                    timePickerIncrement: 5, locale: {
                        format: calendar_date_time_format
                    }
                });

                toggleHalfDayUI();
            }
        });

        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['m' => 'mm', 'd' => 'dd', 'Y' => 'yyyy']) ?>';

        $('#addleave').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    }
    ;

    function clearForm(oForm) {
        var elements = oForm.elements;
        for (i = 0; i < elements.length; i++) {
            field_type = elements[i].type.toLowerCase();
            switch (field_type) {

                case "text":
                case "password":
                case "hidden":

                    elements[i].value = "";
                    break;

                case "select-one":
                case "select-multi":
                    elements[i].selectedIndex = "";
                    break;

                default:
                    break;
            }
        }
    }

    $(document).ready(function() {
        initEmployeeSearchDropdown();
        initLeaveDatepickers();
        // Event listeners for date fields
        $('#leave_from_date, #leave_to_date, #empname, #role').change(function() {
            syncLeaveIsoDates();
            var staff_id = $('#empname').val();
            var leave_from_date = $('#leave_from_date').val();
            var leave_to_date = $('#leave_to_date').val();
            toggleSubstitutionUI();
            toggleHalfDayUI();
            applyPastDateRestrictionUI();
            
            if (staff_id && leave_from_date && leave_to_date && shouldRequireSubstitution()) {
                loadTimetableAndSubstitutes(staff_id, leave_from_date, leave_to_date);
            } else {
                $('#timetable_section').hide();
            }
            updatePermissionQuota();
        });

        $(document).on('change', '#leave_type', function() {
            toggleSubstitutionUI();
            toggleHalfDayUI();
            applyPastDateRestrictionUI();
            toggleOdPresentInfo();
            var staff_id = $('#empname').val();
            var leave_from_date = $('#leave_from_date').val();
            var leave_to_date = $('#leave_to_date').val();
            if (staff_id && leave_from_date && leave_to_date && shouldRequireSubstitution()) {
                loadTimetableAndSubstitutes(staff_id, leave_from_date, leave_to_date);
            } else {
                $('#timetable_section').hide();
            }
            updatePermissionQuota();
            updateLeaveBalanceInfo();
        });

        toggleSubstitutionUI();
        toggleHalfDayUI();
        applyPastDateRestrictionUI();
        toggleOdPresentInfo();
    });

    $(document).on('change', '#leave_duration_type', function() {
        syncHalfDayDateBehavior();
        syncLeaveIsoDates();
    });

    function updatePermissionQuota() {
        var staff_id = $('#empname').val();
        var leave_type_id = $('#leave_type').val();
        var leave_from_date = $('#leave_from_date').val();
        var base_url = '<?php echo base_url() ?>';

        $('#permission_quota_info').hide().text('');
        $('#permission_quota_warning').hide().text('');

        if (!staff_id || !leave_type_id) {
            return;
        }

        $.ajax({
            url: base_url + 'admin/leaverequest/permissionQuota',
            type: 'POST',
            dataType: 'json',
            data: {
                staff_id: staff_id,
                leave_type_id: leave_type_id,
                leave_from_date: leave_from_date
            },
            success: function (response) {
                if (!response || response.status !== 'success') {
                    return;
                }
                if (!response.is_permission) {
                    return;
                }

                var infoText = 'Permission quota this month: ' + response.quota + '. Used: ' + response.used + '. Remaining: ' + response.remaining + '.';
                $('#permission_quota_info').text(infoText).show();

                if (response.remaining <= 0) {
                    var warnText = 'You have consumed your monthly permission quota.';
                    $('#permission_quota_warning').text(warnText).show();
                    alert(warnText);
                }
            }
        });
    }

    function toggleOdPresentInfo() {
        var leaveTypeText = ($('#leave_type option:selected').text() || '').toLowerCase().trim();
        if (leaveTypeText === 'on duty' || leaveTypeText === 'od') {
            $('#od_present_info').show();
        } else {
            $('#od_present_info').hide();
        }
    }

    function updateLeaveBalanceInfo() {
        var staff_id = $('#empname').val();
        var leave_type_id = $('#leave_type').val();
        var panel = $('#leave_balance_info_panel');

        panel.hide().html('');

        if (!staff_id || !leave_type_id) {
            return;
        }

        $.ajax({
            url: '<?php echo base_url() ?>admin/leaverequest/leaveTypeBalanceInfo',
            type: 'POST',
            dataType: 'json',
            data: { staff_id: staff_id, leave_type_id: leave_type_id },
            success: function (r) {
                if (!r || r.status !== 'success') { return; }

                var html = '';
                if (r.is_credit_earn) {
                    panel.css('border-left-color', '#00a65a');
                    html += '<strong style="color:#00a65a;">&#9650; On Duty / Movement Leave</strong> &nbsp;';
                    html += '<span>' + r.type_label + ' may offset Loss of Pay at payroll only when it qualifies under attendance rules.</span><br>';
                    if (r.allotted > 0) {
                        html += '<span>Allotted: <strong>' + r.allotted + '</strong> &nbsp;|&nbsp; '
                             + 'Used (approved): <strong>' + r.used_approved + '</strong></span><br>';
                    }
                } else if (r.is_claim_based) {
                    var availColor = r.available > 0 ? '#333' : '#c0392b';
                    panel.css('border-left-color', r.available > 0 ? '#00a65a' : '#c0392b');
                    html += '<strong>' + r.type_label + ' (Claim-Based Leave)</strong><br>';
                    html += '<span>Allotted: <strong>' + r.allotted + '</strong> &nbsp;|&nbsp; '
                         + 'Approved used: <strong>' + r.used_approved + '</strong>';
                    if (r.used_pending > 0) {
                        html += ' &nbsp;|&nbsp; Pending: <strong>' + r.used_pending + '</strong>';
                    }
                    html += ' &nbsp;|&nbsp; <span style="color:' + availColor + ';">Available: <strong>' + r.available + '</strong></span></span><br>';
                    html += '<small class="text-info">This leave is tracked as its own leave bucket and is not linked to On Duty credit.</small>';
                    if (r.available <= 0) {
                        html += '<br><small class="text-danger"><strong>No balance available.</strong></small>';
                    }
                } else if (r.is_balance_consume) {
                    var availColor = r.available > 0 ? '#333' : '#c0392b';
                    panel.css('border-left-color', r.available > 0 ? '#3c8dbc' : '#c0392b');
                    html += '<strong>Balance: ' + r.type_label + '</strong><br>';
                    html += '<span>Allotted: <strong>' + r.allotted + '</strong> &nbsp;|&nbsp; '
                         + 'Approved used: <strong>' + r.used_approved + '</strong>';
                    if (r.used_pending > 0) {
                        html += ' &nbsp;|&nbsp; Pending (reserved): <strong>' + r.used_pending + '</strong>';
                    }
                    html += ' &nbsp;|&nbsp; <span style="color:' + availColor + ';">Available: <strong>' + r.available + '</strong></span></span><br>';
                    if (r.application_driven) {
                        html += '<small class="text-info">Balance deducted immediately upon approval.</small>';
                    }
                    if (r.available <= 0) {
                        html += '<br><small class="text-danger"><strong>Insufficient balance.</strong> Your request may be rejected.</small>';
                    }
                } else if (r.is_credit_consumer) {
                    var availColor = r.available > 0 ? '#333' : '#c0392b';
                    panel.css('border-left-color', r.available > 0 ? '#00a65a' : '#c0392b');
                    html += '<strong>' + r.type_label + ' (Credit Consumer)</strong> &mdash; uses your <em>' + r.source_type_name + '</em> credit<br>';
                    html += '<span>Earned ' + r.source_type_name + ' credit: <strong>' + r.allotted + '</strong>';
                    html += ' &nbsp;|&nbsp; Used (approved): <strong>' + r.used_approved + '</strong>';
                    if (r.used_pending > 0) { html += ' &nbsp;|&nbsp; Pending: <strong>' + r.used_pending + '</strong>'; }
                    html += ' &nbsp;|&nbsp; <span style="color:' + availColor + ';">Available: <strong>' + r.available + '</strong></span></span><br>';
                    html += '<small class="text-info">Credit deducted from your ' + r.source_type_name + ' pool upon approval.</small>';
                    if (r.available <= 0) {
                        html += '<br><small class="text-danger"><strong>No credit available.</strong> Apply for ' + r.source_type_name + ' first.</small>';
                    }
                } else if (r.is_lop) {
                    panel.css('border-left-color', '#f39c12');
                    html += '<strong style="color:#f39c12;">Loss of Pay</strong> &nbsp;';
                    html += '<span>Apply only when unable to cover with available paid leave.</span>';
                }

                if (html) {
                    panel.html(html).show();
                }
            }
        });
    }

    function loadTimetableAndSubstitutes(staff_id, leave_from_date, leave_to_date) {
        var base_url = '<?php echo base_url() ?>';
        $.ajax({
            url: base_url + 'admin/leaverequest/getTimetableAndSubstitutes',
            type: 'POST',
            data: {
                staff_id: staff_id,
                leave_from_date: leave_from_date,
                leave_to_date: leave_to_date,
                role_id: getSelectedRoleId(),
                leave_type_id: getSelectedLeaveTypeId()
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (shouldRequireSubstitution()) {
                        $('#timetable_display').html(response.timetable_html);
                        $('#substitution_fields').html(response.substitution_html);
                        $('#timetable_section').show();
                    } else {
                        $('#timetable_section').hide();
                    }
                } else {
                    $('#timetable_section').hide();
                    errorMsg(response.message);
                }
            },
            error: function() {
                $('#timetable_section').hide();
                errorMsg('<?php echo $this->lang->line('error_fetching_timetable'); ?>');
            }
        });
    }


</script>