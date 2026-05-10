<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1><i class="fa fa-building-o"></i> Exam Halls <small>Add, Edit &amp; Delete</small><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo site_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo site_url('coe/coe_seating'); ?>">Seating</a></li>
      <li class="active">Exam Halls</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <div class="row">

      <!-- ============================================================
           LEFT: Hall List Table
           ============================================================ -->
      <div class="col-md-<?php echo $this->rbac->hasPrivilege('coe_seating', 'can_add') ? '8' : '12'; ?>">
        <div class="box box-primary">
          <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="box-title"><i class="fa fa-list"></i> Exam Halls (<?php echo count($halls); ?>)</h3>
            <a href="<?php echo site_url('coe/coe_seating'); ?>" class="btn btn-default btn-sm">
              <i class="fa fa-arrow-left"></i> Back to Seating
            </a>
          </div>
          <div class="box-body table-responsive no-padding">
            <?php if (empty($halls)): ?>
              <div class="alert alert-info" style="margin:15px;">
                <i class="fa fa-info-circle"></i> No exam halls found. Add one using the form.
              </div>
            <?php else: ?>
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Hall Name</th>
                  <th>Capacity</th>
                  <th>Location</th>
                  <th>Block / Floor</th>
                  <th>Rooms Used</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($halls as $i => $hall): ?>
                <tr>
                  <td><?php echo $i + 1; ?></td>
                  <td><strong><?php echo htmlspecialchars($hall->name); ?></strong></td>
                  <td><span class="badge bg-blue"><?php echo (int)$hall->capacity; ?></span></td>
                  <td><?php echo htmlspecialchars($hall->location); ?></td>
                  <td><?php echo htmlspecialchars($hall->description ?: '—'); ?></td>
                  <td>
                    <?php if ($hall->rooms_count > 0): ?>
                      <span class="badge bg-orange"><?php echo (int)$hall->rooms_count; ?> room(s)</span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($hall->is_active): ?>
                      <span class="label label-success">Active</span>
                    <?php else: ?>
                      <span class="label label-default">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td style="white-space:nowrap;">
                    <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_edit')): ?>
                    <button class="btn btn-xs btn-warning btn-edit-hall"
                      data-id="<?php echo $hall->id; ?>"
                      data-name="<?php echo htmlspecialchars($hall->name, ENT_QUOTES); ?>"
                      data-capacity="<?php echo (int)$hall->capacity; ?>"
                      data-location="<?php echo htmlspecialchars($hall->location, ENT_QUOTES); ?>"
                      data-description="<?php echo htmlspecialchars($hall->description ?: '', ENT_QUOTES); ?>"
                      data-active="<?php echo (int)$hall->is_active; ?>"
                    ><i class="fa fa-pencil"></i> Edit</button>
                    <?php endif; ?>
                    <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_delete')): ?>
                    <a href="<?php echo site_url('coe/coe_seating/delete_hall/' . $hall->id); ?>"
                       class="btn btn-xs btn-danger btn-confirm-delete"
                       data-name="<?php echo htmlspecialchars($hall->name, ENT_QUOTES); ?>"
                       data-rooms="<?php echo (int)$hall->rooms_count; ?>"
                    ><i class="fa fa-trash"></i> Delete</a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- ============================================================
           RIGHT: Add / Edit Hall Form
           ============================================================ -->
      <?php if ($this->rbac->hasPrivilege('coe_seating', 'can_add')): ?>
      <div class="col-md-4">
        <div class="box box-success" id="hallFormBox">
          <div class="box-header with-border">
            <h3 class="box-title" id="hallFormTitle"><i class="fa fa-plus"></i> Add Exam Hall</h3>
          </div>
          <div class="box-body">
            <form method="post" action="<?php echo site_url('coe/coe_seating/save_hall'); ?>" id="hallForm">
              <input type="hidden" name="id" id="hall_id" value="0">

              <div class="form-group">
                <label>Hall Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="hall_name" class="form-control"
                       placeholder="e.g. ANR Hall, Room 101" required>
              </div>

              <div class="form-group">
                <label>Seating Capacity <span class="text-danger">*</span></label>
                <input type="number" name="capacity" id="hall_capacity" class="form-control"
                       placeholder="e.g. 60" min="1" required>
              </div>

              <div class="form-group">
                <label>Location / Block</label>
                <input type="text" name="location" id="hall_location" class="form-control"
                       placeholder="e.g. A Block, First Floor">
              </div>

              <div class="form-group">
                <label>Block / Floor Notes <small class="text-muted">(optional)</small></label>
                <input type="text" name="block" id="hall_block" class="form-control"
                       placeholder="e.g. Ground Floor, East Wing">
              </div>

              <div class="form-group">
                <label>Status</label>
                <select name="is_active" id="hall_is_active" class="form-control">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>

              <div class="row">
                <div class="col-xs-6">
                  <button type="submit" class="btn btn-success btn-block" id="hallSubmitBtn">
                    <i class="fa fa-save"></i> Save Hall
                  </button>
                </div>
                <div class="col-xs-6">
                  <button type="button" class="btn btn-default btn-block" id="hallResetBtn">
                    <i class="fa fa-times"></i> Clear
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Help tip -->
        <div class="callout callout-info">
          <h4><i class="fa fa-lightbulb-o"></i> Tips</h4>
          <ul style="margin:0;padding-left:18px;font-size:.9rem;">
            <li>Set <strong>capacity</strong> to the number of exam seats (not total chairs).</li>
            <li>You can override capacity per seating room without changing this master value.</li>
            <li>Inactive halls won't appear in the room creation dropdown.</li>
            <li>You cannot delete a hall that is currently assigned to seating rooms.</li>
          </ul>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /.row -->
  </section>
</div>

<script>
(function () {
  // Edit button — populate form
  document.querySelectorAll('.btn-edit-hall').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var d = btn.dataset;
      document.getElementById('hall_id').value        = d.id;
      document.getElementById('hall_name').value      = d.name;
      document.getElementById('hall_capacity').value  = d.capacity;
      document.getElementById('hall_location').value  = d.location;
      document.getElementById('hall_block').value     = d.description;
      document.getElementById('hall_is_active').value = d.active;
      document.getElementById('hallFormTitle').innerHTML = '<i class="fa fa-pencil"></i> Edit Hall: ' + d.name;
      document.getElementById('hallSubmitBtn').innerHTML = '<i class="fa fa-save"></i> Update Hall';
      document.getElementById('hallFormBox').className  = 'box box-warning';
      document.getElementById('hallForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // Reset button
  document.getElementById('hallResetBtn') && document.getElementById('hallResetBtn').addEventListener('click', function () {
    document.getElementById('hall_id').value        = '0';
    document.getElementById('hall_name').value      = '';
    document.getElementById('hall_capacity').value  = '';
    document.getElementById('hall_location').value  = '';
    document.getElementById('hall_block').value     = '';
    document.getElementById('hall_is_active').value = '1';
    document.getElementById('hallFormTitle').innerHTML = '<i class="fa fa-plus"></i> Add Exam Hall';
    document.getElementById('hallSubmitBtn').innerHTML = '<i class="fa fa-save"></i> Save Hall';
    document.getElementById('hallFormBox').className   = 'box box-success';
  });

  // Delete confirmation
  document.querySelectorAll('.btn-confirm-delete').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var name  = btn.dataset.name;
      var rooms = parseInt(btn.dataset.rooms, 10);
      var msg   = rooms > 0
        ? '"' + name + '" is assigned to ' + rooms + ' seating room(s). Remove those rooms first.'
        : 'Delete hall "' + name + '"? This cannot be undone.';

      if (rooms > 0) {
        if (typeof swal !== 'undefined') {
          swal({ title: 'Cannot Delete', text: msg, type: 'error' });
        } else { alert(msg); }
        return;
      }

      if (typeof swal !== 'undefined') {
        swal({
          title: 'Delete Hall?', text: msg, type: 'warning',
          showCancelButton: true, confirmButtonColor: '#c62828', confirmButtonText: 'Delete'
        }, function (confirmed) { if (confirmed) window.location.href = btn.href; });
      } else if (confirm(msg)) {
        window.location.href = btn.href;
      }
    });
  });
})();
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'seating']); ?>
