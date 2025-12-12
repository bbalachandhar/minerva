<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/dropify/dist/css/dropify.min.css">
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar"></i> <?php echo $this->lang->line('academics'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_holidays'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>backend/import/import_holiday_sample_file.csv" class="btn btn-info btn-sm" download="import_holiday_sample_file.csv"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_file'); ?></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                <h4><?php echo $this->lang->line('bulk_holiday_instruction_header'); ?></h4>
                                <p><b><?php echo $this->lang->line('bulk_holiday_csv_columns_instruction'); ?></b></p>
                                <p><b><?php echo $this->lang->line('bulk_holiday_type_note'); ?></b></p>
                                <p><b><?php echo $this->lang->line('bulk_holiday_date_format_instruction'); ?></b></p>
                                <p><b><?php echo $this->lang->line('bulk_holiday_check_exists_note'); ?></b></p>
                            </div>
                        </div>
                    </div>
                    <form action="<?php echo site_url('admin/bulkholliday/bulk_upload') ?>" method="post" enctype="multipart/form-data">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="default_holiday_type_id"><?php echo $this->lang->line('default_holiday_type'); ?></label><small class="req"> *</small>
                                        <select class="form-control" name="default_holiday_type_id" id="default_holiday_type_id">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($holiday_types as $type) { ?>
                                                <option value="<?php echo $type['id']; ?>" <?php echo set_select('default_holiday_type_id', $type['id']); ?>><?php echo $type['type']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('default_holiday_type_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="file"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                        <input type="file" name="file" id="file" class="dropify" autocomplete="off">
                                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('upload'); ?></button>
                        </div>
                    </form>
                    <?php if ($this->session->flashdata('msg')) { ?>
                        <?php echo $this->session->flashdata('msg') ?>
                    <?php } ?>

                    <?php if (isset($error_messages) && !empty($error_messages)) { ?>
                        <div class="alert alert-danger">
                            <h4><?php echo $this->lang->line('errors_encountered'); ?>:</h4>
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