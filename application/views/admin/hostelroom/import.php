<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-building-o"></i> <?php echo $this->lang->line('hostel'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('import_hostel_room'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" id="commentform" class="comment-form" method="post" action="<?php echo site_url('admin/hostelroom/import') ?>" enctype="multipart/form-data">
                            <div class="box-body">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('upload_csv_file'); ?></label><small class="req"> *</small>
                                            <input class="form-control" type="file" name="file" id="file" size="20" />
                                            <span class="text-danger"><?php echo form_error('file'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pt20">
                                        <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('import_hostel_room'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <hr>
                        <p><b><?php echo $this->lang->line('instructions'); ?>:</b></p>
                        <ul>
                            <li><?php echo $this->lang->line('hostel_room_import_note_1'); ?></li>
                            <li><?php echo $this->lang->line('hostel_room_import_note_2'); ?></li>
                        </ul>
                        <a href="<?php echo base_url(); ?>backend/import/hostel_room_sample.csv" class="btn btn-success btn-sm"><i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_data'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
