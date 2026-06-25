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
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-users"></i> Batch List</h3>
        <div class="box-tools">
          <button class="btn btn-sm btn-primary" id="btn-add-batch"><i class="fa fa-plus"></i> Add Batch</button>
        </div>
      </div>
      <div class="box-body">
        <div class="callout callout-info" style="font-size:12px;">
          <strong><i class="fa fa-info-circle"></i> When to use batches?</strong><br>
          Add batches when a lab/practical class is split into groups. E.g., CSE III-A has 60 students &rarr; Batch A (30) and Batch B (30) attend the lab in alternating slots.
        </div>
        <div class="table-responsive">
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
</div>
</section>

<!-- Batch Modal -->
<div class="modal fade" id="batchModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-users"></i> <span id="batchModalLabel">Add Batch</span></h4>
      </div>
      <form id="batch-form">
        <div class="modal-body">
          <input type="hidden" id="batch_id" name="id" value="0">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Department</label>
                <select class="form-control" id="batch_dept">
                  <option value="">-- All --</option>
                  <?php foreach ($departments as $dept): ?>
                  <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Class <span class="text-danger">*</span></label>
                <select class="form-control" name="class_id" id="batch_class" required>
                  <option value="">-- Select Class --</option>
                  <?php foreach ($classlist as $cls): ?>
                  <option value="<?php echo $cls['id']; ?>" data-dept="<?php echo $cls['department_id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Section <span class="text-danger">*</span></label>
                <select class="form-control" name="section_id" id="batch_section" required>
                  <option value="">-- Select Section --</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Batch Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="batch_name" id="batch_name" placeholder="A, B or C" maxlength="5" required>
                <small class="text-muted">Use A, B, C etc. Will be converted to uppercase.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Student Count</label>
                <input type="number" class="form-control" name="student_count" id="batch_count" value="0" min="0">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default pull-left" id="btn-reset-batch" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Batch</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  var select2Initialized = false;

  // Store original class options for dept filtering
  var allClassOpts = [];
  $('#batch_class option').each(function(){
    if ($(this).val()) allClassOpts.push({val:$(this).val(), text:$(this).text(), dept:$(this).data('dept')});
  });

  // Init select2 on modal shown to fix z-index issues
  $('#batchModal').on('shown.bs.modal', function(){
    if (!select2Initialized) {
      $('#batch_dept').select2({ placeholder: '-- All --', allowClear: true, width: '100%', minimumResultsForSearch: 1, dropdownParent: $('#batchModal') });
      $('#batch_class').select2({ placeholder: '-- Select Class --', allowClear: true, width: '100%', minimumResultsForSearch: 1, dropdownParent: $('#batchModal') });
      $('#batch_section').select2({ placeholder: '-- Select Section --', allowClear: true, width: '100%', dropdownParent: $('#batchModal') });
      select2Initialized = true;
    }
  });

  // Add button
  $('#btn-add-batch').on('click', function(){
    $('#batch-form')[0].reset();
    $('#batch_id').val(0);
    if (select2Initialized) {
      $('#batch_dept').val('').trigger('change.select2');
      $('#batch_class').val('').trigger('change.select2');
      $('#batch_section').html('<option value="">-- Select Section --</option>').trigger('change.select2');
    }
    $('#batchModalLabel').text('Add Batch');
    $('#batchModal').modal('show');
  });

  // Dept filter for class options
  $('#batch_dept').on('change', function(){
    var dept = $(this).val();
    var opts = '<option value="">-- Select Class --</option>';
    $.each(allClassOpts, function(i,o){
      if (!dept || o.dept == dept) opts += '<option value="'+o.val+'" data-dept="'+o.dept+'">'+o.text+'</option>';
    });
    $('#batch_class').html(opts).trigger('change.select2');
    $('#batch_section').html('<option value="">-- Select Section --</option>').trigger('change.select2');
  });

  // Class -> Section AJAX
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

  // Edit
  $(document).on('click','.btn-edit-batch', function(){
    var d = $(this).data();
    $('#batch_id').val(d.id);
    $('#batch_name').val(d.name);
    $('#batch_count').val(d.count);
    $('#batchModalLabel').text('Edit Batch');
    $('#batchModal').modal('show');
    // Set select2 values after modal is shown so select2 is initialized
    $('#batchModal').one('shown.bs.modal', function(){
      $('#batch_class').val(d.class).trigger('change');
      setTimeout(function(){ $('#batch_section').val(d.section).trigger('change.select2'); }, 600);
    });
  });

  // Reset
  $('#btn-reset-batch').on('click', function(){
    $('#batch-form')[0].reset();
    $('#batch_id').val(0);
    if (select2Initialized) {
      $('#batch_dept').val('').trigger('change.select2');
      $('#batch_class').val('').trigger('change.select2');
      $('#batch_section').html('<option value="">-- Select Section --</option>').trigger('change.select2');
    }
  });

  // Save
  $('#batch-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('[type=submit]').prop('disabled',true).text('Saving...');
    $.post('<?php echo site_url('admin/tt/save_batch'); ?>', $(this).serialize()+'&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>', function(res){
      if (res.status === '1') {
        toastr.success('Batch saved successfully');
        $('#batchModal').modal('hide');
        location.reload();
      } else {
        swal({title:'Alert',text:'Error saving.',type:'warning'});
        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Batch');
      }
    },'json');
  });

  // Delete confirm
  $(document).on('click','.btn-delete', function(e){
    e.preventDefault(); var _href=$(this).attr('href'); var _msg=$(this).data('confirm')||'Are you sure?'; swal({title:'Confirm',text:_msg,type:'warning',showCancelButton:true,confirmButtonColor:'#dd4b39',confirmButtonText:'Yes'},function(ok){if(ok)window.location.href=_href;});
  });
});
</script>
</div>
