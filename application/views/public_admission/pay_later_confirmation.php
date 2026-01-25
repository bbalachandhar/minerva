<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->lang->line('application_submitted'); ?></title>
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
        .confirmation-card h2 { color: #28a745; margin-bottom: 25px; }
        .confirmation-card p { font-size: 1.1em; line-height: 1.6; color: #555; margin-bottom: 15px; }
        .confirmation-card .reference-no {
            font-size: 1.5em;
            color: #253976;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px;
            border: 1px dashed #253976;
            border-radius: 8px;
            display: inline-block;
        }
        .btn-home {
            padding: 12px 25px;
            font-size: 1.1em;
            border-radius: 8px;
            margin: 10px;
            min-width: 150px;
            background-color: #253976;
            color: white;
            border: none;
        }
        .btn-home:hover { background-color: #1a2a5a; color: white; }
    </style>
</head>
<body>
    <div class="confirmation-card">
        <h2><?php echo $this->lang->line('application_submitted'); ?></h2>
        <p><?php echo $this->lang->line('your_application_has_been_submitted_successfully'); ?>.</p>
        <p><?php echo $this->lang->line('payment_will_be_processed_offline'); ?>.</p>
        
        <div class="reference-no">
            <?php echo $this->lang->line('your_reference_number'); ?>: <?php echo $reference_no; ?>
        </div>
        <p><?php echo $this->lang->line('please_note_this_reference_number_for_future_communication'); ?>.</p>

        <a href="<?php echo site_url('publicadmissionform'); ?>" class="btn btn-home"><?php echo $this->lang->line('go_to_home'); ?></a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
