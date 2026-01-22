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
         <input type="hidden" id="amount_discount_from_advance" value="0">

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
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('discount'); ?> (<?php echo $currency_symbol; ?>)</label>
             <div class="col-sm-9 col-lg-9 col-md-9">
                 <input type="text" class="form-control" id="amount_discount" value="0" readonly="readonly">
             </div>
         </div>
        <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('fine'); ?> (<?php echo $currency_symbol; ?>)</label>
             <div class="col-sm-9 col-lg-9 col-md-9">
                 <input type="text" class="form-control" id="amount_fine" value="<?php echo $remain_amount_fine; ?>">
             </div>
        </div>
         <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 col-lg-3 col-md-3 control-label"><?php echo $this->lang->line('payment_mode'); ?></label>
             <div class="col-sm-9 col-lg-9 col-md-9">
                 <div class="radio-list">
                    <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="Cash" checked="checked"> <?php echo $this->lang->line('cash'); ?>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="Cheque"> <?php echo $this->lang->line('cheque'); ?>
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="DD"><?php echo $this->lang->line('dd'); ?>
                    </label>
                     <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="bank_transfer"><?php echo $this->lang->line('bank_transfer'); ?>
                     </label>
                     <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="upi"><?php echo $this->lang->line('upi'); ?>
                     </label>
                    <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="card"> <?php echo $this->lang->line('card'); ?>
                    </label>
                 </div>
                 <hr>
                 <?php if($paid_advance_balance > 0): ?>
                    <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="paid_advance"> Use Paid Advance (Available: <?php echo amountFormat($paid_advance_balance); ?>)
                    </label>
                 <?php endif; ?>
                 <?php if($discount_advance_balance > 0): ?>
                    <label class="radio-inline">
                        <input type="radio" name="payment_mode" value="discount_advance"> Use Discount Advance (Available: <?php echo amountFormat($discount_advance_balance); ?>)
                    </label>
                 <?php endif; ?>
                 <hr>
                 <?php if(!empty($discount_not_applied)): ?>
                    <?php foreach($discount_not_applied as $discount_value): ?>
                        <?php 
                            $dis_amount = 0;
                            if ($discount_value->type == "fix") {
                                $dis_amount = $discount_value->custom_amount > 0 ? $discount_value->custom_amount : $discount_value->amount;
                            }
                        ?>
                        <label class="radio-inline">
                            <input type="radio" name="payment_mode" value="group_discount_<?php echo $discount_value->id; ?>" 
                                data-discount-type="<?php echo $discount_value->type; ?>"
                                data-discount-amount="<?php echo $dis_amount; ?>"
                                data-discount-percentage="<?php echo $discount_value->percentage; ?>">
                            <?php echo $discount_value->name . " (" . $discount_value->code . ")"; ?>
                        </label>
                    <?php endforeach; ?>
                 <?php endif; ?>
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
    var fee_balance_val = <?php echo str_replace(',', '', $balance); ?>;
    var paid_advance_balance_val = <?php echo $paid_advance_balance; ?>;
    var discount_advance_balance_val = <?php echo $discount_advance_balance; ?>;

    function applyCalculationLogic() {
        var selected_option = $('input[name="payment_mode"]:checked');
        var selected_value = selected_option.val();
        var fine_amount = parseFloat($('#amount_fine').val()) || 0;

        var new_amount = 0;
        var new_discount = 0;

        // Default case (Cash, Cheque, etc.)
        new_amount = fee_balance_val;

        if (selected_value === "paid_advance") {
            var amount_from_advance = Math.min(fee_balance_val, paid_advance_balance_val);
            new_amount = amount_from_advance;
            new_discount = 0;
        } else if (selected_value === "discount_advance") {
            var discount_from_advance = Math.min(fee_balance_val, discount_advance_balance_val);
            new_discount = discount_from_advance;
            new_amount = fee_balance_val - new_discount;
        } else if (selected_value.startsWith("group_discount_")) {
            var discount_type = selected_option.data('discount-type');
            var discount_amount = parseFloat(selected_option.data('discount-amount'));
            var discount_percentage = parseFloat(selected_option.data('discount-percentage'));
            
            var calculated_discount = 0;
            if (discount_type === 'fix') {
                calculated_discount = discount_amount;
            } else { // percentage
                calculated_discount = (fee_balance_val * discount_percentage) / 100;
            }
            
            var actual_discount_to_apply = Math.min(fee_balance_val, calculated_discount);
            new_discount = actual_discount_to_apply;
            new_amount = fee_balance_val - new_discount;
        }

        // Add fine to the final amount
        new_amount += fine_amount;

        $('#amount').val(new_amount.toFixed(2));
        $('#amount_discount').val(new_discount.toFixed(2));
    }

    // Event listeners
    $('input[name="payment_mode"]').on('change', applyCalculationLogic);
    $('#amount_fine').on('change keyup', applyCalculationLogic);

    // Initial call to set the state based on default selections
    applyCalculationLogic();
});
</script>