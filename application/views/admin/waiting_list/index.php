<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">
<section class="content-header">
    <h1><i class="fa fa-clock-o" style="color:#f59e0b;"></i> Waiting List
        <small><?php echo count($waiting_list); ?> application(s)</small>
    </h1>
</section>

<section class="content">
    <?php if ($this->session->flashdata('msg')): ?>
        <?php echo $this->session->flashdata('msg'); ?>
    <?php endif; ?>

    <div class="row" style="margin-bottom:14px;">
        <div class="col-xs-12 col-sm-4">
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px 20px; border-left:4px solid #f59e0b;">
                <div style="font-size:11px; text-transform:uppercase; color:#94a3b8; font-weight:600; letter-spacing:0.5px;">Total Waiting</div>
                <div style="font-size:28px; font-weight:800; color:#f59e0b;"><?php echo count($waiting_list); ?></div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4">
            <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="btn btn-default" style="margin-top:16px;">
                <i class="fa fa-arrow-left"></i> Back to Admissions
            </a>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Waiting List Applications</h3>
        </div>
        <div class="box-body">
            <div class="table-responsive" style="overflow-x:auto;">
                <table class="table table-striped table-bordered table-hover example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo $this->lang->line('reference_no') ?: 'Ref No'; ?></th>
                            <th><?php echo $this->lang->line('student_name') ?: 'Student Name'; ?></th>
                            <th><?php echo $this->lang->line('class') ?: 'Course'; ?></th>
                            <th><?php echo $this->lang->line('mobile') ?: 'Mobile'; ?></th>
                            <th>Email</th>
                            <th>Guardian</th>
                            <th><?php echo $this->lang->line('date') ?: 'Applied On'; ?></th>
                            <th class="text-right noExport"><?php echo $this->lang->line('action') ?: 'Action'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($waiting_list)): ?>
                        <?php $i = 1; foreach ($waiting_list as $row): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['reference_no']); ?></strong></td>
                            <td><?php echo htmlspecialchars(trim(($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? '') . ' ' . ($row['lastname'] ?? ''))); ?></td>
                            <td><?php echo htmlspecialchars($row['course_name'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($row['mobileno'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($row['guardian_name'] ?? '—'); ?></td>
                            <td><?php echo !empty($row['created_at']) ? date($this->customlib->getSchoolDateFormat(), strtotime($row['created_at'])) : '—'; ?></td>
                            <td class="text-right" style="white-space:nowrap;">
                                <?php if ($this->rbac->hasPrivilege('online_admission', 'can_edit')): ?>
                                <button type="button" class="btn btn-success btn-xs" onclick="activateApplication(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['reference_no']); ?>')" data-toggle="tooltip" title="Move to Active">
                                    <i class="fa fa-check"></i> Activate
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted" style="padding:30px;">
                            <i class="fa fa-check-circle fa-2x" style="color:#10b981;"></i><br>
                            No applications in waiting list.
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>

<script>
function activateApplication(id, refNo) {
    swal({
        title: 'Activate Application?',
        text: 'Move application #' + refNo + ' from waiting list to active admissions?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Activate',
        cancelButtonText: 'Cancel'
    }, function(isConfirm) {
        if (isConfirm) {
            $.post('<?php echo site_url("admin/waiting_list/activate/"); ?>' + id,
                {'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'},
                function(resp) {
                    if (resp.status === 'success') {
                        swal({title: 'Activated!', text: resp.message, type: 'success'}, function() { location.reload(); });
                    } else {
                        swal('Error', resp.message, 'error');
                    }
                }, 'json'
            );
        }
    });
}
</script>
