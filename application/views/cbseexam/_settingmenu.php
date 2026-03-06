<div class="col-md-2">
    <div class="box border0">
        <ul class="tablists">
			<?php if ($this->rbac->hasPrivilege('cbse_exam_category', 'can_view')) {  ?>
            <li class="<?php echo set_SubSubmenu('cbseexam/cbsecategory/index'); ?>">
                <a class="<?php echo set_SubSubmenu('cbseexam/cbsecategory/index'); ?>" href="<?php echo site_url('cbseexam/cbsecategory/index') ?>"><?php echo $this->lang->line('exam_category'); ?></a>
            </li>			
			<?php } if ($this->rbac->hasPrivilege('cbse_exam_grade', 'can_view')) {  ?>
            <li class="<?php echo set_SubSubmenu('cbseexam/grade'); ?>">
                <a class="<?php echo set_SubSubmenu('cbseexam/grade'); ?>" href="<?php echo site_url('cbseexam/grade/gradelist') ?>"><?php echo $this->lang->line('exam_grade'); ?></a>
            </li>
			<?php } if ($this->rbac->hasPrivilege('cbse_exam_assessment', 'can_view')) {  ?>
            <li class="<?php echo set_SubSubmenu('cbseexam/assessment'); ?>">
                <a class="<?php echo set_SubSubmenu('cbseexam/assessment'); ?>" href="<?php echo site_url('cbseexam/assessment') ?>"><?php echo $this->lang->line('assessment'); ?></a>
            </li>
			<?php } if ($this->rbac->hasPrivilege('cbse_exam_term', 'can_view')) {  ?>
            <li class="<?php echo set_SubSubmenu('cbseexam/term'); ?>">
                <a class="<?php echo set_SubSubmenu('cbseexam/term'); ?>" href="<?php echo site_url('cbseexam/term/index') ?>"><?php echo $this->lang->line('term'); ?></a>
            </li>
			<?php } ?>
			
        </ul>
    </div>
</div><!--./col-md-3--> 