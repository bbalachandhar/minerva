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

        <!-- ── Session filter ─────────────────────────────────────── -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body" style="padding:12px 15px;">
                        <form method="get" action="<?php echo site_url('coe/coe_event'); ?>" class="form-inline">
                            <div class="form-group" style="margin-right:10px;">
                                <label style="margin-right:6px;">Session:</label>
                                <select name="session_id" class="form-control input-sm" onchange="this.form.submit()">
                                    <?php foreach ($session_list as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php echo ($s['id'] == $selected_session) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['session']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_add')): ?>
                            <a href="<?php echo site_url('coe/coe_event/add?session_id=' . $selected_session); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus-circle"></i> New Exam Event
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Stats cards ────────────────────────────────────────── -->
        <div class="row">
            <div class="col-xs-6 col-sm-3">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Events</span>
                        <span class="info-box-number"><?php echo $stats['total']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-3">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-graduation-cap"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Main Exams</span>
                        <span class="info-box-number"><?php echo $stats['main']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-3">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Arrear Exams</span>
                        <span class="info-box-number"><?php echo $stats['arrear']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-3">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-refresh"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Supplementary</span>
                        <span class="info-box-number"><?php echo $stats['supplementary']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Events table ───────────────────────────────────────── -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Exam Events</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-hover" id="events-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Event Name</th>
                                    <th>Category</th>
                                    <th>Mode</th>
                                    <th style="width:60px;">Batches</th>
                                    <th>Date Range</th>
                                    <th>Workflow Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($events)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                            No exam events for this session.
                                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_add')): ?>
                                                <a href="<?php echo site_url('coe/coe_event/add?session_id=' . $selected_session); ?>">Create one now</a>.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($events as $i => $ev):
                                        $total_bc  = (int)$ev->batch_count;
                                        $subj_done = (int)$ev->batches_with_subjects;
                                        $apps_done = (int)$ev->batches_with_apps;
                                        $elig_done = (int)$ev->batches_with_eligibility;
                                        $ht_done   = (int)$ev->batches_with_halltickets;

                                        $cat_map = ['main' => 'label-primary', 'arrear' => 'label-danger', 'supplementary' => 'label-warning'];
                                        $cat_cls = $cat_map[$ev->exam_category] ?? 'label-default';
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ev->name); ?></strong>
                                            <?php if ($ev->description): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($ev->description); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="label <?php echo $cat_cls; ?>"><?php echo ucfirst($ev->exam_category); ?></span></td>
                                        <td><span class="label label-default"><?php echo ucfirst($ev->exam_type); ?></span></td>
                                        <td class="text-center">
                                            <?php if ($total_bc > 0): ?>
                                                <a href="<?php echo site_url('coe/coe_event/manage/' . $ev->id); ?>" class="badge bg-blue" title="View batches"><?php echo $total_bc; ?></a>
                                            <?php else: ?>
                                                <span class="badge" style="background:#aaa;" title="No batches added yet">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <?php if ($ev->earliest_date): ?>
                                                <?php echo date('d M Y', strtotime($ev->earliest_date)); ?>
                                                <?php if ($ev->latest_date && $ev->latest_date !== $ev->earliest_date): ?>
                                                    &ndash;<br><?php echo date('d M Y', strtotime($ev->latest_date)); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($total_bc === 0): ?>
                                                <small class="text-muted"><i class="fa fa-info-circle"></i> Add batches first</small>
                                            <?php else:
                                                $steps = [
                                                    ['icon' => 'book',         'label' => 'Subjects',     'done' => $subj_done],
                                                    ['icon' => 'users',        'label' => 'Applications', 'done' => $apps_done],
                                                    ['icon' => 'check-circle', 'label' => 'Eligibility',  'done' => $elig_done],
                                                    ['icon' => 'ticket',       'label' => 'Hall Tickets', 'done' => $ht_done],
                                                ];
                                                foreach ($steps as $step):
                                                    $all  = ($step['done'] === $total_bc);
                                                    $none = ($step['done'] === 0);
                                                    $cls  = $all ? 'text-success' : ($none ? 'text-danger' : 'text-warning');
                                                    $ico  = $all ? 'check' : ($none ? 'times' : 'exclamation');
                                            ?>
                                                <span class="<?php echo $cls; ?>" style="margin-right:8px;white-space:nowrap;"
                                                      title="<?php echo $step['label']; ?>: <?php echo $step['done']; ?>/<?php echo $total_bc; ?> batches">
                                                    <i class="fa fa-<?php echo $ico; ?>-circle"></i>
                                                    <small><?php echo $step['label']; ?><?php if (!$all && !$none): ?>&nbsp;(<?php echo $step['done']; ?>/<?php echo $total_bc; ?>)<?php endif; ?></small>
                                                </span>
                                            <?php endforeach; endif; ?>
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <a href="<?php echo site_url('coe/coe_event/manage/' . $ev->id); ?>" class="btn btn-info btn-xs">
                                                <i class="fa fa-list"></i> Manage
                                            </a>
                                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_edit')): ?>
                                            <a href="<?php echo site_url('coe/coe_event/edit/' . $ev->id); ?>" class="btn btn-default btn-xs" title="Edit">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_event', 'can_delete') && $total_bc === 0): ?>
                                            <a href="<?php echo site_url('coe/coe_event/delete/' . $ev->id); ?>"
                                               class="btn btn-danger btn-xs confirm-delete" title="Deactivate">
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
    $(document).on('click', '.confirm-delete', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Deactivate this exam event?',
            text:  'It can be re-activated manually. Cannot delete if batches exist.',
            icon:  'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deactivate'
        }).then(function(r) { if (r.value) window.location = url; });
    });
});
</script>
