<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('coe_exam_events'); ?></h1>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">

            <!-- Session filter + Mark exam as CoE -->
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="row">
                            <!-- Session selector -->
                            <div class="col-md-3">
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

                            <?php if ($this->rbac->hasPrivilege('coe_application', 'can_add')): ?>
                            <!-- Mark exam_group as CoE -->
                            <div class="col-md-9">
                                <form method="post" action="<?php echo site_url('coe/coe_application/mark_end_semester'); ?>">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Mark Exam Group as CoE End-Semester</label>
                                                <select name="exam_group_id" class="form-control" required>
                                                    <option value="">— Select Exam Group —</option>
                                                    <?php foreach ($all_exam_groups as $eg): ?>
                                                        <option value="<?php echo $eg->id; ?>" <?php echo $eg->is_end_semester ? 'style="color:#3c8dbc"' : ''; ?>>
                                                            <?php echo htmlspecialchars($eg->name); ?><?php echo $eg->is_end_semester ? ' ✓ CoE' : ''; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Category</label>
                                                <select name="exam_category" class="form-control" required>
                                                    <option value="main"><?php echo $this->lang->line('coe_exam_category_main'); ?></option>
                                                    <option value="arrear"><?php echo $this->lang->line('coe_exam_category_arrear'); ?></option>
                                                    <option value="supplementary"><?php echo $this->lang->line('coe_exam_category_supplementary'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Mode</label>
                                                <select name="exam_type" class="form-control" required>
                                                    <option value="theory">Theory</option>
                                                    <option value="practical">Practical</option>
                                                    <option value="project">Project</option>
                                                    <option value="viva">Viva</option>
                                                    <option value="online">Online</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2" style="margin-top:25px;">
                                            <button type="submit" class="btn btn-primary btn-sm">Mark as CoE Exam</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <?php endif; ?>
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
                                        <td><?php echo htmlspecialchars($ev->name); ?></td>
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
