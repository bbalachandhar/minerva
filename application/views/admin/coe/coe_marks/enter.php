<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-pencil"></i> Enter Marks
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_marks/listing/' . $batch_exam_id); ?>">
                <i class="fa fa-arrow-left"></i> Back to Results</a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div id="enter-flash"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Marks Entry Matrix</h3>
                        <div class="box-tools pull-right">
                            <button type="button" id="btnSaveAllMarks" class="btn btn-sm btn-primary">
                                <i class="fa fa-save"></i> Save All
                            </button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <?php if (empty($students) || empty($subjects)): ?>
                            <p class="text-muted text-center">No students or subjects configured. Please configure subjects first.</p>
                        <?php else: ?>

                        <!-- Index configs by subject_id -->
                        <?php
                        $cfgs = [];
                        foreach ($configs as $c) {
                            $cfgs[$c->subject_id] = $c;
                        }
                        ?>

                        <form id="marksEntryForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <input type="hidden" name="batch_exam_id" value="<?php echo $batch_exam_id; ?>">

                            <table class="table table-bordered table-condensed" style="min-width:900px">
                                <thead>
                                    <tr>
                                        <th rowspan="2">#</th>
                                        <th rowspan="2">Student</th>
                                        <th rowspan="2">Adm.No</th>
                                        <?php foreach ($subjects as $sub): ?>
                                        <th colspan="2" class="text-center">
                                            <?php echo htmlspecialchars($sub->subject_code); ?><br>
                                            <small>Int / Ext</small>
                                        </th>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <?php foreach ($subjects as $sub): ?>
                                        <?php $cfg = $cfgs[$sub->id] ?? null; ?>
                                        <th class="text-center" style="font-size:10px">/<?php echo $cfg ? (int)$cfg->max_internal : 30; ?></th>
                                        <th class="text-center" style="font-size:10px">/<?php echo $cfg ? (int)$cfg->max_external : 70; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $idx => $st): ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><?php echo htmlspecialchars($st->full_name); ?></td>
                                        <td><?php echo htmlspecialchars($st->admission_no); ?></td>
                                        <?php foreach ($subjects as $sub):
                                            $existing = $results_idx[$st->id][$sub->id] ?? null;
                                            $cfg = $cfgs[$sub->id] ?? null;
                                            $max_int = $cfg ? (float)$cfg->max_internal : 30;
                                            $max_ext = $cfg ? (float)$cfg->max_external : 70;
                                        ?>
                                        <td>
                                            <input type="number"
                                                   name="marks[<?php echo $st->id; ?>][<?php echo $sub->id; ?>][internal]"
                                                   class="form-control input-sm"
                                                   min="0" max="<?php echo $max_int; ?>" step="0.5"
                                                   value="<?php echo $existing ? $existing->internal_marks : ''; ?>"
                                                   style="width:60px;padding:2px 4px">
                                        </td>
                                        <td>
                                            <input type="number"
                                                   name="marks[<?php echo $st->id; ?>][<?php echo $sub->id; ?>][external]"
                                                   class="form-control input-sm"
                                                   min="0" max="<?php echo $max_ext; ?>" step="0.5"
                                                   value="<?php echo $existing ? $existing->external_marks : ''; ?>"
                                                   style="width:60px;padding:2px 4px">
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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

document.getElementById('btnSaveAllMarks') && document.getElementById('btnSaveAllMarks').addEventListener('click', function() {
    var fd = new FormData(document.getElementById('marksEntryForm'));
    fd.set(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_marks/save_marks"); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        var cls = res.status==='success' ? 'alert-success':'alert-danger';
        document.getElementById('enter-flash').innerHTML='<div class="alert '+cls+'">'+res.msg+'</div>';
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'marks']); ?>
