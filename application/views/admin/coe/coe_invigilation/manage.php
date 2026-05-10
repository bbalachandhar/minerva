<?php defined('BASEPATH') or exit('No direct script access allowed');
$duty_labels = [
    'chief_superintendent' => ['label' => 'Chief Superintendent', 'class' => 'label-danger'],
    'invigilator'          => ['label' => 'Invigilator',          'class' => 'label-primary'],
    'deputy'               => ['label' => 'Deputy',               'class' => 'label-warning'],
    'flying_squad'         => ['label' => 'Flying Squad',         'class' => 'label-info'],
];

// Group duties by room for display
$duties_by_room = [];
foreach ($duties as $d) {
    $key = $d->seating_room_id ?? 'other';
    $duties_by_room[$key][] = $d;
}
?>
<style>
.coe-stat-card { border-radius:12px;color:#fff;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 15px rgba(0,0,0,.15);margin-bottom:18px; }
.coe-stat-card .stat-icon { font-size:2.1rem;opacity:.85;flex-shrink:0; }
.coe-stat-card .stat-body .stat-num { font-size:1.8rem;font-weight:700;line-height:1; }
.coe-stat-card .stat-body .stat-lbl { font-size:.83rem;opacity:.9;margin-top:2px; }
.card-indigo { background:linear-gradient(135deg,#283593,#3949ab); }
.card-green  { background:linear-gradient(135deg,#2e7d32,#388e3c); }
.card-orange { background:linear-gradient(135deg,#e65100,#f57c00); }
.card-teal   { background:linear-gradient(135deg,#00796b,#009688); }
.room-section { border:1px solid #e0e0e0;border-radius:8px;margin-bottom:16px;overflow:hidden; }
.room-section .rs-header { padding:10px 16px;background:linear-gradient(135deg,#37474f,#546e7a);color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px; }
.room-section .rs-body { padding:12px 16px; }
.duty-row { display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #f5f5f5; }
.duty-row:last-child { border-bottom:none; }
.fn-badge { background:#e3f2fd;color:#1565c0;border:1px solid #90caf9;border-radius:10px;padding:2px 7px;font-size:.77rem;font-weight:600; }
.an-badge { background:#ede7f6;color:#4527a0;border:1px solid #b39ddb;border-radius:10px;padding:2px 7px;font-size:.77rem;font-weight:600; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_invigilation'); ?> <small><?php echo htmlspecialchars($batch_exam->exam); ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_invigilation'); ?>">Invigilation</a></li>
      <li class="active">Manage Duties</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Stat Cards -->
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-teal">
          <div class="stat-icon"><i class="fa fa-building"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['rooms']; ?></div><div class="stat-lbl">Exam Rooms</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-indigo">
          <div class="stat-icon"><i class="fa fa-user-circle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['total_duties']; ?></div><div class="stat-lbl">Duties Assigned</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['rooms_with_duties']; ?></div><div class="stat-lbl">Rooms Covered</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-orange">
          <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['rooms_unassigned']; ?></div><div class="stat-lbl">Uncovered</div></div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Assign Duty Form -->
      <?php if ($this->rbac->hasPrivilege('coe_invigilation', 'can_add')): ?>
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Assign Duty</h3></div>
          <div class="box-body">
            <?php if (empty($rooms)): ?>
              <div class="alert alert-warning">No seating rooms created for this event. Create rooms in the <a href="<?php echo site_url('coe/coe_seating/manage/' . $batch_exam->id); ?>">Seating module</a> first.</div>
            <?php else: ?>
              <form method="post" action="<?php echo site_url('coe/coe_invigilation/assign_duty'); ?>">
                <?php echo form_hidden('batch_exam_id_redirect', $batch_exam->id); ?>
                <div class="form-group">
                  <label>Exam Room <span class="text-danger">*</span></label>
                  <select name="seating_room_id" class="form-control" required>
                    <option value="">-- Select Room --</option>
                    <?php foreach ($rooms as $room): ?>
                      <option value="<?php echo $room->id; ?>">
                        <?php echo htmlspecialchars($room->hall_name); ?>
                        — <?php echo date('d M', strtotime($room->exam_date)); ?>
                        (<?php echo $room->session_slot; ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Staff Member <span class="text-danger">*</span></label>
                  <select name="staff_id" class="form-control" required>
                    <option value="">-- Select Staff --</option>
                    <?php foreach ($staff as $st): ?>
                      <option value="<?php echo $st->id; ?>">
                        <?php echo htmlspecialchars($st->name . ' ' . $st->surname); ?>
                        <?php if ($st->designation): ?> (<?php echo htmlspecialchars($st->designation); ?>)<?php endif; ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label>Duty Type <span class="text-danger">*</span></label>
                  <select name="duty_type" class="form-control" required>
                    <option value="invigilator">Invigilator</option>
                    <option value="chief_superintendent">Chief Superintendent</option>
                    <option value="deputy">Deputy</option>
                    <option value="flying_squad">Flying Squad</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Remarks <small class="text-muted">(optional)</small></label>
                  <input type="text" name="remarks" class="form-control" placeholder="Any remarks...">
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Assign Duty</button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- Bulk Import Panel -->
        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-upload"></i> Bulk Import</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
          </div>
          <div class="box-body">
            <p class="text-muted" style="font-size:.85rem;">
              Upload a CSV file to assign multiple duties at once.<br>
              Columns: <code>hall_name, exam_date, session_slot, staff_id, duty_type, remarks</code>
            </p>
            <a href="<?php echo site_url('coe/coe_invigilation/download_sample/' . $batch_exam->id); ?>"
               class="btn btn-default btn-sm btn-block" style="margin-bottom:10px;">
              <i class="fa fa-download"></i> Download Sample CSV
            </a>
            <form method="post" action="<?php echo site_url('coe/coe_invigilation/bulk_import/' . $batch_exam->id); ?>"
                  enctype="multipart/form-data">
              <div class="form-group" style="margin-bottom:8px;">
                <input type="file" name="import_file" accept=".csv" class="form-control input-sm" required>
              </div>
              <button type="submit" class="btn btn-success btn-sm btn-block">
                <i class="fa fa-upload"></i> Import Duties
              </button>
            </form>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Duty Roster by Room -->
      <div class="col-md-<?php echo $this->rbac->hasPrivilege('coe_invigilation', 'can_add') ? '8' : '12'; ?>">
        <div class="box box-default">
          <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="box-title"><i class="fa fa-user-circle"></i> Duty Roster</h3>
            <div>
              <a href="<?php echo site_url('coe/coe_invigilation/print_roster/' . $batch_exam->id); ?>"
                 class="btn btn-default btn-sm" target="_blank"><i class="fa fa-print"></i> Print Roster</a>
              <a href="<?php echo site_url('coe/coe_invigilation'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
          </div>
          <div class="box-body">
            <?php if (empty($rooms)): ?>
              <div class="alert alert-info">No seating rooms found for this event.</div>
            <?php elseif (empty($duties)): ?>
              <div class="alert alert-info"><i class="fa fa-info-circle"></i> No duties assigned yet. Use the form to assign staff.</div>
            <?php else: ?>
              <?php foreach ($rooms as $room): ?>
                <div class="room-section">
                  <div class="rs-header">
                    <div>
                      <strong><?php echo htmlspecialchars($room->hall_name); ?></strong>
                      <?php if ($room->location ?? null): ?>
                        <span style="font-size:.8rem;opacity:.8;margin-left:6px;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($room->location); ?></span>
                      <?php endif; ?>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                      <span style="font-size:.82rem;opacity:.85;"><i class="fa fa-calendar"></i> <?php echo date('d M Y', strtotime($room->exam_date)); ?></span>
                      <?php if ($room->session_slot === 'FN'): ?>
                        <span class="fn-badge">FN</span>
                      <?php else: ?>
                        <span class="an-badge">AN</span>
                      <?php endif; ?>
                      <span style="font-size:.8rem;opacity:.7;"><?php echo $room->duty_count; ?> duties</span>
                    </div>
                  </div>
                  <div class="rs-body">
                    <?php
                    $room_duties = $duties_by_room[$room->id] ?? [];
                    if (empty($room_duties)):
                    ?>
                      <div style="color:#999;font-size:.85rem;font-style:italic;"><i class="fa fa-exclamation-circle text-warning"></i> No duties assigned for this room.</div>
                    <?php else: ?>
                      <?php foreach ($room_duties as $d): ?>
                        <div class="duty-row">
                          <?php $dl = $duty_labels[$d->duty_type] ?? ['label' => ucwords(str_replace('_',' ',$d->duty_type)), 'class' => 'label-default']; ?>
                          <span class="label <?php echo $dl['class']; ?>" style="min-width:120px;text-align:center;">
                            <?php echo $dl['label']; ?>
                          </span>
                          <strong><?php echo htmlspecialchars($d->staff_firstname . ' ' . $d->staff_surname); ?></strong>
                          <?php if ($d->designation): ?>
                            <span class="text-muted" style="font-size:.82rem;">(<?php echo htmlspecialchars($d->designation); ?>)</span>
                          <?php endif; ?>
                          <?php if ($d->remarks ?? null): ?>
                            <span class="text-muted" style="font-size:.8rem;"><i class="fa fa-comment"></i> <?php echo htmlspecialchars($d->remarks); ?></span>
                          <?php endif; ?>
                          <?php if ($this->rbac->hasPrivilege('coe_invigilation', 'can_delete')): ?>
                            <a href="<?php echo site_url('coe/coe_invigilation/remove_duty/' . $d->id); ?>"
                               class="btn btn-xs btn-danger confirm-remove" style="margin-left:auto;"
                               data-name="<?php echo htmlspecialchars($d->staff_firstname . ' ' . $d->staff_surname); ?>">
                              <i class="fa fa-times"></i>
                            </a>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
document.querySelectorAll('.confirm-remove').forEach(function(btn){
  btn.addEventListener('click', function(e){
    e.preventDefault();
    var url = btn.href, name = btn.dataset.name;
    if (typeof swal !== 'undefined') {
      swal({ title:'Remove Duty?', text:'Remove duty assignment for "'+name+'"?', type:'warning',
        showCancelButton:true, confirmButtonColor:'#c62828', confirmButtonText:'Remove'
      }, function(){ window.location.href = url; });
    } else if (confirm('Remove duty for "'+name+'"?')) { window.location.href = url; }
  });
});
</script>
