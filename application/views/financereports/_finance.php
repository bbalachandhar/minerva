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
        <span class="nav-icon"><i class="fa fa-money"></i></span>
        <h3><?php echo $this->lang->line('finance'); ?></h3>
    </div>
    <div class="report-tiles">
        <?php if ($this->rbac->hasPrivilege('balance_fees_statement', 'can_view')) { ?>
        <a href="<?php echo site_url('financereports/reportduefees'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/finance/reportduefees'); ?>"><span class="tile-icon"><i class="fa fa-file-text-o"></i></span> <?php echo $this->lang->line('balance_fees_statement'); ?></a>
        <a href="<?php echo site_url('financereports/deleted_payments_report'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/finance/deleted_payments_report'); ?>"><span class="tile-icon"><i class="fa fa-trash-o"></i></span> <?php echo $this->lang->line('deleted_payments_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('daily_collection_report', 'can_view')) { ?>
        <a href="<?php echo site_url('financereports/reportdailycollection'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/finance/reportdailycollection'); ?>"><span class="tile-icon"><i class="fa fa-calendar-check-o"></i></span> <?php echo $this->lang->line('daily_collection_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('fees_statement', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/reportbyname" class="report-tile <?php echo set_SubSubmenu('Reports/finance/reportbyname'); ?>"><span class="tile-icon"><i class="fa fa-user"></i></span> <?php echo $this->lang->line('fees_statement'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('balance_fees_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/studentacademicreport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/studentacademicreport'); ?>"><span class="tile-icon"><i class="fa fa-balance-scale"></i></span> <?php echo $this->lang->line('balance_fees_report'); ?></a>
        <a href="<?php echo base_url(); ?>customfinancereports/custombalancefeesreport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/custombalancefeesreport'); ?>"><span class="tile-icon"><i class="fa fa-sliders"></i></span> Custom Balance Fees Report</a>
        <a href="<?php echo base_url(); ?>financereports/balancesummaryreport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/balancesummaryreport'); ?>"><span class="tile-icon"><i class="fa fa-pie-chart"></i></span> Balance Summary Report</a>
        <a href="<?php echo base_url(); ?>financereports/categorywisebalancefeesreport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/categorywisebalancefeesreport'); ?>"><span class="tile-icon"><i class="fa fa-th-list"></i></span> Category wise balance fees report</a>
        <?php } if ($this->rbac->hasPrivilege('balance_report_between_dates', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>customfinancereports/balancereportbetweendates" class="report-tile <?php echo set_SubSubmenu('Reports/finance/balancereportbetweendates'); ?>"><span class="tile-icon"><i class="fa fa-calendar"></i></span> Balance Report Between Dates</a>
        <?php } if ($this->rbac->hasPrivilege('fees_collection_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/collection_report" class="report-tile <?php echo set_SubSubmenu('Reports/finance/collection_report'); ?>"><span class="tile-icon"><i class="fa fa-line-chart"></i></span> <?php echo $this->lang->line('fees_collection_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('online_fees_collection_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/onlinefees_report" class="report-tile <?php echo set_SubSubmenu('Reports/finance/onlinefees_report'); ?>"><span class="tile-icon"><i class="fa fa-globe"></i></span> <?php echo $this->lang->line('online_fees_collection_report'); ?></a>
        <a href="<?php echo base_url(); ?>financereports/online_fee_pending_report" class="report-tile <?php echo set_SubSubmenu('Reports/finance/online_fee_pending_report'); ?>"><span class="tile-icon"><i class="fa fa-clock-o"></i></span> <?php echo $this->lang->line('online_fee_pending_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('balance_fees_report_with_remark', 'can_view')) { ?>
        <a href="<?php echo base_url('financereports/duefeesremark'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/finance/duefeesremark'); ?>"><span class="tile-icon"><i class="fa fa-comment-o"></i></span> <?php echo $this->lang->line('balance_fees_report_with_remark'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('incidental_fee_report', 'can_view')) { ?>
        <a href="<?php echo site_url('financereports/incidental_fee_report'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/finance/incidental_fee_report'); ?>"><span class="tile-icon"><i class="fa fa-ticket"></i></span> <?php echo $this->lang->line('incidental_fee_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('income_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/income" class="report-tile <?php echo set_SubSubmenu('Reports/finance/income'); ?>"><span class="tile-icon"><i class="fa fa-arrow-circle-down"></i></span> <?php echo $this->lang->line('income_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('expense_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/expense" class="report-tile <?php echo set_SubSubmenu('Reports/finance/expense'); ?>"><span class="tile-icon"><i class="fa fa-arrow-circle-up"></i></span> <?php echo $this->lang->line('expense_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('payroll_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/payroll" class="report-tile <?php echo set_SubSubmenu('Reports/finance/payroll'); ?>"><span class="tile-icon"><i class="fa fa-briefcase"></i></span> <?php echo $this->lang->line('payroll_report'); ?></a>
        <a href="<?php echo base_url(); ?>financereports/payrollreportsummary" class="report-tile <?php echo set_SubSubmenu('Reports/finance/payrollreportsummary'); ?>"><span class="tile-icon"><i class="fa fa-list-alt"></i></span> Payroll Report Summary</a>
        <a href="<?php echo base_url(); ?>financereports/payrollbankcopy" class="report-tile <?php echo set_SubSubmenu('Reports/finance/payrollbankcopy'); ?>"><span class="tile-icon"><i class="fa fa-bank"></i></span> Payroll Bank Copy</a>
        <a href="<?php echo base_url(); ?>financereports/epfreport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/epfreport'); ?>"><span class="tile-icon"><i class="fa fa-shield"></i></span> <?php echo $this->lang->line('epf_report'); ?></a>
        <a href="<?php echo base_url(); ?>financereports/esireport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/esireport'); ?>"><span class="tile-icon"><i class="fa fa-medkit"></i></span> <?php echo $this->lang->line('esi_report'); ?></a>
        <a href="<?php echo base_url(); ?>financereports/salaryabstract" class="report-tile <?php echo set_SubSubmenu('Reports/finance/salaryabstract'); ?>"><span class="tile-icon"><i class="fa fa-table"></i></span> <?php echo $this->lang->line('salary_abstract_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('income_group_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/incomegroup" class="report-tile <?php echo set_SubSubmenu('Reports/finance/incomegroup'); ?>"><span class="tile-icon"><i class="fa fa-folder-o"></i></span> <?php echo $this->lang->line('income_group_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('expense_group_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/expensegroup" class="report-tile <?php echo set_SubSubmenu('Reports/finance/expensegroup'); ?>"><span class="tile-icon"><i class="fa fa-folder"></i></span> <?php echo $this->lang->line('expense_group_report'); ?></a>
        <?php } if ($this->rbac->hasPrivilege('online_admission', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>financereports/onlineadmission" class="report-tile <?php echo set_SubSubmenu('Reports/finance/onlineadmission'); ?>"><span class="tile-icon"><i class="fa fa-laptop"></i></span> <?php echo $this->lang->line('online_admission_fees_collection_report'); ?></a>
        <?php } ?>
        <a href="<?php echo base_url(); ?>financereports/incomeexpensebalancereport" class="report-tile <?php echo set_SubSubmenu('Reports/finance/incomeexpensebalancereport'); ?>"><span class="tile-icon"><i class="fa fa-exchange"></i></span> <?php echo $this->lang->line('income_expense_balance_report'); ?></a>
    </div>
</div>
