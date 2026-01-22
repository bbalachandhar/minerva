<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-birthday-cake"></i> <?php echo $this->lang->line('staff_birthday_report'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" id="search_form" action="<?php echo site_url('admin/staffbirthday') ?>" method="post" class="">
                            <div class="row">
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('date_from'); ?></label><small class="req"> *</small>
                                        <input type="text" name="date_from" class="form-control date" id="date_from" value="<?php echo set_value('date_from', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('date_to'); ?></label><small class="req"> *</small>
                                        <div class="input-group">
                                            <input type="text" name="date_to" class="form-control date" id="date_to" value="<?php echo set_value('date_to', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" autocomplete="off">
                                            <span class="input-group-btn" style="margin-left: 5px;">
                                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box box-info" id="report_data">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('staff_birthday_report'); ?></h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="staff_birthday_table" class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('staff_id'); ?></th>
                                    <th><?php echo $this->lang->line('staff_name'); ?></th>
                                    <th><?php echo $this->lang->line('role'); ?></th>
                                    <th><?php echo $this->lang->line('department'); ?></th>
                                    <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                    <th><?php echo $this->lang->line('gender'); ?></th>
                                    <th><?php echo $this->lang->line('mobile_no'); ?></th>
                                    <th><?php echo $this->lang->line('email'); ?></th>
                                    <th><?php echo $this->lang->line('current_address'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var table = $('#staff_birthday_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo site_url('admin/staffbirthday') ?>",
            "type": "POST",
            "data": function ( d ) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        "columns": [
            { "data": 0 },
            { "data": 1 },
            { "data": 2 },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 },
            { "data": 6 },
            { "data": 7 },
            { "data": 8 }
        ],
        "dom": 'lBfrtip<"actions">',
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    $('#search_form').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });
});
</script>
