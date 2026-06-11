<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Class Availability <small>Mark periods when a class/section is unavailable for scheduling</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Class Availability</li>
    </ol>
</section>
<section class="content">

<!-- Filter -->
<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-filter"></i> Select Class</h3></div>
  <div class="box-body">
    <div class="row">
      <div class="col-md-3">
        <label>Department</label>
        <select class="form-control" id="cu_dept">
          <option value="">-- All --</option>
          <?php foreach ($departments as $d): ?>
          <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Class <span class="text-danger">*</span></label>
        <select class="form-control" id="cu_class_id">
          <option value="">-- Select Class --</option>
          <?php foreach ($classlist as $cls): ?>
          <option value="<?php echo $cls['id']; ?>" data-dept="<?php echo $cls['department_id']; ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label>Section <span class="text-danger">*</span></label>
        <select class="form-control" id="cu_section_id">
          <option value="">-- Select Section --</option>
        </select>
      </div>
      <div class="col-md-3">
        <label>&nbsp;</label>
        <button class="btn btn-primary btn-block" id="btn-load-cu"><i class="fa fa-search"></i> Load Schedule</button>
      </div>
    </div>
  </div>
</div>

<!-- Unavailability Grid -->
<div id="cu-grid-container" style="display:none;">
  <div class="box box-default">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-ban"></i> Mark Unavailable Slots</h3>
      <div class="box-tools">
        <span class="text-muted" style="font-size:12px;"><i class="fa fa-square" style="color:#e74c3c;"></i> Unavailable &nbsp;
          <i class="fa fa-square" style="color:#eee;border:1px solid #ccc;"></i> Available</span>
      </div>
    </div>
    <div class="box-body">
      <p class="text-muted" style="font-size:12px;"><i class="fa fa-info-circle"></i>
        Click a cell to toggle unavailability. Marked slots will be skipped by the auto-generator for this class.
        Use this for fixed assemblies, sports periods, or any class-level time-off.</p>
      <div class="table-responsive">
        <table class="table table-bordered" id="cu-grid" style="font-size:13px;text-align:center;">
          <thead>
            <tr style="background:#3c8dbc;color:#fff;">
              <th style="min-width:90px;">Period</th>
              <?php foreach ($days as $dk => $dv): ?>
              <th><?php echo $dk; ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($periods as $period):
              if ($period->is_break) continue;
            ?>
            <tr>
              <td style="background:#f4f4f4;font-weight:600;">
                <?php echo htmlspecialchars($period->name); ?><br>
                <small style="font-size:11px;"><?php echo date('h:i', strtotime($period->start_time)); ?></small>
              </td>
              <?php foreach ($days as $dk => $dv): ?>
              <td class="cu-cell" data-day="<?php echo $dk; ?>" data-period="<?php echo $period->id; ?>"
                  style="cursor:pointer;min-width:70px;height:45px;transition:background .15s;">
              </td>
              <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="row" style="margin-top:15px;">
        <div class="col-md-4">
          <button class="btn btn-success btn-block" id="btn-save-cu">
            <i class="fa fa-save"></i> Save Class Availability
          </button>
        </div>
        <div class="col-md-4">
          <button class="btn btn-default btn-block" id="btn-clear-cu">
            <i class="fa fa-times"></i> Clear All (Mark All Available)
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

</section>

<style>
.cu-cell.unavail { background: #e74c3c !important; }
.cu-cell.unavail::after { content: '\f00d'; font-family: FontAwesome; color: #fff; font-size: 16px; line-height: 45px; }
.cu-cell:hover { opacity: 0.8; }
</style>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';
  var current_class = 0, current_section = 0;

  $('#cu_dept').select2({ placeholder: '-- All --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });
  $('#cu_class_id').select2({ placeholder: '-- Select Class --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });
  $('#cu_section_id').select2({ placeholder: '-- Select Section --', allowClear: true, width: '100%' });

  // Store original class options for dept filtering
  var allCuClassOpts = [];
  $('#cu_class_id option').each(function(){
    if ($(this).val()) allCuClassOpts.push({val:$(this).val(), text:$(this).text(), dept:$(this).data('dept')});
  });

  $('#cu_dept').on('change', function(){
    var dept = $(this).val();
    var opts = '<option value="">-- Select Class --</option>';
    $.each(allCuClassOpts, function(i,o){
      if (!dept || o.dept == dept) opts += '<option value="'+o.val+'" data-dept="'+o.dept+'">'+o.text+'</option>';
    });
    $('#cu_class_id').html(opts).trigger('change.select2');
    $('#cu_section_id').html('<option value="">-- Select Section --</option>').trigger('change.select2');
    $('#cu-grid-container').hide();
  });

  $('#cu_class_id').on('change', function(){
    var id = $(this).val();
    $('#cu_section_id').html('<option value="">Loading...</option>');
    if (!id) return;
    $.post('<?php echo site_url('admin/tt/get_sections_by_class'); ?>',
      {class_id: id, [csrf_name]: csrf_val}, function(res){
        var opts = '<option value="">-- Select Section --</option>';
        $.each(res, function(i,s){ opts += '<option value="'+s.section_id+'">'+s.section+'</option>'; });
        $('#cu_section_id').html(opts);
      },'json');
  });

  $('#btn-load-cu').on('click', function(){
    current_class   = $('#cu_class_id').val();
    current_section = $('#cu_section_id').val();
    if (!current_class || !current_section) { alert('Please select Class and Section.'); return; }

    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    $.post('<?php echo site_url('admin/tt/get_class_unavail'); ?>',
      {class_id: current_class, section_id: current_section, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Load Schedule');
        // Reset all cells
        $('.cu-cell').removeClass('unavail');
        if (res.status === '1') {
          $.each(res.data, function(i, slot){
            $('.cu-cell[data-day="'+slot.day+'"][data-period="'+slot.period_id+'"]').addClass('unavail');
          });
        }
        $('#cu-grid-container').show();
      },'json');
  });

  // Toggle cell
  $(document).on('click', '.cu-cell', function(){
    $(this).toggleClass('unavail');
  });

  // Clear all
  $('#btn-clear-cu').on('click', function(){
    $('.cu-cell').removeClass('unavail');
  });

  // Save
  $('#btn-save-cu').on('click', function(){
    if (!current_class || !current_section) { alert('Please load a class first.'); return; }
    var slots = [];
    $('.cu-cell.unavail').each(function(){
      slots.push({day: $(this).data('day'), period_id: $(this).data('period')});
    });
    var $btn = $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    $.post('<?php echo site_url('admin/tt/save_class_unavail'); ?>',
      {class_id: current_class, section_id: current_section,
       slots: slots, [csrf_name]: csrf_val},
      function(res){
        $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Class Availability');
        if (res.status === '1') {
          $btn.html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
          setTimeout(function(){ $btn.html('<i class="fa fa-save"></i> Save Class Availability').removeClass('btn-success'); }, 2000);
        } else {
          alert('Error saving. Please try again.');
        }
      },'json');
  });
});
</script>
</div>
