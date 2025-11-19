<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Stripe Pay Custom Integration Demo</title>
        <link href="style.css" type="text/css" rel="stylesheet" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
        <style type="text/css">
            .paybox{width: 460px;margin: 7% auto;}
            .paybox_bg{background: #fff;box-shadow: 0px 1px 15px rgba(0, 0, 0, 0.18);border-radius: 10px;}
            .bt_title{background: #fff; color: #000; padding: 20px 20px; border-bottom:1px solid #ccc;}
            .paybody{padding: 20px;}
            .paybox label{font-size: 13px; padding-top: 7px;}
            .submit_button{border-radius: 4px; padding: 10px 20px; border:0; background: #204d74; color: #fff; display: block;width: 100%; font-size: 16px; text-transform: uppercase; margin-top: 20px;}
            .submit_button:hover{background: #367fa9;}
            .payspan{position: absolute; right:0; top:5px; font-size: 18px;}
            @media(max-width:767px){
                .paybox{width: 100%;margin: 1% auto;}
                .bt_title img{width: 200px; height: 80px;}
            }
            .text-red {
    color: #dd4b39 !important;
}
        </style>

    </head>
    <body>

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="paybox">
                        <div class="paybox_bg">
                            <h3 class="bt_title"><img src="<?php echo base_url();?>/backend/images/stripe.png" style="margin-bottom: 10px;"><br />Stripe Payment Gateway</h3>
                            <div class="paybody">
                                <div class="dropin-page">
                                    <div class="form-group row"><label class="col-sm-4">Name</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo $session_params['name']; ?></div>	
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-4">Email</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo $session_params['email']; ?></div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-4">Guardian Phone</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo $session_params['guardian_phone']; ?></div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4">Fee</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo (amountFormat($session_params['total'])); ?></div>

                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4">Fine</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo amountFormat($session_params['payment_detail']->fine_amount); ?></div>

                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4">Discount</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo amountFormat((float) $session_params['applied_fee_discount'], 2, '.', ''); ?></div>

                                        </div>
                                    </div>
									
									<?php if($session_params['gateway_processing_charge']>0){ ?>
                                    <div class="form-group row">
                                        <label class="col-sm-4">Processing Fees</label>
                                        <div class="col-sm-8"><div class="form-control"><?php echo amountFormat($session_params['gateway_processing_charge']); ?></div></div>
                                    </div><!--./form-group-->
                                    <?php }?>
									
                                    
                                    <div class="form-group row">
                                        <label class="col-sm-4">Total</label>
                                        <div class="col-sm-8">
                                            <div class="form-control"><?php echo (amountFormat($session_params['payment_detail']->fine_amount + $session_params['total'] - $session_params['applied_fee_discount']+$session_params['gateway_processing_charge'])); ?></div>

                                        </div>
                                    </div>
                                   

                                     <div class="divider"></div>

                            <div id="stripe-payment-message" class="hidden"></div>

                            <form id="stripe-payment-form" class="paddtlrb" action="<?php echo site_url('user/gateway/stripe/complete'); ?>" method="POST">



                              


                                <input type='hidden' id='publishable_key' value='<?php echo $session_params['api_publishable_key']; ?>'>
                                <input type='hidden' id='currency' value='<?php echo $session_params['invoice']->currency_name; ?>'>
                                
                                <input type='hidden' id='description' value='<?php echo 'Online fees deposit'; ?>'>

                                <input type="hidden" id="baseurl" name="baseurl" value="<?php echo base_url();?>">
                                <input type="hidden" name="student_id" value="<?php echo $session_params['student_id']; ?>">
                                <input type="hidden" name="total" id="amount" value="<?php echo (convertBaseAmountCurrencyFormat($session_params['payment_detail']->fine_amount + $session_params['total'] - $session_params['applied_fee_discount']+$session_params['gateway_processing_charge'])); ?>">
                                  
                                        <div id="stripe-payment-element">
                                            <!--Stripe.js will inject the Payment Element here to get card details-->
                                        </div>
                                        <div class="button-between">
                                            <a type="button" href="<?php echo base_url('payment/paymentfailed')?>" name="search" value="" class="btn btn-info"><i class="fa fa fa-chevron-left"></i> <?php echo ('Back') ?></a>
                                            <button type="submit" class="pay btn btn-primary" id="submit-button"  data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Processing"> <i class="fa fa-money"></i> Pay Now</button>
                                            <div id="payment-reinitiate" class="hidden" >
                                <button class="btn btn-primary" type="button" onclick="reinitiateStripe()"> <i class="fa fa-money"></i>  Reinitiate Payment</button>
                            </div>
                                        </div>
                                     
                                </div>    

                            </form>
                                </div>
                            </div>		
                        </div><!--./paybox-->
                    </div><!--./paybox_bg-->
                </div><!--./col-md-12-->
            </div><!--./row-->
        </div><!--./container-->
       <script src="https://js.stripe.com/v3/"></script>
 <script src="<?php echo base_url('backend/js/stripe-checkout.js') ?>" defer></script>
    </body>
</html>