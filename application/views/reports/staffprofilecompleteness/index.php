<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php echo $this->lang->line('reports'); ?> <small> <?php echo $this->lang->line('filter_by_name'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $title; ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('report/staffprofilecompleteness') ?>" method="post" class="">
                            <div class="row">
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?></label>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($departmentlist as $department) {
                                                ?>
                                                <option value="<?php echo $department['id'] ?>" <?php if (set_value('department_id', $department_id) == $department['id']) echo "selected=selected" ?>><?php echo $department['department_name'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('department_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="form-group">
                                        <label class="d-block">&nbsp;</label>
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('staff_name'); ?></th>
                                        <th><?php echo $this->lang->line('employee_id'); ?></th>
                                        <th><?php echo $this->lang->line('department'); ?></th>
                                        <th><?php echo "Profile Completeness"; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stafflist)) { ?>
                                        <tr>
                                            <td colspan="4" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                    <?php } else {
                                        foreach ($stafflist as $staff) {
                                            $percentage = $staff['completion_percentage'];
                                            ?>
                                            <tr>
                                                <td><?php echo $staff['name'] . " " . $staff['surname']; ?></td>
                                                <td><?php echo $staff['employee_id']; ?></td>
                                                <td><?php echo $staff['department']; ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-striped <?php if($percentage < 30) { echo 'progress-bar-danger'; } elseif($percentage < 60) { echo 'progress-bar-warning'; } elseif($percentage < 90) { echo 'progress-bar-info'; } else { echo 'progress-bar-success'; } ?>" role="progressbar" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage; ?>%">
                                                            <?php echo round($percentage, 2); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
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
        // No dynamic dropdown for sections as there is no class-section relationship for staff
        // The department filter is handled by form submission.
    });
</script>