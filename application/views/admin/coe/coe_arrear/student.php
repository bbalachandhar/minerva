<!-- Arrear Detail — Single Student -->
<style>
    @media print {
        .content-header, .main-header, .main-sidebar { display: none !important; }
        body, .content-wrapper { background: #fff; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-user"></i> Student Arrear Detail
            <small><?php echo htmlspecialchars($student->full_name); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_arrear'); ?>"><i class="fa fa-arrow-left"></i> Arrear Register</a></li>
            <li class="active"><?php echo htmlspecialchars($student->full_name); ?></li>
            <li>
                <button class="btn btn-xs btn-default" onclick="window.print()">
                    <i class="fa fa-print"></i> Print
                </button>
            </li>
        </ol>
    </section>

    <section class="content">

        <!-- Filter Bar -->
        <div class="box box-default" style="margin-bottom:10px">
            <div class="box-header with-border" style="padding:8px 14px">
                <h3 class="box-title"><i class="fa fa-filter"></i> Filter Arrear History</h3>
                <div class="box-tools">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body" style="padding:10px 14px">
                <form method="get" class="form-inline">
                    <?php
                    $base_url = site_url('coe/coe_arrear/student/' . $student->id);
                    $f = $filters;
                    ?>
                    <div class="form-group" style="margin-right:10px">
                        <label>Academic Session &nbsp;</label>
                        <select name="session_id" class="form-control input-sm"
                                onchange="this.form.submit()">
                            <option value="">All Sessions</option>
                            <?php foreach ($student_sessions as $sess): ?>
                            <option value="<?php echo $sess->id; ?>"
                                <?php echo $f['session_id'] == $sess->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sess->session); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:10px">
                        <label>Exam Event &nbsp;</label>
                        <select name="batch_exam_id" class="form-control input-sm"
                                onchange="this.form.submit()">
                            <option value="">All Events</option>
                            <?php foreach ($student_events as $evt): ?>
                            <option value="<?php echo $evt->batch_exam_id; ?>"
                                <?php echo $f['batch_exam_id'] == $evt->batch_exam_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evt->label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($f['session_id']): ?>
                    <input type="hidden" name="session_id" value="<?php echo $f['session_id']; ?>">
                    <?php endif; ?>
                    <div class="form-group" style="margin-right:12px">
                        <label>
                            <input type="checkbox" name="active_only" value="1"
                                <?php echo !empty($f['active_only']) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            &nbsp;Active arrears only
                            <small class="text-muted">(exclude already cleared)</small>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-search"></i> Apply
                    </button>
                    <a href="<?php echo site_url('coe/coe_arrear/student/' . $student->id); ?>"
                       class="btn btn-sm btn-default">
                        <i class="fa fa-times"></i> Clear
                    </a>
                    <?php if ($f['session_id'] || $f['batch_exam_id'] || $f['active_only']): ?>
                    <span class="label label-warning" style="margin-left:8px;vertical-align:middle">
                        <i class="fa fa-filter"></i> Filtered
                    </span>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Student Info + SGPA History Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary" style="margin-bottom:10px">
                    <div class="box-header with-border" style="padding:8px 14px">
                        <h3 class="box-title"><i class="fa fa-user-circle"></i> Student Information</h3>
                    </div>
                    <div class="box-body" style="padding:10px 14px">
                        <div class="row" style="margin:0">
                            <div class="col-sm-6" style="padding:0 8px 0 0">
                                <table class="table table-condensed" style="margin-bottom:0">
                                    <tr>
                                        <th style="width:110px;border-top:0">Full Name</th>
                                        <td style="border-top:0"><strong><?php echo htmlspecialchars($student->full_name); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Register No.</th>
                                        <td><?php echo htmlspecialchars($student->register_no ?: '—'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Admission No.</th>
                                        <td><?php echo htmlspecialchars($student->admission_no ?: '—'); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-sm-6" style="padding:0 0 0 8px;border-left:1px solid #f0f0f0">
                                <table class="table table-condensed" style="margin-bottom:0">
                                    <tr>
                                        <th style="width:90px;border-top:0">Class</th>
                                        <td style="border-top:0"><?php echo htmlspecialchars($student->class_name ?? '—'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Department</th>
                                        <td><?php echo htmlspecialchars($student->department_name ?? '—'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SGPA History -->
            <div class="col-md-6">
                <div class="box box-info" style="margin-bottom:10px">
                    <div class="box-header with-border" style="padding:8px 14px">
                        <h3 class="box-title"><i class="fa fa-line-chart"></i> SGPA History</h3>
                    </div>
                    <div class="box-body" style="padding:0">
                        <?php if (empty($sgpa_history)): ?>
                        <div class="callout callout-warning" style="margin:10px">
                            <p>No SGPA data computed yet.</p>
                        </div>
                        <?php else: ?>
                        <table class="table table-condensed" style="margin-bottom:0">
                            <thead>
                                <tr class="bg-light-blue-active text-white">
                                    <th>Exam</th>
                                    <th>Session</th>
                                    <th>SGPA</th>
                                    <th>CGPA</th>
                                    <th>Credits</th>
                                    <th>Arrears</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sgpa_history as $sg): ?>
                                <tr>
                                    <td><small><?php echo htmlspecialchars($sg->batch_exam_name); ?></small></td>
                                    <td><small><?php echo htmlspecialchars($sg->session); ?></small></td>
                                    <td>
                                        <strong class="text-<?php echo (float)$sg->sgpa >= 8.5 ? 'green' : ((float)$sg->sgpa >= 6 ? 'yellow' : 'red'); ?>">
                                            <?php echo number_format($sg->sgpa, 2); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo number_format($sg->cgpa, 2); ?></td>
                                    <td><?php echo $sg->total_credits_earned ?? '—'; ?>/<?php echo $sg->total_credits_registered ?? '—'; ?></td>
                                    <td class="text-center">
                                        <?php if ((int)$sg->arrear_count > 0): ?>
                                        <span class="badge bg-red"><?php echo $sg->arrear_count; ?></span>
                                        <?php else: ?>
                                        <span class="text-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="label label-<?php echo $sg->result_status === 'pass' ? 'success' : 'danger'; ?>">
                                            <?php echo strtoupper($sg->result_status); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrear Subjects List -->
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Failed Subjects History
                    <span class="badge bg-red"><?php echo count($arrears); ?></span>
                    <?php if (!empty($filters['active_only'])): ?>
                    <small class="text-warning" style="margin-left:6px"><i class="fa fa-filter"></i> Active arrears only</small>
                    <?php endif; ?>
                </h3>
            </div>
            <div class="box-body" style="padding:0">
                <?php if (empty($arrears)): ?>
                <div class="callout callout-success" style="margin:10px">
                    <p><i class="fa fa-check"></i> This student has no arrear subjects. All clear!</p>
                </div>
                <?php else: ?>
                <table class="table table-bordered table-hover" style="margin-bottom:0;font-size:13px">
                    <thead>
                        <tr class="bg-red" style="color:#fff">
                            <th>#</th>
                            <th>Exam</th>
                            <th>Session</th>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Internal</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 0; foreach ($arrears as $arr): $sn++; ?>
                        <tr>
                            <td><?php echo $sn; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($arr->batch_exam_name); ?></strong>
                                <?php if ($arr->date_from): ?>
                                <br><small class="text-muted"><?php echo date('d M Y', strtotime($arr->date_from)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($arr->session); ?></td>
                            <td><strong><?php echo htmlspecialchars($arr->subject_code); ?></strong></td>
                            <td><?php echo htmlspecialchars($arr->subject_name); ?></td>
                            <td><?php echo $arr->internal_marks ?? '—'; ?></td>
                            <td class="<?php echo (float)$arr->external_marks < 28 ? 'text-danger' : ''; ?>">
                                <?php echo $arr->external_marks ?? '—'; ?>
                                <?php if ((float)$arr->external_marks < 28): ?>
                                <i class="fa fa-exclamation text-danger" title="External below pass mark (28)"></i>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo (float)$arr->total_marks < 50 ? 'text-danger' : ''; ?>">
                                <?php echo $arr->total_marks ?? '—'; ?>
                            </td>
                            <td class="text-danger"><strong>U</strong></td>
                            <td>
                                <?php if (!empty($arr->later_pass)): ?>
                                <span class="label label-success" title="Student passed this subject in a later exam"><i class="fa fa-check"></i> Cleared</span>
                                <?php else: ?>
                                <span class="label label-danger">FAIL</span>
                                <?php endif; ?>
                                <?php if ($arr->moderation_applied > 0): ?>
                                <br><small class="text-warning">+<?php echo $arr->moderation_applied; ?> grace</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php if (!empty($arrears)): ?>
            <div class="box-footer">
                <div class="callout callout-warning" style="margin:0;padding:8px 12px">
                    <i class="fa fa-info-circle"></i>
                    <strong><?php echo count($arrears); ?></strong> arrear subject(s) across all semesters.
                    Subjects highlighted in red show marks below the pass threshold
                    (External ≥28/70, Total ≥50/100).
                </div>
            </div>
            <?php endif; ?>
        </div>

    </section>
</div>
<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'results']); ?>
