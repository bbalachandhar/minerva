<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info"><div class="box-header ptbnull">
                        <h3 class="box-title titlefix"> <?php echo $this->lang->line('vehicle_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('vehicle', 'can_add')) { ?>
                            <button type="button" class="btn btn-sm btn-default pull-right" style="margin-right:5px;" data-toggle="modal" data-target="#vehicleImportModal"><i class="fa fa-upload"></i> Import</button>
                            <button type="button" class="btn btn-sm btn-primary pull-right" style="margin-right:5px;" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?></button>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div > <div class="download_label"><?php echo $this->lang->line('vehicle_list'); ?></div>
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('vehicle_number'); ?></th>
                                        <th><?php echo $this->lang->line('vehicle_model'); ?></th>
                                        <th><?php echo $this->lang->line('year_made'); ?></th>
                                        <th><?php echo $this->lang->line('registration_number'); ?></th>
                                        <th><?php echo $this->lang->line('chasis_number'); ?></th>
                                        <th><?php echo $this->lang->line('max_seating_capacity'); ?></th>
                                        <th><?php echo $this->lang->line('driver_name'); ?></th>
                                        <th><?php echo $this->lang->line('driver_license'); ?></th>
                                        <th><?php echo $this->lang->line('driver_contact'); ?></th>
                                        <th class="text-right noExport" width="10%"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                   
                                    foreach ($listVehicle as $key => $data) { ?>
                                        <tr>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover" ><?php echo $data['vehicle_no'] ?></a>
                                                </td>
                                                <td class="mailbox-name"> <?php echo $data['vehicle_model'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['manufacture_year'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['registration_number'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['chasis_number'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['max_seating_capacity'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['driver_name'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['driver_licence'] ?></td>
                                                <td class="mailbox-name"> <?php echo $data['driver_contact'] ?></td>

                                                <td class="mailbox-date pull-right no-print white-space-nowrap">
                                                        
                                                        <a class="btn btn-default btn-xs vehicledetails" data-id="<?php echo $data['id'] ?>"  data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>"><i class="fa fa-reorder"></i>
                                                        </a>
                                                        
                                                    <?php if ($this->rbac->hasPrivilege('vehicle', 'can_edit')) { ?>
                                                        <a class="btn btn-default btn-xs editvehicle" data-id="<?php echo $data['id'] ?>"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php }if ($this->rbac->hasPrivilege('vehicle', 'can_delete')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/vehicle/delete/<?php echo $data['id'] ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');"><i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== Expiry Status Dashboard ===== -->
    <?php
    $expiry_groups = ['critical'=>[],'warning'=>[],'info'=>[]];
    foreach ($upcoming_expiries as $e) {
        $d = (int)$e['days_remaining'];
        if ($d <= 5)       $expiry_groups['critical'][] = $e;
        elseif ($d <= 15)  $expiry_groups['warning'][]  = $e;
        else               $expiry_groups['info'][]      = $e;
    }
    $date_fmt = $this->customlib->getSchoolDateFormat();
    ?>
    <?php if (!empty($upcoming_expiries)): ?>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Vehicle Expiry Status — Next 30 Days</h3>
                        <div class="box-tools pull-right">
                            <span class="badge bg-red"><?php echo count($expiry_groups['critical']); ?> Critical (≤5 days)</span>
                            <span class="badge bg-yellow" style="margin-left:4px"><?php echo count($expiry_groups['warning']); ?> Warning (6-15 days)</span>
                            <span class="badge bg-blue" style="margin-left:4px"><?php echo count($expiry_groups['info']); ?> Upcoming (16-30 days)</span>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover" style="font-size:13px;margin-bottom:0;">
                            <thead>
                                <tr style="background:#f4f4f4;">
                                    <th>Vehicle No.</th>
                                    <th>Model</th>
                                    <th>Registration</th>
                                    <th>Document</th>
                                    <th>Expiry Date</th>
                                    <th style="width:120px">Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($upcoming_expiries as $e):
                                $d = (int)$e['days_remaining'];
                                $row_class = $d <= 5 ? 'danger' : ($d <= 15 ? 'warning' : 'info');
                                $badge_bg  = $d <= 5 ? '#d9534f' : ($d <= 15 ? '#f0ad4e' : '#31b0d5');
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><strong><?php echo htmlspecialchars($e['vehicle_no']); ?></strong></td>
                                <td><?php echo htmlspecialchars($e['vehicle_model']); ?></td>
                                <td><?php echo htmlspecialchars($e['registration_number']); ?></td>
                                <td><?php echo htmlspecialchars($e['expiry_label']); ?></td>
                                <td><?php echo $e['expiry_date'] ? date($date_fmt, strtotime($e['expiry_date'])) : '—'; ?></td>
                                <td>
                                    <span style="display:inline-block;background:<?php echo $badge_bg; ?>;color:#fff;padding:3px 10px;border-radius:12px;font-weight:700;font-size:12px;">
                                        <?php echo $d; ?> day<?php echo $d != 1 ? 's' : ''; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== Vehicle Expiry Notification Settings ===== -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bell"></i> Vehicle Expiry Notification Settings</h3>
                    </div>
                    <div class="box-body">
                        <form id="vehicleAssigneesForm">
                        <?php echo $this->customlib->getCSRF(); ?>

                        <!-- Row 1: Notification Recipients -->
                        <h4 style="margin:0 0 12px;font-size:14px;color:#555;border-bottom:1px solid #eee;padding-bottom:8px;">
                            <i class="fa fa-users text-warning"></i> Notification Recipients (up to 3 staff)
                        </h4>
                        <div class="row">
                            <?php foreach ([1,2,3] as $slot): ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Recipient <?php echo $slot; ?></label>
                                    <select name="assignee_<?php echo $slot; ?>" class="form-control veh-select2" style="width:100%">
                                        <option value="">-- Select Staff --</option>
                                        <?php foreach ($staffList as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php echo (isset($assigneesBySlot[$slot]) && $assigneesBySlot[$slot] == $s['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['name'] . ' ' . ($s['surname'] ?? '')); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Row 2: Notify Days -->
                        <?php $active_days = array_map('intval', explode(',', $notify_days)); ?>
                        <h4 style="margin:16px 0 12px;font-size:14px;color:#555;border-bottom:1px solid #eee;padding-bottom:8px;">
                            <i class="fa fa-calendar text-warning"></i> Send Alerts Before Expiry
                        </h4>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <?php foreach ([30, 15, 10, 5, 3] as $day): ?>
                                    <label class="checkbox-inline" style="margin-right:18px;font-size:14px;">
                                        <input type="checkbox" name="notify_days[]" value="<?php echo $day; ?>"
                                            <?php echo in_array($day, $active_days) ? 'checked' : ''; ?>>
                                        <strong><?php echo $day; ?></strong> days before
                                    </label>
                                    <?php endforeach; ?>
                                    <p class="help-block" style="margin-top:8px;font-size:12px;">
                                        Cron runs daily — one alert per threshold per vehicle. e.g. checking "30, 15, 5, 3" sends an email exactly 30 days before, again 15 days before, etc.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Channel toggles -->
                        <h4 style="margin:16px 0 12px;font-size:14px;color:#555;border-bottom:1px solid #eee;padding-bottom:8px;">
                            <i class="fa fa-paper-plane text-warning"></i> Delivery Channels
                        </h4>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="enable_email" value="1" <?php echo $enable_email ? 'checked' : ''; ?>>
                                        &nbsp;<strong>Email</strong>
                                        <small class="text-muted">(uses school SMTP settings)</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label>WhatsApp Template ID <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="wa_template_id" class="form-control input-sm"
                                           value="<?php echo htmlspecialchars($wa_template_id ?? ''); ?>"
                                           placeholder="e.g. HXxxxxxxxx or vehicle_expiry_reminder">
                                    <p class="help-block" style="font-size:11px;margin-top:4px;">
                                        Leave blank for email only. Variables: <code>{{vehicle_no}}</code> <code>{{expiry_type}}</code> <code>{{expiry_date}}</code> <code>{{days_remaining}}</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Cron setup + Save -->
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="box box-solid box-default" style="margin-bottom:0;border:1px solid #ddd;">
                                    <div class="box-body" style="padding:10px 14px;">
                                        <strong><i class="fa fa-clock-o text-info"></i> EC2 Cron Setup</strong>
                                        <p class="text-muted" style="margin:6px 0 4px;font-size:12px;">Run daily at 8:00 AM — add via <code>crontab -e</code> on EC2:</p>
                                        <code style="font-size:11px;display:block;word-break:break-all;background:#f9f9f9;padding:6px 10px;border-radius:4px;">
                                            0 8 * * * curl -s "<?php echo base_url(); ?>index.php/cron/vehicleExpiryReminder/<?php echo htmlspecialchars($this->setting_model->getSetting()->cron_secret_key ?? ''); ?>" &gt; /dev/null 2&gt;&amp;1
                                        </code>
                                        <p class="text-muted" style="margin:6px 0 0;font-size:11px;">
                                            <i class="fa fa-check text-success"></i> Already installed on all 7 instances via system cron.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5" style="padding-top:4px;">
                                <button type="submit" class="btn btn-warning btn-block" id="saveAssigneesBtn"
                                        data-loading-text="<i class='fa fa-spinner fa-spin'></i> Saving...">
                                    <i class="fa fa-save"></i> Save Notification Settings
                                </button>
                            </div>
                        </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('.veh-select2').select2({
        placeholder: '-- Select Staff --',
        allowClear: true,
        width: '100%'
    });
});

$('#vehicleAssigneesForm').on('submit', function(e) {
    e.preventDefault();
    var $btn = $('#saveAssigneesBtn');
    $btn.button('loading');
    $.ajax({
        url: '<?php echo site_url("admin/vehicle/saveAssignees"); ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                successMsg(res.message);
            } else {
                errorMsg(res.message);
            }
        },
        error: function() {
            errorMsg('An error occurred. Please try again.');
        },
        complete: function() {
            $btn.button('reset');
        }
    });
});
</script>

<div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('add_vehicle'); ?></h4>
            </div>

            <form id="addvehicleform" method="post" enctype="multipart/form-data">
                <div class="modal-body pb0 ptt10">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('vehicle_number'); ?></label><small class="req"> *</small>
                                        <input autofocus="" id="vehicle_no" name="vehicle_no" placeholder="" type="text" class="form-control"  value="<?php echo set_value('vehicle_no'); ?>" />
                                        <span class="text-danger"><?php echo form_error('vehicle_no'); ?></span>
                                    </div>
                                   
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('vehicle_model'); ?></label>
                                        <input id="vehicle_model" name="vehicle_model" placeholder="" type="text" class="form-control"  value="<?php echo set_value('vehicle_model'); ?>" />
                                        <span class="text-danger"><?php echo form_error('vehicle_model'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('year_made'); ?> </label>
                                        <input id="manufacture_year" name="manufacture_year" placeholder="" type="text" class="form-control"  value="<?php echo set_value('manufacture_year'); ?>" />
                                        <span class="text-danger"><?php echo form_error('manufacture_year'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('registration_number'); ?> </label>
                                        <input id="registration_number" name="registration_number" placeholder="" type="text" class="form-control"  value="<?php echo set_value('registration_number'); ?>" />
                                        <span class="text-danger"><?php echo form_error('registration_number'); ?></span>
                                    </div>
                                   
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('chasis_number'); ?> </label>
                                        <input id="chasis_number" name="chasis_number" placeholder="" type="text" class="form-control"  value="<?php echo set_value('chasis_number'); ?>" />
                                        <span class="text-danger"><?php echo form_error('chasis_number'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Engine Number</label>
                                        <input id="engine_number" name="engine_number" placeholder="" type="text" class="form-control" value="<?php echo set_value('engine_number'); ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('max_seating_capacity'); ?> </label>
                                        <input id="max_seating_capacity" name="max_seating_capacity" placeholder="" type="text" class="form-control"  value="<?php echo set_value('max_seating_capacity'); ?>" />
                                        <span class="text-danger"><?php echo form_error('max_seating_capacity'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('driver_name'); ?></label>
                                        <input id="driver_name" name="driver_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('driver_name'); ?>" />
                                        <span class="text-danger"><?php echo form_error('driver_name'); ?></span>
                                    </div>
                                   
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('driver_license'); ?></label>
                                        <input id=" driver_licence" name="driver_licence" placeholder="" type="text" class="form-control"  value="<?php echo set_value('driver_licence'); ?>" />
                                        <span class="text-danger"><?php echo form_error('driver_licence'); ?></span>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('driver_contact'); ?></label>
                                        <input id="driver_contact" name="driver_contact" placeholder="" type="text" class="form-control"  value="<?php echo set_value('intake'); ?>" />
                                        <span class="text-danger"><?php echo form_error('driver_contact'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                  <div class="form-group">
                                        <label ><?php echo  $this->lang->line('vehicle_photo'); ?></label>
                                        <input id="vehicle_photo" name="vehicle_photo" placeholder="" type="file" class="filestyle form-control" data-height="30"  value="<?php echo set_value('vehicle_photo'); ?>" />
                                        <span class="text-danger"><?php echo form_error('vehicle_photo'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Validity / Expiry Date Fields -->
                            <div class="row"><div class="col-sm-12"><hr class="mt5 mb10"><h5 class="text-muted mb10"><i class="fa fa-calendar"></i> Validity &amp; Expiry Dates</h5></div></div>

                            <div class="row">
                                <div class="col-sm-6"><div class="form-group"><label>FC Validity Start</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="fc_validity_start" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                                <div class="col-sm-6"><div class="form-group"><label>FC Validity End</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="fc_validity_end" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6"><div class="form-group"><label>Insurance Start</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="insurance_start" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                                <div class="col-sm-6"><div class="form-group"><label>Insurance End</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="insurance_end" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6"><div class="form-group"><label>Permit Expiry Start</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="permit_expiry_start" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                                <div class="col-sm-6"><div class="form-group"><label>Permit Expiry End</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="permit_expiry_end" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6"><div class="form-group"><label>Road Tax Start</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="road_tax_start" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                                <div class="col-sm-6"><div class="form-group"><label>Road Tax End</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="road_tax_end" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6"><div class="form-group"><label>Pollution Certificate Start</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="pollution_cert_start" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                                <div class="col-sm-6"><div class="form-group"><label>Pollution Certificate End</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="pollution_cert_end" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6"><div class="form-group"><label>Green Tax Start</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="green_tax_start" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                                <div class="col-sm-6"><div class="form-group"><label>Green Tax End</label>
                                    <input type="text" class="form-control vehicle-datepicker" name="green_tax_end" autocomplete="off" placeholder="<?php echo $this->customlib->getSchoolDateFormat(); ?>"></div></div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                  <div class="form-group">
                                        <label><?php echo $this->lang->line('note'); ?></label>
                                        <textarea class="form-control" id="note" name="note" placeholder="" rows="3"><?php echo set_value('note'); ?></textarea>
                                        <span class="text-danger"><?php echo form_error('note'); ?></span>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <div class="paddA10">
                            <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>
                        </div>
                </div>

                </div>
                
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editvehiclemodal" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('edit_vehicle'); ?></h4>
            </div>

            <form id="editvehicleform" method="post" class="ptt10" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0 ">
                    <div id="editvehicledata"></div>
                </div>
                <div class="box-footer">

                    <div class="paddA10">
                        <button type="submit" class="btn btn-info pull-right" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save') ?></button>

                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="vehicledetails" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-media-content">
            <div class="modal-header modal-media-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="box-title"><?php echo $this->lang->line('vehicle_details'); ?></h4>
            </div>

            <form id="editvehicleform" method="post" class="ptt10" enctype="multipart/form-data">
                <div class="modal-body pt0 pb0 ">
                    <div id="viewvehicledata"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>   
    var vehicle_date_format = '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy', 'M' => 'MM']); ?>';

    function initVehicleDatepickers(ctx) {
        $(ctx).find('.vehicle-datepicker').datepicker({
            todayHighlight: true,
            format: vehicle_date_format,
            autoclose: true,
            weekStart: 1,
            language: 'en'
        });
    }

    $(document).ready(function() {
        initVehicleDatepickers('#myModal');
    });

    (function ($) {
        $('#myModal').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset'); 
        })
    })(jQuery);
    
    
    $("#addvehicleform").on('submit', (function (e) {
        
        e.preventDefault();

        var $this = $(this).find("button[type=submit]:focus");

        $.ajax({
            url: "<?php echo site_url("admin/vehicle/add") ?>",
            type: "POST",
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                $this.button('loading');

            },
            success: function (res)
            {
 
                if (res.status == "fail") {

                    var message = "";
                    $.each(res.error, function (index, value) {

                        message += value;
                    });
                    errorMsg(message);

                } else {

                    successMsg(res.message);

                    window.location.reload(true);
                }
            },
            error: function (xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }

        });
    }));

$('.editvehicle').click(function(){
    $('#editvehiclemodal').modal({
        backdrop: 'static',
        keyboard: false
    });
    var vehicleid = $(this).attr('data-id');

   $.ajax({
       url:'<?php echo site_url("admin/vehicle/getsinglevehicledata"); ?>',
       type:'post',
       data:{vehicleid:vehicleid},
       dataType:'json',
       success:function(response){
          $('#editvehicledata').html(response.page);
          initVehicleDatepickers('#editvehicledata');
       }
   });
})

$('.vehicledetails').click(function(){
    $('#vehicledetails').modal({
        backdrop: 'static',
        keyboard: false
    });
    var vehicleid = $(this).attr('data-id');

   $.ajax({
       url:'<?php echo site_url("admin/vehicle/vehicledetails"); ?>',
       type:'post',
       data:{vehicleid:vehicleid},
       dataType:'json',
       success:function(response){
          $('#viewvehicledata').html(response.page);
       }
   });
})

$("#editvehicleform").on('submit', (function (e) {
    e.preventDefault();

    var $this = $(this).find("button[type=submit]:focus");

    $.ajax({
        url: "<?php echo site_url("admin/vehicle/edit") ?>",
        type: "POST",
        data: new FormData(this),
        dataType: 'json',
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function () {
            $this.button('loading');

        },
        success: function (res)
        {

            if (res.status == "fail") {

                var message = "";
                $.each(res.error, function (index, value) {

                    message += value;
                });
                errorMsg(message);

            } else {

                successMsg(res.message);

                window.location.reload(true);
            }
        },
        error: function (xhr) { // if error occured
            alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
            $this.button('reset');
        },
        complete: function () {
            $this.button('reset');
        }

    });
}));
</script>

<!-- Vehicle Import Modal -->
<div class="modal fade" id="vehicleImportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-upload"></i> Import Vehicles</h4>
            </div>
            <form id="vehicleImportForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="vehicleImportMsg"></div>
                    <div class="form-group">
                        <label>CSV File <small class="req">*</small></label>
                        <input type="file" name="vehicle_csv" id="vehicle_csv" accept=".csv" class="form-control">
                        <p class="help-block">
                            Required: <code>vehicle_no</code><br>
                            Optional: <code>vehicle_model, manufacture_year, registration_number, chasis_number, engine_number, max_seating_capacity, driver_name, driver_licence, driver_contact, note</code><br>
                            Dates (DD-MM-YYYY): <code>fc_validity_start, fc_validity_end, insurance_start, insurance_end, permit_expiry_start, permit_expiry_end, road_tax_start, road_tax_end, pollution_cert_start, pollution_cert_end, green_tax_start, green_tax_end</code>
                        </p>
                    </div>
                    <a href="<?php echo site_url('admin/vehicle/downloadVehicleTemplate'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> Download Template</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="vehicleImportBtn"><i class="fa fa-upload"></i> Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(document).on('submit', '#vehicleImportForm', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    $('#vehicleImportBtn').prop('disabled', true).text('Importing...');
    $.ajax({
        url: '<?php echo site_url("admin/vehicle/import"); ?>',
        type: 'POST',
        data: fd, processData: false, contentType: false,
        success: function(res) {
            res = $.parseJSON(res);
            $('#vehicleImportMsg').html(res.message);
            if (res.status === 'success') { setTimeout(function(){ window.location.reload(true); }, 1500); }
        },
        complete: function() { $('#vehicleImportBtn').prop('disabled', false).text('Import'); }
    });
});
</script>