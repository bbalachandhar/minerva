<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="content-wrapper">
<section class="content-header">
    <h1><?php echo $this->lang->line('complaint_box'); ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('user/home'); ?>"><i class="fa fa-home"></i> <?php echo $this->lang->line('home'); ?></a></li>
        <li class="active"><?php echo $this->lang->line('complaint_box'); ?></li>
    </ol>
</section>

<?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
<div id="ajax-msg"></div>

<section class="content">

    <!-- Status Summary Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fa fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('complaint_status_open'); ?></span>
                    <span class="info-box-number"><?php echo $status_counts['open_count']; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-spinner"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('complaint_status_in_progress'); ?></span>
                    <span class="info-box-number"><?php echo $status_counts['in_progress_count']; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('complaint_status_resolved'); ?></span>
                    <span class="info-box-number"><?php echo $status_counts['resolved_count']; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-list-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $this->lang->line('total'); ?></span>
                    <span class="info-box-number"><?php echo $status_counts['total_count']; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Submit Complaint Form -->
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-plus"></i> <?php echo $this->lang->line('submit_complaint'); ?></h3>
                </div>
                <form id="submit-complaint-form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <!-- Submitter info (read-only) -->
                        <div class="callout callout-info" style="padding:8px 12px;margin-bottom:12px">
                            <strong><i class="fa fa-user-circle"></i> <?php echo $this->lang->line('submitted_by'); ?></strong><br>
                            <?php echo htmlspecialchars($submitter_name); ?>
                            <span class="label label-info" style="margin-left:4px"><?php echo htmlspecialchars($submitter_role); ?></span>
                            <?php if (!empty($submitter_id_label)): ?>
                            <br><small class="text-muted"><?php echo $this->lang->line('admission_no'); ?>: <?php echo htmlspecialchars($submitter_id_label); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('phone'); ?></label>
                            <input type="text" class="form-control" name="contact" id="contact" value="<?php echo htmlspecialchars($submitter_phone ?? ''); ?>" placeholder="10-digit mobile number" maxlength="10" pattern="[0-9]{10}">
                            <?php if (!empty($submitter_phone)): ?>
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Pre-filled from your profile &mdash; edit if needed.</small>
                            <?php else: ?>
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Enter your contact number.</small>
                            <?php endif; ?>
                            <span class="text-danger small" id="err_contact"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('complaint_type'); ?> <span class="text-danger">*</span></label>
                            <select name="complaint_type" id="complaint_type" class="form-control">
                                <option value="">-- <?php echo $this->lang->line('select'); ?> --</option>
                                <?php foreach ($complaint_types as $ct): ?>
                                    <option value="<?php echo htmlspecialchars($ct['complaint_type']); ?>"><?php echo htmlspecialchars($ct['complaint_type']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-danger small" id="err_complaint_type"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('priority'); ?> <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="low"><?php echo $this->lang->line('complaint_priority_low'); ?></option>
                                <option value="medium" selected><?php echo $this->lang->line('complaint_priority_medium'); ?></option>
                                <option value="high"><?php echo $this->lang->line('complaint_priority_high'); ?></option>
                                <option value="critical"><?php echo $this->lang->line('complaint_priority_critical'); ?></option>
                            </select>
                            <span class="text-danger small" id="err_priority"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('date'); ?></label>
                            <input type="text" class="form-control" value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly />
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('description'); ?> <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="5" maxlength="2000"></textarea>
                            <span class="text-danger small" id="err_description"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('attach_document'); ?></label>
                            <div class="input-group">
                                <label class="input-group-btn" style="width:auto">
                                    <span class="btn btn-default">
                                        <i class="fa fa-folder-open"></i> Browse&hellip;
                                        <input type="file" name="attachment" id="attachment" style="display:none">
                                    </span>
                                </label>
                                <input type="text" class="form-control" id="attachment-name" placeholder="No file chosen" readonly style="cursor:pointer">
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-block" id="submit-btn">
                            <i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('submit_complaint'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- My Complaints List -->
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('my_complaints'); ?></h3>
                </div>
                <div class="box-body">
                    <!-- Filter Bar -->
                    <div class="row" style="margin-bottom:10px">
                        <div class="col-md-3">
                            <select id="filter-status" class="form-control">
                                <option value=""><?php echo $this->lang->line('all_statuses'); ?></option>
                                <option value="open"><?php echo $this->lang->line('complaint_status_open'); ?></option>
                                <option value="in_progress"><?php echo $this->lang->line('complaint_status_in_progress'); ?></option>
                                <option value="resolved"><?php echo $this->lang->line('complaint_status_resolved'); ?></option>
                                <option value="closed"><?php echo $this->lang->line('complaint_status_closed'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filter-priority" class="form-control">
                                <option value=""><?php echo $this->lang->line('all_priorities'); ?></option>
                                <option value="low"><?php echo $this->lang->line('complaint_priority_low'); ?></option>
                                <option value="medium"><?php echo $this->lang->line('complaint_priority_medium'); ?></option>
                                <option value="high"><?php echo $this->lang->line('complaint_priority_high'); ?></option>
                                <option value="critical"><?php echo $this->lang->line('complaint_priority_critical'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button id="filter-btn" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                            <button id="clear-btn" class="btn btn-default"><i class="fa fa-times"></i> <?php echo $this->lang->line('clear'); ?></button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped example">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('ticket_no'); ?></th>
                                    <th><?php echo $this->lang->line('complaint_type'); ?></th>
                                    <th><?php echo $this->lang->line('priority'); ?></th>
                                    <th><?php echo $this->lang->line('status'); ?></th>
                                    <th><?php echo $this->lang->line('date'); ?></th>
                                    <th><?php echo $this->lang->line('submitted_by'); ?></th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $pr_class = ['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'danger'];
                                $st_class = ['open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'default'];
                                foreach ($complaints as $c):
                                    $pc = $pr_class[$c['priority']] ?? 'default';
                                    $sc = $st_class[$c['status']] ?? 'default';
                                    $display_date = ($c['date'] && $c['date'] != '0000-00-00')
                                        ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($c['date']))
                                        : date($this->customlib->getSchoolDateFormat(), strtotime($c['created_at']));
                                ?>
                                <tr>
                                    <td><span class="label label-default"><?php echo htmlspecialchars($c['ticket_no'] ?: '#'.$c['id']); ?></span></td>
                                    <td><?php echo htmlspecialchars($c['complaint_type']); ?></td>
                                    <td data-priority="<?php echo $c['priority']; ?>"><span class="label label-<?php echo $pc; ?>"><?php echo ucfirst($c['priority']); ?></span></td>
                                    <td data-status="<?php echo $c['status']; ?>"><span class="label label-<?php echo $sc; ?>"><?php echo ucwords(str_replace('_',' ',$c['status'])); ?></span></td>
                                    <td><?php echo $display_date; ?></td>
                                    <td><span class="label label-<?php echo $c['submitted_by'] === 'parent' ? 'info' : 'default'; ?>"><?php echo ucfirst($c['submitted_by'] ?? ''); ?></span></td>
                                    <td>
                                        <button class="btn btn-xs btn-info view-detail-btn" data-id="<?php echo $c['id']; ?>">
                                            <i class="fa fa-eye"></i> <?php echo $this->lang->line('view'); ?>
                                        </button>
                                        <?php if (!empty($c['image'])): ?>
                                        <a href="<?php echo base_url('uploads/front_office/complaints/'.urlencode($c['image'])); ?>" target="_blank" class="btn btn-xs btn-default" title="<?php echo $this->lang->line('download'); ?>">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($c['status'] === 'open' && empty($c['action_taken'])): ?>
                                        <button class="btn btn-xs btn-warning edit-complaint-btn"
                                            data-id="<?php echo $c['id']; ?>"
                                            data-type="<?php echo htmlspecialchars($c['complaint_type'], ENT_QUOTES); ?>"
                                            data-priority="<?php echo htmlspecialchars($c['priority'], ENT_QUOTES); ?>"
                                            data-description="<?php echo htmlspecialchars($c['description'], ENT_QUOTES); ?>"
                                            data-contact="<?php echo htmlspecialchars($c['contact'], ENT_QUOTES); ?>"
                                            data-image="<?php echo htmlspecialchars($c['image'] ?? '', ENT_QUOTES); ?>">
                                            <i class="fa fa-pencil"></i> <?php echo $this->lang->line('edit'); ?>
                                        </button>
                                        <button class="btn btn-xs btn-danger delete-complaint-btn" data-id="<?php echo $c['id']; ?>">
                                            <i class="fa fa-trash"></i> <?php echo $this->lang->line('delete'); ?>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Complaint Detail Modal -->
<div class="modal fade" id="complaintdetails" tabindex="-1" role="dialog" aria-labelledby="complaintdetailsLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="complaintdetailsLabel"><?php echo $this->lang->line('complaint_details'); ?></h4>
            </div>
            <div class="modal-body">
                <div id="modal-spinner" class="text-center" style="display:none;padding:30px">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                </div>
                <div id="complaintdetails-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Complaint Modal -->
<div class="modal fade" id="editcomplaintmodal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php echo $this->lang->line('edit'); ?> <?php echo $this->lang->line('complaint'); ?></h4>
            </div>
            <form id="edit-complaint-form" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit-complaint-id">
                    <div class="form-group">
                        <label><?php echo $this->lang->line('complaint_type'); ?> <span class="text-danger">*</span></label>
                        <select name="complaint_type" id="edit-complaint-type" class="form-control">
                            <option value="">-- <?php echo $this->lang->line('select'); ?> --</option>
                            <?php foreach ($complaint_types as $ct): ?>
                                <option value="<?php echo htmlspecialchars($ct['complaint_type']); ?>"><?php echo htmlspecialchars($ct['complaint_type']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('priority'); ?> <span class="text-danger">*</span></label>
                        <select name="priority" id="edit-priority" class="form-control">
                            <option value="low"><?php echo $this->lang->line('complaint_priority_low'); ?></option>
                            <option value="medium"><?php echo $this->lang->line('complaint_priority_medium'); ?></option>
                            <option value="high"><?php echo $this->lang->line('complaint_priority_high'); ?></option>
                            <option value="critical"><?php echo $this->lang->line('complaint_priority_critical'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('phone'); ?></label>
                        <input type="text" name="contact" id="edit-contact" class="form-control" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('description'); ?> <span class="text-danger">*</span></label>
                        <textarea name="description" id="edit-description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('attach_document'); ?></label>
                        <div id="edit-current-file" class="mb-1" style="margin-bottom:6px"></div>
                        <input type="file" name="attachment" id="edit-attachment" class="form-control">
                        <small class="text-muted"><?php echo $this->lang->line('upload_new_to_replace'); ?></small>
                    </div>
                    <div id="edit-form-msg"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="edit-save-btn"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {

    // --- Client-side DataTable filter ---
    var table       = $('.example').DataTable();
    var filterSt    = '';
    var filterPr    = '';

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable !== $('.example')[0]) return true;
        var row   = table.row(dataIndex).node();
        var rowSt = $(row).find('td[data-status]').data('status')   || '';
        var rowPr = $(row).find('td[data-priority]').data('priority') || '';
        if (filterSt && rowSt !== filterSt) return false;
        if (filterPr && rowPr !== filterPr) return false;
        return true;
    });

    $('#filter-btn').on('click', function () {
        filterSt = $('#filter-status').val();
        filterPr = $('#filter-priority').val();
        table.draw();
    });

    $('#clear-btn').on('click', function () {
        filterSt = '';
        filterPr = '';
        $('#filter-status').val('');
        $('#filter-priority').val('');
        table.draw();
    });

    // --- Submit complaint via AJAX ---
    $('#submit-complaint-form').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('#submit-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo $this->lang->line('please_wait'); ?>');
        $('[id^="err_"]').text('');
        $.ajax({
            url: '<?php echo site_url("user/complaint_box/add"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                $('#submit-btn').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('submit_complaint'); ?>');
                if (res.status === 'success') {
                    $('#ajax-msg').html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + res.message + '</div>');
                    $('#submit-complaint-form')[0].reset();
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    if (res.error && typeof res.error === 'object') {
                        $.each(res.error, function (k, v) { if (v) $('#err_' + k).text(v); });
                    } else if (typeof res.error === 'string' && res.error) {
                        $('#ajax-msg').html('<div class="alert alert-danger">' + res.error + '</div>');
                    }
                }
            },
            error: function () {
                $('#submit-btn').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo $this->lang->line('submit_complaint'); ?>');
                $('#ajax-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            }
        });
    });

    // --- View complaint detail ---
    function getRecord(id) {
        $('#complaintdetails-content').html('');
        $('#modal-spinner').show();
        $('#complaintdetails').modal('show');
        $.get('<?php echo site_url("user/complaint_box/get_detail"); ?>/' + id, function (res) {
            $('#modal-spinner').hide();
            if (!res || res.status === 'fail') {
                $('#complaintdetails-content').html('<div class="alert alert-danger">Could not load complaint details.</div>');
                return;
            }
            var stClass = {open:'danger', in_progress:'warning', resolved:'success', closed:'default'};
            var prClass = {low:'success', medium:'warning', high:'danger', critical:'danger'};
            var esc     = function (s) { return $('<div>').text(s || '').html(); };
            var stLabel = (res.status || '').replace('_', ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            var prLabel = (res.priority || '').charAt(0).toUpperCase() + (res.priority || '').slice(1);
            var html    = '<table class="table table-bordered table-striped">';
            html += '<tr><th style="width:160px"><?php echo $this->lang->line("ticket_no"); ?></th><td><span class="label label-default">' + esc(res.ticket_no || '#' + res.id) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("submitted_by"); ?></th><td><span class="label label-' + (res.submitted_by === 'parent' ? 'info' : 'default') + '">' + esc((res.submitted_by || '').charAt(0).toUpperCase() + (res.submitted_by || '').slice(1)) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("complaint_type"); ?></th><td>' + esc(res.complaint_type) + '</td></tr>';
            html += '<tr><th><?php echo $this->lang->line("priority"); ?></th><td><span class="label label-' + (prClass[res.priority] || 'default') + '">' + esc(prLabel) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("status"); ?></th><td><span class="label label-' + (stClass[res.status] || 'default') + '">' + esc(stLabel) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("date"); ?></th><td>' + esc(res.date) + '</td></tr>';
            html += '<tr><th><?php echo $this->lang->line("description"); ?></th><td style="white-space:pre-wrap">' + esc(res.description) + '</td></tr>';
            if (res.image) {
                html += '<tr><th><?php echo $this->lang->line("attach_document"); ?></th><td><a href="<?php echo base_url(); ?>uploads/front_office/complaints/' + esc(res.image) + '" target="_blank" class="btn btn-xs btn-primary"><i class="fa fa-download"></i> <?php echo $this->lang->line("download"); ?></a></td></tr>';
            }
            if (res.admin_response) {
                html += '<tr><th><?php echo $this->lang->line("admin_response"); ?></th><td class="bg-success" style="white-space:pre-wrap">' + esc(res.admin_response) + '</td></tr>';
            }
            html += '</table>';
            $('#complaintdetails-content').html(html);
        }, 'json').fail(function () {
            $('#modal-spinner').hide();
            $('#complaintdetails-content').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
        });
    }

    $(document).on('click', '.view-detail-btn', function () {
        getRecord($(this).data('id'));
    });

    // Custom file input — show chosen filename
    $('#attachment').on('change', function () {
        var name = this.files && this.files.length > 0 ? this.files[0].name : 'No file chosen';
        $('#attachment-name').val(name);
    });
    $('#attachment-name').on('click', function () {
        $('#attachment').trigger('click');
    });

    // --- Delete complaint ---
    $(document).on('click', '.delete-complaint-btn', function () {
        var id = $(this).data('id');
        if (!confirm('<?php echo $this->lang->line('are_you_sure_you_want_to_delete_this'); ?>')) return;
        $.post('<?php echo site_url('user/complaint_box/delete_complaint'); ?>/' + id, {}, function (res) {
            if (res.status === 'success') {
                location.reload();
            } else {
                alert(res.message || 'Delete failed.');
            }
        }, 'json').fail(function () {
            alert('An error occurred. Please try again.');
        });
    });

    // --- Edit complaint: populate modal ---
    $(document).on('click', '.edit-complaint-btn', function () {
        var btn = $(this);
        $('#edit-complaint-id').val(btn.data('id'));
        $('#edit-complaint-type').val(btn.data('type'));
        $('#edit-priority').val(btn.data('priority'));
        $('#edit-description').val(btn.data('description'));
        $('#edit-contact').val(btn.data('contact'));
        var img = btn.data('image');
        if (img) {
            $('#edit-current-file').html('<small><?php echo $this->lang->line('current_file'); ?>: <a href="<?php echo base_url(); ?>uploads/front_office/complaints/' + $('<div>').text(img).html() + '" target="_blank"><i class="fa fa-file"></i> ' + $('<div>').text(img).html() + '</a></small>');
        } else {
            $('#edit-current-file').html('');
        }
        $('#edit-form-msg').html('');
        $('#edit-attachment').val('');
        $('#editcomplaintmodal').modal('show');
    });

    // --- Edit complaint: save ---
    $('#edit-complaint-form').on('submit', function (e) {
        e.preventDefault();
        var id       = $('#edit-complaint-id').val();
        var formData = new FormData(this);
        $('#edit-save-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        $.ajax({
            url: '<?php echo site_url('user/complaint_box/update'); ?>/' + id,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                $('#edit-save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?>');
                if (res.status === 'success') {
                    $('#editcomplaintmodal').modal('hide');
                    location.reload();
                } else {
                    $('#edit-form-msg').html('<div class="alert alert-danger">' + (res.message || 'Error saving.') + '</div>');
                }
            },
            error: function () {
                $('#edit-save-btn').prop('disabled', false).html('<i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?>');
                $('#edit-form-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            }
        });
    });
});
</script>
</div><!-- /.content-wrapper -->
