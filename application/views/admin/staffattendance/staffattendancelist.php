<style type="text/css">
    .radio {
        padding-left: 20px;
    }

    .radio label {
        display: inline-block;
        vertical-align: middle;
        position: relative;
        padding-left: 5px;
    }

    .radio label::before {
        content: "";
        display: inline-block;
        position: absolute;
        width: 17px;
        height: 17px;
        left: 0;
        margin-left: -20px;
        border: 1px solid #cccccc;
        border-radius: 50%;
        background-color: #fff;
        -webkit-transition: border 0.15s ease-in-out;
        -o-transition: border 0.15s ease-in-out;
        transition: border 0.15s ease-in-out;
    }

    .radio label::after {
        display: inline-block;
        position: absolute;
        content: " ";
        width: 11px;
        height: 11px;
        left: 3px;
        top: 3px;
        margin-left: -20px;
        border-radius: 50%;
        background-color: #555555;
        -webkit-transform: scale(0, 0);
        -ms-transform: scale(0, 0);
        -o-transform: scale(0, 0);
        transform: scale(0, 0);
        -webkit-transition: -webkit-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        -moz-transition: -moz-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        -o-transition: -o-transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
        transition: transform 0.1s cubic-bezier(0.8, -0.33, 0.2, 1.33);
    }

    .radio input[type="radio"] {
        opacity: 0;
        z-index: 1;
    }

    .radio input[type="radio"]:focus+label::before {
        outline: thin dotted;
        outline: 5px auto -webkit-focus-ring-color;
        outline-offset: -2px;
    }

    .radio input[type="radio"]:checked+label::after {
        -webkit-transform: scale(1, 1);
        -ms-transform: scale(1, 1);
        -o-transform: scale(1, 1);
        transform: scale(1, 1);
    }

    .radio input[type="radio"]:disabled+label {
        opacity: 0.65;
    }

    .radio input[type="radio"]:disabled+label::before {
        cursor: not-allowed;
    }

    .radio.radio-inline {
        margin-top: 0;
        margin-right: 10px;
        margin-left: 0;
    }

    .radio-primary input[type="radio"]+label::after {
        background-color: #337ab7;
    }

    .radio-primary input[type="radio"]:checked+label::before {
        border-color: #337ab7;
    }

    .radio-primary input[type="radio"]:checked+label::after {
        background-color: #337ab7;
    }

    .radio-danger input[type="radio"]+label::after {
        background-color: #d9534f;
    }

    .radio-danger input[type="radio"]:checked+label::before {
        border-color: #d9534f;
    }

    .radio-danger input[type="radio"]:checked+label::after {
        background-color: #d9534f;
    }

    .radio-info input[type="radio"]+label::after {
        background-color: #5bc0de;
    }

    .radio-info input[type="radio"]:checked+label::before {
        border-color: #5bc0de;
    }

    .radio-info input[type="radio"]:checked+label::after {
        background-color: #5bc0de;
    }

    @media (max-width:767px) {
        .radio.radio-inline {
            display: block;
            margin-left: 0;
        }
    }

    /* Inline evaluator spinner + total-hours display styling */
    .eval-spinner { display: inline-block; margin-left: 6px; vertical-align: middle; }
    .eval-spinner i { font-size: 13px; color: #888; }
    .total-hours-display { font-weight: 500; }

    /* Note textarea styling */
    .note-textarea { min-height: 48px; resize: vertical; }
    .note-cell { vertical-align: top; }
    .session-cell { white-space: normal; }
    .raw-punches-cell ul { margin: 0; padding: 0; }

</style>

<div class="content-wrapper">

    <!-- Between-dates progress modal (used by Fetch/Process) -->
    <div class="modal fade" id="betweenProgressModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Working...</h5>
          </div>
          <div class="modal-body text-center">
            <i class="fa fa-spinner fa-pulse fa-3x"></i>
            <div id="betweenProgressMessage" class="mt-3">Please wait — this may take a few minutes.</div>
            <div id="betweenProgressDetails" class="mt-2 text-muted small"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { ?>
            <div id="flashMessage" style="display:none;" data-type="html"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" id="btnSyncPunches" class="btn btn-primary btn-sm"><i class="fa fa-refresh"></i> <?php echo $this->lang->line('sync_punches'); ?></button>
                            <button type="button" id="btnProcessAttendance" class="btn btn-success btn-sm"><i class="fa fa-calculator"></i> <?php echo $this->lang->line('process_biometric_attendance'); ?></button>
                        </div>
                    </div>
                    <form id='form1' action="<?php echo site_url('admin/staffattendance/index') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('role'); ?></label>

                                        <select autofocus="" id="class_id" name="user_id" class="form-control">
                                            <option value="select"><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($classlist as $key => $class) {
                                            ?>
                                                <option value="<?php echo $class["type"] ?>" <?php
                                                                                                if ($class["type"] == $user_type_id) {
                                                                                                    echo "selected =selected";
                                                                                                }
                                                                                                ?>><?php print_r($class["type"]) ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">
                                            <?php echo $this->lang->line('attendance_date'); ?></label>
                                        <input name="date" placeholder="" type="text" class="form-control date" value="<?php echo set_value('date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" />
                                        <span class="text-danger"><?php echo form_error('date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" name="search" value="search" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- FETCH / PROCESS BETWEEN DATES: new UI card -->
                    <?php if ($this->rbac->hasPrivilege('biometric_attendance', 'can_view')) { ?>
                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-6">
                            <div class="box box-solid box-info">
                                <div class="box-header"><h4 class="box-title"><i class="fa fa-refresh"></i> Fetch punches between dates</h4></div>
                                <div class="box-body">
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('from'); ?></label>
                                            <input type="text" class="form-control date" id="fetch_from_date" value="<?php echo date($this->customlib->getSchoolDateFormat(), strtotime('-7 days')); ?>" readonly>
                                        </div>
                                        &nbsp;
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('to'); ?></label>
                                            <input type="text" class="form-control date" id="fetch_to_date" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly>
                                        </div>
                                        &nbsp;
                                        <button id="btnFetchBetween" class="btn btn-primary"><?php echo $this->lang->line('fetch'); ?> &amp; Reset</button>
                                    </div>
                                    <p class="help-block text-muted" style="margin-top:6px;">This will delete existing raw punches between the selected dates and import fresh punches from the configured biometric device.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="box box-solid box-success">
                                <div class="box-header"><h4 class="box-title"><i class="fa fa-calculator"></i> Process attendance between dates</h4></div>
                                <div class="box-body">
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('from'); ?></label>
                                            <input type="text" class="form-control date" id="process_from_date" value="<?php echo date($this->customlib->getSchoolDateFormat(), strtotime('-7 days')); ?>" readonly>
                                        </div>
                                        &nbsp;
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('to'); ?></label>
                                            <input type="text" class="form-control date" id="process_to_date" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly>
                                        </div>
                                        &nbsp;
                                        <button id="btnProcessBetween" class="btn btn-success"><?php echo $this->lang->line('process'); ?></button>
                                    </div>
                                    <p class="help-block text-muted" style="margin-top:6px;">Existing processed attendance (biometric) found in the range will be removed and recomputed from raw punches.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php
                    if (isset($resultlist)) {
                    ?>
                        <div class="box-header ptbnull"></div>
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('staff_list'); ?></h3>
                            <div class="box-tools pull-right">
                            </div>
                        </div>
                        <div class="box-body">
                            <?php
                            if (!empty($resultlist)) {
                                $checked = "";
                           
                                ?>
                                <form action="<?php echo site_url('admin/staffattendance/index') ?>" id="save_attendance" method="post">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <div class="mailbox-controls">
                                    <div class="row">
                                                <div class="col-md-8">
                                                
                                                    <div class="form-group">
                                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('set_attendance_for_all_staff_as'); ?> &nbsp;</label>
                                                        <?php
                                                        foreach ($attendencetypeslist as $key => $type) {
                                                            $att_type = str_replace(" ", "_", strtolower($type['type']));

                                                        ?>
                                                            <div class="radio radio-info radio-inline">
                                                                <input type="radio" data-record_id="<?php echo $type['id'] ?>" name="attendencetype" class="default_radio" value="radio_<?php echo $type['id'] ?>" id="attendencetype<?php echo $type['id'] ?>"   onclick="getatten(<?php echo $type['id'] ?>)">
                                                                <label for="attendencetype<?php echo $type['id'] ?>">
                                                                    <?php echo  $this->lang->line($att_type); ?> 
                                                                </label>

                                                            </div>
                                                        <?php

                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="pull-right">
														<?php if (($this->rbac->hasPrivilege('staff_attendance', 'can_add')) || ($this->rbac->hasPrivilege('staff_attendance', 'can_edit'))) { ?>
                                                        <button type="submit" name="search" value="saveattendence" id="saveattendence" class="btn btn-primary btn-sm pull-right checkbox-toggle"><i class="fa fa-save"></i> <?php echo $this->lang->line('save_attendance'); ?> </button>
														<?php } ?>
                                                    </div>
                                                </div>
                                            </div>                                        
                                    </div>
                                    <input type="hidden" name="is_first_time_attendance" value="<?php echo $is_first_time_attendance;?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user_type_id; ?>">
                                    <input type="hidden" name="section_id" value="">
                                    <input type="hidden" name="date" value="<?php echo $date; ?>">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped example">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th><?php echo $this->lang->line('staff_id'); ?></th>
                                                    <th><?php echo $this->lang->line('name'); ?></th>
                                                                                                        <th><?php echo $this->lang->line('attendance'); ?></th>
                                                                                                        <th class="session-header"><?php echo $this->lang->line('session_attendance'); ?></th>
                                                    <?php  if ($sch_setting->staff_biometric) {  ?>
                                                        <th width="10%"><?php echo $this->lang->line('date'); ?></th>
                                                    <?php  }  ?>
                                                    <!-- role and source columns removed to make Note wider -->
                                                    <th class="white-space-nowrap"><?php echo $this->lang->line('entry_time'); ?></th>
                                                    <th class="white-space-nowrap"><?php echo $this->lang->line('exit_time'); ?></th>
                                                    <th><?php echo $this->lang->line('total_hours'); ?></th>
                                                    <th><?php echo $this->lang->line('raw_punches'); ?></th>
                                                    <th class="text-right note-header" style="width:22%;"><?php echo $this->lang->line('note'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $row_count = 1;
                                                foreach ($resultlist as $key => $value) {

                                                    $attendendence_id = $value["id"];
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="staff_role[]" id="staff_role_<?php echo $value['role_id']; ?>" value="<?php echo $value['role_id']; ?>">
                                                            <input type="hidden" name="student_session[]" value="<?php echo $value['staff_id']; ?>">
                                                            <input type="hidden" value="<?php echo $attendendence_id ?>" name="attendendence_id<?php echo $value["staff_id"]; ?>">
                                                            <?php echo $row_count; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $value['employee_id']; ?>
                                                        </td>                                                      
                                                        <td>
                                                            <?php echo $value['name'] . " " . $value['surname']; ?>
                                                        </td>
                                                        <!-- role column removed -->
                                                        <td>
                                                            <?php
                                                            $c     = 1;
                                                            $count = 0;
                                                            foreach ($attendencetypeslist as $key => $type) {  

                                                                    $att_type = str_replace(" ", "_", strtolower($type['type']));
                                                                    if ($value["date"] != "xxx") {
                                                            ?>
                                                                        <div class="radio radio-info radio-inline">
                                                                            <input onclick="disable_enable(this.value,<?php echo $value["staff_id"] ?>)"  <?php if ($value['staff_attendance_type_id'] == $type['id']) {
                                                                                        echo "checked";
                                                                                    }
                                                                                    ?> type="radio" id="attendencetype<?php echo $value['staff_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['staff_id']; ?>" class="radio_<?php echo $type['id'] ?>">
                                                                            <label for="attendencetype<?php echo $value['staff_id'] . "-" . $count; ?>">
                                                                                <?php echo $this->lang->line(($type['long_lang_name'])); ?>
                                                                            </label>
                                                                        </div>
                                                                    <?php
                                                                    } else {
                                                                    ?>

                                                                        <?php
                                                                        if ($sch_setting->staff_biometric) {
                                                                        ?>
                                                                            <div class="radio radio-info radio-inline">
                                                                                <input <?php if ($att_type == "absent") {
                                                                                            echo "checked";
                                                                                        }
                                                                                        ?> type="radio" id="attendencetype<?php echo $value['staff_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['staff_id']; ?>" class="radio_<?php echo $type['id'] ?>">
                                                                                <label for="attendencetype<?php echo $value['staff_id'] . "-" . $count; ?>">
                                                                                    <?php echo $this->lang->line(($type['long_lang_name'])); ?>
                                                                                </label>
                                                                            </div>
                                                                        <?php
                                                                        } else {
                                                                        ?>
                                                                            <div class="radio radio-info radio-inline">
                                                                                <input  onclick="disable_enable(this.value,<?php echo $value["staff_id"] ?>)"  <?php if (($c == 1) && ($resultlist[0]['staff_attendance_type_id'] != 5)) {
                                                                                            echo "checked";
                                                                                        }
                                                                                        ?> type="radio" id="attendencetype<?php echo $value['staff_id'] . "-" . $count; ?>" value="<?php echo $type['id'] ?>" name="attendencetype<?php echo $value['staff_id']; ?>" class="radio_<?php echo $type['id'] ?>">
                                                                                <label for="attendencetype<?php echo $value['staff_id'] . "-" . $count; ?>">
                                                                                    <?php
                                                                                     echo $this->lang->line(($type['long_lang_name'])); ?>
                                                                                </label>
                                                                            </div>
                                                                        <?php
                                                                        }
                                                                        ?> <?php
                                                                        }
                                                                        $c++;
                                                                        $count++;
                                                                    
                                                                }
                                                                            ?>
                                                        </td>
                                                    <td class="session-cell">
                                                        <?php
                                                        if (!empty($value['session_attendance_data'])) {
                                                            $session_data = json_decode($value['session_attendance_data'], true);
                                                            if ($session_data) {
                                                                $morning_status = isset($session_data['morning_session']) ? $session_data['morning_session'] : null;
                                                                $afternoon_status = isset($session_data['afternoon_session']) ? $session_data['afternoon_session'] : null;
                                                                
                                                                $morning_text = '';
                                                                $afternoon_text = '';

                                                                // Translate IDs to text using $attendencetypeslist
                                                                foreach ($attendencetypeslist as $type) {
                                                                    if ($type['id'] == $morning_status) {
                                                                        $morning_text = $this->lang->line(($type['long_lang_name']));
                                                                    }
                                                                    if ($type['id'] == $afternoon_status) {
                                                                        $afternoon_text = $this->lang->line(($type['long_lang_name']));
                                                                    }
                                                                }
                                                                
                                                                echo 'Morning: ' . ($morning_text ?: 'N/A') . '<br>';
                                                                echo 'Afternoon: ' . ($afternoon_text ?: 'N/A');
                                                                if (!empty($session_data['pending_out_punch'])) {
                                                                    echo '<br><span class="text-warning">Pending out punch</span>';
                                                                }
                                                            } else {
                                                                echo 'N/A';
                                                            }
                                                        } else {
                                                            echo 'N/A';
                                                        }
                                                        ?>
                                                    </td>
                                                        <?php
                                                        if ($sch_setting->staff_biometric) {
                                                        ?>
                                                            <td>
                                                            <?php
                                                            $formatted_date = '';
                                                            if (!empty($value['attendence_dt']) && $value['attendence_dt'] !== '0000-00-00 00:00:00') {
                                                                $formatted_date = $this->customlib->dateyyyymmddToDateTimeformat($value['attendence_dt']);
                                                            } elseif (!empty($value['date']) && $value['date'] !== 'xxx') {
                                                                $formatted_date = date($this->customlib->getSchoolDateFormat(), strtotime($value['date']));
                                                            }

                                                            echo $formatted_date !== '' ? $formatted_date : $this->lang->line('n_a');
                                                            ?>
                                                            </td>
                                                        <?php
                                                        }
                                                        ?>


                                                        <?php
                                                        if($value['staff_attendance_type_id']==3 || $value['staff_attendance_type_id']==5){
                                                            $disable_input_attr="disabled";
                                                        }else{
                                                            $disable_input_attr="";
                                                        }  ?>

                                                    <td class="relative">
                                                        <input <?php echo $disable_input_attr;?> type="text" value="<?php if($value["in_time"]!="00:00:00"){ echo $value["in_time"]; }else{ echo "";} ?>"  name="in_time_<?php echo $value["staff_id"] ?>" id="in_time_<?php echo $value["staff_id"] ?>" class="form-control datetime in_time time in_time_<?php echo $value['role_id']; ?>">
                                                    </td>                                                        
                                                    <td class="relative">
                                                        <input  <?php echo $disable_input_attr;?>  type="text" value="<?php if($value["out_time"]!="00:00:00"){ echo $value["out_time"]; }else{ echo "";} ?>"  name="out_time_<?php echo $value["staff_id"] ?>"  id="out_time_<?php echo $value["staff_id"] ?>" class="form-control datetime out_time time out_time_<?php echo $value['role_id']; ?>">
                                                    </td>  
                                                    <td>
                                                        <span class="total-hours-display"><?php echo isset($value['total_hours_worked']) ? $value['total_hours_worked'] : 'N/A'; ?></span>
                                                        <span class="eval-spinner" style="display:none;"><i class="fa fa-spinner fa-pulse"></i></span>
                                                    </td>
                                                    <td class="raw-punches-cell">
                                                        <?php if (!empty($value['biometric_raw_punches'])) { ?>
                                                            <ul style="list-style: none; padding: 0; margin: 0;">
                                                                <?php foreach ($value['biometric_raw_punches'] as $punch) { ?>
                                                                    <li><?php echo date('H:i:s', strtotime($punch['punch_time'])); ?></li>
                                                                <?php } ?>
                                                            </ul>
                                                        <?php } else { ?>
                                                            N/A
                                                        <?php } ?>
                                                    </td>
                                                        <?php if ($value["date"] == 'xxx') { ?>
                                                            <td class="text-right note-cell"><textarea class="form-control note-textarea" rows="2" name="remark<?php echo $value["staff_id"] ?>"></textarea></td>
                                                        <?php } else { ?>
                                                            <td class="text-right note-cell"><textarea class="form-control note-textarea" rows="2" name="remark<?php echo $value["staff_id"] ?>"><?php echo $value["remark"]; ?></textarea></td>
                                                        <?php } ?>
                                                    </tr>
                                                <?php
                                                    $row_count++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            <?php
                            } else {
                            ?>
                                <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                            <?php
                            }
                            ?>
                        </div>
                </div>
            <?php
                    }
            ?>
    </section>
</div>

<script type="text/javascript">
    $(document).on('submit', '#save_attendance', function(e) {
        // mark bulk save in progress so per-row auto-save handlers ignore transient events
        window._staffAttendanceBulkSave = true;
        $('#load').button('loading');
    });

    $(document).ready(function() {
        $.extend($.fn.dataTable.defaults, {
            searching: false,
            ordering: true,
            paging: false,
            retrieve: true,
            destroy: true,
            info: false
        });
        var table = $('.example').DataTable();
        table.buttons('.export').remove();
    });
</script>
<script type="text/javascript">
  
    $(document).ready(function() {
        // Convert flash messages to toast notifications
        var $flash = $('#flashMessage');
        if ($flash.length) {
            var flashHtml = $flash.html().trim();
            if (flashHtml) {
                // Extract the text from the alert div
                var $temp = $('<div>').html(flashHtml);
                var message = $temp.find('.alert').text().trim();
                var alertClass = $temp.find('.alert').attr('class');
                
                if (message) {
                    if (alertClass && alertClass.indexOf('alert-success') !== -1) {
                        toastr.success(message, '', { timeOut: 4000, positionClass: 'toast-top-right' });
                    } else if (alertClass && alertClass.indexOf('alert-danger') !== -1) {
                        toastr.error(message, '', { timeOut: 5000, positionClass: 'toast-top-right' });
                    } else if (alertClass && alertClass.indexOf('alert-warning') !== -1) {
                        toastr.warning(message, '', { timeOut: 4000, positionClass: 'toast-top-right' });
                    } else {
                        toastr.info(message, '', { timeOut: 4000, positionClass: 'toast-top-right' });
                    }
                }
            }
            $flash.remove(); // Remove the hidden div after converting to toast
        }

        $('.default_radio').click(function() {
            let radio_default=($(this).val());
            var returnVal = confirm("<?php echo $this->lang->line('are_you_sure'); ?>");
            if(returnVal){
                
                $("input[type=radio][class='"+radio_default+"']").prop("checked", returnVal);
                
                let attendance_type_id = ($(this).data('record_id'));
                if(attendance_type_id==3 || attendance_type_id==5){
                    //absent or holiday
                    $('.in_time').attr("disabled",true);
                    $('.out_time').attr("disabled",true);
                }else{
                    $('.in_time').attr("disabled",false);
                    $('.out_time').attr("disabled",false);
                }

            }else{
                return false;
            }
    });
    });
</script>

<script type="text/javascript">
    $(function() {
        $('.button-checkbox').each(function() {
            var $widget = $(this),
                $button = $widget.find('button'),
                $checkbox = $widget.find('input:checkbox'),
                color = $button.data('color'),
                settings = {
                    on: {
                        icon: 'glyphicon glyphicon-check'
                    },
                    off: {
                        icon: 'glyphicon glyphicon-unchecked'
                    }
                };
            $button.on('click', function() {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
                $checkbox.triggerHandler('change');
                updateDisplay();
            });
            $checkbox.on('change', function() {
                updateDisplay();
            });

            function updateDisplay() {
                var isChecked = $checkbox.is(':checked');
                $button.data('state', (isChecked) ? "on" : "off");
                $button.find('.state-icon')
                    .removeClass()
                    .addClass('state-icon ' + settings[$button.data('state')].icon);
                if (isChecked) {
                    $button
                        .removeClass('btn-success')
                        .addClass('btn-' + color + ' active');
                } else {
                    $button
                        .removeClass('btn-' + color + ' active')
                        .addClass('btn-primary');
                }
            }

            function init() {
                updateDisplay();
                if ($button.find('.state-icon').length == 0) {
                    $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i> ');
                }
            }
            init();
        });
    });
</script>

<script type="text/javascript">
//**** staff attendance ****//
 $(function() {
     $('.time').datetimepicker({
            format: 'LT'
     });
 });
 
  $(function()
        {
            $('.time').datetimepicker().on('dp.show',function()
            {
                $(this).closest('.table-responsive').removeClass('table-responsive').addClass('temp');
            }).on('dp.hide',function()
            {
                $(this).closest('.temp').addClass('table-responsive').removeClass('temp')
            });
        });

function tConvert(time) {
if (time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/)) {
    const [timeWithoutPeriod, period] = time.split(" ");
    let [hours, minutes, seconds] = timeWithoutPeriod.split(":");
    let AM_PM = null;
    AM_PM = hours < 12 ? 'AM' : 'PM'; // Set AM/PM
    hours = hours % 12 || 12; // Adjust hours
    return `${hours}:${minutes} ${AM_PM}`;
} else {
    return time;
}
}

var attendance_setting = <?php echo json_encode($staff_settings) ?>;

function getatten(atten_type){
    //3 for absent 5 for holiday
    if(atten_type==3 || atten_type==5){
      // do NOT clear existing in/out times when marking absent/holiday; only disable inputs
      $('.in_time').attr("disabled", true);
      $('.out_time').attr("disabled", true);
      return false;
    }else{
        var role_id = $("input[name='staff_role[]']").map(function(){return $(this).val();}).get();
        let nm = (attendance_setting);     
        for(var i=0;i<role_id.length;i++){
        var returnValue = false;
        $.each(nm, function(key, value) {
            if (value.staff_attendence_type_id == atten_type  &&  value.role_id==role_id[i]) {                
                returnValue = [tConvert(value.entry_time_from), tConvert(value.entry_time_to)];
                // only set default times if the field is empty (do not overwrite manual edits)
                $('.in_time_'+role_id[i]).each(function(){ if($(this).val().trim() === '') { $(this).val(returnValue[0]); } });
                $('.out_time_'+role_id[i]).each(function(){ if($(this).val().trim() === '') { $(this).val(returnValue[1]); } });
            }
        }); 
    }
    }
}

let disable_enable=(type,staff_id)=>{
    if(type==3 || type==5){
        // do NOT clear any manually entered times; just disable the inputs
        $("#in_time_"+staff_id).attr("disabled",true);
        $("#out_time_"+staff_id).attr("disabled",true);
    }else{
        // enable inputs without clearing existing values
        $("#in_time_"+staff_id).attr("disabled",false);
        $("#out_time_"+staff_id).attr("disabled",false);
    }
}

// Map attendance type IDs to human readable labels (used to update session cell client-side)
var attendanceTypeLabels = <?php
    $attendance_type_labels = array();
    foreach ($attendencetypeslist as $type) {
        $attendance_type_labels[$type['id']] = $this->lang->line($type['long_lang_name']);
    }
    echo json_encode($attendance_type_labels);
?>;
var evaluateUrl = "<?php echo site_url('admin/staffattendance/ajax_evaluate_attendance'); ?>";
var saveProcessUrl = "<?php echo site_url('admin/staffattendance/ajax_save_and_process_attendance'); ?>";
var fetchBetweenUrl = "<?php echo site_url('admin/staffattendance/fetch_punches_between_dates'); ?>";
var processBetweenUrl = "<?php echo site_url('admin/staffattendance/process_attendance_between_dates'); ?>";
var syncPunchesUrl = "<?php echo site_url('admin/staff/sync_biometric_attendance'); ?>";
var processAttendanceUrl = "<?php echo site_url('admin/staffattendance/trigger_process_biometric_attendance'); ?>";
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

// Diagnostic: confirm the attendance JS loaded (check browser console)
if (window && window.console && console.log) { console.log('staffattendance.js loaded — CSRF token name:', csrfName); }

// Helper: show/hide progress modal used for between-dates operations
function showBetweenModal(title, message, details) {
    $('#betweenProgressModal .modal-title').text(title || 'Working...');
    $('#betweenProgressMessage').text(message || 'Please wait...');
    $('#betweenProgressDetails').text(details || '');
    $('#betweenProgressModal').modal({ backdrop: 'static', keyboard: false });
}
function hideBetweenModal() { $('#betweenProgressModal').modal('hide'); }

// Sync Punches button handler (fetch all raw punches from biometric device)
$(document).on('click', '#btnSyncPunches', function(e) {
    console.log('btnSyncPunches clicked!');
    e.preventDefault();
    if (!confirm('This will fetch all raw punches from the biometric device. This may take a few minutes. Continue?')) { return; }
    
    $('#btnSyncPunches, #btnProcessAttendance, #btnFetchBetween, #btnProcessBetween, #saveattendence').prop('disabled', true);
    showBetweenModal('Syncing Punches', 'Fetching all raw punches from biometric device. Please do not close this window...', 'This operation may take several minutes.');
    
    // Navigate to the sync endpoint (it will process and redirect back with flash message)
    setTimeout(function() {
        window.location.href = syncPunchesUrl;
    }, 500);
});

// Process Biometric Attendance button handler (process all raw punches into attendance records)
$(document).on('click', '#btnProcessAttendance', function(e) {
    console.log('btnProcessAttendance clicked!');
    e.preventDefault();
    if (!confirm('This will process all raw punches into attendance records. This may take a few minutes. Continue?')) { return; }
    
    $('#btnSyncPunches, #btnProcessAttendance, #btnFetchBetween, #btnProcessBetween, #saveattendence').prop('disabled', true);
    showBetweenModal('Processing Attendance', 'Processing all raw punches into attendance records. Please do not close this window...', 'This operation may take several minutes.');
    
    // Navigate to the process endpoint (it will process and redirect back with flash message)
    setTimeout(function() {
        window.location.href = processAttendanceUrl;
    }, 500);
});

// Fetch punches between two dates (reset raw punches + import) — show progress modal
// Use delegated handler + immediate feedback (console + toast) to ensure responsiveness
$(document).on('click', '#btnFetchBetween', function(e) {
    try { console.log('btnFetchBetween clicked'); } catch (err) {}
    e.preventDefault();
    var from = $('#fetch_from_date').val();
    var to = $('#fetch_to_date').val();
    console.log('Fetch dates - From:', from, 'To:', to);
    if (!from || !to) { toastr.error('Please select both From and To dates'); return; }
    if (!confirm('This will remove existing raw punches between the selected dates and import fresh punches from the biometric device. Continue?')) { return; }

    // immediate UI feedback
    try { toastr.info('Starting fetch — please wait...'); } catch (err) {}
    $('#btnFetchBetween, #btnProcessBetween, #saveattendence').prop('disabled', true);
    showBetweenModal('Fetching punches', 'Fetching raw punches between ' + from + ' and ' + to + '. This may take some time...');

    var postData = { from_date: from, to_date: to };
    postData[csrfName] = csrfHash;
    console.log('Sending fetch request with data:', postData);

    $.ajax({
        url: fetchBetweenUrl,
        type: 'POST',
        data: postData,
        dataType: 'json',
        timeout: 300000, // 5 minutes timeout for large date ranges
        success: function(resp) {
            console.log('Fetch response:', resp);
            if (resp && resp.status === 'success') {
                var inserted = resp.inserted || 0;
                var exceptions = resp.exceptions || 0;
                var detailText = exceptions > 0 ? ' (' + exceptions + ' exception' + (exceptions != 1 ? 's' : '') + ')' : '';
                $('#betweenProgressDetails').text('Inserted: ' + inserted + ', Exceptions: ' + exceptions);
                $('#betweenProgressMessage').text(resp.message || 'Fetch completed');
                toastr.success((resp.message || inserted + ' punches fetched!') + detailText);
                setTimeout(function(){ hideBetweenModal(); location.reload(); }, 3000);
            } else {
                hideBetweenModal();
                toastr.error(resp && resp.message ? resp.message : 'Server error occurred');
                $('#btnFetchBetween, #btnProcessBetween').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Fetch request failed:', status, error, xhr.responseText);
            hideBetweenModal();
            if (status === 'timeout') {
                toastr.error('Request timed out. The operation may still be running. Check logs or refresh the page.');
            } else {
                toastr.error('Request failed — check server logs.');
            }
            $('#btnFetchBetween, #btnProcessBetween').prop('disabled', false);
        }
    });
});

// Process attendance between two dates (delete existing processed rows + re-run processor) — show progress modal
// Delegated handler with immediate feedback
$(document).on('click', '#btnProcessBetween', function(e) {
    try { console.log('btnProcessBetween clicked'); } catch (err) {}
    e.preventDefault();
    var from = $('#process_from_date').val();
    var to = $('#process_to_date').val();
    if (!from || !to) { toastr.error('Please select both From and To dates'); return; }
    if (!confirm('This will remove existing processed (biometric) attendance rows between the selected dates and reprocess them from raw punches. Continue?')) { return; }

    try { toastr.info('Starting processing — please wait...'); } catch (err) {}
    $('#btnFetchBetween, #btnProcessBetween, #saveattendence').prop('disabled', true);
    showBetweenModal('Processing attendance', 'Re-processing attendance between ' + from + ' and ' + to + '. This may take some time...');

    var postData = { from_date: from, to_date: to };
    postData[csrfName] = csrfHash;

    $.ajax({
        url: processBetweenUrl,
        type: 'POST',
        data: postData,
        dataType: 'json',
        timeout: 300000, // 5 minutes timeout for large date ranges
        success: function(resp) {
            if (resp && resp.status === 'success') {
                var processedDays = resp.processed_days || 0;
                var deletedRows = resp.deleted_rows || 0;
                $('#betweenProgressDetails').text('Processed days: ' + processedDays + ', Deleted rows: ' + deletedRows);
                $('#betweenProgressMessage').text(resp.message || 'Processing completed');
                toastr.success((resp.message || 'Attendance processed successfully!') + ' (Days: ' + processedDays + ', Deleted rows: ' + deletedRows + ')');
                setTimeout(function(){ hideBetweenModal(); location.reload(); }, 3000);
            } else {
                hideBetweenModal();
                toastr.error(resp && resp.message ? resp.message : 'Server error occurred');
                $('#btnFetchBetween, #btnProcessBetween').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            hideBetweenModal();
            if (status === 'timeout') {
                toastr.error('Request timed out. The operation may still be running. Check logs or refresh the page.');
            } else {
                toastr.error('Request failed — check server logs.');
            }
            $('#btnFetchBetween, #btnProcessBetween').prop('disabled', false);
        }
    });
});

// AJAX auto‑save + process with debounce and per-row loading spinner
var evalTimers = {}; // keyed by staffId
// global flag to ignore per-row autosaves during full (bulk) save
window._staffAttendanceBulkSave = window._staffAttendanceBulkSave || false;
// global flag to prevent auto-save during initial page load
var _pageLoadComplete = false;

// initialize "last sent" values to avoid duplicate autosaves on harmless blur/submit
// Use setTimeout to ensure this runs AFTER datetimepicker has formatted the values
setTimeout(function() {
    $('.in_time, .out_time').each(function(){
        $(this).data('last', $(this).val() || '');
    });
    // Mark page load as complete - auto-save can now trigger
    _pageLoadComplete = true;
}, 500);

function scheduleEvalForRow($row, staffId, dateRaw, inTime, outTime, showToast) {
    // ignore if bulk save in progress
    if (window._staffAttendanceBulkSave) { return; }
    if (!staffId) { return; }

    // debounce (500ms)
    if (evalTimers[staffId]) {
        clearTimeout(evalTimers[staffId]);
    }
    evalTimers[staffId] = setTimeout(function() {
        var $spinner = $row.find('.eval-spinner');
        $spinner.show();

        // CALL the save+process endpoint (auto‑persist + reprocess)
        var dataToSend = { staff_id: staffId, date: dateRaw, in_time: inTime, out_time: outTime };
        dataToSend[csrfName] = csrfHash;

        $.post(saveProcessUrl, dataToSend, function(resp) {
            if (!resp || resp.status !== 'success') {
                return;
            }

            // resp.data.evaluated contains the computed attendance info
            var d = resp.data.evaluated || {};

            // update inputs' last-sent values so future blurs don't re-trigger for unchanged values
            $row.find('#in_time_' + staffId).data('last', ($row.find('#in_time_' + staffId).val() || ''));
            $row.find('#out_time_' + staffId).data('last', ($row.find('#out_time_' + staffId).val() || ''));

            // prefer processor (DB) result for attendance type and session (authoritative)
            var dbAtt = resp.data.db_attendance || {};
            var attTypeId = (dbAtt.staff_attendance_type_id) ? dbAtt.staff_attendance_type_id : (d.attendance_type_id || null);
            if (attTypeId) {
                var $radio = $row.find('input[name="attendencetype' + staffId + '"][value="' + attTypeId + '"]');
                if ($radio.length) { $radio.prop('checked', true); }
                // enable/disable inputs based on authoritative type
                disable_enable(attTypeId, staffId);
            } else if (d.attendance_type_id) {
                var $radio2 = $row.find('input[name="attendencetype' + staffId + '"][value="' + d.attendance_type_id + '"]');
                if ($radio2.length) { $radio2.prop('checked', true); disable_enable(d.attendance_type_id, staffId); }
            }

            // update total hours display (use evaluated calculation)
            var $totalHoursTd = $row.find('#out_time_' + staffId).closest('td').next('td');
            $totalHoursTd.find('.total-hours-display').text(d.total_hours_worked && d.total_hours_worked !== 0 ? d.total_hours_worked : 'N/A');

            // update session attendance cell (prefer DB session_attendance_data if available)
            var sessionData = null;
            if (dbAtt.session_attendance_data) {
                try { sessionData = JSON.parse(dbAtt.session_attendance_data); } catch (e) { sessionData = d.session || null; }
            } else {
                sessionData = d.session || null;
            }
            var morningLabel = (sessionData && attendanceTypeLabels[sessionData.morning_session]) || 'N/A';
            var afternoonLabel = (sessionData && attendanceTypeLabels[sessionData.afternoon_session]) || 'N/A';
            var sessionHtml = 'Morning: ' + morningLabel + '<br>Afternoon: ' + afternoonLabel;
            if ((sessionData && sessionData.pending_out_punch) || d.pending_out_punch) { sessionHtml += '<br><span class="text-warning">Pending out punch</span>'; }
            $row.find('.session-cell').html(sessionHtml);

            // update raw punches list
            if (resp.data.raw_punches && resp.data.raw_punches.length) {
                var ul = '<ul style="list-style:none;padding:0;margin:0;">';
                resp.data.raw_punches.forEach(function(p){ ul += '<li>' + p.punch_time.substring(11) + '</li>'; });
                ul += '</ul>';
                $row.find('.raw-punches-cell').html(ul);
            }

            // update remark input with appended audit text from DB (if present)
            if (resp.data.db_attendance && typeof resp.data.db_attendance.remark !== 'undefined') {
                $row.find('input[name="remark' + staffId + '"]').val(resp.data.db_attendance.remark || '');
            }

            // show short success toast only when caller requested it (we show toast on blur/focusout only)
            if (showToast) {
                try {
                    var staffName = $row.find('td').eq(2).text().trim();
                    var displayDate = dateRaw; // already in school's display format
                    var toastMsg = staffName + ' — ' + displayDate + ': ' + '<?php echo addslashes($this->lang->line("attendance_saved_successfully")); ?>';
                    toastr.clear();
                    toastr.success(toastMsg, '', { timeOut: 1800, positionClass: 'toast-top-right' });
                } catch (e) {
                    // ignore
                }
            }

        }, 'json').fail(function() {
            // optional: show error toast
        }).always(function() {
            $spinner.hide();
        });

    }, 500);
}

// handle changes, datetimepicker changes AND manual blur/focusout. Skip if bulk save in progress.
$(document).on('change dp.change blur focusout', '.in_time, .out_time', function(e) {
    if (window._staffAttendanceBulkSave) { return; }
    // Prevent auto-save during initial page load/initialization
    if (!_pageLoadComplete) { return; }
    
    var $row = $(this).closest('tr');
    var staffId = $row.find("input[name='student_session[]']").val();
    var dateRaw = $("input[name='date']").val();
    var $in = $row.find('#in_time_' + staffId);
    var $out = $row.find('#out_time_' + staffId);
    var inTime = $in.val();
    var outTime = $out.val();

    // only trigger autosave if values actually changed compared to last-sent value
    var changed = ($in.data('last') !== (inTime || '')) || ($out.data('last') !== (outTime || ''));
    if (!changed) { return; }

    // show toast only when the user leaves the field (blur/focusout)
    var showToast = (e.type === 'blur' || e.type === 'focusout');
    scheduleEvalForRow($row, staffId, dateRaw, inTime, outTime, showToast);
});

</script>