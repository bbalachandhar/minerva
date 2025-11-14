<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="clearfix">
            <h1 class="pull-left">
                <i class="fa fa-bus"></i> <?php echo $this->lang->line('transport'); ?> <small><?php echo $this->lang->line('assign_transport_fee'); ?></small>
            </h1>
            <div class="pull-right">
                <a href="<?php echo site_url('admin/transport/index'); ?>" class="btn btn-primary btn-sm" style="margin-right: 5px;"><i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?></a>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-sm btn-primary" id="assign_button">Assign Pickup Point</button>
                        </div>
                    </div>
                    <form action="<?php echo site_url('admin/assign_transport_fee/search'); ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="section_id"><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="route_filter_id"><?php echo $this->lang->line('route'); ?></label>
                                        <select id="route_filter_id" name="route_filter_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($route_list as $route) { ?>
                                                <option value="<?php echo $route['id']; ?>" <?php if (isset($route_filter_id) && $route_filter_id == $route['id']) echo 'selected'; ?>><?php echo $route['route_title']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('route_filter_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pickup_point_filter_id"><?php echo $this->lang->line('pickup_point'); ?></label>
                                        <select id="pickup_point_filter_id" name="pickup_point_filter_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('pickup_point_filter_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="search_text"><?php echo $this->lang->line('search_by_keywords'); ?></label>
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
                                <button type="button" class="btn btn-sm btn-primary" id="save_assignments_button"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </div>
                        <div class="box-body table-responsive">
                            <form id="assign_form" action="<?php echo site_url('admin/assign_transport_fee/assign'); ?>" method="post">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <input type="hidden" name="route_id" id="assign_route_id">
                                <input type="hidden" name="pickup_point_id" id="assign_pickup_point_id">
                                <table class="table table-striped table-bordered table-hover example">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select_all"></th>
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
                                                        <input type="checkbox" class="checkbox" name="student_session_id[]" value="<?php echo $student['student_session_id']; ?>" <?php echo (!empty($student['route_pickup_point_id'])) ? 'checked' : ''; ?>>
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

        var route_filter_id = $('#route_filter_id').val();
        var pickup_point_filter_id = '<?php echo set_value('pickup_point_filter_id', isset($pickup_point_filter_id) ? $pickup_point_filter_id : ''); ?>';
        getPickupPointsByRouteFilter(route_filter_id, pickup_point_filter_id);


        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html('');
            var class_id = $(this).val();
            getSectionByClass(class_id, 0);
        });

        $(document).on('change', '#route_filter_id', function (e) {
            $('#pickup_point_filter_id').html('');
            var route_filter_id = $(this).val();
            getPickupPointsByRouteFilter(route_filter_id, 0);
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

        function getPickupPointsByRouteFilter(route_id, pickup_point_id) {
            if (route_id != "") {
                $('#pickup_point_filter_id').html("");
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
                        $('#pickup_point_filter_id').append(div_data);
                    }
                });
            }
        }

        $('#select_all').on('click', function () {
            if (this.checked) {
                $('.checkbox').each(function () {
                    this.checked = true;
                });
            } else {
                $('.checkbox').each(function () {
                    this.checked = false;
                });
            }
        });

        $('.checkbox').on('click', function () {
            if ($('.checkbox:checked').length == $('.checkbox').length) {
                $('#select_all').prop('checked', true);
            } else {
                $('#select_all').prop('checked', false);
            }
        });

        $(document).on('change', '#modal_route_id', function (e) {
            $('#modal_pickup_point_id').html('');
            var route_id = $(this).val();
            getPickupPointsByModalRoute(route_id, 0);
        });

        function getPickupPointsByModalRoute(route_id, pickup_point_id) {
            if (route_id != "") {
                $('#modal_pickup_point_id').html("");
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
                        $('#modal_pickup_point_id').append(div_data);
                    }
                });
            }
        }

        // Change Assign button to redirect to new page
        $('#assign_button').on('click', function() {
            window.location.href = '<?php echo site_url('admin/assign_transport/index'); ?>'; // New page for assignment
        });

        $('#save_assignments_button').on('click', function () {
            var unassigned_student_session_ids = [];
            $('.checkbox:not(:checked)').each(function () {
                unassigned_student_session_ids.push($(this).val());
            });

            // Get all student_session_ids currently displayed on the page
            var all_student_session_ids = [];
            $('.checkbox').each(function () {
                all_student_session_ids.push($(this).val());
            });

            if (all_student_session_ids.length === 0) {
                alert('<?php echo $this->lang->line('no_students_found'); ?>');
                return;
            }

            if (confirm('<?php echo $this->lang->line('are_you_sure_you_want_to_save_transport_assignments'); ?>')) {
                var $this = $(this);
                $this.button('loading');

                $.ajax({
                    type: "POST",
                    url: "<?php echo site_url('admin/assign_transport_fee/save_assignments'); ?>",
                    data: {
                        unassigned_student_session_ids: unassigned_student_session_ids,
                        all_student_session_ids: all_student_session_ids, // Pass all IDs to determine which ones are still assigned
                        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                    },
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
                            window.location.href = "<?php echo site_url('admin/assign_transport_fee/search'); ?>";
                        }
                        $this.button('reset');
                    },
                    error: function (xhr, status, error) {
                        errorMsg("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                        console.log("AJAX Error: ", status, error, xhr.responseText);
                        $this.button('reset');
                    }
                });
            }
        });

        // Original assign_button logic (now removed from HTML)
        // $('#assign_button').on('click', function() {
        //     window.location.href = '<?php echo site_url('admin/assign_transport/index'); ?>';
        // });
    });
</script>