<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Substitution Duty Chart — <?php echo date('d M Y', strtotime($date)); ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 12px; color: #222; padding: 20px; }
.page-header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #2c3e50; padding-bottom: 12px; }
.page-header h2 { font-size: 17px; font-weight: bold; color: #2c3e50; }
.page-header h3 { font-size: 13px; color: #555; font-weight: normal; margin-top: 4px; }
.page-header .date-line { font-size: 13px; font-weight: bold; color: #c0392b; margin-top: 6px; }

.period-block { margin-bottom: 16px; break-inside: avoid; }
.period-heading {
  background: #2c3e50; color: #fff;
  padding: 5px 12px; font-size: 12px; font-weight: bold;
  border-radius: 3px 3px 0 0;
}
table { width: 100%; border-collapse: collapse; }
table th { background: #ecf0f1; color: #2c3e50; font-weight: bold; padding: 5px 8px; border: 1px solid #bdc3c7; text-align: left; font-size: 11px; }
table td { padding: 5px 8px; border: 1px solid #bdc3c7; font-size: 11px; vertical-align: middle; }
table tr:nth-child(even) td { background: #fafafa; }
.sub-name { font-weight: bold; color: #16a085; }
.absent-name { color: #c0392b; }
.unassigned { color: #e74c3c; font-style: italic; }
.no-data { text-align: center; padding: 30px; color: #888; font-size: 13px; border: 1px dashed #ccc; border-radius: 4px; margin-top: 10px; }

.footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; text-align: right; font-size: 10px; color: #aaa; }
.signature-row { display: flex; justify-content: space-between; margin-top: 30px; }
.signature-box { text-align: center; width: 30%; }
.signature-box .sig-line { border-top: 1px solid #555; padding-top: 4px; font-size: 10px; color: #555; margin-top: 24px; }

@media print {
  body { padding: 10px; }
  .no-print { display: none; }
  .period-block { break-inside: avoid; }
}
</style>
</head>
<body onload="window.print()">

<?php if (!empty($header_img_url)): ?>
<div style="border-bottom:2px solid #2c3e50;margin-bottom:14px;padding-bottom:10px;">
  <img src="<?php echo htmlspecialchars($header_img_url); ?>" style="width:100%;max-height:140px;object-fit:contain;display:block;" alt="Header">
</div>
<?php endif; ?>

<div class="page-header" style="<?php echo !empty($header_img_url) ? 'border-top:none;' : ''; ?>">
  <?php if (empty($header_img_url) && !empty($school_name)): ?>
  <h2><?php echo htmlspecialchars($school_name); ?></h2>
  <?php endif; ?>
  <h3>Substitution Duty Chart</h3>
  <div class="date-line"><?php echo date('l, d F Y', strtotime($date)); ?></div>
</div>

<?php if (empty($substitutions)): ?>
  <div class="no-data">
    <strong>No substitutions recorded for <?php echo date('d M Y', strtotime($date)); ?></strong><br>
    <span style="font-size:11px;">All teachers are present or no duties have been assigned.</span>
  </div>
<?php else: ?>

<?php foreach ($by_period as $sort => $rows): ?>
<?php $first = $rows[0]; ?>
<div class="period-block">
  <div class="period-heading">
    <?php echo htmlspecialchars($first->period_name ?? 'Period'); ?>
    <?php if ($first->start_time): ?>&nbsp;&mdash;&nbsp;<?php echo date('h:i A', strtotime($first->start_time)); ?><?php endif; ?>
    &nbsp;<span style="font-weight:normal;font-size:10px;">(<?php echo count($rows); ?> substitution<?php echo count($rows)>1?'s':''; ?>)</span>
  </div>
  <table>
    <thead>
      <tr>
        <th style="width:22%;">Absent Teacher</th>
        <th style="width:18%;">Class</th>
        <th style="width:22%;">Subject</th>
        <th style="width:22%;">Substitute Teacher</th>
        <th style="width:16%;">Type / Note</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td class="absent-name"><?php echo htmlspecialchars($r->absent_name.' '.($r->absent_surname??'')); ?></td>
        <td><?php echo htmlspecialchars(($r->class_name??'').' '.($r->section_name??'')); ?></td>
        <td><?php echo htmlspecialchars($r->subject_name ?? 'N/A'); ?></td>
        <td>
          <?php if ($r->sub_name): ?>
            <span class="sub-name"><?php echo htmlspecialchars($r->sub_name.' '.($r->sub_surname??'')); ?></span>
          <?php else: ?>
            <span class="unassigned">⚠ Unassigned</span>
          <?php endif; ?>
        </td>
        <td>
          <span style="font-size:10px;color:#555;"><?php echo $r->substitution_type === 'auto_suggested' ? 'Auto' : 'Manual'; ?></span>
          <?php if ($r->note): ?><br><span style="font-size:10px;color:#888;"><?php echo htmlspecialchars($r->note); ?></span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endforeach; ?>

<div class="signature-row">
  <div class="signature-box"><div class="sig-line">Prepared by</div></div>
  <div class="signature-box"><div class="sig-line">Checked by</div></div>
  <div class="signature-box"><div class="sig-line">Principal</div></div>
</div>

<?php endif; ?>

<div class="footer">
  Printed on <?php echo date('d M Y, h:i A'); ?>
  <?php if (!empty($school_name)): ?> &nbsp;|&nbsp; <?php echo htmlspecialchars($school_name); ?><?php endif; ?>
</div>

<div class="no-print" style="text-align:center;margin-top:20px;">
  <button onclick="window.print()" style="padding:8px 20px;font-size:13px;cursor:pointer;">
    Print / Save as PDF
  </button>
</div>

</body>
</html>
