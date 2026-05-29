<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-file-text-o"></i> Question Paper Distribution
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_qpd'); ?>"><i class="fa fa-arrow-left"></i> Back to Events</a></li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">

            <!-- Upload Panel -->
            <?php if ($this->rbac->hasPrivilege('coe_qpd', 'can_add')): ?>
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> Upload New Question Paper</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <!-- flatpickr CSS (local) -->
                        <link rel="stylesheet" href="<?php echo base_url('backend/plugins/flatpickr/flatpickr.min.css'); ?>">
                        <style>
                          .qpd-upload-row { display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; }
                          .qpd-upload-row .qpd-col { flex:1 1 180px; min-width:0; }
                          .qpd-upload-row .qpd-col-btn { flex:0 0 160px; }
                          .qpd-file-wrap { position:relative; }
                          .qpd-file-wrap input[type=file] { position:absolute;left:0;top:0;width:100%;height:100%;opacity:0;cursor:pointer;z-index:2; }
                          .qpd-file-display { display:flex;align-items:center;border:1px solid #d2d6de;border-radius:3px;background:#fff;height:34px;overflow:hidden; }
                          .qpd-file-display .qpd-file-btn { background:#f4f4f4;border-right:1px solid #d2d6de;padding:0 12px;height:100%;display:flex;align-items:center;white-space:nowrap;font-size:13px;color:#444; }
                          .qpd-file-display .qpd-file-name { padding:0 10px;font-size:12.5px;color:#777;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
                        </style>

                        <form method="post" action="<?php echo site_url('coe/coe_qpd/upload/' . $batch_exam_id); ?>" enctype="multipart/form-data" id="qpd-upload-form">
                          <div class="qpd-upload-row">

                            <!-- Subject -->
                            <div class="qpd-col" style="flex:2 1 220px;">
                              <div class="form-group" style="margin-bottom:0;">
                                <label>Subject <span class="text-red">*</span></label>
                                <select name="subject_id" class="form-control" required>
                                  <option value="">— Select Subject —</option>
                                  <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub->id; ?>"><?php echo htmlspecialchars($sub->code . ' – ' . $sub->name); ?></option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                            </div>

                            <!-- File picker -->
                            <div class="qpd-col" style="flex:2 1 220px;">
                              <div class="form-group" style="margin-bottom:0;">
                                <label>Question Paper File <span class="text-red">*</span></label>
                                <div class="qpd-file-wrap">
                                  <input type="file" name="paper_file" id="qpd-file-input" accept=".pdf,.doc,.docx" required>
                                  <div class="qpd-file-display">
                                    <span class="qpd-file-btn"><i class="fa fa-folder-open"></i>&nbsp; Browse…</span>
                                    <span class="qpd-file-name" id="qpd-file-label">No file chosen</span>
                                  </div>
                                </div>
                                <p class="help-block" style="margin:3px 0 0;font-size:11px;">PDF, DOC, DOCX — max 20 MB</p>
                              </div>
                            </div>

                            <!-- Datetime picker -->
                            <div class="qpd-col" style="flex:1.5 1 180px;">
                              <div class="form-group" style="margin-bottom:0;">
                                <label>Unlock At (Time Lock) <span class="text-red">*</span></label>
                                <div class="input-group flatpickr" id="qpd-dt-wrap">
                                  <input type="text" name="unlock_at" id="qpd-unlock-at" class="form-control"
                                         placeholder="Select date &amp; time" autocomplete="off" data-input>
                                  <span class="input-group-addon" data-toggle style="cursor:pointer;">
                                    <i class="fa fa-calendar"></i>
                                  </span>
                                </div>
                                <p class="help-block" style="margin:3px 0 0;font-size:11px;">Paper sealed until this date/time</p>
                              </div>
                            </div>

                            <!-- Submit -->
                            <div class="qpd-col-btn">
                              <button type="submit" class="btn btn-success btn-block"
                                      onclick="return confirm('Upload and encrypt this question paper?')">
                                <i class="fa fa-lock"></i> Encrypt &amp; Upload
                              </button>
                            </div>

                          </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Papers List -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;">
                        <h3 class="box-title"><i class="fa fa-list"></i> Uploaded Papers</h3>
                        <a href="<?php echo site_url('coe/coe_qpd'); ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Original Filename</th>
                                    <th>Uploaded By</th>
                                    <th>Unlock At</th>
                                    <th>Status</th>
                                    <th>Downloads</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($papers)): ?>
                                    <tr><td colspan="8" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($papers as $i => $p): ?>
                                    <?php
                                        $is_locked    = strtotime($p->unlock_at) > strtotime($now);
                                        $is_distributed = (bool) $p->is_distributed;
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->subject_code); ?></strong> <?php echo htmlspecialchars($p->subject_name); ?></td>
                                        <td><?php echo htmlspecialchars($p->original_filename); ?></td>
                                        <td><?php echo htmlspecialchars($p->uploaded_by_name ?? '—'); ?></td>
                                        <td>
                                            <?php echo date('d M Y h:i A', strtotime($p->unlock_at)); ?>
                                            <?php if ($is_locked): ?>
                                                <span class="label label-warning"><i class="fa fa-lock"></i> Locked</span>
                                            <?php else: ?>
                                                <span class="label label-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_distributed): ?>
                                                <span class="label label-info">Distributed<br><?php echo date('d M Y h:i A', strtotime($p->distributed_at)); ?></span>
                                            <?php elseif (!$is_locked): ?>
                                                <span class="label label-default">Not yet distributed</span>
                                            <?php else: ?>
                                                <span class="label label-default">Sealed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo (int) $p->download_count; ?></td>
                                        <td>
                                            <?php if (!$is_locked): ?>
                                                <a href="<?php echo site_url('coe/coe_qpd/download/' . $p->id); ?>" class="btn btn-xs btn-primary" title="Download (decrypted)">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                                <?php if (strtolower(pathinfo($p->original_filename, PATHINFO_EXTENSION)) === 'pdf'): ?>
                                                <button type="button" class="btn btn-xs btn-info qpd-preview-btn"
                                                        title="Preview PDF"
                                                        data-url="<?php echo site_url('coe/coe_qpd/preview/' . $p->id); ?>"
                                                        data-title="<?php echo htmlspecialchars($p->subject_code . ' – ' . $p->subject_name . ' | ' . $p->original_filename); ?>">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-xs btn-default" disabled title="Locked until <?php echo date('d M Y h:i A', strtotime($p->unlock_at)); ?>">
                                                    <i class="fa fa-lock"></i> Locked
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_qpd', 'can_add') && !$is_distributed): ?>
                                                <button type="button" class="btn btn-xs btn-warning qpd-edit-unlock-btn"
                                                        title="Edit Unlock Time"
                                                        data-id="<?php echo $p->id; ?>"
                                                        data-unlock="<?php echo date('Y-m-d H:i', strtotime($p->unlock_at)); ?>"
                                                        data-subject="<?php echo htmlspecialchars($p->subject_code . ' – ' . $p->subject_name); ?>">
                                                    <i class="fa fa-clock-o"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_qpd', 'can_delete') && !$is_distributed): ?>
                                                <a href="<?php echo site_url('coe/coe_qpd/delete/' . $p->id); ?>" class="btn btn-xs btn-danger"
                                                   onclick="return confirm('Delete this paper? This cannot be undone.')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- flatpickr (local) -->
<script src="<?php echo base_url('backend/plugins/flatpickr/flatpickr.min.js'); ?>"></script>
<script>
(function(){
  var fpBase = {
    enableTime:      true,
    dateFormat:      'Y-m-d H:i',
    altInput:        true,
    altFormat:       'd M Y  H:i',
    minuteIncrement: 5,
    time_24hr:       true,
    disableMobile:   true
  };

  // --- Upload form picker (no wrap, no appendTo — plain inline) ---
  var uploadPicker = flatpickr('#qpd-unlock-at', Object.assign({}, fpBase, {
    minDate: new Date()
  }));
  // Calendar icon click → toggle picker
  var uploadAddon = document.querySelector('#qpd-dt-wrap .input-group-addon');
  if (uploadAddon) {
    uploadAddon.addEventListener('click', function(e) {
      e.stopPropagation();
      uploadPicker.isOpen ? uploadPicker.close() : uploadPicker.open();
    });
  }

  // --- Edit-unlock picker (appendTo body to escape modal overflow/z-index) ---
  var editPicker = flatpickr('#qpd-edit-unlock-at', Object.assign({}, fpBase, {
    appendTo: document.body
  }));
  var editAddon = document.querySelector('#qpd-edit-dt-wrap .input-group-addon');
  if (editAddon) {
    editAddon.addEventListener('click', function(e) {
      e.stopPropagation();
      editPicker.isOpen ? editPicker.close() : editPicker.open();
    });
  }

  // File label updater
  var fileInput = document.getElementById('qpd-file-input');
  var fileLabel = document.getElementById('qpd-file-label');
  if (fileInput && fileLabel) {
    fileInput.addEventListener('change', function() {
      fileLabel.textContent = this.files.length ? this.files[0].name : 'No file chosen';
    });
  }

  // Upload form validation
  document.getElementById('qpd-upload-form').addEventListener('submit', function(e){
    if (!document.getElementById('qpd-unlock-at').value.trim()) {
      e.preventDefault();
      uploadPicker.open();
      return false;
    }
  });

  // Edit-unlock modal: populate picker when opening
  $(document).on('click', '.qpd-edit-unlock-btn', function(){
    var id      = $(this).data('id');
    var unlock  = $(this).data('unlock');   // 'YYYY-MM-DD HH:mm'
    var subject = $(this).data('subject');
    $('#qpd-edit-subject').text(subject);
    $('#qpd-edit-unlock-form').attr('action', '<?php echo site_url('coe/coe_qpd/edit_unlock/'); ?>' + id);
    editPicker.setDate(unlock, false, 'Y-m-d H:i');
    $('#qpdEditUnlockModal').modal('show');
  });
})();
</script>

<!-- Edit Unlock Time Modal -->
<div class="modal fade" id="qpdEditUnlockModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header" style="padding:10px 15px;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-clock-o text-orange"></i> Edit Unlock Time</h4>
      </div>
      <form method="post" id="qpd-edit-unlock-form">
        <div class="modal-body">
          <p id="qpd-edit-subject" class="text-muted" style="margin-bottom:10px;font-size:12px;"></p>
          <div class="form-group">
            <label>New Unlock Date &amp; Time <span class="text-red">*</span></label>
            <div class="input-group flatpickr" id="qpd-edit-dt-wrap">
              <input type="text" name="unlock_at" id="qpd-edit-unlock-at" class="form-control"
                     placeholder="Select date &amp; time" autocomplete="off" data-input>
              <span class="input-group-addon" data-toggle style="cursor:pointer;">
                <i class="fa fa-calendar"></i>
              </span>
            </div>
            <p class="help-block" style="font-size:11px;">Set to a past time to unlock immediately.</p>
          </div>
        </div>
        <div class="modal-footer" style="padding:8px 15px;">
          <button type="submit" class="btn btn-warning btn-sm">
            <i class="fa fa-save"></i> Save
          </button>
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
            <i class="fa fa-times"></i> Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- QPD PDF Preview Modal -->
<div class="modal fade" id="qpdPreviewModal" tabindex="-1" role="dialog" aria-labelledby="qpdPreviewTitle">
  <div class="modal-dialog" style="width:90%;max-width:1100px;" role="document">
    <div class="modal-content">
      <div class="modal-header" style="padding:10px 15px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        <h4 class="modal-title" id="qpdPreviewTitle">
          <i class="fa fa-file-pdf-o text-red"></i> <span id="qpd-modal-title">Question Paper Preview</span>
        </h4>
      </div>
      <div class="modal-body" style="padding:0;">
        <iframe id="qpd-preview-frame"
                src="about:blank"
                style="width:100%;height:78vh;border:none;display:block;"
                allowfullscreen>
        </iframe>
      </div>
      <div class="modal-footer" style="padding:8px 15px;">
        <a id="qpd-preview-download-btn" href="#" class="btn btn-primary btn-sm">
          <i class="fa fa-download"></i> Download
        </a>
        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
          <i class="fa fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  // Preview modal
  $(document).on('click', '.qpd-preview-btn', function(){
    var url   = $(this).data('url');
    var title = $(this).data('title');
    $('#qpd-modal-title').text(title);
    $('#qpd-preview-frame').attr('src', url);
    $('#qpd-preview-download-btn').attr('href', url.replace('/preview/', '/download/'));
    $('#qpdPreviewModal').modal('show');
  });
  // Clear iframe on close
  $('#qpdPreviewModal').on('hidden.bs.modal', function(){
    $('#qpd-preview-frame').attr('src', 'about:blank');
  });
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'qpd']); ?>
