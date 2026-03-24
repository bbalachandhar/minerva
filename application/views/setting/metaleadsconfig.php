<!-- Meta Lead Ads Configuration Page
     URL: schsettings/metaleadsconfig
-->

<div class="content-wrapper">
    <section class="content-header">
        <h1>Meta Lead Ads Integration</h1>
        <ol class="breadcrumb">
            <li><a href="<?= base_url('schsettings/index') ?>"><i class="fa fa-cogs"></i> System Settings</a></li>
            <li class="active">Meta Lead Ads</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">

            <!-- ── Left: Webhook URL Card ─────────────────────────────── -->
            <div class="col-md-5">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-link"></i> Your Webhook URL</h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Copy this URL and paste it in your Meta Business Manager → Webhooks → Page Subscription.</p>
                        <div class="input-group">
                            <input type="text" id="webhookUrlBox" class="form-control" readonly
                                   value="<?= htmlspecialchars($webhook_url, ENT_QUOTES) ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default" onclick="copyWebhookUrl()">
                                    <i class="fa fa-copy"></i> Copy
                                </button>
                            </span>
                        </div>

                        <hr>
                        <h4><i class="fa fa-info-circle text-info"></i> Setup Steps</h4>
                        <ol class="text-muted" style="padding-left:18px; line-height:1.9">
                            <li>Go to <strong>Meta Business Suite</strong> (business.facebook.com).</li>
                            <li>Open your App → <strong>Webhooks</strong> → Add <code>Page</code> subscription.</li>
                            <li>Paste the <strong>Webhook URL</strong> above and the <strong>Verify Token</strong> from the form on the right.</li>
                            <li>Subscribe to the <code>leadgen</code> field.</li>
                            <li>Under <strong>Page Settings → Leads Access</strong>, paste your <strong>Page Access Token</strong> (a long-lived token from Meta's Graph API Explorer).</li>
                            <li>Set <strong>Enable Integration</strong> to ON and click Save.</li>
                            <li>(Optional) Set the <strong>App Secret</strong> for HMAC payload verification — recommended for production.</li>
                        </ol>
                    </div>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-key"></i> Token Tips</h3>
                    </div>
                    <div class="box-body text-muted" style="font-size:13px; line-height:1.8">
                        <p><strong>Verify Token</strong> — any string you choose (or generate one below). It just needs to match between this form and the Meta Webhooks setup screen.</p>
                        <p><strong>Page Access Token</strong> — generate from
                            <a href="https://developers.facebook.com/tools/explorer/" target="_blank">Meta Graph API Explorer</a>
                            with <code>pages_read_engagement</code> + <code>leads_retrieval</code> permissions. Use a <strong>long-lived</strong> token (never expires if app is in Live mode).
                        </p>
                        <p><strong>App Secret</strong> — found in Meta Developer App → Basic Settings → App Secret. Used to verify webhook payload authenticity (HMAC-SHA256).</p>
                    </div>
                </div>
            </div><!-- /col-md-5 -->

            <!-- ── Right: Settings Form ───────────────────────────────── -->
            <div class="col-md-7">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-facebook-square"></i> Meta Lead Ads Settings</h3>
                    </div>
                    <div class="box-body">
                        <form id="metaLeadsForm">
                            <!-- Enable Toggle -->
                            <div class="form-group">
                                <label>Enable Meta Lead Ads Integration</label>
                                <div>
                                    <label class="radio-inline">
                                        <input type="radio" name="meta_leads_enabled" value="1"
                                            <?= (!empty($setting->meta_leads_enabled) && $setting->meta_leads_enabled == 1) ? 'checked' : '' ?>>
                                        <span class="text-success"><strong>Enable</strong></span>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="meta_leads_enabled" value="0"
                                            <?= (empty($setting->meta_leads_enabled) || $setting->meta_leads_enabled == 0) ? 'checked' : '' ?>>
                                        <span class="text-danger"><strong>Disable</strong></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Verify Token -->
                            <div class="form-group">
                                <label for="metaVerifyToken">Verify Token <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="metaVerifyToken"
                                           name="meta_verify_token" placeholder="e.g. mysecrettoken123"
                                           value="<?= htmlspecialchars($setting->meta_verify_token ?? '', ENT_QUOTES) ?>">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" id="generateTokenBtn">
                                            <i class="fa fa-refresh"></i> Generate
                                        </button>
                                    </span>
                                </div>
                                <p class="help-block">Must exactly match the Verify Token you enter in Meta's Webhooks setup.</p>
                            </div>

                            <!-- Page Access Token -->
                            <div class="form-group">
                                <label for="metaPageAccessToken">Page Access Token <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="metaPageAccessToken"
                                          name="meta_page_access_token" rows="4"
                                          placeholder="Paste your long-lived Page Access Token here..."><?= htmlspecialchars($setting->meta_page_access_token ?? '', ENT_QUOTES) ?></textarea>
                                <p class="help-block">Used to fetch lead details from the Meta Graph API after each webhook event.</p>
                            </div>

                            <!-- Page ID -->
                            <div class="form-group">
                                <label for="metaPageId">Facebook Page ID</label>
                                <input type="text" class="form-control" id="metaPageId"
                                       name="meta_page_id" placeholder="e.g. 123456789012345"
                                       value="<?= htmlspecialchars($setting->meta_page_id ?? '', ENT_QUOTES) ?>">
                                <p class="help-block">Your Facebook Page's numeric ID. Found in Page Settings → About.</p>
                            </div>

                            <!-- App Secret -->
                            <div class="form-group">
                                <label for="metaAppSecret">App Secret <small class="text-muted">(recommended)</small></label>
                                <input type="password" class="form-control" id="metaAppSecret"
                                       name="meta_app_secret" placeholder="Leave blank to keep existing value"
                                       autocomplete="new-password">
                                <p class="help-block">From Meta Developer App → Basic Settings. Enables HMAC-SHA256 payload verification. Leave blank to keep the existing value.</p>
                            </div>

                            <!-- Default Course -->
                            <div class="form-group">
                                <label for="metaDefaultCourse">Default Course (if not matched)</label>
                                <select class="form-control" id="metaDefaultCourse" name="meta_default_course_id">
                                    <option value="0">— None —</option>
                                    <?php foreach ($courselist as $course): ?>
                                        <option value="<?= (int) $course->id ?>"
                                            <?= ($setting->meta_default_course_id ?? 0) == $course->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($course->course_name, ENT_QUOTES) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="help-block">If the lead's course cannot be matched automatically, this course is assigned.</p>
                            </div>

                            <!-- Save Button -->
                            <div class="form-group" style="margin-top:20px">
                                <button type="submit" class="btn btn-primary btn-lg" id="saveMetaBtn">
                                    <i class="fa fa-save"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Test Webhook -->
                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-flask"></i> Test + Debug</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Send a simulated GET verification request to confirm the webhook URL responds correctly. Check your server logs (<code>application/logs/</code>) for detailed <code>[MetaLeads]</code> entries.</p>
                        <button class="btn btn-default" id="testVerifyBtn">
                            <i class="fa fa-send"></i> Test Webhook Verification
                        </button>
                        <div id="testResult" style="margin-top:12px"></div>
                    </div>
                </div>
            </div><!-- /col-md-7 -->

        </div><!-- /row -->
    </section>
</div>

<script>
/* ── Copy webhook URL ─────────────────────────────────────────── */
function copyWebhookUrl() {
    var el = document.getElementById('webhookUrlBox');
    el.select();
    document.execCommand('copy');
    toastr && toastr.success('Webhook URL copied!');
}

/* ── Generate verify token ────────────────────────────────────── */
$('#generateTokenBtn').on('click', function () {
    $.ajax({
        url : '<?= base_url('schsettings/ajax_generate_meta_verify_token') ?>',
        type: 'POST',
        data: {<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'},
        success: function (r) {
            if (r.status === 'success') {
                $('#metaVerifyToken').val(r.token);
                toastr && toastr.info('New token generated — remember to copy it to Meta Webhooks!');
            }
        }
    });
});

/* ── Save settings ────────────────────────────────────────────── */
$('#metaLeadsForm').on('submit', function (e) {
    e.preventDefault();
    $('#saveMetaBtn').prop('disabled', true);

    $.ajax({
        url    : '<?= base_url('schsettings/ajax_save_metaleads_config') ?>',
        type   : 'POST',
        data   : $(this).serialize() + '&<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>',
        success: function (r) {
            if (r.status === 'success') {
                toastr ? toastr.success(r.message) : alert(r.message);
            } else {
                toastr ? toastr.error(r.message) : alert('Error: ' + r.message);
            }
        },
        error  : function () { toastr ? toastr.error('Server error.') : alert('Server error.'); },
        complete: function () { $('#saveMetaBtn').prop('disabled', false); }
    });
});

/* ── Test webhook verification ────────────────────────────────── */
$('#testVerifyBtn').on('click', function () {
    var token    = $('#metaVerifyToken').val();
    var baseUrl  = '<?= base_url('metaleads/webhook') ?>';
    var testUrl  = baseUrl + '?hub.mode=subscribe&hub.verify_token=' + encodeURIComponent(token) + '&hub.challenge=TESTCHALLENGE123';

    $('#testResult').html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Sending verification request...</span>');

    $.ajax({
        url    : testUrl,
        type   : 'GET',
        success: function (resp) {
            if (resp === 'TESTCHALLENGE123') {
                $('#testResult').html('<span class="text-success"><i class="fa fa-check-circle"></i> <strong>Verification passed!</strong> The webhook URL is working correctly.</span>');
            } else {
                $('#testResult').html('<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Got response: <code>' + $('<div>').text(resp).html() + '</code></span>');
            }
        },
        error: function (xhr) {
            $('#testResult').html('<span class="text-danger"><i class="fa fa-times-circle"></i> Error ' + xhr.status + ': ' + $('<div>').text(xhr.responseText).html() + '</span>');
        }
    });
});
</script>
