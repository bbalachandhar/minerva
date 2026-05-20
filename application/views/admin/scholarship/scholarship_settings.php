<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-cog"></i> Scholarship Workflow Settings</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('admin/scholarshipapplication'); ?>">Scholarship Applications</a></li>
            <li class="active">Workflow Settings</li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Global Approver Setting</h3>
                    </div>
                    <form action="<?php echo site_url('admin/scholarshipapplication/settings'); ?>" method="post">
                        <div class="box-body">
                            <div class="callout callout-info">
                                <p><strong>Global Approver:</strong><br/>
                                Set the final approver who makes the grant decision for all scholarship types.<br/>
                                The verifier for each scholarship type is configured on the <a href="<?php echo site_url('admin/scholarshiptype'); ?>">Scholarship Types</a> page.</p>
                            </div>
                            <div class="form-group">
                                <label>Approver <small class="req">*</small></label>
                                <select name="approver_id" class="form-control select2">
                                    <option value="">-- Select Approver --</option>
                                    <?php foreach ($staff_list as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"
                                        <?php echo (isset($settings['approver_id']) && (int)$settings['approver_id'] === (int)$s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['name'] . ' ' . $s['surname']); ?>
                                        <?php if (!empty($s['designation'])): ?>
                                            (<?php echo htmlspecialchars($s['designation']); ?>)
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('approver_id'); ?></span>
                            </div>
                        </div>
                        <div class="box-footer">
                            <a href="<?php echo site_url('admin/scholarshipapplication'); ?>" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
