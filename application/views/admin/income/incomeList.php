<style>
.fc-page .fc-panel { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:20px; overflow:visible; }
.fc-panel-header { background:linear-gradient(135deg,#5b73e8 0%,#7c5ce7 100%); color:#fff; padding:14px 20px; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:space-between; }
.fc-panel-header h3 { margin:0; font-size:16px; font-weight:600; }
.fc-panel-header h3 i { margin-right:8px; }
.fc-results { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.fc-results-header { padding:16px 20px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; }
.fc-results-header h4 { margin:0; font-size:15px; font-weight:600; color:#2c3e50; }
.fc-results-header h4 i { margin-right:6px; color:#5b73e8; }
.btn-add-new { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 22px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all .2s; text-decoration:none; }
.btn-add-new:hover { opacity:.9; color:#fff; text-decoration:none; transform:translateY(-1px); }

.crud-modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.4); z-index:99999; }
.crud-modal-overlay.show { display:block; }
.crud-modal { background:#fff; border-radius:12px; width:500px; max-width:92vw; box-shadow:0 20px 60px rgba(0,0,0,.15); position:fixed; top:50%; left:50%; transform:translate(-50%,-50%) scale(.95); opacity:0; transition:transform .25s ease, opacity .25s ease; z-index:100000; }
.crud-modal-overlay.show .crud-modal { transform:translate(-50%,-50%) scale(1); opacity:1; }
.crud-modal-header { padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between; }
.crud-modal-header h3 { margin:0; font-size:18px; font-weight:700; color:#2c3e50; }
.crud-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:#f5f5f5; cursor:pointer; font-size:16px; color:#666; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.crud-modal-close:hover { background:#e74c3c; color:#fff; }
.crud-modal-body { padding:20px 24px; }
.crud-modal-body .form-grp { margin-bottom:14px; }
.crud-modal-body label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
.crud-modal-body label .req { color:#e74c3c; }
.crud-input, .crud-select { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; }
.crud-textarea { width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; padding:10px 14px; font-size:14px; color:#333; transition:border-color .2s; box-sizing:border-box; resize:vertical; min-height:80px; }
.crud-input:focus, .crud-select:focus, .crud-textarea:focus { border-color:#5b73e8; outline:none; background:#fff; }
.crud-modal-footer { padding:0 24px 20px; display:flex; justify-content:flex-end; gap:10px; }
.btn-modal-cancel { background:#f5f5f5; color:#666; border:none; border-radius:8px; padding:10px 20px; font-size:14px; font-weight:500; cursor:pointer; }
.btn-modal-cancel:hover { background:#eee; }
.btn-modal-save { background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none; border-radius:8px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.btn-modal-save:hover { opacity:.9; transform:translateY(-1px); }
</style>

<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>

<div class="content-wrapper fc-page">
    <section class="content">

        <?php if ($this->session->flashdata('msg')) { ?>
            <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
        <?php } ?>
        <?php if ($this->session->flashdata('error')) { ?>
            <?php echo "<div class='alert alert-danger text-left'>" . $this->session->flashdata('error') . "</div>"; $this->session->unset_userdata('error'); ?>
        <?php } ?>

        <div class="fc-results">
            <div class="fc-results-header">
                <h4><i class="fa fa-money"></i> <?php echo $this->lang->line('income_list'); ?></h4>
                <?php if ($this->rbac->hasPrivilege('income', 'can_add')) { ?>
                    <button type="button" class="btn-add-new" onclick="openAddModal()">
                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_income'); ?>
                    </button>
                <?php } ?>
            </div>
            <div style="padding:16px 20px;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover income-list" data-export-title="<?php echo $this->lang->line('income_list'); ?>">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('name'); ?></th>
                                <th width="20%"><?php echo $this->lang->line('description'); ?></th>
                                <th class="white-space-nowrap"><?php echo $this->lang->line('invoice_number'); ?></th>
                                <th class="white-space-nowrap"><?php echo $this->lang->line('date'); ?></th>
                                <th class="white-space-nowrap"><?php echo $this->lang->line('income_head'); ?></th>
                                <th class="white-space-nowrap text-right"><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>)</th>
                                <th class="pull-right noExport"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>
</div>

<!-- Add Income Modal -->
<div id="crudModal" class="crud-modal-overlay" onclick="if(event.target===this)closeModal();">
    <div class="crud-modal">
        <div class="crud-modal-header">
            <h3><?php echo $this->lang->line('add_income'); ?></h3>
            <button type="button" class="crud-modal-close" onclick="closeModal();">&times;</button>
        </div>
        <form id="form1" action="<?php echo base_url(); ?>admin/income" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            <div class="crud-modal-body">
                <?php if (isset($error_message)) {
                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                } ?>
                <?php echo $this->customlib->getCSRF(); ?>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('income_head'); ?> <span class="req">*</span></label>
                    <select id="inc_head_id" name="inc_head_id" class="crud-select" required>
                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                        <?php foreach ($incheadlist as $inchead) { ?>
                            <option value="<?php echo $inchead['id']; ?>" <?php if (set_value('inc_head_id') == $inchead['id']) echo 'selected="selected"'; ?>><?php echo $inchead['income_category']; ?></option>
                        <?php } ?>
                    </select>
                    <span class="text-danger"><?php echo form_error('inc_head_id'); ?></span>
                </div>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('name'); ?> <span class="req">*</span></label>
                    <input type="text" id="name" name="name" class="crud-input" value="<?php echo set_value('name'); ?>" required />
                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                </div>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('invoice_number'); ?></label>
                    <input type="text" id="invoice_no" name="invoice_no" class="crud-input" value="<?php echo set_value('invoice_no'); ?>" />
                    <span class="text-danger"><?php echo form_error('invoice_no'); ?></span>
                </div>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('date'); ?> <span class="req">*</span></label>
                    <input type="text" id="date" name="date" class="crud-input date" value="<?php echo set_value('date'); ?>" readonly="readonly" required />
                    <span class="text-danger"><?php echo form_error('date'); ?></span>
                </div>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('amount'); ?> (<?php echo $currency_symbol; ?>) <span class="req">*</span></label>
                    <input type="number" id="amount" name="amount" class="crud-input" value="<?php echo set_value('amount'); ?>" required />
                    <span class="text-danger"><?php echo form_error('amount'); ?></span>
                </div>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('attach_document'); ?></label>
                    <input type="file" id="documents" name="documents" class="crud-input filestyle" />
                    <span class="text-danger"><?php echo form_error('documents'); ?></span>
                </div>

                <div class="form-grp">
                    <label><?php echo $this->lang->line('description'); ?></label>
                    <textarea id="description" name="description" class="crud-textarea" rows="3"><?php echo set_value('description'); ?></textarea>
                </div>
            </div>
            <div class="crud-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal();"><?php echo $this->lang->line('cancel'); ?></button>
                <button type="submit" class="btn-modal-save" id="submitbtn"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
(function($) {
    'use strict';
    $(document).ready(function() {
        initDatatable('income-list','admin/income/getincomelist',[],[],100,
            [
                { "bSortable": true, "aTargets": [ -2 ] ,'sClass': 'dt-body-right'},
                { "bSortable": false, "aTargets": [ -1 ] ,'sClass': 'dt-body-right'}
            ]);
    });
}(jQuery));

$(function() {
    $('#crudModal').appendTo('body');

    $('#form1').submit(function() {
        $("#submitbtn").button('loading');
    });

    <?php if (form_error('inc_head_id') || form_error('name') || form_error('date') || form_error('amount') || isset($error_message)) { ?>
        openAddModal();
    <?php } ?>
});

function openAddModal() {
    document.getElementById('form1').reset();
    <?php if (set_value('inc_head_id')) { ?>
        $('#inc_head_id').val('<?php echo set_value('inc_head_id'); ?>');
    <?php } ?>
    <?php if (set_value('name')) { ?>
        $('#name').val('<?php echo set_value('name'); ?>');
    <?php } ?>
    <?php if (set_value('invoice_no')) { ?>
        $('#invoice_no').val('<?php echo set_value('invoice_no'); ?>');
    <?php } ?>
    <?php if (set_value('date')) { ?>
        $('#date').val('<?php echo set_value('date'); ?>');
    <?php } ?>
    <?php if (set_value('amount')) { ?>
        $('#amount').val('<?php echo set_value('amount'); ?>');
    <?php } ?>
    <?php if (set_value('description')) { ?>
        $('#description').val('<?php echo set_value('description'); ?>');
    <?php } ?>
    $('#crudModal').addClass('show');
}

function closeModal() {
    $('#crudModal').removeClass('show');
}

$(document).on('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
