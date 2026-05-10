<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<style>
.coe-stat-card{border-radius:10px;padding:16px 18px 12px;color:#fff;display:flex;align-items:center;gap:12px;box-shadow:0 4px 15px rgba(0,0,0,.12);margin-bottom:14px;transition:transform .18s;}
.coe-stat-card:hover{transform:translateY(-2px);}
.coe-stat-card .stat-icon{font-size:32px;opacity:.8;}
.coe-stat-card .stat-num{font-size:26px;font-weight:700;line-height:1;}
.coe-stat-card .stat-lbl{font-size:11px;text-transform:uppercase;letter-spacing:.5px;opacity:.9;margin-top:3px;}
.coe-stat-card .stat-pct{font-size:11px;opacity:.75;margin-top:2px;}
.coe-card-total{background:linear-gradient(135deg,#3c8dbc,#1a6091);}
.coe-card-eligible{background:linear-gradient(135deg,#00a65a,#006b39);}
.coe-card-inelig{background:linear-gradient(135deg,#dd4b39,#a01f10);}
.coe-card-override{background:linear-gradient(135deg,#f39c12,#b06f00);}
.coe-card-pending{background:linear-gradient(135deg,#7a8b99,#4a5c69);}
.chart-box{background:#fff;border-radius:10px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:20px;}
.chart-box h4{margin-top:0;font-size:14px;font-weight:600;color:#444;border-bottom:1px solid #f0f0f0;padding-bottom:9px;margin-bottom:14px;}
.status-pill{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;}
.sp-pending{background:#e9ecef;color:#555;}
.sp-eligible{background:#d4edda;color:#155724;}
.sp-ineligible{background:#f8d7da;color:#721c24;}
.sp-override{background:#fff3cd;color:#856404;}
.cbcs-pill{display:inline-block;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:600;}
.cbcs-core{background:#d0e8ff;color:#004085;}
.cbcs-elective{background:#d4edda;color:#155724;}
.cbcs-open_elective{background:#e2d9f3;color:#432874;}
.cbcs-audit{background:#f8d7da;color:#721c24;}
.att-bar{height:8px;border-radius:4px;background:#e9ecef;overflow:hidden;}
.att-bar-fill{height:100%;border-radius:4px;}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-list-alt"></i> <?php echo $data['title']; ?>
            <small style="font-size:14px;color:#888;">&mdash; <?php echo htmlspecialchars($event->exam_name ?? $event->name ?? ''); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_application'); ?>"><?php echo $this->lang->line('coe_exam_events'); ?></a></li>
            <li class="active">Applications</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <?php
        $total          = (int)($stats->total ?? 0);
        $eligible_count = (int)($stats->eligible_count ?? 0);
        $inelig_count   = (int)($stats->ineligible_count ?? 0);
        $override_count = (int)($stats->override_count ?? 0);
        $pending_count  = (int)($stats->pending_count ?? 0);
        $pct = fn($n) => $total > 0 ? round(($n / $total) * 100, 1) : 0;
        $cbcs_counts = ['core' => 0, 'elective' => 0, 'open_elective' => 0, 'audit' => 0];
        foreach ($applications as $app) { $cbcs_counts[$app->cbcs_category] = ($cbcs_counts[$app->cbcs_category] ?? 0) + 1; }
        ?>

        <!-- Stat Cards -->
        <div class="row">
            <div class="col-md-2 col-sm-6">
                <div class="coe-stat-card coe-card-total">
                    <div class="stat-icon"><i class="fa fa-users"></i></div>
                    <div><div class="stat-num"><?php echo $total; ?></div><div class="stat-lbl">Total</div></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="coe-stat-card coe-card-eligible">
                    <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                    <div><div class="stat-num"><?php echo $eligible_count; ?></div><div class="stat-lbl">Eligible</div><div class="stat-pct"><?php echo $pct($eligible_count); ?>%</div></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="coe-stat-card coe-card-inelig">
                    <div class="stat-icon"><i class="fa fa-times-circle"></i></div>
                    <div><div class="stat-num"><?php echo $inelig_count; ?></div><div class="stat-lbl">Ineligible</div><div class="stat-pct"><?php echo $pct($inelig_count); ?>%</div></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="coe-stat-card coe-card-override">
                    <div class="stat-icon"><i class="fa fa-unlock-alt"></i></div>
                    <div><div class="stat-num"><?php echo $override_count; ?></div><div class="stat-lbl">Override</div><div class="stat-pct"><?php echo $pct($override_count); ?>%</div></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="coe-stat-card coe-card-pending">
                    <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
                    <div><div class="stat-num"><?php echo $pending_count; ?></div><div class="stat-lbl">Pending</div><div class="stat-pct"><?php echo $pct($pending_count); ?>%</div></div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="coe-stat-card" style="background:linear-gradient(135deg,#e67e22,#ca6f1e);">
                    <div class="stat-icon"><i class="fa fa-graduation-cap"></i></div>
                    <div><div class="stat-num"><?php echo $cbcs_counts['core']; ?></div><div class="stat-lbl">Core Courses</div><div class="stat-pct"><?php echo $cbcs_counts['elective']; ?> elective</div></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-5">
                <div class="chart-box">
                    <h4><i class="fa fa-pie-chart" style="color:#3c8dbc;"></i>&nbsp; Application Status</h4>
                    <canvas id="statusDonut" height="220"></canvas>
                </div>
            </div>
            <div class="col-md-7">
                <div class="chart-box">
                    <h4><i class="fa fa-bar-chart" style="color:#9b59b6;"></i>&nbsp; CBCS Category Distribution</h4>
                    <canvas id="cbcsBar" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Filter + Table -->
        <div class="chart-box" style="padding:0;overflow:hidden;">
            <div style="padding:16px 20px;">
                <form method="get" action="<?php echo site_url('coe/coe_application/view/' . $event->id); ?>" class="form-inline">
                    <div class="form-group" style="margin-right:10px;">
                        <label style="font-weight:600;font-size:11px;text-transform:uppercase;color:#666;">Status&nbsp;</label>
                        <select name="application_status" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <?php foreach(['pending','eligible','ineligible','override_eligible'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo ($filters['application_status']===$s)?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:10px;">
                        <label style="font-weight:600;font-size:11px;text-transform:uppercase;color:#666;">CBCS&nbsp;</label>
                        <select name="cbcs_category" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach(['core','elective','open_elective','audit'] as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo ($filters['cbcs_category']===$c)?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$c)); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="<?php echo site_url('coe/coe_application/view/' . $event->id); ?>" class="btn btn-default btn-sm"><i class="fa fa-times"></i> Clear</a>
                    <a href="<?php echo site_url('coe/coe_eligibility?batch_exam_id=' . $event->id); ?>" class="btn btn-warning btn-sm" style="margin-left:10px;border-radius:6px;"><i class="fa fa-check-square-o"></i> Run Eligibility</a>
                </form>
            </div>
            <div style="padding:0 20px 20px;">
                <table class="table table-bordered table-hover dataTable" id="appsTable" style="width:100%;">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th>#</th>
                            <th>Register No.</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>CBCS</th>
                            <th>Arrear</th>
                            <th>Att %</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr><td colspan="9" class="text-center text-muted" style="padding:30px;"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                        <?php else: ?>
                        <?php foreach ($applications as $i => $app):
                            $att = $app->attendance_pct;
                            $attClr = ($att === null) ? '#999' : ($att >= 75 ? '#00a65a' : ($att >= 60 ? '#f39c12' : '#dd4b39'));
                        ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><code><?php echo htmlspecialchars($app->register_no ?? ''); ?></code></td>
                            <td><?php echo htmlspecialchars($app->firstname . ' ' . $app->lastname); ?></td>
                            <td><?php echo htmlspecialchars($app->subject_name); ?><?php if (!empty($app->subject_code)): ?><br><small class="text-muted"><?php echo $app->subject_code; ?></small><?php endif; ?></td>
                            <td><span class="cbcs-pill cbcs-<?php echo $app->cbcs_category; ?>"><?php echo ucfirst(str_replace('_',' ',$app->cbcs_category)); ?></span></td>
                            <td><?php echo $app->is_arrear ? '<span style="color:#f39c12;font-weight:600;">Arrear</span>' : '<span class="text-muted">—</span>'; ?></td>
                            <td>
                                <?php if ($att !== null): ?>
                                <div style="min-width:65px;"><span style="font-weight:600;color:<?php echo $attClr; ?>;font-size:12px;"><?php echo $att; ?>%</span>
                                <div class="att-bar"><div class="att-bar-fill" style="width:<?php echo min(100,$att); ?>%;background:<?php echo $attClr; ?>;"></div></div></div>
                                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                            </td>
                            <td><?php
                                $spMap=['pending'=>'sp-pending','eligible'=>'sp-eligible','ineligible'=>'sp-ineligible','override_eligible'=>'sp-override'];
                                echo '<span class="status-pill '.($spMap[$app->application_status]??'sp-pending').'">'.ucfirst(str_replace('_',' ',$app->application_status)).'</span>';
                            ?></td>
                            <td><?php echo $app->ineligible_reason ? '<small style="color:#888;">'.ucfirst(str_replace('_',' ',$app->ineligible_reason)).'</small>' : '<span class="text-muted">—</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
(function(){
    new Chart(document.getElementById('statusDonut').getContext('2d'),{
        type:'doughnut',
        data:{labels:['Eligible','Ineligible','Override','Pending'],datasets:[{data:[<?php echo "$eligible_count,$inelig_count,$override_count,$pending_count"; ?>],backgroundColor:['#00a65a','#dd4b39','#f39c12','#7a8b99'],borderWidth:2,borderColor:'#fff',hoverOffset:5}]},
        options:{responsive:true,cutout:'60%',plugins:{legend:{position:'bottom',labels:{padding:12,font:{size:12}}},tooltip:{callbacks:{label:function(c){var t=c.dataset.data.reduce(function(a,b){return a+b;},0);return ' '+c.label+': '+c.parsed+' ('+(t>0?Math.round(c.parsed/t*100):0)+'%)';}}}}}
    });
    new Chart(document.getElementById('cbcsBar').getContext('2d'),{
        type:'bar',
        data:{labels:['Core','Elective','Open Elective','Audit'],datasets:[{label:'Applications',data:[<?php echo implode(',',[$cbcs_counts['core'],$cbcs_counts['elective'],$cbcs_counts['open_elective'],$cbcs_counts['audit']]); ?>],backgroundColor:['rgba(0,64,133,.75)','rgba(21,87,36,.75)','rgba(67,40,116,.75)','rgba(114,28,36,.75)'],borderColor:['#004085','#155724','#432874','#721c24'],borderWidth:2,borderRadius:7}]},
        options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'rgba(0,0,0,.05)'}},x:{grid:{display:false}}}}
    });
    $(function(){
        if($.fn.DataTable&&$('#appsTable').length){
            $('#appsTable').DataTable({order:[[2,'asc'],[3,'asc']],pageLength:50,language:{search:'Filter:'}});
        }
    });
})();
</script>
