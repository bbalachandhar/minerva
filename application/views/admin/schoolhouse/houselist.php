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
.crud-table .item-desc { color: #6c757d; font-size: 13px; }
.crud-table .item-id { display: inline-block; background: #f0f2ff; color: #5b73e8; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
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
                    <span class="header-icon"><i class="fa fa-home"></i></span>
                    <?php echo $this->lang->line('student_house_list'); ?>
                    <span class="badge-count"><?php echo count($houselist); ?></span>
                </h2>
                <?php if (($this->rbac->hasPrivilege('student_houses', 'can_add')) || ($this->rbac->hasPrivilege('student_houses', 'can_edit'))): ?>
                <button class="btn-add-new" onclick="openAddModal()"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_school_house'); ?></button>
                <?php endif; ?>
            </div>

            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
            <?php if ($this->session->flashdata('msgdelete')) { echo $this->session->flashdata('msgdelete'); } ?>

            <div class="crud-panel">
                <?php if (empty($houselist)): ?>
                <div class="empty-state"><i class="fa fa-home"></i><p><?php echo $this->lang->line('no_record_found'); ?></p></div>
                <?php else: ?>
                <table class="crud-table">
                    <thead><tr><th style="width:50px">#</th><th><?php echo $this->lang->line('name'); ?></th><th><?php echo $this->lang->line('description'); ?></th><th><?php echo $this->lang->line('house_id'); ?></th><th style="width:100px;text-align:center"><?php echo $this->lang->line('action'); ?></th></tr></thead>
                    <tbody>
                        <?php $count = 1; foreach ($houselist as $house): ?>
                        <tr>
                            <td style="color:#adb5bd;font-size:12px;"><?php echo $count++; ?></td>
                            <td class="item-name"><?php echo $house['house_name']; ?></td>
                            <td class="item-desc"><?php echo $house['description']; ?></td>
                            <td><span class="item-id">#<?php echo $house['id']; ?></span></td>
                            <td style="text-align:center">
                                <?php if ($this->rbac->hasPrivilege('student_houses', 'can_edit')): ?>
                                <a href="javascript:void(0)" class="btn-action btn-action-edit" data-id="<?php echo $house['id']; ?>" data-name="<?php echo htmlspecialchars($house['house_name'], ENT_QUOTES); ?>" data-desc="<?php echo htmlspecialchars($house['description'], ENT_QUOTES); ?>" onclick="openEditModal(this)" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                <?php endif; ?>
                                <?php if ($this->rbac->hasPrivilege('student_houses', 'can_delete')): ?>
                                <a href="<?php echo base_url(); ?>admin/schoolhouse/delete/<?php echo $house['id']; ?>" class="btn-action btn-action-delete" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');"><i class="fa fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<div class="crud-modal-overlay" id="crudModal">
    <div class="crud-modal">
        <div class="crud-modal-header">
            <h3 id="modalTitle"><i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_school_house'); ?></h3>
            <button class="crud-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="crudForm" action="<?php echo base_url(); ?>admin/schoolhouse/create" method="post">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="crud-modal-body">
                <div style="margin-bottom:14px;">
                    <label><?php echo $this->lang->line('name'); ?> <span class="req">*</span></label>
                    <input type="text" name="house_name" id="modalInputName" class="crud-input" value="">
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('house_name'); ?></span>
                </div>
                <div>
                    <label><?php echo $this->lang->line('description'); ?></label>
                    <textarea name="description" id="modalInputDesc" class="crud-input" style="min-height:80px;resize:vertical;"></textarea>
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
.crud-modal-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.4); z-index:99999; backdrop-filter:blur(2px); justify-content:center; align-items:center; }
.crud-modal-overlay.show { display:flex; }
.crud-modal { background:#fff; border-radius:12px; width:460px; max-width:92vw; box-shadow:0 20px 60px rgba(0,0,0,.15); transform:translateY(20px); opacity:0; transition:transform .25s ease, opacity .25s ease; }
.crud-modal-overlay.show .crud-modal { transform:translateY(0); opacity:1; }
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
    $('#modalTitle').html('<i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_school_house'); ?>');
    $('#crudForm').attr('action', '<?php echo base_url(); ?>admin/schoolhouse/create');
    $('#modalInputName').val(''); $('#modalInputDesc').val('');
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalInputName').focus(); }, 200);
}
function openEditModal(el) {
    var id = $(el).data('id'), name = $(el).data('name'), desc = $(el).data('desc') || '';
    $('#modalTitle').html('<i class="fa fa-pencil" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('edit_school_house'); ?>');
    $('#crudForm').attr('action', '<?php echo base_url(); ?>admin/schoolhouse/edit/' + id);
    $('#modalInputName').val(name); $('#modalInputDesc').val(desc);
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalInputName').focus(); }, 200);
}
function closeModal() { $('#crudModal').removeClass('show'); }
$(document).on('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
<?php if (!empty($house_name) || form_error('house_name')): ?>
$(function(){ openEditModal({dataset:{id:'<?php echo $id ?? ''; ?>',name:'<?php echo htmlspecialchars($house_name ?? '', ENT_QUOTES); ?>',desc:'<?php echo htmlspecialchars($description ?? '', ENT_QUOTES); ?>'}}); });
<?php endif; ?>
</script>
