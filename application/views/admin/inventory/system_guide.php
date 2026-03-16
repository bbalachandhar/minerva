<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> Inventory System — Complete Usage Guide
            <small>End-to-end reference for administrators and operators</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/inventorydashboard'); ?>"><i class="fa fa-cubes"></i> Inventory Dashboard</a></li>
            <li class="active">System Guide</li>
        </ol>
    </section>

    <section class="content">

        <!-- ======================== QUICK NAV ======================== -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-compass"></i> Quick Navigation</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <a href="#section-overview"    class="btn btn-default btn-block btn-sm"><i class="fa fa-sitemap"></i> System Overview</a>
                        <a href="#section-setup"       class="btn btn-default btn-block btn-sm"><i class="fa fa-wrench"></i> Initial Setup</a>
                        <a href="#section-onboarding"  class="btn btn-default btn-block btn-sm"><i class="fa fa-flag-checkered"></i> Onboarding Plan</a>
                        <a href="#section-items"       class="btn btn-default btn-block btn-sm"><i class="fa fa-tags"></i> Items &amp; Categories</a>
                        <a href="#section-stock"       class="btn btn-default btn-block btn-sm"><i class="fa fa-download"></i> Stock Inward</a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="#section-indent"      class="btn btn-default btn-block btn-sm"><i class="fa fa-file-text-o"></i> Indent Requests</a>
                        <a href="#section-po"          class="btn btn-default btn-block btn-sm"><i class="fa fa-shopping-cart"></i> Purchase Orders</a>
                        <a href="#section-grn"         class="btn btn-default btn-block btn-sm"><i class="fa fa-truck"></i> Goods Receipt (GRN)</a>
                        <a href="#section-issue"       class="btn btn-default btn-block btn-sm"><i class="fa fa-share-square-o"></i> Issue &amp; Return</a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="#section-approval"    class="btn btn-default btn-block btn-sm"><i class="fa fa-check-circle"></i> Approval Workflows</a>
                        <a href="#section-matrix"      class="btn btn-default btn-block btn-sm"><i class="fa fa-table"></i> Approval Matrix Rules</a>
                        <a href="#section-assets"      class="btn btn-default btn-block btn-sm"><i class="fa fa-laptop"></i> Asset Management</a>
                        <a href="#section-statuses"    class="btn btn-default btn-block btn-sm"><i class="fa fa-exchange"></i> All Status Flows</a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="#section-db"          class="btn btn-default btn-block btn-sm"><i class="fa fa-database"></i> DB Tables Reference</a>
                        <a href="#section-rbac"        class="btn btn-default btn-block btn-sm"><i class="fa fa-lock"></i> Permissions (RBAC)</a>
                        <a href="#section-faq"         class="btn btn-default btn-block btn-sm"><i class="fa fa-question-circle"></i> FAQ / Troubleshooting</a>
                        <a href="<?php echo site_url('admin/inventorydashboard'); ?>" class="btn btn-primary btn-block btn-sm"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================== OVERVIEW ======================== -->
        <div class="box box-primary" id="section-overview">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-sitemap"></i> 1. System Overview</h3></div>
            <div class="box-body">
                <p>
                    This inventory module covers the <strong>full procure-to-use lifecycle</strong> — from raising a purchase
                    indent through multi-level approvals, supplier purchase orders, goods receipt, stock entry, item issuance,
                    and fixed-asset tracking. The diagram below shows the high-level flow:
                </p>
                <div class="well" style="background:#fafafa; font-family:monospace; font-size:13px; overflow-x:auto;">
<pre style="background:transparent; border:none; padding:0;">
  [Staff/Dept]
      |
      |  raises Indent Request
      v
  [Indent] ──approval──> [Indent Approved]
      |
      |  Stores Manager raises PO from approved indent
      v
  [Purchase Order (PO)]
      |  L1 / L2 sequential approval
      |  (routed automatically by Approval Matrix or manually selected)
      v
  [PO Approved]
      |
      |  Supplier delivers goods; Stores creates GRN
      v
  [Goods Receipt Note (GRN)]
    per line: received_qty / accepted_qty / rejected_qty
      |
      |  On  ACCEPT:  stock added to item_stock
      |  On  ACCEPT (asset category): asset records auto-created in inv_assets
      v
  [Stock Available]
      |
      |  Staff requests item issuance
      v
  [Item Issue] ──return──> [Item Return]

  [inv_assets] ──assign──> [Asset Assignment]
               ──transfer─> [Asset Transfer]
               ──maintenance─> [Maintenance Log]
</pre>
                </div>

                <div class="callout callout-info">
                    <h4 style="margin-top:0;">Bulk Upload Dependency Order</h4>
                    <p>
                        For a new institution, upload inventory masters in this order so each next step can resolve the previous one correctly:
                    </p>
                    <ol style="margin-bottom:10px; padding-left:18px;">
                        <li><strong>Category Master</strong> — all items depend on categories.</li>
                        <li><strong>Store Master</strong> — stock entries and GRN receipts depend on stores.</li>
                        <li><strong>Supplier Master</strong> — procurement and opening stock depend on suppliers.</li>
                        <li><strong>Item Master</strong> — items depend on valid categories.</li>
                        <li><strong>Opening Stock / Item Stock</strong> — stock depends on item, supplier, and optionally store masters.</li>
                    </ol>
                    <p style="margin-bottom:0;">
                        After these five bulk uploads are complete, proceed with <strong>Indent → Purchase Order → Approval → GRN → Asset / Issue workflows</strong>.
                    </p>
                </div>

                <h4>Module Summary</h4>
                <table class="table table-bordered table-condensed">
                    <thead><tr class="active"><th>Module</th><th>URL</th><th>Purpose</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Item Masters</strong></td><td><code>admin/item</code></td><td>Define purchasable/issuable items (name, code, unit, category)</td></tr>
                        <tr><td><strong>Item Categories</strong></td><td><code>admin/itemcategory</code></td><td>Group items; flag as <em>Asset Category</em> to trigger auto asset creation on GRN</td></tr>
                        <tr><td><strong>Item Stores</strong></td><td><code>admin/itemstore</code></td><td>Physical store locations (e.g. Main Store, IT Store)</td></tr>
                        <tr><td><strong>Suppliers</strong></td><td><code>admin/itemsupplier</code></td><td>Supplier master — used when creating POs</td></tr>
                        <tr><td><strong>Stock Inward</strong></td><td><code>admin/itemstock</code></td><td>Direct stock entry (non-PO route, e.g. donations / opening stock)</td></tr>
                        <tr><td><strong>Issue / Return</strong></td><td><code>admin/issueitem</code></td><td>Issue items to staff and track returns</td></tr>
                        <tr><td><strong>Indents</strong></td><td><code>admin/inventoryindent</code></td><td>Purchase indents raised by departments</td></tr>
                        <tr><td><strong>Indent Approvals</strong></td><td><code>admin/inventoryindent/approvals</code></td><td>Approve/reject indent requests</td></tr>
                        <tr><td><strong>Purchase Orders</strong></td><td><code>admin/inventoryprocurement/purchaseorders</code></td><td>Create POs from approved indents; track approval status</td></tr>
                        <tr><td><strong>PO Approvals</strong></td><td><code>admin/inventoryprocurement/poapprovals</code></td><td>Approve/reject POs with comments (queue shows only your pending actions)</td></tr>
                        <tr><td><strong>Goods Receipts</strong></td><td><code>admin/inventoryprocurement/goodsreceipts</code></td><td>Record what was physically received against an approved PO</td></tr>
                        <tr><td><strong>Asset Register</strong></td><td><code>admin/assetmanagement/register</code></td><td>View all fixed assets (auto-created from GRN or manually added)</td></tr>
                        <tr><td><strong>Asset Assignment</strong></td><td><code>admin/assetmanagement/assignment</code></td><td>Assign an asset to a staff member or location</td></tr>
                        <tr><td><strong>Asset Transfer</strong></td><td><code>admin/assetmanagement/transfer</code></td><td>Transfer custody of an asset</td></tr>
                        <tr><td><strong>Asset Maintenance</strong></td><td><code>admin/assetmanagement/maintenance</code></td><td>Log maintenance events for assets</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================== INITIAL SETUP ======================== -->
        <div class="box box-warning" id="section-setup">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-wrench"></i> 2. Initial Setup Checklist</h3></div>
            <div class="box-body">
                <p>Complete these steps once before going live:</p>
                <ol>
                    <li>
                        <strong>Confirm system readiness</strong> — ensure all required modules, menus, and workflow tables are already provisioned by your implementation team before user onboarding starts.
                    </li>
                    <li><strong>Create Item Categories</strong> — mark fixed-asset categories (e.g. Laptop, Projector) with <em>Is Asset = Yes</em>.</li>
                    <li><strong>Create Item Masters</strong> — assign each item to a category and unit (Nos, Kg, Box …).</li>
                    <li><strong>Create Stores</strong> — at least one store location (e.g. <em>Main Store</em>).</li>
                    <li><strong>Create Suppliers</strong> — at least one supplier for PO creation.</li>
                    <li>
                        <strong>Configure Approval Matrix</strong> (optional but recommended) — insert rows into <code>inv_po_approval_rules</code>:
                        <pre style="background:#f4f4f4; padding:10px; border-radius:4px;">INSERT INTO inv_po_approval_rules
(rule_name, department_id, min_amount, max_amount,
 approval_level, approver_type, approver_role_id, sort_order, is_active)
VALUES
-- All POs: L1 = anyone with role_id = 5 (e.g. "Purchase Head")
('All POs - L1', NULL, 0, NULL, 1, 'role', 5, 1, 1),
-- POs over ₹50,000: also need L2 from role_id = 2 (e.g. "Principal")
('POs >50k - L2', NULL, 50000.01, NULL, 2, 'role', 2, 1, 1);</pre>
                        See <a href="#section-matrix">Approval Matrix Rules</a> for full details.
                    </li>
                    <li><strong>Assign RBAC permissions</strong> — give roles the <code>item_stock</code> privilege (View/Add/Edit) for all inventory access.</li>
                </ol>
            </div>
        </div>

        <div class="box box-warning" id="section-onboarding">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-flag-checkered"></i> 3. First-Time Institution Onboarding Plan</h3></div>
            <div class="box-body">
                <p>
                    For a brand-new institution going live on ERP, do <strong>not</strong> start by entering stock or raising POs.
                    Build the inventory masters in dependency order so each later step can resolve the earlier references cleanly.
                </p>

                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr class="active">
                            <th>Step</th>
                            <th>Module</th>
                            <th>Why It Must Come First</th>
                            <th>Recommended Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><strong>Item Categories</strong></td>
                            <td>Every item must belong to a category. Asset categories also control auto asset creation during GRN.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/itemcategory</code></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><strong>Item Stores</strong></td>
                            <td>Stores define where opening stock, inward stock, and GRN accepted quantities will be placed.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/itemstore</code></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><strong>Suppliers</strong></td>
                            <td>Suppliers are required by procurement and are also referenced by opening stock imports.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/itemsupplier</code></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><strong>Items</strong></td>
                            <td>Items depend on categories. Without item masters, no stock, indent, PO, or GRN transaction can be created.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/item</code></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><strong>Opening Stock</strong></td>
                            <td>Opening stock depends on valid item, supplier, and optionally store masters.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/itemstock</code></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td><strong>Indent / PO Workflow Setup</strong></td>
                            <td>After inventory masters are ready, procurement users can safely create indents, POs, approvals, and GRNs.</td>
                            <td>Manual operational setup</td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td><strong>PO Approval Matrix</strong></td>
                            <td>Role/staff-based auto-routing should be configured only after staff roles and purchasing governance are finalized.</td>
                            <td>Insert rules into <code>inv_po_approval_rules</code></td>
                        </tr>
                        <tr class="info">
                            <td colspan="4"><strong><i class="fa fa-laptop"></i> Steps 8–10 — Onboarding Existing Physical Assets (do after steps 1–7 are stable)</strong></td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td><strong>Asset Locations</strong></td>
                            <td>Labs, departments, rooms, and stores referenced by asset rows and assignment records. Must exist before importing the asset register.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/assetlocation</code></td>
                        </tr>
                        <tr>
                            <td>9</td>
                            <td><strong>Asset Register Snapshot</strong></td>
                            <td>One row per physical unit — asset tag, serial/model/brand, item linkage, purchase cost, warranty dates, current status, and location. This creates the fixed asset ledger.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/assetregister</code></td>
                        </tr>
                        <tr>
                            <td>10</td>
                            <td><strong>Asset Assignment Snapshot</strong></td>
                            <td>For each asset currently in use: who has it now (staff by employee_id or place by location_code) and when it was assigned. This creates the live "who has what" custody state.</td>
                            <td>Bulk upload via <code>admin/inventoryimport/assetassignment</code></td>
                        </tr>
                    </tbody>
                </table>

                <h4>Recommended Execution Plan</h4>
                <ol>
                    <li>Prepare source data in Excel. Download the sample CSV from each import page for exact column names.</li>
                    <li>Upload in order: <strong>Categories → Stores → Suppliers → Items → Opening Stock</strong>.</li>
                    <li>Review each master list screen after upload. Fix naming mismatches before loading the next dependency.</li>
                    <li>Configure PO Approval Matrix rules and start live procurement (Indent → PO → GRN).</li>
                    <li>To onboard existing physical assets: upload <strong>Asset Locations</strong> first (labs, rooms, departments).</li>
                    <li>Then upload the <strong>Asset Register Snapshot</strong> — one CSV row per physical unit with serial number, brand, warranty, and cost.</li>
                    <li>Finally upload the <strong>Assignment Snapshot</strong> — records who currently holds each asset. This closes the custody gap immediately.</li>
                    <li>Open the <strong>Asset Register</strong> screen and use the filter bar to verify license, warranty, and assignment state for each department.</li>
                </ol>

                <div class="callout callout-warning">
                    <strong>Asset Import Order is Strict:</strong> Location codes must exist before the asset register import, and asset tags must exist before the assignment import.
                    Upload in the order 8 → 9 → 10 without skipping steps.
                </div>

                <div class="callout callout-info">
                    <strong>Operational Rule:</strong> Opening stock import is best used only once at go-live.
                    After go-live, new receipts should come through PO + GRN so procurement, stock, and asset trails stay aligned.
                    For new asset purchases after go-live, use the GRN route — assets are auto-created when the GRN is accepted for an <code>is_asset=1</code> category.
                </div>
            </div>
        </div>

        <!-- ======================== ITEMS & CATEGORIES ======================== -->
        <div class="box box-default" id="section-items">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tags"></i> 4. Items &amp; Categories</h3></div>
            <div class="box-body">
                <h4>Item Categories (<code>admin/itemcategory</code>)</h4>
                <ul>
                    <li>Each item must belong to one category.</li>
                    <li>The flag <strong><code>is_asset = 1</code></strong> on a category tells the system: <em>"when goods in this category are accepted on a GRN, automatically create asset records"</em>.</li>
                    <li>Examples of asset categories: Laptop, Desktop, Projector, Furniture, Lab Equipment.</li>
                    <li>Examples of consumable categories: Stationery, Cleaning Supplies, Toner.</li>
                </ul>
                <h4>Item Masters (<code>admin/item</code>)</h4>
                <ul>
                    <li>Fields: <strong>Item Name</strong>, <strong>Item Code</strong> (unique), <strong>Category</strong>, <strong>Unit</strong> (Nos/Kg/Ltr …), optional description.</li>
                    <li>Items are shared across Purchase Orders, GRNs, Stock Inward, and Issue modules.</li>
                </ul>
                <div class="callout callout-info">
                    <strong>Tip:</strong> Use consistent item codes (e.g. <code>IT-LAPTOP-001</code>) to make purchase and asset reports easier to interpret.
                </div>
            </div>
        </div>

        <!-- ======================== STOCK INWARD ======================== -->
        <div class="box box-default" id="section-stock">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-download"></i> 5. Direct Stock Inward (Non-PO)</h3></div>
            <div class="box-body">
                <p>Use <strong>Stock Inward</strong> (<code>admin/itemstock</code>) to add stock <em>without</em> a Purchase Order — opening balances, donations, transfers from another branch.</p>
                <ul>
                    <li>Select: <strong>Item → Store → Quantity → Unit Price → Date</strong>.</li>
                    <li>Stock is immediately available for issuance.</li>
                    <li>This does <strong>not</strong> create asset records even for asset-category items — use the GRN route for that.</li>
                </ul>
            </div>
        </div>

        <!-- ======================== INDENTS ======================== -->
        <div class="box box-default" id="section-indent">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-text-o"></i> 6. Indent Requests</h3></div>
            <div class="box-body">
                <h4>Raising an Indent (<code>admin/inventoryindent</code> → New Indent)</h4>
                <ul>
                    <li>Any authorised staff member raises an indent when a department needs items purchased.</li>
                    <li>Fields: <strong>Department</strong>, <strong>Required Date</strong>, one or more line items (Item + Quantity + Justification).</li>
                    <li>After submission the indent status is <code>pending</code>.</li>
                </ul>

                <h4>Indent Approval (<code>admin/inventoryindent/approvals</code>)</h4>
                <ul>
                    <li>Indent approvals are now assigned from <strong>System Settings → Indent Approval Fallback</strong>.</li>
                    <li><strong>Configured Fallback</strong>: Level 1 uses the requester department head when enabled and mapped. If no department head is available, Level 1 falls back to the configured Level 2 approver.</li>
                    <li><strong>Final Approver</strong>: Level 2 is always the configured fallback staff member.</li>
                    <li><strong>Super Admin Override</strong>: Super Admin can change only Level 1 during indent creation when that option is enabled in settings.</li>
                    <li>The assigned approver reviews the indent and clicks <strong>Approve</strong> or <strong>Reject</strong> with a comment.</li>
                    <li>Only <code>approved</code> indents appear in the PO creation form for selection.</li>
                </ul>

                <h4>Indent Status Flow</h4>
                <div class="well" style="background:#fafafa; font-family:monospace; font-size:13px;">
                    <code>submitted</code> → <strong>L1 pending</strong> → if L2 exists: <code>pending</code> → <strong>L2 approve</strong> → <code>approved</code><br>
                    <code>submitted</code> → <strong>L1 reject</strong>  → <code>rejected</code><br>
                    <code>submitted</code> → <strong>single approver approve</strong> → <code>approved</code>
                </div>
            </div>
        </div>

        <!-- ======================== PURCHASE ORDERS ======================== -->
        <div class="box box-success" id="section-po">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shopping-cart"></i> 7. Purchase Orders (PO)</h3></div>
            <div class="box-body">
                <h4>6.1 Creating a PO (<code>admin/inventoryprocurement/createpo</code>)</h4>
                <ol>
                    <li>Select an <code>approved</code> indent from the dropdown.</li>
                    <li>Select the supplier, set expected delivery date and tax percentage.</li>
                    <li>Add line items (item, quantity ordered, unit price). The total is calculated automatically.</li>
                    <li>
                        <strong>Approvers — two routes:</strong>
                        <ul>
                            <li><strong>Auto (Approval Matrix)</strong>: If rules exist in <code>inv_po_approval_rules</code> for the indent's department and total amount, the system automatically assigns approvers from the configured roles/staff. A banner on the form confirms this.</li>
                            <li><strong>Configured Fallback</strong>: If no rules match, the system prefills L1 from the indent department head and always uses the configured fallback L2 approver. If no department head is mapped, L1 collapses to the same configured L2 approver. Super Admin can override only L1 when that option is enabled in system settings.</li>
                        </ul>
                    </li>
                    <li>Submit. PO status becomes <code>pending_approval</code>.</li>
                </ol>

                <h4>6.2 PO Status Flow</h4>
                <div class="well" style="background:#fafafa; font-family:monospace; font-size:13px;">
<pre style="background:transparent; border:none; padding:0;">
pending_approval
    |
    |  L1 Approver decides
    v
  L1 approved → if L2 exists: L2 status moves to "pending"
  L1 rejected → PO status = rejected (L2 auto-rejected)
    |
    |  L2 Approver decides (if configured)
    v
  L2 approved → PO status = "approved"   ← GRN can now be created
  L2 rejected → PO status = "rejected"
    |
    |  GRN submitted and all lines received
    v
  partially_received  (some items still outstanding)
    |
    |  All PO lines fully received
    v
  received  (terminal state)
</pre>
                </div>

                <h4>6.3 PO List (<code>admin/inventoryprocurement/purchaseorders</code>)</h4>
                <ul>
                    <li>Shows all POs with current status badge.</li>
                    <li>Click <strong>View</strong> to see line items and approval trail.</li>
                    <li>A PO can only be GRN'd when its status is <code>approved</code> or <code>partially_received</code>.</li>
                </ul>
            </div>
        </div>

        <!-- ======================== APPROVAL WORKFLOWS ======================== -->
        <div class="box box-success" id="section-approval">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-check-circle"></i> 8. Approval Workflows</h3></div>
            <div class="box-body">
                <h4>7.1 PO Approval Queue (<code>admin/inventoryprocurement/poapprovals</code>)</h4>
                <ul>
                    <li>Each approver sees only the POs <strong>assigned to them</strong> where their action is currently required (status = <code>pending</code>).</li>
                    <li><code>queued</code> rows are not shown — they become visible only after the preceding level approves.</li>
                    <li>The approver clicks <strong>Approve</strong> or <strong>Reject</strong> and adds an optional comment.</li>
                    <li>
                        <strong>Security:</strong> The system verifies that the logged-in staff ID matches the assigned approver ID.
                        No other user can act on someone else's approval row.
                    </li>
                </ul>

                <h4>7.2 Sequential Level Promotion</h4>
                <table class="table table-bordered table-condensed">
                    <thead><tr class="active"><th>Event</th><th>L1 Status</th><th>L2 Status</th><th>PO Status</th></tr></thead>
                    <tbody>
                        <tr><td>PO submitted (L1+L2 configured)</td><td><code>pending</code></td><td><code>queued</code></td><td><code>pending_approval</code></td></tr>
                        <tr><td>PO submitted (L1 only)</td><td><code>pending</code></td><td>—</td><td><code>pending_approval</code></td></tr>
                        <tr><td>L1 approves</td><td><code>approved</code></td><td><code>pending</code></td><td><code>pending_approval</code></td></tr>
                        <tr><td>L1 approves (no L2)</td><td><code>approved</code></td><td>—</td><td><code>approved</code></td></tr>
                        <tr><td>L1 rejects</td><td><code>rejected</code></td><td><code>rejected</code></td><td><code>rejected</code></td></tr>
                        <tr><td>L2 approves</td><td><code>approved</code></td><td><code>approved</code></td><td><code>approved</code></td></tr>
                        <tr><td>L2 rejects</td><td><code>approved</code></td><td><code>rejected</code></td><td><code>rejected</code></td></tr>
                    </tbody>
                </table>

                <div class="callout callout-warning">
                    <strong>Note:</strong> A rejected PO cannot be un-rejected through the UI. To reprocess, create a new PO referencing the same indent (or a new indent).
                </div>
            </div>
        </div>

        <!-- ======================== APPROVAL MATRIX ======================== -->
        <div class="box box-success" id="section-matrix">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-table"></i> 9. Approval Matrix Rules</h3></div>
            <div class="box-body">
                <p>
                    The approval matrix allows you to configure <em>who must approve</em> a PO
                    based on the <strong>department that raised the indent</strong> and the <strong>total PO amount slab</strong>.
                    Rules are stored in the <code>inv_po_approval_rules</code> database table.
                </p>

                <h4>8.1 How the Engine Works (on PO Submit)</h4>
                <ol>
                    <li>System reads the department of the linked indent (<code>indent.department_id</code>).</li>
                    <li>Queries <code>inv_po_approval_rules</code> where:
                        <ul>
                            <li><code>is_active = 1</code></li>
                            <li><code>department_id IS NULL</code> (applies to all depts) <strong>OR</strong> <code>department_id = indent.department_id</code></li>
                            <li><code>min_amount &lt;= po_total</code></li>
                            <li><code>max_amount IS NULL</code> (no upper cap) <strong>OR</strong> <code>max_amount &gt;= po_total</code></li>
                        </ul>
                    </li>
                    <li>Rows are ordered by <code>approval_level ASC, sort_order ASC</code>. One row per level is used (the first matching row for each level).</li>
                    <li>For each row the system resolves <em>who</em> to assign:
                        <ul>
                            <li><code>approver_type = 'role'</code>: finds the first active staff member who has that role (filtered by department if <code>department_id</code> is set on the rule).</li>
                            <li><code>approver_type = 'staff'</code>: uses <code>approver_staff_id</code> directly (must be an active staff record).</li>
                        </ul>
                    </li>
                    <li>If no rules match → system falls back to the manually selected L1/L2 dropdowns on the PO form.</li>
                    <li>If rules match but no active staff can be found for a role → that level is skipped silently.</li>
                </ol>

                <h4>8.2 Column Reference for <code>inv_po_approval_rules</code></h4>
                <table class="table table-bordered table-condensed">
                    <thead><tr class="active"><th>Column</th><th>Type</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>rule_name</code></td><td>VARCHAR(150)</td><td>Friendly label for this rule, e.g. "All POs – L1"</td></tr>
                        <tr><td><code>department_id</code></td><td>INT, nullable</td><td>NULL = applies to all departments. Set to a dept ID to restrict to one dept.</td></tr>
                        <tr><td><code>min_amount</code></td><td>DECIMAL(14,2)</td><td>Inclusive lower bound of PO total. Use 0 for no lower limit.</td></tr>
                        <tr><td><code>max_amount</code></td><td>DECIMAL(14,2), nullable</td><td>Inclusive upper bound. NULL = no upper limit (open-ended slab).</td></tr>
                        <tr><td><code>approval_level</code></td><td>INT</td><td>1 = first approver, 2 = second approver, etc.</td></tr>
                        <tr><td><code>approver_type</code></td><td>'staff' | 'role'</td><td>Whether to resolve by a specific staff member or any staff in a role.</td></tr>
                        <tr><td><code>approver_staff_id</code></td><td>INT, nullable</td><td>Required when <code>approver_type = 'staff'</code>.</td></tr>
                        <tr><td><code>approver_role_id</code></td><td>INT, nullable</td><td>Required when <code>approver_type = 'role'</code>. References <code>roles.id</code>.</td></tr>
                        <tr><td><code>sort_order</code></td><td>INT</td><td>Within the same level, lower sort_order rows are tried first.</td></tr>
                        <tr><td><code>is_active</code></td><td>TINYINT(1)</td><td>0 = rule disabled without deleting it.</td></tr>
                    </tbody>
                </table>

                <h4>8.3 Example Rule Configurations</h4>

                <p><strong>Scenario A — Simple: One approver for all POs</strong></p>
                <pre style="background:#f4f4f4; padding:10px; border-radius:4px;">INSERT INTO inv_po_approval_rules
(rule_name, department_id, min_amount, max_amount, approval_level, approver_type, approver_role_id, sort_order, is_active)
VALUES ('All POs - L1', NULL, 0, NULL, 1, 'role', &lt;purchase_head_role_id&gt;, 1, 1);</pre>

                <p><strong>Scenario B — Two levels for POs above ₹50,000</strong></p>
                <pre style="background:#f4f4f4; padding:10px; border-radius:4px;">-- L1 for ALL POs (no amount restriction)
INSERT INTO inv_po_approval_rules VALUES (NULL,'All POs - L1',NULL,0,NULL,1,'role',5,1,1,NOW(),NOW());
-- L2 only for POs over ₹50,000
INSERT INTO inv_po_approval_rules VALUES (NULL,'POs>50k - L2',NULL,50000.01,NULL,2,'role',2,1,1,NOW(),NOW());</pre>

                <p><strong>Scenario C — Department-specific approver</strong></p>
                <pre style="background:#f4f4f4; padding:10px; border-radius:4px;">-- IT Dept (dept_id=3) POs approved by IT Head (staff_id=12)
INSERT INTO inv_po_approval_rules
(rule_name, department_id, min_amount, max_amount, approval_level, approver_type, approver_staff_id, sort_order, is_active)
VALUES ('IT Dept POs - L1', 3, 0, NULL, 1, 'staff', 12, 1, 1);</pre>

                <div class="callout callout-info">
                    <strong>Finding Role IDs:</strong> Run <code>SELECT id, role_name FROM roles;</code> in phpMyAdmin.
                    <strong>Finding Staff IDs:</strong> Run <code>SELECT id, CONCAT(firstname,' ',lastname) AS name FROM staff WHERE is_active=1;</code>
                </div>
            </div>
        </div>

        <!-- ======================== GRN ======================== -->
        <div class="box box-info" id="section-grn">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-truck"></i> 10. Goods Receipt Notes (GRN)</h3></div>
            <div class="box-body">
                <h4>9.1 Prerequisites</h4>
                <ul>
                    <li>The linked PO must be in status <code>approved</code> or <code>partially_received</code>.</li>
                    <li>Only items with outstanding quantity (ordered − already received) appear on the GRN form.</li>
                </ul>

                <h4>9.2 Creating a GRN (<code>admin/inventoryprocurement/creategrn</code>)</h4>
                <ol>
                    <li>Select the approved PO. Line items load automatically with <em>balance to receive</em>.</li>
                    <li>Enter the delivery challan / invoice number and receipt date.</li>
                    <li>For each line enter:
                        <ul>
                            <li><strong>Received Qty</strong> — what physically arrived (≤ balance outstanding).</li>
                            <li><strong>Accepted Qty</strong> — of what arrived, how many passed quality check (≤ received).</li>
                            <li><strong>Rejected Qty</strong> — items failing quality (auto-calculated: received − accepted).</li>
                            <li><strong>Unit Price</strong> — may differ from PO if actual invoice price differs.</li>
                            <li><strong>Store</strong> — which store to inward the accepted items into.</li>
                        </ul>
                    </li>
                    <li>Submit GRN. The system:
                        <ul>
                            <li>Inserts into <code>item_stock</code> for each <em>accepted</em> line (qty &gt; 0).</li>
                            <li>Creates <code>inv_assets</code> records automatically for each accepted unit in an <code>is_asset=1</code> category.</li>
                            <li>Updates PO status to <code>partially_received</code> or <code>received</code>.</li>
                        </ul>
                    </li>
                </ol>

                <h4>9.3 Partial Deliveries</h4>
                <p>
                    You can create multiple GRNs against one PO until all lines are fully received.
                    Each GRN pre-fills only the remaining outstanding quantity per line.
                    Once all ordered quantities are received the PO automatically moves to <code>received</code>.
                </p>

                <div class="callout callout-warning">
                    <strong>Rejected Items:</strong> Rejected quantity is recorded in <code>inv_goods_receipt_items.rejected_qty</code> but does NOT add to stock. The vendor returns / replacement workflow is handled outside this module currently.
                </div>
            </div>
        </div>

        <!-- ======================== ISSUE / RETURN ======================== -->
        <div class="box box-default" id="section-issue">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-share-square-o"></i> 11. Issue &amp; Return</h3></div>
            <div class="box-body">
                <h4>Issuing an Item (<code>admin/issueitem</code>)</h4>
                <ul>
                    <li>Shows items with available stock balance (inward minus prior issues not returned).</li>
                    <li>Select: <strong>Item → Store → Staff/Department → Quantity → Issue Date → Purpose</strong>.</li>
                    <li>Stock balance decreases immediately on issue.</li>
                </ul>
                <h4>Returning an Item</h4>
                <ul>
                    <li>Filter issued items and click <strong>Return</strong>.</li>
                    <li><code>is_returned = 1</code> flag is set; stock balance is restored.</li>
                </ul>
                <div class="callout callout-info">
                    Fixed assets (laptops, projectors, etc.) should be tracked through the <a href="#section-assets">Asset Management</a> module rather than Issue/Return, for better custody and audit trails.
                </div>
            </div>
        </div>

        <!-- ======================== ASSETS ======================== -->
        <div class="box box-info" id="section-assets">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-laptop"></i> 12. Asset Management</h3></div>
            <div class="box-body">
                <h4>11.1 Auto-Creation from GRN</h4>
                <p>
                    When a GRN line is accepted and the item's category has <code>is_asset = 1</code>,
                    the system creates one <code>inv_assets</code> record <em>per accepted unit</em>. 
                    For example, if you accept 3 laptops on a GRN → 3 asset records are created, each with:
                </p>
                <ul>
                    <li><code>asset_code</code> — auto-generated using format <code>AST-&lt;grn_id&gt;-&lt;counter&gt;</code></li>
                    <li><code>item_id</code>, <code>grn_id</code>, <code>store_id</code>, <code>unit_price</code></li>
                    <li><code>status = 'in_store'</code></li>
                    <li><code>purchase_date</code> = GRN receipt date</li>
                </ul>

                <h4>11.2 Asset Status Flow</h4>
                <div class="well" style="background:#fafafa; font-family:monospace; font-size:13px;">
                    <code>in_store</code> → assign → <code>assigned</code> → transfer → <code>assigned</code> (new custodian)<br>
                    <code>assigned</code> → maintenance → <code>under_maintenance</code> → complete → <code>assigned</code> or <code>in_store</code><br>
                    <code>in_store/assigned</code> → dispose → <code>disposed</code>
                </div>

                <h4>11.3 Asset Register (<code>admin/assetmanagement/register</code>)</h4>
                <p>View all assets with current status, custodian, warranty state, license info, and open maintenance count. Use the 7-way filter bar (status, holder type, location, staff, warranty state, maintenance-open, license state) to drill into any subset.</p>

                <h4>11.4 Asset Assignment (<code>admin/assetmanagement/assignment</code>)</h4>
                <p>Assign an <code>in_stock</code> asset to a staff member or location. Records assignment date. Sets asset status to <code>assigned</code>.</p>

                <h4>11.5 Asset Transfer (<code>admin/assetmanagement/transfer</code>)</h4>
                <p>Transfer an <code>assigned</code> asset from current custodian to a new one. Full audit trail maintained.</p>

                <h4>11.6 Asset Maintenance (<code>admin/assetmanagement/maintenance</code>)</h4>
                <p>Log maintenance events: issue description, date sent, vendor, cost, resolved date. Asset moves to <code>under_maintenance</code> during this period.</p>

                <h4>11.7 Bulk Import Routes (for existing asset onboarding)</h4>
                <div class="callout callout-warning" style="margin-top:10px;">
                    Use these three import pages to onboard assets from your existing XLS register. Upload in order — locations first, then assets, then assignments.
                </div>
                <table class="table table-bordered table-condensed">
                    <thead><tr class="active"><th>Step</th><th>Import Page</th><th>Sample CSV Download</th><th>Key Columns</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><a href="<?php echo site_url('admin/inventoryimport/assetlocation'); ?>">Asset Locations</a></td>
                            <td><code>inventory_assetlocation_sample.csv</code></td>
                            <td><code>location_code</code>, <code>location_name</code>, <code>location_type</code> (lab/room/department/store/other)</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><a href="<?php echo site_url('admin/inventoryimport/assetregister'); ?>">Asset Register Snapshot</a></td>
                            <td><code>inventory_assetregister_sample.csv</code></td>
                            <td><code>asset_tag</code>*, <code>asset_name</code>*, <code>serial_no</code>, <code>brand_name</code>, <code>model_no</code>, <code>purchase_cost</code>, <code>warranty_start</code>, <code>warranty_end</code>, <code>current_status</code>, <code>location_code</code></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><a href="<?php echo site_url('admin/inventoryimport/assetassignment'); ?>">Asset Assignment Snapshot</a></td>
                            <td><code>inventory_assetassignment_sample.csv</code></td>
                            <td><code>asset_tag</code>*, <code>assignee_type</code>* (staff/place), <code>employee_id</code> (for staff), <code>location_code</code> (for place), <code>assigned_on</code>*</td>
                        </tr>
                    </tbody>
                </table>
                <p><small>* = required column</small></p>
            </div>
        </div>

        <!-- ======================== STATUS FLOWS ======================== -->
        <div class="box box-default" id="section-statuses">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-exchange"></i> 13. All Status Reference</h3></div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Indent Statuses</h5>
                        <table class="table table-bordered table-condensed">
                            <thead><tr class="active"><th>Status</th><th>Meaning</th></tr></thead>
                            <tbody>
                                <tr><td><span class="label label-warning">pending</span></td><td>Submitted, awaiting approval</td></tr>
                                <tr><td><span class="label label-success">approved</span></td><td>Approved — can be used for PO creation</td></tr>
                                <tr><td><span class="label label-danger">rejected</span></td><td>Rejected by approver</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>PO Approval Level Statuses</h5>
                        <table class="table table-bordered table-condensed">
                            <thead><tr class="active"><th>Status</th><th>Meaning</th></tr></thead>
                            <tbody>
                                <tr><td><span class="label label-default">queued</span></td><td>Level not yet activated (prior level pending)</td></tr>
                                <tr><td><span class="label label-warning">pending</span></td><td>Awaiting this approver's decision now</td></tr>
                                <tr><td><span class="label label-success">approved</span></td><td>This level approved</td></tr>
                                <tr><td><span class="label label-danger">rejected</span></td><td>This level rejected (PO stops here)</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Purchase Order Statuses</h5>
                        <table class="table table-bordered table-condensed">
                            <thead><tr class="active"><th>Status</th><th>Meaning</th></tr></thead>
                            <tbody>
                                <tr><td><span class="label label-warning">pending_approval</span></td><td>Awaiting one or more approval levels</td></tr>
                                <tr><td><span class="label label-success">approved</span></td><td>All approvers signed off — GRN can be created</td></tr>
                                <tr><td><span class="label label-info">partially_received</span></td><td>At least one GRN submitted, not yet fully delivered</td></tr>
                                <tr><td><span class="label label-primary">received</span></td><td>All ordered quantities received</td></tr>
                                <tr><td><span class="label label-danger">rejected</span></td><td>Rejected in approval workflow</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Asset Statuses</h5>
                        <table class="table table-bordered table-condensed">
                            <thead><tr class="active"><th>Status</th><th>Meaning</th></tr></thead>
                            <tbody>
                                <tr><td><span class="label label-default">in_store</span></td><td>In store, not yet assigned</td></tr>
                                <tr><td><span class="label label-success">assigned</span></td><td>Assigned to a staff/dept custodian</td></tr>
                                <tr><td><span class="label label-warning">under_maintenance</span></td><td>Sent for repair/maintenance</td></tr>
                                <tr><td><span class="label label-danger">disposed</span></td><td>Written off / disposed</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================== DB TABLES ======================== -->
        <div class="box box-default" id="section-db">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-database"></i> 14. Database Tables Reference</h3></div>
            <div class="box-body">
                <table class="table table-bordered table-condensed table-striped">
                    <thead><tr class="active"><th>Table</th><th>Purpose</th><th>Key Columns</th></tr></thead>
                    <tbody>
                        <tr><td><code>item</code></td><td>Item master</td><td>id, item_name, item_code, category_id, unit</td></tr>
                        <tr><td><code>item_category</code></td><td>Item categories</td><td>id, category_name, <strong>is_asset</strong></td></tr>
                        <tr><td><code>item_store</code></td><td>Store/warehouse locations</td><td>id, store_name</td></tr>
                        <tr><td><code>item_supplier</code></td><td>Supplier master</td><td>id, supplier_name, contact, email</td></tr>
                        <tr><td><code>item_stock</code></td><td>Stock ledger (inward entries)</td><td>id, item_id, store_id, quantity, unit_price, date</td></tr>
                        <tr><td><code>item_issue</code></td><td>Item issue &amp; return</td><td>id, item_id, store_id, staff_id, quantity, is_returned</td></tr>
                        <tr><td><code>inv_indents</code></td><td>Purchase indent headers</td><td>id, department_id, raised_by, required_date, status</td></tr>
                        <tr><td><code>inv_indent_items</code></td><td>Indent line items</td><td>id, indent_id, item_id, quantity, justification</td></tr>
                        <tr><td><code>inv_purchase_orders</code></td><td>PO headers</td><td>id, indent_id, supplier_id, total_amount, tax_percent, status</td></tr>
                        <tr><td><code>inv_purchase_order_items</code></td><td>PO line items</td><td>id, po_id, item_id, quantity_ordered, unit_price</td></tr>
                        <tr><td><code>inv_po_approvals</code></td><td>PO approval rows (one per level)</td><td>id, po_id, approval_level, approver_staff_id, status, comments, decided_at</td></tr>
                        <tr><td><code>inv_po_approval_rules</code></td><td>Approval matrix rules</td><td>id, rule_name, department_id, min_amount, max_amount, approval_level, approver_type, approver_staff_id, approver_role_id, sort_order, is_active</td></tr>
                        <tr><td><code>inv_goods_receipts</code></td><td>GRN headers</td><td>id, po_id, challan_no, receipt_date, received_by</td></tr>
                        <tr><td><code>inv_goods_receipt_items</code></td><td>GRN line items</td><td>id, grn_id, item_id, store_id, received_qty, accepted_qty, rejected_qty, unit_price</td></tr>
                        <tr><td><code>inv_assets</code></td><td>Fixed asset register</td><td>id, asset_code, item_id, grn_id, store_id, status, purchase_date, unit_price</td></tr>
                        <tr><td><code>staff</code></td><td>Staff master</td><td>id, firstname, lastname, department, designation, is_active</td></tr>
                        <tr><td><code>staff_roles</code></td><td>Staff ↔ role mapping</td><td>staff_id, role_id</td></tr>
                        <tr><td><code>roles</code></td><td>Role master</td><td>id, role_name</td></tr>
                        <tr><td><code>department</code></td><td>Department master</td><td>id, department_name</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ======================== RBAC ======================== -->
        <div class="box box-default" id="section-rbac">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> 15. Permissions (RBAC)</h3></div>
            <div class="box-body">
                <p>All inventory modules currently share the <strong><code>item_stock</code></strong> privilege key. Grant the following to each role that needs access:</p>
                <table class="table table-bordered table-condensed">
                    <thead><tr class="active"><th>Privilege Key</th><th>Module(s) Protected</th><th>Levels Checked</th></tr></thead>
                    <tbody>
                        <tr><td><code>item_stock</code> — <code>can_view</code></td><td>All inventory modules (dashboard, PO list, GRN list, asset register)</td><td>View/List pages</td></tr>
                        <tr><td><code>item_stock</code> — <code>can_add</code></td><td>Create PO, Create GRN, Create Indent, Stock Inward, Issue Item</td><td>Submit forms</td></tr>
                        <tr><td><code>item_stock</code> — <code>can_edit</code></td><td>PO Approval decision, GRN decisions, asset updates</td><td>Decision/update forms</td></tr>
                    </tbody>
                </table>
                <div class="callout callout-info">
                    <strong>Recommended Practice:</strong> Create dedicated roles such as <em>Stores Manager</em> (can_add/can_edit for stock/PO/GRN), <em>Purchase Approver</em> (can_edit for PO approvals), and <em>HOD</em> (can_edit for indent approvals). Assign approver roles carefully — a staff member must have the correct role for the approval matrix engine to route POs to them automatically.
                </div>
            </div>
        </div>

        <!-- ======================== FAQ ======================== -->
        <div class="box box-warning" id="section-faq">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-question-circle"></i> 16. FAQ &amp; Troubleshooting</h3></div>
            <div class="box-body">

                <div class="panel-group" id="accordion-faq">

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq1">
                                    PO form shows "Approval matrix not yet configured" — what does this mean?
                                </a>
                            </h4>
                        </div>
                        <div id="faq1" class="panel-collapse collapse">
                            <div class="panel-body">
                                No active rows exist in <code>inv_po_approval_rules</code>. Either insert rules (see <a href="#section-matrix">Section 8</a>) or select approvers manually from the L1/L2 dropdowns.
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq2">
                                    The PO approval queue is empty but the PO says "pending_approval".
                                </a>
                            </h4>
                        </div>
                        <div id="faq2" class="panel-collapse collapse">
                            <div class="panel-body">
                                You are logged in as a user who is NOT the assigned L1 approver. Only the L1 approver sees the row. Check <code>inv_po_approvals</code> to confirm the <code>approver_staff_id</code> and ask that staff to log in.
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq3">
                                    The approval matrix did not auto-assign anyone even though rules exist.
                                </a>
                            </h4>
                        </div>
                        <div id="faq3" class="panel-collapse collapse">
                            <div class="panel-body">
                                Check: (1) Is <code>is_active = 1</code> on the rule? (2) Does the PO total fall within <code>min_amount</code>/<code>max_amount</code>? (3) For <code>approver_type = 'role'</code>: does at least one active staff member (<code>is_active=1</code>) have that role in <code>staff_roles</code>? (4) Is the indent's department matching (or is <code>department_id</code> NULL)?
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq4">
                                    GRN creation says "No items available to receive" for an approved PO.
                                </a>
                            </h4>
                        </div>
                        <div id="faq4" class="panel-collapse collapse">
                            <div class="panel-body">
                                All ordered quantities on the PO have already been received in full by prior GRNs. The PO status should already be <code>received</code>. If not, check <code>inv_goods_receipt_items</code> for the PO's items and compare ordered vs. total received.
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq5">
                                    Assets were not auto-created after accepting items on a GRN.
                                </a>
                            </h4>
                        </div>
                        <div id="faq5" class="panel-collapse collapse">
                            <div class="panel-body">
                                Check that the item's category has <code>is_asset = 1</code> in the <code>item_category</code> table. Assets are only created when <code>accepted_qty &gt; 0</code> for a line whose item category is flagged as an asset category.
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq6">
                                    I get "access denied" when opening any inventory page.
                                </a>
                            </h4>
                        </div>
                        <div id="faq6" class="panel-collapse collapse">
                            <div class="panel-body">
                                Your role has not been granted the <code>item_stock</code> privilege. Go to <strong>Admin → Roles &amp; Permissions</strong>, find your role, and enable <code>item_stock</code> View/Add/Edit as required.
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion-faq" href="#faq7">
                                    Can I edit or cancel a Purchase Order after it is approved?
                                </a>
                            </h4>
                        </div>
                        <div id="faq7" class="panel-collapse collapse">
                            <div class="panel-body">
                                Not through the current UI. The recommended workflow is: reject and create a new PO. A PO amendment workflow is planned for a future release. Avoid deleting records directly in the database as it will break audit trails.
                            </div>
                        </div>
                    </div>

                </div><!-- /accordion -->
            </div>
        </div>

        <!-- Back button -->
        <div class="box">
            <div class="box-body text-center">
                <a href="<?php echo site_url('admin/inventorydashboard'); ?>" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> Back to Inventory Dashboard
                </a>
            </div>
        </div>

    </section>
</div>
