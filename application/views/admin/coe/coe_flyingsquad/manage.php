<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-4 col-sm-6">
        <?php foreach (['none' => 'default', 'minor' => 'warning', 'major' => 'danger'] as $sev => $cls): ?>
        <?php $cnt = 0; foreach ($severity_summary as $s) { if ($s->severity === $sev) $cnt = (int) $s->cnt; } ?>
        <div class="info-box bg-<?= $cls ?>">
            <span class="info-box-icon"><i class="fa fa-<?= $sev === 'none' ? 'check' : 'exclamation-triangle' ?>"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?= ucfirst($sev) ?> Severity Visits</span>
                <span class="info-box-number"><?= $cnt ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="col-md-8">

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-shield"></i> Flying Squad Visits — <?= htmlspecialchars($event->exam_group_name ?? $event->exam ?? '') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('coe/coe_flyingsquad') ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back</a>
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addVisitModal">
                <i class="fa fa-plus"></i> Add Visit
            </button>
        </div>
    </div>
    <div class="box-body">
        <div id="fsq-msg"></div>
        <?php if (empty($visits)): ?>
            <p class="text-muted">No visits recorded yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" style="font-size:12px">
                <thead><tr>
                    <th>#</th>
                    <th>Date / Time</th>
                    <th>Observer</th>
                    <th>Hall</th>
                    <th>Observations</th>
                    <th>Irregularities</th>
                    <th>Severity</th>
                    <th>Action Taken</th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php $i = 1; foreach ($visits as $v): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= date('d M Y', strtotime($v->visit_date)) ?><br><small><?= $v->visit_time ?></small></td>
                    <td><?= htmlspecialchars($v->observer_name ?? '—') ?><br><small><?= htmlspecialchars($v->designation ?? '') ?></small></td>
                    <td><?= htmlspecialchars($v->hall_name ?? '—') ?></td>
                    <td><?= nl2br(htmlspecialchars($v->observations ?? '')) ?></td>
                    <td>
                        <?php if ($v->irregularities_found): ?>
                            <span class="label label-warning">Yes</span><br>
                            <small><?= htmlspecialchars($v->irregularity_details ?? '') ?></small>
                        <?php else: ?>
                            <span class="label label-success">None</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $sev_cls = ['none'=>'success','minor'=>'warning','major'=>'danger'][$v->severity] ?? 'default'; ?>
                        <span class="label label-<?= $sev_cls ?>"><?= ucfirst($v->severity) ?></span>
                    </td>
                    <td><?= htmlspecialchars($v->action_taken ?? '—') ?></td>
                    <td>
                        <button class="btn btn-xs btn-danger" onclick="deleteVisit(<?= $v->id ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

    </div><!-- col -->
</div><!-- row -->

<!-- Add Visit Modal -->
<div class="modal fade" id="addVisitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h4><i class="fa fa-shield"></i> Record Flying Squad Visit</h4></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Observer (Staff)</label>
                            <select class="form-control" id="fs_observer">
                                <option value="">— Select —</option>
                                <?php foreach ($staff as $s): ?>
                                <option value="<?= $s->id ?>"><?= htmlspecialchars($s->name) ?> (<?= htmlspecialchars($s->designation ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Visit Date</label>
                            <input type="date" class="form-control" id="fs_visit_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Visit Time</label>
                            <input type="time" class="form-control" id="fs_visit_time" value="<?= date('H:i') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hall Name</label>
                            <input type="text" class="form-control" id="fs_hall_name" placeholder="Hall / Room name">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Irregularities Found?</label>
                            <select class="form-control" id="fs_irregularities">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Severity</label>
                            <select class="form-control" id="fs_severity">
                                <option value="none">None</option>
                                <option value="minor">Minor</option>
                                <option value="major">Major</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Observations</label>
                    <textarea class="form-control" id="fs_observations" rows="3" placeholder="Describe what was observed during the visit..."></textarea>
                </div>
                <div class="form-group" id="irregularity_details_wrap" style="display:none">
                    <label>Irregularity Details</label>
                    <textarea class="form-control" id="fs_irreg_details" rows="2" placeholder="Describe irregularities observed..."></textarea>
                </div>
                <div class="form-group">
                    <label>Action Taken</label>
                    <input type="text" class="form-control" id="fs_action_taken" placeholder="Actions taken / Follow-up">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveVisit()"><i class="fa fa-save"></i> Save Visit</button>
            </div>
        </div>
    </div>
</div>

<script>
var BATCH_EXAM_ID = <?= (int) $batch_exam_id ?>;
var BASE_URL = '<?= site_url('coe/coe_flyingsquad') ?>';
var CSRF_NAME = '<?= $this->security->get_csrf_token_name() ?>';
var CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';

$('#fs_irregularities').on('change', function() {
    $('#irregularity_details_wrap').toggle($(this).val() === '1');
    if ($(this).val() === '1' && $('#fs_severity').val() === 'none') {
        $('#fs_severity').val('minor');
    }
});

function saveVisit() {
    var postData = {
        batch_exam_id:     BATCH_EXAM_ID,
        observer_staff_id: $('#fs_observer').val(),
        visit_date:        $('#fs_visit_date').val(),
        visit_time:        $('#fs_visit_time').val(),
        hall_name:         $('#fs_hall_name').val(),
        observations:      $('#fs_observations').val(),
        irregularities_found: $('#fs_irregularities').val(),
        irregularity_details: $('#fs_irreg_details').val(),
        action_taken:      $('#fs_action_taken').val(),
        severity:          $('#fs_severity').val(),
    };
    postData[CSRF_NAME] = CSRF_HASH;

    $.ajax({
        url: BASE_URL + '/add',
        method: 'POST',
        data: postData,
        dataType: 'json',
        success: function(res) {
            var cls = res.status === 'success' ? 'alert-success' : 'alert-danger';
            $('#fsq-msg').html('<div class="alert ' + cls + '">' + res.msg + '</div>');
            if (res.status === 'success') {
                $('#addVisitModal').modal('hide');
                setTimeout(function() { location.reload(); }, 1200);
            }
        }
    });
}

function deleteVisit(id) {
    swal({
        title: 'Delete this visit?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete'
    }, function(c) {
        if (!c) return;
        var d = {}; d[CSRF_NAME] = CSRF_HASH;
        $.ajax({
            url: BASE_URL + '/delete/' + id,
            method: 'POST',
            data: d,
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') location.reload();
                else alert(res.msg);
            }
        });
    });
}
</script>
