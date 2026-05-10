<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-plus-circle"></i> New Revaluation Request
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_revaluation/listing/' . $batch_exam_id); ?>">
                <i class="fa fa-arrow-left"></i> Back</a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div id="rv-flash"></div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title">Request Details</h3></div>
                    <div class="box-body">
                        <form id="rvForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <input type="hidden" name="batch_exam_id" value="<?php echo $batch_exam_id; ?>">

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

                            <div class="form-group">
                                <label>Original Marks <span class="text-danger">*</span></label>
                                <input type="number" name="original_marks" class="form-control" min="0" max="100" step="0.5" required>
                            </div>

                            <div class="form-group">
                                <label>Request Date <span class="text-danger">*</span></label>
                                <input type="date" name="request_date" class="form-control"
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Payment Status</label>
                                <select name="payment_status" class="form-control">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="waived">Waived</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Payment Ref / Challan No</label>
                                <input type="text" name="payment_ref" class="form-control"
                                       placeholder="Receipt or reference number">
                            </div>

                            <div class="form-group">
                                <label>Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="payment_amount" class="form-control"
                                       min="0" step="0.01" value="500" required>
                            </div>

                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>

                            <button type="button" id="btnSaveRv" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

document.getElementById('btnSaveRv').addEventListener('click', function() {
    var fd = new FormData(document.getElementById('rvForm'));
    fd.set(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_revaluation/save_request"); ?>', {method:'POST',body:fd})
    .then(r=>r.json()).then(function(res) {
        var cls = res.status==='success' ? 'alert-success':'alert-danger';
        document.getElementById('rv-flash').innerHTML='<div class="alert '+cls+'">'+res.msg+'</div>';
        if (res.status==='success') {
            setTimeout(function(){
                window.location.href='<?php echo site_url("coe/coe_revaluation/listing/".$batch_exam_id); ?>';
            }, 1500);
        }
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'revaluation']); ?>
