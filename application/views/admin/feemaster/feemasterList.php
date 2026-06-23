<style>
.liststyle1 { margin:0; list-style:none; line-height:28px; }
.fm-panel { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:20px; overflow:visible; }
.fm-panel-header { background:linear-gradient(135deg,#5b73e8 0%,#7c5ce7 100%); color:#fff; padding:14px 20px; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:space-between; }
.fm-panel-header h3 { margin:0; font-size:15px; font-weight:600; color:#fff; }
.fm-panel-header h3 i { margin-right:8px; }
.fm-panel-body { padding:18px; }
.fm-label { font-weight:600; font-size:13px; color:#495057; margin-bottom:5px; display:block; }
.fm-label .req { color:#e74c3c; }
.fm-select, .fm-input { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:9px 12px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; }
.fm-select:focus, .fm-input:focus { border-color:#5b73e8; outline:none; background:#fff; }
.fm-radio-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.fm-radio-item { display:flex; align-items:center; gap:6px; font-size:13px; color:#495057; cursor:pointer; padding:5px 8px; border-radius:6px; transition:background .15s; }
.fm-radio-item:hover { background:#f0f2ff; }
.fm-radio-item input[type=radio] { accent-color:#5b73e8; }
.btn-fm-save { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px; }
.btn-fm-save:hover { opacity:.9; transform:translateY(-1px); }
.fm-list-header { padding:14px 20px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
.fm-list-header h4 { margin:0; font-size:16px; font-weight:700; color:#2c3e50; }
.fm-list-header h4 i { margin-right:6px; color:#5b73e8; }
.btn-fm-upload { background:linear-gradient(135deg,#3498db,#2980b9); color:#fff; border:none; border-radius:6px; padding:7px 16px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; text-decoration:none; }
.btn-fm-upload:hover { opacity:.9; color:#fff; text-decoration:none; }
.btn-action { width:28px; height:28px; border-radius:5px; border:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; font-size:12px; text-decoration:none; }
.btn-action-edit { background:#e8f4fd; color:#3498db; }
.btn-action-edit:hover { background:#3498db; color:#fff; }
.btn-action-delete { background:#fde8e8; color:#e74c3c; }
.btn-action-delete:hover { background:#e74c3c; color:#fff; }
.btn-action-assign { background:#e8fdf0; color:#27ae60; }
.btn-action-assign:hover { background:#27ae60; color:#fff; }
</style>

<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('fees_master', 'can_add')) { ?>
                <div class="col-md-3">
                    <div class="fm-panel">
                        <div class="fm-panel-header">
                            <h3><i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('add_fees_master') . " : " . $this->setting_model->getCurrentSessionName(); ?></h3>
                        </div>
						<form id="form1" action="<?php echo base_url() ?>admin/feemaster/save_data"  name="feemasterform" method="post" accept-charset="utf-8">
                            <div class="fm-panel-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php 
                                        echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg');
                                    ?>
                                <?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_group'); ?></label> <small class="req">*</small>
                                            <select autofocus="" id="fee_groups_id" name="fee_groups_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
                                                $count = 0; // Initialize count
                                                foreach ($feegroupList as $feegroup) {
                                                    ?>
                                                    <option value="<?php echo $feegroup['id'] ?>"<?php
                                                    if (set_value('fee_groups_id') == $feegroup['id']) {
                                                        echo "selected =selected";
                                                    }
                                                    ?>><?php echo $feegroup['name'] ?></option>

                                                    <?php
                                                    $count++;
                                                }
                                                ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('fee_groups_id'); ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_type'); ?></label><small class="req"> *</small>
                                            <select  id="feetype_id" name="feetype_id" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
                                                $count = 0; // Initialize count
                                                foreach ($feetypeList as $feetype) {
                                                    ?>
                                                    <option value="<?php echo $feetype['id'] ?>"<?php
                                                    if (set_value('feetype_id') == $feetype['id']) {
                                                        echo "selected =selected";
                                                    }
                                                    ?>><?php echo $feetype['type'] ?></option>

                                                    <?php
                                                    $count++;
                                                }
                                                ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('feetype_id'); ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('due_date'); ?></label><small class="req" id="due_date_error"> </small>
                                            <input id="due_date" name="due_date" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('due_date'); ?>" />
                                            <span class="text-danger"><?php echo form_error('due_date'); ?></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>)</label><small class="req"> *</small>
                                            <input id="amount" name="amount" placeholder="" type="text" class="form-control"  value="<?php echo set_value('amount'); ?>" />
                                            <span class="text-danger"><?php echo form_error('amount'); ?></span>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label for="input-type"><?php echo $this->lang->line('fine_type'); ?></label>
                                                <div id="input-type" class="row">
                                                    <div class="col-sm-6 col-lg-6 col-md-12">
                                                        <label class="radio-inline">
                                                            <input name="account_type" class="finetype" id="input-type-student" value="none" type="radio" <?php echo set_radio('account_type', 'none', true); ?>/><?php echo $this->lang->line('none') ?>
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-6 col-lg-6 col-md-12">
                                                        <label class="radio-inline">
                                                            <input name="account_type" class="finetype" id="input-type-student" value="percentage" type="radio" <?php echo set_radio('account_type', 'percentage', set_value('percentage')); ?> /><?php echo $this->lang->line('percentage'); ?>
                                                        </label>
                                                    </div>
                                                </div>
												<div id="input-type" class="row">                                                    
                                                    <div class="col-sm-6 col-lg-6 col-md-12">
                                                        <label class="radio-inline">
                                                            <input name="account_type" class="finetype" id="input-type-tutor" value="fix" type="radio"  <?php echo set_radio('account_type', 'fix', set_value('fix')); ?> />
                                                            <?php echo $this->lang->line('fix_amount'); ?>
                                                        </label>
                                                    </div>													
                                                    <div class="col-sm-6 col-lg-6 col-md-12">
                                                        <label class="radio-inline">
                                                            <input name="account_type" class="finetype" id="input-type-tutor" value="cumulative" type="radio"  <?php echo set_radio('account_type', 'cumulative', set_value('cumulative')); ?> />
                                                            <?php echo $this->lang->line('cumulative'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-lg-6 col-md-12" id="percentage_input">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('percentage') ?> (%)</label><small class="req"> *</small>
                                            <input id="fine_percentage" name="fine_percentage" placeholder="" type="text" class="form-control"  value="<?php echo set_value('fine_percentage'); ?>" />
                                            <span class="text-danger"><?php echo form_error('fine_percentage'); ?></span>
                                        </div>
                                    </div>   
                                    <div class="col-sm-6 col-lg-6 col-md-12" id="fix_amount_input">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('fix_amount'); ?> (<?php echo $currency_symbol; ?>)</label><small class="req"> *</small>
                                            <input id="fine_amount" name="fine_amount" placeholder="" type="text" class="form-control"  value="<?php echo set_value('fine_amount'); ?>" />
                                            <span class="text-danger"><?php echo form_error('fine_amount'); ?></span>
                                        </div>
                                    </div>
									<div class="col-md-12" id="cumulative_table" hidden>
                                        <input type="hidden" name="count" id="count" value="0">
                                       
                                        <table class="table table-striped table-bordered table-hover" border="1px" style="border-color: red;">
                                            <thead>
                                                <tr>
                                                    <th colspan="2">
                                                    <div class="form-group">
                                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('per_day'); ?></label>      
                                                        <div class="material-switch ">
                                                          <input id="fine_per_day" name="fine_per_day" type="checkbox" class="chk" value="checked" />
                                                          <label for="fine_per_day" class="label-success"></label>
                                                        </div>
                                                    </div>
                                                    </th>
                                                    <th><button type="button" class="btn btn-info pull-right btn-xs" onclick="add_row()"><?php echo $this->lang->line('add_fine'); ?></button></th>
                                                </tr>

                                                <tr>
                                                    <th width="45%"><?php echo $this->lang->line('total_overdue'); ?></th>
                                                    <th width="45%"><?php echo $this->lang->line('fine_amount'); ?></th>
                                                    <th width="10%"><?php echo $this->lang->line('action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="finetable">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!-- /.box-body -->
                            <div style="padding:0 18px 18px; text-align:right;">
                                <button type="submit" class="btn-fm-save"><i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('fees_master', 'can_add')) {
                echo "9";
            } else {
                echo "12";
            }
            ?>">
                <div class="fm-panel">
                    <div class="fm-list-header">
                        <h4><i class="fa fa-list-alt"></i> <?php echo $this->lang->line('fees_master_list') . " : " . $this->setting_model->getCurrentSessionName(); ?></h4>
                        <a href="<?php echo base_url(); ?>admin/feemaster/bulk_import" class="btn-fm-upload"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_import'); ?></a>
                    </div>
                    <div style="padding:12px;">
                        <div class="download_label"><?php echo $this->lang->line('fees_master_list') . " : " . $this->setting_model->getCurrentSessionName(); ?></div>
                        <div class="mailbox-messages">
                            <div class="table-responsive">  
                                <table class="table table-striped table-bordered table-hover example1">
                                                                                                    <thead>
                                                                                                        <tr>
                                                                                                            <th width="15%"><?php echo $this->lang->line('fees_group'); ?></th>
                                                                                                            <th width="85%">
                                                                                                                <div class="row">
                                                                                                                    <div class="col-md-2 col-lg-2 col-sm-2 col-xs-3">
                                                                                                                        <?php echo $this->lang->line('fees_code'); ?>
                                                                                                                    </div>
                                                                                                                    <div class="col-md-1 col-lg-1 col-sm-1 col-xs-2">
                                                                                                                         <?php echo $this->lang->line('amount'); ?> 
                                                                                                                    </div>
                                                                                                                    <div class="col-md-3 col-lg-3 col-sm-3 col-xs-2">
                                                                                                                         <div class="px-md-2-5 px-lg-2-5"><?php echo $this->lang->line('fine_type'); ?></div>
                                                                                                                    </div>
                                                                                                                    <div class="col-md-2 col-lg-2 col-sm-2 col-xs-1">
                                                                                                                        <?php echo $this->lang->line('due_date'); ?> 
                                                                                                                    </div> 
                                                                                                                    <div class="col-md-1 col-lg-1 col-sm-1 col-xs-1">
                                                                    													<?php echo $this->lang->line('per_day'); ?>
                                                                                                                    </div>
                                                                                                                    <div class="col-md-2 col-lg-2 col-sm-2 col-xs-2">
                                                                                                                         <?php echo $this->lang->line('days')."-".$this->lang->line('fine_amount'); ?>
                                                                                                                    </div>
                                                                                                                    <div class="col-md-1 col-lg-1 col-sm-1 col-xs-1 white-space-nowrap">&nbsp;</div>
                                                                    
                                                                                                                </div>
                                                                                                                  </th>                                            
                                                                                                            <th class=""><?php echo $this->lang->line('action'); ?></th>
                                                                                                        </tr>
                                                                                                    </thead>
                                                                                                    <tbody>
                                                                                                        <?php
                                                                                                        foreach ($feemasterList as $feegroup) {
                                                                                                            ?>
                                                                                                            <tr>
                                                                                                                <td class="mailbox-name">
                                                                                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $feegroup->group_name; ?></a>
                                                                                                                </td>
                                                                                                                <td class="mailbox-name">
                                                                                                                    <ul class="liststyle1 min-w-sm-1000">
                                                                                                                            <?php
                                                                                                                            foreach ($feegroup->feetypes as $feetype_key => $feetype_value) {
                                                                                                                                ?>
                                                                                                                                <li> 
                                                                                                                                    <div class="row">                                                                    
                                                                                                                                        <div class="col-md-2 col-lg-2 col-sm-2 col-xs-3"> 
                                                                                                                                            <i class="fa fa-money"></i>
                                                                                                                                            <?php echo $feetype_value->type."(".$feetype_value->code.")"; ?>
                                                                                                                                        </div>                                                                   
                                                                                                                                        <div class="col-md-1 col-lg-1 col-sm-1 col-xs-2"> 
                                                                    																		<?php echo $currency_symbol.amountFormat($feetype_value->amount); ?>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-3 col-lg-3 col-sm-3 col-xs-2">
                                                                                                                                            <div class="px-md-2-5 px-lg-2-5"><?php echo $this->lang->line($feetype_value->fine_type);?></div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-2 col-lg-2 col-sm-2 col-xs-1">
                                                                                                                                            <?php  echo $this->customlib->dateformat($feetype_value->due_date); ?>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-1 col-lg-1 col-sm-1 col-xs-1">
                                                                                                                                            <?php if($feetype_value->fine_per_day==1){ echo $this->lang->line('yes'); }else{ echo $this->lang->line('no');} ?>
                                                                                                                                        </div>
                                                                                                                                        <!-- show data on table new code section -->
                                                                                                                                        <div class="col-md-2 col-lg-2 col-sm-2 col-xs-2">
                                                                                                                                        <?php 
                                                                                                                                        if($feetype_value->fine_type=='cumulative'){  																	
                                                                                                                                            foreach ($feetype_value->cumulative_fine_data as $fine_key=>$fine_value) {
                                                                    																			echo "Days: ".$fine_value->overdue_day." - Fine: ".$currency_symbol.''.$fine_value->fine_amount; echo "<br>"; 
                                                                    																		}  
                                                                    																	}else {  
                                                                    																		echo "Fine: ".$feetype_value->fine_amount ; 
                                                                    																	}  
                                                                    																	?>
                                                                    																	</div>
                                                                    
                                                                    																	<!-- show data on table new code section -->
                                                                                                                                   
                                                                    																	<div class="col-md-1 col-lg-1 col-sm-1 col-xs-1 white-space-nowrap">
                                                                    
                                                                                                                                        <?php if ($this->rbac->hasPrivilege('fees_master', 'can_edit')) {   ?>
                                                                    																		<a href="<?php echo base_url(); ?>admin/feemaster/edit/<?php echo $feetype_value->id ?>" class="btn-action btn-action-edit" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>&nbsp;
                                                                                                                                        <?php }	if ($this->rbac->hasPrivilege('fees_master', 'can_delete')) {  ?>
                                                                    																		<a href="<?php echo base_url(); ?>admin/feemaster/delete/<?php echo $feetype_value->id ?>" class="btn-action btn-action-delete" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');"><i class="fa fa-trash"></i></a>
                                                                    																	<?php } ?>
                                                                    
                                                                    																</div>                                                           
                                                                                                                                        
                                                                                                                                    </div>
                                                                                                                                 
                                                                                                                                </li>
                                                                                                                                <?php
                                                                                                                            }
                                                                                                                            ?>
                                                                                                                        </ul>
                                                                                                                    </td>
                                                                                                                    <td class="mailbox-date pull-right">
                                                                                                                        <?php if ($this->rbac->hasPrivilege('fees_group_assign', 'can_view')) { ?>
                                                                                                                            <a href="<?php echo base_url(); ?>admin/feemaster/assign/<?php echo $feegroup->id ?>"
                                                                                                                               class="btn-action btn-action-assign" data-toggle="tooltip" title="<?php echo $this->lang->line('assign_view_student'); ?>">
                                                                                                                                <i class="fa fa-tag"></i>
                                                                                                                            </a>
                                                                                                                        <?php } ?>
                                                                                                                        <?php if ($this->rbac->hasPrivilege('fees_master', 'can_delete')) { ?>
                                                                                                                            <a href="<?php echo base_url(); ?>admin/feemaster/deletegrp/<?php echo $feegroup->id ?>" class="btn-action btn-action-delete" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                                                                                <i class="fa fa-trash"></i>
                                                                                                                            </a>
                                                                                                                        <?php } ?>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                                <?php
                                                                                                            }
                                                                                                            ?>
                                                                                                    </tbody>                                </table><!-- /.table -->
                            </div>  
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                    </form>
                </div>
            </div><!--/.col (right) -->
            <!-- left column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    $(document).ready(function () {
        var account_type = "<?php echo set_value('account_type', 0); ?>";
        load_disable(account_type);
    });

    $(document).on('change', '.finetype', function () {
        calculatefine();
    });

    $(document).on('keyup', '#amount,#fine_percentage', function () {
		var finetype = $('input[name=account_type]:checked', '#form1').val();
		if (finetype === "percentage" || finetype === "fix") {
			calculatefine();
		}
    });

    function load_disable(account_type) {
        if (account_type === "percentage") {
            $('#due_date_error').html(' *');
            $('#fine_amount').prop('readonly', true);
            $('#fine_percentage').prop('readonly', false);
        } else if (account_type === "fix") {
             $('#due_date_error').html(' *');
            $('#fine_amount').prop('readonly', false);
            $('#fine_percentage').prop('readonly', true);
        } else {
            $('#due_date_error').html('');
            $('#fine_amount').prop('readonly', true);
            $('#fine_percentage').prop('readonly', true);
        }
    }

    function calculatefine() {
        var amount = $('#amount').val();
        var fine_percentage = $('#fine_percentage').val();
        var finetype = $('input[name=account_type]:checked', '#form1').val();

        if (finetype === "percentage") {
            $('#due_date_error').html(' *');
            fine_amount = ((amount * fine_percentage) / 100).toFixed(2);
            $('#fine_amount').val(fine_amount).prop('readonly', true);
            $('#fine_percentage').prop('readonly', false);

            $("#percentage_input").show();
            $("#fix_amount_input").show();
            $("#cumulative_table").hide();

        } else if (finetype === "fix") {
            $('#due_date_error').html(' *');
            $('#fine_amount').val("").prop('readonly', false);
            $('#fine_percentage').val("").prop('readonly', true);

            $("#percentage_input").show();
            $("#fix_amount_input").show();
            $("#cumulative_table").hide();

        } else if (finetype === "cumulative") {
            $('#due_date_error').html(' *');
            $('#fine_amount').val("").prop('readonly', false);
            $('#fine_percentage').val("").prop('readonly', true);

            $("#percentage_input").hide();
            $("#fix_amount_input").hide();
            $("#cumulative_table").show();
           
            $("#finetable").empty();
            counter =[];
            $("#count").val(0);
            add_row();

        } else {
            $('#due_date_error').html('');
            $('#fine_amount').val("");
            $('#fine_percentage').val("");
            $('#fine_amount').prop('readonly', true);
            $('#fine_percentage').prop('readonly', true);

            $("#percentage_input").show();
            $("#fix_amount_input").show();
            $("#cumulative_table").hide();

            $("#finetable").empty();
            counter =[];
            $("#count").val(0);
        }
    }

    $(document).ready(function () {
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });

          calculatefine();
    });


// add row function //
    var counter =[];
    var count=0;
    function add_row(){
        var num_count=$("#count").val();
        $("#count").val(parseInt(num_count)+parseInt(1));
        var count=$("#count").val();
        counter.push(parseInt(count));
        var over_due="";
        $("#finetable").append(`
            <tr id="row_${count}">
            <td><input type="text" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))" id="days_overdues_${count}" name="overdue_day[]"   value="" class="form-control"></td>    
            <td><input type="text" id="fine_amount_${count}" name="overdue_fine[]"  value="" class="form-control"></td>    
            <td><center><i class="fa fa-remove cursor"  onclick="remove_row(${count})" ></i></center></td>    
            </tr>
            `);
    }

    function remove_row(count){
        const index = counter.indexOf(count);
        if (index > -1) { 
            counter.splice(index, 1); 
            $("#row_"+count).remove();
        }
    }

    $("#form1").submit(function (e) {
            e.preventDefault(); // avoid to execute the actual submit of the form.
            var url = $(this).attr("action");
            // var $this = $('.video_submit');

            $.ajax({
                type: "POST",
                url: url,
                dataType: 'json',
                cache: false,
                contentType: false,
                 processData: false,
                  data: new FormData(this),
                beforeSend: function () {
                },
                success: function (data) {
                 if (!data.status) {
                   var message = "";
                   $.each(data.error, function (index, value) {
                   message += value;
                   });
                   errorMsg(message);
                   } else {
                    successMsg(data.msg);
                    location.reload();
              }

                },
                error: function (xhr) { // if error occured
                    // $this.button('reset');
                },
                complete: function () {
                    // $this.button('reset');
                },
            });
        });
        $(document).ready(function () {
      $('.example1').DataTable({
            "aaSorting": [],           
            "aoColumnDefs": [{ "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'},{ "bSortable": false, "aTargets": [ 1 ]}],
            rowReorder: {
            selector: 'td:nth-child(2)'
            },
      
            dom: "Bfrtip",
            buttons: [

                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    title: $('.download_label').html(),
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: $('.download_label').html(),
                    action: function ( e, dt, button, config ) {
                        var exportData = getExportData(dt);
                        config.customizeData = function ( data ) {
                            data.header = exportData.headers;
                            data.body = exportData.body;
                        };
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call( this, e, dt, button, config );
                    },
                    exportOptions: {
                        // This will be overridden by customizeData
                        columns: [], 
                        format: {
                            header: function ( data, columnIdx ) {
                                // This will be overridden by customizeData
                                return data;
                            },
                            body: function ( data, rowIdx, colIdx, node ) {
                                // This will be overridden by customizeData
                                return data;
                            }
                        }
                    }
                },

                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    title: $('.download_label').html(),
                     exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
                },

         

                {
                  extend:    'pdf',
                  text:      '<i class="fa fa-file-pdf-o"></i>',
                  titleAttr: 'PDF',
                  className: "btn-pdf",
                  title: $('.download_label').html(),
                    exportOptions: {
                      
                      columns: ["thead th:not(.noExport)"]
                    },
  
              },


                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $('.download_label').html(),
                 customize: function ( win ) {

                    $(win.document.body).find('th').addClass('display').css('text-align', 'center');
                    $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                    $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                    // $(win.document.body).find('table').addClass('display').css('text-align', 'center');
                    $(win.document.body).find('h1').css('text-align', 'center');
                },
                     exportOptions: {
                      stripHtml:false,
                    columns: ["thead th:not(.noExport)"]
                  }
                },

                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    titleAttr: 'Columns',
                    title: $('.download_label').html(),
                    postfixButtons: ['colvisRestore']
                },
            ]
        });
    });
</script>

<script>
    var feemasterData = <?php echo json_encode($feemasterList); ?>;
    // console.log(feemasterData); // For debugging

    function getExportData(dt) {
        var allFeeTypes = {};
        feemasterData.forEach(function(feegroup) {
            feegroup.feetypes.forEach(function(feetype) {
                allFeeTypes[feetype.type] = true; // Collect unique fee type names
            });
        });

        var dynamicHeaders = ['Fee Group'];
        var uniqueFeeTypeNames = Object.keys(allFeeTypes).sort();

        uniqueFeeTypeNames.forEach(function(feeTypeName) {
            dynamicHeaders.push(feeTypeName + ' Type');
            dynamicHeaders.push(feeTypeName + ' Amount');
        });

        var exportBody = [];
        feemasterData.forEach(function(feegroup) {
            var rowData = {};
            rowData['Fee Group'] = feegroup.group_name;

            feegroup.feetypes.forEach(function(feetype) {
                var prefix = feetype.type; // Use fee type name as prefix
                rowData[prefix + ' Type'] = feetype.type + '(' + feetype.code + ')';
                rowData[prefix + ' Amount'] = '₹' + amountFormat(feetype.amount);
            });

            // Ensure all dynamic headers are present in rowData, fill with empty string if not
            var finalRow = [];
            dynamicHeaders.forEach(function(header) {
                finalRow.push(rowData[header] || '');
            });
            exportBody.push(finalRow);
        });

        return {
            headers: dynamicHeaders,
            body: exportBody
        };
    }

    // Helper functions (assuming they exist in customlib or globally)
    function amountFormat(amount) {
        // Implement your amount formatting logic here, e.g., add commas
        return parseFloat(amount).toFixed(2);
    }

    function dateFormat(dateString) {
        // Implement your date formatting logic here
        if (!dateString || dateString === '0000-00-00') return '';
        var date = new Date(dateString);
        return date.toLocaleDateString(); // Adjust format as needed
    }
</script>