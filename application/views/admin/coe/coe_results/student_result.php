<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-id-card"></i> Student Result
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_results/listing/' . $batch_exam_id); ?>">
                <i class="fa fa-arrow-left"></i> Back</a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <?php echo htmlspecialchars($student->full_name); ?> &nbsp;|&nbsp;
                            <?php echo htmlspecialchars($student->admission_no); ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('coe/coe_results/listing/' . $batch_exam_id); ?>" class="btn btn-default btn-sm" style="margin-right:8px">
                                <i class="fa fa-arrow-left"></i> Back to Results
                            </a>
                            <small class="text-muted"><?php echo htmlspecialchars($event->exam_group_name . ' — ' . $event->exam); ?></small>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th><th>Subject</th><th>Credits</th>
                                    <th>Internal</th><th>External</th><th>Moderation</th>
                                    <th>Total</th><th>Grade</th><th>GP</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $i => $r): ?>
                                <tr class="<?php echo $r->result_status === 'fail' ? 'danger' : ''; ?>">
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($r->subject_code . ' — ' . $r->subject_name); ?></td>
                                    <td><?php echo $r->credits ?? 4; ?></td>
                                    <td><?php echo number_format($r->internal_marks, 1); ?></td>
                                    <td><?php echo number_format($r->external_marks, 1); ?></td>
                                    <td><?php echo $r->moderation_applied ? '+'.number_format($r->moderation_applied,1) : '—'; ?></td>
                                    <td><strong><?php echo number_format($r->total_marks, 1); ?></strong></td>
                                    <td><strong><?php echo $r->grade; ?></strong></td>
                                    <td><?php echo $r->grade_points; ?></td>
                                    <td>
                                        <span class="label <?php echo $r->result_status==='pass'?'label-success':'label-danger'; ?>">
                                            <?php echo ucfirst($r->result_status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box <?php echo ($sgpa && $sgpa->arrear_count > 0) ? 'bg-yellow' : 'bg-green'; ?>">
                                    <span class="info-box-icon"><i class="fa fa-star"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">SGPA</span>
                                        <span class="info-box-number"><?php echo $sgpa ? number_format($sgpa->sgpa, 2) : '—'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-blue">
                                    <span class="info-box-icon"><i class="fa fa-bar-chart"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">CGPA</span>
                                        <span class="info-box-number"><?php echo $sgpa ? number_format($sgpa->cgpa, 2) : '—'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box <?php echo ($sgpa && $sgpa->arrear_count > 0) ? 'bg-red' : 'bg-green'; ?>">
                                    <span class="info-box-icon"><i class="fa fa-book"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Credits</span>
                                        <span class="info-box-number">
                                            <?php echo $sgpa ? $sgpa->total_credits_earned.' / '.$sgpa->total_credits_registered : '—'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($sgpa && $sgpa->arrear_count > 0): ?>
                        <div class="alert alert-warning">
                            <i class="fa fa-warning"></i> This student has one or more arrears.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'results']); ?>
