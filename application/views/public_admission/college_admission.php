<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form - <?php echo $sch_setting->name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
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
            --bg-input: #ffffff;
            --success: #059669;
            --danger: #dc2626;
            --radius-sm: 8px;
            --radius-md: 12px;
            --shadow-card: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-card-hover: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-input-focus: 0 0 0 3px rgba(37,99,235,0.15);
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Header Banner ─── */
        .admission-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #fff;
            padding: 28px 0 24px;
            width: 100%;
        }

        .header-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-logo {
            flex: 0 0 auto;
        }

        .header-logo img {
            height: 72px;
            width: auto;
            border-radius: 8px;
            background: #fff;
            padding: 6px;
            display: block;
        }

        .header-text {
            flex: 1;
            text-align: center;
            min-width: 0;
        }

        .header-text h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: 0.3px;
        }

        .header-text .header-address {
            font-size: 13px;
            opacity: 0.88;
            margin: 0 0 2px;
            line-height: 1.4;
        }

        .header-text .header-contact {
            font-size: 12px;
            opacity: 0.75;
            margin: 0;
        }

        /* ─── Main Container ─── */
        .form-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px 24px 48px;
        }

        .form-title-bar {
            text-align: center;
            margin-bottom: 24px;
        }

        .form-title-bar h2 {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--primary);
            margin: 0;
            padding: 16px 0;
            border-bottom: 2px solid var(--border-light);
        }

        /* ─── Section Cards ─── */
        .section-card {
            background: var(--bg-card);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-card);
            padding: 24px 28px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
            position: relative;
        }

        .section-card .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: var(--text-muted);
            margin: 0 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light);
        }

        /* ─── Form Fields ─── */
        .field-grid {
            display: grid;
            gap: 16px;
        }

        .field-grid.cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .field-grid.cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .field-grid.cols-4 {
            grid-template-columns: 1fr 2fr 1fr 1fr;
        }

        .field-grid.cols-2-1 {
            grid-template-columns: 2fr 1fr;
        }

        .field-grid .span-2 {
            grid-column: span 2;
        }

        .field-grid .span-3 {
            grid-column: span 3;
        }

        .field-grid .span-full {
            grid-column: 1 / -1;
        }

        .field-group {
            display: flex;
            flex-direction: column;
        }

        .field-group label,
        .field-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: var(--text-label);
            margin-bottom: 6px;
        }

        .field-group label .req,
        .req {
            color: var(--danger);
            font-weight: 700;
        }

        .form-control,
        .form-select {
            height: 44px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            color: var(--text-dark);
            background: var(--bg-input);
            padding: 8px 12px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: var(--shadow-input-focus);
            outline: none;
        }

        .form-control::placeholder {
            color: #9ca3af;
            font-size: 13px;
        }

        textarea.form-control {
            height: auto;
            min-height: 80px;
            resize: vertical;
        }

        .form-control[readonly] {
            background: #f9fafb;
            color: var(--text-muted);
        }

        .text-danger {
            color: var(--danger) !important;
            font-size: 12px;
        }

        .text-muted {
            color: var(--text-muted) !important;
            font-size: 12px;
        }

        /* ─── Admission Type Radios ─── */
        .admission-type-options {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .admission-type-options .type-option {
            flex: 1 1 0;
            min-width: 0;
        }

        .admission-type-options .type-option input[type="radio"] {
            display: none;
        }

        .admission-type-options .type-option label {
            display: block;
            text-align: center;
            padding: 10px 8px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            color: var(--text-label);
            cursor: pointer;
            transition: all 0.15s ease;
            text-transform: none;
            letter-spacing: 0;
            margin: 0;
        }

        .admission-type-options .type-option input[type="radio"]:checked + label {
            border-color: var(--accent);
            background: rgba(37, 99, 235, 0.06);
            color: var(--accent);
        }

        /* ─── Photo Upload ─── */
        .photo-upload-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .passport-upload-frame {
            width: 120px;
            height: 150px;
            border: 2px dashed var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: #f9fafb;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: border-color 0.2s ease;
        }

        .passport-upload-frame:hover {
            border-color: var(--accent);
        }

        .passport-upload-frame input[type="file"] {
            opacity: 0;
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            cursor: pointer;
        }

        .passport-upload-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
        }

        .passport-upload-frame .upload-icon {
            font-size: 24px;
            color: var(--accent-light);
        }

        .photo-upload-note {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
            text-align: center;
        }

        /* ─── References Row ─── */
        .ref-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        /* ─── HSC Table ─── */
        .marks-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .marks-table thead th {
            background: var(--primary);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: center;
            border: none;
        }

        .marks-table tbody td {
            padding: 10px 12px;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
            vertical-align: middle;
        }

        .marks-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .marks-table tbody tr:last-child td {
            border-bottom: none;
        }

        .marks-table tbody tr.highlight-row {
            background: rgba(37, 99, 235, 0.04);
            font-weight: 600;
        }

        .marks-table .form-control {
            height: 38px;
            text-align: center;
            max-width: 120px;
            margin: 0 auto;
        }

        .marks-table td:first-child {
            text-align: left;
            font-weight: 500;
            white-space: nowrap;
        }

        /* ─── Lateral Entry Panels ─── */
        .semester-panels {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .semester-panel {
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            padding: 16px;
            background: #fafbfc;
        }

        .semester-panel h6 {
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            color: var(--primary);
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-light);
        }

        .sem-subject-row {
            display: grid;
            grid-template-columns: auto 1fr 80px auto 80px;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .sem-subject-row .sem-num {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 12px;
            width: 20px;
        }

        .sem-subject-row .form-control {
            height: 36px;
            font-size: 13px;
        }

        .sem-subject-row .sep-text {
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
        }

        .sem-total-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--border-light);
            font-weight: 600;
            font-size: 13px;
        }

        .sem-total-row .form-control {
            height: 36px;
            width: 80px;
            font-size: 13px;
            text-align: center;
        }

        /* ─── Additional Info ─── */
        .radio-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .radio-group label {
            font-size: 14px;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
        }

        .radio-group input[type="radio"] {
            accent-color: var(--accent);
            width: 16px;
            height: 16px;
        }

        .info-item {
            padding: 14px 0;
            border-bottom: 1px solid var(--border-light);
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-item .info-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-label);
            margin-bottom: 8px;
        }

        /* ─── Submit Button ─── */
        .submit-area {
            margin-top: 24px;
        }

        .btn-submit {
            display: block;
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            cursor: pointer;
            transition: box-shadow 0.2s ease, transform 0.15s ease;
        }

        .btn-submit:hover {
            box-shadow: 0 6px 20px rgba(30, 58, 95, 0.35);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ─── Alert Overrides ─── */
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: var(--radius-sm);
            font-size: 13px;
            padding: 10px 14px;
        }

        /* ─── Modals ─── */
        .modal-content {
            border-radius: var(--radius-md);
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-light);
            padding: 16px 20px;
        }

        .modal-body {
            padding: 24px 20px;
        }

        .modal-footer {
            border-top: 1px solid var(--border-light);
            padding: 12px 20px;
        }

        #paymentOptionModal .modal-body .btn {
            min-width: 180px;
            border-radius: var(--radius-sm);
            font-weight: 600;
        }

        #errorModal .modal-header {
            background: var(--danger);
            color: #fff;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }

        /* ─── Input Group ─── */
        .input-group .input-group-text {
            background: #f3f4f6;
            border: 1px solid var(--border);
            border-left: none;
            color: var(--text-muted);
            font-size: 14px;
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .form-control:focus {
            border-right: none;
        }

        .input-group .form-control:focus + .input-group-text {
            border-color: var(--accent);
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .header-inner {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .header-logo img {
                height: 56px;
            }

            .header-text h1 {
                font-size: 18px;
            }

            .form-wrapper {
                padding: 16px 12px 40px;
            }

            .section-card {
                padding: 16px;
            }

            .field-grid.cols-3,
            .field-grid.cols-4,
            .field-grid.cols-2 {
                grid-template-columns: 1fr;
            }

            .field-grid .span-2,
            .field-grid .span-3 {
                grid-column: span 1;
            }

            .admission-type-options {
                flex-direction: column;
            }

            .ref-row {
                grid-template-columns: 1fr;
            }

            .semester-panels {
                grid-template-columns: 1fr;
            }

            .marks-table {
                font-size: 13px;
            }

            .marks-table thead th {
                font-size: 11px;
                padding: 8px 6px;
            }

            .marks-table tbody td {
                padding: 8px 6px;
            }

            .top-row-layout {
                flex-direction: column;
            }

            .top-row-layout .photo-upload-container {
                order: -1;
            }
        }

        /* ─── Top Row Layout (Academic + Type + Photo) ─── */
        .top-row-layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .top-row-fields {
            flex: 1;
            min-width: 0;
        }

        /* ─── Course restriction alert ─── */
        #course_restriction_alert {
            margin-top: 8px;
        }

        /* ─── Checkbox style ─── */
        .form-check-input {
            accent-color: var(--accent);
        }

        .form-check-label {
            font-size: 13px;
        }

        /* ─── Same-as-checkbox area ─── */
        .addr-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .addr-header label {
            margin-bottom: 0;
        }

        .addr-header .form-check {
            margin: 0;
        }

        .addr-header .form-check-label {
            font-size: 12px;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: var(--text-muted);
        }

        /* ─── NATA Section ─── */
        #nata_sec .section-card {
            border-left-color: #f59e0b;
        }

        /* ─── PG Section ─── */
        #pgDetails .section-card {
            border-left-color: var(--success);
        }

        /* ─── Sports level container ─── */
        #level {
            margin-top: 8px;
        }

        #level .form-control {
            max-width: 300px;
        }
    </style>
</head>
<body>

    <!-- ─── Header Banner ─── -->
    <header class="admission-header">
        <div class="header-inner">
            <div class="header-logo">
                <img src="<?php echo base_url('uploads/logos/' . $sch_setting->admission_logo_left); ?>" alt="College Logo">
            </div>
            <div class="header-text">
                <h1><?php echo $sch_setting->name; ?></h1>
                <p class="header-address"><?php echo $sch_setting->address; ?></p>
                <p class="header-contact">Ph: <?php echo $sch_setting->phone; ?> | Email: <?php echo $sch_setting->email; ?><?php echo isset($sch_setting->website) && $sch_setting->website ? ' | ' . $sch_setting->website : ''; ?></p>
            </div>
            <div class="header-logo">
                <img src="<?php echo base_url('uploads/logos/' . $sch_setting->admission_logo_right); ?>" alt="Accreditation Logo">
            </div>
        </div>
    </header>

    <!-- ─── Form ─── -->
    <div class="form-wrapper">
        <div class="form-title-bar">
            <h2>Application Form for Admission</h2>
        </div>

        <form action="<?php echo site_url('publicadmissionform/add_college_admission'); ?>" method="POST" enctype="multipart/form-data" id="admission_form">
            <?php if (!empty($enquiry_id)) { ?>
                <input type="hidden" name="enquiry_id" value="<?php echo htmlspecialchars($enquiry_id); ?>">
            <?php } ?>
            <?php if (!empty($employee_id)) { ?>
                <input type="hidden" name="employee_id" value="<?php echo (int)$employee_id; ?>">
            <?php } ?>

            <!-- ═══ Section 1: Application Info ═══ -->
            <div class="section-card">
                <div class="section-title">Application Information</div>
                <div class="top-row-layout">
                    <div class="top-row-fields">
                        <div class="field-grid cols-3" style="margin-bottom: 16px;">
                            <div class="field-group">
                                <label>Academic Year</label>
                                <input type="text" class="form-control" name="academic_year" id="academic_year" value="2026-2027" readonly tabindex="-1">
                            </div>
                            <div class="field-group span-2">
                                <label>Admission Type <span class="req">*</span></label>
                                <div class="admission-type-options">
                                    <div class="type-option">
                                        <input type="radio" name="courseLevel" id="ugRadio" value="ug" checked tabindex="7">
                                        <label for="ugRadio">Undergraduate (UG)</label>
                                    </div>
                                    <div class="type-option">
                                        <input type="radio" name="courseLevel" id="lateralRadio" value="lateral" tabindex="8">
                                        <label for="lateralRadio">Lateral Entry</label>
                                    </div>
                                    <div class="type-option">
                                        <input type="radio" name="courseLevel" id="pgRadio" value="pg" tabindex="9">
                                        <label for="pgRadio">Postgraduate (PG)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="field-grid cols-3" id="courseSelectionRow">
                            <div class="field-group">
                                <label>Course Apply <span class="req">*</span></label>
                                <select class="form-control" name="ug_course" id="ug_course" tabindex="5" required>
                                    <option value="">Select a Course</option>
                                    <?php if (!empty($ug_first_year_courses)) { ?>
                                        <?php foreach ($ug_first_year_courses as $course) { ?>
                                            <option value="<?php echo (int)$course['id']; ?>" data-govt-fee="<?php echo (float)$course['govt_fee']; ?>" data-mgt-fee="<?php echo (float)$course['mgt_fee']; ?>" data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>" data-is-restricted="<?php echo !empty($course['is_restricted']) ? '1' : '0'; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <select class="form-control" name="lateral_course" id="lateral_course" style="display:none;" tabindex="5">
                                    <option value="">Select a Course</option>
                                    <?php if (!empty($ug_lateral_courses)) { ?>
                                        <?php foreach ($ug_lateral_courses as $course) { ?>
                                            <option value="<?php echo (int)$course['id']; ?>" data-govt-fee="<?php echo (float)$course['govt_fee']; ?>" data-mgt-fee="<?php echo (float)$course['mgt_fee']; ?>" data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>" data-is-restricted="<?php echo !empty($course['is_restricted']) ? '1' : '0'; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <select class="form-control" name="pg_course" id="pg_course" style="display:none;" tabindex="5">
                                    <option value="">Select a Course</option>
                                    <?php if (!empty($pg_first_year_courses)) { ?>
                                        <?php foreach ($pg_first_year_courses as $course) { ?>
                                            <option value="<?php echo (int)$course['id']; ?>" data-govt-fee="<?php echo (float)$course['govt_fee']; ?>" data-mgt-fee="<?php echo (float)$course['mgt_fee']; ?>" data-is-barch="<?php echo (stripos($course['course_name'], 'ARCH') !== false) ? '1' : '0'; ?>" data-is-restricted="<?php echo !empty($course['is_restricted']) ? '1' : '0'; ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <div id="course_restriction_alert" class="alert alert-warning mt-2" style="display:none;">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>This course is filled and no vacancies currently, kindly choose other available course.
                                </div>
                            </div>
                            <div class="field-group">
                                <label>Quota Type <span class="req">*</span></label>
                                <select class="form-control" id="quota_type" name="quota_type" required tabindex="6">
                                    <option value="">Select Quota</option>
                                    <option value="government">Government</option>
                                    <option value="management" selected>Management</option>
                                </select>
                            </div>
                            <div class="field-group">
                                <label>Course Fee (Auto)</label>
                                <input type="text" class="form-control" id="course_fee_display" readonly tabindex="-1" placeholder="Select course + quota">
                                <input type="hidden" id="course_fee_total" name="course_fee_total" value="">
                            </div>
                        </div>
                    </div>
                    <div class="photo-upload-container">
                        <div id="image-upload-area" class="passport-upload-frame">
                            <input type="file" id="imageUpload" name="user_image" accept="image/*" required tabindex="4">
                            <img id="previewImage" src="" alt="Preview" class="d-none">
                            <i id="uploadIcon" class="bi bi-cloud-upload-fill upload-icon"></i>
                        </div>
                        <small id="uploadNote" class="photo-upload-note">Max size: 300KB <span class="req">*</span></small>
                        <span id="image_upload_error" class="text-danger"></span>
                    </div>
                </div>
            </div>

            <!-- ═══ Section 2: Personal Details ═══ -->
            <div class="section-card">
                <div class="section-title">Personal Details</div>
                <div class="field-grid cols-3">
                    <div class="field-group">
                        <label>Name (block letters, initial at end) <span class="req">*</span></label>
                        <input type="text" class="form-control" name="user_name" id="user_name" onkeydown="return allowAlphabets(event)" placeholder="Enter your full name" required tabindex="5" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    </div>
                    <div class="field-group">
                        <label>Father's Name <span class="req">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Father's Name" name="father_name" onkeydown="return allowAlphabets(event)" id="father_name" required tabindex="6">
                    </div>
                    <div class="field-group">
                        <label>Father's / Guardian's Mobile <span class="req">*</span></label>
                        <input type="text" class="form-control" minlength="10" maxlength="10" placeholder="10-digit mobile number" onchange="validateMobile(this)" name="father_mobile" id="father_mobile" onKeyPress="return checkIt(event);" required tabindex="7">
                    </div>
                    <div class="field-group">
                        <label>Father's Occupation <span class="req">*</span></label>
                        <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter Father's Occupation" name="father_occupation" id="father_occupation" required tabindex="8">
                    </div>
                    <div class="field-group">
                        <label>Mother's Name <span class="req">*</span></label>
                        <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter Mother's Name" name="mother_name" id="mother_name" required tabindex="9">
                    </div>
                    <div class="field-group">
                        <label>Mother's / Guardian's Mobile <span class="req">*</span></label>
                        <input type="text" class="form-control" placeholder="10-digit mobile number" name="mother_mobile" id="mother_mobile" onchange="validateMobile(this)" required minlength="10" maxlength="10" onKeyPress="return checkIt(event);" tabindex="10">
                    </div>
                    <div class="field-group">
                        <label>Mother's Occupation <span class="req">*</span></label>
                        <input type="text" class="form-control" onkeydown="return allowAlphabets(event)" placeholder="Enter Mother's Occupation" name="mother_occupation" id="mother_occupation" required tabindex="11">
                    </div>
                    <div class="field-group">
                        <label>Gender <span class="req">*</span></label>
                        <select class="form-select" id="gender" name="gender" required tabindex="12">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label>Community <span class="req">*</span></label>
                        <select class="form-control" id="community" name="community" required tabindex="12a">
                            <option value="">Select Community</option>
                            <option value="OC">OC (General)</option>
                            <option value="BC">BC</option>
                            <option value="MBC">MBC</option>
                            <option value="BCM">BCM</option>
                            <option value="SC">SC</option>
                            <option value="SCA">SCA</option>
                            <option value="ST">ST</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label>Student's Email ID <span class="req">*</span></label>
                        <input type="email" class="form-control" placeholder="Enter your Email" id="student_email" name="student_email" required tabindex="13" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        <span id="email_error" class="text-danger"></span>
                    </div>
                    <div class="field-group">
                        <label>Student's Mobile Number <span class="req">*</span></label>
                        <input type="text" step="any" class="form-control" placeholder="10-digit mobile number" id="student_mobile" onchange="validateMobile(this)" onKeyPress="return checkIt(event);" name="student_mobile" required minlength="10" maxlength="10" tabindex="14" value="<?php echo isset($mobileno) ? htmlspecialchars($mobileno) : ''; ?>">
                        <span id="mobile_error" class="text-danger"></span>
                    </div>
                    <div class="field-group">
                        <label>Student's D.O.B <span class="req">*</span></label>
                        <input type="text" class="form-control" placeholder="DD/MM/YYYY" id="dob" name="dob" required tabindex="15">
                        <span id="dob_error" class="text-danger"></span>
                    </div>
                    <div class="field-group">
                        <label>Aadhaar Number <span class="req">*</span></label>
                        <input type="text" step="any" class="form-control" placeholder="12-digit Aadhaar Number" id="aadhaar" name="aadhaar" required minlength="12" maxlength="12" onKeyPress="return checkIt(event);" tabindex="16">
                        <span id="aadhaar_error" class="text-danger"></span>
                    </div>
                    <div class="field-group">
                        <label>State <span class="req">*</span></label>
                        <select class="form-control" id="state" name="state" required tabindex="17">
                            <option value="">Select State</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label>City <span class="req">*</span></label>
                        <select class="form-control" id="city" name="city" required tabindex="18">
                            <option value="">Select City</option>
                        </select>
                        <input type="text" class="form-control mt-2" id="city_other_text" name="city_custom" placeholder="Enter your city" style="display:none;">
                    </div>
                </div>
                <div class="field-grid cols-2" style="margin-top: 16px;">
                    <div class="field-group">
                        <label>Address for Communication <span class="req">*</span></label>
                        <textarea class="form-control" placeholder="Enter your Communication Address" name="comm_addr" id="comm_addr" required tabindex="19"></textarea>
                    </div>
                    <div class="field-group">
                        <div class="addr-header">
                            <label>Permanent Address <span class="req">*</span></label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="same_as_comm" name="same_as_comm">
                                <label class="form-check-label" for="same_as_comm">Same as Communication</label>
                            </div>
                        </div>
                        <textarea class="form-control" placeholder="Enter your Permanent Address" name="perm_addr" id="perm_addr" required tabindex="20"></textarea>
                    </div>
                </div>
            </div>

            <!-- ═══ Section 3: References ═══ -->
            <div class="section-card">
                <div class="section-title">References (Optional)</div>
                <div class="ref-row">
                    <div class="field-group">
                        <label>Referrer Name</label>
                        <input type="text" class="form-control" name="referral_name" id="referral_name" onkeydown="return allowAlphabets(event)" tabindex="19">
                    </div>
                    <div class="field-group">
                        <label>Relationship</label>
                        <input type="text" class="form-control" name="relationship" id="relationship" onkeydown="return allowAlphabets(event)" tabindex="20">
                    </div>
                    <div class="field-group">
                        <label>Phone No.</label>
                        <input type="text" class="form-control" name="phone_no" id="phone_no" minlength="10" maxlength="10" onKeyPress="return checkIt(event);" tabindex="21">
                    </div>
                </div>
            </div>

            <!-- ═══ Section 4: HSC Examination ═══ -->
            <div class="section-card" id="hscDetails">
                <div class="section-title">HSC Examination Details</div>
                <div style="overflow-x: auto;">
                    <table class="marks-table">
                        <thead>
                            <tr>
                                <th style="text-align:left;">Subject</th>
                                <th>Maximum Marks</th>
                                <th>Marks Obtained</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Maths (M)</td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="total_maths" id="total_maths" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="25"></td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="maths_marks" id="maths_marks" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="26"></td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="maths_perc" id="maths_perc" readonly tabindex="-1"></td>
                            </tr>
                            <tr>
                                <td>Physics (P)</td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="total_physics" id="total_physics" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="27"></td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="physics_marks" id="physics_marks" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="28"></td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="physics_perc" id="physics_perc" readonly tabindex="-1"></td>
                            </tr>
                            <tr>
                                <td>Chemistry (C)</td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="total_chemistry" id="total_chemistry" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="29"></td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="chemistry_marks" id="chemistry_marks" onKeyPress="return checkIt(event);" onfocus="if(this.value=='0') this.value='';" onblur="if(this.value=='') this.value='0';" tabindex="30"></td>
                                <td><input type="number" step="1" min="0" max="100" value="0" class="form-control" name="chemistry_perc" id="chemistry_perc" readonly tabindex="-1"></td>
                            </tr>
                            <tr class="highlight-row">
                                <td><strong>Average: (P+C+M)/3</strong></td>
                                <td colspan="3"><input type="number" step="0.01" min="0" max="100" value="0" class="form-control" name="average_marks" id="average_marks" readonly tabindex="-1" style="max-width:200px;margin:0 auto;"></td>
                            </tr>
                            <tr id="barch_hsc_total_row" style="display:none;">
                                <td><strong>Total Marks (HSC)</strong> <span class="req">*</span><br><small class="text-muted">All subjects combined</small></td>
                                <td><input type="number" step="1" min="1" value="" class="form-control" name="hsc_total_marks" id="hsc_total_marks" tabindex="31"></td>
                                <td><input type="number" step="1" min="0" value="" class="form-control" name="hsc_marks_obtained" id="hsc_marks_obtained" tabindex="32"></td>
                                <td></td>
                            </tr>
                            <tr class="highlight-row" id="cutoff_row">
                                <td id="cutoff_label"><strong>Cut Off: (P+C)/2 + M</strong></td>
                                <td colspan="3"><input type="number" step="0.01" min="0" max="400" value="0" class="form-control" name="cutoff_marks" id="cutoff_marks" readonly tabindex="-1" style="max-width:200px;margin:0 auto;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══ Section 5: School Details (UG) ═══ -->
            <div id="ugDetails">
                <div class="section-card">
                    <div class="section-title">School Details</div>
                    <div class="field-grid cols-3">
                        <div class="field-group">
                            <label>Name of School (X Std) <span class="req">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter school name" name="school_name" id="school_name" onkeydown="return allowAlphabets(event)" tabindex="22">
                        </div>
                        <div class="field-group">
                            <label>Year of Passing (X Std) <span class="req">*</span></label>
                            <select class="form-control" name="tenth_passing" id="tenth_passing" tabindex="23">
                                <option value="">Select Year</option>
                                <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="field-group">
                            <label>X Marks (in %) <span class="req">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" placeholder="Enter marks %" name="tenth_marks_percentage" id="tenth_marks_percentage" tabindex="24">
                        </div>
                    </div>
                    <div class="field-grid cols-2" style="margin-top: 16px;">
                        <div class="field-group">
                            <label>Name of School (XII Std) <span class="req">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter 12th school name" name="school_name_xii" id="school_name_xii" onkeydown="return allowAlphabets(event)" tabindex="25">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Section 6: Lateral Entry ═══ -->
            <div id="lateralDetails" style="display:none">
                <div class="section-card">
                    <div class="section-title">Lateral Entry - School Details</div>
                    <div class="field-grid cols-3">
                        <div class="field-group">
                            <label>Name of School (X Std) <span class="req">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter school name" name="lateral_school_name" id="lateral_school_name" onkeydown="return allowAlphabets(event)" tabindex="29">
                        </div>
                        <div class="field-group">
                            <label>Year of Passing (X Std) <span class="req">*</span></label>
                            <select class="form-control" name="lateral_tenth_passing" id="lateral_tenth_passing" tabindex="30">
                                <option value="">Select Year</option>
                                <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="field-group">
                            <label>X Marks (in %) <span class="req">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" placeholder="Enter marks %" name="lateral_tenth_marks_percentage" id="lateral_tenth_marks_percentage" tabindex="31">
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">Lateral Entry - Semester Marks</div>
                    <div class="semester-panels">
                        <!-- Pre-Final Semester -->
                        <div class="semester-panel">
                            <h6>Pre-Final Semester</h6>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <div class="sem-subject-row">
                                <span class="sem-num"><?php echo $i; ?>.</span>
                                <input type="text" name="presub<?php echo $i; ?>" id="presub<?php echo $i; ?>" class="form-control" placeholder="Subject" tabindex="<?php echo 30 + ($i-1)*3 + 2; ?>">
                                <input type="number" step="1" class="form-control" max="100" min="0" name="preout<?php echo $i; ?>" id="preout<?php echo $i; ?>" value="0" onKeyPress="return checkIt(event);" tabindex="<?php echo 30 + ($i-1)*3 + 3; ?>">
                                <span class="sep-text">of</span>
                                <input type="number" step="1" class="form-control" max="100" min="0" value="0" name="premark<?php echo $i; ?>" id="premark<?php echo $i; ?>" onKeyPress="return checkIt(event);" tabindex="<?php echo 30 + ($i-1)*3 + 4; ?>">
                            </div>
                            <?php endfor; ?>
                            <div class="sem-total-row">
                                <span>Total:</span>
                                <input type="number" step="1" class="form-control" name="pretotal" id="pretotal" value="0" onKeyPress="return checkIt(event);" readonly tabindex="-1">
                                <span>of</span>
                                <input type="number" step="1" class="form-control" name="pretotal1" id="pretotal1" readonly onKeyPress="return checkIt(event);" value="0" tabindex="-1">
                            </div>
                        </div>

                        <!-- Final Semester -->
                        <div class="semester-panel">
                            <h6>Final Semester</h6>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <div class="sem-subject-row">
                                <span class="sem-num"><?php echo $i; ?>.</span>
                                <input type="text" name="finalsub<?php echo $i; ?>" id="finalsub<?php echo $i; ?>" class="form-control" placeholder="Subject" tabindex="<?php echo 49 + ($i-1)*3 + 1; ?>">
                                <input type="number" step="1" class="form-control" max="100" min="0" name="finalout<?php echo $i; ?>" id="finalout<?php echo $i; ?>" value="0" onKeyPress="return checkIt(event);" tabindex="<?php echo 49 + ($i-1)*3 + 2; ?>">
                                <span class="sep-text">of</span>
                                <input type="number" step="1" class="form-control" max="100" min="0" value="0" name="finalmark<?php echo $i; ?>" id="finalmark<?php echo $i; ?>" onKeyPress="return checkIt(event);" tabindex="<?php echo 49 + ($i-1)*3 + 3; ?>">
                            </div>
                            <?php endfor; ?>
                            <div class="sem-total-row">
                                <span>Total:</span>
                                <input type="number" step="1" class="form-control" name="finaltotal" id="finaltotal" value="0" readonly onKeyPress="return checkIt(event);" tabindex="-1">
                                <span>of</span>
                                <input type="number" step="1" class="form-control" name="finaltotal1" id="finaltotal1" readonly onKeyPress="return checkIt(event);" value="0" tabindex="-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Section 7: NATA Score ═══ -->
            <div id="nata_sec" style="display:none">
                <div class="section-card">
                    <div class="section-title">NATA Score (B.Arch Only)</div>
                    <p class="text-muted" style="font-size:13px; margin-bottom:16px;">For B.Arch courses, admission eligibility is based on NATA Score % -- not cut-off marks.</p>
                    <div class="field-grid cols-3">
                        <div class="field-group">
                            <label>NATA Score % <span class="req">*</span></label>
                            <input class="form-control" placeholder="Enter NATA Score %" name="nata_score" id="nata_score" tabindex="68">
                        </div>
                        <div class="field-group">
                            <label>Application Form</label>
                            <input class="form-control" placeholder="Enter Application Form" name="application_number" id="application_number" tabindex="69">
                        </div>
                        <div class="field-group">
                            <label>Year</label>
                            <input class="form-control" placeholder="Enter Year" name="nata_year" id="nata_year" tabindex="70">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Section 8: PG Details ═══ -->
            <div id="pgDetails" style="display: none;">
                <div class="section-card">
                    <div class="section-title">Academic Details (PG)</div>
                    <div class="field-grid cols-2">
                        <div class="field-group">
                            <label>UG Course Studied</label>
                            <input class="form-control" type="text" placeholder="Enter your UG course" name="exam_passed" id="exam_passed" tabindex="71">
                        </div>
                        <div class="field-group">
                            <label>Major Stream</label>
                            <input type="text" class="form-control" placeholder="Enter your major stream" name="branch" id="branch" tabindex="72">
                        </div>
                        <div class="field-group">
                            <label>Year of Passing</label>
                            <input type="text" class="form-control" placeholder="Enter your Year" onKeyPress="return checkIt(event);" name="yop" id="yop" tabindex="74">
                        </div>
                        <div class="field-group">
                            <label>Name of the College</label>
                            <input type="text" class="form-control" minlength="2" maxlength="200" placeholder="Enter your College" name="noc" id="noc" tabindex="75">
                        </div>
                        <div class="field-group">
                            <label>University <span class="req">*</span></label>
                            <select class="form-control" id="university_id" name="university_id" tabindex="76" required>
                                <option value="">Select University</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label>TANCET / PGETA Application No.</label>
                            <input type="text" step="any" class="form-control" placeholder="Enter Application Number" name="pg_app_num" id="pg_app_num" tabindex="77">
                        </div>
                        <div class="field-group">
                            <label>TANCET / PGETA Exam Year</label>
                            <input type="text" step="any" class="form-control" minlength="4" maxlength="4" onKeyPress="return checkIt(event);" placeholder="Enter examination year" name="exam_year" id="exam_year" tabindex="78">
                        </div>
                        <div class="field-group">
                            <label>TANCET / PGETA Exam Score</label>
                            <input type="number" step="any" class="form-control" placeholder="Enter your Score" name="exam_score" id="exam_score" tabindex="79">
                        </div>
                    </div>
                    <div class="field-grid cols-2" style="margin-top:16px;">
                        <div class="field-group">
                            <label>UG Degree Score / Percentage <span class="req">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" max="100" class="form-control" placeholder="0 - 100" name="ug_degree_score" id="ug_degree_score" tabindex="80"
                                    oninput="if(parseFloat(this.value)>100){this.value=100;} if(parseFloat(this.value)<0){this.value=0;}">
                                <span class="input-group-text">%</span>
                            </div>
                            <small id="ug_degree_score_error" class="text-danger" style="display:none;">Value must be between 0 and 100.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Section 9: Additional Information ═══ -->
            <div class="section-card">
                <div class="section-title">Additional Information</div>

                <div class="info-item">
                    <div class="info-label">UG Alumni of Meenakshi Group of Institutions</div>
                    <input type="file" class="form-control" name="bonafide" id="bonafide" tabindex="80" style="max-width:400px;">
                    <small class="text-muted">Attach Bonafide Certificate (PDF, max 5MB)</small>
                </div>

                <div class="info-item">
                    <div class="info-label">Eminent Sports Person</div>
                    <div class="radio-group">
                        <label><input type="radio" name="sports" id="sports" value="Yes" tabindex="81"> Yes</label>
                        <label><input type="radio" name="sports" id="sports" value="No" checked tabindex="82"> No</label>
                    </div>
                    <div id="level" style="margin-top: 10px;">
                        <label class="field-label" style="margin-bottom:6px;">Level</label>
                        <select class="form-control" name="sports_level" id="sports_level" tabindex="83" style="max-width:300px;">
                            <option value="">Select Level</option>
                            <option value="District">District Level</option>
                            <option value="State">State Level</option>
                        </select>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Ward of Ex-Service Men</div>
                    <div class="radio-group">
                        <label><input type="radio" name="exservice" value="Yes" tabindex="84"> Yes</label>
                        <label><input type="radio" name="exservice" value="No" checked tabindex="85"> No</label>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Differently Abled Person</div>
                    <div class="radio-group">
                        <label><input type="radio" name="differently_abled" value="Yes" onclick="showDisabilityType(true)" tabindex="86"> Yes</label>
                        <label><input type="radio" name="differently_abled" value="No" onclick="showDisabilityType(false)" checked tabindex="87"> No</label>
                    </div>
                    <div id="disabilityType" style="display: none; margin-top: 10px;">
                        <label class="field-label">If Yes, type of disability</label>
                        <input type="text" class="form-control" placeholder="Enter type of disability" name="disability_type" id="disability_type" tabindex="88" style="max-width:400px;">
                    </div>
                </div>
            </div>

            <!-- ─── Hidden + Submit ─── -->
            <input type="hidden" name="payment_option" id="payment_option" value="">
            <div class="submit-area">
                <button class="btn-submit" type="button" id="submit_application_btn" name="submit" tabindex="89">Submit Application</button>
            </div>
        </form>
    </div>

    <!-- ─── Payment Option Modal ─── -->
    <div class="modal fade" id="paymentOptionModal" tabindex="-1" aria-labelledby="paymentOptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentOptionModalLabel">Complete Your Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>How would you like to proceed with the application fee?</p>
                    <button type="button" class="btn btn-primary btn-lg mt-3" id="payOnlineBtn">Pay Fee Online</button>
                    <button type="button" class="btn btn-secondary btn-lg mt-3" id="payLaterBtn">Pay Later</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Error Modal ─── -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Your application has been submitted, please contact Admission team!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="errorModalContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showDisabilityType(show) {
        document.getElementById("disabilityType").style.display = show ? "block" : "none";
    }
</script>
<script>
    // Initialize with UG selected by default
    document.addEventListener("DOMContentLoaded", function() {
        toggleCourseSelection();
        loadUniversities();
    });

    function toggleCourseSelection() {
        const ugRadio = document.getElementById("ugRadio");
        const lateralRadio = document.getElementById("lateralRadio");
        const pgRadio = document.getElementById("pgRadio");

        const ugDetails = document.getElementById("ugDetails");
        const lateralDetails = document.getElementById("lateralDetails");
        const pgDetails = document.getElementById("pgDetails");
        const hscDetails = document.getElementById("hscDetails");

        if(ugRadio.checked) {
            // Show UG details, hide others
            ugDetails.style.display = "block";
            lateralDetails.style.display = "none";
            pgDetails.style.display = "none";
            hscDetails.style.display = "block";

            // Show/hide course dropdowns in top row
            document.getElementById("ug_course").style.display = "block";
            document.getElementById("lateral_course").style.display = "none";
            document.getElementById("pg_course").style.display = "none";

            // Set required fields for UG
            setRequiredFields(true, false, false);
            setHscRequired(true);
        }
        else if(lateralRadio.checked) {
            // Show Lateral details, hide others
            ugDetails.style.display = "none";
            lateralDetails.style.display = "block";
            pgDetails.style.display = "none";
            hscDetails.style.display = "none";

            // Show/hide course dropdowns in top row
            document.getElementById("ug_course").style.display = "none";
            document.getElementById("lateral_course").style.display = "block";
            document.getElementById("pg_course").style.display = "none";

            // Set required fields for Lateral
            setRequiredFields(false, true, false);
            setHscRequired(false);
            clearHscFields();
        }
        else if(pgRadio.checked) {
            // Show PG details, hide others
            ugDetails.style.display = "none";
            lateralDetails.style.display = "none";
            pgDetails.style.display = "block";
            hscDetails.style.display = "none";

            // Show/hide course dropdowns in top row
            document.getElementById("ug_course").style.display = "none";
            document.getElementById("lateral_course").style.display = "none";
            document.getElementById("pg_course").style.display = "block";

            // Set required fields for PG
            setRequiredFields(false, false, true);
            setHscRequired(false);
            clearHscFields();
        }
    }

    function setRequiredFields(ugRequired, lateralRequired, pgRequired) {
        // UG fields
        $("#ug_course").prop("required", ugRequired);
        $("#school_name").prop("required", ugRequired);
        $("#tenth_passing").prop("required", ugRequired);
        $("#tenth_marks_percentage").prop("required", ugRequired);
        $("#school_name_xii").prop("required", ugRequired);

        // Lateral fields
        $("#lateral_course").prop("required", lateralRequired);
        $("#lateral_school_name").prop("required", lateralRequired);
        $("#lateral_tenth_passing").prop("required", lateralRequired);
        $("#lateral_tenth_marks_percentage").prop("required", lateralRequired);
        for (let i = 1; i <= 6; i++) {
            $(`#presub${i}`).prop("required", lateralRequired);
            $(`#preout${i}`).prop("required", lateralRequired);
            $(`#premark${i}`).prop("required", lateralRequired);
            $(`#finalsub${i}`).prop("required", lateralRequired);
            $(`#finalout${i}`).prop("required", lateralRequired);
            $(`#finalmark${i}`).prop("required", lateralRequired);
        }

        // PG fields
        $("#pg_course").prop("required", pgRequired);
        $("#exam_passed").prop("required", pgRequired);
        $("#branch").prop("required", pgRequired);
        $("#yop").prop("required", pgRequired);
        $("#noc").prop("required", pgRequired);
        $("#university_id").prop("required", pgRequired);
        $("#exam_score").prop("required", pgRequired);
        $("#exam_year").prop("required", pgRequired);
        $("#pg_app_num").prop("required", pgRequired);
        $("#ug_degree_score").prop("required", pgRequired);
    }

    function setHscRequired(isRequired) {
        $("#maths_marks").prop("required", isRequired);
        $("#total_maths").prop("required", isRequired);
        $("#physics_marks").prop("required", isRequired);
        $("#total_physics").prop("required", isRequired);
        $("#chemistry_marks").prop("required", isRequired);
        $("#total_chemistry").prop("required", isRequired);
    }

    function clearHscFields() {
        const hscFieldIds = [
            "total_maths",
            "maths_marks",
            "maths_perc",
            "total_physics",
            "physics_marks",
            "physics_perc",
            "total_chemistry",
            "chemistry_marks",
            "chemistry_perc",
            "average_marks",
            "cutoff_marks"
        ];

        hscFieldIds.forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = "";
            }
        });
    }

    // Add event listeners to all radio buttons
    document.getElementById("ugRadio").addEventListener("change", toggleCourseSelection);
    document.getElementById("lateralRadio").addEventListener("change", toggleCourseSelection);
    document.getElementById("pgRadio").addEventListener("change", toggleCourseSelection);

    function getSelectedCourseOption() {
        const courseLevel = $("input[name='courseLevel']:checked").val();
        if (courseLevel === 'ug') {
            return $('#ug_course option:selected');
        }
        if (courseLevel === 'lateral') {
            return $('#lateral_course option:selected');
        }
        if (courseLevel === 'pg') {
            return $('#pg_course option:selected');
        }
        return null;
    }

    function isBarchSelected() {
        const selectedOption = getSelectedCourseOption();
        return selectedOption && selectedOption.data('is-barch') == 1;
    }

    function updateNataVisibility() {
        const barch = isBarchSelected();
        if (barch) {
            $("#nata_sec").show();
            $("#nata_score").prop('required', true);
            $("#application_number").prop('required', true);
            $("#nata_year").prop('required', true);
            // B.Arch: show cutoff row with NATA formula label
            $("#cutoff_row").show();
            $("#cutoff_label").html('<strong>Cut Off: NATA + (Obtained/Total)×200</strong>');
            $("#barch_hsc_total_row").show();
        } else {
            $("#nata_sec").hide();
            $("#nata_score").prop('required', false);
            $("#application_number").prop('required', false);
            $("#nata_year").prop('required', false);
            // Non-B.Arch: standard PCM formula label
            $("#cutoff_row").show();
            $("#cutoff_label").html('<strong>Cut Off: (P+C)/2 + M</strong>');
        }
        // Recalculate after label/formula change
        if (typeof calculateTotal === 'function') { calculateTotal(); }
    }

    function updateCourseFee() {
        const quotaType = $('#quota_type').val();
        const selectedOption = getSelectedCourseOption();
        if (!selectedOption || !selectedOption.val() || !quotaType) {
            $('#course_fee_display').val('');
            $('#course_fee_total').val('');
            return;
        }

        const govtFee = parseFloat(selectedOption.data('govt-fee') || 0);
        const mgtFee = parseFloat(selectedOption.data('mgt-fee') || 0);
        const fee = quotaType === 'government' ? govtFee : mgtFee;

        $('#course_fee_total').val(fee.toFixed(2));
        $('#course_fee_display').val(new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
        }).format(fee));
    }

    // NATA section toggle for B.Arch and fee auto-calc
    $("#ug_course, #lateral_course, #pg_course, #quota_type, input[name='courseLevel']").on('change', function(){
        updateNataVisibility();
        updateCourseFee();
    });

    updateNataVisibility();
    updateCourseFee();

    // Load universities for PG dropdown
    function loadUniversities() {
        $.ajax({
            url: '<?php echo base_url("publicadmissionform/get_universities"); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && Array.isArray(data)) {
                    let select = $('#university_id');
                    select.empty();
                    select.append('<option value="">Select University</option>');

                    data.forEach(function(uni) {
                        select.append(`<option value="${uni.id}">${uni.name}</option>`);
                    });
                }
            },
            error: function(err) {
                console.error('Error loading universities:', err);
            }
        });
    }
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#disabilityType').hide();
        $('#level').hide();

        $('input[name="differently_abled"]').change(function() {
            if ($(this).val() === 'Yes') {
                $('#disabilityType').show();
                $('#disabilityType input').prop('required', true);
            } else {
                $('#disabilityType').hide();
                $('#disabilityType input').prop('required', false);
            }
        });

        $('input[name="sports"]').change(function() {
            if ($(this).val() === 'Yes') {
                $('select.form-control').parent().show();
                $('select.form-control').prop('required', true);
            } else {
                $('select.form-control').parent().hide();
                $('select.form-control').prop('required', false);
            }
        });
    });

</script>
<script>
    $(document).ready(function () {

        $("#imageUpload").change(function (event) {
            let file = event.target.files[0];
            if (file) {
                let fileType = file.type;
                let fileSize = file.size; // in bytes
                const maxFileSize = 300 * 1024; // 300KB

                let validTypes = ["image/jpeg", "image/png"];
                if (!validTypes.includes(fileType)) {
                    alert("Only JPG and PNG images are allowed.");
                    $("#imageUpload").val(""); // Reset input
                    return;
                }
                if (fileSize > maxFileSize) {
                    alert("File size must be less than 300KB.");
                    $("#imageUpload").val(""); // Reset input
                    return;
                }
                let reader = new FileReader();
                reader.onload = function (e) {
                    $("#previewImage").attr("src", e.target.result).removeClass("d-none");
                    $("#uploadIcon, #uploadText, #uploadNote").addClass("d-none");
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
<script>
    function checkIt(evt) {
        evt = (evt) ? evt : window.event
        var charCode = (evt.which) ? evt.which : evt.keyCode
        // Allow tab key (charCode 9)
        if (charCode == 9) {
            return true;
        }
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            status = "This field accepts numbers only."
            return false
        }
        status = "";
        return true
    }
</script>
<script>
        function allowAlphabets(evt) {
            evt = evt || window.event;
            var charCode = evt.which || evt.keyCode;
            // Allow tab key (charCode 9)
            if (charCode == 9) {
                return true;
            }
            if ((charCode >= 65 && charCode <= 90) ||
                (charCode >= 97 && charCode <= 122) ||
                charCode == 8 ||
                charCode == 32) {
                return true;
            }
            return false;
        }</script>
<script type="text/javascript">
   function validateMobile(input) {
    var mobileNumber = input.value.trim();
    if (!/^\d{10}$/.test(mobileNumber)) {
        alert("Please enter a valid 10-digit mobile number.");
        input.value = "";
        return false;
    }
    return true;
}
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#bonafide').change(function() {
            var file = this.files[0];
            var fileName = file.name;
            var fileExt = fileName.split('.').pop().toLowerCase();
            var fileSize = file.size;
        var maxSize = 5 * 1024 * 1024; // 5MBs
        if (fileExt !== 'pdf' || (file.type !== '' && file.type !== 'application/pdf')) {
            alert('Only PDF files are allowed!');
            $(this).val('');
            return false;
        }
        if (fileSize > maxSize) {
            alert('File size exceeds 5MB limit!');
            $(this).val('');
            return false;
        }
        return true;
    });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        function calculatePercentage(marksId, totalId, percId) {
            let marks = parseFloat($(marksId).val());
            let total = parseFloat($(totalId).val());

            // Validate total marks
            if (total > 100) {
                alert("Maximum Marks cannot exceed 100.");
                $(totalId).val(100);
                total = 100;
            }
            if (total < 0) {
                alert("Maximum Marks cannot be negative.");
                $(totalId).val(0);
                total = 0;
            }

            // Validate marks obtained
            if (marks > total) {
                alert("Marks Obtained cannot exceed Maximum Marks.");
                $(marksId).val(total);
                marks = total;
            }
            if (marks < 0) {
                alert("Marks Obtained cannot be negative.");
                $(marksId).val(0);
                marks = 0;
            }

            if (total === 0) {
                $(percId).val("0.00");
                return 0;
            }
            let percentage = (marks / total) * 100;
            $(percId).val(percentage.toFixed(2));
            return marks;
        }
        function calculateTotal() {
            let mathsMarks     = calculatePercentage("#maths_marks", "#total_maths", "#maths_perc");
            let physicsMarks   = calculatePercentage("#physics_marks", "#total_physics", "#physics_perc");
            let chemistryMarks = calculatePercentage("#chemistry_marks", "#total_chemistry", "#chemistry_perc");

            setTimeout(function() {
                let mathsPerc     = parseFloat($("#maths_perc").val() || 0);
                let physicsPerc   = parseFloat($("#physics_perc").val() || 0);
                let chemistryPerc = parseFloat($("#chemistry_perc").val() || 0);

                // Average = (M+P+C)/3  — same for all courses
                let average = (mathsPerc + physicsPerc + chemistryPerc) / 3;
                $("#average_marks").val(average.toFixed(2));

                // Cut Off formula depends on course
                let cutoff;
                if (typeof isBarchSelected === 'function' && isBarchSelected()) {
                    // B.Arch: Cut Off = NATA Score + ((hsc_marks_obtained / hsc_total_marks) * 200)
                    let nataScore   = parseFloat($("#nata_score").val() || 0);
                    let hscTotal    = parseFloat($("#hsc_total_marks").val() || 0);
                    let hscObtained = parseFloat($("#hsc_marks_obtained").val() || 0);
                    cutoff = (hscTotal > 0) ? nataScore + ((hscObtained / hscTotal) * 200) : 0;
                } else {
                    // Engineering / others: Cut Off = (P+C)/2 + M
                    cutoff = (physicsPerc + chemistryPerc) / 2 + mathsPerc;
                }
                $("#cutoff_marks").val(cutoff.toFixed(2));
            }, 10);
        }
        $("#maths_marks, #total_maths, #physics_marks, #total_physics, #chemistry_marks, #total_chemistry").on("input", function() {
            calculateTotal();
        });
        // Recalculate cut-off whenever NATA score or HSC totals change (B.Arch only)
        $(document).on("input", "#nata_score, #hsc_total_marks, #hsc_marks_obtained", function() {
            calculateTotal();
        });
    });
    $(document).ready(function() {
        function calculateTotalPreSem() {
            let totalMarksObtained = 0;
            let totalMaxMarks = 0;
            for (let i = 1; i <= 6; i++) {
                let markId = "#premark" + i;
                let outId = "#preout" + i;
                let marks = parseFloat($(markId).val());
                let total = parseFloat($(outId).val());

                // Validate total marks (preoutX)
                if (total > 100) {
                    alert("Pre-Final Semester Maximum Marks cannot exceed 100.");
                    $(outId).val(100);
                    total = 100;
                }
                if (total < 0) {
                    alert("Pre-Final Semester Maximum Marks cannot be negative.");
                    $(outId).val(0);
                    total = 0;
                }

                // Validate marks obtained (premarkX)
                if (marks > total) {
                    alert("Pre-Final Semester Marks Obtained cannot exceed Maximum Marks.");
                    $(markId).val(total);
                    marks = total;
                }
                if (marks < 0) {
                    alert("Pre-Final Semester Marks Obtained cannot be negative.");
                    $(markId).val(0);
                    marks = 0;
                }

                totalMarksObtained += marks;
                totalMaxMarks += total;
            }
            $("#pretotal").val(totalMarksObtained);
            $("#pretotal1").val(totalMaxMarks);
        }
        $("input[id^='premark'], input[id^='preout']").on("input", function() {
            calculateTotalPreSem();
        });
    });
    $(document).ready(function() {
        function calculateFinalTotalSem() {
            let totalMarksObtained = 0;
            let totalMaxMarks = 0;
            for (let i = 1; i <= 6; i++) {
                let markId = "#finalmark" + i;
                let outId = "#finalout" + i;
                let marks = parseFloat($(markId).val());
                let total = parseFloat($(outId).val());

                // Validate total marks (finaloutX)
                if (total > 100) {
                    alert("Final Semester Maximum Marks cannot exceed 100.");
                    $(outId).val(100);
                    total = 100;
                }
                if (total < 0) {
                    alert("Final Semester Maximum Marks cannot be negative.");
                    $(outId).val(0);
                    total = 0;
                }

                // Validate marks obtained (finalmarkX)
                if (marks > total) {
                    alert("Final Semester Marks Obtained cannot exceed Maximum Marks.");
                    $(markId).val(total);
                    marks = total;
                }
                if (marks < 0) {
                    alert("Final Semester Marks Obtained cannot be negative.");
                    $(markId).val(0);
                    marks = 0;
                }

                totalMarksObtained += marks;
                totalMaxMarks += total;
            }
            $("#finaltotal").val(totalMarksObtained);
            $("#finaltotal1").val(totalMaxMarks);
        }
        $("input[id^='finalmark'], input[id^='finalout']").on("input", function() {
            calculateFinalTotalSem();
        });
    });
</script>
<script>
$(document).ready(function() {
    // Function to clear all previous error messages
    function clearErrors() {
        $('.form-error').remove(); // Remove dynamically added error spans
        $('#email_error, #image_upload_error, #mobile_error, #dob_error, #aadhaar_error').text('');
    }

    // Function to display errors from the server
    function displayErrors(errors) {
        clearErrors();
        $.each(errors, function(key, value) {
            if (value) {
                const field = $('[name="' + key + '"]');
                let errorSpan = '<span class="text-danger form-error">' + value + '</span>';

                if (key === 'user_image') {
                    $('#image-upload-area').after(errorSpan);
                } else if (field.length > 0) {
                    field.last().after(errorSpan); // Place error after the field
                }
            }
        });
    }

    // Course restriction check — bypassed when form is opened by staff via employee_id link
    var isStaffReferral = ($('input[name="employee_id"]').length > 0 && parseInt($('input[name="employee_id"]').val()) > 0);

    function checkCourseRestriction($select) {
        if (isStaffReferral) {
            // Staff referrals are always allowed regardless of seat restriction
            $('#course_restriction_alert').hide();
            $('#submit_application_btn').prop('disabled', false);
            return;
        }
        var selected = $select.find('option:selected');
        if (selected.val() && selected.data('is-restricted') == '1') {
            $('#course_restriction_alert').show();
            $('#submit_application_btn').prop('disabled', true);
        } else {
            $('#course_restriction_alert').hide();
            $('#submit_application_btn').prop('disabled', false);
        }
    }

    $('#ug_course').on('change', function() { checkCourseRestriction($(this)); });
    $('#lateral_course').on('change', function() { checkCourseRestriction($(this)); });
    $('#pg_course').on('change', function() { checkCourseRestriction($(this)); });

    // Handler for the main submit button, which now just opens the modal
    $('#submit_application_btn').on('click', function(e) {
        e.preventDefault();
        console.log('Submit Application button clicked.');

        const form = document.getElementById('admission_form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        console.log('Client-side validation passed. Showing payment modal.');

        // Clean up any existing modal instance first
        const modalEl = document.getElementById('paymentOptionModal');
        const existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) {
            existingModal.dispose();
        }

        // Remove any backdrops
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');

        // Create fresh modal instance
        const paymentOptionModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        paymentOptionModal.show();
    });

    // Function to handle form submission via AJAX
    function submitForm(paymentOption) {
        console.log('Submitting form with payment option:', paymentOption);
        $('#payment_option').val(paymentOption);

        // Close the modal first
        const modalEl = document.getElementById('paymentOptionModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }

        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');

        var formData = new FormData(document.getElementById('admission_form'));
        const submitBtn = $('#submit_application_btn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...');

        clearErrors();

        $.ajax({
            url: '<?php echo site_url('publicadmissionform/ajax_add_college_admission'); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.status === 'success') {
                    console.log('Success, redirecting to:', response.redirect_url);
                    window.location.href = response.redirect_url;
                } else if (response.status === 'fail') {
                    console.log('Validation errors:', response.error);
                    displayErrors(response.error);

                    // Show errors in modal
                    let errorHtml = '<ul class="list-unstyled mb-0">';
                    $.each(response.error, function(field, message) {
                        errorHtml += '<li class="mb-2"><i class="bi bi-x-circle-fill text-danger me-2"></i>' + message + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#errorModalContent').html(errorHtml);

                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();

                    submitBtn.prop('disabled', false).text('Submit Application');
                } else {
                    console.error('Unknown response:', response);
                    $('#errorModalContent').html('<p><i class="bi bi-x-circle-fill text-danger me-2"></i>An unknown error occurred. Please try again.</p>');
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    submitBtn.prop('disabled', false).text('Submit Application');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                console.error('Response Text:', jqXHR.responseText);
                $('#errorModalContent').html('<p><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>A server error occurred. Please try again later.</p>');
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
                submitBtn.prop('disabled', false).text('Submit Application');
            }
        });
    }

    // Handlers for modal buttons
    $(document).on('click', '#payOnlineBtn', function() {
        console.log('Pay Online button clicked - event delegated');
        submitForm('pay_online');
    });
    $(document).on('click', '#payLaterBtn', function() {
        console.log('Pay Later button clicked - event delegated');
        submitForm('pay_later');
    });

    // Reset state when navigating back to this page (bfcache)
    window.addEventListener('pageshow', function(event) {
        console.log('pageshow event fired, persisted:', event.persisted);

        if (event.persisted) {
            // Page was restored from bfcache (back button was used)
            console.log('Resetting form state after back button navigation');

            // Re-enable submit button and reset text
            $('#submit_application_btn').prop('disabled', false).text('Submit Application');

            // Clear payment option
            $('#payment_option').val('');

            // Destroy any existing modal instance and remove backdrops
            const modalEl = document.getElementById('paymentOptionModal');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.dispose(); // Completely destroy the modal instance
                }
            }

            // Force remove any modal artifacts
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', '').css('padding-right', '');

            // Clear any error messages
            clearErrors();
        }
    });

    // --- Existing client-side validation handlers for instant feedback ---
    $('#student_email').on('change', function() {
        var email = $(this).val(), academic_year = $('#academic_year').val();
        $('#email_error').text('');
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $.post("<?php echo site_url('publicadmissionform/check_admissions_data'); ?>", { email_id: email, academic_year: academic_year }, function(result) {
                if (JSON.parse(result).count > 0) {
                    $('#email_error').text('Email already submitted for this academic year.');
                    $('#student_email').val("");
                }
            });
        } else if (email) { $('#email_error').text('Invalid email format.'); }
    });

    $('#student_mobile').on('change', function() {
        var mobile = $(this).val();
        $('#mobile_error').text('');
        if (/^[0-9]{10}$/.test(mobile)) {
            $.post("<?php echo site_url('publicadmissionform/check_admissions_data'); ?>", { mobile_no: mobile }, function(result) {
                if (JSON.parse(result).total != 1) {
                    $('#mobile_error').text('Mobile number already exists.');
                    $('#student_mobile').val("");
                }
            });
        } else if (mobile) { $('#mobile_error').text('Must be 10 digits.'); }
    });

    $('#aadhaar').on('change', function() {
        var aadhaar = $(this).val();
        $('#aadhaar_error').text('');
        if (/^[0-9]{12}$/.test(aadhaar)) {
            $.post("<?php echo site_url('publicadmissionform/check_admissions_data'); ?>", { aadhaar_no: aadhaar }, function(result) {
                if (JSON.parse(result).total != 1) {
                    $('#aadhaar_error').text('Aadhaar number already exists.');
                    $('#aadhaar').val("");
                }
            });
        } else if (aadhaar) { $('#aadhaar_error').text('Must be 12 digits.'); }
    });

    $('#dob').on('change', function() {
        $('#dob_error').text('');
    });

    // Load India states and cities
    let statesData = {};

    $.ajax({
        url: '<?php echo base_url("backend/json-files/india_states_cities.json"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            const stateSelect = document.getElementById('state');

            // Sort states alphabetically
            data.states.sort((a, b) => a.name.localeCompare(b.name));

            data.states.forEach(function(state) {
                // Sort cities alphabetically
                state.cities.sort((a, b) => a.localeCompare(b));
                statesData[state.name] = state.cities;

                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });
        }
    });

    // Populate cities when state is selected
    document.getElementById('state').addEventListener('change', function() {
        const selectedState = this.value;
        const citySelect = document.getElementById('city');
        citySelect.innerHTML = '<option value="">Select City</option>';

        if (statesData[selectedState]) {
            statesData[selectedState].forEach(function(city) {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
        const othersOpt = document.createElement('option');
        othersOpt.value = 'Others';
        othersOpt.textContent = 'Others';
        citySelect.appendChild(othersOpt);
        $('#city_other_text').hide().val('').removeAttr('required');
    });

    $('#city').on('change', function() {
        if ($(this).val() === 'Others') {
            $('#city_other_text').show().attr('required', true);
        } else {
            $('#city_other_text').hide().val('').removeAttr('required');
        }
    });

    function copyCommunicationToPermanent() {
        var checkbox = document.getElementById('same_as_comm');
        var commAddr = document.getElementById('comm_addr');
        var permAddr = document.getElementById('perm_addr');

        console.log('Checkbox checked:', checkbox.checked);
        console.log('Comm Address:', commAddr.value);

        if (checkbox.checked) {
            permAddr.value = commAddr.value;
            permAddr.style.backgroundColor = '#f0f8ff';
            console.log('Copied to Permanent Address:', permAddr.value);
        } else {
            permAddr.value = '';
            permAddr.style.backgroundColor = '';
        }
    }

    // Listen for changes on checkbox
    document.getElementById('same_as_comm').addEventListener('change', function() {
        copyCommunicationToPermanent();
    });

    // Listen for changes on communication address if checkbox is checked
    document.getElementById('comm_addr').addEventListener('input', function() {
        var checkbox = document.getElementById('same_as_comm');
        if (checkbox.checked) {
            document.getElementById('perm_addr').value = this.value;
        }
    });

    // Initialize Flatpickr for DOB picker
    flatpickr('#dob', {
        mode: 'single',
        dateFormat: 'd/m/Y',
        altInput: false,
        maxDate: new Date(),
        yearRange: [1950, new Date().getFullYear()],
        monthSelectorType: 'dropdown',
        allowInput: true,
        enableTime: false,
        time_24hr: false,
        locale: 'en'
    });

    // tenth_passing and lateral_tenth_passing are now <select> year dropdowns — no Flatpickr needed
});
</script>
</body>
</html>