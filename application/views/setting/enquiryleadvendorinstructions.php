<style>
.instr-section { margin-bottom: 32px; }
.instr-section h3 { border-left: 4px solid #3c8dbc; padding-left: 10px; margin-bottom: 14px; }
.instr-section h4 { margin-top: 18px; margin-bottom: 6px; color: #444; }
.instr-badge { display:inline-block; padding:2px 8px; border-radius:3px; font-size:12px; font-weight:700; margin-right:4px; }
.instr-get  { background:#61affe; color:#fff; }
.instr-post { background:#49cc90; color:#fff; }
.instr-code { background:#f4f4f4; border:1px solid #ddd; border-radius:3px; padding:10px 14px; font-family:monospace; font-size:13px; white-space:pre-wrap; word-break:break-all; margin:8px 0 14px; }
.instr-table { width:100%; border-collapse:collapse; margin:8px 0 14px; font-size:13px; }
.instr-table th { background:#f0f0f0; text-align:left; padding:7px 10px; border:1px solid #ddd; }
.instr-table td { padding:7px 10px; border:1px solid #ddd; vertical-align:top; }
.instr-note  { background:#fcf8e3; border-left:4px solid #f0ad4e; padding:10px 14px; margin:10px 0; border-radius:3px; }
.instr-tip   { background:#dff0d8; border-left:4px solid #3c763d; padding:10px 14px; margin:10px 0; border-radius:3px; }
.instr-warn  { background:#f2dede; border-left:4px solid #a94442; padding:10px 14px; margin:10px 0; border-radius:3px; }
.steps-ol li { margin-bottom:8px; line-height:1.7; }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">

                <!-- ── Page header ──────────────────────────────────────── -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-book"></i> Lead Integration — Full Setup Guide</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('schsettings/enquiryleadvendors'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Vendors
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">This page documents both integration methods supported by the system. Use the table of contents to jump to the relevant section.</p>
                        <ul>
                            <li><a href="#section-metaleads"><i class="fa fa-facebook-square"></i> Section 1 — Meta Lead Ads (Facebook / Instagram)</a></li>
                            <li><a href="#section-apivendors"><i class="fa fa-plug"></i> Section 2 — Enquiry Lead Gen Vendor API (Shiksha, Career360, etc.)</a></li>
                        </ul>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  SECTION 1 — META LEAD ADS                           -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-warning" id="section-metaleads">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-facebook-square"></i> Section 1 &mdash; Meta Lead Ads Integration</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-note">
                            <strong>How it works:</strong> When a user fills a Meta Lead Ad form on Facebook or Instagram, Meta sends a webhook POST to your server containing a <code>leadgen_id</code>. Your server then calls the Meta Graph API to fetch the actual form field values and creates an Enquiry record automatically.
                        </div>

                        <!-- Step 1: Create Meta App -->
                        <div class="instr-section">
                            <h3>Step 1 &mdash; Create / Open Your Meta App</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong><a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com/apps</a></strong> and log in with the account that manages your Facebook Page.</li>
                                <li>Click <strong>Create App</strong> &rarr; choose type <strong>"Other"</strong> &rarr; <strong>"Business"</strong>.</li>
                                <li>Give it a name (e.g. <em>Minerva Lead Sync</em>) and link it to your Business Manager account.</li>
                                <li>After creation, go to your app dashboard and note the <strong>App ID</strong> and <strong>App Secret</strong> (Settings &rarr; Basic).</li>
                            </ol>
                            <div class="instr-tip">If you already have an app that manages your page, you can use it directly — just add the <strong>Webhooks</strong> product if it isn't already there.</div>
                        </div>

                        <!-- Step 2: Add Webhooks product -->
                        <div class="instr-section">
                            <h3>Step 2 &mdash; Configure the Webhook</h3>
                            <ol class="steps-ol">
                                <li>In your app dashboard, click <strong>Add Product</strong> &rarr; find <strong>Webhooks</strong> &rarr; Set Up.</li>
                                <li>In the Webhooks panel, select object type <strong>Page</strong> from the dropdown.</li>
                                <li>Click <strong>Subscribe to this object</strong>. A dialog appears asking for:</li>
                            </ol>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Value</th></tr>
                                <tr><td><strong>Callback URL</strong></td><td>Your public server URL + <code>/minerva/metaleads/webhook</code><br><small class="text-muted">e.g. <code>https://yourdomain.com/minerva/metaleads/webhook</code></small></td></tr>
                                <tr><td><strong>Verify Token</strong></td><td>The token you set in <em>System Settings &rarr; Enquiry Lead Vendors &rarr; Meta Lead Ads</em> (use the Generate button there)</td></tr>
                            </table>
                            <ol class="steps-ol" start="4">
                                <li>Click <strong>Verify and Save</strong>. Meta will make a GET request to your URL — the system will respond with the challenge automatically if the token matches.</li>
                                <li>After saving, find the <strong><code>leadgen</code></strong> field in the subscription list and tick <strong>Subscribe</strong>.</li>
                            </ol>
                            <div class="instr-warn">
                                <strong>Important:</strong> Your server must be publicly accessible over HTTPS for Meta to reach it. Localhost URLs will not work for production — use a live server or a tunnel tool like <a href="https://ngrok.com" target="_blank">ngrok</a> for testing.
                            </div>
                        </div>

                        <!-- Step 3: Page Access Token -->
                        <div class="instr-section">
                            <h3>Step 3 &mdash; Get a Page Access Token</h3>
                            <ol class="steps-ol">
                                <li>Open the <strong><a href="https://developers.facebook.com/tools/explorer/" target="_blank">Meta Graph API Explorer</a></strong>.</li>
                                <li>Select your <strong>app</strong> from the top-right dropdown.</li>
                                <li>Click <strong>Generate Access Token</strong> &rarr; choose your <strong>Facebook Page</strong>.</li>
                                <li>Grant these permissions when prompted:
                                    <ul>
                                        <li><code>pages_show_list</code></li>
                                        <li><code>pages_read_engagement</code></li>
                                        <li><code>leads_retrieval</code></li>
                                    </ul>
                                </li>
                                <li>Copy the generated token. This is your <strong>Page Access Token</strong>.</li>
                                <li>To make it <strong>long-lived</strong> (never expiring in Live mode), exchange it via:<br>
                                    <div class="instr-code">GET https://graph.facebook.com/v19.0/oauth/access_token
  ?grant_type=fb_exchange_token
  &client_id={APP_ID}
  &client_secret={APP_SECRET}
  &fb_exchange_token={SHORT_LIVED_PAGE_TOKEN}</div>
                                </li>
                                <li>Paste the final long-lived token into <em>System Settings &rarr; Enquiry Lead Vendors &rarr; Meta Lead Ads &rarr; Page Access Token</em>.</li>
                            </ol>
                        </div>

                        <!-- Step 4: Leads Access -->
                        <div class="instr-section">
                            <h3>Step 4 &mdash; Grant Leads Access to Your App</h3>
                            <ol class="steps-ol">
                                <li>Go to your <strong>Facebook Page</strong>.</li>
                                <li>Click <strong>Settings</strong> &rarr; <strong>Instant Forms</strong> (or search for "Leads Access" in settings).</li>
                                <li>Under <strong>CRM Setup / Leads Access</strong>, find your app name and click <strong>Give Access</strong>.</li>
                            </ol>
                            <div class="instr-tip">Without this step, the Graph API call to fetch lead details will return a permissions error even if your access token is valid.</div>
                        </div>

                        <!-- Step 5: Save settings -->
                        <div class="instr-section">
                            <h3>Step 5 &mdash; Save Settings in Admin Panel</h3>
                            <p>Go to <strong>System Settings &rarr; Enquiry Lead Vendors</strong> and scroll to the <strong>Meta Lead Ads Integration</strong> section. Fill in:</p>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Description</th></tr>
                                <tr><td>Integration Status</td><td>Set to <strong>Enabled</strong></td></tr>
                                <tr><td>Verify Token</td><td>Same token you entered in Meta Webhooks (use Generate button)</td></tr>
                                <tr><td>Page Access Token</td><td>The long-lived token from Step 3</td></tr>
                                <tr><td>Facebook Page ID</td><td>Numeric ID — found in your Page's About section or URL</td></tr>
                                <tr><td>App Secret</td><td>(Recommended) From Meta App &rarr; Settings &rarr; Basic &rarr; App Secret. Enables payload signature verification.</td></tr>
                                <tr><td>Default Course</td><td>Fallback course assigned to the enquiry if no course can be matched from the lead form data</td></tr>
                            </table>
                            <p>Click <strong>Save Meta Settings</strong>.</p>
                        </div>

                        <!-- Step 6: Switch to Live Mode -->
                        <div class="instr-section">
                            <h3>Step 6 &mdash; Switch App to Live Mode</h3>
                            <ol class="steps-ol">
                                <li>In your Meta App dashboard, toggle the mode from <strong>Development</strong> to <strong>Live</strong> (top of the page).</li>
                                <li>In Development mode, only app admins and testers receive real webhook events. Live mode is required for all page followers to trigger webhooks.</li>
                                <li>Meta may ask you to confirm your privacy policy URL when switching to Live mode.</li>
                            </ol>
                        </div>

                        <!-- How leads are created -->
                        <div class="instr-section">
                            <h3>How Enquiry Records Are Created</h3>
                            <table class="instr-table">
                                <tr><th>Meta Lead Field</th><th>Enquiry Field</th><th>Notes</th></tr>
                                <tr><td><code>full_name</code> / <code>first_name</code> + <code>last_name</code></td><td>Name</td><td></td></tr>
                                <tr><td><code>phone_number</code> / <code>mobile</code></td><td>Contact</td><td>Non-digits stripped</td></tr>
                                <tr><td><code>email</code></td><td>Email</td><td></td></tr>
                                <tr><td><code>city</code></td><td>City</td><td></td></tr>
                                <tr><td><code>state</code> / <code>province</code></td><td>State</td><td></td></tr>
                                <tr><td><code>course</code> / <code>program</code> / <code>stream</code></td><td>Course (matched by name)</td><td>Falls back to Default Course</td></tr>
                                <tr><td><em>all fields</em></td><td>Note</td><td>All raw Meta field values stored in the note</td></tr>
                                <tr><td>&mdash;</td><td>Source</td><td>Always set to <code>API - META</code></td></tr>
                                <tr><td>&mdash;</td><td>Ref No</td><td>Format: <code>ENQ-META-YYYYMMDDHHmmss###</code></td></tr>
                            </table>
                        </div>

                        <!-- Troubleshooting -->
                        <div class="instr-section">
                            <h3>Troubleshooting</h3>
                            <table class="instr-table">
                                <tr><th>Problem</th><th>Likely Cause &amp; Fix</th></tr>
                                <tr><td>Webhook verification fails</td><td>Verify Token mismatch — check that the token in this admin panel exactly matches what you typed in Meta's Webhooks setup screen.</td></tr>
                                <tr><td>Lead received but no enquiry created</td><td>Check <code>application/logs/log-YYYY-MM-DD.php</code> for <code>[MetaLeads]</code> entries. Common causes: empty Page Access Token, leads_retrieval permission missing.</td></tr>
                                <tr><td>Graph API error 200 or 190</td><td>Access token expired or lacks permission. Regenerate a long-lived page token.</td></tr>
                                <tr><td>Webhook events not arriving</td><td>App is still in Development mode or <code>leadgen</code> field is not subscribed.</td></tr>
                                <tr><td>Duplicate enquiry entries</td><td>If a lead is resent by Meta (retry), the system checks for duplicate contact. Ensure your enquiry table has existing records before re-testing.</td></tr>
                            </table>
                        </div>

                    </div>
                </div>
                <!-- / Section 1 -->

                <!-- ══════════════════════════════════════════════════════ -->
                <!--  SECTION 2 — VENDOR API (Shiksha / Career360 etc.)   -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="box box-primary" id="section-apivendors" style="margin-top:8px;">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plug"></i> Section 2 &mdash; Enquiry Lead Gen Vendor API</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-note">
                            <strong>How it works:</strong> Third-party portals like Shiksha and Career360 POST lead data directly to your server endpoint using a shared <code>vendor_code</code> and <code>api_key</code>. The system validates credentials and creates an Enquiry record automatically.
                        </div>

                        <!-- 1. Endpoint -->
                        <div class="instr-section">
                            <h3>1) Endpoint</h3>
                            <p>
                                <span class="instr-badge instr-post">POST</span>
                                <code><?php echo site_url('lead/enquiry'); ?></code>
                            </p>
                            <table class="instr-table" style="width:auto;">
                                <tr><th>Header</th><th>Value</th></tr>
                                <tr><td><code>Content-Type</code></td><td><code>application/json</code></td></tr>
                            </table>
                            <div class="instr-tip">This URL is an alias for <code><?php echo site_url('leadapi/create_enquiry'); ?></code> — both work interchangeably.</div>
                        </div>

                        <!-- 2. Authentication -->
                        <div class="instr-section">
                            <h3>2) Authentication</h3>
                            <p>Credentials can be sent via <strong>headers (recommended)</strong> or as <strong>body fields</strong>.</p>

                            <h4>Header-based auth (recommended)</h4>
                            <table class="instr-table">
                                <tr><th>Header</th><th>Example value</th></tr>
                                <tr><td><code>X-Vendor-Code</code></td><td><code>shiksha</code></td></tr>
                                <tr><td><code>X-Api-Key</code></td><td><code>&lt;vendor-secret&gt;</code></td></tr>
                            </table>

                            <h4>Body-based auth (alternative)</h4>
                            <div class="instr-code"><?php echo htmlspecialchars('{
  "vendor_code": "shiksha",
  "api_key": "<vendor-secret>",
  "param": { ... }
}', ENT_QUOTES); ?></div>
                        </div>

                        <!-- 3. Request Payload -->
                        <div class="instr-section">
                            <h3>3) Request Payload</h3>
                            <p>The API accepts lead data wrapped inside a <code>param</code> object. The top-level <code>id</code> field is optional and used as a vendor-side transaction reference.</p>
                            <div class="instr-code"><?php echo htmlspecialchars('{
  "id": "SIKSHA",
  "param": {
    "applicantname": "John Doe",
    "mobilenumber": "9876543210",
    "emailid": "john.doe@example.com",
    "programid": "10",
    "programname": "B.TECH INFORMATION TECHNOLOGY",
    "applicationcategory": "UG",
    "applicationcategoryid": "1",
    "campusname": "Main Campus",
    "campusid": "1",
    "city": "Salem",
    "state": "Tamil Nadu",
    "address": "Optional",
    "referencecontact": "Optional"
  }
}', ENT_QUOTES); ?></div>

                            <h4>Mandatory fields</h4>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Type</th><th>Maps to</th></tr>
                                <tr><td><code>param.applicantname</code></td><td>string</td><td>Enquiry &rarr; Name</td></tr>
                                <tr><td><code>param.mobilenumber</code></td><td>string</td><td>Enquiry &rarr; Contact</td></tr>
                            </table>

                            <h4>Recommended fields</h4>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Type</th><th>Notes</th></tr>
                                <tr><td><code>param.emailid</code></td><td>string</td><td>Enquiry &rarr; Email</td></tr>
                                <tr><td><code>param.programid</code></td><td>string / int</td><td>Preferred exact course mapping (see Course Mapping table below)</td></tr>
                                <tr><td><code>param.programname</code></td><td>string</td><td>Fallback course matching if <code>programid</code> is absent or unrecognised</td></tr>
                            </table>

                            <h4>Optional fields</h4>
                            <table class="instr-table">
                                <tr><th>Field</th><th>Notes</th></tr>
                                <tr><td><code>id</code></td><td>Vendor-side transaction reference. Stored as <code>vendor_request_id</code>. Pass a unique per-lead value for correlation, a static label like <code>"SIKSHA"</code>, or omit entirely — all are accepted.</td></tr>
                                <tr><td><code>param.city</code></td><td>Enquiry &rarr; City</td></tr>
                                <tr><td><code>param.state</code></td><td>Enquiry &rarr; State</td></tr>
                                <tr><td><code>param.address</code></td><td>Enquiry &rarr; Address</td></tr>
                                <tr><td><code>param.applicationcategory</code></td><td>e.g. <code>UG</code> / <code>PG</code> — informational</td></tr>
                                <tr><td><code>param.referencecontact</code></td><td>Stored in enquiry notes</td></tr>
                                <tr><td><code>param.campusname</code></td><td>Stored in enquiry notes</td></tr>
                            </table>
                        </div>

                        <!-- 4. Duplicate Behavior -->
                        <div class="instr-section">
                            <h3>4) Duplicate Behaviour</h3>
                            <p>The API <strong>always creates a new enquiry record</strong>, even when a duplicate is detected by <code>mobilenumber</code> or <code>emailid</code>. The response will include duplicate details alongside the new record's ID.</p>
                            <table class="instr-table">
                                <tr><th>Response field</th><th>Meaning</th></tr>
                                <tr><td><code>duplicate</code></td><td><code>0</code> = no duplicates, <code>1</code> = duplicates found</td></tr>
                                <tr><td><code>duplicate_count</code></td><td>Number of matching existing records</td></tr>
                                <tr><td><code>duplicate_source_vendor_id</code></td><td>Vendor ID of the original matching record</td></tr>
                                <tr><td><code>duplicate_source_vendor_name</code></td><td>Vendor name of the original matching record</td></tr>
                                <tr><td><code>existing_duplicates[]</code></td><td>Array of matched records with id, name, contact, date, ref_no, matched_by fields</td></tr>
                            </table>
                        </div>

                        <!-- 5. Response Samples -->
                        <div class="instr-section">
                            <h3>5) Response Samples</h3>

                            <h4>5.1 &mdash; Success, no duplicates <span class="label label-success">HTTP 201</span></h4>
                            <div class="instr-code"><?php echo htmlspecialchars('{
  "status": 1,
  "message": "Enquiry created successfully.",
  "duplicate": 0,
  "duplicate_count": 0,
  "existing_duplicates": [],
  "data": {
    "enquiry_id": 1001,
    "ref_no": "ENQ-API-20260313143000123"
  }
}', ENT_QUOTES); ?></div>

                            <h4>5.2 &mdash; Success, duplicates found <span class="label label-warning">HTTP 201</span></h4>
                            <div class="instr-code"><?php echo htmlspecialchars('{
  "status": 1,
  "message": "Enquiry created successfully. Duplicate entries found.",
  "duplicate": 1,
  "duplicate_count": 2,
  "duplicate_source_vendor_id": 1,
  "duplicate_source_vendor_name": "Vendor A",
  "existing_duplicates": [
    {
      "id": 912,
      "name": "John Doe",
      "contact": "9876543210",
      "email": "john.doe@example.com",
      "date": "2026-03-12",
      "ref_no": "ENQ-API-20260312153000111",
      "lead_vendor_id": 1,
      "lead_vendor_name": "Vendor A",
      "matched_by": ["mobilenumber", "emailid"]
    }
  ],
  "data": {
    "enquiry_id": 1002,
    "ref_no": "ENQ-API-20260313143000999"
  }
}', ENT_QUOTES); ?></div>

                            <h4>5.3 &mdash; Auth failure <span class="label label-danger">HTTP 401</span></h4>
                            <div class="instr-code"><?php echo htmlspecialchars('{
  "status": 0,
  "message": "Unauthorized. Invalid api_key."
}', ENT_QUOTES); ?></div>

                            <h4>5.4 &mdash; Validation failure <span class="label label-warning">HTTP 422</span></h4>
                            <div class="instr-code"><?php echo htmlspecialchars('{
  "status": 0,
  "message": "Validation failed.",
  "errors": {
    "applicantname": "applicantname is required.",
    "mobilenumber": "mobilenumber is required."
  }
}', ENT_QUOTES); ?></div>
                        </div>

                        <!-- 6. Course Mapping Master -->
                        <div class="instr-section">
                            <h3>6) Program / Course Mapping Master</h3>
                            <p>Use <code>programid</code> from this list for accurate course mapping. If <code>programid</code> is not supplied, the system falls back to fuzzy matching on <code>programname</code>.</p>
                            <table class="instr-table">
                                <tr>
                                    <th>Course&nbsp;ID</th>
                                    <th>Course Name</th>
                                    <th>Level</th>
                                    <th>Admission Type</th>
                                    <th>Active</th>
                                </tr>
                                <tr><td>17</td><td>M.ARCH</td><td>PG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>15</td><td>M.E APPLIED ELECTRONICS</td><td>PG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>14</td><td>M.E COMPUTER SCIENCE AND ENGINEERING</td><td>PG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>16</td><td>M.E POWER ELECTRONICS AND DRIVES</td><td>PG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>18</td><td>MBA</td><td>PG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>19</td><td>MCA</td><td>PG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>12</td><td>B.ARCH</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>2</td><td>B.E ARTIFICIAL INTELLIGENCE AND MACHINE LEARNING (AIML)</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>9</td><td>B.E CIVIL ENGINEERING</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>1</td><td>B.E COMPUTER SCIENCE AND ENGINEERING</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>6</td><td>B.E CYBER SECURITY</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>8</td><td>B.E ELECTRICAL AND ELECTRONICS ENGINEERING</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>11</td><td>B.E ELECTRICAL AND INSTRUMENTATION ENGINEERING</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>7</td><td>B.E ELECTRONICS AND COMMUNICATION ENGINEERING</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>10</td><td>B.E MECHANICAL ENGINEERING</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>3</td><td>B.TECH ARTIFICIAL INTELLIGENCE AND DATA SCIENCE (AIDS)</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>4</td><td>B.TECH COMPUTER SCIENCE AND BUSINESS SYSTEM</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>5</td><td>B.TECH INFORMATION TECHNOLOGY</td><td>UG</td><td>First Year</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>30</td><td>B.ARCH</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>21</td><td>B.E ARTIFICIAL INTELLIGENCE AND MACHINE LEARNING (AIML)</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>27</td><td>B.E CIVIL ENGINEERING</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>20</td><td>B.E COMPUTER SCIENCE AND ENGINEERING</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>26</td><td>B.E ELECTRICAL AND ELECTRONICS ENGINEERING</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>29</td><td>B.E ELECTRICAL AND INSTRUMENTATION ENGINEERING</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>25</td><td>B.E ELECTRONICS AND COMMUNICATION ENGINEERING</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>28</td><td>B.E MECHANICAL ENGINEERING</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>22</td><td>B.TECH ARTIFICIAL INTELLIGENCE AND DATA SCIENCE (AIDS)</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>23</td><td>B.TECH COMPUTER SCIENCE AND BUSINESS SYSTEM</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                                <tr><td>24</td><td>B.TECH INFORMATION TECHNOLOGY</td><td>UG</td><td>Lateral</td><td><span class="label label-success">Yes</span></td></tr>
                            </table>
                        </div>

                        <!-- 7. Postman Quick Setup -->
                        <div class="instr-section">
                            <h3>7) Postman Quick Setup</h3>
                            <table class="instr-table">
                                <tr><th>Setting</th><th>Value</th></tr>
                                <tr><td>Method</td><td><code>POST</code></td></tr>
                                <tr><td>URL</td><td><code><?php echo site_url('lead/enquiry'); ?></code></td></tr>
                                <tr><td>Header: <code>Content-Type</code></td><td><code>application/json</code></td></tr>
                                <tr><td>Header: <code>X-Vendor-Code</code></td><td><code>shiksha</code> <span class="text-muted">(or your vendor code)</span></td></tr>
                                <tr><td>Header: <code>X-Api-Key</code></td><td><code>CHANGE_ME_SIKSHA_KEY</code> <span class="text-muted">(from vendor management page)</span></td></tr>
                                <tr><td>Body (raw JSON)</td><td>Use the sample payload from Section 3 above</td></tr>
                            </table>
                            <div class="instr-tip">Go to <strong>Headers</strong> tab in Postman, add the three headers above, then paste the JSON payload from Section 3 into the <strong>Body &rarr; raw &rarr; JSON</strong> tab.</div>
                        </div>

                        <!-- 8. cURL Example -->
                        <div class="instr-section">
                            <h3>8) cURL Example</h3>
                            <div class="instr-code"><?php echo htmlspecialchars("curl -X POST '" . site_url('lead/enquiry') . "' \\
  -H 'X-Vendor-Code: shiksha' \\
  -H 'X-Api-Key: YOUR_API_KEY_HERE' \\
  -H 'Content-Type: application/json' \\
  -d '{
    \"id\": \"SIKSHA\",
    \"param\": {
      \"applicantname\": \"Jane Smith\",
      \"mobilenumber\": \"9123456780\",
      \"emailid\": \"jane@example.com\",
      \"programid\": \"5\",
      \"programname\": \"B.TECH INFORMATION TECHNOLOGY\",
      \"city\": \"Salem\",
      \"state\": \"Tamil Nadu\"
    }
  }'", ENT_QUOTES); ?></div>
                        </div>

                        <!-- 9. Managing Vendors -->
                        <div class="instr-section">
                            <h3>9) Managing Vendors</h3>
                            <p>Go to <strong>System Settings &rarr; <a href="<?php echo site_url('schsettings/enquiryleadvendors'); ?>">Enquiry Lead Vendors</a></strong> to:</p>
                            <ul style="line-height:2">
                                <li><strong>Add a vendor</strong> — set a unique vendor code, name and API key. The API key is shown only once at creation time (stored as a bcrypt hash).</li>
                                <li><strong>Rotate the API key</strong> — click Edit &rarr; enter a new key. The old key is immediately invalidated.</li>
                                <li><strong>Deactivate / Activate</strong> — use the toggle button. Deactivated vendors receive a 403 error on all API calls.</li>
                                <li><strong>Delete</strong> — permanently removes the vendor. Existing enquiry records linked to it are retained.</li>
                            </ul>
                        </div>

                        <!-- 10. How enquiry is created -->
                        <div class="instr-section">
                            <h3>10) How Enquiry Records Are Created</h3>
                            <table class="instr-table">
                                <tr><th>Enquiry Field</th><th>Value / Source</th></tr>
                                <tr><td>Source</td><td><code>API - VENDORCODE</code> (e.g. <code>API - SHIKSHA</code>)</td></tr>
                                <tr><td>Reference</td><td>Vendor code</td></tr>
                                <tr><td>Reference Name</td><td>Vendor name</td></tr>
                                <tr><td>Ref No</td><td><code>ENQ-API-YYYYMMDDHHmmss###</code></td></tr>
                                <tr><td>Vendor Request ID</td><td>Value of <code>id</code> field in request (if supplied)</td></tr>
                                <tr><td>Lead Vendor ID</td><td>Links to <code>lead_api_vendors.id</code> for reporting</td></tr>
                                <tr><td>last_used_at</td><td>Updated on the vendor row on every successful lead insertion</td></tr>
                            </table>
                        </div>

                    </div>
                </div>
                <!-- / Section 2 -->

            </div>
        </div>
    </section>
</div>
