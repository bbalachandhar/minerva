<table class="table table-bordered table-condensed" style="margin-bottom:12px">
    <thead>
        <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Credits</th>
            <th>Internal</th>
            <th>External</th>
            <th>Moderation</th>
            <th>Total</th>
            <th>Grade</th>
            <th>GP</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $total_credits = 0; $earned_gp = 0;
        foreach ($results as $i => $r):
            $credits = $r->credits ?? 4;
            $total_credits += $credits;
            $earned_gp += $r->grade_points * $credits;
        ?>
        <tr class="<?php echo $r->result_status === 'fail' ? 'danger' : ''; ?>">
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($r->subject_code . ' — ' . $r->subject_name); ?></td>
            <td><?php echo $credits; ?></td>
            <td><?php echo number_format($r->internal_marks, 1); ?></td>
            <td><?php echo number_format($r->external_marks, 1); ?></td>
            <td><?php echo $r->moderation_applied ? '+'.number_format($r->moderation_applied,1) : '—'; ?></td>
            <td><strong><?php echo number_format($r->total_marks, 1); ?></strong></td>
            <td><strong><?php echo $r->grade; ?></strong></td>
            <td><?php echo $r->grade_points; ?></td>
            <td>
                <span class="label <?php echo $r->result_status==='pass'?'label-success':'label-danger'; ?>">
                    <?php echo ucfirst($r->result_status); ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col-sm-4">
        <div class="info-box <?php echo ($sgpa && $sgpa->arrear_count > 0) ? 'bg-yellow' : 'bg-green'; ?>">
            <span class="info-box-icon"><i class="fa fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">SGPA</span>
                <span class="info-box-number"><?php echo $sgpa ? number_format($sgpa->sgpa, 2) : '—'; ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-box bg-blue">
            <span class="info-box-icon"><i class="fa fa-bar-chart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">CGPA</span>
                <span class="info-box-number"><?php echo $sgpa ? number_format($sgpa->cgpa, 2) : '—'; ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-box <?php echo ($sgpa && $sgpa->arrear_count > 0) ? 'bg-red' : 'bg-green'; ?>">
            <span class="info-box-icon"><i class="fa fa-book"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Credits Earned</span>
                <span class="info-box-number"><?php echo $sgpa ? $sgpa->total_credits_earned.' / '.$sgpa->total_credits_registered : '—'; ?></span>
            </div>
        </div>
    </div>
</div>

<?php if ($sgpa && $sgpa->arrear_count > 0): ?>
<div class="alert alert-warning" style="margin-top:4px">
    <i class="fa fa-warning"></i>
    This student has <strong><?php echo $sgpa->arrear_count; ?></strong> arrear(s).
    <?php if ($sgpa->arrear_count > 0): ?>
        Credits earned: <strong><?php echo $sgpa->total_credits_earned; ?></strong> out of <?php echo $sgpa->total_credits_registered; ?>.
    <?php endif; ?>
</div>
<?php endif; ?>
