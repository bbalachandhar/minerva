<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-plus-circle"></i> Salary Increment Management
            <small>Add, approve, and manage employee salary increments</small>
        </h1>
    </section>

    <section class="content">
        <!-- Flash Messages -->
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

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Search & Filter Increments</h3>
                    </div>

                    <form method="POST" class="form-horizontal">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Staff Member</label>
                                        <div class="col-sm-9">
                                            <select name="staff_id" class="form-control select2" id="staff_id">
                                                <option value="">Select Staff</option>
                                                <?php if (!empty($stafflist)) {
                                                    foreach ($stafflist as $staff) {
                                                        $selected = ($staff_id == $staff['id']) ? 'selected' : '';
                                                        echo "<option value='" . $staff['id'] . "' $selected>" . $staff['name'] . " (" . $staff['employee_id'] . ")</option>";
                                                    }
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Status</label>
                                        <div class="col-sm-8">
                                            <select name="status_filter" class="form-control">
                                                <option value="">All</option>
                                                <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Approved" <?php echo ($status_filter === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                                <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5" style="text-align: right; padding-top: 0;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                    <a href="<?php echo site_url('admin/payroll/add_increment'); ?>" class="btn btn-success">
                                        <i class="fa fa-plus"></i> Add New Increment
                                    </a>
                                    <a href="<?php echo site_url('admin/payroll/pending_increments'); ?>" class="btn btn-warning">
                                        <i class="fa fa-clock-o"></i> Pending Approvals
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Salary Increments</h3>
                    </div>

                    <div class="box-body">
                        <?php if (!empty($increments)): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr style="background: #ecf0f1;">
                                    <th style="width: 5%;">ID</th>
                                    <th style="width: 20%;">Staff Member</th>
                                    <th style="width: 12%;">Effective Date</th>
                                    <th style="width: 15%;">Increment Amount</th>
                                    <th style="width: 10%;">Type</th>
                                    <th style="width: 15%;">Merge With</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 11%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($increments as $increment): ?>
                                <tr>
                                    <td><?php echo $increment['id']; ?></td>
                                    <td>
                                        <strong><?php echo isset($increment['name']) ? $increment['name'] : 'N/A'; ?></strong><br>
                                        <small style="color: #7f8c8d;"><?php echo isset($increment['employee_id']) ? '(' . $increment['employee_id'] . ')' : ''; ?></small>
                                    </td>
                                    <td><?php echo date('d-M-Y', strtotime($increment['effective_date'])); ?></td>
                                    <td>
                                        <?php if ($increment['increment_type'] === 'Fixed'): ?>
                                            <span class="label label-info"><?php echo $currency_symbol . number_format($increment['increment_amount'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="label label-info"><?php echo $increment['increment_percentage']; ?>%</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: <?php echo ($increment['increment_type'] === 'Fixed') ? '#3498db' : '#9b59b6'; ?>;">
                                            <?php echo $increment['increment_type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label label-default">
                                            <?php echo ucfirst(str_replace('_', ' ', $increment['merge_with'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $status_color = [
                                                'Pending' => 'warning',
                                                'Approved' => 'success',
                                                'Rejected' => 'danger'
                                            ];
                                            $color = $status_color[$increment['approval_status']] ?? 'default';
                                        ?>
                                        <span class="label label-<?php echo $color; ?>">
                                            <?php echo $increment['approval_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($increment['approval_status'] === 'Pending' && $this->rbac->hasPrivilege('staff_payroll', 'can_delete')): ?>
                                            <a href="<?php echo site_url('admin/payroll/delete_increment/' . $increment['id']); ?>" 
                                               class="btn btn-xs btn-danger" 
                                               onclick="return confirm('Delete this increment?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No salary increments found. 
                            <a href="<?php echo site_url('admin/payroll/add_increment'); ?>">Add a new increment</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function() {
    $('.select2').select2();
});
</script>
