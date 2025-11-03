<?php $this->load->view('layout/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php echo $this->lang->line('finance_report'); ?><small><?php echo $this->lang->line('incidental_fee_report'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('financereports/incidental_fee_report') ?>" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                            <?php echo $this->customlib->get = $this->customlib->getCSRF(); ?>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search_type"><?php echo $this->lang->line('search_duration'); ?></label>
                                        <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($searchlist as $key => $search) { ?>
                                                <option value="<?php echo $key ?>" <?php if ((isset($search_type)) && ($search_type == $key)) { echo "selected"; } ?>><?php echo $search ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="session_id"><?php echo $this->lang->line('session'); ?></label>
                                        <select id="session_id" name="session_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($sessions as $session) { ?>
                                                <option value="<?php echo $session['id'] ?>" <?php echo set_select('session_id', $session['id']); ?>><?php echo $session['session'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('session_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fee_type_id"><?php echo $this->lang->line('fee_type'); ?></label>
                                        <select id="fee_type_id" name="fee_type_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($fee_types as $fee_type) { ?>
                                                <option value="<?php echo $fee_type['id'] ?>" <?php echo set_select('fee_type_id', $fee_type['id']); ?>><?php echo $fee_type['title'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('fee_type_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('class'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($classes as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php echo set_select('class_id', $class['id']); ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="student_id"><?php echo $this->lang->line('student'); ?></label>
                                        <select id="student_id" name="student_id" class="form-control select2" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <!-- Students will be loaded via AJAX based on class selection -->
                                        </select>
                                        <span class="text-danger"><?php echo form_error('student_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_from"><?php echo $this->lang->line('date_from'); ?></label>
                                        <input id="date_from" name="date_from" type="text" class="form-control date" value="<?php echo set_value('date_from'); ?>" />
                                        <span class="text-danger"><?php echo form_error('date_from'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_to"><?php echo $this->lang->line('date_to'); ?></label>
                                        <input id="date_to" name="date_to" type="text" class="form-control date" value="<?php echo set_value('date_to'); ?>" />
                                        <span class="text-danger"><?php echo form_error('date_to'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($collections)) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('incidental_fee_collections'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-striped table-bordered table-hover incidental-report-table">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('incidental_report_receipt_no'); ?></th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th><?php echo $this->lang->line('session'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('class'); ?></th>
                                        <th><?php echo $this->lang->line('fee_type'); ?></th>
                                        <th><?php echo $this->lang->line('incidental_report_amount_collected'); ?></th>
                                            <th><?php echo $this->lang->line('collected_by'); ?></th>
                                            <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($collections as $collection) { ?>
                                            <tr>
                                                <td><?php echo $collection['receipt_no']; ?></td>
                                                <td><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($collection['date_collected'])); ?></td>
                                                <td><?php echo $collection['session_name']; ?></td>
                                                <td><?php echo $this->customlib->getFullName($collection['firstname'],'',$collection['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></td>
                                                <td><?php echo $collection['admission_no']; ?></td>
                                                <td><?php echo $collection['class_name'] . ' (' . $collection['section'] . ')'; ?></td>
                                                <td><?php echo $collection['fee_type_title']; ?></td>
                                                <td><?php echo $collection['amount_collected']; ?></td>
                                                <td><?php echo $collection['collected_by_name']; ?></td>
                                                <td class="text-right">
                                                    <a href="<?php echo site_url('admin/collect_incidental_fee/revert/' . $collection['id']); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to revert this fee collection? This action cannot be undone.');" data-toggle="tooltip" title="<?php echo $this->lang->line('revert'); ?>">
                                                        <i class="fa fa-undo"></i> <?php echo $this->lang->line('revert'); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-right"><?php echo $this->lang->line('total_amount_collected'); ?>:</th>
                                        <th><?php echo $total_amount_collected; ?></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Initialize Select2 for student dropdown
        $('.select2').select2();
        // Initialize datepicker
        $('.date').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true
        });

        // Function to show/hide date range inputs based on search type
        function showdate(value) {
            if (value == 'period') {
                $('#date_from').parent().show();
                $('#date_to').parent().show();
            } else {
                $('#date_from').parent().hide();
                $('#date_to').parent().hide();
            }
        }

        // Initial call to show/hide dates based on pre-selected search type
        showdate($('select[name="search_type"]').val());

        // AJAX to load students based on selected class and session
        $('#class_id, #session_id').on('change', function () {
            var class_id = $('#class_id').val();
            var session_id = $('#session_id').val();
            $('#student_id').html(''); // Clear previous students

            if (class_id && session_id) {
                $.ajax({
                    type: "POST",
                    url: baseurl + "admin/assign_incidental_fee/getStudentsByClass", // Reusing this AJAX endpoint
                    data: {class_id: class_id, session_id: session_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                    dataType: "json",
                    success: function (data) {
                        $('#student_id').append('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                        $.each(data, function (key, value) {
                            $('#student_id').append('<option value="' + value.id + '">' + value.firstname + ' ' + value.lastname + ' (' + value.admission_no + ')</option>');
                        });
                        $('#student_id').select2(); // Re-initialize Select2 after adding options
                    }
                });
            }
        });
    });
</script>


<script>
$(document).ready(function() {
    $('.incidental-report-table').DataTable({
        "destroy": true // Add this to allow re-initialization
    });
});
</script>