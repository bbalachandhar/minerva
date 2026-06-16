<div class="table-responsive">
<table class="table table-bordered table-hover" id="sl-rows-table" style="font-size:13px;">
  <thead>
    <tr style="background:#3c8dbc;color:#fff;white-space:nowrap;">
      <th>Subject</th>
      <th>Code</th>
      <th>Type</th>
      <th>Batch</th>
      <th title="Assign one or more teachers. Generator picks any free one (pool mode) or requires ALL to attend simultaneously.">Teachers (Pool) <span class="text-warning">*</span></th>
      <th title="All must attend — if checked, ALL teachers in the pool must be free at the same time, and ALL get marked occupied. Use for activities like Yoga or PT where every teacher is physically present.">All Attend <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
      <th>Periods/Week <span class="text-warning">*</span></th>
      <th>Consecutive <small>(lab)</small></th>
      <th>Room Type</th>
      <th>Preferred Room</th>
      <th>Max/Day</th>
      <th title="Min 1/Day — If checked, the generator tries to place at least one period of this subject on every working day (never skips a day). Useful for languages and daily practice subjects.">Min/Day <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
      <th title="Spread across days — If checked, periods are distributed across different days of the week (no double-booking on the same day unless necessary). Uncheck only if you want back-to-back days allowed.">Spread <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
      <th title="Scheduling priority (1–10). Higher number = scheduled first by the generator. Auto-set to 8 for practical/integrated subjects and 5 for theory — override anytime.">Priority <i class="fa fa-question-circle text-warning" style="font-size:11px;"></i></th>
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
        <select class="form-control input-sm sl-teacher-pool" name="rows[<?php echo $sub->subject_group_subject_id; ?>][teacher_ids][]" multiple style="min-width:200px;">
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
        <?php endif; ?>
      </td>
      <td class="text-center">
        <?php if ($is_joint): ?>
        <span class="text-muted">—</span>
        <?php else: ?>
        <label style="font-weight:normal;margin:0;" title="Require ALL teachers free simultaneously; ALL marked occupied after placement">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][all_teachers_required]" value="1"
            <?php echo ($existing && !empty($existing->all_teachers_required)) ? 'checked' : ''; ?>>
        </label>
        <?php endif; ?>
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
  <span><strong>Teachers (Pool)</strong> — Select one or more teachers. Generator picks any free teacher each slot (pool mode). First selected = primary (preferred).</span>
  <span><i class="fa fa-square" style="font-size:10px;color:#888;"></i> <strong>All Attend</strong> — If checked, ALL selected teachers must be free simultaneously, and ALL are marked occupied. Use for PT/Yoga where every teacher physically attends.</span>
  <span><strong>P/W</strong> — Periods per week for this subject in this class.</span>
  <span><strong>Consecutive</strong> — 1 = single periods &nbsp;|&nbsp; 2 = <em>always</em> placed as a fixed double block (e.g. lab) &nbsp;|&nbsp; 3 = triple block. <em>For "Maths can sometimes be 2-in-a-row": keep Consecutive=1 and set Max/Day=2.</em></span>
  <span><strong>Max/Day</strong> — Hard cap per day. <strong>Max/Day=1</strong>: strictly one per day, never consecutive. <strong>Max/Day=2</strong>: up to two per day; back-to-back is allowed (generator won't penalise adjacency). Use 2 for subjects like Maths where a double is acceptable.</span>
  <span><i class="fa fa-square text-warning" style="font-size:10px;"></i> <strong>Min/Day</strong> — Soft goal: place at least one period every working day.</span>
  <span><i class="fa fa-square text-primary" style="font-size:10px;"></i> <strong>Spread</strong> — Distribute across different days; keep ON for most subjects.</span>
  <span><i class="fa fa-square text-danger" style="font-size:10px;"></i> <strong>Priority 1–10</strong> — Generator schedules higher-priority subjects first. Auto-set to <strong>8</strong> for practical/integrated subjects and <strong>5</strong> for theory when added; override per-row anytime (use <strong>1–3</strong> for electives).</span>
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
