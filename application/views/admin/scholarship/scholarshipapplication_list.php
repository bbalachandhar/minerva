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

        <!-- ── Page Instructions ──────────────────────────────────────────── -->
        <div class="box box-info collapsed-box">
            <div class="box-header with-border" style="cursor:pointer" data-widget="collapse">
                <h3 class="box-title"><i class="fa fa-question-circle"></i> How this page works &amp; Audit Guide</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                </div>
            </div>
            <div class="box-body" style="display:none;">
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-flow-line"></i> Workflow</h4>
                        <ol style="padding-left:18px; line-height:2;">
                            <li><strong>Applicant submits</strong> — application lands in <span class="label label-warning">Pending</span> state.</li>
                            <li><strong>Verifier reviews</strong> — the verifier assigned to each scholarship <em>type</em> checks eligibility and marks it <span class="label label-info">Verified</span> or <span class="label label-danger">Rejected</span>.</li>
                            <li><strong>Approver approves</strong> — the global approver (set in Approver Settings) grants or rejects the verified application. Final state becomes <span class="label label-success">Approved</span> or <span class="label label-danger">Rejected</span>.</li>
                            <li><strong>Merit Scholarship</strong> — applications created via <a href="<?php echo site_url('admin/meritscholarship'); ?>">Merit Exam Marks page</a> are auto-set to <span class="label label-success">Approved</span> based on MAT-SET exam score.</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fa fa-search"></i> Audit Tips</h4>
                        <ul style="padding-left:18px; line-height:2;">
                            <li>Use the <strong>Status filter</strong> to isolate pending/approved/rejected applications.</li>
                            <li>Use the <strong>Scholarship Type filter</strong> to see all applications for a specific scholarship (e.g. only Merit Cat 1).</li>
                            <li>Combine both filters for cross-sections (e.g. "Approved Merit Cat 1" applications).</li>
                            <li>Export the filtered table using the <strong>Excel / PDF</strong> buttons in the table header.</li>
                            <li>Each application detail page shows who verified and who approved, with timestamps.</li>
                            <li>Verifier assignment is per scholarship type — manage via <a href="<?php echo site_url('admin/scholarshiptype'); ?>">Scholarship Types</a>.</li>
                            <li>Global approver is set via the <strong>Approver Settings</strong> button on this page.</li>
                        </ul>
                    </div>
                </div>
                <div class="callout callout-warning" style="margin-top:5px; margin-bottom:0;">
                    <strong>Status meanings:</strong>
                    <span class="label label-warning">Pending</span> — submitted, awaiting verification &nbsp;|&nbsp;
                    <span class="label label-info">Verified</span> — verifier approved, awaiting final approval &nbsp;|&nbsp;
                    <span class="label label-success">Approved</span> — fully granted &nbsp;|&nbsp;
                    <span class="label label-danger">Rejected</span> — declined at any stage
                </div>
            </div>
        </div>

        <!-- ── Status Count Widgets ──────────────────────────────────────── -->
        <div class="row">
            <?php
            $widgets = [
                ['key'=>'pending',  'label'=>'Pending',  'bg'=>'bg-yellow', 'icon'=>'fa-clock-o'],
                ['key'=>'verified', 'label'=>'Verified', 'bg'=>'bg-aqua',   'icon'=>'fa-check-circle'],
                ['key'=>'approved', 'label'=>'Approved', 'bg'=>'bg-green',  'icon'=>'fa-thumbs-up'],
                ['key'=>'rejected', 'label'=>'Rejected', 'bg'=>'bg-red',    'icon'=>'fa-times-circle'],
            ];
            foreach ($widgets as $w):
                $isActive = ($filter_status === $w['key']);
                $wUrl = site_url('admin/scholarshipapplication') . '?status=' . $w['key'] . ($filter_type_id ? '&type_id=' . $filter_type_id : '');
            ?>
            <div class="col-xs-12 col-sm-6 col-md-3">
                <a href="<?php echo $wUrl; ?>" style="text-decoration:none;">
                    <div class="info-box<?php echo $isActive ? ' ' . $w['bg'] : ''; ?>"
                         style="<?php echo $isActive ? '' : 'opacity:0.75;'; ?> transition:opacity .15s;">
                        <span class="info-box-icon <?php echo $isActive ? '' : $w['bg']; ?>">
                            <i class="fa <?php echo $w['icon']; ?>"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text"><?php echo $w['label']; ?></span>
                            <span class="info-box-number"><?php echo $status_counts[$w['key']]; ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Filters ────────────────────────────────────────────────────── -->
        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        // Build base URL preserving existing type_id filter when changing status
                        $type_qs   = $filter_type_id ? '&type_id=' . $filter_type_id : '';
                        $status_qs = $filter_status  ? '&status=' . $filter_status   : '';
                        ?>
                        <select id="statusFilter" class="form-control input-sm" style="display:inline-block;width:auto;vertical-align:middle;">
                            <option value="">— All Statuses (<?php echo $status_counts['all']; ?>) —</option>
                            <option value="pending"  <?php echo $filter_status === 'pending'  ? 'selected' : ''; ?>>Pending (<?php echo $status_counts['pending']; ?>)</option>
                            <option value="verified" <?php echo $filter_status === 'verified' ? 'selected' : ''; ?>>Verified (<?php echo $status_counts['verified']; ?>)</option>
                            <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved (<?php echo $status_counts['approved']; ?>)</option>
                            <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected (<?php echo $status_counts['rejected']; ?>)</option>
                        </select>

                        &nbsp;&nbsp;
                        <select id="typeFilter" class="form-control input-sm" style="display:inline-block;width:auto;vertical-align:middle;">
                            <option value="">— All Scholarship Types —</option>
                            <?php foreach ($scholarship_types as $st): ?>
                            <option value="<?php echo $st['id']; ?>"
                                <?php echo ((int)$filter_type_id === (int)$st['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($st['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="pull-right">
                            <a href="<?php echo site_url('admin/scholarshiptype'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-list"></i> Manage Types
                            </a>
                            <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#settingsModal">
                                <i class="fa fa-cog"></i> Approver Settings
                            </button>
                        </div>
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
            <strong>Approver:</strong>
            <strong><?php echo !empty($settings['approver_id']) ? ($staff_map[(int)$settings['approver_id']] ?? 'Staff #'.$settings['approver_id']) : '<span class="text-danger">Not set</span>'; ?></strong>
            &nbsp;&mdash; <a href="#" data-toggle="modal" data-target="#settingsModal">Change</a>
            &nbsp;|&nbsp; Verifier is assigned per scholarship type (<a href="<?php echo site_url('admin/scholarshiptype'); ?>">Manage Types</a>)
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
                            <th>Amount</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="noExport">Doc</th>
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
                            <td>
                                <?php
                                $eff_amount = isset($app['override_amount']) && $app['override_amount'] !== null
                                    ? $app['override_amount'] : ($app['scholarship_amount'] ?? null);
                                if ($eff_amount !== null && $eff_amount !== ''):
                                    echo '<strong>' . number_format((float)$eff_amount, 2) . '</strong>';
                                    if (isset($app['override_amount']) && $app['override_amount'] !== null):
                                        echo ' <span class="label label-warning" title="Overridden from type default">Override</span>';
                                    endif;
                                else:
                                    echo '<span class="text-muted">—</span>';
                                endif;
                                ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($app['created_at'])); ?></td>
                            <td><?php
                                $badges = ['pending'=>'warning','verified'=>'info','approved'=>'success','rejected'=>'danger'];
                                $b = $badges[$app['status']] ?? 'default';
                            ?><span class="label label-<?php echo $b; ?> status-badge"><?php echo ucfirst($app['status']); ?></span></td>
                            <td>
                                <?php
                                    $doc_ext    = !empty($app['document']) ? strtolower(pathinfo($app['document'], PATHINFO_EXTENSION)) : '';
                                    $doc_is_img = in_array($doc_ext, ['jpg','jpeg','png']);
                                    $_dattrs    = 'data-id="' . $app['id'] . '" '
                                                . 'data-ref="'  . htmlspecialchars($app['reference_no'] ?? '') . '" '
                                                . 'data-name="' . htmlspecialchars(trim(($app['firstname'] ?? '') . ' ' . ($app['lastname'] ?? ''))) . '"';
                                ?>
                                <?php if (!empty($app['document'])): ?>
                                    <div class="btn-group btn-group-xs" role="group">
                                        <a href="<?php echo site_url('admin/scholarshipapplication/view_doc/' . $app['id']); ?>"
                                           target="_blank"
                                           class="btn btn-xs btn-default"
                                           title="<?php echo htmlspecialchars($app['document']); ?>">
                                            <i class="fa <?php echo $doc_is_img ? 'fa-picture-o' : 'fa-file-pdf-o'; ?>"></i>
                                            <?php echo $doc_is_img ? 'Preview' : 'View PDF'; ?>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-warning btn-upload-doc"
                                                <?php echo $_dattrs; ?> title="Replace document">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-danger btn-remove-doc"
                                                <?php echo $_dattrs; ?> title="Remove document">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <button type="button" class="btn btn-xs btn-warning btn-upload-doc"
                                            <?php echo $_dattrs; ?>>
                                        <i class="fa fa-upload"></i> Upload
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <button type="button" class="btn btn-xs btn-primary btn-view-app"
                                        data-url="<?php echo site_url('admin/scholarshipapplication/view/' . $app['id']); ?>"
                                        data-ref="<?php echo htmlspecialchars($app['reference_no'] ?? ''); ?>">
                                    <i class="fa fa-eye"></i> View
                                </button>
                                <?php if ($app['status'] !== 'rejected'): ?>
                                <button type="button" class="btn btn-xs btn-danger btn-reject-app"
                                        data-id="<?php echo $app['id']; ?>"
                                        data-ref="<?php echo htmlspecialchars($app['reference_no'] ?? ''); ?>"
                                        data-name="<?php echo htmlspecialchars(trim(($app['firstname'] ?? '') . ' ' . ($app['lastname'] ?? ''))); ?>">
                                    <i class="fa fa-ban"></i> Reject
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($applications)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- ── Upload Doc Modal ─────────────────────────────────────────────── -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" role="dialog" aria-labelledby="uploadDocModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="uploadDocModalLabel"><i class="fa fa-upload"></i> Upload Scholarship Document</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="uploadDocApplicantName" style="margin-bottom:12px;"></p>
                <div id="uploadDocMsg"></div>
                <div class="form-group">
                    <label>Select File <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="uploadDocFileName" class="form-control"
                               placeholder="No file chosen" readonly
                               style="background:#fff; cursor:pointer;"
                               onclick="document.getElementById('uploadDocFile').click();">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default"
                                    onclick="document.getElementById('uploadDocFile').click();">
                                <i class="fa fa-folder-open"></i> Browse&hellip;
                            </button>
                        </span>
                    </div>
                    <input type="file" id="uploadDocFile" accept=".pdf,.jpg,.jpeg,.png" style="display:none;">
                    <small class="text-muted">Allowed: PDF, JPG, PNG &mdash; Max size: 600 KB</small>
                </div>
                <div id="uploadDocPreviewWrap" style="display:none; margin-top:10px; text-align:center;">
                    <img id="uploadDocPreviewImg" src="" alt="Preview"
                         style="max-height:150px; max-width:100%; border:1px solid #ddd; border-radius:4px; padding:4px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="uploadDocSubmitBtn">
                    <i class="fa fa-upload"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Reject Application Modal ──────────────────────────────────────── -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#d9534f; color:#fff; border-radius:3px 3px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:.8;">&times;</button>
                <h4 class="modal-title" id="rejectModalLabel"><i class="fa fa-ban"></i> Reject Scholarship Application</h4>
            </div>
            <div class="modal-body">
                <div class="callout callout-danger" style="margin-bottom:12px;">
                    <p><i class="fa fa-warning"></i> This will mark the application as <strong>Rejected</strong> regardless of current status. You can still revert it from the detail view.</p>
                </div>
                <p class="text-muted" id="rejectApplicantName" style="margin-bottom:12px; font-weight:600;"></p>
                <div id="rejectMsg"></div>
                <div class="form-group">
                    <label>Rejection Reason <span class="text-danger">*</span></label>
                    <textarea id="rejectRemarks" class="form-control" rows="3"
                              placeholder="Enter reason for rejection..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="rejectSubmitBtn">
                    <i class="fa fa-ban"></i> Confirm Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Application Detail Modal ──────────────────────────────────────── -->
<div class="modal fade" id="appDetailModal" tabindex="-1" role="dialog" aria-labelledby="appDetailModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="appDetailModalLabel">
                    <i class="fa fa-graduation-cap"></i> Scholarship Application
                    <small id="appDetailRef" class="text-muted"></small>
                </h4>
            </div>
            <div class="modal-body" id="appDetailBody">
                <div class="text-center" style="padding:40px 0;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i><br/><small>Loading…</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Approver Settings Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="settingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="settingsModalLabel"><i class="fa fa-cog"></i> Final Approver Setting</h4>
            </div>
            <div class="modal-body">
                <div class="callout callout-info">
                    <p><strong>Global Approver:</strong><br/>
                    The final approver makes the grant decision for all scholarship types.<br/>
                    The verifier for each type is set on the <a href="<?php echo site_url('admin/scholarshiptype'); ?>" target="_blank">Scholarship Types</a> page.</p>
                </div>
                <div id="settingsMsg"></div>
                <form id="settingsForm">
                    <div class="form-group">
                        <label>Final Approver <small class="req">*</small></label>
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
                <button type="button" class="btn btn-primary" id="settingsSaveBtn"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    $('#settingsSaveBtn').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        var data = { approver_id: $('#settingsApprover').val() };
        $.post('<?php echo site_url('admin/scholarshipapplication/settings_ajax'); ?>', data, function (res) {
            if (res.success) {
                $('#settingsMsg').html('<div class="alert alert-success">' + res.msg + '</div>');
                setTimeout(function () { location.reload(); }, 800);
            } else {
                $('#settingsMsg').html('<div class="alert alert-danger">' + res.msg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            }
        }, 'json').fail(function () {
            $('#settingsMsg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
        });
    });

    $('#settingsModal').on('show.bs.modal', function () {
        $('#settingsMsg').html('');
        $('#settingsSaveBtn').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
    });

    $('#settingsApprover').select2({ dropdownParent: $('#settingsModal'), width: '100%' });

    // Type filter dropdown — navigate preserving current status
    $('#typeFilter').on('change', function () {
        var typeId    = $(this).val();
        var statusVal = '<?php echo addslashes($filter_status ?? ''); ?>';
        var url       = '<?php echo site_url('admin/scholarshipapplication'); ?>?';
        if (statusVal) url += 'status=' + statusVal + '&';
        if (typeId)    url += 'type_id=' + typeId;
        window.location.href = url;
    });

    // Status filter dropdown — navigate preserving current type
    $('#statusFilter').on('change', function () {
        var statusVal = $(this).val();
        var typeId    = '<?php echo addslashes($filter_type_id ?? ''); ?>';
        var url       = '<?php echo site_url('admin/scholarshipapplication'); ?>?';
        if (statusVal) url += 'status=' + statusVal + '&';
        if (typeId)    url += 'type_id=' + typeId;
        window.location.href = url;
    });

    // ── Upload Doc Modal ──────────────────────────────────────────────────
    var _uploadDocAppId  = null;
    var _uploadDocRef    = '';
    var _uploadDocName   = '';

    function _escAttr(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    $(document).on('click', '.btn-upload-doc', function () {
        _uploadDocAppId = $(this).data('id');
        var name = $(this).data('name') || '';
        var ref  = $(this).data('ref')  || '';
        _uploadDocRef   = name;
        _uploadDocName  = ref;
        $('#uploadDocApplicantName').text((name || 'Applicant') + (ref ? '  (' + ref + ')' : ''));
        $('#uploadDocMsg').html('');
        $('#uploadDocFile').val('');
        $('#uploadDocFileName').val('');
        $('#uploadDocPreviewWrap').hide();
        $('#uploadDocSubmitBtn').prop('disabled', false).html('<i class="fa fa-upload"></i> Upload');
        $('#uploadDocModal').modal('show');
    });

    $('#uploadDocFile').on('change', function () {
        var file = this.files[0];
        if (!file) { $('#uploadDocPreviewWrap').hide(); $('#uploadDocFileName').val(''); return; }
        $('#uploadDocFileName').val(file.name);
        var maxSize  = 614400; // 600 KB
        var allowed  = ['image/jpeg', 'image/png', 'application/pdf'];
        if (allowed.indexOf(file.type) === -1) {
            $('#uploadDocMsg').html('<div class="alert alert-danger">Only JPG, PNG, or PDF files are allowed.</div>');
            this.value = '';
            $('#uploadDocPreviewWrap').hide();
            return;
        }
        if (file.size > maxSize) {
            $('#uploadDocMsg').html('<div class="alert alert-danger">File must be 600 KB or smaller. Selected: ' + (file.size / 1024).toFixed(1) + ' KB.</div>');
            this.value = '';
            $('#uploadDocPreviewWrap').hide();
            return;
        }
        $('#uploadDocMsg').html('');
        if (file.type.indexOf('image/') === 0) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#uploadDocPreviewImg').attr('src', e.target.result);
                $('#uploadDocPreviewWrap').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#uploadDocPreviewWrap').hide();
        }
    });

    $('#uploadDocSubmitBtn').on('click', function () {
        var file = $('#uploadDocFile')[0].files[0];
        if (!file) {
            $('#uploadDocMsg').html('<div class="alert alert-danger">Please select a file.</div>');
            return;
        }
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
        var formData = new FormData();
        formData.append('doc_file', file);
        $.ajax({
            url: '<?php echo site_url('admin/scholarshipapplication/upload_doc'); ?>/' + _uploadDocAppId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#uploadDocMsg').html('<div class="alert alert-success">' + res.msg + '</div>');
                    var iconClass = res.is_image ? 'fa-picture-o' : 'fa-file-pdf-o';
                    var btnLabel  = res.is_image ? 'Preview'      : 'View PDF';
                    var _id       = _uploadDocAppId;
                    var _ref      = _escAttr(_uploadDocRef);
                    var _nm       = _escAttr(_uploadDocName);
                    var newHtml   = '<div class="btn-group btn-group-xs" role="group">'
                                  + '<a href="' + res.view_url + '" target="_blank" class="btn btn-xs btn-default">'
                                  + '<i class="fa ' + iconClass + '"></i> ' + btnLabel + '</a>'
                                  + '<button type="button" class="btn btn-xs btn-warning btn-upload-doc"'
                                  + ' data-id="' + _id + '" data-ref="' + _ref + '" data-name="' + _nm + '"'
                                  + ' title="Replace document"><i class="fa fa-refresh"></i></button>'
                                  + '<button type="button" class="btn btn-xs btn-danger btn-remove-doc"'
                                  + ' data-id="' + _id + '" data-ref="' + _ref + '" data-name="' + _nm + '"'
                                  + ' title="Remove document"><i class="fa fa-trash"></i></button>'
                                  + '</div>';
                    $('[data-id="' + _uploadDocAppId + '"].btn-upload-doc').closest('td').html(newHtml);
                    setTimeout(function () { $('#uploadDocModal').modal('hide'); }, 1000);
                } else {
                    $('#uploadDocMsg').html('<div class="alert alert-danger">' + res.msg + '</div>');
                    $btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Upload');
                }
            },
            error: function () {
                $('#uploadDocMsg').html('<div class="alert alert-danger">Upload failed. Please try again.</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Upload');
            }
        });
    });

    // ── Reject application ────────────────────────────────────────────────────
    var _rejectAppId = null;

    $(document).on('click', '.btn-reject-app', function () {
        _rejectAppId = $(this).data('id');
        var name = $(this).data('name') || '';
        var ref  = $(this).data('ref')  || '';
        $('#rejectApplicantName').text((name ? name : '') + (ref ? ' (' + ref + ')' : ''));
        $('#rejectMsg').html('');
        $('#rejectRemarks').val('');
        $('#rejectSubmitBtn').prop('disabled', false).html('<i class="fa fa-ban"></i> Confirm Reject');
        $('#rejectModal').modal('show');
    });

    $('#rejectSubmitBtn').on('click', function () {
        var remarks = $.trim($('#rejectRemarks').val());
        if (!remarks) {
            $('#rejectMsg').html('<div class="alert alert-danger">Please enter a rejection reason.</div>');
            return;
        }
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Rejecting...');
        $.post('<?php echo site_url('admin/scholarshipapplication/reject_ajax'); ?>/' + _rejectAppId,
               { remarks: remarks },
        function (res) {
            if (res.success) {
                $('#rejectMsg').html('<div class="alert alert-success">' + res.msg + '</div>');
                $('[data-id="' + _rejectAppId + '"].btn-reject-app').closest('tr')
                    .find('.status-badge')
                    .removeClass().addClass('label label-danger status-badge').text('Rejected');
                $('[data-id="' + _rejectAppId + '"].btn-reject-app').remove();
                setTimeout(function () { $('#rejectModal').modal('hide'); }, 900);
            } else {
                $('#rejectMsg').html('<div class="alert alert-danger">' + res.msg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-ban"></i> Confirm Reject');
            }
        }, 'json').fail(function () {
            $('#rejectMsg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            $btn.prop('disabled', false).html('<i class="fa fa-ban"></i> Confirm Reject');
        });
    });

    // Remove document
    $(document).on('click', '.btn-remove-doc', function () {
        var id   = $(this).data('id');
        var ref  = $(this).data('ref')  || '';
        var name = $(this).data('name') || '';
        if (!confirm('Remove the uploaded document for this application? This cannot be undone.')) return;
        var $td  = $(this).closest('td');
        $.post('<?php echo site_url('admin/scholarshipapplication/remove_doc'); ?>/' + id, {}, function (res) {
            if (res.success) {
                var _r = _escAttr(ref);
                var _n = _escAttr(name);
                var uploadBtn = '<button type="button" class="btn btn-xs btn-warning btn-upload-doc"'
                              + ' data-id="' + id + '" data-ref="' + _r + '" data-name="' + _n + '">'
                              + '<i class="fa fa-upload"></i> Upload</button>';
                $td.html(uploadBtn);
            } else {
                alert(res.msg || 'Could not remove document.');
            }
        }, 'json').fail(function () {
            alert('Error removing document. Please try again.');
        });
    });

    // Application detail modal — AJAX load
    $(document).on('click', '.btn-view-app', function () {
        var url = $(this).data('url');
        var ref = $(this).data('ref');
        $('#appDetailRef').text(ref ? ' \u2014 ' + ref : '');
        $('#appDetailBody').html('<div class="text-center" style="padding:40px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i><br/><small>Loading\u2026</small></div>');
        $('#appDetailModal').modal('show');
        $.ajax({
            url: url,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) { $('#appDetailBody').html(html); },
            error:   function ()     { $('#appDetailBody').html('<div class="alert alert-danger">Failed to load application details. Please try again.</div>'); }
        });
    });

    // DataTable — 20 records per page
    $('.example').DataTable({
        "aaSorting": [],
        pageLength: 20,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5',  text: '<i class="fa fa-files-o"></i>',      titleAttr: 'Copy',  exportOptions: { columns: 'thead th:not(.noExport)' } },
            { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i>',  titleAttr: 'Excel', exportOptions: { columns: 'thead th:not(.noExport)' } },
            { extend: 'pdfHtml5',   text: '<i class="fa fa-file-pdf-o"></i>',    titleAttr: 'PDF',   exportOptions: { columns: 'thead th:not(.noExport)' } },
            { extend: 'colvis',     text: '<i class="fa fa-columns"></i>' }
        ]
    });
});
</script>
