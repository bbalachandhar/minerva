<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-warning"></i> UFM / Malpractice<button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <form method="get" action="<?php echo site_url('coe/coe_ufm'); ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?></label>
                                        <select name="session_id" class="form-control" onchange="this.form.submit()">
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s['id']; ?>" <?php echo ($s['id'] == $selected_session) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($s['session']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">CoE Exam Events</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Exam Group</th>
                                    <th>Batch Exam</th>
                                    <th>Dates</th>
                                    <th>Category</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($events)): ?>
                                    <tr><td colspan="6" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
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
                                            <a href="<?php echo site_url('coe/coe_ufm/listing/' . $ev->batch_exam_id); ?>" class="btn btn-xs btn-danger">
                                                <i class="fa fa-list"></i> View Incidents
                                            </a>
                                            <?php if ($this->rbac->hasPrivilege('coe_ufm', 'can_add')): ?>
                                            <a href="<?php echo site_url('coe/coe_ufm/report/' . $ev->batch_exam_id); ?>" class="btn btn-xs btn-warning">
                                                <i class="fa fa-plus"></i> Report
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

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'ufm']); ?>
