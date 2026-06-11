<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Subject Colors <small>Set timetable color and abbreviation for each subject</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Subject Colors</li>
    </ol>
</section>
<section class="content">

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-paint-brush"></i> Subject Color &amp; Abbreviation Settings</h3>
    <div class="box-tools">
      <button class="btn btn-success btn-sm" id="btn-save-colors"><i class="fa fa-save"></i> Save All</button>
    </div>
  </div>
  <div class="box-body p-0">
    <form id="color-form">
    <table class="table table-bordered table-hover" style="font-size:13px;">
      <thead>
        <tr style="background:#3c8dbc;color:#fff;">
          <th>#</th>
          <th>Subject Name</th>
          <th>Code</th>
          <th>Type</th>
          <th style="width:130px;">Abbreviation <small>(≤8 chars)</small></th>
          <th style="width:110px;">Color</th>
          <th>Preview</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $type_badge = [
            'theory'    => 'label-primary',
            'practical' => 'label-danger',
            'project'   => 'label-warning',
            'other'     => 'label-default',
        ];
        foreach ($subjects as $i => $sub):
            $color = $sub->tt_color ?: '#3498db';
            $abbr  = $sub->tt_abbr  ?: strtoupper(substr($sub->name, 0, 4));
        ?>
        <tr>
          <td><?php echo $i+1; ?></td>
          <td><strong><?php echo htmlspecialchars($sub->name); ?></strong>
            <input type="hidden" name="subjects[<?php echo $sub->id; ?>][id]" value="<?php echo $sub->id; ?>">
          </td>
          <td><?php echo htmlspecialchars($sub->code ?? ''); ?></td>
          <td><span class="label <?php echo $type_badge[strtolower($sub->type ?? 'other')] ?? 'label-default'; ?>"><?php echo $sub->type; ?></span></td>
          <td>
            <input type="text" class="form-control input-sm abbr-input"
              name="subjects[<?php echo $sub->id; ?>][tt_abbr]"
              value="<?php echo htmlspecialchars($abbr); ?>"
              maxlength="8" style="width:100%;"
              data-idx="<?php echo $sub->id; ?>">
          </td>
          <td>
            <input type="color" class="color-input"
              name="subjects[<?php echo $sub->id; ?>][tt_color]"
              value="<?php echo $color; ?>"
              data-idx="<?php echo $sub->id; ?>"
              style="width:60px;height:34px;padding:2px;border:1px solid #ccc;border-radius:4px;cursor:pointer;">
          </td>
          <td>
            <span class="slot-preview slot-tag" id="prev-<?php echo $sub->id; ?>"
              style="background:<?php echo $color; ?>;color:#fff;padding:4px 10px;border-radius:4px;font-size:12px;font-weight:700;">
              <?php echo htmlspecialchars($abbr); ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </form>
  </div>
  <div class="box-footer">
    <button class="btn btn-success" id="btn-save-colors-bottom"><i class="fa fa-save"></i> Save All</button>
    <small class="text-muted ml-3"><i class="fa fa-info-circle"></i> Colors and abbreviations are used in the timetable grid cells.</small>
  </div>
</div>

</section>

<script>
$(function(){
  var csrf_name = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var csrf_val  = '<?php echo $this->security->get_csrf_hash(); ?>';

  // Live preview
  $(document).on('input change', '.abbr-input', function(){
    var idx = $(this).data('idx');
    $('#prev-' + idx).text($(this).val() || '??');
  });
  $(document).on('input change', '.color-input', function(){
    var idx = $(this).data('idx');
    $('#prev-' + idx).css('background', $(this).val());
  });

  function saveColors(){
    var $btn = $('#btn-save-colors, #btn-save-colors-bottom').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    $.post('<?php echo site_url('admin/tt/save_subject_colors'); ?>',
      $('#color-form').serialize() + '&' + csrf_name + '=' + csrf_val,
      function(res){
        $btn.prop('disabled', false);
        if (res.status === '1') {
          $('#btn-save-colors, #btn-save-colors-bottom').html('<i class="fa fa-check"></i> Saved!').addClass('btn-success');
          setTimeout(function(){ $('#btn-save-colors, #btn-save-colors-bottom').html('<i class="fa fa-save"></i> Save All').removeClass('btn-success'); }, 2000);
        } else {
          alert('Error saving. Please try again.');
          $btn.html('<i class="fa fa-save"></i> Save All');
        }
      },'json');
  }

  $('#btn-save-colors, #btn-save-colors-bottom').on('click', saveColors);
});
</script>
</div>
