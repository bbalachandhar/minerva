<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Enquiry</title>
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
    <style>
        body {
            background-color: #f4f4f4;
        }
        .enquiry-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .enquiry-header img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="enquiry-container">
            <div class="enquiry-header text-center">
                <?php if (!empty($header_image)): ?>
                    <img src="<?php echo base_url('uploads/print_headerfooter/general_purpose/' . $header_image); ?>" alt="Header">
                <?php endif; ?>
            </div>
            <?php $this->load->view($main_content); ?>
        </div>
    </div>
</body>
</html>
