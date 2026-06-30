<style>
/* ── Subject Load Cards ─────────────────────────────────────── */
.sl-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 6px rgba(0,0,0,.07);
  margin-bottom: 10px;
  overflow: hidden;
  border-left: 4px solid #bbb;
  transition: box-shadow .15s;
}
.sl-card:hover { box-shadow: 0 3px 14px rgba(0,0,0,.11); }
.sl-card.type-theory     { border-left-color: #3498db; }
.sl-card.type-practical  { border-left-color: #e74c3c; }
.sl-card.type-project    { border-left-color: #f39c12; }
.sl-card.type-joint      { border-left-color: #3498db; background: #f8fcff; }
/* Header */
.sl-card-head {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px 10px;
  border-bottom: 1px solid #f0f0f0;
}
.sl-card-subject { flex: 1; min-width: 0; }
.sl-card-subject strong { font-size: 14px; color: #222; }
.sl-card-subject .sl-code { font-size: 11px; color: #999; margin-left: 6px; }
.sl-type-pill {
  display: inline-block; border-radius: 12px; padding: 1px 9px;
  font-size: 10px; font-weight: 700; color: #fff; vertical-align: middle; margin-left: 5px;
}
.sl-type-pill.theory    { background: #3498db; }
.sl-type-pill.practical { background: #e74c3c; }
.sl-type-pill.project   { background: #f39c12; }
.sl-type-pill.other     { background: #7f8c8d; }
/* P/W stepper */
.sl-pw-wrap { display: flex; flex-direction: column; align-items: center; gap: 2px; min-width: 70px; }
.sl-pw-wrap label { font-size: 9px; font-weight: 700; color: #999; text-transform: uppercase; margin: 0; letter-spacing: .5px; }
.sl-pw-input { width: 62px; text-align: center; font-size: 18px; font-weight: 700; color: #2c3e50;
               border: 1px solid #e0e0e0; border-radius: 6px; padding: 3px 4px; }
/* Head action buttons */
.sl-head-btns { display: flex; gap: 5px; align-items: center; }
.sl-btn-icon { width: 30px; height: 30px; border-radius: 6px; border: none; cursor: pointer;
               display: flex; align-items: center; justify-content: center; font-size: 13px; transition: all .15s; }
.sl-btn-toggle { background: #f0f4f8; color: #555; }
.sl-btn-toggle:hover { background: #3498db; color: #fff; }
.sl-btn-remove { background: #fdf0f0; color: #e74c3c; }
.sl-btn-remove:hover { background: #e74c3c; color: #fff; }
/* Body */
.sl-card-body { padding: 12px 14px 10px; }
.sl-teacher-label {
  font-size: 10px; font-weight: 700; color: #777; text-transform: uppercase;
  letter-spacing: .4px; margin-bottom: 5px; display: flex; align-items: center; gap: 5px;
}
.sl-teacher-label .req { color: #e74c3c; }
.sl-all-attend { font-size: 11px; color: #888; margin-top: 4px; display: flex; align-items: center; gap: 5px; }
/* Config section */
.sl-card-config {
  display: none;
  background: #f8f9fb;
  border-top: 1px dashed #e4e8ee;
  padding: 10px 14px 12px;
}
.sl-cfg-grid {
  display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;
}
.sl-cfg-item { display: flex; flex-direction: column; gap: 3px; }
.sl-cfg-item > label {
  font-size: 9.5px; font-weight: 700; color: #999; text-transform: uppercase;
  margin: 0; letter-spacing: .4px;
}
.sl-cfg-item .form-control { height: 28px; font-size: 12px; padding: 2px 7px; }
.sl-cfg-check { display: flex; align-items: center; height: 28px; }
/* Workload bar */
.sl-workload-bar { margin-top: 6px; }
.sl-workload-row { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
.sl-workload-name { font-size: 10px; color: #666; min-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sl-workload-track { flex: 1; background: #eee; border-radius: 3px; height: 8px; position: relative; }
.sl-workload-fill  { height: 100%; border-radius: 3px; }
.sl-workload-pct { font-size: 10px; font-weight: 600; min-width: 50px; text-align: right; }
/* Add panel */
.sl-add-panel {
  background: #f0f7ff; border: 2px dashed #90caf9; border-radius: 8px;
  padding: 12px 16px; margin-top: 4px;
}
/* Joint badge */
.sl-joint-link { font-size: 10px; }
/* Legend toggle */
.sl-legend-bar { border-top: 1px solid #eee; padding: 6px 14px; }
.sl-legend-content { display: none; padding: 10px 0 4px; }
.sl-legend-content.open { display: flex; flex-wrap: wrap; gap: 16px; }
.sl-legend-item { font-size: 11px; color: #666; max-width: 300px; }
</style>

<div class="sl-rows-container">
<?php
$type_map = ['theory'=>'theory','practical'=>'practical','project'=>'project','other'=>'other'];
foreach ($subjects as $sub):
  $sgs      = $sub->subject_group_subject_id;
  $key      = $sgs . '_0';
  $existing = $load_map[$key] ?? null;
  $load_id  = $existing ? $existing->id : 0;
  $is_joint = $existing && !empty($existing->joint_lesson_name);
  $dis      = $is_joint ? 'disabled' : '';
  $stype    = strtolower($sub->subject_type ?? 'other');
  $card_cls = 'sl-card type-' . ($is_joint ? 'joint' : ($stype ?: 'other'));
?>
<div class="<?php echo $card_cls; ?> sl-main-row" data-sgs="<?php echo $sgs; ?>">

  <!-- Hidden inputs -->
  <input type="hidden" name="rows[<?php echo $sgs; ?>][subject_group_id]"         value="<?php echo $sub->subject_group_id; ?>">
  <input type="hidden" name="rows[<?php echo $sgs; ?>][subject_group_subject_id]" value="<?php echo $sgs; ?>">
  <input type="hidden" name="rows[<?php echo $sgs; ?>][batch_id]"                 value="">
  <input type="hidden" class="sl-load-id" name="rows[<?php echo $sgs; ?>][load_id]" value="<?php echo $load_id; ?>">
  <?php if ($is_joint): ?><input type="hidden" name="rows[<?php echo $sgs; ?>][_skip_joint]" value="1"><?php endif; ?>

  <!-- Card header -->
  <div class="sl-card-head">
    <div class="sl-card-subject">
      <span class="sl-type-pill <?php echo $stype; ?>"><?php echo htmlspecialchars(ucfirst($sub->subject_type ?? 'Other')); ?></span>
      <strong><?php echo htmlspecialchars($sub->subject_name); ?></strong>
      <?php if (!empty($sub->subject_code)): ?>
        <span class="sl-code">(<?php echo htmlspecialchars($sub->subject_code); ?>)</span>
      <?php endif; ?>
      <?php if ($is_joint): ?>
        <a href="<?php echo site_url('admin/tt/joint_lessons'); ?>" class="sl-joint-link label label-info" style="margin-left:6px;">
          <i class="fa fa-object-group"></i> Joint: <?php echo htmlspecialchars($existing->joint_lesson_name); ?>
        </a>
      <?php endif; ?>
    </div>

    <!-- Periods per week -->
    <div class="sl-pw-wrap">
      <label>P / Week</label>
      <input type="number" class="sl-pw-input form-control"
             name="rows[<?php echo $sgs; ?>][periods_per_week]"
             value="<?php echo $existing ? $existing->periods_per_week : 4; ?>"
             min="1" max="30" <?php echo $dis; ?> <?php echo $is_joint ? '' : 'required'; ?>>
    </div>

    <!-- Action buttons -->
    <div class="sl-head-btns">
      <?php if (!$is_joint): ?>
      <button type="button" class="sl-btn-icon sl-btn-toggle btn-sl-toggle" data-sgs="<?php echo $sgs; ?>" title="Advanced scheduling options">
        <i class="fa fa-cog"></i>
      </button>
      <button type="button" class="sl-btn-icon sl-btn-remove btn-remove-sl-row"
              data-load-id="<?php echo $load_id; ?>" data-sgs="<?php echo $sgs; ?>"
              title="Remove from this class">
        <i class="fa fa-trash"></i>
      </button>
      <?php else: ?>
      <a href="<?php echo site_url('admin/tt/joint_lessons'); ?>" class="sl-btn-icon" style="background:#e8f4fd;color:#3498db;text-decoration:none;" title="Managed via Joint Lessons">
        <i class="fa fa-object-group"></i>
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Card body: Teacher assignment -->
  <div class="sl-card-body">
    <div class="sl-teacher-label">
      <i class="fa fa-users" style="color:#3498db;"></i>
      Teacher Pool <span class="req">*</span>
      <span style="font-weight:400;color:#aaa;font-size:10px;text-transform:none;">— generator picks any free teacher per slot</span>
    </div>
    <?php if ($is_joint): ?>
      <p class="text-muted" style="font-size:12px;margin:0;">
        <?php
        $t_names = [];
        if (!empty($existing->teacher_ids)) {
            foreach ($existing->teacher_ids as $tid) {
                foreach ($staff as $st) {
                    if ($st['id'] == $tid) { $t_names[] = $st['name'].' '.$st['surname']; break; }
                }
            }
        } elseif ($existing->staff_name) {
            $t_names[] = $existing->staff_name.' '.($existing->staff_surname ?? '');
        }
        echo $t_names ? htmlspecialchars(implode(', ', $t_names)) : '—';
        ?>
        <br><small><i class="fa fa-info-circle"></i> Managed in Joint Lessons — <a href="<?php echo site_url('admin/tt/joint_lessons'); ?>">edit there</a></small>
      </p>
    <?php else: ?>
      <select class="form-control input-sm sl-teacher-pool"
              name="rows[<?php echo $sgs; ?>][teacher_ids][]"
              multiple style="width:100%;">
        <?php foreach ($staff as $st): ?>
        <option value="<?php echo $st['id']; ?>"
          <?php
          $sel = false;
          if ($existing && !empty($existing->teacher_ids)) {
              $sel = in_array($st['id'], $existing->teacher_ids);
          } elseif ($existing) {
              $sel = ($existing->staff_id == $st['id'] || $existing->alt_staff_id == $st['id']);
          }
          echo $sel ? 'selected' : '';
          ?>><?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?></option>
        <?php endforeach; ?>
      </select>
      <label class="sl-all-attend">
        <input type="checkbox" name="rows[<?php echo $sgs; ?>][all_teachers_required]" value="1"
          <?php echo ($existing && !empty($existing->all_teachers_required)) ? 'checked' : ''; ?>>
        <span>All must attend simultaneously</span>
        <small class="text-muted" style="font-size:10px;">(e.g. PT, Yoga)</small>
      </label>
      <div class="sl-workload-bar" id="sl-wb-<?php echo $sgs; ?>"></div>
    <?php endif; ?>
  </div>

  <!-- Config section (expandable) -->
  <?php if (!$is_joint): ?>
  <div class="sl-card-config sl-config-row" data-sgs="<?php echo $sgs; ?>">
    <div style="font-size:10px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">
      <i class="fa fa-sliders" style="color:#3498db;"></i>&nbsp; Scheduling Options
    </div>
    <div class="sl-cfg-grid">
      <div class="sl-cfg-item">
        <label>Consecutive</label>
        <select class="form-control input-sm" name="rows[<?php echo $sgs; ?>][consecutive_periods]">
          <option value="1" <?php echo (!$existing || $existing->consecutive_periods==1) ? 'selected' : ''; ?>>1 — Single</option>
          <option value="2" <?php echo ($existing && $existing->consecutive_periods==2) ? 'selected' : ''; ?>>2 — Double</option>
          <option value="3" <?php echo ($existing && $existing->consecutive_periods==3) ? 'selected' : ''; ?>>3 — Triple</option>
        </select>
      </div>
      <div class="sl-cfg-item">
        <label>Room Type</label>
        <select class="form-control input-sm" name="rows[<?php echo $sgs; ?>][preferred_room_type]">
          <option value="any"       <?php echo (!$existing || $existing->preferred_room_type=='any')       ? 'selected' : ''; ?>>Any</option>
          <option value="classroom" <?php echo ($existing && $existing->preferred_room_type=='classroom')  ? 'selected' : ''; ?>>Classroom</option>
          <option value="lab"       <?php echo ($existing && $existing->preferred_room_type=='lab')        ? 'selected' : ''; ?>>Lab</option>
          <option value="seminar"   <?php echo ($existing && $existing->preferred_room_type=='seminar')    ? 'selected' : ''; ?>>Seminar</option>
        </select>
      </div>
      <div class="sl-cfg-item">
        <label>Preferred Room</label>
        <select class="form-control input-sm" name="rows[<?php echo $sgs; ?>][preferred_room_id]" style="min-width:120px;">
          <option value="">Auto</option>
          <?php foreach ($rooms as $rm): ?>
          <option value="<?php echo $rm->id; ?>" <?php echo ($existing && $existing->preferred_room_id==$rm->id) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($rm->name); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sl-cfg-item">
        <label>Max / Day</label>
        <input type="number" class="form-control input-sm" name="rows[<?php echo $sgs; ?>][max_per_day]"
          value="<?php echo ($existing && isset($existing->max_per_day)) ? $existing->max_per_day : 2; ?>"
          min="1" max="8" style="width:60px;">
      </div>
      <div class="sl-cfg-item" style="align-items:center;">
        <label>Min/Day</label>
        <div class="sl-cfg-check">
          <input type="checkbox" name="rows[<?php echo $sgs; ?>][min_per_day]" value="1"
            <?php echo ($existing && !empty($existing->min_per_day)) ? 'checked' : ''; ?>>
        </div>
      </div>
      <div class="sl-cfg-item" style="align-items:center;">
        <label>Spread Days</label>
        <div class="sl-cfg-check">
          <input type="checkbox" name="rows[<?php echo $sgs; ?>][distribute_evenly]" value="1"
            <?php echo (!$existing || $existing->distribute_evenly) ? 'checked' : ''; ?>>
        </div>
      </div>
      <div class="sl-cfg-item">
        <label>Priority</label>
        <select class="form-control input-sm" name="rows[<?php echo $sgs; ?>][priority]" style="width:64px;">
          <?php for ($p=10;$p>=1;$p--): ?>
          <option value="<?php echo $p; ?>" <?php echo ($existing && $existing->priority==$p) ? 'selected' : ($p==5 ? 'selected' : ''); ?>><?php echo $p; ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>
    <div style="margin-top:8px;font-size:10px;color:#bbb;">
      <i class="fa fa-info-circle"></i>
      <strong>Priority:</strong> 1–10, higher = scheduled first.
      <strong>Consecutive:</strong> use 2/3 for labs that always run as double/triple blocks.
      <strong>Max/Day=1:</strong> never back-to-back.
    </div>
  </div>
  <?php endif; ?>

</div><!-- .sl-card -->
<?php endforeach; ?>
</div><!-- .sl-rows-container -->

<?php if (empty($subjects)): ?>
<div class="text-center" style="padding:40px;color:#ccc;">
  <i class="fa fa-book-open" style="font-size:36px;display:block;margin-bottom:10px;"></i>
  <p style="font-size:14px;color:#999;">No subjects configured for this class yet.</p>
  <p style="font-size:12px;color:#bbb;">Use the "Add Subject" section below to get started.</p>
</div>
<?php endif; ?>

<!-- Legend -->
<div class="sl-legend-bar">
  <button type="button" id="btn-toggle-legend" style="background:none;border:none;cursor:pointer;font-size:11px;color:#aaa;padding:2px 0;">
    <i class="fa fa-question-circle"></i> Field Guide
    <i class="fa fa-chevron-down" style="font-size:9px;margin-left:3px;"></i>
  </button>
  <div class="sl-legend-content" id="sl-legend">
    <div class="sl-legend-item"><strong>P/Week</strong> — How many periods per week this subject is taught.</div>
    <div class="sl-legend-item"><strong>Teacher Pool</strong> — Generator picks any free teacher each slot. First selected = primary.</div>
    <div class="sl-legend-item"><strong>Consecutive</strong> — 2 = always placed as a fixed double block (labs). 1 = flexible singles.</div>
    <div class="sl-legend-item"><strong>Max/Day</strong> — 1 = never twice on same day. 2 = allows back-to-back.</div>
    <div class="sl-legend-item"><strong>Spread Days</strong> — Distribute across different days of the week (recommended: ON).</div>
    <div class="sl-legend-item"><strong>Priority 1–10</strong> — Higher = scheduled first. Labs/practicals auto-set to 8.</div>
  </div>
</div>

<!-- Add Subject panel -->
<div class="sl-add-panel">
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <div style="font-size:12px;font-weight:700;color:#1565c0;">
      <i class="fa fa-plus-circle"></i> Add Subject to this class:
    </div>
    <select id="sl-add-subject-picker" multiple="multiple" style="min-width:280px;flex:1;" placeholder="Search and select subjects...">
      <?php foreach ($available_subjects as $as): ?>
      <option value="<?php echo $as->id; ?>"><?php echo htmlspecialchars($as->name . ' (' . $as->code . ')'); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="button" id="btn-add-subjects" class="btn btn-primary btn-sm" style="white-space:nowrap;border-radius:6px;">
      <i class="fa fa-plus"></i> Add Selected
    </button>
    <?php if (empty($available_subjects)): ?>
    <small class="text-success"><i class="fa fa-check"></i> All subjects are already assigned.</small>
    <?php endif; ?>
  </div>
</div>
