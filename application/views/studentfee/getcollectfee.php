<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

$total_amount = 0;
$total_fine = 0;

foreach ($feearray as $fee_key => $fee_value) {
    $amount_prev_paid = 0;
    $fees_fine_amount = 0;
    $fine_amount_paid = 0;

    if($fee_value->fee_category == "fees"){
        $amount_to_be_pay = $fee_value->amount;

        if ($fee_value->is_system) {
            $amount_to_be_pay = $fee_value->student_fees_master_amount;
        }

        if (is_string(($fee_value->amount_detail)) && is_array(json_decode(($fee_value->amount_detail), true))) {
            $amount_data = json_decode($fee_value->amount_detail);
            foreach ($amount_data as $amount_data_key => $amount_data_value) {
                $fine_amount_paid += $amount_data_value->amount_fine;
                $amount_prev_paid += ($amount_data_value->amount + $amount_data_value->amount_discount);
            }

            if ($fee_value->is_system) {
                $amount_to_be_pay = $fee_value->student_fees_master_amount - $amount_prev_paid;
            } else {
                $amount_to_be_pay = $fee_value->amount - $amount_prev_paid;
            }
        }

        if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != NULL) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d'))) && $amount_to_be_pay > 0) {
            if($fee_value->fine_type=='cumulative'){
                $date1=date_create("$fee_value->due_date");
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $due_days= $diff->format("%a");

                if($this->customlib->get_cumulative_fine_amount($fee_value->fee_groups_feetype_id,$due_days)){
                    $due_fine_amount=$this->customlib->get_cumulative_fine_amount($fee_value->fee_groups_feetype_id,$due_days);
                }else{
                    $due_fine_amount=0;
                }
                $fees_fine_amount = $due_fine_amount-$fine_amount_paid;
            } else if($fee_value->fine_type=='fix' || $fee_value->fine_type=='percentage'){
                $fees_fine_amount=$fee_value->fine_amount-$fine_amount_paid;
            }
        }

        $total_amount += $amount_to_be_pay;
        $total_fine += $fees_fine_amount;

    } elseif ($fee_value->fee_category == "transport") {
        $amount_to_be_pay = $fee_value->fees;

        if (is_string(($fee_value->amount_detail)) && is_array(json_decode(($fee_value->amount_detail), true))) {
            $amount_data = json_decode($fee_value->amount_detail);
            foreach ($amount_data as $amount_data_key => $amount_data_value) {
                $fine_amount_paid += $amount_data_value->amount_fine;
                $amount_prev_paid += ($amount_data_value->amount + $amount_data_value->amount_discount);
            }
            $amount_to_be_pay = $fee_value->fees - $amount_prev_paid;
        }

        if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != NULL) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d'))) && $amount_to_be_pay > 0) {
            $transport_fine_amount = is_null($fee_value->fine_percentage) ? $fee_value->fine_amount : percentageAmount($fee_value->fees,$fee_value->fine_percentage);
            $fees_fine_amount = $transport_fine_amount - $fine_amount_paid;
            $total_fine += $fees_fine_amount;
        }

        $total_amount += $amount_to_be_pay;
    }
}
?>
<style type="text/css">
    .collect_grp_fees{
      font-size: 15px;
    font-weight: 600;
    padding-bottom: 15px;
    }

    .fees-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .fees-list>.item {
        border-radius: 3px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        background: #fff;
    }
    .fees-list>.item:before, .fees-list>.item:after {
        content: " ";
        display: table;
    }
    .fees-list>.item:after {
        clear: both;
    }
    .fees-list .product-img {
        float: left;
    }
    .fees-list .product-img img {
        width: 50px;
        height: 50px;
    }
    .fees-list .product-info {
        margin-left: 0px;
    }
    .fees-list .product-title {
        font-weight: 600;
        font-size: 15px;
        display: inline;
    }
    .fees-list .product-title span{

        font-size: 15px;
        display: inline;
        font-weight: 100 !important;
    }
    .fees-list .product-description {
        display: block;
        color: #999;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .fees-list-in-box>.item {
        -webkit-box-shadow: none;
        box-shadow: none;
        border-radius: 0;
        border-bottom: 1px solid #f4f4f4;
    }
    .fees-list-in-box>.item:last-of-type {
        border-bottom-width: 0;
    }

.fees-footer {
    border-top-color: #f4f4f4;
}
.fees-footer {
    padding: 15px 0px 0px 0px;
    text-align: right;
    border-top: 1px solid #e5e5e5;
}
</style>

<div class=" ">
  <div class="col-lg-12">
    <div class="form-horizontal">
        <div class="col-lg-12">
            <div class="form-group row">
                <label for="inputEmail3" class="col-sm-3 control-label"><?php echo $this->lang->line('date'); ?> <small class="req"> *</small></label>
                <div class="col-sm-9">
                    <input id="date" name="collected_date" placeholder="" type="text" class="form-control date_fee" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly="readonly" autocomplete="off">
                    <span id="form_collection_collected_date_error" class="text text-danger"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group row">
                <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('paying_amount'); ?> (<?php echo $currency_symbol; ?>)<small class="req"> *</small></label>
                <div class="col-sm-9">
                    <input type="text" autofocus="" class="form-control modal_amount" name="amount" id="amount" value="<?php echo amountFormat((float) ($total_amount + $total_fine), 2, '.', ''); ?>">
                    <span class="text-danger" id="amount_error"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
             <label for="inputPassword3" class="col-sm-3 control-label pt0"> <?php echo $this->lang->line('discount_group'); ?></label>
             <div class="col-sm-9">
                <?php
                if (!empty($discount_not_applied)) {
                    ?>
                     <div class="checkbox-fees-scroll">
                     <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-7"><strong><?php echo $this->lang->line('fees_discount'); ?></strong></div>
                                <div class="col-md-5 text-right"><strong><?php echo $this->lang->line('amount'); ?></strong></div>
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <?php
                    foreach ($discount_not_applied as $discount_value) {

    $dis_amount = $discount_value->amount;
    if ($discount_value->type == "fix" && isset($discount_value->custom_amount) && $discount_value->custom_amount > 0) {
        $dis_amount = $discount_value->custom_amount;
    }
                        ?>
                        <div class="col-md-12">
                            <div class="row">
                <div class="col-md-7">
                    <label class="checkbox-inline pt0">
                                        <input type="checkbox" name="fee_discount_group[]" class="grp_discount" value="<?php echo $discount_value->id; ?>" data-disamount="<?php echo $dis_amount; ?>" data-type="<?php echo $discount_value->type; ?>" data-percentage="<?php echo $discount_value->percentage; ?>"><?php echo $discount_value->name; ?><?php if($discount_value->code){ echo " (".$discount_value->code.")"; } ?><?php if($discount_value->type == "fix" && isset($discount_value->custom_amount) && $discount_value->custom_amount > 0){ echo " - ".$currency_symbol.$discount_value->custom_amount; } ?>
                                    </label></div>
                <div class="col-md-5 text-right"><?php
                    if ($discount_value->type == "fix") {
                         echo $currency_symbol . amountFormat($dis_amount);
                    } else {
                        $discount_amount = (($total_amount + $total_fine) * $discount_value->percentage) / 100;
                        echo $currency_symbol . amountFormat($discount_amount);
                    }
                    ?></div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <span class="text-danger" id="amount_error"></span>
                </div>
                <?php
                } else {
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
        </div>
        <div class="col-lg-12">
            <div class="form-group row">
                <label for="inputPassword3" class="col-sm-3 control-label">Available Paid Advance (<?php echo $currency_symbol; ?>)</label>
                <div class="col-sm-9">
                    <span id="paid_advance_balance_text"><?php echo amountFormat($paid_advance_balance); ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group row">
                <label for="inputPassword3" class="col-sm-3 control-label">Available Discount Advance (<?php echo $currency_symbol; ?>)</label>
                <div class="col-sm-9">
                    <span id="discount_advance_balance_text"><?php echo amountFormat($discount_advance_balance); ?></span>
                </div>
            </div>
        </div>
        <input type="hidden" id="amount_discount_from_advance" name="amount_discount_from_advance" value="0">
        <div class="col-lg-12">
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-3 control-label">Use Paid Advance</label>
                <div class="col-sm-9">
                    <label class="radio-inline">
                        <input type="radio" name="use_paid_advance" value="yes" <?php if($paid_advance_balance <= 0) echo 'disabled'; ?>>Yes
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="use_paid_advance" value="no" checked="checked">No
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-3 control-label">Use Discount Advance</label>
                <div class="col-sm-9">
                    <label class="radio-inline">
                        <input type="radio" name="use_discount_advance" value="yes" <?php if($discount_advance_balance <= 0) echo 'disabled'; ?>>Yes
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="use_discount_advance" value="no" checked="checked">No
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('discount'); ?> (<?php echo $currency_symbol; ?>)</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="amount_discount" id="amount_discount" value="0">
                    <span class="text-danger" id="amount_discount_error"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('fine'); ?> (<?php echo $currency_symbol; ?>)</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="amount_fine" id="amount_fine" value="0">
                    <span class="text-danger" id="amount_fine_error"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
          <div class="form-group row">
            <label for="inputPassword3" class="col-sm-3 control-label"> <?php echo $this->lang->line('payment_mode'); ?></label>
            <div class="col-sm-9">
                <label class="radio-inline">
                    <input type="radio" name="payment_mode_fee" value="Cash" checked="checked"> <?php echo $this->lang->line('cash'); ?></label>
                <label class="radio-inline">
                    <input type="radio" name="payment_mode_fee" value="Cheque"> <?php echo $this->lang->line('cheque'); ?></label>
                <label class="radio-inline">
                    <input type="radio" name="payment_mode_fee" value="DD"><?php echo $this->lang->line('dd'); ?></label>
                <label class="radio-inline">
                    <input type="radio" name="payment_mode_fee" value="bank_transfer"><?php echo $this->lang->line('bank_transfer'); ?>
                </label>
                <label class="radio-inline">
                    <input type="radio" name="payment_mode_fee" value="upi"><?php echo $this->lang->line('upi'); ?>
                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_mode_fee" value="card"> <?php echo $this->lang->line('card'); ?>                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_mode_fee" value="govt_7_5_payment">Govt 7.5 Payment                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="payment_mode_fee" value="govt_fg_payment">Govt FG Payment                </label>
                                <span class="text-danger" id="payment_mode_error"></span>
            </div>
            <span id="form_collection_payment_mode_fee_error" class="text text-danger"></span>
          </div>
        </div>
        <div class="col-lg-12">
            <div class="form-group row">
                <label for="inputPassword3" class="col-sm-3 control-label"> <?php echo $this->lang->line('note') ?></label>
                <div class="col-sm-9">
                    <textarea class="form-control" rows="3" name="fee_gupcollected_note" id="description" placeholder=""></textarea>
                    <span id="form_collection_fee_gupcollected_note_error" class="text text-danger"></span>
                </div>
            </div>
        </div>
    </div>
<ul class="fees-list fees-list-in-box">
    <?php
    $row_counter = 1;
    foreach ($feearray as $fee_key => $fee_value) {
        $amount_prev_paid = 0;
        $fees_fine_amount = 0;
        $fine_amount_paid = 0;
        $fine_amount_status = false;

        if($fee_value->fee_category == "fees"){
        $amount_to_be_pay = $fee_value->amount;

        if ($fee_value->is_system) {
            $amount_to_be_pay = $fee_value->student_fees_master_amount;
        }

        if (is_string(($fee_value->amount_detail)) && is_array(json_decode(($fee_value->amount_detail), true))) {
            $amount_data = json_decode($fee_value->amount_detail);
            foreach ($amount_data as $amount_data_key => $amount_data_value) {
                      $fine_amount_paid+=$amount_data_value->amount_fine;
                $amount_prev_paid = $amount_prev_paid + ($amount_data_value->amount + $amount_data_value->amount_discount);
            }

            if ($fee_value->is_system) {
                $amount_to_be_pay = $fee_value->student_fees_master_amount - $amount_prev_paid;
            } else {
                $amount_to_be_pay = $fee_value->amount - $amount_prev_paid;
            }
        }

    if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != NULL) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d'))) && $amount_to_be_pay > 0) {
         $fine_amount_status=true;

            if($fee_value->fine_type=='cumulative'){
                $date1=date_create("$fee_value->due_date");
                $date2=date_create(date('Y-m-d'));
                $diff=date_diff($date1,$date2);
                $due_days= $diff->format("%a");;

                if($this->customlib->get_cumulative_fine_amount($fee_value->fee_groups_feetype_id,$due_days)){
                    $due_fine_amount=$this->customlib->get_cumulative_fine_amount($fee_value->fee_groups_feetype_id,$due_days);
                }else{
                    $due_fine_amount=0;
                }
                $fees_fine_amount   = $due_fine_amount-$fine_amount_paid;

            }else if($fee_value->fine_type=='fix' || $fee_value->fine_type=='percentage'){
                $fees_fine_amount=$fee_value->fine_amount-$fine_amount_paid;
            }
        }

        if ($amount_to_be_pay > 0) {
            ?>

            <li class="item">
                <input name="row_counter[]" type="hidden" value="<?php echo $row_counter; ?>">
                <input name="student_fees_master_id_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->id; ?>">
                <input name="fee_groups_feetype_id_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->fee_groups_feetype_id; ?>">
                 <input name="fee_groups_feetype_fine_amount_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fees_fine_amount; ?>">
                <input name="fee_amount_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $amount_to_be_pay; ?>">
                <input name="fee_category_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->fee_category; ?>">
                <input name="trans_fee_id_<?php echo $row_counter; ?>" type="hidden" value="0">
                <div class="product-info">
                    <a href="#"  onclick="return false;" class="product-title">

                        <?php
                            if ($fee_value->is_system) {
                                echo $this->lang->line($fee_value->name) . " (" . $this->lang->line($fee_value->type) . ")";
                            } else {
                                echo $fee_value->name . " (" . $fee_value->type . ")";
                            }
                        ?>
                        <span class="pull-right"><?php echo  $currency_symbol.amountFormat((float) $amount_to_be_pay, 2, '.', ''); ?></span></a>
                        <span class="product-description">

                        <?php
                            if ($fee_value->is_system) {
                                echo $this->lang->line($fee_value->code);
                            } else {
                                echo $fee_value->code;
                            }
                        ?>

                        </span>
                        <?php
if($fine_amount_status){
    ?>
                       <a href="#"  onclick="return false;" class="product-title text text-danger"><?php echo $this->lang->line('fine'); ?>
                        <span class="pull-right">
                            <?php echo  $currency_symbol.amountFormat((float) $fees_fine_amount, 2, '.', ''); ?>
                        </span>
                    </a>
    <?php
}
                         ?>
                </div>
            </li>

            <?php
        }
        }elseif ($fee_value->fee_category == "transport") {

        $amount_to_be_pay = $fee_value->fees;

        if (is_string(($fee_value->amount_detail)) && is_array(json_decode(($fee_value->amount_detail), true))) {

            $amount_data = json_decode($fee_value->amount_detail);

            foreach ($amount_data as $amount_data_key => $amount_data_value) {
                      $fine_amount_paid+=$amount_data_value->amount_fine;
                $amount_prev_paid = $amount_prev_paid + ($amount_data_value->amount + $amount_data_value->amount_discount);
            }

                $amount_to_be_pay = $fee_value->fees - $amount_prev_paid;

        }

    if (($fee_value->due_date != "0000-00-00" && $fee_value->due_date != NULL) && (strtotime($fee_value->due_date) < strtotime(date('Y-m-d'))) && $amount_to_be_pay > 0) {

$transport_fine_amount  =  is_null($fee_value->fine_percentage) ? $fee_value->fine_amount : percentageAmount($fee_value->fees,$fee_value->fine_percentage);
         $fees_fine_amount=$transport_fine_amount-$fine_amount_paid;
         $fine_amount_status=true;
        }

        if ($amount_to_be_pay > 0) {
            ?>

            <li class="item">
                <input name="row_counter[]" type="hidden" value="<?php echo $row_counter; ?>">
                <input name="student_fees_master_id_<?php echo $row_counter; ?>" type="hidden" value="0">
                <input name="fee_groups_feetype_id_<?php echo $row_counter; ?>" type="hidden" value="0">
                 <input name="fee_groups_feetype_fine_amount_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fees_fine_amount; ?>">
                <input name="fee_amount_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $amount_to_be_pay; ?>">
                <div class="product-info">
                    <input name="fee_category_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->fee_category; ?>">
                <input name="trans_fee_id_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->id; ?>">

                    <a href="#"  onclick="return false;" class="product-title"><?php echo $this->lang->line("transport_fees") ?>
                        <span class="pull-right"><?php echo  $currency_symbol.amountFormat((float) $amount_to_be_pay, 2, '.', ''); ?></span></a>
                         <span class="product-description">
                        <?php echo $fee_value->month; ?>
                        </span>
                        <?php
if($fine_amount_status){
    ?>
                       <a href="#"  onclick="return false;" class="product-title text text-danger"><?php echo $this->lang->line('fine'); ?>
                        <span class="pull-right">
                            <?php echo  $currency_symbol.amountFormat((float) $fees_fine_amount, 2, '.', ''); ?>
                        </span>
                    </a>
    <?php
}
                         ?>
                </div>
            </li>

            <?php
        }
        }

        $row_counter++;
    }
    ?>
</ul>
</div>
</div>
<?php if ($total_amount > 0) { ?>
<div class="row collect_grp_fees">
    <div class="col-md-8">
        <span class="pull-right">
            <?php echo $this->lang->line('total_pay'); ?>
        </span>
    </div>
    <div class="col-md-4">
        <span class="pull-right">
            <?php echo $currency_symbol.amountFormat((float) ($total_amount + $total_fine), 2, '.', ''); ?>
        </span>
    </div>
</div>
<div class="row fees-footer">
    <div class="col-md-12">
          <button type="submit" class="btn btn-primary pull-right payment_collect" data-loading-text="<i class='fa fa-spinner fa-spin '></i><?php echo $this->lang->line('processing')?>"><i class="fa fa-money"></i> <?php echo $this->lang->line('pay'); ?></button>
    </div>
</div>
<?php }else{
    ?>
    <div class="row">
    <div class="col-md-12">
<div class="alert alert-info mb0">
    <?php echo $this->lang->line('no_fees_found'); ?>
</div>
</div>
    <?php
}
 ?>
<script>
$(document).ready(function(){
    var paid_advance_balance_val = <?php echo $paid_advance_balance; ?>;
    var discount_advance_balance_val = <?php echo $discount_advance_balance; ?>;
    var total_amount_due = <?php echo ($total_amount + $total_fine); ?>; // Total amount for all selected fees

    // This function will be called on initial load and when radio buttons change.
    function applyAdvanceLogic() {
        let currentPayingAmount = total_amount_due; // Start with total amount due
        let currentDiscountAmount = 0; // Start with 0 discount

        let use_paid_advance_checked = $('input[name="use_paid_advance"]:checked').val() === 'yes';
        let use_discount_advance_checked = $('input[name="use_discount_advance"]:checked').val() === 'yes';
        
        // Reset advance discount field
        $('#amount_discount_from_advance').val('0');
        let paymentModeChangedToAdvance = false;

        // --- Apply Group Discounts first (existing logic adapted) ---
        var calculated_group_discount_amount = 0;
        $('.grp_discount:checked').each(function() {
            var type = $(this).data('type');
            var disamount = parseFloat($(this).data('disamount'));
            var percentage = parseFloat($(this).data('percentage'));
            if(type === 'fix'){
                calculated_group_discount_amount += disamount;
            } else { // percentage
                calculated_group_discount_amount += (total_amount_due * percentage) / 100;
            }
        });
        currentDiscountAmount += calculated_group_discount_amount;
        if (calculated_group_discount_amount > 0) {
            currentPayingAmount = 0;
        } else {
            currentPayingAmount -= calculated_group_discount_amount;
        }


        // If no advance is used, revert to original amount due
        if (!use_paid_advance_checked && !use_discount_advance_checked) {
            // Apply fine
            let currentFine = parseFloat($('#amount_fine').val()) || 0;
            currentPayingAmount += currentFine; // Add fine back if not covered by advance

            $('#amount').val(Math.max(0, currentPayingAmount).toFixed(2));
            $('#amount_discount').val(currentDiscountAmount.toFixed(2));
            $('input[name="payment_mode_fee"][value="Cash"]').prop('checked', true);
            $('input[name="payment_mode_fee"]').not('[value="Cash"]').prop('checked', false);
            return; // Exit if no advances are to be applied
        }

        // --- Apply Paid Advance ---
        if (use_paid_advance_checked && paid_advance_balance_val > 0) {
            let actualAmountToReduce = Math.min(paid_advance_balance_val, currentPayingAmount);
            currentPayingAmount -= actualAmountToReduce;
            paymentModeChangedToAdvance = true;
        }

        // --- Apply Discount Advance ---
        if (use_discount_advance_checked && discount_advance_balance_val > 0) {
            let advance_discount_to_apply = Math.min(discount_advance_balance_val, currentPayingAmount);
            $('#amount_discount_from_advance').val(advance_discount_to_apply.toFixed(2));
            currentDiscountAmount += advance_discount_to_apply;
            currentPayingAmount -= advance_discount_to_apply; 
            paymentModeChangedToAdvance = true;
        }
        
        // Ensure paying amount doesn't go below zero
        currentPayingAmount = Math.max(0, currentPayingAmount);

        // Apply fine if not covered by advances
        let currentFine = parseFloat($('#amount_fine').val()) || 0;
        currentPayingAmount += currentFine;


        // Update the input fields
        $('#amount').val(currentPayingAmount.toFixed(2));
        $('#amount_discount').val(currentDiscountAmount.toFixed(2));

        // Ensure that if any advance is used, "Advance" payment mode is selected and others are deselected
        if (paymentModeChangedToAdvance) {
            $('input[name="payment_mode_fee"][value="Advance"]').prop('checked', true);
            $('input[name="payment_mode_fee"]').not('[value="Advance"]').prop('checked', false);
        } else {
             $('input[name="payment_mode_fee"][value="Cash"]').prop('checked', true); // Default to Cash if no advance used
             $('input[name="payment_mode_fee"]').not('[value="Cash"]').prop('checked', false);
        }
    }

    function getSelectedDiscountLabels() {
        var labels = [];
        var seen = {};
        $('input[name="fee_discount_group[]"]:checked').each(function () {
            var labelText = $.trim($(this).closest('label').text());
            if (labelText && !seen[labelText]) {
                seen[labelText] = true;
                labels.push(labelText);
            }
        });
        return labels;
    }

    function setNoteFromSelections() {
        var noteParts = [];
        var discountLabels = getSelectedDiscountLabels();
        if (discountLabels.length) {
            noteParts = noteParts.concat(discountLabels);
        } else {
            var usePaidAdvance = $('input[name="use_paid_advance"]:checked').val() === 'yes';
            var useDiscountAdvance = $('input[name="use_discount_advance"]:checked').val() === 'yes';
            if (usePaidAdvance) {
                noteParts.push('Paid Advance');
            }
            if (useDiscountAdvance) {
                noteParts.push('Discount Advance');
            }
        }
        if (!noteParts.length) {
            $('#description').val('');
            return;
        }
        $('#description').val(noteParts.join(', '));
    }

    // Event listeners for the new radio buttons
    $('input[name="use_paid_advance"]').on('change', function() { applyAdvanceLogic(); setNoteFromSelections(); });
    $('input[name="use_discount_advance"]').on('change', function() { applyAdvanceLogic(); setNoteFromSelections(); });
    $('.grp_discount').on('change', function() { applyAdvanceLogic(); setNoteFromSelections(); });
    $('#amount_fine').on('change keyup', applyAdvanceLogic); // And on fine change

    // Initial call to set the state based on default selections
    applyAdvanceLogic();

    $('#amount').on('change keyup', function() {
        // If user types in amount, disable advances and group discounts
        $('input[name="use_paid_advance"][value="no"]').prop('checked', true);
        $('input[name="use_discount_advance"][value="no"]').prop('checked', true);
        $('#amount_discount').val('0.00');
        $('.grp_discount').prop('checked', false);
    });
});
</script>
