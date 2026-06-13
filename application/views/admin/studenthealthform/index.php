<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-heartbeat"></i> Student Health Forms</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Filter</h3>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('admin/studenthealthform'); ?>">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-sm-4 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select name="class_id" id="class_id" class="form-control">
                                            <option value="">-- All Classes --</option>
                                            <?php foreach ($classlist as $c): ?>
                                            <option value="<?php echo $c['id']; ?>" <?php if ($class_id == $c['id']) echo 'selected'; ?>><?php echo $c['class']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select name="section_id" id="section_id" class="form-control">
                                            <option value="">-- All Sections --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Search</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <?php if (!empty($students)): ?>
                        <?php
                            $submitted = array_filter($students, function($s){ return !empty($s['health_record_id']); });
                            $pending   = array_filter($students, function($s){ return  empty($s['health_record_id']); });
                        ?>
                        <div class="row" style="margin-bottom:12px">
                            <div class="col-sm-4"><div class="info-box bg-green" style="margin-bottom:0"><span class="info-box-icon"><i class="fa fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Submitted</span><span class="info-box-number"><?php echo count($submitted); ?></span></div></div></div>
                            <div class="col-sm-4"><div class="info-box bg-red" style="margin-bottom:0"><span class="info-box-icon"><i class="fa fa-clock-o"></i></span><div class="info-box-content"><span class="info-box-text">Pending</span><span class="info-box-number"><?php echo count($pending); ?></span></div></div></div>
                            <div class="col-sm-4"><div class="info-box bg-blue" style="margin-bottom:0"><span class="info-box-icon"><i class="fa fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total</span><span class="info-box-number"><?php echo count($students); ?></span></div></div></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Admission No</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Form Status</th>
                                        <th>Submitted On</th>
                                        <th class="noExport">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $i = 1; foreach ($students as $s): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars(trim($s['firstname'] . ' ' . $s['middlename'] . ' ' . $s['lastname'])); ?></td>
                                        <td><?php echo htmlspecialchars($s['admission_no']); ?></td>
                                        <td><?php echo htmlspecialchars($s['class'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($s['section'] ?? '-'); ?></td>
                                        <td>
                                            <?php if (!empty($s['health_record_id'])): ?>
                                                <span class="label label-success"><i class="fa fa-check"></i> Submitted</span>
                                            <?php else: ?>
                                                <span class="label label-danger"><i class="fa fa-clock-o"></i> Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo !empty($s['submitted_at']) ? date('d M Y', strtotime($s['submitted_at'])) : '-'; ?></td>
                                        <td>
                                            <a href="<?php echo site_url('admin/studenthealthform/view/' . $s['id']); ?>" class="btn btn-xs btn-info" data-toggle="tooltip" title="View"><i class="fa fa-eye"></i></a>
                                            <?php if ($this->rbac->hasPrivilege('student_health_form', 'can_edit')): ?>
                                            <a href="<?php echo site_url('admin/studenthealthform/edit/' . $s['id']); ?>" class="btn btn-xs btn-default" data-toggle="tooltip" title="Edit"><i class="fa fa-pencil"></i></a>
                                            <?php endif; ?>
                                            <?php if (!empty($s['form_token'])): ?>
                                            <a href="<?php echo site_url('studenthealthform/pdf/' . $s['form_token']); ?>" target="_blank" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Download PDF"><i class="fa fa-file-pdf-o"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php elseif ($this->input->post('class_id') !== null): ?>
                        <div class="alert alert-info">No students found for the selected filter.</div>
                        <?php else: ?>
                        <div class="alert alert-info">Select a class and click Search, or leave blank to load all students.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
$(document).ready(function(){
    var preClass = <?php echo json_encode($class_id ?: ''); ?>;
    var preSection = <?php echo json_encode($section_id ?: ''); ?>;
    if (preClass) loadSections(preClass, preSection);

    $('#class_id').on('change', function(){ loadSections($(this).val(), ''); });

    function loadSections(classId, selectedSection) {
        if (!classId) { $('#section_id').html('<option value="">-- All Sections --</option>'); return; }
        $.get('<?php echo site_url("common/getSection"); ?>', {class_id: classId}, function(data){
            var opts = '<option value="">-- All Sections --</option>';
            $.each(data, function(i,s){ opts += '<option value="'+s.id+'"'+(s.id==selectedSection?' selected':'')+'>'+s.section+'</option>'; });
            $('#section_id').html(opts);
        }, 'json');
    }
});
</script>
