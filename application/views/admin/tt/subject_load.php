<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Subject Load <small>Configure weekly periods per subject per class — the scheduling "cards"</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Subject Load</li>
    </ol>
</section>
<section class="content">
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-filter"></i> Select Class</h3>
  </div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-3">
        <label>Department</label>
        <select class="form-control" id="dept_filter">
          <option value="">-- All --</option>
          <?php foreach ($departments as $d): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Class <span class="text-danger">*</span></label>
        <select class="form-control" id="sl_class_id">
          <option value="">-- Select Class --</option>
          <?php foreach ($classlist as $cls): ?>
          <option value="<?php echo $cls['id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Section <span class="text-danger">*</span></label>
        <select class="form-control" id="sl_section_id">
          <option value="">-- Select Section --</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block" id="btn-load-subjects"><i class="fa fa-search"></i> Load Subjects</button>
      </div>
    </div>
  </div>
</div>

<div id="subject-load-container" style="display:none;">
  <div class="box box-default">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-table"></i> Subject Load Configuration</h3>
      <div class="box-tools">
        <button class="btn btn-success btn-sm" id="btn-save-loads"><i class="fa fa-save"></i> Save All</button>
      </div>
    </div>
    <div class="box-body p-0">
      <form id="subject-load-form">
        <input type="hidden" name="class_id" id="sl_class_id_hidden">
        <input type="hidden" name="section_id" id="sl_section_id_hidden">
        <div id="subject-load-rows"></div>
      </form>
    </div>
    <div class="box-footer">
      <button class="btn btn-success" id="btn-save-loads-bottom"><i class="fa fa-save"></i> Save All Changes</button>
      <small class="text-muted ml-3"><i class="fa fa-info-circle"></i> Changes take effect on the next Auto Generate run.</small>
    </div>
  </div>
</div>

<div id="subject-load-empty" class="text-center text-muted p-5" style="display:none;">
  <i class="fa fa-exclamation-circle fa-3x"></i><br>
  <strong>No subjects found.</strong><br>
  Please assign subjects to a Subject Group for this class first.
</div>
</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  $('#sl_class_id').on('change', function(){
    var id = $(this).val();
    $('#sl_section_id').html('<option value="">Loading...</option>');
    if (!id) return;
    $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
      {class_id: id, [csrf_name]: csrf_val}, function(res){
        var opts = '<option value="">-- Select Section --</option>';
        $.each(res, function(i,s){ opts += '<option value="'+s.section_id+'">'+s.section+'</option>'; });
        $('#sl_section_id').html(opts);
      },'json');
  });

  $('#btn-load-subjects').on('click', function(){
    var class_id   = $('#sl_class_id').val();
    var section_id = $('#sl_section_id').val();
    if (!class_id || !section_id) { alert('Please select Class and Section.'); return; }

    $('#subject-load-container, #subject-load-empty').hide();
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');

    $.post('<?php echo site_url('admin/tt/get_subject_load_data'); ?>',
      {class_id: class_id, section_id: section_id, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Load Subjects');
        if (res.status === '1' && res.html) {
          $('#sl_class_id_hidden').val(class_id);
          $('#sl_section_id_hidden').val(section_id);
          $('#subject-load-rows').html(res.html);
          $('#subject-load-container').show();
        } else {
          $('#subject-load-empty').show();
        }
      },'json');
  });

  function saveLoads() {
    var $btn = $('#btn-save-loads, #btn-save-loads-bottom').prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    var formData = $('#subject-load-form').serialize() + '&' + csrf_name + '=' + csrf_val;
    $.post('<?php echo site_url('admin/tt/save_subject_load'); ?>', formData, function(res){
      $btn.prop('disabled',false);
      if (res.status === '1') {
        $('#btn-save-loads, #btn-save-loads-bottom').html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
        setTimeout(function(){ $('#btn-save-loads, #btn-save-loads-bottom').html('<i class="fa fa-save"></i> Save All').removeClass('btn-success'); }, 2000);
      } else {
        alert('Error saving. Please try again.');
        $btn.html('<i class="fa fa-save"></i> Save All');
      }
    },'json');
  }

  $('#btn-save-loads, #btn-save-loads-bottom').on('click', saveLoads);
});
</script>
