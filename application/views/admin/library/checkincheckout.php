<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-clock-o"></i> <?php echo $this->lang->line('library_checkin_checkout'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('library_checkin_checkout'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_number"><?php echo $this->lang->line('scan_biometric_id'); ?></label>
                                    <input type="text" class="form-control" id="id_number" autofocus autocomplete="off">
                                    <span class="text-danger" id="id_number_error"></span>
                                </div>
                                <div id="feedback_message" class="alert" style="display:none;"></div>
                            </div>
                        </div>

                        <div class="mailbox-messages table-responsive overflow-visible-1">
                            <table width="100%" class="table table-striped table-bordered table-hover attendance-list" data-export-title="<?php echo $this->lang->line('library_attendance'); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('biometric_user'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th><?php echo $this->lang->line('check_in'); ?></th>
                                        <th><?php echo $this->lang->line('check_out'); ?></th>
                                        <th><?php echo $this->lang->line('duration'); ?></th>
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
    var attendanceTable = $('.attendance-list').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + 'admin/library_checkin_checkout/get_attendance_dt',
            "type": "POST",
            "data": function (d) {}
        },
        "columns": [
            { "data": "user_id" },
            { "data": "name" },
            { "data": "attendance_date" },
            { "data": "in_time" },
            { "data": "out_time" },
            { "data": "duration" },
            {
                "data": "out_time",
                "render": function (data, type, row) {
                    if (data === null || data === "") {
                        return '<span class="label label-success"><?php echo $this->lang->line('in'); ?></span>';
                    } else {
                        return '<span class="label label-danger"><?php echo $this->lang->line('out'); ?></span>';
                    }
                }
            }
        ],
        "order": [[ 0, "desc" ]],
        "pageLength": 100,
        dom: '<"top"f><Bl>r<t>ip',
        buttons: [
            {
                extend:    'copy',
                text:      '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                className: "btn-copy",
                title: $('.attendance-list').data("exportTitle"),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend:    'excel',
                text:      '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                className: "btn-excel",
                title: $('.attendance-list').data("exportTitle"),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend:    'csv',
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                className: "btn-csv",
                title: $('.attendance-list').data("exportTitle"),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend:    'pdf',
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: "btn-pdf",
                title: $('.attendance-list').data("exportTitle"),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            },
            {
                extend:    'print',
                text:      '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                className: "btn-print",
                title: $('.attendance-list').data("exportTitle"),
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                }
            }
        ]
    });

    var typingTimer;                //timer identifier
    var doneTypingInterval = 500;  //time in ms, 500ms (0.5 seconds)

    // Handle barcode scanner input
    $('#id_number').on('keyup', function() {
        clearTimeout(typingTimer);
        if ($('#id_number').val()) {
            typingTimer = setTimeout(performScan, doneTypingInterval);
        }
    });

    function performScan() {
        var id_number = $('#id_number').val();
        $('#id_number_error').text(''); // Clear previous errors
        $('#feedback_message').hide().removeClass('alert-success alert-danger').text(''); // Clear feedback

        if (id_number) {
            $.ajax({
                url: base_url + 'admin/library_checkin_checkout/process_scan',
                type: 'POST',
                data: { id_number: id_number },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#feedback_message').text(response.message).addClass('alert-success').show();
                        attendanceTable.ajax.reload(); // Refresh DataTables
                    } else {
                        $('#feedback_message').text(response.message).addClass('alert-danger text-white').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#feedback_message').text('<?php echo $this->lang->line('an_error_occurred'); ?>').addClass('alert-danger').show();
                    console.error("AJAX Error: " + status + error);
                },
                complete: function() {
                    $('#id_number').val('').focus(); // Clear input and refocus for next scan
                }
            });
        } else {
            $('#id_number_error').text('<?php echo $this->lang->line('id_number_required'); ?>');
        }
    }

    // Optional: Add a date filter for the attendance table
    // You would need an input field for this, e.g., <input type="date" id="filter_date">
    // $('#filter_date').on('change', function() {
    //     attendanceTable.ajax.reload();
    // });
});
</script>