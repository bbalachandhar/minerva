<?php $this->load->view('layout/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> <small><?php echo $this->lang->line('incidental_fee_receipt'); ?></small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('incidental_fee_receipt'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="#" onclick="printDiv('receipt_print_area')" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></a>
                        </div>
                    </div>
                    <div class="box-body" id="receipt_print_area">
                        <?php if (!empty($collection)) { ?>
                            <div class="row">
                                <div class="col-xs-6">
                                    <strong><?php echo $this->lang->line('receipt_no'); ?>:</strong> <?php echo $collection['receipt_no']; ?><br>
                                    <strong><?php echo $this->lang->line('date'); ?>:</strong> <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($collection['date_collected'])); ?><br>
                                    <strong><?php echo $this->lang->line('collected_by'); ?>:</strong> <?php echo $collection['collected_by_name']; ?><br>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <strong><?php echo $this->lang->line('session'); ?>:</strong> <?php echo $collection['session_name']; ?><br>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-xs-12">
                                    <h4><?php echo $this->lang->line('student_details'); ?></h4>
                                    <strong><?php echo $this->lang->line('student_name'); ?>:</strong> <?php echo $collection['firstname'] . ' ' . $collection['lastname']; ?><br>
                                    <strong><?php echo $this->lang->line('admission_no'); ?>:</strong> <?php echo $collection['admission_no']; ?><br>
                                    <?php if (!empty($collection['class_name'])) { ?>
                                        <strong><?php echo $this->lang->line('class'); ?>:</strong> <?php echo $collection['class_name']; ?><br>
                                    <?php } ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-xs-12">
                                    <h4><?php echo $this->lang->line('fee_details'); ?></h4>
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo $this->lang->line('fee_type'); ?></th>
                                                <th><?php echo $this->lang->line('description'); ?></th>
                                                <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo $collection['fee_type_title']; ?></td>
                                                <td><?php echo $collection['fee_type_description']; ?></td>
                                                <td class="text-right"><?php echo $collection['amount_collected']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <p class="text-right"><strong><?php echo $this->lang->line('total_amount'); ?>:</strong> <?php echo $collection['amount_collected']; ?></p>
                                    <?php if (!empty($collection['notes'])) { ?>
                                        <p><strong><?php echo $this->lang->line('notes'); ?>:</strong> <?php echo $collection['notes']; ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-danger"><?php echo $this->lang->line('no_record_found'); ?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function printDiv(elem) {
        var mywindow = window.open('', 'PRINT', 'height=400,width=600');
        mywindow.document.write('<html><head><title>' + document.title + '</title>');
        mywindow.document.write('<?php $this->load->view('layout/print_styles'); ?>'); // Assuming a print_styles view exists
        mywindow.document.write('</head><body >');
        mywindow.document.write(document.getElementById(elem).innerHTML);
        mywindow.document.write('</body></html>');
        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/
        mywindow.print();
        mywindow.close();
        return true;
    }
</script>
<?php $this->load->view('layout/footer'); ?>