<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.coe-stat-card { border-radius:12px;color:#fff;padding:16px 18px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 15px rgba(0,0,0,.15);margin-bottom:18px; }
.coe-stat-card .stat-icon { font-size:2.1rem;opacity:.85;flex-shrink:0; }
.coe-stat-card .stat-body .stat-num { font-size:1.8rem;font-weight:700;line-height:1; }
.coe-stat-card .stat-body .stat-lbl { font-size:.83rem;opacity:.9;margin-top:2px; }
.card-teal   { background:linear-gradient(135deg,#00796b,#009688); }
.card-blue   { background:linear-gradient(135deg,#1565c0,#1976d2); }
.card-green  { background:linear-gradient(135deg,#2e7d32,#388e3c); }
.card-orange { background:linear-gradient(135deg,#e65100,#f57c00); }
.room-card { border-radius:8px;border:1px solid #e0e0e0;margin-bottom:14px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.06); }
.room-card .room-header { padding:10px 16px;background:linear-gradient(135deg,#00838f,#006064);color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px; }
.room-card .room-body { padding:12px 16px; }
.fn-pill { background:#e3f2fd;color:#1565c0;border:1px solid #90caf9;border-radius:12px;padding:2px 8px;font-size:.78rem;font-weight:600; }
.an-pill { background:#ede7f6;color:#4527a0;border:1px solid #b39ddb;border-radius:12px;padding:2px 8px;font-size:.78rem;font-weight:600; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_seating'); ?> <small>Manage Rooms — <?php echo htmlspecialchars($batch_exam->exam); ?></small><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_seating'); ?>">Seating</a></li>
      <li class="active">Manage</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Stat Cards -->
    <div class="row">
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-teal">
          <div class="stat-icon"><i class="fa fa-th"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['rooms']; ?></div><div class="stat-lbl">Rooms Created</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-blue">
          <div class="stat-icon"><i class="fa fa-id-card"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['total_ht']; ?></div><div class="stat-lbl">Valid Hall Tickets</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-green">
          <div class="stat-icon"><i class="fa fa-check"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['assigned']; ?></div><div class="stat-lbl">Assigned</div></div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="coe-stat-card card-orange">
          <div class="stat-icon"><i class="fa fa-exclamation-circle"></i></div>
          <div class="stat-body"><div class="stat-num"><?php echo $summary['unassigned']; ?></div><div class="stat-lbl">Unassigned</div></div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Add Room Form -->
      <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_add')): ?>
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-plus"></i> Add Seating Room</h3>
          </div>
          <div class="box-body">
            <form method="post" action="<?php echo site_url('coe/coe_seating/create_room'); ?>">
              <?php echo form_hidden('batch_exam_id', $batch_exam->id); ?>
              <div class="form-group">
                <label>Exam Hall <span class="text-danger">*</span></label>
                <select name="hall_id" class="form-control" required>
                  <option value="">-- Select Hall --</option>
                  <?php foreach ($halls as $hall): ?>
                    <option value="<?php echo $hall->id; ?>">
                      <?php echo htmlspecialchars($hall->name); ?> (Cap: <?php echo $hall->capacity; ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Exam Date <span class="text-danger">*</span></label>
                <input type="date" name="exam_date" class="form-control" required
                       value="<?php echo $batch_exam->date_from ?? date('Y-m-d'); ?>">
              </div>
              <div class="form-group">
                <label>Session Slot <span class="text-danger">*</span></label>
                <select name="session_slot" class="form-control" required>
                  <option value="FN">FN (Forenoon)</option>
                  <option value="AN">AN (Afternoon)</option>
                </select>
              </div>
              <div class="form-group">
                <label>Subject (optional)</label>
                <select name="subject_id" class="form-control">
                  <option value="">-- All / Multiple --</option>
                  <?php foreach ($subjects as $sub): ?>
                    <option value="<?php echo $sub->id; ?>"><?php echo htmlspecialchars($sub->name . ' (' . $sub->code . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Capacity Override <small class="text-muted">(leave blank to use hall default)</small></label>
                <input type="number" name="capacity_override" class="form-control" min="1" placeholder="e.g. 30">
              </div>
              <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> Add Room</button>
            </form>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Room List -->
      <div class="col-md-<?php echo $this->rbac->hasPrivilege('coe_seating', 'can_add') ? '8' : '12'; ?>">
        <div class="box box-default">
          <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="box-title"><i class="fa fa-building"></i> Exam Rooms (<?php echo count($rooms); ?>)</h3>
            <div style="display:flex;gap:6px;">
              <a href="<?php echo site_url('coe/coe_seating/halls'); ?>" class="btn btn-info btn-sm">
                <i class="fa fa-building-o"></i> Manage Exam Halls
              </a>
              <a href="<?php echo site_url('coe/coe_seating'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
          </div>
          <div class="box-body">
            <?php if (empty($rooms)): ?>
              <div class="alert alert-info"><i class="fa fa-info-circle"></i> No rooms created yet. Add one using the form.</div>
            <?php else: ?>
              <?php foreach ($rooms as $room): ?>
              <div class="room-card">
                <div class="room-header">
                  <div>
                    <strong><?php echo htmlspecialchars($room->hall_name); ?></strong>
                    <span style="margin-left:8px;font-size:.8rem;opacity:.85;">
                      <i class="fa fa-calendar"></i> <?php echo date('d M Y', strtotime($room->exam_date)); ?>
                    </span>
                  </div>
                  <?php if ($room->session_slot === 'FN'): ?>
                    <span class="fn-pill">FN</span>
                  <?php else: ?>
                    <span class="an-pill">AN</span>
                  <?php endif; ?>
                </div>
                <div class="room-body">
                  <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div>
                      <span style="font-size:1.3rem;font-weight:700;color:#006064;"><?php echo $room->assigned_count; ?></span>
                      <span style="font-size:.82rem;color:#666;"> / <?php echo $room->effective_capacity; ?> seats</span>
                    </div>
                    <div style="font-size:.82rem;color:#777;">
                      <?php if ($room->subject_name ?? null): ?>
                        <i class="fa fa-book"></i> <?php echo htmlspecialchars($room->subject_name); ?>
                      <?php else: ?>
                        <i class="fa fa-book"></i> All subjects
                      <?php endif; ?>
                    </div>
                    <?php if ($room->location ?? null): ?>
                      <div style="font-size:.82rem;color:#777;"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($room->location); ?></div>
                    <?php endif; ?>
                    <div style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;">
                      <a href="<?php echo site_url('coe/coe_seating/view_room/' . $room->id); ?>" class="btn btn-xs btn-info">
                        <i class="fa fa-list"></i> View
                      </a>
                      <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_add')): ?>
                        <a href="<?php echo site_url('coe/coe_seating/auto_assign/' . $room->id); ?>"
                           class="btn btn-xs btn-success confirm-assign"
                           data-name="<?php echo htmlspecialchars($room->hall_name); ?>">
                          <i class="fa fa-magic"></i> Auto-Assign
                        </a>
                      <?php endif; ?>
                      <a href="<?php echo site_url('coe/coe_seating/print_seating/' . $room->id); ?>"
                         class="btn btn-xs btn-primary" target="_blank">
                        <i class="fa fa-print"></i> Print
                      </a>
                      <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_edit')): ?>
                        <a href="<?php echo site_url('coe/coe_seating/clear_room/' . $room->id); ?>"
                           class="btn btn-xs btn-warning confirm-clear"
                           data-name="<?php echo htmlspecialchars($room->hall_name); ?>">
                          <i class="fa fa-times"></i> Clear
                        </a>
                      <?php endif; ?>
                      <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_delete')): ?>
                        <a href="<?php echo site_url('coe/coe_seating/delete_room/' . $room->id); ?>"
                           class="btn btn-xs btn-danger confirm-delete"
                           data-name="<?php echo htmlspecialchars($room->hall_name); ?>">
                          <i class="fa fa-trash"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
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
(function(){
  function confirmAction(sel, title, text, color) {
    document.querySelectorAll(sel).forEach(function(btn){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        var url = btn.href, name = btn.dataset.name;
        if (typeof swal !== 'undefined') {
          swal({ title:title, text:text.replace('{name}', '"'+name+'"'), type:'warning',
            showCancelButton:true, confirmButtonColor:color, confirmButtonText:'Confirm'
          }, function(){ window.location.href = url; });
        } else if (confirm(title + ' ' + name + '?')) { window.location.href = url; }
      });
    });
  }
  confirmAction('.confirm-assign','Auto-Assign Students?','Assign eligible unassigned students to {name}.','#2e7d32');
  confirmAction('.confirm-clear','Clear All Assignments?','All seat assignments for {name} will be removed.','#e65100');
  confirmAction('.confirm-delete','Delete Room?','This will delete {name} and all its assignments.','#c62828');
})();
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'seating']); ?>
