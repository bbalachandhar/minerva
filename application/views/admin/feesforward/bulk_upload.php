<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/dropify/dist/css/dropify.min.css">
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
                        <h3 class="box-title"><i class="fa fa-upload"></i> Bulk Upload Fees Forward</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>backend/import/import_feesforward_sample_file.csv" class="btn btn-info btn-sm" download="import_feesforward_sample_file.csv"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_file'); ?></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                <h4><?php echo $this->lang->line('instruction'); ?>:</h4>
                                <p><b>1. The CSV file should have two columns: `admission_no` and `amount`.</b></p>
                                <p><b>2. Please do not change the heading of the sample file.</b></p>
                                <p><b>3. The system will forward the balance fees for students found with the given admission number in the previous session.</b></p>
                            </div>
                        </div>
                    </div>
                    <form action="<?php echo site_url('admin/feesforward/bulk_upload') ?>" method="post" enctype="multipart/form-data">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="file"><?php echo $this->lang->line('select_csv_file'); ?></label>
                                        <input type="file" name="file" id="file" class="dropify" autocomplete="off">
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('upload'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php if ($this->session->flashdata('msg')) { ?>
                        <?php echo $this->session->flashdata('msg') ?>
                    <?php } ?>

                    <?php if (isset($error_messages) && !empty($error_messages)) { ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($error_messages as $error) { ?>
                                    <li><?php echo $error; ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="<?php echo base_url(); ?>backend/plugins/dropify/dist/js/dropify.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.dropify').dropify();
    });
</script>