<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-check-square-o"></i> Attendance Sheet
            <small>Room <?php echo $room_id; ?> | <?php echo date('d M Y', strtotime($exam_date)); ?> | <?php echo $session_slot; ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_attendance/rooms/' . $batch_exam_id); ?>"><i class="fa fa-arrow-left"></i> Back to Rooms</a></li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Exam Info Panel -->
        <?php
        $ri = $room_info ?? null;
        $ms = $marking_staff ?? null;
        ?>
        <div class="box box-primary" style="border-top-color:#367fa9;margin-bottom:12px;">
            <div class="box-header with-border" style="background:#f4f8fb;padding:8px 15px;">
                <h3 class="box-title" style="font-size:14px;font-weight:600;">
                    <i class="fa fa-file-text-o text-primary"></i>&nbsp; Exam Details
                </h3>
            </div>
            <div class="box-body" style="padding:10px 15px 6px;">
                <div class="row">
                    <!-- Col 1: Exam & Subject -->
                    <div class="col-sm-4">
                        <table class="table table-condensed" style="margin:0;font-size:13px;">
                            <tr>
                                <td style="width:90px;color:#777;padding:4px 6px;"><i class="fa fa-graduation-cap fa-fw"></i> Exam</td>
                                <td style="padding:4px 6px;font-weight:600;">
                                    <?php echo $ri && $ri->exam_group_name ? htmlspecialchars($ri->exam_group_name) : '<span class="text-muted">—</span>'; ?>
                                    <?php if ($ri && $ri->batch_title): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($ri->batch_title); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="color:#777;padding:4px 6px;"><i class="fa fa-book fa-fw"></i> Subject</td>
                                <td style="padding:4px 6px;font-weight:600;">
                                    <?php if ($ri && $ri->subject_name): ?>
                                        <?php echo htmlspecialchars($ri->subject_name); ?>
                                        <?php if ($ri->subject_code): ?>
                                            &nbsp;<span class="label label-default" style="font-size:11px;"><?php echo htmlspecialchars($ri->subject_code); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="color:#777;padding:4px 6px;"><i class="fa fa-users fa-fw"></i> Class</td>
                                <td style="padding:4px 6px;">
                                    <?php echo $ri && $ri->class_name ? htmlspecialchars($ri->class_name) : '<span class="text-muted">—</span>'; ?>
                                    <?php if ($ri && $ri->session_year): ?>
                                        <small class="text-muted">(<?php echo htmlspecialchars($ri->session_year); ?>)</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- Col 2: Hall & Room -->
                    <div class="col-sm-4">
                        <table class="table table-condensed" style="margin:0;font-size:13px;">
                            <tr>
                                <td style="width:90px;color:#777;padding:4px 6px;"><i class="fa fa-building-o fa-fw"></i> Hall</td>
                                <td style="padding:4px 6px;font-weight:600;">
                                    <?php echo $ri && $ri->hall_name ? htmlspecialchars($ri->hall_name) : '<span class="text-muted">—</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="color:#777;padding:4px 6px;"><i class="fa fa-map-marker fa-fw"></i> Room #</td>
                                <td style="padding:4px 6px;font-weight:600;"><?php echo $room_id; ?></td>
                            </tr>
                            <tr>
                                <td style="color:#777;padding:4px 6px;"><i class="fa fa-chair fa-fw"></i> Capacity</td>
                                <td style="padding:4px 6px;">
                                    <?php echo $ri && $ri->room_capacity ? (int) $ri->room_capacity . ' seats' : '<span class="text-muted">—</span>'; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!-- Col 3: Date, Time & Marked By -->
                    <div class="col-sm-4">
                        <table class="table table-condensed" style="margin:0;font-size:13px;">
                            <tr>
                                <td style="width:90px;color:#777;padding:4px 6px;"><i class="fa fa-calendar fa-fw"></i> Date</td>
                                <td style="padding:4px 6px;font-weight:600;"><?php echo date('d M Y', strtotime($exam_date)); ?></td>
                            </tr>
                            <tr>
                                <td style="color:#777;padding:4px 6px;"><i class="fa fa-clock-o fa-fw"></i> Time</td>
                                <td style="padding:4px 6px;">
                                    <?php if ($ri && $ri->start_time && $ri->end_time): ?>
                                        <?php echo date('h:i A', strtotime($ri->start_time)); ?> &ndash; <?php echo date('h:i A', strtotime($ri->end_time)); ?>
                                        &nbsp;<span class="label label-<?php echo $session_slot === 'FN' ? 'info' : 'warning'; ?>"><?php echo $session_slot; ?></span>
                                    <?php else: ?>
                                        <span class="label label-<?php echo $session_slot === 'FN' ? 'info' : 'warning'; ?>"><?php echo $session_slot; ?></span>
                                        <span class="text-muted"> (time not set)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="color:#777;padding:4px 6px;"><i class="fa fa-user-circle fa-fw"></i> Marked by</td>
                                <td style="padding:4px 6px;">
                                    <?php if ($ms && $ms->full_name): ?>
                                        <strong><?php echo htmlspecialchars($ms->full_name); ?></strong>
                                        <?php if ($ms->designation): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($ms->designation); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <?php if ($summary): ?>
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-aqua">
                    <div class="inner"><h3><?php echo (int) $summary->total; ?></h3><p>Total Seated</p></div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-green">
                    <div class="inner"><h3><?php echo (int) $summary->present_count; ?></h3><p>Present</p></div>
                    <div class="icon"><i class="fa fa-check"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-red">
                    <div class="inner"><h3><?php echo (int) $summary->absent_count; ?></h3><p>Absent</p></div>
                    <div class="icon"><i class="fa fa-times"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $summary->total > 0 ? round(($summary->present_count / $summary->total) * 100) : 0; ?>%</h3>
                        <p>Attendance</p>
                    </div>
                    <div class="icon"><i class="fa fa-bar-chart"></i></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- CSV Bulk Upload -->
        <?php if ($this->rbac->hasPrivilege('coe_attendance', 'can_add')): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;">
                        <h3 class="box-title"><i class="fa fa-upload"></i> Bulk Upload Attendance (CSV)</h3>
                        <div>
                            <a href="<?php echo site_url('coe/coe_attendance/sample_csv/' . $batch_exam_id); ?>" class="btn btn-success btn-sm">
                                <i class="fa fa-download"></i> Download Sample CSV
                            </a>
                            <button type="button" class="btn btn-default btn-sm" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display:none;">
                        <p class="text-muted" style="margin-bottom:10px;">
                            Upload a CSV with columns <strong>hall_ticket_no</strong> and <strong>present</strong> (use <code>1</code> for Present, <code>0</code> for Absent).
                        </p>
                        <form method="post" action="<?php echo site_url('coe/coe_attendance/upload_csv/' . $room_id . '/' . $exam_date . '/' . $session_slot); ?>" enctype="multipart/form-data">
                            <div class="input-group" style="max-width:420px;">
                                <input type="file" name="attendance_csv" accept=".csv" class="form-control" required>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Import</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;">
                        <h3 class="box-title"><i class="fa fa-list"></i> Students</h3>
                        <div>
                            <button type="button" class="btn btn-xs btn-default" id="markAllPresent">Mark All Present</button>
                            <button type="button" class="btn btn-xs btn-default" id="markAllAbsent">Mark All Absent</button>
                            <a href="<?php echo site_url('coe/coe_attendance/rooms/' . $batch_exam_id); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($students)): ?>
                            <div class="callout callout-warning">
                                <h4><i class="fa fa-exclamation-triangle"></i> No students found</h4>
                                <p>No seating assignments exist for this room / date / session. You can still record attendance using the <strong>Bulk Upload CSV</strong> panel above.</p>
                            </div>
                        <?php else: ?>
                        <form method="post" action="<?php echo site_url('coe/coe_attendance/save/' . $room_id . '/' . $exam_date . '/' . $session_slot); ?>">
                            <input type="hidden" name="all_ids" value="<?php echo implode(',', array_column((array)$students, 'hall_ticket_id')); ?>">
                            <table id="attendance-table" class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width:50px">Seat</th>
                                        <th>Hall Ticket No</th>
                                        <th>Student</th>
                                        <th style="width:70px" class="text-center">Present</th>
                                        <th style="width:200px">Remarks</th>
                                        <th style="width:60px" class="text-center">QR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                    <tr class="att-row <?php echo $s->is_present ? 'success' : ($s->att_id ? 'danger' : ''); ?>">
                                        <td><?php echo htmlspecialchars($s->seat_number ?? '—'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($s->hall_ticket_no); ?></strong></td>
                                        <td><?php echo htmlspecialchars($s->student_name); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" name="present_ids[]"
                                                   value="<?php echo $s->hall_ticket_id; ?>"
                                                   class="present-cb"
                                                   <?php echo $s->is_present ? 'checked' : ''; ?>>
                                        </td>
                                        <td>
                                            <input type="text" name="remarks[<?php echo $s->hall_ticket_id; ?>]"
                                                   class="form-control input-sm"
                                                   value="<?php echo htmlspecialchars($s->remarks ?? ''); ?>"
                                                   placeholder="—">
                                        </td>
                                        <td class="text-center">
                                            <?php if ($s->qr_scanned): ?>
                                                <span class="label label-success" title="Marked via QR scan"><i class="fa fa-qrcode"></i> QR</span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ($this->rbac->hasPrivilege('coe_attendance', 'can_add')): ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Attendance
                            </button>
                            <?php endif; ?>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function(){
  // Row highlight on checkbox change
  function syncRow(cb) {
    var row = cb.closest('tr');
    row.classList.remove('success', 'danger');
    if (cb.checked) row.classList.add('success');
  }
  document.querySelectorAll('.present-cb').forEach(function(cb) {
    cb.addEventListener('change', function() { syncRow(this); });
  });

  // Mark All Present
  document.getElementById('markAllPresent').addEventListener('click', function() {
    document.querySelectorAll('.present-cb').forEach(function(cb) {
      cb.checked = true; syncRow(cb);
    });
  });
  // Mark All Absent
  document.getElementById('markAllAbsent').addEventListener('click', function() {
    document.querySelectorAll('.present-cb').forEach(function(cb) {
      cb.checked = false; syncRow(cb);
    });
  });

  // DataTable with search, no paging (attendance sheet shows all students)
  $(function(){
    if ($.fn.dataTable && document.getElementById('attendance-table')) {
      $('#attendance-table').DataTable({
        paging:   false,
        ordering: true,
        info:     false,
        columnDefs: [
          { orderable: false, targets: [3, 4, 5] }  // Present, Remarks, QR not sortable
        ],
        language: { search: 'Search student:' }
      });
    }
  });
})();
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'attendance']); ?>
