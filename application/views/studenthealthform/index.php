<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Health & Emergency Information Form</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4f8;min-height:100vh;display:flex;flex-direction:column}
.page-header{background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08);padding:0}
.header-img-wrap{width:100%;max-height:160px;overflow:hidden;display:flex;justify-content:center;background:#fff}
.header-img-wrap img{width:100%;max-height:160px;object-fit:contain}
.page-body{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px}
.card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.10);padding:40px 36px;max-width:480px;width:100%;text-align:center}
.card-icon{width:64px;height:64px;background:linear-gradient(135deg,#e53935,#b71c1c);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
.card-icon svg{width:32px;height:32px;fill:#fff}
.card h1{font-size:22px;color:#1a1a2e;font-weight:700;margin-bottom:8px}
.card p{font-size:14px;color:#666;margin-bottom:28px;line-height:1.6}
.input-group{position:relative;margin-bottom:16px;text-align:left}
.input-group label{display:block;font-size:13px;font-weight:600;color:#444;margin-bottom:6px}
.input-group input{width:100%;padding:13px 16px;border:2px solid #e0e0e0;border-radius:10px;font-size:15px;outline:none;transition:border-color .2s}
.input-group input:focus{border-color:#e53935}
.btn-fetch{width:100%;padding:14px;background:linear-gradient(135deg,#e53935,#b71c1c);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s;letter-spacing:.3px}
.btn-fetch:hover{opacity:.9}
.btn-fetch:disabled{opacity:.6;cursor:not-allowed}
.error-msg{background:#fdecea;color:#c62828;border-radius:8px;padding:10px 14px;font-size:13px;margin-top:12px;text-align:left;display:none}
.note{font-size:12px;color:#999;margin-top:20px}
@media(max-width:480px){.card{padding:28px 20px}}
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
        <div class="card-icon">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <h1>Student Health &amp; Emergency Information</h1>
        <p>Please enter your child's admission number to begin. We'll prefill information already on record — you only need to complete the health details.</p>

        <div class="input-group">
            <label for="admission_no">Admission Number</label>
            <input type="text" id="admission_no" placeholder="e.g. 2024-001" autocomplete="off" autofocus>
        </div>
        <button class="btn-fetch" id="btn-fetch">Continue &rarr;</button>
        <div class="error-msg" id="error-msg"></div>
        <p class="note">This form is for existing enrolled students only.</p>
    </div>
</div>

<script>
var BASE_URL = '<?php echo site_url(); ?>';
document.getElementById('btn-fetch').addEventListener('click', fetchStudent);
document.getElementById('admission_no').addEventListener('keypress', function(e){ if(e.key==='Enter') fetchStudent(); });

function fetchStudent() {
    var admNo = document.getElementById('admission_no').value.trim();
    var errEl = document.getElementById('error-msg');
    var btn   = document.getElementById('btn-fetch');
    errEl.style.display = 'none';
    if (!admNo) { showError('Please enter the admission number.'); return; }
    btn.disabled = true;
    btn.textContent = 'Looking up...';
    var fd = new FormData();
    fd.append('admission_no', admNo);
    fetch(BASE_URL + 'studenthealthform/fetch', { method:'POST', body:fd })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            btn.disabled = false;
            btn.textContent = 'Continue →';
            if (res.status === '1') {
                sessionStorage.setItem('shf_data', JSON.stringify(res));
                window.location.href = BASE_URL + 'studenthealthform/form';
            } else {
                showError(res.msg);
            }
        })
        .catch(function(){ btn.disabled=false; btn.textContent='Continue →'; showError('Network error. Please try again.'); });
}
function showError(msg){ var el=document.getElementById('error-msg'); el.textContent=msg; el.style.display='block'; }
</script>
</body>
</html>
