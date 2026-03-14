<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-plus-square-o"></i> Create GRN</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Goods Receipt Note</h3>
            </div>
            <form method="post" action="<?php echo site_url('admin/inventoryprocurement/storegrn'); ?>">
                <div class="box-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>GRN Date <span class="text-danger">*</span></label>
                                <input type="text" name="grn_date" class="form-control date" readonly value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Purchase Order <span class="text-danger">*</span></label>
                                <select name="po_id" id="po_id" class="form-control" required>
                                    <option value="">Select</option>
                                    <?php foreach ($po_rows as $row) { ?>
                                        <option value="<?php echo (int) $row['id']; ?>" <?php echo ((int) ($selected_po_id ?? 0) === (int) $row['id']) ? 'selected' : ''; ?>>
                                            <?php echo html_escape((string) $row['po_no']); ?>
                                            (<?php echo html_escape((string) $row['status']); ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Store</label>
                                <select name="store_id" class="form-control">
                                    <option value="">Select</option>
                                    <?php foreach ($stores as $row) { ?>
                                        <option value="<?php echo (int) $row['id']; ?>"><?php echo html_escape((string) $row['item_store']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Invoice Date</label>
                                <input type="text" name="invoice_date" class="form-control date" readonly>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h4>GRN Line Items</h4>
                    <p class="text-muted">Enter received, accepted and rejected quantities for each pending PO line.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="grn-lines-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>UOM</th>
                                    <th>Ordered</th>
                                    <th>Already Received</th>
                                    <th>Pending</th>
                                    <th>Received Now</th>
                                    <th>Accepted</th>
                                    <th>Rejected</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="grn-lines-body">
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Select a PO to load pending lines.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary pull-right">Create GRN</button>
                    <a href="<?php echo site_url('admin/inventoryprocurement/goodsreceipts'); ?>" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>
    </section>
</div>

<script type="text/javascript">
    (function() {
        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderRows(rows) {
            var tbody = $('#grn-lines-body');
            if (!rows || !rows.length) {
                tbody.html('<tr><td colspan="9" class="text-center text-muted">No pending lines to receive for selected PO.</td></tr>');
                return;
            }

            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var r = rows[i];
                var pending = parseFloat(r.remaining_qty || 0);
                html += '<tr>' +
                    '<td>' + escapeHtml(r.item_name) + '<input type="hidden" name="po_item_id[]" value="' + parseInt(r.po_item_id, 10) + '"></td>' +
                    '<td>' + escapeHtml(r.uom) + '</td>' +
                    '<td>' + escapeHtml(r.ordered_qty) + '</td>' +
                    '<td>' + escapeHtml(r.already_received_qty) + '</td>' +
                    '<td>' + escapeHtml(r.remaining_qty) + '</td>' +
                    '<td><input type="number" step="0.01" min="0" max="' + pending + '" name="received_qty[]" class="form-control input-sm js-received" value="' + pending + '"></td>' +
                    '<td><input type="number" step="0.01" min="0" max="' + pending + '" name="accepted_qty[]" class="form-control input-sm js-accepted" value="' + pending + '"></td>' +
                    '<td><input type="number" step="0.01" min="0" max="' + pending + '" name="rejected_qty[]" class="form-control input-sm js-rejected" value="0"></td>' +
                    '<td><input type="text" name="line_remarks[]" class="form-control input-sm" placeholder="Line remarks"></td>' +
                '</tr>';
            }
            tbody.html(html);
        }

        function loadPoLines(poId) {
            if (!poId) {
                $('#grn-lines-body').html('<tr><td colspan="9" class="text-center text-muted">Select a PO to load pending lines.</td></tr>');
                return;
            }

            $('#grn-lines-body').html('<tr><td colspan="9" class="text-center text-muted">Loading...</td></tr>');

            $.getJSON('<?php echo site_url('admin/inventoryprocurement/poitems'); ?>/' + poId, function(response) {
                if (response && response.status === 'success') {
                    renderRows(response.rows || []);
                } else {
                    $('#grn-lines-body').html('<tr><td colspan="9" class="text-center text-danger">Unable to load PO lines.</td></tr>');
                }
            }).fail(function() {
                $('#grn-lines-body').html('<tr><td colspan="9" class="text-center text-danger">Unable to load PO lines.</td></tr>');
            });
        }

        $(document).on('input', '.js-received', function() {
            var row = $(this).closest('tr');
            var received = parseFloat($(this).val() || 0);
            row.find('.js-accepted').val(received.toFixed(2));
            row.find('.js-rejected').val('0');
        });

        $('#po_id').on('change', function() {
            loadPoLines($(this).val());
        });

        var initialPo = $('#po_id').val();
        if (initialPo) {
            loadPoLines(initialPo);
        }
    })();
</script>
