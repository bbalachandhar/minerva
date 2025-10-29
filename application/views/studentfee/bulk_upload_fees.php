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
                            <p><b><?php echo $this->lang->line('example'); ?>:</b></p>
                            <pre>
admission_no,total_amount_paid,old_bill_number,old_bill_date,payment_mode,description
12345,1000,BILL123,2023-10-27,Cash,Monthly Fee Payment
                            </pre>
                        </div>
                        <?php echo form_open_multipart('studentfee/bulk_upload_fees', array('id' => 'bulk_upload_form')); ?>
                        <div class="row">
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
                                </script>                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right"><?php echo $this->lang->line('upload'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
