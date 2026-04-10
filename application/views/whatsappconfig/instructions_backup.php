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
                            <li><a href="#section-saas-reseller"><i class="fa fa-building"></i> SaaS Reseller Setup &mdash; One System User for All Clients</a></li>
                            <li><a href="#section-waba-per-client"><i class="fa fa-sitemap"></i> Recommended: Separate WABA Per Client Under Your BM</a></li>
                            <li><a href="#section-client-agreement"><i class="fa fa-file-text-o"></i> Client Authorization Agreement Template</a></li>
                            <li><a href="#section-billing"><i class="fa fa-inr"></i> Meta Billing &mdash; How to Make Meta Invoice You &amp; Bill Your Clients</a></li>
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

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  SAAS RESELLER SETUP                                  -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-success" id="section-saas-reseller">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-building"></i> SaaS Reseller Setup &mdash; One System User Token for All Clients</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-tip">
                            <strong>This is the recommended setup if you are the WhatsApp reseller</strong> &mdash; you pay Meta directly and bill your clients. One permanent token in your server config handles all schools. Clients only give you their Phone Number ID and WABA ID.
                        </div>

                        <div class="instr-section">
                            <h3>How It Works</h3>
                            <table class="instr-table">
                                <tr><th>Item</th><th>Who owns it</th><th>Same for all clients?</th></tr>
                                <tr><td>Access Token (System User)</td><td><strong>You</strong></td><td>&#9989; Yes &mdash; one token for all</td></tr>
                                <tr><td>Phone Number ID</td><td>Client</td><td>&#10060; Unique per client</td></tr>
                                <tr><td>WABA ID</td><td>Client</td><td>&#10060; Unique per client</td></tr>
                                <tr><td>Message Templates</td><td>Client's WABA</td><td>&#10060; Each client submits their own templates</td></tr>
                            </table>
                        </div>

                        <div class="instr-section">
                            <h3>Step R1 &mdash; Create a System User in Your Meta Business Manager</h3>
                            <ol class="steps-ol">
                                <li>Log in to <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a> with your company account (not a client's account).</li>
                                <li>Go to <strong>Business Settings</strong> (gear icon, top-right).</li>
                                <li>In the left sidebar, click <strong>Users &rarr; System Users</strong>.</li>
                                <li>Click <strong>Add</strong>, enter a name (e.g. <em>Minerva WhatsApp Bot</em>), set role to <strong>Admin</strong>, and click <strong>Create System User</strong>.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step R2 &mdash; Generate a Permanent Token for the System User</h3>
                            <ol class="steps-ol">
                                <li>Click the system user you just created &rarr; click <strong>Generate New Token</strong>.</li>
                                <li>Select your Meta App from the dropdown (the one you created in Phase 1).</li>
                                <li>Set token expiry to <strong>Never</strong>.</li>
                                <li>Check these two permissions:
                                    <ul>
                                        <li><code>whatsapp_business_messaging</code></li>
                                        <li><code>whatsapp_business_management</code></li>
                                    </ul>
                                </li>
                                <li>Click <strong>Generate Token</strong> &mdash; <strong>copy it immediately and store it securely</strong>. It is shown only once.</li>
                            </ol>
                            <div class="instr-note">Store this token in your server's environment or a secure config file. In this system, paste it into the <strong>Access Token</strong> field in the WhatsApp Settings page for each client DB (since each client has their own DB, each DB stores the same shared token but their own Phone Number ID).</div>
                        </div>

                        <div class="instr-section">
                            <h3>Step R3 &mdash; What Each Client Must Do (Takes 5 Minutes)</h3>
                            <p>Send this instruction to each school admin:</p>
                            <div class="instr-code">1. Log in to business.facebook.com with your school's Meta Business account.
2. Go to Business Settings &rarr; Accounts &rarr; WhatsApp Accounts.
3. Select your WhatsApp Business Account.
4. Click the "Partners" tab.
5. Click "Add Partner" and enter our Business Manager ID: [YOUR_BM_ID_HERE]
6. Grant permission: "Manage WhatsApp Business Accounts" and "Create and manage WhatsApp templates".
7. Click Save.</div>
                            <div class="instr-tip">After the client adds you as a partner, your System User token has full access to send from their phone number and manage their templates. No token sharing needed.</div>
                        </div>

                        <div class="instr-section">
                            <h3>Step R4 &mdash; Get the Client's Phone Number ID and WABA ID</h3>
                            <p>After the client grants you access, you can find both IDs inside your own Business Manager:</p>
                            <ol class="steps-ol">
                                <li>Go to <strong>Business Settings &rarr; Accounts &rarr; WhatsApp Accounts</strong>.</li>
                                <li>You will now see the client's WABA listed under your account (shared by partner access).</li>
                                <li>Click on their WABA &rarr; note the <strong>WABA ID</strong> shown.</li>
                                <li>Go to <strong>WhatsApp Manager &rarr; Phone Numbers</strong> &rarr; find the client's number &rarr; the <strong>Phone Number ID</strong> is shown there.</li>
                                <li>Alternatively, make this API call to list all phone numbers your System User can access:<br>
                                    <code>GET https://graph.facebook.com/v19.0/{WABA_ID}/phone_numbers?access_token={YOUR_SYSTEM_USER_TOKEN}</code>
                                </li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step R5 &mdash; Configure in This System</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>WhatsApp Settings</strong> in the client's school admin panel.</li>
                                <li>Click the <strong>Meta</strong> tab.</li>
                                <li>Fill in:
                                    <ul>
                                        <li><strong>Access Token</strong> &rarr; your System User permanent token (same for all clients)</li>
                                        <li><strong>Phone Number ID</strong> &rarr; the client's numeric Phone Number ID</li>
                                        <li><strong>WABA ID</strong> &rarr; the client's WABA ID</li>
                                        <li><strong>Language</strong> &rarr; <code>en</code> or <code>en_US</code> (must match template language)</li>
                                        <li><strong>Status</strong> &rarr; Enabled</li>
                                    </ul>
                                </li>
                                <li>Save, then go to each notification type and set the correct template name (template must be approved in the client's WABA).</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Your Business Manager ID</h3>
                            <p>Clients need your Business Manager ID to add you as a partner. Find it at:</p>
                            <div class="instr-code">business.facebook.com &rarr; Business Settings &rarr; Business Info &rarr; Business Manager ID</div>
                            <p>It is a long numeric string like <code>123456789012345</code>. Share this with every client during onboarding.</p>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  SEPARATE WABA PER CLIENT                             -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-success" id="section-waba-per-client">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-sitemap"></i> Recommended: Create a Separate WABA Per Client Under Your Business Manager</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-tip">
                            <strong>Why this is the best approach:</strong> One school getting flagged for spam does not affect any other school. Each client has isolated templates, quality ratings, and analytics. You can transfer the WABA back to the client if they ever leave. You control everything — the client never needs a Meta account.
                        </div>

                        <div class="instr-section">
                            <h3>What "Separate WABA Per Client" Means</h3>
                            <p>A <strong>WhatsApp Business Account (WABA)</strong> is a container that holds phone numbers and message templates. Under your one Meta Business Manager, you can create many WABAs — one per school. All are billed to your payment method.</p>
                            <table class="instr-table">
                                <tr><th>Your Meta Business Manager</th></tr>
                                <tr><td>
                                    <code>WABA: Greenwood School</code> &rarr; Phone: +91 98765 00001 &rarr; Templates: fee_receipt, attendance_alert &hellip;<br>
                                    <code>WABA: Sunrise Academy</code> &rarr; Phone: +91 98765 00002 &rarr; Templates: fee_receipt, attendance_alert &hellip;<br>
                                    <code>WABA: St. Mary&rsquo;s CBSE</code> &rarr; Phone: +91 98765 00003 &rarr; Templates: fee_receipt, attendance_alert &hellip;
                                </td></tr>
                            </table>
                        </div>

                        <div class="instr-section">
                            <h3>Step W1 &mdash; Create a New WABA for the Client</h3>
                            <ol class="steps-ol">
                                <li>Go to <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a> &rarr; <strong>Business Settings</strong>.</li>
                                <li>In the left sidebar, click <strong>Accounts &rarr; WhatsApp Accounts</strong>.</li>
                                <li>Click <strong>Add &rarr; Create a WhatsApp Business Account</strong>.</li>
                                <li>Enter:
                                    <ul>
                                        <li><strong>WhatsApp Business Account Name</strong>: use the school&rsquo;s legal name (e.g. <em>Greenwood School Notifications</em>)</li>
                                        <li><strong>Business</strong>: select your Business Manager</li>
                                        <li><strong>Timezone</strong>: Asia/Kolkata</li>
                                        <li><strong>Currency</strong>: INR</li>
                                    </ul>
                                </li>
                                <li>Click <strong>Create</strong>. The WABA is now inside your BM and billed to your payment method.</li>
                            </ol>
                            <div class="instr-note">The WABA name is only visible inside Meta. Parents never see it. The phone number display name is what parents see in their WhatsApp.</div>
                        </div>

                        <div class="instr-section">
                            <h3>Step W2 &mdash; Add the Client&rsquo;s Phone Number to the WABA</h3>
                            <p>Get the phone number from the client. It <strong>must not</strong> already be active on any WhatsApp account (personal or business). If it is, the client must first delete their WhatsApp account on that number.</p>
                            <ol class="steps-ol">
                                <li>Go to <strong>WhatsApp Manager</strong> (business.facebook.com &rarr; More Tools &rarr; WhatsApp Manager).</li>
                                <li>Select the newly created WABA from the dropdown.</li>
                                <li>Click <strong>Phone Numbers &rarr; Add Phone Number</strong>.</li>
                                <li>Enter the client&rsquo;s phone number and display name (e.g. <em>Greenwood School</em> &mdash; this is what parents see).</li>
                                <li>Choose verification method: <strong>SMS</strong> or <strong>Voice Call</strong>.</li>
                                <li>Have the client read the OTP to you (or have their SIM in hand), enter it &rarr; click <strong>Verify</strong>.</li>
                                <li>The number is now active in your WABA. Note the <strong>Phone Number ID</strong> shown — you will need it for the config.</li>
                            </ol>
                            <div class="instr-warn">
                                <strong>Number already on WhatsApp?</strong> Client must open WhatsApp on their phone &rarr; Settings &rarr; Account &rarr; Delete Account. This removes the number from WhatsApp consumer/business app. Calls and SMS on that number still work normally. After deletion, wait 5 minutes before adding to your WABA.
                            </div>
                        </div>

                        <div class="instr-section">
                            <h3>Step W3 &mdash; Set the Display Name (What Parents See)</h3>
                            <ol class="steps-ol">
                                <li>In WhatsApp Manager &rarr; Phone Numbers, click the number you just added.</li>
                                <li>Click <strong>Profile &rarr; Edit</strong>.</li>
                                <li>Set <strong>Display Name</strong> to the school&rsquo;s name (e.g. <em>Greenwood School</em>).</li>
                                <li>Set the business description, website, address (optional but improves parent trust).</li>
                                <li>Meta reviews the display name — approval usually takes a few hours to 1 day.</li>
                            </ol>
                            <div class="instr-tip">A verified green tick (Official Business Account) can be applied for after the display name is approved and the number has been active for some weeks. This is a separate process under <strong>WhatsApp Manager &rarr; Account &rarr; Request Official Business Account</strong>.</div>
                        </div>

                        <div class="instr-section">
                            <h3>Step W4 &mdash; Assign Your System User to the New WABA</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>Business Settings &rarr; Accounts &rarr; WhatsApp Accounts</strong>.</li>
                                <li>Select the new WABA.</li>
                                <li>Click <strong>Add People</strong>.</li>
                                <li>Add your System User (created in Step R1) with <strong>Full Control</strong>.</li>
                            </ol>
                            <p>Your one System User token now has access to this WABA&rsquo;s phone number. No new token needed.</p>
                        </div>

                        <div class="instr-section">
                            <h3>Step W5 &mdash; Create Message Templates for the Client</h3>
                            <ol class="steps-ol">
                                <li>In WhatsApp Manager, switch to the client&rsquo;s WABA.</li>
                                <li>Go to <strong>Manage &rarr; Message Templates &rarr; Create Template</strong>.</li>
                                <li>Create each notification template (fee receipt, attendance, login credentials, etc.) with the school&rsquo;s branding and language.</li>
                                <li>Templates must be approved by Meta before use — usually 1&ndash;24 hours for utility templates, up to 7 days for marketing.</li>
                                <li>Note the <strong>template name</strong> exactly (case-sensitive) &mdash; this goes in the notification settings inside the school&rsquo;s admin panel.</li>
                            </ol>
                            <div class="instr-note">Templates belong to the WABA, not the phone number. If you ever add a second number for the same school, it reuses the same approved templates.</div>
                        </div>

                        <div class="instr-section">
                            <h3>Step W6 &mdash; Configure in the School&rsquo;s Admin Panel</h3>
                            <ol class="steps-ol">
                                <li>Log in to the school&rsquo;s instance of this system.</li>
                                <li>Go to <strong>System Settings &rarr; WhatsApp Settings &rarr; Meta tab</strong>.</li>
                                <li>Fill in:
                                    <ul>
                                        <li><strong>Access Token</strong>: your permanent System User token (same for all clients)</li>
                                        <li><strong>Phone Number ID</strong>: the numeric Phone Number ID from Step W2</li>
                                        <li><strong>WABA ID</strong>: the WABA ID from WhatsApp Manager &rarr; Account &rarr; Overview</li>
                                        <li><strong>Language</strong>: must match the language of the approved templates (e.g. <code>en</code> or <code>en_US</code>)</li>
                                        <li><strong>Status</strong>: Enabled</li>
                                    </ul>
                                </li>
                                <li>Save, then configure each notification type&rsquo;s template name in the notification settings.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step W7 &mdash; If a Client Leaves (WABA Transfer)</h3>
                            <p>If a school stops using your platform and wants to keep their WhatsApp number independently:</p>
                            <ol class="steps-ol">
                                <li>Go to <strong>Business Settings &rarr; WhatsApp Accounts</strong> &rarr; select their WABA.</li>
                                <li>Click <strong>Settings &rarr; Business Manager</strong> &rarr; <strong>Transfer WhatsApp Business Account</strong>.</li>
                                <li>Enter the client&rsquo;s Business Manager ID (they create a BM at business.facebook.com).</li>
                                <li>The client accepts the transfer in their BM &mdash; the WABA moves to their account with all templates and history intact.</li>
                                <li>Update billing in their BM to their own payment method.</li>
                            </ol>
                            <div class="instr-tip">This clean exit option is a strong selling point when pitching to schools &mdash; &ldquo;your number and templates are yours, we just manage them for you.&rdquo;</div>
                        </div>

                        <div class="instr-section">
                            <h3>Quick Onboarding Checklist Per New Client</h3>
                            <table class="instr-table">
                                <tr><th>#</th><th>Task</th><th>Who</th><th>Time</th></tr>
                                <tr><td>1</td><td>Get signed authorization from school (phone number, school name)</td><td>You</td><td>Day 1</td></tr>
                                <tr><td>2</td><td>Confirm phone number is not on any WhatsApp account; client deletes if needed</td><td>Client</td><td>Day 1</td></tr>
                                <tr><td>3</td><td>Create new WABA in your BM with school name</td><td>You</td><td>5 min</td></tr>
                                <tr><td>4</td><td>Add phone number to WABA, verify via OTP</td><td>You + Client</td><td>10 min</td></tr>
                                <tr><td>5</td><td>Assign System User to WABA with Full Control</td><td>You</td><td>2 min</td></tr>
                                <tr><td>6</td><td>Set display name and business profile</td><td>You</td><td>5 min</td></tr>
                                <tr><td>7</td><td>Create and submit message templates</td><td>You</td><td>30 min</td></tr>
                                <tr><td>8</td><td>Wait for template approval</td><td>Meta</td><td>1&ndash;24 hrs</td></tr>
                                <tr><td>9</td><td>Enter Phone Number ID + WABA ID in school admin panel</td><td>You</td><td>5 min</td></tr>
                                <tr><td>10</td><td>Test with one fee payment or dummy admission</td><td>You</td><td>15 min</td></tr>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  CLIENT AGREEMENT                                     -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-info" id="section-client-agreement">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Client Authorization Agreement Template</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-note">
                            Get this signed (physically or via email confirmation) from each school before registering their phone number. This keeps you covered under Meta&rsquo;s Terms of Service and Indian IT Act requirements.
                        </div>

                        <div class="instr-code" style="font-family:Georgia,serif; font-size:13px; line-height:1.8;">WHATSAPP BUSINESS PLATFORM — AUTHORIZATION LETTER

Date: ___________

To,
[Your Company Name]
[Your Address]

Subject: Authorization to Register Phone Number on WhatsApp Business Platform

We, [School Full Legal Name], a school registered at [School Address],
hereby authorize [Your Company Name] to:

1. Register our phone number +91 __________ on the Meta WhatsApp Business
   Platform on our behalf.

2. Create and manage a WhatsApp Business Account (WABA) under their Meta
   Business Manager for the purpose of sending automated school notifications
   (fee receipts, attendance alerts, exam results, login credentials, etc.)
   to our students and parents.

3. Create, submit, and manage WhatsApp message templates in our WABA.

4. Send WhatsApp messages to our students/parents using this number through
   the school ERP system provided by [Your Company Name].

5. Pay Meta (WhatsApp) directly for messaging costs on our behalf and invoice
   us accordingly as per the agreed service plan.

We understand that:
- Messages will only be sent for legitimate school communication purposes.
- We will inform [Your Company Name] before removing this authorization.
- On termination of services, we may request transfer of the WABA and phone
  number back to our own Meta Business Manager.
- We are responsible for ensuring the phone number provided belongs to or is
  legally held by our institution.

Authorized Signatory:

Name    : ___________________________
Designation: ___________________________
School  : ___________________________
Signature: ___________________________
Date    : ___________________________
School Seal: (affix here)</div>

                        <div class="instr-tip" style="margin-top:12px;">
                            <strong>Tip:</strong> For faster onboarding, send this as a PDF via email and ask the principal to reply confirming approval in writing. An email reply with "confirmed" from the school&rsquo;s official domain is legally equivalent to a signature in most Indian courts under the IT Act 2000.
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  META BILLING                                         -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-success" id="section-billing">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-inr"></i> Meta Billing &mdash; How to Make Meta Invoice You &amp; Bill Your Clients</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-tip">
                            <strong>Goal:</strong> Meta charges YOU (the reseller) for all WhatsApp conversations across all clients. You then charge your clients separately based on your own pricing.
                        </div>

                        <div class="instr-section">
                            <h3>Step B1 &mdash; Set Up a Payment Method in Meta Business Manager</h3>
                            <ol class="steps-ol">
                                <li>Go to <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a> &rarr; <strong>Business Settings &rarr; Billing &amp; Payments</strong>.</li>
                                <li>Click <strong>Add Payment Method</strong>.</li>
                                <li>Add a <strong>credit/debit card or UPI</strong> registered in your company's name. This is what Meta will charge.</li>
                                <li>Set this as the <strong>primary billing account</strong> for your Business Manager.</li>
                            </ol>
                            <div class="instr-note">Meta bills in <strong>USD</strong>. Your bank will convert at prevailing forex rates. GST (18%) is added for Indian accounts — Meta issues a tax invoice you can use for input credit.</div>
                        </div>

                        <div class="instr-section">
                            <h3>Step B2 &mdash; Link Client WABAs to YOUR Billing</h3>
                            <p>This is the critical step. By default, when a client adds you as a partner, their WABA is still billed to <em>their</em> Meta account. To bill it to yourself:</p>
                            <ol class="steps-ol">
                                <li>During or after client onboarding, go to <strong>WhatsApp Manager &rarr; Accounts</strong>.</li>
                                <li>Select the client's WABA.</li>
                                <li>Go to <strong>Settings &rarr; Billing</strong> inside the WABA settings.</li>
                                <li>Change the billing account to <strong>your Business Manager's payment method</strong>. You need <em>Full Control</em> permission on the WABA to do this.</li>
                            </ol>
                            <div class="instr-warn">
                                <strong>Easier Alternative &mdash; Create the WABA yourself on the client's behalf:</strong><br>
                                Instead of letting the client create their own WABA, you create it for them inside your Business Manager (using the embedded signup flow). This way it is <strong>automatically billed to you</strong> from day one. The client gives you permission to use their phone number and business name — you handle the rest.
                            </div>
                        </div>

                        <div class="instr-section">
                            <h3>Step B3 &mdash; How Meta Charges You</h3>
                            <table class="instr-table">
                                <tr><th>Billing Trigger</th><th>Details</th></tr>
                                <tr><td>Per conversation (not per message)</td><td>A conversation = any number of messages within a 24-hour window</td></tr>
                                <tr><td>Charge frequency</td><td>Meta charges monthly (or when you hit a billing threshold)</td></tr>
                                <tr><td>Invoice</td><td>Meta emails a PDF invoice to the email on your Business Manager account each billing cycle</td></tr>
                                <tr><td>Conversation types</td><td>Marketing, Utility, Authentication, Service &mdash; each priced differently. Utility (fee receipts, attendance) is the cheapest.</td></tr>
                                <tr><td>Free tier</td><td>First 1,000 service conversations per month are free per WABA</td></tr>
                            </table>
                        </div>

                        <div class="instr-section">
                            <h3>Step B4 &mdash; Get Meta Invoices</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>Business Manager &rarr; Business Settings &rarr; Billing &amp; Payments</strong>.</li>
                                <li>Click <strong>Payment Activity</strong> to see all charges.</li>
                                <li>Click <strong>Download Invoice</strong> on any charge to get the PDF.</li>
                                <li>For GST (Indian businesses): Meta's invoices show <strong>IGST at 18%</strong>. Meta's GSTIN is <code>09AAHCF8837R1ZJ</code> (Uttar Pradesh), so all Indian accounts are billed as inter-state IGST.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Step B5 &mdash; How to Bill Your Clients</h3>
                            <p>Since each client's WABA is now billed to your payment method, you need to pass the cost on with your margin. Two approaches:</p>

                            <h4>Option A &mdash; Flat monthly fee (simplest)</h4>
                            <p>Charge a fixed monthly amount per school regardless of message volume. Include a fair usage cap (e.g. 500 conversations/month). Clients exceeding the cap pay an overage rate.</p>

                            <h4>Option B &mdash; Pass-through + margin</h4>
                            <p>Use the <code>whatsapp_message_log</code> table in this system to count conversations per client per month, calculate their actual Meta cost, add your margin (e.g. 20%), and invoice accordingly. Steps:</p>
                            <ol class="steps-ol">
                                <li>This system already logs every WhatsApp send in <code>whatsapp_message_log</code> (recipient_count column for bulk sends).</li>
                                <li>At month end, run: <code>SELECT SUM(recipient_count) FROM whatsapp_message_log WHERE month=M AND year=Y</code> to get total messages sent for each client DB.</li>
                                <li>Multiply by approximate Meta conversation cost for your region (India utility rate &asymp; &#8377;0.35&ndash;0.40 per conversation as of 2024).</li>
                                <li>Add your margin and raise a GST invoice to the school using your billing software.</li>
                            </ol>

                            <div class="instr-note">
                                <strong>Note:</strong> <em>Messages &ne; Conversations.</em> Meta charges per conversation window (24 hours), not per message. 10 messages to the same number in one day = 1 conversation charge. For school use (fee receipt, attendance — typically one message per event per parent), messages &asymp; conversations in practice.
                            </div>
                        </div>

                        <div class="instr-section">
                            <h3>Step B6 &mdash; View Per-WABA Usage in Meta</h3>
                            <p>To see exactly how many conversations each client's WABA consumed:</p>
                            <ol class="steps-ol">
                                <li>Go to <strong>WhatsApp Manager</strong> (business.facebook.com &rarr; More Tools &rarr; WhatsApp Manager).</li>
                                <li>Switch to the client's WABA from the account selector.</li>
                                <li>Click <strong>Overview</strong> &rarr; use the date filter to see conversations by type for the billing period.</li>
                                <li>Or use the Analytics API: <code>GET https://graph.facebook.com/v19.0/{WABA_ID}/conversation_analytics?...</code> (this is the planned "Meta Analytics" section in your config page).</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Summary &mdash; Reseller Flow</h3>
                            <table class="instr-table">
                                <tr><th>#</th><th>Who does it</th><th>Action</th></tr>
                                <tr><td>1</td><td>You</td><td>Create System User, generate permanent token, add your payment method to BM</td></tr>
                                <tr><td>2</td><td>Client</td><td>Add your BM as a Partner on their WABA (5 minutes)</td></tr>
                                <tr><td>3</td><td>You</td><td>Change WABA billing to your payment method (or create WABA for them)</td></tr>
                                <tr><td>4</td><td>You</td><td>Enter Phone Number ID + WABA ID in client's school admin &rarr; WhatsApp Settings</td></tr>
                                <tr><td>5</td><td>Client</td><td>Submit message templates in their WhatsApp Manager (or you do it on their behalf)</td></tr>
                                <tr><td>6</td><td>Meta</td><td>Charges your card monthly, emails invoice to your BM email</td></tr>
                                <tr><td>7</td><td>You</td><td>Bill each school based on flat fee or usage from <code>whatsapp_message_log</code></td></tr>
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
