<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.info-meta { display:flex;gap:18px;flex-wrap:wrap;font-size:.88rem;color:#555;margin-bottom:14px; }
.info-meta .meta-item { display:flex;align-items:center;gap:5px; }
.info-meta .meta-item i { color:#006064; }
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo lang('coe_seating'); ?> <small>Room: <?php echo htmlspecialchars($room->hall_name); ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_seating'); ?>">Seating</a></li>
      <li><a href="<?php echo site_url('coe/coe_seating/manage/' . $room->exam_group_class_batch_exam_id); ?>">Manage</a></li>
      <li class="active"><?php echo htmlspecialchars($room->hall_name); ?></li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <div class="box box-default">
      <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <h3 class="box-title"><i class="fa fa-building"></i> <?php echo htmlspecialchars($room->hall_name); ?></h3>
        <div style="display:flex;gap:8px;">
          <a href="<?php echo site_url('coe/coe_seating/manage/' . $room->exam_group_class_batch_exam_id); ?>" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Back
          </a>
          <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_add')): ?>
            <a href="<?php echo site_url('coe/coe_seating/auto_assign/' . $room->id); ?>"
               class="btn btn-success btn-sm confirm-assign" data-name="<?php echo htmlspecialchars($room->hall_name); ?>">
              <i class="fa fa-magic"></i> Auto-Assign More
            </a>
          <?php endif; ?>
          <a href="<?php echo site_url('coe/coe_seating/print_seating/' . $room->id); ?>"
             class="btn btn-primary btn-sm" target="_blank">
            <i class="fa fa-print"></i> Print Seating Plan
          </a>
        </div>
      </div>
      <div class="box-body">
        <div class="info-meta">
          <div class="meta-item"><i class="fa fa-calendar"></i> <?php echo date('d M Y', strtotime($room->exam_date)); ?></div>
          <div class="meta-item"><i class="fa fa-clock-o"></i> <?php echo $room->session_slot === 'FN' ? 'Forenoon (FN)' : 'Afternoon (AN)'; ?></div>
          <div class="meta-item"><i class="fa fa-users"></i> <?php echo count($assignments); ?> / <?php echo $room->effective_capacity; ?> seats</div>
          <?php if ($room->subject_name ?? null): ?>
            <div class="meta-item"><i class="fa fa-book"></i> <?php echo htmlspecialchars($room->subject_name); ?></div>
          <?php endif; ?>
          <?php if ($room->location ?? null): ?>
            <div class="meta-item"><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($room->location); ?></div>
          <?php endif; ?>
          <div class="meta-item"><i class="fa fa-graduation-cap"></i> <?php echo htmlspecialchars($room->exam_name ?? '—'); ?></div>
        </div>

        <?php if (empty($assignments)): ?>
          <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> No students assigned yet.
            <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_add')): ?>
              <a href="<?php echo site_url('coe/coe_seating/auto_assign/' . $room->id); ?>" class="alert-link">Auto-Assign Now</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="seatingTable">
              <thead>
                <tr>
                  <th style="width:70px;">Seat No.</th>
                  <th>Register No.</th>
                  <th>Student Name</th>
                  <th>Programme / Section</th>
                  <th>Hall Ticket No.</th>
                  <th style="width:80px;">Gender</th>
                  <th style="width:80px;">Attendance</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($assignments as $a): ?>
                <tr>
                  <td><strong style="color:#006064;"><?php echo htmlspecialchars($a->seat_number); ?></strong></td>
                  <td><?php echo htmlspecialchars($a->register_no ?? '—'); ?></td>
                  <td><strong><?php echo htmlspecialchars($a->firstname . ' ' . $a->lastname); ?></strong></td>
                  <td><?php echo htmlspecialchars(($a->class_name ?? '') . ' — ' . ($a->department_name ?? '')); ?></td>
                  <td><?php echo htmlspecialchars($a->hall_ticket_no ?? '—'); ?></td>
                  <td><?php echo ucfirst($a->gender ?? '—'); ?></td>
                  <td style="text-align:center;">
                    <?php if ($a->is_present == 1): ?>
                      <span style="color:#2e7d32;font-weight:600;">P</span>
                    <?php elseif ($a->is_present == 0 && $a->is_present !== null): ?>
                      <span style="color:#b71c1c;font-weight:600;">A</span>
                    <?php else: ?>
                      <span style="color:#999;">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</div>

<script>
$(document).ready(function(){
  if ($('#seatingTable').length) {
    $('#seatingTable').DataTable({ pageLength:50, order:[[0,'asc']] });
  }
  document.querySelectorAll('.confirm-assign').forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.preventDefault();
      var url = btn.href, name = btn.dataset.name;
      if (typeof swal !== 'undefined') {
        swal({ title:'Auto-Assign?', text:'Assign eligible unassigned students to "'+name+'".', type:'info',
          showCancelButton:true, confirmButtonColor:'#2e7d32', confirmButtonText:'Assign'
        }, function(){ window.location.href = url; });
      } else if (confirm('Auto-assign students to "'+name+'"?')) { window.location.href = url; }
    });
  });
});
</script>
