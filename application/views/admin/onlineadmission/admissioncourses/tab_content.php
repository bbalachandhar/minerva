<?php $currency_symbol = $this->customlib->getCurrency(); ?>
<div class="row">
    <?php if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add')) { ?>
        <div class="col-md-4">
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo $this->lang->line('add_course'); ?></h3>
                </div><!-- /.box-header -->
                <form action="<?php echo site_url('admin/admissioncourses/add'); ?>" id="courseform" name="courseform" method="post" accept-charset="utf-8">
                    <input type="hidden" name="active_tab" id="active_tab_course" value="tab_3">
                    <div class="box-body">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <?php echo $this->session->flashdata('msg') ?>
                        <?php } ?>
                        <?php if ($this->session->flashdata('error')) { ?>
                            <?php echo $this->session->flashdata('error') ?>
                        <?php } ?>
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('course_name'); ?></label><small class="req"> *</small>
                            <input autofocus="" id="course_name" name="course_name" placeholder="" type="text" class="form-control" value="<?php echo set_value('course_name'); ?>" />
                            <span class="text-danger"><?php echo form_error('course_name'); ?></span>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('course_code'); ?></label>
                            <input id="course_code" name="course_code" placeholder="" type="text" class="form-control" value="<?php echo set_value('course_code'); ?>" />
                            <span class="text-danger"><?php echo form_error('course_code'); ?></span>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description'); ?></textarea>
                            <span class="text-danger"><?php echo form_error('description'); ?></span>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('status'); ?></label><small class="req"> *</small>
                            <select class="form-control" name="is_active">
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
    if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add')) {
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
                <div class="download_label"><?php echo $this->lang->line('course_list'); ?></div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="admission-courses-table">
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
                                    <td colspan="5" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                </tr>
                                <?php
                            } else {
                                foreach ($course_list as $course) {
                                    ?>
                                    <tr>
                                        <td class="mailbox-name"><?php echo $course['course_name'] ?></td>
                                        <td class="mailbox-name"><?php echo $course['course_code'] ?></td>
                                        <td class="mailbox-name"><?php echo $course['description'] ?></td>
                                        <td class="mailbox-name"><?php echo ($course['is_active'] == 1) ? $this->lang->line('active') : $this->lang->line('inactive'); ?></td>
                                        <td class="mailbox-date text-right">
                                            <?php if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) { ?>
                                                <a data-placement="left" href="<?php echo base_url(); ?>admin/admissioncourses/edit/<?php echo $course['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            <?php } if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_delete')) { ?>
                                                <a data-placement="left" href="<?php echo base_url(); ?>admin/admissioncourses/delete/<?php echo $course['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
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
                    </table>
                </div>
            </div><!-- /.box-body -->
        </div>
    </div><!--/.col (left) -->
    <!-- right column -->
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable for admission courses when tab 3 is shown
        $('a[href="#tab_3"]').on('shown.bs.tab', function (e) {
            if (!$.fn.DataTable.isDataTable('#admission-courses-table')) {
                $('#admission-courses-table').DataTable({
                    "pageLength": 10,
                    "order": [[0, "desc"]],
                    "columnDefs": [
                        { "orderable": false, "targets": [-1] }
                    ]
                });
            }
        });
        
        // If tab 3 is already active on page load
        if ($('#tab_3').hasClass('active')) {
            if (!$.fn.DataTable.isDataTable('#admission-courses-table')) {
                $('#admission-courses-table').DataTable({
                    "pageLength": 10,
                    "order": [[0, "desc"]],
                    "columnDefs": [
                        { "orderable": false, "targets": [-1] }
                    ]
                });
            }
        }
    });
</script>
