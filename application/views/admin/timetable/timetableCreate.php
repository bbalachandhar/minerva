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

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>'; // Make base_url global
    var prev_department_id = '<?php echo set_value('department_id') ?>';
    var prev_class_id = '<?php echo set_value('class_id') ?>';
    var prev_section_id = '<?php echo set_value('section_id') ?>';
    var prev_subject_group_id = '<?php echo set_value('subject_group_id') ?>';

    function getClassesByDepartment(department_id, selected_class_id) {
        $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); 
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); 
        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); 

        if (department_id !== "") {
            $.ajax({
                type: "POST",
                url: base_url + "admin/timetable/getclassesbydepartment",
                data: {'department_id': department_id},
                dataType: "json",
                success: function (data) {
                    var div_data = ''; 
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (selected_class_id && selected_class_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value='" + obj.id + "' " + sel + ">" + obj.class + "</option>";
                    });
                    $('#class_id').append(div_data);

                    if (selected_class_id) {
                        getSectionByClass(selected_class_id, prev_section_id);
                    }
                }
            });
        }
    }

    function getSectionByClass(class_id, selected_section_id) {
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');

        if (class_id !== "") {
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    var div_data = '';
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (selected_section_id && selected_section_id == obj.section_id) {
                            sel = "selected";
                        }
                        div_data += "<option value='" + obj.section_id + "' " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);

                    if (selected_section_id) {
                        getGroupByClassandSection(class_id, selected_section_id, prev_subject_group_id);
                    }
                }
            });
        }
    }

    function getGroupByClassandSection(class_id, section_id, selected_subject_group_id) {
        $('#subject_group_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); 

        if (class_id !== "" && section_id !== "") {
            $.ajax({
                type: "POST",
                url: base_url + "admin/subjectgroup/getGroupByClassandSection",
                data: {'class_id': class_id, 'section_id': section_id},
                dataType: "json",
                success: function (data) {
                    var div_data = '';
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
</script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?> <?php echo ($is_dynamic_timetable) ? '(Dynamic Timetable)' : '(Static Timetable)'; ?></h3>
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
    function getGroupdata(target, target_id, ajax_data) {
        console.log("getGroupdata: AJAX request started for target:", target, "with data:", ajax_data);
        $(target).addClass('show'); // Add 'show' class here before AJAX
        $.ajax({
            type: 'POST',
            url: base_url + "admin/timetable/getBydategroupclasssection",
            data: {'day': ajax_data.day, 'class_id': ajax_data.c, 'section_id': ajax_data.s, 'subject_group_id': ajax_data.group},
            dataType: 'json',
            beforeSend: function () {
                // Already added 'show' class above to ensure it's active immediately
            },
            success: function (data) {
                console.log("getGroupdata: AJAX success. Data received:", data);
                $(target).html(data.html); // Re-enable HTML injection

                $('.staff', target).select2({dropdownAutoWidth: true, width: '100%'});
                $('.subject', target).select2({dropdownAutoWidth: true, width: '100%'});
                $('.period', target).select2({dropdownAutoWidth: true, width: '100%'});

                // Update global tot_count from server response
                tot_count = data.total_count + 1; // +1 to account for the next new row
            },
            error: function (xhr, status, error) {
                console.error("getGroupdata: AJAX error. Status:", status, "Error:", error, "Response:", xhr.responseText);
            },
            complete: function () {
                console.log("getGroupdata: AJAX complete. Removing 'show' class from target:", target);
                $(target).removeClass('show');
            }
        });
    }

    $(document).ready(function () {
        var prev_department_id = '<?php echo set_value('department_id') ?>';
        var prev_class_id = '<?php echo set_value('class_id') ?>';
        var prev_section_id = '<?php echo set_value('section_id') ?>';
        var prev_subject_group_id = '<?php echo set_value('subject_group_id') ?>';

        $(document).on('change', '#department_id', function (e) {
            var department_id = $(this).val();
            getClassesByDepartment(department_id, null);
        });

        $(document).on('change', '#class_id', function (e) {
            var class_id = $(this).val();
            getSectionByClass(class_id, null);
        });

        $(document).on('change', '#section_id', function (e) {
            var section_id = $(this).val();
            var class_id = $('#class_id').val();
            getGroupByClassandSection(class_id, section_id, null);
        });

        if (prev_department_id !== "") {
            getClassesByDepartment(prev_department_id, prev_class_id);
        }

        if (prev_class_id !== "" && prev_section_id !== "" && prev_subject_group_id !== "") {
            var active_tab_anchor = $('#myTabs a:first');
            var target = active_tab_anchor.attr("href");
            var target_id = active_tab_anchor.attr("id");
            var ajax_data = {
                'day': active_tab_anchor.data('day'),
                'c': prev_class_id,
                's': prev_section_id,
                'group': prev_subject_group_id
            };
            active_tab_anchor.tab('show'); 
            getGroupdata(target, target_id, ajax_data);
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href");
            var target_id = $(e.target).attr("id");
            var ajax_data = $(e.target).data();
            console.log('Client-side AJAX data for tab:', ajax_data); // <<< Add this log
            getGroupdata(target, target_id, ajax_data);
        });

        $(document).on('submit','.create_time_table',function(e){
            document.getElementById("insertbtn").disabled = true;
        });   
        
        // Moved ibtnDel handler from addrow.php
        $(document).on('click', '.ibtnDel', function() {
            var row = $(this).closest('tr');
            var recordIdInput = row.find('input[name^="prev_id_"]');
            var recordId = recordIdInput.val();

            if (recordId && recordId !== '0') {
                $(this).closest('form').append('<input type="hidden" name="deleted_ids[]" value="' + recordId + '">');
            }
            row.remove();
        });
    });
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
        ?>
        <option value="<?php echo $subject_value->id; ?>" ><?php echo $subject_value->name . " (" . $subject_value->code . ")"; ?></option>
        <?php
    }
    ?>
</script>
<?php if (isset($is_dynamic_timetable) && $is_dynamic_timetable == 1) { ?>
<!-- Modal -->
<div class="modal fade" id="confirm-navigation-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('confirm_navigation'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->lang->line('unsaved_changes_alert'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="button" class="btn btn-warning" id="discard-changes-btn"><?php echo $this->lang->line('discard'); ?></button>
                <button type="button" class="btn btn-primary" id="save-changes-btn"><?php echo $this->lang->line('save'); ?></button>
            </div>
        </div>
    </div>
</div>
            
<?php } else { ?>

<?php } ?>