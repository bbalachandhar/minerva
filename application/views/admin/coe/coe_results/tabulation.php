<!-- Tabulation Sheet — Anna University Format -->
<style>
    @media print {
        .content-header, .main-header, .main-sidebar, .content-wrapper > .content > .row:first-child { display: none !important; }
        .tab-sheet-wrap { padding: 0; }
        body, .content-wrapper { background: #fff; }
        .box { border: none !important; box-shadow: none !important; }
    }
    .tab-table { font-size: 10px; }
    .tab-table th, .tab-table td { text-align: center; vertical-align: middle; padding: 3px 4px; }
    .tab-table .col-name { text-align: left; min-width: 130px; }
    .tab-table .col-reg { min-width: 100px; }
    .sub-header th { background: #2c6fad; color: #fff; }
    .sub-header2 th { background: #3c8dbc; color: #fff; }
    .pass-row td.result-cell { color: #155724; font-weight: bold; }
    .fail-row td.result-cell { color: #721c24; font-weight: bold; }
    .grade-U { color: #c0392b; font-weight: bold; }
    .grade-O { color: #27ae60; font-weight: bold; }
    .grade-A\+ { color: #2980b9; font-weight: bold; }
    .tab-print-header { border: 2px solid #333; padding: 10px; margin-bottom: 10px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-table"></i> Tabulation Sheet
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_results/listing/'.$batch_exam_id); ?>"><i class="fa fa-arrow-left"></i> Back to Results</a></li>
            <li><button class="btn btn-xs btn-default" onclick="window.print()"><i class="fa fa-print"></i> Print</button></li>
        </ol>
    </section>

    <section class="content">
        <?php if (empty($students)): ?>
        <div class="callout callout-warning">
            <p>No marks data found for this exam event. Please enter marks first.</p>
        </div>
        <?php else: ?>

        <!-- College / Exam Header (shows on print) -->
        <div class="tab-print-header" style="text-align:center;display:none" class="print-only">
            <h4 style="margin:2px 0">TABULATION SHEET</h4>
            <p style="margin:2px 0">
                <strong><?php echo htmlspecialchars($event->exam_group_name); ?></strong> &mdash;
                <?php echo htmlspecialchars($event->exam); ?>
            </p>
            <?php if ($event->date_from): ?>
            <p style="margin:2px 0;font-size:11px">
                <?php echo date('d M Y', strtotime($event->date_from));
                if ($event->date_to) echo ' to ' . date('d M Y', strtotime($event->date_to)); ?>
            </p>
            <?php endif; ?>
            <p style="margin:2px 0;font-size:10px">Session: <?php echo htmlspecialchars($event->session); ?></p>
        </div>

        <!-- Summary -->
        <div class="row" style="margin-bottom:10px">
            <div class="col-md-12">
                <div class="callout callout-info" style="padding:8px 12px">
                    <strong><?php echo count($students); ?></strong> students &bull;
                    <strong><?php echo count($subjects); ?></strong> subjects &bull;
                    <strong style="color:green"><?php
                        $pass_total = 0; $fail_total = 0;
                        foreach ($students as $st) {
                            if (isset($st['overall_status'])) {
                                if ($st['overall_status'] === 'pass') $pass_total++;
                                else $fail_total++;
                            }
                        }
                        echo $pass_total;
                    ?> Pass</strong> &bull;
                    <strong style="color:red"><?php echo $fail_total; ?> Fail</strong> &bull;
                    Pass Rate: <strong><?php echo ($pass_total + $fail_total) > 0 ? round($pass_total/($pass_total+$fail_total)*100,1).'%' : '—'; ?></strong>
                    &nbsp;&nbsp;
                    <button class="btn btn-xs btn-default pull-right" onclick="window.print()">
                        <i class="fa fa-print"></i> Print / PDF
                    </button>
                    <a href="<?php echo site_url('coe/coe_results/merit_list/'.$batch_exam_id); ?>"
                       class="btn btn-xs btn-info pull-right" style="margin-right:5px">
                        <i class="fa fa-trophy"></i> Merit List
                    </a>
                </div>
            </div>
        </div>

        <div class="box box-default" style="overflow-x:auto">
            <table class="table table-bordered tab-table" id="tabulationTable">
                <thead>
                    <!-- Row 1: Subject codes spanning 4 cols each -->
                    <tr class="sub-header">
                        <th rowspan="2" class="col-reg">Reg. No.</th>
                        <th rowspan="2" class="col-name">Student Name</th>
                        <?php foreach ($subjects as $sub): ?>
                        <th colspan="4"><?php echo htmlspecialchars($sub->code); ?></th>
                        <?php endforeach; ?>
                        <th rowspan="2">Credits<br>Earned</th>
                        <th rowspan="2">SGPA</th>
                        <th rowspan="2">CGPA</th>
                        <th rowspan="2">Arrears</th>
                        <th rowspan="2">Result</th>
                    </tr>
                    <!-- Row 2: Int / Ext / Tot / Grd per subject -->
                    <tr class="sub-header2">
                        <?php foreach ($subjects as $sub): ?>
                        <th>Int</th>
                        <th>Ext</th>
                        <th>Tot</th>
                        <th>Grd</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_pass = 0; $grand_fail = 0;
                    $sn = 0;
                    foreach ($students as $st):
                        $sn++;
                        $is_fail = ($st['overall_status'] === 'fail');
                        if ($is_fail) $grand_fail++; else $grand_pass++;
                    ?>
                    <tr class="<?php echo $is_fail ? 'fail-row' : ''; ?>">
                        <td class="col-reg"><?php echo htmlspecialchars($st['register_no'] ?: $st['admission_no'] ?: '—'); ?></td>
                        <td class="col-name text-left"><?php echo htmlspecialchars($st['student_name']); ?></td>
                        <?php foreach ($subjects as $sub_id => $sub):
                            $res = isset($st['subjects'][$sub_id]) ? $st['subjects'][$sub_id] : null;
                        ?>
                        <td><?php echo $res ? $res->internal_marks : '—'; ?></td>
                        <td><?php echo $res ? $res->external_marks : '—'; ?></td>
                        <td><?php echo $res ? $res->total_marks : '—'; ?></td>
                        <td class="grade-<?php echo $res ? htmlspecialchars($res->grade) : ''; ?>">
                            <?php echo $res ? htmlspecialchars($res->grade) : '—'; ?>
                        </td>
                        <?php endforeach; ?>
                        <td><?php echo $st['credits_earned'] ?? '—'; ?></td>
                        <td><strong><?php echo $st['sgpa'] !== null ? number_format($st['sgpa'], 2) : '—'; ?></strong></td>
                        <td><?php echo $st['cgpa'] !== null ? number_format($st['cgpa'], 2) : '—'; ?></td>
                        <td><?php echo $st['arrear_count'] > 0 ? '<span class="text-danger">'.$st['arrear_count'].'</span>' : '<span class="text-muted">0</span>'; ?></td>
                        <td class="result-cell <?php echo $is_fail ? 'text-danger' : 'text-success'; ?>">
                            <strong><?php echo $is_fail ? 'FAIL' : 'PASS'; ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-light-blue-active" style="color:#fff;font-weight:bold">
                        <td colspan="2">SUMMARY</td>
                        <?php foreach ($subjects as $sub_id => $sub):
                            $sub_pass = 0; $sub_fail = 0; $sub_total = 0; $sub_sum = 0;
                            foreach ($students as $st) {
                                $res = isset($st['subjects'][$sub_id]) ? $st['subjects'][$sub_id] : null;
                                if ($res) {
                                    $sub_total++;
                                    $sub_sum += (float)$res->total_marks;
                                    if ($res->result_status === 'pass') $sub_pass++;
                                    else $sub_fail++;
                                }
                            }
                            $sub_avg = $sub_total > 0 ? round($sub_sum/$sub_total,1) : 0;
                            $sub_pct = $sub_total > 0 ? round($sub_pass/$sub_total*100,1) : 0;
                        ?>
                        <td colspan="3">Avg: <?php echo $sub_avg; ?><br>Pass: <?php echo $sub_pct; ?>%</td>
                        <td></td>
                        <?php endforeach; ?>
                        <td>—</td>
                        <td><?php
                            $sgpa_vals = array_filter(array_column($students, 'sgpa'), function($v){ return $v !== null; });
                            echo count($sgpa_vals) > 0 ? number_format(array_sum($sgpa_vals)/count($sgpa_vals), 2) : '—';
                        ?></td>
                        <td>—</td>
                        <td>—</td>
                        <td>P:<?php echo $grand_pass; ?>/F:<?php echo $grand_fail; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Grading Scale Reference -->
        <div class="row">
            <div class="col-md-6">
                <div class="callout callout-default" style="font-size:11px;padding:8px">
                    <strong>Grade Scale (Anna University NEP2020):</strong>
                    O (≥91=10) | A+ (≥81=9) | A (≥71=8) | B+ (≥61=7) | B (≥51=6) | C (50=5) | U (Fail=0)
                    &nbsp;|&nbsp; Ext pass: ≥28/70 &nbsp;|&nbsp; Total pass: ≥50/100
                </div>
            </div>
        </div>

        <?php endif; ?>
    </section>
</div>
<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'results']); ?>
