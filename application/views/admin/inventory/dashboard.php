<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-cubes"></i> Inventory Dashboard
            <a href="<?php echo site_url('admin/inventorydashboard/guide'); ?>"
               class="btn btn-info btn-sm"
               style="margin-left:12px; vertical-align:middle;"
               title="How to use this system — end-to-end guide">
                <i class="fa fa-info-circle"></i> How to Use This System
            </a>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-info">
                    <h4 style="margin-top:0;">New: Inventory System Usage Guide</h4>
                    <p style="margin-bottom:10px;">
                        Open the end-to-end guide for PO approvals, approval matrix setup, GRN flow, stock inward, asset auto-creation, status flows, database references, and troubleshooting.
                    </p>
                    <a class="btn btn-info btn-sm" href="<?php echo site_url('admin/inventorydashboard/guide'); ?>">
                        <i class="fa fa-info-circle"></i> Open Complete Guide
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo (int) ($kpis['item_count'] ?? 0); ?></h3>
                        <p>Total Item Masters</p>
                    </div>
                    <div class="icon"><i class="fa fa-list"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo (int) ($kpis['stock_inward_qty'] ?? 0); ?></h3>
                        <p>Total Inward Quantity</p>
                    </div>
                    <div class="icon"><i class="fa fa-download"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3><?php echo (int) ($kpis['stock_inward_entries'] ?? 0); ?></h3>
                        <p>Total Inward Entries</p>
                    </div>
                    <div class="icon"><i class="fa fa-database"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo (int) ($kpis['issued_not_returned_qty'] ?? 0); ?></h3>
                        <p>Issued (Not Returned)</p>
                    </div>
                    <div class="icon"><i class="fa fa-share-square-o"></i></div>
                </div>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Links</h3>
            </div>
            <div class="box-body">
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/item'); ?>">Items</a>
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/itemcategory'); ?>">Categories</a>
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/itemstock'); ?>">Stock Inward</a>
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/issueitem'); ?>">Issue / Return</a>
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/inventoryindent'); ?>">Indents</a>
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/inventoryprocurement/purchaseorders'); ?>">Purchase Orders</a>
                <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/assetmanagement/register'); ?>">Asset Register</a>
                <a class="btn btn-info btn-sm" href="<?php echo site_url('admin/inventorydashboard/guide'); ?>">System Guide</a>
            </div>
        </div>
    </section>
</div>
