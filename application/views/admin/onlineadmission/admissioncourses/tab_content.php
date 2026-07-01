<?php
$currency_symbol = $this->customlib->getCurrency();
$is_school_k12 = (strtolower(trim($sch_setting_detail->institution_type)) != 'college');
?>
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
                        <?php if ($is_school_k12): ?>
                            <input type="hidden" name="course_level" value="ug">
                            <input type="hidden" name="admission_type" value="first_year">
                            <input type="hidden" name="govt_fee" value="0">
                        <?php else: ?>
                        <div class="form-group">
                            <label>Course Level</label><small class="req"> *</small>
                            <select class="form-control" name="course_level">
                                <option value="ug" <?php echo set_select('course_level', 'ug', true); ?>>UG</option>
                                <option value="pg" <?php echo set_select('course_level', 'pg'); ?>>PG</option>
                            </select>
                            <span class="text-danger"><?php echo form_error('course_level'); ?></span>
                        </div>
                        <div class="form-group">
                            <label>Admission Type</label><small class="req"> *</small>
                            <select class="form-control" name="admission_type">
                                <option value="first_year" <?php echo set_select('admission_type', 'first_year', true); ?>>First Year</option>
                                <option value="lateral" <?php echo set_select('admission_type', 'lateral'); ?>>Lateral Entry</option>
                            </select>
                            <span class="text-danger"><?php echo form_error('admission_type'); ?></span>
                        </div>
                        <div class="form-group">
                            <label>Government Fee (<?php echo $currency_symbol; ?>)</label><small class="req"> *</small>
                            <input id="govt_fee" name="govt_fee" type="number" step="0.01" min="0" class="form-control" value="<?php echo set_value('govt_fee'); ?>" />
                            <span class="text-danger"><?php echo form_error('govt_fee'); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Management Fee (<?php echo $currency_symbol; ?>)</label><small class="req"> *</small>
                            <input id="mgt_fee" name="mgt_fee" type="number" step="0.01" min="0" class="form-control" value="<?php echo set_value('mgt_fee'); ?>" />
                            <span class="text-danger"><?php echo form_error('mgt_fee'); ?></span>
                        </div>
                        <div class="form-group">
                            <label>Sort Order</label>
                            <input id="sort_order" name="sort_order" type="number" step="1" min="0" class="form-control" value="<?php echo set_value('sort_order', '0'); ?>" />
                            <span class="text-danger"><?php echo form_error('sort_order'); ?></span>
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
                        <div class="form-group">
                            <label>Restrict Online Applications</label>
                            <select class="form-control" name="is_restricted">
                                <option value="0" <?php echo set_select('is_restricted', '0', true); ?>>No (Applications Allowed)</option>
                                <option value="1" <?php echo set_select('is_restricted', '1'); ?>>Yes (Restrict Online Applications)</option>
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
    <?php } ?>
    <div class="col-md-<?php
    if ($this->rbac->hasPrivilege('online_admission_admission_courses', 'can_add')) {
        echo "8";
    } else {
        echo "12";
    }
    ?>">
        <!-- Bulk Import Panel -->
        <div class="box box-default" style="margin-bottom:14px;">
            <div class="box-header with-border" style="background:#f0f7ff;">
                <h3 class="box-title"><i class="fa fa-upload text-primary"></i> Bulk Import Courses from CSV</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo site_url('admin/onlineadmission/course_import_template'); ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-download"></i> Download Template CSV
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="align-items:flex-end;">
                    <div class="col-md-6">
                        <p style="font-size:12px;color:#666;margin-bottom:8px;">
                            <strong>CSV columns (in order):</strong>
                            <code>course_name, course_code, course_level (UG/PG), admission_type (first_year/lateral), govt_fee, mgt_fee, sort_order, description, is_active (1/0)</code><br>
                            Duplicate <code>course_code</code> entries will be <strong>updated</strong>; new codes will be <strong>inserted</strong>.
                        </p>
                        <div class="input-group">
                            <input type="file" id="bulk_course_csv" accept=".csv" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" id="btn_bulk_import_courses">
                                    <i class="fa fa-cloud-upload"></i> Import
                                </button>
                            </span>
                        </div>
                        <div id="bulk_import_result" style="margin-top:8px;"></div>
                    </div>
                </div>
            </div>
        </div>

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
                                <?php if (!$is_school_k12): ?>
                                <th>Level</th>
                                <th>Admission Type</th>
                                <th>Govt. Fee</th>
                                <?php endif; ?>
                                <th>Mgt. Fee</th>
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
                                foreach ($course_list as $course) {
                                    ?>
                                    <tr>
                                        <td class="mailbox-name"><?php echo $course['course_name'] ?></td>
                                        <td class="mailbox-name"><?php echo $course['course_code'] ?></td>
                                        <?php if (!$is_school_k12): ?>
                                        <td class="mailbox-name"><?php echo strtoupper($course['course_level']); ?></td>
                                        <td class="mailbox-name"><?php echo ($course['admission_type'] == 'lateral') ? 'Lateral' : 'First Year'; ?></td>
                                        <td class="mailbox-name"><?php echo $currency_symbol . ' ' . number_format((float)$course['govt_fee'], 2); ?></td>
                                        <?php endif; ?>
                                        <td class="mailbox-name"><?php echo $currency_symbol . ' ' . number_format((float)$course['mgt_fee'], 2); ?></td>
                                        <td class="mailbox-name"><?php echo (int)$course['sort_order']; ?></td>
                                        <td class="mailbox-name"><?php echo $course['description'] ?></td>
                                        <td class="mailbox-name"><?php echo ($course['is_active'] == 1) ? $this->lang->line('active') : $this->lang->line('inactive'); ?></td>
                                        <td class="mailbox-name"><?php echo !empty($course['is_restricted']) ? '<span class="label label-danger">Yes</span>' : '<span class="label label-success">No</span>'; ?></td>
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

        // Bulk CSV import
        $('#btn_bulk_import_courses').on('click', function(){
            var file = $('#bulk_course_csv')[0].files[0];
            if (!file) { alert('Please select a CSV file first.'); return; }
            if (file.name.split('.').pop().toLowerCase() !== 'csv') { alert('Only .csv files are supported.'); return; }

            var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');
            var fd   = new FormData();
            fd.append('csv_file', file);
            fd.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

            $.ajax({
                url: '<?php echo site_url("admin/onlineadmission/bulk_import_courses"); ?>',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        $('#bulk_import_result').html('<div class="alert alert-success" style="margin:0;font-size:13px;"><i class="fa fa-check-circle"></i> ' + res.message + '</div>');
                        setTimeout(function(){ location.reload(); }, 1500);
                    } else {
                        $('#bulk_import_result').html('<div class="alert alert-danger" style="margin:0;font-size:13px;"><i class="fa fa-times-circle"></i> ' + res.message + '</div>');
                    }
                },
                error: function(){ $('#bulk_import_result').html('<div class="alert alert-danger" style="margin:0;font-size:13px;">Upload failed. Please try again.</div>'); },
                complete: function(){ $btn.prop('disabled', false).html('<i class="fa fa-cloud-upload"></i> Import'); }
            });
        });
    });
</script>
