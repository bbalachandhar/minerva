<table class="table table-bordered" id="sl-rows-table" style="font-size:13px;margin-bottom:0;">
  <thead>
    <tr style="background:#3c8dbc;color:#fff;">
      <th style="min-width:180px;">Subject</th>
      <th title="Assign one or more teachers. Generator picks any free one (pool mode). Check 'All must attend' below for simultaneous attendance.">Teachers (Pool) <span class="text-warning">*</span></th>
      <th style="width:85px;" title="Periods per Week">P/W <span class="text-warning">*</span></th>
      <th style="width:40px;text-align:center;">
        <button type="button" class="btn btn-xs" id="btn-expand-all" title="Expand / Collapse all scheduling options" style="color:#fff;border-color:rgba(255,255,255,0.4);background:transparent;padding:1px 5px;">
          <i class="fa fa-cog"></i>
        </button>
      </th>
      <th style="width:36px;"></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $type_badge = ['theory'=>'label-primary','practical'=>'label-danger','project'=>'label-warning','other'=>'label-default'];
    foreach ($subjects as $sub):
      $key      = $sub->subject_group_subject_id . '_0';
      $existing = $load_map[$key] ?? null;
      $load_id  = $existing ? $existing->id : 0;
      $is_joint = $existing && !empty($existing->joint_lesson_name);
      $dis      = $is_joint ? 'disabled' : '';
    ?>
    <tr class="sl-main-row" data-sgs="<?php echo $sub->subject_group_subject_id; ?>" <?php echo $is_joint ? 'style="background:#f0f8ff;"' : ''; ?>>
      <td>
        <strong><?php echo htmlspecialchars($sub->subject_name); ?></strong>
        <span class="label <?php echo $type_badge[strtolower($sub->subject_type ?? 'other')] ?? 'label-default'; ?>" style="font-size:10px;vertical-align:middle;margin-left:4px;"><?php echo $sub->subject_type; ?></span>
        <?php if (!empty($sub->subject_code)): ?>
        <small class="text-muted">(<?php echo htmlspecialchars($sub->subject_code); ?>)</small>
        <?php endif; ?>
        <?php if ($is_joint): ?>
        <br><a href="<?php echo site_url('admin/tt/joint_lessons'); ?>" class="label label-info" style="font-size:10px;" title="Managed by Joint Lesson — edit from Joint Lessons screen">
          <i class="fa fa-object-group"></i> Joint: <?php echo htmlspecialchars($existing->joint_lesson_name); ?>
        </a>
        <?php endif; ?>
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][subject_group_id]" value="<?php echo $sub->subject_group_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][subject_group_subject_id]" value="<?php echo $sub->subject_group_subject_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][batch_id]" value="">
        <input type="hidden" class="sl-load-id" name="rows[<?php echo $sub->subject_group_subject_id; ?>][load_id]" value="<?php echo $load_id; ?>">
        <?php if ($is_joint): ?><input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][_skip_joint]" value="1"><?php endif; ?>
      </td>
      <td>
        <?php if ($is_joint): ?>
        <span class="text-muted" style="font-size:12px;">
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
          <br><small>(edit in Joint Lessons)</small>
        </span>
        <?php else: ?>
        <select class="form-control input-sm sl-teacher-pool" name="rows[<?php echo $sub->subject_group_subject_id; ?>][teacher_ids][]" multiple style="width:100%;">
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
            ?>>
            <?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?>
          </option>
          <?php endforeach; ?>
        </select>
        <label style="font-weight:normal;margin:3px 0 0;font-size:11px;color:#777;" title="Require ALL teachers free simultaneously; ALL marked occupied after placement">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][all_teachers_required]" value="1"
            <?php echo ($existing && !empty($existing->all_teachers_required)) ? 'checked' : ''; ?>>
          All must attend
        </label>
        <?php endif; ?>
      </td>
      <td>
        <input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][periods_per_week]"
          value="<?php echo $existing ? $existing->periods_per_week : 4; ?>"
          min="1" max="30" style="width:70px;" <?php echo $dis; ?> <?php echo $is_joint ? '' : 'required'; ?>>
      </td>
      <td class="text-center">
        <?php if (!$is_joint): ?>
        <button type="button" class="btn btn-xs btn-default btn-sl-toggle" data-sgs="<?php echo $sub->subject_group_subject_id; ?>" title="Scheduling options">
          <i class="fa fa-chevron-down"></i>
        </button>
        <?php endif; ?>
      </td>
      <td class="text-center">
        <?php if ($is_joint): ?>
        <a href="<?php echo site_url('admin/tt/joint_lessons'); ?>" class="btn btn-xs btn-info" title="Managed by Joint Lesson — edit there">
          <i class="fa fa-object-group"></i>
        </a>
        <?php else: ?>
        <button type="button" class="btn btn-xs btn-danger btn-remove-sl-row"
          data-load-id="<?php echo $load_id; ?>"
          data-sgs="<?php echo $sub->subject_group_subject_id; ?>"
          title="Remove subject from this class">
          <i class="fa fa-times"></i>
        </button>
        <?php endif; ?>
      </td>
    </tr>
    <?php if (!$is_joint): ?>
    <tr class="sl-config-row" data-sgs="<?php echo $sub->subject_group_subject_id; ?>" style="display:none;">
      <td colspan="5" style="background:#f8f9fb;padding:8px 15px;border-top:1px dashed #e0e0e0;">
        <div class="sl-cfg-group">
          <div class="sl-cfg-item">
            <label>Consecutive</label>
            <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][consecutive_periods]">
              <option value="1" <?php echo (!$existing || $existing->consecutive_periods==1) ? 'selected' : ''; ?>>1 (single)</option>
              <option value="2" <?php echo ($existing && $existing->consecutive_periods==2) ? 'selected' : ''; ?>>2 (double)</option>
              <option value="3" <?php echo ($existing && $existing->consecutive_periods==3) ? 'selected' : ''; ?>>3 (triple)</option>
            </select>
          </div>
          <div class="sl-cfg-item">
            <label>Room Type</label>
            <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][preferred_room_type]">
              <option value="any" <?php echo (!$existing || $existing->preferred_room_type=='any') ? 'selected' : ''; ?>>Any</option>
              <option value="classroom" <?php echo ($existing && $existing->preferred_room_type=='classroom') ? 'selected' : ''; ?>>Classroom</option>
              <option value="lab" <?php echo ($existing && $existing->preferred_room_type=='lab') ? 'selected' : ''; ?>>Lab</option>
              <option value="seminar" <?php echo ($existing && $existing->preferred_room_type=='seminar') ? 'selected' : ''; ?>>Seminar</option>
            </select>
          </div>
          <div class="sl-cfg-item">
            <label>Preferred Room</label>
            <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][preferred_room_id]" style="min-width:120px;">
              <option value="">Auto</option>
              <?php foreach ($rooms as $rm): ?>
              <option value="<?php echo $rm->id; ?>" <?php echo ($existing && $existing->preferred_room_id==$rm->id) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($rm->name); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sl-cfg-item">
            <label>Max/Day</label>
            <input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][max_per_day]"
              value="<?php echo ($existing && isset($existing->max_per_day)) ? $existing->max_per_day : 2; ?>"
              min="1" max="8" style="width:60px;">
          </div>
          <div class="sl-cfg-item">
            <label>Min/Day</label>
            <div class="sl-cfg-check">
              <label style="font-weight:normal;margin:0;">
                <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][min_per_day]" value="1"
                  <?php echo ($existing && !empty($existing->min_per_day)) ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>
          <div class="sl-cfg-item">
            <label>Spread</label>
            <div class="sl-cfg-check">
              <label style="font-weight:normal;margin:0;">
                <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][distribute_evenly]" value="1"
                  <?php echo (!$existing || $existing->distribute_evenly) ? 'checked' : ''; ?>>
              </label>
            </div>
          </div>
          <div class="sl-cfg-item">
            <label>Priority</label>
            <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][priority]" style="width:60px;">
              <?php for ($p=10;$p>=1;$p--): ?>
              <option value="<?php echo $p; ?>" <?php echo ($existing && $existing->priority==$p) ? 'selected' : ($p==5 ? 'selected' : ''); ?>><?php echo $p; ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
      </td>
    </tr>
    <?php endif; ?>

    <?php endforeach; ?>
  </tbody>
</table>

<?php if (empty($subjects)): ?>
<div class="text-center text-muted" style="padding:30px 0 10px;">
  <i class="fa fa-book fa-2x" style="color:#bbb;"></i>
  <p style="margin-top:10px;">No subjects configured for this class yet.</p>
</div>
<?php endif; ?>

<div style="border-top:1px solid #e0e0e0;">
  <button type="button" class="btn btn-link btn-xs" id="btn-toggle-legend" style="padding:6px 14px;font-size:12px;color:#888;">
    <i class="fa fa-question-circle"></i> Column Guide <i class="fa fa-chevron-down" style="font-size:10px;"></i>
  </button>
  <div id="sl-legend" style="display:none;background:#f8f9fa;padding:10px 14px;font-size:12px;color:#555;flex-wrap:wrap;gap:18px;">
    <span><strong>Teachers (Pool)</strong> — Select one or more teachers. Generator picks any free teacher each slot (pool mode). First selected = primary (preferred).</span>
    <span><i class="fa fa-square" style="font-size:10px;color:#888;"></i> <strong>All Must Attend</strong> — If checked, ALL selected teachers must be free simultaneously, and ALL are marked occupied. Use for PT/Yoga where every teacher physically attends.</span>
    <span><strong>P/W</strong> — Periods per week for this subject in this class.</span>
    <span><strong>Consecutive</strong> — 1 = single periods &nbsp;|&nbsp; 2 = <em>always</em> placed as a fixed double block (e.g. lab) &nbsp;|&nbsp; 3 = triple block. <em>For "Maths can sometimes be 2-in-a-row": keep Consecutive=1 and set Max/Day=2.</em></span>
    <span><strong>Max/Day</strong> — Hard cap per day. <strong>Max/Day=1</strong>: strictly one per day, never consecutive. <strong>Max/Day=2</strong>: up to two per day; back-to-back is allowed. Use 2 for subjects like Maths where a double is acceptable.</span>
    <span><i class="fa fa-square text-warning" style="font-size:10px;"></i> <strong>Min/Day</strong> — Soft goal: place at least one period every working day.</span>
    <span><i class="fa fa-square text-primary" style="font-size:10px;"></i> <strong>Spread</strong> — Distribute across different days; keep ON for most subjects.</span>
    <span><i class="fa fa-square text-danger" style="font-size:10px;"></i> <strong>Priority 1–10</strong> — Generator schedules higher-priority subjects first. Auto-set to <strong>8</strong> for practical/integrated and <strong>5</strong> for theory; override per-row anytime.</span>
  </div>
</div>

<div style="border-top:2px dashed #ddd;padding:14px 16px;background:#f9fbff;">
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <label style="margin:0;font-weight:700;font-size:13px;color:#333;white-space:nowrap;">
      <i class="fa fa-plus-circle text-success"></i> Add Subject:
    </label>
    <select id="sl-add-subject-picker" multiple="multiple" style="min-width:300px;flex:1;"
      placeholder="Select subjects to add...">
      <?php foreach ($available_subjects as $as): ?>
      <option value="<?php echo $as->id; ?>">
        <?php echo htmlspecialchars($as->name . ' (' . $as->code . ')'); ?>
      </option>
      <?php endforeach; ?>
    </select>
    <button type="button" id="btn-add-subjects" class="btn btn-success btn-sm" style="white-space:nowrap;">
      <i class="fa fa-plus"></i> Add Selected
    </button>
    <?php if (empty($available_subjects)): ?>
    <small class="text-muted">All subjects are already assigned to this class.</small>
    <?php endif; ?>
  </div>
</div>
