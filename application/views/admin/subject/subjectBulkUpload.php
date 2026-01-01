<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('student_fees1'); ?></small>        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_subjects'); ?></h3>
                        <div class="box-tools pull-right"></div>
                    </div>
                    <div class="box-body">
                        <?php echo validation_errors(); ?>
                        <?php echo $this->session->flashdata('msg'); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <p><?php echo $this->lang->line('bulk_upload_info'); ?></p>
                                    <ul>
                                        <li><?php echo $this->lang->line('bulk_upload_instruction_1'); ?></li>
                                        <li><?php echo $this->lang->line('bulk_upload_instruction_2'); ?></li>
                                        <li><?php echo $this->lang->line('bulk_upload_instruction_3'); ?></li>
                                        <li><?php echo $this->lang->line('bulk_upload_instruction_4_with_department'); ?></li>
                                        <li><?php echo $this->lang->line('bulk_upload_instruction_department_name'); ?></li>
                                        <?php if ($this->sch_setting_detail->institution_type == 'college') { ?>
                                        <li><?php echo $this->lang->line('bulk_upload_instruction_5'); ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="form-group">
                                    <a href="<?php echo base_url(); ?>uploads/sample_files/sample_subjects.csv" class="btn btn-default btn-sm">
                                        <i class="fa fa-download"></i> <?php echo $this->lang->line('download_sample_file'); ?>
                                    </a>
                                </div>
                                <?php echo form_open_multipart('admin/subject/bulk_upload'); ?>
                                    <div class="form-group">
                                        <label for="exampleInputFile"><?php echo $this->lang->line('select_csv_file'); ?></label>
                                        <input type="file" name="file" id="file" class="dropify form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-info"><?php echo $this->lang->line('upload'); ?></button>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.dropify').dropify();
    });
</script>