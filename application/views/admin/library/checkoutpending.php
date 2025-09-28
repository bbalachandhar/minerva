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
    // Initialize DataTables
    var pendingAttendanceTable = $('.pending-attendance-list').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + 'admin/library_checkout_pending/get_pending_dt',
            "type": "POST",
            "data": function (d) {
                // No specific filters for now, but can be added here
            }
        },
        "columns": [
            { "data": "user_id" },
            { "data": "name" },
            { "data": "attendance_date" },
            { "data": "in_time" },
            { "data": "out_time" },
            { "data": "time_spent" }, // This is the calculated column from the model
            {
                "data": "out_time", // Still using out_time to determine status
                "render": function (data, type, row) {
                    // For pending, out_time should always be null, so status is 'In'
                    return '<span class="label label-success"><?php echo $this->lang->line('in'); ?></span>';
                }
            }
        ],
        "order": [[ 0, "asc" ]], // Order by oldest check-in first
        "dom": '<"top">rt<"bottom"ip><"clear">'
    });
});
</script>