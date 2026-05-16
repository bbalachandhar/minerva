<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<style>
/* ── stat cards ── */
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
/* ── chart / table boxes ── */
.chart-box{background:#fff;border-radius:10px;padding:18px 20px;box-shadow:0 2px 10px rgba(0,0,0,.07);margin-bottom:20px;}
.chart-box h4{margin-top:0;font-size:14px;font-weight:600;color:#444;border-bottom:1px solid #f0f0f0;padding-bottom:9px;margin-bottom:14px;}
/* ── pill styles ── */
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
/* ── setup panel ── */
.setup-box{background:#fff;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.09);margin-bottom:22px;overflow:hidden;}
.setup-box .setup-header{padding:14px 20px;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:space-between;}
.setup-box .setup-body{padding:18px 20px;}
.subject-cb-label{display:flex;align-items:center;padding:7px 12px;border-radius:8px;cursor:pointer;transition:background .15s;margin-bottom:4px;border:1px solid #e9ecef;}
.subject-cb-label:hover{background:#f0f7ff;border-color:#b8daff;}
.subject-cb-label input[type=checkbox]{margin-right:10px;width:16px;height:16px;}
.subject-cb-label.selected{background:#e8f5e9;border-color:#a5d6a7;}
.arrear-badge{display:inline-block;background:#fff3cd;color:#856404;font-size:11px;font-weight:700;padding:2px 8px;border-radius:10px;margin-left:auto;}
.candidate-row{display:flex;align-items:flex-start;gap:12px;padding:10px 14px;border-radius:8px;border:1px solid #e9ecef;margin-bottom:6px;}
.candidate-row .reg{font-family:monospace;font-size:11px;color:#666;min-width:100px;}
.candidate-row .name{font-weight:600;font-size:13px;min-width:160px;}
.candidate-row .subs{display:flex;flex-wrap:wrap;gap:5px;}
.candidate-row .sub-tag{background:#e3f2fd;color:#0d47a1;font-size:11px;padding:2px 8px;border-radius:10px;}
.step-badge{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-size:13px;font-weight:700;color:#fff;margin-right:8px;flex-shrink:0;}
.step1-color{background:#3c8dbc;}
.step2-color{background:#f39c12;}
.step3-color{background:#00a65a;}
.setup-divider{border-top:1px solid #f0f0f0;margin:16px 0;}
.empty-setup-hint{text-align:center;padding:30px 20px;color:#999;}
.empty-setup-hint i{font-size:40px;margin-bottom:10px;display:block;color:#ddd;}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <?php
        $cat_map = ['main' => ['label-primary','Main'], 'arrear' => ['label-danger','Arrear'], 'supplementary' => ['label-warning','Supplementary']];
        $cat_info = $cat_map[$event->exam_category] ?? ['label-default', ucfirst($event->exam_category)];
        ?>
        <h1>
            <i class="fa fa-list-alt"></i>
            <?php echo htmlspecialchars($event->exam); ?>
            <span class="label <?php echo $cat_info[0]; ?>" style="font-size:13px;vertical-align:middle;"><?php echo $cat_info[1]; ?></span>
            <?php if ($event->class ?? ''): ?>
                <span class="label label-default" style="font-size:12px;vertical-align:middle;"><?php echo htmlspecialchars($event->class ?? ''); ?></span>
            <?php endif; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_application'); ?>">Exam Applications</a></li>
            <li class="active"><?php echo htmlspecialchars($event->exam); ?></li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <?php /* ── MAIN EXAM: subjects not yet assigned warning ── */
        if (!$is_arrear && isset($main_subject_count) && $main_subject_count === 0): ?>
        <div class="alert alert-warning alert-dismissible" style="border-radius:8px;border-left:5px solid #f39c12;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong><i class="fa fa-exclamation-triangle"></i> Subjects not assigned!</strong>
            &nbsp;No exam subjects are configured for this batch yet.
            <strong>Generate Applications</strong> and <strong>Run Eligibility</strong> will fail until subjects are assigned.
            &nbsp;&nbsp;
            <a href="<?php echo site_url('coe/coe_subject/assign/' . $event->id); ?>" class="btn btn-warning btn-xs" style="border-radius:4px;margin-top:-2px;">
                <i class="fa fa-book"></i> Assign Subjects Now
            </a>
        </div>
        <?php elseif (!$is_arrear && isset($main_subject_count) && $main_subject_count > 0): ?>
        <div style="margin-bottom:10px;">
            <a href="<?php echo site_url('coe/coe_subject/assign/' . $event->id); ?>" class="btn btn-default btn-xs" style="border-radius:4px;">
                <i class="fa fa-book text-info"></i> Subjects: <strong><?php echo $main_subject_count; ?></strong> assigned
                &nbsp;<i class="fa fa-pencil"></i>
            </a>
        </div>
        <?php endif; ?>

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

<?php /* ═══════════════════════════════════════════════════════════════════
       ARREAR / SUPPLEMENTARY SETUP PANEL
       ═══════════════════════════════════════════════════════════════════ */
if ($is_arrear):
    $subjects_data     = $subjects_data ?? (object)['subjects' => [], 'configured_ids' => []];
    $all_subjects      = $subjects_data->subjects ?? [];
    $configured_ids    = $subjects_data->configured_ids ?? [];
    $candidates_data   = $candidates ?? ['students' => [], 'total_pairs' => 0, 'subject_ids' => []];
    $cand_students     = $candidates_data['students'] ?? [];
    $cand_total_pairs  = $candidates_data['total_pairs'] ?? 0;
    $subjects_saved    = !empty($configured_ids);
    $cand_count        = count($cand_students);
    $is_locked         = (bool)($event->coe_locked ?? false);
?>
        <!-- ── STEP 1: SUBJECTS ─────────────────────────────────────── -->
        <div class="row">
            <div class="col-md-5">
                <div class="setup-box">
                    <div class="setup-header" style="background:<?php echo $subjects_saved ? '#e8f5e9' : '#fff8e1'; ?>;">
                        <span>
                            <span class="step-badge step1-color">1</span>
                            Exam Subjects
                            <?php if ($subjects_saved): ?>
                                <small class="text-success" style="font-size:11px;font-weight:400;">
                                    &nbsp;<i class="fa fa-check-circle"></i> <?php echo count($configured_ids); ?> configured
                                </small>
                            <?php else: ?>
                                <small class="text-warning" style="font-size:11px;font-weight:400;">
                                    &nbsp;<i class="fa fa-exclamation-circle"></i> Not configured yet
                                </small>
                            <?php endif; ?>
                        </span>
                        <a class="btn btn-xs btn-default" data-toggle="collapse" href="#subjectSetupBody" role="button">
                            <i class="fa fa-<?php echo $subjects_saved ? 'pencil' : 'plus'; ?>"></i>
                            <?php echo $subjects_saved ? 'Edit' : 'Setup'; ?>
                        </a>
                    </div>
                    <div class="collapse <?php echo !$subjects_saved ? 'in' : ''; ?>" id="subjectSetupBody">
                        <div class="setup-body">
                            <?php if (empty($all_subjects)): ?>
                                <div class="empty-setup-hint">
                                    <i class="fa fa-search"></i>
                                    No subjects with active arrears found for this class.<br>
                                    <small>Arrears are detected from published end-semester results.</small>
                                </div>
                            <?php else: ?>
                                <p class="text-muted" style="font-size:12px;margin-bottom:12px;">
                                    <i class="fa fa-info-circle text-info"></i>
                                    Subjects with active arrears are pre-checked. The number shows how many students have an outstanding arrear in each subject.
                                </p>
                                <?php if (!$is_locked): ?>
                                <form method="post" action="<?php echo site_url('coe/coe_application/save_subjects/' . $event->id); ?>" id="subjectsForm">
                                    <div style="margin-bottom:8px;">
                                        <a href="#" id="selAllSubs" style="font-size:11px;">Select All</a> &nbsp;|&nbsp;
                                        <a href="#" id="selNoneSubs" style="font-size:11px;">Clear All</a>
                                    </div>
                                    <?php foreach ($all_subjects as $sub):
                                        $checked = in_array($sub->id, $configured_ids);
                                    ?>
                                    <label class="subject-cb-label <?php echo $checked ? 'selected' : ''; ?>" id="lbl_sub_<?php echo $sub->id; ?>">
                                        <input type="checkbox" name="subject_ids[]" value="<?php echo $sub->id; ?>"
                                               <?php echo $checked ? 'checked' : ''; ?>
                                               onchange="toggleLbl(this)">
                                        <span style="flex:1;">
                                            <strong><?php echo htmlspecialchars($sub->name); ?></strong>
                                            <?php if ($sub->code ?? ''): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($sub->code); ?></small>
                                            <?php endif; ?>
                                        </span>
                                        <?php if (($sub->arrear_count ?? 0) > 0): ?>
                                            <span class="arrear-badge"><?php echo (int)$sub->arrear_count; ?> students</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php endforeach; ?>
                                    <div style="margin-top:14px;">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-save"></i> Save Subjects
                                        </button>
                                    </div>
                                </form>
                                <?php else: ?>
                                    <?php foreach ($all_subjects as $sub):
                                        $checked = in_array($sub->id, $configured_ids);
                                    ?>
                                    <label class="subject-cb-label <?php echo $checked ? 'selected' : ''; ?>">
                                        <input type="checkbox" disabled <?php echo $checked ? 'checked' : ''; ?>>
                                        <span style="flex:1;">
                                            <strong><?php echo htmlspecialchars($sub->name); ?></strong>
                                            <?php if ($sub->code ?? ''): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($sub->code); ?></small>
                                            <?php endif; ?>
                                        </span>
                                        <?php if (($sub->arrear_count ?? 0) > 0): ?>
                                            <span class="arrear-badge"><?php echo (int)$sub->arrear_count; ?> students</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php endforeach; ?>
                                    <p class="text-muted" style="margin-top:10px;font-size:12px;"><i class="fa fa-lock"></i> Subjects locked — exam is CoE-locked.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── STEP 2: CANDIDATES ─────────────────────────────── -->
            <div class="col-md-7">
                <div class="setup-box">
                    <div class="setup-header" style="background:<?php echo ($cand_count > 0) ? '#fff3cd' : '#f9f9f9'; ?>;">
                        <span>
                            <span class="step-badge step2-color">2</span>
                            Arrear Candidates
                            <?php if ($cand_count > 0): ?>
                                <small class="text-warning" style="font-size:11px;font-weight:400;">
                                    &nbsp;<i class="fa fa-users"></i>
                                    <?php echo $cand_count; ?> student<?php echo $cand_count !== 1 ? 's' : ''; ?>,
                                    <?php echo $cand_total_pairs; ?> application slot<?php echo $cand_total_pairs !== 1 ? 's' : ''; ?> detected
                                </small>
                            <?php elseif (!$subjects_saved): ?>
                                <small class="text-muted" style="font-size:11px;font-weight:400;">
                                    &nbsp;Configure subjects first
                                </small>
                            <?php else: ?>
                                <small class="text-muted" style="font-size:11px;font-weight:400;">
                                    &nbsp;No active arrears found
                                </small>
                            <?php endif; ?>
                        </span>
                        <?php if ($cand_count > 0 && !$is_locked && $total === 0 && $this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                            <a href="<?php echo site_url('coe/coe_application/generate/' . $event->id); ?>"
                               class="btn btn-success btn-sm"
                               onclick="return confirm('Generate <?php echo $cand_total_pairs; ?> application records for <?php echo $cand_count; ?> students? This action auto-enrolls and creates pending applications.');">
                                <i class="fa fa-bolt"></i> Generate Applications
                            </a>
                        <?php elseif ($cand_count > 0 && !$is_locked && $total > 0 && $this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                            <a href="<?php echo site_url('coe/coe_application/generate/' . $event->id); ?>"
                               class="btn btn-warning btn-sm"
                               onclick="return confirm('Re-sync applications? New arrear students will be added. Existing records are kept (duplicates skipped).');">
                                <i class="fa fa-refresh"></i> Re-sync
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="setup-body" style="max-height:380px;overflow-y:auto;">
                        <?php if (!$subjects_saved): ?>
                            <div class="empty-setup-hint">
                                <i class="fa fa-arrow-left"></i>
                                Select and save the exam subjects first.<br>
                                <small>The system will then detect eligible arrear students automatically.</small>
                            </div>
                        <?php elseif (empty($cand_students)): ?>
                            <div class="empty-setup-hint">
                                <i class="fa fa-check-circle" style="color:#28a745;"></i>
                                No active arrears detected for the selected subjects.<br>
                                <small>All students may have already cleared these subjects.</small>
                            </div>
                        <?php else: ?>
                            <p class="text-muted" style="font-size:12px;margin-bottom:10px;">
                                <i class="fa fa-info-circle text-info"></i>
                                These students have uncleared arrears in the selected subjects.
                                Click <strong>Generate Applications</strong> to commit them.
                            </p>
                            <?php foreach ($cand_students as $cs): ?>
                            <div class="candidate-row">
                                <div>
                                    <div class="name"><?php echo htmlspecialchars($cs['firstname'] . ' ' . $cs['lastname']); ?></div>
                                    <div class="reg"><?php echo htmlspecialchars($cs['register_no'] ?? '—'); ?></div>
                                </div>
                                <div class="subs">
                                    <?php foreach ($cs['subjects'] as $csub): ?>
                                        <span class="sub-tag" title="<?php echo htmlspecialchars($csub['subject_name']); ?>">
                                            <?php echo htmlspecialchars($csub['subject_code'] ?: $csub['subject_name']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- /row setup -->

<?php endif; /* end is_arrear */ ?>

        <?php /* ═══════════════════════════════════════════════════════
               STEP 3 (arrear) / MAIN: APPLICATIONS
               ═══════════════════════════════════════════════════════ */
        if ($is_arrear && $total === 0): ?>
        <div class="setup-box" style="padding:24px;text-align:center;color:#aaa;">
            <span class="step-badge step3-color" style="display:inline-flex;margin-bottom:10px;">3</span>
            <br>
            <i class="fa fa-bolt" style="font-size:32px;color:#ddd;display:block;margin-bottom:8px;"></i>
            Applications will appear here after you click <strong>Generate Applications</strong> above.
        </div>
        <?php else: ?>

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
            <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
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
                </form>
                <div>
                    <a href="<?php echo site_url('coe/coe_eligibility?batch_exam_id=' . $event->id); ?>" class="btn btn-info btn-sm" style="border-radius:6px;">
                        <i class="fa fa-check-square-o"></i> Check Eligibility &rarr;
                    </a>
                    <?php if (!($event->coe_locked ?? false) && $this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                    <a href="<?php echo site_url('coe/coe_application/generate/' . $event->id); ?>"
                       class="btn btn-default btn-sm" style="border-radius:6px;margin-left:5px;"
                       onclick="return confirm('Re-sync: add any new eligible students. Existing applications are kept.');">
                        <i class="fa fa-refresh"></i> Re-sync
                    </a>
                    <?php endif; ?>
                </div>
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
                            $att = $app->attendance_pct ?? null;
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
                            <td><?php echo $app->ineligible_reason ?? '' ? '<small style="color:#888;">'.ucfirst(str_replace('_',' ',$app->ineligible_reason)).'</small>' : '<span class="text-muted">—</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; /* end has applications */ ?>
    </section>
</div>

<script>
(function(){
    <?php if ($total > 0): ?>
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
    <?php endif; ?>
    $(function(){
        <?php if ($total > 0): ?>
        if($.fn.DataTable&&$('#appsTable').length){
            $('#appsTable').DataTable({order:[[2,'asc'],[3,'asc']],pageLength:50,language:{search:'Filter:'}});
        }
        <?php endif; ?>
        // Subject checkbox toggle
        function toggleLbl(cb){
            var $lbl = $(cb).closest('.subject-cb-label');
            $lbl.toggleClass('selected', cb.checked);
        }
        window.toggleLbl = toggleLbl;
        $('#selAllSubs').on('click',function(e){
            e.preventDefault();
            $('#subjectsForm input[type=checkbox]').prop('checked',true).each(function(){toggleLbl(this);});
        });
        $('#selNoneSubs').on('click',function(e){
            e.preventDefault();
            $('#subjectsForm input[type=checkbox]').prop('checked',false).each(function(){toggleLbl(this);});
        });
    });
})();
</script>

