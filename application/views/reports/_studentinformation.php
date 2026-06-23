<style>
.report-nav { margin-bottom: 24px; }
.report-nav-header {
    display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
}
.report-nav-header .nav-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: linear-gradient(135deg, #5b73e8, #7c5ce7);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 16px;
}
.report-nav-header h3 {
    margin: 0; font-size: 18px; font-weight: 700; color: #2c3e50;
}
.report-tiles {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 10px;
}
.report-tile {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px; border-radius: 8px;
    background: #fff; border: 1px solid #eef0f3;
    text-decoration: none; color: #495057;
    font-size: 13px; font-weight: 500;
    transition: all .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.report-tile:hover {
    border-color: #5b73e8; background: #f5f7ff;
    color: #5b73e8; text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(91,115,232,.12);
}
.report-tile.active {
    background: linear-gradient(135deg, #5b73e8, #7c5ce7);
    color: #fff; border-color: transparent;
    box-shadow: 0 4px 12px rgba(91,115,232,.25);
}
.report-tile.active .tile-icon { background: rgba(255,255,255,.2); color: #fff; }
.tile-icon {
    width: 32px; height: 32px; border-radius: 7px;
    background: #f0f2ff; color: #5b73e8;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; flex-shrink: 0;
}
.report-tile:hover .tile-icon { background: #e0e5ff; }
</style>

<div class="report-nav">
    <div class="report-nav-header">
        <span class="nav-icon"><i class="fa fa-bar-chart"></i></span>
        <h3><?php echo $this->lang->line('student_information_report'); ?></h3>
    </div>
    <div class="report-tiles">
        <?php if ($this->rbac->hasPrivilege('student_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/studentreport" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_report'); ?>">
            <span class="tile-icon"><i class="fa fa-users"></i></span> <?php echo $this->lang->line('student_report'); ?>
        </a>
        <a href="<?php echo base_url(); ?>report/category_report" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/category_report'); ?>">
            <span class="tile-icon"><i class="fa fa-th-list"></i></span> <?php echo $this->lang->line('category_report'); ?>
        </a>
        <a href="<?php echo base_url(); ?>report/communitybasedreport" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/community_based_report'); ?>">
            <span class="tile-icon"><i class="fa fa-globe"></i></span> <?php echo $this->lang->line('community_based_report'); ?>
        </a>
        <a href="<?php echo site_url('report/classsectionreport'); ?>" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/classsectionreport'); ?>">
            <span class="tile-icon"><i class="fa fa-building"></i></span> <?php echo $this->lang->line('class_section_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('guardian_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/guardianreport" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/guardian_report'); ?>">
            <span class="tile-icon"><i class="fa fa-user-circle"></i></span> <?php echo $this->lang->line('guardian_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('student_history', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/admissionreport" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_history'); ?>">
            <span class="tile-icon"><i class="fa fa-history"></i></span> <?php echo $this->lang->line('student_history'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('student_login_credential_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/logindetailreport" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_login_credential'); ?>">
            <span class="tile-icon"><i class="fa fa-key"></i></span> <?php echo $this->lang->line('student_login_credential'); ?>
        </a>
        <a href="<?php echo base_url(); ?>report/parentlogindetailreport" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/parent_login_credential'); ?>">
            <span class="tile-icon"><i class="fa fa-lock"></i></span> <?php echo $this->lang->line('parent_login_credential'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('class_subject_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/class_subject" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/class_subject_report'); ?>">
            <span class="tile-icon"><i class="fa fa-book"></i></span> <?php echo $this->lang->line('class_subject_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('admission_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/admission_report" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/admission_report'); ?>">
            <span class="tile-icon"><i class="fa fa-user-plus"></i></span> <?php echo $this->lang->line('admission_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('sibling_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/sibling_report" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/sibling_report'); ?>">
            <span class="tile-icon"><i class="fa fa-link"></i></span> <?php echo $this->lang->line('sibling_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('student_profile', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/student_profile" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_profile'); ?>">
            <span class="tile-icon"><i class="fa fa-id-card"></i></span> <?php echo $this->lang->line('student_profile'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('student_gender_ratio_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/boys_girls_ratio" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/boys_girls_ratio'); ?>">
            <span class="tile-icon"><i class="fa fa-venus-mars"></i></span> <?php echo $this->lang->line('student_gender_ratio_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('student_teacher_ratio_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/student_teacher_ratio" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_teacher_ratio'); ?>">
            <span class="tile-icon"><i class="fa fa-balance-scale"></i></span> <?php echo $this->lang->line('student_teacher_ratio_report'); ?>
        </a>
        <?php } if ($this->rbac->hasPrivilege('online_admission_report', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>report/online_admission_report" class="report-tile <?php echo set_SubSubmenu('Reports/online_admission'); ?>">
            <span class="tile-icon"><i class="fa fa-laptop"></i></span> <?php echo $this->lang->line('online_admission_report'); ?>
        </a>
        <?php } ?>
        <a href="<?php echo base_url(); ?>admin/studentprofilecompleteness" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_profile_completeness'); ?>">
            <span class="tile-icon"><i class="fa fa-check-circle"></i></span> Student Profile Completeness
        </a>
        <?php if ($this->rbac->hasPrivilege('student_health_form', 'can_view')) { ?>
        <a href="<?php echo base_url(); ?>admin/studenthealthform" class="report-tile <?php echo set_SubSubmenu('Reports/student_information/student_health_form'); ?>">
            <span class="tile-icon"><i class="fa fa-heartbeat"></i></span> Student Health Forms
        </a>
        <?php } ?>
    </div>
</div>
