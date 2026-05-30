<section class="content-header" style="padding:10px 15px;">
    <h1><i class="fa fa-graduation-cap"></i> Apply for Scholarship</h1>
</section>

<section class="content">
    <?php echo $this->session->flashdata('msg'); ?>

    <div class="row">
        <!-- Apply Form -->
        <div class="col-md-5">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">New Application</h3>
                </div>
                <form action="<?php echo site_url('public_admission/save_scholarship'); ?>" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Scholarship Type <small style="color:red">*</small></label>
                            <select name="scholarship_type_id" class="form-control">
                                <option value="">-- Select Scholarship Type --</option>
                                <?php foreach ($scholarship_types as $t): ?>
                                <option value="<?php echo $t['id']; ?>">
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Remarks / Justification</label>
                            <textarea name="applicant_remarks" class="form-control" rows="3"
                                      placeholder="Briefly describe why you qualify for this scholarship..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Supporting Document <small class="text-muted">(JPG, PNG or PDF, max 300 KB)</small></label>
                            <div class="input-group">
                                <input type="text" id="docFileName" class="form-control" placeholder="No file chosen" readonly style="background:#fff;">
                                <span class="input-group-btn">
                                    <span class="btn btn-default" style="position:relative; overflow:hidden;">
                                        <i class="fa fa-folder-open"></i> Browse&hellip;
                                        <input type="file" id="docFileInput" name="document" accept=".jpg,.jpeg,.png,.pdf"
                                               style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;font-size:100px;">
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <a href="<?php echo site_url('public_admission/applicant_dashboard'); ?>" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary pull-right">
                            <i class="fa fa-paper-plane"></i> Submit Application
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Applications -->
        <div class="col-md-7">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">My Applications</h3>
                </div>
                <div class="box-body table-responsive">
                    <?php if (!empty($my_applications)): ?>
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Scholarship Type</th>
                                <th>Applied On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
                            foreach ($my_applications as $i => $app):
                                $b = $badges[$app['status']] ?? 'default';
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($app['scholarship_name'] ?? ''); ?></td>
                                <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                                <td><span class="label label-<?php echo $b; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="callout callout-info">
                        <p><i class="fa fa-info-circle"></i> You have not applied for any scholarship yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="box-footer clearfix">
                    <a href="<?php echo site_url('public_admission/scholarship_status'); ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-search"></i> Detailed Status
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.getElementById('docFileInput').addEventListener('change', function () {
    var nameBox = document.getElementById('docFileName');
    nameBox.value = this.files.length > 0 ? this.files[0].name : '';
});
</script>
