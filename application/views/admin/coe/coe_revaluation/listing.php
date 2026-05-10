<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-refresh"></i> Revaluation Requests
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_revaluation'); ?>"><i class="fa fa-arrow-left"></i> Back</a></li>
            <?php if ($this->rbac->hasPrivilege('coe_revaluation', 'can_add')): ?>
            <li>
                <a href="<?php echo site_url('coe/coe_revaluation/add/' . $batch_exam_id); ?>"
                   class="btn btn-xs btn-success">
                    <i class="fa fa-plus"></i> New Request
                </a>
            </li>
            <?php endif; ?>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <form method="get" class="form-inline">
                            <div class="form-group" style="margin-right:10px">
                                <label>Subject&nbsp;</label>
                                <select name="subject_id" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>"
                                        <?php echo $this->input->get('subject_id') == $sub->id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin-right:10px">
                                <label>Status&nbsp;</label>
                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach (['pending','assigned','completed','rejected'] as $st): ?>
                                    <option value="<?php echo $st; ?>"
                                        <?php echo $this->input->get('status')===$st ? 'selected':''; ?>>
                                        <?php echo ucfirst($st); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Payment&nbsp;</label>
                                <select name="payment_status" class="form-control input-sm" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <?php foreach (['pending','paid','waived'] as $ps): ?>
                                    <option value="<?php echo $ps; ?>"
                                        <?php echo $this->input->get('payment_status')===$ps ? 'selected':''; ?>>
                                        <?php echo ucfirst($ps); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
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
                        <h3 class="box-title">Requests (<?php echo count($requests); ?>)</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($requests)): ?>
                            <p class="text-muted text-center">No revaluation requests found.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-condensed">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Admission No</th>
                                    <th>Subject</th>
                                    <th>Original Marks</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $status_cls = [
                                    'pending'   => 'label-warning',
                                    'assigned'  => 'label-info',
                                    'completed' => 'label-success',
                                    'rejected'  => 'label-danger',
                                ];
                                $pay_cls = [
                                    'pending' => 'label-warning',
                                    'paid'    => 'label-success',
                                    'waived'  => 'label-default',
                                ];
                                foreach ($requests as $i => $req):
                                ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($req->student_name); ?></td>
                                    <td><?php echo htmlspecialchars($req->admission_no); ?></td>
                                    <td><?php echo htmlspecialchars($req->subject_code); ?></td>
                                    <td><?php echo number_format($req->original_marks, 1); ?></td>
                                    <td>
                                        <span class="label <?php echo $pay_cls[$req->payment_status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($req->payment_status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="label <?php echo $status_cls[$req->status] ?? 'label-default'; ?>">
                                            <?php echo ucfirst($req->status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($req->request_date)); ?></td>
                                    <td>
                                        <a href="<?php echo site_url('coe/coe_revaluation/view/' . $req->id); ?>"
                                           class="btn btn-xs btn-primary">
                                            <i class="fa fa-eye"></i>
                                        </a>
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

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'revaluation']); ?>
