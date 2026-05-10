<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-eye"></i> Revaluation — View Request
            <small><?php echo htmlspecialchars($request->student_name); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_revaluation/listing/' . $request->exam_group_class_batch_exam_id); ?>">
                <i class="fa fa-arrow-left"></i> Back</a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div id="rv-flash"></div>

        <div class="row">
            <!-- Request Detail -->
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border"><h3 class="box-title">Request Info</h3></div>
                    <div class="box-body">
                        <table class="table table-condensed">
                            <tr><th>Student</th><td><?php echo htmlspecialchars($request->student_name); ?></td></tr>
                            <tr><th>Admission No</th><td><?php echo htmlspecialchars($request->admission_no); ?></td></tr>
                            <tr><th>Subject</th><td><?php echo htmlspecialchars($request->subject_code . ' — ' . $request->subject_name); ?></td></tr>
                            <tr><th>Original Marks</th><td><?php echo number_format($request->original_marks, 1); ?></td></tr>
                            <tr><th>Request Date</th><td><?php echo date('d M Y', strtotime($request->request_date)); ?></td></tr>
                            <tr><th>Stage</th><td><?php echo $request->stage; ?></td></tr>
                            <tr><th>Status</th><td><?php echo ucfirst($request->status); ?></td></tr>
                            <tr><th>Payment Status</th><td><?php echo ucfirst($request->payment_status); ?></td></tr>
                            <tr><th>Payment Ref</th><td><?php echo htmlspecialchars($request->payment_ref ?? '—'); ?></td></tr>
                            <tr><th>Payment Amount</th><td>₹<?php echo number_format($request->payment_amount, 2); ?></td></tr>
                            <tr><th>Payment Date</th><td><?php echo $request->payment_date ? date('d M Y', strtotime($request->payment_date)) : '—'; ?></td></tr>
                            <tr><th>Remarks</th><td><?php echo htmlspecialchars($request->remarks ?? '—'); ?></td></tr>
                        </table>
                    </div>
                </div>

                <?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_edit') && $request->status !== 'rejected'): ?>
                <!-- Update payment panel -->
                <div class="box box-default">
                    <div class="box-header with-border"><h3 class="box-title">Update Payment</h3></div>
                    <div class="box-body">
                        <form id="payForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <div class="form-group">
                                <label>Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <?php foreach (['pending','paid','waived'] as $ps): ?>
                                    <option value="<?php echo $ps; ?>"
                                        <?php echo $request->payment_status === $ps ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($ps); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ref / Challan</label>
                                <input type="text" name="payment_ref" class="form-control"
                                       value="<?php echo htmlspecialchars($request->payment_ref ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control"
                                       value="<?php echo $request->payment_date ? substr($request->payment_date,0,10) : ''; ?>">
                            </div>
                            <button type="button" class="btn btn-sm btn-info" id="btnPayment">
                                <i class="fa fa-save"></i> Update Payment
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_edit')
                    && $request->payment_status === 'paid'
                    && $request->status === 'pending'): ?>
                <!-- Reject -->
                <div class="box box-danger box-solid collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Reject Request</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display:none">
                        <textarea id="rejectRemarks" class="form-control" rows="2" placeholder="Reason (optional)"></textarea>
                        <br>
                        <button type="button" class="btn btn-danger" id="btnReject">
                            <i class="fa fa-ban"></i> Reject Request
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Assignments -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title">Evaluator Assignments</h3></div>
                    <div class="box-body">
                        <?php if (empty($assignments)): ?>
                            <p class="text-muted text-center">No assignments yet.</p>
                        <?php else: ?>
                        <?php foreach ($assignments as $idx => $asgn): ?>
                        <div class="callout callout-<?php echo $asgn->status === 'completed' ? 'success' : 'info'; ?>">
                            <h4>Stage <?php echo $idx + 1; ?> — <?php echo htmlspecialchars($asgn->evaluator_name); ?></h4>
                            <p>
                                Assigned: <?php echo date('d M Y', strtotime($asgn->assigned_at)); ?><br>
                                Status: <?php echo ucfirst($asgn->status); ?><br>
                                Original: <?php echo number_format($asgn->original_marks, 1); ?><br>
                                Revised: <?php echo $asgn->revised_marks !== null ? number_format($asgn->revised_marks, 1) : '—'; ?>
                            </p>
                            <?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_edit') && $asgn->status === 'assigned'): ?>
                            <form class="save-eval-form" data-id="<?php echo $asgn->id; ?>">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                       value="<?php echo $this->security->get_csrf_hash(); ?>">
                                <div class="form-group">
                                    <label>Revised Marks</label>
                                    <input type="number" name="revised_marks" class="form-control input-sm"
                                           min="0" max="100" step="0.5" required>
                                </div>
                                <div class="form-group">
                                    <label>Remarks</label>
                                    <input type="text" name="remarks" class="form-control input-sm">
                                </div>
                                <button type="submit" class="btn btn-xs btn-success">
                                    <i class="fa fa-check"></i> Save Revised Marks
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_edit')
                              && $request->payment_status === 'paid'
                              && in_array($request->status, ['pending','assigned'])): ?>
                        <hr>
                        <h4>Assign Evaluator</h4>
                        <form id="assignForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <div class="form-group">
                                <select name="evaluator_id" class="form-control" required>
                                    <option value="">— Select Evaluator —</option>
                                    <?php foreach ($staff as $sf): ?>
                                    <option value="<?php echo $sf->id; ?>">
                                        <?php echo htmlspecialchars($sf->full_name . ($sf->designation ? ' (' . $sf->designation . ')' : '')); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary" id="btnAssign">
                                <i class="fa fa-user-plus"></i> Assign
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
var reqId    = <?php echo (int) $request->id; ?>;

function flash(msg, cls) {
    document.getElementById('rv-flash').innerHTML = '<div class="alert alert-' + cls + '">' + msg + '</div>';
}

// Update payment
document.getElementById('btnPayment') && document.getElementById('btnPayment').addEventListener('click', function() {
    var fd = new FormData(document.getElementById('payForm'));
    fd.set(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_revaluation/update_payment/"); ?>' + reqId, {method:'POST',body:fd})
    .then(r=>r.json()).then(function(res) { flash(res.msg, res.status==='success'?'success':'danger'); if(res.status==='success') setTimeout(()=>location.reload(),1200); });
});

// Assign evaluator
document.getElementById('btnAssign') && document.getElementById('btnAssign').addEventListener('click', function() {
    var fd = new FormData(document.getElementById('assignForm'));
    fd.set(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_revaluation/assign/"); ?>' + reqId, {method:'POST',body:fd})
    .then(r=>r.json()).then(function(res) { flash(res.msg, res.status==='success'?'success':'danger'); if(res.status==='success') setTimeout(()=>location.reload(),1200); });
});

// Save evaluation
document.querySelectorAll('.save-eval-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var id = this.dataset.id;
        var fd = new FormData(this);
        fd.set(csrfName, csrfHash);
        fetch('<?php echo site_url("coe/coe_revaluation/save_evaluation/"); ?>' + id, {method:'POST',body:fd})
        .then(r=>r.json()).then(function(res) { flash(res.msg, res.status==='success'?'success':'danger'); if(res.status==='success') setTimeout(()=>location.reload(),1200); });
    });
});

// Reject
document.getElementById('btnReject') && document.getElementById('btnReject').addEventListener('click', function() {
    if (!confirm('Reject this revaluation request?')) return;
    var fd = new FormData();
    fd.append(csrfName, csrfHash);
    fd.append('remarks', document.getElementById('rejectRemarks').value);
    fetch('<?php echo site_url("coe/coe_revaluation/reject/"); ?>' + reqId, {method:'POST',body:fd})
    .then(r=>r.json()).then(function(res) { flash(res.msg, res.status==='success'?'success':'danger'); if(res.status==='success') setTimeout(()=>location.reload(),1200); });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'revaluation']); ?>
