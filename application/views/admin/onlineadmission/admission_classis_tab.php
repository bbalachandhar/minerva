<div class="row">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo $this->lang->line('add_admission_class'); ?></h3>
            </div>
            <form action="<?php echo site_url('admin/onlineadmission/add_admission_class') ?>" method="post" id="addAdmissionClassForm">
                <div class="box-body">
                    <div class="form-group">
                        <label for="department"><?php echo $this->lang->line('department'); ?></label>
                        <input type="text" class="form-control" id="department" name="department" value="<?php echo set_value('department'); ?>">
                        <span class="text-danger"><?php echo form_error('department'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="class_name"><?php echo $this->lang->line('class_name'); ?></label>
                        <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo set_value('class_name'); ?>">
                        <span class="text-danger"><?php echo form_error('class_name'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="admission_flags"><?php echo $this->lang->line('admission_flags'); ?></label>
                        <input type="text" class="form-control" id="admission_flags" name="admission_flags" value="<?php echo set_value('admission_flags'); ?>">
                        <span class="text-danger"><?php echo form_error('admission_flags'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="is_active"><?php echo $this->lang->line('is_active'); ?></label>
                        <div class="material-switch pull-right">
                            <input id="is_active" name="is_active" type="checkbox" value="1" <?php echo set_checkbox('is_active', '1', true); ?>>
                            <label for="is_active" class="label-success"></label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo $this->lang->line('admission_class_list'); ?></h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover example">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('department'); ?></th>
                                <th><?php echo $this->lang->line('class_name'); ?></th>
                                <th><?php echo $this->lang->line('admission_flags'); ?></th>
                                <th><?php echo $this->lang->line('is_active'); ?></th>
                                <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($admission_classes)) {
                                foreach ($admission_classes as $class) { ?>
                                    <tr>
                                        <td><?php echo $class->department; ?></td>
                                        <td><?php echo $class->class_name; ?></td>
                                        <td><?php echo $class->admission_flags; ?></td>
                                        <td>
                                            <div class="material-switch pull-right">
                                                <input id="status_<?php echo $class->id; ?>" name="is_active_<?php echo $class->id; ?>" type="checkbox" class="chk" value="1" data-id="<?php echo $class->id; ?>" <?php echo ($class->is_active == 1) ? 'checked' : ''; ?>>
                                                <label for="status_<?php echo $class->id; ?>" class="label-success"></label>
                                            </div>
                                        </td>
                                        <td class="mailbox-options text-right">
                                            <a href="#" class="btn btn-default btn-xs edit-admission-class" data-id="<?php echo $class->id; ?>" data-department="<?php echo $class->department; ?>" data-class_name="<?php echo $class->class_name; ?>" data-admission_flags="<?php echo $class->admission_flags; ?>" data-is_active="<?php echo $class->is_active; ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="#" class="btn btn-default btn-xs delete-admission-class" data-id="<?php echo $class->id; ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                <i class="fa fa-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Admission Class Modal -->
<div class="modal fade" id="editAdmissionClassModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $this->lang->line('edit_admission_class'); ?></h4>
            </div>
            <form action="<?php echo site_url('admin/onlineadmission/update_admission_class') ?>" method="post" id="updateAdmissionClassForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_department"><?php echo $this->lang->line('department'); ?></label>
                        <input type="text" class="form-control" id="edit_department" name="department">
                        <span class="text-danger"><?php echo form_error('department'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="edit_class_name"><?php echo $this->lang->line('class_name'); ?></label>
                        <input type="text" class="form-control" id="edit_class_name" name="class_name">
                        <span class="text-danger"><?php echo form_error('class_name'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="edit_admission_flags"><?php echo $this->lang->line('admission_flags'); ?></label>
                        <input type="text" class="form-control" id="edit_admission_flags" name="admission_flags">
                        <span class="text-danger"><?php echo form_error('admission_flags'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_active"><?php echo $this->lang->line('is_active'); ?></label>
                        <div class="material-switch pull-right">
                            <input id="edit_is_active" name="is_active" type="checkbox" value="1">
                            <label for="edit_is_active" class="label-success"></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('update'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Handle Add Form Submission
        $('#addAdmissionClassForm').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        successMsg(response.message);
                        window.location.reload();
                    } else {
                        errorMsg(response.message);
                    }
                },
                error: function () {
                    errorMsg('<?php echo $this->lang->line('error_occurred'); ?>');
                }
            });
        });

        // Handle Edit Button Click
        $(document).on('click', '.edit-admission-class', function () {
            var id = $(this).data('id');
            var department = $(this).data('department');
            var class_name = $(this).data('class_name');
            var admission_flags = $(this).data('admission_flags');
            var is_active = $(this).data('is_active');

            $('#edit_id').val(id);
            $('#edit_department').val(department);
            $('#edit_class_name').val(class_name);
            $('#edit_admission_flags').val(admission_flags);
            $('#edit_is_active').prop('checked', is_active == 1);
            $('#editAdmissionClassModal').modal('show');
        });

        // Handle Update Form Submission
        $('#updateAdmissionClassForm').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var data = form.serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        successMsg(response.message);
                        window.location.reload();
                    } else {
                        errorMsg(response.message);
                    }
                },
                error: function () {
                    errorMsg('<?php echo $this->lang->line('error_occurred'); ?>');
                }
            });
        });

        // Handle Delete Button Click
        $(document).on('click', '.delete-admission-class', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            if (confirm('<?php echo $this->lang->line('delete_confirm'); ?>')) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo site_url('admin/onlineadmission/delete_admission_class'); ?>',
                    data: { id: id },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            successMsg(response.message);
                            window.location.reload();
                        } else {
                            errorMsg(response.message);
                        }
                    },
                    error: function () {
                        errorMsg('<?php echo $this->lang->line('error_occurred'); ?>');
                    }
                });
            }
        });

        // Handle Status Change Toggle
        $(document).on('change', '.chk', function () {
            var id = $(this).data('id');
            var status = this.checked ? 1 : 0;
            if (confirm('<?php echo $this->lang->line('confirm_status'); ?>')) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo site_url('admin/onlineadmission/change_admission_class_status'); ?>',
                    data: { id: id, status: status },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            successMsg(response.message);
                        } else {
                            errorMsg(response.message);
                            // Revert the toggle if update fails
                            $('#status_' + id).prop('checked', !status);
                        }
                    },
                    error: function () {
                        errorMsg('<?php echo $this->lang->line('error_occurred'); ?>');
                        // Revert the toggle if update fails
                        $('#status_' + id).prop('checked', !status);
                    }
                });
            } else {
                // Revert the toggle if user cancels
                $(this).prop('checked', !status);
            }
        });
    });
</script>