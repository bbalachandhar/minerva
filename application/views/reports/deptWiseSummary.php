<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> <?php echo $this->lang->line('library_report'); ?></h1>
    </section>
    <section class="content">
        <?php $this->load->view('reports/_library') ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bar-chart"></i> <?php echo $this->lang->line('dept_wise_summary'); ?></h3>
                        <div class="box-tools pull-right">
                            <button onclick="window.print();" class="btn btn-primary btn-sm no-print">
                                <i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="deptWiseSummaryTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo $this->lang->line('department'); ?></th>
                                    <th><?php echo $this->lang->line('no_of_titles'); ?></th>
                                    <th><?php echo $this->lang->line('no_of_volumes'); ?></th>
                                    <th><?php echo $this->lang->line('no_of_issued'); ?></th>
                                    <th><?php echo $this->lang->line('no_of_available'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_titles    = 0;
                                $total_volumes   = 0;
                                $total_issued    = 0;
                                $total_available = 0;
                                if (!empty($resultlist)):
                                    foreach ($resultlist as $i => $row):
                                        $total_titles    += (int)$row['no_of_titles'];
                                        $total_volumes   += (int)$row['no_of_volumes'];
                                        $total_issued    += (int)$row['no_of_issued'];
                                        $total_available += (int)$row['no_of_available'];
                                ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo (int)$row['no_of_titles']; ?></td>
                                    <td><?php echo (int)$row['no_of_volumes']; ?></td>
                                    <td><?php echo (int)$row['no_of_issued']; ?></td>
                                    <td><?php echo (int)$row['no_of_available']; ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($resultlist)): ?>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right"><strong><?php echo $this->lang->line('total'); ?></strong></th>
                                    <th><strong><?php echo $total_titles; ?></strong></th>
                                    <th><strong><?php echo $total_volumes; ?></strong></th>
                                    <th><strong><?php echo $total_issued; ?></strong></th>
                                    <th><strong><?php echo $total_available; ?></strong></th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {
    $('#deptWiseSummaryTable').DataTable({
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching": true,
        "columnDefs": [{ "orderable": false, "targets": 0 }]
    });
});
</script>
