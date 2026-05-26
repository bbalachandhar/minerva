<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar"></i> Exam Subject Schedule</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li class="active">Exam Subject Schedule</li>
        </ol>
    </section>

    <section class="content">

        <!-- Session Filter -->
        <div class="box box-default" style="margin-bottom:16px;">
            <div class="box-body" style="padding:12px 16px;">
                <form method="GET" action="<?php echo site_url('coe/coe_schedule'); ?>" class="form-inline">
                    <div class="form-group" style="margin-right:10px;">
                        <label style="font-weight:600;margin-right:6px;">Session</label>
                        <select name="session_id" class="form-control input-sm" onchange="this.form.submit()">
                            <?php foreach ($sessions as $sess): ?>
                                <option value="<?php echo $sess['id']; ?>" <?php echo ($sess['id'] == $session_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sess['session']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Purpose note -->
        <div class="alert alert-info" style="border-radius:8px;border-left:5px solid #31b0d5;margin-bottom:16px;">
            <i class="fa fa-info-circle"></i>
            <strong>Exam Subject Schedule</strong> — Assign exam dates, times, and halls to each subject within a batch exam.
            This schedule is used for hall ticket generation and seating arrangement.
        </div>

        <!-- Events Table -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Batch Exams</h3>
            </div>
            <div class="box-body">
                <?php if (empty($events)): ?>
                    <p class="text-muted text-center" style="padding:20px 0;">
                        <i class="fa fa-calendar-times-o fa-2x" style="display:block;margin-bottom:8px;"></i>
                        No exam events found for this session.
                    </p>
                <?php else: ?>
                    <table class="table table-bordered table-hover" style="font-size:14px;">
                        <thead style="background:#f4f4f4;">
                            <tr>
                                <th>#</th>
                                <th>Batch Exam</th>
                                <th>Class</th>
                                <th>Exam Period</th>
                                <th>Applications</th>
                                <th style="width:130px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $i => $evt): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($evt->exam ?? ''); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($evt->exam_group_name ?? ''); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($evt->class_name ?? '—'); ?></td>
                                <td>
                                    <?php echo $evt->date_from ? date('d M Y', strtotime($evt->date_from)) : '—'; ?>
                                    <?php if ($evt->date_to && $evt->date_to !== $evt->date_from): ?>
                                        <br><small class="text-muted">to <?php echo date('d M Y', strtotime($evt->date_to)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($evt->application_count > 0): ?>
                                        <span class="badge bg-green"><?php echo $evt->application_count; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-gray">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo site_url('coe/coe_schedule/manage/' . $evt->batch_exam_id); ?>"
                                       class="btn btn-sm btn-primary">
                                        <i class="fa fa-calendar"></i> Schedule
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </section>
</div>
