<!-- Arrear Register Index -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-exclamation-triangle"></i> Arrear Register
            <small>Students with failed / carry-over subjects</small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard?session_id='.$session_id); ?>"><i class="fa fa-arrow-left"></i> Dashboard</a></li>
            <li class="active">Arrear Register</li>
        </ol>
    </section>

    <section class="content">
        <div id="arrear-flash"></div>

        <!-- Filters -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter"></i> Filters</h3>
                <div class="box-tools">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">
                <form method="get" class="form-inline">
                    <div class="form-group" style="margin-right:10px">
                        <label>Session &nbsp;</label>
                        <select name="session_id" class="form-control input-sm" onchange="this.form.submit()">
                            <?php foreach ($sessions as $sess): ?>
                            <option value="<?php echo $sess->id; ?>" <?php echo $sess->id == $session_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sess->session); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:10px">
                        <label>Exam Event &nbsp;</label>
                        <select name="batch_exam_id" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">All Events</option>
                            <?php foreach ($events as $evt): ?>
                            <option value="<?php echo $evt->batch_exam_id; ?>"
                                <?php echo $filters['batch_exam_id'] == $evt->batch_exam_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($evt->name.' — '.$evt->exam); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:10px">
                        <label>Department &nbsp;</label>
                        <select name="department_id" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept->id; ?>"
                                <?php echo $filters['department_id'] == $dept->id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept->department_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right:10px">
                        <label>Search &nbsp;</label>
                        <input type="text" name="search" class="form-control input-sm"
                               placeholder="Name / Reg. No." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-search"></i> Apply
                    </button>
                    <a href="<?php echo site_url('coe/coe_arrear?session_id='.$session_id); ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-times"></i> Clear
                    </a>
                </form>
            </div>
        </div>

        <!-- Results Table -->
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list"></i> Arrear Students
                    <span class="badge bg-red" style="margin-left:5px"><?php echo count($arrears); ?></span>
                </h3>
                <div class="box-tools">
                    <button class="btn btn-xs btn-default" onclick="window.print()">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="box-body" style="padding:0;overflow-x:auto">
                <?php if (empty($arrears)): ?>
                <div class="callout callout-success" style="margin:15px">
                    <h4><i class="fa fa-check"></i> No Arrears Found</h4>
                    <p>
                        <?php if (!empty($filters['batch_exam_id']) || !empty($filters['search']) || !empty($filters['department_id'])): ?>
                        No arrear students match the current filter. Try clearing filters.
                        <?php else: ?>
                        No students have arrear subjects in the selected session. 
                        <?php endif; ?>
                    </p>
                </div>
                <?php else: ?>
                <table class="table table-bordered table-hover" style="margin-bottom:0;font-size:13px">
                    <thead>
                        <tr class="bg-red" style="color:#fff">
                            <th>#</th>
                            <th>Register No.</th>
                            <th>Student Name</th>
                            <th>Class / Department</th>
                            <th>Arrear Count</th>
                            <th>Failed Subjects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 0; foreach ($arrears as $a): $sn++; ?>
                        <tr>
                            <td><?php echo $sn; ?></td>
                            <td><strong><?php echo htmlspecialchars($a->register_no ?: $a->admission_no ?: '—'); ?></strong></td>
                            <td><?php echo htmlspecialchars($a->student_name); ?></td>
                            <td>
                                <?php echo htmlspecialchars($a->class_name ?? '—'); ?>
                                <?php if ($a->department_name): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($a->department_name); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-red" style="font-size:13px"><?php echo $a->arrear_count; ?></span>
                            </td>
                            <td>
                                <small style="word-break:break-word;max-width:300px;display:inline-block">
                                    <?php echo htmlspecialchars($a->arrear_subjects ?? '—'); ?>
                                </small>
                            </td>
                            <td>
                                <a href="<?php echo site_url('coe/coe_arrear/student/'.$a->student_id); ?>"
                                   class="btn btn-xs btn-warning">
                                    <i class="fa fa-user"></i> Arrear Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php if (!empty($arrears)): ?>
            <div class="box-footer">
                <small class="text-muted">
                    Total <?php echo count($arrears); ?> student(s) with arrears in this session.
                    Note: A student with arrears in multiple subjects is counted once per row.
                </small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Information Box -->
        <div class="callout callout-info">
            <h4><i class="fa fa-info-circle"></i> About Arrear Register</h4>
            <ul style="margin-bottom:0">
                <li>This register shows all students who have at least one <strong>failed subject</strong> in any end-semester exam for the selected session.</li>
                <li>Students must clear arrear subjects in subsequent exam events (odd/even semester) until all subjects are passed.</li>
                <li>Anna University allows candidates to carry arrears forward to the next attempt.</li>
                <li>Use the <strong>Arrear Detail</strong> button to view a student's complete arrear history across all semesters.</li>
            </ul>
        </div>

    </section>
</div>
<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'results']); ?>
