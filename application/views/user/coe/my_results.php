<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> My Exam Results &amp; Transcript</h1>
    </section>
    <section class="content">

<?php if (empty($transcript) || empty($transcript['student'])): ?>
    <div class="callout callout-warning">No published results found for your account.</div>
<?php else:
    $student   = $transcript['student'];
    $semesters = $transcript['semesters'];
    $cgpa      = $transcript['cgpa'];
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= htmlspecialchars($student->full_name ?? '') ?> — <?= htmlspecialchars($student->admission_no ?? '') ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('user_coe/my_transcript?print=1') ?>" class="btn btn-sm btn-default" target="_blank">
                <i class="fa fa-print"></i> Download PDF
            </a>
        </div>
    </div>
    <div class="box-body">
        <?php foreach ($semesters as $sem): ?>
        <h4 class="text-primary"><?= htmlspecialchars($sem['label'] ?? 'Semester') ?> &nbsp; <small>SGPA: <strong><?= number_format($sem['sgpa'] ?? 0, 2) ?></strong></small></h4>
        <table class="table table-sm table-bordered" style="font-size:13px">
            <thead><tr>
                <th>Code</th>
                <th>Subject</th>
                <th>Credits</th>
                <th>Internal</th>
                <th>External</th>
                <th>Total</th>
                <th>Grade</th>
                <th>Result</th>
            </tr></thead>
            <tbody>
            <?php foreach ($sem['subjects'] as $sub): ?>
            <tr class="<?= $sub->result_status === 'fail' ? 'danger' : '' ?>">
                <td><?= htmlspecialchars($sub->subject_code ?? '') ?></td>
                <td><?= htmlspecialchars($sub->subject_name ?? '') ?></td>
                <td><?= htmlspecialchars($sub->credits ?? '—') ?></td>
                <td><?= htmlspecialchars($sub->internal_marks ?? '—') ?></td>
                <td><?= htmlspecialchars($sub->external_marks ?? '—') ?></td>
                <td><?= htmlspecialchars($sub->total_marks ?? '—') ?></td>
                <td><strong><?= htmlspecialchars($sub->grade ?? '—') ?></strong></td>
                <td>
                    <?php if ($sub->result_status === 'pass'): ?>
                        <span class="label label-success">Pass</span>
                    <?php elseif ($sub->result_status === 'fail'): ?>
                        <span class="label label-danger">Fail</span>
                    <?php else: ?>
                        <span class="label label-default">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endforeach; ?>

        <div class="well well-sm text-right">
            <strong>Overall CGPA: <span class="text-primary" style="font-size:18px"><?= number_format($cgpa, 2) ?></span></strong>
            &nbsp;|&nbsp; Total Credits: <?= (int) $transcript['total_credits'] ?>
        </div>

    </div>
</div>

<?php endif; ?>

    </section>
</div>
