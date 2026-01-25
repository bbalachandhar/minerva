<style type="text/css">
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 22px !important; border-radius: 0 !important; padding-left: 0 !important;}
    .input-group-addon .glyphicon{font-size: 12px;}

    .show{
        display : block;
        z-index: 100;
        background-image : url('../../backend/images/timeloader.gif');
        opacity : 0.6;
        background-repeat : no-repeat;
        background-position : center;
    }
    /* .tab-pane{min-height: 200px;}*/
    .commentForm .input-group {position: relative;display: block;border-collapse: separate;}
    .commentForm .input-group-addon{
        position: absolute;
        right: 26px;
        top: 0px;
        z-index: 3;
    }
    .relative{position: relative;}
    .commentForm .input-group-addon i,
    .commentForm .input-group-addon span{padding-left: 13px;}
    .commentForm .relative label.text-danger{position: absolute; bottom: 5px;}
    .addbtnright{ position: absolute;right: 0;top: -46px;}

    @media(max-width:767px){
        .timeresponsive{overflow-x: auto;     overflow-y: hidden;}
        .timeresponsive .dropdown-menu{z-index: 1060;    bottom: 0 !important; height: 250px; padding: 20px;}
        .tablewidthRS{width: 690px;}
    }
</style>
<script src="<?php echo base_url(); ?>backend/custom/jquery.validate.min.js"></script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                             <a href="<?php echo site_url('admin/timetable/bulk') ?>" class="btn btn-primary btn-sm"><i class="fa fa-upload"></i> <?php echo $this->lang->line('import_timetable'); ?></a>
                        </div>
                    </div>
                    <form class="create_time_table" action="<?php echo site_url('admin/timetable/create') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">

                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?><small class="req"> *</small></label>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($departmentlist as $department) {
    ?>
                                                <option value="<?php echo $department['id'] ?>" <?php
if (set_value('department_id') == $department['id']) {
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?><small class="req"> *</small></label>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?><small class="req"> *</small></label>
                                        <select  id="section_id" name="section_id" class="form-control" >
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('subject_group'); ?><small class="req"> *</small></label>
                                        <select  id="subject_group_id" name="subject_group_id" class="form-control" >
                                        </select>
                                        <span class="text-danger"><?php echo form_error('subject_group_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="insertbtn" class="btn btn-primary pull-right btn-sm"><?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php
if (isset($getDaysnameList)) {
    ?>
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_parameter_to_generate_time_table_quickly'); ?></h3>
                       
                </div>
                <div class="box-header ptbnull">                       
                <form action="#" method="POST" id="universal_from">           
         
        <div class="row">
            <div class="col-sm-4 col-lg-2 col-md-2">
                <div class="form-group">
                    <label for="form_name"><?php echo $this->lang->line('period_start_time'); ?><small class="req"> *</small></label>
                   
                    <div class="input-group">
                                        <input type="text" name="start_time" class="form-control time" id="start_time" value="">
                                        <div class="input-group-addon">
                                            <span class="fa fa-clock-o"></span>
                                        </div>
                                    </div>

                    <div class="text text-danger"></div>
                </div>
            </div>
            <div class="col-sm-4 col-lg-2 col-md-3">
                <div class="form-group">
                    <label for="form_email"><?php echo $this->lang->line('duration_minute'); ?><small class="req"> *</small></label>                     
                    <div class="input-group">
                                        <input type="number" name="duration" class="form-control" id="duration" value="">
                                        <div class="input-group-addon">
                                            <span class="fa fa-hourglass-start"></span>
                                        </div>
                                    </div>
                    <div class="text text-danger"></div>
                </div>
            </div>
            <div class="col-sm-3 col-lg-2 col-md-2">
                <div class="form-group">
                    <label for="form_phone"><?php echo $this->lang->line('interval_minute'); ?><small class="req"> *</small></label>
                   <div class="input-group">
                                        <input type="number" name="interval" class="form-control" id="interval" value="0">
                                        <div class="input-group-addon">
                                            <span class="fa fa-hourglass-start"></span>
                                        </div>
                                    </div>
                    <div class="text text-danger"></div>
                </div>
            </div>
                 <div class="col-sm-8 col-lg-2 col-md-2">
                <div class="form-group">
                    <label for="form_phone"><?php echo $this->lang->line('room_no'); ?></label>
<input type="text" name="rroom_no" class="form-control" id="froom_no">
                  
                    <div class="help-block with-errors"></div>
                </div>
            </div>
              <div class="col-sm-2">
                <div class="form-group">
                    <label for="form_phone" class="displayblock opacity d-sm-none">&nbsp;</label>
                    <input type="submit" class="btn btn-primary btn-sm btn-send smallbtn28" value="<?php echo $this->lang->line('apply'); ?>">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        </div>
   </form>   

                        </div>
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs" id="myTabs">
                                <?php
$count = 1;

    foreach ($getDaysnameList as $days_key => $days_value) {
        $cls = "";
        if ($count == 1) {
        }
        ?>
                                    <li <?php echo $cls; ?>><a href="#tab_<?php echo $count; ?>" data-c="<?php echo set_value('class_id'); ?>" data-days="<?php echo $days_value; ?>" data-s="<?php echo set_value('section_id'); ?>" data-group="<?php echo set_value('subject_group_id'); ?>" data-day="<?php echo $days_key; ?>" data-toggle="tab" aria-expanded="true"><?php echo $days_value; ?></a></li>

                                    <?php
$count++;
    }
    ?>
                            </ul>
                            <div class="tab-content">
                                <?php
$count = 1;
    foreach ($getDaysnameList as $days_key => $days_value) {
        $cls = "class='tab-pane'";
        if ($count == 1) {

        }
        ?>
                                    <div <?php echo $cls; ?> id="tab_<?php echo $count; ?>">
                                    </div>

                                    <?php
$count++;
    }
    ?>

                            </div>
                        </div>
                    </div>
                    <?php
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
        var prev_subject_group_id = '<?php echo set_value('subject_group_id') ?>';

        // Function to get classes by department and pre-select
        function getClassesByDepartment(department_id, selected_class_id) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset class dropdown
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset section dropdown
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset subject group dropdown

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
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset subject group dropdown

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

                        // If a section was previously selected, trigger subject group load
                        if (selected_section_id) {
                            getGroupByClassandSection(class_id, selected_section_id, prev_subject_group_id);
                        }
                    }
                });
            }
        }

        // Function to get subject groups by class and section and pre-select
        function getGroupByClassandSection(class_id, section_id, selected_subject_group_id) {
            $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Reset subject group dropdown

            if (class_id !== "" && section_id !== "") {
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/subjectgroup/getGroupByClassandSection",
                    data: {'class_id': class_id, 'section_id': section_id},
                    dataType: "json",
                    success: function (data) {
                        var div_data = ''; // Start with empty string
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (selected_subject_group_id && selected_subject_group_id == obj.subject_group_id) {
                                sel = "selected";
                            }
                            div_data += "<option value='" + obj.subject_group_id + "' " + sel + ">" + obj.name + "</option>";
                        });
                        $('#subject_group_id').append(div_data);
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

        // Section change event
        $(document).on('change', '#section_id', function (e) {
            var section_id = $(this).val();
            var class_id = $('#class_id').val();
            getGroupByClassandSection(class_id, section_id, null); // Don't pre-select subject group, it's a new selection
        });

        // --- Initial Load Logic ---
        // On page load, if a department was previously selected, trigger the cascade
        if (prev_department_id !== "") {
            getClassesByDepartment(prev_department_id, prev_class_id);
        }

        // After initial dropdown population, if all relevant prev values are set,
        // trigger getGroupdata for the first tab to load its content.
        if (prev_class_id !== "" && prev_section_id !== "" && prev_subject_group_id !== "") {
            var active_tab_anchor = $('#myTabs a:first');
            var target = active_tab_anchor.attr("href");
            var target_id = active_tab_anchor.attr("id");
            var ajax_data = {
                'day': active_tab_anchor.data('day'), // Assuming data-day is set correctly on the tab anchor
                'c': prev_class_id,
                's': prev_section_id,
                'group': prev_subject_group_id
            };
            active_tab_anchor.tab('show'); // Activate the tab
            getGroupdata(target, target_id, ajax_data);
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            var target_id = $(e.target).attr("id");
            var ajax_data = $(e.target).data();
            getGroupdata(target, target_id, ajax_data);
        });

        // Additional existing JavaScript
        $(document).on('submit','.create_time_table',function(e){
            document.getElementById("insertbtn").disabled = true;
        });   
        
        $(document).on('focus', '.time', function () {
            var $this = $(this);
            $this.datetimepicker({
                format: 'hh:mm A'
            });
        });
        
        var tot_count = 0; 
        var fixedTimeSchedule = [
            { from: "09:30 AM", to: "10:20 AM" },
            { from: "10:20 AM", to: "11:10 AM" },
            { from: "11:25 AM", to: "12:15 PM" },
            { from: "12:15 PM", to: "01:05 PM" },
            { from: "01:45 PM", to: "02:35 PM" },
            { from: "02:35 PM", to: "03:25 PM" },
            { from: "03:25 PM", to: "04:15 PM" }
        ];// This variable seems to be used later in addrow function, ensure it's initialized correctly if needed globally.

        // This part was wrapped in $(document).ready function previously, 
        // now it is outside but still called on document ready by virtue of being in the main ready function
        $(document).on('click', '.addrow', function () {                       
            var newRow = $("<tr>");
            var cols = "";
            var scheduleIndex = tot_count % fixedTimeSchedule.length;
            var timeFromValue = fixedTimeSchedule[scheduleIndex].from;
            var timeToValue = fixedTimeSchedule[scheduleIndex].to;
            cols += '<td class="relative"><input type="hidden" name="total_row[]" value="' + tot_count + '"><input type="hidden" name="prev_id_' + tot_count + '" value="0"><select class="form-control subject" id="subject_id_' + tot_count + '" name="subject_' + tot_count + '">' + $("#subject_dropdown").text() + '</select></td>';
            cols += '<td class="relative"><select class="form-control staff" id="staff_id_' + tot_count + '" name="staff_' + tot_count + '">' + $("#staff_dropdown").text() + '</select></span></td>';

            cols += '<td><div class="input-group"><input type="text" name="time_from_' + tot_count + '" class="form-control time_from time" id="time_from_' + tot_count + '" value="' + timeFromValue + '" aria-invalid="false"><div class="input-group-addon"><i class="fa fa-clock-o"></i></div></div></span></td>';

            cols += '<td><div class="input-group"><input type="text" name="time_to_' + tot_count + '" class="form-control time_to time" id="time_to_' + tot_count + '" value="' + timeToValue + '" aria-invalid="false"><div class="input-group-addon"><i class="fa fa-clock-o"></i></div></div></span></td>';

            cols += '<td><input type="text" class="form-control room_no" name="room_no_' + tot_count + '" id="room_no_' + tot_count + '"/> </td>';
            cols += '<td class="text-right"><button type="button" class="ibtnDel btn btn-danger btn-sm btn-danger"><i class="fa fa-trash"></i></button></td>';
            newRow.append(cols);

            $("table.order-list").append(newRow);


            $('.staff', newRow).select2({
                dropdownAutoWidth: true,
                width: '100%'
            });

            $('.subject', newRow).select2({
                dropdownAutoWidth: true,
                width: '100%'
            });
            tot_count++;
        });

        $(document).on("click", ".ibtnDel", function (event) {
            if($(this).closest('tr').prev('input').val()){
                if (confirm('<?php echo $this->lang->line("are_you_sure_you_want_to_delete"); ?>')) {
                    $(this).closest("tr").remove();
                    // counter -= 1 // counter is not defined in this scope
                }
                return false;
            }else{
                $(this).closest("tr").remove();
                // counter -= 1 // counter is not defined in this scope
            }
        });

        $(document).on('click', '.submit_subject_group', function () {
            var form_id = $(this).closest("form").attr('id');
            var target = $('.nav-tabs .active a').attr("href"); // activated tab
            var target_id = $('.nav-tabs .active a').attr("id"); // activated tab
            var ajax_data = $('.nav-tabs .active a').data(); // activated tab
            // Further logic if any
        });
        
        // This part of the code was also a separate $(document).ready block.
        // It is now integrated into the main $(document).ready block.
        $("#universal_from").validate({    
            rules: {
                start_time: {
                    required: true
                },
                duration: {
                    required: true
                },
                interval: {
                    required: true
                }
            },
            // Specify validation error messages
            messages: {
                start_time: "<?php echo $this->lang->line('required');?>",
                duration: "<?php echo $this->lang->line('required');?>",
                interval: "<?php echo $this->lang->line('required');?>",
            },
            errorClass: 'text-danger',
            validClass: 'valid',
            errorPlacement: function(error, element) {
                $("#errorText").empty();
                if(error[0].htmlFor == 'start_time') {
                    error.appendTo($(element).parents('div.form-group'));
                }
                if(error[0].htmlFor == 'duration') {
                    error.appendTo($(element).parents('div.form-group'));
                }
                if(error[0].htmlFor == 'interval') {
                    error.appendTo($(element).parents('div.form-group'));
                }
            },
            submitHandler: function(form) {
                let start_time= $('#start_time',form).val();
                let duration= $('#duration',form).val();
                let interval= $('#interval',form).val();
                let froom_no= $('#froom_no',form).val();
                var interest = $('div.tab-pane.active').find('table#tab_logic');
                $('tbody  > tr',interest).each(function() {
                    var new_time = moment(start_time, "hh:mm A")
                                    .add(duration, 'minutes')
                                    .format('hh:mm A');

                    var t_form = $(this).find(".time_from").val(start_time);    
                    var t_to = $(this).find(".time_to").val(new_time);    
                    var r_no = $(this).find(".room_no").val(froom_no);    

                    start_time=moment(new_time, "hh:mm A")
                                    .add(interval, 'minutes')
                                    .format('hh:mm A');
                });
            }
        });
    });

    // Existing functions that are outside $(document).ready need to stay outside
    // or be carefully integrated if they rely on global variables defined within ready.
    // For now, keeping them outside for minimal disruption.
    // However, the original functions getSectionByClass and getGroupByClassandSection 
    // are duplicated. The version inside the main $(document).ready is now gone, 
    // and the functions declared globally are used.
    // Also, the 'tot_count' variable is now global within the main ready block.
    // The 'counter' variable in ibtnDel was not declared, removing its usage.
    
    // The original getSectionByClass and getGroupByClassandSection from the global scope are now integrated above.
    // The $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) { ... }); block was also global.
    // It is now also integrated into the main $(document).ready block.

    // This is the function 'getGroupdata' which was previously global.
    // It remains global as it is called by the tab event listener.
    function getGroupdata(target, target_id, ajax_data) {
        var base_url = '<?php echo base_url() ?>'; // Ensure base_url is accessible
        $.ajax({
            type: 'POST',
            url: base_url + "admin/timetable/getBydategroupclasssection",
            data: {'day': ajax_data.day, 'class_id': ajax_data.c, 'section_id': ajax_data.s, 'subject_group_id': ajax_data.group},
            dataType: 'json',
            beforeSend: function () {
                $(target).addClass('show');
            },
            success: function (data) {
                $(target).html(data.html);

                $('.staff', target).select2({
                    dropdownAutoWidth: true,
                    width: '100%'
                });
                $('.subject', target).select2({
                    dropdownAutoWidth: true,
                    width: '100%'
                });
                // Assuming tot_count was meant to be global or passed correctly
                // tot_count = data.total_count + 1; 
            },
            error: function (xhr) { // if error occured

            },
            complete: function () {
                $(target).removeClass('show');
            }
        });
    }

</script>

<script type="text/template" id="staff_dropdown">
    <option value=""><?php echo $this->lang->line('select') ?></option>
    <?php
foreach ($staff as $staff_key => $staff_value) {
    ?>
        <option value="<?php echo $staff_value['id']; ?>"><?php echo $staff_value['name'] . " " . $staff_value['surname'] . " (" . $staff_value['employee_id'] . ")"; ?></option>
        <?php
}
?>
</script>

<script type="text/template" id="subject_dropdown">
    <option value=""><?php echo $this->lang->line('select') ?></option>
    <?php
foreach ($subject as $subject_key => $subject_value) {
    if ($subject_value->code !== '') {
        $sub_name = $subject_value->name . " (" . $subject_value->code . ")";
    } else {
        $sub_name = $subject_value->name;
    }
    ?>
        <option value="<?php echo $subject_value->id; ?>" ><?php echo $sub_name; ?></option>
        <?php
}
?>
</script>