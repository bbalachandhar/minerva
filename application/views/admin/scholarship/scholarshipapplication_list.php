<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> Scholarship Applications</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Scholarship Applications</li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Filter bar -->
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php
                        $statuses = [''=>'All', 'pending'=>'Pending', 'verified'=>'Verified', 'approved'=>'Approved', 'rejected'=>'Rejected'];
                        foreach ($statuses as $key => $label):
                            $active = ($filter_status === $key || ($key === '' && !$filter_status)) ? 'btn-primary' : 'btn-default';
                        ?>
                        <a href="<?php echo site_url('admin/scholarshipapplication' . ($key ? '?status=' . $key : '')); ?>"
                           class="btn btn-sm <?php echo $active; ?>"><?php echo $label; ?>
                           <?php if ($key !== ''): ?>
                           <span class="badge"><?php echo count(array_filter($applications, fn($a) => $a['status'] === $key)); ?></span>
                           <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="<?php echo site_url('admin/scholarshiptype'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-list"></i> Manage Types
                        </a>
                        <a href="<?php echo site_url('admin/scholarshipapplication/settings'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-cog"></i> Workflow Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow info -->
        <?php if ($settings): ?>
        <div class="callout callout-info" style="margin-bottom:15px">
            <strong>Workflow:</strong>
            Verifier: <strong><?php echo !empty($settings['verifier_id']) ? 'Staff #' . $settings['verifier_id'] : '<span class="text-danger">Not set</span>'; ?></strong>
            &nbsp;|&nbsp;
            Approver: <strong><?php echo !empty($settings['approver_id']) ? 'Staff #' . $settings['approver_id'] : '<span class="text-danger">Not set</span>'; ?></strong>
            &nbsp;&mdash; <a href="<?php echo site_url('admin/scholarshipapplication/settings'); ?>">Change</a>
        </div>
        <?php endif; ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?php echo $filter_status ? ucfirst($filter_status) . ' Applications' : 'All Applications'; ?>
                    <span class="badge"><?php echo count($applications); ?></span>
                </h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-hover table-striped example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference No</th>
                            <th>Applicant</th>
                            <th>Scholarship Type</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="noExport text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $i => $app): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($app['reference_no'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($app['firstname'] ?? '') . ' ' . ($app['lastname'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($app['scholarship_name'] ?? ''); ?></td>
                            <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                            <td><?php
                                $badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
                                $b = $badges[$app['status']] ?? 'default';
                            ?><span class="label label-<?php echo $b; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                            <td class="text-right">
                                <a href="<?php echo site_url('admin/scholarshipapplication/view/' . $app['id']); ?>"
                                   class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applications)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
