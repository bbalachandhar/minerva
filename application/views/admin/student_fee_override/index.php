<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <!-- Filter Box -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-pencil-square-o"></i> Student Fee Override</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/student_fee_override/bulk_import'); ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-upload"></i> Bulk Import
                            </a>
                            <a href="<?php echo site_url('admin/student_fee_override/exportformat'); ?>" class="btn btn-sm btn-default">
                                <i class="fa fa-download"></i> Sample CSV
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('admin/student_fee_override'); ?>" class="row" id="filterForm">
                            <?php echo $this->customlib->getCSRF(); ?>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Session</label>
                                    <select name="session_id" id="session_id" class="form-control select2" required>
                                        <?php foreach ($sessions as $sess): ?>
                                            <option value="<?php echo $sess->id; ?>"
                                                <?php if ($selected_session_id == $sess->id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($sess->session); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Class / Programme</label>
                                    <select name="class_id" id="class_id" class="form-control select2" required>
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classes as $cls): ?>
                                            <option value="<?php echo $cls->id; ?>"
                                                <?php if ($selected_class_id == $cls->id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($cls->class); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>Section (optional)</label>
                                    <select name="section_id" id="section_id" class="form-control select2">
                                        <option value="">-- All Sections --</option>
                                        <?php
                                        // Sections loaded via JS on session/class change; show static list as fallback
                                        $sections_data = $this->db->get('sections')->result();
                                        foreach ($sections_data as $sec):
                                        ?>
                                            <option value="<?php echo $sec->id; ?>"
                                                <?php if ($selected_section_id == $sec->id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($sec->section); ?>
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
                <!-- /Filter Box -->

                <?php if (!is_null($student_list)): ?>
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-users"></i> Student Fee Override List
                        </h3>
                        <div class="box-tools pull-right">
                            <small class="text-muted">Override amount must be &ge; amount already paid</small>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <?php if (empty($student_list)): ?>
                            <p class="text-danger text-center">No students with tuition/other fee assignment found for this selection.</p>
                        <?php else: ?>
                        <table class="table table-striped table-bordered table-hover" id="overrideTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Adm No</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Fee Type</th>
                                    <th>Fee Group</th>
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
                                <tr id="row_<?php echo $row->student_session_id . '_' . $row->fee_groups_feetype_id; ?>">
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row->admission_no); ?></td>
                                    <td>
                                        <?php
                                        $name = trim($row->firstname . ' ' . $row->middlename . ' ' . $row->lastname);
                                        echo htmlspecialchars($name);
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row->class . ' (' . $row->section . ')'); ?></td>
                                    <td>
                                        <span class="label label-info"><?php echo htmlspecialchars($row->feetype_code); ?></span>
                                        <small><?php echo htmlspecialchars($row->feetype_name); ?></small>
                                    </td>
                                    <td><small><?php echo htmlspecialchars($row->fee_group_name); ?></small></td>
                                    <td class="text-right"><?php echo amountFormat($row->base_amount); ?></td>
                                    <td class="text-right <?php echo $row->paid_amount > 0 ? 'text-success font-weight-bold' : ''; ?>">
                                        <?php echo amountFormat($row->paid_amount); ?>
                                    </td>
                                    <td class="text-right effective_amt_<?php echo $row->student_session_id . '_' . $row->fee_groups_feetype_id; ?>">
                                        <?php if ($row->override_amount): ?>
                                            <strong class="text-primary"><?php echo amountFormat($row->effective_amount); ?></strong>
                                            <br><small class="text-muted">(overridden)</small>
                                        <?php else: ?>
                                            <?php echo amountFormat($row->effective_amount); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            class="form-control input-sm override-input"
                                            id="override_<?php echo $row->student_session_id . '_' . $row->fee_groups_feetype_id; ?>"
                                            data-student-session-id="<?php echo $row->student_session_id; ?>"
                                            data-feetype-id="<?php echo $row->fee_groups_feetype_id; ?>"
                                            data-base-amount="<?php echo $row->base_amount; ?>"
                                            data-paid="<?php echo $row->paid_amount; ?>"
                                            value="<?php echo $row->override_amount ? htmlspecialchars($row->override_amount) : ''; ?>"
                                            placeholder="<?php echo amountFormat($row->base_amount); ?>"
                                            style="width:120px">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control input-sm note-input"
                                            id="note_<?php echo $row->student_session_id . '_' . $row->fee_groups_feetype_id; ?>"
                                            value="<?php echo htmlspecialchars($row->override_note ?? ''); ?>"
                                            placeholder="Optional reason"
                                            style="width:150px">
                                    </td>
                                    <td nowrap>
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
    var csrf_hash       = '<?php echo $this->security->get_csrf_hash(); ?>';

    // Reload classes when session changes
    $('#session_id').on('change', function () {
        $('#class_id').val('').trigger('change');
        $('#filterForm').submit();
    });

    // Save override
    $(document).on('click', '.btn-save-override', function () {
        var btn          = $(this);
        var ssid         = btn.data('student-session-id');
        var ftid         = btn.data('feetype-id');
        var paid         = parseFloat(btn.data('paid')) || 0;
        var rowKey       = ssid + '_' + ftid;
        var overrideVal  = parseFloat($('#override_' + rowKey).val());
        var note         = $('#note_' + rowKey).val();

        if (isNaN(overrideVal) || overrideVal <= 0) {
            swal('Error', 'Please enter a valid override amount greater than 0.', 'error');
            return;
        }
        if (overrideVal < paid) {
            swal('Not Allowed',
                'Student has already paid more than the amount you entered. The system cannot reduce the fee below what has already been collected.',
                'warning');
            return;
        }

        var postData = {};
        postData[csrf_token_name]         = csrf_hash;
        postData['student_session_id']    = ssid;
        postData['fee_groups_feetype_id'] = ftid;
        postData['override_amount']       = overrideVal;
        postData['note']                  = note;

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.post('<?php echo site_url("admin/student_fee_override/save"); ?>', postData, function (res) {
            btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            csrf_hash = res.csrf_hash || csrf_hash;
            if (res.status === 'success') {
                swal('Saved', res.message, 'success');
                $('.effective_amt_' + rowKey).html(
                    '<strong class="text-primary">' + overrideVal.toFixed(2) + '</strong>' +
                    '<br><small class="text-muted">(overridden)</small>'
                );
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
            text: 'This will restore the student\'s fee to the default group amount.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Remove',
        }, function (confirmed) {
            if (!confirmed) return;

            var postData = {};
            postData[csrf_token_name]         = csrf_hash;
            postData['student_session_id']    = ssid;
            postData['fee_groups_feetype_id'] = ftid;

            $.post('<?php echo site_url("admin/student_fee_override/delete"); ?>', postData, function (res) {
                csrf_hash = res.csrf_hash || csrf_hash;
                if (res.status === 'success') {
                    swal('Removed', res.message, 'success');
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    swal('Error', res.message, 'error');
                }
            }, 'json');
        });
    });
});
</script>
