<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-warning"></i> Report UFM Incident<button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_ufm/listing/' . $batch_exam_id); ?>"><i class="fa fa-arrow-left"></i> Back to Incidents</a></li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="box box-danger">
                    <div class="box-header with-border" style="display:flex;justify-content:space-between;align-items:center;">
                        <h3 class="box-title">
                            <i class="fa fa-exclamation-triangle"></i>
                            <?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?>
                        </h3>
                        <a href="<?php echo site_url('coe/coe_ufm/listing/' . $batch_exam_id); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Incidents
                        </a>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('coe/coe_ufm/save/' . $batch_exam_id); ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hall Ticket No <span class="text-red">*</span></label>
                                        <select name="hall_ticket_no" id="hall-ticket-select" class="form-control" required style="width:100%;">
                                            <option value="">— Search by ticket no. or student name —</option>
                                            <?php foreach ($hall_tickets as $ht): ?>
                                            <option value="<?php echo htmlspecialchars($ht->hall_ticket_no); ?>">
                                                <?php echo htmlspecialchars($ht->hall_ticket_no); ?><?php if ($ht->student_name): ?> — <?php echo htmlspecialchars($ht->student_name); ?><?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (empty($hall_tickets)): ?>
                                        <p class="help-block text-warning"><i class="fa fa-exclamation-triangle"></i> No hall tickets found. Generate hall tickets for this exam first.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Exam Room <span class="text-red">*</span></label>
                                        <select name="seating_room_id" class="form-control" required>
                                            <option value="">— Select Room —</option>
                                            <?php foreach ($rooms as $room): ?>
                                                <option value="<?php echo $room->id; ?>">
                                                    <?php echo htmlspecialchars($room->hall_name); ?> —
                                                    <?php echo date('d M Y', strtotime($room->exam_date)); ?>
                                                    (<?php echo $room->session_slot; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Exam Date <span class="text-red">*</span></label>
                                        <input type="date" name="exam_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Session <span class="text-red">*</span></label>
                                        <select name="session_slot" class="form-control" required>
                                            <option value="FN">FN (Forenoon)</option>
                                            <option value="AN">AN (Afternoon)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Incident Type <span class="text-red">*</span></label>
                                        <select name="incident_type" class="form-control" required>
                                            <option value="">— Select Type —</option>
                                            <option value="copying">Copying</option>
                                            <option value="mobile_phone">Mobile Phone</option>
                                            <option value="impersonation">Impersonation</option>
                                            <option value="unfair_material">Unfair Material</option>
                                            <option value="communication">Communication</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Describe what was observed..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Material Seized</label>
                                        <textarea name="material_seized" class="form-control" rows="3" placeholder="List any materials seized..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Witness Staff <small class="text-muted">(optional)</small></label>
                                        <select name="witness_staff_id" id="witness-staff-select" class="form-control" style="width:100%;">
                                            <option value="">— None / Not applicable —</option>
                                            <?php foreach ($staff_list as $sf): ?>
                                            <option value="<?php echo $sf->id; ?>">
                                                <?php echo htmlspecialchars($sf->employee_id); ?> — <?php echo htmlspecialchars($sf->name . ' ' . $sf->surname); ?><?php if ($sf->designation_name): ?> (<?php echo htmlspecialchars($sf->designation_name); ?>)<?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="callout callout-warning">
                                <p><i class="fa fa-info-circle"></i> <strong>Note:</strong> This report will be logged permanently with your ID and timestamp. Please ensure the details are accurate.</p>
                            </div>

                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Submit this UFM incident report?')">
                                <i class="fa fa-exclamation-triangle"></i> Submit Incident Report
                            </button>
                            <a href="<?php echo site_url('coe/coe_ufm/listing/' . $batch_exam_id); ?>" class="btn btn-default">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'ufm']); ?>

<link rel="stylesheet" href="<?php echo base_url('backend/plugins/select2/select2.min.css'); ?>">
<script src="<?php echo base_url('backend/plugins/select2/select2.full.min.js'); ?>"></script>
<script>
$(function () {
    $('#hall-ticket-select').select2({
        placeholder: 'Search by ticket no. or student name',
        allowClear: true,
        width: '100%'
    });
    $('#witness-staff-select').select2({
        placeholder: 'Search by employee ID or name',
        allowClear: true,
        width: '100%'
    });
});
</script>
