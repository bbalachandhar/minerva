<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('import_timetable'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                             <a href="<?php echo base_url() ?>/backend/import/sample_timetable.csv" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_data'); ?></a>
                        </div>
                    </div>
                    <?php echo $this->session->flashdata('msg'); ?>
                    <form role="form" id="addfee" class="padd-around20" action="<?php echo site_url('admin/timetable/bulk') ?>" method="post" enctype="multipart/form-data">
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?><small class="req"> *</small></label>
                                        <input class="form-control" type="file" name="file" id="file" size="20" />
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="insertbtn" class="btn btn-primary pull-right btn-sm"><?php echo $this->lang->line('import_timetable'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
    });
</script>
