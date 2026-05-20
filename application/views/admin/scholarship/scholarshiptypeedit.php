<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> Edit Scholarship Type</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('admin/scholarshiptype'); ?>">Scholarship Types</a></li>
            <li class="active">Edit</li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Scholarship Type</h3>
                    </div>
                    <form action="<?php echo site_url('admin/scholarshiptype/edit/' . $scholarship_type['id']); ?>" method="post">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Scholarship Name <small class="req">*</small></label>
                                <input type="text" name="name" class="form-control"
                                       value="<?php echo set_value('name', $scholarship_type['name']); ?>"
                                       maxlength="300"/>
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo set_value('description', $scholarship_type['description']); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Amount (&#8377;)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="0"
                                       value="<?php echo set_value('amount', $scholarship_type['amount'] ?? ''); ?>"
                                       placeholder="Leave blank if not fixed"/>
                            </div>
                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                       value="<?php echo set_value('sort_order', $scholarship_type['sort_order']); ?>"/>
                            </div>
                            <div class="form-group">
                                <label>Verifier <small class="text-muted">(who verifies applications of this type)</small></label>
                                <select name="verifier_id" class="form-control select2">
                                    <option value="">-- None assigned --</option>
                                    <?php foreach ($staff_list as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"
                                        <?php echo (isset($scholarship_type['verifier_id']) && $scholarship_type['verifier_id'] !== null && (int)$scholarship_type['verifier_id'] === (int)$s['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['name'] . ' ' . $s['surname']); ?>
                                        <?php if (!empty($s['designation'])): ?>(<?php echo htmlspecialchars($s['designation']); ?>)<?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" <?php echo (int)$scholarship_type['is_active'] === 1 ? 'selected' : ''; ?>>Active</option>
                                    <option value="0" <?php echo (int)$scholarship_type['is_active'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="box-footer">
                            <a href="<?php echo site_url('admin/scholarshiptype'); ?>" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
