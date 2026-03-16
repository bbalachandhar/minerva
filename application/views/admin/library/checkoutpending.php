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
    var pendingTable = $('.pending-attendance-list').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + 'admin/library_checkout_pending/get_pending_dt',
            "type": "POST",
            "data": function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            }
        },
        "columns": [
            { "data": "user_id" },
            { "data": "name" },
            { "data": "attendance_date" },
            { "data": "in_time" },
            { "data": "out_time" },
            { "data": "time_spent" },
            {
                "data": "out_time",
                "render": function (data, type, row) {
                    if (data === null || data === "") {
                        return '<span class="label label-success"><?php echo $this->lang->line('in'); ?></span>';
                    }
                    return '<span class="label label-danger"><?php echo $this->lang->line('out'); ?></span>';
                }
            }
        ],
        "order": [[ 3, "asc" ]],
        "pageLength": 100,
        dom: '<"top"f><Bl>r<t>ip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                className: 'btn-copy',
                title: $('.pending-attendance-list').data('exportTitle'),
                exportOptions: {
                    columns: ['thead th:not(.noExport)']
                }
            },
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                className: 'btn-excel',
                title: $('.pending-attendance-list').data('exportTitle'),
                exportOptions: {
                    columns: ['thead th:not(.noExport)']
                }
            },
            {
                extend: 'csv',
                text: '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                className: 'btn-csv',
                title: $('.pending-attendance-list').data('exportTitle'),
                exportOptions: {
                    columns: ['thead th:not(.noExport)']
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: 'btn-pdf',
                title: $('.pending-attendance-list').data('exportTitle'),
                exportOptions: {
                    columns: ['thead th:not(.noExport)']
                }
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                className: 'btn-print',
                title: $('.pending-attendance-list').data('exportTitle'),
                exportOptions: {
                    columns: ['thead th:not(.noExport)']
                }
            }
        ]
    });

    $('#filter_form').on('submit', function(e) {
        e.preventDefault();
        pendingTable.ajax.reload();
    });
});
</script>