<style>
.crud-page { padding: 0; }
.crud-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.crud-header h2 { margin: 0; font-size: 22px; font-weight: 700; color: #2c3e50; display: flex; align-items: center; gap: 10px; }
.crud-header h2 .header-icon { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #5b73e8, #7c5ce7); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; }
.crud-header .badge-count { background: #e8ebf7; color: #5b73e8; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-left: 8px; }
.btn-add-new { background: linear-gradient(135deg, #5b73e8, #7c5ce7); color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .2s; text-decoration: none; }
.btn-add-new:hover { opacity: .9; color: #fff; text-decoration: none; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(91,115,232,.3); }
.crud-panel { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.06); overflow: hidden; }
.crud-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.crud-table thead th { background: #f8f9fb; padding: 12px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #8492a6; border-bottom: 2px solid #eef0f3; }
.crud-table tbody tr { transition: background .15s; }
.crud-table tbody tr:hover { background: #f8f9ff; }
.crud-table tbody td { padding: 14px 16px; font-size: 14px; color: #333; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.crud-table .item-name { font-weight: 600; color: #2c3e50; }
.badge-yes { display: inline-block; background: #d4edda; color: #155724; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-no { display: inline-block; background: #f8d7da; color: #721c24; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.btn-action { width: 32px; height: 32px; border-radius: 6px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all .2s; font-size: 13px; text-decoration: none; }
.btn-action-edit { background: #e8f4fd; color: #3498db; }
.btn-action-edit:hover { background: #3498db; color: #fff; }
.btn-action-delete { background: #fde8e8; color: #e74c3c; }
.btn-action-delete:hover { background: #e74c3c; color: #fff; }
.empty-state { text-align: center; padding: 48px 20px; color: #adb5bd; }
.empty-state i { font-size: 40px; display: block; margin-bottom: 8px; }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="crud-page">
            <div class="crud-header">
                <h2>
                    <span class="header-icon"><i class="fa fa-ticket"></i></span>
                    <?php echo $this->lang->line('incidental_fee_type_list'); ?>
                    <span class="badge-count"><?php echo count($incidental_fee_type_list); ?></span>
                </h2>
                <button class="btn-add-new" onclick="openAddModal()"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_incidental_fee_type'); ?></button>
            </div>

            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>

            <div class="crud-panel">
                <?php if (empty($incidental_fee_type_list)): ?>
                <div class="empty-state"><i class="fa fa-ticket"></i><p><?php echo $this->lang->line('no_record_found'); ?></p></div>
                <?php else: ?>
                <div style="padding:12px;"><table class="crud-table example">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th><?php echo $this->lang->line('title'); ?></th>
                            <th><?php echo $this->lang->line('description'); ?></th>
                            <th><?php echo $this->lang->line('default_amount'); ?></th>
                            <th><?php echo $this->lang->line('is_assignable'); ?></th>
                            <th style="width:100px;text-align:center"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; foreach ($incidental_fee_type_list as $item): ?>
                        <tr>
                            <td style="color:#adb5bd;font-size:12px;"><?php echo $count++; ?></td>
                            <td class="item-name"><?php echo $item['title']; ?></td>
                            <td><?php echo $item['description']; ?></td>
                            <td><?php echo $item['default_amount']; ?></td>
                            <td>
                                <?php if ($item['is_assignable'] == 1): ?>
                                    <span class="badge-yes"><?php echo $this->lang->line('yes'); ?></span>
                                <?php else: ?>
                                    <span class="badge-no"><?php echo $this->lang->line('no'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center">
                                <a href="javascript:void(0)" class="btn-action btn-action-edit"
                                   data-id="<?php echo $item['id']; ?>"
                                   data-title="<?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?>"
                                   data-description="<?php echo htmlspecialchars($item['description'], ENT_QUOTES); ?>"
                                   data-amount="<?php echo htmlspecialchars($item['default_amount'], ENT_QUOTES); ?>"
                                   data-assignable="<?php echo $item['is_assignable']; ?>"
                                   onclick="openEditModal(this)"
                                   data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="<?php echo base_url(); ?>admin/incidental_fee_type/delete/<?php echo $item['id']; ?>"
                                   class="btn-action btn-action-delete"
                                   data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>"
                                   onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<div class="crud-modal-overlay" id="crudModal">
    <div class="crud-modal">
        <div class="crud-modal-header">
            <h3 id="modalTitle"><i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_incidental_fee_type'); ?></h3>
            <button class="crud-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="crudForm" action="<?php echo site_url('admin/incidental_fee_type/index'); ?>" method="post">
            <?php echo $this->customlib->getCSRF(); ?>
            <input type="hidden" name="item_id" id="modalItemId" value="">
            <div class="crud-modal-body">
                <div class="form-group" style="margin-bottom:14px;">
                    <label><?php echo $this->lang->line('title'); ?> <span class="req">*</span></label>
                    <input type="text" name="title" id="modalTitle_input" class="crud-input" placeholder="<?php echo $this->lang->line('title'); ?>">
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('title'); ?></span>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><?php echo $this->lang->line('description'); ?></label>
                    <textarea name="description" id="modalDescription" class="crud-input" rows="3" placeholder="<?php echo $this->lang->line('description'); ?>" style="resize:vertical;min-height:60px;"></textarea>
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('description'); ?></span>
                </div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><?php echo $this->lang->line('default_amount'); ?></label>
                    <input type="text" name="default_amount" id="modalAmount" class="crud-input" placeholder="<?php echo $this->lang->line('default_amount'); ?>">
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('default_amount'); ?></span>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_assignable" id="modalAssignable" value="1" style="width:16px;height:16px;">
                        <?php echo $this->lang->line('is_assignable'); ?>
                    </label>
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('is_assignable'); ?></span>
                </div>
            </div>
            <div class="crud-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="submit" class="btn-modal-save"><i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<style>
.crud-modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); z-index:99999; }
.crud-modal-overlay.show { display:block; }
.crud-modal { background:#fff; border-radius:12px; width:460px; max-width:92vw; box-shadow:0 20px 60px rgba(0,0,0,.15); position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) scale(.95); opacity:0; transition:transform .25s ease, opacity .25s ease; z-index:100000; }
.crud-modal-overlay.show .crud-modal { transform:translate(-50%,-50%) scale(1); opacity:1; }
.crud-modal-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; }
.crud-modal-header h3 { margin:0; font-size:18px; font-weight:700; color:#2c3e50; }
.crud-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:#f5f5f5; cursor:pointer; font-size:16px; color:#666; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.crud-modal-close:hover { background:#e74c3c; color:#fff; }
.crud-modal-body { padding:20px 24px; }
.crud-modal-body label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
.crud-modal-body label .req { color:#e74c3c; }
.crud-input { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; }
.crud-input:focus { border-color:#5b73e8; outline:none; background:#fff; }
.crud-modal-footer { padding:0 24px 20px; display:flex; justify-content:flex-end; gap:10px; }
.btn-modal-cancel { background:#f5f5f5; color:#666; border:none; border-radius:8px; padding:10px 20px; font-size:14px; font-weight:500; cursor:pointer; transition:all .15s; }
.btn-modal-cancel:hover { background:#eee; }
.btn-modal-save { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:6px; }
.btn-modal-save:hover { opacity:.9; transform:translateY(-1px); }
</style>

<script>
$(function(){ $('#crudModal').appendTo('body'); });

function openAddModal() {
    $('#modalTitle').html('<i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_incidental_fee_type'); ?>');
    $('#crudForm').attr('action', '<?php echo site_url('admin/incidental_fee_type/index'); ?>');
    $('#modalItemId').val('');
    $('#modalTitle_input').val('');
    $('#modalDescription').val('');
    $('#modalAmount').val('');
    $('#modalAssignable').prop('checked', false);
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalTitle_input').focus(); }, 200);
}

function openEditModal(el) {
    var id = $(el).data('id');
    var title = $(el).data('title');
    var description = $(el).data('description');
    var amount = $(el).data('amount');
    var assignable = $(el).data('assignable');

    $('#modalTitle').html('<i class="fa fa-pencil" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('edit'); ?> <?php echo $this->lang->line('incidental_fee_type'); ?>');
    $('#crudForm').attr('action', '<?php echo base_url(); ?>admin/incidental_fee_type/edit/' + id);
    $('#modalItemId').val(id);
    $('#modalTitle_input').val(title);
    $('#modalDescription').val(description);
    $('#modalAmount').val(amount);
    $('#modalAssignable').prop('checked', assignable == 1);
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalTitle_input').focus(); }, 200);
}

function closeModal() { $('#crudModal').removeClass('show'); }
$(document).on('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

<?php if (form_error('title') || form_error('description') || form_error('default_amount') || form_error('is_assignable')): ?>
$(function(){ openAddModal(); });
<?php endif; ?>
</script>
