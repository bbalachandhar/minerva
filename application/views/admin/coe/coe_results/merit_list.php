<!-- Merit / Rank List -->
<style>
    @media print {
        .content-header, .main-header, .main-sidebar { display: none !important; }
        body, .content-wrapper { background: #fff; }
        .box { border: none !important; box-shadow: none !important; }
    }
    .rank-1 { background: #fff8e1; font-weight: bold; }
    .rank-2 { background: #f5f5f5; }
    .rank-3 { background: #fbe9e7; }
    .medal-1 { color: #f39c12; font-size: 16px; }
    .medal-2 { color: #95a5a6; font-size: 16px; }
    .medal-3 { color: #cd7f32; font-size: 16px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-trophy"></i> Merit / Rank List
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_results/listing/'.$batch_exam_id); ?>">Result Publication</a></li>
            <li class="active">Merit List</li>
        </ol>
        <div style="position:absolute;top:15px;right:15px">
            <a href="<?php echo site_url('coe/coe_results/listing/'.$batch_exam_id); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Results
            </a>
            &nbsp;
            <a href="<?php echo site_url('coe/coe_results/tabulation/'.$batch_exam_id); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-table"></i> Tabulation Sheet
            </a>
            &nbsp;
            <button class="btn btn-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
        </div>
    </section>

    <section class="content">

        <!-- Print Header -->
        <div style="text-align:center;margin-bottom:15px">
            <h4 style="margin:0">MERIT LIST &mdash; RANK REGISTER</h4>
            <p style="margin:3px 0"><strong><?php echo htmlspecialchars($event->exam_group_name); ?></strong>
                &mdash; <?php echo htmlspecialchars($event->exam); ?>
            </p>
            <?php if ($event->date_from): ?>
            <p style="margin:3px 0;font-size:12px">
                <?php echo date('d M Y', strtotime($event->date_from));
                if ($event->date_to) echo ' to '.date('d M Y', strtotime($event->date_to)); ?>
                &nbsp;|&nbsp; Session: <?php echo htmlspecialchars($event->session); ?>
            </p>
            <?php endif; ?>
        </div>

        <?php if (empty($students)): ?>
        <div class="callout callout-warning">
            <p>No SGPA data found. Please compute SGPA first from the Marks module.</p>
        </div>
        <?php else:
            $pass_count = count(array_filter($students, fn($s) => $s->result_status === 'pass'));
            $fail_count = count($students) - $pass_count;
            $sgpa_vals = array_filter(array_map(fn($s) => (float)$s->sgpa, $students), fn($v) => $v > 0);
            $avg_sgpa = count($sgpa_vals) > 0 ? round(array_sum($sgpa_vals)/count($sgpa_vals), 2) : 0;
            $max_sgpa = count($sgpa_vals) > 0 ? max($sgpa_vals) : 0;
        ?>

        <!-- Summary Cards -->
        <div class="row" style="margin-bottom:10px">
            <div class="col-md-2 col-sm-4">
                <div class="info-box" style="min-height:60px">
                    <span class="info-box-icon bg-aqua" style="height:60px;line-height:60px"><i class="fa fa-users"></i></span>
                    <div class="info-box-content" style="padding-top:8px">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number"><?php echo count($students); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="info-box" style="min-height:60px">
                    <span class="info-box-icon bg-green" style="height:60px;line-height:60px"><i class="fa fa-check"></i></span>
                    <div class="info-box-content" style="padding-top:8px">
                        <span class="info-box-text">Pass</span>
                        <span class="info-box-number"><?php echo $pass_count; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="info-box" style="min-height:60px">
                    <span class="info-box-icon bg-red" style="height:60px;line-height:60px"><i class="fa fa-times"></i></span>
                    <div class="info-box-content" style="padding-top:8px">
                        <span class="info-box-text">Fail / Arrear</span>
                        <span class="info-box-number"><?php echo $fail_count; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="info-box" style="min-height:60px">
                    <span class="info-box-icon bg-yellow" style="height:60px;line-height:60px"><i class="fa fa-trophy"></i></span>
                    <div class="info-box-content" style="padding-top:8px">
                        <span class="info-box-text">Highest SGPA</span>
                        <span class="info-box-number"><?php echo number_format($max_sgpa, 2); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="info-box" style="min-height:60px">
                    <span class="info-box-icon bg-blue" style="height:60px;line-height:60px"><i class="fa fa-calculator"></i></span>
                    <div class="info-box-content" style="padding-top:8px">
                        <span class="info-box-text">Average SGPA</span>
                        <span class="info-box-number"><?php echo number_format($avg_sgpa, 2); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="info-box" style="min-height:60px">
                    <span class="info-box-icon bg-purple" style="height:60px;line-height:60px"><i class="fa fa-percent"></i></span>
                    <div class="info-box-content" style="padding-top:8px">
                        <span class="info-box-text">Pass Rate</span>
                        <span class="info-box-number"><?php echo count($students)>0 ? round($pass_count/count($students)*100,1).'%' : '—'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-body" style="padding:0;overflow-x:auto">
                <table class="table table-bordered table-hover" style="margin-bottom:0;font-size:13px">
                    <thead>
                        <tr class="bg-light-blue-active text-white">
                            <th style="width:60px">Rank</th>
                            <th>Register No.</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>SGPA</th>
                            <th>CGPA</th>
                            <th>Credits Earned</th>
                            <th>Credits Reg.</th>
                            <th>Arrears</th>
                            <th>Result</th>
                            <th class="no-print">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $st):
                            $rankClass = '';
                            $medal = '';
                            if ($st->rank === 1) { $rankClass = 'rank-1'; $medal = '<i class="fa fa-trophy medal-1"></i>'; }
                            elseif ($st->rank === 2) { $rankClass = 'rank-2'; $medal = '<i class="fa fa-trophy medal-2"></i>'; }
                            elseif ($st->rank === 3) { $rankClass = 'rank-3'; $medal = '<i class="fa fa-trophy medal-3"></i>'; }
                        ?>
                        <tr class="<?php echo $rankClass; ?>">
                            <td class="text-center">
                                <?php echo $medal ? $medal.'&nbsp;' : ''; ?>
                                <strong><?php echo $st->rank; ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($st->register_no ?: $st->admission_no ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($st->student_name); ?></td>
                            <td><small><?php echo htmlspecialchars($st->class_name ?? '—'); ?></small></td>
                            <td>
                                <strong class="text-<?php echo (float)$st->sgpa >= 8.5 ? 'green' : ((float)$st->sgpa >= 6 ? 'yellow' : 'red'); ?>">
                                    <?php echo number_format($st->sgpa, 2); ?>
                                </strong>
                            </td>
                            <td><?php echo number_format($st->cgpa, 2); ?></td>
                            <td class="text-center"><?php echo $st->total_credits_earned ?? '—'; ?></td>
                            <td class="text-center"><?php echo $st->total_credits_registered ?? '—'; ?></td>
                            <td class="text-center">
                                <?php if ((int)$st->arrear_count > 0): ?>
                                <span class="badge bg-red"><?php echo $st->arrear_count; ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="label label-<?php echo $st->result_status === 'pass' ? 'success' : 'danger'; ?>">
                                    <?php echo strtoupper($st->result_status); ?>
                                </span>
                            </td>
                            <td class="no-print">
                                <a href="<?php echo site_url('coe/coe_results/student_result/'.$st->student_id.'/'.$batch_exam_id); ?>"
                                   class="btn btn-xs btn-default">
                                    <i class="fa fa-user"></i> Card
                                </a>
                                <a href="<?php echo site_url('coe/coe_arrear/student/'.$st->student_id); ?>"
                                   class="btn btn-xs btn-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SGPA Distribution Chart -->
        <div class="row">
            <div class="col-md-8">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bar-chart"></i> SGPA Distribution</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="sgpaChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-pie-chart"></i> Grade Category</h3>
                    </div>
                    <div class="box-body">
                        <?php
                        $bands = ['First Class with Distinction (≥8.5)' => 0, 'First Class (6.5–8.49)' => 0, 'Second Class (5.0–6.49)' => 0, 'Fail / Arrear' => 0];
                        foreach ($students as $st) {
                            $s = (float)$st->sgpa;
                            if ($s >= 8.5) $bands['First Class with Distinction (≥8.5)']++;
                            elseif ($s >= 6.5) $bands['First Class (6.5–8.49)']++;
                            elseif ($s >= 5.0) $bands['Second Class (5.0–6.49)']++;
                            else $bands['Fail / Arrear']++;
                        }
                        $colors = ['success', 'info', 'warning', 'danger'];
                        $ci = 0;
                        ?>
                        <?php foreach ($bands as $label => $cnt): ?>
                        <div class="progress-group" style="margin-bottom:10px">
                            <span class="progress-text"><?php echo $label; ?></span>
                            <span class="progress-number pull-right"><b><?php echo $cnt; ?></b>/<?php echo count($students); ?></span>
                            <div class="progress sm">
                                <div class="progress-bar progress-bar-<?php echo $colors[$ci++]; ?>"
                                     style="width:<?php echo count($students)>0 ? round($cnt/count($students)*100).'%' : '0'; ?>"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </section>
</div>

<script>
<?php if (!empty($students)): ?>
(function() {
    // Build histogram: group SGPA into 0.5 bands
    var bands = {};
    var data = <?php
        $chart = [];
        foreach ($students as $st) {
            $band = floor((float)$st->sgpa * 2) / 2; // round to 0.5
            $key = number_format($band, 1);
            $chart[$key] = ($chart[$key] ?? 0) + 1;
        }
        ksort($chart);
        echo json_encode(array_values($chart));
    ?>;
    var labels = <?php echo json_encode(array_keys($chart)); ?>;

    var ctx = document.getElementById('sgpaChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Students',
                data: data,
                backgroundColor: 'rgba(60,141,188,0.7)',
                borderColor: 'rgba(60,141,188,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { title: { display: true, text: 'SGPA' } }
            },
            plugins: { legend: { display: false } }
        }
    });
})();
<?php endif; ?>
</script>
<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'results']); ?>
