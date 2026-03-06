<div class="row">
    <div class="col-md-12">
        <div class="box box-primary border0 mb0 margesection">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search"></i>  <?php echo $this->lang->line('human_resource_report'); ?></h3>
            </div>
            <div class="">
                <ul class="reportlists">
                    <?php if ($this->rbac->hasPrivilege('staff_report', 'can_view')) { ?>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_SubSubmenu('Reports/human_resource/staff_report'); ?>"><a href="<?php echo base_url(); ?>report/staff_report"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('staff_report'); ?></a></li>
                    <?php } ?>
                    <?php if ($this->rbac->hasPrivilege('staff_profile_completeness_report', 'can_view')) { ?>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_SubSubmenu('Reports/human_resource/staffprofilecompleteness'); ?>"><a href="<?php echo base_url(); ?>report/staffprofilecompleteness"><i class="fa fa-file-text-o"></i> Staff Profile Completeness Report</a></li>
                    <?php }
                    if (($this->rbac->hasPrivilege('payroll_report', 'can_view'))) {
                        ?>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_SubSubmenu('Reports/attendance/attendance_report'); ?>">
                            <a href="<?php echo base_url(); ?>admin/payroll/payrollreport"><i class="fa fa-file-text-o"></i> Paid Payroll Report</a>
                        </li>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_SubSubmenu('Reports/attendance/attendance_report'); ?>">
                            <a href="<?php echo base_url(); ?>admin/payroll/payrollreport_generated"><i class="fa fa-file-text-o"></i> Generated Payroll Report</a>
                        </li>
                    <?php } ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_SubSubmenu('Reports/human_resource/staff_birthday_list'); ?>"><a href="<?php echo base_url(); ?>report/staff_birthday_list"><i class="fa fa-birthday-cake"></i> Staff Birthday List</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>