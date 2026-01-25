<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line('payment_confirmation'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .confirmation-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .confirmation-card h2 { color: #253976; margin-bottom: 25px; }
        .confirmation-card p { font-size: 1.1em; line-height: 1.6; color: #555; margin-bottom: 15px; }
        .confirmation-card .amount-display {
            font-size: 2em;
            color: #28a745;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px;
            border: 1px dashed #28a745;
            border-radius: 8px;
            display: inline-block;
        }
        .btn-proceed, .btn-cancel {
            padding: 12px 25px;
            font-size: 1.1em;
            border-radius: 8px;
            margin: 10px;
            min-width: 150px;
        }
        .btn-proceed { background-color: #253976; color: white; border: none; }
        .btn-proceed:hover { background-color: #1a2a5a; color: white; }
        .btn-cancel { background-color: #dc3545; color: white; border: none; }
        .btn-cancel:hover { background-color: #c82333; color: white; }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <h2><?php echo $this->lang->line('payment_confirmation'); ?></h2>
        <p><?php echo $this->lang->line('application_submitted_successfully'); ?>.</p>
        <p><?php echo $this->lang->line('please_proceed_to_payment_to_complete_your_application'); ?>.</p>
        
        <div class="amount-display">
            <?php echo $total_amount_to_pay_currency; ?>
        </div>

        <form action="<?php echo site_url('publicadmissionform/initiate_gateway_payment'); ?>" method="POST">
            <input type="hidden" name="online_admission_id" value="<?php echo $online_admission_id; ?>">
            <button type="submit" class="btn btn-proceed"><?php echo $this->lang->line('proceed_to_payment'); ?></button>
            <a href="<?php echo site_url('publicadmissionform'); ?>" class="btn btn-cancel"><?php echo $this->lang->line('cancel_application'); ?></a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
