
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> <small><?php echo $this->lang->line('collect_incidental_fee'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('collect_incidental_fee'); ?></h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#nonStudentFeeCollectionModal">
                                <i class="fa fa-user-plus"></i> <?php echo $this->lang->line('collect_fee_from_others'); ?>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#classWiseCollectionModal">
                                <i class="fa fa-users"></i> <?php echo $this->lang->line('collect_class_wise'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                        <?php echo $this->customlib->get = $this->customlib->getCSRF(); ?>

                        <form action="<?php echo site_url('admin/collect_incidental_fee/searchStudent') ?>" method="post" accept-charset="utf-8" class="form-horizontal">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="class_id" class="col-sm-4 control-label"><?php echo $this->lang->line('class'); ?></label>
                                        <div class="col-sm-8">
                                            <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php foreach ($classes as $class) { ?>
                                                    <option value="<?php echo $class['id'] ?>" <?php echo set_select('class_id', $class['id'], (isset($class_id) && $class_id == $class['id']) ? TRUE : FALSE); ?>><?php echo $class['class'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="section_id" class="col-sm-4 control-label"><?php echo $this->lang->line('section'); ?></label>
                                        <div class="col-sm-8">
                                            <select id="section_id" name="section_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php if (isset($sections)) {
                                                    foreach ($sections as $section) { ?>
                                                        <option value="<?php echo $section['id'] ?>" <?php echo set_select('section_id', $section['id'], (isset($section_id) && $section_id == $section['id']) ? TRUE : FALSE); ?>><?php echo $section['section'] ?></option>
                                                    <?php } 
                                                } ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search_text" class="col-sm-4 control-label">Search</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="search_text" name="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>" placeholder="Search by name, admission no, etc.">
                                        </div>
                                    </div>
                                </div>
                                <!-- The session dropdown has been removed as per user request -->
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </form>

                        <?php if (isset($student_list) && !empty($student_list)) { ?>
                            <hr/>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="incidental_fee_table" class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                                    <th><?php echo $this->lang->line('class'); ?></th>
                                                    <th><?php echo $this->lang->line('section'); ?></th>
            <th><?php echo $this->lang->line('gender'); ?></th>
            <th><?php echo $this->lang->line('father_name'); ?></th>
            <th class="text-right no-print"><?php echo $this->lang->line('action'); ?></th>
        </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($student_list as $student) { ?>
                                                    <tr>
                                                        <td><?php echo $student['admission_no']; ?></td>
                                                        <td><?php echo $student['firstname'] . " " . $student['lastname']; ?></td>
                                                        <td><?php echo $student['class']; ?></td>
                                                        <td><?php echo $student['section']; ?></td>
                                                        <td><?php echo $student['gender']; ?></td>
                <td><?php echo $student['father_name']; ?></td>
                <td class="mailbox-date pull-right no-print">
                    <button type="button" class="btn btn-default btn-xs collect_fee_btn" data-student_id="<?php echo $student['id']; ?>" data-session_id="<?php echo $session_id; ?>"
                        data-toggle="tooltip" title="<?php echo $this->lang->line('collect_fee'); ?>">
                        <i class="fa fa-money"></i>
                    </button>
                </td>
            </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Fee Collection Modal -->
<div class="modal fade" id="feeCollectionModal" tabindex="-1" role="dialog" aria-labelledby="feeCollectionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="feeCollectionModalLabel"><?php echo $this->lang->line('collect_incidental_fee'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/collect_incidental_fee/index') ?>" method="post" accept-charset="utf-8" id="form_add_incidental_fee">
                <div class="modal-body">
                    <div id="student_details_modal"></div>
                    <hr/>
                    <h4><?php echo $this->lang->line('outstanding_assignments'); ?></h4>
                    <div id="outstanding_assignments_list"></div>

                    <hr/>
                    <input type="hidden" name="student_id" id="modal_student_id">
                    <input type="hidden" name="session_id" id="modal_session_id">
                    <input type="hidden" name="incidental_fee_assignment_id" id="modal_incidental_fee_assignment_id">
                    <div class="form-group">
                        <label for="fee_type_id_modal"><?php echo $this->lang->line('fee_type'); ?></label>
                        <select id="fee_type_id_modal" name="fee_type_id" class="form-control" >
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php foreach ($fee_types as $fee_type) { ?>
                                <option value="<?php echo $fee_type['id'] ?>"><?php echo $fee_type['title'] ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger"><?php echo form_error('fee_type_id'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="amount_collected"><?php echo $this->lang->line('amount_collected'); ?></label>
                        <input id="amount_collected" name="amount_collected" type="number" class="form-control" />
                        <span class="text-danger"><?php echo form_error('amount_collected'); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="notes"><?php echo $this->lang->line('notes'); ?></label>
                        <textarea id="notes" name="notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('collect_fee'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Class Wise Fee Collection Modal -->
<div class="modal fade" id="classWiseCollectionModal" tabindex="-1" role="dialog" aria-labelledby="classWiseCollectionModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="classWiseCollectionModalLabel"><?php echo $this->lang->line('collect_incidental_fee_class_wise'); ?></h4>
            </div>
            <form id="form_class_wise_incidental_fee" method="post" accept-charset="utf-8">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="class_id_cw"><?php echo $this->lang->line('class'); ?></label>
                                <select id="class_id_cw" name="class_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($classes as $class) { ?>
                                        <option value="<?php echo $class['id'] ?>"><?php echo $class['class'] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="class_id_cw_error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="section_id_cw"><?php echo $this->lang->line('section'); ?></label>
                                <select id="section_id_cw" name="section_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                                <span class="text-danger" id="section_id_cw_error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="incidental_fee_type_id_cw"><?php echo $this->lang->line('incidental_fee_type'); ?></label>
                                <select id="incidental_fee_type_id_cw" name="incidental_fee_type_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($fee_types as $fee_type) { ?>
                                        <option value="<?php echo $fee_type['id'] ?>"><?php echo $fee_type['title'] ?></option>
                                    <?php } ?>
                                </select>
                                <span class="text-danger" id="incidental_fee_type_id_cw_error"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-primary btn-sm" id="search_students_cw_btn"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </div>
                    <hr/>
                    <div id="class_wise_student_list_container">
                        <!-- Student list with amount fields will be loaded here via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="save_class_wise_fees_btn" style="display:none;"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Non-Student Fee Collection Modal -->
<div class="modal fade" id="nonStudentFeeCollectionModal" tabindex="-1" role="dialog" aria-labelledby="nonStudentFeeCollectionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="nonStudentFeeCollectionModalLabel"><?php echo $this->lang->line('collect_fee_from_others'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/collect_incidental_fee/collectNonStudentFee') ?>" method="post" accept-charset="utf-8" id="form_add_non_student_incidental_fee">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="non_student_name"><?php echo $this->lang->line('non_student_name'); ?></label>
                        <input id="non_student_name" name="non_student_name" type="text" class="form-control" />
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="fee_type_id_non_student"><?php echo $this->lang->line('fee_type'); ?></label>
                        <select id="fee_type_id_non_student" name="fee_type_id" class="form-control" >
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php foreach ($fee_types as $fee_type) { ?>
                                <option value="<?php echo $fee_type['id'] ?>"><?php echo $fee_type['title'] ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="amount_collected_non_student"><?php echo $this->lang->line('amount_collected'); ?></label>
                        <input id="amount_collected_non_student" name="amount_collected" type="number" step="0.01" class="form-control" />
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="notes_non_student"><?php echo $this->lang->line('notes'); ?></label>
                        <textarea id="notes_non_student" name="notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('collect_fee'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Non-Student Fee Collection Modal -->
<div class="modal fade" id="nonStudentFeeCollectionModal" tabindex="-1" role="dialog" aria-labelledby="nonStudentFeeCollectionModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="nonStudentFeeCollectionModalLabel"><?php echo $this->lang->line('collect_fee_from_others'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/collect_incidental_fee/collectNonStudentFee') ?>" method="post" accept-charset="utf-8" id="form_add_non_student_incidental_fee">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="non_student_name"><?php echo $this->lang->line('non_student_name'); ?></label>
                        <input id="non_student_name" name="non_student_name" type="text" class="form-control" />
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="fee_type_id_non_student"><?php echo $this->lang->line('fee_type'); ?></label>
                        <select id="fee_type_id_non_student" name="fee_type_id" class="form-control" >
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php foreach ($fee_types as $fee_type) { ?>
                                <option value="<?php echo $fee_type['id'] ?>"><?php echo $fee_type['title'] ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="amount_collected_non_student"><?php echo $this->lang->line('amount_collected'); ?></label>
                        <input id="amount_collected_non_student" name="amount_collected" type="number" step="0.01" class="form-control" />
                        <span class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label for="notes_non_student"><?php echo $this->lang->line('notes'); ?></label>
                        <textarea id="notes_non_student" name="notes" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('collect_fee'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Initialize DataTable
        $('#incidental_fee_table').DataTable({
            "destroy": true,
        });



        // Class change event
        $('#class_id').on('change', function () {
            var class_id = $(this).val();
            $('#section_id').html('');
            if (class_id) {
                $.ajax({
                    url: baseurl + 'admin/collect_incidental_fee/getSectionsByClass',
                    type: "POST",
                    data: {class_id: class_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (key, value) {
                            $('#section_id').append('<option value="' + value.id + '">' + value.section + '</option>');
                        });
                    }
                });
            }
        });

        // Collect fee button click
        $(document).on('click', '.collect_fee_btn', function () {
            var student_id = $(this).data('student_id');
            var session_id = $(this).data('session_id');

            $('#modal_student_id').val(student_id);
            $('#modal_session_id').val(session_id);
            $('#modal_incidental_fee_assignment_id').val(''); // Clear for new collection
            $('#amount_collected').val('');
            $('#notes').val('');
            $('#fee_type_id_modal').val('');


            // Fetch student details and outstanding assignments
            $.ajax({
                url: baseurl + 'admin/collect_incidental_fee/getStudentDetails',
                type: "POST",
                data: {student_id: student_id, session_id: session_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                dataType: 'json',
                success: function (response) {
                    var student_detail = response.student_detail;
                    var outstanding_assignments = response.outstanding_assignments;

                    // Populate student details
                    var student_html = '<p><strong><?php echo $this->lang->line('student_name'); ?>:</strong> ' + student_detail.firstname + ' ' + student_detail.lastname + '</p>';
                    student_html += '<p><strong><?php echo $this->lang->line('admission_no'); ?>:</strong> ' + student_detail.admission_no + '</p>';
                    $('#student_details_modal').html(student_html);

                    // Populate outstanding assignments
                    var assignments_html = '';
                    if (outstanding_assignments.length > 0) {
                        assignments_html += '<table class="table table-bordered table-striped">';
                        assignments_html += '<thead><tr><th><?php echo $this->lang->line('fee_type'); ?></th><th><?php echo $this->lang->line('amount_due'); ?></th><th><?php echo $this->lang->line('due_date'); ?></th><th><?php echo $this->lang->line('select'); ?></th></tr></thead>';
                        assignments_html += '<tbody>';
                        $.each(outstanding_assignments, function (key, assignment) {
                            assignments_html += '<tr>';
                            assignments_html += '<td>' + assignment.fee_type_title + '</td>';
                            assignments_html += '<td>' + assignment.amount_due + '</td>\r\n                            <td>' + (assignment.due_date ? assignment.due_date : 'N/A') + '</td>';
                            assignments_html += '<td><input type="checkbox" name="selected_assignments[]" class="select_assignment_checkbox" data-assignment_id="' + assignment.id + '" data-fee_type_id="' + assignment.incidental_fee_type_id + '" data-amount_due="' + assignment.amount_due + '"></td>';
                            assignments_html += '</tr>';
                        });
                        assignments_html += '</tbody></table>';
                    } else {
                        assignments_html += '<p><?php echo $this->lang->line('no_outstanding_assignments'); ?></p>';
                    }
                    $('#outstanding_assignments_list').html(assignments_html);

                    $('#feeCollectionModal').modal('show');
                }
            });
        });



        // Handle form submission
        $('#form_add_incidental_fee').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();

            $.ajax({
                url: url,
                type: "POST",
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        successMsg(response.message);
                        $('#feeCollectionModal').modal('hide');
                        window.open(baseurl + 'admin/collect_incidental_fee/receipt/' + response.collection_id, '_blank');
                    } else {
                        errorMsg(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    errorMsg("An error occurred: " + error);
                }
            });
        });

        $(document).on('change', '.select_assignment_checkbox', function () {
            if ($(this).is(':checked')) {
                // Uncheck all other checkboxes
                $('.select_assignment_checkbox').not(this).prop('checked', false);

                var assignment_id = $(this).data('assignment_id');
                var fee_type_id = $(this).data('fee_type_id');
                var amount_due = $(this).data('amount_due');

                $('#modal_incidental_fee_assignment_id').val(assignment_id);
                $('#fee_type_id_modal').val(fee_type_id);

                if (amount_due > 0) {
                    $('#amount_collected').val(amount_due);
                } else {
                    $('#amount_collected').val('');
                    $('#amount_collected').attr('placeholder', 'Enter Amount');
                }
            } else {
                // If unchecked, clear the fields
                $('#modal_incidental_fee_assignment_id').val('');
                $('#fee_type_id_modal').val('');
                $('#amount_collected').val('');
                $('#amount_collected').attr('placeholder', '');
            }
        });

        $('#fee_type_id_modal').on('change', function() {
            var selected_fee_type_id = $(this).val();

            // Uncheck all assignment checkboxes first
            $('.select_assignment_checkbox').prop('checked', false);
            $('#modal_incidental_fee_assignment_id').val('');
            $('#amount_collected').val('');
            $('#amount_collected').attr('placeholder', '');

            if (selected_fee_type_id) {
                // Find the checkbox that corresponds to the selected fee type
                var matching_checkbox = $('.select_assignment_checkbox[data-fee_type_id="' + selected_fee_type_id + '"]');

                if (matching_checkbox.length > 0) {
                    // If a matching checkbox is found, check it and trigger its change event
                    matching_checkbox.prop('checked', true).trigger('change');
                }
            }
        });
        // Class Wise Modal - Class change event
        $('#class_id_cw').on('change', function () {
            var class_id = $(this).val();
            $('#section_id_cw').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            if (class_id) {
                $.ajax({
                    url: baseurl + 'admin/collect_incidental_fee/getSectionsByClass',
                    type: "POST",
                    data: {class_id: class_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (key, value) {
                            $('#section_id_cw').append('<option value="' + value.id + '">' + value.section + '</option>');
                        });
                    }
                });
            }
        });

        // Class Wise Modal - Search Students button click
        $('#search_students_cw_btn').on('click', function () {
            var class_id = $('#class_id_cw').val();
            var section_id = $('#section_id_cw').val();
            var incidental_fee_type_id = $('#incidental_fee_type_id_cw').val();

            // Clear previous errors
            $('.text-danger').html('');

            if (!class_id) {
                $('#class_id_cw_error').html('<?php echo $this->lang->line('the_class_field_is_required'); ?>');
                return;
            }
            if (!section_id) {
                $('#section_id_cw_error').html('<?php echo $this->lang->line('the_section_field_is_required'); ?>');
                return;
            }
            if (!incidental_fee_type_id) {
                $('#incidental_fee_type_id_cw_error').html('<?php echo $this->lang->line('the_incidental_fee_type_field_is_required'); ?>');
                return;
            }

            $.ajax({
                url: baseurl + 'admin/collect_incidental_fee/getStudentsForClassWiseCollection',
                type: "POST",
                data: {
                    class_id: class_id,
                    section_id: section_id,
                    incidental_fee_type_id: incidental_fee_type_id,
                    '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                },
                dataType: "json",
                beforeSend: function() {
                    $('#class_wise_student_list_container').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
                    $('#save_class_wise_fees_btn').hide();
                },
                success: function (response) {
                    if (response.status === 'success') {
                        var student_table_html = '<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="class_wise_student_datatable">';
                        student_table_html += '<thead><tr><th><?php echo $this->lang->line('admission_no'); ?></th><th><?php echo $this->lang->line('student_name'); ?></th><th><?php echo $this->lang->line('amount'); ?></th></tr></thead>';
                        student_table_html += '<tbody>';
                        if (response.students.length > 0) {
                            $.each(response.students, function (index, student) {
                                var amount_value = student.amount_collected ? student.amount_collected : '';
                                student_table_html += '<tr>';
                                student_table_html += '<td>' + student.admission_no + '</td>';
                                student_table_html += '<td>' + student.firstname + ' ' + student.lastname + '</td>';
                                student_table_html += '<td><input type="number" step="0.01" name="amounts[' + student.student_session_id + ']" class="form-control" value="' + amount_value + '"></td>';
                                student_table_html += '</tr>';
                            });
                            $('#save_class_wise_fees_btn').show();
                        } else {
                            student_table_html += '<tr><td colspan="3" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>';
                            $('#save_class_wise_fees_btn').hide();
                        }
                        student_table_html += '</tbody></table></div>';
                        $('#class_wise_student_list_container').html(student_table_html);
                        $('#class_wise_student_datatable').DataTable({
                            "destroy": true,
                            "ordering": false,
                            "paging": false,
                            "info": false
                        });
                    } else {
                        errorMsg(response.message);
                        $('#class_wise_student_list_container').html('');
                        $('#save_class_wise_fees_btn').hide();
                    }
                },
                error: function (xhr, status, error) {
                    errorMsg("An error occurred: " + error);
                    $('#class_wise_student_list_container').html('');
                    $('#save_class_wise_fees_btn').hide();
                }
            });
        });

        // Class Wise Modal - Form submission
        $('#form_class_wise_incidental_fee').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = baseurl + 'admin/collect_incidental_fee/saveClassWiseIncidentalFees';
            var data = form.serialize();

            $.ajax({
                url: url,
                type: "POST",
                data: data,
                dataType: 'json',
                beforeSend: function() {
                    $('#save_class_wise_fees_btn').button('loading');
                },
                success: function (response) {
                    if (response.status === 'success') {
                        successMsg(response.message);
                        $('#classWiseCollectionModal').modal('hide');
                        // Optionally, refresh the main page or a specific section
                        // window.location.reload();
                    } else {
                        errorMsg(response.message);
                    }
                    $('#save_class_wise_fees_btn').button('reset');
                },
                error: function (xhr, status, error) {
                    errorMsg("An error occurred: " + error);
                    $('#save_class_wise_fees_btn').button('reset');
                }
            });
        });
    });

    // Non-Student Fee Collection Modal - Form submission
    $('#form_add_non_student_incidental_fee').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        var url = baseurl + 'admin/collect_incidental_fee/collectNonStudentFee';
        var data = form.serialize();

        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function() {
                // You might want to show a loading indicator here
            },
            success: function (response) {
                if (response.status === 'success') {
                    successMsg(response.message);
                    $('#nonStudentFeeCollectionModal').modal('hide');
                    window.open(baseurl + 'admin/collect_incidental_fee/receipt/' + response.collection_id, '_blank');
                } else {
                    errorMsg(response.message);
                }
            },
            error: function (xhr, status, error) {
                errorMsg("An error occurred: " + error);
            },
            complete: function() {
                // You might want to hide a loading indicator here
            }
        });
    });
    // Non-Student Fee Collection Modal - Form submission
    $('#form_add_non_student_incidental_fee').on('submit', function (e) {
        e.preventDefault();
        var form = $(this);
        var url = baseurl + 'admin/collect_incidental_fee/collectNonStudentFee';
        var data = form.serialize();

        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: 'json',
            beforeSend: function() {
                // You might want to show a loading indicator here
            },
            success: function (response) {
                if (response.status === 'success') {
                    successMsg(response.message);
                    $('#nonStudentFeeCollectionModal').modal('hide');
                    window.open(baseurl + 'admin/collect_incidental_fee/receipt/' + response.collection_id, '_blank');
                } else {
                    errorMsg(response.message);
                }
            },
            error: function (xhr, status, error) {
                errorMsg("An error occurred: " + error);
            },
            complete: function() {
                // You might want to hide a loading indicator here
            }
        });
    });
</script>


