<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('online_admission'); ?> <small><?php echo $this->lang->line('admission_courses'); ?></small>        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add') || $this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) { ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('add_course'); ?></h3>
                        </div><!-- /.box-header -->
                        <form action="<?php echo site_url('admin/onlineadmission/admissioncourses') ?>" method="post" id="courseform">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg') ?>
                                    <?php $this->session->unset_userdata('msg');
                                } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <input type="hidden" name="id" id="course_id" value="0">
                                <div class="form-group">
                                    <label for="course_name"><?php echo $this->lang->line('course_name'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="course_name" name="course_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('course_name'); ?>" />
                                    <span class="text-danger"><?php echo form_error('course_name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="course_code"><?php echo $this->lang->line('course_code'); ?></label><small class="req"> *</small>
                                    <input id="course_code" name="course_code" placeholder="" type="text" class="form-control" value="<?php echo set_value('course_code'); ?>" />
                                    <span class="text-danger"><?php echo form_error('course_code'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder=""><?php echo set_value('description'); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('description'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="is_active"><?php echo $this->lang->line('status'); ?></label><small class="req"> *</small>
                                    <select class="form-control" name="is_active" id="is_active">
                                        <option value="1" <?php echo set_select('is_active', '1', true); ?>><?php echo $this->lang->line('active'); ?></option>
                                        <option value="0" <?php echo set_select('is_active', '0'); ?>><?php echo $this->lang->line('inactive'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('is_active'); ?></span>
                                </div>
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div><!--/.col (right) -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add') || $this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('course_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="mailbox-messages table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('course_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('course_name'); ?></th>
                                        <th><?php echo $this->lang->line('course_code'); ?></th>
                                        <th><?php echo $this->lang->line('description'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($course_list)) {
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($course_list as $course) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $course['course_name'] ?></td>
                                                <td class="mailbox-name"><?php echo $course['course_code'] ?></td>
                                                <td class="mailbox-name"><?php echo $course['description'] ?></td>
                                                <td class="mailbox-name">
                                                    <?php
                                                    if ($course['is_active'] == 1) {
                                                        echo '<span class="label label-success">' . $this->lang->line('active') . '</span>';
                                                    } else {
                                                        echo '<span class="label label-danger">' . $this->lang->line('inactive') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="mailbox-date text-right">
                                                    <?php if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) { ?>
                                                        <a href="#" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>" onclick="editCourse('<?php echo $course['id']; ?>', '<?php echo $course['course_name']; ?>', '<?php echo $course['course_code']; ?>', '<?php echo html_escape($course['description']); ?>', '<?php echo $course['is_active']; ?>')">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_delete')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/onlineadmission/deletecourse/<?php echo $course['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>')">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (right) -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    function editCourse(id, course_name, course_code, description, is_active) {
        $('#course_id').val(id);
        $('#course_name').val(course_name);
        $('#course_code').val(course_code);
        $('#description').val(description);
        $('#is_active').val(is_active);
        // Change form title
        $('.box-title').text('<?php echo $this->lang->line('edit_course'); ?>');
        $('html, body').animate({
            scrollTop: $('h3.box-title').offset().top
        }, 500);
    }
</script>
