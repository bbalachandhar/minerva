<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-upload"></i> Register Answer Script
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/listing/' . $batch_exam_id); ?>">
                    <i class="fa fa-arrow-left"></i> Back to Listing
                </a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Script Details</h3>
                    </div>
                    <div class="box-body">
                        <div id="upload-msg"></div>
                        <form id="uploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="batch_exam_id" value="<?php echo (int) $batch_exam_id; ?>">
                            <?php echo $this->security->get_csrf_token_name(); ?>
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <div class="form-group">
                                <label>Hall Ticket <span class="text-danger">*</span></label>
                                <select name="hall_ticket_id" class="form-control" required>
                                    <option value="">— Select Hall Ticket —</option>
                                    <?php foreach ($hall_tickets as $ht): ?>
                                    <option value="<?php echo $ht->id; ?>">
                                        <?php echo htmlspecialchars($ht->hall_ticket_no . ' — ' . $ht->student_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Subject <span class="text-danger">*</span></label>
                                <select name="subject_id" class="form-control" required>
                                    <option value="">— Select Subject —</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>"
                                            data-date="<?php echo $sub->exam_date; ?>"
                                            data-slot="<?php echo $sub->session_slot; ?>">
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                        <?php if ($sub->exam_date): ?>
                                            (<?php echo date('d M Y', strtotime($sub->exam_date)) . ' ' . $sub->session_slot; ?>)
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Exam Date <span class="text-danger">*</span></label>
                                        <input type="date" name="exam_date" id="exam_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Session <span class="text-danger">*</span></label>
                                        <select name="session_slot" id="session_slot" class="form-control" required>
                                            <option value="FN">Forenoon (FN)</option>
                                            <option value="AN">Afternoon (AN)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Page Count</label>
                                <input type="number" name="page_count" class="form-control" min="1" max="100"
                                       placeholder="Number of pages (optional)">
                            </div>

                            <div class="form-group">
                                <label>Upload Scanned File <small class="text-muted">(PDF/JPG/PNG, max 20MB — optional)</small></label>
                                <input type="file" name="script_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>

                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" maxlength="500"
                                          placeholder="Any notes about this script"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Register Script
                            </button>
                            <a href="<?php echo site_url('coe/coe_answer_scripts/listing/' . $batch_exam_id); ?>"
                               class="btn btn-default">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Notes</h3>
                    </div>
                    <div class="box-body">
                        <ul>
                            <li>A unique barcode token will be auto-generated for each script.</li>
                            <li>The file upload is optional — you can register a physical script first and upload the scan later.</li>
                            <li>Each hall ticket + subject combination must be unique.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Auto-fill date/slot when subject is selected
document.querySelector('[name="subject_id"]').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var d = opt.dataset.date;
    var s = opt.dataset.slot;
    if (d) document.getElementById('exam_date').value = d;
    if (s) document.getElementById('session_slot').value = s;
});

// AJAX submit
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    var btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    fetch('<?php echo site_url("coe/coe_answer_scripts/save_upload"); ?>', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(function(res) {
        var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
        document.getElementById('upload-msg').innerHTML =
            '<div class="alert ' + cls + '">' + res.msg + '</div>';
        if (res.status === 'success') {
            document.getElementById('uploadForm').reset();
        }
    })
    .catch(function() {
        document.getElementById('upload-msg').innerHTML =
            '<div class="alert alert-danger">Server error. Please try again.</div>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save"></i> Register Script';
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'answer_scripts']); ?>
