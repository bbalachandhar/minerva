<style>
.report-nav { margin-bottom: 24px; }
.report-nav-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.report-nav-header .nav-icon { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #5b73e8, #7c5ce7); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; }
.report-nav-header h3 { margin: 0; font-size: 18px; font-weight: 700; color: #2c3e50; }
.report-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; }
.report-tile { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 8px; background: #fff; border: 1px solid #eef0f3; text-decoration: none; color: #495057; font-size: 13px; font-weight: 500; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.report-tile:hover { border-color: #5b73e8; background: #f5f7ff; color: #5b73e8; text-decoration: none; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(91,115,232,.12); }
.report-tile.active { background: linear-gradient(135deg, #5b73e8, #7c5ce7); color: #fff; border-color: transparent; box-shadow: 0 4px 12px rgba(91,115,232,.25); }
.report-tile.active .tile-icon { background: rgba(255,255,255,.2); color: #fff; }
.tile-icon { width: 32px; height: 32px; border-radius: 7px; background: #f0f2ff; color: #5b73e8; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.report-tile:hover .tile-icon { background: #e0e5ff; }
</style>
<div class="report-nav">
    <div class="report-nav-header">
        <span class="nav-icon"><i class="fa fa-cubes"></i></span>
        <h3><?php echo $this->lang->line('inventory_report'); ?></h3>
    </div>
    <div class="report-tiles">
        <?php if ($this->rbac->hasPrivilege('stock_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/inventorystock" class="report-tile <?php echo set_SubSubmenu('Reports/inventory/inventorystock'); ?>">
            <span class="tile-icon"><i class="fa fa-archive"></i></span> <?php echo $this->lang->line('stock_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('add_item_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/additem" class="report-tile <?php echo set_SubSubmenu('Reports/inventory/additem'); ?>">
            <span class="tile-icon"><i class="fa fa-plus-square"></i></span> <?php echo $this->lang->line('add_item_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('issue_item_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/issueinventory" class="report-tile <?php echo set_SubSubmenu('Reports/inventory/issueinventory'); ?>">
            <span class="tile-icon"><i class="fa fa-share-square"></i></span> <?php echo $this->lang->line('issue_item_report'); ?>
        </a>
        <?php } ?>
    </div>
</div>
