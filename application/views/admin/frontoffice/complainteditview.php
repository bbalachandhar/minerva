<div class="content-wrapper">  
    <section class="content-header">
        <h1>
            <i class="fa fa-sitemap"></i> <?php echo $this->lang->line('human_resource'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('complaint', 'can_add') || $this->rbac->hasPrivilege('complaint', 'can_edit')) { ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('edit_complain'); ?></h3>
                        </div><!-- /.box-header -->
                        <form id="form1" action="<?php echo site_url('admin/complaint/edit/' . $complaint_data['id']) ?>"   method="post" accept-charset="utf-8" enctype="multipart/form-data" >
                            <div class="box-body">                              
                                
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('complaint_type'); ?></label>

                                    <select name="complaint" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>  
                                        <?php
                                        foreach ($complaint_type as $key => $value) {
                                            ?>
                                            <option value="<?php echo $value['complaint_type']; ?>" <?php if (set_value('complaint', $complaint_data['complaint_type']) == $value['complaint_type']) echo "selected"; ?>><?php echo $value['complaint_type']; ?></option>
                                        <?php } ?>                                       
                                    </select>
                                    <span class="text-danger"><?php echo form_error('complaint'); ?></span>
                                </div>
                                <input type="hidden" name="source" value="<?php echo htmlspecialchars($complaint_data['source']); ?>">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('priority'); ?></label>
                                    <select name="priority" class="form-control">
                                        <option value="low"<?php if (set_value('priority', $complaint_data['priority']) == 'low') echo ' selected'; ?>><?php echo $this->lang->line('complaint_priority_low'); ?></option>
                                        <option value="medium"<?php if (set_value('priority', $complaint_data['priority']) == 'medium') echo ' selected'; ?>><?php echo $this->lang->line('complaint_priority_medium'); ?></option>
                                        <option value="high"<?php if (set_value('priority', $complaint_data['priority']) == 'high') echo ' selected'; ?>><?php echo $this->lang->line('complaint_priority_high'); ?></option>
                                        <option value="critical"<?php if (set_value('priority', $complaint_data['priority']) == 'critical') echo ' selected'; ?>><?php echo $this->lang->line('complaint_priority_critical'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('status'); ?></label>
                                    <select name="status" class="form-control">
                                        <option value="open"<?php if (set_value('status', $complaint_data['status']) == 'open') echo ' selected'; ?>><?php echo $this->lang->line('complaint_status_open'); ?></option>
                                        <option value="in_progress"<?php if (set_value('status', $complaint_data['status']) == 'in_progress') echo ' selected'; ?>><?php echo $this->lang->line('complaint_status_in_progress'); ?></option>
                                        <option value="resolved"<?php if (set_value('status', $complaint_data['status']) == 'resolved') echo ' selected'; ?>><?php echo $this->lang->line('complaint_status_resolved'); ?></option>
                                        <option value="closed"<?php if (set_value('status', $complaint_data['status']) == 'closed') echo ' selected'; ?>><?php echo $this->lang->line('complaint_status_closed'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="pwd"><?php echo $this->lang->line('complain_by'); ?></label> <small class="req"> *</small> 
                                    <input type="text" class="form-control" value="<?php echo set_value('name', $complaint_data['name']); ?>"  name="name">
                                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="email"><?php echo $this->lang->line('phone'); ?></label> 
                                    <input type="text" class="form-control" value="<?php echo set_value('contact', $complaint_data['contact']); ?>" name="contact" maxlength="10" pattern="[0-9]{10}" placeholder="10-digit mobile number">
                                    <span class="text-danger"><?php echo form_error('contact'); ?></span>
                                </div>
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="pwd"><?php echo $this->lang->line('date'); ?></label>
                                        <input type="text" class="form-control date" value="<?php if($complaint_data['date'] != '0000-00-00'){ echo set_value('date', date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($complaint_data['date']))); } ?>"  name="date" id="date" readonly>
                                        <span class="text-danger"><?php echo form_error('date'); ?></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pwd"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description"rows="3"><?php echo set_value('description', $complaint_data['description']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="pwd"><?php echo $this->lang->line('action_taken'); ?></label>
                                    <input type="text" class="form-control" value="<?php echo set_value('action_taken', $complaint_data['action_taken']); ?>"  name="action_taken">
                                    <span class="text-danger"><?php echo form_error('action_taken'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('assigned'); ?></label>
                                    <select class="form-control" name="assigned">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($staff_list as $s): ?>
                                        <option value="<?php echo htmlspecialchars($s['name']); ?>" <?php if (set_value('assigned', $complaint_data['assigned']) === $s['name']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($s['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('assigned'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="pwd"><?php echo $this->lang->line('note'); ?></label>
                                    <textarea class="form-control" id="description" name="note" rows="3"><?php echo set_value('note', $complaint_data['note']); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('note'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('admin_response'); ?></label> <small class="text-muted">(<?php echo $this->lang->line('visible_to_reporter'); ?>)</small>
                                    <textarea class="form-control" name="admin_response" rows="3"><?php echo set_value('admin_response', $complaint_data['admin_response']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputFile"><?php echo $this->lang->line('attach_document'); ?></label>
                                    <?php if (!empty($complaint_data['image'])): ?>
                                    <div class="mb-1">
                                        <a href="<?php echo base_url('admin/complaint/download/' . $complaint_data['id']); ?>" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-paperclip"></i> <?php echo htmlspecialchars($complaint_data['image']); ?></a>
                                        <small class="text-muted"><?php echo $this->lang->line('current_file'); ?></small>
                                    </div>
                                    <?php endif; ?>
                                    <div><input class="filestyle form-control" type='file' name='file' />
                                    </div>
                                    <small class="text-muted"><?php echo $this->lang->line('upload_new_to_replace'); ?></small>
                                    <span class="text-danger"><?php echo form_error('file'); ?></span>
                                </div>
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>

                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('complaint', 'can_add') || $this->rbac->hasPrivilege('complaint', 'can_edit')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('complaint_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <form method="get" action="<?php echo site_url('admin/complaint/edit/'.$complaint_data['id']); ?>" class="form-inline">
                                <select name="session_id" class="form-control input-sm" style="width:100px;" onchange="this.form.submit()">
                                    <?php foreach ($sessions as $sess): ?>
                                        <option value="<?php echo $sess['id']; ?>" <?php if ((int)($filters['session_id'] ?? $current_session) == (int)$sess['id']) echo 'selected'; ?>><?php echo htmlspecialchars($sess['session']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="download_label"></div>
                        <div class="mailbox-messages">
                            <div class="table-responsive overflow-visible">
                              <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('ticket_no'); ?></th>
                                        <th><?php echo $this->lang->line('complaint_type'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?> </th>
										<th><?php echo $this->lang->line('phone'); ?> </th>
										<th><?php echo $this->lang->line('priority'); ?></th>
										<th><?php echo $this->lang->line('status'); ?></th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
										<th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($complaint_list)) {
                                        ?>

                                        <?php
                                    } else {
                                        foreach ($complaint_list as $key => $value) {
                                            $priority_class = ['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'danger'];
                                            $status_class   = ['open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'default'];
                                            $p_label = $priority_class[$value['priority']] ?? 'default';
                                            $s_label = $status_class[$value['status']] ?? 'default';
                                            ?>
                                            <tr>
                                                <td><span class="label label-default"><?php echo $value['ticket_no'] ?: '#'.$value['id']; ?></span></td>
                                                <td class="mailbox-name"><?php echo $value['complaint_type']; ?></td>
                                                <td class="mailbox-name"><?php echo $value['name']; ?><?php if(!empty($value['email'])){ ?> ( <?php echo $value['email']; ?> ) <?php } ?></td>
                                                <td class="mailbox-name"><?php echo $value['contact']; ?></td>
                                                <td><span class="label label-<?php echo $p_label; ?>"><?php echo ucfirst($value['priority'] ?? 'medium'); ?></span></td>
                                                <td><span class="label label-<?php echo $s_label; ?>"><?php echo ucwords(str_replace('_',' ',$value['status'] ?? 'open')); ?></span></td>
                                                <td class="white-space-nowrap"><?php if($value['date'] != '0000-00-00'){ echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value['date'])); } ?></td>
                                                <td class="pull-right white-space-nowrap">
                                                
                                                    <a onclick="getRecord(<?php echo $value['id']; ?>)" class="btn btn-default btn-xs" data-target="#complaintdetails" title="<?php echo $this->lang->line('view') ?>" data-toggle="modal"  data-original-title="<?php echo $this->lang->line('view') ?>"><i class="fa fa-reorder"></i></a>        
                                                    
                                                    <?php if ($value['image'] != "") { ?><a href="<?php echo base_url(); ?>admin/complaint/download/<?php echo $value['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('download'); ?>"><i class="fa fa-download"></i></a><?php } ?> 
                                                        
                                                    <?php if ($this->rbac->hasPrivilege('complaint', 'can_edit')) { ?>    
                                                        <a href="<?php echo base_url(); ?>admin/complaint/edit/<?php echo $value['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->rbac->hasPrivilege('complaint', 'can_delete')) { ?> 
                                                       
                                                            <a href="<?php echo base_url(); ?>admin/complaint/delete/<?php echo $value['id']; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');" data-original-title="<?php echo $this->lang->line('delete'); ?>">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                            <?php
                                                        
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table><!-- /.table -->
                          </div>  
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- new END -->
<div id="complaintdetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('details'); ?></h4>
            </div>
            <div class="modal-body" id="getdetails">

            </div>
        </div>
    </div>
</div>
</div><!-- /.content-wrapper -->
<script type="text/javascript">
    function getRecord(id) {       
        $.ajax({
            url: '<?php echo base_url(); ?>admin/complaint/details/' + id,
            success: function (result) {
                $('#getdetails').html(result);
            }
        });
    }
</script>