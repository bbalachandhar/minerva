<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-clock-o"></i> Pending One-Time Deductions
            <small>Approve or reject uploaded one-time deductions</small>
        </h1>
    </section>

    <section class="content">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Pending Entries</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo site_url('admin/payroll/approve_all_onetime_deductions'); ?>" class="btn btn-success btn-xs" onclick="return confirm('Approve all pending one-time deductions?');">
                        <i class="fa fa-check-square-o"></i> Approve All
                    </a>
                    <a href="<?php echo site_url('admin/payroll/reject_all_onetime_deductions'); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Reject all pending one-time deductions?');">
                        <i class="fa fa-times-circle"></i> Reject All
                    </a>
                    <a href="<?php echo site_url('admin/payroll/bulk_onetime_deduction'); ?>" class="btn btn-primary btn-xs">
                        <i class="fa fa-upload"></i> New Upload
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Name</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Deduction Type</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                            <th style="width: 170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($deductions)): ?>
                        <?php foreach ($deductions as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['employee_id'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                                <td><?php echo (int) ($row['month'] ?? 0); ?></td>
                                <td><?php echo (int) ($row['year'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($row['deduction_type'] ?? ''); ?></td>
                                <td><?php echo number_format((float) ($row['amount'] ?? 0), 2); ?></td>
                                <td><?php echo htmlspecialchars($row['remarks'] ?? ''); ?></td>
                                <td>
                                    <a href="<?php echo site_url('admin/payroll/approve_onetime_deduction/' . (int) $row['id']); ?>" class="btn btn-success btn-xs" onclick="return confirm('Approve this one-time deduction?');">
                                        <i class="fa fa-check"></i> Approve
                                    </a>
                                    <a href="<?php echo site_url('admin/payroll/reject_onetime_deduction/' . (int) $row['id']); ?>" class="btn btn-danger btn-xs" onclick="return confirm('Reject this one-time deduction?');">
                                        <i class="fa fa-times"></i> Reject
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No pending one-time deductions found.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
