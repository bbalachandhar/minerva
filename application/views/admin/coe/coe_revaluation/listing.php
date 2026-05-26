<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-refresh"></i> Revaluation Requests
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_revaluation'); ?>"><i class="fa fa-arrow-left"></i> Back</a></li>
            <?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_add')): ?>
            <li>
                <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#rvAddModal">
                    <i class="fa fa-plus"></i> New Request
                </button>
            </li>
            <?php endif; ?>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <form method="get" class="form-inline">
                            <div class="form-group" style="margin-right:10px">
                                <label>Subject&nbsp;</label>
                                <select name="subject_id" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>"
                                        <?php echo $this->input->get('subject_id') == $sub->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-right:10px">
                                <label>Status&nbsp;</label>
                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach (['pending','assigned','completed','rejected'] as $st): ?>
                                    <option value="<?php echo $st; ?>"
                                        <?php echo $this->input->get('status')===$st ? 'selected':''; ?>>
                                        <?php echo ucfirst($st); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Payment&nbsp;</label>
                                <select name="payment_status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach (['pending','paid','waived'] as $ps): ?>
                                    <option value="<?php echo $ps; ?>"
                                        <?php echo $this->input->get('payment_status')===$ps ? 'selected':''; ?>>
                                        <?php echo ucfirst($ps); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
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
                        <h3 class="box-title">Requests (<?php echo count($requests); ?>)</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($requests)): ?>
                            <p class="text-muted text-center">No revaluation requests found.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Admission No</th>
                                    <th>Subject</th>
                                    <th>Original Marks</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $status_cls = [
                                    'pending'   => 'label-warning',
                                    'assigned'  => 'label-info',
                                    'completed' => 'label-success',
                                    'rejected'  => 'label-danger',
                                ];
                                $pay_cls = [
                                    'pending' => 'label-warning',
                                    'paid'    => 'label-success',
                                    'waived'  => 'label-default',
                                ];
                                foreach ($requests as $i => $req):
                                ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($req->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($req->admission_no); ?></td>
                                    <td><?php echo htmlspecialchars($req->subject_code); ?></td>
                                    <td><?php echo number_format($req->original_marks, 1); ?></td>
                                    <td>
                                        <span class="label <?php echo $pay_cls[$req->payment_status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($req->payment_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label <?php echo $status_cls[$req->status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($req->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($req->request_date)); ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_revaluation/view/' . $req->id); ?>"
                                           class="btn btn-xs btn-primary">
                                            <i class="fa fa-eye"></i>
                                        </a>
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

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'revaluation']); ?>

<!-- New Revaluation Request Modal -->
<?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_add')): ?>
<div class="modal fade" id="rvAddModal" tabindex="-1" role="dialog" aria-labelledby="rvAddModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:8px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#3c8dbc,#1a6091);color:#fff;border:none;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;">&times;</button>
                <h4 class="modal-title" id="rvAddModalLabel">
                    <i class="fa fa-plus-circle"></i> New Revaluation Request
                    <small style="font-size:13px;opacity:.85;">&nbsp;&mdash;&nbsp;<?php echo htmlspecialchars($event->exam_group_name . ' — ' . $event->exam); ?></small>
                </h4>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div id="rv-modal-flash"></div>
                <form id="rvModalForm">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <input type="hidden" name="batch_exam_id" value="<?php echo $batch_exam_id; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Student <span class="text-danger">*</span></label>
                                <select name="student_id" class="form-control" required>
                                    <option value="">— Select Student —</option>
                                    <?php foreach ($students as $st): ?>
                                    <option value="<?php echo $st->id; ?>">
                                        <?php echo htmlspecialchars($st->admission_no . ' — ' . $st->full_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Subject <span class="text-danger">*</span></label>
                                <select name="subject_id" class="form-control" required>
                                    <option value="">— Select Subject —</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>">
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Original Marks <span class="text-danger">*</span></label>
                                <input type="number" name="original_marks" class="form-control" min="0" max="100" step="0.5" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Request Date <span class="text-danger">*</span></label>
                                <input type="date" name="request_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="waived">Waived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="payment_amount" class="form-control" min="0" step="0.01" value="500" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Ref / Challan No</label>
                                <input type="text" name="payment_ref" class="form-control" placeholder="Receipt or reference number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0f0f0;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnSaveRvModal" class="btn btn-primary"><i class="fa fa-save"></i> Save Request</button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    document.getElementById('btnSaveRvModal').addEventListener('click', function() {
        var fd = new FormData(document.getElementById('rvModalForm'));
        fd.set(csrfName, csrfHash);
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        fetch('<?php echo site_url("coe/coe_revaluation/save_request"); ?>', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(res) {
            var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
            document.getElementById('rv-modal-flash').innerHTML = '<div class="alert ' + cls + '">' + res.msg + '</div>';
            if (res.status === 'success') {
                setTimeout(function(){ window.location.reload(); }, 1200);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save"></i> Save Request';
            }
        });
    });
    $('#rvAddModal').on('hidden.bs.modal', function() {
        document.getElementById('rv-modal-flash').innerHTML = '';
        document.getElementById('rvModalForm').reset();
        document.querySelector('#rvModalForm input[name="request_date"]').value = '<?php echo date('Y-m-d'); ?>';
        document.querySelector('#rvModalForm input[name="payment_amount"]').value = '500';
        document.getElementById('btnSaveRvModal').disabled = false;
        document.getElementById('btnSaveRvModal').innerHTML = '<i class="fa fa-save"></i> Save Request';
    });
}());
</script>
<?php endif; ?>
