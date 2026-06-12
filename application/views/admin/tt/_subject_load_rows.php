<div class="table-responsive">
<table class="table table-bordered table-hover" id="sl-rows-table" style="font-size:13px;">
  <thead>
    <tr style="background:#3c8dbc;color:#fff;white-space:nowrap;">
      <th>Subject</th>
      <th>Code</th>
      <th>Type</th>
      <th>Batch</th>
      <th>Teacher <span class="text-warning">*</span></th>
      <th>Alt. Teacher</th>
      <th>Periods/Week <span class="text-warning">*</span></th>
      <th>Consecutive <small>(lab)</small></th>
      <th>Room Type</th>
      <th>Preferred Room</th>
      <th>Max/Day</th>
      <th title="Min 1/Day — If checked, the generator tries to place at least one period of this subject on every working day (never skips a day). Useful for languages and daily practice subjects.">Min/Day <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
      <th title="Spread across days — If checked, periods are distributed across different days of the week (no double-booking on the same day unless necessary). Uncheck only if you want back-to-back days allowed.">Spread <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
      <th title="Scheduling priority (1–10). Higher number = scheduled first by the generator. Set 8–10 for labs, practicals, or subjects with strict room/teacher constraints. Default is 5.">Priority <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
      <th width="36"></th>
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
    <tr data-sgs="<?php echo $sub->subject_group_subject_id; ?>" <?php echo $is_joint ? 'style="background:#f0f8ff;"' : ''; ?>>
      <td>
        <strong><?php echo htmlspecialchars($sub->subject_name); ?></strong>
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
      <td><?php echo htmlspecialchars($sub->subject_code ?? ''); ?></td>
      <td><span class="label <?php echo $type_badge[strtolower($sub->subject_type ?? 'other')] ?? 'label-default'; ?>"><?php echo $sub->subject_type; ?></span></td>
      <td><?php echo $is_joint ? '<span class="label label-info">Joint</span>' : 'Full Class'; ?></td>
      <td>
        <?php if ($is_joint): ?>
        <span class="text-muted" style="font-size:12px;">
          <?php
          $t_names = [];
          if ($existing->staff_name) $t_names[] = $existing->staff_name.' '.($existing->staff_surname ?? '');
          echo $t_names ? htmlspecialchars(implode(', ', $t_names)) : '—';
          ?>
          <br><small>(edit in Joint Lessons)</small>
        </span>
        <?php else: ?>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][staff_id]" required style="min-width:150px;">
          <option value="">-- Select --</option>
          <?php foreach ($staff as $st): ?>
          <option value="<?php echo $st['id']; ?>" <?php echo ($existing && $existing->staff_id == $st['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
      </td>
      <td>
        <?php if (!$is_joint): ?>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][alt_staff_id]" style="min-width:130px;">
          <option value="">-- None --</option>
          <?php foreach ($staff as $st): ?>
          <option value="<?php echo $st['id']; ?>" <?php echo ($existing && $existing->alt_staff_id == $st['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
      </td>
      <td>
        <input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][periods_per_week]"
          value="<?php echo $existing ? $existing->periods_per_week : 4; ?>"
          min="1" max="30" style="width:70px;" <?php echo $dis; ?> <?php echo $is_joint ? '' : 'required'; ?>>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][consecutive_periods]" style="width:80px;" <?php echo $dis; ?>>
          <option value="1" <?php echo (!$existing || $existing->consecutive_periods==1) ? 'selected' : ''; ?>>1</option>
          <option value="2" <?php echo ($existing && $existing->consecutive_periods==2) ? 'selected' : ''; ?>>2 (double)</option>
          <option value="3" <?php echo ($existing && $existing->consecutive_periods==3) ? 'selected' : ''; ?>>3 (triple)</option>
        </select>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][preferred_room_type]" style="width:110px;" <?php echo $dis; ?>>
          <option value="any" <?php echo (!$existing || $existing->preferred_room_type=='any') ? 'selected' : ''; ?>>Any</option>
          <option value="classroom" <?php echo ($existing && $existing->preferred_room_type=='classroom') ? 'selected' : ''; ?>>Classroom</option>
          <option value="lab" <?php echo ($existing && $existing->preferred_room_type=='lab') ? 'selected' : ''; ?>>Lab</option>
          <option value="seminar" <?php echo ($existing && $existing->preferred_room_type=='seminar') ? 'selected' : ''; ?>>Seminar</option>
        </select>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][preferred_room_id]" style="min-width:120px;" <?php echo $dis; ?>>
          <option value="">-- Auto --</option>
          <?php foreach ($rooms as $rm): ?>
          <option value="<?php echo $rm->id; ?>" <?php echo ($existing && $existing->preferred_room_id==$rm->id) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($rm->name); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][max_per_day]"
          value="<?php echo ($existing && isset($existing->max_per_day)) ? $existing->max_per_day : 2; ?>"
          min="1" max="8" style="width:65px;" <?php echo $dis; ?>>
      </td>
      <td>
        <label style="font-weight:normal;margin:0;">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][min_per_day]" value="1"
            <?php echo ($existing && !empty($existing->min_per_day)) ? 'checked' : ''; ?> <?php echo $dis; ?>>
        </label>
      </td>
      <td>
        <label style="font-weight:normal;margin:0;">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][distribute_evenly]" value="1"
            <?php echo (!$existing || $existing->distribute_evenly) ? 'checked' : ''; ?> <?php echo $dis; ?>>
        </label>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][priority]" style="width:70px;" <?php echo $dis; ?>>
          <?php for ($p=10;$p>=1;$p--): ?>
          <option value="<?php echo $p; ?>" <?php echo ($existing && $existing->priority==$p) ? 'selected' : ($p==5 ? 'selected' : ''); ?>><?php echo $p; ?></option>
          <?php endfor; ?>
        </select>
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

    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php if (empty($subjects)): ?>
<div class="text-center text-muted" style="padding:30px 0 10px;">
  <i class="fa fa-book fa-2x" style="color:#bbb;"></i>
  <p style="margin-top:10px;">No subjects configured for this class yet.</p>
</div>
<?php endif; ?>

<!-- Column Legend -->
<div style="background:#f8f9fa;border-top:1px solid #e0e0e0;padding:10px 14px;font-size:12px;color:#555;display:flex;flex-wrap:wrap;gap:18px;">
  <span><strong>P/W</strong> — Periods per week for this subject in this class.</span>
  <span><strong>Consecutive</strong> — 1 = normal single periods &nbsp;|&nbsp; 2 = must be placed as a double period (e.g. lab) &nbsp;|&nbsp; 3 = triple block.</span>
  <span><strong>Max/Day</strong> — Hard cap: no more than this many periods of this subject in a single day.</span>
  <span><i class="fa fa-square text-warning" style="font-size:10px;"></i> <strong>Min/Day</strong> — Soft goal: place at least one period every working day (good for daily-practice subjects like languages, PT).</span>
  <span><i class="fa fa-square text-primary" style="font-size:10px;"></i> <strong>Spread</strong> — Don't put all periods on the same day; distribute across different days of the week. Recommended to keep ON for most subjects.</span>
  <span><i class="fa fa-square text-danger" style="font-size:10px;"></i> <strong>Priority 1–10</strong> — Generator schedules higher-priority subjects first. Use <strong>8–10</strong> for labs/practicals (need specific rooms), <strong>5</strong> for normal subjects, <strong>1–3</strong> for electives or flexible subjects.</span>
</div>

<!-- Add Subject Panel -->
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
