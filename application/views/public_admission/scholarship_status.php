<section class="content-header" style="padding:10px 15px;">
    <h1><i class="fa fa-graduation-cap"></i> Scholarship Application Status</h1>
</section>

<section class="content">
    <?php echo $this->session->flashdata('msg'); ?>

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <?php if (!empty($my_applications)): ?>
                <?php
                $badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
                $icons  = ['pending'=>'clock-o','verified'=>'check-circle','approved'=>'thumbs-up','rejected'=>'times-circle'];
                foreach ($my_applications as $app):
                    $status = $app['status'];
                    $b      = $badges[$status] ?? 'default';
                    $ic     = $icons[$status]  ?? 'question-circle';
                ?>
                <div class="box box-<?php echo $b; ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-graduation-cap"></i>
                            <?php echo htmlspecialchars($app['scholarship_name'] ?? ''); ?>
                        </h3>
                        <span class="pull-right label label-<?php echo $b; ?>" style="font-size:13px;padding:5px 10px;">
                            <i class="fa fa-<?php echo $ic; ?>"></i> <?php echo ucfirst($status); ?>
                        </span>
                    </div>
                    <div class="box-body">
                        <!-- Step progress bar -->
                        <div class="row" style="margin-bottom:20px;">
                            <?php
                            $steps = [
                                ['label'=>'Submitted',  'statuses'=>['pending','verified','approved','rejected']],
                                ['label'=>'Verified',   'statuses'=>['verified','approved']],
                                ['label'=>'Approved',   'statuses'=>['approved']],
                            ];
                            if ($status === 'rejected') {
                                $steps = [
                                    ['label'=>'Submitted',  'statuses'=>['pending','verified','approved','rejected']],
                                    ['label'=>'Rejected',   'statuses'=>['rejected']],
                                ];
                            }
                            $col = 12 / count($steps);
                            foreach ($steps as $step):
                                $done = in_array($status, $step['statuses']);
                                $clr  = $done ? ($status === 'rejected' && $step['label'] === 'Rejected' ? '#e74c3c' : '#27ae60') : '#bdc3c7';
                            ?>
                            <div class="col-md-<?php echo $col; ?>" style="text-align:center;">
                                <div style="width:50px;height:50px;border-radius:50%;background:<?php echo $clr; ?>;
                                            display:inline-flex;align-items:center;justify-content:center;
                                            color:#fff;font-size:20px;margin-bottom:6px;">
                                    <i class="fa fa-<?php echo $done ? 'check' : 'circle-o'; ?>"></i>
                                </div>
                                <div style="font-weight:<?php echo $done ? 'bold' : 'normal'; ?>;
                                            color:<?php echo $done ? '#333' : '#aaa'; ?>;">
                                    <?php echo $step['label']; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Details -->
                        <table class="table table-condensed table-bordered">
                            <tr>
                                <th style="width:30%">Applied On</th>
                                <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                            </tr>
                            <?php if (!empty($app['applicant_remarks'])): ?>
                            <tr>
                                <th>Your Remarks</th>
                                <td><?php echo nl2br(htmlspecialchars($app['applicant_remarks'])); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($app['verifier_remarks'])): ?>
                            <tr>
                                <th>Verifier Remarks</th>
                                <td><?php echo nl2br(htmlspecialchars($app['verifier_remarks'])); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($app['approver_remarks'])): ?>
                            <tr>
                                <th>Approver Remarks</th>
                                <td><?php echo nl2br(htmlspecialchars($app['approver_remarks'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="box box-default">
                <div class="box-body">
                    <div class="callout callout-info" style="margin:0">
                        <p><i class="fa fa-info-circle"></i> You have not submitted any scholarship application yet.</p>
                    </div>
                </div>
                <div class="box-footer">
                    <a href="<?php echo site_url('public_admission/scholarship'); ?>" class="btn btn-primary">
                        <i class="fa fa-graduation-cap"></i> Apply for Scholarship
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <div class="text-center" style="margin-top:10px;">
                <a href="<?php echo site_url('public_admission/scholarship'); ?>" class="btn btn-default">
                    <i class="fa fa-plus"></i> Apply for Another Scholarship
                </a>
                &nbsp;
                <a href="<?php echo site_url('public_admission/applicant_dashboard'); ?>" class="btn btn-default">
                    <i class="fa fa-home"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>
