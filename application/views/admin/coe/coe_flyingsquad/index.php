<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-shield"></i> Flying Squad Visits</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe'); ?>"><i class="fa fa-dashboard"></i> CoE</a></li>
            <li class="active">Flying Squad</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-shield"></i> Select Exam Event</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($events)): ?>
                            <p class="text-muted text-center">No exam events found for this session.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Exam Group</th>
                                        <th>Batch / Exam</th>
                                        <th>Class</th>
                                        <th>Date From</th>
                                        <th>Date To</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; foreach ($events as $ev): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($ev->exam_group_name ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($ev->exam ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($ev->class_name ?? '—'); ?></td>
                                    <td><?php echo $ev->date_from ? date('d M Y', strtotime($ev->date_from)) : '—'; ?></td>
                                    <td><?php echo $ev->date_to   ? date('d M Y', strtotime($ev->date_to))   : '—'; ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_flyingsquad/manage/' . $ev->batch_exam_id); ?>" class="btn btn-sm btn-primary">
                                            <i class="fa fa-shield"></i> Manage Visits
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
