<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-sliders"></i> Moderation Rules
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_moderation'); ?>">Moderation</a></li>
            <li class="active"><?php echo htmlspecialchars($event->exam_group_name . ' — ' . $event->exam); ?></li>
        </ol>
    </section>
    <section class="content">
        <a href="<?php echo site_url('coe/coe_moderation'); ?>" class="btn btn-default btn-sm" style="margin-bottom:12px;">
            <i class="fa fa-arrow-left"></i> Back to Exam List
        </a>

        <!-- How-to guide -->
        <div class="callout callout-info" id="mod-guide" style="margin-bottom:14px;">
            <button type="button" class="close" onclick="document.getElementById('mod-guide').style.display='none'" style="margin-top:-2px;">&times;</button>
            <h4 style="margin-top:0;"><i class="fa fa-info-circle"></i> How Moderation Works</h4>
            <div class="row">
                <div class="col-sm-6">
                    <strong>Rule Types</strong>
                    <table class="table table-condensed" style="margin-top:6px;margin-bottom:0;background:transparent;">
                        <tbody>
                            <tr><td style="width:110px;"><span class="label label-primary">Grace</span></td><td>Add fixed marks to every student for a subject (e.g. +5 marks). Used for board-mandated grace or when a paper was too tough.</td></tr>
                            <tr><td><span class="label label-warning">Moderation</span></td><td>Add a percentage of the base score (e.g. 5% of external marks). Used for batch-level score adjustments.</td></tr>
                            <tr><td><span class="label label-info">Normalisation</span></td><td>Scale marks upward by a percentage when the class average is unusually low.</td></tr>
                            <tr><td><span class="label label-default">Scaling</span></td><td>Apply a multiplication factor to bring scores in line with a target distribution.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-6">
                    <strong>How to Apply</strong>
                    <ol style="margin-top:6px;padding-left:18px;">
                        <li><strong>For all subjects:</strong> Leave Subject blank in the Add Rule form — the rule applies to every subject in this exam.</li>
                        <li><strong>For one subject:</strong> Select a specific subject — only that subject's marks are adjusted.</li>
                        <li><strong>Apply To:</strong> Choose whether the rule adjusts <em>External</em>, <em>Internal</em>, or <em>Total</em> marks.</li>
                        <li>Once rules are added, click <strong>"Apply All Unapplied Rules"</strong> to commit them to student results. <span class="text-danger"><strong>This is irreversible.</strong></span></li>
                        <li>Applied rules are locked (cannot be deleted). Only pending (yellow) rules can be removed.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div id="mod-flash"></div>

        <div class="row">
            <!-- Rules Table -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Rules (<?php echo count($rules); ?>)</h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('coe_moderation', 'can_view')): ?>
                            <button type="button" class="btn btn-xs btn-info" id="btnPreview">
                                <i class="fa fa-eye"></i> Preview Impact
                            </button>
                            <?php endif; ?>
                            <?php if ($this->rbac->hasPrivilege('coe_moderation', 'can_edit')): ?>
                            <button type="button" class="btn btn-xs btn-warning" id="btnApply">
                                <i class="fa fa-check"></i> Apply All Unapplied Rules
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($rules)): ?>
                            <p class="text-muted text-center">No rules defined yet.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr><th>#</th><th>Subject</th><th>Type</th><th>Apply To</th><th>Value</th><th>Description</th><th>Applied</th><th></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rules as $i => $rule): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo $rule->subject_id ? htmlspecialchars($rule->subject_code) : '<em>All</em>'; ?></td>
                                    <td><?php echo ucfirst($rule->rule_type); ?></td>
                                    <td><?php echo ucfirst($rule->applies_to); ?></td>
                                    <td>
                                        <?php if ($rule->value_type === 'flat'): ?>
                                            +<?php echo number_format($rule->value, 1); ?> marks
                                        <?php elseif ($rule->value_type === 'percentage'): ?>
                                            <?php echo number_format($rule->value, 1); ?>%
                                        <?php else: ?>
                                            Target: <?php echo number_format($rule->value, 1); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($rule->reason); ?></td>
                                    <td>
                                        <?php if ($rule->is_applied): ?>
                                            <span class="label label-success">Yes</span>
                                            <br><small><?php echo $rule->applied_at ? date('d M Y', strtotime($rule->applied_at)) : ''; ?></small>
                                        <?php else: ?>
                                            <span class="label label-warning">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$rule->is_applied && $this->rbac->hasPrivilege('coe_moderation', 'can_edit')): ?>
                                        <button class="btn btn-xs btn-success btn-apply-rule"
                                                data-id="<?php echo $rule->id; ?>"
                                                title="Apply this rule to student results">
                                            <i class="fa fa-check"></i> Apply
                                        </button>
                                        <?php endif; ?>
                                        <?php if (!$rule->is_applied && $this->rbac->hasPrivilege('coe_moderation', 'can_delete')): ?>
                                        <button class="btn btn-xs btn-danger btn-del-rule"
                                                data-id="<?php echo $rule->id; ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preview area -->
                <div id="preview-area" style="display:none">
                    <div class="box box-info">
                        <div class="box-header with-border"><h3 class="box-title">Preview Impact</h3></div>
                        <div class="box-body" id="preview-table-wrap"></div>
                    </div>
                </div>
            </div>

            <!-- Add Rule Form -->
            <?php if ($this->rbac->hasPrivilege('coe_moderation', 'can_add')): ?>
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border"><h3 class="box-title">Add Rule</h3></div>
                    <div class="box-body">
                        <form id="ruleForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <input type="hidden" name="batch_exam_id" value="<?php echo $batch_exam_id; ?>">

                            <div class="form-group">
                                <label>Subject (leave blank = all)</label>
                                <select name="subject_id" class="form-control input-sm">
                                    <option value="">All Subjects</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>">
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Rule Type <span class="text-danger">*</span></label>
                                <select name="rule_type" class="form-control input-sm" required>
                                    <option value="grace">Grace</option>
                                    <option value="moderation">Moderation</option>
                                    <option value="normalisation">Normalisation</option>
                                    <option value="scaling">Scaling</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Value Type <span class="text-danger">*</span></label>
                                <select name="value_type" class="form-control input-sm" required>
                                    <option value="flat">Flat (fixed marks)</option>
                                    <option value="percentage">Percentage of base</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Apply To <span class="text-danger">*</span></label>
                                <select name="applies_to" class="form-control input-sm" required>
                                    <option value="external">External</option>
                                    <option value="internal">Internal</option>
                                    <option value="total">Total</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Value <span class="text-danger">*</span></label>
                                <input type="number" name="value" class="form-control input-sm"
                                       min="0" max="100" step="0.5" required>
                                <small class="text-muted">Flat: marks to add. % : percent of base. Norm: target total.</small>
                            </div>

                            <div class="form-group">
                                <label>Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control input-sm"
                                       placeholder="e.g. Grace for first year batch" required>
                            </div>

                            <button type="button" id="btnSaveRule" class="btn btn-success btn-sm btn-block">
                                <i class="fa fa-plus"></i> Add Rule
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
var csrfName  = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash  = '<?php echo $this->security->get_csrf_hash(); ?>';
var batchId   = <?php echo (int) $batch_exam_id; ?>;

function flash(msg, cls) {
    document.getElementById('mod-flash').innerHTML = '<div class="alert alert-' + cls + '">' + msg + '</div>';
}

// Add rule
document.getElementById('btnSaveRule') && document.getElementById('btnSaveRule').addEventListener('click', function() {
    var fd = new FormData(document.getElementById('ruleForm'));
    fd.set(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_moderation/save_rule"); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        flash(res.msg, res.status==='success' ? 'success':'danger');
        if (res.status==='success') setTimeout(()=>location.reload(), 1200);
    });
});

// Delete rule
document.querySelectorAll('.btn-del-rule').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this rule?')) return;
        var id = this.dataset.id;
        var fd = new FormData(); fd.append(csrfName, csrfHash);
        fetch('<?php echo site_url("coe/coe_moderation/delete/"); ?>' + id, {method:'POST', body:fd})
        .then(r=>r.json()).then(function(res) {
            flash(res.msg, res.status==='success' ? 'success':'danger');
            if (res.status==='success') setTimeout(()=>location.reload(), 1000);
        });
    });
});

// Apply single rule
document.querySelectorAll('.btn-apply-rule').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.dataset.id;
        if (!confirm('Apply this rule to student results? This cannot be undone.')) return;
        var me = this;
        me.disabled = true;
        var fd = new FormData(); fd.append(csrfName, csrfHash);
        fetch('<?php echo site_url("coe/coe_moderation/apply_single/"); ?>' + id, {method:'POST', body:fd})
        .then(r=>r.json()).then(function(res) {
            flash(res.msg, res.status==='success' ? 'success':'danger');
            if (res.status==='success') setTimeout(()=>location.reload(), 1200);
            else me.disabled = false;
        });
    });
});

// Apply all rules
document.getElementById('btnApply') && document.getElementById('btnApply').addEventListener('click', function() {
    if (!confirm('Apply all unapplied rules to student results? This cannot be undone.')) return;
    var fd = new FormData(); fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_moderation/apply/"); ?>' + batchId, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        flash(res.msg, res.status==='success' ? 'success':'danger');
        if (res.status==='success') setTimeout(()=>location.reload(), 1500);
    });
});

// Preview
document.getElementById('btnPreview') && document.getElementById('btnPreview').addEventListener('click', function() {
    fetch('<?php echo site_url("coe/coe_moderation/preview/"); ?>' + batchId)
    .then(r=>r.json()).then(function(res) {
        if (res.status !== 'success' || !res.data.length) {
            flash('No preview data (no unapplied rules or no student results).', 'warning');
            return;
        }
        var html = '<table class="table table-condensed table-bordered"><thead><tr><th>Student</th><th>Adm.No</th><th>Before</th><th>Grace</th><th>After</th></tr></thead><tbody>';
        res.data.forEach(function(r) {
            html += '<tr><td>' + r.student_name + '</td><td>' + r.admission_no + '</td>'
                  + '<td>' + parseFloat(r.before_total).toFixed(1) + '</td>'
                  + '<td>+' + parseFloat(r.grace_added).toFixed(1) + '</td>'
                  + '<td><strong>' + parseFloat(r.after_total).toFixed(1) + '</strong></td></tr>';
        });
        html += '</tbody></table>';
        document.getElementById('preview-table-wrap').innerHTML = html;
        document.getElementById('preview-area').style.display = '';
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'moderation']); ?>
