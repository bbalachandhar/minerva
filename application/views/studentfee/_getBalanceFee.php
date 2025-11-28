 <div class="form-horizontal balanceformpopup">
     <style>
         .d-flex {
             display: flex;
         }

         .justify-content-between {
             justify-content: space-between;
         }

         .align-items-center {
             align-items: center;
         }
         .checkbox-fees{
            
            
            padding: 5px 0px 0px 1px;
         }
     </style>
     <div class="box-body">
         <?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
         <input type="hidden" class="form-control" id="std_id" value="<?php echo $student["student_session_id"]; ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="parent_app_key" value="<?php echo $student['parent_app_key'] ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="guardian_phone" value="<?php echo $student['guardian_phone'] ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="guardian_email" value="<?php echo $student['guardian_email'] ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="student_fees_master_id" value="<?php echo $student_fees_master_id ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="fee_groups_feetype_id" value="<?php echo $fee_groups_feetype_id ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="transport_fees_id" value="<?php echo $transport_fees_id ?>" readonly="readonly" />
         <input type="hidden" class="form-control" id="fee_category" value="<?php echo $fee_category ?>" readonly="readonly" />

         <div class="form-group">
             <label for="inputEmail3" class="col-sm-3 col-lg-3 col-md-3 col-xs-2 control-label"><?php echo $this->lang->line('fees'); ?> (<?php echo $currency_symbol; ?>)</label>
             <div class="col-sm-9 col-lg-9 col-md-9 col-xs-10 pt-lg-7 pt-md-7">
                 <span><?php echo $balance; ?></span>
             </div>
         </div>
		 <div class="form-group">
             <label for="inputEmail3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('date'); ?><small class="req"> *</small></label>
             <div class="col-sm-9">
                 <input id="date" name="admission_date" placeholder="" type="text" class="form-control date_fee" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly="readonly" />
                 <span class="text-danger" id="date_error"></span>
             </div>
         </div>
         <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('paying_amount'); ?> (<?php echo $currency_symbol; ?>)<small class="req"> *</small></label>
             <div class="col-sm-9">
                 <input type="text" autofocus="" class="form-control modal_amount" id="amount" value="<?php echo $balance; ?>">
                 <span class="text-danger" id="amount_error"></span>
             </div>
         </div>
         <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label pt0"> <?php echo $this->lang->line('discount_group'); ?></label>
             <div class="col-sm-9 col-lg-9 col-md-9">
<?php 

if(!empty($discount_not_applied)){
?>
     <div class="checkbox-fees-scroll">
     <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-7 col-lg-7 col-sm-7 col-xs-7"><strong><?php echo $this->lang->line('fees_discount'); ?></strong></div>
                <div class="col-md-3 col-sm-3 col-xs-3 text text-center"><strong><?php echo $this->lang->line('available_count'); ?></strong></div>
                <div class="col-md-2 col-sm-2 col-xs-2 text text-right"><strong><?php echo $this->lang->line('value'); ?> </strong></div>
            </div>
        </div>
    </div>
<div class="row">
    <?php

       foreach ($discount_not_applied as $index => $discount_value) {

       ?>
        <div class="col-md-12">
            <div class="row">
<div class="col-md-7 col-sm-7 col-xs-7">   
    <label class="checkbox-inline pt0">
                        <input type="checkbox" name="fee_discount_group[]" class="grp_discount" value="<?php echo $discount_value->id;?>" data-disamount="<?php 
                            $data_disamount_value = "0";
                            if ($discount_value->type == "fix") {
                                if (isset($discount_value->custom_amount) && $discount_value->custom_amount > 0) {
                                    $data_disamount_value = $discount_value->custom_amount;
                                } else {
                                    $data_disamount_value = $discount_value->amount;
                                }
                            }
                            echo $data_disamount_value;
                        ?>" data-type="<?php echo $discount_value->type ; ?>" data-percentage="<?php echo ($discount_value->type == "percentage") ?  ($discount_value->percentage): "0";?>"><?php echo $discount_value->name ; ?><?php if($discount_value->code){ echo " (".$discount_value->code.")"; } ?><?php if($discount_value->type == "fix" && isset($discount_value->custom_amount) && $discount_value->custom_amount > 0){ echo " - ".$currency_symbol.$discount_value->custom_amount; } ?>
                 
                    </label></div>
<div class="col-md-3 col-sm-3 col-xs-3 text text-center"><?php echo $discount_value->remaining_discount_limit; ?></div>
<div class="col-md-2 col-sm-2 col-xs-2 text text-right"><?php 
                            $displayed_value = "";
                            if ($discount_value->type == "fix") {
                                if (isset($discount_value->custom_amount) && $discount_value->custom_amount > 0) {
                                    $displayed_value = $currency_symbol . $discount_value->custom_amount;
                                } else {
                                    $displayed_value = $currency_symbol . $discount_value->amount;
                                }
                            } else {
                                $displayed_value = $discount_value->percentage . "%";
                            }
                            echo $displayed_value;
                        ?></div>
            </div>
         
        </div>
        <?php
           // Close and start a new row after every two columns
           if (($index + 1) % 1 === 0 && $index + 1 !== count($discount_not_applied)) {
           ?>
</div>
<div class="row">
<?php
           }
       }
?>

</div>

<span class="text-danger" id="amount_error"></span>
</div>
<?php
}else{
    ?>
      <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center checkbox-fees text text-danger">
      <?php echo $this->lang->line('no_discount_available'); ?>
      </div>
      </div>
    <?php 
 
}
?>
           
            </div> 
         </div>
         <div class="form-group">
            <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label">Available Paid Advance (<?php echo $currency_symbol; ?>)</label>
            <div class="col-sm-9 col-lg-9 col-md-9">
                <span id="paid_advance_balance_text"><?php echo amountFormat($paid_advance_balance); ?></span>
            </div>
         </div>
         <div class="form-group">
            <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label">Available Discount Advance (<?php echo $currency_symbol; ?>)</label>
            <div class="col-sm-9 col-lg-9 col-md-9">
                <span id="discount_advance_balance_text"><?php echo amountFormat($discount_advance_balance); ?></span>
            </div>
         </div>
         <div class="form-group">
            <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label">Use Total Advance</label>
            <div class="col-sm-9 col-lg-9 col-md-9">
                <label class="radio-inline">
                    <input type="radio" name="use_advance" value="yes" <?php if(($paid_advance_balance + $discount_advance_balance) <= 0) echo 'disabled'; ?>>Yes
                </label>
                <label class="radio-inline">
                    <input type="radio" name="use_advance" value="no" checked="checked">No
                </label>
            </div>
         </div>
         <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('discount'); ?> (<?php echo $currency_symbol; ?>)<small class="req"> *</small></label>
             <div class="col-sm-9 col-lg-9 col-md-9">
                 <div class="row">
                     <div class="col-md-5 col-sm-5 col-lg-5">
                         <div class="">
                             <input type="text" class="form-control" id="amount_discount" value="0">
                             <span class="text-danger" id="amount_discount_error"></span>
                         </div>
                     </div>
                     <div class="col-md-2 col-sm-2 col-lg-2 ltextright">
                         <label for="inputPassword3" class="control-label pt-sm-1"><?php echo $this->lang->line('fine'); ?> (<?php echo $currency_symbol; ?>)<small class="req">*</small></label>
                     </div>
                     <div class="col-md-5 col-sm-5 col-lg-5">
                         <div class="">
                             <input type="text" class="form-control" id="amount_fine" value="<?php echo $remain_amount_fine; ?>">
                             <span class="text-danger" id="amount_fine_error"></span>
                         </div>
                     </div>
                 </div>
             </div><!--./col-sm-9-->
         </div>
         <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('payment_mode'); ?></label>
             <div class="col-sm-9 col-lg-9 col-md-9">
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="Cash" checked="checked"><?php echo $this->lang->line('cash'); ?>
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="Cheque"><?php echo $this->lang->line('cheque'); ?>
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="DD"><?php echo $this->lang->line('dd'); ?>
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="bank_transfer"><?php echo $this->lang->line('bank_transfer'); ?>
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="upi"><?php echo $this->lang->line('upi'); ?>
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="card"><?php echo $this->lang->line('card'); ?>
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="govt_7_5_payment">Govt 7.5 Payment
                 </label>
                 <label class="radio-inline">
                     <input type="radio" name="payment_mode_fee" value="govt_fg_payment">Govt FG Payment
                 </label>
                 <span class="text-danger" id="payment_mode_error"></span>
             </div>
         </div>
         <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('note'); ?></label>
             <div class="col-sm-9 col-lg-9 col-md-9">
                 <textarea class="form-control" rows="3" id="description" placeholder=""></textarea>
             </div>
         </div>
     </div>
 </div>
 <div class="modal-footer pr-0 pl-0 pb0">
     <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
     <button type="button" class="btn cfees save_button" id="load" data-action="collect" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $currency_symbol; ?> <?php echo $this->lang->line('collect_fees'); ?> </button>
     <button type="button" class="btn cfees save_button" id="load" data-action="print" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> <?php echo $this->lang->line('processing'); ?>"> <?php echo $currency_symbol; ?> <?php echo $this->lang->line('collect_print'); ?></button>
 </div>

<script>
$(document).ready(function () {
    var paid_advance_balance = <?php echo $paid_advance_balance; ?>;
    var discount_advance_balance = <?php echo $discount_advance_balance; ?>;
    var total_advance_balance = paid_advance_balance + discount_advance_balance;
    var fee_balance = <?php echo str_replace(',', '', $balance); ?>;

    $('input[name="use_advance"]').on('change', function() {
        if ($(this).val() == 'yes') {
            // Determine how much of the paid and discount advance can be used
            // This needs to be done carefully to not exceed the fee_balance
            var remaining_fee_to_cover = fee_balance;
            var applied_paid_advance = 0;
            var applied_discount_advance = 0;

            if (paid_advance_balance > 0) {
                applied_paid_advance = Math.min(paid_advance_balance, remaining_fee_to_cover);
                remaining_fee_to_cover -= applied_paid_advance;
            }

            if (discount_advance_balance > 0 && remaining_fee_to_cover > 0) {
                applied_discount_advance = Math.min(discount_advance_balance, remaining_fee_to_cover);
            }
            
            $('#amount').val(applied_paid_advance); // Set paying amount to paid advance used
            $('#amount_discount').val(applied_discount_advance); // Set discount field to discount advance used

        } else {
            $('#amount').val(fee_balance);
            $('#amount_discount').val(0); // Reset discount if not using advance or if switching back
        }
    });
});
</script>