<?php $is_school_k12 = in_array(strtolower(trim($sch_setting_detail->institution_type)), ['school', 'school (k-12)']); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?> <small><?php echo $this->lang->line('student_fees'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_course'); ?></h3>
                    </div><!-- /.box-header -->
                    <form id="form1" action="<?php echo site_url('admin/admissioncourses/edit/' . $course['id']) ?>"  id="courseform" name="courseform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <?php
                            if (isset($error_message)) {
                                echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                            }
                            ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('course_name'); ?></label><small class="req"> *</small>
                                <input autofocus="" id="course_name" name="course_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('course_name', $course['course_name']); ?>" />
                                <span class="text-danger"><?php echo form_error('course_name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('course_code'); ?></label>
                                <input id="course_code" name="course_code" placeholder="" type="text" class="form-control"  value="<?php echo set_value('course_code', $course['course_code']); ?>" />
                                <span class="text-danger"><?php echo form_error('course_code'); ?></span>
                            </div>
                            <?php if ($is_school_k12): ?>
                                <input type="hidden" name="course_level" value="<?php echo htmlspecialchars($course['course_level']); ?>">
                                <input type="hidden" name="admission_type" value="<?php echo htmlspecialchars($course['admission_type']); ?>">
                                <input type="hidden" name="govt_fee" value="<?php echo htmlspecialchars($course['govt_fee']); ?>">
                            <?php else: ?>
                            <div class="form-group">
                                <label>Course Level</label><small class="req"> *</small>
                                <select class="form-control" name="course_level">
                                    <option value="ug" <?php echo set_select('course_level', 'ug', (isset($course['course_level']) && $course['course_level'] == 'ug')); ?>>UG</option>
                                    <option value="pg" <?php echo set_select('course_level', 'pg', (isset($course['course_level']) && $course['course_level'] == 'pg')); ?>>PG</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('course_level'); ?></span>
                            </div>
                            <div class="form-group">
                                <label>Admission Type</label><small class="req"> *</small>
                                <select class="form-control" name="admission_type">
                                    <option value="first_year" <?php echo set_select('admission_type', 'first_year', (isset($course['admission_type']) && $course['admission_type'] == 'first_year')); ?>>First Year</option>
                                    <option value="lateral" <?php echo set_select('admission_type', 'lateral', (isset($course['admission_type']) && $course['admission_type'] == 'lateral')); ?>>Lateral Entry</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('admission_type'); ?></span>
                            </div>
                            <div class="form-group">
                                <label>Government Fee</label><small class="req"> *</small>
                                <input id="govt_fee" name="govt_fee" type="number" step="0.01" min="0" class="form-control"  value="<?php echo set_value('govt_fee', isset($course['govt_fee']) ? $course['govt_fee'] : '0'); ?>" />
                                <span class="text-danger"><?php echo form_error('govt_fee'); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Management Fee</label><small class="req"> *</small>
                                <input id="mgt_fee" name="mgt_fee" type="number" step="0.01" min="0" class="form-control"  value="<?php echo set_value('mgt_fee', isset($course['mgt_fee']) ? $course['mgt_fee'] : '0'); ?>" />
                                <span class="text-danger"><?php echo form_error('mgt_fee'); ?></span>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input id="sort_order" name="sort_order" type="number" step="1" min="0" class="form-control"  value="<?php echo set_value('sort_order', isset($course['sort_order']) ? $course['sort_order'] : '0'); ?>" />
                                <span class="text-danger"><?php echo form_error('sort_order'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo set_value('description', $course['description']); ?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('status'); ?></label><small class="req"> *</small>
                                <select class="form-control" name="is_active">
                                    <option value="1" <?php echo set_select('is_active', '1', ($course['is_active'] == 1)); ?>><?php echo $this->lang->line('active'); ?></option>
                                    <option value="0" <?php echo set_select('is_active', '0', ($course['is_active'] == 0)); ?>><?php echo $this->lang->line('inactive'); ?></option>
                                </select>
                                <span class="text-danger"><?php echo form_error('is_active'); ?></span>
                            </div>
                            <div class="form-group">
                                <label>Restrict Online Applications</label>
                                <select class="form-control" name="is_restricted">
                                    <option value="0" <?php echo set_select('is_restricted', '0', (empty($course['is_restricted']))); ?>>No (Applications Allowed)</option>
                                    <option value="1" <?php echo set_select('is_restricted', '1', (!empty($course['is_restricted']))); ?>>Yes (Restrict Online Applications)</option>
                                </select>
                                <span class="text-danger"><?php echo form_error('is_restricted'); ?></span>
                                <p class="help-block"><i class="fa fa-info-circle"></i> Set to <strong>Yes</strong> when this course has no available seats. Students selecting this course on the public admission form will see a message: <em>&ldquo;This course is filled and no vacancies currently, kindly choose other available course.&rdquo;</em> and will not be able to submit their application.</p>
                            </div>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div><!--/.col (right) -->
            <!-- left column -->
            <div class="col-md-8">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('course_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="download_label"><?php echo $this->lang->line('course_list'); ?></div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('course_name'); ?></th>
                                        <th><?php echo $this->lang->line('course_code'); ?></th>
                                        <?php if (!$is_school_k12): ?>
                                        <th>Level</th>
                                        <th>Admission Type</th>
                                        <th>Govt Fee</th>
                                        <?php endif; ?>
                                        <th>Mgt Fee</th>
                                        <th>Sort</th>
                                        <th><?php echo $this->lang->line('description'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th>Restricted</th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($course_list)) {
                                        ?>
                                        <tr>
                                            <td colspan="10" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($course_list as $course_item) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $course_item['course_name'] ?></td>
                                                <td class="mailbox-name"><?php echo $course_item['course_code'] ?></td>
                                                <?php if (!$is_school_k12): ?>
                                                <td class="mailbox-name"><?php echo strtoupper($course_item['course_level']); ?></td>
                                                <td class="mailbox-name"><?php echo ($course_item['admission_type'] == 'lateral') ? 'Lateral' : 'First Year'; ?></td>
                                                <td class="mailbox-name"><?php echo number_format((float)$course_item['govt_fee'], 2); ?></td>
                                                <?php endif; ?>
                                                <td class="mailbox-name"><?php echo number_format((float)$course_item['mgt_fee'], 2); ?></td>
                                                <td class="mailbox-name"><?php echo (int)$course_item['sort_order']; ?></td>
                                                <td class="mailbox-name"><?php echo $course_item['description'] ?></td>
                                                <td class="mailbox-name"><?php echo ($course_item['is_active'] == 1) ? $this->lang->line('active') : $this->lang->line('inactive'); ?></td>
                                                <td class="mailbox-name"><?php echo !empty($course_item['is_restricted']) ? '<span class="label label-danger">Yes</span>' : '<span class="label label-success">No</span>'; ?></td>
                                                <td class="mailbox-date text-right">
                                                    <?php if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_edit')) { ?>
                                                        <a data-placement="left" href="<?php echo base_url(); ?>admin/admissioncourses/edit/<?php echo $course_item['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_delete')) { ?>
                                                        <a data-placement="left" href="<?php echo base_url(); ?>admin/admissioncourses/delete/<?php echo $course_item['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
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
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
        <div class="row">
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
