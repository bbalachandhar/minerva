<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
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
.btn-action-assign { background: #e8fdf0; color: #27ae60; }
.btn-action-assign:hover { background: #27ae60; color: #fff; }
.empty-state { text-align: center; padding: 48px 20px; color: #adb5bd; }
.empty-state i { font-size: 40px; display: block; margin-bottom: 8px; }
.discount-code-pill { display: inline-block; background: #f0f2ff; color: #5b73e8; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="crud-page">
            <div class="crud-header">
                <h2>
                    <span class="header-icon"><i class="fa fa-percent"></i></span>
                    <?php echo $this->lang->line('fees_discount_list'); ?>
                    <span class="badge-count"><?php echo count($feediscountList); ?></span>
                </h2>
                <?php if ($this->rbac->hasPrivilege('fees_discount', 'can_add')): ?>
                <button class="btn-add-new" onclick="openAddModal()"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_fees_discount'); ?></button>
                <?php endif; ?>
            </div>

            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
            <?php if ($this->session->flashdata('msgdelete')) { echo $this->session->flashdata('msgdelete'); } ?>

            <div class="crud-panel">
                <?php if (empty($feediscountList)): ?>
                <div class="empty-state"><i class="fa fa-percent"></i><p><?php echo $this->lang->line('no_record_found'); ?></p></div>
                <?php else: ?>
                <div style="padding:12px;"><table class="crud-table example">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th><?php echo $this->lang->line('discount_code'); ?></th>
                            <th style="text-align:right"><?php echo $this->lang->line('percentage'); ?> (%)</th>
                            <th style="text-align:right"><?php echo $this->lang->line('amount') . ' (' . $currency_symbol . ')'; ?></th>
                            <th style="text-align:right"><?php echo $this->lang->line('number_of_use_count'); ?></th>
                            <th style="text-align:right"><?php echo $this->lang->line('expiry_date'); ?></th>
                            <th style="width:120px;text-align:center"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; foreach ($feediscountList as $feediscount): ?>
                        <tr>
                            <td style="color:#adb5bd;font-size:12px;"><?php echo $count++; ?></td>
                            <td>
                                <span class="item-name"><?php echo $feediscount['name']; ?></span>
                                <?php if (!empty($feediscount['description'])): ?>
                                <div class="item-desc"><?php echo $feediscount['description']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td><span class="discount-code-pill"><?php echo $feediscount['code']; ?></span></td>
                            <td style="text-align:right"><?php echo $feediscount['percentage']; ?></td>
                            <td style="text-align:right"><?php $amount = $feediscount['amount']; if ($amount > 0.00) { echo amountFormat($amount); } ?></td>
                            <td style="text-align:right"><?php echo $feediscount['discount_limit']; ?></td>
                            <td style="text-align:right"><?php echo $this->customlib->dateformat($feediscount['expire_date']); ?></td>
                            <td style="text-align:center;white-space:nowrap;">
                                <?php if ($this->rbac->hasPrivilege('fees_discount_assign', 'can_view')): ?>
                                <a href="<?php echo base_url(); ?>admin/feediscount/assign/<?php echo $feediscount['id']; ?>" class="btn-action btn-action-assign" data-toggle="tooltip" title="<?php echo $this->lang->line('assign_view_student'); ?>"><i class="fa fa-tag"></i></a>
                                <?php endif; ?>
                                <?php if ($this->rbac->hasPrivilege('fees_discount', 'can_edit')): ?>
                                <a href="javascript:void(0)" class="btn-action btn-action-edit"
                                   data-id="<?php echo $feediscount['id']; ?>"
                                   data-name="<?php echo htmlspecialchars($feediscount['name'], ENT_QUOTES); ?>"
                                   data-code="<?php echo htmlspecialchars($feediscount['code'], ENT_QUOTES); ?>"
                                   data-desc="<?php echo htmlspecialchars($feediscount['description'], ENT_QUOTES); ?>"
                                   data-type="<?php echo $feediscount['type']; ?>"
                                   data-percentage="<?php echo $feediscount['percentage']; ?>"
                                   data-amount="<?php echo $feediscount['amount']; ?>"
                                   data-limit="<?php echo $feediscount['discount_limit']; ?>"
                                   data-expire="<?php echo $this->customlib->dateformat($feediscount['expire_date']); ?>"
                                   onclick="openEditModal(this)" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                                <?php endif; ?>
                                <?php if ($this->rbac->hasPrivilege('fees_discount', 'can_delete')): ?>
                                <a href="<?php echo base_url(); ?>admin/feediscount/delete/<?php echo $feediscount['id']; ?>" class="btn-action btn-action-delete" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');"><i class="fa fa-trash"></i></a>
                                <?php endif; ?>
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
    <div class="crud-modal" style="width:520px;">
        <div class="crud-modal-header">
            <h3 id="modalTitle"><i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_fees_discount'); ?></h3>
            <button class="crud-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="crudForm" action="<?php echo site_url('admin/feediscount'); ?>" method="post">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="crud-modal-body">
                <div class="form-grp">
                    <label><?php echo $this->lang->line('name'); ?> <span class="req">*</span></label>
                    <input type="text" name="name" id="modalName" class="crud-input" value="">
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('name'); ?></span>
                </div>
                <div class="form-grp">
                    <label><?php echo $this->lang->line('discount_code'); ?> <span class="req">*</span></label>
                    <input type="text" name="code" id="modalCode" class="crud-input" value="">
                    <span class="text-danger" style="font-size:12px;"><?php echo form_error('code'); ?></span>
                </div>
                <div class="form-grp">
                    <label><?php echo $this->lang->line('discount_type'); ?></label>
                    <div style="display:flex;gap:20px;margin-top:6px;">
                        <label style="display:inline-flex;align-items:center;gap:6px;font-weight:400;cursor:pointer;">
                            <input type="radio" name="account_type" class="modal-finetype" value="percentage"> <?php echo $this->lang->line('percentage'); ?>
                        </label>
                        <label style="display:inline-flex;align-items:center;gap:6px;font-weight:400;cursor:pointer;">
                            <input type="radio" name="account_type" class="modal-finetype" value="fix" checked> <?php echo $this->lang->line('fix_amount'); ?>
                        </label>
                    </div>
                </div>
                <div style="display:flex;gap:12px;">
                    <div class="form-grp" id="grpPercentage" style="flex:1;display:none;">
                        <label><?php echo $this->lang->line('percentage'); ?> (%) <span class="req">*</span></label>
                        <input type="text" name="percentage" id="modalPercentage" class="crud-input" value="">
                        <span class="text-danger" style="font-size:12px;"><?php echo form_error('percentage'); ?></span>
                    </div>
                    <div class="form-grp" id="grpAmount" style="flex:1;">
                        <label><?php echo $this->lang->line('amount') . ' (' . $currency_symbol . ')'; ?> <span class="req">*</span></label>
                        <input type="text" name="amount" id="modalAmount" class="crud-input" value="">
                        <span class="text-danger" style="font-size:12px;"><?php echo form_error('amount'); ?></span>
                    </div>
                </div>
                <div style="display:flex;gap:12px;">
                    <div class="form-grp" style="flex:1;">
                        <label><?php echo $this->lang->line('number_of_use_count'); ?></label>
                        <input type="number" name="discount_limit" id="modalLimit" class="crud-input" min="0" step="1" value="">
                        <span class="text-danger" style="font-size:12px;"><?php echo form_error('discount_limit'); ?></span>
                    </div>
                    <div class="form-grp" style="flex:1;">
                        <label><?php echo $this->lang->line('expiry_date'); ?></label>
                        <input type="text" name="expire_date" id="modalExpire" class="crud-input modal-datepicker" value="">
                        <span class="text-danger" style="font-size:12px;"><?php echo form_error('expire_date'); ?></span>
                    </div>
                </div>
                <div class="form-grp">
                    <label><?php echo $this->lang->line('description'); ?></label>
                    <textarea name="description" id="modalDesc" class="crud-textarea"></textarea>
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
.crud-modal-body .form-grp { margin-bottom:14px; }
.crud-modal-body label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
.crud-modal-body label .req { color:#e74c3c; }
.crud-input { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; }
.crud-textarea { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; resize:vertical; min-height:80px; }
.crud-input:focus, .crud-textarea:focus { border-color:#5b73e8; outline:none; background:#fff; }
.crud-modal-footer { padding:0 24px 20px; display:flex; justify-content:flex-end; gap:10px; }
.btn-modal-cancel { background:#f5f5f5; color:#666; border:none; border-radius:8px; padding:10px 20px; font-size:14px; font-weight:500; cursor:pointer; transition:all .15s; }
.btn-modal-cancel:hover { background:#eee; }
.btn-modal-save { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all .2s; }
.btn-modal-save:hover { opacity:.9; transform:translateY(-1px); }
</style>

<script>
$(function(){
    $('#crudModal').appendTo('body');
    $('.modal-datepicker').datepicker({
        format: '<?php echo strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']); ?>',
        autoclose: true
    });
});

function toggleDiscountFields() {
    var type = $('input[name=account_type]:checked', '#crudForm').val();
    if (type === 'percentage') {
        $('#grpPercentage').show();
        $('#grpAmount').hide();
        $('#modalAmount').val('');
    } else {
        $('#grpPercentage').hide();
        $('#grpAmount').show();
        $('#modalPercentage').val('');
    }
}

function openAddModal() {
    $('#modalTitle').html('<i class="fa fa-plus-circle" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('add_fees_discount'); ?>');
    $('#crudForm').attr('action', '<?php echo site_url('admin/feediscount'); ?>');
    $('#modalName').val('');
    $('#modalCode').val('');
    $('#modalPercentage').val('');
    $('#modalAmount').val('');
    $('#modalLimit').val('');
    $('#modalExpire').val('');
    $('#modalDesc').val('');
    $('input[name=account_type][value=fix]').prop('checked', true);
    toggleDiscountFields();
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalName').focus(); }, 200);
}

function openEditModal(el) {
    var $el = $(el);
    var id = $el.data('id');
    var type = $el.data('type') || 'fix';
    $('#modalTitle').html('<i class="fa fa-pencil" style="color:#5b73e8;margin-right:6px;"></i> <?php echo $this->lang->line('edit'); ?>');
    $('#crudForm').attr('action', '<?php echo base_url(); ?>admin/feediscount/edit/' + id);
    $('#modalName').val($el.data('name'));
    $('#modalCode').val($el.data('code'));
    $('#modalDesc').val($el.data('desc') || '');
    $('#modalPercentage').val($el.data('percentage') || '');
    $('#modalAmount').val($el.data('amount') || '');
    $('#modalLimit').val($el.data('limit') || '');
    $('#modalExpire').val($el.data('expire') || '');
    $('input[name=account_type][value=' + type + ']').prop('checked', true);
    toggleDiscountFields();
    $('#crudModal').addClass('show');
    setTimeout(function(){ $('#modalName').focus(); }, 200);
}

function closeModal() { $('#crudModal').removeClass('show'); }

$(document).on('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
$(document).on('change', '.modal-finetype', function() { toggleDiscountFields(); });

<?php if (form_error('name') || form_error('code') || form_error('percentage') || form_error('amount') || form_error('discount_limit')): ?>
$(function(){ openAddModal(); });
<?php endif; ?>
</script>
