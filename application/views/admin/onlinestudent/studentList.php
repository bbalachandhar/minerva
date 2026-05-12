<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('student_list'); ?></h3>
                    </div><!-- /.box-header -->
                    <!-- Filter Bar -->
                    <div class="box-body" style="padding-bottom:8px;border-bottom:1px solid #f0f0f0;">
                        <!-- Row 1: Course filters -->
                        <div class="row" style="margin-bottom:8px;">
                            <div class="col-sm-4 col-md-3">
                                <select id="filter_course" class="form-control input-sm">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courseList as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-2 col-md-2">
                                <select id="filter_course_level" class="form-control input-sm">
                                    <option value="">All Levels</option>
                                    <option value="ug">UG</option>
                                    <option value="pg">PG</option>
                                </select>
                            </div>
                            <div class="col-sm-3 col-md-2">
                                <select id="filter_admission_type" class="form-control input-sm">
                                    <option value="">All Admission Types</option>
                                    <option value="first_year">First Year</option>
                                    <option value="lateral">Lateral</option>
                                </select>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="btn-group" id="filter_form_status_group" style="width:100%;">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:100%;text-align:left;">
                                        <span id="filter_form_status_label">All Course Fee Status</span> <span class="caret" style="float:right;margin-top:5px;"></span>
                                    </button>
                                    <ul class="dropdown-menu" style="min-width:175px;padding:6px 10px;">
                                        <li><label style="font-weight:normal;margin:0;"><input type="checkbox" class="filter-status-chk" value="applied"> Applied</label></li>
                                        <li><label style="font-weight:normal;margin:0;"><input type="checkbox" class="filter-status-chk" value="0"> Not Paid</label></li>
                                        <li><label style="font-weight:normal;margin:0;"><input type="checkbox" class="filter-status-chk" value="2"> Partially Paid</label></li>
                                        <li><label style="font-weight:normal;margin:0;"><input type="checkbox" class="filter-status-chk" value="1"> Fully Paid</label></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-2 col-md-2">
                                <div class="btn-group" id="filter_quota_group" style="width:100%;">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width:100%;text-align:left;">
                                        <span id="filter_quota_label">All Quota</span> <span class="caret" style="float:right;margin-top:5px;"></span>
                                    </button>
                                    <ul class="dropdown-menu" style="min-width:130px;padding:6px 10px;">
                                        <li><label style="font-weight:normal;margin:0;"><input type="checkbox" class="filter-quota-chk" value="management"> Management</label></li>
                                        <li><label style="font-weight:normal;margin:0;"><input type="checkbox" class="filter-quota-chk" value="government"> Government</label></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Row 2: Applicant / date filters -->
                        <div class="row">
                            <div class="col-sm-2 col-md-2">
                                <select id="filter_submitted_by" class="form-control input-sm">
                                    <option value="">All Submitted By</option>
                                    <option value="student">By Direct Student</option>
                                    <option value="staff">By Staff</option>
                                </select>
                            </div>
                            <div class="col-sm-5 col-md-4">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="filter_submit_from" class="form-control date datepicker-filter" placeholder="Submitted From" autocomplete="off" readonly>
                                    <span class="input-group-addon" style="background:#f4f4f4;padding:0 6px;">–</span>
                                    <input type="text" id="filter_submit_to" class="form-control date datepicker-filter" placeholder="Submitted To" autocomplete="off" readonly>
                                    <span class="input-group-btn">
                                        <button id="clear_submit_dates" class="btn btn-default" title="Clear" style="display:none;"><i class="fa fa-times"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="filter_last_payment_date" class="form-control date datepicker-filter" placeholder="Last Payment Date" autocomplete="off" readonly>
                                    <span class="input-group-btn">
                                        <button id="clear_payment_date" class="btn btn-default" title="Clear" style="display:none;"><i class="fa fa-times"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-addon" style="background:#f4f4f4;padding:0 8px;font-size:11px;white-space:nowrap;">Cut-Off</span>
                                    <input type="number" id="filter_cutoff_from" class="form-control" placeholder="From" min="0" max="300" step="0.01" autocomplete="off">
                                    <span class="input-group-addon" style="background:#f4f4f4;padding:0 6px;">–</span>
                                    <input type="number" id="filter_cutoff_to" class="form-control" placeholder="To" min="0" max="300" step="0.01" autocomplete="off">
                                    <span class="input-group-btn">
                                        <button id="clear_cutoff" class="btn btn-default" title="Clear" style="display:none;"><i class="fa fa-times"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div><!-- /.filter bar -->
                    <div class="box-body">
                      <div class="table-responsive">
                        <div class="mailbox-messages">
                             <?php if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');}?>
                            <table class="table table-striped table-bordered table-hover student-list" data-export-title="<?php echo $this->lang->line('student_list'); ?>">
                                <thead>
                                    <tr>
                                        <th style="width:5%"><?php echo $this->lang->line('reference_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th class="white-space-nowrap">Course</th>
                                        <th>Admission Type</th>
                                        <th>Application Date</th>
                                        <th><?php echo $this->lang->line('submitted_by'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th>Quota Type</th>
                                        <th>Cut-Off</th>
                                                                                <th>Course Fee</th>
                                                                                <th>Paid Amount</th>
                                          <?php if ($sch_setting->mobile_no) {?>
                                        <th style="width:10%"><?php echo $this->lang->line('student_mobile_number'); ?></th>
                                       <?php }?>
                                        <th>Form Status</th>
                                        <th>Course Fee Status</th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                        <div style="text-align:right;font-weight:bold;padding:6px 4px;" id="student-list-footer"></div>
                       </div><!--./table-responsive-->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- Fee Receipt Modal -->
<div class="modal fade" id="feeReceiptModal" tabindex="-1" role="dialog" aria-labelledby="feeReceiptModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="feeReceiptModalLabel"><i class="fa fa-file-text-o"></i> Fee Payment Receipt</h4>
            </div>
            <div class="modal-body" id="feeReceiptBody">
                <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printFeeReceipt()"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel">
    <div class="modal-dialog" role="document">
        <form id="add_payment_form" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addPaymentModalLabel"><?php echo $this->lang->line('add_payment'); ?></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="online_admission_id" id="online_admission_id" value="">
                    <div class="form-group">
                        <label for="reference_no"><?php echo $this->lang->line('reference_no'); ?></label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" readonly>
                    </div>
                    <div class="form-group">
                        <label for="transaction_id"><?php echo $this->lang->line('transaction_id'); ?> <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                    </div>
                    <div class="form-group">
                        <label for="note"><?php echo $this->lang->line('note'); ?> <span style="color: red;">*</span></label>
                        <textarea class="form-control" id="note" name="note" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    ( function ( $ ) {
    'use strict';
    var studentTable;

    function getFilterParams() {
        var params = {};
        var quota = [];
        $('.filter-quota-chk:checked').each(function () { quota.push($(this).val()); });
        var status = [];
        $('.filter-status-chk:checked').each(function () { status.push($(this).val()); });
        var submittedBy     = $('#filter_submitted_by').val();
        var submitFrom      = $('#filter_submit_from').val();
        var submitTo        = $('#filter_submit_to').val();
        var lastPaymentDate = $('#filter_last_payment_date').val();
        var cutoffFrom      = $('#filter_cutoff_from').val();
        var cutoffTo        = $('#filter_cutoff_to').val();
        var courseId        = $('#filter_course').val();
        var courseLevel     = $('#filter_course_level').val();
        var admissionType   = $('#filter_admission_type').val();
        if (quota.length   > 0) params.quota_type_filter  = quota;
        if (status.length  > 0) params.paid_status_filter = status;
        if (submittedBy     !== '') params.submitted_by_filter    = submittedBy;
        if (submitFrom      !== '') params.submit_date_from       = submitFrom;
        if (submitTo        !== '') params.submit_date_to         = submitTo;
        if (lastPaymentDate !== '') params.last_payment_date      = lastPaymentDate;
        if (courseId        !== '') params.course_id_filter       = courseId;
        if (courseLevel     !== '') params.course_level_filter    = courseLevel;
        if (admissionType   !== '') params.admission_type_filter  = admissionType;
        if (cutoffFrom      !== '') params.cutoff_from             = cutoffFrom;
        if (cutoffTo        !== '') params.cutoff_to               = cutoffTo;
        return params;
    }

    function updateDropdownLabel(checkboxClass, labelId, defaultText) {
        var selected = [];
        $('.' + checkboxClass + ':checked').each(function () {
            selected.push($(this).closest('label').text().trim());
        });
        $('#' + labelId).text(selected.length > 0 ? selected.join(', ') : defaultText);
    }

    $(document).ready(function () {
        // Pre-select filter from dashboard eye icon links (?preset_filter=...)
        (function () {
            var params = new URLSearchParams(window.location.search);
            var preset = params.get('preset_filter');
            if (!preset) return;
            var map = {
                'application_received': ['1', '2', 'applied'],
                'fully_paid':           ['1'],
                'partially_paid':       ['2'],
                'only_app_fee_paid':    ['applied']
            };
            var vals = map[preset];
            if (!vals) return;
            $('.filter-status-chk').each(function () {
                if (vals.indexOf($(this).val()) !== -1) {
                    $(this).prop('checked', true);
                }
            });
            updateDropdownLabel('filter-status-chk', 'filter_form_status_label', 'All Course Fee Status');
        })();

        studentTable = $('.student-list').DataTable({
            dom: '<"top"f><Bl>r<t>ip',
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            buttons: [
                {
                    extend:    'copy',
                    text:      '<i class="fa fa-files-o"></i>',
                    titleAttr: 'Copy',
                    className: 'btn-copy',
                    title: $('.student-list').data('export-title'),
                    exportOptions: { columns: ['thead th:not(.noExport)'] }
                },
                {
                    text:      '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    className: 'btn-excel',
                    action: function () {
                        var p = getFilterParams();
                        var url = '<?php echo base_url("admin/onlinestudent/export_excel"); ?>?';
                        $.each(p, function (k, v) {
                            if ($.isArray(v)) {
                                $.each(v, function (i, val) { url += encodeURIComponent(k + '[]') + '=' + encodeURIComponent(val) + '&'; });
                            } else {
                                url += encodeURIComponent(k) + '=' + encodeURIComponent(v) + '&';
                            }
                        });
                        window.location.href = url;
                    }
                },
                {
                    text:      '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    className: 'btn-csv',
                    action: function () {
                        var p = getFilterParams();
                        var url = '<?php echo base_url("admin/onlinestudent/export_excel"); ?>?format=csv&';
                        $.each(p, function (k, v) {
                            if ($.isArray(v)) {
                                $.each(v, function (i, val) { url += encodeURIComponent(k + '[]') + '=' + encodeURIComponent(val) + '&'; });
                            } else {
                                url += encodeURIComponent(k) + '=' + encodeURIComponent(v) + '&';
                            }
                        });
                        window.location.href = url;
                    }
                },
                {
                    extend:    'print',
                    text:      '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    className: 'btn-print',
                    title: $('.student-list').data('export-title'),
                    customize: function (win) {
                        $(win.document.body).find('th').addClass('display').css('text-align', 'center');
                        $(win.document.body).find('table').addClass('display').css('font-size', '14px');
                        $(win.document.body).find('td').addClass('display').css('text-align', 'left');
                        $(win.document.body).find('h1').css('text-align', 'center');
                    },
                    exportOptions: { columns: ['thead th:not(.noExport)'] }
                }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> ',
                sLengthMenu: '_MENU_'
            },
            pageLength: 10,
            searching: true,
            aaSorting: [],
            aoColumnDefs: [{ bSortable: false, aTargets: [-1], sClass: 'dt-body-right' }],
            processing: true,
            serverSide: true,
            ajax: {
                url:     '<?php echo base_url("admin/onlinestudent/getstudentlist"); ?>',
                type:    'POST',
                dataSrc: 'data',
                data: function (d) {
                    return $.extend({}, d, getFilterParams());
                }
            }
        });

        studentTable.on('draw.dt', function () {
            var info = studentTable.page.info();
            $('#student-list-footer').text('Total Records: ' + info.recordsTotal);
        });

        // Single-select change
        $('#filter_submitted_by, #filter_course, #filter_course_level, #filter_admission_type').on('change', function () {
            studentTable.ajax.reload();
        });

        // Dropdown checkboxes — keep open when clicking, update label, reload table
        $(document).on('click', '#filter_form_status_group .dropdown-menu, #filter_quota_group .dropdown-menu', function (e) {
            e.stopPropagation();
        });
        $(document).on('change', '.filter-status-chk', function () {
            updateDropdownLabel('filter-status-chk', 'filter_form_status_label', 'All Course Fee Status');
            studentTable.ajax.reload();
        });
        $(document).on('change', '.filter-quota-chk', function () {
            updateDropdownLabel('filter-quota-chk', 'filter_quota_label', 'All Quota');
            studentTable.ajax.reload();
        });

        // Date filters — use the app's bootstrap-datepicker (class 'date' is handled globally).
        // We just need to react to changeDate and update the table.
        $(document).on('changeDate', '.datepicker-filter', function () {
            var submitFrom = $('#filter_submit_from').val();
            var submitTo   = $('#filter_submit_to').val();
            var payDate    = $('#filter_last_payment_date').val();
            if (submitFrom !== '' || submitTo !== '') {
                $('#clear_submit_dates').show();
            } else {
                $('#clear_submit_dates').hide();
            }
            if (payDate !== '') {
                $('#clear_payment_date').show();
            } else {
                $('#clear_payment_date').hide();
            }
            studentTable.ajax.reload();
        });

        $('#clear_submit_dates').on('click', function () {
            $('#filter_submit_from, #filter_submit_to').val('');
            $(this).hide();
            studentTable.ajax.reload();
        });

        $('#clear_payment_date').on('click', function () {
            $('#filter_last_payment_date').val('');
            $(this).hide();
            studentTable.ajax.reload();
        });

        // Cutoff filter — reload on input change
        $(document).on('input change', '#filter_cutoff_from, #filter_cutoff_to', function () {
            var cf = $('#filter_cutoff_from').val();
            var ct = $('#filter_cutoff_to').val();
            if (cf !== '' || ct !== '') {
                $('#clear_cutoff').show();
            } else {
                $('#clear_cutoff').hide();
            }
            studentTable.ajax.reload();
        });

        $('#clear_cutoff').on('click', function () {
            $('#filter_cutoff_from, #filter_cutoff_to').val('');
            $(this).hide();
            studentTable.ajax.reload();
        });

        // prevent any delegated row click from blocking anchor navigation
        $(document).on('click', '.student-list td a', function (e) {
            e.stopPropagation();
        });
    });
} ( jQuery ) )
</script>

<script>
    function checkpaymentstatus(id){
       $.ajax({
            url: '<?php echo base_url(); ?>admin/onlinestudent/checkpaymentstatus',
            type: "POST",
            data: {id:id},
            success: function (data) {

               if(data!=""){
                    if(confirm(data)){
                      window.location.href="<?php echo base_url() . 'admin/onlinestudent/edit/' ?>"+id ;
                    }else{
                         return false ;
                    }
                }else{
                     window.location.href="<?php echo base_url() . 'admin/onlinestudent/edit/' ?>"+id ;
                }
            }
        });
    }

    function addpayment(id, reference_no){
        $('#online_admission_id').val(id);
        $('#reference_no').val(reference_no);
        $('#addPaymentModal').modal('show');
    }

    function viewFeeReceipt(ref_no) {
        $('#feeReceiptBody').html('<div class="text-center" style="padding:30px;"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="text-muted" style="margin-top:10px;">Loading receipt...</p></div>');
        $('#feeReceiptModal').modal('show');
        $.ajax({
            url: '<?php echo base_url("admin/onlinestudent/fee_receipt"); ?>',
            type: 'POST',
            data: { ref_no: ref_no },
            dataType: 'json',
            success: function(data) {
                if (data.status !== 'success') {
                    $('#feeReceiptBody').html('<div class="alert alert-danger">' + (data.message || 'Failed to load receipt.') + '</div>');
                    return;
                }
                var s = data.student;
                var payments = data.payments;
                var rows = '';
                if (payments.length === 0) {
                    rows = '<tr><td colspan="6" class="text-center text-muted">No payment records found.</td></tr>';
                } else {
                    $.each(payments, function(i, p) {
                        var mode = p.payment_mode || '—';
                        var ref  = p.txn_id || p.cheque_no || '';
                        rows += '<tr>' +
                            '<td>' + (i + 1) + '</td>' +
                            '<td>' + (p.date || '—') + '</td>' +
                            '<td>' + (p.receipt_no || '—') + '</td>' +
                            '<td>' + (p.fee_type || '—') + '</td>' +
                            '<td>' + mode + (ref ? ' / ' + ref : '') + '</td>' +
                            '<td class="text-right"><strong>&#8377; ' + p.amount + '</strong></td>' +
                        '</tr>';
                    });
                }
                var totalFee  = parseFloat(data.total_fee.replace(/,/g,''));
                var totalPaid = parseFloat(data.total_paid.replace(/,/g,''));
                var balance   = parseFloat(data.balance.replace(/,/g,''));

                var html = '<div id="receiptPrintArea" style="padding:5px 10px;">' +
                    /* School header */
                    '<div class="text-center" style="margin-bottom:10px;">' +
                        (data.header_image ? '<img src="' + data.header_image + '" style="width:100%;max-height:120px;object-fit:contain;display:block;margin-bottom:6px;">' : '') +
                        '<h4 style="margin:0 0 3px;font-weight:700;">' + (data.school_name || '') + '</h4>' +
                        '<p style="margin:0;font-size:12px;">' +
                            (data.school_phone ? 'Ph: ' + data.school_phone : '') +
                            (data.school_email ? '&nbsp;&nbsp;|&nbsp;&nbsp;' + data.school_email : '') +
                        '</p>' +
                        '<h5 style="margin:8px 0 0;text-decoration:underline;letter-spacing:1px;"><strong>ONLINE ADMISSION FEE RECEIPT</strong></h5>' +
                    '</div>' +
                    '<hr style="margin:8px 0;">' +
                    /* Applicant details — left / right split */
                    '<div class="row" style="margin-bottom:4px;">' +
                        '<div class="col-xs-6">' +
                            '<strong>Applicant Name:</strong> ' + s.name + '<br>' +
                            '<strong>Reference No.:</strong> ' + s.ref_no + '<br>' +
                            '<strong>Course:</strong> ' + s.course + '<br>' +
                        '</div>' +
                        '<div class="col-xs-6 text-right">' +
                            '<strong>Quota:</strong> ' + (s.quota_type || '—') + '<br>' +
                            '<strong>Mobile:</strong> ' + (s.mobile || '—') + '<br>' +
                            '<strong>Email:</strong> ' + (s.email || '—') + '<br>' +
                        '</div>' +
                    '</div>' +
                    '<hr style="margin:8px 0;">' +
                    /* Payment history table */
                    '<h5 style="margin:0 0 8px;"><strong>Payment History</strong></h5>' +
                    '<div class="table-responsive">' +
                        '<table class="table table-striped table-bordered" style="font-size:13px;">' +
                            '<thead><tr>' +
                                '<th>#</th>' +
                                '<th>Date</th>' +
                                '<th>Receipt No.</th>' +
                                '<th>Fee Type</th>' +
                                '<th>Payment Mode</th>' +
                                '<th class="text-right">Amount (&#8377;)</th>' +
                            '</tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                    '</div>' +
                    /* Summary */
                    '<p class="text-right" style="margin:2px 0;"><strong>Total Fee:&nbsp;&nbsp;&#8377; ' + data.total_fee + '</strong></p>' +
                    '<p class="text-right" style="margin:2px 0;color:green;"><strong>Total Paid:&nbsp;&nbsp;&#8377; ' + data.total_paid + '</strong></p>' +
                    '<p class="text-right ' + (balance > 0 ? 'text-danger' : 'text-success') + '" style="margin:2px 0;"><strong>Balance Due:&nbsp;&nbsp;&#8377; ' + data.balance + '</strong></p>' +
                '</div>';
                $('#feeReceiptBody').html(html);
            },
            error: function() {
                $('#feeReceiptBody').html('<div class="alert alert-danger">Failed to load receipt. Please try again.</div>');
            }
        });
    }

    function printFeeReceipt() {
        var content = document.getElementById('receiptPrintArea');
        if (!content) return;
        var html = '<html><head><title>Fee Receipt</title>' +
            '<link rel="stylesheet" href="<?php echo base_url("backend/bootstrap/css/bootstrap.min.css"); ?>">' +
            '<style>body{font-family:Arial,sans-serif;padding:20px;}@media print{.no-print{display:none;}}</style>' +
            '</head><body>' + content.innerHTML + '</body></html>';
        var w = window.open('', '_blank', 'width=800,height=700');
        w.document.write(html);
        w.document.close();
        w.focus();
        setTimeout(function(){ w.print(); }, 600);
    }

    $(document).ready(function () {
        $('#add_payment_form').on('submit', function (e) {
            e.preventDefault();
            var $this = $(this);
            $.ajax({
                url: '<?php echo site_url("admin/onlinestudent/addpayment") ?>',
                type: "POST",
                data: $this.serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        $('#addPaymentModal').modal('hide');
                        $('.student-list').DataTable().ajax.reload();
                    }
                }
            });
        });
    });
</script>

<?php if ($this->rbac->hasPrivilege('admission_cancellation', 'can_add')): ?>
<!-- =====================================================================
     REVOKE ADMISSION MODAL
     ===================================================================== -->
<div class="modal fade" id="revokeAdmissionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="revoke_admission_form">
            <div class="modal-content">
                <div class="modal-header bg-red-faint">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title text-danger"><i class="fa fa-ban"></i> <?php echo $this->lang->line('revoke_admission'); ?></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="admission_id" id="revoke_admission_id">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('reference_no'); ?></label>
                                <input type="text" class="form-control" id="revoke_ref_no" readonly>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('student_name'); ?></label>
                                <input type="text" class="form-control" id="revoke_applicant_name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('total_paid_amount'); ?> <small class="text-muted">(excl. application fee)</small></label>
                        <div class="input-group">
                            <span class="input-group-addon"><?php echo $this->customlib->getSchoolCurrencyFormat(); ?></span>
                            <input type="text" class="form-control" id="revoke_total_paid" readonly placeholder="Loading...">
                        </div>
                        <small class="text-muted">Course / tuition fees only — application fee is non-refundable</small>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('refund_amount'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><?php echo $this->customlib->getSchoolCurrencyFormat(); ?></span>
                                    <input type="number" name="refund_amount" class="form-control" id="revoke_refund_amount"
                                           min="0" step="0.01" placeholder="0.00" max="0">
                                </div>
                                <small class="text-muted">Cannot exceed course fee paid (application fee excluded)</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('refund_mode'); ?></label>
                                <select name="refund_mode" class="form-control" id="revoke_refund_mode">
                                    <option value="">— Select Mode —</option>
                                    <option value="cash">Cash</option>
                                    <option value="neft">NEFT / IMPS</option>
                                    <option value="upi">UPI</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="dd">Demand Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('cancellation_reason'); ?> <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required
                                  placeholder="State the reason for revoking this admission..."></textarea>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('remarks'); ?></label>
                        <textarea name="remarks" class="form-control" rows="2"
                                  placeholder="Optional — additional notes"></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> <?php echo $this->lang->line('revoke_confirm_message'); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-danger" id="revoke_submit_btn">
                        <i class="fa fa-ban"></i> <?php echo $this->lang->line('revoke_admission'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function ($) {
    'use strict';

    window.openRevokeModal = function (admissionId) {
        // Reset form
        $('#revoke_admission_form')[0].reset();
        $('#revoke_admission_id').val(admissionId);
        $('#revoke_ref_no').val('');
        $('#revoke_applicant_name').val('');
        $('#revoke_total_paid').val('Loading...');
        $('#revoke_refund_amount').val('');

        // Load payment summary
        $.ajax({
            url: '<?php echo site_url("admin/admission_cancellation/get_payment_summary"); ?>/' + admissionId,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    var refundable = parseFloat(res.refundable_amount || 0);
                    $('#revoke_ref_no').val(res.ref_no || '');
                    $('#revoke_applicant_name').val(res.name || '');
                    $('#revoke_total_paid').val(refundable.toFixed(2));
                    $('#revoke_refund_amount')
                        .val(refundable.toFixed(2))
                        .attr('max', refundable.toFixed(2));
                } else {
                    $('#revoke_total_paid').val('0.00');
                    errorMsg(res.message || 'Could not load payment summary.');
                }
            },
            error: function () {
                $('#revoke_total_paid').val('0.00');
            }
        });

        $('#revokeAdmissionModal').modal('show');
    };

    $(document).ready(function () {
        $('#revoke_admission_form').on('submit', function (e) {
            e.preventDefault();
            var admissionId = $('#revoke_admission_id').val();
            if (!admissionId) return;

            var $btn = $('#revoke_submit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: '<?php echo site_url("admin/admission_cancellation/cancel"); ?>/' + admissionId,
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-ban"></i> <?php echo $this->lang->line("revoke_admission"); ?>');
                    if (res.status === 'success') {
                        successMsg(res.message);
                        $('#revokeAdmissionModal').modal('hide');
                        $('.student-list').DataTable().ajax.reload();
                    } else {
                        errorMsg(res.message || 'Failed to revoke admission.');
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).html('<i class="fa fa-ban"></i> <?php echo $this->lang->line("revoke_admission"); ?>');
                    errorMsg('Server error. Please try again.');
                }
            });
        });
    });
}(jQuery));
</script>
<?php endif; ?>