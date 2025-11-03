<?php $this->load->view('layout/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> <small><?php echo $this->lang->line('assign_incidental_fee'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('assign_incidental_fee'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('admin/assign_incidental_fee/index') ?>" id="assign_incidental_fee_form" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                            <?php echo $this->customlib->get = $this->customlib->getCSRF(); ?>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fee_type_id"><?php echo $this->lang->line('fee_type'); ?></label>
                                        <select autofocus="" id="fee_type_id" name="fee_type_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($fee_types as $fee_type) { ?>
                                                <?php if ($fee_type['is_assignable'] == 1) { // Only show assignable fee types ?>
                                                    <option value="<?php echo $fee_type['id'] ?>" <?php echo set_select('fee_type_id', $fee_type['id']); ?>><?php echo $fee_type['title'] ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('fee_type_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="amount_due"><?php echo $this->lang->line('amount_due'); ?></label>
                                        <input id="amount_due" name="amount_due" type="text" class="form-control" value="<?php echo set_value('amount_due'); ?>" />
                                        <span class="text-danger"><?php echo form_error('amount_due'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="due_date"><?php echo $this->lang->line('due_date'); ?></label>
                                        <input id="due_date" name="due_date" type="text" class="form-control date" value="<?php echo set_value('due_date'); ?>" />
                                        <span class="text-danger"><?php echo form_error('due_date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id"><?php echo $this->lang->line('class'); ?></label>
                                        <select id="class_id" name="class_id[]" class="form-control select2" multiple="multiple" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($classes as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php echo set_select('class_id[]', $class['id']); ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id[]'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="student_id"><?php echo $this->lang->line('student'); ?></label>
                                        <select id="student_id" name="student_id[]" class="form-control select2" multiple="multiple" >
                                            <!-- Options will be loaded via AJAX based on class selection -->
                                        </select>
                                        <span class="text-danger"><?php echo form_error('student_id[]'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('assign_fee'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // Initialize Select2 for multi-select dropdowns
        $('.select2').select2();
        // Initialize datepicker
        $('.date').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true
        });

        // AJAX to load students based on selected class(es) and session
        $('#class_id, #session_id').on('change', function () {
            var class_ids = $('#class_id').val();
            var session_id = $('#session_id').val();
            $('#student_id').html(''); // Clear previous students

            if (class_ids && session_id) {
                $.ajax({
                    type: "POST",
                    url: baseurl + "admin/assign_incidental_fee/getStudentsByClass", // Assuming baseurl is defined globally
                    data: {class_id: class_ids, session_id: session_id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                    dataType: "json",
                    success: function (data) {
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

<?php $this->load->view('layout/footer'); ?>