<?php
$dateFormat = $this->customlib->getSchoolDateFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-birthday-cake"></i> Staff Birthday List</h1>
    </section>
    <section class="content">
        <?php $this->load->view('reports/_human_resource'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Select Criteria</h3>
                    </div>
                    <form role="form" action="<?php echo site_url('report/staff_birthday_list'); ?>" method="post">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="text"
                                           id="from_date_bd"
                                           class="form-control date"
                                           name="from_date"
                                           placeholder="<?php echo $dateFormat; ?>"
                                           value="<?php echo htmlspecialchars($from_date); ?>"
                                           autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="text"
                                           class="form-control date"
                                           name="to_date"
                                           placeholder="<?php echo $dateFormat; ?>"
                                           value="<?php echo htmlspecialchars($to_date); ?>"
                                           autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($from_date != '' && $to_date != '') : ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-birthday-cake"></i>
                            Staff Birthdays:
                            <?php
                                echo date($dateFormat, strtotime($from_date))
                                   . ' to '
                                   . date($dateFormat, strtotime($to_date));
                            ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <span class="badge bg-blue"><?php echo count($resultlist); ?> record(s)</span>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="birthdayTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Staff Name</th>
                                        <th>Employee ID</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Birthday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($resultlist)) : ?>
                                        <?php $i = 1; foreach ($resultlist as $row) : ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['department_name'] ?? '—'); ?></td>
                                            <td><?php echo htmlspecialchars($row['designation'] ?? '—'); ?></td>
                                            <td><?php echo $row['dob'] ? date($dateFormat, strtotime($row['dob'])) : '—'; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No staff birthdays found in the selected date range.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function () {
    // Initialize datepickers explicitly so they reopen on every click
    $('#from_date_bd, #to_date_bd').datepicker({
        format: date_format,
        autoclose: true,
        todayHighlight: true,
        weekStart: start_week
    });

    <?php if (!empty($resultlist)): ?>
    var schoolName = "<?php echo addslashes($sch_setting->name ?? ''); ?>";
    var schoolAddr = "<?php echo addslashes($sch_setting->address ?? ''); ?>";
    var reportTitle = "Staff Birthday List";
    var headerMsg = schoolName + "\n" + schoolAddr + "\n" + reportTitle + "\n";

    $('#birthdayTable').DataTable({
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
                customize: function (doc) {
                    doc.content.splice(0, 0,
                        { text: schoolName, style: 'dtHeader' },
                        { text: schoolAddr, style: 'dtSubHeader' },
                        { text: reportTitle, style: 'dtSubHeader' },
                        { text: '\n' }
                    );
                    doc.styles.dtHeader    = { fontSize: 16, bold: true, alignment: 'center' };
                    doc.styles.dtSubHeader = { fontSize: 11, alignment: 'center' };
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
        "paging": true,
        "ordering": true,
        "info": true,
        "searching": true
    });
    <?php endif; ?>
});
</script>
