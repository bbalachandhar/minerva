<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_fees'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>backend/import/import_feemaster_sample_file.csv" class="btn btn-info btn-sm" download="import_feemaster_sample_file.csv"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_file'); ?></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                <h4><?php echo $this->lang->line('instruction'); ?>:</h4>
                                <p><b>1. <?php echo $this->lang->line('the_system_will_check_the_file_for_duplicate_records_on_the_basis_of_student_admission_no_and_fee_type'); ?>.</b></p>
                                <p><b>2. <?php echo $this->lang->line('please_do_not_change_the_heading_of_the_sample_file'); ?>.</b></p>
                                <p><b>3. <?php echo $this->lang->line('the_correct_date_format_is_yyyy_mm_dd'); ?>.</b></p>
                                <p><b>4. <?php echo $this->lang->line('if_payment_mode_is_cash_then_no_transaction_id_is_required'); ?>.</b></p>
                                <p><b>5. <?php echo $this->lang->line('if_payment_mode_is_cheque_or_dd_then_transaction_id_is_required'); ?>.</b></p>
                            </div>
                        </div>
                    </div>
                    <form action="<?php echo site_url('studentfee/bulk_upload_fees') ?>" method="post" enctype="multipart/form-data">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <div><input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                            <span class="text-danger"><?php echo form_error('file'); ?></span></div>
                                    </div>
                                <div class="col-md-6 pt20">
                                    <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('upload'); ?></button>
                                </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
$(document).ready(function() {
    // Explicitly initialize Dropify
    $('#file').dropify();

    // Workaround: Manually trigger click on native file input when Dropify overlay is clicked
    $('.dropify-wrapper').on('click', function() {
        $('#file').trigger('click');
    });
});
</script>