<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar-check-o"></i> CoE Exam Events</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li class="active">Exam Events</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Exam Events</h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_add')): ?>
                            <a href="<?php echo site_url('coe/coe_event/add'); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus-circle"></i> New Event
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped" id="events-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Category</th>
                                    <th>Mode</th>
                                    <th>Classes</th>
                                    <th>Date Range</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($events)): ?>
                                    <tr><td colspan="8" class="text-center text-muted">No exam events found. <a href="<?php echo site_url('coe/coe_event/add'); ?>">Create one</a>.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($events as $i => $ev): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ev->name); ?></strong>
                                            <?php if ($ev->description): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($ev->description); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $cat_map = ['main' => 'label-primary', 'arrear' => 'label-danger', 'supplementary' => 'label-warning'];
                                            $cat_cls = $cat_map[$ev->exam_category] ?? 'label-default';
                                            ?>
                                            <span class="label <?php echo $cat_cls; ?>"><?php echo ucfirst($ev->exam_category); ?></span>
                                        </td>
                                        <td>
                                            <span class="label label-default"><?php echo ucfirst($ev->exam_type); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge"><?php echo (int)$ev->batch_count; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($ev->earliest_date): ?>
                                                <?php echo date('d M Y', strtotime($ev->earliest_date)); ?>
                                                <?php if ($ev->latest_date && $ev->latest_date !== $ev->earliest_date): ?>
                                                    &ndash; <?php echo date('d M Y', strtotime($ev->latest_date)); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ev->is_active): ?>
                                                <span class="label label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-default">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('coe/coe_event/manage/' . $ev->id); ?>" class="btn btn-info btn-xs">
                                                <i class="fa fa-list"></i> Manage
                                            </a>
                                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_edit')): ?>
                                            <a href="<?php echo site_url('coe/coe_event/edit/' . $ev->id); ?>" class="btn btn-warning btn-xs">
                                                <i class="fa fa-pencil"></i> Edit
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_delete') && (int)$ev->batch_count === 0): ?>
                                            <a href="<?php echo site_url('coe/coe_event/delete/' . $ev->id); ?>"
                                               class="btn btn-danger btn-xs"
                                               onclick="return confirm('Deactivate this exam event?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(function () {
    $('#events-table').DataTable({ order: [[0, 'desc']], pageLength: 25 });
});
</script>
