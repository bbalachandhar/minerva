<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-plug"></i> Enquiry Lead Gen Vendors</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('schsettings/enquiryleadvendorinstructions'); ?>" class="btn btn-default btn-sm" style="margin-right: 6px;">
                                <i class="fa fa-book"></i> Instructions
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" id="addLeadVendorBtn">
                                <i class="fa fa-plus"></i> Add Vendor
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" style="margin-bottom: 15px;">
                            Manage external lead vendors here. Each vendor gets a unique <code>vendor_code</code> and secret <code>api_key</code>.
                            API requests are accepted only for active vendors with valid credentials.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="leadVendorTable">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Vendor Name</th>
                                        <th>Vendor Code</th>
                                        <th style="width: 110px;">Status</th>
                                        <th>Last Used</th>
                                        <th>Created At</th>
                                        <th style="width: 220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lead_vendors)) { ?>
                                        <?php $row_no = 1; ?>
                                        <?php foreach ($lead_vendors as $vendor) { ?>
                                            <tr
                                                data-id="<?php echo (int) $vendor['id']; ?>"
                                                data-vendor-name="<?php echo html_escape((string) $vendor['vendor_name']); ?>"
                                                data-vendor-code="<?php echo html_escape((string) $vendor['vendor_code']); ?>"
                                                data-is-active="<?php echo (int) $vendor['is_active']; ?>"
                                            >
                                                <td><?php echo $row_no++; ?></td>
                                                <td class="vendor-name-cell"><?php echo html_escape((string) $vendor['vendor_name']); ?></td>
                                                <td class="vendor-code-cell"><code><?php echo html_escape((string) $vendor['vendor_code']); ?></code></td>
                                                <td class="vendor-status-cell">
                                                    <?php if ((int) $vendor['is_active'] === 1) { ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php } else { ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo !empty($vendor['last_used_at']) ? html_escape((string) $vendor['last_used_at']) : '<span class="text-muted">Never</span>'; ?></td>
                                                <td><?php echo !empty($vendor['created_at']) ? html_escape((string) $vendor['created_at']) : '-'; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-default btn-xs editLeadVendorBtn" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>

                                                    <?php if ((int) $vendor['is_active'] === 1) { ?>
                                                        <button type="button" class="btn btn-warning btn-xs toggleLeadVendorBtn" data-next-active="0" title="Deactivate">
                                                            <i class="fa fa-ban"></i>
                                                        </button>
                                                    <?php } else { ?>
                                                        <button type="button" class="btn btn-info btn-xs toggleLeadVendorBtn" data-next-active="1" title="Activate">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    <?php } ?>

                                                    <button type="button" class="btn btn-danger btn-xs deleteLeadVendorBtn" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr class="no-data-row">
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fa fa-info-circle"></i> No vendors configured yet.
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══════════════════════════════════════════════════════ -->
                <!--  Meta Lead Ads Integration                               -->
                <!-- ═══════════════════════════════════════════════════════ -->
                <div class="box box-warning" style="margin-top:24px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-facebook-square"></i> Meta Lead Ads Integration</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-<?php echo (!empty($setting->meta_leads_enabled) && $setting->meta_leads_enabled == 1) ? 'minus' : 'plus'; ?>"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" <?php echo (empty($setting->meta_leads_enabled) || $setting->meta_leads_enabled != 1) ? 'style="display:none;"' : ''; ?>>
                        <div class="row">
                            <!-- Left: Webhook URL + Tips -->
                            <div class="col-md-5">
                                <div class="well well-sm">
                                    <strong><i class="fa fa-link"></i> Your Webhook URL</strong><br>
                                    <p class="text-muted" style="margin:6px 0;">Paste this in Meta Business Manager &rarr; Webhooks &rarr; Page subscription.</p>
                                    <div class="input-group">
                                        <input type="text" id="metaWebhookUrlBox" class="form-control input-sm" readonly
                                               value="<?php echo htmlspecialchars($webhook_url, ENT_QUOTES); ?>">
                                        <span class="input-group-btn">
                                            <button class="btn btn-default btn-sm" onclick="copyMetaWebhookUrl()">
                                                <i class="fa fa-copy"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-muted" style="font-size:12px; line-height:1.8; padding:0 4px;">
                                    <strong>Steps:</strong>
                                    <ol style="padding-left:16px; margin:6px 0;">
                                        <li>Open Meta Business Suite &rarr; your App &rarr; <strong>Webhooks</strong>.</li>
                                        <li>Add <strong>Page</strong> subscription, paste the URL above &amp; the Verify Token.</li>
                                        <li>Subscribe to the <code>leadgen</code> field.</li>
                                        <li>Paste your <strong>Page Access Token</strong> (from Graph API Explorer with <code>leads_retrieval</code> permission).</li>
                                        <li>Enable the toggle here and click <strong>Save</strong>.</li>
                                    </ol>
                                </div>
                            </div>
                            <!-- Right: Settings form -->
                            <div class="col-md-7">
                                <form id="metaLeadsForm">
                                    <!-- Enable -->
                                    <div class="form-group">
                                        <label>Integration Status</label>
                                        <div>
                                            <label class="radio-inline">
                                                <input type="radio" name="meta_leads_enabled" value="1"
                                                    <?php echo (!empty($setting->meta_leads_enabled) && $setting->meta_leads_enabled == 1) ? 'checked' : ''; ?>>
                                                <span class="text-success"><strong>Enabled</strong></span>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="meta_leads_enabled" value="0"
                                                    <?php echo (empty($setting->meta_leads_enabled) || $setting->meta_leads_enabled == 0) ? 'checked' : ''; ?>>
                                                <span class="text-danger"><strong>Disabled</strong></span>
                                            </label>
                                        </div>
                                    </div>
                                    <!-- Verify Token -->
                                    <div class="form-group">
                                        <label>Verify Token <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="meta_verify_token"
                                                   placeholder="e.g. mysecrettoken123"
                                                   value="<?php echo htmlspecialchars($setting->meta_verify_token ?? '', ENT_QUOTES); ?>">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default" id="metaGenerateTokenBtn">
                                                    <i class="fa fa-refresh"></i> Generate
                                                </button>
                                            </span>
                                        </div>
                                        <p class="help-block">Must match the Verify Token you enter in Meta's Webhooks setup.</p>
                                    </div>
                                    <!-- Page Access Token -->
                                    <div class="form-group">
                                        <label>Page Access Token <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="meta_page_access_token" rows="3"
                                                  placeholder="Paste your long-lived Page Access Token..."><?php echo htmlspecialchars($setting->meta_page_access_token ?? '', ENT_QUOTES); ?></textarea>
                                        <p class="help-block">Used to fetch lead details from Meta Graph API on each webhook event.</p>
                                    </div>
                                    <!-- Page ID + App Secret side by side -->
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Facebook Page ID</label>
                                                <input type="text" class="form-control" name="meta_page_id"
                                                       placeholder="e.g. 123456789012345"
                                                       value="<?php echo htmlspecialchars($setting->meta_page_id ?? '', ENT_QUOTES); ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>App Secret <small class="text-muted">(recommended)</small></label>
                                                <input type="password" class="form-control" name="meta_app_secret"
                                                       placeholder="Leave blank to keep existing"
                                                       autocomplete="new-password">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Default Course -->
                                    <div class="form-group">
                                        <label>Default Course <small class="text-muted">(if not matched from lead)</small></label>
                                        <select class="form-control" name="meta_default_course_id">
                                            <option value="0">&mdash; None &mdash;</option>
                                            <?php foreach ($courselist as $course): ?>
                                                <option value="<?php echo (int) $course->id; ?>"
                                                    <?php echo (isset($setting->meta_default_course_id) && $setting->meta_default_course_id == $course->id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($course->course_name, ENT_QUOTES); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Save -->
                                    <button type="submit" class="btn btn-warning" id="saveMetaLeadsBtn">
                                        <i class="fa fa-save"></i> Save Meta Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- / Meta Lead Ads -->

            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="leadVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="leadVendorModalTitle">Add Lead Vendor</h4>
            </div>
            <div class="modal-body">
                <form id="leadVendorForm">
                    <input type="hidden" name="id" id="lead_vendor_id" value="0">

                    <div class="form-group">
                        <label for="lead_vendor_name">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="vendor_name" id="lead_vendor_name" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label for="lead_vendor_code">Vendor Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="vendor_code" id="lead_vendor_code" maxlength="50" required>
                        <p class="help-block">Used by API clients as <code>vendor_code</code>. Allowed: letters, numbers, <code>_</code>, <code>-</code>.</p>
                    </div>

                    <div class="form-group">
                        <label for="lead_vendor_api_key">API Key <span class="text-danger" id="apiKeyRequiredMark">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="api_key" id="lead_vendor_api_key" autocomplete="off" placeholder="Enter or autogenerate a key">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="copyApiKeyBtn" title="Copy key to clipboard" disabled>
                                    <i class="fa fa-clipboard"></i> Copy
                                </button>
                                <button type="button" class="btn btn-default" id="generateApiKeyBtn" title="Autogenerate strong API key">
                                    <i class="fa fa-refresh"></i> Generate
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="apiKeyHelpText">Provide a secure key. It will be stored as hash only.</p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_active" id="lead_vendor_is_active" value="1" checked>
                            Active Vendor
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLeadVendorBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    'use strict';

    var saveUrl = '<?php echo site_url('schsettings/ajax_save_lead_vendor'); ?>';
    var toggleUrl = '<?php echo site_url('schsettings/ajax_toggle_lead_vendor'); ?>';
    var deleteUrl = '<?php echo site_url('schsettings/ajax_delete_lead_vendor'); ?>';

    function resetVendorForm() {
        $('#leadVendorForm')[0].reset();
        $('#lead_vendor_id').val(0);
        $('#leadVendorModalTitle').text('Add Lead Vendor');
        $('#apiKeyRequiredMark').show();
        $('#apiKeyHelpText').html('Provide a secure key. It will be stored as hash only.');
        $('#lead_vendor_api_key').prop('disabled', false).val('').attr('placeholder', 'Enter or autogenerate a key');
        $('#copyApiKeyBtn').prop('disabled', true);
        $('#lead_vendor_is_active').prop('checked', true);
    }

    function openAddModal() {
        resetVendorForm();
        $('#leadVendorModal').modal('show');
    }

    function openEditModal($row) {
        resetVendorForm();

        var id = parseInt($row.data('id'), 10) || 0;
        var vendorName = $row.data('vendor-name') || '';
        var vendorCode = $row.data('vendor-code') || '';
        var isActive = parseInt($row.data('is-active'), 10) === 1;

        $('#lead_vendor_id').val(id);
        $('#lead_vendor_name').val(vendorName);
        $('#lead_vendor_code').val(vendorCode);
        $('#lead_vendor_is_active').prop('checked', isActive);
        $('#leadVendorModalTitle').text('Edit Lead Vendor');
        $('#apiKeyRequiredMark').hide();
        // Lock the key field — hash is stored, original cannot be retrieved
        $('#lead_vendor_api_key').prop('disabled', true).val('').attr('placeholder', '\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022 Key is already set');
        $('#copyApiKeyBtn').prop('disabled', true);
        $('#apiKeyHelpText').html('Key is stored securely. <a href="#" id="changeKeyLink">Change / Rotate key</a>');

        $('#leadVendorModal').modal('show');
    }

    function sanitizeVendorCode(value) {
        return (value || '').toLowerCase().replace(/[^a-z0-9_-]/g, '');
    }

    $('#lead_vendor_code').on('input', function() {
        var cleaned = sanitizeVendorCode($(this).val());
        if ($(this).val() !== cleaned) {
            $(this).val(cleaned);
        }
    });

    $('#generateApiKeyBtn').on('click', function() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';
        var key = '';
        var array = new Uint8Array(48);
        window.crypto.getRandomValues(array);
        for (var i = 0; i < array.length; i++) {
            key += chars[array[i] % chars.length];
        }
        $('#lead_vendor_api_key').prop('disabled', false).val(key);
        $('#copyApiKeyBtn').prop('disabled', false);
    });

    $('#lead_vendor_api_key').on('input', function() {
        $('#copyApiKeyBtn').prop('disabled', $(this).val().length === 0);
    });

    $(document).on('click', '#changeKeyLink', function(e) {
        e.preventDefault();
        $('#lead_vendor_api_key').prop('disabled', false).val('').attr('placeholder', 'Enter new key or autogenerate').focus();
        $('#apiKeyHelpText').html('Enter a new key to rotate. Leave blank to keep existing key.');
        $('#copyApiKeyBtn').prop('disabled', true);
    });

    $('#copyApiKeyBtn').on('click', function() {
        var key = $('#lead_vendor_api_key').val();
        if (!key) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(key).then(function() {
                successMsg('API key copied to clipboard.');
            });
        } else {
            // Fallback for non-secure contexts
            var $tmp = $('<textarea>').val(key).appendTo('body').select();
            document.execCommand('copy');
            $tmp.remove();
            successMsg('API key copied to clipboard.');
        }
    });

    $('#addLeadVendorBtn').on('click', function() {
        openAddModal();
    });

    $(document).on('click', '.editLeadVendorBtn', function() {
        openEditModal($(this).closest('tr'));
    });

    $('#saveLeadVendorBtn').on('click', function() {
        var $btn = $(this);
        var id = parseInt($('#lead_vendor_id').val(), 10) || 0;
        var vendorName = $.trim($('#lead_vendor_name').val());
        var vendorCode = sanitizeVendorCode($('#lead_vendor_code').val());
        var apiKey = $.trim($('#lead_vendor_api_key').val());

        $('#lead_vendor_code').val(vendorCode);

        if (vendorName === '') {
            errorMsg('Vendor name is required.');
            return;
        }

        if (vendorCode === '') {
            errorMsg('Vendor code is required.');
            return;
        }

        if (id === 0 && apiKey === '') {
            errorMsg('API key is required while creating a vendor.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: saveUrl,
            type: 'POST',
            dataType: 'json',
            data: $('#leadVendorForm').serialize(),
            success: function(response) {
                if (response && response.status === 'success') {
                    successMsg(response.message || 'Vendor saved successfully.');
                    $('#leadVendorModal').modal('hide');
                    setTimeout(function() { location.reload(); }, 400);
                } else {
                    errorMsg((response && response.message) ? response.message : 'Failed to save vendor.');
                }
            },
            error: function() {
                errorMsg('Failed to save vendor.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Save');
            }
        });
    });

    $(document).on('click', '.toggleLeadVendorBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var id = parseInt($row.data('id'), 10) || 0;
        var nextActive = parseInt($btn.data('next-active'), 10) === 1 ? 1 : 0;

        if (id <= 0) {
            errorMsg('Invalid vendor id.');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: toggleUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                id: id,
                is_active: nextActive
            },
            success: function(response) {
                if (response && response.status === 'success') {
                    successMsg(response.message || 'Vendor status updated.');
                    setTimeout(function() { location.reload(); }, 300);
                } else {
                    errorMsg((response && response.message) ? response.message : 'Failed to update vendor status.');
                }
            },
            error: function() {
                errorMsg('Failed to update vendor status.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.deleteLeadVendorBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var id = parseInt($row.data('id'), 10) || 0;
        var vendorName = $row.data('vendor-name') || 'this vendor';

        if (id <= 0) {
            errorMsg('Invalid vendor id.');
            return;
        }

        if (!confirm('Delete vendor "' + vendorName + '"? This will stop API auth for this vendor immediately.')) {
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: deleteUrl,
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(response) {
                if (response && response.status === 'success') {
                    successMsg(response.message || 'Vendor deleted successfully.');
                    setTimeout(function() { location.reload(); }, 300);
                } else {
                    errorMsg((response && response.message) ? response.message : 'Failed to delete vendor.');
                }
            },
            error: function() {
                errorMsg('Failed to delete vendor.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
})(jQuery);

/* ── Meta Lead Ads section ──────────────────────────────────────────────── */
(function($) {
    'use strict';

    // Generate verify token
    $('#metaGenerateTokenBtn').on('click', function() {
        $.post('<?php echo site_url('schsettings/ajax_generate_meta_verify_token'); ?>', function(r) {
            if (r && r.status === 'success') {
                $('[name="meta_verify_token"]').val(r.token);
                successMsg('Token generated \u2014 copy it to Meta Webhooks before saving!');
            }
        }, 'json');
    });

    // Copy webhook URL
    window.copyMetaWebhookUrl = function() {
        var el = document.getElementById('metaWebhookUrlBox');
        el.select();
        document.execCommand('copy');
        successMsg('Webhook URL copied!');
    };

    // Save Meta settings
    $('#metaLeadsForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#saveMetaLeadsBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url      : '<?php echo site_url('schsettings/ajax_save_metaleads_config'); ?>',
            type     : 'POST',
            dataType : 'json',
            data     : $(this).serialize(),
            success  : function(r) {
                if (r && r.status === 'success') {
                    successMsg(r.message || 'Meta settings saved.');
                } else {
                    errorMsg((r && r.message) ? r.message : 'Failed to save.');
                }
            },
            error    : function() { errorMsg('Server error.'); },
            complete : function() { $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Meta Settings'); }
        });
    });
})(jQuery);
</script>
