<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i></h1>
    </section>

    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <form role="form" action="<?php echo site_url('financereports/payrollbankcopy') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>

                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="filter_category[]" multiple="multiple" size="4">
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int) $cat['id']; ?>" <?php echo (isset($filter_category) && is_array($filter_category) && in_array($cat['id'], $filter_category)) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('month'); ?></label>
                                    <select class="form-control" name="filter_month">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                        foreach ($months as $month) {
                                            $selected = (isset($filter_month) && $filter_month == $month) ? 'selected' : '';
                                            echo '<option value="' . $month . '" ' . $selected . '>' . $this->lang->line(strtolower($month)) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('year'); ?></label>
                                    <select class="form-control" name="filter_year">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        $current_year = date('Y');
                                        for ($y = $current_year; $y >= $current_year - 10; $y--) {
                                            $selected = (isset($filter_year) && $filter_year == $y) ? 'selected' : '';
                                            echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('bank_name'); ?></label>
                                    <select class="form-control" name="filter_banks[]" multiple="multiple" size="5">
                                        <option value="__empty__" <?php echo (!empty($filter_banks) && in_array('__empty__', $filter_banks, true)) ? 'selected' : ''; ?>>[EMPTY / NULL BANK NAME]</option>
                                        <?php if (!empty($banks)) {
                                            foreach ($banks as $bank) {
                                                $bank_name = trim((string) ($bank['bank_name'] ?? ''));
                                                if ($bank_name === '') {
                                                    continue;
                                                }
                                                $selected = (!empty($filter_banks) && in_array($bank_name, $filter_banks, true)) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($bank_name, ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($bank_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                            }
                                        } ?>
                                    </select>
                                    <small class="text-muted">Select one or more banks. Use [EMPTY / NULL BANK NAME] for blank entries. For all banks, select all options.</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right">
                                        <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> Payroll Bank Copy</h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label">
                                <?php echo 'Payroll Bank Copy ' . $this->customlib->get_postmessage(); ?>
                            </div>

                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th><?php echo $this->lang->line('employee_id'); ?></th>
                                        <th><?php echo $this->lang->line('bank_name'); ?></th>
                                        <th><?php echo $this->lang->line('bank_branch_name'); ?></th>
                                        <th><?php echo $this->lang->line('ifsc_code'); ?></th>
                                        <th><?php echo $this->lang->line('bank_account_number'); ?></th>
                                        <th class="text text-right"><?php echo $this->lang->line('net_salary'); ?> <span><?php echo '(' . $currency_symbol . ')'; ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_net_salary = 0;
                                    if (!empty($payrollList)) {
                                        foreach ($payrollList as $row) {
                                            $net_salary = isset($row['net_salary']) ? (float) $row['net_salary'] : 0;
                                            $total_net_salary += $net_salary;
                                            ?>
                                            <tr>
                                                <td style="text-transform: capitalize;"><?php echo htmlspecialchars(trim(($row['name'] ?? '') . ' ' . ($row['surname'] ?? ''))); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['department_name'] ?? '-')); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['designation'] ?? '-')); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['employee_id'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['bank_name'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['bank_branch'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['ifsc_code'] ?? '')); ?></td>
                                                <td><?php echo htmlspecialchars((string) ($row['bank_account_no'] ?? '')); ?></td>
                                                <td class="text text-right"><?php echo amountFormat($net_salary); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="box box-solid total-bg">
                                        <td colspan="8" class="text-right"><?php echo $this->lang->line('grand_total'); ?></td>
                                        <td class="text text-right"><?php echo $currency_symbol . amountFormat($total_net_salary); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var schoolName = "<?php echo addslashes($this->sch_setting_detail->name);?>";
        var schoolAddr = "<?php echo addslashes($this->sch_setting_detail->address);?>";
        var reportName = 'Payroll Bank Copy';
        var headerMsg = schoolName + "\n" + schoolAddr + "\n" + reportName + "\n";

        $('.example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', filename: reportName },
                { extend: 'csv', filename: reportName },
                {
                    extend: 'excelHtml5',
                    title: '',
                    filename: reportName,
                    messageTop: headerMsg,
                    exportOptions: {
                        format: {
                            body: function (data) {
                                return data.replace(/(<([^>]+)>)/ig, '').trim();
                            },
                            footer: function (data) {
                                return data.replace(/(<([^>]+)>)/ig, '').trim();
                            }
                        },
                        stripHtml: true,
                        stripNewlines: true
                    },
                    footer: true
                },
                {
                    extend: 'pdfHtml5',
                    title: '',
                    filename: reportName,
                    customize: function (doc) {
                        doc.content.splice(0, 0,
                            { text: schoolName, style: 'dtHeader' },
                            { text: schoolAddr, style: 'dtSubHeader' },
                            { text: reportName, style: 'dtSubHeader' },
                            { text: '\n' }
                        );
                        doc.styles.dtHeader = { fontSize: 16, bold: true, alignment: 'center' };
                        doc.styles.dtSubHeader = { fontSize: 11, alignment: 'center' };
                    },
                    footer: true
                },
                'print'
            ]
        });
    });
</script>
