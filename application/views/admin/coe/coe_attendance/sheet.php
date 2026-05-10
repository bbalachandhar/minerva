<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-check-square-o"></i> Attendance Sheet
            <small>Room <?php echo $room_id; ?> | <?php echo date('d M Y', strtotime($exam_date)); ?> | <?php echo $session_slot; ?></small>
        </h1>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

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

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Students</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-xs btn-default" id="markAllPresent">Mark All Present</button>
                            <button type="button" class="btn btn-xs btn-default" id="markAllAbsent">Mark All Absent</button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($students)): ?>
                            <p class="text-muted">No students found for this room/date/session. Please check seating arrangement.</p>
                        <?php else: ?>
                        <form method="post" action="<?php echo site_url('coe/coe_attendance/save/' . $room_id . '/' . $exam_date . '/' . $session_slot); ?>">
                            <input type="hidden" name="all_ids" value="<?php echo implode(',', array_column((array)$students, 'hall_ticket_id')); ?>">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width:50px">Seat</th>
                                        <th>Hall Ticket No</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th style="width:80px">Present</th>
                                        <th>Remarks</th>
                                        <th>QR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                    <tr class="<?php echo $s->is_present ? 'success' : ($s->att_id ? 'danger' : ''); ?>">
                                        <td><?php echo htmlspecialchars($s->seat_number ?? '—'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($s->hall_ticket_no); ?></strong></td>
                                        <td><?php echo htmlspecialchars($s->student_name); ?></td>
                                        <td><?php echo htmlspecialchars($s->subject_code . ' – ' . $s->subject_name); ?></td>
                                        <td class="text-center">
                                            <input type="checkbox" name="present_ids[]"
                                                   value="<?php echo $s->hall_ticket_id; ?>"
                                                   class="present-cb"
                                                   <?php echo $s->is_present ? 'checked' : ''; ?>>
                                        </td>
                                        <td><?php echo htmlspecialchars($s->remarks ?? ''); ?></td>
                                        <td class="text-center">
                                            <?php if ($s->qr_scanned): ?>
                                                <span class="label label-success" title="Marked via QR scan"><i class="fa fa-qrcode"></i></span>
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
document.getElementById('markAllPresent').addEventListener('click', function() {
    document.querySelectorAll('.present-cb').forEach(function(cb) { cb.checked = true; });
});
document.getElementById('markAllAbsent').addEventListener('click', function() {
    document.querySelectorAll('.present-cb').forEach(function(cb) { cb.checked = false; });
});
</script>
