<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.fee-search-panel {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    margin-bottom: 20px;
    overflow: hidden;
}
.fee-search-panel .panel-heading {
    background: linear-gradient(135deg, #5b73e8 0%, #7c5ce7 100%);
    color: #fff;
    padding: 16px 20px;
    font-size: 16px;
    font-weight: 600;
    border: none;
}
.fee-search-panel .panel-heading i { margin-right: 8px; }
.fee-search-panel .panel-body { padding: 20px 20px 10px; overflow: visible; }
.fee-search-panel { overflow: visible; }

.fee-filter-label {
    font-weight: 600;
    font-size: 13px;
    color: #495057;
    margin-bottom: 6px;
    display: block;
}
.fee-filter-label .req { color: #e74c3c; }

.fee-dropdown-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px 14px;
    cursor: pointer;
    font-size: 14px;
    color: #495057;
    transition: border-color .2s;
    min-height: 38px;
}
.fee-dropdown-trigger:hover, .fee-dropdown-trigger.active { border-color: #5b73e8; }
.fee-dropdown-trigger .arrow { font-size: 10px; color: #adb5bd; }

.fee-dropdown-menu {
    display: none;
    position: absolute;
    z-index: 9999;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    max-height: 320px;
    overflow-y: auto;
    width: 100%;
    margin-top: 4px;
    padding: 6px 0;
}
.fee-dropdown-menu.show { display: block; }

.fee-dropdown-menu .dd-group-label {
    padding: 8px 16px 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #5b73e8;
    letter-spacing: .5px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}
.fee-dropdown-menu .dd-item {
    padding: 6px 16px;
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fee-dropdown-menu .dd-item:hover { background: #f0f2ff; }
.fee-dropdown-menu .dd-item input[type=checkbox] {
    accent-color: #5b73e8;
    width: 16px;
    height: 16px;
    margin: 0;
}
.fee-dropdown-menu .dd-item label {
    margin: 0;
    font-weight: 400;
    font-size: 13px;
    cursor: pointer;
    color: #333;
}
.fee-dropdown-menu .dd-select-all {
    padding: 8px 16px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fee-dropdown-menu .dd-select-all label { font-weight: 600; font-size: 13px; margin: 0; cursor: pointer; color: #333; }

.fee-select-control {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px 14px;
    font-size: 14px;
    color: #495057;
    height: 38px;
    width: 100%;
    transition: border-color .2s;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 32px;
}
.fee-select-control:focus { border-color: #5b73e8; outline: none; }

.fee-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #495057;
    cursor: pointer;
    padding: 4px 0;
}
.fee-checkbox-label input[type=checkbox] {
    accent-color: #5b73e8;
    width: 16px;
    height: 16px;
}

.btn-fee-search {
    background: linear-gradient(135deg, #5b73e8 0%, #7c5ce7 100%);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 9px 28px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .2s, transform .15s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-fee-search:hover { opacity: .9; color: #fff; transform: translateY(-1px); }
.btn-fee-search:active { transform: translateY(0); }
.btn-fee-search:disabled { opacity: .6; cursor: not-allowed; }

/* Summary cards */
.fee-summary-strip {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.fee-summary-card {
    flex: 1;
    min-width: 130px;
    background: #fff;
    border-radius: 8px;
    padding: 14px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    border-left: 4px solid #5b73e8;
    text-align: center;
}
.fee-summary-card.paid { border-left-color: #27ae60; }
.fee-summary-card.discount { border-left-color: #f39c12; }
.fee-summary-card.fine { border-left-color: #e67e22; }
.fee-summary-card.balance { border-left-color: #e74c3c; }
.fee-summary-card .sc-value {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.2;
}
.fee-summary-card .sc-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #95a5a6;
    margin-top: 2px;
}

/* Results panel */
.fee-results-panel {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
    overflow: hidden;
}
.fee-results-panel .results-heading {
    padding: 16px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.fee-results-panel .results-heading h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
}
.fee-results-panel .results-heading .badge-count {
    background: #5b73e8;
    color: #fff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.fee-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.fee-table thead th {
    background: #f8f9fb;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: #6c757d;
    border-bottom: 2px solid #eee;
    white-space: nowrap;
}
.fee-table tbody tr { transition: background .15s; }
.fee-table tbody tr:hover { background: #f8f9ff; }
.fee-table tbody td {
    padding: 12px 14px;
    font-size: 13px;
    color: #333;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.fee-table .text-right { text-align: right; }
.fee-table .text-center { text-align: center; }

.student-name-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.student-avatar-sm {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #e8ebf7;
    color: #5b73e8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.student-name-text { font-weight: 500; color: #2c3e50; }
.student-meta { font-size: 11px; color: #95a5a6; }

.class-badge {
    background: #e8ebf7;
    color: #5b73e8;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.category-badge {
    background: #fef3e2;
    color: #e67e22;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.amount-cell { font-weight: 600; font-variant-numeric: tabular-nums; }
.amount-cell.balance-due { color: #e74c3c; }
.amount-cell.fully-paid { color: #27ae60; }

.fee-tags { display: flex; flex-wrap: wrap; gap: 4px; max-width: 260px; }
.fee-tag {
    background: #f0f2ff;
    color: #5b73e8;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    white-space: nowrap;
}

.btn-collect-fee {
    background: #27ae60;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
}
.btn-collect-fee:hover { background: #219653; color: #fff; text-decoration: none; transform: translateY(-1px); }

.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #95a5a6;
}
.empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: #ddd; }
.empty-state p { font-size: 14px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-search"></i> <?php echo $this->lang->line('fees_collection'); ?> &mdash; <?php echo $this->lang->line('search_due_fees'); ?></h1>
    </section>
    <section class="content">

        <!-- Search Panel -->
        <div class="fee-search-panel">
            <div class="panel-heading">
                <i class="fa fa-filter"></i> <?php echo $this->lang->line('select_criteria'); ?>
            </div>
            <div class="panel-body">
                <form class="studentsearchfee" action="<?php echo site_url('studentfee/feesearch') ?>" method="post" accept-charset="utf-8">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <?php echo validation_errors('<div class="alert alert-danger" style="border-radius:6px;font-size:13px;margin-bottom:14px;"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>

                    <div class="row">
                        <!-- Fees Group Multi-Select -->
                        <div class="col-md-4">
                            <label class="fee-filter-label"><?php echo $this->lang->line('fees_group'); ?> <span class="req">*</span></label>
                            <div style="position:relative;" id="checkbox-dropdown-container">
                                <div class="fee-dropdown-trigger" id="custom-select">
                                    <span id="dd-label"><?php echo $this->lang->line('select'); ?></span>
                                    <span class="arrow"><i class="fa fa-chevron-down"></i></span>
                                </div>
                                <div class="fee-dropdown-menu" id="custom-select-option-box">
                                    <div class="dd-select-all">
                                        <input type="checkbox" id="select_all" name="select_all" <?php if(isset($select_all) && $select_all == 'on'){echo "checked"; } ?>>
                                        <label for="select_all"><?php echo $this->lang->line('select_all'); ?></label>
                                    </div>
                                    <?php foreach ($feesessiongrouplist as $feecategory): ?>
                                        <div class="dd-group-label"><?php echo $feecategory->group_name; ?></div>
                                        <?php if (!empty($feecategory->feetypes)):
                                            foreach ($feecategory->feetypes as $fee_value):
                                                if (!empty($fee_value->type)):
                                                    $cb_val = $feecategory->id . "-" . $fee_value->id;
                                        ?>
                                        <div class="dd-item">
                                            <input type="checkbox" id="fg_<?php echo $cb_val; ?>" value="<?php echo $cb_val; ?>" class="custom-select-option-checkbox" name="feegroup[]" <?php echo set_checkbox("feegroup[]", $cb_val); ?>>
                                            <label for="fg_<?php echo $cb_val; ?>">
                                                <?php echo ($feecategory->is_system) ? $this->lang->line($fee_value->type) . " (" . $this->lang->line($fee_value->code) . ")" : $fee_value->type . " (" . $fee_value->code . ")"; ?>
                                            </label>
                                        </div>
                                        <?php
                                                endif;
                                            endforeach;
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            </div>
                            <span class="text-danger" style="font-size:12px;"><?php echo form_error('feegroup[]'); ?></span>
                        </div>

                        <!-- Class -->
                        <div class="col-md-3">
                            <label class="fee-filter-label"><?php echo $this->lang->line('class'); ?></label>
                            <select id="class_id" name="class_id" class="fee-select-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                <?php foreach ($classlist as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php if (set_value('class_id') == $class['id']) echo "selected"; ?>><?php echo $class['class']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Section -->
                        <div class="col-md-3">
                            <label class="fee-filter-label"><?php echo $this->lang->line('section'); ?></label>
                            <select id="section_id" name="section_id" class="fee-select-control">
                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                            </select>
                        </div>

                        <!-- Search Button -->
                        <div class="col-md-2">
                            <label class="fee-filter-label">&nbsp;</label>
                            <button type="submit" id="search_filter" class="btn-fee-search" style="width:100%;">
                                <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="row" style="margin-top:8px;">
                        <div class="col-md-12">
                            <label class="fee-checkbox-label">
                                <input type="checkbox" name="no_fees_assigned" value="1" <?php echo set_checkbox('no_fees_assigned', '1'); ?>>
                                <?php echo $this->lang->line('search_students_without_fees'); ?>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <?php if (isset($student_list) || isset($student_remain_fees)): ?>

        <?php if (isset($student_list)): ?>
        <!-- Students Without Fees -->
        <div class="fee-results-panel">
            <div class="results-heading">
                <h4><i class="fa fa-users" style="margin-right:6px;color:#5b73e8;"></i> <?php echo $this->lang->line('student_list'); ?> &mdash; <?php echo $this->lang->line('search_students_without_fees'); ?></h4>
                <span class="badge-count"><?php echo count($student_list); ?></span>
            </div>
            <div style="padding:0 16px 16px; overflow-x:auto;">
                <?php if (empty($student_list)): ?>
                <div class="empty-state">
                    <i class="fa fa-check-circle" style="color:#27ae60;"></i>
                    <p><?php echo $this->lang->line('no_record_found'); ?></p>
                </div>
                <?php else: ?>
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('class'); ?></th>
                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                            <th><?php echo $this->lang->line('student_name'); ?></th>
                            <th><?php echo $this->lang->line('category'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($student_list as $student): ?>
                        <tr>
                            <td><span class="class-badge"><?php echo $student['class'] . " - " . $student['section']; ?></span></td>
                            <td><strong><?php echo $student['admission_no']; ?></strong></td>
                            <td><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></td>
                            <td><span class="category-badge"><?php echo $student['category']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Fee Search Results -->
        <?php
            $grand_amount = 0; $grand_paid = 0; $grand_discount = 0; $grand_fine = 0; $grand_balance = 0;
            $student_count = 0;
            if (!empty($student_remain_fees)) {
                foreach ($student_remain_fees as $s) {
                    $sa = 0; $sp = 0; $sd = 0; $sf = 0;
                    if (!empty($s['fees'])) {
                        foreach ($s['fees'] as $f) { $sa += $f['amount']; $sp += $f['amount_deposite']; $sd += $f['amount_discount']; $sf += $f['amount_fine']; }
                    }
                    $grand_amount += $sa; $grand_paid += $sp; $grand_discount += $sd; $grand_fine += $sf;
                    $grand_balance += ($sa - ($sp + $sd));
                    $student_count++;
                }
            }
        ?>

        <!-- Summary Strip -->
        <?php if (!empty($student_remain_fees)): ?>
        <div class="fee-summary-strip">
            <div class="fee-summary-card">
                <div class="sc-value"><?php echo $student_count; ?></div>
                <div class="sc-label">Students</div>
            </div>
            <div class="fee-summary-card">
                <div class="sc-value"><?php echo $currency_symbol . amountFormat($grand_amount); ?></div>
                <div class="sc-label">Total Demand</div>
            </div>
            <div class="fee-summary-card paid">
                <div class="sc-value"><?php echo $currency_symbol . amountFormat($grand_paid); ?></div>
                <div class="sc-label">Total Paid</div>
            </div>
            <div class="fee-summary-card discount">
                <div class="sc-value"><?php echo $currency_symbol . amountFormat($grand_discount); ?></div>
                <div class="sc-label">Total Discount</div>
            </div>
            <div class="fee-summary-card fine">
                <div class="sc-value"><?php echo $currency_symbol . amountFormat($grand_fine); ?></div>
                <div class="sc-label">Total Fine</div>
            </div>
            <div class="fee-summary-card balance">
                <div class="sc-value"><?php echo $currency_symbol . amountFormat($grand_balance); ?></div>
                <div class="sc-label">Total Balance</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="fee-results-panel">
            <div class="results-heading">
                <h4><i class="fa fa-list-alt" style="margin-right:6px;color:#5b73e8;"></i> <?php echo $this->lang->line('student_list'); ?></h4>
                <span class="badge-count"><?php echo $student_count; ?> students</span>
            </div>
            <div style="padding:0; overflow-x:auto;">
                <?php if (empty($student_remain_fees)): ?>
                <div class="empty-state">
                    <i class="fa fa-inbox"></i>
                    <p><?php echo $this->lang->line('no_record_found'); ?></p>
                </div>
                <?php else: ?>
                <table class="fee-table" id="fee-results-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo $this->lang->line('student_name'); ?></th>
                            <th><?php echo $this->lang->line('class'); ?></th>
                            <th><?php echo $this->lang->line('category'); ?></th>
                            <th><?php echo $this->lang->line('fees_group'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('paid'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('discount'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('fine'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('balance'); ?></th>
                            <th class="text-center"><?php echo $this->lang->line('action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $row_num = 1;
                        foreach ($student_remain_fees as $student):
                            $amount = 0; $amount_deposite = 0; $amount_discount = 0; $amount_fine = 0;
                            if (!empty($student['fees'])) {
                                foreach ($student['fees'] as $fv) {
                                    $amount += $fv['amount'];
                                    $amount_deposite += $fv['amount_deposite'];
                                    $amount_discount += $fv['amount_discount'];
                                    $amount_fine += $fv['amount_fine'];
                                }
                            }
                            $balance = $amount - ($amount_deposite + $amount_discount);
                            $initials = strtoupper(substr($student['firstname'], 0, 1) . substr($student['lastname'] ?? '', 0, 1));
                        ?>
                        <tr>
                            <td style="color:#adb5bd; font-size:12px;"><?php echo $row_num++; ?></td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar-sm"><?php echo $initials; ?></div>
                                    <div>
                                        <div class="student-name-text"><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></div>
                                        <div class="student-meta"><?php echo $student['admission_no']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="class-badge"><?php echo $student['class'] . "-" . $student['section']; ?></span></td>
                            <td><span class="category-badge"><?php echo $student['category']; ?></span></td>
                            <td>
                                <div class="fee-tags">
                                    <?php if (!empty($student['fees'])):
                                        foreach ($student['fees'] as $fv):
                                            $tag = ($fv['is_system']) ? $this->lang->line($fv['fee_type']) : $fv['fee_type'];
                                    ?>
                                    <span class="fee-tag"><?php echo $tag; ?></span>
                                    <?php endforeach; endif; ?>
                                </div>
                            </td>
                            <td class="text-right amount-cell"><?php echo amountFormat($amount); ?></td>
                            <td class="text-right amount-cell" style="color:#27ae60;"><?php echo amountFormat($amount_deposite); ?></td>
                            <td class="text-right amount-cell" style="color:#f39c12;"><?php echo amountFormat($amount_discount); ?></td>
                            <td class="text-right amount-cell" style="color:#e67e22;"><?php echo amountFormat($amount_fine); ?></td>
                            <td class="text-right amount-cell <?php echo ($balance > 0) ? 'balance-due' : 'fully-paid'; ?>">
                                <?php echo amountFormat($balance); ?>
                            </td>
                            <td class="text-center">
                                <?php if ($this->rbac->hasPrivilege('collect_fees', 'can_add')): ?>
                                <a href="<?php echo base_url(); ?>studentfee/addfee/<?php echo $student['student_session_id']; ?>" class="btn-collect-fee">
                                    <i class="fa fa-plus-circle"></i> <?php echo $this->lang->line('add_fees'); ?>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </section>
</div>

<script>
$(document).on('submit', '.studentsearchfee', function() {
    document.getElementById("search_filter").disabled = true;
});

$(document).ready(function() {
    var class_id = $('#class_id').val();
    var section_id = '<?php echo set_value('section_id', 0); ?>';
    getSectionByClass(class_id, section_id);

    $(document).on('change', '#class_id', function() {
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        var class_id = $(this).val();
        if (class_id == '') return;
        $.ajax({
            type: "GET",
            url: '<?php echo base_url(); ?>sections/getByClass',
            data: { class_id: class_id },
            dataType: "json",
            success: function(data) {
                $.each(data, function(i, obj) {
                    $('#section_id').append('<option value="' + obj.section_id + '">' + obj.section + '</option>');
                });
            }
        });
    });

    // DataTable init for results
    if ($('#fee-results-table').length) {
        $('#fee-results-table').DataTable({
            paging: true,
            pageLength: 50,
            ordering: true,
            info: true,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>t<"row"<"col-sm-6"i><"col-sm-6"p>>B',
            buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [0, -1] }]
        });
    }

    // Fee dropdown toggle
    $('#custom-select').on('click', function() {
        $('#custom-select-option-box').toggleClass('show');
        $(this).toggleClass('active');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#checkbox-dropdown-container').length) {
            $('#custom-select-option-box').removeClass('show');
            $('#custom-select').removeClass('active');
        }
    });

    // Select all
    $('#select_all').on('change', function() {
        $('input[name="feegroup[]"]').prop('checked', this.checked);
        updateDropdownLabel();
    });

    // Update label on checkbox change
    $(document).on('change', 'input[name="feegroup[]"]', function() {
        updateDropdownLabel();
    });

    function updateDropdownLabel() {
        var checked = $('input[name="feegroup[]"]:checked');
        if (checked.length === 0) {
            $('#dd-label').text('<?php echo $this->lang->line('select'); ?>');
        } else {
            $('#dd-label').text(checked.length + ' fee type(s) selected');
        }
    }
    updateDropdownLabel();
});

function getSectionByClass(class_id, section_id) {
    if (class_id != "" && class_id != null) {
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $.ajax({
            type: "GET",
            url: '<?php echo base_url(); ?>sections/getByClass',
            data: { class_id: class_id },
            dataType: "json",
            success: function(data) {
                $.each(data, function(i, obj) {
                    var sel = (section_id == obj.section_id) ? 'selected' : '';
                    $('#section_id').append('<option value="' + obj.section_id + '" ' + sel + '>' + obj.section + '</option>');
                });
            }
        });
    }
}
</script>
