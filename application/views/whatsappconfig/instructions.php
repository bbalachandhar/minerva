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

                <!-- Page header -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-book"></i> WhatsApp Reseller Setup Guide</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('whatsappconfig/index'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Settings
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">How to onboard each school onto the WhatsApp Business Platform using a single System User token and a separate WABA per client &mdash; all billed to you, the reseller.</p>

                        <div class="instr-tip">
                            <strong>Architecture:</strong> One permanent System User access token (yours) &mdash; multiple WhatsApp Business Accounts (one per school) under your Meta Business Manager. You pay Meta and bill your clients.
                        </div>

                        <ul class="toc-list">
                            <li><a href="#section-saas-reseller"><i class="fa fa-key"></i> Step 1 &mdash; Generate Your System User Access Token</a></li>
                            <li><a href="#section-waba-per-client"><i class="fa fa-sitemap"></i> Step 2 &mdash; Create a Separate WABA Per Client &amp; Configure</a></li>
                            <li><a href="#section-client-agreement"><i class="fa fa-file-text-o"></i> Client Authorization Agreement Template</a></li>
                            <li><a href="#section-billing"><i class="fa fa-inr"></i> Meta Billing &mdash; How to Make Meta Invoice You &amp; Bill Your Clients</a></li>
                        </ul>
                    </div>
                </div>

                <!-- STEP 1: SYSTEM USER TOKEN -->
                <div class="box box-success" id="section-saas-reseller">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-key"></i> Step 1 &mdash; Generate Your System User Access Token (One Token for All Clients)</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-tip">
                            <strong>This token is generated once</strong> and stored in this system. It is used to send messages from every client school's WhatsApp number. Each client only needs to give you their Phone Number ID and WABA ID.
                        </div>

                        <div class="instr-section">
                            <h3>How It Works</h3>
                            <table class="instr-table">
                                <tr><th>Item</th><th>Who owns it</th><th>Same for all clients?</th></tr>
                                <tr><td>Access Token (System User)</td><td><strong>You</strong></td><td>&#9989; Yes &mdash; one token for all</td></tr>
                                <tr><td>Phone Number ID</td><td>Client</td><td>&#10060; Unique per client</td></tr>
                                <tr><td>WABA ID</td><td>Client</td><td>&#10060; Unique per client</td></tr>
                                <tr><td>Message Templates</td><td>Client's WABA</td><td>&#10060; Each client has their own approved templates</td></tr>
                            </table>
                        </div>

                        <div class="instr-section">
                            <h3>R1 &mdash; Create a System User in Your Meta Business Manager</h3>
                            <ol class="steps-ol">
                                <li>Log in to <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a> with your company account (not a client's account).</li>
                                <li>Go to <strong>Business Settings</strong> (gear icon, top-right).</li>
                                <li>In the left sidebar, click <strong>Users &rarr; System Users</strong>.</li>
                                <li>Click <strong>Add</strong>, enter a name (e.g. <em>Minerva WhatsApp Bot</em>), set role to <strong>Admin</strong>, click <strong>Create System User</strong>.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>R2 &mdash; Generate a Permanent Token for the System User</h3>
                            <ol class="steps-ol">
                                <li>Click the system user &rarr; <strong>Generate New Token</strong>.</li>
                                <li>Select your Meta App from the dropdown.</li>
                                <li>Set token expiry to <strong>Never</strong>.</li>
                                <li>Check these two permissions:
                                    <ul>
                                        <li><code>whatsapp_business_messaging</code></li>
                                        <li><code>whatsapp_business_management</code></li>
                                    </ul>
                                </li>
                                <li>Click <strong>Generate Token</strong> &mdash; <strong>copy it immediately</strong>. It is shown only once.</li>
                            </ol>
                            <div class="instr-note">Paste this token into the <strong>Access Token</strong> field in <strong>WhatsApp Settings &rarr; Meta tab</strong> for each client's school DB. Every client DB stores the same shared token but their own Phone Number ID and WABA ID.</div>
                        </div>

                        <div class="instr-section">
                            <h3>R3 &mdash; If a Client Has an Existing WABA (Partner Access)</h3>
                            <p>If the client already has their own WABA, send them these instructions to add you as a partner:</p>
                            <div class="instr-code">1. Log in to business.facebook.com with your school's Meta Business account.
2. Go to Business Settings &rarr; Accounts &rarr; WhatsApp Accounts.
3. Select your WhatsApp Business Account.
4. Click the "Partners" tab &rarr; "Add Partner".
5. Enter our Business Manager ID: [YOUR_BM_ID_HERE]
6. Grant: "Manage WhatsApp Business Accounts" and "Create and manage WhatsApp templates".
7. Click Save.</div>
                            <div class="instr-tip">After this, your System User token has full access to send from their number and manage their templates.</div>
                        </div>

                        <div class="instr-section">
                            <h3>R4 &mdash; Get the Client's Phone Number ID and WABA ID</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>Business Settings &rarr; Accounts &rarr; WhatsApp Accounts</strong>.</li>
                                <li>Select the client's WABA &rarr; note the <strong>WABA ID</strong>.</li>
                                <li><strong>WhatsApp Manager &rarr; Phone Numbers</strong> &rarr; find the client's number &rarr; note the <strong>Phone Number ID</strong>.</li>
                                <li>Or call the API:<br>
                                    <code>GET https://graph.facebook.com/v19.0/{WABA_ID}/phone_numbers?access_token={YOUR_SYSTEM_USER_TOKEN}</code>
                                </li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>R5 &mdash; Configure in the School's Admin Panel</h3>
                            <ol class="steps-ol">
                                <li>Go to <strong>WhatsApp Settings &rarr; Meta tab</strong>.</li>
                                <li>Fill in:
                                    <ul>
                                        <li><strong>Access Token</strong> &rarr; your System User permanent token</li>
                                        <li><strong>Phone Number ID</strong> &rarr; client's numeric Phone Number ID</li>
                                        <li><strong>WABA ID</strong> &rarr; client's WABA ID</li>
                                        <li><strong>Language</strong> &rarr; <code>en</code> or <code>en_US</code> (must match template language)</li>
                                        <li><strong>Status</strong> &rarr; Enabled</li>
                                    </ul>
                                </li>
                                <li>Save, then set the template name for each notification type.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Your Business Manager ID</h3>
                            <p>Find it at:</p>
                            <div class="instr-code">business.facebook.com &rarr; Business Settings &rarr; Business Info &rarr; Business Manager ID</div>
                            <p>Share this numeric ID with every client during onboarding so they can add you as a partner.</p>
                        </div>

                    </div>
                </div>

                <!-- STEP 2: SEPARATE WABA PER CLIENT -->
                <div class="box box-success" id="section-waba-per-client">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-sitemap"></i> Step 2 &mdash; Create a Separate WABA Per Client Under Your Business Manager</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-tip">
                            <strong>Why this is the best approach:</strong> One school getting flagged for spam does not affect any other school. Each client has isolated templates, quality ratings, and analytics. You can transfer the WABA back to the client if they ever leave.
                        </div>

                        <div class="instr-section">
                            <h3>What "Separate WABA Per Client" Means</h3>
                            <p>A <strong>WhatsApp Business Account (WABA)</strong> holds phone numbers and message templates. You create one WABA per school inside your Meta Business Manager. All are billed to your payment method.</p>
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
                            <h3>W1 &mdash; Create a New WABA for the Client</h3>
                            <ol class="steps-ol">
                                <li>Go to <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a> &rarr; <strong>Business Settings &rarr; Accounts &rarr; WhatsApp Accounts</strong>.</li>
                                <li>Click <strong>Add &rarr; Create a WhatsApp Business Account</strong>.</li>
                                <li>Enter:
                                    <ul>
                                        <li><strong>Name</strong>: school's legal name (e.g. <em>Greenwood School Notifications</em>)</li>
                                        <li><strong>Business</strong>: your Business Manager</li>
                                        <li><strong>Timezone</strong>: Asia/Kolkata &nbsp;|&nbsp; <strong>Currency</strong>: INR</li>
                                    </ul>
                                </li>
                                <li>Click <strong>Create</strong>. WABA is now in your BM, billed to your payment method.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>W2 &mdash; Add the Client&rsquo;s Phone Number to the WABA</h3>
                            <p>The number <strong>must not</strong> be active on any WhatsApp account. If it is, the client must delete their WhatsApp account on that number first.</p>
                            <ol class="steps-ol">
                                <li>WhatsApp Manager &rarr; select the new WABA &rarr; <strong>Phone Numbers &rarr; Add Phone Number</strong>.</li>
                                <li>Enter the phone number and display name (e.g. <em>Greenwood School</em>).</li>
                                <li>Choose SMS or Voice Call verification &rarr; enter OTP &rarr; click <strong>Verify</strong>.</li>
                                <li>Note the <strong>Phone Number ID</strong> now shown.</li>
                            </ol>
                            <div class="instr-warn">
                                <strong>Number already on WhatsApp?</strong> Client: WhatsApp &rarr; Settings &rarr; Account &rarr; Delete Account. Calls and SMS still work. Wait 5 minutes before adding to your WABA.
                            </div>
                        </div>

                        <div class="instr-section">
                            <h3>W3 &mdash; Set the Display Name</h3>
                            <ol class="steps-ol">
                                <li>WhatsApp Manager &rarr; Phone Numbers &rarr; click the number &rarr; <strong>Profile &rarr; Edit</strong>.</li>
                                <li>Set Display Name to the school's name. Add description, website, address.</li>
                                <li>Meta reviews the display name &mdash; usually a few hours to 1 day.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>W4 &mdash; Assign Your System User to the New WABA</h3>
                            <ol class="steps-ol">
                                <li><strong>Business Settings &rarr; Accounts &rarr; WhatsApp Accounts</strong> &rarr; select the new WABA.</li>
                                <li>Click <strong>Add People</strong> &rarr; add your System User with <strong>Full Control</strong>.</li>
                            </ol>
                            <p>Your one System User token now works for this WABA's phone number. No new token needed.</p>
                        </div>

                        <div class="instr-section">
                            <h3>W5 &mdash; Create Message Templates for the Client</h3>
                            <ol class="steps-ol">
                                <li>WhatsApp Manager &rarr; switch to client's WABA &rarr; <strong>Manage &rarr; Message Templates &rarr; Create Template</strong>.</li>
                                <li>Create each notification template (fee receipt, attendance, login credentials, etc.).</li>
                                <li>Utility templates approved in 1&ndash;24 hrs; marketing up to 7 days.</li>
                                <li>Note the <strong>template name exactly</strong> (case-sensitive) for use in notification settings.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>W6 &mdash; Configure in the School's Admin Panel</h3>
                            <ol class="steps-ol">
                                <li>Log in to the school's instance &rarr; <strong>System Settings &rarr; WhatsApp Settings &rarr; Meta tab</strong>.</li>
                                <li>Fill in:
                                    <ul>
                                        <li><strong>Access Token</strong>: your permanent System User token</li>
                                        <li><strong>Phone Number ID</strong>: from Step W2</li>
                                        <li><strong>WABA ID</strong>: from WhatsApp Manager &rarr; Account &rarr; Overview</li>
                                        <li><strong>Language</strong>: must match approved template language (e.g. <code>en</code> or <code>en_US</code>)</li>
                                        <li><strong>Status</strong>: Enabled</li>
                                    </ul>
                                </li>
                                <li>Save, then set each notification type's template name.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>W7 &mdash; If a Client Leaves (WABA Transfer)</h3>
                            <ol class="steps-ol">
                                <li><strong>Business Settings &rarr; WhatsApp Accounts</strong> &rarr; select their WABA &rarr; <strong>Settings &rarr; Transfer WhatsApp Business Account</strong>.</li>
                                <li>Enter the client's Business Manager ID.</li>
                                <li>Client accepts in their BM &mdash; WABA moves with all templates and history.</li>
                            </ol>
                            <div class="instr-tip">"Your number and templates are yours &mdash; we just manage them for you." This is a strong selling point.</div>
                        </div>

                        <div class="instr-section">
                            <h3>Quick Onboarding Checklist Per New Client</h3>
                            <table class="instr-table">
                                <tr><th>#</th><th>Task</th><th>Who</th><th>Time</th></tr>
                                <tr><td>1</td><td>Get signed authorization from school (phone number, school name)</td><td>You</td><td>Day 1</td></tr>
                                <tr><td>2</td><td>Confirm phone number is not on any WhatsApp; client deletes if needed</td><td>Client</td><td>Day 1</td></tr>
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

                <!-- CLIENT AGREEMENT -->
                <div class="box box-info" id="section-client-agreement">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Client Authorization Agreement Template</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-note">
                            Get this signed (physically or via email confirmation) from each school before registering their phone number.
                        </div>

                        <div class="instr-code" style="font-family:Georgia,serif; font-size:13px; line-height:1.8;">WHATSAPP BUSINESS PLATFORM - AUTHORIZATION LETTER

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

Name       : ___________________________
Designation: ___________________________
School     : ___________________________
Signature  : ___________________________
Date       : ___________________________
School Seal: (affix here)</div>

                        <div class="instr-tip" style="margin-top:12px;">
                            <strong>Tip:</strong> An email reply with "confirmed" from the school's official domain is legally equivalent to a signature under the IT Act 2000.
                        </div>

                    </div>
                </div>

                <!-- META BILLING -->
                <div class="box box-success" id="section-billing">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-inr"></i> Meta Billing &mdash; How to Make Meta Invoice You &amp; Bill Your Clients</h3>
                    </div>
                    <div class="box-body">

                        <div class="instr-tip">
                            <strong>Goal:</strong> Meta charges YOU (the reseller) for all WhatsApp conversations. You then charge your clients separately.
                        </div>

                        <div class="instr-section">
                            <h3>B1 &mdash; Set Up a Payment Method in Meta Business Manager</h3>
                            <ol class="steps-ol">
                                <li>Go to <a href="https://business.facebook.com/" target="_blank">business.facebook.com</a> &rarr; <strong>Business Settings &rarr; Billing &amp; Payments &rarr; Add Payment Method</strong>.</li>
                                <li>Add a credit/debit card or UPI in your company's name.</li>
                                <li>Set this as the <strong>primary billing account</strong>.</li>
                            </ol>
                            <div class="instr-note">Meta bills in USD. GST (18%) is added for Indian accounts &mdash; Meta issues a tax invoice for input credit.</div>
                        </div>

                        <div class="instr-section">
                            <h3>B2 &mdash; Link Client WABAs to YOUR Billing</h3>
                            <p>WABAs you create (Step W1) are automatically billed to you. If a client added you via partner access:</p>
                            <ol class="steps-ol">
                                <li>WhatsApp Manager &rarr; select client's WABA &rarr; <strong>Settings &rarr; Billing</strong>.</li>
                                <li>Change billing account to your BM's payment method.</li>
                            </ol>
                            <div class="instr-warn"><strong>Easiest:</strong> Always create the WABA yourself (Step W1) &mdash; billing is yours from day one.</div>
                        </div>

                        <div class="instr-section">
                            <h3>B3 &mdash; How Meta Charges You</h3>
                            <table class="instr-table">
                                <tr><th>Item</th><th>Details</th></tr>
                                <tr><td>Per conversation (not per message)</td><td>All messages to one person within 24 hours = 1 conversation charge</td></tr>
                                <tr><td>Frequency</td><td>Monthly (or when you hit a billing threshold)</td></tr>
                                <tr><td>Invoice</td><td>PDF emailed to your BM account email each cycle</td></tr>
                                <tr><td>Template type cost</td><td>Utility (fee receipts, attendance) is cheapest; Marketing is most expensive</td></tr>
                                <tr><td>Free tier</td><td>First 1,000 service conversations/month free per WABA</td></tr>
                            </table>
                        </div>

                        <div class="instr-section">
                            <h3>B4 &mdash; Download Meta Invoices</h3>
                            <ol class="steps-ol">
                                <li><strong>Business Settings &rarr; Billing &amp; Payments &rarr; Payment Activity &rarr; Download Invoice</strong>.</li>
                                <li>Meta's GSTIN: <code>09AAHCF8837R1ZJ</code> &mdash; invoices show IGST at 18%.</li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>B5 &mdash; How to Bill Your Clients</h3>

                            <h4>Option A &mdash; Flat monthly fee (simplest)</h4>
                            <p>Charge a fixed monthly amount per school. Include a fair usage cap with overage rates.</p>

                            <h4>Option B &mdash; Pass-through + margin</h4>
                            <ol class="steps-ol">
                                <li>Run on each client DB:<br><code>SELECT SUM(recipient_count) FROM whatsapp_message_log WHERE month=M AND year=Y</code></li>
                                <li>Multiply by India utility rate (~&#8377;0.35&ndash;0.40 per conversation as of 2024).</li>
                                <li>Add your margin and raise a GST invoice.</li>
                            </ol>
                            <div class="instr-note"><em>Messages &ne; Conversations.</em> Meta charges per 24-hour window. For school use, messages &asymp; conversations in practice.</div>
                        </div>

                        <div class="instr-section">
                            <h3>B6 &mdash; View Per-WABA Usage in Meta</h3>
                            <ol class="steps-ol">
                                <li>WhatsApp Manager &rarr; switch to client's WABA &rarr; <strong>Overview</strong> &rarr; filter by date.</li>
                                <li>Or use the API: <code>GET https://graph.facebook.com/v19.0/{WABA_ID}/conversation_analytics?...</code></li>
                            </ol>
                        </div>

                        <div class="instr-section">
                            <h3>Complete Reseller Flow Summary</h3>
                            <table class="instr-table">
                                <tr><th>#</th><th>Who</th><th>Action</th></tr>
                                <tr><td>1</td><td>You</td><td>Create System User, generate permanent token, add payment method to BM</td></tr>
                                <tr><td>2</td><td>You</td><td>Create WABA for the client in your BM (auto-billed to you)</td></tr>
                                <tr><td>3</td><td>You</td><td>Add client's phone number; assign System User with Full Control</td></tr>
                                <tr><td>4</td><td>You</td><td>Create message templates; wait for template approval</td></tr>
                                <tr><td>5</td><td>You</td><td>Enter Phone Number ID + WABA ID in client's school admin panel</td></tr>
                                <tr><td>6</td><td>Meta</td><td>Charges your card monthly, emails invoice</td></tr>
                                <tr><td>7</td><td>You</td><td>Bill each school (flat fee or from <code>whatsapp_message_log</code>)</td></tr>
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
