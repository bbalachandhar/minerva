<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#424242" />
        <title><?php echo $this->customlib->getAppName(); ?></title>
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css"> 
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css"> 
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/style-main.css">
        <style type="text/css">
            .table2 tr.border_bottom td {
                box-shadow: none;
                border-radius: 0;
                border-bottom: 1px solid #e6e6e6;
            }
            .table2 td {
                padding-bottom: 3px;
                padding-top: 6px;
            }
            .title{
                color: #0084B4;
                font-weight: 600 !important;
                font-size: 15px !important;;
                display: inline;

            }
            .product-description {
                display: block;
                color: #999;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
            .text-fine{
                color: #bf4f4d;
            }
        </style> 
    </head>
    <body style="background: #ededed;">
        <div class="container">
            <div class="row">
                <div class="paddtop20">
                    <div class="col-md-8 col-md-offset-2 text-center">
                        <img src="<?php echo base_url('uploads/school_content/logo/' . $setting[0]['image']); ?>">
                    </div> 
                    <div class="col-md-6 col-md-offset-3 mt20">
                        <div class="paymentbg">
                            <div class="invtext"><?php echo $this->lang->line('fees_payment_details');?></div>
                            <div class="padd2 paddtzero">
                                <table class="table2" width="100%">
                                    <?php if (isset($params['item_type']) && $params['item_type'] == 'online_admission_fee') { ?>
                                        <tr>
                                            <th><?php echo $this->lang->line('description'); ?></th>
                                            <th class="text-right"><?php echo $this->lang->line('amount')?></th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="title"><?php echo $this->lang->line('online_admission_fee'); ?></span>
                                            </td>
                                            <td class="text-right"><?php echo $setting[0]['currency_symbol'] . amountFormat((float) $params['admission_amount'], 2, '.', ''); ?></td>
                                        </tr>
                                        <?php if (isset($params['processing_charge']) && $params['processing_charge'] > 0) { ?>
                                            <tr class="border_bottom">
                                                <td>
                                                    <span class="text-fine"><?php echo $this->lang->line('processing_fees'); ?></span>
                                                </td>
                                                <td class="text-right"><?php echo $setting[0]['currency_symbol'] . amountFormat((float) $params['processing_charge'], 2, '.', ''); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr class="bordertoplightgray">
                                            <td colspan="2" class="text-right"><?php echo $this->lang->line('total');?>: <?php echo $setting[0]['currency_symbol'] . amountFormat((float) $params['total'], 2, '.', ''); ?></td>
                                        </tr>
                                    <?php } else { // Default to student fees logic ?>
                                        <tr>
                                            <th><?php echo $this->lang->line('description'); ?></th>
                                            <th class="text-right"><?php echo $this->lang->line('amount')?></th>
                                        </tr>
                                        <?php
                                        // Ensure student_fees_master_array exists before iterating
                                        if (isset($params['student_fees_master_array']) && is_array($params['student_fees_master_array'])) {
                                            foreach ($params['student_fees_master_array'] as $fees_key => $fees_value) {
                                                ?>
                                                <tr>
                                                   <td>
                                                        <span class="title"><?php if ($fees_value['is_system']) {
                                        echo $this->lang->line($fees_value['fee_group_name']);
                                    } else {
                                        echo $fees_value['fee_group_name'] ;
                                    }?> </span>
                                                        <span class="product-description">
                                                            <?php  if ($fees_value['is_system']) {
                                        echo $this->lang->line($fees_value['fee_type_code']);
                                    } else {
                                        echo $fees_value['fee_type_code'];
                                    } ?></span>
                                                    </td>
                                                    <td class="text-right"><?php echo $setting[0]['currency_symbol'] . amountFormat((float) $fees_value['amount_balance'], 2, '.', ''); ?></td>
                                                </tr>
                                                <tr class="border_bottom">
                                                    <td> 
                                                        <span class="text-fine"><?php echo $this->lang->line('fine'); ?></span></td>
                                                    <td class="text-right"><?php echo $setting[0]['currency_symbol'] . amountFormat((float) $fees_value['fine_balance'], 2, '.', ''); ?></td>
                                                </tr>
                                                <tr class="border_bottom">
                                                    <td>
                                                        <span class="text-text-success"><?php echo $this->lang->line('discount'); ?></span>
                                                    </td>
                                                    <td class="text-right"><?php echo $setting[0]['currency_symbol'] . amountFormat((float) ($params['applied_fee_discount'] ?? 0), 2, '.', ''); ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                        ?>
                                        
                                        <?php if (isset($params['gateway_processing_charge']) && $params['gateway_processing_charge'] > 0) { ?>
                                            <tr class="border_bottom">
                                                <td>
                                                    <span class="text-text-success"><?php echo $this->lang->line('processing_fees'); ?></span>
                                                </td>
                                                <td class="text-right"><?php echo $setting[0]['currency_symbol'] . amountFormat((float) $params['gateway_processing_charge'], 2, '.', ''); ?></td>
                                            </tr>
                                        <?php } ?>
                                        <!-- Dynamic fee row for per-method slab charges -->
                                        <tr id="bd_fee_row" class="border_bottom" style="display:none;">
                                            <td><span class="text-text-success"><?php echo $this->lang->line('processing_fees'); ?></span></td>
                                            <td class="text-right"><span id="bd_fee_amount"></span></td>
                                        </tr>
                                        <tr class="bordertoplightgray">
                                            <td colspan="2" class="text-right"><?php echo $this->lang->line('total');?>: <span id="bd_total_cell"><?php echo $setting[0]['currency_symbol'] . amountFormat((float)(($params['fine_amount_balance'] ?? 0) + ($params['total'] ?? 0) - ($params['applied_fee_discount'] ?? 0) + ($params['gateway_processing_charge'] ?? 0)), 2, '.', ''); ?></span></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                                <script src="<?php echo base_url(); ?>backend/custom/jquery.min.js"></script>
                                <div class="divider"></div>

                                <?php
                                // Build slabs array for JS
                                $slabs_for_js = [];
                                if (!empty($params['billdesk_slabs'])) {
                                    foreach ($params['billdesk_slabs'] as $s) {
                                        if ($s->is_active) {
                                            $slabs_for_js[] = [
                                                'key'             => $s->payment_method,
                                                'label'           => $s->label,
                                                'charge_type'     => $s->charge_type,
                                                'charge_value'    => (float)$s->charge_value,
                                                'amount_threshold'=> (float)$s->amount_threshold,
                                                'charge_value_above' => (float)$s->charge_value_above,
                                            ];
                                        }
                                    }
                                }
                                $base_amount = ($params['total'] ?? 0) + ($params['fine_amount_balance'] ?? 0) - ($params['applied_fee_discount'] ?? 0);
                                $currency_sym = $setting[0]['currency_symbol'];
                                ?>

                                <?php if (!empty($slabs_for_js)): ?>
                                <!-- Payment Method Selector -->
                                <div style="margin-bottom:12px;">
                                    <p style="font-weight:600;margin-bottom:8px;font-size:13px;color:#444;">Select Payment Method:</p>
                                    <?php foreach ($slabs_for_js as $slab): ?>
                                    <label class="bd-method-label" style="display:block;padding:7px 10px;border:1px solid #ddd;border-radius:4px;margin-bottom:5px;cursor:pointer;">
                                        <input type="radio" name="bd_method_radio" value="<?php echo $slab['key']; ?>"
                                               data-type="<?php echo $slab['charge_type']; ?>"
                                               data-val="<?php echo $slab['charge_value']; ?>"
                                               data-threshold="<?php echo $slab['amount_threshold']; ?>"
                                               data-val-above="<?php echo $slab['charge_value_above']; ?>"
                                               style="margin-right:6px;">
                                        <?php echo htmlspecialchars($slab['label']); ?>
                                        <span class="bd-method-fee text-muted" style="font-size:12px;float:right;">
                                            <?php
                                            if ($slab['charge_type'] === 'flat') {
                                                echo $currency_sym . number_format($slab['charge_value'], 2);
                                            } elseif ($slab['charge_value'] == 0 && $slab['charge_value_above'] == 0) {
                                                echo 'Free';
                                            } elseif ($slab['amount_threshold'] > 0) {
                                                echo $slab['charge_value'] . '% (≤₹' . number_format($slab['amount_threshold'], 0) . ') / ' . $slab['charge_value_above'] . '% (above)';
                                            } else {
                                                echo $slab['charge_value'] . '%';
                                            }
                                            ?>
                                        </span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>

                                <script>
                                (function() {
                                    var baseAmount = <?php echo json_encode((float)$base_amount); ?>;
                                    var currSym    = <?php echo json_encode($currency_sym); ?>;

                                    function computeFee(radio) {
                                        var type      = parseFloat(radio.getAttribute('data-val'));
                                        var chargeType= radio.getAttribute('data-type');
                                        var threshold = parseFloat(radio.getAttribute('data-threshold'));
                                        var valAbove  = parseFloat(radio.getAttribute('data-val-above'));
                                        var fee = 0;
                                        if (chargeType === 'flat') {
                                            fee = type; // type holds charge_value
                                        } else {
                                            if (threshold > 0 && baseAmount > threshold) {
                                                fee = (baseAmount * valAbove) / 100;
                                            } else {
                                                fee = (baseAmount * type) / 100;
                                            }
                                        }
                                        return Math.round(fee * 100) / 100;
                                    }

                                    function amountFormat(n) {
                                        return currSym + parseFloat(n).toFixed(2);
                                    }

                                    document.querySelectorAll('input[name="bd_method_radio"]').forEach(function(radio) {
                                        radio.addEventListener('change', function() {
                                            var fee   = computeFee(this);
                                            var total = baseAmount + fee;

                                            // Update hidden inputs for form submit
                                            document.getElementById('bd_payment_method').value = this.value;
                                            document.getElementById('bd_computed_charge').value = fee.toFixed(2);

                                            // Update fee row in summary table
                                            var feeRow = document.getElementById('bd_fee_row');
                                            if (fee > 0) {
                                                feeRow.style.display = '';
                                                document.getElementById('bd_fee_amount').textContent = amountFormat(fee);
                                            } else {
                                                feeRow.style.display = 'none';
                                            }
                                            document.getElementById('bd_total_cell').textContent = amountFormat(total);

                                            // Highlight selected label
                                            document.querySelectorAll('.bd-method-label').forEach(function(l){ l.style.background=''; l.style.borderColor='#ddd'; });
                                            this.parentElement.style.background = '#f0f8ff';
                                            this.parentElement.style.borderColor = '#0084B4';

                                            // Enable pay button
                                            document.querySelector('.submit_button').disabled = false;
                                        });
                                    });
                                })();
                                </script>
                                <?php endif; ?>

                                <form class="paddtlrb" action="<?php echo site_url('user/gateway/billdesk/pay') ?>" method="POST" id="billdeskForm">
                                    <input type="hidden" id="bd_payment_method" name="billdesk_payment_method" value="">
                                    <input type="hidden" id="bd_computed_charge" name="billdesk_computed_charge" value="0">
                                    <button type="button" onclick="window.history.go(-1); return false;" name="search"  value="" class="btn btn-info"><i class="fa fa fa-chevron-left"></i> <?php echo $this->lang->line('back')?></button>
                                    <button type="submit" class="btn cfees pull-right submit_button" <?php echo !empty($slabs_for_js) ? 'disabled="disabled"' : ''; ?>><i class="fa fa fa-money"></i> Pay With Billdesk </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</body>
</html>