<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="content-wrapper">

<section class="content-header">
    <h1><?php echo $this->lang->line('revoked_admissions'); ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/admin'); ?>"><i class="fa fa-dashboard"></i> <?php echo $this->lang->line('home'); ?></a></li>
        <li class="active"><?php echo $this->lang->line('revoked_admissions'); ?></li>
    </ol>
</section>

<section class="content">

    <!-- Summary count boxes -->
    <div class="row">
        <div class="col-xs-12 col-sm-4">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-ban"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('total_revoked'); ?></span>
                    <span class="info-box-number"><?php echo count($cancelled_admissions); ?></span>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('refund_pending'); ?></span>
                    <span class="info-box-number"><?php echo isset($refund_counts['pending']) ? $refund_counts['pending'] : 0; ?></span>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-4">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('refund_processed'); ?></span>
                    <span class="info-box-number"><?php echo isset($refund_counts['processed']) ? $refund_counts['processed'] : 0; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo $this->lang->line('revoked_admissions_list'); ?></h3>
                    <div class="box-tools pull-right">
                        <a href="<?php echo site_url('admin/onlinestudent'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back_to_admissions'); ?>
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <?php if ($this->session->flashdata('msg')): ?>
                        <?php echo $this->session->flashdata('msg'); ?>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="revoked-admissions-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('reference_no'); ?></th>
                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                    <th><?php echo $this->lang->line('course'); ?></th>
                                    <th><?php echo $this->lang->line('mobile'); ?></th>
                                    <th><?php echo $this->lang->line('total_paid'); ?></th>
                                    <th><?php echo $this->lang->line('refund_amount'); ?></th>
                                    <th><?php echo $this->lang->line('refund_mode'); ?></th>
                                    <th><?php echo $this->lang->line('refund_status'); ?></th>
                                    <th><?php echo $this->lang->line('cancelled_by'); ?></th>
                                    <th><?php echo $this->lang->line('cancelled_on'); ?></th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($cancelled_admissions)): ?>
                                <?php $i = 1; foreach ($cancelled_admissions as $row): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                    <td><?php echo htmlspecialchars(trim($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_name'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['mobileno'] ?: '—'); ?></td>
                                    <td><strong><?php echo $this->customlib->getSchoolCurrencyFormat() . number_format((float) $row['total_paid_amount'], 2); ?></strong></td>
                                    <td><?php echo $this->customlib->getSchoolCurrencyFormat() . number_format((float) $row['refund_amount'], 2); ?></td>
                                    <td><?php echo $row['refund_mode'] ? ucfirst($row['refund_mode']) : '—'; ?></td>
                                    <td>
                                        <?php
                                        $rs = $row['refund_status'] ?? 'pending';
                                        if ($rs === 'processed') {
                                            $label_class = 'success';
                                        } elseif ($rs === 'rejected') {
                                            $label_class = 'danger';
                                        } elseif ($rs === 'voided') {
                                            $label_class = 'default';
                                        } else {
                                            $label_class = 'warning';
                                        }
                                        echo '<span class="label label-' . $label_class . '">' . ucfirst($rs) . '</span>';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['cancelled_by_name'] ?: '—'); ?></td>
                                    <td><?php echo $row['cancelled_at'] ? date($this->customlib->getSchoolDateFormat(), strtotime($row['cancelled_at'])) : '—'; ?></td>
                                    <td class="text-right">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                Actions <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-right">
                                                <li>
                                                    <a href="#" onclick="viewRevocationDetail(<?php echo htmlspecialchars(json_encode($row)); ?>); return false;">
                                                        <i class="fa fa-eye"></i> View Details
                                                    </a>
                                                </li>
                                                <?php if (($row['refund_status'] ?? 'pending') === 'pending' && $this->rbac->hasPrivilege('admission_cancellation', 'can_edit')): ?>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="#" onclick="openUpdateRefundModal(<?php echo (int) $row['refund_id']; ?>, '<?php echo htmlspecialchars($row['reference_no']); ?>', '<?php echo number_format((float) $row['refund_amount'], 2); ?>'); return false;">
                                                        <i class="fa fa-pencil"></i> Update Refund
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" onclick="openReadmitModal(<?php echo (int) $row['admission_id']; ?>, '<?php echo htmlspecialchars($row['reference_no']); ?>', '<?php echo htmlspecialchars(trim($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname'])); ?>'); return false;">
                                                        <i class="fa fa-undo"></i> Readmit
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="12" class="text-center text-muted">No revoked admissions found.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     REVOCATION DETAIL MODAL
     ===================================================================== -->
<div class="modal fade" id="revocationDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-red-faint">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-ban text-danger"></i> Revocation Detail</h4>
            </div>
            <div class="modal-body" id="revocationDetailBody">
                <!-- Populated by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     UPDATE REFUND STATUS MODAL
     ===================================================================== -->
<div class="modal fade" id="updateRefundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="update_refund_form">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-pencil"></i> Update Refund Status</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="refund_id" id="update_refund_id">
                    <div class="form-group">
                        <label><?php echo $this->lang->line('reference_no'); ?></label>
                        <input type="text" class="form-control" id="update_refund_ref_display" readonly>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('refund_amount'); ?></label>
                        <input type="text" class="form-control" id="update_refund_amount_display" readonly>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('refund_status'); ?> <span class="text-danger">*</span></label>
                        <select name="refund_status" id="update_refund_status" class="form-control" required>
                            <option value="">— Select Status —</option>
                            <option value="processed"><?php echo $this->lang->line('processed'); ?></option>
                            <option value="rejected"><?php echo $this->lang->line('rejected'); ?></option>
                        </select>
                    </div>
                    <div class="form-group" id="refund_ref_group">
                        <label><?php echo $this->lang->line('refund_reference_no'); ?> <span class="text-danger" id="refund_ref_required_star">*</span></label>
                        <input type="text" name="refund_reference_no" id="update_refund_reference_no" class="form-control"
                               placeholder="UTR / Cheque No. / DD No.">
                        <small class="text-muted">Required when status is Processed</small>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('remarks'); ?></label>
                        <textarea name="remarks" class="form-control" rows="2"
                                  placeholder="Optional remarks (e.g. deduction details, rejection reason)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =====================================================================
     READMIT MODAL
     ===================================================================== -->
<div class="modal fade" id="readmitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="readmit_form">
            <div class="modal-content">
                <div class="modal-header bg-warning-faint">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-undo text-warning"></i> Readmit Application</h4>
                </div>
                <div class="modal-body">
                    <div class="callout callout-warning">
                        <p><strong>This will cancel the revocation</strong> — the application will be restored to <em>Active</em> status and the pending refund will be voided.</p>
                        <p class="mb-0">Only allowed when refund has <strong>not yet been processed</strong>.</p>
                    </div>
                    <input type="hidden" name="admission_id" id="readmit_admission_id">
                    <div class="form-group">
                        <label>Reference No.</label>
                        <input type="text" class="form-control" id="readmit_ref_display" readonly>
                    </div>
                    <div class="form-group">
                        <label>Applicant Name</label>
                        <input type="text" class="form-control" id="readmit_name_display" readonly>
                    </div>
                    <div class="form-group">
                        <label>Reason for Readmission <span class="text-danger">*</span></label>
                        <textarea name="readmit_reason" id="readmit_reason" class="form-control" rows="3"
                                  placeholder="Enter reason for readmitting this application (mandatory)" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-undo"></i> Confirm Readmit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function ($) {
    'use strict';

    // DataTable
    $(document).ready(function () {
        $('#revoked-admissions-table').DataTable({
            dom: '<"top"f>rt<"bottom"lip>',
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            pageLength: 25,
            order: [[10, 'desc']],
            language: { sLengthMenu: '_MENU_' }
        });

        // Show/hide refund_ref required hint based on status
        $('#update_refund_status').on('change', function () {
            var s = $(this).val();
            $('#refund_ref_required_star').toggle(s === 'processed');
        });

        // Submit update refund form
        $('#update_refund_form').on('submit', function (e) {
            e.preventDefault();
            var status = $('#update_refund_status').val();
            var refRef = $('#update_refund_reference_no').val().trim();
            if (!status) {
                errorMsg('Please select a refund status.');
                return;
            }
            if (status === 'processed' && refRef === '') {
                errorMsg('Refund reference number is required when marking as Processed.');
                return;
            }

            var $btn = $(this).find('[type="submit"]').prop('disabled', true).text('Saving...');

            $.ajax({
                url: '<?php echo site_url("admin/admission_cancellation/update_refund"); ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {
                    $btn.prop('disabled', false).text('Save');
                    if (res.status === 'success') {
                        successMsg(res.message);
                        $('#updateRefundModal').modal('hide');
                        setTimeout(function () { location.reload(); }, 1200);
                    } else {
                        errorMsg(res.message || 'Error updating refund.');
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).text('Save');
                    errorMsg('Server error. Please try again.');
                }
            });
        });
    });

    // Open readmit modal
    window.openReadmitModal = function (admissionId, refNo, name) {
        $('#readmit_admission_id').val(admissionId);
        $('#readmit_ref_display').val(refNo);
        $('#readmit_name_display').val(name);
        $('#readmit_reason').val('');
        $('#readmitModal').modal('show');
    };

    // Submit readmit form
    $('#readmit_form').on('submit', function (e) {
        e.preventDefault();
        var reason = $('#readmit_reason').val().trim();
        if (!reason) {
            errorMsg('Reason for readmission is required.');
            return;
        }
        if (!confirm('Are you sure you want to readmit this application? The pending refund will be voided and the application will be restored to Active status.')) {
            return;
        }
        var $btn = $(this).find('[type="submit"]').prop('disabled', true).text('Processing...');
        $.ajax({
            url: '<?php echo site_url("admin/admission_cancellation/readmit"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false).text('Confirm Readmit');
                if (res.status === 'success') {
                    successMsg(res.message);
                    $('#readmitModal').modal('hide');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    errorMsg(res.message || 'Error processing readmit.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text('Confirm Readmit');
                errorMsg('Server error. Please try again.');
            }
        });
    });

    // Open update refund modal
    window.openUpdateRefundModal = function (refundId, refNo, amount) {
        $('#update_refund_id').val(refundId);
        $('#update_refund_ref_display').val(refNo);
        $('#update_refund_amount_display').val(amount);
        $('#update_refund_status').val('');
        $('#update_refund_reference_no').val('');
        $('[name="remarks"]', '#update_refund_form').val('');
        $('#updateRefundModal').modal('show');
    };

    // View revocation detail modal
    window.viewRevocationDetail = function (row) {
        var html = '<table class="table table-condensed table-bordered">';
        html += '<tr><th>Reference No.</th><td>' + row.reference_no + '</td>';
        html += '<th>Name</th><td>' + (row.firstname + ' ' + (row.middlename || '') + ' ' + row.lastname).trim() + '</td></tr>';
        html += '<tr><th>Course</th><td>' + (row.course_name || '—') + '</td>';
        html += '<th>Mobile</th><td>' + (row.mobileno || '—') + '</td></tr>';
        html += '<tr><th>Total Paid</th><td class="text-danger"><strong>' + parseFloat(row.total_paid_amount || 0).toFixed(2) + '</strong></td>';
        html += '<th>Refund Amount</th><td class="text-success"><strong>' + parseFloat(row.refund_amount || 0).toFixed(2) + '</strong></td></tr>';
        html += '<tr><th>Refund Mode</th><td>' + (row.refund_mode ? row.refund_mode : '—') + '</td>';
        html += '<th>Refund Ref. No.</th><td>' + (row.refund_reference_no || 'Not yet assigned') + '</td></tr>';
        var rs = row.refund_status || 'pending';
        var lbl = rs === 'processed' ? 'success' : (rs === 'rejected' ? 'danger' : (rs === 'voided' ? 'default' : 'warning'));
        html += '<tr><th>Refund Status</th><td><span class="label label-' + lbl + '">' + rs.charAt(0).toUpperCase() + rs.slice(1) + '</span></td>';
        html += '<th>Processed By</th><td>' + (row.processed_by_name || '—') + '</td></tr>';
        html += '<tr><th>Cancelled By</th><td>' + (row.cancelled_by_name || '—') + '</td>';
        html += '<th>Cancelled On</th><td>' + (row.cancelled_at || '—') + '</td></tr>';
        html += '<tr><th>Cancellation Reason</th><td colspan="3">' + (row.cancellation_reason || '—') + '</td></tr>';
        if (row.remarks) {
            html += '<tr><th>Remarks</th><td colspan="3">' + row.remarks + '</td></tr>';
        }
        html += '</table>';
        $('#revocationDetailBody').html(html);
        $('#revocationDetailModal').modal('show');
    };

}(jQuery));
</script>

</div><!-- /.content-wrapper -->
