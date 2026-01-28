<?php if (isset($is_dynamic_timetable) && $is_dynamic_timetable): ?>    <!-- DYNAMIC UI CONTENT -->
    <div class="row clearfix">
        <div class="col-md-12 column">
            <a id="add_row" class="addrow addbtnright btn btn-primary btn-sm pull-right"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_new'); ?></a>
            <form method="POST" action="<?php echo site_url('admin/timetable/savegroup'); ?>" id="form_<?php echo $day; ?>" class="commentForm autoscroll">
            <input type="hidden" name="day" value="<?php echo $day; ?>">
            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
            <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
            <input type="hidden" name="subject_group_id" value="<?php echo $subject_group_id; ?>">
            <div class="">
                <table class="table table-bordered table-hover order-list" id="tab_logic">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('start_time'); ?></th>
                            <th><?php echo $this->lang->line('end_time'); ?></th>
                            <th><?php echo $this->lang->line('subject'); ?></th>
                            <th><?php echo $this->lang->line('teacher'); ?></th>
                            <th><?php echo $this->lang->line('room_no'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id='addr0'>
                            <td>
                                <input type="text" name='start_time_1' placeholder='Start Time' class="form-control time_picker" id="start_time_1"/>
                            </td>
                            <td>
                                <input type="text" name='end_time_1' placeholder='End Time' class="form-control time_picker" id="end_time_1"/>
                            </td>
                            <td>
                                <input type="hidden" name="total_row[]" value="1">
                                <input type="hidden" name="prev_id_1" value="0">
                                <select class="form-control subject" id="subject_id_1" name="subject_1">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($subject as $subject_key => $subject_value) { ?>
                                        <option value="<?php echo $subject_value->id; ?>"><?php
                                            $sub_code = ($subject_value->code != "") ? " (" . $subject_value->code . ")" : "";
                                            echo $subject_value->name . $sub_code;
                                            ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <select class="form-control staff" id="staff_id_1" name="staff_1">
                                    <option value=""><?php echo $this->lang->line('select') ?></option>
                                    <?php foreach ($staff as $staff_key => $staff_value) { ?>
                                        <option value="<?php echo $staff_value['id']; ?>"><?php echo $staff_value['name'] . " " . $staff_value['surname'] . " (" . $staff_value['employee_id'] . ")"; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name='room_no_1' placeholder='Room no' class="form-control room_no" id="room_no_1"/>
                            </td>
                            <td class="text-right">
                                <button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php if ($this->rbac->hasPrivilege('class_timetable', 'can_edit')) { ?>
                <button class="btn btn-primary btn-sm pull-right" type="submit"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
            <?php } ?>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var counter = 1;
        $("#add_row").on("click", function () {
            counter++;
            var newRow = $("<tr>");
            var cols = "";

            cols += '<td><input type="text" name="start_time_' + counter + '" placeholder="Start Time" class="form-control time_picker" id="start_time_' + counter + '"/></td>';
            cols += '<td><input type="text" name="end_time_' + counter + '" placeholder="End Time" class="form-control time_picker" id="end_time_' + counter + '"/></td>';
            cols += '<td><input type="hidden" name="total_row[]" value="' + counter + '"><input type="hidden" name="prev_id_' + counter + '" value="0"><select class="form-control subject" id="subject_id_' + counter + '" name="subject_' + counter + '"><option value=""><?php echo $this->lang->line('select') ?></option><?php foreach ($subject as $subject_key => $subject_value) { ?><option value="<?php echo $subject_value->id; ?>"><?php $sub_code = ($subject_value->code != "") ? " (" + subject_value.code + ")" : ""; echo subject_value.name + sub_code; ?></option><?php } ?></select></td>';
            cols += '<td><select class="form-control staff" id="staff_id_' + counter + '" name="staff_' + counter + '"><option value=""><?php echo $this->lang->line('select') ?></option><?php foreach ($staff as $staff_key => $staff_value) { ?><option value="<?php echo $staff_value['id']; ?>"><?php echo $staff_value['name'] + " " + staff_value.surname + " (" + staff_value.employee_id + ")"; ?></option><?php } ?></select></td>';
            cols += '<td><input type="text" name="room_no_' + counter + '" placeholder="Room no" class="form-control room_no" id="room_no_' + counter + '"/></td>';
            cols += '<td class="text-right"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="fa fa-trash"></i></button></td>';

            newRow.append(cols);
            $("table.order-list").append(newRow);
             $('.subject', newRow).select2({dropdownAutoWidth: true, width: '100%'});
              $('.staff', newRow).select2({dropdownAutoWidth: true, width: '100%'});
               $('.time_picker', newRow).timepicker({
                minuteStep: 1,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            });
        });
    });
</script>
<?php else: ?>
    <!-- STATIC UI CONTENT -->
    <style type="text/css">
        .relative label.text-danger{position: absolute; left:5px; bottom:0;}
    </style>
    <div class="row clearfix">
        <div class="col-md-12 column">
            <form method="POST" action="<?php echo site_url('admin/timetable/savegroup'); ?>" id="form_<?php echo $day; ?>" class="commentForm autoscroll">
               
                <input type="hidden" name="day" value="<?php echo $day; ?>">
                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                <input type="hidden" name="subject_group_id" value="<?php echo $subject_group_id; ?>">
                <div class="">   
                    <table class="table table-bordered table-hover order-list tablewidthRS" id="tab_logic">
                        <thead>
                            <tr>
                                <th style="width: 250px;"><?php echo $this->lang->line('period'); ?></th>
                                <th><?php echo $this->lang->line('subject') ?></th>
                                <th><?php echo $this->lang->line('teacher'); ?></th>
                                <th><?php echo $this->lang->line('room_no'); ?></th>
                                <th class="text-right"><?php echo $this->lang->line('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $counter = 1;
                            if (empty($periods)) { // Case 1: No periods defined at all
                                ?>
                                <tr class="info_message">
                                    <td colspan="5" class="text-center">
                                        <?php echo $this->lang->line('no_periods_defined'); ?><br/>
                                        <?php echo $this->lang->line('please_add_periods_from_settings'); ?>: 
                                        <a href="<?php echo site_url('admin/schsettings/timetablesettings'); ?>" target="_blank">http://localhost/minerva/schsettings/timetablesettings</a>
                                    </td>
                                </tr>
                                <?php
                            } elseif (empty($prev_record) && !empty($periods)) { // Case 2: Periods exist, but no timetable entries
                                ?>
                                <tr class="info_message">
                                    <td colspan="5" class="text-center">
                                        <?php echo $this->lang->line('no_timetable_entries_found_for_selection_criteria'); ?><br/>
                                        <?php echo $this->lang->line('to_add_entries_select_below'); ?>
                                    </td>
                                </tr>
                                <?php
                                // Fall through to display empty rows for each period
                                foreach ($periods as $period) {
                                    $existing_record = null; // Re-initialize for this context
                                    ?>
                                <tr id='addr<?php echo $counter; ?>'>
                                    <td>
                                        <input type="hidden" name="period_<?php echo $counter; ?>" value="<?php echo $period->id; ?>">
                                        <span><?php echo $period->name . ' (' . date('H:i', strtotime($period->time_from)) . ' - ' . date('H:i', strtotime($period->time_to)) . ')'; ?></span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="total_row[]" value="<?php echo $counter; ?>">
                                        <input type="hidden" name="prev_id_<?php echo $counter; ?>" value="0">
                                        <select class="form-control subject" id="subject_id_<?php echo $counter; ?>" name="subject_<?php echo $counter; ?>">
                                            <option value=""><?php echo$this->lang->line('select') ?></option>
                                            <?php foreach ($subject as $subject_key => $subject_value) { ?>
                                                <option value="<?php echo $subject_value->id; ?>"><?php
                                                    $sub_code = ($subject_value->code != "") ? " (" . $subject_value->code . ")" : "";
                                                    echo $subject_value->name . $sub_code;
                                                    ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control staff" id="staff_id_<?php echo $counter; ?>" name="staff_<?php echo $counter; ?>">
                                            <option value=""><?php echo $this->lang->line('select') ?></option>
                                            <?php foreach ($staff as $staff_key => $staff_value) { ?>
                                                <option value="<?php echo $staff_value['id']; ?>"><?php echo $staff_value['name'] . " " . $staff_value['surname'] . " (" . $staff_value['employee_id'] . ")"; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name='room_no_<?php echo $counter; ?>' value="" placeholder='Room no' class="form-control room_no" id="room_no_<?php echo $counter; ?>"/>
                                </td>
                                                                <td class="text-right"><button type="button" class="ibtnDel btn btn-danger btn-sm btn-danger"> <i class="fa fa-trash"></i></button></td>
                                                            </tr>
                                                            <?php
                                    $counter++;
                                }
                            } else { // Case 3: Periods exist and timetable entries exist (pre-fill)
                                foreach ($periods as $period) {
                                $existing_record = null;
                                if (!empty($prev_record)) {
                                    foreach ($prev_record as $rec) {
                                        if ($rec->period_id == $period->id) {
                                            $existing_record = $rec;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <tr id='addr<?php echo $counter; ?>'>
                                    <td>
                                        <input type="hidden" name="period_<?php echo $counter; ?>" value="<?php echo $period->id; ?>">
                                        <span><?php echo $period->name . ' (' . date('H:i', strtotime($period->time_from)) . ' - ' . date('H:i', strtotime($period->time_to)) . ')'; ?></span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="total_row[]" value="<?php echo $counter; ?>">
                                        <input type="hidden" name="prev_id_<?php echo $counter; ?>" value="<?php echo $existing_record ? $existing_record->id : 0; ?>">
                                        <select class="form-control subject" id="subject_id_<?php echo $counter; ?>" name="subject_<?php echo $counter; ?>">
                                            <option value=""><?php echo$this->lang->line('select') ?></option>
                                            <?php foreach ($subject as $subject_key => $subject_value) { ?>
                                                <option value="<?php echo $subject_value->id; ?>" <?php echo $existing_record && $existing_record->subject_group_subject_id == $subject_value->id ? 'selected' : ''; ?> >
                                                    <?php
                                                    $sub_code = ($subject_value->code != "") ? " (" . $subject_value->code . ")" : "";
                                                    echo $subject_value->name . $sub_code;
                                                    ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control staff" id="staff_id_<?php echo $counter; ?>" name="staff_<?php echo $counter; ?>">
                                            <option value=""><?php echo $this->lang->line('select') ?></option>
                                            <?php foreach ($staff as $staff_key => $staff_value) { ?>
                                                <option value="<?php echo $staff_value['id']; ?>" <?php echo $existing_record && $existing_record->staff_id == $staff_value['id'] ? 'selected' : ''; ?> ><?php echo $staff_value['name'] . " " . $staff_value['surname'] . " (" . $staff_value['employee_id'] . ")"; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name='room_no_<?php echo $counter; ?>' value="<?php echo $existing_record ? $existing_record->room_no : ''; ?>" placeholder='Room no' class="form-control room_no" id="room_no_<?php echo $counter; ?>"/>
                                </td>
                                                                <td class="text-right"><button type="button" class="ibtnDel btn btn-danger btn-sm btn-danger"> <i class="fa fa-trash"></i></button></td>
                                                            </tr>
                                                            <?php
                                $counter++;
                            } // Closing brace for foreach ($periods as $period)
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($this->rbac->hasPrivilege('class_timetable', 'can_edit')) { ?>
                    <button class="btn btn-primary btn-sm pull-right" type="submit"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                <?php } ?>
            </form>
        </div>
    </div>
<?php endif; ?>