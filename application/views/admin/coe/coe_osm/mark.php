<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-pencil"></i> Mark Script
            <small>Barcode: <?php echo htmlspecialchars($script->barcode_token ?? $script->id); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_osm/dashboard/' . $script->exam_group_class_batch_exam_id); ?>">OSM Dashboard</a></li>
            <li class="active">Mark Script</li>
        </ol>
    </section>
    <section class="content">
        <a href="<?php echo site_url('coe/coe_osm/dashboard/' . $script->exam_group_class_batch_exam_id); ?>"
           class="btn btn-default btn-sm" style="margin-bottom:12px;">
            <i class="fa fa-arrow-left"></i> Back to OSM Dashboard
        </a>

        <div id="mark-flash"></div>

        <div class="row">
            <!-- Script info -->
            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Script Info</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-condensed">
                            <tr><th>Barcode</th><td><code><?php echo htmlspecialchars($script->barcode_token ?? '—'); ?></code></td></tr>
                            <tr><th>Subject</th><td><?php echo htmlspecialchars($script->subject_code . ' ' . $script->subject_name); ?></td></tr>
                            <tr><th>Exam Date</th><td><?php echo $script->exam_date ? date('d M Y', strtotime($script->exam_date)) . ' / ' . $script->session_slot : '—'; ?></td></tr>
                            <tr><th>Stage</th><td><?php echo $script->stage; ?></td></tr>
                            <tr><th>Status</th><td><?php echo ucfirst($script->status); ?></td></tr>
                            <tr><th>Current Total</th><td><strong id="current-total"><?php echo $script->total_marks !== null ? number_format($script->total_marks, 1) : '—'; ?></strong></td></tr>
                        </table>
                        <?php if (!empty($script->scanned_filename)): ?>
                        <p>
                            <a href="<?php echo base_url('uploads/answer_scripts/' . urlencode($script->scanned_filename)); ?>"
                               target="_blank" class="btn btn-block btn-default">
                                <i class="fa fa-file-pdf-o"></i> Open Scanned Script
                            </a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Marks entry -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Question-wise Marks</h3>
                    </div>
                    <div class="box-body">
                        <form id="marksForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">

                            <?php
                            // Build existing marks index: [qno][sub] => row
                            $marks_idx = [];
                            foreach ($existing_marks as $m) {
                                $sub = $m->sub_question ?? '_';
                                $marks_idx[$m->question_no][$sub] = $m;
                            }

                            // Default Anna University End-Sem: Q1-Q10 Part A (2 marks each),
                            // Q11-Q15 Part B (16 marks each, choose 1 from 2 parts: a/b)
                            $part_a_qs  = range(1, 10);
                            $part_b_qs  = range(11, 15);
                            ?>

                            <h4>Part A — Short Answers (2 marks each)</h4>
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>Q.No</th><th>Max</th><th>Marks Awarded</th></tr></thead>
                                <tbody>
                                    <?php foreach ($part_a_qs as $qno): ?>
                                    <?php $ex = $marks_idx[$qno]['_'] ?? null; ?>
                                    <tr>
                                        <td><?php echo $qno; ?></td>
                                        <td>
                                            <input type="hidden" name="max_marks[<?php echo $qno; ?>][_]" value="2">
                                            2
                                        </td>
                                        <td>
                                            <input type="number" name="marks[<?php echo $qno; ?>][_]"
                                                   class="form-control input-sm mark-field"
                                                   min="0" max="2" step="0.5"
                                                   value="<?php echo $ex ? $ex->marks_awarded : 0; ?>"
                                                   style="width:80px">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <h4>Part B — Either/Or (16 marks each)</h4>
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>Q.No</th><th>Part</th><th>Max</th><th>Marks Awarded</th></tr></thead>
                                <tbody>
                                    <?php foreach ($part_b_qs as $qno): ?>
                                    <?php foreach (['a','b'] as $sub): ?>
                                    <?php $ex = $marks_idx[$qno][$sub] ?? null; ?>
                                    <tr>
                                        <?php if ($sub === 'a'): ?>
                                        <td rowspan="2"><?php echo $qno; ?></td>
                                        <?php endif; ?>
                                        <td><?php echo strtoupper($sub); ?></td>
                                        <td>
                                            <input type="hidden" name="max_marks[<?php echo $qno; ?>][<?php echo $sub; ?>]" value="16">
                                            16
                                        </td>
                                        <td>
                                            <input type="number" name="marks[<?php echo $qno; ?>][<?php echo $sub; ?>]"
                                                   class="form-control input-sm mark-field"
                                                   min="0" max="16" step="0.5"
                                                   value="<?php echo $ex ? $ex->marks_awarded : 0; ?>"
                                                   style="width:80px">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="well">
                                Running Total: <strong id="running-total">0</strong> / 100
                            </div>

                            <button type="button" id="btnSave" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Marks
                            </button>
                            <button type="button" id="btnSubmit" class="btn btn-success">
                                <i class="fa fa-check"></i> Submit (Mark as Done)
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
var osmId    = <?php echo (int) $script->id; ?>;

function computeRunning() {
    // Part A total
    var total = 0;
    document.querySelectorAll('.mark-field').forEach(function(inp) {
        total += parseFloat(inp.value) || 0;
    });
    document.getElementById('running-total').textContent = total.toFixed(1);
}

document.querySelectorAll('.mark-field').forEach(function(inp) {
    inp.addEventListener('input', computeRunning);
});
computeRunning();

function postMarks(url, successMsg) {
    var fd = new FormData(document.getElementById('marksForm'));
    fd.set(csrfName, csrfHash);
    fetch(url, {method:'POST', body:fd})
    .then(r=>r.json())
    .then(function(res) {
        var cls = res.status==='success' ? 'alert-success':'alert-danger';
        document.getElementById('mark-flash').innerHTML='<div class="alert '+cls+'">'+res.msg+'</div>';
        if (res.status==='success') {
            document.getElementById('current-total').textContent = res.msg.replace(/[^0-9.]/g,'').split(' ').pop() || '—';
        }
    });
}

document.getElementById('btnSave').addEventListener('click', function() {
    postMarks('<?php echo site_url("coe/coe_osm/save_marks/"); ?>' + osmId);
});

document.getElementById('btnSubmit').addEventListener('click', function() {
    if (!confirm('Submit marking as complete? You can still edit until the script is locked.')) return;
    var fd = new FormData(); fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_osm/submit/"); ?>' + osmId, {method:'POST',body:fd})
    .then(r=>r.json()).then(function(res) {
        var cls = res.status==='success' ? 'alert-success':'alert-danger';
        document.getElementById('mark-flash').innerHTML='<div class="alert '+cls+'">'+res.msg+'</div>';
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'osm']); ?>
