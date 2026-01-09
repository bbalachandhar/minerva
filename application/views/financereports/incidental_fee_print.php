<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->lang->line('incidental_fee_receipt'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
    <style>
        body {
            font-size: 12pt; /* Increased font size */
        }
        .receipt-bg {
            background: #f5f5f5;
            padding: 15px; /* Reduced padding */
            border-radius: 5px;
            margin-bottom: 15px; /* Space between copies */
        }
        hr {
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th {
            padding: 5px; /* Reduced table cell padding */
            line-height: 1.2; /* Reduced line height */
        }
        .text-center h3, .text-center p {
            margin: 0;
            line-height: 1.3;
        }
        address {
            margin-bottom: 5px;
            line-height: 1.3;
        }
        @media print {
            .no-print { display: none; }
            .receipt-bg {
                background: #fff !important;
                border: 1px solid #ccc !important;
            }
            .page-break {
                display: block;
                page-break-before: always;
            }
            body {
                -webkit-print-color-adjust: exact; /* Chrome, Safari */
                color-adjust: exact; /* Firefox */
            }
        }
    </style>
</head>
<body onafterprint="window.location.href = '<?php echo site_url('admin/collect_incidental_fee'); ?>'">
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <?php for ($i = 0; $i < 2; $i++) { // Loop to create two copies ?>
                <div class="receipt-bg">
                    <div class="row">
                        <div class="col-xs-12 text-center">
                            <?php if ($receipt_header) { ?>
                                <img src="<?php echo $this->media_storage->getImageURL('/uploads/print_headerfooter/student_receipt/'.$receipt_header);?>" style="height: 100px; width: 100%;">
                            <?php } ?>

                            <p><strong><?php echo $this->lang->line('incidental_fee_receipt'); ?></strong></p>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-xs-6">
                            <address>
                                <strong><?php echo $this->lang->line('receipt_no'); ?>:</strong> <?php echo $collection['receipt_no']; ?><br>
                                <strong><?php echo $this->lang->line('student_name'); ?>:</strong> <?php echo $this->customlib->getFullName($collection['firstname'],'', $collection['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?><br>
                                <strong><?php echo $this->lang->line('admission_no'); ?>:</strong> <?php echo $collection['admission_no']; ?><br>
                            </address>
                        </div>
                        <div class="col-xs-6 text-right">
                            <address>
                                <strong><?php echo $this->lang->line('date'); ?>:</strong> <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($collection['date_collected'])); ?><br>
                                <strong><?php echo $this->lang->line('class'); ?>:</strong> <?php echo $collection['class_name'] . ' (' . $collection['section'] . ')'; ?><br>
                            </address>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('fee_type'); ?></th>
                                <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $collection['fee_type_title']; ?></td>
                                <td class="text-right"><?php echo number_format($collection['amount_collected'], 2); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-right"><?php echo $this->lang->line('total_amount'); ?>:</th>
                                <th class="text-right"><?php echo number_format($collection['amount_collected'], 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="row">
                        <div class="col-xs-12">
                            <strong><?php echo $this->lang->line('notes'); ?>:</strong> <?php echo $collection['notes']; ?>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-xs-6">
                            <strong><?php echo $this->lang->line('collected_by'); ?>:</strong> <?php echo $collection['collected_by_name']; ?>
                        </div>
                    </div>
                </div>
                <?php if ($i == 0) { echo '<div class="page-break"></div>'; } // Add page break after first copy ?>
            <?php } ?>
            <div class="row no-print">
                <div class="col-xs-12 text-center">
                    <button class="btn btn-primary" onclick="window.print();"><i class="fa fa-print"></i> <?php echo $this->lang->line('print'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>