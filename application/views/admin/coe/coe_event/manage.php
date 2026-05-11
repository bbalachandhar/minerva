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
                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_edit')): ?>
                            <a href="<?php echo site_url('coe/coe_event/edit/' . $event->id); ?>" class="btn btn-default btn-xs">
                                <i class="fa fa-pencil"></i> Edit Event
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($batches)): ?>
                            <p class="text-muted text-center">No batch exams yet. Add one below.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-hover table-condensed">
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
                                <tr class="<?php echo ($edit_batch_id && $edit_batch_id == $b->id) ? 'warning' : ''; ?>">
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
                                        <a href="<?php echo site_url('coe/coe_event/manage/' . $event->id . '?edit_batch=' . $b->id); ?>"
                                           class="btn btn-warning btn-xs" title="Edit batch">
                                            <i class="fa fa-pencil"></i>
                                        </a>
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

        <!-- ========================== INLINE EDIT FORM ========================== -->
        <?php if ($edit_batch && $edit_batch_id): ?>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-pencil"></i> Edit Batch Exam</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('coe/coe_event/update_batch/' . $edit_batch->id); ?>">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Batch Label <span class="text-danger">*</span></label>
                                        <input type="text" name="exam" class="form-control"
                                               value="<?php echo htmlspecialchars($edit_batch->exam); ?>" required maxlength="250">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Pass % <span class="text-danger">*</span></label>
                                        <input type="number" name="passing_percentage" class="form-control"
                                               value="<?php echo $edit_batch->passing_percentage; ?>" min="0" max="100" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Date From <span class="text-danger">*</span></label>
                                        <input type="text" name="date_from" class="form-control date"
                                               value="<?php echo $this->customlib->dateyyyymmddTodateformat($edit_batch->date_from); ?>" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" class="form-control date"
                                               value="<?php echo $this->customlib->dateyyyymmddTodateformat($edit_batch->date_to); ?>" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label>Notes <span class="text-muted">(optional)</span></label>
                                        <input type="text" name="description" class="form-control"
                                               value="<?php echo htmlspecialchars($edit_batch->description ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Save Changes</button>
                            <a href="<?php echo site_url('coe/coe_event/manage/' . $event->id); ?>" class="btn btn-default">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ========================== ADD BATCH FORM ========================== -->
        <?php if ($this->rbac->hasPrivilege('coe_event', 'can_add')): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plus-circle"></i> Add Class Batch Exam</h3>
                    </div>
                    <form method="post" action="<?php echo site_url('coe/coe_event/save_batch/' . $event->id); ?>" id="add-batch-form">
                        <div class="box-body">
                            <div class="row">
                                <!-- Class (multi-select) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_id">
                                            Class <span class="text-danger">*</span> <small class="text-muted">(select one or more)</small>
                                            <span class="pull-right" style="font-weight:normal;font-size:11px;">
                                                <a href="#" id="class-select-all">Select All</a> &nbsp;|&nbsp;
                                                <a href="#" id="class-clear-all">Clear</a>
                                            </span>
                                        </label>
                                        <select name="class_id[]" id="class_id" class="form-control select2-class" multiple>
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

                                <!-- Session -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="session_id">Session <span class="text-danger">*</span></label>
                                        <select name="session_id" id="session_id" class="form-control" required>
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s['id']; ?>"
                                                        <?php echo ($s['id'] == $current_session) ? 'selected' : ''; ?>>
                                                    <?php echo $s['session']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Batch Label -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exam">Batch Label <span class="text-danger">*</span></label>
                                        <input type="text" name="exam" id="exam" class="form-control"
                                               placeholder="e.g. Nov/Dec 2026 - CSE" maxlength="250" required>
                                        <p class="help-block" style="font-size:11px;">Auto-filled when one class selected; editable. One batch is created per class.</p>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <!-- Date From -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_from">Date From <span class="text-danger">*</span></label>
                                        <input type="text" name="date_from" id="date_from" class="form-control date"
                                               placeholder="Select date" autocomplete="off" required>
                                    </div>
                                </div>

                                <!-- Date To -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_to">Date To <span class="text-danger">*</span></label>
                                        <input type="text" name="date_to" id="date_to" class="form-control date"
                                               placeholder="Select date" autocomplete="off" required>
                                    </div>
                                </div>

                                <!-- Pass % -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="passing_percentage">Pass %</label>
                                        <input type="number" name="passing_percentage" id="passing_percentage"
                                               class="form-control" value="50" min="0" max="100" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-info" style="padding:8px 12px;font-size:12px;">
                                        <i class="fa fa-info-circle"></i>
                                        On save, <strong>one batch exam per selected class</strong> is created, and all active students
                                        in each class &amp; session are automatically enrolled. Existing class/session combinations are skipped.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-plus"></i> Add Batch &amp; Enroll Students
                            </button>
                            <a href="<?php echo site_url('coe/coe_event'); ?>" class="btn btn-default">Back to Events</a>
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
    // Select2 for class multi-selector
    $('.select2-class').select2({ placeholder: 'Search and select class(es)…', allowClear: true, width: '100%' });

    // Select All / Clear All
    $('#class-select-all').on('click', function (e) {
        e.preventDefault();
        $('#class_id option').prop('selected', true);
        $('#class_id').trigger('change');
    });
    $('#class-clear-all').on('click', function (e) {
        e.preventDefault();
        $('#class_id').val(null).trigger('change');
        $('#exam').val('');
    });

    // Auto-suggest batch label from selected class names
    var eventName = <?php echo json_encode($event->name); ?>;
    $('#class_id').on('change', function () {
        var selected = $(this).find('option:selected');
        if (selected.length === 0) { return; }

        var names = selected.map(function () { return $(this).text().trim(); }).get();
        var suffix;
        if (names.length <= 2) {
            suffix = names.join(', ');
        } else {
            suffix = names[0] + ', ' + names[1] + ' (+' + (names.length - 2) + ' more)';
        }
        $('#exam').val(eventName + ' - ' + suffix);
    });
});
</script>
