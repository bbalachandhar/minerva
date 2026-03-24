<style>
.instr-section { margin-bottom: 32px; }
.instr-section h3 { border-left: 4px solid #3c8dbc; padding-left: 10px; margin-bottom: 14px; }
.instr-section h4 { margin-top: 18px; margin-bottom: 6px; color: #444; }
.instr-badge { display:inline-block; padding:2px 8px; border-radius:3px; font-size:12px; font-weight:700; margin-right:4px; }
.instr-code { background:#f4f4f4; border:1px solid #ddd; border-radius:3px; padding:10px 14px; font-family:monospace; font-size:13px; white-space:pre-wrap; word-break:break-all; margin:8px 0 14px; }
.instr-table { width:100%; border-collapse:collapse; margin:8px 0 14px; font-size:13px; }
.instr-table th { background:#f0f0f0; text-align:left; padding:7px 10px; border:1px solid #ddd; }
.instr-table td { padding:7px 10px; border:1px solid #ddd; vertical-align:top; }
.instr-note  { background:#fcf8e3; border-left:4px solid #f0ad4e; padding:10px 14px; margin:10px 0; border-radius:3px; }
.instr-tip   { background:#dff0d8; border-left:4px solid #3c763d; padding:10px 14px; margin:10px 0; border-radius:3px; }
.instr-warn  { background:#f2dede; border-left:4px solid #a94442; padding:10px 14px; margin:10px 0; border-radius:3px; }
.steps-ol li { margin-bottom:8px; line-height:1.7; }
.toc-list li { margin-bottom:5px; }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <!-- ── Page header ──────────────────────────────────────── -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-book"></i> Meta WhatsApp Business API &mdash; Integration Guide</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('whatsappconfig/index'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Settings
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Step-by-step guide to connect your Meta WhatsApp Business account to this system for sending automated notifications (fee receipts, attendance alerts, exam results, login credentials, etc.).</p>

                        <div class="instr-note">
                            <strong>Your code is already done.</strong> The WhatsApp messaging library, gateway dispatcher, config controller, and admin UI are all implemented. This guide covers only the <strong>Meta account setup</strong> and <strong>credential configuration</strong> needed to activate it.
                        </div>

                        <ul class="toc-list">
                            <li><a href="#section-overview"><i class="fa fa-th-list"></i> System Overview &amp; Field Reference</a></li>
                            <li><a href="#section-phase1"><i class="fa fa-wrench"></i> Phase 1 &mdash; Meta Developer Setup</a></li>
                            <li><a href="#section-phase2"><i class="fa fa-key"></i> Phase 2 &mdash; Get Your Credentials</a></li>
                            <li><a href="#section-phase3"><i class="fa fa-file-text"></i> Phase 3 &mdash; Create Message Templates</a></li>
                            <li><a href="#section-phase4"><i class="fa fa-cog"></i> Phase 4 &mdash; Configure in Admin Panel</a></li>
                            <li><a href="#section-phase5"><i class="fa fa-list-ol"></i> Phase 5 &mdash; Set Template IDs Per Notification</a></li>
                            <li><a href="#section-phase6"><i class="fa fa-check-circle"></i> Phase 6 &mdash; Test Before Going Live</a></li>
                            <li><a href="#section-pitfalls"><i class="fa fa-exclamation-triangle"></i> Common Pitfalls</a></li>
                            <li><a href="#section-scaling"><i class="fa fa-bar-chart"></i> Scaling Tiers</a></li>
                        </ul>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  SYSTEM OVERVIEW                                      -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-default" id="section-overview">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-th-list"></i> System Overview &amp; Field Reference</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-section">
                            <h3>Implemented Files</h3>
                            <table class="instr-table">
                                <tr><th>File</th><th>Purpose</th></tr>
                                <tr><td><code>application/libraries/Meta_whatsapp.php</code></td><td>Sends template messages via Meta Cloud API (<code>graph.facebook.com/v19.0</code>)</td></tr>
                                <tr><td><code>application/libraries/Whatsappgateway.php</code></td><td>Dispatches to Meta or Twilio based on the active config record</td></tr>
                                <tr><td><code>application/controllers/Whatsappconfig.php</code></td><td>Saves credentials from this admin panel</td></tr>
                                <tr><td><code>application/models/Whatsappconfig_model.php</code></td><td>Reads the <code>whatsapp_config</code> table</td></tr>
                                <tr><td><code>application/views/whatsappconfig/index.php</code></td><td>The settings page you came from</td></tr>
                            </table>
                        </div>

                        <div class="instr-section">
                            <h3>What the <code>whatsapp_config</code> Table Stores for Meta</h3>
                            <table class="instr-table">
                                <tr><th>Column</th><th>Value stored</th></tr>
                                <tr><td><code>type</code></td><td><code>meta</code></td></tr>
                                <tr><td><code>authkey</code></td><td>Your permanent System User Access Token</td></tr>
                                <tr><td><code>contact</code></td><td>Phone Number ID (the numeric Meta internal ID, <strong>not</strong> the actual phone number)</td></tr>
                                <tr><td><code>language</code></td><td>Template language code — e.g. <code>en_US</code>, <code>en</code>, <code>hi</code></td></tr>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  PHASE 1                                              -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-phase1">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-wrench"></i> Phase 1 &mdash; Meta Developer Setup</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-section">
                            <h3>Step 1 &mdash; Create a Meta App</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong><a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com/apps</a></strong> and log in with a Facebook account that has admin rights on your Business Manager.</li>
                                <li>Click <strong>My Apps &rarr; Create App</strong>.</li>
                                <li>Choose <strong>Business</strong> as app type &rarr; click <strong>Next</strong>.</li>
                                <li>Enter an app name (e.g. <em>School Notifications</em>) and a contact email.</li>
                                <li>Click <strong>Create App</strong>.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step 2 &mdash; Add WhatsApp Product</h3>
                            <ol class="steps-ol">
                                <li>In your app dashboard, scroll to <strong>Add a product</strong>.</li>
                                <li>Find <strong>WhatsApp</strong> and click <strong>Set up</strong>.</li>
                                <li>You will be taken to the <strong>WhatsApp &rarr; Getting Started</strong> screen.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step 3 &mdash; Connect a WhatsApp Business Account (WABA)</h3>
                            <ol class="steps-ol">
                                <li>On the Getting Started page, click <strong>Add phone number</strong> or use the embedded sign-up.</li>
                                <li>If you have an existing verified business on <strong>Meta Business Manager</strong>, connect it here. Otherwise, create one at <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a>.</li>
                                <li>The phone number you register <strong>must not</strong> already be active on a personal or business WhatsApp account. You need to either:
                                    <ul>
                                        <li>Use a <strong>new SIM / number</strong> dedicated to notifications, OR</li>
                                        <li><strong>Migrate</strong> an existing number — this disconnects it from the WhatsApp consumer/business app, though the number still works for calls and SMS.</li>
                                    </ul>
                                </li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step 4 &mdash; Verify Your Business</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>Business Manager &rarr; Business Settings &rarr; Security Center &rarr; Start Verification</strong>.</li>
                                <li>Submit your business documents (e.g. registration certificate, utility bill showing business name and address).</li>
                                <li>Verification typically takes <strong>2–7 business days</strong>.</li>
                            </ol>
                            <div class="instr-warn">
                                <strong>Without verification:</strong> Limited to 250 conversations per day. You cannot message users outside the 24-hour session window.<br>
                                <strong>With verification:</strong> Scales to 1,000 &rarr; 10,000 &rarr; 100,000+ conversations per day based on quality rating.
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  PHASE 2                                              -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-phase2">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-key"></i> Phase 2 &mdash; Get Your Credentials</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-section">
                            <h3>Step 5 &mdash; Get the Phone Number ID</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>WhatsApp &rarr; API Setup</strong> in your Meta app dashboard.</li>
                                <li>Under the <strong>From</strong> dropdown, select your registered number.</li>
                                <li>Note the <strong>Phone number ID</strong> displayed below it — a long numeric string like <code>107593658959999</code>.</li>
                                <li>This is what goes in the <strong>"Registered Phone Number"</strong> field in the settings page.</li>
                            </ol>
                            <div class="instr-warn">
                                <strong>&#9888; Important:</strong> The "Registered Phone Number" field on this settings page takes the <strong>Phone Number ID</strong> (a long number like <code>107593658959999</code>), <em>not</em> the actual phone number like <code>+91 98765 43210</code>.
                            </div>
                        </div>

                        <div class="instr-section">
                            <h3>Step 6 &mdash; Create a Permanent Access Token</h3>
                            <p>The temporary token shown on the Getting Started page <strong>expires in 24 hours</strong>. For production you need a permanent System User token:</p>
                            <ol class="steps-ol">
                                <li>Go to <strong>Meta Business Manager &rarr; Business Settings &rarr; Users &rarr; System Users</strong>.</li>
                                <li>Click <strong>Add</strong> &rarr; create a System User, set role to <strong>Admin</strong> &rarr; click <strong>Create System User</strong>.</li>
                                <li>Click <strong>Generate New Token</strong> on the system user.</li>
                                <li>Select your app from the dropdown.</li>
                                <li>Grant these permissions:
                                    <ul>
                                        <li><code>whatsapp_business_messaging</code></li>
                                        <li><code>whatsapp_business_management</code></li>
                                    </ul>
                                </li>
                                <li>Click <strong>Generate Token</strong> — <strong>copy and save it immediately</strong>, it is shown only once.</li>
                                <li>Also assign your WABA to the System User: <strong>Business Settings &rarr; Accounts &rarr; WhatsApp Accounts</strong> &rarr; select your account &rarr; click <strong>Add People</strong> &rarr; add the system user with <strong>Full Control</strong>.</li>
                            </ol>
                            <div class="instr-tip">This token goes in the <strong>"Access Token"</strong> field in the settings page (<code>authkey</code> column in the database).</div>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  PHASE 3                                              -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-phase3">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text"></i> Phase 3 &mdash; Create Message Templates</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-note">
                            <strong>This is mandatory.</strong> Meta's API only allows sending pre-approved template messages. You cannot send free-form / custom text to users.
                        </div>

                        <div class="instr-section">
                            <h3>Step 7 &mdash; Create Templates in WhatsApp Business Manager</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>Meta Business Manager &rarr; WhatsApp Manager &rarr; Manage &rarr; Message Templates</strong>.</li>
                                <li>Click <strong>Create Template</strong> and fill in:</li>
                            </ol>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Guidance</th></tr>
                                <tr><td><strong>Category</strong></td><td><code>UTILITY</code> for transactional messages (fees, attendance, results). <code>MARKETING</code> for promotions.</td></tr>
                                <tr>
                                    <td><strong>Name</strong></td>
                                    <td>
                                        Lowercase letters and underscores only. E.g. <code>fee_receipt_notification</code>.<br>
                                        <small class="text-muted">This exact name goes in the <strong>WhatsApp Template ID</strong> field in your system's notification settings.</small>
                                    </td>
                                </tr>
                                <tr><td><strong>Language</strong></td><td>Must match the language code you enter in the admin panel (e.g. <code>en_US</code>, <code>en</code>, <code>hi</code>).</td></tr>
                            </table>
                            <ol class="steps-ol" start="3">
                                <li>Write the message body using <strong>numbered variables</strong>: <code>{{1}}</code>, <code>{{2}}</code>, etc.</li>
                            </ol>
                            <p><strong>Example — fee receipt:</strong></p>
                            <div class="instr-code">Dear {{1}}, your fee payment of {{2}} for Invoice No. {{3}} has been successfully received. Thank you.</div>
                            <p><strong>Example — attendance absent:</strong></p>
                            <div class="instr-code">Dear {{1}}, your child {{2}} was marked absent on {{3}} for class {{4}}. Please contact the school for details.</div>
                            <ol class="steps-ol" start="4">
                                <li>Click <strong>Submit</strong>. Approvals usually take a few minutes to 24 hours.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Template Variable Mapping</h3>
                            <p>Your system stores template bodies with <code>{{variable_name}}</code> placeholders. The gateway extracts the matching values and builds the <code>components[].parameters[]</code> array in order. The <strong>order</strong> of parameters passed maps to <code>{{1}}</code>, <code>{{2}}</code>, etc. in the Meta template — so ensure the template in Meta Business Manager has the same variable order as in your system template.</p>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  PHASE 4                                              -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-phase4">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cog"></i> Phase 4 &mdash; Configure in Admin Panel</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-section">
                            <h3>Step 8 &mdash; Enter Credentials</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong><a href="<?php echo site_url('whatsappconfig/index'); ?>">System Settings &rarr; WhatsApp Messaging Settings</a></strong>.</li>
                                <li>Click the <strong>Meta WhatsApp Official</strong> tab.</li>
                                <li>Enter the following:</li>
                            </ol>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Value to enter</th></tr>
                                <tr><td><strong>Access Token</strong></td><td>The permanent System User token from Step 6</td></tr>
                                <tr><td><strong>Registered Phone Number</strong></td><td>The Phone Number ID (numeric) from Step 5 — e.g. <code>107593658959999</code></td></tr>
                                <tr><td><strong>Language</strong></td><td>Language code matching your templates — e.g. <code>en_US</code>, <code>en</code>, <code>hi</code></td></tr>
                                <tr><td><strong>Status</strong></td><td>Set to <strong>Enabled</strong></td></tr>
                            </table>
                            <ol class="steps-ol" start="4">
                                <li>Click <strong>Save</strong>.</li>
                            </ol>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  PHASE 5                                              -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-phase5">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list-ol"></i> Phase 5 &mdash; Set Template IDs Per Notification Type</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-section">
                            <h3>Step 9 &mdash; Map Templates to Notification Events</h3>
                            <p>Each notification event in your system (fee receipt, attendance alert, exam results, login credentials, etc.) has a <strong>WhatsApp Template ID</strong> field. Enter the <strong>exact template name</strong> approved in Meta Business Manager.</p>
                            <div class="instr-warn">
                                Template names are <strong>case-sensitive</strong> and must match exactly (e.g. <code>fee_receipt_notification</code>, not <code>Fee_Receipt_Notification</code>).
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  PHASE 6                                              -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-phase6">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-check-circle"></i> Phase 6 &mdash; Test Before Going Live</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-section">
                            <h3>Step 10 &mdash; Test with Allowed Numbers (Development Phase)</h3>
                            <ol class="steps-ol">
                                <li>In <strong>WhatsApp &rarr; API Setup</strong>, under <strong>To</strong>, click <strong>Manage phone number list</strong>.</li>
                                <li>Add up to 5 personal/test phone numbers as recipients — this bypasses template approval during development.</li>
                                <li>Trigger a notification from your system (e.g. submit a test fee payment) and verify delivery.</li>
                                <li>Check <code>application/logs/log-YYYY-MM-DD.php</code> for any API errors — the library logs HTTP errors and cURL failures automatically.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step 11 &mdash; Manual API Test (Verify Credentials Independently)</h3>
                            <p>Run this from your server terminal to confirm your credentials work before testing through the app:</p>
                            <div class="instr-code">curl -X POST \
  "https://graph.facebook.com/v19.0/YOUR_PHONE_NUMBER_ID/messages" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "919848012345",
    "type": "template",
    "template": {
      "name": "your_template_name",
      "language": {"code": "en_US"},
      "components": [
        {
          "type": "body",
          "parameters": [
            {"type": "text", "text": "Test Name"},
            {"type": "text", "text": "5000"},
            {"type": "text", "text": "INV-001"}
          ]
        }
      ]
    }
  }'</div>
                            <p><strong>Successful response:</strong></p>
                            <div class="instr-code">{"messages":[{"id":"wamid.HBgLMTIzNDU2Nzg5..."}]}</div>
                            <p><strong>Failed response (wrong token example):</strong></p>
                            <div class="instr-code">{"error":{"message":"Invalid OAuth access token","type":"OAuthException","code":190}}</div>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  COMMON PITFALLS                                      -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-danger" id="section-pitfalls">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Common Pitfalls</h3>
                    </div>
                    <div class="box-body">
                        <table class="instr-table">
                            <tr><th>Pitfall</th><th>Solution</th></tr>
                            <tr><td>Using the 24-hour temporary token</td><td>Always use the permanent System User token (Step 6)</td></tr>
                            <tr><td>Entering the actual phone number instead of Phone Number ID</td><td>The "Registered Phone Number" field takes the numeric ID (e.g. <code>107593658959999</code>), not <code>+91 98765 43210</code></td></tr>
                            <tr><td>Template name mismatch</td><td>Must match exactly (case-sensitive) what is approved in WhatsApp Manager</td></tr>
                            <tr><td>Sending to non-opted-in users</td><td>Meta policy requires recipients to have opted in to receive messages from your business</td></tr>
                            <tr><td>Template not yet approved</td><td>Check WhatsApp Manager &rarr; Message Templates for status</td></tr>
                            <tr><td>Messages failing silently</td><td>Check <code>application/logs/log-YYYY-MM-DD.php</code> — the library logs all Meta API errors</td></tr>
                            <tr><td>Business not verified</td><td>Go to Business Manager &rarr; Business Settings &rarr; Security Center to start verification</td></tr>
                        </table>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  SCALING TIERS                                        -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-info" id="section-scaling">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Scaling Tiers (after Business Verification)</h3>
                    </div>
                    <div class="box-body">
                        <table class="instr-table">
                            <tr><th>Tier</th><th>Conversations / Day</th><th>Requirement</th></tr>
                            <tr><td>Unverified</td><td>250</td><td>None</td></tr>
                            <tr><td>Tier 1</td><td>1,000</td><td>Business verified + good quality rating</td></tr>
                            <tr><td>Tier 2</td><td>10,000</td><td>Sustained usage + high quality rating</td></tr>
                            <tr><td>Tier 3</td><td>100,000+</td><td>Sustained usage + high quality rating</td></tr>
                        </table>

                        <div class="instr-section" style="margin-top:20px;">
                            <h3>Quick Reference — Where to Find Things in Meta</h3>
                            <table class="instr-table">
                                <tr><th>What you need</th><th>Where to find it</th></tr>
                                <tr><td>Phone Number ID</td><td>App Dashboard &rarr; WhatsApp &rarr; API Setup &rarr; From dropdown</td></tr>
                                <tr><td>Permanent Access Token</td><td>Business Manager &rarr; System Users &rarr; Generate Token</td></tr>
                                <tr><td>Template status</td><td>Business Manager &rarr; WhatsApp Manager &rarr; Manage &rarr; Message Templates</td></tr>
                                <tr><td>Business verification</td><td>Business Manager &rarr; Business Settings &rarr; Security Center</td></tr>
                                <tr><td>Conversation usage / billing</td><td>Business Manager &rarr; WhatsApp Manager &rarr; Overview</td></tr>
                            </table>
                        </div>

                    </div>
                </div>

                <div style="text-align:center; margin-bottom:20px;">
                    <a href="<?php echo site_url('whatsappconfig/index'); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Settings
                    </a>
                </div>

            </div>
        </div>
    </section>
</div>
