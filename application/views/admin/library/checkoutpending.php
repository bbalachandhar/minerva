<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('library_checkout_pending'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('library_checkout_pending'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <form role="form" action="" method="post" class="form-horizontal" id="filter_form">
                                <div class="box-body">
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group" style="margin-right: 15px;">
                                            <label><?php echo $this->lang->line('from_date'); ?></label>
                                            <input type="text" name="start_date" id="start_date" class="form-control datepicker">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group" style="margin-right: 15px;">
                                            <label><?php echo $this->lang->line('to_date'); ?></label>
                                            <input type="text" name="end_date" id="end_date" class="form-control datepicker">
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="form-group" style="margin-top: 25px;">
                                            <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('filter'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="mailbox-messages table-responsive overflow-visible-1">
                            <table width="100%" class="table table-striped table-bordered table-hover pending-attendance-list" data-export-title="<?php echo $this->lang->line('library_checkout_pending'); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('biometric_user'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th><?php echo $this->lang->line('check_in'); ?></th>
                                        <th><?php echo $this->lang->line('check_out'); ?></th>
                                        <th><?php echo $this->lang->line('time_spent'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Initialize the full datatable on page load to show all controls
    initDatatable('pending-attendance-list', 'admin/library_checkout_pending/get_pending_dt', {}, [], 100, [], true, [], 'data');

    // Handle the filter form submission to reload the table with new date parameters
    $('#filter_form').on('submit', function(e) {
        e.preventDefault();
        
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();

        var params = {
            start_date: start_date,
            end_date: end_date
        };

        initDatatable('pending-attendance-list', 'admin/library_checkout_pending/get_pending_dt', params, [], 100, [], true, [], 'data');
    });
});
</script>