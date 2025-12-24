<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/dropify/dist/css/dropify.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/dropify/dist/js/dropify.min.js"></script>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('bulk_upload_leave_allotment'); ?></h3>
                        <div class="box-tools pull-right">
                             <a href="<?php echo site_url('admin/leavetypes/download_sample'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_csv'); ?>
                            </a>
                            <a href="<?php echo site_url('admin/leavetypes'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?>
                            </a>
                        </div>
                    </div>
                    <form id="form1" action="<?php echo site_url('admin/leavetypes/handle_bulk_upload') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8"  enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) {?>
                                <?php echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');?>
                            <?php }?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label><small class="req"> *</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="file" name="file" id="file" class="dropify" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-info pull-left"><?php echo $this->lang->line('upload'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="well">
                                        <h4><?php echo $this->lang->line('instructions'); ?></h4>
                                        <p><?php echo $this->lang->line('the_csv_file_should_be_in_the_following_format'); ?>:</p>
                                        <p><code>staff_id,leave_type_id,days</code></p>
                                        <p><?php echo $this->lang->line('ensure_the_staff_id_and_leave_type_id_are_correct'); ?></p>
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
$(document).ready(function (e) {
    $('.dropify').dropify();
});
</script>