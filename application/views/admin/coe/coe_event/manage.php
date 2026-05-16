<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i>
            <?php echo htmlspecialchars($event->name); ?>
            <?php
            $cat_map = ['main' => 'label-primary', 'arrear' => 'label-danger', 'supplementary' => 'label-warning'];
            $cat_cls = $cat_map[$event->exam_category] ?? 'label-default';
            ?>
            <span class="label <?php echo $cat_cls; ?>" style="font-size:13px;vertical-align:middle;"><?php echo ucfirst($event->exam_category); ?></span>
            <span class="label label-default" style="font-size:13px;vertical-align:middle;"><?php echo ucfirst($event->exam_type); ?></span>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_event'); ?>">Exam Events</a></li>
            <li class="active"><?php echo htmlspecialchars($event->name); ?></li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- ========================== BATCH EXAMS TABLE ========================== -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-calendar-check-o"></i>
                            <?php echo htmlspecialchars($event->name); ?>
                            &nbsp;<span class="label <?php echo $cat_cls; ?>"><?php echo ucfirst($event->exam_category); ?></span>
                            &nbsp;<span class="label label-default"><?php echo ucfirst($event->exam_type); ?></span>
                            <small class="text-muted" style="font-size:12px;font-weight:normal;"> &mdash; Class Batch Exams</small>
                        </h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_add')): ?>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addBatchModal">
                                <i class="fa fa-plus"></i> Add New
                            </button>
                            <?php endif; ?>
                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_edit')): ?>
                            <a href="<?php echo site_url('coe/coe_event/edit/' . $event->id); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-pencil"></i> Edit Event
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($batches)): ?>
                            <p class="text-muted text-center">No batch exams yet. Add one below.</p>
                        <?php else: ?>
                        <table id="batches-table" class="table table-bordered table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class</th>
                                    <th>Batch Label</th>
                                    <th>Session</th>
                                    <th>Date Range</th>
                                    <th>Pass %</th>
                                    <th>Students</th>
                                    <th>Applications</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($batches as $i => $b): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($b->class ?? '—'); ?></strong>
                                        <?php if ($b->department_name): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($b->department_name); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($b->exam); ?></td>
                                    <td><?php echo htmlspecialchars($b->session ?? '—'); ?></td>
                                    <td>
                                        <?php echo $b->date_from ? date('d M Y', strtotime($b->date_from)) : '—'; ?>
                                        <?php if ($b->date_to && $b->date_to !== $b->date_from): ?>
                                            &ndash; <?php echo date('d M Y', strtotime($b->date_to)); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $b->passing_percentage; ?>%</td>
                                    <td><span class="badge bg-blue"><?php echo (int)$b->student_count; ?></span></td>
                                    <td>
                                        <?php if ((int)$b->app_count > 0): ?>
                                            <a href="<?php echo site_url('coe/coe_application/view/' . $b->id); ?>" class="badge bg-green">
                                                <?php echo (int)$b->app_count; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($b->coe_locked): ?>
                                            <span class="label label-danger"><i class="fa fa-lock"></i> Locked</span>
                                        <?php elseif ($b->is_publish): ?>
                                            <span class="label label-success">Published</span>
                                        <?php else: ?>
                                            <span class="label label-default">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- View Applications -->
                                        <a href="<?php echo site_url('coe/coe_application/view/' . $b->id); ?>" class="btn btn-info btn-xs" title="View Applications">
                                            <i class="fa fa-users"></i>
                                        </a>
                                        <!-- Assign Subjects -->
                                        <?php if ($this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                                        <a href="<?php echo site_url('coe/coe_subject/assign/' . $b->id); ?>"
                                           class="btn btn-<?php echo ($b->subject_count ?? 0) > 0 ? 'default' : 'warning'; ?> btn-xs"
                                           title="<?php echo ($b->subject_count ?? 0) > 0 ? 'Subjects: ' . (int)($b->subject_count ?? 0) . ' assigned' : 'No subjects assigned — click to assign'; ?>">
                                            <i class="fa fa-book"></i><?php if (($b->subject_count ?? 0) == 0): ?> <i class="fa fa-exclamation-circle" style="font-size:9px;"></i><?php endif; ?>
                                        </a>
                                        <?php endif; ?>
                                        <!-- Generate Applications -->
                                        <?php if (!$b->coe_locked && $this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                                        <a href="<?php echo site_url('coe/coe_application/generate/' . $b->id); ?>"
                                           class="btn btn-success btn-xs"
                                           onclick="return confirm('Generate/sync student applications for this batch?');"
                                           title="Generate Applications">
                                            <i class="fa fa-bolt"></i>
                                        </a>
                                        <?php endif; ?>
                                        <!-- Edit batch -->
                                        <?php if (!$b->coe_locked && $this->rbac->hasPrivilege('coe_event', 'can_edit')): ?>
                                        <button type="button" class="btn btn-warning btn-xs btn-edit-batch"
                                                data-id="<?php echo $b->id; ?>"
                                                data-exam="<?php echo htmlspecialchars($b->exam, ENT_QUOTES); ?>"
                                                data-passing-percentage="<?php echo $b->passing_percentage; ?>"
                                                data-date-from="<?php echo $b->date_from; ?>"
                                                data-date-to="<?php echo $b->date_to; ?>"
                                                data-description="<?php echo htmlspecialchars($b->description ?? '', ENT_QUOTES); ?>"
                                                title="Edit batch">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <?php endif; ?>
                                        <!-- Delete batch -->
                                        <?php if (!$b->coe_locked && (int)$b->app_count === 0 && $this->rbac->hasPrivilege('coe_event', 'can_delete')): ?>
                                        <a href="<?php echo site_url('coe/coe_event/delete_batch/' . $b->id); ?>"
                                           class="btn btn-danger btn-xs"
                                           onclick="return confirm('Remove this batch exam? Enrolled students will also be removed.');"
                                           title="Delete batch">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                        <!-- Lock / Unlock batch -->
                                        <?php if ($this->rbac->hasPrivilege('coe_event', 'can_edit')): ?>
                                        <?php if ($b->coe_locked): ?>
                                        <a href="<?php echo site_url('coe/coe_event/toggle_lock_batch/' . $b->id); ?>"
                                           class="btn btn-default btn-xs"
                                           onclick="return confirm('Unlock this batch? Editing and application generation will be re-enabled.');"
                                           title="Unlock batch">
                                            <i class="fa fa-unlock"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="<?php echo site_url('coe/coe_event/toggle_lock_batch/' . $b->id); ?>"
                                           class="btn btn-warning btn-xs"
                                           onclick="return confirm('Lock this batch? Editing and application generation will be disabled.');"
                                           title="Lock batch">
                                            <i class="fa fa-lock"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================== EDIT BATCH MODAL ========================== -->
        <div class="modal fade" id="editBatchModal" tabindex="-1" role="dialog" aria-labelledby="editBatchModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#e08e0b;color:#fff;border-radius:3px 3px 0 0;">
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;"><span>&times;</span></button>
                        <h4 class="modal-title" id="editBatchModalLabel"><i class="fa fa-pencil"></i> Edit Batch Exam</h4>
                    </div>
                    <form method="post" action="" id="editBatchForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Batch Label <span class="text-danger">*</span></label>
                                <input type="text" name="exam" id="modal_exam" class="form-control" required maxlength="250">
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Pass % <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="passing_percentage" id="modal_passing_percentage"
                                                   class="form-control" min="0" max="100" step="0.01">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Date From <span class="text-danger">*</span></label>
                                        <input type="text" name="date_from" id="modal_date_from"
                                               class="form-control date" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label>Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" id="modal_date_to"
                                               class="form-control date" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Notes <span class="text-muted">(optional)</span></label>
                                <input type="text" name="description" id="modal_description" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================== ADD BATCH MODAL ========================== -->
        <?php if ($this->rbac->hasPrivilege('coe_event', 'can_add')): ?>
        <div class="modal fade" id="addBatchModal" tabindex="-1" role="dialog" aria-labelledby="addBatchModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#00a65a;color:#fff;border-radius:3px 3px 0 0;">
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;"><span>&times;</span></button>
                        <h4 class="modal-title" id="addBatchModalLabel"><i class="fa fa-plus-circle"></i> Add Class Batch Exam</h4>
                    </div>
                    <form method="post" action="<?php echo site_url('coe/coe_event/save_batch/' . $event->id); ?>" id="add-batch-form">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label for="add_class_id">Class <span class="text-danger">*</span></label>
                                        <select name="class_id" id="add_class_id" class="form-control" required>
                                            <option value="">— Select a class —</option>
                                            <?php
                                            $last_dept = '';
                                            foreach ($class_list as $cls):
                                                if ($cls['department_name'] !== $last_dept):
                                                    if ($last_dept !== '') echo '</optgroup>';
                                                    echo '<optgroup label="' . htmlspecialchars($cls['department_name'] ?? 'Other') . '">';
                                                    $last_dept = $cls['department_name'];
                                                endif;
                                            ?>
                                                <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
                                            <?php endforeach; ?>
                                            <?php if ($last_dept !== '') echo '</optgroup>'; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="add_session_id">Session <span class="text-danger">*</span></label>
                                        <select name="session_id" id="add_session_id" class="form-control" required>
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s['id']; ?>"
                                                        <?php echo ($s['id'] == $current_session) ? 'selected' : ''; ?>>
                                                    <?php echo $s['session']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="add_passing_percentage">Pass %</label>
                                        <div class="input-group">
                                            <input type="number" name="passing_percentage" id="add_passing_percentage"
                                                   class="form-control" value="50" min="0" max="100" step="0.01">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="add_exam">Batch Label <span class="text-danger">*</span></label>
                                <input type="text" name="exam" id="add_exam" class="form-control"
                                       placeholder="e.g. Nov/Dec 2026 - CSE" maxlength="250" required>
                                <p class="help-block" style="font-size:11px;">Auto-filled from the selected class; editable.</p>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="add_date_from">Date From <span class="text-danger">*</span></label>
                                        <input type="text" name="date_from" id="add_date_from"
                                               class="form-control date" placeholder="Select date" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="add_date_to">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" id="add_date_to"
                                               class="form-control date" placeholder="Select date" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                            <div class="callout callout-info" style="padding:8px 12px;font-size:12px;margin-bottom:0;">
                                <i class="fa fa-info-circle"></i>
                                On save, a batch exam for the selected class is created and all active students in that class &amp; session are automatically enrolled. If a batch already exists for that class/session it will be skipped.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add Batch &amp; Enroll Students</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </section>
</div>

<script>
$(function () {
    // Batches DataTable
    if ($('#batches-table').length && $('#batches-table tbody tr').length > 0) {
        $('#batches-table').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            searching: true,
            ordering: true,
            aaSorting: [],
            aoColumnDefs: [{ bSortable: false, aTargets: [-1] }],
            language: {
                search: 'Search batches:',
                processing: '<i class="fa fa-spinner fa-spin"></i> Loading…',
                lengthMenu: 'Show _MENU_ batches'
            }
        });
    }

    // Add Batch Modal: init Select2 when modal opens (dropdownParent required for modals)
    var eventName = <?php echo json_encode($event->name); ?>;
    $('#addBatchModal').on('shown.bs.modal', function () {
        if (!$('#add_class_id').data('select2')) {
            $('#add_class_id').select2({
                placeholder: '— Select a class —',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#addBatchModal')
            });
        }
    });

    // Auto-fill batch label from selected class
    $('#add_class_id').on('change', function () {
        var name = $(this).find('option:selected').text().trim();
        $('#add_exam').val((name && $(this).val()) ? eventName + ' - ' + name : '');
    });

    // Reset add form when modal is closed
    $('#addBatchModal').on('hidden.bs.modal', function () {
        $('#add-batch-form')[0].reset();
        if ($('#add_class_id').data('select2')) {
            $('#add_class_id').val('').trigger('change');
        }
        ['#add_date_from', '#add_date_to'].forEach(function (sel) {
            var $el = $(sel);
            if ($el.data('datepicker')) { $el.datepicker('destroy'); }
            $el.val('');
        });
    });

    // Edit Batch Modal — populate from data attributes
    var editBatchBaseUrl = '<?php echo site_url('coe/coe_event/update_batch/'); ?>';
    $(document).on('click', '.btn-edit-batch', function () {
        var btn = $(this);
        $('#modal_exam').val(btn.data('exam'));
        $('#modal_passing_percentage').val(btn.data('passing-percentage'));
        $('#modal_description').val(btn.data('description'));

        // Destroy any existing datepicker instances, then init with site format and set date from ISO value
        var $df = $('#modal_date_from');
        var $dt = $('#modal_date_to');
        if ($df.data('datepicker')) { $df.datepicker('destroy'); }
        if ($dt.data('datepicker')) { $dt.datepicker('destroy'); }

        function setPickerDate($el, iso) {
            $el.datepicker({ format: date_format, autoclose: true });
            if (iso) {
                var p = String(iso).split('-');
                if (p.length === 3) {
                    $el.datepicker('setDate', new Date(+p[0], +p[1] - 1, +p[2]));
                }
            }
        }
        setPickerDate($df, btn.data('date-from'));
        setPickerDate($dt, btn.data('date-to'));

        $('#editBatchForm').attr('action', editBatchBaseUrl + btn.data('id'));
        $('#editBatchModal').modal('show');
    });
});
</script>
