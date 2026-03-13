<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-plug"></i> Enquiry Lead Gen Vendors</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-primary btn-sm" id="addLeadVendorBtn">
                                <i class="fa fa-plus"></i> Add Vendor
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" style="margin-bottom: 15px;">
                            Manage external lead vendors here. Each vendor gets a unique <code>vendor_code</code> and secret <code>api_key</code>.
                            API requests are accepted only for active vendors with valid credentials.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="leadVendorTable">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Vendor Name</th>
                                        <th>Vendor Code</th>
                                        <th style="width: 110px;">Status</th>
                                        <th>Last Used</th>
                                        <th>Created At</th>
                                        <th style="width: 220px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lead_vendors)) { ?>
                                        <?php $row_no = 1; ?>
                                        <?php foreach ($lead_vendors as $vendor) { ?>
                                            <tr
                                                data-id="<?php echo (int) $vendor['id']; ?>"
                                                data-vendor-name="<?php echo html_escape((string) $vendor['vendor_name']); ?>"
                                                data-vendor-code="<?php echo html_escape((string) $vendor['vendor_code']); ?>"
                                                data-is-active="<?php echo (int) $vendor['is_active']; ?>"
                                            >
                                                <td><?php echo $row_no++; ?></td>
                                                <td class="vendor-name-cell"><?php echo html_escape((string) $vendor['vendor_name']); ?></td>
                                                <td class="vendor-code-cell"><code><?php echo html_escape((string) $vendor['vendor_code']); ?></code></td>
                                                <td class="vendor-status-cell">
                                                    <?php if ((int) $vendor['is_active'] === 1) { ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php } else { ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo !empty($vendor['last_used_at']) ? html_escape((string) $vendor['last_used_at']) : '<span class="text-muted">Never</span>'; ?></td>
                                                <td><?php echo !empty($vendor['created_at']) ? html_escape((string) $vendor['created_at']) : '-'; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-default btn-xs editLeadVendorBtn" title="Edit">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>

                                                    <?php if ((int) $vendor['is_active'] === 1) { ?>
                                                        <button type="button" class="btn btn-warning btn-xs toggleLeadVendorBtn" data-next-active="0" title="Deactivate">
                                                            <i class="fa fa-ban"></i>
                                                        </button>
                                                    <?php } else { ?>
                                                        <button type="button" class="btn btn-info btn-xs toggleLeadVendorBtn" data-next-active="1" title="Activate">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    <?php } ?>

                                                    <button type="button" class="btn btn-danger btn-xs deleteLeadVendorBtn" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr class="no-data-row">
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fa fa-info-circle"></i> No vendors configured yet.
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="leadVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="leadVendorModalTitle">Add Lead Vendor</h4>
            </div>
            <div class="modal-body">
                <form id="leadVendorForm">
                    <input type="hidden" name="id" id="lead_vendor_id" value="0">

                    <div class="form-group">
                        <label for="lead_vendor_name">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="vendor_name" id="lead_vendor_name" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label for="lead_vendor_code">Vendor Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="vendor_code" id="lead_vendor_code" maxlength="50" required>
                        <p class="help-block">Used by API clients as <code>vendor_code</code>. Allowed: letters, numbers, <code>_</code>, <code>-</code>.</p>
                    </div>

                    <div class="form-group">
                        <label for="lead_vendor_api_key">API Key <span class="text-danger" id="apiKeyRequiredMark">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="api_key" id="lead_vendor_api_key" autocomplete="off" placeholder="Enter or autogenerate a key">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="generateApiKeyBtn" title="Autogenerate strong API key">
                                    <i class="fa fa-refresh"></i> Generate
                                </button>
                            </span>
                        </div>
                        <p class="help-block" id="apiKeyHelpText">Provide a secure key. It will be stored as hash only.</p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_active" id="lead_vendor_is_active" value="1" checked>
                            Active Vendor
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLeadVendorBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    'use strict';

    var saveUrl = '<?php echo site_url('schsettings/ajax_save_lead_vendor'); ?>';
    var toggleUrl = '<?php echo site_url('schsettings/ajax_toggle_lead_vendor'); ?>';
    var deleteUrl = '<?php echo site_url('schsettings/ajax_delete_lead_vendor'); ?>';

    function resetVendorForm() {
        $('#leadVendorForm')[0].reset();
        $('#lead_vendor_id').val(0);
        $('#leadVendorModalTitle').text('Add Lead Vendor');
        $('#apiKeyRequiredMark').show();
        $('#apiKeyHelpText').text('Provide a secure key. It will be stored as hash only.');
        $('#lead_vendor_is_active').prop('checked', true);
    }

    function openAddModal() {
        resetVendorForm();
        $('#leadVendorModal').modal('show');
    }

    function openEditModal($row) {
        resetVendorForm();

        var id = parseInt($row.data('id'), 10) || 0;
        var vendorName = $row.data('vendor-name') || '';
        var vendorCode = $row.data('vendor-code') || '';
        var isActive = parseInt($row.data('is-active'), 10) === 1;

        $('#lead_vendor_id').val(id);
        $('#lead_vendor_name').val(vendorName);
        $('#lead_vendor_code').val(vendorCode);
        $('#lead_vendor_is_active').prop('checked', isActive);
        $('#leadVendorModalTitle').text('Edit Lead Vendor');
        $('#apiKeyRequiredMark').hide();
        $('#apiKeyHelpText').text('Leave blank to keep existing key. Enter a value to rotate key.');

        $('#leadVendorModal').modal('show');
    }

    function sanitizeVendorCode(value) {
        return (value || '').toLowerCase().replace(/[^a-z0-9_-]/g, '');
    }

    $('#lead_vendor_code').on('input', function() {
        var cleaned = sanitizeVendorCode($(this).val());
        if ($(this).val() !== cleaned) {
            $(this).val(cleaned);
        }
    });

    $('#generateApiKeyBtn').on('click', function() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';
        var key = '';
        var array = new Uint8Array(48);
        window.crypto.getRandomValues(array);
        for (var i = 0; i < array.length; i++) {
            key += chars[array[i] % chars.length];
        }
        $('#lead_vendor_api_key').val(key);
    });

    $('#addLeadVendorBtn').on('click', function() {
        openAddModal();
    });

    $(document).on('click', '.editLeadVendorBtn', function() {
        openEditModal($(this).closest('tr'));
    });

    $('#saveLeadVendorBtn').on('click', function() {
        var $btn = $(this);
        var id = parseInt($('#lead_vendor_id').val(), 10) || 0;
        var vendorName = $.trim($('#lead_vendor_name').val());
        var vendorCode = sanitizeVendorCode($('#lead_vendor_code').val());
        var apiKey = $.trim($('#lead_vendor_api_key').val());

        $('#lead_vendor_code').val(vendorCode);

        if (vendorName === '') {
            errorMsg('Vendor name is required.');
            return;
        }

        if (vendorCode === '') {
            errorMsg('Vendor code is required.');
            return;
        }

        if (id === 0 && apiKey === '') {
            errorMsg('API key is required while creating a vendor.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: saveUrl,
            type: 'POST',
            dataType: 'json',
            data: $('#leadVendorForm').serialize(),
            success: function(response) {
                if (response && response.status === 'success') {
                    successMsg(response.message || 'Vendor saved successfully.');
                    $('#leadVendorModal').modal('hide');
                    setTimeout(function() { location.reload(); }, 400);
                } else {
                    errorMsg((response && response.message) ? response.message : 'Failed to save vendor.');
                }
            },
            error: function() {
                errorMsg('Failed to save vendor.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Save');
            }
        });
    });

    $(document).on('click', '.toggleLeadVendorBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var id = parseInt($row.data('id'), 10) || 0;
        var nextActive = parseInt($btn.data('next-active'), 10) === 1 ? 1 : 0;

        if (id <= 0) {
            errorMsg('Invalid vendor id.');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: toggleUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                id: id,
                is_active: nextActive
            },
            success: function(response) {
                if (response && response.status === 'success') {
                    successMsg(response.message || 'Vendor status updated.');
                    setTimeout(function() { location.reload(); }, 300);
                } else {
                    errorMsg((response && response.message) ? response.message : 'Failed to update vendor status.');
                }
            },
            error: function() {
                errorMsg('Failed to update vendor status.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.deleteLeadVendorBtn', function() {
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var id = parseInt($row.data('id'), 10) || 0;
        var vendorName = $row.data('vendor-name') || 'this vendor';

        if (id <= 0) {
            errorMsg('Invalid vendor id.');
            return;
        }

        if (!confirm('Delete vendor "' + vendorName + '"? This will stop API auth for this vendor immediately.')) {
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: deleteUrl,
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(response) {
                if (response && response.status === 'success') {
                    successMsg(response.message || 'Vendor deleted successfully.');
                    setTimeout(function() { location.reload(); }, 300);
                } else {
                    errorMsg((response && response.message) ? response.message : 'Failed to delete vendor.');
                }
            },
            error: function() {
                errorMsg('Failed to delete vendor.');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
})(jQuery);
</script>
