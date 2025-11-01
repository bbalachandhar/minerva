<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-newspaper-o"></i> <?php echo $this->lang->line('reports'); ?> <small> <?php echo $this->lang->line('filter_by_name'); ?></small></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('financereports/deleted_payments_report') ?>" method="post" class="">
                            <div class="row">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('search_type'); ?></label>
                                        <select class="form-control" name="search_type">
                                            <option value=""><?php echo $this->lang->line('all'); ?></option>
                                            <?php foreach ($searchlist as $key => $value) { ?>
                                                <option value="<?php echo $key ?>" <?php if ((isset($search_type)) && ($search_type == $key)) {
                                                    echo "selected";
                                                } ?>><?php echo $value ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-4" id="fromdate" >
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('date_from'); ?></label><small class="req"> *</small>
                                        <input id="date_from" name="date_from" placeholder="" type="text" class="form-control date" value="<?php echo set_value('date_from', date($this->customlib->getSchoolDateFormat())); ?>"  />
                                        <span class="text-danger"><?php echo form_error('date_from'); ?></span>
                                    </div>
                                </div> 
                                <div class="col-sm-6 col-md-4" id="todate">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('date_to'); ?></label><small class="req"> *</small>
                                        <input id="date_to" name="date_to" placeholder="" type="text" class="form-control date" value="<?php echo set_value('date_to', date($this->customlib->getSchoolDateFormat())); ?>"  />
                                        <span class="text-danger"><?php echo form_error('date_to'); ?></span>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                
                <?php if (!empty($report_data)) { ?>
                    <div class="box-header ptbnull"></div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover example">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th><?php echo $this->lang->line('fee_group'); ?></th>
                                    <th><?php echo $this->lang->line('fee_type'); ?></th>
                                    <th><?php echo $this->lang->line('deleted_on'); ?></th>
                                    <th><?php echo $this->lang->line('deleted_by'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                                    <th><?php echo $this->lang->line('deletion_reason'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row) { 
                                    $amount_detail = json_decode($row->amount_detail);
                                ?>
                                    <tr>
                                        <td><?php echo $this->customlib->getFullName($row->firstname, (isset($row->middlename) ? $row->middlename : ''), $row->lastname, $sch_setting->middlename, $sch_setting->lastname); ?></td>
                                        <td><?php echo $row->admission_no; ?></td>
                                        <td><?php echo $row->class . ' (' . $row->section . ')'; ?></td>
                                        <td><?php echo $row->fee_group_name; ?></td>
                                        <td><?php echo $row->fee_type_name; ?></td>
                                        <td><?php echo date($this->customlib->getSchoolDateFormat(true, true), strtotime($row->deleted_at)); ?></td>
                                        <td><?php echo $row->deleted_by_name . ' (' . $row->employee_id . ')'; ?></td>
                                        <td class="text-right"><?php echo $currency_symbol . amountFormat($amount_detail->amount); ?></td>
                                        <td><?php echo $row->deletion_reason; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>
