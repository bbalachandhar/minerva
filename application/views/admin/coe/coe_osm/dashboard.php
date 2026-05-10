<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-pencil-square-o"></i> OSM Dashboard
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_osm'); ?>"><i class="fa fa-arrow-left"></i> Back</a></li>
            <?php if ($this->rbac->hasPrivilege('coe_osm', 'can_add')): ?>
            <li>
                <button class="btn btn-xs btn-info" id="btnCreateOsm" data-batch="<?php echo $batch_exam_id; ?>">
                    <i class="fa fa-magic"></i> Create OSM Records from Uploaded Scripts
                </button>
            </li>
            <?php endif; ?>
        </ol>
    </section>
    <section class="content">
        <div id="osm-flash"></div>

        <!-- Stats -->
        <div class="row">
            <?php
            $stat_cfg = [
                'pending'  => ['bg-yellow', 'fa-clock-o',     'Pending'],
                'assigned' => ['bg-blue',   'fa-user',         'Assigned'],
                'marking'  => ['bg-orange', 'fa-pencil',       'In Progress'],
                'done'     => ['bg-green',  'fa-check',        'Done'],
                'locked'   => ['bg-black',  'fa-lock',         'Locked'],
            ];
            foreach ($stat_cfg as $key => [$bg, $icon, $label]):
            ?>
            <div class="col-md-2 col-sm-4">
                <div class="info-box <?php echo $bg; ?>">
                    <span class="info-box-icon"><i class="fa <?php echo $icon; ?>"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $label; ?></span>
                        <span class="info-box-number"><?php echo $counts[$key]; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <form method="get" class="form-inline">
                            <div class="form-group" style="margin-right:10px">
                                <label>Subject&nbsp;</label>
                                <select name="subject_id" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All Subjects</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>"
                                        <?php echo $this->input->get('subject_id') == $sub->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status&nbsp;</label>
                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach (['pending','assigned','marking','done','locked'] as $st): ?>
                                    <option value="<?php echo $st; ?>"
                                        <?php echo $this->input->get('status')===$st ? 'selected':''; ?>>
                                        <?php echo ucfirst($st); ?>
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
                        <h3 class="box-title">OSM Scripts (<?php echo count($osm_scripts); ?>)</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($osm_scripts)): ?>
                            <p class="text-muted text-center">No OSM scripts found. Upload answer scripts first, then click "Create OSM Records".</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Barcode</th>
                                    <th>Hall Ticket</th>
                                    <th>Subject</th>
                                    <th>Stage</th>
                                    <th>Evaluator</th>
                                    <th>Total Marks</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $status_cls = [
                                    'pending'  => 'label-warning',
                                    'assigned' => 'label-info',
                                    'marking'  => 'label-primary',
                                    'done'     => 'label-success',
                                    'locked'   => 'label-default',
                                ];
                                foreach ($osm_scripts as $i => $os):
                                ?>
                                <tr id="osm-row-<?php echo $os->id; ?>">
                                    <td><?php echo $i + 1; ?></td>
                                    <td><code><?php echo htmlspecialchars($os->barcode_token ?? '—'); ?></code></td>
                                    <td><?php echo htmlspecialchars($os->hall_ticket_no ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($os->subject_code . ' ' . $os->subject_name); ?></td>
                                    <td><span class="badge"><?php echo $os->stage; ?></span></td>
                                    <td>
                                        <?php if ($os->evaluator_name): ?>
                                            <?php echo htmlspecialchars($os->evaluator_name); ?>
                                        <?php else: ?>
                                            <em class="text-muted">Unassigned</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $os->total_marks !== null ? number_format($os->total_marks, 1) : '—'; ?></td>
                                    <td>
                                        <span class="label <?php echo $status_cls[$os->status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($os->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($this->rbac->hasPrivilege('coe_osm', 'can_edit') && !in_array($os->status, ['locked'])): ?>
                                        <a href="<?php echo site_url('coe/coe_osm/mark/' . $os->id); ?>"
                                           class="btn btn-xs btn-primary" title="Mark">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <?php if ($os->status === 'done'): ?>
                                        <button class="btn btn-xs btn-default btn-lock"
                                                data-id="<?php echo $os->id; ?>" title="Lock">
                                            <i class="fa fa-lock"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
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

<script>
var csrfName  = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash  = '<?php echo $this->security->get_csrf_hash(); ?>';

// Create OSM records
document.getElementById('btnCreateOsm') && document.getElementById('btnCreateOsm').addEventListener('click', function() {
    var batchId = this.dataset.batch;
    if (!confirm('Create OSM records for all newly uploaded scripts?')) return;
    var fd = new FormData();
    fd.append(csrfName, csrfHash);
    fetch('<?php echo site_url("coe/coe_osm/create_from_scripts/"); ?>' + batchId, {method:'POST',body:fd})
    .then(r=>r.json()).then(function(res){
        var cls = res.status==='success' ? 'alert-success':'alert-danger';
        document.getElementById('osm-flash').innerHTML='<div class="alert '+cls+'">'+res.msg+'</div>';
        if(res.status==='success') setTimeout(function(){location.reload();},1500);
    });
});

// Lock buttons
document.querySelectorAll('.btn-lock').forEach(function(btn){
    btn.addEventListener('click', function(){
        if(!confirm('Lock this script? Marks cannot be changed after locking.')) return;
        var id = this.dataset.id;
        var fd = new FormData(); fd.append(csrfName, csrfHash);
        fetch('<?php echo site_url("coe/coe_osm/lock/"); ?>'+id, {method:'POST',body:fd})
        .then(r=>r.json()).then(function(res){
            document.getElementById('osm-flash').innerHTML=
                '<div class="alert '+(res.status==='success'?'alert-success':'alert-danger')+'">'+res.msg+'</div>';
            if(res.status==='success') setTimeout(function(){location.reload();},1000);
        });
    });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'osm']); ?>
