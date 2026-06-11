<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Batches <small>Define lab/practical split groups per class-section</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Batches</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-4">
    <div class="box box-primary">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-plus"></i> Add Batch</h3></div>
      <div class="box-body">
        <form id="batch-form">
          <input type="hidden" id="batch_id" name="id" value="0">
          <div class="form-group">
            <label>Department</label>
            <select class="form-control" id="batch_dept">
              <option value="">-- All --</option>
              <?php foreach ($departments as $dept): ?>
              <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Class <span class="text-danger">*</span></label>
            <select class="form-control" name="class_id" id="batch_class" required>
              <option value="">-- Select Class --</option>
              <?php foreach ($classlist as $cls): ?>
              <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Section <span class="text-danger">*</span></label>
            <select class="form-control" name="section_id" id="batch_section" required>
              <option value="">-- Select Section --</option>
            </select>
          </div>
          <div class="form-group">
            <label>Batch Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="batch_name" id="batch_name" placeholder="A, B or C" maxlength="5" required>
            <small class="text-muted">Use A, B, C etc. Will be converted to uppercase.</small>
          </div>
          <div class="form-group">
            <label>Student Count</label>
            <input type="number" class="form-control" name="student_count" id="batch_count" value="0" min="0">
          </div>
          <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save Batch</button>
          <button type="button" class="btn btn-default btn-block" id="btn-reset-batch">Reset</button>
        </form>
      </div>
    </div>
    <div class="box box-info callout callout-info">
      <div class="box-body" style="font-size:12px;">
        <strong><i class="fa fa-info-circle"></i> When to use batches?</strong><br>
        Add batches when a lab/practical class is split into groups. E.g., CSE III-A has 60 students → Batch A (30) and Batch B (30) attend the lab in alternating slots.
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="box box-default">
      <div class="box-header"><h3 class="box-title"><i class="fa fa-users"></i> Batch List</h3></div>
      <div class="box-body table-responsive p-0">
        <table class="table table-hover table-bordered table-striped">
          <thead>
            <tr style="background:#3c8dbc;color:#fff;">
              <th>#</th>
              <th>Class</th>
              <th>Section</th>
              <th>Batch</th>
              <th>Students</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($batches as $i => $b): ?>
            <tr>
              <td><?php echo $i+1; ?></td>
              <td><?php echo htmlspecialchars($b->class); ?></td>
              <td><?php echo htmlspecialchars($b->section); ?></td>
              <td><span class="label label-info" style="font-size:14px;">Batch <?php echo htmlspecialchars($b->batch_name); ?></span></td>
              <td><?php echo $b->student_count; ?></td>
              <td>
                <button class="btn btn-xs btn-info btn-edit-batch"
                  data-id="<?php echo $b->id; ?>"
                  data-class="<?php echo $b->class_id; ?>"
                  data-section="<?php echo $b->section_id; ?>"
                  data-name="<?php echo htmlspecialchars($b->batch_name); ?>"
                  data-count="<?php echo $b->student_count; ?>">
                  <i class="fa fa-edit"></i>
                </button>
                <a href="<?php echo site_url('admin/tt/delete_batch/'.$b->id); ?>"
                   class="btn btn-xs btn-danger btn-delete" data-confirm="Delete this batch?">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($batches)): ?>
        <div class="text-center text-muted p-4"><i class="fa fa-users fa-2x"></i><br>No batches defined yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</section>

<script>
$(function(){
  $('#batch_class').on('change', function(){
    var class_id = $(this).val();
    $('#batch_section').html('<option value="">Loading...</option>');
    if (!class_id) return;
    $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>', {class_id: class_id, <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'}, function(res){
      var opts = '<option value="">-- Select Section --</option>';
      $.each(res, function(i, s){ opts += '<option value="'+s.section_id+'">'+s.section+'</option>'; });
      $('#batch_section').html(opts);
    },'json');
  });

  $(document).on('click','.btn-edit-batch', function(){
    var d = $(this).data();
    $('#batch_id').val(d.id);
    $('#batch_class').val(d.class).trigger('change');
    setTimeout(function(){ $('#batch_section').val(d.section); }, 600);
    $('#batch_name').val(d.name);
    $('#batch_count').val(d.count);
    $('html,body').animate({scrollTop:0},400);
  });

  $('#btn-reset-batch').on('click', function(){
    $('#batch-form')[0].reset(); $('#batch_id').val(0);
  });

  $('#batch-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('[type=submit]').prop('disabled',true).text('Saving...');
    $.post('<?php echo site_url('admin/tt/save_batch'); ?>', $(this).serialize()+'&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>', function(res){
      if (res.status==='1') { location.reload(); }
      else { alert('Error saving.'); $btn.prop('disabled',false).text('Save Batch'); }
    },'json');
  });

  $(document).on('click','.btn-delete', function(e){
    if (!confirm($(this).data('confirm')||'Are you sure?')) e.preventDefault();
  });
});
</script>
