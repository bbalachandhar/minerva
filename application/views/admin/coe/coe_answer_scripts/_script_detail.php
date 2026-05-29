<?php
$sc    = $script;
$sid   = (int) $sc->id;
$sclass = ['pending' => 'label-warning', 'scanned' => 'label-info', 'uploaded' => 'label-success'];
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<div class="row">
    <!-- Left: Script Info -->
    <div class="col-sm-7">
        <table class="table table-condensed table-bordered" style="font-size:13px;">
            <tr>
                <th style="width:38%;background:#f9f9f9;">Hall Ticket</th>
                <td><strong><?php echo htmlspecialchars($sc->hall_ticket_no); ?></strong></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Student</th>
                <td><?php echo htmlspecialchars($sc->student_name); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Subject</th>
                <td><?php echo htmlspecialchars($sc->subject_code . ' — ' . $sc->subject_name); ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Exam Date</th>
                <td><?php echo $sc->exam_date ? date('d M Y', strtotime($sc->exam_date)) . ' &nbsp;<span class="label label-default">' . htmlspecialchars($sc->session_slot) . '</span>' : '—'; ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Barcode Token</th>
                <td><code><?php echo htmlspecialchars($sc->barcode_token ?? '—'); ?></code></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Page Count</th>
                <td><?php echo $sc->page_count ? (int)$sc->page_count : '—'; ?></td>
            </tr>
            <tr>
                <th style="background:#f9f9f9;">Status</th>
                <td>
                    <span id="sd-status-badge-<?php echo $sid; ?>" class="label <?php echo $sclass[$sc->scan_status] ?? 'label-default'; ?>">
                        <?php echo ucfirst($sc->scan_status); ?>
                    </span>
                </td>
            </tr>
            <?php if (!empty($sc->uploaded_by_name)): ?>
            <tr>
                <th style="background:#f9f9f9;">Registered By</th>
                <td><?php echo htmlspecialchars($sc->uploaded_by_name); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($sc->remarks)): ?>
            <tr>
                <th style="background:#f9f9f9;">Remarks</th>
                <td><?php echo htmlspecialchars($sc->remarks); ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($sc->scanned_filename)): ?>
            <tr>
                <th style="background:#f9f9f9;">Scanned File</th>
                <td>
                    <a href="<?php echo base_url('uploads/answer_scripts/' . urlencode($sc->scanned_filename)); ?>"
                       target="_blank" class="btn btn-xs btn-default">
                        <i class="fa fa-file-pdf-o"></i> View / Download
                    </a>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Right: Status Update + File Upload -->
    <div class="col-sm-5">
        <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_edit')): ?>
        <div class="box box-warning" style="margin-bottom:10px;">
            <div class="box-header with-border" style="padding:8px 12px;">
                <h4 class="box-title" style="font-size:13px;"><i class="fa fa-pencil"></i> Update Status</h4>
            </div>
            <div class="box-body" style="padding:10px;">
                <div id="sd-status-msg-<?php echo $sid; ?>"></div>
                <form id="sdStatusForm<?php echo $sid; ?>">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>">
                    <div class="input-group input-group-sm">
                        <select name="scan_status" id="sd-status-sel-<?php echo $sid; ?>" class="form-control">
                            <option value="pending"  <?php echo $sc->scan_status === 'pending'  ? 'selected' : ''; ?>>Pending</option>
                            <option value="scanned"  <?php echo $sc->scan_status === 'scanned'  ? 'selected' : ''; ?>>Scanned</option>
                            <option value="uploaded" <?php echo $sc->scan_status === 'uploaded' ? 'selected' : ''; ?>>Uploaded</option>
                        </select>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-sm btn-warning">Save</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')): ?>
        <div class="box box-primary" style="margin-bottom:0;">
            <div class="box-header with-border" style="padding:8px 12px;">
                <h4 class="box-title" style="font-size:13px;"><i class="fa fa-upload"></i> Upload Scanned File</h4>
            </div>
            <div class="box-body" style="padding:10px;">
                <div id="sd-upload-msg-<?php echo $sid; ?>"></div>
                <form id="sdUploadForm<?php echo $sid; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>">
                    <div class="form-group" style="margin-bottom:8px;">
                        <label style="font-size:12px;">File <span class="text-muted">(PDF / JPG / PNG, max 20 MB)</span></label>
                        <input type="file" name="script_file" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="form-group" style="margin-bottom:8px;">
                        <label style="font-size:12px;">Page Count <span class="text-muted">(optional)</span></label>
                        <input type="number" name="page_count" class="form-control input-sm" min="1" max="500"
                               value="<?php echo ($sc->page_count > 0) ? (int)$sc->page_count : ''; ?>">
                    </div>
                    <button type="submit" id="sd-upload-btn-<?php echo $sid; ?>" class="btn btn-sm btn-primary">
                        <i class="fa fa-upload"></i> <?php echo !empty($sc->scanned_filename) ? 'Replace File' : 'Upload'; ?>
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function(){
    var id      = <?php echo $sid; ?>;
    var baseUrl = '<?php echo site_url(); ?>';
    var badgeCls = { pending: 'label-warning', scanned: 'label-info', uploaded: 'label-success' };

    // --- Status update ---
    var statusForm = document.getElementById('sdStatusForm' + id);
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var fd  = new FormData(this);
            var btn = this.querySelector('button[type=submit]');
            btn.disabled = true;
            fetch(baseUrl + 'coe/coe_answer_scripts/update_status/' + id, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
                    document.getElementById('sd-status-msg-' + id).innerHTML =
                        '<div class="alert ' + cls + ' alert-sm" style="padding:5px 10px;margin-bottom:6px;">' + res.msg + '</div>';
                    if (res.status === 'success') {
                        var newVal = document.getElementById('sd-status-sel-' + id).value;
                        var badge  = document.getElementById('sd-status-badge-' + id);
                        if (badge) {
                            badge.className = 'label ' + (badgeCls[newVal] || 'label-default');
                            badge.textContent = newVal.charAt(0).toUpperCase() + newVal.slice(1);
                        }
                    }
                })
                .catch(function() { btn.disabled = false; });
        });
    }

    // --- File upload ---
    var uploadForm = document.getElementById('sdUploadForm' + id);
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var fd  = new FormData(this);
            var btn = document.getElementById('sd-upload-btn-' + id);
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            fetch(baseUrl + 'coe/coe_answer_scripts/upload_file/' + id, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-upload"></i> Replace File';
                    var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
                    document.getElementById('sd-upload-msg-' + id).innerHTML =
                        '<div class="alert ' + cls + ' alert-sm" style="padding:5px 10px;margin-bottom:6px;">' + res.msg + '</div>';
                    if (res.status === 'success') {
                        // update status badge to uploaded
                        var badge = document.getElementById('sd-status-badge-' + id);
                        if (badge) { badge.className = 'label label-success'; badge.textContent = 'Uploaded'; }
                        var sel = document.getElementById('sd-status-sel-' + id);
                        if (sel) sel.value = 'uploaded';
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-upload"></i> Upload';
                    document.getElementById('sd-upload-msg-' + id).innerHTML =
                        '<div class="alert alert-danger" style="padding:5px 10px;">Server error.</div>';
                });
        });
    }
})();
</script>
