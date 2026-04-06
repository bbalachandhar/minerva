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
                        <div class="box-tools pull-right" style="display:flex;gap:8px;align-items:center;">
                            <select id="filter_submitted_by" class="form-control input-sm" style="width:150px;">
                                <option value="">All Submitted By</option>
                                <option value="student">By Direct Student</option>
                                <option value="staff">By Staff</option>
                            </select>
                            <select id="filter_form_status" class="form-control input-sm" style="width:150px;">
                                <option value="">All Course Fee Status</option>
                                <option value="applied">Applied</option>
                                <option value="0">Not Paid</option>
                                <option value="2">Partially Paid</option>
                                <option value="1">Fully Paid</option>
                            </select>
                            <select id="filter_quota" class="form-control input-sm" style="width:150px;">
                                <option value="">All Quota</option>
                                <option value="management">Management</option>
                                <option value="government">Government</option>
                            </select>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
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
                                         <?php if ($sch_setting->father_name) {?>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                        <?php }?>
                                        <th>Application Date</th>
                                        <th><?php echo $this->lang->line('submitted_by'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th>Quota Type</th>
                                                                                <th>Course Fee</th>
                                                                                <th>Paid Amount</th>
                                          <?php if ($sch_setting->mobile_no) {?>
                                        <th style="width:10%"><?php echo $this->lang->line('student_mobile_number'); ?></th>
                                       <?php }?>
                                        <th>Form Status</th>
                                        <th>Course Fee Status</th>
                                        <?php if ($sch_setting->online_admission_payment == 'yes') {?>
                                            <th>App. Fee</th>
                                            <?php }?>
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
    function loadStudentTable() {
        var params = {};
        var quota = $('#filter_quota').val();
        var status = $('#filter_form_status').val();
        var submittedBy = $('#filter_submitted_by').val();
        if (quota !== '') params.quota_type_filter = quota;
        if (status !== '') params.paid_status_filter = status;
        if (submittedBy !== '') params.submitted_by_filter = submittedBy;
        initDatatable('student-list', 'admin/onlinestudent/getstudentlist', params, [], 10);
        $('.student-list').off('draw.dt').on('draw.dt', function() {
            var info = $('.student-list').DataTable().page.info();
            $('#student-list-footer').text('Total Records: ' + info.recordsTotal);
        });
    }

    $(document).ready(function () {
        loadStudentTable();
        $('#filter_form_status, #filter_quota, #filter_submitted_by').on('change', function() {
            loadStudentTable();
        });

        // Override the generic Excel button to export online admission data
        // (the default initDatatable button is hardcoded to the login-detail report)
        $(document).on('click', '.btn-excel', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var quota  = $('#filter_quota').val();
            var status = $('#filter_form_status').val();
            var submittedBy = $('#filter_submitted_by').val();
            var url    = '<?php echo base_url("admin/onlinestudent/export_excel"); ?>?';
            if (quota  !== '') url += 'quota_type_filter='  + encodeURIComponent(quota)  + '&';
            if (status !== '') url += 'paid_status_filter=' + encodeURIComponent(status) + '&';
            if (submittedBy !== '') url += 'submitted_by_filter=' + encodeURIComponent(submittedBy) + '&';
            window.location.href = url;
        });

        // prevent any delegated row click from blocking anchor navigation
        $(document).on('click', '.student-list td a', function(e) {
            e.stopPropagation();
            // allow default action
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
                    '<p class="text-right" style="margin:2px 0;"><strong>Total Course Fee:&nbsp;&nbsp;&#8377; ' + data.total_fee + '</strong></p>' +
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