<?php if (isset($msg)) { echo $msg; } ?>
<div class="content-wrapper">
<section class="content-header">
    <h1>Rooms <small>Manage classrooms, labs and other venues</small></h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo site_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Auto Timetable</li>
        <li class="active">Rooms</li>
    </ol>
</section>
<section class="content">
<div class="row">
  <div class="col-md-4">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-plus"></i> Add Room</h3></div>
      <div class="box-body">
        <form id="room-form">
          <input type="hidden" id="room_id" name="id" value="0">
          <div class="form-group">
            <label>Room Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" id="room_name" placeholder="e.g. CSE Lab 1, Room 301" required>
          </div>
          <div class="form-group">
            <label>Room Number</label>
            <input type="text" class="form-control" name="room_number" id="room_number" placeholder="e.g. 301, B-Lab-2">
          </div>
          <div class="form-group">
            <label>Type <span class="text-danger">*</span></label>
            <select class="form-control" name="room_type" id="room_type">
              <option value="classroom">Classroom</option>
              <option value="lab">Lab / Practical Room</option>
              <option value="seminar">Seminar Hall</option>
              <option value="hall">Auditorium / Hall</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Capacity</label>
            <input type="number" class="form-control" name="capacity" id="room_capacity" value="60" min="1">
          </div>
          <div class="form-group">
            <label>Department (optional)</label>
            <select class="form-control" name="department_id" id="room_dept">
              <option value="">-- All Departments --</option>
              <?php foreach ($departments as $dept): ?>
              <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>
              <input type="checkbox" name="is_shared" id="room_shared" value="1">
              Shared Room
            </label>
            <small class="text-muted block">Shared rooms can be used by multiple classes simultaneously (e.g. Playground, Auditorium)</small>
          </div>
          <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save Room</button>
          <button type="button" class="btn btn-default btn-block" id="btn-reset-room">Reset</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="box box-default">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-building"></i> Rooms List</h3>
        <div class="box-tools">
          <span class="badge bg-green"><?php echo count($rooms); ?> rooms</span>
        </div>
      </div>
      <div class="box-body table-responsive p-0">
        <table class="table table-hover table-bordered table-striped" id="rooms-table">
          <thead>
            <tr style="background:#3c8dbc;color:#fff;">
              <th>#</th>
              <th>Name</th>
              <th>Number</th>
              <th>Type</th>
              <th>Capacity</th>
              <th>Shared</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $type_labels = ['classroom'=>'<span class="label label-primary">Classroom</span>','lab'=>'<span class="label label-danger">Lab</span>','seminar'=>'<span class="label label-info">Seminar</span>','hall'=>'<span class="label label-warning">Hall</span>','other'=>'<span class="label label-default">Other</span>']; ?>
            <?php foreach ($rooms as $i => $r): ?>
            <tr>
              <td><?php echo $i+1; ?></td>
              <td><strong><?php echo htmlspecialchars($r->name); ?></strong></td>
              <td><?php echo htmlspecialchars($r->room_number ?? '-'); ?></td>
              <td><?php echo $type_labels[$r->room_type] ?? $r->room_type; ?></td>
              <td><?php echo $r->capacity; ?></td>
              <td><?php echo $r->is_shared ? '<span class="label label-info">Shared</span>' : '-'; ?></td>
              <td><?php echo $r->is_active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>'; ?></td>
              <td>
                <button class="btn btn-xs btn-info btn-edit-room"
                  data-id="<?php echo $r->id; ?>"
                  data-name="<?php echo htmlspecialchars($r->name); ?>"
                  data-number="<?php echo htmlspecialchars($r->room_number ?? ''); ?>"
                  data-type="<?php echo $r->room_type; ?>"
                  data-capacity="<?php echo $r->capacity; ?>"
                  data-dept="<?php echo $r->department_id ?? ''; ?>"
                  data-shared="<?php echo $r->is_shared; ?>">
                  <i class="fa fa-edit"></i>
                </button>
                <a href="<?php echo site_url('admin/tt/delete_room/'.$r->id); ?>"
                   class="btn btn-xs btn-danger btn-delete" data-confirm="Delete this room?">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($rooms)): ?>
        <div class="text-center text-muted p-4"><i class="fa fa-building fa-2x"></i><br>No rooms added yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</section>

<script>
$(function(){
  $('#room_dept').select2({ placeholder: '-- All Departments --', allowClear: true, width: '100%', minimumResultsForSearch: 1 });

  $(document).on('click', '.btn-edit-room', function(){
    var d = $(this).data();
    $('#room_id').val(d.id);
    $('#room_name').val(d.name);
    $('#room_number').val(d.number);
    $('#room_type').val(d.type);
    $('#room_capacity').val(d.capacity);
    $('#room_dept').val(d.dept || '').trigger('change.select2');
    $('#room_shared').prop('checked', d.shared == 1);
    $('html,body').animate({scrollTop:0}, 400);
  });

  $('#btn-reset-room').on('click', function(){
    $('#room-form')[0].reset();
    $('#room_id').val(0);
  });

  $('#room-form').on('submit', function(e){
    e.preventDefault();
    var $btn = $(this).find('[type=submit]').prop('disabled', true).text('Saving...');
    $.post('<?php echo site_url('admin/tt/save_room'); ?>', $(this).serialize() + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>', function(res){
      if (res.status === '1') { location.reload(); }
      else { alert('Error saving. Please try again.'); $btn.prop('disabled',false).text('Save Room'); }
    },'json');
  });

  $(document).on('click','.btn-delete', function(e){
    if (!confirm($(this).data('confirm') || 'Are you sure?')) e.preventDefault();
  });
});
</script>
</div>
