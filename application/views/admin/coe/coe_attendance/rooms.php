<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-check-square-o"></i> Exam Attendance
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_attendance'); ?>"><i class="fa fa-arrow-left"></i> Back to Events</a></li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border" style="display:flex;justify-content:space-between;align-items:center;">
                        <h3 class="box-title"><i class="fa fa-building-o"></i> Exam Rooms</h3>
                        <a href="<?php echo site_url('coe/coe_attendance'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Events
                        </a>
                    </div>
                    <div class="box-body">
                        <?php if (empty($rooms)): ?>
                            <p class="text-muted">No seating rooms found for this batch exam. Please set up seating arrangement first.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Hall</th>
                                    <th>Date</th>
                                    <th>Session</th>
                                    <th>Capacity</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $i => $room): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($room->hall_name); ?></td>
                                    <td><?php echo date('d M Y', strtotime($room->exam_date)); ?></td>
                                    <td>
                                        <span class="label <?php echo $room->session_slot === 'FN' ? 'label-success' : 'label-warning'; ?>">
                                            <?php echo $room->session_slot; ?>
                                        </span>
                                    </td>
                                    <td><?php echo (int) $room->seating_capacity; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_attendance/sheet/' . $room->id . '/' . $room->exam_date . '/' . $room->session_slot); ?>"
                                           class="btn btn-xs btn-info">
                                            <i class="fa fa-pencil"></i> Attendance Sheet
                                        </a>
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

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'attendance']); ?>
