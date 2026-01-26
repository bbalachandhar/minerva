<div class="row clearfix">
    <div class="col-md-12 column">
        <form method="POST" action="<?php echo site_url('admin/timetable/savegroup'); ?>" id="form_<?php echo $day; ?>" class="commentForm autoscroll">
            <input type="hidden" name="day" value="<?php echo $day; ?>">
            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
            <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
            <input type="hidden" name="subject_group_id" value="<?php echo $subject_group_id; ?>">
            <div class="">
                <table class="table table-bordered table-hover order-list" id="tab_logic">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('period'); ?></th>
                            <th><?php echo $this->lang->line('subject'); ?></th>
                            <th><?php echo $this->lang->line('teacher'); ?></th>
                            <th><?php echo $this->lang->line('room_no'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
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
                                    <?php echo $period->name . ' (' . $period->time_from . ' - ' . $period->time_to . ')'; ?>
                                    <input type="hidden" name="period_<?php echo $counter; ?>" value="<?php echo $period->id; ?>">
                                </td>
                                <td>
                                    <input type="hidden" name="total_row[]" value="<?php echo $counter; ?>">
                                    <input type="hidden" name="prev_id_<?php echo $counter; ?>" value="<?php echo $existing_record ? $existing_record->id : 0; ?>">
                                    <select class="form-control subject" id="subject_id_<?php echo $counter; ?>" name="subject_<?php echo $counter; ?>">
                                        <option value=""><?php echo $this->lang->line('select') ?></option>
                                        <?php foreach ($subject as $subject_key => $subject_value) { ?>
                                            <option value="<?php echo $subject_value->id; ?>" <?php echo $existing_record && $existing_record->subject_group_subject_id == $subject_value->id ? 'selected' : ''; ?>>
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
                                            <option value="<?php echo $staff_value['id']; ?>" <?php echo $existing_record && $existing_record->staff_id == $staff_value['id'] ? 'selected' : ''; ?>>
                                                <?php echo $staff_value['name'] . " " . $staff_value['surname'] . " (" . $staff_value['employee_id'] . ")"; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name='room_no_<?php echo $counter; ?>' value="<?php echo $existing_record ? $existing_record->room_no : ''; ?>" placeholder='Room no' class="form-control room_no" id="room_no_<?php echo $counter; ?>"/>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php
                            $counter++;
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

<script type="text/javascript">
    var form_id = "<?php echo $day ?>";
    $(function () {
        $('form#form_' + form_id).on('submit', function (event) {
            event.preventDefault();

            // Clear previous rules
            $('select[id^="subject_id_"]').each(function () { $(this).rules('remove'); });
            $('select[id^="staff_id_"]').each(function () { $(this).rules('remove'); });
            $('input[id^="room_no_"]').each(function () { $(this).rules('remove'); });

            // adding rules for inputs
            $('select[id^="subject_id_"]').each(function () {
                if ($(this).val() !== "") {
                    var row_id = $(this).attr('id').split('_').pop();
                    $('#staff_id_' + row_id).rules('add', { required: true, messages: { required: "<?php echo $this->lang->line('required');?>" } });
                    $('#room_no_' + row_id).rules('add', { required: true, messages: { required: "<?php echo $this->lang->line('required');?>" } });
                }
            });

            if ($('form#form_' + form_id).validate().form()) {
                var target = $('.nav-tabs .active a').attr("href");
                var target_id = $('.nav-tabs .active a').attr("id");
                var ajax_data = $('.nav-tabs .active a').data();

                $.ajax({
                    type: 'POST',
                    url: base_url + "admin/timetable/savegroup",
                    data: $('#form_' + form_id).serialize(),
                    dataType: 'json',
                    success: function (data) {
                        if (data.status == 1) {
                            successMsg(data.message);
                            if (typeof onSaveCallback === 'function') {
                                onSaveCallback();
                            } else {
                                // Original behavior: reload the tab content
                                var target = $('.nav-tabs .active a').attr("href");
                                var target_id = $('.nav-tabs .active a').attr("id");
                                var ajax_data = $('.nav-tabs .active a').data();
                                getGroupdata(target, target_id, ajax_data);
                            }
                        } else {
                            var list = $('<ul/>', { class: 'liststyle1' });
                            $.each(data.error, function (key, value) {
                                if (value != "") { list.append(value); }
                            });
                            errorMsg(list);
                        }
                    }
                });
            }
        });

        $('form#form_' + form_id).validate({
            debug: false,
            focusCleanup: false,
            errorClass: 'text-danger',
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.appendTo(element.closest('td'));
            }
        });

        $(document).on('click', '.ibtnDel', function() {
            var row = $(this).closest('tr');
            row.find('select, input[type="text"]').val('');
            row.find('select').val('').trigger('change');
        });
    });
</script>
