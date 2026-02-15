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
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/staff/sync_biometric_attendance') ?>" class="btn btn-primary btn-sm"><i class="fa fa-refresh"></i> <?php echo $this->lang->line('sync_punches'); ?></a>
                            <a href="<?php echo site_url('admin/staffattendance/trigger_process_biometric_attendance') ?>" class="btn btn-success btn-sm"><i class="fa fa-calculator"></i> <?php echo $this->lang->line('process_biometric_attendance'); ?></a>
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
var evaluateUrl = '<?php echo site_url('admin/staffattendance/ajax_evaluate_attendance'); ?>';
var saveProcessUrl = '<?php echo site_url('admin/staffattendance/ajax_save_and_process_attendance'); ?>';
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

// AJAX auto‑save + process with debounce and per-row loading spinner
var evalTimers = {}; // keyed by staffId
// global flag to ignore per-row autosaves during full (bulk) save
window._staffAttendanceBulkSave = window._staffAttendanceBulkSave || false;

// initialize "last sent" values to avoid duplicate autosaves on harmless blur/submit
$('.in_time, .out_time').each(function(){
    $(this).data('last', $(this).val() || '');
});

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
        $.post(saveProcessUrl, {
            staff_id: staffId,
            date: dateRaw,
            in_time: inTime,
            out_time: outTime,
            [csrfName]: csrfHash
        }, function(resp) {
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