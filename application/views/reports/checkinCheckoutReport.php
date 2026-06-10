<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> <?php echo $this->lang->line('library_report'); ?></h1>
    </section>
    <section class="content">
        <?php $this->load->view('reports/_library'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('report/checkinCheckoutReport') ?>" method="post">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type'); ?></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                        <?php foreach ($searchlist as $key => $search): ?>
                                            <option value="<?php echo $key ?>" <?php echo (isset($search_type) && $search_type == $key) ? 'selected' : ''; ?>><?php echo $search ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div id="date_result"></div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary btn-sm pull-right">
                                        <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-exchange"></i> <?php echo $this->lang->line('checkin_checkout_report'); ?></h3>
                            <div class="box-tools pull-right">
                                <button onclick="window.print();" class="btn btn-primary btn-sm no-print">
                                    <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                                </button>
                            </div>
                        </div>
                        <?php if (isset($label)): ?>
                        <div class="box-header ptbnull">
                            <p class="text-muted"><?php echo $label; ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('checkin_checkout_report'); ?></div>
                            <table id="checkinCheckoutTable" class="table table-striped table-bordered table-hover"
                                   data-export-title="<?php echo $this->lang->line('checkin_checkout_report'); ?>">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo $this->lang->line('date'); ?></th>
                                        <th><?php echo $this->lang->line('id'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('type'); ?></th>
                                        <th><?php echo $this->lang->line('in_time'); ?></th>
                                        <th><?php echo $this->lang->line('out_time'); ?></th>
                                        <th><?php echo $this->lang->line('duration'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($resultlist)):
                                        foreach ($resultlist as $i => $row):
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo date($this->customlib->getSchoolDateFormat(), strtotime($row['attendance_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo ucfirst($row['user_type']); ?></td>
                                        <td><?php echo $row['in_time'] ? date('h:i A', strtotime($row['in_time'])) : '-'; ?></td>
                                        <td><?php echo $row['out_time'] ? date('h:i A', strtotime($row['out_time'])) : '-'; ?></td>
                                        <td><?php echo $row['duration'] ? $row['duration'] : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if (isset($search_type) && $search_type == 'period'): ?>
<script>
$(document).ready(function () { showdate('period'); });
</script>
<?php endif; ?>

<script>
$(document).ready(function () {
    <?php if (!empty($resultlist)): ?>
    var schoolName  = "<?php echo addslashes($sch_setting->name ?? ''); ?>";
    var schoolAddr  = "<?php echo addslashes($sch_setting->address ?? ''); ?>";
    var reportTitle = "<?php echo $this->lang->line('checkin_checkout_report'); ?>";
    var dateRange   = "<?php echo isset($label) ? addslashes($label) : ''; ?>";
    var headerMsg   = schoolName + "\n" + schoolAddr + "\n" + reportTitle + "\n" + dateRange;

    $('#checkinCheckoutTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv',
            {
                extend: 'excelHtml5',
                title: '',
                messageTop: headerMsg,
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    $('row c', sheet).attr('s', '');
                },
                exportOptions: {
                    format: {
                        body: function (data, row, column, node) {
                            return data.replace(/(<([^>]+)>)/ig, '').trim();
                        }
                    },
                    stripHtml: true,
                    stripNewlines: true
                }
            },
            {
                extend: 'pdfHtml5',
                title: '',
                orientation: 'landscape',
                customize: function (doc) {
                    doc.content.splice(0, 0,
                        { text: schoolName,  style: 'dtHeader' },
                        { text: schoolAddr,  style: 'dtSubHeader' },
                        { text: reportTitle, style: 'dtSubHeader' },
                        { text: dateRange,   style: 'dtSubHeader' },
                        { text: '\n' }
                    );
                    doc.styles.dtHeader    = { fontSize: 14, bold: true, alignment: 'center' };
                    doc.styles.dtSubHeader = { fontSize: 10, alignment: 'center' };
                },
                exportOptions: {
                    format: {
                        body: function (data, row, column, node) {
                            return data.replace(/(<([^>]+)>)/ig, '').trim();
                        }
                    },
                    stripHtml: true,
                    stripNewlines: true
                }
            },
            'print'
        ],
        "paging":    true,
        "pageLength": 50,
        "ordering":  true,
        "info":      true,
        "searching": true
    });
    <?php endif; ?>
});
</script>
