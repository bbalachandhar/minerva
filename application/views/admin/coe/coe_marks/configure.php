<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-cog"></i> Configure Subjects
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_marks/listing/' . $batch_exam_id); ?>">
                <i class="fa fa-arrow-left"></i> Back</a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div id="cfg-flash"></div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="box box-primary">
                    <div class="box-header with-border"><h3 class="box-title">Subject Configuration (Credits, Max Marks, Pass Marks)</h3></div>
                    <div class="box-body">
                        <?php if (empty($subjects)): ?>
                            <p class="text-muted text-center">No subjects linked to this exam event.</p>
                        <?php else: ?>
                        <form id="cfgForm">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                                   value="<?php echo $this->security->get_csrf_hash(); ?>">
                            <input type="hidden" name="batch_exam_id" value="<?php echo $batch_exam_id; ?>">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Credits</th>
                                        <th>Max Internal</th>
                                        <th>Max External</th>
                                        <th>Pass Internal</th>
                                        <th>Pass External</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $sub):
                                        $c = $configs_idx[$sub->id] ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?></td>
                                        <td>
                                            <input type="number" name="config[<?php echo $sub->id; ?>][credits]"
                                                   class="form-control input-sm" min="1" max="6" step="0.5"
                                                   value="<?php echo $c ? $c->credits : 4; ?>" style="width:70px">
                                        </td>
                                        <td>
                                            <input type="number" name="config[<?php echo $sub->id; ?>][max_internal]"
                                                   class="form-control input-sm" min="0" max="50" step="1"
                                                   value="<?php echo $c ? $c->max_internal : 30; ?>" style="width:70px">
                                        </td>
                                        <td>
                                            <input type="number" name="config[<?php echo $sub->id; ?>][max_external]"
                                                   class="form-control input-sm" min="0" max="100" step="1"
                                                   value="<?php echo $c ? $c->max_external : 70; ?>" style="width:70px">
                                        </td>
                                        <td>
                                            <input type="number" name="config[<?php echo $sub->id; ?>][pass_internal]"
                                                   class="form-control input-sm" min="0" max="50" step="1"
                                                   value="<?php echo $c ? $c->pass_internal : 12; ?>" style="width:70px">
                                        </td>
                                        <td>
                                            <input type="number" name="config[<?php echo $sub->id; ?>][pass_external]"
                                                   class="form-control input-sm" min="0" max="100" step="1"
                                                   value="<?php echo $c ? $c->pass_external : 28; ?>" style="width:70px">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <button type="button" id="btnSaveCfg" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Configuration
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

document.getElementById('btnSaveCfg') && document.getElementById('btnSaveCfg').addEventListener('click', function() {
    var fd = new FormData(document.getElementById('cfgForm'));
    fd.set(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_marks/save_config"); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(function(res) {
        var cls = res.status==='success' ? 'alert-success':'alert-danger';
        document.getElementById('cfg-flash').innerHTML='<div class="alert '+cls+'">'+res.msg+'</div>';
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'marks']); ?>
