<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_fees'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('studentfee/exportfeesformat'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_file'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($summary)): ?>
                            <div class="alert alert-info">
                                <h4>Upload Summary</h4>
                                <p>Total Records: <?php echo $summary['total_records']; ?></p>
                                <p>Successful Records: <?php echo $summary['successful_records']; ?></p>
                                <p>Failed Records: <?php echo $summary['failed_records']; ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                        <?php } ?>
                        <?php if (isset($error_messages) && !empty($error_messages)): ?>
                            <div class="alert alert-danger">
                                <h4><i class="icon fa fa-ban"></i> <?php echo $this->lang->line('errors_found_in_csv'); ?></h4>
                                <ul>
                                    <?php foreach ($error_messages as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <div class="well">
                            <h4><?php echo $this->lang->line('instruction'); ?></h4>
                            <p><?php echo $this->lang->line('bulk_upload_instructions_1'); ?></p>
                            <p><?php echo $this->lang->line('bulk_upload_instructions_2'); ?></p>
                            <p><b><?php echo $this->lang->line('required_columns'); ?>:</b> admission_no, total_amount_paid, old_bill_number, old_bill_date, payment_mode, description</p>
                            <p><b><?php echo $this->lang->line('note'); ?>:</b> <?php echo $this->lang->line('fee_will_be_uploaded_against_selected_fee_type'); ?></p>
                            <p><b><?php echo $this->lang->line('example'); ?>:</b></p>
                            <pre>
admission_no,total_amount_paid,old_bill_number,old_bill_date,payment_mode,description
12345,1000,BILL123,2023-10-27,Cash,Monthly Fee Payment
                            </pre>
                        </div>
                        <?php echo form_open_multipart('studentfee/do_bulk_upload_by_feetype', array('id' => 'bulk_upload_form')); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fee_type_id"><?php echo $this->lang->line('fee_type'); ?></label><small class="req"> *</small>
                                    <select autofocus="" id="fee_type_id" name="fee_type_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($feetype_list as $feetype) { ?>
                                            <option value="<?php echo $feetype['id'] ?>" <?php echo set_select('fee_type_id', $feetype['id']); ?>><?php echo $feetype['type'] . " (" . $feetype['code'] . ")"; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('fee_type_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file"><?php echo $this->lang->line('select_csv_file'); ?></label>
                                    <div class="input-group">
                                        <label class="input-group-btn">
                                            <span class="btn btn-primary">
                                                Browse <input type="file" name="file" id="file" style="display: none;">
                                            </span>
                                        </label>
                                        <input type="text" class="form-control" readonly>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('file'); ?></span>
                                </div>
                                
                                <script type="text/javascript">
                                    $(document).on('change', '#file', function() {
                                        var input = $(this),
                                            numFiles = input.get(0).files ? input.get(0).files.length : 1,
                                            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                                        input.trigger('fileselect', [numFiles, label]);
                                    });
                                
                                    $(document).on('fileselect', '#file', function(event, numFiles, label) {
                                        var input = $(this).parents('.input-group').find(':text'),
                                            log = numFiles > 1 ? numFiles + ' files selected' : label;
                                
                                        if (input.length) {
                                            input.val(log);
                                        } else {
                                                                                    if (log) alert(log);
                                                                                }
                                                                            });
                                                                        </script>
                                <script type="text/javascript">
                                    $(document).on('change', '#adjustment_file', function() {
                                        var input = $(this),
                                            numFiles = input.get(0).files ? input.get(0).files.length : 1,
                                            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                                        input.trigger('fileselect', [numFiles, label]);
                                    });
                                
                                    $(document).on('fileselect', '#adjustment_file', function(event, numFiles, label) {
                                        var input = $(this).parents('.input-group').find(':text'),
                                            log = numFiles > 1 ? numFiles + ' files selected' : label;
                                
                                        if (input.length) {
                                            input.val(log);
                                        } else {
                                            if (log) alert(log);
                                        }
                                    });
                                </script>
                                <script type="text/javascript">
                                    $(document).on('change', '#transport_file', function() {
                                        var input = $(this),
                                            numFiles = input.get(0).files ? input.get(0).files.length : 1,
                                            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                                        input.trigger('fileselect', [numFiles, label]);
                                    });
                                
                                    $(document).on('fileselect', '#transport_file', function(event, numFiles, label) {
                                        var input = $(this).parents('.input-group').find(':text'),
                                            log = numFiles > 1 ? numFiles + ' files selected' : label;
                                
                                        if (input.length) {
                                            input.val(log);
                                        } else {
                                            if (log) alert(log);
                                        }
                                    });
                                </script>
                                                                                                                                            </div>                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right" id="upload_btn"><?php echo $this->lang->line('upload'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> Bulk Carry forwarded Fee Adjustment</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('studentfee/exportadjustmentformat'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> Download Sample File</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="well">
                            <h4>Instructions</h4>
                            <p>Please follow the instructions for uploading the fee adjustments file.</p>
                            <p><b>Required Columns:</b> admission_no, amount, date, payment_mode, description</p>
                            <p><b>Note:</b> The amount will be added as a payment against the student's carry forwarded fees.</p>
                            <p><b>Example:</b></p>
                            <pre>
admission_no,amount,date,payment_mode,description
12345,500,2025-11-06,Cash,Adjustment for carry forward
                            </pre>
                        </div>
                        <?php echo form_open_multipart('studentfee/bulk_adjustment_upload', array('id' => 'bulk_adjustment_form')); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="adjustment_file">Select CSV File</label>
                                    <div class="input-group">
                                        <label class="input-group-btn">
                                            <span class="btn btn-primary">
                                                Browse <input type="file" name="adjustment_file" id="adjustment_file" style="display: none;">
                                            </span>
                                        </label>
                                        <input type="text" class="form-control" readonly>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('adjustment_file'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right" id="adjustment_upload_btn">Upload</button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bus"></i> <?php echo $this->lang->line('bulk_transport_fee_upload'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('studentfee/exporttransportfeesformat'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_file'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="well">
                            <h4><?php echo $this->lang->line('instruction'); ?></h4>
                            <p><?php echo $this->lang->line('bulk_transport_upload_instructions_1'); ?></p>
                            <p><b><?php echo $this->lang->line('required_columns'); ?>:</b> admission_no, amount, date, payment_mode, description</p>
                            <p><b><?php echo $this->lang->line('note'); ?>:</b> <?php echo $this->lang->line('amount_will_be_applied_to_student_transport_fees'); ?></p>
                            <p><b><?php echo $this->lang->line('example'); ?>:</b></p>
                            <pre>
admission_no,amount,date,payment_mode,description
12345,1500,2023-10-27,Cash,Yearly Transport Fee Payment
                            </pre>
                        </div>
                        <?php echo form_open_multipart('studentfee/do_bulk_upload_transport_fees', array('id' => 'bulk_transport_upload_form')); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transport_file"><?php echo $this->lang->line('select_csv_file'); ?></label>
                                    <div class="input-group">
                                        <label class="input-group-btn">
                                            <span class="btn btn-primary">
                                                Browse <input type="file" name="transport_file" id="transport_file" style="display: none;">
                                            </span>
                                        </label>
                                        <input type="text" class="form-control" readonly>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('transport_file'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right" id="transport_upload_btn"><?php echo $this->lang->line('upload'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-percent"></i> <?php echo $this->lang->line('apply_discounts'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php echo form_open('', array('id' => 'apply_discount_form')); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="discount_type_id"><?php echo $this->lang->line('discount_type'); ?></label><small class="req"> *</small>
                                    <select autofocus="" id="discount_type_id" name="discount_type_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($discount_list as $discount) { ?>
                                            <option value="<?php echo $discount['id'] ?>"><?php echo $discount['name'] . " (" . $discount['code'] . ")"; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('discount_type_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fee_type_to_adjust_id"><?php echo $this->lang->line('fee_type_to_adjust'); ?></label><small class="req"> *</small>
                                    <select autofocus="" id="fee_type_to_adjust_id" name="fee_type_to_adjust_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($feetype_list as $feetype) { ?>
                                            <option value="<?php echo $feetype['id'] ?>"><?php echo $feetype['type'] . " (" . $feetype['code'] . ")"; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('fee_type_to_adjust_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4" style="margin-top: 25px;">
                                <button type="submit" class="btn btn-primary" id="apply_discount_btn"><?php echo $this->lang->line('apply_discount'); ?></button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function (e) {
        $("#bulk_upload_form").on('submit', (function (e) {
            e.preventDefault();
            var $this = $(this);
            var $btn = $this.find("#upload_btn");
            $btn.button('loading');

            var formData = new FormData();
            formData.append('feetype_id', $('#fee_type_id').val());
            formData.append('file', $('#file')[0].files[0]);

            for (var pair of formData.entries()) {
                console.log(pair[0]+ ', ' + pair[1]); 
            }

            $.ajax({
                url: "<?php echo site_url('studentfee/do_bulk_upload_by_feetype') ?>",
                type: "POST",
                data: formData,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        if (data.summary) {
                            var summary_html = '<div class="alert alert-info"><h4>Upload Summary</h4><p>Total Records: ' + data.summary.total_records + '</p><p>Successful Records: ' + data.summary.successful_records + '</p><p>Failed Records: ' + data.summary.failed_records + '</p></div>';
                            $('.box-body').prepend(summary_html);
                        }
                        if (data.error_messages && data.error_messages.length > 0) {
                            var error_html = '<div class="alert alert-danger"><h4><i class="icon fa fa-ban"></i> ' + data.summary.failed_records + ' Errors Found In CSV</h4><ul>';
                            $.each(data.error_messages, function (index, value) {
                                error_html += '<li>' + value + '</li>';
                            });
                            error_html += '</ul></div>';
                            $('.box-body').prepend(error_html);
                        }
                    }
                    $btn.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("Error occured.please try again");
                    $btn.button('reset');
                }
            });
        }));

        $("#bulk_transport_upload_form").on('submit', (function (e) {
            e.preventDefault();
            var $this = $(this);
            var $btn = $this.find("#transport_upload_btn");
            $btn.button('loading');

            var formData = new FormData();
            formData.append('transport_file', $('#transport_file')[0].files[0]);

            $.ajax({
                url: "<?php echo site_url('studentfee/do_bulk_upload_transport_fees') ?>",
                type: "POST",
                data: formData,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        if (data.summary) {
                            var summary_html = '<div class="alert alert-info"><h4>Upload Summary</h4><p>Total Records: ' + data.summary.total_records + '</p><p>Successful Records: ' + data.summary.successful_records + '</p><p>Failed Records: ' + data.summary.failed_records + '</p></div>';
                            $('.box-body').prepend(summary_html);
                        }
                        if (data.error_messages && data.error_messages.length > 0) {
                            var error_html = '<div class="alert alert-danger"><h4><i class="icon fa fa-ban"></i> ' + data.summary.failed_records + ' Errors Found In CSV</h4><ul>';
                            $.each(data.error_messages, function (index, value) {
                                error_html += '<li>' + value + '</li>';
                            });
                            error_html += '</ul></div>';
                            $('.box-body').prepend(error_html);
                        }
                    }
                    $btn.button('reset');
                },
                error: function (xhr) { // if error occured
                    alert("Error occured.please try again");
                    $btn.button('reset');
                }
            });
        }));

        // New script for applying discount
        $("#apply_discount_form").on('submit', (function (e) {
            e.preventDefault();
            var $this = $(this);
            var $btn = $this.find("#apply_discount_btn");
            $btn.button('loading');

            var discount_id = $('#discount_type_id').val();
            var fee_type_to_adjust_id = $('#fee_type_to_adjust_id').val();

            $.ajax({
                url: "<?php echo site_url('studentfee/apply_discount') ?>",
                type: "POST",
                data: {
                    discount_id: discount_id,
                    fee_type_to_adjust_id: fee_type_to_adjust_id
                },
                dataType: 'json',
                success: function (data) {
                    if (data.status == "fail") {
                        errorMsg(data.message);
                    } else {
                        successMsg(data.message);
                    }
                    $btn.button('reset');
                },
                error: function (xhr) {
                    alert("Error occurred. Please try again.");
                    $btn.button('reset');
                }
            });
        }));
    });
</script>
