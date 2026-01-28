<?php $currency_symbol = $this->customlib->getCurrency(); ?>
<div class="content-wrapper" style="min-height: 100px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-sitemap"></i> <?php echo $this->lang->line('online_admission'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_course'); ?></h3>
                    </div><!-- /.box-header -->
                    <form action="<?php echo site_url("admin/onlineadmission/admissioncourses/edit/" . $course_data['id']) ?>" id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <?php echo $this->customlib->form_error(); ?>
                            <input type="hidden" name="id" value="<?php echo $course_data['id']; ?>">
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('course_name'); ?></label><small class="req"> *</small>
                                <input autofocus="" id="course_name" name="course_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('course_name', $course_data['course_name']); ?>" />
                                <span class="text-danger"><?php echo form_error('course_name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('course_code'); ?></label>
                                <input id="course_code" name="course_code" placeholder="" type="text" class="form-control" value="<?php echo set_value('course_code', $course_data['course_code']); ?>" />
                                <span class="text-danger"><?php echo form_error('course_code'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $course_data['description']); ?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('status'); ?></label><small class="req"> *</small>
                                <select class="form-control" name="is_active">
                                    <option value="1" <?php echo set_select('is_active', '1', ($course_data['is_active'] == 1) ? true : false); ?>><?php echo $this->lang->line('active'); ?></option>
                                    <option value="0" <?php echo set_select('is_active', '0', ($course_data['is_active'] == 0) ? true : false); ?>><?php echo $this->lang->line('inactive'); ?></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('is_active'); ?></span>
                            </div>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('update'); ?></button>
                            <a href="<?php echo site_url('admin/onlineadmission/admissioncourses'); ?>" class="btn btn-default pull-right" style="margin-right: 10px;"><?php echo $this->lang->line('cancel'); ?></a>
                        </div>
                    </form>
                </div>
            </div><!--/.col (right) -->
        </div>
    </section><!-- /.content -->
</div>
