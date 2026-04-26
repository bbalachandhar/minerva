<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-home"></i> Hostel Fee Override</h3>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('admin/hostel_fee_override'); ?>" class="row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Hostel Fee Group</label>
                                    <select name="fee_session_group_id" class="form-control select2" required>
                                        <option value="">-- Select Hostel Fee Group --</option>
                                        <?php foreach ($hostel_fee_groups as $hfg): ?>
                                            <option value="<?php echo $hfg->fee_session_group_id; ?>"
                                                <?php if ($selected_fsg_id == $hfg->fee_session_group_id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($hfg->fee_group_name); ?>
                                                (<?php echo $currency_symbol . amountFormat($hfg->base_amount); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Load Students
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!is_null($student_list)): ?>
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> Students — Hostel Fee Override
                        </h3>
                        <div class="box-tools pull-right">
                            <small class="text-muted">Override amount must be &gt;= amount already paid</small>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <?php if (empty($student_list)): ?>
                            <p class="text-danger text-center">No students assigned to this hostel fee group.</p>
                        <?php else: ?>
                        <table class="table table-striped table-bordered" id="overrideTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Adm No</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Base Fee (<?php echo $currency_symbol; ?>)</th>
                                    <th>Paid (<?php echo $currency_symbol; ?>)</th>
                                    <th>Current Demand (<?php echo $currency_symbol; ?>)</th>
                                    <th>Override Amount (<?php echo $currency_symbol; ?>)</th>
                                    <th>Note</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($student_list as $row): ?>
                                <tr id="row_<?php echo $row->student_session_id; ?>">
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row->admission_no); ?></td>
                                    <td><?php echo htmlspecialchars($row->firstname . ' ' . $row->lastname); ?></td>
                                    <td><?php echo htmlspecialchars($row->class . ' (' . $row->section . ')'); ?></td>
                                    <td class="text-right"><?php echo amountFormat($row->base_amount); ?></td>
                                    <td class="text-right <?php echo $row->paid_amount > 0 ? 'text-success' : ''; ?>">
                                        <?php echo amountFormat($row->paid_amount); ?>
                                    </td>
                                    <td class="text-right effective_amt_<?php echo $row->student_session_id; ?>">
                                        <?php if ($row->override_amount): ?>
                                            <strong class="text-primary"><?php echo amountFormat($row->effective_amount); ?></strong>
                                            <small class="text-muted">(overridden)</small>
                                        <?php else: ?>
                                            <?php echo amountFormat($row->effective_amount); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            class="form-control input-sm override-input"
                                            id="override_<?php echo $row->student_session_id; ?>"
                                            data-student-session-id="<?php echo $row->student_session_id; ?>"
                                            data-feetype-id="<?php echo $row->fee_groups_feetype_id; ?>"
                                            data-base-amount="<?php echo $row->base_amount; ?>"
                                            data-paid="<?php echo $row->paid_amount; ?>"
                                            value="<?php echo $row->override_amount ? $row->override_amount : ''; ?>"
                                            placeholder="<?php echo amountFormat($row->base_amount); ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control input-sm note-input"
                                            id="note_<?php echo $row->student_session_id; ?>"
                                            value="<?php echo htmlspecialchars($row->override_note ?? ''); ?>"
                                            placeholder="Optional reason">
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-primary btn-save-override"
                                            data-student-session-id="<?php echo $row->student_session_id; ?>"
                                            data-feetype-id="<?php echo $row->fee_groups_feetype_id; ?>"
                                            data-paid="<?php echo $row->paid_amount; ?>">
                                            <i class="fa fa-save"></i> Save
                                        </button>
                                        <?php if ($row->override_id && $row->paid_amount == 0): ?>
                                        <button class="btn btn-xs btn-danger btn-remove-override"
                                            data-student-session-id="<?php echo $row->student_session_id; ?>"
                                            data-feetype-id="<?php echo $row->fee_groups_feetype_id; ?>">
                                            <i class="fa fa-times"></i> Remove
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {
    var csrf_token_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrf_hash      = '<?php echo $this->security->get_csrf_hash(); ?>';

    // Save override
    $(document).on('click', '.btn-save-override', function () {
        var btn           = $(this);
        var ssid          = btn.data('student-session-id');
        var ftid          = btn.data('feetype-id');
        var paid          = parseFloat(btn.data('paid')) || 0;
        var overrideVal   = parseFloat($('#override_' + ssid).val());
        var note          = $('#note_' + ssid).val();

        if (isNaN(overrideVal) || overrideVal <= 0) {
            swal('Error', 'Please enter a valid override amount.', 'error');
            return;
        }
        if (overrideVal < paid) {
            swal('Not Allowed',
                'User already paid more than what you are trying to reduce from the actual hostel fee, so our system won\'t allow it.',
                'warning');
            return;
        }

        var postData = {};
        postData[csrf_token_name]        = csrf_hash;
        postData['student_session_id']   = ssid;
        postData['fee_groups_feetype_id']= ftid;
        postData['override_amount']      = overrideVal;
        postData['note']                 = note;

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post('<?php echo site_url("admin/hostel_fee_override/save"); ?>', postData, function (res) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            if (res.status === 'success') {
                swal('Saved', res.message, 'success');
                // Refresh effective demand display
                $('.effective_amt_' + ssid).html('<strong class="text-primary">' + overrideVal.toFixed(2) + '</strong> <small class="text-muted">(overridden)</small>');
                // Update CSRF
                csrf_hash = res.csrf_hash || csrf_hash;
            } else {
                swal('Error', res.message, 'error');
            }
        }, 'json').fail(function () {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            swal('Error', 'Server error. Please try again.', 'error');
        });
    });

    // Remove override
    $(document).on('click', '.btn-remove-override', function () {
        var btn  = $(this);
        var ssid = btn.data('student-session-id');
        var ftid = btn.data('feetype-id');

        swal({
            title: 'Remove Override?',
            text: 'This will restore the student\'s hostel fee to the default amount.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Remove',
        }, function (confirmed) {
            if (!confirmed) return;

            var postData = {};
            postData[csrf_token_name]        = csrf_hash;
            postData['student_session_id']   = ssid;
            postData['fee_groups_feetype_id']= ftid;

            $.post('<?php echo site_url("admin/hostel_fee_override/delete"); ?>', postData, function (res) {
                if (res.status === 'success') {
                    swal('Removed', res.message, 'success');
                    location.reload();
                } else {
                    swal('Error', res.message, 'error');
                }
            }, 'json');
        });
    });
});
</script>
