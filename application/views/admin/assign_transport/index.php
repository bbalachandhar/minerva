<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    </section>

    <div class="pull-right" style="margin-right: 15px; margin-bottom: 15px;">
        <a href="<?php echo site_url('admin/assign_transport_fee/index'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?></a>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('admin/assign_transport/index'); ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('class'); ?></label>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($classlist as $class) {
                                                ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (isset($class_id) && $class_id == $class['id']) echo 'selected'; ?>><?php echo $class['class'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="search_text"><?php echo $this->lang->line('search_by_name_admission_no'); ?></label>
                                        <input type="text" name="search_text" class="form-control" value="<?php echo set_value('search_text', isset($search_text) ? $search_text : ''); ?>">
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
                if (isset($studentlist)) {
                    ?>
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_list'); ?></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-sm btn-primary" id="save_assign_btn"><i class="fa fa-save"></i> <?php echo $this->lang->line('save_assignment'); ?></button>
                            </div>
                        </div>
                        <div class="box-body table-responsive">
                            <form id="assign_transport_form" action="<?php echo site_url('admin/assign_transport/assign'); ?>" method="post">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="route_id"><?php echo $this->lang->line('route'); ?></label>
                                            <select id="route_id" name="route_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php foreach ($route_list as $route) { ?>
                                                    <option value="<?php echo $route['id']; ?>"><?php echo $route['route_title']; ?></option>
                                                <?php } ?>
                                            </select>
                                            <span class="text-danger" id="route_id_error"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pickup_point_id"><?php echo $this->lang->line('pickup_point'); ?></label>
                                            <select id="pickup_point_id" name="pickup_point_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                            <span class="text-danger" id="pickup_point_id_error"></span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <table class="table table-striped table-bordered table-hover example">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select_all_students"></th>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('student_name'); ?></th>
                                            <th><?php echo $this->lang->line('class'); ?></th>
                                            <th><?php echo $this->lang->line('section'); ?></th>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                            <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                            <th><?php echo $this->lang->line('gender'); ?></th>
                                            <th><?php echo $this->lang->line('current_route'); ?></th>
                                            <th><?php echo $this->lang->line('current_pickup_point'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (empty($studentlist)) {
                                            ?>
                                            <tr>
                                                <td colspan="10" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                            </tr>
                                            <?php
                                        } else {
                                            foreach ($studentlist as $student) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="student_checkbox" name="student_session_id[]" value="<?php echo $student['student_session_id']; ?>">
                                                    </td>
                                                    <td><?php echo $student['admission_no']; ?></td>
                                                    <td><?php echo $student['firstname'] . " " . $student['lastname']; ?></td>
                                                    <td><?php echo $student['class']; ?></td>
                                                    <td><?php echo $student['section']; ?></td>
                                                    <td><?php echo $student['father_name']; ?></td>
                                                    <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob'])); ?></td>
                                                    <td><?php echo $student['gender']; ?></td>
                                                    <td><?php echo $student['route_title']; ?></td>
                                                    <td><?php echo $student['pickup_point_name']; ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', isset($section_id) ? $section_id : ''); ?>';
        getSectionByClass(class_id, section_id);

        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html('');
            var class_id = $(this).val();
            getSectionByClass(class_id, 0);
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
                        $.each(data, function (i, obj) {
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

        $(document).on('change', '#route_id', function (e) {
            $('#pickup_point_id').html('');
            var route_id = $(this).val();
            getPickupPointsByRoute(route_id, 0);
        });

        function getPickupPointsByRoute(route_id, pickup_point_id) {
            if (route_id != "") {
                $('#pickup_point_id').html("");
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "POST",
                    url: base_url + "admin/route/getPickupPointsByRoute",
                    data: {'route_id': route_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj) {
                            var sel = "";
                            if (pickup_point_id == obj.id) {
                                sel = "selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.name + " (" + obj.fees + ")</option>";
                        });
                        $('#pickup_point_id').append(div_data);
                    }
                });
            }
        }

        $('#select_all_students').on('click', function () {
            if (this.checked) {
                $('.student_checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $('.student_checkbox').each(function () {
                    this.checked = false;
                });
            }
        });

        $('.student_checkbox').on('click', function () {
            if ($('.student_checkbox:checked').length == $('.student_checkbox').length) {
                $('#select_all_students').prop('checked', true);
            } else {
                $('#select_all_students').prop('checked', false);
            }
        });

        $('#save_assign_btn').on('click', function () {
            var route_id = $('#route_id').val();
            var pickup_point_id = $('#pickup_point_id').val();
            var student_session_ids = [];

            $('.student_checkbox:checked').each(function () {
                student_session_ids.push($(this).val());
            });

            if (student_session_ids.length === 0) {
                alert('<?php echo $this->lang->line('please_select_at_least_one_student'); ?>');
                return;
            }

            if (route_id === "" || pickup_point_id === "") {
                if (route_id === "") {
                    $('#route_id_error').text('<?php echo $this->lang->line('the_route_field_is_required'); ?>');
                } else {
                    $('#route_id_error').text('');
                }
                if (pickup_point_id === "") {
                    $('#pickup_point_id_error').text('<?php echo $this->lang->line('the_pickup_point_field_is_required'); ?>');
                } else {
                    $('#pickup_point_id_error').text('');
                }
                return;
            } else {
                $('#route_id_error').text('');
                $('#pickup_point_id_error').text('');
            }

            var $this = $(this);
            $this.button('loading');

            $.ajax({
                type: "POST",
                url: "<?php echo site_url('admin/assign_transport/assign'); ?>",
                data: $('#assign_transport_form').serialize(),
                dataType: "json",
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        window.location.href = "<?php echo site_url('admin/assign_transport/index'); ?>";
                    }
                    $this.button('reset');
                },
                error: function (xhr, status, error) {
                    errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                    console.log("AJAX Error: ", status, error, xhr.responseText);
                    $this.button('reset');
                }
            });
        });
    });
</script>