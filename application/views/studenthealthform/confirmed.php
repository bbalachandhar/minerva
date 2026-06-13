<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Submitted Successfully</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column}
.page-header{background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.header-img-wrap{width:100%;max-height:160px;overflow:hidden;display:flex;justify-content:center;background:#fff}
.header-img-wrap img{width:100%;max-height:160px;object-fit:contain}
.page-body{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.10);padding:40px 36px;max-width:480px;width:100%;text-align:center}
.success-icon{width:72px;height:72px;background:linear-gradient(135deg,#43a047,#1b5e20);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.success-icon svg{width:40px;height:40px;fill:#fff}
h1{font-size:22px;color:#1a1a2e;font-weight:700;margin-bottom:10px}
p{font-size:14px;color:#666;line-height:1.6;margin-bottom:24px}
.btn-pdf{display:inline-block;width:100%;padding:15px;background:linear-gradient(135deg,#e53935,#b71c1c);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;text-decoration:none;margin-bottom:12px}
.btn-again{display:inline-block;width:100%;padding:13px;background:#fff;color:#666;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none}
.note{font-size:12px;color:#aaa;margin-top:16px}
</style>
</head>
<body>
<div class="page-header">
    <?php if (!empty($header_img)): ?>
    <div class="header-img-wrap">
        <img src="<?php echo htmlspecialchars($header_img); ?>" alt="<?php echo htmlspecialchars($school_name); ?>">
    </div>
    <?php endif; ?>
</div>
<div class="page-body">
    <div class="card">
        <div class="success-icon">
            <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
        </div>
        <h1>Thank You!</h1>
        <p>Your child's health and emergency information has been submitted successfully. Please download and keep a copy of the completed form for your records.</p>
        <a class="btn-pdf" id="btn-download" href="#">⬇ Download PDF</a>
        <a class="btn-again" href="<?php echo site_url('studenthealthform'); ?>">Submit for Another Student</a>
        <p class="note">You can return to this link anytime to download the PDF again.</p>
    </div>
</div>
<script>
var pdfUrl = sessionStorage.getItem('shf_pdf_url');
if (pdfUrl) {
    document.getElementById('btn-download').href = pdfUrl;
} else {
    document.getElementById('btn-download').textContent = 'PDF link unavailable — contact school';
    document.getElementById('btn-download').removeAttribute('href');
    document.getElementById('btn-download').style.opacity = '0.5';
    document.getElementById('btn-download').style.cursor = 'default';
}
</script>
</body>
</html>
