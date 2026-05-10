<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-file-text-o"></i> Script Detail
            <small><?php echo htmlspecialchars($script->hall_ticket_no); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/listing/' . $script->exam_group_class_batch_exam_id); ?>">
                    <i class="fa fa-arrow-left"></i> Back to Listing
                </a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Script Information</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-condensed">
                            <tr><th>Hall Ticket</th><td><?php echo htmlspecialchars($script->hall_ticket_no); ?></td></tr>
                            <tr><th>Student</th><td><?php echo htmlspecialchars($script->student_name); ?></td></tr>
                            <tr><th>Subject</th><td><?php echo htmlspecialchars($script->subject_code . ' — ' . $script->subject_name); ?></td></tr>
                            <tr><th>Exam Date</th><td><?php echo $script->exam_date ? date('d M Y', strtotime($script->exam_date)) . ' / ' . $script->session_slot : '—'; ?></td></tr>
                            <tr><th>Barcode Token</th><td><code><?php echo htmlspecialchars($script->barcode_token ?? '—'); ?></code></td></tr>
                            <tr><th>Page Count</th><td><?php echo $script->page_count ?? '—'; ?></td></tr>
                            <tr><th>Scan Status</th>
                                <td>
                                    <?php
                                    $cls = ['pending' => 'label-warning', 'scanned' => 'label-info', 'uploaded' => 'label-success'];
                                    echo '<span class="label ' . ($cls[$script->scan_status] ?? 'label-default') . '">' . ucfirst($script->scan_status) . '</span>';
                                    ?>
                                </td>
                            </tr>
                            <tr><th>Uploaded By</th><td><?php echo htmlspecialchars($script->uploaded_by_name ?? '—'); ?></td></tr>
                            <tr><th>Uploaded At</th><td><?php echo $script->uploaded_at ? date('d M Y H:i', strtotime($script->uploaded_at)) : '—'; ?></td></tr>
                            <tr><th>Hall</th><td><?php echo htmlspecialchars($script->hall_name ?? '—'); ?></td></tr>
                            <tr><th>Remarks</th><td><?php echo htmlspecialchars($script->remarks ?? '—'); ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <?php if (!empty($script->scanned_filename)): ?>
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Scanned File</h3>
                    </div>
                    <div class="box-body text-center">
                        <p>
                            <a href="<?php echo base_url('uploads/answer_scripts/' . urlencode($script->scanned_filename)); ?>"
                               target="_blank" class="btn btn-default">
                                <i class="fa fa-file-pdf-o"></i> View / Download
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_edit')): ?>
        <div class="row">
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update Status</h3>
                    </div>
                    <div class="box-body">
                        <div id="status-msg"></div>
                        <form id="statusForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <div class="input-group">
                                <select name="scan_status" class="form-control">
                                    <option value="pending"  <?php echo $script->scan_status=='pending'  ? 'selected' : ''; ?>>Pending</option>
                                    <option value="scanned"  <?php echo $script->scan_status=='scanned'  ? 'selected' : ''; ?>>Scanned</option>
                                    <option value="uploaded" <?php echo $script->scan_status=='uploaded' ? 'selected' : ''; ?>>Uploaded</option>
                                </select>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var fd = new FormData(this);
            fetch('<?php echo site_url("coe/coe_answer_scripts/update_status/" . $script->id); ?>', {
                method: 'POST', body: fd
            })
            .then(r => r.json())
            .then(function(res) {
                var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
                document.getElementById('status-msg').innerHTML =
                    '<div class="alert ' + cls + '">' + res.msg + '</div>';
            });
        });
        </script>
        <?php endif; ?>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'answer_scripts']); ?>
