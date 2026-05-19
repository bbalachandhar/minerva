<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> Scholarship Types</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Scholarship Types</li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('online_admission', 'can_add')): ?>
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Scholarship Type</h3>
                    </div>
                    <form action="<?php echo site_url('admin/scholarshiptype'); ?>" method="post">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Scholarship Name <small class="req">*</small></label>
                                <input type="text" name="name" class="form-control" value="<?php echo set_value('name'); ?>" maxlength="300" placeholder="Enter scholarship name"/>
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Optional description"><?php echo set_value('description'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" value="<?php echo set_value('sort_order', 0); ?>" min="0"/>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-md-<?php echo $this->rbac->hasPrivilege('online_admission', 'can_add') ? '8' : '12'; ?>">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Scholarship Type List</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/scholarshipapplication/settings'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-cog"></i> Workflow Settings
                            </a>
                            <a href="<?php echo site_url('admin/scholarshipapplication'); ?>" class="btn btn-info btn-sm">
                                <i class="fa fa-list"></i> View Applications
                            </a>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-hover table-striped example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Scholarship Name</th>
                                    <th>Description</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="noExport text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scholarship_types as $i => $t): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($t['name']); ?></td>
                                    <td><?php echo htmlspecialchars($t['description'] ?? ''); ?></td>
                                    <td><?php echo (int)$t['sort_order']; ?></td>
                                    <td>
                                        <?php if ($t['is_active']): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-default">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if ($this->rbac->hasPrivilege('online_admission', 'can_edit')): ?>
                                        <a href="<?php echo site_url('admin/scholarshiptype/edit/' . $t['id']); ?>" class="btn btn-xs btn-warning">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($this->rbac->hasPrivilege('online_admission', 'can_delete')): ?>
                                        <a href="<?php echo site_url('admin/scholarshiptype/delete/' . $t['id']); ?>"
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Delete this scholarship type?')">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($scholarship_types)): ?>
                                <tr><td colspan="6" class="text-center text-muted">No scholarship types found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
