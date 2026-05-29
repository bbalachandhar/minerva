<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-warning"></i> UFM Incidents
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_ufm'); ?>"><i class="fa fa-arrow-left"></i> Back</a></li>
            <?php if ($this->rbac->hasPrivilege('coe_ufm', 'can_add')): ?>
            <li>
                <a href="<?php echo site_url('coe/coe_ufm/report/' . $batch_exam_id); ?>" class="btn btn-xs btn-warning">
                    <i class="fa fa-plus"></i> Report Incident
                </a>
            </li>
            <?php endif; ?>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <form method="get">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Filter by Status</label>
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            <option value="reported"      <?php echo $this->input->get('status')=='reported'      ? 'selected' : ''; ?>>Reported</option>
                                            <option value="under_review"  <?php echo $this->input->get('status')=='under_review'  ? 'selected' : ''; ?>>Under Review</option>
                                            <option value="penalised"     <?php echo $this->input->get('status')=='penalised'     ? 'selected' : ''; ?>>Penalised</option>
                                            <option value="dismissed"     <?php echo $this->input->get('status')=='dismissed'     ? 'selected' : ''; ?>>Dismissed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border" style="display:flex;justify-content:space-between;align-items:center;">
                        <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Incidents (<?php echo count($incidents); ?>)</h3>
                        <a href="<?php echo site_url('coe/coe_ufm'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Events
                        </a>
                    </div>
                    <div class="box-body">
                        <?php if (empty($incidents)): ?>
                            <p class="text-muted text-center">No UFM incidents recorded for this exam event.</p>
                        <?php else: ?>
                        <?php
                            $status_map = [
                                'reported'     => 'label-danger',
                                'under_review' => 'label-warning',
                                'penalised'    => 'label-default',
                                'dismissed'    => 'label-success',
                            ];
                            $type_labels = [
                                'copying'          => 'Copying',
                                'mobile_phone'     => 'Mobile Phone',
                                'impersonation'    => 'Impersonation',
                                'unfair_material'  => 'Unfair Material',
                                'communication'    => 'Communication',
                                'other'            => 'Other',
                            ];
                        ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Hall Ticket</th>
                                    <th>Student</th>
                                    <th>Hall</th>
                                    <th>Date / Session</th>
                                    <th>Incident Type</th>
                                    <th>Reported By</th>
                                    <th>Status</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incidents as $i => $inc): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($inc->hall_ticket_no); ?></strong></td>
                                    <td><?php echo htmlspecialchars($inc->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($inc->hall_name ?? '—'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($inc->exam_date)); ?> / <?php echo $inc->session_slot; ?></td>
                                    <td><span class="label label-danger"><?php echo $type_labels[$inc->incident_type] ?? ucfirst($inc->incident_type); ?></span></td>
                                    <td><?php echo htmlspecialchars($inc->reported_by_name ?? '—'); ?></td>
                                    <td>
                                        <span class="label <?php echo $status_map[$inc->status] ?? 'label-default'; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $inc->status)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-info ufm-view-btn"
                                            data-id="<?php echo $inc->id; ?>"
                                            data-url="<?php echo site_url('coe/coe_ufm/modal_content/' . $inc->id); ?>"
                                            data-view-url="<?php echo site_url('coe/coe_ufm/view/' . $inc->id); ?>"
                                            title="View Incident">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <?php if ($this->rbac->hasPrivilege('coe_ufm', 'can_delete')): ?>
                                        <a href="<?php echo site_url('coe/coe_ufm/delete/' . $inc->id); ?>" class="btn btn-xs btn-danger"
                                           onclick="return confirm('Delete this UFM incident?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
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

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'ufm']); ?>

<!-- UFM Incident View Modal -->
<div class="modal fade" id="ufm-view-modal" tabindex="-1" role="dialog" aria-labelledby="ufmModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#c9302c;color:#fff;border-radius:5px 5px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.9;">&times;</button>
                <h4 class="modal-title" id="ufmModalLabel">
                    <i class="fa fa-exclamation-triangle"></i>
                    UFM Incident <span id="ufm-modal-incident-id"></span>
                </h4>
            </div>
            <div class="modal-body" id="ufm-modal-body" style="padding:20px;">
                <div class="text-center" style="padding:40px;">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="ufm-modal-full-link" target="_blank" class="btn btn-default btn-sm pull-left">
                    <i class="fa fa-external-link"></i> Open Full Page
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.ufm-view-btn', function () {
    var $btn = $(this);
    var id      = $btn.data('id');
    var url     = $btn.data('url');
    var viewUrl = $btn.data('view-url');

    $('#ufm-modal-incident-id').text('#' + id);
    $('#ufm-modal-full-link').attr('href', viewUrl);
    $('#ufm-modal-body').html(
        '<div class="text-center" style="padding:40px;"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i></div>'
    );
    $('#ufm-view-modal').modal('show');

    $.ajax({
        url: url,
        type: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function (html) {
            $('#ufm-modal-body').html(html);
        },
        error: function () {
            $('#ufm-modal-body').html(
                '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Failed to load incident details.</div>'
            );
        }
    });
});
</script>
