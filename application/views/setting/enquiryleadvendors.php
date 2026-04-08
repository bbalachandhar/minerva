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
                                                       placeholder="<?php echo !empty($setting->meta_app_secret) ? '••••••••  (saved — leave blank to keep)' : 'Enter App Secret'; ?>"
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

                <!-- ═══════════════════════════════════════════════════════ -->
                <!--  Meta Integration Diagnostics                           -->
                <!-- ═══════════════════════════════════════════════════════ -->
                <div class="box box-info" id="metaDiagnosticsBox" style="margin-top:24px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-stethoscope"></i> Meta Integration Diagnostics</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="display:none;">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="well well-sm">
                                    <strong>Step 1 — Check Integration Status</strong>
                                    <p class="text-muted" style="margin:6px 0 10px;">Verifies your Page Access Token and whether the page is subscribed to <code>leadgen</code> webhooks.</p>
                                    <button type="button" class="btn btn-info btn-sm" id="btnCheckMetaStatus">
                                        <i class="fa fa-search"></i> Check Status
                                    </button>
                                    <div id="metaStatusResult" style="margin-top:14px; display:none;"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="well well-sm">
                                    <strong>Step 2 — Subscribe Page to Webhook</strong>
                                    <p class="text-muted" style="margin:6px 0 10px;">
                                        Click to call <code>POST /{page_id}/subscribed_apps?subscribed_fields=leadgen</code> using your stored Page Access Token.
                                        This is required before Meta will send lead events to your webhook URL.
                                    </p>
                                    <button type="button" class="btn btn-warning btn-sm" id="btnSubscribeMetaPage">
                                        <i class="fa fa-link"></i> Subscribe Page to leadgen Webhooks
                                    </button>
                                    <div id="metaSubscribeResult" style="margin-top:14px; display:none;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Token Renewal Instructions ─────────────────────────────── -->
                        <div class="panel panel-default" style="margin-top:18px;">
                            <div class="panel-heading" style="cursor:pointer;" data-toggle="collapse" data-target="#tokenRenewalBody">
                                <strong><i class="fa fa-key"></i> How to renew your Page Access Token</strong>
                                <span class="pull-right text-muted" style="font-size:12px;">
                                    Needed when you see <em>"Session has expired"</em> or <em>"Invalid OAuth access token"</em>
                                    <i class="fa fa-chevron-down" style="margin-left:6px;"></i>
                                </span>
                            </div>
                            <div id="tokenRenewalBody" class="panel-collapse collapse">
                                <div class="panel-body" style="font-size:13px; line-height:1.9;">

                                    <div class="alert alert-warning" style="padding:8px 12px; font-size:12px; margin-bottom:14px;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <strong>Why tokens expire:</strong> Tokens obtained directly from Graph API Explorer are short-lived (~2 hours) or 60-day user tokens.
                                        You need a <strong>Page Access Token derived from a long-lived user token</strong> — this type <em>never expires</em> as long as the user does not revoke app access.
                                    </div>

                                    <ol style="padding-left:18px; margin:0;">
                                        <li style="margin-bottom:10px;">
                                            <strong>Get a short-lived user token</strong><br>
                                            Open <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Graph API Explorer <i class="fa fa-external-link"></i></a>,
                                            select your app, click <strong>Generate Access Token</strong>.<br>
                                            Required permissions: <code>pages_manage_metadata</code>, <code>pages_read_engagement</code>, <code>leads_retrieval</code>.
                                        </li>
                                        <li style="margin-bottom:10px;">
                                            <strong>Exchange for a long-lived user token</strong><br>
                                            Open a new browser tab and go to:
                                            <div class="well well-sm" style="margin:6px 0; font-size:12px; word-break:break-all; background:#f9f9f9;">
                                                https://graph.facebook.com/v19.0/oauth/access_token<br>
                                                &nbsp;&nbsp;?grant_type=fb_exchange_token<br>
                                                &nbsp;&nbsp;&amp;client_id=<strong>YOUR_APP_ID</strong><br>
                                                &nbsp;&nbsp;&amp;client_secret=<strong><?php echo htmlspecialchars($setting->meta_app_secret ?? 'YOUR_APP_SECRET', ENT_QUOTES); ?></strong><br>
                                                &nbsp;&nbsp;&amp;fb_exchange_token=<strong>SHORT_LIVED_TOKEN_FROM_STEP_1</strong>
                                            </div>
                                            Copy the <code>access_token</code> from the JSON response. This is your 60-day user token.
                                        </li>
                                        <li style="margin-bottom:10px;">
                                            <strong>Get the permanent Page Access Token</strong><br>
                                            Call:
                                            <div class="well well-sm" style="margin:6px 0; font-size:12px; word-break:break-all; background:#f9f9f9;">
                                                https://graph.facebook.com/v19.0/me/accounts<br>
                                                &nbsp;&nbsp;?access_token=<strong>LONG_LIVED_USER_TOKEN_FROM_STEP_2</strong>
                                            </div>
                                            Find your page in the <code>data</code> array. Copy its <code>access_token</code> field.
                                            This page token <strong>does not expire</strong> unless you deauthorise the app.
                                        </li>
                                        <li style="margin-bottom:10px;">
                                            <strong>Paste the new token here</strong><br>
                                            Scroll up to <em>Meta Lead Ads Integration</em>, paste into <strong>Page Access Token</strong>, and click <strong>Save Meta Settings</strong>.
                                        </li>
                                        <li>
                                            <strong>Re-subscribe the page</strong><br>
                                            Come back here and click <strong>Subscribe Page to leadgen Webhooks</strong> — the subscription may lapse when a token expires.
                                            Then click <strong>Check Status</strong> to confirm everything is green.
                                        </li>
                                    </ol>

                                </div>
                            </div>
                        </div>
                        <!-- ── /Token Renewal Instructions ─────────────────────────────── -->

                        <div style="margin-top:10px;">
                            <strong>Recent Webhook Events</strong>
                            <button type="button" class="btn btn-default btn-xs" id="btnLoadMetaEvents" style="margin-left:10px;">
                                <i class="fa fa-refresh"></i> Load / Refresh
                            </button>
                            <div id="metaEventsTable" style="margin-top:10px;"></div>
                        </div>

                    </div>
                </div>
                <!-- / Meta Integration Diagnostics -->

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

/* ── Meta Diagnostics section ───────────────────────────────────────────── */
(function($) {
    'use strict';

    var checkUrl     = '<?php echo site_url('schsettings/ajax_check_meta_page_status'); ?>';
    var subscribeUrl = '<?php echo site_url('schsettings/ajax_subscribe_meta_page'); ?>';
    var eventsUrl    = '<?php echo site_url('schsettings/ajax_get_meta_events'); ?>';

    // ── Check Status ──────────────────────────────────────────────────────
    $('#btnCheckMetaStatus').on('click', function() {
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking...');
        $('#metaStatusResult').hide();
        $.ajax({
            url: checkUrl,
            type: 'GET',
            dataType: 'json',
            success: function(r) {
            var html = '';
            if (!r || r.status !== 'success') {
                html = '<div class="alert alert-danger">' + (r ? r.message : 'Request failed') + '</div>';
            } else {
                var d = r.data;
                var tokenBadge = d.token_valid
                    ? '<span class="label label-success"><i class="fa fa-check"></i> Token Valid</span>'
                    : '<span class="label label-danger"><i class="fa fa-times"></i> Token Invalid</span>';
                var subBadge;
                if (d.subscribed) {
                    subBadge = '<span class="label label-success"><i class="fa fa-check"></i> Page Subscribed to leadgen</span>';
                } else if (d.sub_perm_hint) {
                    subBadge = '<span class="label label-info"><i class="fa fa-question-circle"></i> Subscription status unknown (permission)</span>';
                } else {
                    subBadge = '<span class="label label-warning"><i class="fa fa-warning"></i> Page NOT subscribed to leadgen</span>';
                }
                html += '<p>' + tokenBadge + (d.token_error ? ' &mdash; ' + $('<span>').text(d.token_error).html() : '') + '</p>';
                html += '<p>' + subBadge   + (d.sub_error   ? ' &mdash; ' + $('<span>').text(d.sub_error).html()   : '') + '</p>';
                if (!d.subscribed && !d.sub_perm_hint) {
                    html += '<div class="alert alert-warning" style="padding:8px 12px; font-size:12px;">'
                        + '<strong>Action required:</strong> Use the <em>Subscribe Page</em> button on the right to register your page for <code>leadgen</code> events. '
                        + 'Without this, Meta verifies your webhook URL but never sends lead data.'
                        + '</div>';
                }
                if (d.last_event) {
                    var ev = d.last_event;
                    html += '<p class="text-muted" style="font-size:12px; margin-top:6px;">'
                        + '<i class="fa fa-history"></i> Last webhook hit: <strong>' + $('<span>').text(ev.received_at).html() + '</strong>'
                        + ' &mdash; outcome: <code>' + $('<span>').text(ev.outcome).html() + '</code>'
                        + (ev.note ? ' &mdash; ' + $('<span>').text(ev.note).html() : '')
                        + '</p>';
                } else {
                    html += '<p class="text-muted" style="font-size:12px; margin-top:6px;"><i class="fa fa-info-circle"></i> No webhook events recorded yet.</p>';
                }
            }
            $('#metaStatusResult').html(html).show();
            },
            error: function(xhr, status, err) {
                var detail = xhr.responseText ? xhr.responseText.substring(0, 300) : err;
                $('#metaStatusResult').html('<div class="alert alert-danger"><strong>Request failed</strong> (HTTP ' + xhr.status + '): <pre style="font-size:11px;white-space:pre-wrap;margin:4px 0 0;">' + $('<span>').text(detail).html() + '</pre></div>').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Check Status');
            }
        });
    });

    // ── Subscribe Page ────────────────────────────────────────────────────
    $('#btnSubscribeMetaPage').on('click', function() {
        if (!confirm('This will call POST /{page_id}/subscribed_apps?subscribed_fields=leadgen using your current Page Access Token. Continue?')) { return; }
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Subscribing...');
        $('#metaSubscribeResult').hide();
        $.post(subscribeUrl, function(r) {
            var cls = (r && r.status === 'success') ? 'success' : 'danger';
            var msg = r ? r.message : 'Request failed';
            $('#metaSubscribeResult').html('<div class="alert alert-' + cls + '">' + $('<span>').text(msg).html() + '</div>').show();
        }, 'json').fail(function() {
            $('#metaSubscribeResult').html('<div class="alert alert-danger">Request failed.</div>').show();
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-link"></i> Subscribe Page to leadgen Webhooks');
        });
    });

    // ── Load Recent Events ────────────────────────────────────────────────
    $('#btnLoadMetaEvents').on('click', function() {
        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        $.getJSON(eventsUrl, function(r) {
            if (!r || r.status !== 'success') {
                $('#metaEventsTable').html('<div class="alert alert-danger">' + (r ? r.message : 'Request failed') + '</div>');
                return;
            }
            if (!r.rows || r.rows.length === 0) {
                $('#metaEventsTable').html('<p class="text-muted">No webhook events recorded yet. Meta has not contacted the webhook URL.</p>');
                return;
            }
            var cols = ['id','received_at','source_ip','signature_status','leadgen_id','page_id','form_id','outcome','enquiry_id','note'];
            var html = '<div class="table-responsive"><table class="table table-condensed table-striped table-bordered" style="font-size:12px;">';
            html += '<thead><tr>';
            cols.forEach(function(c) { html += '<th>' + c + '</th>'; });
            html += '</tr></thead><tbody>';
            r.rows.forEach(function(row) {
                html += '<tr>';
                cols.forEach(function(c) {
                    var val = row[c] !== null && row[c] !== undefined ? row[c] : '';
                    var safe = $('<span>').text(String(val)).html();
                    if (c === 'outcome') {
                        var cls2 = safe === 'created' ? 'success' : (safe === 'pending' ? 'default' : 'danger');
                        safe = '<span class="label label-' + cls2 + '">' + safe + '</span>';
                    }
                    if (c === 'signature_status') {
                        var cls3 = safe === 'ok' ? 'success' : (safe === 'skipped' ? 'default' : 'danger');
                        safe = '<span class="label label-' + cls3 + '">' + safe + '</span>';
                    }
                    html += '<td>' + safe + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';
            $('#metaEventsTable').html(html);
        }).fail(function() {
            $('#metaEventsTable').html('<div class="alert alert-danger">Request failed.</div>');
        }).always(function() {
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Load / Refresh');
        });
    });

})(jQuery);
</script>
