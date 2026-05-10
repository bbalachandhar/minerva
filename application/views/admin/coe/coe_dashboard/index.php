<!-- CoE Central Dashboard -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-tachometer"></i> CoE Dashboard
            <small>Controller of Examinations — Central Overview</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">CoE Dashboard</li>
        </ol>
    </section>

    <section class="content">

        <!-- Session Selector -->
        <div class="row">
            <div class="col-md-12">
                <form method="get" class="form-inline" style="margin-bottom:15px">
                    <div class="form-group">
                        <label style="margin-right:8px"><i class="fa fa-calendar"></i> Academic Session &nbsp;</label>
                        <select name="session_id" class="form-control" onchange="this.form.submit()">
                            <?php foreach ($sessions as $sess): ?>
                            <option value="<?php echo $sess->id; ?>"
                                <?php echo $sess->id == $session_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sess->session); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPI Cards Row 1 -->
        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo $kpis['total_events']; ?></h3>
                        <p>Exam Events</p>
                    </div>
                    <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
                    <a href="<?php echo site_url('coe/coe_application'); ?>" class="small-box-footer">
                        View Events <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $kpis['total_apps']; ?></h3>
                        <p>Total Applicants</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="<?php echo site_url('coe/coe_eligibility'); ?>" class="small-box-footer">
                        Eligibility <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?php echo $kpis['total_ht']; ?></h3>
                        <p>Hall Tickets</p>
                    </div>
                    <div class="icon"><i class="fa fa-id-card-o"></i></div>
                    <a href="<?php echo site_url('coe/coe_hallticket'); ?>" class="small-box-footer">
                        Hall Tickets <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $kpis['pass_pct']; ?>%</h3>
                        <p>Overall Pass Rate</p>
                    </div>
                    <div class="icon"><i class="fa fa-line-chart"></i></div>
                    <a href="<?php echo site_url('coe/coe_results'); ?>" class="small-box-footer">
                        Results <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <?php $sgpa = $kpis['avg_sgpa']; ?>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box <?php echo $sgpa >= 8 ? 'bg-green' : ($sgpa >= 6 ? 'bg-yellow' : ($sgpa > 0 ? 'bg-orange' : 'bg-gray')); ?>">
                    <div class="inner">
                        <h3><?php echo $sgpa > 0 ? number_format($sgpa, 2) : '—'; ?></h3>
                        <p>Avg. SGPA</p>
                    </div>
                    <div class="icon"><i class="fa fa-star-half-o"></i></div>
                    <a href="<?php echo site_url('coe/coe_results'); ?>" class="small-box-footer">
                        SGPA Details <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-orange">
                    <div class="inner">
                        <h3><?php echo $kpis['arrear_students']; ?></h3>
                        <p>Arrear Students</p>
                    </div>
                    <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
                    <a href="<?php echo site_url('coe/coe_arrear?session_id='.$session_id); ?>" class="small-box-footer">
                        Arrear Register <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box <?php echo $kpis['ufm_count'] > 0 ? 'bg-red' : 'bg-gray'; ?>">
                    <div class="inner">
                        <h3><?php echo $kpis['ufm_count']; ?></h3>
                        <p>UFM Incidents</p>
                    </div>
                    <div class="icon"><i class="fa fa-ban"></i></div>
                    <a href="<?php echo site_url('coe/coe_ufm'); ?>" class="small-box-footer">
                        UFM Register <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div><!-- /.row KPI row 1 -->

        <!-- KPI Cards Row 2 -->
        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?php echo $kpis['published_events']; ?> / <?php echo $kpis['total_events']; ?></h3>
                        <p>Results Published</p>
                    </div>
                    <div class="icon"><i class="fa fa-bullhorn"></i></div>
                    <a href="<?php echo site_url('coe/coe_results'); ?>" class="small-box-footer">
                        Publish <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box <?php echo $kpis['rev_pending'] > 0 ? 'bg-red' : 'bg-gray'; ?>">
                    <div class="inner">
                        <h3><?php echo $kpis['rev_pending']; ?></h3>
                        <p>Revaluation Pending</p>
                    </div>
                    <div class="icon"><i class="fa fa-refresh"></i></div>
                    <a href="<?php echo site_url('coe/coe_revaluation'); ?>" class="small-box-footer">
                        Revaluation <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3><?php echo $kpis['total_computed']; ?></h3>
                        <p>SGPA Computed</p>
                    </div>
                    <div class="icon"><i class="fa fa-calculator"></i></div>
                    <a href="<?php echo site_url('coe/coe_marks'); ?>" class="small-box-footer">
                        Marks Module <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-6 col-md-8 col-sm-12">
                <?php
                $max_apps = max(1, $kpis['total_apps']);
                $pct_ht   = min(100, round($kpis['total_ht']        / $max_apps * 100));
                $pct_sgpa = min(100, round($kpis['total_computed']   / $max_apps * 100));
                $pct_pub  = $kpis['total_events'] > 0
                    ? round($kpis['published_events'] / $kpis['total_events'] * 100) : 0;
                ?>
                <div class="box box-solid bg-light-blue-active" style="margin-bottom:0">
                    <div class="box-header" style="padding:8px 14px">
                        <h4 style="margin:0;color:#fff"><i class="fa fa-tasks"></i> Session Completion Progress</h4>
                    </div>
                    <div class="box-body" style="background:#fff;padding:10px 16px">
                        <div class="row">
                            <div class="col-sm-4">
                                <small class="text-muted">Applications → Hall Tickets</small>
                                <div class="progress progress-sm" style="margin:3px 0 6px">
                                    <div class="progress-bar progress-bar-success" style="width:<?php echo $pct_ht; ?>%" title="<?php echo $pct_ht; ?>%"></div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted">Applications → SGPA Computed</small>
                                <div class="progress progress-sm" style="margin:3px 0 6px">
                                    <div class="progress-bar progress-bar-info" style="width:<?php echo $pct_sgpa; ?>%" title="<?php echo $pct_sgpa; ?>%"></div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted">Events Published</small>
                                <div class="progress progress-sm" style="margin:3px 0 6px">
                                    <div class="progress-bar progress-bar-warning" style="width:<?php echo $pct_pub; ?>%" title="<?php echo $pct_pub; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fa fa-circle text-green"></i> <?php echo $pct_ht; ?>% HT &nbsp;
                            <i class="fa fa-circle text-light-blue"></i> <?php echo $pct_sgpa; ?>% SGPA &nbsp;
                            <i class="fa fa-circle text-yellow"></i> <?php echo $pct_pub; ?>% Published
                        </small>
                    </div>
                </div>
            </div>
        </div><!-- /.row KPI row 2 -->

        <!-- Pending Tasks Alert -->
        <?php if ($pending_tasks['no_hall_tickets'] + $pending_tasks['no_marks'] + $pending_tasks['no_results'] > 0): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-warning">
                    <h4><i class="fa fa-clock-o"></i> Pending Actions</h4>
                    <ul style="margin-bottom:0">
                        <?php if ($pending_tasks['no_hall_tickets'] > 0): ?>
                        <li><?php echo $pending_tasks['no_hall_tickets']; ?> event(s) have no hall tickets generated yet.
                            <a href="<?php echo site_url('coe/coe_hallticket'); ?>">Generate &rarr;</a>
                        </li>
                        <?php endif; ?>
                        <?php if ($pending_tasks['no_marks'] > 0): ?>
                        <li><?php echo $pending_tasks['no_marks']; ?> event(s) have no marks entered yet.
                            <a href="<?php echo site_url('coe/coe_marks'); ?>">Enter Marks &rarr;</a>
                        </li>
                        <?php endif; ?>
                        <?php if ($pending_tasks['no_results'] > 0): ?>
                        <li><?php echo $pending_tasks['no_results']; ?> event(s) have no SGPA computed yet.
                            <a href="<?php echo site_url('coe/coe_marks'); ?>">Compute SGPA &rarr;</a>
                        </li>
                        <?php endif; ?>
                        <?php if ($kpis['rev_pending'] > 0): ?>
                        <li><?php echo $kpis['rev_pending']; ?> revaluation request(s) awaiting action.
                            <a href="<?php echo site_url('coe/coe_revaluation'); ?>">Review &rarr;</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Event Pipeline Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-tasks"></i> Exam Event Pipeline Status
                        </h3>
                        <div class="box-tools">
                            <a href="<?php echo site_url('coe/coe_application'); ?>" class="btn btn-xs btn-default">
                                <i class="fa fa-plus"></i> New Event
                            </a>
                        </div>
                    </div>
                    <div class="box-body" style="overflow-x:auto">
                        <?php if (empty($events)): ?>
                        <div class="callout callout-info">
                            <p>No exam events found for this session. <a href="<?php echo site_url('coe/coe_application'); ?>">Create one &rarr;</a></p>
                        </div>
                        <?php else: ?>
                        <table class="table table-bordered table-hover" style="white-space:nowrap;font-size:12px">
                            <thead>
                                <tr class="bg-light-blue-active text-white">
                                    <th>Exam Event</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th title="Applications">
                                        <abbr title="Exam Applications"><i class="fa fa-file-text"></i> Apps</abbr>
                                    </th>
                                    <th title="Hall Tickets">
                                        <abbr title="Hall Tickets"><i class="fa fa-id-card"></i> H.T.</abbr>
                                    </th>
                                    <th title="Nominal Rolls">
                                        <abbr title="Nominal Rolls"><i class="fa fa-list-ol"></i> N.Roll</abbr>
                                    </th>
                                    <th title="Seating Rooms">
                                        <abbr title="Seating Rooms"><i class="fa fa-th"></i> Seats</abbr>
                                    </th>
                                    <th title="Question Papers">
                                        <abbr title="Question Paper Dist."><i class="fa fa-file"></i> QPD</abbr>
                                    </th>
                                    <th title="Answer Scripts">
                                        <abbr title="Answer Scripts"><i class="fa fa-pencil-square"></i> Scripts</abbr>
                                    </th>
                                    <th title="Marks Entered">
                                        <abbr title="Marks Entered (students)"><i class="fa fa-edit"></i> Marks</abbr>
                                    </th>
                                    <th title="SGPA Computed">
                                        <abbr title="SGPA Computed"><i class="fa fa-calculator"></i> SGPA</abbr>
                                    </th>
                                    <th>Pass / Fail</th>
                                    <th title="Pass %">Pass %</th>
                                    <th>Pipeline</th>
                                    <th title="Result Status">Published</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $evt): ?>
                                <?php
                                $stages_done = 0;
                                if ($evt->app_count      > 0) $stages_done++;
                                if ($evt->ht_count       > 0) $stages_done++;
                                if ($evt->rooms_count    > 0) $stages_done++;
                                if ($evt->script_count   > 0) $stages_done++;
                                if ($evt->marks_students > 0) $stages_done++;
                                if ($evt->sgpa_count     > 0) $stages_done++;
                                $comp_pct = round($stages_done / 6 * 100);
                                $comp_cls = $comp_pct >= 80 ? 'success' : ($comp_pct >= 40 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($evt->event_name); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($evt->exam); ?></small>
                                    </td>
                                    <td>
                                        <span class="label label-<?php
                                            echo $evt->exam_category === 'arrear'        ? 'warning' :
                                                ($evt->exam_category === 'supplementary' ? 'info'    : 'primary');
                                        ?>">
                                            <?php echo ucfirst($evt->exam_category ?: 'Regular'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $evt->date_from ? date('d M Y', strtotime($evt->date_from)) : '—'; ?>
                                        <?php if ($evt->date_to): ?>
                                        <br><small class="text-muted">to <?php echo date('d M Y', strtotime($evt->date_to)); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $evt->app_count > 0 ? '<span class="badge bg-green">'.$evt->app_count.'</span>' : '<span class="badge bg-red">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->ht_count > 0 ? '<span class="badge bg-green">'.$evt->ht_count.'</span>' : '<span class="badge bg-red">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->nr_count > 0 ? '<span class="badge bg-green">'.$evt->nr_count.'</span>' : '<span class="badge bg-yellow">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->rooms_count > 0 ? '<span class="badge bg-green">'.$evt->rooms_count.'</span>' : '<span class="badge bg-yellow">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->qpd_count > 0 ? '<span class="badge bg-green">'.$evt->qpd_count.'</span>' : '<span class="badge bg-yellow">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->script_count > 0 ? '<span class="badge bg-green">'.$evt->script_count.'</span>' : '<span class="badge bg-yellow">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->marks_students > 0 ? '<span class="badge bg-green">'.$evt->marks_students.' stu.</span>' : '<span class="badge bg-red">0</span>'; ?></td>
                                    <td class="text-center"><?php echo $evt->sgpa_count > 0 ? '<span class="badge bg-green">'.$evt->sgpa_count.'</span>' : '<span class="badge bg-red">—</span>'; ?></td>
                                    <td class="text-center" style="min-width:90px">
                                        <?php if ($evt->pass_count + $evt->fail_count > 0): ?>
                                        <span class="text-green" title="Passed"><i class="fa fa-check-circle"></i> <?php echo $evt->pass_count; ?></span>
                                        &nbsp;
                                        <span class="text-red" title="Failed"><i class="fa fa-times-circle"></i> <?php echo $evt->fail_count; ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($evt->pass_pct !== null): ?>
                                        <span class="badge <?php echo $evt->pass_pct >= 75 ? 'bg-green' : ($evt->pass_pct >= 50 ? 'bg-yellow' : 'bg-red'); ?>">
                                            <?php echo $evt->pass_pct; ?>%
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="min-width:80px">
                                        <div class="progress progress-xs" style="margin:2px 0 1px" title="<?php echo $stages_done; ?>/6 stages complete">
                                            <div class="progress-bar progress-bar-<?php echo $comp_cls; ?>" style="width:<?php echo $comp_pct; ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?php echo $stages_done; ?>/6</small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($evt->is_publish): ?>
                                        <span class="label label-success"><i class="fa fa-check"></i> Yes</span>
                                        <?php else: ?>
                                        <span class="label label-default">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space:nowrap">
                                        <a href="<?php echo site_url('coe/coe_results/listing/'.$evt->batch_exam_id); ?>"
                                           class="btn btn-xs btn-primary" title="Results">
                                            <i class="fa fa-bar-chart"></i>
                                        </a>
                                        <a href="<?php echo site_url('coe/coe_results/tabulation/'.$evt->batch_exam_id); ?>"
                                           class="btn btn-xs btn-default" title="Tabulation Sheet">
                                            <i class="fa fa-table"></i>
                                        </a>
                                        <a href="<?php echo site_url('coe/coe_results/merit_list/'.$evt->batch_exam_id); ?>"
                                           class="btn btn-xs btn-info" title="Merit List">
                                            <i class="fa fa-trophy"></i>
                                        </a>
                                        <a href="<?php echo site_url('coe/coe_marks/listing/'.$evt->batch_exam_id); ?>"
                                           class="btn btn-xs btn-warning" title="Marks Entry">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <?php if ($evt->ufm_count > 0): ?>
                                        <a href="<?php echo site_url('coe/coe_ufm/listing/'.$evt->batch_exam_id); ?>"
                                           class="btn btn-xs btn-danger" title="<?php echo $evt->ufm_count; ?> UFM Incident(s)">
                                            <i class="fa fa-ban"></i> <?php echo $evt->ufm_count; ?>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- /.row pipeline -->

        <!-- Department Stats + Subject Performance -->
        <div class="row">
            <!-- Department-wise breakdown -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-university"></i> Department-wise Results</h3>
                    </div>
                    <div class="box-body" style="padding:0">
                        <?php if (empty($dept_stats)): ?>
                        <div class="callout callout-info" style="margin:12px">
                            <p class="text-muted">No SGPA data computed yet for this session.</p>
                        </div>
                        <?php else: ?>
                        <table class="table table-condensed table-hover" style="margin-bottom:0;font-size:12px">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Pass</th>
                                    <th class="text-center">Fail</th>
                                    <th class="text-center">Avg SGPA</th>
                                    <th class="text-center">Avg CGPA</th>
                                    <th class="text-center">Arrears</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dept_stats as $dept): ?>
                                <?php
                                $d_pct = $dept->total_students > 0 ? round($dept->passed / $dept->total_students * 100, 1) : 0;
                                $d_cls = $d_pct >= 75 ? 'success' : ($d_pct >= 50 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dept->department_name); ?></strong></td>
                                    <td class="text-center"><?php echo $dept->total_students; ?></td>
                                    <td class="text-center text-green"><strong><?php echo $dept->passed; ?></strong></td>
                                    <td class="text-center text-red"><?php echo $dept->failed; ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $dept->avg_sgpa >= 8 ? 'bg-green' : ($dept->avg_sgpa >= 6 ? 'bg-yellow' : 'bg-red'); ?>">
                                            <?php echo number_format($dept->avg_sgpa, 2); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $dept->avg_cgpa > 0 ? number_format($dept->avg_cgpa, 2) : '—'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($dept->total_arrears > 0): ?>
                                        <span class="label label-warning"><?php echo $dept->total_arrears; ?></span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="min-width:90px">
                                        <div class="progress progress-xs" style="margin:3px 0 1px">
                                            <div class="progress-bar progress-bar-<?php echo $d_cls; ?>" style="width:<?php echo $d_pct; ?>%"></div>
                                        </div>
                                        <small><?php echo $d_pct; ?>%</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Subject-wise performance -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-book"></i> Subject Performance</h3>
                        <div class="box-tools pull-right">
                            <small class="text-muted" style="line-height:2.2">sorted: lowest pass rate first</small>
                        </div>
                    </div>
                    <div class="box-body" style="padding:0">
                        <?php if (empty($subject_stats)): ?>
                        <div class="callout callout-info" style="margin:12px">
                            <p class="text-muted">No marks data available yet for this session.</p>
                        </div>
                        <?php else: ?>
                        <table class="table table-condensed table-hover" style="margin-bottom:0;font-size:12px">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Pass</th>
                                    <th class="text-center">Fail</th>
                                    <th class="text-center">Avg</th>
                                    <th class="text-center">Hi / Lo</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subject_stats as $sub): ?>
                                <?php
                                $s_pct = $sub->total > 0 ? round($sub->passed / $sub->total * 100, 1) : 0;
                                $s_cls = $s_pct >= 75 ? 'success' : ($s_pct >= 50 ? 'warning' : 'danger');
                                ?>
                                <tr>
                                    <td>
                                        <span class="text-muted" style="font-size:10px"><?php echo htmlspecialchars($sub->subject_code); ?></span><br>
                                        <?php echo htmlspecialchars($sub->subject_name); ?>
                                    </td>
                                    <td class="text-center"><?php echo $sub->total; ?></td>
                                    <td class="text-center text-green"><strong><?php echo $sub->passed; ?></strong></td>
                                    <td class="text-center text-red"><?php echo $sub->failed; ?></td>
                                    <td class="text-center"><?php echo number_format($sub->avg_marks, 1); ?></td>
                                    <td class="text-center">
                                        <span class="text-green"><?php echo number_format($sub->max_marks, 0); ?></span>
                                        /
                                        <span class="text-red"><?php echo number_format($sub->min_marks, 0); ?></span>
                                    </td>
                                    <td style="min-width:80px">
                                        <div class="progress progress-xs" style="margin:3px 0 1px">
                                            <div class="progress-bar progress-bar-<?php echo $s_cls; ?>" style="width:<?php echo $s_pct; ?>%"></div>
                                        </div>
                                        <small><?php echo $s_pct; ?>%</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- /.row dept+subject -->

        <!-- Quick Links + Recent Audit -->
        <div class="row">
            <!-- Quick Links -->
            <div class="col-md-3">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Links</h3>
                    </div>
                    <div class="box-body" style="padding:10px">
                        <?php
                        $links = [
                            ['coe/coe_setup',          'fa-cog',              'Exam Regulations',       ''],
                            ['coe/coe_application',    'fa-calendar-plus-o',  'Exam Events',            ''],
                            ['coe/coe_eligibility',    'fa-check-circle',     'Eligibility Check',      ''],
                            ['coe/coe_hallticket',     'fa-id-card-o',        'Hall Tickets',           $pending_tasks['no_hall_tickets'] > 0 ? 'list-group-item-danger' : ''],
                            ['coe/coe_nominalroll',    'fa-list-ol',          'Nominal Roll',           ''],
                            ['coe/coe_seating',        'fa-th',               'Seating Arrangement',    ''],
                            ['coe/coe_invigilation',   'fa-eye',              'Invigilation Duty',      ''],
                            ['coe/coe_qpd',            'fa-file-text',        'Question Paper Dist.',   ''],
                            ['coe/coe_attendance',     'fa-clipboard',        'Exam Attendance',        ''],
                            ['coe/coe_ufm',            'fa-ban',              'UFM / Malpractice',      $kpis['ufm_count'] > 0 ? 'list-group-item-danger' : ''],
                            ['coe/coe_answer_scripts', 'fa-pencil-square-o',  'Answer Scripts',         ''],
                            ['coe/coe_osm',            'fa-desktop',          'OSM Marking',            ''],
                            ['coe/coe_revaluation',    'fa-refresh',          'Revaluation',            $kpis['rev_pending'] > 0 ? 'list-group-item-warning' : ''],
                            ['coe/coe_moderation',     'fa-sliders',          'Moderation / Grace',     ''],
                            ['coe/coe_marks',          'fa-edit',             'Marks Entry',            $pending_tasks['no_marks'] > 0 ? 'list-group-item-warning' : ''],
                            ['coe/coe_results',        'fa-bullhorn',         'Result Publication',     $pending_tasks['no_results'] > 0 ? 'list-group-item-warning' : ''],
                            ['coe/coe_arrear?session_id='.$session_id, 'fa-exclamation-triangle', 'Arrear Register', $kpis['arrear_students'] > 0 ? 'list-group-item-warning' : ''],
                        ];
                        ?>
                        <div class="list-group" style="margin-bottom:0">
                            <?php foreach ($links as $l): ?>
                            <a href="<?php echo site_url($l[0]); ?>"
                               class="list-group-item <?php echo $l[3]; ?>"
                               style="padding:6px 10px;font-size:12px">
                                <i class="fa <?php echo $l[1]; ?> fa-fw text-light-blue"></i>
                                &nbsp;<?php echo $l[2]; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Audit Log -->
            <div class="col-md-9">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-history"></i> Recent Activity Log</h3>
                    </div>
                    <div class="box-body" style="padding:0">
                        <table class="table table-condensed table-hover" style="margin-bottom:0;font-size:12px">
                            <thead>
                                <tr>
                                    <th style="width:130px">Time</th>
                                    <th>Staff</th>
                                    <th style="width:140px">Action</th>
                                    <th>Module</th>
                                    <th style="width:70px" class="text-center">Record #</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_audit)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No activity recorded yet.</td></tr>
                                <?php else: ?>
                                <?php foreach ($recent_audit as $log): ?>
                                <tr>
                                    <td><small><?php echo date('d M Y H:i', strtotime($log->created_at)); ?></small></td>
                                    <td>
                                        <i class="fa fa-user-circle text-muted"></i>
                                        <?php echo htmlspecialchars($log->staff_name ?: 'System'); ?>
                                    </td>
                                    <td>
                                        <span class="label label-<?php
                                            $a = strtolower($log->action);
                                            if (strpos($a, 'delete') !== false)                                          echo 'danger';
                                            elseif (strpos($a, 'publish') !== false || strpos($a, 'apply') !== false)   echo 'success';
                                            elseif (strpos($a, 'generate') !== false || strpos($a, 'save') !== false
                                                 || strpos($a, 'add')      !== false || strpos($a, 'insert') !== false) echo 'info';
                                            elseif (strpos($a, 'compute') !== false || strpos($a, 'bulk') !== false)    echo 'warning';
                                            else echo 'default';
                                        ?>">
                                            <?php echo htmlspecialchars($log->action); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code style="font-size:11px"><?php echo htmlspecialchars($log->target_table); ?></code>
                                    </td>
                                    <td class="text-muted text-center">
                                        <?php echo $log->target_id ? '#'.$log->target_id : '—'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- /.row quick+audit -->

    </section>
</div>
<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'nominal_roll']); ?>
