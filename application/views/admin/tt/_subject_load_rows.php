<div class="table-responsive">
<table class="table table-bordered table-hover" style="font-size:13px;">
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
      <th>Spread</th>
      <th>Priority</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $type_badge = ['theory'=>'label-primary','practical'=>'label-danger','project'=>'label-warning','other'=>'label-default'];
    foreach ($subjects as $sub):
      // Build key for existing load
      $key      = $sub->subject_group_subject_id . '_0';
      $existing = $load_map[$key] ?? null;
    ?>
    <tr>
      <td>
        <strong><?php echo htmlspecialchars($sub->subject_name); ?></strong>
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][subject_group_id]" value="<?php echo $sub->subject_group_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][subject_group_subject_id]" value="<?php echo $sub->subject_group_subject_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id; ?>][batch_id]" value="">
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
          <option value="1" <?php echo ($existing && $existing->consecutive_periods==1) ? 'selected' : (!$existing ? 'selected' : ''); ?>>1</option>
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
          min="1" max="8" style="width:65px;" title="Max times this subject can appear on the same day">
      </td>
      <td>
        <label style="font-weight:normal;margin:0;">
          <input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id; ?>][distribute_evenly]" value="1"
            <?php echo (!$existing || $existing->distribute_evenly) ? 'checked' : ''; ?>>
          &nbsp;Spread
        </label>
      </td>
      <td>
        <select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id; ?>][priority]" style="width:70px;">
          <?php for ($p=10;$p>=1;$p--): ?>
          <option value="<?php echo $p; ?>" <?php echo ($existing && $existing->priority==$p) ? 'selected' : ($p==5 ? 'selected' : ''); ?>><?php echo $p; ?></option>
          <?php endfor; ?>
        </select>
      </td>
    </tr>

    <?php // Batch-specific rows
    foreach ($batches as $batch):
      $batch_key = $sub->subject_group_subject_id . '_' . $batch->id;
      $bexisting = $load_map[$batch_key] ?? null;
      if (!$bexisting) continue; // Only show batch row if it exists
    ?>
    <tr style="background:#fffde7;">
      <td style="padding-left:30px;"><i class="fa fa-angle-right text-warning"></i> (Batch <?php echo $batch->batch_name; ?>)</td>
      <td></td>
      <td><span class="label label-warning">Batch</span></td>
      <td><span class="label label-info">Batch <?php echo $batch->batch_name; ?></span>
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][subject_group_id]" value="<?php echo $sub->subject_group_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][subject_group_subject_id]" value="<?php echo $sub->subject_group_subject_id; ?>">
        <input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][batch_id]" value="<?php echo $batch->id; ?>">
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
          <option value="1">1</option><option value="2" <?php echo ($bexisting && $bexisting->consecutive_periods==2)?'selected':''; ?>>2</option><option value="3" <?php echo ($bexisting && $bexisting->consecutive_periods==3)?'selected':''; ?>>3</option>
        </select></td>
      <td><select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][preferred_room_type]" style="width:110px;">
          <option value="any">Any</option><option value="lab" <?php echo ($bexisting && $bexisting->preferred_room_type=='lab')?'selected':''; ?>>Lab</option><option value="classroom">Classroom</option>
        </select></td>
      <td><select class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][preferred_room_id]" style="min-width:120px;">
          <option value="">-- Auto --</option>
          <?php foreach ($rooms as $rm): ?><option value="<?php echo $rm->id; ?>" <?php echo ($bexisting && $bexisting->preferred_room_id==$rm->id)?'selected':''; ?>><?php echo htmlspecialchars($rm->name); ?></option><?php endforeach; ?>
        </select></td>
      <td><input type="number" class="form-control input-sm" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][max_per_day]" value="<?php echo ($bexisting && isset($bexisting->max_per_day)) ? $bexisting->max_per_day : 2; ?>" min="1" max="8" style="width:65px;"></td>
      <td><label style="font-weight:normal;margin:0;"><input type="checkbox" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][distribute_evenly]" value="1" <?php echo (!$bexisting || $bexisting->distribute_evenly) ? 'checked' : ''; ?>></label></td>
      <td><input type="hidden" name="rows[<?php echo $sub->subject_group_subject_id.'_b'.$batch->id; ?>][priority]" value="<?php echo $bexisting ? $bexisting->priority : 5; ?>"></td>
    </tr>
    <?php endforeach; ?>

    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if (empty($subjects)): ?>
<div class="text-center text-muted p-5"><i class="fa fa-exclamation-circle fa-3x"></i><br>No subjects found for this class-section.</div>
<?php endif; ?>
