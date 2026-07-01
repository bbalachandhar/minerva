<?php
$language1      = $this->customlib->getLanguage();
$language_name1 = $language1["short_code"];

// Attendance status pill config — keys are lowercase+underscore of type name
$att_colors = [
    'present'                => ['bg'=>'#27ae60','text'=>'#fff','icon'=>'fa-check-circle'],
    'late'                   => ['bg'=>'#f39c12','text'=>'#fff','icon'=>'fa-clock-o'],
    'late_with_excuse'       => ['bg'=>'#e67e22','text'=>'#fff','icon'=>'fa-clock-o'],
    'absent'                 => ['bg'=>'#e74c3c','text'=>'#fff','icon'=>'fa-times-circle'],
    'holiday'                => ['bg'=>'#3498db','text'=>'#fff','icon'=>'fa-calendar'],
    'half_day'               => ['bg'=>'#9b59b6','text'=>'#fff','icon'=>'fa-adjust'],
    'half_day_second_shift'  => ['bg'=>'#16a085','text'=>'#fff','icon'=>'fa-adjust'],
    'on_duty'                => ['bg'=>'#8e44ad','text'=>'#fff','icon'=>'fa-briefcase'],
    'medical_leave'          => ['bg'=>'#c0392b','text'=>'#fff','icon'=>'fa-medkit'],
];
$att_default = ['bg'=>'#7f8c8d','text'=>'#fff','icon'=>'fa-circle-o'];
?>
<style>
/* ── Modern Attendance Page ──────────────────── */
.att-filter-card { border-radius:10px;overflow:hidden;margin-bottom:18px; }
.att-filter-head {
  background:linear-gradient(135deg,#2c3e50,#3c6382);
  padding:14px 18px;display:flex;align-items:center;gap:10px;
}
.att-filter-head h3 { color:#fff;margin:0;font-size:15px;font-weight:700; }
.att-filter-body { background:#f8fafc;padding:18px 18px 10px; }

/* Attendance pill buttons */
.att-pill-group { display:flex;flex-wrap:wrap;gap:5px; }
.att-pill {
  display:inline-flex;align-items:center;gap:5px;
  padding:5px 11px;border-radius:20px;cursor:pointer;
  font-size:11px;font-weight:600;border:2px solid transparent;
  transition:all .15s;user-select:none;white-space:nowrap;
  background:#f0f2f5;color:#666;position:relative;overflow:hidden;
}
.att-pill:hover { filter:brightness(.93); }
.att-pill.selected { border-color:transparent !important; }
.att-pill input[type="radio"] { position:absolute;opacity:0;width:0;height:0; }

/* Student table */
.att-table { width:100%;border-collapse:collapse;font-size:13px; }
.att-table th {
  background:#2c3e50;color:#fff;padding:10px 12px;
  text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;
  letter-spacing:.4px;
}
.att-table td { padding:9px 12px;border-bottom:1px solid #f0f0f0;vertical-align:middle; }
.att-table tbody tr:hover { background:#f8fbff; }
.att-table tbody tr:nth-child(even) { background:#fafbfc; }
.att-table tbody tr:nth-child(even):hover { background:#f0f6ff; }
.att-sno { font-weight:700;color:#666;font-size:12px;min-width:28px; }
.att-name { font-weight:600;color:#2c3e50; }
.att-sub { font-size:11px;color:#888; }
.att-note { border:1px solid #e0e0e0;border-radius:6px;padding:5px 8px;
  font-size:12px;width:100%;min-width:110px;max-width:160px;
  background:#fff;color:#444;outline:none; }
.att-note:focus { border-color:#3498db;box-shadow:0 0 0 2px rgba(52,152,219,.15); }

/* Bulk set bar */
.att-bulk-bar {
  display:flex;align-items:center;flex-wrap:wrap;gap:8px;
  background:#fff;border:1px solid #e8ecf0;border-radius:8px;
  padding:10px 14px;margin-bottom:14px;
}
.att-bulk-bar .label-txt { font-size:12px;font-weight:600;color:#555;margin-right:4px;white-space:nowrap; }

/* Save bar */
.att-save-bar {
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
  background:#eafaf1;border:1px solid #a9dfbf;border-radius:8px;padding:10px 16px;
  margin-bottom:14px;
}

/* Status badges */
.badge-already { font-size:11px;padding:4px 10px;border-radius:12px; }

/* Section change note */
.att-section-chip {
  display:inline-flex;align-items:center;gap:4px;
  background:#e8f4fd;color:#1a78c2;border-radius:12px;
  padding:3px 10px;font-size:11px;font-weight:600;
}
</style>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      <i class="fa fa-calendar-check-o" style="color:#3c6382;"></i>
      Period Attendance
      <small>Mark subject-wise attendance by period</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-home"></i> Home</a></li>
      <li>Attendance</li>
      <li class="active">Period Attendance</li>
    </ol>
  </section>

  <section class="content">

    <!-- ── FILTER CARD ─────────────────────────────────────────── -->
    <div class="box att-filter-card">
      <div class="att-filter-head">
        <i class="fa fa-filter" style="color:rgba(255,255,255,.7);font-size:14px;"></i>
        <h3>Select Criteria</h3>
      </div>
      <div class="att-filter-body">
        <?php if ($this->session->flashdata('msg')): ?>
          <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
        <?php endif; ?>

        <form id="form1" action="<?php echo site_url('admin/subjectattendence'); ?>" method="post">
          <?php echo $this->customlib->getCSRF(); ?>
          <div class="row" style="align-items:flex-end;">
            <div class="col-md-3 col-sm-6">
              <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.4px;">
                  Class <span style="color:#e74c3c;">*</span>
                </label>
                <select autofocus id="class_id" name="class_id" class="form-control select2" style="border-radius:6px;">
                  <option value="">— Select Class —</option>
                  <?php foreach ($classlist as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo set_select('class_id', $class['id'], set_value('class_id')); ?>>
                      <?php echo $class['class']; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <span class="text-danger" style="font-size:11px;"><?php echo form_error('class_id'); ?></span>
              </div>
            </div>
            <div class="col-md-2 col-sm-4">
              <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.4px;">
                  Section <span style="color:#e74c3c;">*</span>
                </label>
                <select id="section_id" name="section_id" class="form-control" style="border-radius:6px;">
                  <option value="">— Select —</option>
                </select>
                <span class="text-danger" style="font-size:11px;"><?php echo form_error('section_id'); ?></span>
              </div>
            </div>
            <div class="col-md-2 col-sm-4">
              <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.4px;">
                  Date <span style="color:#e74c3c;">*</span>
                </label>
                <input name="date" type="text" class="form-control date"
                  value="<?php echo set_value('date') ?: $this->customlib->dateformat(date('Y-m-d')); ?>"
                  readonly style="border-radius:6px;background:#fff;cursor:pointer;">
                <span class="text-danger" style="font-size:11px;"><?php echo form_error('date'); ?></span>
              </div>
            </div>
            <div class="col-md-4 col-sm-8">
              <div class="form-group" style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.4px;">
                  Subject / Period <span style="color:#e74c3c;">*</span>
                </label>
                <select id="subject_timetable_id" name="subject_timetable_id" class="form-control select2" style="border-radius:6px;">
                  <option value="">— Select Subject —</option>
                </select>
                <span class="text-danger" style="font-size:11px;"><?php echo form_error('subject_timetable_id'); ?></span>
              </div>
            </div>
            <div class="col-md-1 col-sm-4" style="padding-top:2px;">
              <div class="form-group" style="margin-bottom:12px;">
                <label style="visibility:hidden;font-size:11px;">.</label>
                <button type="submit" name="search" value="search"
                  class="btn btn-primary btn-block"
                  style="border-radius:6px;font-weight:600;">
                  <i class="fa fa-search"></i> Search
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <?php if (isset($resultlist)): ?>
    <!-- ── STUDENT LIST ─────────────────────────────────────────── -->
    <div class="box" style="border-radius:10px;overflow:hidden;">
      <div class="box-header with-border" style="background:#fff;padding:12px 18px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa fa-users" style="font-size:18px;color:#2c3e50;"></i>
            <strong style="font-size:15px;color:#2c3e50;">Student List</strong>
            <?php if (!empty($resultlist)): ?>
              <span style="background:#e8ecf0;color:#555;border-radius:12px;padding:2px 10px;font-size:12px;font-weight:600;">
                <?php echo count($resultlist); ?> students
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="box-body" style="padding:16px 18px;">
        <?php if (!empty($resultlist)): ?>
          <?php
            $can_edit = 1;
            $already_submitted = false;
            $is_holiday = false;
            if (!isset($msg)) {
              if ($resultlist[0]['attendence_type_id'] != "") {
                $already_submitted = true;
                if ($resultlist[0]['attendence_type_id'] == 5) {
                  $is_holiday = true;
                }
                if (!$this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
                  $can_edit = 0;
                }
              }
            }
          ?>

          <?php if (isset($msg)): ?>
            <div class="alert alert-success" style="border-radius:8px;">
              <i class="fa fa-check-circle"></i> <?php echo $this->lang->line('attendance_saved_successfully'); ?>
            </div>
          <?php elseif ($already_submitted && !$is_holiday): ?>
            <div class="alert alert-info" style="border-radius:8px;font-size:13px;">
              <i class="fa fa-info-circle"></i>
              Attendance already submitted for this period. You can update it below.
            </div>
          <?php elseif ($is_holiday): ?>
            <div class="alert alert-warning" style="border-radius:8px;font-size:13px;">
              <i class="fa fa-calendar"></i>
              This period was marked as Holiday. You can change it below.
            </div>
          <?php endif; ?>

          <form action="<?php echo site_url('admin/subjectattendence/index'); ?>" method="post" class="form_attendence">
            <?php echo $this->customlib->getCSRF(); ?>
            <input type="hidden" name="is_first_time_attendance" value="<?php echo $is_first_time_attendance; ?>">
            <input type="hidden" name="class_id"             value="<?php echo $class_id; ?>">
            <input type="hidden" name="section_id"           value="<?php echo $section_id; ?>">
            <input type="hidden" name="subject_timetable_id" value="<?php echo $subject_timetable_id; ?>">
            <input type="hidden" name="date"                 value="<?php echo $date; ?>">

            <?php if ($this->rbac->hasPrivilege('student_attendance', 'can_add')): ?>
            <!-- Bulk set bar -->
            <div class="att-bulk-bar">
              <span class="label-txt"><i class="fa fa-bolt" style="color:#f39c12;"></i> Set all as:</span>
              <div class="att-pill-group">
                <?php foreach ($attendencetypeslist as $type):
                  $att_key = str_replace(" ", "_", strtolower($type['type']));
                  $cfg = $att_colors[$att_key] ?? $att_default;
                ?>
                <label class="att-pill default_radio_label" style="background:<?php echo $cfg['bg']; ?>20;color:<?php echo $cfg['bg']; ?>;border:2px solid <?php echo $cfg['bg']; ?>40;">
                  <input type="radio" name="attendencetype" class="default_radio" value="radio_<?php echo $type['id']; ?>" id="bulk_<?php echo $type['id']; ?>">
                  <i class="fa <?php echo $cfg['icon']; ?>"></i>
                  <?php echo $this->lang->line($att_key); ?>
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Save + summary bar -->
            <?php if ($can_edit): ?>
            <div class="att-save-bar">
              <small style="font-size:12px;color:#555;">
                <i class="fa fa-info-circle text-success"></i>
                Click an attendance status per student, then save.
              </small>
              <button type="submit" name="search" value="saveattendence" id="saveattendence"
                class="btn btn-success"
                style="border-radius:8px;font-weight:700;padding:8px 24px;font-size:14px;">
                <i class="fa fa-save"></i>&nbsp; Save Attendance
              </button>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Student table -->
            <div class="table-responsive">
              <div class="download_label" style="display:none"><?php echo $this->lang->line('student_list'); ?></div>
              <table class="table table-hover table-striped example" style="margin-bottom:0;font-size:13px;">
                <thead>
                  <tr style="background:#2c3e50;color:#fff;">
                    <th style="width:40px;background:#2c3e50;color:#fff;">#</th>
                    <th style="background:#2c3e50;color:#fff;">Admission No</th>
                    <th style="background:#2c3e50;color:#fff;">Roll No</th>
                    <th style="background:#2c3e50;color:#fff;">Student Name</th>
                    <th style="min-width:340px;background:#2c3e50;color:#fff;">Attendance</th>
                    <th style="min-width:130px;background:#2c3e50;color:#fff;">Note</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Find present type ID for default selection
                  $present_type_id = null;
                  if (!empty($attendencetypeslist)) {
                    foreach ($attendencetypeslist as $_t) {
                      if (strtolower($_t['type']) === 'present') { $present_type_id = $_t['id']; break; }
                    }
                    if (!$present_type_id) $present_type_id = $attendencetypeslist[0]['id'];
                  }
                  $row_count = 1;
                  foreach ($resultlist as $key => $value):
                  ?>
                  <tr>
                    <td style="vertical-align:middle;">
                      <input type="hidden" name="student_session[]" value="<?php echo $value['student_session_id']; ?>">
                      <input type="hidden" name="attendance_id<?php echo $value['student_session_id']; ?>" value="<?php echo $value['student_subject_attendance_id']; ?>">
                      <strong style="color:#666;"><?php echo $row_count; ?></strong>
                    </td>
                    <td style="vertical-align:middle;font-size:12px;color:#888;"><?php echo $value['admission_no']; ?></td>
                    <td style="vertical-align:middle;font-size:12px;color:#888;"><?php echo $value['roll_no']; ?></td>
                    <td style="vertical-align:middle;"><strong style="color:#2c3e50;"><?php echo $this->customlib->getFullName($value['firstname'], $value['middlename'], $value['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></strong></td>
                    <td style="vertical-align:middle;">
                      <div class="att-pill-group">
                        <?php $cnt = 0; foreach ($attendencetypeslist as $atype):
                          $akey = str_replace(" ", "_", strtolower($atype['type']));
                          $acfg = $att_colors[$akey] ?? $att_default;
                          $achk = ($value['date'] != 'xxx')
                            ? ($value['attendence_type_id'] == $atype['id'])
                            : ($atype['id'] == $present_type_id);
                          $apid = 'ap_' . $value['student_session_id'] . '_' . $cnt;
                        ?>
                        <label class="att-pill<?php echo $achk ? ' selected' : ''; ?>"
                               for="<?php echo $apid; ?>"
                               style="<?php echo $achk ? "background:{$acfg['bg']};color:{$acfg['text']};" : "background:{$acfg['bg']}20;color:{$acfg['bg']};border:1.5px solid {$acfg['bg']}50;"; ?>">
                          <input type="radio" id="<?php echo $apid; ?>"
                                 name="attendencetype<?php echo $value['student_session_id']; ?>"
                                 value="<?php echo $atype['id']; ?>"
                                 class="radio_<?php echo $atype['id']; ?>"
                                 <?php echo $achk ? 'checked' : ''; ?>>
                          <i class="fa <?php echo $acfg['icon']; ?>"></i>
                          <?php echo $this->lang->line($akey); ?>
                        </label>
                        <?php $cnt++; endforeach; ?>
                      </div>
                    </td>
                    <td style="vertical-align:middle;">
                      <input type="text" class="form-control input-sm"
                             name="remark<?php echo $value['student_session_id']; ?>"
                             value="<?php echo ($value['date'] != 'xxx') ? htmlspecialchars($value['remark'] ?? '') : ''; ?>"
                             placeholder="Note..." style="border-radius:6px;">
                    </td>
                  </tr>
                  <?php $row_count++; endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Bottom save button -->
            <?php if ($can_edit && $this->rbac->hasPrivilege('student_attendance', 'can_add')): ?>
            <div style="margin-top:14px;text-align:right;">
              <button type="submit" name="search" value="saveattendence"
                class="btn btn-success"
                style="border-radius:8px;font-weight:700;padding:9px 28px;font-size:14px;">
                <i class="fa fa-save"></i>&nbsp; Save Attendance
              </button>
            </div>
            <?php endif; ?>

          </form>

        <?php else: ?>
          <div class="alert alert-info" style="border-radius:8px;">
            <i class="fa fa-info-circle"></i>
            <?php echo $this->lang->line('admited_alert'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </section>
</div>

<script>
var date_format = '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d'=>'dd','m'=>'mm','Y'=>'yyyy']); ?>';

$(function(){
  if ($.fn.select2) {
    $('#class_id, #subject_timetable_id').select2({ width: '100%' });
  }

  // ── Restore state after POST ──
  var section_id_post      = "<?php echo set_value('section_id'); ?>";
  var class_id_post        = "<?php echo set_value('class_id'); ?>";
  var date_post            = "<?php echo set_value('date'); ?>";
  var subject_timetable_id = "<?php echo set_value('subject_timetable_id', 0); ?>";
  populateSection(section_id_post, class_id_post);
  getSubjects(class_id_post, section_id_post, date_post, subject_timetable_id);

  // ── Pill colours map for JS ──
  var attColors = {
    <?php if (!empty($attendencetypeslist)): foreach ($attendencetypeslist as $type):
      $att_key = str_replace(" ", "_", strtolower($type['type']));
      $cfg = $att_colors[$att_key] ?? $att_default;
    ?>
    "<?php echo $type['id']; ?>": { bg: "<?php echo $cfg['bg']; ?>", text: "<?php echo $cfg['text']; ?>" },
    <?php endforeach; endif; ?>
  };

  // ── Pill toggle: use 'change' event (fires AFTER radio state is updated) ──
  function updatePillGroup($group) {
    $group.find('.att-pill').each(function(){
      var $radio = $(this).find('input[type="radio"]');
      // Strip "radio_" prefix used by bulk-bar radios (value="radio_1" → "1")
      var val = ($radio.val() || '').replace('radio_', '');
      var cfg = attColors[val] || {bg:'#95a5a6', text:'#fff'};
      if ($radio.is(':checked')) {
        $(this).css({ background: cfg.bg, color: cfg.text, border: '2px solid ' + cfg.bg });
      } else {
        $(this).css({ background: '#f5f5f5', color: '#888', border: '1.5px solid #ddd' });
      }
    });
  }

  $(document).on('change', '.att-pill-group input[type="radio"]', function(){
    updatePillGroup($(this).closest('.att-pill-group'));
  });

  // Run after DataTables finishes drawing (student rows)
  // Use setTimeout to ensure DataTables has re-rendered
  setTimeout(function(){
    $('.att-pill-group').each(function(){ updatePillGroup($(this)); });
  }, 100);

  // Also hook DataTables draw event if table is initialized
  if ($.fn.DataTable) {
    $(document).on('draw.dt', function(){
      $('.att-pill-group').each(function(){ updatePillGroup($(this)); });
    });
  }

  // ── Bulk set all students ──
  $(document).on('change', '.default_radio', function(){
    var returnVal = confirm("<?php echo $this->lang->line('are_you_sure'); ?>");
    if (!returnVal) { $(this).prop('checked', false); return false; }
    var typeId = $(this).val().replace('radio_', '');
    // Set each student's matching radio and refresh that row's pill group
    $('.att-pill-group').each(function(){
      var $grp   = $(this);
      var $match = $grp.find('input[type="radio"][value="' + typeId + '"]');
      if ($match.length) {
        $match.prop('checked', true);
        updatePillGroup($grp);
      }
    });
  });

  // ── Section population ──
  function populateSection(section_id_post, class_id_post) {
    if (!section_id_post || !class_id_post) return;
    $('#section_id').html('');
    var div_data = '<option value="">— Select —</option>';
    $.ajax({ type:'GET', url: baseurl+'sections/getClassTeacherSection',
      data:{ class_id: class_id_post }, dataType:'json',
      success: function(data) {
        if (!data || !data.length) {
          $.ajax({ type:'GET', url: baseurl+'sections/getByClass',
            data:{ class_id: class_id_post }, dataType:'json',
            success: function(d) {
              $.each(d, function(i,o){ div_data += '<option value="'+o.section_id+'"'+(section_id_post==o.section_id?' selected':'')+'>'+o.section+'</option>'; });
              $('#section_id').html(div_data);
            }
          }); return;
        }
        $.each(data, function(i,o){ div_data += '<option value="'+o.section_id+'"'+(section_id_post==o.section_id?' selected':'')+'>'+o.section+'</option>'; });
        $('#section_id').html(div_data);
      }
    });
  }

  // ── Subject dropdown ──
  function getSubjects(class_id, section_id, date, selected_id) {
    if (!class_id || !section_id || !date) return;
    $('#subject_timetable_id').html('<option value="">Loading…</option>');
    $.ajax({ type:'POST', url: baseurl+'admin/subjectgroup/getSubjectByClassandSectionDate',
      data:{ class_id:class_id, section_id:section_id, date:date }, dataType:'json',
      success: function(data) {
        var div_data = '<option value="">— Select Subject —</option>';
        $.each(data, function(i,o){
          var staff = (o.surname && o.surname!='') ? o.name+' '+o.surname : o.name;
          var sel = (selected_id == o.id) ? ' selected' : '';
          div_data += '<option value="'+o.id+'"'+sel+'>'+o.subject_name+' ('+o.time_from+'–'+o.time_to+') · '+staff+'</option>';
        });
        $('#subject_timetable_id').html(div_data);
        if ($.fn.select2) $('#subject_timetable_id').trigger('change.select2');
      }
    });
  }

  $(document).on('change','#class_id', function(){
    var class_id = $(this).val();
    $('#section_id').html('<option value="">Loading…</option>');
    $('#subject_timetable_id').html('<option value="">— Select Subject —</option>');
    $.ajax({ type:'GET', url: baseurl+'sections/getClassTeacherSection',
      data:{ class_id:class_id }, dataType:'json',
      success: function(data) {
        var div_data = '<option value="">— Select —</option>';
        if (!data || !data.length) {
          $.ajax({ type:'GET', url: baseurl+'sections/getByClass', data:{ class_id:class_id }, dataType:'json',
            success: function(d){ $.each(d, function(i,o){ div_data+='<option value="'+o.section_id+'">'+o.section+'</option>'; }); $('#section_id').html(div_data); }
          }); return;
        }
        $.each(data, function(i,o){ div_data += '<option value="'+o.section_id+'">'+o.section+'</option>'; });
        $('#section_id').html(div_data);
      }
    });
  });

  $(document).on('change','#section_id', function(){
    var c = $('#class_id').val(), s = $(this).val(), d = $('input[name="date"]').val();
    getSubjects(c, s, d, 0);
  });

  $('.date').datepicker({
    format: date_format, weekStart: start_week, todayHighlight: true, autoclose: true,
    language: '<?php echo $language_name1; ?>'
  }).on('changeDate', function(){
    getSubjects($('#class_id').val(), $('#section_id').val(), $(this).val(), 0);
  });
});

$('form.form_attendence').on('submit', function(){ $(this).submit(function(){ return false; }); return true; });
</script>
