<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-university"></i> <?php echo $this->lang->line('coe_exam_regulations'); ?><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <!-- Filter by session -->
            <div class="col-md-12">
                <form method="get" action="<?php echo site_url('coe/coe_setup'); ?>">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?></label>
                                        <select name="session_id" class="form-control" onchange="this.form.submit()">
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s["id"]; ?>" <?php echo ($s["id"] == $selected_session) ? 'selected' : ''; ?>>
                                                    <?php echo $s["session"]; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php if ($this->rbac->hasPrivilege('coe_setup', 'can_add')): ?>
                                <div class="col-md-4" style="margin-top:25px;">
                                    <a href="<?php echo site_url('coe/coe_setup/add'); ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('coe_add_regulation'); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Regulations table -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('coe_exam_regulations'); ?></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th><?php echo $this->lang->line('department'); ?></th>
                                    <th><?php echo $this->lang->line('coe_regulation_type'); ?></th>
                                    <th><?php echo $this->lang->line('coe_min_attendance_pct'); ?></th>
                                    <th>Int%&nbsp;/&nbsp;Ext%</th>
                                    <th><?php echo $this->lang->line('coe_has_credit_system'); ?></th>
                                    <th><?php echo $this->lang->line('coe_grading_scheme'); ?></th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($regulations)): ?>
                                    <tr><td colspan="9" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($regulations as $i => $r): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($r->class_name); ?></td>
                                        <td><?php echo htmlspecialchars($r->department_name ?: '—'); ?></td>
                                        <td>
                                            <?php if ($r->regulation_type === 'affiliated'): ?>
                                                <span class="label label-info"><?php echo $this->lang->line('coe_affiliated'); ?></span>
                                            <?php else: ?>
                                                <span class="label label-success"><?php echo $this->lang->line('coe_autonomous'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $r->min_attendance_pct; ?>%</td>
                                        <td><?php echo $r->internal_marks_pct; ?>% / <?php echo $r->external_marks_pct; ?>%</td>
                                        <td><?php echo $r->has_credit_system ? '<span class="label label-success">CBCS</span>' : '<span class="label label-default">No</span>'; ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $r->grading_scheme)); ?></td>
                                        <td>
                                            <?php if ($this->rbac->hasPrivilege('coe_setup', 'can_edit')): ?>
                                            <a href="<?php echo site_url('coe/coe_setup/edit/' . $r->id); ?>" class="btn btn-xs btn-default" title="<?php echo $this->lang->line('edit'); ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_setup', 'can_delete')): ?>
                                            <a href="<?php echo site_url('coe/coe_setup/delete/' . $r->id); ?>" class="btn btn-xs btn-danger confirm_delete" title="<?php echo $this->lang->line('delete'); ?>">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).on('click', '.confirm_delete', function(e) {
    e.preventDefault();
    var url = $(this).attr('href');
    Swal.fire({
        title: '<?php echo $this->lang->line("are_you_sure"); ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<?php echo $this->lang->line("delete"); ?>'
    }).then(function(result) {
        if (result.value) { window.location = url; }
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'exam_regulations']); ?>
