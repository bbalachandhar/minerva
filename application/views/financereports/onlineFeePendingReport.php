<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('online_fee_pending_report'); ?></h3>
                    </div>
                    
                    <div class="box-body table-responsive">
                        <?php if (empty($pending_transactions)) { ?>
                            <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                        <?php } else { ?>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Gateway</th>
                                        <th>Status</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_transactions as $transaction) { ?>
                                    <tr>
                                        <td><?php echo $transaction['unique_id']; ?></td>
                                        <td><?php echo $transaction['gateway_name']; ?></td>
                                        <td>
                                            <span class="label label-warning"><?php echo ucfirst($transaction['payment_status']); ?></span>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?php echo base_url('financereports/check_status/' . $transaction['unique_id'] . '/' . $transaction['gateway_name']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Check Status from Gateway">
                                                <i class="fa fa-refresh"></i> Check Status
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>   
    </section>
</div>
