<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="content-wrapper">
<section class="content-header">
    <h1><i class="fa fa-users" style="color:#4f46e5;"></i> <?php echo htmlspecialchars($exam->exam); ?> — Candidates</h1>
</section>
<section class="content">
    <div style="margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
        <a href="<?php echo site_url('admin/scholarshipexam'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Scholarship Exams</a>
        <span style="font-size:14px; font-weight:600; color:#4f46e5;"><?php echo count($candidates); ?> candidate(s)</span>
    </div>

    <div class="box box-primary">
        <div class="box-body">
            <div class="table-responsive" style="overflow-x:auto;">
                <table class="table table-striped table-bordered table-hover example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ref No</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>School</th>
                            <th>Registered</th>
                            <th>Source</th>
                            <th>Exam Status</th>
                            <th class="noExport">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($candidates)): $i = 1; foreach ($candidates as $c): ?>
                        <tr id="row-<?php echo $c->assignment_id; ?>">
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($c->reference_no); ?></strong></td>
                            <td><?php echo htmlspecialchars(trim($c->firstname . ' ' . $c->lastname)); ?></td>
                            <td><?php echo htmlspecialchars(($c->course_name ?: '—') . ($c->course_code ? ' (' . $c->course_code . ')' : '')); ?></td>
                            <td><?php echo htmlspecialchars($c->mobileno ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($c->email ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($c->previous_school ?: '—'); ?></td>
                            <td><?php echo $c->created_at ? date('d M Y', strtotime($c->created_at)) : '—'; ?></td>
                            <td><?php echo ($c->source === 'scholarship') ? '<span class="label label-primary">Scholarship</span>' : '<span class="label label-default">Admission</span>'; ?></td>
                            <td>
                                <?php if ($c->is_attempted): ?>
                                    <span class="label label-success">Attempted</span>
                                    <?php if ($c->rank): ?><span class="label label-info">Rank #<?php echo $c->rank; ?></span><?php endif; ?>
                                <?php else: ?>
                                    <span class="label label-warning">Not Attempted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($this->rbac->hasPrivilege('scholarship_exam', 'can_delete')): ?>
                                <button class="btn btn-danger btn-xs" onclick="removeCandidate(<?php echo $c->assignment_id; ?>, '<?php echo htmlspecialchars(addslashes(trim($c->firstname . ' ' . $c->lastname)), ENT_QUOTES); ?>')">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="11" class="text-center text-muted" style="padding:30px;"><i class="fa fa-info-circle"></i> No candidates registered yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
</div>
<script>
function removeCandidate(assignmentId, name) {
    if (!confirm('Are you sure you want to remove "' + name + '" from this exam?\n\nThis will also delete their exam results and attempts.')) return;
    $.post('<?php echo site_url("admin/scholarshipexam/remove_candidate"); ?>', {
        assignment_id: assignmentId,
        <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
    }, function(res) {
        if (res.status === 'success') {
            $('#row-' + assignmentId).fadeOut(300, function() { $(this).remove(); });
            successMsg(res.message);
        } else {
            errorMsg(res.message);
        }
    }, 'json');
}
</script>
