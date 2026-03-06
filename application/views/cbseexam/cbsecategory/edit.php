<?php $this->load->view('layout/cbseexam_css.php'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-mortar-board"></i>  <small></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
		<?php $this->load->view('cbseexam/_settingmenu'); ?>
		<div class="col-md-10">
            <?php if ($this->rbac->hasPrivilege('cbse_exam_category', 'can_edit')) {  ?>
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"> <?php echo $this->lang->line('edit_category'); ?>  </h3>
                        </div> 
                        <form action="<?php echo site_url('cbseexam/cbsecategory/edit/'.$category_data->id) ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                            <div class="box-body">
                           <input type="hidden" name="pre_category_id" value="<?php echo $category_data->id; ?>">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg') ?>
                                <?php } ?>  
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"> <?php echo $this->lang->line('category'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="category_name" name="category_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('category_name',$category_data->name); ?>" />
                                    <span class="text-danger"><?php echo form_error('category_name'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>  
                </div>   
            <?php } ?>
            
			<div class="col-md-<?php
            if ($this->rbac->hasPrivilege('cbse_exam_category', 'can_add')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">			
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('category_list'); ?></h3>
                    </div>
                    <div class="box-body ">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo $this->lang->line('category_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('category'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>                                   

                                    <?php
                                    $count = 1;
                                    foreach ($category_data_list as $value) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name"> <?php echo $value->name; ?></td>
                                            <td class="mailbox-date pull-right">
												<?php if ($this->rbac->hasPrivilege('cbse_exam_category', 'can_edit')) {  ?>
                                                    <a href="<?php echo base_url(); ?>cbseexam/cbsecategory/edit/<?php echo $value->id; ?>" class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
												<?php } if ($this->rbac->hasPrivilege('cbse_exam_category', 'can_edit')) {  ?>
                                                    <a href="<?php echo base_url(); ?>cbseexam/cbsecategory/delete/<?php echo $value->id; ?>"class="btn btn-primary btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('are_you_sure_want_to_delete'); ?>');"> <i class="fa fa-remove"></i>
                                                    </a>
												<?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    $count++;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
			
        </div> 
        </div> 
    </section>
</div>