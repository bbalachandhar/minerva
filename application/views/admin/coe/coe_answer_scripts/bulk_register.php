<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-list-ol"></i> Register Scripts by Subject
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> &mdash; <?php echo htmlspecialchars($event->exam); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/listing/' . $batch_exam_id); ?>">
                    <i class="fa fa-arrow-left"></i> Back to Listing
                </a>
            </li>
        </ol>
    </section>
    <section class="content">
        <div id="bulk-msg"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Step 1: Select Subject</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($subjects)): ?>
                            <p class="text-muted text-center">No subjects configured for this exam event.</p>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subject <span class="text-danger">*</span></label>
                                    <select id="bulk_subject_id" class="form-control">
                                        <option value="">— Select Subject —</option>
                                        <?php foreach ($subjects as $sub): ?>
                                        <option value="<?php echo $sub->id; ?>"
                                                data-date="<?php echo $sub->exam_date; ?>"
                                                data-slot="<?php echo $sub->session_slot; ?>">
                                            <?php echo htmlspecialchars($sub->subject_code . ' — ' . $sub->subject_name); ?>
                                            <?php if ($sub->exam_date): ?>
                                                (<?php echo date('d M Y', strtotime($sub->exam_date)); ?> / <?php echo $sub->session_slot; ?>)
                                            <?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Exam Date</label>
                                    <input type="date" id="bulk_exam_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Session</label>
                                    <select id="bulk_session_slot" class="form-control">
                                        <option value="FN">FN (Forenoon)</option>
                                        <option value="AN">AN (Afternoon)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button id="btn-load-tickets" class="btn btn-default btn-block" disabled>
                                        <i class="fa fa-refresh"></i> Load
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="tickets-section" style="display:none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-users"></i> Step 2: Select Students</h3>
                            <div class="box-tools pull-right">
                                <button type="button" id="btn-select-all" class="btn btn-xs btn-default">
                                    <i class="fa fa-check-square-o"></i> Select All
                                </button>
                                <button type="button" id="btn-deselect-all" class="btn btn-xs btn-default">
                                    <i class="fa fa-square-o"></i> Deselect All
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <p class="text-muted" id="already-note" style="display:none;">
                                <i class="fa fa-info-circle text-blue"></i>
                                Students already registered for this subject are not shown here.
                            </p>
                            <table class="table table-bordered table-condensed table-striped">
                                <thead>
                                    <tr>
                                        <th style="width:40px;"><input type="checkbox" id="chk-all"></th>
                                        <th>#</th>
                                        <th>Hall Ticket No.</th>
                                        <th>Student Name</th>
                                    </tr>
                                </thead>
                                <tbody id="tickets-tbody">
                                </tbody>
                            </table>
                            <p id="no-tickets-msg" class="text-muted text-center" style="display:none;">
                                All students for this subject have already been registered.
                            </p>
                        </div>
                        <div class="box-footer">
                            <button id="btn-bulk-save" class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> Register Selected Scripts
                            </button>
                            <span class="text-muted" style="margin-left:10px;">
                                Each selected student will get a unique barcode token automatically.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'answer_scripts']); ?>

<script>
(function() {
    var batchExamId = <?php echo (int) $batch_exam_id; ?>;
    var baseUrl     = '<?php echo site_url(); ?>';

    // Subject selection → auto-fill exam_date and session_slot
    $('#bulk_subject_id').on('change', function() {
        var $opt = $(this).find('option:selected');
        var dt   = $opt.data('date');
        var slot = $opt.data('slot');

        if (dt)   $('#bulk_exam_date').val(dt);
        if (slot) $('#bulk_session_slot').val(slot);

        $('#btn-load-tickets').prop('disabled', !$(this).val());
        $('#tickets-section').hide();
        $('#tickets-tbody').html('');
    });

    // Load unregistered hall tickets via AJAX
    $('#btn-load-tickets').on('click', function() {
        var subjectId = $('#bulk_subject_id').val();
        if (!subjectId) return;

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: baseUrl + 'coe/coe_answer_scripts/get_unregistered_tickets/' + batchExamId,
            data: { subject_id: subjectId },
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Load');
                if (res.status !== 'success') {
                    showMsg('danger', res.msg);
                    return;
                }

                var tbody = $('#tickets-tbody').empty();
                if (!res.tickets || res.tickets.length === 0) {
                    $('#no-tickets-msg').show();
                    $('#already-note').show();
                    $('#tickets-section').show();
                    return;
                }

                $('#no-tickets-msg').hide();
                $('#already-note').show();
                $.each(res.tickets, function(i, t) {
                    tbody.append(
                        '<tr>' +
                        '<td><input type="checkbox" class="chk-ticket" value="' + t.id + '" checked></td>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td><strong>' + htmlEsc(t.hall_ticket_no) + '</strong></td>' +
                        '<td>' + htmlEsc(t.student_name) + '</td>' +
                        '</tr>'
                    );
                });
                $('#tickets-section').show();
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Load');
                showMsg('danger', 'Server error loading hall tickets.');
            }
        });
    });

    // Select all / deselect all
    $('#chk-all').on('change', function() {
        $('.chk-ticket').prop('checked', this.checked);
    });
    $('#btn-select-all').on('click', function() {
        $('.chk-ticket, #chk-all').prop('checked', true);
    });
    $('#btn-deselect-all').on('click', function() {
        $('.chk-ticket, #chk-all').prop('checked', false);
    });

    // Submit bulk registration
    $('#btn-bulk-save').on('click', function() {
        var subjectId   = $('#bulk_subject_id').val();
        var examDate    = $('#bulk_exam_date').val();
        var sessionSlot = $('#bulk_session_slot').val();
        var ticketIds   = [];

        $('.chk-ticket:checked').each(function() {
            ticketIds.push($(this).val());
        });

        if (!subjectId) { showMsg('warning', 'Please select a subject.'); return; }
        if (ticketIds.length === 0) { showMsg('warning', 'Please select at least one student.'); return; }

        var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        var data = {
            batch_exam_id: batchExamId,
            subject_id:    subjectId,
            exam_date:     examDate,
            session_slot:  sessionSlot,
            'ticket_ids[]': ticketIds
        };
        // Build proper array POST
        var postData = 'batch_exam_id=' + encodeURIComponent(batchExamId)
            + '&subject_id=' + encodeURIComponent(subjectId)
            + '&exam_date=' + encodeURIComponent(examDate)
            + '&session_slot=' + encodeURIComponent(sessionSlot);
        $.each(ticketIds, function(i, v) {
            postData += '&ticket_ids[]=' + encodeURIComponent(v);
        });
        // CSRF
        postData += '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>';

        $.ajax({
            url: baseUrl + 'coe/coe_answer_scripts/bulk_save',
            method: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            data: postData,
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Register Selected Scripts');
                if (res.status === 'success') {
                    showMsg('success', '<i class="fa fa-check"></i> ' + res.msg +
                        ' <a href="' + baseUrl + 'coe/coe_answer_scripts/listing/' + batchExamId + '" class="btn btn-xs btn-default" style="margin-left:8px;"><i class="fa fa-list"></i> View Listing</a>');
                    // Reload ticket table to reflect changes
                    $('#btn-load-tickets').trigger('click');
                } else {
                    showMsg(res.status === 'warning' ? 'warning' : 'danger', res.msg);
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Register Selected Scripts');
                showMsg('danger', 'Server error. Please try again.');
            }
        });
    });

    function showMsg(type, html) {
        $('#bulk-msg').html('<div class="alert alert-' + type + ' alert-dismissible">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' + html + '</div>');
        $('html,body').animate({ scrollTop: 0 }, 300);
    }

    function htmlEsc(s) {
        return $('<div>').text(s || '').html();
    }
})();
</script>
