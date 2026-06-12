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
      <th title="Minimum once per day (On1)">On1</th>
      <th>Spread</th>
      <th>Priority</th>
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
    ?>
    <tr data-sgs="<?php echo $sub->subject_group_subject_id; ?>">
      <td>
        <strong><?php echo htmlspecialchars($sub->subject_name); ?></strong>
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][subject_group_id]" value="<?php echo $sub->subject_group_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][subject_group_subject_id]" value="<?php echo $sub->subject_group_subject_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][batch_id]" value="">
        <input type="hidden" class="sl-load-id" name="rows[<?php echo $sub->subject_group_subject_id; ?>][load_id]" value="<?php echo $load_id; ?>">
      </td>
      <td><?php echo htmlspecialchars($sub->subject_code ?? ''); ?></td>
      <td><span class="label <?php echo $type_badge[strtolower($sub->subject_type ?? 'other')] ?? 'label-default'; ?>"><?php echo $sub->subject_type; ?></span></td>
      <td>Full Class</td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][staff_id]" required style="min-width:150px;">
          <option value="">-- Select --</option>
          <?php foreach ($staff as $st): ?>
          <option value="<?php echo $st['id']; ?>" <?php echo ($existing && $existing->staff_id == $st['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][alt_staff_id]" style="min-width:130px;">
          <option value="">-- None --</option>
          <?php foreach ($staff as $st): ?>
          <option value="<?php echo $st['id']; ?>" <?php echo ($existing && $existing->alt_staff_id == $st['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </td>
      <td>
        <input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][periods_per_week]"
          value="<?php echo $existing ? $existing->periods_per_week : 4; ?>"
          min="1" max="30" style="width:70px;" required>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][consecutive_periods]" style="width:80px;">
          <option value="1" <?php echo (!$existing || $existing->consecutive_periods==1) ? 'selected' : ''; ?>>1</option>
          <option value="2" <?php echo ($existing && $existing->consecutive_periods==2) ? 'selected' : ''; ?>>2 (double)</option>
          <option value="3" <?php echo ($existing && $existing->consecutive_periods==3) ? 'selected' : ''; ?>>3 (triple)</option>
        </select>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][preferred_room_type]" style="width:110px;">
          <option value="any" <?php echo (!$existing || $existing->preferred_room_type=='any') ? 'selected' : ''; ?>>Any</option>
          <option value="classroom" <?php echo ($existing && $existing->preferred_room_type=='classroom') ? 'selected' : ''; ?>>Classroom</option>
          <option value="lab" <?php echo ($existing && $existing->preferred_room_type=='lab') ? 'selected' : ''; ?>>Lab</option>
          <option value="seminar" <?php echo ($existing && $existing->preferred_room_type=='seminar') ? 'selected' : ''; ?>>Seminar</option>
        </select>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][preferred_room_id]" style="min-width:120px;">
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
          min="1" max="8" style="width:65px;">
      </td>
      <td>
        <label style="font-weight:normal;margin:0;">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][min_per_day]" value="1"
            <?php echo ($existing && !empty($existing->min_per_day)) ? 'checked' : ''; ?>>
        </label>
      </td>
      <td>
        <label style="font-weight:normal;margin:0;">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][distribute_evenly]" value="1"
            <?php echo (!$existing || $existing->distribute_evenly) ? 'checked' : ''; ?>>
        </label>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][priority]" style="width:70px;">
          <?php for ($p=10;$p>=1;$p--): ?>
          <option value="<?php echo $p; ?>" <?php echo ($existing && $existing->priority==$p) ? 'selected' : ($p==5 ? 'selected' : ''); ?>><?php echo $p; ?></option>
          <?php endfor; ?>
        </select>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-xs btn-danger btn-remove-sl-row"
          data-load-id="<?php echo $load_id; ?>"
          data-sgs="<?php echo $sub->subject_group_subject_id; ?>"
          title="Remove subject from this class">
          <i class="fa fa-times"></i>
        </button>
      </td>
    </tr>

    <?php foreach ($batches as $batch):
      $batch_key = $sub->subject_group_subject_id . '_' . $batch->id;
      $bexisting = $load_map[$batch_key] ?? null;
      if (!$bexisting) continue;
    ?>
    <tr style="background:#fffde7;" data-sgs="<?php echo $sub->subject_group_subject_id; ?>_b<?php echo $batch->id; ?>">
      <td style="padding-left:30px;"><i class="fa fa-angle-right text-warning"></i> (Batch <?php echo $batch->batch_name; ?>)</td>
      <td></td>
      <td><span class="label label-warning">Batch</span></td>
      <td><span class="label label-info">Batch <?php echo $batch->batch_name; ?></span>
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][subject_group_id]" value="<?php echo $sub->subject_group_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][subject_group_subject_id]" value="<?php echo $sub->subject_group_subject_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][batch_id]" value="<?php echo $batch->id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][load_id]" value="<?php echo $bexisting->id; ?>">
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][staff_id]" style="min-width:150px;">
          <option value="">-- Select --</option>
          <?php foreach ($staff as $st): ?>
          <option value="<?php echo $st['id']; ?>" <?php echo ($bexisting && $bexisting->staff_id==$st['id']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($st['name'].' '.$st['surname']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </td>
      <td><input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][alt_staff_id]" value=""></td>
      <td><input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][periods_per_week]" value="<?php echo $bexisting ? $bexisting->periods_per_week : 2; ?>" min="1" max="30" style="width:70px;"></td>
      <td><select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][consecutive_periods]" style="width:80px;">
          <option value="1">1</option>
          <option value="2" <?php echo ($bexisting && $bexisting->consecutive_periods==2)?'selected':''; ?>>2</option>
          <option value="3" <?php echo ($bexisting && $bexisting->consecutive_periods==3)?'selected':''; ?>>3</option>
        </select></td>
      <td><select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][preferred_room_type]" style="width:110px;">
          <option value="any">Any</option>
          <option value="lab" <?php echo ($bexisting && $bexisting->preferred_room_type=='lab')?'selected':''; ?>>Lab</option>
          <option value="classroom">Classroom</option>
        </select></td>
      <td><select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][preferred_room_id]" style="min-width:120px;">
          <option value="">-- Auto --</option>
          <?php foreach ($rooms as $rm): ?><option value="<?php echo $rm->id; ?>" <?php echo ($bexisting && $bexisting->preferred_room_id==$rm->id)?'selected':''; ?>><?php echo htmlspecialchars($rm->name); ?></option><?php endforeach; ?>
        </select></td>
      <td><input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][max_per_day]" value="<?php echo ($bexisting && isset($bexisting->max_per_day)) ? $bexisting->max_per_day : 2; ?>" min="1" max="8" style="width:65px;"></td>
      <td><label style="font-weight:normal;margin:0;"><input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][min_per_day]" value="1" <?php echo ($bexisting && !empty($bexisting->min_per_day)) ? 'checked' : ''; ?>></label></td>
      <td><label style="font-weight:normal;margin:0;"><input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][distribute_evenly]" value="1" <?php echo (!$bexisting || $bexisting->distribute_evenly) ? 'checked' : ''; ?>></label></td>
      <td><input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][priority]" value="<?php echo $bexisting ? $bexisting->priority : 5; ?>"></td>
      <td></td>
    </tr>
    <?php endforeach; ?>

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
