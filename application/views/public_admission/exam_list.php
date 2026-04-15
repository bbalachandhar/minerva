<section class="content-header">
    <h1><i class="fa fa-pencil-square-o"></i> Online Exams</h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('public_admission/applicant_dashboard'); ?>"><i class="fa fa-home"></i> Dashboard</a></li>
        <li class="active">Online Exams</li>
    </ol>
</section>

<section class="content">
    <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Assigned Exams</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-default">
                            Ref: <?php echo htmlspecialchars($applicant['reference_no']); ?>
                        </span>
                        &nbsp;
                        <strong><?php echo htmlspecialchars($this->customlib->getFullName($applicant['firstname'], $applicant['middlename'], $applicant['lastname'], $sch_setting->middlename, $sch_setting->lastname)); ?></strong>
                    </div>
                </div>
                <div class="box-body">
                    <?php if (empty($onlineexam)): ?>
                        <div class="callout callout-info">
                            <p><i class="fa fa-info-circle"></i> No exam is assigned to your application yet. Please contact the admission office.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($onlineexam as $exam): ?>
                            <div class="box box-default" style="margin-bottom:12px;">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <h4 style="margin-top:0;"><?php echo htmlspecialchars($exam->exam); ?></h4>
                                            <p class="text-muted" style="font-size:13px; margin-bottom:6px;">
                                                <i class="fa fa-calendar"></i>
                                                From: <?php echo $this->customlib->dateyyyymmddToDateTimeformat($exam->exam_from, false); ?>
                                                &nbsp;&nbsp;To: <?php echo $this->customlib->dateyyyymmddToDateTimeformat($exam->exam_to, false); ?>
                                                &nbsp;&nbsp;<i class="fa fa-clock-o"></i> Duration: <?php echo htmlspecialchars($exam->duration); ?>
                                                &nbsp;&nbsp;<i class="fa fa-refresh"></i> Attempts: <?php echo (int)$exam->counter; ?>/<?php echo (int)$exam->attempt; ?>
                                            </p>
                                            <?php if (!empty($exam->description)): ?>
                                                <p style="margin-bottom:0;"><?php echo htmlspecialchars($exam->description); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-3 text-right" style="padding-top:8px;">
                                            <?php if ((int)$exam->is_attempted === 1): ?>
                                                <span class="label label-success" style="font-size:13px; padding:6px 10px; display:inline-block; margin-bottom:6px;"><i class="fa fa-check"></i> Attempted</span><br>
                                                <?php if (!empty($exam->publish_result) || ($exam->is_quiz && !empty($exam->show_result_immediately))): ?>
                                                    <a href="<?php echo site_url('public_admission/exam_view/' . $exam->id); ?>" class="btn btn-success btn-sm" style="margin-bottom:6px;">
                                                        <i class="fa fa-bar-chart"></i> View Result
                                                    </a><br>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="<?php echo site_url('public_admission/exam_view/' . $exam->id); ?>" class="btn btn-primary btn-sm" style="margin-bottom:6px;">
                                                    <i class="fa fa-pencil"></i> Open Exam
                                                </a><br>
                                            <?php endif; ?>
                                            <a href="<?php echo site_url('public_admission/hall_ticket/' . $exam->id); ?>" target="_blank" class="btn btn-default btn-sm">
                                                <i class="fa fa-print"></i> Hall Ticket
                                            </a>
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
