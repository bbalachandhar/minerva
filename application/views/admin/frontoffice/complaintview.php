<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?> &nbsp;<small><?php echo $this->lang->line('complaint_list'); ?></small></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/admin'); ?>"><i class="fa fa-home"></i> <?php echo $this->lang->line('home'); ?></a></li>
            <li class="active"><?php echo $this->lang->line('complaint_list'); ?></li>
        </ol>
    </section>
    <section class="content">

        <?php echo $this->session->flashdata('msg'); ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <!-- Status summary cards -->
        <?php
        $open_cnt  = (int)($status_counts['open_count'] ?? 0);
        $ip_cnt    = (int)($status_counts['in_progress_count'] ?? 0);
        $res_cnt   = (int)($status_counts['resolved_count'] ?? 0);
        $total_cnt = (int)($status_counts['total_count'] ?? 0);
        ?>
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-envelope-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $this->lang->line('complaint_status_open'); ?></span>
                        <span class="info-box-number"><?php echo $open_cnt; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $this->lang->line('complaint_status_in_progress'); ?></span>
                        <span class="info-box-number"><?php echo $ip_cnt; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $this->lang->line('complaint_status_resolved'); ?></span>
                        <span class="info-box-number"><?php echo $res_cnt; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $this->lang->line('total'); ?></span>
                        <span class="info-box-number"><?php echo $total_cnt; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('add_complain'); ?></h3>
                    </div>
                    <form id="form1" action="<?php echo site_url('admin/complaint'); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('complaint_type'); ?></label>
                                <select name="complaint" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($complaint_type as $v): ?>
                                        <option value="<?php echo htmlspecialchars($v['complaint_type']); ?>" <?php if (set_value('complaint') == $v['complaint_type']) echo 'selected'; ?>><?php echo htmlspecialchars($v['complaint_type']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('complain_by'); ?> <small class="req">*</small></label>
                                <input type="text" class="form-control" name="name" value="<?php echo set_value('name', htmlspecialchars($logged_in_name)); ?>">
                                <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($logged_in_empid); ?>">
                                <?php if (!empty($logged_in_empid)): ?>
                                <small class="text-muted"><i class="fa fa-id-card-o"></i> <?php echo $this->lang->line('employee_id'); ?>: <?php echo htmlspecialchars($logged_in_empid); ?> &nbsp;<span class="label label-default">Staff</span></small>
                                <?php endif; ?>
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('phone'); ?></label>
                                <input type="text" class="form-control" name="contact" value="<?php echo set_value('contact', htmlspecialchars($logged_in_contact)); ?>" maxlength="10" pattern="[0-9]{10}" placeholder="10-digit mobile number">
                                <small class="text-muted"><i class="fa fa-info-circle"></i> Pre-filled from your profile &mdash; edit if you want a different contact number.</small>
                                <span class="text-danger"><?php echo form_error('contact'); ?></span>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('priority'); ?></label>
                                <select name="priority" class="form-control">
                                    <option value="low"><?php echo $this->lang->line('complaint_priority_low'); ?></option>
                                    <option value="medium" selected><?php echo $this->lang->line('complaint_priority_medium'); ?></option>
                                    <option value="high"><?php echo $this->lang->line('complaint_priority_high'); ?></option>
                                    <option value="critical"><?php echo $this->lang->line('complaint_priority_critical'); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('date'); ?></label>
                                <input type="text" class="form-control date" name="date" id="date" value="<?php echo set_value('date', date($this->customlib->getSchoolDateFormat())); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('description'); ?></label>
                                <textarea class="form-control" name="description" rows="3"><?php echo set_value('description'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('note'); ?></label>
                                <textarea class="form-control" name="note" rows="2"><?php echo set_value('note'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('attach_document'); ?></label>
                                <input class="filestyle form-control" type="file" name="file">
                                <span class="text-danger"><?php echo form_error('file'); ?></span>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" id="submitbtn" class="btn btn-primary btn-block"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-9">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('complaint_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <!-- Filter bar -->
                            <form method="get" action="<?php echo site_url('admin/complaint'); ?>" class="form-inline">
                                <select name="session_id" class="form-control input-sm" style="width:100px;">
                                    <?php foreach ($sessions as $sess): ?>
                                        <option value="<?php echo $sess['id']; ?>" <?php if ((int)($filters['session_id'] ?? $current_session) == (int)$sess['id']) echo 'selected'; ?>><?php echo htmlspecialchars($sess['session']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="status" class="form-control input-sm" style="width:110px;">
                                    <option value=""><?php echo $this->lang->line('all_statuses'); ?></option>
                                    <?php foreach (['open','in_progress','resolved','closed'] as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php if (($filters['status'] ?? '') === $s) echo 'selected'; ?>><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="priority" class="form-control input-sm" style="width:100px;">
                                    <option value=""><?php echo $this->lang->line('all_priorities'); ?></option>
                                    <?php foreach (['low','medium','high','critical'] as $p): ?>
                                        <option value="<?php echo $p; ?>" <?php if (($filters['priority'] ?? '') === $p) echo 'selected'; ?>><?php echo ucfirst($p); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="submitted_by" class="form-control input-sm" style="width:110px;">
                                    <option value=""><?php echo $this->lang->line('all_sources'); ?></option>
                                    <option value="student" <?php if (($filters['submitted_by'] ?? '') === 'student') echo 'selected'; ?>>Student</option>
                                    <option value="parent" <?php if (($filters['submitted_by'] ?? '') === 'parent') echo 'selected'; ?>>Parent</option>
                                </select>
                                <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-filter"></i></button>
                                <a href="<?php echo site_url('admin/complaint'); ?>" class="btn btn-default btn-sm"><i class="fa fa-times"></i></a>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="download_label"><?php echo $this->lang->line('complaint_list'); ?></div>
                        <table class="table table-hover table-striped table-bordered example">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('ticket_no'); ?></th>
                                    <th><?php echo $this->lang->line('complaint_type'); ?></th>
                                    <th><?php echo $this->lang->line('name'); ?></th>
                                    <th><?php echo $this->lang->line('source'); ?></th>
                                    <th><?php echo $this->lang->line('priority'); ?></th>
                                    <th><?php echo $this->lang->line('status'); ?></th>
                                    <th><?php echo $this->lang->line('assigned'); ?></th>
                                    <th><?php echo $this->lang->line('date'); ?></th>
                                    <th class="noExport"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $prLabels = ['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'danger'];
                                $stLabels = ['open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'default'];
                                foreach ($complaint_list as $v): ?>
                                <tr>
                                    <td><span class="label label-default"><?php echo htmlspecialchars($v['ticket_no'] ?: '#'.$v['id']); ?></span></td>
                                    <td><?php echo htmlspecialchars($v['complaint_type']); ?></td>
                                    <td><?php echo htmlspecialchars($v['name']); ?><?php if (!empty($v['submitted_by'])): ?> <span class="label label-info"><?php echo ucfirst($v['submitted_by']); ?></span><?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($v['source']); ?></td>
                                    <td><span class="label label-<?php echo $prLabels[$v['priority']] ?? 'default'; ?>"><?php echo ucfirst($v['priority']); ?></span></td>
                                    <td><span class="label label-<?php echo $stLabels[$v['status']] ?? 'default'; ?>"><?php echo ucwords(str_replace('_',' ',$v['status'])); ?></span></td>
                                    <td><?php if (!empty($v['assigned'])): ?><span class="label label-primary"><?php echo htmlspecialchars($v['assigned']); ?></span><?php endif; ?></td>
                                    <td class="white-space-nowrap"><?php if ($v['date'] != '0000-00-00') echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($v['date'])); ?></td>
                                    <td class="white-space-nowrap">
                                        <a onclick="getRecord(<?php echo $v['id']; ?>)" class="btn btn-default btn-xs" data-target="#complaintdetails" data-toggle="modal" title="<?php echo $this->lang->line('view'); ?>"><i class="fa fa-eye"></i></a>
                                        <?php if (!empty($v['image'])): ?>
                                        <a href="<?php echo base_url(); ?>admin/complaint/download/<?php echo $v['id']; ?>" class="btn btn-default btn-xs" title="<?php echo $this->lang->line('download'); ?>"><i class="fa fa-download"></i></a>
                                        <?php endif; ?>
                                        <?php if ($this->rbac->hasPrivilege('complaint', 'can_edit')): ?>
                                        <a href="<?php echo base_url(); ?>admin/complaint/edit/<?php echo $v['id']; ?>" class="btn btn-default btn-xs" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if ($this->rbac->hasPrivilege('complaint', 'can_delete')): ?>
                                        <a href="<?php echo base_url(); ?>admin/complaint/delete/<?php echo $v['id']; ?>" class="btn btn-default btn-xs" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');" title="<?php echo $this->lang->line('delete'); ?>"><i class="fa fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Complaint Detail Modal -->
<div id="complaintdetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('complaint_details'); ?></h4>
            </div>
            <div class="modal-body" id="getdetails"></div>
        </div>
    </div>
</div>

<script>
function getRecord(id) {
    $('#getdetails').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $.ajax({
        url: '<?php echo base_url(); ?>admin/complaint/details/' + id,
        success: function (result) { $('#getdetails').html(result); }
    });
}
$(function () {
    $('#form1').submit(function () { $('#submitbtn').button('loading'); });
});
</script>
