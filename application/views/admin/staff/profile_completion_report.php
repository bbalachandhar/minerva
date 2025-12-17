<div class="content-wrapper" style="min-height: 348px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-percent"></i> <?php echo $this->lang->line('staff_profile_completion_report'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if (!empty($report_data)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover example">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('employee_id'); ?></th>
                                            <th><?php echo $this->lang->line('name'); ?></th>
                                            <th><?php echo $this->lang->line('email'); ?></th>
                                            <th><?php echo $this->lang->line('completion_percentage'); ?></th>
                                            <th><?php echo $this->lang->line('status'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $staff) : ?>
                                            <tr class="<?php echo ($staff['completion_percentage'] < 100) ? 'danger' : ''; ?>">
                                                <td><?php echo $staff['employee_id']; ?></td>
                                                <td><?php echo $staff['name'] . ' ' . $staff['surname']; ?></td>
                                                <td><?php echo $staff['email']; ?></td>
                                                <td><?php echo $staff['completion_percentage']; ?>%</td>
                                                <td>
                                                    <?php if ($staff['completion_percentage'] < 100) : ?>
                                                        <span class="label label-warning"><?php echo $this->lang->line('incomplete'); ?></span>
                                                    <?php else : ?>
                                                        <span class="label label-success"><?php echo $this->lang->line('complete'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.example').DataTable({
            "aaSorting": [],
            initComplete: function () {
                this.api().columns().every( function () {
                    var column = this;
                    var select = $('<select class="form-control" style="width:100%"><option value=""></option></select>')
                        .appendTo( $(column.footer()).empty() )
                        .on( 'change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search( val ? '^'+val+'$' : '', true, false )
                                .draw();
                        } );
                    column.data().unique().sort().each( function ( d, j ) {
                        select.append( '<option value="'+d+'">'+d+'</option>' )
                    } );
                } );
            }
        });
    });
</script>