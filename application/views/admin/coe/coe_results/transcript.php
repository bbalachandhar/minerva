<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * CGPA Transcript — printable / screen view
 * Works both as HTML page and as PDF (via ?print=1)
 */
$is_print  = (bool) $this->input->get('print');
$student   = $transcript['student'];
$semesters = $transcript['semesters'];
$cgpa      = $transcript['cgpa'];
$total_cr  = $transcript['total_credits'];

// Try to fetch institution name
$inst = $this->db->select('school_name, address, phone, email, logo')->limit(1)->get('school_settings')->row();
$school_name = $inst->school_name ?? 'Institution';
?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>CGPA Transcript — <?= htmlspecialchars($student->admission_no) ?></title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
.header { text-align: center; border-bottom: 2px solid #1a3c5e; padding-bottom: 8px; margin-bottom: 16px; }
.header h2 { margin: 0; color: #1a3c5e; }
.header p { margin: 2px 0; font-size: 11px; }
.student-info { background: #f5f7fa; padding: 8px 12px; border: 1px solid #ddd; margin-bottom: 12px; }
.student-info table { width: 100%; }
.student-info td { padding: 3px 6px; }
.semester-title { background: #1a3c5e; color: #fff; padding: 4px 8px; font-weight: bold; font-size: 12px; margin-top: 12px; }
table.marks { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 11px; }
table.marks th { background: #e8edf3; padding: 4px 6px; border: 1px solid #ccc; }
table.marks td { padding: 4px 6px; border: 1px solid #ccc; }
.pass { color: #27ae60; font-weight: bold; }
.fail { color: #c0392b; font-weight: bold; }
.sgpa-row { background: #f0f4fa; font-weight: bold; }
.cgpa-box { background: #1a3c5e; color: #fff; padding: 8px 16px; margin-top: 16px; font-size: 14px; text-align: right; }
.no-print { /* print hides these */ }
@media print { .no-print { display: none; } }
</style>
</head>
<body>

<div class="header">
    <h2><?= htmlspecialchars($school_name) ?></h2>
    <p>CUMULATIVE GRADE POINT AVERAGE (CGPA) TRANSCRIPT</p>
    <p style="font-size:10px">Anna University Pattern — NEP 2020</p>
</div>

<?php if (!$is_print): ?>
<div class="no-print" style="margin-bottom:12px">
    <a href="<?= site_url('coe/coe_results/transcript/' . $student->id . '?print=1') ?>" class="btn btn-sm btn-primary" target="_blank">
        <i class="fa fa-print"></i> Print / Download PDF
    </a>
    <a href="javascript:history.back()" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back</a>
</div>
<?php endif; ?>

<div class="student-info">
    <table>
        <tr>
            <td><strong>Name:</strong> <?= htmlspecialchars($student->full_name) ?></td>
            <td><strong>Admission No:</strong> <?= htmlspecialchars($student->admission_no) ?></td>
            <td><strong>Date of Birth:</strong> <?= $student->dob ? date('d M Y', strtotime($student->dob)) : '—' ?></td>
        </tr>
    </table>
</div>

<?php if (empty($semesters)): ?>
    <p style="color:#888">No published results found for this student.</p>
<?php else: ?>

<?php $sem_num = 1; foreach ($semesters as $sem): ?>
<div class="semester-title">
    Semester <?= $sem_num++ ?> — <?= htmlspecialchars($sem->semester_name ?? '') ?>
    (<?= htmlspecialchars($sem->academic_year ?? '') ?>)
    <?php if ($sem->date_from): ?>
    &nbsp;|&nbsp; <?= date('M Y', strtotime($sem->date_from)) ?>
    <?php endif; ?>
</div>

<table class="marks">
    <thead>
        <tr>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Credits</th>
            <th>Internal</th>
            <th>External</th>
            <th>Total</th>
            <th>Grade</th>
            <th>Points</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sem->subjects as $sub): ?>
        <tr>
            <td><?= htmlspecialchars($sub->subject_code ?? '') ?></td>
            <td><?= htmlspecialchars($sub->subject_name ?? '') ?></td>
            <td><?= (int)($sub->credits ?? 4) ?></td>
            <td><?= number_format((float)$sub->internal_marks, 1) ?></td>
            <td><?= number_format((float)$sub->external_marks, 1) ?></td>
            <td><?= number_format((float)$sub->total_marks, 1) ?></td>
            <td><?= htmlspecialchars($sub->grade ?? '—') ?></td>
            <td><?= htmlspecialchars($sub->grade_points ?? '—') ?></td>
            <td class="<?= $sub->result_status === 'pass' ? 'pass' : 'fail' ?>">
                <?= strtoupper($sub->result_status ?? '—') ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr class="sgpa-row">
            <td colspan="2">SGPA</td>
            <td><?= number_format((float)$sem->total_credits, 0) ?> Cr</td>
            <td colspan="5"></td>
            <td><?= $sem->sgpa !== null ? number_format((float)$sem->sgpa, 2) : '—' ?></td>
        </tr>
    </tbody>
</table>
<?php endforeach; ?>

<div class="cgpa-box">
    CGPA (All Semesters, Weighted): &nbsp;
    <span style="font-size:18px"><?= $cgpa !== null ? number_format($cgpa, 2) : '—' ?></span>
    &nbsp; | Total Credits: <?= (int) $total_cr ?>
</div>

<?php endif; ?>

<p style="margin-top:20px; font-size:10px; color:#888; text-align:center">
    Generated on <?= date('d M Y, h:i A') ?> &nbsp;|&nbsp; This is a system-generated document.
</p>

</body>
</html>
