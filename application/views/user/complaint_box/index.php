<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
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
    <div class="row">
        <!-- Submit Complaint Form -->
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-plus"></i> <?php echo $this->lang->line('submit_complaint'); ?></h3>
                </div>
                <form id="submit-complaint-form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label><?php echo $this->lang->line('complaint_type'); ?> <span class="text-danger">*</span></label>
                            <select name="complaint_type" id="complaint_type" class="form-control">
                                <option value="">-- <?php echo $this->lang->line('select'); ?> --</option>
                                <?php foreach ($complaint_types as $ct): ?>
                                    <option value="<?php echo htmlspecialchars($ct['complaint_type']); ?>"><?php echo htmlspecialchars($ct['complaint_type']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-danger" id="err_complaint_type"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('priority'); ?> <span class="text-danger">*</span></label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="low"><?php echo $this->lang->line('complaint_priority_low'); ?></option>
                                <option value="medium" selected><?php echo $this->lang->line('complaint_priority_medium'); ?></option>
                                <option value="high"><?php echo $this->lang->line('complaint_priority_high'); ?></option>
                                <option value="critical"><?php echo $this->lang->line('complaint_priority_critical'); ?></option>
                            </select>
                            <span class="text-danger" id="err_priority"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('description'); ?> <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="4" maxlength="2000"></textarea>
                            <span class="text-danger" id="err_description"></span>
                        </div>
                        <div class="form-group">
                            <label><?php echo $this->lang->line('attach_document'); ?></label>
                            <input type="file" name="attachment" id="attachment" class="form-control" />
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
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('my_complaints'); ?></h3>
                </div>
                <div class="box-body table-responsive">
                    <?php if (empty($complaints)): ?>
                        <p class="text-center text-muted"><?php echo $this->lang->line('no_record_found'); ?></p>
                    <?php else: ?>
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('ticket_no'); ?></th>
                                <th><?php echo $this->lang->line('complaint_type'); ?></th>
                                <th><?php echo $this->lang->line('priority'); ?></th>
                                <th><?php echo $this->lang->line('status'); ?></th>
                                <th><?php echo $this->lang->line('date'); ?></th>
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
                            ?>
                            <tr>
                                <td><span class="label label-default"><?php echo $c['ticket_no'] ?: '#'.$c['id']; ?></span></td>
                                <td><?php echo htmlspecialchars($c['complaint_type']); ?></td>
                                <td><span class="label label-<?php echo $pc; ?>"><?php echo ucfirst($c['priority']); ?></span></td>
                                <td><span class="label label-<?php echo $sc; ?>"><?php echo ucwords(str_replace('_',' ',$c['status'])); ?></span></td>
                                <td><?php echo ($c['date'] && $c['date'] != '0000-00-00') ? date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($c['date'])) : date($this->customlib->getSchoolDateFormat(), strtotime($c['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-xs btn-info view-detail-btn" data-id="<?php echo $c['id']; ?>">
                                        <i class="fa fa-eye"></i> <?php echo $this->lang->line('view'); ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Detail Modal -->
<div class="modal fade" id="detail-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('complaint_details'); ?></h4>
            </div>
            <div class="modal-body" id="detail-modal-body"></div>
        </div>
    </div>
</div>

<script>
$(function () {
    $('#submit-complaint-form').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('#submit-btn').prop('disabled', true);
        $('[id^="err_"]').text('');
        $.ajax({
            url: '<?php echo site_url("user/complaint_box/add"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                $('#submit-btn').prop('disabled', false);
                if (res.status === 'success') {
                    $('#ajax-msg').html('<div class="alert alert-success">' + res.message + '</div>');
                    $('#submit-complaint-form')[0].reset();
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    if (res.error && typeof res.error === 'object') {
                        $.each(res.error, function (k, v) { if (v) $('#err_' + k).text(v); });
                    } else if (typeof res.error === 'string') {
                        $('#ajax-msg').html('<div class="alert alert-danger">' + res.error + '</div>');
                    }
                }
            },
            error: function () {
                $('#submit-btn').prop('disabled', false);
                $('#ajax-msg').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
            }
        });
    });

    $(document).on('click', '.view-detail-btn', function () {
        var id = $(this).data('id');
        $.get('<?php echo site_url("user/complaint_box/get_detail"); ?>/' + id, function (res) {
            if (!res || res.status === 'fail') {
                alert('Could not load complaint details.');
                return;
            }
            var stClass = {open:'danger', in_progress:'warning', resolved:'success', closed:'default'};
            var prClass = {low:'success', medium:'warning', high:'danger', critical:'danger'};
            var esc = function(s){ return $('<div>').text(s||'').html(); };
            var html = '<table class="table table-bordered">';
            html += '<tr><th style="width:160px"><?php echo $this->lang->line("ticket_no"); ?></th><td><span class="label label-default">' + esc(res.ticket_no || '#'+res.id) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("complaint_type"); ?></th><td>' + esc(res.complaint_type) + '</td></tr>';
            html += '<tr><th><?php echo $this->lang->line("priority"); ?></th><td><span class="label label-' + (prClass[res.priority]||'default') + '">' + (res.priority||'').charAt(0).toUpperCase() + (res.priority||'').slice(1) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("status"); ?></th><td><span class="label label-' + (stClass[res.status]||'default') + '">' + (res.status||'').replace('_',' ').replace(/\b\w/g,function(c){return c.toUpperCase();}) + '</span></td></tr>';
            html += '<tr><th><?php echo $this->lang->line("date"); ?></th><td>' + esc(res.date) + '</td></tr>';
            html += '<tr><th><?php echo $this->lang->line("description"); ?></th><td>' + esc(res.description) + '</td></tr>';
            if (res.image) {
                html += '<tr><th><?php echo $this->lang->line("attach_document"); ?></th><td><a href="<?php echo base_url(); ?>uploads/front_office/complaints/' + esc(res.image) + '" target="_blank"><?php echo $this->lang->line("download"); ?></a></td></tr>';
            }
            if (res.admin_response) {
                html += '<tr><th><?php echo $this->lang->line("admin_response"); ?></th><td class="bg-success" style="white-space:pre-wrap">' + esc(res.admin_response) + '</td></tr>';
            }
            html += '</table>';
            $('#detail-modal-body').html(html);
            $('#detail-modal').modal('show');
        }, 'json');
    });
});
</script>
