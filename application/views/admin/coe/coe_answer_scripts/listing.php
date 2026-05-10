<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-file-text-o"></i> Answer Scripts
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_answer_scripts'); ?>"><i class="fa fa-arrow-left"></i> Back</a></li>
            <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_add')): ?>
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/upload/' . $batch_exam_id); ?>"
                   class="btn btn-xs btn-primary">
                    <i class="fa fa-plus"></i> Register Script
                </a>
            </li>
            <?php endif; ?>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending</span>
                        <span class="info-box-number"><?php echo $counts['pending']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-blue">
                    <span class="info-box-icon"><i class="fa fa-barcode"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Scanned</span>
                        <span class="info-box-number"><?php echo $counts['scanned']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Uploaded</span>
                        <span class="info-box-number"><?php echo $counts['uploaded']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Filters</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <form method="get">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <select name="subject_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Subjects</option>
                                            <?php foreach ($subjects as $sub): ?>
                                            <option value="<?php echo $sub->id; ?>"
                                                <?php echo $this->input->get('subject_id') == $sub->id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="scan_status" class="form-control" onchange="this.form.submit()">
                                            <option value="">All Statuses</option>
                                            <option value="pending"  <?php echo $this->input->get('scan_status')=='pending'  ? 'selected' : ''; ?>>Pending</option>
                                            <option value="scanned"  <?php echo $this->input->get('scan_status')=='scanned'  ? 'selected' : ''; ?>>Scanned</option>
                                            <option value="uploaded" <?php echo $this->input->get('scan_status')=='uploaded' ? 'selected' : ''; ?>>Uploaded</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-table"></i> Scripts (<?php echo count($scripts); ?>)</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($scripts)): ?>
                            <p class="text-muted text-center">No answer scripts found.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Hall Ticket</th>
                                    <th>Student</th>
                                    <th>Subject</th>
                                    <th>Exam Date</th>
                                    <th>Barcode</th>
                                    <th>Status</th>
                                    <th>Uploaded By</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $status_class = ['pending' => 'label-warning', 'scanned' => 'label-info', 'uploaded' => 'label-success'];
                                foreach ($scripts as $i => $s):
                                ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($s->hall_ticket_no); ?></strong></td>
                                    <td><?php echo htmlspecialchars($s->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($s->subject_code . ' ' . $s->subject_name); ?></td>
                                    <td><?php echo $s->exam_date ? date('d M Y', strtotime($s->exam_date)) . ' / ' . $s->session_slot : '—'; ?></td>
                                    <td><code><?php echo htmlspecialchars($s->barcode_token ?? '—'); ?></code></td>
                                    <td>
                                        <span class="label <?php echo $status_class[$s->scan_status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($s->scan_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($s->uploaded_by_name ?? '—'); ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_answer_scripts/view/' . $s->id); ?>"
                                           class="btn btn-xs btn-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <?php if ($this->rbac->hasPrivilege('coe_answer_scripts', 'can_delete')): ?>
                                        <a href="<?php echo site_url('coe/coe_answer_scripts/delete/' . $s->id); ?>"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Delete this script record?')">
                                            <i class="fa fa-trash"></i>
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
        </div>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'answer_scripts']); ?>
