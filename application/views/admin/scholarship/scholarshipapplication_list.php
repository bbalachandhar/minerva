<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-graduation-cap"></i> Scholarship Applications</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Scholarship Applications</li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Filter bar -->
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php
                        $statuses = [''=>'All', 'pending'=>'Pending', 'verified'=>'Verified', 'approved'=>'Approved', 'rejected'=>'Rejected'];
                        foreach ($statuses as $key => $label):
                            $active = ($filter_status === $key || ($key === '' && !$filter_status)) ? 'btn-primary' : 'btn-default';
                        ?>
                        <a href="<?php echo site_url('admin/scholarshipapplication' . ($key ? '?status=' . $key : '')); ?>"
                           class="btn btn-sm <?php echo $active; ?>"><?php echo $label; ?>
                           <?php if ($key !== ''): ?>
                           <span class="badge"><?php echo count(array_filter($applications, fn($a) => $a['status'] === $key)); ?></span>
                           <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="<?php echo site_url('admin/scholarshiptype'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-list"></i> Manage Types
                        </a>
                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#settingsModal">
                            <i class="fa fa-cog"></i> Workflow Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workflow info -->
        <?php
        // Build a quick id->name lookup from staff_list
        $staff_map = [];
        if (!empty($staff_list)) {
            foreach ($staff_list as $_s) {
                $staff_map[(int)$_s['id']] = htmlspecialchars($_s['name'] . ' ' . $_s['surname']);
            }
        }
        ?>
        <?php if ($settings): ?>
        <div class="callout callout-info" style="margin-bottom:15px">
            <strong>Workflow:</strong>
            Verifier: <strong><?php echo !empty($settings['verifier_id']) ? ($staff_map[(int)$settings['verifier_id']] ?? 'Staff #'.$settings['verifier_id']) : '<span class="text-danger">Not set</span>'; ?></strong>
            &nbsp;|&nbsp;
            Approver: <strong><?php echo !empty($settings['approver_id']) ? ($staff_map[(int)$settings['approver_id']] ?? 'Staff #'.$settings['approver_id']) : '<span class="text-danger">Not set</span>'; ?></strong>
            &nbsp;&mdash; <a href="#" data-toggle="modal" data-target="#settingsModal">Change</a>
        </div>
        <?php endif; ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?php echo $filter_status ? ucfirst($filter_status) . ' Applications' : 'All Applications'; ?>
                    <span class="badge"><?php echo count($applications); ?></span>
                </h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-hover table-striped example">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference No</th>
                            <th>Applicant</th>
                            <th>Scholarship Type</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="noExport text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $i => $app): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($app['reference_no'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(($app['firstname'] ?? '') . ' ' . ($app['lastname'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($app['scholarship_name'] ?? ''); ?></td>
                            <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                            <td><?php
                                $badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
                                $b = $badges[$app['status']] ?? 'default';
                            ?><span class="label label-<?php echo $b; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                            <td class="text-right">
                                <a href="<?php echo site_url('admin/scholarshipapplication/view/' . $app['id']); ?>"
                                   class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applications)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- ── Workflow Settings Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="settingsModalLabel"><i class="fa fa-cog"></i> Scholarship Workflow Settings</h4>
            </div>
            <div class="modal-body">
                <div class="callout callout-info">
                    <p><strong>Two-step workflow:</strong><br/>
                    The <em>Verifier</em> checks document authenticity and marks the application as verified.<br/>
                    The <em>Approver</em> makes the final grant decision. They must be different people.</p>
                </div>
                <div id="settingsMsg"></div>
                <form id="settingsForm">
                    <div class="form-group">
                        <label>Verifier <small class="req">*</small></label>
                        <select name="verifier_id" id="settingsVerifier" class="form-control">
                            <option value="">-- Select Verifier --</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo (isset($settings['verifier_id']) && (int)$settings['verifier_id'] === (int)$s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name'] . ' ' . $s['surname']); ?>
                                <?php if (!empty($s['designation'])): ?>(<?php echo htmlspecialchars($s['designation']); ?>)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Approver <small class="req">*</small></label>
                        <select name="approver_id" id="settingsApprover" class="form-control">
                            <option value="">-- Select Approver --</option>
                            <?php foreach ($staff_list as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo (isset($settings['approver_id']) && (int)$settings['approver_id'] === (int)$s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['name'] . ' ' . $s['surname']); ?>
                                <?php if (!empty($s['designation'])): ?>(<?php echo htmlspecialchars($s['designation']); ?>)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="settingsSaveBtn"><i class="fa fa-save"></i> Save Settings</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    $('#settingsSaveBtn').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        var data = {
            verifier_id: $('#settingsVerifier').val(),
            approver_id: $('#settingsApprover').val()
        };
        $.post('<?php echo site_url('admin/scholarshipapplication/settings_ajax'); ?>', data, function (res) {
            if (res.success) {
                $('#settingsMsg').html('<div class="alert alert-success">' + res.msg + '</div>');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                $('#settingsMsg').html('<div class="alert alert-danger">' + res.msg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
            }
        }, 'json').fail(function () {
            $('#settingsMsg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
        });
    });

    // Clear message when modal reopens
    $('#settingsModal').on('show.bs.modal', function () {
        $('#settingsMsg').html('');
        $('#settingsSaveBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
    });
});
</script>
