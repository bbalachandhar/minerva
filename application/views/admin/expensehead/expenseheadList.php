<style>
.crud-page { padding:0; }
.crud-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.crud-header h2 { margin:0; font-size:22px; font-weight:700; color:#2c3e50; display:flex; align-items:center; gap:10px; }
.crud-header h2 .header-icon { width:40px; height:40px; border-radius:10px; background:linear-gradient(135deg,#5b73e8,#7c5ce7); display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px; }
.btn-add-new { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all .2s; text-decoration:none; }
.btn-add-new:hover { opacity:.9; color:#fff; text-decoration:none; transform:translateY(-1px); box-shadow:0 4px 12px rgba(91,115,232,.3); }
.crud-panel { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.crud-modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); z-index:99999; }
.crud-modal-overlay.show { display:block; }
.crud-modal { background:#fff; border-radius:12px; width:460px; max-width:92vw; box-shadow:0 20px 60px rgba(0,0,0,.15); position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) scale(.95); opacity:0; transition:transform .25s ease, opacity .25s ease; z-index:100000; }
.crud-modal-overlay.show .crud-modal { transform:translate(-50%,-50%) scale(1); opacity:1; }
.crud-modal-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; }
.crud-modal-header h3 { margin:0; font-size:18px; font-weight:700; color:#2c3e50; }
.crud-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:#f5f5f5; cursor:pointer; font-size:16px; color:#666; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.crud-modal-close:hover { background:#e74c3c; color:#fff; }
.crud-modal-body { padding:20px 24px; }
.crud-modal-body .form-grp { margin-bottom:14px; }
.crud-modal-body label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
.crud-modal-body label .req { color:#e74c3c; }
.crud-input { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; }
.crud-textarea { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; resize:vertical; min-height:80px; }
.crud-input:focus, .crud-textarea:focus { border-color:#5b73e8; outline:none; background:#fff; }
.crud-modal-footer { padding:0 24px 20px; display:flex; justify-content:flex-end; gap:10px; }
.btn-modal-cancel { background:#f5f5f5; color:#666; border:none; border-radius:8px; padding:10px 20px; font-size:14px; font-weight:500; cursor:pointer; }
.btn-modal-cancel:hover { background:#eee; }
.btn-modal-save { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.btn-modal-save:hover { opacity:.9; transform:translateY(-1px); }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="crud-page">
            <div class="crud-header">
                <h2>
                    <span class="header-icon"><i class="fa fa-credit-card"></i></span>
                    <?php echo $this->lang->line('expense_head_list'); ?>
                </h2>
                <?php if ($this->rbac->hasPrivilege('expense_head', 'can_add')): ?>
                <button class="btn-add-new" onclick="openAddModal()"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_expense_head'); ?></button>
                <?php endif; ?>
            </div>

            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>

            <div class="crud-panel">
                <div style="padding:12px;">
                    <div class="download_label"><?php echo $this->lang->line('expense_head_list'); ?></div>
                    <table class="table table-striped table-bordered table-hover expense-head-list" data-export-title="<?php echo $this->lang->line('expense_head_list'); ?>">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('expense_head'); ?></th>
                                <th><?php echo $this->lang->line('description'); ?></th>
                                <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="crud-modal-overlay" id="crudModal">
    <div class="crud-modal">
        <div class="crud-modal-header">
            <h3 id="modalTitle"><i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_expense_head'); ?></h3>
            <button class="crud-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="crudForm" action="<?php echo site_url('admin/expensehead/create'); ?>" method="post">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="crud-modal-body">
                <div class="form-grp">
                    <label><?php echo $this->lang->line('expense_head'); ?> <span class="req">*</span></label>
                    <input type="text" name="expensehead" id="modalInputName" class="crud-input" value="<?php echo set_value('expensehead'); ?>">
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('expensehead'); ?></span>
                </div>
                <div class="form-grp">
                    <label><?php echo $this->lang->line('description'); ?></label>
                    <textarea name="description" id="modalInputDesc" class="crud-textarea"><?php echo set_value('description'); ?></textarea>
                </div>
            </div>
            <div class="crud-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="submit" class="btn-modal-save"><i class="fa fa-check"></i> <?php echo $this->lang->line('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
$(function(){
    $('#crudModal').appendTo('body');
    initDatatable('expense-head-list','admin/expensehead/ajaxSearch',[],[],100,[{ "bSortable": false, "aTargets": [-1], 'sClass': 'dt-body-right'}]);
});
function openAddModal() {
    $('#modalTitle').html('<i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_expense_head'); ?>');
    $('#crudForm').attr('action', '<?php echo site_url('admin/expensehead/create'); ?>');
    $('#modalInputName').val(''); $('#modalInputDesc').val('');
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalInputName').focus(); }, 200);
}
function closeModal() { $('#crudModal').removeClass('show'); }
$(document).on('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
<?php if (form_error('expensehead')): ?>$(function(){ openAddModal(); });<?php endif; ?>
</script>
