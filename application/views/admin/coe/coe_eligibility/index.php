<!-- SweetAlert — required for swal() confirm dialogs on admin pages -->
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.css">
<script src="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.min.js"></script>

<!-- Chart.js 3.x for modern charts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<style>
/* ─── CoE Eligibility Page Styles ───────────────────────────────── */
/* Equal-height stat cards row */
.stat-cards-row { display: flex; flex-wrap: wrap; }
.stat-cards-row > [class*="col-"] { display: flex; flex-direction: column; }
.stat-cards-row > [class*="col-"] .coe-stat-card { flex: 1; }

.coe-stat-card {
    border-radius: 10px;
    padding: 18px 20px 14px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
    margin-bottom: 14px;
    transition: transform .18s;
}
.coe-stat-card:hover { transform: translateY(-2px); }
.coe-stat-card .stat-icon { font-size: 36px; opacity: .8; }
.coe-stat-card .stat-body { flex: 1; }
.coe-stat-card .stat-num  { font-size: 30px; font-weight: 700; line-height: 1; }
.coe-stat-card .stat-lbl  { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; opacity: .9; margin-top: 3px; }
.coe-stat-card .stat-pct  { font-size: 11px; opacity: .75; margin-top: 2px; }

.coe-card-total    { background: linear-gradient(135deg,#3c8dbc 0%,#1a6091 100%); }
.coe-card-eligible { background: linear-gradient(135deg,#00a65a 0%,#006b39 100%); }
.coe-card-inelig   { background: linear-gradient(135deg,#dd4b39 0%,#a01f10 100%); }
.coe-card-override { background: linear-gradient(135deg,#f39c12 0%,#b06f00 100%); }
.coe-card-pending  { background: linear-gradient(135deg,#7a8b99 0%,#4a5c69 100%); }
.coe-card-both     { background: linear-gradient(135deg,#9b59b6 0%,#6c3483 100%); }

.chart-box { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.07); margin-bottom: 20px; }
.chart-box h4 { margin-top: 0; font-size: 15px; font-weight: 600; color: #444; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 16px; }

.coe-filter-bar { background: #fff; border-radius: 10px; padding: 14px 20px; box-shadow: 0 2px 8px rgba(0,0,0,.06); margin-bottom: 20px; }
.coe-filter-bar form { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
.coe-filter-bar .form-group { display: flex; align-items: center; gap: 8px; margin: 0; }
.coe-filter-bar label { font-weight: 600; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; margin: 0; }

.att-bar { height: 10px; border-radius: 5px; background: #e9ecef; overflow: hidden; margin-top: 4px; }
.att-bar-fill { height: 100%; border-radius: 5px; transition: width .4s; }
.att-high  { background: #00a65a; }
.att-med   { background: #f39c12; }
.att-low   { background: #dd4b39; }

.reason-badge { display: inline-block; padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.reason-att  { background: #fff3cd; color: #856404; }
.reason-fee  { background: #f8d7da; color: #842029; }
.reason-both { background: #e2d9f3; color: #432874; }

.run-engine-panel { background: linear-gradient(135deg,#fff 0%,#f8f9fa 100%); border: 2px dashed #3c8dbc; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 20px; }
.run-engine-panel p { color: #555; margin-bottom: 12px; font-size: 13px; }
.run-engine-panel .btn-run { background: linear-gradient(135deg,#3c8dbc,#1a6091); color: #fff; border: none; border-radius: 6px; padding: 10px 30px; font-size: 14px; font-weight: 600; box-shadow: 0 3px 10px rgba(60,141,188,.35); cursor: pointer; }
.run-engine-panel .btn-run:hover { opacity: .92; }
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-check-square-o"></i> <?php echo $title; ?>
            <?php if ($selected_event && isset($event_detail)): ?>
            <small class="text-muted" style="font-size:14px;">&mdash; <?php echo htmlspecialchars($event_detail->exam_group_name ?? ''); ?></small>
            <?php endif; ?>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_eligibility'); ?>"><?php echo $this->lang->line('coe_eligibility'); ?></a></li>
            <li class="active">Check Eligibility</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- ── Filter Bar ─────────────────────────────────────────── -->
        <div class="coe-filter-bar">
            <form method="GET" action="<?php echo site_url('coe/coe_eligibility'); ?>" class="form-inline" id="eligibility-filter-form">
                <div class="form-group">
                    <label>Session</label>
                    <select name="session_id" class="form-control input-sm" onchange="document.getElementById('eligibility-filter-form').submit()">
                        <?php foreach ($session_list as $sess): ?>
                            <option value="<?php echo $sess["id"]; ?>" <?php echo ($sess["id"] == $selected_session) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sess["name"] ?? $sess["session"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Batch Exam</label>
                    <select name="batch_exam_id" id="batch-exam-select" class="form-control input-sm" style="width:280px;">
                        <option value="">— Select Batch —</option>
                        <?php
                        $grouped = [];
                        foreach ($events as $ev) {
                            $grouped[$ev->exam_group_id]['name']    = $ev->exam_group_name;
                            $grouped[$ev->exam_group_id]['batches'][] = $ev;
                        }
                        foreach ($grouped as $grp):
                        ?>
                            <optgroup label="<?php echo htmlspecialchars($grp['name']); ?>">
                                <?php foreach ($grp['batches'] as $ev): ?>
                                <option value="<?php echo $ev->batch_exam_id; ?>"
                                    <?php echo ($ev->batch_exam_id == $selected_event) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ev->class_name . ' — ' . $ev->exam); ?>
                                </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selected_event && $this->rbac->hasPrivilege('coe_eligibility', 'can_add')): ?>
                <div class="btn-group" style="vertical-align:middle;">
                    <button type="button" class="btn btn-warning btn-sm" id="btn-run-engine" style="border-radius:6px 0 0 6px;"
                        data-batch-exam-id="<?php echo (int)$selected_event; ?>"
                        data-csrf-name="<?php echo $this->security->get_csrf_token_name(); ?>"
                        data-csrf-hash="<?php echo $this->security->get_csrf_hash(); ?>"
                        data-url="<?php echo site_url('coe/coe_eligibility/run_ajax'); ?>">
                        <i class="fa fa-cog" id="run-engine-icon"></i>&nbsp; Run Engine
                    </button>
                    <?php if (isset($event_detail) && $event_detail): ?>
                    <button type="button" class="btn btn-default btn-sm" id="btn-run-all" style="border-radius:0 6px 6px 0;border-left:1px solid #ccc;" title="Run eligibility for ALL batches in this event"
                        data-exam-group-id="<?php echo (int)$event_detail->exam_group_id; ?>"
                        data-session-id="<?php echo (int)$selected_session; ?>"
                        data-csrf-name="<?php echo $this->security->get_csrf_token_name(); ?>"
                        data-csrf-hash="<?php echo $this->security->get_csrf_hash(); ?>"
                        data-url="<?php echo site_url('coe/coe_eligibility/run_all_ajax'); ?>">
                        <i class="fa fa-cogs" id="run-all-icon"></i>&nbsp; Run All in Event
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if ($selected_event && !empty($eligibility_run_at)): ?>
                <span class="text-muted" id="last-run-label" style="font-size:11px;margin-left:10px;">
                    <i class="fa fa-clock-o"></i> Last run: <?php echo date('d M Y, H:i', strtotime($eligibility_run_at)); ?>
                </span>
                <?php else: ?>
                <span class="text-muted" id="last-run-label" style="font-size:11px;margin-left:10px;display:none;">
                    <i class="fa fa-clock-o"></i> Last run: <span id="last-run-time"></span>
                </span>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($selected_event && $summary && (int)($summary->total ?? 0) > 0): ?>
        <?php
            $total              = (int)($summary->total ?? 0);
            $total_students     = (int)($summary->total_students ?? 0);
            $eligible_count     = (int)($summary->eligible_count ?? 0);
            $eligible_students  = (int)($summary->eligible_students ?? 0);
            $inelig_count       = (int)($summary->ineligible_count ?? 0);
            $inelig_students    = (int)($summary->ineligible_students ?? 0);
            $override_count     = (int)($summary->override_count ?? 0);
            $override_students  = (int)($summary->override_students ?? 0);
            $pending_count      = (int)($summary->pending_count ?? 0);
            $pending_students   = (int)($summary->pending_students ?? 0);
            $att_fail           = (int)($summary->att_fail_count ?? 0);
            $att_fail_students  = (int)($summary->att_fail_students ?? 0);
            $fee_fail           = (int)($summary->fee_fail_count ?? 0);
            $fee_fail_students  = (int)($summary->fee_fail_students ?? 0);
            $both_fail          = (int)($summary->both_fail_count ?? 0);
            $both_fail_students = (int)($summary->both_fail_students ?? 0);
            $pct = fn($n) => $total_students > 0 ? round(($n / $total_students) * 100, 1) : 0;
        ?>

        <!-- ── Stat Cards ─────────────────────────────────────────── -->
        <div class="row stat-cards-row">
            <div class="col-md-2 col-sm-4">
                <div class="coe-stat-card coe-card-total">
                    <div class="stat-icon"><i class="fa fa-users"></i></div>
                    <div class="stat-body">
                        <div class="stat-num"><?php echo $total_students; ?></div>
                        <div class="stat-lbl">Total Students</div>
                        <div class="stat-pct"><?php echo $total; ?> applications</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="coe-stat-card coe-card-eligible">
                    <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                    <div class="stat-body">
                        <div class="stat-num"><?php echo $eligible_students; ?></div>
                        <div class="stat-lbl">Eligible</div>
                        <div class="stat-pct"><?php echo $pct($eligible_students); ?>% &middot; <?php echo $eligible_count; ?> apps</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="coe-stat-card coe-card-inelig">
                    <div class="stat-icon"><i class="fa fa-times-circle"></i></div>
                    <div class="stat-body">
                        <div class="stat-num"><?php echo $inelig_students; ?></div>
                        <div class="stat-lbl">Ineligible</div>
                        <div class="stat-pct"><?php echo $pct($inelig_students); ?>% &middot; <?php echo $inelig_count; ?> apps</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="coe-stat-card coe-card-override">
                    <div class="stat-icon"><i class="fa fa-unlock-alt"></i></div>
                    <div class="stat-body">
                        <div class="stat-num"><?php echo $override_students; ?></div>
                        <div class="stat-lbl">Override</div>
                        <div class="stat-pct"><?php echo $pct($override_students); ?>% &middot; <?php echo $override_count; ?> apps</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="coe-stat-card coe-card-pending">
                    <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
                    <div class="stat-body">
                        <div class="stat-num"><?php echo $pending_students; ?></div>
                        <div class="stat-lbl">Pending</div>
                        <div class="stat-pct"><?php echo $pct($pending_students); ?>% &middot; <?php echo $pending_count; ?> apps</div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4">
                <div class="coe-stat-card coe-card-both">
                    <div class="stat-icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <div class="stat-body">
                        <div class="stat-num"><?php echo $both_fail_students; ?></div>
                        <div class="stat-lbl">Att + Fee Fail</div>
                        <div class="stat-pct"><?php echo $pct($both_fail_students); ?>% &middot; <?php echo $both_fail; ?> apps</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Charts Row ─────────────────────────────────────────── -->
        <div class="row">
            <!-- Donut: Eligibility Status -->
            <div class="col-md-4">
                <div class="chart-box">
                    <h4><i class="fa fa-pie-chart" style="color:#3c8dbc;"></i>&nbsp; Eligibility Breakdown</h4>
                    <canvas id="eligDonut" height="230"></canvas>
                    <div id="donut-legend" style="margin-top:12px;text-align:center;font-size:12px;"></div>
                </div>
            </div>
            <!-- Horizontal Bar: Failure Reasons -->
            <div class="col-md-4">
                <div class="chart-box">
                    <h4><i class="fa fa-bar-chart" style="color:#dd4b39;"></i>&nbsp; Failure Reasons</h4>
                    <canvas id="reasonBar" height="230"></canvas>
                </div>
            </div>
            <!-- Eligibility Progress Gauge -->
            <div class="col-md-4">
                <div class="chart-box">
                    <h4><i class="fa fa-tachometer" style="color:#00a65a;"></i>&nbsp; Eligibility Rate</h4>
                    <?php $elig_rate = $total_students > 0 ? round((($eligible_students + $override_students) / $total_students) * 100, 1) : 0; ?>
                    <div style="text-align:center;padding:10px 0 6px;">
                        <canvas id="gaugeDonut" height="180"></canvas>
                        <div style="margin-top:-10px;font-size:28px;font-weight:700;color:#333;"><?php echo $elig_rate; ?>%</div>
                        <div style="color:#888;font-size:12px;">
                            <?php echo ($eligible_students + $override_students); ?> of <?php echo $total_students; ?> students cleared
                            &middot; <?php echo ($eligible_count + $override_count); ?> of <?php echo $total; ?> subject applications
                        </div>
                    </div>
                    <div style="margin-top:14px;">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#555;margin-bottom:4px;">
                            <span style="color:#00a65a;font-weight:600;"><i class="fa fa-check-circle"></i> Eligible</span>
                            <span><strong><?php echo $eligible_students; ?></strong> students &middot; <strong><?php echo $eligible_count; ?></strong> subj apps</span>
                        </div>
                        <div class="att-bar"><div class="att-bar-fill att-high" style="width:<?php echo $total_students>0?round($eligible_students/$total_students*100):0; ?>%"></div></div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#555;margin:8px 0 4px;">
                            <span style="color:#f39c12;font-weight:600;"><i class="fa fa-unlock-alt"></i> Override</span>
                            <span><strong><?php echo $override_students; ?></strong> students &middot; <strong><?php echo $override_count; ?></strong> subj apps</span>
                        </div>
                        <div class="att-bar"><div class="att-bar-fill att-med" style="width:<?php echo $total_students>0?round($override_students/$total_students*100):0; ?>%"></div></div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#555;margin:8px 0 4px;">
                            <span style="color:#dd4b39;font-weight:600;"><i class="fa fa-times-circle"></i> Ineligible</span>
                            <span><strong><?php echo $inelig_students; ?></strong> students &middot; <strong><?php echo $inelig_count; ?></strong> subj apps</span>
                        </div>
                        <div class="att-bar"><div class="att-bar-fill att-low" style="width:<?php echo $total_students>0?round($inelig_students/$total_students*100):0; ?>%"></div></div>
                    </div>
                </div>
            </div>
        </div><!-- /.row charts -->

        <!-- ── Ineligible Students Table ──────────────────────────── -->
        <?php if (!empty($ineligible_list)): ?>
        <div class="chart-box" style="padding:0;overflow:hidden;">
            <div style="padding:16px 20px 0;display:flex;align-items:center;justify-content:space-between;">
                <h4 style="margin:0;border:none;padding:0;">
                    <i class="fa fa-times-circle" style="color:#dd4b39;"></i>&nbsp; Ineligible Students
                    <span style="background:#dd4b39;color:#fff;border-radius:12px;padding:2px 10px;font-size:13px;margin-left:6px;"><?php echo $inelig_students; ?></span>
                </h4>
                <small class="text-muted">Override-eligible students are excluded</small>
            </div>
            <div style="padding:14px 20px 20px;">
                <table class="table table-bordered table-hover dataTable" id="ineligTable" style="width:100%;">
                    <thead style="background:#f8f9fa;">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Student</th>
                            <th>Register No.</th>
                            <th>Subject</th>
                            <th style="width:160px;">Attendance %</th>
                            <th>Reason</th>
                            <?php if ($this->rbac->hasPrivilege('coe_override', 'can_add')): ?>
                            <th style="width:100px;">Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($ineligible_list as $app):
                            $att   = $app->attendance_pct;
                            $cls   = ($att === null) ? '' : ($att >= 75 ? 'att-high' : ($att >= 60 ? 'att-med' : 'att-low'));
                            $width = ($att !== null) ? min(100, round($att)) : 0;
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($app->firstname . ' ' . $app->lastname); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($app->register_no ?? '—'); ?></code></td>
                            <td><?php echo htmlspecialchars($app->subject_name); ?></td>
                            <td>
                                <?php if ($att !== null): ?>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="min-width:36px;font-weight:600;color:<?php echo $att<75?'#dd4b39':'#00a65a'; ?>;"><?php echo $att; ?>%</span>
                                    <div class="att-bar" style="flex:1;"><div class="att-bar-fill <?php echo $cls; ?>" style="width:<?php echo $width; ?>%"></div></div>
                                </div>
                                <?php else: ?><span class="text-muted">N/A</span><?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $rbadge = [
                                        'attendance' => '<span class="reason-badge reason-att"><i class="fa fa-calendar-times-o"></i> Low Attendance</span>',
                                        'fee_dues'   => '<span class="reason-badge reason-fee"><i class="fa fa-money"></i> Fee Dues</span>',
                                        'both'       => '<span class="reason-badge reason-both"><i class="fa fa-exclamation-triangle"></i> Att + Fee</span>',
                                    ];
                                    echo $rbadge[$app->ineligible_reason] ?? htmlspecialchars($app->ineligible_reason);
                                ?>
                            </td>
                            <?php if ($this->rbac->hasPrivilege('coe_override', 'can_add')): ?>
                            <td>
                                <button class="btn btn-xs override-btn"
                                        style="background:#f39c12;color:#fff;border-radius:4px;"
                                        data-app-id="<?php echo $app->id; ?>"
                                        data-student="<?php echo htmlspecialchars($app->firstname . ' ' . $app->lastname); ?>">
                                    <i class="fa fa-unlock-alt"></i> Override
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php elseif ($selected_event): ?>
        <div style="text-align:center;padding:60px 20px;color:#888;">
            <i class="fa fa-info-circle" style="font-size:48px;color:#3c8dbc;opacity:.5;"></i>
            <p style="margin-top:16px;font-size:15px;">No eligibility data yet. Click <strong>Run Engine</strong> above to process applications.</p>
            <?php if ($this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
            <p style="font-size:13px;color:#aaa;">No applications found for this batch?&nbsp;
                <a href="<?php echo site_url('coe/coe_application/generate/' . (int)$selected_event); ?>" class="btn btn-sm btn-primary" style="border-radius:6px;">
                    <i class="fa fa-plus-circle"></i> Generate Applications
                </a>
            </p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:60px 20px;color:#888;">
            <i class="fa fa-arrow-circle-up" style="font-size:48px;color:#3c8dbc;opacity:.4;"></i>
            <p style="margin-top:16px;font-size:15px;">Select a session and exam event to get started.</p>
        </div>
        <?php endif; ?>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- ── Override Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="overrideModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;">
            <form method="POST" action="<?php echo site_url('coe/coe_eligibility/override'); ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <input type="hidden" name="application_id" id="modal-app-id" value="">
                <input type="hidden" name="batch_exam_id" value="<?php echo $selected_event ?? 0; ?>">
                <div class="modal-header" style="background:linear-gradient(135deg,#f39c12,#b06f00);color:#fff;border:none;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.9;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-unlock-alt"></i> Override Eligibility</h4>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <p style="color:#555;">Granting eligibility override for: <strong id="modal-student-name" style="color:#333;"></strong></p>
                    <div class="form-group">
                        <label style="font-weight:600;color:#444;">Reason for Override <span style="color:#dd4b39;">*</span></label>
                        <textarea name="override_reason" class="form-control" rows="3"
                                  placeholder="State academic or administrative justification..." required
                                  style="border-radius:6px;border-color:#ddd;resize:none;"></textarea>
                        <small class="text-muted">This is logged in the CoE audit trail.</small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f0f0f0;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-warning" style="border-radius:6px;background:#f39c12;border-color:#e08e0b;color:#fff;">
                        <i class="fa fa-check"></i> Confirm Override
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($selected_event && $summary && (int)($summary->total ?? 0) > 0): ?>
<script>
(function () {
    // Data from PHP
    var eligible  = <?php echo $eligible_students ?? 0; ?>;
    var ineligible= <?php echo $inelig_students ?? 0; ?>;
    var override  = <?php echo $override_students ?? 0; ?>;
    var pending   = <?php echo $pending_students ?? 0; ?>;
    var attFail   = <?php echo $att_fail_students ?? 0; ?>;
    var feeFail   = <?php echo $fee_fail_students ?? 0; ?>;
    var bothFail  = <?php echo $both_fail_students ?? 0; ?>;
    var attFailApps  = <?php echo $att_fail ?? 0; ?>;
    var feeFailApps  = <?php echo $fee_fail ?? 0; ?>;
    var bothFailApps = <?php echo $both_fail ?? 0; ?>;
    var eligRate  = <?php echo $elig_rate ?? 0; ?>;

    // ── Eligibility Donut ─────────────────────────────────────────
    var donutCtx = document.getElementById('eligDonut').getContext('2d');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Eligible', 'Ineligible', 'Override', 'Pending'],
            datasets: [{
                data: [eligible, ineligible, override, pending],
                backgroundColor: ['#00a65a', '#dd4b39', '#f39c12', '#7a8b99'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            cutout: '62%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, font: { size: 12 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
                            var pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                            return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });

    // ── Failure Reasons Horizontal Bar ───────────────────────────
    var reasonCtx = document.getElementById('reasonBar').getContext('2d');
    new Chart(reasonCtx, {
        type: 'bar',
        data: {
            labels: ['Attendance Only', 'Fee Dues Only', 'Att + Fee Both'],
            datasets: [{
                label: 'Subject Applications',
                data: [attFail, feeFail, bothFail],
                backgroundColor: ['rgba(243,156,18,.8)', 'rgba(221,75,57,.8)', 'rgba(155,89,182,.8)'],
                borderColor:     ['#f39c12', '#dd4b39', '#9b59b6'],
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(c){
                    var apps = [attFailApps, feeFailApps, bothFailApps][c.dataIndex];
                    return ' ' + c.parsed.x + ' student(s) · ' + apps + ' application(s)';
                }}}
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
                y: { grid: { display: false } }
            }
        }
    });

    // ── Gauge (semi-donut) ────────────────────────────────────────
    var gaugeCtx = document.getElementById('gaugeDonut').getContext('2d');
    new Chart(gaugeCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [eligRate, 100 - eligRate],
                backgroundColor: ['#00a65a', '#e9ecef'],
                borderWidth: 0,
                hoverOffset: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '72%',
            circumference: 270,
            rotation: -135,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
})();
</script>
<?php endif; ?>

<script>
$(function () {

    // ── Toastr defaults ──────────────────────────────────────────────
    toastr.options = {
        positionClass: 'toast-top-right',
        timeOut: 6000,
        extendedTimeOut: 2000,
        closeButton: true,
        progressBar: true
    };

    // ── Helper: run AJAX engine call ─────────────────────────────────
    // ── Searchable Batch Exam dropdown ────────────────────────────
    if ($.fn.select2) {
        $('#batch-exam-select').select2({
            placeholder: '— Select Batch —',
            allowClear: true,
            width: '280px'
        }).on('change', function () {
            document.getElementById('eligibility-filter-form').submit();
        });
    } else {
        // Fallback: plain onchange submit
        $('#batch-exam-select').on('change', function () {
            document.getElementById('eligibility-filter-form').submit();
        });
    }

    function runEngine(btn, postData, iconId) {
        var $btn  = $(btn);
        var $icon = $('#' + iconId);
        $btn.prop('disabled', true);
        $icon.removeClass('fa-cog fa-cogs').addClass('fa-spinner fa-spin');

        $.ajax({
            url:  $btn.data('url'),
            type: 'POST',
            data: postData,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.status === 'success') {
                toastr.success(res.msg, 'Eligibility Done');
                // Update last-run label without full reload
                $('#last-run-label').show().find('#last-run-time').text(res.run_at);
                $('#last-run-label').show();
                // Reload page after brief delay so stat cards refresh
                setTimeout(function () { location.reload(); }, 1800);
            } else if (res.status === 'warning') {
                toastr.warning(res.msg, 'Skipped');
                $btn.prop('disabled', false);
                $icon.removeClass('fa-spinner fa-spin').addClass(iconId === 'run-engine-icon' ? 'fa-cog' : 'fa-cogs');
            } else {
                toastr.error(res.msg, 'Error');
                $btn.prop('disabled', false);
                $icon.removeClass('fa-spinner fa-spin').addClass(iconId === 'run-engine-icon' ? 'fa-cog' : 'fa-cogs');
            }
        })
        .fail(function () {
            toastr.error('Server error — please try again.', 'Request Failed');
            $btn.prop('disabled', false);
            $icon.removeClass('fa-spinner fa-spin').addClass(iconId === 'run-engine-icon' ? 'fa-cog' : 'fa-cogs');
        });
    }

    // ── Run Engine button ────────────────────────────────────────────
    $('#btn-run-engine').on('click', function () {
        var $btn = $(this);
        swal({
            title: 'Run Eligibility Engine?',
            text: 'This processes all pending applications — calculates attendance %, checks fee dues, and updates status. Overrides are preserved.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            confirmButtonText: 'Yes, Run Now',
            cancelButtonText: 'Cancel'
        }, function (ok) {
            if (!ok) return;
            var postData = {};
            postData[$btn.data('csrf-name')] = $btn.data('csrf-hash');
            postData['batch_exam_id']        = $btn.data('batch-exam-id');
            runEngine($btn[0], postData, 'run-engine-icon');
        });
    });

    // ── Run All in Event button ──────────────────────────────────────
    $('#btn-run-all').on('click', function () {
        var $btn = $(this);
        swal({
            title: 'Run All Batches?',
            text: 'This runs eligibility for every batch in this exam event. Locked batches and batches with no regulation are skipped — you will see a summary.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3c8dbc',
            confirmButtonText: 'Yes, Run All',
            cancelButtonText: 'Cancel'
        }, function (ok) {
            if (!ok) return;
            var postData = {};
            postData[$btn.data('csrf-name')] = $btn.data('csrf-hash');
            postData['exam_group_id']        = $btn.data('exam-group-id');
            postData['session_id']           = $btn.data('session-id');
            runEngine($btn[0], postData, 'run-all-icon');
        });
    });

    // ── Override modal ───────────────────────────────────────────────
    $(document).on('click', '.override-btn', function () {
        $('#modal-app-id').val($(this).data('app-id'));
        $('#modal-student-name').text($(this).data('student'));
        $('textarea[name="override_reason"]').val('');
        $('#overrideModal').modal('show');
    });

    // ── DataTable ────────────────────────────────────────────────────
    if ($.fn.DataTable && $('#ineligTable').length) {
        $('#ineligTable').DataTable({
            order: [[4, 'asc']],
            pageLength: 25,
            language: { search: 'Filter:' },
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    }
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'eligibility']); ?>
