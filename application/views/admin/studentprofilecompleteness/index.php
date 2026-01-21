<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php echo $this->lang->line('reports'); ?> <small> <?php echo $this->lang->line('filter_by_name'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $title; ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/studentprofilecompleteness') ?>" method="post" class="">
                            <div class="row">
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($classlist as $class) {
                                                ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id', $class_id) == $class['id']) echo "selected=selected" ?>><?php echo $class['class'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select  id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label class="d-block">&nbsp;</label>
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('section'); ?></th>
                                        <th><?php echo "Profile Completeness"; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($students)) { ?>
                                        <tr>
                                            <td colspan="5" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                    <?php } else {
                                        foreach ($students as $student) {
                                            $total_fields = 36;
                                            $filled_fields = 0;
                                            if (!empty($student['firstname'])) $filled_fields++;
                                            if (!empty($student['lastname'])) $filled_fields++;
                                            if (!empty($student['dob'])) $filled_fields++;
                                            if (!empty($student['gender'])) $filled_fields++;
                                            if (!empty($student['mobileno'])) $filled_fields++;
                                            if (!empty($student['email'])) $filled_fields++;
                                            if (!empty($student['current_address'])) $filled_fields++;
                                            if (!empty($student['permanent_address'])) $filled_fields++;
                                            if (!empty($student['father_name'])) $filled_fields++;
                                            if (!empty($student['father_phone'])) $filled_fields++;
                                            if (!empty($student['father_occupation'])) $filled_fields++;
                                            if (!empty($student['mother_name'])) $filled_fields++;
                                            if (!empty($student['mother_phone'])) $filled_fields++;
                                            if (!empty($student['mother_occupation'])) $filled_fields++;
                                            if (!empty($student['guardian_name'])) $filled_fields++;
                                            if (!empty($student['guardian_phone'])) $filled_fields++;
                                            if (!empty($student['guardian_email'])) $filled_fields++;
                                            if (!empty($student['adhar_no'])) $filled_fields++;
                                            if (!empty($student['bank_account_no'])) $filled_fields++;
                                            if (!empty($student['bank_name'])) $filled_fields++;
                                            if (!empty($student['ifsc_code'])) $filled_fields++;
                                            if (!empty($student['image'])) $filled_fields++;
                                            if (!empty($student['father_pic'])) $filled_fields++;
                                            if (!empty($student['mother_pic'])) $filled_fields++;
                                            if (!empty($student['guardian_pic'])) $filled_fields++;
                                            if (!empty($student['previous_school'])) $filled_fields++;
                                            if (!empty($student['hsc_reg_no'])) $filled_fields++;
                                            if (!empty($student['ug_reg_no'])) $filled_fields++;
                                            if (!empty($student['emis_num'])) $filled_fields++;
                                            if (!empty($student['migration_cert_num'])) $filled_fields++;
                                            if (!empty($student['medium'])) $filled_fields++;
                                            if (!empty($student['religion'])) $filled_fields++;
                                            if (!empty($student['cast'])) $filled_fields++;
                                            if (!empty($student['blood_group'])) $filled_fields++;
                                            if (!empty($student['height'])) $filled_fields++;
                                            if (!empty($student['weight'])) $filled_fields++;
                                            $percentage = ($filled_fields / $total_fields) * 100;
                                            ?>
                                            <tr>
                                                <td><?php echo $student['firstname'] . " " . $student['lastname']; ?></td>
                                                <td><?php echo $student['admission_no']; ?></td>
                                                <td><?php echo $student['class']; ?></td>
                                                <td><?php echo $student['section']; ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-striped <?php if($percentage < 30) { echo 'progress-bar-danger'; } elseif($percentage < 60) { echo 'progress-bar-warning'; } elseif($percentage < 90) { echo 'progress-bar-info'; } else { echo 'progress-bar-success'; } ?>" role="progressbar" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage; ?>%">
                                                            <?php echo round($percentage, 2); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', $section_id) ?>';
        getSectionByClass(class_id, section_id);
        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        });
        function getSectionByClass(class_id, section_id) {
            if (class_id != "") {
                $('#section_id').html("");
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "GET",
                    url: base_url + "sections/getByClass",
                    data: {'class_id': class_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = "";
                            if (section_id == obj.section_id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                        });
                        $('#section_id').append(div_data);
                    }
                });
            }
        }
    });
</script>