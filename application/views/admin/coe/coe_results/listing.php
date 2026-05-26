<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bullhorn"></i> Result Publication
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_results'); ?>"><i class="fa fa-arrow-left"></i> Back</a></li>
        </ol>
    </section>
    <section class="content">
        <div id="pub-flash"></div>

        <!-- Publication Status Banner -->
        <div class="row">
            <div class="col-md-12">
                <?php if ($pub_status && $pub_status->is_published): ?>
                <div class="callout callout-success">
                    <h4><i class="fa fa-check-circle"></i> Results Published</h4>
                    <p>Published on <strong><?php echo $pub_status->published_at; ?></strong>.</p>
                    <button type="button" id="btnUnpublish" class="btn btn-warning">
                        <i class="fa fa-eye-slash"></i> Unpublish Results
                    </button>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_results/export/' . $batch_exam_id); ?>" class="btn btn-success">
                        <i class="fa fa-download"></i> Export CSV
                    </a>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_results/tabulation/' . $batch_exam_id); ?>" class="btn btn-primary">
                        <i class="fa fa-table"></i> Tabulation Sheet
                    </a>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_results/merit_list/' . $batch_exam_id); ?>" class="btn btn-info">
                        <i class="fa fa-trophy"></i> Merit List
                    </a>
                </div>
                <?php else: ?>
                <div class="callout callout-warning">
                    <h4><i class="fa fa-warning"></i> Results Not Yet Published</h4>
                    <p>Review the results below, then publish when ready.</p>
                    <button type="button" id="btnPublish" class="btn btn-success">
                        <i class="fa fa-bullhorn"></i> Publish Results
                    </button>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_results/export/' . $batch_exam_id); ?>" class="btn btn-success">
                        <i class="fa fa-download"></i> Export CSV
                    </a>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_results/tabulation/' . $batch_exam_id); ?>" class="btn btn-primary">
                        <i class="fa fa-table"></i> Tabulation Sheet
                    </a>
                    &nbsp;
                    <a href="<?php echo site_url('coe/coe_results/merit_list/' . $batch_exam_id); ?>" class="btn btn-info">
                        <i class="fa fa-trophy"></i> Merit List
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filters</h3>
                        <div class="box-tools"><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
                    </div>
                    <div class="box-body">
                        <form method="get" class="form-inline">
                            <div class="form-group" style="margin-right:10px">
                                <label>Subject&nbsp;</label>
                                <select name="subject_id" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>"
                                        <?php echo $this->input->get('subject_id') == $sub->id ? 'selected':''; ?>>
                                        <?php echo htmlspecialchars($sub->subject_code.' — '.$sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-right:10px">
                                <label>Status&nbsp;</label>
                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="pass" <?php echo $this->input->get('status')==='pass'?'selected':''; ?>>Pass</option>
                                    <option value="fail" <?php echo $this->input->get('status')==='fail'?'selected':''; ?>>Fail/Arrear</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Has Arrear&nbsp;</label>
                                <select name="has_arrear" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="1" <?php echo $this->input->get('has_arrear')==='1'?'selected':''; ?>>Yes</option>
                                    <option value="0" <?php echo $this->input->get('has_arrear')==='0'?'selected':''; ?>>No</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Results (<?php echo count($results); ?>)</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <?php if (empty($results)): ?>
                            <p class="text-muted text-center">No results found. Ensure marks are entered and SGPA computed.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-condensed table-hover">
                            <thead>
                                <tr>
                                    <th>#</th><th>Student</th><th>Adm.No</th><th>Subject</th>
                                    <th>Int</th><th>Ext</th><th>Mod</th><th>Total</th>
                                    <th>Grade</th><th>GP</th><th>Status</th>
                                    <th>SGPA</th><th>CGPA</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $i => $res): ?>
                                <tr class="<?php echo $res->result_status === 'fail' ? 'danger' : ''; ?>">
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($res->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($res->admission_no); ?></td>
                                    <td><?php echo htmlspecialchars($res->subject_code); ?></td>
                                    <td><?php echo number_format($res->internal_marks, 1); ?></td>
                                    <td><?php echo number_format($res->external_marks, 1); ?></td>
                                    <td><?php echo $res->moderation_applied ? '+'.number_format($res->moderation_applied,1):'—'; ?></td>
                                    <td><strong><?php echo number_format($res->total_marks, 1); ?></strong></td>
                                    <td><strong><?php echo $res->grade; ?></strong></td>
                                    <td><?php echo $res->grade_points; ?></td>
                                    <td>
                                        <span class="label <?php echo $res->result_status==='pass'?'label-success':'label-danger'; ?>">
                                            <?php echo ucfirst($res->result_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo isset($res->sgpa) && $res->sgpa ? number_format($res->sgpa, 2) : '—'; ?></td>
                                    <td><?php echo isset($res->cgpa) && $res->cgpa ? number_format($res->cgpa, 2) : '—'; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_results/student_result/'.$res->student_id.'/'.$batch_exam_id); ?>"
                                           class="btn btn-xs btn-info">
                                            <i class="fa fa-id-card"></i>
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

        <!-- SGPA Summary -->
        <?php if (!empty($sgpa_summary)): ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-info">
                    <div class="box-header with-border"><h3 class="box-title">SGPA/CGPA Summary</h3></div>
                    <div class="box-body">
                        <table class="table table-condensed table-bordered">
                            <thead><tr><th>Student</th><th>Adm.No</th><th>SGPA</th><th>CGPA</th><th>Credits</th><th>Arrear</th></tr></thead>
                            <tbody>
                                <?php foreach ($sgpa_summary as $sg): ?>
                                <tr class="<?php echo ($sg->arrear_count > 0) ? 'warning':''; ?>">
                                    <td><?php echo htmlspecialchars($sg->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($sg->admission_no); ?></td>
                                    <td><strong><?php echo number_format($sg->sgpa, 2); ?></strong></td>
                                    <td><?php echo number_format($sg->cgpa, 2); ?></td>
                                    <td><?php echo $sg->total_credits_earned; ?> / <?php echo $sg->total_credits_registered; ?></td>
                                    <td><?php echo ($sg->arrear_count > 0) ? '<span class="label label-danger">Yes</span>' : '<span class="label label-success">No</span>'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

<script>
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
var batchId  = <?php echo (int) $batch_exam_id; ?>;

function flash(msg, cls) {
    document.getElementById('pub-flash').innerHTML = '<div class="alert alert-' + cls + '">' + msg + '</div>';
}

var btnP = document.getElementById('btnPublish');
if (btnP) btnP.addEventListener('click', function() {
    if (!confirm('Publish results for all students? This will make results visible to students.')) return;
    var fd = new FormData(); fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_results/publish/"); ?>' + batchId, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        flash(res.msg, res.status==='success'?'success':'danger');
        if (res.status==='success') setTimeout(()=>location.reload(), 1500);
    });
});

var btnU = document.getElementById('btnUnpublish');
if (btnU) btnU.addEventListener('click', function() {
    if (!confirm('Unpublish results? Students will no longer be able to view them.')) return;
    var fd = new FormData(); fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_results/unpublish/"); ?>' + batchId, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        flash(res.msg, res.status==='success'?'success':'danger');
        if (res.status==='success') setTimeout(()=>location.reload(), 1500);
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'results']); ?>
