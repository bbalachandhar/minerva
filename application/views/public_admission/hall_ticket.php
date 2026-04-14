<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Ticket – <?php echo htmlspecialchars($exam->exam); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #222;
            background: #fff;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 0;
            background: #fff;
        }

        /* ── Header ── */
        .hall-header { width: 100%; }
        .hall-header img { width: 100%; height: auto; display: block; }

        /* ── Title bar ── */
        .title-bar {
            background: #1a5c84;
            color: #fff;
            text-align: center;
            padding: 8px 0 6px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* ── Body ── */
        .hall-body { padding: 18px 24px; }

        /* ── Candidate section ── */
        .candidate-block {
            display: flex;
            gap: 16px;
            border: 1.5px solid #1a5c84;
            border-radius: 4px;
            padding: 14px;
            margin-bottom: 16px;
        }
        .candidate-photo {
            width: 110px;
            min-width: 110px;
            text-align: center;
        }
        .candidate-photo img {
            width: 100px;
            height: 110px;
            object-fit: cover;
            border: 1.5px solid #aaa;
            border-radius: 4px;
        }
        .candidate-photo .photo-label {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
        }
        .candidate-info { flex: 1; }
        .candidate-info table { width: 100%; border-collapse: collapse; }
        .candidate-info table tr td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 13px;
        }
        .candidate-info table tr td:first-child {
            font-weight: bold;
            width: 38%;
            color: #444;
        }
        .candidate-info table tr td:last-child { color: #111; }

        /* ── Exam details section ── */
        .section-title {
            background: #1a5c84;
            color: #fff;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 0;
            border-radius: 3px 3px 0 0;
        }
        .exam-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #1a5c84;
            border-top: none;
            margin-bottom: 16px;
            border-radius: 0 0 3px 3px;
        }
        .exam-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #dde3ea;
            font-size: 13px;
        }
        .exam-table tr:last-child td { border-bottom: none; }
        .exam-table td.label-col {
            font-weight: bold;
            width: 38%;
            color: #444;
            background: #f7f9fc;
        }

        /* ── Instructions ── */
        .instructions-box {
            border: 1.5px solid #ccc;
            border-radius: 4px;
            padding: 12px 14px;
            margin-bottom: 18px;
            background: #fffde7;
        }
        .instructions-box h4 {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #7a5200;
        }
        .instructions-box ol {
            padding-left: 18px;
            color: #444;
        }
        .instructions-box ol li { margin-bottom: 4px; line-height: 1.6; }

        /* ── Signature strip ── */
        .sig-strip {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            margin-bottom: 24px;
        }
        .sig-box {
            text-align: center;
            width: 30%;
            border-top: 1.5px solid #333;
            padding-top: 4px;
            font-size: 11px;
            color: #555;
        }

        /* ── Footer ── */
        .hall-footer {
            border-top: 2px solid #1a5c84;
            padding: 10px 24px 14px;
            font-size: 11px;
            color: #555;
            text-align: center;
        }

        /* ── Print button (screen only) ── */
        .print-btn-bar {
            text-align: center;
            padding: 14px 0;
            background: #f4f4f4;
            border-bottom: 1px solid #ddd;
        }
        .print-btn-bar button {
            background: #1a5c84;
            color: #fff;
            border: none;
            padding: 9px 28px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 6px;
        }
        .print-btn-bar button.close-btn {
            background: #666;
        }

        @media print {
            .print-btn-bar { display: none !important; }
            .page { margin: 0; width: 100%; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

<!-- Screen-only print/close bar -->
<div class="print-btn-bar">
    <button onclick="window.print()">&#128438; Print Hall Ticket</button>
    <button class="close-btn" onclick="window.close()">&#x2715; Close</button>
</div>

<div class="page">

    <!-- Header image -->
    <div class="hall-header">
        <?php
        $header_img_path = '';
        if (!empty($print_header->header_image)) {
            $header_img_path = base_url('uploads/print_headerfooter/online_exam/' . $print_header->header_image);
        }
        if ($header_img_path): ?>
            <img src="<?php echo $header_img_path; ?>" alt="Header">
        <?php else: ?>
            <div style="background:#1a5c84;color:#fff;text-align:center;padding:18px;font-size:20px;font-weight:bold;">
                <?php echo htmlspecialchars($sch_setting->name ?? 'Institution'); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Title bar -->
    <div class="title-bar">Hall Ticket / Admit Card</div>

    <div class="hall-body">

        <!-- Candidate details -->
        <div class="candidate-block">
            <div class="candidate-photo">
                <?php
                if (!empty($applicant['image']) && file_exists(FCPATH . $applicant['image'])) {
                    $photo_src = base_url($applicant['image']);
                } elseif (strtolower($applicant['gender'] ?? '') === 'female') {
                    $photo_src = base_url('uploads/staff_images/default_female.jpg');
                } else {
                    $photo_src = base_url('uploads/staff_images/default_male.jpg');
                }
                ?>
                <img src="<?php echo $photo_src; ?>" alt="Photo">
                <div class="photo-label">Candidate Photo</div>
            </div>
            <div class="candidate-info">
                <table>
                    <tr>
                        <td>Reference No</td>
                        <td><strong style="font-size:15px;color:#1a5c84;"><?php echo htmlspecialchars($applicant['reference_no']); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Candidate Name</td>
                        <td><?php echo htmlspecialchars(strtoupper(trim(
                            $applicant['firstname'] . ' ' .
                            ($applicant['middlename'] ?? '') . ' ' .
                            ($applicant['lastname'] ?? '')
                        ))); ?></td>
                    </tr>
                    <tr>
                        <td>Father's Name</td>
                        <td><?php echo htmlspecialchars(strtoupper($applicant['father_name'] ?? '—')); ?></td>
                    </tr>
                    <tr>
                        <td>Mother's Name</td>
                        <td><?php echo htmlspecialchars(strtoupper($applicant['mother_name'] ?? '—')); ?></td>
                    </tr>
                    <tr>
                        <td>Date of Birth</td>
                        <td><?php echo !empty($applicant['dob']) ? date('d-M-Y', strtotime($applicant['dob'])) : '—'; ?></td>
                    </tr>
                    <tr>
                        <td>Gender</td>
                        <td><?php echo htmlspecialchars(ucfirst($applicant['gender'] ?? '—')); ?></td>
                    </tr>
                    <tr>
                        <td>Mobile</td>
                        <td><?php echo htmlspecialchars($applicant['mobileno'] ?? '—'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Exam details -->
        <div class="section-title"><i>&#9679;</i>&nbsp; Examination Details</div>
        <table class="exam-table">
            <tr>
                <td class="label-col">Examination</td>
                <td><strong><?php echo htmlspecialchars($exam->exam); ?></strong></td>
            </tr>
            <tr>
                <td class="label-col">Exam Date &amp; Time</td>
                <td>
                    <?php echo date('d-M-Y h:i A', strtotime($exam->exam_from)); ?>
                    &nbsp;–&nbsp;
                    <?php echo date('d-M-Y h:i A', strtotime($exam->exam_to)); ?>
                </td>
            </tr>
            <tr>
                <td class="label-col">Duration</td>
                <td><?php echo htmlspecialchars($exam->duration); ?></td>
            </tr>
            <tr>
                <td class="label-col">Mode</td>
                <td>Online (Computer Based Test)</td>
            </tr>
            <tr>
                <td class="label-col">Status</td>
                <td>
                    <?php if ((int)$exam->is_attempted === 1): ?>
                        <span style="color:green;font-weight:bold;">&#10003; Exam Submitted</span>
                    <?php else: ?>
                        <span style="color:#b8860b;font-weight:bold;">Not Yet Attempted</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- Instructions -->
        <div class="instructions-box">
            <h4>&#9888; Important Instructions to Candidates</h4>
            <ol>
                <li>This Hall Ticket must be presented at the time of examination.</li>
                <li>Report to the exam hall at least 15 minutes before the scheduled start time.</li>
                <li>Candidates are not allowed to carry any electronic devices, books, or notes into the exam hall.</li>
                <li>Do not navigate away or close the browser window once the exam has started — your exam will be auto-submitted when time expires.</li>
                <li>Ensure stable internet connectivity before starting the exam.</li>
                <li>In case of any technical issue, contact the admission office immediately.</li>
            </ol>
            <div style="margin-top:12px;padding:10px 14px;background:#fff3cd;border-left:4px solid #e6a817;border-radius:3px;color:#5a3e00;font-size:13px;line-height:1.8;">
                <strong>&#9733; Scholarship Eligibility &mdash; Mandatory Attendance Notice:</strong><br>
                Only candidates who are physically present at the examination centre, attend the exam in person, and duly sign the college attendance register on the scheduled date shall be deemed eligible for the scholarship. Remote or online-only participation, without in-person attendance, will render the candidate ineligible for scholarship benefits.
            </div>
        </div>

        <!-- Signature strip -->
        <div class="sig-strip">
            <div class="sig-box">Candidate's Signature</div>
            <div class="sig-box">Invigilator's Signature</div>
            <div class="sig-box">Authorised Signatory</div>
        </div>

    </div><!-- /.hall-body -->

    <!-- Footer -->
    <div class="hall-footer">
        <strong><?php echo htmlspecialchars($sch_setting->name ?? ''); ?></strong><br>
        <?php echo htmlspecialchars($sch_setting->address ?? ''); ?>
        <?php if (!empty($sch_setting->phone)): ?>
            &nbsp;|&nbsp; Ph: <?php echo htmlspecialchars($sch_setting->phone); ?>
        <?php endif; ?>
    </div>

</div><!-- /.page -->

<script>
    // Auto-trigger print dialog on page load
    window.onload = function() {
        // small delay so the image renders first
        setTimeout(function() { window.print(); }, 600);
    };
</script>
</body>
</html>
