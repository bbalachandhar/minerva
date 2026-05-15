<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('coe_exam_events'); ?><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">

            <!-- Session filter -->
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <form method="get" action="<?php echo site_url('coe/coe_application'); ?>">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?></label>
                                        <select name="session_id" class="form-control" onchange="this.form.submit()">
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s["id"]; ?>" <?php echo ($s["id"] == $selected_session) ? 'selected' : ''; ?>><?php echo $s["session"]; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-8" style="padding-top:24px;">
                                <div class="callout callout-info" style="padding:8px 14px;font-size:12px;">
                                    <i class="fa fa-info-circle"></i>
                                    Exam events are now managed in <a href="<?php echo site_url('coe/coe_event'); ?>"><strong>Exam Events</strong></a>.
                                    Use that page to create exam events and add class batches. Applications (View / Generate) are available here.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events table -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">CoE Exam Events</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('exam_group'); ?></th>
                                    <th>Batch Exam</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Mode</th>
                                    <th>Applications</th>
                                    <th>Status</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($events)): ?>
                                    <tr><td colspan="8" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($events as $i => $ev): ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo htmlspecialchars($ev->exam_group_name); ?></td>
                                        <td><?php echo htmlspecialchars($ev->exam); ?></td>
                                        <td><?php echo date('d M Y', strtotime($ev->date_from)); ?> – <?php echo date('d M Y', strtotime($ev->date_to)); ?></td>
                                        <td>
                                            <?php $cat_map = ['main'=>'label-primary','arrear'=>'label-warning','supplementary'=>'label-info']; ?>
                                            <span class="label <?php echo $cat_map[$ev->exam_category] ?? 'label-default'; ?>">
                                                <?php echo ucfirst($ev->exam_category); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php $type_map = ['theory'=>'label-default','practical'=>'label-success','project'=>'label-info','viva'=>'label-warning','online'=>'label-danger']; ?>
                                            <span class="label <?php echo $type_map[$ev->exam_type] ?? 'label-default'; ?>">
                                                <?php echo ucfirst($ev->exam_type ?? 'theory'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $ev->application_count; ?></td>
                                        <td>
                                            <?php if ($ev->coe_locked): ?>
                                                <span class="label label-danger"><?php echo $this->lang->line('coe_exam_locked'); ?></span>
                                            <?php else: ?>
                                                <span class="label label-success">Open</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo site_url('coe/coe_application/view/' . $ev->batch_exam_id); ?>" class="btn btn-xs btn-info" title="View Applications">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <?php if (!$ev->coe_locked && $this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                                            <a href="<?php echo site_url('coe/coe_application/generate/' . $ev->batch_exam_id); ?>" class="btn btn-xs btn-success" title="Generate Applications" onclick="return confirm('Generate applications for all students in this batch exam?')">
                                                <i class="fa fa-cogs"></i> Generate
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'exam_events']); ?>
