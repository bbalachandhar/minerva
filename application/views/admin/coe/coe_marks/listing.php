<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-graduation-cap"></i> Results
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_marks'); ?>">Marks</a></li>
            <li class="active"><?php echo htmlspecialchars($event->exam); ?></li>
        </ol>
    </section>
    <section class="content">
        <div id="marks-flash"></div>
        <div style="margin-bottom:10px">
            <a href="<?php echo site_url('coe/coe_marks'); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Exam List
            </a>
            <button class="btn btn-sm btn-primary" id="btnComputeSGPA" style="margin-left:6px">
                <i class="fa fa-calculator"></i> Compute SGPA/CGPA
            </button>
            <button class="btn btn-sm btn-default" id="btnRecomputeGrades" style="margin-left:4px">
                <i class="fa fa-refresh"></i> Recompute Grades
            </button>
        </div>

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
                                        <?php echo $this->input->get('subject_id') == $sub->id ? 'selected':''; ?>>
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status&nbsp;</label>
                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="pass" <?php echo $this->input->get('status')==='pass'?'selected':''; ?>>Pass</option>
                                    <option value="fail" <?php echo $this->input->get('status')==='fail'?'selected':''; ?>>Fail</option>
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
                    <div class="box-body">
                        <?php if (empty($results)): ?>
                            <p class="text-muted text-center">No results found.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th><th>Student</th><th>Adm.No</th><th>Subject</th>
                                    <th>Internal</th><th>External</th><th>Total</th>
                                    <th>Mod</th><th>Grade</th><th>GP</th><th>Status</th><th>Action</th>
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
                                    <td><strong><?php echo number_format($res->total_marks, 1); ?></strong></td>
                                    <td><?php echo $res->moderation_applied ? '+'.number_format($res->moderation_applied,1):'—'; ?></td>
                                    <td><strong><?php echo $res->grade; ?></strong></td>
                                    <td><?php echo $res->grade_points; ?></td>
                                    <td>
                                        <span class="label <?php echo $res->result_status==='pass'?'label-success':'label-danger'; ?>">
                                            <?php echo ucfirst($res->result_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-info btn-student-card"
                                                data-sid="<?php echo $res->student_id; ?>"
                                                data-sname="<?php echo htmlspecialchars($res->student_name, ENT_QUOTES); ?>"
                                                title="Student Card">
                                            <i class="fa fa-id-card"></i>
                                        </button>
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
                    <div class="box-header with-border"><h3 class="box-title">SGPA / CGPA Summary</h3></div>
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
                                    <td>
                                        <?php if ($sg->arrear_count > 0): ?>
                                            <span class="label label-danger">Yes</span>
                                        <?php else: ?>
                                            <span class="label label-success">No</span>
                                        <?php endif; ?>
                                    </td>
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

<!-- Student Card Modal -->
<div class="modal fade" id="studentCardModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-id-card"></i> <span id="scModalStudentName">Student Result Card</span></h4>
                <small class="text-muted" id="scModalExam"><?php echo htmlspecialchars($event->exam_group_name . ' — ' . $event->exam); ?></small>
            </div>
            <div class="modal-body" id="scModalBody" style="min-height:120px">
                <div class="text-center" style="padding:30px">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
var batchId  = <?php echo (int) $batch_exam_id; ?>;

function flash(msg, cls) {
    document.getElementById('marks-flash').innerHTML = '<div class="alert alert-' + cls + '">' + msg + '</div>';
}

document.getElementById('btnComputeSGPA').addEventListener('click', function() {
    if (!confirm('Compute SGPA/CGPA for all students in this exam event?')) return;
    var fd = new FormData(); fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_marks/compute_sgpa/"); ?>' + batchId, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        flash(res.msg, res.status==='success'?'success':'danger');
        if (res.status==='success') setTimeout(()=>location.reload(), 1500);
    });
});

document.getElementById('btnRecomputeGrades').addEventListener('click', function() {
    var fd = new FormData(); fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_marks/recompute_grades/"); ?>' + batchId, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        flash(res.msg, res.status==='success'?'success':'danger');
        if (res.status==='success') setTimeout(()=>location.reload(), 1200);
    });
});

// Student card modal
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-student-card');
    if (!btn) return;
    var sid   = btn.dataset.sid;
    var sname = btn.dataset.sname;
    document.getElementById('scModalStudentName').textContent = sname;
    document.getElementById('scModalBody').innerHTML = '<div class="text-center" style="padding:30px"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>';
    $('#studentCardModal').modal('show');
    fetch('<?php echo site_url("coe/coe_marks/student_card_ajax/"); ?>' + sid + '/' + batchId)
        .then(function(r) { return r.text(); })
        .then(function(html) { document.getElementById('scModalBody').innerHTML = html; })
        .catch(function() { document.getElementById('scModalBody').innerHTML = '<p class="text-danger">Failed to load card.</p>'; });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'marks']); ?>
