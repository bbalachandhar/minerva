<?php
// ─── Build slot lookup and collect time periods ───────────────────────────
$DAYS = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

// Standard AMACE time periods
$standard_periods = array(
    '09:00' => '09:50',
    '09:50' => '10:40',
    '11:00' => '11:50',
    '11:50' => '12:40',
    '13:30' => '14:20',
    '14:20' => '15:10',
    '15:10' => '16:00',
);

// Build lookup [day][time_from] => slot  and collect unique periods from data
$slot_map     = array();
$extra_periods = array();
foreach ($timetable_slots as $slot) {
    $slot_map[$slot->day][$slot->time_from] = $slot;
    if (!array_key_exists($slot->time_from, $standard_periods)) {
        $extra_periods[$slot->time_from] = $slot->time_to;
    }
}
$all_periods = array_merge($standard_periods, $extra_periods);
ksort($all_periods);

// Colour by type
$type_class = array(
    'theory'          => 'slot-theory',
    'practical'       => 'slot-practical',
    'project'         => 'slot-project',
    'theory & practical' => 'slot-practical',
    'theory&practical'   => 'slot-practical',
);
?>
<style>
/* ── Grid layout ────────────────────────────────── */
#timetable-grid th  { text-align: center; font-weight: 700; background: #3c8dbc; color: #fff; white-space: nowrap; }
#timetable-grid .time-col { width: 90px; min-width: 90px; background: #f4f4f4; font-size: 12px; text-align: center; vertical-align: middle; padding: 6px 4px; }
#timetable-grid .grid-cell { width: 14%; min-width: 100px; padding: 4px; vertical-align: top; cursor: pointer; transition: background 0.15s; }
#timetable-grid .grid-cell:hover { background: #eef6ff; }

/* ── Slot cards ─────────────────────────────────── */
.slot-card { border-radius: 4px; padding: 5px 7px; font-size: 11px; line-height: 1.4; color: #fff; }
.slot-theory    { background: #3c8dbc; }
.slot-practical { background: #00a65a; }
.slot-project   { background: #f39c12; color: #fff; }
.slot-default   { background: #605ca8; }
.slot-card strong { display: block; font-size: 12px; margin-bottom: 2px; }
.slot-card .slot-meta { opacity: 0.9; font-size: 10px; }

/* ── Empty cell hint ────────────────────────────── */
.empty-cell-hint { color: #ccc; text-align: center; padding: 10px 0; font-size: 20px; }
#timetable-grid .grid-cell:hover .empty-cell-hint { color: #3c8dbc; }

/* ── Break row ──────────────────────────────────── */
.break-row td { background: #fffde7; font-size: 11px; text-align: center; color: #888; padding: 2px 4px; }

/* ── Responsive ─────────────────────────────────── */
@media (max-width: 767px) {
    .tt-scroll { overflow-x: auto; }
    #timetable-grid { min-width: 700px; }
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar"></i> Timetable Grid Editor</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Academics</li>
            <li class="active">Timetable Grid</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <!-- ── Filter box ───────────────────────────────── -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Select Class</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/timetable/classreport'); ?>" class="btn btn-default btn-sm"><i class="fa fa-table"></i> Class Report</a>
                            <a href="<?php echo site_url('admin/timetable/create'); ?>" class="btn btn-default btn-sm"><i class="fa fa-edit"></i> Classic Editor</a>
                        </div>
                    </div>
                    <form action="<?php echo site_url('admin/timetable/grid'); ?>" method="post" id="filter-form">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?> <small class="req">*</small></label>
                                        <select id="department_id" name="department_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($departmentlist as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>"
                                                <?php if (set_value('department_id') == $dept['id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($dept['department_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?> <small class="req">*</small></label>
                                        <select id="class_id" name="class_id" class="form-control">
                                            <?php if ($class_id): ?>
                                            <option value="<?php echo $class_id; ?>" selected><?php echo htmlspecialchars($class_name); ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?> <small class="req">*</small></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <?php if ($section_id): ?>
                                            <option value="<?php echo $section_id; ?>" selected>Section A</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Load Timetable</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div><!-- /filter box -->

                <?php if ($class_id && !empty($all_periods)): ?>
                <!-- ── Grid box ─────────────────────────────────── -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-calendar-check-o"></i>
                            <?php echo htmlspecialchars($class_name); ?> — Weekly Timetable
                        </h3>
                        <div class="box-tools pull-right">
                            <span class="label label-info"><?php echo count($timetable_slots); ?> slots loaded</span>
                            &nbsp;
                            <?php if ($this->rbac->hasPrivilege('class_timetable', 'can_edit')): ?>
                            <button class="btn btn-danger btn-xs" id="clear-all-btn" title="Clear all slots for this class">
                                <i class="fa fa-trash"></i> Clear All
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <!-- Legend -->
                        <div style="padding: 8px 15px; border-bottom: 1px solid #eee;">
                            <span class="label slot-theory">Theory</span>&nbsp;
                            <span class="label slot-practical">Practical</span>&nbsp;
                            <span class="label slot-project">Project</span>&nbsp;
                            <span class="label slot-default">Other</span>&nbsp;
                            <small class="text-muted" style="margin-left:10px;"><i class="fa fa-info-circle"></i> Click any cell to add or edit a slot</small>
                        </div>
                        <div class="tt-scroll">
                            <table id="timetable-grid" class="table table-bordered table-condensed" style="margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th style="width:90px;">Time</th>
                                        <?php foreach ($DAYS as $day): ?>
                                        <th><?php echo $day; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $prev_tf = null;
                                    foreach ($all_periods as $tf => $tt):
                                        // Insert break rows for obvious gaps
                                        if ($prev_tf !== null) {
                                            $gapMins = (strtotime($tf) - strtotime($prev_tf ?: $tf)) / 60;
                                            if ($gapMins >= 20 && $gapMins <= 90) {
                                                $break_label = ($gapMins <= 25) ? 'Break' : 'Lunch Break';
                                    ?>
                                    <tr class="break-row">
                                        <td colspan="7"><i class="fa fa-coffee"></i> <?php echo $break_label; ?></td>
                                    </tr>
                                    <?php
                                            }
                                        }
                                        $prev_tf = $tt;
                                    ?>
                                    <tr>
                                        <td class="time-col">
                                            <strong><?php echo $tf; ?></strong><br>
                                            <span class="text-muted"><?php echo $tt; ?></span>
                                        </td>
                                        <?php foreach ($DAYS as $day):
                                            $slot    = isset($slot_map[$day][$tf]) ? $slot_map[$day][$tf] : null;
                                            $hasSlot = ($slot !== null);
                                            $slotJson = $hasSlot ? htmlspecialchars(json_encode($slot), ENT_QUOTES) : 'null';
                                            $typeKey  = $hasSlot ? strtolower(trim($slot->type)) : '';
                                            $cardClass = isset($type_class[$typeKey]) ? $type_class[$typeKey] : 'slot-default';
                                        ?>
                                        <td class="grid-cell <?php echo $hasSlot ? 'has-slot' : 'empty-slot'; ?>"
                                            data-day="<?php echo $day; ?>"
                                            data-tf="<?php echo $tf; ?>"
                                            data-tt="<?php echo $tt; ?>"
                                            data-slot="<?php echo $slotJson; ?>">
                                            <?php if ($hasSlot): ?>
                                            <div class="slot-card <?php echo $cardClass; ?>">
                                                <strong><?php echo htmlspecialchars($slot->subject_name); ?></strong>
                                                <?php if ($slot->code): ?>
                                                <div class="slot-meta"><i class="fa fa-tag"></i> <?php echo htmlspecialchars($slot->code); ?></div>
                                                <?php endif; ?>
                                                <?php if ($slot->staff_name): ?>
                                                <div class="slot-meta"><i class="fa fa-user"></i> <?php echo htmlspecialchars($slot->staff_name . ' ' . $slot->staff_surname); ?></div>
                                                <?php endif; ?>
                                                <?php if ($slot->room_no && $slot->room_no !== '00:00'): ?>
                                                <div class="slot-meta"><i class="fa fa-map-marker"></i> Room <?php echo htmlspecialchars($slot->room_no); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php else: ?>
                                            <div class="empty-cell-hint"><i class="fa fa-plus-circle"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- /box-body -->
                </div><!-- /grid box -->
                <?php elseif ($class_id): ?>
                <div class="box box-warning">
                    <div class="box-body text-center" style="padding:30px;">
                        <i class="fa fa-info-circle fa-2x text-warning"></i><br><br>
                        No subject group found for this class/section. Please set up subject groups first.
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /col -->
        </div><!-- /row -->
    </section>
</div><!-- /content-wrapper -->


<!-- ════════════════════════════════════════════
     Cell Edit Modal
═════════════════════════════════════════════ -->
<div class="modal fade" id="cellModal" tabindex="-1" role="dialog" aria-labelledby="cellModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#3c8dbc; color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="cellModalLabel">Edit Slot</h4>
            </div>
            <form id="cellForm">
                <?php echo $this->customlib->getCSRF(); ?>
                <input type="hidden" name="cell_id"         id="cell_id"         value="0">
                <input type="hidden" name="class_id"        id="modal_class_id"  value="<?php echo $class_id; ?>">
                <input type="hidden" name="section_id"      id="modal_section_id" value="<?php echo $section_id; ?>">
                <input type="hidden" name="subject_group_id" id="modal_sg_id"    value="<?php echo $subject_group_id; ?>">
                <input type="hidden" name="day"             id="modal_day">
                <input type="hidden" name="time_from"       id="modal_time_from">
                <input type="hidden" name="time_to"         id="modal_time_to">

                <div class="modal-body">
                    <div class="form-group">
                        <label>Subject <small class="req">*</small></label>
                        <select name="subject_group_subject_id" id="modal_subject" class="form-control select2" style="width:100%;">
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s->id; ?>"
                                    data-code="<?php echo htmlspecialchars($s->code); ?>"
                                    data-type="<?php echo htmlspecialchars($s->type); ?>">
                                <?php echo htmlspecialchars($s->name . ' (' . $s->code . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Staff</label>
                        <select name="staff_id" id="modal_staff" class="form-control select2" style="width:100%;">
                            <option value="">-- None / TBA --</option>
                            <?php foreach ($staff_list as $st): ?>
                            <option value="<?php echo $st['id']; ?>">
                                <?php echo htmlspecialchars($st['name'] . ' ' . $st['surname'] . ' (' . $st['employee_id'] . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label>Time From</label>
                                <input type="time" name="time_from_display" id="modal_tf_display" class="form-control" step="600">
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label>Time To</label>
                                <input type="time" name="time_to_display" id="modal_tt_display" class="form-control" step="600">
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="form-group">
                                <label>Room No</label>
                                <input type="text" name="room_no" id="modal_room" class="form-control" placeholder="e.g. 12">
                            </div>
                        </div>
                    </div>

                    <div id="cellFormMsg" class="alert" style="display:none;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-left" id="delete-slot-btn" style="display:none;">
                        <i class="fa fa-trash"></i> Delete Slot
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="save-slot-btn">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
(function($) {
    'use strict';

    var BASE_URL     = '<?php echo base_url(); ?>';
    var CSRF_NAME    = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH    = '<?php echo $this->security->get_csrf_hash(); ?>';

    // ── Helper: refresh CSRF after each AJAX call ──
    function refreshCsrf(response) {
        if (response && response.csrf_hash) {
            CSRF_HASH = response.csrf_hash;
        }
    }

    function csrfData() {
        var d = {};
        d[CSRF_NAME] = CSRF_HASH;
        return d;
    }

    // ── Department → Class cascade ─────────────────
    function loadClasses(deptId, selectedClass) {
        $('#class_id').html('<option value="">Loading...</option>');
        $('#section_id').html('<option value="">Select</option>');
        $.post(BASE_URL + 'admin/timetable/getclassesbydepartment', {department_id: deptId}, function(data) {
            var opts = '<option value="">Select Class</option>';
            $.each(data, function(i, obj) {
                var sel = (selectedClass && selectedClass == obj.id) ? 'selected' : '';
                opts += '<option value="' + obj.id + '" ' + sel + '>' + obj.class + '</option>';
            });
            $('#class_id').html(opts);
            if (selectedClass) { loadSections(selectedClass, <?php echo $section_id ?: 0; ?>); }
        }, 'json');
    }

    function loadSections(classId, selectedSection) {
        $('#section_id').html('<option value="">Loading...</option>');
        $.post(BASE_URL + 'admin/academic/getSectionByClassId', {class_id: classId}, function(data) {
            var opts = '<option value="">Select Section</option>';
            $.each(data, function(i, obj) {
                var sel = (selectedSection && selectedSection == obj.id) ? 'selected' : '';
                opts += '<option value="' + obj.id + '" ' + sel + '>' + obj.section + '</option>';
            });
            $('#section_id').html(opts);
        }, 'json');
    }

    $('#department_id').on('change', function() {
        loadClasses($(this).val(), null);
    });
    $('#class_id').on('change', function() {
        loadSections($(this).val(), null);
    });

    // Pre-load on page init if editing
    <?php if ($class_id): ?>
    (function() {
        var deptId = '<?php echo set_value('department_id'); ?>';
        if (!deptId) {
            // Try to get dept from classlist
            $.post(BASE_URL + 'admin/timetable/getclassesbydepartment', {department_id: ''}, function(){});
        }
        // Just restore section dropdown
        loadSections(<?php echo (int)$class_id; ?>, <?php echo (int)$section_id; ?>);
    })();
    <?php endif; ?>

    // ── Cell click → open modal ────────────────────
    $(document).on('click', '.grid-cell', function() {
        <?php if (!$this->rbac->hasPrivilege('class_timetable', 'can_edit')): ?>
        return; // view-only
        <?php endif; ?>

        var $cell   = $(this);
        var day     = $cell.data('day');
        var tf      = $cell.data('tf');
        var tt      = $cell.data('tt');
        var slot    = $cell.data('slot');

        // Reset form
        $('#cellFormMsg').hide();
        $('#cell_id').val(0);
        $('#modal_day').val(day);

        // Update hidden time fields AND visible time inputs
        $('#modal_time_from').val(tf);
        $('#modal_time_to').val(tt);
        $('#modal_tf_display').val(tf);
        $('#modal_tt_display').val(tt);

        // Update modal title
        var title = (slot && slot !== 'null') ? 'Edit Slot' : 'Add Slot';
        $('#cellModalLabel').text(title + ' — ' + day + '  ' + tf + ' – ' + tt);

        if (slot && slot !== 'null' && typeof slot === 'object') {
            $('#cell_id').val(slot.id || 0);
            $('#modal_subject').val(slot.subject_group_subject_id).trigger('change');
            $('#modal_staff').val(slot.staff_id || '').trigger('change');
            $('#modal_room').val(slot.room_no || '');
            $('#delete-slot-btn').show();
        } else {
            $('#modal_subject').val('').trigger('change');
            $('#modal_staff').val('').trigger('change');
            $('#modal_room').val('');
            $('#delete-slot-btn').hide();
        }

        $('#cellModal').modal('show');
    });

    // Sync visible time inputs to hidden fields on change
    $('#modal_tf_display').on('change', function() { $('#modal_time_from').val($(this).val()); });
    $('#modal_tt_display').on('change', function() { $('#modal_time_to').val($(this).val()); });

    // ── Save slot ──────────────────────────────────
    $('#cellForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#save-slot-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving…');
        $('#cellFormMsg').hide();

        var formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: BASE_URL + 'admin/timetable/savecell',
            data: formData,
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                if (res.status == '1') {
                    $('#cellModal').modal('hide');
                    location.reload();
                } else {
                    $('#cellFormMsg').addClass('alert-danger').removeClass('alert-success')
                        .text(res.message || 'Error').show();
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                $('#cellFormMsg').addClass('alert-danger').removeClass('alert-success')
                    .text('Server error. Please try again.').show();
            }
        });
    });

    // ── Delete slot ────────────────────────────────
    $('#delete-slot-btn').on('click', function() {
        if (!confirm('Delete this slot?')) return;
        var cellId = $('#cell_id').val();
        var data   = csrfData();
        data.cell_id = cellId;

        $.ajax({
            type: 'POST',
            url: BASE_URL + 'admin/timetable/deletecell',
            data: data,
            dataType: 'json',
            success: function(res) {
                if (res.status == '1') {
                    $('#cellModal').modal('hide');
                    location.reload();
                } else {
                    alert(res.message || 'Could not delete');
                }
            }
        });
    });

    // ── Clear all slots for this class ────────────────
    $('#clear-all-btn').on('click', function() {
        if (!confirm('Delete ALL <?php echo count($timetable_slots); ?> timetable slots for <?php echo addslashes(htmlspecialchars($class_name)); ?>?\n\nThis cannot be undone.')) return;
        // Post to a simple delete-all (we'll use savecell/deletecell isn't suitable, so use bulk delete via existing savegroup with empty arrays)
        // For now, just reload (user needs to delete one by one or re-import)
        alert('Please use the import script to bulk-clear and re-import, or delete individual slots by clicking each cell.');
    });

    // ── Select2 init (if available) ───────────────
    if ($.fn.select2) {
        $('#modal_subject, #modal_staff').select2({ dropdownParent: $('#cellModal'), width: '100%' });
    }

})(jQuery);
</script>
