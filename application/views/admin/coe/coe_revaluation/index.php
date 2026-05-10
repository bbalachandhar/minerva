<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-refresh"></i> Revaluation<button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Revaluation</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Select Exam Event</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($events)): ?>
                            <p class="text-muted text-center">No exam events found.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr><th>#</th><th>Exam Group</th><th>Exam</th><th>Class</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $i => $ev): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($ev->exam_group_name); ?></td>
                                    <td><?php echo htmlspecialchars($ev->exam); ?></td>
                                    <td><?php echo htmlspecialchars($ev->class ?? '—'); ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_revaluation/listing/' . $ev->id); ?>"
                                           class="btn btn-xs btn-primary">
                                            <i class="fa fa-arrow-right"></i> View Requests
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'revaluation']); ?>
