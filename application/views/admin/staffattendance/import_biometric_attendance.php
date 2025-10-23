<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-upload"></i> <?php echo $this->lang->line('import_biometric_attendance'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('upload_biometric_attendance_file'); ?></h3>
                    </div>
                    <?php if ($this->session->flashdata('msg')) { ?>
                        <?php echo $this->session->flashdata('msg'); ?>
                    <?php } ?>
                    <form action="<?php echo site_url('admin/staffattendance/import_biometric_attendance') ?>" id="import_form" class="form-horizontal" method="post" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="exampleInputFile" class="col-sm-2 control-label">Select File</label>
                                <div class="col-sm-4">
                                    <input class="filestyle form-control" type='file' name='file' id="file" size='20' />
                                    <span class="text-danger"><?php echo form_error('file'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>