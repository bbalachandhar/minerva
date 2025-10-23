<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('sync_biometric_attendance'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('sync_biometric_attendance'); ?></h3>
                    </div>
                    <?php if ($this->session->flashdata('msg')) { ?>
                        <?php echo $this->session->flashdata('msg'); ?>
                    <?php } ?>
                    <form action="<?php echo site_url('admin/staffattendance/sync_biometric_attendance') ?>" id="sync_form" class="form-horizontal" method="post">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="last_sync_datetime" class="col-sm-4 control-label">Last Sync Datetime</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php echo ($last_sync_datetime) ? date($this->customlib->get  SchoolDateFormat(true,true), strtotime($last_sync_datetime)) : 'Never'; ?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="full_day_present_threshold" class="col-sm-4 control-label"><?php echo $this->lang->line('full_day_present_threshold'); ?> <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="number" step="0.01" class="form-control" name="full_day_present_threshold" placeholder="e.g., 8.00 for 8 hours" required="" value="<?php echo set_value('full_day_present_threshold', (isset($full_day_present_threshold)) ? $full_day_present_threshold : '8.00'); ?>">
                                    <span class="text-danger"><?php echo form_error('full_day_present_threshold'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> <?php echo $this->lang->line('sync_biometric_attendance'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>