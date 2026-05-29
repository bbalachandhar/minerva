<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-file-text-o"></i> Answer Scripts
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_answer_scripts'); ?>">Answer Scripts</a></li>
            <li class="active"><?php echo htmlspecialchars($event->exam_group_name . ' — ' . $event->exam); ?></li>
            <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')): ?>
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/bulk_register/' . $batch_exam_id); ?>"
                   class="btn btn-xs btn-success">
                    <i class="fa fa-list-ol"></i> Register by Subject
                </a>
            </li>
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/upload/' . $batch_exam_id); ?>"
                   class="btn btn-xs btn-default" title="Register a single script">
                    <i class="fa fa-plus"></i> Single Register
                </a>
            </li>
            <?php endif; ?>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Back button -->
        <a href="<?php echo site_url('coe/coe_answer_scripts'); ?>" class="btn btn-default btn-sm" style="margin-bottom:12px;">
            <i class="fa fa-arrow-left"></i> Back to Exam List
        </a>

        <!-- Exam Info -->
        <div class="callout callout-info" style="margin-bottom:16px;">
            <h4 style="margin-top:0;"><?php echo htmlspecialchars($event->exam_group_name); ?> &mdash; <?php echo htmlspecialchars($event->exam); ?></h4>
            <table style="font-size:13px; border-collapse:separate; border-spacing:0 4px;">
                <tr>
                    <td style="color:#888; padding-right:12px;"><i class="fa fa-graduation-cap"></i> Class</td>
                    <td><strong><?php echo htmlspecialchars($event->class_name ?? '—'); ?></strong></td>
                    <td style="padding-left:24px; color:#888;"><i class="fa fa-calendar"></i> Session</td>
                    <td><strong><?php echo htmlspecialchars($event->session ?? '—'); ?></strong></td>
                    <?php if (!empty($event->date_from)): ?>
                    <td style="padding-left:24px; color:#888;"><i class="fa fa-calendar-o"></i> Dates</td>
                    <td><strong><?php echo date('d M Y', strtotime($event->date_from)); ?><?php echo !empty($event->date_to) ? ' &mdash; ' . date('d M Y', strtotime($event->date_to)) : ''; ?></strong></td>
                    <?php endif; ?>
                </tr>
            </table>
            <?php if (!empty($subjects)): ?>
            <div style="margin-top:8px; font-size:12px;">
                <span style="color:#888;"><i class="fa fa-book"></i> Subjects:</span>
                <?php foreach ($subjects as $sub): ?>
                <span style="margin:0 3px; font-size:11px; color:#fff;">
                    <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                    <?php if (!empty($sub->exam_date)): ?>
                    &nbsp;<em style="opacity:.8;"><?php echo date('d M Y', strtotime($sub->exam_date)) . ' ' . $sub->session_slot; ?></em>
                    <?php endif; ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending</span>
                        <span class="info-box-number"><?php echo $counts['pending']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-blue">
                    <span class="info-box-icon"><i class="fa fa-barcode"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Scanned</span>
                        <span class="info-box-number"><?php echo $counts['scanned']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Uploaded</span>
                        <span class="info-box-number"><?php echo $counts['uploaded']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Filters</h3>
                    </div>
                    <div class="box-body">
                        <form method="get">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <select name="subject_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Subjects</option>
                                            <?php foreach ($subjects as $sub): ?>
                                            <option value="<?php echo $sub->id; ?>"
                                                <?php echo $this->input->get('subject_id') == $sub->id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="scan_status" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            <option value="pending"  <?php echo $this->input->get('scan_status')=='pending'  ? 'selected' : ''; ?>>Pending</option>
                                            <option value="scanned"  <?php echo $this->input->get('scan_status')=='scanned'  ? 'selected' : ''; ?>>Scanned</option>
                                            <option value="uploaded" <?php echo $this->input->get('scan_status')=='uploaded' ? 'selected' : ''; ?>>Uploaded</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-table"></i> Scripts (<?php echo count($scripts); ?>)</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($scripts)): ?>
                            <p class="text-muted text-center">No answer scripts found.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Hall Ticket</th>
                                    <th>Student</th>
                                    <th>Subject</th>
                                    <th>Exam Date</th>
                                    <th>Barcode</th>
                                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $status_class = ['pending' => 'label-warning', 'scanned' => 'label-info', 'uploaded' => 'label-success'];
                                foreach ($scripts as $i => $s):
                                ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($s->hall_ticket_no); ?></strong></td>
                                    <td><?php echo htmlspecialchars($s->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($s->subject_code . ' ' . $s->subject_name); ?></td>
                                    <td><?php echo $s->exam_date ? date('d M Y', strtotime($s->exam_date)) . ' / ' . $s->session_slot : '—'; ?></td>
                                    <td><code><?php echo htmlspecialchars($s->barcode_token ?? '—'); ?></code></td>
                                    <td>
                                        <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_edit')): ?>
                                        <select class="form-control input-sm status-select"
                                                data-id="<?php echo $s->id; ?>"
                                                data-url="<?php echo site_url('coe/coe_answer_scripts/update_status/' . $s->id); ?>">
                                            <option value="pending"  <?php echo $s->scan_status === 'pending'  ? 'selected' : ''; ?>>Pending</option>
                                            <option value="scanned"  <?php echo $s->scan_status === 'scanned'  ? 'selected' : ''; ?>>Scanned</option>
                                            <option value="uploaded" <?php echo $s->scan_status === 'uploaded' ? 'selected' : ''; ?>>Uploaded</option>
                                        </select>
                                        <?php else: ?>
                                        <span class="label <?php echo $status_class[$s->scan_status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($s->scan_status); ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $s->uploaded_at ? date('d M Y', strtotime($s->uploaded_at)) : '—'; ?></td>
                                    <td>
                                        <button class="btn btn-xs btn-info script-view-btn"
                                                data-id="<?php echo $s->id; ?>"
                                                data-url="<?php echo site_url('coe/coe_answer_scripts/modal_content/' . $s->id); ?>"
                                                data-view-url="<?php echo site_url('coe/coe_answer_scripts/view/' . $s->id); ?>"
                                                title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_delete')): ?>
                                        <a href="<?php echo site_url('coe/coe_answer_scripts/delete/' . $s->id); ?>"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Delete this script record?')">
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
    </section>
</div>

<!-- Script Detail Modal -->
<div class="modal fade" id="script-view-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Script Detail</h4>
            </div>
            <div class="modal-body" id="script-modal-body">
                <p class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></p>
            </div>
            <div class="modal-footer">
                <a id="script-modal-fullpage" href="#" target="_blank" class="btn btn-default btn-sm">
                    <i class="fa fa-external-link"></i> Open Full Page
                </a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'answer_scripts']); ?>

<script>
(function() {
    var csrfName  = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash  = '<?php echo $this->security->get_csrf_hash(); ?>';

    // --- Open script detail in modal ---
    $(document).on('click', '.script-view-btn', function() {
        var url     = $(this).data('url');
        var viewUrl = $(this).data('view-url');

        $('#script-modal-body').html('<p class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></p>');
        $('#script-modal-fullpage').attr('href', viewUrl);
        $('#script-view-modal').modal('show');

        $.ajax({
            url: url,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                $('#script-modal-body').html(html);
            },
            error: function() {
                $('#script-modal-body').html('<p class="text-danger">Failed to load details.</p>');
            }
        });
    });

    // Inline status change
    $(document).on('change', '.status-select', function() {
        var $sel    = $(this);
        var url     = $sel.data('url');
        var newVal  = $sel.val();
        var payload = 'scan_status=' + encodeURIComponent(newVal)
            + '&' + csrfName + '=' + csrfHash;

        $sel.prop('disabled', true);
        $.ajax({
            url: url,
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function(res) {
                $sel.prop('disabled', false);
                if (res.status === 'success') {
                    toastr.success(res.msg || 'Status updated.');
                } else {
                    toastr.error(res.msg || 'Update failed.');
                    // revert is hard without old value; just leave as-is
                }
            },
            error: function() {
                $sel.prop('disabled', false);
                toastr.error('Server error updating status.');
            }
        });
    });
})();
</script>

