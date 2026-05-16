<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Apply for Arrear / Supplementary Exam</h1>
    </section>
    <section class="content">

<?php $msg = $this->session->flashdata('msg'); if ($msg) echo $msg; ?>

<!-- Past Applications -->
<?php if (!empty($applications)): ?>
<div class="box box-default collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> My Previous Applications</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-hover" style="font-size:13px">
            <thead><tr>
                <th>Subject</th>
                <th>Type</th>
                <th>Applied</th>
                <th>Status</th>
                <th>Reviewer Remarks</th>
            </tr></thead>
            <tbody>
            <?php foreach ($applications as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a->subject_name ?? '—') ?> <small class="text-muted">(<?= htmlspecialchars($a->subject_code ?? '') ?>)</small></td>
                <td><?= ucfirst($a->application_type) ?></td>
                <td><?= date('d M Y', strtotime($a->applied_at)) ?></td>
                <td>
                    <?php if ($a->status === 'pending'): ?>
                        <span class="label label-warning">Pending</span>
                    <?php elseif ($a->status === 'approved'): ?>
                        <span class="label label-success">Approved</span>
                    <?php else: ?>
                        <span class="label label-danger">Rejected</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($a->reviewer_remarks ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Apply Form -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-plus-circle"></i> Submit New Application</h3>
    </div>
    <div class="box-body">

        <?php if (empty($failed_subs)): ?>
            <div class="callout callout-info">You have no failed subjects available to apply for arrear.</div>
        <?php else: ?>

        <form method="post" action="<?= site_url('user_coe/apply_arrear') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

            <?php
            // Group by batch_exam_id
            $grouped = [];
            foreach ($failed_subs as $sub) {
                $grouped[$sub->batch_exam_id][] = $sub;
            }
            ?>

            <?php foreach ($grouped as $bid => $subs): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Exam Event #<?= $bid ?></h4>
                </div>
                <div class="panel-body">
                    <input type="hidden" name="batch_exam_id" value="<?= $bid ?>">
                    <table class="table table-sm table-bordered" style="font-size:13px">
                        <thead><tr>
                            <th><input type="checkbox" onclick="toggleAll(this, '<?= $bid ?>')"> Select All</th>
                            <th>Subject Code</th>
                            <th>Subject</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Grade</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($subs as $sub): ?>
                        <tr>
                            <td><input type="checkbox" name="subject_ids[]" value="<?= $sub->subject_id ?>" class="grp-<?= $bid ?>"></td>
                            <td><?= htmlspecialchars($sub->subject_code ?? '') ?></td>
                            <td><?= htmlspecialchars($sub->subject_name ?? '') ?></td>
                            <td><?= $sub->external_marks ?></td>
                            <td><?= $sub->total_marks ?></td>
                            <td><strong class="text-danger"><?= $sub->grade ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Application Type</label>
                        <select name="application_type" class="form-control">
                            <option value="arrear">Arrear Exam</option>
                            <option value="supplementary">Supplementary Exam</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Remarks (optional)</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Any remarks for the CoE office...">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success"><i class="fa fa-paper-plane"></i> Submit Application</button>
        </form>

        <?php endif; ?>
    </div>
</div>

    </section>
</div>

<script>
function toggleAll(master, gid) {
    $('.grp-' + gid).prop('checked', master.checked);
}
</script>
