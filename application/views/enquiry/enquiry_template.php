<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Enquiry - <?php echo $sch_setting_detail->name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #1e3a5f;
            --primary-light: #1e40af;
            --accent: #2563eb;
            --accent-light: #3b82f6;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --text-label: #374151;
            --border: #d1d5db;
            --border-light: #e5e7eb;
            --bg-page: #f3f4f6;
            --bg-card: #ffffff;
            --danger: #dc2626;
            --radius-sm: 8px;
            --radius-md: 12px;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .page-wrapper {
            max-width: 720px;
            margin: 0 auto;
            padding: 0 16px 48px;
        }

        /* ── Header ─────────────────────────────────────────────────── */
        .form-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 0 0 var(--radius-md) var(--radius-md);
            padding: 28px 32px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(30,58,95,0.25);
        }
        .form-header .logo-img {
            height: 60px;
            width: auto;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
            padding: 6px;
            flex-shrink: 0;
        }
        .form-header .header-text {
            flex: 1;
            text-align: center;
            color: #fff;
        }
        .form-header .header-text h1 {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }
        .form-header .header-text p {
            font-size: 12px;
            margin: 0;
            opacity: 0.85;
            line-height: 1.5;
        }
        @media (max-width: 600px) {
            .form-header { flex-direction: column; text-align: center; padding: 20px 16px; }
            .form-header .header-text h1 { font-size: 16px; }
        }

        /* ── Card section ───────────────────────────────────────────── */
        .section-card {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            padding: 28px 28px 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin: 0 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title i { font-size: 16px; color: var(--accent); }

        /* ── Form fields ────────────────────────────────────────────── */
        .field-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .field-grid .full-width { grid-column: 1 / -1; }
        @media (max-width: 600px) {
            .field-grid { grid-template-columns: 1fr; }
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-label);
            margin-bottom: 6px;
        }
        .form-label .text-danger { color: var(--danger); }
        .form-control, .form-select {
            height: 44px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            padding: 8px 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
            outline: none;
        }
        textarea.form-control { height: auto; }
        .form-text { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* ── Course type cards ──────────────────────────────────────── */
        .course-type-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .course-type-group .ct-option {
            flex: 1;
            min-width: 120px;
        }
        .course-type-group input[type="radio"] { display: none; }
        .course-type-group .ct-label {
            display: block;
            padding: 10px 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-label);
            cursor: pointer;
            transition: all 0.2s;
        }
        .course-type-group .ct-label:hover { border-color: var(--accent-light); }
        .course-type-group input[type="radio"]:checked + .ct-label {
            border-color: var(--accent);
            background: #eff6ff;
            color: var(--accent);
        }

        /* ── Submit button ──────────────────────────────────────────── */
        .btn-submit {
            display: block;
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .btn-submit:hover {
            box-shadow: 0 6px 20px rgba(37,99,235,0.3);
            transform: translateY(-1px);
        }

        /* ── Validation errors ──────────────────────────────────────── */
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        /* ── Footer ─────────────────────────────────────────────────── */
        .page-footer {
            text-align: center;
            padding: 16px 0;
            font-size: 11px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <div class="form-header">
            <?php if (!empty($sch_setting_detail->admission_logo_left)): ?>
            <img src="<?php echo base_url('uploads/logos/' . $sch_setting_detail->admission_logo_left); ?>" alt="" class="logo-img">
            <?php endif; ?>
            <div class="header-text">
                <h1><?php echo $sch_setting_detail->name; ?></h1>
                <p>
                    <?php echo $sch_setting_detail->address; ?><br>
                    Ph: <?php echo $sch_setting_detail->phone; ?>
                    <?php if (!empty($sch_setting_detail->email)): ?> | <?php echo $sch_setting_detail->email; ?><?php endif; ?>
                </p>
            </div>
            <?php if (!empty($sch_setting_detail->admission_logo_right)): ?>
            <img src="<?php echo base_url('uploads/logos/' . $sch_setting_detail->admission_logo_right); ?>" alt="" class="logo-img">
            <?php endif; ?>
        </div>

        <!-- Form Content -->
        <?php $this->load->view($main_content); ?>

        <div class="page-footer">
            Powered by Minerva
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
