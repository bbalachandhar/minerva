<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
    }
    .print, .print *
    {
        display: none;
    }
</style>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($departmentlist as $department) {
    ?>
                                                <option value="<?php echo $department['id'] ?>" <?php if (set_value('department_id') == $department['id']) {
        echo "selected=selected";
    }
    ?>><?php echo $department['department_name'] ?></option>
                                                <?php
}
?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('department_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select  id="section_id" name="section_id" class="form-control" >
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>
                </div>
                <?php
if (isset($result_array)) {
    ?>
                    <div class="box box-info" id="timetable">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('class_timetable'); ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="row print" >
                                <div class="col-md-12">
                                    <div class="col-md-offset-4 col-md-4">
                                        <center><b><?php echo $this->lang->line('class'); ?>: </b> <span class="cls"></span></center> 
                                    </div>
                                </div>
                            </div>
                            <?php
if (!empty($result_array)) {
        ?>
                                <div class="table-responsive">
                                    <div class="download_label"><?php echo $this->lang->line('class_timetable'); ?></div>
                                    <table class="table table-bordered example">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <?php echo $this->lang->line('subject'); ?>
                                                </th>
                                                <?php foreach ($getDaysnameList as $key => $value) {
            ?>
                                                    <th class="text text-center">
                                                        <?php echo $value; ?>
                                                    </th>
                                                <?php }
?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($result_array as $key => $timetable) {
            ?>
                                                <tr>
                                                    <th><?php echo $key; ?></th>
                                                    <?php
foreach ($timetable as $key => $value) {
                $status = $value->status;
                if ($status == "Yes") {
                    ?>
                                                            <td class="text text-center">
                                                                <div class="attachment-block clearfix">
                                                                    <?php
if ($value->start_time != "" && $value->end_time != "") {
                        ?>
                                                                        <strong class="text-green"><?php echo $value->start_time; ?></strong>
                                                                        <b class="text text-center">-</b>
                                                                        <strong class="text-green"><?php echo $value->end_time; ?></strong><br/>
                                                                        <strong class="text-green"><?php echo $this->lang->line('room_no'); ?>: <?php echo $value->room_no; ?></strong>
                                                                        <?php
} else {
                        ?>
                                                                        <b class="text text-center"><?php echo $this->lang->line('not'); ?> <br/><?php echo $this->lang->line('scheduled'); ?></b><br/>
                                                                        <strong class="text-green"></strong>
                                                                        <?php
}
                    ?>
                                                                </div>
                                                            </td>
                                                            <?php
} else {
                    ?>
                                                            <td class="text text-center">
                                                                <div class="attachment-block clearfix">
                                                                    <strong class="text-red"><?php echo $value->start_time; ?></strong>
                                                                </div>
                                                            </td>
                                                            <?php
}
            }
            ?>
                                                </tr>
                                                <?php
}
        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
} else {
        ?>
                                <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                                <?php
}
    ?>
                        </div>
                    </div>
                </div> 
            </div>  
            <?php
} else {
}
?>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var base_url = '<?php echo base_url() ?>';
        var prev_department_id = '<?php echo set_value('department_id') ?>';
        var prev_class_id = '<?php echo set_value('class_id') ?>';
        var prev_section_id = '<?php echo set_value('section_id') ?>';

        // Function to get classes by department and pre-select
        function getClassesByDepartment(department_id, selected_class_id) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset class dropdown
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset section dropdown

            if (department_id !== "") {
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/timetable/getclassesbydepartment",
                    data: {'department_id': department_id},
                    dataType: "json",
                    success: function (data) {
                        var div_data = ''; // Start with empty string
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_class_id && selected_class_id == obj.id) {
                                sel = "selected";
                            }
                            div_data += "<option value='" + obj.id + "' " + sel + ">" + obj.class + "</option>";
                        });
                        $('#class_id').append(div_data);

                        // If a class was previously selected, trigger section load
                        if (selected_class_id) {
                            getSectionByClass(selected_class_id, prev_section_id);
                        }
                    }
                });
            }
        }

        // Function to get sections by class and pre-select
        function getSectionByClass(class_id, selected_section_id) {
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset section dropdown
            if (class_id !== "") {
                $.ajax({
                    type: "GET",
                    url: base_url + "sections/getByClass",
                    data: {'class_id': class_id},
                    dataType: "json",
                    success: function (data) {
                        var div_data = ''; // Start with empty string
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_section_id && selected_section_id == obj.section_id) {
                                sel = "selected";
                            }
                            div_data += "<option value='" + obj.section_id + "' " + sel + ">" + obj.section + "</option>";
                        });
                        $('#section_id').append(div_data);
                    }
                });
            }
        }

        // --- Event Listeners ---

        // Department change event
        $(document).on('change', '#department_id', function (e) {
            var department_id = $(this).val();
            getClassesByDepartment(department_id, null); // Don't pre-select class, it's a new selection
        });

        // Class change event
        $(document).on('change', '#class_id', function (e) {
            var class_id = $(this).val();
            getSectionByClass(class_id, null); // Don't pre-select section, it's a new selection
        });

        // Section change event (submits form)
        // This is outside the main $(document).ready block in the original, so I'll put it here.
        // It's important to keep event listeners consistent.
        $(document).on('change', '#section_id', function (e) {
            $("form#schedule-form").submit();
        });


        // --- Initial Load Logic ---
        // On page load, if a department was previously selected, trigger the cascade
        if (prev_department_id !== "") {
            getClassesByDepartment(prev_department_id, prev_class_id);
            // The getClassesByDepartment will then call getSectionByClass if prev_class_id is set
        }

        // Existing feecategory_id change (unrelated to current issue, but keep it)
        $(document).on('change', '#feecategory_id', function (e) {
            $('#feetype_id').html("");
            var feecategory_id = $(this).val();
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "feemaster/getByFeecategory",
                data: {'feecategory_id': feecategory_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        div_data += "<option value=" + obj.id + ">" + obj.type + "</option>";
                    });
                    $('#feetype_id').append(div_data);
                }
            });
        });
    });
</script>
    $(document).on('change', '#section_id', function (e) {
        $("form#schedule-form").submit();
    });